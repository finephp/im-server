<?php
namespace Daemon\Service;
use AckCommand;
use CommandType;
use GenericCommand;
if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
class CmdDirect extends CmdBase {
    protected $nowTime;
    public $client_id;
    /**
     * @param $genericCmd GenericCommand
     * @return bool| GenericCommand
     */
    static function exeCmd($genericCmd,$other=array()){
        $cmdDirect = new self($genericCmd,$other);
        return $cmdDirect->directCommand($genericCmd,$other);
    }
    public function __construct($genericCmd)
    {
        $this->nowTime = new \MongoDate();
    }

    /**
     * directCommand
     * @param $client_id
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand|bool
     */
    public function directCommand($genericCmd,$other){
        echo __METHOD__."\r\n";
        $peerId = $genericCmd->getPeerId();
        //注册用户
        $driectMessage = $genericCmd->getDirectMessage();
        $cid = $driectMessage->getCid();
        $convData = $this->_getConversation($cid);
        //如果聊天室不存在
        if($convData){
            $m = $convData['m'] or $m = [];
        }
        else{
            $m = [];
        }
        //查看peerid是否在对话之中,否则返回错误
        if(empty($convData) || !in_array($peerId,$m)){
            //处理返回信息
            $resp = new GenericCommand();
            $resp->setCmd(CommandType::ack);
            $resp->setI($genericCmd->getI());
            $resp->setPeerId($genericCmd->getPeerId());
            $ackMsg = new AckCommand();
            $ackMsg->setCode(4401);
            $ackMsg->setReason('INVALID_MESSAGING_TARGET');
            $ackMsg->setT(time()*1000);
            $resp->setAckMessage($ackMsg);
            return $this->pushClientQueue($resp);
        }

        //插入消息数据表
        $messageModel = $this->_getMessageModel();
        $data = array(
            'convId' => $cid,
            'from' => $genericCmd->getPeerId(),
            'data' => $driectMessage->getMsg(),
            'passed' => false,
            'createdAt' => $this->nowTime
        );
        $result = $messageModel->add($messageModel->create($data));
        log_write($messageModel->_sql(),__METHOD__);
        $msgId = $messageModel->getLastInsID();
        //插入到对话中的所有对像
        $messageLogModel = $this->_getMessageLogsModel();
        foreach($m as $to){
            $data = array(
                'convId' => $cid,
                'msgId' => $msgId,
                'from' => $genericCmd->getPeerId(),
                'to' => $to,
                'data' => $driectMessage->getMsg(),
                'ackAt'=>null,
                'receipt'=>$driectMessage->getR(),
                'createdAt' => $this->nowTime,
                'unread'=>true,
                'fromIp'=>$other['ip']
            );
            $data = $messageLogModel->create($data);
            //记录最后一条时间
            $this->nowTime = $data['createdAt'];
            //echo __METHOD__,':add_time:',self::getTimestamp($this->nowTime),"\r\n";
            //不要发送给自已
            if($to == $peerId){
                continue;
            }
            $result = $messageLogModel->add($data);
            log_write($messageLogModel->_sql(),__METHOD__);
        }
        //更新对话最后一条消息时间
        $convModel = $this->_getConvModel();
        $convModel->where(array(
            '_id' => $cid
        ))->save(array(
            'lm' => $this->nowTime
        ));
        //处理返回信息
        $resp = new GenericCommand();
        $resp->setCmd(CommandType::ack);
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $ackMsg = new AckCommand();
        $ackMsg->setT(self::getTimestamp($this->nowTime));
        //echo __METHOD__,':ack_time:',self::getTimestamp($this->nowTime),"\r\n";
        $ackMsg->setUid($msgId);
        $resp->setAckMessage($ackMsg);
        //todo
        // need receipt false 否不需要返回ack true 需要回执
        $r = $driectMessage->getR();
        $this->pushClientQueue($resp);
        //广播到会话中所有人
        $this->sendDirect($genericCmd,$m,$msgId);
        return true;
    }

    //发送消息
    /**
     * @param $client_id
     * @param $genericCmd GenericCommand
     */
    public function sendDirect($genericCmd,$m,$msgId){
        $peerId = $genericCmd->getPeerId();
        $directMessage = $genericCmd->getDirectMessage();
        $resp = new GenericCommand();
        $resp->setCmd(CommandType::direct);//2
        $respMsg = new \DirectCommand();
        $resp->setDirectMessage($respMsg);
        $respMsg->setId($msgId);
        $respMsg->setFromPeerId($peerId);
        $respMsg->setTimestamp(self::getTimestamp($this->nowTime));
        $respMsg->setCid($directMessage->getCid());
        $respMsg->setMsg($directMessage->getMsg());
        $respMsg->setTransient($directMessage->getTransient());
        foreach($m as $to) {
            //不要发给自已
            if($to == $peerId){
                continue;
            }
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }

    /**
     * 回执
     */
    public function opRcp(){

    }
    protected function _getMessageModel()
    {
        return Db::MongoModel('message');
    }

    protected function _getMessageLogsModel(){
        return Db::MongoModel('messageLogs');
    }
}
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
        //todo start
        echo __METHOD__."\r\n";
        $msgId = md5(mt_rand(1,1000));
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
        $this->pushClientQueue($resp);
        $m = RedisService::getInstance()->getAllPeerId(true);
        $this->sendDirect($genericCmd,$m,$msgId);
        return true; // todo end

        $peerId = $genericCmd->getPeerId();
        //注册用户
        G('t1_start');
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
        G('t1_end');
        echo colorize(__METHOD__.' getConv runtime:'.G('t1_start','t1_end'),'NOTE')."\r\n";
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
        G('t1_start');
        //插入消息数据表
        $messageModel = $this->_getMessageModel();
        $data = array(
            'convId' => $cid,
            'from' => $genericCmd->getPeerId(),
            'data' => $driectMessage->getMsg(),
            'passed' => false,
            'createdAt' => $this->nowTime,
            'updatedAt' => $this->nowTime,
        );
        $result = $messageModel->add($data);
        //log_write($messageModel->_sql(),__METHOD__);
        $msgId = $messageModel->getLastInsID();
        G('t1_end');
        echo colorize(__METHOD__.' insert message runtime:'.G('t1_start','t1_end'),'NOTE')."\r\n";
        /* todo 改成不要插到 这张表
        //插入到对话中的所有成员的记录
        $messageLogModel = $this->_getMessageLogsModel();
        $data = array(
            'convId' => $cid,
            'msgId' => $msgId,
            'from' => $genericCmd->getPeerId(),
            'data' => $driectMessage->getMsg(),
            'ackAt'=>null,
            'receipt'=>$driectMessage->getR(),
            'createdAt' => $this->nowTime,
            'unread'=>true,
            'fromIp'=>$other['ip']
        );
        $data = $messageLogModel->create($data);

        //批量插入
        $data_list = array();
        foreach($m as $to){
            //不要发送给自已
            if($to == $peerId){
                continue;
            }
            $data['to'] = $to;
            $data_list[] = $data;
        }
        if($data_list){
            $result = $messageLogModel->addAll($data_list);
        }
        unset($data_list);
        */
        //更新对话最后一条消息时间

        G('t1_start');

        $convModel = $this->_getConvModel();
        $convModel->where(array(
            '_id' => $cid
        ))->save(array(
            'lm' => $this->nowTime
        ));
        G('t1_end');
        echo colorize(__METHOD__.' updateConv runtime:'.G('t1_start','t1_end'),'NOTE')."\r\n";
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
        G('t1_start');
        $this->pushClientQueue($resp);
        G('t1_end');
        echo colorize(__METHOD__.' pushClientQueue runtime:'.G('t1_start','t1_end'),'NOTE')."\r\n";

        G('t1_start');
        //广播到会话中所有人
        //查询在线的人
        $m = self::getOnlineSession($m);
        G('t1_end');
        echo colorize(__METHOD__.' getOnlineSession runtime:'.G('t1_start','t1_end'),'NOTE')."\r\n";
        G('t1_start');
        $this->sendDirect($genericCmd,$m,$msgId);
        G('t1_end');
        echo colorize(__METHOD__.' emit_sendDirect runtime:'.G('t1_start','t1_end'),'NOTE')."\r\n";

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

    protected function _getMessageModel()
    {
        return Db::MongoModel('message');
    }

    protected function _getMessageLogsModel(){
        return Db::MongoModel('messageLogs');
    }
}
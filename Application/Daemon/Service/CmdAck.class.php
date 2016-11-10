<?php
namespace Daemon\Service;
use CommandType;
use GenericCommand;
if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
class CmdAck extends CmdBase {
    /**
     * @param $genericCmd GenericCommand
     * @return bool| GenericCommand
     */
    static function exeCmd($genericCmd){
        $cmdAck = new self($genericCmd);
        return $cmdAck->ackCommand($genericCmd);
    }
    /**
     * ackCommand
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand|bool
     */
    public function ackCommand($genericCmd){
        $ackMessage = $genericCmd->getAckMessage();
        $cid = $ackMessage->getCid();
        //处理返回信息
        $resp = new GenericCommand();
        $resp->setCmd(CommandType::rcp);
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $rcpMsg = new \RcpCommand();
        $rcpMsg->setCid($cid);
        $rcpMsg->setT(time()*1000);
        $resp->setRcpMessage($rcpMsg);
        $msgModel = $this->_getMessageLogsModel();
        $where = array(
            'convId'=>$cid,
            'to' => $genericCmd->getPeerId(),
            'createdAt' => array('egt',self::getMongoDate($ackMessage->getFromts()))
        );
        //批量更新对话消息为已读
        $result = $msgModel->where($where)->save($msgModel->create(array(
            'unread'=>false,
        ),MongoModel::MODEL_UPDATE));
        //log_write($msgModel->_sql(),'update messageLogs');
        //log_write(print_r($result,true));
        //查找该对话的需要回执的消息，并通知发消息的那个人，说消息已经被收到啦
        $where['receipt'] = true;
        $msgDataArr = $msgModel->where($where)->select() or $msgData = array();
        //发消息
        //只查在线之人
        foreach($msgDataArr as $msgData) {
            if(!self::isOnline($msgData['from'])){
                continue;
            }
            $rcpMsg->setId($msgData['msgId']);
            $resp->setPeerId($msgData['from']);
            $this->pushClientQueue($resp);
        }
        return true;
    }

    protected function _getMessageModel()
    {
        return Db::MongoModel('message');
    }

    protected function _getMessageLogsModel(){
        return Db::MongoModel('messageLogs');
    }
}
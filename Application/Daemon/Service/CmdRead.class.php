<?php
namespace Daemon\Service;
use CommandType;
use GenericCommand;
use UnreadCommand;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
class CmdRead extends CmdBase {
    public $client_id;
    /**
     * @param $genericCmd GenericCommand
     * @return bool| GenericCommand
     */
    static function exeCmd($genericCmd){
        $cmdRead = new self($genericCmd);
        return $cmdRead->readCommand($genericCmd);
    }
    /**
     * readCommand todo
     * 标记为已读
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand|bool
     */
    public function readCommand($genericCmd){
        $peerId = $genericCmd->getPeerId();
        $readMessage = $genericCmd->getReadMessage();
        $convs = $readMessage->getConvs() or $convs = array();
        $convList = [];
        foreach($convs as $v){
            $convList[$v->getCid()] = $v->getCid();
        }
        //查找是否有未读消息 unreadMessage
        if($convList){
            $where['convId'] = array('in',array_values($convList));
        }
        $logsModel = Db::MongoModel('messageLogs');
        //标记为已读消息
        $unreadMessage = new UnreadCommand();
        foreach ($convs as $conv){
            $cid = $conv->getCid();
            $time = $conv->getTimestamp();
            $where = array(
                'to' => $peerId,
                'convId'=>$cid,
                'createdAt' => array('elt',self::getMongoDate($time))
            );
            $data = $logsModel->create(array(
                'unread' => false
            ),MongoModel::MODEL_UPDATE);
            //更新记录为已读
            $logsModel->where($where)->save($data);
            log_write($logsModel->_sql(),__METHOD__);
            $unReadTuple = new \UnreadTuple();
            $unReadTuple->setCid($cid);
            $unReadTuple->setUnread(0);
            $unreadMessage->appendConvs($unReadTuple);
        }
        $respUnread = new GenericCommand();
        $respUnread->setPeerId($genericCmd->getPeerId());
        $respUnread->setCmd(CommandType::unread);
        $respUnread->setAppId($genericCmd->getAppId());
        $respUnread->setUnreadMessage($unreadMessage);
        $this->pushClientQueue($respUnread);
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
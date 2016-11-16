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
        $debug_sql = debug_factory('LC:SQL','33m');
        $peerId = $genericCmd->getPeerId();
        $readMessage = $genericCmd->getReadMessage();
        $convs = $readMessage->getConvs() or $convs = array();
        $convList = [];
        foreach($convs as $v){
            $convList[$v->getCid()] = $v->getCid();
        }
        $userMsgModel = Db::MongoModel('userMessage');
        $where = array(
            'peerId'=>$peerId
        );
        $mongoTime = new \MongoDate();
        $nowtime = self::getTimestamp($mongoTime);
        $data = array(
            'peerId'=>$peerId,
        );
        //查询是否存在obj_id
        $info = $userMsgModel->where($where)->find();
        if($info){
            $data['_id'] = $info['_id'];
            $data_conv = $info['conv'];
        }
        else {
            $data_conv = array();
        }
        //标记为已读消息
        $unreadMessage = new UnreadCommand();
        foreach ($convs as $conv){
            $cid = $conv->getCid();
            $time = $conv->getTimestamp();
            $data_conv[$cid] = array(
                'convId'=>$cid,
                'unread'=>0,
                'lm'=>$mongoTime
            );
            //更新记录为已读
            $unReadTuple = new \UnreadTuple();
            $unReadTuple->setCid($cid);
            $unReadTuple->setUnread(0);
            $unreadMessage->appendConvs($unReadTuple);
        }
        $data['conv'] = $data_conv;
        //更新记录
        $userMsgModel->where($where)->add($data,array(),true);
        $debug_sql(__METHOD__,$userMsgModel->_sql());
        $respUnread = new GenericCommand();
        $respUnread->setPeerId($genericCmd->getPeerId());
        $respUnread->setCmd(CommandType::unread);
        $respUnread->setAppId($genericCmd->getAppId());
        $respUnread->setUnreadMessage($unreadMessage);
        $this->pushClientQueue($respUnread);
        return true;
    }
    /**
     * readCommand todo
     * 标记为已读
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand|bool
     */
    public function readCommand_Old($genericCmd){
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
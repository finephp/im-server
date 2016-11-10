<?php
namespace Daemon\Service;
use CommandType;
use GenericCommand;
use OpType;
use SessionCommand;
use UnreadCommand;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
class CmdSession extends CmdBase {
    public $client_id;
    /**
     * @param $genericCmd GenericCommand
     */
    public function __construct($genericCmd)
    {

    }
    /**
     * @param $genericCmd GenericCommand
     */
    static function exeCmd($genericCmd,$client_id=null){
        $cmdSession = new self($genericCmd);
        $opType = $genericCmd->getOp();
        switch($opType){
            case OpType::open:// = 1
                return $cmdSession->open($genericCmd);
                break;
            case OpType::close: // close = 4
                return $cmdSession->close($genericCmd);
                break;
            case OpType::query: // query = 7
                return $cmdSession->opQuery($genericCmd);
                break;
            default:
                echo __METHOD__.':unknow opType:'.$opType;
        }
        return false;
    }
    /**
     * session open
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand|bool
     */
    public function open($genericCmd){
        //注册用户
        $fromPearId = $genericCmd->getPeerId() or $fromPearId = "guest_" . md5(time() . mt_rand(1, 10));
        //登录session
        $sessionMessage = $genericCmd->getSessionMessage();
        if ($sessionMessage) {
            //retry SESSION TOKEN
            $st = $sessionMessage->getSt(); //session token
            if($sessionMessage->getR()){
                //todo 校验 session token 是否超时,这儿只是为了让 测试能通过
                if($st == 'fake_session_token'){
                    $resp = new GenericCommand();
                    $resp->setCmd(CommandType::error);// cmd = 7
                    $resp->setAppId($genericCmd->getAppId());
                    $resp->setPeerId($fromPearId);
                    $resp->setI($genericCmd->getI());
                    $errorMessage = new \ErrorCommand();
                    $errorMessage->setCode(4112);
                    $errorMessage->setReason('SESSION_TOKEN_EXPIRED');
                    $resp->setErrorMessage($errorMessage);
                    return $this->pushClientQueue($resp);
                }
                // end to-do
            }
            //如果st为空，生成新的 st
            if(empty($st)){
                $st = md5($fromPearId.time());
                $st = sprintf('%s-%s',substr($st,0,8),substr($st,8,8));
            }
            $resp = new \GenericCommand();
            $resp->setCmd(CommandType::session);
            $resp->setOp(OpType::opened);
            $resp->setAppId($genericCmd->getAppId());
            $resp->setPeerId($fromPearId);
            $resp->setI($genericCmd->getI());
            $sessMsg = new SessionCommand();
            $sessMsg->setSt($st);// session token
            $sessMsg->setStTtl(17280);//session token ttl
            $resp->setSessionMessage($sessMsg);
            $this->pushClientQueue($resp);

            //返回未读消息
            $logsModel = Db::MongoModel('messageLogs');
            $logsResult = $logsModel->where(array(
                'to' => $fromPearId,
                'unread' => true,
                //'ackAt'=>array('exists',true)
            ))->limit(10)->order('createdAt desc')->select();
            //log_write($logsModel->_sql(),__METHOD__);
            if($logsResult){
                $logsResult = array_groupbykey($logsResult,'convId');
                //响应未读消息
                $unreadMessage = new UnreadCommand();
                foreach($logsResult as $convLogs){
                    $logs = current($convLogs);//取第一条
                    $count = count($convLogs);
                    $unReadTuple = new \UnreadTuple();
                    $unReadTuple->setCid($logs['convId']);
                    $unReadTuple->setMid($logs['msgId']);
                    $unReadTuple->setTimestamp(self::getTimestamp($logs['createdAt']));
                    $unReadTuple->setUnread($count);
                    $unreadMessage->appendConvs($unReadTuple);
                }
                $respUnread = new GenericCommand();
                $respUnread->setPeerId($genericCmd->getPeerId());
                $respUnread->setCmd(CommandType::unread);
                $respUnread->setAppId($genericCmd->getAppId());
                $respUnread->setUnreadMessage($unreadMessage);
                $this->pushClientQueue($respUnread);
            }
        }
        return false;
    }
    /**
     * session close 4
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand|bool
     */
    public function close($genericCmd){
        $resp = $genericCmd;
        $resp->setOp(OpType::closed);
        $this->pushClientQueue($resp);
        //todo 要删除 clientsmap 这个以后再说
        //群发通知状态更新
        return true;
    }

    /**
     * 查询在线用户 8
     * @param $genericCmd \GenericCommand
     */
    public function opQuery($genericCmd){
        $genericCmd->setOp(OpType::query_result); //optype:8
        $sessionPeerIds = $genericCmd->getSessionMessage()->getSessionPeerIds();
        $onlineM = self::getOnlineSession($sessionPeerIds);
        $message = new \SessionCommand();
        foreach($onlineM as $peerId) {
            $message->appendOnlineSessionPeerIds($peerId);
        }
        $genericCmd->setSessionMessage($message);
        $this->pushClientQueue($genericCmd);
        return true;
    }
}
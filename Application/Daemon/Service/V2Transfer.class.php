<?php
namespace Daemon\Service;
use \CommandType;
use GatewayWorker\Lib\Gateway;
use \GenericCommand;
use \OpType;
use SessionCommand;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');

//for win
//require_once (APP_PATH.'Common/Vendor/protocolbuf/message/pb_message.php');
//require_once (dirname(APP_PATH).'/proto/pb_proto_message.php');
//for end
class V2Transfer
{
    static $opTypeMap = array();
    static $opTypeStrMap = array();
    static function transCmdIn($in){
        //ping
        if($in == '{}'){
            return self::transPingIn();
        }
        $data = json_decode($in) or $data = $in;
        switch ($data->cmd){
            case 'session':
                return self::transSessionIn($data);
                break;
            case 'conv':
                return self::transConvIn($data);
            case 'logs':
                return self::transLogsIn($data);
            case 'direct':
                return self::transDirectIn($data);
            case 'ack':
                return self::transAckIn($data);
            default:
                echo __METHOD__,' unknow cmd:'.$data->cmd."\r\n";
                return 'transCmdIn error:'.$in;
        }
    }
    /**
     * @param $genericCmd GenericCommand
     */
    static function transCmdOut($genericCmd){
        switch ($genericCmd->getCmd()){
            case CommandType::session:
                return self::transSessionOut($genericCmd);
                break;
            case CommandType::conv: //1
                return self::transConvOut($genericCmd);
                break;
            case CommandType::unread://5
                return self::transUnreadOut($genericCmd);
                break;
            case CommandType::error://14
                return self::transErrorOut($genericCmd);
                break;
            case CommandType::logs://6
                return self::transLogsOut($genericCmd);
                break;
            case CommandType::ack://3
                return self::transAckOut($genericCmd);
                break;
            case CommandType::direct://2
                return self::transDirectOut($genericCmd);
                break;
            case CommandType::echo_a://14
                return self::transPingOut();
                break;
            default:
                echo __METHOD__,' unknow cmd ',$genericCmd->getCmd()."\r\n";
                return false;
        }
    }

    static function transSessionIn($data){
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::session);
        $genericCmd->setOp(self::getOpType($data->op));
        $genericCmd->setAppId($data->appId);
        $genericCmd->setI($data->i);
        $genericCmd->setPeerId($data->peerId);
        $message = new SessionCommand();
        if($data->op == 'query'){
            $sessionPeerIds = $data->sessionPeerIds or $sessionPeerIds = [];
            foreach($sessionPeerIds as $v) {
                $message->appendSessionPeerIds($v);
            }
        }
        $message->setUa($data->ua);
        $genericCmd->setSessionMessage($message);
        return $genericCmd;
    }
    /**
     * @param $genericCmd GenericCommand
     */
    static function transSessionOut($genericCmd){
        $resp = array(
            'cmd'=>'session',
            'op'=>self::getOpTypeStr($genericCmd->getOp()),
            'i'=>$genericCmd->getI(),
            'peerId'=>$genericCmd->getPeerId(),
            'appId'=>$genericCmd->getAppId(),
            'onlineSessionPeerIds'=>$genericCmd->getSessionMessage()->getOnlineSessionPeerIds(),
        );
        if($genericCmd->getOp() == OpType::query_result){
            $resp['op'] = 'query-result';
        }
        echo __METHOD__,print_r($resp,true);
        return json_encode($resp);
    }

    //转换会话 in
    static function transConvIn($data){
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::conv);
        $genericCmd->setOp(self::getOpType($data->op));
        $genericCmd->setI($data->i);
        $genericCmd->setPeerId($data->peerId);
        $message = new \ConvCommand();
        if(!empty($data->cid)){
            $message->setCid($data->cid);
        }
        //查询
        if($data->op == 'query'){
            $where = new \JsonObjectMessage();
            if(!empty($data->where->m)){
                //v2版
                if(is_string($data->where->m)){
                    $data->where->m = array('$in'=>[$data->where->m]);
                }
            }
            $where->setData(json_encode($data->where));
            $message->setWhere($where);
            $message->setSort($data->sort);
            $message->setLimit($data->limit);
            $message->setSkip($data->skip);
            $message->setFlag($data->flag);
        }
        //创建 start
        elseif($data->op == 'start') {
            $attr = new \JsonObjectMessage();
            $attr->setData(json_encode($data->attr));
            $message->setAttr($attr);
            $message->setUnique($data->unique);
            $message->setTransient($data->transient);
            if (!empty($data->m)) {
                foreach ($data->m as $v) {
                    $message->appendM($v);
                }
            }
        }
        //创建 add,remove
        elseif(
            $data->op == 'add' ||
            $data->op == 'remove'
        ) {
            $message->setCid($data->cid);
            if (!empty($data->m)) {
                foreach ($data->m as $v) {
                    $message->appendM($v);
                }
            }
        }
        elseif($data->op == 'count') {
            $message->setCid($data->cid);
        }
        else{
            echo colorize(__METHOD__.' unknow op:'.$data->op.' error','WARNING')."\r\n";
        }
        $genericCmd->setConvMessage($message);
        return $genericCmd;
    }

    /**
     * @param $genericCmd GenericCommand
     */
    static function transConvOut($genericCmd){
        $getConvResults = function()use($genericCmd){
            $convData = $genericCmd->getConvMessage()->getResults()->getData();
            $results = json_decode($convData,true);
            return $results;
        };
        $message = $genericCmd->getConvMessage();
        $resp = array(
            'cmd'=>'conv',
            'op'=>self::getOpTypeStr($genericCmd->getOp()),
            'i'=>$genericCmd->getI(),
            'peerId'=>$genericCmd->getPeerId(),
        );
        if($message && $message->getCid()){
            $resp['cid'] = $message->getCid();
        }
        //查询结果
        if($genericCmd->getOp() == OpType::results){
            $resp['results'] = $getConvResults();
        }
        //创建会话
        elseif($genericCmd->getOp() == OpType::started){
            $resp['cid'] = $message->getCid();
            $resp['cdate']= $message->getCdate();
        }
        //加入会话,移除
        elseif($genericCmd->getOp() == OpType::added
            || $genericCmd->getOp() == OpType::removed
        ){
            $resp['cid'] = $message->getCid();
        }
        elseif($genericCmd->getOp() == OpType::members_left
        ){
            $resp['op'] = 'members-left';
            $resp['cid'] = $message->getCid();
        }
        elseif($genericCmd->getOp() == OpType::kicked
        ){
            $resp['cid'] = $message->getCid();
        }
        elseif($genericCmd->getOp() == OpType::result
        ){
            $resp['count'] = $message->getCount();
        }
        else{
            echo colorize(__METHOD__.' unknow op:'.$genericCmd->getOp().' error','WARNING')."\r\n";
        }
        //echo __METHOD__,print_r($resp,true);
        return json_encode($resp);
    }
    //ping in
    static function transPingIn(){
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::echo_a);
        return $genericCmd;
    }


    //ping out
    static function transPingOut(){
        return '{}';
    }
    /**
     * @param $genericCmd GenericCommand
     */
    static function transUnreadOut($genericCmd){
        $resp = array(
            'cmd'=>'unread',
            'appId'=>$genericCmd->getAppId(),
            'peerId'=>$genericCmd->getPeerId(),
        );
        $message = $genericCmd->getUnreadMessage();
        //todo
        $resp['unread'] = [];
        return json_encode($resp);
    }

    //转换会话 in
    static function transLogsIn($data){
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::logs);
        $genericCmd->setI($data->i);
        $genericCmd->setPeerId($data->peerId);
        $message = new \LogsCommand();
        $message->setCid($data->cid);
        $message->setL($data->limit);
        $message->setT($data->t);
        $message->setTt($data->Tt);
        $genericCmd->setLogsMessage($message);
        return $genericCmd;
    }

    /**
     * @param $genericCmd GenericCommand
     */
    static function transLogsOut($genericCmd){
        $resp = array(
            'cmd'=>'logs',
            'i'=>$genericCmd->getI(),
            'peerId'=>$genericCmd->getPeerId(),
        );
        $message = $genericCmd->getLogsMessage();
        $items = $message->getLogs() or $items = [];
        $resp['logs'] = [];
        foreach($items as $v){
            $resp['logs'][] = array(
                'from' => $v->getFrom(),
                'data' => $v->getData(),
                'timestamp' => $v->getTimestamp(),
                'msgId' => $v->getMsgId(),
                'ackAt' => $v->getAckAt(),
            );
        }
        return json_encode($resp);
    }

    //聊天
    static function transDirectIn($data){
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::direct);
        $genericCmd->setI($data->i);
        $genericCmd->setPeerId($data->peerId);
        $message = new \DirectCommand();
        $message->setCid($data->cid);
        $message->setFromPeerId($data->peerId);
        $message->setMsg($data->msg);
        $message->setR($data->r);
        $message->setTransient($data->transient);
        $genericCmd->setDirectMessage($message);
        return $genericCmd;
    }

    /**
     * @param $genericCmd GenericCommand
     */
    static function transAckOut($genericCmd){
        $resp = array(
            'cmd'=>'ack',
            'i'=>$genericCmd->getI(),
            'peerId'=>$genericCmd->getPeerId(),
        );
        $message = $genericCmd->getAckMessage();
        $resp['uid'] = $message->getUid();
        $resp['t'] = $message->getT();
        return json_encode($resp);
    }

    /**
     * @param $genericCmd GenericCommand
     */
    static function transDirectOut($genericCmd){
        $resp = array(
            'cmd'=>'direct',
            'peerId'=>$genericCmd->getPeerId(),
        );
        $message = $genericCmd->getDirectMessage();
        $resp['id'] = $message->getId();
        $resp['timestamp'] = $message->getTimestamp();
        $resp['cid'] = $message->getCid();
        $resp['fromPeerId'] = $message->getFromPeerId();
        $resp['msg'] = $message->getMsg();
        $resp['offline'] = $message->getOffline();
        return json_encode($resp);
    }

    //回复
    static function transAckIn($data){
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::ack);
        $genericCmd->setPeerId($data->peerId);
        $message = new \AckCommand();
        $message->setCid($data->cid);
        $message->setMid($data->msg);
        $genericCmd->setAckMessage($message);
        return $genericCmd;
    }

    /**
     * @param $genericCmd GenericCommand
     */
    static function transErrorOut($genericCmd){
        $resp = array(
            'cmd'=>'error',
            'i'=> $genericCmd->getI(),
            'peerId'=>$genericCmd->getPeerId(),
        );
        $message = $genericCmd->getErrorMessage();
        $resp['code'] = $message->getCode();
        $resp['reason'] = $message->getReason();
        //todo
        return json_encode($resp);
    }

    static function getOpType($op){
        if(empty(self::$opTypeMap)){
            $opType = new OpType();
            $opTypeMap = $opType->getEnumValues();
        }
        else{
            $opTypeMap = self::$opTypeMap;
        }
        return $opTypeMap[$op];
    }

    static function getOpTypeStr($op){
        if(empty(self::$opTypeStrMap)){
            $opType = new OpType();
            $opTypeStrMap = $opType->getEnumValues();
            $opTypeStrMap = array_flip($opTypeStrMap);
        }
        else{
            $opTypeStrMap = self::$opTypeStrMap;
        }
        return $opTypeStrMap[$op];
    }
}



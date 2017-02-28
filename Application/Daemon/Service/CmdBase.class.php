<?php
namespace Daemon\Service;
use CommandType;
use ErrorCommand;
use GatewayWorker\Lib\Gateway;
use GenericCommand;
if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
abstract class CmdBase{
    /**
     * @param $genericCmd GenericCommand
     * @return bool|GenericCommand
     */
    static function exeCmd($genericCmd){
        return $genericCmd;
    }
    /**
     * @param $code int
     * @param $reason string
     * @return \GenericCommand|bool
     */
    public function respError($code,$reason){
        $resp = new GenericCommand();
        $resp->setCmd(CommandType::error);
        $error = new ErrorCommand();
        $error->setCode($code);
        $error->setReason($reason);
        $resp->setErrorMessage($error);
        return $resp;
    }

    /**
     * @param $cid string
     * @return mixed
     */
    protected function _getConversation($cid){
        $convModel = $this->_getConvModel();
        return $convModel->find($cid);
    }

    protected function _getConvModel(){
        return Db::MongoModel('Rtm_Conversation');
    }
    protected function _getUserConvModel(){
        return Db::MongoModel('Rtm_UserConversations');
    }

    /**
     * @param $resp
     * @return string
     */
    protected function encodeResp($resp){
        /**  todo debug
        ob_start();
        echo 'encodeResp:';
        $resp->dump();
        $respstr = ob_get_clean();
        //log_write($respstr);
        echo $respstr;
         */
        $new_resp = $resp->serializeToString();
        //这个地方要注意 todo 可能在web版中会有问题
        //$new_resp .= pack('H*','EA0600');
        return $new_resp;
    }

    /**
     * @param $data GenericCommand|string
     * @return bool
     */
    protected function pushClientQueue($data){
        //如果不进redis的话，直接调用gateway发送
        if(defined('ENV_NOREDIS')){
            //如果有i，发送到当前的
            if($data->getI()){
                Gateway::sendToCurrentClient($this->encodeResp($data));
            }else{
                Gateway::sendToUid($data->getPeerId(), $this->encodeResp($data));
            }
            return true;
        }
        $data = is_string($data) ? $data:$this->encodeResp($data);
        $redisService = RedisService::getInstance();
        $redisService->pushClientQueue($data);
        return true;
    }

    /**
     * @param $data GenericCommand
     * @param $cid string
     * @param $exclude_peerid array
     * @return bool
     */
    protected function pushGroupQueue($data,$cid,$exclude_peerid = null){
        $data = $this->encodeResp($data);
        if($exclude_peerid) {
            $exclude_client_id = Gateway::getClientIdByUid($exclude_peerid);
        }
        else{
            $exclude_client_id = null;
        }
        Gateway::sendToGroup($cid,$data,$exclude_client_id);
        return true;
    }

    protected function joinGroup(){

    }

    /**
     * @param $timestamp int
     * @return \MongoDate
     */
    protected static function getMongoDate($timestamp){
        return new \MongoDate(substr($timestamp,0,10),substr($timestamp,-3)*1000);
    }

    /**
     * @param $date \MongoDate
     * @return int 13
     */
    static function getTimestamp($date){
        return floor($date->sec*1000+($date->usec/1000));
    }

    /**
     * 获取在线 session
     * @param $m
     * @return string[]
     */
    protected static function getOnlineSession($m){
        return RedisService::getInstance()->getPeerIds($m);
    }

    protected static function isOnline($peerId){
        return RedisService::getInstance()->isPeerIdExists($peerId);
    }

    /**
     * @param $name
     * @param $debug
     * @param $color
     */
    protected static function debug($str,$title=''){
        static $__debug_Closure;
        if(is_object($str) && get_class($str) == 'Closure'){
            $__debug_Closure = $str;
            return;
        }
        if(empty($__debug_Closure)){
            $__debug_Closure = debug_factory('');
        }
        call_user_func($__debug_Closure,$str,$title);
    }

    static function E($msg){
        echo (colorize($msg."\r\n",'FAILURE'));
        //return false;
    }
}
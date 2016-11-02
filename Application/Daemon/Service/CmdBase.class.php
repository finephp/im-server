<?php
namespace Daemon\Service;
use CommandType;
use ErrorCommand;
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

    protected function _getConversation($cid){
        $convModel = $this->_getConvModel();
        return $convModel->find($cid);
    }

    protected function _getConvModel(){
        return Db::MongoModel('conversation');
    }
    protected function encodeResp($resp){
        $new_resp = $resp->serializeToString();
        //这个地方要注意 todo 可能在web版中会有问题
        //$new_resp .= pack('H*','EA0600');
        if($this->noBinary) {
            $new_resp = base64_encode($new_resp);
        }
        ob_start();
        echo 'encodeResp:';
        $resp->dump();
        $respstr = ob_get_clean();
        log_write($respstr);
        echo $respstr;
        return $new_resp;
    }
    protected function pushClientQueue($data){
        echo (__METHOD__);
        $data = $this->encodeResp($data);
        $redisService = RedisService::getInstance();
        $redisService->pushClientQueue($data);
        return true;
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
    protected static function getTimestamp($date){
        return floor($date->sec*1000+($date->usec/1000));
    }
}
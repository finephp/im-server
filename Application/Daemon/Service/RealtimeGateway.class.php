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
class RealtimeGateway {
    /**
     * @var $connection RealtimeConnection
     */
    public $connection;
    public $client_id;
    public $data;
    public $noBinary;
    public $SecWebSocketProtocol = '';

    /**
     * @param $client_id
     * @param $data
     */
    static function handleMessage($client_id,$data){
        $connection = self::getConnection($client_id);
        $noBinary = empty($connection->SecWebSocketProtocol) || $connection->SecWebSocketProtocol=='lc.protobase64.3';
        //echo $connection->SecWebSocketProtocol;
        //v2版
        if(empty($connection->SecWebSocketProtocol)){
            $packed = V2Transfer::transCmdIn($data);
        }
        elseif($noBinary){
            $packed = base64_decode($data);
        }
        else{
            //设置为 BINARY_TYPE_ARRAYBUFFER 格式
            $packed = $data;
            $connection->websocketType = \Workerman\Protocols\Websocket::BINARY_TYPE_ARRAYBUFFER;
        }
        $service = new self();
        $service->noBinary = $noBinary;
        $service->SecWebSocketProtocol = $connection->SecWebSocketProtocol;
        $service->handleGenericCommand($connection,$packed);
    }

    /**
     * @param $client_id string
     */
    static function handleClose($client_id){
        $connection = self::getConnection($client_id);
        RealtimeGatewayClients::unregister($connection);
    }

    /**
     * @param $resp GenericCommand
     * @return string
     */
     public static function encodeResp($resp,$session=array()){
        /* todo debug
        ob_start();
        $resp->dump();
        $respstr = ob_get_clean();
        log_write($respstr);
        echo $respstr;
        unset($respstr);
        */
        //echo __METHOD__,print_r($session,true);
         $noBinary = false;
        if($session){
            //if(strpos($session['ua'],'js/2.')===0){
            if(empty($session['SecWebSocketProtocol'])){
                $new_resp = V2Transfer::transCmdOut($resp);
                return $new_resp;
            }
            if($session['SecWebSocketProtocol']== 'lc.protobase64.3'){
                $noBinary = true;
            }
        }
        $new_resp = $resp->serializeToString();
        //这个地方要注意 todo 可能在web版中会有问题
        //$new_resp .= pack('H*','EA0600');
        if($noBinary) {
            $new_resp = base64_encode($new_resp);
        }
        return $new_resp;
    }

    /**
     * @param $connection RealtimeConnection
     * @param $packed GenericCommand|string
     */
    public function handleGenericCommand($connection,$packed){
        //如果是字符串
        if(is_string($packed)) {
            $genericCmd = new GenericCommand();
            try {
                $genericCmd->parseFromString($packed);
            } catch (\Exception $e) {
                //die('Parse error: ' . $e->getMessage());
                echo 'Parse error: ' . $e->getMessage();
                var_dump(base64_encode($packed));
                return;
            }
        }
        else{
            $genericCmd = $packed;
        }
        $appId = $genericCmd->getAppId();
        $cmd = $genericCmd->getCmd();
        if(
            //只有cmd 为非14记录
            $cmd == 14
            //查询人数的不要记 1 , 43
            || ($cmd == CommandType::conv && $genericCmd->getOp() == OpType::count)
            || !IM_DEBUG
        ){

        }
        //以下要记录日志
        else {
                echo "connection id[" . $connection->id . "]:in:";
                ob_start();
                $genericCmd->dump();
                $in_str = ob_get_clean();
                //log_write($in_str);
                echo $in_str . "\r\n";
        }

        $this->connection = $connection;
        if(!is_int($cmd)){
            echo 'CMD is empty';
            return;
        }
        switch($cmd){
            // rcp 保持心跳？
            case 14:
                $this->send($genericCmd);
                break;
            //session 0 预处理
            case CommandType::session:
                $this->cmdSession($genericCmd);
                break;
            case CommandType::direct: //2
                //$this->pushServerQueue($genericCmd,RedisService::SERVER_QUEUE_DIRECT);
                $this->handleCmd($genericCmd);
                break;
            // 收到响应
            case CommandType::ack: //3
                $this->pushServerQueue($genericCmd,RedisService::SERVER_QUEUE_ACK);
                break;
             //对话操作 1
            case CommandType::conv: //1
                $this->cmdConv($genericCmd);
                break;
             //聊天消息
            //已读
            case CommandType::read: // 11
            //记录
            case CommandType::logs: // 6
                //提交到redis中进行处理
                $this->pushServerQueue($genericCmd);
                break;
            default:
                echo "unknow cmd:".$cmd."\r\n";
        }
    }

    /**
     * @param $data GenericCommand
     * @param null|RealtimeConnection $connection
     */
    public function send($data,$connection = null){
        $session = array();
        if(empty($connection)){
            $connection = & $this->connection;
            $session = $_SESSION;
        }
        if($data->getCmd() != 14) {
            echo 'sendto id:' . $connection->peerId . '=>' . $connection->id . "\r\n";
        }
        Gateway::sendToClient($connection->id,$this->encodeResp($data,$session));
    }

    /**
     * 提交到redis中处理数据
     * @param $data
     * @param string $queue
     */
    public function pushServerQueue($data,$queue = ''){
        echo 'pushServerQueue:'."\r\n";
        $data = $this->encodeResp($data);
        //todo debug
        if(defined('ENV_NOREDIS')) {
            RealtimeService::handleMessage($data);
            return;
        }// end todo
        $redisService = RedisService::getInstance();
        if($queue){
            $redisService->pushQueue($queue,$data);
        }else {
            $redisService->pushServerQueue($data);
        }
    }

    /**
     * @param $data GenericCommand
     */
    public function handleCmd($data){
        $data = $this->encodeResp($data);
        RealtimeService::handleMessage($data);
    }
    /**
     * @param $genericCmd \GenericCommand
     */
    public function cmdSession($genericCmd){
        //todo 原来如果没有peerId,取 connection中的peerId
        $peerId = $genericCmd->getPeerId()
        //or (!empty($this->connection->peerId) && $peerId = $this->connection->peerId)
        or $peerId = "guest_" . md5(time() . mt_rand(1, 10));
        $genericCmd->setPeerId($peerId);
        //如果是open的话,才进行register操作
        if($genericCmd->getOp() == OpType::open) {
            $this->register($genericCmd, $this->connection);
        }
        //$this->handleCmd($genericCmd);
        $this->pushServerQueue($genericCmd);
    }
    /**
     * @param $genericCmd \GenericCommand
     */
    public function cmdConv($genericCmd){
        $this->handleCmd($genericCmd);
    }

    /**
     * @param $connection RealtimeConnection
     * @param $genericCmd GenericCommand
     */
    public function register($genericCmd,$connection){
        $peerId = $genericCmd->getPeerId();
        $sessionMessage = $genericCmd->getSessionMessage();
        $tag = $sessionMessage->getTag();
        RealtimeGatewayClients::register($peerId,$connection,$tag,$sessionMessage->getUa());
        //处理 tag 冲突的connections
        $conflict = RealtimeGatewayClients::getConflictConnection($peerId,$connection,$tag);
        if($conflict){
            echo colorize('CONFLICT:'.$peerId,'WARNING')."\r\n";
            $resp = new \GenericCommand();
            $resp->setCmd(CommandType::session);
            $resp->setOp(OpType::closed);
            $resp->setAppId($genericCmd->getAppId());
            $resp->setPeerId($peerId);
            $sessMsg = new SessionCommand();
            $sessMsg->setCode(4111);// session token
            $sessMsg->setReason('SESSION_CONFLICT');//session token ttl
            $resp->setSessionMessage($sessMsg);
            RealtimeGatewayClients::sendByClients($conflict,$this->encodeResp($resp));
        }
    }

    /**
     * @param $result string
     * @return bool|void
     */
    static function handleClientQueue($result){
        if(!$result){
            return false;
        }
        $genericCmd  = new GenericCommand();
        try {
            $genericCmd->parseFromString($result);
        } catch (\Exception $e) {
            echo 'Parse error: ' . $e->getMessage();
            var_dump(base64_encode($result));
            return false;
        }
        $peerId = $genericCmd->getPeerId();
        //发送到相应的client
        $genericCmd->dump();
        RealtimeGatewayClients::sendByUser($peerId,$result);
        return true;
    }

    /**
     * 处理广播消息
     * @param $msg String
     */
    static function handleBroadMessage($msg){
        if(!$msg){
            return false;
        }
        $genericCmd  = new GenericCommand();
        try {
            $genericCmd->parseFromString($msg);
        } catch (\Exception $e) {
            echo 'Parse error: ' . $e->getMessage();
            var_dump(base64_encode($msg));
            return false;
        }
        //发送到相应的client
        $genericCmd->dump();
        RealtimeGatewayClients::sendBroadcast($msg);
        return true;
    }

    /**
     * 处理组消息
     * @param $msg String
     *
     */
    static function handleGroupMessage($msg){
        if(!$msg){
            return false;
        }
        $genericCmd  = new GenericCommand();
        try {
            $genericCmd->parseFromString($msg);
        } catch (\Exception $e) {
            echo 'Parse error: ' . $e->getMessage();
            var_dump(base64_encode($msg));
            return false;
        }
        //发送到相应的client
        $group_id = $genericCmd->getPeerId();//这儿把peerId转换成组id
        RealtimeGatewayClients::sendGroup($group_id,$msg);
    }
    /**
     * 处理命令
     * @param $msg String
     *
     */
    static function handleCmdMessage($message){
        $message = json_decode($message,true);
        $data = $message['data'];
        $cmd = $message['cmd'];
        switch($cmd){
            case 'transient_group/onlines':
                $gid = $data['gid'];
                $result = Gateway::getClientCountByGroup($gid);
                $result = json_encode(array(
                    'result' => $result
                ));
                break;
            //查询在线人数
            case 'online':
                $peers = $data['peers'];
                $result = array_filter($peers,function($v){
                    return Gateway::isUidOnline($v);
                });
                $result = json_encode(array(
                    'result' => $result
                ));
                break;
            //踢人
            case 'online/kick':
                $uid = $data['client_id'];
                $reason = $data['reason'];
                $clients = Gateway::getClientIdByUid($uid);
                foreach($clients as $client_id) {
                    Gateway::closeClient($client_id);
                }
                $result = '{}';
                break;
            default:
                $result = '';
        }
        return $result;
    }

    /**
     * @param $client_id
     * @return RealtimeConnection|null
     */
    static function getConnection($client_id){
        //如果当前client_id = 全局的 client_id
        if($client_id == $_SERVER['GATEWAY_CLIENT_ID']){
            $session = $_SESSION;
        }
        else {
            try {
                $session = Gateway::getSession($client_id) or $session = array();
            }catch (\Exception $e){
                $session = array();
            }
        }
        if(!$session){
            return null;
        }
        $connection = new RealtimeConnection();
        $connection->session = $session;
        $connection->id = $client_id;
        $connection->peerId = $session['peerId'];
        $connection->SecWebSocketProtocol = $session['SecWebSocketProtocol'];
        return $connection;
    }

    //清理clients
    static function clearClients(){
        RealtimeGatewayClients::clearClients();
    }
}
class RealtimeConnection{
    public $session;
    public $id;
    public $peerId;
    public $SecWebSocketProtocol;
}



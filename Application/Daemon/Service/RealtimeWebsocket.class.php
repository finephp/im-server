<?php
namespace Daemon\Service;
use \CommandType;
use \GenericCommand;
use OpType;
use SessionCommand;
use Workerman\Connection\TcpConnection;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');

//for win
//require_once (APP_PATH.'Common/Vendor/protocolbuf/message/pb_message.php');
//require_once (dirname(APP_PATH).'/proto/pb_proto_message.php');
//for end
class RealtimeWebsocket {
    /**
     * @var $connection TcpConnection
     */
    public $connection;
    public $data;
    public $noBinary;
    static $connections = array();
    static $send;
    static function handleMessage($connection,$data,$ws_worker){
        $_SESSION['connection'] = $connection;
        $noBinary = isset($connection->SecWebSocketProtocol) && $connection->SecWebSocketProtocol=='lc.protobase64.3';
        var_dump($connection->SecWebSocketProtocol);
        if($noBinary){
            $packed = base64_decode($data);
        }
        else{
            //设置为 BINARY_TYPE_ARRAYBUFFER 格式
            $packed = $data;
            $connection->websocketType = \Workerman\Protocols\Websocket::BINARY_TYPE_ARRAYBUFFER;
        }
        $service = new self();
        $service->noBinary = $noBinary;
        $service->handleGenericCommand($connection,$packed);
    }

    /**
     * @param $connection TcpConnection
     */
    static function handleClose($connection){
        //todo debug 打出当前链接的总数
        echo __METHOD__.":count_connections:";
        var_dump(count($connection->worker->connections));
        RealtimeClients::unregister($connection);
    }

    public function encodeResp($resp){
        $new_resp = $resp->serializeToString();
        //这个地方要注意 todo 可能在web版中会有问题
        //$new_resp .= pack('H*','EA0600');
        /*
        if($this->noBinary) {
            $new_resp = base64_encode($new_resp);
        }
        */
        $connection = $_SESSION['connection'];
        ob_start();
        echo "push: connection id:".$connection->id.':';
        $resp->dump();
        $respstr = ob_get_clean();
        log_write($respstr);
        echo $respstr;
        return $new_resp;
    }

    /**
     * @param $connection TcpConnection
     * @param $packed
     */
    public function handleGenericCommand($connection,$packed){
        $genericCmd  = new GenericCommand();
        try {
            $genericCmd->parseFromString($packed);
        } catch (\Exception $e) {
            //die('Parse error: ' . $e->getMessage());
            echo 'Parse error: ' . $e->getMessage();
            var_dump(base64_encode($packed));
            return;
        }
        ob_start();
        echo "connection id[".$connection->id."]:in:";
        $genericCmd->dump();
        $in_str = ob_get_clean();
        log_write($in_str);
        echo $in_str."\r\n";
        $appId = $genericCmd->getAppId();
        $cmd = $genericCmd->getCmd();
        $other = array(
            'ip'=> $connection->getRemoteIp()
        );
        $this->connection = $connection;
        switch($cmd){
            // rcp 保持心跳？
            case 14:
                $this->send($genericCmd);
                break;
            //session 0 预处理
            case CommandType::session:
                $this->cmdSession($genericCmd);
                break;
             //对话操作 1
            case CommandType::conv: //1
             //聊天消息
            case CommandType::direct: //2
            //已读
            case CommandType::read: // 11
            //记录
            case CommandType::logs: // 6
            // 收到响应
            case CommandType::ack: //3
                //提交到redis中进行处理
                $this->pushServerQueue($genericCmd);
                break;
            default:
                echo "unknow cmd:".$cmd."\r\n";
        }
    }

    /**
     * @param $data
     * @param null|TcpConnection $connection
     */
    public function send($data,$connection = null){
        if($connection){
            $connection->send($this->encodeResp($data));
        }
        else {
            $this->connection->send($this->encodeResp($data));
        }
    }

    public function pushServerQueue($data){
        $data = $this->encodeResp($data);
        $redisService = RedisService::getInstance();
        $redisService->pushServerQueue($data);
    }
    /**
     * @param $genericCmd \GenericCommand
     */
    public function cmdSession($genericCmd){
        //$peerId = $genericCmd->getPeerId() or $peerId = "guest_" . md5(time() . mt_rand(1, 10));
        //todo 原来如果没有peerId,取 connection中的peerId
        $peerId = $genericCmd->getPeerId()
        //or (!empty($this->connection->peerId) && $peerId = $this->connection->peerId)
        or $peerId = "guest_" . md5(time() . mt_rand(1, 10));
        $genericCmd->setPeerId($peerId);
        //如果是open的话,才进行register操作
        if($genericCmd->getOp() == OpType::open) {
            $this->register($genericCmd, $this->connection);
        }
        $this->pushServerQueue($genericCmd);
    }

    /**
     * @param $connection \Workerman\Connection\TcpConnection
     * @param $genericCmd GenericCommand
     */
    public function register($genericCmd,$connection){
        $peerId = $genericCmd->getPeerId();
        $sessionMessage = $genericCmd->getSessionMessage();
        $tag = $sessionMessage->getTag();
        RealtimeClients::register($peerId,$connection,$tag);
        //处理 tag 冲突的connections
        $conflict = RealtimeClients::getConflictConnection($peerId,$connection,$tag);
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
            RealtimeClients::sendByClients($conflict,$this->encodeResp($resp));
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
        RealtimeClients::sendByUser($peerId,$result);
        return true;
    }
}


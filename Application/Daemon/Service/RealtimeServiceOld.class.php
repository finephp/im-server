<?php
namespace Daemon\Service;
use \CommandType;
use \GenericCommand;
use Think\Controller;
use Think\Exception;
use Workerman\Connection\TcpConnection;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');

//for win
//require_once (APP_PATH.'Common/Vendor/protocolbuf/message/pb_message.php');
//require_once (dirname(APP_PATH).'/proto/pb_proto_message.php');
//for end
class RealtimeServiceOld extends Controller {
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
        $service->connection = $connection;
        self::$send = function($client_ids,$msg)use($service,$connection){
            if($client_ids){
                $service->send($connection,$msg);
                return;
            }
            foreach(self::$connections as $conn){
                $service->send($conn,$msg);
            }
        };
        $service->handleGenericCommand($connection,$packed);
    }

    public function encodeResp($resp){
        $new_resp = $resp->serializeToString();
        //这个地方要注意 todo 可能在web版中会有问题
        //$new_resp .= pack('H*','EA0600');
        if($this->noBinary) {
            $new_resp = base64_encode($new_resp);
        }
        $connection = $_SESSION['connection'];
        ob_start();
        echo "out: connection id:".$connection->id.':';
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
        } catch (Exception $e) {
            //die('Parse error: ' . $e->getMessage());
            echo 'Parse error: ' . $e->getMessage();
            var_dump(base64_encode($packed));
            return;
        }
        ob_start();
        echo "in:";
        $genericCmd->dump();
        $in_str = ob_get_clean();
        log_write($in_str);
        echo $in_str."\r\n";
        $appId = $genericCmd->getAppId();
        $fromPearId = $genericCmd->getPeerId() or $fromPearId = "guest_".md5(time().mt_rand(1,10));
        $genericCmd->setPeerId($fromPearId);
        $this->register($genericCmd,$connection);
        $cmd = $genericCmd->getCmd();
        $other = array(
            'ip'=> $connection->getRemoteIp()
        );
        switch($cmd){
            //session 0
            case CommandType::session:
                $this->send($connection,$this->cmdSession($connection->id,$genericCmd));
                break;
            //对话操作 1
            case CommandType::conv:
                $this->send($connection,$this->cmdConv($connection->id,$genericCmd));
                break;
            //收到对话 2
            case CommandType::direct: //2
                $this->send($connection,CmdDirect::exeCmd($connection->id,$genericCmd,$other));
                break;
            //记录
            case CommandType::logs: // 6
                $this->send($connection,CmdLogs::exeCmd($connection->id,$genericCmd));
                break;
            //已读
            case CommandType::read: // 11
                echo 'todo Cmd:read:'.$cmd;
                break;
            // 10.	rcp 保持心跳？
            case 14:
                $this->send($connection,$genericCmd);
                break;
            // 收到响应
            case 3:
                $this->send($connection,CmdAck::exeCmd($connection->id,$genericCmd));
                break;
            default:
                echo 'undefined Cmd:'.$cmd;
                break;
        }
    }

    /**
     * @param $client_id
     * @param $genericCmd \GenericCommand
     * @return bool
     */
    public function cmdSession($client_id,$genericCmd){
        return CmdSession::exeCmd($client_id,$genericCmd);
    }

    /**
     * @param $client_id
     * @param $genericCmd \GenericCommand
     * @return bool
     */
    public function cmdConv($client_id,$genericCmd){
        return CmdConv::exeCmd($client_id,$genericCmd);
    }

    /**
     * @param $connection
     * @param $msg
     */
    public function send($connection,$msg){
        if($msg) {
            $connection->send($this->encodeResp($msg));
        }
    }
    /**
     * @param $client_id
     * @param $msg
     */
    static function sendById($client_id, $msg){
        call_user_func(self::$send,$client_id,$msg);
    }

    /**
     * @param $connection \Workerman\Connection\TcpConnection
     * @param $genericCmd GenericCommand
     */
    public function register($genericCmd,$connection){
        $peerId = $genericCmd->getPeerId();
        RealtimeClients::register($peerId,$connection);
    }
}


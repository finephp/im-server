<?php
namespace Daemon\Service;
use \CommandType;
use \GenericCommand;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');

//for win
//require_once (APP_PATH.'Common/Vendor/protocolbuf/message/pb_message.php');
//require_once (dirname(APP_PATH).'/proto/pb_proto_message.php');
//for end
class RealtimeService{
    public $noBinary = false;
    static function handleMessage($data){
        $service = new self();
        $service->handleGenericCommand($data);
        unset($service);
    }

    public function encodeResp($resp){
        $new_resp = $resp->serializeToString();
        //这个地方要注意 todo 可能在web版中会有问题
        //$new_resp .= pack('H*','EA0600');
        if($this->noBinary) {
            $new_resp = base64_encode($new_resp);
        }
        /*
        $connection = $_SESSION['connection'];
        ob_start();
        echo "out: connection id:".$connection->id.':';
        $resp->dump();
        $respstr = ob_get_clean();
        log_write($respstr);
        echo $respstr;
        */
        return $new_resp;
    }

    /**
     * @param $packed
     */
    public function handleGenericCommand($packed){
        G(__METHOD__.'START');
        $genericCmd  = new GenericCommand();
        try {
            $genericCmd->parseFromString($packed);
        } catch (\Exception $e) {
            //die('Parse error: ' . $e->getMessage());
            echo 'Parse error: ' . $e->getMessage();
            var_dump(base64_encode($packed));
            return;
        }
        /*
        ob_start();
        echo __METHOD__." in:";
        $genericCmd->dump();
        $in_str = ob_get_clean();
        log_write($in_str);
        echo $in_str."\r\n";
        */
        $appId = $genericCmd->getAppId();
        $cmd = $genericCmd->getCmd();
        switch($cmd){
            //session 0
            case CommandType::session:
                $this->cmdSession($genericCmd);
                break;
            //对话操作 1
            case CommandType::conv:
                $this->cmdConv($genericCmd);
                break;
            //收到对话 2
            case CommandType::direct: //2
                CmdDirect::exeCmd($genericCmd);
                break;
            //记录
            case CommandType::logs: // 6
                CmdLogs::exeCmd($genericCmd);
                break;
            //已读
            case CommandType::read: // 11
                CmdRead::exeCmd($genericCmd);
                break;
            // 10.	rcp 保持心跳？
            case 14:
                echo 'not todo Cmd:ping:'.$cmd ."\r\n";
                break;
            // 收到响应
            case 3:
                CmdAck::exeCmd($genericCmd);
                break;
            default:
                echo 'undefined Cmd:'.$cmd ."\r\n";
                break;
        }
        G(__METHOD__.'END');
        $runtime = G(__METHOD__.'START',__METHOD__.'END');
        if($runtime>0.1){
            echo colorize('CMD:'.$cmd.' op:'.$genericCmd->getOp().' runtime time long :'.$runtime ,'WARNING')." \r\n";
        }
        else{
            echo colorize('CMD:'.$cmd.' op:'.$genericCmd->getOp().' runtime:'.$runtime,'SUCCESS')."\r\n";
        }
        if($runtime>=0.1){
            log_write('CMD:'.$cmd.' op:'.$genericCmd->getOp().' runtime time long :'.$runtime,'LONG_TIME');
        }
        if($cmd == 2){
            log_write('CMD:'.$cmd.' op:'.$genericCmd->getOp().' runtime:'.$runtime);
        }
    }
    /**
     * @param $genericCmd \GenericCommand
     * @return bool
     */
    public function cmdSession($genericCmd){
        return CmdSession::exeCmd($genericCmd);
    }
    /**
     * @param $genericCmd \GenericCommand
     * @return bool
     */
    public function cmdConv($genericCmd){
        return CmdConv::exeCmd($genericCmd);
    }
}


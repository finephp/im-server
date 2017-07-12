<?php
namespace Daemon\Controller;
use Daemon\Service\RealtimeGateway;
use Daemon\Service\RedisService;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Lib\Context;
use GatewayWorker\Register;
use Think\Controller;
use Workerman\Connection\TcpConnection;
use Workerman\WebServer;
use Workerman\Worker;
use GatewayWorker\Gateway;
use Workerman\Autoloader;
require_once WORKERMAN_PATH;
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
if(!IS_CLI){
    die('NOT CLI');
}
//gater way
class GatewayController extends Controller {
    //初始化
    public function _initialize(){
        //重置avgv 这个很重要，只能运行一次
        unset($_SERVER['argv'][1]);
        global $argv;
        $argv = array_values($_SERVER['argv']);
        Worker::$pidFile = '/tmp/'.ACTION_NAME.'.gateway.pid';
    }
    public function index(){
        $this->show(__METHOD__);
        echo 'run worker';
        echo var_dump(LOG_PATH);
        echo var_dump(C('LOG_PATH'));
    }
    public function testWorker(){
        // 标记是全局启动
        define('GLOBAL_START', 1);
        ob_end_flush();

        // 运行所有服务
        Worker::$pidFile = '/tmp/testWorker.gateway.pid';
        $worker = new Worker();
        $worker->name = 'testWorker';
        $worker->onWorkerStart = function() {
            echo __METHOD__.'worker start';
        };
        Worker::runAll();
    }
    public function worker(){
        // 标记是全局启动
        define('GLOBAL_START', 1);
        ob_end_flush();
        //开启noredis模式
        if(getenv('ENV_NOREDIS') == 'true'){
            define('ENV_NOREDIS',true);
            echo "ENV_NOREDIS:true"."\r\n";
        }
        //运行注册中心
        if(getenv('GATEWAY_REGISTER_URL') == ''){
            $this->registerWorker();
        }
        $this->gatewayWorker();
        $this->businessWorker();
        $this->clientQueueWorker();
        // 运行所有服务
        Worker::$pidFile = '/tmp/'.APP_NAME.'.gateway.pid';
        echo Worker::$pidFile."\r\n";
        Worker::runAll();
    }

    /**
     * gateway 服务
     */
    public function gatewayWorker(){
        Autoloader::setRootPath(__DIR__);
        // gateway 进程
        $gateway = new Gateway("Websocket://0.0.0.0:8585");
        // 设置名称，方便status时查看
        $gateway->name = 'gatewayWorker';
        // 设置进程数，gateway进程数建议与cpu核数相同
        $gateway->count = C('APP_WORKER_COUNT');
        // 分布式部署时请设置成内网ip（非127.0.0.1）
        $gateway->lanIp = C('GATEWAY_LANIP');
        $gateway->lanIpInner = C('GATEWAY_LANIP_INNER');
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort = C('GATEWAY_START_PORT');
        // 心跳间隔
        $gateway->pingInterval = 10;
        // 心跳数据
        $gateway->pingData = '';//base64_decode('CA4=');
        // 服务注册地址
        $gateway->registerAddress = C('GATEWAY_REGISTER_URL');

        /**
         * @param $connection TcpConnection
         */
        $gateway->onBeforeClientMessage = function($connection){
            $noBinary = empty($connection->SecWebSocketProtocol) || $connection->SecWebSocketProtocol=='lc.protobase64.3';
            if(!$noBinary) {
                $connection->websocketType = \Workerman\Protocols\Websocket::BINARY_TYPE_ARRAYBUFFER;
            }
            if(empty($connection->session)){
                $session = array();
            }
            else{
                $session = Context::sessionDecode($connection->session);
            }
            $session['SecWebSocketProtocol'] = $connection->SecWebSocketProtocol;
            $connection->session =  Context::sessionEncode($session);
        };
//        $gateway->onCmdSendToOne = function($connection,$body,$raw = false){
//            if(empty($connection->session)){
//                $session = array();
//            }
//            else{
//                $session = Context::sessionDecode($connection->session);
//            }
////            print_r('onCmdSendToOne:start ');
////            print_r($session);
////            print_r('onCmdSendToOne:end ');
//            //如果是js2的sdk，要把body转换成json
//            if(strpos($session['ua'],'js/2.')===0){
//                print_r($session['ua']);
//            }
//        };
        /**
         *  修改群发到组中的时候，需要对每个数据的peerId进行变更
         * @param $connection TcpConnection
         * @param $body
         * @param bool $raw
         */
        $gateway->onGroupConnectionSend = function($connection,$body,$raw = false){
            if(empty($connection->session)){
                $session = array();
            }
            else{
                $session = Context::sessionDecode($connection->session);
            }
            /*print_r('onGroupConnectionSend:start ');
            print_r($session);
            print_r('onGroupConnectionSend:end ');*/
            //修改peerId
            if($session && $session['peerId']) {
                $msg = new \GenericCommand();
                $msg->parseFromString($body);
                $msg->setPeerId($session['peerId']);
                //$msg->dump();
                $body = RealtimeGateway::encodeResp($msg,$session);
            }
            $connection->send($body,$raw);
            return true;
        };
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }

    /**
     * register 服务 此服务只能有一个进程，分布式全靠这个服务了
     */
    public function registerWorker(){
        $register = new Register('text://0.0.0.0:1236');
        $register->name = 'registerWorker';
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }

    /**
     * todo business 服务
     */
    public function businessWorker(){
        Autoloader::setRootPath(__DIR__);
        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        $worker->name = 'businessWorker';
        // bussinessWorker进程数量
        $worker->count = C('APP_WORKER_COUNT');
        // 服务注册地址
        $worker->registerAddress = C('GATEWAY_REGISTER_URL');

        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }

    public function web(){
        Autoloader::setRootPath(__DIR__);
        // WebServer
        $web = new WebServer("http://0.0.0.0:55151");
        // WebServer进程数量
        $web->count = 2;
        // 设置站点根目录
        $web->addRoot('www.your_domain.com', __DIR__.'/Web');
    }


    /**
     * 取发送到客户端的数据队列 worker
     */
    public function clientQueueWorker(){
        $worker = new Worker();
        $worker->count = C('APP_WORKER_COUNT');
        $worker->name = 'clientQueueWorker';
        $worker->onWorkerStart = function(){
            echo "clientQueueWorker\r\n";
            $i = 0;
            // 建立socket连接到内部推送端口
            $client = stream_socket_client('tcp://'.C('RTM_SOCKET_URL'), $errno, $errmsg, 1);
            //订阅客户端推送事件 ，进程挂住了
            $this->clientQueueScribe(function($instance,$channel_name,$message)use(&$i,& $client){
                //收到消息后，发送内部消息
                // 发送数据，这儿用的是 frameEncode 协议发送二进制数据
                $result = fwrite($client, $this->frameEncode($message));
                //如果失败，重试一次
                if($result === false){
                    log_write('reconnect:','clientQueueWorker');
                    $client = stream_socket_client('tcp://'.C('RTM_SOCKET_URL'), $errno, $errmsg, 1);
                    $result = fwrite($client, $this->frameEncode($message)); //重新再试一次
                    if($result === false){
                        log_write('resend error:','clientQueueWorker');
                    }
                    $i = 0;
                }
                if($i>1000){
                    $i = 0;//重新记数
                }
            });
        };
    }

    public function flashPolicyWorker(){
        $flash_policy = new Worker('tcp://0.0.0.0:843');
        $flash_policy->name = 'flashPolicyWorker';
        /* @param $connection TcpConnection */
        $flash_policy->onMessage = function($connection, $message)
        {
            $connection->send('<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>'."\0");
            $connection->close();
        };
        if(!defined('GLOBAL_START'))
        {
            Worker::$pidFile = '/tmp/flashPolicyWorker.gateway.pid';
            Worker::runAll();
        }
    }

    /**
     * 订阅客户端事件
     * @param $callback callable
     */
    public function clientQueueScribe($callback){
        RedisService::getInstance()->getClientQueue($callback);
    }

    /**
     * @param $buffer string
     *  使用 frame 协议的数据格式
     * @return string
     */
    protected function frameDecode($buffer)
    {
        return substr($buffer, 4);
    }
    /**
     * @param $buffer string
     *  使用 frame 协议的数据格式
     * @return string
     */
    protected function frameEncode($buffer){
        $total_length = 4 + strlen($buffer);
        return pack('N',$total_length) . $buffer;
    }

}
<?php
namespace Daemon\Controller;
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
if(!IS_CLI){
    die('NOT CLI');
}
//gater way
class GatewayController extends Controller {
    public function index(){
        echo 'run worker';
        echo var_dump(LOG_PATH);
        echo var_dump(C('LOG_PATH'));
    }
    public function worker(){
        // 标记是全局启动
        define('GLOBAL_START', 1);
        ob_end_flush();
        //重置avgv
        unset($_SERVER['argv'][1]);
        global $argv;
        $argv = array_values($_SERVER['argv']);
        $this->gatewayWorker();
        $this->registerWorker();
        $this->businessWorker();
        $this->clientQueueWorker();
        // 运行所有服务
        Worker::$pidFile = '/tmp/'.APP_NAME.'.gateway.pid';
        echo Worker::$pidFile."\r\n";
        Worker::runAll();
    }

    /**
     * todo gateway 服务
     */
    public function gatewayWorker(){
        Autoloader::setRootPath(__DIR__);
        // gateway 进程
        $gateway = new Gateway("Websocket://0.0.0.0:8585");
        // 设置名称，方便status时查看
        $gateway->name = 'ChatGateway';
        // 设置进程数，gateway进程数建议与cpu核数相同
        $gateway->count = 4;
        // 分布式部署时请设置成内网ip（非127.0.0.1）
        $gateway->lanIp = '127.0.0.1';
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort = 2300;
        // 心跳间隔
        $gateway->pingInterval = 10;
        // 心跳数据
        $gateway->pingData = '';//base64_decode('CA4=');
        // 服务注册地址
        $gateway->registerAddress = '127.0.0.1:1236';

        /**
         * @param $connection TcpConnection
         */
        $gateway->onBeforeClientMessage = function($connection){
            $noBinary = isset($connection->SecWebSocketProtocol) && $connection->SecWebSocketProtocol=='lc.protobase64.3';
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

        $gateway->onConnect = function($connection){
            var_dump('GateWay:onConnect:'.$connection->SecWebSocketProtocol);
        };
        if(!defined('GLOBAL_START'))
        {
            Worker::runAll();
        }
    }

    /**
     * todo register 服务
     */
    public function registerWorker(){
        $register = new Register('text://0.0.0.0:1236');
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
        $worker->name = 'ChatBusinessWorker';
        // bussinessWorker进程数量
        $worker->count = 4;
        // 服务注册地址
        $worker->registerAddress = '127.0.0.1:1236';

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
        $worker->count = 4;
        $worker->name = 'clientQueueWorker';
        $worker->onWorkerStart = function(){
            echo "clientQueueWorker\r\n";
            $i = 0;
            // 建立socket连接到内部推送端口
            $client = stream_socket_client('tcp://127.0.0.1:2208', $errno, $errmsg, 1);
            //订阅客户端推送事件 ，进程挂住了
            $this->clientQueueScribe(function($instance,$channel_name,$message)use(&$i,& $client){
                //收到消息后，发送内部消息
                // 发送数据，这儿用的是 frameEncode 协议发送二进制数据
                $result = fwrite($client, $this->frameEncode($message));
                //如果失败，重试一次
                if($result === false){
                    log_write('reconnect:','clientQueueWorker');
                    $client = stream_socket_client('tcp://127.0.0.1:2208', $errno, $errmsg, 1);
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
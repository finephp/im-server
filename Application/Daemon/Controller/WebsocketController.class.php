<?php
namespace Daemon\Controller;
use Daemon\Service\RealtimeWebsocket;
use Daemon\Service\RedisService;
use Think\Controller;
use Workerman\Connection\TcpConnection;
use workerman\Worker;
require_once WORKERMAN_PATH;
require_once APP_PATH.'Common/Vendor/Channel/src/Server.php';
require_once APP_PATH.'Common/Vendor/Channel/src/Client.php';
if(!IS_CLI){
    die('NOT CLI');
}
class WebsocketController extends Controller {
    public function index(){
        echo 'run worker';
        echo var_dump(LOG_PATH);
        echo var_dump(C('LOG_PATH'));
    }
    public function worker(){
        ob_end_flush();
        //重置avgv
        unset($_SERVER['argv'][1]);
        global $argv;
        $argv = array_values($_SERVER['argv']);

        Worker::$pidFile = '/tmp/'.APP_NAME.'.workerman.pid';
        echo Worker::$pidFile."\r\n";

        // 初始化一个Channel服务端 多进程数据共享
        $channel_server = new \Channel\Server('0.0.0.0', 2206);
        // 创建一个Worker监听8000端口，使用websocket协议通讯
        $ws_worker = new Worker("websocket://0.0.0.0:8585");
        $ws_worker->name = APP_NAME.'Worker';
        // 启动4个进程对外提供服务
        $ws_worker->count = 1;
        $ws_worker->onMessage = function($connection, $data)use($ws_worker)
        {
            $this->onMessage($connection,$data,$ws_worker);
        };

        $ws_worker->onClose = function($connection){
            RealtimeWebsocket::handleClose($connection);
        };

        /**
         * @param $connection TcpConnection
         */
        $ws_worker->onConnect = function($connection)
        {
            echo "new connection from ip " . $connection->getRemoteIp() . "\n";
        };

        /**
         * @param $worker Worker
         */
        $ws_worker->onWorkerStart = function($worker)
        {
            \Channel\Client::connect('127.0.0.1', 2206);
            // 订阅广播事件
            $event_name = 'genericCmd';
            // 收到广播事件后向当前进程内所有客户端连接发送广播数据
            \Channel\Client::on($event_name, function($event_data)use($worker){
                echo "__________on:genericCmd:";
                $message = $event_data['content'];
                RealtimeWebsocket::handleClientQueue($message);
            });
            //只有worker_id=1的才启动内部推送系统
            if($worker->id !== 0){
                return;
            }
            //再定义一个内部消息通知接口
            // 开启一个内部端口，方便内部系统推送数据，frame 协议格式
            $inner_text_worker = new Worker('frame://0.0.0.0:2207');
            $inner_text_worker->count = 4;
            $inner_text_worker->onMessage = function($connection, $message)
            {
                \Channel\Client::publish('genericCmd', array(
                    'content' => $message
                ));
            };
            // ## 执行监听 ##
            $inner_text_worker->listen();
            // end 内部通知接口
        };
        //开始收队列
        $this->clientQueueWorker();
        Worker::runAll();
    }

    /**
     * 当触发消息时分派消息
     * @param $connection \Workerman\Connection\TcpConnection
     * @param $data
     */
    protected function onMessage($connection,$data,$ws_worker){
        RealtimeWebsocket::handleMessage($connection,$data,$ws_worker);
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
            $client = stream_socket_client('tcp://127.0.0.1:2207', $errno, $errmsg, 1);
            //订阅客户端推送事件 ，进程挂住了
            $this->clientQueueScribe(function($instance,$channel_name,$message)use(&$i,& $client){
                //收到消息后，发送内部消息
                // 发送数据，这儿用的是 frameEncode 协议发送二进制数据
                $result = fwrite($client, $this->frameEncode($message));
                //如果失败，重试一次
                if($result === false){
                    log_write('reconnect:','clientQueueWorker');
                    $client = stream_socket_client('tcp://127.0.0.1:2207', $errno, $errmsg, 1);
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
        RedisService::getInstance()->subClientQueue($callback);
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
<?php
namespace Daemon\Controller;
use Daemon\Service\RealtimeService;
use Daemon\Service\RedisService;
use Think\Controller;
use Workerman\Lib\Timer;
use workerman\Worker;
require_once WORKERMAN_PATH;
if(!IS_CLI){
    die('NOT CLI');
}
class RedisController extends Controller {
    public function index(){
        echo 'run worker';
    }
    public function worker(){
        ob_end_flush();
        //重置avgv
        unset($_SERVER['argv'][1]);
        global $argv;
        $argv = array_values($_SERVER['argv']);
        define('WORKER_RUN_ALL',true);
        Worker::$pidFile = '/tmp/'.APP_NAME.'.ReadQueueWorker.workerman.pid';
        echo Worker::$pidFile."\r\n";
        $this->readQueueWorker();
        Worker::runAll();
    }
    //读取队列
    public function readQueueWorker(){
        // 创建一个Worker监听8000端口，使用websocket协议通讯
        $ws_worker = new Worker();
        $ws_worker->name = APP_NAME.'.ReadQueueWorker';
        // 启动4个进程对外提供服务
        $ws_worker->count = 4;
        $ws_worker->onWorkerStart = function($task)
        {
            $redisService = RedisService::getInstance();
            while(true){
                $result = $redisService->getServerQueue();
                if($result){
                    echo " serverQueue:";
                    //开始处理redis中的数据
                    try {
                        RealtimeService::handleMessage($result);
                    }
                    catch (\Exception $e){
                        echo colorize('readQueueWorker:'.$e->getMessage(),'FAILURE');
                        sleep(1);
                    }
                }
            }
        };
        if(!defined('WORKER_RUN_ALL')){
            Worker::runAll();
        }
    }
}
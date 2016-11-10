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
        $this->readQueueWorker(RedisService::SERVER_QUEUE_DIRECT);
        $this->readQueueWorker();
        Worker::runAll();
    }
    //读取队列
    public function readQueueWorker($queue=''){
        $ws_worker = new Worker();
        $ws_worker->name = APP_NAME.'.ReadQueueWorker:'.$queue;
        // 启动4个进程对外提供服务
        $ws_worker->count = 4;
        $ws_worker->onWorkerStart = function($task)use($queue)
        {
            $redisService = RedisService::getInstance();
            while(true){
                if($queue){
                    $result = $redisService->getQueue($queue);
                }
                else {
                    $result = $redisService->getServerQueue();
                }
                if($result){
                    echo " serverQueue:";
                    //开始处理redis中的数据
                    try {
                        //计算时间
                        G('serverQueue_start');
                        RealtimeService::handleMessage($result);
                        G('serverQueue_end');
                        echo colorize('runtime:'.G('serverQueue_start','serverQueue_end'),'SUCCESS')."\r\n";
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
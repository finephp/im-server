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
class RedisService{
    private static $_instance;
    /**
     * @var \Redis
     */
    public $redis;
    const SERVER_QUEUE = 'server_queue';
    const CLIENT_QUEUE = 'client_queue';
    static function getInstance(){
        if(empty(self::$_instance)) {
            $self = new self();
            $result = $self->connect();
            self::$_instance = $self;
        }
        return self::$_instance;
    }
    public function connect(){
        $this->redis = new \Redis();
        $result = $this->redis->connect(C('REDIS_CONFIG.HOST'),C('REDIS_CONFIG.PORT'));
        if($result){
            return $this->redis;
        }
        else{
            echo "redis error:".print_r(C('REDIS_CONFIG'),true);
            log_write("redis error:".print_r(C('REDIS_CONFIG'),true),__METHOD__);
            return false;
        }
    }
    public function pushServerQueue($data){
        try {
            $result = $this->redis->lPush(self::SERVER_QUEUE, $data);
        }catch (\Exception $e){
            echo $e->getMessage();
            log_write($e->getMessage(),__METHOD__);
        }
    }

    public function pushClientQueue($data){
        echo __METHOD__.':';
        try {
            //$this->redis->lPush(self::CLIENT_QUEUE, $data); //改成sub模式
            $channel = 'channel_'.self::CLIENT_QUEUE;
            $this->redis->publish($channel,$data);
        }catch (\Exception $e){
            echo $e->getMessage();
            log_write($e->getMessage(),__METHOD__);
        }
    }

    public function getServerQueue(){
        try {
            $result = $this->redis->rPop(self::SERVER_QUEUE);
        }catch (\Exception $e){
            echo $e->getMessage();
            log_write($e->getMessage(),__METHOD__);
            $result = false;
        }
        return $result;
    }

    public function getClientQueue(){
        try {
            $result = $this->redis->rPop(self::CLIENT_QUEUE);
        }catch (\Exception $e){
            echo $e->getMessage();
            log_write($e->getMessage(),__METHOD__);
            $result = false;
        }
        return $result;
    }

    /**
     * 订阅消息队列事件
     * @param $callback callable
     */
    public function subClientQueue($callback){
        $channel = 'channel_'.self::CLIENT_QUEUE;
        $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        $this->redis->subscribe(array($channel),$callback);
    }
}

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
    const SERVER_QUEUE_DIRECT = 'server_queue_direct';//发消息队列
    const SERVER_QUEUE_ACK = 'server_queue_ack';//回复队列
    const CLIENT_SESSION = 'client_session';
    const CLIENT_IDS = 'client_ids';
    static function getInstance(){
        if(empty(self::$_instance)) {
            $self = new self();
            $result = $self->connect();
            if(!$result){
                sleep(1);
                exit;
            }
            self::$_instance = $self;
        }
        return self::$_instance;
    }
    public function connect(){
        $this->redis = new \Redis();
        $result = $this->redis->connect(C('REDIS_CONFIG.HOST'),C('REDIS_CONFIG.PORT'));
        var_dump(__METHOD__);
        if($result){
            return $this->redis;
        }
        else{
            echo "redis error:".print_r(C('REDIS_CONFIG'),true);
            log_write("redis error:".print_r(C('REDIS_CONFIG'),true),__METHOD__);
            return false;
        }
    }

    /**
     * 保存存client 和 uid关系
     * @param $peerId
     * @param $client_id
     * @return int
     */
    public function savePeerClient($peerId,$client_id){
        return $this->redis->hSet(self::CLIENT_SESSION.":".$peerId,$client_id,$client_id);
    }
    //删除 client_id
    public function delPeerClient($peerId,$client_id=''){
        //如果有设置client_id
        if($client_id) {
            return $this->redis->hDel(self::CLIENT_SESSION . ":" . $peerId, $client_id);
        }
        else{
            return $this->redis->del(self::CLIENT_SESSION . ":" . $peerId);
        }
    }

    /**
     * 查看peerId 对应的client
     * @param $peerId
     * @return array
     */
    public function getPeerClientId($peerId){
        return $this->redis->hKeys(self::CLIENT_SESSION.":".$peerId);
    }

    //查询在线peerId
    public function getPeerIds($peerIds = array()){
        return array_filter($peerIds,function($key){
            return $this->redis->exists(self::CLIENT_SESSION.":".$key);
        });
    }

    public function isPeerIdExists($peerId){
        return $this->redis->exists(self::CLIENT_SESSION.":".$peerId);
    }

    /**
     * 获取所有的在线用户
     */
    public function getAllPeerId($real = false){
        $list = $this->redis->keys(self::CLIENT_SESSION.':*') or $List = array();
        if($real){
            $len = strlen(self::CLIENT_SESSION.':');
            $list = array_map(function($v)use($len){
                return substr($v,$len);
            },$list);
        }
        return $list;
    }

    /**
     * @param $queue
     * @param $data
     */
    public function pushQueue($queue,$data){
        try {
            $result = $this->redis->lPush($queue, $data);
        }catch (\Exception $e){
            echo $e->getMessage();
            log_write($e->getMessage(),__METHOD__);
        }
    }

    public function getQueue($queue){
        try {
            $result = $this->redis->rPop($queue);
        }catch (\Exception $e){
            echo $e->getMessage();
            log_write($e->getMessage(),__METHOD__);
            $result = false;
        }
        return $result;
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
        try {
            //lpush模式
            $this->redis->lPush(self::CLIENT_QUEUE, $data); //改成sub模式
            //$channel = 'channel_'.self::CLIENT_QUEUE;
            //$this->redis->publish($channel,$data);
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

    public function getClientQueue($callback){
        while (true) {
            try {
                $result = $this->redis->rPop(self::CLIENT_QUEUE);
                if($result) {
                    call_user_func($callback, $this->redis,self::CLIENT_QUEUE,$result);
                }
                else{
                    usleep(200*1000);//休息 100 毫秒
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                log_write($e->getMessage(), __METHOD__);
                sleep(1);
            }
        }
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

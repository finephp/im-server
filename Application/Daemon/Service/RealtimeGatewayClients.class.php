<?php
namespace Daemon\Service;
use Daemon\Service\RealtimeConnection;
use GatewayWorker\Lib\Gateway;

if(!IS_CLI){
    die('NOT CLI');
}
//id 关系绑定
class RealtimeGatewayClients{
    static $clientToUserMaps = array();
    static $userToClientMaps = array();
    static $count = 0;

    /**
     * @param $user string
     * @param $connection RealtimeConnection
     * @param $tag String
     * @param $ua String
     */
    static function register($user,&$connection,$tag = '',$ua = ''){
        echo colorize(__METHOD__.':'."\r\n",'SUCCESS');
        $id = $connection->id;
        $_SESSION['tag'] = $tag;    //保存session tag
        $_SESSION['ua'] = $ua;//保存session ua
        $_SESSION['peerId'] = $user;//保存session
        echo __METHOD__,':session:',print_r($_SESSION,true);
        //保存到全局redis中 //
        $result = RedisService::getInstance()->savePeerClient($user,$id);
        Gateway::bindUid($id,$user);
        echo "{$user} {$tag} => {$id}"." open \r\n";
    }

    //加入到 group中
    static function joinGroup($group_id,$client_id=null){
        Gateway::joinGroup($client_id,$group_id);
    }

    /**
     * @param $connection RealtimeConnection
     */
    static function unregister(&$connection)
    {
        //删除key值
        echo __METHOD__."\r\n";
        if($_SESSION && !empty($_SESSION['peerId'])){
            //判断是否在线
            $peerId = $_SESSION['peerId'];
            //删除key值
            if(!Gateway::isUidOnline($peerId)){
                RedisService::getInstance()->delPeerClient($peerId);
            }
            else{
                RedisService::getInstance()->delPeerClient($peerId,$connection->id);
            }
        }
        //先查询到这个client上绑定的 uid
    }

    /**
     * @param $user string|string[]
     * @param $msg string
     * @return int
     */
    static function sendByUser($user,$msg){
        if(!is_array($user)){
            $user = [$user];
        }
        $count = 0;
        foreach($user as $peerId){
            $connections = self::getClientId($peerId);
            self::sendByClients($connections,$msg);
            $count++;
        }
        return $count;
    }
    //广播给所有人
    static function sendBroadcast($msg){
        Gateway::sendToAll($msg);
    }

    //广播给组
    static function sendGroup($groupId,$msg){
        Gateway::sendToGroup($groupId,$msg);
    }


    /**
     * @param $user String
     * @return RealtimeConnection[]
     */
    static function getClientId($peerId){
        //$client_ids = Gateway::getClientIdByUid($peerId) or $client_ids = array();
        $client_ids = RedisService::getInstance()->getPeerClientId($peerId);
        $connections = array();
        foreach($client_ids as $v){
            if($conn = RealtimeGateway::getConnection($v)) {
                $connections[$v] = $conn;
            }
            //清除 $conn
            else{
                RedisService::getInstance()->delPeerClient($peerId,$v);
            }
        }
        return $connections;
    }
    static function getUser($id){
        return self::$clientToUserMaps[$id]['user'];
    }

    /**
     * 校验tag冲突
     * @param $user string
     * @param $connection RealtimeConnection
     * @param $tag string
     * @return bool|RealtimeConnection[]
     */
    static function getConflictConnection($user,$connection,$tag){
        if(empty($tag)) return false;
        $id = $connection->id;
        $client_ids = Gateway::getClientIdByUid($user);
        $conflict = array();
        foreach($client_ids as $v){
            $conn = RealtimeGateway::getConnection($v);
            if($conn && $id !== $conn->id) {
                if(isset($conn->session['tag']) && $conn->session['tag'] == $tag) {
                    $conflict[$conn->id] = $conn;
                }
            }
        }
        return $conflict;
    }

    /**
     * @param $clients RealtimeConnection[]
     * @param $msg
     * @return int
     */
    static function sendByClients($clients,$msg){
        $count = 0;
        $msgBase64 = null;
        foreach ($clients as $conn){
            echo 'sendto id:'.$conn->peerId.'=>'.$conn->id."\r\n";
            if('lc.protobase64.3' == $conn->SecWebSocketProtocol) {
                if(empty($msgBase64)){
                    $msgBase64 = base64_encode($msg);
                }
                Gateway::sendToClient($conn->id,$msgBase64);
            }else{
                Gateway::sendToClient($conn->id,$msg);
            }
            $count++;
        }
        return $count;
    }

    static function clearClients(){
        $redisService = RedisService::getInstance();
        $list = $redisService->getAllPeerId();
        foreach ($list as $peerIdKey){
            $clients = $redisService->redis->hKeys($peerIdKey);
            foreach($clients as $client_id){
                //如果不在线，则清理该用户
                if(!Gateway::isOnline($client_id)){
                    $redisService->redis->hDel($peerIdKey,$client_id);
                }
            }
            $clients = $redisService->redis->hKeys($peerIdKey);
            //如果都是空的，再清理掉key值
            if(empty($clients)){
                echo "clear:".$peerIdKey."\r\n";
                $redisService->redis->del($peerIdKey);
            }
        }
    }
}




<?php
namespace Daemon\Service;
use Workerman\Connection\TcpConnection;
if(!IS_CLI){
    die('NOT CLI');
}
//id 关系绑定
class RealtimeClients{
    static $clientToUserMaps = array();
    static $userToClientMaps = array();
    /**
     * @param $user string userid
     * @param $connection TcpConnection
     * @param $tag String
     */
    static function register($user,&$connection,$tag=''){
        $id = $connection->id;
        // todo test 如果原来这个connection上的orgPearId 和现在的不一样的话,删除原orgPeerId上的 connection_id
        if(!empty($connection->peerId)) {
            $orgPearId = $connection->peerId;
            if($orgPearId != $user){
                var_dump(__METHOD__);
                echo colorize('unset id:'.$connection->id.' : '.$orgPearId.' => '.$user,'FAILURE');
                //unset(self::$userToClientMaps[$orgPearId][$connection->id]);
            }
        }
        // end
        $connection->peerId = $user;
        self::$clientToUserMaps[$id] = array('peerId'=>$user,'tag'=>$tag);
        if(empty(self::$userToClientMaps[$user])){
            self::$userToClientMaps[$user] = array($connection->id=> & $connection);
        }else {
            self::$userToClientMaps[$user][$connection->id] = & $connection;
        }
        echo "{$user} {$tag} => {$id}"." open \r\n";
    }

    /**
     * @param $connection TcpConnection
     */
    static function unregister($connection){
        $id = $connection->id;
        $connection->peerId = '__STATUS_CLOSE__';
        if(!empty(self::$clientToUserMaps[$id])){
            $user = self::$clientToUserMaps[$id]['peerId'];
            unset(self::$userToClientMaps[$user][$id]);
            unset(self::$clientToUserMaps[$id]);
            echo "{$user} => {$id}"." close \r\n";
        }
    }

    /**
     * @param $user String
     * @return TcpConnection[]
     */
    static function getClientId($peerId){
        if(empty(self::$userToClientMaps[$peerId])){
            return array();
        }
        //清除无效 connection
        foreach(self::$userToClientMaps[$peerId] as $k=>$v){
            if($v->peerId === '__STATUS_CLOSE__'){
                unset(self::$userToClientMaps[$peerId][$k]);
            }
        }
        return self::$userToClientMaps[$peerId];
    }
    static function getUser($id){
        return self::$clientToUserMaps[$id]['user'];
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
        var_dump(__METHOD__.':');
        var_dump($user);
        foreach($user as $peerId){
            $clientIds = self::getClientId($peerId);
            if(!empty($clientIds)){
                $count += self::sendByClients($clientIds,$msg);
            }
            else{
                var_dump('peerId clients is empty:'.$peerId);
            }
        }
        return $count;
    }

    /**
     * 校验tag冲突
     * @param $user string
     * @param $connection TcpConnection
     * @param $tag string
     * @return bool|TcpConnection[]
     */
    static function getConflictConnection($user,$connection,$tag){
        if(empty($tag) || empty(self::$userToClientMaps[$user])) return false;
        $id = $connection->id;
        $connections = self::$userToClientMaps[$user];
        $conflict = array();
        foreach($connections as $v){
            if($id !== $v->id) {
                $conflict[$v->id] = $v;
            }
        }
        return $conflict;
    }

    /**
     * @param $clients TcpConnection[]
     * @param $msg
     * @return int
     */
    static function sendByClients($clients,$msg){
        $count = 0;
        $msgBase64 = null;
        foreach ($clients as $conn){
            var_dump('sendto id:'.$conn->peerId.'=>'.$conn->id);
            if('lc.protobase64.3' == $conn->SecWebSocketProtocol) {
                if(empty($msgBase64)){
                    $msgBase64 = base64_encode($msg);
                }
                $conn->send($msgBase64);
            }else{
                $conn->send($msg);
            }
            $count++;
        }
        return $count;
    }
}




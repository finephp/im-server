<?php
namespace Daemon\Controller;
use Daemon\Service\Db;
use Daemon\Service\MongoModel;
use Think\Controller;
require_once WORKERMAN_PATH;
//include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
if(!IS_CLI){
    die('NOT CLI');
}
class TestController extends Controller {
    public function index(){
        $model = Db::MongoModel('conversation');
        $whereData = json_decode('{"_id":{"$id":"58079dbf9b1eaf34284a52b7"},"createdAt":{"$lt":{"__type":"Date","iso":"2016-10-24T07:29:29.892Z"}}}',true);
        $where = array();
        if(!empty($whereData['objectId'])){
            $where['_id'] = $whereData['objectId'];
        }
        if(!empty($whereData['m'])){
            $where['m'] = $whereData['m'];
        }
        if(!empty($whereData['name'])){
            $where['name'] = $whereData['name'];
        }
        //创建时间
        if(!empty($whereData['createdAt'])){
            $createdAt = $whereData['createdAt'];
            foreach($createdAt as $tp=>$value){
                $where['createdAt'][$tp] = new \MongoDate(strtotime($value['iso']));
            }
        }
        //如果是compact | flag= 1
        //$model->field('m',true);
        var_dump($model->getField('m'));
        print_r($model->limit('1')->select());
        var_dump($model->_sql());
    }

    public function test(){
        $redis = new \Redis();
        var_dump(C('REDIS_CONFIG.HOST'));
        var_dump(C('REDIS_CONFIG.PORT'));
        $result = $redis->connect(C('REDIS_CONFIG.HOST'),C('REDIS_CONFIG.PORT'));
        $redis->subscribe(array('channel_client_queue'), function ($instance, $channelName, $message) {
            echo $channelName, "==>", $message,PHP_EOL;
        });
        var_dump($result);
    }

    public function testPub(){
        $redis = new \Redis();
        var_dump(C('REDIS_CONFIG.HOST'));
        var_dump(C('REDIS_CONFIG.PORT'));
        $result = $redis->pconnect(C('REDIS_CONFIG.HOST'),C('REDIS_CONFIG.PORT'));
        $result = $redis->publish('channel_client_queue',"hello world");
        var_dump($result);
    }

    public function testMute(){
        $cid = '5812b6959b1eaf9b24583bd0';
        $peerId = 'chensf';
        $model =  $model = Db::MongoModel('conversation');
        //把当前用户加到对话的静音列表中
        $where = array(
            '_id'=>$cid,
            'm'=>$peerId
        );
        $data = array(
            'mu'=>['pull',$peerId],
            'name'=>'hahahahahahah '.time(),
        );
        $data = $model->create($data,MongoModel::MODEL_UPDATE);
        $result = $model->where($where)->save($data);
        var_dump($model->_sql());
        var_dump($result);
    }

    public function testLogs(){
        $model =  $model = Db::MongoModel('conversation');
        $data= array(
            'm'=>array('pullAll',['wangtr','chensf'])
        );
        $result = $model->where(array(
            '_id'=>'581c549c9b1eaf6a537b3e5c'
        ))->save($data);
        var_dump($model->_sql());
        var_dump($result);
    }

    public function insertUser(){
        //更新记录
        $userMsgModel = Db::MongoModel('userMessage');
        $peerId = 'leeyeh';
        $cid = '58079dbf9b1eaf34284a52b7';
        $nowtime = new \MongoDate();
        $data['convs'] = array($cid=>array(
            'cid'=>$cid,
            'unread'=>0,
            'lm'=>$nowtime,
        ));
        $data['peerId'] = $peerId;
        //查询是否存在obj_id
        $info = $userMsgModel->where(array(
            'peerId'=>$peerId
        ))->find();
        if($info){
            $data['_id'] = $info['_id'];
        }
        print_r($data);
        $result = $userMsgModel->where(array(
            'peerId'=>$peerId
        ))->add($data,array(),true);
        var_dump($result);
    }

}
<?php
namespace Rtm\Controller;
use Daemon\Service\Db;
use Daemon\Service\RealtimeRestService;

/**
 * Class Messages 消息 restFull接口
 */
class MessagesController extends BaseController {
    public function index(){
        $data = RealtimeRestService::parseInputData();
        $service = RealtimeRestService::getInstance();
        $result = $service->rtmMessage($data);
        return $this->response($result);
    }
    //聊天记录
    public function logs(){
        //修改聊天记录
        if($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $request = RealtimeRestService::parseInputData();
            return $this->response($this->logsPut($request));
        }
        //获取聊天记录
        else{
            $result = $this->queryMessageLog(I('get.'));
            return $this->response(json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 修改聊天记录
     * @param $request
     */
    protected function logsPut($request){
        $data = array(
           'convId' => $request['conv-id'],
           'ackAt' => $request['ack-at'],
           'isConv' => $request['is-conv'],
           'from' => $request['from'],
           'bin' => $request['bin'],
           'isRoom' => $request['is-room'],
           'fromIp' => $request['from-ip'],
           'to' => $request['to'],
           'data' => $request['data'],
        );
        $model = $this->_getMessageLogsModel();
        $where = array(
            'msgId' => $request['msg-id'],
            'timestamp' => $request['timestamp'],
        );
        $result = $model->where($where)->save($data);
        if($result){
            return '{}';
        }
        else{
            return '{}';
        }
    }

    //查询某个对话的消息
    /**
     * @param $data array
     * @return array|mixed
     */
    protected function queryMessageLog($data){
        $data = array_merge(array(
            'convid'=>'',
            'max_ts'=> time()-1000000,//毫秒 //todo
            'msgid'=> '',
            'limit'=> 100,
            'peerid'=> '',
            'nonce'=> '',
            'signature_ts'=> '',
            'signature_'=> '',
        ),$data);
        $peerid = $data['peerid'];
        $cid = $data['convid'];
        $limit = $data['limit'];
        $t = '';
        $tt = $data['max_ts']*1000;//after time
        if($limit === 0){
            return array();
        }
        //默认limit 为100条
        if($limit === null){
            $limit = 100;
        }
        $where  = array(
            //'to' => $peerid,
        );
        //如果没有查询所有的聊天记录
        if($cid){
            $where['convId'] = $cid;
        }
        if($t){
            $where['createdAt'] = array('lt',new \MongoDate(substr($t,0,10),substr($t,-3)*1000));
        }
        if($tt){
            $where['createdAt'] = array('gt',new \MongoDate(substr($tt,0,10)+3,substr($tt,-3)*1000));
        }
        $model = $this->_getMessageLogsModel();
        $result = $model->where($where)->limit($limit)->order("createdAt desc,msgId desc")
            ->select() or $result = array();
        //对结果按时间倒序
        $new_result = array();
        foreach($result as $v){
            $_v = array(
                'timestamp' =>  RealtimeRestService::getTimestamp($v['createdAt']),
                'conv-id' =>   $v['convId'],
                'data' =>      $v['data'],
                'from' =>     $v['convId'],
                'msg-id' =>  $v['msgId'],
                'is-conv' =>  0,
                'is-room' => 0,
                'to' =>  '',
                'bin' => false,
                'from-ip'=>  $v['ip'],
            );
            $new_result[] = $_v;
        }
        return $new_result;
    }

    protected function _getMessageLogsModel(){
        return Db::MongoModel('Rtm_Message');
    }


}
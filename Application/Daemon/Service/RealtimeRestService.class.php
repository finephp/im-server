<?php
namespace Daemon\Service;
use \CommandType;
use \GenericCommand;

if(class_exists('ProtobufMessage')) {
    include_once(dirname(APP_PATH) . '/server/pb_proto_message.php');
}
//for win
//require_once (APP_PATH.'Common/Vendor/protocolbuf/message/pb_message.php');
//require_once (dirname(APP_PATH).'/proto/pb_proto_message.php');
//for end
class RealtimeRestService{
    private static $_instance;
    public $config;
    static function getInstance($config = null){
        if(empty(self::$_instance)) {
            $self = new self();
            if($config){
                $self->config = $config;
            }
            self::$_instance = $self;
        }
        return self::$_instance;
    }

    public function __construct(){
        if(getenv('IM_SOCKET_HOST')){
            C('RTM_SOCKET_URL',getenv('IM_SOCKET_HOST').':'.getenv('IM_SOCKET_PORT'));
        }
        var_dump(C('RTM_SOCKET_URL'));
    }

    /**
     * 广播会话
     * @param $request
     * @param array $config
     */
    public function rtmBroadcast($request,$config = array()){
        $conv_id = $request['conv_id'];
        $from_peer = $request['from_peer'];
        $to_peers = $request['to_peers'];
        $message = $request['message'];
        $tr = $request['transient'];
        //查询聊天室
        $convInfo = $this->_getConversationByid($conv_id);
        if(!$convInfo || empty($convInfo['sys'])){
            return self::responseError('SYSTEM_CONVERSATION_REQUIRED',4313);
        }
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::direct);
        $respMsg = new \DirectCommand();
        $genericCmd->setDirectMessage($respMsg);
        $msgId = $this->inesrtMessage(array(
            'convId' => $conv_id,
            'from' => $from_peer,
            'data' => $message,
        ));
        $timestamp = self::getTimestamp(new \MongoDate());
        $genericCmd->setPeerId($from_peer);
        $respMsg->setId($msgId);
        $respMsg->setFromPeerId($from_peer);
        $respMsg->setTimestamp($timestamp);
        $respMsg->setCid($conv_id);
        $respMsg->setMsg($message);
        $respMsg->setTransient($tr);
        //判断是否群发
        if($to_peers){
            foreach($to_peers as $peer) {
                $genericCmd->setPeerId($peer);
                $this->sendToRtm($this->encodeResp($genericCmd));
            }
        }
        else{
            $this->sendToRtm('MESSAGE_BROAD:'.$this->encodeResp($genericCmd));
        }
        $result = array(
            'msg-id' => $msgId,
            'timestamp' => $timestamp,
        );
        return self::responseResult($result);
    }

    public function rtmBroadcastDelete($request){
        if(empty($request['mid'])){
            return '{}';
        }
        $mid = $request['mid'];
        $model = Db::MongoModel('Rtm_Message');
        try {
            $model->where(array(
                '_id' => $mid
            ))->delete();
        }catch (\Exception $e){
        }
        return '{}';
    }

    /**
     * 对已有对话发送消息
     * @param $request
     */
    public function rtmMessage($request){
        $conv_id = $request['conv_id'];
        $from_peer = $request['from_peer'];
        $to_peers = $request['to_peers'];
        $message = $request['message'];
        $tr = $request['transient'];
        //查询聊天室
        $convInfo = $this->_getConversation($conv_id);
        if(!$convInfo){
            return self::responseError('CONVERSATION_REQUIRED',4313);
        }
        $isSys = !empty($convInfo['sys']);
        $isTr = !empty($convInfo['tr']);
        if($isTr){
            $tr = true;
        }
        $genericCmd = new GenericCommand();
        $genericCmd->setCmd(CommandType::direct);
        $respMsg = new \DirectCommand();
        $genericCmd->setDirectMessage($respMsg);
        $msgId = $this->inesrtMessage(array(
            'convId' => $conv_id,
            'from' => $from_peer,
            'data' => $message,
        ));
        $timestamp = self::getTimestamp(new \MongoDate());
        $genericCmd->setPeerId($from_peer);
        $respMsg->setId($msgId);
        $respMsg->setFromPeerId($from_peer);
        $respMsg->setTimestamp($timestamp);
        $respMsg->setCid($conv_id);
        $respMsg->setMsg($message);
        $respMsg->setTransient($tr);
        //如果人为空，且是普通群聊，则要查询群中所有人
        if(!$to_peers && !$isSys && !$isTr){
            $to_peers = $convInfo['m'];
        }
        //判断是否群发
        if($to_peers){
            foreach($to_peers as $peer) {
                $genericCmd->setPeerId($peer);
                $this->sendToRtm($this->encodeResp($genericCmd));
            }
        }
        //如果是系统对话
        elseif($isSys){
            $genericCmd->setPeerId($conv_id);//这儿的peerId变成了组id，免得再次解析
            $this->sendToRtm('MESSAGE_RROAD:'.$this->encodeResp($genericCmd));
        }
        //否则是暂态对话，发到对话组中
        elseif($isTr){
            $genericCmd->setPeerId($conv_id);//这儿的peerId变成了组id，免得再次解析
            $this->sendToRtm('MESSAGE_GROUP:'.$this->encodeResp($genericCmd));
        }
        $result = array(
            'msg-id' => $msgId,
            'timestamp' => $timestamp,
        );
        return self::responseResult($result);
    }
    //发送数据到worker,通过内部websocket接口
    /**
     * @param $message
     * @return bool|int
     */
    public function sendToRtm($message){
        if(!C('RTM_SOCKET_URL')){
            echo ('config.RTM_SOCKET_URL undefined');
            return false;
        }
        var_dump(C('RTM_SOCKET_URL'));
        $client = stream_socket_client('tcp://'.C('RTM_SOCKET_URL'), $errno, $errmsg, 1);
        $result = fwrite($client, $this->frameEncode($message));
        var_dump(__METHOD__);
        var_dump($result);
        unset($client);
        return $result;
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

    /**
     * @param $resp GenericCommand
     * @return string
     */
    public function encodeResp($resp){
        $new_resp = $resp->serializeToString();
        return $new_resp;
    }

    protected function _getConversationByid($cid)
    {
        $model = Db::MongoModel('Rtm_Conversation');
        try {
            $result = $model->where(array(
                '_id' => $cid
            ))->find();
            return $result;
        }catch(\Exception $e){
            return null;
        }
    }

    static function responseError($reason,$code){
        $array = array(
            'result' => array(
                'error' => array(
                    'reason' => $reason,
                    'code'=>$code
                )
            )
        );
        return json_encode($array,JSON_UNESCAPED_UNICODE);
    }

    static function responseResult($arr){
        return json_encode(array('result'=>$arr));
    }

    //插入消息
    public function inesrtMessage($data){
        //插入消息数据表
        $messageModel = Db::MongoModel('Rtm_Message');
        $data = array_merge(array(
            'convId' => null,
            'from' => null,
            'data' => null,
            'passed' => false,
            'createdAt' => new \MongoDate(),
            'updatedAt' => new \MongoDate(),
        ),$data);
        $result = $messageModel->add($data);
        $msgId = $messageModel->getLastInsID();
        return $msgId;
    }
    //查询聊天室在线人数
    public function transientGroup($request){
        $cmd = array(
            'cmd' => 'transient_group/onlines',
            'data' => $request,
        );
        $result = $this->sendToRtm('CMD:'.json_encode($cmd));
        return $result;
    }

    //查询聊天室在线人数
    public function rtmOnline($request){
        $cmd = array(
            'cmd' => 'online',
            'data' => $request,
        );
        $result = $this->sendToRtm('CMD:'.json_encode($cmd));
        return $result;
    }


    /**
     * 会话禁言 todo
     * @param $request array
     * $request['client_id'] *
     * $request['conv_id'] *
     * $request['ttl'] *
     * @return string
     */
    public function convBlacklistPost($request){
       return '{}';
    }

    /**
     * 会话解除禁言 todo
     * @param $request array
     * $request['client_id'] *
     * $request['conv_id'] *
     * @return string
     */
    public function convBlacklistDelete($request){
        return '{}';
    }
    /**
     * @param $date \MongoDate
     * @return int 13
     */
    static function getTimestamp($date){
        return floor($date->sec*1000+($date->usec/1000));
    }


    static function parseInputData($data = null){
        if($_SERVER['CONTENT_TYPE'] == 'application/json'){
            if($data) {
                $result = json_decode($data,true);
            }else{
                if(!empty($_SERVER['_PHP_INPUT'])) {
                    $result = json_decode($_SERVER['_PHP_INPUT'], true);
                    unset($_SERVER['_PHP_INPUT']);
                }
                else{
                    $result = json_decode(file_get_contents('php://input'), true);
                }
            }
            if(!$result){
                E('{"code":107,"error":"Malformed json object. A json dictionary is expected."}');
            }
            return $result;
        }
        //delete 事件
        elseif($_SERVER['REQUEST_METHOD'] == 'DELETE' || $_SERVER['REQUEST_METHOD'] == 'DETELE'){
            $result = $_GET;
            return $result;
        }
        E('{}');
        return false;
    }

    /**
     * @param $whereData
     * $whereData['m']
     * $whereData['cid']
     * $whereData['name']
     * @return mixed|null
     */
    public function queryConversation($whereData){
        $cid = $whereData['cid'];
        $where = array();
        if(!empty($cid)){
            $where['_id'] = $cid;
        }
        $unique = !empty($whereData['unique']);
        if(!empty($whereData['members'])){
            $m = explode(',',$whereData['members']);
            if($unique){
                $where['unique'] = true;
                $where['m'] = array('eq',$m);
            }
            else{
                $where['m'] = array('all',$m);
            }
        }
        if(!empty($whereData['name'])){
            $where['name'] = $whereData['name'];
        }
        $model = Db::MongoModel('Rtm_Conversation');
        $model->limit(10);
        try {
            $result = $model->field('name,objectId')->where($where)->select()
            or $result = array();
            $data = array();
            foreach($result as &$v){
                $v['cid'] = $v['_id'];
                unset($v['_id']);
                $data[] = $v;
            }unset($v);
            $result = json_encode(array(
                'code'=>0,
                'data'=>$data,
            ));
            return $result;
        }catch(\Exception $e){
            return null;
        }
    }
}

<?php
namespace Rtm\Controller;
use Daemon\Service\RealtimeRestService;

/**
 * Class Messages 消息 restFull接口
 */
class TransientGroupController extends BaseController  {
    public function onlines(){
        $data = $_GET;
        $service = RealtimeRestService::getInstance();
        //发送广播
        $result = $service->transientGroup($data);
        return $this->response($result);
    }


    public function testBroadcast(){
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $data = I('data',
            '{"from_peer": "1a", "message": "{\"_lctype\":-1,\"_lctext\":\"这是一个纯文本消息\",\"_lcattrs\":{\"a\":\"_lcattrs 是用来存储用户自定义的一些键值对\"}}", "conv_id": "580972659b1eaf7d7abfb7202","transient":true}');
        $data = RealtimeRestService::parseInputData($data);
        $service = RealtimeRestService::getInstance();
        $result = $service->rtmBroadcast($data);
        return $this->response($result);
        //$result = $service->sendToRtm('REST:'.$data);
    }
}
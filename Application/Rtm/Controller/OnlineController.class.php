<?php
namespace Rtm\Controller;
use Daemon\Service\RealtimeRestService;

/**
 * Class Messages 消息 restFull接口
 */
class OnlineController extends BaseController  {
    /**
     * 查询在线人数
     */
    public function index(){
        $data = RealtimeRestService::parseInputData();
        $service = RealtimeRestService::getInstance();
        //发送广播
        $result = $service->rtmOnline($data);
        return $this->response($result);
    }

    /**
     * 强制下线
     */
    public function kick(){
        $request = RealtimeRestService::parseInputData();
        $service = RealtimeRestService::getInstance();
        //强制踢人
        $cmd = array(
            'cmd' => 'online_kick',
            'data' => $request,
        );
        $result = $service->sendToRtm('CMD:'.json_encode($cmd));
        return $this->response($result);
    }

}
<?php
namespace Rtm\Controller;
use Daemon\Service\RealtimeRestService;

/**
 * Class Messages 消息 restFull接口
 */
class TransientGroupController extends BaseController  {
    /**
     * 查询在线人数
     * $_GET.gid:会话ID
     * @return string|void
     */
    public function onlines(){
        $data = $_GET;
        $service = RealtimeRestService::getInstance();
        //发送广播
        $result = $service->transientGroup($data);
        return $this->response($result);
    }
}
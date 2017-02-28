<?php
namespace Rtm\Controller;
use Daemon\Service\RealtimeRestService;

/**
 * Class Messages 消息 restFull接口
 */
class EmptyController extends BaseController {
    public function index(){
       return $this->response('error');
    }
}
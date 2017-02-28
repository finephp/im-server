<?php
namespace Rtm\Controller;
/**
 * Class Messages 消息 restFull接口
 */
class BaseController extends \Think\Controller {
    /**
     * @param $msg
     * @return string |void
     */
    protected function response($msg){
        if(IS_CLI){
            if(I('_debug_')==='nocli'){
                echo $msg;
            }
            return $msg;
        }
        echo $msg;
        return $msg;
    }

    public function test(){
        return $this->response(__METHOD__.'hello world！');
    }
}
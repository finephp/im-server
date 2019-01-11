<?php
namespace Rtm\Controller;
use Daemon\Service\RealtimeRestService;

/**
 * Class Messages 消息 restFull接口
 */
class ConversationController extends BaseController {
    /**
     * 禁言 todo
     */
    public function blacklist(){
        $data = RealtimeRestService::parseInputData();
        $service = RealtimeRestService::getInstance();
        $result = '{}';
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $service->convBlacklistPost($data);
        }
        elseif($_SERVER['REQUEST_METHOD'] == 'DELETE'){
            $result = $service->convBlacklistDelete($data);
        }
        return $this->response($result);
    }

    /**
     * 查询
     */
    public function query(){
        $service = RealtimeRestService::getInstance();
        return $service->queryConversation($_GET);
    }

    /**
     */
    public function create_chatrooms(){
        $input = RealtimeRestService::parseInputData();
        $data = array(
            'm' => [],
            'c' => empty($input['create'])?:'',
            'name' => empty($input['name'])?:'',
            'unique' => !empty($input['unique']),
            'tr'=> !empty($input['tr']),
        );
        $data = array_merge($input,$data);

    }

}
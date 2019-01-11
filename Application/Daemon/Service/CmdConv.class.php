<?php
namespace Daemon\Service;
use CommandType;
use GatewayWorker\Lib\Gateway;
use JsonObjectMessage;
use OpType;
use ConvCommand;
use GenericCommand;

if(!IS_CLI){
    die('NOT CLI');
}
include_once (dirname(APP_PATH).'/server/pb_proto_message.php');
class CmdConv extends CmdBase {
    public $client_id;
    public $convModel;
    /** @var $genericCmd GenericCommand*/
    public $genericCmd;
    const CMD = CommandType::conv; //cmd=1

    /**
     * @param $genericCmd GenericCommand
     */
    public function __construct($genericCmd)
    {
        $this->genericCmd = new GenericCommand();
        $this->genericCmd->setCmd(self::CMD);
        $this->genericCmd->setPeerId($genericCmd->getPeerId());
        $this->genericCmd->setI($genericCmd->getI());
        $convMessage = new ConvCommand();
        $convMessage->setCid($genericCmd->getConvMessage()->getCid());
        $this->genericCmd->setConvMessage($convMessage);
    }

    /**
     * @param $genericCmd GenericCommand
     * @return bool|GenericCommand
     */
    static function exeCmd($genericCmd){
        $cmd = new self($genericCmd);
        $opType = $genericCmd->getOp();
        //开始签名校验
        if(C('SIGNATURE_FLAG') && $resp = $cmd->checkConvSignature($genericCmd)){
            return $cmd->pushClientQueue($resp);
        }
        //结束签名校验
        switch($opType){
            case OpType::add:// add = 2
                return $cmd->opAdd($genericCmd);
                break;
            case OpType::remove:// remove = 3
                return $cmd->opRemove($genericCmd);
                break;
            case OpType::query:// query = 7
                return $cmd->opQuery($genericCmd);
                break;
            case OpType::start;// 30
                return $cmd->opStart($genericCmd);
                break;
            case OpType::update:// 45
                return $cmd->opUpdate($genericCmd);
                break;
            case OpType::mute:// 47 静音
                return $cmd->opMute($genericCmd);
                break;
            case OpType::unmute:// 48 取消
                return $cmd->opUnMute($genericCmd);
                break;
            case OpType::status:// 49
                return $cmd->opStatus($genericCmd);
                break;
            case OpType::count://43
                return $cmd->opCount($genericCmd);
                break;
            default:
                echo __METHOD__.':unknow opType:'.$opType."\r\n";
        }
        return false;
    }
    /**
     * optype:7
     * @param $genericCmd \GenericCommand
     * @return \GenericCommand
     */
    public function opQuery($genericCmd){
        $message = new \ConvCommand();
        $jsonObjectMessage = new JsonObjectMessage();
        $jsonObjectMessage->setData($this->queryConvData($genericCmd));
        $message->setResults($jsonObjectMessage);
        $genericCmd->setConvMessage($message);
        $genericCmd->setOp(OpType::results);
        return $this->pushClientQueue($genericCmd);
    }

    /**
     * 创建对话
     * @param $genericCmd GenericCommand
     */
    public function opStart($genericCmd){
        $genericCmd->setOp(OpType::started);
        $convMessage = $genericCmd->getConvMessage();
        $model = self::_getConvModel();
        $m = $convMessage->getM();
        $creater = $genericCmd->getPeerId();
        $unique = $convMessage->getUnique();
        $tr = !empty($convMessage->getTransient());
            //创建对话
        $attrData = $convMessage->getAttr()->getData();
        $attrData = json_decode($attrData,true) or $attrData = array();
        //加入创建人
        if($creater) {
            array_push($m, $creater);
        }
        $m = array_unique($m);
        $data = array(
            'm' => $m,
            'c' => $creater,
            'unique' => $unique,
            'tr'=>$tr,
        );
        $data = array_merge($attrData,$data);
        $data = $model->create($data);
        //查询是否有维一的成员组的聊天室
        $unique = $convMessage->getUnique();
        //如果是暂态聊天室，且唯一，则只按名称查询
        if($tr && $unique){
            $resultConv = $model->where(array(
                'unique' => true,
                'tr' => true, //暂态聊天室
                'name' => $data['name']
                //'c'=>$creater,
            ))->find();
            if($resultConv){
                $cid = $resultConv['_id'];
                $m = $resultConv['m'];
            }
        }
        if(!$tr && $unique){
            $resultConv = $model->where(array(
                'unique'=>true,
                'tr'=>false,//非暂态聊天室
                'm'=>array('eq',$data['m']),
                //'c'=>$creater,
            ))->find();
            if($resultConv){
                $cid = $resultConv['_id'];
                $m = $resultConv['m'];
            }
        }
        /*
        var_dump($resultConv);
        echo __METHOD__.":";
        echo $model->_sql()."\r\n";
        return;
        */
        //如果没有，则创建对话
        if(empty($cid)) {
            /** @var $result \MongoId */
            $result = $model->add($data);
            //log_write($model->_sql(),__METHOD__);
            if(empty($result)){
                log_write($model->getDbError(),'SQL_ERROR');
            }
            $cid = $model->getLastInsID();
        }
        //如果是暂态会话
        if($tr){
        }
        else {
            //更新会话成员信息
            $this->insertUserConv($cid, $m);
        }
        $convMessage->setCid($cid);
        $convMessage->setUdate(date(DATE_ISO8601,$data['updatedAt']->sec));
        $convMessage->setCdate(date(DATE_ISO8601,$data['createdAt']->sec));
        $this->pushClientQueue($genericCmd);
        $onlineM = self::getOnlineSession($m);
        $this->emitJoined($genericCmd,$onlineM);
        $this->emitMembers_joined($genericCmd,$onlineM);
    }

    /**
     * 触发被邀事件（只有被邀用户在线时才发送） 85
     * @param $genericCmd GenericCommand
     */
    public function emitInvited($genericCmd,$m=array()){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::invited);
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = new ConvCommand();
        $cid = $genericCmd->getConvMessage()->getCid();
        $convMessage->setCid($cid);
        $convMessage->setInitBy($genericCmd->getPeerId());
        $resp->setConvMessage($convMessage);
        //通知所有在对话中的人,如果在线的话
        if(empty($m)) {
            $m = $genericCmd->getConvMessage()->getM();
        }
        $m = self::getOnlineSession($m);
        foreach($m as $to) {
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }

    /**
     * 触发加入事件 32
     * @param $genericCmd GenericCommand
     */
    public function emitJoined($genericCmd,$m=array()){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::joined);
        $peerId = $genericCmd->getPeerId();
        $convMessage = new ConvCommand();
        $cid = $genericCmd->getConvMessage()->getCid();
        $convMessage->setCid($cid);
        $convMessage->setInitBy($peerId);
        $resp->setConvMessage($convMessage);
        //通知所有在对话中的人,如果在线的话
        if(empty($m)) {
            $m = $genericCmd->getConvMessage()->getM();
            $m = self::getOnlineSession($m);
        }
        //通知所有在对话中的人
        foreach($m as $to) {
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }
    /**
     * 触发加入事件 33
     * @param $genericCmd GenericCommand
     */
    public function emitMembers_joined($genericCmd,$m=array()){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::members_joined);
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = new ConvCommand();
        $convMessage->setCid($genericCmd->getConvMessage()->getCid());
        $convMessage->setInitBy($genericCmd->getPeerId());
        $addM = $genericCmd->getConvMessage()->getM();
        foreach ($addM as $v){
            $convMessage->appendM($v);
        }
        $resp->setConvMessage($convMessage);
        //通知会话中的所有人
        //通知所有在对话中的人
        foreach($m as $to) {
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }

    /**
     * 触发被踢事件被踢用户在线时才发送）
     * @param $genericCmd GenericCommand
     */
    public function emitKicked($genericCmd,$m=array()){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::kicked);
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = new ConvCommand();
        $cid = $genericCmd->getConvMessage()->getCid();
        $convMessage->setCid($cid);
        $convMessage->setInitBy($genericCmd->getPeerId());
        $resp->setConvMessage($convMessage);
        //通知所有在对话中的人,如果在线的话
        if(empty($m)) {
            $m = $genericCmd->getConvMessage()->getM();
            $m = self::getOnlineSession($m);
        }
        foreach($m as $to) {
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }
    /**
     * 触发用户离开事件只有被邀用户在线时才发送）
     * @param $genericCmd GenericCommand
     */
    public function emitMembers_left($genericCmd,$m = array()){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::members_left);
        $convMessage = new ConvCommand();
        $cid = $genericCmd->getConvMessage()->getCid();
        $convMessage->setCid($cid);
        $convMessage->setInitBy($genericCmd->getPeerId());
        $left_m = $genericCmd->getConvMessage()->getM();
        foreach($left_m as $_m) {
            $convMessage->appendM($_m);
        }
        $resp->setConvMessage($convMessage);
        //通知所有其它人
        foreach($m as $to) {
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }
    /**
     * @param $genericCmd GenericCommand
     */
    protected function queryConvData($genericCmd){
        $convCommand = $genericCmd->getConvMessage();
        $limit = $convCommand->getLimit();
        $skip = $convCommand->getSkip() or $skip = 0;
        $sort = $convCommand->getSort();
        if($limit === 0){
            return '[]';
        }
        if(empty($limit)){
            $limit = 1;
        }
        $where = $convCommand->getWhere()->getData();
        $whereData = json_decode($where,true);
        $model = self::_getConvModel();
        $where = array();
        //处理特殊的 and 事件
        if(!empty($whereData['$and'])){
            $where_and = $whereData['$and'];
            foreach($where_and as $v){
                if(is_array($v)) {
                    $whereData = array_merge($whereData, $v);
                }
            }
        }

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
        //限制数量
        if(empty($skip)){
            $model->limit($limit);
        }else{
            $model->limit($skip,$limit);
        }
        //排序
        if($sort){
            //比如  -createdAt
            if(strpos($sort,'-')){
                $orderby = 'desc';
                $sort = substr($sort,1);
            }
            else{
                $orderby = 'asc';
            }
            $model->order($sort.' '.$orderby);
        }
        //压缩,成员

        $flag = $convCommand->getFlag();
        //$flag = 1;//todo debug
        try {
            $resultList = $model->where($where)->select();
        }catch (\Exception $e){
            $resultList = array();
        }
        if(empty($resultList)){
            $resultList = array();
            print_r(__METHOD__);
            echo " resultList is empty"."\r\n";
            print_r($whereData);
            print_r($model->_sql());
        }
        //log_write($model->_sql(),__METHOD__);
        $data = array();
        foreach($resultList as $result){
            $cid = $result['_id'];
            unset($result['_id']);
            //如果是compact | flag= 1
            if($flag === 1){
                unset($result['m']);
            }
            //如果 flag==2 : 代表 withLastMessagesRefreshed 是否需要返回最后一条消息
            //todo last
            if($flag === 2){
                //查询最后一条消息
                $msgModel = Db::MongoModel('Rtm_Message');
                $msgData = $msgModel->where(array(
                    'convId'=>$cid
                ))->order('createdAt desc')->find();
                //log_write($msgModel->_sql(),__METHOD__);
                $result['msg'] = null;
                if($msgData) {
                    $result['msg'] = $msgData['data'];
                    /*
                    $result['lm'] = array(
                        '__type' => 'Date',
                        'iso' => date(DATE_ISO8601, $msgData['updatedAt']->sec)
                    );
                    */
                    $result['msg_from'] = $msgData['from'];
                    $result['msg_mid'] = $msgData['_id'];
                    $result['msg_timestamp'] = self::getTimestamp($msgData['createdAt']);
                }
            }
            //$lm = empty($result['lm'])?'':date(DATE_ISO8601,$result['lm']->sec);
            $lm = empty($result['lm']) ? null:
                (is_array($result['lm'])?$result['lm']:array(
                    '__type' => 'Date',
                    'iso' => date(DATE_ISO8601, $result['lm']->sec)
                ))
            ;
            $data[] = array_merge($result,array(
                'updatedAt' => date(DATE_ISO8601,$result['updatedAt']->sec),
                'createdAt' => date(DATE_ISO8601,$result['createdAt']->sec),
                'lm' => $lm,
                'objectId'=>$cid
            ));
        }
        return json_encode($data);
    }
    /**
     * 更新对话
     * @param $genericCmd GenericCommand
     * @return bool|void
     */
    public function opUpdate($genericCmd){
        $resp = new GenericCommand();
        $resp->setCmd(OpType::updated);
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = $genericCmd->getConvMessage();
        $cid = $convMessage->getCid();
        $model = self::_getConvModel();
        //todo 校验cid是否有效
        $m = $convMessage->getM();
        if(!$convMessage->getAttr()){
            $resp = $this->respError(4309,'CONVERSATION_UPDATE_REJECTED');
            $resp->setPeerId($genericCmd->getPeerId());
            $resp->setI($genericCmd->getI());
            //错误对话信息
            return $this->pushClientQueue($resp);
        }
        //更新对话
        $attrData = $convMessage->getAttr()->getData();
        $attrData = json_decode($attrData,true) or $attrData = array();
        $data = array(
            'm' => ['set',$m],
        );
        $data = array_merge($attrData,$data);
        unset($data['c']);
        unset($data['unique']);
        unset($data['tr']);
        if(empty($m)){
            unset($data['m']);
        }
        $data = $model->create($data,MongoModel::MODEL_UPDATE);
        //更新对话
        $result = $model->where(array(
            '_id' => $cid
        ))->save($data);

        //log_write($model->_sql(),__METHOD__);
        $convMessage->setCid($cid);
        $convMessage->setUdate(date(DATE_ISO8601,$data['updatedAt']->sec));
        $convMessage->setCdate(date(DATE_ISO8601,$data['createdAt']->sec));
        $resp->setConvMessage($convMessage);
        return $this->pushClientQueue($resp);
    }
    /**
     * 统计在线？
     *  @param $client_id
     * @param $genericCmd GenericCommand
     */
    public function opCount($genericCmd){
        //查询数量
        $convMessage = $genericCmd->getConvMessage();
        $cid = $convMessage->getCid();
        //查找聊天室在线人数，或者会话中人数
        $model = self::_getConvModel();
        $result = $model->find($cid);
        if(!$result){
            $count = 0;
        }
        //如果普通会话
        elseif(empty($result['tr'])){
            $count = count($result['m']);
        }
        //暂态聊天室的连接数
        else{
            $count = Gateway::getClientCountByGroup($cid);
        }
        $convMessage->setCount($count);
        $resp = new GenericCommand();
        $resp->setCmd($genericCmd->getCmd());
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $resp->setOp(OpType::result);//44
        $resp->setConvMessage($convMessage);
        $this->pushClientQueue($resp);
    }

    /**
     * 添加用户
     * @param $genericCmd GenericCommand
     * @return bool|void
     */
    public function opAdd($genericCmd){
        $convMessage = $genericCmd->getConvMessage();
        $m = $convMessage->getM();
        $cid = $convMessage->getCid();
        if(empty($cid)){
            //处理返回信息
            $resp = new GenericCommand();
            $resp->setCmd(CommandType::error);
            $resp->setI($genericCmd->getI());
            $resp->setPeerId($genericCmd->getPeerId());
            $msg = new \ErrorCommand();
            $msg->setCode(4401);
            $msg->setReason('INVALID_CONV_ID');
            $resp->setErrorMessage($msg);
            return $this->pushClientQueue($resp);
        }
        $model = $this->_getConvModel();
        //获取聊天室信息
        $convInfo = $this->_getConversation($cid);
        //判断聊天室是否暂态聊天室
        //如果是暂态聊天室，把用户加到 gateway的聊天组
        if(!empty($convInfo['tr'])){
            if(!empty($_SERVER['GATEWAY_CLIENT_ID'])) {
                Gateway::joinGroup($_SERVER['GATEWAY_CLIENT_ID'],$cid);
            }
        }
       else {
            //更新数据库中的对话成员
            $data = $model->create(array(
                'm' => array('addToSet', array('$each' => $m))
            ), MongoModel::MODEL_UPDATE);
            //更新对话成员
            $result = $model->where(array('_id' => $cid))->save($data);
            $this->insertUserConv($cid,$m);
        }
        //返回
        $resp = new GenericCommand();
        $resp->setCmd($genericCmd->getCmd());
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $resp->setOp(OpType::added);//10
        $message = new ConvCommand();
        $message->setCid($cid);
        $resp->setConvMessage($message);
        $this->pushClientQueue($resp);
        return;//[todo debug]

        //查询数据库,并对其它客户端发送事件，这个考虑移到redis中处理
        $result = $this->_getConversation($cid);
        $new_m = self::getOnlineSession($result['m']);
        //  发送事件 32
        //1、invited 被邀请者(在线）
        $m = self::getOnlineSession($m);
        //$this->emitJoined($genericCmd,$m);//todo debug
        // 2 发送事件 33 邀请者，被邀请者，其它人
        //$this->emitMembers_joined($genericCmd,$new_m);//todo debug
        //2、invited 85 被邀请者(好像SDK没有做判断,估计是被废弃了，这儿就不写了）
        //$this->emitInvited($genericCmd,$m);
        return true;

    }

    /**
     * 删除
     * @param $client_id
     * @param $genericCmd GenericCommand
     * @return bool|void
     */
    public function opRemove($genericCmd){
        $convMessage = $genericCmd->getConvMessage();
        $m = $convMessage->getM();
        $cid = $convMessage->getCid();
        $model = $this->_getConvModel();
        $data = $model->create(array(
            'm' => array('pullAll',$m)
        ),MongoModel::MODEL_UPDATE);
        $result = $model->where(array('_id'=>$cid))->save($data);
        \Think\Log::write($model->_sql(),'SQL');
        $this->removeUserConv($cid,$m);
        //返回本次操作结果
        $resp = new GenericCommand();
        $resp->setCmd($genericCmd->getCmd());
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $resp->setOp(OpType::removed);//11
        $message = new ConvCommand();
        $message->setCid($cid);
        $resp->setConvMessage($message);
        $this->pushClientQueue($resp);
        //查询数据库
        $result = $this->_getConversation($cid);
        $new_m = self::getOnlineSession($result['m']);
        //  发送事件 40
        //1、memberleft 邀请者，被邀请者，其它人
        $this->emitMembers_left($genericCmd,$new_m);
        //2、kicked 86 被邀请者 todo 判断是否在线
        $this->emitKicked($genericCmd);

        return true;
    }

    /**
     * 更新会话的上下线通知策略
     *  @param $client_id
     * @param $genericCmd GenericCommand
     */
    public function opStatus($genericCmd){
        $resp = $this->genericCmd;
        $resp->setOp(OpType::updated);
        //todo
        // pub 是否公开自己的上下线状态
        // sub 是否订阅该会话其他成员公开的上下线状态
        //回复
        $this->pushClientQueue($resp);
        //todo 以后要移走，这儿只是让测试通过
        $this->emitCmdPresence($genericCmd);
    }
    /**
     * 状态更新通知
     * @param $genericCmd GenericCommand
     */
    //todo 状态更新通知 要移到另外地方，这儿先调 通接口
    public function emitCmdPresence($genericCmd){
        $cid = $genericCmd->getConvMessage()->getCid();
        $status = $genericCmd->getConvMessage()->getStatusSub();
        $peerId = $genericCmd->getPeerId();
        $resp = new GenericCommand();
        $resp->setCmd(CommandType::presence);
        $resp->setPeerId($peerId);
        $msg = new \PresenceCommand();
        $msg->setCid($cid);
        $msg->setStatus($status);
        //获取聊天室的会员状态消息
        $convData = $this->_getConversation($cid);
        $m = $convData['m'];
        if(!$status){
            //unset($m[array_search($peerId,$m)]);
        }
        //获取在线的
        $m = self::getOnlineSession($m);
        foreach($m as $v){
            $msg->appendSessionPeerIds($v);
        }
        $resp->setPresenceMessage($msg);
        //todo 这儿应该群发通知所有人状态有变化
        return $this->pushClientQueue($resp);
    }

    /**
     * 静音
     * @param $genericCmd GenericCommand
     */
    public function opMute($genericCmd){
        $peerId = $genericCmd->getPeerId();
        $convMessage = $genericCmd->getConvMessage();
        $cid = $convMessage->getCid();
        //把当前用户加到对话的静音列表中
        $where = array(
            '_id'=>$cid,
            'm' => $peerId
        );
        $data = array(
            'mu'=>array('addToSet',$peerId)
        );
        $model = $this->_getConvModel();
        $data = $model->create($data,MongoModel::MODEL_UPDATE);
        $result = $model->where($where)->save($data);
        $resp = $this->genericCmd;
        $resp->setOp(OpType::updated);
        $this->pushClientQueue($resp);
    }

    /**
     * 取消静音
     * @param $genericCmd GenericCommand
     */
    public function opUnMute($genericCmd){
        $peerId = $genericCmd->getPeerId();
        $convMessage = $genericCmd->getConvMessage();
        $cid = $convMessage->getCid();
        //把当前用户加到取消对话的静音列表中
        $where = array(
            '_id'=>$cid,
            'm'=>$peerId
        );
        $data = array(
            'mu'=>array('pull',$peerId)
        );
        $model = $this->_getConvModel();
        $data = $model->create($data,MongoModel::MODEL_UPDATE);
        $result = $model->where($where)->save($data);
        $resp = $this->genericCmd;
        $resp->setOp(OpType::updated);
        $this->pushClientQueue($resp);
    }

    protected function insertUserConv($cid,$m){
        $userMsgModel = $this->_getUserConvModel();
        $list = $userMsgModel->where(array(
            'peerId'=>array('in',$m)
        ))->select() or $list = array();
        $list = array_column($list,null,'peerId');
        //查询是否存在obj_id
        foreach($m as $peerId) {
            $info = empty($list[$peerId]) ? array() : $list[$peerId];
            $info['convs'] = array_column($info['convs'],null,'convId');
            if (isset($info['convs']) && isset($info['convs'][$cid])) {
                continue;
            }
            $data = array();
            $data_conv = array();
            $data['peerId'] = $peerId;
            if($info){
                $data['_id'] = $info['_id'];
                $data_conv = $info['conv'];
            }
            $data_conv[$cid] = array(
                'convId' => $cid,
                'unread' => 0,
            );
            $data['convs'] = $data_conv;
            //插入记录
            try {
                $userMsgModel->add($data, array(), true);
            }catch (\Exception $e){
                print_r($data);
                self::E(__METHOD__.':'.$e->getMessage());
            }
        }
    }

    protected function removeUserConv($cid,$m){
        $userMsgModel = $this->_getUserConvModel();
        $list = $userMsgModel->where(array(
            'peerId'=>array('in',$m)
        ))->select();
        //查询是否存在obj_id
        if(!$list){
            return;
        }
        //批量更新
        foreach($list as $info) {
            $data = array();
            $data_conv = $info['conv'];
            unset($data_conv[$cid]);
            $data['conv'] = $data_conv;
            //更新记录
            $userMsgModel->where(array(
                '_id' => $info['_id']
            ))->save($data);
        }
    }

    /**
     * 校验会话签名
     * appid:clientid:sorted_member_ids:timestamp:nonce 创建会话
     * appid:clientid:convid:sorted_member_ids:timestamp:nonce:action 操作
     * @param $genericCmd GenericCommand
     * @param $sign String
     * @return bool|GenericCommand
     */
    public function checkConvSignature($genericCmd){
        $msg = $genericCmd->getConvMessage();
        $op = $genericCmd->getOp();
        $action = dict_value($op,array(
            \OpType::start => 'start',
            \OpType::add => 'invite',
            \OpType::join => 'invite',
            \OpType::update => 'invite',
            \OpType::remove => 'kick',
        ),'');
        if(!$action){
            return false;
        }
        $members = $msg->getM();
        $sorted_member_ids = '';
        if($members) {
            sort($members);
            $sorted_member_ids = implode(':', $members);
        }
        $app_id = C('MC_APP_ID');
        if($op == \OpType::start) {
            $text = $app_id .
                ':' . $genericCmd->getPeerId() .
                ':' . $sorted_member_ids .
                ':' . $msg->getT().
                ':' . $msg->getN();
        }
        else{
            $text = $app_id .
                ':' . $genericCmd->getPeerId() .
                ':' . $sorted_member_ids .
                ':' . $msg->getT().
                ':' . $msg->getN().
                ':'. $action;
        }
        $sign = $msg->getS();
        $servSign = self::sign($text,C('MC_APP_MASTERKEY'));
        if($sign && $sign === $servSign){
            return false;
        }
        echo colorize(print_r(array(
                    __METHOD__=>'SIGNATURE_FAILED',
                    'text'=> $text,
                    'sign'=>$sign,
                    'servSign'=>$servSign
                ),true)
                ,'FAILURE')."\r\n";

        $resp = $this->respError(4102,'SIGNATURE_FAILED');
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $resp->setAppId($genericCmd->getAppId());
        return $resp;
    }
}
<?php
namespace Daemon\Service;
use CommandType;
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
        echo __METHOD__."\r\n";
        $cmd = new self($genericCmd);
        $opType = $genericCmd->getOp();
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
            case OpType::count://50
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
        //$genericCmd->setOp(OpType::query_result);
        return $this->pushClientQueue($genericCmd);
    }

    /**
     * 创建对话
     * @param $genericCmd GenericCommand
     */
    public function opStart($genericCmd){
        $genericCmd->setOp(OpType::started);
        $convMessage = $genericCmd->getConvMessage();
        $model = Db::MongoModel('conversation');
        $m = $convMessage->getM();
        $creater = $genericCmd->getPeerId();
        $unique = $convMessage->getUnique();
        $tr = $convMessage->getTransient();
            //创建对话
        $attrData = $convMessage->getAttr()->getData();
        $attrData = json_decode($attrData,true) or $attrData = array();
        $data = array(
            'm' => $m,
            'c' => $creater,
            'unique' => $unique,
            'tr'=>$tr,
        );
        $data = array_merge($attrData,$data);
        $data = $model->create($data);
        //查询是否有维一的聊天室名称
        $unique = $convMessage->getUnique();
        if($unique){
            $resultConv = $model->where(array(
                'unique'=>true,
                'name'=>$data['name'],
                'c'=>$creater,
            ))->find();
            if($resultConv){
                $cid = $resultConv['_id'];
            }
        }
        //如果没有，则创建对话
        if(empty($cid)) {
            /** @var $result \MongoId */
            $result = $model->add($data);
            log_write($model->_sql(),__METHOD__);
            if(empty($result)){
                log_write($model->getDbError(),'SQL_ERROR');
            }
            $cid = $model->getLastInsID();
        }
        $convMessage->setCid($cid);
        $convMessage->setUdate(date(DATE_ISO8601,$data['updatedAt']->sec));
        $convMessage->setCdate(date(DATE_ISO8601,$data['createdAt']->sec));
        $this->pushClientQueue($genericCmd);
        $this->opJoined($genericCmd);
        $this->opMembers_joined($genericCmd);
    }

    /**
     * 触发加入事件
     * @param $genericCmd GenericCommand
     */
    public function opJoined($genericCmd){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::joined);
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = new ConvCommand();
        $cid = $genericCmd->getConvMessage()->getCid();
        $convMessage->setCid($cid);
        $resp->setConvMessage($convMessage);
        //通知所有在对话中的人
        $m = $genericCmd->getConvMessage()->getM();
        foreach($m as $to) {
            $resp->setPeerId($to);
            $this->pushClientQueue($resp);
        }
    }
    /**
     * 触发加入事件
     * @param $genericCmd GenericCommand
     */
    public function opMembers_joined($genericCmd){
        $resp = new GenericCommand();
        $resp->setCmd(self::CMD);
        $resp->setOp(OpType::members_joined);
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = new ConvCommand();
        $convMessage->setCid($genericCmd->getConvMessage()->getCid());
        $convMessage->setInitBy($genericCmd->getPeerId());
        $resp->setConvMessage($convMessage);
        //通知会话中的所有人
        $m = $genericCmd->getConvMessage()->getM();
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
        $model = Db::MongoModel('conversation');
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
        //压缩

        $flag = $convCommand->getFlag();
        $resultList = $model->where($where)->select();
        log_write($model->_sql(),__METHOD__);
        $data = array();
        foreach($resultList as $result){
            $cid = $result['_id'];
            unset($result['_id']);
            //如果是compact | flag= 1
            if($flag === 1){
                unset($result['m']);
            }
            //如果 withLastMessagesRefreshed 更新最后一条消息？
            //todo debug
            if($flag === 2){
                //查询最后一条消息
                $msgModel = Db::MongoModel('message');
                $msgData = $msgModel->where(array(
                    'convId'=>$cid
                ))->order('createdAt desc')->find();
                log_write($msgModel->_sql(),__METHOD__);
                if($msgData) {
                    $result['msg'] = $msgData['data'];
                    $result['lm'] = array(
                        '__type' => 'Date',
                        'iso' => date(DATE_ISO8601, $msgData['updatedAt']->sec)
                    );
                    $result['msg_from'] = $msgData['from'];
                    $result['msg_mid'] = $msgData['_id'];
                    $result['timestamp'] = self::getTimestamp($msgData['createdAt']);
                }
            }
            $data[] = array_merge($result,array(
                'updatedAt' => date(DATE_ISO8601,$result['updatedAt']->sec),
                'createdAt' => date(DATE_ISO8601,$result['createdAt']->sec),
                'lm' => empty($result['lm'])?'':date(DATE_ISO8601,$result['lm']->sec),
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
        echo __METHOD__."\r\n";
        log_write(__METHOD__);
        $resp = new GenericCommand();
        $resp->setCmd(OpType::updated);
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $convMessage = $genericCmd->getConvMessage();
        $cid = $convMessage->getCid();
        $model = Db::MongoModel('conversation');
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
        echo $model->_sql();
        log_write($model->_sql(),__METHOD__);
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
        //查找聊天室信息
        $model = Db::MongoModel('conversation');
        $result = $model->find($cid);
        log_write(print_r($result,true));
        $count = count($result['m']);
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
        //查询数据库
        $result = $this->_getConversation($cid);
        $result_m = $result['m'];
        $m = array_unique(array_merge($result_m,$m));
        $model = $this->_getConvModel();
        $data = $model->create(array(
            'm'=>$m
        ),MongoModel::MODEL_UPDATE);
        $result = $model->where(array('_id'=>$cid))->save($data);
        \Think\Log::write($model->_sql(),'SQL');
        $resp = new GenericCommand();
        $resp->setCmd($genericCmd->getCmd());
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $resp->setOp(OpType::added);//10
        return $this->pushClientQueue($resp);
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
        //查询数据库
        $result = $this->_getConversation($cid);
        $result_m = $result['m'];
        $m = array_unique(array_diff($result_m,$m));
        $model = $this->_getConvModel();
        $data = $model->create(array(
            'm'=>$m
        ),MongoModel::MODEL_UPDATE);
        $result = $model->where(array('_id'=>$cid))->save($data);
        $resp = new GenericCommand();
        $resp->setCmd($genericCmd->getCmd());
        $resp->setI($genericCmd->getI());
        $resp->setPeerId($genericCmd->getPeerId());
        $resp->setOp(OpType::removed);//10
        return $this->pushClientQueue($resp);
    }

    /**
     * 状态更新
     *  @param $client_id
     * @param $genericCmd GenericCommand
     */
    public function opStatus($genericCmd){
        $resp = $this->genericCmd;
        $resp->setOp(OpType::updated);
        //发送其它的 //todo
        $this->pushClientQueue($resp);
        //todo
        $this->_cmdPresence($genericCmd);
    }
    /**
     * 状态更新
     * @param $genericCmd GenericCommand
     */
    //todo 上提提示 要移到另外地方，这儿先调 通接口
    public function _cmdPresence($genericCmd){
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



}
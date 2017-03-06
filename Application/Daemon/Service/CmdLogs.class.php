<?php
namespace Daemon\Service;
use CommandType;
use LogsCommand;
use OpType;
use ConvCommand;
use GenericCommand;
if(!IS_CLI){
    die('NOT CLI');
}
class CmdLogs extends CmdBase {
    public $client_id;
    public $convModel;
    /** @var $genericCmd GenericCommand*/
    public $genericCmd;
    const CMD = CommandType::logs;
    /**
     * @param $genericCmd GenericCommand
     */
    public function __construct($genericCmd)
    {
        $this->genericCmd = new GenericCommand();
        $this->genericCmd->setCmd(self::CMD);
        $this->genericCmd->setPeerId($genericCmd->getPeerId());
        $this->genericCmd->setI($genericCmd->getI());
        $logsMessage = new LogsCommand();
        $this->genericCmd->setLogsMessage($logsMessage);
    }

    /**
     * @param $genericCmd GenericCommand
     * @return bool|GenericCommand
     */
    static function exeCmd($genericCmd){
        $cmd = new self($genericCmd);
        return $cmd->logsCommand($genericCmd);
    }
    /**
     * Logs
     * @param $genericCmd GenericCommand
     * @return GenericCommand|bool
     */
    public function logsCommand($genericCmd){
        $recLogsMessage = $genericCmd->getLogsMessage();
        $cid = $recLogsMessage->getCid();
        //查询消息列表
        $resp = $this->genericCmd;
        $logsMessage = new LogsCommand();
        $logsMessage->setCid($cid);
        $logsList = $this->queryMessageLog($genericCmd) or $logsList = array();
        foreach($logsList as $log){
            $logItem = new \LogItem();
            $logItem->setFrom($log['from']);
            $logItem->setMsgId($log['_id']);
            $logItem->setTimestamp(floor($log['createdAt']->sec*1000+($log['createdAt']->usec/1000)));
            $logItem->setData($log['data']);
            $logsMessage->appendLogs($logItem);
        }
        $this->genericCmd->setLogsMessage($logsMessage);
        return $this->pushClientQueue($resp);
    }

    //查询某个对话的消息
    /**
     * @param $genericCmd GenericCommand
     * @return array|mixed
     */
    protected function queryMessageLog($genericCmd){
        $peerid = $genericCmd->getPeerId();
        $recLogsMessage = $genericCmd->getLogsMessage();
        $cid = $recLogsMessage->getCid();
        $limit = $recLogsMessage->getL();
        $t = $recLogsMessage->getT();//before Time
        $tt = $recLogsMessage->getTt();//after time
        if($limit === 0){
            return array();
        }
        //默认limit 为100条
        if($limit === null){
            $limit = 100;
        }
        $where  = array(
            //'to' => $peerid,
            'convId' => $cid
        );
        if($t){
            $where['createdAt'] = array('lt',new \MongoDate(substr($t,0,10),substr($t,-3)*1000));
        }
        if($tt){
            $where['createdAt'] = array('gt',new \MongoDate(substr($tt,0,10)+3,substr($tt,-3)*1000));
        }
        $model = $this->_getMessageLogsModel();
        $result = $model->where($where)->limit($limit)->order("createdAt desc")
            ->select() or $result = array();
        //对结果按时间倒序
        $new_result = array();
        foreach($result as $v){
            $new_result[$v['createdAt']->sec.$v['createdAt']->usec.$v['msgId']] = $v;
        }
        ksort($new_result);
        $new_result = array_values($new_result);
        log_write($model->_sql(),__METHOD__);
        return $new_result;
    }

    protected function _getMessageLogsModel(){
        return Db::MongoModel('Rtm_Message');
    }
}
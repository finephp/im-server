<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use Daemon\Service\RedisService;
use \GatewayWorker\Lib\Gateway;
use Workerman\Worker;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id) {
        var_dump(__METHOD__.':'.$client_id);
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $data) {
       \Daemon\Service\RealtimeGateway::handleMessage($client_id,$data);
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       var_dump(__METHOD__.':'.$client_id);
       \Daemon\Service\RealtimeGateway::handleClose($client_id);
   }

    /**
     * @param $businessWorker \GatewayWorker\BusinessWorker
     */
    public static function onWorkerStart($businessWorker)
    {
        //注册事件
        if($businessWorker->id === 0){
            //+cleanClient
            \Daemon\Service\RealtimeGateway::clearClients();
            //添加定时器,清除 redis 中无效 clients
            \Daemon\Service\RealtimeGateway::clearClients();
            \Workerman\Lib\Timer::add(60,function(){
                \Daemon\Service\RealtimeGateway::clearClients();
            });
            //-cleanClient

            $innerWokerIp = 'frame://0.0.0.0:2208';
            //再定义一个内部消息通知接口
            // 开启一个内部端口，方便内部系统推送数据，frame 协议格式
            $inner_text_worker = new Worker($innerWokerIp);
            $inner_text_worker->count = 4;
            $inner_text_worker->onMessage = function($connection, $message)
            {
                /** @var $connection \Workerman\Connection\TcpConnection **/
                //查看是否消息广播
                if(strpos($message,'MESSAGE_BROAD:') === 0){
                    $message = substr($message,14);
                    $result = \Daemon\Service\RealtimeGateway::handleBroadMessage($message);
                    $connection->send($result ? $result : '');
                    return;
                }
                //查看是否发到聊天室中
                elseif(strpos($message,'MESSAGE_GROUP:') === 0){
                    $message = substr($message,14);
                    $result = \Daemon\Service\RealtimeGateway::handleGroupMessage($message);
                    $connection->send($result ? $result : '');
                    return;
                }
                //其它命令
                elseif(strpos($message,'CMD:') === 0){
                    $message = substr($message,4);
                    $result = \Daemon\Service\RealtimeGateway::handleCmdMessage($message);
                    $connection->send($result ? $result : '');
                    return;
                }
                echo "__________on:genericCmd:"."\n";
                \Daemon\Service\RealtimeGateway::handleClientQueue($message);
            };
            echo colorize('start inner socket:'.$innerWokerIp,'SUCCESS')."\r\n";
            // ## 执行监听 ##
            $inner_text_worker->listen();
            // end 内部通知接口
        }
    }

}
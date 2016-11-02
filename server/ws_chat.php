<?php
ob_end_clean();
use Workerman\Worker;
require_once '/php_framework/workerman/linux/workerman/Autoloader.php';
require_once 'pb_proto_message.php';
$worker = new Worker('websocket://0.0.0.0:8585');
$worker->count = 1;
$worker->onWorkerStart = function(){
	echo 'start worker'."\n";
	ob_end_flush();
};

$worker->onConnect = function($connection)
{
	echo "new connection from ip " . $connection->getRemoteIp() . "\n";
	//echo $connection->SecWebSocketProtocol."\n";
	/*print_r($connection);
	var_dump($connection->id);*/
	//$connection->send("hahahahha");
};

$worker->onMessage = function($connection, $data)
{
	$_SESSION['connection'] = $connection;
	$noBinary = isset($connection->SecWebSocketProtocol) && $connection->SecWebSocketProtocol=='lc.protobase64.3';
	if($noBinary){
		$packed = base64_decode($data);
	}
	else{
		//设置为 BINARY_TYPE_ARRAYBUFFER 格式
		$packed = $data;
		$connection->websocketType = \Workerman\Protocols\Websocket::BINARY_TYPE_ARRAYBUFFER;
	}
	
	$foo  = new GenericCommand(); 
	try { 
		$foo->parseFromString($packed); 
	} catch (Exception $e) { 
		//die('Parse error: ' . $e->getMessage()); 
		echo 'Parse error: ' . $e->getMessage();
		var_dump(base64_encode($packed));
		return;
	}
	echo "in:";
	$foo->dump();
	/*
	$resp = 'CAMiA1RvbSgGygYfKNvJ7ezWKjIWRmVpU3lraFJTV21MRkRWc0hEU2JFZw==';
	var_dump(base64_decode($resp));
	*/
	/*
	recv:GenericCommand {
  1: cmd => 2
  4: peerId => 'Jerry'
  104: directMessage =>
  DirectCommand {
    1: msg => '{"_lctext":"cccccccccccccc","_lctype":-1}'
    3: fromPeerId => 'Tom'
    4: timestamp => 1466575066451
    11: cid => '576a284f7f578500549dc549'
    12: id => 'nCj1kuRQSIywwP1RsmyHZg'
  }
}
	*/
	$appId = $foo->getAppId();
	$fromPearId = $foo->getPeerId() or $fromPearId = "guest_".md5(time().mt_rand(1,10));
	$cmd = $foo->getCmd();
	switch($cmd){
		//session 0
		case CommandType::session:
			//注册用户
			RoomMember::addMember($connection->id,$foo->getPeerId());
			$connection->session = $foo;
			//登录session
			if($foo->getSessionMessage()){
				$resp = new GenericCommand();
				$resp->setCmd(CommandType::session);
				$resp->setOp(OpType::opened);
				$resp->setAppId($foo->getAppId());
				$resp->setPeerId($fromPearId);
				$resp->setI($foo->getI());
				$sessionMessage = new SessionCommand();
				$sessionMessage->setSt('Kltanbu8Sr6TP_PD3kEWXg');
				$sessionMessage->setStTtl(17280);
				$resp->setSessionMessage($sessionMessage);
				$resp = encodeResp($resp,$noBinary);
				$connection->send($resp);
				return;
			}
		break;
		//对话操作 1
		case CommandType::conv:
			if($foo->getConvMessage()){
				$op = $foo->getOp();
				//查询聊天室 7
				if($op == OpType::query){
					$resp = new GenericCommand();
					$resp->setCmd(CommandType::conv);
					$resp->setOp(OpType::results);
					$resp->setPeerId($foo->getPeerId());
					$resp->setI($foo->getI());
					$convMessage = new ConvCommand();
					$jsonObjectMessage = new JsonObjectMessage();
					$msgData = array(array(
						'updatedAt' => '2016-06-24T03:27:36.749Z',
						'createdAt' => '2016-06-24T03:27:36.749Z',
						'name' => 'leanCloud-Conversation',
						//'objectId' => $foo->getConvMessage()->getWhere(),
						'objectId' => '576ca69ea633bd00640f2878',
						'm' => RoomMember::getNames(),
						'lm'=>array(
							'__type' =>'Date',
							'iso' =>'2016-06-24T03:27:36.726Z',
							'c' =>'tr',
							'mu' => [],
							'attr' => array('test'=>'demo2')
						)
					));
					$jsonObjectMessage->setData(json_encode($msgData));
					$convMessage->setResult($jsonObjectMessage);
					$resp->setConvMessage($convMessage);
					$connection->send(encodeResp($resp,$noBinary));
					return;
				}
				//进入聊天室 2
				elseif($op == OpType::add){
					$resp = new GenericCommand();
					$resp->setCmd(CommandType::conv);
					$resp->setOp(OpType::added);
					$resp->setPeerId($foo->getPeerId());
					$resp->setI($foo->getI());
					$convMessage = new ConvCommand();
					$convMessage->setCid($foo->getConvMessage()->getCid());
					$resp->setConvMessage($convMessage);
					$connection->send(encodeResp($resp,$noBinary));
					//群发通知
					$members = RoomMember::getMembers();
					$resp = new GenericCommand();
					$resp->setCmd(CommandType::conv);
					$resp->setOp(OpType::joined);//32
					$convMessage = new ConvCommand();
					$convMessage->setCid($foo->getConvMessage()->getCid());
					$convMessage->setInitBy('tr');
					$resp->setConvMessage($convMessage);
					print_r($members);
					foreach($connection->worker->connections as $con)
					{
						$resp->setPeerId($members[$con->id]);
						$con->send(encodeResp($resp,$noBinary));
					}
					$resp->setOp(OpType::members_joined);//33
					$convMessage->appendM('tr');
					foreach($connection->worker->connections as $con)
					{
						$resp->setPeerId($members[$con->id]);
						$con->send(encodeResp($resp,$noBinary));
					}
					return;
				}
				//更新聊天室
                elseif($op == OpType::update){
                    $resp = new GenericCommand();
                    $resp->setCmd(CommandType::error);
                    $resp->setPeerId($foo->getPeerId());
                    $resp->setI($foo->getI());
                    $errorMesage = new ErrorCommand();
                    $errorMesage->setCode(1);
                    $errorMesage->setReason("CONVERSATION_UPDATE_REJECTED");
                    $resp->setErrorMessage($errorMesage);
                    $resp = encodeResp($resp,$noBinary);
                    $connection->send($resp);
                    return;
                }
				$resp = new GenericCommand();
				$resp->setCmd(1);
				$resp->setOp(31);
				$resp->setPeerId($foo->getPeerId());
				$resp->setI($foo->getI());
				$convMessage = new ConvCommand();
				$convMessage->setCid('576ca69ea633bd00640f2878');
				$convMessage->setCdate(date('Y-m-dTH:i:s'));
				$resp->setConvMessage($convMessage);
				$resp = encodeResp($resp,$noBinary);
				$connection->send($resp);

				/*
                $resp = new GenericCommand();
                $peerId = $foo->getPeerId();
                $resp->setCmd(2);
                $resp->setI($foo->getI());
                $resp->setAppId($foo->getAppId());
                $resp->setPeerId($foo->getPeerId());
                $directMessage = new DirectCommand();
                $directMessage->setMsg('{"_lctext":"hello "'.$peerId.',"_lctype":-1}');
                $directMessage->setFromPeerId($peerId);
                $directMessage->setTimestamp(time());
                $directMessage->setCid('1');
                $directMessage->setId('nCj1kuRQSIywwP1RsmyHZg');
                $resp->setDirectMessage($directMessage);
                $resp = $resp->serializeToString();
                $resp = base64_encode($resp);
                $connection->send($resp);
                */
				return;
			}
			break;

		//收到对话
		case 2:
			//收到聊天
			if($foo->getDirectMessage()){
				//响应回去 ack
				$resp = new GenericCommand();
				$resp->setCmd(CommandType::ack);//3
				$resp->setPeerId($foo->getPeerId());
				$resp->setI($foo->getI());
				$ackMessage = new AckCommand();
				$ackMessage->setT(getMillisecond());
				$ackMessage->setUid('hmRL7eUpThObDr72zpa3fw');
				$resp->setAckMessage($ackMessage);
				$connection->send(encodeResp($resp,$noBinary));

				$resDirectMessage = $foo->getDirectMessage();
				//转发消息
				$cid = $resDirectMessage->getCid();
				$msg = $resDirectMessage->getMsg();
				$resp = new GenericCommand();
				$resp->setCmd(CommandType::direct);//2
				$directMessage = new DirectCommand();
				//$directMessage->setMsg('{"_lctext":"hello '.$fromPearId.'","_lctype":-1}');
				$directMessage->setMsg($msg);
				$directMessage->setFromPeerId($fromPearId);
				$directMessage->setTimestamp(getMillisecond());
				$directMessage->setCid($cid);
				$directMessage->setId('nCj1kuRQSIywwP1RsmyHZg');
				$resp->setDirectMessage($directMessage);
				//转发给所有连接的客户端
				$members = RoomMember::getMembers();
				foreach($connection->worker->connections as $con)
				{
					//跳过自已
					if($con->id == $connection->id){
						continue;
					}
					$resp->setPeerId($members[$con->id]);
					$con->send(encodeResp($resp,$noBinary));
				}
			}
			break;
		case 6: //
			$resp = new GenericCommand();
			$resp->setCmd(CommandType::logs);
			$resp->setI($foo->getI());
			$resp->setPeerId($foo->getPeerId());
			$logMessage = new LogsCommand();
			$logMessage->appendLogs(new LogItem());
			$resp->setLogsMessage(new LogsCommand());
			$connection->send(encodeResp($resp,$noBinary));
			break;
		// 10.	rcp 保持心跳？
		case 14:
			$connection->send(encodeResp($foo,$noBinary));
			break;
		// 收到响应
		case 3:
			echo 'ack:'.CommandType::ack."\r\n";
			break;
		default:
			echo 'undefined:'.$cmd;
			break;
	}




};
function parseMsg($msg){
	$msg = json_decode($msg);
	return $msg;
}

function encodeResp($resp,$noBinary){
	$new_resp = $resp->serializeToString();
	$new_resp .= pack('H*','EA0600');
	if($noBinary) {
		$new_resp = base64_encode($new_resp);
	}
	$connection = $_SESSION['connection'];
	echo "out: connection id:".$connection->id.':';
	$resp->dump();
	return $new_resp;
}
class RoomMember{
	static $members = array();
	static function addMember($id,$name){
		self::$members[$id] = $name;
	}
	static function getMembers(){
		return self::$members;
	}
	static function getNames(){
		return array_values(self::$members);
	}
}

function getMillisecond() {
	list($s1, $s2) = explode(' ', microtime());
	return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}
// 运行worker
Worker::runAll();

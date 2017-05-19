<?php
namespace Daemon\Service;
class HookService{
    public static function log($info1,$info2 = null){
        echo print_r($info1,true),' ',print_r($info2,true),"\r\n";
    }
    /**
     * @param $genericCmd \GenericCommand
     */
    public static function messageReceived($genericCmd)
    {
        //如果没有设置云函数地址，则直接返回
        $cloudUrl = C('CLOUD_URL');
        if(empty($cloudUrl)){
            return;
        }
        // request.params = {
        //     fromPeer: 'Tom',
        //     receipt: false,
        //     groupId: null,
        //     system: null,
        //     content: '{"_lctext":"耗子，起床！","_lctype":-1}',
        //     convId: '5789a33a1b8694ad267d8040',
        //     toPeers: ['Jerry'],
        //     __sign: '1472200796787,a0e99be208c6bce92d516c10ff3f598de8f650b9',
        //     bin: false,
        //     transient: false,
        //     sourceIP: '121.239.62.103',
        //     timestamp: 1472200796764
        // };
        $directMsg = $genericCmd->getDirectMessage();
        $params = array(
            'fromPeer' => $genericCmd->getPeerId(),
            'receipt' => $directMsg->getR(),
             'groupId' => null,
             'system' => null,
             'content'=> $directMsg->getMsg(),
             'convId' => $directMsg->getCid(),
             'toPeers'=>$directMsg->getToPeerIds(),
             '__sign' =>  '',
             'bin' => false,
             'transient' => $directMsg->getTransient(),
             'sourceIP' => '',
             'timestamp' => $directMsg->getTimestamp()
        );
        $url = $cloudUrl.'/messageReceived';
        self::log(__METHOD__);
        self::log(json_encode($params));
        $result = curl_request($url,json_encode($params),$error,array(
            'HEADERS'=>array(
                //'Authorization: JWT eyJ0eXAiOiJKV1QiLCJhUzI1NiJDIxL',
                'Content-type: application/json;charset=utf-8',
            ),
            'METHOD' => 'POST',

        ));
        self::log($url,$result);
        self::log($error);
        if(empty($result)){
            return;
        }
        $result = json_decode($result,true);
        if($result){
            if(isset($result['fromPeer'])){
                $genericCmd->setPeerId($result['fromPeer']);
            }
            if(isset($result['receipt'])){
                $directMsg->setR($result['receipt']);
            }
            if(isset($result['content'])){
                $directMsg->setMsg($result['content']);
            }
            if(isset($result['convId'])){
                $directMsg->setCid($result['convId']);
            }
            if(isset($result['transient'])){
                $directMsg->setTransient($result['transient']);
            }
            if(isset($result['toPeers'])){
                $directMsg->clearToPeerIds();
                foreach( $result['toPeers'] as $v) {
                    $directMsg->appendToPeerIds($v);
                }
            }
        }
    }

}
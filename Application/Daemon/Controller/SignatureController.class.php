<?php
namespace Daemon\Controller;
use Think\Controller;
if(!IS_CLI){
    die('NOT CLI');
}
class SignatureController extends Controller {
    /**
     * 登录签名
     * appid:clientid::timestamp:nonce
     */
    public $masterKey = 'master_key';
    public $APPID;
    public function sessSignature(){
        $client_id = I('client_id');
        $app_id = I('app_id');
        $timestamp = time();
        $nonce = mt_rand(0,999);
        $str = $app_id.':'.$client_id.'::'.$timestamp.':'.$nonce;
        $key = $this->masterKey;
        $sign = $this->sign($str,$key);
        $signResult = array(
            'signature' => $sign,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'msg' => $str,
        );
        echo json_decode($signResult);
    }

    public function convSignature(){
        $client_id = I('client_id');
        $conv_id = I('conv_id');
        $member_ids = I('members',[]);
        $action = I('action');
        $ts = time();
        $nonce = mt_rand(0,999);

        $msg = [$this->APPID, $client_id];
        if ($conv_id) {
            array_push($msg,$conv_id);
        }

        if ($member_ids) {
            sort($member_ids);
            array_push($msg,implode(':',$member_ids));
        } else {
            array_push($msg,'');
        }
        array_push($msg,$ts);
        array_push($msg,$nonce);
        if ($action) {
            array_push($msg,$action);
        }
        $msg = implode(':',$msg);
        $sig = $this->sign($msg, $this->masterKey);
        $signResult = array(
            'signature' => $sig,
            'timestamp' => $ts,
            'nonce' => $nonce,
            'msg' => $msg,
        );
        echo json_decode($signResult);
    }

    /**
     * 签名
     */
    protected function sign($str,$key){

    }
}
<?php
$config = array();
if(IS_WIN){
    $config['WORKERMAN_PATH'] = '/php_framework/workerman/windows/workerman/Autoloader.php';
}
else{
    $config['WORKERMAN_PATH'] = '/php_framework/workerman/linux/workerman/Autoloader.php';
}
define('WORKERMAN_PATH',$config['WORKERMAN_PATH']);
//获取worker的进程数的环境变量
$config['APP_WORKER_COUNT'] = intval(getenv('APP_WORKER_COUNT')?:1);
$config['GATEWAY_REGISTER_URL'] = '127.0.0.1:1236';
$config['GATEWAY_LANIP'] = '127.0.0.1'; //如果是分布式的话不允许是127.0.0.1 ，必须是内网ip
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
$config['GATEWAY_START_PORT'] = 4000;
//服务注册地址 内网ip加端口（1236)
getenv('GATEWAY_REGISTER_URL') && $config['GATEWAY_REGISTER_URL'] = getenv('GATEWAY_REGISTER_URL');
getenv('GATEWAY_LANIP') && $config['GATEWAY_LANIP'] = getenv('GATEWAY_LANIP');
getenv('GATEWAY_START_PORT') && $config['GATEWAY_START_PORT'] = getenv('GATEWAY_START_PORT');
getenv('GATEWAY_LANIP_INNER') && $config['GATEWAY_LANIP_INNER'] = getenv('GATEWAY_LANIP_INNER');

//是否要签名校验
getenv('SIGNATURE_FLAG') && $config['SIGNATURE_FLAG'] = getenv('SIGNATURE_FLAG')==='true' ? true:false;
getenv('MC_APP_MASTERKEY') && $config['MC_APP_MASTERKEY'] = getenv('MC_APP_MASTERKEY');
getenv('MC_APP_ID') && $config['MC_APP_ID'] = getenv('MC_APP_ID');
//载入测试平台参数
if(C('DEVEL_API_CONF')){
    $config = array_merge($config,C('DEVEL_API_CONF'));
}
define('IM_DEBUG',getenv('IM_DEBUG')=='1'?true:false);
return $config;
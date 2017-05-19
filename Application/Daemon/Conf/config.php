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
//服务注册地址
getenv('GATEWAY_REGISTER_URL') && $config['GATEWAY_REGISTER_URL'] = getenv('GATEWAY_REGISTER_URL');
//载入测试平台参数
if(C('DEVEL_API_CONF')){
    $config = array_merge($config,C('DEVEL_API_CONF'));
}
return $config;
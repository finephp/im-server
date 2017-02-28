<?php
$config = array();
if(IS_WIN){
    $config['WORKERMAN_PATH'] = '/php_framework/workerman/windows/workerman/Autoloader.php';
}
else{
    $config['WORKERMAN_PATH'] = '/php_framework/workerman/linux/workerman/Autoloader.php';
}
define('WORKERMAN_PATH',$config['WORKERMAN_PATH']);
//载入测试平台参数
if(C('DEVEL_API_CONF')){
    $config = array_merge($config,C('DEVEL_API_CONF'));
}
return $config;
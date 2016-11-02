<?php
$config = array();
if(IS_WIN){
    $config['WORKERMAN_PATH'] = '/php_framework/workerman/windows/workerman/Autoloader.php';
}
else{
    $config['WORKERMAN_PATH'] = '/php_framework/workerman/linux/workerman/Autoloader.php';
}
$config['DB_CONFIG_MONGO'] = array(
    'DB_TYPE'=>'mongo',
    'DB_HOST'=>'10.30.0.23',
    //'DB_HOST'=>'127.0.0.1',
    'DB_PORT'=>'27017',
    'DB_NAME'=> 'Realtime',
    'DB_USER'=>'',
    'DB_PWD'=>'',
);
$config['REDIS_CONFIG'] = array(
    'HOST' => '127.0.0.1',
    'PORT' => '16379',
);
define('WORKERMAN_PATH',$config['WORKERMAN_PATH']);
//载入测试平台参数
if(C('DEVEL_API_CONF')){
    $config = array_merge($config,C('DEVEL_API_CONF'));
}
return $config;
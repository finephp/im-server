<?php
$config = array();
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

//环境变量 start
//设置环境变量
if(getenv('REDIS_HOST')){
    $config['REDIS_CONFIG']['HOST'] = getenv('REDIS_HOST');
}
//设置环境变量
if(getenv('REDIS_PORT')){
    $config['REDIS_CONFIG']['PORT'] = getenv('REDIS_PORT');
}

//设置环境变量
if(getenv('DB_HOST')){
    $config['DB_CONFIG_MONGO']['DB_HOST'] = getenv('DB_HOST');
}

//设置环境变量
if(getenv('DB_PORT')){
    $config['DB_CONFIG_MONGO']['DB_PORT'] = getenv('DB_PORT');
}

return $config;
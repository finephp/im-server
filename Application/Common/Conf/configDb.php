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

//设置环境变量
if(getenv('DB_NAME')){
    $config['DB_CONFIG_MONGO']['DB_NAME'] = getenv('DB_NAME');
}

//设置环境变量
if(getenv('DB_PWD')){
    $config['DB_CONFIG_MONGO']['DB_PWD'] = getenv('DB_PWD');
}

//设置环境变量
if(getenv('DB_DSN')){
    //mongodb://10.30.0.23:37017,10.30.0.23:47017
    $config['DB_CONFIG_MONGO']['DB_DSN'] = getenv('DB_DSN');
}


//环境变量云函数地址
if(getenv('CLOUD_URL')){
    $config['CLOUD_URL'] = getenv('CLOUD_URL');
}
//环境变量hook名称列表
if(getenv(('HOOK_NAMES'))){
    //cjs,dxt
    $config['HOOK_NAMES'] = getenv('HOOK_NAMES');
    $arr = explode(',',$config['HOOK_NAMES']);
    $config['HOOK_URLS'] = array();
    foreach($arr as $k=>$v){
        if(getenv('HOOK_URL_'.$k)){
            $config['HOOK_URLS'][$v] = getenv('HOOK_URL_'.$k);
        }
    }
}
return $config;
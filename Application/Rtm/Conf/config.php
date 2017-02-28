<?php
$config = array();
$config['RTM_SOCKET_URL'] = '127.0.0.1:2208';

if(getenv('IM_SOCKET_HOST')){
    $config['RTM_SOCKET_URL'] = getenv('IM_SOCKET_HOST').':'.getenv('IM_SOCKET_PORT');
}
return $config;
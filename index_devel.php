<?php
//测试环境入口
if(!defined('ENV_DEVELOPMENT')) die('非法进入');
//本机调试环境
if(!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'],'localhost') !== false){
	define('ENV_LOCAL',true);
	require('index_local.php');
	exit;
}
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
define('APP_NAME', 'QiankunRealtimeServer');
define('RUNTIME_PATH', '/tmp/runtime/devel/'.APP_NAME.'/');
//设置session 规则
define('COOKIE_DOMAIN','qiankun.com');
session_name(APP_NAME.'SESSID');
session_set_cookie_params (null,'/','.'.COOKIE_DOMAIN,NULL,TRUE);

//define('BIND_MODULE','Home');
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);
// 定义应用目录
define('APP_PATH',__DIR__.'/Application/');
// 引入ThinkPHP入口文件
require( "/php_framework/ThinkPHP3.2.3/ThinkPHP.php");

// 亲^_^ 后面不需要任何代码了 就是如此简单
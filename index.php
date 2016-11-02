<?php
//生产环境入口
//putenv('APP_RUN_ENV=development');//本机专用
define('ENV_DEVELOPMENT',(getenv('APP_RUN_ENV')=='development'));
//如果是开发环境
if(ENV_DEVELOPMENT){
    require('index_devel.php');
    exit;
}
//app-start
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
// 定义应用目录
define('APP_NAME', 'QiankunRealtimeServer');
define('APP_PATH',__DIR__.'/Application/');
define('RUNTIME_PATH', '/tmp/runtime/devel/'.APP_NAME.'/');
//设置session 规则 不同的设置开始
define('COOKIE_DOMAIN','');
session_name(APP_NAME.'SESSID');
session_set_cookie_params (null,'/','.'.COOKIE_DOMAIN,NULL,TRUE);
// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);
// 引入ThinkPHP入口文件
require( "/php_framework/ThinkPHP3.2.3/ThinkPHP.php");

// 亲^_^ 后面不需要任何代码了 就是如此简单

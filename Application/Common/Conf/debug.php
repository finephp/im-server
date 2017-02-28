<?php
//默认系统配置，不要随便修改
$config	= array(
    //日志配置
    'LOG_EXCEPTION_RECORD'  =>  true,    // 是否记录异常信息日志
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR,WARN,NOTIC,SQL',
    'SHOW_PAGE_TRACE'       => false,
);
return $config;
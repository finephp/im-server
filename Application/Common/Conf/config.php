<?php
$config = array(
'DEFAULT_CHARSET' => 'utf-8',
    'VAR_PATHINFO' => '_s',
    'URL_MODEL'=>2,
    'DEFAULT_MODULE' =>'Home',
    'TMPL_FILE_DEPR' => '_',
    'DB_FIELDS_CACHE' => true,
    'DB_FIELDTYPE_CHECK' => true,
    'COOKIE_PREFIX'=>APP_NAME.'_',
    'COOKIE_PATH'=>'/',
    'COOKIE_EXPIRE'=>'',
    'COOKIE_DOMAIN'=>'.'.COOKIE_DOMAIN,
    'VAR_PAGE' => 'p',
    'SHOW_PAGE_TRACE' =>false,
    'URL_CASE_INSENSITIVE'=>false,
    'URL_HTML_SUFFIX'=>'',
    'UPLOAD_PATH'=>'./Uploads/',
    'UPLOAD_BATCH_PATH'=>'./Uploads/marketBatch/',
    'DEFAULT_FILTER' => 'htmlspecialchars,input_filter',
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR,WARN,NOTIC,SQL',
    'LOAD_EXT_CONFIG' =>    'configDb',
);
return $config;
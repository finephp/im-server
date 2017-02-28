<?php
/**
 * Created by PhpStorm.
 * User: wangtr
 * Date: 2016/12/20
 * Time: 13:28
 */

namespace Rtm\Controller;

use Think\Route;
use Think\Think;

class Dispatcher {
    public static $MODULE_PATHINFO_DEPR;
    public static $__INFO__;
    public static $__EXT__;
    public static $MODULE_NAME;
    public static $MODULE_PATH;
    public static $BIND_MODULE;
    public static $BIND_CONTROLLER;
    public static $CONTROLLER_NAME;
    public static $ACTION_NAME;
    public static $BIND_ACTION;
    public static $MODULE_ALIAS;
    public static $APP_DOMAIN;
    public static $SUB_DOMAIN;
    public static $CONTROLLER_ALIAS;
    public static $__CONTROLLER__;
    public static $__MODULE__;
    public static $ACTION_ALIAS;
    public static $__ACTION__;

    /**
     * URL映射到控制器
     * @param $path_info string
     * @access public
     * @return void
     */
    static public function dispatch($path_info = null) {
        $varPath        =   C('VAR_PATHINFO');
        $varModule      =   C('VAR_MODULE');
        $varController  =   C('VAR_CONTROLLER');
        $varAction      =   C('VAR_ACTION');
        $urlCase        =   C('URL_CASE_INSENSITIVE');
        if(isset($_GET[$varPath])) { // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO'] = $_GET[$varPath];
            unset($_GET[$varPath]);
        }
        if(!empty($path_info)) {
            $_SERVER['PATH_INFO'] = $path_info;
        }
        // 开启子域名部署
        if(C('APP_SUB_DOMAIN_DEPLOY')) {
            $rules      = C('APP_SUB_DOMAIN_RULES');
            if(isset($rules[$_SERVER['HTTP_HOST']])) { // 完整域名或者IP配置
                self::$APP_DOMAIN = $_SERVER['HTTP_HOST']; // 当前完整域名
                $rule = $rules[APP_DOMAIN];
            }else{
                if(strpos(C('CLI_APP_DOMAIN_SUFFIX'),'.')){ // com.cn net.cn
                    $domain = array_slice(explode('.', $_SERVER['HTTP_HOST']), 0, -3);
                }else{
                    $domain = array_slice(explode('.', $_SERVER['HTTP_HOST']), 0, -2);
                }
                if(!empty($domain)) {
                    $subDomain = implode('.', $domain);
                    self::$SUB_DOMAIN = $subDomain; // 当前完整子域名
                    $domain2   = array_pop($domain); // 二级域名
                    if($domain) { // 存在三级域名
                        $domain3 = array_pop($domain);
                    }
                    if(isset($rules[$subDomain])) { // 子域名
                        $rule = $rules[$subDomain];
                    }elseif(isset($rules['*.' . $domain2]) && !empty($domain3)){ // 泛三级域名
                        $rule = $rules['*.' . $domain2];
                        $panDomain = $domain3;
                    }elseif(isset($rules['*']) && !empty($domain2) && 'www' != $domain2 ){ // 泛二级域名
                        $rule      = $rules['*'];
                        $panDomain = $domain2;
                    }
                }
            }

            if(!empty($rule)) {
                // 子域名部署规则 '子域名'=>array('模块名[/控制器名]','var1=a&var2=b');
                if(is_array($rule)){
                    list($rule,$vars) = $rule;
                }
                $array      =   explode('/',$rule);
                // 模块绑定
                self::$BIND_MODULE = array_shift($array);
                // 控制器绑定
                if(!empty($array)) {
                    $controller  =   array_shift($array);
                    if($controller){
                        self::$BIND_CONTROLLER = $controller;
                    }
                }
                if(isset($vars)) { // 传入参数
                    parse_str($vars,$parms);
                    if(isset($panDomain)){
                        $pos = array_search('*', $parms);
                        if(false !== $pos) {
                            // 泛域名作为参数
                            $parms[$pos] = $panDomain;
                        }
                    }
                    $_GET   =  array_merge($_GET,$parms);
                }
            }
        }
        // 分析PATHINFO信息
        if(!isset($_SERVER['PATH_INFO'])) {
            $types   =  explode(',',C('URL_PATHINFO_FETCH'));
            foreach ($types as $type){
                if(!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type],$_SERVER['SCRIPT_NAME']))?
                        substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME']))   :  $_SERVER[$type];
                    break;
                }
            }
        }
        if(empty($_SERVER['PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = '';
        }
        $depr = C('URL_PATHINFO_DEPR');
        self::$MODULE_PATHINFO_DEPR =  $depr;
        self::$__INFO__ = trim($_SERVER['PATH_INFO'],'/');
        // URL后缀
        self::$__EXT__ = strtolower(pathinfo($_SERVER['PATH_INFO'],PATHINFO_EXTENSION));

        $_SERVER['PATH_INFO'] = self::$__INFO__;

        if (self::$__INFO__){ // 获取模块名
            $paths      =   explode($depr,self::$__INFO__,2);
            $allowList  =   C('MODULE_ALLOW_LIST'); // 允许的模块列表
            $module     =   preg_replace('/\.' . self::$__EXT__ . '$/i', '',$paths[0]);
            if( empty($allowList) || (is_array($allowList) && in_array_case($module, $allowList))){
                $_GET[$varModule]       =   $module;
                $_SERVER['PATH_INFO']   =   isset($paths[1])?$paths[1]:'';
            }
        }
        // 获取模块名称
        self::$MODULE_NAME = self::getModule($varModule);
        // 检测模块是否存在
        if( self::$MODULE_NAME && (!in_array_case(self::$MODULE_NAME,C('MODULE_DENY_LIST'))) && is_dir(APP_PATH.self::$MODULE_NAME)){
            // 定义当前模块路径
            self::$MODULE_PATH = APP_PATH.self::$MODULE_NAME.'/';
            // 定义当前模块的模版缓存路径
            C('CACHE_PATH',CACHE_PATH.self::$MODULE_NAME.'/');

            // 加载模块配置文件
            if(is_file(self::$MODULE_PATH.'Conf/config.php'))
                C(include self::$MODULE_PATH.'Conf/config.php');
            // 加载模块别名定义
            if(is_file(self::$MODULE_PATH.'Conf/alias.php'))
                Think::addMap(include self::$MODULE_PATH.'Conf/alias.php');
            // 加载模块函数文件
            if(is_file(self::$MODULE_PATH.'Common/function.php'))
                include self::$MODULE_PATH.'Common/function.php';
        }else{
            E(L('_MODULE_NOT_EXIST_').':'.self::$MODULE_NAME);
        }

        if('' != $_SERVER['PATH_INFO']  && (!C('URL_ROUTER_ON') ||  !Route::check()) ){   // 检测路由规则 如果没有则按默认规则调度URL
            // 检查禁止访问的URL后缀
            if(C('URL_DENY_SUFFIX') && preg_match('/\.('.trim(C('URL_DENY_SUFFIX'),'.').')$/i', $_SERVER['PATH_INFO'])){
                E('URL_DENY_SUFFIX:404');
                exit;
            }

            // 去除URL后缀
            $_SERVER['PATH_INFO'] = preg_replace(C('URL_HTML_SUFFIX')? '/\.('.trim(C('URL_HTML_SUFFIX'),'.').')$/i' : '/\.'.__EXT__.'$/i', '', $_SERVER['PATH_INFO']);

            $depr   =   C('URL_PATHINFO_DEPR');
            $paths  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));

            if(!isset(self::$BIND_CONTROLLER)) {// 获取控制器
                $_GET[$varController]   =   array_shift($paths);
            }
            // 获取操作
            if(!isset(self::$BIND_ACTION)){
                $_GET[$varAction]  =   array_shift($paths);
            }
            // 解析剩余的URL参数
            $var  =  array();
            if(C('URL_PARAMS_BIND') && 1 == C('URL_PARAMS_BIND_TYPE')){
                // URL参数按顺序绑定变量
                $var    =   $paths;
            }else{
                preg_replace_callback('/(\w+)\/([^\/]+)/', function($match) use(&$var){$var[$match[1]]=strip_tags($match[2]);}, implode('/',$paths));
            }
            $_GET   =  array_merge($var,$_GET);
        }
        // 获取控制器的命名空间（路径）
        self::$CONTROLLER_NAME = self::$BIND_CONTROLLER? self::$BIND_CONTROLLER : self::getController($varController,$urlCase);
        self::$ACTION_NAME =  self::$BIND_ACTION ? self::$BIND_ACTION : self::getAction($varAction,$urlCase);

        // 当前控制器的UR地址
        $controllerName    =   self::$CONTROLLER_ALIAS ? self::$CONTROLLER_ALIAS : self::$CONTROLLER_NAME;
        self::$__CONTROLLER__ = self::$__MODULE__.$depr.(self::$BIND_CONTROLLER ? '': ( $urlCase ? parse_name($controllerName) : $controllerName ));

        // 当前操作的URL地址
        self::$__ACTION__ = self::$__CONTROLLER__.$depr.(self::$ACTION_ALIAS ? self::$ACTION_ALIAS:self::$ACTION_NAME);
        //设置tp的全局参数
        $_SERVER['DISPATCHER'] = array(
            'MODULE_NAME' => self::$MODULE_NAME,
            'CONTROLLER_NAME' => self::$CONTROLLER_NAME,
            'ACTION_NAME' => self::$ACTION_NAME,
            '__ACTION__' => self::$__ACTION__,
        );
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST,$_GET);
    }

    /**
     * 获得实际的控制器名称
     */
    static private function getController($var,$urlCase) {
        $controller = (!empty($_GET[$var])? $_GET[$var]:C('DEFAULT_CONTROLLER'));
        unset($_GET[$var]);
        if($urlCase) {
            // URL地址不区分大小写
            // 智能识别方式 user_type 识别到 UserTypeController 控制器
            $controller = parse_name($controller,1);
        }
        return strip_tags(ucfirst($controller));
    }

    /**
     * 获得实际的操作名称
     */
    static private function getAction($var,$urlCase) {
        $action   = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_ACTION'));
        unset($_POST[$var],$_GET[$var]);
        return strip_tags($urlCase?strtolower($action):$action);
    }

    /**
     * 获得实际的模块名称
     */
    static private function getModule($var) {
        $module   = (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_MODULE'));
        unset($_GET[$var]);
        if($maps = C('URL_MODULE_MAP')) {
            if(isset($maps[strtolower($module)])) {
                // 记录当前别名
                self::$MODULE_ALIAS  = strtolower($module);
                // 获取实际的模块名
                return   ucfirst($maps[MODULE_ALIAS]);
            }elseif(array_search(strtolower($module),$maps)){
                // 禁止访问原始模块
                return   '';
            }
        }
        return strip_tags(ucfirst(strtolower($module)));
    }

}
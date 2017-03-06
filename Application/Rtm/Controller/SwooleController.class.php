<?php
/***
 * 这儿是restful接口，专门用来做通知的
 */
namespace Rtm\Controller;
use Think\Controller;
use Think\Exception;
use Workerman\Protocols\Http;
use workerman\Worker;
if(!IS_CLI){
    die('NOT CLI');
}
class SwooleController extends Controller {
    static $_SERVER = array();
    public function index(){
        echo 'run worker';
    }

    public function _initialize(){
        if(getenv('IM_SOCKET_HOST')){
            C('RTM_SOCKET_URL',getenv('IM_SOCKET_HOST').':'.getenv('IM_SOCKET_PORT'));
        }
        var_dump(C('RTM_SOCKET_URL'));
    }
    public function worker(){
        self::$_SERVER = $_SERVER;
        $serv = new \Swoole\Http\Server("0.0.0.0", 8081);
        $serv->set(array(
            'daemonize'=>0
        ));
        $serv->on('Request', function($request, $response) {
            //请求过滤
            if($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico'){
                return $response->end();
            }
            /**
             * var_dump($request->get);
            var_dump($request->post);
            var_dump($request->cookie);
            var_dump($request->files);
            var_dump($request->header);
            var_dump($request->server);

            $response->cookie("User", "Swoole");
            $response->header("X-Server", "Swoole");
            $response->end("<h1>Hello Swoole!</h1>");
             */
            if(!empty($request->header['accept-encoding']) && strpos($request->header['accept-encoding'],'gzip')!== false){
                $response->gzip(1);
            }
            $this->onMessage($request,$response);
        });
        $serv->on('start',function($serv){
            $title = "RealTime Swoole Server";
            cli_set_process_title($title);
            $this->show($title.' start at:'.$serv->host.':'.$serv->port);
            echo "\r\n";
        });
        $serv->start();
    }

    /**
     * 当触发消息时分派消息
     * @param $connection \Workerman\Connection\TcpConnection
     * @param $data
     */
    public function onMessage($request, $response){
        $this->parseRequest($request);
        $path = $_SERVER['PATH_INFO'];
        //var_dump($request->get);
        //var_dump($request->post);
        //var_dump($request->cookie);
        //var_dump($request->files);
        //var_dump($request->header);
        //var_dump($request->server);
        //$response->cookie("User", "Swoole");
        $response->header("X-Server", "Swoole");
        //获取api内容
        $resp = $this->getApiData();
        if($resp === false) {
            //$response->header();
            $response->status(404);
            $response->end('[404]'.$path. ' not found.'."\r\n");
            return;
        }
        $response->end($resp);
    }

    protected function parseRequest($request){
        $_GET = isset($request->get) ? $request->get : array();
        $_POST = isset($request->post) ? $request->post : array();
        $_SERVER = array_merge(self::$_SERVER,array_change_key_case($request->server,CASE_UPPER));
        //处理一下header
        foreach($request->header as $k=>$v){
            $k = strtoupper(str_replace('-','_',$k));
            if($k=='CONTENT_TYPE' || $k=='CONTENT_LENGTH'){
                $_SERVER[$k] = $v;
            }
            $_SERVER['HTTP_'.$k] = $v;
        }
        if(!empty($_SERVER['HTTP_CONTENT_LENGTH'])){
            $_SERVER['_PHP_INPUT'] = substr(strstr($request->data,"\r\n\r\n"),4);
        }
        $url = parse_url($request->server['request_uri']);
        $path = $url['path'];
        $_SERVER['PATH_INFO'] = $path;
    }

    //get data from path
    protected function getApiData($path=''){
        if(!empty($path)){
            $_SERVER['PATH_INFO'] = $path;
        }
        return self::run();
    }

    public function testWork(){
        $argv = $_SERVER['argv'];
        $data = $argv[2];
        $url = parse_url($data);
        $path = $url['path'];
        $query = $url['query'];
        parse_str($query,$_GET);
        if(empty($_GET)){
            print_r('GET is empty');
            echo "\r\n";
        }
        //获取api内容
        $resp = $this->getApiData($path);
        var_dump($resp);
        echo "\r\n";
    }
    /**
     * 运行应用实例 入口文件使用的快捷方法
     * @access public
     * @return String
     */
    static public function run() {
        //init start
        // URL调度
        try {
            Dispatcher::dispatch();
        }catch (\Exception $e){
            return  $e->getMessage();
        }
        if(C('REQUEST_VARS_FILTER')){
            // 全局安全过滤
            array_walk_recursive($_GET,		'think_filter');
            array_walk_recursive($_POST,	'think_filter');
            array_walk_recursive($_REQUEST,	'think_filter');
        }
        G('initTime');
        // init end
        try{
            $actionPath = Dispatcher::$MODULE_NAME.'/'.Dispatcher::$CONTROLLER_NAME.'/'.Dispatcher::$ACTION_NAME;
            $resp = R($actionPath);
            return $resp;
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

}
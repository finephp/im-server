<?php
/*----公共函数库开始 -----*/
/**
 * 记录文本日志
 * @author tr
 * @param string $content 内容
 * @param string $level 级别
 * @param string $log_name 日志文件名（不包括日期）
 * */

function log_write($content,$level='',$log_name = ''){
    static $__APP_LOG_PID__;//进程号
    //$first = '[PAGE:'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME.']';
    $first = '';
    if(!$__APP_LOG_PID__){
        $__APP_LOG_PID__ = '[PID:'.mt_rand(1000,9999).']';
        $first .= '[IP:'.get_client_ip().']';
    }
    if(!C('CUSTOM_LOG_PATH')){
        C('CUSTOM_LOG_PATH',C('LOG_PATH'));
    }
    $destination = C('CUSTOM_LOG_PATH').'app_'.$log_name.date('Ymd').'.log';
    //如果内容是空,且level也是false,则输出日志地址
    if($level === false){
        echo $destination."\r\n";
        return;
    }
    trace($content,$level);
    \Think\Log::write($content,$__APP_LOG_PID__.$first.$level,'',$destination);
}

function safe_replace($string) {
	$string = trim($string);
	$string = str_replace(array('\\',';','\'','%2527','%27','%20','&', '"', '<', '>'), array('','','','','','','&amp;', '&quot;', '&lt;', '&gt;'), $string);
	$string=nl2br($string); 
	return $string;
}
function get_safe_replace($array){
	if(!is_array($array)) return safe_replace(strip_tags($array));
	foreach($array as $k => $val) $array[$k] = get_safe_replace($val);
	return $array;
}

function get_current_url(){
    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'];
    $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']);
    return $url;
}

function U2($url='',$vars='',$suffix=true,$domain=false){
    if($domain === false){
        $url = U($url,'',$suffix,$domain);
    }
    elseif($domain === true){
        $url_pre = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
        $url_pre .= $_SERVER['HTTP_HOST'];
        $url = $url_pre.U($url,'',$suffix);
    }
    else{
        $url = $domain.$url.$suffix;
    }
    if(is_array($vars)){
        $vars = http_build_query($vars);
    }
    $maodian = '';
    if(strpos($url,'#') !== false){
        $tmp = explode('#',$url);
        $url = $tmp[0];
        $maodian = '#'.$tmp[1];
    }

    if($vars!='' && strpos($url,'?')===false){
        $url .= '?';
    }
    elseif($vars!='' && strpos($url,'&')!==false){
        $url .= '&';
    }
    $url .= $vars.$maodian;
    return $url;
}

/*扩展原来的W函数*/
function W2($name,$data=array()){
    return R($name,$data,'Widget');
}
/** 提取数组中值为键值
 *
 * 比如 :
 * $array=array(0=>array('a'=>1,'b'=>2),1=>array('a'=>2,'b'=>3));
 * $arr = array_valtokey($array,'a'); //$arr 值为 array(1=>array('a'=>1,'b'=>2),2=>array('a'=>2,'b'=>3))
 * $arr = array_valtokey($array,'a','b');//$arr值为 array(1=>2,2=>3);
 *
 * @param	array	$array 目标数组
 * @param	string	$key 键名
 * @param	string	$val 值名
 * @return	array
 */
function array_valtokey($array,$key = 0,$val = null)
{
	$arr = array();
	foreach($array as $i=>$value)
	{
        if($key == ''){
            $value_key = $i;
        }
        else{
            $value_key = $value[$key];
        }
		if(is_string($val))
		{
			$arr[$value_key] = $value[$val];
		}
		else
		{
			$arr[$value_key] = $value;
		}
	}
	return $arr;
}


/**
 * 数组按键名分组
 * @param $array
 * @param $key
 */
function array_groupbykey($array,$key){
    $result = array();
    foreach($array as $v){
        $key_val = $v[$key];
        isset($result[$key_val]) || $result[$key_val] = array();
        $result[$key_val][] = $v;
    }
    return $result;
}


/*---公共函数库结束 -----*/

function dtime($time,$format = 'Y-m-d H:i:s'){
	if($time == '' || $time == '0000-00-00 00:00:00'){
		return '';
	}
	$_time = strtotime($time);
	if(!$_time){
		return $time;
	}
    if($format == 'year-br-time'){
        $format = 'Y-m-d \\<\\b\\r\\> H:i:s';
    }
	return date($format,$_time);
}

function time_diff($date1, $date2, $unit = "") { //时间比较函数，返回两个日期相差几秒、几分钟、几小时或几天

    switch ($unit) {
        case 's':
            $dividend = 1;
            break;

        case 'i':
            $dividend = 60;
            break;

        case 'h':
            $dividend = 3600;
            break;

        case 'd':
            $dividend = 86400;
            break;

        default:
            $dividend = 86400;

    }

    $time1 = strtotime($date1);

    $time2 = strtotime($date2);

    if ($time1 && $time2)

        return (float)($time1 - $time2) / $dividend;

    return false;

}

/** 数组生成OPTION
 *
 *
 * @param	array	$arr	显示列表
 * @param	string	$val	选中项的值
 * @return	string
 */
function show_arr_opt($arr, $val=null){
	if(!is_array($arr)) return false;
	$re_str = '';
	foreach($arr as $key => $var){
		$re_str .= '<option value="'.$key.'"';
		if("$val"==="$key") $re_str .= ' selected ';
		$re_str .= '>';
		$re_str .= $var;
		$re_str .= '</option>';
	}
	return $re_str;
}

//实例化类，并且只返回一个实例
function get_instance($className,$path){
	static $__instance_list = array();
	if(isset($__instance_list[$className])){
		return $__instance_list[$className];
	}
	import($className,$path) or die($path.$className.' not found');
	$__instance_list[$className] = new $className;
	return $__instance_list[$className];
}

 /**
 * 输出变量的内容，通常用于调试
 *
 * @package Core
 *
 * @param mixed $vars 要输出的变量
 * @param boolean $return
 */
function dump2($vars, $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
		$content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content =  "\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}
//获取字典说明
function dict_value($key,$dict_array,$default=false){
	if($key == '')
	{
		return $key;
	}
	return isset($dict_array[$key])?$dict_array[$key]:($default!==false?$default:$key);
}

/** 处理过滤数组的值
	$column_arr = array(
		'a'=>'字段1',
		'b'=>array(
			'name'=>'字段2',
			'callback'=>array('abc',array('_VALUE_')),
		),
		'c'=>array('name'=>'字段3','info'=>array('1'=>'aa','2'=>'bb','3'=>'cc',null=>'no')),
		);
		$array = array(
			array('a'=>1,'b'=>2,'c'=>'1'),
			array('a'=>11,'b'=>22,'c'=>'2'),
			array('a'=>111,'b'=>222,'c'=>'3'),
		);
 */
function array_value_filter($array,$column_arr){
	$_array = $array;//保存原值
	if(!$column_arr)
	{
		$column_arr = array();
		foreach($array as $column=>$value)
		{
			$column_arr[$column] = $column;
		}
	}

	foreach($column_arr as $column=>$column_info)
	{
		if(!is_array($column_info)) $column_info = array('name'=>$column_info);
		$value = & $array[$column];
		if(is_array($column_info))
		{
			//回调函数
			if(!empty($column_info['callback']))
			{
				$callback = $column_info['callback'];
				if(is_string($callback)) $callback = array($callback,null);
				$function = $callback[0];
				if(function_exists($function))
				{
					$params = $callback[1];
					if(is_string($params)) $params = array($params);
					foreach($params as & $tmpval)
					{
						$value_flag = !is_string($tmpval) ? FALSE : strpos($tmpval,'_VALUE_ORG_');
						if($value_flag === 0)
						{
							$new_column = substr($tmpval,11,strlen($tmpval));
							$tmpval = $new_column ? $_array[$new_column] : $value;
						}
						else{
							$value_flag = !is_string($tmpval) ? FALSE : strpos($tmpval,'_VALUE_');
							if($value_flag === 0)
							{
								$new_column = substr($tmpval,7,strlen($tmpval));
								$tmpval = $new_column ? $array[$new_column] : $value;
							}
						}
					}
					$value = call_user_func_array($function,$params);
				}
			}
		}
		//是否不需要过滤
		if(!empty($column_info['nofilter'])){
			$value = (strpos($value,'<') === false && strpos($value,'>') === false) ?  $value : str_replace(array('<','>'),array('＜','＞'),$value);
		}

		//格式化处理
		$format = isset($column_info['format'])?$column_info['format']:'';
		if($format){
			preg_match_all('/\[([A-Z_].*)\]/isU',$format,$match);
			foreach($match[1] as $val)
			{
				$org_arr = 'array';
				if(strpos($val,'_VALUE_ORG_') === 0)
				{
					$new_val = substr($val,11);
					$new_val = $new_val ? $new_val : $column;
					$org_arr = '_array';
				}
				elseif(strpos($val,'_VALUE_') === 0)
				{
					$new_val = substr($val,7);
					$new_val = $new_val ? $new_val : $column;
				}
				$format = str_replace('['.$val.']',${$org_arr}[$new_val],$format);
			}
			$value = $format;
		}
	}
	return $array;
}

/** 获取统计字段
 *
 */
function array_value_count($array,$count_cols,&$count = array(),$reset = false){
	if(!$array) return $count;
	$new_count = array();
	$array = array_merge($count_cols,$array);
	foreach($array as $key=>$val){
		if($key == 'ROWSEQ') continue;
		if(in_array($key,$count_cols)){
			$count[$key] = isset($count[$key]) ? $count[$key] : 0;
			$new_count[$key] = $count[$key]+($reset === TRUE ? 0 : $val);
		}else
		{
			$new_count[$key] = null;
		}
	}
	return $new_count;
}

//安全过滤函数 sql注入警告
function input_filter($str){
	if(!get_magic_quotes_gpc()){
		return addslashes($str);
	}
}

/**
 * http请求
 * @params $url:地址
 * @params $data:数据
 * @params &$error:错误
 * @params &$opt:选项
 */
function http_post($url,$data=null,&$error=null,$opt = array()){
	$opt = array_merge(array(
			'TIMEOUT'=>	30,
			'METHOD'=>'POST',
	),$opt);
	//创建post请求参数
	$socket = new \Common\Org\Net\FineCurl;
	$socket->setopt('URL',$url);
	$socket->setopt('TIMEOUT',$opt['TIMEOUT']);
	$socket->setopt('METHOD',$opt['METHOD']);
	if(is_array($data)){
		$data = http_build_query($data);
	}
	log_write('request：'.$url.'data:：'.$data,'REMOTE');
	$result = $socket->send($data);
	$error = $socket->error();
	//记录日志
	if($error){
		Log_write($error,'ERROR');
	}
    Log_write('response：'.(function_exists('mb_convert_encoding') ? mb_convert_encoding($result,'utf-8','utf-8,gbk'):$result),'REMOTE');
	return $result;
}

function get_base_url_dir(){
    return '//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
}
/**
 * 判断是否手机访问
 * @return Bool
 */
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = Array("240x320", "acer", "acoon",
        "acs-", "abacho", "ahong", "airness", "alcatel",
        "amoi", "android", "anywhereyougo.com",
        "applewebkit/525", "applewebkit/532", "asus",
        "audio", "au-mic", "avantogo", "becker", "benq",
        "bilbo", "bird", "blackberry", "blazer", "bleu",
        "cdm-", "compal", "coolpad", "danger", "dbtel",
        "dopod", "elaine", "eric", "etouch", "fly ",
        "fly_", "fly-", "go.web", "goodaccess",
        "gradiente", "grundig", "haier", "hedy",
        "hitachi", "htc", "huawei", "hutchison",
        "inno", "ipad", "ipaq", "ipod", "jbrowser",
        "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2",
        "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-",
        "lge-", "lge9", "longcos", "maemo", "mercator",
        "meridian", "micromax", "midp", "mini", "mitsu",
        "mmm", "mmp", "mobi", "mot-", "moto", "nec-",
        "netfront", "newgen", "nexian", "nf-browser",
        "nintendo", "nitro", "nokia", "nook", "novarra",
        "obigo", "palm", "panasonic", "pantech", "philips",
        "phone", "pg-", "playstation", "pocket", "pt-",
        "qc-", "qtek", "rover", "sagem", "sama", "samu",
        "sanyo", "samsung", "sch-", "scooter", "sec-",
        "sendo", "sgh-", "sharp", "siemens", "sie-",
        "softbank", "sony", "spice", "sprint", "spv",
        "symbian", "tablet", "talkabout", "tcl-",
        "teleca", "telit", "tianyu", "tim-", "toshiba",
        "tsm", "up.browser", "utec", "utstar", "verykool",
        "virgin", "vk-", "voda", "voxtel", "vx", "wap",
        "wellco", "wig browser", "wii", "windows ce",
        "wireless", "xda", "xde", "zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent,  $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}


/**
 * 判断是否在微信中
 */
function is_weixin(){
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $is_weixin = strpos($agent, 'micromessenger') ? true : false ;
    return $is_weixin;
}

/**
 * cli颜色控制
 * @param $text
 * @param $status
 * @return string
 * @throws Exception
 */
function colorize($text, $status) {
    $out = "";
    switch($status) {
        case "SUCCESS":
            $out = "[42m"; //Green background
            break;
        case "FAILURE":
            $out = "[41m"; //Red background
            break;
        case "WARNING":
            $out = "[43m"; //Yellow background
            break;
        case "NOTE":
            $out = "[44m"; //Blue background
            break;
        default:
            $out = "[".$status."";
    }
    return chr(27) . "$out" . "$text" . chr(27) . "[0m";
}

function debug_factory($name,$status = '37m'){
    /**
     * echo -e “\033[30m 黑色字 \033[0m”
    　　echo -e “\033[31m 红色字 \033[0m”
    　　echo -e “\033[32m 绿色字 \033[0m”
    　　echo -e “\033[33m 黄色字 \033[0m”
    　　echo -e “\033[34m 蓝色字 \033[0m”
    　　echo -e “\033[35m 紫色字 \033[0m”
    　　echo -e “\033[36m 天蓝字 \033[0m”
    　　echo -e “\033[37m 白色字 \033[0m”
     */
    $debug_env = getenv("DEBUG");
    $show_debug = false;
    if($debug_env){
        if(strpos($debug_env,'*') !== false){
            $debug_env = str_replace('*','.*',$debug_env);
            $reg = '/^'.$debug_env.'$/';
            $show_debug = !!preg_match($reg,$name);
        }
        elseif($debug_env === $name){
            $show_debug = true;
        }
    }
    if($status){
        $name = colorize($name,$status);
    }
    return function($str1,$str2='')use($name,$show_debug){
       if($show_debug) {
           echo $name . ' ' . $str1 . ' ' . $str2 . "\r\n";
       }
    };
}

<?php
 header("Content-Type: text/html; charset=utf-8");
/*function get_onlineip() {
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}


$get_ip=get_onlineip();
$url = "http://api.apidatas.com/ip/$get_ip";
function curl_get($url, $gzip=false){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
//        if($gzip) curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
}

$res = curl_get($url);
if (substr($res, 0,3) == pack("CCC",0xef,0xbb,0xbf)) { $res = substr($res, 3); } 

$data=json_decode($res);
$city=$data->city;
if($city=="南京市"){
 require_once(dirname(__FILE__).'/Dev/_Core/cc.php');
 exit();
}*/

define('APP_NAME', '');
define('APP_PATH',    './');
define('APP_DEBUG',true);

define('THINK_PATH', './Dev/_Core/');

define('RUNTIME_PATH', './RunTime/');



defined('COMMON_PATH')  or define('COMMON_PATH',    './Dev/Common/'); // 项目公共目录
defined('LIB_PATH')     or define('LIB_PATH',       './Dev/Lib/'); // 项目类库目录
defined('CONF_PATH')    or define('CONF_PATH',      './Conf/'); // 项目配置目录
defined('LANG_PATH')    or define('LANG_PATH',      './Dev/Lang/'); // 项目语言包目录
defined('TMPL_PATH')    or define('TMPL_PATH',      './tpl/'); // 项目模板目录
defined('HTML_PATH')    or define('HTML_PATH',      APP_PATH.'Html/'); // 项目静态目录
defined('LOG_PATH')     or define('LOG_PATH',       RUNTIME_PATH.'logs/'); // 项目日志目录
defined('TEMP_PATH')    or define('TEMP_PATH',      RUNTIME_PATH.'Temp/'); // 项目缓存目录
defined('DATA_PATH')    or define('DATA_PATH',      RUNTIME_PATH.'Data/'); // 项目数据目录
defined('CACHE_PATH')   or define('CACHE_PATH',     RUNTIME_PATH.'Cache/'); // 项目模板缓存目录
//define('THINK_PATH', './');
//echo './';
require './Dev/_Core/Lingzh.php';


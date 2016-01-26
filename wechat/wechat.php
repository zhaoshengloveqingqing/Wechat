<?php
 header("Content-type: text/html; charset=utf-8");
class Wechat {
	
	private $appid = '';
	private $appsecret = '';
	private $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
	private $ticket_url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=%s';
	
	function __construct($appid='', $appsecret='') {
		$this->appid = $appid;
		$this->appsecret = $appsecret;
	}
	
 	function get_access_token($access_token = ''){
 		$path = "./wechat/".$this->appid."_access_token.json";
	 	$data = json_decode(file_get_contents($path));
	    if ($data->expire_time < time()) {
	      $url = sprintf($this->access_token_url, $this->appid, $this->appsecret);
	      if (!$access_token) {
		      $res = json_decode($this->https_request($url));
		      $access_token = $res->access_token;
	      }
	      if ($access_token) {
	        $data->expire_time = time() + 7000;
	        $data->access_token = $access_token;
	        $fp = fopen($path, "w");
	        fwrite($fp, json_encode($data));
	        fclose($fp);
	      }
	    } else {
	      $access_token = $data->access_token;
	    }
	    $this->access_token = $data->access_token;
	    return $access_token;
	 }
	 
	 function  get_jsapi_ticket($access_token ='') {
	    $path = "./wechat/".$this->appid."_jsapi_ticket.json";
	    $data = json_decode($path);
	    if ($data->expire_time < time()) {
	      $accessToken = $access_token ? $access_token : $this->get_access_token();
	      $url = sprintf($this->ticket_url, $accessToken);
	      $res = json_decode($this->https_request($url));
	      $ticket = $res->ticket;
	      if ($ticket) {
	        $data->expire_time = time() + 7000;
	        $data->ticket = $ticket;
	        $fp = fopen($path, "w");
	        fwrite($fp, json_encode($data));
	        fclose($fp);
	      }
	    } else {
	      $ticket = $data->ticket;
	    }
	    return $ticket;
	 }
	 
	function https_request($url, $post_data=null){
	        $curl = curl_init();
	        curl_setopt($curl, CURLOPT_URL, $url);       
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	        if (!empty($post_data)){
	            // 发送一个常规的Post请求 
	            curl_setopt($curl, CURLOPT_POST, 1);
	            // Post提交的数据包  http_build_query生成 URL-encode 之后的请求字符串 json_encode
	            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	        }
	         // 获取的信息以文件流的形式返回      
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        // 执行操作  
	        $output = curl_exec($curl);
	        curl_close($curl);
	        return $output;
	 }
	 
	 function get_token_ticket(){
	 	$param['access_token'] = $this->get_access_token();
	 	$param['jsapi_ticket']  = $this->get_jsapi_ticket();
	 	return  json_encode($param);
	 }
}
$wechat = new Wechat($_GET['appid'], $_GET['appsecret']);
echo $wechat->get_token_ticket();

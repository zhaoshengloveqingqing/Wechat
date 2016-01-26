<?php
header("Content-Type: text/html; charset=utf-8");
class LBSAction extends Action{
    
	private $token = '';
	private $appid = '';
	private $appsecret = '';
	
    
    public function location(){
    	//http://218.244.128.8/index.php?g=App&m=LBS&a=location&token=54016446z8d
    	$this->token = $_GET['token'];
    	$this->tpl = M('Wxuser')->where(array('token'=>$this->token, 'status'=>'1'))->find();
    	$this->appid = $this->tpl['appid'];
    	$this->appsecret = $this->tpl['appsecret'];
        if (!isset($_GET['code'])) {
        	$redirectUrl = 'http://'.C('wx_handler_server').'/index.php?g=App&m=LBS&a=location&token='.$_GET['token'];
        	$link = $this->createOauthUrlForCode($redirectUrl);
        	header('Location: '.$link);
        	exit;
        }else{
        	$this->code = $_GET['code'];
        }
        $res = $this->get_curl_info($this->createOauthUrlForOpenId());
        $info = M('wx_access')->where(array('to_public_token'=>$this->token, 'msg_type'=>'event', 'event'=>'LOCATION', 'from_user'=>$res['openid']))->order('access_id desc')->find();
        file_put_contents('/tmp/info', print_r($info, true));
        $latitude = $this->tpl['latitude'];
        $longtitude = $this->tpl['longtitude']; 
        if ($info) {
	        $latitude = $info['location_x'];
	        $longtitude = $info['location_y']; 
        }
        $query = "电信营业厅";
        $link = "http://api.map.baidu.com/place/search?query=".$query
        		."&location=".$latitude.','.$longtitude
        		."&radius=1000"
        		."&region="
        		."&output=html&src=pinet";
        header('Location: '.$link);
    }
    
    
	/**
	 * 	作用：生成可以获得code的url
	 */
	function createOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["redirect_uri"] = urlencode($redirectUrl);
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}
    
    
	/**
	 * 	作用：生成可以获得openid的url
	 */
	function createOauthUrlForOpenId()
	{
		$urlObj["appid"] = $this->appid;
		$urlObj["secret"] = $this->appsecret;
		$urlObj["code"] = $this->code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
	
	
	/**
	 * 	作用：通过curl向微信提交code，以获取access_token openid
	 */
	function get_curl_info($url)
	{
        //初始化curl
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOP_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//运行curl，结果以jason形式返回
        $res = curl_exec($ch);
		curl_close($ch);
		//取出信息
		return json_decode($res,true);
	}
	
	
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode)
	{
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar = "";
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
}
?>
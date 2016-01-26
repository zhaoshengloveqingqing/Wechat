<?php
class GamesAction extends Action{
    
	private $token = '';  //配置的token
	function __construct(){
		$this->token = $_GET['token'];
	}
	
    public function index(){
    	//通过code获得openid
    	$openid = session("wechat_id_".$this->token);
    	$share_code = '';
    	if (isset($_GET['share_code'])) {
    		$share_code = $_GET['share_code'];
    	}
    	if (!$openid) {
			if (!isset($_GET['code']))
			{
			    //触发微信返回code码
			    $redirect_uri = C('site_url').'/index.php/Wap/Games/index?token='.$this->token.'&share_code='.$share_code;
			    $url = $this->createOauthUrlForCode($redirect_uri);
			    header("Location: $url"); 
			}else{
			    //获取code码，以获取openid
			    $url = C('site_url').'/index.php?g=Wap&m=Oauth2&a=index&state='.$this->token.'&code='.$_GET['code'].'&share_code='.$share_code;
			    header('Location:'.$url);
			}
    	}else{
        	$signature = md5($openid.'d2c9b3b4ac4c17d7bd95baca46198df0');
    		if ($this->token == '54447845z7b') {
	        	$url = 'http://xiaoyang.xuetang.cn/apps/index.php/hdbox/HdboxUser/index?openid='.$openid.'&signature='.$signature.'&share_code='.$share_code;
	        }elseif ($this->token == '54570d1ezc9'){
	        	$url = 'http://xiaoyang.xuetang.cn/apps/index.php/xhbox/XhboxUser/index?openid='.$openid.'&signature='.$signature.'&share_code='.$share_code;
	        }
        	header('Location:'.$url);
    	}
    }
    
    
	/**
	 * 	作用：生成可以获得code的url
	 */
	function createOauthUrlForCode($redirectUrl)
	{
		$user = M('Wxuser')->where(array('token'=>$this->token))->find();
        if (empty($user['appid']) || empty($user['appsecret'])) 
        {
            $this->error('账号不是服务号');
            exit;
        }
		$urlObj["appid"] = $user['appid'];
		$urlObj["redirect_uri"] = urlencode($redirectUrl);
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}
	
	
	/**
	 * 	作用：格式化参数，签名过程需要使用
	 */
	function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar = '';
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
}
?>

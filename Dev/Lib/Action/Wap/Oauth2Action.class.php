<?php
class Oauth2Action extends Action{
    
    public function index()
    {
		$code = $_GET['code'];
		$token = $_GET['state'];
        $backurl = $_GET['backurl'];
        if (empty($code) || $code == 'authdeny') 
        {
            $this->error('商城需要验证身份，请授权商城使用您的基本资料。');
            exit;
        }
		
        $user = M('Wxuser')->where(array('token'=>$token))->find();
        if (empty($user['appid']) || empty($user['appsecret'])) 
        {
            $this->error('账号不是服务号');
            exit;
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$user['appid']."&secret=".$user['appsecret']."&code=".$code."&grant_type=authorization_code";
        $remote_resp = file_get_contents($url);
        $ret_json = json_decode($remote_resp, true);
        $refresh_token = $ret_json['refresh_token'];
        Log::write('access_token info'.print_r($remote_resp, true));
        
        $url2 = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$user['appid']."&grant_type=refresh_token&refresh_token=".$refresh_token;
        $remote_resp = file_get_contents($url2);
        $ret_json = json_decode($remote_resp, true);
        $openid = $ret_json['openid'];
        $access_token = $ret_json['access_token'];
        Log::write('refresh_token info'.print_r($remote_resp, true));
        
        $url3 = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid;
        $remote_resp = file_get_contents($url3);
        $ret_json = json_decode($remote_resp, true);
        Log::write('user info'.print_r($remote_resp, true));
        
//        include_once APP_PATH. 'wechat/wechat.php';
//        $wechat = new Wechat($user['appid'], $user['appsecret']);
//		$wechat->get_jsapi_ticket($access_token);
		
		$db = M('wecha_user');
		$data['token'] = $token;
		$data['wecha_id'] = $openid;
		$user = $db->where($data)->find();
		if (empty($user)) {
		    $data['userinfo']   = $remote_resp;
			$data['sex']        = $ret_json['sex'];
			$data['nickname']   = $ret_json['nickname'];
            $data['city']       = $ret_json['city'];
            $data['province']   = $ret_json['province'];
            $data['country']    = $ret_json['country'];
		    $db->add($data);
		} else {
		    $mod['userinfo']      = $remote_resp;
			$mod['sex']           = $ret_json['sex'];
			$mod['nickname']      = $ret_json['nickname'];
            $mod['city']          = $ret_json['city'];
            $mod['province']      = $ret_json['province'];
            $mod['country']       = $ret_json['country'];
		    $db->where($data)->save($mod);
		}
        session("wechat_id_".$token, $openid);
        if (empty($backurl)) 
        {
	        if (in_array($token, array('54447845z7b', '54570d1ezc9')) ) {
	        	if (empty($openid)) {
	        		exit('未能获取openid');
	        	}
	        	$signature = md5($openid.'d2c9b3b4ac4c17d7bd95baca46198df0');
		        if ($token == '54447845z7b') {
		        	$url = 'http://xiaoyang.xuetang.cn/apps/index.php/hdbox/HdboxUser/index?openid='.$openid.'&signature='.$signature.'&share_code='.$_GET['share_code'];
		        }elseif ($token == '54570d1ezc9'){
		        	$url = 'http://xiaoyang.xuetang.cn/apps/index.php/xhbox/XhboxUser/index?openid='.$openid.'&signature='.$signature.'&share_code='.$_GET['share_code'];
		        }
	        	header('Location:'.$url);
	        }
            echo $ret_json;
        } 
        else 
        {
        	//session("wechat_id_".$token, $openid);
            session("nickname_".$token,  $ret_json['nickname']);
            session("sex_".$token, $ret_json['sex']);
            $this->redirect(urldecode($backurl));
        }
    }
    
    
}
?>

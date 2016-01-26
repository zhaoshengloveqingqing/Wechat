<?php
class LoginAction extends Action{
	
	private $token = '';
	private $url = '';
	function __construct(){
		$this->token = $_GET['token'];
		$this->url = $_GET['url'];
	}
	
	function index(){
		if (!session('openid_'.$this->token)) {
    		$this->info = M('wxuser')->where(array('token'=>$this->token))->find();
	    	vendor("Oauth2.OAuth2", LIB_PATH.'../Extend/Vendor');
	    	vendor("Oauth2.Provider.Wechat", LIB_PATH.'../Extend/Vendor');
	        $wechat = new Wechat();
	        $wechat->config(array( 'id' => $this->info['appid'], 'secret' => $this->info['appsecret']));
	        $redirect_uri = C('site_url').'/index.php/Wap/Login/index?token='.$this->token.'&url='.$this->url;
	        if ( !$_GET['code']){
	            // By sending no options it'll come back here
	            $url = $wechat->authorize(array('redirect_uri'=>$redirect_uri));
	            redirect($url);
	        } else{
	            try {
	                // Have a go at creating an access token from the code
	                $access_token = $wechat->access($_GET['code'],array('redirect_uri'=>$redirect_uri));
	                // Use this object to try and get some user details (username, full name, etc)
	                $user = $wechat->get_user_info($access_token);
	                $this->save_wechat_user($user);
	                session('openid_'.$this->token, $user->openid);
	            } catch (OAuth2_Exception $e){
	                print_r($e);die;
	            }
	        }
    	}
    	if ($this->url) redirect($this->url);
	}
	
	function save_wechat_user($info){
    	$data = array(
    		'token' => $this->token,
    		'wecha_id' => $info->openid,
    		'userinfo' => json_encode($info),
    		'nickname' => $info->nickname,
    		'sex' => $info->sex,
    		'city' => $info->city,
    		'province' => $info->province,
    		'country' => $info->country,
    		'headimgurl' => $info->headimgurl,
    		'updatetime'=>time()
    	);
    	$user_db = M('wecha_user');
    	$userinfo = $user_db->where(array('token' => $this->token, 'wecha_id' => $info->openid))->find();
    	if($userinfo){
    		$user_db->where(array('token' => $this->token, 'wecha_id' => $info->openid))->save($data);
    	}else{
    		$user_db->data($data)->add();
    	}
    }
}
?>
<?php
class CouponsAction extends Action{
	
	private $token = '';
	private $coupon_id = '';
	
	function __construct(){
		$this->token = $_GET['token'];
		$this->coupon_id = $_GET['coupon_id'];
	}
	
	public function index(){
    	if (!session('openid_'.$this->token)) {
    		$this->info = M('wxuser')->where(array('token'=>$this->token))->find();
	    	vendor("Oauth2.OAuth2", LIB_PATH.'../Extend/Vendor');
	    	vendor("Oauth2.Provider.Wechat", LIB_PATH.'../Extend/Vendor');
	        $wechat = new Wechat();
	        $wechat->config(array( 'id' => $this->info['appid'], 'secret' => $this->info['appsecret']));
	        $redirect_uri = C('site_url').'/index.php/Wap/Coupons/index?token='.$this->token.'&coupon_id='.$this->coupon_id;
	        if ( !$_GET['code']){
	            // By sending no options it'll come back here
	            $url = $wechat->authorize(array('redirect_uri'=>$redirect_uri));
	            redirect($url);
	        } else{
	            try {
	                // Have a go at creating an access token from the code
	                $token = $wechat->access($_GET['code'],array('redirect_uri'=>$redirect_uri));
	                // Use this object to try and get some user details (username, full name, etc)
	                $user = $wechat->get_user_info($token);
	                $this->openid = $user->openid;
	                session('openid_'.$this->token, $user->openid);
	            } catch (OAuth2_Exception $e){
	                print_r($e);die;
	            }
	        }
    	}else{
    		$this->openid = session('openid_'.$this->token);
    	}
	    $url = C('site_url')."/index.php?g=Wap&m=Coupon&a=index&type=1&token=".$this->token.'&id='.$this->coupon_id.'&wecha_id='.$this->openid;
    	header('Location:'.$url);
    }
}
?>
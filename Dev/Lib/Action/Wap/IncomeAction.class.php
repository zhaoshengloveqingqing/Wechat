<?php
class IncomeAction extends Action{
    
	private $token = '';
	function __construct(){
		$this->token = $_GET['token'];
	}
	
	public function index()
    {
    	if (!session('openid_'.$this->token)) {
    		$this->info = M('wxuser')->where(array('token'=>$this->token))->find();
	    	vendor("Oauth2.OAuth2", LIB_PATH.'../Extend/Vendor');
	    	vendor("Oauth2.Provider.Wechat", LIB_PATH.'../Extend/Vendor');
	        $wechat = new Wechat();
	        $wechat->config(array( 'id' => $this->info['appid'], 'secret' => $this->info['appsecret']));
	        $redirect_uri = C('site_url').'/index.php/Wap/Income/index?token='.$this->token;
	        if ( !$_GET['code'])
	        {
	            // By sending no options it'll come back here
	            $url = $wechat->authorize(array('redirect_uri'=>$redirect_uri));
	            redirect($url);
	        }
	        else
	        {
	            try
	            {
	                // Have a go at creating an access token from the code
	                $token = $wechat->access($_GET['code'],array('redirect_uri'=>$redirect_uri));
	                // Use this object to try and get some user details (username, full name, etc)
	                $user = $wechat->get_user_info($token);
	                session('openid_'.$this->token, $user->openid);
	                $this->save_user($user);
	            } catch (OAuth2_Exception $e)
	            {
	                print_r($e);die;
	            }
	        }
    	}
	if (session('openid_'.$this->token)) {
	    $user = M('reguser')->where(array('token' => $this->token,'openid' => session('openid_'.$this->token)))->find();
		if (!$user) {
			session_destroy();
			header('Location:'.U('Income/index', array('token'=>$this->token)));
		}
	    }
    	header('Location:'.U('Income/reg_phone', array('token'=>$this->token)));
    	//$this->assign('token', $this->token);
    	//$this->display('info/income');
    }
    
    function save_user($user){
    	$data = array(
    		'token' => $this->token,
    		'openid' => $user->openid,
    		'truename' => $user->nickname,
    		'headimgurl' => $user->headimgurl,
    		'update_time'=>time()
    	);
    	$reguser = M('reguser');
    	$user = $reguser->where(array('token' => $this->token,'openid' => $user->openid))->find();
    	if($user){
    		$reguser->where(array('token' => $this->token,'openid' => $user->openid))->save($data);
    	}else{
    		$data['create_time'] = time();
    		$reguser->data($data)->add();
    	}
    }
    
    
 	function reg_phone(){
 		$token = $_GET['token'];
		$openid = session('openid_'.$token);
		if (empty($openid)) {
			header('Location:'.U('Income/index', array('token'=>$token)));
		}
		$user = M('reguser')->where(array('openid'=>$openid, 'token'=>$this->token))->find();
 		if ($user && $user['mb']) {
 			header('Location:'.U('Income/income', array('token'=>$token, 'fxs_id'=>$user['id'])));
 			exit();
 		}
 		$this->assign('token', $token);
		$this->display('info/register');
	}
	/**
	 * 普通接口发短信
	 * apikey 为云片分配的apikey
	 * text 为短信内容
	 * mobile 为接受短信的手机号
	 */
	function send_sms(){
		$code = str_pad(rand(1,1000000),6,'1');
		session('phone_code', $code);
		$mobile=$_GET['mobile'];
		pinet_send_randcode_nocompany($code, $mobile);
		echo json_encode(array('code' => $code));	
	}
	
	function register(){
		$token = $_GET['token'];
		$openid = session('openid_'.$token);
		if (empty($openid)) {
			header('Location:'.U('Income/index', array('token'=>$token)));
		}
		$reguser_db = D("reguser"); 
		if(isset($_SESSION['phone_code']) && $_POST['code'] == $_SESSION['phone_code']) {
			$user = $reguser_db->where(array('token'=>$token, 'openid'=>$openid))->find();
			if ($user) {
				$data = array(
					'mb'=>$this->_post('mobile'),
					'update_time'=>time(),
					'status'=>1
				);
				$reguser_db->where(array('token'=>$token, 'openid'=>$openid))->save($data);
				$user = $reguser_db->where(array('token'=>$token, 'openid'=>$openid))->find();
				$this->success('注册成功', U('Income/income',array('token'=>$token, 'fxs_id'=>$user['id'])));
			}else{
				session('openid_'.$token, null);
				header('Location:'.U('Income/index', array('token'=>$token)));
			}
		}else {
			$this->error($reguser_db->getError());
		}
	}
	
	
	function income(){
		$fxs_id = $_GET['fxs_id'];
		$token = $_GET['token'];
		//产销分销商信息
		$reguser_db = D("reguser"); 
		$user = $reguser_db->where(array('token'=>$token, 'id'=>$fxs_id))->find();
		
		//当月预估收益
		$sql = "SELECT round(o.price*oi.rebate/100,2) as rebate,o.commission"
							." FROM tp_b2c_order as o inner join tp_b2c_order_item as oi on o.order_id = oi.order_id "
							."where o.fxs_id='$fxs_id'  and o.create_time BETWEEN UNIX_TIMESTAMP(DATE_ADD(curdate(),interval -day(curdate())+1 day)) and UNIX_TIMESTAMP(date_add(curdate()-day(curdate())+1,interval 1 month))";
			   
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

		$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
		$list = $Model->query($sql);
		//上月预估收益
		/*$sql = "SELECT round(o.price*oi.rebate/100,2) as rebate,o.commission "
							." FROM tp_b2c_order as o inner join tp_b2c_order_item as oi on o.order_id = oi.order_id "
							."where o.fxs_id='$fxs_id'  and o.create_time BETWEEN UNIX_TIMESTAMP(DATE_ADD(DATE_ADD(curdate(),interval -day(curdate())+1 day),INTERVAL -1 month)) and UNIX_TIMESTAMP(DATE_ADD(curdate(),interval -day(curdate())+1 day))";*/
		$sql = "SELECT commission "
							." FROM tp_b2c_order "
							."where fxs_id='$fxs_id'  and create_time BETWEEN UNIX_TIMESTAMP(DATE_ADD(DATE_ADD(curdate(),interval -day(curdate())+1 day),INTERVAL -1 month)) and UNIX_TIMESTAMP(DATE_ADD(curdate(),interval -day(curdate())+1 day))";
			   
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

		$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
		$lastprofit = $Model->query($sql);
		foreach ($lastprofit as $key => $o){
				$lasttotalcount+=$o['commission'];
		}
		
		//总收益
		/*$sql = "SELECT round(o.price*oi.rebate/100,2) as rebate,o.commission "
							." FROM tp_b2c_order as o inner join tp_b2c_order_item as oi on o.order_id = oi.order_id "
							."where o.fxs_id='$fxs_id'";*/
		$sql = "SELECT commission"
							." FROM tp_b2c_order "
							."where fxs_id='$fxs_id'";
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

		$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
		$allprofit = $Model->query($sql);
		foreach ($allprofit as $key => $o){
				$alltotalcount+=$o['commission'];
		}
		$this->assign('lasttotalcount',$lasttotalcount);
		$this->assign('alltotalcount',$alltotalcount);
		$allrebate=$allprofit['rebate'];
		$this->assign('lastprofit',$lastprofit);
		$this->assign('allrebate',$allrebate);
		$this->assign('user',$user);
		$this->assign('list',$list);
		$this->assign('token', $this->token);
    	$this->display('info/income');
	}
}
?>

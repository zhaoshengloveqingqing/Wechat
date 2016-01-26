<?php
class AgentsAction extends Action{
    
	
	private $agents_id = '';
	private $agents = '';
	
	function __construct(){
		$this->token = $_GET['token'];
		$this->agents = M('reguser');
		$this->payment = M('payment');
		$this->assign('token', $this->token); 
		$this->agents_id = $this->get_user_id();
	}
	
	/**
     * speed : agents info 
     */
	public function index(){
    	$where = array('id'=>$this->agents_id);
    	$user = $this->agents->where($where)->find();
        if ($user){
        	if (!empty($user['qrcode_pic_url'])) {
                $this->assign('qrcode_url', $user['qrcode_pic_url']);
                $this->assign('product_url', pinet_generate_shorturl($user['pic_url_link']));
            } else {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
                $product_url = 'http://'.C('wx_handler_server').U('Fxs/Shop/index', array('fxs_id'=>$user['id'], 'token'=>$this->token));
				$gen->build($product_url, 'reguser', $this->token, array('comp'=>$user['headimgurl']));
                $qrcode_pic_url = $gen->getUrl();
				$this->agents->where($where)->save(array('qrcode_pic_url'=>$qrcode_pic_url, 'pic_url_link'=>$product_url));
                $this->assign('qrcode_url',$qrcode_pic_url);
                $this->assign('product_url',pinet_generate_shorturl($product_url));
            }
		   $payment = $this->payment->where(array('user_id'=>$this->agents_id))->find();
		   $payinfo = '请填写支付方式';
		   if ($payment) {
		   		$payinfo = $payment['pay_name'].'：'.$payment['pay_account'];
		   } 
           $this->assign('payinfo', $payinfo); 
           $this->assign('payment', $payment); 
           $this->assign('photo', $user['headimgurl']); 
           $this->assign('user',$user); 
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/index'));
        }
		$this->display('info/agents');
    }
	
    function modify_user(){
    	$where = array('id'=>$this->agents_id);
    	$user = $this->agents->where($where)->find();
        if ($user){
           $this->agents->where($where)->save(array('username'=>$this->_post('username')));
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/index', array('token'=>$this->token)));
        }
		$this->display('info/agents');
    }
    
    function pay_index(){
    	$where = array('id'=>$this->agents_id);
    	$user = $this->agents->where($where)->find();
    	$this->assign('user', $user);
    	$this->display('info/pay');
    }
    
    function pay_conf(){
    	$token = $this->_post('token');
    	$user_id = $this->_post('user_id');
    	if (empty($_POST['account'])) {
    		$this->error('请完善账号信息', U(MODULE_NAME.'/index', array('token'=>$token)));
    	}
    	$where = array('user_id'=>$user_id);
    	$payment = $this->payment->where($where)->find();
    	switch ($this->_post('pay_code')) {
        	case 'cftpay':
		    	$pay = array(
		    		'pay_code' => 'cftpay',
		    		'pay_name' => '财付通',
		    		'pay_account' => $this->_post('account'),
		    	);
        		break;
        	case 'alipay':
		    	$pay = array(
		    		'pay_code' => 'alipay',
		    		'pay_name' => '支付宝',
		    		'pay_account' => $this->_post('account'),
		    	);
        		break;
        	default:
        		break;
        }
    	if ($payment) {
    		$this->payment->where($where)->save($pay);
    	}else{
    		$pay['user_id'] = $user_id;
    		$pay['create_time'] = time();
    		$this->payment->data($pay)->add();
    	}
        $this->ajaxReturn(U(MODULE_NAME.'/index', array('token'=>$token)), "保存成功", 1);
    }

    function QR_code(){
        $where = array('id'=>$this->agents_id);
        $user = $this->agents->where($where)->find();
        if ($user && isset($user['qrcode_pic_url'])){
            $this->assign('qrcode_url', $user['qrcode_pic_url']);
            $this->assign('product_url', pinet_generate_shorturl($user['pic_url_link']));
        }
        $this->display('info/QR_code');
    }
    
    function get_user_id(){
    	$openid = session('openid_'.$this->token);
    	if (empty($openid)) {
    		header('Location:'.U('Income/index', array('token'=>$this->token)));
    	}
    	$user = $this->agents->where(array('openid'=>$openid, 'token'=>$this->token))->find();
    	return $user['id'];
    }
}
?>

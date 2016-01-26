<?php

class PayConf extends Action{
	
	public $payment_db = null;
	public $token = '';
	public $branch_id = '';
	
	function __construct($token, $branch_id, $pay_type, $table) {
		$this->payment_db =  $table;
		$this->token = $token;
		$this->branch_id = $branch_id;
		$this->pay_type = $pay_type;
	}
	
	function savePayments() {
		//cod
		$cod_pay_enabled =   $this->_post('enable_cod','intval',0);
		$this->saveCodPayment ( $cod_pay_enabled );
		
		//wechat pay
		$wx_pay_enabled = $_POST ['enable_wxpay'];
		$this->saveWxPayment ( $wx_pay_enabled );
		
		//cft pay
		$cft_pay_enabled = $_POST['enable_cftpay'];
		$this->saveCftPayment ( $cft_pay_enabled );
		
		//alipay
		$ali_pay_enabled = $_POST['enable_alipay'];
		$this->saveAliPayment ( $ali_pay_enabled );
		
		//unionpay
		$union_pay_enabled = $_POST['enable_unionpay'];
		$this->saveUnionPayment ( $union_pay_enabled );
		
		//wingpay
		$wing_pay_enabled = $this->_post('enable_wingpay','intval',0);
		$this->saveWingPayment ( $wing_pay_enabled );
	}
	
	function saveCodPayment($is_enabled = 0) {
		$payment_data = array ();
		$payment_data ['enabled'] = $is_enabled;
		$payment_data ['pay_config'] = '';
		$payment_data ['pay_order'] = 0;
		$payment_data ['pay_type'] = $this->pay_type;
		
		$where = array ('token' => $this->token, 'branch_id' => $this->branch_id, 'pay_code' => 'cod' );
		$payment = $this->payment_db->where ( $where )->find ();
		if ($payment) {
			$this->payment_db->where ( $where )->save ( $payment_data );
		} else {
			$payment_data ['token'] = $this->token;
			$payment_data ['branch_id'] = $this->branch_id;
			$payment_data ['pay_code'] = 'cod';
			$payment_data ['pay_name'] = '货到付款';
			$payment_data ['pay_fee'] = 0;
			$payment_data ['is_cod'] = 1;
			$payment_data ['is_online'] = 0;
			$this->payment_db->data ( $payment_data )->add ();
		}
	}
	
	function saveWxPayment($is_enabled = 0) {
		if ($is_enabled == 1 && ! ($_POST ['wxpay_name'] && $_POST ['wxpay_appId'] && $_POST ['wxpay_appSecret'] && $_POST ['wxpay_paySignKey'] && $_POST ['wxpay_partnerId'] && $_POST ['wxpay_partnerKey'])) {
			$this->error ( '请完善微信支付配置信息', U ( MODULE_NAME . '/payconf', array ('id' => $this->branch_id ) ) );
		}
		
		$payment_config = array ();
		$payment_config ['name'] = trim ( $_POST ['wxpay_name'] );
		$payment_config ['APPID'] = trim ( $_POST ['wxpay_appId'] );
		$payment_config ['APPSERCERT'] = trim ( $_POST ['wxpay_appSecret'] );
		$payment_config ['APPKEY'] = trim ( $_POST ['wxpay_paySignKey'] );
		$payment_config ['PARTNERID'] = trim ( $_POST ['wxpay_partnerId'] );
		$payment_config ['PARTNERKEY'] = trim ( $_POST ['wxpay_partnerKey'] );
		
		$payment_data = array ();
		$payment_data ['enabled'] = $is_enabled;
		$payment_data ['pay_config'] = serialize ( $payment_config );
		$payment_data ['pay_order'] = 1;
		
		$where = array ('token' => $this->token, 'branch_id' => $this->branch_id, 'pay_code' => 'wxpay' );
		$payment = $this->payment_db->where ( $where )->find ();
		if ($payment) {
			$this->payment_db->where ( $where )->save ( $payment_data );
		} else {
			$payment_data ['token'] = $this->token;
			$payment_data ['branch_id'] = $this->branch_id;
			$payment_data ['pay_code'] = 'wxpay';
			$payment_data ['pay_name'] = '微信支付';
			$payment_data ['pay_fee'] = 0;
			$payment_data ['is_cod'] = 0;
			$payment_data ['is_online'] = 1;
			$payment_data ['pay_type'] = $this->pay_type;
			$this->payment_db->data ( $payment_data )->add ();
		}
	}
	
	function saveCftPayment($is_enabled = 0) {
		
		if ($is_enabled == 1 && ! ($_POST ['cftpay_name'] && $_POST ['cftpay_partnerId'] && $_POST ['cftpay_partnerKey'])) {
			$this->error ( '请完善财付通配置信息', U ( MODULE_NAME . '/payconf', array ('id' => $this->branch_id ) ) );
		}
		
		$payment_config = array ();
		$payment_config ['name'] = trim ( $_POST ['cftpay_name'] );
		$payment_config ['partnerId'] = trim ( $_POST ['cftpay_partnerId'] );
		$payment_config ['partnerKey'] = trim ( $_POST ['cftpay_partnerKey'] );
		
		$payment_data = array ();
		$payment_data ['enabled'] = $is_enabled;
		$payment_data ['pay_config'] = serialize ( $payment_config );
		$payment_data ['pay_order'] = 2;
		$payment_data ['pay_type'] = $this->pay_type;
		
		$where = array ('token' => $this->token, 'branch_id' => $this->branch_id, 'pay_code' => 'cftpay' );
		$payment = $this->payment_db->where ( $where )->find ();
		if ($payment) {
			$this->payment_db->where ( $where )->save ( $payment_data );
		} else {
			$payment_data ['token'] = $this->token;
			$payment_data ['branch_id'] = $this->branch_id;
			$payment_data ['pay_code'] = 'cftpay';
			$payment_data ['pay_name'] = '财付通';
			$payment_data ['pay_fee'] = 0;
			$payment_data ['is_cod'] = 0;
			$payment_data ['is_online'] = 1;
			$this->payment_db->data ( $payment_data )->add ();
		}
	}
	
	function saveAliPayment($is_enabled = 0) {
		if ($is_enabled == 1 && ! ($_POST ['pay_account'] && $_POST ['alipay_pid'] && $_POST ['alipay_key'])) {
			$this->error ( '请完善支付宝配置信息', U ( MODULE_NAME . '/payconf', array ('id' => $this->branch_id ) ) );
		}
		
		$payment_config = array ();
		$payment_config ['pay_account'] = trim ( $_POST ['pay_account'] );
		$payment_config ['alipay_pid'] = trim ( $_POST ['alipay_pid'] );
		$payment_config ['alipay_key'] = trim ( $_POST ['alipay_key'] );
		
		$payment_data = array ();
		$payment_data ['enabled'] = $is_enabled;
		$payment_data ['pay_config'] = serialize ( $payment_config );
		$payment_data ['pay_order'] = 3;
		$payment_data ['pay_type'] = $this->pay_type;
		
		$where = array ('token' => $this->token, 'branch_id' => $this->branch_id, 'pay_code' => 'alipay' );
		$payment = $this->payment_db->where ( $where )->find ();
		if ($payment) {
			$this->payment_db->where ( $where )->save ( $payment_data );
		} else {
			$payment_data ['token'] = $this->token;
			$payment_data ['branch_id'] = $this->branch_id;
			$payment_data ['pay_code'] = 'alipay';
			$payment_data ['pay_name'] = '支付宝';
			$payment_data ['pay_fee'] = 0;
			$payment_data ['is_cod'] = 0;
			$payment_data ['is_online'] = 1;
			$this->payment_db->data ( $payment_data )->add ();
		}
	}
	
	//union_pay
	function saveUnionPayment($is_enabled = 0) {
		$payment_data = array ();
		$payment_data ['enabled'] = $is_enabled;
		$payment_data ['pay_config'] = '';
		$payment_data ['pay_order'] = 4;
		$payment_data ['pay_type'] = $this->pay_type;
		
		$where = array ('token' => $this->token, 'branch_id' => $this->branch_id, 'pay_code' => 'unionpay' );
		$payment = $this->payment_db->where ( $where )->find ();
		if ($payment) {
			$this->payment_db->where ( $where )->save ( $payment_data );
		} else {
			$payment_data ['token'] = $this->token;
			$payment_data ['branch_id'] = $this->branch_id;
			$payment_data ['pay_code'] = 'unionpay';
			$payment_data ['pay_name'] = '银联支付';
			$payment_data ['pay_fee'] = 0;
			$payment_data ['is_cod'] = 1;
			$payment_data ['is_online'] = 0;
			$this->payment_db->data ( $payment_data )->add ();
		}
	}
	
	/**
	 * wings payment
	 * @param $is_enabled | 1.start 0.stop 
	 */
	function saveWingPayment($is_enabled = 0) {
		if ($is_enabled == 1 && ! ($_POST ['wingpay_mchid'] && $_POST ['wingpay_key'])) {
			$this->error ( '请完善翼支付配置信息', U ( MODULE_NAME . '/payconf', array ('id' => $this->branch_id ) ) );
		}
		
		$payment_config = array ();
		$payment_config ['mch_id'] = trim ( $_POST ['wingpay_mchid'] );
		$payment_config ['key'] = trim ( $_POST ['wingpay_key'] );
		
		$payment_data = array ();
		$payment_data ['enabled'] = $is_enabled;
		$payment_data ['pay_config'] = serialize ( $payment_config );
		$payment_data ['pay_order'] = 5;
		$payment_data ['pay_type'] = $this->pay_type;
		
		$where = array ('token' => $this->token, 'branch_id' => $this->branch_id, 'pay_code' => 'wingpay' );
		$payment = $this->payment_db->where ( $where )->find ();
		if ($payment) {
			$this->payment_db->where ( $where )->save ( $payment_data );
		} else {
			$payment_data ['token'] = $this->token;
			$payment_data ['branch_id'] = $this->branch_id;
			$payment_data ['pay_code'] = 'wingpay';
			$payment_data ['pay_name'] = '翼支付';
			$payment_data ['pay_fee'] = 0;
			$payment_data ['is_cod'] = 0;
			$payment_data ['is_online'] = 1;
			$this->payment_db->data ( $payment_data )->add ();
		}
	}

}
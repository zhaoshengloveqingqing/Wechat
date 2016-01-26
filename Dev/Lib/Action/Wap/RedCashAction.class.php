<?php
echo '<pre>';
header("Content-type: text/html; charset=utf-8");
class RedCashAction extends Action {
	private $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
	
	function  __construct($wechat = array()){
		$this->redcash_id = 2;//$_GET['redcash_id'];
		$this->openid = 'oQGMstzYJjg3EFMLQD0kWtuSpPo8';//$_GET['openid'];
		$this->token = '54016446z8d';//$_GET['token'];
	}
	
	function index() {
		$payconf = M('redcash_wxconf')->where(array('token' => $this->token))->find();
		if ( !$payconf['mchid'] || !$payconf['appid'] ||!$payconf['key']) {
			die('微信参数配置不完整');
		}
		
		$setting = M('redcash_setting')->where(array('token' => $this->token, 'id' => $this->redcash_id))->find();
		$setting['mchid'] = $payconf['mchid'];
		$setting['appid'] = $payconf['appid'];
		$setting['key'] = $payconf['key'];
		
		if ($setting['status'] == '1') {
			$money = intval($setting['fixed_amount'] * 100); 
			$setting['min_value'] = $money;
			$setting['max_value'] = $money;
			$setting['total_amount'] = $money;
		}
		if (!$setting['nick_name'] || !$setting['send_name']  || !$setting['fixed_amount']  || !$setting['wishing']  || !$setting['act_name']  || !$setting['remark']) {
			die('活动信息配置不完整');
		}
		
		$certs = C('certs');
		if (!$certs[$this->token]) {
			die('未设置微信支付证书信息');
		}
		$setting['certs'] = array('certs' => $certs[$this->token]);
		$this->sendRedPack( $setting);
	}
	
	function sendRedPack($conf = array()) {
		$package = array();
		$package['nonce_str'] = '0sd0acp0xezgowahc1di1a89aqmr0wc5';//$this->createNoncestr(32);
		$package['mch_billno'] = '10026873201504141822108922';//$conf['mchid'].date('YmdHis').rand(1000, 9999);
		$package['mch_id'] = $conf['mchid'];
		$package['wxappid'] = $conf['appid'];
		$package['nick_name'] = $conf['nick_name'];
		$package['send_name'] = $conf['send_name'] ;
		$package['re_openid'] = $this->openid;
		$package['total_amount'] = $conf['total_amount'];
		$package['min_value'] = $conf['min_value'];
		$package['max_value'] = $conf['max_value'];
		$package['total_num'] = 1;
		$package['wishing'] = $conf['wishing'];
		$package['client_ip'] = $this->getClientIP();
		$package['act_name'] = $conf['act_name'];
		$package['remark'] = $conf['remark'];
		ksort($package, SORT_STRING);
		$strSign = '';
		foreach($package as $key => $v) {
			$strSign .= "{$key}={$v}&";
		}
		$strSign .= "key={$conf['key']}";
		$package['sign'] = strtoupper(md5($strSign));
		$xml = $this->arrayToXml($package);
	
		$response = $this->http_request($this->url, $xml, $conf['certs'], 'post');
		$responseObj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
		$aMsg = (array)$responseObj;
		file_put_contents(RUNTIME_PATH.'log', print_r($aMsg, 1));die;
		$db_data =array('token' => $this->token, 'mch_billno' => $package['mch_billno'], 'openid' => $package['re_openid'], 'total_amount' => $package['total_amount'], 'param_msg' => serialize($package), 'create_time' => date('Y-m-d H:i:s'));
		if (isset($aMsg['err_code'])) {
			$db_data['err_code'] = $aMsg['err_code'];
			$db_data['err_code_des'] = $aMsg['err_code_des'];
		}else{
			$db_data['err_code'] = 'SUCCESS';
			$db_data['err_code_des'] = '发送成功';
		}
		$db_data['return_msg'] = serialize($aMsg);
		M('redcash_list')->add($db_data);
	}
	
	function createNoncestr( $length = 32 ) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
	}
	
	function getClientIP() {
		$onlineip = '';
		if (getenv ( 'HTTP_CLIENT_IP' ) && strcasecmp ( getenv ( 'HTTP_CLIENT_IP' ), 'unknown' )) {
			$onlineip = getenv ( 'HTTP_CLIENT_IP' );
		} elseif (getenv ( 'HTTP_X_FORWARDED_FOR' ) && strcasecmp ( getenv ( 'HTTP_X_FORWARDED_FOR' ), 'unknown' )) {
			$onlineip = getenv ( 'HTTP_X_FORWARDED_FOR' );
		} elseif (getenv ( 'REMOTE_ADDR' ) && strcasecmp ( getenv ( 'REMOTE_ADDR' ), 'unknown' )) {
			$onlineip = getenv ( 'REMOTE_ADDR' );
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], 'unknown' )) {
			$onlineip = $_SERVER ['REMOTE_ADDR'];
		}
		return $onlineip;
	}
	
	function arrayToXml($arr = null){
		if(!is_array($arr) || empty($arr)){
			die("参数不为数组无法解析");
		}
		$xml = "<xml>";
		foreach ($arr as $key=>$val){
			if (is_numeric($val)){
				$xml.="<".$key.">".$val."</".$key.">"; 
			}else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
			}
		}
		$xml.="</xml>";
		return $xml; 
	}
	
	function http_request($url, $fields, $params, $method='get', $second=30){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_TIMEOUT, $second);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch,CURLOPT_HEADER,FALSE);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		if (isset($params['certs'])) {
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $params['certs']['SSLCERT']);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $params['certs']['SSLKEY']);
			curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
			curl_setopt($ch,CURLOPT_CAINFO, $params['certs']['CAINFO']);
		}
		if ($method=='post') {
			curl_setopt($ch,CURLOPT_POST, true);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}
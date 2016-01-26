<?php
class WingPayApi {

	private $parameters = array();
	private $curtype = 'RMB';   //币种
	private $encodetype = 1;    //加密方式0：不加密 1：MD5
	private $busicode = "0001"; //业务类型  default 0001
	private $productid = '20';  //业务标识
	private $tmnum = '02';      //终端号码
	private $customerid = '08'; //客户标识
	
	private $mobile_best_url = 'https://wappaywg.bestpay.com.cn/payWap.do';
	private $web_best_url    = 'https://webpaywg.bestpay.com.cn/payWeb.do';
	
	//pay config 
	function __construct($conf = array()) {
		$this->mch_id = $conf['mch_id'];
		$this->key = $conf['key'];
	}
	
	function trimString($value){
		$ret = null;
		if (null != $value) {
			$ret = $value;
			if (strlen($ret) == 0) {
				$ret = null;
			}
		}
		return $ret;
	}
	//参数设置
	function setParameter($parameter, $parameterValue) {
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	//MAC校验域
	function getMac() {
		$str_mac = '';
		$str_mac .=  'MERCHANTID='.$this->mch_id;
		$str_mac .=  '&ORDERSEQ='.$this->parameters['orderseq'];
		$str_mac .=  '&ORDERDATE='.$this->parameters['orderdate'];
		$str_mac .=  '&ORDERAMOUNT='.$this->parameters['orderamount'];
		$str_mac .=  '&KEY='.$this->key;
		return  md5($str_mac);
	}
	
	//调用翼支付接口
	function bestpay($type = 'mobile'){
		$url = $type == 'mobile' ? $this->mobile_best_url : $this->web_best_url;
		$mac = $this->getMac();
		$strHtml = '<html>
			<body onload="document.getElementById(\'wingpay\').submit();">
				<form id="wingpay" action="'.$url.'" method="post">
					<input type=hidden name="MERCHANTID" value="'.$this->mch_id.'"/>
					<input type=hidden name="ORDERSEQ" value="'.$this->parameters['orderseq'].'"/>
					<input type=hidden name="ORDERREQTRANSEQ" value="'.$this->parameters['orderreqtranseq'].'"/>
					<input type=hidden name="ORDERDATE" value="'.$this->parameters['orderdate'].'"/>
					<input type=hidden name="ORDERAMOUNT" value="'.$this->parameters['orderamount'].'"/>
					<input type=hidden name="PRODUCTAMOUNT" value="'.$this->parameters['productamount'].'"/>
					<input type=hidden name="ATTACHAMOUNT" value="'.$this->parameters['attachamount'].'"/>
					<input type=hidden name="CURTYPE" value="'.($this->parameters['curtype'] ? $this->parameters['curtype'] : $this->curtype).'"/>
					<input type=hidden name="ENCODETYPE" value="'.($this->parameters['encodetype'] ? $this->parameters['encodetype'] : $this->encodetype).'"/>
					<input type=hidden name="MERCHANTURL" value="'.$this->parameters['merchanturl'].'"/>
					<input type=hidden name="BACKMERCHANTURL" value="'.$this->parameters['backmerchanturl'].'"/>
					<input type=hidden name="BUSICODE" value="'.($this->parameters['busicode'] ? $this->parameters['busicode'] : $this->busicode).'"/>
					<input type=hidden name="PRODUCTDESC" value="'.$this->parameters['productdesc'].'"/>';
		if ($type == 'mobile') {
			$strHtml .= '<input type=hidden name="PRODUCTID" value="'.($this->parameters['productid'] ? $this->parameters['productid'] : $this->productid).'"/>
				<input type=hidden name="TMNUM" value="'.($this->parameters['tmnum'] ? $this->parameters['tmnum'] : $this->tmnum).'"/>
				<input type=hidden name="CUSTOMERID" value="'.($this->parameters['customerid'] ? $this->parameters['customerid'] : $this->customerids).'"/>';
		}
		$strHtml .= '<input type=hidden name="MAC" value="'.$mac.'"/>
				</form>
			</body>
		</html>';
		return $strHtml;
	}
	
	
	//签名认证
	function getSign($type = 'mobile'){
		$str_sign = '';
		$str_sign .=  'UPTRANSEQ='.$this->parameters['uptranseq'];
		$str_sign .=  '&MERCHANTID='.$this->mch_id;
		if ($type == 'mobile') {//mobile sign
			$str_sign .=  '&ORDERSEQ='.$this->parameters['orderseq'];
			$str_sign .=  '&ORDERAMOUNT='.$this->parameters['orderamount'];
			$str_sign .=  '&RETNCODE='.$this->parameters['retncode'];
			$str_sign .=  '&RETNINFO='.$this->parameters['retninfo'];
			$str_sign .=  '&TRANDATE='.$this->parameters['trandate'];
		}else{//web sign
			$str_sign .=  '&ORDERID='.$this->parameters['orderid']; //ORDERID为接口中的ORDERSEQ
			$str_sign .=  '&PAYMENT='.$this->parameters['payment']; //PAYMENT为接口中的ORDERAMOUNT
			$str_sign .=  '&RETNCODE='.$this->parameters['retncode'];
			$str_sign .=  '&RETNINFO='.$this->parameters['retninfo'];
			$str_sign .=  '&PAYDATE='.$this->parameters['paydate']; //PAYDATE为接口中的TRANDATE
		}
		$str_sign .=  '&KEY='.$this->key;
		return  md5($str_sign);
	}
}
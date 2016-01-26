<?php
class UnionPay {

	private $parameters = array();
	
	private $web_pay_url = 'https://payment.ChinaPay.com/pay/TransGet';
	
	function __construct($conf = array()){
		$this->parameters = array_merge($this->parameters, $conf);
	}
	
	//参数设置
	function setParameter($parameter, $parameterValue) {
		$this->parameters[trim($parameter)] = trim($parameterValue);
	}
	
	//构造表单
    function _buildForm($paytype = 'mobile', $method = 'post', $charset = 'utf-8') {
		if (empty($this->parameters)) {
			die('请设置调用接口参数');
		}
		$payurl = $paytype == 'mobile' ? '' : $this->web_pay_url;
		
        header("Content-type:text/html;charset={$charset}");
        $sHtml = '<body onload="document.getElementById(\'unionpay\').submit();">
        	<form id="unionpay" name="unionpay" action="'.$payurl.'" method="'.$method.'">';
        foreach ($this->parameters as $k => $v) {
            $sHtml.= '<input type="hidden" name="'.$k.'" value="'.$v.'"/>';
        }
        $sHtml .= "</form></body>";
        return $sHtml;
    }
}
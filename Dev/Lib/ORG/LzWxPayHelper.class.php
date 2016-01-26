<?php
/**
* 该文件简单改改WxPay.php下面的WxPayHelper
* 尽量保证该类不要和模块有耦合关系：比如商城微支付，所有和商城订单相关的逻辑都放在ShopAction 和ShopWxpayAction中
*/
include_once("WxPay/CommonUtil.php");
//include_once("WxPay.config.php");
include_once("WxPay/SDKRuntimeException.class.php");
include_once("WxPay/MD5SignUtil.php");

class LzWxPayHelper
{
	var $parameters; //cft 参数
        
        private $APPID;
        private $APPKEY; // paysign key
        private $SIGNTYPE = 'SHA1';
        private $PARTNERKEY;
        private $APPSERCERT;
        private $PARTNERID;
	function __construct($wxpay_config)
	{
            $this->APPID = $wxpay_config['APPID'];
            $this->APPKEY = $wxpay_config['APPKEY'];
            //$this->SIGNTYPE = $wxpay_config['SIGNTYPE'];
            $this->PARTNERID = $wxpay_config['PARTNERID']; 
            $this->PARTNERKEY = $wxpay_config['PARTNERKEY'];
            $this->APPSERCERT = $wxpay_config['APPSERCERT'];    
            $this->PARTNERID = $wxpay_config['PARTNERID']; 
        }
        
	function setParameter($parameter, $parameterValue) {
            $commonUtil = new CommonUtil();
            $this->parameters[$commonUtil->trimString($parameter)] = $commonUtil->trimString($parameterValue);
	}
        
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
        
	protected function create_noncestr( $length = 16 ) {  
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";  
		$str ="";  
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
			//$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $str;  
	}
        
	function check_cft_parameters(){
		if($this->parameters["bank_type"] == null || $this->parameters["body"] == null || $this->parameters["partner"] == null || 
			$this->parameters["out_trade_no"] == null || $this->parameters["total_fee"] == null || $this->parameters["fee_type"] == null ||
			$this->parameters["notify_url"] == null || $this->parameters["spbill_create_ip"] == null || $this->parameters["input_charset"] == null
			)
		{
			return false;
		}
		return true;

	}
        
	protected function get_cft_package(){
		try {
			
			if (null == $this->PARTNERKEY || "" == $this->PARTNERKEY ) {
				throw new SDKRuntimeException("密钥不能为空！" . "<br>");
			}
			$commonUtil = new CommonUtil();
			ksort($this->parameters);
			$unSignParaString = $commonUtil->formatQueryParaMap($this->parameters, false);
			$paraString = $commonUtil->formatQueryParaMap($this->parameters, true);

			$md5SignUtil = new MD5SignUtil();
			return $paraString . "&sign=" . $md5SignUtil->sign($unSignParaString,$commonUtil->trimString($this->PARTNERKEY));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}
	public function get_biz_sign($bizObj){
		 foreach ($bizObj as $k => $v){
			 $bizParameters[strtolower($k)] = $v;
		 }
		 try {
		 	if($this->APPKEY == ""){
		 			throw new SDKRuntimeException("APPKEY为空！" . "<br>");
		 	}
		 	$bizParameters["appkey"] = $this->APPKEY;
		 	ksort($bizParameters);
		 	//var_dump($bizParameters);
		 	$commonUtil = new CommonUtil();
		 	$bizString = $commonUtil->formatBizQueryParaMap($bizParameters, false);
		 	//var_dump($bizString);
		 	return sha1($bizString);
		 }catch (SDKRuntimeException $e)
		 {
			die($e->errorMessage());
		 }
	}
	//生成app支付请求json
	/*
    {
	"appid":"wwwwb4f85f3a797777",
	"traceid":"crestxu",
	"noncestr":"111112222233333",
	"package":"bank_type=WX&body=XXX&fee_type=1&input_charset=GBK&notify_url=http%3a%2f%2f
		www.qq.com&out_trade_no=16642817866003386000&partner=1900000109&spbill_create_ip=127.0.0.1&total_fee=1&sign=BEEF37AD19575D92E191C1E4B1474CA9",
	"timestamp":1381405298,
	"app_signature":"53cca9d47b883bd4a5c85a9300df3da0cb48565c",
	"sign_method":"sha1"
	}
	*/
	function create_app_package($traceid=""){
		//echo $this->create_noncestr();
        try {
           //var_dump($this->parameters);
		   if($this->check_cft_parameters() == false) {
			   throw new SDKRuntimeException("生成package参数缺失！" . "<br>");
		    }
		    $nativeObj["appid"] = $this->APPID;
		    $nativeObj["package"] = $this->get_cft_package();
		    $nativeObj["timestamp"] = time();
		    $nativeObj["traceid"] = $traceid;
		    $nativeObj["noncestr"] = $this->create_noncestr();
		    $nativeObj["app_signature"] = $this->get_biz_sign($nativeObj);
		    $nativeObj["sign_method"] = $this->SIGNTYPE;


		   
		    return   json_encode($nativeObj);

		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		
	}
	//生成jsapi支付请求json
	/*
	"appId" : "wxf8b4f85f3a794e77", //公众号名称，由商户传入
	"timeStamp" : "189026618", //时间戳这里随意使用了一个值
	"nonceStr" : "adssdasssd13d", //随机串
	"package" : "bank_type=WX&body=XXX&fee_type=1&input_charset=GBK&notify_url=http%3a%2f
	%2fwww.qq.com&out_trade_no=16642817866003386000&partner=1900000109&spbill_create_i
	p=127.0.0.1&total_fee=1&sign=BEEF37AD19575D92E191C1E4B1474CA9",
	//扩展字段，由商户传入
	"signType" : "SHA1", //微信签名方式:sha1
	"paySign" : "7717231c335a05165b1874658306fa431fe9a0de" //微信签名
	*/
        function create_biz_package(){
		 try {
		  
			if($this->check_cft_parameters() == false) {   
			   throw new SDKRuntimeException("生成package参数缺失！" . "<br>");
		    }
		    $nativeObj["appId"] = $this->APPID;
		    $nativeObj["package"] = $this->get_cft_package();
		    $nativeObj["timeStamp"] = time();
		    $nativeObj["nonceStr"] = $this->create_noncestr();
		    $nativeObj["paySign"] = $this->get_biz_sign($nativeObj);
		    $nativeObj["signType"] = $this->SIGNTYPE;
		   
		    return   $nativeObj;
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		   
		
	}
	
	function create_paysign(){
		if (null == $this->PARTNERKEY || "" == $this->PARTNERKEY ) {
			throw new SDKRuntimeException("密钥不能为空！" . "<br>");
		}
		$commonUtil = new CommonUtil();
		$unSignParaString = $commonUtil->formatQueryParaMap($this->parameters, false);

		$md5SignUtil = new MD5SignUtil();
		return $md5SignUtil->sign($unSignParaString,$commonUtil->trimString($this->PARTNERKEY));
	}
	//生成原生支付url
	/*
	weixin://wxpay/bizpayurl?sign=XXXXX&appid=XXXXXX&productid=XXXXXX&timestamp=XXXXXX&noncestr=XXXXXX
	*/
	function create_native_url($productid){

			$commonUtil = new CommonUtil();
		    $nativeObj["appid"] = $this->APPID;
		    $nativeObj["productid"] = urlencode($productid);
		    $nativeObj["timestamp"] = time();
		    $nativeObj["noncestr"] = $this->create_noncestr();
		    $nativeObj["sign"] = $this->get_biz_sign($nativeObj);
		    $bizString = $commonUtil->formatBizQueryParaMap($nativeObj, false);
		    return "weixin://wxpay/bizpayurl?".$bizString;
		    
	}
	//生成原生支付请求xml
	/*
	<xml>
    <AppId><![CDATA[wwwwb4f85f3a797777]]></AppId>
    <Package><![CDATA[a=1&url=http%3A%2F%2Fwww.qq.com]]></Package>
    <TimeStamp> 1369745073</TimeStamp>
    <NonceStr><![CDATA[iuytxA0cH6PyTAVISB28]]></NonceStr>
    <RetCode>0</RetCode>
    <RetErrMsg><![CDATA[ok]]></ RetErrMsg>
    <AppSignature><![CDATA[53cca9d47b883bd4a5c85a9300df3da0cb48565c]]>
    </AppSignature>
    <SignMethod><![CDATA[sha1]]></ SignMethod >
    </xml>
	*/
	function create_native_package($retcode = 0, $reterrmsg = "ok"){
		 try {
		   if($this->check_cft_parameters() == false && $retcode == 0) {   //如果是正常的返回， 检查财付通的参数
			   throw new SDKRuntimeException("生成package参数缺失！" . "<br>");
		    }
		    $nativeObj["AppId"] = $this->APPID;
		    $nativeObj["Package"] = $this->get_cft_package();
		    $nativeObj["TimeStamp"] = time();
		    $nativeObj["NonceStr"] = $this->create_noncestr();
		    $nativeObj["RetCode"] = $retcode;
		    $nativeObj["RetErrMsg"] = $reterrmsg;
		    $nativeObj["AppSignature"] = $this->get_biz_sign($nativeObj);
		    $nativeObj["SignMethod"] = $this->SIGNTYPE;
		    $commonUtil = new CommonUtil();

		    return  $commonUtil->arrayToXml($nativeObj);
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		

	}
        
        // 微信notify接口扩展方法
        public function get_notify_biz_url_sign() {
            try {
                    if($this->parameters["trade_state"] == null || $this->parameters["trade_mode"] == null)
                    {
                            return false;
                    }
                    if (null == $this->PARTNERKEY || "" == $this->PARTNERKEY ) {
                            throw new SDKRuntimeException("密钥不能为空！" . "<br>");
                    }
                    $commonUtil = new CommonUtil();
                    ksort($this->parameters);
                    $unSignParaString = $commonUtil->formatQueryParaMap($this->parameters, false);
                    //$paraString = $commonUtil->formatQueryParaMap($this->parameters, true);

                    $md5SignUtil = new MD5SignUtil();
                    return $md5SignUtil->sign($unSignParaString,$commonUtil->trimString($this->PARTNERKEY));
            }catch (SDKRuntimeException $e)
            {
                    die($e->errorMessage());
            }	   
        }
        public function get_feedback_sign($feedback) {
            
            return $this->get_biz_sign($feedback);
        }
        
        
        /*
         * $deliver_status: 1 表明成功,0 表明失败,失败时需要在 deliver_msg 填上失败原因;
         */
        public function delivernotify($access_token, $trade) {
            $url = 'https://api.weixin.qq.com/pay/delivernotify?access_token='.$access_token;
            
            $data["appid"] = $this->APPID;
            $data["openid"] = $trade['wecha_id'];
            $data["transid"] = $trade['n_transaction_id'];
            $data["out_trade_no"] = $trade['order_sn'];
            $data["deliver_timestamp"] = time();
            $data["deliver_status"] = "1";
            $data["deliver_msg"] = "ok";
            $data["app_signature"] = $this->get_biz_sign($data);
            $data["sign_method"] = "sha1";
            
            $json_data = json_encode($data);
            
            
            
            $ret = $this->sendPost($url, $json_data);
            if($ret['errcode'] == 0) {
                Log::record('LzWxPayHelper: delivernotify succeed transid:'.$data['transid'].' out_trade_no:'.$data["out_trade_no"], Log::INFO);
                Log::save();
                return true;
            }
            Log::record('LzWxPayHelper: delivernotify fail transid:'.$data['transid'].' out_trade_no:'.$data["out_trade_no"]);
            Log::save();
            return false;
            
        }
        
        public function orderquery($access_token, $trade) {
            $url = 'https://api.weixin.qq.com/pay/orderquery?access_token='.$access_token;
            
            // generate package
            $pkg['out_trade_no'] = $trade['order_sn'];
            $pkg['partner'] = $this->PARTNERID;
            $commonUtil = new CommonUtil();
            ksort($pkg);
            $unSignParaString = $commonUtil->formatQueryParaMap($pkg, false);
            $md5SignUtil = new MD5SignUtil();
            $pkg_sign = $md5SignUtil->sign($unSignParaString,$commonUtil->trimString($this->PARTNERKEY));
            $package_str = $unSignParaString .'&sign='.strtoupper($pkg_sign);
            
            $data["appid"] = $this->APPID;
            $data["package"] = $package_str;
            $data["timestamp"] = strval(time());
            $data['app_signature'] = $this->get_biz_sign($data);
            $data['sign_method'] = 'sha1';
            
            $json_data = json_encode($data);
            $ret = $this->sendPost($url, $json_data);
            Log::record('wxpay orderquery order_sn:'.$trade['order_sn'].' res:'.print_r($ret,true), Log::INFO);
            Log::save();
            return $ret;
            
        }
        private function sendPost($url, $data){
            Log::record("LzWxPayHelper request ".$url." data:".print_r($data,true)."\r\n", Log::DEBUG);       
            Log::save();
            
            $output = $this->http_post($url, $data);
            
            Log::record("LzWxPayHelper req:".$url.' res:'.$output."\r\n", Log::DEBUG);       
            Log::save();

            $ret_json = json_decode($output, true);

            return $ret_json;
       }
       
       private function http_post($url, $data) {
            $ch = curl_init(); 
            $header = array();
            $header[]= 'Content-Type: text/plain';
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
       }
}

?>

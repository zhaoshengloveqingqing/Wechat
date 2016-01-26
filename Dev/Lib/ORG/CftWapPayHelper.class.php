<?php
require_once("WxPay/CommonUtil.php");
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CftWapPayHelper {
    var $bargainor_id;
    var $secret;
    
    var $parameters; //cft 参数
    
    function __construct($partnerId, $partnerKey) {
        $this->bargainor_id = $partnerId;
        $this->secret = $partnerKey;
    }
    
    function setParameter($parameter, $parameterValue) {
        $this->parameters[CommonUtil::trimString($parameter)] = CommonUtil::trimString($parameterValue);
    }

    function getParameter($parameter) {
        return $this->parameters[$parameter];
    }
    
    /*
     * Cft WAP pay init request and response
     */
    function getTokenId() {
        $gw = "https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_init.cgi";
        $requestUrl = $this->getRequestURL($gw);
        Log::record('cftpay init request url:'.$requestUrl, Log::INFO);
        $response = $this->http_get($requestUrl);
        Log::record('cftpay init response:'.$response, Log::INFO);
        Log::save();
        $xmlData = simplexml_load_string($response);
        $token_id = (string)$xmlData->token_id;
        if(!empty($token_id)) {
            return $token_id;
        }
        
        return null;
    }
    
    private function http_get($url) {
            $ch = curl_init(); 
            $header = array();
            $header[]= 'Content-Type: text/plain';
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
       }
       
       
    private function getRequestURL($gw) {
         $this->createSign();

         $reqPar = "";
         ksort($this->parameters);
         foreach($this->parameters as $k => $v) {
                 $reqPar .= $k . "=" . rawurlencode($v) . "&";
         }

         $reqPar = substr($reqPar, 0, strlen($reqPar)-1);

         $requestURL = $gw . "?" . $reqPar;

         return $requestURL;
     }
        
    private function createSign() {
        $signPars = "";
        ksort($this->parameters);
        foreach($this->parameters as $k => $v) {
                if("" != $v && "sign" != $k) {
                        $signPars .= $k . "=" . $v . "&";
                }
        }
        $signPars .= "key=" . $this->secret;
        //dump($signPars);
        $sign = strtoupper(md5($signPars));
        $this->setParameter("sign", $sign);

    }	
    
    function isTenpaySign() {
        $signPars = "";
        ksort($this->parameters);
        foreach($this->parameters as $k => $v) {
                if("sign" != $k && "" != $v) {
                        $signPars .= $k . "=" . $v . "&";
                }
        }
        $signPars .= "key=" . $this->secret;

        $sign = strtolower(md5($signPars));

        $tenpaySign = strtolower($this->getParameter("sign"));

        
        $ret = ($sign == $tenpaySign);
        
        if(! $ret) {
            Log::record('isTenpaySign: signPars:'.$signPars);
            Log::save();
        }
        return $ret;
		
    }
    
    function query_order() {
        $gw = "https://wap.tenpay.com/cgi-bin/wappayv2.0/wm_query_order.cgi";
        $requestUrl = $this->getRequestURL($gw);
        Log::record('cftpay query_order request url:'.$requestUrl, Log::INFO);
        $response = $this->http_get($requestUrl);
        Log::record('cftpay query_order response:'.$response, Log::INFO);
        Log::save();
        $xmlData = simplexml_load_string($response);
        return $xmlData;
    }
    
}
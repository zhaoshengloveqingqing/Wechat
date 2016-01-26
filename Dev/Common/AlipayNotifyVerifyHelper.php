<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AlipayNotifyVerifyHelper {
    const HTTP_REQUEST_TIMEOUT_SECS = 15;
    public static function ali_notify_verify($partner, $notify_id) {
        Log::record(
                'start ali_notify_verify:'.$partner.' '.$notify_id,
                Log::INFO);
        Log::save();
        $s = curl_init();

        $url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&partner='.$partner.'&notify_id='.$notify_id;
        Log::record(
                'ali_notify_verify:'.$url,
                Log::INFO);
        curl_setopt($s,CURLOPT_URL, $url);
        $headers = array(
            'Host: www.lingzhtech.com',
            'Accept-Language: zh-CN,zh;q=0.8',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'X-Requested-With: XMLHttpRequest',
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36',
            'Referer: http://www.lingzhtech.com'
            );

        curl_setopt($s,CURLOPT_HEADER,true); 
        curl_setopt($s,CURLOPT_HTTPHEADER, $headers); 
        //curl_setopt($s, CURLOPT_COOKIEJAR, RUNTIME_PATH.$this->cookieFile);
        //curl_setopt($s, CURLOPT_COOKIEFILE, RUNTIME_PATH.$this->cookieFile);
        curl_setopt($s, CURLOPT_FRESH_CONNECT, 1);

        curl_setopt($s,CURLOPT_TIMEOUT, self::HTTP_REQUEST_TIMEOUT_SECS); 
        curl_setopt($s,CURLOPT_MAXREDIRS, 3); 
        curl_setopt($s,CURLOPT_FOLLOWLOCATION,true); 

        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);

        curl_setopt($s,  CURLINFO_HEADER_OUT, true);
        curl_setopt($s, CURLOPT_HTTPGET,true);
        curl_setopt($s, CURLOPT_COOKIESESSION, 1);
        curl_setopt($s, CURLOPT_COOKIE, 'Wed, 05 Feb 2014 04:45:21 GMT');

        $content = curl_exec($s); 

        $status = curl_getinfo($s); 
        $headerSize = curl_getinfo($s, CURLINFO_HEADER_SIZE);
        curl_close($s); 

        if($status['http_code'] != 200) {
            Log::record('ali_notify_verify fail: url:'.$url.' response:'.$status['http_code'].' '.$content, Log::INFO);
            Log::save();
            return false;
        }

        $header = substr($content, 0, $headerSize);
        $body = substr($content, $headerSize);

        $body = trim($body);
        if($body == 'true') {
            Log::record('Succeed ali_notify_verify'.$partner.' '.$notify_id.' response:'.$body, Log::INFO);
            Log::save();
            return TRUE;
        }
        Log::record('Fail ali_notify_verify'.$partner.' '.$notify_id.' response:'.$body, Log::INFO);
        Log::save();
        return FALSE;
    }
}
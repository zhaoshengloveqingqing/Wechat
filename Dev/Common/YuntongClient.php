<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class YuntongClient {
    private $gateway = 'http://test2.cloudcall.cn:80/ccapi';
    private $_token; //商家在领众平台的token
    private $_wecha_id;
    
    private $_spid;
    private $_appid;
    private $_passwd;
    
    function YuntongClient($wecha_id, $token) {
        $this->_token = $token;
        $this->_wecha_id = $wecha_id;
        
        $m = M('yuntong_merchants')->where(array('token'=>$this->_token))->find();
        if(!empty($m)) {
            $this->_spid = $m['spid'];
            $this->_appid = $m['appid'];
            $this->_passwd = $m['passwd'];
        }
    }
    
    function is_binded() {
        $user = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->find();
        if(empty($user) || empty($user['tel'])) {
            return false;
        }
        
        return true;
    }
    
    function bind($tel, $user_pwd) {
        // 没有办法验证密码
        $user = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->find();
        if(empty($user)) {
            $data = array();
            $data['token'] = $this->_token;
            $data['wecha_id'] = $this->_wecha_id;
            $data['tel'] = $tel;
            $data['last_bind_ts'] = time();
            $data['bind_times'] = 1;
            if(FALSE !== M('yuntong_users')->add($data)) {
                return true;
            }   
            return false;
        }else {
            $data = array();
            $data['tel'] =$tel;
            $data['last_bind_ts'] = time();
            $data['bind_times'] = $user['bind_times'] + 1;
            $ret = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->save($data);
            return $ret !== FALSE;
        }
    }
    
    function unbind() {
        $user = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->find();
        if(!empty($user)) {
            $data = array();
            $data['tel'] = null;
            $data['last_unbind_ts'] = time();
            $data['unbind_times'] = $user['unbind_times'] + 1;
            $ret = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->save($data);
            if($ret !== FALSE) {
                return $user['tel'];
            }
        }
        
        return false;
    }
    
    function register($tel) {
        $url = $this->gateway . '/account/smsregist.do';
        $data = array();
        $data['Spid'] = $this->_spid;
        $data['Appid'] = $this->_appid;
        $data['Passwd'] = $this->_passwd;
        $data['Telnumber'] = $tel;
        $response = $this->http_post($url, json_encode($data));
        return $response;
    }
    
    function get_balance() {
        $user = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->find();
        
        $url = $this->gateway . '/user/getbalance.do';
        $data = array();
        $data['Spid'] = $this->_spid;
        $data['Appid'] = $this->_appid;
        $data['Passwd'] = $this->_passwd;
        $data['Telnumber'] = $user['tel'];
        $response = $this->http_post($url, json_encode($data));
        
        $data = array();
        $data['last_balance_ts'] = time();
        $data['balance_times'] = $user['balance_times'] + 1;
        $ret = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->save($data);
        
        return $response;
    }
    
    function sign() {
        $user = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->find();
        
        $url = $this->gateway . '/application/sign.do';
        $data = array();
        $data['Spid'] = $this->_spid;
        $data['Appid'] = $this->_appid;
        $data['Passwd'] = $this->_passwd;
        $data['Telnumber'] = $user['tel'];
        $response = $this->http_post($url, json_encode($data));
        
        $data = array();
        $data['last_sign_ts'] = time();
        $data['sign_times'] = $user['sign_times'] + 1;
        $ret = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->save($data);
        
        return $response;
    }
    
    function call($bld_tel) {
        $user = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->find();
        if($bld_tel == $user['tel']) {
            return array('result'=>'failed','text'=>'呼叫失败，主叫方和被叫方不能相同！');
        }
        $url = $this->gateway . '/callback/callback.do';
        $data = array();
        $data['Spid'] = $this->_spid;
        $data['Appid'] = $this->_appid;
        $data['Passwd'] = $this->_passwd;
        $data['ClgNumber'] = $user['tel'];
        $data['CldNumber'] = $bld_tel;
        $response = $this->http_post($url, json_encode($data));
        
        $data = array();
        $data['last_call_ts'] = time();
        $data['call_times'] = $user['call_times'] + 1;
        $ret = M('yuntong_users')->where(array('token'=>$this->_token, 'wecha_id'=>$this->_wecha_id))->save($data);
        
        return $response;
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
           Log::record($url.' '.print_r($data, true).'  '.$output);
           Log::save();
           return json_decode($output, true);
           
      }
    
}
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WeixinSimulatorAction
 *
 * @author L
 */
class WeixinClientAction extends UserAction{
    
    protected function _initialize()
    {
        parent::_initialize();
        parent::checkOpenedFunction('shouye');
    }
    //put your code here
    public function Index(){
        
        $this->display();
    }
    
    //获取公众号ID和签名
    public function GetPubIdAndSinid(){
        $token = $_GET["token"];
        if(empty($token)){
           return;
        }
        $timestamp = strtotime("now");
        $nonce = rand(1000000000, 9999999999999);    
        $db=D('wxuser');
        $where['token']=$token;
        $publicAccount = $db->where($where)->find();
        $tmpArr = array($token, $timestamp, $nonce);
	sort($tmpArr, SORT_STRING);
	$signature  = sha1(implode($tmpArr));
        $arr=array(
            'token'=>$token,
            'nonce'=>$nonce,
            'wxid'=>$publicAccount['wxid'],
            'signature'=>$signature,
            'timestamp'=>$timestamp,
        );
        echo json_encode($arr);
    }
}

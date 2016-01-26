<?php

/* 
 * 短信验证码的统一管理：生成验证码，发送，并提供认证验证码的正确性的接口；
 * 至于某个模块是否打开验证码，需要分化到具体模块中控制，该类不负责。
 * 比如订单，商家在创建订单时选择是否需要开启短信验证手机真实性，根据开关，手机端决定是否显示获取验证码的UI。
 */

class SmsvcodeAction extends BaseAction {
    
    public function getVCode(){
        if(!IS_POST) {
            return 1; // err
        }
        
        $token = $_POST['token'];
        $wecha_id = $_POST['wecha_id'];
        $telno = $_POST['telno'];
        $func = $_POST['func'];
        
        // 获得公司名字， 作为短信落款
        $merchant = M('wxuser')->where(array('token'=>$token, 'status'=>1))->field('company')->find();
        if(empty($merchant['company'])) {
            $company = "领众科技";
        }else {
            $company = $merchant['company'];
        }
        
        $result['errcode'] = 0;
        $result['msg'] = '';
        
        $Cache = Cache::getInstance('File',array('expire'=>'610'));
        $code = $Cache->get('vcode_'.$token.$telno);
        
        if(empty($code)) {
            $code = sprintf("%0".strlen($max)."d", mt_rand(100000,999999));
            $Cache->set('vcode_'.$token.$telno, $code);
            //$truth_code = $Cache->get('vcode_'.$token.$telno);
           
        }
        
        Log::record('truthcode:'.'vcode_'.$token.$telno.':'.$code);
        Log::save();
        
        include(LIB_PATH.'Action/SmsSender.class.php');
        $smsSender = new SmsSender();
        $vcode_tmpl = C('vcode');
        if (isset($vcode_tmpl)) 
        {
            $smsContent = str_replace("#merchant#",$company,$vcode_tmpl);
            $smsContent = str_replace("#code#",$code,$smsContent);
            $res = $smsSender->notify( $token, $func, $smsContent, $telno);
            if($res !== 0) 
            {
                $result['errcode'] = 1;
            }
        }
        else
        {
            $result['errcode'] = 1;
        }
        $this->ajaxReturn($result, 'json');
    }
    
    static function verifyVCode($token, $tel, $code) {
        $Cache = Cache::getInstance('File',array('expire'=>'610'));
        $truth_code = $Cache->get('vcode_'.$token.$tel);
        if(!empty($truth_code) && $truth_code == $code) {
            Log::record('verifyvcode succeed:'.$token.":".$tel.":".$code."", Log::DEBUG);
            Log::save();
            return true;
        }
        
        Log::record('verifyvcode fail:'.$token.":".$tel.":".$code.":".$truth_code);
        Log::save();
        return false;
    }
}
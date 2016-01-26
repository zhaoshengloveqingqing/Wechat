<?php
include_once(LIB_PATH.'ORG/SmsClient.class.php');

class SmsAction extends BackAction{
    
    public function index(){
        $smsClient = new SmsClient();
        $fee = $smsClient->getEachFee();
        $balance = $smsClient->getBalance();
        
        if(empty($fee)) {
            $this->assign('fee', '异常');
        }else{
            $this->assign('fee', $fee);
            
        }
        $this->assign('balance', $balance);
        
        if(!empty($fee)) {
            $this->assign('balancecnt', $balance / $fee);
        }else{
            $this->assign('balancecnt', '异常');
        }
        
        $this->display();
    }
    
    public function activate() {
        if(IS_POST) {
            $cardId = trim($_POST['cardid']);
            $cardPass = trim($_POST['cardPass']);
            $smsClient = new SmsClient();
            $stats =  $smsClient->chargeUp($cardId, $cardPass);
            if($stats !== null && $stats !== false && $stats == 0) {
                $this->success('充值成功!', U('Sms/index'));
            }else {
                echo '充值失败。错误码：'.$stats;
                //$this->error('登录失败!'.$stats, U('Sms/index'));
            }        
        }
    }
    
    public function manage() {
        if(IS_POST) {
            if(isset($_POST['login'])) {
                // login
                $smsClient = new SmsClient();
                $stats = $smsClient->login();
                
                if($stats !== null && $stats !== false && $stats == 0) {
                    $this->success('登录成功!', U('Sms/index'));
                }else {
                    echo '登录失败。错误码：'.$stats;
                    //$this->error('登录失败!'.$stats, U('Sms/index'));
                }
            }else if($_POST['logout']) {
                // logout
                $smsClient = new SmsClient();
                $stats = $smsClient->logout();
                if($stats !== null && $stats !== false && $stats == 0) {
                    $this->success('注销成功!', U('Sms/index'));
                }else {
                    echo '注销失败。错误码：'.$stats;
                    //$this->error('注销失败!'.$stats, U('Sms/index'));
                }
            }
        }
    }
}
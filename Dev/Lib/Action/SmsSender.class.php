<?php
include_once(LIB_PATH.'ORG/SmsClient.class.php');
class SmsSender
{
    function SmsSender() {
    }
    
    public function notify($token, $func, $text, $touser=null)
    {
        $user = M('wxuser')->where(array('token'=>$token))->find();
        if (empty($user)) {
            return -1;
        }
        $uid = $user['uid'];
        
        // 检查提醒是否开启
        $smsset = M('sms_set')->where(array('token'=>$token))->find();
        if (empty($touser) && strpos($smsset['function'], $func) === false) {
            return -1;
        }

        
        // 检查是否有余额
        $sms_account_db = M('smsaccount');
        $smsacount = $sms_account_db->where(array('user_id'=>$uid))->find();
        if ($smsacount['total'] <= $smsacount['used']) 
        {
            //余额不足
            $data['statusCode'] = '10000';
        }

        if (empty($touser)) 
        {
            $touser =  $smsset['tel'];
        }
        //检查号码格式
        if(!preg_match("/^1[3-9]{1}[0-9]{9}$/", $touser))
        {   
            //接收短信号码格式不对
            $data['statusCode'] = '10001';
        }
        
        $data['charged_count'] = $this->countSms($text);
        if ($data['charged_count'] > $smsacount['total'] - $smsacount['used']) 
        {
            //余额不足
            $data['statusCode'] = '10000';
        }

        if (!isset($data['statusCode'])) 
        {
            // send
            $smsClient = new SmsClient();
            $data['statusCode'] = $smsClient->sendSMS(array($touser), $text, time());
            
            if ($data['statusCode'] === '0') 
            {   // statuscode可以为空
                //'0'是扣费成功
                $sms_account_db->where(array('user_id'=>$uid))->save(array('used' => $smsacount['used'] + $data['charged_count']));
            }
        }

        $data['token']      = $token;
        $data['touser']     = $touser;
        $data['sendtime']   = time();
        $data['content']    = $text;
        $data['func']       = $func;
        // 记录
        M('sms_list')->add($data);
        if($data['statusCode'] === '0')
        {
            return 0;
        }
        return - 1;
    }

    /**
     * UTF-8编码情况下 ，140个英文字符或者70个中文字符
     * @param   string      $str        字符串
     *
     * @return  返回需要计费的短信条数
     */
    private function countSms($str)
    {
        $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));
     
        $en_len = strlen($str) - $length;
        $cn_len = intval($length / 3);//编码GBK，除以2

        $str_len = $en_len + 2 * $cn_len;
        $extra = $str_len % 140 > 0 ? 1 : 0;
        return (int)($str_len/140) + $extra;
    }
}
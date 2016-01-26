<?php

/* 
 * 该类用于用于生成充值码
 */
class InviteCodeGenerator {
    
    function __construct() {
    }
    
    
    ///  当type＝1:功能码时，$payload代表功能有效期
    ///  当type＝2:短信码时，$payload代表短信数
    ///  $func_groups: 用逗号分隔的功能组ID列表
    ///  $generator: 是谁生成了该码。
    ///  return value: FALSE： 失败；成功后返回批次号(创建时间)
    public function generate($codeCount,  $payload, $type, $func_groups = '', $generator='8', $init_status=0, $packageId=null) {
        $existingInviteCodes = M('invitecode')->field('code')->select();

        $newInviteCodes = $this->generate_invitationcodes($codeCount, $existingInviteCodes);
        $signatures = $this->generate_signatures($newInviteCodes);
        $createTime = time();
        $db = M('invitecode');
        $insertDataArray = array();
        for($i=0; $i<$codeCount; ++$i){
            $row = Array();
            $row['code'] = $newInviteCodes[$i];
            $row['signature'] = $signatures[$i];
            $row['function_group_list'] = $func_groups;
            $row['duration'] = $payload;
            $row['create_time'] = $createTime; //batch id
            $row['type'] = $type;

            $row['generator'] = $generator;
            $row['status'] = $init_status;
            $row['package'] = $packageId;
            
            $insertDataArray[$i] = $row;   
        }
        $res = $db->addAll($insertDataArray);

        $dbErr = $db->getDbError();
        $lastError = $db->getError();
        $ok = empty($dbErr) && empty($lastError);

        Log::record("Generate type:".$type." ".$codeCount." codes with payload:".$payload." funcGroup:".$func_groups." codeBatchId:".$createTime.($ok?"succeed":"fail"),
                Log::DEBUG);
        Log::save();

        if($ok){
            return $createTime;
        }

        return $ok;
    }
    
    private function verify_signature($inviteCode, $signature) {
        
        $pubkeyid = openssl_pkey_get_public(self::$inviteCodePubKey);
        // state whether signature is okay or not
        $ok = openssl_verify($inviteCode, base64_decode($signature), $pubkeyid);
        
        // free the key from memory
        openssl_free_key($pubkeyid);
        Log::record("Verify signature ".($ok?'succeed':'failed')." for invitecode ".$inviteCode, Log::DEBUG);
        Log::save();
        
        return $ok ? true : false;
    }
    
    private function generate_signatures($inviteCodeArray){
        $signatures = Array();
        $i = 0;
        foreach($inviteCodeArray as $code){
            $signatures[$i] = $this->generate_signature($code);
            $i ++;
        }
        
        return $signatures;
    }
    private function generate_signature($inviteCode) {
        
        $fp = fopen(CONF_PATH.'private_key.pem', "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);
        
        $keyid = openssl_pkey_get_private($priv_key, "lingzhtech123");
        $ok = openssl_sign($inviteCode, $out, $keyid);
        openssl_free_key($keyid);
        
        Log::record("generate signature ".($ok?'succeed':'failed')." for invitecode:".$inviteCode."\r\n", Log::DEBUG);       
        Log::save();
        
        if($ok){
            return base64_encode($out);
        }
        return false;
    }
    
    private function generate_keys() {
        
        $res = openssl_pkey_new();
        openssl_pkey_export($res,$pri, 'lingzhtech123');
        $d= openssl_pkey_get_details($res);
        $pub = $d['key'];
        //var_dump($pri,$pub);
        $fp1 = fopen(CONF_PATH.'private_key.pem', "w");
        fwrite($fp1, $pri);
        fclose($fp1);
        $fp1 = fopen(CONF_PATH.'public_key.pem', "w");
        fwrite($fp1, $pub);
        fclose($fp1);
    }
    
    private function generate_invitationcodes($num_of_codes, $exclude_codes_array='', $code_length = 20 ){
        
        $inviteCodes = array();
        for ($i=0; $i < $num_of_codes; $i ++) {
            
            $code = "";
            for ($j=0; $j<$code_length; $j++) {
                $code .= $this->_legalCodeChars[mt_rand(0, strlen($this->_legalCodeChars) - 1)];
            }
            
            if(!in_array($code, $inviteCodes)&&
                    (!is_array($exclude_codes_array) || !in_array($code, $exclude_codes_array))) {
                $inviteCodes[$i] = $code;
            } else {
                $i --;
            }
        }
        
        return $inviteCodes;
    }
    
    
    
    // Load public key only once
    static function init(){
        $fp = fopen(CONF_PATH.'public_key.pem', "r");
        self::$inviteCodePubKey = fread($fp, 8192);
        fclose($fp);
    }
    
    private static $inviteCodePubKey;
    private $_legalCodeChars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXWZabcdefghijklmnopqrstuvwxyz";
}

InviteCodeGenerator::init();

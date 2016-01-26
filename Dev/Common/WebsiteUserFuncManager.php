<?php
require_once(COMMON_PATH.'/PublicAccountFuncManager.php');

/* 
 * 该类用于管理前台用户的功能组
 */
class WebsiteUserFuncManager {
    private $websiteUserId;
    function __construct($websiteUserId) {
        $this->websiteUserId = $websiteUserId;
    }
    
    function openDefaultFuncGroups() {
        // 为新注册用户自动打开部分功能
        $currTime = time();
        $currUserFuncs = M('user_func_group')->where(array('user_id'=>$this->websiteUserId))->select();

        $basicOpenFGs = array();
        $secondaryOpenFGs = array();
        if($currUserFuncs !== FALSE) {
             $basicDefaultFuncGroups = C('DEFAULT_OPEN_FUNC_GROUPS');
             foreach($basicDefaultFuncGroups as $funcGroupId) {
                 // 检查该功能组是否已经存在
                 $existing = false;
                 foreach($currUserFuncs as $currFunc) {
                     if($currFunc['group_id'] == $funcGroupId) {
                         $existing = true;
                         break;
                     }
                 }

                 if(!$existing) {
                     $userFuncGroupData = array();
                     $userFuncGroupData['user_id'] = $this->websiteUserId;
                     $userFuncGroupData['group_id'] = $funcGroupId;
                     $userFuncGroupData['start_time'] = $currTime;
                     $openDuration = intval(C('DEFAULT_OPEN_FUNC_GROUPS_DAYS'));
                     
                     if($openDuration == -1){
                         $expireTime = 2147483647;
                     }else {
                         $expireTime = $currTime + $openDuration * 24 * 60 * 60;
                     }
                     $userFuncGroupData['expire_time'] = $expireTime;
                     $userFuncGroupData['status'] = 1;
                     M('user_func_group')->add($userFuncGroupData);

                     array_push($basicOpenFGs, $funcGroupId);
                 }
             }

             // 自动开通辅助功能组列表：减少测试码的发送量
             $secondaryFuncGroups = C('DEFAULT_OPEN_FUNC_GROUPS_SECONDARY');
             foreach($secondaryFuncGroups as $secondaryFG) {
                 // 检查该功能组是否已经存在
                 $existing = false;
                 foreach($currUserFuncs as $currFunc) {
                     if($currFunc['group_id'] == $secondaryFG) {
                         $existing = true;
                         break;
                     }
                 }

                 if(!$existing) {
                     $userFuncGroupData = array();
                     $userFuncGroupData['user_id'] = $this->websiteUserId;
                     $userFuncGroupData['group_id'] = $secondaryFG;
                     $userFuncGroupData['start_time'] = $currTime;
                     $userFuncGroupData['expire_time'] = $currTime + intval(C('DEFAULT_OPEN_FUNC_GROUPS_SECONDARY_DAYS')) * 24 * 60 * 60;
                     $userFuncGroupData['status'] = 1;
                     M('user_func_group')->add($userFuncGroupData);
                     array_push($secondaryOpenFGs, $secondaryFG);
                 }
             }
        }

        log::record('user:'.$user['id'].' basicFGs:'.join(',', $basicOpenFGs).' secondaryFGs:'.join(',', $secondaryOpenFGs));
        log::save();

    }
    function activate($invitecode, $activator){
        
        $condition = array();
        if(empty($invitecode)){
            return array('success'=>0, 'error'=>'请指定充值码');
        }
        $pos =strpos($invitecode, ' ');
        if($pos !== FALSE) {
            return array('success'=>0, 'error'=>'无效的充值码');
        }
        if(empty($this->websiteUserId)){
            return array('success'=>0, 'error'=>'请指定商户');
        }
            
        unset($condition) ;
        $condition['code'] = $invitecode;
        $tb_invitecode = M('invitecode')->where($condition)->find();
        if(count($tb_invitecode) <= 0) {
            return array('success'=>0, 'error'=>'该邀请码('.$invitecode.')不存在');
        }
            
        if($tb_invitecode['status'] != 0) {
            return array('success'=>0, 'error'=>'该邀请码 已'.($tb_invitecode['status']==1?'激活':'删除').', 不能再用于充值');
           
        }
            
        // verify signature of this code with pub key;
        $verifyRes = $this->verify_signature($tb_invitecode['code'], $tb_invitecode['signature']);
        if(!$verifyRes){
            return array('success'=>0, 'error'=>'邀请码签名验证未通过');
        }
            
        $currTime = time();
        
        $inviteCodeType = intval($tb_invitecode['type']);
        if($inviteCodeType == 2) {
            $ret = $this->processSmsCode($tb_invitecode);
            if($ret['success'] == 0) {
                return $ret;
            }
        }else if($inviteCodeType == 1) {
            $this->processFuncCode($tb_invitecode);
        }
        
        // update InviteCode table
        $inviteCodeUpdateData = array();
        $inviteCodeUpdateData['activator'] = $activator;
        $inviteCodeUpdateData['final_user'] = $this->websiteUserId;
        $inviteCodeUpdateData['activate_time'] = $currTime;
        $inviteCodeUpdateData['status'] = 1;
        M('invitecode')->where('id='.$tb_invitecode['id'])->setField($inviteCodeUpdateData);
        
        
        return array('success'=>1, 'error'=>'');
    }
    
    private function processSmsCode($tb_invitecode) {
        $smsCount = intval($tb_invitecode['duration']);
        $res = $this->extendSmsAccount($smsCount);
        Log::record('processSmsCode: '.$tb_invitecode['code'].' res:'.print_r($res, true), Log::INFO);
        Log::save();
        return $res;
    }
    
    public function extendSmsAccount($smsCount) {
        $smsAccount = M('smsaccount')->where(array('user_id'=>$this->websiteUserId))->find();
        if(empty($smsAccount)) {
            // add a new record
            $data = array();
            $data['total'] = $smsCount;
            $data['used'] = 0;
            $data['user_id'] = $this->websiteUserId;
            $data['status'] = 1;
            $data['last_recharge_time'] = time();
            $id = M('smsaccount')->add($data);
            if(empty($id)) {
                Log::record('smsaccount recharge add record fail: userid:'
                        .$this->websiteUserId
                        .' failure:'.M('smsaccount')->getError());
                return array('success'=>0, 'error'=>'短信充值失败！请刷新重试！');
            }
        }else {
            $data = array();
            $smsAccount['status'] = 1;
            $smsAccount['last_recharge_time'] = time();
            $smsAccount['total'] = $smsAccount['total'] + $smsCount;
            $ret = M('smsaccount')->save($smsAccount);
            if($ret === false) {
                Log::record('smsaccount recharge save record fail: userid:'
                        .$this->websiteUserId
                        .' failure:'.M('smsaccount')->getError());
                return array('success'=>0, 'error'=>'短信充值失败！请刷新重试！');
            }
        }
        
        return array('success'=>1, 'error'=>'');
    }
    
    /// 根据邀请码开通功能组，并更新过期时间
    private function processFuncCode($tb_invitecode){
        
        $durationInSecs = ($tb_invitecode['duration'] + 1)* 24 * 3600;
        $codeFgIdList = explode(",", $tb_invitecode['function_group_list']);
        $this->extendFuncGroups($codeFgIdList, $durationInSecs);
    }
    
    /// 设置功能组对应的可用时长
    public function extendFuncGroups($fgList, $durationInSecs){
        Log::record('WebsiteUserFuncManager extend:'.print_r($fgList, true).' secs:'.$durationInSecs.' uid:'.$this->websiteUserId);
        Log::save();
        $currTime = time();
        $tb_userFG = M('user_func_group');
        $existingRelations = $tb_userFG->where('user_id='.$this->websiteUserId)->select();
        $existingGroupId2RealtionMap = array();
        foreach($existingRelations as $relation) {
            $existingGroupId2RealtionMap[$relation['group_id']] = $relation;
        }
            
        
        foreach($fgList as $codeFgId) {
            if(in_array($codeFgId, array_keys($existingGroupId2RealtionMap))) {
                // 该功能已经存在关系表里，只需要进行update
                $tmpUserFuncGroup = $existingGroupId2RealtionMap[$codeFgId];

                $tmp = array();
                if(!empty($tmpUserFuncGroup['expire_time']) && ($tmpUserFuncGroup['expire_time'] > $currTime)) {
                    // 该功能未过期
                    $tmp['start_time'] =$tmpUserFuncGroup['start_time'];
                    $tmp['expire_time'] = $tmpUserFuncGroup['expire_time'] + $durationInSecs;
                    $tmp['status'] = 1;
                }else{
                    $tmp['start_time'] = $currTime;
                    $tmp['expire_time'] = $currTime + $durationInSecs;
                    $tmp['status'] = 1;
                }

                $tb_userFG->where('id='.$tmpUserFuncGroup['id'])->setField($tmp);
            }else{
                $tmp = array();
                $tmp['user_id'] = $this->websiteUserId;
                $tmp['group_id'] = $codeFgId;
                $tmp['start_time'] = $currTime;
                $tmp['expire_time'] = $currTime + $durationInSecs;
                $tmp['status'] = 1;
                $tb_userFG->add($tmp);
            }
        }
        
        // 检查该用户是否已经配置公共账号，如果配置了，自动开通功能组对应的功能项，并复制模版数据。
        $publicAccount = M('wxuser')->where(array('uid'=>$this->websiteUserId, 'status'=>1))->field('token')->find();

        if($publicAccount) {
            //$websiteUserId, $uname, $token
            $userInfo = M('users')->where(array('id'=>$this->websiteUserId))->field('username')->find();
            $publicAccountFuncManager = new PublicAccountFuncManager($this->websiteUserId, $userInfo['username'], $publicAccount['token']);
            foreach($fgList as $fgId) {
                $publicAccountFuncManager->openSingleFuncGroup($fgId);
            }
        }
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
    
    function openFuncGroups() {
        
    }
    
    // Load public key only once
    static function init(){
        $fp = fopen(CONF_PATH.'public_key.pem', "r");
        self::$inviteCodePubKey = fread($fp, 8192);
        fclose($fp);
    }
    
    private static $inviteCodePubKey;
}

WebsiteUserFuncManager::init();

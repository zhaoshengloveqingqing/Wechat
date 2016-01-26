<?php
require_once(COMMON_PATH.'/InviteCodeGenerator.php');

class InviteCodeAction extends BackAction{
    
    public function index(){
        define('RES',THEME_PATH.'common');
        $isAdmin = 0;
        if(session(C('ADMIN_AUTH_KEY')) == true) {
            $isAdmin = 1;
        }
        $this->assign('isAdmin', $isAdmin);
        
        $condition = $this->prepareSearchCondition($isAdmin);
        
        $db = M('invitecode');
        $count= $db->where($condition)->count(); 
        
        
        $Page= new Page($count,50);
        $show= $Page->show();
        $inviteCodes = $db->where($condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('invitecodes',$inviteCodes);
        $this->assign('page',$show);
        
        $functionGroups = M('function_group')->select();
        $fgGroup = array();
        foreach($functionGroups as $fg){
            $fgGroup[$fg['id']] = $fg['name'];
        }
        $this->assign('funcGroups', $fgGroup);
        
        unset($condition);
        $condition['status'] = 1;
        $backendUsers = M('user')->where($condition)->field('id,username')->select();
        
        $this->assign('backendUsers', $backendUsers);
        
        //build backend user id to user name mapping
        $userId2NameMap = array();
        foreach($backendUsers as $backendUser) {
            $userId2NameMap[$backendUser['id']] = $backendUser['username'];
        }
        $this->assign('userId2NameMap', $userId2NameMap);
        
        // build roleid -> role name mapping
        unset($condition);
        $condition['status'] = 1;
        $tb_role = M('role')->where($condition)->field('id,name')->select();
        $roleId2NameMap = array();
        foreach($tb_role as $ro){
            $roleId2NameMap[$ro['id']] = $ro['name'];
        }
        $this->assign('roleId2NameMap', $roleId2NameMap);
        
        unset($condition);
        if(session(C('ADMIN_AUTH_KEY')) == true) {
            $condition = array();
        }else {
            $condition['administrator'] = session(C('USER_AUTH_KEY'));
        }
        $feUsers = M('users')->where($condition)->field('id, username')->order('username asc')->select();
        $this->assign('feUsers', $feUsers);
        
        
        $agentInfo = M('user')->where(array('id'=>session(C('USER_AUTH_KEY')), 'status'=>1))->find();
        $this->assign('agentInfo', $agentInfo);
        
        $agentPackages = C('AGENT_PACKAGES');
        $this->assign('agentPackages', $agentPackages);
        $this->display();
    }
    private function prepareSearchCondition($isAdmin) {
        $searchtype = '';
        $searchparam = '';
        if(!empty($_GET['searchtype'])) {
            $searchtype = $_GET['searchtype'];
            $searchparam = $_GET['searchparam'];
        }
        if(!empty($_POST['searchtype'])) {
            $searchtype = $_POST['searchtype'];
            $searchparam = $_POST['searchparam'];
        }
        
        $userId = session(C('USER_AUTH_KEY'));
        switch ($searchtype) {
            case 'status':
                switch($searchparam) {
                case '0':
                    $condition['status'] = 0;
                    $condition['manager'] = array('exp', 'is null');
                    break;
                case '100':
                    $condition['status'] = 0;
                    $condition['manager'] = array('exp', 'is not null');
                    break;
                case '1':
                    $condition['status'] = 1;
                    break;
                default:
                    break;
                }
                break;
            case 'manager':
                if($userId !== $searchparam || !$isAdmin) {
                    $condition['manager'] = $searchparam;
                }else{
                    $condition['manager'] = array('exp', 'is null');
                }
                $condition['status'] = array(neq, -1);
                break;
            default:
                if(!$isAdmin) {
                    $condition['manager'] = $userId;
                }
                $condition['status'] = array(neq, -1);
                break;
        }
        
        if(!$isAdmin) {
            $condition['manager'] = session(C('USER_AUTH_KEY'));
        }
        
        $this->assign('searchtype', $searchtype);
        $this->assign('searchparam', $searchparam);
        
        return $condition;
    }
    
    public function del($codeId) {
        M('invitecode')->where('id='.$codeId)->setField('status', '-1');
        $this->success('邀请码删除成功！',U('Admin/InviteCode/index'));             
    }
    public function assign_manager(){
        if(IS_POST)
        {
            if(!isset($_POST['assign_code_list'])) {
                $this->error('请选择要分配的邀请码!');
            }
            
            if(!isset($_POST['assign_to_manager']) || strlen($_POST['assign_to_manager']) <= 0)
            {
                $this->error('请选择代理商!');
            }
            $condition['user_id'] = $_POST['assign_to_manager'];
            $roleid = M('role_user')->where($condition)->getField('role_id');
            
            $tb_invitecode = M('invitecode');
            unset($condition);
            $condition['id'] = array('in', $_POST['assign_code_list']);
            
            $price = $_POST['price'];
            $currentTime = time();
            $updateData = array
                (
                    'manager'=>$_POST['assign_to_manager'],
                    'manager_role' => $roleid,
                    'assign_manager_time' =>$currentTime,
                    'assign_manager_price' =>$price,
                    'remarks' => $_POST['remarks'],
                );
            $res = $tb_invitecode->where($condition)->setField($updateData);
            if(!$res){
                Log::record('assign manager '.$_POST['assign_to_manager'].' failed for '.join($_POST['assign_to_manager']), Log::ERR);
                Log::record('getDBError:'.$tb_invitecode->getDbError().' getError:'.$tb_invitecode->getError(), Log::ERR);
                $this->error('分配失败！');
            }  else {
                 Log::record('assign manager '.$_POST['assign_to_manager'].' succeed for '.join($_POST['assign_to_manager']), Log::DEBUG);
                 $this->success('分配'.$res.'个邀请码成功！');
            }
        }
    }
    
    public function generate_sms() {
        if(IS_POST) {
            
            if(!isset($_POST['codeCount']) || empty($_POST['codeCount'])) {
                $this->error("邀请码总数不能为空");
            }
            
            if(!isset($_POST['smsCount']) || empty($_POST['smsCount'])) {
                $this->error("短信数不能为空");
            }
            $codeCount = $_POST['codeCount'];
            $smsCount = $_POST['smsCount'];
            
            $generator = new InviteCodeGenerator();
            $ok = $generator->generate($codeCount, $smsCount, 2, '', C('ADMIN_USER_ID'));
            if($ok){
                $this->success("成功生成 ".$codeCount." 个邀请码",U('Admin/InviteCode/index'));
            }else{
                Log::record("Generate invitecode getDBError:".$dbErr." getError:".$lastError, Log::ERR);
                $this->error('生成邀请码失败。请查看log!');
            }
        }else {
            $this->display();
        }
    }
    // 生成充值码 － 套餐为基本单位； 该接口主要提供给代理商用
    public function generate_package() {
        $isAdmin = 0;
        if(session(C('ADMIN_AUTH_KEY')) == true) {
            $isAdmin = 1;
        }

        $beUserId = session(C('USER_AUTH_KEY'));
        $beUser = M('User')->where(array('id'=>$beUserId, 'status'=>1))->find();
        
        $agentPackages = C('AGENT_PACKAGES');
        $packagePrices = unserialize($beUser['package_price']);
        foreach ($agentPackages as &$package) {
            if(array_key_exists($package['id'], $packagePrices)) {
                $package['price_month'] = $packagePrices[$package['id']];
            }
        }
        
        if(IS_POST) {
            if(!isset($_POST['packageId']) || !isset($_POST['packageDuration'])){
                $this->error('非法参数请求！');
            }
            $packageId = intval($_POST['packageId']);
            $packageDuration = intval($_POST['packageDuration']); //月数, 当类型type＝2时候，该字段代表短信条数。
            
            $inviteCodeCount = 1; 
            if(!array_key_exists($packageId,$agentPackages)) {
                $this->error('套餐不存在！');
            }
            
            $priceUnit = $agentPackages[$packageId]['price_month'];
            $charge = $priceUnit * $packageDuration;
            
            $balance = $beUser['balance'];
            
            // 检查余额是否足够
            if($charge <= 0 || $balance < 0 ||  $charge > $balance) {
                $this->error('余额不足！请联系客服进行充值！');
            }
            
            // 添加审计记录：记录用户生成充值码的历史记录
            $purchase = array();
            $purchase['agent'] = $beUserId;
            $purchase['package'] = $packageId;
            $purchase['duration'] = $packageDuration;
            $purchase['count'] = $inviteCodeCount;
            $purchase['charge'] = $charge;
            $purchase['pre_balance'] = $balance;
            $purchase['start_time'] = time();
            $purchaseId = M('audit_agent_purchase')->add($purchase);
            if($purchaseId === FALSE) {
                $this->error('服务器忙！请刷新重试！');
            }
            
            
            // 生成充值码，状态为删除
            $inviteCodeGenerator = new InviteCodeGenerator();
            //需根据套餐类型设置payload参数：功能套餐设置成天数，短信包设置为条数
            $package_type = $agentPackages[$packageId]['type'];
            switch($package_type) {
                case 1:
                    $payload = $packageDuration * 31;
                    break;
                case 2:
                    $payload = $packageDuration;
                    break;
            }
            $batchId =  $inviteCodeGenerator->generate($inviteCodeCount, $payload, $package_type, $agentPackages[$packageId]['function_groups'], $beUserId, -1, $packageId);
            if($batchId === FALSE) {
                $this->error('生成充值码失败！！请联系客服！');
            }
            $purchase = array();
            $purchase['id'] = $purchaseId;
            $purchase['invitecode_batch'] = $batchId;
            M('audit_agent_purchase')->save($purchase);
            
            // 修改代理商的余额信息
            $confirmBeUser = M('User')->where(array('id'=>$beUserId, 'status'=>1))->field('balance')->find();
            $remain = $confirmBeUser['balance'] - $charge;
            if($remain < 0) {
                $this->error('余额不足！');
            }
           
            Log::record("start charge codeBatchId:".$batchId." packageId:".$packageId." duration:".$packageDuration."m cnt:".$inviteCodeCount
                    ." preBalance:".$balance." charge:".$charge, Log::INFO);
            Log::save();
            
            $ok = M('User')->where(array('id'=>$beUserId, 'status'=>1))->setField('balance', $remain);
         
            if($ok === FALSE) {
                
                Log::record("fail charge codeBatchId:".$batchId, Log::ERR);
                Log::save();
                $this->error('生成充值码失败！！！请刷新重试！');
                
            }
            Log::record("succeed charge codeBatchId:".$batchId, Log::INFO);
            Log::save();
            
            $purchase = array();
            $purchase['id'] = $purchaseId;
            $purchase['post_balance'] = $remain;
            M('audit_agent_purchase')->save($purchase);
            // 设置充值码的状态为可用
            // 如果是代理商生成的码，需要设置管理者为代理商
            $updateData = array();
            $updateData['status'] = 0;
            if($beUserId != C('ADMIN_USER_ID')) {
                $updateData['manager'] = $beUserId;
                $updateData['assign_manager_time'] = $batchId;
                $updateData['assign_manager_price'] = $priceUnit * $packageDuration;
                $updateData['manager_role'] = $beUser['role'];
            }
            
            $ok = M('invitecode')->where(array('generator'=>$beUserId, 'create_time'=>$batchId))->save($updateData);
            if($ok === FALSE) {
                Log::record("fail enable codeBatchId:".$batchId, Log::ERR);
                Log::save();
                $this->error('生成充值码失败了！请重试！');
            }
            Log::record("succeed codeBatchId:".$batchId, Log::INFO);
            Log::save();
            
            $purchase = array();
            $purchase['id'] = $purchaseId;
            $purchase['end_time'] = time();
            M('audit_agent_purchase')->save($purchase);
            
            $this->success('生成充值码成功！', U('InviteCode/index'));
        }else {
            $this->assign('agentPackages', $agentPackages);
            $this->assign('beUser', $beUser);
            $this->display();
        }
    }
    
    
    // 生成充值码 － 功能组为基本单位； 该接口仅提供给admin使用，对代理商不开放；
    public function generate(){
        if(!isset($_POST['dosubmit'])) {
            $condition['status'] = 1;
            $functionGroups = M('function_group')->where($condition)->order('sort asc, id asc')->select();
            $this->assign('funcGroups',$functionGroups);
            $this->display();
            
        }else {
            
            $func_groups = join(',', $_POST['func_group']);
            if(strlen($func_groups) <= 0){
                $this->error('功能组不能为空');
            }
            
            if(!isset($_POST['codeCount']) || empty($_POST['codeCount'])) {
                $this->error("邀请码总数不能为空");
            }
            
            if(!isset($_POST['codeDuration']) || empty($_POST['codeDuration'])) {
                $this->error("有效期不能为空");
            }
            $codeCount = $_POST['codeCount'];
            $codeDuration = $_POST['codeDuration'];
            
            $generator = new InviteCodeGenerator();
            $ok = $generator->generate($codeCount, $codeDuration, 1, $func_groups, C('ADMIN_USER_ID'));
            if($ok){
                $this->success("成功生成 ".$codeCount." 个邀请码",U('Admin/InviteCode/index'));
            }else{
                Log::record("Generate invitecode getDBError:".$dbErr." getError:".$lastError, Log::ERR);
                $this->error('生成邀请码失败。请查看log!');
            }
        }
    }
    
    public function funcgroup($userid) {
        // build current active function groups list
        $condition['status'] = 1;
        $activeFGs = M('function_group')->where($condition)->field('id,name')->select();
        $activeFGId2NameMap = array();
        foreach($activeFGs as $fg) {
            $activeFGId2NameMap[$fg['id']] = $fg['name']; 
        }
        
        // load invite code
        unset($condition);
        $condition['code'] = $code;
        $tb_invitecode = M('invitecode')->where($condition)->find();
        
        // common function groups for current invite code with active func groups
        $fgIdList = explode(",", $tb_invitecode['function_group_list']);
        $commonFunctionGroups = array();
        foreach($fgIdList as $fgId) {
            if(isset($activeFGId2NameMap[$fgId])) {
                $commonFunctionGroups[$fgId] = $activeFGId2NameMap[$fgId];
            }
        }
        
           
        $currTime = time();
        $durationInSecs = ($tb_invitecode['duration'] + 1)* 24 * 3600;
       
        
        // load  function group and expiration time of current user
        unset($condition);
        $condition['user_id'] = $userid;
        $userFuncGroupList = M('user_func_group')->where($condition)->select();
        
        
        $funcGroupsDataForUI = array();
        foreach($userFuncGroupList as $userFuncGroup) {
            // 邀请码必须active，而且被邀请码包含。此处我们忽略被商户用户但没有被这个邀请码包含的项。
            if(isset($commonFunctionGroups[$userFuncGroup['group_id']])) {
                $tmp = array();
                $tmp['id'] = $userFuncGroup['group_id'];
                $tmp['name'] = $commonFunctionGroups[$userFuncGroup['group_id']];
                // 对于该商户的每个功能组，需要考虑现有的套餐是否到期。如果未到期，需要叠加。
                if(!empty($userFuncGroup['start_time']) && !empty($userFuncGroup['expire_time']) && $userFuncGroup['expire_time'] > $currTime) {
                    // 该功能未过期
                    $tmp['start_time'] =date('Y-m-d H:i:s', $userFuncGroup['start_time']);
                    $tmp['expire_time'] = date('Y-m-d H:i:s', $userFuncGroup['expire_time'] + $durationInSecs);
                }else{
                    $tmp['start_time'] = date('Y-m-d H:i:s', $currTime);
                    $tmp['expire_time'] = date('Y-m-d H:i:s',$currTime + $durationInSecs);
                }
                $funcGroupsDataForUI[$userFuncGroup['group_id']] = $tmp;
            }
        }
        $this->ajaxReturn($funcGroupsDataForUI, 'JSON');
    }
    
}
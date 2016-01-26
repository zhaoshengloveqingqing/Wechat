<?php
class UserinfoAction extends BaseAction
{
    protected function _initialize() {
        parent::_initialize();
        $agent      = $_SERVER['HTTP_USER_AGENT']; 
        if (!strpos($agent,"MicroMessenger")) 
        {
            echo '此功能只能在微信浏览器中使用';
            exit;
        }
    }

    public function infoadd()
    {
        $wecha_id = $this->_get('wecha_id');
        $token = $this->_get('token');
        $where['wecha_id']   = $wecha_id;
        $where['token']      = $token;
        $where['status']     = 1;
        if(empty($wecha_id)||empty($token))
        {
            echo '非法操作!';
            exit();
        }

        $wecha_user_db = M('wecha_user');
        $member_card_create_db = M('member_card_create');
        $member_card_set_db = M('member_card_set');
        $userinfo_db = M('userinfo');
        $member_card_set = $member_card_set_db->where(array('token'=>$token))->find();

        if(!empty($member_card_set['default_show_cols'])) {
            $this->assign('default_show_cols', unserialize($member_card_set['default_show_cols']));
        }
        $text_cols = unserialize($member_card_set['text_cols']);
        if(!empty($text_cols)){
            $this->assign('text_cols', $text_cols);
        }
        $select_cols = unserialize($member_card_set['select_cols']);
        if(!empty($select_cols)) {
            $this->assign('select_cols', $select_cols);
        }
        $card_off = $member_card_set['card_off'];
        $cardInfomation = $member_card_create_db->where($where)->find();
        //编辑时读取数据
        if($cardInfomation)//已领卡则写入会员信息
        {
            $this->assign('cardnum',$cardInfomation['number']);
            $wecha_user = $wecha_user_db->where($where)->find();
            $extuserinfo = $userinfo_db -> where(array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1))->find();
            if($wecha_user)
            {
                $extInfoData = unserialize($extuserinfo['memberinfo']);
                $addressInfoData = unserialize($wecha_user['address']);
                $this->assign('addressData',$addressInfoData);
                $this->assign('extDatas',$extInfoData);
                $this->assign('userdata',$wecha_user);
            }
        }
        $this->assign('card_off',$card_off && !$cardInfomation);//若已领卡则card_off始终为0
        $this->display();
    }

    public function update_user_info(){
        $wecha_id = $this->_get('wecha_id');
        $token = $this->_get('token');
        if(empty($wecha_id)||empty($token))
        {
            echo '非法操作!';
            exit();
        }
        $where['wecha_id']   = $wecha_id;
        $where['token']      = $token;
        $where['status']     = 1;
        $data = $this->procUserInfoData($_POST,$wecha_id,$token);//处理post过来的数据
        //$member_db = M('Member');
        $wecha_user_db = M('wecha_user');
        $member_card_create_db = M('member_card_create');
        $userinfo_db = M('userinfo');
        $cardleft = $member_card_create_db->where($where)->find();
        $defaultInfoChanged = $_POST['defaultinfochanged'];
        $extinfochanged = $_POST['extinfochanged']; 
        if(!empty($cardleft))//领过卡
        {
            $userCheck = $wecha_user_db->where($where)->find();
            $defaultRes = false;
            $extRes = false;
            if(!empty($userCheck))//在此商家填过信息，只是更新
            {
                if($defaultInfoChanged)
                {
                    $defaultRes = $wecha_user_db->where($where)->save($data['updata']);
                }
                if($extinfochanged)
                {
                    $extRes = $userinfo_db->where($where)->save($data['extinfodata']);
                }
            } 
            Log::save();
            $backres = ($defaultInfoChanged ? $defaultRes : true) && ($extinfochanged ? $extRes : true); 
            if ($backres) {
                echo'{"success":1,"msg":"个人信息更新成功！"}';
            }
            else {
                echo'{"success":0,"msg":"个人信息更新失败，请稍后再试！"}';
            }
            Log::save();
            exit();
        }
    }

    public function member_regist(){
        $wecha_id = $this->_get('wecha_id');
        $token = $this->_get('token');
        if(empty($wecha_id)||empty($token))
        {
            echo '非法操作!';
            exit();
        }
        $where['wecha_id']   = $wecha_id;
        $where['token']      = $token;
        $where['status']     = 1;
        $data = $this->procUserInfoData($_POST,$wecha_id,$token);//处理post过来的数据

        $wecha_user_db = M('wecha_user');
        $member_card_create_db = M('member_card_create');
        $member_card_set_db=M('member_card_set');
        $userinfo_db = M('userinfo');

        $cardleft = $member_card_create_db->where($where)->find();//判断是否领过了
        if($cardleft){
            echo '{"success":0,"msg":"您已领过会员卡了！"}';
            exit();
        }
        $card_num = $this->getCardNum($token);
        if ($card_num)
        {
            $card['number'] = $card_num;
            $card['wecha_id'] = $wecha_id;
            $card['token'] = $token;
            $card['groupid'] = C('MEMBER_GROUP')[0]['groupid'];//会员默认等级为最低等级，其groupid一直为配置文件中的最低级
            $card['getcardtime'] = time();

            //领卡，占位
            $card_up = $member_card_create_db->add($card);
            //写入会员卡信息，使用初始信息 积分0，等级为普通会员
            $userinfoBack = $userinfo_db->where($where)->find();
            if (empty($userinfoBack)) 
            {
                $tmpData = array('wecha_id'=>$wecha_id, 'token'=>$token);
                $infoback = $userinfo_db->data(array_merge($tmpData,$data['extinfodata']))->add(); 
            }
                
            // 保存个人信息，和会员卡数据解耦合
            $wecha_user = $wecha_user_db->where($where)->find();
            if ($wecha_user) 
            {
                $back = $wecha_user_db->where($where)->save($data['updata']); 
            } 
            else 
            {
                $back = $wecha_user_db->data($data['updata'])->add(); 
            }

            Log::save();
            if($infoback && $back){
                echo '{"success":1,"msg":"成功领取会员卡！"}';exit;
            }
            else{
                echo '{"success":0,"msg":"会员卡领取失败，请稍后再试！"}';
            }
        }
        else
        {
            //商家没有了会员卡
            Log::save();
            echo '{"success":0,"msg":"抱歉，商家已没有空闲的会员卡了！"}';exit;
        }  
    }

    /**
    *处理post过来的用户信息返回可以直接写入的信息
    **/
    protected function procUserInfoData($postData,$wecha_id,$token){
        //商家扩展的数据
        $extInfo = array('select_cols' =>$_POST['select_cols'] ,
            'text_cols'=>$_POST['text_cols']);
        $extinfodata['memberinfo'] = serialize($extInfo);
        //地址
        $address = array('addr_detail'=>$_POST['addr_detail'],
                            'addr_prov'=>$_POST['addr_prov'],
                            'addr_city'=>$_POST['addr_city'],
                            'addr_area'=>$_POST['addr_area']);
        //去除扩展数据和地址数据
        $updata = array_diff($_POST,$extInfo,$address);
        $updata['token'] = $token;
        $updata['wecha_id'] = $wecha_id;
        $updata['address'] = serialize($address); 
        return array('updata'=>$updata,
                     'extinfodata'=>$extinfodata,
                        );
    }

    /**
    *获取一个卡号，并将商户的可用会员卡减一，若无可用会员卡，或者其他原因导致失败则返回0
    **/
    protected function getCardNum($token){
        $ret = 0;
        $member_card_set_db=M('member_card_set');
        $card_set = $member_card_set_db->field('id,card_off,card_num_set,card_amount_limit,card_amount,card_num_now')
                                        ->where(array('token'=>$token))
                                        ->find();
        if( empty($card_set)){//如果没有card_set数据，则表示未完成开卡设置
            echo '{"success":0,"msg":"领卡失败！"}';
            exit();
        }
        $limit = $card_set['card_amount_limit'] ? $card_set['card_amount_limit'] : C('MEMBER_AMOUNT_LIMIT');
        if($card_off['card_off']){//确定是否暂停发卡
            echo '{"success":0,"msg":"您来晚了，商家已停止发放会员卡了！"}';
            exit();
        }
        $hasCard = $card_set && !$card_set['card_off'] && $card_set['card_amount'] < $limit;
        if($hasCard){
            $num_set = unserialize($card_set['card_num_set']);
            $infix = $card_set['card_num_now'] ? $card_set['card_num_now'] + 1 : C('CARD_NUM_INFIX');
            $card_num = $num_set['prefix'] . $infix . $num_set['suffix'];
            $card_set['card_num_now'] = $infix;
            $card_set['card_amount'] = $card_set['card_amount'] + 1;
            $card_set['card_num_set'] = serialize($num_set);
            $back = $member_card_set_db->where(array('id'=>$card_set['id']))->save($card_set);
            $ret = $back ? $card_num : 0;

        }
        return $ret;
    }

    
    public function bind() {
        $this->display();
    }
    
    public function getcode() {
        $token          = $this->_get('token');
        $wecha_id       = $this->_get('wecha_id');
        $tel            = $this->_post('tel');
        $cardnum        = $this->_post('cardnum');

        $where = array('token'=>$token, 'tel'=>$tel, 'cardnum'=>$cardnum);
        //根据电话号码验证会员是否被导入
        $userinfo = M("physical_member")->where($where)->find();
        if(!$userinfo){
            echo '{"success":0,"msg":"没有找到您的会员卡信息，请联系商家导入。"}';
            exit();
        }
        elseif($userinfo['binded']){
            echo '{"success":0,"msg":"此手机号已被绑定."}';
            exit();
        }elseif ($userinfo['cardnum'] != $cardnum) {
            echo '{"success":0,"msg":"您输入的手机号与卡号不匹配,请重新输入。"}';
            exit();
        }
        
        $Cache = Cache::getInstance('File',array('expire'=>'310'));
        $scode = $Cache->get($token.$tel);
        if (!empty($scode)) {
            // 避免多次发送
            echo '{"success":0,"msg":"验证码已发送，请稍侯。"}';
            exit;
        }
        $code = $this->rand_number(100000, 999999);
        $Cache->set($token.$tel, $code);
        Log::record("Send code : ".$code." at ". date("Y-m-d H:i:s")."\r\n", Log::DEBUG);       
        
        include(LIB_PATH.'Action/SmsSender.class.php');
        $smsSender = new SmsSender();

        $binding_tmpl = C('card_binding');
        $smsContent = '';
        if (isset($binding_tmpl)) 
        {
            $smsContent = str_replace("#merchant#", '领众科技',           $binding_tmpl);
            $smsContent = str_replace("#code#", $code,  $smsContent);
        }

        if ($smsContent != '') 
        {
            $re = $smsSender->notify($this->_get('token'),"huiyuan", $smsContent, $tel);
            if ($re == 0) 
            {
                $Cache->set($token.$tel, $code);
                echo '{"success":1,"msg":"验证码已发送，请输入收到的短信验证码!"}';
            }
            else
            {
                echo '{"success":0,"msg":"验证码发送失败，请稍侯再试。"}';
            }
        }
        else
        {
            echo '{"success":0,"msg":"验证码发送失败，请稍侯再试。"}';
        }
    }
    
    public function verify() {
        $wecha_id   = $this->_get('wecha_id');
        $token      = $this->_get('token'); 
        $tel        = $this->_post('tel');
        $cardnum    = $this->_post('card');
        $code       = $this->_post('code','trim'); 

        $Cache = Cache::getInstance('File',array('expire'=>'310'));
        $scode = $Cache->get($token.$tel);
        if (!empty($code) && $scode == $code ) {
            Log::record("Verify code : ".$code." at ". date("Y-m-d H:i:s")."\r\n", Log::DEBUG);       
            
            $wecha_user_db = M('wecha_user');
            $member_card_create_db = M('member_card_create');
            $member_card_set_db=M('member_card_set');

            // 从physical_member表中取出导入的数据，并判断卡号和手机号是否一致
            $wecha_user = M('physical_member')->where(array('token'=>$token,'tel'=>$tel,'binded'=>0))->find();
            if(!$wecha_user){
                echo '{"success":0,"msg":"会员卡已被绑定，或者商家未导入您的数据！"}';
                Log::save();
                exit();
            }

            $wx_card_num = $this->getCardNum($token);
            $ret = $wx_card_num;
            //$card_off = $member_card_set_db->field('card_off')->where(array('token'=>$token))->find();//确定是否暂停发卡 
            if($ret)//领卡
            {
                $basedata['token']          = $token;
                $basedata['groupid']        = 1;
                $basedata['wecha_id']       = $wecha_id;
                $basedata['getcardtime']    = time();
                $basedata['number']         = $wx_card_num;
                $ret = $member_card_create_db->add($basedata);
            } 
            if($ret){
                //写入会员卡信息，使用初始信息 积分0，等级为普通会员 
                $userinfo_db = M('userinfo');
                $tmpData = array('wecha_id'=>$wecha_id, 'token'=>$token,'source'=>'offline');
                $ret = $userinfo_db->data($tmpData)->add();  
            } 
            if($ret){
                $pid = $wecha_user['id'];
                unset($wecha_user['id']);
                $wecha_user['address'] = array('addr_detail'=>$wecha_user['address'],
                                                'addr_prov'=>$wecha_user['province'],
                                                'addr_city'=>$wecha_user['city'],
                                                'addr_area'=>$wecha_user['area']);
                $wecha_user['address'] = serialize($wecha_user['address']);
                $wecha_user['truename'] = $wecha_user['name'];
                $wecha_user['wecha_id'] = $wecha_id; 
                $ret = $wecha_user_db->data($wecha_user)->add(); 
            }
            Log::record('生成的卡号为：'.$wx_card_num.'\n\r'.'绑定的实体会员id：'.$wecha_user['id']);
            if($ret){
                M('physical_member')->where(array('id'=>$pid))->setfield('binded',1);
                echo '{"success":1,"msg":"成功绑定会员卡！"}';
                Log::save();
                exit;
            }else{
                echo'{"success":0,"msg":"会员卡绑定失败，请稍后再试！"}';
                Log::save();
                exit;
            }
            
        } else {
            echo'{"success":0,"msg":"验证码错误请重试！"}';
        }
    }
    
    function rand_number ($min, $max) {
        return sprintf("%0".strlen($max)."d", mt_rand($min,$max));
    }
} // end class UserinfoAction

?>

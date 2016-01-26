<?php
class MemberAction extends UserAction{

    protected function _initialize(){
        parent::_initialize();
        parent::checkOpenedFunction('huiyuanka');
		import('ORG.ExcelToArrary');
    }
    /**
    *会员消费管理
    **/
    public function index(){
        /*$sql=M('Member');
        $data['token']=$this->_get('token');
        $data['uid']=session('uid');
        $member=$sql->field('homepic')->where($data)->find();
        $this->assign('member',$member);*/
        // 消费记录，且未删除
        // member_card_sign as point | member_card_crate as card | wecha_user as member
        $where = " WHERE ( point.token = '$this->token' ) 
                            AND ( point.score_type = 2 ) 
                            AND ( point.delete = 0 ) 
                            AND ( member.token = '$this->token')
                            AND ( card.status = 1 )
                            AND ( card.token = '$this->token')";
        if (IS_POST)
        {     
            $key = $this->_post('searchkey');
            if ($key)
            {
                $where .= " AND (card.number like '%$key%')";
            }
        } 

        $sql = "SELECT member.id as wxid, 
                        point.id as id,
                        point.wecha_id as uid,
                        card.number as cardnum,
                        point.expense as expense,
                        point.sell_expense as sell_expense,
                        point.sign_time,
                        point.remark  
                FROM `tp_member_card_sign` as point JOIN  tp_member_card_create as card on (point.wecha_id=card.wecha_id and point.token = card.token)
                LEFT JOIN tp_wecha_user as member on (point.wecha_id=member.wecha_id and point.token = member.token)";
        $sql .= $where;
        $sql .= ' ORDER BY sign_time desc ';
        $Model = new Model();
        $points = $Model->query($sql); 
        $date = date("Y-m-d");
        $this->assign('points',$points);
        $this->assign('date',$date);
        
        $this->display();
    }
    /**
    *录入积分记录
    **/
    public function addscorecost(){
        $token = session("token");

            // 检查卡号
            $cardnum = $this->_post('number');
            $card = M('Member_card_create')->where(array('token'=>$token,'number'=>$cardnum,'status'=>1))->find();
            if (!$card) {
                $this->error('卡号不存在');
            }
            if (!$card['wecha_id']) {
                $this->error('该卡号未被领用，无法消费');
            }

            $wecha_id = $card['wecha_id'];
            $userinfo = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1))->find();
            
            $add_expend     = floatval($this->_post('cost_score'));
            $add_expend_time= $this->_post('cost_date');
            $remark         = $this->_post('comment');
            if($add_expend<=0){
                $this->error('兑换积分必须大于0');exit;
            }
            if($userinfo['total_score']<$add_expend){
                $this->error('积分余额不足');exit;    
            }

            $data['token']    = $token;
            $data['wecha_id'] = $wecha_id;
            $data['sign_time'] = strtotime($add_expend_time);
            $data['score_type'] = 3;
            $data['delete'] = 0;
            $data['expense']  = intval($add_expend);
            $data['remark'] = $remark; //备注
            $back = M('Member_card_sign')->data($data)->add();

            //总记录
            $da['total_score']   = $userinfo['total_score'] -  $data['expense'];
            $da['spend_score']  = $userinfo['spend_score'] + $data['expense'];

            $back2 = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1))->save($da);
            if($back && $back2){
                $this->success('操作成功');
            }else{
                $this->error('服务器繁忙，请稍候再试');
            } 
    }
    /**
    *积分兑换管理
    **/
    public function scorecost(){
        /*$sql=M('Member');
        $data['token']=$this->_get('token');
        $data['uid']=session('uid');
        $member=$sql->field('homepic')->where($data)->find();
        $this->assign('member',$member);*/
        // 消费记录，且未删除
		//member_card_sign` as point | member_card_create as card | userinfo info | wecha_user as member
        $where = " WHERE ( point.token = '$this->token' ) "
                    ."AND ( point.score_type = 3 ) "
                    ."AND ( point.delete = 0 ) "
                    ."AND ( card.token = '$this->token')"
					."AND ( card.status = 1 )"
                    ."AND ( info.token = '$this->token')"
					."AND ( info.status = 1 )"
                    ."AND ( member.token = '$this->token')";
        if (IS_POST)
        {     
            $key = $this->_post('keyword');
            if ($key)
            {
                $where .= " AND (card.number like '%$key%')";
            }
        } 

        $sql = "SELECT member.id as wxid, info.total_score as total, point.id as id,point.wecha_id as uid,card.number as cardnum,point.expense as expense,point.sell_expense as sell_expense,point.sign_time, point.remark  
                FROM `tp_member_card_sign` as point 
                JOIN  tp_member_card_create as card on point.wecha_id=card.wecha_id 
                JOIN tp_userinfo info on card.wecha_id=info.wecha_id 
                LEFT JOIN tp_wecha_user as member on point.wecha_id=member.wecha_id";
        $sql .= $where;
        $sql .= ' ORDER BY sign_time desc ';
        $Model = new Model();
        $list = $Model->query($sql);
              
        $this->assign('list',$list);
        
        $this->display();
    }

    /**
    *充值操作
    **/
    public function recharge(){
        //dump($_POST);exit();
        $adminid = session('uid');
        $token = $this->token; 
        if(empty($adminid) || empty($token)){
            Log::record('非法操作');
            $this->error('非法操作！'); 
        }
        $card_num = $_POST['card_num'];
        $where['token'] = $token; 
        $where['number'] = $card_num; 
        $where['status'] = 1; 
        $cardinfo = M('Member_card_create') -> where($where) ->find();
        $wecha_id = $cardinfo['wecha_id']; 
        if(!$cardinfo || empty($cardinfo['wecha_id'])) { 
            Log::record('充值失败：'.M('Member_card_create')->getlastSql());
            $this->error('要充值的卡号不存在！'); 
        }
        $data['wecha_id'] = $cardinfo['wecha_id'];
        $data['type'] = 0;
        $data['logon_user_id'] = $adminid;
        $data['logon_ip'] = getenv('REMOTE_ADDR');
        $data['token'] = $token;
        $model = new Model();
        $model->startTrans();//事务开始
        $isSuccess = $model->autoCheckToken($_POST);//表单令牌验证，防止重复提交
        unset($_POST[C('TOKEN_NAME')]);//删除表单令牌防止table方法save失败
        $data = array_merge($data,$_POST);
        if($isSuccess){
            $isSuccess = $model->table('tp_member_charge')->add($data); 
        }
        if($isSuccess){
            $where = array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1);
            $userinfo = M('userinfo') -> where($where)->find();
            $userinfo['total_money'] += $data['amount'];
            $isSuccess = $model -> table('tp_userinfo')
                                -> where($where)
                                -> save($userinfo);
        }
        if($isSuccess){
            $model->commit();//成功则事务提交
            $this->success("充值成功！");
        }
        else{
            $model->rollback();//失败则事务回滚
            Log::save();
            $this->error('充值失败！请稍后再试！');  
        }
    }

    /**
    *充值管理
    **/
    public function chargelist(){
        $member_charges = M('member_charge');
        $whereCon = array('tp_member_charge.token'=>$this->token,
                          'tp_member_charge.isdelete'=>0 , 
                          'tp_member_charge.type'=>0 );
        $card_num = $_POST['card'] ? $_POST['card'] : $_GET['card'];
        if($card_num){
            $whereCon['tp_member_charge.card_num'] = $card_num;
            $this->assign('card_num',$card_num);
        }
        $count = $member_charges->where($whereCon)->count();
        $page   = new Page($count,15);
        Log::save();
        $whereCon ['tp_member_charge.token'] = $this->token;
        $whereCon ['tp_wecha_user.token'] = $this->token;
        $whereCon ['tp_member_card_create.token'] = $this->token;
        $whereCon ['tp_member_card_create.status'] = 1;
        $chargeinfos = $member_charges->join('tp_users on tp_member_charge.logon_user_id = tp_users.id')
                                      ->join('tp_wecha_user on (tp_member_charge.wecha_id = tp_wecha_user.wecha_id and tp_member_charge.token = tp_wecha_user.token)')
                                      ->join('tp_member_card_create on tp_member_charge.wecha_id = tp_member_card_create.wecha_id')
                                      ->where($whereCon)
                                      ->field('tp_member_charge.*,tp_users.username as username,tp_wecha_user.id as wxid')
                                      ->order('record_id desc')
                                      ->limit($page->firstRow.','.$page->listRows)
                                      ->select();
        $this->assign('historys',$chargeinfos);
        $this->assign('page',$page->show());
        $this->display();
    }

    /**
    *编辑会员信息
    **/
    public function editmemberinfo(){
        $wxid     = $this->_get("wid");
    	if(isset($wxid)&&!IS_POST)
    	{
	    	$where = array( 'tp_userinfo.token' => $this->token,
							'tp_userinfo.status' => 1,
        	    			'tp_wecha_user.token' => $this->token, 
        	    			'tp_member_card_create.token' => $this->token,
							'tp_member_card_create.status' => 1,
                            'tp_wecha_user.id'=>$wxid);
                $model = new Model('userinfo');
                //'tp_wecha_user as wu on wu.wecha_id = member.wecha_id left join tp_member_card_create as card on card.wecha_id = member.wecha_id ';
                $res = $model->join('tp_wecha_user on tp_wecha_user.wecha_id = tp_userinfo.wecha_id')
                             ->join('tp_member_card_create on tp_member_card_create.wecha_id = tp_userinfo.wecha_id')
                             ->where($where)
                             ->field( ' tp_userinfo.token,
                                        tp_userinfo.wecha_id,
                                        tp_userinfo.info,
                                        tp_userinfo.total_score,
                                        tp_userinfo.spend_score,
                                        tp_userinfo.sign_score,
                                        tp_userinfo.expend_score, 
                                        tp_userinfo.total_money,
                                        tp_userinfo.spend_money, 
                                        tp_userinfo.memberinfo as extinfo, 
                                        tp_wecha_user.*,
                                        tp_member_card_create.id as Member_card_create_id,
                                        tp_member_card_create.number,
                                        tp_member_card_create.groupid,
                                        tp_member_card_create.getcardtime,
                                        tp_wecha_user.userinfo')
                             ->find();
                $member_card_set = M('member_card_set')
                                ->where( array('token' =>$this->token ))
                                ->field('default_show_cols,text_cols,select_cols')
                                ->find();
                $extInfoData = unserialize($res['extinfo']);
                $addressInfoData = unserialize($res['address']);
                $this->assign('addressData',$addressInfoData);
                $this->assign('extDatas',$extInfoData);

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
                $class_con = array('token'=>$this->token,'status'=>1);
                $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
                if(empty($class_info)){
                    $class_info = C('MEMBER_GROUP');
                }
                $this->assign('class_info',$class_info);
                $this->assign('memberSet',$memberSet);
                $this->assign('data',$res);
                //dump($res);
                $this->display();
    	}
        if(IS_POST){
            $wechauserinfo = M('wecha_user') -> where(array('token'=>$this->token, 'id' => $wxid)) ->find();
            if(!$wechauserinfo){
                $this->error("未找到要要修改的会员！");
                exit();
            }
            $wecha_id = $wechauserinfo['wecha_id'];
            $defaultinfochanged=$_POST['defaultinfochanged']||$_POST['oldbirthday']!=$_POST['birthday'];
            $classchanged=$_POST['oldgroupid']!=$_POST['groupid'];
            $extinfochanged = $_POST['extinfochanged']; 
            $upUserinfoSuccess=false;
            $upMemberClassSuccess=false;
            $upExtInfoSuccess = false;
            $extinfo = array('select_cols' =>$_POST['select_cols'] ,
                                'text_cols'=>$_POST['text_cols']);
            $address = array('addr_detail'=>$_POST['addr_detail'],
                                'addr_prov'=>$_POST['addr_prov'],
                                'addr_city'=>$_POST['addr_city'],
                                'addr_area'=>$_POST['addr_area']); 
            //判断userinfo是否有改动，如果没有改动，则save方法返回0，
            if($defaultinfochanged){ 
                $userupdata = array_diff($_POST,$extinfo,$address);
                $userupdata['token'] = $this->token;
                $userupdata['wecha_id'] = $wecha_id;
                $userupdata['address'] = serialize($address);  
                //exit();
                $m = M('wecha_user');
                $upUserinfoSuccess=$m->where(array('id'=>$_POST['id'],'token'=>$this->token))->save($userupdata); 
            }

            if($extinfochanged){
                $extinfodata['memberinfo'] = serialize($extinfo); 
                $upExtInfoSuccess = M('userinfo')->where(array('token' => $this->token , 'wecha_id' => $wecha_id , 'status'=>1))->save($extinfodata);
            }
            //判断member_card_create的groupid是否改动，若改动则提交 
            if($classchanged){
                $updata= array('id' =>$_POST['Member_card_create_id'] ,'groupid'=>$_POST['groupid'] );
                $upMemberClassSuccess= M('Member_card_create')->where(array('id' =>$updata['id'],'token'=>$this->token,'status'=>1))->save($updata); 
            }
            $succ=($infochanged ? $upUserinfoSuccess : true) && 
                ($classchanged ? $upMemberClassSuccess : true) && 
                ($extinfochanged ? $upExtInfoSuccess : true);
            if (!$succ) {
                $this->error('服务器繁忙，请稍候再试');
            } else {
                $this->success('操作成功');
            } 
        }
    }
    
    /**
    *实体会员管理
    **/
    public function physicalmemberadmin()
    {
        $model = new Model();
        $where = array('token' => $this->token );
        $sum = $model->table('tp_physical_member')->where($where)->count();
        //dump()
        $Page = new Page($sum,15);
        $show = $Page->show();
        $limit = $Page->firstRow.','.$Page->listRows;

        $table = $model->table('tp_physical_member')->where($where)->limit($limit)->select();
        $this->assign('page',$show);
        $this->assign('table',$table);
        $this->display();
    }
    
    /**
    *设置会员注册时需要填写的信息
    **/
    public function setmemberinfo()
    {
        $m = M('member_card_set');
        if(IS_POST)
        {            
            // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $updata['default_show_cols'] =serialize($default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));   
                }
            }
            $updata['text_cols'] = serialize($text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                     array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                }
            }
            $updata['select_cols'] = serialize($select_cols);
            $back=$m->where(array('token'=>$this->token))->save($updata);
            if($back) {
                    $this->success('保存成功！');
            }else {
                $this->error('服务器繁忙，请稍候再试');
            }
        }
        $data = $m->where(array('token'=>$this->token))->find(); 
        if(!empty($data['default_show_cols'])) {
            $this->assign('default_show_cols', unserialize($data['default_show_cols']));
        }
        $text_cols = unserialize($data['text_cols']);
        if(empty($text_cols)){
            $this->assign('text_cols', array(array()));
        }else{
            $this->assign('text_cols', $text_cols);
        }
        $select_cols = unserialize($data['select_cols']);
        if(empty($select_cols)) {
            $this->assign('select_cols', array(array()));
        }else{
            $this->assign('select_cols', $select_cols);
        } 
        $this->display();
    }

    /*
    public function add(){
        $sql=M('Member');
        $data['token']=session('token');
        $data['uid']=session('uid');
        $member=$sql->field('id')->where($data)->find();
        $pic['homepic']=$this->_post('homepic');
        if($member!=false){
            $back=$sql->where($data)->save($pic);
            if($back){
                $this->success('更新成功');
            }else{
                $this->error('服务器繁忙，请稍后再试1');
            }
        }else{
            $data['homepic']=$pic['homepic'];
            $back=$sql->add($data);
            if($back){
                $this->success('更新成功');
            }else{
                $this->error('服务器繁忙，请稍后再试');
            }
        }
    }*/
    
    /**
    *删除消费记录
    **/
    public function del(){
        $data['id']=$this->_get('id');
        $data['token']=session('token');
		
		$element = M('Member_card_sign')->where($data)->find();
        $re = M('Member_card_sign')->where($data)->setField('delete', 1);
		
		// 删除userinfo里面的总积分中对应部分，可能会存在多线程不安全，不过对于并发小的情况下，概率不大
		$userinfo = M('Userinfo')->where(array('token'=>$data['token'],'wecha_id'=>$element['wecha_id'],'status'=>1))->find();
		
		$da['total_score']   = $userinfo['total_score'] - $element['expense'];
        $da['expend_score']  = $userinfo['expend_score'] - $element['expense'];
        
        $back2 = M('Userinfo')->where(array('token'=>$data['token'],'wecha_id'=>$element['wecha_id'],'status'=>1))->save($da);
		
        if (!$re || !$back2) {
            $this->error('服务器繁忙，请稍候再试');
        } else {
            $this->success('操作成功');
        } 
    }

    /**
    *删除积分兑换记录(实际数据未删除)
    **/
    public function delscorecost()
    {
        $data['id']     = $this->_get('id');
        $data['token']  = $this->token;
        $data['score_type']  = 3;
        if(empty($data['id']))
        {
            Log::record('未收到post过来的id');
            $this->error('出错啦，系统已经记录');
        }
		
	    $element = M('Member_card_sign')->where($data)->find();
        $re = M('Member_card_sign')->where($data)->setField('delete', 1);
		
	    // 删除userinfo里面的总积分中对应部分，可能会存在多线程不安全，不过对于并发小的情况下，概率不大
	    $userinfo = M('Userinfo')->where(array('token'=>$data['token'],'wecha_id'=>$element['wecha_id'],'status'=>1))->find();

        if ($userinfo) 
        {
            $da['total_score']  = $userinfo['total_score'] + $element['expense'];
            $da['spend_score']  = $userinfo['spend_score'] - $element['expense'];
            
            $back2 = M('Userinfo')->where(array('token'=>$data['token'],'wecha_id'=>$element['wecha_id'],'status'=>1))->save($da);
            if (!$re || !$back2) 
            {
                $this->error('服务器繁忙，请稍候再试');
            } 
            else 
            {
                $this->success('操作成功');
            } 
        }
        else
        {
            $this->error('记录不存在');
        }
    }


    //------------------------------------------
    // 添加消费积分记录
    //------------------------------------------
    public function addsell(){
        $isCard = $_POST['iscard'];
        unset($_POST['iscard']);
        //判断数据是否齐全
        if(empty($_POST)){
            $this->error('没有提交任何东西');exit; 
        }
        if($isCard<0){
            $this->error('请选择支付方式');exit; 
        }
        if($_POST['sell_expense'] < 1){
            $this->error('消费金额必须大于0元');exit; 
        }
        $token = session("token");
        // 检查卡号
        $cardnum = $this->_post('card_num');
        $card = M('Member_card_create')->where(array('token'=>$token,'number'=>$cardnum,'status'=>1))->find();
        if (!$card) {
            $this->error('卡号不存在');
        }
        if (!$card['wecha_id']) {
            $this->error('该卡号未被领用，无法消费');
        }

        //获取商家设置 tp_member_card_exchange 
        $getset = M('Member_card_exchange')->where(array('token'=>$token))->find();

        $where = array('token'=>$token,'wecha_id'=>$card['wecha_id'],'status'=>1);
        $userinfo = M('Userinfo')->where($where)->find();
        if(!$getset || !$userinfo){ 
            Log::record("商家：".implode($getset, '|')."会员：".implode($userinfo, '|').'sql:'.M('userinfo')->getlastSql());
            $this->error('没有找到相关的会员信息！');
        }
        $amount = $_POST['sell_expense'];
        $scorebeadd = intval($amount) * $getset['reward'];

        $da['total_score']   = $userinfo['total_score'] +  $scorebeadd;
        $da['expend_score']  = $userinfo['expend_score'] + $scorebeadd;
        if($isCard ){
            if($amount > $userinfo['total_money']){
                $this->error("会员卡余额不足！");exit();
            }
            else
            {
                $da['total_money'] = $userinfo['total_money'] - $amount;
                $da['spend_money'] = $userinfo['spend_money'] + $amount;
            }
        }

        $model = new Model();
        //事务处理开始
        $model->startTrans();
        //在总额上操作 
        $isSuccess = $model->autoCheckToken($_POST);//验证表单令牌，防止重复提交
        if($isSuccess)
        {
            unset($_POST[C('TOKEN_NAME')]);//除去post数组中的令牌数据，防止table方法提交失败 
            $isSuccess = $model->table('tp_userinfo')->where($where)->save($da);
        }
        $data['token'] = $token;
        $data['wecha_id'] = $card['wecha_id'];
        //若上一步成功则写入积分记录表
        if($isSuccess){ 
            $scoredata = array(
                'sign_time'=>time(),
                'is_sign'=>false,
                'score_type'=>2,
                'expense' => $scorebeadd,
                'delete' => 0,
                );
            $scoredata = array_merge($data,$scoredata,$_POST);
            unset($scoredata['card_num']);
            $isSuccess = $model->table('tp_member_card_sign')->add($scoredata);
        }
        //若上一步成功且是会员卡余额消费则写入充值记录表
        if($isSuccess && $isCard){
            $moneydata = array(
                'card_num' => $cardnum,
                'amount' => $amount,
                'comment' => $_POST['remark'],
                'type' => 1,
                'logon_ip' => getenv('REMOTE_ADDR'),
                'logon_user_id' => session('uid'),
                'oprator' => session('uname'),
                );
            $moneydata = array_merge($data,$moneydata);
            $isSuccess = $model->table('tp_member_charge')->add($moneydata);
        }
        if($isSuccess){
            $model->commit();//成功则事务结束
            $this->success('操作成功');
        }else{
            $model->rollback();//失败则rollback
            $this->error('服务器繁忙，请稍候再试');
        } 
    }
	
    /**
    *导入实体会员
    **/
	public function physicalmemberimport()
	{	
	    $token = session("token");
		
		$tmp_file = $_FILES ['file_stu'] ['tmp_name'];
		$file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
		$file_type = $file_types [count ( $file_types ) - 1];
	
		 /*判别是不是.xls文件，判别是不是excel文件*/
		if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls")              
		{
			$this->error('不是Excel文件，重新上传' );
		}
		
		/*设置上传路径*/
	    $savePath = $_SERVER['DOCUMENT_ROOT'].'/customer_imgs/'.$token.'/';
        if (!file_exists($savePath)) 
        {
            mkdir($savePath);
        }
		 
		/*以时间来命名上传的文件*/
		$file_name = date('Ymdhis'). "." . $file_type;
	    
			
		
		/*是否上传成功*/
		
		    ini_set('display_errors',1);
            error_reporting(E_ALL);
		if (!move_uploaded_file( $tmp_file, $savePath . $file_name )) 
		{
			$this->error ( '上传失败' );
		}
		$ExcelToArrary=new ExcelToArrary();//实例化
		$res=$ExcelToArrary->read($savePath.$file_name,"UTF-8",$file_type);//传参,判断office2007还是office2003

		$dump = "";
		$index = 0;
        $upFailedCount = 0;
        $upSuccessCount = 0;
		foreach ( $res as $k => $v ) //循环excel表
		{
		    // skip the title
		    if ($v[0] == "姓名") {
			    continue;
			}
            //根据电话号码去除数据库中已存在的项
            $row = M('physical_member')->where(array('token'=>$token,'tel'=>$v[2]))->find();
            if($row){
                $row['name'] = $v[0];
                $row['cardnum'] = $v[1];
                $row['tel'] = $v[2];
                $row['sex'] = $v[3] ? ($v[3] == '男' ? 1 : 2 ) :0 ;        
                $row['birthday'] = $v[4];
                $row['address'] = $v[5];
                $row['token'] = $token; 
                $res = M('physical_member')->save($row);
                if($res>=0){
                    $upSuccessCount++;
                }
                else {
                    $upFailedCount++;
                }
            }
            else{
                $data[$index]['name'] = $v[0];//创建二维数组
                $data[$index]['cardnum'] = $v[1];
                $data[$index]['tel'] = $v[2];
                $data[$index]['sex'] = $v[3] ? ($v[3] == '男' ? 1 : 2 ) :0 ;        
                $data[$index]['birthday'] = $v[4]; 
                $data[$index]['address'] = $v[5];
                $data[$index]['token'] = $token;
                
                $index = $index + 1;
            }
			
			$dump = $k." ".$v[0]." ".$v[1]." ".$v[2]." ".$v[3]." ".$v[4];
			Log::record($dump."\r\n", Log::DEBUG); 
			
        }
		$result = M("physical_member")->addAll($data);
        if($repeateCount > 0){
            $upres = M('physical_member')->save($updata);
            echo $upres;
        }
		Log::record($result."\r\n".$upres."\r\n", Log::DEBUG); 
        Log::save();	
		if( $index > 0 ? $result : true ) {
            $msg='会员资料导入成功，导入会员'.$index."个。";
            if($upSuccessCount>0){
                $msg = $msg."覆盖".$upSuccessCount."个。";
            } 
            $this->success($msg);
        } else {
            $this->error('服务器繁忙，请稍候再试');
        } 
    }

    /**
    *自定义会员等级
    **/
    public function setclassinfo(){
        $token = $this->token;
        $where['token'] =  $token;
        $where['status'] = 1;
        $data = M('member_group')->where($where)->field('groupid,id,title')->order('groupid asc')->select();

        if(IS_POST){
            $postData = $_POST['group'];
            $updata = array();
            $newData = array();//新增数据
            $deleteGIds = array();//删除的数据的id
            $hasDeleted = false;
            $baseData['token'] = $this->token;
            $baseData['update_time'] = time();

            $ret = true;
            if(!$data){//若没等级信息没有添加到数据库，则先写入数据库
                $ret = $this->movClassInfoToDB($data);
            }
            foreach ($postData as $key => $val) {//分离数据为：需要更新的，需要删除的，新添加的
                if($val['isNew'] && !$val['id']){//判断是否是新的
                    $baseData['create_time'] = time();
                    $temp = array_merge($baseData,$val);
                    array_push($newData, $temp);
                }
                else{
                    if ($val['isDelete']){
                        array_push($deleteGIds, $val['groupid']);
                        $hasDeleted || ($hasDeleted = true);
                    }else{
                        $temp = array_merge($baseData,$val);
                        unset($temp['id']);
                        array_push($updata, $temp);
                    }
                }
            }
            if($ret && $hasDeleted){//删除
                Log::record( '删除的会员等级ID为：' . print_r($deleteGIds,true));
                $ret = $this->delmemberclass($deleteGIds);
            }
            if($ret && $updata){//更新
                $ret = $this->updateClassInfo($updata);
            }
            if($ret && !empty($newData)){//添加新数据
                M('member_group')->addAll($newData);
            }
            if($ret){
                $this->success("保存成功！");
            }
            else{
                $this->error("服务器繁忙，请稍后再试！");
            }
        }
        $maxGroupId = count(C('MEMBER_GROUP'));
        if(empty($data)){
            $data = C('MEMBER_GROUP');
        }
        else {
            //取出最大ID
            $maxLevelInfo = M('member_group')->where(array('token'=>$token))->field('groupid')->order('groupid desc')->find();
            $maxGroupId = $maxLevelInfo['groupid'];
        }
        $this->assign('maxGroupId',$maxGroupId);
        $this->assign('class_info',json_encode($data));
        $this->display();
    }

    /**
    *$isFirst标志是否是第一次编辑会员等级，若是则需要将所有数据添加到member_group表
    **/
    public function updateClassInfo($updata){
        //dump($updata);exit();
        $ret = true;
        Log::record( '更新的会员等级信息为：' . print_r($updata,true));
        foreach ($updata as $key => $val) {
            $back = M('member_group')->where(array('token'=>$this->token,'groupid'=>$val['groupid']))->save($val);
            $ret = $ret && $back >= 0;
            if(!$ret){
                break;
            }
        }
        Log::record('要更新的会员等级为：' . print_r($updata,true));
        return $ret;
    }

    /**
    *删除会员等级并将其等级的会员移至最低级,其他关联数据关联等级为-1；
    **/
    public function delmemberclass($deleteGIds){
        $where['token'] = $this->token;
        $where['status'] = 1;
        $str_ids = implode(',', $deleteGIds);
        $where['groupid'] = array('in',  $str_ids);
        $lowest_groupid = M('member_group')->where( array('token'=>$this->token,
                                                          'groupid'=>array('not in',$str_ids),
                                                          'status'=>1,
                                                         )
                                                  )
                                           ->order('groupid asc')
                                           ->find();
        $ret = $lowest_groupid['groupid'];
        //删除
        if($ret){
            $ret = M('member_group')->where( $where )->setField('status',-1);
        }
        // 处理等级会员、优惠券、特权
        if($ret){
            $back = M('member_card_create')->where($where)->setField('groupid',$lowest_groupid['groupid']);//等级会员
            $ret = $back >= 0 && $ret;
        }
        if($ret){
            $back = M('member_card_coupon')->where($where)->setField('groupid',$lowest_groupid['groupid']);//优惠券
            $ret = $back >= 0 && $ret;
        }
        if($ret){
            $back = M('member_card_vip')->where($where)->setField('groupid',$lowest_groupid['groupid']);//特权
            $ret = $back >= 0 && $ret;
        }
        Log::record('删除的会员等级ID为：'.print_r($deleteIds,true));
        return $ret;
    }

    //从配置文件移动会员等级到数据库，，因为用户会删掉等级信息，所以提供$data参数
    public function movClassInfoToDB($data){
        if($data) { return true; }
        $data = C('MEMBER_GROUP');
        $max = count($data);
        for($i = 0 ; $i<$max ; $i++){
            unset($data[$i]['id']);
            $data[$i]['create_time'] = time();
            $data[$i]['update_time'] = $data['create_time'];
            $data[$i]['token'] = $this->token;
        }
        $ret = M('member_group')->addAll($data);
        return $ret;
    }
}
?>

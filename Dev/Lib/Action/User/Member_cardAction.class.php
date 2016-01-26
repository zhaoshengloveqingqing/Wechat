<?php
class Member_cardAction extends UserAction {

    protected function _initialize() {
        parent::_initialize();
        $this->function='huiyuanka';
        parent::checkOpenedFunction();
    }

    //会员卡配置
    public function index()
    {
        $data = M('Member_card_set')->where(array('token'=>$this->token))->find();
        $num_set = unserialize($data['card_num_set']);

        if (IS_POST) { 
            $_POST['token'] = $this->token;
            if (empty($_POST['diybg']) && (empty($_POST['bg']) || strpos($_POST['bg'], '会员卡背景图') !== false))
            {
                $this->error("请选择会员卡背景图");
            }
            $show_items = array('showMoney'=>$_POST['showMoney'],'showScore'=>$_POST['showScore']);
            $updata = array_diff($_POST, $show_items);
            $updata['show_items'] = serialize($show_items);
            $num_set = array();
            if($_POST['is_diy_num']){
                $num_set['prefix'] = $_POST['prefix'];
                $num_set['suffix'] = $_POST['suffix'];
            }
            $updata['card_num_set'] = serialize($num_set);
            $res = 0;
            if ($data==false){      
                $res = M('Member_card_set') -> add($updata); 
            } else {
                $updata['id'] = $data['id'];
                $res = M('Member_card_set') ->where(array('id'=>$updata['id'])) -> save($updata);
            }
            if($res){
                $this->success("操作成功！");
            }else{
                $this->error('服务器繁忙请稍后再试');
            }
        } else {

            $this->assign('num_set',$num_set);

            $temp = unserialize($data['show_items']);
            $this->assign('show_items',$temp); 
            $this->assign('card',$data);
            $this->display();
        }
    }

    public function change_card_signstate(){
        if(!IS_POST){
            exit();
        }
        $data =  M('Member_card_set')->where(array('token'=>$this->token))->find();
        if(!$data){
            $this->error('非法操作！');
        }
        $oldvalue = $data['card_off'];
        $data['card_off'] = $oldvalue>0 ? 0 : 1;
        $res = 0;
        $res = M('Member_card_set')->where(array('token'=>$this->token))->save($data);
        $msg = array('success' => $res, 
            'oldvalue' => $oldvalue , 
            'msg' => $res? !$oldvalue ? "已成功停止发卡！如要开始发卡请点击发卡按钮！" : "已成功开始发卡！如要停止发卡请点击暂停发卡按钮！" : "操作失败请重试！",
            'newvalue'=>$oldvalue>0 ? 0 : 1);
        echo json_encode($msg);
    }

    public function privilege() {
        $data=M('Member_card_vip')->where(array('token'=>$_SESSION['token']))->order('id desc')->select();
        $class_con = array('token'=>$this->token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }
        $this->assign('class_info',$class_info);
        $this->assign('data_vip',$data);
        $this->display();    
    }    

    public function privilege_add() {
        if (IS_POST) {
            $this->all_insert('Member_card_vip','/privilege');
        } else { 
            $class_con = array('token'=>$this->token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $this->assign('class_info',$class_info);
            $this->display();    
        }
        
    }

    public function privilege_edit() {
        if (IS_POST) {
            $_POST['id']=$this->_get('id');
            $this->all_save('Member_card_vip','/privilege');
        } else {
            $data=M('Member_card_vip')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
            if ($data != false) {
                $class_con = array('token'=>$this->token,'status'=>1);
                $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
                if(empty($class_info)){
                    $class_info = C('MEMBER_GROUP');
                }
                $this->assign('class_info',$class_info);
                $this->assign('vip',$data);
                $this->display();
            } else {
                $this->error('非法操作');
            }
        }
    }

    public function privilege_del() {
            $data=M('Member_card_vip')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
            if($data==false){
                $this->error('服务器繁忙请稍后再试');
            }else{
                $this->success('操作成功',U('Member_card/privilege',array('id'=>$data_vip['id'])));
            
            }
    }
    
    //会员管理
    public function create(){    
        $cond = array('token'=>$_SESSION['token'], 'wecha_id' => array('neq','') , 'status' => 1);
        $data = M('Member_card_create'); 
        $token = $this->token;
        //会员卡总张数
        $member_amount      = $data->where($cond)->count(); 
        //是否停止发卡
        $stopsign = M('Member_card_set')->where(array('token'=>$token))->field('card_off,card_amount_limit')->find();
        $amount_limit = $stopsign['card_amount_limit'] ? $stopsign['card_amount_limit'] : C('MEMBER_AMOUNT_LIMIT');
        $this->assign('stopsign',$stopsign['card_off']); 
        $this->assign('amount_limit',$amount_limit);
        //基本条件,  card_create as card | wecha_user as wu | user_info as member
        $where = array("member.token"=>$this->token,
                        "member.status" => 1,
                        "wu.token"=>$this->token,
                        "card.token"=>$this->token,
                        "card.status" => 1,
                        'card.wecha_id' => array('neq','')); 
        //搜索则组合搜索条件 
        $key = $_POST['searchkey'] ? $_POST['searchkey'] : $_GET['searchkey'] ;
        if($key){
            $where['_string']= "((wu.tel LIKE '%$key%') OR (wu.truename LIKE '%$key%') OR (wu.wecha_id = '$key') OR (card.number = '$key'))";
        }
        $sourcekey = $_POST['sourcekey'] ? $_POST['sourcekey'] : $_GET['sourcekey'];

        if($sourcekey && $sourcekey != 'all'){
            $where['member.source'] = $sourcekey;
        } 
        $table = "tp_member_card_create as card 
                    LEFT JOIN tp_wecha_user as wu on card.wecha_id = wu.wecha_id 
                    LEFT JOIN tp_userinfo as member on card.wecha_id = member.wecha_id"; 
        $model = new Model();
        //获取总条数
        //$membercount = $model->table($table)->where($where)->count();
        //分页 
        $Page       = new Page($member_amount,15);
        $show       = $Page->show();
        $limit = $Page->firstRow.','.$Page->listRows;

        $res = $model->table($table)
        			 ->where($where)
                     ->limit($limit)
                     ->order('card.id desc')
                     ->field('  card.id,
                                card.token,
                                card.number,
                                card.wecha_id,
                                card.groupid,
                                card.getcardtime,
                                wu.nickname,
                                wu.truename,
                                wu.birthday,
                                wu.tel,
                                wu.id as wid,
                                member.total_score,
                                member.source,
                                member.total_money')
        			 ->select();
        $class_con = array('token'=>$this->token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }

        $this->assign('class_info',$class_info);
        $this->assign('sourcekey',$sourcekey);
        $this->assign('keyword',$key);
        $this->assign("member_amount",$member_amount);
        $this->assign('page',$show);
        $this->assign('data_vip',$res);
        $this->display();    
    }

    public function card_del(){
        $where = array('token'=>session('token'),'id'=>$this->_get('id'),'status'=>1);
        $card = M('Member_card_create')->where($where)->find();
        $ret = $card ? 1 : 0;
        if($ret){
            $ret = M('Member_card_create')->where($where)->setField('status',0);
        }
        if($ret){
            //因为userinfo表中使用了token、wecha_id作为唯一索引，所以有status置为0后用户不能再次注册的问题
            M('Userinfo')->where(array('token'=>$this->token,'wecha_id'=>$card['wecha_id'],'status'=>1))->setField('status',0);
        }
        if($ret == false){
            $this->error('服务器繁忙请稍后再试');
        }
        else{
            M('Member_card_set')->where(array('token'=>$this->token))->setDec('card_amount',1);
            $this->success('操作成功',U('Member_card/create'));
        }
    }
        
    public function edit(){
        if (IS_POST) {
            $data['groupid'] = $this->_post('groupid');
            $re=M('Member_card_create')->where(array('token'=>session('token'),'id'=>$this->_get('id'),'status'=>1))->save($data);
            if($re==false){
                $this->error('服务器繁忙请稍后再试');
            }else{
                $this->success('操作成功',U('Member_card/create'));
            }
        } else {
            $data=M('Member_card_create')->where(array('token'=>session('token'),'id'=>$this->_get('id'),'status'=>1))->find();
            if ($data == false || empty($data['wecha_id'])) {
                $this->error("卡片空闲，无法修改", U("Member_card/create"));
            }
        //Log::record($data."\r\n", Log::DEBUG);       
        //Log::save();
            $class_con = array('token'=>$this->token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $this->assign('class_info',$class_info);
            $this->assign("card", $data);
            $this->display();
        }
    }

    //会员优惠卷
    public function coupon(){
        $data=M('Member_card_coupon')->where(array('token'=>$_SESSION['token']))->order('id desc')->select();
        $class_con = array('token'=>$this->token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }
        $this->assign('class_info',$class_info);
        $this->assign('data_vip',$data);
        $this->display();    
    }

    public function coupon_edit(){
        if(IS_POST){            
            $this->all_save('Member_card_coupon','/coupon');
        }else{
            $data=M('Member_card_coupon')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
            if($data!=false){
                $class_con = array('token'=>$this->token,'status'=>1);
                $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
                if(empty($class_info)){
                    $class_info = C('MEMBER_GROUP');
                }
                $this->assign('class_info',$class_info);
                $this->assign('vip',$data);
                $this->display();
            }else{
                $this->error('非法操作');
            }
            
        }
        
    }  

    public function coupon_add(){
        if(IS_POST){            
            $this->all_insert('Member_card_coupon','/coupon');
        }else{
            $where['token'] = $this->token;
            $where['status'] = 1;
            $class_info = M('member_group')->where($where)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $this->assign('class_info',$class_info);
            $this->display();    
        }
        
    }

    public function coupon_del(){
            $data=M('Member_card_coupon')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
            if($data==false){
                $this->error('服务器繁忙请稍后再试');
            }else{
                $this->success('操作成功',U('Member_card/coupon',array('id'=>$data_vip['id'])));
            
            }
    }
    //会员礼卷
    public function integral(){
        $data=M('Member_card_integral')->where(array('token'=>$_SESSION['token']))->order('id desc')->select();
        $class_con = array('token'=>$this->token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }
        $this->assign('class_info',$class_info);
        $this->assign('data_vip',$data);
        $this->display();    
    }

    public function integral_edit(){
        if(IS_POST){            
            $this->all_save('Member_card_integral','/integral');
        }else{
            $data=M('Member_card_integral')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
            if($data!=false){
                $class_con = array('token'=>$this->token,'status'=>1);
                $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
                if(empty($class_info)){
                    $class_info = C('MEMBER_GROUP');
                }
                $this->assign('class_info',$class_info);
                $this->assign('vip',$data);
                $this->display();
            }else{
                $this->error('非法操作');
            }
            
        }
        
    } 

    public function integral_add(){
        if(IS_POST){            
            $this->all_insert('Member_card_integral','/integral');
        }else{
            $class_con = array('token'=>$this->token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $this->assign('class_info',$class_info);
            $this->display();    
        }
        
    }

    public function integral_del(){
            $data=M('Member_card_integral')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
            if($data==false){
                $this->error('服务器繁忙请稍后再试');
            }else{
                $this->success('操作成功',U('Member_card/integral',array('id'=>$data_vip['id'])));
            
            }
    }
    
    //商家信息设置
    public function info(){
        $data=M('Member_card_info')->where(array('token'=>$_SESSION['token']))->find();
        if(IS_POST){

            if ($data == false) {
                // 添加新主页
                $db = M('Member_card_info');
                $data['token'] = session('token');
                $data['logo'] = $_POST['logo'];
                $data['description'] = $_POST['description'];
                $data['info'] = $_POST['info'];
                $data['password'] = $_POST['password'];
                $data['class'] = $_POST['class'];
                $data['createtime'] = time();
                 $data['updatetime'] = $data['createtime'];
                $data['status'] = 1;
                $Membership_id = $db->add($data);
                if ($Membership_id) {
                    /*$kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['type'] = 1;
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = 'huiyuanka';
                    $kwd_data['pid'] = 0;
                    $keywords = array('会员卡');
                    foreach($keywords as $vo) {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }*/
                    $this->success('操作成功',U(MODULE_NAME.'/info'));
                } else {
                    $this->error('添加失败',U(MODULE_NAME.'/info'));
                }

            } else {
                $where['token']     = session('token');
                $data['logo']         = $_POST['logo'];
                $data['description'] = $_POST['description'];
                $data['info'] = $_POST['info'];
                $data['class'] = $_POST['class'];
                $data['password'] = $_POST['password'];
                $data['updatetime'] = time();
                $ret = M('Member_card_info')->where($where)->save($data);
                if ($ret) {
                    /*$kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['type'] = 1;
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = 'huiyuanka';
                    $kwd_data['pid'] = 0;
                    $keywords = array('会员卡');
                    foreach($keywords as $vo) {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }*/
                    $this->success('操作成功',U(MODULE_NAME.'/info'));
                } else {
                    $this->error('保存失败',U(MODULE_NAME.'/info'));
                }
            }
        }else{
            $this->assign('info',$data);
            $contact=M('Member_card_contact')->where(array('token'=>$_SESSION['token']))->order('sort desc')->select();
            $this->assign('contact',$contact);
            $this->display();
        }    
    }
    
    /**
    *积分设置 设置会员卡积分策略及会员卡级别
    * 级跃：
    * {
			"start": 1,
			"map": {
				"5": 10,
				"10": 20,
				"20": 30
			},
			"last": 6,
			"prize": 2
		}
    *
    *
    */
    public function exchange(){
        $data=M('Member_card_exchange')->where(array('token'=>$_SESSION['token']))->find();
        if(IS_POST){
        	if (!trim($this->_post('start'))) {
        		$this->error('请设置每天签到的积分');
        	}
        	$this->valid_num($this->_post('start'), '每天签到的积分');
        	$start = '';
        	if (trim($this->_post('start'))) {
        		$start = '"start":'.$this->_post('start');
        	}
        	$days = $_POST['keys'];
        	$scores = $_POST['values'];
        	switch ($_POST['type']){
        		case '1':
        			if ((trim($this->_post('last')) && !trim($this->_post('prize'))) || (!trim($this->_post('last')) && trim($this->_post('prize')))) {
        				$this->error('请设置最大签到天数限制或最大签到天数积分');
        			}
        			$this->valid_num($this->_post('last'), '最大签到天数限制');
        			$this->valid_num($this->_post('prize'), '最大签到天数积分');
        			$max = '';
        			if (trim($this->_post('last')) && trim($this->_post('prize'))) {
        				$max = '"last":'.trim($this->_post('last')).', "prize":'.trim($this->_post('prize'));
        			}
        			if($days){
        				$max_day = max($days);
        				if (trim($this->_post('last')) && trim($this->_post('last')) <= $max_day) {
        					$this->error('最大签到天数限制应该大于配置的签到天数中最大的天数');
        				}
        			}
        			break;
        		case '2':
        		case '3':
        			if (!trim($this->_post('step'))) {
		        		$this->error('请设置积分增长率');
		        	}
		        	$this->valid_num($this->_post('step'), '最大签到天数限制');
        			$this->valid_num($this->_post('limit'), '最大签到天数积分');
        			break;
        	}
        	
        	$step = '';
        	if (trim($this->_post('step'))) {
        		$step = '"step":'.$this->_post('step');
        	}
        	$limit = '';
        	if (trim($this->_post('limit'))) {
        		$limit = '"limit":'.$this->_post('limit');
        	}
        	
        	$map = '';
        	if ($days) {
        		$day = '';
        		foreach ($days as $key=>$v) {
        			$this->valid_num($v, '积分天数');
        			$this->valid_num($scores[$key], '奖励积分');
        			if ($v && $scores[$key]!='') {
        				if ($v && $v != $day) {
        					$map .= '"'.$v.'"'.':'.$scores[$key].',';
        				}
        				$day = $v;
        			}
        		}
        	}
        	
        	$str = '';
        	if ($start){
        		$str .= $start.',';
        	}
        	if ($step){
        		$str .= $step.',';
        	}
        	if ($max){
        		$str .= $max.',';
        	}
        	if ($limit){
        		$str .= $limit.',';
        	}
        	if ($map){
        		$str .= '"map":{'.($map ? trim($map, ',') : '').'},';
        	}
        	$_POST['config'] = $str ? '{'.trim($str, ',').'}' : '';
        	
            $_POST['token']=$_SESSION['token'];    
            $_POST['create_time'] = time(); 

            if($data==false){                
                $this->all_insert('Member_card_exchange','/exchange');
            }else{
                $_POST['id']=$data['id'];
                $this->all_save('Member_card_exchange','/exchange');
            }
        }else{
        	$config = json_decode($data['config']);
        	if (isset($config->start)) {
        		$data['start'] = $config->start;
        	}
        	if (isset($config->limit)) {
        		$data['limit'] = $config->limit;
        	}
        	if (isset($config->step)) {
        		$data['step'] = $config->step;
        	}
        	if (isset($config->last)) {
        		$data['last'] = $config->last;
        	}
        	if (isset($config->start)) {
        		$data['prize'] = $config->prize;
        	}
            $this->assign('exchange',$data);
            $this->display();
        }    
    }
    
    
    function valid_num($str, $notice){
    	$regex = '/^\d*$/';
    	if ($str && !preg_match($regex, $str)) {
        	$this->error($notice.'：请输入数字');
        }
    }
    
    
	function cal_score($n, $conf) {
		$res = 0;
		if($n <= 0) {
			//trigger_error('Day must bigger than 0!');
			return $res;
		}
		if(!isset($conf->start)) {
			$conf->start = 1;
		}
	
		if(!isset($conf->map) && !isset($conf->step)) {
			$conf->step = 1;
		}
	
		if(isset($conf->step)) {
			$res = $conf->start + ($n - 1) * $conf->step; 
		}
		else {
			$res = $conf->start;
			if(isset($conf->last) && isset($conf->prize)) {
				if($n >= $conf->last)
					$res = $conf->prize;
			}
		}
	
		if(isset($conf->limit) && $conf->limit < $res) {
			$res = $conf->limit;
		}
	
		if(isset($conf->map)) {
			if(isset($conf->map->$n)) {
				$res = $conf->start + $conf->map->$n;
			}
		}
	
		return $res;
	}

}
?>

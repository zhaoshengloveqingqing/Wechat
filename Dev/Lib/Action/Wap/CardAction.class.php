<?php
class CardAction extends BaseAction{
	protected function _initialize(){
		parent::_initialize();
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if (!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
	}

	public function index(){
		$token=$this->_get('token');
		if($token!=false){
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			$this->assign('card',$data);
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }

	public function get_card(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		$get_card=M('member_card_create')->where(array('wecha_id'=>$wecha_id,'token'=>$token,'status'=>1))->find();
		$uniquepair = array('token'=>$token,'wecha_id'=>$wecha_id);

		if($get_card!=false){
			Header("Location: http://".C('wx_handler_server').'/'.U('Wap/Card/vip', $uniquepair)); 
		}
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$info=M('member_card_info')->where(array('token'=>$token))->find();
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			
			//$set_exchange = M('Member_card_exchange')->where(array('token'=>$token))->find();
			//$this->assign('exchange',$set_exchange);
			$this->assign('uniquepair',$uniquepair);
			$this->assign('card',$data);
			//$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('info',$info);
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }

	public function info(){
		$token=$this->_get('token');
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$info=M('member_card_info')->where(array('token'=>$token))->find();
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			//我的卡号
			$mycard=M('member_card_create')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id'),'status'=>1))->find();
			//显示会员等级
            $class_con = array('token'=>$token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $group_info = '';
            foreach ($class_info as $key => $val) {
	          if($mycard['groupid'] == $val['groupid']){
	          	$group_info = $val['title'];
	            break;
	          }
	        }  
            $this->assign('group_info',$group_info);
			$this->assign('mycard',$mycard);
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('info',$info);
		}else{
			$this->error('无此信息');
		}
		$this->display();	
    }

	public function vip(){
		$token=$this->_get('token');
		$wecha_id=$this->_get('wecha_id');
		 
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$shopinfo=M('member_card_info')->where(array('token'=>$token))->find();
			//卡号
			$card=M('member_card_create')->where(array('token'=>$token,'wecha_id'=>$this->_get('wecha_id'),'status'=>1))->find();
			if(!$card){
				$uniquepair = array('token'=>$token,'wecha_id'=>$wecha_id);
				Header("Location: http://".C('wx_handler_server').'/'.U('Wap/Card/get_card', $uniquepair)); 
				exit();
			}
 
 			//余额和积分
			$where = array('token' =>$token ,'wecha_id' => $wecha_id ,'status'=>1);
			$showitems = unserialize($data['show_items']); 
			$show = $showitems ? $showitems['showMoney'] || $showitems['showScore'] : 1; 
			$this -> assign('show',$show);
			if($show){
				$balance = M('userinfo')-> field('total_money,total_score') ->where($where)->find();
				$this->assign('balance',$balance);
				$this->assign('showitems',$showitems);
			}

			//var_dump($card);exit;
			//dump(array('token'=>$token,'wecha_id'=>$this->get('wecha_id')));
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('info',$shopinfo);			
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//dump($data);
			$this->assign('card',$data);
			//特权服务
			$where_vip = array('token'=>$token);
			$where_vip['_string'] = ' ( enddate = 0 OR enddate > unix_timestamp() )';//过滤掉过期的
			$vip=M('member_card_vip')->where($where_vip)->order('id desc')->limit(3)->select();
			//dump($vip);
			$this->assign('vips',$vip);
			//优惠卷
			$coupon=M('member_card_coupon')->where($where_vip)->order('id desc')->limit(3)->select();
			$this->assign('coupons',$coupon);
			//兑换
			$integral=M('member_card_integral')->where($where_vip)->order('id desc')->limit(3)->select();
			$this->assign('integrals',$integral);
			// 是否有签到
			$set_exchange = M('Member_card_exchange')->where(array('token'=>$token))->find();
			$this->assign('exchange',$set_exchange);
			//显示会员等级
            $class_con = array('token'=>$token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $group_info = '';
            foreach ($class_info as $key => $val) {
	          if($card['groupid'] == $val['groupid']){
	          	$group_info = $val['title'];
	            break;
	          }
	        }  
            $this->assign('group_info',$group_info);
            $this->assign('class_info',$class_info);
            $this->assign('token',$token);
            $this->assign('wecha_id',$wecha_id);
		}else{
			$this->error('无此信息');
		}
	
		$this->display();
	
	}
	public function addr(){
	
		$token=$this->_get('token');
		if($token!=false){
			//会员卡信息
			$data=M('member_card_set')->where(array('token'=>$token))->find();
			//商家信息
			$addr=M('member_card_contact')->where(array('token'=>$token))->select();
			//联系方式
			$contact=M('member_card_contact')->where(array('token'=>$token))->order('sort desc')->find();
			//我的卡号
			$mycard=M('member_card_create')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id'),'status'=>1))->find();
			$this->assign('mycard',$mycard);
			$this->assign('card',$data);
			$this->assign('card_info',$card);
			$this->assign('contact',$contact);
			$this->assign('addr',$addr);
		}else{
			$this->error('无此信息');
		}
		$this->display();
	
	}
	//展示会员的特权，优惠券，礼品券，因结构类似，故此放在一起展示
	public function viewbenefits(){
		$token = $this->_get('token');
		$wecha_id = $this->_get('wecha_id');
		$type = $this->_get('type');
		if(!$token || !$wecha_id || !$type){
			$this->error('无此信息');
			exit();
		}
		$where = array('token'=>$token);
		$where['_string'] = ' (enddate = 0 OR enddate > unix_timestamp())';//过滤掉过期的
		$model = '';
		$title = '';
		switch ($type) {
			case 'vip'://特权
				$model = M('member_card_vip');
				$title = '会员特权列表';
				break;
			case 'coupon'://优惠券
				$model = M('member_card_coupon');
				$title = '会员优惠券列表';
				break;
			case 'integral'://礼品券
				$model = M('member_card_integral');
				$title = '会员礼品券列表';
				break;
			default:
				$this->error('无此信息');
				exit();
				break;
		}
		$data = $model->where($where)->order('id desc')->select();

		$this->assign('title',$title);
		$this->assign('data',$data);
		$this->assign('type',$type);

		//显示会员等级
        $class_con = array('token'=>$token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }
        $this->assign('group_info',$class_info);

		$this->display();

	}
}
?>

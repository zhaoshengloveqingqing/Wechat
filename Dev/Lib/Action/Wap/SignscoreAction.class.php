<?php
/**
* 会员签到
*
*/
class SignscoreAction extends BaseAction 
{

	//显示
    public function index()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 

        $wecha_id = $this->_get('wecha_id');

        if (!strpos($agent,"MicroMessenger") || empty($wecha_id)) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }

        $token    =  $this->_get('token');

        //  Member_card_exchange   商家后台积分设置
        $set_exchange = M('Member_card_exchange')->where(array('token'=>$token))->find();
        if (empty($set_exchange))
        {
            exit("该商家尚未设置该功能.");
        }

        $get_card=M('member_card_create')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1))->find();
        //木有领卡 ,跳到领卡页面。
        if (empty($get_card))
        {
            Header("Location: ".C('site_url').'/'.U('Wap/Card/vip',array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))); 
            exit('领卡后才可以签到.');
        }

        //  tp_member_card_sign   会员签到表
        $sign_db    = M('Member_card_sign');   
        $where      = array('token'=>$token,'wecha_id'=>$wecha_id,'score_type'=>1);
        //查找最新的签到记录  
        $sign       = $sign_db->where($where)->order('sign_time desc, id desc')->find();
        
        $signined = 0;
        // Userinfo 总积分 = 签到总积分 + 消费总积分 和 连续签到计数器
        $member_db      = M('Userinfo');
        $member_where   =  array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1);
        $userinfo =  $member_db->where($member_where)->find();
        if (IS_POST)
        {
        	$signined = $this->_todySign($where);
        	if ($signined){
        		header('Location:'.U('Signscore/index', array('token'=>$token,'wecha_id'=>$wecha_id)));
        		exit();
        	}
            //签到记录
            $sign_data['sign_time']  = time();
            $sign_data['is_sign']    = 1; 
            $sign_data['score_type'] = 1;
            $sign_data['token']      = $token;
            $sign_data['wecha_id']   = $wecha_id;
            
            //签到总积分 = 原签到总积分 + 今天签到积分；
            //总积分 = 原总积分 + 今天签到积分；
            $conf = json_decode($set_exchange['config']);
            $sign_num = $this->_continue($where, $userinfo['continuous']);
            
			$sign_data['expense']    = $sign_num == 0 ? (isset($conf->start) ? $conf->start : 1) : $this->cal_score($sign_num, $conf);
			$sign_db->data($sign_data)->add(); 
			
			$member_data['sign_score']  = ($userinfo['sign_score'] ? $userinfo['sign_score'] : 0) + $sign_data['expense'];
			$member_data['total_score'] = ($userinfo['total_score'] ? $userinfo['total_score'] : 0) + $sign_data['expense'];
			$member_data['continuous']  = $sign_num;
			$member_db->where($member_where)->save($member_data);           
        }
        
        $cardset = M('Member_card_set')->where(array('token'=>$token))->find(); 
        $this->assign('cardset',$cardset);

        $sign   = $sign_db->where($where)->order('sign_time desc')->limit(6)->select();
        $this->assign('sign',$sign);  
         
        $userinfo =  $member_db->where($member_where)->find();  
        $this->assign('userinfo',$userinfo);

        $signined = $this->_todySign($where);
        $this->assign('signined',$signined);
		$this->assign('set_exchange',$set_exchange);
    
        $this->display();
    }

    //-----------------------------------
    //  消费记录 
    //-----------------------------------
    public function expend()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if (!strpos($agent,"MicroMessenger")) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }     

        $token      = $this->_get('token');
        $wecha_id   = $this->_get('wecha_id');

        $sign_db    = M('Member_card_sign');  
        $where      = array('token'=>$token,'wecha_id'=>$wecha_id,'score_type'=>2);
        $sign       = $sign_db->where($where)->order('sign_time')->limit(6)->select();  //消费积分 
        $this->assign('sign',$sign);   

        $cardset    = M('Member_card_set')->where(array('token'=>$token))->find(); //获取banner的logo
        $this->assign('cardset',$cardset);
        
        $userinfo   = M('Userinfo')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'status'=>1))->find(); //获取总积分,签到积分,签到天数
		
		$set_exchange = M('Member_card_exchange')->where(array('token'=>$token))->find();
		$this->assign('set_exchange',$set_exchange);
		
        $this->assign('userinfo',$userinfo);
        
        $this->assign('signined',$signined);
        
        $this->display();
    }
    
    
	/*验证当天是否签到*/
    public function _todySign($where){
    	$is_sign 	= 0;
    	$time 		= strtotime(date('Y-m-d')); //凌晨时间
    	$last_time 	= M('member_card_sign')->where($where)->order('sign_time desc')->getField('sign_time');
    	//签到时间大于今天凌晨的时间则今天已经签到
    	if($time<$last_time){
    		$is_sign = 1;
    	}
    	return $is_sign;
    }
    
    
	/*连续签到次数*/
    public function _continue($where, $sign_num){
    	//昨天时间戳
    	$startYesterday = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
    	$endYesterday	= mktime(0,0,0,date('m'),date('d'),date('Y'))-1;

    	$where['sign_time']		= array(array('gt',$startYesterday),array('lt',$endYesterday),'AND');
    	$time 	= M('member_card_sign')->where($where)->getField('sign_time');
    	if($time){
    		return $sign_num+1;
    	}else{
    		return 0;
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


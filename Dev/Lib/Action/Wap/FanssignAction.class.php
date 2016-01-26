<?php
class FanssignAction extends WapAction{
	public $wecha_id; //粉丝识别码
	public $token;	  //用户身份证
	public $fansInfo; //粉丝信息
	public $sign_db;  //签到表
	public $df_integral =5;//默认签到积分
	
	public function _initialize(){
		parent::_initialize();
		if (!defined('RES')){
			define('RES',THEME_PATH.'common');
		}
		$this->wecha_id	= $this->_get('wecha_id');
		$this->token 	= $this->_get('token');
		$this->sign_db 	= M('sign_in');
		$this->sign_set = M('sign_set');
		$this->sign_user = M('sign_user');
		$this->fans = M('sign_user')->where(array('token'=>$this->token,'wecha_id'=>$this->wecha_id))->find();

		$this->assign('token',$this->token);
		$this->assign('wecha_id',$this->wecha_id);
	}
	
	
	function addFans(){
		if (IS_POST) {
			$data = array(
				'token'=>$this->_post('token'),
				'wecha_id'=>$this->_post('wecha_id'),
				'username'=> $this->_post('username'),
				'phone'=> $this->_post('tel'),
				'sex'=> $this->_post('sex'),
				'birthday'=> $this->_post('birthday'),
				'create_time'=> time()
			);
			$this->fans = M('sign_user')->where(array('token'=>$data['token'],'wecha_id'=>$data['wecha_id']))->find();
			if (!$this->fans) {
				$id = $this->sign_user->add($data);
				if ($id) {
					$this->success('添加成功', U('Fanssign/index', array('token'=>$this->_post('token'), 'wecha_id'=>$this->_post('wecha_id'))));
				}else {
					$this->error('添加失败');
				}
			}else{
				$data = array(
					'username'=> $this->_post('username'),
					'phone'=> $this->_post('tel'),
					'sex'=> $this->_post('sex'),
					'birthday'=> $this->_post('birthday')
				);
				M('sign_user')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->wecha_id))->save($data);
				$this->success('修改成功', U('Fanssign/index', array('token'=>$this->_post('token'), 'wecha_id'=>$this->_post('wecha_id'))));
			}
		}else{
			$sign = $this->sign_set->where(array('token'=>$this->token))->find();
			$this->assign('homepic', isset($sign['top_pic']) ? $sign['top_pic'] : '/tpl/static/sign/top.jpg');
			$this->display('info');
		}
	}

	/*签到首页*/
	public function index(){
		if ($this->wecha_id && !$this->fans){
			$this->error('请先完善个人资料再签到', U('Fanssign/addFans',array('token'=>$this->token,'wecha_id'=>$this->wecha_id)));
		}
		/*粉丝个人信息完整验证
		*/
		$where = array('user.token'=>$this->token,'user.wecha_id'=>$this->wecha_id);
		$count	= $this->sign_db
				->join(' INNER JOIN '.C("DB_PREFIX").'sign_user user ON user.`id` = `user_id`')
				->where($where)
				->sum('integral');  //总积分
		$sign   = $this->sign_db
				->join(' INNER JOIN '.C("DB_PREFIX").'sign_user user ON user.`id` = `user_id`')
				->where($where)
				->order('sign_time desc')
				->find(); //连续签到次数
		$month 		= $this->_get('month','intval');
		//查询指定月份签到记录
		if(empty($month)){
			$month 	= date('m');
		}
		//echo strtotime('-1 day');
		$month_time = $this->_mFristAndLast($month); //指定月份起始结束时间戳
		$where['sign_time']	= array(array('gt',$month_time['firstday']),array('lt',$month_time['lastday']),'AND');
		$list = $this->sign_db->join('inner join '.C("DB_PREFIX").'sign_user user on user.`id` = `user_id`')->where($where)->order('sign_time desc')->limit(6)->select();

		$this->top_pic = M('sign_set')->where(array('token'=>$this->token))->getField('top_pic');

		$this->assign('empty','<tr><td colspan="2">您本月还没有签到</td></tr>');
		if ($this->top_pic){
			$this->assign('sign_pic',$this->top_pic);
		}else {
			$this->assign('sign_pic','/tpl/static/sign/top.jpg');
		}
		
		//检查今天是否签到
		$this->assign('tody_sign',$this->_todySign());
		$this->assign('integral',$sign['integral']);
		$this->assign('count',$count);
		$this->assign('sign_num',$sign['continue']);
		$this->assign('list',$list);
		$this->display();
    }

    /*签到*/
    public function addSign(){
    	if($this->_todySign()){
    		echo'{"success":1,"msg":"您今天已经签到了"}';
    		exit();	
    	}
    	$where = array('user.token'=>$this->token,'user.wecha_id'=>$this->wecha_id);
    	$sign_num  = $this->sign_db->join('inner join '.C("DB_PREFIX").'sign_user user on user.`id` = `user_id`')->where($where)->order('sign_time desc')->getField('continue');
    	$sign_integral  = M('sign_conf')->where(array('use'=>'1', 'token'=>$this->token))->select();
    	
    	//连续签到奖励积分
    	$data	 			= array();
    	$data['sign_time']		= time();
    	$data['continue']	= $this->_continue($sign_num);
    	$data['phone']		= $this->fans['phone']?$this->fans['phone']:'';
    	$data['user_id']	= $this->fans['id'];
    	$data['integral']	= $this->df_integral + ($sign_integral ? $this->_reward($data['continue'], $sign_integral) : 0);
    	if($this->sign_db->add($data)){
    		echo'{"success":1,"msg":"恭喜您签到成功"}';
    	}else{
    		echo'{"success":1,"msg":"暂时无法签到"}';
    	}
    }

    /*验证当天是否签到*/
    public function _todySign(){
    	$is_sign 	= 0;
    	$time 		= strtotime(date('Y-m-d')); //凌晨时间
    	$last_time 	= $this->sign_db->join('inner join '.C("DB_PREFIX").'sign_user user on user.`id` = `user_id`')->where(array('user.token'=>$this->token,'user.wecha_id'=>$this->wecha_id))->order('sign_time desc')->getField('sign_time');

    	//签到时间大于今天凌晨的时间则今天已经签到
    	if($time<$last_time){
    		$is_sign = 1;
    	}
    	return $is_sign;
    }

    /*连续签到次数对应的奖励*/
    public function _reward($sign_num, $sign_integral){
		$sign_integral;
		$score = 0;
		foreach ($sign_integral as $v) {
			if ($sign_num && $sign_num%$v['stair'] == 0) {
				$score += $v['integral'] ;
			};
		}
		return $score;
    } 

    /*连续签到次数*/
    public function _continue($sign_num){
    	//昨天时间戳
    	$startYesterday = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
    	$endYesterday	= mktime(0,0,0,date('m'),date('d'),date('Y'))-1;

    	$where['token']		= $this->token;
    	$where['wecha_id']	= $this->wecha_id;
    	$where['sign_time']		= array(array('gt',$startYesterday),array('lt',$endYesterday),'AND');
    	$time 	= $this->sign_db->where($where)->getField('sign_time');
    	if($time){
    		return $sign_num+1;
    	}else{
    		return 0;
    	}
    }
    
    /*获取指定月份起始结束时间戳*/
    function _mFristAndLast($m = "" ,$y = "") {
		if ($y == "")
			$y = date ( "Y" );
		if ($m == "")
			$m = date ( "m" );
		$m = sprintf ( "%02d", intval ( $m ) );
		$y = str_pad ( intval ( $y ), 4, "0", STR_PAD_RIGHT );
		$m = $m > 12 || $m < 1 ? 1 : $m;
		$firstday = strtotime ( $y . $m . "01000000" );
		$firstdaystr = date ( "Y-m-01", $firstday );
		$lastday = strtotime ( date ( 'Y-m-d 23:59:59', strtotime ( "$firstdaystr +1 month -1 day" ) ) );
		return array ("firstday" => $firstday, "lastday" => $lastday );
	}

}
?>
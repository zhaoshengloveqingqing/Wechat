<?php
class VoteAction extends Action{
	
	private $token = '';
	private $openid = '';
	
	private $programs = array();
	private $prizes = array();
	
	function __construct(){
		$this->token = $_GET['token'];
		$this->openid = session('openid_'.$this->token);
		$this->programs = array(
			'p1' => '节目一',		
			'p2' => '节目二',		
			'p3' => '节目三',		
			'p4' => '节目四',		
			'p5' => '节目五',		
			'p6' => '节目六',		
			'p7' => '节目七',		
			'p8' => '节目八',		
			'p9' => '节目九'	
		);
		$this->prizes = array(
			'1' => '最具台风奖',		
			'2' => '最佳默契奖',		
			'3' => '最佳创意奖',		
			'4' => '最具娱乐奖',		
			'5' => '最具文艺奖',		
			'6' => '最佳表演奖',		
			'7' => '最佳表现奖'
		);
	}
	
	public function index(){
    	if (!$this->openid) {
    		$url = urlencode(C('site_url').'/index.php/Wap/Vote/index?token='.$this->token);
    		redirect(C('site_url').'/index.php/Wap/Login/index?token='.$this->token.'&url='.$url);
    	}
		$vote_item = M('vote_item')->join(' inner join '.C('DB_PREFIX').'wecha_user user on user.id = wechat_user_id')
					->where(array('user.token'=>$this->token, 'user.wecha_id'=>$this->openid))
					->find();
		if ($vote_item) {
			redirect(C('site_url').'/index.php/Wap/Vote/vote_result?token='.$this->token.'&openid='.$this->openid);
		}else{
	    	$this->assign('openid', $this->openid);
	    	$this->assign('token', $this->token);
	    	$this->display();
		}
    }
    
    function save_vote() {
    	$openid = $this->openid ? $this->openid : $_GET['openid'];
    	$user = M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$openid))->find();
		
    	
    	$vote_item = M('vote_item')->join(' inner join '.C('DB_PREFIX').'wecha_user user on user.id = wechat_user_id')
					->where(array('user.token'=>$this->token, 'user.wecha_id'=>$openid))
					->find();
		if (!$vote_item) {
	    	$data = array(); 
			$time = strtotime(date('Y-m-d H:i:s'));
	    	foreach ($_POST as $key=>$v) {
	    		if (preg_match('/^p(\d+)$/', $key)) {
	    			$item['wechat_user_id'] = $user['id'];
	    			$item['category'] = $key;
	    			$item['item'] = $v;
	    			$item['create_time'] = $time;
		    		$data[] = $item;
	    		};
	    	}
	    	if ($data) {
		    	M('vote_item')->addAll($data);
	    	}
		}
    	redirect(C('site_url').'/index.php/Wap/Vote/vote_result?token='.$this->token.'&openid='.$openid);
    }
    
    function vote_result(){
    	if (!$this->openid) {
    		$url = urlencode(C('site_url').'/index.php/Wap/Vote/index?token='.$this->token);
    		redirect(C('site_url').'/index.php/Wap/Login/index?token='.$this->token.'&url='.$url);
    	}else{
	    	$vote_item = M('vote_item')->join(' inner join '.C('DB_PREFIX').'wecha_user user on user.id = wechat_user_id')
						->where(array('user.token'=>$this->token, 'user.wecha_id'=>$this->openid))
						->find();
			if (!$vote_item) {
				$url = urlencode(C('site_url').'/index.php/Wap/Vote/index?token='.$this->token);
    			redirect(C('site_url').'/index.php/Wap/Login/index?token='.$this->token.'&url='.$url);
			}
    	}
    	$sql = 'SELECT `item`, `category`, COUNT(`item`) AS vote_num
			FROM  '.C('DB_PREFIX').'vote_item  
			GROUP BY `category`, `item` ORDER BY `item`, `category`';
    	$model = new Model();
    	$res = $model->query($sql);
    	$vote = array();
    	if ($res) {
	    	$msg = array();
	    	foreach ($res as $v) {
	    		$msg[$v['item']][$v['category']] = $v['vote_num'];
	    	}
	    	foreach ($msg as $key=>$item) {
	    		foreach (array_keys($this->programs) as $program) {
	    			$num = $item[$program] ? $item[$program] : 0;
	    			$vote[$key][$this->programs[$program]] = $num;
	    		}
	    	}
    	}
    	$color = array('red', 'macarons', 'gray', 'blue', 'gray', 'blue', 'green');
    	$this->assign('vote', $vote);
    	$this->assign('color', $color);
    	$this->assign('prizes', $this->prizes);
    	$this->assign('programs', $this->programs);
    	$this->display('result');
    }
}
?>
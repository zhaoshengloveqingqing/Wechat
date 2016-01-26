<?php
class ReservationAction extends UserAction{
	
	private $pagesize = 0;
	public function _initialize() {
		parent::_initialize();
		$this->reservation = M('reservation');
		$this->course = M('course');
		$this->token = session('token');
		$this->pagesize = 20;
	}
	
	//课程预约管理
	public function index(){
		$where['token'] = $this->token;
		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
		if ($key) {
			$where['username'] = array('like',"%$key%");
		}
		$count = $this->reservation->where($where)->count();
		$page = new Page($count, $this->pagesize);
		if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		$info = $this->reservation->where($where)->order('update_time desc')->limit($page->firstRow.','.$page->listRows)->select();
		
		$course = $this->course->where(array('token'=>$this->token))->order('sort')->select();
		if ($course) {
			$res = array();
			foreach ($course as $v) {
				$res[$v['id']] = $v['course'];
			}
			$this->assign('res_course',$res);
		}
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display('course/reservation');
	}
	//删除课程预约
	function del(){
		$this->reservation->where(array('id'=>$this->_get('id')))->delete();
		$this->success('删除成功',U('Reservation/index'));
	}
	//修改课程预约
	function edit(){
		if(IS_POST){
			$enroll_course = isset($_POST['enroll_course']) ? implode(',', $_POST['enroll_course']) : '';
    		if (!$enroll_course) {
    			$this->error('请选择课程');
    		}
    		$data = array(
    			'username' => $this->_post('username') ? $this->_post('username') : '',
    			'token' => $this->token,
    			'sex' => $this->_post('sex'),
    			'degree' => $this->_post('degree') ? $this->_post('degree') : '',
    			'phone' => $this->_post('phone') ? $this->_post('phone') : '',
    			'company' => $this->_post('company') ? $this->_post('company') : '',
    			'position' => $this->_post('position') ? $this->_post('position') : '',
    			'total_assets' => $this->_post('total_assets') ? $this->_post('total_assets') : '',
    			'email' => $this->_post('email') ? $this->_post('email') : '',
    			'qq' => $this->_post('qq') ? $this->_post('qq') : '',
    			'access_from' => $this->_post('access_from') ? $this->_post('access_from') : 0,
    			'enroll_course' => $enroll_course,
    			'update_time' => time()
    		);
    		$info = $this->reservation->where(array('id'=>$this->_get('id')))->save($data);
    		if ($info){
    			 $this->success('修改成功');
    		}else{
    			$this->success('修改失败');
    		}
		}else{
			$info = $this->reservation->where(array('id'=>$this->_get('id')))->find();
			$course = $this->course->where(array('token'=>$this->token))->order('sort')->select();
			$this->assign('info', $info);
			$this->assign('course', $course);
			$this->display('course/reservation_modify');	
		}
	}
	
	//课程信息管理
	function course(){
		$where = array('token'=>$this->token);
		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
		if ($key) {
			$where['course'] = array('like',"%$key%");
		}
		$count = $this->course->where($where)->count();
		$page = new Page($count, $this->pagesize);
		if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		$course = $this->course->where($where)->order('sort')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page', $page->show());
		$this->assign('course', $course);
		$this->display('course/course');
	}
	//修改课程信息
	function course_modify(){
		if (IS_POST) {
			$name = $this->_post('course', 'htmlspecialchars, trim');
			$sort = $this->_post('sort', 'trim', 0);
			if (empty($name)) {
				$this->error('请输入课程名', U('Reservation/course'));
			}
			if ($sort && !preg_match('/^\d+$/', $sort)) {
				$this->error('请输入数字');
			}
			if (empty($_POST['course_id'])) {
				$data = array(
					'course'=>$name,			
					'token'=>$this->token,	
				    'sort' => $sort,
					'create_time'=>time()			
				);
				$bak = $this->course->add($data);
			}else{
				$data = array(
					'course'=>$name,
					'sort' => $sort
				);
				$bak = $this->course->where(array('id'=>$_POST['course_id']))->save($data);
			}
			if ($bak) {
				$this->success('保存成功', U('Reservation/course'));
			}else{
				$this->error('保存失败', U('Reservation/course'));
			}
		}else{
			if (isset($_GET['course_id'])) {
				$course = $this->course->where(array('id'=>$_GET['course_id'], 'token'=>$this->token))->find();
				if (empty($course)) {
					$this->error('该记录不存在', U('Reservation/course'));
				}
				$this->assign('res_course', $course);
			}
			$this->display('course/course_modify');
		}
	}
	
	//删除课程信息
	//FIND_IN_SET('1', '1,2,3,4')返回'1'所在的位置，如果没有为0
	function course_del() {
		$info = $this->reservation->where('token = \''.$this->token.'\' AND FIND_IN_SET(\''.$this->_get('course_id').'\', `enroll_course`)')->find();
		if ($info) {
			$this->error('当前课程已被使用，不能删除');
		}
		$this->course->where(array('id'=>$this->_get('course_id')))->delete();
		$this->success('删除成功',U('Reservation/course'));
	}
	
	
	
}
?>
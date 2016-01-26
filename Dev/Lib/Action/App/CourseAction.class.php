<?php
header("Content-Type: text/html; charset=utf-8");
class CourseAction extends Action{
    private $token = '';
    public function add(){
    	$this->token = $_GET['token'];
		$this->reservation = M('reservation');
		$this->course = M('course');
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
    			'create_time' => time(),
    			'update_time' => time()
    		);
    		$info = $this->reservation->add($data);
    		if ($info){
    			 $this->success('预约成功');
    		}else{
    			$this->success('预约失败');
    		}
    	}else{
    		$course = $this->course->where(array('token'=>$this->token))->order('sort')->select();
			$this->assign('course', $course);
    		$this->display();
    	}
    }
}
?>

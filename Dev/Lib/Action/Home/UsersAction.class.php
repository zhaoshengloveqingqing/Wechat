<?php
class UsersAction extends BaseAction{
	public function index(){
		header("Location: /");
	}

	public function checklogin(){
		$db = D('Users');
                $username = $this->_post('username','trim');
		$where['username']= $username;
		$user = $db->where($where)->find();
		
		// if($db->create()==false)
			// $this->error($db->getError());
		$pwd=$this->_post('password','trim,md5');

		if ($user && ($pwd === $user['password'])) {
			if($user['status'] == 0){
                            $db->where($where)->setField('lasttime', time());
                            $this->assign('username',$username);
                            $this->display('Index:logintip');
                            exit;
				//$this->error('请联系在线客户，为你人工审核帐号');exit;
			}
			session('uid',$user['id']);
			session('gid',$user['gid']);
			session('uname',$user['username']);
			session('diynum',$user['diynum']);
			session('connectnum',$user['connectnum']);
			session('activitynum',$user['activitynum']);
			//session('viptime',$user['viptime']);
			$info=M('user_group')->find($user['gid']);
			session('gname',$info['name']);

            //更新回复数目
			/*$tt = getdate();
			if ($tt['mday'] === 1) {
				$data['id']=$user['id'];
				$data['imgcount'] = 0;
				$data['textcount'] = 0;
				$data['musiccount'] = 0;
				$data['activitynum'] = 0;
				$db->save($data);
			}*/
                        //更新上次登录时间
                        $db->where($where)->setField('lasttime', time());
			$this->success('登录成功',U('User/Index/index'));
		} else {
			$this->error('帐号密码错误',U('Index/login'));
		}
	}

	public function register() {
            

        C('TOKEN_ON',false);
	    $data['username'] = $_POST['username'];
	    $data['password'] = $_POST['password'];
	    $data['pwd'] = $_POST['password'];
	    $data['repassword'] = $_POST['repassword'];
	    $data['company'] = $_POST['company'];
	    $data['industry'] = $_POST['industry'];
	    $data['pos'] = $_POST['pos'];
	    $data['tel'] = $_POST['tel'];
	    $data['name'] = $_POST['name'];
	    $data['city'] = $_POST['city'];
            $data['status'] = 1;
	    
		$pageData = array();
		$db = D('Users');
		if ($db->create($data)) {
			$id = $db->add();
			$pageData['status'] = 0;
                        
                        require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');
                        $websiteUserFuncManager = new WebsiteUserFuncManager($id);
                        $websiteUserFuncManager->openDefaultFuncGroups();
		} else {
			$pageData['status'] = 1;
			$pageData['error'] = $db->getError();
		}
		echo json_encode($pageData);
	}
	
	public function checkreg(){
		$info = M('User_group')->find(1);

		$db = D('Users');
		if ($db->create()) {
			$id = $db->add();
			if ($id) {
				if(C('ischeckuser')){
					$this->success('注册成功,请联系在线客服审核帐号',U('User/Index/index'));exit;
				}
				session('uid',$id);
				session('gid',1);
				session('uname',$_POST['username']);
				session('diynum',0);
				session('connectnum',0);
				session('activitynum',0);
				session('gname',$info['name']);
				
				// $smtpserver = C('email_server'); 
				// $port = C('email_port');
				// $smtpuser = C('email_user');
				// $smtppwd = C('email_pwd');
				// $mailtype = "TXT";
				// $sender = C('email_user');
				// $smtp = new Smtp($smtpserver,$port,true,$smtpuser,$smtppwd,$sender); 
				// $to = $list['email']; 
				// $subject = C('reg_email_title');
				// $code = C('site_url').U('User/Index/checkFetchPass?uid='.$list['id'].'&code='.md5($list['id'].$list['password'].$list['email']));
				// $fetchcontent = C('reg_email_content');
				// $fetchcontent = str_replace('{username}',$where['username'],$fetchcontent);
				// $fetchcontent = str_replace('{time}',date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']),$fetchcontent);
				// $fetchcontent = str_replace('{code}',$code,$fetchcontent);
				// $body=$fetchcontent;
				//$body = iconv('UTF-8','gb2312',$fetchcontent);
				// $send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype);
					
				$this->success('注册成功',U('User/Index/index'));
			}else{
				$this->error('注册失败',U('Index/reg'));
			}
		}else{
			$this->error($db->getError(),U('Index/reg'));
		}
	}

	public function checkpwd(){

		$where['username']=$this->_post('username');
		$where['email']=$this->_post('email');
		$db=D('Users');
		$list=$db->where($where)->find();
		if($list==false) $this->error('邮箱和帐号不正确',U('Index/regpwd'));
		
		$smtpserver = C('email_server'); 
		$port = C('email_port');
		$smtpuser = C('email_user');
		$smtppwd = C('email_pwd');
		$mailtype = "TXT";
		$sender = C('email_user');
		$smtp = new Smtp($smtpserver,$port,true,$smtpuser,$smtppwd,$sender); 
		$to = $list['email']; 
		$subject = C('pwd_email_title');
		$code = C('site_url').U('Index/resetpwd',array('uid'=>$list['id'],'code'=>md5($list['id'].$list['password'].$list['email']),'resettime'=>time()));
		$fetchcontent = C('pwd_email_content');
		$fetchcontent = str_replace('{username}',$where['username'],$fetchcontent);
		$fetchcontent = str_replace('{time}',date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']),$fetchcontent);
		$fetchcontent = str_replace('{code}',$code,$fetchcontent);
		$body=$fetchcontent;
		//$body = iconv('UTF-8','gb2312',$fetchcontent);
		$send=$smtp->sendmail($to,$sender,$subject,$body,$mailtype);
		$this->success('请访问你的邮箱 '.$list['email'].' 验证邮箱后登录!<br/>');
		
	}
	
	public function resetpwd(){
		$where['id']=$this->_post('uid','intval');
		$where['password']=$this->_post('password','md5');
		$where['pwd']=$this->_post('password');
		if(M('Users')->save($where)){
			$this->success('修改成功，请登录！',U('Index/login'));
		}else{
			$this->error('密码修改失败！',U('Index/index'));
		}
	}
	
}

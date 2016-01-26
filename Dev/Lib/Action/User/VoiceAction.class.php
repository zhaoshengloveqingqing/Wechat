<?php
/**
 *语音回复
**/
class VoiceAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'kefu';
		parent::checkOpenedFunction();
    }

	public function index(){
	    $uid = session('uid');
		$token = session('token');

		$db = D('voice');
		$where['uid'] = $uid; 
		$where['token'] = $token;
		$where['function'] = 'kefu';
		$where['status'] = 1;
		$count = $db->where($where)->count();
		$page = new Page($count,25);
		$this->assign('page',$page->show());

		$info = $db->where($where)->limit($page->firstRow.','.$page->listRows)->select();

		$this->assign('info',$info);

		$this->display();
	}
	public function add(){
		$this->display('edit');
	}
	public function edit(){
		$voice_id = $this->_get('id','intval');
		$uid = session('uid');
		$token = session('token');

		$db = D('voice');
		$where['uid'] = $uid; 
		$where['token'] = $token;
		$where['function'] = 'kefu';
		$where['id'] = $voice_id;
		$where['status'] = 1;
		$info = $db->where($where)->find(); 
		$this->assign('info',$info);

		$this->display();
	}
	public function del(){
		$voice_id = $this->_get('id','intval');
		$uid = session('uid');
		$token = session('token');

		$where['id'] = $voice_id;
		$where['uid'] = $uid; 
		$where['token'] = $token;
		$data['status'] = 0;
		$ret = M('voice')->where($where)->save($data);
		if ($ret >= 0) {
			M('Keyword')->where(array('pid'=>$voice_id,'token'=>$token, 'function'=>'kefu', 'module'=>'voice'))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		} else {
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}

	public function addNewVoice() {
	    $voice = M('voice');
		$data['token'] = session('token');
		$data['uid'] = session('uid');
		$data['uname'] = session('uname');
		$data['keyword'] = $_POST['keyword'];
		$data['title'] = $_POST['title'];
		$data['description'] = $_POST['description'];
		$data['musicurl'] = $_POST['musicurl'];
		$data['hqmusicurl'] = $_POST['hqmusicurl'];
		$data['createtime'] = time();
		$data['updatetime'] = $data['createtime'];
		$data['function'] = $this->function;
		$data['status'] = 1; 
		
		if (empty($data['keyword'])) {
        	$this->error('请填写关键词');
        }
        if (empty($data['title'])) {
        	$this->error('请填写音乐标题');
        }
        if (empty($data['musicurl'])) {
        	$this->error('请填写音乐链接');
        }
        if (empty($data['hqmusicurl'])) {
        	$this->error('请填写高品质音乐链接');
        }
        $voice_id = $voice->add($data);

		if ($voice_id) {
            $keywords = explode(' ',  $_POST['keyword']);
   			$kwds_db = M('keyword');
    		$kwd_data['uid'] = session('uid');
	    	$kwd_data['token'] = session('token');
    	    $kwd_data['module'] = 'voice';
		    $kwd_data['function'] = $this->function;
   			$kwd_data['pid'] = $voice_id;
    	    foreach($keywords as $vo) {
	    	    $kwd_data['keyword'] = $vo	;    
		    	$kwds_db->add($kwd_data);
   			}
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		} else {
			$this->error('操作失败',U(MODULE_NAME.'/add'));
		}
	}


	public function upsave(){
		$voice_id = $this->_post('id','intval');
		$where['id'] = $voice_id;
		$where['uid'] = session('uid');
		$where['status'] = 1;

	    $voice = M('voice');
		$data['keyword'] = $_POST['keyword'];
		$data['title'] = $_POST['title'];
		$data['description'] = $_POST['description'];
		$data['musicurl'] = $_POST['musicurl'];
		$data['hqmusicurl'] = $_POST['hqmusicurl'];
		$data['updatetime'] = time();
		
		if (empty($data['keyword'])) {
        	$this->error('请填写关键词');
        }
        if (empty($data['title'])) {
        	$this->error('请填写音乐标题');
        }
        if (empty($data['musicurl'])) {
        	$this->error('请填写音乐链接');
        }
        if (empty($data['hqmusicurl'])) {
        	$this->error('请填写高品质音乐链接');
        }
		
        $ret = $voice->where($where)->save($data);

	    if ($ret == true) {
        	$kwds_db = M('keyword');
        	$kwds_db->where(array('pid' => $voice_id,'token'=>  session('token'), 'function'=>'kefu', 'module'=>'voice'))->delete();

		    $kwd_data['uid'] = session('uid');
    		$kwd_data['token'] = session('token');
	    	$kwd_data['module'] = 'voice';
    	    $kwd_data['function'] = $this->function;
    		$kwd_data['pid'] = $voice_id;
            $keywords = explode(' ',  $_POST['keyword']);
		    foreach($keywords as $vo) {
		        $kwd_data['keyword'] = $vo;
    			$kwds_db->add($kwd_data);
	    	}
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		} else {
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
}
?>

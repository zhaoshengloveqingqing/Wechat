<?php
/**
 *文本回复
**/
class TextAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'kefu';
		parent::checkOpenedFunction();
    }

	public function index(){
	    $uid = session('uid');
		$token = session('token');
		$db = D('text');
		$where['uid'] = $uid;
		$where['token'] = $token;
		$where['function'] = 'kefu';
		$where['status'] = 1;
		$where['keyword'] = array('neq','9367a975f19a06750b67f719f4f08ceb');
		$count = $db->where($where)->count();
		$page = new Page($count,25);
		$this->assign('page',$page->show());


		$info = $db->where($where)->limit($page->firstRow, $page->listRows)->select(); 
		$this->assign('info',$info);

		$this->display();
	}
	public function add(){
		$this->display('edit');
	}
	public function edit(){
		$text_id = $this->_get('id','intval');
		$uid = session('uid');
		$token = session('token');

		$db = D('text');
		$where['uid'] = $uid;
		$where['token'] = $token;
		$where['function'] = 'kefu';
		$where['status'] = 1;
		$where['id'] = $text_id;
		$info = $db->where($where)->find(); 

		$this->assign('info',$info);

		$this->display();
	}


	public function del(){
		$text_id = $this->_get('id','intval');
		$uid = session('uid');
		$token = session('token');

		$where['id'] = $text_id;
		$where['uid'] = $uid; 
		$where['token'] = $token;
		$data['status'] = 0;
		$ret = M('text')->where($where)->save($data);
		if ($ret >= 0) {
			M('Keyword')->where(array('pid'=>$text_id,'token'=>$token,'function'=>'kefu','module'=>'text'))->delete();
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		} else {
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}


	public function addNewText(){
	    $text = M('text');
		$data['token'] = session('token');
		$data['uid'] = session('uid');
		$data['uname'] = session('uname');
		$data['keyword'] = $_POST['keyword'];
		$data['type'] = $_POST['type'];
		$data['text'] = $_POST['text'];
		$data['status'] = 1;
		$data['function'] = $this->function;
		$data['createtime'] = time();
		$data['updatetime'] = $data['createtime'];
        
        if (empty($data['keyword'])) {
        	$this->error('请填写关键词');
        }
        if (empty($data['type'])) {
        	$this->error('请选择匹配类型');
        }
        if (empty($data['text'])) {
        	$this->error('请填写内容或简介');
        }
		$text_id = $text->add($data);

		if ($text_id) {
            $keywords = explode(' ',  $_POST['keyword']);
    		$kwds_db = M('keyword');
	    	$kwd_data['uid'] = session('uid');
		  	$kwd_data['token'] = session('token');
		    $kwd_data['module'] = 'text';
		    $kwd_data['type'] = $_POST['type'];
		    $kwd_data['function'] = $this->function;
    		$kwd_data['pid'] = $text_id;
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
        $text_id = $this->_post('id','intval');

		$where['id'] = $text_id; 
		$where['uid'] = session('uid');
		$where['status'] = 1;

	    $text = M('text');
		$data['keyword'] = $_POST['keyword'];
		$data['type'] = $_POST['type'];
		$data['text'] = $_POST['text'];
		$data['updatetime'] = time();
        $ret = $text->where($where)->save($data);

	    if ($ret == true) {
		    $kwds_db = M('keyword');
    		$kwds_db->where(array('pid' => $text_id, 'token'=>session('token') ,'function'=>'kefu','module'=>'text'))->delete();
 
    		$kwd_data['uid'] = session('uid');
	    	$kwd_data['token'] = session('token');
		    $kwd_data['module'] = 'text';
		    $kwd_data['type'] = $_POST['type'];
    	    $kwd_data['function'] = $this->function;
	    	$kwd_data['pid'] = $text_id;
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

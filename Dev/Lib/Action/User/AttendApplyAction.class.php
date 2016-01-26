<?php
 header("Content-Type: text/html; charset=utf-8");
 
class AttendApplyAction extends UserAction{
	
	public function _initialize() {
        parent::_initialize();
		$this->token = $_SESSION['token']; 
		$this->attend_apply = M('attend_apply');
		$this->pagesize = 20;
    }
    
    function index() {
    	import('ORG.Util.Page');
   		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
    	if ($key) {
	        $count = $this->attend_apply->where(array('name'=>array('like', '%'.$key.'%')))->count();
    	}else{
	         $count = $this->attend_apply->count();
    	}
        $page = new Page($count, $this->pagesize);
	    if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$list = $this->attend_apply->where(array('name'=>array('like',  '%'.$key.'%')))->order('create_time desc')->limit($page->firstRow.','.$page->listRows)->select();
			
			$this->assign('search', $key);
		}else{
			$list = $this->attend_apply->order('create_time desc')->limit($page->firstRow.','.$page->listRows)->select();
		}
		$show = $page->show();
		$this->assign('page', $show);
		$this->assign('list', $list);
    	$this->display('apply/index');
    }
    
    
	function modify(){
		if(IS_POST){
			$time = date('Y-m-d H:i:s',  time());
    		$data = array(
    			'name' => $this->_post('name') ? $this->_post('name') : '',
    			'token' => $this->token,
    			'contact' => $this->_post('contact') ? $this->_post('contact') : '',
    			'attend_num' => $this->_post('attend_num') ? $this->_post('attend_num') : '',
    			'room_type' => $this->_post('room_type') ? $this->_post('room_type') : '0',
    			'checkin_time' => $this->_post('checkin_time') ? $this->_post('checkin_time') : '',
    			'checkout_time' => $this->_post('checkout_time') ? $this->_post('checkout_time') : '',
    			'update_time' => $time
    		);
    		$apply_id = $this->_post('apply_id');
    		if (!$apply_id || !$this->attend_apply->where(array('id'=>$apply_id))->find() ) {
    			$data['create_time'] = $time;
	    		$info = $this->attend_apply->data($data)->add();
    		}else{
    			$info = $this->attend_apply->where(array('id'=>$apply_id))->save($data);
    		}
			if ($info){
    			 $this->success('修改成功', U('AttendApply/index'));
    		}else{
    			$this->success('修改失败');
    		}
		}else{
			$apply_id = $this->_get('apply_id');
			if(isset($apply_id)){
				$info = $this->attend_apply->where(array('id'=>$apply_id))->find();
				$this->assign('apply', $info);
			}
			$this->display('apply/modify');	
		}
	}
	
	function del(){
		$this->attend_apply->where(array('id'=>$this->_get('apply_id')))->delete();
		$this->success('删除成功',U('AttendApply/index'));
	}
	
    
}
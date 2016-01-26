<?php
/**
 *关注回复
**/
class AreplyAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		parent::checkOpenedFunction('kefu');
    }

	public function index(){

		$db = D('Areply');
		$where['uid'] = $_SESSION['uid'];
		$where['token'] = $_SESSION['token'];
		$res = $db->where($where)->find();
		$this->assign('areply',$res);

        if ($res['ctype'] == 'text') {
		    $where['id'] = $res['cid'];
		    $where['status'] = 1;
		    $text = M('text')->where($where)->find();
		    $this->assign('text', $text);
		}


        $where = array();
		$where['uid'] = $_SESSION['uid'];
		$where['token'] = $_SESSION['token'];
		$where['function'] = 'kefu';
		$where['module'] = 'img';

        $keywords_db = M('keyword')->where($where)->select();
		$keywords = array();
		foreach($keywords_db as $kwd) {
		    array_push($keywords, $kwd['keyword']);
		}

		$this->assign('keywords',$keywords);
		$this->display();
	}

	public function addOrUpdate(){
		C('TOKEN_ON',false);

	    $resp_type = $_POST['respType'];
        if (!isset($_POST['respType'])
		        || ($resp_type == 1 && !isset($_POST['content']))
				|| ($resp_type == 2 && !isset($_POST['keyword']))) {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
		} 

		$db = D('Areply');
		$where['uid'] = $_SESSION['uid'];
		$where['token'] = $_SESSION['token'];
		$res = $db->where($where)->find();

		if ($res == null) 
		{
		    //新增关注回复
		    $data['token'] 		= session('token');
	        $data['uid'] 		= session('uid');
          	$data['uname'] 		= session('uname');
           	$data['status'] 	= 1;
           	$data['function'] 	= 'kefu';
           	$data['createtime'] = time();
           	$data['updatetime'] = $data['createtime'];
			if ($resp_type == 1) {
             	$data['keyword'] = '9367a975f19a06750b67f719f4f08ceb' ;// md5 subscriber
           		$data['type'] = 1;
            	$data['text'] = $_POST['content'];
                $text_id = M('text')->add($data);
	            if ($text_id) {

    		        $kwds_db = M('keyword');
        	    	$kwd_data['uid'] = session('uid');
		         	$kwd_data['token'] = session('token');
        		    $kwd_data['module'] = 'text';
		            $kwd_data['type'] = 1; 
        		    $kwd_data['function'] = 'kefu';
    	        	$kwd_data['pid'] = $text_id;
	          	    $kwd_data['keyword'] = '9367a975f19a06750b67f719f4f08ceb';    
		        	$kwds_db->add($kwd_data);

				    $reply_data = array();
		            $reply_data ['token'] = session('token');
	                $reply_data ['uid'] = session('uid');
           			$reply_data ['cid'] = $text_id;
		            $reply_data ['ctype'] = 'text';
               		$reply_data ['createtime'] = time();
               		$reply_data ['updatetime'] = $data['createtime'];

		            $id = $db->data($reply_data )->add();
             		if ($id) {
			           	$this->success('发布成功',U('Areply/index'));
             		} else {
			           	$this->error('发布失败',U('Areply/index'));
              		}
	           	} else {
             		$this->error('操作失败',U(MODULE_NAME.'/index'));
             	}
	        } else {
    			$data['ctype'] = 'img';
				$data['keyword']=$this->_post('keyword');

			    $id = $db->data($data)->add();
     			if ($id) {
	    			$this->success('发布成功',U('Areply/index'));
		    	}else{
			        $this->error('发布失败',U('Areply/index'));
		     	}
			}


		} else {
		    //更新 判断当前是否文本消息，
			if ($resp_type == 1 && $res['ctype'] == 'text') {
			    //之前是文本，更新后还是文本，只需要更新文本
           	    $data['updatetime'] = time();
            	$data['text'] = $_POST['content'];
			    $where['id']=$res['cid'];
			    if (M('text')->where($where)->save($data)) {
				    $this->success('更新成功',U('Areply/index'));
    			}else{
	    			$this->error('更新失败',U('Areply/index'));
		    	}
			} else if  ($resp_type == 2 && $res['ctype'] == 'img') {
			    //之前是图文，更新后还是图文，只需要更新图文
		        $where = array();
		        $where['uid'] = $_SESSION['uid'];
		        $where['token'] = $_SESSION['token'];
    			$where['ctype'] = 'img';
				$data['keyword']=$this->_post('keyword');
           		$data['updatetime'] = time();

			    if($db->where($where)->save($data)){
				    $this->success('更新成功',U('Areply/index'));
    			}else{
	    			$this->error('更新失败',U('Areply/index'));
		    	}
			} else if  ($resp_type == 1 && $res['ctype'] == 'img') {
			    //之前是图文,插入文本，更新回复内容,更新关键词
	            $data['token'] = session('token');
              	$data['uid'] = session('uid');
           		$data['uname'] = session('uname');
             	$data['keyword'] = '9367a975f19a06750b67f719f4f08ceb' ;
           		$data['type'] = 1;
            	$data['text'] = $_POST['content'];
           		$data['status'] = 1;
           		$data['function'] = 'kefu';
           		$data['createtime'] = time();
           		$data['updatetime'] = $data['createtime'];
                $text_id = M('text')->add($data);
                if ($text_id) {
                	$kwds_db = M('keyword');
                	$kwd_where['token'] = session('token');
                	$kwd_where['keyword'] = '9367a975f19a06750b67f719f4f08ceb'	;  
                	$kwd_where['function'] = 'kefu';
                	$kwd_data['type'] = 1; 
                	$kwds_db->where($kwd_where)->delete();

        	    	$kwd_data['uid'] = session('uid');
		         	$kwd_data['token'] = session('token');
        		    $kwd_data['module'] = 'text';
		            $kwd_data['type'] = 1; 
        		    $kwd_data['function'] = 'kefu';
    	        	$kwd_data['pid'] = $text_id;
	          	    $kwd_data['keyword'] = '9367a975f19a06750b67f719f4f08ceb'	;    
		        	$kwds_db->add($kwd_data);

				    $reply_data = array();
           			$reply_data ['cid'] = $text_id;
		            $reply_data ['ctype'] = 'text';
               		$reply_data ['updatetime'] = $data['createtime'];

		            $where = array();
    		        $where['uid'] = $_SESSION['uid'];
	    	        $where['token'] = $_SESSION['token'];

		            $id = $db->where($where)->save($reply_data);
             		if ($id) {
			           	$this->success('发布成功',U('Areply/index'));
             		} else {
			            $this->error('发布失败',U('Areply/index'));
              		}
	            } else {
             		$this->error('操作失败',U(MODULE_NAME.'/index'));
             	}
			} else if  ($resp_type == 2 && $res['ctype'] == 'text') {
			    //删除文本，更新内容，删除关键词
				$kwds_db = M('keyword');
                $kwd_where['token'] = session('token');
                $kwd_where['keyword'] = '9367a975f19a06750b67f719f4f08ceb'	;  
                $kwd_where['function'] = 'kefu';
                $kwd_data['type'] = 1; 
                $kwds_db->where($kwd_where)->delete();

		        $where = array();
    		    $where['uid'] = $_SESSION['uid'];
	    	    $where['token'] = $_SESSION['token'];
             	$where['id'] = $res['cid']; 
                M('text')->where($where)->save(array('status'=>0));
			    M('Keyword')->where(array('pid'=>$res['cid'],'token'=>$_SESSION['token'],'function'=>'kefu','module'=>'img'))->delete();
		        $where = array();
        	    $where['uid'] = $_SESSION['uid'];
	            $where['token'] = $_SESSION['token'];

			    $reply_data = array();
    		    $reply_data['ctype'] = 'img';
           	 	$reply_data['updatetime'] = time();
			    $reply_data['keyword']=$this->_post('keyword');

		       	$id = $db->where($where)->save($reply_data);
          		if ($id) {
		           	$this->success('发布成功',U('Areply/index'));
           		}else{
		           	$this->error('发布失败',U('Areply/index'));
                }
	    	}
     	}
	}
}
?>

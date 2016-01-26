<?php
class HomeAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'shouye';
		parent::checkOpenedFunction('shouye');
    }

	//配置
	public function set() {
		$where['token'] = session('token');
		$where['uid'] = session('uid');
		$where['function'] = $this->function;
		$where['status'] = 1;

	    $img = M('img')->where($where)->find() ;

		if (IS_POST) {
			if ($img == false) {
			    // 添加新主页 
	      
                $data['token'] = session('token');
				$data['uid'] = session('uid');
				$data['uname'] = session('uname');
				$data['keyword'] = isset($_POST['keyword']) ? $_POST['keyword'] : '首页 home';
				$data['type'] = 1;
				$data['text'] = $_POST['text'];  //文章摘要
				$data['pic'] = $_POST['pic'];  //图片地址
				$data['showpic'] = 1; 
				$data['info'] = null;
				$data['url'] = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Index&a=index&token='.session('token');
				$data['title'] = $_POST['title'];
				$data['function'] = $this->function;
				$data['detail_display_tmpl'] = 'img_content' ;
				$data['createtime'] = time();
				$data['updatetime'] = $data['createtime'];
				$data['status'] = 1;
				$img_id = M('img')->add($data);

				if ($img_id) {
        		    $kwds_db = M('keyword');
    	        	$kwd_data['uid'] = session('uid');
	    	        $kwd_data['token'] = session('token');
		            $kwd_data['type'] = 1;
    		    	$kwd_data['module'] = 'img';
    	    		$kwd_data['function'] = 'shouye';
        	    	$kwd_data['pid'] = $img_id;
		        	$keywords = explode(' ',  $data['keyword']);
    	            foreach($keywords as $vo) {
	    	            $kwd_data['keyword'] = $vo;    
    		    	    $kwds_db->add($kwd_data);
    	    	    }
	    		    $this->success('操作成功',U(MODULE_NAME.'/set'));
				} else {
	    		    $this->error('添加失败',U(MODULE_NAME.'/set'));
				}

			} else {

	    		$nimg = M('img');
				$data['keyword'] = $_POST['keyword'];
				$data['text'] = $_POST['text'];  //文章摘要
				$data['pic'] = $_POST['pic'];
				$data['title'] = $_POST['title'];
				$data['updatetime'] = time();
		        $ret = $nimg->where($where)->save($data);

				if ($ret) {
				    $kwds_db = M('keyword');
                    $kwds_db->where(array('token'=> session('token'), 'function'=>'shouye', 'module'=>'img'))->delete();
  
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = 'shouye';
                    $kwd_data['type'] = 1;
                    $kwd_data['pid'] = $img['id'];
                    $keywords = explode(' ',  $_POST['keyword']);
                    foreach($keywords as $vo) {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
			        $this->success('操作成功',U(MODULE_NAME.'/set'));
				} else {
	    		    $this->error('保存失败',U(MODULE_NAME.'/set'));
				}
			}
		} else {
			$this->assign('home',$img);
			$this->display();
		}
	}
	
}

?>

<?php
class MembershipAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'huiyuanka';
		parent::checkOpenedFunction('huiyuanka');
    }

	//配置
	public function img() {
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
				$data['keyword'] = isset($_POST['keyword']) ? $_POST['keyword'] : '会员卡';
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
        		    /* 目前不支持用户之定义关键词，注释掉
        		    $kwds_db = M('keyword');
    	        	$kwd_data['uid'] = session('uid');
	    	        $kwd_data['token'] = session('token');
		            $kwd_data['type'] = 1;
    		    	$kwd_data['module'] = 'img';
    	    		$kwd_data['function'] = 'shouye';
        	    	$kwd_data['pid'] = 0;
		        	$keywords = array('首页','home');
    	            foreach($keywords as $vo) {
	    	            $kwd_data['keyword'] = $vo;    
    		    	    $kwds_db->add($kwd_data);
    	    	    }*/
	    		    $this->success('操作成功',U(MODULE_NAME.'/img'));
				} else {
	    		    $this->error('添加失败',U(MODULE_NAME.'/img'));
				}

			} else {

	    		$img = M('img');
				$data['keyword'] = $_POST['keyword'];
				$data['text'] = $_POST['text'];  //文章摘要
				$data['pic'] = $_POST['pic'];
				$data['title'] = $_POST['title'];
				$data['updatetime'] = time();
		        $ret = $img->where($where)->save($data);

				if ($ret) {
			        $this->success('操作成功',U(MODULE_NAME.'/img'));
				} else {
	    		    $this->error('保存失败',U(MODULE_NAME.'/img'));
				}
			}
		} else {
			$this->assign('membership',$img);
			$this->display();
		}
	}
	
}

?>

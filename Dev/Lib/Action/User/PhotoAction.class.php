<?php
class PhotoAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
		$this->function = 'xiangce';
        parent::checkOpenedFunction('xiangce');
    }

	//配置
	public function set() 
    {
        $where['token'] = session('token');
        $where['uid'] = session('uid');
        $where['function'] = $this->function;
        $where['status'] = 1;

	    $img = M('img')->where($where)->find() ;
        $this->assign('gallerytype','微相册'); 
            
		if (IS_POST) 
        {
			if ($img == false) 
            {
			    // 添加新相册 
                $data['token'] = session('token');
				$data['uid'] = session('uid');
				$data['uname'] = session('uname');
				$data['keyword'] = isset($_POST['keyword']) ? $_POST['keyword'] : '相册';
				$data['type'] = 1;
				$data['text'] = $_POST['text'];  //文章摘要
				$data['pic'] = $_POST['pic'];  //图片地址
				$data['showpic'] = 1; 
				$data['info'] = null;
				$data['url'] = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Photo&a=index&token='.session('token');
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
    	    		$kwd_data['function'] = $this->function;
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
                    $kwds_db->where(array('token'=> session('token'), 'function'=>'xiangce', 'module'=>'img'))->delete();
  
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = $this->function;
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
                        
			$this->assign('photo',$img);
			$this->display();
		}
	}
	
    public function index()
    {       
        //相册列表
        $data   = M('Photo');
        $count  = $data->where(array('token'=>$_SESSION['token'], 'status'=> array('neq',2)))->count();
        $Page   = new Page($count,12);
        $show   = $Page->show();

        $list = $data->where(array('token'=>$_SESSION['token'], 'status'=> array('neq',2)))->limit($Page->firstRow.','.$Page->listRows)->select(); 

        $this->assign('page',$show); 
        $this->assign('count',$count); 		
        $this->assign('photo',$list);
        $this->display();       
    }


    public function edit()
    {
        $data=D('Photo');

        if (IS_POST)
        {
            $this->all_save('Photo');
        }
        else
        {
            $photo = $data->where(array('token'=>session('token'),'id'=>$this->_get('id'), 'status'=> array('neq',2)))->find();
            if ($photo == false) 
            {
                $this->error('相册不存在');
            }
            else
            {
                $this->assign('photo',$photo);
            }
            $this->display();       
        }
    }

    public function list_edit()
    {
        $check = M('Photo_list')->field('id,pid')->where(array('token'=>$_SESSION['token'],'id'=>$this->_post('id'), 'status'=> array('neq',2)))->find();
        if ($check == false)
        {
            $this->error('照片不存在');
        }

        if (IS_POST)
        {
            $this->all_save('Photo_list','/index');      
        }
        else
        {
            $this->error('非法操作');
        }
    }
    
    public function list_del()
    {

        $check = M('Photo_list')->field('id,pid')->where(array('token'=>$_SESSION['token'],'id'=>$this->_get('id'), 'status'=> array('neq',2)))->find();
        if ($check == false) 
        {
            $this->error('服务器繁忙');
        }

        if(empty($_POST['edit']))
        {
            if (M('Photo_list')->where(array('id'=>$check['id']))->save(array('status'=>2)))
            {
                M('Photo')->where(array('id'=>$check['pid']))->setDec('num');
                $this->success('操作成功');
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试');
            }
        }
    }


    public function list_add()
    {
        
        $album = M('Photo')->where(array('token'=>$_SESSION['token'],'id'=>$this->_get('id'), 'status'=> array('neq',2)))->find();
        if ($album == false) 
        {
            $this->error('相册不存在');
        }

        if (IS_POST)
        //更新相册图片信息
        {            
            M('Photo')->where(array('token'=>session('token'),'id'=>$this->_get('id'), 'status'=> array('neq',2)))->setInc('num');
            $_POST['create_time'] = time();
            $this->all_insert('Photo_list');  
            $this->success('图片添加成功');			
        }
        else
        {
            $data   =M('Photo_list');
            $count  = $data->where(array('token'=>$_SESSION['token'],'pid'=>$this->_get('id'), 'status'=> array('neq',2)))->count();
            $Page   = new Page($count,12);
            $show   = $Page->show();
            $list   = $data->where(array('token'=>$_SESSION['token'],'pid'=>$this->_get('id'), 'status'=> array('neq',2)))->order('sort desc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();    
            $this->assign('page',$show);        
            $this->assign('photo',$list);
            $this->display();   
        }
        
    }

    public function add()
    {
        if(IS_POST)
        {            
            /*$kwds_db            = M('keyword');
            $kwd_data['uid']    = session('uid');
            $kwd_data['token']  = session('token');
            $kwd_data['function'] = 'xiangce';
            $kwd_data['keyword'] = '相册';    
            $kwd = $kwds_db->where($kwd_data)->find();
            if ($kwd == false) 
            //默认情况下通过 ，开通功能插入关键词
            {
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'img';
                $kwd_data['pid'] = 0;
                $kwds_db->add($kwd_data);
            }*/
            $this->all_insert('Photo','/index');          
        }
        else
        {
            $this->display();   
        }
        
    }
    public function del()
    {
        $album_id   = $this->_get('id');
        $where      = array('token'=>$_SESSION['token'],'id'=>$album_id, 'status'=> array('neq',2));
        $ret        = $photo = M('Photo')->where($where)->save(array('status'=>2));
        if ($ret !== false && $ret > 0)
        {
            M('Photo_list')->where(array('pid'=>$album_id,'token'=>$_SESSION['token']))->save(array('status'=>2));
            $this->success('操作成功',U(MODULE_NAME.'/index'));   
        }
    }


}


?>

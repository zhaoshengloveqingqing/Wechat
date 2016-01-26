
<?php
class EstateAction extends UserAction
{

    protected function _initialize()
    {
        $this->function = 'fangchan';
        $this->token = $_SESSION['token']; 
        
        parent::_initialize();
        parent::checkOpenedFunction();
    }

    public function index() 
    {
        $house_db        = M('Estate');
        $where          = array('token'=>$this->token);
        $house       = $house_db->where($where)->find();
		
        if(IS_POST)
        {
		    if (empty($house)) {
			    $data['token']      = $this->token;
			
			    $data['keyword']    = $this->_post('keyword');
				$data['title']      = $this->_post('title');
				$data['tel']        = $this->_post('tel');
				$data['head_pic_url']    = $this->_post('head_pic_url');
				$data['msg_pic_url']     = $this->_post('msg_pic_url'); 
				$data['banner']     = $this->_post('banner');
				$data['house_banner']     = $this->_post('house_banner');	
				$data['description']      = $this->_post('description'); 
				$data['longtitude'] = $this->_post('longtitude');
				$data['latitude']   = $this->_post('latitude');
				$data['address']   = $this->_post('address');
				$data['traffic_desc']   = $this->_post('traffic_desc');
				$data['photo_id']   = $this->_post('photo_id');
				
				$ret_id = $house_db->add($data);

				if($ret_id)
				{
					$data1['pid']      = $ret_id;
					$data1['module']   = 'house';
					$data1['token']    = $this->token;
					$data1['function'] = $this->function;
					$data1['keyword']     = $_POST['keyword'];
					$data1['type'] = 1;
					M('Keyword')->add($data1);
					$this->success('保存成功',U('Estate/index'));
				}  else
				{
					$this->error($house_db->getError());
				}
			} else {
				$data['keyword']    = $this->_post('keyword');
				$data['title']      = $this->_post('title');
				$data['tel']        = $this->_post('tel');
				$data['head_pic_url']    = $this->_post('head_pic_url');
				$data['msg_pic_url']     = $this->_post('msg_pic_url'); 
				$data['banner']     = $this->_post('banner');
				$data['house_banner']     = $this->_post('house_banner');	
				$data['description']      = $this->_post('description'); 
				$data['longtitude'] = $this->_post('longtitude');
				$data['latitude']   = $this->_post('latitude');
				$data['address']   = $this->_post('address');
				$data['traffic_desc']   = $this->_post('traffic_desc');
				$data['photo_id']   = $this->_post('photo_id');
				
				$ret = $house_db->where($where)->save($data);

				if($ret)
				{
				    $data1['pid']      = $house['id'];
					$data1['module']   = 'house';
					$data1['token']    = $this->token;
					$data1['function'] = $this->function;
					$da['keyword']     = $_POST['keyword'];
					$ret = M('Keyword')->where($data1)->save($da);
                    if(!$ret){
                        $data1['type'] = 1;
                        $data1['keyword'] = $_POST['keyword'];
                        M('keyword')->add($data1);
                    }
					$this->success('修改成功',U('Estate/index'));
				} else
				{
					$this->error($house_db->getError());
				}
			}
        } else {
		    $photos = M('photo')->where(array('token' => $this->token,'status'=>1))->select();
            $this->assign('photos', $photos);
			
            $this->assign('set',$house);
            $this->display();  
        }
    }
	
	public function houses()
    {        
        $room_db = M('estate_house');

        if(IS_POST)
        {

            $map['name']     = array('like',"%$key%"); 
			$map['token']    = $this->token;
			$map['status']   = 1;
            $count  = $room_db->where($map)->count();  
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $room_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            
            $where  = array('token' => $this->token, 'status'=>1);
            $count  = $room_db->where($where)->count();
 
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $room_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
            
        }

        $this->assign('page',$show);        
        $this->assign('list',$list);

        $this->display();        
    }
	
	public function houseAdd()
    { 
        if(IS_POST)
        {
            $room_db = M('estate_house');
            $data['token'] = $this->token;
            $data['name'] = $_POST['name'];
            $data['floor_num'] = $_POST['floor_num'];
			$data['ting'] = $_POST['ting'];
			$data['fang'] = $_POST['fang'];
			$data['area'] = $_POST['area'];
			$data['list_id'] = $_POST['list_id'];
			$data['orderNum'] = $_POST['orderNum'];
			$data['description'] = $_POST['description'];
			$data['pic_url_input'] = $_POST['pic_url_input'];
			$data['pic_url_input_1'] = $_POST['pic_url_input_1'];
			$data['pic_url_input_2'] = $_POST['pic_url_input_2'];
			$data['panorama_id']   = $this->_post('panorama_id');
			//$data['type4'] = $_POST['type4'];
			
            $data['update_time'] = time();
			$data['status'] = 1;
			
            $room_id = $room_db->add($data);

            if ($room_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/houses'));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/houseAdd'));
            }

        }
        else 
        {
		
			$panoramas = M('panorama')->where(array('token' => $this->token,'status'=>1))->select();
            $this->assign('panoramas', $panoramas);
			
		    $estate_list = M('estate_list')->where(array('token' => $this->token))->select();
            $this->assign('estate_list', $estate_list);
            $this->display('houseSet');
        }
    }

    public function houseDel()
    {
        $cid = $this->_get('cid','intval',0);

        if(IS_GET)
        {                              
            $where = array('id'=>$cid, 'token'=>$this->token);

            $room_db = M('estate_house');

            $ret = $room_db->where($where)->save(array("status"=>0));

            if($ret == 1)
            {
                $this->success('操作成功',U(MODULE_NAME.'/houses'));
            } else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/houses'));
            }
        }        
    }
	
    public function houseSet()
    {
        if(IS_POST)
        { 
            //更新分类信息
            $room_id    = $this->_post('id','intval', 0);
			
            $room_db    = M('estate_house');
            $data['name'] = $_POST['name'];
            $data['floor_num'] = $_POST['floor_num'];
			$data['ting'] = $_POST['ting'];
			$data['fang'] = $_POST['fang'];
			$data['area'] = $_POST['area'];
			$data['list_id'] = $_POST['list_id'];
			$data['orderNum'] = $_POST['orderNum'];
			$data['description'] = $_POST['description'];
			$data['pic_url_input'] = $_POST['pic_url_input'];
			$data['pic_url_input_1'] = $_POST['pic_url_input_1'];
			$data['pic_url_input_2'] = $_POST['pic_url_input_2'];
			$data['panorama_id']   = $this->_post('panorama_id');
			//$data['type4'] = $_POST['type4'];
			
            $data['update_time'] = time();
			
            $ret = $room_db->where(array('id'=> $room_id, 'token'=>$this->token))->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/houses'));
            }
            else
            {
                Log::write("更新户型失败：room_id:".$room_id.'; error:'.$room_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $room_id = $this->_get('cid','intval', 0);
            $house = M('estate_house')->where(array('id'=>$room_id, "token"=>$this->token))->find();
            if(empty($house))
            {
                $this->error("没有相应户型.您现在可以添加.",U(MODULE_NAME.'/houseAdd'));
            }
            $this->assign('house', $house);
			
			$panoramas = M('panorama')->where(array('token' => $this->token,'status'=>1))->select();
            $this->assign('panoramas', $panoramas);
			
		    $estate_list = M('estate_list')->where(array('token' => $this->token))->select();
            $this->assign('estate_list', $estate_list);
			
            $this->display();    
        }
    }
	
	public function experts()
    {        
        $room_db = M('estate_expert');

        $where  = array('token' => $this->token, 'status'=>1);
        $count  = $room_db->where($where)->count();
 
        $Page   = new Page($count,20);
        $show   = $Page->show();

        $list   = $room_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('page',$show);        
        $this->assign('list',$list);

        $this->display();        
    }
	
	public function expertAdd()
    { 
        if(IS_POST)
        {
            $room_db = M('estate_expert');
            $data['token'] = $this->token;
			$data['title'] = $_POST['title'];
            $data['name'] = $_POST['name'];
            $data['position'] = $_POST['position'];
			$data['pic_url_input'] = $_POST['pic_url_input'];
			$data['comment'] = $_POST['comment'];
			$data['orderNum'] = $_POST['orderNum'];
			$data['description'] = $_POST['description'];
			
            $data['update_time'] = time();
			$data['status'] = 1;
			
            $room_id = $room_db->add($data);

            if ($room_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/experts'));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/expertAdd'));
            }

        }
        else 
        {
            $this->display('expertSet');
        }
    }

    public function expertDel()
    {
        $cid = $this->_get('cid','intval',0);

        if(IS_GET)
        {                              
            $where = array('id'=>$cid, 'token'=>$this->token);

            $room_db = M('estate_expert');

            $ret = $room_db->where($where)->save(array("status"=>0));

            if($ret == 1)
            {
                $this->success('操作成功',U(MODULE_NAME.'/experts'));
            } else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/expert'));
            }
        }        
    }
	
    public function expertSet()
    {
        if(IS_POST)
        { 
            //更新分类信息
            $room_id    = $this->_post('id','intval', 0);
			
            $room_db    = M('estate_expert');
			$data['title'] = $_POST['title'];
            $data['name'] = $_POST['name'];
            $data['position'] = $_POST['position'];
			$data['pic_url_input'] = $_POST['pic_url_input'];
			$data['comment'] = $_POST['comment'];
			$data['orderNum'] = $_POST['orderNum'];
			$data['description'] = $_POST['description'];
			
            $data['update_time'] = time();
			
            $ret = $room_db->where(array('id'=> $room_id, 'token'=>$this->token))->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/experts'));
            }
            else
            {
                Log::write("更新户型失败：room_id:".$room_id.'; error:'.$room_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $room_id = $this->_get('cid','intval', 0);
            $expert = M('estate_expert')->where(array('id'=>$room_id, "token"=>$this->token))->find();
            if(empty($expert))
            {
                $this->error("没有相应户型.您现在可以添加.",U(MODULE_NAME.'/expertAdd'));
            }
            $this->assign('expert', $expert);
            $this->display();    
        }
    }
	
	
	public function lists()
    {        
        $room_db = M('estate_list');

        $where  = array('token' => $this->token, 'status'=>1);
        $count  = $room_db->where($where)->count();
 
        $Page   = new Page($count,20);
        $show   = $Page->show();

        $list   = $room_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('page',$show);        
        $this->assign('list',$list);

        $this->display();        
    }
	
	public function listAdd()
    { 
        if(IS_POST)
        {
            $room_db = M('estate_list');
            $data['token'] = $this->token;
			$data['name'] = $_POST['name'];
			$data['orderNum'] = $_POST['orderNum'];
			$data['description'] = $_POST['description'];
			
            $data['update_time'] = time();
			$data['status'] = 1;
			
            $room_id = $room_db->add($data);

            if ($room_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/lists'));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/listAdd'));
            }

        }
        else 
        {
            $this->display('listSet');
        }
    }

    public function listDel()
    {
        $cid = $this->_get('cid','intval',0);

        if(IS_GET)
        {                              
            $where = array('id'=>$cid, 'token'=>$this->token);

            $room_db = M('estate_list');

            $ret = $room_db->where($where)->save(array("status"=>0));

            if($ret == 1)
            {
                $this->success('操作成功',U(MODULE_NAME.'/lists'));
            } else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/lists'));
            }
        }        
    }
	
    public function listSet()
    {
        if(IS_POST)
        { 
            //更新分类信息
            $room_id    = $this->_post('id','intval', 0);
			
            $room_db    = M('estate_list');
			$data['name'] = $_POST['name'];
			$data['orderNum'] = $_POST['orderNum'];
			$data['description'] = $_POST['description'];
            $data['update_time'] = time();
			
            $ret = $room_db->where(array('id'=> $room_id, 'token'=>$this->token))->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/lists'));
            }
            else
            {
                Log::write("更新楼盘失败：id:".$room_id.'; error:'.$room_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $room_id = $this->_get('cid','intval', 0);
            $list = M('estate_list')->where(array('id'=>$room_id, "token"=>$this->token))->find();
            if(empty($list))
            {
                $this->error("没有相应户型.您现在可以添加.",U(MODULE_NAME.'/listAdd'));
            }
            $this->assign('son', $list);
            $this->display();    
        }
    }
}

?>

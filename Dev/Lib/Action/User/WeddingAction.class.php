<?php
class WeddingAction extends UserAction
{

	protected function _initialize()
	{
		parent::_initialize();
		$this->function = 'hunqing';
		parent::checkOpenedFunction();
    }

	public function index()
	{
		$data=M('wedding');
        $count      = $data->where(array('token'=>$this->token, 'status'=>1))->count();
        $Page       = new Page($count,15);
        $show       = $Page->show();
        $list = $data->where(array('token'=>$this->token, 'status'=>1))->order("id")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->display();
	}
	
	public function set()
    {
        $id    = $this->_get('id'); 
        $wedding_db        = M('Wedding');
        $where          = array('token'=>$this->token,'id'=>$id, 'status' => 1);
        $wedding       = $wedding_db->where($where)->find();
        if (empty($wedding))
        {
            $this->error("没有喜帖.您现在可以添加.",U('Wedding/set'));
        }
        if(IS_POST)
        {
            $data['keyword']    = $this->_post('keyword');
            $data['title']      = $this->_post('title');
            $data['wedding_address']    = $this->_post('wedding_address');
			$data['wedding_time']    = $this->_post('wedding_time');
            $data['tel']        = $this->_post('tel');
            $data['head_pic_url']    = $this->_post('head_pic_url');
            $data['msg_pic_url']     = $this->_post('msg_pic_url'); 
			$data['qrcode_url']     = $this->_post('qrcode_url'); 
			$data['wedding_music_url']     = $this->_post('wedding_music_url');
            $data['wedding_pic_urls']     = $this->_post('wedding_pic_urls');	
            $data['man']       = $this->_post('man'); 
            $data['woman']       = $this->_post('woman'); 
            $data['description']      = $this->_post('description'); 
			$data['longtitude'] = $this->_post('longtitude');
            $data['latitude']   = $this->_post('latitude');
            $ret = $wedding_db->where($where)->save($data);
            if($ret)
            {
                $data1['module']   = 'wedding';
                $data1['token']    = $this->token;
                $data1['function'] = $this->function;
				$data1['pid'] = $id;
                $da['keyword']     = $_POST['keyword'];
                M('Keyword')->where($data1)->save($da);
                $this->success('修改成功',U('Wedding/index'));
            }
            else
            {
                $this->error('无修改', U('Wedding/set', array('id'=>$id)));
            }
        } else { 
		    $wedding_pic_urls = split("\\^", $wedding["wedding_pic_urls"]);
		    $this->assign("wedding_pic_urls", $wedding_pic_urls);
			
            $this->assign('set',$wedding);
            $this->display();  
        }
    }
    
    public function add()
    { 
        if (IS_POST)
        {
            $data['keyword']    = $this->_post('keyword');
            $data['title']      = $this->_post('title');
            $data['wedding_address']    = $this->_post('wedding_address');
			$data['wedding_time']    = $this->_post('wedding_time');
            $data['tel']        = $this->_post('tel');
            $data['head_pic_url']    = $this->_post('head_pic_url');
            $data['msg_pic_url']     = $this->_post('msg_pic_url'); 
			$data['qrcode_url']     = $this->_post('qrcode_url'); 
			$data['wedding_music_url']     = $this->_post('wedding_music_url'); 
			$data['wedding_pic_urls']     = $this->_post('wedding_pic_urls');
            $data['man']       = $this->_post('man'); 
            $data['woman']       = $this->_post('woman'); 
            $data['description']      = $this->_post('description'); 
			$data['longtitude'] = $this->_post('longtitude');
            $data['latitude']   = $this->_post('latitude');
			
            $data['token']      = $this->token;
            $data['creattime']  = time();
			$data['status']  = 1;
            $merchant_id = M('Wedding')->data($data)->add();
            if($merchant_id)
            {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'wedding';
                $kwd_data['function'] = $this->function;
                $kwd_data['pid'] = $merchant_id;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwds_db->add($kwd_data);
            }
           $this->success('修改成功',U('Wedding/index'));
        }
        else
        {
            $this->display('set');
        }
    }
	
	public function del()
    {
        $id = $this->_get('id');

        if(IS_GET)
        {                              
            $where  = array('id'=>$id,'token'=>$this->token, 'status'=>1);
            $back=M('Wedding')->where($where)->save(array('status'=>0));
            if($back==true)
            {
                M('Keyword')->where(array('pid'=>$id,'token'=>$this->token,'module'=>'wedding', 'function'=> $this->function))->delete();
                $this->success('操作成功',U('Wedding/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Wedding/index'));
            }
        }        
    }
	
	public function reply()
	{
	    $id = $this->_get('id'); 
		$data=M('wedding_reply');
        $count      = $data->where(array('wedding_id'=>$id))->count();
        $Page       = new Page($count,15);
        $show       = $Page->show();
        $list = $data->where(array('wedding_id'=>$id, 'status'=>1))->order("id")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('page',$show);
        $this->assign('list',$list);
		$this->assign('wid',$id);
        $this->display();
	}
	
	public function delreply()
    {
        $id = $this->_get('id');
        $wid = $this->_get('wid');
		
        if(IS_GET)
        {                              
            $back=M('Wedding_reply')->where(array('id'=>$id,'token'=>$this->token))->save(array('status'=>0));
            if($back==true)
            {
                $this->success('操作成功',U('Wedding/reply',array('id'=>$wid)));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Wedding/index'));
            }
        }        
    }
	
	public function wall()
	{
	    $id = $this->_get('id');
		
        $wedding  = M('Wedding')->where(array('token'=>$this->token,'id'=>$id, 'status' => 1))->find();
		$this->assign('set',$wedding);
		
        $this->display();
	}
	
	public function jsonreply()
	{
	    $id = $this->_post('id');
        $replys = M('wedding_reply')->where(array('wedding_id'=>$id))->order("id")->select();
        
        $this->ajaxReturn($replys, "OK", 1);
	}
}
?>
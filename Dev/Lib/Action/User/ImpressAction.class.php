<?php
class ImpressAction extends UserAction
{

	protected function _initialize()
	{
		parent::_initialize();
		$this->function = 'yingxiang';
		parent::checkOpenedFunction();
    }

	public function index()
	{
		$data=M('impress');
        $count      = $data->where(array('token'=>$this->token, 'status'=>array("neq", 0)))->count();
        $Page       = new Page($count,15);
        $show       = $Page->show();
        $list = $data->where(array('token'=>$this->token, 'status'=>array("neq", 0)))->order("id")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->display();
	}
	
	public function set()
    {
        $id    = $this->_get('id'); 
        $where          = array('token'=>$this->token,'id'=>$id, 'status' => 1);
        $reply       = M('Impress')->where($where)->find();
        if (empty($reply))
        {
            $this->error("没有微印象.您现在可以添加.",U('Impress/add'));
        }

        if(IS_POST)
        {
            $data['keyword']    = $this->_post('keyword');
            $data['title']      = $this->_post('title');
            $data['msg_pic_url']     = $this->_post('msg_pic_url');
            
			$impress = array();
            $impress_content = $_POST['impress_content'];
			$impress_count = $_POST['impress_count'];
            for($i = 0; $i<count($impress_content); $i ++) {
                if(!empty($impress_content[$i])) {
                    array_push($impress, array($impress_content[$i], $impress_count[$i]));
                }
            }
            $data['impress'] = serialize($impress);
			
            $ret = M('Impress')->where($where)->save($data);

            if($ret)
            {
                $data1['module']   = 'reply';
                $data1['token']    = $this->token;
                $data1['function'] = $this->function;
				$data1['pid'] = $id;
                $da['keyword']     = $_POST['keyword'];
                M('Keyword')->where($data1)->save($da);
                $this->success('修改成功',U('Impress/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Impress/index'));
            }
        } else { 
		    if(!empty($reply)) {
		        $impress = unserialize($reply['impress']);
                $this->assign('impress', $impress);
			}
            $this->assign('set',$reply);
            $this->display();  
        }
    }
    
    public function add()
    { 
        if (IS_POST)
        {
            $data['keyword']    = $this->_post('keyword');
            $data['title']      = $this->_post('title');
			$data['msg_pic_url']     = $this->_post('msg_pic_url'); 
			
			$impress = array();
            $impress_content = $_POST['impress_content'];
			$impress_count = $_POST['impress_count'];
            for($i = 0; $i<count($impress_content); $i ++) {
                if(!empty($impress_content[$i])) {
                    array_push($impress, array($impress_content[$i], $impress_count[$i]));
                }
            }
            $data['impress'] = serialize($impress);
			
            $data['token']      = $this->token;
            $data['createtime']  = time();
            $data['status']  = 1;
                $merchant_id = M('impress')->data($data)->add();
            if($merchant_id)
            {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 2;
                $kwd_data['module'] = 'reply';
                $kwd_data['function'] = $this->function;
                $kwd_data['pid'] = $merchant_id;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwds_db->add($kwd_data);
            }
            $this->success('修改成功',U('Impress/index'));
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
            $back=M('impress')->where($where)->save(array('status'=>0));
            if($back==true)
            {
                M('Keyword')->where(array('pid'=>$id,'token'=>$this->token,'module'=>'reply', 'function'=> $this->function))->delete();
                $this->success('操作成功',U('Impress/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Impress/index'));
            }
        }        
    }
}
?>
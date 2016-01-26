<?php
/**
 * open api
**/
class OpenapiAction extends UserAction{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'disanfang';
        parent::checkOpenedFunction();
    }
    
    public function index(){
        $token = session('token');
        $db = M('openapi');
        $where['token'] = $token;
        $where['status'] = 1;
        $info = $db->where($where)->select(); 
        $this->assign('list',$info);
        $this->display();
    }

    public function del(){
        $id = $this->_get('id','intval');
        $token = session('token');

        $where['id'] = $id;
        $where['token'] = $token;
        $data['status'] = 0;
        $ret = M('openapi')->where($where)->save($data);
        
        if ($ret >= 0) {
            M('Keyword')->where(array('pid'=>$id,'token'=>$token,'function'=>$this->function,'module'=>'disanfang'))->delete();
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
        }
    }

    public function add(){
        if(IS_POST){
            // 检查是否存在关键词
            $where['token']=session('token');
            $where['keyword']=$this->_post('keyword');
            $where['status']=1;
			
			$data['token']=session('token');
            $data['otoken'] = trim($this->_post('otoken'));
			$data['keyword'] = trim($this->_post('keyword'));
			$data['user'] = $this->_post('user');
			$data['ourl'] = trim($this->_post('ourl'));
			$data['status'] = 1;
			
            $check = M('openapi')->where($where)->find();
            
            if (!empty($check)) {
                $this->error('已经存在该第三方关键词'.$this->_post('keyword').'请删除或者重定义同名关键词');
                return;
            }
			$rid = M('openapi')->add($data);
			
            if($rid!=false){
                $data1['pid']=$rid;
                $data1['module']='disanfang';
                $data1['token']=session('token');
                $data1['keyword']=$this->_post('keyword');
                $data1['function']= $this->function;
                M('Keyword')->add($data1);
                $this->success('第三方接口创建成功',U('Openapi/index'));
            }else{
                $this->error('服务器繁忙,请稍候再试');
            }
        }else{
            $this->display();
        }
    }
	
	public function edit(){
        if(IS_POST){
            
			$id = $this->_get('id','intval');
			$where['id']=$id;
			$where['token'] = session('token');
			$where['status']=1;
            
			$data['keyword'] = trim($this->_post('keyword'));
			$data['user'] = $this->_post('user');
			$data['ourl'] = trim($this->_post('ourl'));
			$data['otoken'] = trim($this->_post('otoken'));
            $check = M('openapi')->where($where)->save($data);
            
            if($check){
			    // 更新关键词
                $where2['pid']=$id;
                $where2['module']='disanfang';
                $where2['token']=session('token');
                $where2['function']= $this->function;
				$data1['keyword']=$this->_post('keyword');
                M('Keyword')->where($where2)->save($data1);
                $this->success('第三方关键词更新成功',U('Openapi/index'));
            }else{
                $this->error('服务器繁忙,请稍候再试');
            }
        }else{
            $where['token'] = session('token');
            $where['status'] = 1;
            $where['id'] = $this->_get('id','intval');
            $info = M('openapi')->where($where)->find(); 

            $this->assign('info',$info);
            $this->display();
        }
    }
}
?>

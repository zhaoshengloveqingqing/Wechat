<?php
class ApiAction extends UserAction{
	public function index(){		
		if($this->_get('token')!=session('token')){$this->error('非法操作');}
		$token_open=M('token_open')->field('queryname')->where(array('token'=>session('token')))->find();
		//dump($token_open);
		if(!strpos($token_open['queryname'],'api')){$this->error('您还开启该模块的使用权,请到功能模块中添加',U('Function/index',array('token'=>session('token'),'id'=>session('wxid'))));}
		$data=D('Api');
		$this->assign('api',$data->where(array('token'=>session('token'),'uid'=>session('uid')))->find());
		if(IS_POST){
			$_POST['uid']=SESSION('uid');
			$_POST['token']=SESSION('token');
			//if(empty($_POST['home']))unset($_POST['home']);
			if($data->create()){				
				if($data->add()){
					$this->success('操作成功');					
				}else{
					$this->error('服务器繁忙，请稍候再试');
				}			
			}else{			
				$this->error($data->getError());
			}
		
		}else{
			$this->display();
		}
	}
	public function edit(){
		if($this->_get('token')!=session('token')){$this->error('非法操作');}
		$data=D('Api');
		if(IS_POST){
			if($data->create()){
				if($data->where(array('token'=>session('token'),'uid'=>session('uid')))->save()!=false){
					$this->success('操作成功');					
				}else{
					$this->error('服务器繁忙，请稍候再试');
				}			
			}else{			
				$this->error($data->getError());
			}
		
		}else{
			$this->error('非法操作');		
		
		}
	}




}


?>
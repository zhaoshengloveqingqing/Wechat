<?php
class GoldenAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'zajindan';
		parent::checkOpenedFunction();
    }

	public function index()
	{
		$where = array('token'=>$this->token,'type'=>4, 'status'=>array('neq', 3));
		$list = M('Lottery')->where($where)->select();

		$this->assign('count',count($list));
		$this->assign('list',$list);
		$this->display();
	}

	public function sn()
	{
		$id 	= $this->_get('id','intval');
		$data 	= M('Lottery')->where(array('token'=>session('token'),'id'=>$id,'type'=>4))->find();

		$record = M('Lottery_record')->where('token="'.session('token').'" and lid='.$id.' and sn!=""')->select();

		$recordcount=M('Lottery_record')->where('token="'.session('token').'" and lid='.$id.' and sn!=""')->count();
		
		$datacount= $data['fistnums']+ $data['secondnums']+ $data['thirdnums']+$data['fournums']+ $data['fivenums']+ $data['sixnums'];
		$this->assign('datacount',$datacount);
		$this->assign('recordcount',$recordcount);
		$this->assign('record',$record);
	
		$this->display();
	
	
	}
	public function add()
	{
		if(IS_POST)
		{
			$data = D('lottery');
			$_POST['statdate']	= strtotime($this->_post('statdate'));
			$_POST['enddate']	= strtotime($this->_post('enddate'));
			$_POST['token']		= session('token');
			$_POST['type']		= 4;
			$_POST['canrqnums'] = 5;
			$_POST['group'] = serialize($_POST['group']);
			if($_POST['enddate'] < $_POST['statdate']){
				$this->error('结束时间不能小于开始时间');
			}else{
				if($data->create()!=false){
					if($id=$data->add()){
						$data1['pid']=$id;
						$data1['module']='Lottery';
						$data1['token']=session('token');
						$data1['keyword']=$this->_post('keyword');
						$data1['function']='zajindan';
						M('Keyword')->add($data1);
						$this->success('活动创建成功',U('Golden/index'));
					}else{
						$this->error('服务器繁忙,请稍候再试');
					}
				}else{
					$this->error($data->getError());
				}
			}
			
		}else{
            $class_con = array('token'=>$this->token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $this->assign('class_info',$class_info);
			$this->display();
		}
	}

	public function setinc()
	{
		$id 	= $this->_get('id','intval');
		$where 	= array('id'=>$id,'token'=>session('token'));

		$coupon = M('Lottery')->where($where)->find();
		if($coupon) 
		{
			$data  =M('Lottery')->where($where)->save(array('status'=>1));
			if($data!=false){
				$this->success('恭喜你,活动已经开始');
			}else{
				$this->error('服务器繁忙,请稍候再试');
			}
		}
		else
		{
			$this->success('恭喜你,活动已经开始了');
		}
	}

	public function setdes()
	{
		$id = $this->_get('id');
		$where = array('id'=>$id,'token'=>session('token'));
		$coupon = M('Lottery')->where($where)->find();

		if ($coupon)
		{
			$ret = M('Lottery')->where($where)->save(array('status'=>2));
			if ($ret !== false)
			{
				$this->success('活动已经结束');
			}
			else
			{
				$this->error('服务器繁忙,请稍候再试');
			}
		}
		else
		{
			$this->success('活动已经结束!');
		}
	}


	public function edit(){
		if(IS_POST){
			//保存砸金蛋信息
			$data=D('Lottery');
			$_POST['id']=$this->_get('id');
			$_POST['token']=session('token');
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate'] =strtotime($_POST['enddate']);
			if ($_POST['canrqnums'] > 50 || $_POST['canrqnums'] < 1) {
			    $_POST['canrqnums'] = 50;
			}
			if ($_POST['enddate'] < $_POST['statdate']) {
				$this->error('结束时间不能小于开始时间');exit();
			}
			//人群
			$_POST['group'] = serialize($_POST['group']);
			
			$where=array('id'=>$_POST['id'],'token'=>$_POST['token'],'type'=>4);
			$check=$data->where($where)->find();
			if($check == false) $this->error('非法操作');

			if($data->where($where)->save($_POST)){
				$data1['pid'] = $_POST['id'];
				$data1['module']='Lottery';
				$data1['token']=session('token');
				$data1['function']='zajindan';

				$da['keyword']=$_POST['keyword'];

				M('Keyword')->where($data1)->save($da);
				$this->success('修改成功',U('Golden/index',array('token'=>session('token'))));
			}else{
				$this->error('操作失败');
			}
			
		}else{
			$id=$this->_get('id');
			$where=array('id'=>$id,'token'=>session('token'));
			$data=M('Lottery');
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			$lottery=$data->where($where)->find();		
			//人群选择
			$group = $lottery['group'] ? unserialize($lottery['group']) : 0;
			$lottery['group'] = $group ? json_encode($group) : 0;
			
			$this->assign('vo',$lottery);	

            $class_con = array('token'=>$this->token,'status'=>1);
            $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
            if(empty($class_info)){
                $class_info = C('MEMBER_GROUP');
            }
            $this->assign('class_info',$class_info);
            
			//dump($_POST);
			$this->display('add');
		}
	
	}


	public function del()
	{
		$id 	= $this->_get('id','intval');
		$where 	= array('id'=>$id,'token'=>$this->token);

		$lottery_db = M('Lottery');
		$coupon = $lottery_db->where($where)->find();

		if ($coupon) 
		{
			$back = $lottery_db->where($where)->save(array('status'=>3));
			if($back !== false)
			{
				M('Keyword')->where(array('pid'=>$id,'token'=>$this->token,'module'=>'lottery'))->delete();
				$this->success('删除成功');
			}
			else
			{
				$this->error('操作失败');
			}
		}
	}
	
	public function sendprize()
	{
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data['sendtime'] = time();
		$data['sendstutas'] = 1;
		$back = M('Lottery_record')->where($where)->save($data);
		if($back==true){
			$this->success('成功发奖');
		}else{
			$this->error('操作失败');
		}
	}
	
	public function sendnull(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data['sendtime'] = '';
		$data['sendstutas'] = 0;
		$back = M('Lottery_record')->where($where)->save($data);
		if($back==true){
			$this->success('已经取消');
		}else{
			$this->error('操作失败');
		}
	}
}


?>

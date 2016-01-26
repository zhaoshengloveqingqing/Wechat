<?php
class LotteryAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'dazhuanpan';
		parent::checkOpenedFunction();
    }

	public function index()
	{
		$where = array('token'=>session('token'),'type'=>1, 'status'=>array('neq', 3));
		$list = M('Lottery')->where($where)->select();
		$this->assign('count',count($list));
		$this->assign('list',$list);
		$this->display();
	}

	public function sn()
	{
		$id=$this->_get('id');
		$data=M('Lottery')->where(array('token'=>session('token'),'id'=>$id))->find();
		$record=M('Lottery_record')->where('token="'.session('token').'" and lid='.$id.' and sn!=""')->select();
		$recordcount=M('Lottery_record')->where('token="'.session('token').'" and lid='.$id.' and sn!=""')->count();
		$datacount= $data['fistnums']+$data['secondnums']+$data['thirdnums']+$data['fournums']+$data['fivenums']+$data['sixnums'];
		$this->assign('datacount',$datacount);
		$this->assign('recordcount',$recordcount);
		$this->assign('record',$record);	
		$this->display();	
	}

	public function add(){
		if(IS_POST){
			//add the use times . 
			$data=D('lottery');
			$_POST['statdate']=strtotime($this->_post('statdate'));
			$_POST['enddate']=strtotime($this->_post('enddate'));
			$_POST['token']=session('token');
			$_POST['group'] = serialize($_POST['group']);
			$this->all_insert('Lottery');
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

	public function setinc(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		$data=M('Lottery')->where($where)->setInc('status');
		if($data!=false){
			$this->success('恭喜你,活动已经开始');
		}else{
			$this->error('服务器繁忙,请稍候再试');
		}

	}
	public function setdes(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$check=M('Lottery')->where($where)->find();
		if($check==false)$this->error('非法操作');
		$data=M('Lottery')->where($where)->save(array('status'=>2));
		if($data!=false){
			$this->success('活动已经结束');
		}else{
			$this->error('服务器繁忙,请稍候再试');
		}
	
	}
	public function edit(){
		if(IS_POST){
			$data=D('Lottery');
			$_POST['id']=$this->_get('id');
			$_POST['token']=session('token');
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate']=strtotime($_POST['enddate']);
			if(empty($_POST['fist']) || empty($_POST['fistnums'])){
				$this->error('必须设置一等奖奖品和数量');
				exit;
			}
			//人群
			$_POST['group'] = serialize($_POST['group']);
			
			$where=array('id'=>$_POST['id'],'token'=>$_POST['token']);
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			if($data->create()){
				if($data->where($where)->save($_POST)){
					$data1['pid']=$_POST['id'];
					$data1['token']=session('token');
					$data1['function']= $this->function;
					$da['keyword']=$_POST['keyword'];
					M('Keyword')->where($data1)->save($da);
					$this->success('修改成功',U('Lottery/index',array('token'=>session('token'))));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($data->getError());
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
	public function del(){
		$id=$this->_get('id');
		$where=array('id'=>$id,'token'=>session('token'));
		$data=M('Lottery');
		$check=$data->where($where)->find();
		if($check==false)$this->error('非法操作');
		$back=$data->where($where)->save(array('status'=>3));
		if($back==true){
			M('Keyword')->where(array('pid'=>$id,'token'=>session('token'), 'function'=>$this->function))->delete();
			$this->success('删除成功');
		}else{
			$this->error('操作失败');
		}
	
	
	}
	
	public function sendprize(){
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

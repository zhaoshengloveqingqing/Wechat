<?php
class CouponAction extends UserAction{

    protected function _initialize(){
		parent::_initialize();
		$this->function = 'youhuiquan';
		parent::checkOpenedFunction();
    }

	public function index()
	{
		$where = array('token'=>$this->token,'type'=>3, 'status'=>array('neq', 3));
		$list = M('Lottery')->where($where)->select();

		$this->assign('count',count($list));
		$this->assign('list',$list);
		$this->display();
	}

	public function sn()
	{
		$id 	= $this->_get('id','intval');
		$data 	= M('Lottery')->where(array('token'=>session('token'),'id'=>$id,'type'=>3))->find();

		$record = M('Lottery_record')->where('token="'.session('token').'" and lid='.$id.' and sn!=""')->select();

		$recordcount=M('Lottery_record')->where('token="'.session('token').'" and lid='.$id.' and sn!=""')->count();
		$datacount= $data['fistnums']+ $data['secondnums']+ $data['thirdnums'];
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
			$_POST['type']		= 3;
			$_POST['canrqnums'] = 5;
			if($_POST['enddate'] < $_POST['statdate']){
				$this->error('结束时间不能小于开始时间');
			}else{
				if($data->create()!=false){
					if($id=$data->add()){
						$data1['pid']=$id;
						$data1['module']='Lottery';
						$data1['token']=session('token');
						$data1['keyword']=$this->_post('keyword');
						$data1['function']='youhuiquan';
						M('Keyword')->add($data1);
						$this->success('活动创建成功',U('Coupon/index'));
					}else{
						$this->error('服务器繁忙,请稍候再试');
					}
				}else{
					$this->error($data->getError());
				}
			}
			
		}else{
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
			//保存优惠券信息
			$data=D('Lottery');
			$_POST['id']=$this->_get('id');
			$_POST['token']=session('token');
			$_POST['statdate']=strtotime($_POST['statdate']);
			$_POST['enddate'] =strtotime($_POST['enddate']);
			$_POST['canrqnums'] = 5;
			if ($_POST['enddate'] < $_POST['statdate']) {
				$this->error('结束时间不能小于开始时间');
			} else {
				$where=array('id'=>$_POST['id'],'token'=>$_POST['token'],'type'=>3);
				$check=$data->where($where)->find();
				if($check == false) $this->error('非法操作');

				if($data->where($where)->save($_POST)){
					$data1['pid'] = $_POST['id'];
					$data1['module']='Lottery';
					$data1['token']=session('token');
					$data1['function']='youhuiquan';

					$da['keyword']=$_POST['keyword'];

					M('Keyword')->where($data1)->save($da);
					$this->success('修改成功',U('Coupon/index',array('token'=>session('token'))));
				}else{
					$this->error('操作失败');
				}
				
			}
		}else{
			$id=$this->_get('id');
			$where=array('id'=>$id,'token'=>session('token'));
			$data=M('Lottery');
			$check=$data->where($where)->find();
			if($check==false)$this->error('非法操作');
			$lottery=$data->where($where)->find();		
			$this->assign('vo',$lottery);
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
			$this->success('成功发优惠券');
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
	
	public function qr(){
        $db_lottery = M('lottery');
        $this->token = session('token');
        $lottery = $db_lottery->where(array('token'=>$this->token, 'type'=>3, 'status'=>array('neq', 3)))->find();
        if ($lottery){
            import("@.ORG.qrcode.QRCodeGenerator");
            $gen = new QRCodeGenerator();
            $product_url = C('site_url').U('Wap/Coupons/index', array('coupon_id'=>$lottery['id'], 'token'=>$this->token));
			$gen->build($product_url, 'coupon', $this->token, array('comp'=>''));
            $qrcode_pic_url = $gen->getUrl();
			$this->assign('link_url', $product_url);
			$this->assign('qrcode_url', $qrcode_pic_url);
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/index'));
        }
		$this->display();
    }
	
}


?>

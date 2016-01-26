<?php 
/**
 * 
 */
 class ShoporderAction extends RestApiAction
 {
	protected $sid;
	protected $token;
	protected $model;
	function __construct()
	{
		parent::checkAuth('Shop-order');
		$this->sid   = $_SESSION['manage_eshop_branch'];
		$this->token = $_SESSION['manage_company_token'];
		$this->model = new ShopLgModel();
	}

	/**
	*url: id/48
	**/
	public function read()
	{
		$input = $this->get;
		$ret = $input == true && $input['id'];
		if($ret)
		{
			$condition['token']  = $this->token;
			$condition['order_id']    = $input['id'];
			$ret = $this->model->orderInfo($condition,$this->token);
		}
		if($ret !== false)
		{
			$this->success("操作成功！" , $ret);
		}
		$this->error('error in update');
		Log::record('error' . print_r($input) . '\n');
	}

	/**
	*data:{'id':48,'status':3}
	**/
	public function update()
	{
		$input = $this->put;
		$ret = $input['status'] && $input['id'];
		if($ret)
		{ 
			$condition['token']  = $this->token;
			$condition['order_id']    = $input['id'];
			$condition['status']      = $input['status'];
			$ret = $this->model->changeState($condition);
		}
		if($ret !== false)
		{
			$this->success("操作成功！");
			
		}
		$this->error('error in update');
		Log::record('error' . print_r($input) . '\n');
	}

	/**
	*发货
	**/
	public function create()
	{
		$input = $this->post;
		$ret = $input['company_name'] && $input['delivery_sn'] && $input['sn'];
		if($ret)
		{ 
			$ret = $this->model->delivery($input['sn'],$input,$this->token);
		}
		if($ret !== false)
		{
			switch ($ret) {
				case 0:
				$this->returnMsg(104,"配送公司，或者配送单号为空！","");
					break;
				case 1:
				$this->returnMsg(103,"未付款，不能发货!","");
					break;
				case 2:
				$this->returnMsg(102,"发货状态更新失败！","");
					break;
				case 3:
				$this->returnMsg(101,"发货信息写入失败！","");
					break;
				case 4:
				$this->returnMsg(0,"操作成功！","");
					break;
				default:
				break;
			}
		}
		$this->error('error in update');
		Log::record('error' . print_r($input) . '\n');
	}


 } ?>
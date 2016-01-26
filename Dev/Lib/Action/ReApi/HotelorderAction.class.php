<?php 
/**
 * 
 */
 class HotelorderAction extends RestApiAction
 {
 	protected $hid;
	protected $token;
	protected $model;
	function __construct()
	{
		parent::checkAuth('Hotel-order');
		$this->hid   = $_SESSION['manage_hotel_branch'];
		$this->token = $_SESSION['manage_company_token'];
		$this->model = new HotelLgModel();
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
			$condition['hid']    = $this->hid;
			$condition['id']     = $input['id'];
			$ret = $this->model->readOne($condition);
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
		$ret = $input['status'] && $input['id'] ;
		if($ret)
		{
			$input['hid'] = $this->hid;
			$input['order_status'] = $input['status'];
			unset($input['status']);
			$ret = $this->model->changeState($input);
		}
		if($ret !== false)
		{
			$this->success("操作成功！");
		}
		$this->error('error in update');
		Log::record('error' . print_r($input) . '\n');
	}
	
 } ?>
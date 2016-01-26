<?php 
/**
 * 
 */
 class BookingAction extends RestApiAction
 {
 	protected $token;
	protected $hid;
	protected $model;
	function __construct()
	{
		parent::checkAuth('Booking-order');
		$this->hid   = $_SESSION['manage_prebook_branch'];
		$this->token = $_SESSION['manage_company_token'];
		$this->model = new BookingLgModel();
	}

	/**
	*url: id/48
	**/
	public function read()
	{
		$input = $this->get;
		$ret = $input == true && $input['id'];
		$condition['token']  = $this->token;
		$condition['hid']    = $this->hid;
		if($ret !== false)
		{
			$condition['id'] = $input['id'];
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
		if($ret !== false)
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
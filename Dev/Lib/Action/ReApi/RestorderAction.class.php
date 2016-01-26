<?php 
/**
 * 
 */
 class RestorderAction extends RestApiAction
 {
 	
	protected $rid;
	protected $token;
	protected $model;
	function __construct()
	{
		parent::checkAuth('Dining-order');
		$this->rid   = $_SESSION['manage_dine_branch'];
		$this->token = $_SESSION['manage_company_token'];
		$this->model = new RestLgModel();
	}

	/**
	*url: id/48
	**/
	public function read()
	{
		$input = $this->get;
		$ret = $input == true && $input['id'];
		$condition['token']  = $this->token;
		$condition['rest_id']    = $this->rid;
		if($ret)
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
		if($ret)
		{
			$input['rest_id'] = $this->rid;
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
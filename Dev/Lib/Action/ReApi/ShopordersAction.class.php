<?php 
/**
* 
*/
class ShopordersAction extends RestApiAction
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
	*get
	*point,offset,status
	*point：请求界点，status订单状态，offset个数，为正则取大于界点point的offset个元素，反之取小于point的offset个元素
	**/
	protected function read()
	{
		$input = $this->get;
		$ret = $input && $input['point'] !== false && $input['offset'] !== false && $input['status'];

		if($ret)
		{
			$condition['token']  = $this->token;
			$condition['status'] = $input['status'];
			$limit = abs($input['offset']);

			if($input['point'] == -1)
			{
				$ret = $this ->model->listOrders($condition , $limit , true);
			}
			else
			{
				$is_desc = $input['offset'] < 0;
				$opration_con = $is_desc ? 'lt' : 'gt'; //lt 小于,gt 大于
				$condition['order_id'] = array($opration_con , $input['point']);

				$ret = $this->model->listOrders($condition, $limit,$is_desc);

			}
		}

		if($ret !== false)
		{
			$this->success("操作成功！",$ret);
		}
		else
		{
			$this->error('error in read');
			Log::record('error' . print_r($input) . '\n');
		}
	}

	/**
	*主要用来改变订单的状态
	*{"act":[markAllRead],"content":"" }
	**/
	protected function update()
	{
		$input = $this->put;
		$ret = $input && $input['act'] ;
		if($ret)
		{
			switch ($ret['act']) {
				case 'markAllRead':
					$ret = $this->model->markAllRead();
					break;
				
				default:
					$ret = false;
					break;
			}
		}
		
		if($ret !== false)
		{
			$this->success("操作成功！");
		}
		else
		{
			$this->error('error in update');
			Log::record('error' . print_r($input) . '\n');
		}
	}

	
}
 ?>
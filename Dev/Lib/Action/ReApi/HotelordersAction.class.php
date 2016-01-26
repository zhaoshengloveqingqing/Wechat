<?php 
/**
* 
*/
class HotelordersAction extends RestApiAction
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
			$condition['hid']    = $this->hid;
			$condition['order_status'] = $input['status'];
			$limit = abs($input['offset']);

			if($input['point'] == -1)
			{
				$ret = $this ->model->listOrders($condition , $limit , true);
			}
			else
			{
				$is_desc = $input['offset'] < 0;
				$opration_con = $is_desc ? 'lt' : 'gt'; //lt 小于,gt 大于
				$condition['id'] = array($opration_con , $input['point']);

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

	protected function listAll($page,$con)
	{
		$ret = $page >= 1 && $con['order_status'];
		if($ret)
		{
			$pre_page = 15;
			$limit_f = ($page - 1 ) * $pre_page;
			$limit_t = $limit_f + $pre_page;
			
			$ret = $this->model->listOrders($con, $limit_f . ',' . $limit_t);
		}
		return $ret;
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
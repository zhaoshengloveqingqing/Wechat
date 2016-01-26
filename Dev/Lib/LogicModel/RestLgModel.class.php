<?php 
/**
 * 
 */
 class RestLgModel  extends LgModel
 {
	protected $rid;
	protected $field;
	function __construct()
	{
		$this->rid  = $_SESSION['manage_dine_branch'];
		$this->field = 'id,  '
						.' tel, '
						.' username,'
						.' status,  '
						.' createtime, '
			        	." from_unixtime(submittime ,'%Y-%m-%d %h:%i') as createtime,"
						." replace(dinetime, '/', '-') as book_time,"
						.' guestnum, '
						.' table, '
						.' price, '
						.' sn,  '
						.' readed';
	}


	/**
    *获取未读的订单数目
    **/
	public static function countUnreads($rid)
	{
		$ret = M('dine_order')->where(array('status'=>2,'rest_id'=>$rid, 'readed'=> 0))->count();
		//dump(M('dine_order')->getLastsql());
		return $ret;
	}

	/**
	*$data，array('id'=>id,'status'=>tostatus,'rest_id'=>hotelid)
	**/
	public function changeState($data)
	{

		if( !$data && !$data['id'] && !$data['status'] && $data['rest_id'])
		{
			return false;
		}
		
		$where['id'] = $data['id'];
		$where['rest_id'] = $data['rest_id'];
		$ret = M('dine_order')->where($where)->setField('status',$data['status']);
		return $ret;
	}

	/**
	*$condition为数组，至少包括token，order_status ，hid（hotelid）
	*$limit若为空，则默认取前20条
	**/
	public function listOrders($condition,$limit,$is_desc)
	{
		$ret = $condition && $condition['token'] && $condition['status'] && $condition['rest_id'];
		if(!$limit)
		{
			$limit = 20;
		}
		if($ret)
		{
			$merchant_db    = M('dine_order');
			$order = $is_desc ? 'id desc' : 'id asc';
                    $ret   = $merchant_db->where($condition)->limit($limit)->field($this->field)->order($order)->select(); 
		}
		if($ret !== false)
		{
			$count = count($ret);
			$up_ids = array();
			for ($i=0; $i < $count; $i++) { 
				if($ret[$i]['readed'] == 0 )
					array_push($up_ids,$ret[$i]['id']);
			}
			if($up_ids)//取过的数据，将readed设为1
			{
				M('dine_order')->where(array('id'=>array('in',$up_ids)))->setField('readed',1);
			}
                        
		}
		if($ret !== false)
		{
			if(!$ret)
			{
				$ret = array();
			}
			if(!$is_desc)
			{
				$ret = array_reverse($ret);
			}
			return $ret;
		}
        return false;
	}



	/**
	*标记所有为已读
	**/
	public static function markAllRead($id)
	{
		$where['readed'] = array('lt' , 2);
		$where['rest_id'] = $id;
		$ret = M('dine_order')->where($where)->setField('readed',2);
		return $ret;
	}

    /**
	*读取一个
	**/
	public function readOne($con)
	{
		$ret = $con == true;
		if($ret)
		{
			$detail_field = $this->field . ',menus as foods,note as comment';
			$ret = M('dine_order')->where($con)->field($detail_field)->find();
		}
		if($ret)
		{
	        $menus = json_decode($ret['foods'], true);
	        $num = 0;
	        $price = 0;
	        for($j = 0; $j < count($menus); $j++) 
	        {
	            $num += (int)$menus[$j]['nums'];
	            $price += (float)$menus[$j]['price'] * (int)$menus[$j]['nums'];
	        }
	        $ret['foods'] = $menus;
		}
		if($ret && $ret['readed'] < 2)
		{
			$ret['readed'] = 2;
			M('dine_order')->where($con)->save($ret);
		}
		if($ret !== false)
		{
			if(!$ret)
			{
				$ret = "";
			}
			return $ret;
		}
        return false;
	}


	  /**
    *获取所有未读的订单
    **/
	public static function unreadOrders($rid)
	{
		$ret = M('dine_order')->where(array('status'=>2,'rest_id'=>$rid, 'readed'=> 0))->select();
		if($ret)
		{
			$count = count($ret);
			for ($i=0; $i < $count; $i++)
			{ 
				if($ret[$i]['readed'] == 0 )
					array_push($up_ids,$ret[$i]['id']);
			}
			if($up_ids)//取过的数据，将readed设为1
			{
				M('dine_order')->where(array('id'=>array('in',$up_ids)))->field('readed',1);
			}
		}
		if(!$ret)
		{
			$ret = array();
		}
		return $ret;
	}
 	
 } ?>
<?php 
/**
 * 
 */
 class HotelLgModel  extends LgModel
 {
	protected $hid;
	protected $field;
	function __construct()
	{
		$this->hid  = $_SESSION['manage_hotel_branch'];
		$this->field = ' `id`, '
						.' `book_people` as  username, '
						.' `tel`, '
						.' `room_type`,'
						.' `book_num` as amount,  '
						.' `price`, '
						.' `order_status` as status, '
						.' `remarks`,'
			        	." from_unixtime(submit_time ,'%Y-%m-%d %h:%i') as createtime,"
						." from_unixtime(`book_time` ,'%Y-%m-%d %h:%i') as check_in, "
						." from_unixtime(`book_lefttime` ,'%Y-%m-%d %h:%i') as check_out, "
						.' `sn`,  '
						.' `readed`';
	}

	/**
	*$data，array('id'=>id,'status'=>tostatus,'hid'=>hotelid)
	**/
	public function changeState($data)
	{
		$ret = $data && $data['id'] && $data['order_status'];
		if($ret)
		{
			$where['id'] = $data['id'];
			$where['hid'] = $this->hid;
			$ret = M('hotel_order')->where($where)->setField('order_status',$data['order_status']);
		}
		
		return $ret;
	}

	/**
    *获取未读的订单数目
    **/
	public static function countUnreads($hid)
	{
		$ret = M('hotel_order')->where(array('order_status'=>3,'hid'=>$hid, 'readed'=> 0))->count();

		return $ret;
	}

	/**
	*$condition为数组，至少包括token，order_status ，hid（hotelid）
	*$limit若为空，则默认取前20条
	**/
	public function listOrders($condition,$limit,$is_desc)
	{
		$ret = $condition && $condition['token'] && $condition['order_status'] && $condition['hid'];
		if(!$limit)
		{
			$limit = 20;
		}
		if($ret)
		{
			$merchant_db    = M('hotel_order');
			$order = $is_desc ? 'id desc' : 'id asc';
	        $ret            = $merchant_db->where($condition)
	        							  ->limit($limit)
	        							  ->field($this->field)
	        							  ->order($order)
	        							  ->select(); 
		}
		if($ret !== false)
		{
			$count = count($ret);
			$up_ids = array();
			for ($i=0; $i < $count; $i++) { 
				if($ret[$i]['readed'] == 0 )
				{
					array_push($up_ids,$ret[$i]['id']);
					$ret[$i]['readed'] = 1;
				}
			}
			if($up_ids)//取过的数据，将readed设为1
			{
				$ids = implode(',', $up_ids); 
				M('hotel_order')->where( array('id'=>array('in',$ids) ) )->setField('readed',1);
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
		$where['hid'] = $id;
		$ret = M('Hotel_order')->where($where)->setField('readed',2);
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
			$ret = M('hotel_order')->where($con)->field($this->field)->find();
		}
		if($ret && $ret['readed'] < 2)
		{
			$ret['readed'] = 2;
			M('hotel_order')->where($con)->save($ret);
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
	public static function unreadOrders($hid)
	{
		$ret = M('hotel_order')->where( array('order_status'=>3,'hid'=>$hid, 'readed'=> 0) )->field($this->field)->select();
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
				M('hotel_order')->where(array('id'=>array('in',$up_ids)))->field('readed',1);
			}
		}
		
		if($ret !== false)
		{
			if(!$ret)
			{
				$ret = array();
			}
		}
		
		return $ret;
	}

 } ?>
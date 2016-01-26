<?php 
/**
* 订单的业务模型
*/
class BookingLgModel  extends LgModel
{
	protected $hid;
	function __construct()
	{
		$this->hid  = $_SESSION['manage_prebook_branch'];
	}

	/**
	*$data，array('id'=>id,'status'=>tostatus)
	**/
	public function changeState($data)
	{

		if(!$data && !$data['id'] && !$data['order_status'] )
		{
			return false;
		}
		$where['id'] = $data['id'];
		$where['hid'] = $data['hid'];
		$ret = M('Host_order')->where($where)->setField('order_status',$data['order_status']);
		return $ret;
	}

	
	/**
    *获取未读的订单数目
    **/
	public static function countUnreads($hid)
	{
		$ret = M('Host_order')->where(array('order_status'=>3,'hid'=>$hid, 'readed'=> 0))->count();

		return $ret;
	}

	/**
	*$condition为数组，至少包括token，order_status ，hid（hostid）
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
			$merchant_db    = M('Host_order');
			$order = $is_desc ? 'id desc' : 'id asc';
	        $ret   = $merchant_db->where($condition)
						        ->limit($limit)
						        ->field('id, book_people as username,'
							        	.' tel,'
							        	." from_unixtime(submit_time,'%Y-%m-%d %h:%i') as createtime,"
							        	." room_type as title, "//roomtype,为内容
							        	." from_unixtime(book_time ,'%Y-%m-%d') as book_time,"
							        	.' book_num as amount, price,'
							        	.' order_status as status,'
							        	.' sn, readed')
						        ->order($order)
						        ->select(); 
						        //dump($merchant_db->getlastsql());
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
				$ids = implode(',', $up_ids);
				M('Host_order')->where(array('id'=>array('in',$ids)))->setfield('readed',1);
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

	public static function markAllRead($id)
	{
		$where['readed'] = array('lt' , 2);
		$where['hid'] = $id;
		$ret = M('Host_order')->where($where)->setField('readed',2);
		return $ret;
	}


	/**
	*获取所有的订单项，即host
	**/
	public function merchantList($token , $key = '')
    {

        $merchant_db    = M('Host');
   
        $map['status']  = 1; 
        $map['token']   = $token; 
        if($key)
        {
	        $map['keyword|title|tel2|tel'] = array('like',"%$key%"); 
        }
        else
        {
        	return array();
        }
        $merchants = $merchant_db->where($map)->limit('0,10')->select();     
        return $merchants;
    }

    /**
	*读取一个
	**/
	public function readOne($con)
	{
		$ret = $con == true;
		if($ret)
		{
			$ret = M('Host_order')->where($con)
								->field('id, '
									.' book_people as username,'
						        	.' tel,'
						        	//." from_unixtime(check_in,'%Y-%m-%d %h:%i') as check_in,"
						        	." room_type as title, "//roomtype,为内容
						        	." from_unixtime(submit_time ,'%Y-%m-%d %h:%i') as createtime,"
						        	.' book_num as amount,'
						        	.' price,'
						        	." from_unixtime(book_time , '%Y-%m-%d') as book_time,"
						        	.' order_status as status,'
						        	.' sn, '
						        	.' remarks')
								->find();
		}
		if($ret)
		{
			if($ret['readed'] < 2)
			{
				$ret['readed'] = 2;
				M('Host_order')->where($con)->save($ret);
			}
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
		$ret = M('Host_order')->where(array('order_status'=>3,'hid'=>$hid, 'readed'=> array('lt' , 2) ))
								->field('id, '
									.' book_people as username,'
						        	.' tel,'
						        	//." from_unixtime(check_in,'%Y-%m-%d %h:%m') as check_in,"
						        	." room_type as title, "//roomtype,为内容
						        	." from_unixtime(submit_time ,'%Y-%m-%d %h:%i') as createtime,"
						        	.' book_num as amount,'
						        	.' price,'
						        	." from_unixtime(book_time , '%Y-%m-%d') as book_time,"
						        	.' order_status as status,'
						        	.' sn, '
						        	.' remarks')
					        	->select();//1,取出过，2,读过
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
				M('Host_order')->where(array('id'=>array('in',$up_ids)))->field('readed',1);
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
}

 ?>
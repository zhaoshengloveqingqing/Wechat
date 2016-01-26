<?php 
/**
 * 
 */
 class ShopLgModel  extends LgModel
 {
	protected $shop_id;
	protected $fields;
	function __construct()
	{
		$this->shop_id  = $_SESSION['manage_eshop_branch'];
		$this->fields = ' order_id as id, '
						.' sn, '
						.' total as amount, '
						.' price, '
						.' truename, '
						.' tel, '
			        	." from_unixtime(create_time ,'%Y-%m-%d %h:%i') as createtime,"
						.' status,  '
						.' payment, '
						.' readed ';
	}

	/**
    *获取未读的订单数目
    **/
	public static function countUnreads($token)
	{
		$ret = M('b2c_order')->where(array('status'=>1,'token'=>$token, 'readed'=> 0))->count();
		return $ret;
	}
	
	/**
	*$data，array('id'=>id,'status'=>tostatus,'hid'=>hotelid)
	**/
	public function changeState($data)
	{
		$ret = $data && $data['order_id'] && $data['status'] && $data['token'];
		if($ret)
		{
			$where['order_id'] = $data['order_id'];
			$where['token'] = $data['token'];
			$ret = M('b2c_order')->where($where)->setField('status',$data['status']);
		}
		
		return $ret == true ? $ret : false;
	}

	/**
	*参数 $sn
	*返回值0未付款表示不能发货，1表示发货状态更新失败，2表示发货信息写入失败,3表示成功
	**/
    public function delivery($sn,$data,$token) 
    {
    	$ret = $sn == true 
    			&& $data['company_name']
    			&& $data['delivery_sn'];

        $result = 0;
        $order = array();
    	if($ret)
    	{
	        $order_db = M('b2c_order');
	        $order_where = array('sn'=>$sn, 'token'=>$token);
	        $order = $order_db->where($order_where)->find();
	        $ret = $order !== false ; //找到订单
	        //根据付款方式（货到付款和第三方支付）判断是否能发货
	        $canSend =( $order['payment'] == 'alipay' && $order['status'] == 2 )
	        			|| ( $order['payment'] == 'cftpay' && $order['status'] == 2 )
	        			|| ( $order['payment'] == 'wxpay' && $order['status'] == 2 )
	        			|| ($order['payment'] == 'cod' && $order['status'] == 1);
	        $ret = $ret	&& $canSend;
	        $result ++;//1
    	}

        if($ret)//根据付款方式（货到付款和第三方支付）判断是否能发货
        {
			$result ++;//2
        	//更改状态
			$ret = $order_db->where($order_where)->save(array('status'=>3));
        }
        if($ret !== false)
        {
        	$result ++;//3
        	$updata = array();
            $updata['type']           = 'express';
            $updata['name']           = $data['company_name'];
            $updata['fee']            = 0;
            $updata['create_time']    = time();
            $updata['logistics_no']   = $data['delivery_sn'];
            $updata['order_id']       = $order['order_id'];
            $ret = M('b2c_logistics')->data($updata)->add();
        }
        if($ret)
        {
        	$result ++;//4
        }
        return $result;//0信息不全, 1未付款表示不能发货，2表示发货状态更新失败，3表示发货信息写入失败,4表示成功
    }


    /**
    *
    *
    **/
	public function listOrders($condition,$limit,$is_desc)
    {
    	$ret = $condition && $condition['token'] && $condition['status'];
		if(!$limit)
		{
			$limit = 20;
		}
		if($ret)
		{
			$order = $is_desc ? 'order_id desc' : 'order_id asc';
	        $ret   = M('b2c_order')->where($condition)->limit($limit)->field($this->fields)->order($order)->select(); 
		}
		if($ret !== false)
		{
			$count = count($ret);
			$up_ids = array();
			for ($i=0; $i < $count; $i++) { 
				if($ret[$i]['readed'] == 0 )
				{
					array_push($up_ids,$ret[$i]['order_id']);
					$ret[$i]['readed'] = 1;
				}
			}
			if($up_ids)//取过的数据，将readed设为1
			{
				$ids = implode(',', $up_ids); 
				M('b2c_order')->where( array('order_id'=>array('in',$ids) ) )->setField('readed',1);
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
	public static function markAllRead($token)
	{
		$where['readed'] = array('lt' , 2);
		$where['token'] = $token;
		$ret = M('b2c_order')->where($where)->setField('readed',2);
		return $ret >= 0 ;
	}

 	/**
	*读取一个
	**/
	public function orderInfo($con,$token)
    {
		$ret = $con == true;
		$order_info;
		if($ret)
		{
			$detail_fields = $this->fields 
								.' info,'
								.' zipcode, '
								.' address,'
								.' update_time';
			$ret = M('b2c_order')->where($con)->field($detail_fields)->find();
		}
		if($ret)
		{
			$order_info = $ret;

			switch($order['payment']) {
	            case 'alipay':
	                $ret      = M('b2c_trade')->where(array('order_sn'=>$order['sn'], 'token' => $token))->find();
	                break;
	            case 'wxpay':
	                $ret      = M('b2c_wxtrade')->where(array('order_sn'=>$order['sn'], 'token' => $token))->find();
	                break;
	            case 'cftpay':
	                $ret      = M('b2c_cfttrade')->where(array('order_sn'=>$order['sn'], 'token' => $token))->find();
	                break;
	        }

		}
		if($ret)
		{
			$ret = $this->order_items_info($order_info['id'],$token);
		}
		$delivery_info;
		if($con == true)
		{
			$order_info['items'] = $ret['items'];
			$order_info['total_price'] = (string) $ret['total_price'];
	        $delivery_info  = M('b2c_logistics')->where($con)->field('name as company_name,logistics_no as delivery_sn')->find();

		}
		if($delivery_info)
		{
			$order_info['company_name'] = $delivery_info['company_name'] ;
	        $order_info['delivery_sn'] = $delivery_info['delivery_sn'] ;
		}
		else
		{
			$order_info['company_name'] =  "";
	        $order_info['delivery_sn'] =  "";
		}
		if($order_info['readed'] < 2)
		{
			$ret['readed'] = 2;
			M('b2c_order')->where($con)->save($ret);
		}
		if($order_info !== false)
		{
			if(!$order_info)
			{
				$order_info = "";
			}
			return $order_info;
		}
		return false;
    }

    /**
    *读取某个订单的配送信息
    **/
    public function delivery_info($con)
    {
		$ret = $con == true;
    	if($ret)
    	{
    		$ret =  M('b2c_logistics')->where(array('order_id'=>$order_info['id'], 'token' => $token))->find();
    	}
    	if($ret)
    	{
    		return $ret;
    	}
    	return false;
    }

    /**
    *读取某个订单的商品列表，并返回总价和商品列表
    *return {"items":[],"total_price":xxx}
    **/
    public function order_items_info($oid,$token)
    {
		$ret = $oid == true && $token == true;
		if($ret)
		{
			$Model = new Model();
	        $sql = "select i.product_id,"
	        				." i.count, "
	        				."p.`name`,"
	        				." p.logo_url, "
	        				."i.price ,"
                            ."i.size_name,"
                            ."i.color_name "
	        			."from tp_b2c_order_item as i "
	        				." LEFT JOIN tp_b2c_product as p " 
	        					." on i.product_id = p.product_id "
	        			." where i.order_id ='$oid'"
	        				." and i.token='$token'";
	        $ret = $Model->query($sql);
		}
		if($ret)
		{
	        $total_price         = 0;
       		foreach ($ret as $k=>$c)
	        {
	            $total_price += $c['price'] * $c['count'];
	        }
	        $total_price = sprintf('%0.2f', $total_price); // 520 -> 520.00
	        $ret["items"] = $ret;
	        $ret["total_price"] = $total_price;
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
	public function unreadOrders($rid)
	{
		$ret = M('b2c_order')->$where(array('status'=>1,'rest_id'=>$rid, 'readed'=> 0))->select();
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
				M('b2c_order')->where(array('id'=>array('in',$up_ids)))->field('readed',1);
			}
		}
		
		if(!$ret)
		{
			$ret = array();
		}
		return $ret;
	}
 	
 } 
?>
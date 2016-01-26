<?php 
/**
 * 所有订单的公共api，轮询获取最新订单
 */
 class OrdersAction extends RestApiAction
 {

	protected $rid; // restarount
	protected $hid; // hotel
	protected $bid; // booking
	protected $sid;
	protected $token;


 	function __construct()
 	{
		$this->rid   = $_SESSION['manage_dine_branch'];
		$this->bid   = $_SESSION['manage_prebook_branch'];
		$this->hid   = $_SESSION['manage_hotel_branch'];
		$this->token = $_SESSION['manage_company_token'];
		$this->sid   = $_SESSION['manage_eshop_branch'];

 	}

 	public function read()
 	{

 		$ret = array(
 			    'hotel'  => $this->countUnHot(),
 			    'rest'   => $this->countUnRest(),
 			    'booking'=> $this->countUnBook(),
 			    'eshop'  => $this->countUnEShop(),
 			);

 		$this->success('',$ret);
 	}

 	/**
 	*计算未读宾馆的订单数
 	**/
 	protected function countUnHot()
 	{
		$ret = $this->check_action('Hotel-order' , $this->hid);
 		if($ret)
 		{
 			$ret = HotelLgModel::countUnreads($this->hid);
 		}
 		else 
 		{
 			return array('code'=>3,'count'=>0);//2 无权限读
 		}
 		if($ret >0 )
 		{
 			HotelLgModel::markAllRead($this->hid);
 		}
		return array('code'=>0,'count'=>$ret);
 	}

 	/**
 	*计算未读商城的订单数
 	**/
 	protected function countUnEShop()
 	{
		$ret = $this->check_action('Shop-order' , $this->sid);
 		if($ret)
 		{
 			$ret = ShopLgModel::countUnreads($this->token);
 		}
 		else 
 		{
 			return array('code'=>3,'count'=>0);//2 无权限读
 		}
 		if($ret >0 )
 		{
 			ShopLgModel::markAllRead($this->token);
 		}
		return array('code'=>0,'count'=>$ret);
 	}

 	/**
 	*计算未读餐馆的订单数
 	**/
 	protected function countUnRest()
 	{
 		$ret = $this->check_action('Dining-order' , $this->rid);
 		if($ret)
 		{
 			$ret = RestLgModel::countUnreads($this->rid);
 		}
 		else 
 		{
 			return array('code'=>3,'count'=>0);//2 无权限读
 		}
 		if($ret >0 )
 		{
 			RestLgModel::markAllRead($this->rid);
 		}
		return array('code'=>0,'count'=>$ret);
 	}

 	/**
 	*计算未读预定的订单数
 	**/
 	protected function countUnBook()
 	{
 		$ret = $this->check_action('Booking-order' , $this->bid);
 		if($ret)
 		{
 			$ret = BookingLgModel::countUnreads($this->bid);
 		}
 		else 
 		{
 			return array('code'=>3,'count'=>0);//2 无权限读
 		}
 		if($ret >0 )
 		{
 			BookingLgModel::markAllRead($this->bid);
 		}
		return array('code'=>0,'count'=>$ret);
 	}

	protected function check_action($action,$id)
	{
		$ret = $id && LoginLgModel::checkAction($action) ;
		return $ret;
	}
 } ?>
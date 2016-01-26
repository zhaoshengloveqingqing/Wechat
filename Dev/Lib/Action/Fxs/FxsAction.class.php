<?php
 header("Content-Type: text/html; charset=utf-8");
 header('Cache-control: private, must-revalidate'); 
class FxsAction extends WapAction
{
	protected $user_name;
    protected $token;
	public $ORDER_STATUS_INIT       = 1;
    public $ORDER_STATUS_PAYED      = 2;
    public $ORDER_STATUS_DELEVERYED = 3;
    public $ORDER_STATUS_CANCELED   = 4;
    //商户取消
    public $ORDER_STATUS_SHOP_CANCELED   = 5;
    public $ORDER_STATUS_CONFIRM = 6;
	private $access_token = '';
	private $backurl = '';
	private $openid = '';
    private $branch_id;
	private $pagesize = 0;
	const ORDER = 'b2c_order';
	const PRODUCT = 'b2c_product';
	function __construct(){
		$this->token = $_GET['token'];
		$this->openid = $_GET['wecha_id'];
	}
    protected function _initialize()
    {
        parent::_initialize();
		$this->token = $_SESSION['token']; 
        $shop_where = array('token'=>$this->token);
        $this->branch_id =  $_SESSION['manage_shop_branch'];
        if (!empty($this->branch_id)) 
        {
            $shop_where['fake_id'] = $this->branch_id;
        }
        $shop_where['status'] = '1';
        $shop = M('b2c_shop')->where($shop_where)->find();
        if ($shop != false) 
        {
            $this->branch_id = $shop['fake_id'];
            $this->assign('merchant_name',$shop['name']);
        }
        /*else
        {
            Log::save();
            exit;
        }*/
		session_start();
		$this->token=session('manage_company_token');
    }
    
    public function Oauth2()
    {
		
        if (!session('openid_'.$this->token)) {
    		$this->info = M('wxuser')->where(array('token'=>$this->token))->find();
	    	vendor("Oauth2.OAuth2", LIB_PATH.'../Extend/Vendor');
	    	vendor("Oauth2.Provider.Wechat", LIB_PATH.'../Extend/Vendor');
	        $wechat = new Wechat();
	        $wechat->config(array( 'id' => $this->info['appid'], 'secret' => $this->info['appsecret']));
			$redirect_uri = $_GET['method_name'];
	        if ( !$_GET['code'])
	        {
				$redirect_uri = C('site_url').'/index.php/Fxs/Fxs/Oauth2?token='.$this->token.'&method_name='.$_GET['method_name'];
	            // By sending no options it'll come back here
	            $url = $wechat->authorize(array('redirect_uri'=>$redirect_uri));
	            redirect($url);
	        }
	        else
	        {
	            try
	            {
	                // Have a go at creating an access token from the code
	                $token = $wechat->access($_GET['code'],array('method_name'=>$redirect_uri));
	                // Use this object to try and get some user details (username, full name, etc)
	                $user = $wechat->get_user_info($token);
	                session('openid_'.$this->token, $user->openid);
	            } catch (OAuth2_Exception $e)
	            {
	                print_r($e);die;
	            }
	        }
    	}
		if (session('openid_'.$this->token)) {
			$user = M('reguser')->where(array('token' => $this->token,'openid' => session('openid_'.$this->token)))->find();
			if (!$user) {
				session_destroy();
			}
		}
		header('Location:'.U('Fxs/'.$_GET['method_name'], array('token'=>$this->token)));
    }
	private function generateUrl($addr, $query_ary = array())
    {
        if (empty($addr)) 
        {
            return "";
        }

        $query_ary['token'] = $this->token;
        if (!empty($this->branch_id)) 
        {
            //如果没有bid 默认选择第一个分店
            $query_ary['bid']   = $this->branch_id;
        }
        

        if (!$this->wechat_type || !$this->is_authed) 
        {
            //如果不是高级认证服务号，则参数中带上openid
            $query_ary['wecha_id'] = $this->wechat_id;
        }
        
//        $host_name = $this->merchant_code.C('shop_domain');
//        if(C('hornor_shop_domain') == 0) {
//            $host_name = C('wx_handler_server');
//        }
        $host_name = $_SERVER['SERVER_NAME'];
        
        return 'http://'.$host_name.U($addr,$query_ary);
    }
	function curl_get($url, $gzip=false){
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	//        if($gzip) curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
			$content = curl_exec($curl);
			curl_close($curl);
			return $content;
	}
	//微信端查询分销商销售额和基本信息
	public function fxs_orderList(){
		/*if(!isset($_GET['code'])){
			$redirect_uri = C('site_url')."/index.php?g=Fxs&m=Fxs&a=fxs_orderList&token=$this->token";
			
			$url = $this->createOauthUrlForCode($redirect_uri);
			Header("Location: $url");
		}else{
			$this->code = $_GET['code'];
		}
		
		$this->code = $_GET['code'];
        $data = $this->get_curl_info($this->createOauthUrlForOpenId());
        $this->access_token = $data['access_token'];
		
		$this->openid = $data['openid'];
		
		
		$this->assign('openid', $this->openid);
		//session('openid',$this->openid);
		//exit(session('openid'));
        $data = $this->get_curl_info($this->createOauthUrlForUser());
        /*if ($this->is_need_auth) 
        {
            $this->error("您还未登录。");
        }*/
		$this->openid = session('openid_'.$this->token);
		if($this->openid==null){
			header('Location:'.U('Fxs/Oauth2', array('token'=>$this->token,'method_name'=>'fxs_orderList')));
		}
        $order_db = M('b2c_order');
	
        if (!empty($this->openid)) 
        {
			
		    //$list = $order_db->where("token='$this->token' and fxs_id='$fxs_id'")->order('status,update_time DESC')->select();
			//查询分销商基本信息
            $sql = "SELECT r.create_time as createtime,r.address as fxsaddress,r.truename as fxsname,r.province as fxsprovince,r.city as fxscity,r.area as fxsarea,table_order.* "
					." from tp_b2c_order as table_order INNER JOIN tp_reguser r ON table_order.fxs_id = r.id "			
					." where r.token='$this->token' AND r.openid='$this->openid'  AND r.status=1";
			$sql .= " ORDER BY r.status,r.update_time DESC ";						
			//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
			$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
			$orders = $Model->query($sql);
			$total_account=0;
			$ordersCount=0;
			$info = array();
            if ($orders)
            {
                foreach($orders as $k=>$v){
					$ordersCount+=$v['total'];					
					$total_account+=$v['price'];
					
					$info['fxsname'] = $v['fxsname'];
					$info['fxsprovince'] = $v['fxsprovince'];
					$info['fxscity'] = $v['fxscity'];
					$info['fxsarea'] = $v['fxsarea'];
					$info['fxsaddress'] = $v['fxsaddress'];
					$info['createtime'] = $v['createtime'];	
					$info['ordersCount'] = $ordersCount;	
					$info['total_account'] = $total_account;					
				}
            }
	
			
			//exit($total_account);
            $this->assign('order',$info);
			$this->assign('total_account',$total_account);
            $this->assign('ordersCount',$ordersCount);
        }
        
        $this->assign('metaTitle','分销商查询');
        $this->display();
	
	}
	//分销商底下客户查询
	
	public function myorder()
    {	
		
		$this->openid = session('openid_'.$this->token);

        $order_db = M('b2c_order');
		$order=$order_db->where("wecha_id='$this->openid'")->select();
		$fxs_id=$order[0]['fxs_id'];
		
        if (!empty($this->openid)) 
        {
           $sql = "SELECT r.create_time as createtime,r.address as fxsaddress,r.truename as fxsname,r.province as fxsprovince,r.city as fxscity,r.area as fxsarea,table_order.* "
					." from tp_b2c_order as table_order INNER JOIN tp_reguser r ON table_order.fxs_id = r.id "			
					." where r.token='$this->token' AND r.openid='$this->openid'  AND r.status=1";
			$sql .= " ORDER BY r.status,r.update_time DESC ";						
			//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
			$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
			$orders = $Model->query($sql);
            /*if (!empty($this->branch_id)) 
            {
                $order_where['branch_id'] = $this->branch_id;
            }*/
            //$orders    = $order_db->where($order_where)->order('create_time DESC')->select();
			
			$totalcount=0;
			foreach ($orders as $key => $o){
				$totalcount=$o['price']*$o['total'];
			}
            if ($orders)
            {
                foreach ($orders as $key => $o)
                {
					$order_id=$o['order_id'];				
                    $orders[$key]['url'] = $this->generateUrl('Fxs/orderMyDetail',array('order_id'=>$order_id)); 
					$order_amount=$o['price'];
                    if ($o['status'] == 1) 
                    {
                        switch($o['payment']) {
                            case 'alipay':
                                $orders[$key]['alipay_url'] = $this->generateUrl('ShopPay/startAlipay',array('order_sn'=>$o['sn'])); 
                                break;
                            case 'wxpay':

                                $orders[$key]['wxpay_url'] = $this->generatePayUrl('wxpay/pay',array('order_sn'=>$o['sn']), true); 
                                break;
                             case 'cftpay': 
                                $orders[$key]['cftpay_url'] = $this->generatePayUrl('cftpay/pay', array('order_sn'=>$o['sn']));
                                break;
                             case 'cod': 
                    			$orders[$key]['cod_url'] = $this->generatePayUrl('Fxs/Fxs/cod_pay', array('order_sn'=>$order['sn']));
                    			break;
							 case 'unionpay': 
                    			$orders[$key]['unionpay_url'] = $this->generatePayUrl('Fxs/Shop/unionpay', array('order_sn'=>$order['sn'], 'order_amount'=>$order_amount));
                    			break;
                    		case 'wingpay': 
								$orders[$key]['wingpay_url'] = $this->generatePayUrl('Wap/Shop/wingpay', array('order_sn'=>$order['sn'], 'type'=>'orders'));
								break;
                        }
                    }
                }
            }
            $this->assign('orders',$orders);
			 $this->assign('totalcount',$totalcount);
            $this->assign('ordersCount',count($orders));
        }
        
        $this->assign('metaTitle','我的订单');
        $this->display();
    }
	//分销商订单详细
	public function orderMyDetail(){
		$this->openid=$_GET['openid'];
		$fxs_id=$this->_get('id');
		$order_id=$_GET['order_id'];

		//$where['id']=$this->_get('id');
        //parent::checkAction("Shop-order");		
        $order_db = M('b2c_order');      
 
		$order=$order_db->where("wecha_id='$this->openid'")->select();
		$fxs_id=$order[0]['fxs_id'];		
        //$where = array('token'=> $this->token);
        $orders = null;
		$count      = $order_db->where($where)->count();
		$Page       = new Page($count,20);
		$show       = $Page->show();
		//$orders     = $order_db->where($where)->order('status ASC, update_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$sql = "SELECT  k.*"
					." from tp_b2c_order as k LEFT JOIN tp_reguser as r on r.id = k.fxs_id "				
					." where k.token='$this->token' AND k.order_id='$order_id'";
		$sql .= " ORDER BY k.status,k.update_time DESC ";						
		$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
		$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
		$orders = $Model->query($sql);

		// 发货详细信息
        if(isset($orders[0]['status']) && $orders[0]['status'] == '3') {
            $logistics = M('b2c_logistics')->where(array('order_id'=>$order_id))->find();
            if(!empty($logistics)) {
                $this->assign('logistics', $logistics);
            }
        }
		//取消订单
		$cancel_url = $this->generateUrl('Fxs/cancelOrder', array('oid'=>$orders[0]['sn'],'wecha_id'=>$this->openid,'token'=>$this->token));
        $this->assign('cancel_url',$cancel_url);
		//确认收货
		$confirm_url = $this->generateUrl('Fxs/confirmOrder', array('oid'=>$orders[0]['sn'],'wecha_id'=>$this->openid,'token'=>$this->token));
        $this->assign('confirm_url',$confirm_url);
		//pr($orders);
		//商品信息
		 $products = $Model->query("select i.product_id, i.count, p.`name`, p.logo_url, i.price from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order_id." and i.token='$this->token'");
		 //var_dump( $products);
		// exit();
        $this->assign('order',$orders);
		$this->assign('products',$products);

        $this->assign('page',$show);
        $this->display();
	}
	//查询微信客户订单信息
	public function my()
    {	
		$this->openid = session('openid_'.$this->token);
		if(!$this->openid){
			header('Location:'.U('Fxs/Oauth2', array('token'=>$this->token,'method_name'=>'my')));
		}
		
        $order_db = M('b2c_order');
		$order=$order_db->where("wecha_id='$this->openid'")->select();
		$fxs_id=$order[0]['fxs_id'];
		
        if (!empty($this->openid)) 
        {
            $order_where = array('token'=>$this->token, 'wecha_id'=>$this->openid, 'status' => array('neq', $this->ORDER_STATUS_SHOP_CANCELED) );
            $orders    = $order_db->where($order_where)->order('create_time DESC')->select();
			$totalcount=1;
			foreach ($orders as $key => $o){
				$totalcount=$o['price'];
			}
            if ($orders)
            {
                foreach ($orders as $key => $o)
                {
					$order_id=$o['order_id'];				
                    $orders[$key]['url'] = $this->generateUrl('Fxs/orderDetail',array('openid'=>$this->openid,'order_id'=>$order_id)); 
					$order_amount = $o['price'];
                    if ($o['status'] == 1) 
                    {
                    		$backurl = urlencode(C('site_url').'/index.php?g=Fxs&m=Fxs&a=my&token='.$o['token']. '&bid='.$o['branch_id']);
	                        switch($o['payment']) {
	                            case 'alipay':
	                                $orders[$key]['alipay_url'] = $this->generateUrl('ShopPay/startAlipay',array('order_sn'=>$o['sn'], 'pay_type'=>'0', 'bid'=>$o['branch_id'], 'front_url'=>$backurl)); 
	                                break;
	                             case 'cftpay': 
	                                $orders[$key]['cftpay_url'] = $this->generatePayUrl('cftpay/pay', array('order_sn'=>$o['sn'], 'pay_type'=>'0', 'bid'=>$o['branch_id'], 'front_url'=>$backurl));
	                                break;
	                             case 'cod': 
	                                $orders[$key]['cod_url'] = $this->generatePayUrl('Wap/Shop/cod_pay', array('order_sn'=>$o['sn'], 'pay_type'=>'0', 'bid'=>$o['branch_id'], 'front_url'=>$backurl));
	                                break;
	                            case 'wxpay':
	                                $orders[$key]['wxpay_url'] = $this->generatePayUrl('wxpay/pay',array('order_sn'=>$o['sn'], 'pay_type'=>'0', 'bid'=>$o['branch_id'], 'front_url'=>$backurl)); 
	                                break;
								case 'unionpay': 
	                                $orders[$key]['unionpay_url'] = $this->generatePayUrl('unionpay/pay', array('order_sn'=>$o['sn'], 'pay_type'=>'0', 'bid'=>$o['branch_id'], 'front_url'=>$backurl));
	                                break;
	                            case 'wingpay': 
									$orders[$key]['wingpay_url'] = $this->generatePayUrl('wingpay/pay', array('order_sn'=>$o['sn'], 'pay_type'=>'0', 'bid'=>$o['branch_id'], 'front_url'=>$backurl));
									break;
	                        }
                    }
                }
            }
            $this->assign('orders',$orders);
			$this->assign('totalcount',$totalcount);
            $this->assign('ordersCount',count($orders));
        }
        
        $this->assign('metaTitle','我的订单');
        $this->display();
    }
    
    
	function cod_pay(){
    	/*$order_sn = $_GET['order_sn'];
    	$where = array('sn'=>$order_sn,'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        $order = M('b2c_order')->where($where)->find();
    	//检查权限
    	if (!$this->wechat_id) {
    		$this->wechat_id = session('wechat_id_'.$this->token);
    	}
        if ( $order == null || $order['wecha_id'] != $this->wechat_id  || $order['status'] != 1)
        {
            $url = $this->generateUrl('Fxs/Fxs/orderDetail', array('oid' => $order_sn));
            $this->error("您不能付款，请联系客服。", $url);
        }
    	M('b2c_order')->where($where)->save(array('status' => $this->ORDER_STATUS_PAYED));*/
    	$redirect = $this->generateUrl('Fxs/my');
        $this->redirect($redirect);
    }
    
    
	//查询微信客户端订单详细
	public function orderDetail()
    {	
		$this->openid=$_GET['openid'];
		$fxs_id=$this->_get('id');
		$order_id=$_GET['order_id'];
		$sql = "SELECT  k.*"
					." from tp_b2c_order as k LEFT JOIN tp_reguser as r on r.id = k.fxs_id "				
					." where k.token='$this->token' AND k.wecha_id='$this->openid' AND k.order_id='$order_id'";
		$sql .= " ORDER BY k.status,k.update_time DESC ";						
		$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
		$orders = $Model->query($sql);	
		
    	if ($orders && $orders[0]['status'] == 1){
    		$order_amount = $orders[0]['price'];
            $backurl = urlencode(C('site_url').'/index.php?g=Fxs&m=Fxs&a=my&token='.$orders[0]['token']. '&bid='.$orders[0]['branch_id']);
			switch($orders[0]['payment']) {
			    case 'alipay':
			        $orders[0]['alipay_url'] = $this->generateUrl('ShopPay/startAlipay',array('order_sn'=>$orders[0]['sn'], 'pay_type'=>'0', 'bid'=>$orders[0]['branch_id'], 'front_url'=>$backurl)); 
			        break;
			    case 'cftpay': 
			        $orders[0]['cftpay_url'] = $this->generatePayUrl('cftpay/pay', array('order_sn'=>$orders[0]['sn'], 'pay_type'=>'0', 'bid'=>$orders[0]['branch_id'], 'front_url'=>$backurl));
			        break;
			    case 'cod': 
			        $orders[0]['cod_url'] = $this->generatePayUrl('Wap/Shop/cod_pay', array('order_sn'=>$orders[0]['sn'], 'pay_type'=>'0', 'bid'=>$orders[0]['branch_id'], 'front_url'=>$backurl));
			        break;
			    case 'wxpay':
			        $orders[0]['wxpay_url'] = $this->generatePayUrl('wxpay/pay',array('order_sn'=>$orders[0]['sn'], 'pay_type'=>'0', 'bid'=>$orders[0]['branch_id'], 'front_url'=>$backurl)); 
			        break;
			    case 'unionpay': 
			        $orders[0]['unionpay_url'] = $this->generatePayUrl('unionpay/pay', array('order_sn'=>$orders[0]['sn'], 'pay_type'=>'0', 'bid'=>$orders[0]['branch_id'], 'front_url'=>$backurl));
			        break;
			    case 'wingpay': 
			        $orders[0]['wingpay_url'] = $this->generatePayUrl('wingpay/pay', array('order_sn'=>$orders[0]['sn'], 'pay_type'=>'0', 'bid'=>$orders[0]['branch_id'], 'front_url'=>$backurl));
			        break;
			 }   					
        }
		
		// 发货详细信息
		if(isset($orders[0]['status']) && ( $orders[0]['status'] == '3' || ($orders[0]['status'] == 2 && $orders[0]['payment'] == 'cod'))) {
            $logistics = M('b2c_logistics')->where(array('order_id'=>$order_id))->find();
            if(!empty($logistics)) {
                $this->assign('logistics', $logistics);
            }
        }
		//取消订单
		$cancel_url = $this->generateUrl('Fxs/cancelOrder', array('oid'=>$orders[0]['sn'],'wecha_id'=>$this->openid,'token'=>$this->token));
        $this->assign('cancel_url',$cancel_url);
		//确认收货
		$confirm_url = $this->generateUrl('Fxs/confirmOrder', array('oid'=>$orders[0]['sn'],'wecha_id'=>$this->openid,'token'=>$this->token,'pay'=>$orders[0]['payment']));
        $this->assign('confirm_url',$confirm_url);
		//pr($orders);
		//商品信息
		 $products = $Model->query("select i.product_id, i.count, p.`name`, p.logo_url, i.price from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order_id." and i.token='$this->token'");
		 //var_dump( $products);
		// exit();
        $this->assign('order',$orders);
		$this->assign('products',$products);
        $this->display();
    }
	
	//取消订单
	public function cancelOrder()
    {
        /*if ($this->is_need_auth) 
        {
            $this->error("您还未登录。");
        }*/
		$token=$this->_get('token');
        $order_sn = intval($_GET['oid']);
		
        $order_db      = M('b2c_order');

        $where = array('sn'=>$order_sn,'token'=>$token);
        $order = $order_db->where($where)->find();
		
		
		
        //检查权限
        /*if ( $order == null || $order['wecha_id'] != $this->wechat_id  || $order['status'] != 1)
        {
            $url = $this->generateUrl('Fxs/orderDetail', array('oid'=>$order[0]['sn'],'wecha_id'=>$this->wechat_id,'token'=>$token));
            $this->error("您的订单不能取消了，请联系客服。", $url);
			 
        }*/

        $order_items_db = M('b2c_order_item');
        $this->ORDER_STATUS_CANCELED='4';
		
		//exit();
        //删除订单和订单列表

		if($order['wecha_id'] != null){
			//exit($order['wecha_id']);
			$res=$order_db->where($where)->save(array('status' => $this->ORDER_STATUS_CANCELED));
			$url = $this->generateUrl('Fxs/my', array('oid'=>$order_sn,'wecha_id'=>$order['wecha_id'],'token'=>$token));
            $this->success("您的订单已经取消成功。", $url);
		}

        //商品销量做相应的减少
        /*$ordered_items = $order_items_db->where(array('order_id'=>$order['order_id']))->select();
        
        $product_db   = M('b2c_product');
        foreach ($ordered_items as $k=>$item)
        {
            $product_db->where(array('product_id'=>$item['product_id']))->setDec('sale_count', $item['count']);
        }
        $redirect = $this->redirect('Fxs/my');
        $this->redirect($redirect);*/
    }
	//确认收货
	public function confirmOrder()
    {
       /* if ($this->is_need_auth) 
        {
            $this->error("您还未登录。");
        }*/
		$token=$this->_get('token');
        $order_sn = intval($_GET['oid']);
        $order_db      = M('b2c_order');

        $where = array('sn'=>$order_sn,'token'=>$token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        $order = $order_db->where($where)->find();
		
		/*
        //检查权限
        if ( $order == null || $order['wecha_id'] != $this->wechat_id  || $order['status'] != 3)
        {
            $url = $this->generateUrl('Fxs/my', array('oid'=>$order_sn,'wecha_id'=>$order['wecha_id'],'token'=>$token));
            $this->error("您不能确认收货，请联系客服。", $url);
        }*/
		if($order['wecha_id'] != null){
			//exit($order['wecha_id']);
			$status = $this->ORDER_STATUS_CONFIRM;
	        if ($_GET['pay'] == 'cod') {
	        	$status = $this->ORDER_STATUS_PAYED;
	        }
			$res=$order_db->where($where)->save(array('status' => $status));
			$url = $this->generateUrl('Fxs/my', array('oid'=>$order_sn,'wecha_id'=>$order['wecha_id'],'token'=>$token));
            $this->success("您的订单已经确认收货成功。", $url);
		}
    }
	
	//查询订单详细
	public function order()
    {
        parent::checkAction("Shop-order");
        $sn = $_GET['order_sn'];
    	$order_db 	= M('b2c_order');
        $order_where = array('token'=>$this->token, 'sn'=> $sn);
        if (!empty($this->branch_id)) 
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        $order    	= $order_db->where($order_where)->find();
		$sql = "SELECT p.name ,k.* "
								." from tp_b2c_order as k LEFT JOIN tp_partner as p on p.id=k.partner_id"
								." where k.token='$this->token' AND k.sn='$sn'";	
		$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $orders = $Model->query($sql);
        $this->assign('orders',$orders);
        if ($order) 
        {
            $this->assign('order',$order);
            $order_id = $order['order_id'];

            //已支付的交易
            $order_trade_db     = M('b2c_trade');
            $trade      = $order_trade_db->where(array('order_id'=>$order_id, 'token' => $this->token, 'status'=>2))->find();
            $this->assign('trade',$trade);

            $order_logistics_db = M('b2c_logistics');
            $logistics  = $order_logistics_db->where(array('order_id'=>$order_id, 'token' => $this->token))->find();
            $this->assign('logistics',$logistics);

            $Model = new Model();
            $items = $Model->query("select i.product_id, i.count, p.`name`, p.logo_url, i.price from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order_id." and i.token='$this->token'");
            $this->assign('products',$items);
            
            $amount         = 0;
            $total_count    = 0;

            foreach ($items as $k=>$c)
            {
                $price = $c['price'];
                $count = $c['count'];

                $amount += $price * $count;
                $total_count += $count;
            }

            $this->assign('order_amount',$amount);
            $this->assign('order_item_count',$total_count);

            if ($_GET['type'] == 'deliveryNote') 
            {
                $this->display('deliveryNote');
            }
            else
            {
                $this->display();
            }
        }
        else
        {
            exit;
        }
	}

    public function signin()
    {
        if (IS_POST) 
        {		
            $user_db = M('reguser');
            $user = $user_db->where(array('username'=> $_POST['user_name'], 'status'=>1))->find();
			if($user==null){
				$this->error('该账户还未激活',U('Fxs/Fxs/signin'));
			}
            if ($user) 
            {
                #verify password
				
                if ($this->_post('user_psw','intval,md5',0) == $user['password']) 
                {				
                    session_destroy();
                    session_start();             
                    $this->success('登陆成功',U('Fxs/Fxs/orderList',array('status'=>1,'id'=>$user['id'])));
                
                }
                else
                {
				
                    $this->error('密码错误，如忘记密码请联系管理员',U('Fxs/Fxs/signin'));
                }
            }
            
        }
        else
        {
            if ($code) 
            {
                $this->assign('merchant_code',$code);
            }
            $this->display();
        }
    }
	 function make_rand($length=32){//验证码文字生成函数
		$str="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$result="";
		for($i=0;$i<$length;$i++){
			$num[$i]=rand(0,25);
			$result.=$str[$num[$i]];
		}
		return $result;
	}
    
	public function verify(){
        //Image::buildImageVerify();
		$text = $this->make_rand(4);
		$_SESSION['verify'] = md5(strtolower($text));
        $im_x = 160;
		$im_y = 40;
		$im = imagecreatetruecolor($im_x,$im_y);
		$text_c = ImageColorAllocate($im, mt_rand(0,100),mt_rand(0,100),
		
		mt_rand(0,100));
		$tmpC0=mt_rand(100,255);
		$tmpC1=mt_rand(100,255);
		$tmpC2=mt_rand(100,255);
		$buttum_c = ImageColorAllocate($im,$tmpC0,$tmpC1,$tmpC2);
		imagefill($im, 16, 13, $buttum_c);
	
		$font = $_SERVER['DOCUMENT_ROOT'].'/tpl/'.MODULE_NAME.'/default/common/t1.ttf';
		for ($i=0;$i<strlen($text);$i++)
		{
			$tmp =substr($text,$i,1);
			$array = array(-1,1);
			$p = array_rand($array);
			$an = $array[$p]*mt_rand(1,10);//角度
			$size = 28;
			imagettftext($im, $size, $an, 15+$i*$size, 35, $text_c, $font, $tmp);
		}
	
	
		$distortion_im = imagecreatetruecolor ($im_x, $im_y);
	
		imagefill($distortion_im, 16, 13, $buttum_c);
		for ( $i=0; $i<$im_x; $i++) {
			for ( $j=0; $j<$im_y; $j++) {
				$rgb = imagecolorat($im, $i , $j);
				if( (int)($i+20+sin($j/$im_y*2*M_PI)*10) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*2*M_PI)*10) >=0 ) {
					imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*4) , $j , $rgb);
				}
			}
		}
		//加入干扰象素;
		$count = 160;//干扰像素的数量
		for($i=0; $i<$count; $i++){
			$randcolor = ImageColorallocate($distortion_im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
			imagesetpixel($distortion_im, mt_rand()%$im_x , mt_rand()%$im_y , $randcolor);
		}
	
		$rand = mt_rand(5,30);
		$rand1 = mt_rand(15,25);
		$rand2 = mt_rand(5,10);
		for ($yy=$rand; $yy<=+$rand+2; $yy++){
			for ($px=-80;$px<=80;$px=$px+0.1)
			{
				$x=$px/$rand1;
				if ($x!=0)
				{
					$y=sin($x);
				}
				$py=$y*$rand2;
	
				imagesetpixel($distortion_im, $px+80, $py+$yy, $text_c);
			}
		}
	
		//设置文件头;
		Header("Content-type: image/JPEG");
	
		//以PNG格式将图像输出到浏览器或文件;
		ImagePNG($distortion_im);
	
		//销毁一图像,释放与image关联的内存;
		ImageDestroy($distortion_im);
		ImageDestroy($im);
    }
	//分销商注册
	public function selectreg(){
		$this->display();
	}
	public function returninfo(){
		$id=$this->_get('id');
		$reguser_db=D("reguser");
		$info=$reguser_db->where("id='$id'")->select();
		$this->assign('info',$info);
		$this->display();
	}
	public function personalreg(){
	
		$this->display();
	}
	public function customersreg(){
		$this->display();
	}
	//分销商编码
	private function get_rand_num(){
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $rand_id = mt_rand(1, 99999);
        return $rand_id+100000;
    }
	function createOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = self::APPID;
		$urlObj["redirect_uri"] = urlencode($redirectUrl);
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
		
		
	}
	
	public function checkreg(){
		$img=session('headimgurl');
		
		$num=$_POST['num'];
			
		$partner_db=D('partner');
		$partner=$partner_db->where("num='$num'")->select();
		
		if($partner==null&&$_GET['personalreg']){
			$this->error('此协同部门编码不存在，请核实后填写正确的编码');
		}
		$username=$_POST['username'];
		$reg_db=D('reguser');
		$list=$reg_db->where("username='$username'")->select();
		if($list){
			$this->error('此账号已被注册，请您重新输入');
		}
		
		session_cache_limiter( "private, must-revalidate" );
		
		$p_id=$this->_get('partner_id');
		$token=$this->_get('token');
		
		
		
		if(IS_POST){
		
			$code = md5(strtolower($this->_post('vercode')));
		
			if($_GET['ver']&&$code != $_SESSION['verify']){

				$this->error('验证码错误',U('Fxs/personalreg',array('ver'=>$_GET['ver'])));
			}
			
			if($_GET['ver']=='customersreg'&&$code != $_SESSION['verify']){
				$this->error('验证码错误',U('Fxs/customersreg',array('ver'=>$_GET['ver'])));
			}
			
			$reguser_db = D("reguser"); 
			$data = array(
				'headimgurl'=>session('headimgurl'),
				'openid'=>session('openid'),
				'token'=>$this->token,
				'pid'=>$p_id,
				'num'=>$_POST['num'],
				'code'=>$this->get_rand_num(),
				'username'=>$this->_post('username'),
				'company'=>$this->_post('company'),
				'password'=>$this->_post('password','intval,md5',0),
				'mb'=>$this->_post('mb'),
				'email'=>$this->_post('email'),
				'idnum'=>$this->_post('idnum'),
				'wxnumber'=>$this->_post('wxnumber'),
				'province'=>$this->_post('province'),
				'city'=>$this->_post('city'),
				'area'=>$this->_post('area'),
				'address'=>$this->_post('address'),
				'publicnumber'=>$this->_post('publicnumber'),
				'license_logo'=>$this->_post('license_logo'),
				'tenpay'=>$this->_post('tenpay'),
				'alipay'=>$this->_post('alipay'),
				'truename'=>$this->_post('truename'),
				'status'=>'0',
				'create_time'=>time()
			);
			
			$id=$reguser_db->add($data);
			if($id){
				$this->success('注册成功', U('Fxs/returninfo',array('id'=>$id)));
			}else{
				$this->error($reguser_db->getError());
			}
		}else
		{
			$this->display();
		}
	
	}
	//加载服务条款
	public function legal(){
		$this->display();
	}
	public function logout()
    {
        session_destroy();
        $this->redirect('Manage/Index/signin');
    }
	
	//分销商登录
	public function login(){		
	if (IS_POST) 
        {		
            $user_db = M('reguser');
            $user = $user_db->where(array('username'=> $_POST['user_name'], 'status'=>1))->find();
			
			if($user==null){
				$this->error('该账户还未激活',U('Fxs/Fxs/login'));
			}
			
            if ($user) 
            {
                #verify password
				
                if ($this->_post('user_psw','intval,md5',0) == $user['password']) 
                {				
                    session_destroy();
                    session_start();             
                    $this->success('登陆成功',U('Fxs/Fxs/my',array('status'=>1,'id'=>$user['id'])));
                
                }
                else
                {
				
                    $this->error('密码错误，如忘记密码请联系管理员',U('Fxs/Fxs/login'));
                }
            }
            
        }
        else
        {
            $this->display();
        }
	}
	
	
	protected function getSideMenu() 
    {
        Log::record("act list:".print_r(session('manage_act_list'),true),Log::INFO);
        $action_list = explode(',', session('manage_act_list'));
        $sideMenu = array();

        // 功能 =》 模块 =》 模块入口
        $modules = C('manage_modules');

        foreach ($action_list as $key => $value) 
        {
            $codes = explode('-', $value);
            if ($codes && count($codes) == 2) 
            {
               if (empty($sideMenu[$codes[0]])){
                   $sideMenu[$codes[0]] = array();
               }                
                $sideMenu[$codes[0]][$codes[1]]['default_entry_url'] = $modules[$codes[0]][$codes[1]]['default_entry_url'];                
                $sideMenu[$codes[0]][$codes[1]]['selected']=  (MODULE_NAME == $codes[0])?(in_array(ACTION_NAME, $modules[$codes[0]][$codes[1]]['active_action_set'])?1:0):0;         
                        
                 }
        }
                return $sideMenu;               
    }

    protected function checkAction($action)
    {
        $action_list = explode(',', session('manage_act_list'));
        if (!in_array($action, $action_list)) 
        {
            $this->error('请联系管理员开通权限',U('Manage/Index/signin'));
        }
    }
	//审核分销商list页面
	public function fxs_list(){
		//检查是否登陆，初始化操作员名称和商户token等信息，初始化菜单栏
        if (isset($_POST['param1']))
        // hack for uploadify flash 302 error without session-id
        {  
            $sid = base64_decode($_POST['param1']);
            if (session_id() != $sid) 
            //destroy the automatical generated one or it won't be resumed to old one;
            {
                session_destroy(); 
            }
            session_id($sid);
            session_start();
        }


        //未登录用户
        if (session('manage_user_name') == false 
            || session('manage_company_token') == false ) 
        {
            $this->redirect('Manage/Index/signin');
        } 
        else
        {
            $this->user_name = session('manage_user_name');
            $this->token     = session('manage_company_token');
        }
	
        //get the sidebar according to the user's action list
        $this->assign('sideMenus',$this->getSideMenu());
        $this->assign('menuLang', C('manage_menu_lang'));

        $this->assign('page_title', session('manage_merchant'));
		
		$where['token']=$this->token;
		//$where['status']=0;
		
		$db=D('reguser');		
		if(IS_POST)
        {
            //搜索订单
            $key = $this->_post('searchkey');
            if(!empty($key))
            {
                $where['username|email|mb|num'] = array('like',"%$key%");
            }
			
            $orders_count = $db->where($where)->count();

            $Page   = new Page($orders_count,20);
            $show   = $Page->show();
			$info=$db->where($where)->order('id desc')->select();
			$sql = "SELECT p.name as pname,p.*,r.* "
							." FROM tp_reguser as r LEFT JOIN tp_partner as p on p.num = r.num "
							."where r.status=1 and r.token='$this->token'";
			   
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

			$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
			$list = $Model->query($sql);
			
               
        }else{			
			$sql = "SELECT p.name as pname,p.*,r.* "
							." FROM tp_reguser as r LEFT JOIN tp_partner as p on p.num = r.num "
							."where r.status=1 and r.token='$this->token'";
			   
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

			$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
			$list = $Model->query($sql);
			$count=$db->count();
			$page=new Page($count,25);
			$info=$db->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
			
		}
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->assign('info',$info);
		$this->display();
	}
	//审核分销商
	
	function fxs_check() {
		$where['id']=$this->_get('fxs_id');
		
		$reguser_db = D("reguser"); 
		//exit($where['id']);
		//session_start();
		
			
		if(IS_POST){
		
			$data = array(
				'mb'=>$this->_post('mb'),
				'email'=>$this->_post('email'),
				'idnum'=>$this->_post('idnum'),
				'wxnumber'=>$this->_post('wxnumber'),
				//'province'=>$this->_post('province'),
				//'city'=>$this->_post('city'),
				//'area'=>$this->_post('area'),
				'address'=>$this->_post('address'),
				'publicnumber'=>$this->_post('publicnumber'),
				'license_logo'=>$this->_post('license_logo'),
				'tenpay'=>$this->_post('tenpay'),
				'alipay'=>$this->_post('alipay'),
				'truename'=>$this->_post('truename'),
				'status'=>'1',
				'update_time'=>time()
				
			);
			
		
			$ret=$reguser_db->where($where)->save($data);
			if($ret !== false ){			
				$this->success('激活成功',U(MODULE_NAME.'/fxs_list'));
			}else{
				$this->error('激活失败',U(MODULE_NAME.'/fxs_list'));
			}
		}else{
			//检查是否登陆，初始化操作员名称和商户token等信息，初始化菜单栏
			if (isset($_POST['param1']))
			// hack for uploadify flash 302 error without session-id
			{  
				$sid = base64_decode($_POST['param1']);
				if (session_id() != $sid) 
				//destroy the automatical generated one or it won't be resumed to old one;
				{
					session_destroy(); 
				}
				session_id($sid);
				session_start();
			}
			//未登录用户
			if (session('manage_user_name') == false 
				|| session('manage_company_token') == false ) 
			{
				$this->redirect('Manage/Index/signin');
			} 
			else
			{
				$this->user_name = session('manage_user_name');
				$this->token     = session('manage_company_token');
			}
		
			//get the sidebar according to the user's action list
			$this->assign('sideMenus',$this->getSideMenu());
			$this->assign('menuLang', C('manage_menu_lang'));

			$this->assign('page_title', session('manage_merchant'));
			$count=$reguser_db->count();
			$page=new Page($count,25);
			$info=$reguser_db->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
			$this->assign('page',$show);
			$this->assign('info',$info);
			$this->display('fxs_check');
		}
	}
	
	
	private function generatePayUrl($addr, $params = null, $is_wxpay = false) {
//        $host_name = $this->merchant_code.C('shop_domain');
//        if(C('hornor_shop_domain') == 0) {
//            $host_name = C('wx_handler_server');
//        }
//        //支付url始终使用wx.lingzhtech.com
//        $host_name = C('wx_handler_server');
        
        $host_name = $_SERVER['SERVER_NAME'];
        if($is_wxpay) {
            //支付url始终使用wx.lingzhtech.com
            $host_name = C('wx_handler_server');
        }
        
        $wxpayUrl =  'http://'.$host_name.'/index.php/'.$addr;
        if(!empty($params)) {
            $params['token'] = $this->token;
            if (!$this->wechat_type || !$this->is_authed) 
            {
                //如果不是高级认证服务号，则参数中带上openid
                $params['wecha_id'] = $this->wechat_id;
            }
            $get_str = '';
            foreach($params as $k => $v) {
                $get_str .= $k.'='.$v.'&';
            }
            $get_str = trim($get_str, '&');
            
            $wxpayUrl  .= '?'.$get_str;
        }
        
        return $wxpayUrl;
    }
	
}

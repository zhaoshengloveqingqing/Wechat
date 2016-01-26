<?php
 header("Content-Type: text/html; charset=utf-8");
class ShopAction extends ManageAction
{
    private $branch_id;
    protected function _initialize()
    {
        parent::_initialize();
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
    }
	
	public function order()
    {
        parent::checkAction("Shop-order");
        $sn = $_GET['order_sn'];
    	$order_db 	= M('b2c_order');
        $where = array('token'=>$this->token, 'sn'=> $sn);
        if (!empty($this->branch_id)) 
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        $order    	= $order_db->where($where)->find();
		$sql = "SELECT r.truename as pname,r.mb as fxsmb,r.address as fxsaddress, k.*"
								." from tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id"
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

    public function cancel ()
    {
        parent::checkAction("Shop-order");
        $order_sn = intval($_POST['order_sn']);
        $order_db = M('b2c_order');

        $order_where = array('sn'=>$order_sn, 'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        if (IS_POST)
        {
            $order = $order_db->where($order_where)->find();
            if ($order) 
            {
                $ret = $order_db->where($order_where)->save(array('status'=>5));
                if ($ret) 
                {
                    $this->success('订单取消成功');
                } 
                else 
                {
                    $this->success('订单取消失败');
                }
            }
        }
    }


    public function delivery() 
    {
        parent::checkAction("Shop-order");
        $order_sn = $_POST['order_sn'];
        $order_db = M('b2c_order');

        $order_where = array('sn'=>$order_sn, 'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        if (IS_POST)
        {
            $order = $order_db->where($order_where)->find();
            if($order && $order['is_audited'] !=1 ){
            	$this->error('该订单未审核，不能发货');
            }
            if ($order) 
            {
                $ret = false;

                if ($order['status'] == 2 || $order['payment'] == 'cod' && $order['status'] == 1) 
                {
                	if (!$_POST['logistics_name'] || !$_POST['logistics_no']) {
                		 $this->error('请完善发货明细');
                		 exit();
                	}
                    //根据付款方式（货到付款和第三方支付）判断是否能发货
                    $ret = $order_db->where($order_where)->save(array('status'=>3));
                    if ($ret) 
                    {
                        $data = array();
                        $data['type']           = 'express';
                        $data['name']           = $_POST['logistics_name'];
                        $data['fee']            = 0;
                        $data['create_time']    = time();
                        $data['logistics_no']   = $_POST['logistics_no'];
                        $data['order_id']       = $order['order_id'];
                        M('b2c_logistics')->data($data)->add();
                        $this->success('发货成功');
                    } 
                    else
                    {
                        $this->success('更新发货状态失败');
                    }
                }
                else
                {
                    $this->success('您的订单还不能发货，操作失败');
                }
            }
        }
    }

    public function signAudit(){
        $order_db = M('b2c_order');
        $a=array('success'=>$order_db->where(array('order_id'=>$_GET['order_id']))->save(array('is_audited'=>$_GET['sign_audit'])));
        echo json_encode($a);
    }

    public function audited_delivery()
    {
        parent::checkAction("Shop-audit");
        $order_sn = $_POST['order_sn'];
        $order_db = M('b2c_order');

        $order_where = array('sn'=>$order_sn, 'token'=>$this->token);
        if (!empty($this->branch_id))
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        if (IS_POST)
        {
            $order = $order_db->where($order_where)->find();
            if ($order)
            {
                $ret = false;

                if ( $order['status'] == 2 || $order['payment'] == 'cod' && $order['status'] == 1)
                {
                	
                	if (!$_POST['logistics_name'] || !$_POST['logistics_no']) {
                		 $this->error('请完善发货明细');
                		 exit();
                	}
                	
                    //根据付款方式（货到付款和第三方支付）判断是否能发货
                    $ret = $order_db->where($order_where)->save(array('status'=>3));
                    if ($ret)
                    {
                        $data = array();
                        $data['type']           = 'express';
                        $data['name']           = $_POST['logistics_name'];
                        $data['fee']            = 0;
                        $data['create_time']    = time();
                        $data['logistics_no']   = $_POST['logistics_no'];
                        $data['order_id']       = $order['order_id'];
                        M('b2c_logistics')->data($data)->add();
                        $this->success('发货成功');
                    }
                    else
                    {
                        $this->success('更新发货状态失败');
                    }
                }
                else
                {
                    $this->success('您的订单还不能发货，操作失败');
                }
            }
        }
    }
    
    
    function order_taocan (){
    	$where = array('sn'=>$this->_post('order_sn'), 'token'=>$this->token);
    	$order_audit = M('b2c_order')->where(array_merge($where, array('is_audited'=>1)))->find();
    	if ($order_audit) {
	    	if (!$this->_post('note')) {
	    		$this->error('请填写套餐信息');
	    	}
	    	$rs = M('b2c_order')->where($where)->save(array('note'=>$this->_post('note')));
			if ($rs){
			    $this->success('保存成功');
			}else{
			    $this->error('保存失败');
			}
    	}else{
    		 $this->error('该订单未审核，不能填写订单信息');
    	}
    }

    public function auditList()
    {

        parent::checkAction("Shop-audit");

        $order_db = M('b2c_order');

        $where = array('token'=> $this->token);
        /*if (!empty($this->branch_id))
        {
            $where['branch_id'] = $this->branch_id;
        }*/
        $orders = null;

        if(IS_POST)
        {
            //搜索订单
            $key = $this->_post('searchkey');
            if(!empty($key))
            {
                $where['truename|address|price|sn|tel'] = array('like',"%$key%");
            }
            $where['is_audited'] = 1;
            $orders_count = $order_db->where($where)->count();

            $Page   = new Page($orders_count,20);
            $show   = $Page->show();
            $sql = "SELECT r.truename as pname,k.*"
                ." FROM tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id LEFT JOIN tp_partner as p on p.num = r.num  "
                ."where k.token='$this->token'";
            if(!empty($key)){
                $sql .= ' AND (k.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'OR p.`name`LIKE\'%'.mysql_real_escape_string($key).'%\' OR p.`num` LIKE \'%'.mysql_real_escape_string($key).'%\'
				OR k.`tel` LIKE \'%'.mysql_real_escape_string($key).'%\' OR k.`sn` LIKE \'%'.mysql_real_escape_string($key).'%\'OR k.`status` LIKE \'%'.mysql_real_escape_string($key).'%\'
				)';
            }
            $sql .= ' AND k.is_audited=1';
            $sql .= " ORDER BY k.update_time desc ";
            $sql .= " limit ".$Page->firstRow.','.$Page->listRows;
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $orders = $Model->query($sql);

        }
        else
        {
            $where['is_audited'] = 1;
            $count      = $order_db->where($where)->count();
            $Page       = new Page($count,20);
            $show       = $Page->show();
            //$orders     = $order_db->where($where)->order('status ASC, update_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();

            $sql = "SELECT r.truename as pname,k.*"
                ." FROM tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id LEFT JOIN tp_partner as p on p.num = r.num  "
                ."where k.token='$this->token'";
            if(!empty($key)){
                $sql .= ' AND (k.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\' OR k.`address` LIKE \'%'.mysql_real_escape_string($key).'%\')';
            }
            $sql .= ' AND k.is_audited=1';
            $sql .= " ORDER BY k.update_time desc";
            $sql .= " limit ".$Page->firstRow.','.$Page->listRows;
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $orders = $Model->query($sql);

        }
        //pr($orders);
        $this->assign('orders',$orders);
        $this->assign('page',$show);
        $this->display();
    }

    public function audited_order()
    {
        parent::checkAction("Shop-audit");
        $sn = $_GET['order_sn'];
        $order_db 	= M('b2c_order');
        $order_where = array('token'=>$this->token, 'sn'=> $sn);
        if (!empty($this->branch_id))
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        $order    	= $order_db->where($order_where)->find();
        $sql = "SELECT p.name ,r.truename as pname, k.*"
            ." from tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id  LEFT JOIN tp_partner as p on p.id=k.partner_id"
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


	public function orderList()
    {
		
        parent::checkAction("Shop-order");

        $order_db = M('b2c_order');
            
        $where = array('token'=> $this->token);
        /*if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }*/
        $orders = null;

        if(IS_POST)
        {
            //搜索订单
            $key = $this->_post('searchkey');
            if(!empty($key))
            {
                $where['truename|address|price|sn|tel'] = array('like',"%$key%");
            }
            $orders_count = $order_db->where($where)->count();

            $Page   = new Page($orders_count,20);
            $show   = $Page->show();
            $sql = "SELECT r.truename as pname,k.*"
							." FROM tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id LEFT JOIN tp_partner as p on p.num = r.num  "
							."where k.token='$this->token'";
			if(!empty($key)){
				$sql .= ' AND (k.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'OR p.`name`LIKE\'%'.mysql_real_escape_string($key).'%\' OR p.`num` LIKE \'%'.mysql_real_escape_string($key).'%\'
				OR k.`tel` LIKE \'%'.mysql_real_escape_string($key).'%\' OR k.`sn` LIKE \'%'.mysql_real_escape_string($key).'%\'OR k.`status` LIKE \'%'.mysql_real_escape_string($key).'%\'
				)';
			}
			$sql .= " ORDER BY k.update_time desc ";
			$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
			$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
			$orders = $Model->query($sql);
               
        }
        else 
        {
            if (isset($_GET['status']))
            {
                $where['status'] = $_GET['status'];
			
				$status=$this->_get('status');
				
				$count      = $order_db->where($where)->count();
				$Page       = new Page($count,20);
				$show       = $Page->show();
				//$orders     = $order_db->where($where)->order('status ASC, update_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
				$sql = "SELECT r.truename as pname,k.*"
							." FROM tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id LEFT JOIN tp_partner as p on p.num = r.num  "
							."where k.token='$this->token' and k.status='$status'";
				if(!empty($key)){
					$sql .= ' AND (k.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'OR k.`status`LIKE\'%'.mysql_real_escape_string($key).'%\' OR p.`num` LIKE \'%'.mysql_real_escape_string($key).'%\')';
				}
				$sql .= " ORDER BY k.create_time desc ";						
				$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
				$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
				$orders = $Model->query($sql);
            }
			else{
                if(isset($_GET['is_audited'])){
                    $where['is_audited'] = $_GET['is_audited'];
                }
				$count      = $order_db->where($where)->count();
				$Page       = new Page($count,20);
				$show       = $Page->show();
				//$orders     = $order_db->where($where)->order('status ASC, update_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			
				$sql = "SELECT r.truename as pname,k.*"
							." FROM tp_b2c_order as k  LEFT JOIN tp_reguser as r on k.fxs_id=r.id LEFT JOIN tp_partner as p on p.num = r.num  "
							."where k.token='$this->token'";
				if(!empty($key)){
					$sql .= ' AND (k.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\' OR k.`address` LIKE \'%'.mysql_real_escape_string($key).'%\')';
				}
                if(isset($_GET['is_audited'])){
                    $sql .= ' AND k.is_audited='.$_GET['is_audited'];
                }
				$sql .= " ORDER BY k.update_time desc";						
				$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
				$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
				$orders = $Model->query($sql);
			}
			
        }
		//pr($orders);
        $this->assign('orders',$orders);
        $this->assign('page',$show);
        $this->display();
    }

    public function categoryList()
    {
        parent::checkAction("Shop-category");
        $category_db = M('b2c_category');

        $categories = null;
        if(IS_POST)
        {

            $map['token']         = $this->token; 
            if (!empty($this->branch_id)) 
            {
                $map['branch_id'] = $this->branch_id;
            }
            $map['status']        = 1; 
            $map['name|desc']     = array('like',"%$key%"); 

            $count  = $category_db->where($map)->count();  
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $categories   = $category_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            
            $where  = array('token' => $this->token, 'status' => 1);
            if (!empty($this->branch_id)) 
            {
                $where['branch_id'] = $this->branch_id;
            }
            $count  = $category_db->where($where)->count();
 
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $categories   = $category_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
            
        }

        $this->assign('page',$show);        
        $this->assign('categories',$categories);

        $this->display();        
    }

    public function category()
    {
        parent::checkAction("Shop-category");
    	$category_db    	= M('b2c_category');
        if(IS_POST)
        {
        	//添加商品分类
        	$category_id = $this->_post('cid', 'intval', -1);
        	if ($category_id == -1) 
            {
            	$data['token'] = $this->token;
                if (!empty($this->branch_id)) 
                {
                    $data['branch_id'] = $this->branch_id;
                }
	            $data['name'] = $_POST['name'];
	            $data['desc'] = $_POST['desc'];
	            $data['logo_url'] = $_POST['logo_url'];

	            $data['status'] = 1;
	            $data['create_time'] = time();
	            $data['update_time'] = $data['create_time'];
	           
	            $category_id = $category_db->add($data);

	            if ($category_id) 
	            {
	                $this->success('操作成功',U(MODULE_NAME.'/categoryList'));
	            } 
	            else
	            {
	               $this->error('操作失败',U(MODULE_NAME.'/category'));
	            }
            }
            else
            {
            	//更新商品分类
            	$where          = array('category_id'=> $category_id, 'token'=>$this->token);
                if (!empty($this->branch_id)) 
                {
                    $where['branch_id'] = $this->branch_id;
                }
	            $category       = $category_db->where($where)->find();
	            if ($category == false)
	            {
	                //找不到也是修改成功
	                Log::write("非法更新分类信息：category_id:".$category_id, Log::INFO);
	                $this->success('操作成功',U(MODULE_NAME.'/categoryList'));
	            }

                //更新
                $data['name']       = $_POST['name'];
                $data['desc']       = $_POST['desc'];
                $data['logo_url']   = $_POST['logo_url'];
	            $data['update_time']= time();

	            $ret = $category_db->where($where)->save($data);

	            if($ret)
	            {
	                $this->success('修改成功',U(MODULE_NAME.'/categoryList'));
	            }
	            else
	            {
	                Log::record("更新分类信息失败：category_id:".$category_id.'; error:'.$category_db->getError(), Log::INFO);
	                $this->error('操作失败');
	            }

            }
        }
        else
        {
        	$category_id = $this->_get('cid','intval', -1);
            $where = array('category_id'=>$category_id, 'token'=>$this->token);
            if (!empty($this->branch_id)) 
            {
                $where['branch_id'] = $this->branch_id;
            }
            $category = $category_db->where($where)->find();
            if($category)
            {
                $this->assign('category', $category);
            }
            
            $this->display('category');
        }
    }


    public function deleteCategory() 
    {
        parent::checkAction("Shop-category");
        $category_id = $this->_get('cid', 'intval', -1);
        $category_db        = M('b2c_category');

        $where          = array('category_id'=> $category_id, 'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }
        $data['status']     = 2;
        $data['update_time']= time();

        $ret = $category_db->where($where)->save($data);

        if($ret)
        {
            $this->success('修改成功',U(MODULE_NAME.'/categoryList'));
        }
        else
        {
            Log::record("更新分类信息失败：category_id:".$category_id.'; error:'.$category_db->getError(), Log::INFO);
            $this->error('操作失败');
        }

    }

    public function deleteProduct() 
    {
        parent::checkAction("Shop-product");
        $product_id = $this->_get('pid', 'intval', -1);
        $product_db        = M('b2c_product');

        $where          = array('product_id'=> $product_id, 'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        $data['status']     = 2;
        $data['update_time']= time();

        $ret = $product_db->where($where)->save($data);

        if($ret)
        {
            $this->success('修改成功',U(MODULE_NAME.'/productList'));
        }
        else
        {
            Log::record("更新分类信息失败：product_id:".$product_id.'; error:'.$product_db->getError(), Log::INFO);
            $this->error('操作失败');
        }

    }


    public function productList()
    {
        parent::checkAction("Shop-product");
        $product_db     = M('b2c_product');
        $category_db    = M('b2c_category');
        $cat_where = array('token'=>$this->token, 'status' => 1);
        if (!empty($this->branch_id)) 
        {
            $cat_where['branch_id'] = $this->branch_id;
        }
        $categories     = $category_db->where($cat_where)->select();

        if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
            if (empty($key))
            {
                $this->error("关键词不能为空");
            }

            $map['token']               = $this->token; 
            if (!empty($this->branch_id)) 
            {
                $map['branch_id'] = $this->branch_id;
            }
            $map['status']               = 1; 
            $map['name|intro|keyword']  = array('like',"%$key%"); 

            $product_count   = $product_db->where($map)->count(); 
            $Page   = new Page($product_count,50);
            //默认通过$_GET['p'] 获取当前页面
            $show   = $Page->show();
            $product_list   = $product_db->where($map)->order('category_id desc, product_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {

            $category_id = 0;
            if (isset($_GET['cid'])) 
            {
                $category_id = intval($_GET['cid'], 0);
            }

            $where = array('token'=>$this->token, 'status' => 1);
            
            $sql = "select p.product_id, p.category_id,p.`name`,p.market_price, p.shop_price,p.`status`,p.logo_url,p.intro, p.update_time,c.`name`  as category_name "
                        ." from tp_b2c_product as p LEFT JOIN tp_b2c_category as c on p.category_id = c.category_id "
                        ." where p.token='$this->token' and p.status=1 ";
            if ($category_id)
            {
                $where['category_id'] = $category_id;
                $sql .= " and c.category_id='$category_id'";
            }

            if (!empty($this->branch_id)) 
            {
                $where['branch_id'] = $this->branch_id;
                $sql .= " and c.branch_id='$this->branch_id'";
            }

            $count  = $product_db->where($where)->count();
            $Page   = new Page($count,50);
            $show   = $Page->show();

            $sql .= " limit ".$Page->firstRow.','.$Page->listRows;

            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $product_list = $Model->query($sql);
        }

        $this->assign('categories',$categories); 
        $this->assign('page',$show); 
        $this->assign('product_list',$product_list);
        
        $this->display();
    }

    /*
     * 产品 CRUD
     */
    public function product()
    { 

        parent::checkAction("Shop-product");
        $product_db       	= M('b2c_product');
        $category_db    	= M('b2c_category');
        if(IS_POST)
        {
        	$product_id = $this->_post('pid', 'intval', -1);

            if ($product_id == -1) 
            {
                //添加新产品
	            $product_id = $this->addProduct();
	            if ($product_id) 
	            {
	                $this->success('操作成功',U(MODULE_NAME.'/product', array('pid'=> $product_id)));
	            } 
	            else 
	            {
	                Log::record('添加信息失败 error:'.$product_db->getError(), Log::INFO);
	                $this->error('操作失败',U(MODULE_NAME.'/product'));
	            }
            }
            else
            {
            	//更新或删除商品
            	$ret = $this->updateProduct($product_id);
	            if ($ret == true) 
	            { 
	                $this->success('操作成功',U(MODULE_NAME.'/product', array('pid' => $product_id)));
	            }
	            else
	            {
	                Log::record($product_db->getError(), Log::INFO);
	                $this->error('操作失败');
	            }
                
	        }
        }
        else
        {
        	$product_id = $this->_get('pid','intval', -1); 
            $prod_where = array('product_id'=>$product_id, 'token' => $this->token);
            if (!empty($this->branch_id)) 
            {
                $prod_where['branch_id'] = $this->branch_id;
            }
            $product = $product_db->where($prod_where)->find();
            if ($product) 
            {
            	$this->assign('product',$product);
            }

            //分类
            $cat_where = array('token' => $this->token);
            if (!empty($this->branch_id)) 
            {
                $cat_where['branch_id'] = $this->branch_id;
            }
            $categories = M('b2c_category')->where($cat_where)->select();
            $this->assign('categories', $categories);
        }

        $this->display('product');
    }

    private function updateProduct($product_id)
    {

        parent::checkAction("Shop-product");
    	$where = array('product_id' => $product_id, 'token' => $this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }
        $product_db         = M('b2c_product');
	    $product = $product_db->where($where)->find();

	    if ($product == false)
	    {
	        //找不到也是修改成功
	        Log::record("非法更新商品信息：good_id:".$product_id, Log::INFO);
	        return true;
	    } 

        $category_id = intval($_POST['cid']); 
        $cat_where = array('category_id' => $category_id, 'token' => $this->token);
        if (!empty($this->branch_id)) 
        {
            $cat_where['branch_id'] = $this->branch_id;
        }
        $category = M('b2c_category')->where($cat_where)->find();
        if(empty($category))
        {
            $this->error("请先添加分类。",U(MODULE_NAME.'/category'));
        }

        $data['name']           = $_POST['name'];
        $data['market_price']   = $_POST['market_price'];
        $data['shop_price']     = $_POST['shop_price'];
        $data['intro']          = $_POST['intro'];
        $data['logo_url']       = $_POST['logo_url'];
        $data['keyword']        = $_POST['keyword'];
        $data['category_id']    = $_POST['cid'];
        $data['update_time']    = time();

        $ret = $product_db->where($where)->save($data);

        /*if ($ret == true) 
	            {              
	                //更新关键词
	                $kwds_db = M('keyword');
	                $kwds_db->where(array('pid' => $product['product_id'], 'token'=>$token ,'function'=>$this->function))->delete();
	 
	                $kwd_data['token']      = $token;
	                $kwd_data['module']     = 'product';
	                $kwd_data['type']       = 2;
	                $kwd_data['function']   = $this->function;
	                $kwd_data['pid']        = $product['product_id'];

	                $keywords = explode(' ',  $_POST['keyword']);
	                foreach($keywords as $vo) 
	                {
	                    $kwd_data['keyword'] = $vo;
	                    $kwds_db->add($kwd_data);
	                }
	           
	    }*/
	    return $ret;
    }

    private function addProduct()
    {
        parent::checkAction("Shop-product");

        $category_id = intval($_POST['cid']);
        $cat_where = array('category_id' => $category_id, 'token' => $this->token);
        if (!empty($this->branch_id)) 
        {
            $cat_where['branch_id'] = $this->branch_id;
        }
        $category = M('b2c_category')->where($cat_where)->find();
        if(empty($category))
        {
            $this->error("请先添加分类。",U(MODULE_NAME.'/category'));
        }

		$data['token']          = $this->token;
        if (!empty($this->branch_id)) 
        {
            $data['branch_id'] = $this->branch_id;
        }
        $data['name']           = $_POST['name'];
        $data['market_price']   = $_POST['market_price'];
        $data['shop_price']     = $_POST['shop_price'];
        $data['intro']          = $_POST['intro'];
        $data['logo_url']       = $_POST['logo_url'];
        $data['keyword']        = $_POST['keyword'];
        $data['category_id']    = $category_id;

        $data['status']         = 1;
        $data['create_time']    = time();
        $data['update_time']    = $data['create_time'];
           
        $product_db         = M('b2c_product');
        $product_id = $product_db->add($data);

 		/*if ($product_id) 
	    {
        	$keywords           = explode(' ',  $_POST['keyword']);
	                if (!empty($keywords)) 
	                {
	                    $kwds_db            = M('keyword');
	                    $kwd_data['uid']    = session('uid');
	                    $kwd_data['token']  = $token;
	                    $kwd_data['module'] = 'product';
	                    $kwd_data['type']   = 2;
	                    $kwd_data['function'] = $this->function;
	                    $kwd_data['pid']    = $product_id;
	                    foreach($keywords as $vo) 
	                    {
	                        $kwd_data['keyword'] = $vo  ;    
	                        $kwds_db->add($kwd_data);
	                    }
	                }
	    }*/
        return $product_id;
    }

    public function ajaxNewestOrder()
    {
        parent::checkAction("Shop-order");
        

        //只判断一天内没别读取过的
        $today = time();
        $last_of_24h = $today - 86400;

        $order_where = array('status'=>2,'readed'=>0, 'token' => $this->token, 'create_time'=>array('gt',$last_of_24h));
        if (!empty($this->branch_id)) 
        {
            $order_where['branch_id'] = $this->branch_id;
        }
        $order_db = M('b2c_order');
        $new_orders = $order_db->field("order_id,tel,truename,info,create_time,address,price,sn,payment")->where($order_where)->order('id DESC')->select();
        $tipMsgs = array();
        if (count($new_orders) > 0) 
        {
            $order_db->where($order_where)->save(array('readed'=>1));
            $orders_html_ary = array();
            $orders_print_ary = array();
            foreach ($new_orders as $key => $order) 
            {
                $Model = new Model();
                $items = $Model->query("select i.product_id, i.count, p.`name`, p.logo_url, i.price, p.intro as desc from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order['order_id']." and i.token='$this->token'");

                $order_items = array();
                for($j = 0; $j < count($items); $j++) 
                {
                    $order_items[$items[$j]['product_id']] = $menus[$j];
                }

                $order["items_list"] = $order_items;
                $order["href"] = U('Shop/order',array('oid'=>$order["sn"]));
                $order['format_submit_time'] = date("Y-m-d H:i:s",$order['create_time']);
                $orders_html_item = $this->formatOrder($order);
                $new_orders[$key]['html'] = $orders_html_item;
                $order_print_item = $this->formatPrintOrder($order);
                $new_orders[$key]['print_html'] = $order_print_item;
            }
            $now = time();
            $now_str = date("Y-m-d H:i:s",$now);

            $order_count = count($new_orders);
            $tipMsgs["num"] = $order_count;
            $tipMsgs["info"] = "您有 $order_count 个新订单，最后更新时间为$now_str";
            $tipMsgs["type"] = "orders";
            $tipMsgs["num"] = count($new_orders);
            $tipMsgs["orders"] = $new_orders;
            $this->ajaxReturn($tipMsgs, "OK", 1);
        }
        else
        {
            $now = time();
            $now_str = date("Y-m-d H:i:s",$now);
            $tipMsgs["info"] = "没有新的订单，最后更新时间为".$now_str;
            $tipMsgs["type"] = "orders";
            $tipMsgs["num"]  = 0;
            $tipMsgs["orders_html"] = '';
            $this->ajaxReturn($tipMsgs, "No new order", 0);
        }
        
    }

    private function formatPrintOrder($order)
    {
        $order_tmpl ='<div id="page#idx#" class="print-content" style="width:100%;">
        <div>=================================</div>
        <h4>#merchant_name#</h4>
        <div>=================================</div>
        <div class="title">订单号：</div><div class="content">#sn#</div>
        <div class="title">下单时间：</div><div class="content">#submittime#</div>
        <div class="title">订购人：</div><div class="content">#username#</div>
        <div class="title">电话：</div><div class="content">#address#</div>
        <div class="title">地址：</div><div class="content">#table#</div>
        <div>==================================</div>
        <h4>已购产品</h4>
        <div class="title" style="width:40%;">名称</div>
        <div class="content" style="margin-left:5px;width:35%;text-align:center">单价(元)</div>
        <div class="content" style="text-align:center;width:20%;">数量</div>
        #item_list#
        <div class="content" style="width:100%;margin-top:10px;text-align:right">总价：<span style="color:#f30;font-size:16px;font-weight:bold">#price#</span>元</div>
        <div class="title" style="width:180px;margin-top:80px;margin-bottom:60px;">签字:_________________</div></div>';

        $item_tmpl = '<div class="title" style="width:40%;"> #name#</div>
          <div class="content"style="margin-left:5px;width:35%;text-align:center;">#price#</div>
          <div class="content" style="width:20%;text-align:center;">#nums#</div>';

        
        $order_tmpl = str_replace("#sn#",         $order['sn'],           $order_tmpl);
        $order_tmpl = str_replace("#submittime#", $order['format_submit_time'],  $order_tmpl);
        $order_tmpl = str_replace("#username#",   $order['truename'],     $order_tmpl);
        $order_tmpl = str_replace("#tel#",        $order['tel'],          $order_tmpl);
        $order_tmpl = str_replace("#address#",    $order['address'],     $order_tmpl);
        $order_tmpl = str_replace("#price#",      $order['price'],        $order_tmpl);

        $order_tmpl = str_replace("#merchant_name#",         $this->branch_name,           $order_tmpl);

        $item_ary_str = '';

        foreach ($order['items_list'] as $key => $item) 
        {
            $item_str = $item_tmpl;
            $item_str = str_replace("#name#",       $item['name'],  $item_str);
            $item_str = str_replace("#nums#",       $item['count'],  $item_str);
            $item_str = str_replace("#price#",      $item['price'], $item_str);
            $item_ary_str .= $item_str;
        }

        $order_tmpl = str_replace("#item_list#",       $item_ary_str,         $order_tmpl);

        return $order_tmpl;
    }

    private function formatOrder($order)
    {

        $order_tmpl ='<div class="panel panel-primary order-panel"   order-sn="#sn#" style="display:none">
            <div class="panel-heading">
              <div class="row">
                <div class="col-lg-12">
                  <h3 class="panel-title"><a href="#href#" target="_blank" class="btn btn-link">订单#sn#</a></h3>
                </div>
              </div>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-lg-4" style="border-right:1px solid #ddd">
                  <table class="">
                    <tbody>
                    <tr><th width="50%" align="center" >下单日期：</th> <td>#submittime#</td></tr>
                    <tr><th width="50%" align="center" >订购人：</th> <td> #username#</td></tr>
                    <tr><th width="50%" align="center" >电话：</th> <td>#tel#</td></tr>
                    <tr><th width="50%" align="center" >地址：</th> <td>#address#</td></tr>
                    <tr><th width="50%" align="center" >总价：</th> <td><span style="color:#f30;font-size:16px;font-weight:bold">#price#</span>元</td></tr>
                  </table>
                </div>
                <div class="col-lg-8">
                  <table class="table">
                    <thead>
                    <tr>
                      <th width="50%" align="center" style="text-align:center">产品</th>
                      <th class="20%" align="center" style="text-align:center">数量</th>
                      <th width="30%" align="center" style="text-align:center">单价（元）</th>
                    </tr>
                    </thead>
                    <tbody>
                    #item_list#
                    </tbody>
                  </table>
                </div>
               
              </div>
            </div>
          </div>';
        $item_tmpl = '<tr><td align="center">#name#</td><td align="center">#desc#</td><td align="center">#nums#</td><td align="center">#price#</td></tr>';
    
        $order_tmpl = str_replace("#sn#",         $order['sn'],           $order_tmpl);
        $order_tmpl = str_replace("#href#",       $order['href'],         $order_tmpl);
        $order_tmpl = str_replace("#submittime#", $order['format_submit_time'],   $order_tmpl);
        $order_tmpl = str_replace("#username#",   $order['truename'],     $order_tmpl);
        $order_tmpl = str_replace("#tel#",        $order['tel'],          $order_tmpl);
        $order_tmpl = str_replace("#price#",      $order['price'],        $order_tmpl);

        $item_ary_str = '';

        foreach ($order['items_list'] as $key => $item) 
        {
            $item_str = $item_tmpl;
            $item_str = str_replace("#name#",       $item['name'],  $item_str);
            $item_str = str_replace("#desc#",       $item['desc'],  $item_str);
            $item_str = str_replace("#nums#",       $item['count'], $item_str);
            $item_str = str_replace("#price#",      $item['price'], $item_str);
            $item_ary_str .= $item_str;
        }

        $order_tmpl = str_replace("#item_list#",       $item_ary_str,         $order_tmpl);

        return $order_tmpl;
    }
	//修改佣金
	function commission(){
		$order_db  = M('b2c_order');
        $order_where = array('order_id'=>$_GET['oid'], 'token' => $this->token);
		
        $order  = $order_db->where($order_where)->find();
		$commission = $this->_post('commission');

           
        if (IS_POST) 
        {
		
             if (empty($commission))
            {
			
                $this->error("佣金不能为空");
            }
            $ret = $order_db->where($order_where)->save(array('commission' => $_POST['commission']));
			
            if ($ret) {
               $this->success('操作成功',U(MODULE_NAME.'/orderList'));
            } else  {
               $this->success('操作成功',U(MODULE_NAME.'/orderList'));
            }
        } 
        else
        {
			$this->assign('order',$order);
			$this->display();
        }
		
	
	}
	//添加商户取消备注
	public function merchant_cancel()
    {

        $order_db  = M('b2c_order');
        $order_where = array('order_id'=>$_GET['oid'], 'token' => $this->token);
        $order  = $order_db->where($order_where)->find();
		$merchant_cancel=$_POST['merchant_cancel'];
        if ($order) 
        {
			if(IS_POST){
				if(empty($merchant_cancel)){
					$this->error("商户取消备注不能为空");
				}
				//修改订单状态
				$ret = $order_db->where($order_where)->save(array('status' => 5,'merchant_cancel'=>$merchant_cancel));

				if ($ret) {
				   $this->success('操作成功',U(MODULE_NAME.'/orderList'));
				} else  {
				   $this->success('操作成功',U(MODULE_NAME.'/orderList'));
				}
			}else{		
				$this->display();		
			}
        } 
        else
        {
			$this->assign('order',$order);
			$this->display();
            //$this->ajaxReturn("订单取消失败", "ERROR", 0);
        }

       
    }
	public function operation_cancel()
    {

        $order_db  = M('b2c_order');
        $order_where = array('order_id'=>$_GET['oid'], 'token' => $this->token);
        $order  = $order_db->where($order_where)->find();
		$merchant_cancel=$_POST['merchant_cancel'];
        if ($order) 
        {
			if(IS_POST){
				if(empty($merchant_cancel)){
					$this->error("商户取消备注不能为空");
				}
				//修改订单状态
				$ret = $order_db->where($order_where)->save(array('status' => 5,'merchant_cancel'=>$merchant_cancel));

				if ($ret) {
				   $this->success('操作成功',U(MODULE_NAME.'/order'));
				} else  {
				   $this->success('操作成功',U(MODULE_NAME.'/order'));
				}
			}else{		
				$this->display();		
			}
        } 
        else
        {
			$this->assign('order',$order);
			$this->display();
            //$this->ajaxReturn("订单取消失败", "ERROR", 0);
        }

       
    }
}

?>

<?php
require_once(COMMON_PATH.'/CopyRightHelper.php');
class ShopAction extends WapAction
{

    public $ORDER_STATUS_INIT       = 1;
    public $ORDER_STATUS_PAYED      = 2;
    public $ORDER_STATUS_DELEVERYED = 3;
    public $ORDER_STATUS_CANCELED   = 4;
    //商户取消
    public $ORDER_STATUS_SHOP_CANCELED   = 5;
    
    public $ORDER_STATUS_CONFIRM   = 6;

    protected $branch_id = null;


    protected $is_need_auth = false;

    protected function _initialize()
    {
        define('PHONE_PAY', 9000);
        define('INTERNET_PAY', 9001);
        
        parent::_initialize();

        $opened_funcs = session('opened_funcs_'.$this->token);
        $cur_func = 'shangcheng';
        if (false && !in_array($cur_func ,$opened_funcs))
        {
            Log::record('Shop function verification failed: token:'.$this->token.' opened_funcs:'.print_r($opened_funcs, true));
            Log::save();
            echo 'Sorry!';
            exit;
        }

        $this->is_need_auth = $this->isNeedDoAuth();

        $this->assign('token',$this->token);

        $this->assign('staticFilePath',str_replace('./','/',THEME_PATH.'common/css/product'));

        //购物车信息
        $cart_items     = $this->getCart();
        $item_count     = 0;
        foreach ($cart_items as $key => $item) 
        {
            $item_count += $item['count'];
        }
        $this->assign('cart_item_count',$item_count);

        //商城信息
        $shop_where = array('token' => $this->token, 'status' => 1);

        $this->branch_id = intval($_GET['bid']);
        if (!empty($this->branch_id)) 
        {
            $shop_where['fake_id'] = $this->branch_id;
        }
        $shop_db      = M('b2c_shop');
        $shop = $shop_db->where($shop_where)->find();
        if (empty($this->branch_id)) 
        {
            $this->branch_id = $shop['fake_id'];
        }
        $this->assign('shop',$shop);

        $my_url = '#';
        if ($this->is_need_auth) 
        {
            $my_url = $this->generateAuthUrl('Shop/my');
        }
        else
        {
            $my_url = $this->generateUrl('Shop/my');
        }
        $this->assign('my_url',$my_url);

        $cart_url = $this->generateUrl('Shop/cart');
        $this->assign('cart_url',$cart_url);

        $home_url = $this->generateUrl('Shop/index');
        $this->assign('home_url',$home_url);
    }

    private function isNeedDoAuth()
    {
        if (empty($this->wechat_id)) 
        {
            //未登录
            if ($this->wechat_type == 1 && $this->is_authed == 1) 
            {
                return true;
            }
        }
        else
        {
            if ($this->wechat_type == 1 && $this->is_authed == 1) 
            {
                //验证该OPEN id是否已经认证过
                return true;
            }
            else
            {
                return false;
            }
            
        }
    }


    public function index()
    {
    	//清空购物车，如果含有特殊商品
    	$cart_items = $this->getCart();
     	foreach ($cart_items as $key => $item) 
        {
       			 if(in_array($item['product_id'], array(PHONE_PAY,INTERNET_PAY, '9006', '9007', '9008', '9009', '9078'))){
                	unset($_SESSION['session_cart_products']);
			        $this->assign('cart_item_count', '0');
                    break;
                }
        }
    	
        //微网站首页
        $cat_where = array('token'=>$this->token, 'status' =>1, 'parent' => 0);
        if (!empty($this->branch_id)) 
        {
            $cat_where['branch_id'] = $this->branch_id;
        }

        $category_db  = M('b2c_category');
        $cats = $category_db->where($cat_where)->order('sort desc')->select();
        foreach ($cats as $key => $vo) 
        {
            if ($cats[$key]['type'] == 0) 
            {
                $cats[$key]['url'] = $this->generateUrl('Shop/products',array('cid'=>$vo['category_id']));
            }
            else
            {
                $cats[$key]['url'] = $this->generateUrl('Shop/categories',array('cid'=>$vo['category_id']));   
            }
        }
        $this->assign('categories',$cats);


        $display_where = array('token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $display_where['branch_id'] = $this->branch_id;
        }
        $display = M('b2c_display')->field('bg_pic_url,tmpl_name')->where($display_where)->find();
        if ($display) 
        {
            $this->assign('display',$display);
            $this->display($display['tmpl_name']);
        }
        else
        {
            $this->display('index');
        }
    }
    
    public function categories()
    {
        //微网站首页
        $cat_where = array('token'=>$this->token, 'status' =>1, 'parent' => 0);
        if (!empty($this->branch_id)) 
        {
            $cat_where['branch_id'] = $this->branch_id;
        }

        if (isset($_GET['cid']))
        {
            $catid = intval($_GET['cid']);
            $cat_where['category_id'] = $catid;
        }

        $category_db  = M('b2c_category');
        $cats = $category_db->where($cat_where)->order('category_id asc')->select();
        foreach ($cats as $key => $vo) 
        {
            if ($cats[$key]['type'] == 0) 
            {
                $cats[$key]['url'] = $this->generateUrl('Shop/products',array('cid'=>$vo['category_id']));
            }
            else
            {
                $cats[$key]['url'] = $this->generateUrl('Shop/categories',array('cid'=>$vo['category_id']));   
            }
        }
        $this->assign('categories',$cats);

        $this->display('categories');
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

    private function generateAuthUrl($addr, $query_ary = array())
    {
        $query_ary['token'] = $this->token;
        if (!empty($this->branch_id)) 
        {
            //如果没有bid 默认选择第一个分店
            $query_ary['bid']   = $this->branch_id;
        }

        $host_name =  $_SERVER['SERVER_NAME'];
        $back_url = urlencode('http://'.$host_name.U($addr,$query_ary));
        //必须是认证过的服务号
        $redirect = urlencode('http://'.$host_name."/index.php?g=Wap&m=Oauth2&a=index&backurl=".$back_url);
       
        $auth_url =  "https://open.weixin.qq.com/connect/oauth2/authorize?appid="
                                .$this->appid
                                ."&redirect_uri=".$redirect
                                ."&response_type=code&scope=snsapi_userinfo&state=".$this->token."#wechat_redirect";
        Log::write('generate auth_url:'.$auth_url);                        
        return $auth_url;
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


    public function products()
    {
        //产品页列表
        $where = array('token'=>$this->token, 'status' =>1);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        if (isset($_GET['cid']))
        {
            $catid = intval($_GET['cid']);
            $where['category_id'] = $catid;
            
            $category_db  = M('b2c_category');
            $cat_where = array('category_id'=>$catid, 'token'=>$this->token, 'status' =>1);
            if (!empty($this->branch_id)) 
            {
                $cat_where['branch_id'] = $this->branch_id;
            }
            $thisCat = $category_db->where($cat_where)->find();
            $this->assign('thisCat',$thisCat);
            $this->assign('metaTitle',$thisCat['name']);
        }

        /*
        //搜索产品
        if (IS_POST)
        {
            $key = $this->_post('search_name');
            $this->redirect('/index.php?g=Wap&m=Shop&a=products&token='.$this->token.'&keyword='.$key);
        }

        if (isset($_GET['keyword']))
        {
            $where['name|intro|keyword'] = array('like',"%".$_GET['keyword']."%");
            $this->assign('isSearch',1);
        }*/
        $product_db   = M('b2c_product');
        
         //每页显示5个，只显示第一页
        $total = $product_db->where($where)->count();
        $pages = ceil($total/6);
        $this->assign('pages',$pages); 

        //排序方式 暂时取消该功能
        /*$method = isset($_GET['method']) && ($_GET['method'] == 'DESC' || $_GET['method'] == 'ASC')?$_GET['method']:'DESC';
        $orders = array('update_time','discount','shop_price');
        $order  = isset($_GET['order'])&&in_array($_GET['order'],$orders)?$_GET['order']:'update_time';
        $this->assign('order',$order);
        $this->assign('method',$method);
        $products = $product_db->where($where)->order($order.' '.$method)->limit('5')->select();
        */
        $products = $product_db->where($where)->order('update_time desc,product_id desc, sort desc')->limit('6')->select();
        foreach ($products as $key => $value) 
        {
            $products[$key]['url'] = $this->generateUrl('Shop/product', array('id'=>$products[$key]['product_id']));
            $products[$key]['price'] = $products[$key]['shop_price'];
        }
        $this->assign('products',$products);

        $get_more_url = $this->generateUrl('Shop/ajaxProducts', array('cid'=>$thisCat['category_id']));
        $this->assign('get_more_url',$get_more_url);
        
        $this->display();
    }

    public function ajaxProducts()
    {
        $where = array('token'=>$this->token, 'status' =>1);
        if (isset($_GET['cid']))
        {
            $category_id = intval($_GET['cid']);
            $where['category_id'] = $category_id;
        }
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        $page = (isset($_GET['page']) && intval($_GET['page']) > 1) ? intval($_GET['page']) : 2;

        //$pageSize = (isset($_GET['pagesize']) && intval($_GET['pagesize']) > 1) ? intval($_GET['pagesize']) : 5;
        //$start = ($page - 1) * $pageSize;

        $product_db   = M('b2c_product');
        $products = $product_db->where($where)->order('update_time desc,product_id desc, sort desc')->page($page.',6')->select();

        foreach ($products as $key => $value) 
        {
            $products[$key]['url'] = $this->generateUrl('Shop/product', array('id'=>$products[$key]['product_id']));
            $products[$key]['price'] = $products[$key]['shop_price'];
        }

        $product_json = array();
        if ($products) 
        {
            $product_json['products'] = $products; 
        }
        Log::save();
        $this->ajaxReturn($product_json,'JSON');
    }

    public function header()
    {
        $this->display();
    }

    public function product()
    {
    	$cart_items = $this->getCart();
     	foreach ($cart_items as $key => $item) 
        {
       			 if(in_array($item['product_id'], array(PHONE_PAY,INTERNET_PAY, '9006', '9007', '9008', '9009', '9078'))){
                	unset($_SESSION['session_cart_products']);
			        $this->assign('cart_item_count', '0');
                    break;
                }
        }
    	
        $where = array('token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        if (isset($_GET['id']))
        {
            $id = intval($_GET['id']);
            $where['product_id'] = $id;
        }

        $product_db   = M('b2c_product');
        $product = $product_db->where($where)->find();

        $product['price'] = $product['shop_price'];

        $this->assign('product',$product);
        $this->assign('metaTitle',$product['name']);


        $add_to_cart_url = $this->generateUrl('Shop/addProductToCart',array('id'=>$product['product_id'],'price'=>$product['shop_price'],'count'=>1));
        $this->assign('add_to_cart_url',$add_to_cart_url);

        $this->assign('intro',$product['intro']);
        //分店信息
        /*$company_model=M('Company');
        $branchStoreCount=$company_model->where(array('token'=>$this->token,'isbranch'=>1))->count();
        $this->assign('branchStoreCount',$branchStoreCount);*/
        //销量最高的商品
        $hot_products_where =array('token'=>$this->token, 'branch_id'=> $this->branch_id,'product_id'=>array('neq',$product['product_id']), 'status'=> 1);
        if (!empty($this->branch_id)) 
        {
            $hot_products_where['branch_id'] = $this->branch_id;
        }
        $hot_products = $product_db->where($hot_products_where)->limit('sale_count DESC')->limit('0,3')->select();
        foreach ($hot_products as $key => $value) 
        {
            $hot_products[$key]['url'] = $this->generateUrl('Shop/product', array('id'=>$hot_products[$key]['product_id']));
            $hot_products[$key]['price'] = $hot_products[$key]['shop_price'];
        }
        
        $category_ids = array('1124', '1134', '1129', '1174');
        $this->assign('category_ids',$category_ids);
        
        $special = array('9000', '9001', '9006', '9007', '9008', '9009', '9078');
        $this->assign('special_ids', $special);
        
        if($product['category_id'] == 1129){
            $product_ids = array($product['product_id'], PHONE_PAY);
            $product_ids = implode(',', $product_ids);
            $goPay=$this->generateUrl('Shop/goPay', array('category_id'=>$product['category_id'], 'branch_id'=>$this->branch_id, 'id'=>$product_ids));
            $this->assign('noNet_goPay_url',$goPay);
            
        	$product_ids = array($product['product_id'], PHONE_PAY);
            if (in_array($product['product_id'], array('9069', '9070', '9081'))) {
	            $product_ids = array($product['product_id'], PHONE_PAY, INTERNET_PAY);
            }
            
            $product_ids = implode(',', $product_ids);
            $goPay=$this->generateUrl('Shop/goPay', array('category_id'=>$product['category_id'], 'branch_id'=>$this->branch_id, 'id'=>$product_ids));
            $this->assign('goPay_url',$goPay);
            $phone_pay = $product_db->where(array('product_id'=>PHONE_PAY))->find();
            $this->assign('phone_pay',$phone_pay);
            $internet_pay = $product_db->where(array('product_id'=>INTERNET_PAY))->find();
            $this->assign('internet_pay',$internet_pay);
        }elseif($product['category_id'] == 1134){
        	if (preg_match('/一年付$/', $product['name'])) {
	        	$product_ids = array($product['product_id'], '9006', '9007');
	            $product_ids = implode(',', $product_ids);
        	}elseif (preg_match('/两年付$/', $product['name'])) {
	        	$product_ids = array($product['product_id'], '9006');
	            $product_ids = implode(',', $product_ids);
        	}else{
        		$product_ids = implode(',', array($product['product_id']));
        	}
        	
            $goPay=$this->generateUrl('Shop/goPay', array('category_id'=>$product['category_id'], 'branch_id'=>$this->branch_id, 'id'=>$product_ids));
            $this->assign('noNet_goPay_url',$goPay);
            $phone_pay = $product_db->where(array('product_id'=>'9006'))->find();
            $this->assign('phone_pay',$phone_pay);
            $internet_pay = $product_db->where(array('product_id'=>'9007'))->find();
            $this->assign('internet_pay',$internet_pay);
        }elseif($product['category_id'] == 1124){
        	$extra = '9008';
        	$product_ids = array($product['product_id'], $extra);
            $product_ids = implode(',', $product_ids);
            $goPay=$this->generateUrl('Shop/goPay', array('category_id'=>$product['category_id'], 'branch_id'=>$this->branch_id, 'id'=>$product_ids));
            $this->assign('noNet_goPay_url',$goPay);
            $phone_pay = $product_db->where(array('product_id'=>$extra))->find();
            $this->assign('phone_pay',$phone_pay);
        }elseif($product['category_id'] == 1174){
        	$extra = '9078';
        	$product_ids = array($product['product_id'], $extra);
            $product_ids = implode(',', $product_ids);
            $goPay=$this->generateUrl('Shop/goPay', array('category_id'=>$product['category_id'], 'branch_id'=>$this->branch_id, 'id'=>$product_ids));
            $this->assign('noNet_goPay_url',$goPay);
            $phone_pay = $product_db->where(array('product_id'=>$extra))->find();
            $this->assign('phone_pay',$phone_pay);
        }

        $specs = M('b2c_product_spec')->where(array('product_id'=>$product['product_id'],'status'=>0))->field('CONCAT(size_id,"_",color_id) as k,inventory')->select();
        $this->assign('specs',$specs);
        $this->assign('size_set',unserialize( $product['size_set']) );
        $this->assign('color_set',unserialize( $product['color_set']) );

        $this->assign('hot_products',$hot_products);
        $this->display();
    }

    public function goPay(){
        $_SESSION['session_cart_products']=array();
        $product_ids = explode(',',$_GET['id']);
        foreach($product_ids as $product_id){
            $_GET['id'] = $product_id;
            $this->addProductToCart();
        }

        $checkout_url = '';
        if (!session('wechat_id_'.$this->token))
        {
            $checkout_url = $this->generateAuthUrl('Shop/checkout', array('showwxpaytitle'=>1));
        }
        else
        {
            $checkout_url = $this->generateUrl('Shop/checkout', array('showwxpaytitle'=>1));
        }
        redirect($checkout_url);
    }

    /**
     * 返回添加成功的数量 若失败则返回0
     */
    public function addProductToCart()
    {
        //商品id|商品价格|商品数量,
        //将商品加入购物车中
        $count      = isset($_POST['amount']) ? intval($_POST['amount']) : 1;
        $cart_items = $this->getCart();
        $product_id = intval($_GET['id']);
        $size_id    = isset($_POST['size_id']) ? $_POST['size_id'] : (isset($_GET['size_id']) ? $_GET['size_id'] : 0);
        $color_id   = isset($_POST['color_id']) ? $_POST['color_id'] : (isset($_GET['color_id']) ? $_GET['color_id'] : 0);
        $size_name  = isset($_POST['size_name']) ? $_POST['size_name'] : (isset($_GET['size_name']) ? $_GET['size_name'] : '');
        $color_name = isset($_POST['color_name']) ? $_POST['color_name'] : (isset($_GET['color_name']) ? $_GET['color_name'] : '');
        
        
        //无规格产品$key为'0_0'
        $key = $size_id . '_' .$color_id ;
        $spec = array(
                    'key'        =>$key,
                    'size_id'    =>$size_id,
                    'size_name'  =>$size_name,
                    'color_id'   =>$color_id,
                    'color_name' =>$color_name,
                    'amount'     =>$count);
        if (key_exists($product_id,$cart_items))
        {
            //获得产品或者某规格产品的库存
            $inventory = $this->inventoryOf($product_id,$size_id,$color_id);
            if ($cart_items[$product_id]['specs'][$key])
            {
                //根据库存更新有规格产品的下单数
                if ($size_id != 0 || $color_id != 0) 
                {
                    $old_amount = $cart_items[$product_id]['specs'][$key]['amount'];
                    $new_amount = $count + $old_amount;
                    if ($inventory > 0)
                    {
                        //取该规格产品库存和购物车的最小值
                        $cart_items[$product_id]['specs'][$key]['amount'] = min($new_amount , $inventory);

                        $count = $cart_items[$product_id]['specs'][$key]['amount'] - $old_amount;
                        $cart_items[$product_id]['count'] += $count;
                    }
                    else if ($inventory == 0) 
                    {
                        $cart_items[$product_id]['specs'][$key]['amount'] = $new_amount ;
                        $cart_items[$product_id]['count'] += $count;
                    }
                }elseif ($size_id == 0 && $color_id == 0){
                	$old_amount = $cart_items[$product_id]['specs'][$key]['amount'];
                    $new_amount = $count + $old_amount;
                    if ($inventory > 0)
                    {
                        //取该规格产品库存和购物车的最小值
                        $cart_items[$product_id]['specs'][$key]['amount'] = min($new_amount , $inventory);

                        $count = $cart_items[$product_id]['specs'][$key]['amount'] - $old_amount;
                        $cart_items[$product_id]['count'] += $count;
                    }
                    else if ($inventory == 0) 
                    {
                        $cart_items[$product_id]['specs'][$key]['amount'] = $new_amount ;
                        $cart_items[$product_id]['count'] += $count;
                    }
                }
                
            }
            else
            {
                $cart_items[$product_id]['specs'][$key] = $spec;
                $cart_items[$product_id]['count'] += $count;
            }
        }
        else 
        {
            $product_db   = M('b2c_product');
            $product_where = array('token'=>$this->token,'product_id'=>$product_id );
            if (!empty($this->branch_id)) 
            {
                $product_where['branch_id'] = $this->branch_id;
            }
            $product = $product_db->where($product_where)->find();
            if ($product) 
            {
                $cart_items[$product_id] = array(
                    'product_id'=>$product_id,
                    'count'=>$count,
                    'price'=>floatval($product['shop_price']),
                    'specs'=>array($key=>$spec));
            }
            else
            {
                echo 0;
            }
            
        }
        $this->setCart($cart_items);
        echo $count;
    }


    public function cart()
    {
        $item_amount   = 0;
        $product_list ;
        $products   = array();
        $ids        = array();
        $carts      = $this->getCart();

        foreach ($carts as $k=>$c)
        {
            if (is_array($c) && $c['count'] != 0)
            {
                if(in_array($c['product_id'], array(PHONE_PAY,INTERNET_PAY, '9006', '9007', '9008'))){//hack for huawei phone
                	unset($_SESSION['session_cart_products']);
                    $ids        = array();
                    break;
                }
                array_push($ids,$c['product_id']);
            }
        }

        if (count($ids))
        {
            $product_db   = M('b2c_product');
            $product_list = $product_db->where(array('product_id'=>array('in',$ids)))->select();
            if ($product_list) 
            {
                foreach ($product_list as $key => $p) 
                {
                    $p['price'] = floatval($p['shop_price']);
                    $p['url'] = $this->generateUrl('Shop/product', array('id'=>$p['product_id']));
                    $p['total_inven'] = $this->inventoryOf($p['product_id'],'','');
                    $cart_item = $carts[$p['product_id']];
                    foreach ($cart_item['specs'] as $k => $val) 
                    {
                        $key = $val['size_id'] . '_' . $val['color_id'];
                        $p['key'] = $key;
                        $p['delete_from_cart_url'] = $this->generateUrl('Shop/deleteFromCart', array('id'=>$p['product_id'],'key'=>$key));
                        $p['size_name'] = $val['size_name'];
                        $p['color_name'] = $val['color_name'];
                        $p['inventory'] = $this->inventoryOf($p['product_id'],$val['size_id'],$val['color_id']);
                        $p['amount'] = $val['amount'];
                        array_push($products, $p);
                    }
                    $item_amount   += $p['price'] * $cart_item['count'];
                }
            }
        }


        $checkout_url = '#';
        if ($this->is_need_auth  &&  !session('wechat_id_'.$this->token)) 
        {
            $checkout_url = $this->generateAuthUrl('Shop/checkout', array('showwxpaytitle'=>1));
        }
        else
        {
            $checkout_url = $this->generateUrl('Shop/checkout', array('showwxpaytitle'=>1));
        }
        $this->assign('checkout_url',$checkout_url);

        $ajax_update_cart_url = $this->generateUrl('Shop/ajaxUpdateCart');
        $this->assign('ajax_update_cart_url',$ajax_update_cart_url);

        $this->assign('products',$products);
        $this->assign('item_amount',$item_amount);
        $this->assign('metaTitle','购物车');
        $this->display();
    }

    public function deleteFromCart()
    {
        $ids        = array();
        $cart       = $this->getCart();
        $pid = $_GET['id'];
        $key = $_GET['key'];
        if(count($cart[$pid]['specs']) > 1)
        {
            $cart[$pid]['count'] = $cart[$pid]['count'] - $cart[$pid]['specs'][$key]['amount'];
            unset($cart[$pid]['specs'][$key]);
        }
        else
        {
            unset($cart[$pid]);    
        }
        
        $this->setCart($cart);

        $redirect = $this->generateUrl('Shop/cart');
        $this->redirect($redirect);
    }

    public function ajaxUpdateCart()
    {
        $g_id    = $_GET['id'];
        $g_count = intval($_GET['count'], 0);
        $cart_items  = $this->getCart();
        $key = $_GET['key'];

        if ($cart_items && isset($cart_items[$g_id]) && $g_count >= 0)
        {
            if(count($cart_items[$g_id]['specs']) > 0)
            {
                $diff = $cart_items[$g_id]['specs'][$key]['amount'] - $g_count;

                $cart_items[$g_id]['specs'][$key]['amount'] = $g_count;
                $cart_items[$g_id]['count'] = $cart_items[$g_id]['count'] - $diff;
            }
            else
            {
                $cart_items[$g_id]['count'] = $g_count;
            }
        }
        else
        {
            $cart_items[$g_id]['count'] = 0;
        }
        $this->setCart($cart_items);
        echo json_encode($cart_items);
    }

    /**
     * 得到新订单号
     * @return  string
     */

    private function get_order_sn()
    {

        /* 选择一个随机的方案 */
        
        mt_srand((double) microtime() * 1000000);
        return date("YmdHis") . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

    }

    public function checkout()
    {
        if (IS_POST)
        {
            $row            = array();
            $cart_items     = $this->getCart();
            $cart_item_count     = 0;

            $product_ids = array();
            foreach ($cart_items as $k=>$c)
            {
                if ($c['count'] > 0) 
                {
                    array_push($product_ids,$k);
                }
            }
            if (count($product_ids) == 0) 
            {
                $redirect = $this->generateUrl('Shop/index');
                $this->error("您的购物车里没有东西啦。", $redirect);
            }

            $pay_code       = trim($this->_post('payment'));
            $payment        =  null;
            if ( $pay_code != 'cod')
            {
                $payment_where = array('token'=>$this->token, 'enabled' => 1, 'pay_code' => $pay_code);
                if (!empty($this->branch_id)) 
                {
                    $payment_where['branch_id'] = $this->branch_id;
                }
                $payment = M('b2c_payment')->where($payment_where)->find();
                if ($payment == null) 
                {
                    $url = $this->generateUrl('Wap/Shop/index');
                    $this->error("本商城不支持该支付方式。", $url);
                }
            }
            
            $product_db = M('b2c_product');
            $products   = $product_db->where(array('product_id'=>array('in',$product_ids)))->select();

            // generate order desc for third party pay
            $order_desc = '';

            if ($products)
            {
                $order_amount       = 0;
                $order_item_count   = 0;

                $product_list       = array();
                //在此处可以做检查库存的逻辑，但是库存对于商家来说只需要进货就可以了,而且为了兼容设置库存的商品，所以不做检查
                foreach ($products as $key => $p) 
                {
                    $cart_item = $cart_items[$p['product_id']];

                    $p['price'] = floatval($p['shop_price']);
                    $p['url'] = $this->generateUrl('Shop/product', array('id'=>$p['product_id']));
                    $count = 0;
                    foreach ($cart_item['specs'] as $k => $val) 
                    {
                        $p['amount'] = $val['amount'];
                        $p['size_name'] = $val['size_name'];
                        $p['color_name'] = $val['color_name'];
                        $count = $count + $val['amount'];
                        array_push($product_list, $p);
                    }
                    $order_amount   += $p['price'] * $count;
                    $order_item_count   += $count;
                    $order_desc .= $p['name'].' ;';
                }

                $order_desc = trim(substr($order_desc, 0, 110), " ;") ;
                
                //用户信息
				$row['cmbProvince'] =$this->_post('cmbProvince');
				$row['cmbCity']		=$this->_post('cmbCity');
				$row['cmbArea']		=$this->_post('cmbArea');
                $row['truename']    = $this->_post('truename');
                $row['tel']         = $this->_post('tel');
                $row['address']     = $this->_post('address');
                $row['zipcode']     = $this->_post('zipcode');
                
                $row['payment']     = $pay_code;
                $row['status']      = $this->ORDER_STATUS_INIT; //1是初始、2是已付款、3是已发货、4是取消
                
                $row['token']       = $this->token;
                $row['branch_id']   = $this->branch_id;
                $row['wecha_id']    = $this->wechat_id;
                if (!$row['wecha_id'])
                {
                    $row['wecha_id']='null';
                }
                
                $time               = time();
                
                $row['total']       = $order_item_count ;
                $row['price']       = $order_amount;
                $row['sn']          = $this->get_order_sn();
                
                $row['create_time']  = $time;
                $row['update_time']  = $time;
                $row['info']        = serialize($cart_items);
                //插入订单信息
                $order_db  = M('b2c_order');
                $order_id  = $order_db->add($row);

                //插入订单货品信息
                if ($order_id)
                {
                    $crow       = array();
                    foreach ($product_list as $k=>$p)
                    {
                        $crow['order_id']   = $order_id;
                        $crow['pic_url ']   = $p['logo_url'];
                        $crow['product_id'] = $p['product_id'];
                        $crow['price']      = $p['shop_price'];

                        $crow['count']      = $cart_items[$p['product_id']]['count'];

                        $crow['wecha_id']   = $row['wecha_id'];
                        $crow['token']      = $row['token'];
                        $crow['color_name'] = $p['color_name'];
                        $crow['size_name']  = $p['size_name'];

                        $crow['create_time']       = $time;
                        M('b2c_order_item')->add($crow);
                        $product_db->where(array('product_id'=>$k))->setInc('sale_count', $cart_items[$p['product_id']]['count']);
                    }
                }


                $_SESSION['session_cart_products']='';
                //保存个人信息

                $user_data      = array('tel'=>$row['tel'],'truename'=>$row['truename'],'address'=>$row['address'], 'zipcode'=> $row['zipcode']);
                $user_data['province'] = $row['cmbProvince'];
				$user_data['city'] =$row['cmbCity'];
				$user_data['area'] = $row['cmbArea'];
                
                $customer_db    = M('b2c_customer');
                $customer       = $customer_db->where(array('token'=>$this->token,'wecha_id'=>$this->wechat_id ))->find();
                if ($customer)
                {
                    $customer_db->where(array('customer_id'=>$customer['customer_id']))->save($user_data);
                }
                else 
                {
                    $user_data['token']     = $this->token;
                    $user_data['wecha_id']  = $this->wechat_id ;
                    $user_data['wechaname'] = '';

                    $customer_db->add($user_data);
                }
                
            }
            $backurl = urlencode(C('site_url').'/index.php?g=Wap&m=Shop&a=my&token='.$row['token']. '&bid='.$row['branch_id']. '&wecha_id='.$row['wecha_id']);
            //pay_code in b2c_payment
            switch ($pay_code) 
            {
                case 'alipay':
                    $this->redirect(U('ShopPay/startAlipay',array('order_sn' => $row['sn'], 'pay_type'=>'0', 'bid'=>$row['branch_id'], 'front_url'=>$backurl)));
                    break;
                case 'wxpay':
                    $redirUrl = $this->generatePayUrl('wxpay/pay', array('order_sn'=>$row['sn'], 'pay_type'=>'0', 'bid'=>$row['branch_id'], 'front_url'=>$backurl));
                    header("location: ".$redirUrl); 
                    break;
                case 'cftpay': 
                    $redirUrl = $this->generatePayUrl('cftpay/pay', array('order_sn'=>$row['sn'], 'pay_type'=>'0', 'bid'=>$row['branch_id'], 'front_url'=>$backurl));
                    header("location: ".$redirUrl);
                    break;
               	case 'wingpay': 
               		$redirUrl = $this->generatePayUrl('wingpay/pay', array('order_sn'=>$row['sn'], 'pay_type'=>'0', 'bid'=>$row['branch_id'], 'front_url'=>$backurl));
                    header("location: ".$redirUrl);
					break;
               case 'unionpay':
                	$redirUrl = $this->generatePayUrl('unionpay/pay', array('order_sn'=>$row['sn'], 'pay_type'=>'0', 'bid'=>$row['branch_id'], 'front_url'=>$backurl));
                    header("location: ".$redirUrl);
                	break;
                default:
                    //ShopAction::minusInventory($row['sn']);
                    $redirect = $this->generateUrl('Shop/my',array('success'=>1));
                    $this->redirect($redirect);  
                    break;
            }
        }
        else
        {
            /*if ($this->is_need_auth) 
            {
                $this->error("您还未登录。");
            }*/
            
            $payment_where = array('token'=>$this->token, 'enabled' => 1);
            if (!empty($this->branch_id)) 
            {
                $payment_where['branch_id'] = $this->branch_id;
            }
            $payments = M('b2c_payment')->where($payment_where)->order('pay_order asc')->select();
            $this->assign('payments', $payments);

            if ($this->wechat_id ) 
            {
                $customer = M('b2c_customer')->where(array('wecha_id'=>$this->wechat_id, 'token'=>$this->token))->find();
                $this->assign('customer', $customer);
            }

                
            $this->display();
            
        }
    }
    
    
    public function my()
    {
        /*if ($this->is_need_auth) 
        {
            $this->error("您还未登录。");
        }*/

        $order_db = M('b2c_order');

        if (!empty($this->wechat_id)) 
        {
            $order_where = array('token'=>$this->token, 'wecha_id'=>$this->wechat_id, 'status' => array('neq', $this->ORDER_STATUS_SHOP_CANCELED) );
            if (!empty($this->branch_id)) 
            {
                $order_where['branch_id'] = $this->branch_id;
            }
            $orders    = $order_db->where($order_where)->order('create_time DESC')->select();
            if ($orders)
            {
                foreach ($orders as $key => $o)
                {
                    $orders[$key]['url'] = $this->generateUrl('Shop/orderDetail',array('oid'=>$o['sn'])); 
                    $order_amount=$o['price'];
                    $backurl = urlencode(C('site_url').'/index.php?g=Wap&m=Shop&a=my&token='.$o['token']. '&bid='.$o['branch_id']. '&wecha_id='.$o['wecha_id']);
                    if ($o['status'] == 1)  {
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
        if ( $order == null || $order['wecha_id'] != $this->wechat_id  || $order['status'] != 1)
        {
            $url = $this->generateUrl('Wap/Shop/orderDetail', array('oid' => $order_sn));
            $this->error("您不能付款，请联系客服。", $url);
        }
    	M('b2c_order')->where($where)->save(array('status' => $this->ORDER_STATUS_PAYED));*/
    	$redirect = $this->generateUrl('Shop/my');
        $this->redirect($redirect);
    }


    public function orderDetail()
    {
        if ($this->is_need_auth) 
        {
           // $this->error("您还未登录。");
        }
        $order_db  = M('b2c_order');
        $order_sn  = $_GET['oid'];
        $where = array('sn'=>$order_sn, 'token'=>$this->token,'status' => array('neq', $this->ORDER_STATUS_SHOP_CANCELED));
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }
        $order  = $order_db->where($where)->find();
        //检查权限
        if (empty($order) || $order['wecha_id'] != $this->wechat_id )
        {
            exit();
        }
        
     	if ($order['status'] == 1)  {
        	$backurl = urlencode(C('site_url').'/index.php?g=Wap&m=Shop&a=my&token='.$order['token']. '&bid='.$order['branch_id']. '&wecha_id='.$order['wecha_id']);
            switch($order['payment']) {
                case 'alipay':
                    $order['alipay_url'] = $this->generateUrl('ShopPay/startAlipay',array('order_sn'=>$order['sn']));
                    break;
                case 'cftpay': 
                    $order['cftpay_url'] = $this->generatePayUrl('cftpay/pay', array('order_sn'=>$order['sn'], 'pay_type'=>'0', 'bid'=>$order['branch_id'], 'front_url'=>$backurl));
                case 'cod': 
                    $order['cod_url'] = $this->generatePayUrl('Wap/Shop/cod_pay', array('order_sn'=>$order['sn'], 'pay_type'=>'0', 'bid'=>$order['branch_id'], 'front_url'=>$backurl));
                    break;
                case 'wxpay':
                    $order['wxpay_url'] = $this->generatePayUrl('wxpay/pay', array('order_sn' => $order['sn'], 'pay_type'=>'0', 'bid'=>$order['branch_id'], 'front_url'=>$backurl));
                    break;
                case 'unionpay': 
					$order['unionpay_url'] = $this->generatePayUrl('unionpay/pay', array('order_sn'=>$order['sn'], 'pay_type'=>'0', 'bid'=>$order['branch_id'], 'front_url'=>$backurl));
					break;
                case 'wingpay': 
					$order['wingpay_url'] = $this->generatePayUrl('wingpay/pay', array('order_sn'=>$order['sn'], 'pay_type'=>'0', 'bid'=>$order['branch_id'], 'front_url'=>$backurl));
					break;
            }
        }
        $this->assign('order',$order);

        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $order_items = $Model->query("select i.product_id, i.count, p.`name`, p.logo_url, i.price from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order['order_id']." and p.branch_id='$this->branch_id' and i.token='$this->token'");

        $amount         = 0.0;
        $total_count    = 0;

        foreach ($order_items as $k=>$c)
        {
            $amount         += $c['price'] * $c['count'];
            $total_count    += $c['count'];
            $order_items[$k]['url'] = $this->generateUrl('Shop/product',array('id'=>$c['product_id']));
        }

        $this->assign('products',$order_items);
        $this->assign('order_amount',$amount);
        $this->assign('order_item_count',$total_count);

        $cancel_url = $this->generateUrl('Shop/cancelOrder', array('oid'=> $order['sn']));
        $this->assign('cancel_url',$cancel_url);
        
        $confirm_url = $this->generateUrl('Shop/confirmOrder', array('oid'=> $order['sn'], 'pay'=>$order['payment']));
        $this->assign('confirm_url',$confirm_url);
        
        // 发货详细信息
        if($order['status'] == 3 || ($order['status'] == 2 && $order['payment'] == 'cod')) {
            $logistics = M('b2c_logistics')->where(array('order_id'=>$order['order_id']))->find();
            if(!empty($logistics)) {
                $this->assign('logistics', $logistics);
            }
        }
        
        $this->assign('metaTitle','修改订单');
        $this->display();
    }


    public function cancelOrder()
    {
        if ($this->is_need_auth) 
        {
           // $this->error("您还未登录。");
        }

        $order_sn = intval($_GET['oid']);
        $order_db      = M('b2c_order');

        $where = array('sn'=>$order_sn,'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        $order = $order_db->where($where)->find();
        //检查权限
        if ( $order == null || $order['wecha_id'] != $this->wechat_id  || $order['status'] != 1)
        {
            $url = $this->generateUrl('Wap/Shop/orderDetail', array('oid' => $order_sn));
            $this->error("您的订单不能取消了，请联系客服。", $url);
        }

        $order_items_db = M('b2c_order_item');
        
        //删除订单和订单列表
        $order_db->where($where)->save(array('status' => $this->ORDER_STATUS_CANCELED));

        //商品销量做相应的减少
        $ordered_items = $order_items_db->where(array('order_id'=>$order['order_id']))->select();
        
        $product_db   = M('b2c_product');
        foreach ($ordered_items as $k=>$item)
        {
            $product_db->where(array('product_id'=>$item['product_id']))->setDec('sale_count', $item['count']);
        }
        $redirect = $this->generateUrl('Shop/my');
        $this->redirect($redirect);
    }
    
	public function confirmOrder()
    {
       /* if ($this->is_need_auth) 
        {
            $this->error("您还未登录。");
        }*/

        $order_sn = intval($_GET['oid']);
        $order_db      = M('b2c_order');

        $where = array('sn'=>$order_sn,'token'=>$this->token);
        if (!empty($this->branch_id)) 
        {
            $where['branch_id'] = $this->branch_id;
        }

        $order = $order_db->where($where)->find();
        //检查权限
        if ( $order == null || $order['wecha_id'] != $this->wechat_id  || $order['status'] != 3)
        {
            $url = $this->generateUrl('Wap/Shop/orderDetail', array('oid' => $order_sn));
            $this->error("您不能确认收货，请联系客服。", $url);
        }
        
        $status = $this->ORDER_STATUS_CONFIRM;
        if ($_GET['pay'] == 'cod') {
        	$status = $this->ORDER_STATUS_PAYED;
        	ShopAction::minusInventory($order_sn);
        }
        $order_db->where($where)->save(array('status' => $status));
        $redirect = $this->generateUrl('Shop/my');
        $this->redirect($redirect);
    }

    private function setCart($cart_items='')
    {
        $_SESSION['session_cart_products'] = serialize($cart_items);
    }

    private function getCart()
    {
        $carts = null;
        if (!isset($_SESSION['session_cart_products']) || !strlen($_SESSION['session_cart_products']))
        {
            $carts = array();
        }
        else 
        {
            $carts = unserialize($_SESSION['session_cart_products']);
        }
        return $carts;
    }

    private function clearCart() 
    {
        unset($_SESSION['session_cart_products']);
    }

    function remove_html_tag($str)
    {  //清除HTML代码、空格、回车换行符
        //trim 去掉字串两端的空格
        //strip_tags 删除HTML元素

        $str = trim($str);
        $str = @preg_replace('/<script[^>]*?>(.*?)<\/script>/si', '', $str);
        $str = @preg_replace('/<style[^>]*?>(.*?)<\/style>/si', '', $str);
        $str = @strip_tags($str,"");
        $str = @ereg_replace("\t","",$str);
        $str = @ereg_replace("\r\n","",$str);
        $str = @ereg_replace("\r","",$str);
        $str = @ereg_replace("\n","",$str);
        $str = @ereg_replace(" ","",$str);
        $str = @ereg_replace("&nbsp;","",$str);
        return trim($str);
    }
    
    
    public function wxpay() {
        $order_sn     = $this->_get('order_sn');
        $token         = $this->token;
        $wecha_id = $this->wechat_id;
        
        //$redirect_url = $this->generateUrl('Shop/orderDetail', array('oid'=>$order_sn));
        //$redirect_url = WapAction::generatePayResultUrl('Shop/my', $token, array('token'=> $token, 'branch_id'=>$this->branch_id,'wecha_id'=>$wecha_id));
        //$redirect_url = urldecode($this->_get('redirect'));
        $redirect_url = $_GET['front_url'];
        
        Log::record('startWxpay: '.$token.':'.$order_sn.':'.$wecha_id);
        Log::save();
        if(empty($order_sn) || empty($token))
        {
            $this->error('请输入订单号');
        }
        
        $pay_type = $_GET['pay_type'];
   	    if ($pay_type == '1') {
	        $order = M('hotel_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'order_status'=>3))->find();
        }elseif ($pay_type == '2'){
        	$order = M('dine_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>2))->find();
        }else{
	        $order = M('b2c_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'branch_id'=>$this->branch_id, 'status'=>1))->find();
        }
        if(!$order)	$this->error('订单号不正确');

        //$shop = M('b2c_shop')->where(array('token'=>$token, 'branch_id'=>$this->branch_id))->find();
        //if(!$shop) 	$this->error('商城不存在');

        $payment_where = array('token'=>$token, 'pay_code'=>'wxpay', 'pay_type'=>$pay_type);
        if (!empty($this->branch_id)) 
        {
            $payment_where['branch_id'] = $this->branch_id;
        }
        $payment = M('b2c_payment')->where($payment_where)->find();

        $wxpay_config = unserialize($payment['pay_config']);
        Log::record('wxpay order:'.$order_sn.' config:'.print_r($wxpay_config, true));
        if (!empty($wxpay_config))
        {
       		 if ($pay_type == '1') {
		        $order_content = $order['room_type'];
	            $total_fee = intval(floatval($order['prepayment']) * 100); //分
	        }elseif ($pay_type == '2'){
        		$order_content = '订单';
	            $total_fee = intval(floatval($order['price']) * 100); //分
        	}else{
	            $order_content =$this->getOrderDesc($order['order_id']);//'订单'.$order['sn'].'支付';
	            $total_fee = intval(floatval($order['price']) * 100); //分
	        }
            $product_fee = $total_fee;
            $transport_fee = 0;
            $bank_type = "WX";
            $spbill_create_ip = '127.0.0.1';
            
            //服务器异步通知页面路径
            $notify_url = $this->generatePayUrl('wxpay/notify', array('pay_type'=>$pay_type), true);//C('site_url').'/index.php/wxpay/notify';
            
            // add audit for WX Pay.
            $trade = M('b2c_wxtrade')->where(array('order_sn'=> $order_sn, 'token'=> $token))->find();
            if ($trade['n_transaction_id']) 
            {
                $this->error('已支付');
            }
            
            include_once(LIB_PATH."ORG/Wpay/WxPayPubHelper.php");
            
            //使用jsapi接口
            $jsApi = new JsApi_pub($wxpay_config);
        
            //=========步骤1：网页授权获取用户openid============
            //通过code获得openid
            if (!session('wechat_id_'.$token)) {
	            if (!isset($_GET['code']))  {
	                //触发微信返回code码
	                $redirect_uri = C('site_url').'/index.php/wxpay/pay?order_sn='.$order_sn.'&token='.$token.'&bid='.$this->branch_id.'&pay_type='.$pay_type.'&front_url='.urlencode($redirect_url);
	                $url = $jsApi->createOauthUrlForCode($redirect_uri);
	                Header("Location: $url"); 
	            }else  {
	                //获取code码，以获取openid
	                $code = $_GET['code'];
	                $jsApi->setCode($code);
	                $openid = $jsApi->getOpenId();
	            }
            }else{
            	$openid = session('wechat_id_'.$token);
            }
            
            //=========步骤2：使用统一支付接口，获取prepay_id============
            //使用统一支付接口
            $unifiedOrder = new UnifiedOrder_pub($wxpay_config);
            
            //设置统一支付接口参数
            //设置必填参数
            //appid已填,商户无需重复填写
            //mch_id已填,商户无需重复填写
            //noncestr已填,商户无需重复填写
            //spbill_create_ip已填,商户无需重复填写
            //sign已填,商户无需重复填写
            $unifiedOrder->setParameter("openid",$openid);//商品描述
            $unifiedOrder->setParameter("body", $order_content);//商品描述
            //自定义订单号，此处仅作举例
            //$timeStamp = time();
            //$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
            $unifiedOrder->setParameter("out_trade_no", $order_sn);//商户订单号 
            $unifiedOrder->setParameter("total_fee", $total_fee);//总金额
            $unifiedOrder->setParameter("notify_url", $notify_url);//通知地址 
            $unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
            
            
            //非必填参数，商户可根据实际情况选填
            //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
            //$unifiedOrder->setParameter("device_info","XXXX");//设备号 
            //$unifiedOrder->setParameter("attach","XXXX");//附加数据 
            //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
            //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
            //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
            //$unifiedOrder->setParameter("openid","XXXX");//用户标识
            //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
        
            $prepay_id = $unifiedOrder->getPrepayId();
            //=========步骤3：使用jsapi调起支付============
            $jsApi->setPrepayId($prepay_id);
        
            $biz_pkg_array = $jsApi->getParameters();
            $biz_package = json_encode($biz_pkg_array);
            $this->assign('biz_package', $biz_package);
            Log::record('wxpay start token:'.$token.' outorder:'.$order_sn.' wecha_id:'.$wecha_id.' biz_package:'.$biz_package, Log::INFO);
            Log::save();
            
            if (!$trade) 
            {
            	$oParams = json_decode($biz_pkg_array);
                $time = time();
                
                // 微信参数
                $data['token'] = $token;
                $data['wecha_id'] = $wecha_id;
                
                // 协议参数
                $data['appId'] = $wxpay_config['APPID'];
                //$data['appkey'] = $wxpay_config['APPKEY'];
                $data['partnerId'] = $wxpay_config['PARTNERID'];
                //$data['partnerkey'] = $wxpay_config['PARTNERKEY'];
                //$data['appsecret'] = $wxpay_config['APPSERCERT'];
                $data['timeStamp'] = $oParams->timeStamp;
                $data['nonceStr'] = $oParams->nonceStr;
                $data['paySign'] = $oParams->paySign;
                
                // 订单详情
                $data['order_sn'] = $order_sn;
                $data['bank_type'] = $bank_type;
                $data['body'] = $order_content;
                $data['total_fee'] = $total_fee;
                $data['transport_fee'] = $transport_fee;
                $data['product_fee'] = $product_fee;
                $data['spbill_create_ip'] = $spbill_create_ip;
                
                // 订单状态追踪
                $data['create_time'] = $time;
                $data['trade_start_times'] = 1;

                
                $this->assign('trade', $data);
                $ret  = M('b2c_wxtrade')->add($data); 
            } else {
                $this->assign('trade', $trade);
                $ret = M('b2c_wxtrade')->where(array('id'=>$trade['id']))->setInc('trade_start_times');
            }
            
            if($ret === FALSE) {
                Log::record('wxpay fail.r/w db fail. order_sn:'.$order_sn.' err:'.M('b2c_wxtrade')->getDbError());
                Log::save();
                $this->error('服务器忙！请稍后重试！');
            }
            $this->assign('receiver', $wxpay_config['name']);
            $this->assign('redirect_url', $redirect_url);
            $this->display();
        }else{
            $this->error('商家信息设置有误！请联系商家客服！');
        }
    }
    
    private function getOrderDesc($order_id) {
        $products = M('b2c_order_item')->join('inner join tp_b2c_product p on tp_b2c_order_item.product_id = p.product_id')
                ->where(array('tp_b2c_order_item.order_id'=>$order_id))
                ->field('p.name product_name')
                ->select();
        if(count($products) > 0) {
            $desc = '';
            foreach($products as $key => $v) {
                $desc .= $v['product_name'].' ;';
            }
            
            return trim(my_cus_substr($desc, 110), " ;") ;
        } else {
            
            return '订单'.$order_id;
        }
    } 
    
    public function cftpay() {
        $order_sn = $_GET['order_sn'];
        $token       = $this->token;
        $wecha_id = $this->wechat_id;
        Log::record('startCftpay: '.$token.':'.$order_sn.':'.$wecha_id);
        Log::save();
        if(empty($order_sn) || empty($token))
        {
            $this->error('请输入订单号');
        }
        
        $order = M('b2c_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>1))->find();
        if(!$order)
            $this->error('订单号不正确');

        $shop = M('b2c_shop')->where(array('token'=>$token))->find();
        if(!$shop)
            $this->error('商城不存在');

        $payment_where = array('token'=>$token, 'pay_code'=>'cftpay');
        if (!empty($this->branch_id)) 
        {
            $payment_where['branch_id'] = $this->branch_id;
        }
        $payment = M('b2c_payment')->where($payment_where)->find();
        $wxpay_config = unserialize($payment['pay_config']);
       
        if (empty($wxpay_config)) {
            $this->error('商家信息设置有误！请联系商家客服！');
        }
        $partnerId = $wxpay_config['partnerId'];
        $partnerKey = $wxpay_config['partnerKey'];
        
        // cft pay init parameter list
        $ver = '2.0';
        $charset = '1';
        $bank_type = '0';
        $desc = $this->getOrderDesc($order['order_id']);
        // purchaser_id
        $bargainor_id = $partnerId;
        $sp_billno = $order_sn;
        $total_fee = intval(floatval($order['price']) * 100); //分
        $fee_type = "1";
        $notify_url = $this->generatePayUrl('cftpay/notify');
        $callback_url = $this->generatePayUrl('cftpay/callback');
        // attach
        //$time_start = date('YmdHs', time());
        // time_expire
        
        // add audit for Cft Pay.
        $trade = M('b2c_cfttrade')->where(array('order_sn'=> $order_sn, 'token'=> $token))->find();
        if ($trade['n_transaction_id']) 
        {
            $this->error('已支付');
        }
        
        import("@.ORG.CftWapPayHelper");
        $cftWapPayHelper = new CftWapPayHelper($partnerId, $partnerKey);
        $cftWapPayHelper->setParameter('ver', $ver);
        $cftWapPayHelper->setParameter('charset', $charset);
        $cftWapPayHelper->setParameter('bank_type', $bank_type);
        $cftWapPayHelper->setParameter('desc', $desc);
        $cftWapPayHelper->setParameter('bargainor_id', $bargainor_id);
        $cftWapPayHelper->setParameter('sp_billno', $sp_billno);
        $cftWapPayHelper->setParameter('total_fee', strval($total_fee));
        $cftWapPayHelper->setParameter('fee_type', $fee_type);
        $cftWapPayHelper->setParameter('notify_url', $notify_url);
        $cftWapPayHelper->setParameter('callback_url', $callback_url);
        
        
        $cft_tokenId = $cftWapPayHelper->getTokenId();
        Log::record('cftpay start token:'.$token.' outorder:'.$order_sn.' wecha_id:'.$wecha_id.' token_id:'.$cft_tokenId, Log::INFO);
        Log::save();
        
        if(empty($cft_tokenId)) {
             $this->error('服务器忙！请刷新重试！');
        }

        if ($trade == null) 
        {
            $time = time();

            // 微信参数
            $data['token'] = $token;
            $data['wecha_id'] = $wecha_id;

            // 协议参数
            $data['partnerId'] = $partnerId;
            $data['partnerkey'] = $partnerKey;

            // 订单详情
            $data['order_sn'] = $order_sn;
            $data['ver'] = $ver;
            $data['charset'] = $charset;
            $data['bank_type'] = $bank_type;
            $data['desc'] = $desc;
            $data['total_fee'] = $total_fee;
            $data['fee_type'] = $fee_type;
            $data['token_id'] = $cft_tokenId;

            // 订单状态追踪
            $data['create_time'] = $time;
            $data['trade_start_times'] = 1;

            $ret  = M('b2c_cfttrade')->add($data); 
        }else {
            $ret = M('b2c_cfttrade')->where(array('id'=>$trade['id']))->setInc('trade_start_times');
        }

        if($ret === FALSE) {
            Log::record('cftpay fail.r/w db fail. order_sn:'.$order_sn.' err:'.M('b2c_cfttrade')->getDbError());
            Log::save();
            $this->error('服务器忙！请稍后重试！');
        }

        header("Location:"."https://wap.tenpay.com/cgi-bin/wappayv2.0/wappay_gate.cgi?token_id=".$cft_tokenId);
    }
    
    /**
     * wings payment
     */
    function wingpay(){
    	$order_sn = $_GET['order_sn'];
        $token       = $this->token;
        $wecha_id = $this->wechat_id;
        if(empty($order_sn)){
            $this->error('没有订单号');
        }
    	$pay_type = $_GET['pay_type'];
    	if ($pay_type == '1') {
	        $order = M('hotel_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'order_status'=>3))->find();
        }elseif ($pay_type == '2'){
        	$order = M('dine_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>2))->find();
        }else{
	        $order = M('b2c_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>1))->find();
        }
        if(!$order){
            $this->error('该订单不存在');
        }
        $payment_where = array('token'=>$token, 'pay_code'=>'wingpay', 'enabled'=>'1', 'pay_type'=>$pay_type);
   		if (!empty($this->branch_id)) {
            $payment_where['branch_id'] = $this->branch_id;
        }
        $payment = M('b2c_payment')->where($payment_where)->find();
        $wingpay_config = unserialize($payment['pay_config']);
       
        if (empty($wingpay_config)) {
            $this->error('商家未设置支付方式！请联系商家客服！');
        }
    	
        include_once(LIB_PATH."ORG/WingPay/WingPayApi.php");
		$conf = array(
			'mch_id' => $wingpay_config['mch_id'],
			'key' => $wingpay_config['key']
		);
		
		//return url
		$merchanturl     = $_GET['front_url'];
		$backmerchanturl = C('site_url').'/index.php/wingpay/notify';
		
    	//Notice：web pay->分, mobile pay->元,
		//Note: 产品金额  附加金额 订单总金额必须保留两位小数
		$attachamount    = '0.00';  		 //web pay $attachamount=0分
    	if ($pay_type == '1') {
        	$productamount   = number_format($order['prepayment'], 2, '.', '');  //web pay intval(floatval($price) * 100)分
        }elseif ($pay_type == '2'){
        	$productamount   = number_format($order['price'], 2, '.', '');  //web pay intval(floatval($price) * 100)分
        }else{
			$productamount   = number_format($order['price'], 2, '.', '');  //web pay intval(floatval($price) * 100)分
        }
		$orderamount = number_format($attachamount + $productamount, 2, '.', '');
		
		$orderreqtranseq = date('YmdHis').'00001';
    	if ($pay_type == '1') {
        	$order_desc = '订单';
        	$orderdate = date('Ymd', $order['submit_time']);
        }elseif ($pay_type == '2'){
        	$menus = json_decode($order['menus'], true);
        	$order_desc = $menus['name'];
        	$orderdate = date('Ymd', $order['submittime']);
        }else{
			$orderdate = date('Ymd', $order['create_time']);
			$order_desc = $this->getOrderDesc($order['order_id']);
        }
        
        $serialize = array(
        		'orderseq' => $order_sn,
        		'orderreqtranseq' => $orderreqtranseq,
        		'orderdate' => $orderdate,
        		'orderamount' => $orderamount,
        		'productamount' => $productamount,
        		'attachamount' => $attachamount,
        		'merchanturl' => $merchanturl,
        		'backmerchanturl' => $backmerchanturl,
        		'productdesc' => $order_desc,
        		'mch_id' => $wingpay_config['mch_id'],
				'key' => $wingpay_config['key'],
				'pay_type'=>$pay_type
        	);
        	$data = array(
        		'token' => $token,
				'order_sn' => $order_sn,
				'is_pay' => '0',
        		'set_params' => serialize($serialize),
        		'update_time' => time()
        	);
    	$trade = M('b2c_wingtrade')->where(array('order_sn'=> $order_sn, 'token'=> $token))->find();
        if ($trade) {
        	if ($trade['is_pay']) {
        		$this->success('该订单已付款，不能重复付款！');
        	}else{
        		M('b2c_wingtrade')->where(array('token' => $token, 'order_sn' => $order_sn, 'is_pay' => '0'))->save($data);
        	}
        }else{
			$data['create_time'] = time();
        	M('b2c_wingtrade')->add($data);
        }
        
	 	$wingpay = new WingPayApi($conf);
	 	$wingpay->setParameter('orderseq', $order_sn);
	 	$wingpay->setParameter('orderreqtranseq', $orderreqtranseq);
	 	$wingpay->setParameter('orderdate', $orderdate);
	 	$wingpay->setParameter('orderamount', $orderamount);
	 	$wingpay->setParameter('productamount', $productamount);
	 	$wingpay->setParameter('attachamount', $attachamount);
	 	$wingpay->setParameter('merchanturl', $merchanturl);
	 	$wingpay->setParameter('backmerchanturl', $backmerchanturl);
	 	$wingpay->setParameter('productdesc', $order_desc);
	 	echo $wingpay->bestpay('mobile');
    }
    
    function wingpay_notify(){
    	$order_sn = $_POST['ORDERSEQ'];
        if(empty($order_sn)){
            Log::record('wings pay error: 没有订单号！');
            Log::save();
        }
        $order = M('b2c_wingtrade')->where(array('order_sn'=> $order_sn, 'is_pay'=>'0'))->find();
         if(!$order){
         	Log::record('wings pay error: 该订单不存在！');
            Log::save();
         }
        $wingpay_config = unserialize($order['set_params']);
        if (empty($wingpay_config)) {
        	Log::record('wings pay error: 商家未设置支付方式！请联系商家客服！');
            Log::save();
        }
    	include_once(LIB_PATH."ORG/WingPay/WingPayApi.php");
		$conf = array(
			'mch_id' => $wingpay_config['mch_id'],
			'key' => $wingpay_config['key']
		);
    	$wingpay = new WingPayApi($conf);
	 	$wingpay->setParameter('uptranseq', $_POST['UPTRANSEQ']);
	 	$wingpay->setParameter('orderseq', $_POST['ORDERSEQ']);
	 	$wingpay->setParameter('orderamount', $_POST['ORDERAMOUNT']);
	 	$wingpay->setParameter('retncode', $_POST['RETNCODE']);
	 	$wingpay->setParameter('retninfo', $_POST['RETNINFO']);
	 	$wingpay->setParameter('trandate', $_POST['TRANDATE']);
	 	if (strtoupper($_POST['SIGN']) == strtoupper($wingpay->getSign('mobile'))) {
		 	$trade = M('b2c_wingtrade')->where(array('order_sn'=> $order_sn, 'token'=> $order['token'], 'is_pay'=>'0'))->find();
	        if ($trade) {
	        	$serialize = array(
	        		'uptranseq' => $_POST['UPTRANSEQ'],
	        		'trandate' => $_POST['TRANDATE'],
	        		'retncode' => $_POST['RETNCODE'],
	        		'retninfo' => $_POST['RETNINFO'],
	        		'orderreqtranseq' => $_POST['ORDERREQTRANSEQ'],
	        		'orderseq' => $_POST['ORDERSEQ'],
	        		'orderamount' => $_POST['ORDERAMOUNT'],
	        		'productamount' => $_POST['PRODUCTAMOUNT'],
	        		'attachamount' => $_POST['ATTACHAMOUNT'],
	        		'curtype' => $_POST['CURTYPE'],
	        		'encodetype' => $_POST['ENCODETYPE'],
	        		'attach' => $_POST['ATTACH'],
	        		'sign' => $_POST['SIGN'],
	        	);
	        	$data = array(
					'is_pay' => '1',
	        		'return_params' => serialize($serialize),
	        		'update_time' => time()
	        	);
	        	M('b2c_wingtrade')->where(array('order_sn' => $order_sn))->save($data);
		 		if ($wingpay_config['pay_type'] == '1') {
			        M('hotel_order')->where(array('sn'=> $order_sn))->save(array('order_status'=>6));
		        }else if ($wingpay_config['pay_type'] == '2') {
		        	M('dine_order')->where(array('sn'=> $order_sn))->save(array('status'=>3));
		        }else {
			 		M('b2c_order')->where(array('sn'=>$order_sn))->save(array('status'=>2, 'update_time' => time()));
					//减库存
					ShopAction::minusInventory($order_sn);
		        }
	        }
	 	}else{
	 		Log::record('union pay sign error: 验证签名失败！');
            Log::save();
	 	}
    }
    
    
    function unionpay1(){
		$order_sn = $_GET['order_sn'];
        $token       = $this->token;
        $wecha_id = $this->wechat_id;
        if(empty($order_sn)){
            $this->error('没有订单号');
        }
        $order = M('b2c_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>1))->find();
        if(!$order){
            $this->error('该订单不存在');
        }
        $payment_where = array('token'=>$token, 'pay_code'=>'unionpay', 'enabled'=>'1');
        $payment = M('b2c_payment')->where($payment_where)->find();
        //$wingpay_config = unserialize($payment['pay_config']);
        if (empty($payment)) {
            $this->error('商家未设置银联支付方式！请联系商家客服！');
        }
        
		if (version_compare(phpversion(), '5.4.10', '>')) {
			include_once(LIB_PATH.'ORG/Payment/UnionPay/lib.php');
		}else{
			include_once(LIB_PATH.'ORG/Payment/UnionPay/netpayclient.php');
		}
		$merid = buildKey('keys/MerPrK_808080301000216_20141106164338.key');
		if (empty($merid)) {
			$this->error('未设置商户号！');
		}
		$ordid      = $this->get_order_no($order['sn']);
		$transamt   = padstr($order['price']*100, 12); //订单交易金额，12位长度，左补0，单位：分
		$curyid     = '156'; 	   //订单交易币种，3位长度，固定为人民币156
		$transdate  = date('Ymd');
		$transtype  = '0001';      //交易类型，4位长度，"0001"表示消费交易，"0002"表示退货交易
		$version    = '20070129';  //支付接入版本号20040916，20070129
		$gateid 	= '8607';
		$priv1 		= 'wechat';
		if (!signOrder($merid, $ordid, $transamt, $curyid, $transdate, $transtype)) {
			$this->error('订单签名验证失败！');
		}
		$chkvalue = sign($merid.$ordid.$transamt.$curyid.$transdate.$transtype.$priv1);
		
		//PageRetUrl 返回支付后的商户网站页面
		if (isset($_GET['fxs_id'])) {
			$pagereturl = C('site_url').'/index.php/Fxs/Shop/my?token='.$token.'&fxs_id='.$_GET['fxs_id'];
		}elseif (isset($_GET['type'])) {
			$pagereturl = C('site_url').'/index.php/Fxs/Fxs/my?token='.$token;
		}else{
			$pagereturl = C('site_url').'/index.php/Wap/Shop/my?token='.$token;
		}
		//后台接受应答地址，用于商户记录交易信息和处理
		$bgreturl	= C('site_url').'/index.php/unionpay/notify';	

    	$trade = M('b2c_wingtrade')->where(array('order_sn'=> $order_sn, 'token'=> $token))->find();
        if ($trade) {
        	if ($trade['is_pay']) {
        		$this->success('该订单已付款，不能重复付款！');
        	};
        }else{
        	$serialize = array(
        		'MerId' => $merid,
        		'OrdId' => $ordid,
        		'TransAmt' => $transamt,
        		'CuryId' => $curyid,
        		'TransDate' => $transdate,
        		'TransType' => $transtype,
        		'Version' => $version,
        		'BgRetUrl' => $bgreturl,
        		'PageRetUrl' => $pagereturl,
        		'GateId' => $gateid,
        		'Priv1' => $priv1,
        		'ChkValue' => $chkvalue
        	);
        	$data = array(
        		'token' => $token,
				'order_sn' => $order_sn,
				'is_pay' => '0',
        		'set_params' => serialize($serialize),
        		'create_time' => time(),
        		'update_time' => time()
        	);
        	M('b2c_wingtrade')->add($data);
        }
		
		include_once(LIB_PATH."ORG/Payment/UnionPay/UnionPay.php");
		$unionpay = new UnionPay();
		$unionpay->setParameter('MerId', $merid);
		$unionpay->setParameter('OrdId', $ordid);
		$unionpay->setParameter('TransAmt', $transamt);
		$unionpay->setParameter('CuryId', $curyid);
		$unionpay->setParameter('TransDate', $transdate);
		$unionpay->setParameter('TransType', $transtype);
		$unionpay->setParameter('Version', $version);
		$unionpay->setParameter('BgRetUrl', $bgreturl);
		$unionpay->setParameter('PageRetUrl', $pagereturl);
		$unionpay->setParameter('GateId', $gateid);
		$unionpay->setParameter('Priv1', $priv1);
		$unionpay->setParameter('ChkValue', $chkvalue);
		echo $unionpay->_buildForm('web');	
    }
    
    
    function unionpay_notify1(){
    	if (version_compare(phpversion(), '5.4.10', '>')) {
			include_once(LIB_PATH.'ORG/Payment/UnionPay/lib.php');
		}else{
			include_once(LIB_PATH.'ORG/Payment/UnionPay/netpayclient.php');
		}
		$merid = buildKey('keys/MerPrK_808080301000216_20141106164338.key');
		if (empty($merid)) {
			Log::record('union pay merid error: 未设置商户号！');
            Log::save();
		}
		
		$merid = $_REQUEST["merid"];
		$orderno = $_REQUEST["orderno"];
		$transdate = $_REQUEST["transdate"];
		$amount = $_REQUEST["amount"];
		$currencycode = $_REQUEST["currencycode"];
		$transtype = $_REQUEST["transtype"];
		$status = $_REQUEST["status"];
		$checkvalue = $_REQUEST["checkvalue"];
		$gateId = $_REQUEST["GateId"];
		$priv1 = $_REQUEST["Priv1"];
		
		$order_sn = $this->get_order_no($orderno);
    	$order = M('b2c_order')->where(array('sn'=> $order_sn, 'status'=>1))->find();
        if(!$order){
         	Log::record('union pay error: 该订单不存在！');
            Log::save();
        }
    	$payment_where = array('token'=>$order['token'], 'pay_code'=>'unionpay', 'enabled'=>'1');
        $payment = M('b2c_payment')->where($payment_where)->find();
        $wingpay_config = unserialize($payment['pay_config']);
        if (empty($wingpay_config)) {
        	Log::record('union pay error: 商家未设置支付方式！请联系商家客服！');
            Log::save();
        }
		
		$plain = $merid . $orderno . $amount . $currencycode . $transdate . $transtype . $status . $checkvalue;
		//对订单验证签名  
		$flag = verifyTransResponse($merid, $orderno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
		$flag = verify($plain, $checkvalue);  
		if (!$flag) {
			Log::record('union pay sign error: 验证签名失败！');
            Log::save();
		}else{
		 	if ($status == '1001'){
                $trade = M('b2c_wingtrade')->where(array('order_sn'=> $order_sn, 'token'=> $order['token']))->find();
		        if ($trade && !$trade['is_pay']) {
		        	$serialize = array(
		        		'merid' => $merid,
						'orderno' => $orderno,
						'transdate' => $transdate,
						'amount' => $amount,
						'currencycode' => $currencycode,
						'transtype' => $transtype,
						'status' => $status,
						'checkvalue' => $checkvalue,
						'gateId' => $gateId,
						'priv1' => $priv1,
		        	);
		        	$data = array(
						'is_pay' => '1',
		        		'return_params' => serialize($serialize),
		        		'update_time' => time()
		        	);
		        	M('b2c_wingtrade')->where(array('order_sn' => $order_sn))->save($data);
		        }
		 		M('b2c_order')->where(array('sn'=>$order_sn))->save(array('status'=>2, 'update_time' => time()));
				//减库存
				ShopAction::minusInventory($order_sn);
            }else{
                Log::record('union pay failed.');
            	Log::save();
            }
		}  
    }
    
	function unionpay() {
		include_once LIB_PATH.'ORG/Payment/UnionPay/mobile/lib/common.php';
		include_once LIB_PATH.'ORG/Payment/UnionPay/mobile/lib/SDKConfig.php';
		include_once LIB_PATH.'ORG/Payment/UnionPay/mobile/lib/secureUtil.php';
		include_once LIB_PATH.'ORG/Payment/UnionPay/mobile/lib/httpClient.php';
		include_once LIB_PATH.'ORG/Payment/UnionPay/mobile/lib/log.class.php';
		
    	$order_sn = $_GET['order_sn'];
        $token       = $this->token;
        $wecha_id = $this->wechat_id;
        if(empty($order_sn)){
            $this->error('没有订单号');
        }
        
        $trade = M('b2c_wingtrade')->where(array('order_sn'=> $order_sn, 'token'=> $token))->find();
        if ($trade && $trade['is_pay'] == '1') {
        	$this->success('该订单已付款，不能重复付款！');
        }
		
        $pay_type = $_GET['pay_type'];
    	if ($pay_type == '1') {
	        $order = M('hotel_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'order_status'=>3))->find();
        }elseif ($pay_type == '2'){
        	$order = M('dine_order')->where(array('sn'=> $order_sn,  'status'=>2))->find();
        }else{
	        $order = M('b2c_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>1))->find();
        }
        if(!$order){
            $this->error('该订单不存在');
        }
        $payment_where = array('token'=>$token, 'pay_code'=>'unionpay', 'enabled'=>'1',  'pay_type'=>$pay_type);
		if (!empty($this->branch_id)) {
            $payment_where['branch_id'] = $this->branch_id;
        }
        $payment = M('b2c_payment')->where($payment_where)->find();
        //$wingpay_config = unserialize($payment['pay_config']);
        if (empty($payment)) {
            $this->error('商家未设置银联支付方式！请联系商家客服！');
        }
		
		
    	//PageRetUrl 返回支付后的商户网站页面
		$pagereturl = $_GET['front_url'];
		
		/**
		 *	以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己需要，按照技术文档编写。该代码仅供参考
		 */
		// 初始化日志
		$log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
		$log->LogInfo ( "============处理前台请求开始===============" );
		
		if ($pay_type == '1') {
			$total_fee = intval($order['prepayment']*100);
        }elseif ($pay_type == '2'){
			$total_fee = intval($order['price']*100);
        }else{
			$total_fee = intval($order['price']*100);
        }
		$order_time = date('YmdHis');
		// 初始化日志
		$params = array(
				'version' => '5.0.0',						//版本号
				'encoding' => 'UTF-8',						//编码方式
				'certId' => getSignCertId (),				//证书ID  
				'txnType' => '01',							//交易类型	
				'txnSubType' => '01',						//交易子类
				'bizType' => '000000',						//业务类型
				'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  		//前台通知地址
				'backUrl' => SDK_BACK_NOTIFY_URL,			//后台通知地址	
				'signMethod' => '01',						//签名方法
				'channelType' => '08',						//渠道类型
				'accessType' => '0',						//接入类型
				'merId' => '898340148160231',				//商户代码
				//'merId' => '898340183980105',				//商户代码测试
				'orderId' => $order_sn,						//商户订单号
				'txnTime' => $order_time,					//订单发送时间
				'txnAmt' => $total_fee,						//交易金额 单位分
				'currencyCode' => '156',					//交易币种
				'defaultPayType' => '0001',			
				//默认支付方式	
				);
		// 签名
		sign ( $params );
		
        	$data = array(
        		'token' => $token,
				'order_sn' => $order_sn,
				'is_pay' => '0',
        		'set_params' => serialize(array_merge($params, array('return_url'=>$pagereturl, 'pay_type'=>$pay_type))),
        		'update_time' => time()
        	);
		if (!$trade) {
			$data['create_time'] = time();
        	M('b2c_wingtrade')->add($data);
        }else{
			  M('b2c_wingtrade')->where(array('token' => $token, 'order_sn' => $order_sn,'is_pay' => '0'))->save($data);      	
        }			
		
		/*手机WAP支付方式*/
		// 前台请求地址
		$front_uri = SDK_FRONT_TRANS_URL;
		$log->LogInfo ( "前台请求地址为>" . $front_uri );
		// 构造 自动提交的表单
		$html_form = create_html ( $params, $front_uri );
		$log->LogInfo ( "-------前台交易自动提交表单>--begin----" );
		$log->LogInfo ( $html_form );
		$log->LogInfo ( "-------前台交易自动提交表单>--end-------" );
		$log->LogInfo ( "============处理前台请求 结束===========" );
		echo $html_form;
    }

    function front_notify() {
    	$orderId = $_POST['orderId'];
		$order = M('b2c_wingtrade')->where(array('order_sn'=> $orderId, 'is_pay'=>'0'))->find();
    	if ($order) {
			if ($_POST['respCode'] == '00') {
			    M('b2c_wingtrade')->where(array('order_sn'=> $orderId))->save(array('is_pay'=>'1', 'return_params'=>serialize($_POST)));
			}else{
			    M('b2c_wingtrade')->where(array('order_sn'=> $orderId))->save(array( 'return_params'=>serialize($_POST)));
			}    	
			$detail = unserialize($order['set_params']);
			if ($detail['pay_type'] == '1') {
		        M('hotel_order')->where(array('sn'=> $orderId))->save(array('order_status'=>6));
			}elseif ($detail['pay_type'] == '2') {
	        	M('dine_order')->where(array('sn'=> $orderId))->save(array('status'=>3));
			}else{
				M('b2c_order')->where(array('sn'=>$orderId))->save(array('status'=>2, 'update_time' => time()));
				//减库存
				ShopAction::minusInventory($orderId);
			}
			header('Location:'.$detail['return_url']);
		}
    }
    
	function convert_year_char($v = ''){
		$years = array();
		$cur_year = 2014;
		for ($i = 0; $i < 10; $i++) {
			$years[$i] = $cur_year++;
		}
		for ($j = 0; $j < 26; $j++) {
			$years[chr(65 + $j)] = $cur_year++;
		}
		for ($k = 0; $k < 26; $k++) {
			$years[chr(97 + $k)] = $cur_year++;
		}
		return strlen($v) == 1 ? $years[$v] : current(array_keys($years, $v));
	}
	
	function get_order_no($sn) {
		if (strlen($sn) == 16) {
			$prefix = $sn{0};
			$suffix = substr($sn, 1);
		}else{
			$prefix = substr($sn, 0, 4);
			$suffix = substr($sn, 4);
		}
		return $this->convert_year_char($prefix).$suffix;
	}
    
    
    public static function minusInventory($order_sn){
        $model = new Model();
        $orders = $model->table('tp_b2c_order as orders')
                        ->join('tp_b2c_order_item as item on orders.order_id = item.order_id')
                        ->where(array('orders.sn'=>$order_sn))
                        ->field('item.product_id as pid, orders.order_id as order_id,item.item_id as item_id, orders.total as total,item.count as count,item.size_name,item.color_name')
                        ->select();
        if(count($orders) < 1){
            return 0;
        }
        foreach ($orders as $key => $val) {
            $product = M('b2c_product')->where(array('product_id'=>$val['pid'],'status'=>1))->find();
            if(!$product){
                continue;
            }
            $category_ids = array('1124', '1134', '1129',  '1174');
            if(in_array($product['category_id'], $category_ids)){
            	$orders['total'] = 1;
            }
            if($product['inventory'] - $orders['total'] < 0){
                continue;
            }
            if($val['size_name'] != '' || $val['color_name'] != ''){	
                $spec_where = array('product_id'=>$val['pid'],'size_name'=>$val['size_name'],'color_name'=>$val['color_name'],'status'=>0);
                $spec = M('b2c_product_spec')->where($spec_where)->find();
                if(!$spec){
                    continue;
                }
                if($spec['inventory'] - $val['count'] < 0){
                    continue;
                }
                M('b2c_product_spec')->where($spec_where)->setDec('inventory', $val['count']);
            }
            M('b2c_product')->where(array('product_id'=>$val['pid'],'status'=>1))->setDec('inventory', $val['count']);
        }
        return 1;
    }


    /**
    *读取库存
    *-1,未找到产品， -2,没有此种规格的产品 
    **/
    public function inventoryOf($pid, $size_id, $color_id)
    {
        $product = M('b2c_product')->where(array('product_id'=>$pid,'status'=>1))->find();
        if(!$product){
            return -1;
        }

        if($size_id != '' || $color_id != ''){
        	$spec = M('b2c_product_spec')->where(array('product_id'=>$pid, 'size_id'=>$size_id, 'color_id'=>$color_id))->find();
        	 if (!$spec) {
        		return $product['inventory'];
        	}
            $spec_where = array('product_id'=>$pid, 'size_id'=>$size_id, 'color_id'=>$color_id, 'status'=>0);
            $spec = M('b2c_product_spec')->where($spec_where)->find();
            if(!$spec){
                return -2;
            }
            return $spec['inventory'];
        } 
        return $product['inventory'];
    }
}  
?>

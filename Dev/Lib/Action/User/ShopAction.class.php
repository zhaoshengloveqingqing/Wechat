<?php
class ShopAction extends UserAction
{
	public $pagesize = 20;
    protected $branch_id = null;
    protected function _initialize()
    {
        $this->function = 'shangcheng';
        $this->token = $_SESSION['token']; 

        $this->branch_id = $_GET['bid'];
        Log::write("branch_id:".$this->branch_id);
		vendor("Logger.Logger", LIB_PATH.'../Extend/Vendor');
		//$this->logger = new Logger();
		//$logger->trace($_POST);
        parent::_initialize();
        parent::checkOpenedFunction();
    }

    public function index()
    {
        $shop_db    = M('b2c_shop');
        $list  = null;
        $show  = null;
       
        $count  = $shop_db->where(array('token'=>$this->token,'fxs_id'=>0, 'status'=>1))->count();
        $Page   = new Page($count,12);
        $show   = $Page->show();

        $list   = $shop_db->where(array('token'=>$this->token,'fxs_id'=>0, 'status'=>1))->limit($Page->firstRow.','.$Page->listRows)->select();
       
        $this->assign('page',$show);    
        $this->assign('shopes',$list);
        $this->display();    
    }

    public function branch()
    {
        if (IS_POST) 
        {
            $data['address']    = trim($_POST['address']);
            $data['telephone']  = trim($_POST['telephone']);
            $data['name']       = trim($_POST['name']);
                    
            $now = time();
            
            $data['update_time'] = $now;
            $data['status']      = 1;

            $shop_db    = M('b2c_shop');
            if (empty($this->branch_id)) 
            {
                $data['token']       = $this->token;
                $data['fake_id']     = $this->get_shop_sn();
                $data['create_time'] = $now;
				
                $ret = $shop_db->add($data);
            }
            else
            {
                $where['token']       = $this->token;
                $where['fake_id']     = $this->branch_id;    

                $ret = $shop_db->where($where)->save($data);
            }
            
            if ($ret) 
            {
                $this->success('操作成功',U('Shop/index'));
            }
        }
        else
        {
            $shop_db    = M('b2c_shop');

            $where['token']      = $this->token;
            $where['fake_id']    = $this->branch_id;

            $branch = $shop_db->where($where)->find();
            if ($branch != false) 
            {
                $this->assign('branch', $branch); 
            }
            $this->display();
            
        }
    }

    public function delBranch()
    {
        $shop_db    = M('b2c_shop');

        $where['token']      = $this->token;
        $where['fake_id']    = $this->branch_id;
                
        $data['update_time'] = time();
        $data['status']      = 2;

        $ret = $shop_db->where($where)->save($data);
        if ($ret) 
        {
            $this->success('操作成功',U('Shop/index'));
        }
        else
        {
            $this->success('删除失败',U('Shop/index'));
        }
        
    }

    public function products() 
    {    

        $product_db = M('b2c_product');
        $category_db = M('b2c_category');
        $categories  = $category_db->where(array('token'=>$this->token,'branch_id'=>$this->branch_id, 'status' => 1))->select();

        if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
            if (empty($key))
            {
                $this->error("关键词不能为空");
            }

            $map['token']               = $this->token; 
            $map['branch_id']           = $this->branch_id; 
            $map['status']              = 1; 
            $map['name|intro|keyword']  = array('like',"%$key%"); 

            $product_count   = $product_db->where($map)->count(); 
            $Page   = new Page($product_count,50);
            //默认通过$_GET['p'] 获取当前页面
            $show   = $Page->show();
            $list   = $product_db->where($map)->order('category_id desc, product_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            $where = array('token'=>$this->token,'branch_id'=>$this->branch_id, 'status' => 1);
            $category_id = 0;
            if (isset($_GET['cid'])) 
            {
                $category_id = intval($_GET['cid'], 0); 
            }

            $count  = $product_db->where($where)->count();
            $Page   = new Page($count,50);
            $show   = $Page->show();

            $sql = "select p.product_id, p.category_id,p.`name`,p.market_price, p.shop_price,p.`status`,p.logo_url,p.intro, p.update_time, p.`inventory`, c.`name`  as category_name "
                        ." from tp_b2c_product as p LEFT JOIN tp_b2c_category as c on p.category_id = c.category_id "
                        ." where p.token='$this->token' and p.branch_id = c.branch_id and c.branch_id='$this->branch_id' and p.status=1 ";
            if ($category_id)
            {
                $where['category_id'] = $category_id;
                $sql .= " and c.category_id='$category_id'";
            }

            $sql .= " limit ".$Page->firstRow.','.$Page->listRows;

            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $list = $Model->query($sql);
        }
        $this->assign('categories',$categories); 
        $this->assign('page',$show); 
        $this->assign('list',$list);
        
        $this->display();
    }

	/**
	 * Desc :  generate qrcode
	 *
	 * @since 2014-9-15
	 */
	public function cats_qr(){
        $cid = $this->_get('cid'); 
        $category_db = M('b2c_category');
        $where = array('category_id' => $cid);
        $partner = $category_db->where($where)->find();
        if ($partner){
            if (!empty($partner['qrcode_pic_url'])) {
                $this->assign('qrcode_url',$partner['qrcode_pic_url']);
            } else {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
                $product_url = 'http://'.C('wx_handler_server').U('Fxs/Shop/cat_index', array('cid'=>$cid,'token'=>$this->token));
				//exit($product_url);
                $gen->build($product_url, 'partner', $this->token);
                $qrcode_pic_url = $gen->getUrl();
                $category_db->where($where)->save(array('qrcode_pic_url'=>$qrcode_pic_url));
                $this->assign('qrcode_url',$qrcode_pic_url);
            }
            $this->display();
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/partner_index'));
        }
    }
    public function cats()
    {        
        $category_db = M('b2c_category');

        if(IS_POST)
        {
            $map['token']         = $this->token; 
            $map['branch_id']     = $this->branch_id; 
            $map['status']        = 1; 
            $map['name|desc']     = array('like',"%$key%"); 

            $count  = $category_db->where($map)->count();  
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $category_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('sort desc')->select();
        }
        else
        {
            
            $where  = array('token' => $this->token,'branch_id'=>$this->branch_id, 'status' => 1);
            $count  = $category_db->where($where)->count();
 
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $category_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('sort desc')->select();
        }

        $this->assign('page',$show);        
        $this->assign('list',$list);

        $this->display();        
    }


    public function catAdd()
    { 
        if(IS_POST)
        {
            $category_db = M('b2c_category');
            $data['token']     = $this->token;
            $data['branch_id'] = $this->branch_id;
            $data['name']      = $_POST['name'];
            $data['desc']      = $_POST['desc'];
            $data['logo_url']  = $_POST['logo_url'];
			$data['percent']   =$_POST['percent'];
			//$data['model']      ='mb';
            $data['status'] = 1;
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            
		Log::write("cate data:".print_r($data,true));
           
            $category_id = $category_db->add($data);
            if ($category_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/cats',array('bid'=>$this->branch_id)));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/catAdd',array('bid'=>$this->branch_id)));
            }

        }
        else 
        {
            $this->display('catSet');
        }
    }

    public function catDel()
    {
        $cid = $this->_get('cid','intval',0);

        if(IS_GET)
        {                              
            $where = array('category_id'=>$cid, 'branch_id'=>$this->branch_id, 'token'=>$this->token, 'status' => 1);

            $category_db = M('b2c_category');

            $category = $category_db->where($where)->find();
            if ($category == null)
            {
                $this->success('操作成功',U(MODULE_NAME.'/cats'));
            }

            $product_db = M('b2c_product');

            $product_count = $product_db->where(array('category_id'=>$cid, 'branch_id'=>$this->branch_id, 'token' => $this->token, 'status' => 1))->count();
            if ($product_count > 0)
            {
                $this->error('本分类下有商品，请删除商品后再删除分类',U(MODULE_NAME.'/index', array('cid' => $cid)));
            }

            $ret = $category_db->where($where)->save(array('status' => 2));

            if($ret == 1)
            {
                $this->success('操作成功',U(MODULE_NAME.'/cats',array('bid'=>$this->branch_id)));
            }
            else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/cats',array('bid'=>$this->branch_id)));
            }
        }        
    }


    public function catSet()
    {
        if(IS_POST)
        { 
            //更新分类信息
            $category_id    = $this->_post('cid','intval', 0);
            $where          = array('category_id'=> $category_id, 'branch_id'=>$this->branch_id, 'token'=>$this->token);

            $category_db    = M('b2c_category');
            $category       = $category_db->where($where)->find();
            if ($category == false)
            {
                //找不到也是修改成功
                Log::write("非法更新分类信息：category_id:".$category_id, Log::INFO);
                $this->success('操作成功',U(MODULE_NAME.'/cats', array('bid'=>$this->branch_id)));
            }
            
            $sort = trim($_POST['sort']);
            if ($sort && !preg_match('/^\d+$/', $sort)) {
            	$this->error('请输入数字');
            }

            $data['token']      = $this->token;
            $data['branch_id']  = $this->branch_id;
			//$data['model']      ='mb';
			$data['percent']    = $_POST['percent'];
            $data['name']       = $_POST['name'];
            $data['parent']     = $_POST['parent'];
            $data['type']       = $_POST['type'];
            $data['desc']       = $_POST['desc'];
            $data['logo_url']   = $_POST['logo_url'];
            $data['bgcolor']    = $_POST['bgcolor'];
            $data['sort']    	= $_POST['sort'];

            $data['update_time']= time();
			
            $ret = $category_db->where($where)->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/cats', array('bid'=>$this->branch_id)));
            }
            else
            {
                Log::write("更新分类信息失败：category_id:".$category_id.'; error:'.$category_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $category_id = $this->_get('cid','intval', 0);
            $category = M('b2c_category')->where(array('category_id'=>$category_id, 'branch_id'=>$this->branch_id, 'token'=>$this->token))->find();
            if(empty($category))
            {
                $this->error("没有相应记录.您现在可以添加.",U(MODULE_NAME.'/catAdd',array('bid'=>$this->branch_id)));
            }
            $this->assign('category', $category);

            $other_categories = M('b2c_category')->where(array('category_id'=>array('neq',$category_id), 'branch_id'=>$this->branch_id, 'token'=>$this->token))->find();
            $this->assign('otherCat', $other_categories);

            $this->display();    
        }
    }

    /*
     * 添加产品
     */
    public function add()
    { 
        $token = $this->token;
        
        $categories = M('b2c_category')->where(array('token' => $token, 'branch_id'=>$this->branch_id,'status'=>array('neq',2)))->select();
        $this->assign('categories', $categories);
        $size_set = C('SIZE');
        $color_set = C('COLOR');
        $this->assign('size_set',$size_set);
        $this->assign('color_set',$color_set);
        $this->display('set');
    }

    public function set()
    {
        
        $token = $this->token;

        $product_id = $this->_get('gid','intval', 0); 
        $product_db       = M('b2c_product');
        $product = $product_db->where(array('product_id'=>$product_id))->find();
        if(empty($product))
        {
            $this->error("找不到相应记录.您现在可以添加.",U(MODULE_NAME.'/add',array('bid'=>$this->branch_id)));
        }

        //分类
        $catWhere = array('token'=>$token, 'branch_id'=>$this->branch_id);

        $category_db    = M('b2c_category');
        $cats = $category_db->where($catWhere)->select();
        $this->assign('categories',$cats);
        
        $thisCat    = $category_db->where(array('category_id'=>$product['category_id']))->find();
        $this->assign('thisCat',$thisCat);

        $size_set = C('SIZE');
        $color_set = C('COLOR');
        $this->assign('size_set',$size_set);
        $this->assign('color_set',$color_set);
        $this->assign('the_size_set',unserialize($product['size_set']));
        $this->assign('the_color_set',unserialize($product['color_set']));

        $specs = M('b2c_product_spec')->where(array('product_id'=>$product_id,'status'=>0))->select();
        $this->assign('specs',$specs);
        //
        $this->assign('product',$product);
        $this->display();    
        
    }

        /**
     * 商品类别ajax select
     *
     */
    public function ajaxCatOptions(){
        $parent_id = intval($_GET['parentid']);
        $category_db = M('b2c_category');

        $cat_Where = array('parent_id'=>$parent_id, 'token'=>$this->token);
        $cats = $category_db->where($cat_Where)->select();

        $str = '';
        if ($cats)
        {
            foreach ($cats as $c)
            {
                $str.='<option value="'.$c['id'].'">'.$c['name'].'</option>';
            }
        }
        $this->show($str);
    }

    public function save_product()
    {
        if(IS_POST)
        { 
            //若product_id存在则是更新产品信息
            $product_id = $this->_post('gid', 'intval', 0 );

            //分类
            $category_id = intval($_POST['cid']); 
            $category = M('b2c_category')->where(array('category_id' => $category_id, 'branch_id' => $this->branch_id, 'token' => $this->token,'status'=>array('neq',2)))->find();
            if(empty($category))
            {
                $this->error("请先添加分类。",U(MODULE_NAME.'/catAdd',array('bid'=>$this->branch_id)));
            }

            $product_db = M('b2c_product');

            $data['token'] = $this->token;
            $data['branch_id'] = $this->branch_id;
			$data['percent']   = $_POST['percent'];
            $data['name']           = $_POST['name'];
            $data['market_price']   = $_POST['market_price'];
            $data['shop_price']     = $_POST['shop_price'];
            $data['intro']          = $_POST['intro'];
            $data['logo_url']       = $_POST['logo_url'];
            $data['keyword']        = $_POST['keyword'];
            $data['category_id']    = $_POST['cid'];
            $data['status']         = 1;
            $data['size_set']       = serialize( $_POST['size_set'] );
            $data['color_set']      = serialize( $_POST['color_set'] );
            $data['inventory']      = $_POST['inventory'];

            $data['size_alias']     = $_POST['size_alias'] == "" ? '尺寸' : $_POST['size_alias'];
            $data['color_alias']    = $_POST['color_alias'] == "" ? '颜色' : $_POST['color_alias'];

            $data['update_time']    = time();

            $ret = false;
            $where = array('product_id' => $product_id, 'branch_id' => $this->branch_id, 'token' => $this->token, 'function' => $this->function);
            $product = $product_db->where($where)->find();
            if($product_id && $product)
            {
                $data['create_time']    = $data['update_time'];
                $ret = $product_db->where($where)->save($data);
                if($ret !== false )
                {
                    $ret = $this->addProductSpecs($product_id,$_POST['spec'],true);
                }
            }
            elseif(!$product_id)
            {
                $ret = $product_db->add($data);
                if($ret !== false )
                {
                    $ret = $this->addProductSpecs($ret,$_POST['spec'],false);
                }
            }
            if ($ret !== false) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/products',array('bid'=>$this->branch_id)));
            }
            else
            {
                Log::write('添加信息失败 error:'.$product_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
    }

    /**
    *添加商品规格，$productId,商品id，$isEdit,是否是更新
    **/
    private function addProductSpecs($productId,$data,$isEdit)
    {
        if($isEdit)
        {
            M('b2c_product_spec')->where(array('product_id'=>$productId))->setField('status',1);
        }
        if($data)
        {
            $index = 0;
            $updata = array();
            foreach ($data as $key => $val) {
                $val['product_id'] = $productId;
                $val['status']     = 0;
                $updata[$index]      = $val;
                $index ++;
            }
            $ret = M('b2c_product_spec')->addAll($updata);
            //dump($data);
            //exit();
            return $ret;
        }
        return true;
    }

    //商品类别下拉列表
    public function catOptions($cats,$selectedid){
        $str='';
        if ($cats){
            foreach ($cats as $c){
                $selected='';
                if ($c['id']==$selectedid){
                    $selected=' selected';
                }
                $str.='<option value="'.$c['id'].'"'.$selected.'>'.$c['name'].'</option>';
            }
        }
        return $str;
    }


    public function del()
    {
        $product_db = M('b2c_product');

        $token = $this->token;

        $product_id = $this->_get('gid', 'intval', 0);
        if(IS_GET)
        {                              
            $where = array('product_id'=>$product_id,'token'=>$this->token, 'branch_id'=>$this->branch_id);
            $product = $product_db->where($where)->find();

            if ($product === null)  
            {
                //找不到也是修改成功
                Log::record("非法更新商品信息：good_id:".$product_id, Log::INFO);
                $this->success('操作成功',U(MODULE_NAME.'/products',array('bid'=>$this->branch_id)));
            }

            $ret = $product_db->where($where)->save(array('status' => 2));
            if($ret == true)
            {
                //删除对应图文消息和关键词
                M('img') -> where(array('id' => $product['img_id'], 'token'=>$token ,'function'=>'shangcheng'))->save(array('status' => 2));
                M('keyword')->where(array('pid' => $product['img_id'], 'token'=>$token ,'function'=>'shangcheng'))->delete();
                $this->success('操作成功',U(MODULE_NAME.'/products',array('bid'=>$this->branch_id)));
            }
            else
            {
                //找不到也是修改成功
                Log::record("更新商品信息失败：good_id:".$product_id.';error:'.$product_db->getError(), Log::INFO);
                $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/products',array('bid'=>$this->branch_id)));
            }
        }        
    }


    public function orders()
    {
    	$fxs_id = $_GET['fxs_id'];
		$reguser = C('DB_PREFIX') .'reguser';
		$b2c_order = C('DB_PREFIX') .'b2c_order';
		//$branch_id = $this->branch_id;
		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
        
    	if (isset($_GET['status'])) {
            $status = $this->_get('status');
            if ($this->_get('status') == '9') {
                $status = '1';
            }else{
            	$status = $this->_get('status');
            }
        }
        
		//table count
        $model = new Model(); 
		$sql = 'SELECT COUNT(*) as num FROM  `tp_b2c_order` orders
				LEFT JOIN `tp_reguser` reguser ON (reguser.`id` = `fxs_id` AND reguser.status = \'1\')
				LEFT JOIN `tp_partner` partner ON reguser.num = partner.num 
				WHERE orders.token = \''.$this->token.'\'';
		if ($key) {
			$sql .= ' AND orders.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
    	if (isset($status)) {
			$sql .= ' AND orders.`status` = \''.$status.'\'';
		}
		$order_count = $model->query($sql);
		$count = $order_count ? $order_count[0]['num'] : 0;
    	
		$page = new Page($count, $this->pagesize);
	    if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		
		//order list
		$sql = 'SELECT reguser.truename AS pname, orders.*
				FROM  `tp_b2c_order` orders
				LEFT JOIN `tp_reguser` reguser ON (reguser.`id` = `fxs_id` AND reguser.status = \'1\')
				LEFT JOIN `tp_partner` partner ON reguser.num = partner.num 
				WHERE orders.token = \''.$this->token.'\'';
		if ($key) {
			$sql .= ' AND orders.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		if (isset($status)) {
			$sql .= ' AND orders.`status` = \''.$status.'\'';
		}
		$sql .= " ORDER BY orders.update_time DESC ";
		$sql .= " limit ".$page->firstRow.','.$page->listRows;
		$orders = $model->query($sql);
		
		$unHandledCount = M('b2c_order')->where(array('token'=> $this->token, 'status'=> '1'))->count();
        $this->assign('unhandledCount',$unHandledCount);
		$this->assign('orders', $orders);
		$this->assign('page', $page->show());
		$this->assign('token', $this->token);
		$this->display();
    }


    public function orderFlow() 
    {
        $order_id = $_POST['oid'];
        $order_db = M('b2c_order');

        $order_where = array('order_id'=>$order_id, 'token'=>$this->token, 'branch_id'=>$this->branch_id);
        if (IS_POST)
        {
            $order = $order_db->where($order_where)->find();
            if ($order) 
            {
                $next_step = $_POST['step'];
                $ret = false;
                if ($next_step == 'delivery') 
                {
                    if ($order['status'] == 2 || ($order['payment'] == 'cod' && $order['status'] == 1)) 
                    {
	                    if (!$_POST['logistics_name'] || !$_POST['logistics_no']) {
	                		 $this->error('请完善配送信息');
	                		 exit();
	                	}
                        //根据付款方式（货到付款和第三方支付）判断是否能发货
                        $ret = $order_db->where($order_where)->save(array('status'=>3, 'note'=>$_POST['note']));
                        if ($ret) 
                        {
                            $data = array();
                            $data['type']           = 'express';
                            $data['name']           = $_POST['logistics_name'];
                            $data['fee']            = 0;
                            $data['create_time']    = time();
                            $data['logistics_no']   = $_POST['logistics_no'];
                            $data['order_id']       = $order['order_id'];
                            $data['note']       = $_POST['note'];
                            M('b2c_logistics')->data($data)->add();
                           
                            //$ret = $this->delivery_notify($order);
                            //if($ret == false) {
                            //    $this->success('更新发货状态失败!请稍后重试！');
                            //}
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
                else if ($next_step == 'cancel') 
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
    }
    
    // 如果当前订单是微信支付，公众号是服务号并且已经认证过，需要将发货状态通知微信服务器
    private function delivery_notify($order) {
        if(strcasecmp($order['payment'], 'wxpay') != 0) {
            return true;
        }
        
        $payment = M('b2c_payment')->where(array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'wxpay'))->find();
        $wxpay_config = unserialize($payment['pay_config']);
        $wxuser = M('wxuser')->where(array('status'=>1, 'token'=>$this->token))->find();
        $trade = M('b2c_wxtrade')->where(array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'order_sn'=>$order['sn']))->find();
        if(!empty($wxpay_config) && !empty($trade) && $wxuser['type'] == 1 && $wxuser['is_authed'] == 1) {
            require_once(COMMON_PATH.'/WeixinAPI.php');
            $weixin = new WeixinAPI($wxuser['appid'], $wxuser['appsecret']);
            $access_token = $weixin->getAccessToken();
            
            import("@.ORG.LzWxPayHelper");
            $lzWxPayHelper = new LzWxPayHelper($wxpay_config);
            $ret = $lzWxPayHelper->delivernotify($access_token, $trade);
            return $ret;
        }else{
            Log::record('wxpay delivery type:'.$wxuser['type'].' auth:'.$wxuser['is_authed'].' config:'.print_r($wxpay_config, true), Log::INFO);
            Log::save();
        }
        
        return true;
    }

    public function delivery()
    {
        $order_id = $_GET['oid'];

        $order_db = M('b2c_order');
        $order_where = array('order_id'=>$order_id, 'token'=>$this->token, 'branch_id'=>$this->branch_id);
        $order = $order_db->where($order_where)->find();
        $order_sn = $order['sn'];
        if ($order) 
        {
            $order_logistics_db = M('b2c_logistics');
            $logistics  = $order_logistics_db->where(array('order_id'=>$order_id, 'token' => $this->token))->find();
            switch($order['payment']) {
                case 'alipay':
                    $order_trade_db = M('b2c_trade');
                    $trade      = $order_trade_db->where(array('order_sn'=>$order_sn, 'token' => $this->token))->find();
                    $this->assign('trade',$trade);
                    break;
                case 'wxpay':
                    $wxtrade = M('b2c_wxtrade')->where(array('order_sn'=>$order_sn, 'token' => $this->token))->find();
                    $this->assign('wxtrade',$wxtrade);
                    break;
                case 'cftpay':
                    $cfttrade = M('b2c_cfttrade')->where(array('order_sn'=>$order_sn, 'token' => $this->token))->find();
                    $this->assign('cfttrade',$cfttrade);
                    break;
            }
            
            $this->assign('order',$order);
            $this->assign('logistics',$logistics);
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $items = $Model->query("select i.product_id, i.size_name , i.color_name, i.count, p.`name`, p.logo_url, i.price from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order_id." and i.token='$this->token'");
            $this->assign('products',$items);
        } 
        else
        {
            echo '订单不存在';
        }

        $this->display();
    }


    public function orderInfo()
    {
        $order_id   = $_GET['oid'];
        $order_db   = M('b2c_order');
        $order      = $order_db->where(array('order_id'=>$order_id, 'token' => $this->token))->find();
        if ($order == null) 
        {
            $this->error('订单不存在', U(MODULE_NAME.'/orders',array('bid'=>$this->branch_id)));
        }
        $pname = M('reguser')->field(C('DB_PREFIX').'reguser.truename as pname')->join(' inner join '.C('DB_PREFIX').'b2c_order on '.C('DB_PREFIX').'reguser.id = fxs_id')->where(array(C('DB_PREFIX').'b2c_order.order_id'=>$order_id))->find();
        if ($pname) {
	        $order = array_merge($order, $pname);
        }
        
        $this->assign('order',$order);

        $order_sn = $order['sn'];
        switch($order['payment']) {
            case 'alipay':
                $trade      = M('b2c_trade')->where(array('order_sn'=>$order_sn, 'token' => $this->token))->find();
                $this->assign('trade',$trade);
                break;
            case 'wxpay':
                $wxtrade = M('b2c_wxtrade')->where(array('order_sn'=>$order_sn, 'token' => $this->token))->find();
                $this->assign('wxtrade',$wxtrade);
                break;
            case 'cftpay':
                $cfttrade = M('b2c_cfttrade')->where(array('order_sn'=>$order_sn, 'token' => $this->token))->find();
                $this->assign('cfttrade',$cfttrade);
                break;
        }

        $order_logistics_db = M('b2c_logistics');
        $logistics  = $order_logistics_db->where(array('order_id'=>$order_id, 'token' => $this->token))->find();
        $this->assign('logistics',$logistics);

        $Model = new Model();
        $items = $Model->query("select i.product_id, i.count, p.`name`, p.logo_url, i.price , i.size_name, i.color_name from tp_b2c_order_item as i LEFT JOIN tp_b2c_product as p on i.product_id = p.product_id where i.order_id =".$order_id." and i.token='$this->token' and p.branch_id='$this->branch_id'");
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
        $this->assign('token', $this->token);
        $this->display();
    }

    public function deleteOrder()
    {
        $order_db  = M('b2c_order');
        $order_where = array('order_id'=>intval($_POST['oid']), 'token' => $this->token);
        $order  = $order_db->where($order_where)->find();
        if ($order) 
        {
            //删除订单
            $ret = $order_db->where($order_where)->save(array('status' => 5));

            if ($ret) {
                $this->ajaxReturn("订单更新成功", "OK", 1);
            } else  {
                $this->ajaxReturn("订单取消失败", "ERROR", 0);
            }
        } 
        else
        {
            $this->ajaxReturn("订单取消失败", "ERROR", 0);
        }

       
    }

    //配置商城基本信息
    public function setShopInfo() 
    {
        $where['token']     = $this->token;
        $where['branch_id'] = $this->branch_id;
        $where['status']    = 1;

        $shop_db    = M('b2c_shop');
        $shop       = $shop_db->where($where)->find();

        if (IS_POST) 
        {
            if ($shop == null) 
            {
                // 添加商城回复 
                $data['token']      = $this->token;
                $data['branch_id']  = $this->branch_id;
                $data['keyword']    = isset($_POST['keyword']) ? $_POST['keyword'] : '商城';
                $data['url'] = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Shop&a=index&token='.$this->token;

                $data['logo_url']   = $_POST['logo_url'];  //图片地址
                $data['desc']       = $_POST['desc'];
                
                $data['create_time'] = time();
                $data['update_time'] = $data['create_time'];
                $data['status']      = 1;

                $shop_id = $shop_db->add($data);

                if ($shop_id) 
                {
                    //$this->savePayments();

                    $kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = $this->token;
                    $kwd_data['type'] = 1;
                    $kwd_data['module'] = 'portal';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['pid'] = $shop_id;
                    $keywords = explode(' ',  $data['keyword']);
                    foreach($keywords as $vo) 
                    {
                        if (!empty($vo)) 
                        {
                            $kwd_data['keyword'] = $vo;    
                            $kwds_db->add($kwd_data);
                        }
                    }
                    $this->success('操作成功',U(MODULE_NAME.'/setShopInfo',array('bid'=>$this->branch_id)));
                }
                else 
                {
                    $this->error('添加失败',U(MODULE_NAME.'/setShopInfo',array('bid'=>$this->branch_id)));
                }
            }
            else 
            {

                $data['logo_url']   = $_POST['logo_url'];  //图片地址
                $data['desc']       = $_POST['desc'];
                $data['keyword']    = $_POST['keyword'];
                
                $data['update_time'] = time();
                $ret = $shop_db->where($where)->save($data);

                if ($ret) 
                {
                    //$this->savePayments();
                    $kwds_db = M('keyword');
                    $kwds_db->where(array('pid' => $shop['shop_id'], 'token'=> $this->token, 'function'=>$this->function, 'module'=>'portal'))->delete();
  
                    $kwd_data['uid']        = session('uid');
                    $kwd_data['token']      = $this->token;
                    $kwd_data['module']     = 'portal';
                    $kwd_data['function']   = $this->function;
                    $kwd_data['type']       = 1;
                    $kwd_data['pid']        = $shop['shop_id'];
                    $keywords = explode(' ',  $data['keyword']);
                    foreach($keywords as $vo) 
                    {
                        if (!empty($vo)) 
                        {
                            $kwd_data['keyword'] = $vo;    
                            $kwds_db->add($kwd_data);
                        }
                        
                    }
                    $this->success('操作成功',U(MODULE_NAME.'/setShopInfo',array('bid'=>$this->branch_id)));
                } else {
                    $this->error('保存失败',U(MODULE_NAME.'/setShopInfo',array('bid'=>$this->branch_id)));
                }
            }
        } 
        else 
        {
            

            $this->assign('set',$shop);
            $this->display();
        }
    }

    private function savePayments()
    {
        $cod_pay_enabled = $this->_post('enable_cod','intval',0);
        $this->saveCodPayment($cod_pay_enabled);
        
        $wx_pay_enabled = $this->_post('enable_wxpay','intval',0);
        $this->saveWxPayment($wx_pay_enabled);
        
        $cft_pay_enabled = $this->_post('enable_cftpay','intval',0);
        $this->saveCftPayment($cft_pay_enabled);
        
        $ali_pay_enabled = $this->_post('enable_alipay','intval',0);
        $this->saveAliPayment($ali_pay_enabled);
        
        
        //unionpay
		$union_pay_enabled=$this->_post('enable_unionpay');
		$this->saveUnionPayment($union_pay_enabled);
		
		//wingpay
		$wing_pay_enabled=$this->_post('enable_wingpay');
		$this->saveWingPayment($wing_pay_enabled);
		
    }

    private function saveCodPayment($is_enabled = 0) {
        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = '';
        $payment_data['pay_order'] = 0;
        
        $payment_db = M('b2c_payment');
        $where = array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'cod');
        $payment = $payment_db->where($where)->find();
        if ($payment) 
        {
            $payment_db->where($where)->save($payment_data);
        }else{
            $payment_data['token']     = $this->token;
            $payment_data['branch_id'] = $this->branch_id;
            $payment_data['pay_code']  = 'cod';
            $payment_data['pay_name']  = '货到付款';
            $payment_data['pay_fee']   = 0;
            $payment_data['is_cod']    = 1;
            $payment_data['is_online'] = 0;
            $payment_db->data($payment_data)->add();
        }
    }
    
	private function saveWxPayment($is_enabled = 0)
    {
    	if ($is_enabled == 1 && !($_POST['wxpay_name'] && $_POST['wxpay_appId'] && $_POST['wxpay_appSecret'] && $_POST['wxpay_paySignKey'] && $_POST['wxpay_partnerId'] && $_POST['wxpay_partnerKey'])) {
    		$this->error('请完善微信支付配置信息',U(MODULE_NAME.'/payconf',array('bid'=>$this->branch_id)));
    	}
    	
        $payment_config = array();
        $payment_config['name']          = trim($_POST['wxpay_name']);
        $payment_config['APPID']          = trim($_POST['wxpay_appId']);
        $payment_config['APPSERCERT']      = trim($_POST['wxpay_appSecret']);
        $payment_config['APPKEY']     = trim($_POST['wxpay_paySignKey']);
        $payment_config['PARTNERID']      = trim($_POST['wxpay_partnerId']);
        $payment_config['PARTNERKEY']     = trim($_POST['wxpay_partnerKey']);

        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = serialize($payment_config);
        $payment_data['pay_order'] = 1;

        $payment_db = M('b2c_payment');
        $where = array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'wxpay');
        $payment = $payment_db->where($where)->find();
        if ($payment) 
        {
            $payment_db->where($where)->save($payment_data);
        }
        else
        {
            $payment_data['token']          = $this->token;
            $payment_data['branch_id']      = $this->branch_id;
            $payment_data['pay_code']       = 'wxpay';
            $payment_data['pay_name']       = '微信支付';
            $payment_data['pay_fee']        = 0;
            $payment_data['is_cod']         = 0;
            $payment_data['is_online']      = 1;
            $payment_db->data($payment_data)->add();
        }
    }
    
	private function saveCftPayment($is_enabled = 0) {
    	
    	if ($is_enabled == 1 && !($_POST['cftpay_name'] && $_POST['cftpay_partnerId'] && $_POST['cftpay_partnerKey'])) {
    		$this->error('请完善财付通配置信息',U(MODULE_NAME.'/payconf',array('bid'=>$this->branch_id)));
    	}
        
        $payment_config = array();
        $payment_config['name']     = trim($_POST['cftpay_name']);
        $payment_config['partnerId']      = trim($_POST['cftpay_partnerId']);
        $payment_config['partnerKey']     = trim($_POST['cftpay_partnerKey']);

        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = serialize($payment_config);
        $payment_data['pay_order'] = 2;

        $payment_db = M('b2c_payment');
        $where = array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'cftpay');
        $payment = $payment_db->where($where)->find();
        if ($payment) 
        {
            $payment_db->where($where)->save($payment_data);
        }
        else
        {
            $payment_data['token']          = $this->token;
            $payment_data['branch_id']      = $this->branch_id;
            $payment_data['pay_code']       = 'cftpay';
            $payment_data['pay_name']       = '财付通';
            $payment_data['pay_fee']        = 0;
            $payment_data['is_cod']         = 0;
            $payment_data['is_online']      = 1;
            $payment_db->data($payment_data)->add();
        }
    }
    
    private function saveAliPayment($is_enabled = 0)
    {
    	if ($is_enabled == 1 && !($_POST['pay_account'] && $_POST['alipay_pid'] && $_POST['alipay_key'])) {
    		$this->error('请完善支付宝配置信息',U(MODULE_NAME.'/payconf',array('bid'=>$this->branch_id)));
    	}
    	
        $payment_config = array();
        $payment_config['pay_account']  = trim($_POST['pay_account']);
        $payment_config['alipay_pid']   = trim($_POST['alipay_pid']);
        $payment_config['alipay_key']   = trim($_POST['alipay_key']);

        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = serialize($payment_config);
        $payment_data['pay_order'] = 3;
        
        $payment_db = M('b2c_payment');
        $where = array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'alipay');
        $payment = $payment_db->where($where)->find();
        if ($payment) 
        {
            $payment_db->where($where)->save($payment_data);
        }
        else
        {
            $payment_data['token']          = $this->token;
            $payment_data['branch_id']      = $this->branch_id;
            $payment_data['pay_code']       = 'alipay';
            $payment_data['pay_name']       = '支付宝';
            $payment_data['pay_fee']        = 0;
            $payment_data['is_cod']         = 0;
            $payment_data['is_online']      = 1;
            $payment_db->data($payment_data)->add();
        }
    }
    
	//union_pay
     private function saveUnionPayment($is_enabled = 0) {
        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = '';
        $payment_data['pay_order'] = 4;
        
        $payment_db = M('b2c_payment');
        $where = array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'unionpay');
        $payment = $payment_db->where($where)->find();
        if ($payment) 
        {
            $payment_db->where($where)->save($payment_data);
        }else{
            $payment_data['token']     = $this->token;
            $payment_data['branch_id'] = $this->branch_id;
            $payment_data['pay_code']  = 'unionpay';
            $payment_data['pay_name']  = '银联支付';
            $payment_data['pay_fee']   = 0;
            $payment_data['is_cod']    = 1;
            $payment_data['is_online'] = 0;
            $payment_db->data($payment_data)->add();
        }
    }

    /**
     * wings payment
     * @param $is_enabled | 1.start 0.stop 
     */
    function saveWingPayment($is_enabled = 0){
    	if ($is_enabled == 1 && !($_POST['wingpay_mchid'] && $_POST['wingpay_key'])) {
    		$this->error('请完善翼支付配置信息',U(MODULE_NAME.'/payconf',array('bid'=>$this->branch_id)));
    	}
    	
        $payment_config = array();
        $payment_config['mch_id']          = trim($_POST['wingpay_mchid']);
        $payment_config['key']          = trim($_POST['wingpay_key']);

        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = serialize($payment_config);
        $payment_data['pay_order'] = 5;

        $payment_db = M('b2c_payment');
        $where = array('token'=>$this->token, 'branch_id'=>$this->branch_id, 'pay_code'=>'wingpay');
        $payment = $payment_db->where($where)->find();
        if ($payment) {
            $payment_db->where($where)->save($payment_data);
        }else {
            $payment_data['token']          = $this->token;
            $payment_data['branch_id']      = $this->branch_id;
            $payment_data['pay_code']       = 'wingpay';
            $payment_data['pay_name']       = '翼支付';
            $payment_data['pay_fee']        = 0;
            $payment_data['is_cod']         = 0;
            $payment_data['is_online']      = 1;
            $payment_db->data($payment_data)->add();
        }
    }
    

    //配置商城模板
    public function setTemplate() 
    {
        $where['token']     = $this->token;
        $where['branch_id'] = $this->branch_id;
        $template_db    = M('b2c_display');
        $template       = $template_db->where($where)->find();

        if (IS_POST) 
        {
            $data['tmpl_name']       = $_POST['tmpl_name'];  //文章摘要
            $data['bg_pic_url'] = $_POST['bg_pic_url'];  //图片地址
            if ($template == null) 
            {
                // 添加商城回复 
                $data['token']      = $this->token;
                $data['branch_id']  = $this->branch_id;
                
                $data['create_time'] = time();
                $data['update_time'] = $data['create_time'];

                $template_id = $template_db->add($data);

                if ($template_id) 
                {
                    $this->success('操作成功',U(MODULE_NAME.'/setTemplate',array('bid'=>$this->branch_id)));
                } 
                else 
                {
                    $this->error('保存失败',U(MODULE_NAME.'/setTemplate',array('bid'=>$this->branch_id)));
                }
            }
            else
            {
                $data['update_time'] = time();
                $ret = $template_db->where($where)->save($data);
                if ($ret) 
                {
                    $this->success('操作成功',U(MODULE_NAME.'/setTemplate',array('bid'=>$this->branch_id)));
                } 
                else 
                {
                    $this->error('保存失败',U(MODULE_NAME.'/setTemplate',array('bid'=>$this->branch_id)));
                }
            }
        }
        else
        {
            $tmplUsage = M('user_func_group')->where(array('user_id'=>session('uid'), 'status'=>1, 'group_id'=>C('SHOP_TEMPLATE_FG_ID')))->find();
            if($tmplUsage && $tmplUsage['expire_time'] > time() ) {
                $this->assign('canSelectTemplate', 1);
            }else {
                $this->assign('canSelectTemplate', 0);
            }
            $this->assign('template',$template);
            $this->display();
        }
    }
    
    public function payconf(){
        if(IS_POST) {
            $this->savePayments();
            $this->success('保存成功！');
        }else {
            $payments = M('b2c_payment')->where(array('token'=>$this->token,'branch_id'=>$this->branch_id))->select();
            if(count($payments) <= 0) {
                // 默认打开货到付款
                $this->saveCodPayment(1);
                $payments = M('b2c_payment')->where(array('token'=>$this->token,'branch_id'=>$this->branch_id))->select();
            }
            
            foreach ($payments as $key => $p) 
            {
                switch($p['pay_code']) {
					 case 'unionpay':
                        $this->assign('unionpay', $p);
                        break;
                    case 'cod':
                        $this->assign('cod', $p);
                        break;
                    case 'alipay':
                        if (is_string($p['pay_config']))
                        {
                            $store = unserialize($p['pay_config']);
                            $p['pay_account']         = $store['pay_account'];
                            $p['alipay_pid']          = $store['alipay_pid'];
                            $p['alipay_key']          = $store['alipay_key'];
                        }
                        $this->assign('alipay',$p);
                        break;
                    case  'wxpay':
                        if (is_string($p['pay_config']))
                        {
                            $store = unserialize($p['pay_config']);
                            $p['wxpay_name']         = $store['name'];
                            $p['wxpay_appId']         = $store['APPID'];
                            $p['wxpay_appSecret']     = $store['APPSERCERT'];
                            $p['wxpay_paySignKey']    = $store['APPKEY'];
                            $p['wxpay_partnerId']     = $store['PARTNERID'];
                            $p['wxpay_partnerKey']    = $store['PARTNERKEY'];
                        }
                        $this->assign('wxpay',$p);
                        break;
                    case  'cftpay':
                        if (is_string($p['pay_config']))
                        {
                            $store = unserialize($p['pay_config']);
                            $p['cftpay_name']    = $store['name'];
                            $p['cftpay_partnerId']     = $store['partnerId'];
                            $p['cftpay_partnerKey']    = $store['partnerKey'];
                        }
                        $this->assign('cftpay',$p);
                        break;
                   	case  'wingpay':
                        if (is_string($p['pay_config']))
                        {
                            $store = unserialize($p['pay_config']);
                            $p['wingpay_mchid']    = $store['mch_id'];
                            $p['wingpay_key']     = $store['key'];
                        }
                        $this->assign('wingpay',$p);
                        break;
                }
            }
            $this->display();
        }
    }


    public function qr()
    {

        $product_id = $this->_get('gid','intval', 0); 
        $product_db       = M('b2c_product');
        $where = array('product_id' => $product_id, 'token' => $this->token,'branch_id'=>$this->branch_id, 'function' => $this->function);
        $product = $product_db->where($where)->find();

        if ($product != false) 
        {
            if (!empty($product['qrcode_pic_url'])) 
            {
                $this->assign('qrcode_url',$product['qrcode_pic_url']);
            }
            else
            {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
                
                $product_url = 'http://'.C('wx_handler_server').U('Wap/Shop/product', array('id'=>$product_id,'token'=>$this->token));				
                $gen->build($product_url,'product',$this->token);
                $qrcode_pic_url = $gen->getUrl();

                $product_db->where($where)->save(array('qrcode_pic_url'=>$qrcode_pic_url));
				
                $this->assign('qrcode_url',$qrcode_pic_url);
            }
            $this->display();
        }
        else
        {
            $this->error('商品不存在',U(MODULE_NAME.'/products',array('bid'=>$this->branch_id)));
        }
    }

    private function get_shop_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $rand_id = mt_rand(1, 99999);
        return $rand_id+100000;
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
               $this->success('操作成功',U(MODULE_NAME.'/orders'));
            } else  {
               $this->success('操作成功',U(MODULE_NAME.'/orders'));
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
				   $this->success('操作成功',U(MODULE_NAME.'/orders'));
				} else  {
				   $this->success('操作成功',U(MODULE_NAME.'/orders'));
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

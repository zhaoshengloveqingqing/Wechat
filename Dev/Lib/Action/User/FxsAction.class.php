<?php
 header("Content-Type: text/html; charset=utf-8");
class FxsAction extends UserAction
{
    private $pagesize = 0;
	const DEPT = 'department';
	const PARTNER = 'partner';
	const REGUSER = 'reguser';
	const PAYMENT = 'payment';
	const B2C_ORDER = 'b2c_order';
	
	protected $branch_id = null;
    protected function _initialize()
    {
        $this->function = 'shangcheng';
        $this->token = $_SESSION['token']; 

        $this->branch_id = $_GET['bid'];
        Log::write("branch_id:".$this->branch_id);
		$this->pagesize = 20;
        parent::_initialize();
        parent::checkOpenedFunction();
    }

    public function index()
    {
    	if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
        
		$model = new Model(); // 实例化一个model对象 没有对应任何数据表
		//table count
		$sql = 'SELECT COUNT(*) AS num
				FROM '.C('DB_PREFIX') .self::REGUSER.'
				LEFT  JOIN '.C('DB_PREFIX') .self::PAYMENT.' ON '.C('DB_PREFIX') .self::REGUSER.'.id = '.C('DB_PREFIX') .self::PAYMENT.'.`user_id`
				LEFT JOIN '.C('DB_PREFIX') .self::PARTNER.' ON '.C('DB_PREFIX') .self::PARTNER.'.`num` = '.C('DB_PREFIX') .self::REGUSER.'.`num` 
				WHERE '.C('DB_PREFIX') .self::REGUSER.'.status = 1 AND  '.C('DB_PREFIX') .self::REGUSER.'.token = \'' . $this->token. '\'';
		if ($key) {
			$sql .= ' AND truename LIKE \'%'.mysql_real_escape_string($key).'%\' OR mb LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		$user_count = $model->query($sql);
		$count = $user_count ? $user_count[0]['num'] : 0;
		
    	$page = new Page($count, $this->pagesize);
	    if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		
		$sql = 'SELECT '.C('DB_PREFIX') .self::REGUSER.'.`id`,  `truename`, `code`, `mb`, `pay_name`, `pay_account`, 
				  '.C('DB_PREFIX') .self::PARTNER.'.`name` AS pname, '.C('DB_PREFIX') .self::PARTNER.'.num AS pnum
				FROM '.C('DB_PREFIX') .self::REGUSER.'
				LEFT  JOIN '.C('DB_PREFIX') .self::PAYMENT.' ON '.C('DB_PREFIX') .self::REGUSER.'.id = '.C('DB_PREFIX') .self::PAYMENT.'.`user_id`
				LEFT JOIN '.C('DB_PREFIX') .self::PARTNER.' ON '.C('DB_PREFIX') .self::PARTNER.'.`num` = '.C('DB_PREFIX') .self::REGUSER.'.`num` 
				WHERE '.C('DB_PREFIX') .self::REGUSER.'.status = 1  AND  '.C('DB_PREFIX') .self::REGUSER.'.token = \'' . $this->token. '\'';
    	if ($key) {
			$sql .= ' AND truename LIKE \'%'.mysql_real_escape_string($key).'%\' OR mb LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		$sql .= " order by ".C('DB_PREFIX') .self::REGUSER.".`update_time` desc, ".C('DB_PREFIX') .self::REGUSER.".`status`";
		$sql .= " limit ".$page->firstRow.','.$page->listRows;
		$user = $model->query($sql);
		$this->assign('page',$page->show());
		$this->assign('info', $user);
		$this->assign('type', '1');
		$this->display();
    }
    
    function fxs_list(){
    	if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
        
		$model = new Model(); // 实例化一个model对象 没有对应任何数据表
		//table count
		$sql = 'SELECT COUNT(*) AS num
				FROM '.C('DB_PREFIX') .self::REGUSER.'
				LEFT  JOIN '.C('DB_PREFIX') .self::PAYMENT.' ON '.C('DB_PREFIX') .self::REGUSER.'.id = '.C('DB_PREFIX') .self::PAYMENT.'.`user_id`
				LEFT JOIN '.C('DB_PREFIX') .self::PARTNER.' ON '.C('DB_PREFIX') .self::PARTNER.'.`num` = '.C('DB_PREFIX') .self::REGUSER.'.`num` 
				WHERE '.C('DB_PREFIX') .self::REGUSER.'.status in (0,1)   AND   '.C('DB_PREFIX') .self::REGUSER.'.token = \'' . $this->token. '\'';
		if ($key) {
			$sql .= ' AND truename LIKE \'%'.mysql_real_escape_string($key).'%\' OR mb LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		$user_count = $model->query($sql);
		$count = $user_count ? $user_count[0]['num'] : 0;
		
    	$page = new Page($count, $this->pagesize);
	    if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		
		$sql = 'SELECT '.C('DB_PREFIX') .self::REGUSER.'.`id`, '.C('DB_PREFIX') .self::REGUSER.'.`status`, `truename`, `code`, `mb`, `pay_name`, `pay_account`, 
				  '.C('DB_PREFIX') .self::PARTNER.'.`name` AS pname, '.C('DB_PREFIX') .self::PARTNER.'.num AS pnum
				FROM '.C('DB_PREFIX') .self::REGUSER.'
				LEFT  JOIN '.C('DB_PREFIX') .self::PAYMENT.' ON '.C('DB_PREFIX') .self::REGUSER.'.id = '.C('DB_PREFIX') .self::PAYMENT.'.`user_id`
				LEFT JOIN '.C('DB_PREFIX') .self::PARTNER.' ON '.C('DB_PREFIX') .self::PARTNER.'.`num` = '.C('DB_PREFIX') .self::REGUSER.'.`num` 
				WHERE '.C('DB_PREFIX') .self::REGUSER.'.status in (0,1)   AND  '.C('DB_PREFIX') .self::REGUSER.'.token = \'' . $this->token. '\'';
    	if ($key) {
			$sql .= ' AND truename LIKE \'%'.mysql_real_escape_string($key).'%\' OR mb LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		$sql .= " order by ".C('DB_PREFIX') .self::REGUSER.".`status`";
		$sql .= " limit ".$page->firstRow.','.$page->listRows;
		$user = $model->query($sql);
		$this->assign('page',$page->show());
		$this->assign('info', $user);
		$this->assign('type', '0');
		$this->display('index');
    }
    
	public function shop_orders(){
		$fxs_id = $_GET['fxs_id'];
		$reguser = C('DB_PREFIX') .self::REGUSER;
		$b2c_order = C('DB_PREFIX') .self::B2C_ORDER;
		
		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
        $model = new Model(); 
		//table count
		$sql = 'SELECT COUNT(*) as num FROM  `tp_b2c_order` orders
				INNER JOIN `tp_reguser` reguser ON reguser.`id` = `fxs_id`
				LEFT JOIN `tp_partner` partner ON reguser.num = partner.num 
				WHERE reguser.status = 1 AND orders.token = \''.$this->token.'\' AND orders.fxs_id = \''.$fxs_id.'\'';
		if ($key) {
			$sql .= ' AND orders.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		if (isset($_GET['status'])) {
			$sql .= ' AND orders.`status` = \''.$_GET['status'].'\'';
		}
		$order_count = $model->query($sql);
		$count = $order_count ? $order_count[0]['num'] : 0;
    	
		$page = new Page($count, $this->pagesize);
	    if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		
		//order list
		$sql = 'SELECT reguser.truename AS pname, reguser.openid AS ropenid, reguser.id AS fxs_id, partner.*, orders.*
				FROM  `tp_b2c_order` orders
				INNER JOIN `tp_reguser` reguser ON reguser.`id` = `fxs_id`
				LEFT JOIN `tp_partner` partner ON reguser.num = partner.num 
				WHERE reguser.status = 1 AND orders.token = \''.$this->token.'\' AND orders.fxs_id = \''.$fxs_id.'\'';
		if ($key) {
			$sql .= ' AND orders.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		if (isset($_GET['status'])) {
			$sql .= ' AND orders.`status` = \''.$_GET['status'].'\'';
		}
		$sql .= " ORDER BY orders.update_time DESC ";
		$sql .= " limit ".$page->firstRow.','.$page->listRows;
		$orders = $model->query($sql);
		
		$unHandledCount = M('b2c_order')->where(array('token'=> $this->token, 'fxs_id'=> $fxs_id, 'status'=> '1'))->count();
        $this->assign('unhandledCount',$unHandledCount);
		$this->assign('orders', $orders);
		$this->assign('page', $page->show());
		$this->assign('token', $this->token);
		$this->display();
	}
	
	 /**
	 * Desc : 显示微商城分店页面
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	public function shop_index(){
		$fxs_id=$this->_get('fxs_id');
		$shop_db    = M('b2c_shop');
        $list  = null;
        $show  = null;
       
        $count  = $shop_db->where(array('token'=>$this->token, 'status'=>1))->count();
        $Page   = new Page($count,12);
        $show   = $Page->show();
	
        $list   = $shop_db->where(array('token'=>$this->token,'fxs_id'=>$fxs_id, 'status'=>1))->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page',$show);    
        $this->assign('shopes',$list);
		$this->display();
	}
	/**
	 * Desc : 显示分店添加页面以及分店的添加和删除
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	public function branchshop_add(){	
		
		$shop_db    = M('b2c_shop');
		$partner_id=$this->_get('partner_id');
		
		$this->fxs_id=$this->_get('fxs_id');
		$this->id=$this->_get('id');
		if (IS_POST) 
        {
		
			$token=session('token');
			$data['address']    = trim($_POST['address']);
			$data['telephone']  = trim($_POST['telephone']);
			$data['name']       = trim($_POST['name']);
				 
			$now = time();
			
			$data['update_time'] = $now;
			$data['status']      = 1;

			$shop_db    = M('b2c_shop');
			$data['token']       = $token;
			$data['fake_id']     = $this->get_shop_sn();
			
			$data['create_time'] = $now;
			$data['p_id']=$partner_id;
			$data['fxs_id']=$this->fxs_id;
			$ret = $shop_db->add($data);
			if ($ret) 
			{
				$this->success('操作成功',U('User/Fxs/shop_index',array('fxs_id'=>$this->fxs_id)));
			}
		}
		else
        {
           
            $this->display('shop_branch');
            
        }
	}
	public function shop_branch(){
		$shop_db    = M('b2c_shop');
		$partner_id=$this->_get('partner_id');
		$this->fxs_id=$this->_get('fxs_id');
		$this->id=$this->_get('id');
		
		$token=session('token');
		
		if (IS_POST) 
        {
			if ($this->fxs_id!=null&&$this->id!=null) 
			{
				
				$data['address']    = trim($_POST['address']);
				$data['telephone']  = trim($_POST['telephone']);
				$data['name']       = trim($_POST['name']);
					 
				$now = time();
				
				$data['update_time'] = $now;
				$data['status']      = 1;
			
				$shop_db    = M('b2c_shop');
				$data['token']       = $token;
				$data['fake_id']     = $this->get_shop_sn();
				
				$data['create_time'] = $now;
				$data['p_id']=$partner_id;
				$data['fxs_id']=$this->fxs_id;
				$shop_id= $this->_get('id');			
				$ret = $shop_db->where(array('shop_id'=>$shop_id))->save($data);
				if ($ret) 
				{
					$this->success('操作成功',U('Fxs/shop_index',array('fxs_id'=>$this->fxs_id)));
				}
			}                
        }
        else
        {
            $shop_db    = M('b2c_shop');

            $where['token']      = session('token');
            $where['fake_id']    = $this->_get('bid');;
			$info= $shop_db->where(array('fxs_id'=>$this->fxs_id))->select();
		
         
            if ($info != false) 
            {
                $this->assign('info',$info);
            }
            $this->display();
            
        }
		
	}
	/**
	 * Desc : 随机产生分店id
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	private function get_shop_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $rand_id = mt_rand(1, 99999);
        return $rand_id+100000;
    }
	
	public function qr(){
	
        $fxs_id = $this->_get('fxs_id'); 
        $reguser_db = M('reguser');
        $where = array('id' => $fxs_id);
        $reguser = $reguser_db->where($where)->find();
		$openid='oAMAIj2ENtn3Yww2mIs06saBT0fA';
		$token=$this->token;
		import("@.Action.Weixin.WeixinAction");
		
        if ($reguser){
            if (!empty($reguser['qrcode_pic_url'])) {
                $this->assign('qrcode_url',$reguser['qrcode_pic_url']);
                $this->assign('img_link_url',$reguser['pic_url_link']);
            } else {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
                $product_url = 'http://'.C('wx_handler_server').U('Fxs/Shop/index', array('fxs_id'=>$fxs_id, 'token'=>$this->token));
				//exit($product_url);
                //$gen->build($product_url, 'reguser', $this->token,array('comp'=>$_SERVER['DOCUMENT_ROOT'].'/images/10.png'));
				$gen->build($product_url, 'reguser', $this->token,array('comp'=>$reguser['headimgurl']));
                $qrcode_pic_url = $gen->getUrl();
				//exit($product_url);
				$this->assign('qrcode_url',$qrcode_pic_url);
				$this->assign('img_link_url', $qrcode_pic_url);
				$reguser_db->where($where)->save(array('qrcode_pic_url'=>$qrcode_pic_url,'pic_url_link'=>$product_url));
			}			
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/index'));
        }
		$this->display();
    }
	//分店二维码
	public function branch_qr(){
		$this->branch_id=$this->_get('bid');
        $id = $this->_get('id'); 
        $shop_db = M('b2c_shop');
        $where = array('id' => $id);
        $reguser = $shop_db->where($where)->find();
        if ($reguser){
            if (!empty($reguser['qrcode_pic_url'])) {
                $this->assign('qrcode_url',$reguser['qrcode_pic_url']);
            } else {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
                $product_url = 'http://'.C('wx_handler_server').U('Fxs/Shop/index', array('id'=>$id,'bid'=>$this->branch_id,'token'=>$this->token));
				exit($product_url);
                $gen->build($product_url, 'reguser', $this->token);
                $qrcode_pic_url = $gen->getUrl();
                $shop_db->where($where)->save(array('qrcode_pic_url'=>$qrcode_pic_url));
                $this->assign('qrcode_url',$qrcode_pic_url);
            }
            $this->display();
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/partner_index'));
        }
    }
	 /**
	 * Desc :生成产品二维码图片
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	public function product_qr()
    {
        $product_id = $this->_get('gid');
		$partner_id=$this->_get('bid');
		$this->branch_id=$this->_get('bid');
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
                $product_url = 'http://'.C('wx_handler_server').U('Wap/Shop/product', array('id'=>$product_id,'bid'=>$partner_id,'token'=>$this->token));			
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
	/**
	 * Desc :微商城管理页面
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	public function shop_products(){
		$this->token=session('token');
		$this->fxs_id=$this->_get('fxs_id');
		$fxs_id=$this->_get('fxs_id');
		$product_db = M('b2c_product');
        $category_db = M('b2c_category');
        $categories  = $category_db->where(array('token'=>$this->token,'fxs_id'=>$fxs_id, 'status' => 1))->select();
        if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
            if (empty($key))
            {
                $this->error("关键词不能为空");
            }

            $map['token']               = $this->token; 
            $map['fxs_id']           = $fxs_id; 
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
            $where = array('token'=>$this->token,'fxs_id'=>$this->fxs_id, 'status' => 1);
            $category_id = 0;
            if (isset($_GET['cid'])) 
            {
                $category_id = intval($_GET['cid'], 0); 
            }

            $count  = $product_db->where($where)->count();
            $Page   = new Page($count,50);
            $show   = $Page->show();
			
            $sql = "select p.product_id,p.inventory, p.category_id,p.fxs_id,p.`name`,p.market_price, p.shop_price,p.`status`,p.logo_url,p.intro, p.update_time,c.`name`  as category_name "
                        ." from tp_b2c_product as p LEFT JOIN tp_b2c_category as c on p.category_id = c.category_id "
                        ." where p.token='$this->token' and p.status=1 ";
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
	
	
	//修改分销商
	public function save_fxs() {
		$email=$this->_post('email');
		$id=$this->_get('fxs_id');
		$where=array('id'=>$id);
		//session_start();
		if(IS_POST){
		
			$reguser_db = D("reguser"); 
			$data = array(
				'truename'=>$this->_post('truename'),
				'mb'=>$this->_post('mb'),
				'email'=>$email,
				'address'=>$this->_post('address'),
				/*'idnum'=>$this->_post('idnum'),
				'wxnumber'=>$this->_post('wxnumber'),
				'province'=>$this->_post('province'),
				'city'=>$this->_post('city'),
				'area'=>$this->_post('area'),
				
				'publicnumber'=>$this->_post('publicnumber'),
				'license_logo'=>$this->_post('license_logo'),
				'tenpay'=>$this->_post('tenpay'),
				'alipay'=>$this->_post('alipay'),
				*/
				'status'=>'1'
			);
			
			$id=$reguser_db->where($where)->save($data);
			if($id){
				$this->success('修改成功', U('Fxs/index',array('id'=>$id)));
			}else{
				 $this->success('修改失败',U('Fxs/index',array('fxs_id'=>$id)));
			}
		}else
		{
			 $this->success('修改失败',U('Fxs/index',array('fxs_id'=>$id)));
		}
	
	}
	public function add() {
		$fxs_id=$this->_get('fxs_id');
		$db=D('reguser');
		$info=$db->where(array('id'=>$fxs_id))->select();
		
        $this->assign('info',$info);
		$this->display(); 
	}
	/**
	 * Desc :删除分销商
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	public function delfxs()
    {
		$fxs_id=$this->_get('fxs_id');
        $db    = M('reguser');
      
		$where['id']    = $fxs_id;
		//exit($where['shop_id']);    
        $data['update_time'] = time();

        $ret = $db->where($where)->delete();
        if ($ret) 
        {
            $this->success('操作成功',U('Fxs/index',array('fxs_id'=>$fxs_id)));
        }
        else
        {
            $this->success('删除失败',U('Fxs/index',array('fxs_id'=>$fxs_id)));
        }
        
    }
	/**
	 * Desc :删除分店
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	public function delBranch()
    {
		$fxs_id=$this->_get('fxs_id');
		$this->token=session('token');
		$this->branch_id=$this->_get('token');
		$this->shop_id=$this->_get('shop_id');
		
        $shop_db    = M('b2c_shop');
      
		$where['shop_id']    = $this->shop_id;
		//exit($where['shop_id']);
                
        $data['update_time'] = time();
        $data['status']      = 2;

        $ret = $shop_db->where($where)->delete();
        if ($ret) 
        {
            $this->success('操作成功',U('Fxs/shop_index',array('fxs_id'=>$fxs_id)));
        }
        else
        {
            $this->success('删除失败',U('Fxs/shop_index',array('fxs_id'=>$fxs_id)));
        }
        
    }
	 /*
     * 添加产品
     */
    public function shop_add()
    { 
        $token = session('token');
		$this->branch_id=$this->_get('bid');
        $categories = M('b2c_category')->where(array('token' => $token, 'branch_id'=>$this->branch_id,'status'=>array('neq',2)))->select();
		
        $this->assign('categories', $categories);
        $size_set = C('SIZE');
        $color_set = C('COLOR');
        $this->assign('size_set',$size_set);
        $this->assign('color_set',$color_set);
        $this->display('shop_set');
    }
    public function shop_set()
    {

        $token = session('token');
		$this->branch_id=$this->_get('bid');
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
	public function shop_cats()
    {        
        $category_db = M('b2c_category');
		$this->branch_id=$this->_get('bid');
        if(IS_POST)
        {
            $map['token']         = $this->token; 
            $map['branch_id']     = $this->branch_id; 
            $map['status']        = 1; 
            $map['name|desc']     = array('like',"%$key%"); 

            $count  = $category_db->where($map)->count();  
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $category_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            
            $where  = array('token' => $this->token,'branch_id'=>$this->branch_id, 'status' => 1);
            $count  = $category_db->where($where)->count();
 
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $category_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        }

        $this->assign('page',$show);        
        $this->assign('list',$list);

        $this->display();        
    }


    public function shop_catAdd()
    { 
		$this->branch_id=$this->_get('bid');
	
        if(IS_POST)
        {
            $category_db = M('b2c_category');
            $data['token']     = $this->token;
            $data['branch_id'] = $this->branch_id;
            $data['name']      = $_POST['name'];
            $data['desc']      = $_POST['desc'];
            $data['logo_url']  = $_POST['logo_url'];

            $data['status'] = 1;
            $data['create_time'] = time();
            $data['update_time'] = $data['create_time'];
            
		Log::write("cate data:".print_r($data,true));
           
            $category_id = $category_db->add($data);
            if ($category_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/shop_cats',array('bid'=>$this->branch_id)));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/shop_catAdd',array('bid'=>$this->branch_id)));
            }

        }
        else 
        {
            $this->display('shop_catSet');
        }
    }

    public function shop_catDel()
    {
        $cid = $this->_get('cid');
		$this->branch_id=$this->_get('bid');

        if(IS_GET)
        {                              
            $where = array('category_id'=>$cid, 'branch_id'=>$this->branch_id, 'token'=>$this->token, 'status' => 1);

            $category_db = M('b2c_category');

            $category = $category_db->where($where)->find();
            if ($category == null)
            {
                $this->success('操作成功',U(MODULE_NAME.'/shop_cats'));
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
                $this->success('操作成功',U(MODULE_NAME.'/shop_cats',array('bid'=>$this->branch_id)));
            }
            else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/shop_cats',array('bid'=>$this->branch_id)));
            }
        }        
    }


    public function shop_catSet()
    {
		$this->branch_id=$this->_get('bid');
        if(IS_POST)
        { 
            //更新分类信息
            $category_id    = $this->_post('cid');
            $where          = array('category_id'=> $category_id, 'branch_id'=>$this->branch_id, 'token'=>$this->token);

            $category_db    = M('b2c_category');
            $category       = $category_db->where($where)->find();
            if ($category == false)
            {
                //找不到也是修改成功
                Log::write("非法更新分类信息：category_id:".$category_id, Log::INFO);
                $this->success('操作成功',U(MODULE_NAME.'/cats'));
            }

            $data['token']      = $this->token;
            $data['branch_id']  = $this->branch_id;

            $data['name']       = $_POST['name'];
            $data['parent']     = $_POST['parent'];
            $data['type']       = $_POST['type'];
            $data['desc']       = $_POST['desc'];
            $data['logo_url']   = $_POST['logo_url'];

            $data['update_time']= time();

            $ret = $category_db->where($where)->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/shop_cats',array('bid'=>$this->branch_id)));
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
                $this->error("没有相应记录.您现在可以添加.",U(MODULE_NAME.'/shop_catAdd',array('bid'=>$this->branch_id)));
            }
            $this->assign('category', $category);

            $other_categories = M('b2c_category')->where(array('category_id'=>array('neq',$category_id), 'branch_id'=>$this->branch_id, 'token'=>$this->token))->find();
            $this->assign('otherCat', $other_categories);

            $this->display();    
        }
    }
	/*
	*产品添加
	*/
	public function save_product()
    {
	
		$this->token=session('token');
		$this->branch_id=$this->_get('bid');
		
        if(IS_POST)
        { 
            //若product_id存在则是更新产品信息
            $product_id = $this->_post('gid');
			
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
                    $this->success('操作成功',U(MODULE_NAME.'/shop_products',array('bid'=>$this->branch_id)));
                }
            }
            elseif(!$product_id)
            {
			
                $ret = $product_db->add($data);
                if($ret !== false )
                {
                   $this->success('操作成功',U(MODULE_NAME.'/shop_products',array('bid'=>$this->branch_id)));
                }
				 else
				{
					Log::write('添加信息失败 error:'.$product_db->getError(), Log::INFO);
					$this->error('操作失败');
				}
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
	//删除产品
	public function del()
    {
        $product_db = M('b2c_product');

        $token = session('token');
		$this->branch_id=$this->_get('bid');
		
        $product_id = $this->_get('gid');
		
        if(IS_GET)
        {                              
            $where = array('product_id'=>$product_id,'token'=>$this->token, 'branch_id'=>$this->branch_id);
            $product = $product_db->where($where)->find();

            if ($product === null)  
            {
                //找不到也是修改成功
                Log::record("非法更新商品信息：good_id:".$product_id, Log::INFO);
                $this->success('操作成功',U(MODULE_NAME.'/shop_products',array('bid'=>$this->branch_id)));
            }

            $ret = $product_db->where($where)->save(array('status' => 2));
            if($ret == true)
            {
                //删除对应图文消息和关键词
                M('img') -> where(array('id' => $product['img_id'], 'token'=>$token ,'function'=>'shangcheng'))->save(array('status' => 2));
                M('keyword')->where(array('pid' => $product['img_id'], 'token'=>$token ,'function'=>'shangcheng'))->delete();
                $this->success('操作成功',U(MODULE_NAME.'/shop_products',array('bid'=>$this->branch_id)));
            }
            else
            {
                //找不到也是修改成功
                Log::record("更新商品信息失败：good_id:".$product_id.';error:'.$product_db->getError(), Log::INFO);
                $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/shop_products',array('bid'=>$this->branch_id)));
            }
        }        
    }
	
	
	
	//发货
	public function shop_delivery()
    {
        $order_id = $_GET['oid'];
		$this->branch_id=$this->_get('bid');
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
	//配置商城基本信息
    public function shop_setShopInfo() 
    {
		$this->branch_id=$this->_get('bid');
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
                    $this->success('操作成功',U(MODULE_NAME.'/shop_setShopInfo',array('bid'=>$this->branch_id)));
                }
                else 
                {
                    $this->error('添加失败',U(MODULE_NAME.'/shop_setShopInfo',array('bid'=>$this->branch_id)));
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
					
                    $this->success('操作成功',U(MODULE_NAME.'/shop_setShopInfo',array('bid'=>$this->branch_id)));
                } else {
                    $this->error('保存失败',U(MODULE_NAME.'/shop_setShopInfo',array('bid'=>$this->branch_id)));
                }
            }
        } 
        else 
        {
            

            $this->assign('set',$shop);
            $this->display();
        }
    }
//配置支付方式选择
	public function shop_payconf(){
      $this->branch_id=$this->_get('bid');
	 
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
                }
            }
            $this->display();
        }
    }
	private function savePayments()
    {
        $cod_pay_enabled = $this->_post('enable_cod','intval',0);
        $this->saveCodPayment($cod_pay_enabled);
        
        $ali_pay_enabled = $this->_post('enable_alipay','intval',0);
        if ($ali_pay_enabled != 0) 
        {
            $this->saveAliPayment(1);
        }
        else
        {
            $this->saveAliPayment(0);
        }

        $wx_pay_enabled = $this->_post('enable_wxpay','intval',0);
        if ($wx_pay_enabled != 0) 
        {
            $this->saveWxPayment(1);
        }
        else
        {
            $this->saveWxPayment(0);
        }
        
        $cft_pay_enabled = $this->_post('enable_cftpay','intval',0);
        $this->saveCftPayment($cft_pay_enabled);
    }
	private function saveCodPayment($is_enabled = 0) {
	
		$this->branch_id=$this->_get('bid');
		
        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = '';
        $payment_data['pay_order'] = 0;
		$payment_data['branch_id'] = $this->branch_id;
        
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

    private function saveCftPayment($enabled) {
        $is_enabled = $enabled == 0 ? 0 : 1;
        
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
        $payment_config = array();
        $payment_config['pay_account']  = trim($_POST['pay_account']);
        $payment_config['alipay_pid']   = trim($_POST['alipay_pid']);
        $payment_config['alipay_key']   = trim($_POST['alipay_key']);

        $payment_data = array();
        $payment_data['enabled']        = $is_enabled;
        $payment_data['pay_config']     = serialize($payment_config);
        $payment_data['pay_order'] = 5;
        
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

    private function saveWxPayment($is_enabled = 0)
    {
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
        $where = array('token'=>$this->token, 'pay_code'=>'wxpay');
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
	
	//审核分销商
	function fxs_check() {
		$where['id']=$this->_get('fxs_id');
		$reguser_db = D("reguser"); 
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
			$info = $where['id'] ? $reguser_db->where($where)->find() : '';
			if ($info && $info['status'] == '1') {
				$ret = $reguser_db->where($where)->save($data);
				if($ret){			
					$this->success('修改成功',U(MODULE_NAME.'/fxs_list'));
				}else{
					$this->error('修改失败',U(MODULE_NAME.'/fxs_list'));
				}
			}else{
				$ret = $reguser_db->where($where)->save($data);
				if($ret){			
					$this->success('激活成功',U(MODULE_NAME.'/index'));
				}else{
					$this->error('激活失败',U(MODULE_NAME.'/index'));
				}
			}
		}else{
			$info = $reguser_db->where($where)->select();
			$this->assign('info',$info);
			$this->display();
		}
	}
   

}

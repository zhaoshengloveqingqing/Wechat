
<?php
class DiningAction extends UserAction
{

    private $rest_id;
    
    protected function _initialize()
    {
        $this->function = 'canyin';
        $this->token = $_SESSION['token']; 
        
        parent::_initialize();
        parent::checkOpenedFunction();
        
        $this->rest_id = $this->_get('id');
    }

	public function index()
    {        
        $room_db = M('dine_restlist');

        if(IS_POST)
        {

            $map['token']         = $this->token; 
            $map['name']     = array('like',"%$key%"); 
            $map['status'] = 1;
			
            $count  = $room_db->where($map)->count();  
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $room_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('orderNum desc')->select();
        }
        else
        {
            
            $where  = array('token' => $this->token, 'status'=>1);
            $count  = $room_db->where($where)->count();
 
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $room_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->order('orderNum desc')->select();
            
        }

        $this->assign('page',$show);        
        $this->assign('list',$list);

        $this->display();        
    }
	
	public function restAdd()
    { 
        if(IS_POST)
        {
            $room_db = M('dine_restlist');
            $data['token']      = $this->token;
            $data['name']       = $_POST['name'];
            $data['address']    = $_POST['address'];
			$data['telephone']  = $_POST['telephone'];
            $data['createtime'] = time();
			$data['longtitude'] = $_POST['longtitude'];
            $data['latitude']   = $_POST['latitude'];
			$data['orderNum']   = $_POST['orderNum'];
			$data['logo_url']   = $_POST['logo_url'];
			$data['status']     = 1;
			
            $room_id = $room_db->add($data);

            if ($room_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/index'));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/restAdd'));
            }

        }
        else 
        {
            $this->display('restSet');
        }
    }

    public function restDel()
    {
        $id = $this->_get('id');

        if(IS_GET)
        {                              
            $where  = array('id'=>$id,'token'=>$this->token);
            $back=M('dine_restlist')->where($where)->save(array('status'=>2));
            if($back==true)
            {
                $this->success('操作成功',U(MODULE_NAME.'/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U(MODULE_NAME.'/index'));
            }
        }          
    }
	
    public function restSet()
    {
        if(IS_POST)
        { 
            //更新分类信息
            $id    = $this->_get('id','intval', 0);
            $where          = array('id'=> $id);

            $room_db    = M('dine_restlist');
            $data['name']       = $_POST['name'];
            $data['address']    = $_POST['address'];
			$data['telephone']  = $_POST['telephone'];
            $data['createtime'] = time();
			$data['longtitude'] = $_POST['longtitude'];
            $data['latitude']   = $_POST['latitude'];
			$data['orderNum']   = $_POST['orderNum'];
			$data['logo_url']   = $_POST['logo_url'];
			
            $ret = $room_db->where($where)->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/index'));
            }
            else
            {
                Log::write("更新分店失败：id:".$id.'; error:'.$room_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $id = $this->_get('id','intval', 0);
            $rest = M('dine_restlist')->where(array('id'=>$id))->find();
            $this->assign('set', $rest);
            $this->display();    
        }
    }
	
    public function menus() 
    {    

        $product_db = M('dine_menu');
        $category_db = M('dine_category');
        $restname=M('dine_restlist')->where(array('id'=>$this->rest_id))->getField('name');
        $categories  = $category_db->where(array('rest_id'=>$this->rest_id,'status' => array('neq' , '2' )))->select();

        if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
            if (empty($key))
            {
                $this->error("关键词不能为空");
            }

            $map['rest_id']               = $this->rest_id; 
            $map['name']  = array('like',"%$key%"); 
            $map['status']  = array('in',"0,1"); 

            $product_count   = $product_db->where($map)->count(); 
            $Page   = new Page($product_count,50);
            //默认通过$_GET['p'] 获取当前页面
            $show   = $Page->show();
            $list   = $product_db->where($map)->order('orderNum desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            $where = array('rest_id'=>$this->rest_id);
            $category_id = 0;
            if (isset($_GET['cid'])) 
            {
                $category_id = intval($_GET['cid'], 0);
            }

            $count  = $product_db->where($where)->count();
            $Page   = new Page($count,50);
            $show   = $Page->show();

            $sql = "select p.id, p.category_id, p.`name`,p.price,p.orderNum,p.status,p.promt_status, c.`name` as category_name "
                        ." from tp_dine_menu as p LEFT JOIN tp_dine_category as c on p.category_id = c.id "
                        ." where p.rest_id='$this->rest_id' and c.rest_id='$this->rest_id' and p.status <> 2 and c.status <> 2";
            if ($category_id)
            {
                $sql .= " and c.id='$category_id'";
            }
            $sql .= " order by p.category_id, p.orderNum desc";
            $sql .= " limit ".$Page->firstRow.','.$Page->listRows;

            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $list = $Model->query($sql);
        }

        $this->assign('categories',$categories); 
        $this->assign('page',$show); 
        $this->assign('list',$list);
        $this->assign('id', $this->rest_id);
        $this->assign('restname', $restname);
        $this->display();
    }


    

    public function rooms()
    {        
        $room_db = M('dine_room');
        $restname=M('dine_restlist')->where(array('id'=>$this->rest_id))->getField('name');
        if(IS_POST)
        {

            $map['rest_id']         = $this->rest_id; 
            $map['name']     = array('like',"%$key%"); 

            $count  = $room_db->where($map)->count();  
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $room_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            
            $where  = array('rest_id' => $this->rest_id);
            $count  = $room_db->where($where)->count();
 
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $list   = $room_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
            
        }

        $this->assign('page',$show);        
        $this->assign('list',$list);
        $this->assign('id', $this->rest_id);
        $this->assign('restname', $restname);
        $this->display();        
    }
	
	public function roomAdd()
    { 
        if (!$this->rest_id) {
            $this->error('请先设置餐厅信息',U(MODULE_NAME.'/setShopInfo'));
        }
        if(IS_POST)
        {
            $room_db = M('dine_room');
            $data['rest_id'] = $this->rest_id;
            $data['name'] = $_POST['name'];
            $data['size'] = $_POST['size'];
            $data['update_time'] = time();
			
            $room_id = $room_db->add($data);

            if ($room_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/rooms', array('id' => $this->rest_id)));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/roomAdd', array('id' => $this->rest_id)));
            }

        }
        else 
        {
		    $this->assign('id', $this->rest_id);
            $this->display('roomSet');
        }
    }

    public function roomDel()
    {
        $cid = $this->_get('cid','intval',0);

        if(IS_GET)
        {                              
            $where = array('id'=>$cid, 'rest_id'=>$this->rest_id);

            $room_db = M('dine_room');

            $ret = $room_db->where($where)->delete();

            if($ret == 1)
            {
                $this->success('操作成功',U(MODULE_NAME.'/rooms'));
            } else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/rooms'));
            }
        }        
    }
	
	public function import() {
	    if(IS_POST)
        {
            $id = $_POST['id'];
			$oid = $_POST['oid'];
			$where['rest_id'] = $oid;
			$categorys = M('dine_category')->where($where)->order('orderNum desc, update_time desc')->select();
			$num = 0;
            foreach($categorys as $c) {
			    $category['rest_id'] = $id;
				$category['name'] = $c['name'];
				$category['status'] = $c['status'];
				$find = M('dine_category')->where(array("name"=>$c['name'], "rest_id"=>$id))->find();
				if ($find) {
				    continue;
				}
				$category['orderNum'] = $c['orderNum'];
				$category['description'] = $c['description'];
				$category['update_time'] = time();
				
				$cid = M('dine_category')->add($category);
				if (!$cid) {
				     $this->ajaxReturn("菜单类别导入出错", "ERROR", 0);
				}
                $where2['category_id'] = $c['id'];
			    $where2['status'] = array("neq", 0);
                $menus = M('dine_menu')->where($where2)->order('status desc, orderNum desc, update_time desc')->select();
				
                foreach ($menus as $m) {
				    $menu['category_id'] = $cid;
					$menu['name'] = $m['name'];
					$find = M('dine_menu')->where(array("name"=>$m['name'], "rest_id"=>$id))->find();
				    if ($find) {
				        continue;
				    }
					$menu['description'] = $m['description'];
					$menu['price'] = $m['price'];
					$menu['oprice'] = $m['oprice'];
					$menu['imgurl'] = $m['imgurl'];
					$menu['status'] = $m['status'];
					$menu['orderNum'] = $m['orderNum'];
					$menu['rest_id'] = $id;
					$menu['update_time'] = time();
					$ret = M('dine_menu')->add($menu);
				    if (!$ret) {
				         $this->ajaxReturn("菜单导入出错", "ERROR", 0);
				    }
					$num++;
				}
            }
        
			$this->ajaxReturn("菜单导入成功，共导入菜品".$num."个", "OK", 1);
        }
	    
	}
	
    public function roomSet()
    {
        if (!$this->rest_id) {
            $this->error('请先设置餐厅信息',U(MODULE_NAME.'/setShopInfo'));
        }
        if(IS_POST)
        { 
            //更新分类信息
            $room_id    = $this->_post('cid','intval', 0);
            $where          = array('id'=> $room_id, 'rest_id'=>$this->rest_id);

            $room_db    = M('dine_room');
            $data['name']   = $_POST['name'];
            $data['size']   = $_POST['size'];
            $data['update_time'] = time();
            $ret = $room_db->where($where)->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/rooms', array('id' => $this->rest_id)));
            }
            else
            {
                Log::write("更新包厢失败：room_id:".$room_id.'; error:'.$room_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $room_id = $this->_get('cid','intval', 0);
            $room = M('dine_room')->where(array('id'=>$room_id))->find();
            if(empty($room))
            {
                $this->error("没有相应包厢.您现在可以添加.",U(MODULE_NAME.'/roomAdd'));
            }
			$this->assign('id', $this->rest_id);
            $this->assign('room', $room);
            $this->display();    
        }
    }
	
    public function cats()
    {        
        $category_db = M('dine_category');
        $restname=M('dine_restlist')->where(array('id'=>$this->rest_id))->getField('name');
        $where  = array('rest_id' => $this->rest_id);
        $where['status']   = array('in',"1,0"); 
        $count  = $category_db->where($where)->count();
 
        $Page   = new Page($count,20);
        $show   = $Page->show();

        $list   = $category_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('page',$show);        
        $this->assign('list',$list);
        $this->assign('id', $this->rest_id);
        $this->assign('restname', $restname);
        $this->display();        
    }


    public function catAdd()
    { 
        if (!$this->rest_id) {
            $this->error('请先设置餐厅信息',U(MODULE_NAME.'/setShopInfo'));
        }
        if(IS_POST)
        {
            $category_db = M('dine_category');
            $data['rest_id']        = $this->rest_id;
            $data['name']           = $_POST['name'];
            $data['description']    = $_POST['description'];
            $data['orderNum']       = $_POST['orderNum'];
            $data['status']         = 1;
            $data['update_time']    = time();
			
            $category_id = $category_db->add($data);

            if ($category_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/cats', array('id' => $this->rest_id)));
            } 
            else
            {
               $this->error('操作失败',U(MODULE_NAME.'/catAdd', array('id' => $this->rest_id)));
            }

        }
        else 
        {
		    $this->assign('id', $this->rest_id);
            $this->display('catSet');
        }
    }

    public function catDel()
    {
        $cid = $this->_get('cid','intval',0);

        if(IS_GET)
        {                              
            $where = array('id'=> $cid, 'rest_id'=> $this->rest_id);

            $category_db = M('dine_category');

            $category = $category_db->where($where)->find();
            if ($category == null)
            {
                $this->success('操作成功',U(MODULE_NAME.'/cats'));
            }

            $product_db = M('dine_menu');

            $product_count = $product_db->where(array('category_id'=>$cid, 'rest_id' => $this->rest_id, 'status' => array('neq','2')))->count();
            if ($product_count > 0)
            {
                $this->error('本分类下有菜单，请删除菜单后再删除分类',U(MODULE_NAME.'/menus', array('cid' => $cid,'id'=>$this->rest_id)));
            }

            $data['status']     = 2;
            $data['update_time']= time();

            $ret = $category_db->where($where)->save($data);

            if($ret == 1)
            {
                $this->success('操作成功',U(MODULE_NAME.'/cats', array('id' => $this->rest_id)));
            }
            else
            {
                 $this->error('操作失败,请稍后再试',U(MODULE_NAME.'/cats', array('id' => $this->rest_id)));
            }
        }        
    }


    public function catSet()
    {
        if (!$this->rest_id) {
            $this->error('请先设置餐厅信息',U(MODULE_NAME.'/setShopInfo'));
        }
		
        if(IS_POST)
        { 
            //更新分类信息
            $category_id    = $this->_post('cid','intval', 0);
            $where          = array('id'=> $category_id, 'rest_id'=>$this->rest_id, 'status'=>array('neq','2'));

            $category_db    = M('dine_category');
            $category       = $category_db->where($where)->find();
            if ($category == false)
            {
                //找不到也是修改成功
                Log::write("非法更新分类信息：category_id:".$category_id, Log::INFO);
                $this->success('操作成功',U(MODULE_NAME.'/cats', array('id' => $this->rest_id)));
            }

            $data['name']       = $_POST['name'];
            $data['description']= $_POST['description'];
            $data['orderNum']   = $_POST['orderNum'];
            $data['update_time'] = time();
            $ret = $category_db->where($where)->save($data);

            if($ret)
            {
                $this->success('修改成功',U(MODULE_NAME.'/cats', array('id' => $this->rest_id)));
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
            $category = M('dine_category')->where(array('id'=>$category_id, 'status'=>array('neq','2')))->find();
            if(empty($category))
            {
                $this->error("没有相应记录.您现在可以添加.",U(MODULE_NAME.'/catAdd'));
            }
			$this->assign('id', $this->rest_id);
            $this->assign('category', $category);
            $this->display();    
        }
    }

    /*
     * 添加产品
     */
    public function add()
    { 
        if(IS_POST)
        {
            //分类
            $category_id = intval($_POST['cid']); 
            $category = M('dine_category')->where(array('id' => $category_id, 'rest_id' => $this->rest_id))->find();
            
            if(empty($category))
            {
                $this->error("请先添加分类。",U(MODULE_NAME.'/catAdd'));
            }

            $product_db = M('dine_menu');
            
            $data['name']       = $_POST['name'];
            $data['oprice']     = $_POST['oprice'];
            $data['price']      = $_POST['price'];
            $data['description']= $_POST['description'];
            $data['imgurl']     = $_POST['imgurl'];
            //$data['keyword']        = $_POST['keyword'];
            $data['category_id']= $category_id;
            $data['orderNum']   = $_POST['orderNum'];
            $data['rest_id']    = $this->rest_id;
            $tuijian = $_POST['tuijian'];
            if (count($tuijian) > 0) 
            {
                $data['promt_status']         = 1;
            } 
            else 
            {
                $data['promt_status']         = 0;
            }
            $data['status']         = 1;
            $data['update_time']    = time();
            //$data['update_time']    = $data['create_time'];
           
            $product_id = $product_db->add($data);

            if ($product_id) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/menus' , array('id'=>$this->rest_id)));
            } 
            else 
            {
                Log::write('添加信息失败 error:'.$product_db->getError(), Log::INFO);
                $this->error('操作失败',U(MODULE_NAME.'/set'));
            }
        }
        else
        {
            $categories = M('dine_category')->where(array('rest_id' => $this->rest_id, 'status'=>array('neq','2')))->select();
            $this->assign('categories', $categories);
            $this->assign('id', $this->rest_id);
            $this->display('set');
            
        }
		
    }

    /**
     * 商品类别ajax select
     *
     */
    public function ajaxCatOptions(){
        $parent_id = intval($_GET['parentid']);
        $category_db = M('dine_category');

        $cat_Where = array('id'=>$parent_id, 'rest_id'=>$this->rest_id);
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

    /*菜品上架*/
    public function instock()
    {
        $menu_db     = M('dine_menu');
        //更新菜品信息
        $menu_id    = $this->_get('gid', 'intval', 0 );
        $where      = array('id' => $menu_id, 'rest_id' => $this->rest_id, 'status'=>array('neq','2'));
        $menu       = $menu_db->where($where)->find();
        if ($menu != false) 
        {
            $data['status']         = 1;
            $data['update_time']    = time();
            $ret = $menu_db->where($where)->save($data);
            if ($ret) 
            {          
                $this->success('操作成功',U(MODULE_NAME.'/menus', array('id'=>$this->rest_id)));
            }
            else
            {
                Log::write($product_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
    }

    /*菜品下架 Out of stork*/
    public function oos()
    {
        $menu_db    = M('dine_menu');
        //更新菜品信息
        $menu_id    = $this->_get('gid', 'intval', 0 );
        $where      = array('id' => $menu_id, 'rest_id' => $this->rest_id, 'status'=>array('neq','2'));
        $menu       = $menu_db->where($where)->find();

        if ($menu != false) 
        {
            $data['status']         = 0;
            $data['update_time']    = time();
            $ret = $menu_db->where($where)->save($data);
            if ($ret) 
            {          
                $this->success('操作成功',U(MODULE_NAME.'/menus', array('id'=>$this->rest_id)));
            }
            else
            {
                Log::write($product_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }


    }

    public function set()
    {
        
        $product_db     = M('dine_menu');
        $category_db    = M('dine_category');
        
        if(IS_POST)
        { 
            //更新产品信息
            $product_id = $this->_post('gid', 'intval', 0 );
            $where = array('id' => $product_id, 'rest_id' => $this->rest_id, 'status'=>array('neq','2'));
            $product = $product_db->where($where)->find();

            if ($product == false)
            {
                //找不到也是修改成功
                Log::write("非法更新菜单信息：good_id:".$product_id, Log::INFO);
                $this->error("非法更新菜单信息：good_id:".$product_id,U(MODULE_NAME.'/menus', array('id'=>$this->rest_id)));  
            }

            $data['name']           = $_POST['name'];
            $data['oprice']         = $_POST['oprice'];
            $data['price']          = $_POST['price'];
            $data['description']    = $_POST['description'];
            $data['imgurl']         = $_POST['imgurl'];
            //$data['keyword']        = $_POST['keyword'];
            $data['orderNum']       = $_POST['orderNum'];
            $data['category_id']    = $_POST['cid'];

            $tuijian = $_POST['tuijian'];
            if (count($tuijian) > 0) {
                $data['promt_status']         = 1;
            } else {
                $data['promt_status']         = 0;
            }
            $data['update_time']    = time();

            $ret = $product_db->where($where)->save($data);
            
            if ($ret) 
            {          
                $this->success('操作成功',U(MODULE_NAME.'/menus', array('id'=>$this->rest_id)));
            }
            else
            {
                Log::write($product_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
        else
        {
            $product_id = $this->_get('gid','intval', 0); 
            $product = $product_db->where(array('id'=>$product_id))->find();
            if(empty($product))
            {
                $this->error("找不到相应记录.您现在可以添加.",U(MODULE_NAME.'/add'));
            }

            //分类
            $catWhere = array('rest_id' => $this->rest_id, 'status' => 1);
            
            $cats = $category_db->where($catWhere)->select();
            $this->assign('categories',$cats);
            
            $thisCat    = $category_db->where(array('id'=>$product['category_id'], 'status'=>1))->find();

            $this->assign('thisCat',$thisCat);

            $this->assign('id', $this->rest_id);
            $this->assign('product',$product);
            $this->display();    
        
        }
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
        $product_db = M('dine_menu');

        $product_id = $this->_get('gid', 'intval', 0);
        if(IS_GET)
        {                              
            $where = array('id'=>$product_id,'rest_id'=>$this->rest_id);
            
            $ret = $product_db->where($where)->save(array('status' => 2));
            if($ret)
            {
                $this->success('操作成功',U(MODULE_NAME.'/menus', array('id'=>$this->rest_id)));
            } 
            else 
            {
                $this->error("删除菜品失败:".$product_id,U(MODULE_NAME.'/menus', array('id'=>$this->rest_id)));
            }
        }        
    }


    public function orders()
    {
        $order_db = M('dine_order');
        $restname=M('dine_restlist')->where(array('id'=>$this->rest_id))->getField('name');    
        $where = array('rest_id'=> $this->rest_id, 'status'=>array("gt", 1));
        
		$rooms   =  M('dine_room')->where(array('rest_id'=> $this->rest_id))->select();
		$this->assign('rooms',$rooms);
		
        if(IS_POST)
        {
            //搜索订单
            $key = $this->_post('searchkey');
            if(!empty($key))
            {
                $where['username|table|tel'] = array('like',"%$key%");
            }
            $orders_count = $order_db->where($where)->count();

            $Page   = new Page($orders_count,20);
            $show   = $Page->show();
            $orders = $order_db->where($where)->order('submittime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
               
        }
        else 
        {
            if (isset($_GET['status']))
            {
                $where['status'] = intval($_GET['status']);
            }
            $count      = $order_db->where($where)->count();
            $Page       = new Page($count,20);
            $show       = $Page->show();
            $orders     = $order_db->where($where)->order('submittime DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
 
        $unHandledCount = $order_db->where(array('rest_id'=> $this->rest_id, 'status'=> 2))->count();
        $this->assign('unhandledCount',$unHandledCount);

        $this->assign('orders',$orders);
        $this->assign('id', $this->rest_id);
        $this->assign('page',$show);
        $this->assign('restname',$restname);
        $this->display();
    }

    public function orderInfo()
    {
        $order_id   = intval($_GET['oid']);
        $order_db   = M('dine_order');
        $order      = $order_db->where(array('id'=>$order_id, 'rest_id' => $this->rest_id))->find();
        if ($order == null) 
        {
            $this->error('订单不存在', U(MODULE_NAME.'/orders'));
        }
        $this->assign('order',$order); 
        $menus = json_decode($order['menus'], true); 
        $num = 0;
        $price = 0;
        $menueids = array();
        for($j = 0; $j < count($menus); $j++) {
            $menueids[$j] = $menus[$j]['dishes_id'];
            $num += (int)$menus[$j]['nums'];
            $price += (float)$menus[$j]['price'] * (int)$menus[$j]['nums'];
        }
        $where['rest_id'] = $this->rest_id;
        $where['id'] = array('in',implode(',', $menueids));
        $menuinfo = M('dine_menu')->field('id,description')->where($where)->select();
        $descriptions = array();
        //将menuinfo转换为key->value的数组便于前台提取
        foreach ($menuinfo as $key => $value) {
            $descriptions[$value['id']] = $value['description'];
        }
        $this->assign("descriptions",$descriptions);
        $this->assign("menus", $menus);
        $this->assign('order_amount',$price);
        $this->assign('order_item_count',$num);
        
        $rest = M('dine_rest')->where(array("id"=>$this->rest_id))->find();
        $this->assign("rest", $rest);
        $this->display();
    }

    public function cancelOrder()
    {
        $order_db  = M('dine_order');
        $order_where = array('id'=>intval($_POST['oid']), 'rest_id' => $this->rest_id, 'status'=>2);

        $order  = $order_db->where($order_where)->find();

        $ret = $order_db->where($order_where)->save(array('status' => 4));

        if ($ret) {
            $this->ajaxReturn("订单更新成功", "OK", 1);
        } else  {
            $this->ajaxReturn("服务器繁忙,请稍后再试", "ERROR", 0);
        }
    }
	
	public function modOrder()
    {
        $order_db  = M('dine_order');
        $order_where = array('id'=>intval($_POST['oid']), 'rest_id' => $this->rest_id);
        $ret = $order_db->where($order_where)->save(array('table'=>$_POST['table']));
		echo $ret;
    }
    
    public function payOrder()
    {
        $order_db  = M('dine_order');
        $order_where = array('id'=>intval($_POST['oid']), 'rest_id' => $this->rest_id, 'status'=>2);
        $ret = $order_db->where($order_where)->save(array('status' => 3));
        if ($ret) {
            $this->ajaxReturn("订单更新成功", "OK", 1);
        } else  {
            $this->ajaxReturn("服务器繁忙,请稍后再试", "ERROR", 0);
        }
    }

    //配置商城基本信息
    public function setShopInfo() 
    {
        $where['token']     = $this->token;
        
        $shop_db    = M('dine_rest');
        $shop       = $shop_db->where($where)->find();

        if (IS_POST) 
        {
            if ($shop == null) 
            {
                // 添加回复 
                $data['token']      = $this->token;
                $data['keyword']    = isset($_POST['keyword']) ? $_POST['keyword'] : '点餐';

                $data['name']       = $_POST['name'];  //文章摘要
                $data['logo_url']   = $_POST['logo_url'];  //图片地址
                $data['desc']       = $_POST['desc'];
                $data['telephone']  = $_POST['telephone'];
                $data['address']    = $_POST['address'];
                
                /*$data['create_time'] = time();
                $data['update_time'] = $data['create_time'];
                $data['status']      = 1;*/
                $data['update_time'] = time();
				
                $shop_id = $shop_db->add($data);
                Log::save();
                if ($shop_id) 
                {
                    $kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = $this->token;
                    $kwd_data['type'] = 1;
                    $kwd_data['module'] = 'dining';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['pid'] = $shop_id;
                    $keywords = explode(' ',  $data['keyword']);
                    foreach($keywords as $vo) 
                    {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
                    $this->success('操作成功',U(MODULE_NAME.'/setShopInfo'));
                }
                else 
                {
                    $this->error('添加失败',U(MODULE_NAME.'/setShopInfo'));
                }
            }
            else 
            {

                $data['name']       = $_POST['name'];  //文章摘要
                $data['logo_url']   = $_POST['logo_url'];  //图片地址
                $data['desc']       = $_POST['desc'];
                $data['telephone']  = $_POST['telephone'];
                $data['address']    = $_POST['address'];
                $data['keyword']    = $_POST['keyword'];
                $data['update_time'] = time();
                $ret = $shop_db->where($where)->save($data);
                
                if ($ret) 
                {
                    
                    $kwds_db = M('keyword');
                    $kwds_db->where(array('pid' => $shop['id'], 'token'=> $this->token, 'function'=>$this->function, 'module'=>'dining'))->delete();
  
                    $kwd_data['uid']        = session('uid');
                    $kwd_data['token']      = $this->token;
                    $kwd_data['module']     = 'dining';
                    $kwd_data['function']   = $this->function;
                    $kwd_data['type']       = 1;
                    $kwd_data['pid']        = $shop['id'];
                    $keywords = explode(' ',  $data['keyword']);
                    foreach($keywords as $vo) 
                    {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
                    $this->success('操作成功',U(MODULE_NAME.'/setShopInfo'));
                } else {
                    $this->error('保存失败',U(MODULE_NAME.'/setShopInfo'));
                }
            }
        } 
        else 
        {
            $this->assign('set',$shop);
            $this->display();
        }
    }
}

?>


<?php
class DiningAction extends ManageAction
{

    private $rest_id;

    private $branch_name;
    
    protected function _initialize()
    {
        parent::_initialize();
        $this->rest_id = $_SESSION['manage_dine_branch'];
        if (!empty($this->rest_id)) 
        {
            $sql = "select rl.name, rl.id from  tp_dine_restlist as rl  where rl.status = 1 AND rl.token='$this->token' and rl.id=$this->rest_id;";
            $Model = new Model();
            $rest = $Model->query($sql);

            if ($rest != false) 
            {
                $this->rest_id = $rest[0]['id'];
                $this->assign('branch_name', $rest[0]['name']);
                $this->branch_name = $rest[0]['name'];
            }
            else
            {
                exit;
            }
        }
        else
        {
            $sql = "select rl.name, rl.id from  tp_dine_restlist as rl  where rl.status = 1 AND rl.token='$this->token' LIMIT 1;";
            $Model = new Model();
            $rest = $Model->query($sql);
            if ($rest != false) 
            {
                $this->rest_id = $rest[0]['id'];
                $this->assign('branch_name', $rest[0]['name']);
            }
            else
            {
                exit;
            }
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if(ereg('Mozilla', $user_agent) && ereg('MSIE', $user_agent))
        {
            $this->assign('is_ie', 1);
        }
        else
        {
            $this->assign('is_ie', 0);
        }
        
    }

    public function menuList() 
    {    

        parent::checkAction("Dining-menu");

        $menu_db = M('dine_menu');
        $category_db = M('dine_category');
        $categories  = $category_db->where(array('rest_id'=>$this->rest_id,'status'=>1))->select();

        if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
            if (empty($key))
            {
                $this->error("关键词不能为空");
            }

            $map['rest_id']     = $this->rest_id; 
            $map['name']        = array('like',"%$key%"); 
            $map['status']      = array('neq',"2"); 

            $menu_count   = $menu_db->where($map)->count(); 
            $Page   = new Page($menu_count,50);
            //默认通过$_GET['p'] 获取当前页面
            $show   = $Page->show();
            $menu_list   = $menu_db->where($map)->order('orderNum desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        else
        {
            $category_id = 0;
            if (isset($_GET['cid'])) 
            {
                $category_id = intval($_GET['cid'], 0);
            }

            $count_sql = "select count(*) as menu_count "
                        ." from tp_dine_menu as p LEFT JOIN tp_dine_category as c on p.category_id = c.id "
                        ." where p.rest_id='$this->rest_id' and c.rest_id='$this->rest_id' and p.status <> 2 and c.status= 1";
            if ($category_id)
            {
                $count_sql .= " and c.id='$category_id'";
            }

            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $count = $Model->query($count_sql);

            $Page   = new Page($count[0]['menu_count'],50);
            $show   = $Page->show();

            $sql = "select p.id, p.category_id, p.`name`,p.price,p.orderNum,p.status,p.promt_status, c.`name` as category_name "
                        ." from tp_dine_menu as p LEFT JOIN tp_dine_category as c on p.category_id = c.id "
                        ." where p.rest_id='$this->rest_id' and c.rest_id='$this->rest_id' and p.status <> 2 and c.status= 1";
            if ($category_id)
            {
                $sql .= " and c.id='$category_id'";
            }
            $sql .= " order by p.category_id, p.orderNum desc";
            $sql .= " limit ".$Page->firstRow.','.$Page->listRows;

            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $menu_list = $Model->query($sql);
        }

        $this->assign('categories',$categories); 
        $this->assign('page',$show); 
        $this->assign('menu_list',$menu_list);
        
        $this->display();
    }

    /*
     * 添加产品
     */
    public function menu()
    { 
        if(IS_POST)
        {
            $menu_id = $this->_post('mid', 'intval', -1);

            if ($menu_id == -1) 
            {
                //添加新产品
                $menu_id = $this->addMenu();
                if ($menu_id) 
                {
                    $this->success('操作成功',U(MODULE_NAME.'/menu', array('mid'=> $menu_id)));
                } 
                else 
                {
                    $this->error('操作失败',U(MODULE_NAME.'/menu'));
                }
            }
            else
            {
                //更新或删除商品
                $ret = $this->updateMenu($menu_id);
            }
            Log::save();
            exit;
        }
        else
        {
            $menu_id = $this->_get('mid','intval', -1); 
            $menu_db        = M('dine_menu');
            $dine_menu   = $menu_db->where(array('id'=>$menu_id, 'rest_id' => $this->rest_id,'status' => array('neq','2')))->find();
            if ($dine_menu) 
            {
                $this->assign('dine_menu',$dine_menu);
            }

            //分类
            $categories = M('dine_category')->where(array('rest_id' => $this->rest_id, 'status'=>1))->select();
            $this->assign('categories', $categories);
            $this->display('menu');
        }

        
    }

    public function deleteMenu() 
    {
        parent::checkAction("Dining-menu");
        $menu_id = $this->_get('mid', 'intval', -1);
        $menu_db        = M('dine_menu');

        $where          = array('id'=> $menu_id, 'rest_id'=>$this->rest_id);

        $data['status']     = 2;
        $data['update_time']= time();

        $ret = $menu_db->where($where)->save($data);

        if($ret)
        {
            $this->success('删除成功',U(MODULE_NAME.'/menuList'));
        }
        else
        {
            Log::record("更新菜品信息失败：menu_id:".$menu_id.'; error:'.$menu_db->getError(), Log::INFO);
            $this->error('删除失败');
        }

    }

    private function updateMenu($menu_id)
    {
        $category_id = intval($_POST['cid']); 
        $category = M('dine_category')->where(array('id' => $category_id, 'rest_id' => $this->rest_id,'status'=>1))->find();
            
        if(empty($category))
        {
            $this->error("分类不存在。");
        }

        $menu_db       = M('dine_menu');
        //更新产品信息
        $menu_id = $this->_post('mid', 'intval', 0 );
        $where = array('id' => $menu_id, 'rest_id' => $this->rest_id);
        $menu = $menu_db->where($where)->find();

        if ($menu == false)
        {
            //找不到也是修改成功
            Log::write("非法更新商品信息：good_id:".$menu_id, Log::INFO);
            $this->error("非法更新商品信息",U(MODULE_NAME.'/menu'));  
        } 

        $data['name']           = $_POST['name'];
        $data['oprice']         = $_POST['oprice'];
        $data['price']          = $_POST['price'];
        $data['description']    = $_POST['intro'];
        $data['imgurl']         = $_POST['logo_url'];
        //$data['keyword']      = $_POST['keyword'];
        $data['orderNum']       = $_POST['orderNum'];
        $data['category_id']    = $_POST['cid'];

        $tuijian = $_POST['tuijian'];
        if (count($tuijian) > 0) 
        {
            $data['promt_status']         = 1;
        } 
        else 
        {
            $data['promt_status']         = 0;
        }
            
        $data['update_time']    = time();

        $ret = $menu_db->where($where)->save($data);
            
        if ($ret) 
        {
            $this->success('操作成功',U(MODULE_NAME.'/menuList'));
        }
        else
        {
            Log::write($menu_db->getError(), Log::INFO);
            $this->error('操作失败');
        }
    }

     /*菜品上架*/
    public function instock()
    {
        $menu_db     = M('dine_menu');
        //更新菜品信息
        $menu_id    = $this->_get('mid', 'intval', 0 );
        $where      = array('id' => $menu_id, 'rest_id' => $this->rest_id, 'status'=>array('neq','2'));
        $menu       = $menu_db->where($where)->find();
        if ($menu != false) 
        {
            $data['status']         = 1;
            $data['update_time']    = time();
            $ret = $menu_db->where($where)->save($data);
            if ($ret) 
            {          
                $this->success('操作成功',U(MODULE_NAME.'/menuList'));
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
        $menu_id    = $this->_get('mid', 'intval', 0 );
        $where      = array('id' => $menu_id, 'rest_id' => $this->rest_id, 'status'=>array('neq','2'));
        $menu       = $menu_db->where($where)->find();

        if ($menu != false) 
        {
            $data['status']         = 0;
            $data['update_time']    = time();
            $ret = $menu_db->where($where)->save($data);
            if ($ret) 
            {          
                $this->success('操作成功',U(MODULE_NAME.'/menuList'));
            }
            else
            {
                Log::write($product_db->getError(), Log::INFO);
                $this->error('操作失败');
            }
        }
    }


    private function addMenu()
    {
        $category_id = intval($_POST['cid']); 
        $category = M('dine_category')->where(array('id' => $category_id, 'rest_id' => $this->rest_id,'status'=>1))->find();
            
        if(empty($category))
        {
            $this->error("请先添加分类。",U(MODULE_NAME.'/category'));
        }

        $menu_db = M('dine_menu');
            
        $data['name']           = $_POST['name'];
        $data['oprice']         = $_POST['oprice'];
        $data['price']          = $_POST['price'];
        $data['description']    = $_POST['intro'];
        $data['imgurl']         = $_POST['logo_url'];
        //$data['keyword']      = $_POST['keyword'];
        $data['category_id']    = $category_id;
        $data['orderNum']       = $_POST['orderNum'];
        $data['rest_id']        = $this->rest_id;
        $data['status']         = 0;

        $tuijian = $_POST['tuijian'];
        if (count($tuijian) > 0) {
            $data['promt_status']   = 1;
        } else {
            $data['promt_status']   = 0;
        }
            
        //$data['create_time']    = time();
        $data['update_time']    = time();
           
        $menu_id = $menu_db->add($data);

        if ($menu_id) 
        {
            //$this->success('操作成功',U(MODULE_NAME.'/menu', array('mid'=> $menu_id)));
        } 
        else 
        {
            Log::write('添加信息失败 error:'.$menu_db->getError(), Log::INFO);
            //$this->error('操作失败',U(MODULE_NAME.'/menu'));
        }

        
        return $menu_id;
    }

    public function categoryList()
    {        
        $category_db = M('dine_category');

        $categories = null;
        $where  = array('rest_id' => $this->rest_id, 'status'=>1);

        $count  = $category_db->where($where)->count();
        $Page   = new Page($count,20);
        $show   = $Page->show();
        $this->assign('page',$show);  

        $categories   = $category_db->where($where)->order('orderNum desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('categories',$categories);

        $this->display();        
    }

    public function category()
    {
        parent::checkAction("Dining-category");
        $category_db        = M('dine_category');
        if(IS_POST)
        {
            //添加商品分类
            $category_id = $this->_post('cid', 'intval', -1);
            if ($category_id == -1) 
            {
                $data['rest_id']        = $this->rest_id;
                $data['name']           = $_POST['name'];
                $data['description']    = $_POST['desc'];
                $data['orderNum']       = $_POST['orderNum'];
               
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
                //更新分类信息
                $category_id    = $this->_post('cid','intval', 0);
                $where          = array('id'=> $category_id, 'rest_id'=>$this->rest_id, 'status'=>1);

                $category       = $category_db->where($where)->find();
                if ($category == false)
                {
                    //找不到也是修改成功
                    Log::write("非法更新分类信息：category_id:".$category_id, Log::INFO);
                    $this->success('操作成功',U(MODULE_NAME.'/categoryList'));
                }

                $data['name']           = $_POST['name'];
                $data['description']    = $_POST['desc'];
                $data['orderNum']       = $_POST['orderNum'];

                $ret = $category_db->where($where)->save($data);

                if($ret)
                {
                    $this->success('修改成功',U(MODULE_NAME.'/categoryList'));
                }
                else
                {
                    Log::write("更新分类信息失败：category_id:".$category_id.'; error:'.$category_db->getError(), Log::INFO);
                    $this->error('操作失败');
                }

            }
        }
        else
        {
            $category_id = $this->_get('cid','intval', -1);
            $category = $category_db->where(array('id'=>$category_id, 'rest_id'=>$this->rest_id, 'status'=>1))->find();
            if($category)
            {
                $this->assign('category', $category);
            }
            
            $this->display('category');
        }
    }


    public function deleteCategory() 
    {
        parent::checkAction("Dining-category");
        $category_id = $this->_get('cid', 'intval', -1);

        $menu_db = M('dine_menu');

        $menu_count = $menu_db->where(array('category_id'=>$category_id, 'rest_id' => $this->rest_id, 'status' => array('neq',2)))->count();
        if ($menu_count > 0)
        {
            $this->error('本分类下有商品，请删除商品后再删除分类',U(MODULE_NAME.'/menuList', array('cid' => $category_id)));
        }


        $category_db = M('dine_category');
        $where       = array('id'=> $category_id, 'rest_id'=>$this->rest_id, 'status' => array('neq',2));

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


    public function ajaxNewestOrder()
    {
        parent::checkAction("Dining-order");

        
        $rest_where = array('token'=>$this->token);
        $rest_where['id'] = $this->rest_id;

        $rest = M('dine_restlist')->where($rest_where)->find();
        
        if ($rest == false) 
        {
            $this->ajaxReturn($tipMsgs, "No rest", 0);
        }

        //只判断一天内没别读取过的
        $today = time();
        $last_of_24h = $today - 86400;

        $order_where = array('rest_id'=>$rest['id'],'status'=>2,'readed'=>0, 'submittime'=>array('gt',$last_of_24h));
        $order_db = M('dine_order');
        $new_orders = $order_db->field("tel,username,menus,submittime,dinetime,note,guestnum,table,price,sn")->where($order_where)->order('id ASC')->select();
        $tipMsgs = array();
        if (count($new_orders) > 0) 
        {
            $order_db->where($order_where)->save(array('readed'=>1));
            $orders_html_ary = array();
            $orders_print_ary = array();
            foreach ($new_orders as $key => $order) 
            {
                $menus = json_decode($order['menus'], true);
                $menue_ids = array();
                $order_items = array();
                for($j = 0; $j < count($menus); $j++) 
                {
                    $menue_ids[$j]   = $menus[$j]['dishes_id'];
                    $order_items[$menus[$j]['dishes_id']] = $menus[$j];
                }
                //取出菜品详细
                $where['rest_id'] = $rest['id'];
                $where['id']      = array('in',implode(',', $menue_ids));
                $menuinfo = M('dine_menu')->field('id,description')->where($where)->select();
                //将menuinfo转换为key->value的数组便于前台提取
                foreach ($menuinfo as  $value) 
                {
                    $order_items[$value['id']]['desc'] = $value['description'];
                }
                $order["items_list"] = $order_items;
                $order["href"] = U('Dining/order',array('oid'=>$order["sn"]));
                $order['format_submit_time'] = date("Y-m-d H:i:s",$order['submittime']);
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
        <div class="title">电话：</div><div class="content">#tel#</div>
        <div class="title">桌号：</div><div class="content">#table#</div>
        <div class="title">用餐时间：</div><div class="content">#dinetime#</div>
        <div class="title">用餐人数：</div><div class="content">#guestnum#</div>
        <div class="title">客户备注:</div><div class="content">#note#</div>
        <div>==================================</div>
        <h4>下单菜品</h4>
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
        $order_tmpl = str_replace("#submittime#", $order['format_submit_time'],   $order_tmpl);
        $order_tmpl = str_replace("#username#",   $order['username'],     $order_tmpl);
        $order_tmpl = str_replace("#tel#",        $order['tel'],          $order_tmpl);
        $order_tmpl = str_replace("#table#",      $order['table'],        $order_tmpl);
        $order_tmpl = str_replace("#dinetime#",   $order['dinetime'],     $order_tmpl);
        $order_tmpl = str_replace("#guestnum#",   $order['guestnum'],     $order_tmpl);
        $order_tmpl = str_replace("#note#",       $order['note'],         $order_tmpl);
        $order_tmpl = str_replace("#price#",      $order['price'],        $order_tmpl);

        $order_tmpl = str_replace("#merchant_name#",         $this->branch_name,           $order_tmpl);

        $item_ary_str = '';

        foreach ($order['items_list'] as $key => $item) 
        {
            $item_str = $item_tmpl;
            $item_str = str_replace("#name#",       $item['name'],  $item_str);
            $item_str = str_replace("#nums#",       $item['nums'],  $item_str);
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
                    <tr>
                    <th width="50%" align="center" >下单日期：</th> <td>#submittime#</td>
                    </tr>
                    <tr>
                    <th width="50%" align="center" >订购人：</th> <td> #username#</td>
                    </tr>
                    <tr>
                    <th width="50%" align="center" >电话：</th> <td>#tel#</td>
                    </tr>
                    <tr><th width="50%" align="center" >桌号：</th> <td>#table#</td></tr>
                    <tr><th width="50%" align="center" >预约用餐时间：</th> <td>#dinetime#</td></tr>
                    <tr><th width="50%" align="center" >预约人数：</th> <td>#guestnum#</td></tr>
                    <tr><th width="50%" align="center" >客户备注：</th> <td>#note#</td></tr>
                    <tr><th width="50%" align="center" >总价：</th> <td><span style="color:#f30;font-size:16px;font-weight:bold">#price#</span>元</td></tr>
                    
                  </table>
                </div>
                <div class="col-lg-8">
                  <table class="table">
                    <thead>
                    <tr>
                      <th width="30%" align="center" style="text-align:center">名称</th>
                      <th class="40%" align="center" style="text-align:center">菜品详细</th>
                      <th class="10%" align="center" style="text-align:center">数量</th>
                      <th width="20%" align="center" style="text-align:center">单价（元）</th>
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
        $order_tmpl = str_replace("#username#",   $order['username'],     $order_tmpl);
        $order_tmpl = str_replace("#tel#",        $order['tel'],          $order_tmpl);
        $order_tmpl = str_replace("#table#",      $order['table'],        $order_tmpl);
        $order_tmpl = str_replace("#dinetime#",   $order['dinetime'],     $order_tmpl);
        $order_tmpl = str_replace("#guestnum#",   $order['guestnum'],     $order_tmpl);
        $order_tmpl = str_replace("#note#",       $order['note'],         $order_tmpl);
        $order_tmpl = str_replace("#price#",      $order['price'],        $order_tmpl);

        $item_ary_str = '';

        foreach ($order['items_list'] as $key => $item) 
        {
            $item_str = $item_tmpl;
            $item_str = str_replace("#name#",       $item['name'],  $item_str);
            $item_str = str_replace("#desc#",       $item['desc'],  $item_str);
            $item_str = str_replace("#nums#",       $item['nums'],  $item_str);
            $item_str = str_replace("#price#",      $item['price'], $item_str);
            $item_ary_str .= $item_str;
        }

        $order_tmpl = str_replace("#item_list#",       $item_ary_str,         $order_tmpl);

        return $order_tmpl;
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



    public function orderList()
    {
        parent::checkAction("Dining-order");
        $order_db = M('dine_order');
            
        $where = array('rest_id'=> $this->rest_id, 'status'=>array("gt", 1));

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

        foreach ($orders as $key => $value) 
        {
            if ($orders[$key]['status'] == 2) 
            {
                $orders[$key]['status_text'] ='新订单';
            }
            else if ($orders[$key]['status'] == 3) 
            {
                $orders[$key]['status_text'] ='已付款';
            }
            else if ($orders[$key]['status'] == 4) 
            {
                $orders[$key]['status_text'] ='商户已取消';
            }
            else if ($orders[$key]['status'] == 5) 
            {
                $orders[$key]['status_text'] ='用户已取消';
            }
        }
 
        $unHandledCount = $order_db->where(array('rest_id'=> $this->rest_id, 'status'=> 2))->count();
        $this->assign('unhandledCount',$unHandledCount);

        $this->assign('orders',$orders);

        $this->assign('page',$show);
        $this->display();
    }

    public function notify()
    {
        parent::checkAction("Dining-order");
       
        $this->assign('auto_printable',session('manage_auto_printalbe'));
        $this->display();
    }

    public function order()
    {
        parent::checkAction("Dining-order");
        $order_sn   = intval($_GET['oid']);
        $order_db   = M('dine_order');
        $order      = $order_db->where(array('sn'=>$order_sn, 'rest_id' => $this->rest_id))->find();
        if ($order == null) 
        {
            $this->error('订单不存在', U(MODULE_NAME.'/orders'));
        }

        if ($order['status'] == 2) 
        {
        $order['status_text'] ='新订单';
        }
        else if ($order['status'] == 3) 
        {
            $order['status_text'] ='已付款';
        }
        else if ($order['status'] == 4) 
        {
            $order['status_text'] ='商户已取消';
        }
        else if ($order['status'] == 5) 
        {
            $order['status_text'] ='用户已取消';
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
        //取出菜品详细
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

    public function deleteOrder()
    {
        parent::checkAction("Dining-order");
        $order_db  = M('dine_order');
        $order_where = array('sn'=>intval($_GET['sn']), 'rest_id' => $this->rest_id, 'status'=>2);

        $order  = $order_db->where($order_where)->find();

        $ret = $order_db->where($order_where)->save(array('status' => 4));

        if ($ret) {
            $this->success('订单取消成功',U(MODULE_NAME.'/orderList'));
        } else  {
            $this->error('订单取消失败',U(MODULE_NAME.'/orderList'));
        }
    }
    
    public function payOrder()
    {
        parent::checkAction("Dining-order");
        $order_db  = M('dine_order');
        $order_where = array('oid'=>intval($_GET['sn']), 'rest_id' => $this->rest_id, 'status'=>2);

        $order  = $order_db->where($order_where)->find();

        $ret = $order_db->where($order_where)->save(array('status' => 3));

        if ($ret) {
            $this->success('订单更新成功',U(MODULE_NAME.'/orderList',array('status'=>2)));
        } else  {
            $this->error('订单更新失败',U(MODULE_NAME.'/orderList',array('status'=>2)));
        }
    }

    public function cancel ()
    {
        parent::checkAction("Dining-order");
        $order_sn = intval($_GET['oid']);
        $order_db = M('dine_order');

        $order_where = array('sn'=>$order_sn, 'rest_id' => $this->rest_id, 'status'=>2 );
        $order = $order_db->where($order_where)->find();
        if ($order) 
        {
            $ret = $order_db->where($order_where)->save(array('status'=>4));
            if ($ret) 
            {
                $this->success('订单取消成功',U(MODULE_NAME.'/orderList',array('status'=>2)));
            }
            else 
            {
                $this->success('订单取消失败',U(MODULE_NAME.'/orderList',array('status'=>2)));
            }
        }
    }
}

?>

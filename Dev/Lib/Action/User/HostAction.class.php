<?php
class HostAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'dingdan';
        parent::checkOpenedFunction();
    }

    public function index()
    {    
        //列出所有订单

        $merchant_db    = M('Host');
        $list = null;
        $show  = null;
       
        if(IS_POST)
        {
            $key = $this->_post('searchkey');
            if(empty($key))
            {
                $this->error("关键词不能为空");
            }
            $map['status']  = 1; 
            $map['token']   = $this->token; 
            $map['keyword|title|tel2|tel'] = array('like',"%$key%"); 
            $list = $merchant_db->where($map)->select();  

            $count  = $merchant_db->where($map)->count();
            $Page   = new Page($count,12);
            $show   = $Page->show();           
            
        }
        else
        {
            $count  = $merchant_db->where(array('token'=>$this->token, 'status'=>1))->count();
            $Page   = new Page($count,12);
            $show   = $Page->show();
			if ($count == 0 || $this->token == '5403da7bza4') {
                $this->assign('addmore',1);
		    }
            $list   = $merchant_db->where(array('token'=>$this->token, 'status'=>1))->limit($Page->firstRow.','.$Page->listRows)->select(); 
        }
        $this->assign('page',$show);    
        $this->assign('list',$list);
        $this->display();    
    }
   
    public function set()
    {
        //商家设置
        $merchant_id    = $this->_get('id'); 
        $host_db        = M('Host');
        $where          = array('token'=>$this->token,'id'=>$merchant_id, 'status' => 1);
        $merchant       = $host_db->where($where)->find();
        if (empty($merchant))
        {
            $this->error("没有商家记录.您现在可以添加.",U('Host/add',array('token'=>$this->token)));
        }

        if(IS_POST)
        {
            $data['keyword']    = $this->_post('keyword');
            $data['title']      = $this->_post('title');
            $data['address']    = $this->_post('address');
            $data['tel']        = $this->_post('tel');
            $data['tel2']       = $this->_post('tel2');
            $data['ppicurl']    = $this->_post('ppicurl'); //图文消息
            $data['picurl']     = $this->_post('picurl'); // 商家banner
            $data['name']       = $this->_post('title'); //商家名称
            $data['info']       = $this->_post('info'); //商家说明
            $data['info2']      = $this->_post('info2'); //订单说明
            $data['longtitude'] = $this->_post('longtitude');
            $data['latitude']   = $this->_post('latitude');
            $data['type']       = $this->_post('type','intval',0);
            
            // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] = serialize($default_show_cols); //join("|", $default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));
                    //array_push($text_cols, $text_text[$i].'$$'.$text_placeholder[$i]);
                }
            }
            $data['text_cols'] = serialize($text_cols);//join("###", $text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                    array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                    //array_push($select_cols, $select_text[$i].'$$'.$select_placeholder[$i]);
                }
            }
            $data['select_cols'] = serialize($select_cols);//join("###", $select_cols);
            
            $ret = $host_db->where($where)->save($data);

            if($ret)
            {
                $data1['pid']      = $merchant_id;
                $data1['module']   = 'merchant';
                $data1['token']    = $this->token;
                $data1['function'] = 'dingdan';
                $da['keyword']     = $_POST['keyword'];
                M('Keyword')->where($data1)->save($da);
                $this->success('修改成功',U('Host/index',array('token'=>$this->token)));
            }
            else
            {
                $this->error($host_db->getError());
            }
        }
        else
        {
            if(!empty($merchant['default_col_show'])) {
                $this->assign('default_col_show', unserialize($merchant['default_col_show']));
                //$this->assign('default_col_show', explode("|", $merchant['default_col_show']));
            }
            //$this->assign('text_cols', explode('###', $merchant['text_cols']));
            //$this->assign('select_cols', explode('###', $merchant['select_cols']));
            $text_cols = unserialize($merchant['text_cols']);
            if(empty($text_cols)){
                $this->assign('text_cols', array(array()));
            }else{
                $this->assign('text_cols', $text_cols);
            }
            $select_cols = unserialize($merchant['select_cols']);
            if(empty($select_cols)) {
                $this->assign('select_cols', array(array()));
            }else{
                $this->assign('select_cols', $select_cols);
            }
            
            $this->assign('set',$merchant);
            $this->display();  
        }
    }
    
    public function add()
    { 
        //添加新商家
        if (IS_POST)
        {
            $data['keyword']    = $this->_post('keyword');
            $data['title']      = $this->_post('title');
            $data['address']    = $this->_post('address');
            $data['tel']        = $this->_post('tel');
            $data['tel2']       = $this->_post('tel2');
            $data['ppicurl']    = $this->_post('ppicurl'); //图文消息
            $data['picurl']     = $this->_post('picurl'); // 商家banner
            $data['name']       = $this->_post('title'); //商家名称
            $data['info']       = $this->_post('info'); //商家说明
            $data['info2']      = $this->_post('info2'); //订单说明
            $data['longtitude'] = $this->_post('longtitude');
            $data['latitude']   = $this->_post('latitude');
            $data['type']       = $this->_post('type','intval',0);
            
            $data['token']      = $this->token;
            $data['creattime']  = time();
            
            // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] =serialize($default_show_cols);// join("|", $default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));
                    //array_push($text_cols, $text_text[$i].'$$'.$text_placeholder[$i]);
                    
                }
            }
            $data['text_cols'] = serialize($text_cols);//join("###", $text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                    
                     array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                    //array_push($select_cols, $select_text[$i].'$$'.$select_placeholder[$i]);
                }
            }
            $data['select_cols'] = serialize($select_cols);//join("###", $select_cols);

            $merchant_id = M('host')->data($data)->add();
            if($merchant_id)
            {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'merchant';
                $kwd_data['function'] = 'dingdan';
                $kwd_data['pid'] = $merchant_id;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwds_db->add($kwd_data);
            }
           $this->success('修改成功',U('Host/index'));
        }
        else
        {
            $this->assign('text_cols', array(''));
            $this->assign('select_cols', array(''));
            $this->display('set');
        }
    }

    public function index_del()
    {
        //删除商家
        $id = $this->_get('id');

        if(IS_GET)
        {                              
            $where  = array('id'=>$id,'token'=>$this->token, 'status'=>1);
            $data   = M('Host');
            $check  = $data->where($where)->find();
            if ($check == null)   $this->error('非法操作');

            $back = $data->where($where)->save(array('status'=>2));
            if($back==true)
            {
                M('Keyword')->where(array('pid'=>$id,'token'=>$this->token,'module'=>'merchant', 'function'=> 'dingdan'))->delete();
                $this->success('操作成功',U('Host/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Host/index'));
            }
        }        
    }

    public function lists()
    {
        //订单列表
        $data = M('Host_list_add');
        $hid = $this->_get('id');
        $count      = $data->where(array('token'=>$this->token,'hid'=>$hid, 'status'=>1))->count();
        $Page       = new Page($count,12);
        $show       = $Page->show();

        $li = $data->where(array('token'=>$this->token,'hid'=>$hid, 'status'=>1))->limit($Page->firstRow.','.$Page->listRows)->select(); 

        $this->assign('page',$show);        
        $this->assign('li',$li);
		$this->assign('hid', $hid);
        $this->display();

    }

    public function list_add()
    {
         $data['hid']      = $this->_get('hid'); 
        if (IS_POST)
        {
            if(empty($data['hid'] ))
            {
                $this->error('链接失效。');
                exit;
            }    

            $data['type']    = $this->_post('type');            
            $data['typeinfo']= $this->_post('typeinfo');
            $data['price']   = $this->_post('price');
            $data['yhprice'] = $this->_post('yhprice');
            $data['name']    = $this->_post('name');
            $data['picurl']  = $this->_post('picurl');
            //$data['url']     = $this->_post('url');
            $data['info']    =  $_POST['info'] ;//$this->_post('info');
            $data['inventory']      = $this->_post('inventory');
            $data['open_inventory'] = $this->_post('open_inventory') == false ? 0 : 1;
            $data['token']   = $this->token;
            if ( empty($data['type']) 
                || empty($data['typeinfo'])
                || intval($data['price']) < 0
                || intval($data['yhprice']) < 0
                || empty($data['info'])) 
            {
                $this->error("不能为空.");
                exit;
            }

            M('Host_list_add')->data($data)->add();
            $this->success('操作成功', U('Host/lists',array('id'=>$data['hid'])));
        }
        else
        {
        	$this->assign('hid',  $data['hid']);
            $this->display();
        }
    }

    public function list_edit()
    {
        
        $id = $this->_get('id');
        $list_add = M('Host_list_add')->where(array('id'=>$id,'token'=>$this->token, 'status'=>1))->find();
        if (IS_POST && $list_add)
        {
            $data['type']    = $this->_post('type');
            $data['typeinfo']= $this->_post('typeinfo');
            $data['price']   = $this->_post('price');
            $data['yhprice'] = $this->_post('yhprice');
            $data['name']    = $this->_post('name');
            $data['picurl']  = $this->_post('picurl');
            $data['inventory']      = $this->_post('inventory');
            $data['open_inventory'] = $this->_post('open_inventory') == false ? 0 : 1;
            $data['info']    = $_POST['info'] ;//$this->_post('info');    
            if(empty($data['type']) 
                || empty($data['typeinfo'])
                || intval($data['price']) < 0
                || intval($data['yhprice']) < 0
                || empty($data['info'])) 
            {
                $this->error("各项不能为空，且价格需要是正数。");exit;
            }
            
                 
            $where = array('id'=>$id,'token'=>$this->token,'status'=>1);                 
            M('Host_list_add')->where($where)->save($data);
            $this->success('操作成功',U('Host/lists',array('id'=>$list_add['hid'])));
        }
        else
        {
            $list_add = M('Host_list_add')->where(array('id'=>$id,'token'=>$this->token, 'status'=>1))->find();
            $this->assign('list',$list_add);
            $this->assign('hid',$list_add['hid']);
            $this->display('list_add');
        }
    }

    public function list_del()
    {
        $id = $this->_get('id');
        $data = M('Host_list_add')->where(array('id'=>$id,'token'=>$this->token))->save(array('status'=>2));
        if ($data==false) 
        {
            $this->error('删除失败');
        }
        else
        {
            $this->success('操作成功');
        }
    }



    public function admin()
    {

        $hid        = $this->_get('id');        
        $data       = M('Host_order');
        $count      = $data->where(array('token'=>$this->token,'hid'=>$hid))->count();
        $ok_count   = $data->where(array('token'=>$this->token,'order_status'=>1,'hid'=>$hid))->count();
        $Page       = new Page($count,20);
        $show       = $Page->show();

        $lost_count = $data->where(array('token'=>$this->token,'order_status'=>2,'hid'=>$hid))->count();
        $no_count   = $data->where(array('token'=>$this->token,'order_status'=>3,'hid'=>$hid))->count();
        
		if (isset($_GET['status']))
        {
            $where['order_status'] = intval($_GET['status']);
        } 
		$where['token'] = $this->token;
		$where['hid'] = $hid;
        $li = $data->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('count',$count);
        $this->assign('ok_count',$ok_count);
        $this->assign('no_count',$no_count);
        $this->assign('lost_count',$lost_count);
        $this->assign('page',$show);        
        $this->assign('li',$li);

        if(IS_POST)
        {
            $da['check_in']     = strtotime($this->_post('check_in'));
            $da['order_status'] = $this->_post('status');
            $id = $this->_post('id');
            $hid = $this->_post('hid');
            M('Host_order')->where(array('id'=>$id,'token'=>$this->token))->save($da);
            $this->success('操作成功',U('Host/admin',array('token'=>session('token'),'id'=>$hid)));
        }
        else
        {
            $host = M('Host')->where(array('token'=>$this->token,'id'=>$hid, 'status' => 1))->find();
            
            if(!empty($host['default_col_show'])) 
            {
                $this->assign('default_col_show', unserialize($host['default_col_show']));
            }

            $text_cols = unserialize($host['text_cols']);
            if (!empty($text_cols)) {
                $tmpTextCols = array();
                foreach($text_cols as $text_col ) {
                    if(!empty($text_col[0])) {
                        array_push($tmpTextCols, $text_col[0]);
                    }
                }
                // 单行文本列名列表
                $this->assign('text_cols', $tmpTextCols);
            }
            
            $select_cols = unserialize($host['select_cols']);
            if (!empty($select_cols))
            {
                $tmpSelCols = array();
                foreach($select_cols as $sel_col ) {
                    if(!empty($sel_col[0])) {
                        array_push($tmpSelCols, $sel_col[0]);
                    }
                }
                // 单行文本列名列表
                $this->assign('select_cols', $tmpSelCols);
            }
            
            $this->display();
        }
    }

}


?>

<?php
class HotelAction extends UserAction
{
	private $pagesize = 0;
    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'binguan';
        $this->pagesize = 20;
        parent::checkOpenedFunction();
    }

    public function index()
    {    
        $merchant_db    = M('Hotel');
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

            $list   = $merchant_db->where(array('token'=>$this->token, 'status'=>1))->limit($Page->firstRow.','.$Page->listRows)->select(); 
        }
        $this->assign('page',$show);    
        $this->assign('list',$list);
        $this->display();    
    }
    
    public function set()
    {
        $merchant_id    = $this->_get('id'); 
        $host_db        = M('Hotel');
        $where          = array('token'=>$this->token,'id'=>$merchant_id, 'status' => 1);
        $merchant       = $host_db->where($where)->find();
        if (empty($merchant))
        {
            $this->error("没有商家记录.您现在可以添加.",U('Host/add'));
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
            $data['latitude'] = $this->_post('latitude');
            
            // default columns
            $default_show_cols = array();
            foreach($_POST as $pk => $pv) {
                if(strpos($pk, 'default_show_') == 0 && $pv == 1) {
                    $colname = substr($pk, strlen('default_show_'));
                    array_push($default_show_cols, $colname);
                }
            }
            $data['default_col_show'] = serialize($default_show_cols);
            
            // single text columns
            $text_cols = array();
            $text_text = $_POST['text_text'];
            $text_placeholder = $_POST['text_placeholder'];
            for($i = 0; $i<count($text_text); $i ++) {
                if(!empty($text_text[$i])) {
                    array_push($text_cols, array($text_text[$i], $text_placeholder[$i]));
                }
            }
            $data['text_cols'] = serialize($text_cols);
            
            // select columns
            $select_cols = array();
            $select_text = $_POST['select_text'];
            $select_placeholder = $_POST['select_placeholder'];
            for($i=0; $i<count($select_text); $i ++) {
                if(!empty($select_text[$i])) {
                    array_push($select_cols, array($select_text[$i], $select_placeholder[$i]));
                }
            }
            $data['select_cols'] = serialize($select_cols);
            
            $ret = $host_db->where($where)->save($data);
            if($ret)  {
                $data1['pid']      = $merchant_id;
                $data1['module']   = 'hotel';
                $data1['token']    = $this->token;
                $data1['function'] = $this->function;
                $da['keyword']     = $_POST['keyword'];
                M('Keyword')->where($data1)->save($da);
                $this->success('修改成功',U('Hotel/index'));
            } else   {
            	$msg = $host_db->getError() ;
                $this->error($msg?  $msg : '无修改');
            }
        } else { 
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

            $merchant_id = M('Hotel')->data($data)->add();
            if($merchant_id)
            {
                $kwds_db = M('keyword');
                $kwd_data['uid'] = session('uid');
                $kwd_data['token'] = $this->token;
                $kwd_data['type'] = 1;
                $kwd_data['module'] = 'hotel';
                $kwd_data['function'] = $this->function;
                $kwd_data['pid'] = $merchant_id;
                $kwd_data['keyword'] = $_POST['keyword']; 
                $kwds_db->add($kwd_data);
            }
           $this->success('修改成功',U('Hotel/index'));
        }
        else
        {
            $merchant = M('Hotel')->field('id,token,keyword,title,ppicurl,picurl,info,info2')->order('id desc')->where(array('token'=>$this->token, 'status' => 1))->find();
			if (!empty($merchant)) {
			    if(!empty($merchant['default_col_show'])) {
                    $this->assign('default_col_show', unserialize($merchant['default_col_show']));
                }
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
		    } else {
                $this->assign('text_cols', array(''));
                $this->assign('select_cols', array(''));
			}
            $this->display('set');
        }
    }

  public function index_del()
  {

        $id = $this->_get('id');

        if(IS_GET)
        {                              
            $where  = array('id'=>$id,'token'=>$this->token, 'status'=>1);
            $data   = M('Hotel');
            $check  = $data->where($where)->find();
            if($check==null)   $this->error('非法操作');

            $back=$data->where($where)->save(array('status'=>2));
            if($back==true)
            {
                M('Keyword')->where(array('pid'=>$id,'token'=>$this->token,'module'=>'hotel', 'function'=> $this->function))->delete();
                $this->success('操作成功',U('Hotel/index'));
            }
            else
            {
                $this->error('服务器繁忙,请稍后再试',U('Hotel/index'));
            }
        }        
  }

    public function rooms()
    {
        //订单列表
        $data = M('Hotel_room');
        $hid = $this->_get('id');
        
        $where = array('token'=>$this->token,'hid'=>$hid, 'status'=>1);
        if (IS_POST) {
        	$type = $this->_post('searchkey') ; 
        }else{
        	$type = $this->_get('search') ;
        }
		if (!empty($type)){
	        $where['type'] = array('like', '%'.$type.'%'); 
		}
        $count      = $data->where($where)->count();
        $Page       = new Page($count, $this->pagesize);
        if (!empty($type)) {
        	$Page->parameter = array_map('urlencode', array('search'=>$type, 'id'=>$hid));
			$this->assign('search', $type);
        }
        $show       = $Page->show();
        $li = $data->where($where)->limit($Page->firstRow.','.$Page->listRows)->select(); 
        $this->assign('page',$show);        
        $this->assign('li',$li);
        $this->assign('hid', $hid);
    	$this->display();    

    }

    public function room_set()
    {
        $rid = $this->_get('id');
        if (IS_POST)
        {
             $data['type']    = $this->_post('type');            
             $data['typeinfo']= $this->_post('typeinfo');
             $data['price']   = $this->_post('price');
             $data['yhprice'] = $this->_post('yhprice');
             //$data['name']    = $this->_post('name');
             $data['picurl']  = $this->_post('picurl');
             //$data['url']     = $this->_post('url');
             $data['info']    = $_POST['info'];//$this->_post('info');
             $data['token']   = $this->token;
             $data['inventory'] = $_POST['inventory'];
             $data['open_inventory'] = $this->_post('open_inventory') == false ? 0 : 1;
            // $data['sale_price'] = $this->_post('sale_price');
             if(empty($data['type']) || empty($data['typeinfo']) ||  intval($data['price']) < 0|| intval($data['yhprice']) < 0||  empty($data['info'])  || intval($data['order_price']) < 0 ) 
             {
                    $this->error("各项不能为空，且价格需要是正数。");exit;
             }
             $data['hid'] = $this->_get('hid'); 

             if(empty($rid) && !empty($data['hid'] )) {
                 // add 
                 M('Hotel_room')->data($data)->add();
                 $this->success('操作成功', U('Hotel/rooms',array('id'=>$data['hid'])));
             } else if (!empty($rid)) {
                 // edit
                 $where = array('id'=>$rid,'token'=>$this->token,'status'=>1);                 
                 M('Hotel_room')->where($where)->save($data);
                 $this->success('操作成功', U('Hotel/rooms',array('id'=>$data['hid'])));
             }
             
        } else {
            if (!empty($rid)) {
                $rooms = M('Hotel_room')->where(array('id'=>$rid,'token'=>$this->token, 'status'=>1))->find();
                $this->assign('list',$rooms);
            }
            $this->display();
        }
    }

    public function room_del()
    {
        $id = $this->_get('id');
        $data = M('Hotel_room')->where(array('id'=>$id,'token'=>$this->token))->save(array('status'=>2));
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
        $data       = M('Hotel_order');
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

            $host = M('Hotel')->where(array('token'=>$this->token,'id'=>$hid, 'status' => 1))->find();
            if(!empty($host['default_col_show'])) {
                $this->assign('default_col_show', unserialize($host['default_col_show']));
                //$this->assign('default_col_show', explode("|", $merchant['default_col_show']));
            }

            //$this->assign('text_cols', explode('###', $merchant['text_cols']));
            //$this->assign('select_cols', explode('###', $merchant['select_cols']));
            $text_cols = unserialize($host['text_cols']);
            if(!empty($text_cols)){
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

            if(!empty($select_cols)){
                $tmpSelCols = array();
                foreach($select_cols as $sel_col ) {
                    if(!empty($sel_col[0])) {
                        array_push($tmpSelCols, $sel_col[0]);
                    }
                }

                // 单行文本列名列表
                $this->assign('select_cols', $tmpSelCols);
            }
            $this->assign('hid', $hid);
            $this->display();
    }
    
    function change_status() {
		$da['check_in']     = time();
		$da['order_status'] = $this->_get('status');
		$id = $this->_get('id');
		$hid = $this->_get('hid');
		HotelOrderModel::roll_inventory($id,$da['order_status']);
		M('Hotel_order')->where(array('id'=>$id,'token'=>$this->token))->save($da);
		$this->success('操作成功',U('Hotel/admin',array('token'=>session('token'),'id'=>$hid)));
    }
    
    
    function payconf() {
    	$branch_id =  $this->_get('id');
    	 include_once(LIB_PATH."Action/PayConf.class.php");
    	 $token = session('token');
    	 $payment->db = M('b2c_payment');
    	 $payconf = new PayConf($token, $branch_id, '1', M('b2c_payment'));
    	if(IS_POST) {
			$payconf->savePayments();
            $this->success('保存成功！');
        }else {
            $payments =  $payment->db->where(array('token'=>$token,'branch_id'=>$branch_id))->select();
            if(count($payments) <= 0) {
                // 默认打开货到付款
                $payconf->saveCodPayment(1);
                $payments =  $payment->db->where(array('token'=>$token,'branch_id'=>$branch_id))->select();
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
                        if (is_string($p['pay_config'])) {
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
            $this->assign('hid', $branch_id);
    		$this->display();
    	}
	}
}

?>

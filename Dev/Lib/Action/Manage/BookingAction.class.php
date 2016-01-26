<?php

class BookingAction extends ManageAction
{

    public function merchantList()
    {
        parent::checkAction("Booking-order");
        $merchant_db    = M('Host');
        $merchants = null;
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

            $count  = $merchant_db->where($map)->count();
            $Page   = new Page($count,20);
            $show   = $Page->show();           

            $merchants = $merchant_db->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();     
        }
        else
        {
            $count  = $merchant_db->where(array('token'=>$this->token, 'status'=>1))->count();
            $Page   = new Page($count,20);
            $show   = $Page->show();

            $merchants   = $merchant_db->where(array('token'=>$this->token, 'status'=>1))->limit($Page->firstRow.','.$Page->listRows)->select(); 
        }
        $this->assign('page',$show);    
        $this->assign('merchants',$merchants);
        $this->display();    
    }

    public function manage()
    {
        parent::checkAction("Booking-order");
        if(IS_POST)
        {
            $hid = $this->_post('mid');
            $data['check_in']     = strtotime($this->_post('check_in'));
            $data['order_status'] = $this->_post('status');
            $id = $this->_post('id');
            
            M('Host_order')->where(array('id'=>$id,'token'=>$this->token))->save($data);

            $this->success('操作成功',U('Booking/orderList',array('mid'=>$hid)));
        }
    }

    public function confirm()
    {
        parent::checkAction("Booking-order");
        if(IS_POST)
        {
            $hid = $this->_get('mid');
            $data['order_status'] = 1;
            
            $sn = $this->_post('order_sn');
            M('Host_order')->where(array('sn'=>$sn,'token'=>$this->token))->save($data);

            $this->success('操作成功');
        }
    }

    public function cancel()
    {
        parent::checkAction("Booking-order");
        if(IS_POST)
        {
            $hid = $this->_get('mid');
            $data['order_status'] = 4;
            
            $sn = $this->_post('order_sn');
            M('Host_order')->where(array('sn'=>$sn,'token'=>$this->token))->save($data);

            $this->success('操作成功');
        }
    }

    private function get_order_sn()
    {

        /* 选择一个随机的方案 */

        mt_srand((double) microtime() * 1000000);
        return date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

    }
	   
	public function orderList()
    {

        parent::checkAction("Booking-order");
        $hid        = $this->_get('mid');        
        $order_db   = M('Host_order');
        
        $merchant_db = M('Host');
        $merchant   = $merchant_db->where(array('token'=>$this->token, 'status'=>1, 'id'=>$hid))->find();
        $this->assign('merchant',$merchant);
         
        //所有订单

        $where = array('token'=>$this->token,'hid'=>$hid);
        //默认查看新订单
        if (isset($_GET['status']))
        {
            $where['order_status'] = intval($_GET['status']);
        }
        else
        {
            $where['order_status'] = 3;
        }

        $count      = $order_db    ->where($where)->count();
        $Page       = new Page($count,50);
        $show       = $Page->show();
        $this->assign('page',$show);       
        $orders     = $order_db->where($where)->order('submit_time DESC,book_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach ($orders as $key => $value) 
        {
            if (empty($orders[$key]['sn'])) 
            {
                $orders[$key]['sn'] = $this->get_order_sn();
                $order_db->where(array('id'=>$orders[$key]['id'],'token'=>$this->token,'hid'=>$hid))->save(array('sn'=>$orders[$key]['sn']));
            }

            $orders[$key]['url'] = U('Booking/order',array('oid'=>$orders[$key]['sn'], 'mid'=>$orders[$key]['hid']));
        }

        $this->assign('orders',$orders);

        $this->display();
    }

    public function order()
    {
        parent::checkAction("Booking-order");
        $order_sn       = intval($_GET['oid']);
        $merchant_id    = intval($_GET['mid']);

        $order_db   = M('host_order');
        $order      = $order_db->where(array('sn'=>$order_sn, 'hid' => $merchant_id))->find();

        if ($order == null) 
        {
            $this->error('订单不存在', U(MODULE_NAME.'/orderList',array('mid'=>$merchant_id)));
        }

        if ($order['order_status'] == 1) 
        {
            $order['status_text'] ='已确认';
        }
        else if ($order['order_status'] == 2) 
        {
            $order['status_text'] ='用户已取消';
        }
        else if ($order['order_status'] == 3) 
        {
            $order['status_text'] ='新订单';
        }
        else if ($order['order_status'] == 4) 
        {
            $order['status_text'] ='商户已取消';
        }
        $this->assign('order',$order);

        $host = M('Host')->where(array('token'=>$this->token,'id'=>$merchant_id, 'status' => 1))->find();

        if(!empty($host['title'])) 
        {
            $this->assign('merchant_name', $host['title']);
        }

        switch ($host['type']) 
       {
            case 0:
                $this->assign('type_text','预定');
                break;
            case 1:
                $this->assign('type_text','报名');
                break;
            case 2:
                $this->assign('type_text','预约');
                break;
            
            default:
                $this->assign('type_text','预定');
                break;
        }

        //默认预约项标题
        if(!empty($host['default_col_show'])) 
        {
            $this->assign('default_col_show', unserialize($host['default_col_show']));
        }

        //自定义文本预约项标题
        $text_cols = unserialize($host['text_cols']);
        if(!empty($text_cols))
        {
            $tmpTextCols = array();
            foreach($text_cols as $text_col ) 
            {
                if(!empty($text_col[0])) 
                {
                    array_push($tmpTextCols, $text_col[0]);
                }
            }
                
            // 单行文本列名列表
            $this->assign('text_cols_title', $tmpTextCols);
        }

        //自定义选择类预约项标题
        $select_cols = unserialize($host['select_cols']);
        if (!empty($select_cols))
        {
            $tmpSelCols = array();
            foreach($select_cols as $sel_col ) 
            {
                if(!empty($sel_col[0])) 
                {
                    array_push($tmpSelCols, $sel_col[0]);
                }
            }
            // 单行文本列名列表
            $this->assign('select_cols_title', $tmpSelCols);
        }

        //订单自定义文本类内容
        $textColVals = unserialize($order['text_cols']);
        $this->assign('text_cols_content', $textColVals);

        //订单自定义选择类内容
        $selectColVals = unserialize($order['select_cols']);
        $this->assign('select_cols_content', $selectColVals);
    
        $this->display();
    }
}

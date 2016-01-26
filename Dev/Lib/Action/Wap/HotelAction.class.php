<?php
class HotelAction extends BaseAction{

    public function index(){
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if (!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $token      = $this->_get('token'); 
        $hid         = $this->_get('hid'); 
        $where      = array('token'=>$token,'hid'=>$hid,'status'=>1);             

        $offer_db   = M('Hotel_room');
        $count      = $offer_db->where( $where )->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $this->assign('show',$show);

        $offers     = $offer_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();   
        $this->assign('list',$offers);

        $hostset =  M('Hotel')->where(array('token'=>$token,'id'=>$hid, 'status'=>1))->find();        
        $this->assign('set',$hostset);
		
		$wecha_id = $this->_get('wecha_id');
        if (!empty($wecha_id)) 
        {
            $orders = M('Hotel_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'hid'=>$hid))->select();
            $this->assign('orders',$orders);
        }
		
	
        $count = M('Hotel')->where(array('token'=>$token, 'status'=>1))->count();
        $this->assign('count',$count);
		
        $navigationLink = "http://api.map.baidu.com/marker?location="
                .$hostset['latitude'].','.$hostset['longtitude']
                .'&title='.urlencode($hostset['title'])
                .'&name='.urlencode($hostset['title'])
                .'&content='.urlencode($hostset['address'])
                .'&output=html&src=lingzhtech';
        $this->assign('navigationLink', $navigationLink);
        $this->display();
    }
    
    public function orders()
    {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $merchantId = $this->_get('hid');
        
        $host = M('Hotel')->where(array('token'=>$token,'id'=>$merchantId, 'status' => 1))->find();
        $this->assign('set',$host);
		 
        $wecha_id = $this->_get('wecha_id');
        if (!empty($wecha_id)) 
        {
            $orders = M('Hotel_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'hid'=>$merchantId))->order('id desc')->select();
            $this->assign('orders',$orders);
        }
        $this->display();
    }
    
    public function order_cancel() {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $orderid = $this->_get('id');
        if (!empty($wecha_id)) 
        {
            M('Hotel_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'id'=>$orderid))->setField('order_status',2);
            HotelOrderModel::roll_inventory($orderid,2);
            echo '0';
        }
        else
        {
            echo '1';
        }
       
    }
    
    //首次进入，罗列在线商家
    public function online()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $token      = $this->_get('token'); 
        if(empty($token))  $this->error('非法操作');

        $where      = array('token'=>$token, 'status'=>1); 
        $list = M('Hotel')->where( $where )->select();

        $this->assign('list',$list);
        $this->display();
    }

    public function lists()
    {
       
       $id    = $this->_get('id');
       $token = $this->_get('token');
       $hid = $this->_get('hid');

       $wecha_id = $this->_get('wecha_id');
       if (!empty($wecha_id)) 
       {
           $userinfo = M('Hotel_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token))->find();
           $userinfo['truename'] = $userinfo['book_people'] ;
           $this->assign('userinfo',$userinfo);
       }

       $host = M('Hotel')->where(array('id'=>$hid,'token'=>$token,'status'=>1))->find();
       
       
       $where = array('id'=>$id,'token'=>$token, 'status'=>1);
       $types = M('Hotel_room')->where($where)->find();
	   //dump($types);
       $this->assign('types',$types);
       $save_monery = $types['price'] - $types['yhprice']; 
       
       $this->assign('saves',$save_monery );
       $this->assign('host',$host);
       if(!empty($host['default_col_show'])) {
            $this->assign('default_col_show', unserialize($host['default_col_show']));
            //$this->assign('default_col_show', explode("|", $merchant['default_col_show']));
        }
        //$this->assign('text_cols', explode('###', $merchant['text_cols']));
        //$this->assign('select_cols', explode('###', $merchant['select_cols']));
        $text_cols = unserialize($host['text_cols']);
        $this->assign('text_cols', $text_cols);
        $select_cols = unserialize($host['select_cols']);
        $selectCols = array();
        foreach($select_cols as $selectArray) {
            if(count($selectArray) == 2) {
                 array_push($selectCols, array($selectArray[0], explode('|', $selectArray[1])));
            }
        }

        $this->assign('select_cols', $selectCols);
        
       // sms setting
       $enable_vcode = '0';
       $smsset = M('sms_set')->where(array('token'=> $token))->find();
       if(!empty($smsset)) {
           if(strpos($smsset['function'], 'vchotel') !== FALSE) {
               $enable_vcode = '1';
           }
       }
       
       $this->assign('enable_vcode', $enable_vcode);
       
       //payment
       $payments = M('b2c_payment')->where(array('token'=>$token, 'branch_id'=>$hid, 'enabled' => 1))->order('pay_order asc')->select();
       $this->assign('payments', $payments);
       
       $this->display();

    }
    
    //在线预定
    public function book(){ 
        if($_POST)
        {
            $token = $this->_get('token');
            
            // 检查是否需要短信验证码
            $enable_vcode = 0;
            $smsset = M('sms_set')->where(array('token'=> $token))->find();
            if(!empty($smsset)) {
                if(strpos($smsset['function'], 'vchotel') !== FALSE) {
                    $enable_vcode = 1;
                }
            }
            
            $tel = $this->_post('tel');
            $smsvcode = $this->_post('smsvcode');
            
            if($enable_vcode) {
                
                if(!SmsvcodeAction::verifyVCode($token, $tel, $smsvcode)) {
                    // 验证码不匹配
                    echo'{"success":0,"msg":"请输入正确的短信验证码。"}';
                    exit;
                }
            }
            
            $data['wecha_id']       =  $this->_post('wecha_id');
            $data['book_people']    =  $this->_post('truename'); 
            $data['remarks']        =  $this->_post('remarks');  
            $data['tel']            = $this->_post('tel');  
            $data['book_num']       = $this->_post('nums'); 
            $data['book_time']      = strtotime($this->_post('dateline'));
            $data['book_lefttime']  = strtotime($this->_post('leftdateline'));			
            $id                     = $this->_post('id');
            $data['room_type']      = $this->_post('roomtype'); 
            $data['order_status']   = 3 ;
            $data['hid']            = $this->_get('hid');
            $data['token']          = $this->_get('token');
            $data['submit_time']    = time();
            $data['sn']             = $this->get_order_sn();
            $data['room_id']        = $_POST['room_id'];
           // $data['payment']        = $_POST['payment'];
			
            $price = M('Hotel_room')->where(array('id'=>$id,'token'=>$data['token'],'hid'=>$data['hid'],'status'=>1))->find();

            //判断库存
            if($price['open_inventory'] == 1 && ($price['inventory'] < $data['book_num']) )
            {
                echo '{"success":0,"msg":"库存不足，请联系商家！"}';
                exit;
            }

            $day = round(($data['leftbook_time'] - $data['book_time'])/3600/24);
			if ($day <= 0) {
			     $day = 1;
			}
            $data['price'] = $day* $price['yhprice'] * $data['book_num'];
           // $data['prepayment'] = $day* $price['sale_price'] * $data['book_num'];
            
            $data['text_cols'] = serialize($this->_post('text_cols'));
            $data['select_cols'] = serialize($this->_post('select_cols'));
            
            $order = M('Hotel_order')->data($data)->add();    
            //若下单成功则减库存
            if($order)
            {
                $booking['inventory'] = $price['inventory'] - $data['book_num'];
                M('Hotel_room')->where(array('id'=>$id,'token'=>$data['token']))->data($booking)->save();
            }

            if($order){
                include(LIB_PATH.'Action/SmsSender.class.php');
                $smsSender = new SmsSender();
                $re = $smsSender->notify($this->_get('token'), "dingdan", "您有一个新的微预订：来自".$data['book_people']."，预订商品：".$data['room_type']."，预订时间：".$this->_post('dateline'));
            
                echo'{"success":1,"msg":"恭喜,预定成功。"}';
            }else{
                echo'{"success":0,"msg":"请从新预定。"}';
            }            
            exit;
        }
    }
	
	private function get_order_sn()
    {
        mt_srand((double) microtime() * 1000000);
        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
    
?>
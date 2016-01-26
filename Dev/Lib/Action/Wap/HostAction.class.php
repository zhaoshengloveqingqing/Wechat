<?php
class HostAction extends BaseAction
{
    protected function _initialize()
    {
        parent::_initialize();

        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if (!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
    }

    public function index()
    {
        
        $token      = $this->_get('token'); 
        $hid         = $this->_get('hid'); 
        $where      = array('token'=>$token,'hid'=>$hid,'status'=>1);             

        $offer_db   = M('Host_list_add');
        $count      = $offer_db->where( $where )->count();
        $Page       = new Page($count,5);
        $show       = $Page->show();
        $this->assign('show',$show);

        $offers     = $offer_db->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();   
        $this->assign('list',$offers);

        $hostset =  M('Host')->where(array('token'=>$token,'id'=>$hid, 'status'=>1))->find();
        $this->assign('set',$hostset);
        $this->assign('hid',$hid);

        switch ($hostset['type']) {
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
		
		$wecha_id = $this->_get('wecha_id');
		$orders = M('Host_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token))->select();
        $this->assign('orders',$orders);
	
        
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
        
        $host = M('Host')->where(array('token'=>$token,'id'=>$merchantId, 'status' => 1))->find();
		$this->assign('set',$host);
		
        /*if(!empty($host['default_col_show'])) {
            $this->assign('default_col_show', unserialize($host['default_col_show']));
        }

        // 单行文本字段名
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

        switch ($host['type']) {
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
        }*/
            
        
        $orders = M('Host_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'hid'=>$merchantId))->order('id desc')->select();
        $this->assign('orders',$orders);
		
        $this->display();
    }
    
    public function order_cancel() {
        $wecha_id = $this->_get('wecha_id');
        $token  = $this->_get('token');
        $orderid = $this->_get('id');
        
        M('Host_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token, 'id'=>$orderid))->setField('order_status',2);
        echo '0';
    }
    
    //首次进入，分店商家
    public function online()
    {

        $token      = $this->_get('token'); 
        if(empty($token))  $this->error('非法操作');

        $where      = array('token'=>$token, 'status'=>1); 
        $data = M('Host');
        $count      = $data->where( $where )->count();
        $Page       = new Page($count,7);
        $show       = $Page->show();
        $list = $data->where( $where )->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('list',$list);
        $this->assign('show',$show);
        $this->display();
    }

    public function lists()
    {
       $id    = $this->_get('id');
       $token = $this->_get('token');
       $hid = $this->_get('hid');
       $wecha_id = $this->_get('wecha_id');

       $userinfo = M('Host_order')->where(array('wecha_id'=>$wecha_id,'token'=>$token))->find();
       $userinfo['truename'] = $userinfo['book_people'] ;

       $host = M('Host')->where(array('id'=>$hid,'token'=>$token,'status'=>1))->find();
       $this->assign('host',$host);
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

        if(!empty($host['default_col_show'])) {
            $this->assign('default_col_show', unserialize($host['default_col_show']));
        }

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

       $where = array('id'=>$id,'token'=>$token, 'status'=>1);
       $types = M('Host_list_add')->where($where)->find();
	   //dump($types);
       $this->assign('types',$types);

       $save_monery = $types['price'] - $types['yhprice']; 
       $this->assign('userinfo',$userinfo);
       $this->assign('saves',$save_monery );
       
       // sms setting
       $enable_vcode = '0';
       $smsset = M('sms_set')->where(array('token' => $token))->find();
       if(!empty($smsset)) {
           if(strpos($smsset['function'], 'vcdindan') !== FALSE) {
               $enable_vcode = '1';
           }
       }
       
       $this->assign('enable_vcode', $enable_vcode);
       
       $this->display();

    }

    private function get_order_sn()
    {

        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        return date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

    }
    
    //在线预定
    public function book()
    {
        if($_POST['action'] == 'book')
        {
            $token = $this->_get('token');
            
            // 检查是否需要短信验证码
            $enable_vcode = 0;
            $smsset = M('sms_set')->where(array('token'=> $token))->find();
            if(!empty($smsset)) {
                if(strpos($smsset['function'], 'vcdindan') !== FALSE) {
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

            $id       = $this->_post('id');
            $data['room_type']      = $this->_post('roomtype'); 
            $data['order_status']   = 3 ;
            $data['hid']            = $this->_get('hid');
            $data['booking_id']     = $this->_post('id');
            $data['token']          = $this->_get('token');
            $data['submit_time']    = time();
            $data['sn']             = $this->get_order_sn();
            


            $price = M('Host_list_add')->where(array('id'=>$id,'token'=>$data['token'],'hid'=>$data['hid'],'status'=>1))->find();

            //判断库存
            if($price['open_inventory'] == 1 && ($price['inventory'] < $data['book_num']) )
            {
                echo '{"success":0,"msg":"库存不足，请联系商家！"}';
                exit;
            }

            $data['price'] = $price['yhprice'] * $data['book_num'];
            
            $data['text_cols'] = serialize($this->_post('text_cols'));
            $data['select_cols'] = serialize($this->_post('select_cols'));
            $order = M('Host_order')->data($data)->add();

            //若下单成功则减库存
            if($price['open_inventory'] == 1 && $order)
            {
                $booking['inventory'] = $price['inventory'] - $data['book_num'];
                M('host_list_add')->where(array('id'=>$id,'token'=>$data['token']))->data($booking)->save();
            }
		    
            if ($order) {
                include(LIB_PATH.'Action/SmsSender.class.php');
                $smsSender = new SmsSender();

                $notify_tmpl = C('host_notify');
                $smsContent = '';
                if (isset($notify_tmpl)) 
                {
                    $smsContent = str_replace("#merchant#",     '领众科技',           $notify_tmpl);
                    $smsContent = str_replace("#book_people#",  $data['book_people'],  $smsContent);
                    $smsContent = str_replace("#tel#",          $data['tel'],       $smsContent);
                    $smsContent = str_replace("#room_type#",    $data['room_type'],  $smsContent);
                    $smsContent = str_replace("#book_num#",     $data['book_num'],  $smsContent);
                    $smsContent = str_replace("#dateline#",     $this->_post('dateline'),  $smsContent);
                    $smsContent = str_replace("#price#",        $data['price'],     $smsContent);
                    $smsContent = str_replace("#sn#",           $data['sn'],  $smsContent);
                }
                if ($smsContent != '') 
                {
                    $re = $smsSender->notify($this->_get('token'), "dingdan", $smsContent);
                }
                
                echo'{"success":1,"msg":"恭喜,预定成功。"}';
            }else{
                echo'{"success":0,"msg":"系统忙，请稍后预定。"}';
            }            
            exit;
        }    
            
        
    }
}
    
?>
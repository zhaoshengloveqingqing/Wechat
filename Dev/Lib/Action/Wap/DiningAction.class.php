<?php
require_once(COMMON_PATH.'/CopyRightHelper.php');
class DiningAction extends WapAction {

    protected function _initialize()
    {
        parent::_initialize();

        $opened_funcs = session('opened_funcs_'.$this->token);
        $cur_func = 'canyin';
        if (!in_array($cur_func ,$opened_funcs))
        {
            //Log::record('Dining function verification failed: token:'.$this->token.' opened_funcs:'.print_r($opened_funcs, true));
            //Log::save();
            echo '没有开启餐饮功能!';
            exit;
        }

        if (empty($this->wechat_id)) 
        {
            //未登录
            if ($this->wechat_type == 1 && $this->is_authed == 1) 
            {
                $host_name = C('wx_handler_server');
                
                $cur_url = urlencode('http://'.$host_name.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']."#wechat_redirect");
                //必须是认证过的服务号
                $redirect = urlencode('http://'.$host_name."/index.php?g=Wap&m=Oauth2&a=index&backurl=".$cur_url."#wechat_redirect");
       
                $auth_url =  "https://open.weixin.qq.com/connect/oauth2/authorize?appid="
                                .$this->appid
                                    ."&redirect_uri=".$redirect
                                    ."&response_type=code&scope=snsapi_userinfo&state=".$this->token."#wechat_redirect";
                //Log::record('auth_url'.$auth_url);
                header("Location:$auth_url");   
                //确保重定向后，后续代码不会被执行   
            }
        }
        $this->assign('token',$this->token);
    }
    
    public function rest() {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $wecha_id = $this->_get('wecha_id');
        if(empty($this->token))  $this->error('非法操作');

        $where      = array('token'=>$this->token, 'status'=>1); 
        $list = M('dine_restlist')->where( $where )->order('orderNum desc')->select();
        for($j = 0; $j < count($list); $j++) {
            $list[$j]["navi"] = "http://api.map.baidu.com/marker?location="
                .$list[$j]['latitude'].','.$list[$j]['longtitude']
                .'&title='.urlencode($list[$j]['name'])
                .'&name='.urlencode($list[$j]['name'])
                .'&content='.urlencode($list[$j]['address'])
                .'&output=html&src=lingzhtech';
        }
        $this->assign('list',$list);
        $this->assign('wecha_id',$wecha_id);
        $this->display();
    }
    
    public function index() {
        $id = $this->_get('rest_id','intval');
        if ($id) $where['id'] = $id;
        if ($this->token) 
        {
            $where['token'] = $this->token;
        }
        else
        {
            exit;
        }

        $where['status'] = 1;
        $rest = M('dine_restlist')->where($where)->find();
        $this->assign("rest", $rest);
        if ($rest) {
            session('token', $rest['token']);
        }
        $this->assign("rest_id", $rest['id']);
        
        $wecha_id = $this->getuid();
        $where2['status'] = 1;
        $where2['wecha_id'] = $wecha_id;
        $where2['rest_id'] = $rest['id'];
        $orders = M('dine_order')->where($where2)->order('createtime desc')->find();
        $menus = json_decode($orders['menus'], true);
        $num = 0;
        for($j = 0; $j < count($menus); $j++) 
        {
            $num += (int)$menus[$j]['nums'];
            $select_orders[$menus[$j]['dishes_id']] = $menus[$j];
        }
        $this->assign("num", $num);
        if ($select_orders) {
            $this->assign("orders", json_encode($select_orders));
            $this->assign("order_id", $orders['id']);
        } else {
            $this->assign("orders", "{}");
        }
        
        $this->display();
    }
    
    private function getuid() {
        $wecha_id = $this->_get('wecha_id');
        if (!$wecha_id) {
            $wecha_id  = session("wechat_id_".$this->token);
        }
        if (!$wecha_id) {
            $wecha_id = session_id();
        }
        return $wecha_id;
    }
    
    public function menus() {
        $rest_id = $this->_get('rest_id');
        $where['rest_id'] = $rest_id;
        $where['status'] = 1;
        $categorys = M('dine_category')->where($where)->order('orderNum desc, update_time desc')->select();
        for($j = 0; $j < count($categorys); $j++) {
            $where2['category_id'] = $categorys[$j]['id'];
            $where2['status'] = 1;
            $menus = M('dine_menu')->where($where2)->order('orderNum desc, update_time desc')->select();
            if ($j == 0) {
                $categorys[$j]['selectedClass'] = 0;
            }
            for($k = 0; $k < count($menus); $k++) {
                if ($menus[$k]['promt_status'] == 1) {
                    $menus[$k]['tag_name'] = "推荐";
                } else {
                    $menus[$k]['tag_name'] = "";
                }
            }
            $categorys[$j]['dishes'] = $menus;
        }
        Log::save();
        $this->ajaxReturn($categorys, "OK", 1);
    }

    public function cart() {
        $menus = $this->_post('menus');
        $rest_id = $this->_post('rest_id');
        $cart_id = $this->_get('cart_id');
        
        $menus = htmlspecialchars_decode($menus);
        $menus = htmlspecialchars_decode($menus);
        $data['menus'] = $menus;
        
        if ($cart_id) {
            $where["id"] = $cart_id;
            M('dine_order')->where($where)->save($data);
            $order = $cart_id;            
        } else {
            $data['rest_id'] = $rest_id;
            $data['status'] = 1;
            $data['createtime'] = time();
            $wecha_id = $this->getuid();
            $data['wecha_id'] = $wecha_id;
            $data['sn'] = $this->get_order_sn();
        
            $order = M('dine_order')->add($data);
        }
        Log::save();
        $this->ajaxReturn($order, "OK", 1);
    }
    
    private function get_order_sn()
    {
        mt_srand((double) microtime() * 1000000);
        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    
    public function mycart() {
        $cart_id = $this->_get('cart_id', 'intval');
        $where['id'] = $cart_id;
        $where['status'] = 1;
        $order = M('dine_order')->where($where)->order('createtime desc')->find();
        
        $wecha_id = $this->getuid();
        $where2['rest_id'] = $order['rest_id'];
        $where2['wecha_id'] = $wecha_id;
        $where2['status'] = 2;
        $user = M('dine_order')->where($where2)->order('submittime desc')->find();
        $this->assign("user", $user);
        $this->assign('wecha_id', $wecha_id);
        
        $order['menus'] = preg_replace('/\r|\n/','', $order['menus']);  //过滤回车字符
            
        $menus = json_decode($order['menus'],true);
        $num = 0;
        $price = 0;
        for($j = 0; $j < count($menus); $j++) {
            $menu = M('dine_menu')->where(array("id"=>$menus[$j]['dishes_id'], "status"=>array("neq", 0)))->find();
            if (!$menu) {
                array_splice($menus, $j, 1);
                $j--;
                continue;
            }
            $menus[$j]['price'] = $menu['price'];
            $num += (int)$menus[$j]['nums'];
            $price += (float)$menus[$j]['price'] * (int)$menus[$j]['nums'];
        }
        // sms setting
        $enable_vcode = '0';
        $smsset = M('sms_set')->where(array('token'=> $this->token))->find();
        if(!empty($smsset)) {
            if(strpos($smsset['function'], 'vccanyin') !== FALSE) {
                $enable_vcode = '1';
            }
        }

        $this->assign('enable_vcode', $enable_vcode);

        Log::save();
        $this->assign("num", $num);
        $this->assign("price", $price);
        $this->assign("order", $order);
        $this->assign("menus", $menus);
        $this->display();
    }
    
    public function book() {
        // 检查是否需要短信验证码
        $token = $this->token;
        $enable_vcode = 0;
        $smsset = M('sms_set')->where(array('token'=>$token))->find();
        if(!empty($smsset)) {
            if(strpos($smsset['function'], 'vccanyin') !== FALSE) {
                $enable_vcode = 1;
            }
        }

        $tel = $this->_post('tel');
        $smsvcode = $this->_post('smsvcode');

        if($enable_vcode) {

            if(!SmsvcodeAction::verifyVCode($token, $tel, $smsvcode)) {
                // 验证码不匹配
                Log::save();
                $this->ajaxReturn("请输入正确的短信验证码！", "ERROR", 0);
            }
        }
        
        $menus      = $this->_post('menus');
        $cart_id    = $this->_post('cart_id');
        $username   = $this->_post('username');
        $guestnum   = $this->_post('guestnum');
        $note       = $this->_post('note');
        $dinetime   = $this->_post('dinetime');
        $where['id'] = $cart_id;
        //获取token以便发送短信提醒
        $order_info = M('dine_order')->join('tp_dine_rest on tp_dine_order.rest_id = tp_dine_rest.id')
                                ->where(array('tp_dine_order.id'=>$cart_id))
                                ->field('tp_dine_rest.token,tp_dine_order.sn')
                                ->order('createtime desc')->find();
        $menus = htmlspecialchars_decode($menus);
        $menus = htmlspecialchars_decode($menus);

        $jmenus = json_decode($menus, true);
        $price = 0;
        $dishnum = count($jmenus);
        $dishname = '';
        for($j = 0; $j < count($jmenus); $j++) {
            $price += (float)$jmenus[$j]['price'] * (int)$jmenus[$j]['nums'];
            $dishname .= $jmenus[$j]['name'].' ';
        }
        $data['price']      = $price;
        $data['tel']        = $tel;
        $data['username']   = $username;
        $data['menus']      = $menus;
        $data['status']     = 2;
        $data['submittime'] = time();
        $data['guestnum']   = $guestnum;
        $data['note']       = $note;
        $data['dinetime']   = $dinetime;
        $data['dishnum']    = $dishnum;
        $data['table'] = $this->_post('table');
        $order = M('dine_order')->where($where)->save($data);
        if($order)
        {
            include(LIB_PATH.'Action/SmsSender.class.php');

            // 获得公司名字， 作为短信落款
            $company = '';
            $merchant = M('wxuser')->where(array('token'=>$this->token, 'status'=>1))->field('company')->find();
            if(empty($merchant['company'])) {
                $company = "领众科技";
            }else {
                $company = $merchant['company'];
            }

            $smsSender = new SmsSender(); 

            $notify_tmpl = C('dine_notify');
            if (isset($notify_tmpl)) 
            {
                $smsContent = str_replace("#merchant#", $company,           $notify_tmpl);
                $smsContent = str_replace("#username#", $data['username'],  $smsContent);
                $smsContent = str_replace("#tel#",      $data['tel'],       $smsContent);
                $smsContent = str_replace("#dinetime#", $data['dinetime'],  $smsContent);
                $smsContent = str_replace("#guestnum#", $data['guestnum'],  $smsContent);
                $smsContent = str_replace("#dishnum#",  $data['dishnum'],   $smsContent);
                $smsContent = str_replace("#sn#",       $order_info['sn'],  $smsContent);
                $smsContent = str_replace("#menus#",    $dishname,          $smsContent);

                if($data['table'])
                {
                    $table_info = $data['table']."号桌";
                    $smsContent = str_replace("#table#", $table_info,  $smsContent);
                }
                else
                {
                    $smsContent = str_replace("#table#", '桌号无',  $smsContent);
                }

                $res = $smsSender->notify($order_info['token'],"canyin_order", $smsContent);
                $this->ajaxReturn($order, "OK", 1);
            }
        }
        else
        {
            $this->ajaxReturn("服务器繁忙，订单生成失败，请稍后再试！", "ERROR", 0);
        }
    }

    public function order() {
        $wecha_id = $this->getuid();
        $rest_id = $this->_get('rest_id', 'intval');
        $this->assign("rest_id", $rest_id);
        $where['rest_id'] = $rest_id;
        $where['wecha_id'] = $wecha_id;
        $where['status'] = array("gt", 1);
        $order = M('dine_order')->where($where)->order('submittime desc')->find();
        
        $menus = json_decode($order['menus'], true);
        $num = 0;
        $price = 0;
        for($j = 0; $j < count($menus); $j++) {
            $num += (int)$menus[$j]['nums'];
            $price += (float)$menus[$j]['price'] * (int)$menus[$j]['nums'];
        }
        
        $this->assign("num", $num);
        $this->assign("price", $price);
        $this->assign("order", $order);
        $this->assign("menus", $menus);
        $this->display();
    }
    
    public function orders() {
        $wecha_id = $this->getuid();
        $rest_id = $this->_get('rest_id', 'intval');
        $where['rest_id'] = $rest_id;
        $where['wecha_id'] = $wecha_id;
        $where['status'] = array("gt", 1);
        $orders = M('dine_order')->where($where)->order('submittime desc')->limit(5)->select();
        for ($i = 0; $i < count($orders); $i++) {
            $menus = json_decode($orders[$i]['menus'], true);
            $num = 0;
            $price = 0;
            for($j = 0; $j < count($menus); $j++) {
                $num += (int)$menus[$j]['nums'];
                $price += (float)$menus[$j]['price'] * (int)$menus[$j]['nums'];
            }
            $orders[$i]["menu"] = $menus;
            $orders[$i]["price"] = $price;
        }
        $this->assign("orders", $orders);
        $this->assign("rest_id", $rest_id);
        $this->display();
    }
    
    public function clear() {
        $cart_id = $this->_get('cart_id', 'intval');
        $where['id'] = $cart_id;
        $data['status'] = 0;
        $order = M('dine_order')->where($where)->save($data);
        $this->ajaxReturn($order, "OK", 1);
    }
    
    public function cancel() {
        $cart_id = $this->_get('cart_id', 'intval');
        $where['id'] = $cart_id;
        $data['status'] = 5;
        $order = M('dine_order')->where($where)->save($data);
        $this->ajaxReturn($order, "OK", 1);
    }
}
?>
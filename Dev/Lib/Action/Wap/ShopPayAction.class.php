<?php
class ShopPayAction extends Action
{
    
    public function startAlipay()
    {
        $order_sn 	= $this->_get('order_sn');
        $token 		= $this->_get('token');
        Log::record($token.':'.$order_sn);
        if(empty($order_sn) || empty($token))
        {
            Log::record('startAlipay: '.print_r($_GET, true));
            Log::save();
            $this->error('请输入订单号');
        }


        $order = M('b2c_order')->where(array('sn'=> $order_sn, 'token'=> $token, 'status'=>1))->find();
        if(!$order)
            $this->error('订单号不正确');

        $shop = M('b2c_shop')->where(array('token'=>$token))->find();
        if(!$shop)
            $this->error('商城不存在');

        $alipay = M('b2c_payment')->where(array('token'=>$token, 'pay_code'=>'alipay'))->find();
        $alipay_config = array();
        if (is_string($alipay['pay_config']))
        {
            $store = unserialize($alipay['pay_config']);
            $alipay_config['pay_account']         = $store['pay_account'];

            //合作身份者id，以2088开头的16位纯数字
            $alipay_config['partner']             = $store['alipay_pid'];
            //安全检验码，以数字和字母组成的32位字符
            //如果签名方式设置为“MD5”时，请设置该参数
            $alipay_config['key']                 = $store['alipay_key'];

            //商户的私钥（后缀是.pen）文件相对路径
            //如果签名方式设置为“0001”时，请设置该参数
            //$alipay_config['private_key_path']  = getcwd()."/Conf/Wap/key/".$shop['token']."_rsa_private_key.pem";

            //支付宝公钥（后缀是.pen）文件相对路径
            //如果签名方式设置为“0001”时，请设置该参数
            //$alipay_config['ali_public_key_path']= getcwd().'/Conf/Wap/key/'.$shop['token'].'_alipay_public_key.pem';

            //签名方式 不需修改
            $alipay_config['sign_type']    = 'MD5';//'0001';

            //字符编码格式 目前支持 gbk 或 utf-8
            $alipay_config['input_charset']= 'utf-8';

            //ca证书路径地址，用于curl中ssl校验
            //请保证cacert.pem文件在当前文件夹目录中
            $alipay_config['cacert']    = getcwd().'\\Conf\\Wap\\key\\cacert.pem';

            //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
            $alipay_config['transport']    = 'http';
        }
        Log::record('alipay_config Args:'.print_r($alipay_config, true),Log::INFO);

        //返回格式
        $format = "xml";
        //必填，不需要修改

        //返回格式
        $v = "2.0";
        //必填，不需要修改

        //请求号
        $req_id = date('Ymdhis'). str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        //必填，须保证每次请求都是唯一


        //**req_data详细信息**

        //服务器异步通知页面路径
        $notify_url = C('site_url').'/index.php/alipay/notify';
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $call_back_url = C('site_url').'/index.php/alipay/return_url';
        //需http://格式的完整路径，不允许加?id=123这类自定义参数

        //操作中断返回地址
        $merchant_url = "http://".$shop['token'].".shop.weixinwz.com/";
        //用户付款中途退出返回商户的地址。需http://格式的完整路径，不允许加?id=123这类自定义参数

        //卖家支付宝帐户
        $seller_email = trim($alipay_config['pay_account']);
        //必填

        //商户订单号
        $out_trade_no = $order['sn'];
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '订单'.$order['sn'].'支付';
        //必填

        //付款金额
        $total_fee = $order['price'];
        //必填

        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';



        //构造要请求的参数数组，无需改动
        $para_token = array(
                "service" => "alipay.wap.trade.create.direct",
                "partner" => trim($alipay_config['partner']),
                "sec_id" => trim($alipay_config['sign_type']),
                "format"    => $format,
                "v" => $v,
                "req_id"    => $req_id,
                "req_data"  => $req_data,
                "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );

        Log::record('para_token Args:'.print_r($para_token, true),Log::INFO);

        $trade = M('b2c_trade')->where(array('order_sn'=> $order_sn, 'token'=> $shop['token']))->find();
        if ($trade == null) 
        {
            $time = time();
            $data = array('subject'=>$subject,'name'=>$order['truename'],'order_sn'=>$out_trade_no,'price'=>$total_fee);
            $data['create_time'] = $time;
            $data['update_time'] = $time;
            $data['token'] = $shop['token'];

            $ret  = M('b2c_trade')->data($data)->add(); 
        } 
        else 
        {
            if ($trade['trade_no']) 
            {
                $this->error('已支付');
            }
        }

        import("@.ORG.AliWapPay.AlipaySubmit");


        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);

        //URLDECODE返回的信息
        $html_text = urldecode($html_text);

        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);

        //获取request_token
        $request_token = $para_html_text['request_token'];
        Log::record('token:'.print_r($para_html_text,true),Log::INFO);

        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填

        //构造要请求的参数数组，无需改动
        $parameter = array(
                "service" => "alipay.wap.auth.authAndExecute",
                "partner" => trim($alipay_config['partner']),
                "sec_id" => trim($alipay_config['sign_type']),
                "format"    => $format,
                "v" => $v,
                "req_id"    => $req_id,
                "req_data"  => $req_data,
                "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
        );

        Log::record('AliWapPay Args:'.print_r($parameter, true),Log::INFO);
        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', 'Start to pay');
        echo $html_text;

    }

    public function setconfig($alipay_pid, $alipay_key)
    {
        $alipay_config['partner']        = trim($alipay_pid);
        //安全检验码，以数字和字母组成的32位字符
        $alipay_config['key']            = trim($alipay_key);
        //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        //签名方式 不需修改
        $alipay_config['sign_type']    = strtoupper('MD5');
        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= strtolower('utf-8');
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']    = getcwd().'\\PigCms\\Lib\\ORG\\Alipay\\cacert.pem';
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';        
        return $alipay_config;
    }

    //同步数据处理
    public function return_url ()
    {
        $out_trade_no       = $_GET['out_trade_no'];
        Log::record("return url:".print_r($_GET, true), Log::INFO);

        $shop = null;
        $trade = null;
        if ($out_trade_no)
        {
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $shop = $Model->query("select o.token, o.wecha_id, p.pay_config  from tp_b2c_order as o JOIN tp_b2c_payment as p on p.token = o.token where p.pay_code ='alipay' and o.sn = '$out_trade_no'");
            if ($shop == null) 
            {
                Log::record("Illegle order sn:".$out_trade_no, Log::NOTICE);
                $this->error('非法订单号',U('Wap/Shop/error'));
            }

            if (is_array($shop) && is_string($shop[0]['pay_config']))
            {
                $store = unserialize($shop[0]['pay_config']);
                $shop[0]['pay_account']         = $store['pay_account'];

                //合作身份者id，以2088开头的16位纯数字
                $shop[0]['partner']             = $store['alipay_pid'];
                //安全检验码，以数字和字母组成的32位字符
                //如果签名方式设置为“MD5”时，请设置该参数
                $shop[0]['key']                 = $store['alipay_key'];
            }
        }

        Log::record("shop_config:".print_r($shop,true), Log::INFO);

        import("@.ORG.AliWapPay.AlipayNotify");
        $alipayNotify = new AlipayNotify($this->setconfig($shop[0]['partner'], $shop[0]['key']));
        $verify_result = $alipayNotify->verifyReturn();

        if($verify_result) 
        {     
            //商户订单号
            $out_trade_no       = $_GET['out_trade_no'];
            //支付宝交易号
            $trade_no           = $_GET['trade_no'];
            //交易状态
            $trade_status       = $_GET['result'];
            //用户付款时间
            $gmt_payment_time   = time();
                

            $trade_db        = M('b2c_trade');
            $trade_where     = array('order_sn'=>$out_trade_no);
            $trade           = $trade_db->field('trade_id','token', 'trade_no')->where($trade_where)->find();
            Log::record('return url: status:'.$trade_status.' trade_no:'.$trade['trade_no'], Log::INFO);
            Log::save();
            if ($trade_status == 'success' && $trade && empty($trade['trade_no']))
            {    
                $data = array(
                    'status'                => 2, 
                    'trade_no'              => $trade_no, 
                    'alipay_create_time'    => time(),
                    'payment_time'          => $gmt_payment_time,
                    );
                $ret = $trade_db->where($trade_where)->save($data);
                if ($ret)
                {
                    //2 是订单已付款
                    $order_ret = M('b2c_order')->where(array('sn'=>$out_trade_no))->save(array('status' => 2)); 
                    Log::save();
                    //echo "交易成功<br />";
                    $redirect = WapAction::generatePayResultUrl('Shop/my', $shop[0]['token'], array('token'=> $shop[0]['token'],'wecha_id'=>$shop[0]['wecha_id'],'success'=>1));
                    $this->redirect($redirect);  
                    //$this->success('交易成功',U('Wap/Shop/index',array('shop'=> $trade['token'])));
                }
            }
            
            if($trade_status == 'success') {
                $redirect = WapAction::generatePayResultUrl('Shop/my', $shop[0]['token'], array('token'=> $shop[0]['token'],'wecha_id'=>$shop[0]['wecha_id'],'success'=>1));
                $this->redirect($redirect); 
            }else {
                $redirect = WapAction::generatePayResultUrl('Shop/my', $shop[0]['token'], array('token'=> $shop[0]['token'],'wecha_id'=>$shop[0]['wecha_id'],'success'=>0));
                $this->redirect($redirect); 
            }
        }
        else
        {            
            Log::save();
            //echo "支付失败<br />";
            $redirect = WapAction::generatePayResultUrl('Shop/my', $shop[0]['token'], array('token'=> $shop[0]['token'],'wecha_id'=>$shop[0]['wecha_id'],'success'=>0));
            $this->redirect($redirect); 
            //$this->error('支付失败 ,请联系客服,为您处理',U('Wap/Shop/index'));
        }
    }

    public function notify()
    {

        $shop = null;

        //$notify_data = $alipayNotify->decrypt($_POST['notify_data']);
        $notify_data = $_POST['notify_data'];
        Log::record("Alipay notify data:".print_r($notify_data,true), Log::INFO);
        //解析notify_data
        //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
        $doc = new DOMDocument();
        $doc->loadXML($notify_data); 

        //商户订单号
        $out_trade_no       = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;

        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $shop = $Model->query("select o.token, p.pay_config  from tp_b2c_order as o JOIN tp_b2c_payment as p on p.token = o.token where p.pay_code ='alipay' and o.sn = '$out_trade_no'");
        if ($shop == null) 
        {
            Log::record("Illegle order sn:".$out_trade_no, Log::NOTICE);
            $this->error('非法订单号',U('Wap/Shop/error'));
        }
        
        if (is_array($shop) && is_string($shop[0]['pay_config']))
        {
            $store = unserialize($shop[0]['pay_config']);
            $shop[0]['pay_account']         = $store['pay_account'];

            //合作身份者id，以2088开头的16位纯数字
            $shop[0]['partner']             = $store['alipay_pid'];
            //安全检验码，以数字和字母组成的32位字符
            //如果签名方式设置为“MD5”时，请设置该参数
            $shop[0]['key']                 = $store['alipay_key'];
        }

        Log::record("shop_config:".print_r($shop,true), Log::INFO);


        import("@.ORG.AliWapPay.AlipayNotify");
        //计算得出通知验证结果

        $alipayNotify = new AlipayNotify($this->setconfig($shop[0]['partner'], $shop[0]['key']));
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) 
        {
            

            if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) 
            {
                
                //支付宝交易号
                $trade_no           = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                //交易状态
                $trade_status       = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;

                $buyer_id           = $doc->getElementsByTagName( "buyer_id" )->item(0)->nodeValue;
                //交易创建时间
                $gmt_create_time    = strtotime($doc->getElementsByTagName( "gmt_create" )->item(0)->nodeValue);
                //用户付款时间
                $gmt_payment_time   = $doc->getElementsByTagName( "gmt_payment" )->item(0)->nodeValue;
                
                $refund_status      = $doc->getElementsByTagName( "refund_status" )->item(0)->nodeValue;

                $trade_db        = M('b2c_trade');
                $trade_where     = array('order_sn'=>$out_trade_no);
                $trade           = $trade_db->field('trade_id','token','trade_no')->where($trade_where)->find();
                if ($trade && empty($trade['trade_no']))
                {    
                    $data = array(
                        'status'                => 2, 
                        'trade_no'              => $trade_no, 
                        'buyer_id'              => $buyer_id,
                        'alipay_create_time'    => $gmt_create_time,
                        'payment_time'          => $gmt_payment_time,
                        'refund_status'         => $refund_status,
                    );
                    $trade_db->where($trade_where)->save($data);
                    $order_ret = M('b2c_order')->where(array('sn'=>$out_trade_no))->save(array('status' => 2));
                    //减库存
                    ShopAction::minusInventory($out_trade_no);
                }

                Log::save();
                
                if($trade_status == 'TRADE_FINISHED') {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                            
                    //注意：
                    //该种交易状态只在两种情况下出现
                    //1、开通了普通即时到账，买家付款成功后。
                    //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
            
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                    
                    echo "success";     //请不要修改或删除
                }
                else if ($trade_status == 'TRADE_SUCCESS') {
                    //判断该笔订单是否在商户网站中已经做过处理
                        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                        //如果有做过处理，不执行商户的业务程序
                            
                    //注意：
                    //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
            
                    //调试用，写文本函数记录程序运行情况是否正常
                    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                    
                    echo "success";     //请不要修改或删除
                }

                
            }
        }
        else
        {
            //验证失败
            echo "fail";

            Log::save();

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }
    
}



?>
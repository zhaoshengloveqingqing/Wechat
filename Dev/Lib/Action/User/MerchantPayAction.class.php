<?php
require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');
class MerchantPayAction extends Action
{
    public function start_m_alipay() 
    {
        if(IS_POST) 
        {
            //获取当前运营管理员的支付宝配置信息
            $uid = session('uid');
            $sql = "select ac.cfg_data,a.role from tp_users as u "
                        ." LEFT JOIN tp_oem_cfg as ac on u.administrator = ac.agent_id "
                        ." LEFT JOIN tp_user as a on u.administrator = a.id "
                        ." where u.id = $uid; ";
            $Model = new Model();
            $cfg = $Model->query($sql);

            $agent_cfg = unserialize($cfg[0]['cfg_data']);
            if ($cfg != false && ($cfg[0]['role'] == 12 || $cfg[0]['role'] == 13 || $cfg[0]['role'] == 18)) 
            {
                $default_agent_cfg = C('default_agent_info');
                //代理商用户和直销用户使用领众的支付信息,oem不允许使用支付宝支付
                $agent_cfg['alipay_seller_email']   = $default_agent_cfg['alipay_seller_email'];
                $agent_cfg['alipay_partner']        = $default_agent_cfg['alipay_partner'];
                $agent_cfg['alipay_key']            = $default_agent_cfg['alipay_key'];
                $agent_cfg['alipay_service']        = $default_agent_cfg['alipay_service'];
            }

            //检查支付宝支付配置信息
            if (!isset($agent_cfg) 
                    || empty($agent_cfg['alipay_seller_email']) 
                    || empty($agent_cfg['alipay_partner'])
                    || empty($agent_cfg['alipay_key'])
                    || empty($agent_cfg['alipay_service'])) 
            {
                $this->error('支付宝支付异常，请联系客服！', U('Index/purchase'));
            }

            //商户充值信息
            $packageId = $_POST['package_id'];
            $packageDuration = intval($_POST['package_duration']); //当type为2时，表示短信条数
            $packageCount = 1;
            $need_invoice = 0;
            if(isset($_POST['need_invoice'])) 
            {
                $need_invoice = 1;
            }
                
            $merchantPackages = C('MERCHANT_PACKAGES');
            if(!array_key_exists($packageId, $merchantPackages))
            {
                $this->error('非法操作！', U('Index/purchase'));
            }
                
            // 计算总价
            $package_fee = $merchantPackages[$packageId]['price_month'] * $packageDuration * $packageCount;
            if($need_invoice == 1) 
            {
                $totalFee = $package_fee * (1 + C('merchant_invoice_ratio'));
            }
            else 
            {           
                $totalFee = $package_fee;
            }
                
            //套餐类型
            $package_type = $merchantPackages[$packageId]['type'];
            
            // 准备插入数据
            $data = $_POST;
            $data['need_invoice']   = $need_invoice;
            $data['package_fee']    = $package_fee;
            $data['total_fee']      = $totalFee;
            $data['package_count']  = $packageCount;
            $data['package_type']   = $package_type;
            $data['status']         = 1; 
            $data['submit_time']    = time();
            $data['user_id']        = $uid;

            // 订单号生成规则(20位)：rand(3) ＋ 时间戳（11位) + 用户ID（6）
            $tradeNo = rand(100,999).str_pad(''.time(), 11, '0', STR_PAD_LEFT).str_pad($uid, 4, '1',STR_PAD_LEFT);
            $data['trade_no'] = $tradeNo;
                
            $auditId = M('audit_merchant_purchase')->add($data);
            if($auditId == FALSE) 
            {
                Log::record('audit_merchant_purchase:'.$uid.' packageId:'.$packageId.' type:'.$package_type.' add audit record fail');
                Log::save();
                $this->error('服务器忙！请重试！', U('Index/purchase'));
            }
                
            // 调用支付宝接口
            import("@.ORG.Alipay.AlipaySubmit");
        
            //订单描述
            if($package_type == 2) {
                $body = $merchantPackages[$packageId]['name'].':'.$packageDuration.'条';
            }else {
                $body = $merchantPackages[$packageId]['name'].':'.$packageDuration.'个月';
            }
            //订单名称
            $subject = $body;


            //建立请求
            $alipayConfig = C('alipay_config');
            $alipayConfig['partner']     = $agent_cfg['alipay_partner'];
            $alipayConfig['key']         = $agent_cfg['alipay_key'];

            //构造要请求的参数数组
            $parameter = array(
                "service"         => $agent_cfg['alipay_service'],
                "partner"         => $agent_cfg['alipay_partner'],
                "payment_type"    => "1",
                "notify_url"      => C('site_url').'/index.php/merchantpay/notify',
                "return_url"      => C('site_url').'/index.php/merchantpay/return_url',
                "seller_email"    => $agent_cfg['alipay_seller_email'],
                "out_trade_no"    => $tradeNo,
                "subject"         => $subject,
                "total_fee"       => $totalFee,
                "body"            => $body,
                "show_url"        => '',
                "anti_phishing_key"    => '',
                "exter_invoke_ip"    => '',
                "_input_charset"    => $alipayConfig['input_charset'],
            );
            
            $alipaySubmit = new AlipaySubmit($alipayConfig);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "开始支付");
            echo $html_text;
        }
    }

    //商户支付返回网址
    public function m_alipay_return() 
    {
        Log::record('m_return_url called. get:'.print_r($_GET, true).' post:'.print_r($_POST, true), Log::INFO);
        Log::save();
        
        $uid = session('uid');
        $agent_cfg;
        if (!empty($uid)) 
        {
            $sql = "select ac.cfg_data, a.role from tp_users as u "
                        ." LEFT JOIN tp_oem_cfg as ac on u.administrator = ac.agent_id "
                        ." LEFT JOIN tp_user as a on u.administrator = a.id "
                        ." where u.id = $uid;";
            $Model = new Model();
            $cfg = $Model->query($sql);
            $agent_cfg = unserialize($cfg[0]['cfg_data']);
            if ($cfg != false && ($cfg[0]['role'] == 12 || $cfg[0]['role'] == 13)) 
            {
                $default_agent_cfg = C('default_agent_info');
                //代理商用户和直销用户使用领众的支付信息
                $agent_cfg['alipay_seller_email']   = $default_agent_cfg['alipay_seller_email'];
                $agent_cfg['alipay_partner']        = $default_agent_cfg['alipay_partner'];
                $agent_cfg['alipay_key']            = $default_agent_cfg['alipay_key'];
                $agent_cfg['alipay_service']        = $default_agent_cfg['alipay_service'];
            }
        } 
        else
        {
            Log::record("wrong request without uid");
            Log::record("record the returned info:".print_r($_GET,true));
            Log::save();
            exit;
        }

        if (empty($agent_cfg['alipay_partner']) 
            || empty($agent_cfg['alipay_key'])) 
        {
            Log::record("wrong alipay cfg.");
            Log::record("record the returned info:".print_r($_GET,true));
            Log::save();
            exit;
        }
        
        
        //载入支付宝支付配置信息
        $alipayConfig                 = C('alipay_config');
        $alipayConfig['partner']     = $agent_cfg['alipay_partner'];
        $alipayConfig['key']         = $agent_cfg['alipay_key'];

        import("@.ORG.Alipay.AlipayNotify");
        $alipayNotify = new AlipayNotify($alipayConfig);
        $verify_result = $alipayNotify->verifyReturn();
        
        if($verify_result) 
        {
            Log::record('m_return_url uid:'.session('uid').' '.print_r($_GET, true), Log::INFO);
            //商户订单号
            $out_trade_no                 = $_GET['out_trade_no'];
            //支付宝交易号
            $data['ali_trade_no']         = $_GET['trade_no'];
            //交易状态
            $data['ali_trade_status']     = $_GET['trade_status'];
            $data['ali_notify_id']        = $_GET['notify_id'];
            $data['ali_notify_type']      = $_GET['notify_type'];
            $data['ali_total_fee']        = $_GET['total_fee'];
            $data['ali_is_success']       = $_GET['is_success'];
            $data['ali_buyer_email']      = $_GET['buyer_email'];
            $data['ali_buyer_id']         = $_GET['buyer_id'];
            $data['handler']              = 0; //return_url
            $data['finish_time']          = time();
                
            // 检查交易是否存在领众数据库
            $auditRecord = M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->find();
            if ($auditRecord == false || empty($auditRecord['user_id'])) 
            {
                Log::record("record the returned info:".print_r($_GET,true));
                $this->error('非法操作！', U('User/Index/purchase'));
            }
            $merchantPackages = C('MERCHANT_PACKAGES');
                
            // 如果订单不处于 提交 状态，则忽略
            if ($auditRecord['status'] != 1 ) 
            {
                Log::record('m_return_url conflict. uid:'.$uid.' '.$out_trade_no);
                Log::save();
                $this->redirect(U('User/Index/purchase'));
                exit;
            }
                
            if ($data['ali_trade_status'] == 'TRADE_FINISHED' || $data['ali_trade_status'] == 'TRADE_SUCCESS') 
            {
                $data['status'] = 2;
                M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->save($data);

                // 验证该请求来自支付宝
                require_once(COMMON_PATH.'/AlipayNotifyVerifyHelper.php');
                $verifyStatus = AlipayNotifyVerifyHelper::ali_notify_verify($alipayConfig['partner'], $data['ali_notify_id']);
                if($verifyStatus == false)
                {
                    Log::record('m_return_url fail ali_notify_verify'.$out_trade_no, Log::ERR);
                    Log::save();
                    M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->setField('status', 4);
                    $this->error('套餐购买失败！如果您已支付成功，请联系客服为您处理！', U('User/Index/purchase'));
                }
                    
                Log::record('m_return_url finish ali_notify_verify. status=>5 '.$out_trade_no, Log::INFO);
                 M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->setField('status', 5);
                    
                 
                //需根据套餐类型设置payload参数：功能套餐设置成天数，短信包设置为条数
                $package_type = $merchantPackages[$auditRecord['package_id']]['type'];
                switch($package_type) {
                    case 1:
                        // 套餐处理
                        $fgListStr = $merchantPackages[$auditRecord['package_id']]['function_groups'];
                        $durationInSecs = $auditRecord['package_duration'] * 31 *24 * 60  * 60;
                        Log::record('m_return_url start open fg '.$fgListStr.' for '.$durationInSecs.' secs '.$out_trade_no , Log::INFO);

                        $codeFgIdList = explode(",", $fgListStr);
                        $websiteUserFuncManager = new WebsiteUserFuncManager($auditRecord['user_id']);
                        $websiteUserFuncManager->extendFuncGroups($codeFgIdList, $durationInSecs);
                        Log::record('m_return_url finish open fg '.$out_trade_no, Log::INFO);
                        Log::save();
                        break;
                    case 2:
                        //短信包
                        $volume = $auditRecord['package_duration'];
                        Log::record('m_return_url start sms '.$volume.' '.$out_trade_no , Log::INFO);

                        $websiteUserFuncManager = new WebsiteUserFuncManager($auditRecord['user_id']);
                        $websiteUserFuncManager->extendSmsAccount($volume);
                        Log::record('m_return_url finish sms '.$out_trade_no, Log::INFO);
                        Log::save();
                        break;
                }
                
                
                $this->redirect(U('User/Index/purchase'));
            }
            else 
            {
                Log::record('m_return_url fail to pay '.$out_trade_no, Log::ERR);
                Log::save();
                $data['status'] = 3; //支付失败
                M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->save($data);
                $this->error('套餐购买失败！如果您已支付成功，请联系客服为您处理！', U('User/Index/purchase'));
            }
        }
        else 
        {
            $out_trade_no                 = $_GET['out_trade_no'];
            Log::record('m_return_url verify fail. uid:'.session('uid').' '.$out_trade_no, Log::ERR);
            Log::save();
            $this->error('套餐购买失败 ,请联系在线客服,为您处理', U('User/Index/purchase'));
        }
    }

    //商户支付提醒网址
    public function m_alipay_notify() 
    {
        Log::record('mp_notify called. get:'.print_r($_GET, true).' post:'.print_r($_POST, true), Log::INFO);
        Log::save();
        $out_trade_no               = $_POST['out_trade_no'];
        $agent_cfg;
        if (!empty($out_trade_no)) 
        {
            $sql = 'select u.id,ac.agent_id,ac.cfg_data,a.role '
                       .' from tp_audit_merchant_purchase as mp '
                       .' left JOIN tp_users as u on mp.user_id = u.id '
                       .' LEFT JOIN tp_oem_cfg as ac on u.administrator = ac.agent_id ' 
                       ." LEFT JOIN tp_user as a on u.administrator = a.id "
                       ." where mp.trade_no = '$out_trade_no';";
               $Model = new Model();
            $cfg = $Model->query($sql);
            $agent_cfg = unserialize($cfg[0]['cfg_data']);
            if ($cfg != false && ($cfg[0]['role'] == 12 || $cfg[0]['role'] == 13)) 
            {
                $default_agent_cfg = C('default_agent_info');
                //代理商用户和直销用户使用领众的支付信息
                $agent_cfg['alipay_seller_email']   = $default_agent_cfg['alipay_seller_email'];
                $agent_cfg['alipay_partner']        = $default_agent_cfg['alipay_partner'];
                $agent_cfg['alipay_key']            = $default_agent_cfg['alipay_key'];
                $agent_cfg['alipay_service']        = $default_agent_cfg['alipay_service'];
            }
        }
           

        $alipayConfig = C('alipay_config');
        $alipayConfig['partner']     = $agent_cfg['alipay_partner'];
        $alipayConfig['key']         = $agent_cfg['alipay_key'];
        import("@.ORG.Alipay.AlipayNotify");
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipayConfig);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) 
        {
            Log::record('mp_notify verify succeed.'.$out_trade_no, Log::INFO);
            //Log::record('mp_notify:  '.print_r($_POST, true), Log::INFO);
            Log::save();
            //商户订单号
            $out_trade_no               = $_POST['out_trade_no'];
            //支付宝交易号
            $data['ali_trade_no']       = $_POST['trade_no'];
            //交易状态
            $data['ali_trade_status']   = $_POST['trade_status'];
            $data['ali_notify_id']      = $_POST['notify_id'];
            $data['ali_notify_type']    = $_POST['notify_type'];
            $data['ali_total_fee']      = $_POST['total_fee'];
            $data['ali_is_success']     = '1';
            $data['ali_buyer_email']    = $_POST['buyer_email'];
            $data['ali_buyer_id']       = $_POST['buyer_id'];
            $data['handler']            = 1; //notify
            $data['finish_time']        = time();

            // 检查交易是否存在领众数据库
            $auditRecord = M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->find();
            
            if($auditRecord == false || empty($auditRecord['user_id'])) 
            {
                Log::record('mp_notify: out_trade_no not exist:'.$out_trade_no, Log::ERR);
               // Log::record("record the notify info:".print_r($_POST,true));
                Log::save();
                echo 'fail';
                exit;
            }
            $merchantPackages = C('MERCHANT_PACKAGES');

            // 如果订单不处于 提交 状态，则忽略
            if ($auditRecord['status'] != 1 ) 
            {
                Log::record('mp_notify conflict '.$out_trade_no);
                if ($auditRecord['status'] != 6 ) 
                {
                    //如果不是重复提交，则应该保存提醒信息
                    Log::record("record the notify info:".print_r($_POST,true));
                }
                Log::save();
                echo "fail";
                exit;
            }

            if($data['ali_trade_status'] == 'TRADE_FINISHED' 
                || $data['ali_trade_status'] == 'TRADE_SUCCESS') 
            {
                $data['status'] = 2;
                M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->save($data);
                // 验证该请求来自支付宝
                require_once(COMMON_PATH.'/AlipayNotifyVerifyHelper.php');
                $verifyStatus = AlipayNotifyVerifyHelper::ali_notify_verify($alipayConfig['partner'], $data['ali_notify_id']);
                    
                if($verifyStatus == false) 
                {
                    Log::record('m_return_url fail ali_notify_verify'.$out_trade_no, Log::ERR);
                    Log::save();
                    M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->setField('status', 4);
                    echo "fail";
                    exit;
                }
                M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->setField('status', 5);
                Log::record('mp_notify finish ali_notify_verify. status=>5 '.$out_trade_no, Log::INFO);
                Log::save();
                
                // 套餐处理
                 //需根据套餐类型设置payload参数：功能套餐设置成天数，短信包设置为条数
                $package_type = $merchantPackages[$auditRecord['package_id']]['type'];
                switch($package_type) {
                    case 1:
                        // 套餐处理
                        $fgListStr = $merchantPackages[$auditRecord['package_id']]['function_groups'];
                        $durationInSecs = $auditRecord['package_duration'] * 31 *24 * 60  * 60;
                        Log::record('mp_notify start open fg '.$fgListStr.' for '.$durationInSecs.' secs '.$out_trade_no , Log::INFO);
                        Log::save();
                        $codeFgIdList = explode(",", $fgListStr);

                        $websiteUserFuncManager = new WebsiteUserFuncManager($auditRecord['user_id']);
                        $websiteUserFuncManager->extendFuncGroups($codeFgIdList, $durationInSecs);
                        Log::record('mp_notify finish open fg '.$out_trade_no, Log::INFO);
                        Log::save();
                        break;
                    case 2:
                        //短信包
                        $volume = $auditRecord['package_duration'];
                        Log::record('mp_notify start sms '.$volume.' '.$out_trade_no , Log::INFO);

                        $websiteUserFuncManager = new WebsiteUserFuncManager($auditRecord['user_id']);
                        $res = $websiteUserFuncManager->extendSmsAccount($volume);
                        Log::record('mp_notify finish sms '.$out_trade_no, Log::INFO);
                        Log::save();
                        break;
                }
                
                M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->setField('status', 6);
                echo "success";
                Log::save();
                exit;
            }
            else 
            {
                Log::record('mp_notify fail to pay '.$out_trade_no, Log::ERR);
                Log::save();
                $data['status'] = 3; //支付失败
                M('audit_merchant_purchase')->where(array('trade_no'=>$out_trade_no))->save($data);
                echo "fail";
                exit;
            }
        }
        else 
        {
            Log::record('mp_notify verify fail.', Log::ERR);
            Log::save();
            echo "fail";
            exit;
        }
    }

    
}



?>

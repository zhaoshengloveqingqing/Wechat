<?php

require_once(COMMON_PATH.'/PublicAccountFuncManager.php');
require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');

class IndexAction extends BaseAction
{

    protected $agent_cfg;
    protected function _initialize()
    {
        $uid = session('uid');
        if ($uid) 
        {
            $sql = "select ac.cfg_data, ag.role from tp_users as u left join tp_user as ag on u.administrator = ag.id LEFT JOIN tp_oem_cfg as ac on u.administrator = ac.agent_id where u.id = $uid; ";
            $Model = new Model();
            $user = $Model->query($sql);

            $this->agent_cfg = $this->getAgentCfg($user[0]['cfg_data']);

            $this->assign('agentInfo', $this->agent_cfg);
            if ($user[0]['role'] == 18) 
            {
                $this->assign('user_role', 'oem');
            }
        }
    }

    //商户公众号列表
    public function index()
    {
        $uid = session('uid');
        if ($uid) 
        {
            $where['status'] = 1;
            $where['uid'] = $uid;
            $db = M('Wxuser');

            $wx_accounts = $db->where($where)->select();
            if (count($wx_accounts) == 0) 
            {
                $wp_user = $db->where(array('uid' => $uid, 'status'=>0))->find();
                if ($wp_user != false) 
                {
                    $this->assign('token',$wp_user['token']);
                }
                else
                {
                    $newToken = $this->generateToken();
                    $code     = $this->generateCode();
                    $wx_user  = $db->where("token = '$newToken' or code=$code")->find();
                    if ($wx_user) 
                    {
                        // 理论上不会到该分支，因为token的生成算法已经保证了全局唯一
                        $this->error('抱歉，添加公众号失败，请刷新重试。',U(MODULE_NAME.'/add'));
                    } 
                    else 
                    {
                        $now = time();
                        $data['uid']        = session('uid');
                        //目前token跟微信id是同一个
                        $data['weixin']     = $newToken;
                        $data['token']      = $newToken; //str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT)
                        $data['headerpic']  = C('site_url').'/themes/a/images/logo.jpg';
                        $data['status']     = 0;
                        $data['code']       = $this->generateCode();
                        //初始化空微信号
                        $publicAccountId = $db->add($data);
                    }
                    $this->assign('token',$newToken);
                }
                $this->display('Account:edit');
            }
            else
            {
                $this->assign('info',$wx_accounts);

                $count = count($wx_accounts);
                $this->assign('public_count',$count);
                $this->display();
            }
        }
        else
        {
            header("Location:".U('User/Index/login@'.$_SERVER['SERVER_NAME'])); 
            exit;
        }
    }

    public function login()
    {
	vendor("Logger.Logger", LIB_PATH.'../Extend/Vendor');
		$logger = new Logger();
		$logger->trace('The stacktrace is ');
        $domain = $_SERVER['SERVER_NAME'];
        if (preg_match("/^([A-Za-z0-9]+)\.weixin\.[A-Za-z0-9]+\.com$/i", $domain, $matches)) 
        {
            //是oem客户
            $oem_name       = $matches[1];
            if (!empty($oem_name)) 
            {
                $login_pic = session('oem_logo');
                if (empty($login_pic)) 
                {
                    $sql = "select ac.cfg_data, u.role from  tp_user as u LEFT JOIN tp_oem_cfg as ac on u.id = ac.agent_id where u.domain_prefix = '$oem_name'; ";
                    $Model = new Model();
                    $user = $Model->query($sql);

                    if ($user != false) 
                    {
                        $this->agent_cfg = $this->getAgentCfg($user[0]['cfg_data']);
                        $login_pic = $this->agent_cfg['company_logo'];
                        if (!empty($login_pic)) 
                        {
                            session('oem_logo',$login_pic);
                        }
                        
                    }
                }
            }

            $this->assign('is_oem', 1);
        }
        else
        {
            //不是oem用户
            $login_pic = '/images/login_logo.png';
        }
        $this->assign('login_pic',$login_pic);
        $this->display();
    }

    protected function getAgentCfg($cfg_data_str)
    {
        //根据代理商配置的网站信息显示logo,客服qq,公司信息等
        $default_agent_cfg = C('default_agent_info');
        $agent_cfg;
        if (!empty($cfg_data_str)) 
        {
           $agent_cfg = unserialize($cfg_data_str); 
        }
        
        if (empty($agent_cfg['service_tel'])) 
        {
            $agent_cfg['service_tel'] = $default_agent_cfg['service_tel'];
        }

        if (empty($agent_cfg['service_qq'])) 
        {
            $agent_cfg['service_qq'] = $default_agent_cfg['service_qq'];
        }

        if (empty($agent_cfg['service_qq2'])) 
        {
            $agent_cfg['service_qq2'] = $default_agent_cfg['service_qq2'];
        }

        if (empty($agent_cfg['company_name'])) 
        {
            $agent_cfg['company_name'] = $default_agent_cfg['company_name'];
        }

        if (empty($agent_cfg['company_logo'])) 
        {
            $agent_cfg['company_logo'] = $default_agent_cfg['company_logo'];
        }

        if (empty($agent_cfg['company_qrcode_url'])) 
        {
            $agent_cfg['company_qrcode_url'] = $default_agent_cfg['company_qrcode_url'];
        }

        if (empty($agent_cfg['platform_name'])) 
        {
            $agent_cfg['platform_name'] = $default_agent_cfg['platform_name'];
        }

        if (empty($agent_cfg['icp_info'])) 
        {
            $agent_cfg['icp_info'] = $default_agent_cfg['icp_info'];
        }

        return $agent_cfg;
    }

    public function resetpwd()
    {
        if (session('uid')) 
        {
            $pwd = $this->_post('password');
            $re_pwd = $this->_post('repassword');
            if ($pwd!=false || $pwd == $re_pwd)
            {
                $data['password'] = md5($pwd);
                $data['pwd'] = $pwd;

                if (M('Users')->where(array('id'=>$_SESSION['uid']))->save($data))
                {
                    $this->success('密码修改成功！',U('Index/index'));
                } 
                else
                {
                    $this->error('密码修改失败！',U('Index/index'));
                }
            }
            else
            {
                $this->error('密码不能为空!或者两次密码不一致',U('Index/useredit'));
            }
        }
        else
        {
            header("Location:".U('User/Index/login@'.$_SERVER['SERVER_NAME'])); 
            exit;
        }
    }


    public function checklogin()
    {
        $db         = D('Users');
        $username   = $this->_post('username','trim');
        $pwd        = $this->_post('password','trim,md5');

        $where['username'] = $username;
        $where['status']   = 1;
        $user       = $db->where($where)->find();

        if ($user && ($pwd === $user['password'])) 
        {
            session('uid',$user['id']);
            session('uname',$user['username']);

            //更新上次登录时间
            $db->where($where)->setField('lasttime', time());
            $this->success('登录成功',U('Index/index'));
        } 
        else 
        {
            $this->error('帐号密码错误',U('Index/login'));
        }
    }

    public function logout() 
    {
        session(null);
        session_destroy();
        unset($_SESSION);
        $this->success('安全退出成功！', U('User/Index/login'));
    }

    public function register() 
    {        

        C('TOKEN_ON',false);
        $data['username']   = $_POST['username'];
        $data['password']   = $_POST['password'];
        $data['pwd']        = $_POST['password'];
        $data['repassword'] = $_POST['repassword'];
        $data['company']    = $_POST['company'];
        $data['industry']   = $_POST['industry'];
        $data['email']      = $_POST['email'];
        $data['pos']        = $_POST['pos'];
        $data['tel']        = $_POST['tel'];
        $data['name']       = $_POST['name'];
        $data['city']       = $_POST['city'];
        $data['status']     = 1;
        
        $pageData = array();
        $db = D('Users');
        if ($db->create($data)) 
        {
            $id = $db->add();
            $pageData['status'] = 0;
                        
            require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');
            $websiteUserFuncManager = new WebsiteUserFuncManager($id);
            $websiteUserFuncManager->openDefaultFuncGroups();
        } 
        else 
        {
            $pageData['status'] = 1;
            $pageData['error'] = $db->getError();
        }
        echo json_encode($pageData);
    }

    public function purchase() {
        
        
        // 如果当前用户的管理者是代理商，需将行业方案的自行购买去除，仅保留短信充值
        $uid = session('uid');
        $sql = "SELECT agent.role role
                FROM tp_users AS u
                INNER JOIN tp_user AS agent ON u.administrator = agent.id
                WHERE u.id=".$uid;
        $Model = new Model();
        $user = $Model->query($sql);
        
        $merchantPackages = C('MERCHANT_PACKAGES');
        if(!empty($user) && $user[0]['role'] == 12) {
            $tmpPkgs = array();
            foreach($merchantPackages as $k => $pkg) {
                if($pkg['type'] == 2){ // sms
                    $tmpPkgs[$k] = $pkg;
                }
            }
            $this->assign('merchantPackages', $tmpPkgs);
        }else{
            $this->assign('merchantPackages', $merchantPackages);
        }
        
        $orders = M('audit_merchant_purchase')->where(array('user_id'=>$uid))->order('id desc')->select();
        if(!empty($orders) && count($orders) > 0)  {
            $this->assign('orders', $orders);
            $this->assign('last_purchase', $orders[0]);
        }
        
        $this->display();
    }

    public function activate() {
        if(IS_POST){

            $code = trim($_POST['invitecode']);
            $user_id = $_SESSION['uid'];

            $websiteUserFuncManager = new WebsiteUserFuncManager($user_id);
            $res = $websiteUserFuncManager->activate($code, session('uname'));
            if($res['success'] == 1) {
                $this->success("充值成功！", U('Index/index'));
            }else {
                $this->error($res['error']);
            }

        }else {
            $this->display();
        }
    }

    private function generateToken() 
    {
        // token不能包含连续多余10个的数字
        return dechex(time()).'z'.dechex(rand(1,256));
    }

    private function generateCode()
    {
        mt_srand((double) microtime() * 1000000);
        return str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_RIGHT);
    }

}

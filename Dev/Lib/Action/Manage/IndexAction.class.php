<?php

class IndexAction extends Action
{
    
    public function index()
    {
        $this->redirect(U('Manage/Index/signin'));
    }

    public function signin()
    {
        $names = explode('.', $_SERVER['SERVER_NAME']);
        $code = intval($names[0]);
    	if(count($names)==4){
            if(is_numeric($names[0]) && is_numeric($names[1]) && is_numeric($names[2]) && is_numeric($names[3])){
                $code = '';
            }
        }
        if ($code) 
        {
            $this->assign('merchant_code',$code);
        }
        else
        {
            $code = $this->_get('mcode','intval');
        }
        
        if (IS_POST) 
        {
            
            if (!$code) 
            {
                $code = $this->_post('merchant_code','intval');
            }

            if (empty($code)) 
            {
                $this->error('请联系管理员获取商家id。', U('Manage/Index/signin'));
            }
            $wxuser = M('wxuser')->field('token,uid,company,code')->where(array('code'=>$code))->find();
            if (empty($wxuser)) 
            {
                $this->error('请联系管理员获取商家id。', U('Manage/Index/signin'));
            }

            $user_db = M('manage_user');
            $user = $user_db->where(array('user_name'=> $_POST['user_name'], 'status'=>1,'token'=>$wxuser['token']))->find();
            if ($user) 
            {
                #verify password
                if (md5(md5($_POST['user_psw'].$user['lz_salt'])) == $user['password']) 
                {
                    session_destroy();
                    session_start();
                    
                    $_SESSION['manage_user_name']       = $user['user_name'];
                    $_SESSION['manage_merchant']        = $wxuser['company'];
                    $_SESSION['manage_company_token']   = $user['token'];
                    $_SESSION['manage_company_code']    = $wxuser['code'];
                    $_SESSION['manage_dine_branch']     = $user['diningsub'];
                    $_SESSION['manage_hotel_branch']    = $user['hotelsub'];
                    $_SESSION['manage_auto_printalbe']  = $user['printable'];
                    $_SESSION['manage_shop_branch']     = $user['shopsub'];
                    
                    
                    $merchant = M('users')->where(array('id'=>$wxuser['uid']))->find();
                    $uid = $merchant['id'];
                    $cur = time();

                    //获取操作员平台支持的功能
                    $support_functions = C('manage_functions');
                    $support_functions_keys = array_keys($support_functions);

                    $in_stmt_str = array();
                    foreach ($support_functions_keys as $key => $value) 
                    {
                        $in_stmt_str[$key] = "'".$value."'";
                    }
                    $in_stmt = implode(',', $in_stmt_str);

                    //若该功能已经过期，则不显示
                    $sql = 'select f.funname from tp_function as f LEFT JOIN tp_function_group as fg on f.fgid = fg.id LEFT JOIN tp_user_func_group as ufg on ufg.group_id = f.fgid'
                                      . " where ufg.user_id = '$uid' and ufg.expire_time > $cur and f.funname in ($in_stmt);";
                    $Model = new Model();
                    $functions = $Model->query($sql);

                    $avail_func = array();
                    foreach ($functions as $key => $value) 
                    {
                        if ( in_array($value['funname'], $support_functions_keys)) 
                        {
                            array_push($avail_func, $support_functions[$value['funname']]);
                        } 
                    }

                    //根据操作员的权限列表action来显示相应的模块
                    $my_actions = explode(',', $user['action_list']);
                    $my_avai_actions = array();
                    foreach ($my_actions as $action) 
                    {
                        foreach ($avail_func as $func) 
                        {
                            if (strpos($action,$func) !== false)
                            {
                                array_push($my_avai_actions, $action);
                            }
                        }
                    }

                    $action_str = implode(',', $my_avai_actions);
                    $_SESSION['manage_act_list']  = $action_str;

                    $modules = C('manage_modules');

                    if (is_array($my_avai_actions) && count($my_avai_actions) > 0) 
                    {
                        //跳转到功能的默认入口
                        $codes = explode('-', $my_avai_actions[0]);
                        $this->success('登陆成功',$modules[$codes[0]][$codes[1]]['default_entry_url']);
                    }
                    else
                    {
                        $this->error('请联系管理员配置权限。', U('Manage/Index/signin'));
                    }
                }
                else
                {
                    $this->error('密码错误，如忘记密码请联系管理员',U('Manage/Index/signin'));
                }
            }
            
        }
        else
        {
            if ($code) 
            {
                $this->assign('merchant_code',$code);
            }
            $this->display();
        }
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('Manage/Index/signin');
    }

}

<?php

class AdminAction extends UserAction
{
    
    public function index()
    {
       
        $adminstrators = M('manage_user')->where(array('token'=>$this->token,'status'=>1))->select();
        if ($adminstrators) 
        {
            $this->assign('adminstrators', $adminstrators);
        }

        $wxuser_db = M('wxuser');
        $wx_account = $wxuser_db->field('code')->where(array('token'=>$this->token))->find();
        if (empty($wx_account['code'])) 
        {
            $new_code = '';
            $ret = 0;
            for ($i = 0; $i < 10; $i++) 
            {
                    //随机10次生成
                $new_code = $this->generateCode();
                $ret = $wxuser_db->where(array('token'=>$this->token))->save(array('code'=>$new_code)) ;
                if ($ret) 
                {
                    break;
                }
            }

            if (!$ret) {
                $this->error('系统忙，请联系客服。',U(MODULE_NAME.'/index'));
            }
            else
            {
                $this->assign('code', $new_code);
            }
        }
        else
        {
            $this->assign('code', $wx_account['code']);
        }

        $manage_domain = C('manage_domain');
        if (empty($manage_domain)) 
        {
            $this->assign('ori_manage_entry', U('Manage/Index/signin',array('mcode'=>$wx_account['code'])));
        }
        else
        {
            $this->assign('well_manage_entry', 'http://'.$wx_account['code'].'.'.C('manage_domain'));
        }
        $this->display();
    }

    public function admin()
    {
        $admin_db        = M('manage_user');

        if(IS_POST)
        {
            
            $admin_id = $this->_post('aid', 'intval', -1);
            if ($admin_id == -1) 
            {
                //添加新操作员
                $admin_count = $admin_db->where(array('token' => $this->token,'status'=>1))->count();

                if ($admin_count >=3 ) 
                {
                    $this->error('最多只能设置3个管理员，若需要更多的管理员，请联系客服。',U(MODULE_NAME.'/admin'));
                }

                $admin = $admin_db->where(array('user_name'=>$_POST['name'], 'token' => $this->token,'status'=>1))->find();
                if ($admin) 
                {
                    $this->error('已经存在同名的管理员，请重新添加',U(MODULE_NAME.'/admin'));
                }

                if ($_POST['password'] != $_POST['re-password']) 
                {
                    $this->error('两次输入密码不一致',U(MODULE_NAME.'/admin'));
                }

                $data['token']      = $this->token;
                $data['user_name']  = $_POST['name'];
                $data['lz_salt']    = substr(md5(time().$data['user_name']),0,6);
                $data['password']   = md5(md5($_POST['password'].$data['lz_salt']));

                $data['status'] = 1;
                $data['create_time'] = time();
                $data['update_time'] = $data['create_time'];

                $data['action_list'] = implode(',', $_POST['action_code']);

                $data['diningsub']   = $_POST['diningsub'];
                $data['hotelsub']    = $_POST['hotelsub'];
                $data['shopsub']     = $_POST['shopsub'];

                $printable = intval($_POST['printable']);
                $printable = $printable == 0 ? 0 : 1; 
                $data['printable']   = $printable;
                
                $admin_id = $admin_db->add($data);
                if ($admin_id) 
                {
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                } 
                else
                {
                   $this->error('操作失败',U(MODULE_NAME.'/admin'));
                }
            }
            else
            {
                //更新操作员信息
                $where       = array('user_id'=> $admin_id, 'token'=>$this->token,'status'=>1);
                $admin       = $admin_db->where($where)->find();
                if ($admin == false)
                {
                    //找不到也是修改成功
                    Log::write("非法更新分类信息：admin_id:".$admin_id, Log::INFO);
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                }

                //更新
                $data['user_name'] = $_POST['name'];
                if (!empty($_POST['password'])  
                    && !empty($_POST['re-password']) 
                    && $_POST['password'] == $_POST['re-password'] ) 
                {
                    $data['password'] = md5(md5($_POST['password'].$admin['lz_salt'])); 
                }

                $data['action_list'] = implode(',', $_POST['action_code']);
                
                $data['update_time'] = time();
                $data['diningsub']   = $_POST['diningsub'];
                $data['hotelsub']    = $_POST['hotelsub'];
                $data['shopsub']     = $_POST['shopsub'];

                $printable = intval($_POST['printable']);
                $printable = $printable == 0 ? 0 : 1; 
                $data['printable']   = $printable;
				
                $ret = $admin_db->where($where)->save($data);

                if($ret)
                {
                    $this->success('修改成功',U(MODULE_NAME.'/index'));
                }
                else
                {
                    Log::record("更新分类信息失败：user_id:".$admin['user_id'].'; error:'.$admin_db->getError(), Log::INFO);
                    $this->error('操作失败');
                }

            }
        }
        else
        {
            $admin_id = $this->_get('aid','intval', -1);
            $admin = $admin_db->where(array('user_id'=>$admin_id, 'token'=>$this->token,'status'=>1))->find();

            $wxuser_db = M('wxuser');
            $wx_account = $wxuser_db->field('code')->where(array('token'=>$this->token))->find();
            if (empty($wx_account['code'])) 
            {
                $new_code = '';
                $ret = 0;
                for ($i = 0; $i < 10; $i++) 
                {
                    //随机10次生成
                    $new_code = $this->generateCode();
                    $ret = $wxuser_db->where(array('token'=>$this->token))->save(array('code'=>$new_code)) ;
                    if ($ret) 
                    {
                        break;
                    }
                }

                if (!$ret) {
                    $this->error('系统忙，请联系客服。',U(MODULE_NAME.'/index'));
                }
                else
                {
                    $this->assign('code', $new_code);
                }
            }
            else
            {
                $this->assign('code', $wx_account['code']);
            }
            if($admin)
            {
                $this->assign('admin', $admin);
                $my_actions = explode(',',$admin['action_list']);
                $this->assign('my_actions', $my_actions);
            }
            else
            {
                $admin_count = $admin_db->where(array('token' => $this->token,'status'=>1))->count();
                if ($admin_count >=3 ) 
                {
                    $this->error('最多只能设置3个管理员，若需要更多的管理员，请联系客服。',U(MODULE_NAME.'/index'));
                }


            }

            $actions = $this->getAvailableActions();
            $this->assign('all_actions', $actions);
            $this->assign('action_lang', C('manage_action_lang'));
            
			$rests = M('dine_restlist')->where(array('token'=>$this->token, 'status'=>1))->order('orderNum desc')->select();
			$this->assign('rests', $rests);
			
			$hotels = M('Hotel')->where(array('token'=>$this->token, 'status'=>1))->order('sort desc')->select();
			$this->assign('hotels', $hotels);

            $shops = M('b2c_shop')->where(array('token'=>$this->token, 'status'=>1))->select();
            $this->assign('shops', $shops);
			
            $this->display('admin');
        }
    }

    private function generateCode()
    {
        mt_srand((double) microtime() * 1000000);
        return str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_RIGHT);
    }


    public function deleteAdmin() 
    {
        $admin_id = $this->_get('aid', 'intval', -1);
        $admin_db        = M('manage_user');

        $where          = array('user_id'=> $admin_id, 'token'=>$this->token,'status'=>1);

        $data['status']     = 2;
        $data['update_time']= time();

        $ret = $admin_db->where($where)->save($data);

        if($ret)
        {
            $this->success('修改成功',U(MODULE_NAME.'/index'));
        }
        else
        {
            Log::record("更新分类信息失败：user_id:".$user_id.'; error:'.$admin_db->getError(), Log::INFO);
            $this->error('操作失败');
        }

    }


    private function getAvailableActions()
    {
        $uid = session('uid');
        $cur = time();
        $functions = C('manage_functions');
        $functions = array_keys($functions);
        foreach ($functions as $key => $value) {
            $functions[$key] = "'".$functions[$key]."'";
        }

        $in_stmt = implode(',', $functions);
        $sql = 'select f.funname, ufg.expire_time from tp_function as f LEFT JOIN tp_function_group as fg on f.fgid = fg.id LEFT JOIN tp_user_func_group as ufg on ufg.group_id = f.fgid'
                          . " where ufg.user_id = '$uid' and ufg.expire_time > $cur and f.funname in ( $in_stmt );";
        Log::write("admin sql:".$sql);
        $Model = new Model();
        $avail_functions = $Model->query($sql);

        $default_actions = C('manage_actions');

        $actions = array();
        foreach ($avail_functions as $key => $fun) 
        {
           if ($default_actions[$fun['funname']]) 
           {
                $actions[$fun['funname']] = $default_actions[$fun['funname']];
           }
        }

        return $actions;
    }
}

?>

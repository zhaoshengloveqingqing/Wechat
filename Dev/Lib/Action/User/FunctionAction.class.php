<?php
require_once(COMMON_PATH.'/TemplateDataLoader.php');

class FunctionAction extends UserAction{

    function index(){

        $wx_id = $this->_get('id','intval');
        $token = $this->_get('token','trim');  

        $info = M('Wxuser')->find($wx_id);
        if (empty($info)|| $info['token'] !== $token) {
            if(isset($_GET['internal'])) {
               $this->error('请充值后联系客服开通该功能。',U('User/Index/index'));
            }else {
                $this->error('非法操作',U('Home/Index/index'));
            }
        }

        //保存该公众号的token
        session('token',$info['token']);
        session('wxid',$info['id']);

        if (!empty($token)) {
            $wecha = M('Wxuser')->field('wxname,weixin,type')->where(array('token' => $token,'uid' => session('uid')))->find();
            $this->assign('wecha',$wecha);
            $this->assign('token', $token);
        } else {
               $this->redirect('User/Index/index');
        }
        //第一次登陆　创建　功能所有权
        $token_open = M('Token_open')->field('id,queryname')->where(array('token' => $token,'uid' => session('uid')))->find();
        //遍历功能列表
        $opened_func = explode(',',$token_open['queryname']);
        $this->assign('checked_funcs',$opened_func);

        //获取当前用户购买可用的功能组
        $now = time();
        $ufg_where['start_time'] = array('ELT', $now);
        $ufg_where['expire_time'] = array('EGT', $now);
        $ufg_where['status'] = '1';
        $ufg_where['user_id'] =  array('Eq', session('uid'));
        $user_func_groups =  M('user_func_group')->field('group_id, start_time, expire_time')->where($ufg_where)->select();

        //获取当前所有功能
        $funcs = M('Function')->where(array('status'=>1))->select();

        //获取功能组，并将功能分组
        $func_groups = M('function_group')
                ->field('id,name')
                ->where(array('status'=>1, 'visible'=>1))
                ->order('sort asc, id asc')
                ->select();
        foreach($func_groups as $key => $fgroup){
            $func_idx = 0;
            foreach($funcs as $fun) {
               if ($fun['fgid'] == $fgroup['id'] ) {
                   $func_groups[$key]['functions'][$func_idx++] = $fun;
               }
            }
            if (!empty($user_func_groups) && is_array($user_func_groups) ) {
                foreach($user_func_groups as $u_group) {
                    if ($u_group['group_id'] ==  $fgroup['id'] ) {
                        $func_groups[$key]['is_enable'] = 1;
                        $func_groups[$key]['start_time'] = $u_group['start_time'];
                        $func_groups[$key]['expire_time'] = $u_group['expire_time'];
                    }
                }
            }
        }

        /*$Dao = M();
        //或者使用 $Dao = new Model();

       $function = $Dao->query("SELECT tp_function.id,tp_function.`name`,tp_function.gid,tp_function.usenum,tp_function.funname,tp_function.info,
                tp_function.`status`,tp_function.isserve from tp_user_group, tp_function where tp_function.gid = tp_user_group.id");*/
        $this->assign('groups',$func_groups);
        
        // sms
        $smsaccount = M('smsaccount')->where(array('user_id'=>session('uid')))->find();
        if(!empty($smsaccount)) {
            $smsaccount['balance'] = $smsaccount['total'] - $smsaccount['used'];
            $this->assign('smsaccount', $smsaccount);
        }
        
        $this->display();
    }
    /*public function sms() {
        $token = session('token');
        $uid = session('uid');
        $func = $_GET['func'];
        $status = intval($_GET['status']);
        if($status !== 0) {
            $status = 1;
        }
        
        if(strcmp($func, 'shop') == 0 || strcmp($func, 'order') == 0) {
            $smsAccount = M('smsaccount')->where(array('user_id'=>$uid))->find();
            if(empty($smsAccount)) {
                $data = array();
                $data['user_id'] = $uid;
                $data['total'] = 0;
                $data['used'] = 0;
                $data['status'] = 1;
                $data[$func] = $status;
                M('smsaccount')->add($data);
            }else {
               
                $smsAccount[$func] = $status;
                M('smsaccount')->save($smsAccount);
            }
            
            echo 1;
            return;
        }
        
        echo 2;
    }*/

    public function add(){

        $server_token = session('token');

        $func_id = $this->_get('id');
        $fun = $this->queryFunction($func_id, $server_token); 

        if (!empty($fun)) {
            $templateDataLoader = new TemplateDataLoader(session('uid'), session('uname'), session('token'));
            $templateDataLoader->loadTemplateData($fun['funname']) ;
            //该用户已经购买该功能组
            $ret = $this->updateOpenedFuncs('add',  $server_token, $fun['funname']);
            if($ret){
                if (isset($fun['keywords']) && !empty($fun['keywords'])) {
                    $keywords = explode(' ', $fun['keywords'] );
                    $kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['type'] = 2;
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = $fun['funname'];
                    $kwd_data['pid'] = 0;
                    foreach($keywords as $vo) {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
                }
                
                echo 1;
            }else{
                echo 2;
            }
        } else {
            echo 2;
        }
    }

    public function del() 
    {
        $server_token = session('token');
        $func_id = $this->_get('id');

        $fun = $this->queryFunction($func_id, $server_token); 

        if (!empty($fun)) 
        {
            $ret = $this->updateOpenedFuncs('del', $server_token, $fun['funname']);

            if ($ret) {
                if (isset($fun['keywords'])) {
                  $kwds_db = M('keyword')->where(array('function' => $fun['funname'], 'token' => $server_token ))->delete();
                }
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 2;
        }
    }

    private function queryFunction ($func_id , $token) 
    {
        //根据功能id查询该用户对应的功能信息
        $user_id = session('uid');
        if ($func_id && $token) 
        {
            return M('Function')->join('tp_function_group on tp_function.fgid = tp_function_group.id ')->join('tp_user_func_group on tp_user_func_group.group_id = tp_function.fgid')->where(array('tp_function.id' => $func_id, 'tp_user_func_group.user_id' => $user_id))->find();
        }
    }

    private function updateOpenedFuncs($type, $token, $funName) 
    {
        $open_where = array('uid'=>session('uid'), 'token' => session('token'));
        $open = M('Token_open')->where($open_where)->find();
        if ($open == false) 
        {
             M('Token_open')->add(array('uid'=>session('uid'), 'token' => session('token') ));
        }

        if ($type == 'add') 
        {
            $str['queryname'] = str_replace(',,',',',$open['queryname'].','.$funName);        
            $ret = M('Token_open')->where($open_where)->save($str);
            if ($ret) 
            {
                $opened_funcs = explode(',',$str['queryname']);
                session('opened_funcs',$opened_funcs);
            }
            return $ret; 
        } 
        else 
        {
            $str['queryname']=ltrim(str_replace(',,',',',str_replace($funName,'',$open['queryname'])),',');    
            $ret = M('Token_open')->where($open_where)->save($str);
            if ($ret) 
            {
                 $opened_funcs = explode(',',$str['queryname']);
                session('opened_funcs',$opened_funcs);
            }
            return $ret; 
        }
    }

    
}

?>

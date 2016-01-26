<?php
require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');

class UsersAction extends BackAction{
    public function index(){
        $db=D('Users');
        
        $isAdmin = 0;
        if (session(C('ADMIN_AUTH_KEY')) == true) {
            $condition = array();
            $this->assign('isAdmin', 1);
            $isAdmin = 1;
        } else {
            $condition['administrator'] = session(C('USER_AUTH_KEY'));
            $this->assign('isAdmin', 0);
        }
                
        $count= $db->where($condition)->count();
        $Page= new Page($count,100);
        $show= $Page->show();
        $list = $db->where($condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $adminList = M('user')->where('status=1')->field('id,username')->select();
        $this->assign('info',$list);
        $this->assign('page',$show);
        $this->assign('adminList', $adminList);
                
        if($isAdmin == 1) {
            unset($condition);
            $condition['status'] = 1;
            $backendUsers = M('user')->where($condition)->field('id,username')->select();
            $this->assign('backendUsers', $backendUsers);
        }
                
        $this->display();
    }
        
        public function search() {
            if (IS_POST) {
                $searchtype = $_POST['searchtype'];
                $searchparam = trim($_POST['searchparam']);
            } else {
                $searchtype = $_GET['searchtype'];
                $searchparam = trim($_GET['searchparam']);
                if (isset($_GET['p'])) {
                    $p =intval($_GET['p']);
                } else {
                    $p = 1;
                }
            }
                
            $db=D('Users');
    
            $isAdmin = 0;
            if(session(C('ADMIN_AUTH_KEY')) == true) {
                $condition = array();
                $this->assign('isAdmin', 1);
                $isAdmin = 1;
            } else {
                $condition['administrator'] = session(C('USER_AUTH_KEY'));
                $this->assign('isAdmin', 0);
            }
                
            if ($searchtype != 'administrator') {
                $condition[$searchtype] = array('like', '%'.$searchparam.'%');
            } else {
                $condition[$searchtype] = $searchparam;
            }
                     
            $condition['status'] = 1;
            $count= $db->where($condition)->count();
            $Page= new Page($count,100);
            $show= $Page->show();
            $list = $db->where($condition)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        
            $adminList = M('user')->where('status=1')->field('id,username')->select();
            $this->assign('info', $list);
            $this->assign('page', $show);
            $this->assign('adminList', $adminList);
            $this->assign('searchparam', $searchparam);
            $this->assign('searchtype', $searchtype);
                
            if ($isAdmin == 1) {
                unset($condition);
                $condition['status'] = 1;
                $backendUsers = M('user')->where($condition)->field('id,username')->select();
                $this->assign('backendUsers', $backendUsers);
            }
                
            $this->display('index');
        }
        
        public function updatefuncgroup($userid, $gid, $checked) {
            $condition = array();
            $condition['user_id'] = $userid;
            $condition['group_id'] = $gid;
            
            
            if(0 < count(M('user_func_group')->where($condition)->find()))
            {
                if( M('user_func_group')->where($condition)->setField('status', $checked))
                {
                    echo 1;
                }
                else
                {
                    echo 0;
                }
            } 
            else 
            {
                $data['user_id'] = $userid;
                $data['group_id'] = $gid;
                $data['status'] = $checked;
                if(M('user_func_group')->add($data))
                {
                    echo 1;
                }
                else
                {
                    echo 0;
                }
            }
        }

        public function updatefgtime($userid, $gid, $expiretime) {
           
            $condition = array();
            $condition['user_id'] = $userid;
            $condition['group_id'] = $gid;
            
            $data = M('user_func_group')->where($condition)->find();
            if(empty($data)) {
                echo 1; // 失败
                exit;
            }
            
            
            $newtime  = strtotime($expiretime);
            if($newtime > $data['expire_time']) {
                echo 2; // 太大
                exit;
            }
            if($newtime < $data['start_time']) {
                echo 3; // 太小
                exit;
            }
            
            $ret = M('user_func_group')->where($condition)->setField('expire_time', $newtime);
            if($ret === FALSE) {
                echo 1;
                exit;
            }
            echo 0;
            exit;
        }
        
    // 添加用户
        public function add() {
        $UserDB = D("Users");
        if(isset($_POST['dosubmit'])) {
          
            $password = $_POST['password'];
            $repassword = $_POST['repassword'];
            if(empty($password) || empty($repassword)){
                $this->error('密码必须填写！');
            }
            if($password != $repassword){
                $this->error('两次输入密码不一致！');
            }
            //根据表单提交的POST数据创建数据对象
            //$_POST['viptime']=strtotime($_POST['viptime']);
            $_POST['pwd'] = $_POST['password'];
            //$_POST['assign_time'] = time();
            if($UserDB->create()){
                $user_id = $UserDB->add();
                if($user_id){
                        //为用户开启缺省权限
                        require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');
                        $websiteUserFuncManager = new WebsiteUserFuncManager($user_id);
                        $websiteUserFuncManager->openDefaultFuncGroups();
                        
                    $this->success('添加成功！',U('Admin/Users/index'));                    
                }else{
                     $this->error('添加失败!');
                }
            }else{
                $this->error($UserDB->getError());
            }
        }else{
            $role = M('User_group')->field('id,name')->where('status = 1')->select();
            $this->assign('role',$role);
            $this->assign('tpltitle','添加');
            $this->display();
        }
    }
    
    public function activate() {
        $condition = array();
        if(IS_POST && isset($_POST['activateSubmit'])){
            $code = $_POST['invitecode'];
            $user_id = $_POST['userid'];
           
            $websiteUserFuncManager = new WebsiteUserFuncManager($user_id);
            $res = $websiteUserFuncManager->activate($code, current(M('user')->field('username')->find(session(C('USER_AUTH_KEY')))));
     
            if($res['success'] == 1) {
                $this->success("充值成功！");
            }else {
                $this->error($res['error']);
            }
           
        }
        else {
            $id = $this->_get('id','intval',-1);
            if (!$id) $this->error('参数错误!');
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $fgroups = $Model->query("SELECT  tp_function_group.`name` ,tp_function_group.visible visible,tp_function_group.id id, tp_user_func_group.id relationid, tp_user_func_group.status status, tp_user_func_group.start_time, tp_user_func_group.expire_time from tp_function_group LEFT OUTER JOIN tp_user_func_group  on tp_function_group.id = tp_user_func_group.group_id and tp_function_group.status =1 and tp_user_func_group.user_id=".$id
                    ." order by tp_function_group.sort asc, tp_function_group.id asc");
            $this->assign('func_groups', $fgroups);
            
            $this->assign('userid', $id);
            $this->assign('tpltitle','授权');
            
            $smsAccount = M('smsaccount')->where(array('user_id'=>$id))->find();
            if(!empty($smsAccount)) {
                $smsAccount['balance'] = $smsAccount['total'] - $smsAccount['used'];
                $this->assign('smsaccount', $smsAccount);
            }
            $this->display();
        }
    }
    // 编辑用户
    public function edit(){
        $UserDB = D("Users");
        if(isset($_POST['dosubmit'])) {
            $user_id = $this->_post('id','intval',-1);
            $users = M('Users')->find($user_id);
            if ($users === false) {
                $this->error('编辑失败!');
            }
            
               
            if((!empty($users['administrator']) && $users['administrator'] != $_POST['administrator']) || (empty($users['administrator']) && $_POST['administrator']!='8')) {
                $_POST['assign_time'] = time();
            }
            $password = $this->_post('password','trim',0);
            $repassword = $this->_post('repassword','trim',0);
            
            if($password != $repassword){
                $this->error('两次输入密码不一致！');
            }

            if($password==false){ 
                unset($_POST['password']);
                unset($_POST['repassword']);
            }else{
                $_POST['pwd'] = $_POST['password'];
                $_POST['password'] = md5($password);
            }
            unset($_POST['dosubmit']);
            unset($_POST['__hash__']);
            //$_POST['viptime']=strtotime($_POST['viptime']);
            if($UserDB->save($_POST) !== false){
                
                $this->success('编辑成功！',U('Admin/Users/index'));
            }else{
                $this->error('编辑失败!');
            }
            
        }else{
            $id = $this->_get('id','intval',-1);
            if (!$id) $this->error('参数错误!');
            $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
            $fgroups = $Model->query("SELECT  tp_function_group.`name` ,tp_function_group.id, start_time, expire_time from tp_function_group LEFT OUTER JOIN tp_user_func_group  on tp_function_group.id = tp_user_func_group.group_id and tp_function_group.status =1 and tp_user_func_group.user_id=".$id);
            $this->assign('func_groups', $fgroups);
            
            $info = $UserDB->find($id);
            $this->assign('tpltitle','编辑');
            
            $condition = array();
            $condition['status'] = 1;
            //$condition['username'] = array('neq', 'admin');
            $backendUsers = M('user')->where($condition)->field('id,username')->select();
            if(session(C('ADMIN_AUTH_KEY')) == true) {
                $this->assign("isAdmin", 1);
            }else {
                $this->assign("isAdmin", 0);
            }
                
            $this->assign('backendUsers', $backendUsers);
            //$this->assign('role',$role);
            $this->assign('info',$info);
            $this->display('add');
        }
    }
    
    public function addfc(){
        $token_open=M('Token_open');
        $open['uid']=session('uid');
        $open['token']=$_POST['token'];
        $gid=session('gid');
        $fun=M('Function')->field('funname,gid,isserve')->where('`gid` <= '.$gid)->select();
        foreach($fun as $key=>$vo){
            $queryname.=$vo['funname'].',';
        }
        $open['queryname']=rtrim($queryname,',');
        $token_open->data($open)->add();
    }
    
    //删除用户
    public function del()
    {
        $id = $this->_get('id','intval',0);
        if(!$id) $this->error('参数错误!');
        $UserDB = D('Users');
        $where = array('id' => $id, 'status' => 1);
        $where['administrator'] = session(C('USER_AUTH_KEY'));
        
        $ret = $UserDB->where($where)->save(array('status'=>2));
        if($ret)
        {
            $this->assign("jumpUrl");
            $this->success('删除成功！');            
        }
        else
        {
            $this->error('删除失败!');
        }
    }
}

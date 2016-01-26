<?php
class UserAction extends BackAction{
	public function _initialize() {
        parent::_initialize();  //RBAC 验证接口初始化
    }
	public function index()
    {
        $role = M('Role')->getField('id,name');
        $map = array();
        $UserDB = D('User');
        $count = $UserDB->where($map)->count();
        $Page       = new Page($count, 50);// 实例化分页类 传入总记录数
        // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        $nowPage = isset($_GET['p'])?$_GET['p']:1;
        $show       = $Page->show();// 分页显示输出
        $list = $UserDB->where($map)->order('id ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('role',$role);
        $this->assign('list',$list);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();
    }

    // 添加用户
    public function add(){
        $UserDB = D("User");
        if(isset($_POST['dosubmit'])) {
            $password = $_POST['password'];
            $repassword = $_POST['repassword'];
            if(empty($password) || empty($repassword)){
                $this->error('密码必须填写！');
            }
            if($password != $repassword){
                $this->error('两次输入密码不一致！');
            }
            
            // set package_price field
            $packagePrices = array();
            $priceIds = $_POST['price_idlist'];
            $prices = $_POST['pricelist'];
            for($i=0; $i<count($priceIds); $i ++) {
                $packagePrices[$priceIds[$i]] = $prices[$i];
            }
            $_POST['package_price'] = serialize($packagePrices);
            
            //根据表单提交的POST数据创建数据对象
            if($UserDB->create()){
                $user_id = $UserDB->add();
                if($user_id){
                    $data['user_id'] = $user_id;
                    $data['role_id'] = $_POST['role'];
                    if (M("role_user")->data($data)->add()){
                        $this->assign("jumpUrl",U('Admin/User/index'));
                        $this->success('添加成功！');
                    }else{
                        $this->error('用户添加成功,但角色对应关系添加失败!');
                    }
                }else{
                     $this->error('添加失败!');
                }
            }else{
                $this->error($UserDB->getError());
            }
        }else{
            // id DESC: make Proxy selected in role dropdownlist
            $role = M('Role')->field('id,name')->where(' id != 5 and status =1 ')->select();
            //$role = D('Role')->getAllRole(array('status'=>1),'id DESC');
            $this->assign('role',$role);
            $this->assign('tpltitle','添加');
            
            $agentPackages = C('AGENT_PACKAGES');
            $this->assign('agentPackages', $agentPackages);
            $this->display();
        }
    }

    // 编辑用户
    public function edit(){
        $UserDB = D("User");
        if(isset($_POST['dosubmit'])) {
            $password   = $_POST['password'];
            $repassword = $_POST['repassword'];
            if(!empty($password) || !empty($repassword)){
                if($password != $repassword){
                    $this->error('两次输入密码不一致！');
                }
                $_POST['password'] = md5($password);
            }
            if(empty($password) && empty($repassword)) 
                unset($_POST['password']);   //不填写密码不修改
            
            // set package_price field
            $packagePrices = array();
            $priceIds = $_POST['price_idlist'];
            $prices = $_POST['pricelist'];
            for($i=0; $i<count($priceIds); $i ++) {
                $packagePrices[$priceIds[$i]] = $prices[$i];
            }
            $_POST['package_price'] = serialize($packagePrices);

            
            $audit_data = array();
            $user = $UserDB->where(array('id'=>$_POST['id']))->find();
            if ($user != false) 
            {
                $audit_data['balance_before']   = $user['balance'];
                $audit_data['agent_id']         = $user['id'];
                $audit_data['agent_name']       = $user['username'];
                $audit_data['balance_after']    = $_POST['balance'];
                $audit_data['admin_name'] = $user_name = session('username');
                $audit_data['create_time'] = time();
                //根据表单提交的POST数据创建数据对象
                if ($UserDB->create())
                {
                    if($UserDB->save())
                    {
                        //插入审计记录
                        M("audit_agent_balance")->add($audit_data);

                        //改变角色
                        $where['user_id'] = $_POST['id'];
                        $data['role_id']  = $_POST['role'];
                        M("role_user")->where($where)->save($data);
                        $this->assign("jumpUrl",U('Admin/User/index'));
                        $this->success('编辑成功！');
                    }
                    else
                    {
                         $this->error('编辑失败!');
                    }
                }
                else
                {
                    $this->error($UserDB->getError());
                }
            }

            
            
        }else{
            $id = $this->_get('id','intval',0);
            if(!$id)$this->error('参数错误!');
            $info = $UserDB->getUser(array('id'=>$id));
            $this->assign('tpltitle','编辑');
            $role = M('Role')->field('id,name')->where(array('status'=>1))->select();
            $this->assign('role',$role);
            $this->assign('info',$info);
            
            $agentPackages = C('AGENT_PACKAGES');
            $packagePrices = unserialize($info['package_price']);
            foreach ($agentPackages as &$package) {
                if(array_key_exists($package['id'], $packagePrices)) {
                    $package['price_month'] = $packagePrices[$package['id']];
                }
            }
                    
            $this->assign('agentPackages', $agentPackages);
            
            $this->display('add');
        }
    }

    //ajax 验证用户名
    public function check_username(){
        $userid = $this->_get('userid');
        $username = $this->_get('username');
        if(D("User")->check_name($username,$userid)){
            echo 1;
        }else{
            echo 0;
        }
    }

    //删除用户
    public function del(){
        $id = $this->_get('id','intval',0);
        if(!$id)$this->error('参数错误!');
        $UserDB = D('User');
        $info = $UserDB->getUser(array('id'=>$id));
        if($info['username']==C('SPECIAL_USER')){     //无视系统权限的那个用户不能删除
           $this->error('禁止删除此用户!');
        }
        if($UserDB->delUser('id='.$id)){
            if(M("RoleUser")->where('user_id='.$id)->delete()){
                $this->assign("jumpUrl",U('Admin/User/index'));
                $this->success('删除成功！');
            }else{
                $this->error('用户成功,但角色对应关系删除失败!');
            }
        }else{
            $this->error('删除失败!');
        }
    }
}
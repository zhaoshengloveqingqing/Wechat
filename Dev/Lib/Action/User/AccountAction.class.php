<?php
require_once(COMMON_PATH.'/WeixinWebsiteSimulator.php');
require_once(COMMON_PATH.'/PublicAccountFuncManager.php');

class AccountAction extends UserAction
{
    //公众帐号管理类，绑定、添加、编辑、删除公众号
    //公众号列表
    public function index()
    {
        $where['uid'] = session('uid');
        $where['status'] = 1;
        
        $db = M('Wxuser');

        $count = $db->where($where)->count();
        //$page = new Page($count,25);
        //$this->assign('page',$page->show());
        $this->assign('public_count',$count);

        $info = $db->where($where)->select();
        $this->assign('info',$info);
                
        $this->display();
    }
    //添加公众帐号
    public function add()
    {
        $public_db = M('Wxuser');
        $public_count = $public_db->where(array('uid' => $uid, 'status'=>1))->count();
        if ($public_count >=1) 
        {
            $this->error('您已经添加过一个公众号了。',U(MODULE_NAME.'/index'));
        } 
        else
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
                $wx_user  = $public_db->where("token = '$newToken' or code=$code")->find();
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
                    $publicAccountId = $public_db->add($data);
                }
                $this->$this->assign('token',$newToken);
            }
            $this->display('edit');
        }
    }

    public function edit()
    {

        $id=$this->_get('id','intval');
        $where['uid']=session('uid');
        $res=M('Wxuser')->where($where)->find($id);
        $this->assign('info',$res);
        $this->display();
    }
    
    public function del()
    {
        $data['status']     = 2; //
        $data['updatetime'] = time();
        $where['id']        = $this->_get('id','intval');
        $where['uid']       = session('uid');
        if (D('Wxuser')->where($where)->save($data))
        {
            $this->success('操作成功',U('Index/index'));
        }
        else
        {
            $this->error('操作失败',U('Index/index'));
        }
    }
    
    public function upsave()
    {
        if(IS_POST)
        {
            $wxUsersId = $_GET['id'];
            if(empty($wxUsersId)) 
            {
                $this->success('修改成功!');
            }
            else 
            {
                $data['type']       = $_POST['type'];
                $data['appid']      = $_POST['appid'];
                $data['appsecret']  = $_POST['appsecret'];
                $data['is_authed']  = $_POST['is_authed'];
                $data['qrcode_pic'] = $_POST['qrcode_pic'];

                $res = M('wxuser')->where(array('uid'=>session('uid'), 'id'=>$wxUsersId))->save($data);
                if($res !== FALSE)
                {
                    $this->success('修改成功');
                }
                else
                {
                    $this->error('修改失败，请刷新重试！');
                }
            }
        }
    }
        
    private function generateToken() 
    {
        // token不能包含连续多余10个的数字
        return dechex(time()).'z'.dechex(rand(1,256));
    }
    
    public function addNewPublic()
    {
        $public_db = M('Wxuser');
        $uid = session('uid');
        $public = $public_db->where(array('uid' => $uid,'status'=>1))->find();
        if ($public != false) 
        {
            $this->error('您当前已经存在一个活跃的公众号，请先将其删除再尝试添加。',U(MODULE_NAME.'/index'));
        } 

        
        $wx_name    = $_POST['wxname'];
        $wx_id      = $_POST['weixin'];
        $wx_ori     = $_POST['wxid'];
        $wx_type    = $_POST['type'];
        $wx_appid   = $_POST['appid'];

        $wx_appsecret    = $_POST['appsecret'];
        $wx_qrcode  = $_POST['qrcode_pic'];

        //已经有token
        $now = time();
        $data['wxname']     = $wx_name;
        $data['wxid']       = $wx_ori;
        //目前token跟微信id是同一个
        $data['weixin']     = $wx_id;
        $data['type']       = $wx_type;
        $data['headerpic']  = C('site_url').'/themes/a/images/logo.jpg';
        $data['qrcode_pic'] = $wx_qrcode;
        $data['createtime'] = $now;
        $data['updatetime'] = $now;
                
        $data['appid']      = $wx_appid;
        $data['appsecret']  = $wx_appsecret;
        
        $public = $public_db->where(array('uid' => $uid,'status'=>0))->find();    
        if ($public != false) 
        {
            
            $data['status'] = 1;
            $ret = $public_db->where(array('id'=>$public['id']))->save($data);
            if ($ret) 
            {
                // 根据用户功能组权限，自动为公共账号开通功能项
                $publicAccountFuncManager = new PublicAccountFuncManager($uid, session('uname'), $public['token']);
                $publicAccountFuncManager->openFunctions();
                $this->success('操作成功',U(MODULE_NAME.'/index'));
            } 
            else 
            {
                $this->success('添加失败，请联系客服',U(MODULE_NAME.'/index'));
            }
        }
        else
        {
            $newToken = $this->generateToken();
            $code      = $this->generateCode();
            $wx_user = $public_db->where("token = '$newToken' or code=$code")->find();
            if ($wx_user) 
            {
                // 理论上不会到该分支，因为token的生成算法已经保证了全局唯一
                $this->error('抱歉，添加公众号失败，请刷新重试。',U(MODULE_NAME.'/add'));
            } 
            else 
            {

                $data['uid']        = session('uid');
                //目前token跟微信id是同一个
                $data['weixin']     = $newToken;
                $data['token']      = $newToken; //str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT)
                $data['code']       = $code;
                $data['status']     = 1;
                
                //初始化空微信号
                $publicAccountId = $public_db->add($data);
                if ($publicAccountId) 
                {
                    // 根据用户功能组权限，自动为公共账号开通功能项
                    $publicAccountFuncManager = new PublicAccountFuncManager($uid, session('uname'), $newToken);
                    $publicAccountFuncManager->openFunctions();
                    $this->success('操作成功',U(MODULE_NAME.'/index'));
                } 
                else 
                {
                    $this->success('添加失败，请联系客服',U(MODULE_NAME.'/index'));
                }
            }
        }

    }
    

    private function generateCode()
    {
        mt_srand((double) microtime() * 1000000);
        return str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_RIGHT);
    }
}

?>

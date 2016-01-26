<?php
class WifiConfigAction extends BackAction
{
	public function _initialize() {
        parent::_initialize();  //RBAC 验证接口初始化
    }


	public function index()
    {

        $admin_id = session(C('USER_AUTH_KEY'));

        $config = M('wifi_config')->where(array('admin_id'=>$admin_id))->find();
        if ($config) 
        {
            $this->assign('config', $config);
        }

        $this->display();
    }

    public function set()
    {

        $app_key    = trim($_POST['app_key']);
        $app_secret = trim($_POST['app_secret']);

        if(empty($app_key) || empty($app_secret))
        {
            $this->error('两个参数都是必填的');
        }

        $admin_id = session(C('USER_AUTH_KEY'));

        $config = M('wifi_config')->where(array('admin_id'=>$admin_id))->find();
        $ret = 0;
        if ($config) 
        {
            $ret = M('wifi_config')->where(array('admin_id'=>$admin_id))->save(array('app_key'=>$app_key, 'app_secret'=>$app_secret));
        }
        else
        {
            $ret = M('wifi_config')->data(array('admin_id'=>$admin_id,'app_key'=>$app_key, 'app_secret'=>$app_secret))->add();
        }

        if ($ret) 
        {
            $this->success('保存成功',U(MODULE_NAME.'/index'));
        }
        else
        {
            $this->error('保存失败，请联系客服',U(MODULE_NAME.'/index'));
        }

    }

    
}
<?php
class OEMConfigAction extends BackAction
{
	public function _initialize() {
        parent::_initialize();  //RBAC 验证接口初始化
    }


	public function index()
    {

        $admin_id = session(C('USER_AUTH_KEY'));

        $config = M('oem_cfg')->where(array('agent_id'=>$admin_id))->find();
        if ($config && isset($config['cfg_data'])) 
        {
            $cfg_data = unserialize($config['cfg_data']);
            $this->assign('config', $cfg_data);
        }

        $this->display();
    }

    public function set()
    {
        $oem_cfg = array(
            'service_tel'   => '4000806930',
            'service_qq'    => '2623187081',
            'service_qq2'   => '2324727187',
            'company_name'  => '深圳市领众时代科技传媒有限公司',
            'company_short_name'  => '领众科技',
            'company_logo'  => '/themes/a/images/logo.png',
            'company_qrcode_url' => '/images/qr_code.jpg',
            'platform_name' => '领众微信营销系统',
            'icp_info'      => '粤ICP备14002402号-2',
            );

        $oem_cfg['service_tel']     = $this->_post('service_tel','trim');
        $oem_cfg['service_qq']      = $this->_post('service_qq','intval');
        $oem_cfg['service_qq2']     = $this->_post('service_qq2','intval');
        $oem_cfg['company_name']    = trim($_POST['company_name']);
        
        $oem_cfg['company_logo']    = trim($_POST['company_logo']);
        $oem_cfg['company_qrcode_url']    = trim($_POST['company_qrcode_url']);
        $oem_cfg['platform_name']   = trim($_POST['platform_name']);
        $oem_cfg['icp_info']        = trim($_POST['icp_info']);
        $oem_cfg['copyright_link']  = trim($_POST['copyright_link']);

        $oem_cfg['company_short_name']    = trim($_POST['company_short_name']);
        if (empty($oem_cfg['company_short_name'])) 
        {
            $oem_cfg['company_short_name'] = $oem_cfg['company_name'];
        }


        $oem_cfg_str = serialize($oem_cfg);

        $admin_id = session(C('USER_AUTH_KEY'));

        $config = M('oem_cfg')->where(array('agent_id'=>$admin_id))->find();
        $ret = 0;
        if ($config) 
        {
            $ret = M('oem_cfg')->where(array('agent_id'=>$admin_id))->save(array('cfg_data'=>$oem_cfg_str));
        }
        else
        {
            $ret = M('oem_cfg')->data(array('agent_id'=>$admin_id,'cfg_data'=>$oem_cfg_str, 'domain'=> $_SERVER['SERVER_NAME']))->add();
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
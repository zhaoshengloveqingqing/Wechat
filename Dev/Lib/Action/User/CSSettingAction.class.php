<?php
/**
 * 多客服设置
**/
class CSSettingAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'kefu';
    }

    public function index()
    {
        $db = M('cs_setting');
        $where['token'] = $this->token;

        $setting = $db->where($where)->find(); 
        $this->assign('setting',$setting);

        $this->display();
    }

    public function set()
    {
        
        $data['keyword'] = trim($_POST['keyword']);
        $data['type']    = $this->_post('type','intval');

        $where['token'] = $this->token;

        $setting_db = M('cs_setting');
        $setting = $setting_db->where($where)->find(); 
        if ($setting != false) 
        {
            $ret = $setting_db->where($where)->save($data);
        }
        else
        {
            $data['token'] = $this->token;
            $ret = $setting_db->add($data);
        }

        if ($ret == true) 
        {   
            $kwds_db = M('keyword');
            $kwds_db->where(array('token'=>$this->token ,'function'=>$this->function,'module'=>'transfer'))->delete();

            $setting = $setting_db->where($where)->find();
            if ($setting['type'] == 2 && !empty($setting['keyword'])) 
            {
                $kwd_data['uid']      = session('uid');
                $kwd_data['token']    = $this->token;
                $kwd_data['module']   = 'transfer';
                $kwd_data['type']     = 1;
                $kwd_data['function'] = $this->function;
                $kwd_data['pid']      = $setting['setting_id'];
                $kwd_data['keyword']  = $setting['keyword'];
                $kwds_db->add($kwd_data);
            }
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } 
        else 
        {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
        }
        
    }
}
?>

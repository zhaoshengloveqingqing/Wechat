<?php
class ManageAction extends Action
{

    protected $user_name;
    protected $token;

    protected function _initialize()
    {
        //检查是否登陆，初始化操作员名称和商户token等信息，初始化菜单栏
        if (isset($_POST['param1']))
        // hack for uploadify flash 302 error without session-id
        {  
            $sid = base64_decode($_POST['param1']);
            if (session_id() != $sid) 
            //destroy the automatical generated one or it won't be resumed to old one;
            {
                session_destroy(); 
            }
            session_id($sid);
            session_start();
        }


        //未登录用户
        if (session('manage_user_name') == false 
            || session('manage_company_token') == false ) 
        {
            $this->redirect('Manage/Index/signin');
        } 
        else
        {
            $this->user_name = session('manage_user_name');
            $this->token     = session('manage_company_token');
        }

        //get the sidebar according to the user's action list
        $this->assign('sideMenus',$this->getSideMenu());
        $this->assign('menuLang', C('manage_menu_lang'));

        $this->assign('page_title', session('manage_merchant'));
    }

    protected function getSideMenu() 
    {
        Log::record("act list:".print_r(session('manage_act_list'),true),Log::INFO);
        $action_list = explode(',', session('manage_act_list'));
        $sideMenu = array();

        // 功能 =》 模块 =》 模块入口
        $modules = C('manage_modules');

        foreach ($action_list as $key => $value) 
        {
            $codes = explode('-', $value);
            if ($codes && count($codes) == 2) 
            {
               if (empty($sideMenu[$codes[0]])){
                   $sideMenu[$codes[0]] = array();
               }                
                $sideMenu[$codes[0]][$codes[1]]['default_entry_url'] = $modules[$codes[0]][$codes[1]]['default_entry_url'];                
                $sideMenu[$codes[0]][$codes[1]]['selected']=  (MODULE_NAME == $codes[0])?(in_array(ACTION_NAME, $modules[$codes[0]][$codes[1]]['active_action_set'])?1:0):0;         
                        
                 }
        }
                return $sideMenu;               
    }

    protected function checkAction($action)
    {
        $action_list = explode(',', session('manage_act_list'));
        if (!in_array($action, $action_list)) 
        {
            $this->error('请联系管理员开通权限',U('Manage/Index/signin'));
        }
    }

}    
<?php
/**
 *文本回复
**/
class TmplsAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        parent::checkOpenedFunction('shouye');
    }

    public function index()
    {
        $db  			= D('vweb_setting');
        $where['token'] = session('token');
        $info = $db->where($where)->find();
        $this->assign('info',$info);
                
        $webpageTmplUsage = M('user_func_group')
        					->where(array(
                            	'user_id'=>session('uid'), 
                            	'status'=>1, 
                            	'group_id'=>C('WEBPAGE_TEMPLATE_FG_ID')
                            	))
                        	->find();
        
        if($webpageTmplUsage && $webpageTmplUsage['expire_time'] > time()) 
        {
            $this->assign('canSelectTemplate', 1);
        }
        else 
        {
            $this->assign('canSelectTemplate', 0);
        }

        $web_tmpls   = C('web_homepage_tmpl');
        $web_tmpl_setting = array();

        foreach ($web_tmpls as $key => $tmpl) 
        {
            if (isset($tmpl['exlusive']) && !in_array($this->token,$tmpl['exlusive'])
                || $tmpl['enabled'] != 1) 
            {
                //如果是独家且不是独家用户 则跳过，
                //如果不能用则不显示
                unset($web_tmpls[$key]);
                continue;
            }
            $web_tmpl_setting[$key]['support_diy_bg_color']         = empty($tmpl['support_diy_bg_color']) ? 0 : $tmpl['support_diy_bg_color'];
            $web_tmpl_setting[$key]['default_bg_color']             = empty($tmpl['default_bg_color']) ? '#fff' : $tmpl['default_bg_color'];
            $web_tmpl_setting[$key]['support_diy_classify_color']   = empty($tmpl['support_diy_classify_color']) ? 0 : $tmpl['support_diy_classify_color'];
            $web_tmpl_setting[$key]['default_classify_bg_color']    = empty($tmpl['default_classify_bg_color']) ? '#fff' : $tmpl['default_classify_bg_color'];
            $web_tmpl_setting[$key]['default_classify_font_color']  = empty($tmpl['default_classify_font_color']) ? '#000' : $tmpl['default_classify_font_color'];
        }
        $this->assign('tmpl_diy_setting', json_encode($web_tmpl_setting));
                
        $this->assign('homepagetmpls', $web_tmpls);
        $this->display();
    }

    public function add()
    {
        $tmplId     = $this->_post('optype', 'intval');
        $allTmpls   = C('web_homepage_tmpl');
        if(isset($allTmpls[$tmplId])) 
        {
            if ($allTmpls[$tmplId]['free'] == 0) 
            {
                //检查是否已购买相应的功能
                $cur = time();
                $uid = session('uid');
                $sql = 'select * from tp_users as u left JOIN tp_user_func_group as g on u.id = g.user_id' 
                         ." where u.id = $uid and g.group_id=14 and g.expire_time > $cur and g.start_time < $cur";

                $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
                $opened_func = $Model->query($sql);
                if ($opened_func == false) 
                {
                    $this->ajaxReturn("请联系客服购买“微网站模板充值码”", "Failed", 4);
                }
            }
            else if (isset($allTmpls[$tmplId]['exlusive']) && !in_array($this->token,$allTmpls[$tmplId]['exlusive']
                || $allTmpls[$tmplId]['enabled'] != 1)) 
            {
                $this->ajaxReturn("该模板不可用", "Failed", 5);
            }
            $data['tmpl_id']    = $allTmpls[$tmplId]['id'];
            $data['tmpl_name']  = $allTmpls[$tmplId]['view'];
			$data['show_nav']  = $this->_post('show_nav');
            $data['update_time']  = time();

            /*if (isset($allTmpls[$tmplId]['support_diy_bg_pic']) 
                && $allTmpls[$tmplId]['support_diy_bg_pic'] == 1) 
            { 
                $data['bg_pic_url'] = $this->_post('bg_pic_url','trim');
            }*/

            if (isset($allTmpls[$tmplId]['support_diy_bg_color']) 
                && $allTmpls[$tmplId]['support_diy_bg_color'] == 1 )  
            {
                $data['bg_color']   = $this->_post('bg_color','trim');
            }

            if (isset($allTmpls[$tmplId]['support_diy_classify_color']) 
                && $allTmpls[$tmplId]['support_diy_classify_color'] == 1 )  
            {
                $data['classify_bg_color']   = $this->_post('classify_bg_color','trim');
                $data['classify_font_color']   = $this->_post('classify_font_color','trim');
            }
            $data['navi_bg_color']   = $this->_post('navi_bg_color','trim');   
            if ($_SESSION['token'] != 'lingzhtech' &&  $data['tmpl_name'] =='index_lingzhtech') 
            {
                exit;
            }

            $db = M('vweb_setting');

            $where['token'] = $this->token;
            $tmpl = $db->where($where)->find();

            $ret = 0;
            if ($tmpl != false) 
            {
                $ret = $db->where($where)->save($data);
            }
            else
            {
                $data['token'] = $this->token;
                $ret = $db->add($data);
            }

            if ($ret) {
                $this->ajaxReturn("更新成功", "OK", 1);
            } else  {
                $this->ajaxReturn("更新失败", "ERROR", 0);
            }
        }
    }
}
?>

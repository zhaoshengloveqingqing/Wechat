<?php 

class BackAction extends Action 
{
    protected function _initialize()
    {
        /*if (!session(C('ADMIN_AUTH_KEY'))) {
            $this->error('请重新登录',U('Admin/index'));
        } */
        define('RES',THEME_PATH.'common');
        define('STATICS',TMPL_PATH.'static');
        //Input::noGPC();

        $is_passed = true;
        if (RBAC::checkAccess()) 
        {
            $is_passed = RBAC::AccessDecision();
        }

        if ($is_passed == false) 
        {
            echo('不允许查看');
            exit;

        }
        $this->assign('action',$this->getActionName());
    }

    protected function all_insert($name='',$back='/index')
    {
        $name = $name?$name:MODULE_NAME;
        $db = D($name);
        if ($db->create() === false)
        {
            $this->error($db->getError());
        }
        else
        {
            $id = $db->add();
            if ($id)
            {
                $this->success('操作成功',U(MODULE_NAME.$back));
            }
            else
            {
                $this->error('操作失败',U(MODULE_NAME.$back));
            }
        }
    }

    //修改所有内容,包含关键词
    protected function all_save($name='',$back='/index')
    {
        $name = $name ? $name : MODULE_NAME;
        $db = D($name);
        if ($db->create()===false)
        {
            $this->error($db->getError());
        }
        else
        {
            $id=$db->save();
            if ($id) 
            {
                $this->success('操作成功',U(MODULE_NAME.$back));
            }
            else 
            {
                $this->error('操作失败',U(MODULE_NAME.$back));
            }
        }
    }

    protected function all_del ($id,$name='',$back='/index') 
    {
        $name = $name?$name:MODULE_NAME;
        $db = D($name);
        if ($db->delete($id)) 
        {
            $this->ajaxReturn('操作成功',U(MODULE_NAME.$back));
        }
        else
        {
            $this->ajaxReturn('操作失败',U(MODULE_NAME.$back));
        }
    }
}
 

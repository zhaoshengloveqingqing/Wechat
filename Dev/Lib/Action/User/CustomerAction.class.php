<?php

class CustomerAction extends UserAction
{
    //公众帐号列表
    public function index()
    {

        $info = M('Wxuser')->where(array('token'=>$this->token))->find();
        if(!empty($info['picture']) ) {
            $picRecord = M('raw_image')->find(intval($info['picture']));
            $info['raw_name'] = $picRecord['raw_name'];
           
        }else{
            $info['raw_name'] = '';
        }
        $this->assign('info', $info);
        $this->display();
    }
    
    public function upsave() {
        M('Wxuser')->save($_POST);
        $this->success('保存成功');
    }
    
    public function branchstore() {
        $contact=M('Member_card_contact')->where(array('token'=>$_SESSION['token']))->order('sort asc')->select();
        $this->assign('contact',$contact);
        $this->display();
    }
    public function branch_add() {
        if(IS_POST) {
            $this->all_insert('Member_card_contact', '/branchstore');
        }else {
            $this->display();
        }
    }
    
    public function branch_edit(){
            if(IS_POST){			
                $_POST['token'] = session('token');
                $desc = $_POST['description'];
                $this->all_save('Member_card_contact','/branchstore');
            }else{
                $branchId = $_GET['id'];
                $token = session('token');
                $branch = M('Member_card_contact')->where(array('id'=>$branchId, 'token'=>$token))->select();
                if(!empty($branch) && !empty($branch[0])) {
                    $branch = $branch[0];
                    if(!empty($branch['picture'])) {
                        $img = M('raw_image')->find($branch['picture']);
                        $branch['raw_name'] = $img['raw_name'];
                    }else {
                        $branch['raw_name'] = '';
                    }
                    $this->assign('branch', $branch);
                    $this->display('branch_add');
                }else {
                    $this->error('无效的分店');
                }
            }		
    }

    public function branch_del(){
            $id = $this->_get('id','intval');
            $token = session('token');

            $where['id'] = $id;
            $where['token'] = $token;
            $ret = M('Member_card_contact')->where($where)->delete();
            if ($ret >= 0) {
                    $this->success('操作成功', U(MODULE_NAME.'/branchstore'));
            } else {
                    $this->error('操作失败', U(MODULE_NAME.'/branchstore'));
            }		
    }

}

?>

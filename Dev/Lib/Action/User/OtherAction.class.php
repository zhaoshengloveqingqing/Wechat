<?php
class OtherAction extends UserAction{

    protected function _initialize(){
        parent::_initialize();
        parent::checkOpenedFunction('kefu');
    }

    //配置
    public function index(){
        $other=M('Other')->where(array('token'=>session('token')))->find();
        $where = array();
        $where['uid'] = $_SESSION['uid'];
        $where['token'] = $_SESSION['token'];
        $where['function'] = 'kefu';
        $where['module'] = 'img';

        $keywords_in_db = M('keyword')->where($where)->select();
        $keywords = array();
        if ($keywords_in_db) 
        {
            foreach($keywords_in_db as $kwd) 
            {
                array_push($keywords, $kwd['keyword']);
            }
        }

        $this->assign('keywords',$keywords);
            //dump($other);
        $this->assign('other',$other);
        $this->display();
    }

    public function addOrUpdate() {
        $other = M('Other')->where(array('token'=>session('token')))->find();
        if ($other == false) {                
            $this->all_insert('Other','/index');
        } else {
            $data=array();
            $resp_type = $_POST['respType'];
            if ($resp_type == 1) {
                $data['info'] = $_POST['info'];
                $data['keyword'] = null;
            } else {
                $data['keyword'] = $_POST['keyword'];
            }
            $ret = M('Other')->where(array('token'=>session('token')))->save($data);
            if ($ret) {
                $this->success('操作成功',U(MODULE_NAME.'/index'));
            }else{
                $this->error('操作失败',U(MODULE_NAME.'/index'));
            }
        }
    }
    
}



?>

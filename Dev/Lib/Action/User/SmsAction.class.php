<?php
class SmsAction extends UserAction
{

    public function index()
    {
        $token = session('token');
        $where['token'] = $token;
        $info = M('sms_set')->where($where)->find();
	$funcs = split(';', $info['function']);
        foreach($funcs as $func)
        {
            $info[$func] = 1;
        }
        $this->assign('info',$info);

        $user = M('wxuser')->where(array('token'=>$token))->find();
        $uid = $user['uid'];
        $smsacount = M('smsaccount')->where(array('user_id'=>$uid))->find(); 
        $this->assign('smsaccount',$smsacount);
        $count = $smsacount['used'];
        //$count = M('sms_list')->where(array('token'=>$token))->count();
        $this->assign('count',$count);
		
        $this->display();
    }
    
    public function set() {
        $token = session('token');
        $func = $this->_post('func');
        $tel = $this->_post('tel');
		
        $data['token']=$token;
        $old = M('sms_set')->where($data)->find();
        if ($old) {
            $data2['function']=$func;
            $data2['tel']=$tel;
            $ret = M('sms_set')->where($data)->save($data2);
        } else {
            $data['function']=$func;
            $data['tel']=$tel;
            $ret = M('sms_set')->add($data);
        }
        if($ret){
            $this->success('短信通知设置成功');
        } else {
            $this->error('短信通知设置失败');
        }
		
    }
}
?>
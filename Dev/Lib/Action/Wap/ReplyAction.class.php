<?php
class ReplyAction extends BaseAction{

    public function index() {
        $id = $this->_get('id','intval');
		$wecha_id = $this->_get('wecha_id');
		$token = $this->_get('token');
		
        if ($id) $where['id'] = $id;
        if ($token) $where['token'] = $token;
		$where['status'] = 1;
		
        $reply = M('reply')->where($where)->find();
		$admins = unserialize($reply['admins']);
		for($j = 0; $j < count($admins); $j++) {
		     if ($wecha_id === $admins[$j][0]) {
				 $this->assign("admin", 1);
			 }
		}
        $this->assign("reply", $reply);
		
        $replys = M('reply_reply')->where(array('rid'=>$id, "token"=>$token, "_query"=>'status=2&_logic=and&openid='.$wecha_id))->order("parentid desc")->select();
        for($j = 0; $j < count($replys); $j++) {
		    if ($replys[$j]["id"] == $replys[$j]["parentid"]) {
			    $replys[$j]["sub"] = array();
				for ($k = $j + 1; $k < count($replys); $k++) {
				    if ($replys[$j]["id"] == $replys[$k]["parentid"]) {
				        array_push($replys[$j]["sub"], array($replys[$k]["id"], $replys[$k]["text"], $replys[$k]["nickname"]));
					} else {
					    break;
					}
                }				
			}
			if (!empty($replys[$j]['openid']) && $replys[$j]['openid'] == $wecha_id) {
			    $nickname = $replys[$j]['nickname'];
			}
		}
		$this->assign("nickname", $nickname);
		
        $this->assign('replys',$replys);
		$this->assign('wecha_id', $wecha_id);
		$this->display();
    }
	
	public function reply() {
        $id = $this->_get('id','intval');
        $pid = $this->_post('parentid','intval');
		
		$reply = M('reply')->where(array("id"=>$id, "status"=>1))->find();
		
        $data['rid'] = $id;
        $data['nickname'] = $this->_post('nickname');
		$data['text'] = $this->_post('text');
		$data['createtime'] = time();
		if ($reply['check'] == 2) {
		    $data['status'] = 2;
		} else {
		    $data['status'] = 1;
		}
		$data['token'] = $this->_get('token');
		$data['openid']  = $this->_post('wecha_id');
		$data['parentid']  = $this->_post('parentid','intval');
		
        $reply = M('reply_reply')->add($data);
		if (empty($pid)) {
		    M('reply_reply')->where(array('id'=>$reply))->save(array('parentid'=>$reply));
		}
		if ($reply) {
		    $this->ajaxReturn("发送成功", "OK", 1);
		} else {
		    $this->ajaxReturn("服务器忙，稍后重试", "OK", 1);
		}
    }
	
	public function delreply()
    {
        $id = $this->_post('id');
		$token = $this->_get('token');
        if(IS_POST)
        {                              
            $back=M('reply_reply')->where(array('id'=>$id,'token'=>$token))->save(array('status'=>0));
            if($back==true)
            {
                $this->ajaxReturn("", "OK", 1);
            }
            else
            {
                $this->ajaxReturn("", "服务器繁忙,请稍后再试", 0);
            }
        }        
    }
}
?>
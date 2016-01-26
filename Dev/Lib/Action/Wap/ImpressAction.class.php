<?php
class ImpressAction extends BaseAction{

	public function index() {
        $id = $this->_get('id','intval');
		$wecha_id = $this->_get('wecha_id');
		$token = $this->_get('token');
		
        if ($id) $where['id'] = $id;
        if ($token) $where['token'] = $token;
		$where['status'] = 1;
		
        $reply = M('impress')->where($where)->find();
		
        $this->assign("reply", $reply);
		
		$this->assign('wecha_id', $wecha_id);
		$this->assign('token', $token);
		$this->display();
    }
	
	public function jsonimpress() {
        $id = $this->_get('id','intval');
		$wecha_id = $this->_get('wecha_id');
		
        if ($id) $where['id'] = $id;
        if ($token) $where['token'] = $token;
		$where['status'] = 1;
		
        $impress = M('impress')->where($where)->find();
		$impresses = unserialize($impress['impress']);
		$sum = 0;
		$tops = array();
		for($j = 0; $j < count($impresses); $j++) {
		    $top['content'] = $impresses[$j][0];
			$top['count'] = $impresses[$j][1];
			$top['id'] = $j + 1;
			$sum += $top['count'];
			array_push($tops, $top);
		}
		
		$where2['openid'] = $wecha_id;
		$where2['rid'] = $id;
		$userimpress = M('reply_impress')->where($where)->find();
		if ($userimpress) {
		    $user['id'] = $userimpress['openid'];
			$user['content'] = $userimpress['content'];
			$user['count'] = $userimpress['count'];
		} else {
		    $user['id'] = -1;
		}
		
		$data["top"] = $tops;
		$data["msg"] = "ok";
		$data["ret"] = 0;
		$data["sum"] = $sum;
		$data["user"] = $user;
		$result = json_encode($data);  
		echo "reviewResult($result)"; 
    }
	
	public function addimpress() {
        $id = $this->_get('id','intval');
		$wecha_id = $this->_get('wecha_id');
		$token = $this->_get('token');
		$content = $this->_get('content');
		
        $where2['id'] = $id;
        $where2['token'] = $token;
		$where2['status'] = 1;
		$impress = M('impress')->where($where2)->find();
		$impresses = unserialize($impress['impress']);
		$where['count'] = 1;
		for($j = 0; $j < count($impresses); $j++) {
		    if ($impresses[$j][0] == $content) {
			    $where['count'] += $impresses[$j][1];
			}
		}
		
		$where['token'] = $token;
		$where['status'] = 1;
		$where['rid'] = $id;
		$where['content'] = $content;
		$where['createtime'] = time();
		$where['openid'] = $wecha_id;
		$re = M('reply_impress')->add($where);
		
		$user['id'] = $wecha_id;
		$user['count'] = $where['count'];
		$data["msg"] = "ok";
		$data["ret"] = 0;
		$data["user"] = $user;
		$result = json_encode($data);  
		echo "sendReviewResult($result)"; 
    }
}
?>
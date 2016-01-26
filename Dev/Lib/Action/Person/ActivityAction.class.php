<?php
class ActivityAction extends BaseAction{
    public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$this->display();
    }
	
	public function send(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$db = M('activity');
		$data["status"] = 1;
		$data["title"] = $this->_get('title');
		$data["content"] = $this->_get('content');
		$data["content"] = str_replace("\r\n", "<br/>", $data["content"]); 
		$data["content"] = str_replace("\n", "<br/>", $data["content"]);  
		$data["author"] = $this->_get('author');
		$data["pass"] = $this->_get('pass');
		$data["date"] = date("Y-m-d");
		
		$rid =$db->add($data);
		$result["id"]=$rid;
		$result["title"] = $this->_get('title');
		$result["content"] = $data["content"];
    	$this->ajaxReturn($result, "OK", 1);
    }
	
	public function showAct(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$db = M('activity');
		$id = $this->_get('id','intval');
    	$where['id'] = $id;
		$where['status'] = 1;
    	$activity = $db->where($where)->find();

		$jdb = M('activity_join');
    	$id = $this->_get('id','intval');
		$where2["act_id"] = $id;
		$joinNum = $jdb->where($where2)->count();
		$activity["joinNum"] = $joinNum;
		if($activity["date"] == date("Y-m-d")) {
		    $activity["date"] = "刚刚";
		} else if($activity["date"] == date("Y-m-d",strtotime("-1 day"))) {
		    $activity["date"] = "昨天";
		}
		$this->assign('activity',$activity);
		
		$where2['hots'] = array("gt", 0);
		$where2['status'] = 1;
		$hots = $db->where($where2)->limit(3)->order('hots desc')->select();
		$this->assign('hots',$hots);
		
		$this->display();
    }
	
	public function pass(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$db = M('activity');
		$id = $this->_get('id','intval');
		$where['pass'] = $this->_get('pass');
    	$where['id'] = $id;
		$where['status'] = 1;
    	$activity = $db->where($where)->find();
		$result["pass"]=0;
		if ($activity) {
		    $result["pass"]=1;
			$jdb = M('activity_join');
			$where2['act_id'] = $id;
			$result["joins"] = $jdb->where($where2)->select();
		}
		$this->ajaxReturn($result, "OK", 1);
    }
	
	public function join(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$jdb = M('activity_join');
    	$id = $this->_get('id','intval');
		$data["act_id"] = $id;
		$data["nick"] = $this->_get('nick');
		$data["phone"] = $this->_get('phone');
	    $check = $jdb->where($data)->find();
		
		$data["date"] = date("Y-m-d");
		if (!$check) {
		    $rid =$jdb->add($data);
			$result["state"] = 1;
			$result["id"] = $rid;
		    $result["nick"]=$data["nick"];
		    $result["phone"]=$data["phone"];
			$result["date"]=$data["date"];
		} else {
		    $result["state"] = 0;
		}
    	$this->ajaxReturn($result, "OK", 1);
    }
}
<?php
class GreetcardAction extends BaseAction {
    public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$db = M('greetcard_tp');
		$where['status'] = 1;
		
		$cardtps = $db->where($where)->order('sorts desc')->select();
		$this->assign('cardtps',$cardtps);
		$this->display();
    }
    
    public function tp(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$db = M('greetcard_tp');
    	$id = $this->_get('id','intval');
    	$where['status'] = 1;
    	$where['id'] = $id;
    
    	$cardtp = $db->where($where)->find();
    	
    	$this->assign('cardtp',$cardtp);
		$this->assign('date', date("Y-m-d"));
    	$this->display();
    }
	
	public function send(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$db = M('greetcard');
		$id = $this->_get('id','intval');
		$data["tp_id"] = $id;
		$data["recver"] = $this->_get('recver');
		$data["content"] = $this->_get('content');
		$data["author"] = $this->_get('author');
		$data["date"] = date("Y-m-d");
		
		$rid =$db->add($data);
		$result["id"] = $rid;
		$result["author"]=$data["author"];
		$result["content"]=$data["content"];
    	$this->ajaxReturn($result, "OK", 1);
    }
	
	public function showCard(){
    	$agent = $_SERVER['HTTP_USER_AGENT'];
    	if(!strpos($agent,"MicroMessenger")) {
    		echo '此功能只能在微信浏览器中使用';exit;
    	}
    	
    	$db = M('greetcard');
		$id = $this->_get('id','intval');
    	$where['id'] = $id;
    	$card = $db->where($where)->find();
		
		$tdb = M('greetcard_tp');
    	$where['status'] = 1;
    	$where['id'] = $card["tp_id"];
    	$cardtp = $tdb->where($where)->find();
		$card["bimgurl"] = $cardtp["bimgurl"];
		$card["himgurl"] = $cardtp["himgurl"];
		$card["title"] = $cardtp["title"];  
		$card["content"]=str_replace("\n", "<br>", $card["content"]);
		
		$this->assign('card',$card);
		$this->display();
    }
}

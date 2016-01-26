<?php
class WeddingAction extends BaseAction{

    public function index() {
        $id = $this->_get('id','intval');
        $token = $this->_get('token');
        if ($id) $where['id'] = $id;
        if ($token) $where['token'] = $token;
        $wedding = M('wedding')->where($where)->find();
		
		$navigationLink = "http://api.map.baidu.com/marker?location="
                .$wedding['latitude'].','.$wedding['longtitude']
                .'&title='.urlencode($wedding['title'])
                .'&name='.urlencode($wedding['title'])
                .'&content='.urlencode($wedding['wedding_address'])
                .'&output=html&src=lingzhtech';
        $this->assign('navigationLink', $navigationLink);
		
        $this->assign("wedding", $wedding);
		
		$wedding_pic_urls = split("\\^", $wedding["wedding_pic_urls"]);
		$this->assign("wedding_pic_urls", $wedding_pic_urls);
		
		Log::record($navigationLink."\r\n", Log::DEBUG);       
        Log::save();
		
		$this->display();
    }
	
	public function reply() {
        $id = $this->_get('id','intval');
        
        $data['wedding_id'] = $id;
        $data['name'] = $this->_post('name');
		$data['tel'] = $this->_post('tel');
		$data['text'] = $this->_post('text');
		$data['createtime'] = time();
		$data['status'] = 1;
		
        $reply = M('wedding_reply')->add($data);
		
		if ($reply) {
		    echo "发送成功";
		} else {
		    echo "服务器忙，稍后重试";
		}
    }
}
?>
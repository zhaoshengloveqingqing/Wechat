<?php
class EstateAction extends BaseAction{

    public function index(){
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $token      = $this->_get('token'); 
		$wecha_id      = $this->_get('wecha_id'); 
        $where      = array('token'=>$token);             

        $hostset =  M('Estate')->where(array('token'=>$token))->find();        
        $this->assign('set',$hostset);
		
		$photo=M('photo')->where(array('token'=>$token,'id'=>$hostset['photo_id'],'status'=>1))->find();
		$this->assign('photo', $photo);
		
		$this->assign('token', $token);
		$this->assign('wecha_id', $wecha_id);
		
        $this->display();
    }
    
    public function houses()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $token      = $this->_get('token'); 
		$wecha_id      = $this->_get('wecha_id');           

        $hostset =  M('Estate')->where(array('token'=>$token))->find();        
        $this->assign('set',$hostset);
		
		$houses =  M('Estate_house')->where(array('token'=>$token))->select();        
        $this->assign('houses',$houses);
		
		$panorama=M('panorama')->where(array('token'=>$token,'id'=>$hostset['panorama_id'],'status'=>1))->find();
		$this->assign('panorama', $panorama);
		
		$photo=M('photo')->where(array('token'=>$token,'id'=>$hostset['photo_id'],'status'=>1))->find();
		$this->assign('photo', $photo);
		
		$this->assign('token', $token);
		$this->assign('wecha_id', $wecha_id);
		
        $this->display();
    }
	
	public function photos()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $token      = $this->_get('token'); 
		$wecha_id      = $this->_get('wecha_id');
        $id      = $this->_get('id');        

		$house =  M('Estate_house')->where(array('token'=>$token, 'id'=>$id))->find();        
        $this->assign('house',$house);
		
		$this->assign('token', $token);
		$this->assign('wecha_id', $wecha_id);
		
        $this->display();
    }
    
	public function experts()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
            echo '此功能只能在微信浏览器中使用';exit;
        }
        $token      = $this->_get('token'); 
		$wecha_id      = $this->_get('wecha_id');

		$expert =  M('Estate_expert')->where(array('token'=>$token))->select();        
        $this->assign('expert',$expert);
		
		$photo=M('photo')->where(array('token'=>$token,'id'=>$hostset['photo_id'],'status'=>1))->find();
		$this->assign('photo', $photo);
		
		$this->assign('token', $token);
		$this->assign('wecha_id', $wecha_id);
		
        $this->display();
    }
}
    
?>
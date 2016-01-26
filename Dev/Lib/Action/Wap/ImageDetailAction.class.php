<?php
class ImageDetailAction extends BaseAction{
	private $wx_user;	//微信公共帐号信息
	private $wecha_id;
//	private $copyright;
	
	public function _initialize(){
		parent::_initialize();
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
		//	echo '此功能只能在微信浏览器中使用';exit;
		}
		$where['token'] = $this->_get('token','trim');
		$wx_user = D('Wxuser')->where($where)->find();
		$this->wecha_id = $this->_get('wecha_id','intval');
		$this->wx_user = $wx_user;
	}
	
	public function index() {
		
		$where['token']=$this->_get('token','trim');
		$where['id'] = array('neq',(int)$_GET['id']);
		$where['status'] = 1;
		$where['id']=$this->_get('id','intval');

		$res = M('Img')->where($where)->find();
        if ($res) {
		    $this->assign('res',$res);			//内容详情;
    		$this->assign('copyright',1);	//版权是否显示
	    	$this->assign('wx_user', $this->wx_user);				//微信帐号信息

		    $this->display($res['detail_display_tmpl']);
		} else {
		    $this->display('404_display');
		}
	}
}

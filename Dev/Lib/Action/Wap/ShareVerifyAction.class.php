<?php 
/**
* 处理分享页面时的问题，避免因wecha_id泄漏导致的信息泄露
*/
class ShareVerifyAction extends BaseAction
{
	 protected function _initialize(){
	 	parent::_initialize();
	 }

	 public function verify(){
	 	$token = $_GET['token'];
	 	if(!$token){
	 		exit();
	 	}
	 	$pub_account = M('wxuser')
                        ->where(array('token'=>$token))
                        ->field('wxname,company,telephone,type,description,picture,qrcode_pic,is_authed')
                        ->find();
	 	$this->assign('pub_account',$pub_account);
	 	$this->display();
	 }
}
 ?>
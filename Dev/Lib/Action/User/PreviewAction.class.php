<?php
class PreviewAction extends UserAction
{

	public function index()
	{
	    $model=$this->_get('model');
		if ($model=="home") {
		    $this->assign("title","微信官网");
		    $this->assign("url", U('Wap/Index/index',array('token'=>$_SESSION['token'])));
		} else if ($model=="article") {
		    $this->assign("title","微信官网文章");
		    $this->assign("url", U('Wap/Index/content',array('token'=>$_SESSION['token'], 'id'=>$_GET['id'])));
		} else if ($model=="hosts") {
		    $this->assign("title","微预订");
		    $this->assign("url", U('Wap/Host/index',array('token'=>$_SESSION['token'], 'hid'=>$_GET['id'])));
		} else if ($model=="card") {
		    $this->assign("title","微会员卡");
		    $this->assign("url", U('Wap/Card/get_card',array('token'=>$_SESSION['token'])));
		} else if ($model=="photo") {
		    $this->assign("title","微相册");
		    $this->assign("url", U('Wap/Photo/index',array('token'=>$_SESSION['token'])));
		} else if ($model=="photo_list") {
		    $this->assign("title","微相册");
		    $this->assign("url", U('Wap/Photo/plist',array('token'=>$_SESSION['token'], 'id'=>$_GET['id'])));
		} else if ($model=="panorama") {
		    $this->assign("title","3D全景相册");
		    $this->assign("url", U('Wap/Panorama/index',array('token'=>$_SESSION['token'])));
		} else if ($model=="dining") {
		    $this->assign("title","微餐饮");
		    $this->assign("url", U('Wap/Dining/index',array('token'=>$_SESSION['token'], 'id'=>$_GET['id'])));
		} else if ($model=="hotel") {
		    $this->assign("title","微宾馆");
		    $this->assign("url", U('Wap/Hotel/index',array('token'=>$_SESSION['token'], 'hid'=>$_GET['id'])));
		} else if ($model=="estate") {
		    $this->assign("title","微房产");
		    $this->assign("url", U('Wap/Estate/index',array('token'=>$_SESSION['token'])));
		}
		$this->display();
	}
}
?>
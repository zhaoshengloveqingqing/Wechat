<?php
class AnliAction extends BaseAction{
	//关注回复
	public function index(){
//	    $cases = M('case')->where(array('status' => 1))->select();
//		if (!empty($cases)){
//		    $this->assign('cases', $cases);
//		}
	    $this->display();
	}

}

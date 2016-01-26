<?php
class TipsAction extends UserAction
{

	public function check()
	{
		$orders = M('Host_order')->where(array('token'=>$this->token,'order_status'=>3))->select();
		$tipMsgs = array();
		$id = "";
		if (count($orders) > 0) {
		    $id = $id."order_";
		    foreach ($orders as $order) {
			    $id = $id.$tipId.$order['id'];
			}
			$tipMsg["href"] = "/index.php?g=User&m=Host&a=index";
			$tipMsg["type"] = "order";
			$tipMsg["num"] = count($orders);
			array_push($tipMsgs, $tipMsg);
		}
		$tips["id"] = $id;
		$tips["msg"] = $tipMsgs;
		$tips["num"] = count($tipMsgs);		
		$this->ajaxReturn($tips, "OK", 1);
	}
}



?>
<?php 
/**
*默认的会员等级
**/
return   array(
	'MEMBER_GROUP' 			=>array(array("id" => "1", "groupid"=>"1", "title" => "新会员"),//会员默认等级
	                                array("id" => "2", "groupid"=>"2", "title" => "普通"),
	                                array("id" => "3", "groupid"=>"3", "title" => "银卡"),
	                                array("id" => "4", "groupid"=>"4", "title" => "金卡"),
	                                array("id" => "5", "groupid"=>"5", "title" => "钻石"),),
	'MEMBER_AMOUNT_LIMIT'		=> 8888,//商家的默认会员数量限制
	'CARD_NUM_SET'=>array(),//
	'CARD_NUM_INFIX'=>1000000,//会员卡号中间数字默认起始
 );
 ?>
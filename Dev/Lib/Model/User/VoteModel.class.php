<?php
	class VoteModel extends Model{
	protected $_validate = array(
			array('keyword','require','关键词不能为空',1),
			array('title','require','投票信息不能为空',1),
			array('starttime','require','开始时间不能为空',1),
			array('endtime','require','结束时间不能为空',1),
			array('content','require','投票说明不能为空',1),
			array('type','require','投票类型不能为空',1)
	);
}

?>
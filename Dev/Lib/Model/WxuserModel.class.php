<?php
class WxuserModel extends Model{
	protected $_validate =array(
		array('wxname','require','公众号名称不能为空',1),
		array('wxid','require','公众号原始id不能为空',1),
		array('weixin','require','微信号不能为空',1),
		array('token','require','TOKEN不能为空',1),
		array('token','','token已经存在！',1,'unique',1),
		array('email','email','公众号邮箱格式不正确'),
		
	);
	
	protected $_auto = array (
		array('uid','getuser',self::MODEL_INSERT,'callback'),
		array('uname','getname',self::MODEL_INSERT,'callback'),
		array('tpltypeid','1',self::MODEL_INSERT),
		array('tpllistid','1',self::MODEL_INSERT),
		array('tpltypename','ty_index',self::MODEL_INSERT),
		array('tpllistname','yl_list',self::MODEL_INSERT),
		array('createtime','time',self::MODEL_INSERT,'function'),
		array('updatetime','time',self::MODEL_BOTH,'function'),
		array('typeid','gettypeid',self::MODEL_BOTH,'callback'),
		array('typename','gettypename',self::MODEL_BOTH,'callback'),
	);
	
	public function getuser(){
		return session('uid');
	}
	
	public function getname(){
		return session('uname');
	}
	
	public function gettypeid(){
		$res=explode(',',$_POST['type']);
		return $res[0];
	}
	
	public function gettypename(){
		$res=explode(',',$_POST['type']);
		return $res[1];
	}
	
}

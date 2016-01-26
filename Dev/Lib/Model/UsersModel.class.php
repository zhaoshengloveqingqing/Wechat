<?php
class UsersModel extends Model{

	//自动验证
	protected $_validate=array(
		array('username','require','用户名称必须填写！',1,'',3),
		array('username','','用户名称已经存在！',1,'unique',1), // 新增修改时候验证username字段是否唯一
		array('password','require','用户密码必须填写！',1,'',3),
		array('repassword','password','两次密码不一致',1,'confirm'), 
		array('name','require','用户姓名必须填写！',1,'',3),
		array('tel','require','常用号码必须填写！',1,'',3),
		array('company','require','所属公司必须填写！',1,'',3),
		array('industry','require','行业必须填写！',1,'',3),
		array('city','require','所在城市必须填写！',1,'',3),
		array('pos','require','职位必须填写！',1,'',3),
	);
	
	protected $_auto = array (
		array('password','md5',self::MODEL_BOTH,'function'),
		array('repassword','md5',self::MODEL_BOTH,'function'),
		array('createtime','time',self::MODEL_INSERT,'function'),
		array('createip','getip',self::MODEL_INSERT,'callback'),
		array('viptime','time',self::MODEL_BOTH,'function'),
		array('lasttime','time',self::MODEL_BOTH,'function'),
		array('lastip','getip',self::MODEL_BOTH,'callback'),
		//array('status','getstatus',self::MODEL_BOTH,'callback'),
		array('gid','getgid',self::MODEL_INSERT,'callback'),
                array('administrator','getAdministator',self::MODEL_INSERT,'callback'),
	);
	
	public function getip(){
		return $_SERVER['REMOTE_ADDR'];
	}
	
	public function getstatus(){
		return C('ischeckuser')?0:1;
	}
	
	public function getgid(){
		return isset($_POST['gid'])?(int)$_POST['gid']:1;
	}
        
        public function getAdministator(){
                return session(C('USER_AUTH_KEY'));
	}
}

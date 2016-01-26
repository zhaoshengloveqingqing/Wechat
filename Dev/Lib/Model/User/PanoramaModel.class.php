<?php
class PanoramaModel extends Model{
	protected $_validate = array(
			array('title','require','全景图标题不能为空',1),
			//array('info','require','相册详细内容必须填写',1),
			array('sort','require','请填写全景图的顺序',1),
			array('id','checkid','非法操作',2,'callback',2),
	 );
	protected $_auto = array (		
                array('status',1,Model:: MODEL_INSERT),
		array('token','getToken',Model:: MODEL_INSERT,'callback'),
		array('create_time','time',Model:: MODEL_INSERT,'function'), // 对create_time字段在更新的时候写入当前时间戳);
	);
	function checkid(){
		$dataid=$this->field('id')->where(array('id'=>$_POST['id'],'token'=>session('token')))->find();
		if($dataid==false){
			return false;
		}else{
			return true;
		}
	}
	function getToken(){	
		return $_SESSION['token'];
	}
}

?>

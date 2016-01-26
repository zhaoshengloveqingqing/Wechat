<?php
class SiteAction extends BackAction{
	public function _initialize() {
        parent::_initialize();  //RBAC 验证接口初始化
    }
	
	public function index(){
		$this->display();
	}
	public function email(){
		$this->display();
	}	
	public function alipay(){
		$this->display();
	}
	public function safe(){
		$this->display();
	}
	public function upfile(){
		$this->display();
	}
	public function insert(){
		$file=$this->_post('files');
		unset($_POST['files']);
		unset($_POST[C('TOKEN_NAME')]);
		
		if($this->update_config($_POST,CONF_PATH.$file)){
			$this->success('操作成功',U('Site/index',array('pid'=>6,'level'=>3)));
		}else{
			$this->success('操作失败',U('Site/index',array('pid'=>6,'level'=>3)));
		}
	}
	
	private function update_config($config, $config_file = '') {
		if (@is_file($config_file) && @is_writable($config_file)) {
			$current_config = require $config_file;
			$config = array_merge($current_config, $config);
			file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
			@unlink(RUNTIME_FILE);
			return true;
		} else {
			return false;
		}
	}
}
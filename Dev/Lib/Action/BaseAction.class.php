<?php
require_once(COMMON_PATH.'/CopyRightHelper.php');
class BaseAction extends Action{
	protected function _initialize(){
		define('RES',THEME_PATH.'common');
		define('STATICS',TMPL_PATH.'static');
		//Input::noGPC();
		$this->assign('action',$this->getActionName());
	}
	
	// /**
     // * 上传文件默认规则定义
     // */
    // protected function _upload_init($upload) {
        // $allow_max = C('up_size'); //读取配置
        // $allow_exts = explode(',', C('up_exts')); //读取配置
        // $allow_max && $upload->maxSize = $allow_max * 1024;   //文件大小限制
        // $allow_exts && $upload->allowExts = $allow_exts;  //文件类型限制
        // $upload->saveRule = 'uniqid';
        // return $upload;
    // }

    // /**
     // * 上传文件
     // */
    // protected function _upload($file, $dir = '', $thumb = array(), $save_rule='uniqid') {
        // $upload = new UploadFile();
        // if ($dir==false) {
            // $upload_path = C('up_path') . $dir . '/';
            // $upload->savePath = $upload_path;
        // }
        // if ($thumb) {
            // $upload->thumb = true;
            // $upload->thumbMaxWidth = $thumb['width'];
            // $upload->thumbMaxHeight = $thumb['height'];
            // $upload->thumbPrefix = '';
         //  $upload->thumbSuffix = isset($thumb['suffix']) ? $thumb['suffix'] : '_thumb';
            // $upload->thumbExt = isset($thumb['ext']) ? $thumb['ext'] : '';
            // $upload->thumbRemoveOrigin = isset($thumb['remove_origin']) ? true : false;
       // }
      //  自定义上传规则
        // $upload = $this->_upload_init($upload);
        // if( $save_rule!='uniqid' ){
            // $upload->saveRule = $save_rule;
        // }

        // if ($result = $upload->uploadOne($file)) {
            // return array('error'=>0, 'info'=>$result);
        // } else {
            // return array('error'=>1, 'info'=>$upload->getErrorMsg());
        // }
   // }
	
	//添加所有内容,包含关键词
	protected function all_insert($name='',$back='/index'){
		$name=$name?$name:MODULE_NAME;
		$db=D($name);
		if($db->create()===false){
			$this->error($db->getError());
		}else{
			$id=$db->add();
			if($id){
				$m_arr=array('Ordering','Lottery','Host');
				if(in_array($name,$m_arr)){
					$data['pid']=$id;
					$data['module'] = 'img';
					$data['token']=session('token');
					$data['keyword']=$_POST['keyword'];
					switch ($name) {
					case 'Lottery': 
					    $data['function'] ='dazhuanpan';
						break;
					case 'Host': 
					    $data['function'] ='dingdan';
						break;
					case 'Ordering': 
					    $data['function'] ='dingdan';
						break;
					}
					M('Keyword')->add($data);
				}
				$this->success('操作成功',U(MODULE_NAME.$back));
			}else{
				$this->error('操作失败',U(MODULE_NAME.$back));
			}
		}
	}
	//单一信息添加
	protected function insert($name='',$back='/index'){
		$name=$name?$name:MODULE_NAME;
		$db=D($name);
		if($db->create()===false){
			$this->error($db->getError());
		}else{
			$id=$db->add();
			if($id==true){
				$this->success('操作成功',U(MODULE_NAME.$back));
			}else{
				$this->error('操作失败',U(MODULE_NAME.$back));
			}
		}
	}
	//单子信息修改
	protected function save($name='',$back='/index'){
		$name=$name?$name:MODULE_NAME;
		$db=D($name);
		if($db->create()===false){
			$this->error($db->getError());
		}else{
			$id=$db->save();
			if($id==true){				
				$this->success('操作成功',U(MODULE_NAME.$back));
			}else{
				$this->error('操作失败',U(MODULE_NAME.$back));
			}
		}
	}
	//修改所有内容,包含关键词
	protected function all_save($name='',$back='/index')
	{
		$name 	= $name?$name:MODULE_NAME;
		$db  	= D($name);

		if($db->create()===false)
        {
			$this->error($db->getError());
		}
        else
        {
			$id = $db->save();
			if($id !== false){
				$m_arr=array('Img','Text','Voiceresponse','Ordering','Lottery','Host');
				if(in_array($name,$m_arr)){
					$data['pid']=$_POST['id'];
					$data['module']=$name;
					$data['token']=session('token');
					$da['keyword']=$_POST['keyword'];
					M('Keyword')->where($data)->save($da);
					//F('SQLTT',M('Keyword')->getLastSql());
				}
				$this->success('操作成功',U(MODULE_NAME.$back));
			}else{
				$this->error('操作失败',U(MODULE_NAME.$back));
			}
		}
	}
	
	protected function all_del($id,$name='',$back='/index'){
		$name=$name?$name:MODULE_NAME;
		$db=D($name);
		if($db->delete($id)){
			$this->ajaxReturn('操作成功',U(MODULE_NAME.$back));
		}else{
			$this->ajaxReturn('操作失败',U(MODULE_NAME.$back));
		}
	}
        
}

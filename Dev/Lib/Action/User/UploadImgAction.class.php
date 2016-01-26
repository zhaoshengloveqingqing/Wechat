<?php
/**
 *文本回复
**/
class UploadImgAction extends UserAction{

    protected function _initialize(){
        parent::_initialize();
    }

    public function index(){
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $galleryid = isset($_GET['galleryid']) ? $_GET['galleryid'] : 0;
        $is3d = isset($_GET['is3d']) ? $_GET['is3d'] : 0;
        
        $this->assign("galleryid", $id);
        $this->assign('is3d', $is3d);
        $this->assign("id", $id);
        $this->display();
    }
    
    public function multi(){
        $id = isset($_GET['galleryid']) ? $_GET['galleryid'] : 0;
        $is3d = isset($_GET['is3d']) ? $_GET['is3d'] : 0;
        $this->assign("galleryid", $id);
        $this->assign('is3d', $is3d);
        $this->display();
    }

    public function upload(){
        
        $ret = array('error' => 0, 'message' => '');

        $token = session('token');
        if (!isset($token) || empty($token)) {
        	$ret['error'] = 2;
            $ret['message'] = "保存失败";
            echo json_encode($ret);
            exit;
        }

        //kindeditor is imgFile but uploadify is Filedata
        $file = isset($_FILES["Filedata"]) ? $_FILES["Filedata"] : $_FILES["imgFile"];
        if(1000000 < $file["size"])
        //检查文件大小 Byte
        {
            $ret['error'] = 2;
            $ret['message'] = "文件太大!";
            echo json_encode($ret);
            exit;
        }

        //上传文件类型列表
        $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
        $fileParts = pathinfo($file['name']);

        $is_quanlified_type = 0;

        foreach ($fileTypes as $key => $type) 
        {
            if (strcasecmp($fileParts['extension'], $type) == 0) 
            {
                $is_quanlified_type = 1;
            }
        }
    
        if ($is_quanlified_type != 1) 
        //检查文件类型
        {
            $ret['error'] = 2;
            $ret['message'] = $fileParts['extension']." 文件类型不符!";
            echo json_encode($ret);
            exit;
        }
        
        $targetFolder = '/customer_imgs/'.$token; // Relative to the root and should match the upload folder in the uploader script
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $targetFolder)) 
        {
            mkdir($_SERVER['DOCUMENT_ROOT'] . $targetFolder);
        }
        
        if(isset($_GET['is3d']) && intval($_GET['is3d']) == 1) {
            // 3d gallery
            $targetFolder = $targetFolder.'/d3';
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $targetFolder)) 
            {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $targetFolder);
            }
            
            if(isset($_GET['galleryid']) && intval($_GET['galleryid']) > 0) {
                $targetFolder = $targetFolder.'/'.intval($_GET['galleryid']);
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $targetFolder)) 
                {
                    mkdir($_SERVER['DOCUMENT_ROOT'] . $targetFolder);
                }
            }
        }
        else {
            $targetFolder = $targetFolder.'/d2';
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $targetFolder)) 
            {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $targetFolder);
            }
        }

        $encodeFile = urlencode($file['name']);
        $targetFile = time().'_'.$file["name"];
        if (strpos($encodeFile, "%") !== false) 
        {
            $targetFile = md5($targetFile);
            if (strlen($targetFile) > 16) 
            {
                $targetFile = substr($targetFile, 0, 16);
            }
            $targetFile = $targetFile.'.'.$fileParts['extension'];
        }
        
        if (!move_uploaded_file($file["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $targetFolder.'/'.$targetFile))
        {
            $ret['error'] = 2;
            $ret['message'] = "保存文件出错";
            echo json_encode($ret);
            exit;
        } else 
        {
            $rawImageData = array();
            $rawImageData['link'] = "http://".C('wx_handler_server').$targetFolder.'/'.$targetFile;
            $rawImageData['create_time'] = time();
            $rawImageData['raw_name'] = $file['name'];
            $rawImageData['status'] = 1;
            $rawImageData['token'] = $token;
            $rawImageId = M('raw_image')->add($rawImageData);
            if(!$rawImageId) {
                $ret['error'] = 2;
                $ret['message'] = "保存文件信息到数据库出错";
                echo json_encode($ret);
                exit;
            }
            $ret['rawImageId'] = $rawImageId;
            $ret['rawImageName'] = $file['name'];
            
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $ret['id'] = $id;
            $ret['message'] = "保存成功".$file['name'];
            $ret['content'] = "http://".C('wx_handler_server').$targetFolder.'/'.$targetFile;
            $ret['url'] = "http://".C('wx_handler_server').$targetFolder.'/'.$targetFile;
            
            
            echo json_encode($ret);
        }
    }

    
}
?>

<?php
/**
 *文本回复
**/
class UploadImgAction extends ManageAction
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    public function upload()
    {
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->maxSize  = 1048576 ;// 设置附件上传大小 1MB
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath =  $_SERVER['DOCUMENT_ROOT']."/customer_imgs/$this->token/2d/";// 设置附件上传目录

        $ret = array('error' => 0, 'message' => '');

        if (!$upload->upload()) 
        {
            // 上传错误提示错误信息
            $ret['error'] = 2;
            $ret['message'] = $upload->getErrorMsg();
            $this->ajaxReturn($ret,'JSON');
          
        }
        else
        {
            // 上传成功 获取上传文件信息
            $info =  $upload->getUploadFileInfo();
            Log::record(print_r($info,true), Log::DEBUG);

            $rawImageData = array();
            $rawImageData['link'] = "http://".C('wx_handler_server')."/customer_imgs/$this->token/2d/".$info[0]['savename'];
            $rawImageData['create_time'] = time();
            $rawImageData['raw_name'] = $info[0]['name'];
            $rawImageData['status'] = 1;
            $rawImageData['token'] = $token;
            $rawImageId = M('raw_image')->add($rawImageData);

            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $ret['id']      = $id;
            $ret['message'] = "保存成功".$info[0]['name'];
            $ret['content'] = "http://".C('wx_handler_server')."/customer_imgs/$this->token/2d/".$info[0]['savename'];
            $ret['url']     = "http://".C('wx_handler_server')."/customer_imgs/$this->token/2d/".$info[0]['savename'];

            
            $this->ajaxReturn($ret,'JSON');
        }
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
    
}
?>

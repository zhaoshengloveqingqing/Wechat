<?php
class PanoramaAction extends UserAction
{
    public static $SliceNameList = array('前剖面','右剖面','后剖面','左剖面','顶部剖面','底部剖面');
    protected function _initialize()
    {
        parent::_initialize();
		$this->function = 'panorama';
        parent::checkOpenedFunction('panorama');
    }
    
    public function index()
    {       
        //相册列表
        $data   = M('gallery3d');
        $count  = $data->where(array('token'=>$_SESSION['token'], 'status' => '1'))->count();
        $Page   = new Page($count,12);
        $show   = $Page->show();

        $list = $data->where(array('token'=>$_SESSION['token'], 'status' => '1'))->limit($Page->firstRow.','.$Page->listRows)->select(); 

        $this->assign('page',$show); 
        $this->assign('count',$count); 		
        $this->assign('photo',$list);
        $this->display();       
    }

    public function addmultipano(){

        $panoramaid = $_GET['panoramaid'];
        $galleryid = $_GET['galleryid'];
        $where = array('token'=>$_SESSION['token'],'id'=>$panoramaid, 'galleryid'=>$galleryid);
        $panorama = M('panorama')->where($where)->find();

        $slices = M('panorama_slices')->join('tp_raw_image on tp_raw_image.id = tp_panorama_slices.raw_image_id')
                ->where(array('tp_panorama_slices.panorama_id'=>$panoramaid, 'tp_panorama_slices.status'=>'1'))
                ->order('tp_panorama_slices.sort asc')
                ->field('tp_raw_image.link link')
                ->select();
        if($panorama == null || $slices == null)
        {
            $this->error('该全景图不存在或者全景图设置有误，请重新设置！');
            exit;
        }

        if(IS_POST){
            $nav_info =  array();
            $count = count($_POST['album']) > 8 ? 8 : count($_POST['album']);
            for ($i=0; $i < $count; $i++) { 
                if(empty($_POST['album'][$i])&&empty($_POST['pano'][$i])){
                    continue;
                }
                array_push($nav_info,array('album'=>$_POST['album'][$i],
                                            'pano'=>$_POST['pano'][$i],
                                            'pan'=>$_POST['pan'][$i],
                                            'tilt'=>$_POST['tilt'][$i],
                                            'desc'=>$_POST['desc'][$i]));
            }
            $panorama['nav_info'] = serialize($nav_info);
            $ret = M('panorama')->where($where)->save($panorama);
            if($ret < 0){
                $this->error("服务器繁忙，请稍后再试！");
            }
            else{
                $this->success("保存成功！");
            }
        }


        $data   = M('gallery3d');
        $albumList = $data->where(array('token'=>$_SESSION['token'], 'status' => '1'))
                     ->field('id,title')->select();
        $albumIds  = array();
        foreach ($albumList as $k => $val) {
            array_push($albumIds, $val['id']);
        }
        $con['_string'] = ' galleryid in ('. implode(',', $albumIds) .')';
        $panoList = M('panorama')->where($con)
                                 ->field('id,title,galleryid as aid')
                                 ->select();
        $albumList = json_encode($albumList);
        $panoList = json_encode($panoList);
        $this->assign('token',$this->token);
        $this->assign('panoList',$panoList);
        $this->assign('albumList',$albumList);
        $this->assign('nav_info',unserialize($panorama['nav_info']));
        $this->assign('panorama', $panorama);
        $this->assign('panoramaid', $panoramaid);
        $this->display();
    }

    //配置
    public function set() {
        $where['token'] = session('token');
        $where['uid'] = session('uid');
        $where['function'] = $this->function;
        $where['status'] = 1;

        $img = M('img')->where($where)->find() ;
        $this->assign('gallerytype','全景图');  
        if (IS_POST) 
        {
            if ($img == false) 
            {
                // 添加新图文消息
                $data['token'] = session('token');
                $data['uid'] = session('uid');
                $data['uname'] = session('uname');
                $data['keyword'] = isset($_POST['keyword']) ? $_POST['keyword'] : '全景相册';
                $data['type'] = 1;
                $data['text'] = $_POST['text'];  //文章摘要
                $data['pic'] = $_POST['pic'];  //图片地址
                $data['showpic'] = 1; 
                $data['info'] = null;
                $data['url'] = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Panorama&a=index&token='.session('token');
                $data['title'] = $_POST['title'];
                $data['function'] = $this->function;
                $data['detail_display_tmpl'] = 'img_content' ;
                $data['createtime'] = time();
                $data['updatetime'] = $data['createtime'];
                $data['status'] = 1;
                $img_id = M('img')->add($data);

                if ($img_id) {
                    $kwds_db = M('keyword');
                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['type'] = 1;
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['pid'] = $img_id;
                    $keywords = explode(' ',  $data['keyword']);
                    foreach($keywords as $vo) {
                            $kwd_data['keyword'] = $vo;    
                            $kwds_db->add($kwd_data);
                    }
                    $this->success('操作成功',U(MODULE_NAME.'/set'));
                 } else {
                    $this->error('添加失败',U(MODULE_NAME.'/set'));
                 }

             } else {

                $nimg = M('img');
                        $data['keyword'] = $_POST['keyword'];
                        $data['text'] = $_POST['text'];  //文章摘要
                        $data['pic'] = $_POST['pic'];
                        $data['title'] = $_POST['title'];
                        $data['updatetime'] = time();
                $ret = $nimg->where($where)->save($data);

                if ($ret) {
                    $kwds_db = M('keyword');
                    $kwds_db->where(array('token'=> session('token'), 'function'=>$this->function, 'module'=>'img'))->delete();

                    $kwd_data['uid'] = session('uid');
                    $kwd_data['token'] = session('token');
                    $kwd_data['module'] = 'img';
                    $kwd_data['function'] = $this->function;
                    $kwd_data['type'] = 1;
                    $kwd_data['pid'] = $img['id'];
                    $keywords = explode(' ',  $_POST['keyword']);
                    foreach($keywords as $vo) {
                        $kwd_data['keyword'] = $vo;    
                        $kwds_db->add($kwd_data);
                    }
                    $this->success('操作成功',U(MODULE_NAME.'/set'));
                } else {
                    $this->error('保存失败',U(MODULE_NAME.'/set'));
                }
             }
        } else {
              
            $this->assign('photo',$img);
            $this->display('Photo:set');
        }
    }
	
    public function panorama_edit()
    {
        if (empty($_SESSION['token']))
        {
            $this->error('非法操作');
        }
        $panoramaid = $_GET['panoramaid'];
        $galleryid = $_GET['galleryid'];
        $panorama = M('panorama')->where(array('token'=>$_SESSION['token'],'id'=>$panoramaid, 'galleryid'=>$galleryid))->find();
        
        if ($panorama == false)
        {
            $this->error('该全景照片不存在');
        }
        if (IS_POST)
        {
            $_POST['id'] = $panoramaid;
            $db = D('Panorama');
            if ($db->create()===false)
            {
                $this->error($db->getError());
            }
            else
            {
                $id=$db->save();
                if ($id >= 0) 
                {
                    $allImgIds = M('panorama_slices')->field('raw_image_id')->where(array('panorama_id'=>$panoramaid, 'status'=>'1'))->select();
                    $existingImgIds = array();
                    foreach($allImgIds as $key=>$tmpId){
                        array_push($existingImgIds, $tmpId['raw_image_id']);
                    }
                    for($i=1; $i<7;$i++) {
                        if(!isset($_POST['pic_url_id_input_'.$i]) || empty($_POST['pic_url_id_input_'.$i])) {
                            continue;
                        }
                      
                        $currImageId = $_POST['pic_url_id_input_'.$i];
                        $itemKey = array_search($currImageId, $existingImgIds, true);
                        if($itemKey !== FALSE) {
                            unset($existingImgIds[$itemKey]);
                        }else{
                            M('panorama_slices')
                                    ->where(array('panorama_id'=>$panoramaid, 'raw_image_id'=>$currImageId))
                                    ->setField('status', '0');
                            $sliceData['panorama_id'] = $panoramaid;
                            $sliceData['raw_image_id'] = $currImageId;
                            $sliceData['sort'] = $i;
                            $sliceData['create_time'] = time();
                            $sliceData['status'] = 1;
                            if(!M('panorama_slices')->add($sliceData)) {
                                $this->error('添加全景图片失败！请刷新重试！');

                                exit;
                            }
                        }
                    }
                    if(count($existingImgIds) > 0) {
                        $condition['panorama_id'] = $panoramaid;
                        $condition['raw_image_id'] = array('in', $existingImgIds);
                        M('panorama_slices')->where($condition)->setField('status', '0');
                    }
                    
                    $this->success('操作成功', U('Panorama/panorama_list' , array('galleryid' =>$galleryid)));
                }
                else 
                {
                    $this->error('修改全景图失败！！！请刷新重试！');
                }
            }  
            
        }
        else
        {
            // get all slices
            $slices = M('panorama_slices')
                    ->join(' tp_raw_image on tp_panorama_slices.raw_image_id = tp_raw_image.id')
                    ->where(array('tp_raw_image.status' => 1, 'tp_panorama_slices.panorama_id' => $panorama['id'], 'tp_panorama_slices.status'=>1))
                    ->field(' tp_panorama_slices.sort image_order,  tp_panorama_slices.raw_image_id raw_image_id, tp_raw_image.link link, tp_raw_image.raw_name raw_name')
                    ->order('image_order asc' )
                    ->select();
            
            $slicesMap = array();
            foreach($slices as $slice) {
                $slicesMap[$slice['image_order']] = $slice;
            }
            
            
            $this->assign('panorama', $panorama);
            $this->assign('slices', $slicesMap);
            $this->display('Panorama:panorama_add');
        }
    }
    
    public function panorama_del($panoramaid, $galleryid)
    {
        if (empty($_SESSION['token']))
        {
            $this->error('非法操作');
        }

        $check = M('panorama')->field('id,galleryid')->where(array('token'=>$_SESSION['token'],'galleryid'=>$galleryid, 'id'=>$panoramaid, 'status'=>'1'))->find();
        if ($check == false) 
        {
            $this->error('服务器繁忙');
        }
        
        // 从panorama表中删除
        if (M('panorama')->where(array('id'=>$panoramaid))->setField('status', '0'))
        {
            // 从gallery3d表中减少相册中照片总数
            if(M('gallery3d')->where(array('id'=>$galleryid))->setDec('num'))
            {
                // 从切片表中删除该全景图对应的关系
                if(M('panorama_slices')->where(array('panorama_id'=>$panoramaid))->setField('status', '0'))
                {
                    $this->success('删除全景图成功', U('Panorama/panorama_list' , array('galleryid' =>$galleryid)));
                    exit;
                }
            }
        }
        
        $this->error('服务器繁忙,请稍后再试');
    }

    public function panorama_add() {
        
        $galleryid = $this->_get('galleryid');
        $album = M('gallery3d')->where(array('token'=>$_SESSION['token'],'id'=>$galleryid))->find();
        if ($album == false) 
        {
            $this->error('相册不存在');
            exit;
        }
        
        if (IS_POST)
        {
            // 创建新的panorama记录
            $_POST['galleryid'] = $galleryid;
            $db=D('Panorama');
            if($db->create()===false){
                    $this->error($db->getError());
                    exit;
            }
            $panoramaId=$db->add();
            if(!$panoramaId) {
                $this->error('创建全景图失败！请重试！');
                exit;
            }
            
            // 更新相册中全景图片的个数
            M('gallery3d')->where(array('token'=>session('token'),'id'=>$galleryid))->setInc('num');
            
            // 创建全景图片与切片之间的关系
            for($i=1; $i<7;$i++) {
                if(!isset($_POST['pic_url_id_input_'.$i]) || empty($_POST['pic_url_id_input_'.$i])) {
                    continue;
                }
                
                $sliceData['panorama_id'] = $panoramaId;
                $sliceData['raw_image_id'] = $_POST['pic_url_id_input_'.$i];
                $sliceData['sort'] = $i;
                $sliceData['create_time'] = time();
                $sliceData['status'] = 1;
                
                if(!M('panorama_slices')->add($sliceData)) {
                    $this->error('添加全景图片失败！请刷新重试！');
                
                    exit;
                }
            }
            
            $this->success('操作成功', U('Panorama/panorama_list' , array('galleryid' =>$galleryid)));
            exit;
        }
        else
        {
            $this->assign('galleryid', $galleryid);
            $this->display();   
           
        }
    }
    
    public function panorama_list()
    { 
        $galleryid = $this->_get('galleryid');
        $album = M('gallery3d')->where(array('token'=>$_SESSION['token'],'galleryid'=>$galleryid, 'status'=>1))->find();
        if ($album == false) 
        {
            $this->error('相册不存在');
            exit;
        }
        if (!IS_POST)
        {            
            $data   =M('panorama');     
            $count  = $data->where(array('token'=>$_SESSION['token'],'galleryid'=>$galleryid, 'status'=>1))->count();
            $Page   = new Page($count,30);
            $show   = $Page->show();
            $list   = $data->where(array('token'=>$_SESSION['token'],'galleryid'=>$galleryid, 'status'=>1))->order('sort asc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();    
            $this->assign('page',$show);      
            $this->assign('photo',$list);
            $this->assign('galleryid', $galleryid);
         
            $this->display();   
        }
    }

     public function gallery3d_edit()
    {
        if (IS_POST)
        {
            $this->all_save('Gallery3d');
        }
        else
        {
            $this->assign('token', session('token'));
            $photo = M('gallery3d')->where(array('token'=>session('token'),'id'=>$this->_get('id'), 'status'=>'1'))->find();
            if ($photo == false) 
            {
                $this->error('相册不存在');
            }
            else
            {
                $this->assign('photo',$photo);
            }
            $this->display();       
        }
    }
    
    public function gallery3d_add()
    {
        if(IS_POST)
        {
            $this->all_insert('Gallery3d','/index');          
        }
        else
        {
            $this->assign('token', session('token'));
            $this->display();   
        }
        
    }
    public function gallery3d_del()
    {
        $check=M('gallery3d')->field('id')->where(array('token'=>$_SESSION['token'],'id'=>$this->_get('id'), 'status'=>'1'))->find();
        if($check==false){$this->error('服务器繁忙');}
        if(empty($_POST['edit'])){
            if(M('gallery3d')->where(array('id'=>$check['id'], 'status'=>'1'))->setField('status', '0')){
                // 删除该相册中的全景照片
                M('panorama')->where(array('galleryid'=>$this->_get('id')))->setField('status', '0');
                $this->success('操作成功');
            }else{
                $this->error('服务器繁忙,请稍后再试');
            }
        }
    
    }

    


}


?>

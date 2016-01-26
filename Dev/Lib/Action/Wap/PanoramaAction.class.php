<?php
class PanoramaAction extends BaseAction{
	public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")&& empty($_GET['preview'])) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token==false){
			echo '数据不存在';exit;
		}
		$photo=M('gallery3d')->where(array('token'=>$token,'status'=>1))->order('id desc')->select();
		
		$this->assign('photo',$photo);
		$this->display();
	}
        
        public function panorama($token, $galleryid, $panoramaid) {
            // 获得前一个后一个全景图
            $panoramas=M('panorama')->where(array('token'=>$token,'galleryid'=>$galleryid,'status'=>1))
                    ->order("sort asc")->select();
            if($panoramas == FALSE || count($panoramas) <= 0)
            {
                echo '非法请求!！请联系客服！';
                exit;
            }
            
            for($i = 0; $i < count($panoramas); $i ++){
                if($panoramas[$i]['id'] == $panoramaid) {
                    
                    $panorama = $panoramas[$i];
                    $prev = ($i - 1 + count($panoramas)) % count($panoramas);
                    $next = ($i + 1 + count($panoramas)) % count($panoramas);
                    
                    $prevPanoId = $panoramas[$prev]['id'];
                    $nextPanoId = $panoramas[$next]['id'];
                    break;
                }
            }
            
            // validation
            $slices = M('panorama_slices')->join('tp_raw_image on tp_raw_image.id = tp_panorama_slices.raw_image_id')
                    ->where(array('tp_panorama_slices.panorama_id'=>$panoramaid, 'tp_panorama_slices.status'=>'1'))
                    ->order('tp_panorama_slices.sort asc')
                    ->field('tp_raw_image.link link')
                    ->select();
            if($panorama == null || $slices == null)
            {
                echo '该全景图不存在或者全景图设置有误，请重新设置！';
                exit;
            }
            
            
            $this->assign('token',$token);
            $this->assign('shareimg',$slices[0]['link']);
            $this->assign('panorama', $panorama);
            $this->assign('panoramaid', $panoramaid);
            $this->assign('prevPanoId', $prevPanoId);
            $this->assign('nextPanoId', $nextPanoId);
            $this->display();
        }

		public function panorama_one($token, $panoramaid) {
            // 获得前一个后一个全景图
            $panorama=M('panorama')->where(array('token'=>$token,'id'=>$panoramaid,'status'=>1))
                    ->order("sort asc")->select();
            if($panorama == FALSE)
            {
                echo '非法请求!！请联系客服！';
                exit;
            }
            
            // validation
            $slices = M('panorama_slices')->join('tp_raw_image on tp_raw_image.id = tp_panorama_slices.raw_image_id')
                    ->where(array('tp_panorama_slices.panorama_id'=>$panoramaid, 'tp_panorama_slices.status'=>'1'))
                    ->order('tp_panorama_slices.sort asc')
                    ->field('tp_raw_image.link link')
                    ->select();
            if($panorama == null || $slices == null)
            {
                echo '该全景图不存在或者全景图设置有误，请重新设置！';
                exit;
            }
            
            $this->assign('shareimg',$slices[0]['link']);
            $this->assign('panorama', $panorama);
            $this->assign('panoramaid', $panoramaid);
            $this->display("panorama");
        }
		
        public function meta($panoramaid,$token) {
            
            // load slices
            $slices = M('panorama_slices')->join('tp_raw_image on tp_raw_image.id = tp_panorama_slices.raw_image_id')
                    ->where(array('tp_panorama_slices.panorama_id'=>$panoramaid, 'tp_panorama_slices.status'=>'1'))
                    ->order('tp_panorama_slices.sort asc')
                    ->field('tp_raw_image.link link')
                    ->select();
            
            if(!$slices){
                echo '数据不存在！请联系客服！';
                exit;
            }

            $metadata = simplexml_load_file(CONF_PATH."panorama_meta.xml");
            $maxSlices = count($slices);
            if($maxSlices > 6) {
                $maxSlices = 6;
            }
            $host = $_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"];
            for($i=0; $i<$maxSlices; $i++){
                $url = $slices[$i]['link'];
                $startIdx=stripos($url ,C('wx_handler_server'),0);
                $url = substr_replace($url,$host,$startIdx,strlen(C('wx_handler_server')));
                $metadata->input['tile'.$i.'url'] = $url;
            }

            $where = array('token'=>$token,'id'=>$panoramaid);
            $pano = M('panorama')->where($where)->field('id,nav_info,galleryid')->find();
            $nav_info = unserialize($pano['nav_info']);
            $navCount = count($nav_info);
            if($pano && $navCount>0){
                $i=0;
                foreach ($nav_info as $k => $val) {
                    if(!$nav_info || !$nav_info[$i] || !$val['album'] || !$val['pano']){ continue; }
                    $metadata->hotspots->hotspot[$i]['pan'] = $val['pan'];
                    $metadata->hotspots->hotspot[$i]['tilt'] = $val['tilt'];
                    $metadata->hotspots->hotspot[$i]['skinid'] = $val['title'];
                    $metadata->hotspots->hotspot[$i]['url'] = 'http://'.C('wx_handler_server').'/index.php/3d/p/'.$token.'/'.$val['album'].'/'.$val['pano'];
                    $metadata->hotspots->hotspot[$i]['id'] = $i;
                    $metadata->hotspots->hotspot[$i]['target'] = "_self";
                    //$metadata->hotspots->hotspot[$i]['title'] = $val['desc'];//去掉导航按钮的title显示
                    $i++;
                }
            }
            $data = $metadata->asXML();
            header('Content-Type:text/xml; charset=utf-8');
            exit($data);
        }
        
        public function gallerymeta(){
            if(!isset($_GET['token']) || !isset($_GET['galleryid'])) {
                echo '数据不存在！';
                exit;
            }
            
            $token = $_GET['token'];
            $galleryid = $_GET['galleryid'];
            
            // 获得该相册下所有的全景图
            $panoramas=M('panorama')->where(array('token'=>$token,'galleryid'=>$galleryid,'status'=>1))
                    ->order("sort asc")->select();
            
            // 获得该相册的元信息
            $gallery=M('gallery3d')->where(array('token'=>$token,'id'=>$galleryid))->find();
            
            if(!$panoramas || !$gallery) {
                echo '数据不存在!';
                exit;
            }
            
            // build json response for metadata
            $data["banner"] = $gallery['picurl'];
            $data["rooms"] = array();
            
            $room["id"] = $gallery['id'];
            $room["name"] = $gallery['title'];
            $room["desc"] = $gallery['info'];
            $room["rooms"] = "";
            $room["floor"] = "1";
            $room["area"] = "200";
            $room["simg"] = "";
            $room["bimg"] = "";
            $room["width"] = 1600;
            $room["height"] = 1600;
            $room["dtitle"] = array();
            array_push($room["dtitle"],  "");
            $room["dlist"] = array();
            $room["full3d"] = array();
            
            // full3d - item0
            $item["name"] = $gallery['title'];
            $item["list"] = array();
            foreach($panoramas as $pano) {
                $panodata = array();
                $panodata["name"] = $pano['title'];
                $panodata["url"] = 'http://'.C('wx_handler_server').'/index.php/3d/p/'.$token.'/'.$galleryid.'/'.$pano['id'];
                array_push($item["list"], $panodata);
            }
            $item["bimg"] = $gallery['picurl'];
            
            array_push($room["full3d"], $item);
            array_push($data["rooms"], $room);
            
            header('Content-type: application/json; charset=UTF-8');
            echo 'showRooms('. json_encode($data).');';
        }
        
	public function gallery($token, $galleryid){
            
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$token=$this->_get('token');
		if($token==false){
			echo '数据不存在';exit;
		}
		$gallery=M('gallery3d')->where(array('token'=>$token,'id'=>$galleryid))->find();
		
		$this->assign('gallery',$gallery);
		
		$this->display();
	}
}
?>

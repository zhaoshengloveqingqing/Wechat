<?php
require_once(COMMON_PATH.'/ServiceHelper.php');
class DiymenAction extends UserAction
{
    
    public function index()
    {
        //检查当前公众号信息
        $data = M('Wxuser')->where(array('token'=>$_SESSION['token']))->find();
        $init = strlen($data['appid']) > 0 && strlen($data['appsecret']) > 0;
        $this->assign('diymen',$init);
        $this->assign('userid',$data['id']);

        //获取系统业务
        $this->assign('services', ServiceHelper::getServices($this->token));
		
		$info = M('Wxuser')->where(array('token'=>$this->token))->find();
		$this->assign('address', $info['address']);
		
        //当前菜单
        $classTrees = $this->getMenuData();
        $this->assign('class',json_encode($classTrees));

        $this->display();
    }

    private function getMenuData()
    {
        $class = M('Diymen_class')->where(array('token' => $this->token,'pid'=>0))->order('sort desc')->select();
            
        $classTrees = array();
        $nid = 2;
        foreach ($class as $key=>$vo) 
        {   
            //主菜单数据
            unset($classTree);
            $classTree['text']      = $vo['title'];
            $classTree['id']        = $vo['id'];
            $classTree['sort']      = $vo['sort'];
            $classTree['type']      = $vo['type'];
            if ($vo['type'] == 'system') 
            {
                $classTree['service'] = $vo['keyword'];
            } 
            else
            {
                $classTree['keyword']   = $vo['keyword'];
            }

            $classTree['nid']       = "menu_tree_".$nid;

            unset($children);
            $children = array();
            $c = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->order('sort desc')->select();
            if ($c != false && count($c) > 0) 
            {
                //当前菜单有子菜单
                foreach ($c as $key2=>$vo2) 
                {
                    $child              = array();
                    $child['text']      = $vo2['title'];
                    $child['keyword']   = $vo2['keyword'];
                    $child['id']        = $vo2['id'];
                    $child['sort']      = $vo2['sort'];
                    $child['type']      = $vo2['type'];
                    $child['nid']       = "menu_tree_".$nid;
                    if ($vo2['type'] == 'system') 
                    {
                        $child['service'] = $vo2['keyword'];
                    } 
                    else
                    {
                        $child['keyword'] = $vo2['keyword'];
                    }
                    $nid++;
                    array_push($children, $child);
                }

                $classTree['children'] = $children;
            } 
            
            array_push($classTrees, $classTree);
            $nid++;
        }

        return $classTrees;
    }
    
    public function  class_add()
    {
        if(IS_POST)
        {
            if (isset($_GET['pid'])) 
            {
                $_POST['pid'] = $_GET['pid'];        
            }

            $menu_db = M('diymen_class');
            $data['keyword']     = $_POST['keyword'];
            $data['pid']         = $this->_get('pid','intval');
            $data['title']       = $_POST['title'];
            $data['type']        = $_POST['type'];
            $data['token']       = session('token');
            $data['sort']        = $_POST['sort'];
            if ($data['type'] == 'system') 
            {
                $data['keyword']     = $_POST['service'];
            }
             else if ($data['type'] == 'view')
            {
                $data['keyword']     = $_POST['url'];
            } else if ($data['type'] == 'navig')
            {
			    $data['keyword']    = 'navig';
            }
            $menu_id = $menu_db->add($data);

            if ($menu_id) 
            {
                $this->success('添加成功');
            }
            else
            {
                $this->error('添加失败，请稍后再试');
            }
        }
        else
        {
            $class = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort desc')->select();
            $this->assign('class',$class);
            $this->display();
        }
    }

    public function  class_del()
    {        
        $class = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$this->_get('id')))->order('sort desc')->find();
        if ($class==false) 
        {
            $back=M('Diymen_class')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
            if ($back==true)
            {
                $this->success('删除成功');
            }
            else
            {
                $this->error('删除失败');
            }
        }
        else
        {
            $this->error('请删除该分类下的子分类');
        }
    }

    public function  class_edit()
    {
        if(IS_POST)
        {
			
            $menu_id = $this->_get('id','intval');

            $where['id']     = $menu_id; 
            $where['token'] = session('token');

            $menu_db = M('diymen_class');
            $data['keyword']     = $_POST['keyword'];
            $data['pid']         = $this->_get('pid','intval');
            $data['title']       = $_POST['title'];
            $data['type']        = $_POST['type'];
            //$data['is_show']     = $_POST['is_show'];
            $data['sort']        = $_POST['sort'];
            if ($data['type'] == 'system') 
            {
                $data['keyword']     = $_POST['service'];
            }
            else if ($data['type'] == 'view')
            {
                $data['keyword']     = $_POST['url'];
            }
            else if ($data['type'] == 'navig')
            {
			    $data['keyword']    = 'navig';
            }
            $ret = $menu_db->where($where)->save($data);
			
            if ($ret == true) 
            {
                $this->success('操作成功',U(MODULE_NAME.'/index', array('id' => $menu_id )));
            } 
            else 
            {
                $this->success('保存失败',U(MODULE_NAME.'/index', array('id' => $menu_id )));
            }

        }
        else
        {
            $data = M('Diymen_class')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
            echo json_encode($data);
        }
    }
    
    function checkAccessToken($params = array()){
            require_once(COMMON_PATH.'/WeixinAPI.php');
			$weixin = new WeixinAPI($params['appid'], $params['appsecret']);
			return $weixin->getAccessToken();
    }
    

    public function  class_remove()
    {
        if(IS_GET)
        {
            //检查公众号信息
            $public = M('Wxuser')->where(array('token'=>session('token')))->find();
            if ($public['appid'] == false || $public['appsecret'] == false) 
            {
                $this->error('必须先填写【AppId】【 AppSecret】');
                exit;
            }

            if ($public['is_authed'] == 0 && $public['type'] == 0)
            {
                $this->error('您的公众号不能添加自定义菜单，请重新编辑您的公众号信息。');
                exit;
            }

			$access_token = $this->checkAccessToken($public);
       		 if (!$access_token) 
            {
                $this->error('获取access_token参数错误');
                exit;
            }
            /*$url_get = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$public['appid'].'&secret='.$public['appsecret'];
            $json = json_decode( file_get_contents($url_get));

            if ($json->errcode != 0) 
            {
                $this->error($json->errmsg);
                exit;
            }*/

            $url_remove = file_get_contents('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$access_token);
            $json = json_decode( file_get_contents($url_remove));
            if ($json->errcode != 0) 
            {
                $this->error($json->errmsg);
                exit;
            }
            else
            {
                $this->success('自定义菜单删除成功。');
            }
        }
    }

    public function  class_send()
    {
        if(IS_GET)
        {
            //检查公众号信息
            $public = M('Wxuser')->where(array('token'=>session('token')))->find();
            if ($public['appid'] == false || $public['appsecret'] == false) 
            {
                $this->error('必须先填写【AppId】【 AppSecret】');
                exit;
            }

            if ($public['is_authed'] == 0 && $public['type'] == 0)
            {
                $this->error('您的公众号不能添加自定义菜单，请重新编辑您的公众号信息。');
                exit;
            }

        	$access_token = $this->checkAccessToken($public);
       		 if (!$access_token) 
            {
                $this->error('获取access_token参数错误');
                exit;
            }
            /*$url_get = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$public['appid'].'&secret='.$public['appsecret'];
            $json = json_decode( $ojson);

            if ($json->errcode != 0) 
            {
                $this->error('请联系客服，错误码：'.$json->errcode.",错误提示".$json->errmsg);
                exit;
            }*/

            //添加公众号自定义菜单
            $button_arr = array('button' => array());
            
            $buttons = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->limit(3)->order('sort desc')->select();

            if ($buttons != false && count($buttons) > 0) 
            {
               foreach ($buttons as $key=>$vo)
                {
                    $button = array();//主菜单
                    $button['name'] = urlencode($vo['title']);
                    $sub_btns = M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->limit(5)->order('sort desc')->select();
                    if ($sub_btns == false)
                    {
                        
                        if ($vo['type'] == "click") 
                        {
                            $button['key']  = urlencode($vo['keyword']);
                            $button['type'] = $vo['type'];

                        } 
                        else if ($vo['type'] == "view")
                        {
                            $button['url']  = $vo['keyword'];
                            $button['type'] = $vo['type'];
                        }
                        else if ($vo['type'] == "system")
                        {
                            $button = array_merge($button, $this->getSystemServiceEntry($public,$vo['keyword']));
                        }
                        else if ($vo['type'] == "navig")
                        {
                            $button['url'] = $this->getNaviLink();
                            $button['type'] = 'view';
                        }
                        
                        //检查是否符合要求
                        if (empty($button['name']) || (strlen($button['name']) > 16 && strlen($vo['title']) > 16)) 
                        {
                            $this->error('菜单名"'.$vo['title'].'"不符合要求'.strlen($button['name']));
                        }
                        
                        if ($button['type'] == 'click' && (strlen($button['key']) > 128 || empty($button['key']))
                                || $button['type'] == 'view' && (strlen($button['url']) > 256 || empty($button['url']))) 
                        {
                            $this->error($vo['title'].'的菜单KEY值或者url不符合要求');
                        }
                    } 
                    else 
                    {
                        //子菜单
                        $button['sub_button'] = array();
                        foreach($sub_btns as $voo)
                        {
                            if ($voo['type'] == "click") 
                            {
                                $sub_btn = array('type' => $voo['type'],  'name' => urlencode($voo['title']), 'key' => urlencode($voo['keyword']));
                            } 
                            else if ($voo['type'] == 'view') 
                            {
                                $sub_btn = array('type' => $voo['type'],  'name' => urlencode($voo['title']), 'url' => $voo['keyword']);
                            } 
                            else if ($voo['type'] == "system")
                            {
                                $sub_btn = $this->getSystemServiceEntry($public,$voo['keyword']);
                                $sub_btn['name'] = urlencode($voo['title']);
                            }
                            else if ($voo['type'] == "navig")
                            {
                                $sub_btn['url'] = $this->getNaviLink();
                                $sub_btn['name'] = urlencode($voo['title']);
                                $sub_btn['type'] = 'view';
                            }

                            //检查是否符合要求
                            if (empty($sub_btn['name']) || strlen($sub_btn['name']) > 40 && strlen($voo['title']) > 40) 
                            {
                                $this->error('菜单名"'.$voo['title'].'"不符合要求');
                            }
                            
                            if ($sub_btn['type'] == 'click' && (strlen($sub_btn['key']) > 128 || empty($sub_btn['key']))) {
                                 $this->error($voo['title'].'的菜单关键词不符合要求');
                            }
                            
                            //url长度限制针对encode之前
                            if ($sub_btn['type'] == 'view' && (strlen($sub_btn['url']) > 256 || empty($sub_btn['url'] ))) {
                                Log::record('subbtn:'.strlen($sub_btn['url']).' :'.$sub_btn['url']);
                                Log::save();
                                $this->error($voo['title'].'的菜单外链网址不符合要求');
                            }
                            
                            if($sub_btn['type'] == 'view') {
                                $sub_btn['url'] = urlencode($sub_btn['url']);
                            }
                            
                            array_push($button['sub_button'], $sub_btn);
                        }
                    }

                    array_push($button_arr['button'], $button);
                }   
            }

            if (count($button_arr) > 0) 
            {

                Log::record('new menus:'.print_r($button_arr,true));
                $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
                $data = urldecode(json_encode($button_arr));
                $ret = $this->createNewMenu($url, $data);
                Log::record('ret:'.print_r($ret,true));
                Log::save();

                if ($ret->errcode == 0)
                {
                    $this->success('菜单创建成功,请重新关注，或者等待一会再查看菜单。');
                }
                else
                {
                    $this->error('请联系客服，错误码：'.$ret->errcode.",错误提示：".$ret->errmsg);
                }
            }
            else
            {
                $this->success('自定义菜单为空，若要删除，请点击“删除自定义菜单”按钮。');
            }
            exit;
        }
        else
        {
            $this->error('非法操作');
        }
    }

    private function getSystemServiceEntry($public_account, $service_tag)
    {
        if (empty($public_account) || empty($service_tag)) 
        {
            return array();
        }

        $button = array();

        $tags = explode('-', $service_tag);
         if ($tags[0] == 'wifi' || $tags[0] == 'canyin' || $tags[0] == 'hotel' || $tags[0] == 'dingdan'
            || $tags[0] == 'dazhuanpan' || $tags[0] == 'guaguaka' || $tags[0] == 'youhuiquan'
            || $tags[0] == 'toupiao' || $tags[0] == 'huiyuanka' )
        {
            $button['type'] = 'click';
            $button['key']  = urlencode($this->constructKeyword($this->token, $tags[0], $tags[1], $tags[2]));
        }
        else if ($tags[0] == 'shouye') 
        {
            if (empty($tags[1])) 
            {
                //这是首页，首页通过关键词进入
                $button['type'] = 'click';
                $button['key']  = urlencode($this->constructKeyword($this->token, $tags[0], $tags[1], $tags[2]));
            }
            else
            {
                //栏目或者文章页
                $button['type'] = 'view';
                $button['url']  = ServiceHelper::constructHyperLink($this->token, $tags[0], $tags[1], $tags[2]);
            }
            
        }
        else if ($tags[0] == 'car') 
        {
            if ($tags[1] == 'sales') 
            {
                $button['type'] = 'view';
                $button['url']  = ServiceHelper::constructHyperLink($this->token, $tags[0], $tags[1], $tags[2]);
            }
            else if ($tags[1] == 'car' || $tags[1] == 'drive' || $tags[1] == 'maintain' || $tags[1] == 'care') 
            {
                $button['type'] = 'click';
                $button['key']  = urlencode($this->constructKeyword($this->token, $tags[0], $tags[1], $tags[2]));
            }
        }
        else
        {
            if ($public_account['is_authed'] == 1 && $public_account['type'] == 1)
            {
                //认证过的服务号，url不带wechat_id
                $button['type'] = 'view';
                $button['url']  = ServiceHelper::constructHyperLink($this->token, $tags[0], $tags[1], $tags[2]);
            }
            else
            {
                //其他情况下用关键词实现
                $button['type'] = 'click';
                $button['key']  = urlencode($this->constructKeyword($this->token, $tags[0], $tags[1], $tags[2]));
            }
        }
        
        return $button;
    }

    private function constructKeyword($token, $function, $module='', $activity='')
    {
        $where = array('token'=>$token,'function'=>$function);
        if (!empty($module)) 
        {
            $where['module'] = $module;
        }

        if (!empty($activity)) 
        {
            $where['pid'] = $activity;
        }

        $keyword = M('keyword')->where($where)->find();
        if ($keyword != false) 
        {
            return $keyword['keyword'];
        }
    }

	private function getNaviLink() {
	    $info = M('Wxuser')->where(array('token'=>$this->token))->find();
		if (empty($info['address'])) {
		    $this->error('请先在基础设置中设置公司地址信息');
		}
        $navi_url = "http://api.map.baidu.com/marker?location=".$info['latitude'].','.$info['longtitude']
                .'&title='.urlencode($info['company']).'&output=html&src=lingzhtech';
		return $navi_url;
	}

    function createNewMenu($url, $data)
    {
        $header[] = "Accept-Charset: utf-8"; 
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

        $ret = curl_exec($ch); 
        if (curl_errno($ch)) 
        {  
            return json_decode('{"errcode":"-1","errmsg":"网络错误"}');
        }
        else
        {
            return json_decode($ret);
        }
    }
}
?>
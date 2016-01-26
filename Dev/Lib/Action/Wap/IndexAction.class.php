<?php
require_once(COMMON_PATH.'/LinkHelper.php');
class IndexAction extends BaseAction
{
    private $tpl;    //微信公共帐号信息
    private $info;    //分类信息
    private $web_setting;
    private $wecha_id;
    private $token;
    
    public function _initialize()
    {
        parent::_initialize();
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger") && empty($_GET['preview'])) 
        {
           // echo '此功能只能在微信浏览器中使用';exit;
        }

        $where['token'] = $this->_get('token','trim');
        if (empty($where['token'])) 
        {
            exit;
        }
        $where['status']    = 1;
        $wechat_public = D('Wxuser')->where($where)->find();

        if ($wechat_public != false) 
        {
            $web_setting = M('vweb_setting')->where(array('token'=>$wechat_public['token']))->find();
            $this->web_setting      = $web_setting;

            
            $this->tpl      = $wechat_public;
            $this->token    = $wechat_public['token'];
        }
        else
        {
            exit;
        }
        $this->wecha_id  = $this->_get('wecha_id','trim');
    }
    
    
    
        
    public function index() 
    {
        $where['token'] = $this->token;
        $flash = M('Flash')->where($where)->order('sorts desc')->select();
        $this->assign('flash',$this->constructFlashHyperLinks($flash));
        $count = count($flash);
        $this->assign('num',$count);
        

        $web_tmpls   = C('web_homepage_tmpl');
        $selected_tmpl = $web_tmpls[$this->web_setting['tmpl_id']];

        if (!isset($selected_tmpl['support_diy_bg_color']) 
            || $selected_tmpl['support_diy_bg_color'] == 0
            || $selected_tmpl['support_diy_bg_color'] == 1 && empty($this->web_setting['bg_color'])) 
        {
            //不支持自定义背景色 或者支持背景色但未设置自定义背景
            //则使用默认背景色
            $this->web_setting['bg_color'] = empty($selected_tmpl['default_bg_color']) ? '#fff' : $selected_tmpl['default_bg_color'];
        }

        if (!isset($selected_tmpl['support_diy_classify_color']) 
            || $selected_tmpl['support_diy_classify_color'] == 0) 
        {
            //不支持自定义栏目背景色 或者支持栏目背景色但未设置自定义背景
            //则使用默认背景色
            $this->web_setting['classify_bg_color'] = empty($selected_tmpl['default_classify_bg_color']) ? '#fff' : $selected_tmpl['default_classify_bg_color'];
            $this->web_setting['classify_font_color'] = empty($selected_tmpl['default_classify_font_color']) ? '#000' : $selected_tmpl['default_classify_font_color'];
        }
        else
        {
            //检查是否做了相应的栏目背景和字体颜色设置，否则使用默认色
            if (empty($this->web_setting['classify_bg_color'])) 
            {
                $this->web_setting['classify_bg_color'] = empty($selected_tmpl['default_classify_bg_color']) ? '#fff' : $selected_tmpl['default_classify_bg_color'];
            }

            if (empty($this->web_setting['classify_font_color'])) 
            {
                $this->web_setting['classify_font_color'] = empty($selected_tmpl['default_classify_font_color']) ? '#000' : $selected_tmpl['default_classify_font_color'];
            }
        }

        $info = M('Classify')->where(array('token'=>$this->token,'status'=>1,'parent'=>0))->order('sorts desc')->select();
        $this->assign('info',$this->contructClassifyHyperLinks($info));
        $this->assign('web_setting',$this->web_setting);
        $this->assign('tpl',$this->tpl);
                
        // index_book: 该模版需要获得每个栏目下的文章列表，白名单出来该模版，以减少不必要的开销
                
        if($this->web_setting['tmpl_id'] == 17) 
        {
            $articleMap  = array();
            $allArticles = M('article')
                            ->where(array(
                                    'token' => $this->token,
                                    'status' => 1
                                ))
                            ->order('c_id asc, sorts desc')
                            ->field('c_id, title')
                            ->select();
                    
            if($allArticles && is_array($allArticles)) 
            {
                foreach($allArticles as $article) 
                {
                    if(!isset($articleMap[$article['c_id']])) 
                    {
                        $articleMap[$article['c_id']] = array();
                    }
                            
                    array_push($articleMap[$article['c_id']], $article['title']);
                }
            }
                    
            $this->assign('articleMap', $articleMap);
        } 
        $navigationLink = "http://api.map.baidu.com/marker?location="
                        .$this->tpl['latitude'].','.$this->tpl['longtitude']
                        .'&title='.urlencode($this->tpl['company'])
                        .'&name='.urlencode($this->tpl['company'])
                        .'&content='.urlencode($this->tpl['address'])
                        .'&output=html&src=lingzhtech';
        $this->assign('navi', $navigationLink);
		$this->assign('tel', $this->tpl['telephone']);
		$this->assign('show_nav', $this->web_setting['show_nav']);
        $this->display($this->web_setting['tmpl_name']);
    }

        
    private function constructFlashHyperLinks($flashs)
    {
        foreach($flashs as &$flash) 
        {
            $flash['hyperlink'] = $this->contructFlashHyperLink($flash);
        }
        return $flashs;
    }
        

    private function contructFlashHyperLink($flash) 
    {
        $token          = $flash['token'];
        $linktype       = $flash['linktype'];
        $link_param_l1  = $flash['link_param_l1'];
        $link_param_l2  = $flash['link_param_l2'];

        switch($linktype){
            case 'nolink':
                return 'javascript:void(0)';
                break;
            default:
                return LinkHelper::constructHyperLink($token, $this->wecha_id, $linktype, $link_param_l1, $link_param_l2);
        }
    }
    
    public function lists() 
    {
        $cls_db = M('Classify');
        $cls_id = $this->_get('classid','intval');
        $classify = $cls_db->where(array('token'=>$this->token,'status'=>1,'id'=>$cls_id))->order('sorts desc')->find();
        
        if ($classify == false) 
        {
            exit;
        }
        $this->assign('classify', $classify);

        //父分类
        $parent_cls = $cls_db->where(array('token'=>$this->token,'status'=>1,'parent'=>0))->order('sorts desc')->select();
        $this->assign('info',$this->contructClassifyHyperLinks($parent_cls));

        $this->assign('tpl',$this->tpl);    
        $this->assign('token',$this->token);

        $p = $this->_get('p','intval',1);

        if ($classify['linktype'] == 'articles') 
        {
            $where['token'] = $this->token;
            $where['c_id']  = $this->_get('classid','intval');
            $where['status'] = 1;

            $db = D('Article');

            $count = $db->where($where)->count();
            $this->assign('page',(ceil($count/5)));
            $this->assign('p',$p);

            $articles = $db->where($where)->page($p.',5')->order('sorts desc')->select();
            //文章详细中含有html标签，需要去掉,并裁剪长度
            foreach ($articles as $key => $a) 
            {
                if (!empty($articles[$key]['content'])) 
                {
                    $articles[$key]['content'] = $this->remove_html_tag($articles[$key]['content']);
                    $articles[$key]['content'] = my_cus_substr(htmlspecialchars($articles[$key]['content']), 66);
                }
            }
            $this->assign('res',$this->contructArticleHyperLinks($articles));
        }
        else if ($classify['linktype'] == 'subClassifies')
        {
            $where['token']     = $this->token;
            $where['parent']    = $cls_id;
            $where['status']    = 1;

            $count = $cls_db->where($where)->count();
            $this->assign('page',(ceil($count/10)));
            $this->assign('p',$p);

            $sub_cls = $cls_db->where($where) ->field("id,name as title,img as pic, info as content, parent,token,linktype,link_param_l1,link_param_l2")->page($p.',10')->order('sorts desc')->select();

            $this->assign('res',$this->contructClassifyHyperLinks($sub_cls));
        } 
        $this->assign('wecha_id', $this->wecha_id);

        // genereate navigation link
        $navigationLink = "http://api.map.baidu.com/marker?location="
                        .$this->tpl['latitude'].','.$this->tpl['longtitude']
                        .'&title='.urlencode($this->tpl['company'])
                        .'&name='.urlencode($this->tpl['company'])
                        .'&content='.urlencode($this->tpl['address'])
                        .'&output=html&src=lingzhtech';
        $this->assign('navigationLink', $navigationLink);
        $this->display($classify['tmpl']);

    }
    
    public function content() {
        $db = M('Article');
        $where['token']  = $this->token;
        $where['status'] = 1;
        $where['id']=$this->_get('id','intval');
                
        $this->assign('info',$this->contructClassifyHyperLinks($this->info));    //分类信息
        $this->assign('tpl',$this->tpl);                //微信帐号信息

        $res=$db->where($where)->find();
        
                
        // 更新网页显示次数
        if(empty($_GET['preview'])) {
            $db->where($where)->setInc('click');
        }
        
        $params = array(
        	'token'=> $where['token'], 
        	'id'=> $where['id']
        );
        if ($res && $res['title']) {
        	$title = explode('|', $res['title']);
        	$res['title'] = $title > 0 ? trim($title[0]) : $res['title'];
        	$res['strcontent'] = $title > 0 ? (trim($title[1]) ? trim($title[1]) : $res['title']) : $res['title'];
        }
        $this->assign('url',C('site_url').U('Index/content', $params));  
        $this->assign('res',$res);            //内容详情;       
        $this->display($res['tmpl']);
    }
    

    private function remove_html_tag($str)
    {  //清除HTML代码、空格、回车换行符
        //trim 去掉字串两端的空格
        //strip_tags 删除HTML元素

        $str = trim($str);
        $str = @preg_replace('/<script[^>]*?>(.*?)<\/script>/si', '', $str);
        $str = @preg_replace('/<style[^>]*?>(.*?)<\/style>/si', '', $str);
        $str = @strip_tags($str,"");
        $str = @ereg_replace("\t","",$str);
        $str = @ereg_replace("\r\n","",$str);
        $str = @ereg_replace("\r","",$str);
        $str = @ereg_replace("\n","",$str);
        $str = @ereg_replace(" ","",$str);
        $str = @ereg_replace("&nbsp;","",$str);
        return trim($str);
    }
    
    private function contructArticleHyperLinks($articles) {
        foreach($articles as &$article) {
            $article['hyperlink'] = $this->constructArticleHyperLink($article);
            
        }
        return $articles;
    }
    
    
    
    private function constructArticleHyperLink($article) {
        $token = $article['token'];
        $linktype = $article['linktype'];
        $link_param_l1 = $article['link_param_l1'];
        $link_param_l2 = $article['link_param_l2'];

        switch($linktype){
            case 'articles':
                return U('Wap/Index/content',
                        array(
                            'id'=>$article['id'],
                            'token'=>$token,
                            'wxref'=>'mp.weixin.qq.com',
                            //'wecha_id' => $this->wecha_id
                            )
                        );
                break;
            default:
                return LinkHelper::constructHyperLink($token, $this->wecha_id, $linktype, $link_param_l1, $link_param_l2);
        }
    }
    
    private function contructClassifyHyperLinks($classifies){
        foreach($classifies as &$classify) {
            $classify['hyperlink'] = $this->contructClassifyHyperLink($classify);
        }
        return $classifies;
    }

    private function contructClassifyHyperLink($classify) {
        $token = $classify['token'];
        $linktype = $classify['linktype'];
        $link_param_l1 = $classify['link_param_l1'];
        $link_param_l2 = $classify['link_param_l2'];

        switch($linktype){
            case 'articles':
                return U('Wap/Index/lists',
                        array(
                            'classid'  => $classify['id'],
                            'token'    => $token,
                            'wxref'    =>'mp.weixin.qq.com',
                            'wecha_id' => $this->wecha_id
                            )
                        );
                break;
            case 'subClassifies':
                return U('Wap/Index/lists',
                        array(
                            'classid'  => $classify['id'],
                            'token'    => $token,
                            'wxref'    =>'mp.weixin.qq.com',
                            'wecha_id' => $this->wecha_id
                            )
                        );
                break;
            default:
                return LinkHelper::constructHyperLink($token, $this->wecha_id, $linktype, $link_param_l1, $link_param_l2);
        }
    }
    
}

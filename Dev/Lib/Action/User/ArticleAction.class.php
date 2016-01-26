<?php
require_once(COMMON_PATH.'/LinkHelper.php');
class ArticleAction extends UserAction
{

    protected function _initialize()
    {
        parent::_initialize();
        $this->function = 'shouye';
        parent::checkOpenedFunction();
    }

    //配置
    public function index()
    {
        
        $Model = new Model(); // 实例化一个model对象 没有对应任何数据表
        $articles = $Model->query("select a.id, a.title,a.sorts, a.click,a.updatetime,c.`name` as c_name, c.linktype as cls_type, a.linktype  linktype, a.link_param_l1  link_param_l1, a.link_param_l2  link_param_l2 , a.token token "
            ."from tp_article as a left JOIN tp_classify as c on a.c_id = c.id and (c.`status` =1 or c.`status` =0) "
            ."where a.status = 1  and a.token='$this->token'");

        $this->assign('articles',$this->generateLinkTypeDesc($articles));
        $this->display();
    }

    private function  generateLinkTypeDesc($lists) 
    { 
        $tmpLinkTypes = $this->getLinkTypes();
        $linkTypes = LinkHelper::buildId2RecordMap($tmpLinkTypes);

        $tmpmodules = LinkHelper::getModules();
        $modules = LinkHelper::buildId2RecordMap($tmpmodules);

        $tmpactivities = LinkHelper::getActivities();
        $activities = LinkHelper::buildId2RecordMap($tmpactivities);

        $tmpCarModules = LinkHelper::getCarModules();
        $carModules = LinkHelper::buildId2RecordMap($tmpCarModules);
         
            
        foreach($lists as &$l) 
        {
            $token = $l['token'];
            switch($l['linktype']) 
            {
                case 'articles':
                    $articleUrl = U('Wap/Index/content',
                        array(
                            'id'=>$l['id'],
                            'token'=>$token,
                            'wxref'=>'mp.weixin.qq.com'
                            )
                        );
                    $externLink = '<a style=" text-decoration:underline;" href="'.$articleUrl.'">'.'链接</a>';
                        
                    $l['linkdesc'] = $linkTypes['articles']['name'].'('.$externLink.')';
                    break;
                case 'linkurls':
                    $externLink = '<a style=" text-decoration:  underline" href="'.$l['link_param_l1'].'">'.'链接</a>';
                    $l['linkdesc'] = $linkTypes['linkurls']['name'].'('.$externLink.')';
                    break;
                case 'modules':
                    $l['linkdesc'] = $modules[$l['link_param_l1']]['name'];
                    break;
                case 'activitys':
                    $l['linkdesc'] = $activities[$l['link_param_l1']]['name'];
                    break;
                case 'car':
                    $l['linkdesc'] = $carModules[$l['link_param_l1']]['name'];
                    break;
                default:
                    $l['linkdesc'] = '未知';
                    break;
            }
        }
        return $lists;
    }

    public function getActivityDetail() 
    {
            
        $token = session('token');
        $activityId = $_POST['activityId'];
        $currentArticleId = intval($_POST['currentArticleId']);

        if(empty($token) || empty($activityId)) 
        {

            $this->ajaxReturn(array(), 'JSON');
        }

        $details = array();
        switch($activityId)
        {
            case 'dingdan':
                $details = LinkHelper::getOrders($token);
                break;
            case 'dazhuanpan':
                $details = LinkHelper::getLotteryList($token,1);
                break;
            case 'guaguaka':
                $details = LinkHelper::getLotteryList($token, 2);
                break;
            case 'youhuiquan':
                $details = LinkHelper::getLotteryList($token,3);
                break;
            case 'zajindan':
                $details = LinkHelper::getLotteryList($token,4);
                break;
            case 'toupiao':
                $details = LinkHelper::getVoteList($token); 
                break;
            case 'hotel':
                $details = LinkHelper::getHotels($token);
                break;
            default:
                break;
        }
            
        // load article information
        $selectedId = '';
        if(!empty($currentArticleId)) {
            $id = $this->_get('id','intval');
            $uid = session('uid');

            $db = M('article');
            $where['uid'] = $uid; 
            $where['token'] = $token;
            $where['id'] = $currentArticleId;
            $where['status'] = 1;
            $article = $db->where($where)->find(); 
            if($article) {
                if($article['link_param_l1'] == $activityId) {
                    $selectedId = $article['link_param_l2'];
                }
            }
        }
            
        $res = array();
        $res['details'] = $details;
        $res['selected'] = $selectedId;
        $this->ajaxReturn($res, 'JSON');
    }
        
    private function getLinkTypes() {
        return array_merge(
            array(array('id'=>'articles', 'name'=>'详细内容')),
            LinkHelper::getCommonLinkTypes());
    }
        
    public function addNewArticle() {
            
        $cls_id = $_POST['classid'];
        $cls = null;
        if ($cls_id != -1) {
            $db = M('Classify');
            $where['token']     = session('token');
            $where['id']        = $cls_id;
            $where['linktype']  = 'articles';
            $where['status']    = array('in',array('1','0'));
            $cls = $db->where($where)->find();
        }

        $article = M('article');
        $data['token']      = session('token');
        $data['uid']        = session('uid');
        $data['title']      = $_POST['title'];
        $data['content']    = $_POST['content'];
        
        $data['pic']        = $_POST['pic'];
        $data['sorts']      = $_POST['sorts'];
        $data['createtime'] = time();
        $data['updatetime'] = $data['createtime'];
        $data['display_pic']= intval($_POST['display_pic']);
        $data['display_title_time'] = intval($_POST['display_title_time']);

        if ($cls != false) 
        { 
            $data['c_id']       = $cls['id'];
            $data['c_name']     = $cls['name']; 
        }
                
        $data['linktype']       = $_POST['linktype'];
        $data['link_param_l1']  = isset($_POST['link_param_l1']) ? $_POST['link_param_l1'] : null ;
        $data['link_param_l2']  = isset($_POST['link_param_l2']) ? $_POST['link_param_l2'] : null;

        $tmpl_id = isset($_POST['tmplId']) ? $_POST['tmplId'] : '1' ;
        switch ($tmpl_id) {
            case '1':
                $data['tmpl'] = 'content_pic';
                break;
            case '2':
                $data['tmpl'] = 'content_author';
                break;
            case '3':
                $data['tmpl'] = 'content_simple';
                break;
            default:
                $data['tmpl'] = 'content_pic';
                break;
        }
        $data['status'] = 1;
        $a_id = $article->add($data);
        if ($a_id >= 0) {
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/add'));
        }
    }

    public function add()
    {
        $db = M('Classify');
        $where['token'] = session('token');
        $where['status'] = array('in',array('1','0'));
        // 只显示链接类型为文章列表的栏目， 其他栏目不能包含文章
        $where['linktype'] = 'articles';
        $cls = $db->where($where)->select();
        if ($cls == null) 
        { 
            $this->error('您还没有添加分类，请先添加分类再发表文章。',U('Classify/add'));
        }
        $this->assign('cls',$cls);
                
        $this->assign('linktypes', $this->getLinkTypes());
        $this->assign('allActivities', LinkHelper::getActivities());
        $this->assign('allModules', LinkHelper::getModules());
        $this->assign('carModules', LinkHelper::getCarModules());
        $this->display('edit');
    }

    public function edit()
    {
        $id = $this->_get('id','intval');
        $uid = session('uid');
        $token = session('token');

        $db = D('article');
        $where['uid'] = $uid; 
        $where['token'] = $token;
        $where['id'] = $id;
        $where['status'] = 1;
        $article = $db->where($where)->find(); 
        $this->assign('article',$article);

        $where = array();
        $where['token'] = $token;
        $where['status'] = 1;

        // 只显示链接类型为文章列表的栏目， 其他栏目不能包含文章
        $where['linktype'] = 'articles';
        $db = M('Classify');
        $cls = $db->where($where)->select();
        $this->assign('cls',$cls);

                
        $this->assign('linktypes', $this->getLinkTypes());
        $this->assign('allActivities', LinkHelper::getActivities());
        $this->assign('allModules', LinkHelper::getModules());
        $this->assign('carModules', LinkHelper::getCarModules());
                
        $this->display();
    }

    public function del() {
        $art_id = $this->_get('id','intval');
        $uid    = session('uid');
        $token  = session('token');

        $where['id']    = $art_id;
        $where['uid']   = $uid; 
        $where['token'] = $token;
        $data['status'] = 0;
        $ret = M('article')->where($where)->save($data);
        if ($ret >= 0) {
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
        }
    }

    public function update() {

        $cls_id = $_POST['classid'];
        $cls = null;
        if ($cls_id != -1) 
        {
            $db = M('Classify');
            $where['token']    = session('token');
            $where['id']       = $cls_id;
            $where['status']   = array('in',array('1','0'));
            $where['linktype'] = 'articles';
            $cls = $db->where($where)->find();
        }

        $art_where['id']        = $this->_post('id','intval');
        $art_where['uid']       = session('uid');
        $art_where['token']     = session('token');
        $art_where['status']    = 1;

        $article_db = M('article');
        $data['title']      = $_POST['title'];
        $data['content']    = $_POST['content'];
        $data['pic']        = $_POST['pic'];
        $data['display_pic']= intval($_POST['display_pic']);
        $data['sorts']      = $_POST['sorts'];

        if ($cls != false) 
        { 
            $data['c_id']       = $cls['id'];
            $data['c_name']     = $cls['name'];
        }
        
        $data['updatetime']         = time();
        $data['display_title_time'] = intval($_POST['display_title_time']);
                
        $data['linktype']       = $_POST['linktype'];
        $data['link_param_l1']  = isset($_POST['link_param_l1']) ? $_POST['link_param_l1'] : null ;
        $data['link_param_l2']  = isset($_POST['link_param_l2']) ? $_POST['link_param_l2'] : null;
                
        $tmpl_id = isset($_POST['tmplId']) ? $_POST['tmplId'] : '1' ;
        switch ($tmpl_id) {
            case '1':
                $data['tmpl']='content_pic';
                break;
            case '2':
                $data['tmpl']='content_author';
                break;
            case '3':
                $data['tmpl']='content_simple';
                break;
            default:
                $data['tmpl']='content_pic';
                break;
        }
        $ret = $article_db->where($art_where)->save($data);
        if ($ret >= 0) {
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/edit'));
        }
    }
}
?>

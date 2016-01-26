<?php
require_once(COMMON_PATH.'/LinkHelper.php');
/**
 *语音回复
**/
class ClassifyAction extends UserAction{

    protected function _initialize()
    {
        parent::_initialize();
        parent::checkOpenedFunction('shouye');
    }

    public function index()
    {
        $db     = D('Classify');
        $where['token']  = $this->token;
        $where['status'] = array('in',array('1','0'));

        $count = $db->where($where)->count();
        $page  = new Page($count,25);
        $this->assign('page',$page->show());

        $info=$db->where($where)->order('sorts desc')->limit($page->firstRow.','.$page->listRows)->select();
        $this->assign('info',$this->generateLinkTypeDesc($info));

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
        
        foreach($lists as &$l) {
            switch($l['linktype']) {
                case 'articles':
                    $l['linkdesc'] = '文章列表';
                    break;
                case 'linkurls':
                    $externLink = '<a style=" text-decoration:  underline" href="'.$l['link_param_l1'].'">链接</a>';
                    $l['linkdesc'] = $linkTypes['linkurls']['name'].'('.$externLink.')';
                    break;
                case 'subClassifies':
                    $l['linkdesc'] = '栏目列表';
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


    public function getActivityDetail() {
        $token = session('token');
        $activityId = $_POST['activityId'];
        $classifyId = intval($_POST['classifyId']);
        
        if(empty($token) || empty($activityId)) {

            $this->ajaxReturn(array(), 'JSON');
        }

        $details = array();
        switch($activityId){
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
            case 'pinglun':
                $details = LinkHelper::getReplyList($token);
                break;
            case 'yingxiang':
                $details = LinkHelper::getImpressList($token);
                break;
            default:
                break;
        }

        // load classify information
        $selectedId = '';
        if(!empty($classifyId)) {
            $where['id'] = $classifyId;
            $where['uid'] = session('uid');
            $where['token'] = session('token');
            $where['status'] = array('in',array('1','0'));
            $info=M('Classify')->where($where)->find();
            if($info) {
                if($info['link_param_l1'] == $activityId) {
                    $selectedId = $info['link_param_l2'];
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
                array(array('id'=>'articles', 'name'=>'文章列表'),array('id'=>'subClassifies', 'name'=>'次级分类列表')),
                LinkHelper::getCommonLinkTypes());
    }
        
    public function add()
    {
        //获取用户设置的首页模板主题
        $home = M('vweb_setting')->where(array('token'=>$this->token))->find();
        $home_tmpl = $home['tmpl_name'];

        $industry_mapping = array();

        $cls_db = M('classify');
        //获取已经有的分类
        $classify_count = $cls_db->where(array('token'=>session('token')))->count();
        $classify_count = $classify_count % 6;
        //获取当期那默认的图标
        $icon_url = 'http://wx.lingzhtech.com/themes/w/images/icons/cls_'.substr($home['tmpl_name'],6, 6).'_'.$classify_count.'.png';
        $this->assign('default_icon_url', $icon_url);
                
        $this->assign('linktypes', $this->getLinkTypes());
        $this->assign('allActivities', LinkHelper::getActivities());
        $this->assign('allModules', LinkHelper::getModules());
        $this->assign('carModules', LinkHelper::getCarModules());

        $other_where['token']    = $this->token;
        $other_where['status']   = array('in',array('1','0'));
        $other_cls = $cls_db->where($other_where)->select();
        $this->assign('otherCls',      $other_cls);
                
        $all_home_tmpls =  C('web_homepage_tmpl');
        $this->assign('homepage_template', $all_home_tmpls[$home['tmpl_id']]);

        $all_cls_tmpls =  C('web_classify_tmpl');
        $this->assign('classify_tmpls', $all_cls_tmpls);
                
        $this->display('edit');
    }
    
    public function sys_icon(){
        $sys_icon_list = array ("企业风采", "产品介绍", "服务介绍", "品牌介绍", "联系方式", "交通工具", "楼房", "新闻", "活动", "会员","餐饮");
        $count = count($sys_icon_list);
        for($i = 0; $i < $count; $i++) {
            $result[$i]["id"] = $i + 1;
            $result[$i]["name"] = $sys_icon_list[$i];
        }
        $this->ajaxReturn($result, "OK", 1);
    }
    
    public function sys_icon_list(){
        $id = $this->_get('id','intval');
        $sys_icon_index = array(7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 15);
        $result["t"] = $id;
        $icons = array();
        for($i = 1; $i <= $sys_icon_index[$id - 1]; $i++) {
            $icon["url"] = 'http://'.C('wx_handler_server').'/themes/a/images/sys_icons/'.$id.'_'.$i.'.png';;
            $icon["id"] = $id.'_'.$i;
            array_push($icons, $icon);
        }
        $result["icons"] = $icons;
        $this->ajaxReturn($result, "OK", 1);
    }
    
    public function edit(){
        $cls_id = $this->_get('id','intval');

        $where['id'] = $cls_id;
        $where['token'] = session('token');
        $where['status'] = array('in',array('1','0'));

        $classify_db = M('Classify');

        $info = $classify_db->where($where)->find();
        if ($info) 
        {
            $this->assign('info',$info);

            $other_where['token']    = session('token');
            $other_where['status']   = array('in',array('1','0'));
            $other_where['parent']   = array('neq',$cls_id);
            $other_cls = $classify_db->where($other_where)->select();
            $this->assign('otherCls',      $other_cls);

            $this->assign('linktypes',      $this->getLinkTypes());
            $this->assign('allActivities',  LinkHelper::getActivities());
            $this->assign('allModules',     LinkHelper::getModules());
            $this->assign('carModules',     LinkHelper::getCarModules());
            $this->assign('sub_cls',        $this->getSubClassifies($cls_id));
                        
            $home = M('vweb_setting')->where(array('token'=>session('token')))->find();
            $allTmpls =  C('web_homepage_tmpl');
            $this->assign('homepage_template', $allTmpls[$home['tmpl_id']]);

            $all_cls_tmpls =  C('web_classify_tmpl');
            $this->assign('classify_tmpls', $all_cls_tmpls);
                        
            $this->display();
        }
        else
        {
            $this->error('该栏目不存在',U(MODULE_NAME.'/index'));
        }
    }

    private function getSubClassifies($parent='')
    {
        if (!empty($parent)) 
        {
            $db     = D('Classify');
            $where['token']  = $this->token;
            $where['status'] = array('in',array('1','0'));
            $where['parent'] = $parent;

            $sub_cls = $db->where($where)->order('sorts desc')->select();
            return $sub_cls;
        }
    }
    
    public function del(){
        $where['id']      = $this->_get('id','intval');
        $where['uid']    = session('uid');
        $where['token'] = session('token');
        if(D(MODULE_NAME)->where($where)->save(array('status' => 2)))
        {
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        }
        else
        {
            $this->error('操作失败',U(MODULE_NAME.'/index'));
        }
    }


    public function addNewClassify(){
        $data['name']   = $_POST['name'];
        $data['img']    = $_POST['img'];
        $data['info']   = isset($_POST['info']) && strlen($_POST['info'])>0 ? substr($_POST['info'], 0, 90) :'';
        //$data['url'] = $_POST['url'];
        $data['sorts']  = $_POST['sorts'];
        $data['status'] = $_POST['status'];
        $data['token']  = session('token');
                
        $data['linktype']      = $_POST['linktype'];
        $data['link_param_l1'] = isset($_POST['link_param_l1']) ? $_POST['link_param_l1'] : null;
        $data['link_param_l2'] = isset($_POST['link_param_l2']) ? $_POST['link_param_l2'] : null;
        $data['slide_img']     = $_POST['slide_img'];
        $data['use_cover_img'] = $_POST['use_cover_img'];
               
        $tmpl_id = isset($_POST['tmplId']) ? $_POST['tmplId'] : '1' ;
        $all_classify_tmpls = C('web_classify_tmpl');
        if (isset($all_classify_tmpls[$tmpl_id])) 
        {
            $data['tmpl'] = $all_classify_tmpls[$tmpl_id]['file_name'];
        }
        else
        {
            //默认模板
            $data['tmpl'] = $all_classify_tmpls[1]['file_name'];
        }

        $ret = M('classify')->add($data);
        if ($ret >= 0) {
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/edit'));
        }
    }


    public function upsave()
    {

        $where['id']        = $this->_post('id','intval');
        $where['uid']       = session('uid');
        $where['token']     = session('token');
        $where['status']    = array('in',array('1','0'));

        $classify_db = M('classify');

        $data['name']       = $_POST['name'];
        $data['img']        = $_POST['img'];
        $data['info']       = isset($_POST['info']) && strlen($_POST['info'])>0? substr($_POST['info'], 0, 90) :'';
        $data['sorts']      = $_POST['sorts'];
        $data['status']     = $_POST['status'];
        $data['updatetime'] = time();

        $parent_id     = intval($_POST['parent']);
        if ($parent_id) 
        {
            $parent_cls = $classify_db->where(array('id' =>$parent_id , 'token'=>$this->token))->find();
            if ($parent_cls != false) 
            {
                $data['parent']     = $parent_id;
            }
        }
        else
        {
            $data['parent']     = $parent_id;
        }
        
        $data['slide_img'] = $_POST['slide_img'];
        $data['use_cover_img'] = $_POST['use_cover_img'];
                
        $data['linktype']       = $_POST['linktype'];
        $data['link_param_l1']  = isset($_POST['link_param_l1']) ? $_POST['link_param_l1'] : null;
        $data['link_param_l2']  = isset($_POST['link_param_l2']) ? $_POST['link_param_l2'] : null;

        $tmpl_id = isset($_POST['tmplId']) ? $_POST['tmplId'] : '1' ;
        $all_classify_tmpls = C('web_classify_tmpl');
        if (isset($all_classify_tmpls[$tmpl_id])) 
        {
            $data['tmpl'] = $all_classify_tmpls[$tmpl_id]['file_name'];
        }
        else
        {
            //默认模板
            $data['tmpl'] = $all_classify_tmpls[1]['file_name'];
        }

        $ret = $classify_db->where($where)->save($data);
        if ($ret >= 0) {
            $this->success('操作成功',U(MODULE_NAME.'/index'));
        } else {
            $this->error('操作失败',U(MODULE_NAME.'/edit'));
        }
    }
}
?>

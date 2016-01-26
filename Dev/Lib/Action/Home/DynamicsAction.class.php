<?php

class DynamicsAction extends BaseAction{

    protected $categories = array();

    public function _initialize(){
        parent::_initialize();
        $this->categories = C('LZH_NEWS_CATEGORIES');
    }

    public function index() {
        $cid = $_GET['cid'];

        if(empty($cid)) {
            $cid = '0';
        }
        $where['status'] = 1;
        $where['category_id'] = $cid;
        $sum = M('lingzh_news')->where($where)->count();
        $mod = 50;
        // 0 based
        $page = intval($_GET['page']);
        $limit = ($page ? ($page-1) * $mod : 0) . ',' . ($page ? $page * $mod : $mod);

        $prev = ($page - 1) < 0 ? 0 : ($page - 1);
        $next = ($sum > $page * $mod) ? $page + 1 : $page;

        $data = M('lingzh_news')->where($where)->order('id desc')->limit($limit)->select();
        //dump(M('lingzh_news')->getlastSql());
        $this->assign('data',$data);
        $this->assign('class',$this->$categories);
        $this->assign('cid', $cid);
        $this->assign('prev', $prev);
        $this->assign('next', $next);
        $this->assign('current', $page);
        $this->display();
    }
    
    public function details() {
        $cid = intval($_GET['cid']);
        // 动态文章的ID
        $item_id = intval($_GET['item']);
        $page = $_GET['page'] ? intval($_GET['page']) : 1;//page从1开始
        $where['status'] = 1;
        $where['category_id'] = $cid;
        $pre_con['id'] = array(array('gt',$item_id));
        $next_con['id'] = array(array('lt', $item_id));
        $curr_con['id'] = $item_id;
        
        $curr_data = M('lingzh_news')->where(array_merge($where,$curr_con))->find();
        $pre_data = M('lingzh_news')->where(array_merge($where,$pre_con))->order('id asc')->find();
        $next_data = M('lingzh_news')->where(array_merge($where,$next_con))->order('id desc')->find();
        if(empty($pre_data)){
            $pre_data = M('lingzh_news')->where($where)->order('id asc')->find();
        }
        if(empty($next_data)){
            $next_data = M('lingzh_news')->where($where)->order('id desc')->find();
        }

        $pre_item = array(
            'has'=>$pre_data ? 1 : 0 ,
            'url'=> U('Home/Dynamics/details', array('cid'=>$cid, 'item'=>$pre_data['id'])),
            'title'=>$pre_data['title'],
            );
        $next_item = array(
            'has'=>$next_data ? 1 : 0 ,
            'url' => U('Home/Dynamics/details', array('cid'=>$cid, 'item'=>$next_data['id'])),
            'title' => $next_data['title']
            );
        $this->assign('cid',$cid);
        $this->assign('prev_item', $pre_item);
        $this->assign('next_item', $next_item);
        $this->assign('curr_item', $curr_data);//若当前是第0页，则显示第一条，否则显示第二条
        $this->display();
    }
}
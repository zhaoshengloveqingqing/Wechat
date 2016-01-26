<?php 
/**
* 
*/
class LzhNewsAction extends UserAction
{
	protected $categories = array();

    public function _initialize(){
		parent::_initialize();
		if($this->uid!='2'){
			$this->error("非法操作。");
		}
		$this->categories = C('LZH_NEWS_CATEGORIES');
	}

	public function admin(){
		$where['status'] = 1;
		$where['category_id'] = $_GET['cid'] > 0 ? $_GET['cid'] : 0;//若id存在则获取分类id
		$_POST['key'] && $where['title'] = array('like', '%'.$_POST['key'].'%');//若key存在则获取搜索用的key

		$count = M('lingzh_news')->where($where)->count();
		$Page       = new Page($count,15);//分页
        $show       = $Page->show();
        $limit = $Page->firstRow.','.$Page->listRows;//分页的limit
		$data = M('lingzh_news')->where($where)->limit($limit)->select();
		$this->assign('data',$data);
		$this->assign('page',$show);
		$this->assign('categories',$this->categories);
		$this->display();
	}

	public function add_news(){
		if(IS_POST)
		{
			$data['create_time'] = time();
			$data['update_time'] = time();
			$data['added_uid'] = $this->uid;
			$data = array_merge($data,$_POST);
			$ret = M('lingzh_news')->add($data);
			$this->add_edit_done($ret);
		}
		$this->assign('categories',$this->categories);
		$this->display();
	}

	protected function add_edit_done($ret){
		if($ret){
			$this->success("操作成功！",U('LzhNews/admin'));
		}
		else{
			$this->error('操作失败，请稍后再试！');
		}
	}

	public function del_news(){
		$id = $_GET['nid'];
		$ret = M('lingzh_news')->where(array('id'=>$id))->setField('status',0);
		echo $ret ? 1 : 0 ;
	}

	public function edit_news(){
		$id = $_GET['nid'];
		$news = M('lingzh_news')->where(array('id'=>$id,'status'=>1))->find();
		if(!$news){
			$this->error('要编辑的文章不存在！');
			exit();
		}
		if(IS_POST){
			$data = $_POST;
			$data['update_time'] = time();
			$data['added_uid'] = $this->uid;
			$ret = M('lingzh_news')->where(array('id'=>$id))->save($data);
			$this->add_edit_done($ret);
		}
		$this->assign('categories',$this->categories);
		$this->assign('news',$news);
		$this->display('add_news');
	}


}
 ?>
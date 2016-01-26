<?php
/**
 *网站后台
 *@package YiCms
 *@author YiCms
 **/
 header("Content-Type: text/html; charset=utf-8");
class DepartmentAction extends UserAction{
	public function _initialize() {
        parent::_initialize();
		$this->token = $_SESSION['token']; 
    }
    
    function insertRootNode() {
    	if (!M("Department")->where(array('status'=>1, 'token' => $this->token))->find()) {
	    	$data = array(
	    		'name' => '根节点',
	    		'status' => '1',
	    	    'token' => $this->token,
	    		'pid' => 0
	    	);
	    	M("Department")->add($data);
		}
    }
    
    
	public function index(){
		$this->insertRootNode();
		$Node = D('Department')->getAllNode(array('status'=>'1', 'token'=>$this->token));
		$array = array();
		foreach($Node as $k => $r) {
			$r['id']      = $r['id'];
			$r['title']   = $r['title'];
			//$r['name']    = $r['name'];
			$r['submenu'] = $r['status']=='1' && $r['pid']!='0' ? "<a href='".U('Department/add',array('id'=>$r['id']))."'>添加子部门</a>" : '<font color="#cccccc">添加子部门</font>';
			$r['edit']    = $r['status']=='1' && $r['pid']!='0' ? "<a href='".U('Department/edit',array('id'=>$r['id'],'pid'=>$r['pid']))."'>修改</a>" : '<font color="#cccccc">修改</font>';
			$r['del']     = $r['status']=='1' && $r['pid']!='0' ? "<a href='".U('Department/del',array('id'=>$r['id']))."'>删除</a>" : '<font color="#cccccc">删除</font>';
			$r['status']  = $r['status']=='1' ? '<font color="blue">×</font>' : '<font color="red">√</font>';
			$array[]      = $r;
		}
		$str  = "<tr class='ListProduct'>
				    <td align='center'>\$id</td> 
				    <td align='center' style='text-align:left'>\$spacer \$name</td> 
					<td align='center'>
						\$submenu | \$edit | \$del
					</td>
				  </tr>";
  		$Tree = new Tree();
		$Tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$Tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$Tree->init($array);
		$html_tree = $Tree->get_tree(0, $str);
		$this->assign('html_tree',$html_tree);
		$this->display();
	}
	
	public function add(){
		if(isset($_POST['dosubmit'])) {
			$data['name']=$this->_post('name');
			$data['pid']= $this->_post('pid') ? $this->_post('pid') : 0;
			$data['status']='1';
			$data['token']= $this->token;
			$NodeDB = M("Department");
			//根据表单提交的POST数据创建数据对象
			$validate = array(
				array('name','require','名称必须填写！',1,'',3),
				array('pid','require','请选择部门！',1,'',3)
			);
			if ($NodeDB->where(array('name'=>$data['name'], 'pid'=>$data['pid']))->find()) {
				$this->error($data['name'].'已存在！');
			}
			if($NodeDB->validate($validate)->create()){
				if($NodeDB->add($data)){
    				$this->success('添加成功！',U('Department/index'));
				}else{
					 $this->error('添加失败!');
				}
			}else{
				$this->error($NodeDB->getError());
			}
		}else{
			$pid = $this->_get('id','intval',0);
			$Node = D('Department')->getAllNode(array('status'=>'1', 'token'=>$this->token));
			foreach($Node as $k => $r) {
				$r['id']         = $r['id'];				
				$r['name']       = $r['name'];
				$r['selected']   = $pid == $r['id'] ? 'selected' : '';
				$array[$r['id']] = $r;
			}
			$str  = "<option value='\$id' \$selected \$disabled >\$spacer \$name</option>";
			$Tree = new Tree();
			$Tree->init($array);
			$select_categorys = $Tree->get_tree(0, $str, $pid);
			$this->assign('tpltitle','添加');
			$this->assign('select_categorys',$select_categorys);
			$this->display();
		}
	}

	//编辑菜单
	public function edit(){
		$NodeDB = D('Department');
		$id = $this->_get('id');
		if (IS_POST) {
			$data['name']=$this->_post('name');
			$data['pid']=$this->_post('pid');
			//根据表单提交的POST数据创建数据对象
			if ($NodeDB->where(array('name'=>$data['name'], 'pid'=>$data['pid'], 'id'=>array('neq', $id)))->find()) {
				$this->error($data['name'].'已存在！');
			}
			$validate = array(
				array('name','require','名称必须填写！',1,'',3),
				array('pid','require','请选择部门！',1,'',3)
			);
			if($NodeDB->validate($validate)->where(array('id'=>$id))->create()){
				$NodeDB->save();
				$this->success('修改成功！',U('Department/index'));
			}else{
				 $this->error('修改失败!');
			}
		}else{
			$id = $this->_get('id');
			$pid = $this->_get('pid');
			if(!$id || !preg_match('/^\d+$/', $pid))$this->error('参数错误！');
			$department = $NodeDB->where((array('id'=>$id)))->find();
			$allNode = $Node = D('Department')->getAllNode(array('status'=>'1', 'token'=>$this->token));
			foreach($allNode as $k => $r) {
				$r['id']         = $r['id'];
				$r['title']      = $r['title'];
				$r['name']       = $r['name'];
				//$r['disabled']   = $department['id'] == $r['id'] ? '' : 'disabled';
				$array[$r['id']] = $r;
			}
			$str  = "<option value='\$id' \$selected \$disabled >\$spacer \$name</option>";
			$Tree = new Tree();
			$Tree->init($array);
			$select_categorys = $Tree->get_tree(0, $str, $pid);
			$this->assign('tpltitle','编辑');
			$this->assign('select_categorys',$select_categorys);
			$this->assign('department',isset($department['name']) ? $department['name'] : '');
			$this->assign('info', $NodeDB->getNode('id='.$id));
			$this->display();
		}
	}
	
	//删除菜单
	public function del(){
		$id = $this->_get('id');
		if(!$id)$this->error('参数错误!');
		$NodeDB = D('Department');
		$info = $NodeDB -> getNode(array('id'=>$id),'id');
		if($NodeDB->childNode($info['id'])){
			$this->error('存在子部门，不可删除!');
		}
		if($NodeDB->delNode('id='.$id)){
			$this->success('删除成功',U('Department/index'));
		}else{
			$this->error('删除失败!');
		}
	}
}
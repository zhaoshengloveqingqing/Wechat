<?php
class IndexAction extends BaseAction{
	private $tpl;	//微信公共帐号信息
	private $info;	//分类信息
	private $wecha_id;
	private $copyright;
	
	public function _initialize(){
		parent::_initialize();
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$where['token']=$this->_get('token','trim');
		$tpl=D('Wxuser')->where($where)->find();
		//dump($where);
		$info=M('Classify')->where(array('token'=>$this->_get('token'),'status'=>1))->order('sorts desc')->select();
		$gid=D('Users')->field('gid')->find($tpl['uid']);
		$copy=D('user_group')->field('iscopyright')->find($gid['gid']);	//查询用户所属组
		$this->copyright=$copy['iscopyright'];
		$this->wecha_id=$this->_get('wecha_id','intval');
		$this->info=$info;
		$this->tpl=$tpl;
	}
	
	
	public function classify(){
		$this->assign('info',$this->info);
		
		$this->display($this->tpl['tpltypename']);
	}
	
	public function index(){
		$where['token']=$this->_get('token');
		//dump($where);
	//	$where['status']=1;
	$flash=M('Flash')->where($where)->select();
		$count=count($flash);
		$this->assign('flash',$flash);
		$this->assign('info',$this->info);
		$this->assign('num',$count);
		$this->assign('info',$this->info);
		$this->assign('tpl',$this->tpl);
		$this->assign('copyright',$this->copyright);
		$this->display($this->tpl['tpltypename']);
		
	}
	
	public function lists(){
		$where['token']=$this->_get('token','trim');
		$db=D('Img');
		$p=$this->_get('p','intval',0);
		if($p) $p-=1;
		$where['classid']=$this->_get('classid','intval');
		$res=$db->where($where)->limit($p.',5')->select();
		$count=$db->where($where)->count();
		$p+=1;
		$this->assign('page',(ceil($count/5)));
		$this->assign('p',$p);
		$this->assign('info',$this->info);
		$this->assign('tpl',$this->tpl);
		$this->assign('res',$res);
		$this->assign('copyright',$this->copyright);
		$this->display($this->tpl['tpllistname']);
	}
	
	public function content(){
		$db=M('Img');
		$where['token']=$this->_get('token','trim');
		$where['id']=array('neq',(int)$_GET['id']);
		$lists=$db->where($where)->limit(5)->order('uptatetime')->select();
		$where['id']=$this->_get('id','intval');
		$res=$db->where($where)->find();
		$this->assign('info',$this->info);	//分类信息
		$this->assign('lists',$lists);		//列表信息
		$this->assign('res',$res);			//内容详情;
		$this->assign('tpl',$this->tpl);				//微信帐号信息
		$this->assign('copyright',$this->copyright);	//版权是否显示
		$this->display($this->tpl['tplcontentname']);
	}
	
	public function flash(){
		$where['token']=$this->_get('token','trim');
		$flash=M('Flash')->where($where)->select();
		$count=count($flash);
		$this->assign('flash',$flash);
		$this->assign('info',$this->info);
		$this->assign('num',$count);
		$this->display('ty_index');
	}
	
}
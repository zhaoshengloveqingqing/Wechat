<?php
class  SigninAction extends UserAction {

	public $open_sign	= 1; //是否开启签到
	public $df_integral = 5; //初始赠送积分
	public $sign_conf;
	public $sign_db;
	private $pagesize = 0;
	/*初始化*/
	public function _initialize() {
		parent::_initialize();

		$this->sign_conf = M('sign_conf');
		$this->sign_db   = D('sign_in');
		$this->pagesize   = 20;
		
		$this->function = 'sign';
        $this->token = $_SESSION['token']; 
        parent::checkOpenedFunction();

	}

	/*签到列表*/
	public function index(){
        $set_id 	= M('sign_set')->where(array('token'=>session('token')))->getField('id');

        $where 	= array('user.token'=>$this->token);
        //$where 		= array();
        if (IS_POST) {
	        $user_name	= $this->_post('username','htmlspecialchars,trim') ;
	       	$sort		= $this->_post('sort','trim');
	        $startdate	= strtotime($this->_post('startdate','trim'));
	        $enddate	= strtotime($this->_post('enddate','trim'));
        }else{
        	$user_name	= $this->_get('username','htmlspecialchars,trim');
	       	$sort		= $this->_get('sort','trim');
	        $startdate	= strtotime($this->_get('startdate','trim')); 
	        $enddate	= strtotime($this->_get('enddate','trim'));
        }
		if($startdate && $enddate){
			$day = 1*24*60*60;
			$where['sign_time'] = array(array('gt', $startdate + $day), array('lt', $enddate + $day),'and');
			$params['startdate'] = $startdate;
			$params['enddate'] = $enddate;
		}

		if($user_name){
			$where['user.username'] = array('like','%'.$user_name.'%');
			$params['user.username'] = $user_name;
		}

		if(empty($sort)){
			$order 	= 'sign_time desc';
		}else{
			$order 	= 'sign_time '.$sort;
		}
		$params['sign_time'] = $sort ? $sort : 'desc';

        $count = $this->sign_db
				->join('inner join '.C("DB_PREFIX").'sign_user user on user.`id` = `user_id`')
				->where($where)->count();
        $Page  = new Page($count, $this->pagesize);
       	if ($params) {
       		$Page->paramater = array_map('urlencode', $params);
       	}
        
        $list = $this->sign_db
				->join('inner join '.C("DB_PREFIX").'sign_user user on user.`id` = `user_id`')
				->where($where)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('search',array('startdate'=>$startdate,'enddate'=>$enddate,'sort'=>$sort));
        $this->assign('username', $user_name);
		$this->assign('page',$Page->show());
		$this->assign('list',$list);
		$this->assign('listinfo',1);
		$this->display('Signin/list');
	}

	/*签到奖励*/
	public function integral_conf(){
		$where = array('token'=>$this->token);
		$list = $this->sign_conf->where($where)->order(array('use'=>'desc','conf_id'=>'desc'))->select();
		$this->assign('list',$list);
		$this->assign('listinfo',2);
		$this->display('Signin/integral_conf');

	}

	/*设置连续签到奖励奖励*/

	public function add_integral(){
		$set = $this->sign_conf->where(array('token'=>$this->token,'conf_id'=>$this->_get('id','intval')))->find();
		if(IS_POST){
			//if($this->sign_conf->create() === false){

			//	$this->error($this->sign_conf->getError());
			//}else{
			$data = array();
			$data['integral'] 	= $this->_post('integral','intval');
			$data['stair']	 	= $this->_post('stair','intval');
			$data['use'] 		= $this->_post('use');
			$data['token'] 		= $this->token;

			if($data['integral']==0 || $data['stair']==0 ){
				$this->error('签到奖励和签到次数必须为大于0的整数');
				exit();
			}
			if($set){
				$this->sign_conf->where(array('token'=>$this->token,'conf_id'=>$this->_post('conf_id','intval')))->save($data);

				$this->success('修改成功',U("Signin/integral_conf",array('token'=>session('token'))));
			}else{
				$this->sign_conf->add($data);
				$this->success('设置成功',U("Signin/integral_conf",array('token'=>session('token'))));

			}
			//}
		}else{
			$this->assign('set',$set);
			$this->display('Signin/add_integral');
		}
		
	}


	/*删除签到奖励*/

	public function del_integral(){
		$conf_id 	= filter_var($this->_get('id'),FILTER_VALIDATE_INT);
		$where 		= array('conf_id'=>$conf_id,'token'=>session('token'));

		$del = $this->sign_conf->where($where)->delete();
		if($del){
			$this->success('操作成功',U("Signin/sign_conf",array('token'=>session('token'))));
		}else{
			$this->error('操作失败');
		}
	}



	/*签到配置*/

	public function set(){

		$set_db		= M('sign_set'); //签到设置
		$keyword_db	= M('keyword'); //关键词
		$where 	= array('token'=>$this->token);
		$set_info	= $set_db->where($where)->find();
		if(IS_POST){
			$data 				= array();
			$data['keywords'] 	= $this->_post('keywords','trim');
			$data['title'] 		= $this->_post('title','trim');
			$data['content'] 	= $this->_post('content','htmlspecialchars');
			$data['reply_img'] 	= $this->_post('reply_img','trim');
			$data['top_pic'] 	= $this->_post('top_pic','trim');
			$data['token'] 		= $this->token;
			if($set_info){
					$set_db->where($where)->save($data);
					$keyword['pid']		= $this->_post('id','intval');
                    $keyword['module']	= 'img';
                    $keyword['token']	= $this->token;
                    $keyword['keyword']	= $data['keywords'];
                    $keyword['function']	= $this->function;
                    
                    $keyword_db->where(array('token'=>$this->token,'pid'=>$this->_post('id','intval')))->save($keyword);
					$this->success('修改成功');
			}else{
				$id = $set_db->add($data);
				$keyword['pid']		= $id;
                $keyword['module']	= 'img';
                $keyword['token']	= $this->token;
                $keyword['keyword']	= $data['keywords'];
                $keyword['function']	= $this->function;
                $keyword_db->add($keyword);
				$this->success('设置成功');		
			}
		}else{
			if (!$set_info){
				$set_info['top_pic']=C('site_url').'/tpl/static/sign/top.jpg';
				$set_info['reply_img']=C('site_url').'/tpl/static/sign/r.jpg';
			}
			$this->assign('set',$set_info);
			$this->assign('listinfo',3);
			$this->display('Signin/set');
		}
	}
}

?>
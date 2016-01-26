<?php
/**
 * Class WscAction for weixin shop
**/
class WscAction extends UserAction{
	
	private $pagesize = 0;
	const DEPT = 'department';
	const PARTNER = 'partner';
	
	public function _initialize() {
		parent::_initialize();
		$this->token = $_SESSION['token']; 
		$this->pagesize = 20;
	}
	
	private function get_rand_num(){
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        $rand_id = mt_rand(1, 99999);
        return $rand_id+100000;
    }
	
	/**
	 * Desc :  partner list
	 *
	 * @author Jason Hu
	 * @since 2014-9-15
	 */
	public function partner_index(){
			$db = D('partner');
			$partner = C("DB_PREFIX").self::PARTNER;
			$dept = C("DB_PREFIX").self::DEPT;
			$params = array('partner.token'=>$this->token, 'partner.status'=>'1');
	        $search = isset($_POST['search'])&&$_POST['search'] ? $_POST['search'] : (isset($_GET['search'])&&$_GET['search'] ? $_GET['search'] : '');
	        if ($search) {
	        	$params = array('partner.token'=>$this->token, 'partner.status'=>'1', 'partner.name'=>$search);
	        }
			$count  = $db->table($partner.' partner')
					->join('left join '.$dept.' dept on partner.`dept_id` = dept.`id`')
					->where($params)
					->count();
	        $Page   = new Page($count, $this->pagesize);
	        $show   = $Page->show();
			$list = $db->table($partner.' partner')
					->join('left join '.$dept.' dept on partner.`dept_id` = dept.`id`')
					->field('partner.`id` as part_id, partner.`name` as part_name, partner.`num` as part_num, partner.`mobile`, partner.`address`, partner.`bank_account`, dept.`name` as dept_name,dept.`pid` as dept_pid')
					->where($params)
					->order('partner.create_time desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
			$this->assign('partner', $list);
			$this->assign('page', $show);
	        $this->assign('search',$search);
			$this->display('partner/partner');
	}
	
	
	/**
	 * Desc :  add partner
	 *
	 * @author Jason Hu
	 * @since 2014-9-15
	 */
	function add_partner() {
		$deptinfo   = M('department')->where(array('token'=>$this->token, 'status'=>'1'))->field('id,name')->select();
		//子部门
		$Node = D('Department')->where(array('token'=>$this->token))->order('id asc')->select();
		$pid = '0';	//选择子菜单
		$array = array();
		foreach($Node as $k => $r) {
			$r['id']         = $r['id'];
			$r['title']      = $r['title'];
			$r['name']       = $r['name'];
			$array[$r['id']] = $r;
		}
		$str  = "<option value='\$id' \$selected \$disabled >\$spacer \$name</option>";
		$Tree = new Tree();
		$Tree->init($array);
		$select_categorys = $Tree->get_tree(0, $str, $pid);
		$this->assign('tpltitle','添加');
		$this->assign('select_categorys',$select_categorys);
        $this->assign('dept',$deptinfo);
		$this->display('partner/modify_partner');    
	}
	
	/**
	 * Desc :  edit partner
	 *
	 * @author Jason Hu
	 * @since 2014-9-15
	 */
	function edit_partner() {
		$deptinfo = M('department')->where(array('token'=>$this->token, 'status'=>'1'))->field('id,name')->select();
		$partner  = M('partner')->table(C("DB_PREFIX").self::PARTNER.' partner')
				->join('left join '.C("DB_PREFIX").self::DEPT.' dept on partner.`dept_id` = dept.`id`')
				->field('partner.`id` as part_id, partner.`name` as part_name, partner.`num` as part_num, partner.`mobile`, partner.`address`, partner.`bank_account`, dept.`id` as dept_id, dept.`name` as dept_name')
				->where(array('partner.token'=>$this->token, 'partner.status'=>'1', 'partner.id'=>$this->_get('partner_id')))
				->find();
		//子部门
		$Node = D('Department')->where(array('token'=>$this->token))->order('id asc')->select();
		$pid =$this->_get('pids');	//选择子菜单
	
		$array = array();
		foreach($Node as $k => $r) {
			$r['id']         = $r['id'];
			$r['title']      = $r['title'];
			$r['name']       = $r['name'];
			$array[$r['id']] = $r;
		}
		$str  = "<option value='\$id' \$selected \$disabled >\$spacer \$name</option>";
		$Tree = new Tree();
		$Tree->init($array);
		$select_categorys = $Tree->get_tree(0, $str, $pid);
		$this->assign('tpltitle','添加');
		$this->assign('select_categorys',$select_categorys);
        $this->assign('dept',$deptinfo);
        $this->assign('partner',$partner);
		$this->display('partner/modify_partner');
	}
	
	
	/**
	 * Desc :  add/update partner
	 *
	 * @author Jason Hu
	 * @since 2014-9-15
	 */
	function save_partner() {
		if(IS_POST){
			$partner = D("Partner"); 
			
			$part_id = $this->_post('part_id'); 
			$data = array(
				'dept_id'=>$this->_post('dept_id'),
				'name'=>$this->_post('part_name'),
				'mobile'=>$this->_post('mobile'),
				'bank_account'=>$this->_post('bank_account'),
				'address'=>$this->_post('address'),
				'token'=>$this->token,
				'status'=>'1'
			);
			$validate = array(
				array('name','require','名称必须填写！',1,'',3),
				array('num','/^[\da-zA-Z]*$/','请输入仅包含数字和字母的编号！',2,'',3),
				array('mobile','require','手机号码必须填写！',1,'',3),
				array('mobile','/^1(3|5|8)\d{9}$/','请输入正确的手机号码！',1,'',3),
				array('bank_account','/^\d{16,19}$/','请输入正确的银行卡号！', 2,'',3),
				array('address','require','地址必须填写！',1,'',3)
			);
			C('TOKEN_ON', false);
			if (empty($part_id)) {
				$data = array_merge($data, array('num'=>$this->get_rand_num(), 'create_time'=>time()));
				$validate = array_merge($validate, array('num','','编号已经存在！',1,'unique',1));
				if ($partner->where(array('name'=>$data['name']))->find()) {
					$this->error($data['name'].'已存在！');
				}
				if($partner->validate($validate)->create($data)){
					$partner->add();
					$this->success('保存成功', U('Wsc/partner_index'));
				}else{
					$this->error($partner->getError());
				}
			}else{
				if ($partner->where(array('name'=>$data['name'], 'id'=>array('neq', $part_id)))->find()) {
					$this->error($data['name'].'已存在！');
				}
				if($partner->validate($validate)->where(array('id'=>$this->_post('part_id')))->create($data)){
					$partner->save();
					$this->success('保存成功', U('Wsc/partner_index'));
				}else{
					$this->error($partner->getError());
				}
			}
		}else{
			$Node = D('Department')->getAllNode();
			$pid = '0';	//选择子菜单
			$array = array();
			foreach($Node as $k => $r) {
				$r['id']         = $r['id'];
				$r['title']      = $r['title'];
				$r['name']       = $r['name'];
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
	
	/**
	 * Desc : delete partner
	 *
	 * @author Jason Hu
	 * @since 2014-9-15
	 */
	function del_partner() {
		if(D('partner')->where(array('id'=>$this->_get('partner_id')))->save(array('status'=>'0'))){
			$this->success('操作成功',U(MODULE_NAME.'/partner_index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/partner_index'));
		}
	}
	
	/**
	 * Desc : import partner 
	 *
	 * @author Jason Hu
	 * @since 2014-9-15
	 */
	public function import_partner(){	
		try {
			$token = session("token");
			$tmp_file = $_FILES ['partner'] ['tmp_name'];
			$file_types = explode ( ".", $_FILES ['partner'] ['name'] );
			$file_type = $file_types [count ( $file_types ) - 1];
		
			 /*判别是不是.xls文件，判别是不是excel文件*/
			if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
				$this->error('不是Excel文件，重新上传' );
			}
			/*设置上传路径*/
		    $savePath = $_SERVER['DOCUMENT_ROOT'].'/customer_imgs/'.$token.'/';
	        if (!file_exists($savePath)){
	            mkdir($savePath);
	        }
			/*以时间来命名上传的文件*/
			$file_name = date('Ymdhis'). "." . $file_type;
			/*是否上传成功*/
			ini_set('display_errors',1);
	        error_reporting(E_ALL);
			if (!move_uploaded_file( $tmp_file, $savePath . $file_name )) {
				$this->error ( '上传失败' );
			}
			
			$alldept = M('department')->where(array('token'=>$token, 'status'=>'1'))->field('id, name')->select();
			$dept = array();
			foreach ($alldept as $v) {
				$dept[$v['id']] = $v['name'];
			}
			$allpart = M('partner')->where(array('token'=>$token, 'status'=>'1'))->field('mobile')->select();
			$partner = array();
			foreach ($allpart as $v) {
				$partner[] = $v['mobile'];
			}
			       
			import('ORG.ExcelToArrary');
			$ExcelToArrary = new ExcelToArrary();//实例化
			$res = $ExcelToArrary->read($savePath.$file_name, "UTF-8", $file_type);//传参,判断office2007还是office2003
			
			$insertCount = 0;
	        $data = array();
	        $filterSame = array();
	        $same = array();
	        foreach ( $res as $k => $v ){
			    // skip the title
			    if ($k == 1) continue;
	            if ($filterSame && in_array($v[0], $filterSame)) {
	            	$same[] = $v[0];
	            }
	            if((!$partner || !in_array($v[3], $partner)) && !in_array($v[0], $filterSame)){
	               $filterSame[] = $v[0];
	               $data[$insertCount]['num'] = $v[0];
	               $data[$insertCount]['name'] = $v[1];
	               $data[$insertCount]['dept_id'] = in_array($v[2], $dept) && ($key=array_search($v[2], $dept)) ? $key : 0;
	               $data[$insertCount]['mobile'] = $v[3];
	               $data[$insertCount]['bank_account'] = $v[4];
	               $data[$insertCount]['address'] = $v[5];
	               $data[$insertCount]['token'] = $token;
	               $data[$insertCount]['status'] = '1';
	               $data[$insertCount]['create_time'] = time();
	               $insertCount = $insertCount + 1;
	            }
	        }
			$result = M("partner")->addAll($data);
			if( $insertCount > 0 ? $result : true ) {
	            $msg='合作伙伴资料导入成功，导入合作伙伴'.$insertCount."个。";
	            if(count($same)>0){
	                $msg .= "有".count($same)."个重复。";
	            }
	            $this->success('导入成功',U('Wsc/partner_index'));
	        } else {
	            $this->error('服务器繁忙，请稍候再试',U(MODULE_NAME.'/partner_index'));
	        } 
		} catch (Exception $e) {
			echo $e->getMessage();die;
		}
    }
	
		
	/**
	 * Desc :  generate qrcode
	 *
	 * @since 2014-9-15
	 */
	public function qr(){
        $partner_id = $this->_get('partner_id'); 
        $partner_db = M('partner');
        $where = array('id' => $partner_id);
        $partner = $partner_db->where($where)->find();
        if ($partner){
            if (!empty($partner['qrcode_pic_url'])) {
                $this->assign('qrcode_url',$partner['qrcode_pic_url']);
            } else {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
                $product_url = 'http://'.C('wx_handler_server').U('Wsc/Shop/index', array('partner_id'=>$partner_id,'token'=>$this->token));
				//exit($product_url);
                $gen->build($product_url, 'partner', $this->token);
                $qrcode_pic_url = $gen->getUrl();
                $partner_db->where($where)->save(array('qrcode_pic_url'=>$qrcode_pic_url));
                $this->assign('qrcode_url',$qrcode_pic_url);
            }
            $this->display('partner/qr');
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/partner_index'));
        }
    }
    
	/**
	 * Desc :  department list
	 *
	 * @author Jason Hu
	 * @since 2014-9-12
	 */
	public function department_index(){
		$dept    = D('department');
        $list  = null;
        $show  = null;
        
        $params = array('token'=>$this->token, 'status'=>'1','pid'=>'0');
        $search = isset($_POST['search'])&&$_POST['search'] ? $_POST['search'] : (isset($_GET['search'])&&$_GET['search'] ? $_GET['search'] : '');
        if ($search) {
        	$params = array('token'=>$this->token, 'status'=>'1', 'name'=>$search);
        }
        
        $count  = $dept->where($params)->count();
        $Page   = new Page($count, $this->pagesize);
        $show   = $Page->show();

        $list   = $dept->where($params)->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('search',$search);
        $this->assign('page',$show);    
        $this->assign('dept',$list);
        $this->display('department/department'); 
	}
	
	
	/**
	 * Desc : add department
	 *
	 * @author Jason Hu
	 * @since 2014-9-12
	 */
	function add_department(){
        $this->display('department/modify_department');    
	}
	
	/**
	 * Desc : edit department
	 *
	 * @author Jason Hu
	 * @since 2014-9-12
	 */
	function edit_department(){
		$dept   = M('department');
        $list   = $dept->where(array('token'=>$this->token, 'status'=>'1', 'id'=>$_GET['id']))->find();
        $this->assign('dept',$list);
        $this->display('department/modify_department');    
	}
	
	/**
	 * Desc : delete department
	 *
	 * @author Jason Hu
	 * @since 2014-9-12
	 */
	function del_department() {
		if(D('department')->where(array('id'=>$this->_get('id')))->save(array('status'=>'0'))){
			$this->success('操作成功',U(MODULE_NAME.'/department_index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/department_index'));
		}
	}
	
	/**
	 * Desc : save department
	 *
	 * @author Jason Hu
	 * @since 2014-9-12
	 */
	function save_department() {
		$dept_db = D('department');
		if (isset($_POST['dept_id']) && $_POST['dept_id']) {
			$data = array(
				'name'=>$_POST['dept_name'],
				'token'=>$this->token,
				'status'=>'1',
				'pid'=>'0',
				'title'=>$_POST['dept_name'],
				'create_time'=>time()
			);
			if (empty($data['name'])) {
				$this->error('请输入部门名称', U(MODULE_NAME.'/edit_department?id='.$_POST['dept_id']));
			}elseif($dept_db->where(array('token'=>$this->token, 'status'=>'1', 'name'=>$data['name'], 'id'=>array('neq',$_POST['dept_id'])))->find()){
				$this->error('该部门已经存在，请重新修改',U(MODULE_NAME.'/edit_department?id='.$_POST['dept_id']));
			}elseif($dept_db->where(array('id'=>$_POST['dept_id']))->save($data)){
				$this->success('操作成功', U(MODULE_NAME.'/department_index'));
			}else{
				$this->error('操作失败', U(MODULE_NAME.'/department_index?id='.$_POST['dept_id']));
			}
		}else{
			$data = array(
				'name'=>$_POST['dept_name'],
				'token'=>$this->token,
				'status'=>'1',
				'pid'=>'0',
				'title'=>$_POST['dept_name'],
				'create_time'=>time()
			);
			if (empty($data['name'])) {
				$this->error('请输入部门名称', U(MODULE_NAME.'/add_department'));
			}elseif($dept_db->where(array('token'=>$this->token, 'status'=>'1', 'name'=>$data['name']))->find()){
				$this->error('该部门已经存在，请重新添加',U(MODULE_NAME.'/add_department'));
			}elseif($dept_db->add($data)){
				$this->success('操作成功',U(MODULE_NAME.'/department_index'));
			}else{
				$this->error('操作失败',U(MODULE_NAME.'/add_department'));
			}
		}
	}
	
	
	/**
	 * Desc :  import department
	 *
	 * @author Jason Hu
	 * @since 2014-9-12
	 */
	public function import_dept(){	
		$token = session("token");
		$tmp_file = $_FILES ['department'] ['tmp_name'];
		$file_types = explode ( ".", $_FILES ['department'] ['name'] );
		$file_type = $file_types [count ( $file_types ) - 1];
	
		 /*判别是不是.xls文件，判别是不是excel文件*/
		if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls"){
			$this->error('不是Excel文件，重新上传' );
		}
		/*设置上传路径*/
	    $savePath = $_SERVER['DOCUMENT_ROOT'].'/customer_imgs/'.$token.'/';
        if (!file_exists($savePath)){
            mkdir($savePath);
        }
		/*以时间来命名上传的文件*/
		$file_name = date('Ymdhis'). "." . $file_type;
		/*是否上传成功*/
		ini_set('display_errors',1);
        error_reporting(E_ALL);
		if (!move_uploaded_file( $tmp_file, $savePath . $file_name )) {
			$this->error ( '上传失败' );
		}
		
		$alldept = M('department')->where(array('token'=>$token, 'status'=>'1'))->field('name')->select();
		$dept = array();
		foreach ($alldept as $v) {
			$dept[] = $v['name'];
		}
		
		import('ORG.ExcelToArrary');
		$ExcelToArrary = new ExcelToArrary();//实例化
		$res = $ExcelToArrary->read($savePath.$file_name, "UTF-8", $file_type);//传参,判断office2007还是office2003
		
		$insertCount = 0;
        $data = array();
        $filterSame = array();
        $same = array();
        foreach ( $res as $k => $v ){
		    // skip the title
		    if ($k == 1) continue;
            if ($filterSame && in_array($v[1], $filterSame)) {
            	$same[] = $v[1];
            }
            if((!$dept || !in_array($v[1], $dept)) && !in_array($v[1], $filterSame)){
               $filterSame[] = $v[1];
               $data[$insertCount]['name'] = $v[1];
               $data[$insertCount]['token'] = $token;
               $data[$insertCount]['status'] = '1';
               $data[$insertCount]['create_time'] = time();
               $insertCount = $insertCount + 1;
            }
        }
		$result = M("department")->addAll($data);
		if( $insertCount > 0 ? $result : true ) {
            $msg='会员资料导入成功，导入会员'.$insertCount."个。";
            if(count($same)>0){
                $msg .= "有".count($same)."个重复。";
            }
            $this->success('导入成功',U('Wsc/department_index'));
        } else {
            $this->error('服务器繁忙，请稍候再试',U(MODULE_NAME.'/department_index'));
        } 
    }
	 /**
	 * Desc :查询订单
	 *
	 * @author Morgan Zhao
	 * @since 2014-9-15
	 */
	 public function shop_orders()
    {
    	$partner = C("DB_PREFIX").self::PARTNER;
		$partner_db=D('partner');
        $b2c_order = C("DB_PREFIX").'b2c_order';
    	//$fxs_id=$_GET['partner_id'];
		$db=D('reguser');
    	$pagesize = 20;
        $order_db = M('b2c_order');
		$this->partner_id=$this->_get('partner_id');
		$partner_id=$_GET['partner_id'];
        $where = array($b2c_order.'.token'=> $this->token, $b2c_order.'.partner_id'=>$this->partner_id);
		$params['partner_id'] = $this->partner_id;
		
		$res=$partner_db->where("id='$partner_id'")->select();
		$num=$res[0]['num'];
		$pname=$res[0]['name'];
		$fxs=$db->where("num='$num'")->select();
		$fxs_id=$fxs[0]['id'];
		//echo $partner_db->getLastSql();
		
        if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
            if (empty($key))
            {
                $this->error("关键词不能为空");
            }

            $map['token']               = $this->token; 
            //$map['id']           = $this->branch_id; 
            //$map['status']              = 1; 
            $map['truename|pname|tel|fxs_id']  = array('like',"%$key%"); 
			
			$count=$db->count();
			$page=new Page($count,25);
			$sql = "SELECT r.truename as pname, r.openid as ropenid,r.id as fxs_id,p.*,k.* "
							." FROM tp_b2c_order as k  INNER JOIN tp_reguser as r on k.fxs_id = r.id  INNER JOIN tp_partner as p on r.num=p.num "
							."where r.status=1 and r.token='$this->token' and p.id='$partner_id'";
            	if(!empty($key)){
	                $sql .= ' AND (k.`truename` LIKE \'%'.mysql_real_escape_string($key).'%\'OR r.`truename`LIKE\'%'.mysql_real_escape_string($key).'%\' OR p.`num` LIKE \'%'.mysql_real_escape_string($key).'%\')';
	            }
				$sql .= " ORDER BY k.update_time DESC ";
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;
				$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
				$orders = $Model->query($sql);
        }else{
			if (isset($_GET['status']))
            {
				
				$status=$_GET['status'];
				
				$sql = "SELECT r.truename as pname, r.openid as ropenid,r.id as fxs_id,p.*,k.* "
							." FROM tp_b2c_order as k  INNER JOIN tp_reguser as r on k.fxs_id = r.id  INNER JOIN tp_partner as p on r.num=p.num "
							."where r.status=1 and r.token='$this->token' and p.id='$partner_id' and k.status='$status'";
			   
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

				$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
				$orders = $Model->query($sql);
			}else{
				
				$count=$db->where("status='$status'")->count();
			
				$page=new Page($count,25);
				
				//$info=$db->where(array('token'=>$this->token, 'status'=>1))->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
				$sql = "SELECT r.truename as pname, r.openid as ropenid,r.id as fxs_id,p.*,k.* "
							." FROM tp_b2c_order as k  INNER JOIN tp_reguser as r on k.fxs_id = r.id  INNER JOIN tp_partner as p on r.num=p.num "
							."where r.status=1 and r.token='$this->token' and p.id='$partner_id'";
			   
				//$sql .= " limit ".$Page->firstRow.','.$Page->listRows;

				$Model = new Model(); // 实例化一个model对象 没有对应任何数据表
				$orders = $Model->query($sql);
				
				$res=$db->where("id='$fxs_id'")->select();
				
				//$openid=$res[0]['openid'];
				//$orders=$order_db->where("token='$this->token' and fxs_id='$fxs_id' or partner_id='$partner_id'")->select();
				}
		}
		$fxs_id=$orders[0]['fxs_id'];
        $unHandledCount = $order_db->where(array('token'=> $this->token, 'fxs_id'=> $fxs_id, 'status'=> array(array('eq',1),array('eq',3), 'or') ))->count();
        $this->assign('unhandledCount',$unHandledCount);

		$this->assign('info',$info);
        $this->assign('orders',$orders);
		$this->assign('pname',$pname);
        $this->assign('page',$show);
        $this->display('partner/orders/orders');
    }
}
?>
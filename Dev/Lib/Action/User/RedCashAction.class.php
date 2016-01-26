<?php
class RedCashAction extends UserAction{
	
	private $pagesize = 0;
	public function _initialize() {
		parent::_initialize();
		$this->token = session('token');
		$this->pagesize = 20;
		
		$this->function = 'redcash';
        parent::checkOpenedFunction();
	}
	
	public function setting_list(){
		$where['token'] = $this->token;
		$where['status'] =  array('neq', '3');
		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
		if ($key) {
			$where['act_name'] = array('like',"%$key%");
		}
		$count = M('redcash_setting')->where($where)->count();
		$page = new Page($count, $this->pagesize);
		if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		$setting_list = M('redcash_setting')->where($where)->order('create_time desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('setting', $setting_list);
		$this->display('cash/setting_list');
	}
	
	function setting_modify(){
		if (IS_POST) {
			$setting = array(
				'token' => $this->token, 
				'create_time' => date('Y-m-d H:i:s'),
				'keyword' => $this->_post('keyword'),
				'act_name' => $this->_post('act_name'),
				'start_time' => $this->_post('start_time'), 
				'end_time' => $this->_post('end_time'), 
				'fixed_amount'=> $this->_post('fixed_amount'),
				'nick_name' => $this->_post('nick_name'),
				'send_name' => $this->_post('send_name'),
				'remark' => $this->_post('remark'), 
				'wishing' => $this->_post('wishing'),
				'update_time' =>date('Y-m-d H:i:s')
			);
			
			$validate = array(
				array('keyword', 'require', '关键词必须填写！', 1, '', 3),
				array('act_name', 'require', '请配置活动名称！', 1, '', 3),
				array('start_time', 'require', '请配置活动开始时间！', 1, '', 3),
				array('end_time', 'require', '请配置活动结束时间！', 1, '', 3),
				array('fixed_amount', 'require', '请配置红包金额！', 1, '', 3),
				array('nick_name', 'require', '请配置活动提供方名称！', 1, '', 3),
				array('send_name', 'require', '请配置商户名称！', 1, '', 3),
				array('remark', 'require', '请设置备注信息！', 1, '', 3),
				array('wishing', 'require', '请设置祝福语！', 1, '', 3)
			);
			
			if (M('redcash_setting')->validate($validate)->create()) {
				if ($this->_post('setting_id')) {
					$bak =M('redcash_setting')->where(array('id' => $this->_post('setting_id'), 'token' => $this->token))->save($setting);
					$keyword['pid'] = $this->_post('setting_id');
                    $keyword['module']	= 'text';
                    $keyword['token']	= $this->token;
                    $keyword['keyword']	= $setting['keyword'];
                    $keyword['function']	= $this->function;
					M('keyword')->where(array('pid' => $keyword['pid'], 'token' => $keyword['token'], 'function' => $keyword['function']))->delete();
					M('keyword')->add($keyword);
				}else{
					$setting['update_time'] = date('Y-m-d H:i:s');
					$bak =M('redcash_setting')->where(array('token' => $this->token))->add($setting);
					if ($bak) {
						$keyword['pid'] = $bak;
	                    $keyword['module']	= 'text';
	                    $keyword['token']	= $this->token;
	                    $keyword['keyword']	= $setting['keyword'];
	                    $keyword['function']	= $this->function;
						M('keyword')->add($keyword);
					}
				}
				if ($bak) {
					$this->success('保存成功', U('RedCash/setting_list'));
				}else{
					$this->error('保存失败', U('RedCash/setting_list'));
				}
			}else{
				$this->error(M('redcash_setting')->getError());
			}
		}else {
			$setting = M('redcash_setting')->where(array('token' => $this->token, 'id' => $this->_get('id')))->find();
			if ($setting) {
				$setting['fixed_amount'] = intval($setting['fixed_amount'] ) ;
			}
			$this->assign('setting', $setting);
			$this->display('cash/setting_modify');
		}
	}
	
	public function wxconf(){
		if (IS_POST) {
			$ssl_cert = preg_replace('#^'.C('site_url').'\/#', '', $this->_post('ssl_cert')); 
			$ssl_key = preg_replace('#^'.C('site_url').'\/#', '', $this->_post('ssl_key'));
			$ssl_cainfo = preg_replace('#^'.C('site_url').'\/#', '', $this->_post('ssl_cainfo'));
			
			if (!$ssl_cert || !preg_match('/\.pem$/i', $ssl_cert)) {
				$this->error('请输入pem格式的证书');
			}
			if (!$ssl_key || !preg_match('/\.pem$/', $ssl_key)) {
				$this->error('请输入pem格式的证书密钥');
			}
			if (!$ssl_cainfo || !preg_match('/\.pem$/', $ssl_cainfo)) {
				$this->error('请输入pem格式的CA证书');
			}
			
			$wxconf = array(
				'token' => $this->token, 
				'appid' => $this->_post('appid'), 
				'key' => $this->_post('key'),  
				'ssl_cert' => $ssl_cert, 
				'ssl_key' => $ssl_key, 
				'ssl_cainfo' => $ssl_cainfo,
				'update_time' => date('Y-m-d H:i:s') 
			);
			if ($this->_post('wxconf_id')) {
				$bak = M('redcash_wxconf')->where(array('id' => $this->_post('wxconf_id'), 'token' => $this->token))->save($wxconf);
			}else{
				$wxconf['create_time'] = date('Y-m-d H:i:s');
				$bak =M('redcash_wxconf')->where(array('token' => $this->token))->add($wxconf);
			}
			if ($bak) {
				$this->success('保存成功', U('RedCash/wxconf'));
			}else{
				$this->error('保存失败', U('RedCash/wxconf'));
			}
		}else{
			$wxconf = M('redcash_wxconf')->where(array('token' => $this->token))->find();
			$this->assign('conf', $wxconf);
			$this->assign('token', $this->token);
			$this->display('cash/wxconf');
		}
	}
	
	
	function change_status() {
		$status = $this->_get('status') ;
		$cash_id = $this->_get('id');
		$bFlag = M('redcash_setting')->where(array('token' => $this->token, 'id' => $cash_id))->save(array('status' => $status));
		if ($status == '3') {
			$cash = M('redcash_setting')->where(array('token' => $this->token, 'id' => $cash_id))->find();
			if($cash && $bFlag) {
				M('Keyword')->where(array('pid' => $cash_id,'token'=>$this->token, 'function'=>'redcash'))->delete();
			} 
		}
		header("Location:/index.php?g=User&m=RedCash&a=setting_list");
	}
	
	public function cash_list(){
		$setting_id = $this->_get('id');
		if(IS_POST){
			$key = $this->_post('searchkey');
        }else{
        	$key = $this->_get('search');
        }
		
        $model = new Model(); 
        $sql = " SELECT  COUNT(*) as num  FROM `tp_redcash_list` 
					 INNER JOIN `tp_wecha_user` ON `tp_wecha_user`.`wecha_id` = `tp_redcash_list`.`openid`
					 WHERE `tp_redcash_list`.`err_code` = 'SUCCESS'";
		if ($key) {
			$sql .= ' AND `nickname` LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
		$cash_count = $model->query($sql);
		$count = $cash_count ? $cash_count[0]['num'] : 0;
		
		$page = new Page($count, $this->pagesize);
	 	if ($key) {
			$page->parameter = array_map('urlencode', array('search'=>$key));
			$this->assign('search', $key);
		}
		
		$sql = " SELECT  IFNULL(`nickname`, '匿名') as nickname, IFNULL(`headimgurl`, '/themes/a/images/logo.jpg') as headimgurl, CAST((`total_amount`)/100 AS SIGNED) as total_amount, `mch_billno`, `err_code_des`
					 FROM `tp_redcash_list` 
					 INNER JOIN `tp_wecha_user` ON `tp_wecha_user`.`wecha_id` = `tp_redcash_list`.`openid` AND `tp_wecha_user`.`token` = `tp_redcash_list`.`token` 
					 WHERE `err_code` = 'SUCCESS' AND  `tp_redcash_list`.`token` = '{$this->token}' AND `cashsetting_id` = $setting_id";
		if ($key) {
			$sql .= ' AND `nickname` LIKE \'%'.mysql_real_escape_string($key).'%\'';
		}
        $sql .= " ORDER BY `create_time` DESC";
        $sql .= " LIMIT ".$page->firstRow.', '.$page->listRows;
        $cash = $model->query($sql);
        $this->assign('info', $cash);
		$this->display('cash/list');
	}
}
?>
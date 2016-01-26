<?php
 header("Content-Type: text/html; charset=utf-8");

class AppAction extends WapAction
{
    private $branch_id;
	private $pagesize = 0;
	const ORDER = 'b2c_order';
	const PRODUCT = 'b2c_product';
    protected function _initialize()
    {
        parent::_initialize();		
		session_start();
    }
    public function test(){
		$dir=$_GET['url']; //获取图片地址
		$file = fopen($dir,"r"); // 打开文件
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: ".filesize($dir));
		Header("Content-Disposition: attachment; filename=" . $dir);
		echo fread($file,filesize($dir));
		fclose($file);
		exit;
	}
    public function activity()
    {
		
       $this->display();
    }
	public function appoint(){
		$this->display();
	
	}
	//二维码列表
	public function qr_list(){
		$appoint_db=D('appointment');
		$orders_count = $appoint_db->count();
		
		$Page   = new Page($orders_count,20);
		$show   = $Page->show();
		$list = $appoint_db->where($where)->order('status ASC, create_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		
		$this->assign('list',$list);			
		$this->assign('page',$show);
		$this->display();
	}
	public function qr_code(){
	$this->display();
	}
	//导出excel
	public function export_excel(){
		
		$appoint_db=D('appointment');
		$appointments   = $appoint_db->where("customer_name is not null")->order('status desc, create_time desc')->select();
		
		$data = array();
		$ret = array();
		foreach ( $appointments as $k => $v ){
			    // skip the title
			   $data['id'] = $v['id'];
			   $data['customer_name'] = $v['customer_name'];
			   $data['mb'] =$v['mb'];
			   $data['status'] = $v['status'];
			   $data['mb_model'] = $v['mb_model'];
			   $data['mb_color'] =  $v['mb_color'];
			   $data['idnum'] =  $v['idnum'];
			   $data['capcity'] =  $v['capcity'];
			   $data['create_time'] =  $v['create_time'];
			   //$data['qrcode_pic_url'] =  $v['qrcode_pic_url'];
			   $data['mb_style'] =  $v['mb_style'];
			   $data['develop_name'] =  $v['develop_name'];
			   //$data['img'] =  $v['img'];
			   
			   $ret[]=$data;
	        }
		
			
	$this->create_excel($ret);


	}
	function create_excel($data, $filename = 'export.xls') {
		if(count($data)) {
			// Output the excel header
			header("Content-Type: application/vnd.ms-excel");
			header("Content-disposition: attachment; filename=".$filename);

			$header = true;
			$keys = array();
			foreach($data as $row) {
				$row = (array) $row;
				if($header) {
					foreach(array_keys($row) as $key) {
						$keys []= $key;
					}
					echo implode("\t", $keys)."\n";
					$header = false;
				}
				$out = array();
				foreach($keys as $key) {
					$out []= $row[$key];
				}
				echo implode("\t", $out)."\n";
			}
		}
		else {
			return false;
		}
	}
	
	public function qr(){
		$ver=$_GET['ver'];
		$store=$this->_post('store');
		$appoint_db=D('appointment');
		$data['develop_name']=$this->_post('develop_name');
		$data['code']='LgX4xmIYEtvA7b6azux3UQ==';
		$this->token='20140911';
		//exit($data['address']);
        if ($ver!=null){
            if (!empty($partner['qrcode_pic_url'])) {
                $this->assign('qrcode_url',$partner['qrcode_pic_url']);
            } else {
                import("@.ORG.qrcode.QRCodeGenerator");
                $gen = new QRCodeGenerator();
				//$data['qrcode_pic_url']=$qrcode_pic_url;
				$id=$appoint_db->add($data);
                $product_url = 'http://'.C('wx_handler_server').U('App/App/activity', array('store'=>$store,'id'=>$id));
				$res['qrcode_pic_url']=$product_url;
				
				//exit($product_url);
                $gen->build($product_url, 'partner', $this->token);
                $qrcode_pic_url = $gen->getUrl();
				$res['img']=$qrcode_pic_url;
				$res=$appoint_db->where("id='$id'")->save($res);				
                $this->assign('qrcode_url',$qrcode_pic_url);
				$this->assign('link',$product_url);
            }
            $this->display('qr');
        } else {
            $this->error('该记录不存在', U(MODULE_NAME.'/qr_code'));
        }
    }
	public function order_search(){
		$ver_code=$_SESSION['code'];
		$appoint_db=D('appointment');
		if (IS_POST)
        {
            //搜索已添加产品
            $key = $this->_post('searchkey');
			//exit($key);
            /*if (empty($key))
            {
                $this->error("请填写手机号码");
            }*/
            $map['mb']  = array('like',"%$key%"); 
			$map['code'] =$ver_code;
            $product_count   = $appoint_db->where($map)->count(); 
            $Page   = new Page($product_count,50);
            //默认通过$_GET['p'] 获取当前页面
            $show   = $Page->show();
            $appointments   = $appoint_db->where($map)->order('status desc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$developcode='AHHFF00182 ';
			$this->assign('developcode',$developcode);
			$this->assign('appointments',$appointments);
			$this->display('appoint_order');
        }else{
			$this->display();
		}
		
	}
	//预约订单查询
	public function appoint_order(){
		$_SESSION['code']=$_POST['code'];
		
		$appoint_db=D('appointment');
		$res=$appoint_db->select();
		$vercode=$res[0]['code'];
		if($_POST['code']==$vercode)
		{
			
			$orders_count = $appoint_db->count();
			
			$Page   = new Page($orders_count,20);
			$show   = $Page->show();
			$appointments = $appoint_db->where("customer_name is not null")->order('status ASC, create_time DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
			
			$this->assign('appointments',$appointments);
			$developcode='AHHFF00182 ';
			$this->assign('developcode',$developcode);
			$this->assign('page',$show);
			$this->display();
		}else{		
			$this->display();
		}
		
	}
	//验证预约订单密码
	public function ver_code(){
	
	$code=$_POST['code'];
		
	}
	//生成短信验证码
	function rand_number ($min, $max) {
        return sprintf("%0".strlen($max)."d", mt_rand($min,$max));
    }
	//发送短信验证码
	public function getcode() {
		
		$code = $this->rand_number(100000, 999999);		
		 //普通提交方式
		include_once('HttpClient.class.php');
		//目标主机的地址，我这里填上测试的地址
		$Client = new HttpClient("mssms.cn:8000");
		$url = "http://222.185.228.25:8000/msm/sdk/http/sendsmsutf8.jsp";
		//请求的页面地址      
		//ＰＯＳＴ的参数
		$mobile=$this->_post('mobile');
		
		$params = array('username'=>"JSMB260705",'scode'=>"095169",'mobile'=>$mobile,'content'=>"@1@=$code",'tempid'=>"MB-2013102300");      
		$pageContents = HttpClient::quickPost($url, $params);      
		echo "提交返回=".$pageContents; 
		exit();
        $token          = '540e6acfz71';
        $wecha_id       = $this->_get('wecha_id');
        $tel            = '15678456321';
        $cardnum        = 'LZ948820080012';

        $where = array('token'=>$token, 'tel'=>$tel, 'cardnum'=>$cardnum);
        //根据电话号码验证会员是否被导入
        $userinfo = M("physical_member")->where($where)->find();
        if(!$userinfo){
            echo '{"success":0,"msg":"没有找到您的会员卡信息，请联系商家导入。"}';
            exit();
        }
        elseif($userinfo['binded']){
            echo '{"success":0,"msg":"此手机号已被绑定."}';
            exit();
        }elseif ($userinfo['cardnum'] != $cardnum) {
            echo '{"success":0,"msg":"您输入的手机号与卡号不匹配,请重新输入。"}';
            exit();
        }
        
        $Cache = Cache::getInstance('File',array('expire'=>'310'));
        $scode = $Cache->get($token.$tel);
        if (!empty($scode)) {
            // 避免多次发送
            echo '{"success":0,"msg":"验证码已发送，请稍侯。"}';
            exit;
        }
        $code = $this->rand_number(100000, 999999);
        $Cache->set($token.$tel, $code);
        Log::record("Send code : ".$code." at ". date("Y-m-d H:i:s")."\r\n", Log::DEBUG);       
        
        include(LIB_PATH.'Action/SmsSender.class.php');
        $smsSender = new SmsSender();

        $binding_tmpl = C('card_binding');
        $smsContent = '';
        if (isset($binding_tmpl)) 
        {
            $smsContent = str_replace("#merchant#", '领众科技',           $binding_tmpl);
            $smsContent = str_replace("#code#", $code,  $smsContent);
        }

        if ($smsContent != '') 
        {
            $re = $smsSender->notify($this->_get('token'),"huiyuan", $smsContent, $tel);
            if ($re == 0) 
            {
                $Cache->set($token.$tel, $code);
                echo '{"success":1,"msg":"验证码已发送，请输入收到的短信验证码!"}';
            }
            else
            {
                echo '{"success":0,"msg":"验证码发送失败，请稍侯再试。"}';
            }
        }
        else
        {
            echo '{"success":0,"msg":"验证码发送失败，请稍侯再试。"}';
        }
    }
	//支付宝支付
	public function post (){
		$id=$this->_get('id');
		$appoint_db=D('appointment');
		$customer=$this->_post('customer_name');
		$res=$appoint_db->select();
		if($_POST['mb']==$res[0]['mb']){
			$this->error('此手机号已预约过，请重新输入新的手机号码');
		}
		if($_POST['idnum']==$res[0]['idnum']){
			$this->error('此身份证号码已预约过，请重新输入新的身份证号');
		}
		$data=array(
			'customer_name'=>$_POST['customer_name'],
			'mb'=>$_POST['mb'],
			'idnum'=>$_POST['idnum'],
			'mb_model'=>$_POST['mb_model'],
			'capcity'=>$_POST['capcity'],
			'mb_color'=>$_POST['mb_color'],
			'mb_style'=>$_POST['mb_style'],
			'create_time'=>time()
			
		);
		
		$res=$appoint_db->where("id='$id'")->save($data);
		
		if(!$res){
			echo $appoint_db->getDbError();
		}
		if($res){
			$this->success('预约成功', U('App/activity'));
		}
		/*
		//exit($customer);
		$total_fee='100';
		$username=$_SESSION['username'];
		//exit($username);
		//if($total_fee==false||$_SESSION['username']==false)$this->error('价格和用户名必须填写');
		import("@.ORG.Alipay.AlipaySubmit");
		//支付类型
		$payment_type = "1";
		//必填，不能修改
		//服务器异步通知页面路径
		$notify_url = C('site_url').U('User/Alipay/notify');
		//需http://格式的完整路径，不能加?id=123这类自定义参数
		//页面跳转同步通知页面路径
		$return_url = C('site_url').U('User/Alipay/return_url');
		//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
		//卖家支付宝帐户
		$seller_email =trim(C('alipay_name'));
		 //商户订单号
		$out_trade_no = $this->user['id'].'_'.time();
		//商户网站订单系统中唯一订单号，必填
		//订单名称
		//$subject ='充值vip'.$this->_post('group').'会员'.$this->_post('num').'个月';
		$subject='Iphone6预约付款';
		//必填
		//付款金额
		//$total_fee =(int)$_POST['price'];

        $body = 'vip高级会员服务费';
        //商品展示地址
        $show_url = C('site_url').U('Home/Index/price');
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html

        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1
		$body = $subject;
		
		$show_url = rtrim(C('site_url'),'/');

		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service" => "create_direct_pay_by_user",
			"partner" =>trim(C('alipay_pid')),
			"payment_type"	=> $payment_type,
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"seller_email"	=> $seller_email,
			"out_trade_no"	=> $out_trade_no,
			"subject"	=> $subject,
			"total_fee"	=> $total_fee,
			"body"	=> $body,
			"show_url"	=> $show_url,
			"anti_phishing_key"	=> $anti_phishing_key,
			"exter_invoke_ip"	=> $exter_invoke_ip,
			"_input_charset"	=>trim(strtolower('utf-8'))
		);
		

	//建立请求
	$alipaySubmit = new AlipaySubmit($this->setconfig());
	$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
	echo $html_text;
	*/
	}
	
	public function setconfig(){
	
		$alipay_config['partner']		= trim(C('alipay_pid'));
		//安全检验码，以数字和字母组成的32位字符
		$alipay_config['key']			= trim(C('alipay_key'));
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');
		//ca证书路径地址，用于curl中ssl校验
		//请保证cacert.pem文件在当前文件夹目录中
		$alipay_config['cacert']    = getcwd().'\\Dev\\Lib\\ORG\\Alipay\\cacert.pem';
		
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';		
		return $alipay_config;
	}
	
	

}

<?php /**
* 获取put，delete方法的数据可以使用$this->_put()、$this->delete()
*/
class RestApiAction extends BaseAction
{
	/**
	*分发的映射
	**/
	protected static $action_map = array('post'  =>'create',
										'get'   =>'read',
										'put'   =>'update',
										'delete'=>'delete',
										);

	protected $token;
	protected $rid;

	function __construct()
	{
		if($this->checkLogin())
		{
			$this->rid   = $_SESSION['manage_dine_branch'];
			$this->token = $_SESSION['manage_company_token'];
		}
	}

    protected function _initialize()
	{ 
		Log::record( date("Y-m-d h:i:s"),true );
		Log::save();
	}

	/**
	*所有API action的方法都应该是protected的，由此处做分发
	**/
	public function index()
	{	
		$this->handleMethods();
	}

	/**
	*空方法处理
	**/
	protected function _empty($method,$args)
	{
		$this->handleMethods();
		//$this->error('empty method ' . $method . ' not found in ' . get_class($this));
	}

	protected function handleMethods()
	{
		$r_method = $this->request_method;
		$api_action = RestApiAction::$action_map[$r_method];
		if(method_exists($this, $api_action))
		{
			call_user_func(array($this,$api_action));
		}
		else
		{
			$this->error('from init ,method ' . $api_action . ' not found in ' . get_class($this));
			Log::record('error' . print_r($r_method) . ' at ' . get_class($this) . '\n',true);
		}
	}

	/**
	*获取put，post，get，delete等数据的处理，并在此处做验证
	**/
	public function __get($name)
	{
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		$name = strtolower($name);
		switch ($name) 
		{
			case 'put':
				if($method == 'put')
				{
					$content = file_get_contents('php://input');
					$input = json_decode( $content ,true );
				}
			case 'post':
				if( !isset($input) && $method == 'post') 
				{
					$content = file_get_contents('php://input');
					$input = json_decode( $content ,true );
				}
			case 'get':
				if( !isset($input)  && $method == 'get') {  $input = $this->_get(); }
			case 'delete':
				if( !isset($input) && $method == 'delete') { $input = $this->_get(); }
				if($method == $name)//保证HTTP的方法维持rest风格
				{
					return $input;
				}
				else
				{
					$this->api_error($name);
				}
				break;
			case 'request_method':
				return $method;
				break;
			default:
				return parent::get($name);
				break;
		}
	}

	protected function _get()
	{
		$para = $_GET[C('VAR_URL_PARAMS')];
		$args = array();
		if($para)
		{
			$action_name = str_replace('action','',strtolower( get_class($this) ));

			$start = false;

			$len = count($para);
			for ($i=0; $i < $len;) 
			{ 
				if($start)
				{
					$args[ $para[$i] ] = $para[$i + 1] !== false ? $para[$i + 1] : '';
					$i = $i + 2;
				}
				else
				{
					$start = strtolower($para[$i]) == $action_name;
					$i = $i + 1;
				}
			}

		}
		else
		{
			$args = $_GET;
		}
		return $args;
	}

	protected function checkAuth($action)
	{
		$ret = $this->checkLogin();
		if(!$ret)
		{
			$this->needLogin();
		}
		$ret = LoginLgModel::checkAction($action);
		if($ret)
		{
			return $ret;
		}
		$this->returnMsg(3,'您没有权限执行此操作！');
	}

	/**
	*登陆验证
	**/
	protected function checkLogin()
	{
		//return true;
		return session('manage_user_name') == true;
	}

	/**
	*错误消息，根据type提示不同的消息
	*/
	protected function api_error($type)
	{
		$error = array('code'=>1,'content'=>'');
		$error['msg'] = 'HTTP method error,should be ' . $type;
		echo json_encode($error);
		Log::record('error' . print_r($error,true) . ' at ' . get_class($this) . '\n');
		Log::save();
		exit();
	}

	protected function needLogin()
	{
		$error = array('code'=>2,'msg'=>'need login','content'=>'');
		echo json_encode($error);
		Log::record('error' . print_r($error,true) . ' at ' . get_class($this) . '\n');
		Log::save();
		exit();
	}

	/**
	*重写基类baseAction中的error方法
	**/
	protected function error($msg, $content='',$code)
	{
		$error = array('code'=>1,'msg'=>$msg . print_r(file_get_contents('php://input') , true) ,'content'=>$content);
		echo json_encode($error);
		Log::record('error' . print_r($error,true) . ' at ' . get_class($this) . '\n');
		Log::save();
		exit();
	}
	
	/**
	*重写基类baseAction中的success方法
	**/
	protected function success($msg,$content, $code)
	{
		if(!$content)
		{
			$content = "";
		}
		$success = array('code' => 0, 'msg'=>$msg ,'content'=>$content);
		echo json_encode($success);
		exit();
	}

	protected function returnMsg($code,$msg ,$content)
	{
		if(!$content)
		{
			$content = "";
		}
		if(!$msg)
		{
			$msg = "";
		}
		$data = array('code' => $code, 'msg'=>$msg ,'content'=>$content);
		echo json_encode($data);
		exit();
	}

} ?>
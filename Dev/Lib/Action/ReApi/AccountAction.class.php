<?php 
/**
* 用户登录
*/
class AccountAction extends RestApiAction
{
	/**
	*获取用户名 + 密码 + 客户端发送过来的随机值 产生的MD5值，返回给客户端，客户端用此验证是否登陆成功
	**/
	protected function create()
	{

		$input = $this->post;
		$str = print_r($input,true);
		Log::record($str,true);
		Log::save();
		$ret = $input['username'] && $input['pwd'] && $input['code'];
		if($ret)
		{ 
			$ret = LoginLgModel::checkLogin($input['username'],$input['pwd'],$input['code']);
		}
		if($ret)
		{
			$this->success('登陆成功！' . $str , $ret);
		}
		else
		{
			$this->returnMsg(200,'用户名或密码错误' . $str);
		}
	}

	/**
	*登陆验证
	**/
	protected function checkLogin()
	{
		return true;
		//return session('manage_user_name') == true;
	}

}
 ?>
<?php 
/**
* 权限检查
*/
class LoginLgModel
{
	/**
	*$pwd明文，code为商家公众号code
	**/
	public static function checkLogin($username,$pwd,$code)
	{
		$wxuser = M('wxuser')->field('token,uid,company,code')->where(array('code'=>$code))->find();
		$ret = $wxuser && true;
		if($ret)
		{
	        $user =  M('manage_user')->where(array('user_name'=> $username, 'status'=>1,'token'=>$wxuser['token']))->find();
	        $pwd = md5(md5($pwd . $user['lz_salt']));
			$ret = $user && ($pwd === $user['password']);
		}
		//获取预定的id及信息，获取商城id及信息，获取餐馆id及信息
		if($ret)
		{	
			$token = $user['token'];
			session_destroy();
            session_start();
            $user['diningsub'] = $user['diningsub'] === false ? 0 : $user['diningsub'];
            $user['hotelsub']  = $user['hotelsub'] === false ? 0 : $user['hotelsub'];
            $_SESSION['manage_user_name']       = $user['user_name'];
            $_SESSION['manage_merchant']        = $wxuser['company'];
            $_SESSION['manage_company_token']   = $user['token'];
            $_SESSION['manage_company_code']    = $wxuser['code'];
            $_SESSION['manage_dine_branch']     = $user['diningsub'];
            $_SESSION['manage_hotel_branch']    = $user['hotelsub'];

            $av_actions = LoginLgModel::getAvAction($wxuser['uid'],$user['action_list']);
            
            $_SESSION['manage_act_list']  = implode(',', $av_actions);

            $prebook = LoginLgModel::getPreBook($token);
            $eshop    = LoginLgModel::getEShop($token);

            $_SESSION['manage_prebook_branch'] = $prebook['id'];
            $_SESSION['manage_eshop_branch']   = $eshop['id'];

			unset($user['action_list']);
			unset($user['password']);
			$result = array(
					'company' => $wxuser,
					'user'    => $user,
					'booking' => $prebook,
					'eshop'   => $eshop,
					'hotel'   => LoginLgModel::getAvHotel($user['hotelsub'], $token),
					'rest'    => LoginLgModel::getAvRest($user['diningsub'], $token),
					'actions' => $av_actions, 
				);
			return $result;
		}
		else
		{
			return false;
		}
	}

	protected static function getAvAction($id,$user_action_list)
	{
        $merchant = M('users')->where(array('id'=>$id))->find();
        $uid = $merchant['id'];
        $cur = time();

        //获取操作员平台支持的功能
        $support_functions = C('manage_functions');
        $support_functions_keys = array_keys($support_functions);

        $in_stmt_str = array();
        foreach ($support_functions_keys as $key => $value) 
        {
            $in_stmt_str[$key] = "'".$value."'";
        }
        $in_stmt = implode(',', $in_stmt_str);

        //若该功能已经过期，则不显示
        $sql = 'select f.funname from tp_function as f LEFT JOIN tp_function_group as fg on f.fgid = fg.id LEFT JOIN tp_user_func_group as ufg on ufg.group_id = f.fgid'
                          . " where ufg.user_id = '$uid' and ufg.expire_time > $cur and f.funname in ($in_stmt);";
        $Model = new Model();
        $functions = $Model->query($sql);

        $avail_func = array();
        foreach ($functions as $key => $value) 
        {
            if ( in_array($value['funname'], $support_functions_keys)) 
            {
                array_push($avail_func, $support_functions[$value['funname']]);
            } 
        }

        //根据操作员的权限列表action来显示相应的模块
        $my_actions = explode(',', $user_action_list);
        $my_avai_actions = array();
        foreach ($my_actions as $action) 
        {
            foreach ($avail_func as $func) 
            {
                if (strpos($action,$func) !== false)
                {
                    array_push($my_avai_actions, $action);
                }
            }
        }
        if(!$my_avai_actions)
        {
        	$my_avai_actions = array();
        }

        return  $my_avai_actions;
	}

	/**
	*获取电子商城信息
	**/
	protected static function getEShop($token)
	{
		$map['status']  = 1; 
        $map['token']   = $token;
		$shop  = M('b2c_shop')->where($map)->field('shop_id as id,name,status')->find();
		if(!$shop)
		{
			$shop = "";
		}
		return $shop;
	}

	/**
	*获取预定/报名 信息
	**/
	protected static function getPreBook($token)
	{
		$map['status']  = 1; 
        $map['token']   = $token; 

        //预定，报名 field('id,token,title,address,tel,tel2,name,type')-
		$order = M('Host')->where($map)->field('id,title as name,status')->find();
		if(!$order)
		{
			$order = "";
		}

		return $order;
	}

	/**
	*获取餐馆信息
	**/
	protected static function getAvRest($rid,$token)
	{
		$map['status']  = 1; 
        $map['token']   = $token; 
		if($rid)
		{
			$map['id'] = $rid;
		}
		//餐馆
		$din = M('dine_restlist')->where($map)->field('id,name,status')->find();
		if(!$din)
		{
			$din = "";
		}
		return $din;
	}

	/**
	*获取宾馆信息
	**/
	protected static function getAvHotel($hid,$token)
	{
		$map['status']  = 1; 
        $map['token']   = $token; 
		if($hid)
		{
			$map['id'] = $hid;
		}
		//宾馆->field('id,token,title,address,')
		$hotel = M('hotel')->where($map)->field('id,title as name,status')->find();
		if(!$hotel)
		{
			$hotel = "";
		}
		return $hotel;
	}

    public function checkAction($action)
    {
        $action_list = explode(',', session('manage_act_list'));
        return in_array($action, $action_list) ;
    }

}
?>
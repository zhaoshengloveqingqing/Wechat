<?php
class WapAction extends Action
{
    //0是订阅号，1是服务号
    protected $wechat_type;

    //0是未认证，1是已认证
    protected $is_authed    = 0;
    protected $appid        = null;

    //关注者open_id
    protected $wechat_id    = null;
    //公众号id
    protected $token        = null;
    //6 digit merchant_code for url
    protected $merchant_code  = null;

    protected function _initialize()
    {
        Log::record('Host:'.$_SERVER['SERVER_NAME'].' WapRequest:'.print_r($_GET, true)); //$_SERVER
        Log::save();
        $this->token = $this->_get('token');
        $this->merchant_code = $this->_get('shop');
        $wx_user = null;
        
        if(empty($this->token) && !empty($this->merchant_code)) 
        {
            // 二级域名url rewrite过来的只含有merchant code,而没有token，需要做下mapping并记录在cache里面。
            $wx_user = M('wxuser')->field('code,appid, type, is_authed, token')->where(array('code' => $this->merchant_code, 'status' => 1))->find();
        }
        elseif (!empty($this->token) && empty($this->merchant_code)) 
        {
            $wx_user = M('wxuser')->field('code,appid, type, is_authed, token')->where(array('token' => $this->token, 'status' => 1))->find();
        }

        if ($wx_user) 
        {
            $this->token = $wx_user['token'];
            //将商家公众号信息保存到session
            session('account_token_'.$this->token,  $wx_user['token']);
            session('account_type_'.$this->token,   $wx_user['type']);
            session('is_authed_'.$this->token,      $wx_user['is_authed']);
            session('appid_'.$this->token,          $wx_user['appid']);
            session('merchant_code_'.$this->token,  $wx_user['code']);
                
            $this->wechat_type  = $wx_user['type'];
            $this->is_authed    = $wx_user['is_authed'];
            $this->appid        = $wx_user['appid'];
            $this->merchant_code  = $wx_user['code'];
        }

        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger") || empty($this->token)) 
        {
            //如果不是高级服务号，则可禁止未带id访问
            if ($this->token != 'lingzhtech') 
            {
                Log::save();
                //echo '此功能只能在微信浏览器中使用!';
                //exit;
            }
        }

        if (session('?account_token_'.$this->token)) 
        {
            $this->wechat_type  = session('account_type_'.$this->token);
            $this->is_authed    = session('is_authed_'.$this->token);
            $this->appid        = session('appid_'.$this->token);
            $this->merchant_code        = session('merchant_code_'.$this->token);
        }
        else
        {
            $wx_user = M('wxuser')->field('code,appid, type, is_authed, token')->where(array('token' => $this->token, 'status' => 1))->find();
            if ($wx_user) 
            {
                //将商家公众号信息保存到session
                session('account_token_'.$this->token,  $wx_user['token']);
                session('account_type_'.$this->token,   $wx_user['type']);
                session('is_authed_'.$this->token,      $wx_user['is_authed']);
                session('appid_'.$this->token,          $wx_user['appid']);
                session('merchant_code_'.$this->token,  $wx_user['code']);
                
                $this->wechat_type  = $wx_user['type'];
                $this->is_authed    = $wx_user['is_authed'];
                $this->appid        = $wx_user['appid'];
                $this->merchant_code  = $wx_user['code'];
            }
        }

        unset($_SESSION['opened_funcs_'.$this->token]);
        if (session('?opened_funcs_'.$this->token) == false) 
        {
            //保存开通功能信息
            $token_open = M('Token_open')->field('queryname')->where(array('token'=>$this->token))->find();
            if ($token_open) 
            {
                $opened_funcs = explode(',',$token_open['queryname']);
                session('opened_funcs_'.$this->token,$opened_funcs);
            } 
        }

        if ($this->wechat_type == 1 && $this->is_authed == 1) 
        {
            $this->wechat_id = session('wechat_id_'.$this->token);
        }
        else
        {
            $this->wechat_id = $this->_get('wecha_id');
        }

        Log::record('wechat_id:'.$this->wechat_id.' merchant_token:'.$this->token.' session_id:'.session_id(), Log::INFO);
    }
    
    public static function generatePayResultUrl($addr, $token, $query_ary = array())
    {
        if (empty($addr)) 
        {
            return "";
        }
        
//        $host_name = C('wx_handler_server');
//       
//        if(C('hornor_shop_domain') == 1) {
//            $wx_user = M('wxuser')->field('code')->where(array('token' => $token, 'status' => 1))->find();
//            $host_name = $wx_user['code'].C('shop_domain');
//        }
//        
        $host_name = $_SERVER['SERVER_NAME'];
        $ret = 'http://'.$host_name.U($addr,$query_ary);
        
        Log::record('payredirect '.$ret);
        Log::save();
        return $ret;
    }
}
?>
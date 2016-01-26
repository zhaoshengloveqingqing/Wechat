<?php
/**
 * Oauth2 SocialAuth for CodeIgniter
 * Weixin OAuth2 Provider
 *
 * @category   Provider
 * @author     Jake
 * @since 2014-08-04
 */
class Wechat extends Oauth2_Provider {

    public $name = 'wechat';

    public $uid_key = 'openid';

    protected $scope = 'snsapi_userinfo';//snsapi_base

    public $method = 'POST';

    public function __construct()
    {
    }

    public function scope_min()
    {
        $this->scope = 'snsapi_base';
    }
    public function url_authorize()
    {
        return 'https://open.weixin.qq.com/connect/oauth2/authorize';
    }

    public function url_access_token()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    public function get_user_info(OAuth2_Token_Access $token)
    {
        $lang = C('DEFAULT_LANG');
        if(cookie('think_language')){
            $lang = cookie('think_language');
        }
        $url = 'https://api.weixin.qq.com/sns/userinfo?'.http_build_query(array(
                'access_token' => $token->access_token,
                'openid' => $token->uid,
                'lang' => $lang
            ));
        $user = json_decode(@file_get_contents($url));
        if (array_key_exists("errcode", $user)) {
            throw new OAuth2_Exception((array) $user);
        }

        $user->provider = $this->name;
        $user->access_token = $token->access_token;
        $user->expires = $token->expires;
        $user->refresh_token = $token->refresh_token;
        return $user;
    }

    public function config(array $options = array()){
        if ( ! $this->name)
        {
            // Attempt to guess the name from the class name
            $this->name = strtolower(substr(get_class($this), strlen('OAuth2_Provider_')));
        }

        if (empty($options['id']))
        {
            throw new Exception('Required option not provided: id');
        }

        $this->client_id = $options['id'];

        isset($options['callback']) and $this->callback = $options['callback'];
        isset($options['secret']) and $this->client_secret = $options['secret'];
        isset($options['scope']) and $this->scope = $options['scope'];
    }

    /*
	* Get an authorization code from Facebook.  Redirects to Facebook, which this redirects back to the app using the redirect address you've set.
	*/
    public function authorize($options = array())
    {
        $state = md5(uniqid(rand(), TRUE));
        session('state', $state);

        $params = array(
            'appid' 		=> $this->client_id,
            'redirect_uri' 		=> $options['redirect_uri'],
            'response_type' 	=> 'code',
            'scope'				=> is_array($this->scope) ? implode($this->scope_seperator, $this->scope) : $this->scope,
            'state' 			=> $state
        );

        return $this->url_authorize().'?'.http_build_query($params).'#wechat_redirect';
    }

    /*
	* Get access to the API
	*
	* @param	string	The access code
	* @return	object	Success or failure along with the response details
	*/
    public function access($code, $options = array())
    {
        $params = array(
            'appid' 	=> $this->client_id,
            'secret' => $this->client_secret,
            'grant_type' 	=> isset($options['grant_type']) ? $options['grant_type'] : 'authorization_code',
        );

        switch ($params['grant_type'])
        {
            case 'authorization_code':
                $params['code'] = $code;
                $params['redirect_uri'] = $options['redirect_uri'];
                break;

            case 'refresh_token':
                $params['refresh_token'] = $code;
                break;
        }

        $response = null;
        $url = $this->url_access_token();

        switch ($this->method)
        {
            case 'GET':

                // Need to switch to Request library, but need to test it on one that works
                $url .= '?'.http_build_query($params);
                $response = @file_get_contents($url);

                $return = $this->parse_response($response);

                break;

            case 'POST':

                $postdata = http_build_query($params);
                $opts = array(
                    'http' => array(
                        'method'  => 'POST',
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $postdata
                    )
                );
                $context  = @stream_context_create($opts);
                $response = @file_get_contents($url, false, $context);

                $return = $this->parse_response($response);

                break;

            default:
                throw new OutOfBoundsException("Method '{$this->method}' must be either GET or POST");
        }

        if ( ! empty($return['error']))
        {
            throw new OAuth2_Exception($return);
        }

        return OAuth2_Token::factory('access', $return);
    }

    /**
     * For each response to each response type for using
     *
     * @param string $response
     * @author jake
     * @since 2014-07-30
     * @return response with string | object
     */
    protected function parse_response($response = '')
    {
        if (strpos($response, "callback") !== false)
        {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $return = json_decode($response, true);
        }
        elseif (strpos($response, "&") !== false)
        {
            parse_str($response, $return);
            if(isset($return['openid']))
                $return['uid'] = $return['openid'];
        }
        else
        {
            $return = json_decode($response, true);
            if(isset($return['openid']))
                $return['uid'] = $return['openid'];
        }
        return $return;
    }
}

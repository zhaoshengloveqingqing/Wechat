<?php
class Wechat{
	private $appId;
	private $appSecret;
	private $token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
	private $ticket_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s';
	private $qrcode_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s';

	public function __construct($appId, $appSecret) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;
	}
	
	/**
	 * 获取access_token
	 * @param string $appid 如在类初始化时已提供，则可为空
	 * @param string $appsecret 如在类初始化时已提供，则可为空
	 * @param string $access_token 手动指定access_token，非必要情况不建议用
	 */
	public function checkAuth($appid = '', $appsecret = '', $access_token = ''){
			if (!$appid || !$appsecret) {
				$appid = $this->appId;
				$appsecret = $this->appSecret;
			}
			if ($access_token) { //手动指定$access_token，优先使用
			    $this->access_token = $access_token;
			    return $this->access_token;
			}
			$file_path = DATA_PATH.'access_token.json';
			$data = json_decode(file_get_contents($file_path));
		    if ($data->expire_time < time()) {
		      $result = json_decode( $this->http_get(sprintf($this->token_url, $appid, $appsecret)));
		      if (!$result || isset($result->errcode)) {
					$this->errCode = $result->errcode;
					$this->errMsg = $result->errmsg;
					return false;
			  }
		      $this->access_token = $result->access_token;
		      if ($this->access_token) {
		        $data->expire_time = time() + 7000;
		        $data->access_token = $this->access_token;
		        $fp = fopen($file_path, "w");
		        fwrite($fp, json_encode($data));
		        fclose($fp);
		      }
		    } else {
		      $this->access_token = $data->access_token;
		    }
		    return $this->access_token;
	}
	
	/**
	 * 创建二维码ticket
	 * @param int|string $scene_id 自定义追踪id,临时二维码只能用数值型
	 * @param int $type 0:QR_SCENE 临时二维码；1:QR_LIMIT_SCENE 永久二维码(此时expire参数无效)；2:QR_LIMIT_STR_SCENE 永久二维码(此时expire参数无效)
	 * @param int $expire 临时二维码有效期，最大为1800秒
	 * @return array('ticket'=>'qrcode字串', 'expire_seconds'=>1800, 'url'=>'二维码图片解析后的地址')
	 */
	public function getQRCode($scene_id, $type=0, $expire=1800){
		if (!$this->access_token && !$this->checkAuth()) return false;
		$type = ($type && is_string($scene_id)) ? 2 : $type;
		$data = array(
			'expire_seconds' => $expire,
			'action_name' => $type ? ($type == 2 ? "QR_LIMIT_STR_SCENE" : "QR_LIMIT_SCENE") : "QR_SCENE",
			'action_info' => array('scene' => ($type == 2 ? array('scene_str' => $scene_id) : array('scene_id' => $scene_id)))
		);
		
		if ($type == 1) {
			unset($data['expire_seconds']);
		}
		$result = $this->http_post(sprintf($this->ticket_url, $this->access_token), json_encode($data));
		if ($result){
			$aRes = json_decode($result,true);
			if (!$aRes || !empty($aRes['errcode'])) {
				$this->errCode = $aRes['errcode'];
				$this->errMsg = $aRes['errmsg'];
				return false;
			}
			$this->ticket = $aRes['ticket'];
			return $this->ticket;
		}
		return false;
	}
	
	/**
	 * 获取二维码图片
	 * @param string $ticket 传入由getQRCode方法生成的ticket参数
	 * @return string url 返回http地址
	 */
	public function getQRUrl($ticket = '') {
			return sprintf($this->qrcode_url, $ticket);
	}
	
	/**
	 * GET 请求
	 * @param string $url
	 */
	private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
	
	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	private function http_post($url, $param, $post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (is_string($param) || $post_file) {
			$strPOST = $param;
		} else {
			$strPOST = http_build_query($param);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST, true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
	
}
<?php
/*
 * 该类封装了微信API接口。
 */
class WeixinAPI {

        private $AppID;

        private $AppSecret;
        
        private $pushUrl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=";

        function WeixinAPI($appId, $appSecret) {
            if(empty($appId) || empty($appSecret)) {
                throw new Exception('WeixinAPI: appID and appSecret must not be empty.');
            }
            
            $this->AppID = $appId;
            $this->AppSecret = $appSecret;
        }

        public function getFollowers($nextopenid=""){
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->getAccessToken();
            if (!empty($nextopenid)) {
                $url = $url."&next_openid=".$nextopenid;
            }
            Log::record('getFollowers:'.$url."\r\n", Log::DEBUG);       
            Log::save();
            $remote_resp = file_get_contents($url);
            $ret_json = json_decode($remote_resp, true);
            return $ret_json;
        }
        
        public function getUserInfo($openid){
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->getAccessToken()."&openid=".$openid."&lang=zh_CN";
            $remote_resp = file_get_contents($url);
            Log::record('get user info: '.$remote_resp, Log::INFO);
            Log::save();
            $ret_json = json_decode($remote_resp, true);
			if (isset($ret_json['errcode'])) {
				return $this->get_user_info($openid);
            }
            return $ret_json;
        }
        
		public  function get_user_info($openid) {
			$Cache = Cache::getInstance('File',array('expire'=>'3600'));
			$cacheKey = 'at_'.$this->AppID;
			$Cache->rm($cacheKey);
			$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->getAccessToken()."&openid=".$openid."&lang=zh_CN";
			$remote_resp = file_get_contents($url);
			$ret_json = json_decode($remote_resp, true);
			Log::record('get user info again: '.$remote_resp, Log::INFO);    
			return $ret_json;
		}
		
        public function pushText($user, $text){
            $msg["touser"] = $user;
            $msg["msgtype"] = "text";
            $content["content"] = $text;
            $msg["text"] = $content;
            $data = json_encode($msg);
            $url = $this->pushUrl.$this->getAccessToken();
            return $this->sendPost($url, $data);
        }

        public function pushNews($user, $articles){
            $msg["touser"] = $user;
            $msg["msgtype"] = "news";
            $content["articles"] = array();
            foreach($articles as $article) { 
                $a["title"] = $article["title"];
                $a["description"] = $article["description"];
                $a["url"] = $article["url"];
                $a["picurl"] = $article["picurl"];
                array_push($content["articles"], $a);
            }
            $msg["news"] = $content;
            $data = json_encode($msg);
            $url = $this->pushUrl.$this->getAccessToken();
            return $this->sendPost($url, $data);
        }

        public function pushImg($user, $imageId){
            $msg["touser"] = $user;
            $msg["msgtype"] = "image";
            $content["media_id"] = $imageId;
            $msg["image"] = $content;
            $data = json_encode($msg);
            $url = $this->pushUrl.$this->getAccessToken();
            return $this->sendPost($url, $data);
        }

        public function getAccessToken() {
            $cacheKey = 'at_'.$this->AppID;
            $Cache = Cache::getInstance('File',array('expire'=>'3600'));
            $access_token = $Cache->get($cacheKey);
            if (!empty($access_token)) {
                Log::record('get cache access token appId:'.$this->AppID.' appSecret:'.$this->AppSecret.' token:'.$access_token, Log::INFO);
                Log::save();
                return $access_token;
            }

            $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->AppID."&secret=".$this->AppSecret;
            $remote_resp = file_get_contents($tokenUrl);
            $ret_json = json_decode($remote_resp, true);
            $access_token = $ret_json['access_token'];
            $Cache->set($cacheKey, $access_token);
            
            Log::record('get new access token info:'.$remote_resp, Log::INFO);
            Log::save();
            return $access_token;
        }
        
       	public function get_ticket($type = 'jsapi') {
        	$cacheKey = 'ticket_'.$this->AppID;
            $Cache = Cache::getInstance('File',array('expire'=>'7000'));
            $ticket = $Cache->get($cacheKey);
            if (!empty($ticket)) {
                Log::record('get cache ticket appId:'.$this->AppID.' appSecret:'.$this->AppSecret.$type.'_ticket:'.$ticket, Log::INFO);
                Log::save();
                return $ticket;
            }
            
       		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type='.$type.'&access_token='.$this->getAccessToken();
            $remote_resp = file_get_contents($url);
            $ret_json = json_decode($remote_resp, true);
            $ticket = $ret_json['ticket'];
            $Cache->set($cacheKey, $ticket);
            
            Log::record('get new ticket appId:'.$this->AppID.' appSecret:'.$this->AppSecret.$type.'_ticket:'.$ticket, Log::INFO);
            Log::save();
            return $ticket;
        }
        

        private function sendPost($url, $data){
            Log::record("WeixinAPI request ".$url."\r\n", Log::DEBUG);       
            Log::save();
            
            $output = $this->http_post($url, $data);
            
            Log::record("WeixinAPI response ".$output."\r\n", Log::DEBUG);       
            Log::save();

            return json_decode($output, true);       
        }
       
       private function http_post($url, $data) {
            $ch = curl_init(); 
            $header = array();
            $header[]= 'Content-Type: text/plain';
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
       }

}
?>

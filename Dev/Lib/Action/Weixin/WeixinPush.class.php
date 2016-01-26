<?php
class WeixinPush {

        private $appid;

        private $secret;
        
        private $pushUrl;

        function WeixinPush($appid, $secret) {
            $this->appid = $appid;
            $this->secret = $secret;
            $this->pushUrl = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=";
        }

        private function getToken() {
            $Cache = Cache::getInstance('File',array('expire'=>'3600'));
            $access_token = $Cache->get("access_token");
            if (!empty($access_token)) {
                return $access_token;
            }

            $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->secret;
            $remote_resp = file_get_contents($tokenUrl);
            $ret_json = json_decode($remote_resp, true);
            $access_token = $ret_json['access_token'];
            $Cache->set("access_token", $access_token);
            return $access_token;
        }

		public function getFollowers($nextopenid=""){
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$this->getToken();
			if (!empty($nextopenid)) {
			    $url = $url."&next_openid=".$nextopenid;
			}
			$remote_resp = file_get_contents($url);
            $ret_json = json_decode($remote_resp, true);
			return $ret_json;
        }
		
        public function pushText($user, $text){
            $msg["touser"] = $user;
            $msg["msgtype"] = "text";
            $content["content"] = $text;
            $msg["text"] = $content;
            $data = json_encode($msg);
            $url = $this->pushUrl.$this->getToken();
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
            $url = $this->pushUrl.$this->getToken();
            return $this->sendPost($url, $data);
        }

        public function pushImg($user, $imageId){
            $msg["touser"] = $user;
            $msg["msgtype"] = "image";
            $content["media_id"] = $imageId;
            $msg["image"] = $content;
            $data = json_encode($msg);
            $url = $this->pushUrl.$this->getToken();
            return $this->sendPost($url, $data);
        }


        private function sendPost($url, $data){
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

            Log::record("WeixinPush return ".$output."\r\n", Log::DEBUG);       
            Log::save();

            curl_close($ch);
            $ret_json = json_decode($output, true);

            return $ret_json["errmsg"];
       }

}
?>

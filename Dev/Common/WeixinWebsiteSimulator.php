<?php

class WeixinWebsiteSimulator {
    private $username;
    private $password;
    
    // logon redirect url
    private $logonRedirUrl;
    private $accountInfoUrl;
    // token assigned by Weixin Server
    public $token;
    
    public $cookies;
    
    const HTTP_REQUEST_TIMEOUT_SECS = 15;
    
    function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    function logon() {
        $this->Log('start logon', Log::INFO);
        $postData = array();
        $postData['username'] = $this->username;
        $postData['pwd'] = md5($this->password);
        $postData['imgcode'] = '';
        $postData['f'] = 'json';
        $content = $this->fetch('https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN',
                true, $postData, 'json', 'https://mp.weixin.qq.com/');
        if($content !== false) {
            
            // 解析json response，判断是否登录成功
            if(!$this->validateLogonJsonResponse($content[1])) {
                $this->Log('logon fail ValidateJsonResponse:'. $content[1]);
                return false;
            }

            // 得到登录后得cookie
            $this->parseCookies($content[0]);
            $this->Log('logon succeeds', Log::INFO);
            return $this->followLogonRedirect();
        }
        $this->Log('logon fail');
        return false;
    }
    
    
    function logout() {
        $this->Log('start logout');
        $referer = "https://mp.weixin.qq.com/cgi-bin/home?t=home/index&lang=zh_CN&token=".$this->token;
        $url = "https://mp.weixin.qq.com/cgi-bin/logout?t=wxm-logout&lang=zh_CN&token=".$this->token;
        $this->fetch($url, false, null, 'html', $referer);
        $this->Log('finish logout');
    }
    
    const SERVICE_ACCOUNT = 1;
    const SUBSCRIBE_ACCOUNT = 0;
    function getAccountDetails() {
        $this->Log('start getAccountDetails');
        $this->accountInfoUrl = 'https://mp.weixin.qq.com/cgi-bin/settingpage?t=setting/index&action=index&token='.$this->token.'&lang=zh_CN';
        $content = $this->fetch($this->accountInfoUrl, false, null, 'html', $this->logonRedirUrl, true);
        if($content !== false) {
            $stats = $this->extractAccountDetails($content[1]);
            if($stats == false) {
                $this->Log('getAccountDetails fail: header:'.$content[0].' details:'.$content[1]);
            }else {
                $this->Log('getAccountDetails succeeds');
            }
            
            return $stats;
        }
        $this->Log('getAccountDetails fail');
        return false;
    }
   private function extractAccountDetails($document) {
        $doc = new DOMDocument();
        $e = $doc->loadHTML($document);
        if($e == false) {
            $this->Log('extractAccountDetails fail: invalid html');
            return false;
        }
        
        $settingArea = $doc->getElementById('settingArea');
        if(empty($settingArea)) {
            $this->Log('extractAccountDetails: can not find settingArea.');
            return false;
        }
        $settingArea = $settingArea->getElementsByTagName('ul');
        if(empty($settingArea)) {
            $this->Log('extractAccountDetails: can not find ul in settingArea.');
            return false;
        }
        
        $settingArea =  $settingArea->item(0) ;
        if(empty($settingArea)) {
            $this->Log('extractAccountDetails: can not find item ul in settingArea doc.');
            return false;
        }

        $accountInfoArray = array();
        foreach($settingArea->childNodes as $liNode) {
            if(strcasecmp($liNode->nodeName, 'li') == 0) {
                $items = explode(' ', trim($liNode->textContent));
                $i = 0;
                while($i<count($items)) {
                    $item = trim($items[$i]);
                    if(!empty($item)) {
                        $first = $item;
                        break;
                    }
                    $i ++;
                }
                foreach($liNode->childNodes as $contentNode) {
                    if(! $contentNode->hasAttributes()) {
                        continue;
                    }
                    foreach($contentNode->attributes as $attribute) {
                        if(strcasecmp($attribute->name, 'class') == 0) {
                            $class = $attribute->value;
                            $pos = strpos($class, 'meta_content');
                            if($pos !== false) {
                                $second = trim($contentNode->textContent);
                                break;
                            }
                        }
                    }
                }
                
                if(isset($first) && isset($second)) {
                    $accountInfoArray[$first] = $second;
                }
            }
        }
        if(count($accountInfoArray) <= 0) {
            $this->Log('AccountDetails fail empty.');
        }
        $this->Log('AccountDetails finsh: '.print_r($accountInfoArray, true), Log::INFO);
        return $accountInfoArray;
    }
    
    const EDIT_MODE = 1;
    const DEV_MODE = 2;
    function disableEditMode() {
        $this->Log('start disableEditMode', Log::INFO);
        $referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=index&t=advanced/index&token=".$this->token."&lang=zh_CN";
        $url = "https://mp.weixin.qq.com/misc/skeyform?form=advancedswitchform&lang=zh_CN";
        //"https://mp.weixin.qq.com/cgi-bin/skeyform?form=advancedswitchform&lang=zh_CN";
        
        $postDataArray = array();
        $postDataArray['type'] = self::EDIT_MODE;
        $postDataArray['flag'] = 0;
        $postDataArray['token'] = $this->token;
        $response = $this->fetch($url, true, $postDataArray, 'json', $referer);
        if($response !== false) {
            $json = json_decode($response[1]);
            if($json->base_resp->ret == 0) {
                $this->Log('disableEditMode succeeds', Log::INFO);
                return true;
            }
            $this->Log('disableEditMode fail: json response: '.$response[1]);
        }
        $this->Log('disableEditMode fail');
        return false;
    }
    
    function enableDevModel() {
        $this->Log('enableDevModel start', Log::INFO);
        $referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=index&t=advanced/index&token=".$this->token."&lang=zh_CN";
        $url = "https://mp.weixin.qq.com/misc/skeyform?form=advancedswitchform&lang=zh_CN";//"https://mp.weixin.qq.com/cgi-bin/skeyform?form=advancedswitchform&lang=zh_CN";
        $postDataArray = array();
        $postDataArray['type'] = self::DEV_MODE;
        $postDataArray['flag'] = 1;
        $postDataArray['token'] = $this->token;
        $response = $this->fetch($url, true, $postDataArray, 'json', $referer);
        if($response !== false) {
            $json = json_decode($response[1]);
            if($json->base_resp->ret == 0){
                $this->Log('enableDevModel succeeds', Log::INFO);
                return true;
            }
            $this->Log('enableDevModel fail: json response: '.$response[0].'  details:'.$response[1]);
        }
        $this->Log('enableDevModel faile');
        return false;
    }
    
    function setThirdPartyEntryPoint($callbackUrl, $callbackToken) {
        $this->Log('setThirdPartyEntryPoint start', Log::INFO);
        $referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=interface&t=advanced/interface&token=".$this->token."&lang=zh_CN";
        $url = 'https://mp.weixin.qq.com/advanced/callbackprofile?t=ajax-response&token='.$this->token.'&lang=zh_CN';
        $postDataArray = array();
        $postDataArray['url'] = ($callbackUrl);
        $postDataArray['callback_token'] = ($callbackToken);
        $response = $this->fetch($url, true, $postDataArray, 'json', $referer);
        if($response !== false) {
            $json = json_decode($response[1]);
            if($json->ret == 0) {
                $this->Log('setThirdPartyEntryPoint suucceed', Log::INFO);
                return true;
            }
            $this->Log('setThirdPartyEntryPoint fail: json response: '.$response[1]);
        }
        $this->Log('setThirdPartyEntryPoint fail');
        return false;
    }
    
    function getAppIdAndSecret() {
        $this->Log('getAppIdAndSecret start', Log::INFO);
        $referer = "https://mp.weixin.qq.com/cgi-bin/advanced?action=index&t=advanced/index&token=".$this->token."&lang=zh_CN";
        $url = "https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=".$this->token."&lang=zh_CN";
        $response = $this->fetch($url, false, null, "html", $referer, true);
        $stats = false;
        if($response !== false) {
            
            $stats = $this->extractAppIdAndSecret($response[1]);
            if($stats == false) {
                $this->Log('getAppIdAndSecret fail: HttpHEADER:'.$response[0].'  details:'.$response[1]);
            }
        }
        if($stats !== false) {
            $this->Log('getAppIdAndSecret succeeds', Log::INFO);
            return $stats;
        }
        $this->Log('getAppIdAndSecret fail');
        return false;
    }
    private function extractAppIdAndSecret($document) {
        $this->Log('extractAppIdAndSecret start', Log::INFO);
        $doc = new DOMDocument();
        $e = $doc->loadHTML($document);
        if($e == false) {
            $this->Log('extractAppIdAndSecret invalid html');
            return false;
        }
        
        $divList = $doc->getElementsByTagName('div');
        if(empty($divList)){
            $this->Log('extractAppIdAndSecret can not find DIV');
            return false;
        }
        
        $appIdSecretMap = array();
        foreach($divList as $divNode) {
            if(! $divNode->hasAttributes()) {
                continue;
            }
            foreach($divNode->attributes as $attribute) {
                if(strcasecmp($attribute->name, 'class') == 0) {
                    $class = $attribute->value;
                    if(strpos($class, 'frm_control_group') !== false) {
                        $items = explode(' ', $divNode->textContent);
                        $i = 0;
                        while($i < count($items)) {
                            $item = trim($items[$i]);
                            if(!empty($item)) {
                                $first = $item;
                                break;
                            }
                            $i ++;
                        }
                        $i ++;
                        while($i < count($items)) {
                            $item = trim($items[$i]);
                            if(!empty($item)) {
                                $second = $item;
                                break;
                            }
                            $i ++;
                        }
                        if(isset($first) && isset($second)) {
                            $appIdSecretMap[$first] = $second;
                        }
                    }
                }
                        
            }
        }
        if(count($appIdSecretMap) <= 0 ) {
             $this->Log('extractAppIdAndSecret failed');
            return false;
        }
        $this->Log('extractAppIdAndSecret finish: AppIdSecret: '.print_r($appIdSecretMap, true), Log::INFO);
        
        return $appIdSecretMap;
    }
    
    
    private function fetch($url, $is_post, $postDataArray, $responseType, $referer, $dumpResponse=false) {
        $this->Log(
                'start fetching:'.($is_post? print_r($postDataArray, true):'get').' responseType:'.$responseType.' url:'.$url,
                Log::INFO);
        $s = curl_init();
        
        curl_setopt($s,CURLOPT_URL, $url);
        $headers = array(
            'Origin: https://mp.weixin.qq.com',
            //'Accept-Encoding: gzip,deflate,sdch',
            'Host: mp.weixin.qq.com',
            'Accept-Language: zh-CN,zh;q=0.8',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            $responseType=='json' ? 'Accept: application/json, text/javascript, */*; q=0.01' : 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'X-Requested-With: XMLHttpRequest',
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36',
            'Referer: '.$referer
            );
        
        curl_setopt($s,CURLOPT_HEADER,true); 
        curl_setopt($s,CURLOPT_HTTPHEADER, $headers); 
        //curl_setopt($s, CURLOPT_COOKIEJAR, RUNTIME_PATH.$this->cookieFile);
        //curl_setopt($s, CURLOPT_COOKIEFILE, RUNTIME_PATH.$this->cookieFile);
        curl_setopt($s, CURLOPT_FRESH_CONNECT, 1);
        
        curl_setopt($s,CURLOPT_TIMEOUT, self::HTTP_REQUEST_TIMEOUT_SECS); 
        curl_setopt($s,CURLOPT_MAXREDIRS, 3); 
        curl_setopt($s,CURLOPT_FOLLOWLOCATION,true); 
        
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        
        curl_setopt($s,  CURLINFO_HEADER_OUT, true);
        
        if($is_post) {
            curl_setopt($s,CURLOPT_POST,true); 
            curl_setopt($s,CURLOPT_POSTFIELDS, http_build_query($postDataArray));
            
        }else {
            
            curl_setopt($s, CURLOPT_HTTPGET,true);
        }
        curl_setopt($s, CURLOPT_COOKIESESSION, 1);
        if(!empty($this->cookies)) {
            curl_setopt($s, CURLOPT_COOKIE, $this->cookies);
        }else {
            curl_setopt($s, CURLOPT_COOKIE, 'Wed, 05 Feb 2014 04:45:21 GMT');
        }

        //curl_setopt($s,CURLOPT_NOBODY,true); 

        $content = curl_exec($s); 
        if($dumpResponse !== false) {
            //$this->Log('FetchResponse: '.$content);
        }
        
        $status = curl_getinfo($s); 
        $headerSize = curl_getinfo($s, CURLINFO_HEADER_SIZE);
        curl_close($s); 
        
        if($status['http_code'] != 200) {
            $this->Log('Fetch fail: url:'.$url.' response:'.$status['http_code'].' '.($responseType=='json'?$content:''));
            return false;
        }
        
        $header = substr($content, 0, $headerSize);
        $body = substr($content, $headerSize);
        $this->Log('finish fetching.', Log::INFO);
        return array($header, $body);
    }
    
    private function followLogonRedirect() {
        $this->Log('followLogonRedirect start', Log::INFO);
        $content = $this->fetch($this->logonRedirUrl, false, null, 'html', 'https://mp.weixin.qq.com/');
        if($content !== false) {
            //更新cookie
            $this->parseCookies($content[0]);
            $this->Log('followLogonRedirect finish', Log::INFO);
            return true;
        }
        $this->Log('followLogonRedirect fail');
        return false;
    }
    
    private function parseCookies($content) {
        preg_match_all('|Set-Cookie: (.*);|U', $content, $results);  
        $this->cookies = implode(';', $results[1]);
    }
    
    private function validateLogonJsonResponse($jsonres) {
        //preg_match_all('|\{(?<jsonres>[^\{\}]+)\}|', $content, $matches);
        //$jsonres = $matches['jsonres'][0];
        if(!empty($jsonres)) {
            $logonRes = json_decode( $jsonres);
            if($logonRes->base_resp->ret == 0) {
                $this->logonRedirUrl = 'https://mp.weixin.qq.com/' . $logonRes->redirect_url;
                $queryParams = $this->convertUrlQuery(parse_url($this->logonRedirUrl, PHP_URL_QUERY));
                if(isset($queryParams['token'])) {
                    $this->token = $queryParams['token'];
                    return true;
                }
            }
        }
        
        return false;
    }
    private function convertUrlQuery($query) { 
        $queryParts = explode('&', $query); 

        $params = array(); 
        foreach ($queryParts as $param) { 
            $item = explode('=', $param); 
            $params[$item[0]] = $item[1]; 
        } 

        return $params; 
    } 
    
    
    private function Log($detail, $level = Log::ERR ) {
        Log::record( date("Y-m-d H:i:s").':AutoBindLog '.$this->username.' '.$detail, $level);
        Log::save();
    }
}

<?php
require_once(COMMON_PATH.'/Branch.php');
require_once(COMMON_PATH.'/ServiceHelper.php');
class WeixinAction extends Action 
{

    public $token = '';
    public $userId = '';
    public $oriWxId = '';
    public $fromWxId = '';

    //0是订阅号，1是服务号
    protected $wechat_type;

    //0是未认证，1是已认证
    protected $is_authed    = 0;

    protected $cs_setting   = null;

    protected $has_default_resp   = 0;

    protected $merchant_code;

    /**
     * 是否debug的状态标示，方便我们在调试的时候记录一些中间数据
     * @var boolean
     */
    public $debug =  true;

    /**
     *('text','image','location')
     * @var string
     */
    public $msgType = 'text';
    /**
     *消息信息
     * @var array
     */
    public $msg = array();


    /**
     * subscribe(订阅)、unsubscribe(取消订阅)、CLICK(自定义菜单点击事件)
     * @var string
     */
    public $event = '';

    public $eventKey = '';

    public function index()
    {
        $this->debug = C('APP_DEBUG');

        //valid signature , option
        if(!$this->checkSignature())
        {
            exit;
        } 
        else 
        {
            $this->processMsg();
            if (empty($this->msg)) 
            {
                echo $_GET['echostr'];
                exit;
            }
        }
        //获得相应的关键字
        $keyword ;
        if ($this->msgType === 'event') 
        {
            if ($this->event == 'subscribe') 
            {
            	//扫描带有参数的二维码
            	if ($this->eventKey) {
            		$qrscene = str_replace('qrscene_', '', $this->eventKey);
		             $this->sendMessage($qrscene);
            	}
            	
                $where = array('token'=>$this->token, 'uid'  => $this->userId);
                $sub_reply = M('Areply')->where($where)->find(); 
                if ($sub_reply != null) 
                {
                // '9367a975f19a06750b67f719f4f08ceb' md5  subscriber
                    $keyword = $sub_reply['ctype'] == 'text'
                        ? '9367a975f19a06750b67f719f4f08ceb'
                        : $sub_reply['keyword'];
                }
            } 
            else if ($this->event == 'unsubscribe') 
            {
                $keyword ='3ce2bca8a052d0bc3ec05a052ec06ade'; //md5 unsubscriber
            } 
            else if ($this->event == 'click') 
            {
                $keyword = $this->eventKey;
            }
            else if ($this->event == 'location') 
            {
                $Cache = Cache::getInstance('File',array('expire'=>'3600'));
                $Cache->set($this->fromWxId, $this->msg['Latitude'].",".$this->msg['Longitude']);
                $this->write_log('location :'.$this->msg['Latitude'].",".$this->msg['Longitude']);
                Log::save();
                exit;
            }
            else if ($this->event == 'scan') {//扫描带参数的已关注的二维码
            	$qrscene = $this->eventKey;
				$this->write_log($this->event);
	            $this->sendMessage($qrscene);
             }
        } 
        else 
        {
        	//特殊处理微信墙
        	$this->special_wall();
        	
            //获取发送的消息内容
            if ($this->msgType === 'text') 
            {   
                //文本输入
                $keyword = $this->msg['Content'];
            } 
            else if ($this->msgType === 'voice') 
            { 
                //语音输入
                $keyword = $this->msg['Recognition'];
            }else if ($this->msgType === 'location')
            {
                $this->handleBasicLocationMsg($this->msg);
            }
        }

        $this->handleMsgPush($keyword);
    }
    
    
	function special_wall() {
    	$user = M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$this->fromWxId))->find();
        if (!$user || (!$user['nickname'] && !$user['headimgurl'])) {
			$userinfo = $this->getUserInfo($this->token);
			    
			$data['userinfo']   = json_encode($userinfo);
			$data['sex']        = $userinfo['sex'];
			
			require_once(COMMON_PATH.'/emoji.php');
			$data['nickname']   = emoji_unified_to_html($userinfo['nickname']);
			$data['city']       = $userinfo['city'];
			$data['province']   = $userinfo['province'];
			$data['country']    = $userinfo['country'];
			$data['headimgurl'] = $userinfo['headimgurl'];
			$data['updatetime']= time();
			$data['token']      = $this->token;
			$data['wecha_id']   = $this->fromWxId;
			if ($user) {
				M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$this->fromWxId))->save($data);
			}else{
				M('wecha_user')->add($data);
			}
        }
        
        preg_match_all ('/^微信墙#(.*)$/', $this->msg['Content'], $enter_wall);
        if (isset($enter_wall[1][0]) && $enter_wall[1][0]) {
           if (!M('Wall')->where(array('token' => $this->token, 'status'=>'1', 'keyword' => trim($enter_wall[1][0])))->find()) {
			list($content, $type) = array('您没有在派尔网络微信营销平台上配置关键词“'.trim($enter_wall[1][0]).'”', 'text');
			$this->response($content, $type);	
           }
           M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$this->fromWxId))->save(array('keyword'=>$enter_wall[1][0], 'wallopen'=>'1'));
           $user = M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$this->fromWxId))->find();
           list($content, $type) = array('您已进入微信墙对话模式，您下面发送的所有文字和图片信息都将会显示在大屏幕上，如需退出微信墙模式，请输入“退出#关键词”', 'text');
       	   $this->response($content, $type);
        }
        preg_match_all ('/^退出#(.*)$/', $this->msg['Content'], $quit_wall);
        if (isset($quit_wall[1][0]) && $quit_wall[1][0]) {
            M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$this->fromWxId))->save(array('keyword'=>'', 'wallopen'=>'0'));
        	list($content, $type) = array('成功退出微信墙对话模式', 'text');
       		$this->response($content, $type);
        }
        if ($user['wallopen'] == '1') {
	        $wallopen_user = M('wecha_user')->where(array('token'=>$this->token, 'wecha_id'=>$this->fromWxId, 'wallopen'=>'1'))->find();
			if ($wallopen_user) {
				$keyword = M('keyword')->where(array('keyword'=>$wallopen_user['keyword'], 'module'=> 'wall', 'token' => $this->token, 'function' => 'xianchang'))->find();
				if (!$keyword) {
					list($content, $type) = array('当前您已经是微信墙模式，设置的关键词是'.$wallopen_user['keyword'].'，由于上次未退出微信墙模式，关键词不匹配，请重新发送“微信墙#关键词”', 'text');
					$this->response($content, $type);
				}
			}
        	//获取发送的消息内容
            if ($this->msgType === 'text'){   
                $strMsg = $user['keyword'].$this->msg['Content'];
            }elseif ($this->msgType === 'image'){
            	$strMsg = $user['keyword'].'<a href="'.$this->msg['PicUrl'].'"><img src='.$this->msg['PicUrl'].'></a>';
            }
        	$this->handleMsgPush($strMsg);
        	exit;
        }
    }
    
 	function sendMessage($qrscene) {
    	if ($qrscene == '1') {
	    	list($content, $type) = array('<a href="http://8.8.8.8">点我上网</a>', 'text');
    	}
       	$this->response($content, $type);
    }
    
    
    
    private function handleBasicLocationMsg($msg){
        $latitude = $msg['Location_X'];
        $longtitude = $msg['Location_Y'];
        
        $branch = new Branch($this->token);
        $branches = $branch->getNearbyBranchList_Gps($longtitude, $latitude);
        
        // 为了冲用makeNews方法，需要将门店列表做些转化
        
        
        if(empty($branches) || !is_array($branches)) {
            $this->reply($this->makeText('该商家没有设置分店信息。如想体验门店设置，请关注Pinet_Tech。'));
            return;
        }
        $maxResCount = count($branches) > 9 ? 9 : count($branches);
        
        $results = array();
        
        // the first result: summary
        $company = M('wxuser')
                ->join('tp_raw_image on tp_wxuser.picture = tp_raw_image.id')
                ->where(array('tp_wxuser.token'=>$this->token))
                ->field('tp_raw_image.link picurl, tp_wxuser.company')
                ->find(); 
        $targetUrl = 'http://'.C('wx_handler_server'). U('Wap/Branch/index', array('token'=>$this->token, 'wecha_id'=>$this->fromWxId));
        $picUrl = $company['picurl'];
        $title = '您附近有'.$maxResCount.'家门店信息';
        $description = '您附近有'.$maxResCount.'家门店信息';
        array_push($results, array('url'=>$targetUrl, 'title'=>$title, 'picUrl'=>$picUrl, 'description'=>$description));
        
        // detail branches
        foreach($branches as $branchItem) {
            $targetUrl ='http://'.C('wx_handler_server'). U('Wap/Branch/detail', array('token'=>$this->token, 'id'=>$branchItem['id'], 'wecha_id'=>$this->fromWxId));
            $picUrl = $branchItem['picurl'];
            $title = $branchItem['cname'].' 电话：'.$branchItem['tel'].' 距离：'.$branchItem['distance'].'公里';
            $description = $branchItem['cname'].' 电话：'.$branchItem['tel'].' 距离：'.$branchItem['distance'].'公里';
            array_push($results, array('url'=>$targetUrl, 'title'=>$title, 'picUrl'=>$picUrl, 'description'=>$description));
        }
        
        Log::record('branchprev:'.print_r($results, true));
        Log::save();
        $newsResponse = $this->makeNews($results);
        Log::record('branchafter:'.print_r($newsResponse, true));
        Log::save();
        $this->reply($newsResponse);
    }


    private function findMatchedKeywords($keywords, $keyword, $tokens) 
    {
        $matched_kwds = array();
        foreach ($keywords as $kwd) 
        {
            if (!(strpos($tokens['queryname'], $kwd['function']) === false
                    //&&  time() < func_expiraton[$kwd['function']]['expiretime']  当前功能是否还在使用
                    )) {
                //判断该功能已开通
                if ($kwd['type'] == 1 && $kwd['keyword'] == $keyword) 
                {
                    //完全匹配关键字
                    array_push($matched_kwds, $kwd);
                } 
                else if ($kwd['type'] == 2 && !(strpos($keyword, $kwd['keyword']) === false) && $keyword !='9367a975f19a06750b67f719f4f08ceb') 
                {
                    //包含匹配关键字
                    array_push($matched_kwds, $kwd);
                }
            }
        }
        return $matched_kwds;
    }

    private function getMatchedResponse($matched_kwds, $content)
    {
        $responses = array();
        foreach ($matched_kwds as $keyword) 
        {

            $resp = null;
            $resp_arr = null;

            if ($keyword['function'] == 'dazhuanpan') 
            {
                $resp = $this->getLotteryResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'guaguaka') 
            {
                $resp = $this->getLotteryResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'zajindan') 
            {
                $resp = $this->getLotteryResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'youhuiquan') 
            {
                $resp = $this->getLotteryResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'shouye') 
            {
                $resp = $this->getWebHomeResponse($this->token);
            } 
            else if ($keyword['function'] == 'xiangce') 
            {
                $resp = $this->getAlbumnResponse($this->token);
            } 
            else if ($keyword['function'] == 'dingdan') 
            {
                $resp = $this->getMerchantResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'pinglun') 
            {
                $resp = $this->getReplyResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'yingxiang') 
            {
                $resp = $this->getImpressResponse($this->token, $keyword);
            }
            else if ($keyword['function'] == 'hunqing') 
            {
                $resp = $this->getWeddingResponse($this->token, $keyword);
            } 
            else if ($keyword['function'] == 'xianchang') 
            {
                $resp = $this->getWallResponse($this->token, $keyword ,$content);
            } 
            else if ($keyword['function'] == 'fanyi') 
            {
                $resp = $this->getTranslationResponse($this->token, $content);
            } 
            else if ($keyword['function'] == 'kuaidi') 
            {
                $resp = $this->getExpressResponse($this->token, $keyword['keyword'] ,$content);
            } 
            else if ($keyword['function'] == 'tianqi') 
            {
                $resp = $this->getWeatherResponse($this->token, $keyword['keyword'] ,$content);
            }
            else if ($keyword['function'] == 'caipiao') 
            {
                array_push($responses, $this->getLottery2Response($this->token, $keyword['keyword'] ,$content));
            }
            else if ($keyword['function'] == 'huiyuanka') 
            {
                $resp = $this->getMembershipResponse($this->token);
            } 
            else if ($keyword['function'] == 'wifi') 
            {
                $resp = $this->getWifiResponse($this->token);
            } 
            else if ($keyword['function'] == 'shangcheng') 
            {
                $resp = $this->getShopResponse($this->token, $keyword);
            }
            else if ($keyword['function'] == 'canyin') 
            {
                $resp = $this->getDiningResponse($this->token);
            }
            else if ($keyword['function'] == 'fangchan') 
            {
                $resp = $this->getEstateResponse($this->token);
            }
            else if ($keyword['function'] == 'binguan') 
            {
                $resp = $this->getHotelResponse($this->token, $keyword);
            }
            else if ($keyword['function'] == 'car') 
            {
                switch($keyword['module']) {
                    case 'drive':
                        $resp = $this->getCarRdriveResponse($this->token, $keyword);
                        break;
                    case 'maintain':
                        $resp = $this->getCarRmaintainResponse($this->token, $keyword);
                        break;
                    case 'care':
                        $resp = $this->getCarCareResponse($this->token, $keyword);
                        break;
                    default:
                        $resp = $this->getCarResponse($this->token, $keyword);
                        break;
                }
                
            }
            else if ($keyword['function'] == 'toupiao') 
            {
                $resp = $this->getVote($this->token, $keyword);
            }
            else if ($keyword['function'] == 'disanfang') 
            {
                return $this->getOpenApi($this->token, $keyword['keyword']);
            }
            else if ($keyword['function'] == 'kefu')
            {
                switch ($keyword['module']) 
                {
                    case 'text' :
                        $resp = $this->processTextResponse($this->token, $keyword['pid']);
                        break;
                    case 'voice' :
                        $resp = $this->processVoiceResponse($this->token, $keyword['pid']);
                        break;
                    case 'img' :
                    	file_put_contents('/tmp/kehu', print_r($keyword, true));
                        $resp_arr = $this->processNewsResponses($this->token, array($keyword['pid']));
                        file_put_contents('/tmp/resp_arr', print_r($resp_arr, true));
                        break;
                    case 'transfer' :
                        $resp = $this->getTransferResponse($this->token);
                        break;
                }
            }
            else if ($keyword['function'] == 'panorama') 
            {
                $resp = $this->getPanoramaResponse($this->token);
            }else if($keyword['function'] == 'sign'){ //add By Jason Hu 2014-10-15
            	$this->getSignResponse($this->token, $keyword);
            }else if($keyword['function'] == 'redcash'){ 
            	$this->getRedCashResponse($this->token, $keyword);
            }

            if ($resp != null & is_array($resp)) 
            {
                array_push($responses, $resp);
            }

            if ($resp_arr != null && is_array($resp_arr))
            {
                $responses = array_merge($responses, $resp_arr);
            } 
        }
        return $responses;
    }
    
    function getSignResponse($token, $keyword){
    	 $thisItem = M('Sign_set')->where(array('id' => $keyword['pid']))->find();
         list($content, $type) = array(
         		array(
         			array(
         				$thisItem['title'], 
		         		$thisItem['content'], 
		         		$thisItem['reply_img'], 
	         			C('site_url') . U('Wap/Fanssign/index', array('token' => $token, 'wecha_id' => $this->msg['FromUserName']))
	         		)
	         	), 'news'
         );
         $this->response($content, $type);
    }

    private function formatResponse($responses) 
    {
        $resp;
        $resp_count = count($responses);
        
        if ( $resp_count == 1) 
        {
            if ($responses[0]['type'] == 'text') 
            {
                $resp = $this->makeText($responses[0]['text']);
            } 
            else if ($responses[0]['type'] == 'img') 
            {
                $resp = $this->makeNews(array($responses[0]));
            }
            else if ($responses[0]['type'] == 'voice') 
            {
                $resp = $this->makeMusic($responses[0]);
            }
            else if ($responses[0]['type'] == 'transfer_customer_service') 
            {
                $resp = $this->makeTransfer();
            }
        } 
        else if ( $resp_count > 1)  
        {
            $items = array();
            // 只回复多图文消息，文本消息不转成图文消息
            foreach ($responses as $resp) 
            {

                if ($resp['type'] == 'img') 
                {
                    array_push($items, $resp);
                } 
                else if  ($resp['type'] == 'transfer_customer_service')
                {
                    //若需联系客服，则直接转发
                    $items = array();
                    array_push($items, $resp);
                    break;
                }
               /* else if  ($resp['type'] == 'voice')
                {
                    
                }
                else if  ($resp['type'] == 'text')
                {
                    //array_push($items, array('title' => $resp['text'] , 'description' => '' , 'picUrl' =>''  , 'url' => ''));
                }*/
                
            }

            //若匹配的结果中不含有图文消息，则根据第一条结果返回相应的消息
            if (count($items) == 0) 
            {
                if ($responses[0]['type'] == 'text') 
                {
                    $resp = $this->makeText($responses[0]['text']);
                } 
                else if ($responses[0]['type'] == 'voice') 
                {
                    $resp = $this->makeMusic($responses[0]);
                }
            }
            else 
            {
                if (count($items) == 1 && $items[0]['type'] == 'transfer_customer_service') 
                {
                    $resp = $this->makeTransfer();
                }
                else
                {
                    $resp = $this->makeNews($items);
                }
            }
        } 
        else 
        {
            if ($this->has_default_resp == 0) 
            {   
                //若用户已经设置了默认回复，则无需返回该消息
                $resp = $this->makeText('抱歉，我的主人没有在派尔网络网络微信营销平台上配置这个输入的回复哦，主人已经收到留言了，他会尽快答复您的。');
            }
        }

        return $resp;
    }
    
    /*
     * 需要特殊处理的消息
     */
    private function whilelist_msg_handler($content) {
       
        //默认回复，用于验证是否关注成功
        if ($content == '派尔网络') 
        {
            $resp = $this->makeText('恭喜你已经成功接入全国最领先的微信营销平台！派尔网络，让你的营销领先同行，与众不同！');
            $this->reply($resp);
            exit;
        } 
        else if ($this->token == 'lingzhtech' && strpos($content,'审核') === 0 && strlen($content) > strlen('审核'))
        {
            $username = substr($content, strlen('审核') );
            $users_db = M('users');
            $where    = array('username'=>$username, 'status'=>1);
            $user     = $users_db->where($where)->find();
            if ($user !== FALSE && $user != NULL) 
            {
//               $stats = $users_db->where($where)->save(array('status'=>1));
//               
//               require_once(COMMON_PATH.'/WebsiteUserFuncManager.php');
//               $websiteUserFuncManager = new WebsiteUserFuncManager($user['id']);
//               $websiteUserFuncManager->openDefaultFuncGroups();
               $resp = $this->makeText('亲，恭喜您已经成功通过审核啦！请登录派尔网络微信营销平台，马上开启您的微信营销致富之路吧！您现在可以免费体验通用基础版的所有功能，如需开通更多功能请联系客服或销售经理。派尔网络，让您的营销领先同行，与众不同！');
               $this->reply($resp);
               exit;
            }
            else
            {
                $resp = $this->makeText("亲，您提交待审核的用户名".$username."不存在，请提交正确的用户名，或回到www.lingzhtech.com注册页面，重新提交注册。");
                $this->reply($resp);
                exit;
            }

        }  else if($this->token == 'adspsscp'){
            $prefix_register = 'zc';
            $prefix_bind = 'bd';
            $prefix_call = 'c';
            switch($content) {
                case '注册账号':
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    
                    if($yuntongClient->is_binded()) {
                        $resp = $this->makeText("亲，此微信号已绑定。您可以拨打电话了！");
                        $this->reply($resp);
                       
                    }else {
                        $resp = $this->makeText("亲，您可以输入'".$prefix_register."+手机号'，如'".$prefix_register."13811401028'");
                        $this->reply($resp);
                    }
                    exit;
                    break;
                case '账号绑定':
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    
                    if($yuntongClient->is_binded()) {
                        $resp = $this->makeText("亲，此微信号已绑定。您可以拨打电话了！");
                        $this->reply($resp);
                       
                    }else {
                        $resp = $this->makeText("亲，您可以输入'".$prefix_bind."+手机号+#密码'，如'".$prefix_bind."13811401028#12345'。");
                        $this->reply($resp);
                    }
                    exit;
                    break;
                case '查询余额':
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    
                    if($yuntongClient->is_binded()) {
                        $ret = $yuntongClient->get_balance();
                        
                        //$resp = $this->makeText("账户余额:云通点 ：".$ret['balance1']." 有效期至".$ret['validtime1']." 云呼币 ：".$ret['balance2']." 有效期至".$ret['validtime2']." 绑定手机:".$ret['telnumber']);
                        $resp = $this->makeText($ret['text'].'  绑定手机:'.$ret['telnumber']);
                        $this->reply($resp);
                       
                    }else {
                        $resp = $this->makeText("请先进行绑定！");
                        $this->reply($resp);
                    }
                    exit;
                    break;
                case '解除绑定':
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    
                    if($yuntongClient->is_binded()) {
                        $ret = $yuntongClient->unbind();
                        $resp = $this->makeText("解除绑定号码".$ret);
                        $this->reply($resp);
                        
                    }else {
                        $resp = $this->makeText("请先进行绑定！");
                        $this->reply($resp);
                    }
                    exit;
                    break;
                case '每日签到':
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    
                    if($yuntongClient->is_binded()) {
                        $ret = $yuntongClient->sign();
                        $resp = $this->makeText($ret['text']);
                        $this->reply($resp);
                        
                    }else {
                        $resp = $this->makeText("请先进行绑定！");
                        $this->reply($resp);
                    }
                    exit;
                    break;
                case '拨打电话':
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    
                    if($yuntongClient->is_binded()) {
                        
                        $resp = $this->makeText("输入".$prefix_call."+电话号码进行拨号！");
                        $this->reply($resp);
                        
                    }else {
                        $resp = $this->makeText("请先进行绑定！");
                        $this->reply($resp);
                    }
                    exit;
                    break;
            }
            // prefix check
            if(strpos($content, $prefix_register) === 0) {
                // extract tel
                $tel = substr($content, strlen($prefix_register));
                if(strlen($tel) == 11) {
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    if(!$yuntongClient->is_binded()) {
                        //区分已经注册
                        $ret = $yuntongClient->register($tel);
                        
                        if(isset($ret['errorcode']) && $ret['errorcode'] == '101') {
                            $resp = $this->makeText($ret['text']. ' 请直接进行绑定！');
                        }else {
                            $resp = $this->makeText($ret['text']);
                        }
                        
                        $this->reply($resp);

                    }else {
                        $resp = $this->makeText("亲，此微信号已绑定过！");
                        $this->reply($resp);
                    }
                    exit;
                }else {
                    $resp = $this->makeText("输入有误！");
                    $this->reply($resp);
                    exit;
                }
            }else if(strpos($content, $prefix_bind) === 0) {
                 // extract tel, password
                $tel = substr($content, strlen($prefix_bind), 11);
                $password = trim(substr($content, strlen($prefix_bind)+11),"#");
                if(strlen($tel) == 11 && strlen($password) > 3) {
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                   
                    if(!$yuntongClient->is_binded()) {
                        //先检查是否注册
                        $reg_ret = $yuntongClient->register($tel);
                        if($reg_ret['success']) { //未注册，已经自动为该电话注册了
                            $resp = $this->makeText($reg_ret['text']);
                            $this->reply($resp);
                        }else if(isset($reg_ret['errorcode']) && $reg_ret['errorcode'] == '101') {
                            // 已经注册
                            $yuntongClient->bind($tel, $password);
                            $resp = $this->makeText("绑定号码成功！");
                            $this->reply($resp);
                        }
                        else {
                            // 失败
                            $resp = $this->makeText('请先进行注册！');
                            $this->reply($resp);
                        }
                    }else {
                        $resp = $this->makeText("亲，此微信号已绑定。");
                        $this->reply($resp);
                    }
                    exit;
                }else {
                    $resp = $this->makeText("输入有误！");
                    $this->reply($resp);
                    exit;
                }
            }else if(strpos($content, $prefix_call) === 0) {
                 // extract tel
                $cld_tel = substr($content, strlen($prefix_call), 11);
                if(strlen($cld_tel) == 11) {
                    include(COMMON_PATH.'/YuntongClient.php');
                    $yuntongClient = new YuntongClient($this->fromWxId, $this->token);
                    if($yuntongClient->is_binded()) {
                        $ret = $yuntongClient->call($cld_tel);
                        // 发起呼叫成功，稍后你的电话会响铃。。。被叫方：xxx
                        if($ret['result'] == 'success') {
                            $resp = $this->makeText('发起呼叫成功，稍后你的电话会响铃。。。被叫方：'.$cld_tel);
                        }else{
                            $resp = $this->makeText($ret['text']);
                        }
                        $this->reply($resp);

                    }else {
                        $resp = $this->makeText("请先进行绑定！");
                        $this->reply($resp);
                        
                    }
                    exit;
                }else {
                    $resp = $this->makeText("输入有误！");
                    $this->reply($resp);
                    exit;
                }
            }
        }
    }
    /**
     * 负责处理用户所发送的文本消息、语音消息和图片消息
     * @param content 用户输入内容
     */
    private function handleMsgPush ($content='') 
    {
        Log::record('customer input:'.$content, Log::NOTICE);

        $responses = null;
        if ($this->cs_setting != false && ($this->cs_setting['type'] == 1)) 
        {
            //商户主动将请求转发至客服
            $responses = array();
            $resp = $this->getTransferResponse();
            array_push($responses, $resp);
        }  
        else
        {
            //第三方平台处理
            // whitelist keyword handler
            // $this->whilelist_msg_handler($content);

            $tokens = M('Token_open')->field('id,queryname')->where(array('token'=>$this->token ,'uid'=>$this->userId))->find();
            $qualifiedFunctions = M('function')->join('inner join tp_user_func_group on tp_function.fgid = tp_user_func_group.group_id')
                    ->where(array(
                        'tp_user_func_group.user_id'=>$this->userId, 
                        'tp_user_func_group.status'=>1, 
                        'tp_user_func_group.expire_time' => array('gt', time()),
                        'tp_function.status' => 1
                        ))
                    ->field('tp_function.funname funname')
                    ->select();
            Log::record('qualified functions before:'.$this->userId.' '.$tokens['queryname']);
            if($tokens !== false && $qualifiedFunctions !== false) {
                $queryNameList = array();
                if(is_array($qualifiedFunctions)) {
                    foreach($qualifiedFunctions as $qf) {
                        if(strpos($tokens['queryname'], $qf['funname']) !== false) {
                            array_push($queryNameList, $qf['funname']);
                        }
                    }
                }
                $tokens['queryname'] = implode(',', $queryNameList);
            }
            Log::record('qualified functions after:'.$this->userId.' '.$tokens['queryname']);

            $keywords = M('keyword')->where(array('token' => $this->token))->order("sorts desc,pid")->select();
            
            $matched_kwds = array();
            if ($keywords) {
                $matched_kwds = $this->findMatchedKeywords($keywords, $content, $tokens) ;
                $this->write_log('matched keywords:'.print_r($matched_kwds,true));
            }
            
            $responses = array();

            if (count($matched_kwds) == 0) 
            {
                //没有匹配的关键词
                $mis_match_resp = M('other')->where(array('token' => $this->token))->find();
                if ($mis_match_resp) {
                    $this->has_default_resp = 1;
                    if (!empty($mis_match_resp['keyword'])) 
                    {
                        $matched_kwds = $this->findMatchedKeywords($keywords, $mis_match_resp['keyword'], $tokens) ;
                    } 
                    else
                    {
                        array_push($responses, array('type' => 'text', 'text' => $mis_match_resp['info']));
                    } 
                }
            }

            
            if (count($matched_kwds) > 0) 
            {
                $responses = array();
                $responses = $this->getMatchedResponse($matched_kwds, $content);
            } 
        }
        
        
        

        $this->write_log('responses:'.print_r($responses,true));

        $format_resp = $this->formatResponse($responses);
        $this->write_log('filted responses:'.print_r($format_resp,true));

        $this->reply($format_resp);
    }

    private function getTransferResponse($token='')
    {
        $resp['type'] = 'transfer_customer_service';
        return $resp;
    }

    private function processTextResponse($token ='', $id = '') 
    {
        //文本回复
        $where      = array('token'=>$token, 'id'  => $id);
        $text_db    = M('text');
        $text_resp  = $text_db->where($where)->order('updatetime desc')->find();
        if ($text_resp) 
        {
            $text_db->where(array('id'=>$text_resp['id']))->setInc('click');
            $text_resp['type'] = 'text';
            return $text_resp;
        } 
        else 
        {
            return null;
        } 
    }
 
    private function processVoiceResponse($token ='', $id = '') {
        //语音回复
        $where = array('token' => $token, 'id'  => $id);
        $voice_resp = M('voice')->where($where)->order('updatetime desc')->find();
        if ($voice_resp) 
        {
            //M('voice')->where(array('id'=>$voice_resp['id']))->setInc('click');
            $format_voice_resp = array(
                            'type'          =>'voice',
                            'MusicUrl'      => $voice_resp['musicurl'],
                            'HQMusicUrl'    => $voice_resp['hqmusicurl'],
                            'description'   => $voice_resp['description'],
                            'title'         => $voice_resp['title'],
                            );
            return $format_voice_resp;
        } 
    }

    private function processNewsResponses($token ='', $ids)
    {
        //图文回复
        $where = array('token' => $token, 'id'  => array('in', implode(',', $ids))); 
        $img_db = M('img');
        $img_items = $img_db->field(array('id','title', 'text'=>'description', 'pic'=>'picUrl','url', 'linktype', 'service'))->where($where)->limit(10)->select();
        if ($img_items) 
        {
            foreach ($img_items as $key => $val) 
            {
                $img_db->where(array('id'=>$img_items[$key]['id']))->setInc('click');
                //linktype为info或者normal时应为正常逻辑，不是系统模块
                if (!empty($img_items[$key]['linktype']) 
                    && !in_array($img_items[$key]['linktype'], array('info','normal')) 
                    && !empty($img_items[$key]['service'])) {

                    $tags = explode('-', $img_items[$key]['service']);

                    $service_url = ServiceHelper::constructHyperLink($token, $tags[0], $tags[1], $tags[2]);
                    if (!empty($service_url)) 
                    {
                        if ($tags[0] != 'xiangce') 
                        {
                            //相册url不需要wechat_id, 否则token读取不正确
                            $service_url = $service_url.'&wecha_id='.$this->fromWxId;
                        }
                        $img_items[$key]['url'] = $service_url;
                    }
                            
                } 
                else if (empty($img_items[$key]['url'])) 
                {
                    $img_items[$key]['url'] = U('Wap/ImageDetail/index@'.C('wx_handler_server'), array('id'=>$img_items[$key]['id'],'template'=>'ktv_content', 'token' => $token));          
                }
                else
                {
                    if(strpos($img_items[$key]['url'],'?')&&!strpos($img_items[$key]['url'], 'wecha_id='))//检查url中是否已经有参数
                    {
                        $img_items[$key]['url'].='&wecha_id='.$this->fromWxId; //为用户的链接加上wxid
                    }
                }
                $img_items[$key]['type'] = 'img';
            }
            
        }         
        file_put_contents('/tmp/img_item', print_r($img_items, true));
        return $img_items;
    }
   
    
    private function getExpressResponse($token='', $keyword = '', $content) {
        include 'expresses.php';
        $id = '102401';
        $secret = '10025ed8aa7e4607bb84ba4655e11fa0';
        $matchCode = "";
        $express_num = "";
        foreach ($expresses as $exp => $code) { 
            if (!(strpos($content, $exp) === false)) {
                $matchCode = $code;
                $content = str_replace($exp, "", $content);
                $content = str_replace("物流", "", $content);
                $content = str_replace("快递", "", $content); 
                $express_num = trim(str_replace($exp, "", $content));
                $this->write_log($exp." ".$matchCode." ".$express_num);
                break;
            }
        }
        if (empty($matchCode) || empty($express_num)) {
            return array('type' => 'text', 'text' => '对不起，我没查到"'.$content.'"相关订单信息！');
        }
        //$express_num = urlencode(substr($content, strlen($keyword), strlen($content) - strlen($keyword)));
        //$express_comp = $express_map[$keyword];
        $url = 'http://api.ickd.cn/?id='.$id.'&secret='.$secret.'&com='.$matchCode.'&nu='.$express_num.'&type=json&ord=desc&encode=utf8';
        $this->write_log($url);
        $remote_resp = file_get_contents($url);

        $ret_json = json_decode($remote_resp, true);

        $str = '您所查询的'.$ret_json['expTextName'].'订单:'.$ret_json['mailNo'].', 进度跟踪如下：';
        for ($i = 0; $i < count($ret_json['data']); $i++) {
            $str = $str."\n".$ret_json['data'][$i]['time'].' '.$ret_json['data'][$i]['context'];
        }
        $this->write_log($str);
        if (!empty($ret_json) && $ret_json['errCode'] == 0) {
            return array('type' => 'text', 'text' => $str);
        } else {
            return array('type' => 'text', 'text' => '对不起，我没查到"'.$content.'"相关订单信息！');
        }
    }

    private function getWeatherResponse($token='', $keyword = '', $content) {
        include 'weather-city.php';
        $content = trim(str_replace("天气", "", $content));
        if (strlen($content) == 0) {
            $Cache = Cache::getInstance('File',array('expire'=>'3600'));
            $loc = $Cache->get($this->fromWxId);
            if (!empty($loc)) {
                $locUrl = "http://api.map.baidu.com/geocoder?output=json&location=".$loc;
                $remote_resp0 = file_get_contents($locUrl);
                $ret_json0 = json_decode($remote_resp0, true);
                $content = $ret_json0['result']['addressComponent']['city'];
                $content = str_replace("市", "", $content);
            } else {
                return array('type' => 'text', 'text' => '无法提供您所在地天气信息，您可以点击右上角的小人头勾选【提供位置信息】或回复直接城市名+天气（如：北京天气）');
            }
        }
        $city_code = $weather_city[$content];
        if (empty($city_code)) {
            return array('type' => 'text', 'text' => '对不起，我没查到'.$content.'天气信息！');
        }
        $subcode = substr($city_code, strlen($city_code) - 6, 4);
        $url = "http://weather.api.114la.com/".$subcode."/".$city_code.".txt";
        $this->write_log($url);
        $remote_resp = file_get_contents($url);
        $remote_resp = str_replace("window.Ylmf.getWeather(", "", $remote_resp);
        $remote_resp = str_replace(")", "", $remote_resp);
        
        $ret_json = json_decode($remote_resp, false);
        $Cache = Cache::getInstance('File',array('expire'=>'3600'));
        $pm25Cache = $Cache->get($content);
        if (empty($pm25Cache)) {
            $this->write_log("miss cache ".$content);
            $url = "http://www.pm25.in/api/querys/pm2_5.json?token=FubpqbUtsXpP4ncs6yZA&stations=no&city=".urlencode($content);
            $remote_resp2 = file_get_contents($url);
            
            $ret_json2 = json_decode($remote_resp2, true);
            if (count($ret_json2) > 0) {
                if (!empty($ret_json2['error'])) {
                    $Cache->set($content, "null");
                } else {
                    $pm25Cache = $ret_json2[0];
                    $Cache->set($content, $pm25Cache);
                }
            }
        }
        $str = "【".$ret_json->weatherinfo->city."天气预报】\n".$ret_json->weatherinfo->date_y." ".$ret_json->weatherinfo->fchh."时发布"."\n\n实时天气\n".$ret_json->weatherinfo->weather1." ".$ret_json->weatherinfo->temp1." ".$ret_json->weatherinfo->wind1;
        if (!empty($pm25Cache) && $pm25Cache != "null") {
            $str = $str."\n\n空气质量\n".$pm25Cache['quality'].' 空气质量指数 '.$pm25Cache['aqi'].' PM2.5 '.$pm25Cache['pm2_5'];
        }
        $str = $str."\n\n温馨提示：".$ret_json->weatherinfo->index_d."\n\n明天\n".$ret_json->weatherinfo->weather2." ".$ret_json->weatherinfo->temp2." ".$ret_json->weatherinfo->wind2."\n\n后天\n".$ret_json->weatherinfo->weather3." ".$ret_json->weatherinfo->temp3." ".$ret_json->weatherinfo->wind3;
        if ($loc) {
           $str = $str."\n\n查询其他城市 请回复 城市名+天气（如：北京天气）";
        }
        if (!empty($ret_json)) {
            return array('type' => 'text', 'text' => $str);
        } else {
            return array('type' => 'text', 'text' => '对不起，我没查到'.$keyword.'天气信息！');
        }
    }
    
    private function getLottery2Response($token='', $keyword = '', $content) {
        if(substr($content,0,6) == "彩票" && strlen($content) >= 6){
             if (strlen($content) == 6) {
                 $history = M('wx_access')->where(array('from_user'=>$this->fromWxId, 'to_public_token'=>$token,
                 '_string'=>"msg_content<>'彩票'",'msg_content'=>array('like', '彩票%')))->order('receive_time desc')->find();
                 if (!empty($history)) {
                     $content = $history['msg_content'];
                 }
             }
             $content = trim(substr($content, 6, strlen($content)));
             $lottery = M('caipiao')->where(array('name'=>$content))->find();
             if (!empty($lottery)) {
                 $contentStr = $lottery['name']."\n第".$lottery['period']."\n开奖日期 ".$lottery['time']."\n开奖号码 ".$lottery['result'];
                 if (!empty($lottery['money'])) {
                     $contentStr = $contentStr."\n头奖奖金 ".$lottery['money'];
                 }
             } else {
                 $contentStr = "抱歉，暂不支持该彩种!";
             }
             return array('type' => 'text', 'text' => $contentStr);
        }
        return null;
    }

    private function getTranslationResponse($token='', $keyword = '') {
        $content = urlencode(substr($keyword, strlen('翻译'), strlen($keyword) - strlen('翻译')));
        $url = 'http://fanyi.youdao.com/openapi.do?keyfrom=coonut&key=1354754262&type=data&doctype=json&version=1.1&q='.$content;
        $remote_resp = file_get_contents($url);

        $ret_json = json_decode($remote_resp, true);

        $this->write_log($remote_resp, true);
        if (!empty($ret_json) && count($ret_json['translation']) > 0) {
            return array('type' => 'text', 'text' => $ret_json['translation'][0]);
        } else {
            return array('type' => 'text', 'text' => '你说的是什么？我不懂');
        }
    }

    private function getWifiResponse($token='', $keyword = '') 
    {
        //获取商家ID
        $ap = M('wifi_ap')->where(array('token' => $token ))->find();
        if (empty($ap)) 
        {
            Log::record("No wifi merchant config for token:".$token, Log::INFO);
            return null;
        }

        $where = array('token'=>$token, 'function'  => 'wifi');
        $resp = null;
        //获取微信回复类型，文本或者图文 或一键下单
        if ($ap['resp_type'] == 1) 
        {
            $text_db = M('text');
            $resp = $text_db->where($where)->order('updatetime desc')->find();
            if ($resp) 
            {
                $text_db->where(array('id'=>$resp['id']))->setInc('click');
            } 
        } 
        else if ($ap['resp_type'] == 2 || $ap['resp_type'] == 3) 
        {
            $img_db = M('Img');
            $resp = $img_db->field(array('id','title', 'text', 'pic'=>'picUrl'))->where($where)->find();
            if ($resp) 
            {
                $img_db->where(array('id'=>$resp['id']))->setInc('click');
            } 
        }

        if ($resp) 
        {
            if ($ap['type'] == 1) 
            {
                //树熊路由器
                $config = array(0 => array("app_key" => 10001, 'app_secret' => 'c648dd7d559b11e3847000163e0c16e5' ));
                $ret_json;
                $ret_json = $this->getPasswordFromWitown($config[0]['app_key'], $config[0]['app_secret'], $ap['merchant_id'],'true');
				if (!empty($ret_json) && isset($ret_json['passwd'] ))
				{
                	$resp = $this->formateWitownResp($ap['resp_type'], $resp, $ret_json['passwd'], $ret_json['url'],  $ap['merchant_id']);
                    return $resp;
				}
            }
            else if ($ap['type'] == 2) 
            {
                //安网路由器
                $resp = $this->formateSecnetResp($ap['resp_type'], $resp);
                return $resp;
            }
        }
        else
        {
            Log::record('witown error: code: '.print_r($ret_json,true), Log::NOTICE); 
        }
        return array('type' => 'text', 'text' => '出错了，我也拿不到密码，问店小2吧。');
    }

    private function formateSecnetResp($resp_type, $resp)
    {
        if ($resp_type == 1) 
        {
            $resp['text'] = str_replace('#认证链接#', "http://".C('wx_handler_server').'/vweb/'.$this->token, $resp['text']);
            $resp['type'] = 'text';
        }
        else if ($resp_type == 2 || $resp_type == 3) 
        {
            unset($resp['text']);
            $resp['url'] = $url; 
            $resp['type'] = 'img';
        }
        return $resp;
    }

    /**
     * @param $url  一键登录网址
    */
    private function formateWitownResp($resp_type, $resp, $password, $url, $merchant_id)
    {

        if ($resp_type == 1) 
        {
            $resp['text'] = str_replace('#wifi#', $password, $resp['text']);
            if (!empty($url) )
            {
                $resp['text'] = str_replace('#认证链接#', $url, $resp['text']);
            }
            $resp['type'] = 'text';
        }
        else if ($resp_type == 2) 
        {
            $resp['description'] = str_replace('#wifi#', $password, $resp['text']);
            unset($resp['text']);
            $resp['url'] = str_replace('#mid#', $ap['merchant_id'], C('witown_auth_portal')); 
            $resp['type'] = 'img';
        }
        else if ($resp_type == 3) 
        {
            $resp['description'] = str_replace('#wifi#', $password, $resp['text']);
            unset($resp['text']);
            $resp['url'] = $url; 
            $resp['type'] = 'img';
        }
        return $resp;
    }

    private function getPasswordFromWitown($app_key, $app_secret, $merchant_id, $is_need_url = 'false') 
    {
        $param_arr = array (    
            'appKey' => $app_key, 
            'v' => '1.0', 
            'method' => 'shop.pass.get', 
            'merchantId' => $merchant_id,
            'openUserId' => $this->fromWxId,
            'needUrl' => $is_need_url); 
            
        ksort($param_arr, SORT_STRING);
        $param_str = $app_secret;

        $get_str = '';
        foreach ($param_arr as $key => $value) 
        {
            $param_str .= $key;
            $param_str .= $value;
            $get_str .= $key.'='.$value.'&';
        }

        $param_str .= $app_secret;
        $access_token = strtoupper(sha1($param_str));

        Log::record('param:'.$param_str, Log::DEBUG);
        $witown_url = C('witown_api');
        $ret_json = null;
        if (!empty($witown_url)) 
        {
            $url = $witown_url.$get_str.'sign='.$access_token;
            Log::record('url:'.$url, Log::DEBUG);
            $remote_resp = file_get_contents($url);
            Log::record('resp:'.$remote_resp, Log::DEBUG);
            $ret_json = json_decode($remote_resp, true);
        }
        else
        {
            Log::record('No witown api config', Log::INFO);
        }
        return $ret_json;
    }


    private function getMerchantResponse($token ='', $keyword=null) 
    {
        $merchant = M('host')->field(array('id','title', 'info'=>'description', 'ppicurl'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($merchant) 
        {
            $merchant['type']   = "img";
            $merchant['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Host&a=index&token=".$token.'&wecha_id='.$this->fromWxId.'&hid='.$merchant['id'].'&wxref=mp.weixin.qq.com';
            return $merchant;
        }
        else 
        {
            return null;
        }
    }
    
    private function getImpressResponse($token ='', $keyword=null) 
    {
        $impress = M('impress')->field(array('id','title', 'msg_pic_url'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($impress) 
        {
            $impress['type']   = "img";
            $impress['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Impress&a=index&token=".$token.'&wecha_id='.$this->fromWxId.'&id='.$impress['id'];
            return $impress;
        }
        else 
        {
            return null;
        }
    }
    
    private function getReplyResponse($token ='', $keyword=null) 
    {
        $reply = M('reply')->field(array('id','title', 'msg_pic_url'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($reply) 
        {
            $reply['type']   = "img";
            $reply['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Reply&a=index&token=".$token.'&wecha_id='.$this->fromWxId.'&id='.$reply['id'];
            return $reply;
        }
        else 
        {
            return null;
        }
    }
    
    private function getWeddingResponse($token ='', $keyword=null) 
    {
        $wedding = M('wedding')->field(array('id','title', 'description', 'msg_pic_url'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($wedding) 
        {
            $wedding['type']   = "img";
            $wedding['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Wedding&a=index&token=".$token.'&wecha_id='.$this->fromWxId.'&id='.$merchant['id'];
            return $wedding;
        }
        else 
        {
            return null;
        }
    }
    
    private function getWallResponse($token='', $keyword = array(), $content) 
    {
        $wall = M('Wall')->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
        if (!$wall) {
            return null;
        }
        $time = time();
        if ($time < strtotime($wall['start_time'])) {
            $str = "活动尚未开始，敬请期待";
            return array('type' => 'text', 'text' => $str);
        }

        //若活动已经结束，则不处理
        if ($time > strtotime($wall['end_time'])){
            return null;
        }

        if ($keyword['module'] == 'wall'){
            return $this->handleWallMessage($token, $keyword['keyword'], $content, $wall);
        }else if ($keyword['module'] == 'nickname') {
            return $this->handleWallNickname($token, $keyword['keyword'], $content);
        } else if ($keyword['module'] == 'lottery') {
            return $this->handleWallLottery($token, $keyword['keyword'], $wall);
        }
    }
    
    function getUserInfo($token) {
	    	$wx_user = M('wxuser')->where(array('wxid'=>$this->oriWxId, 'token'=>$token, 'status'=>1))->find();
			if (empty($wx_user['appid']) || empty($wx_user['appsecret'])){
		    	return null;
			}
			require_once(COMMON_PATH.'/WeixinAPI.php');
			$weixin = new WeixinAPI($wx_user['appid'], $wx_user['appsecret']);
			return $weixin->getUserInfo($this->fromWxId);
    }

    private function handleWallMessage($token='', $keyword = '', $content, $wall)
    {
		$wallopen_user = M('wecha_user')->where(array('token'=>$token, 'wecha_id'=>$this->fromWxId, 'wallopen'=>'1'))->find();
    	if ($wallopen_user) {
    		$content = trim(preg_replace('/^'.$wall["keyword"].'/', "", $content));
    	}else{
	        $content = trim(preg_replace('/^'.$wall["keyword"].'/', "", $content));
    	}
        $name_remind = "";
        $name = "";

        $wecha_user_db = M('wecha_user');
        $where['token'] = $token;
        $where['wecha_id'] = $this->fromWxId;
        $user = $wecha_user_db->where($where)->find();
        if ($this->wechat_type == 1 && $this->is_authed == 1) 
        {
            $name = htmlspecialchars($user['nickname'])."先生/女士,";
            //如果是认证服务号，则主动获取用户信息，昵称、头像等
            if ($user  &&  $user['nickname']  && $user['headimgurl']) {
            	$days = (time() - $user['updatetime']) / (60*60*24); 
            	if( (int)$days >= 1 ){
					$userinfo =$this->getUserInfo($token);
					$data['userinfo']   = json_encode($userinfo);
					$data['sex']        = $userinfo['sex'];
					require_once(COMMON_PATH.'/emoji.php');
					$data['nickname']   = emoji_unified_to_html($userinfo['nickname']);
					$data['city']       = $userinfo['city'];
					$data['province']   = $userinfo['province'];
					$data['country']    = $userinfo['country'];
					$data['headimgurl'] = $userinfo['headimgurl'];
					$data['updatetime']    = time();
					$wecha_user_db->where($where)->save($data);
            	}
            }else if ($user  &&  (!$user['nickname'] || !$user['headimgurl'])){
                // 若已通过认证接口获取过用户信息，则直接读取
            	$userinfo = json_decode($user['userinfo']);
            	if (empty($userinfo->nickname) || empty($userinfo->headimgurl)) {
	            	 $userinfo = json_encode($this->getUserInfo($token));
            	}
            	require_once(COMMON_PATH.'/emoji.php');
                $reply['nickname']      = emoji_unified_to_html( $userinfo->nickname);
                $reply['headimgurl']    = $userinfo->headimgurl;
                $reply['updatetime']    = time();
                if (empty($user['headimgurl']) || empty($user['nickname'])) {
	                $wecha_user_db->where($where)->save($reply);
                }
                $name = htmlspecialchars($userinfo->nickname)."先生/女士,";
            }else{
                // 如果是认证服务号，则读取用户信息
                $userinfo =$this->getUserInfo($token);
                    
                $data['userinfo']   = json_encode($userinfo);
                $data['sex']        = $userinfo['sex'];
                
                require_once(COMMON_PATH.'/emoji.php');
                $data['nickname']   = emoji_unified_to_html($userinfo['nickname']);
                $data['city']       = $userinfo['city'];
                $data['province']   = $userinfo['province'];
                $data['country']    = $userinfo['country'];
                $data['headimgurl'] = $userinfo['headimgurl'];
                $data['updatetime']    = time();
                if ($user != null){
                    $ret = $wecha_user_db->where($where)->save($data);
                }else{
                    $data['token']      = $token;
                    $data['wecha_id']   = $this->fromWxId;
                    $ret = $wecha_user_db->add($data);
                }
                // 获得用户信息
                $reply['nickname']      = $userinfo['nickname'];
                $reply['headimgurl']    = $userinfo['headimgurl'];
                $name = htmlspecialchars($userinfo['nickname'])."先生/女士,";
            }
        } else  {
            if ($user == null || empty($user['nickname'])){
                $keyword = M('keyword')->where(array('token' => $this->token, 'function'=> 'xianchang','module'=>'nickname','pid'=>$wall['id']))->find();
                if ($keyword != false) {
                    $name_remind = "，您还没有设置昵称，请发送'".$keyword['keyword']."+昵称'设置";
                }
            } else{
                $name = htmlspecialchars($user['nickname'])."先生/女士,";
            }
        }
        
        $faces = C('face');
    	foreach ($faces as $v) {
	        $content = str_replace($v['code'], '<img src="'.$v['img'].'">', $content);
        }
            
        $reply['token']     = $token;
        $reply['wall_id']   = $wall['id'];
        $reply['text']      = $content;
        $reply['createtime'] = time();
        $reply['wecha_id']    = $this->fromWxId;
        $reply['status']    = 1;
        M('wall_reply')->add($reply);
            
        $count = M('wall_reply')->where(array('token'=>$token, 'wall_id' => $wall['id']))->count("distinct(wecha_id)");
        M('Wall')->where(array('token' => $token, 'id' => $wall['id'] ))->save(array('num'=>$count));
            
        $str = $name."我们已收到您的消息，感谢您参与微现场活动".$name_remind;
        return array('type' => 'text', 'text' => $str);
    }

    private function handleWallNickname($token='', $keyword = '', $content)
    {
        $wecha_user_db      = M('wecha_user');
        $where['token']     = $token;
        $where['wecha_id']  = $this->fromWxId;
        $user = $wecha_user_db->where($where)->find();
        $nickname = trim(preg_replace('/^'.$keyword.'/', "", $content));
        if (empty($nickname)) {
            $str = "您输入的昵称不正确，请重新编辑发送'$keyword+昵称'。";
	        return array('type' => 'text', 'text' => $str);
        }else{
            $data['nickname']   = $nickname;
            if ($user != null){
                $ret = $wecha_user_db->where($where)->save($data);
            }else{
                $data['token']      = $token;
                $data['wecha_id']   = $this->fromWxId;
                $ret = $wecha_user_db->add($data);
            }
            $str = "您的昵称已经更新为'$nickname'";
            return array('type' => 'text', 'text' => $str);
        }
    }

    private function handleWallLottery($token='', $keyword = '', $wall)
    {
        $wecha_user_db      = M('wecha_user');
        $data['token']      = $token;
        $data['wecha_id']   = $this->fromWxId;
        $user = $wecha_user_db->where($data)->find();

        $nickname = "";
        if ($user != false && !empty($user['nickname'])){
            $nickname = htmlspecialchars($user['nickname']);
        }

        $winner  = M('wall_winner')->where(array('token'=>$this->token,'wall_id'=>$wall['id'],'wecha_id'=>$this->fromWxId))->find();
        if ($winner == false){
            $str = "'$nickname'先生/女士，很遗憾，您未中奖，请再接再厉。";
            return array('type' => 'text', 'text' => $str);
        }

        $lotterys = unserialize($wall['lotterys']);
        for($i = 0; $i < count($lotterys); $i++){
            if ($lotterys[$i][1] == $winner["lottery"]){
                $str = "'$nickname'先生/女士，恭喜您参加'".$wall['title']."'抽奖活动中了【".$lotterys[$i][0]."】,奖品是一份【".$lotterys[$i][1]."】";
                return array('type' => 'text', 'text' => $str);
            }
        }
        $str = "'$nickname'先生/女士，很遗憾，您未中奖，请再接再厉。";
        return array('type' => 'text', 'text' => $str);
    }
    
    private function getHotelResponse($token ='', $keyword=null) 
    {
        $hotel = M('Hotel')->field(array('id','title', 'info'=>'description', 'ppicurl'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($hotel) 
        {
            $hotel['type']   = "img";
            $count = M('Hotel')->where(array('token'=>$token, 'status'=>1))->count();
            if ($count > 1) {
                $hotel['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Hotel&a=online&token=".$token.'&wecha_id='.$this->fromWxId.'&hid='.$hotel['id'];
            } else {
                $hotel['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Hotel&a=index&token=".$token.'&wecha_id='.$this->fromWxId.'&hid='.$hotel['id'];
            }
            return $hotel;
        }
        else 
        {
            return null;
        }
    }
    
    private function getCarCareResponse($token ='', $keyword=null) {
        $care = M('car_care')->field(array('id', 'img_title' => 'title', 'description', 'img_url'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($care)
        {
            $care['type']   = "img";
            $care['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Car&a=care&token=".$token.'&wecha_id='.$this->fromWxId.'&id='.$care['id'];
            
            return $care;
        }
        else 
        {
            return null;
        }
    }
    private function getCarRmaintainResponse($token ='', $keyword=null) {
        $rdrive = M('car_rmaintain')->field(array('id', 'img_title' => 'title', 'description', 'img_url'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($rdrive)
        {
            $rdrive['type']   = "img";
            $rdrive['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Car&a=rmaintain&token=".$token.'&wecha_id='.$this->fromWxId.'&id='.$rdrive['id'];
            
            return $rdrive;
        }
        else 
        {
            return null;
        }
    }
    private function getCarRdriveResponse($token ='', $keyword=null) {
        $rdrive = M('car_rdrive')->field(array('id', 'img_title' => 'title', 'description', 'img_url'=>'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($rdrive)
        {
            $rdrive['type']   = "img";
            $rdrive['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Car&a=rdrive&token=".$token.'&wecha_id='.$this->fromWxId.'&id='.$rdrive['id'];
            
            return $rdrive;
        }
        else 
        {
            return null;
        }
    }
    private function getCarResponse($token ='', $keyword=null) {
        $brand = M('car_brand')->field(array('id', 'title', 'introduction'=>'description', 'picUrl'))->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
       
        if($brand)
        {
            $brand['type']   = "img";
            $brand['url']    = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Car&a=index&token=".$token.'&wecha_id='.$this->fromWxId.'&bid='.$brand['id'];
            
            return $brand;
        }
        else 
        {
            return null;
        }
    }
    private function getAlbumnResponse($token ='') {
        $photo = M('Img')->field(array('id','title', 'text'=>'description', 'pic'=>'picUrl','url'))->where(array('token' => $token, 'function'=>'xiangce' ))->find();
        if ($photo != false) 
        {
            $photo['type'] = 'img';
            return $photo;
        } 
        else 
        {
            return null;
        }
    }
    
    private function getPanoramaResponse($token='')
    {
        $panorama = M('Img')->field(array('id','title', 'text'=>'description', 'pic'=>'picUrl','url'))->where(array('token' => $token, 'function'=>'panorama' ))->find();
        if ($panorama != false) 
        {
            $panorama['type'] = 'img';
            return $panorama;
        } 
        else 
        {
            return null;
        }
    }

    private function getShopResponse($token ='', $keyword=null) 
    {
        $shop_id = null;
        if ($keyword != null) 
        {
            $shop_id = $keyword['pid'];
        }

        $shop_where = array('token' => $token);
        if (!empty($shop_id)) 
        {
            $shop_where['shop_id'] = $shop_id;
        }

        $shop = M('b2c_shop')->field(array('name'=>'title', 'desc'=>'description', 'logo_url'=>'picUrl', 'fake_id'=>'branch_id'))->where($shop_where)->find();
        if ($shop != false) 
        {
            $shop['type']   = "img";
            
            $host_name = $this->merchant_code.C('shop_domain');
            if(C('hornor_shop_domain') == 0) {
                $host_name = C('wx_handler_server');
            }
            //指定分店ID，否则使用默认
            $shop['url']    = 'http://'.$host_name.'/index.php?g=Wap&m=Shop&a=index&token='.$token;
            if (!empty($shop['branch_id'])) 
            {
                $shop['url'] = $shop['url'].'&bid='.$shop['branch_id'];
            }
            
            if ($this->wechat_type != 1 || $this->is_authed != 1) 
            {
                $shop['url'] = $shop['url'].'&wecha_id='.$this->fromWxId;
            }
            
            return $shop;
        } 
        else 
        {
            return null;
        }
        
    }
    
    private function getDiningResponse($token ='') 
    {
        $dine = M('dine_rest')->field(array('name'=>'title', 'desc'=>'description', 'logo_url'=>'picUrl'))->where(array('token' => $token))->find();
        $count = M('dine_restlist')->where(array('token' => $token, 'status'=>1))->count();
        if ($dine != false) 
        {
            $dine['type']   = "img";
            if ($count > 1) {
                $dine['url']    = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Dining&a=rest&token='.$token.'&wecha_id='.$this->fromWxId;
            } else {
                $dine['url']    = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Dining&a=index&token='.$token.'&wecha_id='.$this->fromWxId;
            }
            return $dine;
        } 
        else 
        {
            return null;
        }
        
    }
    
    private function getEstateResponse($token ='') 
    {
        $estate = M('estate')->field(array('title', 'description', 'msg_pic_url'=>'picUrl'))->where(array('token' => $token))->find();
        if ($estate != false) 
        {
            $estate['type']   = "img";
            $estate['description'] = $estate['title']."欢迎您！";
            $estate['url']    = 'http://'.C('wx_handler_server').'/index.php?g=Wap&m=Estate&a=index&token='.$token.'&wecha_id='.$this->fromWxId;
            return $estate;
        } 
        else 
        {
            return null;
        }
        
    }

    private function getWebHomeResponse($token ='') 
    {
        $home = M('Img')->field(array('id','title', 'text'=>'description', 'pic'=>'picUrl','url'))->where(array('token' => $token, 'function'=>'shouye' ))->find();
        if ($home != false) 
        {
            $home['type'] = 'img';
            $home['url'] = $home['url'].'&wxref=mp.weixin.qq.com'.'&wecha_id='.$this->fromWxId;
            return $home;
        } 
        else 
        {
            return null;
        }
        
    }

    private function getMembershipResponse($token ='')
    {

        //获取会员首页
        $user_member_card_id = M('member_card_create')->where(array('wecha_id'=>$this->fromWxId,'status'=>1))->find();
        $url =  "http://".C('wx_handler_server')."/index.php?g=Wap&m=Card&a=vip&token=".$token.'&wecha_id='.$this->fromWxId;
        if ($user_member_card_id == false) 
        {
            //还没领取过会员卡
            $url =  "http://".C('wx_handler_server')."/index.php?g=Wap&m=Card&a=get_card&token=".$token.'&wecha_id='.$this->fromWxId;
        }

        //图文回复
        $membership = M('Img')->field(array('id','title', 'text'=>'description', 'pic'=>'picUrl','url'))->where(array('token' => $token, 'function'=>'huiyuanka' ))->find();

        if ($membership != false) 
        {
            $membership['type'] = 'img';
            $membership['url'] = $url;
            return $membership;
        } 
        else 
        {
            return null;
        }
    }

    private function getLotteryResponse($token ='', $keyword = '') 
    {
        $lottery = M('Lottery')->field(array('id','title', 'txt'=>'description', 'starpicurl'=>'picUrl','type' => 'category'))-> where(array('token' => $token, 'id'=>$keyword['pid'], 'status' => 1))->find();
        if ($lottery != false) 
        {
            switch ($lottery['category']) {
                case 1:
                    $lottery['url'] = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Lottery&a=index&type=1&token=".$token.'&id='.$lottery['id'].'&wecha_id='.$this->fromWxId;
                    break;
                case 2:
                    $lottery['url'] = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Guajiang&a=index&type=1&token=".$token.'&id='.$lottery['id'].'&wecha_id='.$this->fromWxId;
                    break;
                case 3:
                    $lottery['url'] = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Coupon&a=index&type=1&token=".$token.'&id='.$lottery['id'].'&wecha_id='.$this->fromWxId;
                    break;
                case 4:
                    $lottery['url'] = "http://".C('wx_handler_server')."/index.php?g=Wap&m=Golden&a=index&type=1&token=".$token.'&id='.$lottery['id'].'&wecha_id='.$this->fromWxId;
                    break;
                default :
                    break;
           
            }
            $lottery['type'] = 'img';
            return $lottery;
        } else {
            return null;
        }
    }
    
    private function getVote($token ='', $keyword = null) 
    {
        $now = date("Y-m-d");
        
        $vote = M('Vote') -> where(array('token' => $token, 'starttime'=>array('elt', $now), 'endtime'=>array('egt', $now),
                                 'status' => 1, 'id' => $keyword['pid'] ))->find();
        if ($vote) 
        {
            $imgurl = "http://".C('wx_handler_server')."/themes/p/images/vote.jpg";
            if (!empty($vote['imgurl'])) {
                $imgurl = $vote['imgurl'];
            }
            $this->write_log("is empty".$imgurl);
            $img_data = array('title' => $vote['title'] , 
                              'description' =>$vote['content'] , 
                              'picUrl' =>$imgurl , 
                              'url' => "http://".C('wx_handler_server')."/index.php?g=Person&m=Vote&a=showVote&token=".$token.'&id='.$vote['id'].'&wecha_id='.$this->fromWxId ) ; 
            $img_data['type'] = 'img';
            return $img_data;
        } else {
            return null;
        }
    }
    
    public function getOpenApi($token='', $keyword = '') 
    {
        $rawdata = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(empty($rawdata)) 
        {
            $rawdata = file_get_contents("php://input"); 
        }
        $openapi = M('Openapi') -> where(array('token' => $token, 'status' => 1, 'keyword' => $keyword ))->find();
        if (empty($openapi)) {
            return null;
        }
        
        /*$start = stripos($rawdata, '<ToUserName>') + strlen('<ToUserName>');
        $end = stripos($rawdata, '</ToUserName>');
        $rawdata = substr($rawdata, 0, $start)."<![CDATA[lingzhtech_".$openapi["id"]."]]>".substr($rawdata, $end);
        $this->write_log($rawdata);
        */
        $timestamp = time();
        $nonce = $timestamp + rand(1,10000);    
        $token = $openapi['otoken'];
        $signature = array($token, strval($timestamp), strval($nonce));
        sort($signature);
        $signature = implode( $signature );
        $signature = sha1( $signature );
        $url = $openapi['ourl'].'?signature='.$signature.'&timestamp='.$timestamp.'&nonce='.$nonce;
        $this->write_log($url);
        
        /*$post_data = array('xml' => $rawdata);
        $options = array(   
            'http' => array(   
            'method' => 'POST',   
            'header' => 'Content-type:application/x-www-form-urlencoded',   
            'content' => $rawdata,//http_build_query($post_data),   
            'timeout' => 15 * 60 // 超时时间（单位:s）   
            )   
        );   
        $context = stream_context_create($options);   
        $apireturn = file_get_contents($url, false, $context);*/
        $apireturn = $this->makeRemoteResp('POST', $url, $rawdata);
        $this->write_log($apireturn);
        
        // parse
        $msg = (array)simplexml_load_string($apireturn, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        $msgType = strtolower($msg['MsgType']);//获取用户信息的类型
        $res_data = array();
        if ($msgType == 'text') {
            $data['type'] = 'text';
            $data['text'] = $msg['Content'];
            array_push($res_data, $data);
        } else if ($msgType == 'news') {
            $articles = $msg['Articles']->xpath("item");
            foreach ($articles as $article) {
                $item = (array)$article;
                $data['type']        = 'img';
                $data['title']       = $item['Title'];
                $data['description'] = $item['Description'];
                $data['picUrl']      = $item['PicUrl'];
                $data['url']         = $item['Url'];
                array_push($res_data, $data);
            }
        }
        return $res_data;
    }
    
    
    function getRedCashResponse($token='', $keyword = array()) {
	    $cash = M('redcash_setting')->where(array('token' => $token, 'id' => $keyword['pid'] ))->find();
		if (!$cash) {
			return null;
		}
		$cur_time = time();
		if ($cur_time < strtotime($cash['start_time']) || !$cash['status']) {
	    	list($content, $type) = array('活动尚未开始，敬请期待', 'text');
	       	$this->response($content, $type);
		}
		if ($cur_time > strtotime($cash['end_time']) || $cash['status'] == '2' ||  $cash['status'] == '3'){
			list($content, $type) = array('活动已经结束，谢谢您的关注', 'text');
	       	$this->response($content, $type);
		}
		
		$cash = M('redcash_list')->where(array('token' => $token, 'openid' => $this->fromWxId, 'cashsetting_id' => $keyword['pid'], 'err_code' => 'SUCCESS'))->find();
		if (!$cash) {
			require_once(COMMON_PATH.'/RedCashAPI.php');
			$cash = new RedCashAPI(array('token' => $this->token, 'redcash_id' => $keyword['pid'], 'openid' => $this->fromWxId));
	    	$msg = $cash->sendRedCash();
	    	
	    	$strMsg = '';
	    	if ($msg['err_code'] == 'SUCCESS') {
	    		$strMsg = '红包已发送，请领取！';
	    	}else{
	    		$strMsg = '亲， 你来晚了，红包都被抢完了！';
	    	}
	    	list($content, $type) = array($strMsg, 'text');
	       	$this->response($content, $type);
		}else{
			list($content, $type) = array('亲，你已经领取红包了，不能太贪心哦！', 'text');
	       	$this->response($content, $type);
		}
    }
    

    /**
     * signature对请求signature进行校验，若确认此次GET请求来自微信服务器 
     */
    private function checkSignature() 
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = $_GET['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr,SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        if ( $tmpStr == $signature )
        {
                return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获得用户发过来的消息（消息内容和消息类型）
     */
    private function processMsg() {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(empty($postStr)) {
            $postStr = file_get_contents("php://input"); 
        }
        if (!empty($postStr)) {
            $this->msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->msgType = strtolower($this->msg['MsgType']);//获取用户信息的类型
            if ($this->msgType === 'event') {
                $this->event = strtolower($this->msg['Event']);//获取key值
                $this->eventKey = $this->msg['EventKey'];//获取key值
            }
            $this->oriWxId = $this->msg['ToUserName'];
            $this->fromWxId = $this->msg['FromUserName'];  //open_id
            $wx_user = M('wxuser')->where(array('wxid'=>$this->oriWxId, 'token'=>$_GET['token'], 'status'=>1))->find();
            if ($wx_user) 
            {
           		 if (in_array($wx_user['token'] , array('54447845z7b', '54570d1ezc9')) && $this->msg['Event'] != 'unsubscribe') {
	            	$this->push_info($this->msg, $wx_user, $_GET['token']);
            	}
            	
                if (empty($wx_user['code'])) 
                {
                    $wx_user['code'] = $this->generateCode();
                    M('wxuser')->where(array('id'=>$wx_user['id']))->save(array('code'=>$wx_user['code'])) ;
                }
                $this->token            = $wx_user['token'];
                $this->merchant_code    = $wx_user['code'];
                $this->userId           = $wx_user['uid'];
                //0是订阅号，1是服务号
                $this->wechat_type      = $wx_user['type'];

                //0是未认证，1是已认证
                $this->is_authed        = $wx_user['is_authed'];

                if ($this->wechat_type == 1 && $this->is_authed == 1) 
                {
                    $this->cs_setting= M('cs_setting')->where(array('token'=>$wx_user['token']))->find();
                }

                $data = array();
                $data['from_user']          = $this->fromWxId;
                $data['to_public_id']       = $this->oriWxId;
                $data['to_public_token']    = $this->token;
                $data['receive_time']       = $this->msg['CreateTime'];
                $data['msg_type']           = $this->msg['MsgType'];
                if ($data['msg_type'] == 'text') 
                {
                    $data['msg_content']        = $this->msg['Content'];
                }
                else if ($data['msg_type'] == 'voice') 
                {
                    $data['msg_content']        = $this->msg['Recognition'];
                }
                else if ($data['msg_type'] == 'location') 
                {
                    $data['location_x'] = $this->msg['Location_X'];
                    $data['location_y'] = $this->msg['Location_Y'];
                    $data['scale'] = $this->msg['Scale'];
                    $data['label'] = $this->msg['Label'];
                }
                else if ($data['msg_type'] == 'event') 
                {
                    $data['event'] = $this->msg['Event'];

                    switch ($data['event']) {
                        case 'CLICK':
                        case 'scan':
                        case 'subscribe':
                            $data['event_key'] = $this->msg['EventKey'];
                            break;
                        case 'LOCATION':
                            $data['location_x'] = $this->msg['Latitude'];
                            $data['location_y'] = $this->msg['Longitude'];
                            $data['scale'] = $this->msg['Precision'];
                            $data['label'] = $this->msg['Label'];
                            break;
                        default:
                            break;
                    }

                }
                $data['msg_id']             = $this->msg['MsgId'];
                $data['create_time']        = time();
                M('wx_access')->add($data);
                
            }
            else
            {
                Log::record('Wrong oriWxId:'.$this->oriWxId.' token:'.$_GET['token'],Log::INFO);
            } 
        }
    }
    
    
	//需要推送的信息
    function push_info($msg, $wx_user, $token){
    	$push = M('push_info');
    	$info = $push->where(array('openid'=>$msg['FromUserName'], 'wechat_id'=>$msg['ToUserName']))->find();
    	//获取appid和appsecret
    	if (empty($info) || ($info && floor((time() - $info['update_time'])/86400) >= 1)) {
	    	require_once(COMMON_PATH.'/WeixinAPI.php');
			$weixin = new WeixinAPI($wx_user['appid'], $wx_user['appsecret']);
			$user = $weixin->getUserInfo($msg['FromUserName']);
			
	    	if (empty($info)) {
	    		$data = array(
	    			'openid'=>$msg['FromUserName'], 
	    			'wechat_id'=>$msg['ToUserName'],
	    			'content'=>json_encode($user),
	    			'create_time'=>time(), 
	    			'update_time'=>time()
	    		);
	    		$push->add($data);
	    		$this->push_user_info(json_encode($this->signature($user)), $token);
	    	}elseif (floor((time() - $info['update_time'])/86400) >= 1) {
	    		$data = array(
	    			'content'=>json_encode($user),
	    			'update_time'=>time()
	    		);
	    		$this->push_user_info(json_encode($this->signature($user)), $token);
	    		$push->where(array('openid'=>$msg['FromUserName'], 'wechat_id'=>$msg['ToUserName']))->save($data);
	    	}
    	}
    }
    //获取url返回信息
    function get_content_info($url){
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//运行curl，结果以jason形式返回
        $res = curl_exec($ch);
		curl_close($ch);
		//取出data
		$data = json_decode($res,true);
		return $data;
	}
	
	function push_user_info($json, $token){
		if ($token == '54570d1ezc9') {
			$url = 'http://xiaoyang.xuetang.cn/wx/index.php/api/inputBeidaUser';
		}elseif ($token == '54447845z7b') {
			$url = 'http://xiaoyang.xuetang.cn/wx/index.php/api/inputWxUser';
		}
    	$this->fetch($url, $json, 'post',array(
            'content-type:application/json;charset=utf-8'
        ));
    }
	
	protected function signature($data){
		$token = 'a714b92fb6ec7d289ee130702a343baf';
        $nonce = time().mt_rand(1,9999999);
        asort($data,SORT_STRING);
        $signature = sha1(implode($data).$token.$nonce);
        $data['nonce'] = $nonce;
        $data['signature'] = $signature;
        return $data;
    }
    
    public function fetch($url,$params=array(),$method="get",$header=array()){
        $ch = curl_init();
        if(is_array($params)){
            $query = http_build_query($params);
        }else{
            $query = $params;
        }
        if($method == 'get'){
            if(strpos($url,'?') !== false){
                $url .= "&".$query;
            }else{
                $url .= "?".$query;
            }
        }else{
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$query);
        }
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    

    private function generateCode()
    {
        mt_srand((double) microtime() * 1000000);
        return str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_RIGHT);
    }

    /**
     * 转发多客服
     * @return string
     */
    public function makeTransfer() {
        $createtime = time();
        $textTpl = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[transfer_customer_service]]></MsgType>
            </xml>";
        return $textTpl;
    }
    
    /**
     * 回复文本消息
     * @param string $text
     * @return string
     */
    public function makeText($text='') {
        $createtime = time();
        $textTpl = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        return sprintf($textTpl,$text);
    }

    /**
     * 回复图文消息
     * @param array $newsData
     * @return string
     */
     public function makeNews($newsData=array()) {
        $createtime = time();
        $newTplHeader = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount><Articles>";
        $newTplItem = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
        $newTplFoot = "</Articles>
            </xml>";
        $content = '';
        $itemsCount = count($newsData);
        $itemsCount = $itemsCount < 10 ? $itemsCount : 10;//微信公众平台图文回复的消息一次最多10条
        if ($itemsCount) {
            for ($i = 0; $i < $itemsCount; $i++) 
            {
                $url = empty($newsData[$i]['url'])
                           ?  U('Wap/Index/content@'.C('wx_handler_server'),array('id'=> $newsData[$i]['id'], 'token' => $newsData[$i]['token']))
                           :  $newsData[$i]['url'];
                $content .= sprintf($newTplItem,$newsData[$i]['title'],$newsData[$i]['description'],$newsData[$i]['picUrl'],$url);//微信的信息数据

            }
        }
        $header = sprintf($newTplHeader,$itemsCount);
        $footer = $newTplFoot;
        return $header . $content . $footer;
    }

    /**
    * 回复音乐消息
    * @param array $newsData
    * @return string
    */
    private function makeMusic($newsData=array()) {
        $createtime = time();
        $textTpl = "<xml>
            <ToUserName><![CDATA[{$this->msg['FromUserName']}]]></ToUserName>
            <FromUserName><![CDATA[{$this->msg['ToUserName']}]]></FromUserName>
            <CreateTime>{$createtime}</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
            <Title><![CDATA[{$newsData['title']}]]></Title>
            <Description><![CDATA[{$newsData['description']}]]></Description>
            <MusicUrl><![CDATA[{$newsData['MusicUrl']}]]></MusicUrl>
            <HQMusicUrl><![CDATA[{$newsData['HQMusicUrl']}]]></HQMusicUrl>
            </Music>
            </xml>";
        return sprintf($textTpl,'');
    }

    /**
     *param method   POST, or GET
     */
    private function makeRemoteResp($method, $url, $data){
         $ch = curl_init(); 
        $header = array();
       // $header[] = "Host:fanyi.youdao.com";
        //$header[] = "Host:www.xiaohuangji.com";
        /*$header[] = "Referer:http://wx.lingzhtech.com/";
        $header[] = "Accept-Charset: utf-8"; 
        $header[]= 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, *'. '/* ';
        $header[]= 'Accept-Language: zh-cn ';
        $header[]= 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1';
        $header[]= 'Connection: Keep-Alive ';*/
        $header[]= 'Content-Type: text/plain';
//        $header[]= 'Cookie: JSESSIONID=2D96E7F39FBAB9B28314607D0328D35F';
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

        $output = curl_exec($ch);
        //$resp = json_decode($output,true);
        if(curl_errno($ch)){//出错则显示错误信息
           $this->write_log( curl_error($ch));
        }

        curl_close($ch);
        //$_str = ob_get_contents();
        //$_str = str_replace("script", "", $_str);
        //ob_end_clean();
        //return $_str;
        return $output;

    /*if (curl_errno($ch)) {  
        return '';
    }else{
        // var_dump($tmpInfo);  
        return $tmpInfo;
    }*/
    }
  
    /**
     * 返回给用户信息
     *
     */
    private function reply($data) {
       if ($this->debug) {
            //$this->write_log($data);
       }
       echo $data;
    }

    /**
     *
     * @param type $log
     */
    private function write_log($log) {
        //这里是你记录调试信息的地方请自行完善以便中间调试
        Log::record($log."\r\n", Log::DEBUG);       
        //Log::save();
    }
    
    private $data = array();
    public function response($content, $type = 'text', $flag = 0){
        $this -> data = array('ToUserName' => $this->msg['FromUserName'], 'FromUserName' => $this->msg['ToUserName'], 'CreateTime' => time(), 'MsgType' => $type,);
        $this -> $type($content);
        $this -> data['FuncFlag'] = $flag;
        $xml = new SimpleXMLElement('<xml></xml>');
        $this -> data2xml($xml, $this -> data);
        exit($xml -> asXML());
    }
    
	private function text($content){
        $this -> data['Content'] = $content;
    }
    private function music($music){
        list($music['Title'], $music['Description'], $music['MusicUrl'], $music['HQMusicUrl']) = $music;
        $this -> data['Music'] = $music;
    }
	private function news($news){
        $articles = array();
        foreach ($news as $key => $value){
            list($articles[$key]['Title'], $articles[$key]['Description'], $articles[$key]['PicUrl'], $articles[$key]['Url']) = $value;
            if($key >= 9){
                break;
            }
        }
        $this -> data['ArticleCount'] = count($articles);
        $this -> data['Articles'] = $articles;
    }
	private function transfer_customer_service($content){
        $this -> data['Content'] = '';
    }
    
    private function data2xml($xml, $data, $item = 'item'){
        foreach ($data as $key => $value){
            is_numeric($key) && $key = $item;
            if(is_array($value) || is_object($value)){
                $child = $xml -> addChild($key);
                $this -> data2xml($child, $value, $item);
            }else{
                if(is_numeric($value)){
                    $child = $xml -> addChild($key, $value);
                }else{
                    $child = $xml -> addChild($key);
                    $node = dom_import_simplexml($child);
                    $node -> appendChild($node -> ownerDocument -> createCDATASection($value));
                }
            }
        }
    }
}

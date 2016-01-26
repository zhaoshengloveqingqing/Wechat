<?php
class LotteryAction extends BaseAction
{
    
    
    public function index()
    {
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(!strpos($agent,"MicroMessenger")) 
        {
            //echo '此功能只能在微信浏览器中使用';
            //exit;
        }
        $token        = $this->_get('token');
        $wecha_id    = $this->_get('wecha_id');
        $lottery_id         = $this->_get('id');
        
        $pageData = array();
        
        //用户基本信息
        $user = array();
        $user['token']      = $token;
        $user['wecha_id']   = $wecha_id;
        $this->assign('UserInfo', $user);

        $pageData['UserInfo'] = $user;
        
        $Lottery     = M('Lottery')->where(array('id'=>$lottery_id,'token'=>$token,'type'=>1,'status'=>1))->find();
        if ($Lottery != null) 
        {
            //抽奖活动信息    
            $activity = array();    
            $activity['lid']        = $lottery_id;
            
            $now = time();
            // 0 
            if ($Lottery['enddate'] < $now ) 
            {
                //活动结束
                $activity['status'] = 4;
            } 
            else if ($Lottery['statdate'] > $now) 
            {
                //活动还没开始
                $activity['status'] = 0;
            } 
            else if  ($Lottery['statdate'] < $now && $Lottery['enddate'] > $now ) 
            {
                //抽奖进行中
                $activity['status'] = 1;
            }
            $activity['canrqnums']    = $Lottery['canrqnums'];    
            $activity['fist']         = $Lottery['fist'];
            $activity['second']         = $Lottery['second'];
            $activity['third']         = $Lottery['third'];
            $activity['four']         = $Lottery['four'];
            $activity['five']         = $Lottery['five'];
            $activity['six']         = $Lottery['six'];
            $activity['fistnums']     = $Lottery['fistnums'];
            $activity['secondnums'] = $Lottery['secondnums'];
            $activity['thirdnums']     = $Lottery['thirdnums'];    
            $activity['fournums']     = $Lottery['fournums'];
            $activity['fivenums']     = $Lottery['fivenums'];
            $activity['sixnums']     = $Lottery['sixnums'];
            $activity['info']        = $Lottery['info'];
            $activity['txt']        = $Lottery['txt'];
            $activity['sttxt']        = $Lottery['sttxt'];        
            $activity['title']        = $Lottery['title'];
            $activity['statdate']    = date('Y-m-d', $Lottery['statdate']);
            $activity['enddate']    = date('Y-m-d', $Lottery['enddate']);        
            $activity['endinfo']     = $Lottery['endinfo'];
            $this->assign('LotteryInfo', $activity);
            $pageData['LotteryInfo'] = $activity;
        }
        
        
        
        //小票流水號信息
        $receipts = M('Lottery_receipt')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'lottery_id'=>$lottery_id))->select();        
        if($receipts != Null)
        {
            foreach ($receipts as $key => $val) 
            {
                $receipts[$key]['lottery_count'] = floor($receipts[$key]['qualified_amount']/$Lottery['quanlifiedAmount']);
            }
            $this->assign('LotteryReceipts', $receipts);            
            $pageData['LotteryReceipts'] = $receipts;
        }
        
        //中奖信息
        $records = M('Lottery_record')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$lottery_id))->select();        
        if($records != Null)
        {
            $this->assign('LotteryRecords', $records);
            $pageData['LotteryRecords'] = $records;
        }
        $this->assign('pageData', json_encode($pageData));
        //var_dump(json_encode($pageData));exit();
        
        //var_dump($data);exit();
        $this->display();
    }
    
    protected function get_random_prize()
    {
        $prize_arr = array( 
            '0' => array('id'=>1,'v'=>1), 
            '1' => array('id'=>2,'v'=>2), 
            '2' => array('id'=>3,'v'=>3),
            '3' => array('id'=>4,'v'=>5),
            '4' => array('id'=>5,'v'=>10),
            '5' => array('id'=>6,'v'=>15),
            '6' => array('id'=>7,'v'=>65)
        );
        
        foreach ($prize_arr as $key => $val) 
        { 
            $arr[$val['id']] = $val['v']; 
        } 
        
        $prize_cls = 0;
        
        $proSum = array_sum($arr); 
        foreach ($arr as $key => $proCur) 
        { 
            $randNum = mt_rand(1, $proSum); 
            if ($randNum <= $proCur) { 
                $prize_cls = $key; 
                break; 
            } else { 
                $proSum -= $proCur; 
            } 
        } 
        unset ($arr);
        return $prize_cls;
    }
    
    public function getajax()
    {    
        
        $token		=    $this->_post('token');
        $wecha_id   =    $this->_post('oneid');
        $lottery_id =    $this->_post('id');
        $receipt_id =    $this->_post('rid');
        
        /*
         * Ret = {
         *  ret_code: 404, 1, 2
         *  ret_msg: 
         *  ret_data: 
         *  }
         */
        $result = array();

        $lottery = M('Lottery')->where(array('id'=>$lottery_id,'token'=>$token,'type'=>1,'status'=>1))->find();

        //抽奖活动是否存在
        if ($lottery == null || $lottery['statdate'] > time()) 
        {
            $result['ret_code'] = 404; // 不存在
            $result['ret_msg'] = "活动尚未开始或者不存在";
        }
        
        //非法流水号
        $receipt     = M('Lottery_receipt')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'id'=>$receipt_id))->find();
        if ($receipt == null) 
        {
            $result['ret_code'] = 404; // 不存在
            $result['ret_msg'] = "流水号不存在";
            echo json_encode($result);
            exit;    
        }
        
        $records = M('Lottery_record')->where(array('token'=>$token,'wecha_id'=>$wecha_id,'receipt_id'=>$receipt_id))->select();
        $lottery_count = floor($receipt['qualified_amount']/$lottery['quanlifiedAmount']);
        if ($records != null 
                && count($records) >= $lottery_count) 
        {
            $result['ret_code'] = 405; // 抽奖次数购
            $result['ret_msg'] = "您已经抽了"+$lottery_count + "次奖,不能再抽了,下次再来吧!";
            echo json_encode($result);
            exit;    
        }
        
        //-------------------------------     
        //随机抽奖[如果预计活动的人数为1为各个奖项100%中奖]
        //-------------------------------     
        if ($lottery['allpeople'] == 1) 
        {
     		$prize_cls = 1;   
        }else{
            $prize_cls = $this->get_random_prize($lottery_id);
        }
        
        $prize_mapping = array(
            '1' =>  array ('prize_cls_cn' => '一等奖', 'prize_name' => 'fist', prize_count => 'fistnums'),
            '2' =>  array ('prize_cls_cn' => '二等奖', 'prize_name' => 'second', prize_count =>'secondnums'),
            '3' =>  array ('prize_cls_cn' => '三等奖', 'prize_name' => 'third', prize_count =>'thirdnums'),
            '4' =>  array ('prize_cls_cn' => '四等奖', 'prize_name' => 'four', prize_count =>'fournums'),
            '5' =>  array ('prize_cls_cn' => '五等奖', 'prize_name' => 'five', prize_count =>'fivenums'),
            '6' =>  array ('prize_cls_cn' => '六等奖', 'prize_name' => 'six', prize_count =>'sixnums'),
        
        );
        $records = M('Lottery_record')->where(array('lid' =>$lottery_id, 'prize_class' => $prize_cls, 'receipt_id' => $receipt_id ))->select();
        if(empty($lottery[$prize_mapping[$prize_cls]['prize_name']]) 
        	|| count($records) >= $lottery[$prize_mapping[$prize_cls]['prize_count']]) 
        {
            //判断是否设置该奖项
            //或者是否已经抽出足够数
            $prize_cls = 0;
        }
        
        $data = array();
        $data['receipt_id']   = $receipt_id;
        $data['wecha_id']     = $wecha_id;
        $data['lid'] = $lottery_id;
        $data['time']= time();     
        $data['token']= $token;     
        $data['prize_class'] = $prize_cls;
        $data['prize'] = $prize_mapping[$prize_cls]['prize_cls_cn'];
                    
        if ($prize_cls >= 1 && $prize_cls <= 6) {  
            $data['sn']= uniqid();    
        }
        $record = M('Lottery_record');
        $record ->add($data);
            
        $result['ret_code'] = 200;
        $result['ret_msg'] = "抽奖成功";
        $result['ret_data'] = $data;
        echo json_encode($result);
        exit;    
    }
    
    
    //填写流水号信息
    public function run(){
    	if($_POST['action'] != 'run' )
        {
    		exit;
    	}
        if(C('LOG_RECORD')) Log::save();
        
    	$lottery_id		= $this->_post('lid');
        $wechaid        = $this->_post('wechaId');
        $token          = $this->_post('token');
        
        $serial_num     = $this->_post('serialNo');
        $qualified_amount = 0;
            
        $data = array();
        $data['phone']      	= $this->_post('tel');
        $data['wecha_id']     	= $wechaid;
        $data['lottery_id'] 	= $lottery_id;
        $data['create_time']	= time();     
        $data['token']			= $token;     
        
        
        /*
         * Ret = {
         *  ret_code: 404, 1, 2
         *  ret_msg: 
         *  ret_data: 
         *  }
         */
        $result = array();
        
        if ($serial_num != null) 
        {
        	//get quanlified amount from maoye webservice
        	$qualified_amount = 700;
        	$data['serial_num'] 	= $serial_num;
        	$data['qualified_amount']= $qualified_amount;
        } 
        else 
        {
        	$result['ret_code'] = 500;
        	$result['ret_msg'] = '非法的流水号';
        	echo json_encode($ret) ;
        	if(C('LOG_RECORD')) Log::save();
            exit;
        }

        $lottery = M('Lottery')->where(array('id'=>$lottery_id,'token'=>$token,'type'=>1,'status'=>1))->find();
        if ($lottery != null) 
        {
        	
        	$history_recp = M('Lottery_receipt')->where(array('serial_num'=> $serial_num))->select();
        	if ($history_recp == null) 
            {
        		$data['lottery_times']= floor($qualified_amount/$lottery['quanlifiedAmount']);   
           		$receipt = M('Lottery_receipt');
           		$receipt ->add($data);
           		$result['ret_code'] = 200;
        		$result['ret_msg'] = '';
        		$result['ret_data'] = $data;
        	} 
            else 
            {
        		$result['ret_code'] = 500;
        		$result['ret_msg'] = '该流水号已参加过活动';
        	}
           	
        } 
        else 
        {
        	$result['ret_code'] = 500;
        	$result['ret_msg'] = '抽奖活动不存在';
        }
        echo json_encode($result) ;
        if(C('LOG_RECORD')) Log::save();
        exit;
    }
}
    
?>
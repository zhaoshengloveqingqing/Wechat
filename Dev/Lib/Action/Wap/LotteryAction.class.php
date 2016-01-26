<?php
class LotteryAction extends BaseAction
{

	public function index()
	{
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		/*if(!strpos($agent,"MicroMessenger")) 
		{
			echo '此功能只能在微信浏览器中使用';
			exit;
		}*/
		$token		= $this->_get('token');
		$wecha_id	= $this->_get('wecha_id');
		$id 		= $this->_get('id');
		$record_db  = M('Lottery_record');
		$where 		= array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record 	= $record_db->where($where)->find();		
		if ($record == null)
		{
			$record_db->add($where);
			$record = $record_db->where($where)->find();
		}

		$Lottery 	= M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->find();
		
		//1.活动过期,显示结束
		  
		//4.显示奖项,说明,时间
		if ($Lottery['enddate'] < time()) 
		{
			 $data['end'] = 1;
			 $data['endinfo'] = $Lottery['endinfo'];
			 $this->assign('Dazpan',$data);
			 $this->display();
			 exit();
		}

		// 1. 中过奖金	
		if ($record['islottery'] == 1) 
		{				
			$data['end'] = 5;
			$data['sn']	 	 = $record['sn'];
			$data['uname']	 = $record['wecha_name'];
			$data['prize']	 = $record['prize'];
			$data['tel'] 	 = $record['phone'];	
		}
		$data['status']     = 1;
		$data['On'] 		= 1;
		$data['token'] 		= $token;
		$data['wecha_id']	= $record['wecha_id'];		
		$data['lid']		= $record['lid'];
		$data['rid']		= $record['id'];
		$data['usenums'] 	= $record['usenums'];
		$data['canrqnums']	= $Lottery['canrqnums'];	
		$data['fist'] 		= $Lottery['fist'];
		$data['second'] 	= $Lottery['second'];
		$data['third'] 		= $Lottery['third'];
		$data['four'] 		= $Lottery['four'];
		$data['five'] 		= $Lottery['five'];
		$data['six'] 		= $Lottery['six'];
		$data['fistnums'] 	= $Lottery['fistnums'];
		$data['secondnums'] = $Lottery['secondnums'];
		$data['thirdnums'] 	= $Lottery['thirdnums'];	
		$data['fournums'] 	= $Lottery['fournums'];
		$data['fivenums'] 	= $Lottery['fivenums'];
		$data['sixnums'] 	= $Lottery['sixnums'];
		$data['info']		= $Lottery['info'];
		$data['txt']		= $Lottery['txt'];
		$data['sttxt']		= $Lottery['sttxt'];		
		$data['title']		= $Lottery['title'];
		$data['statdate']	= $Lottery['statdate'];
		$data['enddate']	= $Lottery['enddate'];	
		$data['isDisplayPrize']     = $Lottery['displayjpnums'];	
		$data['aginfo']     = $Lottery['aginfo'];

		/***
		//会员等级
        $class_con = array('token'=>$token,'status'=>1);
        $class_info = M('member_group')->where($class_con)->field('groupid,title')->select();
        if(empty($class_info)){
            $class_info = C('MEMBER_GROUP');
        }
        $this->assign('class_info',$class_info);
        $this->assign('group',$group);
        **/

        $can = $this->hasPermission($Lottery,$wecha_id);
        
        if(!$can){
            $data['status'] = 0;//0没有权限
        }


		$this->assign('Dazpan',$data);
		//var_dump($data);exit();
		$this->display();
	}
	
    /**
    *验证是否有权限抽奖
    **/
    protected function hasPermission($lottery,$wecha_id){
    	if($lottery['all_funs']){
    		return true;
    	}
        $group = unserialize($lottery['group']);

        $card = M('member_card_create')->where(array('token'=>$lottery['token'],'wecha_id'=>$wecha_id,'status'=>1))->find();
        $groupid = $card ? $card['groupid'] : -1;
        if(empty($group) && $groupid == -1){//group里没有限制，而且此wecha_id不是会员
            return false;
        }
        if(in_array($groupid, $group['groupid'])){
            return true;
        }
        return false;
    }
	
	private function getPrizeInfoList() {
            return array(
                1 => array('name'=>'一等奖', 'upperLimitColName' => 'fistnums', 'hitCountColName' => 'fistlucknums'),
                2 => array('name'=>'二等奖', 'upperLimitColName' => 'secondnums', 'hitCountColName' => 'secondlucknums'),
                3 => array('name'=>'三等奖', 'upperLimitColName' => 'thirdnums', 'hitCountColName' => 'thirdlucknums'),
                4 => array('name'=>'四等奖', 'upperLimitColName' => 'fournums', 'hitCountColName' => 'fourlucknums'),
                5 => array('name'=>'五等奖', 'upperLimitColName' => 'fivenums', 'hitCountColName' => 'fivelucknums'),
                6 => array('name'=>'六等奖', 'upperLimitColName' => 'sixnums', 'hitCountColName' => 'sixlucknums'),
            );
        }
	protected function get_prize($Lottery)
	{   
            $prizeInfoList = $this->getPrizeInfoList();
            // 获得当前剩余的奖品数
            $remainingPrizeList = array();
            $remainingPrizeCount = 0;
            foreach($prizeInfoList as $level => $prizeInfo) {
                $remainingPrizeList[$level] = intval($Lottery[$prizeInfo['upperLimitColName']]) - intval($Lottery[$prizeInfo['hitCountColName']]);
                if($remainingPrizeList[$level] < 0) {
                    $remainingPrizeList[$level] = 0;
                }
                $remainingPrizeCount += $remainingPrizeList[$level];
            }
            
            // 获得当前剩余的抽奖次数
            $remainingJoinNum = ($Lottery['canrqnums']  * $Lottery['allpeople']) - $Lottery['joinnum'];
            if($remainingJoinNum < 0) {
                $remainingJoinNum = 0;
            }
            
            // 分配奖品
            if($remainingPrizeCount <= 0){
                return 7;
            }
            
            // 如果剩余奖品树比预计抽奖次数要多，随机分配一个奖品
            $randUpperBound = 0;
            if($remainingPrizeCount >= $remainingJoinNum) {
                $randUpperBound = $remainingPrizeCount;
               
            }else {
                // 如果剩余奖品比预计抽奖人数要少， 这按概率抽取
                $randUpperBound = $remainingJoinNum;
            }
            
            $r = mt_rand(0,  999999999);
            $r =intval( $r % $randUpperBound );
            if($r < 0) {
                $r = (-1) * $r;
            }
            
            $randomData = $r + 1;
            
            $bar = 0;
            $level= 7;
            for($level=1; $level<=6; $level++) {
                $bar += $remainingPrizeList[$level];
                if($randomData <= $bar) {
                    break;
                }
            }
            
            if($level >= 1 && $level <= 6) {
                // 中奖啦
                M('Lottery')->where(array('id'=>$Lottery['id']))->setInc($prizeInfoList[$level]['hitCountColName']);
                // 为尽量减少奖项超支，再次读取Lottery以确认该奖项的已中奖次数不超过其上限
                $Lottery = M('Lottery')->find($Lottery['id']);
               
                if($Lottery[$prizeInfoList[$level]['hitCountColName']] > $Lottery[$prizeInfoList[$level]['upperLimitColName']]) {
                    
                    $level = 7;
                }
            }
            
            return $level;
	}

	public function getajax(){	
		
		$token 		=	$this->_post('token');
		$wecha_id	=	$this->_post('oneid');
		$id 		=	$this->_post('id');
		$rid 		= 	$this->_post('rid');	
		$record_db 	=	M('Lottery_record');
		$where 		= 	array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record 	=	$record_db->where($where)->find();	

		// 1. 中过奖金	
		if ($record['islottery'] == 1) {				
			//$norun = 1;
			$sn	 	 = $record['sn'];
			$uname	 = $record['wecha_name'];
			$prize	 = $record['prize'];
			$tel 	 = $record['phone'];
			$msg = "尊敬的:<font color='red'>$uname</font>,您已经中过<font color='red'> $prize</font> 了,您的领奖序列号:<font color='red'> $sn </font>请您牢记及尽快与我们联系.";
			echo '{"norun":1,"msg":"'.$msg.'"}';
			exit;		
		}
		//检查是否有权限抽奖
		$Lottery 	= M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>1,'status'=>1))->find();
        $can = $this->hasPermission($Lottery,$wecha_id);
        if(!$can){
        	$msg = "抱歉您没有在可以抽奖的人群中！";
    		echo '{"norun":0,"msg":"'.$msg.'"}';
			exit;	
        }
		// 2. 抽奖次数是否达到			
		if ($record['usenums'] >= $Lottery['canrqnums'] ) {
			$norun 	 =  2;
			$usenums =  $record['usenums'];	
			$canrqnums=	$Lottery['canrqnums'];
			echo '{ 				
				"norun":'.$norun.',
				"usenums":"'.$usenums.'",
				"canrqnums":"'.$canrqnums.'",
				"id":"'.$id.'",
				"token":"'.$token.'",
				"type":"'.$type.'",
				"status":"'.$status.'"
			}';
			exit;	
		}else{ //每次请求先增加 使用次数 usenums 
			$prizetype	=	$this->get_prize($Lottery);	
                        
                        M('Lottery')->where(array('id' => $Lottery['id']))->setInc('joinnum');
                        $record_db->where(array('id'=>$record['id']))->setInc('usenums');
                        $usenums = $record['usenums'] + 1;
			if ($prizetype >= 1 && $prizetype <= 6) {				 
				$sn 	= uniqid();				
				echo '{"success":1,"sn":"'.$sn.'","prizetype":"'.$prizetype.'","usenums":"'.$usenums.'"}';
			}else{
				echo '{"success":0,"prizetype":"","usenums":"'.$usenums.'"}';
			}			
			exit;
		} 
	}
	
	
	//中奖后填写信息
	public function add(){
		 if($_POST['action'] ==  'add' ){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			$data['sn']			= $this->_post('sncode');
			$data['phone'] 		= $this->_post('tel');
			$data['prize']		= $this->_post('prizetype');
			$data['wecha_name'] = $this->_post('wxname');
			$data['time']		= time(); 
			$data['islottery']	= 1;			

			$rollback = M('Lottery_record')->where(array('lid'=> $lid,
				'wecha_id'=>$wechaid))->save($data);
			
			echo'{"success":1,"msg":"恭喜！尊敬的 '.$data['wecha_name'].',请您保持手机通畅！你的领奖序号:'.$data['sn'].'"}';
			exit;
		}
	}
}
	
?>

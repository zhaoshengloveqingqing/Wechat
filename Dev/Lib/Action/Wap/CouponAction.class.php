<?php
class CouponAction extends BaseAction{
	public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"MicroMessenger")) {
			echo '此功能只能在微信浏览器中使用';exit;
		}
		$token		= $this->_get('token');
		$wecha_id	= $this->_get('wecha_id');
		$id 		= $this->_get('id');
		//0.判断是否有该用户记录,没有则记录		
		$redata		= M('Lottery_record');
		$where 		= array('token'=>$token,'wecha_id'=>$wecha_id,'lid'=>$id);
		$record 	= $redata->where($where)->find();		
		if($record == null)
		{
			$redata->add($where);
			$record = $redata->where($where)->find();
		}

		$data['end'] = 0; //初始未结束

		$Lottery 	= M('Lottery')->where(array('id'=>$id,'token'=>$token,'type'=>3,'status'=>1))->find();
		//var_dump($Lottery);
		//0. 判断优惠券是否领完了 
		if($Lottery['fistlucknums']  == $Lottery['fistnums'] 
		     && $Lottery['secondlucknums'] == $Lottery['secondnums']
		     && $Lottery['thirdlucknums']  == $Lottery['thirdnums'])
		{		  	
		   $data['end'] = 1;
		   $data['msg'] = "您来晚了,优惠券已经被领完了.";
			 
		}
		else
		{
			if ($Lottery['enddate'] < time()) 
			{ 
				//过期
				$data['end'] = 2;
				$data['endinfo'] = $Lottery['endinfo'];				 
				$data['endimg']  = empty($Lottery['endpicurl']) ? 1 : $Lottery['endpicurl']; 
			}
			else
			{
				//2.活动还没过期,接着是否领过了
				if ($record['islottery'] == 1) 
				{
					 $data['end'] 		= 3;
					 $data['msg'] 		= "您已经领取过优惠券了";
					 $data['wxname']	= $record['wecha_name'];	
					 $data['sncode']  	= $record['sn'];	
					 $data['myprize'] 	= $record['prize'];	 
					 $data['isused']	= $record['sendstutas'];
				}
				else
				{ 
					//是否已经够次数
					if ($record['usenums'] >= $Lottery['canrqnums'] ) 
					{
						$data['prizetype']	= 4; //啥都没了
					  	$data['zjl']	  	= 0;
						$data['usenums']  	= $record['usenums'];
					  	$data['winprize'] 	= "抽奖次数已经达到";
						//exit;	
					}
					else
					{
						//3.没有领过,次数没达到,开始随机发放优惠券
						M('Lottery_record')->where(array('id'=>$record['id']))->setInc('usenums');
						$record = M('Lottery_record')->where(array('id'=>$record['id']))->find();
						//排除没有设置的优惠券
						//奖品数 != 已经领取该奖品数 => 还有奖品
						if (!empty($Lottery['third']) && $Lottery['thirdlucknums'] < $Lottery['thirdnums']) 
						{
							if(!empty($Lottery['third'])){
								$data['prizetype'] 		= 3;
								$data['winprize']	   	= $Lottery['third'];
								$data['zjl']	   		= 1;
								$data['sncode'] 		= uniqid();
								M('Lottery')->where(array('id'=>$id))->setInc('thirdlucknums');
							}else{
								$data['prizetype']= 4; //啥都没了
								$data['zjl']	  = 0;
								$data['winprize'] = "这次真不走运.";
							}
							
						} else if (!empty($Lottery['second']) && $Lottery['secondlucknums'] < $Lottery['secondnums']) {
							if(!empty($Lottery['second'])){
								$data['prizetype'] = 2; 
								$data['winprize']  = $Lottery['second'];
								$data['zjl']	   = 1;
								$data['sncode']    = uniqid();
								M('Lottery')->where(array('id'=>$id))->setInc('secondlucknums');
							}else{
								$data['prizetype'] = 4; //啥都没了
								$data['zjl']	  = 0;
								$data['winprize']	   = "这次真不走运.";
							}
							
						}else if (!empty($Lottery['fist']) && $Lottery['fistlucknums']  < $Lottery['fistnums']){
							if(!empty($Lottery['fist'])){
								$data['prizetype'] 	= 1; 
								$data['sncode'] 	= uniqid();
								$data['winprize']	= $Lottery['fist'];
								$data['zjl']	   	= 1;
								M('Lottery')->where(array('id'=>$id))->setInc('fistlucknums');
							}else{
								$data['prizetype'] = 4; //啥都没了
								$data['zjl']	  = 0;
								$data['winprize']	   = "这次真不走运.";
							}
							
						}else{
							$data['prizetype'] = 4; //啥都没了
							$data['zjl']	   = 0;
							$data['winprize']	   = "真心木有了,下次来早点吧";
						}		  	
				  	 
				   }//以上没领过
				}//在没达到次数 
			}
			
		} 
	
		$data['token'] 		= $token;
		$data['wecha_id']	= $record['wecha_id'];		
		$data['lid']		= $record['lid'];
		$data['rid']		= $record['id']; 
		$data['usenums']	= $record['usenums'];

		$data['canrqnums']	= $Lottery['canrqnums'];
		$data['fist'] 		= $Lottery['fist'];
		$data['second'] 	= $Lottery['second'];
		$data['third'] 		= $Lottery['third'];
		$data['fistnums'] 	= $Lottery['fistnums'];
		$data['secondnums'] = $Lottery['secondnums'];
		$data['thirdnums'] 	= $Lottery['thirdnums'];	
		$data['info']		= $Lottery['info'];
		$data['aginfo']		= $Lottery['aginfo'];
		$data['txt']		= $Lottery['txt'];
		$data['title']		= $Lottery['title'];
		$data['statdate']	= $Lottery['statdate'];
		$data['enddate']	= $Lottery['enddate'];		
		$data['sttxt']        = $Lottery['sttxt'];
		$this->assign('Coupon',$data);
		$this->display();
	}
	
	//中奖后填写信息
	public function add(){
		 if($_POST['action'] ==  'add' ){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			$data['sn']			= $this->_post('sncode');
			$data['phone'] 		= $this->_post('tel');
			$data['prize']		= $this->_post('winprize');
			$data['wecha_name'] = $this->_post('wxname');
			$data['time']		= time(); 
			$data['islottery']	= 1;		

			$rollback = M('Lottery_record')->where(array('lid'=> $lid,
				'wecha_id'=>$wechaid))->save($data);
			
			echo'{"success":1,"msg":"恭喜！尊敬的<font color=red>'.$data['wecha_name'].'</font>请您保持手机通畅！你的领奖序号:<font color=red>'.$data['sn'].'</font>"}';
			exit;	
		}
	}
}
	
?>
<?php
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 * 
*/
class ShopWxpayAction extends Action{
	
    public function notify() {
    	
		include_once(LIB_PATH."ORG/Wpay/WxPayPubHelper.php");
		//存储微信的回调
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
		Log::record('wxpay notify get:'.print_r($xml, true));
	    Log::save();
		
	    //使用通用通知接口
		$notify = new Notify_pub();
		$notify->saveData($xml);
		$bak_params = $notify->getData();
		try {
			$sql = 'SELECT `pay_config` 
				FROM `'.C('DB_PREFIX').'b2c_wxtrade`
				INNER JOIN `'.C('DB_PREFIX').'b2c_payment` ON (`'.C('DB_PREFIX').'b2c_wxtrade`.`token` = `'.C('DB_PREFIX').'b2c_payment`.`token` AND `pay_code` = \'wxpay\' AND `enabled` = 1)
				WHERE `order_sn` = \''.$bak_params['out_trade_no'].'\' AND `wecha_id` = \''.$bak_params['openid'].'\'';
			$model = new Model();
			$payment  = $model->query($sql);
	        $wxpay_config = unserialize($payment[0]['pay_config']);
	        $notify->setAppkey($wxpay_config['APPKEY']);
		} catch (Exception $e) {
			Log::record('get wxpay configuration params error:'.print_r($e, true));
	    	Log::save();
	    	exit();
		}
		//验证签名，并回应微信。
		//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
		//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
		//尽可能提高通知的成功率，但微信不保证通知最终能成功。
		if($notify->checkSign() == FALSE){
			$notify->setReturnParameter("return_code","FAIL");//返回状态码
			$notify->setReturnParameter("return_msg","签名失败");//返回信息
		}else{
			$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
		}
		$returnXml = $notify->returnXml();
		Log::record('wxpay notify get:'.print_r($returnXml, true));
	    Log::save();
		//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
		if($notify->checkSign() == TRUE){
			if ($notify->data["return_code"] == "FAIL") {
				//此处应该更新一下订单状态，商户自行增删操作
				Log::record('【通信出错】:\n'.$xml.'\n');
	    		Log::save();
	    		echo 'fail';
		        exit;
			}elseif($notify->data["result_code"] == "FAIL"){
				//此处应该更新一下订单状态，商户自行增删操作
				Log::record('【通信出错】:\n'.$xml.'\n');
	    		Log::save();
	    		echo 'fail';
		        exit;
			}else{
				$this->change_order($bak_params);
				Log::record('【支付成功】:\n'.$xml.'\n');
	    		Log::save();
			}
	  }
    }
    
    function change_order($data){
    	$out_trade_no = $data['out_trade_no'];
    	$wecha_id = $data['openid'];
        $trade = M('b2c_wxtrade')->where(array('order_sn'=>$out_trade_no, 'wecha_id'=>$wecha_id))->find();
        if(empty($trade)) {
            Log::record('wxpay notify invalid notify request get: out_trade_no'.$out_trade_no);
            Log::save();
            echo 'fail';
            exit;
        }
        if($trade['n_trade_state'] == -1) {
        	 $wxtrade = array(
        	 	'n_trade_state'=>0,
        	 	'n_bank_type'=>$data['bank_type'],
        	 	'n_fee_type'=>$data['fee_type'],
        	 	'n_IsSubscribe'=>$data['is_subscribe'],
        	 	'n_NonceStr'=>$data['nonce_str'],
        	 	'n_time_end'=>$data['time_end'],
        	 	'n_transaction_id'=>$data['transaction_id'],
        	 	'n_total_fee'=>$data['total_fee']
        	 );
             M('b2c_wxtrade')->where(array('id'=>$trade['id']))->save($wxtrade);
        }
        
        $pay_type = $_GET['pay_type'];
        if ($pay_type == '1') {
        	M('hotel_order')->where(array('sn'=> $out_trade_no))->save(array('order_status'=>6));
        }else if ($pay_type == '2') {
        	$order = M('dine_order')->where(array('sn'=> $out_trade_no,  'status'=>2))->find();
        }else{
        	$ret = M('b2c_order')->where(array('sn'=>$out_trade_no))->save(array('status'=>2, 'update_time' => time()));
			if($ret === FALSE) {
			    $err = M('b2c_order')->getDbError();
			    Log::record('wxpay notify update order status fail. out_trade_no: '.$out_trade_no.' err:'.$err);
			    Log::save();
			    echo 'fail';
			    exit;
			}
			//减库存
			ShopAction::minusInventory($out_trade_no);
        }
    }
    
}
?>
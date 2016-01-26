<?php
class ShopCftPayAction extends Action
{
    public function notify() {
        Log::record('cftpay notify get:'.print_r($_GET, true));
        Log::save();
        
        // 由于二级域名redirect的原因，我们会自动在GET参数里添加shop=merchant_code,需要显示的去掉该参数
        unset($_GET['shop']);
        unset($_GET['_URL_']);
        
        $out_trade_no = $_GET['sp_billno'];
        $transaction_id = $_GET['transaction_id'];
        if(empty($out_trade_no) || empty($transaction_id)) {
            echo 'fail';
            exit;
        }
        
        // verify whether trade record existing in our db for <order_sn>
        $trade = M('b2c_cfttrade')->where(array('order_sn'=>$out_trade_no))->find();
        if(empty($trade)) {
            Log::record('cftpay notify non existing order_sn get:'.print_r($_GET, true));
            Log::save();
            echo 'fail';
            exit;
        }
        
        // trade status verification
        if($trade['n_pay_result'] != -1) {
            // invalid state: this may be caused by wx server retry logics, ignore this request
            Log::record('cftpay duplicated cft notify entries. trade status:'.$trade['n_pay_result'].' get:'.print_r($_GET, true));
            Log::save();
            $updateData = array();
            M('b2c_cfttrade')->where(array('id'=>$trade['id']))->setInc('trade_notify_times');
            // inform cft with SUCCESS stats
            echo 'success';
            exit;
        }
        
        // get cftpay config
        $token = $trade['token'];
        $payment = M('b2c_payment')->where(array('token'=>$token, 'pay_code'=>'cftpay'))->find();
        $cftpay_config = unserialize($payment['pay_config']);
        if(empty($cftpay_config)) {
            Log::record('cftpay fail: empty cft config. get:'.print_r($_GET, true));
            Log::save();
            echo 'fail';
            exit;
        }
        $partnerId = $cftpay_config['partnerId'];
        $partnerKey = $cftpay_config['partnerKey'];
        
        // url decode: get raw get parameter value for verification
        $DECODED_GET = array();
        foreach($_GET as $k => $v) {
            $DECODED_GET[$k] = urldecode($v);
        }
        
        import("@.ORG.CftWapPayHelper");
        
        // url signature verification
        $cftWapPayHelper = new CftWapPayHelper($partnerId, $partnerKey);
        foreach($DECODED_GET as $k => $v) {
            $cftWapPayHelper->setParameter($k, $v);
        }
        
        if(!$cftWapPayHelper->isTenpaySign()) {
            // sign mismatch
            Log::record('cftpay url sign mismatch: .GET:'.print_r($_GET, true));
            Log::save();
            echo 'fail';
            exit;
        }

        Log::record('cftpay url sign verification passed!', Log::INFO);
        Log::save();
        
        // get pay result
        $pay_result = $_GET['pay_result'];
        
        // ask CFT server for confirmation
        $query_order_helper = new CftWapPayHelper($partnerId, $partnerKey);
        $query_order_helper->setParameter('ver', '2.0');
        $query_order_helper->setParameter('bargainor_id', $partnerId);
        $query_order_helper->setParameter('transaction_id', $transaction_id);
        $query_order_helper->setParameter('charset', '1');
        
        $query_order_resp = $query_order_helper->query_order();
        $confirm_pay_result = (string)$query_order_resp->pay_result;
        if(strcasecmp($pay_result, $confirm_pay_result) != 0) {
            // pay result mismatch, ignore
            Log::record('cftpay pay result mismatch. notify:'.$pay_result.' confirm:'.$confirm_pay_result);
            Log::save();
            echo 'fail';
            exit;
        }
        
        Log::record('cftpay pay_result confirm passed order:'.$out_trade_no.' trade status:'.$pay_result, Log::INFO);
        Log::save();
        
        // save data to db
        unset($audit);
        $audit['n_charset'] = $_GET['charset'];
        $audit['n_bank_type'] = $_GET['bank_type'];
        $audit['n_bank_billno'] = $_GET['bank_billno'];
        $audit['n_pay_result'] = intval($_GET['pay_result']);
        $audit['n_pay_info'] = $_GET['pay_info'];
        $audit['n_purchase_alias'] = $_GET['purchase_alias'];
        $audit['n_bargainor_id'] = $_GET['bargainor_id'];
        $audit['n_transaction_id'] = $_GET['transaction_id'];
        $audit['n_total_fee'] = intval($_GET['total_fee']);
        $audit['n_fee_type'] = intval($_GET['fee_type']);
        $audit['n_time_end'] = $_GET['time_end'];
        $audit['trade_notify_timestamp'] = time();
        $audit['trade_notify_times'] = 1;
        
        
        // if pay is successful, update order status
        if($audit['n_pay_result'] == 0) {
            $updateData =  array();
            $updateData['status'] = 2; // 已付款
            $updateData['update_time'] = time();
            $ret = M('b2c_order')->where(array('sn'=>$out_trade_no))->save($updateData);
            //减库存
            ShopAction::minusInventory($out_trade_no);
            if($ret === FALSE) {
                $err = M('b2c_order')->getDbError();
                Log::record('cftpay update order status fail. err:'.$err);
                Log::save();
                echo 'fail'; // wait wx retry
                exit;
            }
        }
        Log::record('cftpay order:'.$out_trade_no.' update order status succeed.', Log::INFO);
        Log::save();
        // update trade status. THIS SHOULD BE AFTER ORDER STATUS UPDATE.
        $ret = M('b2c_cfttrade')->where(array('id'=>$trade['id']))->save($audit);
        if($ret === FALSE) {
            $err = M('b2c_cfttrade')->getDbError();
            Log::record('cftpay order:'.$out_trade_no.' update cfttrade table fail.err:'.$err);
            Log::save();
            echo 'fail';
            exit;
        }
        
        // well done
        Log::record('cftpay order:'.$out_trade_no.' succeed.', Log::INFO);
        Log::save();
        echo 'success';
        exit;
    }
    
    /*
     * callback在notify处理之后，所以没有必要再去做notify的事情，
     * 直接查下数据库，看看交易状态，redirect就行
     */
    public function callback() {
        Log::record('cftpay callback get:'.print_r($_GET, true));
        Log::save();
        
        // 由于二级域名redirect的原因，我们会自动在GET参数里添加shop=merchant_code,需要显示的去掉该参数
        unset($_GET['shop']);
         unset($_GET['_URL_']);
        
        $out_trade_no = $_GET['sp_billno'];
        $transaction_id = $_GET['transaction_id'];
        if(empty($out_trade_no) || empty($transaction_id)) {
            echo 'fail';
            exit;
        }
        
        // verify whether trade record existing in our db for <order_sn>
        $trade = M('b2c_cfttrade')->where(array('order_sn'=>$out_trade_no,'transaction_id'=>$transaction_id))->find();
        if(empty($trade)) {
            Log::record('cftpay callback non existing order_sn get:'.print_r($_GET, true));
            Log::save();
            $this->error('非法订单号',U('Wap/Shop/error'));
        }
        
        $DECODED_GET = array();
        foreach($_GET as $k => $v) {
            $DECODED_GET[$k] = urldecode($v);
        }
        
        import("@.ORG.CftWapPayHelper");
        
        // url signature verification
        $cftWapPayHelper = new CftWapPayHelper($trade['partnerId'], $trade['partnerkey']);
        foreach($DECODED_GET as $k => $v) {
            $cftWapPayHelper->setParameter($k, $v);
        }
        
        if(!$cftWapPayHelper->isTenpaySign()) {
            // sign mismatch
            Log::record('cftpay callback url sign mismatch: .GET:'.print_r($_GET, true));
            Log::save();
            echo 'fail';
            exit;
        }

        Log::record('cftpay callback url sign verification passed!', Log::INFO);
        Log::save();
        
        $audit['trade_callback_timestamp'] = time();
        $audit['trade_callback_times'] = $trade['trade_callback_times'] + 1;
        M('b2c_cfttrade')->where(array('id'=>$trade['id']))->save($audit);
        
        $host_name = C('wx_handler_server');
        // update trade table for tracking
        if($trade['n_pay_result'] == 0) {
            
            $redirect = WapAction::generatePayResultUrl('Shop/my', $trade['token'], array('token'=> $trade['token'],'wecha_id'=>$trade['wecha_id'],'success'=>1));
            $this->redirect($redirect);  
            //$this->success('交易成功',U('Wap/Shop/index',array('shop'=> $trade['token'],'wecha_id'=>$trade['wecha_id'])));
        }else {
            $redirect = WapAction::generatePayResultUrl('Shop/my', $trade['token'], array('token'=> $trade['token'],'wecha_id'=>$trade['wecha_id'],'success'=>0));
            $this->redirect($redirect);  
            //$this->error('交易失败',U('Wap/Shop/index',array('shop'=> $trade['token'],'wecha_id'=>$trade['wecha_id'])));
        }
    }
    
    

}
?>
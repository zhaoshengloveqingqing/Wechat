<?php
//银联支付
		$order_amount='0.01';
		if (version_compare(phpversion(), '5.4.10', '>')) {
					include_once($_SERVER['DOCUMENT_ROOT'].'/Dev/Lib/ORG/yinlian/lib.php');
				}else{
					include_once($_SERVER['DOCUMENT_ROOT'].'/Dev/Lib/ORG/yinlian/netpayclient.php');
				}
				$merid = buildKey('keys/MerPrK_808080301000216_20141106164338.key');
				if($merid) {
					$merid = $_REQUEST["merid"];
					$orderno = $_REQUEST["orderno"];
					$transdate = $_REQUEST["transdate"];
					$amount = $_REQUEST["amount"];
					$currencycode = $_REQUEST["currencycode"];
					$transtype = $_REQUEST["transtype"];
					$status = $_REQUEST["status"];
					$checkvalue = $_REQUEST["checkvalue"];
					$gateId = $_REQUEST["GateId"];
					$priv1 = $_REQUEST["Priv1"];
					$plain = $merid . $orderno . $amount . $currencycode . $transdate . $transtype . $status . $checkvalue;
					//对订单验证签名  
					$flag = verifyTransResponse($merid, $orderno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);  
					$flag  =  verify($plain, $checkvalue);  
					if(!flag){  
						echo "<h2>验证签名失败！</h2>";
						exit;  
						} 
				?> 
				<html>
				<body onload="document.getElementById('form').submit();">  
				<form id="form" action="https://payment.ChinaPay.com/pay/TransGet" method="post"> 
					<input type=hidden name="merid" value="<?php echo $merid; ?>"/> 
					<input type=hidden name="orderno" value="<?php echo $ordid; ?>"/> 
					<input type=hidden name="transdate" value="<?php echo $transamt; ?>"/>
					<input type=hidden name="amount" value="<?php echo $curyid; ?>"/>
					<input type=hidden name="currencycode" value="<?php echo $transdate; ?>"/>
					<input type=hidden name="transtype" value="<?php echo $transtype; ?>"/>
					<input type=hidden name="status" value="<?php echo $version; ?>"/>
					<input type=hidden name="checkvalue" value="<?php echo $bgreturl; ?>"/>
					<input type=hidden name="GateId" value="<?php echo $pagereturl; ?>"/>
					<input type=hidden name="Priv1" value="8607">
				</form>
				</body>
				</html>
				<?php
				}
				else {
					die('Failed');
				}
				exit();
?>
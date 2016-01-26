<?php
	include_once('netpayclient.php');

	$merid = buildKey('keys/MerPrK_808080301000216_20141106164338.key');

	if($merid) {
		$ordid = "00" . date('YmdHis'); # Order ID
		$transamt = padstr('4444',12); # Amount
		$curyid = "156"; # Currency Type, Use CNY
		$transdate = date('Ymd'); # Order Date
		$transtype = "0001"; # Transaction type, Consume
		$version = "20070129"; # Payment Version
		$pagereturl = "http://www.pinet.cc/yinlian/feedback.php"; # Feedback Url
		$bgreturl = "http://www.pinet.cc/yinlian/feedback.php";
		$priv1 = "Wechat";
		$plain = $merid . $ordid . $transamt . $curyid . $transdate . $transtype . $priv1; # The body
		
		$chkvalue = sign($plain);
?>

	<form action="https://payment.ChinaPay.com/pay/TransGet" method="post"> 
		<input type=hidden name="MerId" value="<?php echo $merid; ?>"/> 
		<input type=hidden name="OrdId" value="<?php echo $ordid; ?>"/> 
		<input type=hidden name="TransAmt" value="<?php echo $transamt; ?>"/>
		<input type=hidden name="CuryId" value="<?php echo $curyid; ?>"/>
		<input type=hidden name="TransDate" value="<?php echo $transdate; ?>"/>
		<input type=hidden name="TransType" value="<?php echo $transtype; ?>"/>
		<input type=hidden name="Version" value="<?php echo $version; ?>"/>
		<input type=hidden name="BgRetUrl" value="<?php echo $bgreturl; ?>"/>
		<input type=hidden name="PageRetUrl" value="<?php echo $pagereturl; ?>"/>
		<input type=hidden name="GateId" value="8607">
		<input type=hidden name="Priv1" value="<?php echo $priv1; ?>">
		<input type=hidden name="ChkValue" value="<?php echo $chkvalue; ?>">
		<input type="submit" value="Test"/>
	</form>
<?php
	}
	else {
		die('Failed');
	}

<?php
$key='21B039458D76AAD8C0C06203DEFADAC9B948BB19633EB3C2';
$merchantid='03340103030956000';
if ($merchantid!=null)
		{	
			//MERCHANTID=03340103030956000&ORDERSEQ=00020141119094450111&ORDERDATE=20141119&ORDERAMOUNT=2000&KEY=21B039458D76AAD8C0C06203DEFADAC9B948BB19633EB3C2
			$order_amount=222;
				if($p=1) {
						$merid='03340103030956000';
						$ordid = "00" . date('YmdHis'); # Order ID
						//$ordid = '0020141119113004'; # Order ID
						$attachamount = "0"; # Payment Version
						$productamount='1';
						$orderamount =$attachamount+$productamount; # Amount
					
						$orderdate=date('YmdHis');
						//$orderdate='20141119112328';
						//md5 mac
						$macmd5="MERCHANTID=$merchantid&ORDERSEQ=$ordid&ORDERDATE=$orderdate&ORDERAMOUNT=$orderamount&KEY=$key";
						//$mac='c612179b6364333aa91afcba75c2479e';
						
						$mac=md5($macmd5);
						
						$curtype='RMB';
						$orderreqtranseq="99" . date('YmdHis'); # Order ID
						$encodetype = "1"; # Currency Type, Use CNY
						$transdate = date('Ymd'); # Order Date
						$busicode = "0001"; # Transaction type, Consume
		
						$pagereturl = "http://www.pinet.cc/web.backurl.php"; # Feedback Url
						$bgreturl = "http://www.pinet.cc/web.backurl.php";
						$productdesc='Iphone6 plus';
				?>
				<html>
				<body onload="document.getElementById('form').submit();">
				<form id="form" action="https://webpaywg.bestpay.com.cn/payWeb.do" method="post">
					<input type=hidden name="MERCHANTID" value="<?php echo $merid; ?>"/>
					<input type=hidden name="ORDERSEQ" value="<?php echo $ordid; ?>"/>
					<input type=hidden name="ORDERREQTRANSEQ" value="<?php echo $orderreqtranseq; ?>"/>
					<input type=hidden name="ORDERDATE" value="<?php echo $orderdate; ?>"/>
					<input type=hidden name="ORDERAMOUNT" value="<?php echo $orderamount; ?>"/>
					<input type=hidden name="PRODUCTAMOUNT" value="<?php echo $productamount; ?>"/>
					<input type=hidden name="ATTACHAMOUNT" value="<?php echo $attachamount; ?>"/>
					
					<input type=hidden name="CURTYPE" value="<?php echo $curtype; ?>"/>
					<input type=hidden name="ENCODETYPE" value="<?php echo $encodetype; ?>"/>
					<input type=hidden name="MERCHANTURL" value="<?php echo $pagereturl; ?>"/>
					<input type=hidden name="BACKMERCHANTURL" value="<?php echo $bgreturl; ?>"/>
					<input type=hidden name="BUSICODE" value="<?php echo $busicode; ?>"/>
					<input type=hidden name="PRODUCTDESC" value="<?php echo $productdesc; ?>"/>
					<input type=hidden name="MAC" value="<?php echo $mac; ?>"/>
				</form>
				</body>
				</html>
				<?php
				}
				else {
						die('Failed');
				}
				exit();
		}
?>

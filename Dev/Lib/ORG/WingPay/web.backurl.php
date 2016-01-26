<?php
$key='21B039458D76AAD8C0C06203DEFADAC9B948BB19633EB3C2';
$merchantid='03340103030956000';
if($_POST['UPTRANSEQ']!=null){
			$UPTRANSEQ=$_POST['UPTRANSEQ'];
			$TRANDATE=$_POST['TRANDATE'];
			$RETNCODE=$_POST['RETNCODE'];
			$RETNINFO=$_POST['RETNINFO'];
			$ORDERREQTRANSEQ=$_POST['ORDERREQTRANSEQ'];
			$ORDERSEQ=$_POST['ORDERSEQ'];
			$ORDERAMOUNT=$_POST['ORDERAMOUNT'];
			$PRODUCTAMOUNT=$_POST['PRODUCTAMOUNT'];
			$ATTACHAMOUNT=$_POST['ATTACHAMOUNT'];
			$CURTYPE=$_POST['CURTYPE'];
			$ENCODETYPE=$_POST['ENCODETYPE'];
			$BACKID=$_POST['BANKID'];
			$SIGN=$_POST['SIGN'];
			//compare sign
			$originalsign="UPTRANSEQ=$UPTRANSEQ&MERCHANTID=$merchantid&ORDERID=$ORDERSEQ&PAYMENT=$ORDERAMOUNT&RETNCODE=$RETNCODE&RETNINFO=$RETNINFO&PAYDATE=$TRANDATE&KEY=$key";
			$md5_originalsign=md5($originalsign);
			//$sub_originalsign=str_replace(array("\t","\n","\r"),'',stripcslashes($md5_originalsign));
			var_dump($SIGN);
			var_dump(strtoupper($md5_originalsign));
			if($SIGN==strtoupper($md5_originalsign)){
				echo  "UPTRANSEQ_".$UPTRANSEQ;
			}
			else{
				echo "error";			
			}
			
		}
?>

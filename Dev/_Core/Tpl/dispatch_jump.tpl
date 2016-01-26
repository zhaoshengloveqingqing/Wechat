<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>跳转提示</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
.system-message{ padding:0 0 48px;margin:150px auto;width:590px;border:10px solid #c1d1df; height:250px; background-color:#ebf3f9;}
.system-message h3{ font-size: 50px; font-weight: normal; line-height: 120px; margin-bottom: 12px;border:1px solid #ccc}
.system-message .jump{ padding-top: 10px; color:#47596b; font-size:18px;padding-top:150px;}
.system-message .jump a{ color: #ff0000; text-decoration:underline;}
.system-message .success,.system-message .error{ line-height: 1.8em; font-size: 23px ; float:left;}
.system-message .detail{ font-size: 12px; line-height: 30px; margin-top: 12px; display:none}
</style>
</head>
<body>
<div class="system-message">
	<div style="padding:24px;">
		<present name="message">		
		<div class="success"><img style="margin-left:40px;padding-top:50px;" src="/images/system/success.png"><div style="color:#0084ff; font-weight:bolder; font-size:40px; margin-top:-100px; margin-left:200px;"><?php echo($message); ?></div></div>
		<else/>		
		<div class="error"><img style="margin-left:40px;padding-top:50px;" src="/images/system/error.png" style="cursor:pointer;"><div style="color:#0084ff; font-weight:bolder; font-size:40px; margin-top:-100px; margin-left:200px;"><span><?php echo($error); ?></div></div>
		</present>
	
	</div><br />
<p class="detail"></p>
<div class="jump" style="float:left;padding-left:225px; margin-top:-130px;">
页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b></div>
</div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time == 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>
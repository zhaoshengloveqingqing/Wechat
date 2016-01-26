<?php header('HTTP/1.1 500 Internal Server Error'); ?>
<!DOCTYPE html> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
<title>出错啦！</title>
<style type="text/css">
*{ padding: 0; margin: 0; }
body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
@media screen and (min-width: 1280px) {
  .system-message{ padding:24px;margin:150px auto;width:590px;border:10px solid #c1d1df; height:250px; background-color:#ebf3f9;}
  .system-message .notify{ line-height: 1.8em; font-size: 23px ; float:left; margin-left:20px;width: 60%;}
  .system-message .jump{ padding: 5px; color:#4b4c4d; font-size:16px; clear: left;float: left;border: 1px #fadaba solid; background-color:#fdf1e0;}
  .system-message .jump a{ color: #099621; text-decoration:underline;}
}

@media screen and (max-width: 1000px) {
.system-message{ padding:24px;margin:15% auto; width:70%;border:10px solid #c1d1df; height:250px; background-color:#ebf3f9;}
.system-message .notify{ line-height: 1.5em; font-size: 23px ; float:left; width: 100%;}
.system-message .jump{ padding: 5px; color:#4b4c4d; font-size:16px; clear: left;float: left;border: 1px #fadaba solid; background-color:#fdf1e0;}
.system-message .jump a{ color: #099621; text-decoration:underline;}
img {display:none;}
}
</style>

</head>
<body>
<div class="system-message">
    <div class="notify">
      <div style="float:left;color:#0084ff; font-weight:bolder; font-size:20px; margin-top:25px;">
        Oops!<br>
        系统出错啦！我们已记录下错误信息，请稍后再试。
   	  </div>
      <div class="jump">
        <b id="wait">5</b>秒后 <a id="href" href="javascript:history.go(-1);">返回上一页</a> 
      </div>
    </div>
    <img style="float:right;margin-top:50px;" src="/images/system/server_error.png">
  
</div>
  
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time == 0) {
		clearInterval(interval);
		history.go(-1);
	};
}, 1000);
})();
</script>
</div>
</body>
</html>
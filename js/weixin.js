function trim(text) {
    return $.trim(text.replace(/(<[^>]+>)|(&nbsp;)/ig,""));
};
function encode(text){
    return text.replace(/&amp;/ig,"&").replace(/&nbsp;/ig," ").replace(/&[a-z]{2,4};/ig,"");
};

jQuery.fn.center = function () {  
  $win = $(window);  
  this.css('position','absolute');  
  this.css('top', (($win.height() - this.outerHeight()) / 2) + $win.scrollTop() + 'px');  
  this.css('left', (($win.width() - this.outerWidth()) / 2) + $win.scrollLeft() + 'px');  
  return this;  
} 

function _toast(text,fun){
      $("#toast").show();
      $("#toast").text(text);
      $("#toast").center();
      setTimeout(function(){
         $("#toast").hide();
         if(fun){(fun)();}
      },3*1000);
};

function ok(text, fun) {
      $("#ok_text").text(text);
      $("#ok").center();
	  $("#ok").show();
      setTimeout(function(){
         $("#ok").hide();
         if(fun){(fun)();}
      },2*1000);
}

function guide() {
    cover(true);
    $("#guide").show();
}

function cover(show) {
    if (show) {
        var winWidth=document.documentElement.clientWidth;
        if (winWidth < document.documentElement.scrollWidth) {
		    winWidth = document.documentElement.scrollWidth;
        }		
        var winHeight=document.documentElement.clientHeight;
		if (winHeight < document.documentElement.scrollHeight) {
		    winHeight = document.documentElement.scrollHeight;
        }
        $("#cover").width(winWidth);  
        $("#cover").height(winHeight); 
        $("#cover").show();
	 } else {
	    $("#cover").hide();
	 }
}

        function setCookies(name, value) {
            var DAY = 30; //此cookie将被保存30天
            var expire = new Date();
            expire.setTime(expire.getTime() + DAY * 24 * 60 * 60 * 1000);

            document.cookie = name + "=" + encodeURIComponent(value) + ";expires=" + expire.toGMTString();
        }

        // 获得cookie
        function getCookies(name) {
            // 一个cookie在字符串开头(^)或者以空格开头
            // 一个cookie以分号(;)结尾或者在字符串末尾($)
            // 得到的结果
            var a = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));

            if (a !== null) {
                return decodeURIComponent(a[2]);
            } else {
                return null;
            }
        }
  
    /*function weixinTimeline() {
        if (typeof WeixinJSBridge == "undefined") {           
            alert("此功能只能在微信浏览器中使用");       
        } else {           
            WeixinJSBridge.invoke('shareTimeline', 
            {
                 "title": dataForWeixin.title,
                 "link": dataForWeixin.path,
                 "desc": dataForWeixin.desc,              
                 "img_url": dataForWeixin.Img			 
            });       
        }   
    }

    function weixinFriend() {
        if (typeof WeixinJSBridge == "undefined") {           
            alert("此功能只能在微信浏览器中使用");       
        } else {           
            WeixinJSBridge.invoke('sendAppMessage', 
            {
                 "title": dataForWeixin.title,
                 "link": dataForWeixin.path,
                 "desc": dataForWeixin.desc,              
                 "img_url": dataForWeixin.Img			 
            });       
        }   
    } */
	
$(function() {
   var onBridgeReady=function(){
       WeixinJSBridge.on('menu:share:appmessage', function(argv){
          WeixinJSBridge.invoke('sendAppMessage',{
             "appid":dataForWeixin.appId,
             "img_url":dataForWeixin.Img,
             "img_width":"120",
             "img_height":"120",
             "link":dataForWeixin.path,
             "desc":dataForWeixin.desc,
             "title":dataForWeixin.title
          }, function(res){(dataForWeixin.callback)();});
       });
       WeixinJSBridge.on('menu:share:timeline', function(argv){
          (dataForWeixin.callback)();
          WeixinJSBridge.invoke('shareTimeline',{
             "img_url":dataForWeixin.Img,
             "img_width":"120",
             "img_height":"120",
             "link":dataForWeixin.path,
             "desc":dataForWeixin.desc,
             "title":dataForWeixin.title
          }, function(res){});
       });
       WeixinJSBridge.on('menu:share:weibo', function(argv){
          WeixinJSBridge.invoke('shareWeibo',{
             "content":dataForWeixin.title,
             "url":dataForWeixin.path
          }, function(res){(dataForWeixin.callback)();});
       });
       WeixinJSBridge.on('menu:share:facebook', function(argv){
          (dataForWeixin.callback)();
          WeixinJSBridge.invoke('shareFB',{
             "img_url":dataForWeixin.Img,
             "img_width":"120",
             "img_height":"120",
             "link":dataForWeixin.path,
             "desc":dataForWeixin.desc,
             "title":dataForWeixin.title
          }, function(res){});
       });
   };
     if(document.addEventListener){  
             document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);  
     } else if(document.attachEvent){  
             document.attachEvent('WeixinJSBridgeReady'   , onBridgeReady);  
             document.attachEvent('onWeixinJSBridgeReady' , onBridgeReady);  
     };
});

function SetFrameScroll(IsScroll,defaultValue)
 {  
     if(IsScroll)
     {    
        if(window.name=="aa")
        {
             window.document.body.scroll="no"; 
        }
        else
        { 
           if(defaultValue==""||typeof(defaultValue)=="undefined")
          {
             window.document.body.scroll="";  
          }
          else
          {
             window.document.body.scroll=defaultValue;
           }            
        }
     }
     else
     {      
      window.document.body.scroll="no";
     }
 }
function ShowAlert(title,message)
{
    Ext.onReady(function() {   
        title="<div class='info'></div><div class='title'>"+title+"</div>";
        message="<UL><LI>"+message+"</LI></UL>";   
        var defaultValue = window.document.body.scroll;
        SetFrameScroll(false,defaultValue); 
        Ext.MessageBox.alert(title,message,function(){ SetFrameScroll(true,defaultValue);});});
}
 
function ShowConfirm(title,message,callback)
{
 Ext.onReady(function() {   
        title="<div class='info'></div><div class='title'>"+title+"</div>";
        message="<UL><LI>"+message+"</LI></UL>";           
       var  defaultValue = window.document.body.scroll;
        SetFrameScroll(false,defaultValue); 
        Ext.MessageBox.confirm(title,message,function(btn){ 
                                                        SetFrameScroll(true,defaultValue); 
                                                         if(btn=="yes")
                                                            eval(callback);
                                                         else
                                                            iframeAutoFit();
                                                        });
               });
}
 
function ShowAlertAndGotoUrl(title,message,url)
{ 
    Ext.onReady(function() {    
    title="<div class='info'></div><div class='title'>"+title+"</div>";
    message="<UL><LI>"+message+"</LI></UL>";
      var defaultValue = window.document.body.scroll;
     Ext.MessageBox.alert(title,message,function(){SetFrameScroll(true,defaultValue); window.location.href=url;});
    });
}
function ShowErrorMsg(title,messageId)
{
  Ext.onReady(function() { 
        title="<div class='info'></div><div class='title'>"+title+"</div>";
        var defaultValue = window.document.body.scroll;
        SetFrameScroll(false,defaultValue); 
        var strTemp=Ext.get(messageId);
        strTemp.dom.style.display="none";
        Ext.MessageBox.alert(title,strTemp.dom.innerHTML,function(){ SetFrameScroll(true,defaultValue);}); 
         }); 
         iframeAutoFit(); //解决内页滚动  
}

function FrameDialog(/*title,url,callback,width,height*/)
{ 
    var Title// = title;
    var Url //= url;
    var Callback //= callback;
    var Width //= width;
    var Height //= height;
// create the LayoutExample application (single instance)
    this.LayoutExample = function() {

        // everything in this space is private and only accessible in the HelloWorld block

        var defaultValue = window.document.body.scroll;
        if (window.name == "" || window.name == "aa") {
            SetFrameScroll(false, defaultValue);
        }
        else {
            SetFrameScroll(false, defaultValue);
            //   Ext = parent.window.Ext;
        }

        // define some private variables
        var dialog, showBtn;
        showBtn = "dialogFrame";
        // return a public interface

        dialog = Ext.DialogManager.get(showBtn);
        if (window.name == "" || window.name == "ECTSdialogFrame") {
        }
        else {
            if (dialog) {
                dialog.purgeListeners();
                // dialog.restoreState();
                //dialog.destroy(true);
                // dialog=null;
            }
        }

        var handleHide = function() {

            if (dialog.isVisible()) {
                dialog.hide();
            }
            try {
                if (window.name == "" || window.name == "ECTSdialogFrame") {
                    SetFrameScroll(true, defaultValue);
                    var callbackfunction = _frameDialog.Callback.substr(_frameDialog.Callback.indexOf('.') + 1);
                    if (callbackfunction != "null" && callbackfunction != "") {
                        eval(_frameDialog.Callback);
                    }
                }
                else {
                    SetFrameScroll(true, defaultValue);
                    window.frames.window.eval(_frameDialog.Callback);
                }
            }
            catch (e) {
                alert(e.message);
            }

        };

        return {
            showDialog: function() {

                if (!dialog) { // lazy initialize the dialog and only create it once
                    dialog = new Ext.LayoutDialog(showBtn, {
                        autoCreate: true,
                        modal: true,
                        width: _frameDialog.Width,
                        height: _frameDialog.Height,
                        shadow: true,
                        minWidth: 500,
                        minHeight: 300,
                        proxyDrag: true,
                        resizable: true,
                        center: {
                            autoScroll: true,
                            tabPosition: 'top',
                            closeOnTab: true,
                            alwaysShowTabs: true
                        },
                        fixedcenter: true,
                        collapsible: false
                    });
                    dialog.on("hide", handleHide);
                    dialog.addKeyListener(27, dialog.hide, dialog);

                    setCookie("width", _frameDialog.Width, 1);
                    setCookie("height", _frameDialog.Height, 1);
                }
                else {

                    var oldWidth, oldHeight;
                    oldWidth = getCookie("width");
                    oldHeight = getCookie("height");
                    if (window.name == "" || window.name == "ECTSdialogFrame") {
                        if (oldWidth != _frameDialog.Width || oldHeight != _frameDialog.Height) {
                            dialog.resizeTo(_frameDialog.Width, _frameDialog.Height);
                            dialog.moveTo(0, 0);
                        }
                    }
                    else {
                        if (oldWidth != _frameDialog.Width || oldHeight != _frameDialog.Height) {
                            dialog.resizeTo(_frameDialog.Width, _frameDialog.Height);
                            //dialog.moveTo(0,0);
                        }

                    }
                    setCookie("width", _frameDialog.Width, 1);
                    setCookie("height", _frameDialog.Height, 1);

                }

                dialog.setTitle("<div class='info'></div><div class='title'>" + _frameDialog.Title + "</div>");
                var layout = dialog.getLayout();
                layout.beginUpdate();
                var iframePanel = new Ext.ContentPanel("dialogFrameContent", {
                    autoCreate: true,
                    fitContainer: true,
                    fitToFrame: true,
                    autoScroll: true
                });
                var iframePanelEl = iframePanel.getEl();
                iframePanelEl.dom.style.scrolling = "no";
                iframePanelEl.dom.style.overflow = "hidden";
                iframePanelEl.dom.innerHTML = '';
                iframePanelEl.dom.innerHTML = '<iframe name="ECTSdialogFrame" id="ECTSdialogFrame" frameborder=0 src=\"' + _frameDialog.Url + '" scrolling="auto" style="overflow-x:hidden;overflow-y:auto;width:' + (_frameDialog.Width - 10) + 'px;height:' + (_frameDialog.Height - 8) + 'px;"/>';
                layout.add('center', iframePanel);
                layout.endUpdate();
                //alert(iframePanelEl.dom.outerHTML);
                dialog.show(showBtn);
            }
        };
    } ();

   
    this.registDialog = function(){
        //alert(this.LayoutExample);
        // using onDocumentReady instead of window.onload initializes the application
        // when the DOM is ready, without waiting for images and other resources to load
        Ext.EventManager.onDocumentReady(this.LayoutExample.showDialog, this.LayoutExample, true);
    }
}

function CloseShowDialogFrame()
{
     // create the LayoutExample application (single instance)
    var LayoutExample = function(){
        // everything in this space is private and only accessible in the HelloWorld block
          
        // define some private variables
        var dialog, showBtn;
        showBtn="dialogFrame";  
        // return a public interface
         dialog=parent.window.Ext.DialogManager.get(showBtn);
          return {
            hideDialog : function(){  
                if(dialog!=null)
                     dialog.hide();
                     
                /*     if (window.name == "" || window.name == "ECTSdialogFrame") {
                        SetFrameScroll(true, defaultValue);
                        var callbackfunction = callback.substr(callback.indexOf('.') + 1);
                        if (callbackfunction != "null" && callbackfunction != "")
                            eval(callback);
                    }
                    else {
                        SetFrameScroll(true, defaultValue); 
                            window.frames.window.eval(callback);
                    }*/
                 }
            }
     
        
    }();

    // using onDocumentReady instead of window.onload initializes the application
    // when the DOM is ready, without waiting for images and other resources to load
    Ext.EventManager.onDocumentReady(LayoutExample.hideDialog, LayoutExample, true);
}

function AlertAndCloseShowDialogFrame(title,message)
{
   Ext.onReady(function() {    
    title="<div class='info'></div><div class='title'>"+title+"</div>";
    message="<UL><LI>"+message+"</LI></UL>";
      var defaultValue = window.document.body.scroll;
      SetFrameScroll(false,defaultValue);
     Ext.MessageBox.alert(title,message,function(){CloseShowDialogFrame();});
    });
}
function setCookie(arg_CookieName,arg_CookieValue,arg_CookieExpireDays){
    
    if(arg_CookieExpireDays != null)
    {
	    var todayDate = new Date();
	    todayDate.setDate(todayDate.getDate() + arg_CookieExpireDays);
	    document.cookie = arg_CookieName+"="+arg_CookieValue+";path=/;expires="+todayDate.toGMTString()+";";
	}
	else {
	    var todayDate = new Date();
	    todayDate.setDate(todayDate.getDate() + 1);
	    document.cookie = arg_CookieName + "=" + arg_CookieValue + ";path=/;expires=" + todayDate.toGMTString() + ";";
	}
}

function getCookie(arg_CookieName){
	var Found = false;
	var start,end;
	var i = 0;
	var cookieName = arg_CookieName + "=";
	while(i <= document.cookie.length){
		start = i;end = start+cookieName.length;
		if(document.cookie.substring(start,end) == cookieName){
			Found = true;break;
		}
		i++;
	}

	if(Found==true){
		start = end;
		end = document.cookie.indexOf(";",start);
		if(end < start)
		{
			end = document.cookie.length;
		}
		return document.cookie.substring(start,end);
	}
	return "";
}

var _frameDialog = null;

function ShowTopDialogFrame(title,url,callback,width,height)
{
    var win = window;
    var frm = "";
    while (win.parent != null && win.parent != win) {
        frm += win.frameElement.id + "."
        win = win.parent;
    }
    if(win._frameDialog == null){
        win._frameDialog = new win.FrameDialog(/*title, url, frm + callback, width, height*/);
        
    }
   //alert(win._frameDialog);
    win._frameDialog.Title = title;
    win._frameDialog.Url = url;
    win._frameDialog.Callback = frm + callback;//alert(frm + callback);
    win._frameDialog.Width = width;
    win._frameDialog.Height = height;
//       alert(win.FrameDialog.registDialog());
    win._frameDialog.registDialog();

    //alert(document.cookie);
    //alert(win.getCookie("ECTS_DialogKey"));
}


function CloseTopDialogFrame()
{
    var win = window;
    while (win.parent != null && win.parent != win)
        win = win.parent;
   
    win.CloseShowDialogFrame();
}

function ShowTopAlert(title,message)
{
    var win = window;
    while (win.parent != null && win.parent != win) {    
        win = win.parent;
    }
    win.ShowAlert(title,message);  
}

function ShowTopAlertAndGotoUrl(title,message,url)
{ 
    var win = window;
    while (win.parent != null && win.parent != win) {    
        win = win.parent;
    }
    win.Ext.onReady(function() {    
    title="<div class='info'></div><div class='title'>"+title+"</div>";
    message="<UL><LI>"+message+"</LI></UL>";
      var defaultValue = window.document.body.scroll;
     win.Ext.MessageBox.alert(title,message,function(){SetFrameScroll(true,defaultValue); window.location.href=url;});
    });
}

function ShowConfirmNo(title, message, callback) {
    Ext.onReady(function() {
        title = "<div class='info'></div><div class='title'>" + title + "</div>";
        message = "<UL><LI>" + message + "</LI></UL>";
        var defaultValue = window.document.body.scroll;
        SetFrameScroll(false, defaultValue);
        Ext.MessageBox.confirm(title, message, function(btn) {
            SetFrameScroll(true, defaultValue);
            if (btn == "no")
                eval(callback);
            else
                iframeAutoFit();
        });
    });
}

function TransferLogin(url) {
    if (window.parent != null) {
        window.parent.location.href = url;
    }
    else {
        window.location.href = url;
    }
}

function ReloadDefault(menuid,url,type)
{
  var msg = "删除成功！";
  if(type=='A')
  {
  msg = "保存成功！";
  }
    ShowAlert('提示',msg);
//alert(msg);
 if (window.parent != null) {
        window.parent.location.href = '/Default.aspx?menu=' + menuid + '&openUrl=('+url+')';
    }
}




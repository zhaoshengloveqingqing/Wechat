    function Request(strUrl,strName) 
    {
        var strHref = strUrl;
        if (strHref.indexOf("JumpID") == -1) {
            return "";
        }
        var intPos = strHref.indexOf("?");
        if (intPos == -1) {
            return "";
        }
        var strRight = strHref.substr(intPos + 1); 

        var arrTmp = strRight.split("&"); 
        for(var i = 0; i < arrTmp.length; i++) 
        { 
            var arrTemp = arrTmp[i].split("=");

            if (arrTemp[0].toUpperCase() == strName.toUpperCase()) {
                return arrTemp[1];
            }
        } 
        return ""; 
    }

   function SetCurrentMenu()
   {
       if (window != parent) {
           var page = "";
           var folder = "";
           var url = window.location.href;
           var jumpID = Request(url, "JumpID");
           if (jumpID != "") {
               url = jumpID;
           }
           else {
              
               var nIndex = url.lastIndexOf("/");
               var mIndex = url.lastIndexOf(".");
               //                   alert(nIndex);
               //                   alert(url.length);

               page = url.substring(nIndex + 1, mIndex)
//               alert(page);
               url = url.substring(0, nIndex);
               nIndex = url.lastIndexOf("/");
               folder = url.substring(nIndex + 1);
               url = folder + page;
               //                   alert(url);
           }
           if (url != "") {
//               alert(url);
               var currentObj = parent.document.getElementById("MenuhidCurrent");
               if (currentObj != null) {
                   if (url != currentObj.value) {

                       var obj = parent.document.getElementById(url);
                       if (obj == null) {
                           obj = parent.document.getElementById(folder);
                       }
                       if (obj != null) {
                           if (currentObj.value != "") {
                               if (parent.document.getElementById(currentObj.value) != null) {
                                   parent.document.getElementById(currentObj.value).className = "";
                               }
                           }
                           obj.className = "active";
                           currentObj.value = url;
                       }

                   }
               }
           }
       }
   }
   function PageLoad() {
       
       iframeAutoFit();
   }
   
    function iframeAutoFit() {

        
        try
        {
            if (window != parent) {

                /*                
                var a = parent.document.getElementsByTagName("IFRAME");
                
                for (var i = 0; i < a.length; i++) //author:meizz
                {
                if (a[i].contentWindow == window) {
                        
                var h1 = 0, h2 = 0;
                a[i].parentNode.style.height = a[i].offsetHeight + "px";
                a[i].style.height = "10px";
                if (document.documentElement && document.documentElement.scrollHeight) {
                h1 = document.documentElement.scrollHeight;
                }
                if (document.body) h2 = document.body.scrollHeight;

                        var h = Math.max(h1, h2);
                if (document.all) { h += 4; }
                if (window.opera) { h += 1; }

                        if (h < 410) //change by alex
                {
                h = 410
                }
                a[i].style.height = a[i].parentNode.style.height = h + "px";
                }

                }
                */

                //changed by jack
                var frm = window.frameElement;

                if (frm != null && frm.id != "ECTSdialogFrame") {
                    var h1 = 0, h2 = 0;
                 
                    frm.parentNode.style.height = frm.offsetHeight + "px";
                    frm.style.height = "10px";
                    if (document.documentElement && document.documentElement.scrollHeight) {
                        h1 = document.documentElement.scrollHeight;
                    }
                    if (document.body) h2 = document.body.scrollHeight;

                    var h = Math.max(h1, h2);
                    if (document.all) { h += 4; }
                    if (window.opera) { h += 1; }

                    if (h < 410) //change by alex
                    {
                        h = 410
                    }
                    frm.style.height = frm.parentNode.style.height = h + "px";
                    frm.style.height = frm.parentNode.style.height = h + "px";
                }
            }
           
            
        }
        catch (ex){}
    }
    if(window.attachEvent)
    {
        window.attachEvent("onload", PageLoad);
        //window.attachEvent("onresize",  iframeAutoFit);
    }
    else if(window.addEventListener)
    {
        window.addEventListener('load', PageLoad, false);
        //window.addEventListener('resize',  iframeAutoFit,  false);
    }

    function iframeAutoFit1() {


        try {
            if (window != parent) {
                var a = parent.document.getElementsByTagName("IFRAME");
                for (var i = 0; i < a.length; i++) //author:meizz
                {
                    if (a[i].contentWindow == window) {
                        var h1 = 0, h2 = 0;
                        a[i].parentNode.style.height = a[i].offsetHeight + "px";
                        a[i].style.height = "10px";
                        if (document.documentElement && document.documentElement.scrollHeight) {
                            h1 = document.documentElement.scrollHeight;
                        }
                        if (document.body) h2 = document.body.scrollHeight;

                        var h = Math.max(h1, h2);
                        if (document.all) { h += 4; }
                        if (window.opera) { h += 1; }

                        if (h < 1100) //change by alex
                        {
                            h = 1100

                        }
                        alert(h);
                        a[i].style.height = a[i].parentNode.style.height = h + "px";
                    }

                }
            }

        }
        catch (ex) { }
    }
 

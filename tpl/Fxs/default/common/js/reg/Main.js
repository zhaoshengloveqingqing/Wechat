

// getElementById
var $ = function(element) {
	return typeof(element) == 'object' ? element : document.getElementById(element);
},

// getElementsByTagName
$t = function(tag, efrom) {
	return efrom == undefined ? document.getElementsByTagName(tag) : efrom.getElementsByTagName(tag);
},

// getElementsByTagName
$c = function(className,oBox) {
	//适用于获取某个HTML区块内部含有某一特定className的所有HTML元素
	this.d = oBox || document;
	var children = this.d.getElementsByTagName('*') || document.all;
	var elements = new Array();
	for (var ii = 0; ii < children.length; ii++) {
		var child = children[ii];
		var classNames = child.className.split(' ');
		for (var j = 0; j < classNames.length; j++) {
			if (classNames[j] == className) {
				elements.push(child);
				break;
			}
		}
	}
	return elements;
};

var getCoords = function(node) {
    var x = node.offsetLeft;
    var y = node.offsetTop;
    var parent = node.offsetParent;
    while (parent != null) {
        x += parent.offsetLeft;
        y += parent.offsetTop;
        parent = parent.offsetParent;
    }
    return { x: x, y: y };
}
,
// 获取页面大小
pagesize = null,
getPageSize = function() {
    var xScroll, yScroll;
    if (window.innerHeight && window.scrollMaxY) {
        xScroll = document.body.scrollWidth;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
        xScroll = document.body.offsetWidth;
        yScroll = document.body.offsetHeight;
    }
    var windowWidth, windowHeight;
    if (self.innerHeight) { // all except Explorer
        windowWidth = self.innerWidth;
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }
    // Modify By Mudoo
    var visibleWidth, visibleHeight;
    visibleWidth = document.body.clientWidth;
    visibleHeight = document.body.clientHeight;
    // for small pages with total height less then height of the viewport
    if (yScroll < windowHeight) {
        pageHeight = windowHeight;
    } else {
        pageHeight = yScroll;
    }
    // for small pages with total width less then width of the viewport
    if (xScroll < windowWidth) {
        pageWidth = windowWidth;
    } else {
        pageWidth = xScroll;
    }
    arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight, visibleWidth, visibleHeight);
    return arrayPageSize;
};

　var telFomat =/(^(\d{2,4}[-_－—]?)?\d{3,8}([-_－—]?\d{3,8})?([-_－—]?\d{1,7})?$)|(^0?1[35]\d{9}$)/; //电话格式
　//var mobileFomat=/^1[3,5]{1}[0-9]{1}[0-9]{8}$/; 
　var mobileFomat=/^\d*$/;//不限制位数  
 
/*---------------------------------------------------------------------------------*/
  //标签切换
  function showLab(current,lab1,lab2,lab3,lab4,lab5)
    { 
        if(lab1!=null&&$(lab1)!=null)
        {
            if($(current)==$(lab1))
            {
               $(lab1).className="active";
               $(lab1 + "_body").style.display = "";
               if ('undefined' != typeof (changeTab1))//页面定义了changeTab1方法执行,
               { 
                   changeTab1();
               }
            } 
            else
            {
            
               $(lab1).className="";
               $(lab1+"_body").style.display="none";
            }
        }
         //
         if(lab2!=null&&$(lab2)!=null)
         {
            if($(current)==$(lab2))
            {
               $(lab2).className="active";
               $(lab2+"_body").style.display="";
               if( 'undefined'!=typeof(changeTab2))//页面定义了changeTab2方法执行,
               {
    //                var str=lab2.toString();
    //                var pre=str.substr(0,str.length- "divDealInfo".length);
    //                if($(pre+"hidCurrent")!=null)
    //                {
    //                    $(pre+"hidCurrent").value='2';//设置当前标签
    //                }
                    changeTab2();
               }
            } 
            else
            {
               $(lab2).className="";
               $(lab2+"_body").style.display="none";
            } 
        }
        //
        
        if(lab3!=null&&$(lab3)!=null)
        {
            if($(current)==$(lab3))
            {
               $(lab3).className="active";
               $(lab3+"_body").style.display="";
               if( 'undefined'!=typeof(changeTab3))//页面定义了changeTab3方法执行,
               {
//                    var str=lab3.toString();
//                    var pre=str.substr(0,str.length- "divReagenInfo".length);
//                    if($(pre+"hidCurrent")!=null)
//                    {
//                        $(pre+"hidCurrent").value='3';//设置当前标签
//                    }
                    changeTab3();
               }
            } 
            else
            {
               $(lab3).className="";
               $(lab3+"_body").style.display="none";
            } 
        }
        if(lab5!=null&&$(lab5)!=null)
        {
            if($(current)==$(lab5))
            {
               $(lab5).className="active";
               $(lab5+"_body").style.display="";
            } 
            else
            {
               $(lab5).className="";
               $(lab5+"_body").style.display="none";
            } 
        }
        if(lab4!=null&&$(lab4)!=null)
        {
            if($(current)==$(lab4))
            {
               $(lab4).className="active";
               $(lab4+"_body").style.display="";
            } 
            else
            {
               $(lab4).className="";
               $(lab4+"_body").style.display="none";
            } 
        }
        iframeAutoFit();
    }
    
     //confirm回调函数
     function confirmCallback(control)
     {        
          var obj=$(control);
          if(obj!=null)
            obj.click();
     }
     
     
     var dialogReturn_key="ECTS_DialogKey"; //保存弹出窗口的返回值的cookie键值
     function DialogCloseCallback(control, callback) {
         var returnvalue = getCookie(dialogReturn_key); //获得窗口的返回值
         if(returnvalue!=null&&returnvalue!="")
         { 
            if(callback!=null)
            {
              eval(callback);
            }
            if(control!=null)
               $(control).click();
         } 
         setCookie(dialogReturn_key,"");//清空Cookie
//         iframeAutoFit();//iframe自动伸展，解决滚动问题
     }

//伸展和收缩
//img:点击触发伸展和收缩的img控件
// 伸展和收缩部分所在的容器（div,span,table 等）
function ShowOrHidden(img,panel)
{
    
     if($(panel).style.display=="none")//由收缩设置为伸展 
     {
         $(panel).style.display="";
         $(img).src="/App_Themes/EOCD_Theme/Images/Sub.gif";//显示减号
         $(img).alt="隐藏";
         iframeAutoFit();//iframe自动伸展，解决滚动问题
       
     }
     else  //由伸展设置为收缩
     {
         $(panel).style.display="none";
         $(img).src="/App_Themes/EOCD_Theme/Images/Add.gif";//显示加号
         $(img).alt="显示";    
     }
}

//判断是否有Radio被选择
function hasRadioCheck(scopeHtml)
{
  var  elements;
  if(scopeHtml==null)
  { 
  
    elements= document.getElementsByTagName("input");
    
  }
  else
  {
    var curHtml = document.getElementById(scopeHtml);
    elements = curHtml.getElementsByTagName("input");
  }
  for (var i=0;i<elements.length;i++)
  {
       if(elements[i].type=="radio"&&elements[i].checked)
       {
          return true;
          break;
       }
  }
  return false;
}



//判断是否有Radio被选择
function hasCheckBoxCheck(scopeHtml) {
    var elements;
    if (scopeHtml == null) {

        elements = document.getElementsByTagName("input");

    }
    else {
        var curHtml = document.getElementById(scopeHtml);
        elements = curHtml.getElementsByTagName("input");
    }
    for (var i = 0; i < elements.length; i++) {
        if (elements[i].type == "checkbox" && elements[i].checked) {
            return true;
            break;
        }
    }
    return false;
}


var objO;
var objBox;
var objErrorCode;
var objControl;
//转换故障代码全角为半角，并做格式验证
//传入 设备序列号
function CheckAndChangeErrorCode1(str,   equipmentId) {
    var result = '';
    for (i = 0; i < str.length; i++) {
        code = str.charCodeAt(i);             //获取当前字符的unicode编码
        if (code >= 65281 && code <= 65373)   //unicode编码范围是所有的英文字母以及各种字符
        {
            result += String.fromCharCode(str.charCodeAt(i) - 65248);    //把全角字符的unicode编码转换为对应半角字符的unicode码
        }
        else if (code == 12288)                                      //空格
        {
            result += String.fromCharCode(str.charCodeAt(i) - 12288 + 32); //半角空格
        } else {
            result += str.charAt(i);                                     //原字符返回
        }
    }
    result = result.toUpperCase();
    if (result.match("^[a-zA-Z\-0-9]+$")) { 
        //return result;
        //---------------------------------------------------Add by Pater
        objErrorCode = result;
        CheckErrorCode1(str,  equipmentId);
        //---------------------------------------------------end Add by Pater
    }
    else {
        ShowTopAlert("提示信息","故障代码只能输入英文、数字和短横分隔符！");
        return "";
    }
}


//传入 设备序列号，设备模块号
function CheckAndChangeErrorCode3(str, equipmentId, equipmentModuleId) {
    var result = '';
    for (i = 0; i < str.length; i++) {
        code = str.charCodeAt(i);             //获取当前字符的unicode编码
        if (code >= 65281 && code <= 65373)   //unicode编码范围是所有的英文字母以及各种字符
        {
            result += String.fromCharCode(str.charCodeAt(i) - 65248);    //把全角字符的unicode编码转换为对应半角字符的unicode码
        }
        else if (code == 12288)                                      //空格
        {
            result += String.fromCharCode(str.charCodeAt(i) - 12288 + 32); //半角空格
        } else {
            result += str.charAt(i);                                     //原字符返回
        }
    }
    result = result.toUpperCase();
    if (result.match("^[a-zA-Z\-0-9]+$")) {
        //return result;
        //---------------------------------------------------Add by Pater
        objErrorCode = result;
        CheckErrorCode3(str, equipmentId, equipmentModuleId);
        //---------------------------------------------------end Add by Pater
    }
    else {
        ShowTopAlert("提示信息", "故障代码只能输入英文、数字和短横分隔符！");
        return "";
    }
}

//传入 设备型号
function CheckAndChangeErrorCode2(str, equipmentModelId) {
    var result = '';
    for (i = 0; i < str.length; i++) {
        code = str.charCodeAt(i);             //获取当前字符的unicode编码
        if (code >= 65281 && code <= 65373)   //unicode编码范围是所有的英文字母以及各种字符
        {
            result += String.fromCharCode(str.charCodeAt(i) - 65248);    //把全角字符的unicode编码转换为对应半角字符的unicode码
        }
        else if (code == 12288)                                      //空格
        {
            result += String.fromCharCode(str.charCodeAt(i) - 12288 + 32); //半角空格
        } else {
            result += str.charAt(i);                                     //原字符返回
        }
    }
    result = result.toUpperCase();
    if (result.match("^[a-zA-Z\-0-9]+$")) {
        //return result;
        //---------------------------------------------------Add by Pater
        objErrorCode = result;
        CheckErrorCode2(str, equipmentModelId);
        //---------------------------------------------------end Add by Pater
    }
    else {
        ShowTopAlert("提示信息", "故障代码只能输入英文、数字和短横分隔符！");
        return "";
    }
}

//验证故障代码
var hideErrorCodeControl;
function CheckErrorCode1(errorCode,   equipmentId) { 
    if (errorCode == 'NA' || errorCode == 'Na' || errorCode == 'nA' || errorCode == 'na') {
        AddOnSuccessCheck1(); 
    }
    else {
        var params = 'ErrorCode=' + errorCode + '&EquipmentId=' + equipmentId;
        AjaxRequestMethod('CheckErrorCode', params, onSuccessCheck1);
    }
}

function CheckErrorCode3(errorCode, equipmentId, equipmentModuleId) {
    if (errorCode == 'NA' || errorCode == 'Na' || errorCode == 'nA' || errorCode == 'na') {
        AddOnSuccessCheck1();
    }
    else {
        var params = 'ErrorCode=' + errorCode + '&EquipmentId=' + equipmentId + '&EquipmentModuleId=' + equipmentModuleId;
        AjaxRequestMethod('CheckErrorCode', params, onSuccessCheck1);
    }
}

function CheckErrorCode2(errorCode, equipmentModelId) {
    if (errorCode == 'NA' || errorCode == 'Na' || errorCode == 'nA' || errorCode == 'na') {
        AddOnSuccessCheck1();
    }
    else {
        var params = 'ErrorCode=' + errorCode + '&EquipmentModelId=' + equipmentModelId;
        AjaxRequestMethod('CheckErrorCode', params, onSuccessCheck1);
    }
}


function onSuccessCheck1(requestResult) {
    if (requestResult == "0") {
        ShowConfirm("提示信息", "故障代码不存在,确认添加？", 'AddOnSuccessCheck1()');
        return false;
    }
    else if (requestResult == "1") {
        AddOnSuccessCheck1();
    }
}

function AddOnSuccessCheck1() { 
    if (objErrorCode != "") {
        var op = new Option(objErrorCode, objErrorCode);
        $(objBox).options.add(op);
        $(objO).value = ""; //清空textBox;
    }
 
    var saveCtr = $(objControl);
    if (saveCtr != null) {
        saveCtr.value = GetErrorCode1(objBox);
    }
} 

//故障代码添加
//参数:代码值/textbox,listbox
function AddErrorCode1(o, box,   equipmentId) { 
    //delete empty item
    if ($(box).options.length == 1 && $(box).options[0].value == "")
        $(box).options.remove(0);
   var value="";
   if($(o)!=null)
       value=$(o).value;
    else
       value=o;
   if (value != null && value != "") {

       //---------------------------------------------------Add by Pater
       objO = o;
       objBox = box; 
       //---------------------------------------------------end Add by Pater
        //转换全角，并验证
       value = CheckAndChangeErrorCode1(value,  equipmentId); 
   }

}


//故障代码添加
//参数:代码值/textbox,listbox
function AddErrorCode3(o, box, equipmentId, equipmentModuleId) {
    //delete empty item
    if ($(box).options.length == 1 && $(box).options[0].value == "")
        $(box).options.remove(0);
    var value = "";
    if ($(o) != null)
        value = $(o).value;
    else
        value = o;
    if (value != null && value != "") {

        //---------------------------------------------------Add by Pater
        objO = o;
        objBox = box;
        //---------------------------------------------------end Add by Pater
        //转换全角，并验证
        value = CheckAndChangeErrorCode3(value, equipmentId, equipmentModuleId);
    }
}
//故障代码添加
//参数:代码值/textbox,listbox
function AddErrorCode2(o, box, equipmentModelId) { 
    //delete empty item
    if ($(box).options.length == 1 && $(box).options[0].value == "")
        $(box).options.remove(0);
    var value = "";
    if ($(o) != null)
        value = $(o).value;
    else
        value = o;
    if (value != null && value != "") {

        //---------------------------------------------------Add by Pater
        objO = o;
        objBox = box; 
        //---------------------------------------------------end Add by Pater
        //转换全角，并验证
        value = CheckAndChangeErrorCode2(value, equipmentModelId); 
    } 

}


//故障代码添加并把值保存到指定控件中
//参数:textbox,listbox,用于保存故障代码的控件ID
function AddErrorCodeAndSave1(o, box, control, equipmentId) { 
    if (equipmentId == "" || equipmentId == undefined) {
        ShowTopAlert("提示信息", "请选择设备！");
        return false;
     }
     else {
           objControl = control;
        AddErrorCode1(o, box, equipmentId);
        
        var saveCtr = $(control);
        var code = $(o) != null ? $(o).value : "";
        if (code != "") {
            if (saveCtr != null) {
                /*--------------------------------------------
                if(saveCtr.value.length>0)
                saveCtr.value+="/"+code;
                else
                saveCtr.value = code;
                ---------------------------------------------*/
                saveCtr.value = GetErrorCode1(box);
            }
        }
    }
}


function AddErrorCodeAndSave3(o, box, control, equipmentId, equipmentModuleId) {
    if (equipmentId.value == "" || equipmentId.value == undefined) {
        ShowTopAlert("提示信息", "请选择设备！");
        return false;
    }
    else {
        objControl = control; 
        AddErrorCode3(o, box, equipmentId.value, equipmentModuleId.value);

        var saveCtr = $(control);
        var code = $(o) != null ? $(o).value : "";
        if (code != "") {
            if (saveCtr != null) {
                /*--------------------------------------------
                if(saveCtr.value.length>0)
                saveCtr.value+="/"+code;
                else
                saveCtr.value = code;
                ---------------------------------------------*/
                saveCtr.value = GetErrorCode1(box);
            }
        }
    }
}

//故障代码添加并把值保存到指定控件中
//参数:textbox,listbox,用于保存故障代码的控件ID
function AddErrorCodeAndSave2(o, box, control, equipmentModelId) {
    if (equipmentModelId == "" || equipmentModelId == undefined) {
        ShowTopAlert("提示信息", "请选择设备型号！");
        return false;
    }
    else {
        objControl = control;
        AddErrorCode2(o, box, equipmentModelId);

        var saveCtr = $(control);
        var code = $(o) != null ? $(o).value : "";
        if (code != "") {
            if (saveCtr != null) { 
                saveCtr.value = GetErrorCode1(box);
            }
        }
    }

}

//删除故障代码
function DelErrorCode1(box)
{
     if($(box).value!="")
     {
          $(box).options.remove($(box).selectedIndex);      
     }
}

//删除故障代码,,值从指定控件中移出
function DelErrorCodeaAndSave1(box,control)
{
     var value=$(box).value;
     if(value!="")
     {
         DelErrorCode(box);
         $(control).value= GetErrorCode1(box);
     }
} 

//获得listbox中的故障代码
function GetErrorCode1(obj)
{
   
    if($(obj)==null)
      return "";
    var codes="";
    var array = $(obj).options; 
    for(var i=0;i<array.length;i++) { 
        codes+=array[i].value+"/";
    }
    if(codes.length>0)
    codes=codes.substr(0,codes.length-1); 
    
    return codes;
}

//添加故障代码
//参数：listBox,故障代码
function LoadErrorCode1(obj, codes) { 
   if(obj!=null&&$(obj)!=null&&codes!="") {
     var array=codes.split('/');
     for(var i=0;i<array.length;i++) {
         if (array[i] != "") {
             var op = new Option(array[i], array[i]);
             $(obj).options.add(op);
         }
    }
   }

} 





/////////////////////////////////////////////////////////////////////原故障代码javascript
//转换故障代码全角为半角，并做格式验证
function CheckAndChangeErrorCode(str) {
    var result = '';
    for (i = 0; i < str.length; i++) {
        code = str.charCodeAt(i);             //获取当前字符的unicode编码
        if (code >= 65281 && code <= 65373)   //unicode编码范围是所有的英文字母以及各种字符
        {
            result += String.fromCharCode(str.charCodeAt(i) - 65248);    //把全角字符的unicode编码转换为对应半角字符的unicode码
        }
        else if (code == 12288)                                      //空格
        {
            result += String.fromCharCode(str.charCodeAt(i) - 12288 + 32); //半角空格
        } else {
            result += str.charAt(i);                                     //原字符返回
        }
    }
    result = result.toUpperCase();
    if (result.match("^[a-zA-Z\-0-9]+$")) {
        return result;
        //---------------------------------------------------Add by Pater
        // objErrorCode = result;    --mark  by tina
        //  CheckErrorCode(errorCode); --mark by tina
      
        //---------------------------------------------------end Add by Pater
    }
    else {
        ShowTopAlert("提示信息", "故障代码只能输入英文、数字和短横分隔符！");
        return "";
    }
}

//验证故障代码
function CheckErrorCode(errorCode) {
    var params = 'ErrorCode=' + errorCode;
    AjaxRequestMethod('CheckErrorCode', params, onSuccessCheck);
}


function onSuccessCheck(requestResult) {
//    if (requestResult == "0") {
//        ShowTopAlert("提示信息", "故障代码不存在！");
//        return false;
//    }
//    else if (requestResult == "1") {
        if (objErrorCode != "") {
            var op = new Option(objErrorCode, objErrorCode);
            $(objBox).options.add(op);
            $(objO).value = ""; //清空textBox;
        }

//    }
}

//故障代码添加
//参数:代码值/textbox,listbox
function AddErrorCode(o, box) {


    //delete empty item
    if ($(box).options.length == 1 && $(box).options[0].value == "")
        $(box).options.remove(0);
    var value = "";
    if ($(o) != null)
        value = $(o).value;
    else
        value = o;
    if (value != null && value != "") {

        //---------------------------------------------------Add by Pater
        objO = o;
        objBox = box;
        errorCode = value;
        //---------------------------------------------------end Add by Pater
        //转换全角，并验证
        value = CheckAndChangeErrorCode(value);

        //---------------------------------------------------modify by Pater
        //以下放到Ajax中  
        //       if (value != "") {
        //           var op = new Option(value, value);
        //           $(box).options.add(op);
        //           $(o).value = ""; //清空textBox;
        //       }
        //---------------------------------------------------end modify by Pater
    }
    //   if($(o)!=null)
    //       $(o).value="";//清空textBox;

}


//故障代码添加并把值保存到指定控件中
//参数:textbox,listbox,用于保存故障代码的控件ID
function AddErrorCodeAndSave(o, box, control) {
    var saveCtr = $(control);
    var code = $(o) != null ? $(o).value : "";
    if (code != "") {
        AddErrorCode(o, box);
        if (saveCtr != null) {
            /*--------------------------------------------
            if(saveCtr.value.length>0)
            saveCtr.value+="/"+code;
            else
            saveCtr.value = code;
            ---------------------------------------------*/
            saveCtr.value = GetErrorCode(box);
        }
    }
}

//删除故障代码
function DelErrorCode(box) {
    if ($(box).value != "") {
        $(box).options.remove($(box).selectedIndex);
    }
}

//删除故障代码,,值从指定控件中移出
function DelErrorCodeaAndSave(box, control) {
    var value = $(box).value;
    if (value != "") {
        DelErrorCode(box);
        $(control).value = GetErrorCode(box);
    }
}

//获得listbox中的故障代码
function GetErrorCode(obj) {

    if ($(obj) == null)
        return "";
    var codes = "";
    var array = $(obj).options;
    for (var i = 0; i < array.length; i++) {
        codes += array[i].value + "/";
    }
    if (codes.length > 0)
        codes = codes.substr(0, codes.length - 1);

    return codes;
}

//添加故障代码
//参数：listBox,故障代码
function LoadErrorCode(obj, codes) {
    if (obj != null && $(obj) != null && codes != "") {
        var array = codes.split('/');
        for (var i = 0; i < array.length; i++) {
            if (array[i] != "") {
                var op = new Option(array[i], array[i]);
                $(obj).options.add(op);
            }
        }
    }
} 
/////////////////////////////////////////////////////////////////////原故障代码javascript
 

//获得时间控件的时间
function GetTimeControlValue(control)
{
  if($("txt"+control+"_cpDate")==null)
   return null;
  var date=$("txt"+control+"_cpDate").value;
  if(date!="")
  {
    var  hour=$(control+"_ddlHour").value;
    var  min=$(control+"_ddlMinute").value;
    var time=new Date(Date.parse((date+" "+hour+":"+min).replace(/-/g,"/")));
    return time;
  }
  else
    return null;
}

//点击某个checkbox使某个HTML区块内的checkbox的全部选择和撤销
function  SelectAll(oBox)
 {
      var elements=$t("input",$(oBox));
      for(var i=0;i<elements.length;i++)
      {
        if(elements[i].type=="checkbox")
        {
            if(elements[i].id!=event.srcElement.id)
            {
              elements[i].checked=event.srcElement.checked;
              checkChange(elements[i]);
            }
          }
      }
 }
 
 //textBox整数输入的验证,如果不是数据清空textBox
 function CheckInt(txtBox)
 {
     if($(txtBox)!=null&& $(txtBox).value!="")
     {
       var numFormat=/^\d*$/;
       var text=$(txtBox).value;
       if(!numFormat.test(text))
       { 
           $(txtBox).value=text.substr(0,text.length-1);
           CheckInt(txtBox);
       }
     }
 }
 
 //手机格式验证
 function CheckMobile(value)
 {
   if(value!="")
   {
     if(!mobileFomat.test(value))
     {
        return false;
     }
   }  
   return true;
 }
 
  //电话格式验证
 function CheckPhone(value)
 {
   if(value!="")
   {
     if(! telFomat.test(value))
     {
        return false;
     }
   }  
   return true;

}
 
 //字符转换为半角 字符
function GetHalfWidthString(str) {
    var result = '';
    for (i = 0; i < str.length; i++) {
        code = str.charCodeAt(i);             //获取当前字符的unicode编码
        if (code >= 65281 && code <= 65373)   //unicode编码范围是所有的英文字母以及各种字符
        {
            result += String.fromCharCode(str.charCodeAt(i) - 65248);    //把全角字符的unicode编码转换为对应半角字符的unicode码
        }
        else if (code == 12288)                                      //空格
        {
            result += String.fromCharCode(str.charCodeAt(i) - 12288 + 32); //半角空格
        } else {
            result += str.charAt(i);                                     //原字符返回
        }
    }
    return result;
}

//打印设备主信息（不包含合同和kop信息）
function printEquipmentMainInfo() {

    var bdhtml = window.document.body.innerHTML;

    var sprnstr = "<!--startprint-->"; //开始标记
    var eprnstr = "<!--endprint-->"; //结束标记
    var noPrintStart1 = "<!--startNoPrint1-->"; //合同开始标记
    var noPrintend1 = "<!--endNoPrint1-->"; //合同结束标记
    var noPrintStart2 = "<!--startNoPrint2-->"; //kop开始标记
    var noPrintend2 = "<!--endNoprint2-->"; //kop结束标记
    var nosIndex1 = bdhtml.indexOf(noPrintStart1);
    var noeIndex1 = bdhtml.indexOf(noPrintend1);
    var nosIndex2 = bdhtml.indexOf(noPrintStart2);
    var noeIndex2 = bdhtml.indexOf(noPrintend2);

    //保存打印时不需要的数据 
    var beforePrnHtml = bdhtml.substr(0, bdhtml.indexOf(sprnstr) + 17);
    var afterPrnHtml = bdhtml.substr(bdhtml.indexOf(eprnstr));
    var contractHtml = bdhtml.substr(nosIndex1 + 20, noeIndex1 - nosIndex1 - 20); //合同信息
    var kopHtml = bdhtml.substr(nosIndex2 + 20, noeIndex2 - nosIndex2 - 20); //Kop信息


    //提取欲打印的数据
    //<!--startprint--> 到 <!--startNoPrint1--> 之间的html
    var printHtml1 = bdhtml.substr(bdhtml.indexOf(sprnstr) + 17, nosIndex1 + 20 - bdhtml.indexOf(sprnstr) - 17);
    //<!--endNoPrint1--> 到 <!--startNoPrint2--> 之间的html
    var printHtml2 = bdhtml.substr(noeIndex1, nosIndex2 + 20 - noeIndex1);
    //<!--endNoPrint2--> 到 <!--endprint--> 之间的html
    var printHtml3 = bdhtml.substr(noeIndex2, bdhtml.indexOf(eprnstr) - noeIndex2);

    window.document.body.innerHTML = '<div class="right"><div class="mainBody">' + printHtml1 + printHtml2 + printHtml3 + "</div></div>";

    //打印
    window.print();

    //恢复页面，可再次打印
    window.document.body.innerHTML = beforePrnHtml + printHtml1 + contractHtml + printHtml2 + kopHtml + printHtml3 + afterPrnHtml;


}
 
 

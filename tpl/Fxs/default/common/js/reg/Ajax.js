function createXMLHttpRequest() {
    if (window.ActiveXObject) {
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
    else if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    }
}

function AjaxRequest(typeName, params, callBackFunc) {
    var xmlHttp = createXMLHttpRequest();
    var url = "../UserControls/SuggestTextBox/Ajax.aspx?TYPENAME=" + typeName + "&" + params;
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            if (xmlHttp.status == 200) { 
                callBackFunc(xmlHttp.responseText);
            }
        }
    }
    xmlHttp.send(null);
}


//---------------------------------------------页面Ajax-------------------------------------- 脚本
function AjaxRequestMethod(methodName, params, callBackFunc) {
    var xmlHttp = createXMLHttpRequest();
    var url = "../WorkflowPage/Ajax.aspx?METHODNAME=" + methodName + "&" + params;
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            if (xmlHttp.status == 200) {
                callBackFunc(xmlHttp.responseText);
            }
        }
    }
    xmlHttp.send(null);
}

//---------------------------------------------SuggestTextBox2 -------------------------------------- 脚本
function AjaxRequestXml(ctrID,params) {
    var xmlHttp = createXMLHttpRequest();
    if (xmlHttp != null) {
         if(event.keyCode!=13&&event.keyCode!=40&&event.keyCode!=38)
         {
            var url = "/UserControls/SuggestTextBox/Ajax.aspx?TYPENAME=getsuggest&name=" + escape($(ctrID+"_txtName").value)+"&"+params;
            xmlHttp.open("GET", url, true);
            xmlHttp.onreadystatechange = function() {
                if (xmlHttp.readyState == 4) {
                     setMenuVisible(false,ctrID);
                    if (xmlHttp.status == 200) { 
                       processRequest(xmlHttp.responseXML,ctrID);
                    }
                }
            }
            xmlHttp.send(null);
        }
    }
}

 function setMenuVisible(isVisible,ctrID)
{
    if(isVisible == true)
    {
        document.getElementById(ctrID+"_divmenu").style.display = "";
        var e=document.getElementById(ctrID+"_txtName");
        var menu= $(ctrID+"_divmenu"); 
        var XY = getCoords(e);
        if (menu.offsetHeight > 400) menu.style.height = '400px';
        if (pagesize == null) pagesize = getPageSize();
            XY.y += (e.offsetHeight + 2);    
        if (XY.x + menu.offsetWidth > pagesize[4]) {
            XY.x -= Math.abs(e.offsetWidth - menu.offsetWidth);
        }
        menu.style.left = XY.x + 'px';
        menu.style.top = XY.y + 'px';  
        
        //隐藏下拉框，解决IE6中被下拉框遮住问题
        SetIE6DropDownListVisible(false);         
    }
    else
    {
       document.getElementById(ctrID+"_divmenu").style.display = "none";
       items=null;
       index=-1;
       SetIE6DropDownListVisible(true);
    }
}

 function SetIE6DropDownListVisible(isVisible)
 {
    if(isVisible==null)
     return;
     
     var Sys = {};
     var ua = navigator.userAgent.toLowerCase();
     if (window.ActiveXObject)
            Sys.ie = ua.match(/msie ([\d.]+)/)[1];
     if(Sys.ie=="6.0")
    {
         var elements=$t("select");
         if(elements.length>0)
         {
            for(var i=0;i<elements.length;i++)
            {
              elements[i].style.display=isVisible?"":"none";
            }
         }
    }      
 }

  function clearMenu(ctrID)
  {
         document.getElementById(ctrID+"_divmenu").innerHTML = "&nbsp;";    
         //position:absolute;
  }
  
function processRequest(responseXML,ctrID)
{
    if(document.activeElement.id!=ctrID+"_txtName")
    {
       setMenuVisible(false, ctrID);
       return;
    }
    var names = responseXML.getElementsByTagName("Items")[0].selectNodes("Name");
    var ids= responseXML.getElementsByTagName("Items")[0].selectNodes("id");
    var loginIds= responseXML.getElementsByTagName("Items")[0].selectNodes("LOGINID");
    clearMenu(ctrID);
    $(ctrID + "_hidValue").value = "";
    if (names.length > 0) {
        var newDivHtml = "";
        for (var i = 0; i < names.length; i++) {
         ids[i].text=ids[i].text.replace('\\','\\\\');
            //newDivHtml += "<div style=\"width:100%;  align:center; background-color:White; border-right: #cccccc 0px solid;z-index:2;border-left: #cccccc 0px solid;padding-left:5px;pdding-right:3px;display:\"inline\";\" onMouseOver=\"this.style.background ='#ddefff'\" onMouseOut=\"this.style.background ='#FFFFFF'\" onclick=\"fillSuggest('"+names[i].text+"')\"><nobr>"+names[i].text+"</nobr></div>";
             var displayName= names[i].text;
            if(loginIds!=null&&loginIds.length>0)
            {
              var index= loginIds[i].text.indexOf("\\");
              if(index>=0)
              {
                  loginIds[i].text= loginIds[i].text.substr(index+1,loginIds[i].text.length-index) //清楚域名
              }
              displayName+= "_"+loginIds[i].text;
            }
            newDivHtml += "<div style=\"width:100%; align:center; background-color:White; padding-left:5px border-right: #cccccc 0px solid;z-index:2; border-left: #cccccc 0px solid;display:\"inline\";\" onMouseOver=\"this.style.background ='#ddefff'\" onMouseOut=\"this.style.background ='#FFFFFF'\" onclick=\"fillSuggest('" + names[i].text + "','" + ids[i].text + "','" + ctrID + "')\" title=\""+displayName+"\">" +displayName + "</div>";
        } 
        document.getElementById(ctrID + "_divmenu").innerHTML = newDivHtml;  
        if (names.length > 10) {
            $(ctrID + "_divmenu").style.height = "250px";
        }
        else {
            $(ctrID + "_divmenu").style.height = "auto";
        }
        
        setMenuVisible(true, ctrID);
    }                      
}

function fillSuggest(name,id,ctrID)
{
    
    document.getElementById(ctrID+"_txtName").value = name;
    $(ctrID+"_hidValue").value=id;
    setMenuVisible(false, ctrID);
    
    //如果页面定义了SuggestTextBox2_SelectFunc函数，则去执行该函数
    if (typeof (SuggestTextBox2_SelectFunc) != 'undefined') {
        SuggestTextBox2_SelectFunc(ctrID);
    }
}
function onBlur(ctrID,params)
{
  // setTimeout("checkValidate('"+ctrID+"')",250); 
   var inMenu=false;
   var menu=$(ctrID + "_divmenu");
 
   if(menu.style.display!="none")
   {
      var pX=Number(event.clientX + document.body.scrollLeft);
      var pY=Number(event.clientY + document.body.scrollLeft);
      var mX1=Number(menu.style.left.toString().substr(0,menu.style.left.toString().length-2));
      var mX2=mX1+Number( menu.offsetWidth);
      var mY1=Number(menu.style.top.toString().substr(0,menu.style.top.toString().length-2));
      var mY2=mY1+Number(menu.offsetHeight);
      if(pX>mX1&&pX<mX2
           &&pY>mY1&&pY<mY2)
           {
              inMenu=true;//鼠标在下拉框内
           }
   }
   if(!inMenu)
   {
    
      checkValidate(ctrID,params);
  } 
}

function checkValidate(ctrID,params)
{
  if($(ctrID + "_hidValue").value=="")//check the name is validate?
   {
     var array=$t("div",$(ctrID + "_divmenu")); 
     var count=0;
     var name=$(ctrID+'_txtName').value;
     if(array.length!=0)
     {
         var resultItem=null;
         for(var i=0;i<array.length;i++)
         {
              if(array[i].onclick.toString().indexOf("'"+name+"'")>0)
              {
                 count++;
                 resultItem=array[i];  
              }
         }
         if(count==1)
         {
            resultItem.click();
         }
         else if(count>1)//有重名的情况存在
         {
           ShowTopAlert("提示信息","有重名不能确定具体人员,请从下拉列表选择！");  
           return;
         }
     }
     else //服务端查询
       {
           var xmlHttp = createXMLHttpRequest();
           if (xmlHttp != null) {
           
               var url = "/UserControls/SuggestTextBox/Ajax.aspx?TYPENAME=getsuggest&name=" + escape(name)+"&"+params+"&exact=true";
                xmlHttp.open("GET", url, true);
                xmlHttp.onreadystatechange = function() {
                    if (xmlHttp.readyState == 4) {
                        if (xmlHttp.status == 200) { 
                           processName(xmlHttp.responseXML,ctrID);
                        }
                    }
               }
                xmlHttp.send(null);
               
            }    
       }
   }
   setMenuVisible(false,ctrID);
}

function processName(responseXML,ctrID)
{
   
    var ids= responseXML.getElementsByTagName("Items")[0].selectNodes("id");
    var count=ids.length;
    if(count>1)
    {
        processRequest(responseXML,ctrID);
        ShowTopAlert("提示信息","有重名不能确定具体人员,请从下拉列表选择！"); 
    }
    else
    if(count==1)
    {
        $(ctrID + "_hidValue").value = ids[0].text;
        
        //如果页面定义了SuggestTextBox2_SelectFunc函数，则去执行该函数
        if (typeof (SuggestTextBox2_SelectFunc) != 'undefined') {
            SuggestTextBox2_SelectFunc(ctrID);
        }
    }
}
 function Value_ClientValidate(source, args)//vlaue 的客户端验证
    {
    var obj =  $(source.id.substr(0,source.id.length-8)+"txtName");
        if (obj.value==""||args.Value.length>0 )
            args.IsValid = true;
        else
     
            args.IsValid = false;

    }
    
    
// 上下键选择
var index=-1;
var items=null;
function SwitchItem(ctrID)
{
   key=event.keyCode; 
   if((key==32||key==13||key==38||key==40)&&$(ctrID + "_divmenu").style.display!="none")
   { 
     
      if(items==null)
        items=$t("div",document.getElementById(ctrID + "_divmenu"));
       if(items.length!=0)
       {
          if(key==40&&index<items.length-1)
          {
             if(index!=-1)
                 items[index].style.background ='#FFFFFF';
             items[index+1].style.background='#ddefff';
             index=index+1;
          }
          else if(key==38&&index>0)
          {
             items[index].style.background ='#FFFFFF';
             items[index-1].style.background='#ddefff';
             index=index-1;
          }
          else if((key==13||key==32)&&index>=0)
          {
              items[index].click(); 
              event.returnValue=false; 
          }
       }
       if(key==13)
         event.returnValue=false;
   }
}  
//--------------------------------------------- end SuggestTextBox2 ----------------------------------------------



//-------------------------------------------- start 保外订购备件check脚本-----------------------------------------
function CheckParts(txtID)
{
  if($(txtID)==null)
   return;
   var pre=$(txtID).id.substr(0,$(txtID).id.length-5);
   if($(txtID).value!="")
   {
      //判断是否已经添加过
//      for(var i=0;i<partsArray.length;i++)
//      {
//         if(partsArray[i]==$(txtID).value)
//         {
//             ShowTopAlert("提示信息","备件已经存在！");
//              clearInput(pre);
//             return;
//         }
//      }
       if(hasExist($(txtID).value))
       {
          ShowTopAlert("提示信息","备件已经存在！");
          clearInput(pre,false);
          return;
       }
      AjaxRequestPartsCheck(txtID);
  }
  else
  {
      clearInput(pre);
  }
}

function AjaxRequestPartsCheck(txtID) {
    var xmlHttp = createXMLHttpRequest();
    var url = "../PartsMan/OuterGuaranteeOrderApply.aspx?METHODNAME=GetlatestPartsInfo&Value="+ escape($(txtID).value);
    xmlHttp.open("GET", url, true);
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4) {
            if (xmlHttp.status == 200) {
               ShowPartsName(xmlHttp.responseXML,txtID);
            }
        }
    }
    xmlHttp.send(null);
}

var partsArray= new Array();//记录列表中已经存在的备件ID

//备件不存在
function ShowPartsName(responseXML,txtID)
{

    var names = responseXML.getElementsByTagName("Items")[0].selectNodes("Name");
    var ids= responseXML.getElementsByTagName("Items")[0].selectNodes("id"); 
    var jdes=responseXML.getElementsByTagName("Items")[0].selectNodes("JDE"); 
    var isInrange=responseXML.getElementsByTagName("Items")[0].selectNodes("isInRange"); 
    var preID=$(txtID).id.substr(0,$(txtID).id.length-5);
    if(ids.length>0)
    {
      if(jdes[0].text!="1")
      {  
          ShowTopAlert("提示信息","备件不包含JDE定义！");  
          clearInput(preID);
          return ;
      }
      if(isInrange[0].text!="1")
      {  
          ShowTopAlert("提示信息","备件不在定购的产品范围内！");  
         clearInput(preID);
          return;
      }
      
      if($(txtID).value!=ids[0].text)
      {
         if(hasExist(ids[0].text))
         {
              ShowTopAlert("提示信息","有新的可替换备件:"+ids[0].text+",且新的备件已经存在！");  
              clearInput(preID,false); 
              return;
         }
         ShowTopAlert("提示信息","有新的可替换备件:"+ids[0].text+"！");        
         $(txtID).value=ids[0].text;
      }
      $(preID+"hidName").value=names[0].text;
      $(preID+"txtName").value=names[0].text;
      $(preID+"txtNum").value='1';//默认一个备件
      partsArray.push(ids[0].text);
    }
    else
    {
       ShowTopAlert("提示信息","备件不存在！");
      //清空数据
     clearInput(preID);
    }
}

function clearInput(preID,clearHas)
{
      if(clearHas==null||clearHas)
      {
          for(var i=0;i<partsArray.length;i++)
          {
             if(partsArray[i]=$(preID+"txtID").value)
             {
                partsArray[i]="";
                break;
             }
          }
      }
      $(preID+"txtID").value="";
      $(preID+"txtName").value="";
      $(preID+"hidName").value="";
      $(preID+"txtNum").value="";
}

function hasExist(value)
{
     //判断是否已经添加过
      for(var i=0;i<partsArray.length;i++)
      {
         if(partsArray[i]==value)
         {          
             return true;
         }
      }
      return false;
}

//-------------------------------------------- end 保外订购备件check脚本-----------------------------------------
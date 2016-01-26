
function HashMap()
{
    return  {
              size : 0,
              put : function(key,value)
                {
                    this[key] = value,this.size++
                },
              get : function(key)
                {
                    return this[key]
                },
              contains : function(key)
                {
                    return this.get(key) == null?false:true
                },
              remove : function(key)
              {
                delete this[key],this.size--
              },
              indexOf: function(index)
              {
                  var keys = new Array();
                  var i = 0;
                  for (var key in this) {
                      if(i == index && !$.isFunction(this[key]) && key != 'size')
                      {
                          return this[key];
                      }
                      if(!$.isFunction(this[key]) && key != 'size')
                      {
                        i++;
                      }
                  }
              },
              getKeyByIndex: function(index)
              {
                  var keys = new Array();
                  var i = 0;
                  for (var key in this) 
                  {
                      if(i == index && !$.isFunction(this[key]) && key != 'size')
                      {
                          return key;
                      }
                      if(!$.isFunction(this[key]) && key != 'size')
                      {
                        i++;
                      }
                  }
              }
          };
}

 /*
 map.put("A","1");
 map.put("B","2");
 map.put("A","5");
 map.put("C","3");
 map.put("A","4");
 */
 
 /*
 alert(map.containsKey("XX"));
 alert(map.size());
 alert(map.get("A"));
 alert(map.get("XX"));
 map.remove("A");
 alert(map.size());
 alert(map.get("A"));
 */
 
 /** 同时也可以把对象作为 Key **/
 /*
 var arrayKey = new Array("1","2","3","4");
 var arrayValue = new Array("A","B","C","D");
 map.put(arrayKey,arrayValue);
 var value = map.get(arrayKey);
 for(var i = 0 ; i < value.length ; i++)
 {
     //alert(value[i]);
 }
 */
 /** 把对象做为Key时 ，自动调用了该对象的 toString() 方法 其实最终还是以String对象为Key**/
 
 /** 如果是自定义对象 那自己得重写 toString() 方法  **/
 
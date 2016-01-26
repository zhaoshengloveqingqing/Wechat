<?php
/**
 *项目公共配置
 **/
return array(
	'LOAD_EXT_CONFIG' 		=> 'db,info,safe,upfile,cache,route,alipay,userfunc,web_template,packages,car,manage_info,member_default_set,rest_api,sms_template,goods_specs,face',		
	'APP_AUTOLOAD_PATH'     =>'@.ORG,@.LogicModel',
	'OUTPUT_ENCODE'         =>  true, 			//页面压缩输出
	'PAGE_NUM'				=> 15,
	/*Cookie配置*/
	'COOKIE_PATH'           => '/',     		// Cookie路径
    'COOKIE_PREFIX'         => '',      		// Cookie前缀 避免冲突
	/*定义模版标签*/
	'TMPL_L_DELIM'   		=>'{lingzh:',			//模板引擎普通标签开始标记
	'TMPL_R_DELIM'			=>'}',				//模板引擎普通标签结束标记
);
?>
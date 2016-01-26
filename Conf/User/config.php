<?php
return array(
	'TMPL_FILE_DEPR'=>'_',
	'DEFAULT_THEME'=>'default',
	'UPLOAD_ADDR'=>'POCO.CN',
	/*定义模版标签*/
    'TMPL_L_DELIM'           =>'{lingzh:',            //模板引擎普通标签开始标记
    'TMPL_R_DELIM'            =>'}', 
    
    'LZH_NEWS_CATEGORIES' =>  array(    //公司发布的新闻的分类信息，修改此处的同时请修改Home/onfig.php中的LZH_NEWS_CATEGORIES条目
    array('id'=>0,'name'=>'行业动态'),
    array('id'=>1,'name'=>'系统更新/优化'),
    array('id'=>2,'name'=>'企业动态'),
    array('id'=>3,'name'=>'最新动态'),
    ),

);
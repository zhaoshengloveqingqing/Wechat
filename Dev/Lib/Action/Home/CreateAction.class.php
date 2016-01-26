<?php
class CreateAction extends BaseAction{
    public function index(){
        $gid = session('gid');
        $uid = session('uid');
        $token = session('token');
        if(empty($gid) && empty($uid)){
            exit("请登录后操作!");
        }

        $group = M('User_group')->field('create_card_num')->where(array('id'=>session('gid')))->find();
 
         $create_tb="CREATE TABLE IF NOT EXISTS `tp_member_card_sign` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `token` varchar(50) NOT NULL,
                  `wecha_id` varchar(50) NOT NULL COMMENT '',
                  `sign_time` int(11) NOT NULL COMMENT '',
                  `is_sign` int(11) NOT NULL COMMENT '',
                  `expense` int(11) NOT NULL COMMENT '',
                  `score_type` int(11) NOT NULL COMMENT ' ',
                  `sell_expense` int(11) NOT NULL COMMENT '',
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT=' ' AUTO_INCREMENT=25;";   
        
          $is_ok = mysql_query($create_tb) or die ("创建表失败。");   
          
           if($is_ok){
                 echo "tp_member_card_sign 表创建成功。<br />";
           }
       
      
        $add_fields = "ALTER TABLE  `tp_wxuser` 
        ADD  `allcardnum` INT NOT NULL AFTER  `tplcontentname` ,
        ADD  `yetcardnum` INT NOT NULL AFTER  `allcardnum` ,
        ADD  `cardisok` INT NOT NULL AFTER  `yetcardnum` ,
        ADD  `totalcardnum` INT NOT NULL AFTER  `cardisok` ;";

        // ALTER TABLE  `tp_wxuser` ADD  `sdf` INT NOT NULL DEFAULT  '10' AFTER  `totalcardnum` ; 

        $is_add = mysql_query($add_fields);
        if($is_add){
            echo "tp_wxuser 添加字段成功!<br />";
        }
        
       $c_ok =  mysql_query("update `tp_wxuser` set `allcardnum`='{$group['create_card_num']}' where `uid`='{$uid}'  and `token`='{$token}'");
       if($c_ok){
        echo "ok,自动创建完成.<br />";
       }


$tp_host = "CREATE TABLE IF NOT EXISTS `tp_host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `keyword` varchar(50) NOT NULL COMMENT '',
  `title` varchar(50) NOT NULL COMMENT '',
  `address` varchar(50) NOT NULL COMMENT '',
  `tel` varchar(13) NOT NULL COMMENT '',
  `tel2` varchar(13) NOT NULL COMMENT '',
  `ppicurl` varchar(250) NOT NULL COMMENT '',
  `headpic` varchar(250) NOT NULL COMMENT '',
  `name` varchar(50) NOT NULL COMMENT '',
  `sort` int(11) NOT NULL COMMENT '',
  `picurl` varchar(100) NOT NULL COMMENT '',
  `url` varchar(50) NOT NULL COMMENT '',
  `info` text NOT NULL COMMENT '',
  `info2` text NOT NULL COMMENT '',
  `creattime` int(11) NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='' AUTO_INCREMENT=5 ;";
 $ok_host = mysql_query($tp_host) or die ("创建表失败。");
 if($ok_host){
    echo "tp_host 创建成功.<br />";
 }  


$tp_host_list_add = "CREATE TABLE IF NOT EXISTS `tp_host_list_add` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hid` int(11) NOT NULL COMMENT '',
  `token` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT '',
  `typeinfo` varchar(100) NOT NULL COMMENT '',
  `price` decimal(10,2) NOT NULL COMMENT '',
  `yhprice` decimal(10,2) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '',
  `picurl` varchar(110) NOT NULL COMMENT '',
  `url` varchar(100) NOT NULL COMMENT '',
  `info` text NOT NULL COMMENT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='' AUTO_INCREMENT=7 ;";

 $ok_list = mysql_query($tp_host_list_add) or die ("创建表失败。");
 if($ok_list){
    echo "tp_host_list_add 创建成功.<br />";
 } 

$tp_host_order = "CREATE TABLE IF NOT EXISTS `tp_host_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `book_people` varchar(50) NOT NULL COMMENT '',
  `tel` varchar(13) NOT NULL COMMENT '',
  `check_in` int(11) NOT NULL COMMENT '',
  `check_out` int(11) NOT NULL COMMENT '',
  `room_type` varchar(50) NOT NULL COMMENT '',
  `book_time` int(11) NOT NULL COMMENT '',
  `book_num` int(11) NOT NULL COMMENT '',
  `price` decimal(10,2) NOT NULL COMMENT ' ',
  `order_status` int(11) NOT NULL COMMENT ' ',
  `hid` int(11) NOT NULL COMMENT '',
  `remarks` varchar(250) NOT NULL COMMENT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='' AUTO_INCREMENT=6 ;";
 $ok_order = mysql_query($tp_host_order) or die ("创建表失败。");
 if($ok_order){
    echo "tp_host_order 创建成功.<br />";
 } 
$tp_other="	
CREATE TABLE IF NOT EXISTS `tp_other` (
  `id` int(11) NOT NULL auto_increment,
  `token` varchar(60) NOT NULL,
  `keyword` varchar(60) NOT NULL,
  `info` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
";
 $ok_order = mysql_query($tp_other) or die ("创建表失败。");
 if($ok_order){
    echo "tp_other 创建成功.<br />";
 } 
@unlink('PigCms/Lib/Action/Home/Create.class.php');


}

}
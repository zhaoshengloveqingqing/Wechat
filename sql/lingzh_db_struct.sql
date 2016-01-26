-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 06 月 06 日 04:19
-- 服务器版本: 5.5.31
-- PHP 版本: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `lingzh`
--
CREATE DATABASE IF NOT EXISTS `lingzh` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `lingzh`;

-- --------------------------------------------------------

--
-- 表的结构 `tp_access`
--
-- 创建时间: 2013 年 10 月 15 日 02:39
-- 最后更新: 2014 年 04 月 03 日 14:56
-- 最后检查: 2013 年 11 月 06 日 10:20
--

CREATE TABLE IF NOT EXISTS `tp_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_activity`
--
-- 创建时间: 2013 年 12 月 04 日 10:22
--

CREATE TABLE IF NOT EXISTS `tp_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT NULL,
  `author` varchar(45) DEFAULT NULL,
  `content` text,
  `pass` varchar(45) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `date` varchar(45) DEFAULT NULL,
  `hots` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_activity_join`
--
-- 创建时间: 2013 年 12 月 04 日 10:22
--

CREATE TABLE IF NOT EXISTS `tp_activity_join` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `act_id` varchar(45) DEFAULT NULL,
  `nick` varchar(45) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `date` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_adma`
--
-- 创建时间: 2013 年 10 月 15 日 02:39
-- 最后更新: 2014 年 03 月 03 日 07:08
--

CREATE TABLE IF NOT EXISTS `tp_adma` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `token` varchar(60) NOT NULL,
  `url` varchar(100) NOT NULL,
  `copyright` varchar(50) NOT NULL,
  `info` varchar(120) NOT NULL,
  `title` varchar(60) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='diy 宣传页' AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_android_crash_report`
--
-- 创建时间: 2014 年 05 月 29 日 06:52
--

CREATE TABLE IF NOT EXISTS `tp_android_crash_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=217 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_api`
--
-- 创建时间: 2013 年 10 月 15 日 02:39
-- 最后更新: 2013 年 10 月 15 日 02:39
--

CREATE TABLE IF NOT EXISTS `tp_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `token` varchar(60) NOT NULL,
  `url` varchar(100) NOT NULL,
  `number` tinyint(1) NOT NULL,
  `order` tinyint(1) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='第三方api接口表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_areply`
--
-- 创建时间: 2013 年 10 月 24 日 13:41
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_areply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) DEFAULT NULL,
  `uid` int(11) NOT NULL,
  `uname` varchar(90) DEFAULT NULL,
  `createtime` varchar(13) NOT NULL,
  `updatetime` varchar(13) NOT NULL,
  `token` char(30) NOT NULL,
  `cid` int(11) DEFAULT NULL COMMENT '内容id',
  `ctype` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=695 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_article`
--
-- 创建时间: 2014 年 05 月 23 日 14:03
-- 最后更新: 2014 年 06 月 06 日 02:11
-- 最后检查: 2014 年 05 月 23 日 14:03
--

CREATE TABLE IF NOT EXISTS `tp_article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `token` varchar(60) NOT NULL,
  `status` tinyint(3) NOT NULL COMMENT '1是显示 0是不显示',
  `title` varchar(90) NOT NULL,
  `content` text NOT NULL,
  `updatetime` varchar(13) NOT NULL,
  `createtime` varchar(13) NOT NULL,
  `c_id` int(11) NOT NULL,
  `c_name` varchar(60) NOT NULL,
  `click` int(11) NOT NULL DEFAULT '0',
  `pic` varchar(255) DEFAULT NULL,
  `tmpl` varchar(60) NOT NULL DEFAULT 'content_pic',
  `sorts` int(11) unsigned DEFAULT '1',
  `linktype` varchar(25) NOT NULL DEFAULT 'articles',
  `link_param_l1` varchar(512) DEFAULT NULL,
  `link_param_l2` varchar(512) DEFAULT NULL,
  `display_title_time` tinyint(4) DEFAULT '1' COMMENT '是否显示标题和时间',
  `display_pic` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否在文章页中显示封面图片，1是显示，0是不显示',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8831 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_audit_agent_balance`
--
-- 创建时间: 2014 年 03 月 27 日 08:27
--

CREATE TABLE IF NOT EXISTS `tp_audit_agent_balance` (
  `audit_id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `agent_name` varchar(127) COLLATE utf8_general_ci DEFAULT NULL,
  `admin_name` varchar(127) COLLATE utf8_general_ci NOT NULL,
  `balance_after` double NOT NULL,
  `balance_before` double DEFAULT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`audit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_audit_agent_purchase`
--
-- 创建时间: 2014 年 02 月 20 日 12:04
-- 最后更新: 2014 年 06 月 06 日 01:19
--

CREATE TABLE IF NOT EXISTS `tp_audit_agent_purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent` int(11) NOT NULL COMMENT '代理商ID',
  `package` int(11) NOT NULL COMMENT '套餐',
  `duration` int(11) NOT NULL COMMENT '套餐周期',
  `count` int(11) NOT NULL COMMENT '套餐数',
  `charge` int(11) NOT NULL COMMENT '预扣款',
  `pre_balance` double NOT NULL COMMENT '扣款钱余额',
  `start_time` int(11) NOT NULL COMMENT '购买套餐开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '购买成功时间：可标示是否成功',
  `invitecode_batch` int(11) DEFAULT NULL COMMENT '充值码批次号：可标示创建充值码是否成功',
  `post_balance` double DEFAULT NULL COMMENT '购买后余额：标示是否扣款成功',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='追踪代理商自行购买充值码的过程' AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_audit_merchant_purchase`
--
-- 创建时间: 2014 年 02 月 27 日 08:37
-- 最后更新: 2014 年 05 月 16 日 05:59
--

CREATE TABLE IF NOT EXISTS `tp_audit_merchant_purchase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trade_no` varchar(64) NOT NULL COMMENT '商家购买的唯一订单号',
  `user_id` int(11) NOT NULL COMMENT '前台用户ID',
  `submit_time` int(11) NOT NULL COMMENT '订单提交时间',
  `package_type` tinyint(2) NOT NULL COMMENT '1:功能套餐；2:流量包',
  `package_id` int(11) NOT NULL COMMENT '套餐ID',
  `package_count` int(11) NOT NULL COMMENT '套餐数量',
  `package_duration` int(11) DEFAULT NULL COMMENT '套餐周期',
  `package_fee` double NOT NULL COMMENT '套餐总价，不包含发票税',
  `total_fee` double NOT NULL COMMENT '商户应付总价，包含发票税',
  `need_invoice` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:不需要；1:需要开发票',
  `invoice_crp` varchar(256) DEFAULT NULL COMMENT '发票抬头',
  `invoice_name` varchar(256) DEFAULT NULL COMMENT '发票收件人',
  `invoice_phone` varchar(256) DEFAULT NULL COMMENT '发票收件人电话',
  `invoice_address` varchar(256) DEFAULT NULL COMMENT '发票收件人地址',
  `invoice_zipcode` varchar(64) DEFAULT NULL COMMENT '发票收件人邮编',
  `status` int(11) NOT NULL COMMENT '订单状态：1:提交；2:支付成功；3:支付失败；4:验证请求来源失败；5:验证请求来源成功；6:开通套餐成功；7:开通套餐失败',
  `ali_trade_no` varchar(64) DEFAULT NULL COMMENT '支付宝订单号',
  `ali_trade_status` varchar(64) DEFAULT NULL COMMENT '支付宝返回的状态码',
  `ali_notify_id` varchar(256) DEFAULT NULL COMMENT '用以验证是否请求来自支付宝',
  `ali_notify_type` varchar(64) DEFAULT NULL COMMENT '支付宝通知类型',
  `ali_total_fee` double DEFAULT NULL COMMENT '支付宝返回的总额',
  `ali_is_success` varchar(16) DEFAULT NULL COMMENT '支付宝接口调用状态',
  `ali_buyer_email` varchar(128) DEFAULT NULL COMMENT '商家支付宝email',
  `ali_buyer_id` varchar(64) DEFAULT NULL COMMENT '商家支付宝ID',
  `handler` int(2) DEFAULT NULL COMMENT '0:return_url; 1:notify',
  `finish_time` int(11) NOT NULL COMMENT '订单完成时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_trade_no` (`trade_no`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='商户购买功能套餐和流量包的审计表；通过该表可以获得商户购买记录，发票索取记录，以及充值过程中的错误纠察' AUTO_INCREMENT=145 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_autobind`
--
-- 创建时间: 2014 年 01 月 08 日 05:19
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_autobind` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL COMMENT '微信网站登录用户名',
  `password` varchar(60) NOT NULL COMMENT '微信网站登录密码',
  `start_time` int(11) NOT NULL COMMENT '一键绑定开始时间',
  `step` int(11) NOT NULL DEFAULT '0' COMMENT '当前步骤，0表示未开始',
  `step1_start_time` int(11) DEFAULT NULL,
  `step1_end_time` int(11) DEFAULT NULL,
  `step2_start_time` int(11) DEFAULT NULL,
  `step2_end_time` int(11) DEFAULT NULL,
  `step3_start_time` int(11) DEFAULT NULL,
  `step3_end_time` int(11) DEFAULT NULL,
  `step4_start_time` int(11) DEFAULT NULL,
  `step4_end_time` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:无效;1:有效',
  `public_account_name` varchar(255) DEFAULT NULL COMMENT '公众号名称',
  `public_account_raw_id` varchar(255) DEFAULT NULL COMMENT '原始ID',
  `public_account_wxid` varchar(255) DEFAULT NULL COMMENT '微信号',
  `public_account_type` varchar(64) DEFAULT NULL COMMENT '微信公众号类型',
  `appid` varchar(255) DEFAULT NULL,
  `appsecret` varchar(255) DEFAULT NULL,
  `wxuser_id` int(11) DEFAULT NULL COMMENT '在wxuser表中的记录',
  `token` varchar(60) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username_index` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=966 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_category`
--
-- 创建时间: 2013 年 12 月 09 日 09:08
-- 最后更新: 2014 年 06 月 05 日 15:26
--

CREATE TABLE IF NOT EXISTS `tp_b2c_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL DEFAULT '0' COMMENT '0表示顶级分类',
  'type'   mediumint(4) NOT NULL DEFAULT '0' COMMENT '0表示产品列表，1表示有下级分类',
  `token` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL,
  `desc` varchar(500) NOT NULL DEFAULT '',
  `logo_url` varchar(100) NOT NULL DEFAULT '0',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1082 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_cfttrade`
--
-- 创建时间: 2014 年 03 月 29 日 06:41
-- 最后更新: 2014 年 05 月 25 日 03:57
--

CREATE TABLE IF NOT EXISTS `tp_b2c_cfttrade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wecha_id` varchar(127) NOT NULL,
  `token` varchar(127) NOT NULL,
  `order_sn` varchar(32) NOT NULL COMMENT '订单流水号',
  `partnerId` varchar(127) NOT NULL,
  `partnerkey` varchar(127) NOT NULL,
  `ver` varchar(32) NOT NULL,
  `charset` varchar(32) NOT NULL,
  `bank_type` varchar(32) NOT NULL,
  `desc` varchar(127) NOT NULL,
  `total_fee` int(11) NOT NULL COMMENT '交易总价单位为分',
  `fee_type` int(11) NOT NULL COMMENT '交易总价单位为分',
  `token_id` varchar(127) DEFAULT NULL COMMENT '财付通生成的token id',
  `create_time` int(11) NOT NULL COMMENT '交易创建时间',
  `trade_start_times` int(10) DEFAULT '0' COMMENT '交易尝试开始次数',
  `n_charset` varchar(16) DEFAULT NULL,
  `n_bank_type` varchar(16) DEFAULT NULL,
  `n_bank_billno` varchar(32) DEFAULT NULL,
  `n_pay_result` int(11) DEFAULT '-1' COMMENT '0:成功；其他失败',
  `n_pay_info` varchar(64) DEFAULT NULL,
  `n_purchase_alias` varchar(64) DEFAULT NULL,
  `n_bargainor_id` varchar(32) DEFAULT NULL,
  `n_transaction_id` varchar(32) DEFAULT NULL COMMENT '财付通交易号',
  `n_total_fee` int(11) DEFAULT NULL,
  `n_fee_type` int(11) DEFAULT NULL,
  `n_time_end` varchar(32) DEFAULT NULL,
  `trade_notify_timestamp` int(11) DEFAULT '0' COMMENT 'notify时间',
  `trade_notify_times` int(10) DEFAULT '0' COMMENT '交易notify次数',
  `trade_callback_timestamp` int(11) DEFAULT '0' COMMENT 'callback request时间',
  `trade_callback_times` int(10) DEFAULT '0' COMMENT 'callback调用次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`),
  KEY `token` (`token`),
  KEY `n_transaction_id` (`n_transaction_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财付通支付交易明细' AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_customer`
--
-- 创建时间: 2014 年 01 月 25 日 06:38
-- 最后更新: 2014 年 05 月 31 日 06:50
--

CREATE TABLE IF NOT EXISTS `tp_b2c_customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `wecha_id` varchar(60) NOT NULL,
  `wechaname` varchar(60) DEFAULT NULL,
  `truename` varchar(60) NOT NULL,
  `tel` varchar(11) NOT NULL,
  `province` VARCHAR(30) NULL COMMENT '省份',
  `city` VARCHAR(30) NULL COMMENT '城市',
  `area` VARCHAR(30) NULL COMMENT '区域',
  `address` varchar(255) NOT NULL,
  `zipcode` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  KEY `token` (`token`),
  KEY `wecha_id` (`wecha_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=135 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_display`
--
-- 创建时间: 2013 年 12 月 21 日 08:44
--

CREATE TABLE IF NOT EXISTS `tp_b2c_display` (
  `display` int(11) NOT NULL AUTO_INCREMENT,
  `tmpl_name` varchar(63) COLLATE utf8_general_ci NOT NULL,
  `token` varchar(127) COLLATE utf8_general_ci NOT NULL,
  `bg_pic_url` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  PRIMARY KEY (`display`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_logistics`
--
-- 创建时间: 2013 年 12 月 09 日 09:08
--

CREATE TABLE IF NOT EXISTS `tp_b2c_logistics` (
  `logistics_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL,
  `name` varchar(127) NOT NULL,
  `fee` float NOT NULL,
  `create_time` int(10) NOT NULL,
  `logistics_no` varchar(63) NOT NULL,
  `order_id` int(10) NOT NULL,
  PRIMARY KEY (`logistics_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_order`
--
-- 创建时间: 2014 年 05 月 16 日 09:08
-- 最后更新: 2014 年 05 月 31 日 15:19
-- 最后检查: 2014 年 05 月 16 日 09:08
--

CREATE TABLE IF NOT EXISTS `tp_b2c_order` (
  `order_id` int(10) NOT NULL AUTO_INCREMENT,
  `sn` varchar(64) NOT NULL,
  `token` varchar(50) NOT NULL DEFAULT '',
  `wecha_id` varchar(60) NOT NULL DEFAULT '',
  `info` varchar(300) NOT NULL DEFAULT '',
  `total` mediumint(4) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `truename` varchar(20) NOT NULL DEFAULT '',
  `tel` varchar(14) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `status` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '1是初始、2是已付款、3是已发货、4是取消',
  `payment` varchar(15) NOT NULL,
  `zipcode` varchar(15) DEFAULT '',
  `readed` tinyint(4) DEFAULT '0' COMMENT '1已读，0未读',
  `branch_id` int(11) DEFAULT NULL,
  `cmbProvince` varchar(20) NOT NULL,
  `cmbCity` varchar(20) NOT NULL,
  `cmbArea` varchar(20) NOT NULL,
  `fxs_id` int(32) NULL,
  `is_audited`  tinyint(1) NULL,
  `commission` int(20) NULL,
  `merchant_cancel` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_sn` (`sn`) COMMENT '订单号',
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=345 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_order_item`
--
-- 创建时间: 2014 年 05 月 16 日 09:09
-- 最后更新: 2014 年 05 月 31 日 15:19
-- 最后检查: 2014 年 05 月 16 日 09:09
--

CREATE TABLE IF NOT EXISTS `tp_b2c_order_item` (
  `item_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `count` mediumint(4) NOT NULL DEFAULT '0',
  `token` varchar(50) NOT NULL,
  `create_time` int(10) NOT NULL DEFAULT '0',
  `order_id` int(10) NOT NULL,
  `pic_url` varchar(255) DEFAULT '',
  `name` varchar(127) NOT NULL,
  `size_name` varchar(15) DEFAULT NULL,
  `color_name` varchar(15) DEFAULT NULL,
  `rebate` int(11) NULL,
  PRIMARY KEY (`item_id`),
  KEY `token` (`token`),
  KEY `item_goods_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=364 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_payment`
--
-- 创建时间: 2013 年 12 月 17 日 13:32
-- 最后更新: 2014 年 06 月 03 日 04:29
--

CREATE TABLE IF NOT EXISTS `tp_b2c_payment` (
  `payment_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(63) NOT NULL DEFAULT '',
  `pay_code` varchar(20) NOT NULL DEFAULT '',
  `pay_name` varchar(120) NOT NULL DEFAULT '',
  `pay_fee` varchar(10) NOT NULL DEFAULT '0',
  `pay_desc` text NOT NULL,
  `pay_order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pay_config` text NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_cod` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_online` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `branch_id` int(11) NOT NULL COMMENT '分店id',
  PRIMARY KEY (`payment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=115 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_product`
--
-- 创建时间: 2014 年 05 月 29 日 14:34
-- 最后更新: 2014 年 06 月 05 日 15:26
-- 最后检查: 2014 年 05 月 29 日 14:34
--

CREATE TABLE IF NOT EXISTS `tp_b2c_product` (
  `product_id` int(10) NOT NULL AUTO_INCREMENT,
  `category_id` mediumint(4) NOT NULL DEFAULT '0',
  `branch_id` int(4) NOT NULL DEFAULT '0',
  `name` varchar(150) NOT NULL DEFAULT '',
  `market_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shop_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` float NOT NULL DEFAULT '10',
  `status` tinyint(10) NOT NULL DEFAULT '0',
  `intro` text NOT NULL,
  `token` varchar(50) NOT NULL,
  `keyword` varchar(100) DEFAULT '',
  `sale_count` mediumint(4) NOT NULL DEFAULT '0',
  `logo_url` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `img_id` int(10) DEFAULT '0',
  `sn` varchar(64) DEFAULT '',
  `sort` mediumint(8) unsigned DEFAULT '0',
  `qrcode_pic_url` varchar(255) DEFAULT NULL,
  `size_set` varchar(500) NOT NULL DEFAULT '' COMMENT '商品尺寸php数组',
  `color_set` varchar(1240) NOT NULL DEFAULT '' COMMENT '商品颜色php数组',
  `inventory` int(11) NOT NULL DEFAULT '100' COMMENT '商品库存',
  `size_alias` varchar(10) NOT NULL DEFAULT '尺寸' COMMENT 'size_set这个字段前端显示的名字',
  `color_alias` varchar(10) NOT NULL DEFAULT '颜色' COMMENT 'color_set这个字段前端显示的名字',
  PRIMARY KEY (`product_id`),
  KEY `catid_id` (`category_id`),
  KEY `store_id` (`store_id`),
  KEY `token` (`token`),
  KEY `market_price` (`market_price`),
  KEY `shop_price` (`shop_price`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7636 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_product_spec`
--
-- 创建时间: 2014 年 06 月 11 日 06:44
--

CREATE TABLE IF NOT EXISTS `tp_b2c_product_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `size_id` int(11) NOT NULL DEFAULT '0',
  `color_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `inventory` int(11) NOT NULL DEFAULT '20',
  `size_name` varchar(15) NOT NULL DEFAULT '',
  `color_name` varchar(15) NOT NULL DEFAULT '',
  `status` int(4) NOT NULL DEFAULT '0' COMMENT '0正常，1删除',
  PRIMARY KEY (`id`),
  KEY `product_idx` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='某种尺寸及颜色的商品的库存' AUTO_INCREMENT=126 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_shop`
--
-- 创建时间: 2013 年 12 月 09 日 09:08
--

CREATE TABLE IF NOT EXISTS `tp_b2c_shop` (
  `shop_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `logo_url` varchar(255) NOT NULL,
  `keyword` varchar(63) NOT NULL,
  `name` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `telephone` varchar(31) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `create_time` int(10) NOT NULL,
  `update_time` int(10) NOT NULL,
  `fake_id` int(11) DEFAULT NULL,
  `alipay_pid` varchar(63) DEFAULT NULL,
  `alipay_key` varchar(127) DEFAULT NULL,
  `alipay_email` varchar(127) DEFAULT NULL,
  PRIMARY KEY (`shop_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=249 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_trade`
--
-- 创建时间: 2014 年 05 月 16 日 09:09
--

CREATE TABLE IF NOT EXISTS `tp_b2c_trade` (
  `trade_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(127) NOT NULL,
  `order_sn` varchar(32) NOT NULL,
  `subject` varchar(127) NOT NULL,
  `name` varchar(127) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `create_time` int(10) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `trade_no` varchar(127) DEFAULT '',
  `update_time` int(10) DEFAULT '0',
  `buyer_email` varchar(127) DEFAULT '',
  `buyer_id` varchar(127) DEFAULT '',
  `alipay_create_time` int(10) DEFAULT '0',
  `payment_time` int(10) DEFAULT '0',
  `refund_status` varchar(127) DEFAULT '',
  `refund_time` int(10) DEFAULT '0',
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`trade_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=135 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_wxtrade`
--
-- 创建时间: 2014 年 04 月 30 日 04:12
-- 最后更新: 2014 年 05 月 31 日 07:27
--

CREATE TABLE IF NOT EXISTS `tp_b2c_wxtrade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `wecha_id` varchar(127) NOT NULL,
  `token` varchar(127) NOT NULL,
  `order_sn` varchar(32) NOT NULL COMMENT '订单流水号',
  `appId` varchar(127) NOT NULL,
  `partnerId` varchar(127) NOT NULL,
  `timeStamp` varchar(32) NOT NULL,
  `nonceStr` varchar(32) NOT NULL,
  `paySign` varchar(256) NOT NULL,
  `bank_type` varchar(32) NOT NULL,
  `body` varchar(127) NOT NULL,
  `total_fee` int(11) NOT NULL COMMENT '交易总价单位为分',
  `transport_fee` int(11) NOT NULL COMMENT '运费价格单位为分',
  `product_fee` int(11) NOT NULL COMMENT '商品价格单位为分',
  `spbill_create_ip` varchar(32) NOT NULL,
  `create_time` int(11) NOT NULL COMMENT '交易创建时间',
  `trade_start_times` int(10) DEFAULT '0' COMMENT '交易尝试开始次数',
  `n_trade_mode` varchar(32) DEFAULT NULL COMMENT '交易模式,1 为即时到帐,其他保留',
  `n_trade_state` int(11) DEFAULT '-1' COMMENT '订单状态,0 为成功,其他为失败',
  `n_pay_info` varchar(64) DEFAULT NULL,
  `n_partner` varchar(32) DEFAULT NULL,
  `n_bank_type` varchar(16) DEFAULT NULL,
  `n_bank_billno` varchar(32) DEFAULT NULL,
  `n_total_fee` varchar(32) DEFAULT NULL,
  `n_fee_type` varchar(32) DEFAULT NULL,
  `n_notify_id` varchar(128) DEFAULT NULL,
  `n_transaction_id` varchar(32) DEFAULT NULL,
  `n_time_end` varchar(14) DEFAULT NULL,
  `n_transport_fee` varchar(32) DEFAULT NULL,
  `n_product_fee` varchar(32) DEFAULT NULL,
  `n_discount` varchar(32) DEFAULT NULL,
  `n_buyer_alias` varchar(64) DEFAULT NULL,
  `n_IsSubscribe` varchar(32) DEFAULT NULL,
  `n_NonceStr` varchar(32) DEFAULT NULL,
  `n_TimeStamp` varchar(32) DEFAULT NULL,
  `trade_notify_timestamp` int(11) DEFAULT '0' COMMENT 'notify时间',
  `trade_notify_times` int(10) DEFAULT '0' COMMENT '交易notify次数',
  `f_FeedBackId` varchar(32) DEFAULT NULL,
  `f_Reason` varchar(512) DEFAULT NULL,
  `f_Solution` varchar(512) DEFAULT NULL,
  `f_ExtInfo` varchar(512) DEFAULT NULL,
  `trade_feedback_request_timestamp` int(11) DEFAULT '0' COMMENT 'feedback request时间',
  `trade_feedback_response_timestamp` int(11) DEFAULT '0' COMMENT 'feedback response时间',
  `trade_feedback_request_times` int(10) DEFAULT '0' COMMENT '维权请求次数',
  `trade_feedback_response_times` int(10) DEFAULT '0' COMMENT '维权确认次数',
  `alarm_ErrorType` varchar(32) DEFAULT NULL,
  `alarm_Description` varchar(256) DEFAULT NULL,
  `alarm_AlarmContent` varchar(256) DEFAULT NULL,
  `trade_alarm_timestamp` int(11) DEFAULT '0' COMMENT 'alarm时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_sn` (`order_sn`),
  KEY `token` (`token`),
  KEY `n_transaction_id` (`n_transaction_id`),
  KEY `f_FeedBackId` (`f_FeedBackId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信支付交易明细' AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_caipiao`
--
-- 创建时间: 2013 年 12 月 22 日 05:10
--

CREATE TABLE IF NOT EXISTS `tp_caipiao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `period` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `time` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `result` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `money` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_brand`
--
-- 创建时间: 2014 年 03 月 09 日 18:42
-- 最后更新: 2014 年 04 月 11 日 03:09
--

CREATE TABLE IF NOT EXISTS `tp_car_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `homepage` varchar(256) DEFAULT NULL,
  `logo` varchar(256) DEFAULT NULL,
  `introduction` varchar(2048) DEFAULT NULL,
  `sequence` smallint(3) NOT NULL COMMENT '数值小者优先显示',
  `status` tinyint(1) NOT NULL COMMENT '1:可用；0:删除',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  `kwd_id` int(11) DEFAULT NULL COMMENT '图文消息关键词ID',
  `keyword` varchar(256) DEFAULT NULL COMMENT '图文消息关键词',
  `title` varchar(256) NOT NULL COMMENT '图文标题',
  `picUrl` varchar(256) NOT NULL COMMENT '图文封面',
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汽车品牌' AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_care`
--
-- 创建时间: 2014 年 03 月 10 日 14:42
-- 最后更新: 2014 年 05 月 16 日 03:24
--

CREATE TABLE IF NOT EXISTS `tp_car_care` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `kwd_id` int(11) DEFAULT NULL COMMENT '图文消息关键词ID',
  `keyword` varchar(64) DEFAULT NULL COMMENT '图文消息关键词',
  `img_title` varchar(64) DEFAULT NULL COMMENT '图文消息标题',
  `img_url` varchar(256) DEFAULT NULL COMMENT '图文消息配图',
  `picture` varchar(250) NOT NULL COMMENT '车主关怀手机页面顶部图片',
  `description` varchar(256) NOT NULL COMMENT '图文说明',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='车主关怀' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_details`
--
-- 创建时间: 2014 年 03 月 24 日 12:45
-- 最后更新: 2014 年 04 月 21 日 08:58
--

CREATE TABLE IF NOT EXISTS `tp_car_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `brand` int(11) DEFAULT NULL COMMENT '品牌',
  `series` int(11) DEFAULT NULL COMMENT '车系',
  `model` int(11) DEFAULT NULL COMMENT '车型',
  `car_type` varchar(256) DEFAULT NULL COMMENT '用户手动输入车型',
  `number` varchar(32) DEFAULT NULL COMMENT '车牌号',
  `number_starttime` varchar(16) DEFAULT NULL COMMENT '挂牌时间',
  `owner` varchar(32) DEFAULT NULL COMMENT '车主',
  `insurance_lastDate` varchar(16) DEFAULT NULL COMMENT '上次保险时间',
  `insurance_lastCost` int(11) DEFAULT NULL COMMENT '上次保险费用',
  `care_mileage` int(11) DEFAULT NULL COMMENT '上次保养里程',
  `care_lastDate` varchar(16) DEFAULT NULL COMMENT '上次保养日期',
  `care_lastCost` int(11) DEFAULT NULL COMMENT '上次保养费用',
  `submit_time` int(11) DEFAULT NULL COMMENT '提交时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `wecha_id` (`wecha_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='车主信息' AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_models`
--
-- 创建时间: 2014 年 03 月 09 日 18:42
-- 最后更新: 2014 年 04 月 11 日 03:11
--

CREATE TABLE IF NOT EXISTS `tp_car_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `car_brand` int(11) NOT NULL COMMENT '所属品牌',
  `car_series` int(11) NOT NULL COMMENT '所属车系',
  `model_year` varchar(64) DEFAULT NULL,
  `sequence` smallint(3) NOT NULL COMMENT '数值小者优先显示',
  `guide_price` double DEFAULT NULL COMMENT '指导价',
  `dealer_price` double DEFAULT NULL COMMENT '经销商报价',
  `pic_id_list` varchar(256) DEFAULT NULL,
  `emission` double DEFAULT NULL,
  `stalls` int(11) DEFAULT NULL,
  `box` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1:可用；0:删除',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `idx_series` (`car_series`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汽车车型' AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_rdrive`
--
-- 创建时间: 2014 年 03 月 09 日 18:43
-- 最后更新: 2014 年 04 月 06 日 13:45
--

CREATE TABLE IF NOT EXISTS `tp_car_rdrive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `kwd_id` int(11) DEFAULT NULL COMMENT '图文消息关键词ID',
  `keyword` varchar(64) DEFAULT NULL COMMENT '图文消息关键词',
  `img_title` varchar(64) DEFAULT NULL COMMENT '图文消息标题',
  `img_url` varchar(256) DEFAULT NULL COMMENT '图文消息配图',
  `tel` varchar(32) NOT NULL COMMENT '商家电话',
  `address` varchar(64) NOT NULL COMMENT '商家地',
  `longtitude` varchar(32) DEFAULT NULL COMMENT '商家地址经纬度',
  `latitude` varchar(32) DEFAULT NULL COMMENT '商家地址经纬度',
  `picture` varchar(250) NOT NULL COMMENT '预约顶部图片',
  `description` varchar(256) NOT NULL COMMENT '预约说明',
  `setting_type` int(3) DEFAULT NULL COMMENT '预约限制类型：1:限定时间；2:每日量；3:总量限制',
  `start_time` int(11) DEFAULT NULL COMMENT '预约开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '预约结束时间',
  `upperbound` int(11) DEFAULT NULL COMMENT '限定量数值：当settting_type＝2:该字段表示每日量；当为3:表示总数上限；',
  `default_col_show` varchar(512) DEFAULT NULL COMMENT '缺省字段是否显示',
  `text_cols` varchar(1024) DEFAULT NULL COMMENT '单行文本字段名称和提示',
  `select_cols` varchar(1024) DEFAULT NULL COMMENT '下拉列表字段名称和提示',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:可用；0:删除',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='预约试驾项目列表' AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_rdrive_order`
--
-- 创建时间: 2014 年 03 月 09 日 18:43
-- 最后更新: 2014 年 04 月 06 日 14:07
--

CREATE TABLE IF NOT EXISTS `tp_car_rdrive_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `rdrive_id` int(11) NOT NULL COMMENT '试驾项目',
  `name` varchar(50) NOT NULL COMMENT '预订人',
  `tel` varchar(13) NOT NULL COMMENT '电话',
  `brand` int(11) NOT NULL COMMENT '品牌',
  `series` int(11) NOT NULL COMMENT '车系',
  `model` int(11) NOT NULL COMMENT '车型',
  `reserve_date` int(11) NOT NULL COMMENT '预约日期',
  `reserve_time` int(11) NOT NULL COMMENT '预约时间',
  `remarks` varchar(255) DEFAULT NULL COMMENT '留言备注',
  `text_cols` varchar(1024) DEFAULT NULL COMMENT '单行文本字段(|分隔)',
  `select_cols` varchar(1024) DEFAULT NULL COMMENT '下拉列表字段(|分隔)',
  `submit_time` int(11) DEFAULT NULL COMMENT '订单提交时间',
  `update_time` int(11) DEFAULT NULL COMMENT '订单修改时间',
  `status` int(11) NOT NULL COMMENT '订单状态 1:未处理；2:已确认；3:用户已删除；4:已拒绝',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `wecha_id` (`wecha_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='预约试驾订单' AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_rmaintain`
--
-- 创建时间: 2014 年 03 月 09 日 18:43
-- 最后更新: 2014 年 04 月 10 日 04:30
--

CREATE TABLE IF NOT EXISTS `tp_car_rmaintain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `kwd_id` int(11) DEFAULT NULL COMMENT '图文消息关键词ID',
  `keyword` varchar(64) DEFAULT NULL COMMENT '图文消息关键词',
  `img_title` varchar(64) DEFAULT NULL COMMENT '图文消息标题',
  `img_url` varchar(256) DEFAULT NULL COMMENT '图文消息配图',
  `tel` varchar(32) NOT NULL COMMENT '商家电话',
  `address` varchar(64) NOT NULL COMMENT '商家地',
  `longtitude` varchar(32) DEFAULT NULL COMMENT '商家地址经纬度',
  `latitude` varchar(32) DEFAULT NULL COMMENT '商家地址经纬度',
  `picture` varchar(250) NOT NULL COMMENT '预约顶部图片',
  `description` varchar(256) NOT NULL COMMENT '预约说明',
  `setting_type` int(3) DEFAULT NULL COMMENT '预约限制类型：1:限定时间；2:每日量；3:总量限制',
  `start_time` int(11) DEFAULT NULL COMMENT '预约开始时间',
  `end_time` int(11) DEFAULT NULL COMMENT '预约结束时间',
  `upperbound` int(11) DEFAULT NULL COMMENT '限定量数值：当settting_type＝2:该字段表示每日量；当为3:表示总数上限；',
  `default_col_show` varchar(512) DEFAULT NULL COMMENT '缺省字段是否显示',
  `text_cols` varchar(1024) DEFAULT NULL COMMENT '单行文本字段名称和提示',
  `select_cols` varchar(1024) DEFAULT NULL COMMENT '下拉列表字段名称和提示',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:可用；0:删除',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='预约维修项目列表' AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_rmaintain_order`
--
-- 创建时间: 2014 年 03 月 09 日 18:43
-- 最后更新: 2014 年 03 月 12 日 10:29
--

CREATE TABLE IF NOT EXISTS `tp_car_rmaintain_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `rdrive_id` int(11) NOT NULL COMMENT '试驾项目',
  `name` varchar(50) NOT NULL COMMENT '预订人',
  `tel` varchar(13) NOT NULL COMMENT '电话',
  `number` varchar(32) NOT NULL COMMENT '车牌',
  `miles` varchar(32) NOT NULL COMMENT '里程数',
  `reserve_date` int(11) NOT NULL COMMENT '预约日期',
  `reserve_time` int(11) NOT NULL COMMENT '预约时间',
  `remarks` varchar(255) DEFAULT NULL COMMENT '留言备注',
  `text_cols` varchar(1024) DEFAULT NULL COMMENT '单行文本字段(|分隔)',
  `select_cols` varchar(1024) DEFAULT NULL COMMENT '下拉列表字段(|分隔)',
  `submit_time` int(11) DEFAULT NULL COMMENT '订单提交时间',
  `update_time` int(11) DEFAULT NULL COMMENT '订单修改时间',
  `status` int(11) NOT NULL COMMENT '订单状态 1:未处理；2:已确认；3:用户已删除；4:已拒绝',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `wecha_id` (`wecha_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='预约保养订单' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_sales`
--
-- 创建时间: 2014 年 03 月 09 日 18:43
-- 最后更新: 2014 年 04 月 06 日 13:43
--

CREATE TABLE IF NOT EXISTS `tp_car_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `name` varchar(64) NOT NULL,
  `picture` varchar(256) NOT NULL,
  `tel` varchar(64) NOT NULL,
  `sequence` smallint(3) NOT NULL COMMENT '数值小者优先显示',
  `introduction` varchar(512) DEFAULT NULL,
  `pre_sale` tinyint(1) NOT NULL COMMENT '1:售前；0:不是售前',
  `post_sale` tinyint(1) NOT NULL COMMENT '1:售后；0:不是售后',
  `status` tinyint(1) NOT NULL COMMENT '1:可用；0:删除',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汽车销售' AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_series`
--
-- 创建时间: 2014 年 03 月 09 日 18:42
-- 最后更新: 2014 年 04 月 11 日 03:11
--

CREATE TABLE IF NOT EXISTS `tp_car_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `short_name` varchar(256) NOT NULL,
  `picture` varchar(256) DEFAULT NULL,
  `brand_id` int(11) NOT NULL COMMENT '所属品牌',
  `sequence` smallint(3) NOT NULL COMMENT '数值小者优先显示',
  `description` varchar(512) DEFAULT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1:可用；0:删除',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '上次更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_brand_id` (`brand_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汽车车系' AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_car_tools`
--
-- 创建时间: 2014 年 03 月 10 日 13:13
-- 最后更新: 2014 年 03 月 12 日 14:52
--

CREATE TABLE IF NOT EXISTS `tp_car_tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(256) NOT NULL,
  `closed_tools` varchar(256) DEFAULT NULL COMMENT '用户关闭的工具列表',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汽车实用工具状态表' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_classify`
--
-- 创建时间: 2014 年 05 月 23 日 14:22
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_classify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `info` varchar(90) DEFAULT NULL COMMENT '分类描述',
  `sorts` int(10) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `img` char(255) NOT NULL,
  `url` char(255) NOT NULL,
  `status` varchar(1) NOT NULL,
  `token` varchar(30) NOT NULL,
  `tmpl` varchar(30) DEFAULT NULL,
  `linktype` varchar(25) NOT NULL DEFAULT 'articles',
  `link_param_l1` varchar(512) DEFAULT NULL,
  `link_param_l2` varchar(512) DEFAULT NULL,
  `slide_img` varchar(255) DEFAULT NULL COMMENT '栏目列表页顶部图片',
  `use_cover_img` tinyint(1) DEFAULT '1' COMMENT '使用封面做为slide_img',
  `parent` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类，0是最顶级分类',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4908 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_cs_setting`
--
-- 创建时间: 2014 年 05 月 21 日 13:24
--

CREATE TABLE IF NOT EXISTS `tp_cs_setting` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(63) NOT NULL,
  `keyword` varchar(127) DEFAULT NULL COMMENT '用户触发多客服关键词',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '商户转发多客服标记，0 表示不转，1 表示始终转发 2 按需转发',
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='多客服设置' AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_dine_category`
--
-- 创建时间: 2014 年 04 月 15 日 08:21
--

CREATE TABLE IF NOT EXISTS `tp_dine_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rest_id` int(11) DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint(4) unsigned DEFAULT '1' COMMENT '0 是初始不显示 1是显示 2是删除',
  `orderNum` int(11) DEFAULT '1',
  `description` text COLLATE utf8_general_ci,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=268 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_dine_menu`
--
-- 创建时间: 2014 年 05 月 04 日 07:34
--

CREATE TABLE IF NOT EXISTS `tp_dine_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `description` text COLLATE utf8_general_ci,
  `price` float DEFAULT NULL,
  `oprice` float DEFAULT NULL,
  `imgurl` text COLLATE utf8_general_ci,
  `status` tinyint(4) DEFAULT '1' COMMENT '0初始不显示 1上架  2下架并删除 5是推荐',
  `orderNum` int(11) DEFAULT '1',
  `rest_id` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `promt_status` tinyint(3) DEFAULT '0' COMMENT '促销属性，用于描述当前菜品是否推荐、促销，0是无，1是推荐，其他有待定义',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1154 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_dine_order`
--
-- 创建时间: 2014 年 05 月 16 日 09:08
--

CREATE TABLE IF NOT EXISTS `tp_dine_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rest_id` varchar(45) COLLATE utf8_general_ci DEFAULT '',
  `tel` varchar(11) COLLATE utf8_general_ci DEFAULT '',
  `username` varchar(45) COLLATE utf8_general_ci DEFAULT '',
  `menus` text COLLATE utf8_general_ci,
  `status` int(11) DEFAULT NULL,
  `createtime` int(11) DEFAULT '0',
  `submittime` int(11) DEFAULT '0',
  `note` text COLLATE utf8_general_ci,
  `dinetime` varchar(24) COLLATE utf8_general_ci DEFAULT '',
  `guestnum` int(11) DEFAULT '0',
  `wecha_id` varchar(45) COLLATE utf8_general_ci DEFAULT '',
  `table` varchar(45) COLLATE utf8_general_ci DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `sn` varchar(45) COLLATE utf8_general_ci DEFAULT '',
  `readed` tinyint(4) DEFAULT '0' COMMENT '是否已读，1表示已读，0未读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1031 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_dine_rest`
--
-- 创建时间: 2014 年 02 月 12 日 06:07
--

CREATE TABLE IF NOT EXISTS `tp_dine_rest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_general_ci DEFAULT NULL,
  `keyword` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `logo_url` text COLLATE utf8_general_ci,
  `desc` text COLLATE utf8_general_ci,
  `address` text COLLATE utf8_general_ci,
  `telephone` varchar(11) COLLATE utf8_general_ci DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_dine_restlist`
--
-- 创建时间: 2014 年 04 月 11 日 15:07
--

CREATE TABLE IF NOT EXISTS `tp_dine_restlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_general_ci NOT NULL,
  `address` text COLLATE utf8_general_ci,
  `telephone` varchar(11) COLLATE utf8_general_ci DEFAULT NULL,
  `longtitude` float DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  `orderNum` int(1) DEFAULT '1',
  `logo_url` varchar(512) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=53 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_dine_room`
--
-- 创建时间: 2014 年 02 月 23 日 03:05
--

CREATE TABLE IF NOT EXISTS `tp_dine_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rest_id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_diymen_class`
--
-- 创建时间: 2014 年 02 月 02 日 05:19
-- 最后更新: 2014 年 06 月 05 日 13:58
--

CREATE TABLE IF NOT EXISTS `tp_diymen_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `pid` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `is_show` tinyint(1) NOT NULL,
  `sort` tinyint(3) NOT NULL,
  `type` varchar(45) DEFAULT 'click',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1254 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_estate`
--
-- 创建时间: 2014 年 03 月 09 日 06:27
-- 最后更新: 2014 年 05 月 30 日 02:07
-- 最后检查: 2014 年 03 月 09 日 06:27
--

CREATE TABLE IF NOT EXISTS `tp_estate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(100) NOT NULL,
  `title` varchar(50) NOT NULL,
  `keyword` varchar(50) NOT NULL,
  `msg_pic_url` varchar(200) NOT NULL,
  `banner` varchar(200) NOT NULL,
  `house_banner` varchar(200) NOT NULL,
  `longtitude` float NOT NULL,
  `latitude` float NOT NULL,
  `description` text NOT NULL,
  `tel` int(11) DEFAULT NULL,
  `traffic_desc` text,
  `address` varchar(200) DEFAULT NULL,
  `photo_id` int(11) DEFAULT '0',
  `panorama_id` varchar(45) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `token_2` (`token`),
  FULLTEXT KEY `token` (`token`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `keyword` (`keyword`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_estate_expert`
--
-- 创建时间: 2014 年 03 月 09 日 06:27
-- 最后更新: 2014 年 05 月 13 日 08:29
--

CREATE TABLE IF NOT EXISTS `tp_estate_expert` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(30) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `orderNum` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `pic_url_input` varchar(200) NOT NULL,
  `description` varchar(200) NOT NULL,
  `comment` varchar(200) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_estate_house`
--
-- 创建时间: 2014 年 03 月 09 日 07:36
-- 最后更新: 2014 年 05 月 13 日 08:12
--

CREATE TABLE IF NOT EXISTS `tp_estate_house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(10) NOT NULL DEFAULT '0',
  `token` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `floor_num` varchar(20) NOT NULL,
  `area` varchar(50) NOT NULL,
  `fang` int(11) NOT NULL,
  `ting` int(11) NOT NULL,
  `orderNum` int(11) NOT NULL,
  `description` varchar(200) NOT NULL,
  `pic_url_input` varchar(200) NOT NULL,
  `pic_url_input_1` varchar(200) NOT NULL,
  `pic_url_input_2` varchar(200) NOT NULL,
  `type4` varchar(200) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `panorama_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_estate_list`
--
-- 创建时间: 2014 年 03 月 09 日 06:27
-- 最后更新: 2014 年 05 月 05 日 06:56
--

CREATE TABLE IF NOT EXISTS `tp_estate_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderNum` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `token` varchar(50) NOT NULL,
  `update_time` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_flash`
--
-- 创建时间: 2013 年 12 月 27 日 13:40
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_flash` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `img` char(255) NOT NULL,
  `url` char(255) NOT NULL,
  `info` varchar(90) NOT NULL,
  `sorts` int(11) DEFAULT '0',
  `linktype` varchar(25) NOT NULL DEFAULT 'nolink',
  `link_param_l1` varchar(512) DEFAULT NULL,
  `link_param_l2` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='3g网站头部flash' AUTO_INCREMENT=2531 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_function`
--
-- 创建时间: 2013 年 11 月 02 日 07:22
-- 最后更新: 2014 年 04 月 20 日 10:24
--

CREATE TABLE IF NOT EXISTS `tp_function` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` tinyint(3) NOT NULL,
  `usenum` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `funname` varchar(100) NOT NULL,
  `info` varchar(100) NOT NULL,
  `isserve` tinyint(1) NOT NULL,
  `keywords` varchar(127) DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `fgid` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_function_group`
--
-- 创建时间: 2014 年 01 月 22 日 04:14
--

CREATE TABLE IF NOT EXISTS `tp_function_group` (
  `id` int(11) NOT NULL,
  `name` varchar(63) NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `visible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1:显示；0:不显示；',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '控制功能管理页面功能组的显示顺序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_gallery3d`
--
-- 创建时间: 2013 年 12 月 18 日 14:01
-- 最后更新: 2014 年 06 月 06 日 01:36
-- 最后检查: 2013 年 12 月 18 日 14:01
--

CREATE TABLE IF NOT EXISTS `tp_gallery3d` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `title` varchar(20) NOT NULL,
  `picurl` varchar(255) NOT NULL,
  `isshoinfo` tinyint(1) NOT NULL,
  `num` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `create_time` int(11) NOT NULL,
  `info` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token_index` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=613 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_greetcard`
--
-- 创建时间: 2013 年 12 月 04 日 10:22
--

CREATE TABLE IF NOT EXISTS `tp_greetcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tp_id` int(11) DEFAULT NULL,
  `recver` varchar(128) DEFAULT NULL,
  `content` text,
  `author` varchar(128) DEFAULT NULL,
  `date` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=345 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_greetcard_tp`
--
-- 创建时间: 2013 年 12 月 04 日 10:22
--

CREATE TABLE IF NOT EXISTS `tp_greetcard_tp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `content` text,
  `imgurl` varchar(128) DEFAULT NULL,
  `bimgurl` varchar(128) DEFAULT NULL,
  `himgurl` varchar(128) DEFAULT NULL,
  `sorts` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_home`
--
-- 创建时间: 2013 年 10 月 15 日 02:39
-- 最后更新: 2013 年 11 月 02 日 03:01
--

CREATE TABLE IF NOT EXISTS `tp_home` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `title` varchar(30) NOT NULL,
  `picurl` varchar(120) NOT NULL,
  `info` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_host`
--
-- 创建时间: 2014 年 02 月 22 日 06:05
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_host` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `keyword` varchar(50) NOT NULL COMMENT '关键词',
  `title` varchar(50) NOT NULL COMMENT '商家名称',
  `address` varchar(50) NOT NULL COMMENT '商家地',
  `tel` varchar(13) NOT NULL COMMENT '商家电话',
  `tel2` varchar(13) NOT NULL COMMENT '手机号',
  `ppicurl` varchar(250) NOT NULL COMMENT '订房封面图片',
  `headpic` varchar(250) NOT NULL COMMENT '订单页头部图片',
  `name` varchar(50) DEFAULT NULL COMMENT '文字描述',
  `sort` int(11) NOT NULL COMMENT '排序',
  `picurl` varchar(100) NOT NULL COMMENT '图片地址',
  `url` varchar(50) NOT NULL COMMENT '图片跳转地址以http',
  `info` text NOT NULL COMMENT '商家介绍：',
  `info2` text NOT NULL COMMENT '订房说明u',
  `creattime` int(11) NOT NULL COMMENT '创建日期',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `default_col_show` varchar(256) DEFAULT NULL COMMENT '缺省字段是否显示',
  `text_cols` varchar(1024) DEFAULT NULL COMMENT '单行文本字段名称和提示',
  `select_cols` varchar(1024) DEFAULT NULL COMMENT '下拉列表字段名称和提示',
  `longtitude` varchar(32) DEFAULT NULL COMMENT '商家地址经纬度',
  `latitude` varchar(32) DEFAULT NULL COMMENT '商家地址经纬度',
  `type` tinyint(3) unsigned DEFAULT '0' COMMENT '0是预定 1是报名 2是预约',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='酒店商家设置' AUTO_INCREMENT=649 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_host_list_add`
--
-- 创建时间: 2014 年 06 月 04 日 07:04
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_host_list_add` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hid` int(11) NOT NULL COMMENT '商家id',
  `token` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT '房间类型',
  `typeinfo` varchar(100) NOT NULL COMMENT '简要说明',
  `price` decimal(10,2) DEFAULT NULL COMMENT '原价：',
  `yhprice` decimal(10,2) DEFAULT NULL,
  `name` varchar(50) NOT NULL COMMENT '文字描述',
  `picurl` varchar(110) NOT NULL COMMENT '图片地址',
  `url` varchar(100) DEFAULT NULL COMMENT '图片跳转地址以http',
  `info` text NOT NULL COMMENT '配套设施',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `inventory` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `open_inventory` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否开启库存，兼容以前没有库存的情况',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='房间类型信息表' AUTO_INCREMENT=1395 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_host_order`
--
-- 创建时间: 2014 年 06 月 04 日 07:04
-- 最后更新: 2014 年 06 月 05 日 10:34
--

CREATE TABLE IF NOT EXISTS `tp_host_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `book_people` varchar(50) NOT NULL COMMENT '预订人',
  `tel` varchar(13) NOT NULL COMMENT '电话',
  `check_in` int(11) NOT NULL COMMENT '入住时间',
  `check_out` int(11) NOT NULL COMMENT '离开时间',
  `room_type` varchar(50) NOT NULL COMMENT '房间类型',
  `book_time` int(11) NOT NULL COMMENT '预订时间',
  `book_num` int(11) NOT NULL COMMENT '预订数量',
  `price` decimal(10,2) NOT NULL COMMENT ' 价格',
  `order_status` int(11) NOT NULL COMMENT '订单状态 1 成功,2 失败,3 未处理',
  `hid` int(11) NOT NULL COMMENT '订房商家id',
  `remarks` varchar(255) DEFAULT '' COMMENT '留言备注',
  `text_cols` varchar(1024) DEFAULT '' COMMENT '单行文本字段(|分隔)',
  `select_cols` varchar(1024) DEFAULT '' COMMENT '下拉列表字段(|分隔)',
  `submit_time` int(11) DEFAULT '0' COMMENT '订单提交时间',
  `sn` varchar(15) DEFAULT '',
  `readed` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已读，1表示已读，默认为0',
  `booking_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'host_list_add表中的主键ID，表明订单所属的项',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单管理' AUTO_INCREMENT=538 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_hotel`
--
-- 创建时间: 2014 年 02 月 14 日 13:27
-- 最后更新: 2014 年 06 月 04 日 03:50
--

CREATE TABLE IF NOT EXISTS `tp_hotel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `keyword` varchar(50) NOT NULL COMMENT '关键词',
  `title` varchar(50) NOT NULL COMMENT '商家名称',
  `address` varchar(50) NOT NULL COMMENT '商家地',
  `tel` varchar(13) NOT NULL COMMENT '商家电话',
  `tel2` varchar(13) NOT NULL COMMENT '手机号',
  `ppicurl` varchar(250) NOT NULL COMMENT '订房封面图片',
  `name` varchar(50) NOT NULL COMMENT '文字描述',
  `sort` int(11) NOT NULL COMMENT '排序',
  `picurl` varchar(100) NOT NULL COMMENT '图片地址',
  `url` varchar(50) NOT NULL COMMENT '图片跳转地址以http',
  `info` text NOT NULL COMMENT '商家介绍：',
  `info2` text NOT NULL COMMENT '订房说明u',
  `creattime` int(11) NOT NULL COMMENT '创建日期',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `default_col_show` varchar(256) DEFAULT NULL COMMENT '缺省字段是否显示',
  `text_cols` varchar(1024) DEFAULT NULL COMMENT '单行文本字段名称和提示',
  `select_cols` varchar(1024) DEFAULT NULL COMMENT '下拉列表字段名称和提示',
  `longtitude` varchar(32) DEFAULT NULL,
  `latitude` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='宾馆设置' AUTO_INCREMENT=530742 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_hotel_order`
--
-- 创建时间: 2014 年 05 月 16 日 08:59
-- 最后更新: 2014 年 06 月 05 日 12:20
--

CREATE TABLE IF NOT EXISTS `tp_hotel_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `book_people` varchar(50) NOT NULL COMMENT '预订人',
  `tel` varchar(13) NOT NULL COMMENT '电话',
  `check_in` int(11) NOT NULL COMMENT '入住时间',
  `check_out` int(11) NOT NULL COMMENT '离开时间',
  `room_type` varchar(50) NOT NULL COMMENT '房间类型',
  `book_time` int(11) NOT NULL COMMENT '预订时间',
  `book_num` int(11) NOT NULL COMMENT '预订数量',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT ' 价格',
  `order_status` int(11) NOT NULL COMMENT '订单状态 1 成功,2 商户取消,3 未处理 4 用户取消',
  `hid` int(11) NOT NULL COMMENT '订房商家id',
  `remarks` varchar(250) NOT NULL COMMENT '留言备注',
  `text_cols` varchar(1024) DEFAULT '' COMMENT '单行文本字段(|分隔)',
  `select_cols` varchar(1024) DEFAULT '' COMMENT '下拉列表字段(|分隔)',
  `submit_time` int(11) DEFAULT '0' COMMENT '订单提交时间',
  `book_lefttime` int(11) DEFAULT '0',
  `sn` varchar(15) DEFAULT '',
  `readed` tinyint(4) DEFAULT '0' COMMENT '1已读，0未读',
  `room_id` int(11) NOT NULL DEFAULT '0' COMMENT '预定房间的id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单管理' AUTO_INCREMENT=322 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_hotel_room`
--
-- 创建时间: 2014 年 02 月 17 日 14:47
-- 最后更新: 2014 年 06 月 04 日 04:00
--

CREATE TABLE IF NOT EXISTS `tp_hotel_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hid` int(11) NOT NULL COMMENT '商家id',
  `token` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT '房间类型',
  `typeinfo` varchar(100) NOT NULL COMMENT '简要说明',
  `price` decimal(10,2) NOT NULL COMMENT '原价：',
  `yhprice` decimal(10,2) NOT NULL,
  `name` varchar(50) DEFAULT NULL COMMENT '文字描述',
  `picurl` varchar(110) NOT NULL COMMENT '图片地址',
  `url` varchar(100) DEFAULT NULL COMMENT '图片跳转地址以http',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `inventory` int(11) NOT NULL DEFAULT '0' COMMENT '房间库存',
  `open_inventory` int(11) NOT NULL DEFAULT '0' COMMENT '是否开启库存',
  `info` text NOT NULL COMMENT '配套设施',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='房间类型信息表' AUTO_INCREMENT=272 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_img`
--
-- 创建时间: 2014 年 04 月 28 日 16:27
-- 最后更新: 2014 年 06 月 06 日 02:16
-- 最后检查: 2014 年 04 月 28 日 16:27
--

CREATE TABLE IF NOT EXISTS `tp_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uname` varchar(90) NOT NULL,
  `keyword` char(255) NOT NULL,
  `type` varchar(1) NOT NULL COMMENT '关键词匹配类型',
  `status` tinyint(3) NOT NULL,
  `token` char(30) NOT NULL,
  `function` varchar(30) NOT NULL,
  `text` text NOT NULL COMMENT '简介',
  `pic` char(255) NOT NULL COMMENT '封面图片',
  `showpic` varchar(1) NOT NULL COMMENT '图片是否显示封面',
  `info` text COMMENT '图文详细内容',
  `url` char(255) DEFAULT NULL COMMENT '图文外链地址',
  `click` int(11) NOT NULL,
  `title` varchar(60) NOT NULL,
  `createtime` varchar(13) NOT NULL,
  `detail_display_tmpl` varchar(30) DEFAULT NULL,
  `updatetime` varchar(13) NOT NULL,
  `classid` int(11) DEFAULT NULL,
  `classname` varchar(60) DEFAULT NULL,
  `sorts` int(11) unsigned DEFAULT '1',
  `linktype` varchar(45) DEFAULT NULL,
  `service` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classid` (`classid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信图文' AUTO_INCREMENT=5592 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_impress`
--
-- 创建时间: 2014 年 04 月 03 日 08:07
--

CREATE TABLE IF NOT EXISTS `tp_impress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `keyword` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `title` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `msg_pic_url` varchar(512) COLLATE utf8_general_ci DEFAULT NULL,
  `impress` text COLLATE utf8_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_invitecode`
--
-- 创建时间: 2014 年 02 月 20 日 12:05
-- 最后更新: 2014 年 06 月 06 日 02:06
-- 最后检查: 2014 年 02 月 20 日 12:05
--

CREATE TABLE IF NOT EXISTS `tp_invitecode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `signature` varchar(512) NOT NULL COMMENT '数字签名 身份认证',
  `function_group_list` varchar(512) NOT NULL COMMENT '功能列表',
  `duration` int(10) NOT NULL COMMENT '有效天数',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `manager` int(10) DEFAULT NULL COMMENT '管理者',
  `manager_role` int(10) NOT NULL COMMENT '管理者角色',
  `assign_manager_time` int(11) DEFAULT NULL COMMENT '分配给管理者的时间',
  `assign_manager_price` int(11) DEFAULT NULL COMMENT '分配给管理者的价格',
  `activator` varchar(512) DEFAULT NULL COMMENT '充值者',
  `final_user` int(10) DEFAULT NULL COMMENT '终极用户',
  `activate_time` int(11) DEFAULT NULL COMMENT '出售给终极用户的时间',
  `activate_price` int(11) DEFAULT NULL COMMENT '出售给终极用户的价格',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0:未激活；1:激活；-1: 删除',
  `remarks` varchar(250) DEFAULT NULL COMMENT '留言备注',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT '充值码类型：1－功能码；2-短信码；',
  `generator` int(11) NOT NULL DEFAULT '8' COMMENT '生成充值码的后台用户',
  `package` int(11) DEFAULT NULL COMMENT '充值码对应的套餐',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_index` (`code`),
  KEY `createtime_index` (`create_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='邀请码管理' AUTO_INCREMENT=801 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_keyword`
--
-- 创建时间: 2014 年 03 月 11 日 14:58
-- 最后更新: 2014 年 06 月 06 日 02:01
-- 最后检查: 2014 年 03 月 11 日 14:58
--

CREATE TABLE IF NOT EXISTS `tp_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` char(255) NOT NULL,
  `pid` int(11) NOT NULL,
  `token` varchar(60) NOT NULL,
  `module` varchar(15) NOT NULL,
  `type` tinyint(3) NOT NULL DEFAULT '1',
  `function` varchar(30) NOT NULL DEFAULT 'kefu',
  `sorts` int(11) unsigned DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:删除；1:可用',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30962 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_lingzh_news`
--
-- 创建时间: 2014 年 04 月 17 日 10:38
--

CREATE TABLE IF NOT EXISTS `tp_lingzh_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL COMMENT '分类ID，分类信息放到Home/Config下',
  `sorts` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `slide_show` int(11) NOT NULL DEFAULT '1' COMMENT '是否在首页滚动',
  `has_link` int(11) NOT NULL DEFAULT '0' COMMENT '在首页滚动的标题是否有链接',
  `source` varchar(50) NOT NULL DEFAULT '领众科技' COMMENT '文章来源',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL,
  `added_uid` int(11) NOT NULL COMMENT '添加者id',
  `visit_num` int(11) NOT NULL DEFAULT '0' COMMENT '访问次数',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '为0时表示已删除',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '文章内容',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_lottery`
--
-- 创建时间: 2014 年 04 月 30 日 07:34
-- 最后更新: 2014 年 06 月 06 日 02:10
-- 最后检查: 2014 年 04 月 30 日 07:34
--

CREATE TABLE IF NOT EXISTS `tp_lottery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joinnum` int(11) NOT NULL COMMENT '参与人数',
  `click` int(11) NOT NULL,
  `token` varchar(30) NOT NULL,
  `keyword` varchar(10) NOT NULL,
  `starpicurl` varchar(100) NOT NULL COMMENT '填写活动开始图片网址',
  `title` varchar(60) NOT NULL COMMENT '活动名称',
  `txt` varchar(60) NOT NULL COMMENT '用户输入兑奖时候的显示信息',
  `sttxt` varchar(200) NOT NULL COMMENT '简介',
  `statdate` int(11) NOT NULL COMMENT '活动开始时间',
  `enddate` int(11) NOT NULL COMMENT '活动结束时间',
  `info` varchar(200) NOT NULL COMMENT '活动说明',
  `aginfo` varchar(200) NOT NULL COMMENT '重复抽奖回复',
  `endtite` varchar(60) NOT NULL COMMENT '活动结束公告主题',
  `endpicurl` varchar(100) NOT NULL,
  `endinfo` varchar(60) NOT NULL,
  `fist` varchar(50) NOT NULL COMMENT '一等奖奖品设置',
  `fistnums` int(4) NOT NULL COMMENT '一等奖奖品数量',
  `fistlucknums` int(1) NOT NULL COMMENT '一等奖中奖号码',
  `second` varchar(50) NOT NULL COMMENT '二等奖奖品设置',
  `type` tinyint(1) NOT NULL,
  `secondnums` int(4) NOT NULL,
  `secondlucknums` int(1) NOT NULL,
  `third` varchar(50) NOT NULL,
  `thirdnums` int(4) NOT NULL,
  `thirdlucknums` int(1) NOT NULL,
  `allpeople` int(11) NOT NULL,
  `canrqnums` int(2) NOT NULL COMMENT '个人限制抽奖次数',
  `parssword` int(15) NOT NULL,
  `renamesn` int(20) NOT NULL,
  `renametel` varchar(60) NOT NULL,
  `displayjpnums` int(1) NOT NULL,
  `createtime` int(11) NOT NULL,
  `status` int(1) NOT NULL COMMENT '0初始、1开始 2结束',
  `four` varchar(50) NOT NULL,
  `fournums` int(11) NOT NULL,
  `fourlucknums` int(11) NOT NULL,
  `five` varchar(50) NOT NULL,
  `fivenums` int(11) NOT NULL,
  `fivelucknums` int(11) NOT NULL,
  `six` varchar(50) NOT NULL COMMENT '六等奖',
  `sixnums` int(11) NOT NULL,
  `sixlucknums` int(11) NOT NULL,
  `group` varchar(500) DEFAULT NULL COMMENT '限定的会员等级，为序列化后的php数组',
  `all_funs` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否是全体粉丝',
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=400 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_lottery_record`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2014 年 06 月 06 日 02:10
--

CREATE TABLE IF NOT EXISTS `tp_lottery_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lid` int(11) NOT NULL,
  `usenums` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户使用次数',
  `wecha_id` varchar(60) NOT NULL COMMENT '微信唯一识别码',
  `token` varchar(30) NOT NULL,
  `islottery` int(1) NOT NULL COMMENT '是否中奖',
  `wecha_name` varchar(60) NOT NULL COMMENT '微信号',
  `phone` varchar(15) NOT NULL,
  `sn` varchar(13) NOT NULL COMMENT '中奖后序列号',
  `time` int(11) NOT NULL,
  `prize` varchar(50) NOT NULL DEFAULT '' COMMENT '已中奖项',
  `sendstutas` int(11) NOT NULL DEFAULT '0',
  `sendtime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`lid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9458 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_manage_user`
--
-- 创建时间: 2014 年 04 月 23 日 06:38
-- 最后更新: 2014 年 06 月 04 日 14:46
--

CREATE TABLE IF NOT EXISTS `tp_manage_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(60) NOT NULL DEFAULT '',
  `token` varchar(60) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `lz_salt` varchar(10) DEFAULT NULL,
  `create_time` int(11) NOT NULL DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `last_ip` varchar(15) NOT NULL DEFAULT '',
  `action_list` text,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `update_time` int(11) NOT NULL,
  `hotelsub` int(11) DEFAULT NULL,
  `diningsub` int(11) DEFAULT NULL,
  `printable` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否支持自动打印，0不支持，1支持',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=115 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member`
--
-- 创建时间: 2013 年 12 月 06 日 07:48
-- 最后更新: 2013 年 12 月 06 日 07:48
-- 最后检查: 2013 年 12 月 06 日 07:48
--

CREATE TABLE IF NOT EXISTS `tp_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `isopen` int(1) NOT NULL,
  `homepic` varchar(255) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_contact`
--
-- 创建时间: 2014 年 01 月 13 日 06:24
-- 最后更新: 2014 年 05 月 29 日 06:10
--

CREATE TABLE IF NOT EXISTS `tp_member_card_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `cname` varchar(30) NOT NULL,
  `tel` varchar(12) NOT NULL,
  `sort` tinyint(1) NOT NULL,
  `info` varchar(60) NOT NULL,
  `longtitude` varchar(32) DEFAULT NULL,
  `latitude` varchar(32) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL COMMENT '公司描述',
  `picture` int(11) DEFAULT NULL COMMENT '公司展示图片ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_coupon`
--
-- 创建时间: 2014 年 04 月 21 日 12:11
-- 最后更新: 2014 年 06 月 03 日 04:46
--

CREATE TABLE IF NOT EXISTS `tp_member_card_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `title` varchar(60) NOT NULL,
  `groupid` tinyint(4) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `price` int(11) NOT NULL,
  `people` int(3) NOT NULL,
  `statdate` int(11) NOT NULL,
  `enddate` int(11) NOT NULL,
  `info` text NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_create`
--
-- 创建时间: 2014 年 04 月 30 日 08:08
-- 最后更新: 2014 年 06 月 05 日 14:18
-- 最后检查: 2014 年 04 月 30 日 08:08
--

CREATE TABLE IF NOT EXISTS `tp_member_card_create` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `number` varchar(20) NOT NULL,
  `wecha_id` varchar(60) NOT NULL,
  `groupid` int(3) DEFAULT '1',
  `getcardtime` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0表示已删除，1表示正常',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_number_token_idx` (`token`,`number`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40725 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_exchange`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2014 年 05 月 30 日 06:32
--

CREATE TABLE IF NOT EXISTS `tp_member_card_exchange` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `everyday` tinyint(4) NOT NULL,
  `continuation` tinyint(4) NOT NULL,
  `reward` tinyint(4) NOT NULL,
  `cardinfo` varchar(200) NOT NULL,
  `cardinfo2` varchar(200) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=68 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_info`
--
-- 创建时间: 2013 年 12 月 06 日 07:48
-- 最后更新: 2014 年 06 月 05 日 08:56
--

CREATE TABLE IF NOT EXISTS `tp_member_card_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `info` varchar(200) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `description` varchar(12) NOT NULL,
  `class` tinyint(1) NOT NULL,
  `password` varchar(11) NOT NULL,
  `createtime` varchar(13) NOT NULL,
  `updatetime` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=89 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_integral`
--
-- 创建时间: 2014 年 01 月 06 日 09:09
-- 最后更新: 2014 年 05 月 25 日 04:34
--

CREATE TABLE IF NOT EXISTS `tp_member_card_integral` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `title` varchar(60) NOT NULL,
  `integral` int(8) NOT NULL,
  `statdate` int(11) NOT NULL,
  `enddate` int(11) NOT NULL,
  `info` text NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_set`
--
-- 创建时间: 2014 年 04 月 23 日 14:24
-- 最后更新: 2014 年 06 月 05 日 15:32
--

CREATE TABLE IF NOT EXISTS `tp_member_card_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `cardname` varchar(60) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `bg` varchar(100) NOT NULL,
  `diybg` varchar(255) NOT NULL,
  `msg` varchar(100) NOT NULL,
  `numbercolor` varchar(10) NOT NULL,
  `vipnamecolor` varchar(10) NOT NULL,
  `Lastmsg` varchar(100) NOT NULL,
  `vip` varchar(100) NOT NULL,
  `qiandao` varchar(100) NOT NULL,
  `shopping` varchar(100) NOT NULL,
  `memberinfo` varchar(100) NOT NULL,
  `membermsg` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `show_items` varchar(64) DEFAULT NULL,
  `card_off` tinyint(4) DEFAULT '0',
  `create_time` int(11) NOT NULL,
  `default_show_cols` varchar(1000) DEFAULT NULL,
  `text_cols` varchar(1000) DEFAULT NULL,
  `select_cols` varchar(1000) DEFAULT NULL,
  `card_amount` int(11) DEFAULT NULL,
  `card_amount_limit` int(11) DEFAULT NULL,
  `card_num_set` varchar(500) DEFAULT NULL,
  `card_num_now` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=105 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_sign`
--
-- 创建时间: 2014 年 01 月 12 日 06:16
-- 最后更新: 2014 年 06 月 06 日 01:22
--

CREATE TABLE IF NOT EXISTS `tp_member_card_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) NOT NULL,
  `wecha_id` varchar(50) NOT NULL,
  `sign_time` int(11) NOT NULL,
  `is_sign` int(11) NOT NULL,
  `score_type` int(11) NOT NULL,
  `expense` int(11) NOT NULL,
  `sell_expense` float(11,2) NOT NULL,
  `delete` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4699 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_card_vip`
--
-- 创建时间: 2014 年 01 月 04 日 07:57
-- 最后更新: 2014 年 06 月 05 日 09:48
--

CREATE TABLE IF NOT EXISTS `tp_member_card_vip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `title` varchar(60) NOT NULL,
  `groupid` tinyint(1) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `statdate` int(11) NOT NULL,
  `enddate` int(11) NOT NULL,
  `info` text NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=114 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_charge`
--
-- 创建时间: 2014 年 04 月 08 日 12:55
--

CREATE TABLE IF NOT EXISTS `tp_member_charge` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `wecha_id` varchar(60) NOT NULL,
  `card_num` varchar(20) NOT NULL,
  `token` varchar(60) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '0是充值，1是消费',
  `amount` decimal(13,2) NOT NULL COMMENT '充值或者消费的金额',
  `oprator` varchar(45) NOT NULL COMMENT '操作员',
  `createtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comment` varchar(200) DEFAULT NULL COMMENT '备注',
  `logon_user_id` int(11) NOT NULL COMMENT '作此操作的logonuser',
  `logon_ip` varchar(15) NOT NULL COMMENT '作此操作的logonIP',
  `isdelete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否是删除的',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=87 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_member_group`
--
-- 创建时间: 2014 年 04 月 21 日 12:11
--

CREATE TABLE IF NOT EXISTS `tp_member_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `groupid` tinyint(4) NOT NULL COMMENT '会员等级',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '为-1表示被删除了',
  `title` varchar(20) NOT NULL COMMENT '等级名称',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`,`groupid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='会员等级表，默认的等级信息存在Conf/member_group.php中' AUTO_INCREMENT=108 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_nearby_user`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2013 年 10 月 15 日 02:40
--

CREATE TABLE IF NOT EXISTS `tp_nearby_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(30) NOT NULL,
  `uid` varchar(32) NOT NULL,
  `keyword` varchar(100) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_node`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2014 年 04 月 03 日 14:55
--

CREATE TABLE IF NOT EXISTS `tp_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '节点名称',
  `title` varchar(50) NOT NULL COMMENT '菜单名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否激活 1：是 2：否',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注说明',
  `pid` smallint(6) unsigned NOT NULL COMMENT '父ID',
  `level` tinyint(1) unsigned NOT NULL COMMENT '节点等级',
  `data` varchar(255) DEFAULT NULL COMMENT '附加参数',
  `sort` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序权重',
  `display` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '菜单显示类型 0:不显示 1:导航菜单 2:左侧菜单',
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=107 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_oem_cfg`
--
-- 创建时间: 2014 年 03 月 21 日 07:06
--

CREATE TABLE IF NOT EXISTS `tp_oem_cfg` (
  `oem_cfg_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cfg_data` text COLLATE utf8_general_ci,
  `agent_id` int(10) unsigned NOT NULL,
  `domain` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`oem_cfg_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_openapi`
--
-- 创建时间: 2014 年 01 月 22 日 09:19
--

CREATE TABLE IF NOT EXISTS `tp_openapi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) DEFAULT NULL,
  `ourl` text,
  `keyword` varchar(45) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `user` varchar(45) DEFAULT NULL,
  `otoken` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_ordering_class`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2013 年 10 月 15 日 02:40
--

CREATE TABLE IF NOT EXISTS `tp_ordering_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `name` varchar(10) NOT NULL,
  `sort` tinyint(2) NOT NULL,
  `info` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_ordering_set`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
--

CREATE TABLE IF NOT EXISTS `tp_ordering_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `keyword` varchar(10) NOT NULL,
  `title` varchar(60) NOT NULL,
  `info` varchar(120) NOT NULL,
  `picurl` varchar(100) NOT NULL,
  `flash` text NOT NULL,
  `create_time` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_other`
--
-- 创建时间: 2013 年 11 月 17 日 14:39
-- 最后更新: 2014 年 06 月 06 日 01:36
--

CREATE TABLE IF NOT EXISTS `tp_other` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `keyword` varchar(60) NOT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=690 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_panorama`
--
-- 创建时间: 2014 年 04 月 14 日 16:30
-- 最后更新: 2014 年 06 月 06 日 01:36
-- 最后检查: 2014 年 04 月 14 日 16:30
--

CREATE TABLE IF NOT EXISTS `tp_panorama` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `galleryid` int(11) NOT NULL,
  `title` varchar(20) NOT NULL,
  `sort` tinyint(3) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `info` varchar(120) NOT NULL,
  `nav_info` varchar(2048) DEFAULT NULL COMMENT '使用array存放导航信息',
  PRIMARY KEY (`id`),
  KEY `galleryid_index` (`galleryid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2421 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_panorama_slices`
--
-- 创建时间: 2013 年 12 月 18 日 14:02
-- 最后更新: 2014 年 06 月 06 日 01:36
-- 最后检查: 2014 年 04 月 28 日 06:31
--

CREATE TABLE IF NOT EXISTS `tp_panorama_slices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `panorama_id` int(11) NOT NULL,
  `raw_image_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `panorama_image_uniqiue` (`panorama_id`,`raw_image_id`),
  KEY `panorama_id_index` (`panorama_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13379 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_photo`
--
-- 创建时间: 2013 年 12 月 06 日 07:33
-- 最后更新: 2014 年 06 月 05 日 16:19
--

CREATE TABLE IF NOT EXISTS `tp_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `title` varchar(20) NOT NULL,
  `picurl` varchar(255) NOT NULL,
  `isshoinfo` tinyint(1) NOT NULL,
  `num` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `create_time` int(11) NOT NULL,
  `info` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=225 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_photo_list`
--
-- 创建时间: 2013 年 12 月 06 日 07:33
-- 最后更新: 2014 年 06 月 05 日 16:19
--

CREATE TABLE IF NOT EXISTS `tp_photo_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `pid` int(11) NOT NULL,
  `title` varchar(20) NOT NULL,
  `sort` tinyint(3) NOT NULL,
  `picurl` varchar(255) NOT NULL,
  `create_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `info` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1370 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_physical_member`
--
-- 创建时间: 2014 年 04 月 08 日 12:58
--

CREATE TABLE IF NOT EXISTS `tp_physical_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) COLLATE utf8_general_ci NOT NULL,
  `cardnum` varchar(50) COLLATE utf8_general_ci NOT NULL,
  `tel` varchar(11) COLLATE utf8_general_ci NOT NULL,
  `sex` tinyint(1) NOT NULL,
  `birthday` varchar(11) COLLATE utf8_general_ci DEFAULT NULL,
  `token` varchar(60) COLLATE utf8_general_ci DEFAULT NULL,
  `province` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  `city` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
  `area` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  `address` varchar(100) COLLATE utf8_general_ci DEFAULT NULL,
  `updatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `binded` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tel` (`tel`,`token`),
  UNIQUE KEY `cardnum` (`cardnum`,`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=673 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_raw_image`
--
-- 创建时间: 2013 年 12 月 19 日 03:10
-- 最后更新: 2014 年 06 月 05 日 17:34
--

CREATE TABLE IF NOT EXISTS `tp_raw_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `raw_name` varchar(255) NOT NULL,
  `create_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `token` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15784 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_reply`
--
-- 创建时间: 2014 年 03 月 24 日 14:08
--

CREATE TABLE IF NOT EXISTS `tp_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `title` varchar(256) COLLATE utf8_general_ci DEFAULT NULL,
  `admins` text COLLATE utf8_general_ci,
  `status` int(11) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `head_pic_url` varchar(512) COLLATE utf8_general_ci DEFAULT NULL,
  `msg_pic_url` varchar(512) COLLATE utf8_general_ci DEFAULT NULL,
  `keyword` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `check` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_reply_impress`
--
-- 创建时间: 2014 年 03 月 26 日 13:27
--

CREATE TABLE IF NOT EXISTS `tp_reply_impress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `content` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL,
  `openid` varchar(45) COLLATE utf8_general_ci DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_reply_reply`
--
-- 创建时间: 2014 年 03 月 23 日 12:33
--

CREATE TABLE IF NOT EXISTS `tp_reply_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `rid` int(11) DEFAULT NULL,
  `nickname` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `text` text COLLATE utf8_general_ci,
  `createtime` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `openid` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `parentid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=120 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_role`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2014 年 02 月 19 日 08:54
--

CREATE TABLE IF NOT EXISTS `tp_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '后台组名',
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '是否激活 1：是 0：否',
  `sort` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序权重',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注说明',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_role_user`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2014 年 05 月 29 日 02:13
--

CREATE TABLE IF NOT EXISTS `tp_role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` smallint(6) unsigned NOT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_smsaccount`
--
-- 创建时间: 2014 年 01 月 17 日 04:49
-- 最后更新: 2014 年 06 月 05 日 12:17
--

CREATE TABLE IF NOT EXISTS `tp_smsaccount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '前台用户名',
  `total` varchar(60) NOT NULL COMMENT '全部短信数',
  `used` int(11) NOT NULL COMMENT '已使用短信数',
  `last_recharge_time` int(11) DEFAULT NULL COMMENT '上次充值时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0:无效 1:有效',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_index` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_sms_list`
--
-- 创建时间: 2014 年 04 月 19 日 16:06
--

CREATE TABLE IF NOT EXISTS `tp_sms_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `touser` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `sendtime` varchar(11) COLLATE utf8_general_ci DEFAULT NULL,
  `content` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
  `statusCode` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `func` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `charged_count` int(11) NOT NULL DEFAULT '1'  COMMENT '实际发送短信条数，超过70个中文字符或140个英文字符算两条短信',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=433 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_sms_set`
--
-- 创建时间: 2014 年 04 月 19 日 15:48
--

CREATE TABLE IF NOT EXISTS `tp_sms_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `function` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `tel` varchar(11) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_idx` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_snccode`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2013 年 10 月 15 日 02:40
--

CREATE TABLE IF NOT EXISTS `tp_snccode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) NOT NULL,
  `status` int(1) NOT NULL,
  `wechaname` varchar(60) NOT NULL,
  `caeatetime` int(11) NOT NULL,
  `phone` int(11) NOT NULL,
  `token` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_text`
--
-- 创建时间: 2013 年 10 月 22 日 15:53
-- 最后更新: 2014 年 06 月 06 日 02:19
--

CREATE TABLE IF NOT EXISTS `tp_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uname` varchar(90) DEFAULT NULL,
  `function` varchar(30) NOT NULL,
  `status` tinyint(3) NOT NULL,
  `keyword` char(255) NOT NULL,
  `type` varchar(1) NOT NULL,
  `text` text NOT NULL,
  `createtime` varchar(13) NOT NULL,
  `updatetime` varchar(13) NOT NULL,
  `click` int(11) NOT NULL DEFAULT '0',
  `token` char(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='文本回复表' AUTO_INCREMENT=3695 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_token_open`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2014 年 06 月 06 日 02:06
--

CREATE TABLE IF NOT EXISTS `tp_token_open` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `token` varchar(60) NOT NULL,
  `queryname` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=707 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_user`
--
-- 创建时间: 2014 年 02 月 20 日 12:04
-- 最后更新: 2014 年 06 月 06 日 01:42
--

CREATE TABLE IF NOT EXISTS `tp_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` char(32) NOT NULL,
  `role` smallint(6) unsigned NOT NULL COMMENT '组ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1:启用 0:禁止',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注说明',
  `last_login_time` int(11) unsigned NOT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(15) DEFAULT NULL COMMENT '最后登录IP',
  `last_location` varchar(100) DEFAULT NULL COMMENT '最后登录位置',
  `balance` double NOT NULL DEFAULT '0' COMMENT '代理商账户余额',
  `package_price` varchar(1024) DEFAULT NULL COMMENT '代理商的套餐价格（元）',
  `domain_prefix` varchar(63) DEFAULT NULL COMMENT 'oem域名前缀，如输入huayue,则可访问huayue.weixin.weixinwz.com',
  UNIQUE KEY `unique_user_domain_index` (`domain_prefix`),
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表' AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_userinfo`
--
-- 创建时间: 2014 年 04 月 30 日 08:08
-- 最后更新: 2014 年 06 月 06 日 01:22
-- 最后检查: 2014 年 04 月 30 日 08:08
--

CREATE TABLE IF NOT EXISTS `tp_userinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `wecha_id` varchar(60) NOT NULL,
  `wechaname` varchar(60) NOT NULL,
  `truename` varchar(60) NOT NULL,
  `tel` varchar(11) NOT NULL,
  `qq` int(11) NOT NULL,
  `sex` tinyint(1) NOT NULL,
  `age` int(3) NOT NULL,
  `birthday` varchar(11) NOT NULL,
  `address` varchar(100) NOT NULL,
  `info` varchar(200) NOT NULL,
  `total_score` int(11) NOT NULL,
  `sign_score` int(11) NOT NULL,
  `expend_score` int(11) NOT NULL,
  `continuous` int(11) NOT NULL,
  `add_expend` int(11) NOT NULL,
  `add_expend_time` int(11) NOT NULL,
  `live_time` int(11) NOT NULL,
  `getcardtime` int(11) DEFAULT NULL,
  `spend_score` int(11) NOT NULL,
  `total_money` decimal(10,0) DEFAULT '0',
  `spend_money` decimal(10,0) DEFAULT '0',
  `source` varchar(45) NOT NULL DEFAULT 'weixin',
  `memberinfo` varchar(1024) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0表示删除，1表示正常，删除时会将wecha_id加上以冒号为开头的后缀',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token_wecha_idx` (`token`,`wecha_id`),
  KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1689 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_users`
--
-- 创建时间: 2014 年 03 月 21 日 09:59
-- 最后更新: 2014 年 06 月 06 日 02:15
-- 最后检查: 2014 年 03 月 21 日 09:59
--

CREATE TABLE IF NOT EXISTS `tp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `pwd` varchar(32) DEFAULT NULL,
  `password` varchar(32) NOT NULL,
  `pos` varchar(63) DEFAULT NULL,
  `tel` varchar(90) NOT NULL,
  `name` varchar(127) NOT NULL,
  `industry` varchar(63) NOT NULL,
  `city` varchar(127) NOT NULL,
  `company` varchar(127) NOT NULL,
  `createtime` varchar(13) NOT NULL,
  `lasttime` varchar(13) NOT NULL,
  `status` varchar(1) NOT NULL,
  `createip` varchar(30) NOT NULL,
  `lastip` varchar(30) NOT NULL,
  `gid` int(11) DEFAULT NULL,
  `diynum` int(11) DEFAULT NULL,
  `activitynum` int(11) DEFAULT NULL,
  `card_num` int(11) DEFAULT NULL,
  `card_create_status` tinyint(1) DEFAULT NULL,
  `money` int(11) DEFAULT NULL,
  `viptime` varchar(13) DEFAULT NULL,
  `connectnum` int(11) DEFAULT '0',
  `administrator` int(10) DEFAULT NULL COMMENT '前台用户的管理员',
  `assign_time` int(11) DEFAULT NULL COMMENT '上次分配时间',
  `email` varchar(127) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `administrator_index` (`administrator`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='前台用户表' AUTO_INCREMENT=964 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_user_func_group`
--
-- 创建时间: 2013 年 12 月 10 日 07:23
--

CREATE TABLE IF NOT EXISTS `tp_user_func_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '1:开通 0:关闭',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_func_group_index` (`user_id`,`group_id`),
  KEY `idx_user_func_group` (`user_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=9410 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_user_group`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2013 年 10 月 15 日 02:40
--

CREATE TABLE IF NOT EXISTS `tp_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `diynum` int(11) NOT NULL,
  `connectnum` int(11) NOT NULL,
  `iscopyright` tinyint(1) NOT NULL,
  `activitynum` int(3) NOT NULL,
  `price` int(11) NOT NULL,
  `statistics_user` int(11) NOT NULL,
  `create_card_num` int(4) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_voice`
--
-- 创建时间: 2013 年 10 月 22 日 14:51
-- 最后更新: 2014 年 05 月 28 日 06:43
--

CREATE TABLE IF NOT EXISTS `tp_voice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `uname` varchar(90) NOT NULL,
  `token` char(30) NOT NULL,
  `function` varchar(30) NOT NULL,
  `status` tinyint(3) NOT NULL,
  `createtime` varchar(13) NOT NULL,
  `updatetime` varchar(13) NOT NULL,
  `keyword` char(255) NOT NULL,
  `title` varchar(60) NOT NULL,
  `musicurl` char(255) NOT NULL,
  `hqmusicurl` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='语音回复表' AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_vote`
--
-- 创建时间: 2014 年 05 月 28 日 09:10
--

CREATE TABLE IF NOT EXISTS `tp_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `content` text,
  `options` text,
  `author` varchar(45) DEFAULT NULL,
  `date` varchar(45) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `starttime` varchar(45) DEFAULT NULL,
  `endtime` varchar(45) DEFAULT NULL,
  `imgurl` text,
  `keyword` varchar(45) DEFAULT NULL,
  `token` varchar(45) DEFAULT NULL,
  `hots` int(11) DEFAULT '0',
  `icons` text,
  `share_limit` int(11) NOT NULL DEFAULT '0' COMMENT '0,不限制分享，1，限制分享',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=194 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_vote_join`
--
-- 创建时间: 2013 年 12 月 04 日 10:22
--

CREATE TABLE IF NOT EXISTS `tp_vote_join` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) DEFAULT NULL,
  `user` varchar(45) DEFAULT NULL,
  `optionid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11884 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_vweb_setting`
--
-- 创建时间: 2014 年 04 月 24 日 13:45
--

CREATE TABLE IF NOT EXISTS `tp_vweb_setting` (
  `web_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(255) COLLATE utf8_general_ci NOT NULL,
  `tmpl_id` int(11) NOT NULL,
  `tmpl_name` varchar(127) COLLATE utf8_general_ci NOT NULL,
  `bg_pic_url` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `bg_color` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  `classify_font_color` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  `classify_bg_color` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  `show_nav` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '1',
  `navi_bg_color` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`web_id`),
  KEY `token` (`token`) USING HASH
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=985 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wall`
--
-- 创建时间: 2014 年 03 月 23 日 11:32
--

CREATE TABLE IF NOT EXISTS `tp_wall` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `keyword` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `title` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `name` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `logo` varchar(256) COLLATE utf8_general_ci DEFAULT NULL,
  `description` text COLLATE utf8_general_ci,
  `backgroud_pic_url` varchar(256) COLLATE utf8_general_ci DEFAULT NULL,
  `music_url` varchar(256) COLLATE utf8_general_ci DEFAULT NULL,
  `qrcode_url` varchar(256) COLLATE utf8_general_ci DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `start_time` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `end_time` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `num` int(11) DEFAULT '0',
  `lotterys` text COLLATE utf8_general_ci,
  `name_prefix` varchar(45) DEFAULT NULL,
  `lot_prefix` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wall_reply`
--
-- 创建时间: 2014 年 05 月 16 日 07:42
--

CREATE TABLE IF NOT EXISTS `tp_wall_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) DEFAULT NULL,
  `wall_id` int(11) DEFAULT NULL,
  `text` text,
  `createtime` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `nickname` varchar(45) DEFAULT NULL,
  `headimgurl` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `wecha_id` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=102 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wall_winner`
--
-- 创建时间: 2014 年 05 月 16 日 07:44
--

CREATE TABLE IF NOT EXISTS `tp_wall_winner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `lottery` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `headimgurl` text COLLATE utf8_general_ci ,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `wall_id` int(11) DEFAULT NULL,
  `wecha_id` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE utf8_general_ci AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_weather`
--
-- 创建时间: 2013 年 10 月 15 日 02:40
-- 最后更新: 2013 年 10 月 15 日 02:41
--

CREATE TABLE IF NOT EXISTS `tp_weather` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `code` char(9) NOT NULL,
  `name` varchar(16) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2502 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wecha_user`
--
-- 创建时间: 2014 年 05 月 16 日 07:45
-- 最后更新: 2014 年 06 月 05 日 14:18
--

CREATE TABLE IF NOT EXISTS `tp_wecha_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(60) NOT NULL,
  `wecha_id` varchar(60) NOT NULL,
  `userinfo` text,
  `nickname` varchar(45) DEFAULT NULL,
  `truename` varchar(45) DEFAULT NULL,
  `birthday` varchar(11) DEFAULT NULL,
  `address` text,
  `sex` tinyint(1) DEFAULT NULL,
  `tel` varchar(11) DEFAULT NULL,
  `headimgurl` varchar(255) DEFAULT NULL,
  `city` varchar(127) DEFAULT NULL,
  `province` varchar(127) DEFAULT NULL,
  `country` varchar(63) DEFAULT NULL,
  `updatetime` int(11) DEFAULT NULL,
  `wallopen` ENUM('0', '1') DEFAULT '0',
  `keyword` VARCHAR(100) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index` (`token`,`wecha_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1799 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wedding`
--
-- 创建时间: 2014 年 03 月 10 日 13:53
--

CREATE TABLE IF NOT EXISTS `tp_wedding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `keyword` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `title` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `man` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `woman` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `password` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `description` text COLLATE utf8_general_ci,
  `msg_pic_url` text COLLATE utf8_general_ci,
  `head_pic_url` text COLLATE utf8_general_ci,
  `wedding_pic_urls` text COLLATE utf8_general_ci,
  `tel` varchar(11) COLLATE utf8_general_ci DEFAULT NULL,
  `wedding_time` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `wedding_address` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `longtitude` float DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `wedding_music_url` text COLLATE utf8_general_ci,
  `createtime` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `qrcode_url` text COLLATE utf8_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wedding_reply`
--
-- 创建时间: 2014 年 03 月 10 日 14:14
--

CREATE TABLE IF NOT EXISTS `tp_wedding_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `tel` varchar(45) COLLATE utf8_general_ci DEFAULT NULL,
  `text` text COLLATE utf8_general_ci,
  `createtime` int(11) DEFAULT NULL,
  `wedding_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wifi_ap`
--
-- 创建时间: 2014 年 02 月 20 日 03:57
--

CREATE TABLE IF NOT EXISTS `tp_wifi_ap` (
  `ap_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商家wifi信息',
  `merchant_id` varchar(127) NOT NULL,
  `token` varchar(127) NOT NULL,
  `type` tinyint(3) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `merchant_name` varchar(127) NOT NULL,
  `resp_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1为文本 2为图文',
  PRIMARY KEY (`ap_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- 表的结构 `tp_wxuser`
--
-- 创建时间: 2014 年 05 月 13 日 14:03
-- 最后更新: 2014 年 06 月 06 日 01:37
-- 最后检查: 2014 年 05 月 13 日 14:03
--

CREATE TABLE IF NOT EXISTS `tp_wxuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `wxname` varchar(60) NOT NULL COMMENT '公众号名称',
  `wxid` varchar(20) NOT NULL COMMENT '公众号原始ID',
  `weixin` char(20) NOT NULL COMMENT '微信号',
  `token` char(255) NOT NULL,
  `company` varchar(127) DEFAULT NULL,
  `code` int(11) unsigned DEFAULT NULL COMMENT '商家唯一6位数字id,操作员登陆时可以唯一标识商家',
  `telephone` char(25) DEFAULT NULL COMMENT '公众号邮箱',
  `typeid` int(11) NOT NULL COMMENT '分类ID',
  `typename` varchar(90) NOT NULL DEFAULT '0' COMMENT '分类名',
  `createtime` varchar(13) NOT NULL,
  `tpltypeid` varchar(2) NOT NULL DEFAULT '1' COMMENT '默认首页模版ID',
  `updatetime` varchar(13) NOT NULL,
  `tpltypename` varchar(20) NOT NULL DEFAULT 'index_hotel' COMMENT '首页模版名',
  `tpllistid` varchar(2) NOT NULL COMMENT '列表模版ID',
  `tpllistname` varchar(20) NOT NULL COMMENT '列表模版名',
  `status` tinyint(3) NOT NULL DEFAULT '1',
  `appid` varchar(45) DEFAULT NULL,
  `appsecret` varchar(45) DEFAULT NULL,
  `type` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0' COMMENT '0是订阅号，1是服务号',
  `address` varchar(256) DEFAULT NULL,
  `longtitude` varchar(32) DEFAULT NULL,
  `latitude` varchar(32) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL COMMENT '公司描述',
  `picture` int(11) DEFAULT NULL COMMENT '公司展示图片ID',
  `is_authed` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0' COMMENT '0是没认证 1是经过认证',
  `qrcode_pic` varchar(255) DEFAULT NULL COMMENT '二维码地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `code` (`code`) USING HASH,
  KEY `uid` (`uid`),
  KEY `uid_2` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='微信公共帐号' AUTO_INCREMENT=747 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_wx_access`
--
-- 创建时间: 2014 年 01 月 03 日 07:18
--

CREATE TABLE IF NOT EXISTS `tp_wx_access` (
  `access_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `to_public_token` varchar(127) CHARACTER SET utf8 NOT NULL,
  `to_public_id` varchar(127) COLLATE utf8_general_ci NOT NULL,
  `msg_type` varchar(15) COLLATE utf8_general_ci NOT NULL,
  `from_user` varchar(127) COLLATE utf8_general_ci NOT NULL,
  `receive_time` int(10) NOT NULL,
  `msg_content` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `msg_id` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  `create_time` int(10) NOT NULL,
  `event` varchar(15) COLLATE utf8_general_ci DEFAULT NULL,
  `event_key` varchar(127) COLLATE utf8_general_ci DEFAULT NULL,
  `location_x` varchar(63) COLLATE utf8_general_ci DEFAULT NULL,
  `location_y` varchar(63) COLLATE utf8_general_ci DEFAULT NULL,
  `scale` int(10) DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`access_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=166664 ;

-- --------------------------------------------------------

--
-- 表的结构 `tp_department`
--
-- 创建时间: 2014 年 09 月 11 日 13:09
--

CREATE TABLE `tp_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `token` varchar(30) DEFAULT NULL,
  `pid` int(20) DEFAULT NULL,
  `title` varchar(60) DEFAULT NULL,
  `level` int(10) DEFAULT NULL,
  `sort` int(1) DEFAULT NULL,
  `status` enum('0','1') DEFAULT '1' COMMENT '0 delete 1.default',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_partners`
--
-- 创建时间: 2014 年 09 月 11 日 13:09
--

CREATE TABLE `tp_partner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL DEFAULT '',
  `num` varchar(100) NOT NULL DEFAULT '',
  `mobile` varchar(50) NOT NULL DEFAULT '',
  `bank_account` varchar(120) NOT NULL DEFAULT '',
  `address` text,
  `token` varchar(30) NOT NULL DEFAULT '',
  `status` enum('0','1') DEFAULT '1' COMMENT '0 delete 1.default',
  `create_time` int(11) DEFAULT NULL,
  `qrcode_pic_url` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `depid` (`dept_id`)
  UNIQUE KEY `uk_num` (`num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- 表的结构 `tp_sign_user`
--
-- 创建时间: 2014 年 09 月 20 日 
--

CREATE TABLE IF NOT EXISTS `tp_sign_user`(
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `phone`  VARCHAR(11) NOT NULL,
  `sex` ENUM('0', '1') DEFAULT '0',
  `birthday` VARCHAR(11) NOT NULL,
  `token` CHAR(40) NOT NULL,
  `wecha_id` CHAR(60) NOT NULL,
  `create_time` INT(11),
 PRIMARY KEY(`id`)
)ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_sign_in`
--
-- 创建时间: 2014 年 09 月 20 日 
--

CREATE TABLE IF NOT EXISTS `tp_sign_in`(
  `sign_id` INT(11) NOT NULL AUTO_INCREMENT,
  `integral` TINYINT(4) NOT NULL,
  `sign_time` INT(11),
  `continue` INT NOT NULL,
  `user_id` INT NOT NULL,
 PRIMARY KEY(`sign_id`)
)ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_sign_conf`
--
-- 创建时间: 2014 年 09 月 20 日 
--

CREATE TABLE IF NOT EXISTS `tp_sign_conf` (
  `conf_id` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `use` ENUM('0','1') NOT NULL,
  `integral` TINYINT(4) NOT NULL,
  `stair` TINYINT(4) NOT NULL,
  `token` CHAR(25) NOT NULL,
  PRIMARY KEY (`conf_id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_sign_set`
--
-- 创建时间: 2014 年 09 月 20 日 
--

CREATE TABLE IF NOT EXISTS `tp_sign_set` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `keywords` CHAR(25) NOT NULL,
  `title` CHAR(60) NOT NULL,
  `content` VARCHAR(250) NOT NULL,
  `token` CHAR(35) NOT NULL,
  `reply_img` CHAR(150) NOT NULL,
  `top_pic` CHAR(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_files`
--
-- 创建时间: 2014 年 09 月 20 日 
--

CREATE TABLE IF NOT EXISTS `tp_files` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `token` varchar(30) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `size` int(10) NOT NULL DEFAULT '0',
  `type` varchar(20) NOT NULL DEFAULT '',
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- 表的结构 `tp_reguser`
--
-- 创建时间: 2014 年 10 月 20 日 
--

CREATE TABLE IF NOT EXISTS `tp_reguser` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` char(32) DEFAULT NULL,
  `mb` varchar(20) DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '状态 1:启用 0:禁止',
  `email` varchar(255) DEFAULT NULL COMMENT '邮件',
  `last_login_time` int(11) unsigned DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(15) DEFAULT NULL COMMENT '最后登录IP',
  `last_location` varchar(100) DEFAULT NULL COMMENT '最后登录位置',
  `idnum` varchar(20) DEFAULT '0' COMMENT '身份证号',
  `wxnumber` varchar(1024) DEFAULT NULL COMMENT '微信号',
  `province` varchar(63) DEFAULT NULL COMMENT '所在省',
  `city` varchar(63) DEFAULT NULL COMMENT '所在市',
  `area` varchar(63) DEFAULT NULL COMMENT '所在区',
  `address` varchar(63) DEFAULT NULL COMMENT '详细地址',
  `publicnumber` varchar(63) DEFAULT NULL COMMENT '公众号',
  `license_logo` varchar(250) DEFAULT NULL COMMENT '营业执照',
  `tenpay` varchar(63) DEFAULT NULL COMMENT '财付通账号',
  `alipay` varchar(63) DEFAULT NULL COMMENT '支付宝账号',
  `truename` varchar(63) DEFAULT NULL,
  `company` varchar(63) DEFAULT NULL,
  `qrcode_pic_url` varchar(250) DEFAULT NULL,
  `num` varchar(60) DEFAULT NULL,
  `create_time` int(20) DEFAULT NULL,
  `token` varchar(60) DEFAULT NULL,
  `pid` int(60) DEFAULT NULL,
  `code` int(20) DEFAULT NULL,
  `openid` varchar(60) DEFAULT NULL,
  `pic_url_link` varchar(260) DEFAULT NULL,
  `update_time` int(20) DEFAULT NULL,
  `headimgurl` varchar(280) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='分销商注册表'


-- --------------------------------------------------------

--
-- 表的结构 `tp_push_info`
--
-- 创建时间: 2014 年 11 月 11 日 
--

CREATE TABLE IF NOT EXISTS `tp_push_info`(
	`openid` VARCHAR(150),
	`wechat_id` VARCHAR(100),
	`content` TEXT,
	`create_time` INT(11),
	`update_time` INT(11),
	PRIMARY KEY(`openid`, `wechat_id`)	
)ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_course`
--
-- 创建时间: 2014 年 11 月 13 日 
--
CREATE TABLE IF NOT EXISTS `tp_course` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `course` VARCHAR(200) DEFAULT NULL,
  `token` CHAR(100) DEFAULT NULL,
  `create_time` INT(11) DEFAULT NULL,
  `sort` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `tp_b2c_wingtrade`
--
-- 创建时间: 2014 年 12 月 02 日 
--
CREATE TABLE IF NOT EXISTS `tp_b2c_wingtrade`(
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(100) NOT NULL,
  `order_sn` VARCHAR(50) NOT NULL,
  `is_pay` ENUM('0', '1')  DEFAULT '0',
  `set_params` TEXT,
  `return_params` TEXT, 
  `create_time` INT,
  `update_time` INT,  
  PRIMARY KEY(`id`)
)ENGINE=MYISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- 北大总裁班节目评选活动 表的结构 `tp_vote_item`
--
-- 创建时间: 2015 年 01 月 08 日 
-- 
--
CREATE TABLE `tp_vote_item` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `wechat_user_id` INT(11) DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `item` VARCHAR(50) DEFAULT NULL,
  `create_time` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
--
-- 北大总裁班参会申请人员 `tp_attend_apply`
--
-- 创建时间: 2015 年 03 月 02 日 
-- 
CREATE TABLE `tp_attend_apply`(
	`id` INT NOT NULL AUTO_INCREMENT,
	`token` varchar(30),
	`openid`  varchar(60),
	`name` VARCHAR(100) COMMENT '姓名',
	`contact` varchar(20)  COMMENT '联系人手机号码',
	`attend_num` INT DEFAULT 0  COMMENT '出席人数',
	`room_type` ENUM('0', '1') DEFAULT '0' COMMENT '0 标间 1 单间',
	`checkin_time` DATETIME COMMENT '入住时间',
	`checkout_time` DATETIME COMMENT '退房时间',
  `create_time` DATETIME COMMENT '创建时间',
  `update_time` DATETIME COMMENT '更新时间',
	PRIMARY KEY(`id`)
)ENGINE=mysql DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
--
-- 微信红包 表结构
--
-- 创建时间: 2015 年 04 月 15 日 
-- 
CREATE TABLE IF NOT EXISTS `tp_redcash_wxconf` (
 `id` INT NOT NULL AUTO_INCREMENT,
 `appid` VARCHAR(60) NOT NULL,
 `key` VARCHAR(32) NOT NULL,
 `mchid` VARCHAR(30) NOT NULL,
 `token` VARCHAR(30) NOT NULL,
 `ssl_cert` VARCHAR(200) DEFAULT '',
 `ssl_key` VARCHAR(200) DEFAULT '',
 `ssl_cainfo` VARCHAR(200) DEFAULT '',
 `create_time` DATETIME,
 `update_time` DATETIME,
 PRIMARY KEY(`id`)
)ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tp_redcash_setting` (
 `id` INT NOT NULL AUTO_INCREMENT,
 `keyword` VARCHAR(100) NOT NULL,
 `min_value` DECIMAL(10,2),
 `max_value` DECIMAL(10,2), 
 `fixed_amount` DECIMAL(10,2), 
 `status` ENUM('0', '1', '2', '3') DEFAULT '0' COMMENT '0 默认 1 开始 2 结束 3 删除',
 `nick_name` VARCHAR(50) NOT NULL,
 `send_name` VARCHAR(50) NOT NULL,
 `wishing` VARCHAR(50) NOT NULL,
 `act_name` VARCHAR(50) NOT NULL,
 `remark` VARCHAR(100) NOT NULL,
 `token` VARCHAR(30) NOT NULL,
 `start_time` VARCHAR(30),
 `end_time` VARCHAR(30), 
 `create_time` DATETIME,
 `update_time` DATETIME,
 PRIMARY KEY(`id`)
)ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tp_redcash_list` (
 `id` INT NOT NULL AUTO_INCREMENT,
 `cashsetting_id` INT NOT NULL,
 `openid` VARCHAR(50) NOT NULL, 
 `token` VARCHAR(30) NOT NULL,
 `mch_billno` VARCHAR(50), 
 `total_amount` INT,
 `err_code` VARCHAR(50),
 `err_code_des` TEXT,
 `param_msg` TEXT,
 `return_msg` TEXT,
 `create_time` DATETIME,
 `update_time` DATETIME,
 PRIMARY KEY(`id`)
)ENGINE=MYISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 04 月 29 日 06:39
-- 服务器版本: 5.5.31
-- PHP 版本: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: 'lingzh'
--
CREATE DATABASE IF NOT EXISTS lingzh DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE lingzh;

--
-- 插入之前先把表清空（truncate） 'tp_function'
--

TRUNCATE TABLE tp_function;
--
-- 转存表中的数据 'tp_function'
--

INSERT INTO tp_function (id, gid, usenum, name, funname, info, isserve, keywords, status, fgid) VALUES
(1, 1, 0, '天气查询', 'tianqi', '天气查询服务:例  城市名+天气', 1, '天气', 1, 8),
(19, 4, 0, '幸运大转盘', 'dazhuanpan', '输入抽奖　即可参加幸运大转盘抽奖活动', 2, NULL, 1, 6),
(22, 1, 0, '翻译', 'fanyi', '翻译＋关键词 例：翻译你好', 1, '翻译', 1, 8),
(27, 4, 0, '优惠券', 'youhuiquan', '抽奖,输入抽奖即可参加幸运大转盘', 1, NULL, 1, 6),
(28, 4, 0, '刮刮卡', 'guaguaka', '刮刮卡抽奖活动', 1, NULL, 1, 6),
(29, 1, 0, '自助建站', 'shouye', '输入首页,访问微3g 网站', 2, '', 1, 1),
(31, 4, 0, '会员卡', 'huiyuanka', '尊贵享受vip会员卡,回复会员卡即可领取会员卡', 1, '会员卡', 1, 3),
(40, 4, 0, '快递查询', 'kuaidi', '发送“顺丰快递+单号”，不含+号', 1, '快递 物流', 1, 8),
(37, 4, 0, '微信相册', 'xiangce', '用户在发送“相册”关键词或在关注时可直接推送相册链接', 2, '相册', 1, 5),
(38, 4, 0, '微信客服', 'kefu', '商户通过特定关键词完成客服功能', 1, NULL, 1, 0),
(39, 4, 0, '预约/报名/订单系统', 'dingdan', '商户设置订单内容，用户可通过点击下单或预约', 2, NULL, 1, 7),
(42, 4, 0, '投票', 'toupiao', '组织投票、调查、测试，帮助企业轻松完成市场数据收集', 1, NULL, 1, 6),
(41, 4, 0, 'Wifi配置', 'wifi', '商户设定关键词，客户通过发送关键词获取wifi接入密码', 1, NULL, 1, 10),
(43, 4, 0, '彩票', 'caipiao', '发送“彩票+彩票名”，如“彩票大乐透”，不含+号', 1, '彩票', 1, 8),
(44, 4, 0, '电商行业方案', 'shangcheng', '微信商城，支持货到付款和支付宝支付', 1, NULL, 1, 4),
(45, 4, 0, '第三方接口', 'disanfang', '接入第三方应用', 2, NULL, 1, 11),
(46, 4, 0, '3D全景相册', 'panorama', '用户在发送“全景相册”关键词或在关注时可直接推送相册链接', 2, NULL, 1, 12),
(47, 4, 0, '餐饮行业方案', 'canyin', '微餐饮，支持微信点餐下单', 1, NULL, 1, 17),
(48, 4, 0, '宾馆行业方案', 'binguan', '微宾馆，轻松预订宾馆', 1, NULL, 1, 18),
(49, 4, 0, '婚庆行业方案', 'hunqing', '微喜帖，轻松编辑微信喜帖', 1, NULL, 1, 19),
(50, 4, 0, '房产行业方案', 'fangchan', '微房产，微信看房', 1, NULL, 1, 20),
(51, 4, 0, '汽车行业方案', 'car', '4S店，汽服', 1, NULL, 1, 21),
(52, 4, 0, '微现场', 'xianchang', '微现场，支持微信墙，抽奖', 1, NULL, 1, 22),
(53, 4, 0, '微评论', 'pinglun', '微评论，支持微信留言与答复', 1, NULL, 1, 23),
(54, 4, 0, '微印象', 'yingxiang', '微印象，支持微信标签', 1, NULL, 1, 23),
(55, 4, 0, '砸金蛋', 'zajindan', '砸金蛋活动', 1, NULL, 1, 6);

--
-- 插入之前先把表清空（truncate） 'tp_function_group'
--

TRUNCATE TABLE tp_function_group;
--
-- 转存表中的数据 'tp_function_group'
--

INSERT INTO tp_function_group (id, name, status, visible, sort) VALUES
(0, '微信客服', 1, 1, 0),
(1, '微信网站', 1, 1, 0),
(3, '会员卡', 1, 1, 0),
(4, '电商行业方案', 1, 1, 20),
(5, '微信相册', 1, 1, 0),
(6, '互动营销工具', 1, 1, 0),
(7, '预约/报名/订单系统', 1, 1, 0),
(8, '实用生活工具', 1, 1, 0),
(9, '微信推广页', 1, 0, 0),
(10, 'Wifi', 1, 1, 0),
(11, '第三方接口', 1, 1, 0),
(12, '3D全景相册', 1, 1, 0),
(13, '自定义版权', 1, 0, 0),
(14, '微网站模版', 1, 0, 0),
(16, '商城模版', 1, 0, 0),
(17, '餐饮行业方案', 1, 1, 21),
(18, '宾馆行业方案', 1, 1, 22),
(19, '婚庆行业方案', 1, 1, 23),
(20, '房地行业方案', 1, 1, 24),
(21, '汽车行业方案', 1, 1, 25),
(22, '微现场', 1, 1, 0),
(23, '微评论', 1, 1, 0);

--
-- 插入之前先把表清空（truncate） 'tp_node'
--

TRUNCATE TABLE tp_node;
--
-- 转存表中的数据 'tp_node'
--

INSERT INTO tp_node (id, name, title, status, remark, pid, level, data, sort, display) VALUES
(1, 'cms', '根节点', 1, '', 0, 1, NULL, 0, 0),
(2, 'Site', '站点管理', 1, '', 1, 0, NULL, 0, 1),
(3, 'User', '用户管理', 1, '', 1, 0, NULL, 0, 1),
(4, 'extent', '扩展管理', 1, '', 1, 0, NULL, 0, 0),
(5, 'article', '内容管理', 1, '', 1, 0, NULL, 0, 0),
(6, 'Site', '站点设置', 1, '', 2, 2, NULL, 0, 2),
(7, 'index', '基本信息设置', 1, '', 6, 3, NULL, 0, 2),
(8, 'safe', '安全设置', 1, '', 6, 3, NULL, 0, 2),
(9, 'email', '邮箱设置', 1, '', 6, 3, NULL, 0, 2),
(10, 'upfile', '附件设置', 1, '', 6, 3, NULL, 0, 2),
(11, 'Node', '节点管理', 1, NULL, 2, 2, NULL, 0, 2),
(12, 'add', '添加节点', 1, '', 11, 3, NULL, 0, 2),
(13, 'index', '节点列表', 1, '', 11, 3, NULL, 0, 2),
(14, 'insert', '写入', 1, '0', 11, 3, NULL, 0, 0),
(15, 'edit', '编辑节点', 1, '0', 11, 3, NULL, 0, 0),
(16, 'update', '更新节点', 1, '0', 11, 3, NULL, 0, 0),
(17, 'del', '删除节点', 1, '0', 11, 3, NULL, 0, 0),
(18, 'User', '后台用户', 1, '0', 3, 2, NULL, 0, 2),
(19, 'add', '添加用户', 1, '0', 18, 3, NULL, 0, 2),
(20, 'index', '用户列表', 1, '0', 18, 3, NULL, 0, 2),
(21, 'edit', '编辑用户', 1, '0', 18, 3, NULL, 0, 0),
(22, 'insert', '写入数据库', 1, '0', 18, 3, NULL, 0, 0),
(23, 'update', '更新用户', 1, '0', 18, 3, NULL, 0, 0),
(24, 'del', '删除用户', 1, '0', 18, 3, NULL, 0, 0),
(25, 'Group', '角色管理', 1, '0', 3, 2, NULL, 0, 2),
(26, 'add', '创建用户组', 1, '0', 25, 3, NULL, 0, 2),
(27, 'index', '用户组列表', 1, '0', 25, 3, NULL, 0, 2),
(28, 'edit', '编辑用户组', 1, '0', 25, 3, NULL, 0, 0),
(29, 'del', '删除用户组', 1, '0', 25, 3, NULL, 0, 0),
(30, 'insert', '写入数据库', 1, '0', 25, 3, NULL, 0, 0),
(31, 'update', '更新用户组', 1, '0', 25, 3, NULL, 0, 0),
(32, 'insert', '保存测试', 1, '0', 6, 3, NULL, 0, 0),
(36, 'menu', '左侧栏', 1, '0', 35, 3, NULL, 0, 0),
(35, 'System', '首页', 1, '0', 2, 2, NULL, 0, 0),
(37, 'main', '右侧栏目', 1, '0', 35, 3, NULL, 0, 0),
(38, 'Article', '微信图文', 1, '0', 5, 2, NULL, 0, 2),
(39, 'index', '图文列表', 1, '0', 38, 3, NULL, 0, 2),
(40, 'add', '图文添加', 1, '0', 38, 3, NULL, 0, 2),
(41, 'edit', '微信图文编辑', 1, '0', 38, 3, NULL, 0, 0),
(42, 'del', '微信图文删除', 1, '0', 38, 3, NULL, 0, 0),
(80, 'token', '公众号管理', 1, '0', 1, 2, NULL, 0, 1),
(45, 'Function', '功能模块', 1, '0', 1, 0, NULL, 0, 0),
(46, 'Function', '功能模块', 1, '0', 45, 2, NULL, 0, 2),
(47, 'add', '添加模块', 1, '0', 46, 3, NULL, 0, 2),
(48, 'User_group', '会员组', 1, '0', 3, 2, NULL, 0, 0),
(49, 'add', '添加会员组', 1, '0', 48, 3, NULL, 0, 2),
(50, 'Users', '注册用户', 1, '0', 3, 2, NULL, 0, 2),
(51, 'index', '用户列表', 1, '0', 50, 3, NULL, 0, 0),
(52, 'add', '添加用户', 1, '0', 50, 3, NULL, 0, 2),
(53, 'edit', '编辑用户', 1, '0', 50, 3, NULL, 0, 0),
(54, 'del', '删除用户', 1, '0', 50, 3, NULL, 0, 0),
(55, 'insert', '写入数据库', 1, '0', 50, 3, NULL, 0, 0),
(56, 'upsave', '更新用户', 1, '0', 50, 3, NULL, 0, 0),
(57, 'Text', '微信文本', 1, '0', 5, 2, NULL, 0, 2),
(58, 'index', '文本列表', 1, '0', 57, 3, NULL, 0, 2),
(59, 'del', '删除', 1, '0', 57, 3, NULL, 0, 0),
(60, 'Custom', '自定义页面', 1, '0', 5, 2, NULL, 0, 2),
(61, 'index', '列表', 1, '0', 60, 3, NULL, 0, 2),
(62, 'edit', '编辑', 1, '0', 60, 3, NULL, 0, 0),
(63, 'del', '删除', 1, '0', 60, 3, NULL, 0, 0),
(64, 'Records', '充值记录', 1, '0', 4, 2, NULL, 0, 0),
(65, 'index', '充值列表', 1, '0', 64, 3, NULL, 0, 2),
(66, 'Case', '用户案例', 1, '0', 4, 2, NULL, 0, 0),
(67, 'index', '案例列表', 1, '0', 66, 3, NULL, 0, 2),
(68, 'add', '添加案例', 1, '0', 66, 3, NULL, 0, 2),
(69, 'edit', '编辑案例', 1, '0', 66, 3, NULL, 0, 0),
(70, 'del', '删除案例', 1, '0', 66, 3, NULL, 0, 0),
(71, 'insert', '写入数据库', 1, '0', 66, 3, NULL, 0, 0),
(72, 'upsave', '更新数据库', 1, '0', 66, 3, NULL, 0, 0),
(73, 'Links', '友情链接', 1, '0', 4, 2, NULL, 0, 0),
(74, 'index', '友情链接', 1, '0', 73, 3, NULL, 0, 2),
(75, 'add', '添加链接', 1, '0', 73, 3, NULL, 0, 0),
(76, 'edit', '编辑链接', 1, '0', 73, 3, NULL, 0, 0),
(77, 'insert', '插入数据库', 1, '0', 73, 3, NULL, 0, 0),
(78, 'upsave', '更新数据库', 1, '0', 73, 3, NULL, 0, 0),
(79, 'del', '删除友情链接', 1, '0', 73, 3, NULL, 0, 0),
(81, 'Token', '公众号管理', 1, '0', 80, 2, NULL, 0, 2),
(83, 'alipay', '在线支付接口', 1, '0', 6, 3, NULL, 0, 2),
(85, 'wxm', '微活动管理平台', 1, '0', 0, 1, NULL, 0, 0),
(86, 'User', '微活动设置平台', 1, '0', 85, 2, NULL, 0, 0),
(87, 'Customer', '微活动兑奖', 1, '0', 85, 2, NULL, 0, 0),
(88, 'InviteCode', '充值码管理', 1, '0', 3, 2, NULL, 0, 2),
(89, 'index', '邀请码列表', 1, '0', 88, 3, NULL, 0, 0),
(90, 'generate', '生成邀请码', 1, '0', 88, 3, NULL, 0, 0),
(91, 'assign_manager', '分配邀请码', 1, '0', 88, 3, NULL, 0, 0),
(92, 'del', '删除邀请码', 1, '0', 88, 3, NULL, 0, 0),
(94, 'activate', '授权', 1, '0', 50, 3, NULL, 0, 0),
(95, 'index', '首页', 1, '0', 35, 3, NULL, 0, 0),
(96, 'updatefuncgroup', '修改功能组', 1, '0', 50, 3, NULL, 0, 0),
(97, 'search', '搜索', 1, '0', 50, 3, NULL, 0, 0),
(98, 'WifiConfig', 'Witown接入配置', 1, '0', 3, 2, NULL, 0, 2),
(99, 'set', 'set', 1, '0', 98, 3, NULL, 0, 2),
(100, 'index', 'index', 1, '0', 98, 3, NULL, 0, 2),
(101, 'Sms', '短信平台', 1, '0', 3, 2, NULL, 0, 2),
(102, 'generate_package', '套餐码生成', 1, '0', 88, 3, NULL, 0, 0),
(103, 'OEMConfig', 'OEM配置', 1, '0', 3, 2, NULL, 0, 2),
(104, 'index', 'index', 1, '0', 103, 3, NULL, 0, 2),
(105, 'set', 'set', 1, '0', 103, 3, NULL, 0, 2),
(106, 'updatefgtime', '修改功能组时间', 1, '0', 50, 3, NULL, 0, 0);

--
-- 插入之前先把表清空（truncate） 'tp_role'
--

TRUNCATE TABLE tp_role;
--
-- 转存表中的数据 'tp_role'
--

INSERT INTO tp_role (id, name, pid, status, sort, remark) VALUES
(5, '超级管理员', 0, 1, 0, ''),
(14, '管理员', 0, 1, 0, '销售主管专用'),
(13, '直销销售', 0, 1, 0, ''),
(12, '代理商', 0, 1, 0, NULL),
(18, 'OEM', 0, 1, 0, '个性化oem');


--
-- 转存表中的数据 `tp_access`
--

INSERT INTO `tp_access` (`role_id`, `node_id`, `pid`, `level`, `module`) VALUES
(13, 100, 98, 3, NULL),
(12, 102, 88, 3, NULL),
(5, 63, 60, 3, NULL),
(5, 62, 60, 3, NULL),
(5, 61, 60, 3, NULL),
(13, 99, 98, 3, NULL),
(13, 98, 3, 2, NULL),
(13, 102, 88, 3, NULL),
(13, 89, 88, 3, NULL),
(5, 60, 5, 2, NULL),
(5, 59, 57, 3, NULL),
(12, 89, 88, 3, NULL),
(5, 58, 57, 3, NULL),
(5, 57, 5, 2, NULL),
(5, 42, 38, 3, NULL),
(12, 88, 3, 2, NULL),
(13, 88, 3, 2, NULL),
(5, 41, 38, 3, NULL),
(5, 40, 38, 3, NULL),
(5, 39, 38, 3, NULL),
(12, 106, 50, 3, NULL),
(13, 106, 50, 3, NULL),
(5, 38, 5, 2, NULL),
(5, 5, 1, 0, NULL),
(5, 4, 1, 0, NULL),
(5, 105, 103, 3, NULL),
(5, 104, 103, 3, NULL),
(5, 103, 3, 2, NULL),
(5, 101, 3, 2, NULL),
(5, 100, 98, 3, NULL),
(5, 99, 98, 3, NULL),
(5, 98, 3, 2, NULL),
(5, 92, 88, 3, NULL),
(13, 97, 50, 3, NULL),
(13, 96, 50, 3, NULL),
(13, 94, 50, 3, NULL),
(13, 53, 50, 3, NULL),
(13, 52, 50, 3, NULL),
(13, 51, 50, 3, NULL),
(13, 50, 3, 2, NULL),
(13, 3, 1, 0, NULL),
(13, 95, 35, 3, NULL),
(13, 37, 35, 3, NULL),
(13, 36, 35, 3, NULL),
(13, 35, 2, 2, NULL),
(13, 2, 1, 0, NULL),
(13, 1, 0, 1, NULL),
(12, 97, 50, 3, NULL),
(12, 96, 50, 3, NULL),
(12, 94, 50, 3, NULL),
(12, 53, 50, 3, NULL),
(12, 52, 50, 3, NULL),
(12, 51, 50, 3, NULL),
(5, 91, 88, 3, NULL),
(5, 90, 88, 3, NULL),
(5, 89, 88, 3, NULL),
(5, 88, 3, 2, NULL),
(5, 106, 50, 3, NULL),
(5, 97, 50, 3, NULL),
(5, 96, 50, 3, NULL),
(5, 94, 50, 3, NULL),
(5, 56, 50, 3, NULL),
(5, 55, 50, 3, NULL),
(5, 54, 50, 3, NULL),
(5, 53, 50, 3, NULL),
(5, 52, 50, 3, NULL),
(5, 51, 50, 3, NULL),
(5, 50, 3, 2, NULL),
(5, 31, 25, 3, NULL),
(5, 30, 25, 3, NULL),
(5, 29, 25, 3, NULL),
(5, 28, 25, 3, NULL),
(5, 27, 25, 3, NULL),
(5, 26, 25, 3, NULL),
(5, 25, 3, 2, NULL),
(5, 24, 18, 3, NULL),
(5, 23, 18, 3, NULL),
(5, 22, 18, 3, NULL),
(5, 21, 18, 3, NULL),
(5, 20, 18, 3, NULL),
(5, 19, 18, 3, NULL),
(5, 18, 3, 2, NULL),
(5, 3, 1, 0, NULL),
(5, 17, 11, 3, NULL),
(5, 16, 11, 3, NULL),
(5, 15, 11, 3, NULL),
(5, 14, 11, 3, NULL),
(5, 13, 11, 3, NULL),
(5, 12, 11, 3, NULL),
(5, 11, 2, 2, NULL),
(5, 10, 6, 3, NULL),
(5, 9, 6, 3, NULL),
(5, 8, 6, 3, NULL),
(5, 7, 6, 3, NULL),
(5, 6, 2, 2, NULL),
(5, 2, 1, 0, NULL),
(11, 85, 0, 1, NULL),
(11, 87, 85, 2, NULL),
(10, 85, 0, 1, NULL),
(10, 86, 85, 2, NULL),
(5, 1, 0, 1, NULL),
(12, 50, 3, 2, NULL),
(12, 3, 1, 0, NULL),
(12, 95, 35, 3, NULL),
(12, 37, 35, 3, NULL),
(12, 36, 35, 3, NULL),
(12, 35, 2, 2, NULL),
(12, 2, 1, 0, NULL),
(12, 1, 0, 1, NULL),
(18, 102, 88, 3, NULL),
(18, 89, 88, 3, NULL),
(18, 106, 50, 3, NULL),
(18, 97, 50, 3, NULL),
(18, 96, 50, 3, NULL),
(18, 94, 50, 3, NULL),
(18, 53, 50, 3, NULL),
(18, 52, 50, 3, NULL),
(18, 51, 50, 3, NULL),
(18, 50, 3, 2, NULL),
(18, 3, 1, 0, NULL),
(18, 95, 35, 3, NULL),
(18, 37, 35, 3, NULL),
(18, 36, 35, 3, NULL),
(18, 35, 2, 2, NULL),
(18, 2, 1, 0, NULL),
(18, 103, 3, 2, NULL),
(18, 104, 103, 3, NULL),
(18, 105, 103, 3, NULL),
(18, 1, 0, 1, NULL);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
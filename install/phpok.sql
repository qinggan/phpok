-- phpMyAdmin SQL Dump
-- version 4.1.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2014-11-10 10:03:51
-- 服务器版本： 5.5.20
-- PHP Version: 5.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phpok`
--

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_address`
--

CREATE TABLE IF NOT EXISTS `qinggan_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `country` varchar(255) NOT NULL COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省信息',
  `city` varchar(255) NOT NULL COMMENT '市',
  `county` varchar(255) NOT NULL COMMENT '县',
  `address` varchar(255) NOT NULL COMMENT '地址信息（不含国家，省市县镇区信息）',
  `zipcode` varchar(20) NOT NULL COMMENT '邮编',
  `type_id` enum('shipping','billing') NOT NULL DEFAULT 'shipping' COMMENT '类型，默认走送货地址',
  `mobile` varchar(100) NOT NULL COMMENT '手机号码',
  `tel` varchar(100) NOT NULL COMMENT '电话号码',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `fullname` varchar(100) NOT NULL COMMENT '联系人姓名',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0普通，1默认填写',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0女1男',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员地址库' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_adm`
--

CREATE TABLE IF NOT EXISTS `qinggan_adm` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID，系统自增',
  `account` varchar(50) NOT NULL COMMENT '管理员账号',
  `pass` varchar(100) NOT NULL COMMENT '管理员密码',
  `email` varchar(50) NOT NULL COMMENT '管理员邮箱',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未审核1正常2管理员锁定',
  `if_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统管理员',
  `vpass` varchar(50) NOT NULL COMMENT '二次验证密码，两次MD5加密',
  `category` longtext NOT NULL COMMENT '可操作的分类ID，系统管理员无效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理员信息' AUTO_INCREMENT=8 ;

--
-- 转存表中的数据 `qinggan_adm`
--

INSERT INTO `qinggan_adm` (`id`, `account`, `pass`, `email`, `status`, `if_system`, `vpass`, `category`) VALUES
(1, 'admin', 'e0ae361b631ce089a16f4a4c8cc8d033:5a', 'qinggan@188.com', 1, 1, '', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_adm_popedom`
--

CREATE TABLE IF NOT EXISTS `qinggan_adm_popedom` (
  `id` int(10) unsigned NOT NULL COMMENT '管理员ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权限ID，对应popedom表里的id',
  PRIMARY KEY (`id`,`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理员权限分配表';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_all`
--

CREATE TABLE IF NOT EXISTS `qinggan_all` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `identifier` varchar(100) NOT NULL COMMENT '标识串',
  `title` varchar(200) NOT NULL COMMENT '分类名称',
  `ico` varchar(255) NOT NULL COMMENT '图标',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0普通１系统',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分类管理' AUTO_INCREMENT=38 ;

--
-- 转存表中的数据 `qinggan_all`
--

INSERT INTO `qinggan_all` (`id`, `site_id`, `identifier`, `title`, `ico`, `is_system`) VALUES
(4, 1, 'copyright', '页脚版权', 'images/ico/copyright.png', 0),
(9, 1, 'contactus', '联系方式', 'images/ico/email2.png', 0),
(37, 1, 'share', '分享代码', 'images/ico/share.png', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_cart`
--

CREATE TABLE IF NOT EXISTS `qinggan_cart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `session_id` varchar(255) NOT NULL COMMENT 'SESSION_ID号',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示游客',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='购物车' AUTO_INCREMENT=1 ;

--
-- 转存表中的数据 `qinggan_cart`
--

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_cart_product`
--

CREATE TABLE IF NOT EXISTS `qinggan_cart_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `cart_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购物车ID号',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `title` varchar(255) NOT NULL COMMENT '产品名称',
  `price` float NOT NULL COMMENT '产品单价',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `ext` text NOT NULL COMMENT '扩展属性',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='购物车里的产品信息' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_cate`
--

CREATE TABLE IF NOT EXISTS `qinggan_cate` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID，0为根分类',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不使用1正常使用',
  `title` varchar(200) NOT NULL COMMENT '分类名称',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '分类排序，值越小越往前靠',
  `tpl_list` varchar(255) NOT NULL COMMENT '列表模板',
  `tpl_content` varchar(255) NOT NULL COMMENT '内容模板',
  `psize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '列表每页数量',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` varchar(255) NOT NULL COMMENT 'SEO描述',
  `identifier` varchar(255) NOT NULL COMMENT '分类标识串',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分类管理' AUTO_INCREMENT=207 ;

--
-- 转存表中的数据 `qinggan_cate`
--

INSERT INTO `qinggan_cate` (`id`, `site_id`, `parent_id`, `status`, `title`, `taxis`, `tpl_list`, `tpl_content`, `psize`, `seo_title`, `seo_keywords`, `seo_desc`, `identifier`) VALUES
(8, 1, 7, 1, '公司新闻', 10, '', '', 0, '', '', '', 'company'),
(7, 1, 0, 1, '新闻资讯', 10, '', '', 0, '', '', '', 'information'),
(68, 1, 7, 1, '行业新闻', 20, '', '', 0, '', '', '', 'industry'),
(70, 1, 0, 1, '产品分类', 20, '', '', 0, '', '', '', 'chanpinfenlei'),
(72, 1, 70, 1, '男装', 10, '', '', 0, '', '', '', 'nanzhuang'),
(151, 1, 70, 1, '女装', 20, '', '', 0, '', '', '', 'nvzhuang'),
(152, 1, 70, 1, '配饰', 30, '', '', 0, '', '', '', 'peishi'),
(191, 1, 152, 1, '项链', 80, '', '', 0, '', '', '', 'xianglian'),
(154, 1, 0, 1, '图集相册', 30, '', '', 0, '', '', '', 'album'),
(155, 1, 154, 1, '图话', 10, '', '', 0, '', '', '', 'tuhua'),
(156, 1, 154, 1, '型男靓女', 20, '', '', 0, '', '', '', 'xingnanliangnv'),
(157, 1, 72, 1, 'T恤', 10, '', '', 0, '', '', '', 't-shirt'),
(158, 1, 72, 1, 'polo衫', 20, '', '', 0, '', '', '', 'polo-shirt'),
(160, 1, 151, 1, '小西服', 60, '', '', 0, '', '', '', 'xiaoxifu'),
(161, 1, 151, 1, '雪纺衫', 20, '', '', 0, '', '', '', 'xuefangshan'),
(163, 1, 151, 1, '印花T恤', 30, '', '', 0, '', '', '', 'yinhuatxu'),
(164, 1, 151, 1, '短裙', 50, '', '', 0, '', '', '', 'duanqun'),
(165, 1, 151, 1, '半身裙', 40, '', '', 0, '', '', '', 'banshenqun'),
(166, 1, 151, 1, '衬衫', 30, '', '', 0, '', '', '', 'chenshan-woman'),
(167, 1, 151, 1, '连衣裙', 10, '', '', 0, '', '', '', 'lianyiqun'),
(168, 1, 72, 1, '衬衫', 30, '', '', 0, '', '', '', 'shirt'),
(169, 1, 72, 1, '薄夹克', 40, '', '', 0, '', '', '', 'a-thin-jacket'),
(170, 1, 72, 1, '西服', 50, '', '', 0, '', '', '', 'a-suit'),
(171, 1, 72, 1, '牛仔裤', 60, '', '', 0, '', '', '', 'niuziku'),
(172, 1, 72, 1, '休闲裤', 70, '', '', 0, '', '', '', 'xiuxianku'),
(173, 1, 72, 1, '西裤', 80, '', '', 0, '', '', '', 'xiku'),
(174, 1, 72, 1, '短裤', 90, '', '', 0, '', '', '', 'duanku'),
(175, 1, 72, 1, '七分裤', 100, '', '', 0, '', '', '', 'qifenku'),
(176, 1, 72, 1, '九分裤', 120, '', '', 0, '', '', '', 'jiufenku'),
(177, 1, 72, 1, '棉麻裤', 130, '', '', 0, '', '', '', 'mianmaku'),
(178, 1, 72, 1, '中老年男装', 140, '', '', 0, '', '', '', 'zhonglaoniannanzhuang'),
(179, 1, 72, 1, '唐装/中山装', 150, '', '', 0, '', '', '', 'zhongshanzhuang'),
(180, 1, 72, 1, '潮牌', 160, '', '', 0, '', '', '', 'chaopai'),
(181, 1, 151, 1, '卫衣套装', 70, '', '', 0, '', '', '', 'weiyitaozhuang'),
(182, 1, 151, 1, '妈妈装', 80, '', '', 0, '', '', '', 'mamazhuang'),
(183, 1, 151, 1, '皮草皮衣', 90, '', '', 0, '', '', '', 'picaopiyi'),
(184, 1, 152, 1, '眼镜', 10, '', '', 0, '', '', '', 'yanjing'),
(185, 1, 152, 1, '腰带', 20, '', '', 0, '', '', '', 'yaodai'),
(186, 1, 152, 1, '丝巾', 30, '', '', 0, '', '', '', 'sijin'),
(187, 1, 152, 1, '领带', 40, '', '', 0, '', '', '', 'lingdai'),
(188, 1, 152, 1, '袖扣', 50, '', '', 0, '', '', '', 'xiukou'),
(189, 1, 152, 1, '帽子', 60, '', '', 0, '', '', '', 'maozi'),
(190, 1, 152, 1, '手套', 70, '', '', 0, '', '', '', 'shoutao'),
(192, 1, 152, 1, '戒指', 90, '', '', 0, '', '', '', 'jiezhi'),
(193, 1, 152, 1, '耳饰', 100, '', '', 0, '', '', '', 'ershi'),
(197, 1, 0, 1, '资源下载', 40, '', '', 0, '', '', '', 'ziyuanxiazai'),
(198, 1, 197, 1, '软件下载', 10, '', '', 0, '', '', '', 'ruanjianxiazai'),
(199, 1, 197, 1, '风格下载', 20, '', '', 0, '', '', '', 'fenggexiazai'),
(200, 1, 197, 1, '官方插件', 30, '', '', 0, '', '', '', 'guanfangchajian'),
(201, 1, 0, 1, '论坛分类', 50, '', '', 0, '', '', '', 'bbs-cate'),
(204, 1, 201, 1, '情感驿站', 10, '', '', 0, '', '', '', 'qingganyizhan'),
(205, 1, 201, 1, '产品讨论', 20, '', '', 0, '', '', '', 'chanpintaolun'),
(206, 1, 201, 1, '水吧专区', 30, '', '', 0, '', '', '', 'shuibazhuanqu');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_currency`
--

CREATE TABLE IF NOT EXISTS `qinggan_currency` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '货币ID',
  `code` varchar(3) NOT NULL COMMENT '货币标识，仅限三位数的大写字母',
  `val` float(13,8) unsigned NOT NULL COMMENT '货币转化',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `title` varchar(50) NOT NULL COMMENT '名称',
  `symbol_left` varchar(24) NOT NULL COMMENT '价格左侧',
  `symbol_right` varchar(24) NOT NULL COMMENT '价格右侧',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不隐藏1隐藏',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='货币管理' AUTO_INCREMENT=7 ;

--
-- 转存表中的数据 `qinggan_currency`
--

INSERT INTO `qinggan_currency` (`id`, `code`, `val`, `taxis`, `title`, `symbol_left`, `symbol_right`, `status`, `hidden`) VALUES
(1, 'CNY', 6.16989994, 10, '人民币', '￥', '', 1, 0),
(2, 'USD', 1.00000000, 20, '美金', 'US$', '', 1, 0),
(3, 'HKD', 7.76350021, 30, '港元', 'HK$', '', 1, 0),
(4, 'EUR', 0.76639998, 40, '欧元', 'EUR', '', 1, 0),
(5, 'GBP', 0.64529997, 50, '英镑', '￡', '', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_email`
--

CREATE TABLE IF NOT EXISTS `qinggan_email` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID，0表示全部网站',
  `identifier` varchar(255) NOT NULL COMMENT '发送标识',
  `title` varchar(200) NOT NULL COMMENT '邮件主题',
  `content` text NOT NULL COMMENT '邮件内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='邮件内容' AUTO_INCREMENT=8 ;

--
-- 转存表中的数据 `qinggan_email`
--

INSERT INTO `qinggan_email` (`id`, `site_id`, `identifier`, `title`, `content`) VALUES
(4, 1, 'register_code', '获取会员注册资格', '<p>您好，{$email}</p><p>您将注册成为网站【{$config.title} 】会员，请点击下面的地址，进入下一步注册：</p><p><br/></p><blockquote style="margin: 0 0 0 40px; border: none; padding: 0px;"><p><a href="{$link}" target="_blank">{$link}</a></p><p>（此链接24小时内有效）</p></blockquote><p><br/></p><p><br/></p><p>感谢您对本站的关注，茫茫人海中，能有缘走到一起。</p>'),
(5, 1, 'getpass', '取回密码操作', '<p>您好，{$user.account}</p><p>您执行了忘记密码操作功能，请点击下面的链接执行下一步：</p><p><br /></p><p><blockquote style="margin: 0 0 0 40px; border: none; padding: 0px;"><p><a href="{$link}" target="_blank">{$link}</a></p></blockquote><br /></p><p>感谢您对本站的支持，有什么问题您在登录后可以咨询我们的客服。</p>'),
(6, 1, 'project_save', '主题添加通知', '<p>您好，管理员</p><blockquote><p>您的网站（<a href="http://{$sys.url}" target="_self">{$sys.url}</a>）新增了一篇主题，下述是主题的基本信息：<br/></p><p>主题名称：{$rs.title}</p><p>项目类型：{$page_rs.title}</p><p><br/></p><p>请登录网站查询</p></blockquote>'),
(7, 1, 'order_admin', '网站收到一个新的订单，订单号是：{$order.sn}', '<p>您好，管理员</p><blockquote><p>您的网站：{$sys.url} 收到一份新的订单，订单号是：{$order.sn}</p><p><br/></p><p>请登录网站后台进行核验</p></blockquote>');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_ext`
--

CREATE TABLE IF NOT EXISTS `qinggan_ext` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段ID，自增',
  `module` varchar(100) NOT NULL COMMENT '模块',
  `title` varchar(255) NOT NULL COMMENT '字段名称',
  `identifier` varchar(50) NOT NULL COMMENT '字段标识串',
  `field_type` varchar(255) NOT NULL DEFAULT '200' COMMENT '字段存储类型',
  `note` varchar(255) NOT NULL COMMENT '字段内容备注',
  `form_type` varchar(100) NOT NULL COMMENT '表单类型',
  `form_style` varchar(255) NOT NULL COMMENT '表单CSS',
  `format` varchar(100) NOT NULL COMMENT '格式化方式',
  `content` text NOT NULL COMMENT '默认值',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `ext` text NOT NULL COMMENT '扩展内容',
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=247 ;

--
-- 转存表中的数据 `qinggan_ext`
--

INSERT INTO `qinggan_ext` (`id`, `module`, `title`, `identifier`, `field_type`, `note`, `form_type`, `form_style`, `format`, `content`, `taxis`, `ext`) VALUES
(35, 'all-4', '内容', 'content', 'longtext', '', 'code_editor', '', 'html_js', '', 90, 'a:2:{s:5:"width";s:3:"700";s:6:"height";s:3:"200";}'),
(59, 'all-9', '联系人', 'fullname', 'varchar', '填写联系人姓名', 'text', '', 'safe', '', 10, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}'),
(221, 'project-148', '二维码图片', 'barcode', 'varchar', '请上传相应的二维码图片', 'upload', '', '', '', 255, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(61, 'all-9', '邮箱', 'email', 'varchar', '', 'text', '', 'safe', '', 50, 'a:2:{s:8:"form_btn";b:0;s:5:"width";b:0;}'),
(62, 'all-9', '联系地址', 'address', 'varchar', '', 'text', '', 'safe', '', 20, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";}'),
(63, 'all-9', '联系电话', 'tel', 'varchar', '', 'text', '', 'safe', '', 40, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}'),
(66, 'all-9', '邮编', 'zipcode', 'varchar', '请填写六位数字的邮编号码', 'text', '', 'safe', '', 30, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}'),
(161, 'project-90', '内容', 'content', 'longtext', '', 'editor', '', 'html_js', '', 255, 'a:7:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";s:1:"1";s:9:"btn_video";s:1:"1";s:8:"btn_file";s:1:"1";s:8:"btn_page";b:0;}'),
(162, 'project-90', '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(164, 'project-90', '摘要', 'note', 'longtext', '简要文字描述', 'editor', '', 'html_js', '', 20, 'a:7:{s:5:"width";s:3:"800";s:6:"height";s:3:"160";s:7:"is_code";b:0;s:9:"btn_image";s:1:"1";s:9:"btn_video";b:0;s:8:"btn_file";b:0;s:8:"btn_page";b:0;}'),
(165, 'project-92', '内容', 'content', 'longtext', '', 'editor', '', 'html_js', '', 255, 'a:7:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";s:1:"1";s:9:"btn_video";s:1:"1";s:8:"btn_file";s:1:"1";s:8:"btn_page";b:0;}'),
(228, 'project-93', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:4:"full";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}'),
(229, 'project-45', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(227, 'project-87', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(212, 'cate-160', '性别', 'gender', 'varchar', '', 'radio', '', 'safe', '女', 120, ''),
(213, 'project-146', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:4:"full";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}'),
(218, 'project-43', '英文标题En-Title', 'entitle', 'varchar', '', 'text', '', 'safe', '', 10, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";}'),
(219, 'project-43', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(220, 'all-9', '公司名称', 'company', 'varchar', '', 'text', '', 'safe', '', 10, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";}'),
(222, 'project-149', '英文标题', 'entitle', 'varchar', '放在首页的公司简介的英文小标题', 'text', '', 'safe', '', 30, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";}'),
(223, 'project-149', '小标题', 'subtitle', 'varchar', '这里是放在首页的小标题信息，如公司简介', 'text', '', 'safe', '', 20, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}'),
(224, 'project-149', '摘要', 'note', 'longtext', '简要文字描述', 'editor', '', 'html', '', 40, 'a:12:{s:5:"width";s:3:"700";s:6:"height";s:3:"140";s:7:"is_code";b:0;s:9:"btn_image";s:1:"1";s:9:"btn_video";b:0;s:8:"btn_file";b:0;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:6:"simple";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}'),
(225, 'project-149', '图片', 'pic', 'varchar', '', 'text', '', 'safe', '', 255, 'a:2:{s:8:"form_btn";s:5:"image";s:5:"width";s:3:"500";}'),
(226, 'project-149', '更多的链接地址', 'link', 'longtext', '请填写公司简介的链接地址', 'url', '', 'safe', '', 90, 'a:1:{s:5:"width";s:3:"500";}'),
(230, 'project-150', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:4:"full";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}'),
(231, 'all-37', '百度分享代码', 'baidu', 'longtext', '', 'code_editor', '', 'html_js', '', 10, 'a:2:{s:5:"width";s:3:"800";s:6:"height";s:3:"300";}'),
(232, 'cate-195', '链接', 'link', 'longtext', '手动指定外部链接时，伪静态链接可以留空', 'url', '', 'safe', '', 90, ''),
(233, 'cate-195', '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:1:{s:11:"option_list";s:5:"opt:6";}'),
(234, 'cate-196', '链接', 'link', 'longtext', '手动指定外部链接时，伪静态链接可以留空', 'url', '', 'safe', '', 90, ''),
(235, 'cate-196', '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:1:{s:11:"option_list";s:5:"opt:6";}'),
(236, 'project-96', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(237, 'project-151', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(238, 'cate-204', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:"width";s:3:"600";s:6:"height";s:2:"80";}'),
(239, 'project-152', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}'),
(240, 'cate-205', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:"width";s:3:"600";s:6:"height";s:2:"80";}'),
(241, 'cate-206', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:"width";s:3:"600";s:6:"height";s:2:"80";}'),
(244, 'project-144', '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}'),
(246, 'project-142', '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_extc`
--

CREATE TABLE IF NOT EXISTS `qinggan_extc` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容值ID，对应ext表中的id',
  `content` longtext NOT NULL COMMENT '内容文本',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='扩展字段内容维护';

--
-- 转存表中的数据 `qinggan_extc`
--

INSERT INTO `qinggan_extc` (`id`, `content`) VALUES
(35, 'Powered By phpok.com 版权所有 © 2004-2014, All right reserved.'),
(59, 'XXX'),
(61, 'admin@domain.com'),
(62, '广东深圳市罗湖区东盛路辐照中心7栋3楼'),
(63, '158185XXXXX'),
(66, '518000'),
(162, '352'),
(161, '<p>PHPOK企业建站系统（下述将用“系统”简称）是一套致力于企业网通用配置平台应用。公司长期专注于微小型企业网络化的研发和经营，拥有八年多的企业建站经验。系统广泛应用于全国多个省市，涉及行业包括保险、服装、电器、化工、物流、房地产、旅游、贸易、珠宝、WAP等行业。&nbsp;<br/>&nbsp;<br/>公司一贯坚持以“专业是基础，服务是保证，质量是信誉”的理念，来适应和满足客户不断增长的业务需求，提供有竞争力的、可持续发展的产品和技术解决方案。&nbsp;</p>'),
(164, '<p>PHPOK企业建站系统（下述将用“系统”简称）是一套致力于企业网通用配置平台应用。公司长期专注于微小型企业网络化的研发和经营，拥有八年多的企业建站经验。系统广泛应用于全国多个省市，涉及行业包括保险、服装、电器、化工、物流、房地产、旅游、贸易、珠宝、WAP等行业。 <br/> <br/>公司一贯坚持以“专业是基础，服务是保证，质量是信誉”的理念，来适应和满足客户不断增长的业务需求，提供有竞争力的、可持续发展的产品和技术解决方案。</p>'),
(165, '<p>公司网站：www.phpok.com</p><p>联系地址：深圳市罗湖区东盛路辐照中心7栋3楼</p><p>联系电话：15818533971</p><p><br /></p><p>如何到达：<br />地铁环中线——布心站”下车B出口直走,第一个红绿灯也就是太白路，往右走一直沿着太白路走直到看到左侧有一东盛路，沿着东盛路左侧第一栋就是辐照中心。地铁步行到公司大约15分钟。周围标志性建筑：金威啤酒厂。<br /><br />途径附近公交：<br />乘坐107路，203路，212路，24路，2路，379路，40路，59路，62路，83路，<br />B698路单向行驶，N2路，N6路，到松泉公寓下车。<br /></p>'),
(228, '<p>这里是内容说明！</p>'),
(229, '625'),
(227, '625'),
(212, ''),
(213, '<table><tbody><tr class="firstRow"><td width="117" valign="top" style="word-break: break-all;"><span style="color: rgb(192, 0, 0);">2011年12月</span></td><td width="721" valign="top" style="word-break: break-all;">phpok3.4版发布（后台更换为桌面式）</td></tr><tr><td width="116" valign="top" style="word-break: break-all;"><span style="color: rgb(192, 0, 0);">2011年9月</span></td><td width="721" valign="top" style="word-break: break-all;">phpok3.3完整版发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2010年8月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">phpok3.0完整版发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2008年9月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">phpok3.0精简版发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2008年5月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">phpok2.2稳定版发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="116"><span style="color: rgb(192, 0, 0);">2008年3月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">phpok2.0发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="116"><span style="color: rgb(192, 0, 0);">2007年5月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgweb5.2发布，同时更名为 phpok1.0版本</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2007年1月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgweb5.0发布（第一次实现多语言，多风格的建站系统）</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2006年10月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgweb4.2发布（GBK）</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2006年8月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgweb4.1发布（UTF-8）</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2006年6月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgweb4.0发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2005年11月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgWeb3.0发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2005年8月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">工作室论坛开通</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2005年7月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgWeb1.0发布</td></tr><tr><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="115"><span style="color: rgb(192, 0, 0);">2005年4月</span></td><td valign="top" colspan="1" rowspan="1" style="word-break: break-all;" width="719">qgWeb0.54版发布</td></tr></tbody></table><p><br/></p>'),
(218, 'News'),
(219, '625'),
(220, '深圳市锟铻科技有限公司'),
(221, '629'),
(223, '公司简介'),
(222, 'Intro'),
(224, '<p style="text-indent: 2em; text-align: left;">PHPOK企业程序899（简称程序）是锟铻科技有限公司（前身为情感工作室）开发的一套实用性强，定制灵活的企业网站建设系统，基于PHP+MySQL架构，可运行于Linux、Windows、MacOSX、Solaris等各种平台上。</p><p style="text-indent: 2em; text-align: left;">程序采用MVC模式开发，支持各种自定义：分类，项目，模块，站点信息等等，您甚至可以基于这些自定义选项来编写相应的插件以实现各个项目的勾连。</p><p style="text-indent: 2em; text-align: left;">程序最新版本已内置了这些常用的项目：单页面（适用于公司简介），新闻资讯，下载中心，图片展示，在线商城，留言本，迷你小论坛及基础会员功能。您随时可以在后台禁用这些项目甚至是删除之。简约，实用，够用，好用，是我们一直都在努力追求的目标。</p>'),
(226, 'a:2:{s:7:"default";s:21:"index.php?id=about-us";s:7:"rewrite";s:13:"about-us.html";}'),
(225, 'res/201409/03/5b0086d14de1bbf2.jpg'),
(230, '<p>售后保障</p><p>这里填写通用的售后保障信息~~~</p>'),
(231, '<div class="bdsharebuttonbox"><a href="#" class="bds_more" data-cmd="more"></a><a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a><a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a><a href="#" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博"></a><a href="#" class="bds_renren" data-cmd="renren" title="分享到人人网"></a><a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a></div>\r\n<script>window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdPic":"","bdStyle":"0","bdSize":"16"},"share":{}};with(document)0[(getElementsByTagName(''head'')[0]||body).appendChild(createElement(''script'')).src=''http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=''+~(-new Date()/36e5)];</script>'),
(237, '543'),
(238, '本区以讨论各种感情，各类人生为核心主题\r\n心灵鸡汤无处不在，不在于多少，只在于感悟\r\n懂了就是懂了，不懂仍然不懂'),
(236, '625'),
(239, '545'),
(240, '围绕我公司提供的产品进行讨论\r\n广开言路，我公司会虚心接纳，完善产品'),
(241, '吐吐糟，发发牢骚，八卦精神无处不在\r\n笑一笑，十年少，在这个快节奏的时代里，这里还有一片净土供您休息\r\n不是我不爱，只是世界变化快^o^'),
(244, 'Photos'),
(246, 'Links');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_fields`
--

CREATE TABLE IF NOT EXISTS `qinggan_fields` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段ID，自增',
  `title` varchar(255) NOT NULL COMMENT '字段名称',
  `identifier` varchar(50) NOT NULL COMMENT '字段标识串',
  `field_type` varchar(255) NOT NULL DEFAULT '200' COMMENT '字段存储类型',
  `note` varchar(255) NOT NULL COMMENT '字段内容备注',
  `form_type` varchar(100) NOT NULL COMMENT '表单类型',
  `form_style` varchar(255) NOT NULL COMMENT '表单CSS',
  `format` varchar(100) NOT NULL COMMENT '格式化方式',
  `content` varchar(100) NOT NULL COMMENT '默认值',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `ext` text NOT NULL COMMENT '扩展内容',
  `area` text NOT NULL COMMENT '使用范围，多个应用范围用英文逗号隔开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=124 ;

--
-- 转存表中的数据 `qinggan_fields`
--

INSERT INTO `qinggan_fields` (`id`, `title`, `identifier`, `field_type`, `note`, `form_type`, `form_style`, `format`, `content`, `taxis`, `ext`, `area`) VALUES
(6, '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:4:"full";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}', 'all,cate,module,project,user,usergroup'),
(7, '图片', 'pictures', 'varchar', '支持多图', 'upload', '', 'safe', '', 50, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"1";}', 'all,cate,module,project,user'),
(8, '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 'all,cate,module,project,user'),
(9, '压缩文件', 'file', 'varchar', '仅支持压缩文件', 'upload', '', 'safe', '', 60, 'a:3:{s:11:"upload_type";s:3:"zip";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 'all,cate,module,project'),
(11, '链接', 'link', 'longtext', '手动指定外部链接时，伪静态链接可以留空', 'url', '', 'safe', '', 90, 'a:1:{s:5:"width";s:3:"500";}', 'all,cate,module,project,user'),
(12, '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:"width";s:3:"600";s:6:"height";s:2:"80";}', 'all,cate,module,project'),
(13, '性别', 'gender', 'varchar', '', 'radio', '', 'safe', '女', 120, 'a:3:{s:11:"option_list";b:0;s:9:"put_order";s:1:"0";s:10:"ext_select";s:8:"男\r\n女";}', 'all,cate,module,project,user'),
(14, '邮箱', 'email', 'varchar', '', 'text', '', 'safe', '', 130, 'a:2:{s:8:"form_btn";b:0;s:5:"width";b:0;}', 'all,cate,module,project,user'),
(37, '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:1:{s:11:"option_list";s:5:"opt:6";}', 'cate,module,project'),
(30, '姓名', 'fullname', 'varchar', '', 'text', '', 'safe', '', 10, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all,cate,module,project,user'),
(31, '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 'all,cate,module,project'),
(34, '附件', 'files', 'varchar', '仅支持rar和zip的压缩包，支持多附件', 'upload', '', 'safe', '', 70, 'a:3:{s:11:"upload_type";s:3:"zip";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"1";}', 'all,cate,module,project'),
(35, '文档', 'doc', 'varchar', '支持在线办公室的文档', 'upload', '', 'safe', '', 80, 'a:3:{s:11:"upload_type";s:8:"document";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"1";}', 'all,cate,module,project'),
(36, '视频', 'video', 'varchar', '支持并推荐您使用FlV格式视频', 'upload', '', 'int', '', 110, 'a:3:{s:11:"upload_type";b:0;s:7:"cate_id";b:0;s:11:"is_multiple";b:0;}', 'module,project'),
(60, '客服QQ', 'qq', 'varchar', '', 'text', '', 'safe', '', 150, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all,cate,module,project,user'),
(116, '广告内容', 'ad', 'longtext', '', 'editor', '', 'html_js', '', 255, 'a:12:{s:5:"width";s:3:"600";s:6:"height";s:3:"100";s:7:"is_code";i:1;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";b:0;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:6:"simple";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}', 'all,cate,module,project'),
(75, '联系地址', 'address', 'varchar', '', 'text', '', 'safe', '', 79, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";}', 'all,module,user'),
(76, '联系电话', 'tel', 'varchar', '', 'text', '', 'safe', '', 89, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all,cate,module,project,user'),
(77, '邮编', 'zipcode', 'varchar', '请填写六位数字的邮编号码', 'text', '', 'safe', '', 30, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all,module'),
(79, 'LOGO', 'logo', 'varchar', '网站LOGO，规格：88x31', 'text', '', 'safe', '', 160, 'a:2:{s:8:"form_btn";s:5:"image";s:5:"width";s:3:"500";}', 'all,cate,module,project'),
(80, '图片', 'pic', 'varchar', '', 'text', '', 'safe', '', 255, 'a:2:{s:8:"form_btn";s:5:"image";s:5:"width";s:3:"500";}', 'all,cate,module,project,user'),
(81, '统计', 'statjs', 'varchar', '', 'code_editor', '', 'html_js', '', 255, 'a:2:{s:5:"width";s:3:"500";s:6:"height";s:2:"80";}', 'all'),
(82, '备案号', 'cert', 'varchar', '', 'text', '', 'safe', '', 255, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all'),
(95, '发货时间', 'sendtime', 'varchar', '设置发货时间', 'text', '', 'time', '', 255, 'a:2:{s:8:"form_btn";s:4:"date";s:5:"width";s:3:"300";}', 'module'),
(96, '企业名称', 'company', 'varchar', '', 'text', '', 'safe', '', 255, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";}', 'all,module,project,user'),
(106, '管理员回复', 'adm_reply', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"180";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:6:"simple";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}', 'module'),
(112, '赞', 'good', 'varchar', '设置点赞次数', 'text', '', 'int', '', 20, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"100";}', 'module'),
(113, '省市', 'province_city', 'longtext', '', 'select', '', 'safe', '', 255, 'a:4:{s:11:"option_list";s:5:"opt:2";s:11:"is_multiple";s:1:"0";s:5:"width";b:0;s:10:"ext_select";b:0;}', 'all,cate,module,project,user,usergroup'),
(114, '手机号', 'mobile', 'varchar', '', 'text', '', 'safe', '', 255, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all,cate,module,project,user,usergroup'),
(117, '规格参数', 'spec', 'longtext', '', 'param', '', '', '', 255, 'a:2:{s:6:"p_name";s:30:"名称\r\n型号\r\n流量\r\n大小";s:6:"p_type";s:1:"1";}', 'module'),
(118, '产品属性', 'spec_single', 'longtext', '', 'param', '', '', '', 255, 'a:2:{s:6:"p_name";s:119:"型号\r\n推荐用途\r\n平台\r\n显卡类型\r\n网卡\r\n类型\r\n速度\r\n核心数\r\n二级缓存\r\n显示芯片\r\n显存容量";s:6:"p_type";s:1:"0";}', 'module'),
(119, '页脚版权', 'copyright', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"100";s:7:"is_code";b:0;s:9:"btn_image";s:1:"1";s:9:"btn_video";b:0;s:8:"btn_file";b:0;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:6:"simple";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}', 'all,module'),
(120, '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 'all,cate,module,project,user,usergroup,cart,order,pay'),
(121, '二维码图片', 'barcode', 'varchar', '请上传相应的二维码图片', 'upload', '', '', '', 255, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 'all,cate,module,project,user,usergroup,cart,order,pay'),
(122, '子标题', 'subtitle', 'varchar', '', 'text', '', '', '', 20, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 'all,cate,module,project,user,usergroup,cart,order,pay'),
(123, '百度分享代码', 'baidu', 'longtext', '', 'code_editor', '', 'html_js', '', 10, 'a:2:{s:5:"width";s:3:"800";s:6:"height";s:3:"300";}', 'all,cate,module,project,user,usergroup,cart,order,pay');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_gd`
--

CREATE TABLE IF NOT EXISTS `qinggan_gd` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `identifier` varchar(100) NOT NULL COMMENT '标识串',
  `width` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片宽度',
  `height` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图片高度',
  `mark_picture` varchar(255) NOT NULL COMMENT '水印图片位置',
  `mark_position` varchar(100) NOT NULL COMMENT '水印位置',
  `cut_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图片生成方式，支持缩放法、裁剪法、等宽、等高及自定义五种，默认使用缩放法',
  `quality` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '图片生成质量，默认是100',
  `bgcolor` varchar(10) NOT NULL DEFAULT 'FFFFFF' COMMENT '补白背景色，默认是白色',
  `trans` tinyint(3) unsigned NOT NULL DEFAULT '65' COMMENT '透明度',
  `editor` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0普通1默认插入编辑器',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- 转存表中的数据 `qinggan_gd`
--

INSERT INTO `qinggan_gd` (`id`, `identifier`, `width`, `height`, `mark_picture`, `mark_position`, `cut_type`, `quality`, `bgcolor`, `trans`, `editor`) VALUES
(2, 'thumb', 300, 400, '', 'bottom-right', 1, 80, 'FFFFFF', 0, 0),
(12, 'auto', 0, 0, '', 'bottom-right', 0, 80, 'FFFFFF', 0, 1),
(21, 'mobile', 0, 180, '', 'bottom-right', 1, 80, 'FFFFFF', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list`
--

CREATE TABLE IF NOT EXISTS `qinggan_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0为根主题，其他ID对应list表的id字段',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `module_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '模块ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `site_id` mediumint(8) unsigned NOT NULL COMMENT '网站ID',
  `title` varchar(255) NOT NULL COMMENT '主题',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未审核，1已审核',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0显示，1隐藏',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `tpl` varchar(255) NOT NULL COMMENT '自定义的模板',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` varchar(255) NOT NULL COMMENT 'SEO描述',
  `tag` varchar(255) NOT NULL COMMENT 'tag标签',
  `attr` varchar(255) NOT NULL COMMENT '主题属性',
  `replydate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示管理员发布',
  `identifier` varchar(255) NOT NULL COMMENT '内容标识串',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '价格',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '货币ID，对应currency表',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='内容主表' AUTO_INCREMENT=1354 ;

--
-- 转存表中的数据 `qinggan_list`
--

INSERT INTO `qinggan_list` (`id`, `parent_id`, `cate_id`, `module_id`, `project_id`, `site_id`, `title`, `dateline`, `sort`, `status`, `hidden`, `hits`, `tpl`, `seo_title`, `seo_keywords`, `seo_desc`, `tag`, `attr`, `replydate`, `user_id`, `identifier`, `price`, `currency_id`) VALUES
(1276, 0, 0, 21, 41, 1, '企业建站，我信赖PHPOK', 1394008409, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(520, 0, 0, 23, 42, 1, '网站首页', 1380942032, 10, 1, 0, 0, '', '', '', '', '', 'mobile', 0, 0, '', '0.0000', 0),
(694, 0, 0, 0, 0, 0, 'fasdfasdfasdfasdf', 1381444969, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(695, 0, 0, 0, 0, 0, 'fasdfasdfa', 1381445019, 0, 0, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(712, 0, 0, 23, 42, 1, '关于我们', 1383355821, 20, 1, 0, 0, '', '', '', '', '', 'mobile', 0, 0, '', '0.0000', 0),
(713, 0, 0, 23, 42, 1, '新闻中心', 1383355842, 30, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(714, 0, 0, 23, 42, 1, '产品展示', 1383355849, 40, 1, 0, 0, '', '', 'Array', 'Array', 'Array', '', 0, 0, '', '0.0000', 0),
(716, 0, 0, 23, 42, 1, '在线留言', 1383355870, 60, 1, 0, 0, '', '', 'Array', 'Array', 'Array', '', 0, 0, '', '0.0000', 0),
(719, 712, 0, 23, 42, 1, '联系我们', 1383355984, 23, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1277, 0, 0, 21, 41, 1, '选择PHPOK，企业更专业', 1394008434, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(755, 712, 0, 23, 42, 1, '工作环境', 1383640450, 24, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1306, 0, 191, 24, 45, 1, '施华洛世奇（Swarovski） 浅粉蓝色雨滴项链', 1410443859, 0, 1, 0, 63, '', '', '', '', '', '', 0, 0, '', '799.0000', 1),
(758, 0, 8, 22, 43, 1, '31条航线机票取消打折下限 多与京沪京广高铁竞争', 1383806674, 0, 1, 0, 15, '', '', '', '', '', '', 1399239499, 0, '', '0.0000', 0),
(759, 0, 68, 22, 43, 1, '阿里TV系统升级 将增加安全监控功能', 1383806741, 0, 1, 0, 6, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(760, 713, 0, 23, 42, 1, '公司新闻', 1383815715, 10, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(761, 713, 0, 23, 42, 1, '行业新闻', 1383815736, 20, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1258, 0, 0, 46, 96, 1, '测试的留言', 1392376101, 0, 1, 0, 0, '', '', '', '', '', '', 0, 3, '', '0.0000', 0),
(1252, 0, 0, 61, 142, 1, 'phpok官网', 1390465160, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1253, 0, 168, 24, 45, 1, '新款男人时尚长袖格子衬衫', 1391830871, 0, 1, 0, 58, '', '', '', '', '', '', 1404983732, 0, '', '158.0000', 1),
(1254, 712, 0, 23, 42, 1, '发展历程', 1392375210, 26, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1256, 0, 0, 23, 42, 1, '图集相册', 1392375722, 70, 1, 0, 0, '', '', '', '', '', 'mobile', 0, 0, '', '0.0000', 0),
(1261, 0, 0, 61, 142, 1, '启邦互动', 1393321211, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1262, 0, 0, 61, 142, 1, '联迅网络', 1393321235, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1263, 0, 0, 61, 142, 1, '梦幻网络', 1393321258, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1264, 0, 0, 61, 142, 1, '中国站长站', 1393321288, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1265, 0, 0, 61, 142, 1, 'A5站长网', 1393321321, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1266, 0, 0, 61, 142, 1, '中国站长', 1393321365, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1267, 0, 0, 61, 142, 1, '落伍者', 1393321391, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1268, 0, 0, 61, 142, 1, '源码之家', 1393321413, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1269, 0, 68, 22, 43, 1, 'Chrome 33 新变化引发用户强烈抗议', 1393332440, 0, 1, 0, 197, '', '', '', '', '', '', 1410437460, 0, '', '0.0000', 0),
(1341, 0, 155, 68, 144, 1, '无奈的发明家', 1413169790, 0, 1, 0, 7, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1278, 0, 0, 21, 41, 1, '开源精神，开创未来', 1394008456, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1279, 0, 0, 46, 96, 1, '测试留言', 1396947239, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1285, 0, 0, 46, 96, 1, '测试留言', 1399239571, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1286, 0, 0, 46, 96, 1, '测试下留言', 1401775853, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1293, 0, 0, 46, 96, 1, '测试留言', 1405773694, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1297, 0, 0, 46, 96, 1, '测试留言', 1407329418, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1298, 0, 0, 23, 42, 1, '下载中心', 1409552212, 80, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1299, 0, 0, 23, 42, 1, '论坛BBS', 1409552219, 90, 1, 0, 0, '', '', '', '', '', 'mobile', 0, 0, '', '0.0000', 0),
(1300, 0, 0, 23, 147, 1, '公司简介', 1409554964, 10, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1301, 0, 0, 23, 147, 1, '发展历程', 1409554975, 20, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1302, 0, 0, 23, 147, 1, '新闻中心', 1409554988, 30, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1303, 0, 0, 23, 147, 1, '在线留言', 1409554999, 40, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1304, 0, 0, 23, 147, 1, '联系我们', 1409555008, 50, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1305, 0, 0, 64, 148, 1, 'PHPOK销售客服', 1409747629, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1308, 0, 0, 46, 96, 1, '测试一下留言功能', 1410960969, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1310, 0, 198, 65, 151, 1, '测试软件下载', 1412136071, 0, 1, 0, 36, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1311, 0, 204, 66, 152, 1, '测试论坛功能', 1412391521, 0, 1, 0, 1, '', '', '', '', '', '', 0, 3, '', '0.0000', 0),
(1342, 0, 155, 68, 144, 1, '悬崖上的环卫工', 1413169968, 0, 1, 0, 22, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1333, 0, 0, 67, 153, 1, 'demo', 1412977072, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1334, 0, 204, 66, 152, 1, '测试', 1413063267, 0, 1, 0, 3, '', '', '', '', '', '', 0, 3, '', '0.0000', 0),
(1336, 0, 204, 66, 152, 1, '测试图片功能', 1413064520, 0, 1, 0, 7, '', '', '', '', '', '', 0, 3, '', '0.0000', 0),
(1332, 0, 0, 67, 153, 1, 'aIkrtvvzw81412976415', 1412976415, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1344, 0, 68, 22, 43, 1, '测试视频', 1415263868, 0, 1, 0, 51, '', '', '', '', '', '', 0, 0, '', '0.0000', 0),
(1348, 0, 204, 66, 152, 1, '测试权限功能', 1414120852, 0, 1, 0, 7, '', '', '', '', '', '', 1414121403, 3, '', '0.0000', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_21`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_21` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `link` longtext NOT NULL COMMENT '链接',
  `target` varchar(255) NOT NULL DEFAULT '_self' COMMENT '链接方式',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图片播放器';

--
-- 转存表中的数据 `qinggan_list_21`
--

INSERT INTO `qinggan_list_21` (`id`, `site_id`, `project_id`, `cate_id`, `link`, `target`, `pic`) VALUES
(1276, 1, 41, 0, 'http://www.phpok.com', '_blank', '628'),
(1277, 1, 41, 0, 'http://www.phpok.com', '_blank', '627'),
(1278, 1, 41, 0, 'http://www.phpok.com', '_blank', '626');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_22`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_22` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `content` longtext NOT NULL COMMENT '内容',
  `note` longtext NOT NULL COMMENT '摘要',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章资讯';

--
-- 转存表中的数据 `qinggan_list_22`
--

INSERT INTO `qinggan_list_22` (`id`, `site_id`, `project_id`, `cate_id`, `thumb`, `content`, `note`) VALUES
(758, 1, 43, 8, '543', '<p style="margin-bottom: 29px; font-size: 16px; line-height: 28px; font-family: 宋体, Arial, sans-serif; text-indent: 2em; ">昨天，中国民用航空局和国家发改委发布通知称，对旅客运输票价实行政府指导价的国内航线，均取消票价下浮幅度限制。与此同时，国内不设打折下限的航线又新增31条。</p><p style="margin-bottom: 29px; font-size: 16px; line-height: 28px; font-family: 宋体, Arial, sans-serif; text-indent: 2em; ">据民航业内人士介绍，根据2004年出台的《民航国内<a class="a-tips-Article-QQ" href="http://stockhtm.finance.qq.com/astock/ggcx/ATSG.OQ.htm" target="_blank" style="text-decoration: none; outline: none; color: rgb(0, 0, 0); border-bottom-width: 1px; border-bottom-style: dotted; border-bottom-color: rgb(83, 109, 166); ">航空运输</a>价格改革方案》，省、自治区内，及直辖市与相邻省、自治区、直辖市之间的短途航线，已经与其他替代运输方式形成竞争的，实行市场调节价，不规定票价浮动幅度。除上述施行市场调节价的航线外，民航国内航空旅客运输票价实行浮动幅度管理，票价上浮幅度最高不得超过基准价的25%。</p><p style="margin-bottom: 29px; font-size: 16px; line-height: 28px; font-family: 宋体, Arial, sans-serif; text-indent: 2em; ">昨天发布的通知规定，对部分与地面主要交通运输方式形成竞争，且由两家(含)以上航空公司共同经营的国内航线，旅客运输票价由实行政府指导价改为市场调节价。航空公司可根据市场供求情况自主确定票价水平的航线新增31条。实行市场调节价的国内航线目录由民航局和国家发改委规定，于每年一季度调整公布。航空公司在上述范围内制定或调整旅客运输票价时，应至少提前7日向社会公布，并通过航空价格信息系统抄报民航局、发改委。上述业内人士分析，此次由政府指导价转为市场调节价的31条航线，大多是与京广、京沪等高铁存在竞争，取消浮动幅度限制，有利于提高民航的竞争力。</p>', ''),
(759, 1, 43, 68, '', '<p align="center" style="margin-top: 20px; margin-right: auto; margin-left: auto; padding-top: 5px; padding-bottom: 5px; line-height: 26px; font-size: 16px; color: rgb(51, 51, 51); font-family: 微软雅黑, Tahoma, Verdana, 宋体; "></p><div class="mbArticleSharePic        " r="1" style="margin: 0px auto; padding: 0px; position: relative; z-index: 10; width: 500px; "><img alt="阿里TV系统升级 将增加安全监控功能" src="res/201311/07/85032f7e7ba3cfd7_37.jpg" style="border: 0px; " /></div><p></p><p style="margin-top: 20px; margin-right: auto; margin-left: auto; padding-top: 5px; padding-bottom: 5px; line-height: 26px; font-size: 16px; color: rgb(51, 51, 51); font-family: 微软雅黑, Tahoma, Verdana, 宋体; text-indent: 2em; "><strong>腾讯科技讯</strong>（范蓉）11月7日消息，阿里TV系统将于本月进行升级，增加家庭安全监控功能。同时，阿里在今年“双11”期间，将采用100万台天猫魔盒免费送的方式，加速阿里TV系统在终端的普及。</p><p style="margin-top: 20px; margin-right: auto; margin-left: auto; padding-top: 5px; padding-bottom: 5px; line-height: 26px; font-size: 16px; color: rgb(51, 51, 51); font-family: 微软雅黑, Tahoma, Verdana, 宋体; text-indent: 2em; ">尽管阿里TV系统已经将电商与TV结合，但阿里并不满足于这一现状。“电视处于客厅的重要位置，阿里一直欲加强自身砝码，安全监控就是其中之一。”阿里一位内部人士表示。</p><p style="margin-top: 20px; margin-right: auto; margin-left: auto; padding-top: 5px; padding-bottom: 5px; line-height: 26px; font-size: 16px; color: rgb(51, 51, 51); font-family: 微软雅黑, Tahoma, Verdana, 宋体; text-indent: 2em; ">据透露，阿里TV系统将提供的安全监控功能，主要通过系统应用、摄像头及智能手机之间的软硬结合来实现。用户只需在智能电视上增设一个摄像头，同时在手机中安装阿里TV助手，就可在任意场所，通过手机屏幕看到家中实时传送过来的画面。</p><p style="margin-top: 20px; margin-right: auto; margin-left: auto; padding-top: 5px; padding-bottom: 5px; line-height: 26px; font-size: 16px; color: rgb(51, 51, 51); font-family: 微软雅黑, Tahoma, Verdana, 宋体; text-indent: 2em; ">业内人士认为，阿里不仅将电视定位为PC、手机、平板之外的第四个屏幕选择，还希望将电视提升到智能家居的大概念中。“加入安全监控功能的阿里TV系统，将使电视在家庭中扮演更加重要的监护设备角色。”</p><p style="margin-top: 20px; margin-right: auto; margin-left: auto; padding-top: 5px; padding-bottom: 5px; line-height: 26px; font-size: 16px; color: rgb(51, 51, 51); font-family: 微软雅黑, Tahoma, Verdana, 宋体; text-indent: 2em; ">据悉，安全监控功能将是阿里TV系统11月份更新的重头戏。未来阿里TV系统还会增加云存储功能，让用户可随时查询、回放家庭生活片段。</p>', ''),
(1269, 1, 43, 68, '544', '<p>Google发布了Chrome 33，其中一项新变化是从Chrome://flags移除了Instant Extended API。结果在官方论坛用户吵翻了天，因为移除Instant Extended API影响了新标签页，引发用户强烈抗议，许多用户认为改动后的新标签页无比丑陋。</p><p>Google自我辩解说，Chrome://flags本来就是展示的 实验性功能，是不被官方正式支持的，是随时会被移除或改动的。Google开发者向抱怨的用户推荐三个扩展替代被移除的Instant Extended API：Replace New Tab，Modern New Tab Page，和iChrome。</p><p><img src="res/201410/08/auto_666.jpg" alt="auto_666.jpg"/></p>', 'Google发布了Chrome 33，其中一项新变化是从Chrome://flags移除了Instant Extended API。结果在官方论坛用户吵翻了天，因为移除Instant Extended API影响了新标签页，引发用户强烈抗议，许多用户认为改动后的新标签页无比丑陋。'),
(1344, 1, 43, 68, '', '<p>[title:1336]测试图片功能[/title]</p><p>[title:1332]aIkrtvvzw81412976415[/title]</p><p>[download=687]退休矿工自家宅院掘地六米挖出地下居室[/download]</p><p>[download=686]悬崖上的环卫工03[/download]</p><p><br/></p>', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_23`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_23` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `link` longtext NOT NULL COMMENT '链接',
  `target` varchar(255) NOT NULL DEFAULT '_self' COMMENT '链接方式',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='导航';

--
-- 转存表中的数据 `qinggan_list_23`
--

INSERT INTO `qinggan_list_23` (`id`, `site_id`, `project_id`, `cate_id`, `link`, `target`) VALUES
(520, 1, 42, 0, 'a:2:{s:7:"default";s:9:"index.php";s:7:"rewrite";s:10:"index.html";}', '_self'),
(712, 1, 42, 0, 'a:2:{s:7:"default";s:21:"index.php?id=about-us";s:7:"rewrite";s:13:"about-us.html";}', '_self'),
(713, 1, 42, 0, 'a:2:{s:7:"default";s:17:"index.php?id=news";s:7:"rewrite";s:9:"news.html";}', '_self'),
(714, 1, 42, 0, 'a:2:{s:7:"default";s:20:"index.php?id=product";s:7:"rewrite";s:12:"product.html";}', '_self'),
(716, 1, 42, 0, 'a:2:{s:7:"default";s:17:"index.php?id=book";s:7:"rewrite";s:9:"book.html";}', '_self'),
(719, 1, 42, 0, 'a:2:{s:7:"default";s:23:"index.php?id=contact-us";s:7:"rewrite";s:15:"contact-us.html";}', '_self'),
(755, 1, 42, 0, 'a:2:{s:7:"default";s:17:"index.php?id=work";s:7:"rewrite";s:9:"work.html";}', '_self'),
(760, 1, 42, 0, 'a:2:{s:7:"default";s:30:"index.php?id=news&cate=company";s:7:"rewrite";s:17:"news/company.html";}', '_self'),
(761, 1, 42, 0, 'a:2:{s:7:"default";s:31:"index.php?id=news&cate=industry";s:7:"rewrite";s:18:"news/industry.html";}', '_self'),
(1254, 1, 42, 0, 'a:2:{s:7:"default";s:31:"index.php?id=development-course";s:7:"rewrite";s:23:"development-course.html";}', '_self'),
(1256, 1, 42, 0, 'a:2:{s:7:"default";s:18:"index.php?id=photo";s:7:"rewrite";s:10:"photo.html";}', '_self'),
(1298, 1, 42, 0, 'a:2:{s:7:"default";s:28:"index.php?id=download-center";s:7:"rewrite";s:20:"download-center.html";}', '_self'),
(1299, 1, 42, 0, 'a:2:{s:7:"default";s:16:"index.php?id=bbs";s:7:"rewrite";s:8:"bbs.html";}', '_self'),
(1300, 1, 147, 0, 'a:2:{s:7:"default";s:21:"index.php?id=about-us";s:7:"rewrite";s:13:"about-us.html";}', '_self'),
(1301, 1, 147, 0, 'a:2:{s:7:"default";s:31:"index.php?id=development-course";s:7:"rewrite";s:23:"development-course.html";}', '_self'),
(1302, 1, 147, 0, 'a:2:{s:7:"default";s:17:"index.php?id=news";s:7:"rewrite";s:9:"news.html";}', '_self'),
(1303, 1, 147, 0, 'a:2:{s:7:"default";s:17:"index.php?id=book";s:7:"rewrite";s:9:"book.html";}', '_self'),
(1304, 1, 147, 0, 'a:2:{s:7:"default";s:23:"index.php?id=contact-us";s:7:"rewrite";s:15:"contact-us.html";}', '_self');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_24`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_24` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `pictures` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `content` longtext NOT NULL COMMENT '内容',
  `spec_single` longtext NOT NULL COMMENT '产品属性',
  `qingdian` longtext NOT NULL COMMENT '包装清单',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品';

--
-- 转存表中的数据 `qinggan_list_24`
--

INSERT INTO `qinggan_list_24` (`id`, `site_id`, `project_id`, `cate_id`, `thumb`, `pictures`, `content`, `spec_single`, `qingdian`) VALUES
(1253, 1, 45, 168, '634', '631,633,522,634,632', '<p>这里编辑产品详细说明，支持图片！</p>', 'a:2:{i:0;a:17:{i:0;s:1:"1";i:1;b:0;i:2;s:1:"1";i:3;s:1:"1";i:4;s:1:"1";i:5;s:1:"1";i:6;s:1:"1";i:7;b:0;i:8;s:1:"1";i:9;b:0;i:10;s:1:"1";i:11;b:0;i:12;b:0;i:13;b:0;i:14;b:0;i:15;b:0;i:16;b:0;}i:1;a:17:{i:0;s:6:"长袖";i:1;b:0;i:2;s:12:"简约纯色";i:3;s:12:"L，XL，XXL";i:4;s:6:"青年";i:5;s:9:"修身型";i:6;s:24:"草绿，纯白，淡灰";i:7;b:0;i:8;s:6:"方领";i:9;b:0;i:10;s:3:"棉";i:11;b:0;i:12;b:0;i:13;b:0;i:14;b:0;i:15;b:0;i:16;b:0;}}', ''),
(1306, 1, 45, 191, '636', '635,636', '<p>这款极为精致讲究的项链，缀有闪烁独特的浅粉蓝色切割水晶，并添加了施华洛世奇独有的闪钻效果，令整体设计更璀璨耀眼。作品随附一条镀白金色项链，是配衬日常装扮的不二之选。</p>', 'a:2:{i:0;a:17:{i:0;b:0;i:1;b:0;i:2;b:0;i:3;b:0;i:4;b:0;i:5;b:0;i:6;b:0;i:7;b:0;i:8;b:0;i:9;b:0;i:10;s:1:"1";i:11;b:0;i:12;s:1:"1";i:13;s:1:"1";i:14;b:0;i:15;b:0;i:16;s:1:"1";}i:1;a:17:{i:0;b:0;i:1;b:0;i:2;b:0;i:3;b:0;i:4;b:0;i:5;b:0;i:6;b:0;i:7;b:0;i:8;b:0;i:9;b:0;i:10;s:21:"施华洛世奇水晶";i:11;b:0;i:12;s:6:"项链";i:13;s:6:"60.00g";i:14;b:0;i:15;b:0;i:16;s:9:"奥地利";}}', '清单1\r\n清单2');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_40`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_40` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `content` longtext NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='关于我们';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_46`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_46` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `fullname` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `content` longtext NOT NULL COMMENT '内容',
  `adm_reply` longtext NOT NULL COMMENT '管理员回复',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='留言模块';

--
-- 转存表中的数据 `qinggan_list_46`
--

INSERT INTO `qinggan_list_46` (`id`, `site_id`, `project_id`, `cate_id`, `fullname`, `email`, `content`, `adm_reply`) VALUES
(1258, 1, 96, 0, 'phpok', 'admin@phpok.com', '您好，测试最新留言功能', '<p>测试管理员回复，感<strong>谢您的</strong>支持，回复支持HTML噢！</p>'),
(1279, 1, 96, 0, '这个是测试的', 'seika@admin.com', '这个也是测试的', ''),
(1285, 1, 96, 0, '测试留言', '测试留言', '测试留言', ''),
(1286, 1, 96, 0, 'test', 'admin@phpok.com', '这个留言是测试用的', ''),
(1293, 1, 96, 0, 'seika', 'seika@phpok.com', '这个内容是测试的`', ''),
(1297, 1, 96, 0, 'seika', 'seika@phpok.com', '这个是测试用的', ''),
(1308, 1, 96, 0, 'seika', 'seika@phpok.com', '这个留言是测试用的！', '<p>测试下留言的回复功能！</p>');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_61`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_61` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `link` longtext NOT NULL COMMENT '链接',
  `target` varchar(255) NOT NULL DEFAULT '_self' COMMENT '链接方式',
  `tel` varchar(255) NOT NULL DEFAULT '' COMMENT '联系人电话',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='友情链接';

--
-- 转存表中的数据 `qinggan_list_61`
--

INSERT INTO `qinggan_list_61` (`id`, `site_id`, `project_id`, `cate_id`, `link`, `target`, `tel`) VALUES
(1252, 1, 142, 0, 'http://www.phpok.com', '_blank', ''),
(1261, 1, 142, 0, 'http://www.sz-qibang.com/', '_blank', ''),
(1262, 1, 142, 0, 'http://www.17tengfei.com/', '_blank', ''),
(1263, 1, 142, 0, 'http://www.7139.com', '_blank', ''),
(1264, 1, 142, 0, 'http://www.chinaz.com/', '_blank', ''),
(1265, 1, 142, 0, 'http://www.admin5.com/', '_blank', ''),
(1266, 1, 142, 0, 'http://www.cnzz.cn/', '_blank', ''),
(1267, 1, 142, 0, 'http://www.im286.com/', '_blank', ''),
(1268, 1, 142, 0, 'http://www.mycodes.net/', '_blank', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_64`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_64` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `code` longtext NOT NULL COMMENT '客服代码',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客服';

--
-- 转存表中的数据 `qinggan_list_64`
--

INSERT INTO `qinggan_list_64` (`id`, `site_id`, `project_id`, `cate_id`, `code`) VALUES
(1305, 1, 148, 0, '<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=40782502&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:40782502:51" alt="点击这里给我发消息" title="点击这里给我发消息"/></a>');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_65`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_65` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `file` varchar(255) NOT NULL DEFAULT '' COMMENT '压缩文件',
  `note` longtext NOT NULL COMMENT '摘要',
  `fsize` varchar(255) NOT NULL DEFAULT '' COMMENT '文件大小',
  `content` longtext NOT NULL COMMENT '内容',
  `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本',
  `website` varchar(255) NOT NULL DEFAULT '' COMMENT '官方网站',
  `platform` varchar(255) NOT NULL DEFAULT '' COMMENT '适用平台',
  `devlang` varchar(255) NOT NULL DEFAULT '' COMMENT '开发语言',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '开发商',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `copyright` varchar(255) NOT NULL DEFAULT '' COMMENT '授权协议',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资源下载';

--
-- 转存表中的数据 `qinggan_list_65`
--

INSERT INTO `qinggan_list_65` (`id`, `site_id`, `project_id`, `cate_id`, `file`, `note`, `fsize`, `content`, `version`, `website`, `platform`, `devlang`, `author`, `thumb`, `copyright`) VALUES
(1310, 1, 151, 198, '648', '​测试下载~', '10MB', '<p>测试下载~<br/></p>', '1.0', 'http://www.phpok.com', 'OS', 'PHP/MySQL', 'PHPOK.com', '625', '免费版');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_66`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_66` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `content` longtext NOT NULL COMMENT '内容',
  `toplevel` varchar(255) NOT NULL DEFAULT '0' COMMENT '置顶',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='论坛BBS';

--
-- 转存表中的数据 `qinggan_list_66`
--

INSERT INTO `qinggan_list_66` (`id`, `site_id`, `project_id`, `cate_id`, `content`, `toplevel`, `thumb`) VALUES
(1311, 1, 152, 204, '<p>测试论坛功能</p>', '0', ''),
(1334, 1, 152, 204, '<p>测试</p>', '0', ''),
(1336, 1, 152, 204, '<p>这个图片要搁在哪呢~~</p>', '0', '669'),
(1348, 1, 152, 204, '<p>测试权限功能</p>', '0', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_67`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_67` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `account` varchar(255) NOT NULL DEFAULT '' COMMENT '会员账号',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='注册验证';

--
-- 转存表中的数据 `qinggan_list_67`
--

INSERT INTO `qinggan_list_67` (`id`, `site_id`, `project_id`, `cate_id`, `account`) VALUES
(1333, 1, 153, 0, ''),
(1332, 1, 153, 0, 'suxiangkun');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_68`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_68` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `pictures` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图集相册';

--
-- 转存表中的数据 `qinggan_list_68`
--

INSERT INTO `qinggan_list_68` (`id`, `site_id`, `project_id`, `cate_id`, `thumb`, `pictures`) VALUES
(1341, 1, 144, 155, '683', '687,683'),
(1342, 1, 144, 155, '684', '684,685,686');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_tag`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_tag` (
  `id` int(10) unsigned NOT NULL COMMENT '自增ID',
  `title_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TAG标签ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内容中的Tag管理器';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_log`
--

CREATE TABLE IF NOT EXISTS `qinggan_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` text NOT NULL COMMENT '日志内容',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `app` varchar(100) NOT NULL COMMENT '控制器',
  `action` varchar(100) NOT NULL COMMENT '方法',
  `app_id` varchar(100) NOT NULL COMMENT '应用ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='日志表' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_module`
--

CREATE TABLE IF NOT EXISTS `qinggan_module` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `title` varchar(255) NOT NULL COMMENT '模块名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '模块排序',
  `note` varchar(255) NOT NULL COMMENT '模块说明',
  `layout` text NOT NULL COMMENT '布局',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模块管理，每创建一个模块自动创建一个表' AUTO_INCREMENT=69 ;

--
-- 转存表中的数据 `qinggan_module`
--

INSERT INTO `qinggan_module` (`id`, `title`, `status`, `taxis`, `note`, `layout`) VALUES
(21, '图片轮播', 1, 20, '适用于图片播放器，图片友情链接', 'pic,link,target'),
(22, '文章资讯', 1, 10, '适用于新闻，文章之类', 'hits,dateline,thumb'),
(23, '自定义链接', 1, 30, '适用于导航，页脚文本导航，文字友情链接', 'link,target'),
(24, '产品', 1, 40, '适用于电子商务中产品展示模型', 'hits,dateline,thumb'),
(40, '单页信息', 1, 60, '适用于公司简介，联系我们', 'hits,dateline'),
(46, '留言模块', 1, 100, '', 'dateline,fullname,email,content'),
(61, '友情链接', 1, 120, '适用于导航，页脚文本导航，文字友情链接', 'link,target,tel'),
(64, '客服', 1, 130, '', 'dateline'),
(65, '资源下载', 1, 70, '', 'hits,dateline,fsize,version,author,website,thumb'),
(66, '论坛BBS', 1, 50, '', 'hits,dateline'),
(67, '注册验证', 1, 255, '', 'dateline,account'),
(68, '图集相册', 1, 80, '', 'hits,dateline,thumb');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_module_fields`
--

CREATE TABLE IF NOT EXISTS `qinggan_module_fields` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段ID，自增',
  `module_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '模块ID',
  `title` varchar(255) NOT NULL COMMENT '字段名称',
  `identifier` varchar(50) NOT NULL COMMENT '字段标识串',
  `field_type` varchar(255) NOT NULL DEFAULT '200' COMMENT '字段存储类型',
  `note` varchar(255) NOT NULL COMMENT '字段内容备注',
  `form_type` varchar(100) NOT NULL COMMENT '表单类型',
  `form_style` varchar(255) NOT NULL COMMENT '表单CSS',
  `format` varchar(100) NOT NULL COMMENT '格式化方式',
  `content` varchar(255) NOT NULL COMMENT '默认值',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `ext` text NOT NULL COMMENT '扩展内容',
  `is_front` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0前端不可用1前端可用',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=245 ;

--
-- 转存表中的数据 `qinggan_module_fields`
--

INSERT INTO `qinggan_module_fields` (`id`, `module_id`, `title`, `identifier`, `field_type`, `note`, `form_type`, `form_style`, `format`, `content`, `taxis`, `ext`, `is_front`) VALUES
(92, 21, '链接', 'link', 'longtext', '', 'text', '', 'safe', '', 90, 'a:2:{s:8:"form_btn";s:3:"url";s:5:"width";s:3:"500";}', 0),
(82, 22, '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";b:0;}', 0),
(83, 22, '内容', 'content', 'longtext', '', 'editor', '', 'html_js', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";i:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";i:1;s:8:"btn_info";i:1;s:7:"is_read";i:0;s:5:"etype";b:0;s:7:"btn_tpl";i:1;s:7:"btn_map";i:1;}', 0),
(84, 23, '链接', 'link', 'longtext', '设置导航链接', 'url', '', 'safe', '', 90, 'a:1:{s:5:"width";s:3:"500";}', 0),
(85, 23, '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:1:{s:11:"option_list";s:5:"opt:6";}', 0),
(87, 24, '缩略图', 'thumb', 'varchar', '主要应用于列表及首页调用中使用', 'upload', '', 'safe', '', 30, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";b:0;}', 0),
(88, 24, '图片', 'pictures', 'varchar', '设置产品的图片，支持多图', 'upload', '', 'safe', '', 50, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"1";}', 0),
(90, 24, '内容', 'content', 'longtext', '填写产品介绍信息', 'editor', '', 'html_js', '', 255, 'a:7:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";i:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;}', 0),
(93, 21, '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_blank', 100, 'a:3:{s:11:"option_list";s:5:"opt:6";s:9:"put_order";s:1:"0";s:10:"ext_select";b:0;}', 0),
(131, 40, '内容', 'content', 'longtext', '', 'editor', '', 'html_js', '', 255, 'a:7:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";s:1:"1";s:9:"btn_video";s:1:"1";s:8:"btn_file";s:1:"1";s:8:"btn_page";b:0;}', 0),
(141, 46, '姓名', 'fullname', 'varchar', '', 'text', '', 'safe', '', 10, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 1),
(142, 46, '邮箱', 'email', 'varchar', '', 'text', '', 'safe', '', 130, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 1),
(143, 46, '内容', 'content', 'longtext', '', 'textarea', '', 'safe', '', 200, 'a:2:{s:5:"width";s:3:"500";s:6:"height";s:3:"180";}', 1),
(144, 46, '管理员回复', 'adm_reply', 'longtext', '', 'editor', '', 'html', '', 255, 'a:7:{s:5:"width";s:3:"800";s:6:"height";s:3:"100";s:7:"is_code";i:0;s:9:"btn_image";i:0;s:9:"btn_video";i:0;s:8:"btn_file";i:0;s:8:"btn_page";b:0;}', 0),
(200, 21, '图片', 'pic', 'varchar', '图片宽高建议使用980x180', 'upload', '', 'safe', '', 20, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 0),
(177, 22, '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:"width";s:3:"800";s:6:"height";s:2:"80";}', 0),
(204, 61, '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:1:{s:11:"option_list";s:5:"opt:6";}', 0),
(203, 61, '链接', 'link', 'longtext', '填写链接要求带上http://', 'text', 'height:22px;line-height:22px;padding:3px;border:1px solid #ccc;', 'safe', '', 90, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"280";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 1),
(217, 24, '参数', 'spec_single', 'longtext', '设置产品的规格参数，不需要的参数请将前面的勾去掉', 'param', '', 'safe', '', 110, 'a:3:{s:6:"p_name";s:140:"袖型\r\n细节\r\n风格\r\n尺码\r\n人群\r\n版型\r\n颜色\r\n元素\r\n领型\r\n图案\r\n材质\r\n镶嵌方式\r\n款式\r\n重量\r\n圈号\r\n证书\r\n产地";s:6:"p_type";s:1:"0";s:7:"p_width";b:0;}', 0),
(218, 64, '客服代码', 'code', 'longtext', '请输入相应的客服代码，不支持JS', 'code_editor', '', 'html', '', 20, 'a:2:{s:5:"width";s:3:"500";s:6:"height";s:3:"100";}', 0),
(219, 24, '包装清单', 'qingdian', 'longtext', '设置产品包装中包含哪些清单', 'textarea', '', 'safe', '', 130, 'a:2:{s:5:"width";s:3:"500";s:6:"height";s:2:"80";}', 0),
(220, 65, '压缩文件', 'file', 'varchar', '仅支持压缩文件', 'upload', '', 'safe', '', 60, 'a:3:{s:11:"upload_type";s:3:"zip";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 0),
(221, 65, '摘要', 'note', 'longtext', '简要描述下载信息', 'textarea', '', 'safe', '', 120, 'a:2:{s:5:"width";s:3:"600";s:6:"height";s:2:"80";}', 0),
(222, 65, '文件大小', 'fsize', 'varchar', '设置文件大小，注意填写相应的单位，如KB，MB', 'text', '', 'safe', '', 10, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 0),
(226, 65, '版本', 'version', 'varchar', '设置软件版本号', 'text', '', 'safe', '', 15, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"100";}', 0),
(227, 65, '官方网站', 'website', 'varchar', '请输入软件官方网址，没有请留空，需要加 http://', 'text', '', 'safe', 'http://', 30, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 0),
(224, 65, '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:4:"full";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}', 0),
(228, 65, '适用平台', 'platform', 'varchar', '请填写该软件适用在哪个平台下运行', 'text', '', 'safe', '', 40, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"500";s:15:"ext_quick_words";s:93:"WinXP\r\nWin2003\r\nWinVista\r\nWin7\r\nWin8\r\nWin2008\r\nWin2012\r\nCentOS\r\nRedHat\r\nUbuntu\r\nFreeBSD\r\nOS\r\n";s:14:"ext_quick_type";s:1:"/";}', 0),
(229, 65, '开发语言及数据库', 'devlang', 'varchar', '设置该软件的开发语言及数据库', 'text', '', 'safe', '', 50, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";s:15:"ext_quick_words";s:108:"PHP\r\nASP\r\nJSP\r\nPerl\r\nHTML\r\nJS\r\nMySQL\r\nAccess\r\nSQLite\r\nOracle\r\nC++\r\nC#\r\nVB\r\nDephi\r\nJava\r\nPython\r\nRuby\r\n其他";s:14:"ext_quick_type";s:1:"/";}', 0),
(230, 65, '开发商', 'author', 'varchar', '设置开发商名称', 'text', '', 'safe', '', 20, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 0),
(231, 65, '缩略图', 'thumb', 'varchar', '设置附件缩略图，宽高为420x330', 'upload', '', 'safe', '', 110, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 0),
(232, 65, '授权协议', 'copyright', 'varchar', '针对这个软件设置相应的授权协议', 'radio', '', 'safe', '免费版', 70, 'a:3:{s:11:"option_list";b:0;s:9:"put_order";s:1:"0";s:10:"ext_select";s:97:"免费版\r\n共享版\r\n试用版\r\n商业版\r\n开源软件\r\nGPL\r\nLGPL\r\nApache License\r\n其他授权";}', 0),
(233, 66, '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";i:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";i:0;s:8:"btn_info";i:0;s:7:"is_read";i:0;s:5:"etype";s:6:"simple";s:7:"btn_tpl";i:0;s:7:"btn_map";i:0;}', 1),
(234, 66, '置顶', 'toplevel', 'varchar', '', 'radio', '', 'int', '0', 10, 'a:3:{s:11:"option_list";s:6:"opt:12";s:9:"put_order";s:1:"0";s:10:"ext_select";b:0;}', 0),
(235, 67, '会员账号', 'account', 'varchar', '', 'text', '', 'safe', '', 10, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 0),
(238, 66, '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 1),
(239, 68, '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"0";}', 0),
(240, 68, '图片', 'pictures', 'varchar', '支持多图', 'upload', '', 'safe', '', 50, 'a:3:{s:11:"upload_type";s:7:"picture";s:7:"cate_id";s:1:"1";s:11:"is_multiple";s:1:"1";}', 0),
(244, 61, '联系人电话', 'tel', 'varchar', '填写联系人电话，以方便与人取得联系', 'text', 'height:22px;line-height:22px;padding:3px;border:1px solid #ccc;', 'safe', '', 110, 'a:4:{s:8:"form_btn";b:0;s:5:"width";s:3:"280";s:15:"ext_quick_words";b:0;s:14:"ext_quick_type";b:0;}', 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_opt`
--

CREATE TABLE IF NOT EXISTS `qinggan_opt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '组ID',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `val` varchar(255) NOT NULL COMMENT '值',
  `taxis` int(10) unsigned NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='表单列表选项' AUTO_INCREMENT=65 ;

--
-- 转存表中的数据 `qinggan_opt`
--

INSERT INTO `qinggan_opt` (`id`, `group_id`, `parent_id`, `title`, `val`, `taxis`) VALUES
(1, 1, 0, '女', '0', 20),
(2, 1, 0, '男', '1', 10),
(5, 2, 0, '福建省', '福建省', 255),
(6, 2, 5, '泉州市', '泉州市', 255),
(7, 2, 6, '永春县', '永春县', 255),
(8, 2, 7, '一都镇', '一都镇', 255),
(9, 2, 8, '美岭村', '美岭村', 255),
(11, 2, 0, '广东省', '广东省', 255),
(12, 2, 11, '深圳市', '深圳市', 255),
(13, 2, 12, '龙岗区', '龙岗区', 10),
(14, 2, 12, '罗湖区', '罗湖区', 20),
(15, 2, 12, '福田区', '福田区', 30),
(16, 2, 12, '龙华区', '龙华区', 40),
(17, 4, 0, '是', '1', 10),
(18, 4, 0, '否', '0', 20),
(21, 6, 0, '当前窗口', '_self', 10),
(22, 6, 0, '新窗口', '_blank', 20),
(23, 7, 0, '启用', '1', 10),
(24, 7, 0, '禁用', '0', 20),
(25, 8, 0, 'UTF-8', 'utf8', 20),
(26, 8, 0, 'GBK', 'gbk', 10),
(62, 12, 0, '不置顶', '0', 10),
(63, 12, 0, '一级置顶', '1', 20),
(64, 12, 0, '二级置顶', '2', 30);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_opt_group`
--

CREATE TABLE IF NOT EXISTS `qinggan_opt_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `title` varchar(100) NOT NULL COMMENT '名称，用于后台管理',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='可选菜单管理器' AUTO_INCREMENT=13 ;

--
-- 转存表中的数据 `qinggan_opt_group`
--

INSERT INTO `qinggan_opt_group` (`id`, `title`) VALUES
(1, '性别'),
(2, '省市县多级联动'),
(4, '是与否'),
(6, '窗口打开方式'),
(7, '注册'),
(8, '邮件编码'),
(12, '置顶属性');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order`
--

CREATE TABLE IF NOT EXISTS `qinggan_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `sn` varchar(255) NOT NULL COMMENT '订单编号，唯一值',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示游客',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `qty` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品数量',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '金额',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '货币类型',
  `status` varchar(255) NOT NULL COMMENT '订单的最后状态',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `passwd` varchar(255) NOT NULL COMMENT '密码串',
  `ext` text NOT NULL COMMENT '扩展内容信息，可用于存储一些扩展信息',
  `note` text NOT NULL COMMENT '摘要',
  `pay_id` int(11) NOT NULL COMMENT '支付接口ID',
  `pay_title` varchar(255) NOT NULL COMMENT '支付名称',
  `pay_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间戳',
  `pay_status` varchar(255) NOT NULL COMMENT '支付状态',
  `pay_price` varchar(255) NOT NULL COMMENT '支付金额',
  `pay_currency` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付货币类型',
  `pay_currency_code` varchar(20) NOT NULL COMMENT '支付货币简码',
  `pay_currency_rate` decimal(13,8) unsigned NOT NULL DEFAULT '0.00000000' COMMENT '汇率',
  `pay_end` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1支付已审核并已结束0表示正在进行中',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordersn` (`sn`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单中心' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_address`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `country` varchar(255) NOT NULL DEFAULT '中国' COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省信息',
  `city` varchar(255) NOT NULL COMMENT '市',
  `county` varchar(255) NOT NULL COMMENT '县',
  `address` varchar(255) NOT NULL COMMENT '地址信息（不含国家，省市县镇区信息）',
  `zipcode` varchar(20) NOT NULL COMMENT '邮编',
  `type_id` enum('shipping','billing') NOT NULL DEFAULT 'shipping' COMMENT '类型，默认走送货地址',
  `mobile` varchar(100) NOT NULL COMMENT '手机号码',
  `tel` varchar(100) NOT NULL COMMENT '电话号码',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `fullname` varchar(100) NOT NULL COMMENT '联系人姓名',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0女1男',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单地址库' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_product`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `title` varchar(255) NOT NULL COMMENT '产品名称',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '产品单价',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `thumb` int(11) NOT NULL COMMENT '产品图片ID',
  `ext` text NOT NULL COMMENT '产品扩展属性',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单的产品信息' AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_payment`
--

CREATE TABLE IF NOT EXISTS `qinggan_payment` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `gid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '付款组',
  `code` varchar(100) NOT NULL COMMENT '标识ID',
  `title` varchar(255) NOT NULL COMMENT '主题',
  `currency` varchar(30) NOT NULL COMMENT '可使用的货币ID',
  `logo1` varchar(255) NOT NULL COMMENT 'LOGO小图',
  `logo2` varchar(255) NOT NULL COMMENT 'LOGO中图',
  `logo3` varchar(255) NOT NULL COMMENT 'LOGO大图',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态0未使用1正在使用中',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `note` text NOT NULL COMMENT '付款注意事项说明',
  `param` text NOT NULL COMMENT '参数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `qinggan_payment`
--

INSERT INTO `qinggan_payment` (`id`, `gid`, `code`, `title`, `currency`, `logo1`, `logo2`, `logo3`, `status`, `taxis`, `note`, `param`) VALUES
(1, 1, 'alipay', '支付宝快捷支付', 'CNY', '', '', '', 1, 255, '', 'a:4:{s:3:"pid";b:0;s:3:"key";b:0;s:5:"email";b:0;s:5:"ptype";s:25:"create_direct_pay_by_user";}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_payment_group`
--

CREATE TABLE IF NOT EXISTS `qinggan_payment_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点ID，为0表示全部',
  `title` varchar(255) NOT NULL COMMENT '付款组名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不启用1启用',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1默认组0普通组',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='付款组管理' AUTO_INCREMENT=5 ;

--
-- 转存表中的数据 `qinggan_payment_group`
--

INSERT INTO `qinggan_payment_group` (`id`, `site_id`, `title`, `status`, `taxis`, `is_default`) VALUES
(1, 1, '快捷支付', 1, 10, 0),
(2, 1, '银行卡支付', 1, 20, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_phpok`
--

CREATE TABLE IF NOT EXISTS `qinggan_phpok` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `type_id` varchar(255) NOT NULL COMMENT '调用类型',
  `identifier` varchar(100) NOT NULL COMMENT '标识串，同一个站点中只能唯一',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `cateid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `ext` text NOT NULL COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`,`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=104 ;

--
-- 转存表中的数据 `qinggan_phpok`
--

INSERT INTO `qinggan_phpok` (`id`, `title`, `pid`, `type_id`, `identifier`, `site_id`, `status`, `cateid`, `ext`) VALUES
(18, '网站首页图片播放', 41, 'arclist', 'picplayer', 1, 1, 0, 'a:23:{s:5:"psize";s:1:"5";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";s:1:"1";s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(19, '头部导航内容', 42, 'arclist', 'menu', 1, 1, 0, 'a:23:{s:5:"psize";s:2:"80";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";s:1:"1";s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";s:1:"1";s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(20, '公司简介', 90, 'project', 'aboutus', 1, 1, 0, 'a:20:{s:5:"psize";b:0;s:6:"offset";b:0;s:7:"is_list";b:0;s:7:"in_text";b:0;s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";b:0;s:12:"catelist_ext";b:0;s:11:"project_ext";i:1;s:11:"sublist_ext";b:0;s:10:"parent_ext";b:0;s:13:"fields_format";b:0;s:8:"user_ext";b:0;s:4:"user";b:0;s:12:"userlist_ext";b:0;s:6:"in_sub";b:0;}'),
(21, '产品分类', 45, 'catelist', 'products_cate', 1, 1, 70, 'a:20:{s:5:"psize";b:0;s:6:"offset";b:0;s:7:"is_list";b:0;s:7:"in_text";b:0;s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";b:0;s:12:"catelist_ext";b:0;s:11:"project_ext";b:0;s:11:"sublist_ext";b:0;s:10:"parent_ext";b:0;s:13:"fields_format";b:0;s:8:"user_ext";b:0;s:4:"user";b:0;s:12:"userlist_ext";b:0;s:6:"in_sub";b:0;}'),
(22, '最新产品', 45, 'arclist', 'new_products', 1, 1, 70, 'a:23:{s:5:"psize";i:8;s:6:"offset";i:0;s:7:"is_list";i:1;s:7:"in_text";i:1;s:4:"attr";b:0;s:11:"fields_need";s:9:"ext.thumb";s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:1;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(55, '友情链接', 142, 'arclist', 'link', 1, 1, 0, 'a:23:{s:5:"psize";s:2:"30";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";s:1:"1";s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";s:1:"2";s:7:"in_cate";b:0;s:8:"title_id";b:0;}'),
(91, '新闻中心', 43, 'arclist', 'news', 1, 1, 7, 'a:23:{s:5:"psize";s:1:"8";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";s:1:"2";s:7:"in_cate";b:0;s:8:"title_id";b:0;}'),
(92, '图集相册', 144, 'arclist', 'photo', 1, 1, 0, 'a:23:{s:5:"psize";s:2:"10";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";s:9:"ext.thumb";s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";s:1:"1";s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(93, '图片滚动新闻', 43, 'arclist', 'picnews', 1, 1, 7, 'a:23:{s:5:"psize";s:2:"10";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";s:9:"ext.thumb";s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(94, '页脚导航', 147, 'arclist', 'footnav', 1, 1, 0, 'a:23:{s:5:"psize";s:2:"10";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";s:1:"1";s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(95, '客服', 148, 'arclist', 'kefu', 1, 1, 0, 'a:23:{s:5:"psize";s:2:"50";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";s:1:"1";s:4:"attr";b:0;s:11:"fields_need";s:8:"ext.code";s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";s:1:"2";s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(96, '售后保障', 150, 'project', 'after-sale-protection', 1, 1, 0, 'a:23:{s:5:"psize";b:0;s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";s:1:"1";s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(97, '图集相册', 144, 'arclist', 'tujixiangce', 1, 1, 154, 'a:23:{s:5:"psize";s:1:"8";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";s:9:"ext.thumb";s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";s:1:"2";s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(98, '产品展示', 45, 'catelist', 'catelist', 1, 1, 70, 'a:23:{s:5:"psize";b:0;s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(99, '下载中心', 151, 'arclist', 'xiazaizhongxin', 1, 1, 197, 'a:23:{s:5:"psize";s:2:"10";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";s:8:"ext.file";s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(100, '导航菜单', 42, 'arclist', 'menu_mobile', 1, 1, 0, 'a:23:{s:5:"psize";s:1:"4";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";s:1:"1";s:4:"attr";s:6:"mobile";s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";i:0;s:7:"in_cate";i:0;s:8:"title_id";b:0;}'),
(101, '论坛BBS', 152, 'arclist', 'bbs_mobile', 1, 1, 201, 'a:23:{s:5:"psize";s:1:"8";s:6:"offset";i:0;s:7:"is_list";s:1:"1";s:7:"in_text";i:0;s:4:"attr";b:0;s:11:"fields_need";b:0;s:3:"tag";b:0;s:8:"keywords";b:0;s:7:"orderby";b:0;s:4:"cate";b:0;s:8:"cate_ext";i:0;s:12:"catelist_ext";i:0;s:11:"project_ext";i:0;s:11:"sublist_ext";i:0;s:10:"parent_ext";i:0;s:13:"fields_format";i:0;s:8:"user_ext";i:0;s:4:"user";b:0;s:12:"userlist_ext";i:0;s:6:"in_sub";i:0;s:10:"in_project";s:1:"1";s:7:"in_cate";i:0;s:8:"title_id";b:0;}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_plugins`
--

CREATE TABLE IF NOT EXISTS `qinggan_plugins` (
  `id` varchar(100) NOT NULL COMMENT '插件ID，仅限字母，数字及下划线',
  `title` varchar(255) NOT NULL COMMENT '插件名称',
  `author` varchar(255) NOT NULL COMMENT '开发者',
  `version` varchar(50) NOT NULL COMMENT '插件版本号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0禁用1使用',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '值越小越往前靠',
  `note` varchar(255) NOT NULL COMMENT '摘要说明',
  `param` text NOT NULL COMMENT '参数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='插件管理器';

--
-- 转存表中的数据 `qinggan_plugins`
--

INSERT INTO `qinggan_plugins` (`id`, `title`, `author`, `version`, `status`, `taxis`, `note`, `param`) VALUES
('identifier', '标识串自动生成工具', 'phpok.com', '1.0', 1, 10, '可实现以 title 的表单数据', 'a:5:{s:9:"is_youdao";i:1;s:7:"keyfrom";s:9:"phpok-com";s:5:"keyid";s:9:"108499576";s:10:"is_pingyin";i:1;s:5:"is_py";i:1;}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_popedom`
--

CREATE TABLE IF NOT EXISTS `qinggan_popedom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID，即自增ID',
  `gid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '所属组ID，对应sysmenu表中的ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID，仅在list中有效',
  `title` varchar(255) NOT NULL COMMENT '名称，如：添加，修改等',
  `identifier` varchar(255) NOT NULL COMMENT '字符串，如add，modify等',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限明细' AUTO_INCREMENT=690 ;

--
-- 转存表中的数据 `qinggan_popedom`
--

INSERT INTO `qinggan_popedom` (`id`, `gid`, `pid`, `title`, `identifier`, `taxis`) VALUES
(2, 19, 0, '配置全局', 'gset', 10),
(3, 19, 0, '内容', 'set', 20),
(4, 29, 0, '添加', 'add', 10),
(5, 29, 0, '修改', 'modify', 20),
(6, 29, 0, '删除', 'delete', 30),
(7, 18, 0, '添加', 'add', 10),
(8, 18, 0, '编辑', 'modify', 20),
(9, 18, 0, '删除', 'delete', 30),
(10, 23, 0, '添加', 'add', 10),
(11, 23, 0, '编辑', 'modify', 20),
(12, 23, 0, '删除', 'delete', 30),
(14, 22, 0, 'GD配置', 'gd', 10),
(15, 22, 0, '附件分类维护', 'cate', 20),
(16, 22, 0, '批处理', 'pl', 30),
(17, 16, 0, '配置', 'config', 10),
(18, 16, 0, '安装', 'install', 20),
(19, 16, 0, '卸载', 'uninstall', 30),
(20, 16, 0, '删除', 'delete', 40),
(21, 17, 0, '添加', 'add', 20),
(22, 13, 0, '添加', 'add', 10),
(23, 13, 0, '修改', 'modify', 20),
(24, 13, 0, '删除', 'delete', 30),
(25, 13, 0, '查看', 'list', 5),
(26, 19, 0, '查看', 'list', 5),
(27, 20, 0, '查看', 'list', 10),
(28, 20, 0, '编辑', 'set', 20),
(29, 20, 0, '添加', 'add', 30),
(30, 20, 0, '修改', 'modify', 40),
(31, 20, 0, '删除', 'delete', 50),
(32, 20, 0, '启用/禁用', 'status', 60),
(33, 21, 0, '查看', 'list', 10),
(34, 21, 0, '添加', 'add', 20),
(35, 21, 0, '编辑', 'modify', 30),
(36, 21, 0, '删除', 'delete', 40),
(37, 18, 0, '查看', 'list', 5),
(38, 23, 0, '查看', 'list', 5),
(83, 16, 0, '启用/禁用', 'status', 50),
(40, 16, 0, '查看', 'list', 5),
(41, 17, 0, '查看', 'list', 10),
(42, 18, 0, '扩展', 'ext', 40),
(43, 19, 0, '扩展', 'ext', 30),
(44, 14, 0, '查看', 'list', 10),
(45, 14, 0, '添加', 'add', 20),
(46, 14, 0, '修改', 'modify', 30),
(47, 14, 0, '删除', 'delete', 40),
(48, 25, 0, '查看', 'list', 10),
(49, 25, 0, '配置', 'set', 20),
(80, 14, 0, '启用/禁用', 'status', 50),
(52, 29, 0, '查看', 'list', 5),
(53, 27, 0, '查看', 'list', 10),
(54, 27, 0, '配置', 'set', 20),
(81, 19, 0, '网站', 'site', 40),
(82, 19, 0, '域名', 'domain', 50),
(58, 8, 0, '查看', 'list', 10),
(59, 8, 0, '维护', 'set', 20),
(84, 20, 1, '查看', 'list', 10),
(85, 20, 1, '编辑', 'set', 20),
(63, 6, 0, '查看', 'list', 10),
(64, 6, 0, '维护', 'set', 20),
(86, 20, 22, '查看', 'list', 10),
(67, 7, 0, '查看', 'list', 10),
(68, 7, 0, '添加', 'add', 20),
(69, 7, 0, '修改', 'modify', 30),
(70, 7, 0, '删除', 'delete', 40),
(71, 28, 0, '查看', 'list', 10),
(72, 28, 0, '添加', 'add', 20),
(73, 28, 0, '修改', 'modify', 30),
(74, 28, 0, '删除', 'delete', 40),
(75, 9, 0, '查看', 'list', 10),
(76, 9, 0, '添加', 'add', 20),
(77, 9, 0, '修改', 'modify', 30),
(78, 9, 0, '启用/禁用', 'status', 40),
(79, 29, 0, '启用/禁用', 'status', 40),
(87, 20, 22, '添加', 'add', 30),
(88, 20, 22, '修改', 'modify', 40),
(89, 20, 22, '删除', 'delete', 50),
(90, 20, 22, '启用/禁用', 'status', 60),
(91, 20, 24, '查看', 'list', 10),
(92, 20, 24, '添加', 'add', 30),
(93, 20, 24, '修改', 'modify', 40),
(94, 20, 24, '删除', 'delete', 50),
(95, 20, 24, '启用/禁用', 'status', 60),
(96, 20, 26, '查看', 'list', 10),
(97, 20, 26, '添加', 'add', 30),
(98, 20, 26, '修改', 'modify', 40),
(99, 20, 26, '删除', 'delete', 50),
(100, 20, 26, '启用/禁用', 'status', 60),
(101, 20, 25, '查看', 'list', 10),
(102, 20, 25, '编辑', 'set', 20),
(103, 20, 25, '添加', 'add', 30),
(104, 20, 25, '修改', 'modify', 40),
(105, 20, 25, '删除', 'delete', 50),
(106, 20, 25, '启用/禁用', 'status', 60),
(107, 20, 33, '查看', 'list', 10),
(108, 20, 33, '编辑', 'set', 20),
(109, 20, 34, '查看', 'list', 10),
(110, 20, 34, '添加', 'add', 30),
(111, 20, 34, '修改', 'modify', 40),
(112, 20, 34, '删除', 'delete', 50),
(113, 20, 34, '启用/禁用', 'status', 60),
(114, 20, 35, '查看', 'list', 10),
(115, 20, 35, '编辑', 'set', 20),
(116, 20, 36, '查看', 'list', 10),
(117, 20, 36, '编辑', 'set', 20),
(118, 20, 37, '查看', 'list', 10),
(119, 20, 37, '添加', 'add', 30),
(120, 20, 37, '修改', 'modify', 40),
(121, 20, 37, '删除', 'delete', 50),
(122, 20, 37, '启用/禁用', 'status', 60),
(123, 20, 38, '查看', 'list', 10),
(124, 20, 38, '添加', 'add', 30),
(125, 20, 38, '修改', 'modify', 40),
(126, 20, 38, '删除', 'delete', 50),
(127, 20, 38, '启用/禁用', 'status', 60),
(128, 20, 39, '查看', 'list', 10),
(129, 20, 39, '添加', 'add', 30),
(130, 20, 39, '修改', 'modify', 40),
(131, 20, 39, '删除', 'delete', 50),
(132, 20, 39, '启用/禁用', 'status', 60),
(133, 30, 0, '查看', 'list', 10),
(134, 30, 0, '设置', 'set', 20),
(135, 30, 0, '文件管理', 'filelist', 30),
(136, 30, 0, '删除', 'delete', 40),
(137, 20, 40, '查看', 'list', 10),
(138, 20, 40, '添加', 'add', 30),
(139, 20, 40, '修改', 'modify', 40),
(140, 20, 40, '删除', 'delete', 50),
(141, 20, 40, '启用/禁用', 'status', 60),
(142, 13, 0, '审核', 'status', 40),
(143, 20, 41, '查看', 'list', 10),
(144, 20, 41, '添加', 'add', 30),
(145, 20, 41, '修改', 'modify', 40),
(146, 20, 41, '删除', 'delete', 50),
(147, 20, 41, '启用/禁用', 'status', 60),
(148, 20, 42, '查看', 'list', 10),
(149, 20, 42, '添加', 'add', 30),
(150, 20, 42, '修改', 'modify', 40),
(151, 20, 42, '删除', 'delete', 50),
(152, 20, 42, '启用/禁用', 'status', 60),
(153, 20, 43, '查看', 'list', 10),
(154, 20, 43, '添加', 'add', 30),
(155, 20, 43, '修改', 'modify', 40),
(156, 20, 43, '删除', 'delete', 50),
(157, 20, 43, '启用/禁用', 'status', 60),
(162, 31, 0, '添加站点', 'add', 40),
(159, 31, 0, '查看', 'list', 10),
(160, 31, 0, '删除', 'delete', 20),
(161, 31, 0, '设为默认', 'default', 30),
(163, 20, 44, '查看', 'list', 10),
(164, 20, 44, '编辑', 'set', 20),
(165, 20, 45, '查看', 'list', 10),
(166, 20, 45, '添加', 'add', 30),
(167, 20, 45, '修改', 'modify', 40),
(168, 20, 45, '删除', 'delete', 50),
(169, 20, 45, '启用/禁用', 'status', 60),
(170, 19, 0, '添加站点', 'add', 60),
(171, 20, 46, '查看', 'list', 10),
(172, 20, 46, '添加', 'add', 30),
(173, 20, 46, '修改', 'modify', 40),
(174, 20, 46, '删除', 'delete', 50),
(175, 20, 46, '启用/禁用', 'status', 60),
(176, 20, 47, '查看', 'list', 10),
(177, 20, 47, '添加', 'add', 30),
(178, 20, 47, '修改', 'modify', 40),
(179, 20, 47, '删除', 'delete', 50),
(180, 20, 47, '启用/禁用', 'status', 60),
(181, 20, 48, '查看', 'list', 10),
(182, 20, 48, '添加', 'add', 30),
(183, 20, 48, '修改', 'modify', 40),
(184, 20, 48, '删除', 'delete', 50),
(185, 20, 48, '启用/禁用', 'status', 60),
(186, 20, 49, '查看', 'list', 10),
(187, 20, 49, '添加', 'add', 30),
(188, 20, 49, '修改', 'modify', 40),
(189, 20, 49, '删除', 'delete', 50),
(190, 20, 49, '启用/禁用', 'status', 60),
(191, 20, 50, '查看', 'list', 10),
(192, 20, 50, '添加', 'add', 30),
(193, 20, 50, '修改', 'modify', 40),
(194, 20, 50, '删除', 'delete', 50),
(195, 20, 50, '启用/禁用', 'status', 60),
(196, 20, 51, '查看', 'list', 10),
(197, 20, 51, '编辑', 'set', 20),
(198, 20, 51, '添加', 'add', 30),
(199, 20, 51, '修改', 'modify', 40),
(200, 20, 51, '删除', 'delete', 50),
(201, 20, 51, '启用/禁用', 'status', 60),
(202, 20, 52, '查看', 'list', 10),
(203, 20, 52, '编辑', 'set', 20),
(204, 20, 53, '查看', 'list', 10),
(205, 20, 53, '编辑', 'set', 20),
(206, 20, 54, '查看', 'list', 10),
(207, 20, 54, '添加', 'add', 30),
(208, 20, 54, '修改', 'modify', 40),
(209, 20, 54, '删除', 'delete', 50),
(210, 20, 54, '启用/禁用', 'status', 60),
(211, 20, 55, '查看', 'list', 10),
(212, 20, 55, '添加', 'add', 30),
(213, 20, 55, '修改', 'modify', 40),
(214, 20, 55, '删除', 'delete', 50),
(215, 20, 55, '启用/禁用', 'status', 60),
(216, 20, 56, '查看', 'list', 10),
(217, 20, 56, '编辑', 'set', 20),
(218, 20, 57, '查看', 'list', 10),
(219, 20, 57, '添加', 'add', 30),
(220, 20, 57, '修改', 'modify', 40),
(221, 20, 57, '删除', 'delete', 50),
(222, 20, 57, '启用/禁用', 'status', 60),
(223, 20, 58, '查看', 'list', 10),
(224, 20, 58, '添加', 'add', 30),
(225, 20, 58, '修改', 'modify', 40),
(226, 20, 58, '删除', 'delete', 50),
(227, 20, 58, '启用/禁用', 'status', 60),
(228, 20, 59, '查看', 'list', 10),
(229, 20, 59, '添加', 'add', 30),
(230, 20, 59, '修改', 'modify', 40),
(231, 20, 59, '删除', 'delete', 50),
(232, 20, 59, '启用/禁用', 'status', 60),
(233, 20, 60, '查看', 'list', 10),
(234, 20, 60, '添加', 'add', 30),
(235, 20, 60, '修改', 'modify', 40),
(236, 20, 60, '删除', 'delete', 50),
(237, 20, 60, '启用/禁用', 'status', 60),
(238, 20, 61, '查看', 'list', 10),
(239, 20, 61, '添加', 'add', 30),
(240, 20, 61, '修改', 'modify', 40),
(241, 20, 61, '删除', 'delete', 50),
(242, 20, 61, '启用/禁用', 'status', 60),
(243, 20, 62, '查看', 'list', 10),
(244, 20, 62, '编辑', 'set', 20),
(245, 20, 62, '添加', 'add', 30),
(246, 20, 62, '修改', 'modify', 40),
(247, 20, 62, '删除', 'delete', 50),
(248, 20, 62, '启用/禁用', 'status', 60),
(249, 20, 63, '查看', 'list', 10),
(250, 20, 63, '编辑', 'set', 20),
(251, 20, 64, '查看', 'list', 10),
(252, 20, 64, '编辑', 'set', 20),
(253, 20, 65, '查看', 'list', 10),
(254, 20, 65, '添加', 'add', 30),
(255, 20, 65, '修改', 'modify', 40),
(256, 20, 65, '删除', 'delete', 50),
(257, 20, 65, '启用/禁用', 'status', 60),
(258, 20, 66, '查看', 'list', 10),
(259, 20, 66, '添加', 'add', 30),
(260, 20, 66, '修改', 'modify', 40),
(261, 20, 66, '删除', 'delete', 50),
(262, 20, 66, '启用/禁用', 'status', 60),
(263, 20, 67, '查看', 'list', 10),
(264, 20, 67, '编辑', 'set', 20),
(265, 20, 68, '查看', 'list', 10),
(266, 20, 68, '添加', 'add', 30),
(267, 20, 68, '修改', 'modify', 40),
(268, 20, 68, '删除', 'delete', 50),
(269, 20, 68, '启用/禁用', 'status', 60),
(270, 20, 69, '查看', 'list', 10),
(271, 20, 69, '添加', 'add', 30),
(272, 20, 69, '修改', 'modify', 40),
(273, 20, 69, '删除', 'delete', 50),
(274, 20, 69, '启用/禁用', 'status', 60),
(275, 20, 70, '查看', 'list', 10),
(276, 20, 70, '添加', 'add', 30),
(277, 20, 70, '修改', 'modify', 40),
(278, 20, 70, '删除', 'delete', 50),
(279, 20, 70, '启用/禁用', 'status', 60),
(280, 20, 71, '查看', 'list', 10),
(281, 20, 71, '编辑', 'set', 20),
(282, 20, 72, '查看', 'list', 10),
(283, 20, 72, '编辑', 'set', 20),
(284, 20, 73, '查看', 'list', 10),
(285, 20, 73, '编辑', 'set', 20),
(286, 20, 74, '查看', 'list', 10),
(287, 20, 74, '编辑', 'set', 20),
(288, 20, 75, '查看', 'list', 10),
(289, 20, 75, '编辑', 'set', 20),
(290, 20, 76, '查看', 'list', 10),
(291, 20, 76, '编辑', 'set', 20),
(292, 20, 77, '查看', 'list', 10),
(293, 20, 77, '编辑', 'set', 20),
(294, 20, 77, '添加', 'add', 30),
(295, 20, 77, '修改', 'modify', 40),
(296, 20, 77, '删除', 'delete', 50),
(297, 20, 77, '启用/禁用', 'status', 60),
(298, 20, 78, '查看', 'list', 10),
(299, 20, 78, '添加', 'add', 30),
(300, 20, 78, '修改', 'modify', 40),
(301, 20, 78, '删除', 'delete', 50),
(302, 20, 78, '启用/禁用', 'status', 60),
(303, 20, 79, '查看', 'list', 10),
(304, 20, 79, '添加', 'add', 30),
(305, 20, 79, '修改', 'modify', 40),
(306, 20, 79, '删除', 'delete', 50),
(307, 20, 79, '启用/禁用', 'status', 60),
(308, 20, 80, '查看', 'list', 10),
(309, 20, 80, '编辑', 'set', 20),
(310, 20, 81, '查看', 'list', 10),
(311, 20, 81, '添加', 'add', 30),
(312, 20, 81, '修改', 'modify', 40),
(313, 20, 81, '删除', 'delete', 50),
(314, 20, 81, '启用/禁用', 'status', 60),
(315, 20, 82, '查看', 'list', 10),
(316, 20, 82, '添加', 'add', 30),
(317, 20, 82, '修改', 'modify', 40),
(318, 20, 82, '删除', 'delete', 50),
(319, 20, 82, '启用/禁用', 'status', 60),
(320, 20, 83, '查看', 'list', 10),
(321, 20, 83, '编辑', 'set', 20),
(322, 20, 83, '添加', 'add', 30),
(323, 20, 83, '修改', 'modify', 40),
(324, 20, 83, '删除', 'delete', 50),
(325, 20, 83, '启用/禁用', 'status', 60),
(326, 20, 84, '查看', 'list', 10),
(327, 20, 84, '编辑', 'set', 20),
(328, 20, 84, '添加', 'add', 30),
(329, 20, 84, '修改', 'modify', 40),
(330, 20, 84, '删除', 'delete', 50),
(331, 20, 84, '启用/禁用', 'status', 60),
(332, 20, 85, '查看', 'list', 10),
(333, 20, 85, '编辑', 'set', 20),
(334, 20, 85, '添加', 'add', 30),
(335, 20, 85, '修改', 'modify', 40),
(336, 20, 85, '删除', 'delete', 50),
(337, 20, 85, '启用/禁用', 'status', 60),
(338, 20, 86, '查看', 'list', 10),
(339, 20, 86, '编辑', 'set', 20),
(340, 20, 86, '添加', 'add', 30),
(341, 20, 86, '修改', 'modify', 40),
(342, 20, 86, '删除', 'delete', 50),
(343, 20, 86, '启用/禁用', 'status', 60),
(344, 32, 0, '查看', 'list', 10),
(345, 32, 0, '启用/禁用', 'status', 20),
(346, 32, 0, '删除', 'delete', 30),
(347, 32, 0, '修改', 'modify', 40),
(348, 32, 0, '回复', 'reply', 50),
(349, 20, 87, '查看', 'list', 10),
(350, 20, 87, '添加', 'add', 30),
(351, 20, 87, '修改', 'modify', 40),
(352, 20, 87, '删除', 'delete', 50),
(353, 20, 87, '启用/禁用', 'status', 60),
(354, 20, 88, '查看', 'list', 10),
(355, 20, 88, '添加', 'add', 30),
(356, 20, 88, '修改', 'modify', 40),
(357, 20, 88, '删除', 'delete', 50),
(358, 20, 88, '启用/禁用', 'status', 60),
(359, 20, 89, '查看', 'list', 10),
(360, 20, 89, '添加', 'add', 30),
(361, 20, 89, '修改', 'modify', 40),
(362, 20, 89, '删除', 'delete', 50),
(363, 20, 89, '启用/禁用', 'status', 60),
(364, 20, 90, '查看', 'list', 10),
(365, 20, 90, '编辑', 'set', 20),
(366, 20, 91, '查看', 'list', 10),
(367, 20, 91, '添加', 'add', 30),
(368, 20, 91, '修改', 'modify', 40),
(369, 20, 91, '删除', 'delete', 50),
(370, 20, 91, '启用/禁用', 'status', 60),
(371, 20, 92, '查看', 'list', 10),
(372, 20, 92, '编辑', 'set', 20),
(373, 20, 93, '查看', 'list', 10),
(378, 20, 94, '查看', 'list', 10),
(379, 20, 94, '添加', 'add', 30),
(380, 20, 94, '修改', 'modify', 40),
(381, 20, 94, '删除', 'delete', 50),
(382, 20, 94, '启用/禁用', 'status', 60),
(383, 20, 95, '查看', 'list', 10),
(384, 20, 95, '添加', 'add', 30),
(385, 20, 95, '修改', 'modify', 40),
(386, 20, 95, '删除', 'delete', 50),
(387, 20, 95, '启用/禁用', 'status', 60),
(388, 20, 96, '查看', 'list', 10),
(389, 20, 96, '添加', 'add', 30),
(390, 20, 96, '修改', 'modify', 40),
(391, 20, 96, '删除', 'delete', 50),
(392, 20, 96, '启用/禁用', 'status', 60),
(393, 20, 97, '查看', 'list', 10),
(394, 20, 97, '添加', 'add', 30),
(395, 20, 97, '修改', 'modify', 40),
(396, 20, 97, '删除', 'delete', 50),
(397, 20, 97, '启用/禁用', 'status', 60),
(398, 20, 98, '查看', 'list', 10),
(399, 20, 98, '添加', 'add', 30),
(400, 20, 98, '修改', 'modify', 40),
(401, 20, 98, '删除', 'delete', 50),
(402, 20, 98, '启用/禁用', 'status', 60),
(403, 20, 99, '查看', 'list', 10),
(404, 20, 99, '添加', 'add', 30),
(405, 20, 99, '修改', 'modify', 40),
(406, 20, 99, '删除', 'delete', 50),
(407, 20, 99, '启用/禁用', 'status', 60),
(408, 20, 100, '查看', 'list', 10),
(409, 20, 100, '添加', 'add', 30),
(410, 20, 100, '修改', 'modify', 40),
(411, 20, 100, '删除', 'delete', 50),
(412, 20, 100, '启用/禁用', 'status', 60),
(413, 20, 101, '查看', 'list', 10),
(414, 20, 101, '添加', 'add', 30),
(415, 20, 101, '修改', 'modify', 40),
(416, 20, 101, '删除', 'delete', 50),
(417, 20, 101, '启用/禁用', 'status', 60),
(418, 20, 102, '查看', 'list', 10),
(419, 20, 102, '添加', 'add', 30),
(420, 20, 102, '修改', 'modify', 40),
(421, 20, 102, '删除', 'delete', 50),
(422, 20, 102, '启用/禁用', 'status', 60),
(423, 20, 103, '查看', 'list', 10),
(424, 20, 103, '添加', 'add', 30),
(425, 20, 103, '修改', 'modify', 40),
(426, 20, 103, '删除', 'delete', 50),
(427, 20, 103, '启用/禁用', 'status', 60),
(428, 20, 104, '查看', 'list', 10),
(429, 20, 104, '添加', 'add', 30),
(430, 20, 104, '修改', 'modify', 40),
(431, 20, 104, '删除', 'delete', 50),
(432, 20, 104, '启用/禁用', 'status', 60),
(433, 20, 105, '查看', 'list', 10),
(434, 20, 105, '添加', 'add', 30),
(435, 20, 105, '修改', 'modify', 40),
(436, 20, 105, '删除', 'delete', 50),
(437, 20, 105, '启用/禁用', 'status', 60),
(438, 20, 106, '查看', 'list', 10),
(439, 20, 106, '添加', 'add', 30),
(440, 20, 106, '修改', 'modify', 40),
(441, 20, 106, '删除', 'delete', 50),
(442, 20, 106, '启用/禁用', 'status', 60),
(443, 20, 107, '查看', 'list', 10),
(444, 20, 107, '添加', 'add', 30),
(445, 20, 107, '修改', 'modify', 40),
(446, 20, 107, '删除', 'delete', 50),
(447, 20, 107, '启用/禁用', 'status', 60),
(448, 20, 108, '查看', 'list', 10),
(449, 20, 108, '添加', 'add', 30),
(450, 20, 108, '修改', 'modify', 40),
(451, 20, 108, '删除', 'delete', 50),
(452, 20, 108, '启用/禁用', 'status', 60),
(453, 20, 109, '查看', 'list', 10),
(454, 20, 109, '添加', 'add', 30),
(455, 20, 109, '修改', 'modify', 40),
(456, 20, 109, '删除', 'delete', 50),
(457, 20, 109, '启用/禁用', 'status', 60),
(458, 20, 110, '查看', 'list', 10),
(459, 20, 110, '编辑', 'set', 20),
(460, 20, 111, '查看', 'list', 10),
(461, 20, 111, '编辑', 'set', 20),
(462, 20, 112, '查看', 'list', 10),
(463, 20, 112, '编辑', 'set', 20),
(464, 20, 113, '查看', 'list', 10),
(465, 20, 113, '编辑', 'set', 20),
(466, 20, 114, '查看', 'list', 10),
(467, 20, 114, '添加', 'add', 30),
(468, 20, 114, '修改', 'modify', 40),
(469, 20, 114, '删除', 'delete', 50),
(470, 20, 114, '启用/禁用', 'status', 60),
(471, 20, 115, '查看', 'list', 10),
(472, 20, 115, '添加', 'add', 30),
(473, 20, 115, '修改', 'modify', 40),
(474, 20, 115, '删除', 'delete', 50),
(475, 20, 115, '启用/禁用', 'status', 60),
(476, 33, 0, '查看', 'list', 10),
(477, 33, 0, '添加', 'add', 20),
(478, 33, 0, '修改', 'modify', 30),
(479, 33, 0, '删除', 'delete', 40),
(480, 33, 0, '启用/禁用', 'status', 50),
(481, 20, 116, '查看', 'list', 10),
(482, 20, 116, '添加', 'add', 30),
(483, 20, 116, '修改', 'modify', 40),
(484, 20, 116, '删除', 'delete', 50),
(485, 20, 116, '启用/禁用', 'status', 60),
(486, 20, 117, '查看', 'list', 10),
(487, 20, 117, '添加', 'add', 30),
(488, 20, 117, '修改', 'modify', 40),
(489, 20, 117, '删除', 'delete', 50),
(490, 20, 117, '启用/禁用', 'status', 60),
(491, 20, 118, '查看', 'list', 10),
(492, 20, 118, '添加', 'add', 30),
(493, 20, 118, '修改', 'modify', 40),
(494, 20, 118, '删除', 'delete', 50),
(495, 20, 118, '启用/禁用', 'status', 60),
(496, 20, 119, '查看', 'list', 10),
(497, 20, 119, '添加', 'add', 30),
(498, 20, 119, '修改', 'modify', 40),
(499, 20, 119, '删除', 'delete', 50),
(500, 20, 119, '启用/禁用', 'status', 60),
(501, 20, 120, '查看', 'list', 10),
(502, 20, 120, '添加', 'add', 30),
(503, 20, 120, '修改', 'modify', 40),
(504, 20, 120, '删除', 'delete', 50),
(505, 20, 120, '启用/禁用', 'status', 60),
(506, 20, 121, '查看', 'list', 10),
(507, 20, 121, '添加', 'add', 30),
(508, 20, 121, '修改', 'modify', 40),
(509, 20, 121, '删除', 'delete', 50),
(510, 20, 121, '启用/禁用', 'status', 60),
(511, 20, 122, '查看', 'list', 10),
(512, 20, 122, '添加', 'add', 30),
(513, 20, 122, '修改', 'modify', 40),
(514, 20, 122, '删除', 'delete', 50),
(515, 20, 122, '启用/禁用', 'status', 60),
(516, 20, 123, '查看', 'list', 10),
(517, 20, 123, '添加', 'add', 30),
(518, 20, 123, '修改', 'modify', 40),
(519, 20, 123, '删除', 'delete', 50),
(520, 20, 123, '启用/禁用', 'status', 60),
(521, 20, 124, '查看', 'list', 10),
(522, 20, 124, '添加', 'add', 30),
(523, 20, 124, '修改', 'modify', 40),
(524, 20, 124, '删除', 'delete', 50),
(525, 20, 124, '启用/禁用', 'status', 60),
(526, 20, 125, '查看', 'list', 10),
(527, 20, 125, '添加', 'add', 30),
(528, 20, 125, '修改', 'modify', 40),
(529, 20, 125, '删除', 'delete', 50),
(530, 20, 125, '启用/禁用', 'status', 60),
(531, 20, 126, '查看', 'list', 10),
(532, 20, 126, '添加', 'add', 30),
(533, 20, 126, '修改', 'modify', 40),
(534, 20, 126, '删除', 'delete', 50),
(535, 20, 126, '启用/禁用', 'status', 60),
(536, 20, 128, '查看', 'list', 10),
(537, 20, 128, '添加', 'add', 30),
(538, 20, 128, '修改', 'modify', 40),
(539, 20, 128, '删除', 'delete', 50),
(540, 20, 128, '启用/禁用', 'status', 60),
(541, 20, 129, '查看', 'list', 10),
(542, 20, 129, '添加', 'add', 30),
(543, 20, 129, '修改', 'modify', 40),
(544, 20, 129, '删除', 'delete', 50),
(545, 20, 129, '启用/禁用', 'status', 60),
(546, 20, 130, '查看', 'list', 10),
(547, 20, 130, '添加', 'add', 30),
(548, 20, 130, '修改', 'modify', 40),
(549, 20, 130, '删除', 'delete', 50),
(550, 20, 130, '启用/禁用', 'status', 60),
(551, 20, 131, '查看', 'list', 10),
(552, 20, 131, '添加', 'add', 30),
(553, 20, 131, '修改', 'modify', 40),
(554, 20, 131, '删除', 'delete', 50),
(555, 20, 131, '启用/禁用', 'status', 60),
(556, 20, 132, '查看', 'list', 10),
(557, 20, 132, '添加', 'add', 30),
(558, 20, 132, '修改', 'modify', 40),
(559, 20, 132, '删除', 'delete', 50),
(560, 20, 132, '启用/禁用', 'status', 60),
(561, 20, 133, '查看', 'list', 10),
(562, 20, 133, '添加', 'add', 30),
(563, 20, 133, '修改', 'modify', 40),
(564, 20, 133, '删除', 'delete', 50),
(565, 20, 133, '启用/禁用', 'status', 60),
(566, 20, 134, '查看', 'list', 10),
(567, 20, 134, '添加', 'add', 30),
(568, 20, 134, '修改', 'modify', 40),
(569, 20, 134, '删除', 'delete', 50),
(570, 20, 134, '启用/禁用', 'status', 60),
(571, 20, 135, '查看', 'list', 10),
(572, 20, 135, '添加', 'add', 30),
(573, 20, 135, '修改', 'modify', 40),
(574, 20, 135, '删除', 'delete', 50),
(575, 20, 135, '启用/禁用', 'status', 60),
(576, 20, 136, '查看', 'list', 10),
(577, 20, 136, '添加', 'add', 30),
(578, 20, 136, '修改', 'modify', 40),
(579, 20, 136, '删除', 'delete', 50),
(580, 20, 136, '启用/禁用', 'status', 60),
(581, 20, 137, '查看', 'list', 10),
(582, 20, 137, '添加', 'add', 30),
(583, 20, 137, '修改', 'modify', 40),
(584, 20, 137, '删除', 'delete', 50),
(585, 20, 137, '启用/禁用', 'status', 60),
(586, 20, 138, '查看', 'list', 10),
(587, 20, 138, '添加', 'add', 30),
(588, 20, 138, '修改', 'modify', 40),
(589, 20, 138, '删除', 'delete', 50),
(590, 20, 138, '启用/禁用', 'status', 60),
(591, 20, 139, '查看', 'list', 10),
(592, 20, 139, '添加', 'add', 30),
(593, 20, 139, '修改', 'modify', 40),
(594, 20, 139, '删除', 'delete', 50),
(595, 20, 139, '启用/禁用', 'status', 60),
(596, 20, 140, '查看', 'list', 10),
(597, 20, 140, '编辑', 'set', 20),
(598, 20, 141, '查看', 'list', 10),
(599, 20, 141, '编辑', 'set', 20),
(600, 20, 93, '编辑', 'set', 20),
(601, 34, 0, '查看', 'list', 10),
(602, 34, 0, '添加', 'add', 20),
(603, 34, 0, '修改', 'modify', 30),
(604, 34, 0, '审核', 'status', 40),
(605, 34, 0, '删除', 'delete', 50),
(606, 20, 142, '查看', 'list', 10),
(607, 20, 142, '编辑', 'set', 20),
(608, 20, 142, '添加', 'add', 30),
(609, 20, 142, '修改', 'modify', 40),
(610, 20, 142, '删除', 'delete', 50),
(611, 20, 142, '启用/禁用', 'status', 60),
(612, 20, 144, '查看', 'list', 10),
(613, 20, 144, '添加', 'add', 30),
(614, 20, 144, '修改', 'modify', 40),
(615, 20, 144, '删除', 'delete', 50),
(616, 20, 144, '启用/禁用', 'status', 60),
(617, 42, 0, '查看', 'list', 10),
(618, 42, 0, '执行', 'set', 20),
(619, 43, 0, '查看', 'list', 10),
(620, 44, 0, '查看', 'list', 10),
(621, 45, 0, '查看', 'list', 10),
(622, 45, 0, '升级', 'update', 20),
(623, 45, 0, '配置升级服务器', 'set', 30),
(624, 46, 0, '查看', 'list', 10),
(625, 9, 0, '删除', 'delete', 50),
(626, 52, 0, '查看', 'list', 10),
(627, 52, 0, '添加组', 'groupadd', 20),
(628, 52, 0, '修改组', 'groupedit', 30),
(629, 52, 0, '删除组', 'groupdelete', 40),
(630, 52, 0, '添加', 'add', 50),
(631, 52, 0, '修改', 'edit', 60),
(632, 52, 0, '删除', 'delete', 70),
(633, 52, 0, '启用/禁用', 'status', 80),
(634, 52, 0, '组启用/禁用', 'groupstatus', 35),
(635, 54, 0, '查看', 'list', 10),
(636, 54, 0, '添加', 'add', 20),
(637, 54, 0, '修改', 'modify', 30),
(638, 54, 0, '删除', 'delete', 40),
(639, 54, 0, '审核', 'status', 50),
(640, 54, 0, '排序', 'taxis', 60),
(641, 20, 145, '查看', 'list', 10),
(642, 20, 145, '编辑', 'set', 20),
(643, 20, 145, '添加', 'add', 30),
(644, 20, 145, '修改', 'modify', 40),
(645, 20, 145, '删除', 'delete', 50),
(646, 20, 145, '启用/禁用', 'status', 60),
(647, 55, 0, '查看', 'list', 10),
(648, 55, 0, '更新HTML', 'create', 20),
(649, 20, 146, '查看', 'list', 10),
(650, 20, 146, '编辑', 'set', 20),
(651, 20, 147, '查看', 'list', 10),
(652, 20, 147, '编辑', 'set', 20),
(653, 20, 147, '添加', 'add', 30),
(654, 20, 147, '修改', 'modify', 40),
(655, 20, 147, '删除', 'delete', 50),
(656, 20, 147, '启用/禁用', 'status', 60),
(657, 20, 148, '查看', 'list', 10),
(658, 20, 148, '编辑', 'set', 20),
(659, 20, 148, '添加', 'add', 30),
(660, 20, 148, '修改', 'modify', 40),
(661, 20, 148, '删除', 'delete', 50),
(662, 20, 148, '启用/禁用', 'status', 60),
(663, 20, 149, '查看', 'list', 10),
(664, 20, 149, '编辑', 'set', 20),
(669, 20, 150, '查看', 'list', 10),
(670, 20, 150, '编辑', 'set', 20),
(671, 20, 151, '查看', 'list', 10),
(672, 20, 151, '编辑', 'set', 20),
(673, 20, 151, '添加', 'add', 30),
(674, 20, 151, '修改', 'modify', 40),
(675, 20, 151, '删除', 'delete', 50),
(676, 20, 151, '启用/禁用', 'status', 60),
(677, 20, 152, '查看', 'list', 10),
(678, 20, 152, '编辑', 'set', 20),
(679, 20, 152, '添加', 'add', 30),
(680, 20, 152, '修改', 'modify', 40),
(681, 20, 152, '删除', 'delete', 50),
(682, 20, 152, '启用/禁用', 'status', 60),
(683, 20, 153, '查看', 'list', 10),
(684, 20, 153, '编辑', 'set', 20),
(685, 20, 153, '添加', 'add', 30),
(686, 20, 153, '修改', 'modify', 40),
(687, 20, 153, '删除', 'delete', 50),
(688, 20, 153, '启用/禁用', 'status', 60),
(689, 20, 144, '编辑', 'set', 20);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_project`
--

CREATE TABLE IF NOT EXISTS `qinggan_project` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID，也是应用ID',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '上一级ID',
  `site_id` mediumint(8) unsigned NOT NULL COMMENT '网站ID',
  `module` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '指定模型ID，为0表页面空白',
  `cate` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '绑定根分类ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `nick_title` varchar(255) NOT NULL COMMENT '后台别称',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `tpl_index` varchar(255) NOT NULL COMMENT '封面页',
  `tpl_list` varchar(255) NOT NULL COMMENT '列表页',
  `tpl_content` varchar(255) NOT NULL COMMENT '详细页',
  `ico` varchar(255) NOT NULL COMMENT '图标',
  `orderby` text NOT NULL COMMENT '排序',
  `alias_title` varchar(255) NOT NULL COMMENT '主题别名',
  `alias_note` varchar(255) NOT NULL COMMENT '主题备注',
  `psize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0表示不限制，每页显示数量',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示管理员维护',
  `identifier` varchar(255) NOT NULL COMMENT '标识',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` varchar(255) NOT NULL COMMENT 'SEO描述',
  `subtopics` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否启用子主题功能',
  `is_search` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否支持搜索',
  `is_tag` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '必填Tag',
  `is_biz` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0不启用电商，1启用电商',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '默认货币ID',
  `admin_note` text NOT NULL COMMENT '管理员备注，给编辑人员使用的',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0显示1隐藏',
  `post_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '发布模式，0不启用1启用',
  `comment_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '启用评论功能',
  `post_tpl` varchar(255) NOT NULL COMMENT '发布页模板',
  `etpl_admin` varchar(255) NOT NULL COMMENT '通知管理员邮件模板',
  `etpl_user` varchar(255) NOT NULL COMMENT '发布邮件通知会员模板',
  `etpl_comment_admin` varchar(255) NOT NULL COMMENT '评论邮件通知管理员模板',
  `etpl_comment_user` varchar(255) NOT NULL COMMENT '评论邮件通知会员',
  `is_attr` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1启用主题属性0不启用',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='项目管理器' AUTO_INCREMENT=154 ;

--
-- 转存表中的数据 `qinggan_project`
--

INSERT INTO `qinggan_project` (`id`, `parent_id`, `site_id`, `module`, `cate`, `title`, `nick_title`, `taxis`, `status`, `tpl_index`, `tpl_list`, `tpl_content`, `ico`, `orderby`, `alias_title`, `alias_note`, `psize`, `uid`, `identifier`, `seo_title`, `seo_keywords`, `seo_desc`, `subtopics`, `is_search`, `is_tag`, `is_biz`, `currency_id`, `admin_note`, `hidden`, `post_status`, `comment_status`, `post_tpl`, `etpl_admin`, `etpl_user`, `etpl_comment_admin`, `etpl_comment_user`, `is_attr`) VALUES
(41, 0, 1, 21, 0, '图片播放器', '', 20, 1, '', '', '', 'images/ico/picplayer.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'picture-player', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(42, 0, 1, 23, 0, '导航菜单', '', 30, 1, '', '', '', 'images/ico/menu.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '导航名称', '', 30, 0, 'menu', '', '', '', 1, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 1),
(43, 0, 1, 22, 7, '新闻中心', '', 10, 1, '', '', '', 'images/ico/article.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '新闻主题', '', 10, 0, 'news', '', '', '', 0, 1, 0, 0, 0, '', 0, 0, 1, '', '', '', '', '', 1),
(87, 0, 1, 0, 0, '关于我们', '', 10, 1, '', '', '', 'images/ico/about.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'about', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(45, 0, 1, 24, 70, '产品展示', '', 50, 1, '', '', '', 'images/ico/product.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '产品名称', '', 5, 0, 'product', '', '', '', 0, 1, 0, 1, 1, '', 0, 0, 0, '', '', '', '', '', 0),
(90, 87, 1, 0, 0, '公司简介', '', 10, 1, '', '', '', 'images/ico/company.png', '', '', '', 30, 0, 'about-us', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(146, 87, 1, 0, 0, '发展历程', '', 20, 1, '', '', '', 'images/ico/time.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'development-course', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(92, 87, 1, 0, 0, '联系我们', '', 30, 1, '', '', '', 'images/ico/email.png', '', '', '', 30, 0, 'contact-us', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(93, 87, 1, 0, 0, '工作环境', '', 40, 1, '', '', '', 'images/ico/extension.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'work', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(142, 0, 1, 61, 0, '友情链接', '', 120, 1, '', '', '', 'images/ico/link.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '网站名称', '', 30, 0, 'link', '', '', '', 0, 0, 0, 0, 0, '', 0, 1, 0, 'post_link', 'project_save', '', '', '', 0),
(96, 0, 1, 46, 0, '在线留言', '', 70, 1, '', '', '', 'images/ico/comment.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '留言主题', '', 30, 0, 'book', '', '', '', 0, 0, 0, 0, 0, '', 0, 1, 1, '', 'project_save', '', '', '', 0),
(144, 0, 1, 68, 154, '图集相册', '', 90, 1, '', '', '', 'images/ico/photo.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'photo', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(151, 0, 1, 65, 197, '下载中心', '', 100, 1, '', 'download_list', 'download_content', 'images/ico/cloud.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '附件名称', '', 30, 0, 'download-center', '', '', '', 0, 1, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(147, 0, 1, 23, 0, '页脚导航', '', 35, 1, '', '', '', 'images/ico/menu.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'yejiaodaohang', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(148, 0, 1, 64, 0, '在线客服', '', 130, 1, '', '', '', 'images/ico/qq.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '客服类型', '', 30, 0, 'kefu', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(149, 0, 1, 0, 0, '首页自定义', '', 10, 1, '', '', '', 'images/ico/home.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'index', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(150, 45, 1, 0, 0, '售后保障', '', 10, 1, '', '', '', 'images/ico/paper.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'shouhoukouzhang', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0),
(152, 0, 1, 66, 201, '论坛BBS', '', 110, 1, 'bbs_index', 'bbs_list', 'bbs_detail', 'images/ico/forum.png', 'ext.toplevel DESC,l.replydate DESC,l.dateline DESC,l.id DESC', '讨论主题', '', 30, 0, 'bbs', '', '', '', 0, 0, 0, 0, 0, '', 0, 1, 1, 'bbs_fabu', '', '', '', '', 0),
(153, 0, 1, 67, 0, '注册验证', '', 255, 1, '', '', '', 'images/ico/card.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '验证串', '', 30, 0, 'regcheck', '', '', '', 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_reply`
--

CREATE TABLE IF NOT EXISTS `qinggan_reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父回复ID',
  `vouch` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '推荐评论',
  `star` tinyint(1) NOT NULL DEFAULT '3' COMMENT '星级',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `ip` varchar(255) NOT NULL COMMENT '回复人IP',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未审核1审核',
  `session_id` varchar(255) NOT NULL COMMENT '游客标识',
  `content` text NOT NULL COMMENT '评论内容',
  `admin_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `adm_content` longtext NOT NULL COMMENT '管理员回复内容',
  `adm_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复时间',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='主题评论表' AUTO_INCREMENT=21 ;

--
-- 转存表中的数据 `qinggan_reply`
--

INSERT INTO `qinggan_reply` (`id`, `tid`, `parent_id`, `vouch`, `star`, `uid`, `ip`, `addtime`, `status`, `session_id`, `content`, `admin_id`, `adm_content`, `adm_time`) VALUES
(2, 1269, 0, 0, 3, 1, '127.0.0.1', 1393852980, 1, 'ktpts7ud8etbmmb3k7k6lpm2s6', '测试下！', 0, '', 0),
(3, 1269, 0, 0, 3, 1, '127.0.0.1', 1393852985, 1, 'ktpts7ud8etbmmb3k7k6lpm2s6', '测试下！3333', 0, '', 0),
(4, 1269, 0, 0, 0, 3, '127.0.0.1', 1394006030, 1, 'm9j80sjh79uvr9f31g51jev222', 'dfasfadsf', 0, '', 0),
(5, 1269, 0, 0, 0, 3, '127.0.0.1', 1394006271, 1, 'm9j80sjh79uvr9f31g51jev222', '安全字符测试！', 0, '', 0),
(6, 1269, 0, 0, 0, 3, '127.0.0.1', 1394006320, 1, 'm9j80sjh79uvr9f31g51jev222', '再来测试一次', 0, '', 0),
(7, 1269, 0, 0, 0, 3, '127.0.0.1', 1394006366, 1, 'm9j80sjh79uvr9f31g51jev222', '测试一下！', 0, '', 0),
(8, 1269, 0, 0, 0, 3, '127.0.0.1', 1394006553, 1, 'm9j80sjh79uvr9f31g51jev222', '测试吧！', 0, '', 0),
(9, 758, 0, 0, 0, 3, '127.0.0.1', 1399239499, 1, '49uk0vnlntj3pqouklj5ecrq56', '测试评论', 0, '', 0),
(10, 1253, 0, 0, 0, 3, '127.0.0.1', 1404983726, 1, 'hdh2mfshg5372i1ub8hi5sm9d4', '测试一下评论！', 0, '', 0),
(11, 1253, 0, 0, 0, 3, '127.0.0.1', 1404983732, 1, 'hdh2mfshg5372i1ub8hi5sm9d4', '再测试下！', 0, '', 0),
(12, 1269, 0, 0, 0, 3, '127.0.0.1', 1410328879, 1, 'g5rlmqnslocur0s94s9t9lcuv5', '测试的噢！', 0, '', 0),
(13, 1269, 0, 0, 0, 3, '127.0.0.1', 1410437447, 1, 'pp3svp4ttrubatomtgarohvsa2', '测试评论~~~', 0, '', 0),
(14, 1269, 0, 0, 0, 3, '127.0.0.1', 1410437460, 1, 'pp3svp4ttrubatomtgarohvsa2', '8193额3', 0, '', 0),
(19, 1348, 0, 0, 0, 3, '127.0.0.1', 1414121370, 1, 'e6imcpgvei5tq0cmm8p7f0fs45', '测试评论！', 0, '', 0),
(20, 1348, 0, 0, 0, 3, '127.0.0.1', 1414121403, 1, 'e6imcpgvei5tq0cmm8p7f0fs45', '测噢！', 0, '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res`
--

CREATE TABLE IF NOT EXISTS `qinggan_res` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资源ID',
  `cate_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `folder` varchar(255) NOT NULL COMMENT '存储目录',
  `name` varchar(255) NOT NULL COMMENT '资源文件名',
  `ext` varchar(30) NOT NULL COMMENT '资源后缀，如jpg等',
  `filename` varchar(255) NOT NULL COMMENT '文件名带路径',
  `ico` varchar(255) NOT NULL COMMENT 'ICO图标文件',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `title` varchar(255) NOT NULL COMMENT '内容',
  `attr` text NOT NULL COMMENT '附件属性',
  `note` text NOT NULL COMMENT '备注',
  `session_id` varchar(100) NOT NULL COMMENT '操作者 ID，即会员ID用于检测是否有权限删除 ',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID，当该ID为时检则sesson_id，如不相同则不能删除 ',
  `download` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下载次数',
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源ID' AUTO_INCREMENT=711 ;

--
-- 转存表中的数据 `qinggan_res`
--

INSERT INTO `qinggan_res` (`id`, `cate_id`, `folder`, `name`, `ext`, `filename`, `ico`, `addtime`, `title`, `attr`, `note`, `session_id`, `user_id`, `download`) VALUES
(352, 1, 'res/201310/08/', '3820bb76e7d78cbb.jpg', 'jpg', 'res/201310/08/3820bb76e7d78cbb.jpg', 'res/201310/08/_352.jpg', 1381180667, 'about', 'a:2:{s:5:"width";i:300;s:6:"height";i:225;}', '', 'n8fnfs29la334mckhrdvk2p2d4', 0, 0),
(543, 1, 'res/201403/05/', '47a8027562da1fac.jpg', 'jpg', 'res/201403/05/47a8027562da1fac.jpg', 'res/201403/05/_543.jpg', 1394008401, '980X180-01', 'a:2:{s:5:"width";i:980;s:6:"height";i:180;}', '', 's1etv68a2usgarbi8fdaf5jqo1', 0, 0),
(544, 1, 'res/201403/05/', '9efb2e3ea01c9570.jpg', 'jpg', 'res/201403/05/9efb2e3ea01c9570.jpg', 'res/201403/05/_544.jpg', 1394008416, '980X180-02', 'a:2:{s:5:"width";i:980;s:6:"height";i:180;}', '', 's1etv68a2usgarbi8fdaf5jqo1', 0, 0),
(545, 1, 'res/201403/05/', 'e8c5c2a7f7e2c455.jpg', 'jpg', 'res/201403/05/e8c5c2a7f7e2c455.jpg', 'res/201403/05/_545.jpg', 1394008439, '980X180-03', 'a:2:{s:5:"width";i:980;s:6:"height";i:180;}', '', 's1etv68a2usgarbi8fdaf5jqo1', 0, 0),
(624, 1, 'res/201409/01/', '27a6e141c3d265ae.jpg', 'jpg', 'res/201409/01/27a6e141c3d265ae.jpg', 'res/201409/01/_624.jpg', 1409550321, 'logo', 'a:2:{s:5:"width";i:219;s:6:"height";i:57;}', '', '', 0, 0),
(625, 1, 'res/201409/02/', 'e05cc1135fa86f92.jpg', 'jpg', 'res/201409/02/e05cc1135fa86f92.jpg', 'res/201409/02/_625.jpg', 1409659399, 'about_banner', 'a:2:{s:5:"width";i:980;s:6:"height";i:200;}', '', '', 0, 0),
(626, 1, 'res/201409/02/', 'f592e6035fbe3eb4.jpg', 'jpg', 'res/201409/02/f592e6035fbe3eb4.jpg', 'res/201409/02/_626.jpg', 1409662451, 'banner1', 'a:2:{s:5:"width";i:980;s:6:"height";i:350;}', '', '', 0, 0),
(627, 1, 'res/201409/02/', 'd000b846737b3951.jpg', 'jpg', 'res/201409/02/d000b846737b3951.jpg', 'res/201409/02/_627.jpg', 1409662493, 'banner2', 'a:2:{s:5:"width";i:980;s:6:"height";i:350;}', '', '', 0, 0),
(628, 1, 'res/201409/02/', '350ec25c2f455445.jpg', 'jpg', 'res/201409/02/350ec25c2f455445.jpg', 'res/201409/02/_628.jpg', 1409662514, 'banner3', 'a:2:{s:5:"width";i:980;s:6:"height";i:350;}', '', '', 0, 0),
(629, 1, 'res/201409/03/', 'e8b2a2815497215c.png', 'png', 'res/201409/03/e8b2a2815497215c.png', 'res/201409/03/_629.png', 1409747220, 'bbs', 'a:2:{s:5:"width";i:280;s:6:"height";i:280;}', '', '', 0, 0),
(630, 1, 'res/201409/03/', '5b0086d14de1bbf2.jpg', 'jpg', 'res/201409/03/5b0086d14de1bbf2.jpg', 'res/201409/03/_630.jpg', 1409749616, 'about-img', 'a:2:{s:5:"width";i:129;s:6:"height";i:133;}', '', '', 0, 0),
(631, 1, 'res/201409/11/', '8179d9fbe71f5cf1.jpg', 'jpg', 'res/201409/11/8179d9fbe71f5cf1.jpg', 'res/201409/11/_631.jpg', 1410443658, '01', 'a:2:{s:5:"width";i:573;s:6:"height";i:631;}', '', '', 0, 0),
(632, 1, 'res/201409/11/', '9f22f356aced771f.jpg', 'jpg', 'res/201409/11/9f22f356aced771f.jpg', 'res/201409/11/_632.jpg', 1410443658, '02', 'a:2:{s:5:"width";i:516;s:6:"height";i:533;}', '', '', 0, 0),
(633, 1, 'res/201409/11/', '3a2d20c51a30b4b3.jpg', 'jpg', 'res/201409/11/3a2d20c51a30b4b3.jpg', 'res/201409/11/_633.jpg', 1410443659, '03', 'a:2:{s:5:"width";i:596;s:6:"height";i:664;}', '', '', 0, 0),
(634, 1, 'res/201409/11/', '3c34fc73cc0ea535.jpg', 'jpg', 'res/201409/11/3c34fc73cc0ea535.jpg', 'res/201409/11/_634.jpg', 1410443659, '04', 'a:2:{s:5:"width";i:641;s:6:"height";i:648;}', '', '', 0, 0),
(635, 1, 'res/201409/11/', 'e77fa09c0a487b0f.jpg', 'jpg', 'res/201409/11/e77fa09c0a487b0f.jpg', 'res/201409/11/_635.jpg', 1410443978, '01', 'a:2:{s:5:"width";i:490;s:6:"height";i:490;}', '', '', 0, 0),
(636, 1, 'res/201409/11/', '785bf4c3d697cdce.jpg', 'jpg', 'res/201409/11/785bf4c3d697cdce.jpg', 'res/201409/11/_636.jpg', 1410443978, '02', 'a:2:{s:5:"width";i:440;s:6:"height";i:440;}', '', '', 0, 0),
(685, 1, 'res/201410/13/', 'd3d47ae3f1bb1e96.jpg', 'jpg', 'res/201410/13/d3d47ae3f1bb1e96.jpg', 'res/201410/13/_685.jpg', 1413170208, '悬崖上的环卫工02', 'a:2:{s:5:"width";i:769;s:6:"height";i:493;}', '1999年，中国启动了首个国庆长假。每年，各大景区迎来旅游人潮，也迎来了“垃圾大潮”。彭文才就是在其后一年上岗，成为一名“高山环卫工”。峨眉金顶海拔3000多米，他每年下崖80多次，一干就是14年。每年国庆 ，彭文才和金顶区域的其他21位同事都在满负荷工作。长假时，峨眉山的垃圾会被统一收集起来，长假过后再运下山处理，每天四车垃圾，需要一周时间才能运完。需要谈起愿望，彭文才说：“就是希望以后不需要再下崖了。”', '', 0, 0),
(683, 1, 'res/201410/13/', '00bc5d4674b7a14c.jpg', 'jpg', 'res/201410/13/00bc5d4674b7a14c.jpg', 'res/201410/13/_683.jpg', 1413169947, '毕首金老师的体育教具', 'a:2:{s:5:"width";i:760;s:6:"height";i:455;}', '云南省昆明市白汉场中心小学毕首金老师耗费30年，用课余时间手工制作8000余件，106种体育教具。高跷、大板鞋、踢踢球、橄榄球、保龄球、大弹弓都由废旧材料制作而成。', '', 0, 0),
(684, 1, 'res/201410/13/', '333d91b566a24693.jpg', 'jpg', 'res/201410/13/333d91b566a24693.jpg', 'res/201410/13/_684.jpg', 1413170104, '悬崖上的环卫工01', 'a:2:{s:5:"width";i:760;s:6:"height";i:509;}', '国庆长假，攀悬崖捡垃圾的“蜘蛛侠”又被“看见”了一次。人们感叹清洁工不易的同时，不得不面对一个现实——蜘蛛侠之所以被塑造，是因为人间处处有不公；清洁工之所以攀悬崖，是因为景区处处有垃圾。', '', 0, 0),
(686, 1, 'res/201410/13/', '02fb392d19e61f1d.jpg', 'jpg', 'res/201410/13/02fb392d19e61f1d.jpg', 'res/201410/13/_686.jpg', 1413170285, '悬崖上的环卫工03', 'a:2:{s:5:"width";i:760;s:6:"height";i:742;}', '“飞檐走壁”的环卫工通常并非年轻人。48岁的康仲军在泰山环卫工里算是“年轻人”了。“太年轻的人压根不愿意干清洁工，年纪大的人干起来太危险，只能我来了。”踩在悬崖边捡垃圾是康仲军的日常工作。康仲军说，工作时被游客的垃圾砸到是常事，曾经还有游客差点把燃着的烟头扔到他脖子上。节假日里，泰山每天产生8吨垃圾，一个环卫工人每天要捡拾游客随意丢弃的垃圾10大袋。', '', 0, 0),
(687, 1, 'res/201410/13/', 'fffb0a13f8abd14a.jpg', 'jpg', 'res/201410/13/fffb0a13f8abd14a.jpg', 'res/201410/13/_687.jpg', 1413170984, '退休矿工自家宅院掘地六米挖出地下居室', 'a:2:{s:5:"width";i:760;s:6:"height";i:507;}', '郑州市退休矿工陈新年，在自家宅院掘地六米挖出50平米地下居室。陈新年称他的设计能抵抗8级地震，供人居住没有任何问题。陈新年称他挖地下居室的原因很简单——原来的房间太拥挤，商品房又太贵，就利用自己当矿工时的技术给家里挖一间房。', '', 0, 1),
(700, 1, 'res/201411/06/', 'a50b479341925654', 'jpg', 'res/201411/06/a50b479341925654.jpg', 'res/201411/06/_700.jpg', 1415255292, 'logo200', 'a:2:{s:5:"width";i:200;s:6:"height";i:200;}', '', '3ua49d1mc854trcn2b205tbhf1', 3, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res_cate`
--

CREATE TABLE IF NOT EXISTS `qinggan_res_cate` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '资源分类ID',
  `title` varchar(255) NOT NULL COMMENT '分类名称',
  `root` varchar(255) NOT NULL DEFAULT '/' COMMENT '存储目录',
  `folder` varchar(255) NOT NULL DEFAULT 'Ym/d/' COMMENT '存储目录格式',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1默认0非默认',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源分类存储' AUTO_INCREMENT=9 ;

--
-- 转存表中的数据 `qinggan_res_cate`
--

INSERT INTO `qinggan_res_cate` (`id`, `title`, `root`, `folder`, `is_default`) VALUES
(1, '默认分类', 'res/', 'Ym/d/', 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res_ext`
--

CREATE TABLE IF NOT EXISTS `qinggan_res_ext` (
  `res_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `gd_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'GD库方案ID',
  `x1` mediumint(9) NOT NULL DEFAULT '0' COMMENT '手工裁剪定位x1',
  `y1` mediumint(9) NOT NULL DEFAULT '0' COMMENT '手工裁剪定位y1',
  `x2` mediumint(9) NOT NULL DEFAULT '0' COMMENT '手工裁剪定位x2',
  `y2` mediumint(9) NOT NULL DEFAULT '0' COMMENT '手工裁剪定位y2',
  `w` mediumint(9) NOT NULL DEFAULT '0' COMMENT '参数下的宽',
  `h` mediumint(9) NOT NULL DEFAULT '0' COMMENT '参数下的高',
  `filename` varchar(255) NOT NULL COMMENT '文件地址（含路径）',
  `filetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后',
  PRIMARY KEY (`res_id`,`gd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='生成扩展图片';

--
-- 转存表中的数据 `qinggan_res_ext`
--

INSERT INTO `qinggan_res_ext` (`res_id`, `gd_id`, `x1`, `y1`, `x2`, `y2`, `w`, `h`, `filename`, `filetime`) VALUES
(352, 21, 0, 0, 0, 0, 0, 0, 'res/201310/08/mobile_352.jpg', 1413389085),
(543, 21, 0, 0, 0, 0, 0, 0, 'res/201403/05/mobile_543.jpg', 1413389083),
(544, 21, 0, 0, 0, 0, 0, 0, 'res/201403/05/mobile_544.jpg', 1413389082),
(545, 21, 0, 0, 0, 0, 0, 0, 'res/201403/05/mobile_545.jpg', 1413389081),
(624, 21, 0, 0, 0, 0, 0, 0, 'res/201409/01/mobile_624.jpg', 1413389080),
(625, 21, 0, 0, 0, 0, 0, 0, 'res/201409/02/mobile_625.jpg', 1413389078),
(626, 21, 0, 0, 0, 0, 0, 0, 'res/201409/02/mobile_626.jpg', 1413389077),
(627, 21, 0, 0, 0, 0, 0, 0, 'res/201409/02/mobile_627.jpg', 1413389076),
(628, 21, 0, 0, 0, 0, 0, 0, 'res/201409/02/mobile_628.jpg', 1413389074),
(629, 21, 0, 0, 0, 0, 0, 0, 'res/201409/03/mobile_629.png', 1413389073),
(630, 21, 0, 0, 0, 0, 0, 0, 'res/201409/03/mobile_630.jpg', 1413389072),
(631, 21, 0, 0, 0, 0, 0, 0, 'res/201409/11/mobile_631.jpg', 1413389071),
(632, 21, 0, 0, 0, 0, 0, 0, 'res/201409/11/mobile_632.jpg', 1413389069),
(633, 21, 0, 0, 0, 0, 0, 0, 'res/201409/11/mobile_633.jpg', 1413389068),
(634, 21, 0, 0, 0, 0, 0, 0, 'res/201409/11/mobile_634.jpg', 1413389067),
(635, 21, 0, 0, 0, 0, 0, 0, 'res/201409/11/mobile_635.jpg', 1413389065),
(636, 21, 0, 0, 0, 0, 0, 0, 'res/201409/11/mobile_636.jpg', 1413389064),
(636, 12, 0, 0, 0, 0, 0, 0, 'res/201409/11/auto_636.jpg', 1413389064),
(687, 21, 0, 0, 0, 0, 0, 0, 'res/201410/13/mobile_687.jpg', 1413389057),
(687, 12, 0, 0, 0, 0, 0, 0, 'res/201410/13/auto_687.jpg', 1413389057),
(687, 2, 0, 0, 0, 0, 0, 0, 'res/201410/13/thumb_687.jpg', 1413389057),
(683, 21, 0, 0, 0, 0, 0, 0, 'res/201410/13/mobile_683.jpg', 1413389063),
(683, 12, 0, 0, 0, 0, 0, 0, 'res/201410/13/auto_683.jpg', 1413389063),
(684, 21, 0, 0, 0, 0, 0, 0, 'res/201410/13/mobile_684.jpg', 1413389061),
(684, 12, 0, 0, 0, 0, 0, 0, 'res/201410/13/auto_684.jpg', 1413389061),
(685, 21, 0, 0, 0, 0, 0, 0, 'res/201410/13/mobile_685.jpg', 1413389060),
(685, 12, 0, 0, 0, 0, 0, 0, 'res/201410/13/auto_685.jpg', 1413389060),
(686, 21, 0, 0, 0, 0, 0, 0, 'res/201410/13/mobile_686.jpg', 1413389058),
(628, 12, 0, 0, 0, 0, 0, 0, 'res/201409/02/auto_628.jpg', 1413389074),
(628, 2, 0, 0, 0, 0, 0, 0, 'res/201409/02/thumb_628.jpg', 1413389074),
(627, 12, 0, 0, 0, 0, 0, 0, 'res/201409/02/auto_627.jpg', 1413389076),
(627, 2, 0, 0, 0, 0, 0, 0, 'res/201409/02/thumb_627.jpg', 1413389076),
(626, 12, 0, 0, 0, 0, 0, 0, 'res/201409/02/auto_626.jpg', 1413389077),
(626, 2, 0, 0, 0, 0, 0, 0, 'res/201409/02/thumb_626.jpg', 1413389077),
(686, 12, 0, 0, 0, 0, 0, 0, 'res/201410/13/auto_686.jpg', 1413389058),
(686, 2, 0, 0, 0, 0, 0, 0, 'res/201410/13/thumb_686.jpg', 1413389058),
(685, 2, 0, 0, 0, 0, 0, 0, 'res/201410/13/thumb_685.jpg', 1413389060),
(684, 2, 0, 0, 0, 0, 0, 0, 'res/201410/13/thumb_684.jpg', 1413389061),
(683, 2, 0, 0, 0, 0, 0, 0, 'res/201410/13/thumb_683.jpg', 1413389063),
(636, 2, 0, 0, 0, 0, 0, 0, 'res/201409/11/thumb_636.jpg', 1413389064),
(635, 12, 0, 0, 0, 0, 0, 0, 'res/201409/11/auto_635.jpg', 1413389065),
(635, 2, 0, 0, 0, 0, 0, 0, 'res/201409/11/thumb_635.jpg', 1413389065),
(634, 12, 0, 0, 0, 0, 0, 0, 'res/201409/11/auto_634.jpg', 1413389067),
(634, 2, 0, 0, 0, 0, 0, 0, 'res/201409/11/thumb_634.jpg', 1413389067),
(633, 12, 0, 0, 0, 0, 0, 0, 'res/201409/11/auto_633.jpg', 1413389068),
(633, 2, 0, 0, 0, 0, 0, 0, 'res/201409/11/thumb_633.jpg', 1413389068),
(632, 12, 0, 0, 0, 0, 0, 0, 'res/201409/11/auto_632.jpg', 1413389069),
(632, 2, 0, 0, 0, 0, 0, 0, 'res/201409/11/thumb_632.jpg', 1413389069),
(631, 12, 0, 0, 0, 0, 0, 0, 'res/201409/11/auto_631.jpg', 1413389071),
(631, 2, 0, 0, 0, 0, 0, 0, 'res/201409/11/thumb_631.jpg', 1413389071),
(630, 12, 0, 0, 0, 0, 0, 0, 'res/201409/03/auto_630.jpg', 1413389072),
(630, 2, 0, 0, 0, 0, 0, 0, 'res/201409/03/thumb_630.jpg', 1413389072),
(629, 12, 0, 0, 0, 0, 0, 0, 'res/201409/03/auto_629.png', 1413389073),
(629, 2, 0, 0, 0, 0, 0, 0, 'res/201409/03/thumb_629.png', 1413389073),
(625, 12, 0, 0, 0, 0, 0, 0, 'res/201409/02/auto_625.jpg', 1413389078),
(625, 2, 0, 0, 0, 0, 0, 0, 'res/201409/02/thumb_625.jpg', 1413389078),
(624, 12, 0, 0, 0, 0, 0, 0, 'res/201409/01/auto_624.jpg', 1413389080),
(624, 2, 0, 0, 0, 0, 0, 0, 'res/201409/01/thumb_624.jpg', 1413389080),
(545, 12, 0, 0, 0, 0, 0, 0, 'res/201403/05/auto_545.jpg', 1413389081),
(545, 2, 0, 0, 0, 0, 0, 0, 'res/201403/05/thumb_545.jpg', 1413389081),
(544, 12, 0, 0, 0, 0, 0, 0, 'res/201403/05/auto_544.jpg', 1413389082),
(544, 2, 0, 0, 0, 0, 0, 0, 'res/201403/05/thumb_544.jpg', 1413389082),
(543, 12, 0, 0, 0, 0, 0, 0, 'res/201403/05/auto_543.jpg', 1413389083),
(352, 12, 0, 0, 0, 0, 0, 0, 'res/201310/08/auto_352.jpg', 1413389085),
(543, 2, 0, 0, 0, 0, 0, 0, 'res/201403/05/thumb_543.jpg', 1413389083),
(352, 2, 0, 0, 0, 0, 0, 0, 'res/201310/08/thumb_352.jpg', 1413389085),
(700, 21, 0, 0, 0, 0, 0, 0, 'res/201411/06/mobile_700.jpg', 1415255292),
(700, 12, 0, 0, 0, 0, 0, 0, 'res/201411/06/auto_700.jpg', 1415255292),
(700, 2, 0, 0, 0, 0, 0, 0, 'res/201411/06/thumb_700.jpg', 1415255292);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_session`
--

CREATE TABLE IF NOT EXISTS `qinggan_session` (
  `id` varchar(32) NOT NULL COMMENT 'session_id',
  `data` text NOT NULL COMMENT 'session 内容',
  `lasttime` int(10) unsigned NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='SESSION操作';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_site`
--

CREATE TABLE IF NOT EXISTS `qinggan_site` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `domain_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '默认域名ID',
  `title` varchar(255) NOT NULL COMMENT '网站名称',
  `dir` varchar(255) NOT NULL DEFAULT '/' COMMENT '安装目录，以/结尾',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `content` text NOT NULL COMMENT '网站关闭原因',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1默认站点',
  `tpl_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '模板ID',
  `url_type` enum('default','rewrite','html') NOT NULL DEFAULT 'default' COMMENT '默认，即带?等能数，rewrite是伪静态页，html为生成的静态页',
  `logo` varchar(255) NOT NULL COMMENT '网站 LOGO ',
  `meta` text NOT NULL COMMENT '扩展配置',
  `currency_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '默认货币ID',
  `register_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0关闭注册1开启注册',
  `register_close` varchar(255) NOT NULL COMMENT '关闭注册说明',
  `login_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0关闭登录1开启',
  `login_close` varchar(255) NOT NULL COMMENT '关闭登录说明',
  `adm_logo29` varchar(255) NOT NULL COMMENT '在后台左侧LOGO地址',
  `adm_logo180` varchar(255) NOT NULL COMMENT '登录LOGO地址',
  `lang` varchar(255) NOT NULL COMMENT '语言包',
  `api` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0不走接口',
  `email_charset` enum('gbk','utf-8') NOT NULL DEFAULT 'utf-8' COMMENT '邮箱模式',
  `email_server` varchar(100) NOT NULL COMMENT '邮件服务器',
  `email_port` varchar(10) NOT NULL COMMENT '端口',
  `email_ssl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'SSL模式',
  `email_account` varchar(100) NOT NULL COMMENT '邮箱账号',
  `email_pass` varchar(100) NOT NULL COMMENT '邮箱密码',
  `email_name` varchar(100) NOT NULL COMMENT '发件人名称',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO主题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` text NOT NULL COMMENT 'SEO摘要',
  `biz_sn` varchar(255) NOT NULL COMMENT '订单号生成规则',
  `biz_payment` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '默认支付方式',
  `biz_billing` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0未绑定',
  `upload_guest` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '游客上传权限',
  `upload_user` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '会员上传权限',
  `html_root_dir` varchar(255) NOT NULL DEFAULT 'html/' COMMENT 'HTML根目录',
  `html_content_type` varchar(255) NOT NULL DEFAULT 'empty' COMMENT 'HTML生成规则',
  `biz_etpl` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='网站管理' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `qinggan_site`
--

INSERT INTO `qinggan_site` (`id`, `domain_id`, `title`, `dir`, `status`, `content`, `is_default`, `tpl_id`, `url_type`, `logo`, `meta`, `currency_id`, `register_status`, `register_close`, `login_status`, `login_close`, `adm_logo29`, `adm_logo180`, `lang`, `api`, `email_charset`, `email_server`, `email_port`, `email_ssl`, `email_account`, `email_pass`, `email_name`, `email`, `seo_title`, `seo_keywords`, `seo_desc`, `biz_sn`, `biz_payment`, `biz_billing`, `upload_guest`, `upload_user`, `html_root_dir`, `html_content_type`, `biz_etpl`) VALUES
(1, 1, 'PHPOK企业网站', '/phpok/', 1, '网站正在建设中！', 1, 1, 'default', 'res/201409/01/27a6e141c3d265ae.jpg', '', 1, 1, '本系统暂停新会员注册，给您带来不便还请见谅，如需会员服务请联系QQ：40782502', 1, '本系统暂停会员登录，给您带来不便还请见谅！', '', '', '', 0, 'utf-8', 'smtp.qq.com', '25', 0, 'admin@phpok.com', '', '网站管理员', 'admin@phpok.com', '网站建设|企业网站建设|PHPOK网站建设|PHPOK企业网站建设', '网站建设,企业网站建设,PHPOK网站建设,PHPOK企业网站建设', '高效的企业网站建设系统，可实现高定制化的企业网站电商系统，实现企业网站到电子商务企业网站。定制功能更高，操作更简单！', 'prefix[P]-year-month-date-number', 0, 1, 0, 1, 'html/', 'Ym/', 'order_admin');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_site_domain`
--

CREATE TABLE IF NOT EXISTS `qinggan_site_domain` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` mediumint(8) unsigned NOT NULL COMMENT '网站ID',
  `domain` varchar(255) NOT NULL COMMENT '域名信息',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='网站指定的域名' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `qinggan_site_domain`
--

INSERT INTO `qinggan_site_domain` (`id`, `site_id`, `domain`) VALUES
(1, 1, 'localhost');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_stock`
--

CREATE TABLE IF NOT EXISTS `qinggan_stock` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '仓库ID，自增ID',
  `title` varchar(255) NOT NULL COMMENT '仓库名称',
  `address` varchar(255) NOT NULL COMMENT '仓库地址',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品仓库' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_sysmenu`
--

CREATE TABLE IF NOT EXISTS `qinggan_sysmenu` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID，0为根菜单',
  `title` varchar(100) NOT NULL COMMENT '分类名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态0禁用1正常',
  `appfile` varchar(100) NOT NULL COMMENT '应用文件名，放在phpok/admin/目录下，记录不带.php',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠，可选0-255',
  `func` varchar(100) NOT NULL COMMENT '应用函数，为空使用index',
  `identifier` varchar(100) NOT NULL COMMENT '标识串，用于区分同一应用文件的不同内容',
  `ext` varchar(255) NOT NULL COMMENT '表单扩展',
  `if_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0常规项目，1系统项目',
  `site_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '0表示全局网站',
  `icon` varchar(255) NOT NULL COMMENT '图标路径',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='PHPOK后台系统菜单' AUTO_INCREMENT=57 ;

--
-- 转存表中的数据 `qinggan_sysmenu`
--

INSERT INTO `qinggan_sysmenu` (`id`, `parent_id`, `title`, `status`, `appfile`, `taxis`, `func`, `identifier`, `ext`, `if_system`, `site_id`, `icon`) VALUES
(1, 0, '设置', 1, 'setting', 50, '', '', '', 1, 0, ''),
(3, 0, '会员', 1, 'user', 30, '', '', '', 0, 0, ''),
(5, 0, '内容', 1, 'index', 10, '', '', '', 0, 0, ''),
(6, 1, '表单选项', 1, 'opt', 54, '', '', '', 0, 0, ''),
(7, 4, '字段维护', 1, 'fields', 55, '', '', '', 0, 0, ''),
(8, 1, '模块管理', 1, 'module', 53, '', '', '', 0, 0, ''),
(9, 1, '核心配置', 1, 'system', 57, '', '', '', 1, 0, ''),
(13, 3, '会员列表', 1, 'user', 31, '', '', '', 0, 0, ''),
(14, 3, '会员组', 1, 'usergroup', 33, '', '', '', 0, 0, 'users'),
(25, 3, '会员字段', 1, 'user', 255, 'fields', '', '', 0, 0, ''),
(16, 4, '插件', 1, 'plugin', 70, '', '', '', 0, 0, 'powercord'),
(18, 5, '分类管理', 1, 'cate', 14, '', '', '', 0, 0, 'stack'),
(19, 5, '全局内容', 1, 'all', 11, '', '', '', 0, 0, ''),
(20, 5, '内容管理', 1, 'list', 12, '', '', '', 0, 0, 'book'),
(22, 5, '资源管理', 1, 'res', 18, '', '', '', 0, 0, 'download'),
(23, 5, '数据调用', 1, 'call', 16, '', '', '', 0, 0, 'rocket'),
(27, 1, '项目管理', 1, 'project', 51, '', '', '', 0, 0, 'finder'),
(28, 1, '邮件通知模板', 1, 'email', 56, '', '', '', 0, 0, 'envelope'),
(29, 1, '管理员维护', 1, 'admin', 100, '', '', '', 0, 0, 'windows8'),
(30, 1, '风格管理', 1, 'tpl', 60, '', '', '', 0, 0, 'leaf'),
(31, 1, '站点管理', 1, 'site', 110, '', '', '', 0, 0, ''),
(32, 5, '评论管理', 1, 'reply', 17, '', '', '', 0, 1, 'bubbles'),
(33, 2, '货币及汇率', 1, 'currency', 80, '', '', '', 0, 1, ''),
(34, 2, '订单管理', 1, 'order', 15, '', '', '', 0, 1, ''),
(4, 0, '工具', 1, 'tool', 40, '', '', '', 0, 0, ''),
(45, 4, '程序升级', 1, 'update', 30, '', '', '', 0, 1, 'earth'),
(2, 0, '订单', 1, 'order', 20, '', '', '', 0, 0, ''),
(52, 2, '付款方案', 1, 'payment', 20, '', '', '', 0, 1, ''),
(55, 1, '生成静态页', 0, 'html', 150, '', '', '', 0, 1, 'screen');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tag`
--

CREATE TABLE IF NOT EXISTS `qinggan_tag` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `url` varchar(255) NOT NULL COMMENT '关键字网址',
  `urlid` varchar(255) NOT NULL COMMENT '网址串，可以是拼音，也可以是英文单词',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用次数',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不启用1启用',
  `taxis` int(11) NOT NULL DEFAULT '0' COMMENT '值越大越靠前',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='关键字管理器' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_temp`
--

CREATE TABLE IF NOT EXISTS `qinggan_temp` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tbl` varchar(100) NOT NULL COMMENT '表',
  `admin_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `content` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tbl` (`tbl`,`admin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='临时表单存储器' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tpl`
--

CREATE TABLE IF NOT EXISTS `qinggan_tpl` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(100) NOT NULL COMMENT '模板名称',
  `author` varchar(100) NOT NULL COMMENT '开发者名称',
  `folder` varchar(100) NOT NULL DEFAULT 'www' COMMENT '模板目录',
  `refresh_auto` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1自动判断更新刷新0不刷新',
  `refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1强制刷新0普通刷新',
  `ext` varchar(20) NOT NULL DEFAULT 'html' COMMENT '后缀',
  `folder_change` varchar(255) NOT NULL COMMENT '更改目录',
  `phpfolder` varchar(200) NOT NULL COMMENT 'PHP执行文件目录',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模板管理' AUTO_INCREMENT=17 ;

--
-- 转存表中的数据 `qinggan_tpl`
--

INSERT INTO `qinggan_tpl` (`id`, `title`, `author`, `folder`, `refresh_auto`, `refresh`, `ext`, `folder_change`, `phpfolder`) VALUES
(1, '默认风格', 'phpok.com', 'www', 1, 0, 'html', 'css,images,js', 'phpinc');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user`
--

CREATE TABLE IF NOT EXISTS `qinggan_user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID，即会员ID',
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '主要会员组',
  `user` varchar(100) NOT NULL COMMENT '会员账号',
  `pass` varchar(100) NOT NULL COMMENT '会员密码',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态ID，0未审核1正常2锁定',
  `regtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `email` varchar(200) NOT NULL COMMENT '邮箱，可用于取回密码',
  `mobile` varchar(50) NOT NULL COMMENT '手机或电话',
  `code` varchar(255) NOT NULL COMMENT '验证串，可用于取回密码',
  `avatar` varchar(255) NOT NULL COMMENT '会员头像',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员管理' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_ext`
--

CREATE TABLE IF NOT EXISTS `qinggan_user_ext` (
  `id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `fullname` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `gender` varchar(255) NOT NULL DEFAULT '' COMMENT '性别',
  `content` longtext NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员扩展字段';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_fields`
--

CREATE TABLE IF NOT EXISTS `qinggan_user_fields` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '字段ID，自增',
  `title` varchar(255) NOT NULL COMMENT '字段名称',
  `identifier` varchar(50) NOT NULL COMMENT '字段标识串',
  `field_type` varchar(255) NOT NULL DEFAULT '200' COMMENT '字段存储类型',
  `note` varchar(255) NOT NULL COMMENT '字段内容备注',
  `form_type` varchar(100) NOT NULL COMMENT '表单类型',
  `form_style` varchar(255) NOT NULL COMMENT '表单CSS',
  `format` varchar(100) NOT NULL COMMENT '格式化方式',
  `content` varchar(255) NOT NULL COMMENT '默认值',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `ext` text NOT NULL COMMENT '扩展内容',
  `is_edit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不可编辑1可编辑',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=24 ;

--
-- 转存表中的数据 `qinggan_user_fields`
--

INSERT INTO `qinggan_user_fields` (`id`, `title`, `identifier`, `field_type`, `note`, `form_type`, `form_style`, `format`, `content`, `taxis`, `ext`, `is_edit`) VALUES
(21, '姓名', 'fullname', 'varchar', '', 'text', '', 'safe', '', 10, 'a:2:{s:8:"form_btn";b:0;s:5:"width";s:3:"300";}', 1),
(22, '性别', 'gender', 'varchar', '', 'radio', '', 'safe', '0', 120, 'a:3:{s:11:"option_list";s:5:"opt:1";s:9:"put_order";s:1:"0";s:10:"ext_select";b:0;}', 1),
(23, '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:"width";s:3:"800";s:6:"height";s:3:"360";s:7:"is_code";b:0;s:9:"btn_image";i:1;s:9:"btn_video";i:1;s:8:"btn_file";i:1;s:8:"btn_page";b:0;s:8:"btn_info";b:0;s:7:"is_read";b:0;s:5:"etype";s:4:"full";s:7:"btn_tpl";b:0;s:7:"btn_map";b:0;}', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_group`
--

CREATE TABLE IF NOT EXISTS `qinggan_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会员组ID',
  `title` varchar(255) NOT NULL COMMENT '会员组名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0不使用1使用',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1为会员注册默认组',
  `is_guest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '游客组',
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1开放供用户选择，0不开放',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `register_status` varchar(100) NOT NULL COMMENT '1通过0审核email邮件code邀请码mobile手机',
  `tbl_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '关联验证串项目',
  `fields` text NOT NULL COMMENT '会员字段，多个字段用英文逗号隔开',
  `popedom` longtext NOT NULL COMMENT '权限，包括读写及评论审核',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员组信息管理' AUTO_INCREMENT=7 ;

--
-- 转存表中的数据 `qinggan_user_group`
--

INSERT INTO `qinggan_user_group` (`id`, `title`, `status`, `is_default`, `is_guest`, `is_open`, `taxis`, `register_status`, `tbl_id`, `fields`, `popedom`) VALUES
(2, '普通会员', 1, 1, 0, 0, 10, '0', 153, '', 'a:2:{i:1;s:178:"read:149,read:87,read:90,read:146,read:92,read:93,read:43,read:41,read:42,read:147,read:45,read:150,read:96,post:96,read:144,read:151,read:152,post:152,read:142,read:148,read:153";i:18;s:0:"";}'),
(3, '游客组', 1, 0, 1, 0, 200, '0', 0, '', 'a:1:{i:1;s:169:"read:149,read:87,read:90,read:146,read:92,read:93,read:43,read:41,read:42,read:147,read:45,read:150,read:96,post:96,read:144,read:151,read:152,read:142,read:148,read:153";}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

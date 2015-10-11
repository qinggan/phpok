-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1:3306
-- 生成日期: 2015 年 10 月 11 日 12:25
-- 服务器版本: 5.5.40
-- PHP 版本: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `phpok`
--

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
  `fullname` varchar(100) NOT NULL COMMENT '姓名',
  `close_tip` varchar(255) NOT NULL COMMENT '关闭窗口前弹出的提示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='管理员信息' AUTO_INCREMENT=12 ;

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
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否前台调用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分类管理' AUTO_INCREMENT=40 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_attr`
--

CREATE TABLE IF NOT EXISTS `qinggan_attr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(100) NOT NULL COMMENT '属性名称',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='产品属性' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_attr_values`
--

CREATE TABLE IF NOT EXISTS `qinggan_attr_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '属性ID',
  `title` varchar(200) NOT NULL COMMENT '参数名称',
  `pic` varchar(200) NOT NULL COMMENT '参数图片',
  `taxis` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `val` varchar(255) NOT NULL COMMENT '值',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='属性参数管理' AUTO_INCREMENT=11 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='购物车' AUTO_INCREMENT=48 ;

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
  `weight` float unsigned NOT NULL DEFAULT '0' COMMENT '重量',
  `volume` float unsigned NOT NULL DEFAULT '0' COMMENT '体积',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='购物车里的产品信息' AUTO_INCREMENT=33 ;

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
  `tag` varchar(255) NOT NULL COMMENT '自身Tag设置',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `site_id` (`site_id`,`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分类管理' AUTO_INCREMENT=219 ;

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
  `code_num` varchar(5) NOT NULL COMMENT '币种数值',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='货币管理' AUTO_INCREMENT=1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='邮件内容' AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_express`
--

CREATE TABLE IF NOT EXISTS `qinggan_express` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点ID，为0所有站点使用',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `company` varchar(255) NOT NULL COMMENT '公司名称',
  `homepage` varchar(255) NOT NULL COMMENT '官方网站',
  `code` varchar(100) NOT NULL COMMENT '接口标识，用于读取logistics文件夹下的接口文件',
  `rate` int(11) NOT NULL DEFAULT '6' COMMENT '查询频率，用于减少请求',
  `ext` text NOT NULL COMMENT '扩展数据保存',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='快递平台管理' AUTO_INCREMENT=1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=278 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_extc`
--

CREATE TABLE IF NOT EXISTS `qinggan_extc` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容值ID，对应ext表中的id',
  `content` longtext NOT NULL COMMENT '内容文本',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='扩展字段内容维护';

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_freight`
--

CREATE TABLE IF NOT EXISTS `qinggan_freight` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '运费模板ID，自增ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '网站ID',
  `title` varchar(100) NOT NULL COMMENT '模板名称，便于后台管理',
  `type` enum('weight','volume','number','fixed') NOT NULL DEFAULT 'weight' COMMENT 'weight重量volume体积number数量fixed固定值',
  `currency_id` int(11) NOT NULL DEFAULT '0' COMMENT '货币ID',
  `taxis` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='物流运费模板管理' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_freight_price`
--

CREATE TABLE IF NOT EXISTS `qinggan_freight_price` (
  `zid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '区域ID',
  `unit_val` varchar(20) NOT NULL COMMENT '单位量，如0.5kg，或1个或1立方米，取决于系统设定',
  `price` varchar(50) NOT NULL DEFAULT '0' COMMENT '运费价格，最低为0，不能为负数',
  PRIMARY KEY (`zid`,`unit_val`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='单位体积价格';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_freight_zone`
--

CREATE TABLE IF NOT EXISTS `qinggan_freight_zone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '运费模板ID',
  `title` varchar(100) NOT NULL COMMENT '名称',
  `taxis` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `note` varchar(255) NOT NULL COMMENT '简单说明该区域信息',
  `area` longtext NOT NULL COMMENT '省份+城市',
  PRIMARY KEY (`id`),
  KEY `fid` (`fid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='运费模板区域设置' AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_gateway`
--

CREATE TABLE IF NOT EXISTS `qinggan_gateway` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID，为0表示所有站点可用',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不可用1可用',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1表示默认使用',
  `type` varchar(50) NOT NULL COMMENT '类型，gateway文件夹的子文件夹',
  `code` varchar(50) NOT NULL COMMENT '路由引挈',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `note` varchar(255) NOT NULL COMMENT '功能备注',
  `ext` text NOT NULL COMMENT '扩展参数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='第三方网关路由引挈' AUTO_INCREMENT=1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='上传图片自动生成方案' AUTO_INCREMENT=30 ;

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
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `site_id` (`site_id`,`identifier`,`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='内容主表' AUTO_INCREMENT=1427 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_attr`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_attr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '属性组ID',
  `vid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '参数ID',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '增减价格值',
  `weight` float NOT NULL DEFAULT '0' COMMENT '重量增减',
  `volume` float NOT NULL DEFAULT '0' COMMENT '体积增减值，带-号为减值',
  `taxis` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='主题属性' AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_biz`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_biz` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '价格',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '货币ID',
  `weight` float unsigned NOT NULL DEFAULT '0' COMMENT '重量，单位是Kg',
  `volume` float unsigned NOT NULL DEFAULT '0' COMMENT '体积，单位立方米',
  `unit` varchar(50) NOT NULL COMMENT '单位',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='电子商务';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_cate`
--

CREATE TABLE IF NOT EXISTS `qinggan_list_cate` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `cate_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  PRIMARY KEY (`id`,`cate_id`),
  KEY `id` (`id`),
  KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='主题绑定的分类';

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模块管理，每创建一个模块自动创建一个表' AUTO_INCREMENT=73 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='字段管理器' AUTO_INCREMENT=274 ;

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

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_opt_group`
--

CREATE TABLE IF NOT EXISTS `qinggan_opt_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID ',
  `title` varchar(100) NOT NULL COMMENT '名称，用于后台管理',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='可选菜单管理器' AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order`
--

CREATE TABLE IF NOT EXISTS `qinggan_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `sn` varchar(255) NOT NULL COMMENT '订单编号，唯一值',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示游客',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '金额',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '货币类型',
  `status` varchar(255) NOT NULL COMMENT '订单的最后状态',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `passwd` varchar(255) NOT NULL COMMENT '密码串',
  `ext` text NOT NULL COMMENT '扩展内容信息，可用于存储一些扩展信息',
  `note` text NOT NULL COMMENT '摘要',
  `email` varchar(255) NOT NULL COMMENT '邮箱，用于接收通知',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordersn` (`sn`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单中心' AUTO_INCREMENT=48 ;

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
  `mobile` varchar(100) NOT NULL COMMENT '手机号码',
  `tel` varchar(100) NOT NULL COMMENT '电话号码',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `fullname` varchar(100) NOT NULL COMMENT '联系人姓名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单地址库' AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_express`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_express` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `express_id` int(11) NOT NULL DEFAULT '0' COMMENT '物流ID号',
  `code` varchar(255) NOT NULL COMMENT '物流查询编码，可用于查询快递进度',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登记时间',
  `last_query_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次检索时间',
  `title` varchar(255) NOT NULL COMMENT '快递名称',
  `homepage` varchar(255) NOT NULL COMMENT '快递官网',
  `company` varchar(255) NOT NULL COMMENT '快递的公司全称',
  `is_end` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未结束1已结束',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单中涉及到的物流分配' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_invoice`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_invoice` (
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `type` varchar(100) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `content` text NOT NULL COMMENT '发票内容',
  `note` text NOT NULL COMMENT '发票的备注信息',
  PRIMARY KEY (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单发票';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_log`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_express_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '定单中的物流ID',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `who` varchar(255) NOT NULL COMMENT '操作人名称（可以是公司名称，也可以是用户名，可以是物流等）',
  `note` text NOT NULL COMMENT '操作内容',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单日志，用于了解当前的订单处理进度' AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_payment`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `payment_id` int(11) NOT NULL DEFAULT '0' COMMENT '支付方式ID，为0表示货到付款或线下支付',
  `title` varchar(255) NOT NULL COMMENT '支付方式名称',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '支付金额',
  `startdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始支付操作',
  `dateline` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `ext` text NOT NULL COMMENT '其他常用扩展信息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单支付' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_price`
--

CREATE TABLE IF NOT EXISTS `qinggan_order_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `code` varchar(255) NOT NULL COMMENT '编码',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '金额，-号表示优惠',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单金额明细清单' AUTO_INCREMENT=142 ;

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
  `thumb` varchar(255) NOT NULL COMMENT '产品图片地址',
  `ext` text NOT NULL COMMENT '产品扩展属性',
  `weight` varchar(50) NOT NULL COMMENT '重量',
  `volume` varchar(50) NOT NULL COMMENT '体积',
  `unit` varchar(50) NOT NULL COMMENT '单位',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='订单的产品信息' AUTO_INCREMENT=58 ;

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
  `wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0PC端1手机端',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='支付方案' AUTO_INCREMENT=1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='付款组管理' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_payment_log`
--

CREATE TABLE IF NOT EXISTS `qinggan_payment_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sn` varchar(255) NOT NULL COMMENT '支付编号',
  `type` varchar(100) NOT NULL COMMENT 'order订单,recharge充值other其他',
  `payment_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式',
  `title` varchar(255) NOT NULL COMMENT '主题',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
  `currency_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '货币ID',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未支付成功1已支付成功',
  `ext` text NOT NULL COMMENT '扩展',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='支付日志' AUTO_INCREMENT=1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='数据调用中心' AUTO_INCREMENT=106 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限明细' AUTO_INCREMENT=785 ;

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
  `is_identifier` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义标识',
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
  `is_userid` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否绑定会员',
  `is_tpl_content` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义内容模板',
  `is_seo` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认使用seo',
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
  `tag` varchar(255) NOT NULL COMMENT '自身Tag设置',
  `is_appoint` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '指定维护',
  `cate_multiple` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0分类单选1分类支持多选',
  `biz_attr` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '产品属性，0不使用1使用',
  `freight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '运费模板ID',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `site_id` (`site_id`,`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='项目管理器' AUTO_INCREMENT=165 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='主题评论表' AUTO_INCREMENT=34 ;

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
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  PRIMARY KEY (`id`),
  KEY `ext` (`ext`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源ID' AUTO_INCREMENT=993 ;

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
  `filetypes` varchar(255) NOT NULL COMMENT '附件类型',
  `typeinfo` varchar(200) NOT NULL COMMENT '类型说明',
  `gdtypes` varchar(255) NOT NULL COMMENT '支持的GD方案，多个GD方案用英文ID分开',
  `gdall` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1支持全部GD方案0仅支持指定的GD方案',
  `ico` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台缩略图',
  `filemax` int(10) unsigned NOT NULL DEFAULT '2' COMMENT '上传文件大小限制',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源分类存储' AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res_ext`
--

CREATE TABLE IF NOT EXISTS `qinggan_res_ext` (
  `res_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '附件ID',
  `gd_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT 'GD库方案ID',
  `filename` varchar(255) NOT NULL COMMENT '文件地址（含路径）',
  `filetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后',
  PRIMARY KEY (`res_id`,`gd_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='生成扩展图片';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_rewrite`
--

CREATE TABLE IF NOT EXISTS `qinggan_rewrite` (
  `id` varchar(100) NOT NULL COMMENT '规则ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `urltype` varchar(255) NOT NULL COMMENT '网址规则',
  PRIMARY KEY (`id`,`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='网址规范，用于伪静态页';

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
  `api_code` varchar(255) NOT NULL COMMENT 'API验证串',
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='网站管理' AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_site_domain`
--

CREATE TABLE IF NOT EXISTS `qinggan_site_domain` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` mediumint(8) unsigned NOT NULL COMMENT '网站ID',
  `domain` varchar(255) NOT NULL COMMENT '域名信息',
  `is_mobile` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1此域名强制为手机版',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='网站指定的域名' AUTO_INCREMENT=26 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='PHPOK后台系统菜单' AUTO_INCREMENT=79 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tag`
--

CREATE TABLE IF NOT EXISTS `qinggan_tag` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `url` varchar(255) NOT NULL COMMENT '关键字网址',
  `target` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0原窗口打开，1新窗口打开',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击次数',
  `alt` varchar(255) NOT NULL COMMENT '链接里的提示',
  `is_global` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否全局状态1是0否',
  `replace_count` tinyint(4) NOT NULL DEFAULT '3' COMMENT '替换次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='关键字管理器' AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tag_stat`
--

CREATE TABLE IF NOT EXISTS `qinggan_tag_stat` (
  `title_id` varchar(200) NOT NULL COMMENT '主题ID，以p开头的表示项目ID，以c开头的表示分类ID',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TAG标签ID',
  PRIMARY KEY (`title_id`,`tag_id`),
  KEY `title_id` (`title_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tag主题统计';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_task`
--

CREATE TABLE IF NOT EXISTS `qinggan_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `year` varchar(9) NOT NULL COMMENT '年份',
  `month` varchar(5) NOT NULL COMMENT '月',
  `day` varchar(5) NOT NULL COMMENT '日',
  `hour` varchar(5) NOT NULL COMMENT '时',
  `minute` varchar(5) NOT NULL COMMENT '分',
  `second` varchar(5) NOT NULL COMMENT '秒',
  `exec_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始执行时间',
  `stop_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `action` varchar(100) NOT NULL COMMENT '执行动作脚本',
  `param` varchar(255) NOT NULL COMMENT '参数',
  `only_once` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1表示仅执行一次',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未锁定1已锁定',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='计划任务' AUTO_INCREMENT=1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模板管理' AUTO_INCREMENT=19 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员管理' AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_address`
--

CREATE TABLE IF NOT EXISTS `qinggan_user_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `country` varchar(255) NOT NULL COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省信息',
  `city` varchar(255) NOT NULL COMMENT '市',
  `county` varchar(255) NOT NULL COMMENT '县',
  `address` varchar(255) NOT NULL COMMENT '地址信息（不含国家，省市县镇区信息）',
  `mobile` varchar(200) NOT NULL COMMENT '手机',
  `tel` varchar(200) NOT NULL COMMENT '固定电话',
  `email` varchar(255) NOT NULL COMMENT '邮箱，用来接收订单提醒邮件',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0普通，1默认填写',
  `fullname` varchar(100) NOT NULL COMMENT '姓名',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员地址库' AUTO_INCREMENT=12 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员组信息管理' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_invoice`
--

CREATE TABLE IF NOT EXISTS `qinggan_user_invoice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `type` varchar(100) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `content` varchar(255) NOT NULL COMMENT '发票内容',
  `note` text NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='会员发票信息' AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_relation`
--

CREATE TABLE IF NOT EXISTS `qinggan_user_relation` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `introducer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '介绍人ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '介绍时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员介绍关系图';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth`
--

CREATE TABLE IF NOT EXISTS `qinggan_wealth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '财富ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(100) NOT NULL COMMENT '财产名称',
  `identifier` varchar(100) NOT NULL COMMENT '标识，仅限英文字符',
  `unit` varchar(100) NOT NULL COMMENT '单位名称',
  `dnum` tinyint(1) NOT NULL DEFAULT '0' COMMENT '保留几位小数，为0表示只取整数',
  `ifpay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持充值',
  `pay_ratio` float unsigned NOT NULL DEFAULT '0' COMMENT '兑换比例，即1元可以兑换多少，为0不支持充值，为1表示1：1，不支持小数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，0-255，越小越往前靠',
  `ifcash` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否抵现，即允许财富当现金使用',
  `cash_ratio` float unsigned NOT NULL DEFAULT '0' COMMENT '抵现比例，即100财富值可抵用多少元',
  `ifcheck` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核，为1时表示获取到的财富需要管理员审核后才行',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财富类型' AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth_info`
--

CREATE TABLE IF NOT EXISTS `qinggan_wealth_info` (
  `wid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '方案ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID或会员ID或分类ID或项目ID',
  `lasttime` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
  `val` float unsigned NOT NULL DEFAULT '0' COMMENT '最小财富为0，不考虑负数情况',
  PRIMARY KEY (`wid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财富内容';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth_log`
--

CREATE TABLE IF NOT EXISTS `qinggan_wealth_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `wid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '财富ID',
  `goal_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '目标会员ID',
  `mid` varchar(100) NOT NULL COMMENT '主键ID关联',
  `val` int(11) NOT NULL DEFAULT '0' COMMENT '不带负号表示增加，带负号表示减去',
  `note` varchar(255) NOT NULL COMMENT '操作摘要',
  `appid` enum('admin','www','api') NOT NULL DEFAULT 'www' COMMENT '来自哪个接口',
  `dateline` int(11) NOT NULL DEFAULT '0' COMMENT '写入时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID，为0非会员',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID，为0非管理员',
  `ctrlid` varchar(100) NOT NULL COMMENT '控制器ID',
  `funcid` varchar(100) NOT NULL COMMENT '方法ID',
  `url` varchar(255) NOT NULL COMMENT '执行的URL',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未审核1已审核',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财富获取或消耗日志' AUTO_INCREMENT=72 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth_rule`
--

CREATE TABLE IF NOT EXISTS `qinggan_wealth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则ID',
  `wid` int(10) unsigned NOT NULL COMMENT '财产ID',
  `action` varchar(255) NOT NULL COMMENT '触发动作',
  `repeat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不支持重复1支持多次',
  `mintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在多少秒内重复是无效的，为0表示不限时',
  `val` varchar(255) NOT NULL DEFAULT '0' COMMENT '值，负值表示减，大于0表示加，支持计算如price*2',
  `goal` enum('user','introducer') NOT NULL DEFAULT 'user' COMMENT '目标类型user会员introducer介绍人',
  `efunc` varchar(255) NOT NULL COMMENT '自定义执行的函数',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序',
  `linkid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '绑定主题，0不考虑1考虑',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财富生成规则' AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_workflow`
--

CREATE TABLE IF NOT EXISTS `qinggan_workflow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '指派谁来管理的管理员ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `is_end` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否结束',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `note` varchar(255) NOT NULL,
  `actting` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1正在操作处理中',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='工作流处理' AUTO_INCREMENT=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

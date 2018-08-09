-- phpMyAdmin SQL Dump
-- version 4.7.8
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 2018-08-09 18:20:13
-- 服务器版本： 5.5.53
-- PHP Version: 5.5.38

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
-- 表的结构 `qinggan_adm`
--

CREATE TABLE `qinggan_adm` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '管理员ID，系统自增',
  `account` varchar(50) NOT NULL COMMENT '管理员账号',
  `pass` varchar(100) NOT NULL COMMENT '管理员密码',
  `email` varchar(50) NOT NULL COMMENT '管理员邮箱',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未审核1正常2管理员锁定',
  `if_system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '系统管理员',
  `vpass` varchar(50) NOT NULL COMMENT '二次验证密码，两次MD5加密',
  `fullname` varchar(100) NOT NULL COMMENT '姓名',
  `close_tip` varchar(255) NOT NULL COMMENT '关闭窗口前弹出的提示'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理员信息';

--
-- 转存表中的数据 `qinggan_adm`
--

INSERT INTO `qinggan_adm` (`id`, `account`, `pass`, `email`, `status`, `if_system`, `vpass`, `fullname`, `close_tip`) VALUES
(1, 'admin', '101d9fd14b31a93b06a10421f14dd023:21', 'qinggan@188.com', 1, 1, '', '苏相锟', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_adm_popedom`
--

CREATE TABLE `qinggan_adm_popedom` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '管理员ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权限ID，对应popedom表里的id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='管理员权限分配表';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_all`
--

CREATE TABLE `qinggan_all` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `identifier` varchar(100) NOT NULL COMMENT '标识串',
  `title` varchar(200) NOT NULL COMMENT '分类名称',
  `ico` varchar(255) NOT NULL COMMENT '图标',
  `is_system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0普通１系统',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否前台调用'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分类管理';

--
-- 转存表中的数据 `qinggan_all`
--

INSERT INTO `qinggan_all` (`id`, `site_id`, `identifier`, `title`, `ico`, `is_system`, `status`) VALUES
(4, 1, 'copyright', '页脚版权', 'images/ico/copyright.png', 0, 1),
(37, 1, 'share', '分享代码', 'images/ico/share.png', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_attr`
--

CREATE TABLE `qinggan_attr` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(100) NOT NULL COMMENT '属性名称',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品属性';

--
-- 转存表中的数据 `qinggan_attr`
--

INSERT INTO `qinggan_attr` (`id`, `site_id`, `title`, `taxis`) VALUES
(1, 1, '颜色', 5),
(3, 1, '尺码', 10),
(8, 1, '版本', 15);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_attr_values`
--

CREATE TABLE `qinggan_attr_values` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `aid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '属性ID',
  `title` varchar(200) NOT NULL COMMENT '参数名称',
  `pic` varchar(200) NOT NULL COMMENT '参数图片',
  `taxis` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
  `val` varchar(255) NOT NULL COMMENT '值'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='属性参数管理';

--
-- 转存表中的数据 `qinggan_attr_values`
--

INSERT INTO `qinggan_attr_values` (`id`, `aid`, `title`, `pic`, `taxis`, `val`) VALUES
(1, 1, '红色', '', 10, 'red'),
(3, 1, '绿色', '', 20, 'green'),
(4, 1, '蓝色', '', 30, 'blue'),
(5, 1, '黑色', '', 40, 'black'),
(6, 1, '白色', '', 50, 'white'),
(7, 3, 'M', '', 10, ''),
(8, 3, 'L', '', 20, ''),
(9, 3, 'XL', '', 30, ''),
(10, 3, 'XXL', '', 40, ''),
(29, 8, '标准版(3G RAM+32G ROM)标配', '', 10, '2499'),
(30, 8, '标准版(3G RAM+32G ROM)套装', '', 20, '2549'),
(31, 8, '高配版(3G RAM+64G ROM)标配', '', 30, '3199'),
(32, 8, '高配版(3G RAM+64G ROM)套装', '', 40, '3249'),
(33, 1, '金色', '', 60, 'gold'),
(34, 8, '16G ROM', '', 50, 'MZ16G'),
(35, 8, '32G ROM', '', 60, 'MZ32G'),
(36, 1, '灰色', '', 70, 'gray'),
(37, 8, '64G ROM', '', 70, '64G'),
(38, 1, 'demo', '', 80, 'demo'),
(39, 1, 'ok', '', 90, ''),
(40, 8, '在99', '', 40, '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_cart`
--

CREATE TABLE `qinggan_cart` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `session_id` varchar(255) NOT NULL COMMENT 'SESSION_ID号',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示游客',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='购物车';

--
-- 转存表中的数据 `qinggan_cart`
--

INSERT INTO `qinggan_cart` (`id`, `session_id`, `user_id`, `addtime`) VALUES
(40, 'f4m6ofv7tgv5h3cafpsmt6ohq5', 0, 1533784722);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_cart_product`
--

CREATE TABLE `qinggan_cart_product` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `cart_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '购物车ID号',
  `tid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `title` varchar(255) NOT NULL COMMENT '产品名称',
  `price` float NOT NULL COMMENT '产品单价',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `ext` text NOT NULL COMMENT '扩展属性',
  `weight` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '重量',
  `volume` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '体积',
  `thumb` varchar(255) NOT NULL COMMENT '缩略图',
  `is_virtual` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0实物1虚拟或服务',
  `unit` varchar(50) NOT NULL COMMENT '单位',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后操作时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='购物车里的产品信息';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_cate`
--

CREATE TABLE `qinggan_cate` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `parent_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID，0为根分类',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不使用1正常使用',
  `title` varchar(200) NOT NULL COMMENT '分类名称',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '分类排序，值越小越往前靠',
  `tpl_list` varchar(255) NOT NULL COMMENT '列表模板',
  `tpl_content` varchar(255) NOT NULL COMMENT '内容模板',
  `psize` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '列表每页数量',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` varchar(255) NOT NULL COMMENT 'SEO描述',
  `identifier` varchar(255) NOT NULL COMMENT '分类标识串',
  `tag` varchar(255) NOT NULL COMMENT '自身Tag设置'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分类管理';

--
-- 转存表中的数据 `qinggan_cate`
--

INSERT INTO `qinggan_cate` (`id`, `site_id`, `parent_id`, `status`, `title`, `taxis`, `tpl_list`, `tpl_content`, `psize`, `seo_title`, `seo_keywords`, `seo_desc`, `identifier`, `tag`) VALUES
(7, 1, 0, 1, '新闻资讯', 10, '', '', 0, '', '', '', 'information', ''),
(8, 1, 7, 1, '公司新闻', 10, '', '', 0, '', '', '', 'company', '公司 新闻'),
(68, 1, 7, 1, '行业新闻', 25, '', '', 0, '', '', '', 'industry', ''),
(70, 1, 0, 1, '产品分类', 20, '', '', 0, '', '', '', 'chanpinfenlei', ''),
(154, 1, 0, 1, '图集相册', 30, '', '', 0, '', '', '', 'album', ''),
(168, 1, 70, 1, '手机', 10, '', '', 0, '', '', '', 'shouji', ''),
(180, 1, 70, 1, '产品分类二', 20, '', '', 0, '', '', '', 'chanpinfenleier', ''),
(197, 1, 0, 1, '资源下载', 40, '', '', 0, '', '', '', 'ziyuanxiazai', ''),
(198, 1, 197, 1, '软件下载', 10, '', '', 0, '', '', '', 'ruanjianxiazai', ''),
(199, 1, 197, 1, '风格下载', 20, '', '', 0, '', '', '', 'fenggexiazai', ''),
(200, 1, 197, 1, '官方插件', 30, '', '', 0, '', '', '', 'guanfangchajian', ''),
(201, 1, 0, 1, '论坛分类', 50, '', '', 0, '', '', '', 'bbs-cate', ''),
(204, 1, 201, 1, '情感驿站', 10, '', '', 0, '', '', '', 'qingganyizhan', ''),
(205, 1, 201, 1, '产品讨论', 20, '', '', 0, '', '', '', 'chanpintaolun', ''),
(206, 1, 201, 1, '水吧专区', 30, '', '', 0, '', '', '', 'shuibazhuanqu', ''),
(207, 1, 201, 1, '常见问题', 30, '', '', 0, '', '', '', 'faq', ''),
(211, 1, 154, 1, '手机美图', 10, '', '', 0, '', '', '', 'shoujimeitu', ''),
(216, 1, 168, 1, '苹果', 50, '', '', 0, '', '', '', 'apple', ''),
(219, 1, 70, 1, '产品分类三', 30, '', '', 0, '', '', '', 'chanpinfenleisan', ''),
(582, 1, 168, 1, '小米', 10, '', '', 0, '', '', '', 'xiaomi', ''),
(583, 1, 168, 1, '魅族', 20, '', '', 0, '', '', '', 'meizu', ''),
(584, 1, 168, 1, '华为', 30, '', '', 0, '', '', '', 'huawei', ''),
(585, 1, 168, 1, 'Vivo', 40, '', '', 0, '', '', '', 'vivo', ''),
(588, 1, 583, 1, '经典', 5, '', '', 0, '', '', '', 'jingdian', ''),
(589, 1, 583, 1, '旗舰', 10, '', '', 0, '', '', '', 'qijian', ''),
(590, 1, 588, 1, 'MX2', 5, '', '', 0, '', '', '', 'mx2', ''),
(591, 1, 588, 1, 'MX3', 10, '', '', 0, '', '', '', 'mx3', ''),
(592, 1, 0, 1, 'Demo', 55, '', '', 0, '', '', '', 'demo', ''),
(593, 1, 592, 1, 'Demo1', 5, '', '', 0, '', '', '', 'demo1', ''),
(594, 1, 592, 1, 'Demo2', 10, '', '', 0, '', '', '', 'demo2', ''),
(595, 1, 592, 1, 'Demo3', 15, '', '', 0, '', '', '', 'demo3', ''),
(596, 1, 0, 1, '测试根分类及扩展', 60, '', '', 0, '', '', '', 'ceshigenfenleijikuozhan', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_currency`
--

CREATE TABLE `qinggan_currency` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '货币ID',
  `code` varchar(3) NOT NULL COMMENT '货币标识，仅限三位数的大写字母',
  `val` decimal(13,8) UNSIGNED NOT NULL COMMENT '货币转化',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `title` varchar(50) NOT NULL COMMENT '名称',
  `symbol_left` varchar(24) NOT NULL COMMENT '价格左侧',
  `symbol_right` varchar(24) NOT NULL COMMENT '价格右侧',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不隐藏1隐藏',
  `code_num` varchar(5) NOT NULL COMMENT '币种数值'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='货币管理';

--
-- 转存表中的数据 `qinggan_currency`
--

INSERT INTO `qinggan_currency` (`id`, `code`, `val`, `taxis`, `title`, `symbol_left`, `symbol_right`, `status`, `hidden`, `code_num`) VALUES
(1, 'CNY', '6.16989994', 10, '人民币', '', '元', 1, 0, '165'),
(2, 'USD', '1.00000000', 20, '美金', 'US$', '', 1, 0, '840'),
(3, 'HKD', '7.76350021', 30, '港元', 'HK$', '', 1, 0, '344'),
(4, 'EUR', '0.76639998', 40, '欧元', 'EUR', '', 1, 0, '978'),
(5, 'GBP', '0.64529997', 50, '英镑', '￡', '', 1, 0, '826'),
(7, 'AUD', '1.00000000', 60, '澳币', 'A$', '', 1, 0, '036');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_email`
--

CREATE TABLE `qinggan_email` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID，0表示全部网站',
  `identifier` varchar(255) NOT NULL COMMENT '发送标识',
  `title` varchar(200) NOT NULL COMMENT '邮件主题',
  `content` text NOT NULL COMMENT '邮件内容',
  `note` varchar(255) NOT NULL COMMENT '备注'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='邮件内容';

--
-- 转存表中的数据 `qinggan_email`
--

INSERT INTO `qinggan_email` (`id`, `site_id`, `identifier`, `title`, `content`, `note`) VALUES
(4, 1, 'register_code', '获取会员注册资格', '<p>您好，{$email}</p><p>您将注册成为网站【{$config.title} 】会员，请点击下面的地址，进入下一步注册：</p><p><br/></p><blockquote style=\"margin: 0 0 0 40px; border: none; padding: 0px;\"><p><a href=\"{$link}\" target=\"_blank\">{$link}</a></p><p>（此链接24小时内有效）</p></blockquote><p><br/></p><p><br/></p><p>感谢您对本站的关注，茫茫人海中，能有缘走到一起。</p>', ''),
(5, 1, 'getpass', '取回密码操作', '<p>您好，{$user.account}</p><p>您执行了忘记密码操作功能，请点击下面的链接执行下一步：</p><p><br /></p><p><blockquote style=\"margin: 0 0 0 40px; border: none; padding: 0px;\"><p><a href=\"{$link}\" target=\"_blank\">{$link}</a></p></blockquote><br /></p><p>感谢您对本站的支持，有什么问题您在登录后可以咨询我们的客服。</p>', ''),
(6, 1, 'project_save', '主题添加通知', '<p>您好，管理员</p><blockquote><p>您的网站（<a href=\"http://{$sys.url}\" target=\"_self\">{$sys.url}</a>）新增了一篇主题，下述是主题的基本信息：<br/></p><p>主题名称：{$rs.title}</p><p>项目类型：{$page_rs.title}</p><p><br/></p><p>请登录网站查询</p></blockquote>', ''),
(7, 1, 'order_admin', '网站有新订单【{$order.sn}】', '<p>您好，管理员</p><blockquote><p>您的网站：{$sys.url} 收到一份新的订单，订单号是：{$order.sn}，请登录网站后台进行核验。</p></blockquote>', ''),
(8, 1, 'user_order_create', '我们已收到您的订单【{$order.sn}】，欢迎您随时关注订单状态', '<p><strong>尊敬的{$fullname}，您好：</strong><br/></p><p><br/></p><p>感谢您在<span style=\"color: rgb(0, 112, 192);\">{$config.title}</span>（{$sys.url}）购物。</p><p>我们已经收到您的订单<span style=\"color: rgb(0, 112, 192);\">{$order.sn}</span>，建议您选择<span style=\"color: rgb(0, 112, 192);\">在线支付</span>的支付配送方式。订单信息以个人中心里的“<span style=\"color: rgb(0, 112, 192);\"><strong>我的订单</strong></span>”信息为准，您也可以随时进入订单详细进行查看修改等操作。</p><p><br/></p><p><strong>重要说明：</strong></p><p>本邮件仅表明销售方已收到了您提交的订单；销售方收到你的订单信息后，只有在销售方将您的订单中订购的商品从仓库实际直接向您发出时（以商品出库为标志），方视为您与销售方之间就实际直接向您发出的商品建立了合同关系；<br/>如果您在一份订单里订购了多种商品并且销售方只给您发出了部分商品时，您与销售方之间仅就实际直接向您发出的商品建立了合同关系；只有在销售方实际直接向您发出了订单中订购的其他商品时，您和销售方之间就订单中该其他已实际直接向您发出的商品建立了合同关系。<br/>您可以随时登陆您在京东注册的账户，查询您的订单状态。更多内容请见最新的京东网站用户注册协议及京东网站各类购物规则，我们建议您不时地浏览阅读。</p><p><br/></p><p><strong>账户安全提醒：</strong><br/>互联网账号存在被盗风险，为了保障您的账号及资金安全，我们提醒您访问 我的账户- &gt; 修改密码，尽量使用复杂密码，如字母+数字+特殊符号等。</p>', ''),
(9, 1, 'sms_order_create', '会员下单成功后，短信通知', '<p>您的订单：{$order.sn} 已成功提交，请您及时完成支付操作。超过24小时未支付订单将会自动删除。感谢您对我们的支持！</p>', ''),
(10, 1, 'order_user_paid', '您的订单【{$order.sn}】已支付成功', '<p><strong>尊敬的{$fullname}，您好：</strong></p><p style=\"white-space: normal;\">感谢您在<span style=\"color: rgb(0, 112, 192);\">{$config.title}</span>（{$sys.url}）购物。</p><p style=\"white-space: normal;\">您的订单<span style=\"color: rgb(0, 112, 192);\">{$order.sn}</span>已成功支付，请耐心等候，我们管理员正在核验付款信息。</p><p style=\"white-space: normal;\">订单信息以个人中心里的“<span style=\"color: rgb(0, 112, 192);\"><strong>我的订单</strong></span>”信息为准，您也可以随时进入订单详细进行查看修改等操作。</p><p style=\"white-space: normal;\"><br/></p><p style=\"white-space: normal;\"><strong>重要说明：</strong></p><p style=\"white-space: normal;\">本邮件仅表明销售方已收到了您提交的订单；销售方收到你的订单信息后，只有在销售方将您的订单中订购的商品从仓库实际直接向您发出时（以商品出库为标志），方视为您与销售方之间就实际直接向您发出的商品建立了合同关系；<br/>如果您在一份订单里订购了多种商品并且销售方只给您发出了部分商品时，您与销售方之间仅就实际直接向您发出的商品建立了合同关系；只有在销售方实际直接向您发出了订单中订购的其他商品时，您和销售方之间就订单中该其他已实际直接向您发出的商品建立了合同关系。<br/>您可以随时登陆您在京东注册的账户，查询您的订单状态。更多内容请见最新的京东网站用户注册协议及京东网站各类购物规则，我们建议您不时地浏览阅读。</p><p style=\"white-space: normal;\"><br/></p><p style=\"white-space: normal;\"><strong>账户安全提醒：</strong><br/>互联网账号存在被盗风险，为了保障您的账号及资金安全，我们提醒您访问 我的账户- &gt; 修改密码，尽量使用复杂密码，如字母+数字+特殊符号等。</p><p><br/></p>', ''),
(11, 1, 'order_admin_paid', '客户{$user.user}订单【{$orser.sn}】付款成功', '<p>您好，管理员，请登录网站后台 {$sys.url} 核验订单【{$orser.sn}】支付是否成功</p>', ''),
(12, 1, 'sms_order_paid', '订单付款成功后的通知', '<p>您的订单：{$order.sn} 已成功付款，我们正在核验中，请耐心等候！</p>', ''),
(13, 1, 'order_user_shipped', '您的订单【{$order.sn}】已发货', '<p style=\"white-space: normal;\"><strong>尊敬的{$fullname}，您好：</strong><br/></p><p style=\"white-space: normal;\"><br/></p><p style=\"white-space: normal;\">感谢您在<span style=\"color: rgb(0, 112, 192);\">{$config.title}</span>（{$sys.url}）购物。</p><p style=\"white-space: normal;\">您的订单<span style=\"color: rgb(0, 112, 192);\">{$order.sn}</span>已经发货，请保持您的电话畅通，以方便快递人员能与您取得联系。</p><p style=\"white-space: normal;\">订单信息以个人中心里的“<span style=\"color: rgb(0, 112, 192);\"><strong>我的订单</strong></span>”信息为准，您也可以随时进入订单详细进行查看修改等操作。</p><p style=\"white-space: normal;\"><br/></p><p style=\"white-space: normal;\"><strong>重要说明：</strong></p><p style=\"white-space: normal;\">本邮件仅表明销售方已收到了您提交的订单；销售方收到你的订单信息后，只有在销售方将您的订单中订购的商品从仓库实际直接向您发出时（以商品出库为标志），方视为您与销售方之间就实际直接向您发出的商品建立了合同关系；<br/>如果您在一份订单里订购了多种商品并且销售方只给您发出了部分商品时，您与销售方之间仅就实际直接向您发出的商品建立了合同关系；只有在销售方实际直接向您发出了订单中订购的其他商品时，您和销售方之间就订单中该其他已实际直接向您发出的商品建立了合同关系。<br/>您可以随时登陆您在京东注册的账户，查询您的订单状态。更多内容请见最新的京东网站用户注册协议及京东网站各类购物规则，我们建议您不时地浏览阅读。</p><p style=\"white-space: normal;\"><br/></p><p style=\"white-space: normal;\"><strong>账户安全提醒：</strong><br/>互联网账号存在被盗风险，为了保障您的账号及资金安全，我们提醒您访问 我的账户- &gt; 修改密码，尽量使用复杂密码，如字母+数字+特殊符号等。</p><p><br/></p>', ''),
(14, 1, 'sms_order_shipped', '订单发货短信通知', '您的订单：{$order.sn} 已经发货，请保持电话畅通，以方便快递人员能与您取得联系。', ''),
(15, 1, 'order_admin_recerved', '订单【{$order.sn}】已确认收货', '<p>您好，管理员，客户已对订单【{$order.sn}】执行确认收货操作，请登录后台核验</p>', ''),
(16, 1, 'email_code', '【{$config.title}】邮件验证码', '<p>你的验证码是：{$code}，三十分钟内有效，请及时输入</p>', ''),
(17, 1, 'sms_code', '短信验证码', '您的验证码：{$code}，请在10分钟内输入【微光互助】', ''),
(18, 1, 'sms_paid_admin', '订单成功后管理员', '订单：{$order.sn}，客户已支付成功，请检查', ''),
(34, 1, 'login_email', '您的验证码', '<p>您的验证码是：{$code}<br/></p><p><br/></p>', ''),
(35, 1, 'sms_sendcloud_code', '4575', 'code:{$code}', 'SendCloud使用的验证码'),
(36, 1, 'sms_aliyun_test', 'SMS_49105038', 'customer:{$user.user}', '阿里云短信测试'),
(37, 1, 'sms_61825160', 'SMS_61825160', 'code:{$code}', '阿里云：验证码'),
(38, 1, 'email_toall', '您的留言 #{$rs.id}，管理员已经回复', '<p>您好：{$rs.fullname}，您在我们网站上的留言，管理员已经回复了，您可以下面链接进行查看：</p><blockquote><p><a href=\"{$sys.url}{$rs.id}.html\" target=\"_blank\">{$sys.url}{$rs.id}.html</a><br/></p></blockquote><p>感谢您对我们网站（{$config.title}）的认可。</p><p><br/></p><p>您的支持是我们发展的动力</p><p><img src=\"{$sys.url}res/201704/22/auto_1092.png\" style=\"width: 500px;\"/></p>', '留言回复通知');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_express`
--

CREATE TABLE `qinggan_express` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点ID，为0所有站点使用',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `company` varchar(255) NOT NULL COMMENT '公司名称',
  `homepage` varchar(255) NOT NULL COMMENT '官方网站',
  `code` varchar(100) NOT NULL COMMENT '接口标识，用于读取logistics文件夹下的接口文件',
  `rate` int(11) NOT NULL DEFAULT '6' COMMENT '查询频率，用于减少请求',
  `ext` text NOT NULL COMMENT '扩展数据保存'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='快递平台管理';

--
-- 转存表中的数据 `qinggan_express`
--

INSERT INTO `qinggan_express` (`id`, `site_id`, `title`, `company`, `homepage`, `code`, `rate`, `ext`) VALUES
(1, 1, '宅急送-官方', '北京宅急送快运股份有限公司', 'http://www.zjs.com.cn/', 'zjs', 4, 'a:3:{s:18:\"logisticProviderID\";s:14:\"NanFang_LianHe\";s:7:\"keyseed\";s:36:\"86AF9251-F3A4-40AF-B9CC-7E509B303F9A\";s:12:\"fixed_string\";s:13:\"z宅J急S送g\";}'),
(4, 1, '顺丰速运', '顺丰速运(集团)有限公司', 'http://www.sf-express.com/', 'showapi', 4, 'a:3:{s:6:\"app_id\";s:4:\"4485\";s:10:\"app_secret\";s:32:\"95a43a307f51416980ff86cae4c70f4e\";s:7:\"app_com\";s:8:\"shunfeng\";}'),
(5, 1, '京东物流', '京东', 'http://route-ql.jd.com/trace/push', 'jd', 10, 'a:4:{s:12:\"company_code\";s:3:\"RRT\";s:10:\"debug_pass\";s:6:\"111111\";s:7:\"app_key\";s:6:\"123456\";s:12:\"push_address\";s:36:\"http://111.202.36.9/trace/trace/push\";}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_extc`
--

CREATE TABLE `qinggan_extc` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '内容值ID，对应ext表中的id',
  `content` longtext NOT NULL COMMENT '内容文本'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='扩展字段内容维护';

--
-- 转存表中的数据 `qinggan_extc`
--

INSERT INTO `qinggan_extc` (`id`, `content`) VALUES
(836, 'Powered By phpok.com 版权所有 © 2004-2014, All right reserved.'),
(837, 'News'),
(838, '1008'),
(839, '629'),
(840, '1006'),
(841, '1007'),
(842, '<h3>正品行货</h3><p>商城向您保证所售商品均为正品行货，自营商品开具机打发票或电子发票。</p><h3>全国联保</h3><p>凭质保证书及发票，可享受全国联保服务（奢侈品、钟表除外；奢侈品、钟表由京东联系保修，享受法定三包售后服务），与您亲临商场选购的商品享受相同的质量保证。商城还为您提供具有竞争力的商品价格和运费政策，请您放心购买！&nbsp;</p><p>注：因厂家会在没有任何提前通知的情况下更改产品包装、产地或者一些附件，本司不能确保客户收到的货物与商城图片、产地、附件说明完全一致。只能确保为原厂正货！并且保证与当时市场上同样主流新品一致。若本商城没有及时更新，请大家谅解！</p><h3>无忧退换货</h3><p>客户购买自营商品7日内（含7日，自客户收到商品之日起计算），在保证商品完好的前提下，可无理由退货。（部分商品除外，详情请见各商品细则）</p>'),
(843, ''),
(844, '545'),
(845, '1006'),
(846, '本区以讨论各种感情，各类人生为核心主题心灵鸡汤无处不在，不在于多少，只在于感悟懂了就是懂了，不懂仍然不懂'),
(847, '1007'),
(848, '围绕我公司提供的产品进行讨论广开言路，我公司会虚心接纳，完善产品'),
(849, '吐吐糟，发发牢骚，八卦精神无处不在笑一笑，十年少，在这个快节奏的时代里，这里还有一片净土供您休息不是我不爱，只是世界变化快^o^'),
(850, 'Photos'),
(851, 'Links'),
(852, '关于常见问题'),
(853, 'Categories'),
(854, 'Download'),
(855, '深圳市锟铻科技有限公司'),
(856, '广东深圳龙华区民治大道325号东边商务大楼13层1309室'),
(857, 'admin@phpok.com'),
(858, '15818533971'),
(859, '苏先生'),
(860, 'res/201409/03/5b0086d14de1bbf2.jpg'),
(861, '<p>深圳市锟铻科技有限公司（Shenzhen Kunwu Technology Co., Ltd.）创立于2014年，专注于企业网站技术的研究和开发，是国内最有影响力的企业网站技术提供商。</p><p>锟铻科技成长的过程，就是服务客户并和客户一起不断成功的过程！我们用心、努力作好每一件事，满怀信心迎接每一次挑战。</p>'),
(862, '518000'),
(869, '1008');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_fav`
--

CREATE TABLE `qinggan_fav` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `thumb` varchar(255) NOT NULL COMMENT '缩略图',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `note` varchar(255) NOT NULL COMMENT '摘要',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `lid` int(11) NOT NULL COMMENT '关联主题'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员收藏夹';

--
-- 转存表中的数据 `qinggan_fav`
--

INSERT INTO `qinggan_fav` (`id`, `user_id`, `thumb`, `title`, `note`, `addtime`, `lid`) VALUES
(4, 23, '', 'EverEdit - 值得关注的代码编辑器', 'Everedit 结合众多编辑器的特点开发出的兼顾性能和使用、小巧的、强悍的文本编辑器。首先，要能够支持多种文档编码，显示和输入的时候不应该乱码。其次，要能够对于常见的代…', 1528103487, 1368),
(5, 23, '', '金山 WPS - 免费正版办公软件(支持Win/Linux/手机)', '一直以来办公软件市场份额都是被微软的 Office 牢牢占据，但其价格较贵，国内大多都是用着盗版。其实国产免费的 WPS 已经完完全全可以和Office媲美，且完美兼容各种doc、do…', 1528103808, 1369);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_fields`
--

CREATE TABLE `qinggan_fields` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '字段ID，自增',
  `ftype` varchar(255) NOT NULL COMMENT '模型ID，当为数字时表示模块ID，非数表示其他模型的ID',
  `title` varchar(255) NOT NULL COMMENT '字段名称',
  `identifier` varchar(50) NOT NULL COMMENT '字段标识串',
  `field_type` varchar(255) NOT NULL DEFAULT '200' COMMENT '字段存储类型',
  `note` varchar(255) NOT NULL COMMENT '字段内容备注',
  `form_type` varchar(100) NOT NULL COMMENT '表单类型',
  `form_style` varchar(255) NOT NULL COMMENT '表单CSS',
  `format` varchar(100) NOT NULL COMMENT '格式化方式',
  `content` varchar(255) NOT NULL COMMENT '默认值',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序',
  `ext` text NOT NULL COMMENT '扩展内容',
  `is_front` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0前端不可用1前端可用',
  `search` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不支持搜索1完全匹配搜索2模糊匹配搜索3区间搜索',
  `search_separator` varchar(10) NOT NULL COMMENT '分割符，仅限区间搜索时有效',
  `form_class` varchar(255) NOT NULL COMMENT '自定义表单Class'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='字段管理器';

--
-- 转存表中的数据 `qinggan_fields`
--

INSERT INTO `qinggan_fields` (`id`, `ftype`, `title`, `identifier`, `field_type`, `note`, `form_type`, `form_style`, `format`, `content`, `taxis`, `ext`, `is_front`, `search`, `search_separator`, `form_class`) VALUES
(82, '22', '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 10, 'a:8:{s:7:\"cate_id\";s:0:\"\";s:11:\"cate_custom\";s:1:\"0\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_type\";s:11:\"png,jpg,gif\";s:11:\"upload_name\";s:6:\"图片\";s:13:\"upload_binary\";s:1:\"0\";s:15:\"upload_compress\";s:1:\"0\";s:18:\"upload_compress_wh\";s:3:\"500\";}', 0, 0, '', ''),
(83, '22', '内容', 'content', 'longtext', '', 'editor', '', 'html_js', '', 30, 'a:8:{s:5:\"width\";s:3:\"950\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:5:\"etype\";s:4:\"full\";s:7:\"inc_tag\";s:1:\"1\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:9:{s:5:\"image\";s:1:\"1\";s:4:\"info\";s:1:\"1\";s:5:\"video\";s:1:\"1\";s:4:\"file\";s:1:\"1\";s:4:\"page\";s:1:\"1\";s:10:\"insertcode\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";s:10:\"fontfamily\";s:1:\"1\";}}', 0, 0, '', ''),
(84, '23', '链接', 'link', 'longtext', '设置导航链接', 'url', '', 'safe', '', 90, 'a:1:{s:5:\"width\";s:3:\"500\";}', 0, 0, '', ''),
(85, '23', '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:1:{s:11:\"option_list\";s:5:\"opt:6\";}', 0, 0, '', ''),
(88, '24', '图片', 'pictures', 'varchar', '设置产品的图片，支持多图，上传规格为500x500', 'upload', '', 'safe', '', 50, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"1\";s:11:\"upload_auto\";s:1:\"1\";}', 0, 0, '', ''),
(92, '21', '链接', 'link', 'longtext', '', 'text', '', 'safe', '', 90, 'a:2:{s:8:\"form_btn\";s:3:\"url\";s:5:\"width\";s:3:\"500\";}', 0, 0, '', ''),
(93, '21', '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_blank', 100, 'a:3:{s:11:\"option_list\";s:5:\"opt:6\";s:9:\"put_order\";s:1:\"0\";s:10:\"ext_select\";b:0;}', 0, 0, '', ''),
(131, '40', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:7:{s:5:\"width\";s:3:\"800\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:6:{s:5:\"image\";s:1:\"1\";s:4:\"info\";s:1:\"1\";s:10:\"insertcode\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";s:10:\"fontfamily\";s:1:\"1\";}}', 0, 0, '', ''),
(141, '46', '姓名', 'fullname', 'varchar', '', 'text', '', 'safe', '', 10, 'a:5:{s:8:\"form_btn\";s:0:\"\";s:10:\"ext_format\";s:0:\"\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";s:13:\"ext_include_3\";s:1:\"0\";}', 1, 0, '', ''),
(142, '46', '邮箱', 'email', 'varchar', '', 'text', '', 'safe', '', 130, 'a:5:{s:8:\"form_btn\";s:0:\"\";s:10:\"ext_format\";s:0:\"\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";s:13:\"ext_include_3\";s:1:\"0\";}', 1, 0, '', ''),
(143, '46', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 200, 'a:7:{s:6:\"height\";s:3:\"180\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:1:\"1\";s:4:\"btns\";s:0:\"\";s:8:\"is_float\";s:0:\"\";}', 1, 0, '', ''),
(144, '46', '管理员回复', 'adm_reply', 'longtext', '', 'editor', '', 'html_js', '', 255, 'a:7:{s:5:\"width\";s:3:\"800\";s:6:\"height\";s:3:\"100\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:3:{s:5:\"image\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";}}', 0, 0, '', ''),
(177, '22', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:\"width\";s:3:\"800\";s:6:\"height\";s:2:\"80\";}', 0, 2, '', ''),
(200, '21', '图片', 'pic', 'varchar', '统一宽度为980，高度自定义，建议统一高度300', 'upload', '', 'safe', '', 20, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_auto\";s:1:\"1\";}', 0, 0, '', ''),
(203, '61', '链接', 'link', 'longtext', '填写链接要求带上http://', 'text', '', 'safe', '', 90, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"280\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(204, '61', '链接方式', 'target', 'varchar', '设置是否在新窗口打开', 'radio', '', 'safe', '_self', 100, 'a:3:{s:11:\"option_list\";s:5:\"opt:6\";s:9:\"put_order\";s:1:\"0\";s:10:\"ext_select\";s:0:\"\";}', 0, 0, '', ''),
(221, '65', '摘要', 'note', 'longtext', '简要描述下载信息', 'textarea', '', 'safe', '', 120, 'a:2:{s:5:\"width\";s:3:\"600\";s:6:\"height\";s:2:\"80\";}', 0, 0, '', ''),
(222, '65', '文件大小', 'fsize', 'varchar', '设置文件大小，注意填写相应的单位，如KB，MB', 'text', '', 'safe', '', 10, 'a:2:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"300\";}', 0, 0, '', ''),
(224, '65', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:7:{s:5:\"width\";s:3:\"800\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:3:{s:5:\"image\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";}}', 0, 0, '', ''),
(226, '65', '版本', 'version', 'varchar', '设置软件版本号', 'text', '', 'safe', '', 15, 'a:2:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"100\";}', 0, 0, '', ''),
(227, '65', '官方网站', 'website', 'varchar', '请输入软件官方网址，没有请留空，需要加 http:// 或 https://', 'text', '', 'safe', '', 30, 'a:5:{s:8:\"form_btn\";s:0:\"\";s:10:\"ext_format\";s:0:\"\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";s:13:\"ext_include_3\";s:1:\"0\";}', 0, 0, '', ''),
(230, '65', '开发商', 'author', 'varchar', '设置开发商名称', 'text', '', 'safe', '', 20, 'a:4:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";b:0;s:14:\"ext_quick_type\";b:0;}', 0, 0, '', ''),
(231, '65', '缩略图', 'thumb', 'varchar', '设置附件缩略图，宽高为420x420', 'upload', '', 'safe', '', 110, 'a:8:{s:7:\"cate_id\";s:1:\"1\";s:11:\"cate_custom\";s:1:\"0\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_type\";s:11:\"png,jpg,gif\";s:11:\"upload_name\";s:6:\"图片\";s:13:\"upload_binary\";s:1:\"1\";s:15:\"upload_compress\";s:1:\"0\";s:18:\"upload_compress_wh\";s:3:\"500\";}', 0, 0, '', ''),
(233, '66', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:7:{s:5:\"width\";s:3:\"800\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:4:{s:5:\"image\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";s:10:\"fontfamily\";s:1:\"1\";}}', 1, 0, '', ''),
(234, '66', '置顶', 'toplevel', 'varchar', '', 'radio', '', 'int', '', 10, 'a:3:{s:11:\"option_list\";s:6:\"opt:12\";s:9:\"put_order\";s:1:\"0\";s:10:\"ext_select\";b:0;}', 0, 0, '', ''),
(238, '66', '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_auto\";s:1:\"1\";}', 1, 0, '', ''),
(239, '68', '缩略图', 'thumb', 'varchar', '请上传300x300规格的图片，文件大小建议不超过100KB', 'upload', '', 'safe', '', 30, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_auto\";s:1:\"1\";}', 0, 0, '', ''),
(240, '68', '图片', 'pictures', 'varchar', '支持多图，建议上传500x500或600x600规格的图片', 'upload', '', 'safe', '', 50, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"1\";s:11:\"upload_auto\";s:1:\"1\";}', 0, 0, '', ''),
(244, '61', '联系人电话', 'tel', 'varchar', '填写联系人电话', 'text', '', 'safe', '', 110, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"280\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(248, '69', '产品多属性', 'attrs', 'longtext', '', 'param', '', 'safe', '', 20, 'a:3:{s:6:\"p_name\";s:0:\"\";s:6:\"p_type\";s:1:\"1\";s:7:\"p_width\";s:0:\"\";}', 0, 0, '', ''),
(267, '68', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:7:{s:5:\"width\";s:3:\"950\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:3:{s:5:\"image\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";}}', 0, 0, '', ''),
(268, '65', '附件', 'dfile', 'varchar', '请上传文件，启用此项，附件链接失效，适用于小文件', 'upload', '', 'safe', '', 60, 'a:8:{s:7:\"cate_id\";s:2:\"11\";s:11:\"cate_custom\";s:1:\"0\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_type\";s:7:\"rar,zip\";s:11:\"upload_name\";s:12:\"压缩软件\";s:13:\"upload_binary\";s:1:\"0\";s:15:\"upload_compress\";s:1:\"0\";s:18:\"upload_compress_wh\";s:3:\"500\";}', 0, 0, '', ''),
(269, '46', '图片', 'pic', 'varchar', '', 'upload', '', 'safe', '', 180, 'a:8:{s:7:\"cate_id\";s:1:\"1\";s:11:\"cate_custom\";s:1:\"0\";s:11:\"is_multiple\";s:1:\"1\";s:11:\"upload_type\";s:11:\"png,jpg,gif\";s:11:\"upload_name\";s:6:\"图片\";s:13:\"upload_binary\";s:1:\"0\";s:15:\"upload_compress\";s:1:\"1\";s:18:\"upload_compress_wh\";s:3:\"500\";}', 1, 0, '', ''),
(270, '64', '客服QQ', 'qq', 'varchar', '', 'text', '', 'safe', '', 150, 'a:2:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"300\";}', 0, 0, '', ''),
(288, '24', '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 20, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_auto\";s:1:\"1\";}', 0, 0, '', ''),
(293, '24', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:8:{s:5:\"width\";s:3:\"950\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:5:\"etype\";s:4:\"full\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";s:4:\"btns\";a:4:{s:5:\"image\";s:1:\"1\";s:9:\"paragraph\";s:1:\"1\";s:8:\"fontsize\";s:1:\"1\";s:10:\"fontfamily\";s:1:\"1\";}}', 0, 0, '', ''),
(294, '24', '手机版标题', 'm_title', 'varchar', '标题较短，请根据实际情况使用', 'text', '', 'safe', '', 10, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(296, '74', '会员账号', 'account', 'varchar', '验证会员模块的账号', 'text', '', 'safe', '', 10, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(297, '75', '姓名', 'fullname', 'varchar', '请填写汇款人的姓名', 'text', '', 'safe', '', 10, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(298, '75', '手机号', 'mobile', 'varchar', '请填写汇款人的手机号', 'text', '', 'safe', '', 20, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(299, '75', '汇款金额', 'bankprice', 'varchar', '请填写您汇款的金额，建议多汇几分，以示区别', 'text', '', 'safe', '', 30, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(300, '75', '摘要', 'note', 'longtext', '填写您的备注或汇款银行上的备注信息', 'textarea', '', 'safe', '', 40, 'a:2:{s:5:\"width\";s:3:\"600\";s:6:\"height\";s:2:\"80\";}', 1, 0, '', ''),
(301, '75', '汇款银行', 'bankname', 'varchar', '请填写您汇款的银行', 'text', '', 'safe', '', 5, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 1, 0, '', ''),
(389, '65', '附件链接', 'dlink', 'varchar', '请填写外部附件链接，注意必须附件为空此项才有效', 'text', '', 'safe', '', 65, 'a:5:{s:8:\"form_btn\";s:0:\"\";s:10:\"ext_format\";s:0:\"\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";s:13:\"ext_include_3\";s:1:\"0\";}', 0, 0, '', ''),
(390, '65', '限制会员可下载', 'onlyuser', 'int', '', 'radio', '', 'safe', '0', 70, 'a:3:{s:11:\"option_list\";s:5:\"opt:4\";s:9:\"put_order\";s:1:\"0\";s:10:\"ext_select\";s:0:\"\";}', 0, 0, '', ''),
(836, 'all-4', '内容', 'content', 'longtext', '', 'code_editor', '', 'html_js', '', 90, 'a:2:{s:5:\"width\";s:3:\"700\";s:6:\"height\";s:3:\"200\";}', 0, 0, '', ''),
(837, 'project-43', '英文标题En-Title', 'entitle', 'varchar', '', 'text', '', 'safe', '', 10, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(838, 'project-43', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:6:{s:7:\"cate_id\";s:1:\"1\";s:11:\"cate_custom\";s:1:\"0\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_type\";s:11:\"png,jpg,gif\";s:11:\"upload_name\";s:6:\"图片\";s:13:\"upload_binary\";s:1:\"0\";}', 0, 0, '', ''),
(839, 'project-148', '二维码图片', 'barcode', 'varchar', '请上传相应的二维码图片', 'upload', '', '', '', 255, 'a:3:{s:11:\"upload_type\";s:7:\"picture\";s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";}', 0, 0, '', ''),
(840, 'project-87', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:\"upload_type\";s:7:\"picture\";s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";}', 0, 0, '', ''),
(841, 'project-45', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:\"upload_type\";s:7:\"picture\";s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";}', 0, 0, '', ''),
(842, 'project-150', '内容', 'content', 'longtext', '', 'editor', '', 'html', '', 255, 'a:12:{s:5:\"width\";s:3:\"950\";s:6:\"height\";s:3:\"360\";s:7:\"is_code\";s:0:\"\";s:9:\"btn_image\";s:1:\"1\";s:9:\"btn_video\";s:1:\"1\";s:8:\"btn_file\";s:1:\"1\";s:8:\"btn_page\";s:0:\"\";s:8:\"btn_info\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:5:\"etype\";s:4:\"full\";s:7:\"btn_map\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";}', 0, 0, '', ''),
(843, 'all-37', '百度分享代码', 'baidu', 'longtext', '', 'code_editor', '', 'html_js', '', 10, 'a:2:{s:5:\"width\";s:3:\"800\";s:6:\"height\";s:3:\"300\";}', 0, 0, '', ''),
(844, 'project-96', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:\"upload_type\";s:7:\"picture\";s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";}', 0, 0, '', ''),
(845, 'project-151', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:\"upload_type\";s:7:\"picture\";s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";}', 0, 0, '', ''),
(846, 'cate-204', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:\"width\";s:3:\"600\";s:6:\"height\";s:2:\"80\";}', 0, 0, '', ''),
(847, 'project-152', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 40, 'a:3:{s:11:\"upload_type\";s:7:\"picture\";s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";}', 0, 0, '', ''),
(848, 'cate-205', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:\"width\";s:3:\"600\";s:6:\"height\";s:2:\"80\";}', 0, 0, '', ''),
(849, 'cate-206', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:\"width\";s:3:\"600\";s:6:\"height\";s:2:\"80\";}', 0, 0, '', ''),
(850, 'project-144', '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";b:0;s:14:\"ext_quick_type\";b:0;}', 0, 0, '', ''),
(851, 'project-142', '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";b:0;s:14:\"ext_quick_type\";b:0;}', 0, 0, '', ''),
(852, 'cate-207', '摘要', 'note', 'longtext', '简要文字描述', 'textarea', '', 'safe', '', 20, 'a:2:{s:5:\"width\";s:3:\"600\";s:6:\"height\";s:2:\"80\";}', 0, 0, '', ''),
(853, 'cate-70', '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";b:0;s:14:\"ext_quick_type\";b:0;}', 0, 0, '', ''),
(854, 'project-151', '英文标题', 'entitle', 'varchar', '设置与主题名称相对应的英文标题', 'text', '', 'safe', '', 255, 'a:4:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";b:0;s:14:\"ext_quick_type\";b:0;}', 0, 0, '', ''),
(855, 'list-1757', '企业名称', 'company', 'varchar', '', 'text', '', 'safe', '', 255, 'a:2:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"500\";}', 0, 0, '', ''),
(856, 'list-1757', '公司地址', 'address', 'varchar', '请填写您的办公地址', 'text', '', 'safe', '', 79, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(857, 'list-1757', 'Email', 'email', 'varchar', '请填写联系邮箱，用户方便客户与您取得联系', 'text', '', 'safe', '', 50, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:0:\"\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(858, 'list-1757', '客服电话', 'tel', 'varchar', '请填写客服电话，建议填写400号', 'text', '', 'safe', '', 20, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(859, 'list-1757', '联系人', 'fullname', 'varchar', '', 'text', '', 'safe', '', 10, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"300\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(860, 'list-1756', '图片', 'pic', 'varchar', '此图片仅在首页调用中显示，限制宽高为120x150', 'text', '', 'safe', '', 10, 'a:4:{s:8:\"form_btn\";s:5:\"image\";s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(861, 'list-1756', '摘要', 'note', 'longtext', '支持HTML，仅在首页显示，请注意长度', 'editor', '', 'html', '', 20, 'a:13:{s:5:\"width\";s:0:\"\";s:6:\"height\";s:3:\"100\";s:7:\"is_code\";s:0:\"\";s:9:\"btn_image\";s:0:\"\";s:9:\"btn_video\";s:0:\"\";s:8:\"btn_file\";s:0:\"\";s:8:\"btn_page\";s:0:\"\";s:8:\"btn_info\";s:0:\"\";s:7:\"is_read\";s:0:\"\";s:5:\"etype\";s:6:\"simple\";s:7:\"btn_map\";s:0:\"\";s:7:\"inc_tag\";s:0:\"\";s:10:\"paste_text\";s:0:\"\";}', 0, 0, '', ''),
(862, 'list-1757', '邮编', 'zipcode', 'varchar', '请填写六位数字的邮编号码', 'text', '', 'safe', '', 30, 'a:2:{s:8:\"form_btn\";b:0;s:5:\"width\";s:3:\"300\";}', 0, 0, '', ''),
(863, 'cate-582', '缩略图', 'thumb', 'varchar', '', 'upload', '', 'safe', '', 30, 'a:3:{s:7:\"cate_id\";s:1:\"1\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_auto\";s:1:\"1\";}', 0, 0, '', ''),
(864, 'cate-598', '联系地址', 'address', 'varchar', '', 'text', '', 'safe', '', 0, '', 0, 0, '', ''),
(865, 'cate-598', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 0, '', 0, 0, '', ''),
(866, 'cate-599', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 0, '', 0, 0, '', ''),
(869, 'project-144', '通栏图片', 'banner', 'varchar', '', 'upload', '', 'safe', '', 0, 'a:8:{s:7:\"cate_id\";s:1:\"1\";s:11:\"cate_custom\";s:1:\"0\";s:11:\"is_multiple\";s:1:\"0\";s:11:\"upload_type\";s:11:\"png,jpg,gif\";s:11:\"upload_name\";s:6:\"图片\";s:13:\"upload_binary\";s:1:\"0\";s:15:\"upload_compress\";s:1:\"0\";s:18:\"upload_compress_wh\";s:3:\"500\";}', 0, 0, '', ''),
(870, 'user', '姓名', 'fullname', 'varchar', '', 'text', '', 'safe', '', 10, 'a:5:{s:8:\"form_btn\";s:0:\"\";s:10:\"ext_format\";s:0:\"\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";s:13:\"ext_include_3\";s:1:\"0\";}', 1, 0, '', ''),
(871, 'user', '性别', 'gender', 'varchar', '', 'radio', '', 'int', '0', 15, 'a:3:{s:11:\"option_list\";s:5:\"opt:1\";s:9:\"put_order\";s:1:\"0\";s:10:\"ext_select\";s:0:\"\";}', 1, 0, '', ''),
(872, 'user', '联系地址', 'address', 'varchar', '', 'text', '', 'safe', '', 20, 'a:4:{s:8:\"form_btn\";s:0:\"\";s:5:\"width\";s:3:\"500\";s:15:\"ext_quick_words\";s:0:\"\";s:14:\"ext_quick_type\";s:0:\"\";}', 0, 0, '', ''),
(873, '40', '测试链接', 'demo', 'varchar', '', 'radio', '', 'safe', '', 5, 'a:3:{s:11:\"option_list\";s:0:\"\";s:9:\"put_order\";s:1:\"0\";s:10:\"ext_select\";s:13:\"链接\n事件\";}', 0, 0, '', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_freight`
--

CREATE TABLE `qinggan_freight` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '运费模板ID，自增ID',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `title` varchar(100) NOT NULL COMMENT '模板名称，便于后台管理',
  `type` enum('weight','volume','number','fixed') NOT NULL DEFAULT 'weight' COMMENT 'weight重量volume体积number数量fixed固定值',
  `currency_id` int(11) NOT NULL DEFAULT '0' COMMENT '货币ID',
  `taxis` int(11) NOT NULL DEFAULT '0' COMMENT '排序'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='物流运费模板管理';

--
-- 转存表中的数据 `qinggan_freight`
--

INSERT INTO `qinggan_freight` (`id`, `site_id`, `title`, `type`, `currency_id`, `taxis`) VALUES
(1, 1, '计重运费模板', 'weight', 1, 10),
(2, 1, '体积运费模板', 'volume', 1, 20),
(3, 1, '基于数量的运费模板', 'number', 1, 30),
(4, 1, '固定运费模板', 'fixed', 1, 40);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_freight_price`
--

CREATE TABLE `qinggan_freight_price` (
  `zid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '区域ID',
  `unit_val` varchar(20) NOT NULL COMMENT '单位量，如0.5kg，或1个或1立方米，取决于系统设定',
  `price` varchar(50) NOT NULL DEFAULT '0' COMMENT '运费价格，最低为0，不能为负数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='单位体积价格';

--
-- 转存表中的数据 `qinggan_freight_price`
--

INSERT INTO `qinggan_freight_price` (`zid`, `unit_val`, `price`) VALUES
(1, '1', '10'),
(1, '2', '17'),
(1, '3', '7*N'),
(2, '1', '10'),
(2, '2', '17'),
(2, '3', '8*N'),
(3, '1', '10'),
(3, '2', '24'),
(3, '3', '10*N'),
(4, '1', '10'),
(4, '2', '24'),
(4, '3', '10*N'),
(5, '1', '11'),
(5, '2', '22'),
(5, '3', '10*N'),
(6, '1', '15'),
(6, '2', '27'),
(6, '3', '10*N'),
(7, '1', '11'),
(7, '2', '22'),
(7, '3', '10*N'),
(8, '1', '30'),
(8, '2', '30'),
(8, '3', '10*N'),
(10, '0.5', '10'),
(10, '1', '12*N'),
(10, '1.5', '13*N'),
(11, '0.5', '20'),
(11, '1', '35'),
(11, '1.5', '45'),
(12, 'fixed', '10'),
(24, 'fixed', '20');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_freight_zone`
--

CREATE TABLE `qinggan_freight_zone` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `fid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '运费模板ID',
  `title` varchar(100) NOT NULL COMMENT '名称',
  `taxis` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序',
  `note` varchar(255) NOT NULL COMMENT '简单说明该区域信息',
  `area` longtext NOT NULL COMMENT '省份+城市'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='运费模板区域设置';

--
-- 转存表中的数据 `qinggan_freight_zone`
--

INSERT INTO `qinggan_freight_zone` (`id`, `fid`, `title`, `taxis`, `note`, `area`) VALUES
(1, 3, '华东', 10, '包括省市有上海，江苏，浙江，安徽，江西', 'a:5:{s:9:\"上海市\";a:1:{s:9:\"上海市\";b:1;}s:9:\"江苏省\";a:13:{s:9:\"南京市\";b:1;s:9:\"无锡市\";b:1;s:9:\"徐州市\";b:1;s:9:\"常州市\";b:1;s:9:\"苏州市\";b:1;s:9:\"南通市\";b:1;s:12:\"连云港市\";b:1;s:9:\"淮安市\";b:1;s:9:\"盐城市\";b:1;s:9:\"扬州市\";b:1;s:9:\"镇江市\";b:1;s:9:\"泰州市\";b:1;s:9:\"宿迁市\";b:1;}s:9:\"浙江省\";a:11:{s:9:\"杭州市\";b:1;s:9:\"宁波市\";b:1;s:9:\"温州市\";b:1;s:9:\"嘉兴市\";b:1;s:9:\"湖州市\";b:1;s:9:\"绍兴市\";b:1;s:9:\"金华市\";b:1;s:9:\"衢州市\";b:1;s:9:\"舟山市\";b:1;s:9:\"台州市\";b:1;s:9:\"丽水市\";b:1;}s:9:\"安徽省\";a:17:{s:9:\"合肥市\";b:1;s:9:\"芜湖市\";b:1;s:9:\"蚌埠市\";b:1;s:9:\"淮南市\";b:1;s:12:\"马鞍山市\";b:1;s:9:\"淮北市\";b:1;s:9:\"铜陵市\";b:1;s:9:\"安庆市\";b:1;s:9:\"黄山市\";b:1;s:9:\"滁州市\";b:1;s:9:\"阜阳市\";b:1;s:9:\"宿州市\";b:1;s:9:\"巢湖市\";b:1;s:9:\"六安市\";b:1;s:9:\"亳州市\";b:1;s:9:\"池州市\";b:1;s:9:\"宣城市\";b:1;}s:9:\"江西省\";a:11:{s:9:\"南昌市\";b:1;s:12:\"景德镇市\";b:1;s:9:\"萍乡市\";b:1;s:9:\"九江市\";b:1;s:9:\"新余市\";b:1;s:9:\"鹰潭市\";b:1;s:9:\"赣州市\";b:1;s:9:\"吉安市\";b:1;s:9:\"宜春市\";b:1;s:9:\"抚州市\";b:1;s:9:\"上饶市\";b:1;}}'),
(2, 3, '华北', 20, '包含北京，天津，山西，山东，河北，内蒙古', 'a:6:{s:9:\"北京市\";a:1:{s:9:\"北京市\";b:1;}s:9:\"天津市\";a:1:{s:9:\"天津市\";b:1;}s:9:\"河北省\";a:11:{s:12:\"石家庄市\";b:1;s:9:\"唐山市\";b:1;s:12:\"秦皇岛市\";b:1;s:9:\"邯郸市\";b:1;s:9:\"邢台市\";b:1;s:9:\"保定市\";b:1;s:12:\"张家口市\";b:1;s:9:\"承德市\";b:1;s:9:\"沧州市\";b:1;s:9:\"廊坊市\";b:1;s:9:\"衡水市\";b:1;}s:9:\"山西省\";a:11:{s:9:\"太原市\";b:1;s:9:\"大同市\";b:1;s:9:\"阳泉市\";b:1;s:9:\"长治市\";b:1;s:9:\"晋城市\";b:1;s:9:\"朔州市\";b:1;s:9:\"晋中市\";b:1;s:9:\"运城市\";b:1;s:9:\"忻州市\";b:1;s:9:\"临汾市\";b:1;s:9:\"吕梁市\";b:1;}s:18:\"内蒙古自治区\";a:12:{s:15:\"呼和浩特市\";b:1;s:9:\"包头市\";b:1;s:9:\"乌海市\";b:1;s:9:\"赤峰市\";b:1;s:9:\"通辽市\";b:1;s:15:\"鄂尔多斯市\";b:1;s:15:\"呼伦贝尔市\";b:1;s:15:\"巴彦淖尔市\";b:1;s:15:\"乌兰察布市\";b:1;s:9:\"兴安盟\";b:1;s:15:\"锡林郭勒盟\";b:1;s:12:\"阿拉善盟\";b:1;}s:9:\"山东省\";a:17:{s:9:\"济南市\";b:1;s:9:\"青岛市\";b:1;s:9:\"淄博市\";b:1;s:9:\"枣庄市\";b:1;s:9:\"东营市\";b:1;s:9:\"烟台市\";b:1;s:9:\"潍坊市\";b:1;s:9:\"济宁市\";b:1;s:9:\"泰安市\";b:1;s:9:\"威海市\";b:1;s:9:\"日照市\";b:1;s:9:\"莱芜市\";b:1;s:9:\"临沂市\";b:1;s:9:\"德州市\";b:1;s:9:\"聊城市\";b:1;s:9:\"滨州市\";b:1;s:9:\"荷泽市\";b:1;}}'),
(3, 3, '华中', 30, '包括湖南，湖北，河南', 'a:3:{s:9:\"河南省\";a:17:{s:9:\"郑州市\";b:1;s:9:\"开封市\";b:1;s:9:\"洛阳市\";b:1;s:12:\"平顶山市\";b:1;s:9:\"安阳市\";b:1;s:9:\"鹤壁市\";b:1;s:9:\"新乡市\";b:1;s:9:\"焦作市\";b:1;s:9:\"濮阳市\";b:1;s:9:\"许昌市\";b:1;s:9:\"漯河市\";b:1;s:12:\"三门峡市\";b:1;s:9:\"南阳市\";b:1;s:9:\"商丘市\";b:1;s:9:\"信阳市\";b:1;s:9:\"周口市\";b:1;s:12:\"驻马店市\";b:1;}s:9:\"湖北省\";a:14:{s:9:\"武汉市\";b:1;s:9:\"黄石市\";b:1;s:9:\"十堰市\";b:1;s:9:\"宜昌市\";b:1;s:9:\"襄樊市\";b:1;s:9:\"鄂州市\";b:1;s:9:\"荆门市\";b:1;s:9:\"孝感市\";b:1;s:9:\"荆州市\";b:1;s:9:\"黄冈市\";b:1;s:9:\"咸宁市\";b:1;s:9:\"随州市\";b:1;s:30:\"恩施土家族苗族自治州\";b:1;s:9:\"神农架\";b:1;}s:9:\"湖南省\";a:14:{s:9:\"长沙市\";b:1;s:9:\"株洲市\";b:1;s:9:\"湘潭市\";b:1;s:9:\"衡阳市\";b:1;s:9:\"邵阳市\";b:1;s:9:\"岳阳市\";b:1;s:9:\"常德市\";b:1;s:12:\"张家界市\";b:1;s:9:\"益阳市\";b:1;s:9:\"郴州市\";b:1;s:9:\"永州市\";b:1;s:9:\"怀化市\";b:1;s:9:\"娄底市\";b:1;s:30:\"湘西土家族苗族自治州\";b:1;}}'),
(4, 3, '华南', 40, '包括广东，广西，福建，海南', 'a:4:{s:9:\"福建省\";a:9:{s:9:\"福州市\";b:1;s:9:\"厦门市\";b:1;s:9:\"莆田市\";b:1;s:9:\"三明市\";b:1;s:9:\"泉州市\";b:1;s:9:\"漳州市\";b:1;s:9:\"南平市\";b:1;s:9:\"龙岩市\";b:1;s:9:\"宁德市\";b:1;}s:9:\"广东省\";a:21:{s:9:\"广州市\";b:1;s:9:\"韶关市\";b:1;s:9:\"深圳市\";b:1;s:9:\"珠海市\";b:1;s:9:\"汕头市\";b:1;s:9:\"佛山市\";b:1;s:9:\"江门市\";b:1;s:9:\"湛江市\";b:1;s:9:\"茂名市\";b:1;s:9:\"肇庆市\";b:1;s:9:\"惠州市\";b:1;s:9:\"梅州市\";b:1;s:9:\"汕尾市\";b:1;s:9:\"河源市\";b:1;s:9:\"阳江市\";b:1;s:9:\"清远市\";b:1;s:9:\"东莞市\";b:1;s:9:\"中山市\";b:1;s:9:\"潮州市\";b:1;s:9:\"揭阳市\";b:1;s:9:\"云浮市\";b:1;}s:21:\"广西壮族自治区\";a:14:{s:9:\"南宁市\";b:1;s:9:\"柳州市\";b:1;s:9:\"桂林市\";b:1;s:9:\"梧州市\";b:1;s:9:\"北海市\";b:1;s:12:\"防城港市\";b:1;s:9:\"钦州市\";b:1;s:9:\"贵港市\";b:1;s:9:\"玉林市\";b:1;s:9:\"百色市\";b:1;s:9:\"贺州市\";b:1;s:9:\"河池市\";b:1;s:9:\"来宾市\";b:1;s:9:\"崇左市\";b:1;}s:9:\"海南省\";a:2:{s:9:\"海口市\";b:1;s:9:\"三亚市\";b:1;}}'),
(5, 3, '东北', 50, '包括辽宁，吉林，黑龙江', 'a:3:{s:9:\"辽宁省\";a:14:{s:9:\"沈阳市\";b:1;s:9:\"大连市\";b:1;s:9:\"鞍山市\";b:1;s:9:\"抚顺市\";b:1;s:9:\"本溪市\";b:1;s:9:\"丹东市\";b:1;s:9:\"锦州市\";b:1;s:9:\"营口市\";b:1;s:9:\"阜新市\";b:1;s:9:\"辽阳市\";b:1;s:9:\"盘锦市\";b:1;s:9:\"铁岭市\";b:1;s:9:\"朝阳市\";b:1;s:12:\"葫芦岛市\";b:1;}s:9:\"吉林省\";a:9:{s:9:\"长春市\";b:1;s:9:\"吉林市\";b:1;s:9:\"四平市\";b:1;s:9:\"辽源市\";b:1;s:9:\"通化市\";b:1;s:9:\"白山市\";b:1;s:9:\"松原市\";b:1;s:9:\"白城市\";b:1;s:24:\"延边朝鲜族自治州\";b:1;}s:12:\"黑龙江省\";a:13:{s:12:\"哈尔滨市\";b:1;s:15:\"齐齐哈尔市\";b:1;s:9:\"鸡西市\";b:1;s:9:\"鹤岗市\";b:1;s:12:\"双鸭山市\";b:1;s:9:\"大庆市\";b:1;s:9:\"伊春市\";b:1;s:12:\"佳木斯市\";b:1;s:12:\"七台河市\";b:1;s:12:\"牡丹江市\";b:1;s:9:\"黑河市\";b:1;s:9:\"绥化市\";b:1;s:18:\"大兴安岭地区\";b:1;}}'),
(6, 3, '西北', 60, '包括陕西，甘肃，宁夏，青海，新疆', 'a:5:{s:9:\"陕西省\";a:10:{s:9:\"西安市\";b:1;s:9:\"铜川市\";b:1;s:9:\"宝鸡市\";b:1;s:9:\"咸阳市\";b:1;s:9:\"渭南市\";b:1;s:9:\"延安市\";b:1;s:9:\"汉中市\";b:1;s:9:\"榆林市\";b:1;s:9:\"安康市\";b:1;s:9:\"商洛市\";b:1;}s:9:\"甘肃省\";a:14:{s:9:\"兰州市\";b:1;s:12:\"嘉峪关市\";b:1;s:9:\"金昌市\";b:1;s:9:\"白银市\";b:1;s:9:\"天水市\";b:1;s:9:\"武威市\";b:1;s:9:\"张掖市\";b:1;s:9:\"平凉市\";b:1;s:9:\"酒泉市\";b:1;s:9:\"庆阳市\";b:1;s:9:\"定西市\";b:1;s:9:\"陇南市\";b:1;s:21:\"临夏回族自治州\";b:1;s:21:\"甘南藏族自治州\";b:1;}s:9:\"青海省\";a:8:{s:9:\"西宁市\";b:1;s:12:\"海东地区\";b:1;s:21:\"海北藏族自治州\";b:1;s:21:\"黄南藏族自治州\";b:1;s:21:\"海南藏族自治州\";b:1;s:21:\"果洛藏族自治州\";b:1;s:21:\"玉树藏族自治州\";b:1;s:30:\"海西蒙古族藏族自治州\";b:1;}s:21:\"宁夏回族自治区\";a:5:{s:9:\"银川市\";b:1;s:12:\"石嘴山市\";b:1;s:9:\"吴忠市\";b:1;s:9:\"固原市\";b:1;s:9:\"中卫市\";b:1;}s:24:\"新疆维吾尔自治区\";a:18:{s:15:\"乌鲁木齐市\";b:1;s:15:\"克拉玛依市\";b:1;s:15:\"吐鲁番地区\";b:1;s:12:\"哈密地区\";b:1;s:21:\"昌吉回族自治州\";b:1;s:27:\"博尔塔拉蒙古自治州\";b:1;s:27:\"巴音郭楞蒙古自治州\";b:1;s:15:\"阿克苏地区\";b:1;s:33:\"克孜勒苏柯尔克孜自治州\";b:1;s:12:\"喀什地区\";b:1;s:12:\"和田地区\";b:1;s:24:\"伊犁哈萨克自治州\";b:1;s:12:\"塔城地区\";b:1;s:15:\"阿勒泰地区\";b:1;s:12:\"石河子市\";b:1;s:12:\"阿拉尔市\";b:1;s:15:\"图木舒克市\";b:1;s:12:\"五家渠市\";b:1;}}'),
(7, 3, '西南', 70, '包括重庆，云南，贵州，西藏，四川', 'a:5:{s:9:\"重庆市\";a:1:{s:9:\"重庆市\";b:1;}s:9:\"四川省\";a:21:{s:9:\"成都市\";b:1;s:9:\"自贡市\";b:1;s:12:\"攀枝花市\";b:1;s:9:\"泸州市\";b:1;s:9:\"德阳市\";b:1;s:9:\"绵阳市\";b:1;s:9:\"广元市\";b:1;s:9:\"遂宁市\";b:1;s:9:\"内江市\";b:1;s:9:\"乐山市\";b:1;s:9:\"南充市\";b:1;s:9:\"眉山市\";b:1;s:9:\"宜宾市\";b:1;s:9:\"广安市\";b:1;s:9:\"达州市\";b:1;s:9:\"雅安市\";b:1;s:9:\"巴中市\";b:1;s:9:\"资阳市\";b:1;s:27:\"阿坝藏族羌族自治州\";b:1;s:21:\"甘孜藏族自治州\";b:1;s:21:\"凉山彝族自治州\";b:1;}s:9:\"贵州省\";a:9:{s:9:\"贵阳市\";b:1;s:12:\"六盘水市\";b:1;s:9:\"遵义市\";b:1;s:9:\"安顺市\";b:1;s:12:\"铜仁地区\";b:1;s:33:\"黔西南布依族苗族自治州\";b:1;s:12:\"毕节地区\";b:1;s:30:\"黔东南苗族侗族自治州\";b:1;s:30:\"黔南布依族苗族自治州\";b:1;}s:9:\"云南省\";a:16:{s:9:\"昆明市\";b:1;s:9:\"曲靖市\";b:1;s:9:\"玉溪市\";b:1;s:9:\"保山市\";b:1;s:9:\"昭通市\";b:1;s:9:\"丽江市\";b:1;s:9:\"思茅市\";b:1;s:9:\"临沧市\";b:1;s:21:\"楚雄彝族自治州\";b:1;s:30:\"红河哈尼族彝族自治州\";b:1;s:27:\"文山壮族苗族自治州\";b:1;s:27:\"西双版纳傣族自治州\";b:1;s:21:\"大理白族自治州\";b:1;s:30:\"德宏傣族景颇族自治州\";b:1;s:24:\"怒江傈僳族自治州\";b:1;s:21:\"迪庆藏族自治州\";b:1;}s:15:\"西藏自治区\";a:7:{s:9:\"拉萨市\";b:1;s:12:\"昌都地区\";b:1;s:12:\"山南地区\";b:1;s:15:\"日喀则地区\";b:1;s:12:\"那曲地区\";b:1;s:12:\"阿里地区\";b:1;s:12:\"林芝地区\";b:1;}}'),
(8, 3, '港澳台', 80, '包括包港，澳门，台湾', 'a:3:{s:21:\"香港特别行政区\";a:1:{s:21:\"香港特别行政区\";b:1;}s:21:\"澳门特别行政区\";a:1:{s:21:\"澳门特别行政区\";b:1;}s:9:\"台湾省\";a:1:{s:9:\"台湾省\";b:1;}}'),
(10, 1, 'zoom1', 10, '广东深圳', 'a:1:{s:9:\"广东省\";a:1:{s:9:\"深圳市\";b:1;}}'),
(11, 1, 'zoom2', 20, '福建及广东', 'a:2:{s:9:\"福建省\";a:9:{s:9:\"福州市\";b:1;s:9:\"厦门市\";b:1;s:9:\"莆田市\";b:1;s:9:\"三明市\";b:1;s:9:\"泉州市\";b:1;s:9:\"漳州市\";b:1;s:9:\"南平市\";b:1;s:9:\"龙岩市\";b:1;s:9:\"宁德市\";b:1;}s:9:\"广东省\";a:20:{s:9:\"广州市\";b:1;s:9:\"韶关市\";b:1;s:9:\"珠海市\";b:1;s:9:\"汕头市\";b:1;s:9:\"佛山市\";b:1;s:9:\"江门市\";b:1;s:9:\"湛江市\";b:1;s:9:\"茂名市\";b:1;s:9:\"肇庆市\";b:1;s:9:\"惠州市\";b:1;s:9:\"梅州市\";b:1;s:9:\"汕尾市\";b:1;s:9:\"河源市\";b:1;s:9:\"阳江市\";b:1;s:9:\"清远市\";b:1;s:9:\"东莞市\";b:1;s:9:\"中山市\";b:1;s:9:\"潮州市\";b:1;s:9:\"揭阳市\";b:1;s:9:\"云浮市\";b:1;}}'),
(12, 4, '一线城市', 10, '', 'a:4:{s:9:\"北京市\";a:1:{s:9:\"北京市\";b:1;}s:9:\"天津市\";a:1:{s:9:\"天津市\";b:1;}s:9:\"上海市\";a:1:{s:9:\"上海市\";b:1;}s:9:\"广东省\";a:2:{s:9:\"广州市\";b:1;s:9:\"深圳市\";b:1;}}'),
(24, 4, '偏远地区', 20, '', 'a:1:{s:24:\"新疆维吾尔自治区\";a:18:{s:15:\"乌鲁木齐市\";b:1;s:15:\"克拉玛依市\";b:1;s:15:\"吐鲁番地区\";b:1;s:12:\"哈密地区\";b:1;s:21:\"昌吉回族自治州\";b:1;s:27:\"博尔塔拉蒙古自治州\";b:1;s:27:\"巴音郭楞蒙古自治州\";b:1;s:15:\"阿克苏地区\";b:1;s:33:\"克孜勒苏柯尔克孜自治州\";b:1;s:12:\"喀什地区\";b:1;s:12:\"和田地区\";b:1;s:24:\"伊犁哈萨克自治州\";b:1;s:12:\"塔城地区\";b:1;s:15:\"阿勒泰地区\";b:1;s:12:\"石河子市\";b:1;s:12:\"阿拉尔市\";b:1;s:15:\"图木舒克市\";b:1;s:12:\"五家渠市\";b:1;}}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_gateway`
--

CREATE TABLE `qinggan_gateway` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点ID，为0表示所有站点可用',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不可用1可用',
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1表示默认使用',
  `type` varchar(50) NOT NULL COMMENT '类型，gateway文件夹的子文件夹',
  `code` varchar(50) NOT NULL COMMENT '路由引挈',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序',
  `note` varchar(255) NOT NULL COMMENT '功能备注',
  `ext` text NOT NULL COMMENT '扩展参数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='第三方网关路由引挈';

--
-- 转存表中的数据 `qinggan_gateway`
--

INSERT INTO `qinggan_gateway` (`id`, `site_id`, `status`, `is_default`, `type`, `code`, `title`, `taxis`, `note`, `ext`) VALUES
(13, 1, 1, 1, 'sms', 'sendcloud', '短信引挈', 5, '', 'a:3:{s:8:\"api_user\";s:5:\"phpok\";s:7:\"api_key\";s:32:\"XVdRE302USCRNE9oxXg13cAcq37D5unF\";s:6:\"mobile\";s:11:\"15818533971\";}'),
(14, 1, 1, 1, 'email', 'sendcloud', '邮件', 5, '', 'a:5:{s:8:\"api_user\";s:8:\"phpokcom\";s:7:\"api_key\";s:16:\"gdMH23CczgNE51AU\";s:8:\"fullname\";s:12:\"锟铻科技\";s:5:\"email\";s:15:\"admin@phpok.org\";s:8:\"label_id\";s:6:\"118347\";}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_gd`
--

CREATE TABLE `qinggan_gd` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID号',
  `identifier` varchar(100) NOT NULL COMMENT '标识串',
  `width` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '图片宽度',
  `height` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '图片高度',
  `mark_picture` varchar(255) NOT NULL COMMENT '水印图片位置',
  `mark_position` varchar(100) NOT NULL COMMENT '水印位置',
  `cut_type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '图片生成方式，支持缩放法、裁剪法、等宽、等高及自定义五种，默认使用缩放法',
  `quality` tinyint(3) UNSIGNED NOT NULL DEFAULT '100' COMMENT '图片生成质量，默认是100',
  `bgcolor` varchar(10) NOT NULL DEFAULT 'FFFFFF' COMMENT '补白背景色，默认是白色',
  `trans` tinyint(3) UNSIGNED NOT NULL DEFAULT '65' COMMENT '透明度',
  `editor` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0普通1默认插入编辑器'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='上传图片自动生成方案';

--
-- 转存表中的数据 `qinggan_gd`
--

INSERT INTO `qinggan_gd` (`id`, `identifier`, `width`, `height`, `mark_picture`, `mark_position`, `cut_type`, `quality`, `bgcolor`, `trans`, `editor`) VALUES
(2, 'thumb', 320, 320, '', 'bottom-right', 1, 80, 'FFFFFF', 0, 0),
(12, 'auto', 0, 0, '', 'bottom-right', 0, 80, 'FFFFFF', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list`
--

CREATE TABLE `qinggan_list` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0为根主题，其他ID对应list表的id字段',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `module_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模块ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL COMMENT '网站ID',
  `title` varchar(255) NOT NULL COMMENT '主题',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未审核，1已审核',
  `hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0显示，1隐藏',
  `hits` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '查看次数',
  `tpl` varchar(255) NOT NULL COMMENT '自定义的模板',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` varchar(255) NOT NULL COMMENT 'SEO描述',
  `tag` varchar(255) NOT NULL COMMENT 'tag标签',
  `attr` varchar(255) NOT NULL COMMENT '主题属性',
  `replydate` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后回复时间',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示管理员发布',
  `identifier` varchar(255) NOT NULL COMMENT '内容标识串',
  `integral` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '财富基于，用于计算财富的基础量'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内容主表';

--
-- 转存表中的数据 `qinggan_list`
--

INSERT INTO `qinggan_list` (`id`, `parent_id`, `cate_id`, `module_id`, `project_id`, `site_id`, `title`, `dateline`, `sort`, `status`, `hidden`, `hits`, `tpl`, `seo_title`, `seo_keywords`, `seo_desc`, `tag`, `attr`, `replydate`, `user_id`, `identifier`, `integral`) VALUES
(520, 0, 0, 23, 42, 1, '网站首页', 1380942032, 10, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(712, 0, 0, 23, 42, 1, '关于我们', 1383355821, 20, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(713, 0, 0, 23, 42, 1, '资讯中心', 1383355842, 30, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(714, 0, 0, 23, 42, 1, '产品展示', 1383355849, 40, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(716, 0, 0, 23, 42, 1, '在线留言', 1383355870, 60, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(755, 712, 0, 23, 42, 1, '工作环境', 1383640450, 24, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(760, 713, 0, 23, 42, 1, '公司新闻', 1383815715, 10, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(761, 713, 0, 23, 42, 1, '行业新闻', 1383815736, 20, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1254, 712, 0, 23, 42, 1, '发展历程', 1392375210, 26, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1256, 0, 0, 23, 42, 1, '图集相册', 1392375722, 70, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1261, 0, 0, 61, 142, 1, '启邦互动', 1393321211, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1262, 0, 0, 61, 142, 1, '联迅网络', 1393321235, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1263, 0, 0, 61, 142, 1, '梦幻网络', 1393321258, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1265, 0, 0, 61, 142, 1, 'A5站长网', 1393321321, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1266, 0, 0, 61, 142, 1, '中国站长', 1393321365, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1267, 0, 0, 61, 142, 1, '落伍者', 1393321391, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1268, 0, 0, 61, 142, 1, '源码之家', 1393321413, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1276, 0, 0, 21, 41, 1, '企业建站，我信赖PHPOK', 1394008409, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1277, 0, 0, 21, 41, 1, '选择PHPOK，专业建站', 1394008434, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1278, 0, 0, 21, 41, 1, '开源精神，开创未来', 1394008456, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1285, 0, 0, 46, 96, 1, '测试留言', 1399239571, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1298, 0, 0, 23, 42, 1, '下载中心', 1409552212, 80, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1299, 0, 0, 23, 42, 1, '论坛BBS', 1409552219, 90, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1300, 0, 0, 23, 147, 1, '公司简介', 1409554964, 10, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1301, 0, 0, 23, 147, 1, '发展历程', 1409554975, 20, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1302, 0, 0, 23, 147, 1, '新闻中心', 1409554988, 30, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1303, 0, 0, 23, 147, 1, '在线留言', 1409554999, 40, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1304, 0, 0, 23, 147, 1, '联系我们', 1409555008, 50, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1305, 0, 0, 64, 148, 1, 'PHPOK销售客服', 1409747629, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1311, 0, 204, 66, 152, 1, '测试论坛功能', 1412391521, 0, 1, 0, 11, '', '', '', '', '', '', 0, 23, '', 0),
(1334, 0, 204, 66, 152, 1, '测试888', 1413063267, 0, 1, 0, 14, '', '', '', '', '', '', 0, 23, '', 0),
(1368, 0, 8, 22, 43, 1, 'EverEdit - 值得关注的代码编辑器', 1424912045, 0, 1, 0, 35, '', '', '', '', '', '', 0, 0, '', 0),
(1369, 0, 8, 22, 43, 1, '金山 WPS - 免费正版办公软件(支持Win/Linux/手机)', 1424916504, 0, 1, 0, 71, '', '', '', '', '', '', 1480329276, 0, '', 0),
(1370, 0, 68, 22, 43, 1, 'MySQL出错代码', 1424918437, 0, 1, 0, 53, '', '', '', '', '', '', 0, 0, '', 0),
(1371, 0, 68, 22, 43, 1, 'MySQL安装后需要调整什么', 1424918471, 0, 1, 0, 45, '', '', '', '', 'mysql', '', 1523947003, 0, '', 0),
(1427, 0, 0, 64, 148, 1, '前台客服', 1446469762, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1753, 0, 582, 24, 45, 1, '小米(MI) 小米5 全网通4G手机 双卡双待', 1452570664, 0, 1, 0, 41, '', '', '', '', '', '', 0, 0, '', 0),
(1756, 0, 0, 40, 87, 1, '公司简介', 1458467228, 10, 1, 0, 175, '', '', '', '', '', '', 0, 0, 'aboutus', 0),
(1757, 0, 0, 40, 87, 1, '联系我们', 1458474081, 40, 1, 0, 36, 'contactus', '', '', '', '', '', 0, 0, 'contactus', 0),
(1758, 0, 0, 40, 87, 1, '发展历程', 1458486519, 20, 1, 0, 45, '', '', '', '', '', '', 0, 0, 'development-course', 0),
(1759, 0, 0, 40, 87, 1, '工作环境', 1458486574, 30, 1, 0, 56, '', '', '', '', '', '', 0, 0, 'work', 0),
(1760, 0, 583, 24, 45, 1, '魅族 MX5 移动联通双4G手机 双卡双待', 1458626730, 0, 1, 0, 263, '', '', '', '', '', '', 0, 0, '', 0),
(1761, 0, 584, 24, 45, 1, '华为 P7 移动4G手机', 1458667195, 0, 1, 0, 26, '', '', '', '', '', '', 0, 0, '', 0),
(1762, 0, 585, 24, 45, 1, 'vivo Xplay5 全网通4G手机 4GB+128GB 双卡双待', 1458668060, 0, 1, 0, 34, '', '', '', '', '', '', 1480393813, 0, '', 0),
(1763, 0, 216, 24, 45, 1, 'Apple iPhone 5SE 16G 移动联通电信4G手机', 1458669038, 0, 1, 0, 395, '', '', '', '', '', '', 1523791703, 0, '', 0),
(1765, 0, 211, 68, 144, 1, 'Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE Apple iPhone 5SE ', 1458701924, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1766, 0, 211, 68, 144, 1, 'vivo Xplay5', 1458701947, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1767, 0, 211, 68, 144, 1, '华为P7', 1458701998, 0, 1, 0, 4, '', '', '', '', '', '', 0, 0, '', 0),
(1768, 0, 211, 68, 144, 1, '魅族MX5', 1458702037, 0, 1, 0, 7, '', '', '', '', '', '', 0, 0, '', 0),
(1769, 0, 211, 68, 144, 1, '小米5', 1458702065, 0, 1, 0, 15, '', '', '', '', '', '', 1523955465, 0, '', 0),
(1772, 0, 0, 61, 142, 1, 'PHPOK官网', 1459324936, 0, 1, 0, 0, '', '', '', '', '', '', 0, 23, '', 0),
(1854, 0, 204, 66, 152, 1, '测试新主题', 1498026275, 0, 1, 0, 83, '', '', '', '', '', '', 1524282417, 23, '', 0),
(1855, 0, 200, 65, 151, 1, '主题复制', 1516290172, 0, 1, 0, 34, '', '', '', '', '', '', 1523963359, 0, '', 0),
(1869, 0, 0, 46, 96, 1, 'fasdfasdfasdf', 1523951240, 0, 1, 0, 0, '', '', '', '', '', '', 0, 0, '', 0),
(1870, 0, 206, 66, 152, 1, '测试上传图片功能999', 1524195487, 0, 1, 0, 0, '', '', '', '', '', '', 0, 23, '', 0),
(1903, 0, 68, 22, 43, 1, '餐饮网站做好网上订餐的启示', 1426003200, 0, 1, 0, 1971, '', '', '', '', '', '', 0, 0, '', 0),
(1904, 0, 68, 22, 43, 1, '好网站助医院保健机构赢得好名声', 1426003200, 0, 1, 0, 1094, '', '', '', '', '', '', 0, 0, '', 0),
(1905, 0, 68, 22, 43, 1, '自助建站让金融典当拍卖公司更具竞争力', 1426003200, 0, 1, 0, 1121, '', '', '', '', '', '', 0, 0, '', 0),
(1906, 0, 68, 22, 43, 1, '环保生态生物技术等行业也需网站建设来助力', 1426003200, 0, 1, 0, 1086, '', '', '', '', '', '', 0, 0, '', 0),
(1907, 0, 68, 22, 43, 1, '网站建站助广告会展印刷行业业绩冲天', 1426003200, 0, 1, 0, 1831, '', '', '', '', '', '', 0, 0, '', 0),
(1908, 0, 68, 22, 43, 1, '行业协会和网站也需网站来彰显形象', 1425916800, 0, 1, 0, 1109, '', '', '', '', '', '', 0, 0, '', 0),
(1909, 0, 68, 22, 43, 1, '美容美发休闲养生网站建设成就事业', 1425916800, 0, 1, 0, 1551, '', '', '', '', '', '', 0, 0, '', 0),
(1910, 0, 68, 22, 43, 1, '咖啡网站建设提升商家收入', 1425916800, 0, 1, 0, 1113, '', '', '', '', '', '', 0, 0, '', 0),
(1911, 0, 68, 22, 43, 1, '旅游网站建设是向公众展示旅游信息的首要平台', 1425916800, 0, 1, 0, 1128, '', '', '', '', '', '', 0, 0, '', 0),
(1912, 0, 68, 22, 43, 1, '物业和家政业需要在网站上进行品牌宣传', 1425916800, 0, 1, 0, 1102, '', '', '', '', '', '', 0, 0, '', 0),
(1913, 0, 68, 22, 43, 1, '旅游网站建设和推广的那点事儿', 1425916800, 0, 1, 0, 2782, '', '', '', '', '', '', 0, 0, '', 0),
(1914, 0, 68, 22, 43, 1, '旅游网站建设后应处理好的事情', 1425916800, 0, 1, 0, 4400, '', '', '', '', '', '', 0, 0, '', 0),
(1915, 0, 68, 22, 43, 1, '好公司更需优秀网站建设公司帮助做最好的网站设计', 1425916800, 0, 1, 0, 1110, '', '', '', '', '', '', 0, 0, '', 0),
(1916, 0, 68, 22, 43, 1, '优秀网站设计带来更高经济效益', 1425916800, 0, 1, 0, 1684, '', '', '', '', '', '', 0, 0, '', 0),
(1917, 0, 68, 22, 43, 1, '网站做得好 客源滚滚来', 1425916800, 0, 1, 0, 1083, '', '', '', '', '', '', 0, 0, '', 0),
(1918, 0, 68, 22, 43, 1, '酒店网站拉近与客户的服务距离', 1425916800, 0, 1, 0, 1109, '', '', '', '', '', '', 0, 0, '', 0),
(1919, 0, 68, 22, 43, 1, '做好网站建设，发挥潮流影响力', 1425916800, 0, 1, 0, 1141, '', '', '', '', '', '', 0, 0, '', 0),
(1920, 0, 68, 22, 43, 1, '网站成为建筑和装修企业向客户展示的窗口', 1425916800, 0, 1, 0, 1966, '', '', '', '', '', '', 0, 0, '', 0),
(1921, 0, 68, 22, 43, 1, '物业网站建设提升物业公司服务形象', 1425916800, 0, 1, 0, 1107, '', '', '', '', '', '', 0, 0, '', 0),
(1922, 0, 68, 22, 43, 1, '网站设计应用助建筑装修公司实现梦想', 1425916800, 0, 1, 0, 1097, '', '', '', '', '', '', 0, 0, '', 0),
(1923, 0, 68, 22, 43, 1, '网站建设助力货运、贸易、物流与世界接轨', 1425916800, 0, 1, 0, 1108, '', '', '', '', '', '', 0, 0, '', 0),
(1924, 0, 68, 22, 43, 1, '网站建设成功让物业和家政双赢', 1425916800, 0, 1, 0, 1118, '', '', '', '', '', '', 0, 0, '', 0),
(1925, 0, 68, 22, 43, 1, '创意网站帮助企业吸引客户', 1425916800, 0, 1, 0, 1907, '', '', '', '', '', '', 0, 0, '', 0),
(1926, 0, 68, 22, 43, 1, '网站建设成功助力水产等食品行业提升竞争力', 1425916800, 0, 1, 0, 1110, '', '', '', '', '', '', 0, 0, '', 0),
(1927, 0, 68, 22, 43, 1, '创意网站帮助企业吸引客户', 1425830400, 0, 1, 0, 1101, '', '', '', '', '', '', 0, 0, '', 0),
(1928, 0, 68, 22, 43, 1, '优秀网站助您吸引更多客户', 1425830400, 0, 1, 0, 1113, '', '', '', '', '', '', 0, 0, '', 0),
(1900, 0, 68, 22, 43, 1, '装修网站让居住梦想轻松实现', 1426003200, 0, 1, 0, 1109, '', '', '', '', '', '', 0, 0, '', 0),
(1901, 0, 68, 22, 43, 1, '模拟旅游正成为旅游网站的热门应用', 1426003200, 0, 1, 0, 1611, '', '', '', '', '', '', 0, 0, '', 0),
(1902, 0, 68, 22, 43, 1, '做好网站助农业水产养殖实现电子商务化', 1426003200, 0, 1, 0, 1610, '', '', '', '', '', '', 0, 0, '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_21`
--

CREATE TABLE `qinggan_list_21` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `link` longtext NOT NULL COMMENT '链接',
  `target` varchar(255) NOT NULL DEFAULT '_self' COMMENT '链接方式',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '图片'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图片播放器';

--
-- 转存表中的数据 `qinggan_list_21`
--

INSERT INTO `qinggan_list_21` (`id`, `site_id`, `project_id`, `cate_id`, `link`, `target`, `pic`) VALUES
(1276, 1, 41, 0, 'http://www.phpok.com', '_blank', '1007'),
(1277, 1, 41, 0, 'http://www.phpok.com', '_blank', '1007'),
(1278, 1, 41, 0, 'http://www.phpok.com', '_blank', '1008');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_22`
--

CREATE TABLE `qinggan_list_22` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `content` longtext NOT NULL COMMENT '内容',
  `note` longtext NOT NULL COMMENT '摘要',
  `plugin_vote` int(11) NOT NULL DEFAULT '0' COMMENT '插件投票统计-plugin-vote'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章资讯';

--
-- 转存表中的数据 `qinggan_list_22`
--

INSERT INTO `qinggan_list_22` (`id`, `site_id`, `project_id`, `cate_id`, `thumb`, `content`, `note`, `plugin_vote`) VALUES
(1368, 1, 43, 8, '724', '<p style=\"text-align: center;\"><img src=\"res/201502/26/auto_724.jpg\" alt=\"auto_724.jpg\"/></p><p>Everedit 结合众多编辑器的特点开发出的兼顾性能和使用、小巧的、强悍的文本编辑器。</p><blockquote><p>首先，要能够支持多种文档编码，显示和输入的时候不应该乱码。</p><p>其次，要能够对于常见的代码进行着色和自定义。</p><p>再者，要能够自定义键盘和工具等。</p></blockquote><p>对于绝大多数人而言，上面的功能就足够了。那么对于进阶者，可能要求更高一些。比如对于着色，有的人希望着色能够足够强大，显示自己定义的日记格式、折叠等；对于键盘，有的人希望能够多个按键组合触发命令，甚至模拟一些终端编辑器的操作，比如&nbsp;VIM，高手还希望这个编辑器的自定义性足够强，可玩度高，能够支持脚本和插件等等。那么很高兴的告诉大家，Everedit具备上面无论是初学者还是高手所期望的全部功能，而且非常的小巧，压缩包只有3M左右，无论是冷启动还是热启动都非常的迅速。</p><p>因为作者初开发这个目的就是做一个强化的 Editplus。所以在 Everedit 的身上，您能够找到很多这个编辑器的影子！</p><p>官网地址：<a href=\"http://www.everedit.net/\" target=\"_blank\" textvalue=\"http://www.everedit.net/\" style=\"color: rgb(255, 0, 0); text-decoration: underline;\"><span style=\"color: rgb(255, 0, 0);\">http://www.everedit.net/</span></a></p><p><br/></p>', 'EverEdit 是一款相当优秀国产的免费文本(代码)编辑器，最初这项目是为了解决 EditPlus 的一些不足并增强其功能而开始的，比如 Editplus 的着色器较为简陋，无法进行复杂着色，如markdown语法; 也不支持自动完成, 还有多点 Snippet 等等。', 0),
(1369, 1, 43, 8, '725', '<p style=\"text-align: center;\"><img src=\"res/201502/26/auto_725.jpg\" alt=\"auto_725.jpg\"/></p><p>一直以来办公软件市场份额都是被微软的 Office&nbsp;牢牢占据，但其价格较贵，国内大多都是用着盗版。其实国产免费的 WPS 已经完完全全可以和Office媲美，且完美兼容各种doc、docx、xls、xlsx、ppt等微软文档格式！</p><p>金山 WPS Office 作为优秀免费国产软件，一直在用户中口碑相当好！它完全免费，体积小巧，跨平台支持Win、Linux、Android 和 iOS，兼容微软包括最新的&nbsp;Office2013&nbsp;在内的各种文档格式，几乎可以完美替代收费的&nbsp;Office。另外&nbsp;WPS 新增了用于协同工作的「轻办公」，适合国情的大量在线模版、范文、素材库等也都让其更加适合国人使用……</p><p>WPS Office 全面采用了「扁平化」界面设计，看起来非常专业时尚！它包含3个主要组件：文字、表格和演示，分别对应微软 MS Office 的 Word、Excel和PowerPoint，并且针对国内用户的习惯，WPS提供更多适合国人使用的模版。在界面和操作使用上也很相似，如果你习惯了用Office，那么你几乎可以不用重新学习即可马上熟练使用WPS。</p><p>WPS Office 深度兼容 Microsoft Office 的文档格式，你可以直接保存和打开 Microsoft Word、Excel 和 PowerPoint 文件；也可以用 Microsoft Office轻松编辑 WPS 系列文档。经测试，微软新的 docx、xlsx等格式打开都非常正常，兼容接近完美。</p><p>目前金山 WPS 已经提供了包括 Windows、Linux、Android 和 iOS 等系统的版本，而且它们通过轻办公的云服务将用户文档完全打通，轻松实现随时随地的移动办公，相比目前市面上很多 Office 类的软件都要方便得多。</p><p>对于非重度办公的用户而言，金山WPS&nbsp;和&nbsp;微软Office&nbsp;在界面和使用上其实并没有很大的差别，由于WPS有着良好的兼容性，实测几乎所有文档均能正常打开，完全可以替代MS Office。具体 WPS 和 MS Office 的技术谁更先进其实我们并不需要了解，免费好用才是王道！免去什么激活啊，什么注册码的麻烦，直接安装就可以免费使用，随时升级，这省下多少心呢！</p><p>最后，感谢金山给国人带来如此优秀实用的一款免费正版办公软件！</p>', '一直以来办公软件市场份额都是被微软的 Office 牢牢占据，但其价格较贵，国内大多都是用着盗版。其实国产免费的 WPS 已经完完全全可以和Office媲美，且完美兼容各种doc、docx、xls、xlsx、ppt等微软文档格式！', 0),
(1370, 1, 43, 68, '1019', '<p>1005：创建表失败</p><p>1006：创建数据库失败</p><p>1007：数据库已存在，创建数据库失败</p><p>1008：数据库不存在，删除数据库失败</p><p>1009：不能删除数据库文件导致删除数据库失败</p><p>1010：不能删除数据目录导致删除数据库失败</p><p>1011：删除数据库文件失败</p><p>1012：不能读取系统表中的记录</p><p>1020：记录已被其他用户修改</p><p>1021：硬盘剩余空间不足，请加大硬盘可用空间</p><p>1022：关键字重复，更改记录失败</p><p>1023：关闭时发生错误</p><p>1024：读文件错误</p><p>1025：更改名字时发生错误</p><p>1026：写文件错误</p><p>1032：记录不存在</p><p>1036：数据表是只读的，不能对它进行修改</p><p>1037：系统内存不足，请重启数据库或重启服务器</p><p>1038：用于排序的内存不足，请增大排序缓冲区</p><p>1040：已到达数据库的最大连接数，请加大数据库可用连接数</p><p>1041：系统内存不足</p><p>1042：无效的主机名</p><p>1043：无效连接</p><p>1044：当前用户没有访问数据库的权限</p><p>1045：不能连接数据库，用户名或密码错误</p><p>1048：字段不能为空</p><p>1049：数据库不存在</p><p>1050：数据表已存在</p><p>1051：数据表不存在</p><p>1054：字段不存在</p><p>1065：无效的SQL语句，SQL语句为空</p><p>1081：不能建立Socket连接</p><p>1114：数据表已满，不能容纳任何记录</p><p>1116：打开的数据表太多</p><p>1129：数据库出现异常，请重启数据库</p><p>1130：连接数据库失败，没有连接数据库的权限</p><p>1133：数据库用户不存在</p><p>1141：当前用户无权访问数据库</p><p>1142：当前用户无权访问数据表</p><p>1143：当前用户无权访问数据表中的字段</p><p>1146：数据表不存在</p><p>1147：未定义用户对数据表的访问权限</p><p>1149：SQL语句语法错误</p><p>1158：网络错误，出现读错误，请检查网络连接状况</p><p>1159：网络错误，读超时，请检查网络连接状况</p><p>1160：网络错误，出现写错误，请检查网络连接状况</p><p>1161：网络错误，写超时，请检查网络连接状况</p><p>1062：字段值重复，入库失败</p><p>1169：字段值重复，更新记录失败</p><p>1177：打开数据表失败</p><p>1180：提交事务失败</p><p>1181：回滚事务失败</p><p>1203：当前用户和数据库建立的连接已到达数据库的最大连接数，请增大可用的数据库连接数或重启数据库</p><p>1205：加锁超时</p><p>1211：当前用户没有创建用户的权限</p><p>1216：外键约束检查失败，更新子表记录失败</p><p>1217：外键约束检查失败，删除或修改主表记录失败</p><p>1226：当前用户使用的资源已超过所允许的资源，请重启数据库或重启服务器</p><p>1227：权限不足，您无权进行此操作</p><p>1235：MySQL版本过低，不具有本功能</p><p><br/></p>', '', 0),
(1371, 1, 43, 68, '', '<p>面对MySQL的DBA或者做MySQL性能相关的工作的人，我最喜欢问的问题是，在MySQL服务器安装后，需要调整什么，假设是以缺省的设置安装的。</p><p>我很惊讶有非常多的人没有合理的回答，很多的MySQL服务器都在缺省的配置下运行。</p><p>尽管可以调整非常多的MySQL服务器变量，但是在通常情况下只有少数的变量是真正重要的。在设置完这些变量以后，其他变量的改动通常只能带来相对有限的性能改善。</p><p><strong>key_buffer_size</strong>：非常重要，如果使用MyISAM表。如果只使用MyISAM表，那么把它的值设置为可用内存的30%到40%。恰当的大小依赖索引的数量、数据量和负载 ----记住MyISAM使用操作系统的cache去缓存数据，所以也需要为它留出内存，而且数据通常比索引要大很多。然而需要查看是否所有的 key_buffer总是在被使用 ---- key_buffer为4G而.MYI文件只有1G的情况并不罕见。这样就有些浪费了。如果只是使用很少的MyISAM表，希望它的值小一些，但是仍然至少要设成16到32M，用于临时表（占用硬盘的）的索引。</p><p><strong>MyISAM_buffer_pool_size</strong>：非常重要，如果使用MyISAM表。相对于MyISAM表而言，MyISAM表对buffer size的大小更敏感。在处理大的数据集（data set）时，使用缺省的key_buffer_size和MyISAM_buffer_pool_size，MyISAM可能正常工作，而MyISAM可能就是慢得像爬一样了。同时MyISAM buffer pool缓存了数据和索引页，因此不需要为操作系统的缓存留空间，在只用MyISAM的数据库服务器上，可以设成占内存的70%到80%。上面 key_buffer的规则也同样适用 ---- 如果只有小的数据集，而且也不会戏剧性地增大，那么不要把MyISAM_buffer_pool_size设得过大。因为可以更好地使用多余的内存。</p><p><br/></p><p><strong>MyISAM_additional_pool_size</strong>：这个变量并不太影响性能，至少在有像样的（decent）内存分配的操作系统中是这样。但是仍然需要至少设为20MB（有时候更大），是MyISAM分配出来用于处理一些杂事的。</p><p><strong>MyISAM_log_file_size</strong>：对于以写操作为主的负载(workload)非常重要，特别是数据集很大的时候。较大的值会提高性能，但增加恢复的时间。因此需要谨慎。我通常依据服务器的大小（server size）设置为64M到512M。</p><p><strong>MyISAM_log_buffer_size</strong>：缺省值在中等数量的写操作和短的事务的大多数负载情况下是够用的。如果有大量的UPDATE或者大量地使用blob，可能需要增加它的值。不要把它的值设得过多，否则会浪费内存--log buffer至少每秒刷新一次，没有必要使用超过一秒钟所需要的内存。8MB到16MB通常是足够的。小一些的安装应该使用更小的值。</p><p><strong>MyISAM_flush_logs_at_trx_commit</strong>：为MyISAM比MyISAM慢100倍而哭泣？可能忘记了调整这个值。缺省值是1，即每次事务提交时都会把日志刷新到磁盘上，非常耗资源，特别是没有电池备份的cache时。很多应用程序，特别是那些从MyISAM表移植过来的，应该把它设成2。意味着只把日志刷新到操作系统的cache，而不刷新到磁盘。此时，日志仍然会每秒一次刷新到磁盘上，因此通常不会丢失超过1到2秒的更新。设成0会更快一些，但安全性差一些，在MySQL服务崩溃的时候，会丢失事务。设成2只会在操作系统崩溃的时候丢失数据。</p><p><br/></p><p><strong>table_cache</strong>：打开表是昂贵的（耗资源）。例如，MyISAM表在MYI文件头做标记以标明哪些表正在使用。您不会希望这样的操作频繁发生，通常最好调整cache 大小，使其能够满足大多数打开的表的需要。它使用了一些操作系统的资源和内存，但是对于现代的硬件水平来说通常不是问题。对于一个使用几百个表的应用， 1024是一个合适的值（注意每个连接需要各自的缓存）。如果有非常多的连接或者非常多的表，则需要增大它的值。我曾经看到过使用超过100000的值。</p><p><br/></p><p><strong>thread_cache</strong>：线程创建/销毁是昂贵的，它在每次连接和断开连接时发生。我通常把这个值至少设成16。如果应用有时会有大量的并发连接，并且可以看到 threads_created变量迅速增长，我就把它的值调高。目标是在通常的操作中不要有线程的创建。</p><p><strong>query_cache</strong>：如果应用是以读为主的，并且没有应用级的缓存，那么它会有很大帮助。不要把它设得过大，因为它的维护可能会导致性能下降。通常会设置在32M到 512M之间。设置好后，经过一段时间要进行检查，看看是否合适。对于某些工作负载，缓存命中率低于会就启用它。</p><p>注意：就像看到的，上面所说的都是全局变量。这些变量依赖硬件和存储引擎的使用，而会话级的变量（per session variables）则与特定的访问量(workload)相关。如果只是一些简单的查询，就没有必要增加sort_buffer_size，即使有 64G的内存让您去浪费。而且这样做还可能降低性能。我通常把调整会话级的变量放在第二步，在我分析了访问量（或负载）之后。</p><p>此外在MySQL分发版中包含了一些my.cnf文件的例子，可以作为非常好的模板去使用。如果能够恰当地从中选择一个，通常会比缺省值要好。</p>', '', 1),
(1399, 1, 43, 68, '', '<p ></p><ul><li >PTFE膜材&mdash;&mdash;耐久性强，使用寿命在30年以上</li><li >PTFE膜材&mdash;&mdash;是永久性建筑的首选材料</li><li >PTFE膜材&mdash;&mdash;超自洁，防火材料</li><li >PTFE膜材&mdash;&mdash;专业化的加工工艺，严格的施工规程<br /> 膜结构建筑中最常用的膜材料。PTFE膜材料是指在极细的玻璃纤维（3微米）编织成的基布上涂上PTFE（聚四氟乙烯）树脂而形成的复合材料。PVC膜材料是指在聚酯纤维编织的基布上涂上PVC（聚氟乙烯）树脂而形成的复合材料。</li></ul>', '', 0),
(1400, 1, 43, 68, '', '<p ></p><ul><li >PTFE膜材&mdash;&mdash;耐久性强，使用寿命在30年以上</li><li >PTFE膜材&mdash;&mdash;是永久性建筑的首选材料</li><li >PTFE膜材&mdash;&mdash;超自洁，防火材料</li><li >PTFE膜材&mdash;&mdash;专业化的加工工艺，严格的施工规程<br /> 膜结构建筑中最常用的膜材料。PTFE膜材料是指在极细的玻璃纤维（3微米）编织成的基布上涂上PTFE（聚四氟乙烯）树脂而形成的复合材料。PVC膜材料是指在聚酯纤维编织的基布上涂上PVC（聚氟乙烯）树脂而形成的复合材料。</li></ul>', '', 0),
(1401, 1, 43, 68, '', '<p ></p><pid=\"MyContent\"><p>  2020年东京奥运会和残奥会筹备委员会公布了作为东京奥运会主会场的新国立竞技场的概念图。</p><p>　　国际奥委会全会当地时间9月7日在阿根廷首都布宜诺斯艾利斯投票选出2020年夏季奥运会的主办城市。日本东京最终击败西班牙马德里和土耳其伊斯坦布尔，获得2020年夏季奥运会举办权。</p><p></p><p ></p><p align=\"center\"><img id=\"23416362\" align=\"center\" src=\"res/201509/02/1441090082_0_293.jpg\" width=\"602\" height=\"276\" md5=\"\" alt=\"\" /></p><p align=\"center\"></p><p ></p><p align=\"center\"><img id=\"23416363\" align=\"center\" src=\"res/201509/02/1441090082_1_175.jpg\" width=\"600\" height=\"353\" md5=\"\" alt=\"\" /></p><p align=\"center\"></p><p ></p><p align=\"center\"><img id=\"23416364\" align=\"center\" src=\"res/201509/02/1441090082_2_260.jpg\" width=\"598\" height=\"353\" md5=\"\" alt=\"\" /></p><p align=\"center\"></p><p align=\"center\"></p><p >据了解，日本新国家体育场效果图是由东京奥运会审查委员会从全球募集的众多设计图中评选而出，该设计图出自的伊拉克女建筑家扎哈-哈迪德之手，从效果图来看，日本新国家体育场外观采用了全新的流线型设计，审查委员会给予了&ldquo;内部空间感强烈，与东京都城市空间相呼应&rdquo;、&ldquo;可开闭式天窗增加了体育场的实用性&rdquo;等高度评价。</p><p >根据计算，日本新国家体育场的扩建总花费将达到1300亿日元（约人民币78亿元），预计竣工时间为2019年3月，该体育场作为2020年东京奥运会比赛主会场，届时奥运会的开幕式、闭幕式、足球、田径等项目都将在该会场举行。</p><p ></p><p></p><p></p><p></p><p></p></p><p ></p>', '', 0),
(1402, 1, 43, 68, '', '<p ><span >2014年，建筑节能与科技工作按照党中央、国务院关于深化改革的总体要求，围绕贯彻落实党的十八大、十八届三中全会关于生态文明建设的战略部署和住房城乡建设领域中心工作，创新机制、整合资源、提高效率、突出重点、以点带面，积极探索集约、智能、绿色、低碳的新型城镇化发展道路，着力抓好建筑节能和绿色建筑发展，努力发挥科技对提升行业发展水平的支撑和引领作用。</span></p><p >　　大力推进绿色建筑发展 实施&ldquo;建筑能效提升工程&rdquo;</p><p >　　研究制订&ldquo;建筑能效提升工程路线图&rdquo;，明确中长期发展目标、原则、总体思路和策略以及政策取向，为制订&ldquo;十三五&rdquo;建筑节能规划奠定基础。</p><p >　　继续抓好《绿色建筑行动方案》的贯彻落实工作。继续实施绿色生态城区示范，加大绿色建筑和绿色基础设施建设推广力度，强化质量管理。重点做好政府办公建筑、政府投资的学校、医院、保障性住房等公益性建筑强制执行绿色建筑标准工作。</p><p >　　稳步提升新建建筑节能质量和水平。做好新修订发布的民用建筑节能设计标准贯彻实施工作。加大抓好新建建筑在施工阶段执行标准的监管力度。总结国际国内先进经验，开展高标准建筑节能示范区试点。强化民用建筑规划阶段节能审查、节能评估、民用建筑节能信息公示、能效测评标识等制度。</p><p >　　继续开展既有居住建筑节能改造。确保完成北方采暖区既有居住建筑供热计量及节能改造1.7亿平方米以上，督促完成节能改造的既有居住建筑全部实行供热计量收费。力争完成夏热冬冷地区既有居住建筑节能改造1800万平方米以上。</p><p >　　提高公共建筑节能运行管理与改造水平。进一步做好省级公共建筑能耗动态监测平台建设工作。推动学校、医院等公益性行业和大型公共建筑节能运行与管理。指导各地分类制订公共建筑能耗限额标准，研究建立基于能耗限额的公共建筑节能管理制度。加快推行合同能源管理。积极探索能效交易等节能新机制。<img src=\"res/201509/02/1441090074_0_254.jpg\" border=\"0\" alt=\"\" /></p><p >　　推进区域性可再生能源建筑规模化应用。总结光伏建筑一体化示范项目经验，扩大自发自用光伏建筑应用规模。继续抓好可再生能源示范省、市、县(区)工作，推动资源条件具备的省(区、市)针对成熟的可再生能源应用技术尽快制定强制性推广政策。</p><p >　　加强和完善绿色建筑评价管理工作。修订印发《绿色建筑评价标识管理办法》和《绿色建筑评价技术细则》。加大对绿色建筑标识评价的指导监督力度，加强绿色建筑评价标准贯彻实施培训，引导和支持地方出台鼓励绿色建筑发展的政策措施。</p><p ><span >转载钢构之窗</span></p>', '', 0),
(1403, 1, 43, 68, '', '<p ><span >建筑钢结构的发展揭开城市发展新篇章</span></p><p ><br /><br /> 建筑钢结构产业的发展是我国经济实力和科技水平快速发展的具体体现。近年来，在高层重型钢结构、大跨度空间钢结构、轻钢结构、钢-混凝土组合结构、钢结构住宅的共同发展推动下，我国城市经济快速发展。这些钢结构的广泛运用展示和证明了它的建筑魅力，以及无限的发展空间。<br /><br /><strong>　　高层重型钢结构成为城市的重要标志<br /></strong><br />高层钢结构建筑是一个国家经济实力和科技水平的反映，也往往被当作是一个城市的重要标志性建筑。在超高层建筑中往往采用部分钢结构或全钢结构建造，超高层建筑的发展体现了我国建筑科技水平、材料工业水平和综合技术水平的提高。</p><p ><img src=\"res/201509/02/1441090069_0_873.jpg\" alt=\"\" /></p><p >　　建筑钢结构揭开城市发展新篇章</p><p >　　从20世纪80年代至今我国已建成和在建高层钢结构达80多幢，总面积约600万平方米，钢材用量60多万吨。高层、超高层建筑的楼板和屋盖具有很大的平面刚度，是竖向钢柱与剪力墙或筒体的平面抗侧力构件，能使钢柱与各竖向构件起到变形协调作用。北京和上海新建和在建高层钢结构房屋数量超过了10幢。如上海环球金融中心101层，高492米，用钢量6.5万吨，中关村金融中心建筑面积11万平方米，高度为150米，用钢量1.5万吨。今后，全国每年将有200万平方米至300万平方米高层钢结构建筑施工，用钢量约45万吨。<br /><br /><strong>　　大跨度空间钢结构持续发展</strong><br /><br />近年来，以网架和网壳为代表的空间结构继续大量发展，不仅用于民用建筑，而且用于工业厂房、候机楼、体育馆、大剧院、博物馆等。开发空间钢结构的新材料、新结构、新技术、新节点、新工艺，实现大跨度与超大跨度空间钢结构的抗风抗震工程建设。展望未来，应在重点、热点、难点的科技领域开拓和发展各类新型、适用、美观的空间钢结构，并且无论在使用范围、结构型式、安装施工工法等方面均具有中国建筑结构的特色。如杭州、成都、西安、长春、上海、北京、武汉、济南、郑州等地的飞机航站楼、机库、会展中心等建筑，都采用圆钢管、矩型钢管制作为空间桁架、拱架及斜拉网架结构，其新颖和富有现代特色的风格使它们成为所在中心城市的标志性建筑。<br /><br />据中国钢结构协会空间结构分会统计：网架和网壳的生产已趋于平稳状态，每年建造1500座，约250万平方米，用钢约7万吨，悬索和膜结构目前处于发展阶段，用量还不大，专家预计每年将以20%的速度增加。随着我国经济建设的蓬勃发展和人民生活水平的不断提高，根据实际需要将在我国研究、设计、制作和安装150米至200米，甚至将大于200米的大跨度与超跨度的空间钢结构。</p><p ><strong>　揭开轻</strong><strong>钢结构</strong><strong>新的篇章</strong><br /><br />轻钢结构是相对于重钢结构而言的，其类型有门式刚架、拱型波纹钢屋盖结构等，用钢量（不含钢筋用量）一般为每平方米30公斤。门式刚架房屋跨度一般不超过40米，个别达到70多米，单跨或多跨均用，以单层为主，也可用于二层或三层建筑，拱型波纹钢屋盖结构跨度一般为8米，每平方米自重仅为20公斤，每年增长约100万平方米，用钢4万吨。门式刚架和拱型波纹钢屋盖都有相应的设计施工规程、专用软件和通用图集。<br /><br />自进入20世纪90年代以来，我国钢结构建筑的发展十分迅速，特别是一些代表城市标志性高层建筑的建成，为钢结构在我国的发展揭开了新的一页。如世界第三高的金茂大厦已竣工，现已投入运营。据了解，世界第一高度的上海浦东环球金融中心，高460米，建筑面积为31万平方米，现正在加紧建设中。由外商投资的大连总统大厦，正在加紧筹建之中，共95层，建成后其高度将名列世界前茅。</p><p ></p><p ><img src=\"res/201509/02/1441090069_1_435.jpg\" alt=\"\" /></p><p >　　建筑钢结构的发展 揭开城市发展新篇章</p><p ><br /><br />轻钢结构的发展则更是如火如荼，特别在工业厂房的建设中则更为迅猛。从钢结构制造加施工企业数量的大幅增长就可见一斑，如上海市的钢结构制造和施工单位已由原来的几十家发展到现在的400多家，仅上海的宝钢地区就有近百家的钢结构制造厂。<br /><br /><strong>　　钢-混凝土组合结构发展迅速</strong><br /><br />钢-混凝土组合结构是充分发挥钢材和混凝土两种材料各自优点的合理组合，不但具有优良的静、动力工作性能，而且能大量节约钢材、降低工程造价和加快施工进度，同时，对环境污染也较小，符合我国建筑结构发展的方向。<br /><br />钢-混凝土组合结构在我国发展十分迅速，已广泛应用于冶金、造船、电力、交通等部门的建筑中，并以迅猛的势头进入桥梁工程和高层、超高层建筑中。<br /><br />我国已采用钢-混凝土组合结构建成了许多大型的公路拱桥，如广州丫鬓沙大桥，桥长360米，重庆万州长江大桥，跨度420米，前者为钢管混凝土拱桥，后者为劲性钢管混凝土骨架拱桥。全国建成的组合结构拱桥已超过300座。在高层建筑方面，建成了全部采用组合结构的超高层建筑--深圳赛格广场大厦，高291.6米，属世界最高的钢-混凝土组合结构。全国已建成的采用组合结构的高层建筑也已超过40幢。<br /><br />钢-混凝土组合中的薄壁型钢主要有百叶薄壁型钢和装配式薄壁型钢等形式。其中，许多类型均能与混凝土有效地结合，共同承受外界弯矩和剪力，有的类型为装配式截面，布置较为灵活，可适用于不同截面尺寸的轻钢组合梁，并可作为标准型材批量生产，但在浇混凝土之前必须用框架固定其形状，有的为箱形薄壁型钢截面，与混凝土的粘结性能较差，一般只起到模板的作用。此外，还可根据实际需要，在薄壁型钢混凝土梁中配置一定数量的纵向钢筋，以进一步提高其抗弯刚度和极限承载力。<br /><br /><strong>　　钢结构住宅的发展走向</strong><br /><br />钢结构住宅具有强度高、自重轻、抗震性能好、施工速度快、结构构件尺寸小、工业化程度高的特点，同时钢结构又是可重复利用的绿色环保材料，因此钢结构住宅符合国家产业政策的推广项目。随着国家禁用实心粘土砖和限制使用空心粘土砖政策的推出，加快住宅产业化进程、积极推广钢结构住宅体系已迫在眉睫。但我国的钢结构住宅尚处于探索起步阶段，这种体系在钢结构防火、梁柱节点做法、楼板形式、配套墙体材料、经济性及市场可接受程度上尚有许多不完善之处。<br /><br />因此，发挥钢结构住宅的自身优势，可提高住宅的综合效益：一是用钢结构建造的住宅重量是钢筋混凝土住宅重量的1/2左右，可满足住宅大开间的需要，使用率也比钢筋混凝土住宅提高4%左右。二是抗震性能好，其延性优于钢筋混凝土。从国内外震后调查结果看，钢结构住宅建筑倒塌数量是最少的。三是钢结构构件、墙板及有关部品都在工厂制作，其质量可靠，尺寸精确，安装方便，易与相关部品配合，因此，不仅减少了现场工作量，而且也缩短了施工工期。钢结构住宅工地实质上是工厂产品的组装和集成场所，再补充少量无法在工厂进行的工序项目，符合产业化的要求。四是钢结构住宅是环保型的建筑，可以回收循环利用，污染很少，符合推进住宅产业化和发展节能省地型住宅的国家政策。</p><p ></p>', '', 0),
(1404, 1, 43, 68, '', '<p ><p id=\"zoom2\"><p><font3 face=\"Verdana\"></font3>2013中国上海国际膜结构应用与工程技术展览</p><p><font3 face=\"Verdana\"></font3>同期举办：第二十四届中国国际绿色建筑建材博览会<br /> 第十五届中国上海国际园林、景观及别墅配套设施展览会<br /> 时间：2013年8月15日-17日 地点：上海新国际博览中心（龙阳路2345号）<br /><span class=\"Apple-converted-space\"></span><br /> 组织单位： 协办单位：<br /> 中国膜结构建筑行业专委会 上海市城乡建设和交通委员会<br /> 中国钢结构协会空间结构分会 中国房地产企业管理协会<br /> 中国风景园林绿化协会 上海市房地产协 <br /> 上海市园林景观学会 媒体推广：<br /> 香港博亚国际展览集团 中国膜结构网<br /> 承办单位： 《别墅》杂志<span class=\"Apple-converted-space\"></span><br /> 上海京慕展览策划有限公司《景观设计》杂志<br /><span class=\"Apple-converted-space\"></span><br /> 目前，在全球范围内索膜结构无论在工程界还是在科研领域均处于热潮中。近年来，我国建筑市场对索膜建筑技术的需求明显有大幅度增长的趋势，国外各大著名索膜技术专业公司纷纷登陆我国，刺激了我国索膜建筑事业的发展。现代建筑环境是现代城市，现代文化与社会，现代人的生活和观念的综合表象。展现人的个性化，自娱性和多元性环境空间方面，膜结构以其独具魅力的建筑形式，必将会在环境建设中得到越来越广泛的应用。由于新材料、新形式的不断出现，膜结构具有强大的生命力，必将是21世纪建筑结构发展的主流。它的应用范围不仅限于体育或展览建筑，已向房屋建筑的各个方面扩展，因而具有广阔的发展前景。在中国，膜结构的开发与研究还刚刚起步，因此当务之急是学习并引进国外先进技术，开发生产我国自己的膜材，解决设计中存在的问题，膜结构在中国也将会得到越来越多的应用。故此，特举办&ldquo;2013中国上海国际膜结构应用与工程技术展览会&rdquo;，为行业搭建一次合作、交流的平台。</p></p></p>', '', 0),
(1405, 1, 43, 68, '', '<p ><imgborder=\"0\" alt=\"\" width=\"913\" height=\"4495\" src=\"res/201509/02/1441090048_0_167.png\" /></p>', '', 0),
(1406, 1, 43, 68, '', '<p >住建部发布了《城镇污水再生利用技术指南(试行)》(以下简称《技术指南》)用以指导城镇污水处理再生利用的规划、设施建设运行和管理。《技术指南》涵盖城镇污水再生利用技术路线、城镇污水再生处理技术、城镇污水再生处理工艺方案、城镇污水再生利用工程建设与设施运行维护、城镇污水再生利用风险管理等内容。</p><p ><strong >　　污水再生处理技术：常规处理、深度处理和消毒</strong></p><p >　　《技术指南》详细介绍了城镇污水再生处理技术，主要包括常规处理、深度处理和消毒。常规处理包括一级处理、二级处理和二级强化处理。主要功能为去除SS、溶解性有机物和营养盐(氮、磷)。深度处理包括混凝沉淀、介质过滤(含生物过滤)、膜处理、氧化等单元处理技术及其组合技术，主要功能为进一步去除二级(强化)处理未能完全去除的水中有机污染物、SS、色度、嗅味和矿化物等。消毒是再生水生产环节的必备单元，可采用液氯、氯气、次氯酸盐、二氧化氯、紫外线、臭氧等技术或其组合技术。</p><p >　　《技术指南》强调，城市污水再生处理系统应优先发挥常规处理在氮磷去除方面的功能，一般情况下应避免在深度处理中专门脱氮。</p><p ><strong >　　单元处理技术有机组合 保证不同用途水质要求</strong></p><p >　　《技术指南》指出，再生水的主要用途包括工业、景观环境、绿地灌溉、农田灌溉、城市杂用和地下水回灌等。污水再生处理工艺方案应根据不同用途的水质要求，选择不同的单元技术进行组合，并考虑工艺的可行性、整体流程的合理性、工程投资与运行成本以及运行管理方便程度等多方面因素，同时宜具有一定的前瞻性。《技术指南》针对各种不同用途给出了具体的工艺方案建议。对于向服务区域内多用户供水的城镇污水再生处理设施，供水水质应符合用水量最大的用户的水质要求;个别水质要求更高的用户，可自行增加处理措施，直至达到其水质要求。</p><p ><strong >　　风险管理核心：保证城镇污水再生利用的水质安全</strong></p><p >　　《技术指南》在城镇污水再生利用风险管理中强调，城镇污水再生利用必须保证再生水水源水质水量的可靠、稳定与安全，水源宜优先选用生活污水或不包含重污染工业废水在内的城市污水。要加强对污水接入城镇排水管网的许可管理，禁止含重金属、有毒有害有机物和病原微生物超标的工业或医疗等污水进入排水管网。</p><p >　　城镇污水再生利用的核心问题是水质安全。污水再生处理、存储及输配设施运营单位应具备相应的水质检测能力。另外，应制定针对重大事故和突发事件的应急预案，建立相应的应急管理体系，并按规定定期开展培训和演练。</p><p ><strong >　　城镇污水再生利用工程建设与设施运行维护</strong></p><p >　　在工程建设方面，《技术指南》指出，工程建设包括再生处理设施、再生水储存设施及再生水输配管网的建设，《技术指出》对选址、设计、设备选择、施工、验收等环节均提出指导建议。</p><p >　　在设施运行维护管理方面，《技术指南》指出，污水再生处理设施运营单位应加强对来水水质的日常监测，应依据污水排放&mdash;污水处理&mdash;再生水利用三者之间的水质关系，以及再生水用途和水质要求，建立水源水质控制目标。同时，应定期对储存设施进行检查，防止再生水泄漏或污染物入渗;定期对存储的再生水水质、水量进行监测，防止水质恶化;再生水作为城市河道或其他景观水系用水时，在汛期时，应服从统一调度，确保排水排涝畅通。</p><p ><strong >　　城镇污水再生利用 要合理布局统筹规划</strong></p><p >　　城镇污水再生利用规划是城镇排水与污水处理规划的重要内容。《技术指南》指出，污水处理厂的建设应考虑再生利用的需求，统一规划、统筹建设，对于暂时没有再生水需求的地方可以在污水处理厂规划过程中预留深度处理设施位置和接口。污水再生处理、储存和输配设施的布局应综合考虑水源和再生水用户的分布，统筹规划。再生水可通过压力管网、河道或供水车等方式输送至用户，管网的布置形式可选择环状或枝状管网，枝状管网末端需设置泄水设施;应考虑输配过程的加压、消毒及维护抢修站点用地等。再生水的储存和输配可充分利用城市景观水系。</p><p ><span >来源：中国污水处理工程网</span></p>', '', 0),
(1407, 1, 43, 68, '', '<p ><span >摘　要: 本文主要介绍了选择中小规模城市污水处理厂工艺流程的依据、原则和方法, 并根据不同的条件推荐了适用的工艺流程。</span></p><p >关键词: 城市污水处理; 工艺流程; 原则; 方法</p><p ><br />1　前言</p><p >根据我国发展规划, 2010 年全国设市城市和建制镇的污水平均处理率不低于50% , 设市城市的污水处理率不低于60% , 重点城市的污水处理率不低于70%。为了引导城市污水处理及污染防治技术的发展, 加快城市污水处理设施的建设, 2000 年5 月国家建设部、环境保护局和科技部联合印发了《城市污水处理及污染防治技术政策》。本文将结合该政策的内容, 主要研究日处理能力为10 万m 3 以下, 特别是1～ 5 万m 3.d 规模的城市污水处理厂适用的各种处理工艺流程的比较和选择, 从而确定不同条件下适用的较优工艺流程。</p><p >1　中小规模城市污水处理厂工艺流程概述</p><p >二级生物处理指利用水中的微生物来去除污水中的碳源有机物, 二级强化生物处理是指除利用微生物来去除污水中的碳源有机物外, 还需去除污水中的<span class=\"keyword\">氮</span>和磷。城市污水二级及二级强化处理一般以好氧生物处理为主, 好氧处理可分为活性污泥法和生物膜法两大类。<br />活性污泥法是利用河川自净原理, 人工创建的生化净化污水处理方法。中小规模城市污水厂适用的方法主要有AB 法、SBR 法、氧化沟法、AO 法、 A 2O 法、水解好氧法等。</p><p >生物膜法是利用土壤自净原理发展起来的, 通过附着在各种载体上的生物膜来处理污水的好氧生物处理法, 主要包括生物转盘、生物滤池和生物接触氧化法等工艺。</p><p >2　污水处理工艺流程选择的依据和原则</p><p >2. 1　污水处理级别的确定</p><p >选择污水处理工艺流程时首先应按受纳水体的性质确定出水水质要求, 并依此确定处理级别, 排水应达到国家排放标准(GB8978- 1996)。<br />设市城市和重点流域及水资源保护区的建制镇必须建设二级污水处理设施; 受纳水体为封闭或半封闭水体时, 为防治富营养化, 城市污水应进行二级强化处理, 增强除磷脱<span class=\"keyword\">氮</span>的效果; 非重点流域和非水源保护区的建制镇, 根据当地的经济条件和水污染控制要求, 可先行一级强化处理, 分期实现二级处理。</p><p >2. 2　工艺流程选择应考虑的技术因素</p><p >处理规模; 进水水质特性, 重点考虑有机物负荷、<span class=\"keyword\">氮</span>磷含量; 出水水质要求, 重点考虑对<span class=\"keyword\">氮</span>磷的要求以及回用要求; 各种污染物的去除率; 气候等自然条件, 北方地区应考虑低温条件下稳定运行; 污泥的特性和用途。 2. 3　工艺流程选择应考虑的技术经济因素〔3〕批准的占地面积, 征地价格; 基建投资; 运行成本; 自动化水平, 操作难易程度, 当地运行<span class=\"keyword\">管</span>理能力。</p><p >2. 4　工艺流程选择的原则</p><p >保证出水水质达到要求; 处理效果稳定, 技术成熟可靠、先进适用; 降低基建投资和运行费用, 节省电耗; 减小占地面积; 运行<span class=\"keyword\">管</span>理方便, 运转灵活; 污泥需达到稳定; 适应当地的具体情况; 可积极稳妥地选用污水处理新技术。</p><p >3　污水处理工艺流程的比较和选择方法〔2、3、4、5〕</p><p >在选定污水处理工艺流程时可以采用下面介绍的一种或几种比较方法。</p><p >3. 1　技术比较</p><p >在方案初选时可以采用定性的技术比较, 城市污水处理工艺应根据处理规模、水质特性、排放方式和水质要求、受纳水体的环境功能以及当地的用地、气候、经济等实际情况和要求, 经全面的技术比较和初步经济比较后优选确定。</p><p >方案选择比较时需要考虑的主要技术经济指标包括: 处理单位水量投资、削减单位污染物投资、处理单位水量电耗和成本、削减单位污染物电耗和成本、占地面积、运行性能可靠性、<span class=\"keyword\">管</span>理维护难易程度、总体环境效益等。</p><p >定性比较时可以采用有定论的结论和经验值等, 而不必进行详细计算。几种常用生物处理方法的比较见表1。</p><img alt=\"\" src=\"res/201509/02/1441090034_0_765.jpg\"/><p ><br />3. 2　经济比较</p><p >在选定最终采用的工艺流程时, 应选择2～ 3 种工艺流程进行全面的定量化的经济比较。可以采用年成本法或净现值法进行比较。</p><p >3. 2. 1　年成本法。将各方案的基建投资和年经营费用按标准投资收益率, 考虑复利因素后, 换算成使用年限内每年年末等额偿付的成本- 年成本, 比较年成本最低者为经济可取的方案。</p><p >3. 2. 2　净现值法。将工程使用整个年限内的收益和成本(包括投资和经营费) 按照适当的贴现率折算为基准年的现值, 收益与成本现行总值的差额即净现值, 净现值大的方案较优。</p><p >3. 2. 3　多目标决策法。多目标决策是根据模糊决策的概念, 采用定性和定量相结合的系统评价法。按工程特点确定评价指标, 一般可以采用5 分制评分, 效益最好的为5 分, 最差的为1 分。同时, 按评价指标的重要性进行级差量化处理(加权) , 分为极重要、很重要、重要、应考虑、意义不大五级。取意义不大权重为1 级, 依次按2n- 1 进级, 再按加权数算出评价总分, 总分最高的为多目标系统的最佳方案。评价指标项目及权重应根据项目具体情况合理确定。</p><p >例如确定某城市污水处理厂工艺流程时采用了表2 所示的评价指标及权重:</p><img alt=\"\" src=\"res/201509/02/1441090034_1_947.jpg\"/><p >进行工艺流程选择时, 可以先根据污水处理厂的建设规模, 进水水质特点和排放所要求的处理程度, 排除不适用的处理工艺, 初选2～ 3 种流程, 然后再针对初选的处理工艺进行全面的技术经济对比后确定最终的工艺流程。</p><p >4　中小规模城市污水厂处理工艺流程选择的探讨〔6、7、8〕</p><p >4. 1　根据进水有机物负荷选择处理工艺</p><p >进水BOD5 负荷较高(如&gt; 250m g.L ) 或生化性能较差时, 可以采用AB 法或水解- 生物接触氧化法、水解- SBR 法等; 进水BOD5 负荷较低时可以采用SBR 法或常规活性污泥法等。</p><p >4. 2　根据处理级别选择处理工艺</p><p >二级处理工艺可选用氧化沟法、SBR 法、水解好氧法、AB 法和生物滤池法等成熟工艺技术, 也可选用常规活性污泥法; 二级强化处理要求除磷脱<span class=\"keyword\">氮</span>, 工艺流程除可以选用AO 法、A 2O 法外, 也可选用具有除磷脱<span class=\"keyword\">氮</span>效果的氧化沟法、CA SS 法和水解- 接触氧化法等; 在投资有限的非重点流域县城, 可以先建设一级强化处理厂, 采用水解工艺、生物絮凝吸附(即AB 法的A 段) 和混凝沉淀等物化强化一级处理, 待资金等条件成熟后再续建后续生物处理工艺, 形成水解好氧法、AB 法等完整工艺。</p><p >4. 3　根据回用要求选择处理工艺</p><p >严重缺水地区要求污水回用率较高, 应选择 BOD5 和SS 去除率高的污水处理工艺, 例如采用氧化沟或SBR 工艺, 使BOD5 和SS 均达到20m g.L 以下甚至更低, 则回用处理只需要直接过滤就可以达到生活杂用水标准, 整个污水处理及回用厂流程非常简捷、经济。</p><p >如果出水将在相当长的时期内用于农灌, 解决缺水问题, 则处理目标可以以去除有机物为主, 适当保留肥效。</p><p >4. 4　根据气候条件选择处理工艺</p><p >冰冻期长的寒冷地区应选用水下曝气装置, 而不宜采用表面曝气; 生物处理设施需建在室内时, 应采用占地面积小的工艺, 如UN ITAN K 等; 水解池对水温变化有较好的适应性, 在低水温条件下运行稳定, 北方寒冷地区可选择水解池作为预处理; 较温暖的地区可选择各种氧化沟和SBR 法。</p><p >4. 5　根据占地面积选择处理工艺</p><p >地价贵、用地紧张的地区可采用SBR 工艺(尤其是UN TAN K) ; 在有条件的地区可利用荒地、闲地等可利用的条件, 采用各种类型的土地处理和稳定塘等自然净化技术, 但在北方寒冷地区不宜采用。用水解池作为稳定塘的预处理, 可以改善污水的生化性能, 减小稳定塘的面积。</p><p >4. 6　根据基建投资选择处理工艺</p><p >为了节省投资, 应尽量采用国内成熟的, 设备国产化率较高的工艺。</p><p >基建投资较小的处理工艺有水解- SBR 法、 SBR 法及其变型、水解- 活性污泥法等。用水解池作预处理可以提高对有机物的去除率, 并改善后续二级处理构筑物污水的生化性能, 可使总的停留时间比常规法少30%。采用水解- 好氧处理工艺高效节能, 其出水水质优于常规活性污泥法。<br />氧化沟法在用于以去除碳源污染物为目的二级处理时, 与各种活性污泥法相比, 优势不明显, 但用于还须去除<span class=\"keyword\">氮</span>磷的二级强化处理时, 则投资和运行费用明显降低。</p><p >4. 7　根据运行费用选择处理工艺</p><p >节省运行费用的途径有降低电耗、减少污泥量、减少操作<span class=\"keyword\">管</span>理人员等。电耗较低的流程有自然净化、氧化沟、生物滤池、水解好氧法等, 污泥量较少的有氧化沟和SBR 等, 自动化程度高、<span class=\"keyword\">管</span>理简单的流程有SBR 等。综合比较, 在基建费相当的条件下, 运行费用较低的处理方法有氧化沟、SBR、水解好氧法等。</p><p >4. 8　污泥处理</p><p >中小规模城市污水处理厂产生的污泥可进行堆肥处理和综合利用, 采用延时曝气的氧化沟法、SBR 法等技术的污水处理设施, 污泥需达到稳定化。</p><p >4. 9　可以推广应用的新工艺</p><p >在尽量采用成熟可靠工艺流程的同时, 也要研究开发适用于北方地区中小污水厂的新工艺, 或审慎采用国内外新开发的高效经济的先进工艺技术。城市污水处理新工艺应向简单、高效、经济的方向发展, 各类构筑物从工艺和结构上都应向合建一体化发展。</p><p >目前可以重点考虑应用和推广使用的流程有一体化氧化沟技术、CA SS 、UN ITAN K 和膜法等。</p><p >5　结束语</p><p >城市污水处理工艺应根据污水水质特性、排放水质要求, 以及当地的用地、气候、经济等实际情况, 经全面的技术经济比较后优选确定。处理水量在10 万m 3 以下的城市污水处理厂可以优先考虑的处理工艺有水解- SBR 法、SBR 法、氧化沟法、AB 法、水解- 接触氧化法、AO 法等, 如果条件适宜也可采用稳定塘等自然净化工艺。来源：谷腾水网</p><pid=\"leftDiv\" ><pid=\"left2\" class=\"itemFloat\" ><br /></p></p><pid=\"rightDiv\" ><pid=\"right2\" class=\"itemFloat\" ><br /></p></p><p></p>', '', 0),
(1408, 1, 43, 68, '', '<p ><p ><pclass=\"MsoNormal\" align=\"left\"><span >钢结构因其自身优点，在桥梁、工业厂房、高层建筑等现代建筑中得到广泛应用。在大量的工程建设过程中，钢结构工程也暴露出不少质量通病。本文主要针对辽宁近年来在钢结构主体验收及竣工验收中的常见问题及整改措施谈一些看法。</span></p><pclass=\"MsoNormal\" align=\"center\"><b ><span >一、钢结构工程施工过程中的部分问题及解决方法</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><b ><span lang=\"EN-US\">1</span></b><b ><span >、构件的生产制作问题</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><span >门式钢架所用的板件很薄，最薄可用到</span><st1:chmetcnv w:st=\"on\" tcsc=\"0\" numbertype=\"1\" negative=\"False\" hasspace=\"False\" sourcevalue=\"4\" unitname=\"毫米\"><span lang=\"EN-US\">4</span><span >毫米</span></st1:chmetcnv><span >。多薄板的下料应首选剪切方式而避免用火焰切割。因为用火焰切割会使板边产生很大的波浪变形。目前</span><span lang=\"EN-US\">H</span><span >型钢的焊接大多数厂家均采用埋弧自动焊或半自动焊。如果控制不好宜发生焊接变形，使构件弯曲或扭曲。</span></p><pclass=\"MsoNormal\" align=\"left\"><b ><span lang=\"EN-US\">2</span></b><b ><span >、柱脚安装问题</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(1)</span><span >预埋件</span><span lang=\"EN-US\">(</span><span >锚栓</span><span lang=\"EN-US\">)</span><span >问题现象：整体或布局偏移；标高有误；丝扣未采取保护措施。直接造成钢柱底板螺栓孔不对位，造成丝扣长度不够。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >措施：钢结构施工单位协同土建施工单位一起完成预埋件工作，混凝土浇捣之前。必须复核相关尺寸及固定牢固。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(2)</span><span >锚栓不垂直现象：框架柱柱脚底板水平度差，锚栓不垂直，基础施工后预埋锚栓水平误差偏大。柱子安装后不在一条直线上，东倒西歪，使房屋外观很难看，给钢柱安装带来误差，结构受力受到影响，不符合施工验收规范要求。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >措施：锚栓安装应坚持先将底板用下部调整螺栓调平，再用无收缩砂浆二次灌浆填实，国外此法施工。所以锚栓施工时，可采用出钢筋或者角钢等固定锚栓。焊成笼状，完善支撑，或采取其他一些有效措施，避免浇灌基础混凝土时锚栓移一位。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(3)</span><span >锚栓连接问题现象：柱脚锚栓未拧紧，垫板未与底板焊接；部分未露</span><span lang=\"EN-US\">2</span><span >～</span><span lang=\"EN-US\">3</span><span >个丝扣的锚栓。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >措施：应采取焊接锚杆与螺帽；在化学锚栓外部，应加厚防火涂料与隔热处理，以防失火时影响锚固性能；应补测基础沉降观测资料。</span></p><pclass=\"MsoNormal\" align=\"left\"><b ><span lang=\"EN-US\">3</span></b><b ><span >、连接问题</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(1)</span><span >高强螺栓连接</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >螺栓装备面不符合要求，造成螺栓不好安装，或者螺栓紧固的程度不符合设计要求。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >原因分析：</span></p><pclass=\"MsoNormal\" align=\"left\"><span >①表面有浮锈、油污等杂质，螺栓孔璧有毛刺、焊瘤等。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >②螺栓安装面虽经处理仍有缺陷。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >解决方法：</span></p><pclass=\"MsoNormal\" align=\"left\"><span >①高强螺栓表面浮锈、油污以及螺栓孔璧毛病，应逐个清理干净。使用前必须经防锈处理，使拼装用的螺栓，不得在正式拼装时使用。螺栓应由专人保管和发放。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >②处理装配面应考虑到施工安装顺序，防止重复进行，并尽量在吊装之前处理。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >螺栓丝扣损伤，螺杆不能自由旋入螺母，影响螺栓的装配。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >原因分析：丝扣严重锈蚀。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >解决方法：</span></p><pclass=\"MsoNormal\" align=\"left\"><span >①使用前螺栓应进行挑选，清洗除锈后作预配。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >②丝扣损伤的螺栓不能做临时螺栓使用，严禁强行打进螺孔。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >③预先选配的螺栓组件应按套存放，使用时不得互换。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(2)</span><span >现场焊缝现象：质量难以保证；设计要求全焊透的一、二级焊缝未采用超声波探伤；楼面主梁与柱未施焊；未采用引弧板施焊。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >解决方法：钢结构施焊前，对焊条的合格证进行检查，按设计要求选用焊含条，按说明书和操作规程要求使用焊条，焊缝表面不得有裂纹、焊瘤，一、二级焊缝不得有气孔、夹渣、弧坑裂纹，一级焊缝不得有咬边、未满焊等缺陷，一、二级焊缝按要求进行无损检测，在规定的焊缝及部位要检查焊工的钢印。不合格的焊缝不得擅自处理，定出修改工艺后再处理，同一部位的焊缝返修次数不宜超过两次。</span></p><pclass=\"MsoNormal\" align=\"left\"><b ><span lang=\"EN-US\">4</span></b><b ><span >、构件的变形问题</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(1)</span><span >构件在运输时发生变形，出现死弯或缓弯，造成构件无法进行安装。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >原因分析：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >构件制作时因焊接产生的变形，一般呈现缓弯。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >构件待运时，支垫点不合理，如上下垫木不垂直等或堆放场地发生沉陷，使构件产生死弯或缓变形。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">3)</span><span >构件运输中因碰撞而产生变形，一般呈现死弯。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >预防措施：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >构件制作时，采用减小焊接变形的措施。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >组装焊接中，采用反方向变形等措施，组装顺序应服从焊接顺序，使用组装胎具，设置足够多的支架，防止变形。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">3)</span><span >待运及运输中，注意垫点的合理配置。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >解决方法：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >构件死弯变形，一般采用机械矫正法治理。即用千斤顶或其他工具矫正或辅以氧乙炔火焰烤后矫正。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >结构发生缓弯变形时，采取氧乙炔火焰加热矫正。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(2)</span><span >钢梁构件拼装后全长扭曲超过允许值，造成钢梁的安装质量不良。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >原因分析：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >拼接工艺不合理。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >拼装节点尺寸不符合设计要求。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >解决方法：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >拼装构件要设拼装工作台，定为焊时要将构件底面找平，防止翘曲。拼装工作台应各支点水平，组焊中要防止出现焊接变形。尤其是梁段或梯道的最后组装，要在定位焊后调整变形，注意节点尺寸要符合设计，否则易造成构件扭曲。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >自身刚性较差的构件，翻身施焊前要进行加固，构件翻身后也应进行找平，否则构件焊后无法矫正。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(3)</span><span >构件起拱，数值大干或小于设计数值。构件起拱数值小时，安装后梁下挠；起拱数值大时，易产生挤面标高超标。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >原因分析：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >构件尺寸不符合设计要求。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >架设过程中，未根据实测值与计算值的出入进行修正。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">3)</span><span >跨径小的桥梁，起拱度较小，拼装时忽视。</span></p><pclass=\"MsoNormal\" align=\"left\"><span >解决方法：</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">1)</span><span >严格按钢结构构件制作允许偏差进行各步检验。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">2)</span><span >在架设过程中，杆件且装完毕，以及工地接头施工结束后，都进行上拱度测量，并在施工中对其他进行调整。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">3)</span><span >在小拼装过程，应严格控制累计偏差，注意采取措施，消除焊接收缩量的影响。</span></p><pclass=\"MsoNormal\" align=\"left\"><b ><span lang=\"EN-US\">5</span></b><b ><span >、钢结构安装问题</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(1)</span><span >钢柱底脚有空隙预控措施钢柱吊装前，应严格控制基础标高，测量准确，并按其测量值对基础表面仔细找平；如采用二次灌浆法，在柱脚底板开浇灌孔</span><span lang=\"EN-US\">(</span><span >兼作排气孔</span><span lang=\"EN-US\">)</span><span >，利用钢垫板将钢柱底部不平处垫平，并预先按设计标高安置好柱脚支座钢板，然后采取二次灌浆。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(2)</span><span >钢柱位移预控措施浇筑混凝土基础前，应用定型卡盘将预埋螺栓按设计位置卡住，以防浇灌混凝土时发生位移；柱低钢板预留孔应放大样，确定孔位后再作预留孔。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\">(3)</span><span >柱垂直偏差过大预控措施钢柱应按计算的吊挂点吊装就位，且必须采用二点以上的吊装方法，吊装时应进行临时固定，以防吊装变形；柱就位后应及时增设临时支撑；对垂直偏差，应在固定前予以修正。</span></p><pclass=\"MsoNormal\" align=\"center\"><b ><span >二、结论</span><span lang=\"EN-US\"><o:p></o:p></span></b></p><pclass=\"MsoNormal\" align=\"left\"><span >只有在施工管理过程中，加强对技术人员、工人对规范标准和操作规程的培训学习，切实做好开工前的准备，加强施工过程中的质量控制和监督检查，积极发挥施工、监理等各方面的作用，做好各分项工程的工序验收工作，才能保证钢结构工程的整体质量。</span></p><pclass=\"MsoNormal\" align=\"left\"><span lang=\"EN-US\"><o:p></o:p></span></p><pclass=\"MsoNormal\" align=\"left\"><span >信息来源：中国焊接网</span><spanlang=\"EN-US\"><o:p></o:p></span></p></p></p>', '', 0),
(1409, 1, 43, 68, '', '<p ></p><p ></p><p >绿色建筑评价标准<br />　　<br />　　1、在建筑的全寿命周期内，最大限度地节约资源(节能、节地、节水、节材)、保护环境和减少污染，为人们提供健康、适用和高效的使用空间，与自然和谐共生的建筑。<br />　　<br />　　2、节能能源：充分利用太阳能，采用节能的建筑围护结构以及采暖和空调，减少采暖和空调的使用。根据自然通风的原理设置风冷系统，使建筑能够有效地利用夏季的主导风向。建筑采用适应当地气候条件的平面形式及总体布局<br />　　<br />　　3、可再生能源：指从自然界获取的、可以再生的非化石能源，包括风能、太阳能、水能、生物质能、地热能和海洋能等。<br />　　<br />　　4、节约资源：在建筑设计、建造和建筑材料的选择中，均考虑资源的合理使用和处置。要减少资源的使用，力求使资源可再生利用。节约水资源，包括绿化的节约用水。<br />　　<br />　　5、可再利用材料：指在不改变所回收物质形态的前提下进行材料的直接再利用，或经过再组合、再修复后再利用的材料。<br />　　<br />　　6、可再循环利用材料：指已经无法进行再利用的产品通过改变其物质形态，生产成为另一种材料，使其加入物质的多次循环利用过程中的材料。<br />　　<br />　　7、以节约和适用的原则确定绿色建筑标准。<br />　　<br />　　8、绿色建筑建设应选用质量合格并符合使用要求的材料和产品，严禁使用国家或地方管理部门禁止、限制和淘汰的材料和产品。<br />　　<br />　　9、回归自然：绿色建筑外部要强调与周边环境相融合，和谐一致、动静互补，做到保护自然生态环境。<br />　　<br />　　10：建筑场地选址无洪灾、泥石流及含氡土壤的威胁，建筑场地安全范围内无电磁辐射危害和火、爆、有毒物质等危险源。<br />　　<br />　　11、住区建筑布局保证室内外的日照环境、采光和通风的要求，满足《城市居住区规划设计规范》GB50180中有关住宅建筑日照标准的要求。<br />　　<br />　　12、绿化种植适应当地气候和土壤条件的乡土植物，选用少维护、耐候性强、病虫害少，对人体无害的植物。<br />　　<br />　　13、建筑内部不使用对人体有害的建筑材料和装修材料。<br />　　<br />　　14、绿色建筑应尽量采用天然材料。建筑中采用的木材、树皮、竹材、石块、石灰、油漆等，要经过检验处理，确保对人体无害。<br />　　<br />　　良好的居住环境对室内和室外的要求都很高，绿色建筑能给人舒适和健康的生活居住环境，绿色建筑的建造并不等于高价和高成本，也不仅仅限于新建筑，最主要的是要环保、无害。</p><p ></p><p >　来源:0731房产网综合整理</p>', '', 0),
(1410, 1, 43, 68, '', '<p ><strong>美国回收材料打造&ldquo;资源保护屋&rdquo;</strong><p>　　美国政府的《超级基金法》规定&ldquo;任何生产有工业废弃物的企业，必须自行妥善处理，不得擅自随意倾卸&rdquo;。该法规从源头上限制了建筑垃圾的产生量，促使各企业自觉寻求建筑垃圾资源化利用途径。</p><p>　　近一段时间以来，美国住宅营造商协会开始推广一种&ldquo;资源保护屋&rdquo;，其墙壁就是用回收的轮胎和铝合金废料建成的，屋架所用的大部分钢料是从建筑工地上回收来的，所用的板材是锯末和碎木料加上20%的聚乙烯制成，屋面的主要原料是旧的报纸和纸板箱。这种住宅不仅积极利用了废弃的金属、木料、纸板等回收材料，而且比较好地解决了住房紧张和环境保护之间的矛盾。</p><p>　<strong>　法国将废物整体管起来</strong></p><p>　　法国CSTB公司是欧洲首屈一指的&ldquo;废物及建筑业&rdquo;集团，专门统筹在欧洲的&ldquo;废物及建筑业&rdquo;业务。公司提出的废物管理整体方案有两大目标：一是通过对新设计建筑产品的环保特性进行研究，从源头控制工地废物的产量；二是在施工、改善及清拆工程中，对工地废物的生产及收集作出预测评估，以确定相关回收应用程序，从而提升废物管理层次。</p><p>　　该公司以强大的数据库为基础，使用软件工具对建筑垃圾进行从产生到处理的全过程分析控制，以协助相关机构针对建筑物使用寿命期的不同阶段作出决策。例如，可评估建筑产品的整体环保性能；可依据有关执行过程、维修类别，以及不同的建筑物清拆类型，对某种产品所产生的废物量进行评估；可向顾问人员、总承建商，以及承包机构(客户)，就某一产品或产品系列对环保及健康的影响提供相关概览资料；可以对废物管理所需的程序及物料作出预测；可根据废物的最终用途或质量制订运输方案；就任何使用&ldquo;再造&rdquo;原料的新工艺，在技术、经济及环境方面的可行性作出评定，而且可估计产品的性能。</p><p><strong>　　荷兰有效分类建筑垃圾</strong></p><p>　　在荷兰，目前已有70%的建筑废物可以被循环再利用，但是荷兰政府希望将这一比例增加到90%。因此，他们制定了一系列法律，建立限制废物的倾卸处理、强制再循环运行的质量控制制度。荷兰建筑废物循环再利用的重要副产品是筛砂。由于砂很容易被污染，其再利用是有限制的。针对于此，荷兰采用了砂再循环网络，由拣分公司负责有效筛砂，即依照其污染水平进行分类，储存干净的砂，清理被污染的砂。</p><p>　　总体来讲，上述这些国家大多施行的是&ldquo;建筑垃圾源头削减策略&rdquo;，即在建筑垃圾形成之前，就通过科学管理和有效的控制措施将其减量化；对于产生的建筑垃圾则采用科学手段，使其具有再生资源的功能。</p><p>　　而对于已经过预处理的建筑垃圾，还有一些国家则运往&ldquo;再资源化处理中心&rdquo;，采用焚烧法进行集中处理，如德国西门子公司开发的干馏燃烧垃圾处理工艺，可使垃圾中的各种可再生材料十分干净地被分离出来，实现回收再利用，对于处理过程中产生的燃气则用于发电，每吨垃圾经干馏燃烧处理后仅剩下2到3公斤的有害重金属物质，从而有效地解决了垃圾占用大片耕地的问题。</p><p><strong>　　日本立法实现建筑垃圾循环利用</strong></p><p>　　由于国土面积小、资源相对匮乏，日本的构造原料价格比欧洲都要高。因此日本人将建筑垃圾视为&ldquo;建筑副产品&rdquo;，十分重视将其作为可再生资源而重新开发利用。比如港埠设施，以及其他改造工程的基础设施配件，都可以利用再循环的石料，代替相当数量的自然采石场砾石材料。</p><p>　　1977年，日本政府就制定了《再生骨料和再生混凝土使用规范》，并相继在各地建立了以处理混凝土废弃物为主的再生加工厂，生产再生水泥和再生骨料。1991年，日本政府又制定了《资源重新利用促进法》，规定建筑施工过程中产生的渣土、混凝土块、沥青混凝土块、木材、金属等建筑垃圾，必须送往&ldquo;再资源化设施&rdquo;进行处理。日本对于建筑垃圾的主导方针是：尽可能不从施工现场排出建筑垃圾；建筑垃圾要尽可能重新利用；对于重新利用有困难的则应适当予以处理</p></p>', '', 0),
(1411, 1, 43, 68, '', '<p >深圳宝安国际机场 T3 航站楼概念方案为美国兰德隆布朗公司和杨莫岚设计公司联合体设计，在此方案的基础上，通过国际招标，选定意大利 mFUKSASarch 建筑事务所的建筑方案，北京市建筑设计研究院中标为国内配合单位。在 2006~2008年期间，扩建工程指挥部进行了填海工程，以配合 T3航站楼的建设。T3航站楼南北长 1128m、东西宽 640m，建筑面积达55 万平方米左右，为目前国内最大单体面<br />积的航站楼之一。 T3 航站楼主体结构采用钢筋混凝土框架结构，整个航站楼的混凝土结构共分为 10 块。屋顶为不规则曲面，采用网壳结构。屋顶结构共分七块，包括主指廊D、次指廊G和H、交叉指廊C、过渡区B以及大厅A。典型屋顶结构的特点如下：<br />（1） 主指廊D块和次指廊G、 H块屋顶 这三部分屋顶网壳均采用斜交斜放的双层筒壳， 网壳曲面延伸到二层楼面 （标<br />高4.4m），与下部混凝土支承结构对应，屋顶结构每隔18m设一支座铰接于混凝土异形柱，并且在与支座对应的屋顶部位，<br />设置两片加强桁架作为主要受力体系。沿结构横向剖面，支座间距均为44.8m，主指廊最宽处为61.1m左右，次指廊最宽处<br />为 54.9m 左右。网壳主网格尺寸为 5.4m，为配合屋顶幕墙的需要，还布置有加密的檩条，檩条与主网格之间铰接。因屋顶<br />曲面造型的需要， 沿筒壳纵向和横向均变厚度，主指廊 （D块） 网壳最厚处为8.8m， 次指廊（G和H块） 网壳最厚处为4.2m。<br />因筒壳面内刚度较大且筒壳较长，为减小屋顶的温度内力，沿筒壳的纵向布置了弹簧支座。同时弹簧支座也减小了由于屋顶<br />分块和混凝土分块不对应、下部混凝土和上部筒壳变形不一致造成的上、下部相互影响。 <br />（2）交叉指廊C块屋顶 交叉指廊部分的屋顶由主指廊和次指廊屋顶交叉形成， 也是采用带加强桁架的斜交斜放网壳<br />结构。其中主指廊方向屋顶长度为162m，包括4榀落在2层楼面上（标高＋4.4m）的加强桁架；次指廊方向长度为199m，包括 10 榀落在 2 层楼面上的加强桁架。另外沿主指廊方向存在 108m 跨度的室内大厅，为此在室内大厅布置了 4 个落在三层楼面（标高+8.8m）的摇摆柱，在摇摆柱上方沿主指廊方向设置了两榀加强桁架。为提高结构刚度、减小关键加强桁架的内力（红色虚线圈出<br />的为加强桁架），设置16根水平拉杆将加强桁架与3层楼面的混凝土结<br />构拉接。 <br />（3）大厅屋顶A块 大厅屋顶跨越E、A和F共三块混凝土结构，东西方向长约640m，南北方向宽约320m，投影面积约为12.3万m2。屋顶支承结构的柱网为36m&times;36m和36m&times;27m两种，由钢筒体、框架柱、<br />摇摆柱以及一榀加强拱架组成屋顶支承体系，承担屋顶的竖向荷载、水平荷载以及幕墙的各种荷载。这里的框架柱均下端与混凝土结构铰接、上端与屋顶网架刚接，这种柱子的受力特点也与柱子下端横截面小、上端横截面大的截面形式一致。屋顶结构采用斜交斜放曲面网架，网格尺寸5.4m，网架高度3.6m。另外与屋顶支承体系的柱网配合，还设有正交正放加强桁架，加强桁架的网格尺寸为4.5m。 目前该工程正在设计中，还有很多问题需要研究，例如满足建筑外观要求、具有一定减震、减小温度内力作用的弹簧铰<br />支座。在长 640m、宽 320m的大厅区采用钢筒体+上端刚接、下端铰接的框架柱+摇摆柱的结构体系，有很多问题都是未遇<br />见的、其设计标准也需要进行性能化研究。</p><p ></p><p ></p><p ><em>转载</em></p>', '', 0),
(1412, 1, 43, 68, '', '<p ><img src=\"res/201509/02/1441090016_0_396.jpg\" border=\"0\" alt=\"\" /></p>', '', 0),
(1413, 1, 43, 68, '', '<p >在新中国成立60周年之际，建设科技正处于大发展的阶段。在科学发展观指导下，可持续发展的理念日益深入人心，建设行业的各领域无不突出着节能的宗旨，建筑节能成为当今建设科技发展的重要主题，并不断进步，与建筑节能有关的科学技术取得了丰硕成果。高度重视建筑节能，正是今天建设科技和建设行业的一大特点。<p>　<strong>　21世纪建设科技的主旋律</strong></p><p>　　我国的建筑节能，起步于上世纪80年代。改革开放后，建筑业在墙体改革及新型墙体材料方面有了发展。与此同时，一批高能耗的高档旅馆、公寓和商场出现了。如何在发展中降低建筑能耗，使之与当时能源供应较紧缺的现状相协调，成为相关部门关注的重点。为此，建筑节能工作首先从减少采暖能耗开始，1986年建设部颁布了《民用建筑节能设计标准》，要求新建居住建筑，在1980年当地通用设计能耗水平基础上节能30%%，《民用建筑节能设计标准》是我国第一部建筑节能设计标准，它的颁布，开启了我国建筑节能新阶段。以它提出的指标为目标，建筑节能的设计、节能技术纷纷发展起来，一系列的标准和法规先后制定。</p><p>　　20世纪90年代，建筑节能的地位进一步提高，节能工作有效开展。1990年，建设部提出&ldquo;节能、节水、节材、节地&rdquo;的战略目标。1994年在《中国21世纪议程》中，建筑节能作为项目之一被郑重提出;从1994年起，国家对北方节能建筑实施免征固定资产投资方向调节税，一批节能小区相继建成。1995年《民用建筑节能设计标准》修订并于次年执行，修订后的《民用建筑节能设计标准》将第二阶段建筑节能指标提高到50%%。同年，建设部发布《建筑节能&ldquo;九五&rdquo;计划和2010年规划》，这个专门的规划以及1996年9月建设部发布的《建筑节能技术政策》和《市政公用事业节能技术政策》，为其后建筑节能的发展明确了方向，同时也表明建筑节能地位的空前提高。建筑节能的地位最终由1998年1月1日实施的《中华人民共和国节约能源法》确定下来，建筑节能成为这部法律中明确规定的内容。</p><p>　　21世纪的到来，在科学发展观的指引下，建设领域明确了必须走资源节约型、环境友好型的新型工业化道路，建设科技工作将&ldquo;四节一环保&rdquo;作为科技攻关的主要方向，取得了明显效果。目前我国已初步建立起了以节能50%%为目标的建筑节能设计标准体系，部分地区执行更高的65%节能标准。2008年《民用建筑能效测评标识管理暂行办法》、《民用建筑节能条例》等施行，《民用建筑节能条例》的颁布，标志着我国民用建筑节能标准体系已基本形成，基本实现对民用建筑领域的全面覆盖。</p><p>　　在国务院办公厅《2009年节能减排工作安排》中规定，2009年底施工阶段执行节能强制性标准比例提高到90%以上。除新建建筑外，既有建筑的节能改造也有效开展起来，并取得了一批成果和经验。而兼顾土地资源节约、室内环境优化、居住人的健康、节能节水节材等方面的目标绿色建筑，成为新世纪建筑节能发展的亮点。</p><p><strong>　　建筑节能技术飞速发展</strong></p><p>　　在建筑节能逐步成为建设科技主旋律的过程中，相关的节能技术也有了长足进步。</p><p>　　在建设部组织下，&ldquo;九五&rdquo;期间实施了&ldquo;2000年小康型城乡住宅科技产业工程&rdquo;，&ldquo;十五&rdquo;期间组织实施了&ldquo;小城镇科技发展重大专项&rdquo;、&ldquo;居住区与小城镇建设关键技术研究&rdquo;、&ldquo;绿色建筑关键技术研究&rdquo;等，&ldquo;十一五&rdquo;期间实施了&ldquo;建筑节能关键技术研究与示范&rdquo;、&ldquo;现代建筑设计与施工关键技术研究&rdquo;、&ldquo;既有建筑综合改造关键技术研究与示范&rdquo;、&ldquo;可再生能源与建筑集成技术研究与示范&rdquo;等项目，这些科研攻关项目的组织实施，使一系列建筑节能的重大、关键、共性技术得到突破，形成了一大批科技成果。</p><p>　　建筑节能的各项技术都达到很高水平。</p><p>　　降低建筑能耗，首先要从围护结构、外墙、屋面、外门窗来实现。墙体改革的调查研究开始于上世纪70年代，80年代以来，新型墙体材料和高保温材料不断涌现，混凝土空心砌块、聚苯乙烯泡沫板等材料，逐渐替代了传统墙体材料，在建筑节能中发挥了重要作用。同时，我国广泛开展研究建筑外墙保温技术，近年来，各种外墙外保温技术系统日益成熟并在工程中应用，显示出良好前景。</p><p>　　此外还有建筑门窗。门窗传热系数的高低，决定了能耗的高低，要降低能耗，就必须提高门窗的热工性能，增加门窗的隔热保温性能。近20年来，为满足节能需求，外窗玻璃产品及工艺水平迅速发展，由之前采用普通单层玻璃、双层玻璃发展到中空、充气、LOW-E玻璃，塑钢型材、钢化玻璃等也广泛应用，取代了传统的钢窗和铝合金门窗。</p><p>　　建筑能耗的降低，还有赖于暖通技术和设备。为实现采暖系统的节能，上世纪80年代我国研发了平衡供暖技术及其产品、锅炉运行管理技术与产品。在散热器方面，上世纪90年代以来各种新型散热器纷纷得到开发，这些新产品比传统的铸铁散热器，具有金属热强度高、散热性能好、承压能力高、造型美观、工艺性好、安装方便等优点。</p><p>　　进入新世纪后，随着既有建筑节能改造的开展，供热改革成为建筑节能的重要内容。为适应改革的需要，室温可调和采暖计量收费技术及产品有了进一步的发展。采暖系统的单管顺流系统变为双管系统，散热器恒温阀及热表的应用已经十分普及。</p><p>　　技术是保证建筑节能得以实现的关键，多年来我国建筑节能技术的发展，让人们对&ldquo;十一五&rdquo;期间实现建筑节能1.6亿吨标准煤的目标充满信心。</p><p><strong>　　绿色建筑成果丰硕</strong></p><p>　　绿色建筑是生态环境与建筑有机结合，在建筑生命周期内最大限度地节约资源、保护环境，为人们提供高效、舒适空间的建筑。近10年来，绿色节能建筑成为建筑节能中的一大亮点，体现了新世纪建筑节能更高的追求目标。</p><p>　　进入21世纪后，绿色建筑评价体系逐步建立，保证了绿色建筑的健康发展。2001年建设部住宅产业化促进中心编制了《绿色生态住宅小区建设要点与技术导则》，2004年建设部针对北京奥运会，开展了&ldquo;绿色奥运建筑评估体系&rdquo;课题研究，形成了我国第一套绿色建筑项目标准。同年8月建设部颁布实施《全国绿色建筑创新奖管理办法》，次年，首届全国绿色建筑创新奖揭晓，40个项目获得此项殊荣，中国在推进智能与绿色建筑方面迈出了坚实的一步。2005年，历时5年编制完成的《绿色建筑技术导则》颁布施行，自此，绿色建筑的评定有了明确依据。&ldquo;十五&rdquo;期间，重点攻关计划&ldquo;绿色建筑规划设计导则和评估体系研究&rdquo;项目完成。2006年，建设部组织编制了《绿色建筑评价标准》。2007年8月，《绿色建筑评价技术细则》和《绿色建筑评价标识管理办法》出台，2008年6月住房和城乡建设部为进一步规范和细化绿色建筑评价标识工作，根据评价标识工作实际情况，编制了《绿色建筑评价技术细则补充说明(规划设计部分)》，制定了《绿色建筑评价标识使用规定》，进一步完善了绿色建筑设计评价标识的申报评价程序。</p><p>　　一系列工作，建立了适合我国国情的绿色建筑评价体系，有力地推动了绿色建筑技术发展。经过多年的攻关和研究，绿色建筑形成了六大技术体系评价标准：节地与室外环境、节能与能源利用、节水与水资源利用、节材与材料资源利用、室内环境质量及运营管理。通过对建筑的节能、节水、节地、节材和室内环境的具体性能进行实测，给出数据，实现定量化检测标准，达到标准的即为绿色建筑。</p><p>　　2008年8月，住房城乡建设部建筑节能与科技司向首批绿色建筑设计评价标识项目颁发了证书，上海世博会世博中心工程等6个项目获得了行业主管部门认可的第一批绿色建筑设计评价标识，标志着由政府部门主导的绿色建筑评价正式启动，结束了我国依赖国外标准进行绿色建筑评价的历史。</p><p>　　回顾建筑节能的历程，可以看到，这项利国利民的事业，紧跟时代步伐，取得了举世瞩目的成就。而这一切，没有党和政府的重视，是不可想象的。也正因此，人们完全有理由对建筑节能的前景充满信心。</p><p></p></p>', '', 0),
(1414, 1, 43, 68, '', '<p ><p align=\"center\"><img title=\"8月30日拍摄的世博轴膜结构工程（局部）。 8月30日，上海世博园区世博轴膜结构工程全面完成。世博轴工程采用全新建筑形式，其屋顶设计为长约840米、宽约97米的巨型索膜结构，形如蓝天下的朵朵白云，并在整个索膜覆盖的结构中设置了6个巨型圆锥状钢结构&ldquo;阳光谷&rdquo;，让自然光透过&ldquo;阳光谷&rdquo;倾泻而下，满足部分地下空间的采光，体现环保和节约的理念。据介绍，世博轴索膜结构厚度仅为1毫米，使用寿命可达30年。新华社发 \"height=\"175\" src=\"res/201509/02/1441090001_0_548.jpg\" width=\"402\" alt=\"\" /></p><p> 8月30日拍摄的世博轴膜结构工程（局部）。 8月30日，上海世博园区世博轴膜结构工程全面完成。世博轴工程采用全新建筑形式，其屋顶设计为长约840米、宽约97米的巨型索膜结构，形如蓝天下的朵朵白云，并在整个索膜覆盖的结构中设置了6个巨型圆锥状钢结构&ldquo;阳光谷&rdquo;，让自然光透过&ldquo;阳光谷&rdquo;倾泻而下，满足部分地下空间的采光，体现环保和节约的理念。据介绍，世博轴索膜结构厚度仅为1毫米，使用寿命可达30年。新华社发</p><p align=\"center\"><img title=\" 8月30日拍摄的世博轴膜结构工程全景（拼图）。 新华社发\" src=\"res/201509/02/1441090001_1_697.jpg\" alt=\"\" /></p></p>', '', 0),
(1415, 1, 43, 68, '', '<p ></p><ul><li >PTFE膜材&mdash;&mdash;耐久性强，使用寿命在30年以上</li><li >PTFE膜材&mdash;&mdash;是永久性建筑的首选材料</li><li >PTFE膜材&mdash;&mdash;超自洁，防火材料</li><li >PTFE膜材&mdash;&mdash;专业化的加工工艺，严格的施工规程<br /> 膜结构建筑中最常用的膜材料。PTFE膜材料是指在极细的玻璃纤维（3微米）编织成的基布上涂上PTFE（聚四氟乙烯）树脂而形成的复合材料。PVC膜材料是指在聚酯纤维编织的基布上涂上PVC（聚氟乙烯）树脂而形成的复合材料。</li></ul>', '', 0),
(1416, 1, 43, 68, '', '<p><strong><span >  <span >ETFE建筑</span></span></strong><span ><span >膜</span><span >材是一种乙烯</span><span >-</span><span >四氟乙烯的共聚物</span><span >.ETFE膜材的厚度通常小于0.20mm，是一种透明膜材.</span></span></p><p><span ><span >   用ETFE原料制成的膜材料替代传统的玻璃和其他高分子采光板用于大型建筑物的屋面或墙体材料，显示出无可比拟的优势。</span></span></p><p><span ><span > ETFE膜使用寿命至少为25-35年,</span><span > ETFE</span><span >膜达到</span><span >B1</span><span >、</span><span >DIN4102防火等级标准，燃烧时也不会滴落。且该膜质量很轻，每平方米只有0.15-0.35公斤。这种特点使其即使在由于烟、火引起的膜融化情况下也具有相当的优势。</span></span></p><p><span ><span > 与玻璃不同的是ETFE具有很好的隔热介质，单层膜可以在无色膜材上印刷不同图案，可调节室内光线。</span></span></p><p><p ><img src=\"res/201509/02/1441090077_0_111.jpg\" border=\"0\" alt=\"\" /></p><p ><img src=\"res/201509/02/1441090077_1_885.jpg\" border=\"0\" alt=\"\" /></p></p>', '', 0),
(1417, 1, 43, 68, '', '<p class=\"MsoNormal\" ><b><span lang=\"EN-US\" >2014</span></b><b><span >第三届中国（广州）国际建筑钢结构、空间结构及金属材料设备展览会</span></b><b><span lang=\"EN-US\" ><o:p></o:p></span></b></p><p class=\"MsoNormal\" ><b><span lang=\"EN-US\" >The 3<sup>rd</sup><st1:country-region w:st=\"on\">China</st1:country-region>(<st1:city w:st=\"on\">Guangzhou</st1:city>) International Exhibition for<st1:place w:st=\"on\"><st1:placename w:st=\"on\">Steel</st1:placename><st1:placename w:st=\"on\">Construction &amp; Metal</st1:placename><st1:placetype w:st=\"on\">Building</st1:placetype></st1:place>Materials<o:p></o:p></span></b></p><p class=\"MsoNormal\" ><span >地点：中国进出口商品交易会&middot;琶洲展馆<span lang=\"EN-US\"></span></span><b><span >时间</span></b><span >：<st1:chsdate w:st=\"on\" isrocdate=\"False\" islunardate=\"False\" day=\"12\" month=\"5\" year=\"2014\"><span lang=\"EN-US\">2014</span>年<span lang=\"EN-US\">5</span>月<span lang=\"EN-US\">12</span>日</st1:chsdate><span lang=\"EN-US\">-14</span>日</span></p><p class=\"MsoNormal\" ><b><span lang=\"EN-US\" ><o:p></o:p></span></b></p><pclass=\"Section1\" ><p class=\"MsoNormal\" align=\"left\" ><v:line id=\"_x0000_s1026\" strokeweight=\"1.5pt\" to=\"549.7pt,3.1pt\" from=\"-7.95pt,3.1pt\" ></v:line><b><span >主办单位：<span lang=\"EN-US\"><o:p></o:p></span></span></b></p><p class=\"MsoNormal\" align=\"left\" ><span >亚洲建筑技术联盟协会<span lang=\"EN-US\"></span>中国市政工程协会<span lang=\"EN-US\"></span>中国贸促会建设行业分会集成建筑委员会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >广东省空间结构学会<span lang=\"EN-US\"></span>粤港经济合作交流促进会<span lang=\"EN-US\"></span>香港鸿威展览集团<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><b><span >协办单位：<span lang=\"EN-US\"><o:p></o:p></span></span></b></p><p class=\"MsoNormal\" align=\"left\" ><span >中国贸促会建设行业分会国际交流中心<span lang=\"EN-US\"></span>东莞市建筑金属结构行业协会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><b><span >国际机构：</span></b><span lang=\"EN-US\" ><o:p></o:p></span></p><p class=\"MsoNormal\" align=\"left\" ><span >美国钢结构协会<span lang=\"EN-US\"></span>澳大利亚钢结构协会<span lang=\"EN-US\"></span>加拿大钢结构协会<span lang=\"EN-US\"></span>韩国钢结构协会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >智利钢结构协会<span lang=\"EN-US\"></span>日本钢结构协会<span lang=\"EN-US\"></span>墨西哥钢结构协会<span lang=\"EN-US\"></span>新西兰钢结构协会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >新加坡钢结构协会<span lang=\"EN-US\"></span>法国驻广州总领事馆商务处<span lang=\"EN-US\"></span>美国钢铁协会<span lang=\"EN-US\"></span>欧洲钢结构协会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >新西兰驻广州总领事馆<span lang=\"EN-US\"></span>新西兰大型工程研究会<span lang=\"EN-US\"></span>西班牙安达卢西亚自治区政府贸促会上海代表处<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >尼日利亚联邦共和国驻上海总领事馆<span lang=\"EN-US\"></span>日本建筑钢骨协会<span lang=\"EN-US\"></span>丹麦未来产业化可持续建筑和城市发展组织</span><span lang=\"EN-US\" ><o:p></o:p></span></p><p class=\"MsoNormal\" align=\"left\" ><b><span >承办单位：<span lang=\"EN-US\"><o:p></o:p></span></span></b></p><p class=\"MsoNormal\" ><span >广州市鸿威展览服务有限公司<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" ><b><span >支持单位：<span lang=\"EN-US\"><o:p></o:p></span></span></b></p><p class=\"MsoNormal\" align=\"left\" ><span >广东省住房和城乡建设厅<span lang=\"EN-US\"></span>浙江省钢结构行业协会<span lang=\"EN-US\"></span>江苏省建筑钢结构混凝土协会钢结构分会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >四川省金属结构行业协会<span lang=\"EN-US\"></span>辽宁省建筑金属结构协会<span lang=\"EN-US\"></span>福建建筑业协会金属结构与建材分会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >安徽省钢结构协会<span lang=\"EN-US\"></span>河南省钢结构协会<span lang=\"EN-US\"></span>山西省土建学会空间结构专业委员会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >香港建筑金属结构协会<span lang=\"EN-US\"></span>澳门金属结构协会<span lang=\"EN-US\"></span>山东省勘察设计协会钢结构分会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><span >上海市金属结构行业协会<span lang=\"EN-US\"></span>天津市钢结构学会<span lang=\"EN-US\"></span>北京市建设工程物资协会钢结构分会<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><b><span >支持媒体：<span lang=\"EN-US\"><o:p></o:p></span></span></b></p><p class=\"MsoNormal\" align=\"left\" ><span >广东电视台、南方电视台、中国建设报、中华建筑报、建筑时报、广东建设报、羊城晚报、新浪地产、搜房网、中国钢结构网、中国钢结构资讯网、钢结构在线、中国生态环境与节能建设网、建筑钢结构网、钢结构网、广东建设信息网、钢构之窗、《钢结构》杂志、《中国住宅设施》、《中国钢结构产业》、《钢结构资源》、商务时报品牌钢构周刊、《钢结构与设备》杂志<span lang=\"EN-US\">...</span>各协会（学会）刊物及网站等一百多家海内外媒体<span lang=\"EN-US\"><o:p></o:p></span></span></p><p class=\"MsoNormal\" align=\"left\" ><b><span lang=\"EN-US\" ><o:p></o:p></span></b></p></p><p ><b><span lang=\"EN-US\" ><br clear=\"all\"/></span></b></p><p class=\"MsoNormal\" align=\"center\" ><b><i><span >以钢代木，保护地球生态资源；以钢代砼，促进绿色环保建筑</span></i></b></p><p ><span ><p class=\"MsoNormal\" ><strong>参展范围<o:p></o:p></strong></p><p class=\"MsoNormal\" ><span >1、钢结构及钢铁产品，包括建筑金属结构、钢结构、轻钢结构、重钢结构、海洋结构、预应力结构、钢砼组合结构、空间网架结构、拉膜结构等空间结构；重钢、轻钢、H型钢、无缝钢管、工字型钢、冷弯型钢、特殊钢材等；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >2、建筑钢结构板材、板件，包括中厚板、压型板、采光板、夹芯板、不锈钢薄板、镜面板、艺术板、镀钛板、彩色涂层板等板材；不锈钢棒、线、管材等；彩钢、钢结构预制品等；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >3、钢结构连接产品和设备，包括各种固件锚栓及标准和非标准紧固件，螺栓，栓钉，铆钉，锚夹具；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >4、建筑钢结构安全防护工业体系，包括涂料、防腐、保温、隔热、防水、防火耐火产品及防爆技术；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >5、新型住宅房屋技术及配套装饰、装修产品类：新型房屋设计、建设单位、新型结构产品、墙体、屋面、门窗、龙骨、幕墙、楼层板、吊顶、遮阳系统、通风设备；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >6、铝合金、塑钢、非金属装饰性材料及相关技术和设备类：铝合金、塑钢、复合材料等装饰性构件和板材、玻璃制品；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >7、立体车库设备、钢结构门业；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >8、金属钢结构加工配套设备及检测设备类，包括各种成型加工设备、焊接设备、焊接材料、切割、铸造、数控技术及五金电动工具、施工安装机具、喷涂设备、涂锈设备、钢材检验、探伤设备等；桥梁、塔桅、容器、管道的制造加工设备；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >9、计算机设计、分析、计算与CAD绘图软件类：各类钢结构设计、分析、计算软件；项目管理、投标及工程预算软件；加工中心与结构样图CAD工作站等；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >10、节能节地型建筑（钢结构住宅试点工程、实验基地）；钢结构领域新成果（名人、名企、名项目）；钢结构工程招标；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >11、设计及施工展示（大型场馆、会议中心、大型公共建筑的设计技术及方案，大型施工公司示范工程）；<o:p></o:p></span></p><p class=\"MsoNormal\" ><span >12、建筑设计及房地产项目规划设计区：建筑设计院所、优秀建筑设计项目、优秀房地产规划项目、技术咨询单位样板工程</span></p></span></p>', '', 0),
(1418, 1, 43, 68, '', '<p ></p><h2>青口镇文体中心外立面膜结构工程（重新招标）</h2><pclass=\"gg-xl-fbsj\">来源：中国国际招标网 发布时间：2014.02.21</p><!--div class=\"gg-jdb\"><img src=\"res/201509/02/1441090066_0_157.gif\" width=\"292\" height=\"82\"/></a></div--><!--有权限--><pclass=\"gg-conte\"><p ><palign=\"left\">招 标 公 告</p><palign=\"left\">1. 招标条件</p><palign=\"left\">本招标项目青口镇文体中心外立面膜结构工程（重新招标）已由闽侯县发展和改革局以侯发改审批[2013]44号批准建设，项目业主为闽侯县青口镇人民政府，建设资金来自财政拨款，招标人为闽侯县青口镇小城镇综合改革建设试点指挥部，委托的招标代理单位为福建省闽建工程造价咨询有限公司。项目已具备招标条件，现对该项目的设计、施工进行国内公开招标。</p><palign=\"left\">2. 项目概况和招标范围</p><palign=\"left\">2.1. 建设地点：青口镇壶山村；</p><palign=\"left\">2.2. 工程建设规模：青口镇文体中心的综合馆气枕包覆面积约5300㎡，游泳馆气枕包覆面积约3300㎡，综合馆和游泳馆中部PTFE雨棚覆盖面积约2000㎡；最高控制价为18845308元 ；</p><palign=\"left\">2.3. 招标范围和内容： 青口镇文体中心外立面膜结构工程施工，内容包括 ETFE 充气膜 结构、PTFE雨棚的二次深化设计、材料供应、膜结构工程制作及安装等全部相关工程施工 ，具体详见工程量清单和施工图纸；</p><palign=\"left\">2.4. 工期要求：总工期：150个日历天；其中各关键节点的工期要求为：无；</p><palign=\"left\">2.5. 工程质量要求：符合设计、国家工程施工质量验收规范合格标准；</p><palign=\"left\">2.6. 本项目（标段）招标有关的单位：</p><palign=\"left\">2.6.1. 咨询单位：福建省闽建工程造价咨询有限公司；</p><palign=\"left\">2.6.2.设计单位：城市建设研究院；</p><palign=\"left\">2.6.3.代建单位：无 ；</p><palign=\"left\">2.6.4.监理单位：待定。</p><palign=\"left\">3. 投标人资格要求及审查办法</p><palign=\"left\">3.1.本招标项目要求投标人具备建设行政主管部门核发有效的三级及以上钢结构工程专业承包施工资质和《施工企业安全生产许可证》；投标人同时具备中国钢结构协会空间结构分会核发有效的膜结构工程设计二级及以上资质和膜结构工程承包二级及以上资质。</p><palign=\"left\">3.2.投标人拟担任本招标项目的项目经理应具备有效的不低于二级建筑工程专业注册建造师执业资格(含临时执业证书)注册建造师执业资格，并持有有效的安全生产考核合格证书（B证）；依据闽建筑[2013]41号和闽建筑[2014]6号文规定：（1）已按规定在2013年12月31日前提出延续注册申请或者已办理完延续注册的二级临时注册建造师；已按规定在2014年2月28日前提出延续注册申请或者已办理完延续注册的一级建造师临时注册建造师；（2）已提出申请但还未办理完成延续注册的二级及以上临时注册建造师，投标人应在投标文件中附有省住房和城乡建设厅行政服务中心出具的延续注册受理单证明并加盖投标人公章，否则按资格审查不合格处理。</p><palign=\"left\">3.3. 本招标项目 接受 联合体投标，自愿组成联合体的应由 具备 三级及以上钢结构工程专业承包施工资质 的企业为主办方，且各方均应具备承担招标项目的相应资质条件，相同专业单位组成的联合体的，按照资质等级较低的单位确定资质等级。</p><palign=\"left\">3.4.投标人&ldquo;类似工程业绩&rdquo;要求：投标人至少有1项业绩；&ldquo;类似工程业绩&rdquo;是指（下同）：自本招标公告发布之日的前5年内（不含发布招标公告当日）完成的并经竣工验收合格的单项合同工程造价不少于500万元的ETFE充气膜结构工程的国内（不含港澳台地区）施工项目。注：根据闽建筑（2011）39号文要求，本招标项目（工程）所称的类似工程业绩应符合以下条件之一：（1）在福建省行政区域内完成的业绩或抢险救灾中由福建省组织在省外完成的业绩；（2）在福建省外完成的业绩，必须是通过互联网且不需任何权限即可在工程所在地的建设行政主管部门政务网站查询得到，而且查询到的数据应能满足本招标项目（工程）的要求。</p><palign=\"left\">3.5. 投标人应在人员、设备、资金等方面具有承担本招标项目（标段）施工的能力，具体要求详见招标文件；</p><palign=\"left\">3.6. 本招标项目招标人对投标人的资格审查采用的方式：资格后审。</p><palign=\"left\">3.7. 投标时，投标人和拟派本工程项目管理班子成员没有因违法违规被有关行政监督部门取消或限制本招标项目的投标。</p><palign=\"left\">3.8.投标人具备已通过年检合格有效的企业法人营业执照，企业注册资本金金额应超过3769062元（即本项目最高控制价&times;20%的金额）。</p><palign=\"left\">4. 招标文件的获取</p><palign=\"left\">4.1. 凡有意参加投标者，请于 2014 年 2 月 24 日至 2014 年 2 月 28日（法定公休日、法定节假日除外），每天上午 9 时 00 分至 12 时 00 分，下午 13 时 00 分至 16 时 30 分（北京时间，下同），到<u>闽侯县建设工程交易中心</u> 福建省闽建工程造价咨询有限公司 <u>代表处（地址：闽侯县科技中心内闽侯县行政服务中心负一楼</u> ） 购买招标文件；</p><palign=\"left\">4.2. 招标文件每份售价 200元，（含工程量清单、工程控制价、电子光盘等），售后不退。投标人若需要购买本项目施工图纸的，可向招标人提出申请，招标人在三个工作日内提供购买的施工图纸，图纸售价不高于《福建省物价局转发国家计委关于印发</p><palign=\"left\">5. 评标办法</p><palign=\"left\">本招标项目采用的评标办法： 经评审的最低投标价中标法 。</p><palign=\"left\">6. 投标保证金的提交</p><palign=\"left\">6.1. 投标保证金提交的时间：投标截止时间之前；</p><palign=\"left\">6.2.投标保证金提交的方式：从投标人企业基本账户以电汇或银行转账的形式汇达投标保证金指定账户；或按榕建招[2013]38号文规定交存年度投标保证金；</p><palign=\"left\">6.3. 投标保证金提交的金额：人民币叁拾陆万元。</p><palign=\"left\">7. 投标文件的递交</p><palign=\"left\">7.1. 投标文件递交的截止时间（投标截止时间）： 2014 年 3 月 18 日 10 时 00 分，提交地点为闽侯县建设工程交易中心本项目开标室 ；<u>在递交纸质投标文件的同时，投标人拟派出的委托代理人出示授权委托书原件和身份证原件，项目经理应当持注册建造师执业证书（或建造师临时执业证书）原件、身份证原件、购买招标文件凭证（一份凭证仅代表一个投标人）原件到场核验登记，否则其投标文件将不予接收。</u></p><palign=\"left\">7.2. 逾期送达的或未送达指定地点或投标文件密封不符合规定要求的投标文件，招标人不予受理。</p><palign=\"left\">8. 发布公告的媒介</p><palign=\"left\">本次招标公告同时在 福建招标与采购网（ http://www.fjbid.gov.cn ） 、福州市建设工程招标投标网（ www.fzztb .org）、闽侯招标网（www.mhztb.com）及闽侯县建设工程交易中心公示栏 上发布。</p><palign=\"left\">9. 联系方式</p><palign=\"left\">招标人：闽侯县青口镇小城镇综合改革建设试点指挥部；</p><palign=\"left\">地址：闽侯县青口镇工业路1号，邮编：350119；</p><palign=\"left\">电 话：0591-22770987</p><palign=\"left\">联系人：林主任。</p><palign=\"left\">招标代理机构：福建省闽建工程造价咨询有限公司</p><palign=\"left\">地址：福州市工业路451号鼓楼科技商务中心大厦六层，邮编：350002；</p><palign=\"left\">电话：0591-87605650-819，传真：0591-87623982；</p><palign=\"left\">联系人：林工。</p><palign=\"left\">投标保证金银行帐号：</p><palign=\"left\">开户银行：民生银行福州闽侯支行；</p><palign=\"left\">帐户名称：闽侯县建设工程交易中心；</p><palign=\"left\">帐 号：1516 0142 1000 0041 。</p><palign=\"left\">（应在汇款凭证上注明&ldquo;闽侯房建招2014011&rdquo;投标保证金，如因投标人汇款凭证未注明项目招标编号造成银行无法识别投标保证金到账情况或识别错误的，其责任由投标人自行承担。）</p><palign=\"left\">交易中心名称： 闽侯县建设工程交易中心 ；</p><palign=\"left\">电 话： 0591-22063699 ；</p><palign=\"left\">地 址： 闽侯县科技中心内闽侯县行政服务中心负一楼 。</p></p></p>', '', 0);
INSERT INTO `qinggan_list_22` (`id`, `site_id`, `project_id`, `cate_id`, `thumb`, `content`, `note`, `plugin_vote`) VALUES
(1419, 1, 43, 68, '', '<p ><br class=\"Apple-interchange-newline\" /><p align=\"center\"></p><center><imgid=\"597978\" title=\"\" border=\"0\" align=\"center\" src=\"res/201509/02/1441090061_0_691.jpg\" sourcename=\"本地文件\" sourcedescription=\"编辑提供的本地文件\" alt=\"\" /></center><p></p><p></p><p >大连体育场，2754个气枕附着在体育新城中心体育场周围的钢结构桁架上，白天或夜晚在阳光或LED灯的照射下，蓝白相间的膜气枕将会形成海浪翻滚的大景观，将把本市这座海滨城市的特点充分展现在世人面前。据了解，中心体育场外膜结构工程将在5月底全部完工。昨天，记者走进中心体育场进行了一番探访。</p><p >蓝白相间的条块将中心体育场从空中&ldquo;包裹&rdquo;起来，远看，犹如大海中的波浪在翻滚。蓝色条块就像蓝色的海面一样，白色条块象征翻滚着的白色浪花。近看，在偌大的圆形体育场四周布满了脚手架，蓝色条块和白色条块由众多气枕组成，将圆形的体育场在纵向也形成圆弧形状，若一幢巨型战鼓悬于半空。</p><p >进入体育场内部，看台上坐椅林立，广场上的塑胶跑道和中间的绿色球场已经显现出来。往上看，圆穹形的膜结构将看台全部覆盖。&ldquo;观看比赛时，自然是风吹不着，雨淋不到。 &rdquo;中心体育场外膜结构工程施工单位，是曾参与北京水立方膜结构工程施工的本市民营企业大连伟霖膜结构工程有限公司，该公司高级工程师刘昌伟说，大连中心体育场还有内膜，将会从内部把眼前的纵横交错的钢结构桁架全部包裹起来。</p><p >目前，仅有西南部接近1万平方米的外膜结构还没有完工。本月底外膜结构将全部完工。</p></p>', '', 0),
(1420, 1, 43, 68, '', '<p ><p><b>膜结构</b><span >主要适用场所：体育场馆,体育场看台,主席台,相关遮阳遮雨膜结构; 高速公路收费站,加油站,停车场,公交站台,机场,地铁站,游乐园,休闲广场,观景台,舞台空,停车场膜结构；小区入口、车库入口、通道走廊、城市标志入口；高速公路收费站空间膜结构、加油站膜结构、博览会展厅膜结构、购物中心、售货亭、商业步行街、批发中心、临时会场张拉膜结构、休闲场所张拉膜结构景观膜结构、泳池遮阳膜，景观膜结构、大门出入张拉膜、小品膜、标志性膜结构建筑等。</span></p><p><span class=\"Apple-converted-space\"></span><br /></p><p><imgborder=\"0\" width=\"675\" height=\"670\" alt=\"\" src=\"res/201509/02/1441090045_0_444.jpg\" /></p><p><p >膜结构主要适用场所：运动场、体育馆、体育看台张拉膜结构; 博物馆张拉膜、音乐广场索膜结构、游乐园、休闲广场张拉膜结构、观景台张拉膜、舞台空间膜结构、停车场膜结构；高速公路收费站空间膜结构、加油站膜结构、博览会展厅膜结构、购物中心、售货亭、商业步行街、批发中心、临时会场张拉膜结构、休闲场所张拉膜结构景观膜结构、泳池遮阳膜，景观膜结构、大门出入张拉膜、小品膜、标志性膜结构建筑等。</p><p ></p><p ></p><p ></p></p></p>', '', 0),
(1421, 1, 43, 68, '', '<p ></p><ul><li >PTFE膜材&mdash;&mdash;耐久性强，使用寿命在30年以上</li><li >PTFE膜材&mdash;&mdash;是永久性建筑的首选材料</li><li >PTFE膜材&mdash;&mdash;超自洁，防火材料</li><li >PTFE膜材&mdash;&mdash;专业化的加工工艺，严格的施工规程<br /> 膜结构建筑中最常用的膜材料。PTFE膜材料是指在极细的玻璃纤维（3微米）编织成的基布上涂上PTFE（聚四氟乙烯）树脂而形成的复合材料。PVC膜材料是指在聚酯纤维编织的基布上涂上PVC（聚氟乙烯）树脂而形成的复合材料。</li></ul>', '', 0),
(1422, 1, 43, 68, '', '<p ></p><pid=\"MyContent\"><p>  2020年东京奥运会和残奥会筹备委员会公布了作为东京奥运会主会场的新国立竞技场的概念图。</p><p>　　国际奥委会全会当地时间9月7日在阿根廷首都布宜诺斯艾利斯投票选出2020年夏季奥运会的主办城市。日本东京最终击败西班牙马德里和土耳其伊斯坦布尔，获得2020年夏季奥运会举办权。</p><p></p><p ></p><p align=\"center\"><img id=\"23416362\" align=\"center\" src=\"res/201509/02/1441090082_0_293.jpg\" width=\"602\" height=\"276\" md5=\"\" alt=\"\" /></p><p align=\"center\"></p><p ></p><p align=\"center\"><img id=\"23416363\" align=\"center\" src=\"res/201509/02/1441090082_1_175.jpg\" width=\"600\" height=\"353\" md5=\"\" alt=\"\" /></p><p align=\"center\"></p><p ></p><p align=\"center\"><img id=\"23416364\" align=\"center\" src=\"res/201509/02/1441090082_2_260.jpg\" width=\"598\" height=\"353\" md5=\"\" alt=\"\" /></p><p align=\"center\"></p><p align=\"center\"></p><p >据了解，日本新国家体育场效果图是由东京奥运会审查委员会从全球募集的众多设计图中评选而出，该设计图出自的伊拉克女建筑家扎哈-哈迪德之手，从效果图来看，日本新国家体育场外观采用了全新的流线型设计，审查委员会给予了&ldquo;内部空间感强烈，与东京都城市空间相呼应&rdquo;、&ldquo;可开闭式天窗增加了体育场的实用性&rdquo;等高度评价。</p><p >根据计算，日本新国家体育场的扩建总花费将达到1300亿日元（约人民币78亿元），预计竣工时间为2019年3月，该体育场作为2020年东京奥运会比赛主会场，届时奥运会的开幕式、闭幕式、足球、田径等项目都将在该会场举行。</p><p ></p><p></p><p></p><p></p><p></p></p><p ></p>', '', 0),
(1922, 1, 43, 68, '', '<p>放眼当今社会，最热门的莫过于现今的房子问题了，国家出台了限购等等措施，但是无法阻挡需求的不断增加，由此建筑、装修等等公司孕育而生，然而新的建筑房产都是预期提前销售的，客人看不到已经建筑好的房，或者装修好的效果。于是建筑网站建设和装修网站建设，就必然成为建筑公司和装修公司为客人展示的好方式了。在PHPOK企业站系统里的装修网站设计和建筑网页设计，能让您的网站更完美呈现公司的产品。</p><p>我国建筑起步较完，发展气势不容小觑，通过网络上进一步超前展示出建成后的效果，让购房者有了更多的遐想空间，从而为自己选择满意房。网站建设和网站设计后的完美体现，对于建筑公司和装修公司来说就是自己另外一张脸。至少从视觉上让购房者满意。</p><p>建筑是人类用建筑材料从自然空间中围隔出来的一种人造空间，现代社会的人们对生存环境质量的期望值越来越高,高品质的生活方式与理想生活状态，应该来源于能够给人以精神安慰和精神享受的外在环境，这种优美的人造空间，我们可以优先通过建站大师的建筑网站建设帮您呈现在客户的面前，为客户有更多的机会来选择。我们将给您带来满意的网络空间建筑的同时，为您吸引众多的眼球。里面的自助建站功能，让您可以为自己的网站尽情的变化，使自己直接接触新兴的互联网知识。增加乐趣的同时为您招来客户。</p><p>生活质量的提高，购房者们不仅仅满足于网站上看到的网站设计的效果。应用到实际房上，大多数装修都依然参考装修网站里面的装修网站设计,因为满意网站上的设计效果，在看中后更想应用到实际生活中，这样让设计者更多的发挥空间，给购买者更多的选择机会。</p><p>PHPOK企业站系统，有强劲的网站设计团队，技术过硬的网站建设团队，良好的网站建设一条龙服务，让您的愿望从梦想变成现实。</p><p><br/></p>', '放眼当今社会，最热门的莫过于现今的房子问题了，国家出台了限购等等措施，但是无法阻挡需求的不断增加，由此建筑、装修等等公司孕育而生，然而新的建筑房产都是预期提前销', 0),
(1923, 1, 43, 68, '', '<p>贸易、物流和货运，逐渐成为我国核心产业之一，而生活中我们越来越依赖于网络。它们都属于服务性行业，贸易网站建设和物流网站建设，是公司在发展过程中必不可少的，现在贸易基本都针对国内和国外的形式进行着，另外货源网站模板是网站建设中不可忽视的。</p><p>随着国际贸易、运输方式的发展，致使贸易、运输的经营人大都不能亲自处理每一项具体业务，大量业务需要委托他人代为办理。同时，为了使国际贸易运输向简单化、统一化发展，由同一代理人完成或组织完成货物贸易运输显得十分必要，“货运代理”就应运而生了。代理人就更需要通过网站来处理业务，已是网站设计关系着企业的形象。</p><p>完成国际贸易运输所涉及的货主、货运代理、承运人这些主体，与港口、场、站、库等客体不能相混，兼营、交叉经营会使整个市场的竞争秩序出现混乱。而且，国际货运代理联系面广、环节多、专业性强，可以把国际贸易相当繁杂的货运业务集中协调、统筹和理顺。国际货运代理的形成，是国际商品流通过程的必然产物，是国际贸易必不可缺的组成部分。</p><p>随着社会生产力与竞争的发展，各国经济相互依赖，世界经济逐渐成为不可分割的有机整体，经济全球化已经成为强劲的时代潮流，企业国际化经营的趋势日趋明显。中国加入WTO也过十年，这给中国企业国际化经营而言既是绝好的机遇，也是严峻的考验和挑战。因为今天的市场竞争已不同于昨天，而是更加激烈和惨烈。</p><p>中国企业走向国际化是经济全球化的必然趋势，是企业发展到一定的必然选择。国际化经营不是企业发展的捷径和目标，而是循序渐进的突破，是实现企业目标的必由之路。在建站大师的自助建站，改变传统的经营观念，树立“走出去”的信心，策划企业国际化战略成为当务之急。随着互联网络应用，企业经营国际化因为纷纷趋于网络化，这不但使整个经营过程速度加快，更重要的使经营成本大大降低。全球范围内跨国界经营往往使企业组织结构分散而导致管理困难和成本上升，网站建设使企业组织机构和权力应用更趋集中，各经营环节或渠道信息得到更及时、有效和通畅，从而使管理者与市场更接近，能更好熟悉和控制市场运行脉搏，并从根本上降低管理成本和提高经营效益。贸易网站建设、物流网站建设、货源网站模板成本是企业经营决策中首选因素，其不仅关系到企业目前生存，而且关系到企业未来发展，因此，网络化是新世纪企业经营必然趋势。网络犹如肌体内的神经，能够适时适度调节和控制企业经营运行方向：网络犹如血管，能及时输送新鲜血液，激发或促进企业各部门组织新陈代谢活力。</p><p><br/></p>', '贸易、物流和货运，逐渐成为我国核心产业之一，而生活中我们越来越依赖于网络。它们都属于服务性行业，贸易网站建设和物流网站建设，是公司在发展过程中必不可少的，现在贸', 0),
(1924, 1, 43, 68, '', '<p>一提到家政首先映入脑海的是保姆，清洁工等等，文化较低的农村务工人员做的事情，于是无论从现实中寻找工作的人，或者在网络上点击，都很难把眼光投入到这边去。在这个商机面前，只要能放下偏见就依然可以找寻到机会。而人们对物业公司认识相对比家政更多，因为现在买房的人越来越多，交的物业费用让他们更有机会去了解物业，现在网络普及使用，家政公司和物业公司嗅到了网络上存在的巨大商机。对自己公司的网站建设和网站设计就成为必需品。现实中的广告要打响，网络上的一点都不能少，现在网民太多了。</p><p>因为现在越来越多的人把时间投入到紧张的工作中和业余时间的休闲活动上，愿意雇用专人处理家务。只不过随着生活水平的提高，人们对家政服务人员的素质要求也越来越高，不再满足于搞卫生，做饭，带孩子等体力劳动者，而是需要具备一点专业知识，在语言、技能、修养、心态等方面接受过专门培训，在接人待物、家庭营养、医疗护理以及教育孩子等方面有一定特长的“高级保姆”，这些都直接从网站上体现出来，每点开一个家政网站和物业网站，先去关注的是从业人员的情况，一个好的家政网站建设和物业网站建设，可以让家政或物业公司优势体现得淋漓尽致。而好的网站建设和网站设计，更需要有家政网站模板，物业网站模板的支持。Chinaz旗下的建站大师，给您全新的网站理念，自助建站功能轻松建站，无需担心对网络的不熟悉。</p><p>提高家政服务和物业行业的社会地位，要通过媒体等多种渠道的宣传，网络上的宣传力度更大，改变社会对家政服务业和物业的传统偏见，让更多的家政和物业网站，进入人们的视线，更优质的服务让人看到，接触到。网站设计得有特色可以让公司在众多网站中凸现出来，家政和物业成为人们在生活中不可缺少的一部分，家政和物业网站让更多人可以直接从网站上先了解公司有那些服务项目，哪些比较适合自己所需要的。PHPOK企业站系统让您自助建站轻松实现网站建设和网站设计。做出自己想要的网站，满足客户的需求。家政网站模板对家政网站设计是极其重要的，好的模板设计出来的网站美观，同时不失实用，同样物业网站模板和物业网站设计也能达到一样的效果。</p><p><br/></p>', '一提到家政首先映入脑海的是保姆，清洁工等等，文化较低的农村务工人员做的事情，于是无论从现实中寻找工作的人，或者在网络上点击，都很难把眼光投入到这边去。在这个商机', 0),
(1925, 1, 43, 68, '', '<p>新经济时代的来临，酒店和宾馆网站建设成功正悄然改变着人们的价值观念。工作环境和生活方式，酒店是最敏感的产业，应该最先体察客人的需求和消费观念的变化，跟上时代步伐，利用高科技来提供酒店的创新能力，提高增加企业的网站建设，提升自己的竞争优势。</p><p>信息技术已广泛运用和不断更新，网站建设也应向更广、更深层次发展。运用现代信息技术在原有酒店管理系统上建立一个高效、互动、实时的内部信息管理系统，酒店网站模板可以使原有组织机构打破部门界限，使用跨部门的团组，把决策权放到最基层。</p><p>宾馆其实就是酒店的一种形式，是从酒店的发展中分歧出来的。酒店的出现在人类商业活动逐渐频繁之后而产生的，古时称之为客栈，宾馆必须充分体现人性化理念，宾馆网站建设坚持“以人为本”，提倡亲情化、个性化、家居化，突出温馨、柔和、活泼、典雅的特点，满足人们丰富的情感生活和高层次的精神享受，网站设计适度张扬个性，通过多种形式创造出使客人舒心悦目.独具艺术魅力和技术强度的“作品”。酒店网站模板通过细小环节向客人传递感情，努力实现酒店和宾馆与客人的情感沟通，体现公司对客人的关怀，增加客人的亲近感，无形中带动人气和知名度上升。</p><p>另外网站设计必须充分体现超前性理念，所谓超前性，酒店网站建设（宾馆网站建设）要统筹考虑，既要绿色、环保，又要时尚，要不留遗憾。让客人从心理上产生赏心悦目的感觉，网站设计独特，创意新颖，造型别具一格，合适可以成为酒店和宾馆的标志性代表，设计必须深刻了解酒店的市场定位，深刻地研究如何创造酒店的形象并使其功能全面合理化。最终才能成功造就一个酒店，因此，网站设计要坚决反对抄袭之风，真正地根据每个酒店不同的要素，创造出各自的特色和形象来，PHPOK企业站系统里面的自助建站功能为酒店找个特定建筑围合的空间，它不仅要满足人们住宿、餐饮的要求，还要满足会以商务、娱乐、健身诸多方面的需求。它不仅是功能上的，还是精神上的，要让客人在入驻酒店及宾馆的同时，经历文化的感染和艺术的熏陶，无论商务还是度假，都有一种惊喜的体验。网站设计师们一定要合理的控制好网络空间，帮助业主在网络设计方面合理调配，有的放矢，用空间创造最佳的艺术效果。</p><p>客人在入驻酒店后，大部分时间均在客房度过，面对生活水准和欣赏力都日益提高的客人，国内大多数酒店客房无论从功能，面积，户型到客房中家具的款式、布艺、地毯的颜色，甚至在酒柜、衣柜的做法上都惊人的相似，不同的客人有不同的需求。在网站上要展示出不同的特色，吸引客户。让更多的人看到公司的特色，为公司吸引更多的客源。</p>', '新经济时代的来临，酒店和宾馆网站建设成功正悄然改变着人们的价值观念。工作环境和生活方式，酒店是最敏感的产业，应该最先体察客人的需求和消费观念的变化，跟上时代步伐', 0),
(1926, 1, 43, 68, '', '<p>农业发展到一定基础，随着水产品市场的全面开放，以及&quot;以养为主&quot;养殖发展方针的确立，使水产业生产力得到了空前的释放，水产品养殖产量大幅度提高，花色品种逐渐增多，产品鲜度和质量也有了很大的改善。水产品产量的增加使水产品市场供给有了根本改观，全国人均水产品占有量逐年提高。而网站使水产品能得到如此好的推广，也离不开网站建设与网站设计上做的文章，现如今网络传播广而快，这是不争的事实。<br/></p><p>  水产品不仅为国民提供优质的食物蛋白，而且还是出口创汇的重要产品，对提高我国和区域性的经济地位发挥着重要作用。改革开放以来，我国水产业发展迅速，规模不断扩大，品种增多，产量迅猛增加。随着世界互联网的加速发展的同时现代化工业，进入环境的有害、有毒物质越来越多，对人类的食用安全构成了严重威胁。健康技术和食品安全发展战略将成为我国21世纪水产研究的重要领域。但是对于农户们来说，除了农业部门的指导以往，只能在网络上得到想要了解的信息，对于特产网站建设，水产网站建设，养殖网站建设不仅仅是政府部门必须具备的了，广泛的养殖户也意识到网络带给他们的不仅仅是知识的增加，更需要养殖户们的相互交流经验。网络迅速的发展，在chinaz旗下的建站大师，自助建站等等为您打造互动平台的基础上，让您接触更多的同行以及网上专家，帮助打造一个属于自己公司的空间。</p><p>另外提高养殖工作者和广大养殖户水产品安全意识，扩大宣传效果。加大无公害水产品标准、规定的推广和实施力度。建设无公害水产品基地。也成为现在发展新的方向，现在生活水平的提高，让更多的人意识到安全食品的重要性，在网络上的传播就显得尤其重要，建站大师通过网站建设，和网站设计让更多的人，认识到按照“有企业、有注册品牌、环境检测达标、有标准或生产操作规程、有塘口档案、有渔药处方、有技术管理人员”的七大要求，做好无公害水产品生产基地的认定准备工作。在网络上大量宣传养殖单位与养殖户、饲料供应商、鱼药销售商签订了无公害养殖共保协议。为公司特产网站建设，水产网站建设，养殖网站建设提升服务，宣传养殖技术，让公司实现有地方来请专家讲课，并组织部分人员外出学习先进的无公害养殖管理经验，逐步实施养殖持证上岗等等，有地方可去学习的机会，</p><p>PHPOK企业站系统，为您公司的网站建设打造独特的类型。吸引客户以及提供互动平台，增加更多的经验。</p><p><br/></p>', '农业发展到一定基础，随着水产品市场的全面开放，以及&quot;以养为主&quot;养殖发展方针的确立，使水产业生产力得到了空前的释放，水产品养殖产量大幅度提高，花色品种逐渐', 0),
(1927, 1, 43, 68, '', '<p>新经济时代的来临，酒店和宾馆网站建设成功正悄然改变着人们的价值观念。工作环境和生活方式，酒店是最敏感的产业，应该最先体察客人的需求和消费观念的变化，跟上时代步伐，利用高科技来提供酒店的创新能力，提高增加企业的网站建设，提升自己的竞争优势。<br/><br/>信息技术已广泛运用和不断更新，网站建设也应向更广、更深层次发展。运用现代信息技术在原有酒店管理系统上建立一个高效、互动、实时的内部信息管理系统，酒店网站模板可以使原有组织机构打破部门界限，使用跨部门的团组，把决策权放到最基层。<br/><br/>宾馆其实就是酒店的一种形式，是从酒店的发展中分歧出来的。酒店的出现在人类商业活动逐渐频繁之后而产生的，古时称之为客栈，宾馆必须充分体现人性化理念，宾馆网站建设坚持“以人为本”，提倡亲情化、个性化、家居化，突出温馨、柔和、活泼、典雅的特点，满足人们丰富的情感生活和高层次的精神享受，网站设计适度张扬个性，通过多种形式创造出使客人舒心悦目.独具艺术魅力和技术强度的“作品”。酒店网站模板通过细小环节向客人传递感情，努力实现酒店和宾馆与客人的情感沟通，体现公司对客人的关怀，增加客人的亲近感，无形中带动人气和知名度上升。<br/><br/>另外网站设计必须充分体现超前性理念，所谓超前性，酒店网站建设（宾馆网站建设）要统筹考虑，既要绿色、环保，又要时尚，要不留遗憾。让客人从心理上产生赏心悦目的感觉，网站设计独特，创意新颖，造型别具一格，合适可以成为酒店和宾馆的标志性代表，设计必须深刻了解酒店的市场定位，深刻地研究如何创造酒店的形象并使其功能全面合理化。最终才能成功造就一个酒店，因此，网站设计要坚决反对抄袭之风，真正地根据每个酒店不同的要素，创造出各自的特色和形象来，chinaz旗下的建站大师，里面的自助建站功能为酒店找个特定建筑围合的空间，它不仅要满足人们住宿、餐饮的要求，还要满足会以商务、娱乐、健身诸多方面的需求。它不仅是功能上的，还是精神上的，要让客人在入驻酒店及宾馆的同时，经历文化的感染和艺术的熏陶，无论商务还是度假，都有一种惊喜的体验。网站设计师们一定要合理的控制好网络空间，帮助业主在网络设计方面合理调配，有的放矢，用空间创造最佳的艺术效果。<br/><br/>客人在入驻酒店后，大部分时间均在客房度过，面对生活水准和欣赏力都日益提高的客人，国内大多数酒店客房无论从功能，面积，户型到客房中家具的款式、布艺、地毯的颜色，甚至在酒柜、衣柜的做法上都惊人的相似，不同的客人有不同的需求。在网站上要展示出不同的特色，吸引客户。让更多的人看到公司的特色，为公司吸引更多的客源。</p>', '新经济时代的来临，酒店和宾馆网站建设成功正悄然改变着人们的价值观念。工作环境和生活方式，酒店是最敏感的产业，应该最先体察客人的需求和消费观念的变化，跟上时代步伐', 0),
(1928, 1, 43, 68, '', '<p>当相爱的情侣在跑完马拉松式的爱情长跑后，执子之手，与子偕老。步入神圣的婚姻殿堂，为了留住这段美好幸福的时刻。会想到用摄影来留住这个快乐幸福时光。于是选择一个好的婚庆和摄影公司对于新人来说是极其重要的，在网络上的选择和比较公司服务的好坏，成为人们的首选。而Chinaz旗下的建站大师能为您的网站设计一个形象好并且全面实用的网站。、</p><p>随着互联网的发展，网络已经深刻的改变着当今的市场格局。婚庆服务业已如火如荼的蓬勃发展，那么如何利用网络来增强婚庆服务业公司和摄影公司的综合竞争力，已经成为公司面临的首要选择，网站建设的必要性就体现出来了，婚庆网站建设和摄影网站建设是公司扩展业务的前提，目前市场上的婚庆公司良莠不齐，婚庆业还面临“小、散、弱”的局面，存在服务市场不规范、收费不够合理、规范监督机制缺失等问题，造成市场供需矛盾突出。比如：客户找不到服务。不敢接受服务，而服务企业又不知道谁需要服务.需要什么服务等，严重影响了行业发展和客户服务需求的满足。那么客户和婚庆企业双方如何顺利找到满意的合作伙伴呢？我们仍然想到了婚庆网站建设，摄影网站建设，让客户和婚庆和摄影服务公司零距离接触，以最快捷和明确的方式让客户来选择自己喜欢的。</p><p>目前我国婚庆公司网站提供的服务很有限，一般都是宣传和咨询类的，且地域性比较强，无法满足市场如此大的需求。建站大师里面的自助建站功能齐全，婚庆公司网站设计一个能够为结婚新人的婚纱摄影、婚庆策划、喜宴酒店、蜜月旅行等一系列婚庆事务提供了网络预定平台，网络的普及已经让人们跨区域的认识交流，比如：一些影楼还专门成立了网络销售部，以适应客户的需要。网络上的婚庆服务价格、服务质量、专业程度等各方面的比较也由此产生。网络的出现为婚庆和摄影服务业带来新的机遇但同时也面临着前所未有的挑战，人们不仅仅要求留住，更进一步要求有个性，有与众不同的创意。</p><p>网站建设的好，网站设计的美观实用，吸引着新人的眼球。PHPOK企业站系统让您的网站更具特色，不断的创新才可以吸引更多的客户来公司。</p><p><br/></p>', '当相爱的情侣在跑完马拉松式的爱情长跑后，执子之手，与子偕老。步入神圣的婚姻殿堂，为了留住这段美好幸福的时刻。会想到用摄影来留住这个快乐幸福时光。于是选择一个好的', 0),
(1903, 1, 43, 68, '', '<p>民以食为天”，由于进入门槛低，餐饮业一直是一个红火的行业。随着我国社会经济的发展，餐饮行业竞争激烈，赢利企业占比低主要是由于供应链采购系统中间成本高和餐饮企业营销方式落后。首先，供应链采购系统中间成本高。在现代企业竞争力不断强调供应链物流管理时，餐饮业这个古老的行业更需要在供应链采购创新上下工夫，从而降低中间成本，而开展电子商务是解决这一问题的好方式。其次，我国餐饮业目前的营销观念还很陈旧，经营者对消费者需求的了解还很不足。所以经营者只是凭感觉猜测消费者需要什么，而消费者对吃的变化是非常快的。同时，消费者也处于非常被动的地位，只能在餐馆做什么的基础上去选择自己所吃的东西。同时餐馆与餐馆之间消息非常闭塞，餐馆经营者之间关于信息，管理经验，厨师、服务员招聘，以及餐馆买卖的沟通非常少。因此开展网上营销对于拓宽传统的经营方式，提高竞争力有很大的意义。</p><p>电子商务技术在传统的餐饮行业中的运用，对餐饮企业具有变革性的作用。首先，电子商务中供应链技术的运用可以大大降低餐饮企业的采购成本。其次，电子商务中网络营销的实施可以为餐饮企业提供广阔的顾客渠道。第三，物流技术的运用可以为餐饮企业拓展销售的模式。运用电子商务推广，首先要建立属于企业自己的门户企业网站，那么企业在选择网站建设公司的时候一定要慎重，要选择具有权威，有规模的网站设计公司，这样才可以保证餐饮网站建设的质量。同时，颇具规模和经验的网站建设公司会为企业制定相应的网站建设，进行必要的网站设计，同时良好的后续服务可以避免出现企业网站“荒废”情况的出现。</p><p>“餐饮网站建设是一个综合工程，不仅仅是技术那么简单。”纯粹的一个网站摆在那里是不能创造任何价值的，粗枝烂叶的网站更可能带来负面的效果。只有在网站维护和更新上下足了力气，才能真正的发挥网站的功用。</p><p><br/></p>', '民以食为天”，由于进入门槛低，餐饮业一直是一个红火的行业。随着我国社会经济的发展，餐饮行业竞争激烈，赢利企业占比低主要是由于供应链采购系统中间成本高和餐饮企业营', 0),
(1904, 1, 43, 68, '', '<p><br/></p><p>随着中国经济发展水平的提高，人们越来越重视自身的健康，医疗服务消费早已突破了“有病求医”的观念，而随着因特网的发展计算机技术的不断成熟，人们也越来越依赖网络，于是各企业把主意打到医院诊所网站建设上。网站建设经这些年的发展后已多如牛毛，医院诊所保键网站建设的发展也日趋成熟。医疗消费动机表现出多层次、多样化的特点，美容、整形、康复服务正在悄然走俏，健康咨询、家庭保健等方面的潜在需求不断增长，以及保健品市场的一再升温、特需服务的产生等现象为医院开拓出了更多的市场。而PHPOK企业站系统能为您的网站设计一个形象好并且全面实用的网站。自助建站可以根据门诊部各种疾病分科室，例如口腔科、神经科、体检科、男科、内科、外科、眼科、皮肤科、外科、妇科、中医针灸等，网站设计更是可以只在网上就可以直接挂号看诊。</p><p>医院网站建设和保键网站建设服务所具有的公益性、事业性、商业性、常规性、突发性等特点相适应；医院自助建站不仅仅是给人看的，更是企业经营决策中首选因素，其不仅关系到企业目前生存，而且关系到企业未来发展，因此，网络化是新世纪企业经营必然趋势。医院网站建设将直接、明显影响到医院企业建站的发展性和收益性；有对医院网站形象和品牌提升的作用。</p><p>医院企业建站泛指医院网站主体和客体在长期的医学网站实践中创造的特定的物质财富和精神财富的总和。医院网站制作主要是设计站内的物质状态：医疗设备、医院建筑、医院环境、医疗技术水平和医院效益等有形的东西，其主体是物。医院网站文化是指医院在建站发展过程中形成的具有本网站特色的思想、意识、观念等意识形态和行为模式以及与之相适应的制度和组织结构，其主体是人。医院企业建站是医院网站建设形成和发展的基础。现在，两者是有机整体，彼此相互制约，又互相转换。医院自助建站是医院长期网站建设中逐渐形成的以人为核心的文化理论、价值观念、生活方式和行为准则等等。</p><p> 处于世纪之交的建筑，正经历着经济体制、医学模式和技术革命的三大变革。经济体制从计划经济转向社会主义的市场经济，医疗服务从供给型转向经营型；医学模式从生物医学转向生物、心理、社会医学，大大扩展了医学空间的深度和广度；技术革命使工业社会步入信息社会，医院智能化及覆盖全球的医疗信息网络，将极大地突破医疗的时空界限和原有格局。这三大变革将在中国医院的价值观念、功能结构、空间形态等方面产生强烈的震撼和影响</p><p><br/></p>', '随着中国经济发展水平的提高，人们越来越重视自身的健康，医疗服务消费早已突破了“有病求医”的观念，而随着因特网的发展计算机技术的不断成熟，人们也越来越依赖网络，于', 0),
(1905, 1, 43, 68, '', '<p>随着经济发展步伐的加快，金融行业项目分类的繁多,从线下走到了线上,金融网站建设、典当网站建设、拍卖网站建设已成为这些行业的必要配备。</p><p>它始终只是整个金融业和商业的一个分支或称边缘业种，但小额.短期.便捷.安全的融资特点，使其在支持生产.活跃流通.方便人民生活和满足投资需要等方面发挥了积极作用，成为银行等金融机构的有益补充，并逐渐得到肯定和重视。典当金融拍卖是相互关联的，PHPOK企业站系统的网站建站让金融网站建设，典当网站建设，拍卖网站建设体现我国典当金融拍卖的现状，网站设计出典当金融拍卖的优点和作用，以及典当金融拍卖的风险和防范。财产权利押或房地产抵押等形式向典当机构借贷的特殊金融方式，其本质特征与拍卖一样是以物换钱。PHPOK企业站系统中自助建站功能让这行业更清晰。</p><p>拍卖既有典型的买卖合同性质又有其特殊性。在一定条件下，拍卖行对拍品的瑕疵担保免职是拍卖行业的国际惯例，对这一免责条款，无论是理论界争议，还是现实生活中的操作。金融网站建设，典当网站建设，拍卖网站建设都是很重要的，在一定程度上，典当和拍卖围绕着金融的气息，网站设计的巧妙，可以更好的让金融网站建设，典当网站建设，拍卖网站建设，成为相互呼应，相互关联的信息平台。</p><p>发展的根本出路在于不断创新，加强自身建设。金融网站建设，典当网站建设，拍卖网站建设里面这包括企业形象创新、企业内部管理制度的创新和经营业务的创新。网站设计企业形象创新主要是指塑造良好的企业形象；自助建站使企业制度创新实质上就是其产权制度、管理制度、薪酬制度的创新；网站建设和网站设计是广义业务创新，不仅指品种的创新，它还包括方式、工具、技术创新。</p><p>金融和典当市场是市场体系中的一个重要组成部分，它具有市场体系的共性，又有自身特点，这些特点奠定了金融网站建设，典当网站建设，拍卖网站建设在市场体系中的特殊地位。全面。系统地考察金融市场是发展和利用金融市场的基础。</p><p><br/></p>', '随着经济发展步伐的加快，金融行业项目分类的繁多,从线下走到了线上,金融网站建设、典当网站建设、拍卖网站建设已成为这些行业的必要配备。它始终只是整个金融业和商业的一', 0),
(1906, 1, 43, 68, '', '<p>生物技术处理垃圾废弃物是降解破坏污染物的分子结构，降解的产物以及副产物，大都是可以被生物重新利用的，有助于把人类活动产生的环境污染减轻到最小程度，这样既做到一劳永逸，不留下长期污染问题,同时也对垃圾废弃物进行了资源化利用。环保生态网站建设，生物技术网站建设相互联系，让更多的人认识到环保和生物技术的重要性。</p><p>大多数生物治理技术可以就地实施，而且不影响其他作业的正常进行，与常常需要高温高压的化工过程比较，反应条件大大简化，具有设备简单、成本低廉、效果好、过程稳定、操作简便等优点。所以，环保生态网站建设，生物技术网站建设使当今生物技术已广泛应用于环境监测、工业清洁生产、工业废弃物和城市生活垃圾的处理，有毒有害物质的无害化处理等各个方面。PHPOK企业站系统里面的网站设计体现生物技术给人类带来的益处也包括在生态和环境两个方面。利用生物技术提高现有农业生态系统的生产力可以减低农业向原始的、自然、半自然生态系统扩张的要求，因此，它有助于有人类保存、保护地球上仅有的自然生态系统及其资源，有助于人们未来再利用其中的基因资源开发新的产品。</p><p>从经济角度上讲，生物技术带来的不利并不明显，然而，它会引起发达国家与发展中国家贫富差距进一步扩大。因为，生物技术公司网站设计体现主要集中在发达国家，发达国家可以通过输出生物技术产品而获得利润。与此同时，发展中国家由于技术、及其产品还远没有被广泛接受。 生物技术也可能引发环境问题。网站建设展示人们利用生物技术生产出抗旱、耐盐、抗病虫害作物同时，也导致生物多样性遭受严重破坏，甚至导致一些物种灭绝。</p><p>网站设计体现生物技术作为科学和技术在这场变革中将起到关键性的作用。原则上讲，生物技术和环保生态本身有能力帮助人们提高农业生产力和保护环境，但在实践中，生物技术作为环境保护的代理人其作用相对来说是微乎其微的。环保生态网站建设，生物技术网站建设人们对它在环境保护以及促进人类进步中的作用仍将拭目以待。生态环保和生物技术已成为当前国际关系、经贸合作中的一个极为重要的问题，也日益严重地影响着我国国民经济的可持续发展。在我国过去几十年的经济发展中，由于忽视了发展中的环境保护，目前环境状况十分严峻。近年来虽采取了大量控制措施，但环境质量下降的趋势仍在继续。</p><p>当前我国社会经济仍然保持着高度发展的态势，环境保护的压力将进一步加重，PHPOK企业站系统里面的自助建站功能，让环保和生态问题展示在人们面前。网站设计的好以及由人类活动所造成的环境污染和环境质量的恶化已成为制约我国社会和经济可持续发展的障碍。如何在经济高速发展的同时控制环境污染，改善环境质量，以实现社会经济可持续发展之目标是我国目前亟待解决的重要问题。</p><p><br/></p>', '生物技术处理垃圾废弃物是降解破坏污染物的分子结构，降解的产物以及副产物，大都是可以被生物重新利用的，有助于把人类活动产生的环境污染减轻到最小程度，这样既做到一劳', 0),
(1907, 1, 43, 68, '', '<p>未来的广告会展人已经不是普通的高薪阶层，21世纪的广告会展印刷业将真正形成知识密集、技术密集、智慧密集的一大产业，广告会展网站建设，印刷网站建设正以其强劲的发展势头，成为一支新兴产业的劲旅，正成为被人们看好的，是极具发展潜力的朝阳产业。</p><p>广告会展具有强大的经济功能，包括联系和交易功能、整合营销功能、调节供需功能、技术扩散功能、产业联动功能、促进经济一体化等。网站建设和网站设计联系和交易功能让会展孕育巨大商机，具有联系和交易功能。网络上宣传会展的联系沟通作用非常明显：联系量大、联系面广、联系效果好，因此会展可以向会展组织者、参展商、观众提供彼此联系和交流的机会。通常在短短几天有限的会展期间，参展商往往可以接触整个行业或市场的大部分客户，可能比登门拜访等其他常规方式一年甚至几年所接触的客户还多。</p><p>参加者在专业展会上可以接触到行业主管部门领导、本领域专家、现有客户、潜在客户、供应者、代理商、用户等与己相关的各种角色的人，其中不乏决策人物、关键人物，形成的人际联系质量高。会展的环境氛围典雅，有利于进行高质量的交流。印刷的广告贸易成交一般有若干环节：生产厂家向客户宣传产品，客户产生兴趣并进行询问了解，客户产生购买意向，厂家与客户洽谈，讨价还价成交。通常这个过程有时可能比较长，但在展览会上，这一过程可以比较迅速完成。PHPOK企业站系统，让广告会展网站建设，印刷网站建设丰富的信息、知识交流传播使得生产、贸易、生活趋于更轻松、直接、快捷、准确，消除了供求中的许多不确定因素，产生高效低耗的经济功能，创造了经济均衡的巨大可能性。网站建设让展销会上的参展商为卖而参展，参观者为买而参观，均有备而来。广告会展网站建设，印刷网站建设让参展商可以在有限的时间内最广泛地接触买主，观众购买商可以在有限的空间里最广泛地了解产品，参展商可以于潜在客户在网站自主建站功能上表示出兴趣时就抓住机会开展推销、洽谈工作，直至成交甚至当场回款，买卖双方可以完成介绍产品、了解产品、交流信息、建立联系、签约成交等买卖流通过程，展会起到沟通和交易作用。</p><p>广告会展网站建设，印刷网站建设具备了其他营销工具的相关属性：作为广告工具，网站设计媒介将信息针对性地传送给特定用户观众；作为促销工具，网站刺激公众的消费和购买欲望；作为直销的一种形式，可以直接将展品销给观众；作为公共关系，会展网站具有提升形象的功能。</p><p><br/></p>', '未来的广告会展人已经不是普通的高薪阶层，21世纪的广告会展印刷业将真正形成知识密集、技术密集、智慧密集的一大产业，广告会展网站建设，印刷网站建设正以其强劲的发展势', 0),
(1908, 1, 43, 68, '', '<p>无论是国内还是国外，行业协会和机构的产生、发展和壮大始终与经济因素息息相关。协会网站建设，机构网站建设在社会转型时期，国家因素则逐渐加入，网站建设的必然形成，但是归根结底还是在生产力发展的情况下为了满足维持经济发展的需要。</p><p>建站大师里面的网站设计现代意义上的行业协会和机构已经成为联系政府和市场的纽带。随着我国全面建设中国特色社会主义市场经济的理论和实践的进一步深化，在新的历史条件下，如何全面认识行业协会的实质内涵和法律地位，解决行业协会的若干深层次问题，保障其健康有序地发展，这是对机构协会进行研究所必须面对的难题。近年来随着我国加入WTO，政府职能进一步转换，协会网站建设，机构网站建设成就了机构协会在我国市场经济中发挥的作用越来越大。但是作为后进的经济转型国家，相比于国外成熟完善得多的机构协会法律制度，我国目前的网站建设还带有相当浓厚的官办痕迹和计划经济色彩，远远不能适应现代市场经济发展和经济全球化的需要，但是，PHPOK建站系统里面的自助建站功能让商会甚至非营利组织的层面上看到专门立法，而且笔者认为，对于后进国家而言，要想促进行业协会的健康发展，自上而下的制度推动是必不可少的，所以网站建设和网站设计上有必要制定专门的行业协会，这是发挥我国后发优势的重要途径。</p><p>从不同层面反映了当前行业协会研究的现状，其共同基调也都是希望为我国行业协会制度建设进言。但是在笔者看来，协会网站建设，机构网站建设尚缺乏通过对各国行业协会的法律制度进行比较研究，从而在法治层面（尤其是经济法层面）上对我国行业协会进行制度构建的相关研究。所以本文试图在对国外相关法律制度比较研究的基础上，结合我国的实际国情，对行业协会若干法律问题进行探讨。</p><p>该改变过去行业协会功能过于单一的局面。我国行业协会长期以来一直过于偏重管理职能，当然这并不是意味着管理职能不需要，但决不应局限于此。网站建设让成员企业的多元化的利益诉求必须以行业协会职能扩大、功能多样为保障。自助建站真正让行业协会行使一部分原政府行使的职能，如制定行业标准，监督成员企业执行产业政策，信息咨询与发布等等，使得行业协会真正代表企业，服务企业。</p><p><br/></p>', '无论是国内还是国外，行业协会和机构的产生、发展和壮大始终与经济因素息息相关。协会网站建设，机构网站建设在社会转型时期，国家因素则逐渐加入，网站建设的必然形成，但', 0),
(1909, 1, 43, 68, '', '<p>美容休闲养生，是以个人的文化修养为背景，以探求和享受文化生活为目的，以获得现实生活中个人的心理满足、精神愉悦、身体健康为目标的生命活动。休闲养生中的健康问题非常突出，它们既有对人的身体有益的一面，也存在对人体健康不利的问题。如何解读这些问题，给大众的健康休闲开辟一条正确的路径，让大众在休闲养生中避害趋利，获得健康的知识和享受，是许多人既关注又困惑的问题。通过上网来了解和选择比较公司服务的好坏，成为人们的首选。而PHPOK建站系统能为您的网站设计一个形象好并且全面实用的网站。网站建设是现在互联网行业必争的一块大蛋糕，市场的庞大说明企业公司乃至个人的网站建设意识都很强烈。</p><p>美容美发网站建设是一个全新的信息发布平台，它首次引入“针对性”“专业性”便捷性的概念，将专业且有针对性的信息让广大用户更方便快捷的看到、找到、做到。休闲网站建设和养生网站建设专注于各个地区相关信息，独树一帜的页面风格，覆盖 全国软硬件基础设施以及强大的搜索表现，已经显示出良好的专注、专业、高效、权威等特PHPOK建站系统点，并已得到广大客户的认可和喜爱，正在成为优秀著名的行业自助建站。建站大师的自助建站功能齐全，而投资管理、美容、美发、形象设计、美体塑身、养生保健等更是网站设计中必要功能。</p><p>2011年，美容养生网站建设已经成为全国各地用户搜索相关信息的最主要平台，在全国范围内登陆用户超过350万，日浏览量最高突破26万次，是最受推崇的互联网品牌。美容自助建站式为用户提供全面相关信息以及求购信息，轻松自由地把美容养生资讯尽展眼前，通过与国内外数家大型门户平台、搜索平台以及各大主流媒体的合作，更成为过亿互联网用户不可或缺的部分。</p><p>美容美发企业建站一直致力于倾听、挖掘与满足用户的需求，秉承“用户至上”的理念，保证信息的真实性,有效性是本站的服务宗旨。</p><p><br/></p>', '美容休闲养生，是以个人的文化修养为背景，以探求和享受文化生活为目的，以获得现实生活中个人的心理满足、精神愉悦、身体健康为目标的生命活动。休闲养生中的健康问题非常', 0),
(1910, 1, 43, 68, '', '<p>随着第一粒咖啡豆被人们采摘下来、第一次焙烤、第一次研磨、第一次冲调和第一杯热咖啡醇香的飘散，有关咖啡种植和咖啡文化在我们这个小小的星球上传播的传说，已经成为人类文明史上最伟大、最浪漫的故事之一，随着时间的推移，这些浪漫的故事已经在人们日常生活消费中体现了。而咖啡在中国的消费仍处于草木萌动的阶段。</p><p>在国民经济持续发展，生活水平不断提高，人们消费观念、生活习惯的转变，以及文化思潮的多元化等趋势，“咖啡经济”确实还有无可限量的发展空间。据预计，中国将成为全球最大的咖啡消费国而非生产国，看似前方一片坦途，但咖啡市场却不是我们臆想中的一片繁荣，它遇上了种种瓶颈。那么究其原因，咖啡行业的发展除了强大的消费能力和可靠的市场还需要什么？还需要通过网络传播推广，于是建立网站就成为网络推广不可缺少的事情。当然也有些企业是跟风上网，网站建好之后就不再打理。</p><p>当前很多企业都完全是跟风，看见对手有网站，自己就想也找一家网站建设公司帮忙建设一个网站，但是网站设计好之后就放着当摆设。很多时候，客人进入企业网站，会发现上面的信息几乎是建设时发布的，根本就没有更新。在网络盛行的年代，企业的网站就是“第一门面”，客户的第一印象在进入公司网站的时候就建立了。因此，企业网站建设质量直接影响着客户的抉择。企业进行网站建设，是为了开辟广阔的互联网市场，建立企业与消费者无间隙的沟通联系。所以企业在选择网站建设公司的时候一定要慎重，要选择具有权威，有规模的网络公司，这样才可以保证网站建设的质量。同时，颇具规模和经验的网站制作公司会为企业制定相应的网站建设，进行必要的网站设计，同时良好的后续服务，避免企业网站“荒废”情况的出现。</p><p>“咖啡网站设计是一个综合工程，不仅仅是技术那么简单。”纯粹的一个网站摆在那里是不能创造任何价值的，粗枝烂叶的网站更可能带来负面的效果。只有在网站维护和更新上下足了力气，才能真正的发挥网站的功用。</p><p>即便是“跟风上网”的网站，只要找到优质的网站建设公司，获得他们的网站更新维护支持，也可以取得意想不到的效果。</p><p><br/></p>', '随着第一粒咖啡豆被人们采摘下来、第一次焙烤、第一次研磨、第一次冲调和第一杯热咖啡醇香的飘散，有关咖啡种植和咖啡文化在我们这个小小的星球上传播的传说，已经成为人类', 0),
(1911, 1, 43, 68, '', '<p>旅游网是旅游组织向公众展示旅游信息的平台，有官方旅游网站，也有私人旅游网站，官方的侧重政务，私人的侧重旅游市场及宣传，向广大旅游朋友提供旅游相关信息资讯、产品等信息。中国的旅游网在1996年开始出现。旅游是大众趋势，互联网已经成为最大的传媒之一，因此旅游网站建设发展速度非常快，每年都有成千上万家旅游网站出现，目前具有一定旅游资讯能力的旅游网有5000多家，其中专业旅游网300余家。</p><p>一个好的旅游自助建站通常具备以下的功能：景点介绍、旅游游记、线路自助(拼盘)、旅游问答。</p><p>景点介绍是任何一个旅游网站设计中必须有的功能，给网友一个直观系统的景点认识;但是如果只是单一的景点介绍页面也太单薄，通过景点介绍来串联景点周围的吃、住、行、购物，尽量给用户提供完整详尽的参考信息。作为企业旅游自助建站和旅游网站建设可以很好的让公司让更多的人认识，网站设计的好吸引更多的客户来尝试。</p><p>旅游游记是对景点介绍的重要补充，景点介绍基本是固定不变的，但是游记是根据每个游客的感受写的，每篇游记都具备可看性，大部分旅游网站模板吸引点也都依靠游记;我们一般通过看游记来了解当地的吃、玩、行、购物及相关费用，网站设计中游记功能要突出特色，按天编写游记并提供游记记帐功能，等于帮读者做了简单的整理，你在看游记时候能一目了然的明白游记作者在某个景区玩了几天，每天玩哪些景点，那里有什么好吃的、好玩的，买什么特色产品，每天花在交通、旅游、餐饮、住宿、购物上多少钱，整个旅游花了多少钱;这样任何看了游记想去游玩的朋友就比较好做财务预算。</p><p>网站建设就须用发展的观点来认识旅游这一观念。因为现代社会中的旅游不同于古代文人的游山玩水或徐霞客式的旅行和科学考察。它是人类社会中一种不断发展的生活方式。关于这一点，国外一些学者也有同类的叙述。如英国伊什图里金（Estoril）就指出过旅游的性质在逐渐发生变化，主要表现在：①娱乐旅行概念发生了变化。②现代旅游是闲暇追享的“民主化”。 。③现代旅游发展为“社会旅游”。“吃、住、行、游、购、娱”，简单的六个字，但它的形成过程却经历了半个世纪，凝集了几代人的心血，集中了成千上万旅游工作者的智慧。这六个要素，现在是中国发展旅游业的根本，指导旅游业的规范，衡量旅游业的标准，同时，也是我们广大导游员进行导游安排时必须考虑的六个要点、六个方面。了解了它的形成过程，有助于在实际导游工作中更好的为游客服务。</p><p><br/></p>', '旅游网是旅游组织向公众展示旅游信息的平台，有官方旅游网站，也有私人旅游网站，官方的侧重政务，私人的侧重旅游市场及宣传，向广大旅游朋友提供旅游相关信息资讯、产品等', 0),
(1912, 1, 43, 68, '', '<p>撇开家政和物业通过网络来实现的效果，家政和物业面临着更为严峻的生存和发展问题。在当今市场环境下，家政网站建设和物业网站建设使企业获得更大的生存和发展空间，降低了各种成本，提高利润。企业管理信息化，这需要充分利用信息资源、把握市场机遇，完美的网站设计更好组织人力、物力、财力进行生产经营活动。</p><p> 一个现代的企业，要能生存才是最重要的，能生存且具有其它企业所不能及的竞争优势，才是企业能长久生存之道。这就需要选择合适的家政网站模板，物业网站模板，网络成本低、品质好、交货时间短、生产弹性大是现在以及可预见的未来，客户们的主要诉求。因此要找出如何做好且领先同行业的方法，彻底执行，以建立企业特殊的竞争优势，家政网站建设和物业网站建设，能让企业更好的展示自己，让其他企业可望但不容易学到的竞争优势。而更重要的是一种先进思想：如果借助于建站大师的网站建设和网站设计来实现就有效果。</p><p> 网站技术已逐渐向数字化、智能化、宽带化和综合化的方向发展，因此，我们在规划企业网络化、信息化发展目标时，既要着眼当前的生产经营、管理和通讯、计算机的需要，更需要考虑网络中长期发展的规划。这就需要给力的家政网站模板和物业网站模板。让公司更全面的展示自己的优势。建站大师里面的自助建站功能，能更好的体现公司网站品牌形象。</p><p>打造物业和家政企业品牌是企业自身发展的需求,也是整个行业发展的需要。实施品牌化发展战略，走品牌化发展道路，不仅是发展的需要，也是行业发展的必然选择。行业的发展过程，也是企业求生存求发展的过程。要想更好地参与国际化的市场竞争中保持优势，就必须有好的推广，宣传。网络上的宣传成本低，效率高，传播面广。让需要的人认识到公司有一批熟悉法规、运作方式和操作技能的人才队伍，有一批实干的、团结的、进取的战斗团体来共同打造并树立起自己良好的物业品牌，才能在行业中占有一席之地。因此，选择PHPOK建站系统里面的家政网站模板和物业网站模板，能创建出更好的网站。来为自己的品牌做宣传。</p><p>如果具有良好的品牌，则可以让现实的与潜在的客户、对物业和家政增加更多一些信心。因为在客户的眼中，物业公司和家政公司才是和他们“同呼吸，共命运”，并将长期和他们共处的单位。</p><p>  PHPOK建站系统，让您和客户将这种关系更长久的维持下去。</p><p><br/></p>', '撇开家政和物业通过网络来实现的效果，家政和物业面临着更为严峻的生存和发展问题。在当今市场环境下，家政网站建设和物业网站建设使企业获得更大的生存和发展空间，降低了', 0),
(1913, 1, 43, 68, '', '<p>这些年来随着我国经济的发展旅游行业也是欣欣向荣，也就有越来越多的旅游公司需要旅游网站建设，因此市场上也就多了很多的网站建设的公司提供旅游网站建设的服务。</p><p> 但是一般网站建设公司只是提供给你旅游网站的建设，旅游网站设计，或者卖给你旅游网站模板其他的就没有了。这也导致了很多旅游的网站没有人维护也没有人推广，这其实就变成一个没有用的网站，所以要做旅游网站就得必须选择一家可以信赖的网站建设公司来帮助你进行规划。这边推荐PHPOK建站系统，他们提供一条龙的自助建站服务，为你免费的进行旅游网站设计，助你轻松完成旅游网站建设，并有成百上千的网站设计师、网站工程师提供专业服务。目前网站上面有非常多的旅游网站模板供你挑选，选好之后注册，10分钟之内就能看见你的旅游网站了，省事。并且建站大师提供了良好的售后服务保证你的旅游网站能够得到最好的资源。</p><p>那么旅游网站建设好了之后要如果开始运营推广呢？笔者根据自身体会进行了简单归纳为四点，希望对你有帮助。</p><p>第一种：首先旅游不可或缺的一定要酒店、机票预订等。依靠酒店、机票预订等为主要盈利点，酒店网站、机票网站的代表有：携程网、去哪儿网，艺龙等。其中去哪儿的模式不仅仅是单纯的预订业务，其主要方式是比价平台。这类网站已经以携程的一枝独秀而变得没有太大市场可以进入了，所以这种方式已经不适合中小站长选择了。</p><p>第二种：以旅游线路产品为主要盈利点，做旅游网站的B2C商城。此类网站的代表有：同程网，驴妈妈，欣欣旅游，途牛等。搭配着产品来进行销售推广，这类网站也是旅游网站模式中最多被选择的一种，因为盈利模式清晰，但同时也要求网站运营方对旅游产品足够的熟悉，以及对旅行社行业足够的了解，目前已经有这么几家同类佼佼者了，再进入有些以卵击石的感觉。</p><p>第三种：以驴友户外为主的论坛社区，比如8264户外资料网，蚂蜂窝，穷游网等。其中8264以户外路线为主，蚂蜂窝以旅游游记分享为主，同为相对专业的驴友论坛社区模式。</p><p>第四种：各个旅行社旅游公司的旅游门户网站，也是以旅游线路产品为主。</p><p>以上四种是目前网络旅游网站的大致分类，其中第一种与第二种仅仅是侧重点不同，内容均有所交集，而且均以大公司企业化来运营，方式方法成熟，这两种方式及第四种方式已经不适宜目前中小站长来选择进入了，那么第三种呢?</p><p>我认为第三种的模式大家可以考虑选择，但是方式方法一定要选择清楚，因为同类网站已经有做的出类拔萃的同行了，那么我们如何细分一下市场呢?我们只做一个地区如何，找网站建设公司做一个某某地区的旅游网站建设服务，然后把这个地区内同行业人士聚集到一起，同样组织活动及网络推广，这样在当地就有一定的优势，也能避开同类网站的直接竞争。</p><p>我认为我们草根站长做旅游社区的话要以一种开放的心态来做，大家做论坛社区均不想有广告帖子的出现，那样怎么能行呢?浏览者也是需要有广告的，不然三五个人找个旅行团报名还要去找报纸看，去一家家打电话问，为什么就不能做一个当地的旅游专业社区服务大家呢？开放OPEN，只有服务好了本地区的网友，提供了信息服务，才能有宣传，才能有口碑。旅游网，我们要做口碑!</p><p><br/></p>', '这些年来随着我国经济的发展旅游行业也是欣欣向荣，也就有越来越多的旅游公司需要旅游网站建设，因此市场上也就多了很多的网站建设的公司提供旅游网站建设的服务。 但是一般', 0),
(1914, 1, 43, 68, '', '<p>最近把自己制作的旅游网站模板拿到了：PHPOK企业站系统的平台进行展示，主要是为了帮助更多需要旅游网站建设的站长门提供一个自助建站的帮助。今年来随着经济的发展旅游行业也是星星向荣，慢慢的就有越来越多的旅游公司需要旅游网站建设，旅游网站设计合作已经有了网站需要一个旅游网站模板等等。因此市场上也就多了很多的网站建设的公司提供旅游网站建设的服务。</p><p>但是旅游公司只是提供旅游网站的建设，旅游网站设计，或者卖给你旅游网站模板其他的就没有了。因此也导致了很多旅游的网站没有人维护其实也是一个没有用的网站，所以要做旅游就得先选择一家可以信赖的网站建设公司来帮助你进行旅游网站建设。这边推荐：PHPOK企业站系统，：PHPOK企业站系统，他们提供一条龙的自助建站服务，为您免费的进行旅游网站设计，并且完成旅游网站建设，放心他们有专门的设计师，网站上面有非常多的旅游网站模板供你挑选，选好之后注册，ok几分钟之内就能看见你的旅游网站了，省事。并且他们有良好的售后服务保证您的旅游网站能够得到最好的资源。</p><p>那么旅游网站建设好了之后要如果开始旅游网站的运营呢，笔者进行了简单的归纳希望能够帮助您。</p><p>第一种：首先旅游不可离开的一定要酒店，机票预订等。依靠酒店、机票预订等为主要盈利点，酒店网站、机票网站的代表有：携程网、去哪儿网，艺龙等。其中去哪儿的模式不仅仅是单纯的预订业务，其主要方式是比价平台。这类网站已经以携程的一枝独秀而变得没有太大市场可以进入了，所以这种方式已经不适合中小站长选择了。</p><p>第二种：以旅游线路产品为主要盈利点，做旅游网站的B2C商城。此类网站的代表有：同程网，驴妈妈，欣欣旅游，途牛等。搭配着产品来进行销售推广，这类网站也是旅游网站模式中最多被选择的一种，因为盈利模式清晰，但同时也要求网站运营方对旅游产品足够的熟悉，以及对旅行社行业足够的了解，目前已经有这么几家同类佼佼者了，再进入有些以卵击石的感觉。</p><p>第三种：以驴友户外为主的论坛社区，比如8264户外资料网，蚂蜂窝，穷游网等。其中8264以户外路线为主，蚂蜂窝以旅游游记分享为主，同为相对专业的驴友论坛社区模式。</p><p>第四种：各个旅行社旅游公司的旅游门户网站，也是以旅游线路产品为主。以上四种是目前网络旅游网站的大致分类，其中第一种与第二种仅仅是侧重点不同，内容均有所交集，而且均以大公司企业化来运营，方式方法成熟，这两种方式及第四种方式已经不适宜目前中小站长来选择进入了，那么第三种呢?</p><p>我认为第三种的模式大家可以考虑选择，但是方式方法一定要选择清楚，因为同类网站已经有做的出类拔萃的同行了，那么我们细分一下市场呢?我们只做一个地区如何，找网站建设公司做一个某某地区的旅游网站建设服务，然后把一个地区内同行业人士聚集到一起，同样组织活动及网络推广，这样在当地就有一定的优势，也能避开同类网站的影响。</p><p>我认为我们草根站长做旅游社区的话要以一种开放的心态来做，大家做论坛社区均不想有广告帖子的出现，那样怎么能行呢?浏览者也是需要有广告的，不然三五个人找个旅行团报名还要去找报纸看，去一家家打电话问，为什么就不能做一个当地的旅游专业社区服务大家呢!开放OPEN，只有服务好了本地区的网友，提供了信息服务，才能有宣传，才能有口碑。旅游网，我们要做口碑!</p><p>一时的思绪也就整理了这么多吧，欢迎大家一起探讨，如果需要旅游网站建设可以到PHPOK企业站，咨询客服自助建站流程，然后完成旅游网站模板的选择或者和设计师沟通进行旅游网站设计，沟通好后您的旅游网站建设就交给PHPOK企业站完成吧。</p><p><br/></p>', '最近把自己制作的旅游网站模板拿到了：PHPOK企业站系统的平台进行展示，主要是为了帮助更多需要旅游网站建设的站长门提供一个自助建站的帮助。今年来随着经济的发展旅游行业', 0),
(1915, 1, 43, 68, '', '<p>随着工作压力越来越大，放松自己的机会越来越少，在这种高节奏的生活环境下，更多的人想到了用旅游的形式放松自己。随着旅游的兴起，旅游网站成为不可或缺的一部分，它让旅游者更清楚的认识到自己应该去哪里比较好，哪里可以让自己紧张的大脑得以调节，因此旅游网站建设就显的尤其重要。</p><p>随着旅游行业的不断发展，各家旅游行业之间的竞争日益激烈，旅游部门所需的信息量越来越大，业务操作中涉及的各种线路情况，客户情况以及旅游协作部门的情况越来越复杂多变，而除了一些个别地区已采用了的旅游网站，一般通常是以原始的手工方式处理/交流信息。但是工作人员若仅靠手工方式处理大量资料，很可能带来出错率的增长以及大量资源的浪费和闲置等问题。因此，好的旅游网站设计不仅加强对旅游信息资源的整合，统一管理，使旅游部门运行更加合理，高效地运转。</p><p>作为旅游社的负责人点开百度搜索，您可以看到到处是网站建设，网站建设的价格和网站设计的美观是您考虑的同时，相信您也很在乎网站的管理。313建站大师针对您的需求为您打造了适合您的旅游网站建设，旅游网站模板，旅游网站设计。最重要的是有自助建站简单便捷的流程，便于您的管理。</p><p>根据旅游行业的这种现状，采用现代化统一的计算机网络系统，实现了旅游管理的网络化，各类信息有序地进行存储，同时采用了权限认证的方式，只有经过了系统的权限认证之后，方可进入系统的主控制界面，进行信息管理，信息查询，在线预定，留言簿等功能的使用。实现了各种业务系统的数据集成和信息采集，对旅行社各类信息、资源进行协同集中管理，这就需要有好的网站建设公司，实现旅游信息快速发布及接受游客的网上预定。</p><p> </p>', '随着工作压力越来越大，放松自己的机会越来越少，在这种高节奏的生活环境下，更多的人想到了用旅游的形式放松自己。随着旅游的兴起，旅游网站成为不可或缺的一部分，它让旅', 0),
(1916, 1, 43, 68, '', '<p>最近随着人们经济条件的不断提高，简单的物质生活已经不能满足经济条件优越的人们的需求了，他们开始追求精神上的满足。如外出旅游、时尚度假等等已经成为人们的一种精神粮食，成为精神上的放松的一种方式。同时为了适应现代潮流，符合现在人们的需要，全国各地都开发了许多的旅游景点，旅游景点多了人们选择的机会也就多了。</p><p>旅游消费逐渐成为人们新的消费热点之一，通过网络这个平台一方面让人们更准确、更详细的了解旅游方面的动态，让人们在节假日期间有更多的娱乐节目来丰富自己的生活，来放松自己的生活；另外一方面也可以了解一下世界各地的大好河山，去游历，去体验，去环游世界，让世界尽收眼底。还有一个方面就是可以给各个酒店和各个景点的旅游业带来发展机遇，带动地区经济的发展，旅游网站也就成为人们获得旅游资讯的一个重要途径，也是人们快速得到旅游方面资料的必要途径，让人们坐在家里就能得知世界旅游信息。因此好的旅游网站建设和网站设计对于旅游公司就非常重要。</p><p>这也意味着在互联网进入一个崭新的发展阶段，信息化的发展带动其他产业的发展，各行业都将进行更深入的融入和渗透。旅游网站成为人们快速获取，发布和传递信息的重要渠道，它在人们政治、经济、生活等各个方面发挥着重要的作用，为了适应知识经济社会的需要，促进学习和交流。网上交流和协作的功能比较普遍，技术管理和资源管理受到重视。随着互联网的普及和发展，必将有越来越多的企业及个人在因特网上拥有自己的网站，网站建设和网站设计为企业形象宣传、产品展示推广、客户沟通的最新最快捷的桥梁；好的旅游网站建设，旅游网站模板，旅游网站设计成为个人展示自我，与世界交流的重要平台。越来越多的人开始从对互联网陌生阶段进入到认同和行动阶段。Internet上发布信息主要是通过网站来实现的，获取信息业是要在Internet“海洋”中按照一定的检索方式将所需要的信息从网站上下载下来。因此网站建设在Internet应用上的地位显而易见，它已成为政府、企事业单位信息化建设中的重要组成部分，从而倍受人们的重视。为了更好的协作，更多的与外界交流新的信息，和他人共享信息，特构建旅游网站。</p><p>PHPOK企业站系统，技术过硬的网站建设团队，良好的网站建设一条龙服务，是企业自助建站的一个不错的选择，旅游业具有“无烟产业”和 “永远的朝阳产业”的美称，但我国旅游产业仍然基础薄弱，管理手段滞后，信息化程度低，企业效益较差。随着业务的不断扩展，旅游社业务操作中涉及的各种收费情况，客户情况以及旅游线路情况越来越复杂，业务操作人员若仅靠手工方式处理大量资料，则遗漏信息的现象更容易发生，同时也可能带来出错率的增长以及大量资源的浪费和闲置等问题。因此，只有加强对旅游资源的整合、统一管理，才能使旅游部门更加合理、高效地运转。这在无形中促进了旅游网站建设的需求，于是选择一个好的旅游网站模板为旅游网站设计提供一个完美的基础，更能突出网站的个性，吸引旅游爱好者的眼球。</p><p><br/></p>', '最近随着人们经济条件的不断提高，简单的物质生活已经不能满足经济条件优越的人们的需求了，他们开始追求精神上的满足。如外出旅游、时尚度假等等已经成为人们的一种精神粮', 0),
(1917, 1, 43, 68, '', '<p>人们在解决了衣食住行的问题后，有了多余的时间和费用时，想用什么方式让自己紧张的神经放松下来，于是普遍的把眼光投入到外出旅行上，让自己增长见识的同时，身心的到放松。随着网络进入千家万户后，人们习惯性的通过网络来寻找自己想去的旅游景点等信息，这样的需求使旅游网站建设就成了一种必然的趋势，而好的旅游网站模板,能让旅游网站设计以最好的一面展示给游客，chinaz旗下的建站大师，就让网站建设变成最简单的建站方式，以自助建站为最简洁方便，易用。</p><p>旅游业是“朝阳产业”，是“无烟工业”，是“投入少，回收快，回收高”的经济产业……。从改革开放起，旅游产业被冠以各种各样的头衔，全国各个地方都在发展旅游经济，要把旅游业建成当地的支柱产业，而且有好多地方也确实尝到了甜头，例如早些年的周庄，近几年的西塘，都是由一个不为人知的小村庄变成了旅游者的梦中水乡，当地人均收入也大幅度地提升。旅游经济的高速发展存在着很多不良因素，如：旅行社恶性竞争、导游素质低、星级旅游酒店服务不达标等等。这些都无法在网站上看出来，但是我们可以提前在网上做好选择，而不会在到了地点后才发现其中的不好。因此网站建设的好可以让游客有更好的选择机会。网站设计按实际情况来进行设计，更能有效的让游客认识到景点的真实性，可靠性。为争取客源提供了一个好的平台。</p><p>大家都已经意识到，好的旅游网站模板是旅游网站建设和旅游网站设计的前提条件。它们之间是相互依存的，PHPOK企业站系统在助你轻松建站的同时，为您添加客源，打造良好口碑</p><p><br/></p>', '人们在解决了衣食住行的问题后，有了多余的时间和费用时，想用什么方式让自己紧张的神经放松下来，于是普遍的把眼光投入到外出旅行上，让自己增长见识的同时，身心的到放松', 0),
(1918, 1, 43, 68, '', '<p>酒店和宾馆与人们出行办事越来越紧密联系在一起，好的宾馆（酒店）会增加出行人（办事者）对当地的印象。</p><p>酒店业是一个前景广阔而又竞争激烈的行业，改革开放以来，我国的酒店业迅速发展，已经成为一个具有相当规模的产业，由于我国的旅游业迅速发展，我国加入世贸组织，酒店业将完全开放，这个时候，我国的酒店业将面临着前所未有的机遇和挑战。但是，现在还有一些酒店还停留在由人工操作和管理阶段，这样已经无法适应当前的发展趋势。因此要想使酒店的工作质量和效率提高，采用先进的计算机网络，通过网站改变酒店业务模式，实现酒店业务管理自动化已经成为一种必然，酒店网站建设和宾馆网站建设，让更多的人认识到企业的特色，吸引更多的客户来入住。Chinaz旗下的建站大师，为网站建设打造了好案例，提供健全的服务，自助建站功能等等。</p><p>对于酒店和宾馆整体来说，对经营状况起决定作用的是酒店的服务管理水平。如何利用先进的管理手段来提高酒店（宾馆）的管理水平成为业务发展的当务之急。利用好计算机管理并不仅是酒店（宾馆）管理走向成功的关键元素，但它可以最大限度地发挥准确、快捷、高效等作用，对酒店（宾馆）的业务管理提供了强有力的支持。因此，通过网站建设并且网站设计特色能吸引客户的网站，使用好的酒店网站模板建成的网站，使作业人员与管理系统之间灵活互动，实现流畅的工作流衔接，帮助酒店有效地进行业务管理，释放最大价值。网站管理系统在达到在节省人力资源成本的同时，可以提高业务效率，并能够及时、准确、迅速地满足顾客服务的需求。</p><p>作为人们食宿、娱乐、休闲的场所而得到了快速的发展。社会上也成立了各种类型，不同规模的宾馆（酒店）服务企业。如何为客户提供更加准确及时的服务，成为各个酒店（宾馆）竞争关键。所以酒店网站建设（宾馆网站建设）的好坏就成为了客户衡量酒店（宾馆）提供商服务标准的一个准则，信息系统成为了基础。随着Internet技术的进一步发展和普及，市场现有的产品化的酒店（宾馆）业务软件系统在不断发展中的酒店（宾馆）需求。基于Chinaz建站大师的网站设计目标能够建立完善、高效、可靠的酒店（酒店）业务信息系统，为酒店（宾馆）提供良好的信息环境，灵活的自助建站功能，让企业可自行操作。</p><p>任何成功的企业都是从一个好的定位开始，进入什么样的市场，为什么样的顾客服务，提供什么样的产品，追求什么样的价值诉求等等。网络上的宾馆网站和酒店网站很多，但是真正做到能很好表现出公司特色风格的网站，还是比较少的。PHPOK企业站系统让企业满意，让客户中意，从而拉近酒店与客户的距离。</p><p><br/></p>', '酒店和宾馆与人们出行办事越来越紧密联系在一起，好的宾馆（酒店）会增加出行人（办事者）对当地的印象。酒店业是一个前景广阔而又竞争激烈的行业，改革开放以来，我国的酒', 0),
(1919, 1, 43, 68, '', '<p>现如今互联网的飞速发展，用“多如牛毛”来形容网站建设的公司一点都不夸张。根据资料显示，厦门的网站建设公司多达数千家，除此之外还有数千个个人工作室和专门接单的个人，在做网站建设的任务。网站建设是现在互联网行业必争的一块大蛋糕，市场的庞大说明企业公司乃至个人的网站建设意识都很强烈。</p><p>“现在的客人对于网站建设的认识越来越深刻，企业也开始重视网络营销。因此，网站建设的兴起就自然而然。当然也有一些客人是跟风上网，网站建好之后就不再打理。”</p><p>PHPOK建站系统的网站建设人员指出，当前很多企业都完全是跟风，看见对手有网站，自己就想也找一家网站建设公司帮忙建设一个网站，但是网站建设好之后就放着当摆设。很多时候，客人进入企业网站，会发现上面的信息几乎是建设时发布的，根本就没有更新。</p><p>在网络盛行的年代，企业的网站就是“第一门面”，客户的第一印象在进入公司网站的时候就建立了。因此，企业网站建设质量直接影响着客户的抉择。企业进行网站建设，是为了开辟广阔的互联网市场，建立企业与消费者无间隙的沟通联系。但是不少“跟风建站”的企业，他们不了解互联网，甚至没有对网站建设有一个清晰的认识，只是听说网站建设具有非凡的功能，拥有上亿的潜在客户，网站建设完之后就能够有收入，所以就决定也要做一个网站。</p><p>因为缺乏对网站建设和网络营销的了解，在选择网站建设公司时又不知道怎么选择，基本上都是追求经济便宜，最后建设出的网站不是网站设计差劲，就是功能不全，又或者不是公司这个行业的风格……亦或者，花费了资金和经历做出来的网站，因为更新维护意识的欠缺，致使访问量低下，没有任何的利润，完全失去了网站建设的意义。甚至客户对网站印象低劣，导致一些潜在意向客户溜掉。</p><p>PHPOK建站系统表示，对于这些网站建设的问题，企业其实都是可以避免的。首先企业在选择网站建设公司的时候一定要慎重，要选择具有权威，有规模的网络公司，这样才可以保证网站建设的质量。同时，颇具规模和经验的网站建设公司会为企业制定相应的网站建设，进行必要的网站设计，同时良好的后续服务可以避免出现企业网站“荒废”情况的出现。</p><p>“网站建设是一个综合工程，不仅仅是技术那么简单。”Chinaz旗下建站大师负责人如是道。纯粹的一个网站摆在那里是不能创造任何价值的，粗枝烂叶的网站更可能带来负面的效果。只有在网站维护和更新上下足了力气，才能真正的发挥网站的功用。</p><p>即便是“跟风上网”的网站，只要找到优质的网站建设公司，获得他们的网站更新维护支持，也可以取得意想不到的效果。</p><p>PHPOK企业站系统，有强劲的网站设计团队，技术过硬的网站建设团队，良好的网站建设一条龙服务，是企业自助建站的一个不错的选择，可以看下他们旅游网站建设的网站。</p><p><br/></p>', '现如今互联网的飞速发展，用“多如牛毛”来形容网站建设的公司一点都不夸张。根据资料显示，厦门的网站建设公司多达数千家，除此之外还有数千个个人工作室和专门接单的个人', 0),
(1920, 1, 43, 68, '', '<p>人靠衣装，房子在建筑成功后，一样需要“穿”上美丽的“衣服”。中意的装修需要时间来选择，忙碌的上班族在工作之中要腾出时间去选择哪家装修公司好，去看他已装修成功的案例，对于他们来说是不现实的。于是网站成为人们首选的方式，网络上的建筑网站公司对更看重建筑网页设计，装修网站公司对于装修网站设计也同样看重，因为这2个方面的网站设计是人们首先认识建筑效果和装修效果的窗口。</p><p>怎样建设网站使之成为公司和客户之间的桥梁呢？PHPOK企业站系统为您找到了这个问题的答案，一个好的建筑网站建设和建筑网页设计，是公司推销产品的前提条件。当然还有其它的地理位置，环境等等条件也不可忽视。其后客户会将眼光投向对装修公司的选择，而这也引起装修公司对于装修网站建设和装修网站设计的重视。建站大师在建站上，前提就是满足中小企业应对各行各业的需求而设立项目，比如：自助建站等等。让您从不熟悉网站建设引导为您做出满意的网站，让您对网站设计有进一步的认识。</p><p>人和环境是相互依存的，建筑网页设计是解决室内空间的使用、美观的要求，同时在外部形体上，具有一定特性风格的前提下与周围环境、城市文脉及城市控制性规划相协调的结果。一个全新的观念进入了建筑师的思想和他的生活之中，他必须先在图纸上展现出来，然后通过网络让大众去认识和接受它，这就需要网站设计出来，设计的效果直接影响了客户对产品的印象。因此公司对于建筑网站建设和装修网站建设就更重视这个前期操作。</p><p>PHPOK企业站系统让产品完美的效果全部展示在网站上，因为有强劲的网站设计团队，技术过硬的网站建设团队，具有灵活的自助建站功能，让您的选择更加多样化。</p><p><br/></p>', '人靠衣装，房子在建筑成功后，一样需要“穿”上美丽的“衣服”。中意的装修需要时间来选择，忙碌的上班族在工作之中要腾出时间去选择哪家装修公司好，去看他已装修成功的案', 0),
(1921, 1, 43, 68, '', '<p>物业管理正伴随着城市房地产的迅速发展蓬勃兴起，广大百姓在成为购房客户的同时也成为物业的业主，他们在享受拥有产权兴奋的同时，也更加关注在居住区内是否能享受良好的服务。而物业网站建设能更有效的通过网络向客户展示一系列全方位、多层次、专业化的客户服务，随之带动家政公司的业务形成。人们更期盼一个网站上有一条龙的服务，PHPOK企业站系统的网站建设为家政公司提供了专业的家政网站建设，家政网站模板，同时也具备物业网站建设，物业网站模板。您无需为网站建站和网站设计痛苦，因为里面有自助建站的功能，简单便捷易懂易学。</p><p>市场经济的深入发展，竞争已不可避免地把企业带入“以客户为中心”的“客户满意”时代。作为物业管理行业更应做到“以人为本，业主至上”的客户服务。以客户为中心，使客户满意，并提高客户满意度，做好物业管理工作，适应市场竞争，有效的处理好物业管理企业和业主关系，才能在竞争中处于有利地位并持续发展。业主在小区生活时特别希望能获得完善、及时和周到的服务，而且服务的内容和方式也呈现了多样化的趋势。</p><p>如何满足业主高标准、多变化、快速扩展的服务需求已经成为国内外评定高品质物业管理公司的一个重要标准。物业网站建设成功将为物业管理企业应对业主提供最前端、最及时、最周到、最安全、最优秀的客户服务，使客户感觉体贴、细致、关怀、个性化服务。好的物业网站模板设计从中大幅度的提升了物业管理行业的品牌，树立了企业形象，有效的发挥了物业的最大价值。物业的发展带动了家政的发展，家政为物业提供了足够的人手处理问题，对家政服务的要求也随之扩大。当前我国家政服务业已得到了长足发展，经营模式和体制也在逐步完善。为了有更好的家政服务让更多的人认识，也相应促进了家政网站建设的需求。网站建设能否成功，离不开好公司的家政网站模板，灵活运用可自助建站等等。</p><p>物业管理和家政通过实施差别化服务，可缩短与业主的距离，赢得业主的信赖，同时也为物业管理企业带来更大的利润空间，实现经济效益和社会效益的统一。PHPOK企业站系统更轻松的提升物业公司的服务形象。</p><p><br/></p>', '物业管理正伴随着城市房地产的迅速发展蓬勃兴起，广大百姓在成为购房客户的同时也成为物业的业主，他们在享受拥有产权兴奋的同时，也更加关注在居住区内是否能享受良好的服', 0),
(1900, 1, 43, 68, '', '<p>装修是把生活的各种情形“物化”到房间之中，买的一般房间的设计业已完成，不能做大的调整了，所以剩下可以动的就是装修装点（大的装修概念包括房间设计、装修、家具布置、富有情趣的小装点）。作为房主，因为生活是自己的，所以自己必须亲自介入到装修过程中，不仅在装修设计施工期间，还包括住进去之后的长期的不断改进。而中意的装修需要时间来选择，忙碌的上班族在工作之余要腾出时间去选择哪家装修公司好，一定要选择多家装修设计公司进行比较，比较方案，比较价格等等，当房主精心挑选装修设计公司的同时，装修公司也需要不断进行营销方案，以最快最短的时间内让房主找到并挑选它。</p><p>现如今，装修公司的营销战争可以说是烟云四起，到处是兵戎相见，在那客户流量的狭窄独木桥上，各路群雄展开了激烈的厮杀，营造了一系列的营销方式，而装饰公司如果要做网络推广，实力强的营销网站是首选，在做装饰公司营销时，要充分考虑到面对群体的特点：学历、经济基础等。一般来说，知识面越广的人群，对装饰公司的信任程度就越低，这就需要营销人员花大力气去说服，因此在网站的页面设置上也要花心思，要紧紧围绕主题多做文章：详细介绍相关装修知识以及我公司的实力和在这些方面的优势等等，多列举些实例，设置答疑栏目、告诉客户一些不为外行知道的东西等，获取比较好得访问量，拉近之间的距离。而在首页，要比较详细的介绍公司实力，积极进取，开拓创新，打造一支专业化水平高、服务水平高、质量水平高的三高装修团队，同时设置反馈信息板块，在线留言板块等，增加与客户之间互动，装修公司企业建站质量直接影响着客户的抉择，而“装修网站建设是一个综合工程，不仅仅是技术那么简单。”纯粹的一个网站摆在那里是不能创造任何价值的，粗枝烂叶的网站更可能带来负面的效果。只有在网站维护和更新上下足了力气，才能真正的发挥网站的功用。即便是“跟风上网”的网站，只要找到优质的网站制作公司，获得他们的网站更新维护支持，也可以取得意想不到的效果。</p><p><br/></p>', '装修是把生活的各种情形“物化”到房间之中，买的一般房间的设计业已完成，不能做大的调整了，所以剩下可以动的就是装修装点（大的装修概念包括房间设计、装修、家具布置、', 0),
(1901, 1, 43, 68, '', '<p>旅游是人们为寻求精神上的愉快感受而进行的非定居性旅行和在游览过程中所发生的一切关系和现象的总和。随着因特网的发展，计算机技术的不断成熟，旅游网站建设纷纷落户。旅游这个行业近年来在网络上大力发展，网站建设经这些年的发展后已多如牛毛，旅游网站设计的发展也日趋成熟。这类自助建站和网站设计提供及时的旅游报价、打折门票信息、切实的旅游建议，以及详细的旅游资讯。将旅游业内信息进行整合分类，人性化的开设了旅游线路预定、打折门票、签证服务、机票酒店预订、旅游保险、旅游书城、包车服务、旅行游记、旅游博客、等多方面的服务！</p><p>随着旅游业形势的发展，致使旅游业经营人大都不能亲自处理每一项具体业务，大量业务需要委托他人代为办理。同时，为了使生产、管理、服务、统一化发展，由同一代理人完成或组织完成旅游业显得十分必要，“代理人就更需要通过网站来处理业务，于是网站设计关系着企业的形象。</p><p>据艾瑞网最新公布的《2008-2009中国网上旅行预订行业发展报告》显示，亲朋好友和网络已经成为用户获取旅游信息的最主要途径。可以说旅游自助建站更适合网络营销，在亲朋好友的口碑传播中也起到了决定性的作用。对于很多大型的成熟网站来说，增加现有会员的黏度是必不可少的一个环节，所以大量的推出网站建设模式也成为一个新的发展趋势。旅游网站模板设计迎来了一个新的时代，旅游线路、酒店、航空等的口碑传播“化身”为又一商业盈利模式。虽然，现在已有很多在线预定网站已经推出了网友点评模式，但这些设置更多的是站到企业的角度，进行“旁敲侧击”的宣传，是一种被“潜规则”了的点评模式，其效果十分有限，可信度也大受质疑，显然无法与完全置身于“第三方”的专业性点评网站相提并论。</p><p>如同游戏一样，选择在线模拟旅游，人们可以设定不同的身份，到世界各地旅游，沿途可以欣赏风景，体验异域风情的餐饮、购物、住宿等，使用虚拟货币进行支付和交易，再现真实旅游场景，外国已经出现类似网站，如“虚拟瑞典”“虚拟紫禁城”功能上还不是很完善，但业内对其未来发展一致看好。</p><p><br/></p>', '旅游是人们为寻求精神上的愉快感受而进行的非定居性旅行和在游览过程中所发生的一切关系和现象的总和。随着因特网的发展，计算机技术的不断成熟，旅游网站建设纷纷落户。旅', 0),
(1902, 1, 43, 68, '', '<p>随着经济的发展，农民不再局限于种植农作物了，水产养殖业也随之发展起来，并且已经从一个小产业发展成为目前农业经济中重要的大产业,目前科技成果转化率低是制约水产养殖现代化发展的重要因素,中国的水产养殖还有很长一段路要走。水产品是人民生活必不可少的优质动物蛋白食物来源,随着人们的消费倾向从数量型转向质量型,优质动物蛋白的需求度将持续走高,水产品需求将进一步增长。然而,受资源状况限制,我国水产品捕捞产量已无增长可能。面对日益增长的水产品需求量,水产养殖是解决问题的唯一方法，水产养殖业的发展空间非常巨大,而毫无疑问,水产科技对行业的发展将发挥至关重要的作用,水产类相关企业也将在养殖模式的创新、优化及技术创新等方面起着重要的牵引和指导作用。</p><p>水产养殖业发展起来了，销路和销量也是一个问题，在网络发展迅速的情况下，特产网站建设和水产网站建设，以及养殖网站建设成为一个打开市场的窗口，以往因为消息不及时或者地域的极限性，使得销量大的地方无东西可买，销量小的地方物资堆积成山，浪费了资源，人力，物力的同时。打击了农民的积极性，前途变的迷茫。网络让物资迅速的从供求那边准确及时供应到所需地。随着水产品市场的全面开放，以及&quot;以养为主&quot;渔业发展方针的确立，使渔业生产力得到了空前的释放，水产品产量大幅度提高，花色品种逐渐增多，产品鲜度和质量也有了很大的改善。水产品产量的增加使水产品市场供给有了根本改观，全国人均水产品占有量逐年提高。</p><p>PHPOK企业站系统，在市场经济发展的同时，让特产网站建设，水产网站建设，养殖网站建设不仅为公司推广形象，还为产品打开销路。网站设计满足农业水产养殖业的供需结合情况，自助建站功能满足客户自己手动管理网站，更多最新消息能够及时上传，利于网站的知名度上升。用网站设计装备公司形象，有助于进一步提升产品在销售上的价值，网站建设的好，给顾客留下好的印象，产品远销也就只是时间问题了。建站大师的自助建站功能，让更多的人认识到网络建设对于公司的辅助作用</p><p><br/></p>', '随着经济的发展，农民不再局限于种植农作物了，水产养殖业也随之发展起来，并且已经从一个小产业发展成为目前农业经济中重要的大产业,目前科技成果转化率低是制约水产养殖现', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_23`
--

CREATE TABLE `qinggan_list_23` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `link` longtext NOT NULL COMMENT '链接',
  `target` varchar(255) NOT NULL DEFAULT '_self' COMMENT '链接方式'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='导航';

--
-- 转存表中的数据 `qinggan_list_23`
--

INSERT INTO `qinggan_list_23` (`id`, `site_id`, `project_id`, `cate_id`, `link`, `target`) VALUES
(520, 1, 42, 0, 'a:2:{s:7:\"default\";s:9:\"index.php\";s:7:\"rewrite\";s:10:\"index.html\";}', '_self'),
(712, 1, 42, 0, 'a:2:{s:7:\"default\";s:20:\"index.php?id=aboutus\";s:7:\"rewrite\";s:12:\"aboutus.html\";}', '_self'),
(713, 1, 42, 0, 'a:2:{s:7:\"default\";s:17:\"index.php?id=news\";s:7:\"rewrite\";s:9:\"news.html\";}', '_self'),
(714, 1, 42, 0, 'a:2:{s:7:\"default\";s:20:\"index.php?id=product\";s:7:\"rewrite\";s:12:\"product.html\";}', '_self'),
(716, 1, 42, 0, 'a:2:{s:7:\"default\";s:17:\"index.php?id=book\";s:7:\"rewrite\";s:9:\"book.html\";}', '_self'),
(755, 1, 42, 0, 'a:2:{s:7:\"default\";s:17:\"index.php?id=work\";s:7:\"rewrite\";s:9:\"work.html\";}', '_self'),
(760, 1, 42, 0, 'a:2:{s:7:\"default\";s:30:\"index.php?id=news&cate=company\";s:7:\"rewrite\";s:17:\"news/company.html\";}', '_self'),
(761, 1, 42, 0, 'a:2:{s:7:\"default\";s:31:\"index.php?id=news&cate=industry\";s:7:\"rewrite\";s:18:\"news/industry.html\";}', '_self'),
(1254, 1, 42, 0, 'a:2:{s:7:\"default\";s:31:\"index.php?id=development-course\";s:7:\"rewrite\";s:23:\"development-course.html\";}', '_self'),
(1256, 1, 42, 0, 'a:2:{s:7:\"default\";s:18:\"index.php?id=photo\";s:7:\"rewrite\";s:10:\"photo.html\";}', '_self'),
(1298, 1, 42, 0, 'a:2:{s:7:\"default\";s:28:\"index.php?id=download-center\";s:7:\"rewrite\";s:20:\"download-center.html\";}', '_self'),
(1299, 1, 42, 0, 'a:2:{s:7:\"default\";s:16:\"index.php?id=bbs\";s:7:\"rewrite\";s:8:\"bbs.html\";}', '_self'),
(1300, 1, 147, 0, 'a:2:{s:7:\"default\";s:21:\"index.php?id=about-us\";s:7:\"rewrite\";s:13:\"about-us.html\";}', '_self'),
(1301, 1, 147, 0, 'a:2:{s:7:\"default\";s:31:\"index.php?id=development-course\";s:7:\"rewrite\";s:23:\"development-course.html\";}', '_self'),
(1302, 1, 147, 0, 'a:2:{s:7:\"default\";s:17:\"index.php?id=news\";s:7:\"rewrite\";s:9:\"news.html\";}', '_self'),
(1303, 1, 147, 0, 'a:2:{s:7:\"default\";s:17:\"index.php?id=book\";s:7:\"rewrite\";s:9:\"book.html\";}', '_self'),
(1304, 1, 147, 0, 'a:2:{s:7:\"default\";s:23:\"index.php?id=contact-us\";s:7:\"rewrite\";s:15:\"contact-us.html\";}', '_self');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_24`
--

CREATE TABLE `qinggan_list_24` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `pictures` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `content` longtext NOT NULL COMMENT '内容',
  `m_title` varchar(255) NOT NULL DEFAULT '' COMMENT '手机版标题'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品';

--
-- 转存表中的数据 `qinggan_list_24`
--

INSERT INTO `qinggan_list_24` (`id`, `site_id`, `project_id`, `cate_id`, `pictures`, `thumb`, `content`, `m_title`) VALUES
(1753, 1, 45, 582, '', '1013', '<p><img src=\"res/201603/22/auto_1011.jpg\" alt=\"auto_1011.jpg\"/></p><p><img src=\"res/201603/22/auto_1010.jpg\" alt=\"auto_1010.jpg\"/></p><p><img src=\"res/201603/22/auto_1012.jpg\" alt=\"auto_1012.jpg\"/></p>', '小米5'),
(1760, 1, 45, 583, '1015,1017,1016', '1015', '', '魅族 MX5'),
(1761, 1, 45, 584, '1019,1020,1018', '1018', '<p style=\"text-indent: 2em;\">2014年5月7日，华为在巴黎发布了2014旗舰机型P7。P7配置5英寸1080P全高清屏幕，采用金属+双玻璃结构，机身厚度仅6.5mm，支持CAT4 LTE网络，五月起在中国大陆等30多个国家及地区开售，全球售价449欧元，中国大陆售价为人民币2888元。</p><p style=\"text-indent: 2em;\"><br/></p><p style=\"text-indent: 2em;\">华为P7正面采用5寸1080p屏，有着6.5mm的极致超薄机身，拍照方面有着前置800万+后置1300万摄像头组合，内置1.8GHz海思Kirin910T四核处理器，有着2GBRAM+16GBROM机身存储，后置不可拆卸的2500mAh电池，支持CAT4LTE4G网络。华为Ascend P7分辨率为1920X1080像素的FHD级别，显示效果非常细腻。核心方面内置一颗主频1.8GHz海思Kirin 910T四核芯处理器，以及2GB RAM+16GB ROM的内存组合，流畅运行基于Android 4.4系统的Emotion UI 2.3用户界面。<br/></p><p><br/></p>', '华为 P7'),
(1762, 1, 45, 585, '1021,1022,1023,1024', '1021', '<h4>双曲面屏幕</h4><p>vivo Xplay5采用了双曲面屏幕，屏幕两侧有较大的弧度，曲面的屏幕会使屏幕呈现出无边的视觉效果。</p><p>vivo Xplay5专为曲面侧屏设计了侧屏来电提醒、解锁。</p><p><br/></p><h4>侧面解锁</h4><p>解锁方面，用户在进行图标滑动至曲面屏部分时会发生明显的「变形」，当在锁屏界面滑动解锁时，手机的曲面屏会有相当明显的光晕效果。</p><p>侧屏来电提醒：当手机反扣放在桌面时，如果手机来电时，双侧曲面屏也会散发波浪光影提醒用户来电信息。</p><p><br/></p><h4>智慧引擎</h4><p>智慧引擎优化主要分为内存加速和处理器加速。根据用户的使用情况选择性地智能加载部分常用应用，并调高这些常用应用的优先级，减小被回收的几率。另外，vivo 还优化了系统代码，大幅度降低系统的内存占用，并针对性地做了缓存碎片和内存的闲时动态回收，进而腾出更多的内存空间供用户使用。</p><p><br/></p><h4>分屏多任务</h4><p>vivo Xplay5配备了分屏多任务功能。在用户进行看电影，游戏，看书时，微信QQ聊天时不需进行切换，手机可以自动分屏为功能屏幕，一边聊微信，一边看电影。</p><p>但是现在分屏多任务支持的软件还有限，如果支持更多软件，分屏功能将会更实用。</p><p><br/></p><h4>128GB存储和3600mAh</h4><p>vivo Xplay5和vivo Xplay5旗舰版均配备了128GB的存储空间和3600mAH，提供更大和更长的续航选择。</p>', 'vivo Xplay5'),
(1763, 1, 45, 216, '1026,1027,1025,1028', '1025', '<p>iPhone SE是美国苹果公司推出的一款新的4英寸iPhone智能手机，该手机基本上是2013年发布的iPhone 5s的升级版本。苹果公司将该款产品命名为：iPhone SE，这意味着iPhone升级版本的名称将首次不带数字。</p><p><br/></p><p>iPhone SE由苹果公司于美国时间2016年3月21日13点在美国加州库比蒂诺总部举行发布会正式发布。 iPhone SE有玫瑰金色，有一个嵌入不锈钢标志。正面和背面底部有玻璃镜面。iPhone SE外观与iPhone 5s基本一致。iPhone SE 16G和64G的美国市场售价分别为399和499美元，中国首发售价分别为3288元4088元。</p><p><br/></p>', 'Apple iPhone 5SE');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_40`
--

CREATE TABLE `qinggan_list_40` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `content` longtext NOT NULL COMMENT '内容',
  `demo` varchar(255) NOT NULL DEFAULT '' COMMENT '测试链接'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='关于我们';

--
-- 转存表中的数据 `qinggan_list_40`
--

INSERT INTO `qinggan_list_40` (`id`, `site_id`, `project_id`, `cate_id`, `content`, `demo`) VALUES
(1756, 1, 87, 0, '<p>深圳市锟铻科技有限公司（Shenzhen Kunwu Technology Co., Ltd.）创立于2014年，专注于企业网站技术的研究和开发，是国内最有影响力的企业网站技术提供商。</p><p>“创新，将新技术转化为生产力”是锟铻科技的核心竞争力。凭借对软件和互联网行业的深刻理<img src=\"images/emotion/04.png\"/>解，锟铻科技将软件技术与互联网应用相结合，将领先业<img src=\"images/emotion/37.png\" style=\"width: 62px; height: 62px;\" width=\"62\" height=\"62\"/>界的产品理念和丰富的产品开发经验相结合，为用户提供简单、方便、安全、实用的协同应用软件产品和解决方案，帮助客户实现低成本、低风险、快起步、高效率的信息化目标。</p><p>锟铻科技成长的过程，就是服务客户并和客户一起不断成功的过程！我们用心、努力作好每一件事，满怀信心迎接每一次挑战。</p><p><img src=\"images/emotion/01.png\"/>d</p>', ''),
(1757, 1, 87, 0, '<p>联系我们</p><p>请到后台：关于我们》联系我们那里管理相关内容</p>', ''),
(1758, 1, 87, 0, '<table><tbody><tr class=\"firstRow\"><td style=\"word-break: break-all;\" width=\"117\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2011年12月</span></td><td style=\"word-break: break-all;\" width=\"721\" valign=\"top\">phpok3.4版发布（后台更换为桌面式）</td></tr><tr><td style=\"word-break: break-all;\" width=\"116\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2011年9月</span></td><td style=\"word-break: break-all;\" width=\"721\" valign=\"top\">phpok3.3完整版发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2010年8月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">phpok3.0完整版发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2008年9月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">phpok3.0精简版发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2008年5月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">phpok2.2稳定版发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"116\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2008年3月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">phpok2.0发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"116\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2007年5月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgweb5.2发布，同时更名为 phpok1.0版本</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2007年1月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgweb5.0发布（第一次实现多语言，多风格的建站系统）</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2006年10月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgweb4.2发布（GBK）</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2006年8月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgweb4.1发布（UTF-8）</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2006年6月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgweb4.0发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2005年11月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgWeb3.0发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2005年8月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">工作室论坛开通</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2005年7月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgWeb1.0发布</td></tr><tr><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"115\" valign=\"top\"><span style=\"color: rgb(192, 0, 0);\">2005年4月</span></td><td colspan=\"1\" rowspan=\"1\" style=\"word-break: break-all;\" width=\"719\" valign=\"top\">qgWeb0.54版发布</td></tr></tbody></table><p><br/></p>', ''),
(1759, 1, 87, 0, '<p>工作环境~</p>', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_46`
--

CREATE TABLE `qinggan_list_46` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `fullname` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT '邮箱',
  `content` longtext NOT NULL COMMENT '内容',
  `adm_reply` longtext NOT NULL COMMENT '管理员回复',
  `pic` varchar(255) NOT NULL DEFAULT '' COMMENT '图片'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='留言模块';

--
-- 转存表中的数据 `qinggan_list_46`
--

INSERT INTO `qinggan_list_46` (`id`, `site_id`, `project_id`, `cate_id`, `fullname`, `email`, `content`, `adm_reply`, `pic`) VALUES
(1285, 1, 96, 0, '测试留言', '测试留言', '测试留言', '', ''),
(1869, 1, 96, 0, 'fasfasdfasdf', 'fasdfasdfasdf', '<p>fasdfasdfasdfasdfadf<br/></p>', '', '1309,1310');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_61`
--

CREATE TABLE `qinggan_list_61` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `link` longtext NOT NULL COMMENT '链接',
  `target` varchar(255) NOT NULL DEFAULT '_self' COMMENT '链接方式',
  `tel` varchar(255) NOT NULL DEFAULT '' COMMENT '联系人电话'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='友情链接';

--
-- 转存表中的数据 `qinggan_list_61`
--

INSERT INTO `qinggan_list_61` (`id`, `site_id`, `project_id`, `cate_id`, `link`, `target`, `tel`) VALUES
(1261, 1, 142, 0, 'http://www.sz-qibang.com/', '_blank', ''),
(1262, 1, 142, 0, 'http://www.17tengfei.com/', '_blank', ''),
(1263, 1, 142, 0, 'http://www.7139.com', '_blank', ''),
(1265, 1, 142, 0, 'http://www.admin5.com/', '_blank', ''),
(1266, 1, 142, 0, 'http://www.cnzz.cn/', '_blank', ''),
(1267, 1, 142, 0, 'http://www.im286.com/', '_blank', ''),
(1268, 1, 142, 0, 'http://www.mycodes.net/', '_blank', ''),
(1772, 1, 142, 0, 'http://www.phpok.com', '_self', '15818533971');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_64`
--

CREATE TABLE `qinggan_list_64` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `qq` varchar(255) NOT NULL DEFAULT '' COMMENT '客服QQ'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客服';

--
-- 转存表中的数据 `qinggan_list_64`
--

INSERT INTO `qinggan_list_64` (`id`, `site_id`, `project_id`, `cate_id`, `qq`) VALUES
(1305, 1, 148, 0, '40782502'),
(1427, 1, 148, 0, '150467466');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_65`
--

CREATE TABLE `qinggan_list_65` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `note` longtext NOT NULL COMMENT '摘要',
  `fsize` varchar(255) NOT NULL DEFAULT '' COMMENT '文件大小',
  `content` longtext NOT NULL COMMENT '内容',
  `version` varchar(255) NOT NULL DEFAULT '' COMMENT '版本',
  `website` varchar(255) NOT NULL DEFAULT '' COMMENT '官方网站',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '开发商',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `dfile` varchar(255) NOT NULL DEFAULT '' COMMENT '附件',
  `dlink` varchar(255) NOT NULL DEFAULT '' COMMENT '附件链接',
  `onlyuser` int(11) NOT NULL DEFAULT '0' COMMENT '限制会员可下载'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资源下载';

--
-- 转存表中的数据 `qinggan_list_65`
--

INSERT INTO `qinggan_list_65` (`id`, `site_id`, `project_id`, `cate_id`, `note`, `fsize`, `content`, `version`, `website`, `author`, `thumb`, `dfile`, `dlink`, `onlyuser`) VALUES
(1855, 1, 151, 200, '实现主题复制功能，做模板时很适用噢', '5KB', '<p>实现主题复制功能，做模板时很适用噢</p>', '1.0', 'https://www.phpok.com', 'phpok.com', '1250', '1029', '', 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_66`
--

CREATE TABLE `qinggan_list_66` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `content` longtext NOT NULL COMMENT '内容',
  `toplevel` varchar(255) NOT NULL DEFAULT '0' COMMENT '置顶',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='论坛BBS';

--
-- 转存表中的数据 `qinggan_list_66`
--

INSERT INTO `qinggan_list_66` (`id`, `site_id`, `project_id`, `cate_id`, `content`, `toplevel`, `thumb`) VALUES
(1311, 1, 152, 204, '<p>测试论坛功能</p>', '', ''),
(1334, 1, 152, 204, '<p>测试</p>', '', ''),
(1854, 1, 152, 204, '<p>测试新主题测试新主题测试新主题测试新主题测试新主题</p>', '0', '1040'),
(1870, 1, 152, 206, '<p>嘿嘿~~~<br/></p>', '0', '1314');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_68`
--

CREATE TABLE `qinggan_list_68` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `pictures` varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  `content` longtext NOT NULL COMMENT '内容'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='图集相册';

--
-- 转存表中的数据 `qinggan_list_68`
--

INSERT INTO `qinggan_list_68` (`id`, `site_id`, `project_id`, `cate_id`, `thumb`, `pictures`, `content`) VALUES
(1765, 1, 144, 211, '1025', '1025,1028,1027,1026', ''),
(1766, 1, 144, 211, '1024', '1021,1023,1024,1022', '<h4 style=\"white-space: normal;\">双曲面屏幕</h4><p style=\"white-space: normal;\">vivo Xplay5采用了双曲面屏幕，屏幕两侧有较大的弧度，曲面的屏幕会使屏幕呈现出无边的视觉效果。</p><p style=\"white-space: normal;\">vivo Xplay5专为曲面侧屏设计了侧屏来电提醒、解锁。</p><p style=\"white-space: normal;\"><br/></p><h4 style=\"white-space: normal;\">侧面解锁</h4><p style=\"white-space: normal;\">解锁方面，用户在进行图标滑动至曲面屏部分时会发生明显的「变形」，当在锁屏界面滑动解锁时，手机的曲面屏会有相当明显的光晕效果。</p><p style=\"white-space: normal;\">侧屏来电提醒：当手机反扣放在桌面时，如果手机来电时，双侧曲面屏也会散发波浪光影提醒用户来电信息。</p><p style=\"white-space: normal;\"><br/></p><h4 style=\"white-space: normal;\">智慧引擎</h4><p style=\"white-space: normal;\">智慧引擎优化主要分为内存加速和处理器加速。根据用户的使用情况选择性地智能加载部分常用应用，并调高这些常用应用的优先级，减小被回收的几率。另外，vivo 还优化了系统代码，大幅度降低系统的内存占用，并针对性地做了缓存碎片和内存的闲时动态回收，进而腾出更多的内存空间供用户使用。</p><p style=\"white-space: normal;\"><br/></p><h4 style=\"white-space: normal;\">分屏多任务</h4><p style=\"white-space: normal;\">vivo Xplay5配备了分屏多任务功能。在用户进行看电影，游戏，看书时，微信QQ聊天时不需进行切换，手机可以自动分屏为功能屏幕，一边聊微信，一边看电影。</p><p style=\"white-space: normal;\">但是现在分屏多任务支持的软件还有限，如果支持更多软件，分屏功能将会更实用。</p><p style=\"white-space: normal;\"><br/></p><h4 style=\"white-space: normal;\">128GB存储和3600mAh</h4><p style=\"white-space: normal;\">vivo Xplay5和vivo Xplay5旗舰版均配备了128GB的存储空间和3600mAH，提供更大和更长的续航选择。</p>'),
(1767, 1, 144, 211, '1020', '1018,1020,1019', ''),
(1768, 1, 144, 211, '1015', '1015,1017,1016', ''),
(1769, 1, 144, 211, '1013', '1011,1012,1010', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_69`
--

CREATE TABLE `qinggan_list_69` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `attrs` longtext NOT NULL COMMENT '产品多属性'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品参考数据';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_74`
--

CREATE TABLE `qinggan_list_74` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `account` varchar(255) NOT NULL DEFAULT '' COMMENT '会员账号'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='注册审核模块';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_75`
--

CREATE TABLE `qinggan_list_75` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '网站ID',
  `project_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主分类ID',
  `fullname` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号',
  `bankprice` varchar(255) NOT NULL DEFAULT '' COMMENT '汇款金额',
  `note` longtext NOT NULL COMMENT '摘要',
  `bankname` varchar(255) NOT NULL DEFAULT '' COMMENT '汇款银行'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='银行汇款';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_attr`
--

CREATE TABLE `qinggan_list_attr` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `tid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `aid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '属性组ID',
  `vid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参数ID',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '增减价格值',
  `weight` float NOT NULL DEFAULT '0' COMMENT '重量增减',
  `volume` float NOT NULL DEFAULT '0' COMMENT '体积增减值，带-号为减值',
  `taxis` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='主题属性';

--
-- 转存表中的数据 `qinggan_list_attr`
--

INSERT INTO `qinggan_list_attr` (`id`, `tid`, `aid`, `vid`, `price`, `weight`, `volume`, `taxis`) VALUES
(10, 1306, 3, 7, '5.0000', 0, 0, 10),
(11, 1306, 3, 8, '5.0000', 0, 0, 20),
(24, 1306, 1, 1, '3.0000', 1, 0, 10),
(25, 1306, 1, 3, '4.0000', 1, 0, 20),
(26, 1306, 1, 4, '2.0000', 1, 0, 30),
(31, 1753, 8, 29, '499.0000', 0, 0, 10),
(32, 1753, 8, 30, '549.0000', 0, 0, 20),
(33, 1753, 8, 31, '1199.0000', 0, 0, 30),
(34, 1753, 8, 32, '1249.0000', 0, 0, 40),
(35, 1753, 1, 5, '0.0000', 0, 0, 40),
(36, 1753, 1, 6, '0.0000', 0, 0, 50),
(37, 1753, 1, 33, '0.0000', 0, 0, 60),
(38, 1760, 1, 6, '0.0000', 0, 0, 50),
(39, 1760, 1, 36, '0.0000', 0, 0, 70),
(40, 1760, 8, 34, '0.0000', 0, 0, 50),
(41, 1760, 8, 35, '1000.0000', 0, 0, 60),
(42, 1761, 1, 5, '0.0000', 0, 0, 40),
(43, 1761, 1, 6, '0.0000', 0, 0, 50),
(44, 1761, 8, 34, '0.0000', 0, 0, 50),
(45, 1761, 8, 35, '100.0000', 0, 0, 60),
(52, 1763, 1, 1, '0.0000', 0, 0, 5),
(53, 1763, 1, 3, '0.0000', 0, 0, 10),
(54, 1763, 1, 4, '0.0000', 0, 0, 15),
(55, 1763, 8, 34, '0.0000', 0, 0, 5),
(56, 1763, 8, 35, '1299.0000', 0, 0, 10),
(63, 1762, 1, 1, '0.0000', 0, 0, 10),
(64, 1762, 1, 3, '0.0000', 0, 0, 15),
(65, 1762, 1, 33, '0.0000', 0, 0, 60),
(66, 1762, 8, 34, '0.0000', 0, 0, 5),
(67, 1762, 8, 37, '3200.0000', 0, 0, 10),
(68, 1762, 8, 35, '1600.0000', 0, 0, 15);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_biz`
--

CREATE TABLE `qinggan_list_biz` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '产品ID',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '价格',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币ID',
  `weight` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '重量，单位是Kg',
  `volume` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '体积，单位立方米',
  `unit` varchar(50) NOT NULL COMMENT '单位',
  `is_virtual` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0实物1虚拟产品'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='电子商务';

--
-- 转存表中的数据 `qinggan_list_biz`
--

INSERT INTO `qinggan_list_biz` (`id`, `price`, `currency_id`, `weight`, `volume`, `unit`, `is_virtual`) VALUES
(1253, '300.0000', 1, 0, 0, '', 0),
(1306, '170.0000', 1, 0, 0, '条', 0),
(1680, '8000.0000', 1, 0, 0, '', 0),
(1681, '8000.0000', 1, 0, 0, '', 0),
(1682, '8000.0000', 1, 0, 0, '', 0),
(1683, '8000.0000', 1, 0, 0, '', 0),
(1684, '8000.0000', 1, 0, 0, '', 0),
(1685, '8000.0000', 1, 0, 0, '', 0),
(1686, '8000.0000', 1, 0, 0, '', 0),
(1687, '8000.0000', 1, 0, 0, '', 0),
(1688, '8000.0000', 1, 0, 0, '', 0),
(1689, '8000.0000', 1, 0, 0, '', 0),
(1690, '8000.0000', 1, 0, 0, '', 0),
(1691, '8000.0000', 1, 0, 0, '', 0),
(1692, '8000.0000', 1, 0, 0, '', 0),
(1693, '8000.0000', 1, 0, 0, '', 0),
(1694, '8000.0000', 1, 0, 0, '', 0),
(1748, '8000.0000', 1, 0, 0, '', 0),
(1749, '8000.0000', 1, 0, 0, '', 0),
(1750, '8000.0000', 1, 0, 0, '', 0),
(1753, '2000.0000', 1, 0, 0, '', 0),
(1760, '1499.0000', 1, 0, 0, '', 0),
(1761, '999.0000', 1, 0, 0, '台', 1),
(1762, '3698.0000', 1, 0, 0, '', 0),
(1763, '3288.0000', 1, 0, 0, '台', 1),
(1855, '0.0000', 1, 0, 0, '', 0),
(1856, '0.0000', 1, 0, 0, '', 0),
(1857, '0.0000', 1, 0, 0, '', 0),
(222125, '0.0000', 1, 0, 0, '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_list_cate`
--

CREATE TABLE `qinggan_list_cate` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `cate_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='主题绑定的分类';

--
-- 转存表中的数据 `qinggan_list_cate`
--

INSERT INTO `qinggan_list_cate` (`id`, `cate_id`) VALUES
(1311, 204),
(1334, 204),
(1368, 8),
(1369, 8),
(1370, 68),
(1371, 68),
(1676, 68),
(1677, 68),
(1753, 582),
(1760, 583),
(1761, 584),
(1762, 585),
(1763, 216),
(1763, 589),
(1765, 211),
(1766, 211),
(1767, 211),
(1768, 211),
(1769, 211),
(1855, 200),
(1870, 206),
(1900, 68),
(1901, 68),
(1902, 68),
(1903, 68),
(1904, 68),
(1905, 68),
(1906, 68),
(1907, 68),
(1908, 68),
(1909, 68),
(1910, 68),
(1911, 68),
(1912, 68),
(1913, 68),
(1914, 68),
(1915, 68),
(1916, 68),
(1917, 68),
(1918, 68),
(1919, 68),
(1920, 68),
(1921, 68),
(1922, 68),
(1923, 68),
(1924, 68),
(1925, 68),
(1926, 68),
(1927, 68),
(1928, 68);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_log`
--

CREATE TABLE `qinggan_log` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `note` varchar(255) NOT NULL COMMENT '日志摘要',
  `url` varchar(255) NOT NULL COMMENT '请求网址',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '执行时间',
  `app_id` varchar(30) NOT NULL DEFAULT 'www' COMMENT '接入APP_ID',
  `ctrl` varchar(255) NOT NULL COMMENT '控制器',
  `func` varchar(255) NOT NULL COMMENT '方法',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `ip` varchar(255) NOT NULL COMMENT '登录IP',
  `referer` varchar(255) NOT NULL COMMENT '来源网址',
  `mask` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0表示系统日志，1表示手动断点日志用于调试',
  `session_id` varchar(255) NOT NULL COMMENT 'SESSION_ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='日志记录';

--
-- 转存表中的数据 `qinggan_log`
--

INSERT INTO `qinggan_log` (`id`, `note`, `url`, `dateline`, `app_id`, `ctrl`, `func`, `admin_id`, `user_id`, `ip`, `referer`, `mask`, `session_id`) VALUES
(1, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1525283870', 1525283874, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1525283867', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(2, '999.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=999.0000&_=1525283903235', 1525283906, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1761', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(3, '1099.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=1099&_=1525283903236', 1525283907, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1761', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(4, '1', 'http://localhost/phpok/api.php?c=cart&f=total&_=1525283908602', 1525283908, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?c=cart&f=checkout&id[]=1', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(5, '成功创建支付链，请稍候，即将为您跳转支付页面…', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&payment=15', 1525283914, 'www', 'payment', 'create', 0, 23, '::1', 'http://localhost/phpok/index.php?c=cart&f=checkout&id[]=1', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(6, '您的购物车里没有任何产品', 'http://localhost/phpok/index.php?c=cart&f=checkout&id%5B0%5D=1', 1525283923, 'www', 'cart', 'checkout', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1761', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(7, '删除成功', 'http://localhost/phpok/admin.php?c=payment&f=delete&id=18&_=1525283990178', 1525283993, 'admin', 'payment', 'delete', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=payment&menu_id=52&_noCache=0.2727652903393465', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(8, '删除成功', 'http://localhost/phpok/admin.php?c=payment&f=groupdel&id=14&_=1525283994118', 1525283997, 'admin', 'payment', 'groupdel', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=payment&menu_id=52&_noCache=0.2727652903393465', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(9, '添加成功', 'http://localhost/phpok/admin.php?c=payment&f=save&_noCache=0.1525284006', 1525284037, 'admin', 'payment', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=payment&f=set&gid=1&code=wxpay', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(10, '成功创建支付链，请稍候，即将为您跳转支付页面…', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 1525284054, 'www', 'payment', 'create', 0, 23, '::1', 'http://localhost/phpok/index.php?c=order&f=payment&id=1&_noCache=0.1525284049', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(11, 'Array\n(\n    [return_code] => FAIL\n    [return_msg] => invalid spbill_create_ip\n)', 'http://localhost/phpok/index.php?c=payment&f=submit&id=4&_noCache=0.1525284054', 1525284056, 'www', 'payment', 'submit', 0, 23, '::1', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(12, '支付出错，请联系管理员', 'http://localhost/phpok/index.php?c=payment&f=submit&id=4&_noCache=0.1525284054', 1525284056, 'www', 'payment', 'submit', 0, 23, '::1', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(13, '编辑成功', 'http://localhost/phpok/admin.php?c=payment&f=save&_noCache=0.1525284064', 1525284125, 'admin', 'payment', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=payment&f=set&id=19', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(14, 'Array\n(\n    [return_code] => FAIL\n    [return_msg] => invalid spbill_create_ip\n)', 'http://localhost/phpok/index.php?c=payment&f=submit&id=4&_noCache=0.1525284054', 1525284142, 'www', 'payment', 'submit', 0, 23, '::1', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(15, '支付出错，请联系管理员', 'http://localhost/phpok/index.php?c=payment&f=submit&id=4&_noCache=0.1525284054', 1525284142, 'www', 'payment', 'submit', 0, 23, '::1', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(16, '成功创建支付链，请稍候，即将为您跳转支付页面…', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 1525284146, 'www', 'payment', 'create', 0, 23, '::1', 'http://localhost/phpok/index.php?c=order&f=payment&id=1&_noCache=0.1525284049', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(17, 'Array\n(\n    [return_code] => FAIL\n    [return_msg] => invalid spbill_create_ip\n)', 'http://localhost/phpok/index.php?c=payment&f=submit&id=5&_noCache=0.1525284146', 1525284203, 'www', 'payment', 'submit', 0, 23, '::1', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(18, '支付出错，请联系管理员', 'http://localhost/phpok/index.php?c=payment&f=submit&id=5&_noCache=0.1525284146', 1525284203, 'www', 'payment', 'submit', 0, 23, '::1', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284051', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(19, '成功创建支付链，请稍候，即将为您跳转支付页面…', 'http://localhost/phpok/index.php?c=payment&f=create&id=1&_noCache=0.1525284377', 1525284380, 'www', 'payment', 'create', 0, 23, '::1', 'http://localhost/phpok/index.php?c=order&f=payment&id=1&_noCache=0.1525284376', 0, 'dujg3s4v1t2pmtl303896rubqg'),
(20, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1525347804, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1525347544', 0, 'mmcs4em6pnni6kssn7k6n02hrq'),
(21, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1525347815650', 1525347815, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1525347808', 0, 'mmcs4em6pnni6kssn7k6n02hrq'),
(22, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1525348940672', 1525348940, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1525347808', 0, 'mmcs4em6pnni6kssn7k6n02hrq'),
(23, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1525349005788', 1525349005, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1525347808', 0, 'mmcs4em6pnni6kssn7k6n02hrq'),
(24, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1525349123238', 1525349123, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1525347808', 0, 'mmcs4em6pnni6kssn7k6n02hrq'),
(25, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1525349307890', 1525349308, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1525347808', 0, 'mmcs4em6pnni6kssn7k6n02hrq'),
(26, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1525398490, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1525398485', 0, 'ef7781nahae1r4o0s3fh8851vt'),
(27, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1525749865', 1525749870, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1525749861', 0, '23rr29tf6u6l8vgavk0d2h3fr1'),
(28, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1525749951, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1525749947', 0, '23rr29tf6u6l8vgavk0d2h3fr1'),
(29, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1525834554, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1525834548', 0, 'rlq73u8tgul5u90riba9j36bn2'),
(30, '标识符已被使用', 'http://localhost/phpok/admin.php?c=project&f=save&_noCache=0.1525839400', 1525839431, 'admin', 'project', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=project&f=set&_noCache=0.1525839398', 0, 'rlq73u8tgul5u90riba9j36bn2'),
(31, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1525916508, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1525916501', 0, 'bgr1n8a88l6cc4r3c2580g06s6'),
(32, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1525958515, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1525958510', 0, '2kiial25dv721khe6cfs30d6n4'),
(33, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526009054, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526009049', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(34, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009110010', 1526009110, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(35, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009226402', 1526009226, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(36, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009289474', 1526009289, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(37, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009505193', 1526009505, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(38, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009505194', 1526009514, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(39, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009583949', 1526009584, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(40, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009583950', 1526009587, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(41, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009670649', 1526009670, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(42, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009670650', 1526009674, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(43, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009747443', 1526009747, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(44, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009747444', 1526009751, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(45, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009770112', 1526009773, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(46, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009815953', 1526009816, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(47, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009815954', 1526009819, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(48, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526009999887', 1526010000, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(49, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=1&_=1526009999888', 1526010003, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(50, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&_=1526009999889', 1526010020, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(51, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=2&_=1526009999890', 1526010027, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526009107', 0, '0bh6828kg0lnbd5gm6n4b70jr3'),
(52, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526347948, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526347942', 0, 'gto669g59kdaa70d71ch3qtdo4'),
(53, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1526391360', 1526391368, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1526391094', 0, 'vs069hbntued5luik14hb0e8n6'),
(54, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526455326, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526455321', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(55, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526455334405', 1526455334, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(56, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=3&_=1526455334406', 1526455340, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(57, '未指定字段ID', 'http://localhost/phpok/admin.php?c=form&f=quickadd&id=demo&tid=3', 1526455345, 'admin', 'form', 'quickadd', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(58, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&_=1526455334407', 1526455356, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(59, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=4&_=1526455334408', 1526455361, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(60, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526455513321', 1526455513, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(61, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=3&_=1526455513322', 1526455518, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(62, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&_=1526455513323', 1526455523, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(63, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=2&_=1526455513324', 1526455525, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(64, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=2%2C3&_=1526455513325', 1526455526, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(65, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=2%2C3%2C4&_=1526455513326', 1526455527, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(66, '字段信息不存在', 'http://localhost/phpok/admin.php?c=form&f=quickadd&id=4&tid=393', 1526455531, 'admin', 'form', 'quickadd', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(67, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526455581271', 1526455581, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(68, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=3&_=1526455581272', 1526455585, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(69, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=3%2C2&_=1526455581273', 1526455586, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(70, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526456041542', 1526456041, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(71, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=2&_=1526456041543', 1526456068, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(72, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=2%2C3&_=1526456041544', 1526456069, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(73, '字段信息不存在', 'http://localhost/phpok/admin.php?c=form&f=quickadd&id=2&tid=22', 1526456072, 'admin', 'form', 'quickadd', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(74, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526456397117', 1526456397, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(75, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=4&_=1526456397118', 1526456401, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(76, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=4%2C2&_=1526456397119', 1526456401, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(77, '字段信息不存在', 'http://localhost/phpok/admin.php?c=form&f=quickadd&id=4&tid=393', 1526456407, 'admin', 'form', 'quickadd', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(78, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526456465821', 1526456466, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(79, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=4&_=1526456465822', 1526456469, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(80, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=4%2C2&_=1526456465823', 1526456470, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(81, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526456886014', 1526456886, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(82, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&content=3&_=1526456921058', 1526456947, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(83, '&lt;div class=&quot;list&quot;&gt;\n&lt;table width=&quot;100%&quot; class=&quot;list&quot; cellpadding=&quot;0&quot; cellspacing=&quot;0&quot;&gt;\n&lt;tr&gt;\n	&lt;th width=&quot;180px&quot;&gt;ID&lt;/th&gt;\n		&lt;th class=&quot;lft&quot;&gt;联系地址&lt;/th&gt', 'http://localhost/phpok/admin.php?c=form&f=redata&id=393&_=1526456921059', 1526456959, 'admin', 'form', 'redata', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(84, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526457004289', 1526457004, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526455330', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(85, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1526457386681', 1526457386, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1371&_noCache=0.1526457020', 0, '1pk64pfdlfrv7mu2acsg1mp4n7'),
(86, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526520966, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526520961', 0, 't353rkekmuhu2k36cddgrgumb3'),
(87, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526543610, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526543604', 0, 'j3pkktomq5cr3nqpnijm813lu2'),
(88, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526609940, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526609935', 0, 'pss6aa2g9e5itpbmjhnqi1k296'),
(89, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526644861, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526644856', 0, '4g7i7if8mse0horc5om0mrqft1'),
(90, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526711836, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526711831', 0, 'fj90qg5hogbhsf1s8tgj083200'),
(91, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526787192, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526787187', 0, '36hio34kv1p2jffm3j38d8ef47'),
(92, '您已成功登录', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526787920', 1526789598, 'admin', 'login', 'index', 1, 0, '::1', '', 0, '36hio34kv1p2jffm3j38d8ef47'),
(93, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526789817, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526789812', 0, '36hio34kv1p2jffm3j38d8ef47'),
(94, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1526883190, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1526883184', 0, 'b5oth30sahh52vqlnaisgg1d64'),
(95, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1527149259, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1527149253', 0, 'c49sl3jv3t8k3mo8l21rirvq46'),
(96, '验证码填写不正确', 'http://localhost/phpok/admin.php?c=login&f=ok', 1527581049, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1527581044', 0, '108b1go9og3a0b4p3s1flhjh15'),
(97, 'code:', 'http://localhost/phpok/admin.php?c=gateway&f=extmanage&update=2&type=ajax&id=13&manageid=send&tplcode=35&_=1527581073707', 1527581077, 'admin', 'gateway', 'extmanage', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=gateway&f=extmanage&id=13&manageid=send', 0, '108b1go9og3a0b4p3s1flhjh15'),
(98, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1527680145, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1527680140', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(99, '1', 'http://localhost/phpok/admin.php?c=order&f=express_check&id=1&_=1527680273690', 1527680275, 'admin', 'order', 'express_check', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=order', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(100, 'refresh', 'http://localhost/phpok/api.php?c=express&id=1&_=1527680297487', 1527680300, 'api', 'express', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=order&f=express&id=1', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(101, '1', 'http://localhost/phpok/admin.php?c=order&f=express_check&id=1&_=1527680273691', 1527680341, 'admin', 'order', 'express_check', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=order', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(102, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1527680511', 1527680521, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1527680508', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(103, '会员&lt;span class=&quot;red&quot;&gt; admin &lt;/span&gt;成功退出', 'http://localhost/phpok/index.php?c=logout', 1527681413, 'www', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/index.php?c=usercp&f=fav&_noCache=0.1527681145', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(104, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1527681494', 1527681502, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(105, '会员&lt;span class=&quot;red&quot;&gt; admin &lt;/span&gt;成功退出', 'http://localhost/phpok/index.php?c=logout', 1527681510, 'www', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/index.php?c=usercp&_noCache=0.1527681504', 0, 'kv3vq1cacvhthc9799a8elgo85'),
(106, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1527749064, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1527749056', 0, 'n6asnjkmb33hkejm90ojgnfuv2'),
(107, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1527763862323', 1527763862, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1370&_noCache=0.1527763859', 0, 'n6asnjkmb33hkejm90ojgnfuv2'),
(108, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1527765634383', 1527765634, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1370&_noCache=0.1527765632', 0, 'n6asnjkmb33hkejm90ojgnfuv2'),
(109, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1527765702504', 1527765702, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1370&_noCache=0.1527765632', 0, 'n6asnjkmb33hkejm90ojgnfuv2'),
(110, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1527841607, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1527841601', 0, 'ee9epilngojubjfr4f9l4mo4r5'),
(111, '&lt;span class=&quot;red&quot;&gt;HTML转PDF&lt;/span&gt; 安装成功', 'http://localhost/phpok/admin.php?c=plugin&f=install_save&_noCache=0.1527841980', 1527841981, 'admin', 'plugin', 'install_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=install&id=html2pdf', 0, 'ee9epilngojubjfr4f9l4mo4r5'),
(112, '&lt;span class=&quot;red&quot;&gt;PDF订单生成&lt;/span&gt; 安装成功', 'http://localhost/phpok/admin.php?c=plugin&f=install_save&_noCache=0.1527842224', 1527842226, 'admin', 'plugin', 'install_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=install&id=ordertopdf', 0, 'ee9epilngojubjfr4f9l4mo4r5'),
(113, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1527935710, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1527935704', 0, 'p53u6c9cic9tobffdfu1afkpf5'),
(114, '&lt;span class=&quot;red&quot;&gt;演示插件&lt;/span&gt; 安装成功', 'http://localhost/phpok/admin.php?c=plugin&f=install_save&_noCache=0.1527938662', 1527938668, 'admin', 'plugin', 'install_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=install&id=demo', 0, 'p53u6c9cic9tobffdfu1afkpf5'),
(115, '插件卸载成功', 'http://localhost/phpok/admin.php?c=plugin&f=uninstall&id=demo&_=1527938669493', 1527939714, 'admin', 'plugin', 'uninstall', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&_noCache=0.1527938668', 0, 'p53u6c9cic9tobffdfu1afkpf5'),
(116, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528009073, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528009067', 0, '54b42ptuqjr6cvtcskkvh5iok4'),
(117, '附件更新中，当前已更新数量：&lt;span class=\'red\'&gt;&lt;strong&gt;9&lt;/strong&gt;&lt;/span&gt;', 'http://localhost/phpok/admin.php?c=res&f=update_pl&id=all&_noCache=0.3437132578349489', 1528009229, 'admin', 'res', 'update_pl', 1, 0, '::1', 'http://localhost/phpok/admin.php', 0, '54b42ptuqjr6cvtcskkvh5iok4'),
(118, '附件更新中，当前已更新数量：&lt;span class=\'red\'&gt;&lt;strong&gt;17&lt;/strong&gt;&lt;/span&gt;', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009229&id=all&pageid=8', 1528009230, 'admin', 'res', 'update_pl', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=update_pl&id=all&_noCache=0.3437132578349489', 0, '54b42ptuqjr6cvtcskkvh5iok4'),
(119, '附件更新中，当前已更新数量：&lt;span class=\'red\'&gt;&lt;strong&gt;25&lt;/strong&gt;&lt;/span&gt;', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009230&id=all&pageid=16', 1528009232, 'admin', 'res', 'update_pl', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009229&id=all&pageid=8', 0, '54b42ptuqjr6cvtcskkvh5iok4'),
(120, '附件更新中，当前已更新数量：&lt;span class=\'red\'&gt;&lt;strong&gt;29&lt;/strong&gt;&lt;/span&gt;', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009232&id=all&pageid=24', 1528009233, 'admin', 'res', 'update_pl', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009230&id=all&pageid=16', 0, '54b42ptuqjr6cvtcskkvh5iok4'),
(121, '附件信息更新完毕，共更新数量：&lt;span class=\'red\'&gt;28&lt;/span&gt;，点击右上角关闭窗口^_^', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009233&id=all&pageid=28', 1528009234, 'admin', 'res', 'update_pl', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=update_pl&_noCache=0.1528009232&id=all&pageid=24', 0, '54b42ptuqjr6cvtcskkvh5iok4'),
(122, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528017296, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528013059', 0, 'r6tihm0vj6njtni907acnbv3m7'),
(123, '项目添加/更新成功', 'http://localhost/phpok/admin.php?c=system&f=save&_noCache=0.1528019277', 1528019963, 'admin', 'system', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=system&f=set&pid=5&_noCache=0.1528018641', 0, 'r6tihm0vj6njtni907acnbv3m7'),
(124, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528076945, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528076940', 0, 'rmtlnppehda6p757p4dlt6r3v5'),
(125, '项目添加/更新成功', 'http://localhost/phpok/admin.php?c=system&f=save&_noCache=0.1528077745', 1528077763, 'admin', 'system', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=system&f=set&pid=5&_noCache=0.1528077739', 0, 'rmtlnppehda6p757p4dlt6r3v5'),
(126, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528095184, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528095178', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(127, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1528095203', 1528095208, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1528095200', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(128, 'add', 'http://localhost/phpok/api.php?c=fav&f=act&id=1368&_=1528103464378', 1528103475, 'api', 'fav', 'act', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1368', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(129, 'delete', 'http://localhost/phpok/api.php?c=fav&f=act&id=1368&_=1528103464379', 1528103483, 'api', 'fav', 'act', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1368', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(130, 'delete', 'http://localhost/phpok/api.php?c=fav&f=act&id=1369&_=1528103800787', 1528103803, 'api', 'fav', 'act', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1369', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(131, 'add', 'http://localhost/phpok/api.php?c=fav&f=act&id=1369&_=1528103800788', 1528103808, 'api', 'fav', 'act', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1369', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(132, '更新成功', 'http://localhost/phpok/admin.php?c=list&f=attr_set&ids=1370&val=h&type=add&_=1528105915955', 1528105997, 'admin', 'list', 'attr_set', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=action&id=43&_noCache=0.235613202823848', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(133, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1528106018956', 1528106019, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1370&_noCache=0.1528105997', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(134, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&_=1528106089111', 1528106089, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1370&_noCache=0.1528106022', 0, '6kge0aievnrq5qogpcsfbq7pb3'),
(135, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528163641, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528163635', 0, '3gjksftiuobgk2l2h95271adl2'),
(136, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528370836, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528370831', 0, 'lulpfsp7cn4kmq3c8krqrk7s50'),
(137, '项目添加/更新成功', 'http://localhost/phpok/admin.php?c=system&f=save&_noCache=0.1528371806', 1528371886, 'admin', 'system', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=system&f=set&pid=1&_noCache=0.1528371799', 0, 'lulpfsp7cn4kmq3c8krqrk7s50'),
(138, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528429287, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528429282', 0, '0glahfjgtc1itlnj492d4uiri0'),
(139, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528512177, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528512170', 0, 'h65pc9atgp0psim4ii6lthtod7'),
(140, '您没有配置环境权限', 'http://localhost/phpok/admin.php?c=appsys&f=setting', 1528512810, 'admin', 'appsys', 'setting', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, 'h65pc9atgp0psim4ii6lthtod7'),
(141, '项目添加/更新成功', 'http://localhost/phpok/admin.php?c=system&f=save&_noCache=0.1528512823', 1528512838, 'admin', 'system', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=system&f=set&id=95&_noCache=0.1528512818', 0, 'h65pc9atgp0psim4ii6lthtod7'),
(142, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528724413, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528724408', 0, 'ib4vsamn8e5m5vhpch5llk56b1'),
(143, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1528765250, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1528765244', 0, '4ukqpdarrm8f5dk1vtfa94dvr2'),
(144, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1529065103', 1529065111, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1529065099', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(145, '999.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=999.0000&_=1529065119265', 1529065121, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1761', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(146, '1099.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=1099&_=1529065119266', 1529065122, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1761', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(147, '1', 'http://localhost/phpok/api.php?c=cart&f=total&_=1529065126293', 1529065126, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?c=usercp&_noCache=0.1529065118', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(148, '3288.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=3288.0000&_=1529065133671', 1529065135, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1763', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(149, '4587.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=4587&_=1529065133672', 1529065137, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1763', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(150, '2', 'http://localhost/phpok/api.php?c=cart&f=total&_=1529065180596', 1529065180, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1763', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(151, '3', 'http://localhost/phpok/api.php?c=cart&f=total&_=1529065180600', 1529065186, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1763', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(152, '6898.00元', 'http://localhost/phpok/api.php?c=cart&f=price_format&price=6898&_=1529065199975', 1529065201, 'api', 'cart', 'price_format', 0, 23, '::1', 'http://localhost/phpok/index.php?id=1762', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(153, '4', 'http://localhost/phpok/api.php?c=cart&f=total&_=1529065203107', 1529065203, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?c=cart&f=checkout&id[]=4', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(154, '4', 'http://localhost/phpok/api.php?c=cart&f=total&_=1529065265007', 1529065265, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?c=cart&f=checkout&id[]=4', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(155, '4', 'http://localhost/phpok/api.php?c=cart&f=total&_=1529065326966', 1529065327, 'api', 'cart', 'total', 0, 23, '::1', 'http://localhost/phpok/index.php?c=cart&_noCache=0.1529065264', 0, 'lc8jeadg9ibu9o09b6l49v6rj6'),
(156, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1529409211', 1529409220, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1529409209', 0, 'lfeoj9vllkkvt9iato5uph90v7'),
(157, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529461964, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529461959', 0, '35a9m84m4c22ar0o97ivnntot3'),
(158, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529543395, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529543390', 0, 'joov2jg3hioaoqve5vfnuort40'),
(159, '项目添加/更新成功', 'http://localhost/phpok/admin.php?c=system&f=save&_noCache=0.1529544415', 1529544430, 'admin', 'system', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=system&f=set&id=95&_noCache=0.1529544411', 0, 'l8d42vfndai06pms9fbt1e2rb1'),
(160, '远程更新数据失败', 'http://localhost/phpok/admin.php?c=appsys&f=remote&_=1529544459605', 1529544462, 'admin', 'appsys', 'remote', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=appsys&menu_id=95&_noCache=0.7600033436962981', 0, 'l8d42vfndai06pms9fbt1e2rb1'),
(161, '未配置好远程服务器环境，请查看帮助进行配置', 'http://localhost/phpok/admin.php?c=appsys&f=remote&_=1529544807981', 1529544813, 'admin', 'appsys', 'remote', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=appsys&menu_id=95&_noCache=0.7600033436962981', 0, 'l8d42vfndai06pms9fbt1e2rb1'),
(162, '远程更新数据失败', 'http://localhost/phpok/admin.php?c=appsys&f=remote&_=1529545287751', 1529545288, 'admin', 'appsys', 'remote', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=appsys&menu_id=95&_noCache=0.7600033436962981', 0, 'l8d42vfndai06pms9fbt1e2rb1'),
(163, '远程更新数据失败', 'http://localhost/phpok/admin.php?c=appsys&f=remote&_=1529545287753', 1529545355, 'admin', 'appsys', 'remote', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=appsys&menu_id=95&_noCache=0.7600033436962981', 0, 'l8d42vfndai06pms9fbt1e2rb1'),
(164, '非 JSON 数据', 'http://localhost/phpok/admin.php?c=appsys&f=remote&_=1529545287754', 1529546022, 'admin', 'appsys', 'remote', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=appsys&menu_id=95&_noCache=0.7600033436962981', 0, 'l8d42vfndai06pms9fbt1e2rb1'),
(165, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529633537, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529633532', 0, 'h7667j2luqeslbb2f9he5qeib4'),
(166, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529738927, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529738918', 0, 'vrboiqq82dog6h2juqoh8899g7'),
(167, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529803056, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529803050', 0, 'udtrj867dfnr73hea71qc583q4'),
(168, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529926466, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529926461', 0, 'o9p3qf5l48gpg85105ltbac324'),
(169, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1529973331, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1529973326', 0, 'l0s2blliq7ombt970hi6e1v9f5'),
(170, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1530014295, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1530014289', 0, '5rfpdohbhjet7srllfaklouua6'),
(171, '&lt;span class=&quot;red&quot;&gt;数据库比较工具&lt;/span&gt; 安装成功', 'http://localhost/phpok/admin.php?c=plugin&f=install_save&_noCache=0.1530014332', 1530014335, 'admin', 'plugin', 'install_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=install&id=sqldiff', 0, '5rfpdohbhjet7srllfaklouua6'),
(172, '主数据库配置不完整', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=rs&id=sqldiff&_noCache=0.1530014674', 1530014997, 'admin', 'plugin', 'exec', 1, 0, '::1', '', 0, '5rfpdohbhjet7srllfaklouua6'),
(173, '_cache/3fd0ecad395dd63a.zip', 'http://localhost/phpok/admin.php?c=upload&f=zip&_noCache=0.1530017374&PHPSESSION=5rfpdohbhjet7srllfaklouua6&id=WU_FILE_0&name=99999.zip&type=application%2Fx-zip-compressed&lastModifiedDate=Tue+Jun+26+2018+20%3A49%3A11+GMT%2B0800&size=4522', 1530017384, 'admin', 'upload', 'zip', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=upload', 0, '5rfpdohbhjet7srllfaklouua6'),
(174, '&lt;span class=&quot;red&quot;&gt;赞插件&lt;/span&gt; 安装成功', 'http://localhost/phpok/admin.php?c=plugin&f=install_save&_noCache=0.1530018949', 1530018952, 'admin', 'plugin', 'install_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=install&id=zhan', 0, '5rfpdohbhjet7srllfaklouua6'),
(175, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1530350869, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1530350864', 0, 'o60uuiae5m20r9u82cnuijjc72'),
(176, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1530433676, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1530433671', 0, '0jj5mmhq5chsomufm9pej4emp5');
INSERT INTO `qinggan_log` (`id`, `note`, `url`, `dateline`, `app_id`, `ctrl`, `func`, `admin_id`, `user_id`, `ip`, `referer`, `mask`, `session_id`) VALUES
(177, '删除成功', 'http://localhost/phpok/admin.php?c=system&f=delete&id=94&_=1530433711779', 1530433718, 'admin', 'system', 'delete', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=system&menu_id=9&_noCache=0.3476714957878686', 0, '0jj5mmhq5chsomufm9pej4emp5'),
(178, '安装成功', 'http://localhost/phpok/admin.php?c=appsys&f=install&id=fav&_=1530433833966', 1530433838, 'admin', 'appsys', 'install', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=appsys&menu_id=95&_noCache=0.06858955026158708', 0, '0jj5mmhq5chsomufm9pej4emp5'),
(179, '会员登录成功', 'http://localhost/phpok/index.php?c=login&f=ok&_noCache=0.1530439456', 1530439460, 'www', 'login', 'ok', 0, 23, '::1', 'http://localhost/phpok/index.php?c=login&_noCache=0.1530439453', 0, '0jj5mmhq5chsomufm9pej4emp5'),
(180, '验证通过', 'http://localhost/phpok/admin.php?c=user&f=chk&id=23&user=admin&mobile=15818533971&email=40782502%40qq.com&_=1530439483575', 1530439685, 'admin', 'user', 'chk', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=user&f=set&id=23&_noCache=0.1530439478', 0, '0jj5mmhq5chsomufm9pej4emp5'),
(181, '会员编辑成功', 'http://localhost/phpok/admin.php?c=user&f=setok&_noCache=0.1530439483', 1530439685, 'admin', 'user', 'setok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=user&f=set&id=23&_noCache=0.1530439478', 0, '0jj5mmhq5chsomufm9pej4emp5'),
(182, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1530783378, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1530783374', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(183, '&lt;span class=&quot;red&quot;&gt;采集器&lt;/span&gt; 安装成功', 'http://localhost/phpok/admin.php?c=plugin&f=install_save&_noCache=0.1530789878', 1530789880, 'admin', 'plugin', 'install_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=install&id=collection', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(184, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/website-knowledge/2.html&lt;/span&gt; 采集到网址数量：&lt;span class=&quot;red b&quot;&gt;11&lt;/span&gt; 条', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=url2&tid=1&listurl=https%3A%2F%2Fwww.phpok.com%2Fwebsite-knowledge%2F2.html&_=1530790242030', 1530790243, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(185, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/website-knowledge/3.html&lt;/span&gt; 采集到网址数量：&lt;span class=&quot;red b&quot;&gt;11&lt;/span&gt; 条', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=url2&tid=1&listurl=https%3A%2F%2Fwww.phpok.com%2Fwebsite-knowledge%2F3.html&_=1530790242031', 1530790245, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(186, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/website-knowledge/4.html&lt;/span&gt; 采集到网址数量：&lt;span class=&quot;red b&quot;&gt;10&lt;/span&gt; 条', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=url2&tid=1&listurl=https%3A%2F%2Fwww.phpok.com%2Fwebsite-knowledge%2F4.html&_=1530790242032', 1530790246, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(187, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20557.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242033', 1530790252, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(188, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20556.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242034', 1530790253, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(189, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20555.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242035', 1530790254, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(190, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20554.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242036', 1530790255, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(191, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20553.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242037', 1530790256, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(192, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20552.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242038', 1530790258, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(193, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20551.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242039', 1530790259, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(194, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20550.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242040', 1530790260, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(195, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20547.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242041', 1530790261, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(196, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20546.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242042', 1530790262, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(197, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20545.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242043', 1530790263, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(198, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20544.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242044', 1530790265, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(199, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20543.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242045', 1530790266, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(200, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20542.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242046', 1530790267, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(201, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20541.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242047', 1530790268, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(202, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20540.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242048', 1530790269, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(203, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20538.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242049', 1530790271, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(204, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20535.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242050', 1530790272, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(205, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20533.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242051', 1530790273, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(206, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20532.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242052', 1530790274, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(207, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20531.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242053', 1530790275, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(208, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20530.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242054', 1530790276, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(209, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20529.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242055', 1530790278, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(210, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20528.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242056', 1530790279, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(211, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20527.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242057', 1530790280, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(212, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20526.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242058', 1530790281, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(213, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20525.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242059', 1530790282, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(214, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20524.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242060', 1530790283, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(215, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20523.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242061', 1530790285, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(216, 'end', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530790242062', 1530790286, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790239', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(217, '主题：&lt;span class=&quot;red&quot;&gt;装修网站让居住梦想轻松实现 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=post_save&id=collection&_noCache=0.1530790370&cid=1', 1530790375, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530790365', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(218, '主题：&lt;span class=&quot;red&quot;&gt;模拟旅游正成为旅游网站的热门应用 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.421492&cid=1&numid=1', 1530790377, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=post_save&id=collection&_noCache=0.1530790370&cid=1', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(219, '主题：&lt;span class=&quot;red&quot;&gt;做好网站助农业水产养殖实现电子商务化 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.74594&cid=1&numid=2', 1530790380, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.421492&cid=1&numid=1', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(220, '主题：&lt;span class=&quot;red&quot;&gt;餐饮网站做好网上订餐的启示 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.549261&cid=1&numid=3', 1530790391, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.74594&cid=1&numid=2', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(221, '主题：&lt;span class=&quot;red&quot;&gt;好网站助医院保健机构赢得好名声 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.816429&cid=1&numid=4', 1530790393, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.549261&cid=1&numid=3', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(222, '主题：&lt;span class=&quot;red&quot;&gt;自助建站让金融典当拍卖公司更具竞争力 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.585576&cid=1&numid=5', 1530790395, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.816429&cid=1&numid=4', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(223, '主题：&lt;span class=&quot;red&quot;&gt;环保生态生物技术等行业也需网站建设来助力 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.159793&cid=1&numid=6', 1530790398, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.585576&cid=1&numid=5', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(224, '主题：&lt;span class=&quot;red&quot;&gt;网站建站助广告会展印刷行业业绩冲天 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.799299&cid=1&numid=7', 1530790400, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.159793&cid=1&numid=6', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(225, '主题：&lt;span class=&quot;red&quot;&gt;行业协会和网站也需网站来彰显形象 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.970270&cid=1&numid=8', 1530790402, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.799299&cid=1&numid=7', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(226, '主题：&lt;span class=&quot;red&quot;&gt;美容美发休闲养生网站建设成就事业 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.235233&cid=1&numid=9', 1530790404, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.970270&cid=1&numid=8', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(227, '主题：&lt;span class=&quot;red&quot;&gt;咖啡网站建设提升商家收入 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.266261&cid=1&numid=10', 1530790407, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.235233&cid=1&numid=9', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(228, '主题：&lt;span class=&quot;red&quot;&gt;旅游网站建设是向公众展示旅游信息的首要平台 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.877247&cid=1&numid=11', 1530790409, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.266261&cid=1&numid=10', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(229, '主题：&lt;span class=&quot;red&quot;&gt;物业和家政业需要在网站上进行品牌宣传 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.356475&cid=1&numid=12', 1530790411, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.877247&cid=1&numid=11', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(230, '主题：&lt;span class=&quot;red&quot;&gt;旅游网站建设和推广的那点事儿 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.965769&cid=1&numid=13', 1530790413, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.356475&cid=1&numid=12', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(231, '主题：&lt;span class=&quot;red&quot;&gt;旅游网站建设后应处理好的事情 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.177437&cid=1&numid=14', 1530790415, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.965769&cid=1&numid=13', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(232, '主题：&lt;span class=&quot;red&quot;&gt;好公司更需优秀网站建设公司帮助做最好的网站设计 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.195292&cid=1&numid=15', 1530790418, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.177437&cid=1&numid=14', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(233, '主题：&lt;span class=&quot;red&quot;&gt;优秀网站设计带来更高经济效益 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.490981&cid=1&numid=16', 1530790420, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.195292&cid=1&numid=15', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(234, '主题：&lt;span class=&quot;red&quot;&gt;网站做得好 客源滚滚来 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.951871&cid=1&numid=17', 1530790422, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.490981&cid=1&numid=16', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(235, '主题：&lt;span class=&quot;red&quot;&gt;酒店网站拉近与客户的服务距离 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.171757&cid=1&numid=18', 1530790424, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.951871&cid=1&numid=17', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(236, '主题：&lt;span class=&quot;red&quot;&gt;做好网站建设，发挥潮流影响力 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.640623&cid=1&numid=19', 1530790426, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.171757&cid=1&numid=18', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(237, '主题：&lt;span class=&quot;red&quot;&gt;网站成为建筑和装修企业向客户展示的窗口 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.414725&cid=1&numid=20', 1530790429, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.640623&cid=1&numid=19', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(238, '主题：&lt;span class=&quot;red&quot;&gt;物业网站建设提升物业公司服务形象 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.843227&cid=1&numid=21', 1530790431, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.414725&cid=1&numid=20', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(239, '主题：&lt;span class=&quot;red&quot;&gt;网站设计应用助建筑装修公司实现梦想 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.32659&cid=1&numid=22', 1530790433, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.843227&cid=1&numid=21', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(240, '主题：&lt;span class=&quot;red&quot;&gt;网站建设助力货运、贸易、物流与世界接轨 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.635426&cid=1&numid=23', 1530790435, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.32659&cid=1&numid=22', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(241, '主题：&lt;span class=&quot;red&quot;&gt;网站建设成功让物业和家政双赢 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.653645&cid=1&numid=24', 1530790441, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.635426&cid=1&numid=23', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(242, '主题：&lt;span class=&quot;red&quot;&gt;创意网站帮助企业吸引客户 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.794525&cid=1&numid=25', 1530790443, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.653645&cid=1&numid=24', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(243, '主题：&lt;span class=&quot;red&quot;&gt;网站建设成功助力水产等食品行业提升竞争力 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.38580&cid=1&numid=26', 1530790445, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.794525&cid=1&numid=25', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(244, '主题：&lt;span class=&quot;red&quot;&gt;优秀网站助您吸引更多客户 - 建站知识 - 锟铻科技 - PHPOK企业站，专业的网站建设系统&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.248677&cid=1&numid=28', 1530790450, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.86890&cid=1&numid=27', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(245, '数据已发布完成，请到网站平台上检查数据是否发布完整', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.465451&cid=1&numid=29', 1530790452, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.248677&cid=1&numid=28', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(246, '主题删除成功', 'http://localhost/phpok/admin.php?c=list&f=del&id=1878%2C1877%2C1876%2C1875%2C1874%2C1873%2C1872%2C1871%2C1897%2C1896%2C1895%2C1894%2C1893%2C1892%2C1891%2C1890%2C1889%2C1888%2C1887%2C1886&_=1530791291018', 1530791308, 'admin', 'list', 'del', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=action&_noCache=0.1530791278&id=43&cateid=68', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(247, '数据不存在', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=info_delete&lid=26%2C25%2C24%2C23%2C22%2C21%2C20%2C19%2C18%2C17%2C16%2C15%2C14%2C13%2C12%2C11%2C10%2C8%2C7%2C6&_=1530792885372', 1530792887, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=collection_list&_noCache=0.1530791378&tid=1', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(248, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/website-knowledge/2.html&lt;/span&gt; 采集到网址数量：&lt;span class=&quot;red b&quot;&gt;11&lt;/span&gt; 条', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=url2&tid=1&listurl=https%3A%2F%2Fwww.phpok.com%2Fwebsite-knowledge%2F2.html&_=1530792923386', 1530792925, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(249, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/website-knowledge/3.html&lt;/span&gt; 采集到网址数量：&lt;span class=&quot;red b&quot;&gt;11&lt;/span&gt; 条', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=url2&tid=1&listurl=https%3A%2F%2Fwww.phpok.com%2Fwebsite-knowledge%2F3.html&_=1530792923387', 1530792927, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(250, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/website-knowledge/4.html&lt;/span&gt; 采集到网址数量：&lt;span class=&quot;red b&quot;&gt;10&lt;/span&gt; 条', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=url2&tid=1&listurl=https%3A%2F%2Fwww.phpok.com%2Fwebsite-knowledge%2F4.html&_=1530792923388', 1530792928, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(251, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20557.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923389', 1530792934, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(252, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20556.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923390', 1530792936, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(253, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20555.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923391', 1530792937, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(254, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20554.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923392', 1530792938, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(255, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20553.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923393', 1530792939, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(256, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20552.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923394', 1530792940, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(257, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20551.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923395', 1530792942, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(258, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20550.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923396', 1530792943, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(259, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20547.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923397', 1530792944, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(260, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20546.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923398', 1530792945, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(261, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20545.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923399', 1530792946, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(262, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20544.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923400', 1530792947, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(263, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20543.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923401', 1530792949, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(264, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20542.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923402', 1530792950, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(265, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20541.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923403', 1530792951, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(266, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20540.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923404', 1530792952, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(267, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20538.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923405', 1530792953, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(268, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20535.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923406', 1530792955, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(269, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20533.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923407', 1530792956, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(270, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20532.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923408', 1530792957, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(271, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20531.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923409', 1530792958, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(272, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20530.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923410', 1530792959, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(273, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20529.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923411', 1530792960, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(274, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20528.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923412', 1530792962, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(275, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20527.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923413', 1530792963, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(276, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20526.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923414', 1530792964, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(277, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20525.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923415', 1530792965, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(278, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20524.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923416', 1530792966, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(279, '网址：&lt;span class=&quot;red&quot;&gt;https://www.phpok.com/20523.html&lt;/span&gt; 数据采集完毕，请稍候，正在执行下一动作&hellip;', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923417', 1530792967, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(280, 'end', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=content2&cid=1&_=1530792923418', 1530792969, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792921', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(281, '主题：&lt;span class=&quot;red&quot;&gt;装修网站让居住梦想轻松实现&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=post_save&id=collection&_noCache=0.1530792981&cid=1', 1530792984, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=manage&id=collection&_noCache=0.1530792979', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(282, '主题：&lt;span class=&quot;red&quot;&gt;模拟旅游正成为旅游网站的热门应用&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.545545&cid=1&numid=1', 1530792986, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&exec=post_save&id=collection&_noCache=0.1530792981&cid=1', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(283, '主题：&lt;span class=&quot;red&quot;&gt;做好网站助农业水产养殖实现电子商务化&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.923471&cid=1&numid=2', 1530792988, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.545545&cid=1&numid=1', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(284, '主题：&lt;span class=&quot;red&quot;&gt;餐饮网站做好网上订餐的启示&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.418411&cid=1&numid=3', 1530792991, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.923471&cid=1&numid=2', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(285, '主题：&lt;span class=&quot;red&quot;&gt;好网站助医院保健机构赢得好名声&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.259342&cid=1&numid=4', 1530792993, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.418411&cid=1&numid=3', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(286, '主题：&lt;span class=&quot;red&quot;&gt;自助建站让金融典当拍卖公司更具竞争力&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.861506&cid=1&numid=5', 1530792995, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.259342&cid=1&numid=4', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(287, '主题：&lt;span class=&quot;red&quot;&gt;环保生态生物技术等行业也需网站建设来助力&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.147738&cid=1&numid=6', 1530792997, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.861506&cid=1&numid=5', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(288, '主题：&lt;span class=&quot;red&quot;&gt;网站建站助广告会展印刷行业业绩冲天&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.996676&cid=1&numid=7', 1530792999, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.147738&cid=1&numid=6', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(289, '主题：&lt;span class=&quot;red&quot;&gt;行业协会和网站也需网站来彰显形象&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.840025&cid=1&numid=8', 1530793001, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.996676&cid=1&numid=7', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(290, '主题：&lt;span class=&quot;red&quot;&gt;美容美发休闲养生网站建设成就事业&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.410133&cid=1&numid=9', 1530793004, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.840025&cid=1&numid=8', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(291, '主题：&lt;span class=&quot;red&quot;&gt;咖啡网站建设提升商家收入&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.824133&cid=1&numid=10', 1530793006, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.410133&cid=1&numid=9', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(292, '主题：&lt;span class=&quot;red&quot;&gt;旅游网站建设是向公众展示旅游信息的首要平台&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.265899&cid=1&numid=11', 1530793008, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.824133&cid=1&numid=10', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(293, '主题：&lt;span class=&quot;red&quot;&gt;物业和家政业需要在网站上进行品牌宣传&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.42055&cid=1&numid=12', 1530793010, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.265899&cid=1&numid=11', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(294, '主题：&lt;span class=&quot;red&quot;&gt;旅游网站建设和推广的那点事儿&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.583310&cid=1&numid=13', 1530793012, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.42055&cid=1&numid=12', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(295, '主题：&lt;span class=&quot;red&quot;&gt;旅游网站建设后应处理好的事情&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.137345&cid=1&numid=14', 1530793015, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.583310&cid=1&numid=13', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(296, '主题：&lt;span class=&quot;red&quot;&gt;好公司更需优秀网站建设公司帮助做最好的网站设计&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.484515&cid=1&numid=15', 1530793017, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.137345&cid=1&numid=14', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(297, '主题：&lt;span class=&quot;red&quot;&gt;优秀网站设计带来更高经济效益&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.742620&cid=1&numid=16', 1530793019, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.484515&cid=1&numid=15', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(298, '主题：&lt;span class=&quot;red&quot;&gt;网站做得好 客源滚滚来&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.421976&cid=1&numid=17', 1530793021, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.742620&cid=1&numid=16', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(299, '主题：&lt;span class=&quot;red&quot;&gt;酒店网站拉近与客户的服务距离&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.717060&cid=1&numid=18', 1530793023, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.421976&cid=1&numid=17', 0, 'k8ngm6r0jk4mbju7poqvmokn43');
INSERT INTO `qinggan_log` (`id`, `note`, `url`, `dateline`, `app_id`, `ctrl`, `func`, `admin_id`, `user_id`, `ip`, `referer`, `mask`, `session_id`) VALUES
(300, '主题：&lt;span class=&quot;red&quot;&gt;做好网站建设，发挥潮流影响力&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.445964&cid=1&numid=19', 1530793026, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.717060&cid=1&numid=18', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(301, '主题：&lt;span class=&quot;red&quot;&gt;网站成为建筑和装修企业向客户展示的窗口&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.373908&cid=1&numid=20', 1530793028, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.445964&cid=1&numid=19', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(302, '主题：&lt;span class=&quot;red&quot;&gt;物业网站建设提升物业公司服务形象&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.452067&cid=1&numid=21', 1530793030, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.373908&cid=1&numid=20', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(303, '主题：&lt;span class=&quot;red&quot;&gt;网站设计应用助建筑装修公司实现梦想&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.737967&cid=1&numid=22', 1530793032, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.452067&cid=1&numid=21', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(304, '主题：&lt;span class=&quot;red&quot;&gt;网站建设助力货运、贸易、物流与世界接轨&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.428683&cid=1&numid=23', 1530793034, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.737967&cid=1&numid=22', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(305, '主题：&lt;span class=&quot;red&quot;&gt;网站建设成功让物业和家政双赢&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.823106&cid=1&numid=24', 1530793037, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.428683&cid=1&numid=23', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(306, '主题：&lt;span class=&quot;red&quot;&gt;创意网站帮助企业吸引客户&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.704522&cid=1&numid=25', 1530793039, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.823106&cid=1&numid=24', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(307, '主题：&lt;span class=&quot;red&quot;&gt;网站建设成功助力水产等食品行业提升竞争力&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.849753&cid=1&numid=26', 1530793041, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.704522&cid=1&numid=25', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(308, '主题：&lt;span class=&quot;red&quot;&gt;优秀网站助您吸引更多客户&lt;/span&gt;保存成功，进入下一步', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.512402&cid=1&numid=28', 1530793046, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.216109&cid=1&numid=27', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(309, '数据已发布完成，请到网站平台上检查数据是否发布完整', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.715278&cid=1&numid=29', 1530793048, 'admin', 'plugin', 'exec', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=plugin&f=exec&id=collection&exec=post_save&_noCache=0.512402&cid=1&numid=28', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(310, '数据调用中心配置成功', 'http://localhost/phpok/admin.php?c=call&f=save&_noCache=0.1530793913', 1530793917, 'admin', 'call', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=call&f=set&id=91&_noCache=0.1530793908', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(311, '数据调用中心配置成功', 'http://localhost/phpok/admin.php?c=call&f=save&_noCache=0.1530794048', 1530794050, 'admin', 'call', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=call&f=set&id=91&_noCache=0.1530793933', 0, 'k8ngm6r0jk4mbju7poqvmokn43'),
(312, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1530948714, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1530948707', 0, 'a3p0hcl4i4vlge1hnrmjfsmbt2'),
(313, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1531190822, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1531190816', 0, '60lk9okighsmkignm662cg9id0'),
(314, '上传的文件异常', 'http://localhost/phpok/admin.php?c=upload&f=save&_noCache=0.1531190827&PHPSESSION=60lk9okighsmkignm662cg9id0&cateid=1&id=WU_FILE_0&name=%E5%A4%87%E6%A1%88%E4%BF%A1%E6%81%AF%E7%99%BB%E8%AE%B0%E8%A1%A8.jpg&type=image%2Fjpeg&lastModifiedDate=2018%2F7%2F10+%E', 1531190843, 'admin', 'upload', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=add', 0, '60lk9okighsmkignm662cg9id0'),
(315, '上传的文件异常', 'http://localhost/phpok/admin.php?c=upload&f=save&_noCache=0.1531190966&PHPSESSION=60lk9okighsmkignm662cg9id0&cateid=1&id=WU_FILE_0&name=%E5%A4%87%E6%A1%88%E4%BF%A1%E6%81%AF%E7%99%BB%E8%AE%B0%E8%A1%A8.jpg&type=image%2Fjpeg&lastModifiedDate=2018%2F7%2F10+%E', 1531190972, 'admin', 'upload', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=add', 0, '60lk9okighsmkignm662cg9id0'),
(316, '附件上传失败', 'http://localhost/phpok/admin.php?c=upload&f=replace&oldid=1320&_noCache=0.1531190982', 1531190987, 'admin', 'upload', 'replace', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=set&id=1320', 0, '60lk9okighsmkignm662cg9id0'),
(317, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&content=0&_=1531192411650', 1531192411, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1928&_noCache=0.1531192408', 0, '60lk9okighsmkignm662cg9id0'),
(318, '上传的文件异常', 'http://localhost/phpok/admin.php?c=upload&f=save&_noCache=0.1531206893&PHPSESSION=60lk9okighsmkignm662cg9id0&cateid=1&id=WU_FILE_0&name=%E5%A4%87%E6%A1%88%E4%BF%A1%E6%81%AF%E7%99%BB%E8%AE%B0%E8%A1%A8.jpg&type=image%2Fjpeg&lastModifiedDate=2018%2F7%2F10+%E', 1531206897, 'admin', 'upload', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=add', 0, '60lk9okighsmkignm662cg9id0'),
(319, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1531476967, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1531476962', 0, '5ljuvvtd1q3ugjeg2q710kkj37'),
(320, '字段标识不符合系统要求，限小写字母、数字、中划线及下划线且必须是小写字母开头', 'http://localhost/phpok/admin.php?c=call&f=check&identifier=_ddd&_=1531477324504', 1531477335, 'admin', 'call', 'check', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=call&f=set&_noCache=0.1531477314', 0, '5ljuvvtd1q3ugjeg2q710kkj37'),
(321, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1531794726, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1531794720', 0, 'her0djnohgefsher2eoimbbjv1'),
(322, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1531808983, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1531808977', 0, 'rfgbrsqh9rcprb8qe7binbiff0'),
(323, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1531893963, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1531893959', 0, 'e7dpb97mkoq9qf3bvl2mdrjft5'),
(324, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1532742684, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1532742679', 0, 'gmol2t5gg2h7v4seqnlrj6l5m6'),
(325, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1532782620, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1532782615', 0, '35c0tagb04gf4c7qh26aic3dl3'),
(326, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533018348, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533018342', 0, '3v4lradt6l7p7u8bdjn1btcvh7'),
(327, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533124327, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533124318', 0, '39m8fio5vaedbin58ff38picp7'),
(328, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533177916, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533177910', 0, 't1pls4iep0bu98huhgen3hvab0'),
(329, '上传的文件异常', 'http://localhost/phpok/admin.php?c=upload&f=save&_noCache=0.1533177922&PHPSESSION=t1pls4iep0bu98huhgen3hvab0&cateid=1&id=WU_FILE_0&name=1-2.jpg&type=image%2Fjpeg&lastModifiedDate=2018%2F8%2F2+%E4%B8%8A%E5%8D%8810%3A45%3A33&size=281534&chunks=3&chunk=0', 1533177934, 'admin', 'upload', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=add', 0, 't1pls4iep0bu98huhgen3hvab0'),
(330, '上传的文件异常', 'http://localhost/phpok/admin.php?c=upload&f=save&_noCache=0.1533178229&PHPSESSION=t1pls4iep0bu98huhgen3hvab0&cateid=1&id=WU_FILE_0&name=1-2.jpg&type=image%2Fjpeg&lastModifiedDate=2018%2F8%2F2+%E4%B8%8A%E5%8D%8810%3A50%3A32&size=281534&chunks=3&chunk=0', 1533178233, 'admin', 'upload', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=res&f=add', 0, 't1pls4iep0bu98huhgen3hvab0'),
(331, '管理员账号不能为空', 'http://localhost/phpok/admin.php?c=login&f=ok&langid=cn&username=admin&password=123456&vercode=6876', 1533476651, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(332, '管理员账号不能为空', 'http://localhost/phpok/admin.php?c=login&f=ok&langid=cn&username=admin&password=123456&vercode=5351', 1533476844, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(333, '管理员账号不能为空', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533477532, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(334, '验证码填写不正确', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533477573, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(335, '管理员密码输入不正确', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533477598, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(336, '管理员账户系统锁定，解锁时间是 2018-08-06 00:00:08', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533477620, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(337, '管理员账户系统锁定，解锁时间是 2018-08-06 00:00:08', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533477707, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(338, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533477728, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533473624', 0, '18j0890vstgt41qj49mkvs0fv4'),
(339, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533477806, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, '18j0890vstgt41qj49mkvs0fv4'),
(340, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533557422, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533557323', 0, 'o0qmarr31tbndo7vo336addug3'),
(341, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533557600, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, 'o0qmarr31tbndo7vo336addug3'),
(342, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533557618, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533557600', 0, 'o0qmarr31tbndo7vo336addug3'),
(343, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533558149, 'admin', 'logout', 'index', 0, 0, '::1', '', 0, 'o0qmarr31tbndo7vo336addug3'),
(344, '验证码填写不正确', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533558228, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533558149', 0, 'o0qmarr31tbndo7vo336addug3'),
(345, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533560658, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, 'o0qmarr31tbndo7vo336addug3'),
(346, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533560663, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533560658', 0, 'o0qmarr31tbndo7vo336addug3'),
(347, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533564506, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, 'o0qmarr31tbndo7vo336addug3'),
(348, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533564514, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533564506', 0, 'o0qmarr31tbndo7vo336addug3'),
(349, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533605978, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533605972', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(350, '1111', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533608461, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(351, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533608975, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(352, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533609259, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(353, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533609361, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(354, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533610346, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(355, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533610690, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(356, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533611456, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533611450', 0, 'hdumpnba8pqnikv6d5ht3hrjc6'),
(357, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533612218, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(358, '管理员密码不正确', 'http://localhost/phpok/admin.php?c=me&f=pass_submit', 1533612579, 'admin', 'me', 'pass_submit', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=me&f=pass', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(359, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533624173, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(360, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533624177, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533624173', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(361, 'ok', 'http://localhost/phpok/admin.php?c=inp&type=user&content=0&_=1533631048377', 1533631048, 'admin', 'inp', 'index', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=list&f=edit&id=1928&_noCache=0.1533631044', 0, 'hdumpnba8pqnikv6d5ht3hrjc6'),
(362, '管理员登录成功', 'http://192.168.1.188/phpok/admin.php?c=login&f=ok', 1533632286, 'admin', 'login', 'ok', 1, 0, '192.168.1.129', 'http://192.168.1.188/phpok/admin.php?c=login&_noCache=0.1533632272', 0, 'kipmd5fjau3thjedcdsg5i6el1'),
(363, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533632343, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?_langid=cn&_noCache=0.1533625144', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(364, '验证码填写不正确', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533632541, 'admin', 'login', 'ok', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533632343', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(365, '管理员 &lt;span class=&quot;red&quot;&gt;admin&lt;/span&gt; 成功退出', 'http://localhost/phpok/admin.php?c=logout', 1533632553, 'admin', 'logout', 'index', 0, 0, '::1', 'http://localhost/phpok/admin.php?c=index', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(366, '管理员登录成功', 'http://192.168.1.188/phpok/admin.php?c=login&f=ok', 1533632571, 'admin', 'login', 'ok', 1, 0, '192.168.1.145', 'http://192.168.1.188/phpok/admin.php?c=login&_noCache=0.1533632473', 0, 'c9g663rctm70e7svfg70incnh3'),
(367, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533632685, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533632553', 0, 'i51nk4o085v7h9qllsk2s5fj03'),
(368, '管理员账号不能为空', 'http://192.168.1.188/phpok/admin.php?c=login&f=ok', 1533632859, 'admin', 'login', 'ok', 0, 0, '192.168.1.188', 'http://192.168.1.188/phpok/admin.php?c=login&_noCache=0.1533632036', 0, 'mvqsajfm2hjkq3qbb5pekfoso6'),
(369, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533637371, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533637206', 0, 'd5k27sn27s75evnvd2t28m0qo5'),
(370, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533696225, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533696204', 0, 'tpth2qpkhehlg9o1mde0gc7oh3'),
(371, '管理员登录成功', 'http://192.168.1.188/phpok/admin.php?c=login&f=ok', 1533710166, 'admin', 'login', 'ok', 1, 0, '192.168.1.151', 'http://192.168.1.188/phpok/admin.php?c=login&_noCache=0.1533710160', 0, 'vskl42bdho12acks6fnceb9s97'),
(372, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533777773, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533777722', 0, 'f4m6ofv7tgv5h3cafpsmt6ohq5'),
(373, '管理员登录成功', 'http://localhost/phpok/admin.php?c=login&f=ok', 1533805432, 'admin', 'login', 'ok', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=login&_noCache=0.1533805426', 0, 'g7fbmpue07um2g88hopfeqqsd6'),
(374, '网站信息更新完成', 'http://localhost/phpok/admin.php?c=all&f=save&_noCache=0.1533805500', 1533805503, 'admin', 'all', 'save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=all&f=setting&_noCache=0.1533805484', 0, 'g7fbmpue07um2g88hopfeqqsd6'),
(375, '扩展全局内容设置成功', 'http://localhost/phpok/admin.php?c=all&f=ext_save', 1533805514, 'admin', 'all', 'ext_save', 1, 0, '::1', 'http://localhost/phpok/admin.php?c=all&f=set&id=4&_noCache=0.1533805484', 0, 'g7fbmpue07um2g88hopfeqqsd6');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_module`
--

CREATE TABLE `qinggan_module` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID号',
  `title` varchar(255) NOT NULL COMMENT '模块名称',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '模块排序',
  `note` varchar(255) NOT NULL COMMENT '模块说明',
  `layout` text NOT NULL COMMENT '布局',
  `mtype` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0联合模块，1独立模块'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模块管理，每创建一个模块自动创建一个表';

--
-- 转存表中的数据 `qinggan_module`
--

INSERT INTO `qinggan_module` (`id`, `title`, `status`, `taxis`, `note`, `layout`, `mtype`) VALUES
(21, '图片轮播', 1, 20, '适用于图片播放器，图片友情链接', 'sort,pic,link,target', 0),
(22, '文章资讯', 1, 10, '适用于新闻，文章之类', 'hits,dateline,sort,thumb', 0),
(23, '自定义链接', 1, 30, '适用于导航，页脚文本导航，文字友情链接', 'sort,link,target', 0),
(24, '产品', 1, 40, '适用于电子商务中产品展示模型', 'hits,dateline,sort,m_title,thumb', 0),
(40, '单页信息', 1, 60, '适用于公司简介，联系我们', 'hits,dateline,sort', 0),
(46, '留言模块', 1, 90, '', 'dateline,sort,fullname,email,content', 0),
(61, '友情链接', 1, 100, '适用于导航，页脚文本导航，文字友情链接', 'sort,link,target,tel', 0),
(64, '客服', 1, 110, '', 'sort,qq', 0),
(65, '资源下载', 1, 70, '', 'hits,dateline,sort,fsize,version,onlyuser,thumb', 0),
(66, '论坛BBS', 1, 50, '', 'hits,dateline,user_id,sort', 0),
(68, '图集相册', 1, 80, '', 'hits,dateline,sort,thumb', 0),
(69, '产品参考数据', 1, 120, '', 'hits,dateline,sort', 0),
(74, '注册审核模块', 1, 130, '用户实现会员自动审核验证', 'dateline,account', 0),
(75, '银行汇款', 1, 140, '', 'dateline,user_id,bankname,fullname,mobile,bankprice', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_opt`
--

CREATE TABLE `qinggan_opt` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `group_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '组ID',
  `parent_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `val` varchar(255) NOT NULL COMMENT '值',
  `taxis` int(10) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='表单列表选项';

--
-- 转存表中的数据 `qinggan_opt`
--

INSERT INTO `qinggan_opt` (`id`, `group_id`, `parent_id`, `title`, `val`, `taxis`) VALUES
(1, 1, 0, '未设置', '0', 30),
(2, 1, 0, '男', '1', 10),
(17, 4, 0, '是', '1', 10),
(18, 4, 0, '否', '', 20),
(21, 6, 0, '当前窗口', '_self', 10),
(22, 6, 0, '新窗口', '_blank', 20),
(23, 7, 0, '启用', '1', 10),
(24, 7, 0, '禁用', '', 20),
(25, 8, 0, 'UTF-8', 'utf8', 20),
(26, 8, 0, 'GBK', 'gbk', 10),
(62, 12, 0, '不置顶', '', 10),
(63, 12, 0, '一级置顶', '1', 20),
(64, 12, 0, '二级置顶', '2', 30),
(65, 13, 0, '三分钟', '180', 10),
(66, 14, 0, '点击推事件', 'click', 10),
(67, 14, 0, '跳转链接', 'view', 20),
(10147, 19, 0, '北京市', '北京市', 5),
(10148, 19, 10147, '朝阳区', '朝阳区', 5),
(10149, 19, 10148, '三环到四环之间', '三环到四环之间', 5),
(10150, 19, 10148, '四环到五环之间', '四环到五环之间', 10),
(10151, 19, 10148, '五环到六环之间', '五环到六环之间', 15),
(10152, 19, 10148, '管庄', '管庄', 20),
(10153, 19, 10148, '北苑', '北苑', 25),
(10154, 19, 10148, '定福庄', '定福庄', 30),
(10155, 19, 10148, '三环以内', '三环以内', 35),
(10156, 19, 10147, '海淀区', '海淀区', 10),
(10157, 19, 10156, '三环以内', '三环以内', 5),
(10158, 19, 10156, '三环到四环之间', '三环到四环之间', 10),
(10159, 19, 10156, '四环到五环之间', '四环到五环之间', 15),
(10160, 19, 10156, '五环到六环之间', '五环到六环之间', 20),
(10161, 19, 10156, '六环以外', '六环以外', 25),
(10162, 19, 10156, '西三旗', '西三旗', 30),
(10163, 19, 10156, '西二旗', '西二旗', 35),
(10164, 19, 10147, '西城区', '西城区', 15),
(10165, 19, 10164, '内环到二环里', '内环到二环里', 5),
(10166, 19, 10164, '二环到三环', '二环到三环', 10),
(10167, 19, 10147, '东城区', '东城区', 20),
(10168, 19, 10167, '内环到三环里', '内环到三环里', 5),
(10169, 19, 10147, '崇文区', '崇文区', 25),
(10170, 19, 10169, '一环到二环', '一环到二环', 5),
(10171, 19, 10169, '二环到三环', '二环到三环', 10),
(10172, 19, 10147, '宣武区', '宣武区', 30),
(10173, 19, 10172, '内环到三环里', '内环到三环里', 5),
(10174, 19, 10147, '丰台区', '丰台区', 35),
(10175, 19, 10174, '四环到五环之间', '四环到五环之间', 5),
(10176, 19, 10174, '二环到三环', '二环到三环', 10),
(10177, 19, 10174, '三环到四环之间', '三环到四环之间', 15),
(10178, 19, 10174, '五环到六环之间', '五环到六环之间', 20),
(10179, 19, 10174, '六环之外', '六环之外', 25),
(10180, 19, 10147, '石景山区', '石景山区', 40),
(10181, 19, 10180, '四环到五环内', '四环到五环内', 5),
(10182, 19, 10180, '石景山城区', '石景山城区', 10),
(10183, 19, 10180, '八大处科技园区', '八大处科技园区', 15),
(10184, 19, 10147, '门头沟', '门头沟', 45),
(10185, 19, 10184, '城区', '城区', 5),
(10186, 19, 10184, '龙泉镇', '龙泉镇', 10),
(10187, 19, 10184, '永定镇', '永定镇', 15),
(10188, 19, 10184, '大台镇', '大台镇', 20),
(10189, 19, 10184, '潭柘寺镇', '潭柘寺镇', 25),
(10190, 19, 10184, '王平镇', '王平镇', 30),
(10191, 19, 10184, '军庄镇', '军庄镇', 35),
(10192, 19, 10184, '妙峰山镇', '妙峰山镇', 40),
(10193, 19, 10184, '雁翅镇', '雁翅镇', 45),
(10194, 19, 10184, '斋堂镇', '斋堂镇', 50),
(10195, 19, 10184, '清水镇', '清水镇', 55),
(10196, 19, 10147, '房山区', '房山区', 50),
(10197, 19, 10196, '城区', '城区', 5),
(10198, 19, 10196, '大安山乡', '大安山乡', 10),
(10199, 19, 10196, '大石窝镇', '大石窝镇', 15),
(10200, 19, 10196, '窦店镇', '窦店镇', 20),
(10201, 19, 10196, '佛子庄乡', '佛子庄乡', 25),
(10202, 19, 10196, '韩村河镇', '韩村河镇', 30),
(10203, 19, 10196, '河北镇', '河北镇', 35),
(10204, 19, 10196, '良乡镇', '良乡镇', 40),
(10205, 19, 10196, '琉璃河镇', '琉璃河镇', 45),
(10206, 19, 10196, '南窖乡', '南窖乡', 50),
(10207, 19, 10196, '蒲洼乡', '蒲洼乡', 55),
(10208, 19, 10196, '青龙湖镇', '青龙湖镇', 60),
(10209, 19, 10196, '十渡镇', '十渡镇', 65),
(10210, 19, 10196, '石楼镇', '石楼镇', 70),
(10211, 19, 10196, '史家营乡', '史家营乡', 75),
(10212, 19, 10196, '霞云岭乡', '霞云岭乡', 80),
(10213, 19, 10196, '新镇', '新镇', 85),
(10214, 19, 10196, '阎村镇', '阎村镇', 90),
(10215, 19, 10196, '燕山地区', '燕山地区', 95),
(10216, 19, 10196, '张坊镇', '张坊镇', 100),
(10217, 19, 10196, '长沟镇', '长沟镇', 105),
(10218, 19, 10196, '长阳镇', '长阳镇', 110),
(10219, 19, 10196, '周口店镇', '周口店镇', 115),
(10220, 19, 10147, '通州区', '通州区', 55),
(10221, 19, 10220, '六环内（马驹桥镇）', '六环内（马驹桥镇）', 5),
(10222, 19, 10220, '中仓街道', '中仓街道', 10),
(10223, 19, 10220, '新华街道', '新华街道', 15),
(10224, 19, 10220, '玉桥街道', '玉桥街道', 20),
(10225, 19, 10220, '北苑街道', '北苑街道', 25),
(10226, 19, 10220, '六环外（马驹桥镇）', '六环外（马驹桥镇）', 30),
(10227, 19, 10220, '永顺镇', '永顺镇', 35),
(10228, 19, 10220, '梨园镇', '梨园镇', 40),
(10229, 19, 10220, '宋庄镇', '宋庄镇', 45),
(10230, 19, 10220, '漷县镇', '漷县镇', 50),
(10231, 19, 10220, '张家湾镇', '张家湾镇', 55),
(10232, 19, 10220, '西集镇', '西集镇', 60),
(10233, 19, 10220, '永乐店镇', '永乐店镇', 65),
(10234, 19, 10220, '潞城镇', '潞城镇', 70),
(10235, 19, 10220, '台湖镇', '台湖镇', 75),
(10236, 19, 10220, '于家务乡', '于家务乡', 80),
(10237, 19, 10220, '次渠镇', '次渠镇', 85),
(10238, 19, 10147, '大兴区', '大兴区', 60),
(10239, 19, 10238, '四环至五环之间', '四环至五环之间', 5),
(10240, 19, 10238, '五环至六环之间', '五环至六环之间', 10),
(10241, 19, 10238, '六环以外', '六环以外', 15),
(10242, 19, 10238, '亦庄经济开发区', '亦庄经济开发区', 20),
(10243, 19, 10147, '顺义区', '顺义区', 65),
(10244, 19, 10243, '北石槽镇', '北石槽镇', 5),
(10245, 19, 10243, '北务镇', '北务镇', 10),
(10246, 19, 10243, '北小营镇', '北小营镇', 15),
(10247, 19, 10243, '大孙各庄镇', '大孙各庄镇', 20),
(10248, 19, 10243, '高丽营镇', '高丽营镇', 25),
(10249, 19, 10243, '光明街道', '光明街道', 30),
(10250, 19, 10243, '后沙峪地区', '后沙峪地区', 35),
(10251, 19, 10243, '空港街道', '空港街道', 40),
(10252, 19, 10243, '李桥镇', '李桥镇', 45),
(10253, 19, 10243, '李遂镇', '李遂镇', 50),
(10254, 19, 10243, '龙湾屯镇', '龙湾屯镇', 55),
(10255, 19, 10243, '马坡地区', '马坡地区', 60),
(10256, 19, 10243, '木林镇', '木林镇', 65),
(10257, 19, 10243, '南彩镇', '南彩镇', 70),
(10258, 19, 10243, '南法信地区', '南法信地区', 75),
(10259, 19, 10243, '牛栏山地区', '牛栏山地区', 80),
(10260, 19, 10243, '仁和地区', '仁和地区', 85),
(10261, 19, 10243, '胜利街道', '胜利街道', 90),
(10262, 19, 10243, '石园街道', '石园街道', 95),
(10263, 19, 10243, '双丰街道', '双丰街道', 100),
(10264, 19, 10243, '天竺地区', '天竺地区', 105),
(10265, 19, 10243, '旺泉街道', '旺泉街道', 110),
(10266, 19, 10243, '杨镇地区', '杨镇地区', 115),
(10267, 19, 10243, '张镇', '张镇', 120),
(10268, 19, 10243, '赵全营镇', '赵全营镇', 125),
(10269, 19, 10147, '怀柔区', '怀柔区', 70),
(10270, 19, 10269, '城区以内', '城区以内', 5),
(10271, 19, 10269, '郊区', '郊区', 10),
(10272, 19, 10147, '密云区', '密云区', 75),
(10273, 19, 10272, '城区', '城区', 5),
(10274, 19, 10272, '城区以外', '城区以外', 10),
(10275, 19, 10147, '昌平区', '昌平区', 80),
(10276, 19, 10275, '六环以内', '六环以内', 5),
(10277, 19, 10275, '城区', '城区', 10),
(10278, 19, 10275, '城区以外', '城区以外', 15),
(10279, 19, 10147, '平谷区', '平谷区', 85),
(10280, 19, 10279, '城区', '城区', 5),
(10281, 19, 10279, '城区以外', '城区以外', 10),
(10282, 19, 10147, '延庆县', '延庆县', 90),
(10283, 19, 10282, '延庆镇', '延庆镇', 5),
(10284, 19, 10282, '城区', '城区', 10),
(10285, 19, 10282, '康庄镇', '康庄镇', 15),
(10286, 19, 10282, '八达岭镇', '八达岭镇', 20),
(10287, 19, 10282, '永宁镇', '永宁镇', 25),
(10288, 19, 10282, '旧县镇', '旧县镇', 30),
(10289, 19, 10282, '张山营镇', '张山营镇', 35),
(10290, 19, 10282, '四海镇', '四海镇', 40),
(10291, 19, 10282, '千家店镇', '千家店镇', 45),
(10292, 19, 10282, '沈家营镇', '沈家营镇', 50),
(10293, 19, 10282, '大榆树镇', '大榆树镇', 55),
(10294, 19, 10282, '井庄镇', '井庄镇', 60),
(10295, 19, 10282, '大庄科乡', '大庄科乡', 65),
(10296, 19, 10282, '刘斌堡乡', '刘斌堡乡', 70),
(10297, 19, 10282, '香营乡', '香营乡', 75),
(10298, 19, 10282, '珍珠泉乡', '珍珠泉乡', 80),
(10299, 19, 0, '上海市', '上海市', 10),
(10300, 19, 10299, '黄浦区', '黄浦区', 5),
(10301, 19, 10299, '徐汇区', '徐汇区', 10),
(10302, 19, 10299, '长宁区', '长宁区', 15),
(10303, 19, 10299, '静安区', '静安区', 20),
(10304, 19, 10299, '闸北区', '闸北区', 25),
(10305, 19, 10299, '虹口区', '虹口区', 30),
(10306, 19, 10299, '杨浦区', '杨浦区', 35),
(10307, 19, 10299, '宝山区', '宝山区', 40),
(10308, 19, 10307, '罗店镇', '罗店镇', 5),
(10309, 19, 10307, '城区', '城区', 10),
(10310, 19, 10307, '大场镇', '大场镇', 15),
(10311, 19, 10307, '杨行镇', '杨行镇', 20),
(10312, 19, 10307, '月浦镇', '月浦镇', 25),
(10313, 19, 10307, '罗泾镇', '罗泾镇', 30),
(10314, 19, 10307, '顾村镇', '顾村镇', 35),
(10315, 19, 10307, '高境镇', '高境镇', 40),
(10316, 19, 10307, '庙行镇', '庙行镇', 45),
(10317, 19, 10307, '淞南镇', '淞南镇', 50),
(10318, 19, 10307, '宝山城市工业园区', '宝山城市工业园区', 55),
(10319, 19, 10299, '闵行区', '闵行区', 45),
(10320, 19, 10319, '城区', '城区', 5),
(10321, 19, 10319, '莘庄镇', '莘庄镇', 10),
(10322, 19, 10319, '七宝镇', '七宝镇', 15),
(10323, 19, 10319, '浦江镇', '浦江镇', 20),
(10324, 19, 10319, '梅陇镇', '梅陇镇', 25),
(10325, 19, 10319, '虹桥镇', '虹桥镇', 30),
(10326, 19, 10319, '马桥镇', '马桥镇', 35),
(10327, 19, 10319, '吴泾镇', '吴泾镇', 40),
(10328, 19, 10319, '华漕镇', '华漕镇', 45),
(10329, 19, 10319, '颛桥镇', '颛桥镇', 50),
(10330, 19, 10299, '嘉定区', '嘉定区', 50),
(10331, 19, 10330, '城区', '城区', 5),
(10332, 19, 10330, '南翔镇', '南翔镇', 10),
(10333, 19, 10330, '马陆镇', '马陆镇', 15),
(10334, 19, 10330, '华亭镇', '华亭镇', 20),
(10335, 19, 10330, '江桥镇', '江桥镇', 25),
(10336, 19, 10330, '菊园新区', '菊园新区', 30),
(10337, 19, 10330, '安亭镇', '安亭镇', 35),
(10338, 19, 10330, '徐行镇', '徐行镇', 40),
(10339, 19, 10330, '外冈镇', '外冈镇', 45),
(10340, 19, 10330, '嘉定工业区', '嘉定工业区', 50),
(10341, 19, 10299, '浦东新区', '浦东新区', 55),
(10342, 19, 10341, '城区', '城区', 5),
(10343, 19, 10341, '川沙新镇', '川沙新镇', 10),
(10344, 19, 10341, '祝桥镇', '祝桥镇', 15),
(10345, 19, 10341, '新场镇', '新场镇', 20),
(10346, 19, 10341, '高桥镇', '高桥镇', 25),
(10347, 19, 10341, '惠南镇', '惠南镇', 30),
(10348, 19, 10341, '北蔡镇', '北蔡镇', 35),
(10349, 19, 10341, '合庆镇', '合庆镇', 40),
(10350, 19, 10341, '唐镇', '唐镇', 45),
(10351, 19, 10341, '曹路镇', '曹路镇', 50),
(10352, 19, 10341, '金桥镇', '金桥镇', 55),
(10353, 19, 10341, '高行镇', '高行镇', 60),
(10354, 19, 10341, '高东镇', '高东镇', 65),
(10355, 19, 10341, '张江镇', '张江镇', 70),
(10356, 19, 10341, '三林镇', '三林镇', 75),
(10357, 19, 10341, '南汇新城镇', '南汇新城镇', 80),
(10358, 19, 10341, '康桥镇', '康桥镇', 85),
(10359, 19, 10341, '宣桥镇', '宣桥镇', 90),
(10360, 19, 10341, '书院镇', '书院镇', 95),
(10361, 19, 10341, '大团镇', '大团镇', 100),
(10362, 19, 10341, '周浦镇', '周浦镇', 105),
(10363, 19, 10341, '芦潮港镇', '芦潮港镇', 110),
(10364, 19, 10341, '泥城镇', '泥城镇', 115),
(10365, 19, 10341, '航头镇', '航头镇', 120),
(10366, 19, 10341, '万祥镇', '万祥镇', 125),
(10367, 19, 10341, '老港镇', '老港镇', 130),
(10368, 19, 10299, '青浦区', '青浦区', 60),
(10369, 19, 10368, '城区', '城区', 5),
(10370, 19, 10368, '赵巷镇', '赵巷镇', 10),
(10371, 19, 10368, '徐泾镇', '徐泾镇', 15),
(10372, 19, 10368, '华新镇', '华新镇', 20),
(10373, 19, 10368, '重固镇', '重固镇', 25),
(10374, 19, 10368, '白鹤镇', '白鹤镇', 30),
(10375, 19, 10368, '练塘镇', '练塘镇', 35),
(10376, 19, 10368, '金泽镇', '金泽镇', 40),
(10377, 19, 10368, '朱家角镇', '朱家角镇', 45),
(10378, 19, 10299, '松江区', '松江区', 65),
(10379, 19, 10378, '城区', '城区', 5),
(10380, 19, 10378, '泗泾镇', '泗泾镇', 10),
(10381, 19, 10378, '佘山镇', '佘山镇', 15),
(10382, 19, 10378, '车墩镇', '车墩镇', 20),
(10383, 19, 10378, '新桥镇', '新桥镇', 25),
(10384, 19, 10378, '洞泾镇', '洞泾镇', 30),
(10385, 19, 10378, '九亭镇', '九亭镇', 35),
(10386, 19, 10378, '泖港镇', '泖港镇', 40),
(10387, 19, 10378, '石湖荡镇', '石湖荡镇', 45),
(10388, 19, 10378, '新浜镇', '新浜镇', 50),
(10389, 19, 10378, '叶榭镇', '叶榭镇', 55),
(10390, 19, 10378, '小昆山镇', '小昆山镇', 60),
(10391, 19, 10299, '金山区', '金山区', 70),
(10392, 19, 10391, '城区', '城区', 5),
(10393, 19, 10391, '金山工业区', '金山工业区', 10),
(10394, 19, 10391, '朱泾镇', '朱泾镇', 15),
(10395, 19, 10391, '枫泾镇', '枫泾镇', 20),
(10396, 19, 10391, '张堰镇', '张堰镇', 25),
(10397, 19, 10391, '亭林镇', '亭林镇', 30),
(10398, 19, 10391, '吕巷镇', '吕巷镇', 35),
(10399, 19, 10391, '廊下镇', '廊下镇', 40),
(10400, 19, 10391, '金山卫镇', '金山卫镇', 45),
(10401, 19, 10391, '漕泾镇', '漕泾镇', 50),
(10402, 19, 10391, '山阳镇', '山阳镇', 55),
(10403, 19, 10299, '奉贤区', '奉贤区', 75),
(10404, 19, 10403, '南桥镇', '南桥镇', 5),
(10405, 19, 10403, '奉城镇', '奉城镇', 10),
(10406, 19, 10403, '四团镇', '四团镇', 15),
(10407, 19, 10403, '柘林镇', '柘林镇', 20),
(10408, 19, 10403, '庄行镇', '庄行镇', 25),
(10409, 19, 10403, '金汇镇', '金汇镇', 30),
(10410, 19, 10403, '青村镇', '青村镇', 35),
(10411, 19, 10403, '海湾镇', '海湾镇', 40),
(10412, 19, 10299, '普陀区', '普陀区', 80),
(10413, 19, 10299, '崇明县', '崇明县', 85),
(10414, 19, 10413, '堡镇', '堡镇', 5),
(10415, 19, 10413, '庙镇', '庙镇', 10),
(10416, 19, 10413, '陈家镇', '陈家镇', 15),
(10417, 19, 10413, '城桥镇', '城桥镇', 20),
(10418, 19, 10413, '东平镇', '东平镇', 25),
(10419, 19, 10413, '港西镇', '港西镇', 30),
(10420, 19, 10413, '港沿镇', '港沿镇', 35),
(10421, 19, 10413, '建设镇', '建设镇', 40),
(10422, 19, 10413, '绿华镇', '绿华镇', 45),
(10423, 19, 10413, '三星镇', '三星镇', 50),
(10424, 19, 10413, '竖新镇', '竖新镇', 55),
(10425, 19, 10413, '向化镇', '向化镇', 60),
(10426, 19, 10413, '新海镇', '新海镇', 65),
(10427, 19, 10413, '新河镇', '新河镇', 70),
(10428, 19, 10413, '中兴镇', '中兴镇', 75),
(10429, 19, 10413, '长兴乡', '长兴乡', 80),
(10430, 19, 10413, '横沙乡', '横沙乡', 85),
(10431, 19, 10413, '新村乡', '新村乡', 90),
(10432, 19, 0, '天津市', '天津市', 15),
(10433, 19, 10432, '东丽区', '东丽区', 5),
(10434, 19, 10432, '和平区', '和平区', 10),
(10435, 19, 10432, '河北区', '河北区', 15),
(10436, 19, 10432, '河东区', '河东区', 20),
(10437, 19, 10432, '河西区', '河西区', 25),
(10438, 19, 10432, '红桥区', '红桥区', 30),
(10439, 19, 10432, '蓟县', '蓟县', 35),
(10440, 19, 10432, '静海县', '静海县', 40),
(10441, 19, 10432, '南开区', '南开区', 45),
(10442, 19, 10432, '塘沽区', '塘沽区', 50),
(10443, 19, 10432, '西青区', '西青区', 55),
(10444, 19, 10443, '杨柳青,中北,精武,大寺镇,环外海泰及外环内', '杨柳青,中北,精武,大寺镇,环外海泰及外环内', 5),
(10445, 19, 10443, '其它地区', '其它地区', 10),
(10446, 19, 10432, '武清区', '武清区', 60),
(10447, 19, 10446, '杨村镇、下朱庄内', '杨村镇、下朱庄内', 5),
(10448, 19, 10446, '其它地区', '其它地区', 10),
(10449, 19, 10432, '津南区', '津南区', 65),
(10450, 19, 10449, '双港，辛庄', '双港，辛庄', 5),
(10451, 19, 10449, '咸水沽镇、海河教育园，海河科技园', '咸水沽镇、海河教育园，海河科技园', 10),
(10452, 19, 10449, '其他地区', '其他地区', 15),
(10453, 19, 10432, '汉沽区', '汉沽区', 70),
(10454, 19, 10453, '汉沽区街里、汉沽开发区', '汉沽区街里、汉沽开发区', 5),
(10455, 19, 10453, '其它地区', '其它地区', 10),
(10456, 19, 10432, '大港区', '大港区', 75),
(10457, 19, 10456, '大港油田', '大港油田', 5),
(10458, 19, 10456, '主城区内', '主城区内', 10),
(10459, 19, 10456, '主城区外', '主城区外', 15),
(10460, 19, 10432, '北辰区', '北辰区', 80),
(10461, 19, 10460, '外环外双街镇，河北工大新校，屈店工业园', '外环外双街镇，河北工大新校，屈店工业园', 5),
(10462, 19, 10460, '外环内', '外环内', 10),
(10463, 19, 10460, '外环外其它地区', '外环外其它地区', 15),
(10464, 19, 10432, '宝坻区', '宝坻区', 85),
(10465, 19, 10464, '城关镇、马家店开发区、天宝工业园', '城关镇、马家店开发区、天宝工业园', 5),
(10466, 19, 10464, '其它地区', '其它地区', 10),
(10467, 19, 10432, '宁河县', '宁河县', 90),
(10468, 19, 10467, '芦台镇、经济开发区、贸易开发区', '芦台镇、经济开发区、贸易开发区', 5),
(10469, 19, 10467, '其它地区', '其它地区', 10),
(10470, 19, 0, '重庆市', '重庆市', 20),
(10471, 19, 10470, '万州区', '万州区', 5),
(10472, 19, 10471, '城区', '城区', 5),
(10473, 19, 10471, '白土镇', '白土镇', 10),
(10474, 19, 10471, '白羊镇', '白羊镇', 15),
(10475, 19, 10471, '大周镇', '大周镇', 20),
(10476, 19, 10471, '弹子镇', '弹子镇', 25),
(10477, 19, 10471, '分水镇', '分水镇', 30),
(10478, 19, 10471, '甘宁镇', '甘宁镇', 35),
(10479, 19, 10471, '高峰镇', '高峰镇', 40),
(10480, 19, 10471, '高梁镇', '高梁镇', 45),
(10481, 19, 10471, '后山镇', '后山镇', 50),
(10482, 19, 10471, '李河镇', '李河镇', 55),
(10483, 19, 10471, '龙驹镇', '龙驹镇', 60),
(10484, 19, 10471, '龙沙镇', '龙沙镇', 65),
(10485, 19, 10471, '罗田镇', '罗田镇', 70),
(10486, 19, 10471, '孙家镇', '孙家镇', 75),
(10487, 19, 10471, '太安镇', '太安镇', 80),
(10488, 19, 10471, '太龙镇', '太龙镇', 85),
(10489, 19, 10471, '天城镇', '天城镇', 90),
(10490, 19, 10471, '武陵镇', '武陵镇', 95),
(10491, 19, 10471, '响水镇', '响水镇', 100),
(10492, 19, 10471, '小周镇', '小周镇', 105),
(10493, 19, 10471, '新田镇', '新田镇', 110),
(10494, 19, 10471, '新乡镇', '新乡镇', 115),
(10495, 19, 10471, '熊家镇', '熊家镇', 120),
(10496, 19, 10471, '余家镇', '余家镇', 125),
(10497, 19, 10471, '长岭镇', '长岭镇', 130),
(10498, 19, 10471, '长坪镇', '长坪镇', 135),
(10499, 19, 10471, '长滩镇', '长滩镇', 140),
(10500, 19, 10471, '走马镇', '走马镇', 145),
(10501, 19, 10471, '瀼渡镇', '瀼渡镇', 150),
(10502, 19, 10471, '茨竹乡', '茨竹乡', 155),
(10503, 19, 10471, '柱山乡', '柱山乡', 160),
(10504, 19, 10471, '燕山乡', '燕山乡', 165),
(10505, 19, 10471, '溪口乡', '溪口乡', 170),
(10506, 19, 10471, '普子乡', '普子乡', 175),
(10507, 19, 10471, '地宝乡', '地宝乡', 180),
(10508, 19, 10471, '铁峰乡', '铁峰乡', 185),
(10509, 19, 10471, '黄柏乡', '黄柏乡', 190),
(10510, 19, 10471, '九池乡', '九池乡', 195),
(10511, 19, 10471, '梨树乡', '梨树乡', 200),
(10512, 19, 10471, '郭村乡', '郭村乡', 205),
(10513, 19, 10471, '恒合乡', '恒合乡', 210),
(10514, 19, 10470, '涪陵区', '涪陵区', 10),
(10515, 19, 10514, '城区', '城区', 5),
(10516, 19, 10514, '李渡镇', '李渡镇', 10),
(10517, 19, 10514, '白涛镇', '白涛镇', 15),
(10518, 19, 10514, '百胜镇', '百胜镇', 20),
(10519, 19, 10514, '堡子镇', '堡子镇', 25),
(10520, 19, 10514, '焦石镇', '焦石镇', 30),
(10521, 19, 10514, '蔺市镇', '蔺市镇', 35),
(10522, 19, 10514, '龙桥镇', '龙桥镇', 40),
(10523, 19, 10514, '龙潭镇', '龙潭镇', 45),
(10524, 19, 10514, '马武镇', '马武镇', 50),
(10525, 19, 10514, '南沱镇', '南沱镇', 55),
(10526, 19, 10514, '青羊镇', '青羊镇', 60),
(10527, 19, 10514, '清溪镇', '清溪镇', 65),
(10528, 19, 10514, '石沱镇', '石沱镇', 70),
(10529, 19, 10514, '新妙镇', '新妙镇', 75),
(10530, 19, 10514, '义和镇', '义和镇', 80),
(10531, 19, 10514, '增福乡', '增福乡', 85),
(10532, 19, 10514, '珍溪镇', '珍溪镇', 90),
(10533, 19, 10514, '镇安镇', '镇安镇', 95),
(10534, 19, 10514, '致韩镇', '致韩镇', 100),
(10535, 19, 10514, '土地坡乡', '土地坡乡', 105),
(10536, 19, 10514, '武陵山乡', '武陵山乡', 110),
(10537, 19, 10514, '中峰乡', '中峰乡', 115),
(10538, 19, 10514, '梓里乡', '梓里乡', 120),
(10539, 19, 10514, '丛林乡', '丛林乡', 125),
(10540, 19, 10514, '大木乡', '大木乡', 130),
(10541, 19, 10514, '惠民乡', '惠民乡', 135),
(10542, 19, 10514, '酒店乡', '酒店乡', 140),
(10543, 19, 10514, '聚宝乡', '聚宝乡', 145),
(10544, 19, 10514, '卷洞乡', '卷洞乡', 150),
(10545, 19, 10514, '两汇乡', '两汇乡', 155),
(10546, 19, 10514, '罗云乡', '罗云乡', 160),
(10547, 19, 10514, '明家乡', '明家乡', 165),
(10548, 19, 10514, '仁义乡', '仁义乡', 170),
(10549, 19, 10514, '山窝乡', '山窝乡', 175),
(10550, 19, 10514, '石和乡', '石和乡', 180),
(10551, 19, 10514, '石龙乡', '石龙乡', 185),
(10552, 19, 10514, '太和乡', '太和乡', 190),
(10553, 19, 10514, '天台乡', '天台乡', 195),
(10554, 19, 10514, '同乐乡', '同乐乡', 200),
(10555, 19, 10514, '新村乡', '新村乡', 205),
(10556, 19, 10470, '梁平县', '梁平县', 15),
(10557, 19, 10556, '县城内', '县城内', 5),
(10558, 19, 10556, '梁山镇', '梁山镇', 10),
(10559, 19, 10556, '柏家镇', '柏家镇', 15),
(10560, 19, 10556, '碧山镇', '碧山镇', 20),
(10561, 19, 10556, '大观镇', '大观镇', 25),
(10562, 19, 10556, '福禄镇', '福禄镇', 30),
(10563, 19, 10556, '合兴镇', '合兴镇', 35),
(10564, 19, 10556, '和林镇', '和林镇', 40),
(10565, 19, 10556, '虎城镇', '虎城镇', 45),
(10566, 19, 10556, '回龙镇', '回龙镇', 50),
(10567, 19, 10556, '金带镇', '金带镇', 55),
(10568, 19, 10556, '聚奎镇', '聚奎镇', 60),
(10569, 19, 10556, '礼让镇', '礼让镇', 65),
(10570, 19, 10556, '龙门镇', '龙门镇', 70),
(10571, 19, 10556, '明达镇', '明达镇', 75),
(10572, 19, 10556, '蟠龙镇', '蟠龙镇', 80),
(10573, 19, 10556, '屏锦镇', '屏锦镇', 85),
(10574, 19, 10556, '仁贤镇', '仁贤镇', 90),
(10575, 19, 10556, '石安镇', '石安镇', 95),
(10576, 19, 10556, '文化镇', '文化镇', 100),
(10577, 19, 10556, '新盛镇', '新盛镇', 105),
(10578, 19, 10556, '荫平镇', '荫平镇', 110),
(10579, 19, 10556, '袁驿镇', '袁驿镇', 115),
(10580, 19, 10556, '云龙镇', '云龙镇', 120),
(10581, 19, 10556, '竹山镇', '竹山镇', 125),
(10582, 19, 10556, '安胜乡', '安胜乡', 130),
(10583, 19, 10556, '铁门乡', '铁门乡', 135),
(10584, 19, 10556, '紫照乡', '紫照乡', 140),
(10585, 19, 10556, '曲水乡', '曲水乡', 145),
(10586, 19, 10556, '龙胜乡', '龙胜乡', 150),
(10587, 19, 10556, '城北乡', '城北乡', 155),
(10588, 19, 10556, '城东乡', '城东乡', 160),
(10589, 19, 10556, '复平乡', '复平乡', 165),
(10590, 19, 10470, '南川区', '南川区', 20),
(10591, 19, 10590, '城区', '城区', 5),
(10592, 19, 10590, '头渡镇', '头渡镇', 10),
(10593, 19, 10590, '兴隆镇', '兴隆镇', 15),
(10594, 19, 10590, '冷水关乡', '冷水关乡', 20),
(10595, 19, 10590, '德隆乡', '德隆乡', 25),
(10596, 19, 10590, '峰岩乡', '峰岩乡', 30),
(10597, 19, 10590, '福寿乡', '福寿乡', 35),
(10598, 19, 10590, '古花乡', '古花乡', 40),
(10599, 19, 10590, '河图乡', '河图乡', 45),
(10600, 19, 10590, '民主乡', '民主乡', 50),
(10601, 19, 10590, '木凉乡', '木凉乡', 55),
(10602, 19, 10590, '乾丰乡', '乾丰乡', 60),
(10603, 19, 10590, '庆元乡', '庆元乡', 65),
(10604, 19, 10590, '石莲乡', '石莲乡', 70),
(10605, 19, 10590, '石溪乡', '石溪乡', 75),
(10606, 19, 10590, '铁村乡', '铁村乡', 80),
(10607, 19, 10590, '土溪乡', '土溪乡', 85),
(10608, 19, 10590, '鱼泉乡', '鱼泉乡', 90),
(10609, 19, 10590, '中桥乡', '中桥乡', 95),
(10610, 19, 10590, '太平场镇', '太平场镇', 100),
(10611, 19, 10590, '大观镇', '大观镇', 105),
(10612, 19, 10590, '大有镇', '大有镇', 110),
(10613, 19, 10590, '合溪镇', '合溪镇', 115),
(10614, 19, 10590, '金山镇', '金山镇', 120),
(10615, 19, 10590, '鸣玉镇', '鸣玉镇', 125),
(10616, 19, 10590, '南平镇', '南平镇', 130),
(10617, 19, 10590, '三泉镇', '三泉镇', 135),
(10618, 19, 10590, '神童镇', '神童镇', 140),
(10619, 19, 10590, '石墙镇', '石墙镇', 145),
(10620, 19, 10590, '水江镇', '水江镇', 150),
(10621, 19, 10470, '潼南县', '潼南县', 25),
(10622, 19, 10621, '县城内', '县城内', 5),
(10623, 19, 10621, '柏梓镇', '柏梓镇', 10),
(10624, 19, 10621, '宝龙镇', '宝龙镇', 15),
(10625, 19, 10621, '崇龛镇', '崇龛镇', 20),
(10626, 19, 10621, '古溪镇', '古溪镇', 25),
(10627, 19, 10621, '龙形镇', '龙形镇', 30),
(10628, 19, 10621, '米心镇', '米心镇', 35),
(10629, 19, 10621, '群力镇', '群力镇', 40),
(10630, 19, 10621, '上和镇', '上和镇', 45),
(10631, 19, 10621, '双江镇', '双江镇', 50),
(10632, 19, 10621, '太安镇', '太安镇', 55),
(10633, 19, 10621, '塘坝镇', '塘坝镇', 60),
(10634, 19, 10621, '卧佛镇', '卧佛镇', 65),
(10635, 19, 10621, '五桂镇', '五桂镇', 70),
(10636, 19, 10621, '小渡镇', '小渡镇', 75),
(10637, 19, 10621, '新胜镇', '新胜镇', 80),
(10638, 19, 10621, '玉溪镇', '玉溪镇', 85),
(10639, 19, 10621, '别口乡', '别口乡', 90),
(10640, 19, 10621, '田家乡', '田家乡', 95),
(10641, 19, 10621, '寿桥乡', '寿桥乡', 100),
(10642, 19, 10470, '大足区', '大足区', 30),
(10643, 19, 10642, '城区', '城区', 5),
(10644, 19, 10642, '龙滩子镇', '龙滩子镇', 10),
(10645, 19, 10642, '龙水镇', '龙水镇', 15),
(10646, 19, 10642, '智凤镇', '智凤镇', 20),
(10647, 19, 10642, '宝顶镇', '宝顶镇', 25),
(10648, 19, 10642, '中敖镇', '中敖镇', 30),
(10649, 19, 10642, '三驱镇', '三驱镇', 35),
(10650, 19, 10642, '宝兴镇', '宝兴镇', 40),
(10651, 19, 10642, '玉龙镇', '玉龙镇', 45),
(10652, 19, 10642, '石马镇', '石马镇', 50),
(10653, 19, 10642, '拾万镇', '拾万镇', 55),
(10654, 19, 10642, '回龙镇', '回龙镇', 60),
(10655, 19, 10642, '金山镇', '金山镇', 65),
(10656, 19, 10642, '万古镇', '万古镇', 70),
(10657, 19, 10642, '国梁镇', '国梁镇', 75),
(10658, 19, 10642, '雍溪镇', '雍溪镇', 80),
(10659, 19, 10642, '珠溪镇', '珠溪镇', 85),
(10660, 19, 10642, '龙石镇', '龙石镇', 90),
(10661, 19, 10642, '邮亭镇', '邮亭镇', 95),
(10662, 19, 10642, '铁山镇', '铁山镇', 100),
(10663, 19, 10642, '高升镇', '高升镇', 105),
(10664, 19, 10642, '季家镇', '季家镇', 110),
(10665, 19, 10642, '古龙镇', '古龙镇', 115),
(10666, 19, 10642, '高坪镇', '高坪镇', 120),
(10667, 19, 10642, '双路镇', '双路镇', 125),
(10668, 19, 10642, '通桥镇', '通桥镇', 130),
(10669, 19, 10470, '黔江区', '黔江区', 35),
(10670, 19, 10669, '城区', '城区', 5),
(10671, 19, 10669, '正阳镇', '正阳镇', 10),
(10672, 19, 10669, '舟白镇', '舟白镇', 15),
(10673, 19, 10669, '阿蓬江镇', '阿蓬江镇', 20),
(10674, 19, 10669, '小南海镇', '小南海镇', 25),
(10675, 19, 10669, '鹅池镇', '鹅池镇', 30),
(10676, 19, 10669, '冯家镇', '冯家镇', 35),
(10677, 19, 10669, '黑溪镇', '黑溪镇', 40),
(10678, 19, 10669, '黄溪镇', '黄溪镇', 45),
(10679, 19, 10669, '金溪镇', '金溪镇', 50),
(10680, 19, 10669, '黎水镇', '黎水镇', 55),
(10681, 19, 10669, '邻鄂镇', '邻鄂镇', 60),
(10682, 19, 10669, '马喇镇', '马喇镇', 65),
(10683, 19, 10669, '石会镇', '石会镇', 70),
(10684, 19, 10669, '石家镇', '石家镇', 75),
(10685, 19, 10669, '濯水镇', '濯水镇', 80),
(10686, 19, 10669, '白石乡', '白石乡', 85),
(10687, 19, 10669, '白土乡', '白土乡', 90),
(10688, 19, 10669, '金洞乡', '金洞乡', 95),
(10689, 19, 10669, '蓬东乡', '蓬东乡', 100),
(10690, 19, 10669, '沙坝乡', '沙坝乡', 105),
(10691, 19, 10669, '杉岭乡', '杉岭乡', 110),
(10692, 19, 10669, '水市乡', '水市乡', 115),
(10693, 19, 10669, '水田乡', '水田乡', 120),
(10694, 19, 10669, '太极乡', '太极乡', 125),
(10695, 19, 10669, '五里乡', '五里乡', 130),
(10696, 19, 10669, '新华乡', '新华乡', 135),
(10697, 19, 10669, '中塘乡', '中塘乡', 140),
(10698, 19, 10470, '武隆县', '武隆县', 40),
(10699, 19, 10698, '县城内', '县城内', 5),
(10700, 19, 10698, '仙女山镇', '仙女山镇', 10),
(10701, 19, 10698, '巷口镇', '巷口镇', 15),
(10702, 19, 10698, '白马镇', '白马镇', 20),
(10703, 19, 10698, '火炉镇', '火炉镇', 25),
(10704, 19, 10698, '江口镇', '江口镇', 30),
(10705, 19, 10698, '平桥镇', '平桥镇', 35),
(10706, 19, 10698, '桐梓镇', '桐梓镇', 40),
(10707, 19, 10698, '土坎镇', '土坎镇', 45),
(10708, 19, 10698, '鸭江镇', '鸭江镇', 50),
(10709, 19, 10698, '羊角镇', '羊角镇', 55),
(10710, 19, 10698, '长坝镇', '长坝镇', 60),
(10711, 19, 10698, '白云乡', '白云乡', 65),
(10712, 19, 10698, '沧沟乡', '沧沟乡', 70),
(10713, 19, 10698, '凤来乡', '凤来乡', 75),
(10714, 19, 10698, '浩口乡', '浩口乡', 80),
(10715, 19, 10698, '和顺乡', '和顺乡', 85),
(10716, 19, 10698, '后坪乡', '后坪乡', 90),
(10717, 19, 10698, '黄莺乡', '黄莺乡', 95),
(10718, 19, 10698, '接龙乡', '接龙乡', 100),
(10719, 19, 10698, '庙垭乡', '庙垭乡', 105),
(10720, 19, 10698, '石桥乡', '石桥乡', 110),
(10721, 19, 10698, '双河乡', '双河乡', 115),
(10722, 19, 10698, '铁矿乡', '铁矿乡', 120),
(10723, 19, 10698, '土地乡', '土地乡', 125),
(10724, 19, 10698, '文复乡', '文复乡', 130),
(10725, 19, 10698, '赵家乡', '赵家乡', 135),
(10726, 19, 10470, '丰都县', '丰都县', 45),
(10727, 19, 10726, '县城内', '县城内', 5),
(10728, 19, 10726, '南天湖镇', '南天湖镇', 10),
(10729, 19, 10726, '许明寺镇', '许明寺镇', 15),
(10730, 19, 10726, '包鸾镇', '包鸾镇', 20),
(10731, 19, 10726, '董家镇', '董家镇', 25),
(10732, 19, 10726, '高家镇', '高家镇', 30),
(10733, 19, 10726, '虎威镇', '虎威镇', 35),
(10734, 19, 10726, '江池镇', '江池镇', 40),
(10735, 19, 10726, '龙河镇', '龙河镇', 45),
(10736, 19, 10726, '名山镇', '名山镇', 50),
(10737, 19, 10726, '三元镇', '三元镇', 55),
(10738, 19, 10726, '社坛镇', '社坛镇', 60),
(10739, 19, 10726, '十直镇', '十直镇', 65),
(10740, 19, 10726, '树人镇', '树人镇', 70),
(10741, 19, 10726, '双路镇', '双路镇', 75),
(10742, 19, 10726, '武平镇', '武平镇', 80),
(10743, 19, 10726, '兴义镇', '兴义镇', 85),
(10744, 19, 10726, '湛普镇', '湛普镇', 90),
(10745, 19, 10726, '镇江镇', '镇江镇', 95),
(10746, 19, 10726, '太平坝乡', '太平坝乡', 100),
(10747, 19, 10726, '双龙场乡', '双龙场乡', 105),
(10748, 19, 10726, '保合乡', '保合乡', 110),
(10749, 19, 10726, '崇兴乡', '崇兴乡', 115),
(10750, 19, 10726, '都督乡', '都督乡', 120),
(10751, 19, 10726, '暨龙乡', '暨龙乡', 125),
(10752, 19, 10726, '栗子乡', '栗子乡', 130),
(10753, 19, 10726, '龙孔乡', '龙孔乡', 135),
(10754, 19, 10726, '青龙乡', '青龙乡', 140),
(10755, 19, 10726, '仁沙乡', '仁沙乡', 145),
(10756, 19, 10726, '三坝乡', '三坝乡', 150),
(10757, 19, 10726, '三建乡', '三建乡', 155),
(10758, 19, 10470, '奉节县', '奉节县', 50),
(10759, 19, 10758, '永乐镇', '永乐镇', 5),
(10760, 19, 10758, '县城内', '县城内', 10),
(10761, 19, 10758, '永安镇', '永安镇', 15),
(10762, 19, 10758, '白帝镇', '白帝镇', 20),
(10763, 19, 10758, '草堂镇', '草堂镇', 25),
(10764, 19, 10758, '大树镇', '大树镇', 30),
(10765, 19, 10758, '汾河镇', '汾河镇', 35),
(10766, 19, 10758, '公平镇', '公平镇', 40),
(10767, 19, 10758, '甲高镇', '甲高镇', 45),
(10768, 19, 10758, '康乐镇', '康乐镇', 50),
(10769, 19, 10758, '青龙镇', '青龙镇', 55),
(10770, 19, 10758, '吐祥镇', '吐祥镇', 60),
(10771, 19, 10758, '新民镇', '新民镇', 65),
(10772, 19, 10758, '兴隆镇', '兴隆镇', 70),
(10773, 19, 10758, '羊市镇', '羊市镇', 75),
(10774, 19, 10758, '朱衣镇', '朱衣镇', 80),
(10775, 19, 10758, '竹园镇', '竹园镇', 85),
(10776, 19, 10758, '安坪乡', '安坪乡', 90),
(10777, 19, 10758, '冯坪乡', '冯坪乡', 95),
(10778, 19, 10758, '鹤峰乡', '鹤峰乡', 100),
(10779, 19, 10758, '红土乡', '红土乡', 105),
(10780, 19, 10758, '康坪乡', '康坪乡', 110),
(10781, 19, 10758, '龙桥乡', '龙桥乡', 115),
(10782, 19, 10758, '平安乡', '平安乡', 120),
(10783, 19, 10758, '石岗乡', '石岗乡', 125),
(10784, 19, 10758, '太和乡', '太和乡', 130),
(10785, 19, 10758, '五马乡', '五马乡', 135),
(10786, 19, 10758, '新政乡', '新政乡', 140),
(10787, 19, 10758, '岩湾乡', '岩湾乡', 145),
(10788, 19, 10758, '云雾乡', '云雾乡', 150),
(10789, 19, 10758, '长安乡', '长安乡', 155),
(10790, 19, 10470, '开县', '开县', 55),
(10791, 19, 10790, '白桥镇', '白桥镇', 5),
(10792, 19, 10790, '大德镇', '大德镇', 10),
(10793, 19, 10790, '金峰镇', '金峰镇', 15),
(10794, 19, 10790, '谭家镇', '谭家镇', 20),
(10795, 19, 10790, '天和镇', '天和镇', 25),
(10796, 19, 10790, '白泉乡', '白泉乡', 30),
(10797, 19, 10790, '县城内', '县城内', 35),
(10798, 19, 10790, '九龙山镇', '九龙山镇', 40),
(10799, 19, 10790, '大进镇', '大进镇', 45),
(10800, 19, 10790, '敦好镇', '敦好镇', 50),
(10801, 19, 10790, '高桥镇', '高桥镇', 55),
(10802, 19, 10790, '郭家镇', '郭家镇', 60),
(10803, 19, 10790, '和谦镇', '和谦镇', 65),
(10804, 19, 10790, '河堰镇', '河堰镇', 70),
(10805, 19, 10790, '厚坝镇', '厚坝镇', 75),
(10806, 19, 10790, '临江镇', '临江镇', 80),
(10807, 19, 10790, '南门镇', '南门镇', 85),
(10808, 19, 10790, '南雅镇', '南雅镇', 90),
(10809, 19, 10790, '渠口镇', '渠口镇', 95),
(10810, 19, 10790, '铁桥镇', '铁桥镇', 100),
(10811, 19, 10790, '岳溪镇', '岳溪镇', 105),
(10812, 19, 10790, '温泉镇', '温泉镇', 110),
(10813, 19, 10790, '义和镇', '义和镇', 115),
(10814, 19, 10790, '长沙镇', '长沙镇', 120),
(10815, 19, 10790, '赵家镇', '赵家镇', 125),
(10816, 19, 10790, '镇安镇', '镇安镇', 130),
(10817, 19, 10790, '中和镇', '中和镇', 135),
(10818, 19, 10790, '竹溪镇', '竹溪镇', 140),
(10819, 19, 10790, '三汇口乡', '三汇口乡', 145),
(10820, 19, 10790, '白桥乡', '白桥乡', 150),
(10821, 19, 10790, '大德乡', '大德乡', 155),
(10822, 19, 10790, '关面乡', '关面乡', 160),
(10823, 19, 10790, '金峰乡', '金峰乡', 165),
(10824, 19, 10790, '麻柳乡', '麻柳乡', 170),
(10825, 19, 10790, '满月乡', '满月乡', 175),
(10826, 19, 10790, '谭家乡', '谭家乡', 180),
(10827, 19, 10790, '天和乡', '天和乡', 185),
(10828, 19, 10790, '巫山镇', '巫山镇', 190),
(10829, 19, 10790, '五通乡', '五通乡', 195),
(10830, 19, 10790, '紫水乡', '紫水乡', 200),
(10831, 19, 10470, '云阳县', '云阳县', 60),
(10832, 19, 10831, '县城内', '县城内', 5),
(10833, 19, 10831, '云阳镇', '云阳镇', 10),
(10834, 19, 10831, '巴阳镇', '巴阳镇', 15),
(10835, 19, 10831, '凤鸣镇', '凤鸣镇', 20),
(10836, 19, 10831, '高阳镇', '高阳镇', 25),
(10837, 19, 10831, '故陵镇', '故陵镇', 30),
(10838, 19, 10831, '红狮镇', '红狮镇', 35),
(10839, 19, 10831, '黄石镇', '黄石镇', 40),
(10840, 19, 10831, '江口镇', '江口镇', 45),
(10841, 19, 10831, '龙角镇', '龙角镇', 50),
(10842, 19, 10831, '路阳镇', '路阳镇', 55),
(10843, 19, 10831, '南溪镇', '南溪镇', 60),
(10844, 19, 10831, '农坝镇', '农坝镇', 65),
(10845, 19, 10831, '盘龙镇', '盘龙镇', 70),
(10846, 19, 10831, '平安镇', '平安镇', 75),
(10847, 19, 10831, '渠马镇', '渠马镇', 80),
(10848, 19, 10831, '人和镇', '人和镇', 85),
(10849, 19, 10831, '桑坪镇', '桑坪镇', 90),
(10850, 19, 10831, '沙市镇', '沙市镇', 95),
(10851, 19, 10831, '双土镇', '双土镇', 100),
(10852, 19, 10831, '鱼泉镇', '鱼泉镇', 105),
(10853, 19, 10831, '云安镇', '云安镇', 110),
(10854, 19, 10831, '洞鹿乡', '洞鹿乡', 115),
(10855, 19, 10831, '后叶乡', '后叶乡', 120),
(10856, 19, 10831, '龙洞乡', '龙洞乡', 125),
(10857, 19, 10831, '毛坝乡', '毛坝乡', 130),
(10858, 19, 10831, '泥溪乡', '泥溪乡', 135),
(10859, 19, 10831, '票草乡', '票草乡', 140),
(10860, 19, 10831, '普安乡', '普安乡', 145),
(10861, 19, 10831, '栖霞乡', '栖霞乡', 150),
(10862, 19, 10831, '清水乡', '清水乡', 155),
(10863, 19, 10831, '上坝乡', '上坝乡', 160),
(10864, 19, 10831, '石门乡', '石门乡', 165),
(10865, 19, 10831, '双龙乡', '双龙乡', 170),
(10866, 19, 10831, '水口乡', '水口乡', 175),
(10867, 19, 10831, '外郎乡', '外郎乡', 180),
(10868, 19, 10831, '新津乡', '新津乡', 185),
(10869, 19, 10831, '堰坪乡', '堰坪乡', 190),
(10870, 19, 10831, '养鹿乡', '养鹿乡', 195),
(10871, 19, 10831, '耀灵乡', '耀灵乡', 200),
(10872, 19, 10831, '云硐乡', '云硐乡', 205),
(10873, 19, 10470, '忠县', '忠县', 65),
(10874, 19, 10873, '县城内', '县城内', 5),
(10875, 19, 10873, '忠州镇', '忠州镇', 10),
(10876, 19, 10873, '拔山镇', '拔山镇', 15),
(10877, 19, 10873, '白石镇', '白石镇', 20),
(10878, 19, 10873, '东溪镇', '东溪镇', 25),
(10879, 19, 10873, '复兴镇', '复兴镇', 30),
(10880, 19, 10873, '官坝镇', '官坝镇', 35),
(10881, 19, 10873, '花桥镇', '花桥镇', 40),
(10882, 19, 10873, '黄金镇', '黄金镇', 45),
(10883, 19, 10873, '金鸡镇', '金鸡镇', 50),
(10884, 19, 10873, '马灌镇', '马灌镇', 55),
(10885, 19, 10873, '任家镇', '任家镇', 60),
(10886, 19, 10873, '汝溪镇', '汝溪镇', 65),
(10887, 19, 10873, '三汇镇', '三汇镇', 70),
(10888, 19, 10873, '石宝镇', '石宝镇', 75),
(10889, 19, 10873, '石黄镇', '石黄镇', 80),
(10890, 19, 10873, '双桂镇', '双桂镇', 85),
(10891, 19, 10873, '乌杨镇', '乌杨镇', 90),
(10892, 19, 10873, '新生镇', '新生镇', 95),
(10893, 19, 10873, '洋渡镇', '洋渡镇', 100),
(10894, 19, 10873, '野鹤镇', '野鹤镇', 105),
(10895, 19, 10873, '永丰镇', '永丰镇', 110),
(10896, 19, 10873, '金声乡', '金声乡', 115),
(10897, 19, 10873, '磨子乡', '磨子乡', 120),
(10898, 19, 10873, '善广乡', '善广乡', 125),
(10899, 19, 10873, '石子乡', '石子乡', 130),
(10900, 19, 10873, '涂井乡', '涂井乡', 135),
(10901, 19, 10873, '兴峰乡', '兴峰乡', 140),
(10902, 19, 10873, '新立镇', '新立镇', 145),
(10903, 19, 10470, '巫溪县', '巫溪县', 70),
(10904, 19, 10903, '县城内', '县城内', 5),
(10905, 19, 10903, '城厢镇', '城厢镇', 10),
(10906, 19, 10903, '凤凰镇', '凤凰镇', 15),
(10907, 19, 10903, '古路镇', '古路镇', 20),
(10908, 19, 10903, '尖山镇', '尖山镇', 25),
(10909, 19, 10903, '宁厂镇', '宁厂镇', 30),
(10910, 19, 10903, '上磺镇', '上磺镇', 35),
(10911, 19, 10903, '文峰镇', '文峰镇', 40),
(10912, 19, 10903, '下堡镇', '下堡镇', 45),
(10913, 19, 10903, '徐家镇', '徐家镇', 50),
(10914, 19, 10903, '朝阳洞乡', '朝阳洞乡', 55),
(10915, 19, 10903, '大河乡', '大河乡', 60),
(10916, 19, 10903, '峰灵乡', '峰灵乡', 65),
(10917, 19, 10903, '花台乡', '花台乡', 70),
(10918, 19, 10903, '兰英乡', '兰英乡', 75),
(10919, 19, 10903, '菱角乡', '菱角乡', 80),
(10920, 19, 10903, '蒲莲乡', '蒲莲乡', 85),
(10921, 19, 10903, '胜利乡', '胜利乡', 90),
(10922, 19, 10903, '双阳乡', '双阳乡', 95),
(10923, 19, 10903, '塘坊乡', '塘坊乡', 100),
(10924, 19, 10903, '天星乡', '天星乡', 105),
(10925, 19, 10903, '天元乡', '天元乡', 110),
(10926, 19, 10903, '田坝乡', '田坝乡', 115),
(10927, 19, 10903, '通城乡', '通城乡', 120),
(10928, 19, 10903, '土城乡', '土城乡', 125),
(10929, 19, 10903, '乌龙乡', '乌龙乡', 130),
(10930, 19, 10903, '鱼鳞乡', '鱼鳞乡', 135),
(10931, 19, 10903, '长桂乡', '长桂乡', 140),
(10932, 19, 10903, '中岗乡', '中岗乡', 145),
(10933, 19, 10903, '中梁乡', '中梁乡', 150),
(10934, 19, 10470, '巫山县', '巫山县', 75),
(10935, 19, 10934, '县城内', '县城内', 5),
(10936, 19, 10934, '巫峡镇', '巫峡镇', 10),
(10937, 19, 10934, '大昌镇', '大昌镇', 15),
(10938, 19, 10934, '福田镇', '福田镇', 20),
(10939, 19, 10934, '官渡镇', '官渡镇', 25),
(10940, 19, 10934, '官阳镇', '官阳镇', 30),
(10941, 19, 10934, '龙溪镇', '龙溪镇', 35),
(10942, 19, 10934, '骡坪镇', '骡坪镇', 40),
(10943, 19, 10934, '庙堂乡', '庙堂乡', 45),
(10944, 19, 10934, '庙宇镇', '庙宇镇', 50),
(10945, 19, 10934, '双龙镇', '双龙镇', 55),
(10946, 19, 10934, '铜鼓镇', '铜鼓镇', 60),
(10947, 19, 10934, '抱龙镇', '抱龙镇', 65),
(10948, 19, 10934, '大溪乡', '大溪乡', 70),
(10949, 19, 10934, '当阳乡', '当阳乡', 75),
(10950, 19, 10934, '邓家乡', '邓家乡', 80),
(10951, 19, 10934, '笃坪乡', '笃坪乡', 85),
(10952, 19, 10934, '红椿乡', '红椿乡', 90),
(10953, 19, 10934, '建平乡', '建平乡', 95),
(10954, 19, 10934, '金坪乡', '金坪乡', 100),
(10955, 19, 10934, '两坪乡', '两坪乡', 105),
(10956, 19, 10934, '龙井乡', '龙井乡', 110),
(10957, 19, 10934, '培石乡', '培石乡', 115),
(10958, 19, 10934, '平河乡', '平河乡', 120),
(10959, 19, 10934, '曲尺乡', '曲尺乡', 125),
(10960, 19, 10934, '三溪乡', '三溪乡', 130),
(10961, 19, 10934, '竹贤乡', '竹贤乡', 135),
(10962, 19, 10470, '石柱县', '石柱县', 80),
(10963, 19, 10962, '王家乡', '王家乡', 5),
(10964, 19, 10962, '洗新乡', '洗新乡', 10),
(10965, 19, 10962, '新乐乡', '新乐乡', 15),
(10966, 19, 10962, '中益乡', '中益乡', 20),
(10967, 19, 10962, '县城内', '县城内', 25),
(10968, 19, 10962, '南宾镇', '南宾镇', 30),
(10969, 19, 10962, '黄水镇', '黄水镇', 35),
(10970, 19, 10962, '临溪镇', '临溪镇', 40),
(10971, 19, 10962, '龙沙镇', '龙沙镇', 45),
(10972, 19, 10962, '马武镇', '马武镇', 50),
(10973, 19, 10962, '沙子镇', '沙子镇', 55),
(10974, 19, 10962, '王场镇', '王场镇', 60),
(10975, 19, 10962, '西沱镇', '西沱镇', 65),
(10976, 19, 10962, '下路镇', '下路镇', 70),
(10977, 19, 10962, '沿溪镇', '沿溪镇', 75),
(10978, 19, 10962, '渔池镇', '渔池镇', 80),
(10979, 19, 10962, '悦崃镇', '悦崃镇', 85),
(10980, 19, 10962, '大歇乡', '大歇乡', 90),
(10981, 19, 10962, '枫木乡', '枫木乡', 95),
(10982, 19, 10962, '河嘴乡', '河嘴乡', 100),
(10983, 19, 10962, '黄鹤乡', '黄鹤乡', 105),
(10984, 19, 10962, '金铃乡', '金铃乡', 110),
(10985, 19, 10962, '金竹乡', '金竹乡', 115),
(10986, 19, 10962, '冷水乡', '冷水乡', 120),
(10987, 19, 10962, '黎场乡', '黎场乡', 125),
(10988, 19, 10962, '六塘乡', '六塘乡', 130),
(10989, 19, 10962, '龙潭乡', '龙潭乡', 135),
(10990, 19, 10962, '桥头乡', '桥头乡', 140),
(10991, 19, 10962, '三河乡', '三河乡', 145),
(10992, 19, 10962, '三益乡', '三益乡', 150),
(10993, 19, 10962, '石家乡', '石家乡', 155),
(10994, 19, 10962, '万朝乡', '万朝乡', 160),
(10995, 19, 10470, '彭水县', '彭水县', 85),
(10996, 19, 10995, '保家镇', '保家镇', 5),
(10997, 19, 10995, '高谷镇', '高谷镇', 10),
(10998, 19, 10995, '黄家镇', '黄家镇', 15),
(10999, 19, 10995, '连湖镇', '连湖镇', 20),
(11000, 19, 10995, '龙射镇', '龙射镇', 25),
(11001, 19, 10995, '鹿角镇', '鹿角镇', 30),
(11002, 19, 10995, '普子镇', '普子镇', 35),
(11003, 19, 10995, '桑柘镇', '桑柘镇', 40),
(11004, 19, 10995, '万足镇', '万足镇', 45),
(11005, 19, 10995, '郁山镇', '郁山镇', 50),
(11006, 19, 10995, '梅子垭乡', '梅子垭乡', 55),
(11007, 19, 10995, '鞍子乡', '鞍子乡', 60),
(11008, 19, 10995, '大垭乡', '大垭乡', 65),
(11009, 19, 10995, '棣棠乡', '棣棠乡', 70),
(11010, 19, 10995, '靛水乡', '靛水乡', 75),
(11011, 19, 10995, '朗溪乡', '朗溪乡', 80),
(11012, 19, 10995, '联合乡', '联合乡', 85),
(11013, 19, 10995, '龙塘乡', '龙塘乡', 90),
(11014, 19, 10995, '龙溪乡', '龙溪乡', 95),
(11015, 19, 10995, '芦塘乡', '芦塘乡', 100),
(11016, 19, 10995, '鹿鸣乡', '鹿鸣乡', 105),
(11017, 19, 10995, '平安乡', '平安乡', 110),
(11018, 19, 10995, '迁乔乡', '迁乔乡', 115),
(11019, 19, 10995, '乔梓乡', '乔梓乡', 120),
(11020, 19, 10995, '润溪乡', '润溪乡', 125),
(11021, 19, 10995, '三义乡', '三义乡', 130),
(11022, 19, 10995, '善感乡', '善感乡', 135),
(11023, 19, 10995, '县城内', '县城内', 140),
(11024, 19, 10995, '石柳乡', '石柳乡', 145),
(11025, 19, 10995, '石盘乡', '石盘乡', 150),
(11026, 19, 10995, '双龙乡', '双龙乡', 155),
(11027, 19, 10995, '太原乡', '太原乡', 160),
(11028, 19, 10995, '桐楼乡', '桐楼乡', 165),
(11029, 19, 10995, '小厂乡', '小厂乡', 170),
(11030, 19, 10995, '新田乡', '新田乡', 175),
(11031, 19, 10995, '岩东乡', '岩东乡', 180),
(11032, 19, 10995, '长滩乡', '长滩乡', 185),
(11033, 19, 10995, '诸佛乡', '诸佛乡', 190),
(11034, 19, 10995, '走马乡', '走马乡', 195),
(11035, 19, 10470, '垫江县', '垫江县', 90),
(11036, 19, 11035, '县城内', '县城内', 5),
(11037, 19, 11035, '桂溪镇', '桂溪镇', 10),
(11038, 19, 11035, '澄溪镇', '澄溪镇', 15),
(11039, 19, 11035, '高安镇', '高安镇', 20),
(11040, 19, 11035, '高峰镇', '高峰镇', 25),
(11041, 19, 11035, '鹤游镇', '鹤游镇', 30),
(11042, 19, 11035, '普顺镇', '普顺镇', 35),
(11043, 19, 11035, '沙坪镇', '沙坪镇', 40),
(11044, 19, 11035, '太平镇', '太平镇', 45),
(11045, 19, 11035, '五洞镇', '五洞镇', 50),
(11046, 19, 11035, '新民镇', '新民镇', 55),
(11047, 19, 11035, '砚台镇', '砚台镇', 60),
(11048, 19, 11035, '永安镇', '永安镇', 65),
(11049, 19, 11035, '周嘉镇', '周嘉镇', 70),
(11050, 19, 11035, '白家乡', '白家乡', 75),
(11051, 19, 11035, '包家乡', '包家乡', 80),
(11052, 19, 11035, '曹回乡', '曹回乡', 85),
(11053, 19, 11035, '大石乡', '大石乡', 90),
(11054, 19, 11035, '杠家乡', '杠家乡', 95),
(11055, 19, 11035, '坪山镇', '坪山镇', 100),
(11056, 19, 11035, '黄沙乡', '黄沙乡', 105),
(11057, 19, 11035, '裴兴乡', '裴兴乡', 110),
(11058, 19, 11035, '三溪乡', '三溪乡', 115),
(11059, 19, 11035, '沙河乡', '沙河乡', 120),
(11060, 19, 11035, '永平乡', '永平乡', 125),
(11061, 19, 11035, '长龙乡', '长龙乡', 130),
(11062, 19, 10470, '酉阳县', '酉阳县', 95),
(11063, 19, 11062, '县城内', '县城内', 5),
(11064, 19, 11062, '钟多镇', '钟多镇', 10),
(11065, 19, 11062, '苍岭镇', '苍岭镇', 15),
(11066, 19, 11062, '车田乡', '车田乡', 20),
(11067, 19, 11062, '大溪镇', '大溪镇', 25),
(11068, 19, 11062, '丁市镇', '丁市镇', 30),
(11069, 19, 11062, '泔溪镇', '泔溪镇', 35),
(11070, 19, 11062, '龚滩镇', '龚滩镇', 40),
(11071, 19, 11062, '黑水镇', '黑水镇', 45),
(11072, 19, 11062, '后溪镇', '后溪镇', 50),
(11073, 19, 11062, '李溪镇', '李溪镇', 55),
(11074, 19, 11062, '龙潭镇', '龙潭镇', 60),
(11075, 19, 11062, '麻旺镇', '麻旺镇', 65),
(11076, 19, 11062, '小河镇', '小河镇', 70),
(11077, 19, 11062, '兴隆镇', '兴隆镇', 75),
(11078, 19, 11062, '酉酬镇', '酉酬镇', 80),
(11079, 19, 11062, '南腰界乡', '南腰界乡', 85),
(11080, 19, 11062, '后坪坝乡', '后坪坝乡', 90),
(11081, 19, 11062, '板溪乡', '板溪乡', 95),
(11082, 19, 11062, '官清乡', '官清乡', 100),
(11083, 19, 11062, '花田乡', '花田乡', 105),
(11084, 19, 11062, '江丰乡', '江丰乡', 110),
(11085, 19, 11062, '可大乡', '可大乡', 115),
(11086, 19, 11062, '浪坪乡', '浪坪乡', 120),
(11087, 19, 11062, '两罾乡', '两罾乡', 125),
(11088, 19, 11062, '毛坝乡', '毛坝乡', 130),
(11089, 19, 11062, '庙溪乡', '庙溪乡', 135),
(11090, 19, 11062, '木叶乡', '木叶乡', 140),
(11091, 19, 11062, '楠木乡', '楠木乡', 145),
(11092, 19, 11062, '偏柏乡', '偏柏乡', 150),
(11093, 19, 11062, '清泉乡', '清泉乡', 155),
(11094, 19, 11062, '双泉乡', '双泉乡', 160),
(11095, 19, 11062, '天馆乡', '天馆乡', 165),
(11096, 19, 11062, '铜鼓乡', '铜鼓乡', 170),
(11097, 19, 11062, '涂市乡', '涂市乡', 175),
(11098, 19, 11062, '万木乡', '万木乡', 180),
(11099, 19, 11062, '五福乡', '五福乡', 185),
(11100, 19, 11062, '宜居乡', '宜居乡', 190),
(11101, 19, 11062, '腴地乡', '腴地乡', 195),
(11102, 19, 11062, '板桥乡', '板桥乡', 200),
(11103, 19, 10470, '秀山县', '秀山县', 100),
(11104, 19, 11103, '县城内', '县城内', 5),
(11105, 19, 11103, '清溪场镇', '清溪场镇', 10),
(11106, 19, 11103, '中和镇', '中和镇', 15),
(11107, 19, 11103, '隘口镇', '隘口镇', 20),
(11108, 19, 11103, '峨溶镇', '峨溶镇', 25),
(11109, 19, 11103, '官庄镇', '官庄镇', 30),
(11110, 19, 11103, '洪安镇', '洪安镇', 35),
(11111, 19, 11103, '兰桥镇', '兰桥镇', 40),
(11112, 19, 11103, '龙池镇', '龙池镇', 45),
(11113, 19, 11103, '梅江镇', '梅江镇', 50),
(11114, 19, 11103, '平凯镇', '平凯镇', 55),
(11115, 19, 11103, '溶溪镇', '溶溪镇', 60),
(11116, 19, 11103, '石堤镇', '石堤镇', 65),
(11117, 19, 11103, '石耶镇', '石耶镇', 70),
(11118, 19, 11103, '雅江镇', '雅江镇', 75),
(11119, 19, 11103, '巴家乡', '巴家乡', 80),
(11120, 19, 11103, '保安乡', '保安乡', 85),
(11121, 19, 11103, '岑溪乡', '岑溪乡', 90),
(11122, 19, 11103, '大溪乡', '大溪乡', 95),
(11123, 19, 11103, '干川乡', '干川乡', 100),
(11124, 19, 11103, '膏田乡', '膏田乡', 105),
(11125, 19, 11103, '官舟乡', '官舟乡', 110),
(11126, 19, 11103, '海洋乡', '海洋乡', 115),
(11127, 19, 11103, '里仁乡', '里仁乡', 120),
(11128, 19, 11103, '妙泉乡', '妙泉乡', 125),
(11129, 19, 11103, '平马乡', '平马乡', 130),
(11130, 19, 11103, '宋农乡', '宋农乡', 135),
(11131, 19, 11103, '溪口乡', '溪口乡', 140),
(11132, 19, 11103, '孝溪乡', '孝溪乡', 145),
(11133, 19, 11103, '涌洞乡', '涌洞乡', 150),
(11134, 19, 11103, '中平乡', '中平乡', 155),
(11135, 19, 11103, '钟灵乡', '钟灵乡', 160),
(11136, 19, 10470, '城口县', '城口县', 105),
(11137, 19, 11136, '县城内', '县城内', 5),
(11138, 19, 11136, '葛城镇', '葛城镇', 10),
(11139, 19, 11136, '巴山镇', '巴山镇', 15),
(11140, 19, 11136, '高观镇', '高观镇', 20),
(11141, 19, 11136, '庙坝镇', '庙坝镇', 25),
(11142, 19, 11136, '明通镇', '明通镇', 30),
(11143, 19, 11136, '坪坝镇', '坪坝镇', 35),
(11144, 19, 11136, '修齐镇', '修齐镇', 40),
(11145, 19, 11136, '北屏乡', '北屏乡', 45),
(11146, 19, 11136, '东安乡', '东安乡', 50),
(11147, 19, 11136, '高楠乡', '高楠乡', 55),
(11148, 19, 11136, '高燕乡', '高燕乡', 60),
(11149, 19, 11136, '河鱼乡', '河鱼乡', 65),
(11150, 19, 11136, '厚坪乡', '厚坪乡', 70),
(11151, 19, 11136, '鸡鸣乡', '鸡鸣乡', 75),
(11152, 19, 11136, '岚天乡', '岚天乡', 80),
(11153, 19, 11136, '蓼子乡', '蓼子乡', 85),
(11154, 19, 11136, '龙田乡', '龙田乡', 90),
(11155, 19, 11136, '明中乡', '明中乡', 95),
(11156, 19, 11136, '双河乡', '双河乡', 100),
(11157, 19, 11136, '咸宜乡', '咸宜乡', 105),
(11158, 19, 11136, '沿河乡', '沿河乡', 110),
(11159, 19, 11136, '治平乡', '治平乡', 115),
(11160, 19, 11136, '周溪乡', '周溪乡', 120),
(11161, 19, 11136, '左岚乡', '左岚乡', 125),
(11162, 19, 10470, '璧山县', '璧山县', 110),
(11163, 19, 11162, '县城内', '县城内', 5),
(11164, 19, 11162, '青杠镇', '青杠镇', 10),
(11165, 19, 11162, '来凤镇', '来凤镇', 15),
(11166, 19, 11162, '丁家镇', '丁家镇', 20),
(11167, 19, 11162, '大路镇', '大路镇', 25),
(11168, 19, 11162, '八塘镇', '八塘镇', 30),
(11169, 19, 11162, '七塘镇', '七塘镇', 35),
(11170, 19, 11162, '河边镇', '河边镇', 40),
(11171, 19, 11162, '福禄镇', '福禄镇', 45),
(11172, 19, 11162, '大兴镇', '大兴镇', 50),
(11173, 19, 11162, '正兴镇', '正兴镇', 55),
(11174, 19, 11162, '广普镇', '广普镇', 60),
(11175, 19, 11162, '三合镇', '三合镇', 65),
(11176, 19, 11162, '健龙镇', '健龙镇', 70),
(11177, 19, 10470, '荣昌县', '荣昌县', 115),
(11178, 19, 11177, '县城内', '县城内', 5),
(11179, 19, 11177, '广顺镇', '广顺镇', 10),
(11180, 19, 11177, '安富镇', '安富镇', 15),
(11181, 19, 11177, '峰高镇', '峰高镇', 20),
(11182, 19, 11177, '双河镇', '双河镇', 25),
(11183, 19, 11177, '直升镇', '直升镇', 30),
(11184, 19, 11177, '路孔镇', '路孔镇', 35),
(11185, 19, 11177, '清江镇', '清江镇', 40),
(11186, 19, 11177, '仁义镇', '仁义镇', 45),
(11187, 19, 11177, '河包镇', '河包镇', 50),
(11188, 19, 11177, '古昌镇', '古昌镇', 55),
(11189, 19, 11177, '吴家镇', '吴家镇', 60),
(11190, 19, 11177, '观胜镇', '观胜镇', 65),
(11191, 19, 11177, '铜鼓镇', '铜鼓镇', 70),
(11192, 19, 11177, '清流镇', '清流镇', 75),
(11193, 19, 11177, '盘龙镇', '盘龙镇', 80),
(11194, 19, 11177, '远觉镇', '远觉镇', 85),
(11195, 19, 11177, '清升镇', '清升镇', 90),
(11196, 19, 11177, '荣隆镇', '荣隆镇', 95),
(11197, 19, 11177, '龙集镇', '龙集镇', 100),
(11198, 19, 10470, '铜梁县', '铜梁县', 120),
(11199, 19, 11198, '县城内', '县城内', 5),
(11200, 19, 11198, '土桥镇', '土桥镇', 10),
(11201, 19, 11198, '二坪镇', '二坪镇', 15),
(11202, 19, 11198, '水口镇', '水口镇', 20),
(11203, 19, 11198, '安居镇', '安居镇', 25),
(11204, 19, 11198, '白羊镇', '白羊镇', 30),
(11205, 19, 11198, '平滩镇', '平滩镇', 35),
(11206, 19, 11198, '石鱼镇', '石鱼镇', 40),
(11207, 19, 11198, '福果镇', '福果镇', 45),
(11208, 19, 11198, '维新镇', '维新镇', 50),
(11209, 19, 11198, '高楼镇', '高楼镇', 55),
(11210, 19, 11198, '大庙镇', '大庙镇', 60),
(11211, 19, 11198, '围龙镇', '围龙镇', 65),
(11212, 19, 11198, '华兴镇', '华兴镇', 70),
(11213, 19, 11198, '永嘉镇', '永嘉镇', 75),
(11214, 19, 11198, '安溪镇', '安溪镇', 80),
(11215, 19, 11198, '西河镇', '西河镇', 85),
(11216, 19, 11198, '太平镇', '太平镇', 90),
(11217, 19, 11198, '旧县镇', '旧县镇', 95),
(11218, 19, 11198, '虎峰镇', '虎峰镇', 100),
(11219, 19, 11198, '少云镇', '少云镇', 105),
(11220, 19, 11198, '蒲吕镇', '蒲吕镇', 110),
(11221, 19, 11198, '侣俸镇', '侣俸镇', 115),
(11222, 19, 11198, '小林乡', '小林乡', 120),
(11223, 19, 11198, '双山乡', '双山乡', 125),
(11224, 19, 11198, '庆隆乡', '庆隆乡', 130),
(11225, 19, 10470, '合川区', '合川区', 125),
(11226, 19, 11225, '城区', '城区', 5),
(11227, 19, 11225, '草街镇', '草街镇', 10),
(11228, 19, 11225, '盐井镇', '盐井镇', 15),
(11229, 19, 11225, '云门镇', '云门镇', 20),
(11230, 19, 11225, '大石镇', '大石镇', 25),
(11231, 19, 11225, '沙鱼镇', '沙鱼镇', 30),
(11232, 19, 11225, '官渡镇', '官渡镇', 35),
(11233, 19, 11225, '涞滩镇', '涞滩镇', 40),
(11234, 19, 11225, '肖家镇', '肖家镇', 45),
(11235, 19, 11225, '古楼镇', '古楼镇', 50),
(11236, 19, 11225, '三庙镇', '三庙镇', 55),
(11237, 19, 11225, '二郎镇', '二郎镇', 60),
(11238, 19, 11225, '龙凤镇', '龙凤镇', 65),
(11239, 19, 11225, '隆兴镇', '隆兴镇', 70),
(11240, 19, 11225, '铜溪镇', '铜溪镇', 75),
(11241, 19, 11225, '双凤镇', '双凤镇', 80),
(11242, 19, 11225, '狮滩镇', '狮滩镇', 85),
(11243, 19, 11225, '清平镇', '清平镇', 90),
(11244, 19, 11225, '土场镇', '土场镇', 95),
(11245, 19, 11225, '小沔镇', '小沔镇', 100),
(11246, 19, 11225, '三汇镇', '三汇镇', 105),
(11247, 19, 11225, '香龙镇', '香龙镇', 110),
(11248, 19, 11225, '钱塘镇', '钱塘镇', 115),
(11249, 19, 11225, '龙市镇', '龙市镇', 120),
(11250, 19, 11225, '燕窝镇', '燕窝镇', 125),
(11251, 19, 11225, '太和镇', '太和镇', 130),
(11252, 19, 11225, '渭沱镇', '渭沱镇', 135),
(11253, 19, 11225, '双槐镇', '双槐镇', 140),
(11254, 19, 10470, '巴南区', '巴南区', 130),
(11255, 19, 11254, '城区', '城区', 5),
(11256, 19, 11254, '南泉镇', '南泉镇', 10),
(11257, 19, 11254, '一品镇', '一品镇', 15),
(11258, 19, 11254, '南彭镇', '南彭镇', 20),
(11259, 19, 11254, '惠民镇', '惠民镇', 25),
(11260, 19, 11254, '麻柳嘴镇', '麻柳嘴镇', 30),
(11261, 19, 11254, '天星寺镇', '天星寺镇', 35),
(11262, 19, 11254, '双河口镇', '双河口镇', 40),
(11263, 19, 11254, '界石镇', '界石镇', 45),
(11264, 19, 11254, '安澜镇', '安澜镇', 50),
(11265, 19, 11254, '跳石镇', '跳石镇', 55),
(11266, 19, 11254, '木洞镇', '木洞镇', 60),
(11267, 19, 11254, '丰盛镇', '丰盛镇', 65),
(11268, 19, 11254, '二圣镇', '二圣镇', 70),
(11269, 19, 11254, '东泉镇', '东泉镇', 75),
(11270, 19, 11254, '姜家镇', '姜家镇', 80),
(11271, 19, 11254, '接龙镇', '接龙镇', 85),
(11272, 19, 11254, '石滩镇', '石滩镇', 90),
(11273, 19, 11254, '石龙镇', '石龙镇', 95),
(11274, 19, 10470, '北碚区', '北碚区', 135),
(11275, 19, 11274, '城区', '城区', 5),
(11276, 19, 11274, '东阳镇', '东阳镇', 10),
(11277, 19, 11274, '蔡家岗镇', '蔡家岗镇', 15),
(11278, 19, 11274, '童家溪镇', '童家溪镇', 20),
(11279, 19, 11274, '施家梁镇', '施家梁镇', 25),
(11280, 19, 11274, '金刀峡镇', '金刀峡镇', 30),
(11281, 19, 11274, '澄江镇', '澄江镇', 35),
(11282, 19, 11274, '水土镇', '水土镇', 40),
(11283, 19, 11274, '歇马镇', '歇马镇', 45),
(11284, 19, 11274, '天府镇', '天府镇', 50),
(11285, 19, 11274, '复兴镇', '复兴镇', 55),
(11286, 19, 11274, '静观镇', '静观镇', 60),
(11287, 19, 11274, '柳荫镇', '柳荫镇', 65),
(11288, 19, 11274, '三圣镇', '三圣镇', 70),
(11289, 19, 10470, '江津区', '江津区', 140),
(11290, 19, 11289, '城区', '城区', 5),
(11291, 19, 11289, '四面山镇', '四面山镇', 10),
(11292, 19, 11289, '支坪镇', '支坪镇', 15),
(11293, 19, 11289, '白沙镇', '白沙镇', 20),
(11294, 19, 11289, '珞璜镇', '珞璜镇', 25),
(11295, 19, 11289, '柏林镇', '柏林镇', 30),
(11296, 19, 11289, '蔡家镇', '蔡家镇', 35),
(11297, 19, 11289, '慈云镇', '慈云镇', 40),
(11298, 19, 11289, '杜市镇', '杜市镇', 45),
(11299, 19, 11289, '广兴镇', '广兴镇', 50),
(11300, 19, 11289, '嘉平镇', '嘉平镇', 55),
(11301, 19, 11289, '贾嗣镇', '贾嗣镇', 60),
(11302, 19, 11289, '李市镇', '李市镇', 65),
(11303, 19, 11289, '龙华镇', '龙华镇', 70),
(11304, 19, 11289, '石蟆镇', '石蟆镇', 75),
(11305, 19, 11289, '石门镇', '石门镇', 80),
(11306, 19, 11289, '塘河镇', '塘河镇', 85),
(11307, 19, 11289, '吴滩镇', '吴滩镇', 90),
(11308, 19, 11289, '西湖镇', '西湖镇', 95),
(11309, 19, 11289, '夏坝镇', '夏坝镇', 100),
(11310, 19, 11289, '先锋镇', '先锋镇', 105),
(11311, 19, 11289, '永兴镇', '永兴镇', 110),
(11312, 19, 11289, '油溪镇', '油溪镇', 115),
(11313, 19, 11289, '中山镇', '中山镇', 120),
(11314, 19, 11289, '朱杨镇', '朱杨镇', 125),
(11315, 19, 10470, '渝北区', '渝北区', 145),
(11316, 19, 11315, '城区', '城区', 5),
(11317, 19, 11315, '礼嘉镇', '礼嘉镇', 10),
(11318, 19, 11315, '两路镇', '两路镇', 15),
(11319, 19, 11315, '王家镇', '王家镇', 20),
(11320, 19, 11315, '悦来镇', '悦来镇', 25),
(11321, 19, 11315, '玉峰山镇', '玉峰山镇', 30),
(11322, 19, 11315, '茨竹镇', '茨竹镇', 35),
(11323, 19, 11315, '大盛镇', '大盛镇', 40),
(11324, 19, 11315, '大塆镇', '大塆镇', 45),
(11325, 19, 11315, '古路镇', '古路镇', 50),
(11326, 19, 11315, '龙兴镇', '龙兴镇', 55),
(11327, 19, 11315, '洛碛镇', '洛碛镇', 60),
(11328, 19, 11315, '木耳镇', '木耳镇', 65),
(11329, 19, 11315, '石船镇', '石船镇', 70),
(11330, 19, 11315, '统景镇', '统景镇', 75),
(11331, 19, 11315, '兴隆镇', '兴隆镇', 80),
(11332, 19, 10470, '长寿区', '长寿区', 150),
(11333, 19, 11332, '城区', '城区', 5),
(11334, 19, 11332, '长寿湖镇', '长寿湖镇', 10),
(11335, 19, 11332, '邻封镇', '邻封镇', 15),
(11336, 19, 11332, '但渡镇', '但渡镇', 20),
(11337, 19, 11332, '云集镇', '云集镇', 25),
(11338, 19, 11332, '双龙镇', '双龙镇', 30),
(11339, 19, 11332, '龙河镇', '龙河镇', 35),
(11340, 19, 11332, '石堰镇', '石堰镇', 40),
(11341, 19, 11332, '云台镇', '云台镇', 45),
(11342, 19, 11332, '海棠镇', '海棠镇', 50),
(11343, 19, 11332, '葛兰镇', '葛兰镇', 55),
(11344, 19, 11332, '新市镇', '新市镇', 60),
(11345, 19, 11332, '八颗镇', '八颗镇', 65),
(11346, 19, 11332, '洪湖镇', '洪湖镇', 70),
(11347, 19, 11332, '万顺镇', '万顺镇', 75),
(11348, 19, 10470, '永川区', '永川区', 155),
(11349, 19, 11348, '城区', '城区', 5),
(11350, 19, 11348, '双竹镇', '双竹镇', 10),
(11351, 19, 11348, '三教镇', '三教镇', 15),
(11352, 19, 11348, '大安镇', '大安镇', 20),
(11353, 19, 11348, '陈食镇', '陈食镇', 25),
(11354, 19, 11348, '板桥镇', '板桥镇', 30),
(11355, 19, 11348, '宝峰镇', '宝峰镇', 35),
(11356, 19, 11348, '临江镇', '临江镇', 40),
(11357, 19, 11348, '红炉镇', '红炉镇', 45),
(11358, 19, 11348, '吉安镇', '吉安镇', 50),
(11359, 19, 11348, '金龙镇', '金龙镇', 55),
(11360, 19, 11348, '来苏镇', '来苏镇', 60),
(11361, 19, 11348, '青峰镇', '青峰镇', 65),
(11362, 19, 11348, '三教镇', '三教镇', 70),
(11363, 19, 11348, '双石镇', '双石镇', 75),
(11364, 19, 11348, '松溉镇', '松溉镇', 80),
(11365, 19, 11348, '五间镇', '五间镇', 85),
(11366, 19, 11348, '仙龙镇', '仙龙镇', 90),
(11367, 19, 11348, '永荣镇', '永荣镇', 95),
(11368, 19, 11348, '朱沱镇', '朱沱镇', 100),
(11369, 19, 11348, '何埂镇', '何埂镇', 105),
(11370, 19, 10470, '江北区', '江北区', 160),
(11371, 19, 11370, '内环以内', '内环以内', 5),
(11372, 19, 11370, '寸滩镇', '寸滩镇', 10),
(11373, 19, 11370, '郭家沱镇', '郭家沱镇', 15),
(11374, 19, 11370, '铁山坪镇', '铁山坪镇', 20),
(11375, 19, 11370, '鱼嘴镇', '鱼嘴镇', 25),
(11376, 19, 11370, '复盛镇', '复盛镇', 30),
(11377, 19, 11370, '五宝镇', '五宝镇', 35),
(11378, 19, 11370, '大石坝镇', '大石坝镇', 40),
(11379, 19, 10470, '南岸区', '南岸区', 165),
(11380, 19, 11379, '城区', '城区', 5),
(11381, 19, 11379, '内环以内', '内环以内', 10),
(11382, 19, 11379, '茶园新区', '茶园新区', 15),
(11383, 19, 11379, '鸡冠石镇', '鸡冠石镇', 20),
(11384, 19, 11379, '长生桥镇', '长生桥镇', 25),
(11385, 19, 11379, '峡口镇', '峡口镇', 30),
(11386, 19, 11379, '广阳镇', '广阳镇', 35),
(11387, 19, 11379, '迎龙镇', '迎龙镇', 40),
(11388, 19, 10470, '九龙坡区', '九龙坡区', 170),
(11389, 19, 11388, '内环以内', '内环以内', 5),
(11390, 19, 11388, '白市驿镇', '白市驿镇', 10),
(11391, 19, 11388, '铜罐驿镇', '铜罐驿镇', 15),
(11392, 19, 11388, '华岩镇', '华岩镇', 20),
(11393, 19, 11388, '巴福镇', '巴福镇', 25),
(11394, 19, 11388, '含谷镇', '含谷镇', 30),
(11395, 19, 11388, '金凤镇', '金凤镇', 35),
(11396, 19, 11388, '石板镇', '石板镇', 40),
(11397, 19, 11388, '陶家镇', '陶家镇', 45),
(11398, 19, 11388, '西彭镇', '西彭镇', 50),
(11399, 19, 11388, '走马镇', '走马镇', 55),
(11400, 19, 10470, '沙坪坝区', '沙坪坝区', 175),
(11401, 19, 11400, '内环以内', '内环以内', 5),
(11402, 19, 11400, '陈家桥镇', '陈家桥镇', 10),
(11403, 19, 11400, '歌乐山镇', '歌乐山镇', 15),
(11404, 19, 11400, '青木关镇', '青木关镇', 20),
(11405, 19, 11400, '回龙坝镇', '回龙坝镇', 25),
(11406, 19, 11400, '大学城', '大学城', 30),
(11407, 19, 11400, '虎溪镇', '虎溪镇', 35),
(11408, 19, 11400, '西永镇', '西永镇', 40),
(11409, 19, 11400, '土主镇', '土主镇', 45),
(11410, 19, 11400, '井口镇', '井口镇', 50),
(11411, 19, 11400, '曾家镇', '曾家镇', 55),
(11412, 19, 11400, '凤凰镇', '凤凰镇', 60),
(11413, 19, 11400, '中梁镇', '中梁镇', 65),
(11414, 19, 10470, '大渡口区', '大渡口区', 180),
(11415, 19, 11414, '茄子溪镇', '茄子溪镇', 5),
(11416, 19, 11414, '建胜镇', '建胜镇', 10),
(11417, 19, 11414, '跳磴镇', '跳磴镇', 15),
(11418, 19, 11414, '内环以内', '内环以内', 20),
(11419, 19, 10470, '綦江区', '綦江区', 185),
(11420, 19, 11419, '城区', '城区', 5),
(11421, 19, 11419, '三江镇', '三江镇', 10),
(11422, 19, 11419, '安稳镇', '安稳镇', 15),
(11423, 19, 11419, '打通镇', '打通镇', 20),
(11424, 19, 11419, '丁山镇', '丁山镇', 25),
(11425, 19, 11419, '东溪镇', '东溪镇', 30),
(11426, 19, 11419, '扶欢镇', '扶欢镇', 35),
(11427, 19, 11419, '赶水镇', '赶水镇', 40),
(11428, 19, 11419, '郭扶镇', '郭扶镇', 45),
(11429, 19, 11419, '横山镇', '横山镇', 50),
(11430, 19, 11419, '隆盛镇', '隆盛镇', 55),
(11431, 19, 11419, '三角镇', '三角镇', 60),
(11432, 19, 11419, '石壕镇', '石壕镇', 65),
(11433, 19, 11419, '石角镇', '石角镇', 70),
(11434, 19, 11419, '新盛镇', '新盛镇', 75),
(11435, 19, 11419, '永城镇', '永城镇', 80),
(11436, 19, 11419, '永新镇', '永新镇', 85),
(11437, 19, 11419, '中峰镇', '中峰镇', 90),
(11438, 19, 11419, '篆塘镇', '篆塘镇', 95),
(11439, 19, 11419, '丛林镇', '丛林镇', 100),
(11440, 19, 11419, '关坝镇', '关坝镇', 105),
(11441, 19, 11419, '黑山镇', '黑山镇', 110),
(11442, 19, 11419, '金桥镇', '金桥镇', 115),
(11443, 19, 11419, '南桐镇', '南桐镇', 120),
(11444, 19, 11419, '青年镇', '青年镇', 125),
(11445, 19, 11419, '石林镇', '石林镇', 130),
(11446, 19, 11419, '万东镇', '万东镇', 135),
(11447, 19, 10470, '渝中区', '渝中区', 190),
(11448, 19, 10470, '高新区', '高新区', 195),
(11449, 19, 10470, '北部新区', '北部新区', 200),
(11450, 19, 0, '河北省', '河北省', 25),
(11451, 19, 11450, '石家庄市', '石家庄市', 5),
(11452, 19, 11451, '藁城市', '藁城市', 5),
(11453, 19, 11451, '鹿泉市', '鹿泉市', 10),
(11454, 19, 11451, '正定县', '正定县', 15),
(11455, 19, 11451, '新华区', '新华区', 20),
(11456, 19, 11451, '桥西区', '桥西区', 25),
(11457, 19, 11451, '桥东区', '桥东区', 30),
(11458, 19, 11451, '裕华区', '裕华区', 35),
(11459, 19, 11451, '长安区', '长安区', 40),
(11460, 19, 11451, '辛集市', '辛集市', 45),
(11461, 19, 11451, '晋州市', '晋州市', 50),
(11462, 19, 11451, '新乐市', '新乐市', 55),
(11463, 19, 11451, '平山县', '平山县', 60),
(11464, 19, 11451, '井陉矿区', '井陉矿区', 65),
(11465, 19, 11451, '井陉县', '井陉县', 70),
(11466, 19, 11451, '栾城县', '栾城县', 75),
(11467, 19, 11451, '行唐县', '行唐县', 80),
(11468, 19, 11451, '灵寿县', '灵寿县', 85),
(11469, 19, 11451, '高邑县', '高邑县', 90),
(11470, 19, 11451, '赵县', '赵县', 95),
(11471, 19, 11451, '赞皇县', '赞皇县', 100),
(11472, 19, 11451, '深泽县', '深泽县', 105),
(11473, 19, 11451, '无极县', '无极县', 110),
(11474, 19, 11451, '元氏县', '元氏县', 115),
(11475, 19, 11450, '邯郸市', '邯郸市', 10),
(11476, 19, 11475, '丛台区', '丛台区', 5),
(11477, 19, 11475, '邯山区', '邯山区', 10),
(11478, 19, 11475, '复兴区', '复兴区', 15),
(11479, 19, 11475, '武安市', '武安市', 20),
(11480, 19, 11475, '临漳县', '临漳县', 25),
(11481, 19, 11475, '永年县', '永年县', 30),
(11482, 19, 11475, '邯郸县', '邯郸县', 35),
(11483, 19, 11475, '峰峰矿区', '峰峰矿区', 40),
(11484, 19, 11475, '曲周县', '曲周县', 45),
(11485, 19, 11475, '馆陶县', '馆陶县', 50),
(11486, 19, 11475, '魏县', '魏县', 55),
(11487, 19, 11475, '成安县', '成安县', 60),
(11488, 19, 11475, '大名县', '大名县', 65),
(11489, 19, 11475, '涉县', '涉县', 70),
(11490, 19, 11475, '鸡泽县', '鸡泽县', 75),
(11491, 19, 11475, '邱县', '邱县', 80),
(11492, 19, 11475, '广平县', '广平县', 85),
(11493, 19, 11475, '肥乡县', '肥乡县', 90),
(11494, 19, 11475, '磁县', '磁县', 95),
(11495, 19, 11450, '邢台市', '邢台市', 15),
(11496, 19, 11495, '宁晋县', '宁晋县', 5),
(11497, 19, 11495, '威县', '威县', 10);
INSERT INTO `qinggan_opt` (`id`, `group_id`, `parent_id`, `title`, `val`, `taxis`) VALUES
(11498, 19, 11495, '桥西区', '桥西区', 15),
(11499, 19, 11495, '桥东区', '桥东区', 20),
(11500, 19, 11495, '邢台县', '邢台县', 25),
(11501, 19, 11495, '南宫市', '南宫市', 30),
(11502, 19, 11495, '沙河市', '沙河市', 35),
(11503, 19, 11495, '柏乡县', '柏乡县', 40),
(11504, 19, 11495, '任县', '任县', 45),
(11505, 19, 11495, '清河县', '清河县', 50),
(11506, 19, 11495, '隆尧县', '隆尧县', 55),
(11507, 19, 11495, '临城县', '临城县', 60),
(11508, 19, 11495, '广宗县', '广宗县', 65),
(11509, 19, 11495, '临西县', '临西县', 70),
(11510, 19, 11495, '内丘县', '内丘县', 75),
(11511, 19, 11495, '平乡县', '平乡县', 80),
(11512, 19, 11495, '巨鹿县', '巨鹿县', 85),
(11513, 19, 11495, '新河县', '新河县', 90),
(11514, 19, 11495, '南和县', '南和县', 95),
(11515, 19, 11450, '保定市', '保定市', 20),
(11516, 19, 11515, '涿州市', '涿州市', 5),
(11517, 19, 11515, '定州市', '定州市', 10),
(11518, 19, 11515, '徐水县', '徐水县', 15),
(11519, 19, 11515, '高碑店市', '高碑店市', 20),
(11520, 19, 11515, '新市区', '新市区', 25),
(11521, 19, 11515, '北市区', '北市区', 30),
(11522, 19, 11515, '南市区', '南市区', 35),
(11523, 19, 11515, '安国市', '安国市', 40),
(11524, 19, 11515, '安新县', '安新县', 45),
(11525, 19, 11515, '满城县', '满城县', 50),
(11526, 19, 11515, '清苑县', '清苑县', 55),
(11527, 19, 11515, '涞水县', '涞水县', 60),
(11528, 19, 11515, '阜平县', '阜平县', 65),
(11529, 19, 11515, '定兴县', '定兴县', 70),
(11530, 19, 11515, '唐县', '唐县', 75),
(11531, 19, 11515, '高阳县', '高阳县', 80),
(11532, 19, 11515, '容城县', '容城县', 85),
(11533, 19, 11515, '涞源县', '涞源县', 90),
(11534, 19, 11515, '望都县', '望都县', 95),
(11535, 19, 11515, '易县', '易县', 100),
(11536, 19, 11515, '曲阳县', '曲阳县', 105),
(11537, 19, 11515, '蠡县', '蠡县', 110),
(11538, 19, 11515, '顺平县', '顺平县', 115),
(11539, 19, 11515, '博野县', '博野县', 120),
(11540, 19, 11515, '雄县', '雄县', 125),
(11541, 19, 11450, '张家口市', '张家口市', 25),
(11542, 19, 11541, '怀安县', '怀安县', 5),
(11543, 19, 11541, '沽源县', '沽源县', 10),
(11544, 19, 11541, '宣化区', '宣化区', 15),
(11545, 19, 11541, '宣化县', '宣化县', 20),
(11546, 19, 11541, '康保县', '康保县', 25),
(11547, 19, 11541, '张北县', '张北县', 30),
(11548, 19, 11541, '阳原县', '阳原县', 35),
(11549, 19, 11541, '赤城县', '赤城县', 40),
(11550, 19, 11541, '崇礼县', '崇礼县', 45),
(11551, 19, 11541, '尚义县', '尚义县', 50),
(11552, 19, 11541, '蔚县', '蔚县', 55),
(11553, 19, 11541, '涿鹿县', '涿鹿县', 60),
(11554, 19, 11541, '万全县', '万全县', 65),
(11555, 19, 11541, '下花园区', '下花园区', 70),
(11556, 19, 11541, '桥西区', '桥西区', 75),
(11557, 19, 11541, '桥东区', '桥东区', 80),
(11558, 19, 11541, '怀来县', '怀来县', 85),
(11559, 19, 11450, '承德市', '承德市', 30),
(11560, 19, 11559, '双滦区', '双滦区', 5),
(11561, 19, 11559, '鹰手营子矿区', '鹰手营子矿区', 10),
(11562, 19, 11559, '隆化县', '隆化县', 15),
(11563, 19, 11559, '兴隆县', '兴隆县', 20),
(11564, 19, 11559, '平泉县', '平泉县', 25),
(11565, 19, 11559, '滦平县', '滦平县', 30),
(11566, 19, 11559, '丰宁县', '丰宁县', 35),
(11567, 19, 11559, '围场县', '围场县', 40),
(11568, 19, 11559, '宽城县', '宽城县', 45),
(11569, 19, 11559, '双桥区', '双桥区', 50),
(11570, 19, 11559, '承德县', '承德县', 55),
(11571, 19, 11450, '秦皇岛市', '秦皇岛市', 35),
(11572, 19, 11571, '卢龙县', '卢龙县', 5),
(11573, 19, 11571, '青龙县', '青龙县', 10),
(11574, 19, 11571, '昌黎县', '昌黎县', 15),
(11575, 19, 11571, '北戴河区', '北戴河区', 20),
(11576, 19, 11571, '海港区', '海港区', 25),
(11577, 19, 11571, '山海关区', '山海关区', 30),
(11578, 19, 11571, '抚宁县', '抚宁县', 35),
(11579, 19, 11450, '唐山市', '唐山市', 40),
(11580, 19, 11579, '路北区', '路北区', 5),
(11581, 19, 11579, '路南区', '路南区', 10),
(11582, 19, 11579, '迁安市', '迁安市', 15),
(11583, 19, 11579, '丰润区', '丰润区', 20),
(11584, 19, 11579, '古冶区', '古冶区', 25),
(11585, 19, 11579, '开平区', '开平区', 30),
(11586, 19, 11579, '遵化市', '遵化市', 35),
(11587, 19, 11579, '丰南区', '丰南区', 40),
(11588, 19, 11579, '迁西县', '迁西县', 45),
(11589, 19, 11579, '滦南县', '滦南县', 50),
(11590, 19, 11579, '玉田县', '玉田县', 55),
(11591, 19, 11579, '曹妃甸区', '曹妃甸区', 60),
(11592, 19, 11579, '乐亭县', '乐亭县', 65),
(11593, 19, 11579, '滦县', '滦县', 70),
(11594, 19, 11450, '沧州市', '沧州市', 45),
(11595, 19, 11594, '沧县', '沧县', 5),
(11596, 19, 11594, '泊头市', '泊头市', 10),
(11597, 19, 11594, '河间市', '河间市', 15),
(11598, 19, 11594, '献县', '献县', 20),
(11599, 19, 11594, '肃宁县', '肃宁县', 25),
(11600, 19, 11594, '青县', '青县', 30),
(11601, 19, 11594, '东光县', '东光县', 35),
(11602, 19, 11594, '吴桥县', '吴桥县', 40),
(11603, 19, 11594, '南皮县', '南皮县', 45),
(11604, 19, 11594, '盐山县', '盐山县', 50),
(11605, 19, 11594, '海兴县', '海兴县', 55),
(11606, 19, 11594, '孟村县', '孟村县', 60),
(11607, 19, 11594, '运河区', '运河区', 65),
(11608, 19, 11594, '新华区', '新华区', 70),
(11609, 19, 11594, '任丘市', '任丘市', 75),
(11610, 19, 11594, '黄骅市', '黄骅市', 80),
(11611, 19, 11450, '廊坊市', '廊坊市', 50),
(11612, 19, 11611, '三河市', '三河市', 5),
(11613, 19, 11611, '广阳区', '广阳区', 10),
(11614, 19, 11611, '开发区', '开发区', 15),
(11615, 19, 11611, '固安县', '固安县', 20),
(11616, 19, 11611, '安次区', '安次区', 25),
(11617, 19, 11611, '永清县', '永清县', 30),
(11618, 19, 11611, '香河县', '香河县', 35),
(11619, 19, 11611, '大城县', '大城县', 40),
(11620, 19, 11611, '文安县', '文安县', 45),
(11621, 19, 11611, '大厂县', '大厂县', 50),
(11622, 19, 11611, '霸州市', '霸州市', 55),
(11623, 19, 11450, '衡水市', '衡水市', 55),
(11624, 19, 11623, '冀州市', '冀州市', 5),
(11625, 19, 11623, '深州市', '深州市', 10),
(11626, 19, 11623, '饶阳县', '饶阳县', 15),
(11627, 19, 11623, '枣强县', '枣强县', 20),
(11628, 19, 11623, '桃城区', '桃城区', 25),
(11629, 19, 11623, '故城县', '故城县', 30),
(11630, 19, 11623, '阜城县', '阜城县', 35),
(11631, 19, 11623, '安平县', '安平县', 40),
(11632, 19, 11623, '武邑县', '武邑县', 45),
(11633, 19, 11623, '景县', '景县', 50),
(11634, 19, 11623, '武强县', '武强县', 55),
(11635, 19, 0, '山西省', '山西省', 30),
(11636, 19, 11635, '太原市', '太原市', 5),
(11637, 19, 11636, '小店区', '小店区', 5),
(11638, 19, 11636, '迎泽区', '迎泽区', 10),
(11639, 19, 11636, '晋源区', '晋源区', 15),
(11640, 19, 11636, '万柏林区', '万柏林区', 20),
(11641, 19, 11636, '尖草坪区', '尖草坪区', 25),
(11642, 19, 11636, '杏花岭区', '杏花岭区', 30),
(11643, 19, 11636, '古交市', '古交市', 35),
(11644, 19, 11636, '阳曲县', '阳曲县', 40),
(11645, 19, 11636, '娄烦县', '娄烦县', 45),
(11646, 19, 11636, '清徐县', '清徐县', 50),
(11647, 19, 11635, '大同市', '大同市', 10),
(11648, 19, 11647, '大同县', '大同县', 5),
(11649, 19, 11647, '天镇县', '天镇县', 10),
(11650, 19, 11647, '灵丘县', '灵丘县', 15),
(11651, 19, 11647, '阳高县', '阳高县', 20),
(11652, 19, 11647, '左云县', '左云县', 25),
(11653, 19, 11647, '浑源县', '浑源县', 30),
(11654, 19, 11647, '广灵县', '广灵县', 35),
(11655, 19, 11647, '城区', '城区', 40),
(11656, 19, 11647, '新荣区', '新荣区', 45),
(11657, 19, 11647, '南郊区', '南郊区', 50),
(11658, 19, 11647, '矿区', '矿区', 55),
(11659, 19, 11635, '阳泉市', '阳泉市', 15),
(11660, 19, 11659, '盂县', '盂县', 5),
(11661, 19, 11659, '平定县', '平定县', 10),
(11662, 19, 11659, '郊区', '郊区', 15),
(11663, 19, 11659, '城区', '城区', 20),
(11664, 19, 11659, '矿区', '矿区', 25),
(11665, 19, 11635, '晋城市', '晋城市', 20),
(11666, 19, 11665, '城区', '城区', 5),
(11667, 19, 11665, '高平市', '高平市', 10),
(11668, 19, 11665, '阳城县', '阳城县', 15),
(11669, 19, 11665, '沁水县', '沁水县', 20),
(11670, 19, 11665, '陵川县', '陵川县', 25),
(11671, 19, 11665, '泽州县', '泽州县', 30),
(11672, 19, 11635, '朔州市', '朔州市', 25),
(11673, 19, 11672, '平鲁区', '平鲁区', 5),
(11674, 19, 11672, '山阴县', '山阴县', 10),
(11675, 19, 11672, '右玉县', '右玉县', 15),
(11676, 19, 11672, '应县', '应县', 20),
(11677, 19, 11672, '怀仁县', '怀仁县', 25),
(11678, 19, 11672, '朔城区', '朔城区', 30),
(11679, 19, 11635, '晋中市', '晋中市', 30),
(11680, 19, 11679, '介休市', '介休市', 5),
(11681, 19, 11679, '昔阳县', '昔阳县', 10),
(11682, 19, 11679, '祁县', '祁县', 15),
(11683, 19, 11679, '左权县', '左权县', 20),
(11684, 19, 11679, '寿阳县', '寿阳县', 25),
(11685, 19, 11679, '太谷县', '太谷县', 30),
(11686, 19, 11679, '和顺县', '和顺县', 35),
(11687, 19, 11679, '灵石县', '灵石县', 40),
(11688, 19, 11679, '平遥县', '平遥县', 45),
(11689, 19, 11679, '榆社县', '榆社县', 50),
(11690, 19, 11679, '榆次区', '榆次区', 55),
(11691, 19, 11635, '忻州市', '忻州市', 35),
(11692, 19, 11691, '原平市', '原平市', 5),
(11693, 19, 11691, '代县', '代县', 10),
(11694, 19, 11691, '神池县', '神池县', 15),
(11695, 19, 11691, '五寨县', '五寨县', 20),
(11696, 19, 11691, '五台县', '五台县', 25),
(11697, 19, 11691, '偏关县', '偏关县', 30),
(11698, 19, 11691, '宁武县', '宁武县', 35),
(11699, 19, 11691, '静乐县', '静乐县', 40),
(11700, 19, 11691, '繁峙县', '繁峙县', 45),
(11701, 19, 11691, '河曲县', '河曲县', 50),
(11702, 19, 11691, '保德县', '保德县', 55),
(11703, 19, 11691, '定襄县', '定襄县', 60),
(11704, 19, 11691, '忻府区', '忻府区', 65),
(11705, 19, 11691, '岢岚县', '岢岚县', 70),
(11706, 19, 11635, '吕梁市', '吕梁市', 40),
(11707, 19, 11706, '离石区', '离石区', 5),
(11708, 19, 11706, '孝义市', '孝义市', 10),
(11709, 19, 11706, '汾阳市', '汾阳市', 15),
(11710, 19, 11706, '文水县', '文水县', 20),
(11711, 19, 11706, '中阳县', '中阳县', 25),
(11712, 19, 11706, '兴县', '兴县', 30),
(11713, 19, 11706, '临县', '临县', 35),
(11714, 19, 11706, '方山县', '方山县', 40),
(11715, 19, 11706, '柳林县', '柳林县', 45),
(11716, 19, 11706, '岚县', '岚县', 50),
(11717, 19, 11706, '交口县', '交口县', 55),
(11718, 19, 11706, '交城县', '交城县', 60),
(11719, 19, 11706, '石楼县', '石楼县', 65),
(11720, 19, 11635, '临汾市', '临汾市', 45),
(11721, 19, 11720, '曲沃县', '曲沃县', 5),
(11722, 19, 11720, '侯马市', '侯马市', 10),
(11723, 19, 11720, '霍州市', '霍州市', 15),
(11724, 19, 11720, '汾西县', '汾西县', 20),
(11725, 19, 11720, '吉县', '吉县', 25),
(11726, 19, 11720, '安泽县', '安泽县', 30),
(11727, 19, 11720, '浮山县', '浮山县', 35),
(11728, 19, 11720, '大宁县', '大宁县', 40),
(11729, 19, 11720, '古县', '古县', 45),
(11730, 19, 11720, '隰县', '隰县', 50),
(11731, 19, 11720, '襄汾县', '襄汾县', 55),
(11732, 19, 11720, '翼城县', '翼城县', 60),
(11733, 19, 11720, '永和县', '永和县', 65),
(11734, 19, 11720, '乡宁县', '乡宁县', 70),
(11735, 19, 11720, '洪洞县', '洪洞县', 75),
(11736, 19, 11720, '蒲县', '蒲县', 80),
(11737, 19, 11720, '尧都区', '尧都区', 85),
(11738, 19, 11635, '运城市', '运城市', 50),
(11739, 19, 11738, '盐湖区', '盐湖区', 5),
(11740, 19, 11738, '河津市', '河津市', 10),
(11741, 19, 11738, '永济市', '永济市', 15),
(11742, 19, 11738, '新绛县', '新绛县', 20),
(11743, 19, 11738, '平陆县', '平陆县', 25),
(11744, 19, 11738, '垣曲县', '垣曲县', 30),
(11745, 19, 11738, '绛县', '绛县', 35),
(11746, 19, 11738, '稷山县', '稷山县', 40),
(11747, 19, 11738, '芮城县', '芮城县', 45),
(11748, 19, 11738, '夏县', '夏县', 50),
(11749, 19, 11738, '临猗县', '临猗县', 55),
(11750, 19, 11738, '万荣县', '万荣县', 60),
(11751, 19, 11738, '闻喜县', '闻喜县', 65),
(11752, 19, 11635, '长治市', '长治市', 55),
(11753, 19, 11752, '长治县', '长治县', 5),
(11754, 19, 11752, '潞城市', '潞城市', 10),
(11755, 19, 11752, '郊区', '郊区', 15),
(11756, 19, 11752, '襄垣县', '襄垣县', 20),
(11757, 19, 11752, '屯留县', '屯留县', 25),
(11758, 19, 11752, '平顺县', '平顺县', 30),
(11759, 19, 11752, '黎城县', '黎城县', 35),
(11760, 19, 11752, '壶关县', '壶关县', 40),
(11761, 19, 11752, '长子县', '长子县', 45),
(11762, 19, 11752, '武乡县', '武乡县', 50),
(11763, 19, 11752, '沁县', '沁县', 55),
(11764, 19, 11752, '沁源县', '沁源县', 60),
(11765, 19, 11752, '城区', '城区', 65),
(11766, 19, 0, '河南省', '河南省', 35),
(11767, 19, 11766, '郑州市', '郑州市', 5),
(11768, 19, 11767, '二七区', '二七区', 5),
(11769, 19, 11767, '中原区', '中原区', 10),
(11770, 19, 11767, '郑东新区', '郑东新区', 15),
(11771, 19, 11767, '管城区', '管城区', 20),
(11772, 19, 11767, '金水区', '金水区', 25),
(11773, 19, 11767, '经济开发区', '经济开发区', 30),
(11774, 19, 11767, '高新技术开发区', '高新技术开发区', 35),
(11775, 19, 11767, '新郑市', '新郑市', 40),
(11776, 19, 11767, '巩义市', '巩义市', 45),
(11777, 19, 11767, '荥阳市', '荥阳市', 50),
(11778, 19, 11767, '中牟县', '中牟县', 55),
(11779, 19, 11767, '新密市', '新密市', 60),
(11780, 19, 11767, '登封市', '登封市', 65),
(11781, 19, 11767, '惠济区', '惠济区', 70),
(11782, 19, 11767, '上街区', '上街区', 75),
(11783, 19, 11766, '开封市', '开封市', 10),
(11784, 19, 11783, '金明区', '金明区', 5),
(11785, 19, 11783, '龙亭区', '龙亭区', 10),
(11786, 19, 11783, '顺河区', '顺河区', 15),
(11787, 19, 11783, '鼓楼区', '鼓楼区', 20),
(11788, 19, 11783, '禹王台区', '禹王台区', 25),
(11789, 19, 11783, '通许县', '通许县', 30),
(11790, 19, 11783, '开封县', '开封县', 35),
(11791, 19, 11783, '杞县', '杞县', 40),
(11792, 19, 11783, '兰考县', '兰考县', 45),
(11793, 19, 11783, '尉氏县', '尉氏县', 50),
(11794, 19, 11766, '洛阳市', '洛阳市', 15),
(11795, 19, 11794, '涧西区', '涧西区', 5),
(11796, 19, 11794, '西工区', '西工区', 10),
(11797, 19, 11794, '洛龙区', '洛龙区', 15),
(11798, 19, 11794, '嵩县', '嵩县', 20),
(11799, 19, 11794, '偃师市', '偃师市', 25),
(11800, 19, 11794, '孟津县', '孟津县', 30),
(11801, 19, 11794, '汝阳县', '汝阳县', 35),
(11802, 19, 11794, '伊川县', '伊川县', 40),
(11803, 19, 11794, '洛宁县', '洛宁县', 45),
(11804, 19, 11794, '宜阳县', '宜阳县', 50),
(11805, 19, 11794, '栾川县', '栾川县', 55),
(11806, 19, 11794, '新安县', '新安县', 60),
(11807, 19, 11794, '伊滨区', '伊滨区', 65),
(11808, 19, 11794, '吉利区', '吉利区', 70),
(11809, 19, 11794, '瀍河区', '瀍河区', 75),
(11810, 19, 11794, '老城区', '老城区', 80),
(11811, 19, 11766, '平顶山市', '平顶山市', 20),
(11812, 19, 11811, '湛河区', '湛河区', 5),
(11813, 19, 11811, '卫东区', '卫东区', 10),
(11814, 19, 11811, '新华区', '新华区', 15),
(11815, 19, 11811, '汝州市', '汝州市', 20),
(11816, 19, 11811, '舞钢市', '舞钢市', 25),
(11817, 19, 11811, '郏县', '郏县', 30),
(11818, 19, 11811, '叶县', '叶县', 35),
(11819, 19, 11811, '鲁山县', '鲁山县', 40),
(11820, 19, 11811, '宝丰县', '宝丰县', 45),
(11821, 19, 11811, '石龙区', '石龙区', 50),
(11822, 19, 11766, '焦作市', '焦作市', 25),
(11823, 19, 11822, '沁阳市', '沁阳市', 5),
(11824, 19, 11822, '孟州市', '孟州市', 10),
(11825, 19, 11822, '修武县', '修武县', 15),
(11826, 19, 11822, '温县', '温县', 20),
(11827, 19, 11822, '武陟县', '武陟县', 25),
(11828, 19, 11822, '博爱县', '博爱县', 30),
(11829, 19, 11822, '山阳区', '山阳区', 35),
(11830, 19, 11822, '解放区', '解放区', 40),
(11831, 19, 11822, '马村区', '马村区', 45),
(11832, 19, 11822, '中站区', '中站区', 50),
(11833, 19, 11766, '鹤壁市', '鹤壁市', 30),
(11834, 19, 11833, '淇滨区', '淇滨区', 5),
(11835, 19, 11833, '浚县', '浚县', 10),
(11836, 19, 11833, '淇县', '淇县', 15),
(11837, 19, 11833, '鹤山区', '鹤山区', 20),
(11838, 19, 11833, '山城区', '山城区', 25),
(11839, 19, 11766, '新乡市', '新乡市', 35),
(11840, 19, 11839, '牧野区', '牧野区', 5),
(11841, 19, 11839, '红旗区', '红旗区', 10),
(11842, 19, 11839, '卫滨区', '卫滨区', 15),
(11843, 19, 11839, '卫辉市', '卫辉市', 20),
(11844, 19, 11839, '辉县市', '辉县市', 25),
(11845, 19, 11839, '新乡县', '新乡县', 30),
(11846, 19, 11839, '获嘉县', '获嘉县', 35),
(11847, 19, 11839, '原阳县', '原阳县', 40),
(11848, 19, 11839, '长垣县', '长垣县', 45),
(11849, 19, 11839, '延津县', '延津县', 50),
(11850, 19, 11839, '封丘县', '封丘县', 55),
(11851, 19, 11839, '凤泉区', '凤泉区', 60),
(11852, 19, 11766, '安阳市', '安阳市', 40),
(11853, 19, 11852, '龙安区', '龙安区', 5),
(11854, 19, 11852, '殷都区', '殷都区', 10),
(11855, 19, 11852, '文峰区', '文峰区', 15),
(11856, 19, 11852, '开发区', '开发区', 20),
(11857, 19, 11852, '北关区', '北关区', 25),
(11858, 19, 11852, '林州市', '林州市', 30),
(11859, 19, 11852, '安阳县', '安阳县', 35),
(11860, 19, 11852, '滑县', '滑县', 40),
(11861, 19, 11852, '汤阴县', '汤阴县', 45),
(11862, 19, 11852, '内黄县', '内黄县', 50),
(11863, 19, 11766, '濮阳市', '濮阳市', 45),
(11864, 19, 11863, '濮阳县', '濮阳县', 5),
(11865, 19, 11863, '南乐县', '南乐县', 10),
(11866, 19, 11863, '台前县', '台前县', 15),
(11867, 19, 11863, '清丰县', '清丰县', 20),
(11868, 19, 11863, '范县', '范县', 25),
(11869, 19, 11863, '华龙区', '华龙区', 30),
(11870, 19, 11766, '许昌市', '许昌市', 50),
(11871, 19, 11870, '魏都区', '魏都区', 5),
(11872, 19, 11870, '禹州市', '禹州市', 10),
(11873, 19, 11870, '长葛市', '长葛市', 15),
(11874, 19, 11870, '许昌县', '许昌县', 20),
(11875, 19, 11870, '鄢陵县', '鄢陵县', 25),
(11876, 19, 11870, '襄城县', '襄城县', 30),
(11877, 19, 11766, '漯河市', '漯河市', 55),
(11878, 19, 11877, '郾城区', '郾城区', 5),
(11879, 19, 11877, '临颍县', '临颍县', 10),
(11880, 19, 11877, '召陵区', '召陵区', 15),
(11881, 19, 11877, '舞阳县', '舞阳县', 20),
(11882, 19, 11877, '源汇区', '源汇区', 25),
(11883, 19, 11766, '三门峡市', '三门峡市', 60),
(11884, 19, 11883, '渑池县', '渑池县', 5),
(11885, 19, 11883, '湖滨区', '湖滨区', 10),
(11886, 19, 11883, '义马市', '义马市', 15),
(11887, 19, 11883, '灵宝市', '灵宝市', 20),
(11888, 19, 11883, '陕县', '陕县', 25),
(11889, 19, 11883, '卢氏县', '卢氏县', 30),
(11890, 19, 11766, '南阳市', '南阳市', 65),
(11891, 19, 11890, '社旗县', '社旗县', 5),
(11892, 19, 11890, '西峡县', '西峡县', 10),
(11893, 19, 11890, '卧龙区', '卧龙区', 15),
(11894, 19, 11890, '宛城区', '宛城区', 20),
(11895, 19, 11890, '邓州市', '邓州市', 25),
(11896, 19, 11890, '桐柏县', '桐柏县', 30),
(11897, 19, 11890, '方城县', '方城县', 35),
(11898, 19, 11890, '淅川县', '淅川县', 40),
(11899, 19, 11890, '镇平县', '镇平县', 45),
(11900, 19, 11890, '唐河县', '唐河县', 50),
(11901, 19, 11890, '南召县', '南召县', 55),
(11902, 19, 11890, '内乡县', '内乡县', 60),
(11903, 19, 11890, '新野县', '新野县', 65),
(11904, 19, 11766, '商丘市', '商丘市', 70),
(11905, 19, 11904, '永城市', '永城市', 5),
(11906, 19, 11904, '宁陵县', '宁陵县', 10),
(11907, 19, 11904, '虞城县', '虞城县', 15),
(11908, 19, 11904, '民权县', '民权县', 20),
(11909, 19, 11904, '夏邑县', '夏邑县', 25),
(11910, 19, 11904, '柘城县', '柘城县', 30),
(11911, 19, 11904, '睢县', '睢县', 35),
(11912, 19, 11904, '睢阳区', '睢阳区', 40),
(11913, 19, 11904, '梁园区', '梁园区', 45),
(11914, 19, 11766, '周口市', '周口市', 75),
(11915, 19, 11914, '项城市', '项城市', 5),
(11916, 19, 11914, '商水县', '商水县', 10),
(11917, 19, 11914, '淮阳县', '淮阳县', 15),
(11918, 19, 11914, '太康县', '太康县', 20),
(11919, 19, 11914, '鹿邑县', '鹿邑县', 25),
(11920, 19, 11914, '西华县', '西华县', 30),
(11921, 19, 11914, '扶沟县', '扶沟县', 35),
(11922, 19, 11914, '沈丘县', '沈丘县', 40),
(11923, 19, 11914, '郸城县', '郸城县', 45),
(11924, 19, 11914, '川汇区', '川汇区', 50),
(11925, 19, 11914, '东新区', '东新区', 55),
(11926, 19, 11914, '经济开发区', '经济开发区', 60),
(11927, 19, 11766, '驻马店市', '驻马店市', 80),
(11928, 19, 11927, '确山县', '确山县', 5),
(11929, 19, 11927, '新蔡县', '新蔡县', 10),
(11930, 19, 11927, '上蔡县', '上蔡县', 15),
(11931, 19, 11927, '泌阳县', '泌阳县', 20),
(11932, 19, 11927, '西平县', '西平县', 25),
(11933, 19, 11927, '遂平县', '遂平县', 30),
(11934, 19, 11927, '汝南县', '汝南县', 35),
(11935, 19, 11927, '平舆县', '平舆县', 40),
(11936, 19, 11927, '正阳县', '正阳县', 45),
(11937, 19, 11927, '驿城区', '驿城区', 50),
(11938, 19, 11766, '信阳市', '信阳市', 85),
(11939, 19, 11938, '潢川县', '潢川县', 5),
(11940, 19, 11938, '淮滨县', '淮滨县', 10),
(11941, 19, 11938, '息县', '息县', 15),
(11942, 19, 11938, '新县', '新县', 20),
(11943, 19, 11938, '固始县', '固始县', 25),
(11944, 19, 11938, '罗山县', '罗山县', 30),
(11945, 19, 11938, '光山县', '光山县', 35),
(11946, 19, 11938, '商城县', '商城县', 40),
(11947, 19, 11938, '平桥区', '平桥区', 45),
(11948, 19, 11938, '浉河区', '浉河区', 50),
(11949, 19, 11766, '济源市', '济源市', 90),
(11950, 19, 11949, '城区', '城区', 5),
(11951, 19, 11949, '五龙口镇', '五龙口镇', 10),
(11952, 19, 11949, '下冶镇', '下冶镇', 15),
(11953, 19, 11949, '轵城镇', '轵城镇', 20),
(11954, 19, 11949, '王屋镇', '王屋镇', 25),
(11955, 19, 11949, '思礼镇', '思礼镇', 30),
(11956, 19, 11949, '邵原镇', '邵原镇', 35),
(11957, 19, 11949, '坡头镇', '坡头镇', 40),
(11958, 19, 11949, '梨林镇', '梨林镇', 45),
(11959, 19, 11949, '克井镇', '克井镇', 50),
(11960, 19, 11949, '大峪镇', '大峪镇', 55),
(11961, 19, 11949, '承留镇', '承留镇', 60),
(11962, 19, 0, '辽宁省', '辽宁省', 40),
(11963, 19, 11962, '沈阳市', '沈阳市', 5),
(11964, 19, 11963, '苏家屯区', '苏家屯区', 5),
(11965, 19, 11963, '新民市', '新民市', 10),
(11966, 19, 11963, '法库县', '法库县', 15),
(11967, 19, 11963, '辽中县', '辽中县', 20),
(11968, 19, 11963, '康平县', '康平县', 25),
(11969, 19, 11963, '皇姑区', '皇姑区', 30),
(11970, 19, 11963, '铁西区', '铁西区', 35),
(11971, 19, 11963, '大东区', '大东区', 40),
(11972, 19, 11963, '沈河区', '沈河区', 45),
(11973, 19, 11963, '东陵区', '东陵区', 50),
(11974, 19, 11963, '于洪区', '于洪区', 55),
(11975, 19, 11963, '和平区', '和平区', 60),
(11976, 19, 11963, '浑南新区', '浑南新区', 65),
(11977, 19, 11963, '沈北新区', '沈北新区', 70),
(11978, 19, 11962, '大连市', '大连市', 10),
(11979, 19, 11978, '中山区', '中山区', 5),
(11980, 19, 11978, '沙河口区', '沙河口区', 10),
(11981, 19, 11978, '西岗区', '西岗区', 15),
(11982, 19, 11978, '甘井子区', '甘井子区', 20),
(11983, 19, 11978, '高新园区', '高新园区', 25),
(11984, 19, 11978, '大连开发区', '大连开发区', 30),
(11985, 19, 11978, '金州区', '金州区', 35),
(11986, 19, 11978, '旅顺口区', '旅顺口区', 40),
(11987, 19, 11978, '普兰店市', '普兰店市', 45),
(11988, 19, 11978, '瓦房店市', '瓦房店市', 50),
(11989, 19, 11978, '庄河市', '庄河市', 55),
(11990, 19, 11978, '长海县', '长海县', 60),
(11991, 19, 11962, '鞍山市', '鞍山市', 15),
(11992, 19, 11991, '铁东区', '铁东区', 5),
(11993, 19, 11991, '立山区', '立山区', 10),
(11994, 19, 11991, '台安县', '台安县', 15),
(11995, 19, 11991, '海城市', '海城市', 20),
(11996, 19, 11991, '岫岩县', '岫岩县', 25),
(11997, 19, 11991, '铁西区', '铁西区', 30),
(11998, 19, 11991, '千山区', '千山区', 35),
(11999, 19, 11962, '抚顺市', '抚顺市', 20),
(12000, 19, 11999, '望花区', '望花区', 5),
(12001, 19, 11999, '东洲区', '东洲区', 10),
(12002, 19, 11999, '新抚区', '新抚区', 15),
(12003, 19, 11999, '顺城区', '顺城区', 20),
(12004, 19, 11999, '抚顺县', '抚顺县', 25),
(12005, 19, 11999, '新宾县', '新宾县', 30),
(12006, 19, 11999, '清原县', '清原县', 35),
(12007, 19, 11962, '本溪市', '本溪市', 25),
(12008, 19, 12007, '桓仁县', '桓仁县', 5),
(12009, 19, 12007, '本溪县', '本溪县', 10),
(12010, 19, 12007, '平山区', '平山区', 15),
(12011, 19, 12007, '溪湖区', '溪湖区', 20),
(12012, 19, 12007, '明山区', '明山区', 25),
(12013, 19, 12007, '南芬区', '南芬区', 30),
(12014, 19, 11962, '丹东市', '丹东市', 30),
(12015, 19, 12014, '元宝区', '元宝区', 5),
(12016, 19, 12014, '振兴区', '振兴区', 10),
(12017, 19, 12014, '振安区', '振安区', 15),
(12018, 19, 12014, '东港市', '东港市', 20),
(12019, 19, 12014, '凤城市', '凤城市', 25),
(12020, 19, 12014, '宽甸县', '宽甸县', 30),
(12021, 19, 11962, '锦州市', '锦州市', 35),
(12022, 19, 12021, '凌河区', '凌河区', 5),
(12023, 19, 12021, '古塔区', '古塔区', 10),
(12024, 19, 12021, '太和区', '太和区', 15),
(12025, 19, 12021, '义县', '义县', 20),
(12026, 19, 12021, '凌海市', '凌海市', 25),
(12027, 19, 12021, '北镇市', '北镇市', 30),
(12028, 19, 12021, '黑山县', '黑山县', 35),
(12029, 19, 12021, '经济技术开发区', '经济技术开发区', 40),
(12030, 19, 11962, '葫芦岛市', '葫芦岛市', 40),
(12031, 19, 12030, '龙港区', '龙港区', 5),
(12032, 19, 12030, '连山区', '连山区', 10),
(12033, 19, 12030, '兴城市', '兴城市', 15),
(12034, 19, 12030, '绥中县', '绥中县', 20),
(12035, 19, 12030, '建昌县', '建昌县', 25),
(12036, 19, 12030, '南票区', '南票区', 30),
(12037, 19, 11962, '营口市', '营口市', 45),
(12038, 19, 12037, '西市区', '西市区', 5),
(12039, 19, 12037, '站前区', '站前区', 10),
(12040, 19, 12037, '大石桥市', '大石桥市', 15),
(12041, 19, 12037, '盖州市', '盖州市', 20),
(12042, 19, 12037, '老边区', '老边区', 25),
(12043, 19, 12037, '鲅鱼圈区', '鲅鱼圈区', 30),
(12044, 19, 11962, '盘锦市', '盘锦市', 50),
(12045, 19, 12044, '盘山县', '盘山县', 5),
(12046, 19, 12044, '大洼县', '大洼县', 10),
(12047, 19, 12044, '兴隆台区', '兴隆台区', 15),
(12048, 19, 12044, '双台子区', '双台子区', 20),
(12049, 19, 11962, '阜新市', '阜新市', 55),
(12050, 19, 12049, '阜新县', '阜新县', 5),
(12051, 19, 12049, '彰武县', '彰武县', 10),
(12052, 19, 12049, '海州区', '海州区', 15),
(12053, 19, 12049, '太平区', '太平区', 20),
(12054, 19, 12049, '细河区', '细河区', 25),
(12055, 19, 12049, '清河门区', '清河门区', 30),
(12056, 19, 12049, '新邱区', '新邱区', 35),
(12057, 19, 11962, '辽阳市', '辽阳市', 60),
(12058, 19, 12057, '辽阳县', '辽阳县', 5),
(12059, 19, 12057, '白塔区', '白塔区', 10),
(12060, 19, 12057, '文圣区', '文圣区', 15),
(12061, 19, 12057, '灯塔市', '灯塔市', 20),
(12062, 19, 12057, '太子河区', '太子河区', 25),
(12063, 19, 12057, '弓长岭区', '弓长岭区', 30),
(12064, 19, 12057, '宏伟区', '宏伟区', 35),
(12065, 19, 11962, '朝阳市', '朝阳市', 65),
(12066, 19, 12065, '凌源市', '凌源市', 5),
(12067, 19, 12065, '北票市', '北票市', 10),
(12068, 19, 12065, '喀喇沁左翼县', '喀喇沁左翼县', 15),
(12069, 19, 12065, '朝阳县', '朝阳县', 20),
(12070, 19, 12065, '双塔区', '双塔区', 25),
(12071, 19, 12065, '建平县', '建平县', 30),
(12072, 19, 12065, '龙城区', '龙城区', 35),
(12073, 19, 11962, '铁岭市', '铁岭市', 70),
(12074, 19, 12073, '银州区', '银州区', 5),
(12075, 19, 12073, '清河区', '清河区', 10),
(12076, 19, 12073, '开原市', '开原市', 15),
(12077, 19, 12073, '铁岭县', '铁岭县', 20),
(12078, 19, 12073, '西丰县', '西丰县', 25),
(12079, 19, 12073, '昌图县', '昌图县', 30),
(12080, 19, 12073, '调兵山市', '调兵山市', 35),
(12081, 19, 0, '吉林省', '吉林省', 45),
(12082, 19, 12081, '长春市', '长春市', 5),
(12083, 19, 12082, '德惠市', '德惠市', 5),
(12084, 19, 12082, '榆树市', '榆树市', 10),
(12085, 19, 12082, '九台市', '九台市', 15),
(12086, 19, 12082, '农安县', '农安县', 20),
(12087, 19, 12082, '朝阳区', '朝阳区', 25),
(12088, 19, 12082, '南关区', '南关区', 30),
(12089, 19, 12082, '宽城区', '宽城区', 35),
(12090, 19, 12082, '二道区', '二道区', 40),
(12091, 19, 12082, '双阳区', '双阳区', 45),
(12092, 19, 12082, '绿园区', '绿园区', 50),
(12093, 19, 12082, '净月区', '净月区', 55),
(12094, 19, 12082, '汽车产业开发区', '汽车产业开发区', 60),
(12095, 19, 12082, '高新技术开发区', '高新技术开发区', 65),
(12096, 19, 12082, '经济技术开发区', '经济技术开发区', 70),
(12097, 19, 12081, '吉林市', '吉林市', 10),
(12098, 19, 12097, '昌邑区', '昌邑区', 5),
(12099, 19, 12097, '龙潭区', '龙潭区', 10),
(12100, 19, 12097, '船营区', '船营区', 15),
(12101, 19, 12097, '丰满区', '丰满区', 20),
(12102, 19, 12097, '舒兰市', '舒兰市', 25),
(12103, 19, 12097, '桦甸市', '桦甸市', 30),
(12104, 19, 12097, '蛟河市', '蛟河市', 35),
(12105, 19, 12097, '磐石市', '磐石市', 40),
(12106, 19, 12097, '永吉县', '永吉县', 45),
(12107, 19, 12081, '四平市', '四平市', 15),
(12108, 19, 12107, '铁东区', '铁东区', 5),
(12109, 19, 12107, '铁西区', '铁西区', 10),
(12110, 19, 12107, '公主岭市', '公主岭市', 15),
(12111, 19, 12107, '双辽市', '双辽市', 20),
(12112, 19, 12107, '梨树县', '梨树县', 25),
(12113, 19, 12107, '伊通县', '伊通县', 30),
(12114, 19, 12081, '通化市', '通化市', 20),
(12115, 19, 12114, '东昌区', '东昌区', 5),
(12116, 19, 12114, '梅河口市', '梅河口市', 10),
(12117, 19, 12114, '集安市', '集安市', 15),
(12118, 19, 12114, '通化县', '通化县', 20),
(12119, 19, 12114, '辉南县', '辉南县', 25),
(12120, 19, 12114, '柳河县', '柳河县', 30),
(12121, 19, 12114, '二道江区', '二道江区', 35),
(12122, 19, 12081, '白山市', '白山市', 25),
(12123, 19, 12122, '浑江区', '浑江区', 5),
(12124, 19, 12122, '临江市', '临江市', 10),
(12125, 19, 12122, '江源区', '江源区', 15),
(12126, 19, 12122, '靖宇县', '靖宇县', 20),
(12127, 19, 12122, '抚松县', '抚松县', 25),
(12128, 19, 12122, '长白县', '长白县', 30),
(12129, 19, 12081, '松原市', '松原市', 30),
(12130, 19, 12129, '宁江区', '宁江区', 5),
(12131, 19, 12129, '前郭县', '前郭县', 10),
(12132, 19, 12129, '乾安县', '乾安县', 15),
(12133, 19, 12129, '长岭县', '长岭县', 20),
(12134, 19, 12129, '扶余县', '扶余县', 25),
(12135, 19, 12081, '白城市', '白城市', 35),
(12136, 19, 12135, '大安市', '大安市', 5),
(12137, 19, 12135, '洮南市', '洮南市', 10),
(12138, 19, 12135, '通榆县', '通榆县', 15),
(12139, 19, 12135, '镇赉县', '镇赉县', 20),
(12140, 19, 12135, '洮北区', '洮北区', 25),
(12141, 19, 12081, '延边州', '延边州', 40),
(12142, 19, 12141, '延吉市', '延吉市', 5),
(12143, 19, 12141, '图们市', '图们市', 10),
(12144, 19, 12141, '敦化市', '敦化市', 15),
(12145, 19, 12141, '珲春市', '珲春市', 20),
(12146, 19, 12141, '龙井市', '龙井市', 25),
(12147, 19, 12141, '和龙市', '和龙市', 30),
(12148, 19, 12141, '汪清县', '汪清县', 35),
(12149, 19, 12141, '安图县', '安图县', 40),
(12150, 19, 12081, '辽源市', '辽源市', 45),
(12151, 19, 12150, '龙山区', '龙山区', 5),
(12152, 19, 12150, '西安区', '西安区', 10),
(12153, 19, 12150, '东丰县', '东丰县', 15),
(12154, 19, 12150, '东辽县', '东辽县', 20),
(12155, 19, 0, '黑龙江省', '黑龙江省', 50),
(12156, 19, 12155, '哈尔滨市', '哈尔滨市', 5),
(12157, 19, 12156, '阿城区', '阿城区', 5),
(12158, 19, 12156, '尚志市', '尚志市', 10),
(12159, 19, 12156, '双城市', '双城市', 15),
(12160, 19, 12156, '五常市', '五常市', 20),
(12161, 19, 12156, '方正县', '方正县', 25),
(12162, 19, 12156, '宾县', '宾县', 30),
(12163, 19, 12156, '依兰县', '依兰县', 35),
(12164, 19, 12156, '巴彦县', '巴彦县', 40),
(12165, 19, 12156, '通河县', '通河县', 45),
(12166, 19, 12156, '木兰县', '木兰县', 50),
(12167, 19, 12156, '延寿县', '延寿县', 55),
(12168, 19, 12156, '呼兰区', '呼兰区', 60),
(12169, 19, 12156, '松北区', '松北区', 65),
(12170, 19, 12156, '道里区', '道里区', 70),
(12171, 19, 12156, '南岗区', '南岗区', 75),
(12172, 19, 12156, '道外区', '道外区', 80),
(12173, 19, 12156, '香坊区', '香坊区', 85),
(12174, 19, 12156, '平房区', '平房区', 90),
(12175, 19, 12155, '齐齐哈尔市', '齐齐哈尔市', 10),
(12176, 19, 12175, '建华区', '建华区', 5),
(12177, 19, 12175, '龙沙区', '龙沙区', 10),
(12178, 19, 12175, '铁锋区', '铁锋区', 15),
(12179, 19, 12175, '梅里斯区', '梅里斯区', 20),
(12180, 19, 12175, '昂昂溪区', '昂昂溪区', 25),
(12181, 19, 12175, '富拉尔基区', '富拉尔基区', 30),
(12182, 19, 12175, '碾子山区', '碾子山区', 35),
(12183, 19, 12175, '讷河市', '讷河市', 40),
(12184, 19, 12175, '富裕县', '富裕县', 45),
(12185, 19, 12175, '拜泉县', '拜泉县', 50),
(12186, 19, 12175, '甘南县', '甘南县', 55),
(12187, 19, 12175, '依安县', '依安县', 60),
(12188, 19, 12175, '克山县', '克山县', 65),
(12189, 19, 12175, '龙江县', '龙江县', 70),
(12190, 19, 12175, '克东县', '克东县', 75),
(12191, 19, 12175, '泰来县', '泰来县', 80),
(12192, 19, 12155, '鹤岗市', '鹤岗市', 15),
(12193, 19, 12192, '兴山区', '兴山区', 5),
(12194, 19, 12192, '向阳区', '向阳区', 10),
(12195, 19, 12192, '工农区', '工农区', 15),
(12196, 19, 12192, '南山区', '南山区', 20),
(12197, 19, 12192, '兴安区', '兴安区', 25),
(12198, 19, 12192, '东山区', '东山区', 30),
(12199, 19, 12192, '萝北县', '萝北县', 35),
(12200, 19, 12192, '绥滨县', '绥滨县', 40),
(12201, 19, 12155, '双鸭山市', '双鸭山市', 20),
(12202, 19, 12201, '尖山区', '尖山区', 5),
(12203, 19, 12201, '岭东区', '岭东区', 10),
(12204, 19, 12201, '四方台区', '四方台区', 15),
(12205, 19, 12201, '宝山区', '宝山区', 20),
(12206, 19, 12201, '集贤县', '集贤县', 25),
(12207, 19, 12201, '宝清县', '宝清县', 30),
(12208, 19, 12201, '友谊县', '友谊县', 35),
(12209, 19, 12201, '饶河县', '饶河县', 40),
(12210, 19, 12155, '鸡西市', '鸡西市', 25),
(12211, 19, 12210, '恒山区', '恒山区', 5),
(12212, 19, 12210, '滴道区', '滴道区', 10),
(12213, 19, 12210, '梨树区', '梨树区', 15),
(12214, 19, 12210, '城子河区', '城子河区', 20),
(12215, 19, 12210, '麻山区', '麻山区', 25),
(12216, 19, 12210, '鸡冠区', '鸡冠区', 30),
(12217, 19, 12210, '密山市', '密山市', 35),
(12218, 19, 12210, '虎林市', '虎林市', 40),
(12219, 19, 12210, '鸡东县', '鸡东县', 45),
(12220, 19, 12155, '大庆市', '大庆市', 30),
(12221, 19, 12220, '萨尔图区', '萨尔图区', 5),
(12222, 19, 12220, '龙凤区', '龙凤区', 10),
(12223, 19, 12220, '让胡路区', '让胡路区', 15),
(12224, 19, 12220, '红岗区', '红岗区', 20),
(12225, 19, 12220, '大同区', '大同区', 25),
(12226, 19, 12220, '林甸县', '林甸县', 30),
(12227, 19, 12220, '肇州县', '肇州县', 35),
(12228, 19, 12220, '肇源县', '肇源县', 40),
(12229, 19, 12220, '杜尔伯特县', '杜尔伯特县', 45),
(12230, 19, 12155, '伊春市', '伊春市', 35),
(12231, 19, 12230, '伊春区', '伊春区', 5),
(12232, 19, 12230, '南岔区', '南岔区', 10),
(12233, 19, 12230, '友好区', '友好区', 15),
(12234, 19, 12230, '西林区', '西林区', 20),
(12235, 19, 12230, '翠峦区', '翠峦区', 25),
(12236, 19, 12230, '新青区', '新青区', 30),
(12237, 19, 12230, '美溪区', '美溪区', 35),
(12238, 19, 12230, '金山屯区', '金山屯区', 40),
(12239, 19, 12230, '五营区', '五营区', 45),
(12240, 19, 12230, '乌马河区', '乌马河区', 50),
(12241, 19, 12230, '汤旺河区', '汤旺河区', 55),
(12242, 19, 12230, '带岭区', '带岭区', 60),
(12243, 19, 12230, '乌伊岭区', '乌伊岭区', 65),
(12244, 19, 12230, '红星区', '红星区', 70),
(12245, 19, 12230, '上甘岭区', '上甘岭区', 75),
(12246, 19, 12230, '铁力市', '铁力市', 80),
(12247, 19, 12230, '嘉荫县', '嘉荫县', 85),
(12248, 19, 12155, '牡丹江市', '牡丹江市', 40),
(12249, 19, 12248, '爱民区', '爱民区', 5),
(12250, 19, 12248, '东安区', '东安区', 10),
(12251, 19, 12248, '阳明区', '阳明区', 15),
(12252, 19, 12248, '西安区', '西安区', 20),
(12253, 19, 12248, '绥芬河市', '绥芬河市', 25),
(12254, 19, 12248, '海林市', '海林市', 30),
(12255, 19, 12248, '宁安市', '宁安市', 35),
(12256, 19, 12248, '穆棱市', '穆棱市', 40),
(12257, 19, 12248, '林口县', '林口县', 45),
(12258, 19, 12248, '东宁县', '东宁县', 50),
(12259, 19, 12155, '佳木斯市', '佳木斯市', 45),
(12260, 19, 12259, '桦川县', '桦川县', 5),
(12261, 19, 12259, '抚远县', '抚远县', 10),
(12262, 19, 12259, '桦南县', '桦南县', 15),
(12263, 19, 12259, '汤原县', '汤原县', 20),
(12264, 19, 12259, '前进区', '前进区', 25),
(12265, 19, 12259, '向阳区', '向阳区', 30),
(12266, 19, 12259, '东风区', '东风区', 35),
(12267, 19, 12259, '郊区', '郊区', 40),
(12268, 19, 12259, '同江市', '同江市', 45),
(12269, 19, 12259, '富锦市', '富锦市', 50),
(12270, 19, 12155, '七台河市', '七台河市', 50),
(12271, 19, 12270, '勃利县', '勃利县', 5),
(12272, 19, 12270, '桃山区', '桃山区', 10),
(12273, 19, 12270, '新兴区', '新兴区', 15),
(12274, 19, 12270, '茄子河区', '茄子河区', 20),
(12275, 19, 12155, '黑河市', '黑河市', 55),
(12276, 19, 12275, '北安市', '北安市', 5),
(12277, 19, 12275, '五大连池市', '五大连池市', 10),
(12278, 19, 12275, '逊克县', '逊克县', 15),
(12279, 19, 12275, '孙吴县', '孙吴县', 20),
(12280, 19, 12275, '嫩江县', '嫩江县', 25),
(12281, 19, 12275, '爱辉区', '爱辉区', 30),
(12282, 19, 12155, '绥化市', '绥化市', 60),
(12283, 19, 12282, '北林区', '北林区', 5),
(12284, 19, 12282, '安达市', '安达市', 10),
(12285, 19, 12282, '肇东市', '肇东市', 15),
(12286, 19, 12282, '海伦市', '海伦市', 20),
(12287, 19, 12282, '绥棱县', '绥棱县', 25),
(12288, 19, 12282, '兰西县', '兰西县', 30),
(12289, 19, 12282, '明水县', '明水县', 35),
(12290, 19, 12282, '青冈县', '青冈县', 40),
(12291, 19, 12282, '庆安县', '庆安县', 45),
(12292, 19, 12282, '望奎县', '望奎县', 50),
(12293, 19, 12155, '大兴安岭地区', '大兴安岭地区', 65),
(12294, 19, 12293, '加格达奇区', '加格达奇区', 5),
(12295, 19, 12293, '松岭区', '松岭区', 10),
(12296, 19, 12293, '呼中区', '呼中区', 15),
(12297, 19, 12293, '呼玛县', '呼玛县', 20),
(12298, 19, 12293, '塔河县', '塔河县', 25),
(12299, 19, 12293, '漠河县', '漠河县', 30),
(12300, 19, 12293, '新林区', '新林区', 35),
(12301, 19, 0, '内蒙古自治区', '内蒙古自治区', 55),
(12302, 19, 12301, '呼和浩特市', '呼和浩特市', 5),
(12303, 19, 12302, '玉泉区', '玉泉区', 5),
(12304, 19, 12302, '赛罕区', '赛罕区', 10),
(12305, 19, 12302, '土默特左旗', '土默特左旗', 15),
(12306, 19, 12302, '和林格尔县', '和林格尔县', 20),
(12307, 19, 12302, '武川县', '武川县', 25),
(12308, 19, 12302, '托克托县', '托克托县', 30),
(12309, 19, 12302, '清水河县', '清水河县', 35),
(12310, 19, 12302, '回民区', '回民区', 40),
(12311, 19, 12302, '新城区', '新城区', 45),
(12312, 19, 12301, '包头市', '包头市', 10),
(12313, 19, 12312, '固阳县', '固阳县', 5),
(12314, 19, 12312, '土默特右旗', '土默特右旗', 10),
(12315, 19, 12312, '达茂联合旗', '达茂联合旗', 15),
(12316, 19, 12312, '东河区', '东河区', 20),
(12317, 19, 12312, '九原区', '九原区', 25),
(12318, 19, 12312, '青山区', '青山区', 30),
(12319, 19, 12312, '昆都仑区', '昆都仑区', 35),
(12320, 19, 12312, '石拐区', '石拐区', 40),
(12321, 19, 12312, '白云矿区', '白云矿区', 45),
(12322, 19, 12301, '乌海市', '乌海市', 15),
(12323, 19, 12322, '海勃湾区', '海勃湾区', 5),
(12324, 19, 12322, '海南区', '海南区', 10),
(12325, 19, 12322, '乌达区', '乌达区', 15),
(12326, 19, 12301, '赤峰市', '赤峰市', 20),
(12327, 19, 12326, '宁城县', '宁城县', 5),
(12328, 19, 12326, '敖汉旗', '敖汉旗', 10),
(12329, 19, 12326, '喀喇沁旗', '喀喇沁旗', 15),
(12330, 19, 12326, '翁牛特旗', '翁牛特旗', 20),
(12331, 19, 12326, '巴林右旗', '巴林右旗', 25),
(12332, 19, 12326, '林西县', '林西县', 30),
(12333, 19, 12326, '克什克腾旗', '克什克腾旗', 35),
(12334, 19, 12326, '巴林左旗', '巴林左旗', 40),
(12335, 19, 12326, '阿鲁科尔沁旗', '阿鲁科尔沁旗', 45),
(12336, 19, 12326, '元宝山区', '元宝山区', 50),
(12337, 19, 12326, '红山区', '红山区', 55),
(12338, 19, 12326, '松山区', '松山区', 60),
(12339, 19, 12301, '乌兰察布市', '乌兰察布市', 25),
(12340, 19, 12339, '集宁区', '集宁区', 5),
(12341, 19, 12339, '丰镇市', '丰镇市', 10),
(12342, 19, 12339, '兴和县', '兴和县', 15),
(12343, 19, 12339, '卓资县', '卓资县', 20),
(12344, 19, 12339, '商都县', '商都县', 25),
(12345, 19, 12339, '凉城县', '凉城县', 30),
(12346, 19, 12339, '化德县', '化德县', 35),
(12347, 19, 12339, '察哈尔右翼前旗', '察哈尔右翼前旗', 40),
(12348, 19, 12339, '察哈尔右翼中旗', '察哈尔右翼中旗', 45),
(12349, 19, 12339, '察哈尔右翼后旗', '察哈尔右翼后旗', 50),
(12350, 19, 12339, '四子王旗', '四子王旗', 55),
(12351, 19, 12301, '锡林郭勒盟', '锡林郭勒盟', 30),
(12352, 19, 12351, '锡林浩特市', '锡林浩特市', 5),
(12353, 19, 12351, '二连浩特市', '二连浩特市', 10),
(12354, 19, 12351, '多伦县', '多伦县', 15),
(12355, 19, 12351, '阿巴嘎旗', '阿巴嘎旗', 20),
(12356, 19, 12351, '西乌珠穆沁旗', '西乌珠穆沁旗', 25),
(12357, 19, 12351, '东乌珠穆沁旗', '东乌珠穆沁旗', 30),
(12358, 19, 12351, '苏尼特右旗', '苏尼特右旗', 35),
(12359, 19, 12351, '苏尼特左旗', '苏尼特左旗', 40),
(12360, 19, 12351, '太仆寺旗', '太仆寺旗', 45),
(12361, 19, 12351, '正镶白旗', '正镶白旗', 50),
(12362, 19, 12351, '正蓝旗', '正蓝旗', 55),
(12363, 19, 12351, '镶黄旗', '镶黄旗', 60),
(12364, 19, 12301, '呼伦贝尔市', '呼伦贝尔市', 35),
(12365, 19, 12364, '海拉尔区', '海拉尔区', 5),
(12366, 19, 12364, '满洲里市', '满洲里市', 10),
(12367, 19, 12364, '牙克石市', '牙克石市', 15),
(12368, 19, 12364, '扎兰屯市', '扎兰屯市', 20),
(12369, 19, 12364, '根河市', '根河市', 25),
(12370, 19, 12364, '额尔古纳市', '额尔古纳市', 30),
(12371, 19, 12364, '陈巴尔虎旗', '陈巴尔虎旗', 35),
(12372, 19, 12364, '阿荣旗', '阿荣旗', 40),
(12373, 19, 12364, '新巴尔虎左旗', '新巴尔虎左旗', 45),
(12374, 19, 12364, '新巴尔虎右旗', '新巴尔虎右旗', 50),
(12375, 19, 12364, '鄂伦春旗', '鄂伦春旗', 55),
(12376, 19, 12364, '莫力达瓦旗', '莫力达瓦旗', 60),
(12377, 19, 12364, '鄂温克族旗', '鄂温克族旗', 65),
(12378, 19, 12301, '鄂尔多斯市', '鄂尔多斯市', 40),
(12379, 19, 12378, '东胜区', '东胜区', 5),
(12380, 19, 12378, '准格尔旗', '准格尔旗', 10),
(12381, 19, 12378, '伊金霍洛旗', '伊金霍洛旗', 15),
(12382, 19, 12378, '乌审旗', '乌审旗', 20),
(12383, 19, 12378, '杭锦旗', '杭锦旗', 25),
(12384, 19, 12378, '鄂托克旗', '鄂托克旗', 30),
(12385, 19, 12378, '鄂托克前旗', '鄂托克前旗', 35),
(12386, 19, 12378, '达拉特旗', '达拉特旗', 40),
(12387, 19, 12378, '康巴什新区', '康巴什新区', 45),
(12388, 19, 12301, '巴彦淖尔市', '巴彦淖尔市', 45),
(12389, 19, 12388, '临河区', '临河区', 5),
(12390, 19, 12388, '五原县', '五原县', 10),
(12391, 19, 12388, '磴口县', '磴口县', 15),
(12392, 19, 12388, '杭锦后旗', '杭锦后旗', 20),
(12393, 19, 12388, '乌拉特中旗', '乌拉特中旗', 25),
(12394, 19, 12388, '乌拉特后旗 ', '乌拉特后旗 ', 30),
(12395, 19, 12388, '乌拉特前旗', '乌拉特前旗', 35),
(12396, 19, 12301, '阿拉善盟', '阿拉善盟', 50),
(12397, 19, 12396, '阿拉善右旗', '阿拉善右旗', 5),
(12398, 19, 12396, '阿拉善左旗', '阿拉善左旗', 10),
(12399, 19, 12396, '额济纳旗', '额济纳旗', 15),
(12400, 19, 12301, '兴安盟', '兴安盟', 55),
(12401, 19, 12400, '乌兰浩特市', '乌兰浩特市', 5),
(12402, 19, 12400, '阿尔山市', '阿尔山市', 10),
(12403, 19, 12400, '突泉县', '突泉县', 15),
(12404, 19, 12400, '扎赉特旗', '扎赉特旗', 20),
(12405, 19, 12400, '科尔沁右翼前旗', '科尔沁右翼前旗', 25),
(12406, 19, 12400, '科尔沁右翼中旗', '科尔沁右翼中旗', 30),
(12407, 19, 12301, '通辽市', '通辽市', 60),
(12408, 19, 12407, '科尔沁区', '科尔沁区', 5),
(12409, 19, 12407, '霍林郭勒市', '霍林郭勒市', 10),
(12410, 19, 12407, '开鲁县', '开鲁县', 15),
(12411, 19, 12407, '库伦旗', '库伦旗', 20),
(12412, 19, 12407, '奈曼旗', '奈曼旗', 25),
(12413, 19, 12407, '扎鲁特旗', '扎鲁特旗', 30),
(12414, 19, 12407, '科尔沁左翼中旗', '科尔沁左翼中旗', 35),
(12415, 19, 12407, '科尔沁左翼后旗', '科尔沁左翼后旗', 40),
(12416, 19, 0, '江苏省', '江苏省', 60),
(12417, 19, 12416, '南京市', '南京市', 5),
(12418, 19, 12417, '玄武区', '玄武区', 5),
(12419, 19, 12417, '秦淮区', '秦淮区', 10),
(12420, 19, 12417, '建邺区', '建邺区', 15),
(12421, 19, 12417, '鼓楼区', '鼓楼区', 20),
(12422, 19, 12417, '栖霞区', '栖霞区', 25),
(12423, 19, 12417, '江宁区', '江宁区', 30),
(12424, 19, 12417, '六合区', '六合区', 35),
(12425, 19, 12417, '雨花台区', '雨花台区', 40),
(12426, 19, 12417, '高淳区', '高淳区', 45),
(12427, 19, 12417, '溧水区', '溧水区', 50),
(12428, 19, 12417, '浦口区', '浦口区', 55),
(12429, 19, 12416, '徐州市', '徐州市', 10),
(12430, 19, 12429, '贾汪区', '贾汪区', 5),
(12431, 19, 12429, '金山桥开发区', '金山桥开发区', 10),
(12432, 19, 12429, '铜山经济技术开发区', '铜山经济技术开发区', 15),
(12433, 19, 12429, '八段工业园区', '八段工业园区', 20),
(12434, 19, 12429, '鼓楼区', '鼓楼区', 25),
(12435, 19, 12429, '邳州市', '邳州市', 30),
(12436, 19, 12429, '泉山区', '泉山区', 35),
(12437, 19, 12429, '新沂市', '新沂市', 40),
(12438, 19, 12429, '云龙区', '云龙区', 45),
(12439, 19, 12429, '铜山区', '铜山区', 50),
(12440, 19, 12429, '睢宁县', '睢宁县', 55),
(12441, 19, 12429, '沛县', '沛县', 60),
(12442, 19, 12429, '丰县', '丰县', 65),
(12443, 19, 12416, '连云港市', '连云港市', 15),
(12444, 19, 12443, '海州区', '海州区', 5),
(12445, 19, 12443, '新浦区', '新浦区', 10),
(12446, 19, 12443, '赣榆县', '赣榆县', 15),
(12447, 19, 12443, '连云区', '连云区', 20),
(12448, 19, 12443, '灌云县', '灌云县', 25),
(12449, 19, 12443, '东海县', '东海县', 30),
(12450, 19, 12443, '灌南县', '灌南县', 35),
(12451, 19, 12416, '淮安市', '淮安市', 20),
(12452, 19, 12451, '经济开发区', '经济开发区', 5),
(12453, 19, 12451, '楚州区', '楚州区', 10),
(12454, 19, 12451, '洪泽县', '洪泽县', 15),
(12455, 19, 12451, '金湖县', '金湖县', 20),
(12456, 19, 12451, '盱眙县', '盱眙县', 25),
(12457, 19, 12451, '清河区', '清河区', 30),
(12458, 19, 12451, '淮阴区', '淮阴区', 35),
(12459, 19, 12451, '清浦区', '清浦区', 40),
(12460, 19, 12451, '涟水县', '涟水县', 45),
(12461, 19, 12416, '宿迁市', '宿迁市', 25),
(12462, 19, 12461, '宿城区', '宿城区', 5),
(12463, 19, 12461, '沭阳县', '沭阳县', 10),
(12464, 19, 12461, '泗阳县', '泗阳县', 15),
(12465, 19, 12461, '宿豫区', '宿豫区', 20),
(12466, 19, 12461, '泗洪县', '泗洪县', 25),
(12467, 19, 12461, '宿迁经济开发区', '宿迁经济开发区', 30),
(12468, 19, 12416, '盐城市', '盐城市', 30),
(12469, 19, 12468, '射阳县', '射阳县', 5),
(12470, 19, 12468, '亭湖区', '亭湖区', 10),
(12471, 19, 12468, '盐都区', '盐都区', 15),
(12472, 19, 12468, '东台市', '东台市', 20),
(12473, 19, 12468, '大丰市', '大丰市', 25),
(12474, 19, 12468, '建湖县', '建湖县', 30),
(12475, 19, 12468, '响水县', '响水县', 35),
(12476, 19, 12468, '阜宁县', '阜宁县', 40),
(12477, 19, 12468, '滨海县', '滨海县', 45),
(12478, 19, 12416, '扬州市', '扬州市', 35),
(12479, 19, 12478, '广陵区', '广陵区', 5),
(12480, 19, 12478, '邗江区', '邗江区', 10),
(12481, 19, 12478, '宝应县', '宝应县', 15),
(12482, 19, 12478, '仪征市', '仪征市', 20),
(12483, 19, 12478, '高邮市', '高邮市', 25),
(12484, 19, 12478, '江都区', '江都区', 30),
(12485, 19, 12416, '泰州市', '泰州市', 40),
(12486, 19, 12485, '海陵区', '海陵区', 5),
(12487, 19, 12485, '高港区', '高港区', 10),
(12488, 19, 12485, '泰兴市', '泰兴市', 15),
(12489, 19, 12485, '靖江市', '靖江市', 20),
(12490, 19, 12485, '兴化市', '兴化市', 25),
(12491, 19, 12485, '姜堰市', '姜堰市', 30),
(12492, 19, 12416, '南通市', '南通市', 45),
(12493, 19, 12492, '港闸区', '港闸区', 5),
(12494, 19, 12492, '崇川区', '崇川区', 10),
(12495, 19, 12492, '通州区', '通州区', 15),
(12496, 19, 12492, '南通经济技术开发区', '南通经济技术开发区', 20),
(12497, 19, 12492, '如东县', '如东县', 25),
(12498, 19, 12492, '海安县', '海安县', 30),
(12499, 19, 12492, '如皋市', '如皋市', 35),
(12500, 19, 12492, '海门市', '海门市', 40),
(12501, 19, 12492, '启东市', '启东市', 45),
(12502, 19, 12416, '镇江市', '镇江市', 50),
(12503, 19, 12502, '润州区', '润州区', 5),
(12504, 19, 12502, '京口区', '京口区', 10),
(12505, 19, 12502, '丹徒区', '丹徒区', 15),
(12506, 19, 12502, '镇江新区', '镇江新区', 20),
(12507, 19, 12502, '丹阳市', '丹阳市', 25),
(12508, 19, 12502, '句容市', '句容市', 30),
(12509, 19, 12502, '扬中市', '扬中市', 35),
(12510, 19, 12502, '丹徒新区', '丹徒新区', 40),
(12511, 19, 12416, '常州市', '常州市', 55),
(12512, 19, 12511, '钟楼区', '钟楼区', 5),
(12513, 19, 12511, '天宁区', '天宁区', 10),
(12514, 19, 12511, '武进区', '武进区', 15),
(12515, 19, 12511, '新北区', '新北区', 20),
(12516, 19, 12511, '戚墅堰区', '戚墅堰区', 25),
(12517, 19, 12511, '金坛市', '金坛市', 30),
(12518, 19, 12511, '溧阳市', '溧阳市', 35),
(12519, 19, 12416, '无锡市', '无锡市', 60),
(12520, 19, 12519, '崇安区', '崇安区', 5),
(12521, 19, 12519, '南长区', '南长区', 10),
(12522, 19, 12519, '北塘区', '北塘区', 15),
(12523, 19, 12519, '锡山区', '锡山区', 20),
(12524, 19, 12519, '惠山区', '惠山区', 25),
(12525, 19, 12519, '新区', '新区', 30),
(12526, 19, 12519, '江阴市', '江阴市', 35),
(12527, 19, 12519, '宜兴市', '宜兴市', 40),
(12528, 19, 12519, '滨湖区', '滨湖区', 45),
(12529, 19, 12416, '苏州市', '苏州市', 65),
(12530, 19, 12529, '常熟市', '常熟市', 5),
(12531, 19, 12529, '张家港市', '张家港市', 10),
(12532, 19, 12529, '太仓市', '太仓市', 15),
(12533, 19, 12529, '相城区', '相城区', 20),
(12534, 19, 12529, '金阊区', '金阊区', 25),
(12535, 19, 12529, '虎丘区', '虎丘区', 30),
(12536, 19, 12529, '平江区', '平江区', 35),
(12537, 19, 12529, '沧浪区', '沧浪区', 40),
(12538, 19, 12529, '工业园区', '工业园区', 45),
(12539, 19, 12529, '高新区', '高新区', 50),
(12540, 19, 12529, '吴江区', '吴江区', 55),
(12541, 19, 12529, '吴中区', '吴中区', 60),
(12542, 19, 12529, '昆山市', '昆山市', 65),
(12543, 19, 0, '山东省', '山东省', 65),
(12544, 19, 12543, '济南市', '济南市', 5),
(12545, 19, 12544, '高新区', '高新区', 5),
(12546, 19, 12544, '长清区', '长清区', 10),
(12547, 19, 12544, '历城区', '历城区', 15),
(12548, 19, 12544, '天桥区', '天桥区', 20),
(12549, 19, 12544, '槐荫区', '槐荫区', 25),
(12550, 19, 12544, '历下区', '历下区', 30),
(12551, 19, 12544, '市中区', '市中区', 35),
(12552, 19, 12544, '章丘市', '章丘市', 40),
(12553, 19, 12544, '平阴县', '平阴县', 45),
(12554, 19, 12544, '济阳县', '济阳县', 50),
(12555, 19, 12544, '商河县', '商河县', 55),
(12556, 19, 12543, '青岛市', '青岛市', 10),
(12557, 19, 12556, '四方区', '四方区', 5),
(12558, 19, 12556, '市北区', '市北区', 10),
(12559, 19, 12556, '市南区', '市南区', 15),
(12560, 19, 12556, '黄岛区', '黄岛区', 20),
(12561, 19, 12556, '李沧区', '李沧区', 25),
(12562, 19, 12556, '即墨市', '即墨市', 30),
(12563, 19, 12556, '城阳区', '城阳区', 35),
(12564, 19, 12556, '崂山区', '崂山区', 40),
(12565, 19, 12556, '胶州市', '胶州市', 45),
(12566, 19, 12556, '平度市', '平度市', 50),
(12567, 19, 12556, '莱西市', '莱西市', 55),
(12568, 19, 12543, '淄博市', '淄博市', 15),
(12569, 19, 12568, '临淄区', '临淄区', 5),
(12570, 19, 12568, '张店区', '张店区', 10),
(12571, 19, 12568, '周村区', '周村区', 15),
(12572, 19, 12568, '淄川区', '淄川区', 20),
(12573, 19, 12568, '博山区', '博山区', 25),
(12574, 19, 12568, '高青县', '高青县', 30),
(12575, 19, 12568, '沂源县', '沂源县', 35),
(12576, 19, 12568, '桓台县', '桓台县', 40),
(12577, 19, 12543, '枣庄市', '枣庄市', 20),
(12578, 19, 12577, '滕州市', '滕州市', 5),
(12579, 19, 12577, '山亭区', '山亭区', 10),
(12580, 19, 12577, '台儿庄区', '台儿庄区', 15),
(12581, 19, 12577, '峄城区', '峄城区', 20),
(12582, 19, 12577, '薛城区', '薛城区', 25),
(12583, 19, 12577, '市中区', '市中区', 30),
(12584, 19, 12543, '东营市', '东营市', 25),
(12585, 19, 12584, '河口区', '河口区', 5),
(12586, 19, 12584, '广饶县', '广饶县', 10),
(12587, 19, 12584, '利津县', '利津县', 15),
(12588, 19, 12584, '垦利县', '垦利县', 20),
(12589, 19, 12584, '东营区', '东营区', 25),
(12590, 19, 12543, '潍坊市', '潍坊市', 30),
(12591, 19, 12590, '潍城区', '潍城区', 5),
(12592, 19, 12590, '奎文区', '奎文区', 10),
(12593, 19, 12590, '高新区', '高新区', 15),
(12594, 19, 12590, '寒亭区', '寒亭区', 20),
(12595, 19, 12590, '寿光市', '寿光市', 25),
(12596, 19, 12590, '青州市', '青州市', 30),
(12597, 19, 12590, '诸城市', '诸城市', 35),
(12598, 19, 12590, '安丘市', '安丘市', 40),
(12599, 19, 12590, '高密市', '高密市', 45),
(12600, 19, 12590, '昌邑市', '昌邑市', 50),
(12601, 19, 12590, '昌乐县', '昌乐县', 55),
(12602, 19, 12590, '临朐县', '临朐县', 60),
(12603, 19, 12590, '坊子区', '坊子区', 65),
(12604, 19, 12543, '烟台市', '烟台市', 35),
(12605, 19, 12604, '莱山区', '莱山区', 5),
(12606, 19, 12604, '芝罘区', '芝罘区', 10),
(12607, 19, 12604, '开发区', '开发区', 15),
(12608, 19, 12604, '福山区', '福山区', 20),
(12609, 19, 12604, '牟平区', '牟平区', 25),
(12610, 19, 12604, '龙口市', '龙口市', 30),
(12611, 19, 12604, '莱州市', '莱州市', 35),
(12612, 19, 12604, '莱阳市', '莱阳市', 40),
(12613, 19, 12604, '招远市', '招远市', 45),
(12614, 19, 12604, '蓬莱市', '蓬莱市', 50),
(12615, 19, 12604, '栖霞市', '栖霞市', 55),
(12616, 19, 12604, '海阳市', '海阳市', 60),
(12617, 19, 12604, '长岛县', '长岛县', 65),
(12618, 19, 12543, '威海市', '威海市', 40),
(12619, 19, 12618, '荣成市', '荣成市', 5),
(12620, 19, 12618, '文登市', '文登市', 10),
(12621, 19, 12618, '乳山市', '乳山市', 15),
(12622, 19, 12618, '环翠区', '环翠区', 20),
(12623, 19, 12543, '莱芜市', '莱芜市', 45),
(12624, 19, 12623, '莱城区', '莱城区', 5),
(12625, 19, 12623, '钢城区', '钢城区', 10),
(12626, 19, 12543, '德州市', '德州市', 50),
(12627, 19, 12626, '德城区', '德城区', 5),
(12628, 19, 12626, '临邑县', '临邑县', 10),
(12629, 19, 12626, '齐河县', '齐河县', 15),
(12630, 19, 12626, '乐陵市', '乐陵市', 20),
(12631, 19, 12626, '禹城市', '禹城市', 25),
(12632, 19, 12626, '陵县', '陵县', 30),
(12633, 19, 12626, '宁津县', '宁津县', 35),
(12634, 19, 12626, '武城县', '武城县', 40),
(12635, 19, 12626, '庆云县', '庆云县', 45),
(12636, 19, 12626, '平原县', '平原县', 50),
(12637, 19, 12626, '夏津县', '夏津县', 55),
(12638, 19, 12543, '临沂市', '临沂市', 55),
(12639, 19, 12638, '兰陵县', '兰陵县', 5),
(12640, 19, 12638, '兰山区', '兰山区', 10),
(12641, 19, 12638, '河东区', '河东区', 15),
(12642, 19, 12638, '沂南县', '沂南县', 20),
(12643, 19, 12638, '沂水县', '沂水县', 25),
(12644, 19, 12638, '费县', '费县', 30),
(12645, 19, 12638, '平邑县', '平邑县', 35),
(12646, 19, 12638, '蒙阴县', '蒙阴县', 40),
(12647, 19, 12638, '临沭县', '临沭县', 45),
(12648, 19, 12638, '莒南县', '莒南县', 50),
(12649, 19, 12638, '郯城县', '郯城县', 55),
(12650, 19, 12638, '罗庄区', '罗庄区', 60),
(12651, 19, 12543, '聊城市', '聊城市', 60),
(12652, 19, 12651, '东昌府区', '东昌府区', 5),
(12653, 19, 12651, '临清市', '临清市', 10),
(12654, 19, 12651, '阳谷县', '阳谷县', 15),
(12655, 19, 12651, '茌平县', '茌平县', 20),
(12656, 19, 12651, '莘县', '莘县', 25),
(12657, 19, 12651, '东阿县', '东阿县', 30),
(12658, 19, 12651, '冠县', '冠县', 35),
(12659, 19, 12651, '高唐县', '高唐县', 40),
(12660, 19, 12543, '滨州市', '滨州市', 65),
(12661, 19, 12660, '北海新区', '北海新区', 5),
(12662, 19, 12660, '滨城区', '滨城区', 10),
(12663, 19, 12660, '邹平县', '邹平县', 15),
(12664, 19, 12660, '沾化县', '沾化县', 20),
(12665, 19, 12660, '惠民县', '惠民县', 25),
(12666, 19, 12660, '博兴县', '博兴县', 30),
(12667, 19, 12660, '阳信县', '阳信县', 35),
(12668, 19, 12660, '无棣县', '无棣县', 40),
(12669, 19, 12543, '菏泽市', '菏泽市', 70),
(12670, 19, 12669, '牡丹区', '牡丹区', 5),
(12671, 19, 12669, '单县', '单县', 10),
(12672, 19, 12669, '曹县', '曹县', 15),
(12673, 19, 12669, '定陶县', '定陶县', 20),
(12674, 19, 12669, '巨野县', '巨野县', 25),
(12675, 19, 12669, '成武县', '成武县', 30),
(12676, 19, 12669, '东明县', '东明县', 35),
(12677, 19, 12669, '郓城县', '郓城县', 40),
(12678, 19, 12669, '鄄城县', '鄄城县', 45),
(12679, 19, 12543, '日照市', '日照市', 75),
(12680, 19, 12679, '岚山区', '岚山区', 5),
(12681, 19, 12679, '新市区', '新市区', 10),
(12682, 19, 12679, '五莲县', '五莲县', 15),
(12683, 19, 12679, '东港区', '东港区', 20),
(12684, 19, 12679, '莒县', '莒县', 25),
(12685, 19, 12543, '泰安市', '泰安市', 80),
(12686, 19, 12685, '东平县', '东平县', 5),
(12687, 19, 12685, '岱岳区', '岱岳区', 10),
(12688, 19, 12685, '泰山区', '泰山区', 15),
(12689, 19, 12685, '肥城市', '肥城市', 20),
(12690, 19, 12685, '新泰市', '新泰市', 25),
(12691, 19, 12685, '宁阳县', '宁阳县', 30),
(12692, 19, 12543, '济宁市', '济宁市', 85),
(12693, 19, 12692, '梁山县', '梁山县', 5),
(12694, 19, 12692, '兖州市', '兖州市', 10),
(12695, 19, 12692, '微山县', '微山县', 15),
(12696, 19, 12692, '汶上县', '汶上县', 20),
(12697, 19, 12692, '泗水县', '泗水县', 25),
(12698, 19, 12692, '嘉祥县', '嘉祥县', 30),
(12699, 19, 12692, '鱼台县', '鱼台县', 35),
(12700, 19, 12692, '金乡县', '金乡县', 40),
(12701, 19, 12692, '邹城市', '邹城市', 45),
(12702, 19, 12692, '市中区', '市中区', 50),
(12703, 19, 12692, '曲阜市', '曲阜市', 55),
(12704, 19, 12692, '高新区', '高新区', 60),
(12705, 19, 12692, '任城区', '任城区', 65),
(12706, 19, 0, '安徽省', '安徽省', 70),
(12707, 19, 12706, '铜陵市', '铜陵市', 5),
(12708, 19, 12707, '铜官山区', '铜官山区', 5),
(12709, 19, 12707, '郊区', '郊区', 10),
(12710, 19, 12707, '狮子山区', '狮子山区', 15),
(12711, 19, 12707, '铜陵县', '铜陵县', 20),
(12712, 19, 12706, '合肥市', '合肥市', 10),
(12713, 19, 12712, '包河区', '包河区', 5),
(12714, 19, 12712, '蜀山区', '蜀山区', 10),
(12715, 19, 12712, '瑶海区', '瑶海区', 15),
(12716, 19, 12712, '庐阳区', '庐阳区', 20),
(12717, 19, 12712, '滨湖新区', '滨湖新区', 25),
(12718, 19, 12712, '经济技术开发区', '经济技术开发区', 30),
(12719, 19, 12712, '高新技术开发区', '高新技术开发区', 35),
(12720, 19, 12712, '新站综合开发试验区', '新站综合开发试验区', 40),
(12721, 19, 12712, '肥西县', '肥西县', 45),
(12722, 19, 12712, '政务文化新区', '政务文化新区', 50),
(12723, 19, 12712, '巢湖市', '巢湖市', 55),
(12724, 19, 12712, '长丰县', '长丰县', 60),
(12725, 19, 12712, '肥东县', '肥东县', 65),
(12726, 19, 12712, '庐江县', '庐江县', 70),
(12727, 19, 12712, '北城新区', '北城新区', 75),
(12728, 19, 12706, '淮南市', '淮南市', 15),
(12729, 19, 12728, '淮南高新技术开发区', '淮南高新技术开发区', 5),
(12730, 19, 12728, '田家庵区', '田家庵区', 10),
(12731, 19, 12728, '大通区', '大通区', 15),
(12732, 19, 12728, '谢家集区', '谢家集区', 20),
(12733, 19, 12728, '八公山区', '八公山区', 25),
(12734, 19, 12728, '凤台县', '凤台县', 30),
(12735, 19, 12728, '潘集区', '潘集区', 35),
(12736, 19, 12706, '淮北市', '淮北市', 20),
(12737, 19, 12736, '杜集区', '杜集区', 5),
(12738, 19, 12736, '烈山区', '烈山区', 10),
(12739, 19, 12736, '濉溪县', '濉溪县', 15),
(12740, 19, 12736, '相山区', '相山区', 20),
(12741, 19, 12706, '芜湖市', '芜湖市', 25),
(12742, 19, 12741, '镜湖区', '镜湖区', 5),
(12743, 19, 12741, '弋江区', '弋江区', 10),
(12744, 19, 12741, '无为县', '无为县', 15),
(12745, 19, 12741, '芜湖县', '芜湖县', 20),
(12746, 19, 12741, '繁昌县', '繁昌县', 25),
(12747, 19, 12741, '南陵县', '南陵县', 30),
(12748, 19, 12741, '鸠江区', '鸠江区', 35),
(12749, 19, 12741, '三山区', '三山区', 40),
(12750, 19, 12706, '蚌埠市', '蚌埠市', 30),
(12751, 19, 12750, '蚌山区', '蚌山区', 5),
(12752, 19, 12750, '怀远县', '怀远县', 10),
(12753, 19, 12750, '固镇县', '固镇县', 15),
(12754, 19, 12750, '五河县', '五河县', 20),
(12755, 19, 12750, '淮上区', '淮上区', 25),
(12756, 19, 12750, '龙子湖区', '龙子湖区', 30),
(12757, 19, 12750, '禹会区', '禹会区', 35),
(12758, 19, 12706, '马鞍山市', '马鞍山市', 35),
(12759, 19, 12758, '博望区', '博望区', 5),
(12760, 19, 12758, '花山区', '花山区', 10),
(12761, 19, 12758, '雨山区', '雨山区', 15),
(12762, 19, 12758, '当涂县', '当涂县', 20),
(12763, 19, 12758, '含山县', '含山县', 25),
(12764, 19, 12758, '和县', '和县', 30),
(12765, 19, 12706, '安庆市', '安庆市', 40),
(12766, 19, 12765, '桐城市', '桐城市', 5),
(12767, 19, 12765, '宿松县', '宿松县', 10),
(12768, 19, 12765, '枞阳县', '枞阳县', 15),
(12769, 19, 12765, '太湖县', '太湖县', 20),
(12770, 19, 12765, '怀宁县', '怀宁县', 25),
(12771, 19, 12765, '岳西县', '岳西县', 30),
(12772, 19, 12765, '望江县', '望江县', 35),
(12773, 19, 12765, '潜山县', '潜山县', 40),
(12774, 19, 12765, '大观区', '大观区', 45),
(12775, 19, 12765, '宜秀区', '宜秀区', 50),
(12776, 19, 12765, '迎江区', '迎江区', 55),
(12777, 19, 12765, '开发区', '开发区', 60),
(12778, 19, 12706, '黄山市', '黄山市', 45),
(12779, 19, 12778, '徽州区', '徽州区', 5),
(12780, 19, 12778, '屯溪区', '屯溪区', 10),
(12781, 19, 12778, '休宁县', '休宁县', 15),
(12782, 19, 12778, '歙县', '歙县', 20),
(12783, 19, 12778, '黟县', '黟县', 25),
(12784, 19, 12778, '祁门县', '祁门县', 30),
(12785, 19, 12778, '黄山区', '黄山区', 35),
(12786, 19, 12706, '滁州市', '滁州市', 50),
(12787, 19, 12786, '琅琊区', '琅琊区', 5),
(12788, 19, 12786, '天长市', '天长市', 10),
(12789, 19, 12786, '明光市', '明光市', 15),
(12790, 19, 12786, '全椒县', '全椒县', 20),
(12791, 19, 12786, '来安县', '来安县', 25),
(12792, 19, 12786, '南谯区', '南谯区', 30),
(12793, 19, 12786, '定远县', '定远县', 35),
(12794, 19, 12786, '凤阳县', '凤阳县', 40),
(12795, 19, 12706, '阜阳市', '阜阳市', 55),
(12796, 19, 12795, '经济开发区', '经济开发区', 5),
(12797, 19, 12795, '界首市', '界首市', 10),
(12798, 19, 12795, '太和县', '太和县', 15),
(12799, 19, 12795, '阜南县', '阜南县', 20),
(12800, 19, 12795, '颍上县', '颍上县', 25),
(12801, 19, 12795, '临泉县', '临泉县', 30),
(12802, 19, 12795, '颍泉区', '颍泉区', 35),
(12803, 19, 12795, '颍州区', '颍州区', 40),
(12804, 19, 12795, '颍东区', '颍东区', 45),
(12805, 19, 12706, '亳州市', '亳州市', 60),
(12806, 19, 12805, '利辛县', '利辛县', 5),
(12807, 19, 12805, '蒙城县', '蒙城县', 10),
(12808, 19, 12805, '涡阳县', '涡阳县', 15),
(12809, 19, 12805, '谯城区', '谯城区', 20),
(12810, 19, 12706, '宿州市', '宿州市', 65),
(12811, 19, 12810, '经济开发区', '经济开发区', 5),
(12812, 19, 12810, '埇桥区', '埇桥区', 10),
(12813, 19, 12810, '灵璧县', '灵璧县', 15),
(12814, 19, 12810, '泗县', '泗县', 20),
(12815, 19, 12810, '萧县', '萧县', 25),
(12816, 19, 12810, '砀山县', '砀山县', 30),
(12817, 19, 12706, '池州市', '池州市', 70),
(12818, 19, 12817, '贵池区', '贵池区', 5),
(12819, 19, 12817, '东至县', '东至县', 10),
(12820, 19, 12817, '石台县', '石台县', 15),
(12821, 19, 12817, '青阳县', '青阳县', 20),
(12822, 19, 12706, '六安市', '六安市', 75),
(12823, 19, 12822, '寿县', '寿县', 5),
(12824, 19, 12822, '霍山县', '霍山县', 10),
(12825, 19, 12822, '金寨县', '金寨县', 15),
(12826, 19, 12822, '霍邱县', '霍邱县', 20),
(12827, 19, 12822, '舒城县', '舒城县', 25),
(12828, 19, 12822, '金安区', '金安区', 30),
(12829, 19, 12822, '裕安区', '裕安区', 35),
(12830, 19, 12706, '宣城市', '宣城市', 80),
(12831, 19, 12830, '旌德县', '旌德县', 5),
(12832, 19, 12830, '宁国市', '宁国市', 10),
(12833, 19, 12830, '郎溪县', '郎溪县', 15),
(12834, 19, 12830, '广德县', '广德县', 20),
(12835, 19, 12830, '绩溪县', '绩溪县', 25),
(12836, 19, 12830, '泾县', '泾县', 30),
(12837, 19, 12830, '宣州区', '宣州区', 35),
(12838, 19, 0, '浙江省', '浙江省', 75),
(12839, 19, 12838, '宁波市', '宁波市', 5),
(12840, 19, 12839, '海曙区', '海曙区', 5),
(12841, 19, 12839, '江东区', '江东区', 10),
(12842, 19, 12839, '高新科技开发区', '高新科技开发区', 15),
(12843, 19, 12839, '慈溪市', '慈溪市', 20),
(12844, 19, 12839, '北仑区', '北仑区', 25),
(12845, 19, 12839, '镇海区', '镇海区', 30),
(12846, 19, 12839, '鄞州区', '鄞州区', 35),
(12847, 19, 12839, '江北区', '江北区', 40),
(12848, 19, 12839, '余姚市', '余姚市', 45),
(12849, 19, 12839, '奉化市', '奉化市', 50),
(12850, 19, 12839, '宁海县', '宁海县', 55),
(12851, 19, 12839, '象山县', '象山县', 60),
(12852, 19, 12838, '杭州市', '杭州市', 10),
(12853, 19, 12852, '上城区', '上城区', 5),
(12854, 19, 12852, '下城区', '下城区', 10),
(12855, 19, 12852, '拱墅区', '拱墅区', 15),
(12856, 19, 12852, '西湖区', '西湖区', 20),
(12857, 19, 12852, '江干区', '江干区', 25),
(12858, 19, 12852, '下沙区', '下沙区', 30),
(12859, 19, 12852, '余杭区', '余杭区', 35),
(12860, 19, 12852, '萧山区', '萧山区', 40),
(12861, 19, 12852, '滨江区', '滨江区', 45),
(12862, 19, 12852, '临安市', '临安市', 50),
(12863, 19, 12852, '富阳市', '富阳市', 55),
(12864, 19, 12852, '桐庐县', '桐庐县', 60),
(12865, 19, 12852, '建德市', '建德市', 65),
(12866, 19, 12852, '淳安县', '淳安县', 70),
(12867, 19, 12838, '温州市', '温州市', 15),
(12868, 19, 12867, '龙湾区', '龙湾区', 5),
(12869, 19, 12867, '茶山高教园区', '茶山高教园区', 10),
(12870, 19, 12867, '瑞安市', '瑞安市', 15),
(12871, 19, 12867, '乐清市', '乐清市', 20),
(12872, 19, 12867, '鹿城区', '鹿城区', 25),
(12873, 19, 12867, '瓯海区', '瓯海区', 30),
(12874, 19, 12867, '永嘉县', '永嘉县', 35);
INSERT INTO `qinggan_opt` (`id`, `group_id`, `parent_id`, `title`, `val`, `taxis`) VALUES
(12875, 19, 12867, '文成县', '文成县', 40),
(12876, 19, 12867, '平阳县', '平阳县', 45),
(12877, 19, 12867, '泰顺县', '泰顺县', 50),
(12878, 19, 12867, '洞头县', '洞头县', 55),
(12879, 19, 12867, '苍南县', '苍南县', 60),
(12880, 19, 12838, '嘉兴市', '嘉兴市', 20),
(12881, 19, 12880, '桐乡市', '桐乡市', 5),
(12882, 19, 12880, '平湖市', '平湖市', 10),
(12883, 19, 12880, '嘉善县', '嘉善县', 15),
(12884, 19, 12880, '南湖区', '南湖区', 20),
(12885, 19, 12880, '秀洲区', '秀洲区', 25),
(12886, 19, 12880, '海宁市', '海宁市', 30),
(12887, 19, 12880, '海盐县', '海盐县', 35),
(12888, 19, 12838, '湖州市', '湖州市', 25),
(12889, 19, 12888, '南浔区', '南浔区', 5),
(12890, 19, 12888, '吴兴区', '吴兴区', 10),
(12891, 19, 12888, '长兴县', '长兴县', 15),
(12892, 19, 12888, '德清县', '德清县', 20),
(12893, 19, 12888, '安吉县', '安吉县', 25),
(12894, 19, 12838, '绍兴市', '绍兴市', 30),
(12895, 19, 12894, '柯桥区', '柯桥区', 5),
(12896, 19, 12894, '越城区', '越城区', 10),
(12897, 19, 12894, '诸暨市', '诸暨市', 15),
(12898, 19, 12894, '上虞区', '上虞区', 20),
(12899, 19, 12894, '嵊州市', '嵊州市', 25),
(12900, 19, 12894, '新昌县', '新昌县', 30),
(12901, 19, 12838, '金华市', '金华市', 35),
(12902, 19, 12901, '金东区', '金东区', 5),
(12903, 19, 12901, '婺城区', '婺城区', 10),
(12904, 19, 12901, '兰溪市', '兰溪市', 15),
(12905, 19, 12901, '武义县', '武义县', 20),
(12906, 19, 12901, '浦江县', '浦江县', 25),
(12907, 19, 12901, '磐安县', '磐安县', 30),
(12908, 19, 12901, '义乌市', '义乌市', 35),
(12909, 19, 12901, '永康市', '永康市', 40),
(12910, 19, 12901, '东阳市', '东阳市', 45),
(12911, 19, 12838, '衢州市', '衢州市', 40),
(12912, 19, 12911, '柯城区', '柯城区', 5),
(12913, 19, 12911, '衢江区', '衢江区', 10),
(12914, 19, 12911, '江山市', '江山市', 15),
(12915, 19, 12911, '常山县', '常山县', 20),
(12916, 19, 12911, '开化县', '开化县', 25),
(12917, 19, 12911, '龙游县', '龙游县', 30),
(12918, 19, 12838, '丽水市', '丽水市', 45),
(12919, 19, 12918, '龙泉市', '龙泉市', 5),
(12920, 19, 12918, '缙云县', '缙云县', 10),
(12921, 19, 12918, '遂昌县', '遂昌县', 15),
(12922, 19, 12918, '松阳县', '松阳县', 20),
(12923, 19, 12918, '景宁县', '景宁县', 25),
(12924, 19, 12918, '云和县', '云和县', 30),
(12925, 19, 12918, '青田县', '青田县', 35),
(12926, 19, 12918, '莲都区', '莲都区', 40),
(12927, 19, 12918, '庆元县', '庆元县', 45),
(12928, 19, 12838, '台州市', '台州市', 50),
(12929, 19, 12928, '临海市', '临海市', 5),
(12930, 19, 12928, '三门县', '三门县', 10),
(12931, 19, 12928, '天台县', '天台县', 15),
(12932, 19, 12928, '仙居县', '仙居县', 20),
(12933, 19, 12928, '黄岩区', '黄岩区', 25),
(12934, 19, 12928, '椒江区', '椒江区', 30),
(12935, 19, 12928, '路桥区', '路桥区', 35),
(12936, 19, 12928, '温岭市', '温岭市', 40),
(12937, 19, 12928, '玉环县', '玉环县', 45),
(12938, 19, 12838, '舟山市', '舟山市', 55),
(12939, 19, 12938, '岱山县', '岱山县', 5),
(12940, 19, 12938, '嵊泗县', '嵊泗县', 10),
(12941, 19, 12938, '普陀区', '普陀区', 15),
(12942, 19, 12938, '定海区', '定海区', 20),
(12943, 19, 0, '福建省', '福建省', 80),
(12944, 19, 12943, '福州市', '福州市', 5),
(12945, 19, 12944, '台江区', '台江区', 5),
(12946, 19, 12944, '鼓楼区', '鼓楼区', 10),
(12947, 19, 12944, '晋安区', '晋安区', 15),
(12948, 19, 12944, '仓山区', '仓山区', 20),
(12949, 19, 12944, '马尾区', '马尾区', 25),
(12950, 19, 12944, '福清市', '福清市', 30),
(12951, 19, 12944, '闽侯县', '闽侯县', 35),
(12952, 19, 12944, '长乐市', '长乐市', 40),
(12953, 19, 12944, '平潭县', '平潭县', 45),
(12954, 19, 12944, '连江县', '连江县', 50),
(12955, 19, 12944, '罗源县', '罗源县', 55),
(12956, 19, 12944, '永泰县', '永泰县', 60),
(12957, 19, 12944, '闽清县', '闽清县', 65),
(12958, 19, 12943, '厦门市', '厦门市', 10),
(12959, 19, 12958, '思明区', '思明区', 5),
(12960, 19, 12958, '湖里区', '湖里区', 10),
(12961, 19, 12958, '翔安区', '翔安区', 15),
(12962, 19, 12958, '海沧区', '海沧区', 20),
(12963, 19, 12958, '集美区', '集美区', 25),
(12964, 19, 12958, '同安区', '同安区', 30),
(12965, 19, 12943, '三明市', '三明市', 15),
(12966, 19, 12965, '永安市', '永安市', 5),
(12967, 19, 12965, '明溪县', '明溪县', 10),
(12968, 19, 12965, '将乐县', '将乐县', 15),
(12969, 19, 12965, '大田县', '大田县', 20),
(12970, 19, 12965, '宁化县', '宁化县', 25),
(12971, 19, 12965, '建宁县', '建宁县', 30),
(12972, 19, 12965, '沙县', '沙县', 35),
(12973, 19, 12965, '尤溪县', '尤溪县', 40),
(12974, 19, 12965, '清流县', '清流县', 45),
(12975, 19, 12965, '泰宁县', '泰宁县', 50),
(12976, 19, 12965, '梅列区', '梅列区', 55),
(12977, 19, 12965, '三元区', '三元区', 60),
(12978, 19, 12943, '莆田市', '莆田市', 20),
(12979, 19, 12978, '仙游县', '仙游县', 5),
(12980, 19, 12978, '城厢区', '城厢区', 10),
(12981, 19, 12978, '荔城区', '荔城区', 15),
(12982, 19, 12978, '秀屿区', '秀屿区', 20),
(12983, 19, 12978, '涵江区', '涵江区', 25),
(12984, 19, 12943, '泉州市', '泉州市', 25),
(12985, 19, 12984, '泉港区', '泉港区', 5),
(12986, 19, 12984, '石狮市', '石狮市', 10),
(12987, 19, 12984, '南安市', '南安市', 15),
(12988, 19, 12984, '惠安县', '惠安县', 20),
(12989, 19, 12984, '安溪县', '安溪县', 25),
(12990, 19, 12984, '德化县', '德化县', 30),
(12991, 19, 12984, '永春县', '永春县', 35),
(12992, 19, 12984, '金门县', '金门县', 40),
(12993, 19, 12984, '洛江区', '洛江区', 45),
(12994, 19, 12984, '鲤城区', '鲤城区', 50),
(12995, 19, 12984, '丰泽区', '丰泽区', 55),
(12996, 19, 12984, '晋江市', '晋江市', 60),
(12997, 19, 12943, '漳州市', '漳州市', 30),
(12998, 19, 12997, '芗城区', '芗城区', 5),
(12999, 19, 12997, '龙文区', '龙文区', 10),
(13000, 19, 12997, '龙海市', '龙海市', 15),
(13001, 19, 12997, '平和县', '平和县', 20),
(13002, 19, 12997, '南靖县', '南靖县', 25),
(13003, 19, 12997, '诏安县', '诏安县', 30),
(13004, 19, 12997, '漳浦县', '漳浦县', 35),
(13005, 19, 12997, '华安县', '华安县', 40),
(13006, 19, 12997, '云霄县', '云霄县', 45),
(13007, 19, 12997, '东山县', '东山县', 50),
(13008, 19, 12997, '长泰县', '长泰县', 55),
(13009, 19, 12943, '南平市', '南平市', 35),
(13010, 19, 13009, '建瓯市', '建瓯市', 5),
(13011, 19, 13009, '邵武市', '邵武市', 10),
(13012, 19, 13009, '武夷山市', '武夷山市', 15),
(13013, 19, 13009, '建阳市', '建阳市', 20),
(13014, 19, 13009, '松溪县', '松溪县', 25),
(13015, 19, 13009, '顺昌县', '顺昌县', 30),
(13016, 19, 13009, '浦城县', '浦城县', 35),
(13017, 19, 13009, '政和县', '政和县', 40),
(13018, 19, 13009, '光泽县', '光泽县', 45),
(13019, 19, 13009, '延平区', '延平区', 50),
(13020, 19, 12943, '龙岩市', '龙岩市', 40),
(13021, 19, 13020, '新罗区', '新罗区', 5),
(13022, 19, 13020, '漳平市', '漳平市', 10),
(13023, 19, 13020, '长汀县', '长汀县', 15),
(13024, 19, 13020, '武平县', '武平县', 20),
(13025, 19, 13020, '永定县', '永定县', 25),
(13026, 19, 13020, '上杭县', '上杭县', 30),
(13027, 19, 13020, '连城县', '连城县', 35),
(13028, 19, 12943, '宁德市', '宁德市', 45),
(13029, 19, 13028, '蕉城区', '蕉城区', 5),
(13030, 19, 13028, '东侨开发区', '东侨开发区', 10),
(13031, 19, 13028, '福安市', '福安市', 15),
(13032, 19, 13028, '福鼎市', '福鼎市', 20),
(13033, 19, 13028, '寿宁县', '寿宁县', 25),
(13034, 19, 13028, '霞浦县', '霞浦县', 30),
(13035, 19, 13028, '柘荣县', '柘荣县', 35),
(13036, 19, 13028, '屏南县', '屏南县', 40),
(13037, 19, 13028, '古田县', '古田县', 45),
(13038, 19, 13028, '周宁县', '周宁县', 50),
(13039, 19, 0, '湖北省', '湖北省', 85),
(13040, 19, 13039, '武汉市', '武汉市', 5),
(13041, 19, 13040, '硚口区', '硚口区', 5),
(13042, 19, 13040, '武昌区', '武昌区', 10),
(13043, 19, 13040, '武汉经济技术开发区', '武汉经济技术开发区', 15),
(13044, 19, 13040, '江岸区', '江岸区', 20),
(13045, 19, 13040, '江汉区', '江汉区', 25),
(13046, 19, 13040, '蔡甸区', '蔡甸区', 30),
(13047, 19, 13040, '江夏区', '江夏区', 35),
(13048, 19, 13040, '新洲区', '新洲区', 40),
(13049, 19, 13040, '黄陂区', '黄陂区', 45),
(13050, 19, 13040, '汉阳区', '汉阳区', 50),
(13051, 19, 13040, '青山区', '青山区', 55),
(13052, 19, 13040, '洪山区', '洪山区', 60),
(13053, 19, 13040, '汉南区', '汉南区', 65),
(13054, 19, 13040, '东西湖区', '东西湖区', 70),
(13055, 19, 13039, '黄石市', '黄石市', 10),
(13056, 19, 13055, '黄石港区', '黄石港区', 5),
(13057, 19, 13055, '下陆区', '下陆区', 10),
(13058, 19, 13055, '西塞山区', '西塞山区', 15),
(13059, 19, 13055, '铁山区', '铁山区', 20),
(13060, 19, 13055, '大冶市', '大冶市', 25),
(13061, 19, 13055, '阳新县', '阳新县', 30),
(13062, 19, 13055, '经济技术开发区', '经济技术开发区', 35),
(13063, 19, 13039, '襄阳市', '襄阳市', 15),
(13064, 19, 13063, '老河口市', '老河口市', 5),
(13065, 19, 13063, '枣阳市', '枣阳市', 10),
(13066, 19, 13063, '宜城市', '宜城市', 15),
(13067, 19, 13063, '南漳县', '南漳县', 20),
(13068, 19, 13063, '保康县', '保康县', 25),
(13069, 19, 13063, '谷城县', '谷城县', 30),
(13070, 19, 13063, '樊城区', '樊城区', 35),
(13071, 19, 13063, '襄城区', '襄城区', 40),
(13072, 19, 13063, '襄州区', '襄州区', 45),
(13073, 19, 13039, '十堰市', '十堰市', 20),
(13074, 19, 13073, '丹江口市', '丹江口市', 5),
(13075, 19, 13073, '房县', '房县', 10),
(13076, 19, 13073, '竹山县', '竹山县', 15),
(13077, 19, 13073, '竹溪县', '竹溪县', 20),
(13078, 19, 13073, '郧县', '郧县', 25),
(13079, 19, 13073, '郧西县', '郧西县', 30),
(13080, 19, 13073, '茅箭区', '茅箭区', 35),
(13081, 19, 13073, '张湾区', '张湾区', 40),
(13082, 19, 13039, '荆州市', '荆州市', 25),
(13083, 19, 13082, '沙市区', '沙市区', 5),
(13084, 19, 13082, '荆州区', '荆州区', 10),
(13085, 19, 13082, '江陵县', '江陵县', 15),
(13086, 19, 13082, '洪湖市', '洪湖市', 20),
(13087, 19, 13082, '石首市', '石首市', 25),
(13088, 19, 13082, '松滋市', '松滋市', 30),
(13089, 19, 13082, '监利县', '监利县', 35),
(13090, 19, 13082, '公安县', '公安县', 40),
(13091, 19, 13039, '宜昌市', '宜昌市', 30),
(13092, 19, 13091, '伍家岗区', '伍家岗区', 5),
(13093, 19, 13091, '西陵区', '西陵区', 10),
(13094, 19, 13091, '宜都市', '宜都市', 15),
(13095, 19, 13091, '猇亭区', '猇亭区', 20),
(13096, 19, 13091, '点军区', '点军区', 25),
(13097, 19, 13091, '当阳市', '当阳市', 30),
(13098, 19, 13091, '枝江市', '枝江市', 35),
(13099, 19, 13091, '夷陵区', '夷陵区', 40),
(13100, 19, 13091, '秭归县', '秭归县', 45),
(13101, 19, 13091, '兴山县', '兴山县', 50),
(13102, 19, 13091, '远安县', '远安县', 55),
(13103, 19, 13091, '五峰土家族自治县', '五峰土家族自治县', 60),
(13104, 19, 13091, '长阳土家族自治县', '长阳土家族自治县', 65),
(13105, 19, 13039, '孝感市', '孝感市', 35),
(13106, 19, 13105, '汉川市', '汉川市', 5),
(13107, 19, 13105, '云梦县', '云梦县', 10),
(13108, 19, 13105, '大悟县', '大悟县', 15),
(13109, 19, 13105, '孝昌县', '孝昌县', 20),
(13110, 19, 13105, '孝南区', '孝南区', 25),
(13111, 19, 13105, '应城市', '应城市', 30),
(13112, 19, 13105, '安陆市', '安陆市', 35),
(13113, 19, 13039, '黄冈市', '黄冈市', 40),
(13114, 19, 13113, '黄州区', '黄州区', 5),
(13115, 19, 13113, '蕲春县', '蕲春县', 10),
(13116, 19, 13113, '麻城市', '麻城市', 15),
(13117, 19, 13113, '武穴市', '武穴市', 20),
(13118, 19, 13113, '浠水县', '浠水县', 25),
(13119, 19, 13113, '红安县', '红安县', 30),
(13120, 19, 13113, '罗田县', '罗田县', 35),
(13121, 19, 13113, '黄梅县', '黄梅县', 40),
(13122, 19, 13113, '英山县', '英山县', 45),
(13123, 19, 13113, '团风县', '团风县', 50),
(13124, 19, 13039, '咸宁市', '咸宁市', 45),
(13125, 19, 13124, '咸安区', '咸安区', 5),
(13126, 19, 13124, '赤壁市', '赤壁市', 10),
(13127, 19, 13124, '嘉鱼县', '嘉鱼县', 15),
(13128, 19, 13124, '通山县', '通山县', 20),
(13129, 19, 13124, '崇阳县', '崇阳县', 25),
(13130, 19, 13124, '通城县', '通城县', 30),
(13131, 19, 13039, '恩施州', '恩施州', 50),
(13132, 19, 13131, '恩施市', '恩施市', 5),
(13133, 19, 13131, '利川市', '利川市', 10),
(13134, 19, 13131, '建始县', '建始县', 15),
(13135, 19, 13131, '来凤县', '来凤县', 20),
(13136, 19, 13131, '巴东县', '巴东县', 25),
(13137, 19, 13131, '鹤峰县', '鹤峰县', 30),
(13138, 19, 13131, '宣恩县', '宣恩县', 35),
(13139, 19, 13131, '咸丰县', '咸丰县', 40),
(13140, 19, 13039, '鄂州市', '鄂州市', 55),
(13141, 19, 13140, '梁子湖区', '梁子湖区', 5),
(13142, 19, 13140, '华容区', '华容区', 10),
(13143, 19, 13140, '鄂城区', '鄂城区', 15),
(13144, 19, 13039, '荆门市', '荆门市', 60),
(13145, 19, 13144, '东宝区', '东宝区', 5),
(13146, 19, 13144, '掇刀区', '掇刀区', 10),
(13147, 19, 13144, '钟祥市', '钟祥市', 15),
(13148, 19, 13144, '京山县', '京山县', 20),
(13149, 19, 13144, '沙洋县', '沙洋县', 25),
(13150, 19, 13039, '随州市', '随州市', 65),
(13151, 19, 13150, '曾都区', '曾都区', 5),
(13152, 19, 13150, '广水市', '广水市', 10),
(13153, 19, 13150, '随县', '随县', 15),
(13154, 19, 13039, '潜江市', '潜江市', 70),
(13155, 19, 13154, '园林', '园林', 5),
(13156, 19, 13154, '杨市', '杨市', 10),
(13157, 19, 13154, '周矶', '周矶', 15),
(13158, 19, 13154, '广华', '广华', 20),
(13159, 19, 13154, '泰丰', '泰丰', 25),
(13160, 19, 13154, '竹根滩镇', '竹根滩镇', 30),
(13161, 19, 13154, '高石碑镇', '高石碑镇', 35),
(13162, 19, 13154, '积玉口镇', '积玉口镇', 40),
(13163, 19, 13154, '渔洋镇', '渔洋镇', 45),
(13164, 19, 13154, '王场镇', '王场镇', 50),
(13165, 19, 13154, '熊口镇', '熊口镇', 55),
(13166, 19, 13154, '老新镇', '老新镇', 60),
(13167, 19, 13154, '浩口镇', '浩口镇', 65),
(13168, 19, 13154, '张金镇', '张金镇', 70),
(13169, 19, 13154, '龙湾镇', '龙湾镇', 75),
(13170, 19, 13154, '江汉石油管理局', '江汉石油管理局', 80),
(13171, 19, 13154, '潜江经济开发区', '潜江经济开发区', 85),
(13172, 19, 13154, '西大垸管理区', '西大垸管理区', 90),
(13173, 19, 13154, '运粮湖管理区', '运粮湖管理区', 95),
(13174, 19, 13154, '周矶管理区', '周矶管理区', 100),
(13175, 19, 13154, '后湖管理区', '后湖管理区', 105),
(13176, 19, 13154, '熊口管理区', '熊口管理区', 110),
(13177, 19, 13154, '总口管理区', '总口管理区', 115),
(13178, 19, 13154, '高场原种场', '高场原种场', 120),
(13179, 19, 13154, '浩口原种场', '浩口原种场', 125),
(13180, 19, 13039, '天门市', '天门市', 75),
(13181, 19, 13180, '侨乡街道开发区', '侨乡街道开发区', 5),
(13182, 19, 13180, '竟陵街道', '竟陵街道', 10),
(13183, 19, 13180, '杨林街道', '杨林街道', 15),
(13184, 19, 13180, '佛子山镇', '佛子山镇', 20),
(13185, 19, 13180, '多宝镇', '多宝镇', 25),
(13186, 19, 13180, '拖市镇', '拖市镇', 30),
(13187, 19, 13180, '张港镇', '张港镇', 35),
(13188, 19, 13180, '蒋场镇', '蒋场镇', 40),
(13189, 19, 13180, '汪场镇', '汪场镇', 45),
(13190, 19, 13180, '渔薪镇', '渔薪镇', 50),
(13191, 19, 13180, '黄潭镇', '黄潭镇', 55),
(13192, 19, 13180, '岳口镇', '岳口镇', 60),
(13193, 19, 13180, '横林镇', '横林镇', 65),
(13194, 19, 13180, '彭市镇', '彭市镇', 70),
(13195, 19, 13180, '麻洋镇', '麻洋镇', 75),
(13196, 19, 13180, '多祥镇', '多祥镇', 80),
(13197, 19, 13180, '干驿镇', '干驿镇', 85),
(13198, 19, 13180, '马湾镇', '马湾镇', 90),
(13199, 19, 13180, '卢市镇', '卢市镇', 95),
(13200, 19, 13180, '小板镇', '小板镇', 100),
(13201, 19, 13180, '九真镇', '九真镇', 105),
(13202, 19, 13180, '皂市镇', '皂市镇', 110),
(13203, 19, 13180, '胡市镇', '胡市镇', 115),
(13204, 19, 13180, '石河镇', '石河镇', 120),
(13205, 19, 13180, '净潭乡', '净潭乡', 125),
(13206, 19, 13180, '蒋湖农场', '蒋湖农场', 130),
(13207, 19, 13180, '白茅湖农场', '白茅湖农场', 135),
(13208, 19, 13180, '沉湖管委会', '沉湖管委会', 140),
(13209, 19, 13039, '仙桃市', '仙桃市', 80),
(13210, 19, 13209, '城区', '城区', 5),
(13211, 19, 13209, '郑场镇', '郑场镇', 10),
(13212, 19, 13209, '毛嘴镇', '毛嘴镇', 15),
(13213, 19, 13209, '豆河镇', '豆河镇', 20),
(13214, 19, 13209, '三伏潭镇', '三伏潭镇', 25),
(13215, 19, 13209, '胡场镇', '胡场镇', 30),
(13216, 19, 13209, '长埫口镇', '长埫口镇', 35),
(13217, 19, 13209, '西流河镇', '西流河镇', 40),
(13218, 19, 13209, '沙湖镇', '沙湖镇', 45),
(13219, 19, 13209, '杨林尾镇', '杨林尾镇', 50),
(13220, 19, 13209, '彭场镇', '彭场镇', 55),
(13221, 19, 13209, '张沟镇', '张沟镇', 60),
(13222, 19, 13209, '郭河镇', '郭河镇', 65),
(13223, 19, 13209, '沔城镇', '沔城镇', 70),
(13224, 19, 13209, '通海口镇', '通海口镇', 75),
(13225, 19, 13209, '陈场镇', '陈场镇', 80),
(13226, 19, 13209, '工业园区', '工业园区', 85),
(13227, 19, 13209, '九合垸原种场', '九合垸原种场', 90),
(13228, 19, 13209, '沙湖原种场', '沙湖原种场', 95),
(13229, 19, 13209, '排湖渔场', '排湖渔场', 100),
(13230, 19, 13209, '五湖渔场', '五湖渔场', 105),
(13231, 19, 13209, '赵西垸林场', '赵西垸林场', 110),
(13232, 19, 13209, '刘家垸林场', '刘家垸林场', 115),
(13233, 19, 13209, '畜禽良种场', '畜禽良种场', 120),
(13234, 19, 13039, '神农架林区', '神农架林区', 85),
(13235, 19, 13234, '松柏镇', '松柏镇', 5),
(13236, 19, 13234, '阳日镇', '阳日镇', 10),
(13237, 19, 13234, '木鱼镇', '木鱼镇', 15),
(13238, 19, 13234, '红坪镇', '红坪镇', 20),
(13239, 19, 13234, '新华镇', '新华镇', 25),
(13240, 19, 13234, '宋洛乡', '宋洛乡', 30),
(13241, 19, 13234, '九湖乡', '九湖乡', 35),
(13242, 19, 13234, '下谷坪乡', '下谷坪乡', 40),
(13243, 19, 0, '湖南省', '湖南省', 90),
(13244, 19, 13243, '长沙市', '长沙市', 5),
(13245, 19, 13244, '芙蓉区', '芙蓉区', 5),
(13246, 19, 13244, '岳麓区', '岳麓区', 10),
(13247, 19, 13244, '雨花区', '雨花区', 15),
(13248, 19, 13244, '开福区', '开福区', 20),
(13249, 19, 13244, '天心区', '天心区', 25),
(13250, 19, 13244, '浏阳市', '浏阳市', 30),
(13251, 19, 13244, '长沙县', '长沙县', 35),
(13252, 19, 13244, '宁乡县', '宁乡县', 40),
(13253, 19, 13244, '望城区', '望城区', 45),
(13254, 19, 13243, '株洲市', '株洲市', 10),
(13255, 19, 13254, '天元区', '天元区', 5),
(13256, 19, 13254, '石峰区', '石峰区', 10),
(13257, 19, 13254, '芦淞区', '芦淞区', 15),
(13258, 19, 13254, '荷塘区', '荷塘区', 20),
(13259, 19, 13254, '醴陵市', '醴陵市', 25),
(13260, 19, 13254, '株洲县', '株洲县', 30),
(13261, 19, 13254, '攸县', '攸县', 35),
(13262, 19, 13254, '茶陵县', '茶陵县', 40),
(13263, 19, 13254, '炎陵县', '炎陵县', 45),
(13264, 19, 13243, '湘潭市', '湘潭市', 15),
(13265, 19, 13264, '雨湖区', '雨湖区', 5),
(13266, 19, 13264, '岳塘区', '岳塘区', 10),
(13267, 19, 13264, '湘乡市', '湘乡市', 15),
(13268, 19, 13264, '湘潭县', '湘潭县', 20),
(13269, 19, 13264, '韶山市', '韶山市', 25),
(13270, 19, 13243, '韶山市', '韶山市', 20),
(13271, 19, 13270, '韶山市区内', '韶山市区内', 5),
(13272, 19, 13243, '衡阳市', '衡阳市', 25),
(13273, 19, 13272, '蒸湘区', '蒸湘区', 5),
(13274, 19, 13272, '石鼓区', '石鼓区', 10),
(13275, 19, 13272, '珠晖区', '珠晖区', 15),
(13276, 19, 13272, '雁峰区', '雁峰区', 20),
(13277, 19, 13272, '常宁市', '常宁市', 25),
(13278, 19, 13272, '衡阳县', '衡阳县', 30),
(13279, 19, 13272, '耒阳市', '耒阳市', 35),
(13280, 19, 13272, '衡东县', '衡东县', 40),
(13281, 19, 13272, '衡南县', '衡南县', 45),
(13282, 19, 13272, '衡山县', '衡山县', 50),
(13283, 19, 13272, '祁东县', '祁东县', 55),
(13284, 19, 13272, '南岳区', '南岳区', 60),
(13285, 19, 13243, '邵阳市', '邵阳市', 30),
(13286, 19, 13285, '大祥区', '大祥区', 5),
(13287, 19, 13285, '双清区', '双清区', 10),
(13288, 19, 13285, '北塔区', '北塔区', 15),
(13289, 19, 13285, '武冈市', '武冈市', 20),
(13290, 19, 13285, '邵东县', '邵东县', 25),
(13291, 19, 13285, '洞口县', '洞口县', 30),
(13292, 19, 13285, '新邵县', '新邵县', 35),
(13293, 19, 13285, '绥宁县', '绥宁县', 40),
(13294, 19, 13285, '新宁县', '新宁县', 45),
(13295, 19, 13285, '邵阳县', '邵阳县', 50),
(13296, 19, 13285, '隆回县', '隆回县', 55),
(13297, 19, 13285, '城步县', '城步县', 60),
(13298, 19, 13243, '岳阳市', '岳阳市', 35),
(13299, 19, 13298, '岳阳楼区', '岳阳楼区', 5),
(13300, 19, 13298, '君山区', '君山区', 10),
(13301, 19, 13298, '云溪区', '云溪区', 15),
(13302, 19, 13298, '临湘市', '临湘市', 20),
(13303, 19, 13298, '汨罗市', '汨罗市', 25),
(13304, 19, 13298, '岳阳县', '岳阳县', 30),
(13305, 19, 13298, '湘阴县', '湘阴县', 35),
(13306, 19, 13298, '华容县', '华容县', 40),
(13307, 19, 13298, '平江县', '平江县', 45),
(13308, 19, 13243, '常德市', '常德市', 40),
(13309, 19, 13308, '汉寿县', '汉寿县', 5),
(13310, 19, 13308, '石门县', '石门县', 10),
(13311, 19, 13308, '安乡县', '安乡县', 15),
(13312, 19, 13308, '鼎城区', '鼎城区', 20),
(13313, 19, 13308, '武陵区', '武陵区', 25),
(13314, 19, 13308, '津市市', '津市市', 30),
(13315, 19, 13308, '澧县', '澧县', 35),
(13316, 19, 13308, '临澧县', '临澧县', 40),
(13317, 19, 13308, '桃源县', '桃源县', 45),
(13318, 19, 13243, '张家界市', '张家界市', 45),
(13319, 19, 13318, '慈利县', '慈利县', 5),
(13320, 19, 13318, '桑植县', '桑植县', 10),
(13321, 19, 13318, '武陵源区', '武陵源区', 15),
(13322, 19, 13318, '永定区', '永定区', 20),
(13323, 19, 13243, '郴州市', '郴州市', 50),
(13324, 19, 13323, '资兴市', '资兴市', 5),
(13325, 19, 13323, '宜章县', '宜章县', 10),
(13326, 19, 13323, '安仁县', '安仁县', 15),
(13327, 19, 13323, '汝城县', '汝城县', 20),
(13328, 19, 13323, '嘉禾县', '嘉禾县', 25),
(13329, 19, 13323, '临武县', '临武县', 30),
(13330, 19, 13323, '桂东县', '桂东县', 35),
(13331, 19, 13323, '永兴县', '永兴县', 40),
(13332, 19, 13323, '桂阳县', '桂阳县', 45),
(13333, 19, 13323, '北湖区', '北湖区', 50),
(13334, 19, 13323, '苏仙区', '苏仙区', 55),
(13335, 19, 13243, '益阳市', '益阳市', 55),
(13336, 19, 13335, '南县', '南县', 5),
(13337, 19, 13335, '桃江县', '桃江县', 10),
(13338, 19, 13335, '安化县', '安化县', 15),
(13339, 19, 13335, '赫山区', '赫山区', 20),
(13340, 19, 13335, '资阳区', '资阳区', 25),
(13341, 19, 13335, '沅江市', '沅江市', 30),
(13342, 19, 13243, '永州市', '永州市', 60),
(13343, 19, 13342, '冷水滩区', '冷水滩区', 5),
(13344, 19, 13342, '祁阳县', '祁阳县', 10),
(13345, 19, 13342, '双牌县', '双牌县', 15),
(13346, 19, 13342, '道县', '道县', 20),
(13347, 19, 13342, '江永县', '江永县', 25),
(13348, 19, 13342, '江华县', '江华县', 30),
(13349, 19, 13342, '宁远县', '宁远县', 35),
(13350, 19, 13342, '新田县', '新田县', 40),
(13351, 19, 13342, '蓝山县', '蓝山县', 45),
(13352, 19, 13342, '东安县', '东安县', 50),
(13353, 19, 13342, '零陵区', '零陵区', 55),
(13354, 19, 13243, '怀化市', '怀化市', 65),
(13355, 19, 13354, '鹤城区', '鹤城区', 5),
(13356, 19, 13354, '洪江市', '洪江市', 10),
(13357, 19, 13354, '会同县', '会同县', 15),
(13358, 19, 13354, '溆浦县', '溆浦县', 20),
(13359, 19, 13354, '中方县', '中方县', 25),
(13360, 19, 13354, '辰溪县', '辰溪县', 30),
(13361, 19, 13354, '靖州县', '靖州县', 35),
(13362, 19, 13354, '通道县', '通道县', 40),
(13363, 19, 13354, '芷江县', '芷江县', 45),
(13364, 19, 13354, '新晃县', '新晃县', 50),
(13365, 19, 13354, '麻阳县', '麻阳县', 55),
(13366, 19, 13354, '沅陵县', '沅陵县', 60),
(13367, 19, 13243, '娄底市', '娄底市', 70),
(13368, 19, 13367, '娄星区', '娄星区', 5),
(13369, 19, 13367, '冷水江市', '冷水江市', 10),
(13370, 19, 13367, '涟源市', '涟源市', 15),
(13371, 19, 13367, '新化县', '新化县', 20),
(13372, 19, 13367, '双峰县', '双峰县', 25),
(13373, 19, 13243, '湘西州', '湘西州', 75),
(13374, 19, 13373, '吉首市', '吉首市', 5),
(13375, 19, 13373, '古丈县', '古丈县', 10),
(13376, 19, 13373, '龙山县', '龙山县', 15),
(13377, 19, 13373, '永顺县', '永顺县', 20),
(13378, 19, 13373, '泸溪县', '泸溪县', 25),
(13379, 19, 13373, '凤凰县', '凤凰县', 30),
(13380, 19, 13373, '花垣县', '花垣县', 35),
(13381, 19, 13373, '保靖县', '保靖县', 40),
(13382, 19, 0, '广东省', '广东省', 95),
(13383, 19, 13382, '广州市', '广州市', 5),
(13384, 19, 13383, '天河区', '天河区', 5),
(13385, 19, 13383, '海珠区', '海珠区', 10),
(13386, 19, 13383, '荔湾区', '荔湾区', 15),
(13387, 19, 13383, '越秀区', '越秀区', 20),
(13388, 19, 13383, '番禺区', '番禺区', 25),
(13389, 19, 13383, '花都区', '花都区', 30),
(13390, 19, 13383, '萝岗区', '萝岗区', 35),
(13391, 19, 13383, '白云区', '白云区', 40),
(13392, 19, 13383, '南沙区', '南沙区', 45),
(13393, 19, 13383, '黄埔区', '黄埔区', 50),
(13394, 19, 13383, '增城市', '增城市', 55),
(13395, 19, 13383, '从化市', '从化市', 60),
(13396, 19, 13383, '广州大学城', '广州大学城', 65),
(13397, 19, 13382, '深圳市', '深圳市', 10),
(13398, 19, 13397, '罗湖区', '罗湖区', 5),
(13399, 19, 13397, '福田区', '福田区', 10),
(13400, 19, 13397, '南山区', '南山区', 15),
(13401, 19, 13397, '宝安区', '宝安区', 20),
(13402, 19, 13397, '光明新区', '光明新区', 25),
(13403, 19, 13397, '龙岗区', '龙岗区', 30),
(13404, 19, 13397, '坪山新区', '坪山新区', 35),
(13405, 19, 13397, '盐田区', '盐田区', 40),
(13406, 19, 13397, '龙华新区', '龙华新区', 45),
(13407, 19, 13397, '大鹏新区', '大鹏新区', 50),
(13408, 19, 13382, '珠海市', '珠海市', 15),
(13409, 19, 13408, '斗门区', '斗门区', 5),
(13410, 19, 13408, '金湾区', '金湾区', 10),
(13411, 19, 13408, '香洲区', '香洲区', 15),
(13412, 19, 13382, '汕头市', '汕头市', 20),
(13413, 19, 13412, '龙湖区', '龙湖区', 5),
(13414, 19, 13412, '金平区', '金平区', 10),
(13415, 19, 13412, '澄海区', '澄海区', 15),
(13416, 19, 13412, '潮阳区', '潮阳区', 20),
(13417, 19, 13412, '潮南区', '潮南区', 25),
(13418, 19, 13412, '濠江区', '濠江区', 30),
(13419, 19, 13412, '南澳县', '南澳县', 35),
(13420, 19, 13382, '韶关市', '韶关市', 25),
(13421, 19, 13420, '武江区', '武江区', 5),
(13422, 19, 13420, '浈江区', '浈江区', 10),
(13423, 19, 13420, '南雄市', '南雄市', 15),
(13424, 19, 13420, '乐昌市', '乐昌市', 20),
(13425, 19, 13420, '仁化县', '仁化县', 25),
(13426, 19, 13420, '始兴县', '始兴县', 30),
(13427, 19, 13420, '翁源县', '翁源县', 35),
(13428, 19, 13420, '新丰县', '新丰县', 40),
(13429, 19, 13420, '乳源瑶族自治县', '乳源瑶族自治县', 45),
(13430, 19, 13420, '曲江区', '曲江区', 50),
(13431, 19, 13382, '河源市', '河源市', 30),
(13432, 19, 13431, '和平县', '和平县', 5),
(13433, 19, 13431, '龙川县', '龙川县', 10),
(13434, 19, 13431, '紫金县', '紫金县', 15),
(13435, 19, 13431, '连平县', '连平县', 20),
(13436, 19, 13431, '源城区', '源城区', 25),
(13437, 19, 13431, '东源县', '东源县', 30),
(13438, 19, 13382, '梅州市', '梅州市', 35),
(13439, 19, 13438, '梅江区', '梅江区', 5),
(13440, 19, 13438, '兴宁市', '兴宁市', 10),
(13441, 19, 13438, '梅县', '梅县', 15),
(13442, 19, 13438, '蕉岭县', '蕉岭县', 20),
(13443, 19, 13438, '大埔县', '大埔县', 25),
(13444, 19, 13438, '丰顺县', '丰顺县', 30),
(13445, 19, 13438, '五华县', '五华县', 35),
(13446, 19, 13438, '平远县', '平远县', 40),
(13447, 19, 13382, '惠州市', '惠州市', 40),
(13448, 19, 13447, '惠阳区', '惠阳区', 5),
(13449, 19, 13447, '大亚湾区', '大亚湾区', 10),
(13450, 19, 13447, '惠城区', '惠城区', 15),
(13451, 19, 13447, '惠东县', '惠东县', 20),
(13452, 19, 13447, '博罗县', '博罗县', 25),
(13453, 19, 13447, '龙门县', '龙门县', 30),
(13454, 19, 13382, '汕尾市', '汕尾市', 45),
(13455, 19, 13454, '城区', '城区', 5),
(13456, 19, 13454, '陆丰市', '陆丰市', 10),
(13457, 19, 13454, '陆河县', '陆河县', 15),
(13458, 19, 13454, '海丰县', '海丰县', 20),
(13459, 19, 13382, '东莞市', '东莞市', 50),
(13460, 19, 13459, '长安镇', '长安镇', 5),
(13461, 19, 13459, '莞城区', '莞城区', 10),
(13462, 19, 13459, '南城区', '南城区', 15),
(13463, 19, 13459, '寮步镇', '寮步镇', 20),
(13464, 19, 13459, '大岭山镇', '大岭山镇', 25),
(13465, 19, 13459, '横沥镇', '横沥镇', 30),
(13466, 19, 13459, '常平镇', '常平镇', 35),
(13467, 19, 13459, '厚街镇', '厚街镇', 40),
(13468, 19, 13459, '万江区', '万江区', 45),
(13469, 19, 13459, '樟木头镇', '樟木头镇', 50),
(13470, 19, 13459, '塘厦镇', '塘厦镇', 55),
(13471, 19, 13459, '凤岗镇', '凤岗镇', 60),
(13472, 19, 13459, '大朗镇', '大朗镇', 65),
(13473, 19, 13459, '东坑镇', '东坑镇', 70),
(13474, 19, 13459, '清溪镇', '清溪镇', 75),
(13475, 19, 13459, '企石镇', '企石镇', 80),
(13476, 19, 13459, '茶山镇', '茶山镇', 85),
(13477, 19, 13459, '东城区', '东城区', 90),
(13478, 19, 13459, '虎门镇', '虎门镇', 95),
(13479, 19, 13459, '黄江镇', '黄江镇', 100),
(13480, 19, 13459, '石排镇', '石排镇', 105),
(13481, 19, 13459, '道滘镇', '道滘镇', 110),
(13482, 19, 13459, '沙田镇', '沙田镇', 115),
(13483, 19, 13459, '高埗镇', '高埗镇', 120),
(13484, 19, 13459, '石龙镇', '石龙镇', 125),
(13485, 19, 13459, '石碣镇', '石碣镇', 130),
(13486, 19, 13459, '洪梅镇', '洪梅镇', 135),
(13487, 19, 13459, '麻涌镇', '麻涌镇', 140),
(13488, 19, 13459, '松山湖', '松山湖', 145),
(13489, 19, 13459, '桥头镇', '桥头镇', 150),
(13490, 19, 13459, '望牛墩镇', '望牛墩镇', 155),
(13491, 19, 13459, '中堂镇', '中堂镇', 160),
(13492, 19, 13459, '谢岗镇', '谢岗镇', 165),
(13493, 19, 13382, '中山市', '中山市', 55),
(13494, 19, 13493, '城区', '城区', 5),
(13495, 19, 13493, '火炬开发区', '火炬开发区', 10),
(13496, 19, 13493, '小榄镇', '小榄镇', 15),
(13497, 19, 13493, '古镇', '古镇', 20),
(13498, 19, 13493, '三乡镇', '三乡镇', 25),
(13499, 19, 13493, '民众镇', '民众镇', 30),
(13500, 19, 13493, '东凤镇', '东凤镇', 35),
(13501, 19, 13493, '板芙镇', '板芙镇', 40),
(13502, 19, 13493, '神湾镇', '神湾镇', 45),
(13503, 19, 13493, '横栏镇', '横栏镇', 50),
(13504, 19, 13493, '港口镇', '港口镇', 55),
(13505, 19, 13493, '三角镇', '三角镇', 60),
(13506, 19, 13493, '大涌镇', '大涌镇', 65),
(13507, 19, 13493, '南头镇', '南头镇', 70),
(13508, 19, 13493, '沙溪镇', '沙溪镇', 75),
(13509, 19, 13493, '坦洲镇', '坦洲镇', 80),
(13510, 19, 13493, '黄圃镇', '黄圃镇', 85),
(13511, 19, 13493, '五桂山镇', '五桂山镇', 90),
(13512, 19, 13493, '南朗镇', '南朗镇', 95),
(13513, 19, 13493, '沙朗镇', '沙朗镇', 100),
(13514, 19, 13493, '阜沙镇', '阜沙镇', 105),
(13515, 19, 13493, '东升镇', '东升镇', 110),
(13516, 19, 13382, '江门市', '江门市', 60),
(13517, 19, 13516, '台山市', '台山市', 5),
(13518, 19, 13516, '新会区', '新会区', 10),
(13519, 19, 13516, '鹤山市', '鹤山市', 15),
(13520, 19, 13516, '江海区', '江海区', 20),
(13521, 19, 13516, '蓬江区', '蓬江区', 25),
(13522, 19, 13516, '开平市', '开平市', 30),
(13523, 19, 13516, '恩平市', '恩平市', 35),
(13524, 19, 13382, '佛山市', '佛山市', 65),
(13525, 19, 13524, '顺德区', '顺德区', 5),
(13526, 19, 13524, '禅城区', '禅城区', 10),
(13527, 19, 13524, '高明区', '高明区', 15),
(13528, 19, 13524, '三水区', '三水区', 20),
(13529, 19, 13524, '南海区', '南海区', 25),
(13530, 19, 13382, '阳江市', '阳江市', 70),
(13531, 19, 13530, '江城区', '江城区', 5),
(13532, 19, 13530, '阳东县', '阳东县', 10),
(13533, 19, 13530, '阳春市', '阳春市', 15),
(13534, 19, 13530, '阳西县', '阳西县', 20),
(13535, 19, 13382, '湛江市', '湛江市', 75),
(13536, 19, 13535, '赤坎区', '赤坎区', 5),
(13537, 19, 13535, '霞山区', '霞山区', 10),
(13538, 19, 13535, '经济技术开发区', '经济技术开发区', 15),
(13539, 19, 13535, '麻章区', '麻章区', 20),
(13540, 19, 13535, '遂溪县', '遂溪县', 25),
(13541, 19, 13535, '廉江市', '廉江市', 30),
(13542, 19, 13535, '坡头区', '坡头区', 35),
(13543, 19, 13535, '雷州市', '雷州市', 40),
(13544, 19, 13535, '吴川市', '吴川市', 45),
(13545, 19, 13535, '徐闻县', '徐闻县', 50),
(13546, 19, 13382, '茂名市', '茂名市', 80),
(13547, 19, 13546, '茂南区', '茂南区', 5),
(13548, 19, 13546, '电白县', '电白县', 10),
(13549, 19, 13546, '高州市', '高州市', 15),
(13550, 19, 13546, '化州市', '化州市', 20),
(13551, 19, 13546, '茂港区', '茂港区', 25),
(13552, 19, 13546, '信宜市', '信宜市', 30),
(13553, 19, 13382, '肇庆市', '肇庆市', 85),
(13554, 19, 13553, '端州区', '端州区', 5),
(13555, 19, 13553, '四会市', '四会市', 10),
(13556, 19, 13553, '高要市', '高要市', 15),
(13557, 19, 13553, '广宁县', '广宁县', 20),
(13558, 19, 13553, '德庆县', '德庆县', 25),
(13559, 19, 13553, '怀集县', '怀集县', 30),
(13560, 19, 13553, '封开县', '封开县', 35),
(13561, 19, 13553, '鼎湖区', '鼎湖区', 40),
(13562, 19, 13382, '云浮市', '云浮市', 90),
(13563, 19, 13562, '云城区', '云城区', 5),
(13564, 19, 13562, '罗定市', '罗定市', 10),
(13565, 19, 13562, '云安县', '云安县', 15),
(13566, 19, 13562, '新兴县', '新兴县', 20),
(13567, 19, 13562, '郁南县', '郁南县', 25),
(13568, 19, 13382, '清远市', '清远市', 95),
(13569, 19, 13568, '连州市', '连州市', 5),
(13570, 19, 13568, '佛冈县', '佛冈县', 10),
(13571, 19, 13568, '阳山县', '阳山县', 15),
(13572, 19, 13568, '清新县', '清新县', 20),
(13573, 19, 13568, '连山县', '连山县', 25),
(13574, 19, 13568, '连南县', '连南县', 30),
(13575, 19, 13568, '清城区', '清城区', 35),
(13576, 19, 13568, '英德市', '英德市', 40),
(13577, 19, 13382, '潮州市', '潮州市', 100),
(13578, 19, 13577, '湘桥区', '湘桥区', 5),
(13579, 19, 13577, '枫溪区', '枫溪区', 10),
(13580, 19, 13577, '潮安县', '潮安县', 15),
(13581, 19, 13577, '饶平县', '饶平县', 20),
(13582, 19, 13382, '揭阳市', '揭阳市', 105),
(13583, 19, 13582, '东山区', '东山区', 5),
(13584, 19, 13582, '普宁市', '普宁市', 10),
(13585, 19, 13582, '榕城区', '榕城区', 15),
(13586, 19, 13582, '揭东县', '揭东县', 20),
(13587, 19, 13582, '揭西县', '揭西县', 25),
(13588, 19, 13582, '惠来县', '惠来县', 30),
(13589, 19, 0, '广西壮族自治区', '广西壮族自治区', 100),
(13590, 19, 13589, '南宁市', '南宁市', 5),
(13591, 19, 13590, '良庆区', '良庆区', 5),
(13592, 19, 13590, '江南区', '江南区', 10),
(13593, 19, 13590, '兴宁区', '兴宁区', 15),
(13594, 19, 13590, '青秀区', '青秀区', 20),
(13595, 19, 13590, '西乡塘区', '西乡塘区', 25),
(13596, 19, 13590, '横县', '横县', 30),
(13597, 19, 13590, '上林县', '上林县', 35),
(13598, 19, 13590, '隆安县', '隆安县', 40),
(13599, 19, 13590, '马山县', '马山县', 45),
(13600, 19, 13590, '武鸣县', '武鸣县', 50),
(13601, 19, 13590, '邕宁区', '邕宁区', 55),
(13602, 19, 13590, '宾阳县', '宾阳县', 60),
(13603, 19, 13589, '柳州市', '柳州市', 10),
(13604, 19, 13603, '融安县', '融安县', 5),
(13605, 19, 13603, '三江县', '三江县', 10),
(13606, 19, 13603, '融水县', '融水县', 15),
(13607, 19, 13603, '鱼峰区', '鱼峰区', 20),
(13608, 19, 13603, '城中区', '城中区', 25),
(13609, 19, 13603, '柳南区', '柳南区', 30),
(13610, 19, 13603, '柳北区', '柳北区', 35),
(13611, 19, 13603, '柳江县', '柳江县', 40),
(13612, 19, 13603, '柳城县', '柳城县', 45),
(13613, 19, 13603, '鹿寨县', '鹿寨县', 50),
(13614, 19, 13589, '桂林市', '桂林市', 15),
(13615, 19, 13614, '象山区', '象山区', 5),
(13616, 19, 13614, '恭城县', '恭城县', 10),
(13617, 19, 13614, '秀峰区', '秀峰区', 15),
(13618, 19, 13614, '叠彩区', '叠彩区', 20),
(13619, 19, 13614, '七星区', '七星区', 25),
(13620, 19, 13614, '雁山区', '雁山区', 30),
(13621, 19, 13614, '阳朔县', '阳朔县', 35),
(13622, 19, 13614, '临桂县', '临桂县', 40),
(13623, 19, 13614, '灵川县', '灵川县', 45),
(13624, 19, 13614, '全州县', '全州县', 50),
(13625, 19, 13614, '平乐县', '平乐县', 55),
(13626, 19, 13614, '兴安县', '兴安县', 60),
(13627, 19, 13614, '灌阳县', '灌阳县', 65),
(13628, 19, 13614, '荔浦县', '荔浦县', 70),
(13629, 19, 13614, '资源县', '资源县', 75),
(13630, 19, 13614, '永福县', '永福县', 80),
(13631, 19, 13614, '龙胜县', '龙胜县', 85),
(13632, 19, 13589, '梧州市', '梧州市', 20),
(13633, 19, 13632, '岑溪市', '岑溪市', 5),
(13634, 19, 13632, '苍梧县', '苍梧县', 10),
(13635, 19, 13632, '藤县', '藤县', 15),
(13636, 19, 13632, '蒙山县', '蒙山县', 20),
(13637, 19, 13632, '万秀区', '万秀区', 25),
(13638, 19, 13632, '蝶山区', '蝶山区', 30),
(13639, 19, 13632, '长洲区', '长洲区', 35),
(13640, 19, 13589, '北海市', '北海市', 25),
(13641, 19, 13640, '海城区', '海城区', 5),
(13642, 19, 13640, '银海区', '银海区', 10),
(13643, 19, 13640, '合浦县', '合浦县', 15),
(13644, 19, 13640, '铁山港区', '铁山港区', 20),
(13645, 19, 13589, '防城港市', '防城港市', 30),
(13646, 19, 13645, '防城区', '防城区', 5),
(13647, 19, 13645, '港口区', '港口区', 10),
(13648, 19, 13645, '东兴市', '东兴市', 15),
(13649, 19, 13645, '上思县', '上思县', 20),
(13650, 19, 13589, '钦州市', '钦州市', 35),
(13651, 19, 13650, '钦南区', '钦南区', 5),
(13652, 19, 13650, '钦北区', '钦北区', 10),
(13653, 19, 13650, '浦北县', '浦北县', 15),
(13654, 19, 13650, '灵山县', '灵山县', 20),
(13655, 19, 13589, '贵港市', '贵港市', 40),
(13656, 19, 13655, '港南区', '港南区', 5),
(13657, 19, 13655, '港北区', '港北区', 10),
(13658, 19, 13655, '桂平市', '桂平市', 15),
(13659, 19, 13655, '平南县', '平南县', 20),
(13660, 19, 13655, '覃塘区', '覃塘区', 25),
(13661, 19, 13589, '玉林市', '玉林市', 45),
(13662, 19, 13661, '玉州区', '玉州区', 5),
(13663, 19, 13661, '北流市', '北流市', 10),
(13664, 19, 13661, '容县', '容县', 15),
(13665, 19, 13661, '博白县', '博白县', 20),
(13666, 19, 13661, '陆川县', '陆川县', 25),
(13667, 19, 13661, '兴业县', '兴业县', 30),
(13668, 19, 13589, '贺州市', '贺州市', 50),
(13669, 19, 13668, '八步区', '八步区', 5),
(13670, 19, 13668, '钟山县', '钟山县', 10),
(13671, 19, 13668, '昭平县', '昭平县', 15),
(13672, 19, 13668, '富川县', '富川县', 20),
(13673, 19, 13668, '平桂管理区', '平桂管理区', 25),
(13674, 19, 13589, '百色市', '百色市', 55),
(13675, 19, 13674, '右江区', '右江区', 5),
(13676, 19, 13674, '平果县', '平果县', 10),
(13677, 19, 13674, '乐业县', '乐业县', 15),
(13678, 19, 13674, '田阳县', '田阳县', 20),
(13679, 19, 13674, '西林县', '西林县', 25),
(13680, 19, 13674, '田林县', '田林县', 30),
(13681, 19, 13674, '德保县', '德保县', 35),
(13682, 19, 13674, '靖西县', '靖西县', 40),
(13683, 19, 13674, '田东县', '田东县', 45),
(13684, 19, 13674, '那坡县', '那坡县', 50),
(13685, 19, 13674, '隆林县', '隆林县', 55),
(13686, 19, 13674, '凌云县', '凌云县', 60),
(13687, 19, 13589, '河池市', '河池市', 60),
(13688, 19, 13687, '宜州市', '宜州市', 5),
(13689, 19, 13687, '天峨县', '天峨县', 10),
(13690, 19, 13687, '凤山县', '凤山县', 15),
(13691, 19, 13687, '南丹县', '南丹县', 20),
(13692, 19, 13687, '东兰县', '东兰县', 25),
(13693, 19, 13687, '巴马县', '巴马县', 30),
(13694, 19, 13687, '环江县', '环江县', 35),
(13695, 19, 13687, '大化县', '大化县', 40),
(13696, 19, 13687, '都安县', '都安县', 45),
(13697, 19, 13687, '金城江区', '金城江区', 50),
(13698, 19, 13687, '罗城县', '罗城县', 55),
(13699, 19, 13589, '来宾市', '来宾市', 65),
(13700, 19, 13699, '兴宾区', '兴宾区', 5),
(13701, 19, 13699, '合山市', '合山市', 10),
(13702, 19, 13699, '忻城县', '忻城县', 15),
(13703, 19, 13699, '武宣县', '武宣县', 20),
(13704, 19, 13699, '象州县', '象州县', 25),
(13705, 19, 13699, '金秀县', '金秀县', 30),
(13706, 19, 13589, '崇左市', '崇左市', 70),
(13707, 19, 13706, '江州区', '江州区', 5),
(13708, 19, 13706, '凭祥市', '凭祥市', 10),
(13709, 19, 13706, '扶绥县', '扶绥县', 15),
(13710, 19, 13706, '大新县', '大新县', 20),
(13711, 19, 13706, '天等县', '天等县', 25),
(13712, 19, 13706, '宁明县', '宁明县', 30),
(13713, 19, 13706, '龙州县', '龙州县', 35),
(13714, 19, 0, '江西省', '江西省', 105),
(13715, 19, 13714, '南昌市', '南昌市', 5),
(13716, 19, 13715, '青云谱区', '青云谱区', 5),
(13717, 19, 13715, '西湖区', '西湖区', 10),
(13718, 19, 13715, '东湖区', '东湖区', 15),
(13719, 19, 13715, '昌北区', '昌北区', 20),
(13720, 19, 13715, '南昌县', '南昌县', 25),
(13721, 19, 13715, '进贤县', '进贤县', 30),
(13722, 19, 13715, '安义县', '安义县', 35),
(13723, 19, 13715, '青山湖区', '青山湖区', 40),
(13724, 19, 13715, '红谷滩新区', '红谷滩新区', 45),
(13725, 19, 13715, '新建县', '新建县', 50),
(13726, 19, 13715, '湾里区', '湾里区', 55),
(13727, 19, 13715, '高新区', '高新区', 60),
(13728, 19, 13714, '景德镇市', '景德镇市', 10),
(13729, 19, 13728, '珠山区', '珠山区', 5),
(13730, 19, 13728, '乐平市', '乐平市', 10),
(13731, 19, 13728, '浮梁县', '浮梁县', 15),
(13732, 19, 13728, '昌江区', '昌江区', 20),
(13733, 19, 13714, '萍乡市', '萍乡市', 15),
(13734, 19, 13733, '湘东区', '湘东区', 5),
(13735, 19, 13733, '莲花县', '莲花县', 10),
(13736, 19, 13733, '上栗县', '上栗县', 15),
(13737, 19, 13733, '芦溪县', '芦溪县', 20),
(13738, 19, 13733, '安源区', '安源区', 25),
(13739, 19, 13714, '新余市', '新余市', 20),
(13740, 19, 13739, '分宜县', '分宜县', 5),
(13741, 19, 13739, '渝水区', '渝水区', 10),
(13742, 19, 13714, '九江市', '九江市', 25),
(13743, 19, 13742, '九江县', '九江县', 5),
(13744, 19, 13742, '瑞昌市', '瑞昌市', 10),
(13745, 19, 13742, '星子县', '星子县', 15),
(13746, 19, 13742, '武宁县', '武宁县', 20),
(13747, 19, 13742, '彭泽县', '彭泽县', 25),
(13748, 19, 13742, '永修县', '永修县', 30),
(13749, 19, 13742, '修水县', '修水县', 35),
(13750, 19, 13742, '湖口县', '湖口县', 40),
(13751, 19, 13742, '德安县', '德安县', 45),
(13752, 19, 13742, '都昌县', '都昌县', 50),
(13753, 19, 13742, '共青城市', '共青城市', 55),
(13754, 19, 13742, '经济技术开发区', '经济技术开发区', 60),
(13755, 19, 13742, '八里湖新区', '八里湖新区', 65),
(13756, 19, 13742, '庐山风景名胜区', '庐山风景名胜区', 70),
(13757, 19, 13742, '庐山区', '庐山区', 75),
(13758, 19, 13742, '浔阳区', '浔阳区', 80),
(13759, 19, 13714, '鹰潭市', '鹰潭市', 30),
(13760, 19, 13759, '龙虎山风景旅游区', '龙虎山风景旅游区', 5),
(13761, 19, 13759, '余江县', '余江县', 10),
(13762, 19, 13759, '贵溪市', '贵溪市', 15),
(13763, 19, 13759, '月湖区', '月湖区', 20),
(13764, 19, 13714, '上饶市', '上饶市', 35),
(13765, 19, 13764, '德兴市', '德兴市', 5),
(13766, 19, 13764, '广丰县', '广丰县', 10),
(13767, 19, 13764, '鄱阳县', '鄱阳县', 15),
(13768, 19, 13764, '婺源县', '婺源县', 20),
(13769, 19, 13764, '余干县', '余干县', 25),
(13770, 19, 13764, '横峰县', '横峰县', 30),
(13771, 19, 13764, '弋阳县', '弋阳县', 35),
(13772, 19, 13764, '铅山县', '铅山县', 40),
(13773, 19, 13764, '玉山县', '玉山县', 45),
(13774, 19, 13764, '万年县', '万年县', 50),
(13775, 19, 13764, '信州区', '信州区', 55),
(13776, 19, 13764, '上饶县', '上饶县', 60),
(13777, 19, 13714, '宜春市', '宜春市', 40),
(13778, 19, 13777, '丰城市', '丰城市', 5),
(13779, 19, 13777, '樟树市', '樟树市', 10),
(13780, 19, 13777, '袁州区', '袁州区', 15),
(13781, 19, 13777, '高安市', '高安市', 20),
(13782, 19, 13777, '铜鼓县', '铜鼓县', 25),
(13783, 19, 13777, '靖安县', '靖安县', 30),
(13784, 19, 13777, '宜丰县', '宜丰县', 35),
(13785, 19, 13777, '奉新县', '奉新县', 40),
(13786, 19, 13777, '万载县', '万载县', 45),
(13787, 19, 13777, '上高县', '上高县', 50),
(13788, 19, 13714, '抚州市', '抚州市', 45),
(13789, 19, 13788, '南丰县', '南丰县', 5),
(13790, 19, 13788, '乐安县', '乐安县', 10),
(13791, 19, 13788, '金溪县', '金溪县', 15),
(13792, 19, 13788, '南城县', '南城县', 20),
(13793, 19, 13788, '东乡县', '东乡县', 25),
(13794, 19, 13788, '资溪县', '资溪县', 30),
(13795, 19, 13788, '宜黄县', '宜黄县', 35),
(13796, 19, 13788, '崇仁县', '崇仁县', 40),
(13797, 19, 13788, '黎川县', '黎川县', 45),
(13798, 19, 13788, '广昌县', '广昌县', 50),
(13799, 19, 13788, '临川区', '临川区', 55),
(13800, 19, 13714, '吉安市', '吉安市', 50),
(13801, 19, 13800, '青原区', '青原区', 5),
(13802, 19, 13800, '吉州区', '吉州区', 10),
(13803, 19, 13800, '井冈山市', '井冈山市', 15),
(13804, 19, 13800, '吉安县', '吉安县', 20),
(13805, 19, 13800, '永丰县', '永丰县', 25),
(13806, 19, 13800, '永新县', '永新县', 30),
(13807, 19, 13800, '新干县', '新干县', 35),
(13808, 19, 13800, '泰和县', '泰和县', 40),
(13809, 19, 13800, '峡江县', '峡江县', 45),
(13810, 19, 13800, '遂川县', '遂川县', 50),
(13811, 19, 13800, '安福县', '安福县', 55),
(13812, 19, 13800, '吉水县', '吉水县', 60),
(13813, 19, 13800, '万安县', '万安县', 65),
(13814, 19, 13714, '赣州市', '赣州市', 55),
(13815, 19, 13814, '章贡区', '章贡区', 5),
(13816, 19, 13814, '南康市', '南康市', 10),
(13817, 19, 13814, '瑞金市', '瑞金市', 15),
(13818, 19, 13814, '石城县', '石城县', 20),
(13819, 19, 13814, '安远县', '安远县', 25),
(13820, 19, 13814, '赣县', '赣县', 30),
(13821, 19, 13814, '宁都县', '宁都县', 35),
(13822, 19, 13814, '寻乌县', '寻乌县', 40),
(13823, 19, 13814, '兴国县', '兴国县', 45),
(13824, 19, 13814, '定南县', '定南县', 50),
(13825, 19, 13814, '上犹县', '上犹县', 55),
(13826, 19, 13814, '于都县', '于都县', 60),
(13827, 19, 13814, '龙南县', '龙南县', 65),
(13828, 19, 13814, '崇义县', '崇义县', 70),
(13829, 19, 13814, '大余县', '大余县', 75),
(13830, 19, 13814, '信丰县', '信丰县', 80),
(13831, 19, 13814, '全南县', '全南县', 85),
(13832, 19, 13814, '会昌县', '会昌县', 90),
(13833, 19, 0, '四川省', '四川省', 110),
(13834, 19, 13833, '成都市', '成都市', 5),
(13835, 19, 13834, '武侯区', '武侯区', 5),
(13836, 19, 13834, '金牛区', '金牛区', 10),
(13837, 19, 13834, '青羊区', '青羊区', 15),
(13838, 19, 13834, '成华区', '成华区', 20),
(13839, 19, 13834, '高新区', '高新区', 25),
(13840, 19, 13834, '锦江区', '锦江区', 30),
(13841, 19, 13834, '郫县', '郫县', 35),
(13842, 19, 13834, '双流县', '双流县', 40),
(13843, 19, 13834, '高新西区', '高新西区', 45),
(13844, 19, 13834, '龙泉驿区', '龙泉驿区', 50),
(13845, 19, 13834, '新都区', '新都区', 55),
(13846, 19, 13834, '温江区', '温江区', 60),
(13847, 19, 13834, '都江堰市', '都江堰市', 65),
(13848, 19, 13834, '彭州市', '彭州市', 70),
(13849, 19, 13834, '青白江区', '青白江区', 75),
(13850, 19, 13834, '崇州市', '崇州市', 80),
(13851, 19, 13834, '金堂县', '金堂县', 85),
(13852, 19, 13834, '新津县', '新津县', 90),
(13853, 19, 13834, '邛崃市', '邛崃市', 95),
(13854, 19, 13834, '大邑县', '大邑县', 100),
(13855, 19, 13834, '蒲江县', '蒲江县', 105),
(13856, 19, 13833, '自贡市', '自贡市', 10),
(13857, 19, 13856, '自流井区', '自流井区', 5),
(13858, 19, 13856, '沿滩区', '沿滩区', 10),
(13859, 19, 13856, '荣县', '荣县', 15),
(13860, 19, 13856, '富顺县', '富顺县', 20),
(13861, 19, 13856, '大安区', '大安区', 25),
(13862, 19, 13856, '贡井区', '贡井区', 30),
(13863, 19, 13833, '攀枝花市', '攀枝花市', 15),
(13864, 19, 13863, '仁和区', '仁和区', 5),
(13865, 19, 13863, '西区', '西区', 10),
(13866, 19, 13863, '东区', '东区', 15),
(13867, 19, 13863, '米易县', '米易县', 20),
(13868, 19, 13863, '盐边县', '盐边县', 25),
(13869, 19, 13833, '泸州市', '泸州市', 20),
(13870, 19, 13869, '纳溪区', '纳溪区', 5),
(13871, 19, 13869, '江阳区', '江阳区', 10),
(13872, 19, 13869, '龙马潭区', '龙马潭区', 15),
(13873, 19, 13869, '泸县', '泸县', 20),
(13874, 19, 13869, '合江县', '合江县', 25),
(13875, 19, 13869, '叙永县', '叙永县', 30),
(13876, 19, 13869, '古蔺县', '古蔺县', 35),
(13877, 19, 13833, '绵阳市', '绵阳市', 25),
(13878, 19, 13877, '江油市', '江油市', 5),
(13879, 19, 13877, '涪城区', '涪城区', 10),
(13880, 19, 13877, '游仙区', '游仙区', 15),
(13881, 19, 13877, '高新区', '高新区', 20),
(13882, 19, 13877, '经开区', '经开区', 25),
(13883, 19, 13877, '盐亭县', '盐亭县', 30),
(13884, 19, 13877, '三台县', '三台县', 35),
(13885, 19, 13877, '平武县', '平武县', 40),
(13886, 19, 13877, '北川县', '北川县', 45),
(13887, 19, 13877, '安县', '安县', 50),
(13888, 19, 13877, '梓潼县', '梓潼县', 55),
(13889, 19, 13833, '德阳市', '德阳市', 30),
(13890, 19, 13889, '广汉市', '广汉市', 5),
(13891, 19, 13889, '什邡市', '什邡市', 10),
(13892, 19, 13889, '旌阳区', '旌阳区', 15),
(13893, 19, 13889, '绵竹市', '绵竹市', 20),
(13894, 19, 13889, '罗江县', '罗江县', 25),
(13895, 19, 13889, '中江县', '中江县', 30),
(13896, 19, 13833, '广元市', '广元市', 35),
(13897, 19, 13896, '元坝区', '元坝区', 5),
(13898, 19, 13896, '朝天区', '朝天区', 10),
(13899, 19, 13896, '利州区', '利州区', 15),
(13900, 19, 13896, '青川县', '青川县', 20),
(13901, 19, 13896, '旺苍县', '旺苍县', 25),
(13902, 19, 13896, '剑阁县', '剑阁县', 30),
(13903, 19, 13896, '苍溪县', '苍溪县', 35),
(13904, 19, 13833, '遂宁市', '遂宁市', 40),
(13905, 19, 13904, '船山区', '船山区', 5),
(13906, 19, 13904, '射洪县', '射洪县', 10),
(13907, 19, 13904, '蓬溪县', '蓬溪县', 15),
(13908, 19, 13904, '大英县', '大英县', 20),
(13909, 19, 13904, '安居区', '安居区', 25),
(13910, 19, 13833, '内江市', '内江市', 45),
(13911, 19, 13910, '东兴区', '东兴区', 5),
(13912, 19, 13910, '资中县', '资中县', 10),
(13913, 19, 13910, '隆昌县', '隆昌县', 15),
(13914, 19, 13910, '威远县', '威远县', 20),
(13915, 19, 13910, '市中区', '市中区', 25),
(13916, 19, 13833, '乐山市', '乐山市', 50),
(13917, 19, 13916, '市中区', '市中区', 5),
(13918, 19, 13916, '峨眉山市', '峨眉山市', 10),
(13919, 19, 13916, '五通桥区', '五通桥区', 15),
(13920, 19, 13916, '沙湾区', '沙湾区', 20),
(13921, 19, 13916, '金口河区', '金口河区', 25),
(13922, 19, 13916, '夹江县', '夹江县', 30),
(13923, 19, 13916, '井研县', '井研县', 35),
(13924, 19, 13916, '犍为县', '犍为县', 40),
(13925, 19, 13916, '沐川县', '沐川县', 45),
(13926, 19, 13916, '峨边县', '峨边县', 50),
(13927, 19, 13916, '马边县', '马边县', 55),
(13928, 19, 13833, '宜宾市', '宜宾市', 55),
(13929, 19, 13928, '宜宾县', '宜宾县', 5),
(13930, 19, 13928, '南溪区', '南溪区', 10),
(13931, 19, 13928, '江安县', '江安县', 15),
(13932, 19, 13928, '长宁县', '长宁县', 20),
(13933, 19, 13928, '兴文县', '兴文县', 25),
(13934, 19, 13928, '珙县', '珙县', 30),
(13935, 19, 13928, '翠屏区', '翠屏区', 35),
(13936, 19, 13928, '高县', '高县', 40),
(13937, 19, 13928, '屏山县', '屏山县', 45),
(13938, 19, 13928, '筠连县', '筠连县', 50),
(13939, 19, 13833, '广安市', '广安市', 60),
(13940, 19, 13939, '前锋区', '前锋区', 5),
(13941, 19, 13939, '岳池县', '岳池县', 10),
(13942, 19, 13939, '武胜县', '武胜县', 15),
(13943, 19, 13939, '邻水县', '邻水县', 20),
(13944, 19, 13939, '广安区', '广安区', 25),
(13945, 19, 13939, '华蓥市', '华蓥市', 30),
(13946, 19, 13833, '南充市', '南充市', 65),
(13947, 19, 13946, '顺庆区', '顺庆区', 5),
(13948, 19, 13946, '高坪区', '高坪区', 10),
(13949, 19, 13946, '嘉陵区', '嘉陵区', 15),
(13950, 19, 13946, '西充县', '西充县', 20),
(13951, 19, 13946, '阆中市', '阆中市', 25),
(13952, 19, 13946, '南部县', '南部县', 30),
(13953, 19, 13946, '仪陇县', '仪陇县', 35),
(13954, 19, 13946, '蓬安县', '蓬安县', 40),
(13955, 19, 13946, '营山县', '营山县', 45),
(13956, 19, 13833, '达州市', '达州市', 70),
(13957, 19, 13956, '通川区', '通川区', 5),
(13958, 19, 13956, '达县', '达县', 10),
(13959, 19, 13956, '大竹县', '大竹县', 15),
(13960, 19, 13956, '渠县', '渠县', 20),
(13961, 19, 13956, '万源市', '万源市', 25),
(13962, 19, 13956, '宣汉县', '宣汉县', 30),
(13963, 19, 13956, '开江县', '开江县', 35),
(13964, 19, 13833, '巴中市', '巴中市', 75),
(13965, 19, 13964, '巴州区', '巴州区', 5),
(13966, 19, 13964, '恩阳区', '恩阳区', 10),
(13967, 19, 13964, '南江县', '南江县', 15),
(13968, 19, 13964, '平昌县', '平昌县', 20),
(13969, 19, 13964, '通江县', '通江县', 25),
(13970, 19, 13833, '雅安市', '雅安市', 80),
(13971, 19, 13970, '芦山县', '芦山县', 5),
(13972, 19, 13970, '石棉县', '石棉县', 10),
(13973, 19, 13970, '名山区', '名山区', 15),
(13974, 19, 13970, '天全县', '天全县', 20),
(13975, 19, 13970, '荥经县', '荥经县', 25),
(13976, 19, 13970, '汉源县', '汉源县', 30),
(13977, 19, 13970, '宝兴县', '宝兴县', 35),
(13978, 19, 13970, '雨城区', '雨城区', 40),
(13979, 19, 13833, '眉山市', '眉山市', 85),
(13980, 19, 13979, '仁寿县', '仁寿县', 5),
(13981, 19, 13979, '彭山县', '彭山县', 10),
(13982, 19, 13979, '洪雅县', '洪雅县', 15),
(13983, 19, 13979, '丹棱县', '丹棱县', 20),
(13984, 19, 13979, '青神县', '青神县', 25),
(13985, 19, 13979, '东坡区', '东坡区', 30),
(13986, 19, 13833, '资阳市', '资阳市', 90),
(13987, 19, 13986, '雁江区', '雁江区', 5),
(13988, 19, 13986, '安岳县', '安岳县', 10),
(13989, 19, 13986, '乐至县', '乐至县', 15),
(13990, 19, 13986, '简阳市', '简阳市', 20),
(13991, 19, 13833, '阿坝州', '阿坝州', 95),
(13992, 19, 13991, '马尔康县', '马尔康县', 5),
(13993, 19, 13991, '九寨沟县', '九寨沟县', 10),
(13994, 19, 13991, '红原县', '红原县', 15),
(13995, 19, 13991, '阿坝县', '阿坝县', 20),
(13996, 19, 13991, '理县', '理县', 25),
(13997, 19, 13991, '若尔盖县', '若尔盖县', 30),
(13998, 19, 13991, '金川县', '金川县', 35),
(13999, 19, 13991, '小金县', '小金县', 40),
(14000, 19, 13991, '黑水县', '黑水县', 45),
(14001, 19, 13991, '松潘县', '松潘县', 50),
(14002, 19, 13991, '壤塘县', '壤塘县', 55),
(14003, 19, 13991, '茂县', '茂县', 60),
(14004, 19, 13991, '汶川县', '汶川县', 65),
(14005, 19, 13833, '甘孜州', '甘孜州', 100),
(14006, 19, 14005, '康定县', '康定县', 5),
(14007, 19, 14005, '泸定县', '泸定县', 10),
(14008, 19, 14005, '九龙县', '九龙县', 15),
(14009, 19, 14005, '丹巴县', '丹巴县', 20),
(14010, 19, 14005, '道孚县', '道孚县', 25),
(14011, 19, 14005, '炉霍县', '炉霍县', 30),
(14012, 19, 14005, '色达县', '色达县', 35),
(14013, 19, 14005, '甘孜县', '甘孜县', 40),
(14014, 19, 14005, '新龙县', '新龙县', 45),
(14015, 19, 14005, '白玉县', '白玉县', 50),
(14016, 19, 14005, '德格县', '德格县', 55),
(14017, 19, 14005, '石渠县', '石渠县', 60),
(14018, 19, 14005, '雅江县', '雅江县', 65),
(14019, 19, 14005, '理塘县', '理塘县', 70),
(14020, 19, 14005, '巴塘县', '巴塘县', 75),
(14021, 19, 14005, '稻城县', '稻城县', 80),
(14022, 19, 14005, '乡城县', '乡城县', 85),
(14023, 19, 14005, '得荣县', '得荣县', 90),
(14024, 19, 13833, '凉山州', '凉山州', 105),
(14025, 19, 14024, '美姑县', '美姑县', 5),
(14026, 19, 14024, '昭觉县', '昭觉县', 10),
(14027, 19, 14024, '会理县', '会理县', 15),
(14028, 19, 14024, '会东县', '会东县', 20),
(14029, 19, 14024, '普格县', '普格县', 25),
(14030, 19, 14024, '宁南县', '宁南县', 30),
(14031, 19, 14024, '德昌县', '德昌县', 35),
(14032, 19, 14024, '冕宁县', '冕宁县', 40),
(14033, 19, 14024, '盐源县', '盐源县', 45),
(14034, 19, 14024, '金阳县', '金阳县', 50),
(14035, 19, 14024, '布拖县', '布拖县', 55),
(14036, 19, 14024, '雷波县', '雷波县', 60),
(14037, 19, 14024, '越西县', '越西县', 65),
(14038, 19, 14024, '喜德县', '喜德县', 70),
(14039, 19, 14024, '甘洛县', '甘洛县', 75),
(14040, 19, 14024, '木里县', '木里县', 80),
(14041, 19, 14024, '西昌市', '西昌市', 85),
(14042, 19, 0, '海南省', '海南省', 115),
(14043, 19, 14042, '海口市', '海口市', 5),
(14044, 19, 14043, '秀英区', '秀英区', 5),
(14045, 19, 14043, '龙华区', '龙华区', 10),
(14046, 19, 14043, '琼山区', '琼山区', 15),
(14047, 19, 14043, '美兰区', '美兰区', 20),
(14048, 19, 14042, '儋州市', '儋州市', 10),
(14049, 19, 14048, '热作学院', '热作学院', 5),
(14050, 19, 14048, '那大镇', '那大镇', 10),
(14051, 19, 14048, '富克镇', '富克镇', 15),
(14052, 19, 14048, '和庆镇', '和庆镇', 20),
(14053, 19, 14048, '南丰镇', '南丰镇', 25),
(14054, 19, 14048, '大成镇', '大成镇', 30),
(14055, 19, 14048, '雅星镇', '雅星镇', 35),
(14056, 19, 14048, '兰洋镇', '兰洋镇', 40),
(14057, 19, 14048, '光村镇', '光村镇', 45),
(14058, 19, 14048, '木棠镇', '木棠镇', 50),
(14059, 19, 14048, '海头镇', '海头镇', 55),
(14060, 19, 14048, '峨蔓镇', '峨蔓镇', 60),
(14061, 19, 14048, '三都镇', '三都镇', 65),
(14062, 19, 14048, '王五镇', '王五镇', 70),
(14063, 19, 14048, '白马井镇', '白马井镇', 75),
(14064, 19, 14048, '中和镇', '中和镇', 80),
(14065, 19, 14048, '排浦镇', '排浦镇', 85),
(14066, 19, 14048, '东成镇', '东成镇', 90),
(14067, 19, 14048, '新州镇', '新州镇', 95),
(14068, 19, 14048, '洋浦经济开发区', '洋浦经济开发区', 100),
(14069, 19, 14048, '西培农场', '西培农场', 105),
(14070, 19, 14048, '西联农场', '西联农场', 110),
(14071, 19, 14048, '蓝洋农场', '蓝洋农场', 115),
(14072, 19, 14048, '八一农场', '八一农场', 120),
(14073, 19, 14048, '西华农场', '西华农场', 125),
(14074, 19, 14048, '西庆农场', '西庆农场', 130),
(14075, 19, 14048, '西流农场', '西流农场', 135),
(14076, 19, 14048, '新盈农场', '新盈农场', 140),
(14077, 19, 14048, '龙山农场', '龙山农场', 145),
(14078, 19, 14048, '红岭农场', '红岭农场', 150),
(14079, 19, 14042, '琼海市', '琼海市', 15),
(14080, 19, 14079, '嘉积镇', '嘉积镇', 5),
(14081, 19, 14079, '万泉镇', '万泉镇', 10),
(14082, 19, 14079, '石壁镇', '石壁镇', 15),
(14083, 19, 14079, '中原镇', '中原镇', 20),
(14084, 19, 14079, '博鳌镇', '博鳌镇', 25),
(14085, 19, 14079, '阳江镇', '阳江镇', 30),
(14086, 19, 14079, '龙江镇', '龙江镇', 35),
(14087, 19, 14079, '潭门镇', '潭门镇', 40),
(14088, 19, 14079, '塔洋镇', '塔洋镇', 45),
(14089, 19, 14079, '长坡镇', '长坡镇', 50),
(14090, 19, 14079, '大路镇', '大路镇', 55),
(14091, 19, 14079, '会山镇', '会山镇', 60),
(14092, 19, 14079, '彬村山华侨农场', '彬村山华侨农场', 65),
(14093, 19, 14079, '东太农场', '东太农场', 70),
(14094, 19, 14079, '东红农场', '东红农场', 75),
(14095, 19, 14079, '东升农场', '东升农场', 80),
(14096, 19, 14079, '南俸农场', '南俸农场', 85),
(14097, 19, 14042, '万宁市', '万宁市', 20),
(14098, 19, 14097, '万城镇', '万城镇', 5),
(14099, 19, 14097, '龙滚镇', '龙滚镇', 10),
(14100, 19, 14097, '和乐镇', '和乐镇', 15),
(14101, 19, 14097, '后安镇', '后安镇', 20),
(14102, 19, 14097, '大茂镇', '大茂镇', 25),
(14103, 19, 14097, '东澳镇', '东澳镇', 30),
(14104, 19, 14097, '礼纪镇', '礼纪镇', 35),
(14105, 19, 14097, '长丰镇', '长丰镇', 40),
(14106, 19, 14097, '山根镇', '山根镇', 45),
(14107, 19, 14097, '北大镇', '北大镇', 50),
(14108, 19, 14097, '南桥镇', '南桥镇', 55),
(14109, 19, 14097, '三更罗镇', '三更罗镇', 60),
(14110, 19, 14097, '六连林场', '六连林场', 65),
(14111, 19, 14097, '东兴农场', '东兴农场', 70),
(14112, 19, 14097, '东和农场', '东和农场', 75),
(14113, 19, 14097, '新中农场', '新中农场', 80),
(14114, 19, 14097, '兴隆华侨农场', '兴隆华侨农场', 85),
(14115, 19, 14042, '东方市', '东方市', 25),
(14116, 19, 14115, '八所镇', '八所镇', 5),
(14117, 19, 14115, '东河镇', '东河镇', 10),
(14118, 19, 14115, '大田镇', '大田镇', 15),
(14119, 19, 14115, '感城镇', '感城镇', 20),
(14120, 19, 14115, '板桥镇', '板桥镇', 25),
(14121, 19, 14115, '三家镇', '三家镇', 30),
(14122, 19, 14115, '四更镇', '四更镇', 35),
(14123, 19, 14115, '新龙镇', '新龙镇', 40),
(14124, 19, 14115, '天安乡', '天安乡', 45),
(14125, 19, 14115, '江边乡', '江边乡', 50),
(14126, 19, 14115, '广坝农场', '广坝农场', 55),
(14127, 19, 14115, '东方华侨农场', '东方华侨农场', 60),
(14128, 19, 14042, '三亚市', '三亚市', 30),
(14129, 19, 14128, '崖城镇', '崖城镇', 5),
(14130, 19, 14128, '海棠湾镇', '海棠湾镇', 10),
(14131, 19, 14128, '吉阳镇', '吉阳镇', 15),
(14132, 19, 14128, '凤凰镇', '凤凰镇', 20),
(14133, 19, 14128, '天涯镇', '天涯镇', 25),
(14134, 19, 14128, '育才镇', '育才镇', 30),
(14135, 19, 14128, '河西区', '河西区', 35),
(14136, 19, 14128, '河东区', '河东区', 40),
(14137, 19, 14128, '南田农场', '南田农场', 45),
(14138, 19, 14128, '南新农场', '南新农场', 50),
(14139, 19, 14128, '南岛农场', '南岛农场', 55),
(14140, 19, 14128, '立才农场', '立才农场', 60),
(14141, 19, 14128, '南滨农场', '南滨农场', 65),
(14142, 19, 14042, '文昌市', '文昌市', 35),
(14143, 19, 14142, '文城镇', '文城镇', 5),
(14144, 19, 14142, '重兴镇', '重兴镇', 10),
(14145, 19, 14142, '蓬莱镇', '蓬莱镇', 15),
(14146, 19, 14142, '会文镇', '会文镇', 20),
(14147, 19, 14142, '东路镇', '东路镇', 25),
(14148, 19, 14142, '潭牛镇', '潭牛镇', 30),
(14149, 19, 14142, '东阁镇', '东阁镇', 35),
(14150, 19, 14142, '文教镇', '文教镇', 40),
(14151, 19, 14142, '东郊镇', '东郊镇', 45),
(14152, 19, 14142, '龙楼镇', '龙楼镇', 50),
(14153, 19, 14142, '昌洒镇', '昌洒镇', 55),
(14154, 19, 14142, '翁田镇', '翁田镇', 60),
(14155, 19, 14142, '抱罗镇', '抱罗镇', 65),
(14156, 19, 14142, '冯坡镇', '冯坡镇', 70),
(14157, 19, 14142, '锦山镇', '锦山镇', 75),
(14158, 19, 14142, '铺前镇', '铺前镇', 80),
(14159, 19, 14142, '公坡镇', '公坡镇', 85),
(14160, 19, 14142, '迈号镇', '迈号镇', 90),
(14161, 19, 14142, '清谰镇', '清谰镇', 95),
(14162, 19, 14142, '南阳镇', '南阳镇', 100),
(14163, 19, 14142, '新桥镇', '新桥镇', 105),
(14164, 19, 14142, '头苑镇', '头苑镇', 110),
(14165, 19, 14142, '宝芳乡', '宝芳乡', 115),
(14166, 19, 14142, '龙马乡', '龙马乡', 120),
(14167, 19, 14142, '湖山乡', '湖山乡', 125),
(14168, 19, 14142, '东路农场', '东路农场', 130),
(14169, 19, 14142, '南阳农场', '南阳农场', 135),
(14170, 19, 14142, '罗豆农场', '罗豆农场', 140),
(14171, 19, 14142, '橡胶研究所', '橡胶研究所', 145),
(14172, 19, 14042, '五指山市', '五指山市', 40),
(14173, 19, 14172, '通什镇', '通什镇', 5),
(14174, 19, 14172, '南圣镇', '南圣镇', 10),
(14175, 19, 14172, '毛阳镇', '毛阳镇', 15),
(14176, 19, 14172, '番阳镇', '番阳镇', 20),
(14177, 19, 14172, '畅好乡', '畅好乡', 25),
(14178, 19, 14172, '毛道乡', '毛道乡', 30),
(14179, 19, 14172, '水满乡', '水满乡', 35),
(14180, 19, 14172, '畅好农场', '畅好农场', 40),
(14181, 19, 14042, '临高县', '临高县', 45),
(14182, 19, 14181, '城区', '城区', 5),
(14183, 19, 14181, '临城镇', '临城镇', 10),
(14184, 19, 14181, '波莲镇', '波莲镇', 15),
(14185, 19, 14181, '东英镇', '东英镇', 20),
(14186, 19, 14181, '博厚镇', '博厚镇', 25),
(14187, 19, 14181, '皇桐镇', '皇桐镇', 30),
(14188, 19, 14181, '多文镇', '多文镇', 35),
(14189, 19, 14181, '和舍镇', '和舍镇', 40),
(14190, 19, 14181, '南宝镇', '南宝镇', 45),
(14191, 19, 14181, '新盈镇', '新盈镇', 50),
(14192, 19, 14181, '调楼镇', '调楼镇', 55),
(14193, 19, 14181, '加来镇', '加来镇', 60),
(14194, 19, 14181, '红华农场', '红华农场', 65),
(14195, 19, 14181, '加来农场', '加来农场', 70),
(14196, 19, 14042, '澄迈县', '澄迈县', 50),
(14197, 19, 14196, '城区', '城区', 5),
(14198, 19, 14196, '金江镇', '金江镇', 10),
(14199, 19, 14196, '老城镇', '老城镇', 15),
(14200, 19, 14196, '瑞溪镇', '瑞溪镇', 20),
(14201, 19, 14196, '永发镇', '永发镇', 25),
(14202, 19, 14196, '加乐镇', '加乐镇', 30),
(14203, 19, 14196, '文儒镇', '文儒镇', 35),
(14204, 19, 14196, '中兴镇', '中兴镇', 40),
(14205, 19, 14196, '仁兴镇', '仁兴镇', 45),
(14206, 19, 14196, '福山镇', '福山镇', 50),
(14207, 19, 14196, '桥头镇', '桥头镇', 55),
(14208, 19, 14196, '大丰镇', '大丰镇', 60),
(14209, 19, 14196, '红光农场', '红光农场', 65),
(14210, 19, 14196, '西达农场', '西达农场', 70),
(14211, 19, 14196, '金安农场', '金安农场', 75),
(14212, 19, 14042, '定安县', '定安县', 55),
(14213, 19, 14212, '黄竹镇', '黄竹镇', 5),
(14214, 19, 14212, '城区', '城区', 10),
(14215, 19, 14212, '定城镇', '定城镇', 15),
(14216, 19, 14212, '新竹镇', '新竹镇', 20),
(14217, 19, 14212, '龙湖镇', '龙湖镇', 25),
(14218, 19, 14212, '雷鸣镇', '雷鸣镇', 30),
(14219, 19, 14212, '龙门镇', '龙门镇', 35),
(14220, 19, 14212, '龙河镇', '龙河镇', 40),
(14221, 19, 14212, '岭口镇', '岭口镇', 45),
(14222, 19, 14212, '翰林镇', '翰林镇', 50),
(14223, 19, 14212, '富文镇', '富文镇', 55),
(14224, 19, 14212, '金鸡岭农场', '金鸡岭农场', 60),
(14225, 19, 14212, '中瑞农场', '中瑞农场', 65),
(14226, 19, 14212, '南海农场', '南海农场', 70),
(14227, 19, 14042, '屯昌县', '屯昌县', 60),
(14228, 19, 14227, '县城内', '县城内', 5),
(14229, 19, 14227, '屯城镇', '屯城镇', 10),
(14230, 19, 14227, '新兴镇', '新兴镇', 15),
(14231, 19, 14227, '枫木镇', '枫木镇', 20),
(14232, 19, 14227, '乌坡镇', '乌坡镇', 25),
(14233, 19, 14227, '南吕镇', '南吕镇', 30),
(14234, 19, 14227, '南坤镇', '南坤镇', 35),
(14235, 19, 14227, '中建农场', '中建农场', 40),
(14236, 19, 14227, '坡心镇', '坡心镇', 45),
(14237, 19, 14227, '中坤农场', '中坤农场', 50),
(14238, 19, 14227, '西昌镇', '西昌镇', 55),
(14239, 19, 14042, '昌江县', '昌江县', 65),
(14240, 19, 14239, '红林农场', '红林农场', 5),
(14241, 19, 14239, '城区', '城区', 10),
(14242, 19, 14239, '石碌镇', '石碌镇', 15),
(14243, 19, 14239, '叉河镇', '叉河镇', 20),
(14244, 19, 14239, '十月田镇', '十月田镇', 25),
(14245, 19, 14239, '乌烈镇', '乌烈镇', 30),
(14246, 19, 14239, '昌化镇', '昌化镇', 35),
(14247, 19, 14239, '海尾镇', '海尾镇', 40),
(14248, 19, 14239, '七叉镇', '七叉镇', 45),
(14249, 19, 14239, '王下乡', '王下乡', 50),
(14250, 19, 14239, '海南矿业公司', '海南矿业公司', 55),
(14251, 19, 14239, '霸王岭林场', '霸王岭林场', 60),
(14252, 19, 14042, '白沙县', '白沙县', 70);
INSERT INTO `qinggan_opt` (`id`, `group_id`, `parent_id`, `title`, `val`, `taxis`) VALUES
(14253, 19, 14252, '南开乡', '南开乡', 5),
(14254, 19, 14252, '阜龙乡', '阜龙乡', 10),
(14255, 19, 14252, '青松乡', '青松乡', 15),
(14256, 19, 14252, '金波乡', '金波乡', 20),
(14257, 19, 14252, '荣邦乡', '荣邦乡', 25),
(14258, 19, 14252, '城区', '城区', 30),
(14259, 19, 14252, '白沙农场', '白沙农场', 35),
(14260, 19, 14252, '牙叉镇', '牙叉镇', 40),
(14261, 19, 14252, '龙江农场', '龙江农场', 45),
(14262, 19, 14252, '七坊镇', '七坊镇', 50),
(14263, 19, 14252, '邦溪农场', '邦溪农场', 55),
(14264, 19, 14252, '邦溪镇', '邦溪镇', 60),
(14265, 19, 14252, '打安镇', '打安镇', 65),
(14266, 19, 14252, '细水乡', '细水乡', 70),
(14267, 19, 14252, '元门乡', '元门乡', 75),
(14268, 19, 14042, '琼中县', '琼中县', 75),
(14269, 19, 14268, '吊罗山乡', '吊罗山乡', 5),
(14270, 19, 14268, '黎母山林业公司', '黎母山林业公司', 10),
(14271, 19, 14268, '阳江农场', '阳江农场', 15),
(14272, 19, 14268, '乌石农场', '乌石农场', 20),
(14273, 19, 14268, '加钗农场', '加钗农场', 25),
(14274, 19, 14268, '长征农场', '长征农场', 30),
(14275, 19, 14268, '营根镇', '营根镇', 35),
(14276, 19, 14268, '湾岭镇', '湾岭镇', 40),
(14277, 19, 14268, '黎母山镇', '黎母山镇', 45),
(14278, 19, 14268, '和平镇', '和平镇', 50),
(14279, 19, 14268, '长征镇', '长征镇', 55),
(14280, 19, 14268, '红毛镇', '红毛镇', 60),
(14281, 19, 14268, '中平镇', '中平镇', 65),
(14282, 19, 14268, '上安乡', '上安乡', 70),
(14283, 19, 14268, '什运乡', '什运乡', 75),
(14284, 19, 14268, '城区', '城区', 80),
(14285, 19, 14042, '陵水县', '陵水县', 80),
(14286, 19, 14285, '吊罗山林业公司', '吊罗山林业公司', 5),
(14287, 19, 14285, '岭门农场', '岭门农场', 10),
(14288, 19, 14285, '南平农场', '南平农场', 15),
(14289, 19, 14285, '椰林镇', '椰林镇', 20),
(14290, 19, 14285, '光坡镇', '光坡镇', 25),
(14291, 19, 14285, '三才镇', '三才镇', 30),
(14292, 19, 14285, '英州镇', '英州镇', 35),
(14293, 19, 14285, '隆广镇', '隆广镇', 40),
(14294, 19, 14285, '文罗镇', '文罗镇', 45),
(14295, 19, 14285, '本号镇', '本号镇', 50),
(14296, 19, 14285, '新村镇', '新村镇', 55),
(14297, 19, 14285, '黎安镇', '黎安镇', 60),
(14298, 19, 14285, '提蒙乡', '提蒙乡', 65),
(14299, 19, 14285, '群英乡', '群英乡', 70),
(14300, 19, 14285, '城区', '城区', 75),
(14301, 19, 14042, '保亭县', '保亭县', 85),
(14302, 19, 14301, '保亭研究所', '保亭研究所', 5),
(14303, 19, 14301, '新星农场', '新星农场', 10),
(14304, 19, 14301, '金江农场', '金江农场', 15),
(14305, 19, 14301, '三道农场', '三道农场', 20),
(14306, 19, 14301, '保城镇', '保城镇', 25),
(14307, 19, 14301, '什玲镇', '什玲镇', 30),
(14308, 19, 14301, '加茂镇', '加茂镇', 35),
(14309, 19, 14301, '响水镇', '响水镇', 40),
(14310, 19, 14301, '新政镇', '新政镇', 45),
(14311, 19, 14301, '三道镇', '三道镇', 50),
(14312, 19, 14301, '六弓乡', '六弓乡', 55),
(14313, 19, 14301, '南林乡', '南林乡', 60),
(14314, 19, 14301, '毛感乡', '毛感乡', 65),
(14315, 19, 14042, '乐东县', '乐东县', 90),
(14316, 19, 14315, '尖峰岭林业公司', '尖峰岭林业公司', 5),
(14317, 19, 14315, '莺歌海盐场', '莺歌海盐场', 10),
(14318, 19, 14315, '山荣农场', '山荣农场', 15),
(14319, 19, 14315, '乐光农场', '乐光农场', 20),
(14320, 19, 14315, '抱由镇', '抱由镇', 25),
(14321, 19, 14315, '保国农场', '保国农场', 30),
(14322, 19, 14315, '万冲镇', '万冲镇', 35),
(14323, 19, 14315, '大安镇', '大安镇', 40),
(14324, 19, 14315, '志仲镇', '志仲镇', 45),
(14325, 19, 14315, '千家镇', '千家镇', 50),
(14326, 19, 14315, '九所镇', '九所镇', 55),
(14327, 19, 14315, '利国镇', '利国镇', 60),
(14328, 19, 14315, '黄流镇', '黄流镇', 65),
(14329, 19, 14315, '佛罗镇', '佛罗镇', 70),
(14330, 19, 14315, '尖峰镇', '尖峰镇', 75),
(14331, 19, 14315, '莺歌海镇', '莺歌海镇', 80),
(14332, 19, 14315, '城区', '城区', 85),
(14333, 19, 14042, '三沙市', '三沙市', 95),
(14334, 19, 14333, '中沙群岛', '中沙群岛', 5),
(14335, 19, 14333, '西沙群岛', '西沙群岛', 10),
(14336, 19, 14333, '南沙群岛', '南沙群岛', 15),
(14337, 19, 0, '贵州省', '贵州省', 120),
(14338, 19, 14337, '贵阳市', '贵阳市', 5),
(14339, 19, 14338, '南明区', '南明区', 5),
(14340, 19, 14338, '云岩区', '云岩区', 10),
(14341, 19, 14338, '花溪区', '花溪区', 15),
(14342, 19, 14338, '小河区', '小河区', 20),
(14343, 19, 14338, '白云区', '白云区', 25),
(14344, 19, 14338, '清镇市', '清镇市', 30),
(14345, 19, 14338, '开阳县', '开阳县', 35),
(14346, 19, 14338, '修文县', '修文县', 40),
(14347, 19, 14338, '息烽县', '息烽县', 45),
(14348, 19, 14338, '乌当区', '乌当区', 50),
(14349, 19, 14338, '观山湖区', '观山湖区', 55),
(14350, 19, 14337, '六盘水市', '六盘水市', 10),
(14351, 19, 14350, '盘县', '盘县', 5),
(14352, 19, 14350, '六枝特区', '六枝特区', 10),
(14353, 19, 14350, '水城县', '水城县', 15),
(14354, 19, 14350, '钟山区', '钟山区', 20),
(14355, 19, 14337, '遵义市', '遵义市', 15),
(14356, 19, 14355, '红花岗区', '红花岗区', 5),
(14357, 19, 14355, '汇川区', '汇川区', 10),
(14358, 19, 14355, '赤水市', '赤水市', 15),
(14359, 19, 14355, '仁怀市', '仁怀市', 20),
(14360, 19, 14355, '遵义县', '遵义县', 25),
(14361, 19, 14355, '桐梓县', '桐梓县', 30),
(14362, 19, 14355, '绥阳县', '绥阳县', 35),
(14363, 19, 14355, '习水县', '习水县', 40),
(14364, 19, 14355, '凤冈县', '凤冈县', 45),
(14365, 19, 14355, '正安县', '正安县', 50),
(14366, 19, 14355, '湄潭县', '湄潭县', 55),
(14367, 19, 14355, '余庆县', '余庆县', 60),
(14368, 19, 14355, '道真县', '道真县', 65),
(14369, 19, 14355, '务川县', '务川县', 70),
(14370, 19, 14337, '铜仁市', '铜仁市', 20),
(14371, 19, 14370, '碧江区', '碧江区', 5),
(14372, 19, 14370, '德江县', '德江县', 10),
(14373, 19, 14370, '江口县', '江口县', 15),
(14374, 19, 14370, '思南县', '思南县', 20),
(14375, 19, 14370, '万山区', '万山区', 25),
(14376, 19, 14370, '石阡县', '石阡县', 30),
(14377, 19, 14370, '玉屏侗族自治县', '玉屏侗族自治县', 35),
(14378, 19, 14370, '松桃苗族自治县', '松桃苗族自治县', 40),
(14379, 19, 14370, '印江土家族苗族自治县', '印江土家族苗族自治县', 45),
(14380, 19, 14370, '沿河土家族自治县', '沿河土家族自治县', 50),
(14381, 19, 14337, '毕节市', '毕节市', 25),
(14382, 19, 14381, '七星关区', '七星关区', 5),
(14383, 19, 14381, '黔西县', '黔西县', 10),
(14384, 19, 14381, '大方县', '大方县', 15),
(14385, 19, 14381, '织金县', '织金县', 20),
(14386, 19, 14381, '金沙县', '金沙县', 25),
(14387, 19, 14381, '赫章县', '赫章县', 30),
(14388, 19, 14381, '纳雍县', '纳雍县', 35),
(14389, 19, 14381, '威宁彝族回族苗族自治县', '威宁彝族回族苗族自治县', 40),
(14390, 19, 14337, '安顺市', '安顺市', 30),
(14391, 19, 14390, '西秀区', '西秀区', 5),
(14392, 19, 14390, '普定县', '普定县', 10),
(14393, 19, 14390, '平坝县', '平坝县', 15),
(14394, 19, 14390, '镇宁布依族苗族自治县', '镇宁布依族苗族自治县', 20),
(14395, 19, 14390, '关岭布依族苗族自治县', '关岭布依族苗族自治县', 25),
(14396, 19, 14390, '紫云苗族布依族自治县', '紫云苗族布依族自治县', 30),
(14397, 19, 14337, '黔西南州', '黔西南州', 35),
(14398, 19, 14397, '兴义市', '兴义市', 5),
(14399, 19, 14397, '望谟县', '望谟县', 10),
(14400, 19, 14397, '兴仁县', '兴仁县', 15),
(14401, 19, 14397, '普安县', '普安县', 20),
(14402, 19, 14397, '册亨县', '册亨县', 25),
(14403, 19, 14397, '晴隆县', '晴隆县', 30),
(14404, 19, 14397, '贞丰县', '贞丰县', 35),
(14405, 19, 14397, '安龙县', '安龙县', 40),
(14406, 19, 14337, '黔东南州', '黔东南州', 40),
(14407, 19, 14406, '凯里市', '凯里市', 5),
(14408, 19, 14406, '施秉市', '施秉市', 10),
(14409, 19, 14406, '从江县', '从江县', 15),
(14410, 19, 14406, '锦屏县', '锦屏县', 20),
(14411, 19, 14406, '镇远县', '镇远县', 25),
(14412, 19, 14406, '麻江县', '麻江县', 30),
(14413, 19, 14406, '台江县', '台江县', 35),
(14414, 19, 14406, '天柱县', '天柱县', 40),
(14415, 19, 14406, '黄平县', '黄平县', 45),
(14416, 19, 14406, '榕江县', '榕江县', 50),
(14417, 19, 14406, '剑河县', '剑河县', 55),
(14418, 19, 14406, '三穗县', '三穗县', 60),
(14419, 19, 14406, '雷山县', '雷山县', 65),
(14420, 19, 14406, '黎平县', '黎平县', 70),
(14421, 19, 14406, '岑巩县', '岑巩县', 75),
(14422, 19, 14406, '丹寨县', '丹寨县', 80),
(14423, 19, 14337, '黔南州', '黔南州', 45),
(14424, 19, 14423, '都匀市', '都匀市', 5),
(14425, 19, 14423, '福泉市', '福泉市', 10),
(14426, 19, 14423, '贵定县', '贵定县', 15),
(14427, 19, 14423, '惠水县', '惠水县', 20),
(14428, 19, 14423, '罗甸县', '罗甸县', 25),
(14429, 19, 14423, '瓮安县', '瓮安县', 30),
(14430, 19, 14423, '荔波县', '荔波县', 35),
(14431, 19, 14423, '龙里县', '龙里县', 40),
(14432, 19, 14423, '平塘县', '平塘县', 45),
(14433, 19, 14423, '长顺县', '长顺县', 50),
(14434, 19, 14423, '独山县', '独山县', 55),
(14435, 19, 14423, '三都县', '三都县', 60),
(14436, 19, 0, '云南省', '云南省', 125),
(14437, 19, 14436, '昆明市', '昆明市', 5),
(14438, 19, 14437, '盘龙区', '盘龙区', 5),
(14439, 19, 14437, '五华区', '五华区', 10),
(14440, 19, 14437, '西山区', '西山区', 15),
(14441, 19, 14437, '官渡区', '官渡区', 20),
(14442, 19, 14437, '呈贡区', '呈贡区', 25),
(14443, 19, 14437, '东川区', '东川区', 30),
(14444, 19, 14437, '安宁市', '安宁市', 35),
(14445, 19, 14437, '富民县', '富民县', 40),
(14446, 19, 14437, '嵩明县', '嵩明县', 45),
(14447, 19, 14437, '晋宁县', '晋宁县', 50),
(14448, 19, 14437, '宜良县', '宜良县', 55),
(14449, 19, 14437, '禄劝县', '禄劝县', 60),
(14450, 19, 14437, '石林县', '石林县', 65),
(14451, 19, 14437, '寻甸县', '寻甸县', 70),
(14452, 19, 14436, '曲靖市', '曲靖市', 10),
(14453, 19, 14452, '麒麟区', '麒麟区', 5),
(14454, 19, 14452, '马龙县', '马龙县', 10),
(14455, 19, 14452, '宣威市', '宣威市', 15),
(14456, 19, 14452, '富源县', '富源县', 20),
(14457, 19, 14452, '会泽县', '会泽县', 25),
(14458, 19, 14452, '陆良县', '陆良县', 30),
(14459, 19, 14452, '师宗县', '师宗县', 35),
(14460, 19, 14452, '罗平县', '罗平县', 40),
(14461, 19, 14452, '沾益县', '沾益县', 45),
(14462, 19, 14436, '玉溪市', '玉溪市', 15),
(14463, 19, 14462, '红塔区', '红塔区', 5),
(14464, 19, 14462, '华宁县', '华宁县', 10),
(14465, 19, 14462, '澄江县', '澄江县', 15),
(14466, 19, 14462, '易门县', '易门县', 20),
(14467, 19, 14462, '通海县', '通海县', 25),
(14468, 19, 14462, '江川县', '江川县', 30),
(14469, 19, 14462, '元江县', '元江县', 35),
(14470, 19, 14462, '新平县', '新平县', 40),
(14471, 19, 14462, '峨山县', '峨山县', 45),
(14472, 19, 14436, '昭通市', '昭通市', 20),
(14473, 19, 14472, '鲁甸县', '鲁甸县', 5),
(14474, 19, 14472, '绥江县', '绥江县', 10),
(14475, 19, 14472, '昭阳区', '昭阳区', 15),
(14476, 19, 14472, '镇雄县', '镇雄县', 20),
(14477, 19, 14472, '永善县', '永善县', 25),
(14478, 19, 14472, '大关县', '大关县', 30),
(14479, 19, 14472, '盐津县', '盐津县', 35),
(14480, 19, 14472, '彝良县', '彝良县', 40),
(14481, 19, 14472, '水富县', '水富县', 45),
(14482, 19, 14472, '巧家县', '巧家县', 50),
(14483, 19, 14472, '威信县', '威信县', 55),
(14484, 19, 14436, '普洱市', '普洱市', 25),
(14485, 19, 14484, '孟连县', '孟连县', 5),
(14486, 19, 14484, '思茅区', '思茅区', 10),
(14487, 19, 14484, '宁洱县', '宁洱县', 15),
(14488, 19, 14484, '景东县', '景东县', 20),
(14489, 19, 14484, '镇沅县', '镇沅县', 25),
(14490, 19, 14484, '景谷县', '景谷县', 30),
(14491, 19, 14484, '墨江县', '墨江县', 35),
(14492, 19, 14484, '澜沧县', '澜沧县', 40),
(14493, 19, 14484, '西盟县', '西盟县', 45),
(14494, 19, 14484, '江城县', '江城县', 50),
(14495, 19, 14436, '临沧市', '临沧市', 30),
(14496, 19, 14495, '双江县', '双江县', 5),
(14497, 19, 14495, '沧源县', '沧源县', 10),
(14498, 19, 14495, '临翔区', '临翔区', 15),
(14499, 19, 14495, '镇康县', '镇康县', 20),
(14500, 19, 14495, '凤庆县', '凤庆县', 25),
(14501, 19, 14495, '云县', '云县', 30),
(14502, 19, 14495, '永德县', '永德县', 35),
(14503, 19, 14495, '耿马县', '耿马县', 40),
(14504, 19, 14436, '保山市', '保山市', 35),
(14505, 19, 14504, '隆阳区', '隆阳区', 5),
(14506, 19, 14504, '施甸县', '施甸县', 10),
(14507, 19, 14504, '昌宁县', '昌宁县', 15),
(14508, 19, 14504, '龙陵县', '龙陵县', 20),
(14509, 19, 14504, '腾冲县', '腾冲县', 25),
(14510, 19, 14436, '丽江市', '丽江市', 40),
(14511, 19, 14510, '玉龙县', '玉龙县', 5),
(14512, 19, 14510, '华坪县', '华坪县', 10),
(14513, 19, 14510, '永胜县', '永胜县', 15),
(14514, 19, 14510, '宁蒗县', '宁蒗县', 20),
(14515, 19, 14510, '古城区', '古城区', 25),
(14516, 19, 14436, '文山州', '文山州', 45),
(14517, 19, 14516, '文山市', '文山市', 5),
(14518, 19, 14516, '麻栗坡县', '麻栗坡县', 10),
(14519, 19, 14516, '砚山县', '砚山县', 15),
(14520, 19, 14516, '广南县', '广南县', 20),
(14521, 19, 14516, '马关县', '马关县', 25),
(14522, 19, 14516, '富宁县', '富宁县', 30),
(14523, 19, 14516, '西畴县', '西畴县', 35),
(14524, 19, 14516, '丘北县', '丘北县', 40),
(14525, 19, 14436, '红河州', '红河州', 50),
(14526, 19, 14525, '个旧市', '个旧市', 5),
(14527, 19, 14525, '开远市', '开远市', 10),
(14528, 19, 14525, '弥勒县', '弥勒县', 15),
(14529, 19, 14525, '红河县', '红河县', 20),
(14530, 19, 14525, '绿春县', '绿春县', 25),
(14531, 19, 14525, '蒙自市', '蒙自市', 30),
(14532, 19, 14525, '泸西县', '泸西县', 35),
(14533, 19, 14525, '建水县', '建水县', 40),
(14534, 19, 14525, '元阳县', '元阳县', 45),
(14535, 19, 14525, '石屏县', '石屏县', 50),
(14536, 19, 14525, '金平县', '金平县', 55),
(14537, 19, 14525, '屏边县', '屏边县', 60),
(14538, 19, 14525, '河口县', '河口县', 65),
(14539, 19, 14436, '西双版纳州', '西双版纳州', 55),
(14540, 19, 14539, '景洪市', '景洪市', 5),
(14541, 19, 14539, '勐海县', '勐海县', 10),
(14542, 19, 14539, '勐腊县', '勐腊县', 15),
(14543, 19, 14436, '楚雄州', '楚雄州', 60),
(14544, 19, 14543, '元谋县', '元谋县', 5),
(14545, 19, 14543, '南华县', '南华县', 10),
(14546, 19, 14543, '牟定县', '牟定县', 15),
(14547, 19, 14543, '武定县', '武定县', 20),
(14548, 19, 14543, '大姚县', '大姚县', 25),
(14549, 19, 14543, '双柏县', '双柏县', 30),
(14550, 19, 14543, '禄丰县', '禄丰县', 35),
(14551, 19, 14543, '永仁县', '永仁县', 40),
(14552, 19, 14543, '姚安县', '姚安县', 45),
(14553, 19, 14543, '楚雄市', '楚雄市', 50),
(14554, 19, 14436, '大理州', '大理州', 65),
(14555, 19, 14554, '剑川县', '剑川县', 5),
(14556, 19, 14554, '弥渡县', '弥渡县', 10),
(14557, 19, 14554, '云龙县', '云龙县', 15),
(14558, 19, 14554, '洱源县', '洱源县', 20),
(14559, 19, 14554, '鹤庆县', '鹤庆县', 25),
(14560, 19, 14554, '宾川县', '宾川县', 30),
(14561, 19, 14554, '祥云县', '祥云县', 35),
(14562, 19, 14554, '永平县', '永平县', 40),
(14563, 19, 14554, '巍山县', '巍山县', 45),
(14564, 19, 14554, '漾濞县', '漾濞县', 50),
(14565, 19, 14554, '南涧县', '南涧县', 55),
(14566, 19, 14554, '大理市', '大理市', 60),
(14567, 19, 14436, '德宏州', '德宏州', 70),
(14568, 19, 14567, '芒市', '芒市', 5),
(14569, 19, 14567, '瑞丽市', '瑞丽市', 10),
(14570, 19, 14567, '盈江县', '盈江县', 15),
(14571, 19, 14567, '梁河县', '梁河县', 20),
(14572, 19, 14567, '陇川县', '陇川县', 25),
(14573, 19, 14436, '怒江州', '怒江州', 75),
(14574, 19, 14573, '泸水县', '泸水县', 5),
(14575, 19, 14573, '福贡县', '福贡县', 10),
(14576, 19, 14573, '兰坪县', '兰坪县', 15),
(14577, 19, 14573, '贡山县', '贡山县', 20),
(14578, 19, 14436, '迪庆州', '迪庆州', 80),
(14579, 19, 14578, '香格里拉县', '香格里拉县', 5),
(14580, 19, 14578, '德钦县', '德钦县', 10),
(14581, 19, 14578, '维西县', '维西县', 15),
(14582, 19, 0, '西藏自治区', '西藏自治区', 130),
(14583, 19, 14582, '拉萨市', '拉萨市', 5),
(14584, 19, 14583, '城关区', '城关区', 5),
(14585, 19, 14583, '林周县', '林周县', 10),
(14586, 19, 14583, '当雄县', '当雄县', 15),
(14587, 19, 14583, '尼木县', '尼木县', 20),
(14588, 19, 14583, '曲水县', '曲水县', 25),
(14589, 19, 14583, '堆龙德庆县', '堆龙德庆县', 30),
(14590, 19, 14583, '达孜县', '达孜县', 35),
(14591, 19, 14583, '墨竹工卡县', '墨竹工卡县', 40),
(14592, 19, 14582, '那曲地区', '那曲地区', 10),
(14593, 19, 14592, '索县', '索县', 5),
(14594, 19, 14592, '那曲县', '那曲县', 10),
(14595, 19, 14592, '嘉黎县', '嘉黎县', 15),
(14596, 19, 14592, '比如县', '比如县', 20),
(14597, 19, 14592, '聂荣县', '聂荣县', 25),
(14598, 19, 14592, '安多县', '安多县', 30),
(14599, 19, 14592, '申扎县', '申扎县', 35),
(14600, 19, 14592, '班戈县', '班戈县', 40),
(14601, 19, 14592, '巴青县', '巴青县', 45),
(14602, 19, 14592, '尼玛县', '尼玛县', 50),
(14603, 19, 14582, '山南地区', '山南地区', 15),
(14604, 19, 14603, '贡嘎县', '贡嘎县', 5),
(14605, 19, 14603, '扎囊县', '扎囊县', 10),
(14606, 19, 14603, '乃东县', '乃东县', 15),
(14607, 19, 14603, '桑日县', '桑日县', 20),
(14608, 19, 14603, '琼结县', '琼结县', 25),
(14609, 19, 14603, '曲松县', '曲松县', 30),
(14610, 19, 14603, '措美县', '措美县', 35),
(14611, 19, 14603, '洛扎县', '洛扎县', 40),
(14612, 19, 14603, '加查县', '加查县', 45),
(14613, 19, 14603, '隆子县', '隆子县', 50),
(14614, 19, 14603, '错那县', '错那县', 55),
(14615, 19, 14603, '浪卡子县', '浪卡子县', 60),
(14616, 19, 14582, '昌都地区', '昌都地区', 20),
(14617, 19, 14616, '昌都县', '昌都县', 5),
(14618, 19, 14616, '江达县', '江达县', 10),
(14619, 19, 14616, '贡觉县', '贡觉县', 15),
(14620, 19, 14616, '类乌齐县', '类乌齐县', 20),
(14621, 19, 14616, '丁青县', '丁青县', 25),
(14622, 19, 14616, '察雅县', '察雅县', 30),
(14623, 19, 14616, '八宿县', '八宿县', 35),
(14624, 19, 14616, '左贡县', '左贡县', 40),
(14625, 19, 14616, '芒康县', '芒康县', 45),
(14626, 19, 14616, '洛隆县', '洛隆县', 50),
(14627, 19, 14616, '边坝县', '边坝县', 55),
(14628, 19, 14582, '日喀则地区', '日喀则地区', 25),
(14629, 19, 14628, '聂拉木县', '聂拉木县', 5),
(14630, 19, 14628, '昂仁县', '昂仁县', 10),
(14631, 19, 14628, '日喀则市', '日喀则市', 15),
(14632, 19, 14628, '南木林县', '南木林县', 20),
(14633, 19, 14628, '江孜县', '江孜县', 25),
(14634, 19, 14628, '定日县', '定日县', 30),
(14635, 19, 14628, '萨迦县　', '萨迦县　', 35),
(14636, 19, 14628, '拉孜县', '拉孜县', 40),
(14637, 19, 14628, '谢通门县', '谢通门县', 45),
(14638, 19, 14628, '白朗县', '白朗县', 50),
(14639, 19, 14628, '仁布县', '仁布县', 55),
(14640, 19, 14628, '康马县', '康马县', 60),
(14641, 19, 14628, '定结县', '定结县', 65),
(14642, 19, 14628, '仲巴县', '仲巴县', 70),
(14643, 19, 14628, '亚东县', '亚东县', 75),
(14644, 19, 14628, '吉隆县', '吉隆县', 80),
(14645, 19, 14628, '萨嘎县', '萨嘎县', 85),
(14646, 19, 14628, '岗巴县', '岗巴县', 90),
(14647, 19, 14582, '阿里地区', '阿里地区', 30),
(14648, 19, 14647, '噶尔县', '噶尔县', 5),
(14649, 19, 14647, '普兰县', '普兰县', 10),
(14650, 19, 14647, '札达县　', '札达县　', 15),
(14651, 19, 14647, '日土县', '日土县', 20),
(14652, 19, 14647, '革吉县', '革吉县', 25),
(14653, 19, 14647, '改则县', '改则县', 30),
(14654, 19, 14647, '措勤县', '措勤县', 35),
(14655, 19, 14582, '林芝地区', '林芝地区', 35),
(14656, 19, 14655, '林芝县', '林芝县', 5),
(14657, 19, 14655, '工布江达县', '工布江达县', 10),
(14658, 19, 14655, '米林县', '米林县', 15),
(14659, 19, 14655, '墨脱县', '墨脱县', 20),
(14660, 19, 14655, '波密县', '波密县', 25),
(14661, 19, 14655, '察隅县', '察隅县', 30),
(14662, 19, 14655, '朗县', '朗县', 35),
(14663, 19, 0, '陕西省', '陕西省', 135),
(14664, 19, 14663, '西安市', '西安市', 5),
(14665, 19, 14664, '新城区', '新城区', 5),
(14666, 19, 14664, '雁塔区', '雁塔区', 10),
(14667, 19, 14664, '未央区', '未央区', 15),
(14668, 19, 14664, '长安区', '长安区', 20),
(14669, 19, 14664, '灞桥区', '灞桥区', 25),
(14670, 19, 14664, '碑林区', '碑林区', 30),
(14671, 19, 14664, '莲湖区', '莲湖区', 35),
(14672, 19, 14664, '临潼区', '临潼区', 40),
(14673, 19, 14664, '阎良区', '阎良区', 45),
(14674, 19, 14664, '杨凌农业示范区', '杨凌农业示范区', 50),
(14675, 19, 14664, '西安武警工程学院', '西安武警工程学院', 55),
(14676, 19, 14664, '高陵县', '高陵县', 60),
(14677, 19, 14664, '蓝田县', '蓝田县', 65),
(14678, 19, 14664, '户县', '户县', 70),
(14679, 19, 14664, '周至县', '周至县', 75),
(14680, 19, 14663, '铜川市', '铜川市', 10),
(14681, 19, 14680, '印台区', '印台区', 5),
(14682, 19, 14680, '宜君县', '宜君县', 10),
(14683, 19, 14680, '王益区', '王益区', 15),
(14684, 19, 14680, '耀州区', '耀州区', 20),
(14685, 19, 14663, '宝鸡市', '宝鸡市', 15),
(14686, 19, 14685, '渭滨区', '渭滨区', 5),
(14687, 19, 14685, '金台区', '金台区', 10),
(14688, 19, 14685, '岐山县', '岐山县', 15),
(14689, 19, 14685, '太白县', '太白县', 20),
(14690, 19, 14685, '凤翔县', '凤翔县', 25),
(14691, 19, 14685, '陇县', '陇县', 30),
(14692, 19, 14685, '麟游县', '麟游县', 35),
(14693, 19, 14685, '千阳县', '千阳县', 40),
(14694, 19, 14685, '扶风县', '扶风县', 45),
(14695, 19, 14685, '凤县', '凤县', 50),
(14696, 19, 14685, '眉县', '眉县', 55),
(14697, 19, 14685, '陈仓区', '陈仓区', 60),
(14698, 19, 14663, '咸阳市', '咸阳市', 20),
(14699, 19, 14698, '秦都区', '秦都区', 5),
(14700, 19, 14698, '渭城区', '渭城区', 10),
(14701, 19, 14698, '兴平市', '兴平市', 15),
(14702, 19, 14698, '礼泉县', '礼泉县', 20),
(14703, 19, 14698, '泾阳县', '泾阳县', 25),
(14704, 19, 14698, '永寿县', '永寿县', 30),
(14705, 19, 14698, '三原县', '三原县', 35),
(14706, 19, 14698, '彬县', '彬县', 40),
(14707, 19, 14698, '旬邑县', '旬邑县', 45),
(14708, 19, 14698, '长武县', '长武县', 50),
(14709, 19, 14698, '乾县', '乾县', 55),
(14710, 19, 14698, '武功县', '武功县', 60),
(14711, 19, 14698, '淳化县', '淳化县', 65),
(14712, 19, 14698, '杨陵区', '杨陵区', 70),
(14713, 19, 14663, '渭南市', '渭南市', 25),
(14714, 19, 14713, '韩城市', '韩城市', 5),
(14715, 19, 14713, '华阴市', '华阴市', 10),
(14716, 19, 14713, '蒲城县', '蒲城县', 15),
(14717, 19, 14713, '华县', '华县', 20),
(14718, 19, 14713, '潼关县', '潼关县', 25),
(14719, 19, 14713, '大荔县', '大荔县', 30),
(14720, 19, 14713, '澄城县', '澄城县', 35),
(14721, 19, 14713, '合阳县', '合阳县', 40),
(14722, 19, 14713, '白水县', '白水县', 45),
(14723, 19, 14713, '富平县', '富平县', 50),
(14724, 19, 14713, '临渭区', '临渭区', 55),
(14725, 19, 14663, '延安市', '延安市', 30),
(14726, 19, 14725, '宝塔区', '宝塔区', 5),
(14727, 19, 14725, '安塞县', '安塞县', 10),
(14728, 19, 14725, '洛川县', '洛川县', 15),
(14729, 19, 14725, '子长县', '子长县', 20),
(14730, 19, 14725, '黄陵县', '黄陵县', 25),
(14731, 19, 14725, '延长县', '延长县', 30),
(14732, 19, 14725, '宜川县', '宜川县', 35),
(14733, 19, 14725, '延川县', '延川县', 40),
(14734, 19, 14725, '甘泉县', '甘泉县', 45),
(14735, 19, 14725, '富县', '富县', 50),
(14736, 19, 14725, '志丹县', '志丹县', 55),
(14737, 19, 14725, '黄龙县', '黄龙县', 60),
(14738, 19, 14725, '吴起县', '吴起县', 65),
(14739, 19, 14663, '汉中市', '汉中市', 35),
(14740, 19, 14739, '汉台区', '汉台区', 5),
(14741, 19, 14739, '南郑县', '南郑县', 10),
(14742, 19, 14739, '城固县', '城固县', 15),
(14743, 19, 14739, '洋县', '洋县', 20),
(14744, 19, 14739, '佛坪县', '佛坪县', 25),
(14745, 19, 14739, '留坝县', '留坝县', 30),
(14746, 19, 14739, '镇巴县', '镇巴县', 35),
(14747, 19, 14739, '西乡县', '西乡县', 40),
(14748, 19, 14739, '勉县', '勉县', 45),
(14749, 19, 14739, '略阳县', '略阳县', 50),
(14750, 19, 14739, '宁强县', '宁强县', 55),
(14751, 19, 14663, '榆林市', '榆林市', 40),
(14752, 19, 14751, '清涧县', '清涧县', 5),
(14753, 19, 14751, '绥德县', '绥德县', 10),
(14754, 19, 14751, '佳县', '佳县', 15),
(14755, 19, 14751, '神木县', '神木县', 20),
(14756, 19, 14751, '府谷县', '府谷县', 25),
(14757, 19, 14751, '子洲县', '子洲县', 30),
(14758, 19, 14751, '横山县', '横山县', 35),
(14759, 19, 14751, '米脂县', '米脂县', 40),
(14760, 19, 14751, '吴堡县', '吴堡县', 45),
(14761, 19, 14751, '定边县', '定边县', 50),
(14762, 19, 14751, '榆阳区', '榆阳区', 55),
(14763, 19, 14751, '靖边县', '靖边县', 60),
(14764, 19, 14663, '商洛市', '商洛市', 45),
(14765, 19, 14764, '商州区', '商州区', 5),
(14766, 19, 14764, '镇安县', '镇安县', 10),
(14767, 19, 14764, '山阳县', '山阳县', 15),
(14768, 19, 14764, '洛南县', '洛南县', 20),
(14769, 19, 14764, '商南县', '商南县', 25),
(14770, 19, 14764, '丹凤县', '丹凤县', 30),
(14771, 19, 14764, '柞水县', '柞水县', 35),
(14772, 19, 14663, '安康市', '安康市', 50),
(14773, 19, 14772, '汉滨区', '汉滨区', 5),
(14774, 19, 14772, '紫阳县', '紫阳县', 10),
(14775, 19, 14772, '岚皋县', '岚皋县', 15),
(14776, 19, 14772, '旬阳县', '旬阳县', 20),
(14777, 19, 14772, '镇坪县', '镇坪县', 25),
(14778, 19, 14772, '平利县', '平利县', 30),
(14779, 19, 14772, '宁陕县', '宁陕县', 35),
(14780, 19, 14772, '汉阴县', '汉阴县', 40),
(14781, 19, 14772, '石泉县', '石泉县', 45),
(14782, 19, 14772, '白河县', '白河县', 50),
(14783, 19, 0, '甘肃省', '甘肃省', 140),
(14784, 19, 14783, '兰州市', '兰州市', 5),
(14785, 19, 14784, '七里河区', '七里河区', 5),
(14786, 19, 14784, '安宁区', '安宁区', 10),
(14787, 19, 14784, '城关区', '城关区', 15),
(14788, 19, 14784, '西固区', '西固区', 20),
(14789, 19, 14784, '红古区', '红古区', 25),
(14790, 19, 14784, '永登县', '永登县', 30),
(14791, 19, 14784, '榆中县', '榆中县', 35),
(14792, 19, 14784, '皋兰县', '皋兰县', 40),
(14793, 19, 14783, '金昌市', '金昌市', 10),
(14794, 19, 14793, '永昌县', '永昌县', 5),
(14795, 19, 14793, '金川区', '金川区', 10),
(14796, 19, 14783, '白银市', '白银市', 15),
(14797, 19, 14796, '白银区', '白银区', 5),
(14798, 19, 14796, '平川区', '平川区', 10),
(14799, 19, 14796, '靖远县', '靖远县', 15),
(14800, 19, 14796, '景泰县', '景泰县', 20),
(14801, 19, 14796, '会宁县', '会宁县', 25),
(14802, 19, 14783, '天水市', '天水市', 20),
(14803, 19, 14802, '麦积区', '麦积区', 5),
(14804, 19, 14802, '秦州区', '秦州区', 10),
(14805, 19, 14802, '甘谷县', '甘谷县', 15),
(14806, 19, 14802, '武山县', '武山县', 20),
(14807, 19, 14802, '清水县', '清水县', 25),
(14808, 19, 14802, '秦安县', '秦安县', 30),
(14809, 19, 14802, '张家川县', '张家川县', 35),
(14810, 19, 14783, '嘉峪关市', '嘉峪关市', 25),
(14811, 19, 14810, '长城区', '长城区', 5),
(14812, 19, 14810, '镜铁区', '镜铁区', 10),
(14813, 19, 14810, '雄关区', '雄关区', 15),
(14814, 19, 14783, '平凉市', '平凉市', 30),
(14815, 19, 14814, '静宁县', '静宁县', 5),
(14816, 19, 14814, '崆峒区', '崆峒区', 10),
(14817, 19, 14814, '华亭县', '华亭县', 15),
(14818, 19, 14814, '崇信县', '崇信县', 20),
(14819, 19, 14814, '泾川县', '泾川县', 25),
(14820, 19, 14814, '灵台县', '灵台县', 30),
(14821, 19, 14814, '镇原县', '镇原县', 35),
(14822, 19, 14814, '庄浪县', '庄浪县', 40),
(14823, 19, 14783, '庆阳市', '庆阳市', 35),
(14824, 19, 14823, '庆城县', '庆城县', 5),
(14825, 19, 14823, '西峰区', '西峰区', 10),
(14826, 19, 14823, '镇原县', '镇原县', 15),
(14827, 19, 14823, '合水县', '合水县', 20),
(14828, 19, 14823, '华池县', '华池县', 25),
(14829, 19, 14823, '环县', '环县', 30),
(14830, 19, 14823, '宁县', '宁县', 35),
(14831, 19, 14823, '正宁县', '正宁县', 40),
(14832, 19, 14783, '陇南市', '陇南市', 40),
(14833, 19, 14832, '成县', '成县', 5),
(14834, 19, 14832, '礼县', '礼县', 10),
(14835, 19, 14832, '康县', '康县', 15),
(14836, 19, 14832, '武都区', '武都区', 20),
(14837, 19, 14832, '文县', '文县', 25),
(14838, 19, 14832, '两当县', '两当县', 30),
(14839, 19, 14832, '徽县', '徽县', 35),
(14840, 19, 14832, '宕昌县', '宕昌县', 40),
(14841, 19, 14832, '西和县', '西和县', 45),
(14842, 19, 14783, '武威市', '武威市', 45),
(14843, 19, 14842, '凉州区', '凉州区', 5),
(14844, 19, 14842, '古浪县', '古浪县', 10),
(14845, 19, 14842, '天祝县', '天祝县', 15),
(14846, 19, 14842, '民勤县', '民勤县', 20),
(14847, 19, 14783, '张掖市', '张掖市', 50),
(14848, 19, 14847, '甘州区', '甘州区', 5),
(14849, 19, 14847, '山丹县', '山丹县', 10),
(14850, 19, 14847, '临泽县', '临泽县', 15),
(14851, 19, 14847, '高台县', '高台县', 20),
(14852, 19, 14847, '肃南县', '肃南县', 25),
(14853, 19, 14847, '民乐县', '民乐县', 30),
(14854, 19, 14783, '酒泉市', '酒泉市', 55),
(14855, 19, 14854, '金塔县', '金塔县', 5),
(14856, 19, 14854, '阿克塞县', '阿克塞县', 10),
(14857, 19, 14854, '肃北县', '肃北县', 15),
(14858, 19, 14854, '瓜州县', '瓜州县', 20),
(14859, 19, 14854, '肃州区', '肃州区', 25),
(14860, 19, 14854, '玉门市', '玉门市', 30),
(14861, 19, 14854, '敦煌市', '敦煌市', 35),
(14862, 19, 14783, '甘南州', '甘南州', 60),
(14863, 19, 14862, '合作市', '合作市', 5),
(14864, 19, 14862, '夏河县', '夏河县', 10),
(14865, 19, 14862, '碌曲县', '碌曲县', 15),
(14866, 19, 14862, '舟曲县', '舟曲县', 20),
(14867, 19, 14862, '玛曲县', '玛曲县', 25),
(14868, 19, 14862, '迭部县', '迭部县', 30),
(14869, 19, 14862, '临潭县', '临潭县', 35),
(14870, 19, 14862, '卓尼县', '卓尼县', 40),
(14871, 19, 14783, '临夏州', '临夏州', 65),
(14872, 19, 14871, '临夏县', '临夏县', 5),
(14873, 19, 14871, '康乐县', '康乐县', 10),
(14874, 19, 14871, '永靖县', '永靖县', 15),
(14875, 19, 14871, '和政县', '和政县', 20),
(14876, 19, 14871, '东乡族自治县', '东乡族自治县', 25),
(14877, 19, 14871, '积石山县', '积石山县', 30),
(14878, 19, 14871, '临夏市', '临夏市', 35),
(14879, 19, 14871, '广河县', '广河县', 40),
(14880, 19, 14783, '定西市', '定西市', 70),
(14881, 19, 14880, '岷县', '岷县', 5),
(14882, 19, 14880, '安定区', '安定区', 10),
(14883, 19, 14880, '通渭县', '通渭县', 15),
(14884, 19, 14880, '临洮县', '临洮县', 20),
(14885, 19, 14880, '漳县', '漳县', 25),
(14886, 19, 14880, '渭源县', '渭源县', 30),
(14887, 19, 14880, '陇西县', '陇西县', 35),
(14888, 19, 0, '青海省', '青海省', 145),
(14889, 19, 14888, '西宁市', '西宁市', 5),
(14890, 19, 14889, '湟中县', '湟中县', 5),
(14891, 19, 14889, '湟源县', '湟源县', 10),
(14892, 19, 14889, '大通县', '大通县', 15),
(14893, 19, 14889, '城中区', '城中区', 20),
(14894, 19, 14889, '城东区', '城东区', 25),
(14895, 19, 14889, '城西区', '城西区', 30),
(14896, 19, 14889, '城北区', '城北区', 35),
(14897, 19, 14888, '海东地区', '海东地区', 10),
(14898, 19, 14897, '平安县', '平安县', 5),
(14899, 19, 14897, '乐都县', '乐都县', 10),
(14900, 19, 14897, '民和县', '民和县', 15),
(14901, 19, 14897, '互助县', '互助县', 20),
(14902, 19, 14897, '化隆县', '化隆县', 25),
(14903, 19, 14897, '循化县', '循化县', 30),
(14904, 19, 14888, '海北州', '海北州', 15),
(14905, 19, 14904, '海晏县', '海晏县', 5),
(14906, 19, 14904, '祁连县', '祁连县', 10),
(14907, 19, 14904, '刚察县', '刚察县', 15),
(14908, 19, 14904, '门源县', '门源县', 20),
(14909, 19, 14888, '黄南州', '黄南州', 20),
(14910, 19, 14909, '尖扎县', '尖扎县', 5),
(14911, 19, 14909, '同仁县', '同仁县', 10),
(14912, 19, 14909, '泽库县', '泽库县', 15),
(14913, 19, 14909, '河南县', '河南县', 20),
(14914, 19, 14888, '海南州', '海南州', 25),
(14915, 19, 14914, '共和县', '共和县', 5),
(14916, 19, 14914, '同德县', '同德县', 10),
(14917, 19, 14914, '贵德县', '贵德县', 15),
(14918, 19, 14914, '兴海县', '兴海县', 20),
(14919, 19, 14914, '贵南县', '贵南县', 25),
(14920, 19, 14888, '果洛州', '果洛州', 30),
(14921, 19, 14920, '玛沁县', '玛沁县', 5),
(14922, 19, 14920, '甘德县', '甘德县', 10),
(14923, 19, 14920, '达日县', '达日县', 15),
(14924, 19, 14920, '班玛县', '班玛县', 20),
(14925, 19, 14920, '久治县', '久治县', 25),
(14926, 19, 14920, '玛多县', '玛多县', 30),
(14927, 19, 14888, '玉树州', '玉树州', 35),
(14928, 19, 14927, '玉树县', '玉树县', 5),
(14929, 19, 14927, '称多县', '称多县', 10),
(14930, 19, 14927, '囊谦县', '囊谦县', 15),
(14931, 19, 14927, '杂多县', '杂多县', 20),
(14932, 19, 14927, '治多县', '治多县', 25),
(14933, 19, 14927, '曲麻莱县', '曲麻莱县', 30),
(14934, 19, 14888, '海西州', '海西州', 40),
(14935, 19, 14934, '德令哈市', '德令哈市', 5),
(14936, 19, 14934, '乌兰县', '乌兰县', 10),
(14937, 19, 14934, '天峻县', '天峻县', 15),
(14938, 19, 14934, '都兰县', '都兰县', 20),
(14939, 19, 14934, '大柴旦行委', '大柴旦行委', 25),
(14940, 19, 14934, '冷湖行委', '冷湖行委', 30),
(14941, 19, 14934, '茫崖行委', '茫崖行委', 35),
(14942, 19, 14934, '格尔木市', '格尔木市', 40),
(14943, 19, 0, '宁夏回族自治区', '宁夏回族自治区', 150),
(14944, 19, 14943, '银川市', '银川市', 5),
(14945, 19, 14944, '灵武市', '灵武市', 5),
(14946, 19, 14944, '永宁县', '永宁县', 10),
(14947, 19, 14944, '贺兰县', '贺兰县', 15),
(14948, 19, 14944, '兴庆区', '兴庆区', 20),
(14949, 19, 14944, '金凤区', '金凤区', 25),
(14950, 19, 14944, '西夏区', '西夏区', 30),
(14951, 19, 14943, '石嘴山市', '石嘴山市', 10),
(14952, 19, 14951, '平罗县', '平罗县', 5),
(14953, 19, 14951, '惠农区', '惠农区', 10),
(14954, 19, 14951, '大武口区', '大武口区', 15),
(14955, 19, 14943, '吴忠市', '吴忠市', 15),
(14956, 19, 14955, '青铜峡市', '青铜峡市', 5),
(14957, 19, 14955, '同心县', '同心县', 10),
(14958, 19, 14955, '盐池县', '盐池县', 15),
(14959, 19, 14955, '红寺堡开发区', '红寺堡开发区', 20),
(14960, 19, 14955, '利通区', '利通区', 25),
(14961, 19, 14943, '固原市', '固原市', 20),
(14962, 19, 14961, '西吉县', '西吉县', 5),
(14963, 19, 14961, '隆德县', '隆德县', 10),
(14964, 19, 14961, '泾源县', '泾源县', 15),
(14965, 19, 14961, '彭阳县', '彭阳县', 20),
(14966, 19, 14961, '原州区', '原州区', 25),
(14967, 19, 14943, '中卫市', '中卫市', 25),
(14968, 19, 14967, '中宁县', '中宁县', 5),
(14969, 19, 14967, '海原县', '海原县', 10),
(14970, 19, 14967, '沙坡头区', '沙坡头区', 15),
(14971, 19, 0, '新疆维吾尔自治区', '新疆维吾尔自治区', 155),
(14972, 19, 14971, '乌鲁木齐市', '乌鲁木齐市', 5),
(14973, 19, 14972, '天山区', '天山区', 5),
(14974, 19, 14972, '头屯河区', '头屯河区', 10),
(14975, 19, 14972, '达坂城区', '达坂城区', 15),
(14976, 19, 14972, '米东区', '米东区', 20),
(14977, 19, 14972, '新市区', '新市区', 25),
(14978, 19, 14972, '沙依巴克区', '沙依巴克区', 30),
(14979, 19, 14972, '水磨沟区', '水磨沟区', 35),
(14980, 19, 14972, '乌鲁木齐县', '乌鲁木齐县', 40),
(14981, 19, 14971, '克拉玛依市', '克拉玛依市', 10),
(14982, 19, 14981, '克拉玛依区', '克拉玛依区', 5),
(14983, 19, 14981, '独山子区', '独山子区', 10),
(14984, 19, 14981, '乌尔禾区', '乌尔禾区', 15),
(14985, 19, 14981, '白碱滩区', '白碱滩区', 20),
(14986, 19, 14971, '石河子市', '石河子市', 15),
(14987, 19, 14986, '石河子市', '石河子市', 5),
(14988, 19, 14971, '吐鲁番地区', '吐鲁番地区', 20),
(14989, 19, 14988, '吐鲁番市', '吐鲁番市', 5),
(14990, 19, 14988, '托克逊县', '托克逊县', 10),
(14991, 19, 14988, '鄯善县', '鄯善县', 15),
(14992, 19, 14971, '哈密地区', '哈密地区', 25),
(14993, 19, 14992, '哈密市', '哈密市', 5),
(14994, 19, 14992, '巴里坤县', '巴里坤县', 10),
(14995, 19, 14992, '伊吾县', '伊吾县', 15),
(14996, 19, 14971, '和田地区', '和田地区', 30),
(14997, 19, 14996, '和田县', '和田县', 5),
(14998, 19, 14996, '和田市', '和田市', 10),
(14999, 19, 14996, '墨玉县', '墨玉县', 15),
(15000, 19, 14996, '洛浦县', '洛浦县', 20),
(15001, 19, 14996, '策勒县', '策勒县', 25),
(15002, 19, 14996, '于田县', '于田县', 30),
(15003, 19, 14996, '民丰县', '民丰县', 35),
(15004, 19, 14996, '皮山县', '皮山县', 40),
(15005, 19, 14971, '阿克苏地区', '阿克苏地区', 35),
(15006, 19, 15005, '阿拉尔市', '阿拉尔市', 5),
(15007, 19, 15005, '阿克苏市', '阿克苏市', 10),
(15008, 19, 15005, '温宿县', '温宿县', 15),
(15009, 19, 15005, '沙雅县', '沙雅县', 20),
(15010, 19, 15005, '拜城县', '拜城县', 25),
(15011, 19, 15005, '阿瓦提县', '阿瓦提县', 30),
(15012, 19, 15005, '库车县', '库车县', 35),
(15013, 19, 15005, '柯坪县', '柯坪县', 40),
(15014, 19, 15005, '新和县', '新和县', 45),
(15015, 19, 15005, '乌什县', '乌什县', 50),
(15016, 19, 14971, '喀什地区', '喀什地区', 40),
(15017, 19, 15016, '喀什市', '喀什市', 5),
(15018, 19, 15016, '巴楚县', '巴楚县', 10),
(15019, 19, 15016, '泽普县', '泽普县', 15),
(15020, 19, 15016, '伽师县', '伽师县', 20),
(15021, 19, 15016, '叶城县', '叶城县', 25),
(15022, 19, 15016, '岳普湖县', '岳普湖县', 30),
(15023, 19, 15016, '疏附县', '疏附县', 35),
(15024, 19, 15016, '疏勒县', '疏勒县', 40),
(15025, 19, 15016, '英吉沙县', '英吉沙县', 45),
(15026, 19, 15016, '麦盖提县', '麦盖提县', 50),
(15027, 19, 15016, '莎车县', '莎车县', 55),
(15028, 19, 15016, '塔什库尔干县', '塔什库尔干县', 60),
(15029, 19, 14971, '克孜勒苏州', '克孜勒苏州', 45),
(15030, 19, 15029, '阿图什市', '阿图什市', 5),
(15031, 19, 15029, '阿合奇县', '阿合奇县', 10),
(15032, 19, 15029, '乌恰县', '乌恰县', 15),
(15033, 19, 15029, '阿克陶县', '阿克陶县', 20),
(15034, 19, 14971, '巴音郭楞州', '巴音郭楞州', 50),
(15035, 19, 15034, '库尔勒市', '库尔勒市', 5),
(15036, 19, 15034, '尉犁县', '尉犁县', 10),
(15037, 19, 15034, '和静县', '和静县', 15),
(15038, 19, 15034, '博湖县', '博湖县', 20),
(15039, 19, 15034, '和硕县', '和硕县', 25),
(15040, 19, 15034, '轮台县', '轮台县', 30),
(15041, 19, 15034, '若羌县', '若羌县', 35),
(15042, 19, 15034, '且末县', '且末县', 40),
(15043, 19, 15034, '焉耆县', '焉耆县', 45),
(15044, 19, 14971, '昌吉州', '昌吉州', 55),
(15045, 19, 15044, '昌吉市', '昌吉市', 5),
(15046, 19, 15044, '阜康市', '阜康市', 10),
(15047, 19, 15044, '奇台县', '奇台县', 15),
(15048, 19, 15044, '玛纳斯县', '玛纳斯县', 20),
(15049, 19, 15044, '吉木萨尔县', '吉木萨尔县', 25),
(15050, 19, 15044, '呼图壁县', '呼图壁县', 30),
(15051, 19, 15044, '木垒县', '木垒县', 35),
(15052, 19, 14971, '博尔塔拉州', '博尔塔拉州', 60),
(15053, 19, 15052, '博乐市', '博乐市', 5),
(15054, 19, 15052, '精河县', '精河县', 10),
(15055, 19, 15052, '温泉县', '温泉县', 15),
(15056, 19, 14971, '伊犁州', '伊犁州', 65),
(15057, 19, 15056, '伊宁县', '伊宁县', 5),
(15058, 19, 15056, '伊宁市', '伊宁市', 10),
(15059, 19, 15056, '特克斯县', '特克斯县', 15),
(15060, 19, 15056, '尼勒克县', '尼勒克县', 20),
(15061, 19, 15056, '昭苏县', '昭苏县', 25),
(15062, 19, 15056, '新源县', '新源县', 30),
(15063, 19, 15056, '霍城县', '霍城县', 35),
(15064, 19, 15056, '察布查尔县', '察布查尔县', 40),
(15065, 19, 15056, '巩留县', '巩留县', 45),
(15066, 19, 15056, '奎屯市', '奎屯市', 50),
(15067, 19, 14971, '塔城地区', '塔城地区', 70),
(15068, 19, 15067, '塔城市', '塔城市', 5),
(15069, 19, 15067, '乌苏市', '乌苏市', 10),
(15070, 19, 15067, '额敏县', '额敏县', 15),
(15071, 19, 15067, '裕民县', '裕民县', 20),
(15072, 19, 15067, '沙湾县', '沙湾县', 25),
(15073, 19, 15067, '托里县', '托里县', 30),
(15074, 19, 15067, '和布克赛尔县', '和布克赛尔县', 35),
(15075, 19, 14971, '阿勒泰地区', '阿勒泰地区', 75),
(15076, 19, 15075, '北屯市', '北屯市', 5),
(15077, 19, 15075, '阿勒泰市', '阿勒泰市', 10),
(15078, 19, 15075, '富蕴县', '富蕴县', 15),
(15079, 19, 15075, '青河县', '青河县', 20),
(15080, 19, 15075, '吉木乃县', '吉木乃县', 25),
(15081, 19, 15075, '布尔津县', '布尔津县', 30),
(15082, 19, 15075, '福海县', '福海县', 35),
(15083, 19, 15075, '哈巴河县', '哈巴河县', 40),
(15084, 19, 14971, '五家渠市', '五家渠市', 80),
(15085, 19, 15084, '五家渠市', '五家渠市', 5),
(15086, 19, 14971, '阿拉尔市', '阿拉尔市', 85),
(15087, 19, 15086, '阿拉尔市', '阿拉尔市', 5),
(15088, 19, 14971, '图木舒克市', '图木舒克市', 90),
(15089, 19, 15088, '图木舒克市', '图木舒克市', 5),
(15090, 19, 0, '台湾省', '台湾省', 160),
(15091, 19, 15090, '台北', '台北', 5),
(15092, 19, 15091, '台北县', '台北县', 5),
(15093, 19, 15091, '台北市', '台北市', 10),
(15094, 19, 15090, '高雄', '高雄', 10),
(15095, 19, 15094, '市区', '市区', 5),
(15096, 19, 15094, '东港', '东港', 10),
(15097, 19, 15094, '大武', '大武', 15),
(15098, 19, 15094, '恒春', '恒春', 20),
(15099, 19, 15094, '兰屿', '兰屿', 25),
(15100, 19, 15090, '台南', '台南', 15),
(15101, 19, 15090, '台中', '台中', 20),
(15102, 19, 15090, '桃园', '桃园', 25),
(15103, 19, 15090, '新竹', '新竹', 30),
(15104, 19, 15103, '新竹县', '新竹县', 5),
(15105, 19, 15103, '新竹市', '新竹市', 10),
(15106, 19, 15103, '公馆', '公馆', 15),
(15107, 19, 15090, '宜兰', '宜兰', 35),
(15108, 19, 15090, '澎湖', '澎湖', 40),
(15109, 19, 15108, '马公', '马公', 5),
(15110, 19, 15108, '东吉屿', '东吉屿', 10),
(15111, 19, 15090, '嘉义', '嘉义', 45),
(15112, 19, 15111, '嘉义', '嘉义', 5),
(15113, 19, 15111, '阿里山', '阿里山', 10),
(15114, 19, 15111, '玉山', '玉山', 15),
(15115, 19, 15111, '新港', '新港', 20),
(15116, 19, 15090, '花莲', '花莲', 50),
(15117, 19, 15090, '台东', '台东', 55),
(15118, 19, 15090, '基隆', '基隆', 60),
(15119, 19, 15118, '彭佳屿', '彭佳屿', 5),
(15120, 19, 0, '香港特别行政区', '香港特别行政区', 165),
(15121, 19, 15120, '市区', '市区', 5),
(15122, 19, 15120, '九龙', '九龙', 10),
(15123, 19, 15120, '新界', '新界', 15),
(15124, 19, 15120, '中环', '中环', 20),
(15125, 19, 15120, '铜锣湾', '铜锣湾', 25),
(15126, 19, 0, '澳门特别行政区', '澳门特别行政区', 170),
(15127, 19, 15126, '市区内', '市区内', 5),
(15128, 1, 0, '女', '2', 20);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_opt_group`
--

CREATE TABLE `qinggan_opt_group` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID ',
  `title` varchar(100) NOT NULL COMMENT '名称，用于后台管理',
  `link_symbol` varchar(10) NOT NULL COMMENT '连接字符，未设置使用英文竖线'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='可选菜单管理器';

--
-- 转存表中的数据 `qinggan_opt_group`
--

INSERT INTO `qinggan_opt_group` (`id`, `title`, `link_symbol`) VALUES
(1, '性别', ''),
(4, '是与否', ''),
(6, '窗口打开方式', ''),
(7, '注册', ''),
(8, '邮件编码', ''),
(12, '置顶属性', ''),
(13, '等候时间', ''),
(14, '微信菜单类型', ''),
(19, '中国省市县信息', '|');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order`
--

CREATE TABLE `qinggan_order` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `sn` varchar(255) NOT NULL COMMENT '订单编号，唯一值',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示游客',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '金额',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币类型',
  `currency_rate` decimal(13,8) UNSIGNED NOT NULL DEFAULT '1.00000000' COMMENT '货币汇率',
  `status` varchar(255) NOT NULL COMMENT '订单的最后状态',
  `status_title` varchar(255) NOT NULL COMMENT '订单状态说明',
  `endtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间',
  `passwd` varchar(255) NOT NULL COMMENT '密码串',
  `ext` text NOT NULL COMMENT '扩展内容信息，可用于存储一些扩展信息',
  `note` text NOT NULL COMMENT '摘要',
  `email` varchar(255) NOT NULL COMMENT '邮箱，用于接收通知',
  `mobile` varchar(50) NOT NULL COMMENT '手机号，用于短信发送'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单中心';

--
-- 转存表中的数据 `qinggan_order`
--

INSERT INTO `qinggan_order` (`id`, `sn`, `user_id`, `addtime`, `price`, `currency_id`, `currency_rate`, `status`, `status_title`, `endtime`, `passwd`, `ext`, `note`, `email`, `mobile`) VALUES
(1, 'P2018050390U00023001', 23, 1525283914, '1099.0000', 1, '6.16989994', 'shipping', '', 0, '756f4ced83c7af2e9cc9cdd57dad3ba9', '', '', '40782502@qq.com', '15818533971');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_address`
--

CREATE TABLE `qinggan_order_address` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
  `country` varchar(255) NOT NULL DEFAULT '中国' COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省信息',
  `city` varchar(255) NOT NULL COMMENT '市',
  `county` varchar(255) NOT NULL COMMENT '县',
  `address` varchar(255) NOT NULL COMMENT '地址信息（不含国家，省市县镇区信息）',
  `mobile` varchar(100) NOT NULL COMMENT '手机号码',
  `tel` varchar(100) NOT NULL COMMENT '电话号码',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `fullname` varchar(100) NOT NULL COMMENT '联系人姓名',
  `zipcode` varchar(50) NOT NULL COMMENT '邮编'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单地址库';

--
-- 转存表中的数据 `qinggan_order_address`
--

INSERT INTO `qinggan_order_address` (`id`, `order_id`, `country`, `province`, `city`, `county`, `address`, `mobile`, `tel`, `email`, `fullname`, `zipcode`) VALUES
(1, 1, '中国', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_express`
--

CREATE TABLE `qinggan_order_express` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `express_id` int(11) NOT NULL DEFAULT '0' COMMENT '物流ID号',
  `code` varchar(255) NOT NULL COMMENT '物流查询编码，可用于查询快递进度',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登记时间',
  `last_query_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次检索时间',
  `title` varchar(255) NOT NULL COMMENT '快递名称',
  `homepage` varchar(255) NOT NULL COMMENT '快递官网',
  `company` varchar(255) NOT NULL COMMENT '快递的公司全称',
  `is_end` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未结束1已结束'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单中涉及到的物流分配';

--
-- 转存表中的数据 `qinggan_order_express`
--

INSERT INTO `qinggan_order_express` (`id`, `order_id`, `express_id`, `code`, `addtime`, `last_query_time`, `title`, `homepage`, `company`, `is_end`) VALUES
(1, 1, 4, '820620736220', 1527680297, 1527680300, '顺丰速运', 'http://www.sf-express.com/', '顺丰速运(集团)有限公司', 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_invoice`
--

CREATE TABLE `qinggan_order_invoice` (
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `type` varchar(100) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `content` text NOT NULL COMMENT '发票内容',
  `note` text NOT NULL COMMENT '发票的备注信息'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单发票';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_log`
--

CREATE TABLE `qinggan_order_log` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_express_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '定单中的物流ID',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作时间',
  `who` varchar(255) NOT NULL COMMENT '操作人名称（可以是公司名称，也可以是用户名，可以是物流等）',
  `note` text NOT NULL COMMENT '操作内容',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单日志，用于了解当前的订单处理进度';

--
-- 转存表中的数据 `qinggan_order_log`
--

INSERT INTO `qinggan_order_log` (`id`, `order_id`, `order_express_id`, `addtime`, `who`, `note`, `user_id`, `admin_id`) VALUES
(1, 1, 0, 1525283914, 'admin', '订单创建成功，订单编号：P2018050390U00023001', 23, 0),
(2, 1, 0, 1525283914, 'admin', '订单（P2018050390U00023001）状态变更为：等待付款', 23, 0),
(3, 1, 0, 1525283923, 'admin', '订单（P2018050390U00023001）状态变更为：等待付款', 23, 0),
(4, 1, 0, 1525283923, 'admin', '订单（P2018050390U00023001）状态变更为：等待付款', 23, 0),
(5, 1, 0, 1525284054, 'admin', '订单（P2018050390U00023001）状态变更为：等待付款', 23, 0),
(6, 1, 0, 1525284146, 'admin', '订单（P2018050390U00023001）状态变更为：等待付款', 23, 0),
(7, 1, 0, 1525284380, 'admin', '订单（P2018050390U00023001）状态变更为：等待付款', 23, 0),
(8, 0, 0, 1527680271, 'admin', '管理员编辑订单', 0, 1),
(9, 1, 1, 1527680297, 'admin', '您的订单已经拣货完毕，待出库交付顺丰速运，运单号为：820620736220', 0, 1),
(10, 1, 0, 1527680297, '管理员：admin', '订单（P2018050390U00023001）状态变更为：正在发货', 0, 1),
(11, 1, 1, 1527469644, '顺丰速运', '已签收(同事 ),感谢使用顺丰,期待再次为您服务', 0, 0),
(12, 1, 1, 1527469556, '顺丰速运', '快件交给黄达，正在派送途中（联系电话：18903018371）', 0, 0),
(13, 1, 1, 1527466612, '顺丰速运', '快件交给叶潮惠，正在派送途中（联系电话：18620234953）', 0, 0),
(14, 1, 1, 1527461791, '顺丰速运', '正在派送途中,请您准备签收(派件人:叶潮惠,电话:18620234953)', 0, 0),
(15, 1, 1, 1527430196, '顺丰速运', '因休息日或假期客户不便收件,待工作日派送', 0, 0),
(16, 1, 1, 1527381437, '顺丰速运', '快件派送不成功(因休息日或假期客户不便收件),待工作日再次派送', 0, 0),
(17, 1, 1, 1527380627, '顺丰速运', '快件交给邓广峻，正在派送途中（联系电话：13431951252）', 0, 0),
(18, 1, 1, 1527379687, '顺丰速运', '正在派送途中,请您准备签收(派件人:邓广峻,电话:13431951252)', 0, 0),
(19, 1, 1, 1527375157, '顺丰速运', '快件到达 【广州萝岗科晟广场营业点】', 0, 0),
(20, 1, 1, 1527361458, '顺丰速运', '快件已发车', 0, 0),
(21, 1, 1, 1527352617, '顺丰速运', '快件在【广州新塘集散中心】已装车,准备发往 【广州萝岗科晟广场营业点】', 0, 0),
(22, 1, 1, 1527348877, '顺丰速运', '快件到达 【广州新塘集散中心】', 0, 0),
(23, 1, 1, 1527344561, '顺丰速运', '快件已发车', 0, 0),
(24, 1, 1, 1527336698, '顺丰速运', '快件在【深圳黄田集散中心】已装车,准备发往 【广州新塘集散中心】', 0, 0),
(25, 1, 1, 1527335686, '顺丰速运', '快件到达 【深圳黄田集散中心】', 0, 0),
(26, 1, 1, 1527332876, '顺丰速运', '快件已发车', 0, 0),
(27, 1, 1, 1527331111, '顺丰速运', '快件在【深圳宝安深圳北站营业部】已装车,准备发往 【深圳黄田集散中心】', 0, 0),
(28, 1, 1, 1527306108, '顺丰速运', '顺丰速运 已收取快件', 0, 0),
(29, 1, 1, 1527304671, '顺丰速运', '顺丰速运 已收取快件', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_payment`
--

CREATE TABLE `qinggan_order_payment` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID',
  `payment_id` varchar(255) NOT NULL DEFAULT '0' COMMENT '支付方式ID，数字表示网上业务支付，字母为财富支付',
  `title` varchar(255) NOT NULL COMMENT '支付方式名称',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '支付金额',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币ID，为0使用订单默认货币',
  `currency_rate` decimal(13,8) UNSIGNED NOT NULL DEFAULT '0.00000000' COMMENT '货币汇率',
  `startdate` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始支付操作',
  `dateline` int(11) NOT NULL DEFAULT '0' COMMENT '支付时间',
  `ext` text NOT NULL COMMENT '其他常用扩展信息'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单支付';

--
-- 转存表中的数据 `qinggan_order_payment`
--

INSERT INTO `qinggan_order_payment` (`id`, `order_id`, `payment_id`, `title`, `price`, `currency_id`, `currency_rate`, `startdate`, `dateline`, `ext`) VALUES
(6, 1, '15', '支付宝', '1099.0000', 1, '0.00000000', 1525284380, 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_price`
--

CREATE TABLE `qinggan_order_price` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `code` varchar(255) NOT NULL COMMENT '编码',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '金额，-号表示优惠'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单金额明细清单';

--
-- 转存表中的数据 `qinggan_order_price`
--

INSERT INTO `qinggan_order_price` (`id`, `order_id`, `code`, `price`) VALUES
(5, 1, 'product', '1099.0000'),
(6, 1, 'shipping', '0.0000'),
(7, 1, 'discount', '0.0000');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_order_product`
--

CREATE TABLE `qinggan_order_product` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID号',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订单ID号',
  `tid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `title` varchar(255) NOT NULL COMMENT '产品名称',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '产品单价',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '产品数量',
  `thumb` varchar(255) NOT NULL COMMENT '产品图片地址',
  `ext` text NOT NULL COMMENT '产品扩展属性',
  `weight` varchar(50) NOT NULL COMMENT '重量',
  `volume` varchar(50) NOT NULL COMMENT '体积',
  `unit` varchar(50) NOT NULL COMMENT '单位',
  `note` varchar(255) NOT NULL COMMENT '备注',
  `is_virtual` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0实物1虚拟或服务'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单的产品信息';

--
-- 转存表中的数据 `qinggan_order_product`
--

INSERT INTO `qinggan_order_product` (`id`, `order_id`, `tid`, `title`, `price`, `qty`, `thumb`, `ext`, `weight`, `volume`, `unit`, `note`, `is_virtual`) VALUES
(1, 1, 1761, '华为 P7 移动4G手机', '1099.0000', 1, 'res/201603/23/c941c40778124f2c.jpg', 'a:2:{i:0;a:2:{s:5:\"title\";s:6:\"颜色\";s:7:\"content\";s:6:\"白色\";}i:1;a:2:{s:5:\"title\";s:6:\"版本\";s:7:\"content\";s:7:\"32G ROM\";}}', '0', '0', '台', '111', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_payment`
--

CREATE TABLE `qinggan_payment` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID号',
  `gid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '付款组',
  `code` varchar(100) NOT NULL COMMENT '标识ID',
  `title` varchar(255) NOT NULL COMMENT '主题',
  `currency` varchar(30) NOT NULL COMMENT '可使用的货币ID',
  `logo1` varchar(255) NOT NULL COMMENT 'LOGO小图',
  `logo2` varchar(255) NOT NULL COMMENT 'LOGO中图',
  `logo3` varchar(255) NOT NULL COMMENT 'LOGO大图',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态0未使用1正在使用中',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `note` text NOT NULL COMMENT '付款注意事项说明',
  `param` text NOT NULL COMMENT '参数',
  `wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0PC端1手机端'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='支付方案';

--
-- 转存表中的数据 `qinggan_payment`
--

INSERT INTO `qinggan_payment` (`id`, `gid`, `code`, `title`, `currency`, `logo1`, `logo2`, `logo3`, `status`, `taxis`, `note`, `param`, `wap`) VALUES
(15, 1, 'alipay', '支付宝', 'CNY', '', '', '', 1, 10, '', 'a:4:{s:3:\"pid\";s:0:\"\";s:3:\"key\";s:0:\"\";s:5:\"email\";s:15:\"admin@phpok.com\";s:5:\"ptype\";s:25:\"create_direct_pay_by_user\";}', 0),
(19, 1, 'wxpay', '微信支付', 'CNY', '', '', '', 1, 255, '', 'a:11:{s:5:\"appid\";s:18:\"wxd61676fe9d7468ed\";s:6:\"mch_id\";s:10:\"1283067101\";s:7:\"app_key\";s:32:\"a6d5b10386897dab7f605228a1d04c43\";s:10:\"app_secret\";s:32:\"5239321d1305a4786f23cd106f5ab479\";s:11:\"device_info\";s:3:\"WEB\";s:10:\"trade_type\";s:6:\"native\";s:8:\"pem_cert\";s:25:\"cert/51c3115608a1d3d1.pem\";s:7:\"pem_key\";s:25:\"cert/5c9ac2e8fa574814.pem\";s:6:\"pem_ca\";s:0:\"\";s:10:\"proxy_host\";s:7:\"0.0.0.0\";s:10:\"proxy_port\";s:0:\"\";}', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_payment_group`
--

CREATE TABLE `qinggan_payment_group` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` int(11) NOT NULL DEFAULT '0' COMMENT '站点ID，为0表示全部',
  `title` varchar(255) NOT NULL COMMENT '付款组名称',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不启用1启用',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1默认组0普通组',
  `is_wap` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0是PC端，1是手机端'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='付款组管理';

--
-- 转存表中的数据 `qinggan_payment_group`
--

INSERT INTO `qinggan_payment_group` (`id`, `site_id`, `title`, `status`, `taxis`, `is_default`, `is_wap`) VALUES
(1, 1, '快捷支付', 1, 10, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_payment_log`
--

CREATE TABLE `qinggan_payment_log` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `sn` varchar(255) NOT NULL COMMENT '支付编号',
  `type` varchar(100) NOT NULL COMMENT 'order订单,recharge充值other其他',
  `payment_id` varchar(255) NOT NULL DEFAULT '0' COMMENT '支付方式，为数字时表示payment表中的主要支付方式，为字母数字混合表示财富付款',
  `title` varchar(255) NOT NULL COMMENT '主题',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '记录时间',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `price` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '价格',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币ID',
  `currency_rate` decimal(13,8) UNSIGNED NOT NULL DEFAULT '0.00000000' COMMENT '货币汇率',
  `content` varchar(255) NOT NULL COMMENT '内容',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未支付成功1已支付成功',
  `ext` text NOT NULL COMMENT '扩展'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='支付日志';

--
-- 转存表中的数据 `qinggan_payment_log`
--

INSERT INTO `qinggan_payment_log` (`id`, `sn`, `type`, `payment_id`, `title`, `dateline`, `user_id`, `price`, `currency_id`, `currency_rate`, `content`, `status`, `ext`) VALUES
(6, 'P2018050390U00023001', 'order', '15', '订单：P2018050390U00023001', 1525284380, 23, '1099.00', 1, '0.00000000', '订单：P2018050390U00023001', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_phpok`
--

CREATE TABLE `qinggan_phpok` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID号',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `type_id` varchar(255) NOT NULL COMMENT '调用类型',
  `identifier` varchar(100) NOT NULL COMMENT '标识串，同一个站点中只能唯一',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点ID',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态',
  `cateid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `ext` text NOT NULL COMMENT '扩展属性',
  `is_api` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不支持API调用，1支持',
  `sqlinfo` text NOT NULL COMMENT 'SQL语句'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='数据调用中心';

--
-- 转存表中的数据 `qinggan_phpok`
--

INSERT INTO `qinggan_phpok` (`id`, `title`, `pid`, `type_id`, `identifier`, `site_id`, `status`, `cateid`, `ext`, `is_api`, `sqlinfo`) VALUES
(18, '网站首页图片播放', 41, 'arclist', 'picplayer', 1, 1, 0, 'a:23:{s:5:\"psize\";s:1:\"5\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";s:1:\"1\";s:4:\"attr\";b:0;s:11:\"fields_need\";b:0;s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";i:0;s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";i:0;s:7:\"in_cate\";i:0;s:8:\"title_id\";b:0;}', 0, ''),
(19, '头部导航内容', 42, 'arclist', 'menu', 1, 1, 0, 'a:15:{s:5:\"psize\";s:2:\"80\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:6:\"fields\";s:1:\"*\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:8:\"user_ext\";i:0;s:9:\"usergroup\";i:0;s:6:\"in_sub\";s:1:\"1\";s:8:\"title_id\";s:0:\"\";}', 0, ''),
(20, '公司简介', 87, 'arc', 'aboutus', 1, 1, 0, 'a:13:{s:5:\"psize\";i:0;s:6:\"offset\";i:0;s:7:\"is_list\";i:0;s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:7:\"aboutus\";}', 0, ''),
(21, '产品分类', 45, 'catelist', 'products_cate', 1, 1, 70, 'a:20:{s:5:\"psize\";b:0;s:6:\"offset\";b:0;s:7:\"is_list\";b:0;s:7:\"in_text\";b:0;s:4:\"attr\";b:0;s:11:\"fields_need\";b:0;s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";b:0;s:12:\"catelist_ext\";b:0;s:11:\"project_ext\";b:0;s:11:\"sublist_ext\";b:0;s:10:\"parent_ext\";b:0;s:13:\"fields_format\";b:0;s:8:\"user_ext\";b:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";b:0;s:6:\"in_sub\";b:0;}', 0, ''),
(22, '最新产品', 45, 'arclist', 'new_products', 1, 1, 70, 'a:15:{s:5:\"psize\";s:1:\"8\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:9:\"ext.thumb\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:6:\"fields\";s:21:\"thumb,m_title,content\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:8:\"user_ext\";i:0;s:9:\"usergroup\";i:0;s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(55, '友情链接', 142, 'arclist', 'link', 1, 1, 0, 'a:23:{s:5:\"psize\";s:2:\"30\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";s:1:\"1\";s:4:\"attr\";b:0;s:11:\"fields_need\";b:0;s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";i:0;s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";s:1:\"2\";s:7:\"in_cate\";b:0;s:8:\"title_id\";b:0;}', 0, ''),
(91, '新闻中心', 43, 'arclist', 'news', 1, 1, 7, 'a:15:{s:5:\"psize\";s:1:\"6\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:6:\"fields\";s:2:\"id\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:8:\"user_ext\";i:0;s:9:\"usergroup\";i:0;s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(92, '图集相册', 144, 'arclist', 'photo', 1, 1, 0, 'a:23:{s:5:\"psize\";s:2:\"10\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";i:0;s:4:\"attr\";b:0;s:11:\"fields_need\";s:9:\"ext.thumb\";s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";i:0;s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";s:1:\"1\";s:7:\"in_cate\";i:0;s:8:\"title_id\";b:0;}', 0, ''),
(93, '图片滚动新闻', 43, 'arclist', 'picnews', 1, 1, 7, 'a:23:{s:5:\"psize\";s:2:\"10\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";i:0;s:4:\"attr\";b:0;s:11:\"fields_need\";s:9:\"ext.thumb\";s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";i:0;s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";i:0;s:7:\"in_cate\";i:0;s:8:\"title_id\";b:0;}', 0, ''),
(94, '页脚导航', 147, 'arclist', 'footnav', 1, 1, 0, 'a:23:{s:5:\"psize\";s:2:\"10\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";s:1:\"1\";s:4:\"attr\";b:0;s:11:\"fields_need\";b:0;s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";i:0;s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";i:0;s:7:\"in_cate\";i:0;s:8:\"title_id\";b:0;}', 0, ''),
(95, '客服', 148, 'arclist', 'kefu', 1, 1, 0, 'a:13:{s:5:\"psize\";s:2:\"50\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(96, '售后保障', 150, 'project', 'after-sale-protection', 1, 1, 0, 'a:23:{s:5:\"psize\";b:0;s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";i:0;s:4:\"attr\";b:0;s:11:\"fields_need\";b:0;s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";s:1:\"1\";s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";i:0;s:7:\"in_cate\";i:0;s:8:\"title_id\";b:0;}', 0, ''),
(97, '图集相册', 144, 'arclist', 'tujixiangce', 1, 1, 154, 'a:13:{s:5:\"psize\";s:1:\"6\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:9:\"ext.thumb\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(98, '产品展示', 45, 'catelist', 'catelist', 1, 1, 70, 'a:23:{s:5:\"psize\";b:0;s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:7:\"in_text\";i:0;s:4:\"attr\";b:0;s:11:\"fields_need\";b:0;s:3:\"tag\";b:0;s:8:\"keywords\";b:0;s:7:\"orderby\";b:0;s:4:\"cate\";b:0;s:8:\"cate_ext\";i:0;s:12:\"catelist_ext\";i:0;s:11:\"project_ext\";i:0;s:11:\"sublist_ext\";i:0;s:10:\"parent_ext\";i:0;s:13:\"fields_format\";i:0;s:8:\"user_ext\";i:0;s:4:\"user\";b:0;s:12:\"userlist_ext\";i:0;s:6:\"in_sub\";i:0;s:10:\"in_project\";i:0;s:7:\"in_cate\";i:0;s:8:\"title_id\";b:0;}', 0, ''),
(99, '下载中心', 151, 'arclist', 'xiazaizhongxin', 1, 1, 197, 'a:13:{s:5:\"psize\";s:2:\"10\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:9:\"ext.dfile\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(104, '资讯中心', 43, 'arclist', 'titlelist', 1, 1, 7, 'a:13:{s:5:\"psize\";s:2:\"10\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(105, '资讯中心', 43, 'catelist', 'news_catelist', 1, 1, 7, 'a:13:{s:5:\"psize\";i:0;s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(280, '联系我们', 87, 'arc', 'contactus', 1, 1, 0, 'a:13:{s:5:\"psize\";i:0;s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:4:\"cate\";s:0:\"\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:6:\"in_sub\";i:0;s:8:\"title_id\";s:9:\"contactus\";}', 0, ''),
(282, '热门产品', 45, 'arclist', 'hot_products', 1, 1, 70, 'a:15:{s:5:\"psize\";s:1:\"5\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:9:\"ext.thumb\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:11:\"l.hits DESC\";s:6:\"fields\";s:5:\"thumb\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:8:\"user_ext\";i:0;s:9:\"usergroup\";i:0;s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(286, '银行汇款', 386, 'project', 'drmpjihitd', 1, 1, 0, 'a:15:{s:5:\"psize\";i:0;s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:6:\"fields\";s:1:\"*\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:8:\"user_ext\";i:0;s:9:\"usergroup\";i:0;s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 0, ''),
(287, '企业资料', 393, 'arclist', 'company', 1, 1, 0, 'a:15:{s:5:\"psize\";s:3:\"100\";s:6:\"offset\";i:0;s:7:\"is_list\";s:1:\"1\";s:4:\"attr\";s:0:\"\";s:11:\"fields_need\";s:0:\"\";s:3:\"tag\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:7:\"orderby\";s:0:\"\";s:6:\"fields\";s:1:\"*\";s:13:\"fields_format\";i:0;s:4:\"user\";s:0:\"\";s:8:\"user_ext\";i:0;s:9:\"usergroup\";i:0;s:6:\"in_sub\";i:0;s:8:\"title_id\";s:0:\"\";}', 1, '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_plugins`
--

CREATE TABLE `qinggan_plugins` (
  `id` varchar(100) NOT NULL COMMENT '插件ID，仅限字母，数字及下划线',
  `title` varchar(255) NOT NULL COMMENT '插件名称',
  `author` varchar(255) NOT NULL COMMENT '开发者',
  `version` varchar(50) NOT NULL COMMENT '插件版本号',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0禁用1使用',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '值越小越往前靠',
  `note` varchar(255) NOT NULL COMMENT '摘要说明',
  `param` text NOT NULL COMMENT '参数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='插件管理器';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_popedom`
--

CREATE TABLE `qinggan_popedom` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '权限ID，即自增ID',
  `gid` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属组ID，对应sysmenu表中的ID',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID，仅在list中有效',
  `title` varchar(255) NOT NULL COMMENT '名称，如：添加，修改等',
  `identifier` varchar(255) NOT NULL COMMENT '字符串，如add，modify等',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='权限明细';

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
(33, 21, 0, '查看', 'list', 10),
(34, 21, 0, '添加', 'add', 20),
(35, 21, 0, '编辑', 'modify', 30),
(36, 21, 0, '删除', 'delete', 40),
(37, 18, 0, '查看', 'list', 5),
(38, 23, 0, '查看', 'list', 5),
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
(52, 29, 0, '查看', 'list', 5),
(53, 27, 0, '查看', 'list', 10),
(54, 27, 0, '配置', 'set', 20),
(58, 8, 0, '查看', 'list', 10),
(59, 8, 0, '维护', 'set', 20),
(63, 6, 0, '查看', 'list', 10),
(64, 6, 0, '维护', 'set', 20),
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
(80, 14, 0, '启用/禁用', 'status', 50),
(81, 19, 0, '网站', 'site', 40),
(82, 19, 0, '域名', 'domain', 50),
(83, 16, 0, '启用/禁用', 'status', 50),
(133, 30, 0, '查看', 'list', 10),
(134, 30, 0, '设置', 'set', 20),
(135, 30, 0, '文件管理', 'filelist', 30),
(136, 30, 0, '删除', 'delete', 40),
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
(159, 31, 0, '查看', 'list', 10),
(160, 31, 0, '删除', 'delete', 20),
(161, 31, 0, '设为默认', 'default', 30),
(162, 31, 0, '添加站点', 'add', 40),
(165, 20, 45, '查看', 'list', 10),
(166, 20, 45, '添加', 'add', 30),
(167, 20, 45, '修改', 'modify', 40),
(168, 20, 45, '删除', 'delete', 50),
(169, 20, 45, '启用/禁用', 'status', 60),
(170, 19, 0, '添加站点', 'add', 60),
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
(388, 20, 96, '查看', 'list', 10),
(389, 20, 96, '添加', 'add', 30),
(390, 20, 96, '修改', 'modify', 40),
(391, 20, 96, '删除', 'delete', 50),
(392, 20, 96, '启用/禁用', 'status', 60),
(476, 33, 0, '查看', 'list', 10),
(477, 33, 0, '添加', 'add', 20),
(478, 33, 0, '修改', 'modify', 30),
(479, 33, 0, '删除', 'delete', 40),
(480, 33, 0, '启用/禁用', 'status', 50),
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
(647, 55, 0, '查看', 'list', 10),
(648, 55, 0, '更新HTML', 'create', 20),
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
(689, 20, 144, '编辑', 'set', 20),
(690, 57, 0, '查看', 'list', 10),
(691, 57, 0, '创建备份', 'create', 20),
(692, 57, 0, '删除备份', 'delete', 30),
(693, 57, 0, '恢复备份', 'recover', 40),
(694, 57, 0, '优化', 'optimize', 50),
(695, 57, 0, '修复', 'repair', 60),
(696, 58, 0, '查看', 'list', 10),
(697, 58, 0, '添加', 'add', 20),
(698, 58, 0, '修改', 'modify', 30),
(699, 58, 0, '删除', 'delete', 40),
(700, 18, 0, '状态', 'status', 50),
(701, 59, 0, '查看', 'list', 10),
(702, 59, 0, '设置', 'set', 20),
(703, 59, 0, '删除', 'delete', 30),
(704, 27, 0, '扩展', 'ext', 30),
(748, 62, 0, '查看', 'list', 10),
(749, 62, 0, '添加', 'add', 20),
(750, 62, 0, '编辑', 'modify', 30),
(751, 62, 0, '删除', 'delete', 40),
(752, 63, 0, '查看', 'list', 10),
(753, 63, 0, '添加', 'add', 20),
(754, 63, 0, '修改', 'modify', 30),
(755, 63, 0, '删除', 'delete', 40),
(757, 20, 43, '评论', 'comment', 80),
(758, 66, 0, '查看', 'list', 10),
(759, 66, 0, '添加', 'add', 20),
(760, 66, 0, '修改', 'modify', 30),
(761, 66, 0, '配置', 'setting', 40),
(762, 66, 0, '删除', 'delete', 60),
(763, 66, 0, '状态', 'status', 50),
(764, 67, 0, '查看', 'list', 10),
(765, 67, 0, '添加', 'add', 20),
(766, 67, 0, '修改', 'modify', 30),
(767, 67, 0, '删除', 'delete', 40),
(768, 68, 0, '查看', 'list', 10),
(769, 68, 0, '添加', 'add', 20),
(770, 68, 0, '修改', 'modify', 30),
(771, 68, 0, '删除', 'delete', 40),
(772, 77, 0, '查看', 'list', 10),
(773, 77, 0, '添加', 'add', 20),
(774, 77, 0, '修改', 'modify', 30),
(775, 77, 0, '删除', 'delete', 40),
(776, 77, 0, '状态', 'status', 50),
(777, 34, 0, '配置', 'set', 60),
(778, 31, 0, '订单配置', 'order', 50),
(779, 78, 0, '查看', 'list', 10),
(780, 78, 0, '添加', 'add', 20),
(781, 78, 0, '修改', 'modify', 30),
(782, 78, 0, '删除', 'delete', 40),
(783, 78, 0, '审核', 'status', 50),
(784, 78, 0, '设为默认', 'isdefault', 60),
(793, 80, 0, '查看', 'list', 10),
(794, 80, 0, '添加', 'add', 20),
(795, 80, 0, '修改', 'modify', 30),
(796, 80, 0, '删除', 'delete', 40),
(965, 20, 0, '查看', 'list', 10),
(966, 20, 0, '编辑', 'set', 20),
(967, 20, 0, '添加', 'add', 30),
(968, 20, 0, '修改', 'modify', 40),
(969, 20, 0, '删除', 'delete', 50),
(970, 20, 0, '启用/禁用', 'status', 60),
(971, 20, 0, '扩展', 'ext', 70),
(972, 20, 0, '评论', 'comment', 80),
(981, 20, 43, '编辑', 'set', 20),
(982, 20, 43, '扩展', 'ext', 70),
(983, 20, 311, '查看', 'list', 10),
(984, 20, 311, '添加', 'add', 30),
(985, 20, 311, '修改', 'modify', 40),
(986, 20, 311, '删除', 'delete', 50),
(987, 20, 311, '启用/禁用', 'status', 60),
(988, 20, 312, '查看', 'list', 10),
(989, 20, 312, '添加', 'add', 30),
(990, 20, 312, '修改', 'modify', 40),
(991, 20, 312, '删除', 'delete', 50),
(992, 20, 312, '启用/禁用', 'status', 60),
(993, 20, 313, '查看', 'list', 10),
(994, 20, 313, '添加', 'add', 30),
(995, 20, 313, '修改', 'modify', 40),
(996, 20, 313, '删除', 'delete', 50),
(997, 20, 313, '启用/禁用', 'status', 60),
(998, 20, 313, '评论', 'comment', 80),
(999, 20, 313, '编辑', 'set', 20),
(1000, 20, 313, '扩展', 'ext', 70),
(1001, 20, 314, '查看', 'list', 10),
(1002, 20, 314, '添加', 'add', 30),
(1003, 20, 314, '修改', 'modify', 40),
(1004, 20, 314, '删除', 'delete', 50),
(1005, 20, 314, '启用/禁用', 'status', 60),
(1006, 20, 315, '查看', 'list', 10),
(1007, 20, 315, '添加', 'add', 30),
(1008, 20, 315, '修改', 'modify', 40),
(1009, 20, 315, '删除', 'delete', 50),
(1010, 20, 315, '启用/禁用', 'status', 60),
(1011, 20, 316, '查看', 'list', 10),
(1012, 20, 316, '编辑', 'set', 20),
(1013, 20, 317, '查看', 'list', 10),
(1014, 20, 317, '编辑', 'set', 20),
(1015, 20, 318, '查看', 'list', 10),
(1016, 20, 318, '编辑', 'set', 20),
(1017, 20, 319, '查看', 'list', 10),
(1018, 20, 319, '编辑', 'set', 20),
(1019, 20, 320, '查看', 'list', 10),
(1020, 20, 320, '编辑', 'set', 20),
(1021, 20, 320, '添加', 'add', 30),
(1022, 20, 320, '修改', 'modify', 40),
(1023, 20, 320, '删除', 'delete', 50),
(1024, 20, 320, '启用/禁用', 'status', 60),
(1025, 20, 321, '查看', 'list', 10),
(1026, 20, 321, '添加', 'add', 30),
(1027, 20, 321, '修改', 'modify', 40),
(1028, 20, 321, '删除', 'delete', 50),
(1029, 20, 321, '启用/禁用', 'status', 60),
(1030, 20, 322, '查看', 'list', 10),
(1031, 20, 322, '添加', 'add', 30),
(1032, 20, 322, '修改', 'modify', 40),
(1033, 20, 322, '删除', 'delete', 50),
(1034, 20, 322, '启用/禁用', 'status', 60),
(1035, 20, 322, '编辑', 'set', 20),
(1036, 20, 323, '查看', 'list', 10),
(1037, 20, 323, '编辑', 'set', 20),
(1038, 20, 323, '添加', 'add', 30),
(1039, 20, 323, '修改', 'modify', 40),
(1040, 20, 323, '删除', 'delete', 50),
(1041, 20, 323, '启用/禁用', 'status', 60),
(1042, 20, 324, '查看', 'list', 10),
(1043, 20, 324, '编辑', 'set', 20),
(1044, 20, 324, '添加', 'add', 30),
(1045, 20, 324, '修改', 'modify', 40),
(1046, 20, 324, '删除', 'delete', 50),
(1047, 20, 324, '启用/禁用', 'status', 60),
(1048, 20, 325, '查看', 'list', 10),
(1049, 20, 325, '编辑', 'set', 20),
(1050, 20, 325, '添加', 'add', 30),
(1051, 20, 325, '修改', 'modify', 40),
(1052, 20, 325, '删除', 'delete', 50),
(1053, 20, 325, '启用/禁用', 'status', 60),
(1054, 20, 326, '编辑', 'set', 20),
(1055, 20, 326, '查看', 'list', 10),
(1056, 20, 327, '查看', 'list', 10),
(1057, 20, 327, '编辑', 'set', 20),
(1058, 20, 328, '查看', 'list', 10),
(1059, 20, 328, '编辑', 'set', 20),
(1060, 20, 328, '添加', 'add', 30),
(1061, 20, 328, '修改', 'modify', 40),
(1062, 20, 328, '删除', 'delete', 50),
(1063, 20, 328, '启用/禁用', 'status', 60),
(1064, 20, 329, '查看', 'list', 10),
(1065, 20, 329, '添加', 'add', 30),
(1066, 20, 329, '修改', 'modify', 40),
(1067, 20, 329, '删除', 'delete', 50),
(1068, 20, 329, '启用/禁用', 'status', 60),
(1069, 20, 330, '查看', 'list', 10),
(1070, 20, 330, '添加', 'add', 30),
(1071, 20, 330, '修改', 'modify', 40),
(1072, 20, 330, '删除', 'delete', 50),
(1073, 20, 330, '启用/禁用', 'status', 60),
(1074, 20, 331, '查看', 'list', 10),
(1075, 20, 331, '添加', 'add', 30),
(1076, 20, 331, '修改', 'modify', 40),
(1077, 20, 331, '删除', 'delete', 50),
(1078, 20, 331, '启用/禁用', 'status', 60),
(1079, 20, 331, '评论', 'comment', 80),
(1080, 20, 331, '编辑', 'set', 20),
(1081, 20, 331, '扩展', 'ext', 70),
(1082, 20, 332, '查看', 'list', 10),
(1083, 20, 332, '添加', 'add', 30),
(1084, 20, 332, '修改', 'modify', 40),
(1085, 20, 332, '删除', 'delete', 50),
(1086, 20, 332, '启用/禁用', 'status', 60),
(1087, 20, 333, '查看', 'list', 10),
(1088, 20, 333, '添加', 'add', 30),
(1089, 20, 333, '修改', 'modify', 40),
(1090, 20, 333, '删除', 'delete', 50),
(1091, 20, 333, '启用/禁用', 'status', 60),
(1092, 20, 334, '查看', 'list', 10),
(1093, 20, 334, '编辑', 'set', 20),
(1094, 20, 335, '查看', 'list', 10),
(1095, 20, 335, '编辑', 'set', 20),
(1096, 20, 336, '查看', 'list', 10),
(1097, 20, 336, '编辑', 'set', 20),
(1098, 20, 337, '查看', 'list', 10),
(1099, 20, 337, '编辑', 'set', 20),
(1100, 20, 338, '查看', 'list', 10),
(1101, 20, 338, '编辑', 'set', 20),
(1102, 20, 338, '添加', 'add', 30),
(1103, 20, 338, '修改', 'modify', 40),
(1104, 20, 338, '删除', 'delete', 50),
(1105, 20, 338, '启用/禁用', 'status', 60),
(1106, 20, 339, '查看', 'list', 10),
(1107, 20, 339, '添加', 'add', 30),
(1108, 20, 339, '修改', 'modify', 40),
(1109, 20, 339, '删除', 'delete', 50),
(1110, 20, 339, '启用/禁用', 'status', 60),
(1111, 20, 340, '查看', 'list', 10),
(1112, 20, 340, '添加', 'add', 30),
(1113, 20, 340, '修改', 'modify', 40),
(1114, 20, 340, '删除', 'delete', 50),
(1115, 20, 340, '启用/禁用', 'status', 60),
(1116, 20, 340, '编辑', 'set', 20),
(1117, 20, 341, '查看', 'list', 10),
(1118, 20, 341, '编辑', 'set', 20),
(1119, 20, 341, '添加', 'add', 30),
(1120, 20, 341, '修改', 'modify', 40),
(1121, 20, 341, '删除', 'delete', 50),
(1122, 20, 341, '启用/禁用', 'status', 60),
(1123, 20, 342, '查看', 'list', 10),
(1124, 20, 342, '编辑', 'set', 20),
(1125, 20, 342, '添加', 'add', 30),
(1126, 20, 342, '修改', 'modify', 40),
(1127, 20, 342, '删除', 'delete', 50),
(1128, 20, 342, '启用/禁用', 'status', 60),
(1129, 20, 343, '查看', 'list', 10),
(1130, 20, 343, '编辑', 'set', 20),
(1131, 20, 343, '添加', 'add', 30),
(1132, 20, 343, '修改', 'modify', 40),
(1133, 20, 343, '删除', 'delete', 50),
(1134, 20, 343, '启用/禁用', 'status', 60),
(1135, 20, 344, '编辑', 'set', 20),
(1136, 20, 344, '查看', 'list', 10),
(1137, 20, 345, '查看', 'list', 10),
(1138, 20, 345, '编辑', 'set', 20),
(1139, 20, 346, '查看', 'list', 10),
(1140, 20, 346, '编辑', 'set', 20),
(1141, 20, 346, '添加', 'add', 30),
(1142, 20, 346, '修改', 'modify', 40),
(1143, 20, 346, '删除', 'delete', 50),
(1144, 20, 346, '启用/禁用', 'status', 60),
(1145, 20, 347, '查看', 'list', 10),
(1146, 20, 347, '添加', 'add', 30),
(1147, 20, 347, '修改', 'modify', 40),
(1148, 20, 347, '删除', 'delete', 50),
(1149, 20, 347, '启用/禁用', 'status', 60),
(1150, 20, 348, '查看', 'list', 10),
(1151, 20, 348, '添加', 'add', 30),
(1152, 20, 348, '修改', 'modify', 40),
(1153, 20, 348, '删除', 'delete', 50),
(1154, 20, 348, '启用/禁用', 'status', 60),
(1155, 20, 349, '查看', 'list', 10),
(1156, 20, 349, '添加', 'add', 30),
(1157, 20, 349, '修改', 'modify', 40),
(1158, 20, 349, '删除', 'delete', 50),
(1159, 20, 349, '启用/禁用', 'status', 60),
(1160, 20, 349, '评论', 'comment', 80),
(1161, 20, 349, '编辑', 'set', 20),
(1162, 20, 349, '扩展', 'ext', 70),
(1163, 20, 350, '查看', 'list', 10),
(1164, 20, 350, '添加', 'add', 30),
(1165, 20, 350, '修改', 'modify', 40),
(1166, 20, 350, '删除', 'delete', 50),
(1167, 20, 350, '启用/禁用', 'status', 60),
(1168, 20, 351, '查看', 'list', 10),
(1169, 20, 351, '添加', 'add', 30),
(1170, 20, 351, '修改', 'modify', 40),
(1171, 20, 351, '删除', 'delete', 50),
(1172, 20, 351, '启用/禁用', 'status', 60),
(1173, 20, 352, '查看', 'list', 10),
(1174, 20, 352, '编辑', 'set', 20),
(1175, 20, 353, '查看', 'list', 10),
(1176, 20, 353, '编辑', 'set', 20),
(1177, 20, 354, '查看', 'list', 10),
(1178, 20, 354, '编辑', 'set', 20),
(1179, 20, 355, '查看', 'list', 10),
(1180, 20, 355, '编辑', 'set', 20),
(1181, 20, 356, '查看', 'list', 10),
(1182, 20, 356, '编辑', 'set', 20),
(1183, 20, 356, '添加', 'add', 30),
(1184, 20, 356, '修改', 'modify', 40),
(1185, 20, 356, '删除', 'delete', 50),
(1186, 20, 356, '启用/禁用', 'status', 60),
(1187, 20, 357, '查看', 'list', 10),
(1188, 20, 357, '添加', 'add', 30),
(1189, 20, 357, '修改', 'modify', 40),
(1190, 20, 357, '删除', 'delete', 50),
(1191, 20, 357, '启用/禁用', 'status', 60),
(1192, 20, 358, '查看', 'list', 10),
(1193, 20, 358, '添加', 'add', 30),
(1194, 20, 358, '修改', 'modify', 40),
(1195, 20, 358, '删除', 'delete', 50),
(1196, 20, 358, '启用/禁用', 'status', 60),
(1197, 20, 358, '编辑', 'set', 20),
(1198, 20, 359, '查看', 'list', 10),
(1199, 20, 359, '编辑', 'set', 20),
(1200, 20, 359, '添加', 'add', 30),
(1201, 20, 359, '修改', 'modify', 40),
(1202, 20, 359, '删除', 'delete', 50),
(1203, 20, 359, '启用/禁用', 'status', 60),
(1204, 20, 360, '查看', 'list', 10),
(1205, 20, 360, '编辑', 'set', 20),
(1206, 20, 360, '添加', 'add', 30),
(1207, 20, 360, '修改', 'modify', 40),
(1208, 20, 360, '删除', 'delete', 50),
(1209, 20, 360, '启用/禁用', 'status', 60),
(1210, 20, 361, '查看', 'list', 10),
(1211, 20, 361, '编辑', 'set', 20),
(1212, 20, 361, '添加', 'add', 30),
(1213, 20, 361, '修改', 'modify', 40),
(1214, 20, 361, '删除', 'delete', 50),
(1215, 20, 361, '启用/禁用', 'status', 60),
(1216, 20, 362, '编辑', 'set', 20),
(1217, 20, 362, '查看', 'list', 10),
(1218, 20, 363, '查看', 'list', 10),
(1219, 20, 363, '编辑', 'set', 20),
(1220, 20, 364, '查看', 'list', 10),
(1221, 20, 364, '编辑', 'set', 20),
(1222, 20, 364, '添加', 'add', 30),
(1223, 20, 364, '修改', 'modify', 40),
(1224, 20, 364, '删除', 'delete', 50),
(1225, 20, 364, '启用/禁用', 'status', 60),
(1307, 20, 87, '编辑', 'set', 20),
(1308, 20, 87, '扩展', 'ext', 70),
(1309, 20, 87, '评论', 'comment', 80),
(1310, 20, 45, '编辑', 'set', 20),
(1311, 20, 45, '扩展', 'ext', 70),
(1312, 20, 45, '评论', 'comment', 80),
(1337, 20, 386, '查看', 'list', 10),
(1338, 20, 386, '编辑', 'set', 20),
(1339, 20, 386, '添加', 'add', 30),
(1340, 20, 386, '修改', 'modify', 40),
(1341, 20, 386, '删除', 'delete', 50),
(1342, 20, 386, '启用/禁用', 'status', 60),
(1343, 20, 386, '扩展', 'ext', 70),
(1344, 20, 386, '评论', 'comment', 80),
(1345, 34, 0, '取消', 'cancel', 70),
(1346, 34, 0, '结束', 'stop', 80),
(1347, 34, 0, '完成', 'end', 90),
(1364, 88, 0, '查看', 'list', 10),
(1365, 88, 0, '添加', 'add', 20),
(1366, 88, 0, '修改', 'modify', 30),
(1367, 88, 0, '删除', 'delete', 40),
(1400, 92, 0, '查看', 'list', 10),
(1401, 92, 0, '生成', 'save', 20),
(1402, 92, 0, '删除', 'delete', 30),
(1403, 95, 0, '查看', 'list', 10),
(1404, 95, 0, '安装', 'install', 20),
(1405, 95, 0, '卸载', 'uninstall', 30),
(1406, 95, 0, '配置', 'setting', 15),
(1407, 95, 0, '远程获取', 'remote', 40),
(1408, 97, 0, '查看', 'list', 10),
(1409, 97, 0, '删除', 'delete', 10);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_project`
--

CREATE TABLE `qinggan_project` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID，也是应用ID',
  `parent_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上一级ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL COMMENT '网站ID',
  `module` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '指定模型ID，为0表页面空白',
  `cate` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '绑定根分类ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `nick_title` varchar(255) NOT NULL COMMENT '后台别称',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `tpl_index` varchar(255) NOT NULL COMMENT '封面页',
  `tpl_list` varchar(255) NOT NULL COMMENT '列表页',
  `tpl_content` varchar(255) NOT NULL COMMENT '详细页',
  `is_identifier` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否自定义标识',
  `ico` varchar(255) NOT NULL COMMENT '图标',
  `orderby` text NOT NULL COMMENT '排序',
  `alias_title` varchar(255) NOT NULL COMMENT '主题别名',
  `alias_note` varchar(255) NOT NULL COMMENT '主题备注',
  `psize` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0表示不限制，每页显示数量',
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID号，为0表示管理员维护',
  `identifier` varchar(255) NOT NULL COMMENT '标识',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` varchar(255) NOT NULL COMMENT 'SEO描述',
  `subtopics` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否启用子主题功能',
  `is_search` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否支持搜索',
  `is_tag` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '必填Tag',
  `is_biz` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不启用电商，1启用电商',
  `is_userid` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否绑定会员',
  `is_tpl_content` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否自定义内容模板',
  `is_seo` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否默认使用seo',
  `currency_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '默认货币ID',
  `admin_note` text NOT NULL COMMENT '管理员备注，给编辑人员使用的',
  `hidden` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0显示1隐藏',
  `post_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布模式，0不启用1启用',
  `comment_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '启用评论功能',
  `post_tpl` varchar(255) NOT NULL COMMENT '发布页模板',
  `etpl_admin` varchar(255) NOT NULL COMMENT '通知管理员邮件模板',
  `etpl_user` varchar(255) NOT NULL COMMENT '发布邮件通知会员模板',
  `etpl_comment_admin` varchar(255) NOT NULL COMMENT '评论邮件通知管理员模板',
  `etpl_comment_user` varchar(255) NOT NULL COMMENT '评论邮件通知会员',
  `is_attr` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1启用主题属性0不启用',
  `tag` varchar(255) NOT NULL COMMENT '自身Tag设置',
  `cate_multiple` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0分类单选1分类支持多选',
  `biz_attr` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '产品属性，0不使用1使用',
  `freight` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '运费模板ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='项目管理器';

--
-- 转存表中的数据 `qinggan_project`
--

INSERT INTO `qinggan_project` (`id`, `parent_id`, `site_id`, `module`, `cate`, `title`, `nick_title`, `taxis`, `status`, `tpl_index`, `tpl_list`, `tpl_content`, `is_identifier`, `ico`, `orderby`, `alias_title`, `alias_note`, `psize`, `uid`, `identifier`, `seo_title`, `seo_keywords`, `seo_desc`, `subtopics`, `is_search`, `is_tag`, `is_biz`, `is_userid`, `is_tpl_content`, `is_seo`, `currency_id`, `admin_note`, `hidden`, `post_status`, `comment_status`, `post_tpl`, `etpl_admin`, `etpl_user`, `etpl_comment_admin`, `etpl_comment_user`, `is_attr`, `tag`, `cate_multiple`, `biz_attr`, `freight`) VALUES
(41, 0, 1, 21, 0, '图片播放器', '', 20, 1, '', '', '', 0, 'images/ico/picplayer.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'picture-player', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0),
(42, 0, 1, 23, 0, '导航菜单', '', 30, 1, '', '', '', 0, 'images/ico/menu.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '导航名称', '', 30, 0, 'menu', '', '', '', 1, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0),
(43, 0, 1, 22, 7, '资讯中心', '', 12, 1, '', '', '', 1, 'images/ico/article.png', 'l.id DESC', '新闻主题', '测试备注~~~~', 20, 0, 'news', '', '', '', 0, 1, 1, 0, 1, 0, 1, 0, '', 0, 0, 1, '', '', '', '', '', 1, '新闻,资讯', 0, 0, 0),
(45, 0, 1, 24, 70, '产品展示', '', 50, 1, '', '', '', 0, 'images/ico/product.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '产品名称', '', 12, 0, 'product', '', '', '', 0, 1, 0, 1, 0, 0, 0, 1, '', 0, 0, 1, '', '', '', '', '', 1, '', 1, 1, 1),
(87, 0, 1, 40, 0, '关于我们', '', 15, 1, '', '', 'about_content', 1, 'images/ico/about.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'about', '', '', '', 0, 0, 0, 0, 0, 1, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0),
(96, 0, 1, 46, 0, '在线留言', '', 70, 1, '', '', '', 0, 'images/ico/comment.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '留言主题', '', 30, 0, 'book', '', '', '', 0, 0, 0, 0, 1, 0, 0, 0, '', 0, 1, 1, '', 'project_save', '', '', '', 0, '', 0, 0, 0),
(142, 0, 1, 61, 0, '友情链接', '', 120, 1, '', '', '', 0, 'images/ico/link.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '网站名称', '', 30, 0, 'link', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 1, 0, 'post_link', 'project_save', '', '', '', 0, '', 0, 0, 0),
(144, 0, 1, 68, 154, '图集相册', '', 90, 1, '', '', '', 0, 'images/ico/photo.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'photo', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 1, '', '', '', '', '', 0, '', 0, 0, 0),
(147, 0, 1, 23, 0, '页脚导航', '', 35, 1, '', '', '', 0, 'images/ico/menu.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'yejiaodaohang', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0),
(148, 0, 1, 64, 0, '在线客服', '', 130, 1, '', '', '', 0, 'images/ico/qq.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '客服类型', '', 30, 0, 'kefu', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0),
(150, 0, 1, 0, 0, '售后保障', '', 60, 1, '', '', '', 0, 'images/ico/paper.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '', '', 30, 0, 'shouhoukouzhang', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0),
(151, 0, 1, 65, 197, '下载中心', '', 100, 1, '', 'download_list', 'download_content', 0, 'images/ico/cloud.png', 'l.sort DESC,l.dateline DESC,l.id DESC', '附件名称', '', 30, 0, 'download-center', '', '', '', 0, 1, 0, 0, 0, 0, 0, 0, '', 0, 0, 1, '', '', '', '', '', 0, '', 0, 0, 0),
(152, 0, 1, 66, 201, '论坛BBS', '', 110, 1, 'bbs_index', 'bbs_list', 'bbs_detail', 0, 'images/ico/forum.png', 'ext.toplevel DESC,l.replydate DESC,l.dateline DESC,l.id DESC', '讨论主题', '', 30, 0, 'bbs', '', '', '', 0, 0, 0, 0, 1, 0, 0, 0, '', 0, 1, 1, 'bbs_fabu', '', '', '', '', 0, '', 0, 0, 0),
(386, 0, 1, 75, 0, '银行汇款', '', 140, 1, '', '', '', 0, 'images/ico/bank.png', 'l.sort ASC,l.dateline DESC,l.id DESC', '订单编号', '', 30, 0, 'yinxinghuikuan', '', '', '', 0, 0, 0, 0, 1, 0, 0, 0, '', 0, 1, 0, '', '', '', '', '', 0, '', 0, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_reply`
--

CREATE TABLE `qinggan_reply` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `tid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父回复ID',
  `vouch` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推荐评论',
  `star` tinyint(1) NOT NULL DEFAULT '3' COMMENT '星级',
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `ip` varchar(255) NOT NULL COMMENT '回复人IP',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未审核1审核',
  `session_id` varchar(255) NOT NULL COMMENT '游客标识',
  `content` text NOT NULL COMMENT '评论内容',
  `admin_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `adm_content` longtext NOT NULL COMMENT '管理员回复内容',
  `adm_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复时间',
  `order_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0为评论，非零绑定订单ID',
  `res` varchar(255) NOT NULL COMMENT '附件ID，多个附件用英文逗号隔开',
  `vtype` varchar(255) NOT NULL DEFAULT 'title' COMMENT '主题类型，titlte表示列表中的主题，project表示项目，cate表示分类'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='主题评论表';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res`
--

CREATE TABLE `qinggan_res` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '资源ID',
  `cate_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `folder` varchar(255) NOT NULL COMMENT '存储目录',
  `name` varchar(255) NOT NULL COMMENT '资源文件名',
  `ext` varchar(30) NOT NULL COMMENT '资源后缀，如jpg等',
  `filename` varchar(255) NOT NULL COMMENT '文件名带路径',
  `ico` varchar(255) NOT NULL COMMENT 'ICO图标文件',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `title` varchar(255) NOT NULL COMMENT '内容',
  `attr` text NOT NULL COMMENT '附件属性',
  `note` text NOT NULL COMMENT '备注',
  `session_id` varchar(100) NOT NULL COMMENT '操作者 ID，即会员ID用于检测是否有权限删除 ',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID，当该ID为时检则sesson_id，如不相同则不能删除 ',
  `download` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '下载次数',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资源ID';

--
-- 转存表中的数据 `qinggan_res`
--

INSERT INTO `qinggan_res` (`id`, `cate_id`, `folder`, `name`, `ext`, `filename`, `ico`, `addtime`, `title`, `attr`, `note`, `session_id`, `user_id`, `download`, `admin_id`) VALUES
(624, 1, 'res/201409/01/', '27a6e141c3d265ae.jpg', 'jpg', 'res/201409/01/27a6e141c3d265ae.jpg', 'res/201409/01/_624.jpg', 1409550321, 'logo', 'a:2:{s:5:\"width\";i:219;s:6:\"height\";i:57;}', '', '', 0, 0, 0),
(629, 1, 'res/201409/03/', 'e8b2a2815497215c.png', 'png', 'res/201409/03/e8b2a2815497215c.png', 'res/201409/03/_629.png', 1409747220, 'bbs', 'a:2:{s:5:\"width\";i:280;s:6:\"height\";i:280;}', '', '', 0, 0, 0),
(630, 1, 'res/201409/03/', '5b0086d14de1bbf2.jpg', 'jpg', 'res/201409/03/5b0086d14de1bbf2.jpg', 'res/201409/03/_630.jpg', 1409749616, 'about-img', 'a:2:{s:5:\"width\";i:129;s:6:\"height\";i:133;}', '', '', 0, 0, 0),
(1006, 1, 'res/201603/22/', 'a9c66d15979de244.jpg', 'jpg', 'res/201603/22/a9c66d15979de244.jpg', 'res/201603/22/_1006.jpg', 1458614426, 'banner (1)', 'a:2:{s:5:\"width\";i:980;s:6:\"height\";i:180;}', '', '', 0, 0, 1),
(1007, 1, 'res/201603/22/', '5c94d5a5d4729ee2.jpg', 'jpg', 'res/201603/22/5c94d5a5d4729ee2.jpg', 'res/201603/22/_1007.jpg', 1458614426, 'banner (2)', 'a:2:{s:5:\"width\";i:980;s:6:\"height\";i:180;}', '', '', 0, 0, 1),
(1008, 1, 'res/201603/22/', '572864921e9b72f0.jpg', 'jpg', 'res/201603/22/572864921e9b72f0.jpg', 'res/201603/22/_1008.jpg', 1458614426, 'banner (3)', 'a:2:{s:5:\"width\";i:980;s:6:\"height\";i:180;}', '', '', 0, 0, 1),
(1010, 1, 'res/201603/22/', '671d21cb49401430.jpg', 'jpg', 'res/201603/22/671d21cb49401430.jpg', 'res/201603/22/_1010.jpg', 1458626175, '小米5-2', 'a:2:{s:5:\"width\";i:720;s:6:\"height\";i:420;}', '', '', 0, 0, 1),
(1011, 1, 'res/201603/22/', '6bd0beb0726e32cf.jpg', 'jpg', 'res/201603/22/6bd0beb0726e32cf.jpg', 'res/201603/22/_1011.jpg', 1458626175, '小米5-1', 'a:2:{s:5:\"width\";i:720;s:6:\"height\";i:424;}', '', '', 0, 0, 1),
(1012, 1, 'res/201603/22/', '8ec700add8e54d49.jpg', 'jpg', 'res/201603/22/8ec700add8e54d49.jpg', 'res/201603/22/_1012.jpg', 1458626175, '小米5-3', 'a:2:{s:5:\"width\";i:720;s:6:\"height\";i:335;}', '', '', 0, 0, 1),
(1013, 1, 'res/201603/22/', '6e32b648bf93b490.jpg', 'jpg', 'res/201603/22/6e32b648bf93b490.jpg', 'res/201603/22/_1013.jpg', 1458626325, '小米5-thumb', 'a:2:{s:5:\"width\";i:350;s:6:\"height\";i:350;}', '', '', 0, 0, 1),
(1015, 1, 'res/201603/22/', 'c329c62e183765ad.jpg', 'jpg', 'res/201603/22/c329c62e183765ad.jpg', 'res/201603/22/_1015.jpg', 1458627033, '魅族5', 'a:2:{s:5:\"width\";i:500;s:6:\"height\";i:500;}', '', '', 0, 0, 1),
(1016, 1, 'res/201603/22/', '9fa4450173e59070.jpg', 'jpg', 'res/201603/22/9fa4450173e59070.jpg', 'res/201603/22/_1016.jpg', 1458627040, '魅族5-1', 'a:2:{s:5:\"width\";i:500;s:6:\"height\";i:500;}', '', '', 0, 0, 1),
(1017, 1, 'res/201603/22/', '10f10d8a66069b59.jpg', 'jpg', 'res/201603/22/10f10d8a66069b59.jpg', 'res/201603/22/_1017.jpg', 1458627040, '魅族5-2', 'a:2:{s:5:\"width\";i:500;s:6:\"height\";i:500;}', '', '', 0, 0, 1),
(1018, 1, 'res/201603/23/', 'c941c40778124f2c.jpg', 'jpg', 'res/201603/23/c941c40778124f2c.jpg', 'res/201603/23/_1018.jpg', 1458667317, 'P7-2', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1019, 1, 'res/201603/23/', '945b1df945e039f5.jpg', 'jpg', 'res/201603/23/945b1df945e039f5.jpg', 'res/201603/23/_1019.jpg', 1458667317, 'P7-1', 'a:2:{s:5:\"width\";i:532;s:6:\"height\";i:532;}', '', '', 0, 0, 1),
(1020, 1, 'res/201603/23/', '281512b3b3f9c5f0.jpg', 'jpg', 'res/201603/23/281512b3b3f9c5f0.jpg', 'res/201603/23/_1020.jpg', 1458667317, 'P7-3', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1021, 1, 'res/201603/23/', 'fceefc0374ff1ef2.jpg', 'jpg', 'res/201603/23/fceefc0374ff1ef2.jpg', 'res/201603/23/_1021.jpg', 1458668292, 'xplay5-b-1', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1022, 1, 'res/201603/23/', '63d31419a3bc3163.jpg', 'jpg', 'res/201603/23/63d31419a3bc3163.jpg', 'res/201603/23/_1022.jpg', 1458668292, 'xplay5-b-3', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1023, 1, 'res/201603/23/', 'c94f7ff8e44ec536.jpg', 'jpg', 'res/201603/23/c94f7ff8e44ec536.jpg', 'res/201603/23/_1023.jpg', 1458668292, 'xplay5-b-2', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1024, 1, 'res/201603/23/', '9470f2408e492d99.jpg', 'jpg', 'res/201603/23/9470f2408e492d99.jpg', 'res/201603/23/_1024.jpg', 1458668293, 'xplay5-b-4', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1025, 1, 'res/201603/23/', '5b8b8f3f6cfd32b9.jpg', 'jpg', 'res/201603/23/5b8b8f3f6cfd32b9.jpg', 'res/201603/23/_1025.jpg', 1458669513, 'iphone5se-1', 'a:2:{s:5:\"width\";i:755;s:6:\"height\";i:755;}', '', '', 0, 0, 1),
(1026, 1, 'res/201603/23/', '2e16c80d821beaf0.jpg', 'jpg', 'res/201603/23/2e16c80d821beaf0.jpg', 'res/201603/23/_1026.jpg', 1458669513, 'iphone5se-2', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1027, 1, 'res/201603/23/', '1548d11e0a50ee55.jpg', 'jpg', 'res/201603/23/1548d11e0a50ee55.jpg', 'res/201603/23/_1027.jpg', 1458669513, 'iphone5se-3', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1028, 1, 'res/201603/23/', 'e2bb1c4c3a4bc11b.jpg', 'jpg', 'res/201603/23/e2bb1c4c3a4bc11b.jpg', 'res/201603/23/_1028.jpg', 1458669514, 'iphone5se-4', 'a:2:{s:5:\"width\";i:600;s:6:\"height\";i:600;}', '', '', 0, 0, 1),
(1029, 11, 'res/soft/2016/', '37e7a0aff81446b8.zip', 'zip', 'res/soft/2016/37e7a0aff81446b8.zip', 'images/filetype-large/zip.jpg', 1458715867, 'copy', '', '', '', 0, 7, 1),
(1250, 1, 'res/201709/16/', '655b45e9c29c8e30.jpg', 'jpg', 'res/201709/16/655b45e9c29c8e30.jpg', 'res/201709/16/_1250.jpg', 1505531806, '400', 'a:2:{s:5:\"width\";i:400;s:6:\"height\";i:400;}', '', 'cl2gqvhrno0fojg2g2k45bvnj2', 0, 0, 0),
(1317, 1, 'res/201805/03/', '8e6f069014922fe6.jpg', 'jpg', 'res/201805/03/8e6f069014922fe6.jpg', 'res/201805/03/_1317.jpg', 1525347821, 'paypal', 'a:2:{s:5:\"width\";i:70;s:6:\"height\";i:44;}', '', '', 0, 0, 1),
(1318, 1, 'res/201805/10/', 'ab0fb86d606f68fc.jpg', 'jpg', 'res/201805/10/ab0fb86d606f68fc.jpg', 'res/201805/10/_1318.jpg', 1525919519, '400', 'a:2:{s:5:\"width\";i:400;s:6:\"height\";i:400;}', '', '', 0, 0, 1),
(1321, 1, 'res/201807/10/', '99680776cece137e.jpg', 'jpg', 'res/201807/10/99680776cece137e.jpg', 'res/201807/10/_1321.jpg', 1531206898, '备案信息登记表', 'a:2:{s:5:\"width\";s:4:\"2048\";s:6:\"height\";i:2730;}', '', '', 0, 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res_cate`
--

CREATE TABLE `qinggan_res_cate` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '资源分类ID',
  `title` varchar(255) NOT NULL COMMENT '分类名称',
  `root` varchar(255) NOT NULL DEFAULT '/' COMMENT '存储目录',
  `folder` varchar(255) NOT NULL DEFAULT 'Ym/d/' COMMENT '存储目录格式',
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1默认0非默认',
  `filetypes` varchar(255) NOT NULL COMMENT '附件类型',
  `typeinfo` varchar(200) NOT NULL COMMENT '类型说明',
  `gdtypes` varchar(255) NOT NULL COMMENT '支持的GD方案，多个GD方案用英文ID分开',
  `gdall` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1支持全部GD方案0仅支持指定的GD方案',
  `ico` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台缩略图',
  `filemax` int(10) UNSIGNED NOT NULL DEFAULT '2' COMMENT '上传文件大小限制'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资源分类存储';

--
-- 转存表中的数据 `qinggan_res_cate`
--

INSERT INTO `qinggan_res_cate` (`id`, `title`, `root`, `folder`, `is_default`, `filetypes`, `typeinfo`, `gdtypes`, `gdall`, `ico`, `filemax`) VALUES
(1, '图片', 'res/', 'Ym/d/', 1, 'png,jpg,gif', '图片', '', 1, 1, 2000),
(11, '压缩软件', 'res/soft/', 'Y/', 0, 'rar,zip', '压缩包', '', 0, 0, 2000),
(20, 'Excel', 'res/excel', '', 0, 'xls,xlsx', 'Excel文件', '', 0, 0, 2048),
(24, '视频', 'res/', 'Ym/d/', 0, 'mp4,flv,mp3,mpeg', '影音文字', '', 0, 0, 2048);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_res_ext`
--

CREATE TABLE `qinggan_res_ext` (
  `res_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '附件ID',
  `gd_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'GD库方案ID',
  `filename` varchar(255) NOT NULL COMMENT '文件地址（含路径）',
  `filetime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='生成扩展图片';

--
-- 转存表中的数据 `qinggan_res_ext`
--

INSERT INTO `qinggan_res_ext` (`res_id`, `gd_id`, `filename`, `filetime`) VALUES
(624, 2, 'res/201409/01/thumb_624.jpg', 1528009233),
(624, 12, 'res/201409/01/auto_624.jpg', 1528009233),
(629, 2, 'res/201409/03/thumb_629.png', 1528009233),
(629, 12, 'res/201409/03/auto_629.png', 1528009233),
(630, 2, 'res/201409/03/thumb_630.jpg', 1528009233),
(630, 12, 'res/201409/03/auto_630.jpg', 1528009233),
(1006, 2, 'res/201603/22/thumb_1006.jpg', 1528009233),
(1006, 12, 'res/201603/22/auto_1006.jpg', 1528009233),
(1007, 2, 'res/201603/22/thumb_1007.jpg', 1528009232),
(1007, 12, 'res/201603/22/auto_1007.jpg', 1528009232),
(1008, 2, 'res/201603/22/thumb_1008.jpg', 1528009232),
(1008, 12, 'res/201603/22/auto_1008.jpg', 1528009232),
(1010, 2, 'res/201603/22/thumb_1010.jpg', 1528009232),
(1010, 12, 'res/201603/22/auto_1010.jpg', 1528009232),
(1011, 2, 'res/201603/22/thumb_1011.jpg', 1528009232),
(1011, 12, 'res/201603/22/auto_1011.jpg', 1528009232),
(1012, 2, 'res/201603/22/thumb_1012.jpg', 1528009232),
(1012, 12, 'res/201603/22/auto_1012.jpg', 1528009232),
(1013, 2, 'res/201603/22/thumb_1013.jpg', 1528009232),
(1013, 12, 'res/201603/22/auto_1013.jpg', 1528009232),
(1015, 2, 'res/201603/22/thumb_1015.jpg', 1528009232),
(1015, 12, 'res/201603/22/auto_1015.jpg', 1528009232),
(1016, 2, 'res/201603/22/thumb_1016.jpg', 1528009232),
(1016, 12, 'res/201603/22/auto_1016.jpg', 1528009232),
(1017, 2, 'res/201603/22/thumb_1017.jpg', 1528009230),
(1017, 12, 'res/201603/22/auto_1017.jpg', 1528009230),
(1018, 2, 'res/201603/23/thumb_1018.jpg', 1528009230),
(1018, 12, 'res/201603/23/auto_1018.jpg', 1528009230),
(1019, 2, 'res/201603/23/thumb_1019.jpg', 1528009230),
(1019, 12, 'res/201603/23/auto_1019.jpg', 1528009230),
(1020, 2, 'res/201603/23/thumb_1020.jpg', 1528009230),
(1020, 12, 'res/201603/23/auto_1020.jpg', 1528009230),
(1021, 2, 'res/201603/23/thumb_1021.jpg', 1528009230),
(1021, 12, 'res/201603/23/auto_1021.jpg', 1528009230),
(1022, 2, 'res/201603/23/thumb_1022.jpg', 1528009230),
(1022, 12, 'res/201603/23/auto_1022.jpg', 1528009230),
(1023, 2, 'res/201603/23/thumb_1023.jpg', 1528009230),
(1023, 12, 'res/201603/23/auto_1023.jpg', 1528009230),
(1024, 2, 'res/201603/23/thumb_1024.jpg', 1528009230),
(1024, 12, 'res/201603/23/auto_1024.jpg', 1528009230),
(1025, 2, 'res/201603/23/thumb_1025.jpg', 1528009229),
(1025, 12, 'res/201603/23/auto_1025.jpg', 1528009229),
(1026, 2, 'res/201603/23/thumb_1026.jpg', 1528009229),
(1026, 12, 'res/201603/23/auto_1026.jpg', 1528009229),
(1027, 2, 'res/201603/23/thumb_1027.jpg', 1528009229),
(1027, 12, 'res/201603/23/auto_1027.jpg', 1528009229),
(1028, 2, 'res/201603/23/thumb_1028.jpg', 1528009229),
(1028, 12, 'res/201603/23/auto_1028.jpg', 1528009229),
(1250, 2, 'res/201709/16/thumb_1250.jpg', 1528009229),
(1250, 12, 'res/201709/16/auto_1250.jpg', 1528009229),
(1317, 2, 'res/201805/03/thumb_1317.jpg', 1528009229),
(1317, 12, 'res/201805/03/auto_1317.jpg', 1528009229),
(1318, 2, 'res/201805/10/thumb_1318.jpg', 1528009229),
(1318, 12, 'res/201805/10/auto_1318.jpg', 1528009229),
(1321, 12, 'res/201807/10/auto_1321.jpg', 1531206898),
(1321, 2, 'res/201807/10/thumb_1321.jpg', 1531206898);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_session`
--

CREATE TABLE `qinggan_session` (
  `id` varchar(32) NOT NULL COMMENT 'session_id',
  `data` varchar(20485) NOT NULL COMMENT 'session 内容，最多只能放20K',
  `lasttime` int(10) UNSIGNED NOT NULL COMMENT '时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='SESSION操作';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_site`
--

CREATE TABLE `qinggan_site` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '应用ID',
  `domain_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '默认域名ID',
  `title` varchar(255) NOT NULL COMMENT '网站名称',
  `dir` varchar(255) NOT NULL DEFAULT '/' COMMENT '安装目录，以/结尾',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态',
  `content` text NOT NULL COMMENT '网站关闭原因',
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1默认站点',
  `tpl_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模板ID',
  `url_type` enum('default','rewrite','html') NOT NULL DEFAULT 'default' COMMENT '默认，即带?等能数，rewrite是伪静态页，html为生成的静态页',
  `logo` varchar(255) NOT NULL COMMENT '网站 LOGO ',
  `meta` text NOT NULL COMMENT '扩展配置',
  `currency_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '默认货币ID',
  `register_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0关闭注册1开启注册',
  `register_close` varchar(255) NOT NULL COMMENT '关闭注册说明',
  `login_status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0关闭登录1开启',
  `login_close` varchar(255) NOT NULL COMMENT '关闭登录说明',
  `adm_logo29` varchar(255) NOT NULL COMMENT '在后台左侧LOGO地址',
  `adm_logo180` varchar(255) NOT NULL COMMENT '登录LOGO地址',
  `lang` varchar(255) NOT NULL COMMENT '语言包',
  `api` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不走接口',
  `api_code` varchar(255) NOT NULL COMMENT 'API验证串',
  `seo_title` varchar(255) NOT NULL COMMENT 'SEO主题',
  `seo_keywords` varchar(255) NOT NULL COMMENT 'SEO关键字',
  `seo_desc` text NOT NULL COMMENT 'SEO摘要',
  `biz_sn` varchar(255) NOT NULL COMMENT '订单号生成规则',
  `biz_payment` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '默认支付方式',
  `upload_guest` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '游客上传权限',
  `upload_user` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员上传权限'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='网站管理';

--
-- 转存表中的数据 `qinggan_site`
--

INSERT INTO `qinggan_site` (`id`, `domain_id`, `title`, `dir`, `status`, `content`, `is_default`, `tpl_id`, `url_type`, `logo`, `meta`, `currency_id`, `register_status`, `register_close`, `login_status`, `login_close`, `adm_logo29`, `adm_logo180`, `lang`, `api`, `api_code`, `seo_title`, `seo_keywords`, `seo_desc`, `biz_sn`, `biz_payment`, `upload_guest`, `upload_user`) VALUES
(1, 1, '锟铻科技', '/phpok/', 1, '网站关闭测试', 1, 1, 'default', 'res/201409/01/27a6e141c3d265ae.jpg', '', 1, 1, '本系统暂停新会员注册，如需会员服务请联系QQ：40782502', 1, '本系统暂停会员登录，给您带来不便还请见谅！', '', '', 'cn', 0, 'wMbo#qAhsafg@c15', '网站建设|企业网站建设|PHPOK网站建设|PHPOK企业网站建设', '网站建设,企业网站建设,PHPOK网站建设,PHPOK企业网站建设', '高效的企业网站建设系统，可实现高定制化的企业网站电商系统，实现企业网站到电子商务企业网站。定制功能更高，操作更简单！', 'prefix[P]-year-month-date-rand-user-number', 0, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_site_domain`
--

CREATE TABLE `qinggan_site_domain` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID',
  `site_id` mediumint(8) UNSIGNED NOT NULL COMMENT '网站ID',
  `domain` varchar(255) NOT NULL COMMENT '域名信息',
  `is_mobile` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1此域名强制为手机版'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='网站指定的域名';

--
-- 转存表中的数据 `qinggan_site_domain`
--

INSERT INTO `qinggan_site_domain` (`id`, `site_id`, `domain`, `is_mobile`) VALUES
(1, 1, 'localhost', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_sysmenu`
--

CREATE TABLE `qinggan_sysmenu` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID号',
  `parent_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID，0为根菜单',
  `title` varchar(100) NOT NULL COMMENT '分类名称',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态0禁用1正常',
  `appfile` varchar(100) NOT NULL COMMENT '应用文件名，放在phpok/admin/目录下，记录不带.php',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，值越小越往前靠，可选0-255',
  `func` varchar(100) NOT NULL COMMENT '应用函数，为空使用index',
  `identifier` varchar(100) NOT NULL COMMENT '标识串，用于区分同一应用文件的不同内容',
  `ext` varchar(255) NOT NULL COMMENT '表单扩展',
  `if_system` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0常规项目，1系统项目',
  `site_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0表示全局网站',
  `icon` varchar(255) NOT NULL COMMENT '图标路径'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='PHPOK后台系统菜单';

--
-- 转存表中的数据 `qinggan_sysmenu`
--

INSERT INTO `qinggan_sysmenu` (`id`, `parent_id`, `title`, `status`, `appfile`, `taxis`, `func`, `identifier`, `ext`, `if_system`, `site_id`, `icon`) VALUES
(1, 0, '设置', 1, 'setting', 50, '', '', '', 1, 0, ''),
(2, 0, '商务', 1, '', 20, '', '', '', 1, 0, ''),
(3, 0, '会员', 1, 'user', 30, '', '', '', 0, 0, ''),
(4, 0, '工具', 1, 'tool', 40, '', '', '', 0, 0, ''),
(5, 0, '内容', 1, 'index', 10, '', '', '', 0, 0, ''),
(6, 1, '表单选项', 1, 'opt', 30, '', '', '', 0, 0, ''),
(7, 4, '字段维护', 1, 'fields', 20, '', '', '', 0, 0, ''),
(8, 1, '模块管理', 1, 'module', 20, '', '', '', 0, 0, 'newtab'),
(9, 1, '核心配置', 1, 'system', 50, '', '', '', 1, 0, ''),
(13, 3, '会员列表', 1, 'user', 10, '', '', '', 0, 0, 'newtab'),
(14, 3, '会员组', 1, 'usergroup', 20, '', '', '', 0, 0, ''),
(16, 4, '插件中心', 1, 'plugin', 30, '', '', '', 0, 0, 'newtab'),
(18, 5, '分类管理', 1, 'cate', 30, '', '', '', 0, 0, 'newtab'),
(19, 5, '全局内容', 1, 'all', 10, '', '', '', 0, 0, ''),
(20, 5, '内容管理', 1, 'list', 20, '', '', '', 0, 0, 'newtab'),
(22, 5, '资源管理', 1, 'res', 60, '', '', '', 0, 0, 'newtab'),
(23, 5, '数据调用', 1, 'call', 40, '', '', '', 0, 0, 'newtab'),
(25, 3, '会员字段', 1, 'user', 30, 'fields', '', '', 0, 0, ''),
(27, 1, '项目管理', 1, 'project', 10, '', '', '', 0, 0, 'newtab'),
(28, 4, '通知模板', 1, 'email', 40, '', '', '', 0, 0, ''),
(29, 1, '管理员维护', 1, 'admin', 80, '', '', '', 0, 0, ''),
(30, 1, '风格管理', 1, 'tpl', 60, '', '', '', 0, 0, ''),
(31, 1, '站点管理', 1, 'site', 90, '', '', '', 0, 0, ''),
(32, 5, '评论管理', 1, 'reply', 50, '', '', '', 0, 0, 'newtab'),
(33, 2, '货币及汇率', 1, 'currency', 30, '', '', '', 0, 0, ''),
(34, 2, '订单管理', 1, 'order', 10, '', '', '', 0, 0, 'newtab'),
(45, 4, '程序升级', 1, 'update', 10, '', '', '', 0, 0, 'newtab'),
(52, 2, '付款方案', 1, 'payment', 20, '', '', '', 0, 0, ''),
(57, 1, '数据库管理', 1, 'sql', 100, '', '', '', 0, 0, ''),
(58, 5, '标签管理', 1, 'tag', 70, '', '', '', 0, 0, 'newtab'),
(59, 1, '伪静态页规则', 1, 'rewrite', 70, '', '', '', 0, 0, ''),
(62, 4, '附件分类管理', 1, 'rescate', 120, '', '', '', 0, 0, ''),
(63, 4, '图片规格方案', 1, 'gd', 130, '', '', '', 0, 0, ''),
(66, 3, '财富方案', 1, 'wealth', 40, '', '', '', 0, 0, ''),
(67, 2, '商品属性', 1, 'options', 40, '', '', '', 0, 0, ''),
(68, 2, '运费模板', 1, 'freight', 50, '', '', '', 0, 0, ''),
(77, 2, '物流快递', 1, 'express', 50, '', '', '', 0, 0, ''),
(78, 4, '网关路由', 1, 'gateway', 110, '', '', '', 0, 0, ''),
(80, 4, '计划任务', 1, 'task', 140, '', '', '', 0, 0, ''),
(87, 1, '日志', 1, 'log', 110, '', '', '', 0, 1, ''),
(88, 3, '会员地址库', 1, 'address', 50, '', '', '', 0, 1, ''),
(92, 5, '报表统计', 1, 'report', 80, '', '', '', 0, 1, ''),
(93, 1, '风格管理器', 1, 'template', 30, '', '', '', 0, 1, 'forward'),
(97, 5, '收藏夹管理', 1, 'fav', 255, '', '', '', 0, 0, 'newtab'),
(95, 1, '应用管理', 1, 'appsys', 115, '', '', '', 0, 1, 'wrench');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tag`
--

CREATE TABLE `qinggan_tag` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT 'id',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `url` varchar(255) NOT NULL COMMENT '关键字网址',
  `target` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0原窗口打开，1新窗口打开',
  `hits` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点击次数',
  `alt` varchar(255) NOT NULL COMMENT '链接里的提示',
  `is_global` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否全局状态1是0否',
  `replace_count` tinyint(4) NOT NULL DEFAULT '3' COMMENT '替换次数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='关键字管理器';

--
-- 转存表中的数据 `qinggan_tag`
--

INSERT INTO `qinggan_tag` (`id`, `site_id`, `title`, `url`, `target`, `hits`, `alt`, `is_global`, `replace_count`) VALUES
(1, 1, '新闻', '', 0, 0, '', 0, 3),
(2, 1, '资讯', '', 0, 0, '', 0, 3);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tag_stat`
--

CREATE TABLE `qinggan_tag_stat` (
  `title_id` varchar(200) NOT NULL COMMENT '主题ID，以p开头的表示项目ID，以c开头的表示分类ID',
  `tag_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'TAG标签ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tag主题统计';

--
-- 转存表中的数据 `qinggan_tag_stat`
--

INSERT INTO `qinggan_tag_stat` (`title_id`, `tag_id`) VALUES
('p43', 1),
('p43', 2);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_task`
--

CREATE TABLE `qinggan_task` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `year` varchar(9) NOT NULL COMMENT '年份',
  `month` varchar(5) NOT NULL COMMENT '月',
  `day` varchar(5) NOT NULL COMMENT '日',
  `hour` varchar(5) NOT NULL COMMENT '时',
  `minute` varchar(5) NOT NULL COMMENT '分',
  `second` varchar(5) NOT NULL COMMENT '秒',
  `exec_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '开始执行时间',
  `stop_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间',
  `action` varchar(100) NOT NULL COMMENT '执行动作脚本',
  `param` varchar(255) NOT NULL COMMENT '参数',
  `only_once` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1表示仅执行一次',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未锁定1已锁定'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='计划任务';

--
-- 转存表中的数据 `qinggan_task`
--

INSERT INTO `qinggan_task` (`id`, `year`, `month`, `day`, `hour`, `minute`, `second`, `exec_time`, `stop_time`, `action`, `param`, `only_once`, `is_lock`) VALUES
(1, '2018', '05', '03', '01', '58', '39', 0, 0, 'order', 'id=1&status=create', 1, 0),
(2, '2018', '05', '03', '01', '58', '39', 0, 0, 'order', 'id=1&status=unpaid', 1, 0),
(3, '2018', '05', '03', '01', '58', '48', 0, 0, 'order', 'id=1&status=unpaid', 1, 0),
(4, '2018', '05', '03', '01', '58', '48', 0, 0, 'order', 'id=1&status=unpaid', 1, 0),
(5, '2018', '05', '03', '02', '00', '59', 0, 0, 'order', 'id=1&status=unpaid', 1, 0),
(6, '2018', '05', '03', '02', '02', '31', 0, 0, 'order', 'id=1&status=unpaid', 1, 0),
(7, '2018', '05', '03', '02', '06', '25', 0, 0, 'order', 'id=1&status=unpaid', 1, 0),
(8, '2018', '05', '30', '19', '38', '22', 0, 0, 'order', 'id=1&status=shipping', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tpl`
--

CREATE TABLE `qinggan_tpl` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID',
  `title` varchar(100) NOT NULL COMMENT '模板名称',
  `author` varchar(100) NOT NULL COMMENT '开发者名称',
  `folder` varchar(100) NOT NULL DEFAULT 'www' COMMENT '模板目录',
  `refresh_auto` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1自动判断更新刷新0不刷新',
  `refresh` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1强制刷新0普通刷新',
  `ext` varchar(20) NOT NULL DEFAULT 'html' COMMENT '后缀',
  `folder_change` varchar(255) NOT NULL COMMENT '更改目录',
  `phpfolder` varchar(200) NOT NULL COMMENT 'PHP执行文件目录'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模板管理';

--
-- 转存表中的数据 `qinggan_tpl`
--

INSERT INTO `qinggan_tpl` (`id`, `title`, `author`, `folder`, `refresh_auto`, `refresh`, `ext`, `folder_change`, `phpfolder`) VALUES
(1, '默认风格', 'phpok.com', 'www', 1, 0, 'html', 'css,images,js', 'phpinc');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user`
--

CREATE TABLE `qinggan_user` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '自增ID，即会员ID',
  `group_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主要会员组',
  `user` varchar(100) NOT NULL COMMENT '会员账号',
  `pass` varchar(100) NOT NULL COMMENT '会员密码',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态ID，0未审核1正常2锁定',
  `regtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注册时间',
  `email` varchar(200) NOT NULL COMMENT '邮箱，可用于取回密码',
  `mobile` varchar(50) NOT NULL COMMENT '手机或电话',
  `code` varchar(255) NOT NULL COMMENT '验证串，可用于取回密码',
  `avatar` varchar(255) NOT NULL COMMENT '会员头像'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员管理';

--
-- 转存表中的数据 `qinggan_user`
--

INSERT INTO `qinggan_user` (`id`, `group_id`, `user`, `pass`, `status`, `regtime`, `email`, `mobile`, `code`, `avatar`) VALUES
(23, 2, 'admin', 'f58ccc30c69981351d8c4e0904411604:2f', 1, 1438668082, '40782502@qq.com', '15818533971', '', 'res/201805/10/ab0fb86d606f68fc.jpg'),
(24, 2, 'seika', '6ee392c1b77d9b200e7bbd2ae9a5b22e:59', 1, 1439398782, 'admin@phpok.com', '15818533972', '2964-1525151473', ''),
(25, 2, 'demo', 'edd2f2aac34c1bb0c746876bfae9fbf8:ac', 1, 1469963807, 'demo@demo.com', '', '', ''),
(26, 2, 'd2', 'defe12aad396f90e6b179c239de260d4:ab', 1, 1469963896, 'ddd@ddd.com', '', '', ''),
(27, 2, 'suxiangkun', 'e8eb7ea7212ace80bbc98aa93a17904e:35', 1, 1470033757, 'suxiangkun@126.com', '', '', ''),
(28, 2, '18928475010', '44e8f70e59e6b6a2472c241d351428a7:ed', 1, 1481105125, '', '18928475010', '8536-1481439164', ''),
(31, 2, 'demo123', 'a6c742c087858b436686c019306f0bb9:c3', 1, 1504764209, 'de@dd.com', '147258369', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_address`
--

CREATE TABLE `qinggan_user_address` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `country` varchar(255) NOT NULL DEFAULT '中国' COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省信息',
  `city` varchar(255) NOT NULL COMMENT '市',
  `county` varchar(255) NOT NULL COMMENT '县',
  `address` varchar(255) NOT NULL COMMENT '地址信息（不含国家，省市县镇区信息）',
  `mobile` varchar(100) NOT NULL COMMENT '手机号码',
  `tel` varchar(100) NOT NULL COMMENT '电话号码',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `fullname` varchar(100) NOT NULL COMMENT '联系人姓名',
  `zipcode` varchar(50) NOT NULL COMMENT '邮编',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1表示默认地址，0为常规'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员地址库';

--
-- 转存表中的数据 `qinggan_user_address`
--

INSERT INTO `qinggan_user_address` (`id`, `user_id`, `country`, `province`, `city`, `county`, `address`, `mobile`, `tel`, `email`, `fullname`, `zipcode`, `is_default`) VALUES
(1, 23, '中国', '天津市', '天津市', '和平区', 'fasdfasdfasdf', '15818533971', '', '', '苏相锟', '', 0),
(2, 23, '中国', '山西省', '长治市', '襄垣县', 'fasfasdf', '15818533970', '', '', 'dfasdfasdf', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_ext`
--

CREATE TABLE `qinggan_user_ext` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `fullname` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `gender` varchar(255) NOT NULL DEFAULT '' COMMENT '性别',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '联系地址'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员扩展字段';

--
-- 转存表中的数据 `qinggan_user_ext`
--

INSERT INTO `qinggan_user_ext` (`id`, `fullname`, `gender`, `address`) VALUES
(0, 'demo', '', ''),
(23, '苏相锟', '0', ''),
(24, '', '', ''),
(25, '', '', ''),
(26, '', '', ''),
(27, '', '', ''),
(28, '', '', ''),
(31, 'Su', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_group`
--

CREATE TABLE `qinggan_user_group` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '会员组ID',
  `title` varchar(255) NOT NULL COMMENT '会员组名称',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '0不使用1使用',
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1为会员注册默认组',
  `is_guest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '游客组',
  `is_open` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1开放供用户选择，0不开放',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序',
  `register_status` varchar(100) NOT NULL COMMENT '1通过0审核email邮件code邀请码mobile手机',
  `tbl_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关联验证串项目',
  `fields` text NOT NULL COMMENT '会员字段，多个字段用英文逗号隔开',
  `popedom` longtext NOT NULL COMMENT '权限，包括读写及评论审核'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员组信息管理';

--
-- 转存表中的数据 `qinggan_user_group`
--

INSERT INTO `qinggan_user_group` (`id`, `title`, `status`, `is_default`, `is_guest`, `is_open`, `taxis`, `register_status`, `tbl_id`, `fields`, `popedom`) VALUES
(2, '普通会员', 1, 1, 0, 0, 10, '', 0, '', 'a:2:{i:1;s:333:\"read:43,read:87,read:41,read:42,read:147,read:45,read:150,read:96,post:96,read:144,read:151,read:152,post:152,post1:152,reply:152,reply1:152,read:142,post:142,read:148,read:386,post:386,read:389,read:390,read:391,read:392,read:393,read:394,read:395,read:396,read:397,read:398,read:399,read:387,post:387,reply:387,post1:387,reply1:387\";i:33;s:57:\"read:384,post:384,reply:384,post1:384,reply1:384,read:385\";}'),
(3, '游客组', 1, 0, 1, 0, 200, '', 0, '', 'a:2:{i:1;s:622:\"read:149,read:87,read:90,read:146,read:92,read:93,read:43,read:41,read:42,read:147,read:45,read:150,read:96,post:96,read:144,read:151,read:152,read:142,post:142,read:148,read:153,read:156,read:157,read:158,post:158,post1:158,read:159,read:160,post:160,reply:160,post1:160,reply1:160,read:161,post:161,reply:161,post1:161,reply1:161,read:162,post:162,reply:162,post1:162,reply1:162,read:163,read:164,post:164,reply:164,post1:164,reply1:164,read:165,read:166,read:386,post:386,read:389,read:390,read:391,read:392,read:393,read:394,read:395,read:396,read:397,read:398,read:399,read:387,post:387,reply:387,post1:387,reply1:387\";i:33;s:57:\"read:384,post:384,reply:384,post1:384,reply1:384,read:385\";}'),
(7, 'VIP会员', 1, 0, 0, 0, 255, '0', 0, 'fullname,gender', 'a:1:{i:1;s:291:\"read:43,read:87,read:41,read:42,read:147,read:45,read:150,read:96,post:96,post1:96,reply:96,reply1:96,read:144,read:151,read:152,post:152,post1:152,reply:152,reply1:152,read:142,post:142,post1:142,read:148,read:386,post:386,post1:386,read:399,read:387,post:387,reply:387,post1:387,reply1:387\";}');

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_user_relation`
--

CREATE TABLE `qinggan_user_relation` (
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `introducer` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '介绍人ID',
  `dateline` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '介绍时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员介绍关系图';

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth`
--

CREATE TABLE `qinggan_wealth` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '财富ID',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(100) NOT NULL COMMENT '财产名称',
  `identifier` varchar(100) NOT NULL COMMENT '标识，仅限英文字符',
  `unit` varchar(100) NOT NULL COMMENT '单位名称',
  `dnum` tinyint(1) NOT NULL DEFAULT '0' COMMENT '保留几位小数，为0表示只取整数',
  `ifpay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持充值',
  `pay_ratio` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '兑换比例，即1元可以兑换多少，为0不支持充值，为1表示1：1，不支持小数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，0-255，越小越往前靠',
  `ifcash` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否抵现，即允许财富当现金使用',
  `cash_ratio` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '抵现比例，即100财富值可抵用多少元',
  `ifcheck` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核，为1时表示获取到的财富需要管理员审核后才行',
  `min_val` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '最低使用值'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财富类型';

--
-- 转存表中的数据 `qinggan_wealth`
--

INSERT INTO `qinggan_wealth` (`id`, `site_id`, `title`, `identifier`, `unit`, `dnum`, `ifpay`, `pay_ratio`, `status`, `taxis`, `ifcash`, `cash_ratio`, `ifcheck`, `min_val`) VALUES
(1, 1, '积分', 'integral', '点', 2, 0, 0, 1, 10, 1, 1, 0, 100),
(2, 1, '钱包', 'wallet', '元', 2, 1, 1, 1, 20, 1, 100, 1, 0),
(5, 1, '红包', 'redbao', '个', 2, 0, 0, 1, 30, 1, 1, 0, 100);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth_info`
--

CREATE TABLE `qinggan_wealth_info` (
  `wid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '方案ID',
  `uid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID或会员ID或分类ID或项目ID',
  `lasttime` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
  `val` float UNSIGNED NOT NULL DEFAULT '0' COMMENT '最小财富为0，不考虑负数情况'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财富内容';

--
-- 转存表中的数据 `qinggan_wealth_info`
--

INSERT INTO `qinggan_wealth_info` (`wid`, `uid`, `lasttime`, `val`) VALUES
(1, 23, 1530439460, 46),
(1, 31, 1526718045, 970);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth_log`
--

CREATE TABLE `qinggan_wealth_log` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '自增ID',
  `wid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '财富ID',
  `rule_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '规则ID',
  `goal_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '目标会员ID',
  `mid` varchar(100) NOT NULL COMMENT '主键ID关联',
  `val` float NOT NULL DEFAULT '0' COMMENT '不带负号表示增加，带负号表示减去',
  `note` varchar(255) NOT NULL COMMENT '操作摘要',
  `appid` enum('admin','www','api') NOT NULL DEFAULT 'www' COMMENT '来自哪个接口',
  `dateline` int(11) NOT NULL DEFAULT '0' COMMENT '写入时间',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID，为0非会员',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID，为0非管理员',
  `ctrlid` varchar(100) NOT NULL COMMENT '控制器ID',
  `funcid` varchar(100) NOT NULL COMMENT '方法ID',
  `url` varchar(255) NOT NULL COMMENT '执行的URL',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未审核1已审核'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财富获取或消耗日志';

--
-- 转存表中的数据 `qinggan_wealth_log`
--

INSERT INTO `qinggan_wealth_log` (`id`, `wid`, `rule_id`, `goal_id`, `mid`, `val`, `note`, `appid`, `dateline`, `user_id`, `admin_id`, `ctrlid`, `funcid`, `url`, `status`) VALUES
(1, 1, 5, 23, '', 5, '会员登录', 'www', 1525283874, 23, 0, 'login', 'ok', '', 1),
(2, 1, 12, 23, '1762', 1, '阅读#1762', 'www', 1525283894, 23, 0, 'content', 'index', '', 1),
(3, 1, 12, 23, '1761', 1, '阅读#1761', 'www', 1525283903, 23, 0, 'content', 'index', '', 1),
(4, 1, 5, 23, '', 5, '会员登录', 'www', 1525749870, 23, 0, 'login', 'ok', '', 1),
(5, 1, 5, 23, '', 5, '会员登录', 'www', 1526391368, 23, 0, 'login', 'ok', '', 1),
(6, 1, 0, 31, '0', 1000, '管理员操作：管理员~', 'admin', 1526718026, 0, 1, 'wealth', 'val', 'admin.php...', 1),
(7, 1, 0, 31, '0', -30, '管理员操作：买了什么东东', 'admin', 1526718045, 0, 1, 'wealth', 'val', 'admin.php...', 1),
(8, 1, 5, 23, '', 5, '会员登录', 'www', 1527680521, 23, 0, 'login', 'ok', '', 1),
(9, 1, 12, 23, '1369', 1, '阅读#1369', 'www', 1527681139, 23, 0, 'content', 'index', '', 1),
(10, 1, 5, 23, '', 5, '会员登录', 'www', 1528095208, 23, 0, 'login', 'ok', '', 1),
(11, 1, 12, 23, '1371', 1, '阅读#1371', 'www', 1528095212, 23, 0, 'content', 'index', '', 1),
(12, 1, 12, 23, '1763', 1, '阅读#1763', 'www', 1528100918, 23, 0, 'content', 'index', '', 1),
(13, 1, 12, 23, '1368', 1, '阅读#1368', 'www', 1528103464, 23, 0, 'content', 'index', '', 1),
(14, 1, 5, 23, '', 5, '会员登录', 'www', 1529065111, 23, 0, 'login', 'ok', '', 1),
(15, 1, 5, 23, '', 5, '会员登录', 'www', 1529409220, 23, 0, 'login', 'ok', '', 1),
(16, 1, 5, 23, '', 5, '会员登录', 'www', 1530439460, 23, 0, 'login', 'ok', '', 1);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_wealth_rule`
--

CREATE TABLE `qinggan_wealth_rule` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '规则ID',
  `wid` int(10) UNSIGNED NOT NULL COMMENT '财产ID',
  `action` varchar(255) NOT NULL COMMENT '触发动作',
  `val` varchar(255) NOT NULL DEFAULT '0' COMMENT '值，负值表示减，大于0表示加，支持计算如price*2',
  `goal` varchar(255) NOT NULL DEFAULT 'user' COMMENT '目标类型user用户，agent1一级代理',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财富生成规则';

--
-- 转存表中的数据 `qinggan_wealth_rule`
--

INSERT INTO `qinggan_wealth_rule` (`id`, `wid`, `action`, `val`, `goal`, `taxis`) VALUES
(2, 1, 'register', '50', 'user', 10),
(4, 1, 'register', '20', 'introducer', 20),
(5, 1, 'login', '5', 'user', 30),
(12, 1, 'content', '1', 'user', 40),
(13, 1, 'comment', '5', 'user', 50),
(14, 1, 'payment', 'price', 'user', 60),
(15, 1, 'post', '10', 'user', 70),
(16, 1, 'register', '10', 'introducer2', 25),
(17, 1, 'register', '5', 'introducer3', 28),
(18, 1, 'payment', 'price*0.6', 'introducer', 62),
(19, 1, 'payment', 'price*0.3', 'introducer2', 65),
(20, 1, 'payment', 'price*0.1', 'introducer3', 68);

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_weixin_res`
--

CREATE TABLE `qinggan_weixin_res` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '附件ID，也是主键ID',
  `media_id` varchar(255) NOT NULL COMMENT '微信平台对应的media_id',
  `created_at` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `title` varchar(255) NOT NULL COMMENT '素材标题',
  `url` varchar(255) NOT NULL COMMENT '素材链接地址，仅限图片有效',
  `catetype` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0图文素材，1临时素材，2永久素材',
  `note` text NOT NULL COMMENT '素材摘要'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='素材管理器';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `qinggan_adm`
--
ALTER TABLE `qinggan_adm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_adm_popedom`
--
ALTER TABLE `qinggan_adm_popedom`
  ADD PRIMARY KEY (`id`,`pid`);

--
-- Indexes for table `qinggan_all`
--
ALTER TABLE `qinggan_all`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_attr`
--
ALTER TABLE `qinggan_attr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_attr_values`
--
ALTER TABLE `qinggan_attr_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aid` (`aid`);

--
-- Indexes for table `qinggan_cart`
--
ALTER TABLE `qinggan_cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_cart_product`
--
ALTER TABLE `qinggan_cart_product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_cate`
--
ALTER TABLE `qinggan_cate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `site_id` (`site_id`,`status`);

--
-- Indexes for table `qinggan_currency`
--
ALTER TABLE `qinggan_currency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_email`
--
ALTER TABLE `qinggan_email`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_express`
--
ALTER TABLE `qinggan_express`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_extc`
--
ALTER TABLE `qinggan_extc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_fav`
--
ALTER TABLE `qinggan_fav`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qinggan_fields`
--
ALTER TABLE `qinggan_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_freight`
--
ALTER TABLE `qinggan_freight`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_freight_price`
--
ALTER TABLE `qinggan_freight_price`
  ADD PRIMARY KEY (`zid`,`unit_val`);

--
-- Indexes for table `qinggan_freight_zone`
--
ALTER TABLE `qinggan_freight_zone`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fid` (`fid`);

--
-- Indexes for table `qinggan_gateway`
--
ALTER TABLE `qinggan_gateway`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_gd`
--
ALTER TABLE `qinggan_gd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_list`
--
ALTER TABLE `qinggan_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `site_id` (`site_id`,`identifier`,`status`);

--
-- Indexes for table `qinggan_list_21`
--
ALTER TABLE `qinggan_list_21`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_22`
--
ALTER TABLE `qinggan_list_22`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_23`
--
ALTER TABLE `qinggan_list_23`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_24`
--
ALTER TABLE `qinggan_list_24`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_40`
--
ALTER TABLE `qinggan_list_40`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_46`
--
ALTER TABLE `qinggan_list_46`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_61`
--
ALTER TABLE `qinggan_list_61`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_64`
--
ALTER TABLE `qinggan_list_64`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_65`
--
ALTER TABLE `qinggan_list_65`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_66`
--
ALTER TABLE `qinggan_list_66`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_68`
--
ALTER TABLE `qinggan_list_68`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_69`
--
ALTER TABLE `qinggan_list_69`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_74`
--
ALTER TABLE `qinggan_list_74`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_75`
--
ALTER TABLE `qinggan_list_75`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`,`project_id`,`cate_id`);

--
-- Indexes for table `qinggan_list_attr`
--
ALTER TABLE `qinggan_list_attr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tid` (`tid`);

--
-- Indexes for table `qinggan_list_biz`
--
ALTER TABLE `qinggan_list_biz`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_list_cate`
--
ALTER TABLE `qinggan_list_cate`
  ADD PRIMARY KEY (`id`,`cate_id`),
  ADD KEY `id` (`id`),
  ADD KEY `cate_id` (`cate_id`);

--
-- Indexes for table `qinggan_log`
--
ALTER TABLE `qinggan_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_module`
--
ALTER TABLE `qinggan_module`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_opt`
--
ALTER TABLE `qinggan_opt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `qinggan_opt_group`
--
ALTER TABLE `qinggan_opt_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_order`
--
ALTER TABLE `qinggan_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ordersn` (`sn`);

--
-- Indexes for table `qinggan_order_address`
--
ALTER TABLE `qinggan_order_address`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_order_express`
--
ALTER TABLE `qinggan_order_express`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_order_invoice`
--
ALTER TABLE `qinggan_order_invoice`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `qinggan_order_log`
--
ALTER TABLE `qinggan_order_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `qinggan_order_payment`
--
ALTER TABLE `qinggan_order_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_order_price`
--
ALTER TABLE `qinggan_order_price`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `qinggan_order_product`
--
ALTER TABLE `qinggan_order_product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_payment`
--
ALTER TABLE `qinggan_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_payment_group`
--
ALTER TABLE `qinggan_payment_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_payment_log`
--
ALTER TABLE `qinggan_payment_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qinggan_phpok`
--
ALTER TABLE `qinggan_phpok`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`,`site_id`);

--
-- Indexes for table `qinggan_plugins`
--
ALTER TABLE `qinggan_plugins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_popedom`
--
ALTER TABLE `qinggan_popedom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gid` (`gid`);

--
-- Indexes for table `qinggan_project`
--
ALTER TABLE `qinggan_project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `site_id` (`site_id`,`status`);

--
-- Indexes for table `qinggan_reply`
--
ALTER TABLE `qinggan_reply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tid` (`tid`);

--
-- Indexes for table `qinggan_res`
--
ALTER TABLE `qinggan_res`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ext` (`ext`);

--
-- Indexes for table `qinggan_res_cate`
--
ALTER TABLE `qinggan_res_cate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_res_ext`
--
ALTER TABLE `qinggan_res_ext`
  ADD PRIMARY KEY (`res_id`,`gd_id`);

--
-- Indexes for table `qinggan_session`
--
ALTER TABLE `qinggan_session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_site`
--
ALTER TABLE `qinggan_site`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_site_domain`
--
ALTER TABLE `qinggan_site_domain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `site_id` (`site_id`);

--
-- Indexes for table `qinggan_sysmenu`
--
ALTER TABLE `qinggan_sysmenu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_tag`
--
ALTER TABLE `qinggan_tag`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_tag_stat`
--
ALTER TABLE `qinggan_tag_stat`
  ADD PRIMARY KEY (`title_id`,`tag_id`),
  ADD KEY `title_id` (`title_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `qinggan_task`
--
ALTER TABLE `qinggan_task`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_tpl`
--
ALTER TABLE `qinggan_tpl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_user`
--
ALTER TABLE `qinggan_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_user_address`
--
ALTER TABLE `qinggan_user_address`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_user_ext`
--
ALTER TABLE `qinggan_user_ext`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_user_group`
--
ALTER TABLE `qinggan_user_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_wealth`
--
ALTER TABLE `qinggan_wealth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_wealth_info`
--
ALTER TABLE `qinggan_wealth_info`
  ADD PRIMARY KEY (`wid`,`uid`);

--
-- Indexes for table `qinggan_wealth_log`
--
ALTER TABLE `qinggan_wealth_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_wealth_rule`
--
ALTER TABLE `qinggan_wealth_rule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qinggan_weixin_res`
--
ALTER TABLE `qinggan_weixin_res`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `qinggan_adm`
--
ALTER TABLE `qinggan_adm`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID，系统自增', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_all`
--
ALTER TABLE `qinggan_all`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=130;

--
-- 使用表AUTO_INCREMENT `qinggan_attr`
--
ALTER TABLE `qinggan_attr`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `qinggan_attr_values`
--
ALTER TABLE `qinggan_attr_values`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=41;

--
-- 使用表AUTO_INCREMENT `qinggan_cart`
--
ALTER TABLE `qinggan_cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=41;

--
-- 使用表AUTO_INCREMENT `qinggan_cart_product`
--
ALTER TABLE `qinggan_cart_product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号';

--
-- 使用表AUTO_INCREMENT `qinggan_cate`
--
ALTER TABLE `qinggan_cate`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=597;

--
-- 使用表AUTO_INCREMENT `qinggan_currency`
--
ALTER TABLE `qinggan_currency`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '货币ID', AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `qinggan_email`
--
ALTER TABLE `qinggan_email`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=39;

--
-- 使用表AUTO_INCREMENT `qinggan_express`
--
ALTER TABLE `qinggan_express`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `qinggan_fav`
--
ALTER TABLE `qinggan_fav`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `qinggan_fields`
--
ALTER TABLE `qinggan_fields`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '字段ID，自增', AUTO_INCREMENT=875;

--
-- 使用表AUTO_INCREMENT `qinggan_freight`
--
ALTER TABLE `qinggan_freight`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '运费模板ID，自增ID', AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `qinggan_freight_zone`
--
ALTER TABLE `qinggan_freight_zone`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=25;

--
-- 使用表AUTO_INCREMENT `qinggan_gateway`
--
ALTER TABLE `qinggan_gateway`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `qinggan_gd`
--
ALTER TABLE `qinggan_gd`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `qinggan_list`
--
ALTER TABLE `qinggan_list`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=1929;

--
-- 使用表AUTO_INCREMENT `qinggan_list_attr`
--
ALTER TABLE `qinggan_list_attr`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=69;

--
-- 使用表AUTO_INCREMENT `qinggan_log`
--
ALTER TABLE `qinggan_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=376;

--
-- 使用表AUTO_INCREMENT `qinggan_module`
--
ALTER TABLE `qinggan_module`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=76;

--
-- 使用表AUTO_INCREMENT `qinggan_opt`
--
ALTER TABLE `qinggan_opt`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=15129;

--
-- 使用表AUTO_INCREMENT `qinggan_opt_group`
--
ALTER TABLE `qinggan_opt_group`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID ', AUTO_INCREMENT=20;

--
-- 使用表AUTO_INCREMENT `qinggan_order`
--
ALTER TABLE `qinggan_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_order_address`
--
ALTER TABLE `qinggan_order_address`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_order_express`
--
ALTER TABLE `qinggan_order_express`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_order_log`
--
ALTER TABLE `qinggan_order_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=30;

--
-- 使用表AUTO_INCREMENT `qinggan_order_payment`
--
ALTER TABLE `qinggan_order_payment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `qinggan_order_price`
--
ALTER TABLE `qinggan_order_price`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `qinggan_order_product`
--
ALTER TABLE `qinggan_order_product`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_payment`
--
ALTER TABLE `qinggan_payment`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=20;

--
-- 使用表AUTO_INCREMENT `qinggan_payment_group`
--
ALTER TABLE `qinggan_payment_group`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_payment_log`
--
ALTER TABLE `qinggan_payment_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `qinggan_phpok`
--
ALTER TABLE `qinggan_phpok`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=288;

--
-- 使用表AUTO_INCREMENT `qinggan_popedom`
--
ALTER TABLE `qinggan_popedom`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '权限ID，即自增ID', AUTO_INCREMENT=1418;

--
-- 使用表AUTO_INCREMENT `qinggan_project`
--
ALTER TABLE `qinggan_project`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID，也是应用ID', AUTO_INCREMENT=388;

--
-- 使用表AUTO_INCREMENT `qinggan_reply`
--
ALTER TABLE `qinggan_reply`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID';

--
-- 使用表AUTO_INCREMENT `qinggan_res`
--
ALTER TABLE `qinggan_res`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '资源ID', AUTO_INCREMENT=1324;

--
-- 使用表AUTO_INCREMENT `qinggan_res_cate`
--
ALTER TABLE `qinggan_res_cate`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '资源分类ID', AUTO_INCREMENT=25;

--
-- 使用表AUTO_INCREMENT `qinggan_site`
--
ALTER TABLE `qinggan_site`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '应用ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_site_domain`
--
ALTER TABLE `qinggan_site_domain`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_sysmenu`
--
ALTER TABLE `qinggan_sysmenu`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID号', AUTO_INCREMENT=98;

--
-- 使用表AUTO_INCREMENT `qinggan_tag`
--
ALTER TABLE `qinggan_tag`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id', AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `qinggan_task`
--
ALTER TABLE `qinggan_task`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `qinggan_tpl`
--
ALTER TABLE `qinggan_tpl`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `qinggan_user`
--
ALTER TABLE `qinggan_user`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID，即会员ID', AUTO_INCREMENT=32;

--
-- 使用表AUTO_INCREMENT `qinggan_user_address`
--
ALTER TABLE `qinggan_user_address`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `qinggan_user_group`
--
ALTER TABLE `qinggan_user_group`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '会员组ID', AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `qinggan_wealth`
--
ALTER TABLE `qinggan_wealth`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '财富ID', AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `qinggan_wealth_log`
--
ALTER TABLE `qinggan_wealth_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=17;

--
-- 使用表AUTO_INCREMENT `qinggan_wealth_rule`
--
ALTER TABLE `qinggan_wealth_rule`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则ID', AUTO_INCREMENT=21;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

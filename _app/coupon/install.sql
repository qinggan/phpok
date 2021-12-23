SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `qinggan_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL COMMENT '优惠方案',
  `pic1` varchar(255) NOT NULL,
  `pic2` varchar(255) NOT NULL,
  `pic3` varchar(255) NOT NULL,
  `country_id` int(10) UNSIGNED NOT NULL COMMENT '国家ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1启用，0禁用',
  `times` int(11) NOT NULL DEFAULT '1' COMMENT '优惠次数，不限次数请设置-1',
  `startdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `stopdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `types` varchar(255) NOT NULL COMMENT '规则，user基于用户，list基于主题，project基于项目，cate基于分类，all不限',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cateid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `user_groupid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `code` varchar(255) NOT NULL COMMENT '优惠码',
  `is_multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0只允许一个优惠使用，1允许多个优惠共用',
  `discount_val` float NOT NULL DEFAULT '0' COMMENT '优惠值',
  `discount_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0优惠比例，1表示金额，使用站点货币',
  `min_price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '不能低于多少金额',
  `users` text NOT NULL COMMENT '用户ID，多个用户ID用英文逗号隔开',
  `tids` text NOT NULL COMMENT '主题ID，多个主题ID用英文逗号隔开',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序，最大值255',
  `note` varchar(255) NOT NULL COMMENT '摘要说明',
  `time_start` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `time_stop` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '结束时间',
  `is_vouch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0普通，1推荐',
  `freq` varchar(255) NOT NULL COMMENT '频率，day指每天，week1-7表示每周几',
  `content` TEXT NOT NULL COMMENT '福利说明',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='优惠码规则管理' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qinggan_coupon_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `coupon_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优惠规则',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '指定哪个订单使用优惠',
  `title` VARCHAR(255) NOT NULL COMMENT '优惠券名称',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '优惠金额',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行时间',
  `currency_id` int(11) NOT NULL DEFAULT '0' COMMENT '货币ID',
  `currency_rate` decimal(13,8) NOT NULL DEFAULT '0.00000000' COMMENT '汇率',
  `code` varchar(255) NOT NULL COMMENT '优惠码',
  PRIMARY KEY (`id`),
  KEY `coupon_id` (`coupon_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='优惠历史' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `qinggan_coupon_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `coupon_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优惠码ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `code` varchar(255) NOT NULL COMMENT '用户领取后生成的唯一优惠码',
  `startdate` int(11) NOT NULL COMMENT '优惠码生效时间',
  `stopdate` int(11) NOT NULL COMMENT '优惠码结束时间',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户领取到的优惠码信息' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

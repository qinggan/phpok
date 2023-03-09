-- 安装数据库文件，直接在这里写SQL

CREATE TABLE IF NOT EXISTS `qinggan_social` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID，主键ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `who_id` int(10) UNSIGNED NOT NULL COMMENT '关注或黑名单的会员ID',
  `addtime` int(10) UNSIGNED NOT NULL COMMENT '关注时间',
  `is_black` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0普通模式，1黑名单模式',
  `is_idol` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0普通模式，1爱豆模式',
  PRIMARY KEY (`id`),
  KEY `fans_id` (`who_id`),
  KEY `user_id` (`user_id`,`is_idol`,`is_black`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员社交信息';

CREATE TABLE IF NOT EXISTS `qinggan_social_homepage` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID，主键ID',
  `banner` varchar(255) NOT NULL COMMENT '个人主页Banner',
  `mbanner` varchar(255) NOT NULL COMMENT '个人主页手机Banner',
  `heart` varchar(255) NOT NULL COMMENT '个人心语',
  `tags` varchar(255) NOT NULL COMMENT '标签，多个标签用英文逗号隔开',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='个人主页装饰信息';

CREATE TABLE IF NOT EXISTS `qinggan_user_links` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID，主键ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `who_id` int(10) UNSIGNED NOT NULL COMMENT '关注或黑名单的会员ID',
  `addtime` int(10) UNSIGNED NOT NULL COMMENT '关注时间',
  `is_black` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0普通模式，1黑名单模式',
  `is_idol` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0普通模式，1爱豆模式',
  PRIMARY KEY (`id`),
  KEY `fans_id` (`who_id`),
  KEY `user_id` (`user_id`,`is_idol`,`is_black`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员互关表，包括黑名单模式';
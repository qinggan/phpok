ALTER TABLE  `qinggan_site` ADD  `html_content_type` VARCHAR( 255 ) NOT NULL DEFAULT  'empty',
ADD  `html_root_dir` VARCHAR( 255 ) NOT NULL DEFAULT  'html/';


CREATE TABLE  `qinggan_user_relation` (
`uid` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '用户ID',
`introducer` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '介绍人ID',
`dateline` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '介绍时间'
) ENGINE = MYISAM COMMENT =  '会员介绍关系图';


CREATE TABLE IF NOT EXISTS `qinggan_wealth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '财富ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(100) NOT NULL COMMENT '财产名称',
  `identifier` varchar(100) NOT NULL COMMENT '标识，仅限英文字符',
  `unit` varchar(100) NOT NULL COMMENT '单位名称',
  `dnum` tinyint(1) NOT NULL DEFAULT '0' COMMENT '保留几位小数，为0表示只取整数',
  `ifpay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否支持充值',
  `ratio` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '兑换比例，即1元可以兑换多少，为0不支持充值，为1表示1：1，不支持小数',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不使用1使用',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '排序，0-255，越小越往前靠',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财富类型' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `qinggan_wealth_info` (
  `wid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '方案ID',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID或会员ID或分类ID或项目ID',
  `lasttime` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
  `val` float unsigned NOT NULL DEFAULT '0' COMMENT '最小财富为0，不考虑负数情况',
  PRIMARY KEY (`wid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='财富内容';


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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财富获取或消耗日志' AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='财富生成规则' AUTO_INCREMENT=1 ;

ALTER TABLE  `qinggan_user` ADD  `invoice_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '发票类型' AFTER  `integral` ,
ADD  `invoice_title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '发票抬头' AFTER  `invoice_type` ;

ALTER TABLE `qinggan_user`  ADD `post_date` int(10) unsigned NOT NULL default '0' COMMENT '本次登陆时间' ;
ALTER TABLE `qinggan_user`  ADD `pdip` varchar(100) NOT NULL COMMENT '本次登陆IP' ;
ALTER TABLE `qinggan_user`  ADD `lasttime` int(10) unsigned NOT NULL default '0' COMMENT '上次登陆时间' ;
ALTER TABLE `qinggan_user`  ADD `lastip` varchar(100) NOT NULL COMMENT '上次登陆IP' ;

ALTER TABLE  `qinggan_site_domain` ADD  `is_mobile` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '1此域名强制为手机版';

CREATE TABLE  `qinggan_order_invoice` (
`order_id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '订单ID号',
`type` VARCHAR( 100 ) NOT NULL COMMENT  '发票类型',
`title` VARCHAR( 255 ) NOT NULL COMMENT  '发票抬头',
`content` TEXT NOT NULL COMMENT  '发票内容',
PRIMARY KEY (  `order_id` )
) ENGINE = MYISAM COMMENT =  '订单发票';

CREATE TABLE IF NOT EXISTS `qinggan_user_invoice` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '自增ID号',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认',
  `type` varchar(100) NOT NULL COMMENT '发票类型',
  `title` varchar(255) NOT NULL COMMENT '发票抬头',
  `content` text NOT NULL COMMENT '发票内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员发票记录';


ALTER TABLE  `qinggan_project` ADD `cate_multiple` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '0分类单选1分类支持多选';=======
ALTER TABLE  `qinggan_all` ADD  `status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '0不使用1正常使用' AFTER  `title` ;>>>>>>> .r1735

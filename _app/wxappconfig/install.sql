-- 安装
CREATE TABLE IF NOT EXISTS `qinggan_weixin_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `openid` varchar(255) NOT NULL COMMENT '主键',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `token` varchar(255) NOT NULL COMMENT '会员token',
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登录时间',
  `token_refresh` varchar(255) NOT NULL COMMENT '用于定时刷新token的刷新token',
  `token_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次刷新token的时间',
  `headimg` varchar(255) NOT NULL COMMENT '头像图片',
  `nickname` varchar(255) NOT NULL COMMENT '昵称',
  `country` varchar(255) NOT NULL COMMENT '国家',
  `province` varchar(255) NOT NULL COMMENT '省份',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1男2女0未知',
  `unionid` varchar(255) NOT NULL COMMENT '有关注公众号才有此数据',
  `language` varchar(255) NOT NULL COMMENT '语言标识',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='微信会员' AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `qinggan_fav` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `lid` int(11) NOT NULL COMMENT '关联主题',
  `thumb` varchar(255) NOT NULL COMMENT '缩略图',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `note` varchar(255) NOT NULL COMMENT '摘要',
  `uid` int(10) unsigned NOT NULL COMMENT '作者ID',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `hits` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点击次数（仅限从收藏夹中点过去的）',
  `platform` varchar(255) NOT NULL COMMENT '平台，如小程序用miniprogram，网站用web，手机版用h5',
  `url` varchar(255) NOT NULL COMMENT 'WEB和H5无效，其他使用这个链接',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户收藏夹';
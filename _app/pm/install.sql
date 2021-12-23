-- 安装数据库文件，直接在这里写SQL

CREATE TABLE IF NOT EXISTS `qinggan_pm` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `addtime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '添加时间',
  `isread` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否已读，0未读1已读',
  `readtime` int(10) UNSIGNED NOT NULL COMMENT '已读时间',
  `content` text NOT NULL COMMENT '通知内容',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0表示系统发出，其他表示管理员发送',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='站内短消息功能';
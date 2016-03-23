CREATE TABLE IF NOT EXISTS `qinggan_fav` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `thumb` varchar(255) NOT NULL COMMENT '缩略图',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `note` varchar(255) NOT NULL COMMENT '摘要',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `lid` int(11) NOT NULL COMMENT '关联主题',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员收藏夹' AUTO_INCREMENT=1 ;


ALTER TABLE  `qinggan_site` DROP  `email_charset` ,
DROP  `email_server` ,
DROP  `email_port` ,
DROP  `email_ssl` ,
DROP  `email_account` ,
DROP  `email_pass` ,
DROP  `email_name` ,
DROP  `email` ,
DROP  `biz_billing` ,
DROP  `html_root_dir` ,
DROP  `html_content_type` ,
DROP  `biz_etpl` ;

ALTER TABLE  `qinggan_adm` ADD  `mobile` INT( 255 ) NOT NULL COMMENT  '手机号';

ALTER TABLE  `qinggan_module_fields` ADD  `search` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0不支持搜索1完全匹配搜索2模糊匹配搜索3区间搜索',
ADD  `search_separator` VARCHAR( 10 ) NOT NULL COMMENT  '分割符，仅限区间搜索时有效';


ALTER TABLE  `qinggan_phpok` ADD  `is_api` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '0不支持API调用，1支持',
ADD  `sqlinfo` TEXT NOT NULL COMMENT  'SQL语句';
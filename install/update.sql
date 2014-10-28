ALTER TABLE `qinggan_adm`  ADD `category` LONGTEXT NOT NULL COMMENT '可操作的分类ID，系统管理员无效' ;

-- 删除会员组扩展字段

ALTER TABLE `qinggan_user_group`
  DROP `read_popedom`,
  DROP `post_popedom`,
  DROP `reply_status`,
  DROP `post_status`;

ALTER TABLE `qinggan_user_group`  ADD `popedom` LONGTEXT NOT NULL COMMENT '前端权限' ;


ALTER TABLE `qinggan_module_fields`  ADD `is_front` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0前端不可用1前端可用' ;

ALTER TABLE `qinggan_adm`  ADD `vpass` VARCHAR(50) NOT NULL COMMENT '二次密码验证' ;

CREATE TABLE IF NOT EXISTS `qinggan_list_tag` (
  `id` int(10) unsigned NOT NULL COMMENT '自增ID',
  `title_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TAG标签ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='内容中的Tag管理器';
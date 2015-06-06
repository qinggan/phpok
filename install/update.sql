ALTER TABLE  `qinggan_res_cate` ADD  `filetypes` VARCHAR( 255 ) NOT NULL COMMENT  '附件类型',
ADD  `gdtypes` VARCHAR( 255 ) NOT NULL COMMENT  '支持的GD方案，多个GD方案用英文ID分开',
ADD  `gdall` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '1支持全部GD方案0仅支持指定的GD方案';



ALTER TABLE  `qinggan_res_cate` ADD  `typeinfo` VARCHAR( 200 ) NOT NULL COMMENT  '类型说明' AFTER  `filetypes`;

ALTER TABLE `qinggan_res_ext` DROP `x1`, DROP `y1`, DROP `x2`, DROP `y2`, DROP `w`, DROP `h`;


ALTER TABLE  `qinggan_res` ADD  `admin_id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '管理员ID';


CREATE TABLE IF NOT EXISTS `qinggan_list_cate` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `cate_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模块ID',
  PRIMARY KEY (`id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='主题绑定的分类';

-- 更新主题下的分类信息
INSERT INTO `qinggan_list_cate`(id,cate_id,site_id,project_id,module_id) SELECT id,cate_id,site_id,project_id,module_id FROM `qinggan_list` WHERE cate_id>0 AND module_id>0;


ALTER TABLE  `qinggan_project` ADD  `is_userid` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否绑定会员' AFTER  `is_biz` ,
ADD  `is_tpl_content` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '是否自定义内容模板' AFTER  `is_userid`;

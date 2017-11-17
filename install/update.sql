-- 2017年07月29日
ALTER TABLE `qinggan_reply` ADD `res` VARCHAR( 255 ) NOT NULL COMMENT '附件ID，多个附件用英文逗号隔开',
ADD `vtype` VARCHAR( 255 ) NOT NULL DEFAULT 'title' COMMENT '主题类型，titlte表示列表中的主题，project表示项目，cate表示分类，order表示订单，tag表示标签';

-- 2017年08月08日
ALTER TABLE `qinggan_opt_group` ADD `link_symbol` VARCHAR( 10 ) NOT NULL COMMENT '连接字符，未设置使用英文竖线';


-- 2017年09月03日
ALTER TABLE `qinggan_wealth_log` CHANGE `val` `val` FLOAT NOT NULL DEFAULT '0' COMMENT '不带负号表示增加，带负号表示减去';

-- 2017年09月08日
ALTER TABLE `qinggan_currency` CHANGE `val` `val` DECIMAL( 13, 8 ) UNSIGNED NOT NULL COMMENT '货币转化';


-- 2017年10月04日
ALTER TABLE `qinggan_module` ADD `mtype` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0联合模块，1独立模块';


-- 2017年10月11日 创建索引
ALTER TABLE `qinggan_list` DROP INDEX `project_id` ,ADD INDEX `project_index` ( `project_id` , `module_id` , `site_id` , `status` , `hidden` );
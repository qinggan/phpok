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


-- 2018年01月18日
ALTER TABLE `qinggan_module_fields` ADD `form_class` VARCHAR( 255 ) NOT NULL COMMENT '自定义表单Class';


-- 2018年03月17日
ALTER TABLE `qinggan_cart_product` ADD `dateline` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后操作时间' AFTER `unit`;

-- 2018年04月23日
ALTER TABLE `qinggan_order_payment`  ADD `currency_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币ID，为0使用订单默认货币'  AFTER `price`;


-- 2018年05月01日
ALTER TABLE `qinggan_order_log` ADD `user_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID' AFTER `note`, ADD `admin_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID' AFTER `user_id`;


-- 2018年05月18日
-- 货币汇率
ALTER TABLE `qinggan_order_payment`  ADD `currency_rate` DECIMAL(13,8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币汇率'  AFTER `currency_id`;

ALTER TABLE `qinggan_payment_log`  ADD `currency_rate` DECIMAL(13,8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '货币汇率'  AFTER `currency_id`;

-- 更新模块字段表
ALTER TABLE `qinggan_module_fields` RENAME TO `qinggan_fields`;
ALTER TABLE `qinggan_fields` DROP INDEX `module_id`;
ALTER TABLE `qinggan_fields` CHANGE `module_id` `ftype` VARCHAR(255) NOT NULL COMMENT '模型ID，当为数字时表示模块ID，非数表示其他模型的ID';

-- 2018年05月18日 删除 qinggan_ext 表
DROP TABLE IF EXISTS `qinggan_ext`;

-- 2018年05月18日 删除会员扩展字段表
DROP TABLE IF EXISTS `qinggan_user_fields`;

-- 2018年10月19日
ALTER TABLE `qinggan_project`  ADD `list_fields` VARCHAR(255) NOT NULL COMMENT '列表读取长度，如为空读全部' ;

-- 2018年10月21日
ALTER TABLE `qinggan_reply`  ADD `title` VARCHAR(255) NOT NULL COMMENT '评论标题，留空从主题中读取' ;

-- 2019年1月8日
ALTER TABLE `qinggan_order` ADD `confirm_time` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '系统自动确认收货时间';

-- 2019年1月13日
ALTER TABLE `qinggan_res_cate` ADD `etype` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0本地存储，其他数据则调用不同的网关存储';

-- 2019年1月18日
ALTER TABLE `qinggan_res_cate` ADD `upload_binary` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT '0传统上传，1二进制上传',ADD `compress` INT NOT NULL DEFAULT '0' COMMENT '0不压缩，大于0的数值表示宽高超过时就压缩到这个值内';

-- 2019年1月20日
ALTER TABLE `qinggan_res` CHANGE `ico` `ico` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'ICO图标文件';
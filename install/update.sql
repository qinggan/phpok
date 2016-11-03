ALTER TABLE `qinggan_wealth_rule` DROP `repeat`, DROP `mintime`;
ALTER TABLE `qinggan_wealth_rule` DROP `linkid`;
ALTER TABLE `qinggan_wealth_rule` DROP `efunc`;

ALTER TABLE  `qinggan_wealth_log` ADD  `rule_id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '规则ID' AFTER  `wid`;

CREATE TABLE  `qinggan_log` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT  '自增ID',
`note` VARCHAR( 255 ) NOT NULL COMMENT  '日志摘要',
`url` VARCHAR( 255 ) NOT NULL COMMENT  '请求网址',
`dateline` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '执行时间',
`appid` VARCHAR( 30 ) NOT NULL DEFAULT  'www' COMMENT  '接入APP_ID'
) ENGINE = MYISAM COMMENT =  '日志记录';


ALTER TABLE  `qinggan_wealth` ADD  `min_val` FLOAT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '最低使用值';


ALTER TABLE  `qinggan_order` ADD  `currency_rate` DECIMAL( 13, 8 ) UNSIGNED NOT NULL DEFAULT  '1' COMMENT  '货币汇率' AFTER  `currency_id`;

ALTER TABLE  `qinggan_order` ADD  `status_title` VARCHAR( 255 ) NOT NULL COMMENT  '订单状态说明' AFTER  `status`;

ALTER TABLE  `qinggan_order` ADD  `mobile` VARCHAR( 50 ) NOT NULL COMMENT  '手机号，用于短信发送';

ALTER TABLE  `qinggan_cart_product` ADD  `thumb` VARCHAR( 255 ) NOT NULL COMMENT  '缩略图';
ALTER TABLE  `qinggan_list_biz` ADD  `is_virtual` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0实物1虚拟产品';
ALTER TABLE  `qinggan_order_product` ADD  `note` VARCHAR( 255 ) NOT NULL COMMENT  '备注';
ALTER TABLE  `qinggan_cart_product` ADD  `is_virtual` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '0实物1虚拟或服务';
ALTER TABLE  `qinggan_order_product` ADD  `is_virtual` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '0实物1虚拟或服务';

DROP TABLE `qinggan_user_address`, `qinggan_user_invoice`;
ALTER TABLE  `qinggan_cart_product` ADD  `unit` VARCHAR( 50 ) NOT NULL COMMENT  '单位';

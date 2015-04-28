ALTER TABLE  `qinggan_res_cate` ADD  `filetypes` VARCHAR( 255 ) NOT NULL COMMENT  '附件类型',
ADD  `gdtypes` VARCHAR( 255 ) NOT NULL COMMENT  '支持的GD方案，多个GD方案用英文ID分开',
ADD  `gdall` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '1支持全部GD方案0仅支持指定的GD方案';



ALTER TABLE  `qinggan_res_cate` ADD  `typeinfo` VARCHAR( 200 ) NOT NULL COMMENT  '类型说明' AFTER  `filetypes`;

ALTER TABLE `qinggan_res_ext` DROP `x1`, DROP `y1`, DROP `x2`, DROP `y2`, DROP `w`, DROP `h`;


ALTER TABLE  `qinggan_res` ADD  `admin_id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '管理员ID';
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

-- 2019年1月26日
ALTER TABLE `qinggan_list`  ADD `style` VARCHAR(255) NOT NULL COMMENT 'CSS样式';

-- 2019年1月26日
ALTER TABLE `qinggan_project`  ADD `style` VARCHAR(255) NOT NULL COMMENT 'CSS样式';

-- 2019年1月26日
ALTER TABLE `qinggan_cate`  ADD `style` VARCHAR(255) NOT NULL COMMENT 'CSS样式';

-- 2019年2月12日
ALTER TABLE `qinggan_cart` ADD `coupon_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠码ID（仅当优惠码有效时体现）';

-- 2019年2月13日
ALTER TABLE `qinggan_coupon` ADD `note` VARCHAR( 255 ) NOT NULL COMMENT '摘要说明';


-- 2019年2月14日
ALTER TABLE `qinggan_project`  ADD `is_front` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否直接访问前台' ;

-- 2019年2月27日
ALTER TABLE `qinggan_freight` CHANGE `type` `type` ENUM('weight','volume','number','fixed','price') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'weight' COMMENT 'weight重量volume体积number数量fixed固定值price价格';

-- 2019年3月3日
ALTER TABLE `qinggan_module`  ADD `tbl` VARCHAR(255) NOT NULL DEFAULT 'list' COMMENT '关联主表，默认是list' ;

-- 2019年3月18日
ALTER TABLE `qinggan_cate`  ADD `module_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '模块ID' ;

-- 2019年3月27日
ALTER TABLE `qinggan_cart_product`  ADD `apps` VARCHAR(255) NOT NULL COMMENT '应用管理器' ;


-- 2019年4月30日
ALTER TABLE `qinggan_express` ADD `logo` VARCHAR(255) NOT NULL COMMENT '物流快递公司的Logo' AFTER `homepage`;
ALTER TABLE `qinggan_express` ADD `content` TEXT NOT NULL COMMENT '公司介绍';

-- 2019年5月10日
ALTER TABLE `qinggan_cart_product` ADD `parent_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID，不为0表示这是一个捆绑销售';

-- 2019年5月12日
ALTER TABLE `qinggan_user_group` ADD `tpl_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '通知模板ID' AFTER `tbl_id`;

ALTER TABLE `qinggan_order_address` ADD `type` VARCHAR(255) NOT NULL DEFAULT 'shipping' COMMENT '地址类型，shipping表示收货地址，billing表示账单地址' AFTER `zipcode`;

ALTER TABLE `qinggan_order_address` ADD `address2` VARCHAR(255) NOT NULL COMMENT '第二行地址，适用于第一行地址太多补全' AFTER `address`;

ALTER TABLE `qinggan_order_address` ADD `firstname` VARCHAR(255) NOT NULL COMMENT '名字' AFTER `fullname`, ADD `lastname` VARCHAR(255) NOT NULL COMMENT '姓氏' AFTER `firstname`;

-- 2019年5月18日
ALTER TABLE `qinggan_order_address` ADD `country_code` VARCHAR(255) NOT NULL COMMENT '国家代码' AFTER `country`;

-- 2019年7月14日
ALTER TABLE `qinggan_res` ADD `mime_type` VARCHAR(255) NOT NULL COMMENT '附件类型';

-- 2019年7月26日
ALTER TABLE `qinggan_list_biz` CHANGE `price` `price` DECIMAL(15,4) NOT NULL DEFAULT '0' COMMENT '价格';
ALTER TABLE `qinggan_list_attr` CHANGE `price` `price` DECIMAL(15,4) NOT NULL DEFAULT '0' COMMENT '增减价格值';

-- 2019年8月4日
ALTER TABLE `qinggan_order` CHANGE `price` `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000' COMMENT '金额';

ALTER TABLE `qinggan_order_payment` CHANGE `price` `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000' COMMENT '支付金额';

ALTER TABLE `qinggan_order_price` CHANGE `price` `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000' COMMENT '金额，-号表示优惠';

ALTER TABLE `qinggan_order_product` CHANGE `price` `price` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000' COMMENT '产品单价';

ALTER TABLE `qinggan_payment_log` CHANGE `price` `price` DECIMAL( 15, 4 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '价格';

-- 2019年9月1日
ALTER TABLE `qinggan_wealth_rule` ADD `group_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限制，其他限制这条规则仅在此会员组下的会员有效';
ALTER TABLE `qinggan_wealth_rule` ADD `uids` VARCHAR(255) NOT NULL COMMENT '多个会员ID用英文逗号隔开';
ALTER TABLE `qinggan_wealth_rule` ADD `qty_type` VARCHAR(255) NOT NULL DEFAULT 'order' COMMENT 'order指订单数，product指产品数';
ALTER TABLE `qinggan_wealth_rule` ADD `qty` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限，其他值是在订单数量或产品数量值';
ALTER TABLE `qinggan_wealth_rule` ADD `price_type` VARCHAR(255) NOT NULL DEFAULT 'order' COMMENT 'order指订单价格，product指产品价格';
ALTER TABLE `qinggan_wealth_rule` ADD `price` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限，其他值指订单或产品价格时有效';
ALTER TABLE `qinggan_wealth_rule` ADD `project_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限制，其他值限制项目';
ALTER TABLE `qinggan_wealth_rule` ADD `title_id` VARCHAR( 255 ) NOT NULL COMMENT '主题限制，多个主题用英文逗号隔开，建议不超过30个主题';

-- 2019年9月2日
ALTER TABLE `qinggan_wealth_rule` ADD `goal_group_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限，其他限制目标会员组ID';
ALTER TABLE `qinggan_wealth_rule` ADD `goal_uids` VARCHAR(255) NOT NULL COMMENT '目标会员ID，多个会员ID用英文逗号隔开';

-- 2019年9月3日
ALTER TABLE `qinggan_project` ADD `is_api` INT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0接口不可访问，1可访问';

-- 2019年9月9日
ALTER TABLE `qinggan_order_address` ADD `address2` VARCHAR(255) NOT NULL COMMENT '楼层房号';
ALTER TABLE `qinggan_order_address` ADD `type` VARCHAR(255) NOT NULL DEFAULT 'shipping' COMMENT '地址类型，shipping收货地址，billing账单地址';
ALTER TABLE `qinggan_order_address` ADD `firstname` VARCHAR(255) NOT NULL COMMENT '名字';
ALTER TABLE `qinggan_order_address` ADD `lastname` VARCHAR(255) NOT NULL COMMENT '姓氏';

ALTER TABLE `qinggan_user_address` ADD `address2` VARCHAR(255) NOT NULL COMMENT '楼层房号';
ALTER TABLE `qinggan_user_address` ADD `firstname` VARCHAR(255) NOT NULL COMMENT '名字';
ALTER TABLE `qinggan_user_address` ADD `lastname` VARCHAR(255) NOT NULL COMMENT '姓氏';


-- 2019年9月17日
ALTER TABLE `qinggan_wealth_rule` ADD `if_stop` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不中止，1中止';

-- 2019年10月06日
ALTER TABLE `qinggan_all` ADD `is_api` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0禁用API，1启用API';

-- 2019年10月30日
ALTER TABLE `qinggan_tag` ADD `seo_title` VARCHAR( 255 ) NOT NULL COMMENT 'SEO标题';
ALTER TABLE `qinggan_tag` ADD `seo_keywords` VARCHAR( 255 ) NOT NULL COMMENT 'SEO关键字';
ALTER TABLE `qinggan_tag` ADD `seo_desc` VARCHAR( 255 ) NOT NULL COMMENT 'SEO描述';

-- 2019年11月5日
ALTER TABLE `qinggan_tag` ADD `tpl` VARCHAR( 255 ) NOT NULL COMMENT '模板名称';

-- 2019年11月5日
ALTER TABLE `qinggan_tag` CHANGE `replace_count` `replace_count` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '替换次数';

-- 2019年11月9日
CREATE TABLE `qinggan_tag_node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的TagID',
  `identifier` varchar(255) NOT NULL COMMENT '标识变量名，在同一个标签里不能重复',
  `title` varchar(255) NOT NULL COMMENT '节点名称',
  `psize` int(11) NOT NULL DEFAULT '0' COMMENT '默认文章数，用于未指定时自动读取的数量',
  `ids` text NOT NULL COMMENT '文章ID，多个ID用英文逗号隔开',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '为1表示读列表，为0表示随机从ids里选择一篇读取（如果有多个）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未启用，1启用',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `taxis` tinyint(3) unsigned NOT NULL DEFAULT '255' COMMENT '值越小越往前排，最大不超过255',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='标签节点管理器';

-- 2019年11月16日
ALTER TABLE `qinggan_list` ADD `lastdate` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后修改时间' AFTER `dateline`;
ALTER TABLE `qinggan_tag` ADD `identifier` VARCHAR(255) NOT NULL COMMENT '标识' AFTER `site_id`;
UPDATE `qinggan_list` SET lastdate=dateline;

-- 2019年11月20日
CREATE TABLE IF NOT EXISTS `qinggan_menu` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID，主键',
  `site_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '站点ID',
  `group_id` varchar(255) NOT NULL COMMENT '菜单组ID',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID，支持无限级菜单',
  `title` varchar(255) NOT NULL COMMENT '菜单名称',
  `type` varchar(255) NOT NULL COMMENT '类型，project指项目，cate指分类，content指内容，link自定义',
  `project_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cate_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类ID',
  `list_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '主题ID',
  `link` varchar(255) NOT NULL COMMENT '自定义链接，最长不能超过255',
  `target` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0当前页，1新窗口',
  `is_userid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0游客，1仅限会员',
  `taxis` tinyint(3) UNSIGNED NOT NULL DEFAULT '255' COMMENT '排序，最大255，值越小越往前靠',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未审，1正常',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='菜单管理';

-- 2020年1月3日
ALTER TABLE `qinggan_project` ADD `psize_api` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'API接口读取的数量';

ALTER TABLE `qinggan_cate` ADD `psize_api` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'API接口读取的数量';

-- 2020年1月7日
ALTER TABLE `qinggan_project` ADD `limit_times` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间间隔限制，0表示不限制';
ALTER TABLE `qinggan_project` ADD `limit_similar` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '相似度值限制，0表示不限制';

-- 2020年3月10日
ALTER TABLE `qinggan_order_product`  ADD `parent_tid` INT NOT NULL DEFAULT '0' COMMENT '父级产品ID，用于区分购买的产品是主产品ID还是配件产品，捆绑销售用于区分是从哪个主产品进入的'  AFTER `tid`;

CREATE TABLE IF NOT EXISTS `qinggan_item_merge` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0表示组合价格模式，1表示单独价格模式',
  `price` decimal(16,4) NOT NULL COMMENT '产品价格，仅在组合模式下有效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='产品组合销售';

CREATE TABLE IF NOT EXISTS `qinggan_item_merge_list` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `item_id` int(11) NOT NULL COMMENT '组合产品ID',
  `tid` int(11) NOT NULL COMMENT '产品ID',
  `price` decimal(16,4) NOT NULL COMMENT '产品价格，仅在组合ID的类型为独立价格时有效',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='组合产品明细';
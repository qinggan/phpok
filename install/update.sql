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
ALTER TABLE `qinggan_order_log` ADD `user_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID' AFTER `note`, ADD `admin_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '管理员ID' AFTER `user_id`;


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

-- 2018年05月18日 删除用户扩展字段表
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
ALTER TABLE `qinggan_wealth_rule` ADD `group_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限制，其他限制这条规则仅在此用户组下的用户有效';
ALTER TABLE `qinggan_wealth_rule` ADD `uids` VARCHAR(255) NOT NULL COMMENT '多个用户ID用英文逗号隔开';
ALTER TABLE `qinggan_wealth_rule` ADD `qty_type` VARCHAR(255) NOT NULL DEFAULT 'order' COMMENT 'order指订单数，product指产品数';
ALTER TABLE `qinggan_wealth_rule` ADD `qty` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限，其他值是在订单数量或产品数量值';
ALTER TABLE `qinggan_wealth_rule` ADD `price_type` VARCHAR(255) NOT NULL DEFAULT 'order' COMMENT 'order指订单价格，product指产品价格';
ALTER TABLE `qinggan_wealth_rule` ADD `price` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限，其他值指订单或产品价格时有效';
ALTER TABLE `qinggan_wealth_rule` ADD `project_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限制，其他值限制项目';
ALTER TABLE `qinggan_wealth_rule` ADD `title_id` VARCHAR( 255 ) NOT NULL COMMENT '主题限制，多个主题用英文逗号隔开，建议不超过30个主题';

-- 2019年9月2日
ALTER TABLE `qinggan_wealth_rule` ADD `goal_group_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不限，其他限制目标用户组ID';
ALTER TABLE `qinggan_wealth_rule` ADD `goal_uids` VARCHAR(255) NOT NULL COMMENT '目标用户ID，多个用户ID用英文逗号隔开';

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
  `is_userid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0游客，1仅限用户',
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


-- 2020年5月4日
ALTER TABLE `qinggan_coupon_history` ADD `title` VARCHAR( 255 ) NOT NULL COMMENT '优惠券名称' AFTER `id`;

-- 2020年7月19日
ALTER TABLE `qinggan_menu` ADD `submenu` VARCHAR( 255 ) NOT NULL COMMENT '二级菜单类型';

-- 2020年7月21日
CREATE TABLE IF NOT EXISTS `qinggan_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(255) NOT NULL COMMENT '关键字',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次执行时间',
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT '搜索次数',
  `sign` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未标记，1已标记',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='搜索数据统计' AUTO_INCREMENT=1 ;

-- 2020年9月14日 增加运费/税费增减操作
ALTER TABLE `qinggan_list_attr` ADD `tax` DECIMAL(15,4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '税费';
ALTER TABLE `qinggan_list_attr` ADD `freight` DECIMAL(15,4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '运费';

-- 2020年9月14日 增加库存管理
CREATE TABLE IF NOT EXISTS `qinggan_stock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `attrs` text NOT NULL COMMENT '主题属性，多个属性用英文逗号隔开，属性ID和值ID用冒号隔开',
  `qty` varchar(255) NOT NULL COMMENT '库存数量',
  `unit` varchar(255) NOT NULL COMMENT '计量单位',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='库存表' AUTO_INCREMENT=1;

-- 2020年9月15日 税费及全球价格设置
ALTER TABLE `qinggan_project` ADD `is_stock` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1启用库存，0禁用库存';
ALTER TABLE `qinggan_project` ADD `world_location` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '产品全球价格设置';

-- 产品扩展字段里的价格
CREATE TABLE IF NOT EXISTS `qinggan_list_extprice` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主产品ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '字段ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的产品ID',
  `price` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '价格',
  `tax` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '税费',
  `freight` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '运费',
  PRIMARY KEY (`id`,`fid`,`tid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='扩展字段涉及到的价格属性';

CREATE TABLE IF NOT EXISTS `qinggan_list_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `country_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '国家ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `price` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '价格',
  `freight` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '运费',
  `tax` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '税费',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='针对不同国家实现不同的价格' AUTO_INCREMENT=1 ;

-- 运费增加国家
ALTER TABLE `qinggan_freight` ADD `country_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '国家ID',ADD INDEX (`country_id`); 

-- 2020年10月25日
ALTER TABLE `qinggan_list` ADD INDEX `admin_project` (`parent_id`, `site_id`, `project_id`);

-- 2020年11月24日
ALTER TABLE `qinggan_fields` ADD `onlyone` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '唯一性检测，为1值时检测字段在当前项目是否唯一';

-- 2021年1月4日
ALTER TABLE `qinggan_gd` ADD `title` VARCHAR(255) NOT NULL COMMENT '类型名称，方便管理' AFTER `identifier`;

-- 2021年3月8日
ALTER TABLE `qinggan_project` ADD `biz_service` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '1服务0实物' AFTER `world_location`;

-- 2021年4月13日
ALTER TABLE `qinggan_fields` ADD `group_id` VARCHAR(255) NOT NULL DEFAULT 'main' COMMENT '字段所在组，默认是main';

-- 2021年5月14日
ALTER TABLE `qinggan_res_cate` ADD `is_front` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0前台不可用，1前台可用';

-- 2021年5月15日
ALTER TABLE `qinggan_payment` ADD `iframe` TINYINT(1) UNSIGNED NOT NULL COMMENT '0表示跳转支付1表示嵌入支付';

-- 2021年5月18日
ALTER TABLE `qinggan_adm` ADD `note` VARCHAR(50) NOT NULL COMMENT '管理员角色' AFTER `email`;

-- 2021年6月4日
ALTER TABLE `qinggan_project`  ADD `group_id` VARCHAR(255) NOT NULL COMMENT '项目在前台显示的组标识';


-- 2021年6月19日
ALTER TABLE `qinggan_menu` ADD `thumb` VARCHAR(255) NOT NULL COMMENT '图片图标' AFTER `list_id`;
ALTER TABLE `qinggan_menu` ADD `iconfont` VARCHAR(255) NOT NULL COMMENT '字体图标' AFTER `thumb`;

-- 2021年6月28日
-- 增加插件绑定项目或是多项目
ALTER TABLE `qinggan_plugins` ADD `pid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '项目ID' AFTER `id`;
ALTER TABLE `qinggan_plugins` ADD `pids` VARCHAR(255) NOT NULL COMMENT '关联多个项目，用英文逗号隔开' AFTER `pid`;
ALTER TABLE `qinggan_plugins` ADD INDEX (`pid`);


-- 2021年6月29日
ALTER TABLE `qinggan_wealth` ADD `thumb` VARCHAR(255) NOT NULL COMMENT '背景图片' AFTER `identifier`, ADD `iconfont` VARCHAR(255) NOT NULL COMMENT '字体图标' AFTER `thumb`;


-- 2021年7月15日
DROP TABLE IF EXISTS `qinggan_user_autologin`;
CREATE TABLE IF NOT EXISTS `qinggan_user_autologin` (
  `id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `code` varchar(255) NOT NULL COMMENT '随机码，用于生成验签，以确保单点登录模式',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `device` varchar(255) NOT NULL COMMENT '设备标识',
  PRIMARY KEY (`id`,`device`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统自动登录验签模式';

-- 2021年7月23日
CREATE TABLE IF NOT EXISTS `qinggan_config` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `identifier` varchar(50) NOT NULL COMMENT '变量标识',
  `langid` varchar(10) NOT NULL COMMENT '语言ID',
  `content` text NOT NULL COMMENT '变量内容',
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`,`langid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统变量参数';

ALTER TABLE `qinggan_exam_topic` ADD `level` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '等级，最小为1级' AFTER `is_require`;
ALTER TABLE `qinggan_exam_info` ADD `level` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目等级' AFTER `is_require`;
ALTER TABLE `qinggan_exam` ADD `level` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '试卷级别' AFTER `timetype`;

-- 2021年11月30日
ALTER TABLE `qinggan_fields` ADD `filter` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不启用1单选2可多选';
ALTER TABLE `qinggan_fields` ADD `filter_title` VARCHAR(255) NOT NULL COMMENT '筛选器名称';
ALTER TABLE `qinggan_fields` ADD `filter_join` VARCHAR(20) NOT NULL COMMENT '连接符';
ALTER TABLE `qinggan_fields` ADD `filter_content` TEXT NOT NULL COMMENT '自定义筛选列表'; 

-- 2021年12月2日
ALTER TABLE `qinggan_project` ADD `filter_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不使用筛选1使用';
ALTER TABLE `qinggan_project` ADD `filter_cate_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不启用筛选分类，1启用';
ALTER TABLE `qinggan_project` ADD `filter_cate` VARCHAR(255) NOT NULL COMMENT '筛选分类名称，留空使用分类自身';
ALTER TABLE `qinggan_project` ADD `filter_price` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0不使用，1使用';
ALTER TABLE `qinggan_project` ADD `filter_price_title` VARCHAR(255) NOT NULL COMMENT '价格标题，留空使用价格';
ALTER TABLE `qinggan_project` ADD `filter_price_info` TEXT NOT NULL COMMENT '筛选价格内容设定';

-- 2021年12月17日
ALTER TABLE `qinggan_project` ADD `user_alias` VARCHAR( 100 ) NOT NULL COMMENT '用户别名';
ALTER TABLE `qinggan_project` ADD `user_note` VARCHAR( 255 ) NOT NULL COMMENT '用户备注';

-- 2022年1月10日
ALTER TABLE `qinggan_wealth` ADD banner varchar(255) NOT NULL COMMENT '大图';
ALTER TABLE `qinggan_wealth` ADD thumb varchar(255) NOT NULL COMMENT '小图';
ALTER TABLE `qinggan_wealth` ADD iconfont varchar(255) NOT NULL COMMENT '字体图标';

DROP TABLE IF EXISTS `qinggan_user_autologin`;

ALTER TABLE `qinggan_site` DROP api_code;


-- 2022年1月24日
ALTER TABLE `qinggan_project` ADD `admin_group` VARCHAR( 255 ) NOT NULL COMMENT '后台分组';

-- 2022年2月14日
CREATE TABLE IF NOT EXISTS `qinggan_fulltext` (
  `id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主键ID，关联 qinggan_list 里的ID',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `project_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '模块ID',
  `cate_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后执行时间',
  `content` text NOT NULL COMMENT '全文索引',
  PRIMARY KEY (`id`),
  KEY `site_id` (`site_id`,`project_id`,`module_id`,`cate_id`),
  FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='全文搜索，以此定位ID，独立模块不支持';

-- 2022年2月18日
CREATE TABLE IF NOT EXISTS `qinggan_stat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `dateinfo` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日期',
  `pv` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '页面浏览数',
  `uv` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户数',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'IP数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `dateinfo` (`dateinfo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='统计' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `qinggan_stat_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `stat_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应主表stat里的ID',
  `e_year` varchar(5) NOT NULL COMMENT '年',
  `e_month` varchar(5) NOT NULL COMMENT '月份',
  `e_day` varchar(5) NOT NULL COMMENT '日',
  `e_hour` varchar(5) NOT NULL COMMENT '时',
  `e_browser` varchar(255) NOT NULL COMMENT '浏览器',
  `e_version` varchar(10) NOT NULL COMMENT '版本号',
  `e_country` varchar(255) NOT NULL COMMENT '国家',
  `e_province` varchar(255) NOT NULL COMMENT '省份',
  `e_city` varchar(255) NOT NULL COMMENT '城市',
  `e_net` varchar(255) NOT NULL COMMENT '网络',
  `e_device` varchar(255) NOT NULL COMMENT '设备',
  `e_pixel` varchar(255) NOT NULL COMMENT '设备分辨率',
  `pv` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问页面数',
  `uv` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户数',
  `ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'IP数',
  PRIMARY KEY (`id`),
  KEY `stat_id` (`stat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='统计明细' AUTO_INCREMENT=1 ;

-- 2022年3月3日 更新独立表的属性
ALTER TABLE `qinggan_77` ADD `status` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '状态';
ALTER TABLE `qinggan_77` ADD `hidden` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '隐藏';
ALTER TABLE `qinggan_77` ADD `sort` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序';
ALTER TABLE `qinggan_77` ADD `dateline` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间';
ALTER TABLE `qinggan_77` ADD `hits` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '查看次数';

-- 2022年6月7日
ALTER TABLE `qinggan_fields` ADD `parent_id` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID（用于实现横排）';

-- 2022年6月9日
ALTER TABLE `qinggan_stat_info` ADD `firstlogin` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '首次进入时间';
ALTER TABLE `qinggan_stat_info` ADD `lastlogin` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后进入时间'; 

-- 2022年6月18日

CREATE TABLE IF NOT EXISTS `qinggan_stock` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `attr` varchar(255) NOT NULL COMMENT '属性值，多个属性值用英文逗号隔开',
  `qty` int(10) NOT NULL DEFAULT '0' COMMENT '库存数量，仅支持整数',
  `cost` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '进货价',
  `market` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '市场价',
  `price` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT '销售价',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku_id` (`tid`,`attr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存表' AUTO_INCREMENT=1 ;

-- 2022年6月18日
ALTER TABLE `qinggan_list_biz` ADD `qty` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '数量';

-- 2022年6月18日

CREATE TABLE IF NOT EXISTS `qinggan_wholesale` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题',
  `qty` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '数量',
  `price` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='批发价格管理' AUTO_INCREMENT=1 ;

-- 2022年6月20日
ALTER TABLE `qinggan_order_product` ADD `price_total` DECIMAL( 10, 4 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总价格';
ALTER TABLE `qinggan_order_product` ADD `discount` DECIMAL( 10, 4 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '优惠价';
ALTER TABLE `qinggan_order_product` ADD `discount_note` VARCHAR( 255 ) NOT NULL COMMENT '优惠说明';

-- 2022年6月21日
CREATE TABLE IF NOT EXISTS `qinggan_log_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tbl` varchar(255) NOT NULL COMMENT '表名（不含前缀）',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `vtype` varchar(255) NOT NULL DEFAULT '' COMMENT '类型',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '类型标识',
  `content1` text NOT NULL COMMENT '变更前的数据',
  `content2` text NOT NULL COMMENT '变更后的数据',
  PRIMARY KEY (`id`),
  KEY `tbl_tid` (`tbl`,`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='内容变更记录' AUTO_INCREMENT=6 ;

-- 2022年6月26日
ALTER TABLE `qinggan_list_biz` ADD `min_qty` INT UNSIGNED NOT NULL DEFAULT '1' COMMENT '最小购买数量';

-- 2022年6月29日
ALTER TABLE `qinggan_payment` ADD `admin_note` VARCHAR( 255 ) NOT NULL COMMENT '管理员备注';

-- 2022年6月29日 格式化货币位数
ALTER TABLE `qinggan_currency` ADD `dpl` INT UNSIGNED NOT NULL DEFAULT '2' COMMENT 'Decimal point length 简写，即小数点长度';


-- 2022年7月16日
ALTER TABLE `qinggan_order` ADD `fullname` VARCHAR(255) NOT NULL COMMENT '联系人';


-- 2022年9月1日
ALTER TABLE `qinggan_world_location` ADD note VARCHAR(255) NOT NULL COMMENT '备注';
UPDATE `qinggan_world_location` SET pid=7 WHERE id IN(278,279,280);
UPDATE `qinggan_world_location` SET name='台湾',name_en='Taiwan' WHERE id=278;
UPDATE `qinggan_world_location` SET name='香港',name_en='HongKong' WHERE id=279;
UPDATE `qinggan_world_location` SET name='澳门',name_en='Macao' WHERE id=280;

ALTER TABLE `qinggan_list_biz` CHANGE `qty` `qty` INT(10) NOT NULL DEFAULT '0' COMMENT '数量';

-- 2022年10月25日

CREATE TABLE IF NOT EXISTS `qinggan_click` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `code` varchar(20) NOT NULL COMMENT '字段标识',
  `tbl` varchar(30) NOT NULL COMMENT '用户表，不含前缀',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `session_id` varchar(100) NOT NULL COMMENT 'SessionID',
  `ip` varchar(100) NOT NULL COMMENT '用户IP',
  `val` int(11) NOT NULL DEFAULT '0' COMMENT '值，仅支持整数',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后点击时间（游客3小时内重复操作会取消）',
  PRIMARY KEY (`id`),
  KEY `user` (`tid`,`user_id`,`code`,`tbl`),
  KEY `guest` (`tid`,`session_id`,`code`,`ip`,`tbl`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='主题点击记录'

-- 2023年1月29日，增加设计器组件
CREATE TABLE IF NOT EXISTS `qinggan_design` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(120) NOT NULL COMMENT '名称',
  `note` text NOT NULL COMMENT '摘要',
  `img` varchar(255) NOT NULL COMMENT '图片',
  `code` varchar(255) NOT NULL COMMENT '用于生成模板文件',
  `vtype` varchar(50) NOT NULL COMMENT '组件类型',
  `ext` text COMMENT '组件扩展参数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='组件设计器' AUTO_INCREMENT=1 ;

-- 2023年3月2日
CREATE TABLE IF NOT EXISTS `qinggan_fields_ext` (
  `id` int(10) unsigned NOT NULL COMMENT '自增ID',
  `fields_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '扩展字段ID',
  `keyname` varchar(255) NOT NULL COMMENT '键名',
  `keydata` text NOT NULL COMMENT '键值'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='字段扩展表';


ALTER TABLE `qinggan_fields_ext`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fields_id` (`fields_id`);


ALTER TABLE `qinggan_fields_ext`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID';


-- 2023年9月9日
ALTER TABLE `qinggan_module` ADD `tbname` VARCHAR( 50 ) NOT NULL COMMENT '表别名，仅限英文字母数字';
ALTER TABLE `qinggan_fields` ADD `hidden` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0显示1隐藏';
ALTER TABLE `qinggan_fields` ADD `is_system` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0常规1系统';

-- 2023年10月6日
ALTER TABLE `qinggan_project` ADD `icon` VARCHAR( 255 ) NOT NULL COMMENT '侧边栏文本图标';
-- 更新时间：2015年01月22日 01时38分
DROP TABLE IF EXISTS `qinggan_tag`;
CREATE TABLE IF NOT EXISTS `qinggan_tag` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `site_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '站点ID',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `url` varchar(255) NOT NULL COMMENT '关键字网址',
  `target` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0原窗口打开，1新窗口打开',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='关键字管理器' AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_tag_stat`
--

DROP TABLE IF EXISTS `qinggan_tag_stat`;
CREATE TABLE IF NOT EXISTS `qinggan_tag_stat` (
  `title_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TAG标签ID',
  PRIMARY KEY (`title_id`,`tag_id`),
  KEY `title_id` (`title_id`),
  KEY `tag_id` (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tag主题统计';

DROP TABLE IF EXISTS `qinggan_list_tag`;
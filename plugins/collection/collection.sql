-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1:3306
-- 生成日期: 2015 年 09 月 02 日 00:22
-- 服务器版本: 5.5.40
-- PHP 版本: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `phpok`
--

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_collection`
--

CREATE TABLE IF NOT EXISTS `qinggan_collection` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '主题',
  `title` varchar(200) NOT NULL COMMENT '要采集的标题',
  `linkurl` varchar(255) NOT NULL COMMENT '来源网站，可用于伪造refer url',
  `url_charset` varchar(20) NOT NULL DEFAULT 'utf-8' COMMENT '网站编码',
  `totalcount` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文章数',
  `project_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '项目ID',
  `cateid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `listurl` text NOT NULL COMMENT '列表页网址，一行一个列表',
  `list_tags_start` text NOT NULL COMMENT '网址采集范围开始',
  `list_tags_end` text NOT NULL COMMENT '网址采集范围结束',
  `url_tags` varchar(255) NOT NULL COMMENT '目标网址必须包含哪些字段',
  `url_not_tags` varchar(255) NOT NULL COMMENT '内容网址不能包含哪些字符',
  `is_gzip` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不使用GZIP，1使用',
  `is_proxy` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不使用代理，1使用',
  `proxy_service` varchar(255) NOT NULL COMMENT '代理服务器',
  `proxy_user` varchar(100) NOT NULL COMMENT '代理账号',
  `proxy_pass` varchar(100) NOT NULL COMMENT '代理密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_collection_files`
--

CREATE TABLE IF NOT EXISTS `qinggan_collection_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `cid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '采集项目ID',
  `lid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `fid` int(10) unsigned NOT NULL COMMENT '标签ID',
  `ext` varchar(10) NOT NULL COMMENT '扩展',
  `srcurl` varchar(255) NOT NULL COMMENT '原src文件地址',
  `newurl` varchar(255) NOT NULL COMMENT '新图片地址',
  PRIMARY KEY (`id`),
  KEY `lid` (`lid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_collection_format`
--

CREATE TABLE IF NOT EXISTS `qinggan_collection_format` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID号',
  `lid` mediumint(9) NOT NULL COMMENT '采集的ID',
  `fid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
  `content` longtext NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`),
  KEY `lid` (`lid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_collection_list`
--

CREATE TABLE IF NOT EXISTS `qinggan_collection_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cid` mediumint(8) unsigned NOT NULL COMMENT '采集器ID',
  `url` varchar(255) NOT NULL COMMENT '网址',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未采集1已采集2已发布',
  `postdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `qinggan_collection_tags`
--

CREATE TABLE IF NOT EXISTS `qinggan_collection_tags` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `cid` mediumint(8) unsigned NOT NULL COMMENT '采集器ID',
  `title` varchar(200) NOT NULL COMMENT '字段主题',
  `identifier` varchar(100) NOT NULL COMMENT '字段名',
  `tags_type` enum('var','string') NOT NULL DEFAULT 'var' COMMENT '字段类型',
  `rules` varchar(255) NOT NULL COMMENT '固定字符',
  `rules_start` varchar(255) NOT NULL COMMENT '采集内容开始标范围',
  `rules_end` varchar(255) NOT NULL COMMENT '采集内容结束',
  `del` text NOT NULL COMMENT '删除文字，任意值用(*)表示，一行一条规则',
  `del_url` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '删除网址',
  `del_html` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除HTML，级别最高，如果设为1，所有HTML代码将被去除',
  `del_font` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '清除font信息',
  `del_table` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '清除table信息',
  `del_span` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '清除span信息',
  `del_bold` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '清除加粗信息',
  `suburl_start` varchar(255) NOT NULL COMMENT '内容分页开始',
  `suburl_end` varchar(255) NOT NULL COMMENT '内容分页结束',
  `post_save` varchar(50) NOT NULL COMMENT '发布格式化',
  `translate` varchar(100) NOT NULL DEFAULT '0' COMMENT '翻译',
  `re1` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不保留原文1保留原文',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

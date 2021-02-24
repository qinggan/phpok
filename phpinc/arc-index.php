<?php
/**
 * 首页涉及到调用代码
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年5月3日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

//图片新闻
$piclist = phpok('news-pictures');
if($piclist && $piclist['ids']){
	$arclist = phpok('news','notin='.implode(",",$piclist['ids']));
}else{
	$arclist = phpok('news');
}


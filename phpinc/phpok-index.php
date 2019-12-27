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

//图片轮播
$slider = phpok('picplayer');


//产品列表
$products = phpok('new_products');

//图片新闻
$piclist = phpok('news-pictures');
$ids = '';
if($piclist['rslist']){
	$ids = array_keys($piclist['rslist']);
	$ids = implode(",",$ids);
}
if($ids){
	$arclist = phpok('news','notin='.$ids);
}else{
	$arclist = phpok('news');
}

//关于我们
$about = phpok('aboutus');


//联系我们
$contactus = phpok('contactus');

//友情链接
$link = phpok('link');

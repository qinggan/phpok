<?php
/**
 * 微信分享专用
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年9月24日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

$app->lib('weixin')->app_id('wxf84d920dc558bd08');
$wxurl = $rs ? $rs['url'] : ($cate_rs ? $cate_rs['url'] : ($page_rs ? $page_rs['url'] : $app->url));
$wxconfig = $app->lib('weixin')->jsapi_config($wxurl);
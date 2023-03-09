<?php
/**
 * 格式化论坛主题
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2022年3月12日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//常用项目参数
//多少小时内发的贴子或回复，显示为最新主题
$hour = 6;
if($rslist){
	foreach($rslist as $key=>$value){
		$icon = "fa-angle-right";
		if($value['toplevel'] == 2){
			$icon = 'fa-angle-double-up';
		}
		if($value['toplevel'] == 1){
			$icon = 'fa-angle-up';
		}
		$value['_icon'] = $icon;
		$value['_user'] = $value['user'] ? $value['user'] : array('user'=>'吃瓜群众','avatar'=>'images/avatar.gif');
		$value['_author'] = $value['_user']['user'];
		$value['_avatar'] = $value['_user']['avatar'];
		$value['_author_url'] = $value['user_id'] ? $app->url('user','info','id='.$value['user_id']) : '';
		$value['_lastdate'] = $value['replydate'] ? time_format($value['replydate']) : time_format($value['dateline']);
		$rslist[$key] = $value;
	}
}
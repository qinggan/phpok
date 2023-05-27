<?php
/*****************************************************************************************
	文件： phpinc/bbs_content.php
	备注： 论坛内容
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月18日 08时47分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if($rs['user_id']){
	$rs['user'] = $app->model('user')->get_one($rs['user_id']);
}
$pageid = $app->get('pageid','int');
if(!$pageid){
	$pageid = 1;
}
$comment = phpok('_comment','tid='.$rs['id'].'&pageid='.$pageid.'&psize=10');

<?php
/***********************************************************
	Filename: {phpok}/www/global.func.php
	Note	: 前台公共函数
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-16 13:13
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

if(!function_exists("phpok_next")){
	function phpok_next($rs)
	{
		if(!$rs){
			return false;
		}
		$id = is_array($rs) ? $rs['id'] : $rs;
		$next_id = $GLOBALS['app']->model('list')->get_next($id);
		if(!$next_id){
			return false;
		}
		return phpok('_arc',"title_id=".$next_id);
	}
}

if(!function_exists("phpok_prev")){
	function phpok_prev($rs)
	{
		if(!$rs){
			return false;
		}
		$id = is_array($rs) ? $rs['id'] : $rs;
		$prev_id = $GLOBALS['app']->model('list')->get_prev($id);
		if(!$prev_id){
			return false;
		}
		return phpok('_arc',"title_id=".$prev_id);
	}
}

?>
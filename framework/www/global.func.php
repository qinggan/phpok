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
		if(!$rs) return false;
		if(!is_array($rs)){
			$rs = $GLOBALS['app']->model('list')->call_one($rs);
			if(!$rs) return false;
		}
		$rs = $GLOBALS['app']->model('list')->get_next($rs['id'],$rs["cate_id"],$rs["project_id"],$rs["module_id"],$rs["site_id"]);
		if($rs){
			$idname = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$rs['url'] = $GLOBALS['app']->url($idname);
		}
		return $rs;
	}
}

if(!function_exists("phpok_prev")){
	function phpok_prev($rs)
	{
		if(!$rs) return false;
		if(!is_array($rs)){
			$rs = $GLOBALS['app']->model('list')->call_one($rs);
			if(!$rs) return false;
		}
		$rs = $GLOBALS['app']->model('list')->get_prev($rs['id'],$rs["cate_id"],$rs["project_id"],$rs["module_id"],$rs["site_id"]);
		if($rs){
			$idname = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$rs['url'] = $GLOBALS['app']->url($idname);
		}
		return $rs;
	}
}

?>
<?php
/**
 * 前台公共函数
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月05日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

if(!function_exists("phpok_next")){
	function phpok_next($rs)
	{
		if(!$rs){
			return false;
		}
		$next_id = $GLOBALS['app']->model('list')->get_next($rs);
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
		$prev_id = $GLOBALS['app']->model('list')->get_prev($rs);
		if(!$prev_id){
			return false;
		}
		return phpok('_arc',"title_id=".$prev_id);
	}
}
<?php
/***********************************************************
	Filename: {phpok}/form/ptitle_admin.php
	Note	: 主题选择器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ptitle_form
{
	function __construct()
	{
		//
	}

	function config()
	{
		$opt_list = $GLOBALS['app']->model('project')->get_all_project($_SESSION['admin_site_id'],"p.module>0");
		$GLOBALS['app']->assign("opt_list",$opt_list);
		$html = $GLOBALS['app']->dir_phpok."form/html/title_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	function format($rs)
	{
		if(!$rs["optlist_id"])
		{
			return "未指定选项组";
		}
		$project_id = $rs["optlist_id"];
		$project_rs = $GLOBALS['app']->model('project')->get_one($project_id);
		if(!$project_rs)
		{
			return "项目不存在，请检查";
		}
		$rslist = $GLOBALS['app']->model('list')->get_list($project_rs["module"],"l.status='1' AND l.project_id='".$project_id."'",0,0,$project_rs["orderby"]);
		if(!$rslist)
		{
			return "选项内容不存在，请检查";
		}
		if($rs["is_multiple"])
		{
			$content = array();
			if($rs["content"])
			{
				$content = explode(",",$rs["content"]);
			}
			$rs["content"] = $content;		
		}
		$file = $GLOBALS['app']->dir_phpok."form/html/title_form_admin.html";
		$GLOBALS['app']->assign("rs",$rs);
		$GLOBALS['app']->assign("rslist",$rslist);
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("rs");
		$GLOBALS['app']->unassign("rslist");
		return $content;
	}
}
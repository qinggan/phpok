<?php
/***********************************************************
	Filename: {phpok}/form/title_admin.php
	Note	: 主题选择器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class title_form
{
	function __construct()
	{
		//
	}

	function config()
	{
		if($GLOBALS['app']->app_id == "admin")
		{
			$site_id = $_SESSION["admin_site_id"];
		}
		else
		{
			$site_id = $GLOBALS['app']->site["id"];
		}
		//可选主题列表
		$opt_list = $GLOBALS['app']->model("project")->get_all_project($site_id,"p.module>0");
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
		$idlist = $rs["optlist_id"];
		if(!$idlist || !is_array($idlist))
		{
			return "未指定项目，请配置";
		}
		$project_id = implode(",",$idlist);
		//取得项目信息
		$project_list = $GLOBALS['app']->model("project")->title_list($project_id);
		if($project_list)
		{
			$open_title = implode(" / ",$project_list) ." - 主题列表";
		}
		else
		{
			$open_title = "主题资源";
		}
		$condition = " l.project_id IN(".$project_id.") ";
		$total = $GLOBALS['app']->model("list")->get_all_total($condition);
		$file = $GLOBALS['app']->dir_phpok."form/html/title_form_admin.html";
		if($rs["is_multiple"])
		{
			$content = $rs["content"] ? explode(",",$rs["content"]) : array();
			$rs["content"] = $content;
		}
		$GLOBALS['app']->assign("_project_id_btn",$project_id);
		$GLOBALS['app']->assign("_rs",$rs);
		$GLOBALS['app']->assign("_open_title",$open_title);
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("_project_id_btn");
		$GLOBALS['app']->unassign("_rs");
		$GLOBALS['app']->unassign("_open_title");
		return $content;
	}
}
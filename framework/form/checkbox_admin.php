<?php
/***********************************************************
	Filename: {phpok}/form/checkbox_admin.php
	Note	: 复选框
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class checkbox_form
{
	public function __construct()
	{
		//
	}

	public function config()
	{
		$opt_list = $GLOBALS['app']->model('opt')->group_all();
		$GLOBALS['app']->assign("opt_list",$opt_list);
		$site_id = $_SESSION['admin_site_id'];
		$rslist = $GLOBALS['app']->model('project')->get_all_project($site_id);
		if($rslist)
		{
			$p_list = $m_list = array();
			foreach($rslist AS $key=>$value)
			{
				if(!$value["parent_id"])
				{
					$p_list[] = $value;
				}
				if($value["module"])
				{
					$m_list[] = $value;
				}
			}
			if($p_list && count($p_list)>0) $GLOBALS['app']->assign("project_list",$p_list);
			if($m_list && count($m_list)>0) $GLOBALS['app']->assign("title_list",$m_list);
		}
		$catelist = $GLOBALS['app']->model('cate')->root_catelist($site_id);
		$GLOBALS['app']->assign("catelist",$catelist);
		$html = $GLOBALS['app']->dir_phpok."form/html/checkbox_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	// 复选框
	function format($rs)
	{
		if(!$rs["option_list"]) $rs['option_list'] = 'default:0';
		$opt_list = explode(":",$rs["option_list"]);
		$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
		//如果内容为空，则返回空信息
		if(!$rslist) return false;
		if($rs['content'])
		{
			if(is_string($rs['content']))
			{
				$rs['content'] = unserialize($rs['content']);
			}
			elseif($rs['content']['info'])
			{
				$tmp = "";
				foreach($rs['content']['info'] AS $key=>$value)
				{
					$tmp[] = $value['val'];
				}
				$rs['content'] = $tmp;
			}			
		}
		//返回HTML内容
		$html = '<ul class="ext_checkbox clearfix">';
		foreach($rslist AS $key=>$value)
		{
			$html .= '<li><label>';
			$html .= '<input type="checkbox" name="'.$rs['identifier'].'[]" value="'.$value['val'].'"';
			if($value && $rs['content'] && in_array($value['val'],$rs['content']))
			{
				$html .= ' checked';
			}
			$html .= '>'.$value['title'];
			$html .= '</label></li>';
		}
		$html .= '</ul><div class="clear"></div>';
		return $html;
	}
}
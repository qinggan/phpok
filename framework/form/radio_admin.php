<?php
/***********************************************************
	Filename: {phpok}/form/radio_admin.php
	Note	: 单选框
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class radio_form
{
	function __construct()
	{
		//
	}
	
	function config()
	{
		$opt_list = $GLOBALS['app']->model('opt')->group_all();
		$GLOBALS['app']->assign("opt_list",$opt_list);
		if($GLOBALS['app']->app_id == "admin")
		{
			$site_id = $_SESSION["admin_site_id"];
		}
		else
		{
			$site_id = $GLOBALS['app']->site["id"];
		}
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
		//绑定根分类
		$catelist = $GLOBALS['app']->model('cate')->root_catelist($site_id);
		$GLOBALS['app']->assign("catelist",$catelist);
		$html = $GLOBALS['app']->dir_phpok."form/html/radio_admin.html";
		$GLOBALS['app']->view($html,"abs-file",false);
	}

	function format($rs)
	{
		//echo '<pre>';
		//print_r($rs);
		//未指定组时，自动附一个值
		if(!$rs["option_list"]) $rs['option_list'] = 'default:0';
		$opt_list = explode(":",$rs["option_list"]);
		$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
		//如果内容为空，则返回空信息
		if(!$rslist) return false;
		//存在内容且为数组时，绑定字串
		if($rs["content"] && is_array($rs['content']))
		{
			$rs['content'] = $rs['content']['val'];
		}
		$html = '<table cellpadding="0" cellspacing="0" class="inp inp_radio inp_radio_'.$rs['identifier'].'">';
		if($rs['put_order'])
		{
			foreach($rslist as $key=>$value)
			{
				$html .= '<tr>';
				$html .= '<td><lable><input type="radio" name="'.$rs['identifier'].'" value="'.$value['val'].'"';
				if($rs['content'] == $value['val'])
				{
					$html .= ' checked';
				}
				$html .= ' /> '.$value['title'].'</label></td>';
				$html .= '</tr>';
			}
		}
		else
		{
			$html .= '<tr>';
			foreach($rslist as $key=>$value)
			{
				$html .= '<td><lable><input type="radio" name="'.$rs['identifier'].'" value="'.$value['val'].'"';
				if($rs['content'] == $value['val'])
				{
					$html .= ' checked';
				}
				$html .= ' /> '.$value['title'].'</label></td>';
			}
			$html .= '</tr>';
		}
		$html .= '</table>';
		return $html;
	}
}
<?php
/***********************************************************
	Filename: {phpok}/api/opt_control.php
	Note	: OPT选项功能前后台数据读取
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月21日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	//获取
	function index_f()
	{
		$this->model("opt");
		$val = $this->get("val");
		$group_id = $this->get("group_id",'int');
		if(!$group_id)
		{
			exit("操作异常，没有指定选项组");
		}
		$identifier = $this->get("identifier");
		if(!$identifier)
		{
			exit("未定义变量");
		}
		$rslist = $this->model('opt')->opt_all("group_id=".$group_id);
		if(!$rslist)
		{
			exit("没有内容选项");
		}
		function ajax_admin_opt_tmp_list(&$tmp_array,$list,$pid)
		{
			if($pid)
			{
				$tmp_all = $list[$pid];
				$tmp_array[] = $tmp_all["val"];
				ajax_admin_opt_tmp_list($tmp_array,$list,$tmp_all["parent_id"]);
			}
		}

		if($val)
		{
			$list = explode("|",$val);
			$val_list = array();
			$group_list = array();
			$new_list = array();
			foreach($rslist AS $key=>$value)
			{
				$val_list[$value["val"]] = $value;
				$group_list[$value["parent_id"]][$value["id"]] = $value;
				$new_list[$value["id"]] = $value;
			}
			//如果存在此值，需要逆向推导
			if($val_list[$val] && $val_list[$val]["parent_id"])
			{
				$tmp_array = array($val);
				ajax_admin_opt_tmp_list($tmp_array,$new_list,$val_list[$val]["parent_id"]);
				krsort($tmp_array);
				$list = $tmp_array;
			}
			$mylist = array();
			foreach($list AS $key=>$value)
			{
				$tmp_val = $val_list[$value];
				if($tmp_val)
				{
					$mylist[$key] = $group_list[$tmp_val["parent_id"]];
				}
			}
			# 读取最后一个值
			$end_parent = end($list);
			if($val_list[$end_parent])
			{
				$pid = $val_list[$end_parent]["id"];
				if($group_list[$pid])
				{
					$mylist[] = $group_list[$pid];
				};
			}
		}
		else
		{
			$mylist = array();
			foreach($rslist AS $key=>$value)
			{
				if(!$value["parent_id"])
				{
					$mylist[0][] = $value;
				}
			}
			$list = array();
		}
		$total = count($mylist);

		$html  = '<table cellpadding="0" cellspacing="0" style="width:auto;height:auto;border:0" border="0">';
		$html .= '<tr>';
		foreach($mylist AS $key=>$value)
		{
			$html .= '<td>';
			$html .= '<select onchange="opt_'.$identifier.'_onchange(this.value)"';
			if( ($key+1) == $total )
			{
				$html .= ' name="'.$identifier.'" id="'.$identifier.'"';
			}
			$html .= '>';
			$str = "";
			for($i=0;$i<$key;$i++)
			{
				if($str)
				{
					$str .= "|";
				}
				$str .= $list[$i];
			}
			if( ($key+1) == $total )
			{
				$html .= '<option value="'.$str.'">请选择…</option>';
			}
			$n_str = $str ? $str."|" : "";
			foreach($value AS $k=>$v)
			{
				$html .= '<option value="'.$n_str.$v["val"].'"';
				if($v["val"] == $list[$key])
				{
					$html .= ' selected';
				}
				$html .= '>'.$v["title"]."</option>";
			}
			$html .= '</select>';
			$html .= '</td>';
		}
		$html .= '</tr><table>';
		exit($html);
	}
}
?>
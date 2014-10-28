<?php
/***********************************************************
	Filename: {phpok}model/content.php
	Note	: 内容
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年9月24日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//取得单个主题信息
	function get_one($id)
	{
		$sql = "SELECT l.*,id.phpok identifier FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."id id ON(l.id=id.id AND id.type_id='content' AND l.site_id=id.site_id) ";
		$sql.= "WHERE l.id='".$id."' AND l.status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$extlist = $this->ext_list($rs["module_id"],$id);
		if($extlist && $extlist[$id])
		{
			$rs = array_merge($extlist[$id],$rs);
		}
		return $rs;
	}

	//通过主题ID获取对应的模块ID
	function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs["module_id"];
	}

	//取得子主题列表
	function get_sub($tid,$orderby="")
	{
		if(!$tid) return false;
		$mid = $this->get_mid($tid);
		if(!$mid) return false;
		if(!$orderby) $orderby = "l.dateline DESC,l.id DESC";
		$sql = "SELECT l.*,id.phpok identifier FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."id id ON(l.id=id.id AND id.type_id='content' AND l.site_id=id.site_id) ";
		$sql.= "WHERE l.parent_id='".$tid."' AND l.status=1 ORDER BY ".$orderby;
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		//获取扩展扩展数据并格式化
		$idlist = array_keys($rslist);
		$ids = implode(",",$idlist);
		$extlist = $this->ext_list($mid,$ids);
		if($extlist)
		{
			//合并扩展数据
			foreach($rslist AS $key=>$value)
			{
				if($extlist[$key])
				{
					$rslist[$key] = array_merge($extlist[$key],$value);
				}
			}
		}
		return $rslist;
	}

	//获取扩展字段并格式化内容
	function ext_list($mid,$ids)
	{
		if(!$mid || !$ids) return false;
		$flist = $GLOBALS['app']->model("module")->fields_all($mid);
		if(!$flist) return false;
		//取得扩展内容
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		foreach($rslist AS $key=>$value)
		{
			foreach($flist AS $k=>$v)
			{
				if($value[$v["identifier"]])
				{
					$v["content"] = $value[$v["identifier"]];
					$value[$v["identifier"]] = $GLOBALS['app']->lib('ext')->content_format($v);
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

}
?>
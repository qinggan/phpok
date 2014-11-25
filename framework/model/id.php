<?php
/***********************************************************
	Filename: {phpok}models/id.php
	Note	: ID管理工具
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 13:23
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class id_model extends phpok_model
{
	var $site_id = 0;
	function __construct()
	{
		parent::model();
	}

	function get_ctrl($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs) return 'project';
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs) return 'content';
		return false;
	}

	//检测标识ID是否被使用了
	//identifier：字符串
	//site_id，站点ID，整数
	function check_id($identifier,$site_id=0,$id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		//在项目中检测
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs) return true;
		//在分类中检测
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs) return true;
		//在内容里检测
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs) return true;
		return false;
	}

	function project_id($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}
}
?>
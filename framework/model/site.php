<?php
/***********************************************************
	Filename: phpok/model/site.php
	Note	: 网站信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-17 15:15
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class site_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	# 取得网站信息
	function get_one($id)
	{
		$sql  = " SELECT s.*,d.domain FROM ".$this->db->prefix."site s ";
		$sql .= " LEFT JOIN ".$this->db->prefix."site_domain d ON(s.domain_id=d.id) ";
		$sql .= " WHERE s.id='".$id."'";
		return $this->db->get_one($sql);
	}

	# 取得默认网站信息
	function get_one_default()
	{
		$sql = "SELECT id FROM ".$this->db->prefix."site WHERE is_default=1 ";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["id"])
		{
			return false;
		}
		return $this->get_one($rs["id"]);
	}

	# 根据域名取网站信息
	function get_one_from_domain($domain)
	{
		if(!$domain) return false;
		$sql = "SELECT site_id FROM ".$this->db->prefix."site_domain WHERE domain='".$domain."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["site_id"])
		{
			return false;
		}
		return $this->get_one($rs["site_id"]);
	}

	# 取得全部网站
	function get_all_site()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site ";
		return $this->db->get_all($sql);
	}

	# 取得域名列表
	function domain_list($site_id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain ";
		if($site_id)
		{
			$sql .= "WHERE site_id='".$site_id."'";
		}
		return $this->db->get_all($sql);
	}

	# 检查域名是否存在
	function domain_check($domain)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE domain='".$domain."'";
		return $this->db->get_one($sql);
	}

	# 取得域名信息
	function domain_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	# 取得扩展全局字段
	function all_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	# 取得全部扩展字段内容
	function all_ext($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all_ext WHERE all_id='".$id."'";
		return $this->db->get_all($sql,"fields_id");
	}

	function all_check($identifier,$site_id=0,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE identifier='".$identifier."' AND site_id='".$site_id."'";
		if($id)
		{
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	

	# 读取扩展配置
	function all_list($site_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE site_id='".$site_id."'";
		return $this->db->get_all($sql);
	}

	# 取得网站下的所有扩展配置
	function site_config($id)
	{
		if(!$id) return false;
		$sql = "SElECT * FROM ".$this->db->prefix."all WHERE site_id='".$id."'";
		$list = $this->db->get_all($sql);
		if(!$list)
		{
			return false;
		}
		$tmp = $tmp2 = array();
		foreach($list AS $key=>$value)
		{
			$tmp[$value["identifier"]] = "all-".$value["id"];
			$tmp2["all-".$value["id"]] = $value["identifier"];
		}
		$condition = implode("','",$tmp);
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.module FROM ".$this->db->prefix."ext ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		$sql.= "WHERE ext.module IN('".$condition."') ORDER BY ext.taxis ASC,ext.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$info = false;
		foreach($rslist AS $key=>$value)
		{
			if(!$tmp2[$value["module"]]) continue;
			$info[$tmp2[$value["module"]]][$value["identifier"]] = content_format($value);
		}
		return $info;
	}
}
?>
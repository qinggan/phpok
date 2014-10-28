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
class site_model extends phpok_model
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

	//设置站点为默认
	function set_default($id)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."site SET is_default=0";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."site SET is_default=1 WHERE id=".$id;
		$this->db->query($sql);
		return true;
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

	# 存储网站信息
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"site",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"site");
		}
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

	# 更新网站域名
	function domain_update($domain,$id)
	{
		if(!$domain || !$id) return false;
		phpok_delete_cache("site");
		$sql = "UPDATE ".$this->db->prefix."site_domain SET domain='".$domain."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	# 添加新的域名
	function domain_add($domain,$site_id)
	{
		if(!$domain || !$site_id) return false;
		phpok_delete_cache("site");
		$sql = "INSERT INTO ".$this->db->prefix."site_domain(site_id,domain) VALUES('".$site_id."','".$domain."')";
		return $this->db->insert($sql);
	}

	# 取得域名信息
	function domain_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	# 删除域名
	function domain_delete($id)
	{
		phpok_delete_cache("site");
		$sql = "DELETE FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->query($sql);
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

	# 存储扩展配置
	function all_save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		phpok_delete_cache("site");
		if($id)
		{
			return $this->db->update_array($data,"all",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"all");
		}
	}

	# 读取扩展配置
	function all_list($site_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE site_id='".$site_id."'";
		return $this->db->get_all($sql);
	}


	# 删除扩展
	function ext_delete($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."all WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE module='all-".$id."'";
		$rslist = $this->db->get_all($sql,"id");
		if($rslist)
		{
			$id_array = array_keys($rslist);
			$ids = implode(",",$id_array);
			$sql = "DELETE FROM ".$this->db->prefix."ext_c WHERE id IN(".$ids.")";
			$this->db->query($sql);
			$sql = "DELETE FROM ".$this->db->prefix."ext WHERE id IN(".$ids.")";
			$this->db->query($sql);
		}
		phpok_delete_cache("site");
		return true;
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

	function site_delete($id)
	{
		//读取所有的表
		$rslist = $this->db->list_tables();
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$flist = $this->db->list_fields($value);
				if($flist && in_array("site_id",$flist))
				{
					$sql = "DELETE FROM ".$value." WHERE site_id='".$id."'";
					$this->db->query($sql);
				}
			}
		}
		//删除主表信息
		$sql = "DELETE FROM ".$this->db->prefix."site WHERE id='".$id."'";
		$this->db->query($sql);
		//清空缓存操作
		phpok_delete_cache('site,call,phpok');
		return true;
	}

}
?>
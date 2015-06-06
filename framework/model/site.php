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
	private $sitelist = false;
	private $domainlist = false;
	public function __construct()
	{
		parent::model();
		$this->_site_all();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function get_one($id)
	{
		if(!$this->sitelist){
			return false;
		}
		if(!$this->sitelist[$id]){
			return false;
		}
		$rs = $this->sitelist[$id];
		$domain_id = $rs['domain_id'];
		if($rs['_domain'] && $rs['_domain'][$domain_id]){
			$rs['domain'] = $rs['_domain'][$domain_id];
		}
		return $rs;
	}

	public function get_one_default()
	{
		if(!$this->sitelist){
			return false;
		}
		$rs = false;
		foreach($this->sitelist as $key=>$value){
			if($value['is_default']){
				$rs = $value;
			}
		}
		$domain_id = $rs['domain_id'];
		if($rs['_domain'] && $rs['_domain'][$domain_id]){
			$rs['domain'] = $rs['_domain'][$domain_id];
		}
		return $rs;
	}

	public function get_one_from_domain($domain='')
	{
		if(!$domain || !$this->domainlist){
			return false;
		}
		$site_id = false;
		foreach($this->domainlist as $key=>$value){
			if($value['domain'] = $domain){
				$site_id = $value['site_id'];
				break;
			}
		}
		if(!$site_id){
			return false;
		}
		return $this->get_one($site_id);
	}

	public function get_all_site()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site ";
		return $this->db->get_all($sql);
	}

	private function _site_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain";
		$dlist = $this->db->get_all($sql);
		if(!$dlist){
			return $rslist;
		}
		$this->domainlist = $dlist;
		foreach($dlist as $key=>$value){
			$rslist[$value['site_id']]['_domain'][$value['id']] = $value['domain'];
		}
		$this->sitelist = $rslist;
		return $rslist;
	}

	public function domain_list($site_id=0)
	{
		if(!$this->domainlist){
			return false;
		}
		if(!$site_id){
			return $this->domainlist;
		}
		$rslist = false;
		foreach($this->domainlist as $key=>$value){
			if($value['site_id'] == $site_id){
				$rslist[] = $value;
			}
		}
		return $rslist;
	}

	public function domain_check($domain)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE domain='".$domain."'";
		return $this->db->get_one($sql);
	}

	public function domain_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function all_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function all_ext($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all_ext WHERE all_id='".$id."'";
		return $this->db->get_all($sql,"fields_id");
	}

	public function all_check($identifier,$site_id=0,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE identifier='".$identifier."' AND site_id='".$site_id."'";
		if($id){
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function all_list($site_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."all WHERE site_id='".$site_id."'";
		return $this->db->get_all($sql);
	}

	public function site_config($id)
	{
		if(!$id){
			return false;
		}
		$sql = "SElECT * FROM ".$this->db->prefix."all WHERE site_id='".$id."'";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$tmp = $tmp2 = array();
		foreach($list AS $key=>$value){
			$tmp[$value["identifier"]] = "all-".$value["id"];
			$tmp2["all-".$value["id"]] = $value["identifier"];
		}
		$condition = implode("','",$tmp);
		$sql = "SELECT ext.id,ext.identifier,ext.form_type,extc.content,ext.ext,ext.module FROM ".$this->db->prefix."ext ext ";
		$sql.= "JOIN ".$this->db->prefix."extc extc ON(ext.id=extc.id) ";
		$sql.= "WHERE ext.module LIKE 'all-%' ORDER BY ext.taxis ASC,ext.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$info = false;
		foreach($rslist AS $key=>$value){
			if(!$tmp2[$value["module"]]){
				continue;
			}
			$info[$tmp2[$value["module"]]][$value["identifier"]] = $this->lib('form')->show($value);
		}
		return $info;
	}
}
?>
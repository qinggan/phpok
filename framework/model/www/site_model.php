<?php
/*****************************************************************************************
	文件： {phpok}/model/www/site_model.php
	备注： 站点信息
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月19日 21时41分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class site_model extends site_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site WHERE id='".$id."'";
		$cache_id = $this->cache->id($sql);
		$rs = $this->cache->get($cache_id);
		if($rs){
			return $rs;
		}
		$this->db->cache_set($cache_id);
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$rs['_domain'] = $this->domain_list($id,'id');
		if($rs['_domain']){
			foreach($rs['_domain'] as $key=>$value){
				if($value['is_mobile']){
					$rs['_mobile'] = $value;
				}
				if($value['id'] == $rs['domain_id']){
					$rs['domain'] = $value['domain'];
				}
			}
		}
		$this->cache->save($cache_id,$rs);
		return $rs;
	}

	public function domain_list($site_id=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."site_domain ";
		if($site_id){
			$sql .= "WHERE site_id='".$site_id."'";
		}
		$cache_id = $this->cache->id($sql);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			return $rslist;
		}
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		$this->cache->save($cache_id,$rslist);
		return $rslist;
	}


	public function get_one_from_domain($domain='')
	{
		$sql = "SELECT site_id FROM ".$this->db->prefix."site_domain WHERE domain='".$domain."'";
		$cache_id = $this->cache->id($sql);
		$tmp = $this->cache->get($cache_id);
		if(!$tmp){
			$this->db->cache_set($cache_id);
			$tmp = $this->db->get_one($sql);
			if(!$tmp){
				return false;
			}
			$this->cache->save($cache_id,$tmp);
		}
		return $this->get_one($tmp['site_id']);
	}


	public function site_config($id,$status=1)
	{
		if(!$id){
			return false;
		}
		$sql = "SElECT * FROM ".$this->db->prefix."all WHERE site_id='".$id."' AND status='1'";
		$cache_id = $this->cache->id($sql);
		$list = $this->cache->get($cache_id);
		if($list){
			return $list;
		}
		$this->db->cache_set($cache_id);
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
		if($info){
			$this->cache->save($cache_id,$info);
		}
		return $info;
	}

}

?>
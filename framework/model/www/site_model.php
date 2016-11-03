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
		if(file_exists($this->dir_root.'data/xml/site_'.$id.'.xml')){
			$tmp = $this->lib('xml')->read($this->dir_root.'data/xml/site_'.$id.'.xml');
			if($tmp){
				$rs = array_merge($tmp,$rs);
			}
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

}

?>
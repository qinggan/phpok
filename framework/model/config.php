<?php
/**
 * 配置信息保存
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2022年1月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class config_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function save($data='',$site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		if(!$data || !is_array($data)){
			return false;
		}
		$keys = array_keys($data);
		$sql  = " DELETE FROM ".$this->db->prefix."config WHERE site_id='".$site_id."'";
		$sql .= " AND identifier NOT IN('".implode("','",$keys)."')";
		$this->db->query($sql);
		$sql  = " SELECT * FROM ".$this->db->prefix."config WHERE site_id='".$site_id."'";
		$sql .= " AND identifier IN('".implode("','",$keys)."')";
		$tmplist = $this->db->get_all($sql);
		$e = array();
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "UPDATE ".$this->db->prefix."config SET content='".$data[$value['identifier']]."' WHERE id='".$value['id']."'";
				$e[] = $value['identifier'];
				$this->db->query($sql);
			}
		}
		if($e && count($e)>0){
			$d = array_diff($keys,$e);
		}else{
			$d = $keys;
		}
		if($d){
			foreach($d as $key=>$value){
				$tmp = array('site_id'=>$site_id);
				$tmp['identifier'] = $value;
				$tmp['content'] = $data[$value];
				$this->db->insert($tmp,'config');
			}
		}
		return true;
	}

	public function get_one($id,$site_id=0)
	{
		if(!$id){
			return false;
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql  = " SELECT * FROM ".$this->db->prefix."config WHERE site_id='".$site_id."' ";
		$sql .= " AND identifier='".$id."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $tmp['content'];
	}

	public function get_all($site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql  = " SELECT * FROM ".$this->db->prefix."config WHERE site_id='".$site_id."' ";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rs = array();
		foreach($tmplist as $key=>$value){
			$rs[$value['identifier']] = $value['content'];
		}
		return $rs;
	}

}
<?php
/**
 * 搜索关键字管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2020年7月21日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class search_model extends search_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_all($condition="",$offset=0,$psize=30,$orderby='')
	{
		$sql  = "SELECT * FROM ".$this->db->prefix."search ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if($orderby){
			$sql .= " ORDER BY ".$orderby;
		}
		if($psize){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		return $this->db->get_all($sql);
	}

	public function get_count($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."search ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function update($data,$id=0)
	{
		if(!$id){
			return $this->db->insert($data,'search');
		}
		return $this->db->update($data,'search',array('id'=>$id));
	}

	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$list = explode(",",$id);
		$tmp = array();
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$tmp[] = $value;
		}
		if(!$tmp || count($tmp)<1){
			return false;
		}
		$tmp = array_unique($tmp);
		$sql = "DELETE FROM ".$this->db->prefix."search WHERE id IN(".implode(",",$tmp).")";
		$this->db->query($sql);
	}
}

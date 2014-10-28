<?php
/***********************************************************
	Filename: {phpok}/model/call.php
	Note	: 数据调用中心涉及到的SQL操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-18 02:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_model extends phpok_model
{
	//site_id，为网站ID
	var $site_id = 0;
	var $psize = 20;
	function __construct()
	{
		parent::model();
	}
	
	function call_model()
	{
		$this->__construct();
	}

	function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}

	function psize($psize=20)
	{
		$this->psize = $psize;
	}
	
	//通过ID取得数据（此操作用于后台）
	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_rs($id,$site_id)
	{
		if(!$id || !$site_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$id."' AND site_id='".$site_id."'";
		return $this->db->get_one($sql);
	}

	function get_list($condition="",$pageid=0)
	{
		$offset = $pageid>0 ? ($pageid-1)*$this->psize : 0;
		//获取调用数据的列表
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$this->psize;
		return $this->db->get_all($sql);
	}

	function get_all($site_id=0,$status=0)
	{
		if(!$site_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id='".$site_id."'";
		if($status) $sql.= " AND status=1";
		return $this->db->get_all($sql,"identifier");
	}

	function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."phpok WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	function chk_identifier($val)
	{
		return $this->get_one_sign($val);
	}

	//通过标识串取得调用的配置数据
	function get_one_sign($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$val."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	//检测标识串是否存在
	function chksign($val)
	{
		return $this->get_one_sign($val);
	}

	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		//删除缓存文件
		phpok_delete_cache("call");
		if($id)
		{
			return $this->db->update_array($data,"phpok",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"phpok");
		}
	}

	function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."phpok SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function del($id)
	{
		phpok_delete_cache("call");
		$sql = "DELETE FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->query($sql);
	}

}
?>
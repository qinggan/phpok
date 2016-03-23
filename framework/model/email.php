<?php
/***********************************************************
	Filename: {phpok}/model/email.php
	Note	: 邮件内容管理器
	Version : 3.0
	Author  : qinggan
	Update  : 2013年06月30日 23时42分
***********************************************************/
class email_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}


	function get_one($id)
	{
		if(!$id)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."email WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_list($condition="",$offset=0,$psize=20)
	{
		$sql = " SELECT * FROM ".$this->db->prefix."email ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function simple_list($siteid=0)
	{
		$condition = $siteid ? "site_id IN(0,".$siteid.")" : "site_id=0";
		$sql = "SELECT id,identifier,title FROM ".$this->db->prefix."email WHERE ".$condition;
		return $this->db->get_all($sql);
	}

	//取得总数量
	function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."email ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	//存储邮件内容信息
	function save($data,$id=0)
	{
		if($id)
		{
			$this->db->update_array($data,"email",array("id"=>$id));
			return true;
		}
		else
		{
			$insert_id = $this->db->insert_array($data,"email");
			return $insert_id;
		}
	}

	//删除邮件内容
	function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."email WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	function get_identifier($identifier,$site_id=0,$id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."email WHERE identifier='".$identifier."' AND site_id='".$site_id."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		$sql .= " ORDER BY id DESC LIMIT 1";
		return $this->db->get_one($sql);
	}

	public function tpl($code,$site_id=0)
	{
		return $this->get_identifier($code,$site_id);
	}
}
?>
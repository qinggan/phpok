<?php
/*****************************************************************************************
	文件： {phpok}/model/www/tag_model.php
	备注： Tag标签前端调用
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月22日 01时04分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends tag_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function get_one($id,$field='id',$site_id=0)
	{
		if($site_id)
		{
			$this->site_id($site_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE ".$field."='".$id."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	public function get_total($tag_id)
	{
		$sql = "SELECT count(s.title_id) FROM ".$this->db->prefix."tag_stat s ";
		$sql.= "JOIN ".$this->db->prefix."list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='".$tag_id."'";
		return $this->db->count($sql);
	}

	public function id_list($tag_id,$offset=0,$psize=30)
	{
		$sql = "SELECT title_id as id FROM ".$this->db->prefix."tag_stat WHERE tag_id='".$tag_id."' ";
		//$sql.= "JOIN ".$this->db->prefix."list l ON(s.title_id=l.id) WHERE l.status=1 AND s.tag_id='".$tag_id."' ";
		$sql.= " ORDER BY title_id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	public function add_hits($tag_id)
	{
		$sql = "UPDATE ".$this->db->prefix."tag SET hits=hits+1 WHERE id='".$tag_id."'";
		return $this->db->query($sql);
	}
}

?>
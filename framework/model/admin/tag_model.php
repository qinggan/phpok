<?php
/*****************************************************************************************
	文件： {phpok}/model/tag.php
	备注： Tag标签在后台的调用
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年5月25日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends phpok_model
{
	private $popedom;
	private $site_id = 0;
	function __construct()
	{
		parent::model();
	}

	public function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}

	public function get_list($condition="",$offset=0,$psize=30,$site_id=0)
	{
		if($site_id)
		{
			$this->site_id($site_id);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist)
		{
			return false;
		}
		$ids = array_keys($rslist);
		$sql = "SELECT count(title_id) as count,tag_id FROM ".$this->db->prefix."list_tag WHERE tag_id IN(".implode(",",$ids).") GROUP BY tag_id";
		$count_list = $this->db->get_all($sql,'tag_id');
		if($count_list)
		{
			foreach($rslist as $key=>$value)
			{
				$rslist['count'] = $count_list[$key]['count'] ? $count_list[$key]['count'] : 0;
			}
		}
		return $rslist;
	}

	public function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."tag WHERE ".$condition;
		return $this->db->count($sql);
	}

	public function chk_title($title,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."tag WHERE title='".$title."'";
		if($id)
		{
			$sql .= " AND id!='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	public function save($data,$id=0)
	{
		if($id)
		{
			return $this->db->update_array($data,'tag',array('id'=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"tag");
		}
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."tag WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}

?>
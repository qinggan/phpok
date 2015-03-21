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
class tag_model extends tag_model_base
{
	private $popedom;
	function __construct()
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
		$sql.= " ORDER BY is_global DESC,id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist)
		{
			return false;
		}
		$ids = array_keys($rslist);
		$sql = "SELECT count(title_id) as count,tag_id FROM ".$this->db->prefix."tag_stat WHERE tag_id IN(".implode(",",$ids).") GROUP BY tag_id";
		$count_list = $this->db->get_all($sql,'tag_id');
		if($count_list)
		{
			foreach($rslist as $key=>$value)
			{
				$rslist[$key]['count'] = $count_list[$key]['count'] ? $count_list[$key]['count'] : 0;
			}
		}
		return $rslist;
	}

	public function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."tag WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		return $this->db->count($sql);
	}

	public function chk_title($title,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."tag WHERE title='".$title."' AND site_id='".$this->site_id."'";
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
		//删除记录
		$this->stat_delete($id,'tag_id');
		$sql = "DELETE FROM ".$this->db->prefix."tag WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function stat_delete($id,$field='tag_id')
	{
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE ".$field."='".$id."'";
		return $this->db->query($sql);
	}

	//批量更新Tag及Tag里的统计
	public function update_tag($data,$list_id,$site_id=0)
	{
		if(!$data || !$list_id || (is_string($data) && !trim($data))) return false;
		$data = $this->string_to_array($data);
		if($site_id) $this->site_id($site_id);
		//删除主题的Tag Id
		$this->stat_delete($list_id,'title_id');
		foreach($data as $key=>$value)
		{
			$value = trim($value);
			if(!$value) continue;
			$chk_rs = $this->chk_title($value);
			if($chk_rs)
			{
				$id = $chk_rs['id'];
			}
			else
			{
				$array = array('site_id'=>$this->site_id,'title'=>$value,'url'=>'','target'=>'0');
				$id = $this->save($array);
			}
			if($id)
			{
				$this->stat_save($id,$list_id);
			}
		}
		return true;
	}

	public function string_to_array($string)
	{
		if(!$string || !trim($string))
		{
			return false;
		}
		if(is_array($string))
		{
			return $string;
		}
		$str_list = array("　","，",",","｜","|","、","/","\\","／","＼","+","＋","-","－","_","＿","—","&nbsp;","&lt;","&gt;",">","<");
		$string = str_replace($str_list," ",$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);
		return explode(" ",$string);
	}

	public function get_tags($id)
	{
		$sql = "SELECT t.title FROM ".$this->db->prefix."tag_stat s ";
		$sql.= " JOIN ".$this->db->prefix."tag t ON(s.tag_id=t.id) ";
		$sql.= " WHERE s.title_id='".$id."'";
		$rs = $this->db->get_all($sql);
		if(!$rs)
		{
			return false;
		}
		$list = array();
		foreach($rs as $key=>$value)
		{
			$list[] = $value['title'];
		}
		return implode(" ",$list);
	}

}

?>
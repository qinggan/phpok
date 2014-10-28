<?php
/***********************************************************
	Filename: {phpok}/model/tag.php
	Note	: TAG管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-16 07:53
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function save($tag,$id=0,$py="")
	{
		$tag_id = $this->get_tag_id($tag,$py);
		if(!$tag_id)
		{
			$tag_id = $this->save_tag($tag,$py);
			if(!$tag_id) return false;
		}
		//统计原来的关键字
		if($id)
		{
			$sql = "REPLACE INTO ".$this->db->prefix."tag_list(id,tid) VALUES('".$tag_id."','".$id."')";
			//file_put_contents(md5($sql).".sql",$sql);
			$this->db->query($sql);
			$this->reduce($tag_id);
		}
		return true;
	}

	function tag_list_del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."tag_list WHERE tid='".$id."'";
		return $this->db->query($id);
	}

	function reduce($id)
	{
		$sql = "UPDATE ".$this->db->prefix."tag SET total=(SELECT count(*) FROM ".$this->db->prefix."tag_list WHERE id='".$id."') WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function tag_list($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."tag_list WHERE tid='".$id."'";
		return $this->db->get_all($sql);
	}


	function get_tag_id($tag,$py="")
	{
		$sql = "SELECT id FROM ".$this->db->prefix."tag WHERE title ='".$tag."' ";
		if($py)
		{
			$sql.= " AND pingyin='".$py."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs["id"];
	}

	function save_tag($title,$py="")
	{
		if(!$title) return false;
		$data = array("title"=>$title,"pingyin"=>$py);
		return $this->db->insert_array($data,"tag");
	}

	//更新主题的Tag信息
	function update_tag($id,$tag="")
	{
		$this->lib("pingyin")->path = $this->dir_phpok."libs/pingyin.qdb";
 		$py = iconv("UTF-8","GBK",$tag);
	 	$py = $this->lib("pingyin")->ChineseToPinyin($py);
	 	$py = strtolower($py);
	 	$pylist = explode(" ",$py);
 		$taglist = $this->tag_list($id);
 		if($taglist)
 		{
	 		$this->tag_list_del($id);
	 		foreach($taglist AS $key=>$value)
	 		{
		 		$this->reduce($value["id"]);
	 		}
 		}
 		if($tag)
 		{
	 		$taglist = explode(" ",$tag);
	 		foreach($taglist AS $key=>$value)
	 		{
		 		$tmp = $this->lib('pingyin')->ChineseToPinyin($value);
				//$tmp = $pylist[$key];		 		
				$this->save($value,$id,$tmp);
	 		}
 		}
 		return true;
	}
}
?>
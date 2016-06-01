<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/gd_model.php
	备注： GD保存，删除等操作，仅限后台
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月25日 22时24分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gd_model extends gd_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"gd",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"gd");
		}
	}

	public function delete($id,$root_dir='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_ext WHERE gd_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist AS $key=>$value){
				if($value["filename"] && file_exists($root_dir.$value["filename"]) && is_file($root_dir.$value["filename"])){
					@unlink($root_dir.$value["filename"]);
				}
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE gd_id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."gd WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function update_editor($id)
	{
		$sql = "UPDATE ".$this->db->prefix."gd SET editor='0' WHERE id!='".$id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."gd SET editor='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

}

?>
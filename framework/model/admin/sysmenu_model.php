<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/sysmenu_model.php
	备注： 系统菜单管理器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月24日 10时04分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sysmenu_model extends sysmenu_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id='".$id."' AND parent_id !=0";
		return $this->db->query($sql);
	}

	public function save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"sysmenu");
		}else{
			return $this->db->update_array($data,"sysmenu",array("id"=>$id));
		}
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."sysmenu SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."sysmenu SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}

?>
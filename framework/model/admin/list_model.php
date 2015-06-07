<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/list_model.php
	备注： 主题内容管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月06日 22时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_model extends list_model_base
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

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update_sort($id,$sort=0)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET sort='".$sort."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}


	public function ext_catelist($id)
	{
		$sql = "SELECT cate_id FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[] = $value['cate_id'];
		}
		return $rslist;
	}
}

?>
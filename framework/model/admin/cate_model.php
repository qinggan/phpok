<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/cate_model.php
	备注： 分类后台操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年05月03日 09时48分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model extends cate_model_base
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
			return $this->db->update_array($data,"cate",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"cate");
		}
	}

	public function cate_next_taxis($parent_id=0)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."cate WHERE site_id='".$this->site_id."' AND parent_id='".$parent_id."'";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function get_root_id(&$rootid,$id)
	{
		$rs = $this->get_one($id);
		if($rs){
			if(!$rs['parent_id']){
				$rootid = $id;
			}else{
				$this->get_root_id($rootid,$rs['parent_id']);
			}
		}
	}
}

?>
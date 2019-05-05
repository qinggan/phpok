<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/call_model.php
	备注： 调用中心后台管理中涉及到的函数
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月12日 15时17分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_model extends call_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."phpok SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"phpok",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"phpok");
		}
	}

	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT ok.id,ok.title,ok.pid,ok.type_id,ok.identifier,ok.status,ok.cateid,ok.is_api,p.title project,c.title cate FROM ".$this->db->prefix."phpok ok LEFT JOIN ".$this->db->prefix."project p ON(ok.pid=p.id) LEFT JOIN ".$this->db->prefix."cate c ON(ok.cateid=c.id) WHERE ok.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY ok.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}
}
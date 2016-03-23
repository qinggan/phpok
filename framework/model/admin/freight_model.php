<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/freight_model.php
	备注： 运费管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月08日 07时13分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class freight_model extends freight_model_base
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
		if(!$data['site_id']){
			$data['site_id'] = $this->site_id;
		}
		if($id){
			return $this->db->update_array($data,'freight',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'freight');
		}
	}

	public function delete($id)
	{
		//删除主表
		$sql = "DELETE FROM ".$this->db->prefix."freight WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."freight_zone WHERE fid='".$id."'";
		$rslist = $this->db->get_all($sql,'id');
		if($rslist){
			$idlist = array_keys($rslist);
			$sql = "DELETE FROM ".$this->db->prefix."freight_price WHERE zid IN(".implode(",",$idlist).")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."freight_zone WHERE fid='".$id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."project SET freight_id='0' WHERE freight_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function zone_sort($id,$val=255)
	{
		$sql = "UPDATE ".$this->db->prefix."freight_zone SET taxis='".$val."' WHERE id='".$id."' ";
		return $this->db->query($sql);
	}

	public function zone_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'freight_zone',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'freight_zone');
		}
	}

	public function zone_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."freight_price WHERE zid='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."freight_zone WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function price_save($data)
	{
		return $this->db->insert_array($data,'freight_price','replace');
	}

	public function price_delete($fid=0,$unit_val=0){
		if(!$fid || !$unit_val){
			return false;
		}
		$zlist = $this->zone_all($fid,'id','id');
		if($zlist){
			$ids = implode(",",array_keys($zlist));
			$sql = "DELETE FROM ".$this->db->prefix."freight_price WHERE unit_val='".$unit_val."' AND zid IN(".$ids.")";
			$this->db->query($sql);
		}
		return true;
	}

}

?>
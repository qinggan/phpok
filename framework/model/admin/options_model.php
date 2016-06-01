<?php
/*****************************************************************************************
	文件： {phpok}/model/options_model.php
	备注： 产品属性管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月07日 13时42分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class options_model extends options_model_base
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
			return $this->db->update_array($data,'attr',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'attr');
		}
	}

	public function delete($id)
	{
		//删除主表
		$sql = "DELETE FROM ".$this->db->prefix."attr WHERE id='".$id."'";
		$this->db->query($sql);
		//删除参数表
		$sql = "DELETE FROM ".$this->db->prefix."attr_values WHERE aid='".$id."'";
		$this->db->query($sql);
		//删除已经使用的
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE aid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function save_values($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'attr_values',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'attr_values');
		}
	}

	public function delete_values($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."attr_values WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE vid='".$id."'";
		$this->db->query($sql);
		return true;
	}
}

?>
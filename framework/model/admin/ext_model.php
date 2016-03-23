<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/ext_model.php
	备注： 扩展字段管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月20日 12时42分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ext_model extends ext_model_base
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

	//添加验证
	public function check_identifier_add($identifier,$type='')
	{
		if(!$identifier || !$type){
			return false;
		}
		if(!preg_match('/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u',$identifier)){
			return false;
		}
		if(strlen($type)>5 && substr($type,0,5) == 'list_'){
			$flist = $this->db->list_fields($this->db->prefix.$type);
			if(!$flist){
				return true;
			}
		}
		if(!$flist){
			$flist = array();
		}
		$chk = array('title','phpok','identifier','status','taxis','tag','parent_id','project');
		if(in_array($identifier,$flist) || in_array($identifier,$chk)){
			return false;
		}
		return true;
	}

	public function extc_save($content,$id)
	{
		if(!$id) return false;
		$sql = "REPLACE INTO ".$this->db->prefix."extc(id,content) VALUES('".$id."','".$content."')";
		return $this->db->query($sql);
	}

	public function content_save($content,$id)
	{
		if(!$id || !$content) return false;
		$sql = "REPLACE INTO ".$this->db->prefix."extc(id,content) VALUES('".$id."','".$content."')";
		return $this->db->query($sql);
	}

	public function ext_delete($id,$module)
	{
		$sql = "DELETE FROM ".$this->db->prefix."ext WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function delete($val,$module,$type="id")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ext WHERE `".$type."`='".$val."' AND module='".$module."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."ext WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return true;
	}

	//存储表单
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id)
		{
			return $this->db->update_array($data,"ext",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"ext");
		}
	}

	//删除表单
	public function del($module)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE module='".$module."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return true;
		foreach($rslist AS $key=>$value)
		{
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value["id"]."'";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."ext WHERE module='".$module."'";
		return $this->db->query($sql);
	}

	public function get_from_identifier($identifier,$module)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ext WHERE identifier='".$identifier."' AND module='".$module."'";
		return $this->db->get_one($sql);
	}

	public function ext_next_taxis($module)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."ext WHERE module='".$module."'";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}
}

?>
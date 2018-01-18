<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/user_model.php
	备注： 会员增删改
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月13日 13时14分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class user_model extends user_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$this->db->query($sql);
		//删除相应的积分
		$sql = "DELETE FROM ".$this->db->prefix."wealth_info WHERE uid='".$id."'";
		$this->db->query($sql);
		//删除积分日志
		$sql = "DELETE FROM ".$this->db->prefix."wealth_log WHERE goal_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function set_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."user SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function create_fields($rs)
	{
		if(!$rs || !is_array($rs)){
			return false;
		}
		$idlist = $this->tbl_fields_list($this->db->prefix."user_ext");
		if($idlist && in_array($rs["identifier"],$idlist)){
			return true;
		}
		$tlist = array("varchar","int","float","date","datetime","text","longtext","blob","longblob");
		if(!in_array($rs["field_type"],$tlist)){
			return false;
		}
		$sql = "ALTER TABLE ".$this->db->prefix."user_ext ADD `".$rs["identifier"]."`";
		if($rs["field_type"] == "int"){
			$sql.= " INT ";
			$rs["content"] = intval($rs["content"]);
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}elseif($rs["field_type"] == "float"){
			$sql.= " FLOAT ";
			$rs["content"] = intval($rs["content"]);
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}elseif($rs["field_type"] == "date"){
			$sql.= " DATE NULL ";
		}elseif($rs["field_type"] == "datetime"){
			$sql.= " DATETIME NULL ";
		}elseif($rs["field_type"] == "longtext" || $rs["field_type"] == "text"){
			$sql.= " LONGTEXT NOT NULL ";
		}elseif($rs["field_type"] == "longblob" || $rs["field_type"] == "blob"){
			$sql.= " LONGBLOB NOT NULL ";
		}else{
			$sql.= " VARCHAR( 255 ) ";
			$sql.= " NOT NULL DEFAULT '".$rs["content"]."' ";
		}
		$sql.= " COMMENT  '".$rs["title"]."' ";
		return $this->db->query($sql);
	}

	public function field_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		$field = $rs["identifier"];
		$sql = "ALTER TABLE ".$this->db->prefix."user_ext DROP `".$field."`";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//后台显示
	public function get_one($id,$field='id',$ext=true,$wealth=true)
	{
		if(!$id){
			return false;
		}
		$sql = " SELECT u.*,e.* FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		$sql.= " WHERE u.".$field."='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs;
	}

	/**
	 * 会员自定义字段排序
	**/
	public function user_next_taxis()
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."user_fields WHERE taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	/**
	 * 保存扩展字段数据
	 * @参数 $data 一维数组
	 * @参数 $id 主键ID，留空或为0表示写入新的
	**/
	public function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"user_fields",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"user_fields");
		}
	}

	/**
	 * 简单通过会员ID获取会员的ID及账号
	 * @参数 $ids 会员ID，支持数据及字串
	 * @参数 $field 字段，要查询的字段
	**/
	public function simple_user_list($ids,$field='user')
	{
		if(!$ids){
			return false;
		}
		if($ids && is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT id,".$field." FROM ".$this->db->prefix."user WHERE id IN(".$ids.")";
		$tmplist = $this->db->get_all($sql,'id');
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$rslist[$value['id']] = $value[$field];
		}
		return $rslist;
	}
}
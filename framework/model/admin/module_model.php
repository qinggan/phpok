<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/module_model.php
	备注： 模块扩展
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月05日 21时03分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_model extends module_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function module_next_taxis()
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."module";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function fields_next_taxis($mid)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."'";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	//创建系统表
	public function create_tbl($id)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$sql = "CREATE TABLE ".$this->db->prefix."list_".$id." (";
		$sql.= "`id` INT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '主题ID',";
		$sql.= "`site_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '网站ID',";
		$sql.= "`project_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '项目ID',";
		$sql.= "`cate_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '主分类ID',";
		$sql.= "PRIMARY KEY (  `id` ) ,";
		$sql.= "INDEX (  `site_id` ,  `project_id` ,  `cate_id` )";
		$sql.= ") ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT = '".$rs["title"]."'";
		return $this->db->query($sql);
	}

	public function update_fields($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		if(!$rs){
			return false;
		}
		$chk_tbl = $this->chk_tbl_exists($rs['module_id']);
		if(!$chk_tbl){
			return false;
		}
		$idlist = $this->db->list_fields('list_'.$rs['module_id']);
		if(!$idlist){
			return false;
		}
		if(!in_array($rs['identifier'],$idlist)){
			return $this->create_fields($rs['module_id'],$rs);
		}
		//创建表字段，这里不加索引等功能，如果在数据量大时，可咨询PHPOK官方进行优化
		$sql = "ALTER TABLE ".$this->db->prefix."list_".$rs['module_id']." ";
		$sql.= "CHANGE `".$rs["identifier"]."` `".$rs['identifier']."`";
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

	//创建字段
	public function create_fields($id,$rs)
	{
		if(!$id || !$rs){
			return false;
		}
		$chk_tbl = $this->chk_tbl_exists($id);
		if(!$chk_tbl){
			return false;
		}
		$idlist = $this->db->list_fields('list_'.$id);
		if($idlist && in_array($rs['identifier'],$idlist)){
			return true;
		}
		//创建表字段，这里不加索引等功能，如果在数据量大时，可咨询PHPOK官方进行优化
		$sql = "ALTER TABLE ".$this->db->prefix."list_".$id." ADD `".$rs["identifier"]."`";
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

	//删除字段
	public function field_delete($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->field_one($id);
		$idlist = $this->db->list_fields('list_'.$rs['module_id']);
		if($idlist && in_array($rs['identifier'],$idlist)){
			$sql = "ALTER TABLE ".$this->db->prefix."list_".$rs["module_id"]." DROP `".$rs["identifier"]."`";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."module_fields WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//删除模块操作
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		if($this->chk_tbl_exists($id)){
			$sql = "DROP TABLE ".$this->db->prefix."list_".$id;
			$this->db->query($sql);
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				//删除主题绑定的分类
				$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$value['id']."'";
				$this->db->query($sql);
				//删除主题电商相关
				$sql = "DELETE FROM ".$this->db->prefix."list_biz WHERE id='".$value['id']."'";
				$this->db->query($sql);
				//删除主题相关属性
				$sql = "DELETE FROM ".$this->db->prefix."list_attr WHERE tid='".$value['id']."'";
				$this->db->query($sql);
			}
			$sql = "DELETE FROM ".$this->db->prefix."list WHERE module_id='".$id."'";
			$this->db->query($sql);
		}
		//更新项目信息
		$sql = "UPDATE ".$this->db->prefix."project SET module='0' WHERE module='".$id."'";
		$this->db->query($sql);
		//删除扩展字段
		$sql = "DELETE FROM ".$this->db->prefix."module_fields WHERE module_id='".$id."'";
		$this->db->query($sql);
		//删除记录
		$sql = "DELETE FROM ".$this->db->prefix."module WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//更新排序
	public function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."module SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//存储模块下的字段表
	public function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"module_fields",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"module_fields");
		}
	}

	//存储模块表
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"module",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"module");
		}
	}
}
?>
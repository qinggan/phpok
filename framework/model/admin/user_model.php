<?php
/**
 * 会员增删改查
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月20日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class user_model extends user_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function identifier_chk($identifier='')
	{
		if(!$identifier){
			return false;
		}
		$fields = $this->db->list_fields('user');
		$fields[] = 'wealth';
		$fields[] = 'introducer';
		$fields[] = 'title';
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE ftype='user'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$fields[] = $value['identifier'];
			}
		}
		if(in_array($identifier,$fields)){
			return false;
		}
		return true;
	}

	/**
	 * 创建会员字段
	**/
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
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$id."'";
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
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."fields WHERE ftype='user' AND taxis<255";
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
		$data['ftype'] = 'user';
		if(isset($data['is_edit'])){
			$data['is_front'] = $data['is_edit'];
			unset($data['is_edit']);
		}
		if($id){
			return $this->db->update_array($data,"fields",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"fields");
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
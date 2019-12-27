<?php
/***********************************************************
	Filename: phpok/model/project.php
	Note	: 应用信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-15 18:05
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class project_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//首页取得简单的项目信息，通过ID
	function simple_project_from_identifier($identifier="",$site_id=0)
	{
		if(!$identifier) return false;
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE site_id=".intval($site_id)." AND status=1 ";
		$sql.= "AND identifier='".$identifier."' LIMIT 1";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得项目信息
	 * @参数 $id 标识ID，可以是主键，也可以是标识
	 * @参数 $ext 是否加载扩展
	**/
	public function get_one($id,$ext=true)
	{
		if(!$id){
			return false;
		}
		if(is_numeric($id)){
			$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".intval($id);
		}else{
			$condition = "site_id='".$this->site_id."' AND identifier='".$id."'";
			$sql = "SELECT * FROM ".$this->db->prefix."project WHERE ".$condition;
		}
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($ext && is_bool($ext)){
			$ext_rs = $this->model("ext")->get_all("project-".$id);
			if($ext_rs){
				$rs = array_merge($ext_rs,$rs);
			}
		}
		return $rs;
	}

	//通过identifier获取项目信息
	function identifier_one($id,$site_id=0,$ext=true)
	{
		$site_id = $site_id ? '0,'.intval($site_id) : '0';
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE identifier='".$id."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if(!$ext){
			return $rs;
		}
		$ext_rs = $this->model("ext")->get_all("project-".$rs['id']);
		if($ext_rs){
			$rs = array_merge($ext_rs,$rs);
		}
		return $rs;
	}


	//前台获取相应的get_one信息
	function www_one($id)
	{
		return $this->get_one($id,true);
	}

	public function project_all($site_id=0,$pri="id",$condition="")
	{
		$site_id = intval($site_id);
		$sql = "SELECT * FROM ".$this->db->prefix."project p WHERE site_id=".$site_id." ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		return $this->db->get_all($sql,$pri);
	}

	//取得单项
	public function project_one($site_id,$id)
	{
		if(!$id){
			return false;
		}
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id='".$id."' AND site_id='".$site_id."'";
		return $this->db->get_one($sql);
	}

	//取得当前分类下的父级分类信息，无父级分类则调用当前分类
	function get_parent($id)
	{
		$sql = "SELECT id,parent_id FROM ".$this->db->prefix."project WHERE id='".intval($id)."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		if(!$rs["parent_id"])
		{
			return $this->get_one($id);
		}
		else
		{
			return $this->get_one($rs["parent_id"]);
		}
	}

	function get_one_condition($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE 1=1 ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		return $this->db->get_one($sql);
	}

	//取得子栏目信息
	function sublist($id,$condition="")
	{
		return $this->project_sonlist($id);
	}

	//取得子分类列表
	function get_sonlist(&$list,$id="0")
	{
		$mylist = $this->sublist($id);
		if($mylist)
		{
			foreach($mylist AS $key=>$value)
			{
				$list[] = $value;
				$this->get_sonlist($list,$value["id"]);
			}
		}
	}

	function get_parentlist(&$list,$id)
	{
		if($id)
		{
			$rs = $this->get_one($id);
			if($rs)
			{
				$list[] = $rs;
				if($rs["parent_id"])
				{
					$this->get_parentlist($list,$rs["parent_id"]);
				}
			}
		}
	}

	function get_all($site_id=0,$pid=0,$condition="",$pri_id="")
	{
		$sql = "SELECT p.*,m.title project_module_title,m.mtype FROM ".$this->db->prefix."project p ";
		$sql.= " LEFT JOIN ".$this->db->prefix."module m ON(p.module=m.id) ";
		$sql.= " WHERE p.parent_id='".$pid."' ";
		if($site_id){
			$sql.= " AND p.site_id='".$site_id."' ";
		}
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql.= " ORDER BY p.taxis ASC,p.id DESC";
		return $this->db->get_all($sql,$pri_id);
	}

	function call_project_same($pid)
	{
		if(!$pid) return false;
		$sql = "SELECT parent_id,site_id FROM ".$this->db->prefix."project WHERE id='".$pid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$parent_id = intval($rs["parent_id"]);
		$site_id = $rs["site_id"];
		return $this->get_all($site_id,$parent_id,"p.status='1'");
	}

	function call_mid($pid)
	{
		$sql = "SELECT module FROM ".$this->db->prefix."project WHERE id='".$pid."' AND status='1'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["module"]) return false;
		return $rs["module"];
	}

	function get_sublist(&$list,$pid=0,$site_id=0,$space="",$condition='')
	{
		$rslist = $this->get_all($site_id,$pid,$condition);
		if($rslist){
			foreach($rslist as $key=>$value){
				$value["space"] = $space ? $space."├─ " : '';
				$list[] = $value;
				$newspace = $space."　　";
				$this->get_sublist($list,$value["id"],$site_id,$newspace,$condition);
			}
		}
	}

	public function get_all_project($site_id=0,$condition='')
	{
		$list = array();
		if(!$site_id){
			$site_id = $this->site_id;
		}
		$this->get_sublist($list,0,$site_id,"",$condition);
		return $list;
	}


	//子项目
	function get_son($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE parent_id='".$id."'";
		return $this->db->get_all($sql);
	}

	//检测模块是否被项目调用
	function chk_module($module_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE module='".$module_id."'";
		return $this->db->get_one($sql);
	}

	public function chk_cate($cate_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE cate='".$cate_id."'";
		return $this->db->get_one($sql);
	}

	//取得子项目信息
	public function project_sonlist($pid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE parent_id=".intval($pid)." AND status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$idlist = array_keys($rslist);
		foreach($idlist as $key=>$value){
			$idlist[$key] = "project-".$value;
		}
		$id = implode(",",$idlist);
		$extlist = $this->model('ext')->get_all($id,true);
		foreach($rslist as $key=>$value){
			$tk = "project-".$key;
			if($extlist[$tk]){
				$rslist[$key] = array_merge($extlist[$tk],$rslist[$key]);
			}
		}
		return $rslist;
	}

	public function title_list($pid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id IN(".$pid.") ORDER BY parent_id ASC,taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			$list[] = $value["title"];
		}
		return $list;
	}

	/**
	 * 取得项目信息
	 * @参数 $id 多个ID用英文逗号隔开，支持数组
	 * @参数 $status 是否只读启用状态的
	 * @参数 $pri 绑定主键，默认不绑定
	 * @返回 数组或false 
	**/
	public function plist($id,$status=0,$pri='')
	{
		if(!$id){
			return false;
		}
		if(is_string($id)){
			$id = explode(",",$id);
		}
		foreach($id as $key=>$value){
			if(!$value || !intval($value)){
				unset($id[$key]);
				continue;
			}
			$id[$key] = intval($value);
		}
		$id = implode(",",$id);
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id IN(".$id.") AND hidden=0 ";
		if($status){
			$sql.= "AND status=1 ";
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}
}
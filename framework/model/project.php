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
class project_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//首页取得简单的项目信息，通过ID
	function simple_project_from_identifier($identifier="",$site_id=0)
	{
		if(!$identifier || !$site_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE site_id=".$site_id." AND status=1 ";
		$sql.= "AND identifier='".$identifier."' LIMIT 1";
		return $this->db->get_one($sql);
	}

	//取得项目信息
	function get_one($id,$ext=true)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".$id;
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		if($ext)
		{
			$ext_rs = $GLOBALS['app']->model("ext")->get_all("project-".$id);
			if($ext_rs) $rs = array_merge($ext_rs,$rs);
		}
		return $rs;
	}

	//通过identifier获取项目信息
	function identifier_one($id,$site_id=0,$ext=true)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE identifier='".$id."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		if(!$ext) return $rs;
		$ext_rs = $GLOBALS['app']->model("ext")->get_all("project-".$rs['id']);
		if($ext_rs) $rs = array_merge($ext_rs,$rs);
		return $rs;
	}


	//前台获取相应的get_one信息
	function www_one($id)
	{
		return $this->get_one($id,true);
	}

	function project_all($site_id=0,$pri="id",$condition="")
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT * FROM ".$this->db->prefix."project p WHERE site_id IN(".$site_id.")";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		return $this->db->get_all($sql,$pri);
	}

	//取得当前分类下的父级分类信息，无父级分类则调用当前分类
	function get_parent($id)
	{
		$sql = "SELECT id,parent_id FROM ".$this->db->prefix."project WHERE id='".$id."'";
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
		if($condition)
		{
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
		$sql = "SELECT p.*,m.title project_module_title FROM ".$this->db->prefix."project p ";
		$sql.= " LEFT JOIN ".$this->db->prefix."module m ON(p.module=m.id) ";
		$sql.= " WHERE p.parent_id='".$pid."' ";
		if($site_id)
		{
			$sql.= " AND p.site_id='".$site_id."' ";
		}
		if($condition)
		{
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
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$value["space"] = $space ? $space."├─ " : '';
				$list[] = $value;
				$newspace = $space."　　";
				$this->get_sublist($list,$value["id"],$site_id,$newspace,$condition);
			}
		}
	}

	function get_all_project($site_id,$condition='')
	{
		$list = array();
		$this->get_sublist($list,0,$site_id,"",$condition);
		return $list;
	}

	# 存储核心菜单
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		phpok_delete_cache("project");
		if(!$id)
		{
			return $this->db->insert_array($data,"project");
		}
		else
		{
			$this->db->update_array($data,"project",array("id"=>$id));
			return true;
		}
	}

	# 设置状态
	function status($id,$status=0)
	{
		phpok_delete_cache("project");
		$sql = "UPDATE ".$this->db->prefix."project SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}


	//子项目
	function get_son($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE parent_id='".$id."'";
		return $this->db->get_all($sql);
	}

	//删除项目
	function delete_project($id)
	{
		$rs = $this->get_one($id,false);
		//删除模块下的内容信息
		if($rs['module'])
		{
			$sql = "DELETE FROM ".$this->db->prefix."list_".$rs['module']." WHERE project_id=".$id;
			$this->db->query($sql);
		}
		//删除项目中的内容信息
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE project_id='".$id."'";
		$this->db->query($sql);
		//删除项目中的扩展信息
		$sql = "SELECT id FROM ".$this->db->prefix."ext WHERE module='project-".$id."'";
		$extlist = $this->db->get_all($sql);
		if($extlist)
		{
			foreach($extlist AS $key=>$value)
			{
				$this->db->query("DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'");
			}
			$this->db->query("DELETE FROM ".$this->db->prefix."ext WHERE module='project-".$id."'");
		}
		//删除项目信息
		$sql = "DELETE FROM ".$this->db->prefix."project WHERE id='".$id."'";
		$this->db->query($sql);
		//删除缓存信息
		phpok_delete_cache("project");
	}

	//检测模块是否被项目调用
	function chk_module($module_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE module='".$module_id."'";
		return $this->db->get_one($sql);
	}

	function chk_cate($cate_id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE cate='".$cate_id."'";
		return $this->db->get_one($sql);
	}

	function update_taxis($id,$taxis="0")
	{
		phpok_delete_cache("project");
		$sql = "UPDATE ".$this->db->prefix."project SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	//取得子项目信息
	function project_sonlist($pid=0)
	{
		$pid = intval($pid);
		$sql = "SELECT * FROM ".$ths->db->prefix."project WHERE parent_id=".$pid." AND status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		$idlist = array_keys($rslist);
		foreach($idlist AS $key=>$value)
		{
			$idlist[$key] = "project-".$value;
		}
		$id = implode(",",$idlist);
		$extlist = $GLOBALS['app']->model('ext')->get_all($id,true);
		foreach($rslist AS $key=>$value)
		{
			$tk = "project-".$key;
			if($extlist[$tk])
			{
				$rslist[$key] = array_merge($extlist[$tk],$rslist[$key]);
			}
		}
		return $rslist;
	}

	function title_list($pid=0)
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

	//取得项目信息
	function plist($id,$status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id IN(".$id.") ";
		if($status) $sql.= "AND p.status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}
}
?>
<?php
/***********************************************************
	Filename: {phpok}/model/popedom.php
	Note	: 权限信息参数设置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class popedom_model extends popedom_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function delete($id)
	{
		if(!$id) return false;
		//取得项目信息
		$sql = "SELECT p.identifier,p.pid,s.appfile FROM ".$this->db->prefix."popedom p ";
		$sql.= " JOIN ".$this->db->prefix."sysmenu s ON(p.gid=s.id) ";
		$sql.= " WHERE p.id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs["appfile"] == "list" && !$rs["pid"])
		{
			$sql = "SELECT * FROM ".$this->db->prefix."popedom WHERE pid != '0' && identifier='".$rs["identifier"]."'";
			$rslist = $this->db->get_all($sql,"id");
			if($rslist)
			{
				$idstring = implode(",",array_keys($rslist));
				//删除配置
				$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE id IN(".$idstring.")";
				$this->db->query($sql);
				//删除管理员权限
				$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE pid IN(".$idstring.")";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE id='".$id."'";
		$this->db->query($sql);
		//删除已分配的权限
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE pid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	//更新权限，仅限当前一个
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if(!$id)
		{
			return $this->db->insert_array($data,"popedom");
		}
		else
		{
			return $this->db->update_array($data,"popedom",array("id"=>$id));
		}
	}

	//更新内容模块的权限字段
	public function update_popedom_list($data,$gid,$identifier="")
	{
		if(!$identifier || !$gid || !$data || count($data) < 1 || !is_array($data))
		{
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."popedom SET ";
		$str = array();
		foreach($data AS $key=>$value)
		{
			$str[] = $key."='".$value."'";
		}
		$sql .= implode(",",$str);
		$sql .= " WHERE gid='".$gid."' AND identifier='".$identifier."'";
		$this->db->query($sql);
	}

	public function is_exists($identifier,$gid,$pid=0)
	{
		if(!$identifier || !$gid) return true;
		$sql = "SELECT id FROM ".$this->db->prefix."popedom WHERE gid='".$gid."' AND identifier='".$identifier."' AND pid='".$pid."'";
		return $this->db->get_one($sql);
	}

	public function get_pid($condition="")
	{
		if(!$condition) return false;
		$sql = "SELECT p.id FROM ".$this->db->prefix."popedom p ";
		$sql.= " JOIN ".$this->db->prefix."sysmenu s ON(p.gid=s.id) ";
		$sql.= " WHERE ".$condition." LIMIT 1";
		$rs = $this->db->get_one($sql);
		if($rs){
			return $rs["id"];
		}
		return false;
	}

	

	public function get_site_id($pid)
	{
		if(!$pid){
			return false;
		}
		if(is_array($pid)){
			$idlist = false;
			foreach($pid as $key=>$value){
				if($value && intval($value)){
					$idlist[] = intval($value);
				}
			}
			if(!$idlist){
				return false;
			}
			$pid = implode(",",$idlist);
		}
		$sql = "SELECT gid,pid FROM ".$this->db->prefix."popedom WHERE id IN(".$pid.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$pids = $gids = array();
		foreach($rslist as $key=>$value){
			if($value['pid']){
				$pids[] = $value['pid'];
			}else{
				$gids[] = $value['gid'];
			}
		}
		if($pids && count($pids)>0){
			$tmp = array_unique($pids);
			$pid = implode(",",$tmp);
			$sql = "SELECT site_id FROM ".$this->db->prefix."project WHERE id IN(".$pid.") AND site_id!='0' ORDER BY site_id ASC";
			$rs = $this->db->get_one($sql);
			if($rs && $rs['site_id']){
				return $rs['site_id'];
			}
		}
		if($gids && count($gids)>0){
			$tmp = array_unique($gids);
			$gid = implode(",",$tmp);
			$sql = "SELECT site_id FROM ".$this->db->prefix."sysmenu WHERE id IN(".$gid.") ORDER BY site_id DESC";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				return false;
			}
			$site_id = 0;
			foreach($tmplist as $key=>$value){
				if($value['site_id']){
					$site_id = $value['site_id'];
					break;
				}
			}
			if(!$site_id){
				return false;
			}
			return $site_id;
		}
		return false;
	}

	//检测是否有站点权限
	public function site_popedom($site_id,$user_id)
	{
		$sql = "SELECT pid FROM ".$this->db->prefix."adm_popedom WHERE id='".$user_id."'";
		$list = $this->db->get_all($sql,'pid');
		if(!$list){
			return false;
		}
		return true;
	}
}
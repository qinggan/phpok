<?php
/***********************************************************
	Filename: {phpok}/model/ad.php
	Note	: 广告管理（Model层）
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年5月13日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ad_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_list($condition="",$offset=0,$psize=30,$pri_id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ad ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql,$pri_id);
	}

	function get_total($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."ad ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."ad WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		if($rs["pic_id"])
		{
			$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id='".$rs["pic_id"]."'";
			$res_rs = $this->db->get_one($sql);
			if(!$res_rs) return $rs;
			$sql = "SELECT e.*,gd.identifier FROM ".$this->db->prefix."res_ext e ";
			$sql.= " JOIN ".$this->db->prefix."gd gd ON(e.gd_id=gd.id) ";
			$sql.= " WHERE e.res_id='".$rs["pic_id"]."' ";
			$gd_list = $this->db->get_all($sql);
			if($gd_list)
			{
				foreach($gd_list AS $key=>$value)
				{
					$res_rs["gd"][$value["identifier"]] = $value;
				}
			}
			$rs["pic_id"] = $res_rs;
		}
		return $rs;
	}

	function get_all_in($id,$condition="")
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."ad WHERE id IN(".$id.")";
		if($condition)
		{
			$sql .= " AND ".$condition;
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$res = array();
		foreach($rslist AS $key=>$value)
		{
			if($value["pic_id"])
			{
				$res[] = $value["pic_id"];
			}
		}
		$res = implode(",",$res);
		if(!$res) return $rslist;
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$res.")";
		$reslist = $this->db->get_all($sql,"id");
		if(!$reslist) return $rslist;
		/*$sql = "SELECT e.*,gd.identifier FROM ".$this->db->prefix."res_ext e ";
		$sql.= " JOIN ".$this->db->prefix."gd gd ON(e.gd_id=gd.id) ";
		$sql.= " WHERE e.res_id IN(".$res.") ";
		$extlist = $this->db->get_all($sql);
		if($extlist)
		{
			foreach($extlist AS $key=>$value)
			{
				$reslist[$value["res_id"]]["gd"][$value["identifier"]] = $value;
			}
		}*/
		foreach($rslist AS $key=>$value)
		{
			if($value["pic_id"])
			{
				$value["pic_id"] = $reslist[$value["pic_id"]];
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1) return false;
		if($id)
		{
			return $this->db->update_array($data,"ad",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"ad");
		}
	}

	function set_status($id,$status=0)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."ad SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function ad_delete($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."ad WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."ad_pv WHERE aid='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."ad_info WHERE aid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function save_pv($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1) return false;
		if($id)
		{
			return $this->db->update_array($data,"ad_pv",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"ad_pv");
		}
	}

	function save_info($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1) return false;
		if($id)
		{
			return $this->db->update_array($data,"ad_info",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"ad_info");
		}
	}

	function update_stat($id)
	{
		if(!$id) return false;
		$sql = "SELECT count(id) hits,count(DISTINCT ip) ip,count(DISTINCT session_id) uv FROM ".$this->db->prefix."ad_info WHERE aid='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$sql = "UPDATE ".$this->db->prefix."ad SET hits='".$rs["hits"]."',ip='".$rs["ip"]."',uv='".$rs["uv"]."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;		
	}

	function update_pv_stat($id)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."ad_pv WHERE aid='".$id."'";
		$pv = $this->db->count($sql);
		$sql = "UPDATE ".$this->db->prefix."ad SET pv='".$pv."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	function stat_list($id,$date="")
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."ad_info WHERE aid='".$id."' ";
		if($date)
		{
			$sql .= " AND FROM_UNIXTIME(addtime,'%Y-%m-%d')='".$date."' ";
		}
		$sql .= " ORDER BY addtime DESC,id DESC ";
		return $this->db->get_all($sql);
	}

	function stat_num($id,$type="%Y-%m-%d",$condition="")
	{
		if(!$id) return false;
		$sql = "SELECT FROM_UNIXTIME(addtime,'".$type."') title,count(id) hits,count(DISTINCT ip) ip,count(DISTINCT session_id) uv ";
		$sql.= " FROM ".$this->db->prefix."ad_info WHERE aid='".$id."'";
		if($condition)
		{
			$sql.= " AND ".$condition;
		}
		$sql.= " GROUP BY FROM_UNIXTIME(addtime,'".$type."') ORDER BY addtime DESC,id DESC";
		return $this->db->get_all($sql);
	}

	function pv_num($id,$type="%Y-%m-%d",$condition="")
	{
		$sql = "SELECT FROM_UNIXTIME(addtime,'".$type."') title,count(id) pv ";
		$sql.= " FROM ".$this->db->prefix."ad_pv WHERE aid='".$id."'";
		if($condition)
		{
			$sql.= " AND ".$condition;
		}
		$sql.= " GROUP BY FROM_UNIXTIME(addtime,'".$type."') ORDER BY addtime DESC,id DESC";
		return $this->db->get_all($sql);
	}

	function stat_list_num($id,$date="")
	{
		if(!$id) return false;
		//读PV
		$sql = "SELECT count(id) total,aid FROM ".$this->db->prefix."ad_pv WHERE aid IN(".$id.") ";
		if($date)
		{
			$sql.= " AND FROM_UNIXTIME(addtime,'%Y-%m-%d')='".$date."' ";
		}
		$sql .= ' GROUP BY aid ';
		$pv_rs = $this->db->get_all($sql,"aid");
		if(!$pv_rs) return false;
		$list = array();
		foreach($pv_rs AS $key=>$value)
		{
			$list[$value["aid"]]["pv"] = $value["total"];
		}
		$sql = "SELECT count(id) hits,count(DISTINCT ip) ip,count(DISTINCT session_id) uv,aid ";
		$sql.= " FROM ".$this->db->prefix."ad_info WHERE aid IN(".$id.") ";
		if($date)
		{
			$sql.= " AND FROM_UNIXTIME(addtime,'%Y-%m-%d')='".$date."' ";
		}
		$sql .= ' GROUP BY aid ';
		$rslist = $this->db->get_all($sql,"aid");
		if(!$rslist) return $list;
		foreach($rslist AS $key=>$value)
		{
			$list[$value["aid"]]["hits"] = $value["hits"];
			$list[$value["aid"]]["ip"] = $value["ip"];
			$list[$value["aid"]]["uv"] = $value["uv"];
		}
		return $list;
	}
}
?>
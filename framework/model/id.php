<?php
/***********************************************************
	Filename: {phpok}models/id.php
	Note	: ID管理工具
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 13:23
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class id_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	function get_ctrl($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs) return 'project';
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs) return 'content';
		return false;
	}

	//检测标识ID是否被使用了
	//identifier：字符串
	//site_id，站点ID，整数
	function check_id($identifier,$site_id=0,$id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		//在项目中检测
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs) return true;
		//在分类中检测
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs) return true;
		//在内容里检测
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs) return true;
		return false;
	}

	function project_id($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	//取得id
	public function id($identifier,$site_id=0)
	{
		$rslist = $this->id_all($site_id);
		if($rslist[$identifier])
		{
			return $rslist[$identifier];
		}
		return false;
	}

	//
	public function id_all($site_id=0,$status=0)
	{
		$sql_1 = "SELECT concat('p',id) AS id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."'";
		$sql_2 = "SELECT concat('c',id) AS id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		$sql_3 = "SELECT concat('t',id) AS id,identifier FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier!=''";
		if($status)
		{
			$sql_1 .= " AND status=1";
			$sql_2 .= " AND status=1";
			$sql_3 .= " AND status=1";
		}
		$sql = "(".$sql_1.") UNION (".$sql_2.") UNION (".$sql_3.")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist)
		{
			return false;
		}
		$rslist = array();
		$tlist = array('t'=>'content','p'=>'project','c'=>'cate');
		foreach($tmplist as $key=>$value)
		{
			$tmp = substr($value['id'],0,1);
			$id = substr($value['id'],1);
			$rslist[$value['identifier']] = array('id'=>$id,'type'=>$tlist[$tmp]);
		}
		return $rslist;
	}
}
?>
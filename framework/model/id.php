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

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function get_ctrl($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs){
			return 'project';
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE identifier='".$identifier."' AND site_id IN(".$site_id.")";
		$rs = $this->db->get_one($sql);
		if($rs){
			return 'content';
		}
		return false;
	}

	//检测标识ID是否被使用了
	//identifier：字符串
	//site_id，站点ID，整数
	function check_id($identifier,$site_id=0,$id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		//在项目中检测
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE LOWER(identifier)='".strtolower($identifier)."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		if($id){
			$sql .= " AND id !=".intval($id);
		}
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		//在分类中检测
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE LOWER(identifier)='".strtolower($identifier)."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		//在内容里检测
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE LOWER(identifier)='".strtolower($identifier)."' AND site_id IN(".$site_id.")";
		if($id) $sql .= " AND id !=".intval($id);
		$check_rs = $this->db->get_one($sql);
		if($check_rs){
			return true;
		}
		return false;
	}

	function project_id($identifier,$site_id=0)
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id FROM ".$this->db->prefix."project WHERE LOWER(identifier)='".strtolower($identifier)."' ";
		$sql.= "AND site_id IN(".$site_id.") ";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	//取得id
	public function id($identifier,$site_id=0,$status=false)
	{
		$rslist = $this->id_all($site_id,$status);
		if($rslist[$identifier]){
			return $rslist[$identifier];
		}
		return false;
	}

	//
	public function id_all($site_id=0,$status=0)
	{
		$cache_id = $this->cache->id('model','id','id_all',$site_id,$status);
		$rslist = $this->cache->get($cache_id);
		if($rslist){
			return $rslist;
		}
		$this->db->cache_set($cache_id);
		$rslist = array();
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."project WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1 ";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'project');
			}
			unset($tmplist);
		}
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql.= " AND status=1";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'cate');
			}
		}
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."list WHERE site_id='".$site_id."' AND identifier!=''";
		if($status){
			$sql.= " AND status=1";
		}
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'content');
			}
		}
		if($rslist && count($rslist)>0){
			$this->cache->save($cache_id,$rslist);
			return $rslist;
		}
		return false;
	}
}
?>
<?php
/***********************************************************
	Filename: {phpok}/model/call.php
	Note	: 数据调用中心涉及到的SQL操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-18 02:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_model_base extends phpok_model
{
	public $psize = 20;
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function types()
	{
		$xmlfile = $this->dir_root.'data/xml/calltype_'.$this->site_id.'.xml';
		if(!file_exists($xmlfile)){
			$xmlfile = $this->dir_root.'data/xml/calltype.xml';
		}
		return $this->lib('xml')->read($xmlfile);
	}

	public function psize($psize=20)
	{
		$this->psize = $psize;
	}
	
	//通过ID取得数据（此操作用于后台）
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_rs($id,$site_id)
	{
		if(!$id || !$site_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$id."' AND site_id='".$site_id."'";
		return $this->db->get_one($sql);
	}

	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT call.* FROM ".$this->db->prefix."phpok call WHERE call.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql.= " ORDER BY call.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	public function get_all($site_id=0,$status=0)
	{
		if(!$site_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id='".$site_id."'";
		if($status) $sql.= " AND status=1";
		return $this->db->get_all($sql,"identifier");
	}

	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."phpok WHERE site_id='".$this->site_id."' ";
		if($condition)
		{
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}

	public function chk_identifier($val)
	{
		return $this->get_one_sign($val);
	}

	//通过标识串取得调用的配置数据
	function get_one_sign($val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$val."' AND site_id='".$this->site_id."'";
		return $this->db->get_one($sql);
	}

	//检测标识串是否存在
	function chksign($val)
	{
		return $this->get_one_sign($val);
	}

	public function one($identifier,$siteid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE identifier='".$identifier."' AND site_id='".$siteid."' ";
		$sql.= "AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$ext = unserialize($rs['ext']);
			$rs = array_merge($rs,$ext);
		}
		return $rs;
	}

	public function all($siteid=0,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."phpok WHERE site_id='".$siteid."' AND status=1";
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$ext = unserialize($value['ext']);
				unset($value['ext']);
				$value = array_merge($value,$ext);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

}
?>
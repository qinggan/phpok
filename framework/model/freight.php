<?php
/*****************************************************************************************
	文件： {phpok}/model/freight.php
	备注： 运费模板
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月08日 03时21分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class freight_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."freight WHERE site_id='".$this->site_id."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."freight WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function zone_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."freight_zone WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function zone_count($fid)
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."freight_zone WHERE fid='".$fid."'";
		return $this->db->count($sql);
	}

	public function zone_all($fid,$ext='*',$pri='')
	{
		$sql = "SELECT ".$ext." FROM ".$this->db->prefix."freight_zone WHERE fid='".$fid."'";
		return $this->db->get_all($sql,$pri);
	}

	public function zone_id($fid=0,$province='',$city='')
	{
		$condition = "fid='".$fid."' AND area LIKE '%".$province."%".$city."%'";
		$sql = "SELECT id FROM ".$this->db->prefix."freight_zone WHERE ".$condition." ORDER BY taxis ASC,id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['id'];
	}

	public function price_all($condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."freight_price ";
		if($condition){
			$sql.= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY unit_val ASC,zid DESC";
		return $this->db->get_all($sql);
	}

	public function price_one($zid,$val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."freight_price WHERE zid='".$zid."' AND CAST(unit_val AS DECIMAL)<=".floatval($val)." ";
		$sql.= " ORDER BY unit_val+0 DESC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if($rs){
			return $rs['price'];
		}
		return false;
	}

}

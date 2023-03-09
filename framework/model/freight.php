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

	
	public function area_ids_used($fid,$not_zone_id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."freight_zone WHERE fid='".$fid."'";
		$tmplist = $this->db->get_all($sql,'id');
		if(!$tmplist){
			return false;
		}
		$ids = array_keys($tmplist);
		if($not_zone_id && !is_array($not_zone_id)){
			$not_zone_id = explode(",",$not_zone_id);
		}
		if($not_zone_id){
			$ids = array_diff($ids,$not_zone_id);
			if(!$ids){
				return false;
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."freight_zone WHERE id in(".implode(",",$ids).")";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$idlist = array();
		foreach($tmplist as $key=>$value){
			if(!$value['area']){
				continue;
			}
			$tmp = unserialize($value['area']);
			if($tmp){
				foreach($tmp as $k=>$v){
					$idlist[] = $k;
					$idlist = array_merge($idlist,array_keys($v));
				}
			}
		}
		$idlist = array_unique($idlist);
		return $idlist;
	}

	public function get_all($condition='',$offset=0,$psize=30)
	{
		$sql  = " SELECT f.*,w.name country_name,w.name_en country_name_en,c.title currency_title ";
		$sql .= " FROM ".$this->db->prefix."freight f ";
		$sql .= " LEFT JOIN ".$this->db->prefix."world_location w ON(f.country_id=w.id) ";
		$sql .= " LEFT JOIN ".$this->db->prefix."currency c ON(f.currency_id=c.id) ";
		$sql .= " WHERE f.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql .= " ORDER BY f.taxis ASC,f.id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$typelist = $this->typelist();
		foreach($rslist as $key=>$value){
			if($value['type'] && $typelist[$value['type']]){
				$value['type_name'] = $typelist[$value['type']];
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	public function get_count($condition='')
	{
		$sql  = " SELECT count(f.id) FROM ".$this->db->prefix."freight f ";
		$sql .= " WHERE f.site_id='".$this->site_id."' ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
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
		if(($province && !is_numeric($province)) || ($city && !is_numeric($city))){
			$sql = "SELECT * FROM ".$this->db->prefix."freight_zone WHERE fid='".$fid."' ORDER BY taxis ASC,id DESC";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				return false;
			}
			$prolist = array();
			$citylist = array();
			foreach($tmplist as $key=>$value){
				if(!$value['area']){
					continue;
				}
				$tmp = unserialize($value['area']);
				foreach($tmp as $k=>$v){
					$prolist[] = $k;
					$citylist = array_merge($citylist,array_keys($v));
				}
			}
			$ids = array_merge($prolist,$citylist);
			$ids = array_unique($ids);
			$sql = "SELECT * FROM ".$this->db->prefix."world_location WHERE id IN(".implode(",",$ids).")";
			$tlist = $this->db->get_all($sql);
			if(!$tlist){
				return false;
			}
			$clist = $plist = array();
			foreach($tlist as $key=>$value){
				if(strpos($province,$value['name']) !== false && in_array($value['id'],$prolist)){
					$plist[] = $value;
					continue;
				}
				if(strpos($city,$value['name']) !== false && in_array($value['id'],$citylist)){
					$clist[] = $value;
					continue;
				}
			}
			if(!$plist || !$clist){
				return false;
			}
			$province = current($plist);
			$city = current($clist);
			$province_id = $province['id'];
			$city_id = $city['id'];
			$rs = array();
			foreach($tmplist as $key=>$value){
				if(!$value['area']){
					continue;
				}
				$tmp = unserialize($value['area']);
				if($tmp && $tmp[$province_id] && $tmp[$province_id][$city_id]){
					$rs = $value;
					break;
				}
			}
			if(!$rs){
				return false;
			}
			return $rs['id'];
		}
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

	public function vweight($val='')
	{
		$file = $this->dir_data.'freight_vweight.php';
		if($val != ''){
			$this->lib('file')->vi($val,$file);
			return true;
		}
		$vw = 5000;
		if(file_exists($file)){
			$vw = $this->lib('file')->cat($file);
		}
		return $vw;
	}

	public function typelist($code='')
	{
		$list = array('weight'=>P_Lang('重量'));
		$list['volume'] = P_Lang('体积');
		$list['number'] = P_Lang('数量');
		$list['fixed'] = P_Lang('固定值');
		$list['price'] = P_Lang('价格');
		$list['vweight'] = P_Lang('体积重');
		if($code){
			if($list[$code]){
				return $list[$code];
			}
			return false;
		}
		return $list;
	}
}

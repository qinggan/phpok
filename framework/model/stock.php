<?php
/**
 * 库存管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2022年6月17日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class stock_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function val_all($tid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."stock WHERE tid='".$tid."'";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmps = explode(",",$value['attr']);
			sort($tmps);
			$attr = implode(",",$tmps);
			$rslist[$attr] = $value;
		}
		return $rslist;

	}

	public function clean($tid)
	{
		$sql = "DELETE FROM ".$this->db->prefix."stock WHERE tid='".$tid."'";
		return $this->db->query($sql);
	}

	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."stock WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function update($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		return $this->db->update($data,"stock",array('id'=>$id));
	}

	public function add($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		return $this->db->insert($data,"stock");
	}

	/**
	 * 减库存
	**/
	public function reduce($tid,$attr='',$qty=0)
	{
		if(!$tid){
			return false;
		}
		$qty = intval($qty);
		if(!$qty){
			return true;
		}
		if($attr){
			if(is_string($attr)){
				$attr = explode(",",$attr);
			}
			sort($attr);
			$attr = implode(",",$attr);
			$sql  = "UPDATE ".$this->db->prefix."stock SET qty=if(qty>".$qty.",qty-".$qty.",0) WHERE tid='".$tid."'";
			$sql .= " AND attr='".$attr."'";
			$this->db->query($sql);
		}
		$sql = "UPDATE ".$this->db->prefix."list_biz SET qty=if(qty>".$qty.",qty-".$qty.",0) WHERE id='".$tid."'";
		return $this->db->query($sql);
	}

	/**
	 * 加库存
	**/
	public function plus($tid,$attr='',$qty=0)
	{
		if(!$tid){
			return false;
		}
		$qty = intval($qty);
		if(!$qty){
			return true;
		}
		if($attr){
			if(is_string($attr)){
				$attr = explode(",",$attr);
			}
			sort($attr);
			$attr = implode(",",$attr);
			$sql  = "UPDATE ".$this->db->prefix."stock SET qty=qty+".$qty." WHERE tid='".$tid."'";
			$sql .= " AND attr='".$attr."'";
			$this->db->query($sql);
		}
		$sql = "UPDATE ".$this->db->prefix."list_biz SET qty=qty+".$qty." WHERE id='".$tid."'";
		return $this->db->query($sql);
	}
}

<?php
/***********************************************************
	Filename: {phpok}/model/cart.php
	Note	: 购物车相关SQL操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月11日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cart_model_base extends phpok_model
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

	//获取购物车，如果购物车不存在，则自动创建
	public function cart_id($sessid,$uid=0)
	{
		if(!$sessid){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE session_id='".$sessid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			if($uid){
				$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE user_id='".$uid."'";
				$rs = $this->db->get_one($sql);
				if(!$rs){
					$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
					$id = $this->db->insert_array($array,'cart');
				}else{
					$id = $rs['id'];
				}
			}else{
				$array = array('session_id'=>$sessid,'user_id'=>$uid,'addtime'=>$this->time);
				$id = $this->db->insert_array($array,'cart');
			}
		}else{
			$id = $rs['id'];
		}
		if($uid){
			$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE user_id='".$uid."'";
			$rs = $this->db->get_one($sql);
			if($rs && $rs['id'] != $id){
				$this->cart_merge($rs['id'],$id);
				$this->delete($rs['id']);
			}
			$sql = "UPDATE ".$this->db->prefix."cart SET user_id='".$uid."' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		return $id;
	}

	private function cart_merge($old_id,$new_id)
	{
		if(!$old_id || !$new_id || $old_id == $new_id){
			return false;
		}
		$old_list = $this->get_all($old_id);
		if(!$old_list){
			return true;
		}
		$new_list = $this->get_all($new_id);
		if(!$new_list){
			$sql = "UPDATE ".$this->db->prefix."cart_product SET cart_id='".$new_id."' WHERE cart_id='".$old_id."'";
			$this->db->query($sql);
			return true;
		}
		$tlist = array();
		foreach($new_list AS $key=>$value){
			$tlist[] = $value['tid'];
		}
		foreach($old_list AS $key=>$value){
			if(in_array($value['tid'],$tlist)){
				$sql = "UPDATE ".$this->db->prefix."cart_product SET qty=qty+".$value['qty']." WHERE cart_id='".$new_id."' AND tid='".$value['tid']."'";
				$this->db->query($sql);
				$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE id='".$value['id']."'";
				$this->db->query($sql);
			}else{
				$sql = "UPDATE ".$this->db->prefix."cart_product SET cart_id='".$new_id."' WHERE id='".$value['id']."'";
				$this->db->query($sql);
			}
		}
		return true;
	}

	public function get_all($cart_id)
	{
		if(!$cart_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist AS $key=>$value){
			if(!$value['tid']){
				continue;
			}
			$arc_rs = $this->call->phpok("_arc",array("title_id"=>$value['tid']));
			if($arc_rs){
				$value = array_merge($arc_rs,$value);
				$rslist[$key] = $value;
			}
		}
		return $rslist;
	}

	//取得购物车下的一个产品信息
	//id并不是指产品ID，而是购物车指定的ID
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function update($id,$qty=1)
	{
		$sql = "UPDATE ".$this->db->prefix."cart_product SET qty='".$qty."' WHERE id='".$id."'";
		$this->db->query($sql);
		$rs = $this->get_one($id);
		if($rs){
			$this->update_cart_time($rs['cart_id']);
		}
		return true;
	}

	//添加产品数据
	public function add($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$this->update_cart_time($data['cart_id']);
		return $this->db->insert_array($data,'cart_product');
	}

	public function update_cart_time($cart_id)
	{
		$sql = "UPDATE ".$this->db->prefix."cart SET addtime='".$this->time."' WHERE id='".$cart_id."'";
		return $this->db->query($sql);
	}

	public function total($cart_id)
	{
		$sql = "SELECT SUM(qty) FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		return $this->db->count($sql);
	}

	public function delete($cart_id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id='".$cart_id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id='".$cart_id."'";
		$this->db->query($sql);
		return true;
	}

	public function delete_product($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function clear_expire_cart()
	{
		$oldtime = $this->time - 24 * 60 *60;
		$sql = "SELECT id FROM ".$this->db->prefix."cart WHERE addtime<".$oldtime;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist) return true;
		$idlist = array_keys($rslist);
		$idstring = implode(',',$idlist);
		$sql = "DELETE FROM ".$this->db->prefix."cart_product WHERE cart_id IN(".$idstring.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."cart WHERE id IN(".$idstring.")";
		$this->db->query($sql);
		return true;
	}
}
?>
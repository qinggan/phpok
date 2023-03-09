<?php
/**
 * 批发价管理
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年6月18日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class wholesale_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function all($tid='')
	{
		if(!$tid){
			return false;
		}
		if(is_array($tid)){
			$tid = implode(",",$tid);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."wholesale WHERE tid IN(".$tid.") ORDER BY tid ASC, qty ASC";
		return $this->db->get_all($sql);
	}

	public function delete($tid)
	{
		$sql = "DELETE FROM ".$this->db->prefix."wholesale WHERE tid='".$tid."'";
		return $this->db->query($sql);
	}

	public function price($tids='')
	{
		if(!$tids){
			return false;
		}
		if(is_array($tids)){
			$tids = implode(",",$tids);
		}
		$sql = "SELECT tid,min(price) min_price,max(price) max_price FROM ".$this->db->prefix."wholesale WHERE tid IN(".$tids.") GROUP BY tid";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$rs = array();
		foreach($rslist as $key=>$value){
			$t[$value['tid']] = $value['min_price'].'-'.$value['max_price'];
		}
		return $t;
	}

	public function save($data,$tid)
	{
		if(!$data || !$tid){
			return false;
		}
		$olist = $this->all($tid);
		if(!$olist){
			foreach($data as $key=>$value){
				$tmp = array();
				$tmp['tid'] = $tid;
				$tmp['qty'] = $value['qty'];
				$tmp['price'] = $value['price'];
				$this->db->insert($tmp,"wholesale");
			}
			return true;
		}
		$old = array();
		foreach($olist as $key=>$value){
			$old[$value['qty']] = $value;
		}
		$new = array();
		foreach($data as $key=>$value){
			$new[$value['qty']] = $value;
		}
		$old_keys = array_keys($old);
		$new_keys = array_keys($new);
		$deletes = array_diff($old_keys,$new_keys);
		if($deletes){
			foreach($deletes as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."wholesale WHERE tid='".$tid."' AND qty='".$value."'";
				$this->db->query($sql);
			}
		}
		foreach($data as $key=>$value){
			if($old[$value['qty']]){
				$sql = "UPDATE ".$this->db->prefix."wholesale SET price='".$value['price']."' WHERE tid='".$tid."' AND qty='".$value['qty']."'";
				$this->db->query($sql);
				continue;
			}
			$tmp = array();
			$tmp['tid'] = $tid;
			$tmp['qty'] = $value['qty'];
			$tmp['price'] = $value['price'];
			$this->db->insert($tmp,"wholesale");
		}
		return true;
	}
}

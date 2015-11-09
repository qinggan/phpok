<?php
/***********************************************************
	Filename: {$phpok}/model/user.php
	Note	: 会员模块
	Version : 3.0
	Author  : qinggan
	Update  : 2013年5月4日
***********************************************************/
class user_model_base extends phpok_model
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

	public function get_one($id,$field='id')
	{
		if(!$id){
			return false;
		}
		if(!$field){
			$field = 'id';
		}
		$sql = " SELECT u.*,e.* FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		$sql.= " WHERE u.".$field."='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$flist = $this->fields_all();
		if(!$flist){
			return $rs;
		}
		foreach($flist AS $key=>$value){
			$rs[$value['identifier']] = $this->lib('form')->show($value,$rs[$value['identifier']]);
		}
		//获取会员积分
		$wlist = $this->model('wealth')->get_all(1,'id');
		if($wlist){
			foreach($wlist as $key=>$value){
				$val = number_format(0,$value['dnum']);
				$rs['wealth'][$value['identifier']] = array('title'=>$value['title'],'val'=>$val,'unit'=>$value['unit']);
			}
			$condition = "uid='".$rs['id']."'";
			$tlist = $this->model('wealth')->vals($condition);
			if($tlist){
				foreach($tlist as $key=>$value){
					$tmp = $wlist[$value['wid']];
					$val = number_format($value['val'],$tmp['dnum']);
					$rs['wealth'][$tmp['identifier']]['val'] = $val;
				}
			}
		}
		return $rs;
	}

	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = " SELECT u.*,e.* FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$offset = intval($offset);
		$psize = intval($psize);
		$sql.= " ORDER BY u.id DESC ";
		if($psize){
			$offset = intval($offset);
			$sql .= "LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		//读取会员积分信息
		$wlist = $this->model('wealth')->get_all(1,'id');
		if($wlist){
			$condition = "uid IN(".implode(",",$idlist).")";
			$tlist = $this->model('wealth')->vals($condition);
			if($tlist){
				$wealth = array();
				foreach($tlist as $key=>$value){
					$tmp = $wlist[$value['wid']];
					$rslist[$value['uid']]['wealth'][$tmp['identifier']] = $value['val'];
				}
			}
		}
		//获取会员扩展字段
		$flist = $this->fields_all();
		if(!$flist){
			return $rslist;
		}
		foreach($rslist AS $key=>$value){
			foreach($flist AS $k=>$v){
				$value[$v['identifier']] = $this->lib('form')->show($v,$value[$v['identifier']]);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}


	//取得总数量
	public function get_count($condition="")
	{
		$sql = "SELECT count(u.id) FROM ".$this->db->prefix."user u ";
		$sql.= " LEFT JOIN ".$this->db->prefix."user_ext e ON(u.id=e.id) ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}


	//检测账号是否冲突
	function chk_name($name,$id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE user='".$name."' ";
		if($id)
		{
			$sql.= " AND id!='".$id."' ";
		}
		return $this->db->get_one($sql);
	}

	public function fields_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"user_fields",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"user_fields");
		}
	}

	//取得扩展字段的所有扩展信息
	function fields_all($condition="",$pri_id="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri_id);
	}

	public function tbl_fields_list($tbl)
	{
		$sql = "SELECT FIELDS FROM ".$tbl;
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist AS $key=>$value){
				$idlist[] = $value["Field"];
			}
		}
		return $idlist;
	}

	function field_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_fields WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	//取得全部会员ID
	function get_all_from_uid($uid,$pri="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id IN(".$uid.")";
		return $this->db->get_all($sql,$pri);
	}

	function fields()
	{
		return $this->db->list_fields($this->db->prefix."user");
	}

	function uid_from_email($email,$id="")
	{
		if(!$email) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE email='".$email."'";
		if($id)
		{
			$sql.= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	function uid_from_chkcode($code)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."user WHERE code='".$code."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	public function save($data,$id=0)
	{
		if($id){
			$status = $this->db->update_array($data,"user",array("id"=>$id));
			if($status){
				return $id;
			}
			return false;
		}else{
			return $this->db->insert_array($data,"user");
		}
	}

	public function save_ext($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		return $this->db->insert_array($data,"user_ext","replace");
	}

	public function update_ext($data,$id)
	{
		if(!$data || !is_array($data) || !$id){
			return false;
		}
		return $this->db->update_array($data,"user_ext",array("id"=>$id));
	}

	public function get_relation($uid)
	{
		$sql = "SELECT introducer FROM ".$this->db->prefix."user_relation WHERE uid='".$uid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $rs['introducer'];
	}

	public function save_relation($uid,$introducer)
	{
		$sql = "REPLACE INTO ".$this->db->prefix."user_relation(uid,introducer,dateline) VALUES('".$uid."','".$introducer."','".$this->time."')";
		return $this->db->query($sql);
	}

	public function address($uid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE user_id='".$uid."' ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	public function address_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function address_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'user_address',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'user_address');
		}
	}

	public function address_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function address_default($id=0)
	{
		if(!$id){
			return false;
		}
		$rs = $this->address_one($id);
		$sql = "UPDATE ".$this->db->prefix."user_address SET is_default=0 WHERE user_id='".$rs['user_id']."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_address SET is_default=1 WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function invoice($uid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_invoice WHERE user_id='".$uid."' ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	public function invoice_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_invoice WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function invoice_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'user_invoice',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'user_invoice');
		}
	}

	public function invoice_delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user_invoice WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function invoice_default($id=0)
	{
		if(!$id){
			return false;
		}
		$rs = $this->invoice_one($id);
		$sql = "UPDATE ".$this->db->prefix."user_invoice SET is_default=0 WHERE user_id='".$rs['user_id']."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."user_invoice SET is_default=1 WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}
}
?>
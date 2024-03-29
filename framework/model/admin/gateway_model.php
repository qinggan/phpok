<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/gateway_model.php
	备注： 第三方网关后台管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年10月09日 15时14分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gateway_model extends gateway_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_all($status=0)
	{
		$grouplist = $this->group_all();
		if($grouplist){
			foreach($grouplist as $key=>$value){
				$grouplist[$key] = array('title'=>$value);
			}		
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE site_id='".$this->site_id."'";
		if($status){
			$sql .= " AND status='".($status == 1 ? 1 : 0)."' ";
		}
		$sql.= " ORDER BY taxis ASC";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$grouplist[$value['type']]['list'][] = $value;
			}
		}
		return $grouplist;
	}


	public function group_all()
	{
		$file = $this->dir_gateway.'config.xml';
		if(!file_exists($file)){
			$tmplist = array('sms'=>P_Lang('短信'));
			$tmplist['email'] = P_Lang('Email邮件');
			$tmplist['object-storage'] = P_Lang('对象存储');
			return $tmplist;
		}
		return $this->lib('xml')->read($file);
	}
	
	public function code_all($type='')
	{
		if(!$type){
			return false;
		}
		if(!file_exists($this->dir_gateway.$type)){
			$this->lib('file')->make($this->dir_gateway.$type);
		}
		//读取目录下的
		$list = $this->lib('file')->ls($this->dir_gateway.$type);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$tmp = array();
			$tmp['id'] = basename($value);
			$tmp['dir'] = $value;
			$tmpfile = $value.'/config.xml';
			if(file_exists($tmpfile)){
				$t = $this->lib('xml')->read($tmpfile);
				$tmp['title'] = $t['title'];
				if($t['note']){
					$tmp['note'] = $t['note'];
				}
				$tmp['code'] = $t['code'];
			}else{
				$tmp['title'] = $tmp['id'];
			}
			$rslist[$tmp['id']] = $tmp;
		}
		return $rslist;
	}

	public function next_taxis($type,$code)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."gateway WHERE type='".$type."' AND code='".$code."'";
		$sql.= " AND site_id='".$this->site_id."'";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	public function update_default($id)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."gateway SET is_default=0 WHERE site_id='".$this->site_id."' AND type='".$rs['type']."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."gateway SET is_default=1 WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,'gateway',array('id'=>$id));
		}else{
			return $this->db->insert_array($data,'gateway');
		}
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."gateway WHERE id='".$id."'";
		return $this->db->query($sql);
	}
	
}
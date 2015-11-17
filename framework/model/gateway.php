<?php
/*****************************************************************************************
	文件： {phpok}/model/gateway.php
	备注： 第三方网关接入管理工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月29日 23时58分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gateway_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_all($status=0)
	{
		$grouplist = $this->group_all();
		foreach($grouplist as $key=>$value){
			$grouplist[$key] = array('title'=>$value);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gateway ";
		if($status){
			$sql .= " WHERE status='".($status == 1 ? 1 : 0)."' ";
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

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		return $rs;
	}

	public function get_default($type)
	{
		if(!$type){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."gateway WHERE type='".$type."' AND site_id='".$this->site_id."' AND status=1";
		$sql.= " AND is_default=1 ORDER BY id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		$param = $this->code_one($rs['type'],$rs['code']);
		if(!$param['code']){
			return false;
		}
		$code = $param['code'];
		$chk_status = true;
		foreach($code as $key=>$value){
			if($value['required'] && $value['required'] != 'false' && $value['required'] != '0'){
				$tmpid = $rs['ext'][$key];
				if($tmpid == ''){
					$chk_status = false;
					break;
				}
			}
		}
		if(!$chk_status){
			return false;
		}
		return $rs;
	}

	public function code_one($type,$id)
	{
		if(!$type || !$id){
			return false;
		}
		$rs = array('id'=>$id,'dir'=>$this->dir_root.'gateway/'.$type.'/'.$id);
		$xmlfile = $this->dir_root.'gateway/'.$type.'/'.$id.'/config.xml';
		if(file_exists($xmlfile)){
			$tmp = $this->lib('xml')->read($xmlfile);
		}else{
			$tmp = array('title'=>$id,'code'=>'');
		}
		$rs['code'] = $tmp['code'];
		$rs['title'] = $tmp['title'];
		return $rs;
	}


	public function action($server,$data)
	{
		$rs = $server;
		$extinfo = $data;
		$file = $this->dir_root.'gateway/'.$rs['type'].'/'.$rs['code'].'/exec.php';
		if(!file_exists($file)){
			return false;
		}
		return include $file;
	}

	//保存临时数据
	public function save_temp($info,$gid,$uid=0)
	{
		$file = 'gateway_'.$gid.'_'.$uid.'.php';
		return $this->lib('file')->vi($info,$this->dir_root.'data/cache/'.$file);
	}

	//读取临时数据
	public function read_temp($gid,$uid=0)
	{
		$file = 'gateway_'.$gid.'_'.$uid.'.php';
		if(!file_exists($this->dir_root.'data/cache/'.$file)){
			return false;
		}
		$info = $this->lib('file')->cat($this->dir_root.'data/cache/'.$file);
		if(!$info || !trim($info)){
			return false;
		}
		return trim($info);
	}

	public function delete_temp($gid,$uid=0)
	{
		$file = 'gateway_'.$gid.'_'.$uid.'.php';
		$this->lib('file')->rm($this->dir_root.'data/cache/'.$file);
		return true;
	}
}

?>
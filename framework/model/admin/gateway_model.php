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

	public function group_all()
	{
		$file = $this->dir_root.'gateway/config.xml';
		if(!file_exists($file)){
			return array('sms'=>P_Lang('短信网关'),'email'=>P_Lang('Email邮件'));
		}
		return $this->lib('xml')->read($file);
	}

	public function code_all($type='')
	{
		if(!$type){
			return false;
		}
		//读取目录下的
		$handle = opendir($this->dir_root.'gateway/'.$type);
		$list = array();
		while(false !== ($myfile = readdir($handle))){
			if(substr($myfile,0,1) != '.' && is_dir($this->dir_root.'gateway/'.$type.'/'.$myfile)){
				$list[$myfile] = array('id'=>$myfile,'dir'=>$this->dir_root.'gateway/'.$type.'/'.$myfile);
				$tmpfile = $this->dir_root.'gateway/'.$type.'/'.$myfile.'/config.xml';
				if(file_exists($tmpfile)){
					$tmp = $this->lib('xml')->read($tmpfile);
				}else{
					$tmp = array('title'=>$myfile,'code'=>'');
				}
				$list[$myfile]['title'] = $tmp['title'];
				$list[$myfile]['code'] = $tmp['code'];
			}
		}
		closedir($handle);
		return $list;
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

?>
<?php
/***********************************************************
	Filename: {phpok}/model/plugins.php
	Note	: 插件中心
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 10:19
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_model extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	//取得全部插件
	function get_all($status=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins ";
		if($status)
		{
			$sql .= "WHERE status=1 ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,'id');
		if(!$rslist) return false;
	}

	//取得全部的插件列表
	function dir_list()
	{
		$folder = $this->dir_root."plugins/";
		//读取列表
		$handle = opendir($folder);
		$list = array();
		while(false !== ($file = readdir($handle)))
		{
			if(substr($file,0,1) != "." && is_dir($folder.$file))
			{
				$list[] = $file;
			}
		}
		closedir($handle);
		return $list;
	}

	function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	function get_xml($id)
	{
		$folder = $this->dir_root."plugins/".$id."/";
		if(!is_dir($folder))
		{
			return false;
		}
		$rs = array();
		if(is_file($folder."config.xml"))
		{
			$rs = xml_to_array(file_get_contents($folder."config.xml"));
		}
		$rs["id"] = $id;
		$rs["path"] = $folder;
		return $rs;
	}

	function install_save($data)
	{
		if(!$data || !is_array($data)) return false;
		$this->db->insert_array($data,'plugins','replace');
		//检测是否写入成功
		$sql = "SELECT id FROM ".$this->db->prefix."plugins WHERE id='".$data['id']."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs['id'];
	}

	function update_param($id,$info='')
	{
		$sql = "UPDATE ".$this->db->prefix."plugins SET param='".$info."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function update_plugin($data,$id)
	{
		if(!$data || !$id || !is_array($data)) return false;
		$this->db->update_array($data,'plugins',array('id'=>$id));
	}

	function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."plugins WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."plugins SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
?>
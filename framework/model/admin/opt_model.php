<?php
/**
 * 选项组管理
 * @package phpok\model\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_model extends opt_model_base
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 选项组删除
	 * @参数 $id 组ID
	**/
	public function group_del($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."opt_group WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."opt WHERE group_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 保存选项组
	 * @参数 $data 数组
	 * @参数 $id 组ID，为0或空时，表示添加
	**/
	public function group_save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"opt_group");
		}else{
			return $this->db->update_array($data,"opt_group",array("id"=>$id));
		}
	}

	/**
	 * 保存选项内容
	 * @参数 $data 数组
	 * @参数 $id 选项ID
	**/
	public function opt_save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"opt");
		}else{
			return $this->db->update_array($data,"opt",array("id"=>$id));
		}
	}

	/**
	 * 删除选项
	 * @参数 $id 选项ID
	**/
	public function opt_del($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."opt WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
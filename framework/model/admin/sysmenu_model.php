<?php
/**
 * 系统菜单管理器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月31日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class sysmenu_model extends sysmenu_model_base
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 删除核心菜单，同时删除相应的权限配置
	 * @参数 $id 菜单ID
	 * @返回 true
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id='".$id."' AND parent_id !=0";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 保存核心菜单数据
	 * @参数 $data 一维数组，要保存的数据
	 * @参数 $id 当ID为0或空时，表示添加，反之表示更新
	**/
	public function save($data,$id=0)
	{
		if(!$id){
			return $this->db->insert_array($data,"sysmenu");
		}else{
			return $this->db->update_array($data,"sysmenu",array("id"=>$id));
		}
	}

	/**
	 * 更新核心菜单的状态
	 * @参数 $id 主键ID
	 * @参数 $status 要变更的值
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."sysmenu SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新核心菜单的排序
	 * @参数 $id 主键ID
	 * @参数 $taxis 排序的值
	 * @更新时间 
	**/
	public function update_taxis($id,$taxis=255)
	{
		$taxis = intval($taxis);
		if($taxis > 255){
			$taxis = 255;
		}
		$sql = "UPDATE ".$this->db->prefix."sysmenu SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
}
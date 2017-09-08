<?php
/**
 * 会员地址库
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月05日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * 按条件取得会员地址库数量
	 * @参数 $condition 查询条件
	**/
	public function count($condition='')
	{
		$sql = "SELECT count(a.id) FROM ".$this->db->prefix."user_address a LEFT JOIN ".$this->db->prefix."user u ON(a.user_id=u.id)";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得会员地址库
	 * @参数 $condition 查询条件
	 * @参数 $offset 定位
	 * @参数 $psize 读取数量
	**/
	public function get_list($condition='',$offset=0,$psize=20)
	{
		$sql = "SELECT a.*,u.user FROM ".$this->db->prefix."user_address a LEFT JOIN ".$this->db->prefix."user u ON(a.user_id=u.id)";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY u.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 取得单条地址信息
	 * @参数 $id 地址ID
	**/
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user_address WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 存储更新地址库信息
	 * @参数 $data 要保存的数据，一维数组
	 * @参数 $id 要更新的地址ID，留空表示添加
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"user_address",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"user_address");
		}
	}

	/**
	 * 删除地址信息
	 * @参数 $id 要删除的地址ID 
	**/
	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."user_address WHERE id='".intval($id)."'";
		return $this->db->query($sql);
	}
}
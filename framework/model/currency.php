<?php
/**
 * 货币管理器
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年10月10日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class currency_model_base extends phpok_model
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得国际货币列表
	 * @参数 $pri 多维数组中的key定位，留空使用数字，从0起
	 * @返回 多维数组或false
	**/
	public function get_list($pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 取得指定货币的基本信息
	 * @参数 $id 主键ID
	 * @参数 $field_id 表字段名，在货币里常指id、code、title等
	 * @返回 数组或false
	**/
	public function get_one($id,$field_id='id')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."currency WHERE ".$field_id."='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 存储信息，添加或更新
	 * @参数 $data 数组
	 * @参数 $id 主键ID
	 * @返回 true/false/或自增ID
	**/
	public function save($data,$id="")
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"currency",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"currency","replace");
		}
	}

	/**
	 * 更新货币状态
	 * @参数 $id 货币ID
	 * @参数 $status 为0表示禁用，1表示启用
	 * @返回 true/false
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."currency SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}
	
	/**
	 * 更新排序
	 * @参数 $id 货币ID
	 * @参数 $taxis 排序值，最大255，最小0
	 * @返回 true/false
	**/
	public function update_sort($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."currency SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 删除货币操作，请慎用，执行此删除后，货币订单等计算会有问题
	 * @参数 $id 货币ID
	 * @返回 true/false
	**/
	public function del($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."currency WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得货币汇率
	 * @参数 $id 货币ID
	 * @参数 $field_id 表字段名，在货币里常指id、code、title等
	 * @返回 汇率信息或空
	**/
	public function rate($id,$field_id='id')
	{
		$sql = "SELECT val FROM ".$this->db->prefix."currency WHERE ".$field_id."='".$id."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $tmp['val'];
	}
}
?>
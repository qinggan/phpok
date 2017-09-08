<?php
/**
 * 管理员信息管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 通过管理员账号取得管理员信息
	 * @参数 $username
	**/
	public function get_one_from_name($username)
	{
		if(!$username) return false;
		return $this->get_one($username,"account");
	}

	/**
	 * 取得一条管理员数据
	 * @参数 $id 参数值
	 * @参数 $field 参数名称，可选：account，id
	**/
	public function get_one($id,$field="id")
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."adm WHERE ".$field."='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 检测管理员账号是否存在
	 * @参数 $account 账号
	 * @参数 $id 不包括指定的ID
	**/
	public function check_account($account,$id=0)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."adm WHERE account='".$account."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 更新管理员密码 
	 * @参数 $id 管理员ID
	 * @参数 $password 密码，必须是已加密过的
	**/
	public function update_password($id,$password)
	{
		if(!$id || !$password){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."adm SET pass='".$password."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得管理员列表
	 * @参数 $condition 查询条件 
	 * @参数 $offset 起始位置
	 * @参数 $psize 查询数量
	**/
	public function get_list($condition="",$offset=0,$psize=30)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."adm ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		$sql .= " ORDER BY id DESC ";
		if($psize && intval($psize) > 0){
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 取得管理员数量
	 * @参数 $condition 查询条件
	**/
	public function get_total($condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."adm ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得管理员权限
	 * @参数 $id 管理员ID
	**/
	public function get_popedom_list($id)
	{
		$sql = "SELECT pid FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist AS $key=>$value){
			$list[] = $value["pid"];
		}
		return $list;
	}

	/**
	 * 删除管理员
	 * @参数 $id 管理员ID
	**/
	public function delete($id)
	{
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."adm WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 保存管理员信息
	 * @参数 $data 管理员资料，一维数组
	 * @参数 $id 不为空时表示更新，为空或0时表示新增
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"adm",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"adm");
		}
	}

	/**
	 * 清除非系统管理中权限
	 * @参数 $id 管理员ID
	**/
	public function clear_popedom($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."adm_popedom WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 存储权限
	 * @参数 $data 权限ID，支持数组及字串
	 * @参数 $id 管理员ID
	**/
	public function save_popedom($data,$id)
	{
		if(!$id || !$data){
			return false;
		}
		if(!is_array($data)){
			$data = explode(",",$data);
		}
		foreach($data as $key=>$value){
			$tmp = array("id"=>$id,"pid"=>$value);
			$this->db->insert_array($tmp,"adm_popedom","replace");
		}
		return true;
	}

	/**
	 * 更新管理员状态
	 * @参数 $id 管理员ID
	 * @参数 $status 状态，0禁用，1使用
	**/
	public function update_status($id,$status=0)
	{
		$sql = "UPDATE ".$this->db->prefix."adm SET status='".$status."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

}
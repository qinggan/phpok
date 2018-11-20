<?php
/**
 * 选项组信息
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月03日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_model_base extends phpok_model
{
	private $_cache;
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得全部的选项组
	**/
	function group_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group ORDER BY id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得某个组信息
	 * @参数 $id 组ID
	**/
	public function group_one($id)
	{
		$cache_id = "group_one_".$id;
		if(isset($this->_cache[$cache_id])){
			return $this->_cache[$cache_id];
		}
		$sql = "SELECT * FROM ".$this->db->prefix."opt_group WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$this->_cache[$cache_id] = $rs;
		return $rs;
	}

	/**
	 * 取得值列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始位置
	 * @参数 $psize 内容数
	**/
	public function opt_list($condition="",$offset=0,$psize=20)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE 1=1 ";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC";
		$sql .= " LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 选项列表
	 * @参数 $condition 查询条件
	**/
	public function opt_all($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 格式化多维数组
	 * @参数 $groupid 组ID
	 * @参数 $pid 父级ID
	**/
	public function opt_format($groupid=0,$pid=0)
	{
		if(!$groupid){
			return false;
		}
		$condition = "group_id=".intval($groupid);
		$rslist = $this->opt_all($condition);
		if(!$rslist){
			return false;
		}
		$list = array();
		$this->_opt_format($list,$rslist,$pid);
		return $list;
	}

	private function _opt_format(&$list,$rslist,$pid=0)
	{
		foreach($rslist as $key=>$value){
			if($value['parent_id'] == $pid){
				$tmp = $value;
				$tmp['sublist'] = array();
				$this->_opt_format($tmp['sublist'],$rslist,$value['id']);
				$list[] = $tmp;
			}
		}
	}

	/**
	 * 取得数量总数
	 * @参数 $condition 查询条件
	**/
	public function opt_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."opt ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得选项内容
	 * @参数 $id 选项ID
	**/
	public function opt_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得单条选项内容
	 * @参数 $condition 查询条件
	**/
	public function opt_one_condition($condition)
	{
		if(!$condition){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE ".$condition;
		return $this->db->get_one($sql);
	}

	/**
	 * 选项内容
	 * @参数 $gid 组ID
	 * @参数 $val 值
	**/
	public function opt_val($gid,$val)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE val='".$val."' AND group_id='".$gid."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$array = array('val'=>$val,'title'=>($rs['title'] ? $rs['title'] : $val));
		return $array;
	}

	/**
	 * 检测值是否重复
	 * @参数 $gid 组ID
	 * @参数 $val 值
	 * @参数 $pid 当前父级ID
	 * @参数 $id 当前ID
	**/
	public function chk_val($gid,$val,$pid=0,$id=0)
	{
		$cache_id = "chk_val_".$gid.'_'.$val."_".$pid."_".$id;
		if(isset($this->_cache[$cache_id])){
			return $this->_cache[$cache_id];
		}
		$sql = "SELECT * FROM ".$this->db->prefix."opt WHERE val='".$val."' AND group_id='".$gid."'";
		$sql.= " AND parent_id='".$pid."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		$rs = $this->db->get_one($sql);
		$this->_cache[$cache_id] = $rs;
		if(!$rs){
			return false;
		}
		return $rs;
	}

	/**
	 * 取得父子关系的数组
	 * @参数 $list 引用组
	 * @参数 $pid 父级ID
	**/
	public function opt_parent(&$list,$pid=0)
	{
		if($pid){
			$rs = $this->opt_one($pid);
			$list[] = $rs;
			if($rs["parent_id"]){
				$this->opt_parent($list,$rs["parent_id"]);
			}
		}
	}

	/**
	 * 取得子项列表
	 * @参数 $list 引用组
	 * @参数 $id 父级ID
	**/
	public function opt_son(&$list,$id=0)
	{
		$condition = "parent_id='".$id."'";
		$tmplist = $this->opt_all($condition);
		if($tmplist){
			foreach($tmplist AS $key=>$value){
				$list[] = $value;
				$this->opt_son($list,$value["id"]);
			}
		}
	}
}
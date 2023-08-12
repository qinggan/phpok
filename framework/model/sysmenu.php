<?php
/**
 * 后台核心应用管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2012年10月27日
 * @更新 2023年8月13日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class sysmenu_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function get_title($ctrl,$func='')
	{
		if(!$ctrl){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile='".$ctrl."'";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$rs = array();
		foreach($list as $key=>$value){
			if($func && $value['func'] == $func){
				$rs = $value;
				break;
			}
		}
		if(!$rs['title']){
			$rs = $list[0];
		}
		return $rs;
	}

	# 获取一条信息
	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_one_condition($condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."sysmenu ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->get_one($sql);
	}

	# 取得指定菜单
	public function get_list($parent_id=0,$status=0)
	{
		# 当未指定子菜单时，直接获取父栏目信息
		$parent_id = intval($parent_id);
		$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE parent_id=".intval($parent_id)." ";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//取得全部菜单，并生成树状分类，仅限后台使用
	public function get_all($site_id=0,$status=0,$condition='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE 1=1 ";
		if($status){
			$sql .= " AND status=1 ";
		}
		if($site_id){
			$sql_in = "0,".$site_id;
			$sql .= " AND site_id IN(".$sql_in.") ";
		}
		if($condition){
			$sql .= " AND ".$condition;
		}
		$sql .= " ORDER BY taxis ASC,id DESC";
		$tmp_list = $this->db->get_all($sql);
		if(!$tmp_list) return false;
		$rslist = array();
		foreach($tmp_list AS $key=>$value){
			if(!$value["parent_id"]){
				$rslist[$value["id"]] = $value;
			}
		}
		foreach($tmp_list AS $key=>$value){
			if($value["parent_id"]){
				$ctrl = 'CORE/admin/'.$value['appfile'].'_control.php';
				if(is_dir($this->dir_app.$value['appfile'])){
					$ctrl = 'APP/'.$value['appfile'].'/admin.control.php';
				}
				$value['_ctrlfile'] = $ctrl;
				$rslist[$value["parent_id"]]["sublist"][$value["id"]] = $value;
			}
		}
		return $rslist;
	}

	//根据菜单取得导航高亮对应的菜单ID
	public function get_current_id($site_id=0,$ctrl='',$condition=array())
	{
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE site_id IN(".$site_id.") ";
		if($ctrl){
			$sql .= " AND appfile='".$ctrl."'";
		}
		$sql .= " AND parent_id !=0 AND status=1";
		$sql .= " ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		//计算相似度
		$ulist = array();
		foreach($rslist AS $key=>$value){
			$tmp = array();
			$i = 0;
			if($value['ext'] && $condition['ext'] && $this->sort($value['ext']) == $this->sort($condition['ext'])){
				$i = $i+4;
			}
			if($value['identifier'] && $condition['identifier'] && $value['identifier'] == $condition['identifier']){
				$i = $i+3;
			}
			if($value['func'] && $condition['func'] && $value['func'] == $condition['func']){
				$i++;
			}
			$ulist[$value['id']] = $i;
		}
		//比较两个数据的相似度
		$r = array_search(max($ulist),$ulist);
		return $r;
	}


	public function get_menu_id($site_id=0,$ctrl="",$func="",$identifier="")
	{
		if(!$ctrl) return false;
		$sql = " SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile='".$ctrl."' ";
		if($site_id){
			$sql .= " AND site_id IN(0,".$site_id.") ";
		}
		$sql .= " AND parent_id !=0 ";
		$sql.= " ORDER BY id ASC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist) return false;
		# 最接近
		$first = $second = $third = false;
		foreach($tmplist as $key=>$value){
			if($value["identifier"] && $value["identifier"] == $identifer && $value["func"] && $value["func"] == $func && $value["appfile"] == $ctrl){
				if(!$third){
					$third = true;
					$third_id = $value["id"];
				}
			}
			if(!$value["identifier"] && $value["func"] && $value["func"] == $func && $value["appfile"] == $ctrl){
				if(!$second){
					$second = true;
					$second_id = $value["id"];
				}
			}
			if(!$value["identifier"] && !$value["func"] && $value["appfile"] == $ctrl){
				if(!$first){
					$first = true;
					$first_id = $value["id"];
				}
			}
		}
		if($third){
			return $third_id;
		}
		if($second){
			return $second_id;
		}
		if($first){
			return $first_id;
		}
		return false;
	}

	private function sort($string)
	{
		if(!$string){
			return false;
		}
		$list = explode('&',$string);
		sort($list);
		return implode('&',$list);
	}

}
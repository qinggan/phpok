<?php
/**
 * 扩展字段内容格式化
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2013年3月5日
 * @更新 2023年3月13日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class ext_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 检查字段是否有被使用
	 * @参数 $identifier 变量名
	 * @参数 $module 模块ID
	 * @参数 $id 不包含的ID
	**/
	public function check_identifier($identifier, $module, $id=0)
	{
		if(!$identifier || !$module){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' AND ftype='".$module."'";
		if($id){
			$sql .= " AND id !='".$id."'";
		}
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return true;
	}

	/**
	 * 读取模块下的字段内容
	 * @参数 $module 模块名称
	 * @参数 $show_content 是否读取内容，默认true
	**/
	public function ext_all($module,$show_content=true)
	{
		$rslist = $this->model('fields')->flist($module);
		if(!$rslist){
			return false;
		}
		if($show_content){
			$ids = array();
			foreach($rslist as $key=>$value){
				$ids[] = $value['id'];
			}
			$rs = $this->extc_info($ids,true);
			foreach($rslist as $key=>$value){
				if(isset($rs[$value['id']]) && $rs[$value['id']] != ''){
					$value['content'] = $rs[$value['id']];
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}

	public function extc_info($id,$mult=false)
	{
		if($mult){
			$id = $this->_ids($id);
			$sql = "SELECT * FROM ".$this->db->prefix."extc WHERE id IN(".$id.")";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				return false;
			}
			$rs = array();
			foreach($tmplist as $key=>$value){
				$rs[$value['id']] = $value['content'];
			}
			return $rs;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."extc WHERE id='".$id."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		$rs = array();
		$rs[$tmp['id']] = $tmp['content'];
		return $rs;
	}



	# 取得数据库下的字段
	# tbl 指定数据表名，多个数据表用英文逗号隔开
	# prefix 表名是否带有前缀，默认不带
	public function fields($tbl,$prefix=false)
	{
		if(!$tbl) return false;
		$list = explode(",",$tbl);
		$idlist = array();
		foreach($list as $key=>$value){
			$table = $prefix ? $value : $this->db->prefix.$value;
			$extlist = $this->db->list_fields($table);
			if($extlist){
				$idlist = array_merge($idlist,$extlist);
			}
		}
		foreach($idlist as $key=>$value){
			$idlist[$key] = strtolower($value);
		}
		return array_unique($idlist);
	}

	# 取得单个字段的配置
	public function get_one($id)
	{
		return $this->model('fields')->one($id);
	}


	//取得所有扩展选项信息
	public function get_all($id,$mult = false)
	{
		if($mult){
			$id = $this->_string($id,true);
			$rs = array();
			foreach($id as $key=>$value){
				$tmp = $this->get_all($value,false);
				if($tmp){
					$rs[$value] = $tmp;
				}
			}
			if(!$rs){
				return false;
			}
			return $rs;
		}
		$rslist = $this->model('fields')->flist($id);
		if(!$rslist){
			return false;
		}
		$ids = array();
		foreach($rslist as $key=>$value){
			$ids[] = $value['id'];
		}
		$content = $this->extc_info($ids,true);
		$rs = array();
		foreach($rslist as $key=>$value){
			if($content && $content[$value['id']]){
				$value['content'] = $content[$value['id']];
			}
			$rs[$value['identifier']] = $this->lib('form')->show($value);
		}
		return $rs;
	}

	public function get_all_like($id)
	{
		$condition = "ftype LIKE '".$id."%'";
		$rslist = $this->model('fields')->get_all($condtion,0,999);
		if(!$rslist){
			return false;
		}
		$ids = array();
		foreach($rslist as $key=>$value){
			$ids[] = $value['ftype'];
		}
		$ids = array_unique($ids);
		return $this->get_all($ids,true);
	}
}
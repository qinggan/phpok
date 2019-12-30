<?php
/**
 * 读取内容列表，涉及到的主要表有 list及list_数字ID
 * @package phpok\model\list
 * @author qinggan <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @homepage http://www.phpok.com
 * @version 4.x
 * @license http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @update 2016年06月26日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_model_base extends phpok_model
{
	protected $is_biz = false;
	protected $is_user = false;
	protected $multiple_cate = false;
	protected $_total = 0;
	protected $_primary_id_asc = true; //ID递减，设为false时表示ID递增
	/**
	 * 构造函数，继承父Model
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 是否启用电商
	 * @参数 $is_biz true 或 false
	**/
	public function is_biz($is_biz='')
	{
		if(isset($is_biz) && (is_bool($is_biz) || is_int($is_biz))){
			$this->is_biz = $is_biz;
		}
		return $this->is_biz;
	}

	/**
	 * 是否有绑定会员
	 * @参数 $is_user true 或 false
	**/
	public function is_user($is_user='')
	{
		if(isset($is_user) && is_bool($is_user)){
			$this->is_user = $is_user;
		}
		return $this->is_user;
	}

	/**
	 * 是否有多级分类
	 * @参数 $is_user true 或 false
	**/
	public function multiple_cate($multiple_cate='')
	{
		if(isset($multiple_cate) && is_bool($multiple_cate)){
			$this->multiple_cate = $multiple_cate;
		}
		return $this->multiple_cate;
	}

	/**
	 * 获取扩展模块使用的扩展字段
	 * @参数 $mid，模块ID，数值
	 * @参数 $prefix，表别名，默认是ext
	 * @返回 字符串，类似：ext.field1,ext.field2
	 * @更新时间 2016年06月26日
	**/
	public function ext_fields($mid,$prefix="ext",$condition='')
	{
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE ftype='".$mid."'";
		if($condition){
			$sql .= " AND ".$condition;
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		if(!$prefix){
			$prefix = 'ext';
		}
		$list = array();
		foreach($rslist as $key=>$value){
			$list[] = 'ext.'.$value['identifier'];
		}
		return implode(",",$list);
	}

	/**
	 * 获取主题列表
	 * @参数 $mid，模块ID，数值
	 * @参数 $condition，查询条件
	 * @参数 $offset，查询起始位置，默认是0
	 * @参数 $psize，查询条数，默认是0，表示不限制
	 * @参数 $orderby，排序
	 * @返回 数组，查询结果集，扩展字段内容已经格式化
	**/
	public function get_list($mid,$condition="",$offset=0,$psize=0,$orderby="")
	{
		if(!$mid){
			return false;
		}
		if($this->_total > 100000 && $offset > 10000){
			return $this->_get_list($mid,$condition,$offset,$psize,$orderby);
		}
		$fields_list = $this->db->list_fields('list');
		$field = "l.id,u.user _user";
		foreach($fields_list as $key=>$value){
			if($value == 'id' || !$value){
				continue;
			}
			$field .= ",l.".$value;
		}
		$field_ext = $this->ext_fields($mid,'ext');
		if($field_ext){
			$field .= ",".$field_ext;
		}
		$linksql  = " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id AND l.project_id=ext.project_id) ";
		$linksql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
		if($this->is_biz || ($condition && strpos($condition,'b.') !== false) || strpos($orderby,'b.') !== false){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
			$field.= ",b.price,b.currency_id,b.weight,b.volume,b.unit";
		}
		if($this->multiple_cate || ($condition && strpos($condition,'lc.') !== false) || strpos($orderby,'lc.') !== false){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ".$linksql;
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		if(!$orderby){
			$orderby = " l.sort DESC,l.dateline DESC,l.id DESC ";
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		return $this->_list_format($mid,$rslist);
	}

	private function _list_format($mid,$rslist)
	{
		$cid_list = array();
		foreach($rslist as $key=>$value){
			$cid_list[$value["cate_id"]] = $value["cate_id"];
		}
		$m_rs = $this->lib('ext')->module_fields($mid);
		if($m_rs){
			foreach($rslist as $key=>$value){
				foreach($value as $k=>$v){
					if($m_rs[$k]){
						$value[$k] = $this->lib('ext')->content_format($m_rs[$k],$v);
					}
				}
				$rslist[$key] = $value;
			}
		}
		$cid_string = implode(",",$cid_list);
		if($cid_string){
			$catelist = $this->lib('ext')->cate_list($cid_string);
			foreach($rslist as $key=>$value){
				if($value["cate_id"]){
					$value["cate_id"] = $catelist[$value["cate_id"]];
					$rslist[$key] = $value;
				}
			}
		}
		return $rslist;
	}

	private function _get_list($mid,$condition='',$offset=0,$psize=20,$orderby='')
	{
		$sql = "SELECT l.id FROM ".$this->db->prefix."list l ";
		if($condition){
			if(strpos($condition,'ext.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ";
				$sql.= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
			}
			if(strpos($condition,'u.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
			}
			if(strpos($condition,'b.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
			}
			if(strpos($condition,'lc.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
			}
			$sql .= " WHERE ".$condition;
		}
		if(!$orderby){
			$orderby = " l.sort DESC,l.dateline DESC,l.id DESC ";
		}
		$sql .= " ORDER BY ".$orderby." LIMIT ".$offset.",1";
		$main = $this->db->get_one($sql);
		if(!$main){
			return false;
		}
		$this->_primary_id_asc_checking($orderby);
		if($condition){
			$condition .= " AND l.id".($this->_primary_id_asc ? '>=' : '<=').''.$main['id']." ";
		}else{
			$condition = " l.id".($this->_primary_id_asc ? '>=' : '<=').''.$main['id']." ";
		}
		$fields_list = $this->db->list_fields('list');
		$field = "l.id,u.user _user";
		foreach($fields_list as $key=>$value){
			if($value == 'id' || !$value){
				continue;
			}
			$field .= ",l.".$value;
		}
		$field_ext = $this->ext_fields($mid,'ext');
		if($field_ext){
			$field .= ",".$field_ext;
		}
		$linksql  = " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id AND l.project_id=ext.project_id) ";
		$linksql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
		if($this->is_biz || ($condition && strpos($condition,'b.') !== false) || strpos($orderby,'b.') !== false){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
			$field.= ",b.price,b.currency_id,b.weight,b.volume,b.unit";
		}
		if($this->multiple_cate || ($condition && strpos($condition,'lc.') !== false) || strpos($orderby,'lc.') !== false){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ".$linksql;
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)){
			$sql.= " LIMIT ".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		return $this->_list_format($mid,$rslist);
	}

	private function _primary_id_asc_checking($orderby='')
	{
		$orderby = trim($orderby);
		$list = explode(",",$orderby);
		foreach($list as $key=>$value){
			$tmp = strtolower($value);
			if(!$value || !trim($value)){
				continue;
			}
			$value = trim($value);
			if($value == 'l.id desc'){
				$this->_primary_id_asc = true;
				break;
			}
			if($value == 'l.id asc'){
				$this->_primary_id_asc = false;
			}
		}
		return $this->_primary_id_asc;
	}

	/**
	 * 取得总数
	 * @参数 $mid 模块ID
	 * @参数 $condition 查询条件
	**/
	public function get_total($mid,$condition="")
	{
		if(!$mid){
			return false;
		}
		$sql = " SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition){
			if(strpos($condition,'ext.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_".$mid." ext ";
				$sql.= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
			}
			if(strpos($condition,'u.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
			}
			if(strpos($condition,'b.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
			}
			if(strpos($condition,'lc.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
			}
			$sql .= " WHERE ".$condition;
		}
		$this->_total = $this->db->count($sql);
		return $this->_total;
	}

	/**
	 * 获取独立表数据
	 * @参数 $id 主题ID
	 * @参数 $mid 模块ID
	**/
	public function single_one($id,$mid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix.$mid." WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}


	/**
	 * 独立表数据保存
	 * @参数 $data 要保存的数据，如果存在 $data[id]，表示更新
	 * @参数 $mid 模块ID
	**/
	public function single_save($data,$mid=0)
	{
		if(!$data || !$mid){
			return false;
		}
		if($data['id']){
			$id = $data['id'];
			unset($data['id']);
			return $this->db->update_array($data,$mid,array('id'=>$id));
		}else{
			return $this->db->insert_array($data,$mid);
		}
	}

	/**
	 * 独立表列表数据
	 * @参数 $mid 模块ID
	 * @参数 $condition 查询条件
	 * @参数 $offset 起始位置
	 * @参数 $psize 查询数量
	 * @参数 $orderby 排序
	**/
	public function single_list($mid,$condition='',$offset=0,$psize=30,$orderby='',$field='*')
	{
		$sql = "SELECT ".$field." FROM ".$this->db->prefix.$mid." ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if(!$orderby){
			$orderby = 'id DESC';
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)>0){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		return $this->db->get_all($sql);
	}

	/**
	 * 查询独立表数量
	 * @参数 $mid 模块ID
	 * @参数 $condition 查询条件
	**/
	public function single_count($mid,$condition='')
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix.$mid." ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}


	/**
	 * 删除独立项目下的主题信息
	 * @参数 $id 主题ID
	 * @参数 $mid 模块ID
	**/
	public function single_delete($id,$mid=0)
	{
		if(!$id || !$mid){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix.$mid." WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得当前一个主题的信息
	 * @参数 $id 主题ID
	 * @参数 $format 是否格式化
	**/
	public function get_one($id,$format=true)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_biz WHERE id='".$id."'";
		$biz_rs = $this->db->get_one($sql);
		if($biz_rs){
			foreach($biz_rs as $key=>$value){
				$rs[$key] = $value;
			}
			unset($biz_rs);
		}
		if($rs['module_id']){
			$ext_rs = $this->get_ext($rs['module_id'],$id);
			if(!$ext_rs) return $rs;
			if(!$format){
				$rs = array_merge($ext_rs,$rs);
				return $rs;
			}
			$flist = $this->model('module')->fields_all($rs['module_id'],'identifier');
			if(!$flist){
				return $rs;
			}
			foreach($flist as $key=>$value){
				$content = $ext_rs[$value['identifier']];
				$content = $this->lib('ext')->content_format($value,$content);
				$rs[$value['identifier']] = $content;
			}
		}
		return $rs;
	}

	public function call_one($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list l ";
		$sql.= " WHERE l.id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function get_ext($mid,$id)
	{
		if(!$mid || !$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $rs;
	}

	public function get_ext_list($mid,$id)
	{
		if(!$mid || !$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$id.")";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		return $rslist;
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data) || count($data) < 1){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"list",array("id"=>$id));
		}
		return $this->db->insert_array($data,"list");
	}

	public function update_field($ids,$field,$val=0)
	{
		if(!$field || !$ids){
			return false;
		}
		if(is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "UPDATE ".$this->db->prefix."list SET ".$field."='".$val."' WHERE id IN(".$ids.")";
		return $this->db->query($sql);
	}

	/**
	 * 保存扩展表信息
	 * @参数 $data 数组，一维
	 * @参数 $mid 模块ID
	**/
	public function save_ext($data,$mid)
	{
		if(!$data || !is_array($data) || !$mid){
			return false;
		}
		if($data['id']){
			$sql = "SELECT id FROM ".$this->db->prefix."list_".$mid." WHERE id='".$data['id']."'";
			$chk = $this->db->get_one($sql);
			if($chk){
				unset($data['id']);
				$this->db->update_array($data,'list_'.$mid,array('id'=>$chk['id']));
				return true;
			}
		}
		return $this->db->insert_array($data,"list_".$mid,"replace");
	}

	/**
	 * 更新扩展表信息
	 * @参数 $data 数组，一维数组 
	 * @参数 $mid 模块ID
	 * @参数 $id 主题ID
	**/
	public function update_ext($data,$mid,$id)
	{
		if(!$data || !is_array($data) || !$mid || !$id){
			return false;
		}
		return $this->db->update_array($data,"list_".$mid,array("id"=>$id));
	}

	/**
	 * 存储扩展分类
	 * @参数 $id 主题ID
	 * @参数 $catelist 要保存的扩展分类ID，支持数组，字串，整数
	**/
	public function save_ext_cate($id,$catelist)
	{
		if(!$id || !$catelist){
			return false;
		}
		if(is_string($catelist) || is_numeric($catelist)){
			$catelist = explode(",",$catelist);
		}
		$this->list_cate_clear($id);
		$catelist = array_unique($catelist);
		$sql = "INSERT INTO ".$this->db->prefix."list_cate(id,cate_id) VALUES ";
		foreach($catelist as $key=>$value){
			if($key>0){
				$sql .= ",";
			}
			$sql .= "('".$id."','".$value."')";
		}
		$this->db->query($sql);
		return true;
	}

	/**
	 * 删除主题绑定的分类
	 * @参数 $id 要删除的主题
	**/
	public function list_cate_clear($id)
	{
		$id = intval($id);
		if(!$id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id=".$id;
		return $this->db->query($sql);
	}


	/**
	 * 批量删除
	 * @参数 $condition 查询条件
	 * @参数 $mid 模块ID
	 * @参数 
	**/
	public function pl_delete($condition='',$mid=0)
	{
		$sql = "SELECT id,module_id FROM ".$this->db->prefix."list WHERE ".$condition;
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$id_list = array_keys($rslist);
		$ids = implode(",",$id_list);
		//删除全部回复
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid IN(".$ids.")";
		$this->db->query($sql);
		//删除关键字记录
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id IN(".$ids.")";
		$this->db->query($sql);
		//
		foreach($rslist AS $key=>$value){
			if(!$mid && $value['module_id']){
				$mid = $value['module_id'];
			}
		}
		if($mid){
			$sql = "DELETE FROM ".$this->db->prefix."list_".$mid." WHERE id IN(".$ids.")";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id IN(".$ids.")";
		$this->db->query($sql);
		return true;
	}

	
	/**
	 * 检测主表及扩展表中的唯一内容记录
	 * @参数 $field 字段标识
	 * @参数 $val 字段内容
	 * @参数 $pid 项目ID
	 * @参数 $mid 模块ID
	**/
	public function only_record($field,$val,$pid=0,$mid=0)
	{
		if(!$field || !$value){
			return true;
		}
		$chk = $this->main_only_check($field,$val,$pid,$mid);
		if($chk){
			return true;
		}
		$chk = $this->ext_only_check($field,$val,$pid,$mid);
		if($chk){
			return true;
		}
		return false;
	}


	/**
	 * 检测主表中的唯一性
	 * @参数 $field 字段标识
	 * @参数 $val 字段内容
	 * @参数 $pid 项目ID
	 * @参数 $mid 模块ID
	**/
	public function main_only_check($field,$val,$pid=0,$mid=0)
	{
		if(!$field || !$val){
			return true;
		}
		$flist = $this->db->list_fields('list');
		if(!$flist || ($flist && !in_array($field,$flist))){
			return true;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE ".$field."='".$val."'";
		$sql .= " AND site_id='".$this->site_id."'";
		if($pid){
			$sql .= " AND project_id='".$pid."'";
		}
		if($mid){
			$sql .= " AND module_id='".$mid."'";
		}
		return $this->db->get_one($sql);
	}

	/**
	 * 扩展表唯一性检查
	 * @参数 $field 字段标识
	 * @参数 $val 字段内容
	 * @参数 $pid 项目ID
	 * @参数 $mid 模块ID
	**/
	public function ext_only_check($field,$val,$pid=0,$mid=0)
	{
		if(!$field || !$val || !$mid){
			return true;
		}
		//检查表字段
		$flist = $this->db->list_fields('list_'.$mid);
		if(!$flist){
			return false;
		}
		if(!in_array($field,$flist)){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list_".$mid." WHERE `".$field."`='".$val."'";
		$sql .= " AND site_id='".$this->site_id."'";
		if($pid){
			$sql .= " AND project_id='".$pid."'";
		}
		return $this->db->get_one($sql);
		
	}

	private function _project_format_orderby($orderby='')
	{
		if(!$orderby){
			$orderby = "l.sort DESC,l.dateline DESC,l.id DESC";
		}
		$tmp = explode(",",$orderby);
		$list = false;
		foreach($tmp as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$tmp2 = explode(" ",$value);
			$type = end($tmp2);
			if(!$type){
				$type = "ASC";
			}
			$id = $tmp2[0];
			$chk = explode(".",$id);
			$field = $chk[1] ? trim($chk[1]) : $id;
			$list[] = array('id'=>$tmp2[0],'type'=>strtoupper($type),'field'=>$field);
		}
		return $list;
	}
	

	/**
	 * 取得下一个主题ID
	 * @参数 $id 当前主题ID
	 * @返回 数字或false
	 * @更新时间 
	**/
	public function get_next($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$rs = $id;
			$id = $rs['id'];
		}else{
			$rs = $this->call_one($id);
			if(!$rs || !$rs['status'] || !$rs['project_id']){
				return false;
			}
		}
		$project = $this->model('project')->get_one($rs['project_id'],false);
		if(!$project || !$project['status']){
			return false;
		}
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.id DESC';
		$orderby_list = $this->_project_format_orderby($orderby);
		$sql = $this->_np_sql($rs,$project,$orderby_list,'l.id');
		$is_dateline = false;
		$orderby = '';
		foreach($orderby_list as $key=>$value){
			if($value['field'] == 'dateline'){
				$is_dateline = true;
			}
			if($orderby){
				$orderby .= ",";
			}
			$orderby .= $value['id']." ".($value['type'] == 'DESC' ? 'ASC' : 'DESC');
		}
		if($is_dateline){
			$sql .= " AND l.dateline>=".$rs['dateline']." AND l.id!='".$rs['id']."'";
		}else{
			$sql .= " AND l.id>".$id;
		}
		$sql .= " ORDER BY ".$orderby." LIMIT 1";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $tmp['id'];
	}

	/**
	 * 取得上一主题ID
	 * @参数 $id 当前主题ID 或主题内容
	 * @返回 数字或false
	 * @更新时间 2017年02月24日
	**/
	public function get_prev($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$rs = $id;
			$id = $rs['id'];
		}else{
			$rs = $this->call_one($id);
			if(!$rs || !$rs['status'] || !$rs['project_id']){
				return false;
			}
		}
		$project = $this->model('project')->get_one($rs['project_id'],false);
		if(!$project || !$project['status']){
			return false;
		}
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.id DESC';
		$orderby_list = $this->_project_format_orderby($orderby);
		$sql = $this->_np_sql($rs,$project,$orderby_list,'l.id');
		$orderby = '';
		foreach($orderby_list as $key=>$value){
			if($value['field'] == 'dateline'){
				$is_dateline = true;
			}
			if($orderby){
				$orderby .= ",";
			}
			$orderby .= $value['id']." ".$value['type'];
		}
		if($is_dateline){
			$sql .= " AND l.dateline<=".$rs['dateline']." AND l.id!='".$rs['id']."'";
		}else{
			$sql .= " AND l.id<".$id;
		}
		$sql .= " ORDER BY ".$orderby." LIMIT 1";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		return $tmp['id'];
	}

	private function _np_sql($rs,$project,$orderby_list,$field='*')
	{
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$is_ext = false;
		foreach($orderby_list as $key=>$value){
			if(strpos($value['id'],'ext.') !== false){
				$is_ext = true;
				break;
			}
		}
		if($is_ext){
			$sql.= " LEFT JOIN ".$this->db->prefix."list_".$project['module']." ext ON(l.id=ext.id) ";
		}
		if($rs['cate_id'] && $project['cate_multiple']){
			$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		if($project['is_biz'] && $orderby_list){
			$is_biz = false;
			foreach($orderby_list as $key=>$value){
				if(strpos($value['id'],'b.') !== false){
					$is_biz = true;
					break;
				}
			}
			if($is_biz){
				$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(l.id=b.id) ";
			}
		}
		$sql.= " WHERE l.status=1 AND l.hidden=0 ";
		if($rs['cate_id']){
			if($project['cate_multiple']){
				$sql .= " AND (l.cate_id=".$rs['cate_id']." OR lc.cate_id=".$rs['cate_id'].") ";
			}else{
				$sql .= " AND l.cate_id=".$rs['cate_id'];
			}
		}
		if($rs['project_id']){
			$sql .= " AND l.project_id=".$rs['project_id']." ";
		}
		if($rs['module_id']){
			$sql .= " AND l.module_id=".$rs['module_id']." ";
		}
		$sql .= " AND l.site_id=".$rs['site_id']." ";
		return $sql;
	}

	public function attr_list()
	{
		$xmlfile = $this->dir_data."xml/attr.xml";
		if(!file_exists($xmlfile)){
			$array = array("h"=>"头条","c"=>"推荐","a"=>"特荐");
			return $array;
		}
		return $this->lib('xml')->read($xmlfile);
	}

	public function title_list($pid=0)
	{
		if(!$pid){
			return false;
		}
		$sql = " SELECT l.*,c.title catename FROM ".$this->db->prefix."list l ";
		$sql.= " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
		$sql.= " WHERE l.project_id IN(".$pid.") AND l.status='1' ";
		$sql.= " ORDER BY l.sort ASC,l.dateline DESC,l.id DESC";
		return $this->db->get_all($sql);
	}

	public function get_all($condition="",$offset=0,$psize=30,$pri="")
	{
		$sql = "SELECT l.* FROM ".$this->db->prefix."list l ";
		if($condition){
			$sql.= " WHERE ".$condition;
		}
		$sql .= " ORDER BY l.dateline DESC,l.id DESC ";
		if($psize && $psize>0){
			$offset = intval($offset);
			$sql.= " LIMIT ".$offset.",".$psize;
		}
		return $this->db->get_all($sql,$pri);
	}

	function get_all_total($condition="")
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition)
		{
			$sql.= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	

	public function get_mid($id)
	{
		$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs || !$rs["module_id"]){
			return false;
		}
		return $rs["module_id"];
	}

	//
	public function simple_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 简单读取主题信息
	 * @参数 $ids，多个主题用英文逗号隔开，支持数组
	 * @参数 $status 是否仅读已审核的
	**/
	public function simple_all($ids,$status=0)
	{
		if(!$ids){
			return false;
		}
		if(is_string($ids)){
			$ids = explode(",",$ids);
		}
		foreach($ids as $key=>$value){
			if(!$value || !intval($value)){
				unset($ids[$key]);
				continue;
			}
			$ids[$key] = intval($value);
		}
		if(!$ids){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id IN(".implode(",",$ids).")";
		return $this->db->get_all($sql,"id");
	}

	public function get_one_condition($condition="",$mid=0)
	{
		if(!$condition || !$mid) return false;
		$sql = "SELECT l.*,ext.id _id FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."list_".$mid." ext ON(l.id=ext.id) WHERE ".$condition." ORDER BY l.id DESC";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		$ext_rs = $this->get_ext($rs["module_id"],$rs["id"]);
		if($ext_rs) $rs = array_merge($ext_rs,$rs);
		return $rs;
	}

	public function arc_all($project,$condition='',$field='*',$offset=0,$psize=0,$orderby='')
	{
		if($this->_total > 100000 && $offset > 10000){
			return $this->_arc_all($project,$condition,$field,$offset,$psize,$orderby);
		}
		$sql  = " SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$sql .= " JOIN ".$this->db->prefix."list_".$project['module']." ext ";
		$sql .= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		if($project['is_biz']){
			$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		}
		if($project['cate'] && $project['cate_multiple']){
			$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if($orderby){
			$sql .= " ORDER BY ".$orderby." ";
		}
		if($psize){
			$sql .= " LIMIT ".intval($offset).",".$psize;
		}
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		return $this->_arc_list_format($rslist,$project);
	}

	private function _arc_all($project,$condition='',$field='*',$offset=0,$psize=0,$orderby='')
	{
		$sql = "SELECT l.id FROM ".$this->db->prefix."list l ";
		if($condition){
			if(strpos($condition,'ext.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_".$project['module']." ext ";
				$sql.= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
			}
			if(strpos($condition,'u.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
			}
			if(strpos($condition,'b.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
			}
			if(strpos($condition,'lc.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
			}
			$sql .= " WHERE ".$condition;
		}
		if(!$orderby){
			$orderby = " l.sort DESC,l.dateline DESC,l.id DESC ";
		}
		$sql .= " ORDER BY ".$orderby." LIMIT ".$offset.",1";
		$main = $this->db->get_one($sql);
		if(!$main){
			return false;
		}
		$this->_primary_id_asc_checking($orderby);
		if($condition){
			$condition .= " AND l.id".($this->_primary_id_asc ? '>=' : '<=').''.$main['id']." ";
		}else{
			$condition = " l.id".($this->_primary_id_asc ? '>=' : '<=').''.$main['id']." ";
		}

		$linksql = " LEFT JOIN ".$this->db->prefix."list_".$project['module']." ext ON(l.id=ext.id AND l.project_id=ext.project_id) ";
		if(($condition && strpos($condition,'u.') !== false) || strpos($orderby,'u.') !== false){
			$linksql .= " LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id AND u.status=1) ";
		}
		if($project['is_biz']){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_biz b ON(b.id=l.id) ";
		}
		if($project['cate'] && $project['cate_multiple']){
			$linksql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
		}
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ".$linksql;
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)){
			$sql.= " LIMIT ".$psize;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		return $this->_arc_list_format($rslist,$project);
	}

	private function _arc_list_format($rslist,$project)
	{
		//ulist，会员信息
		//clist，分类信息
		//elist，扩展主题信息
		$user_id_list = $idlist = $ulist = $elist = array();
		foreach($rslist as $key=>$value){
			$idlist[] = $value['id'];
			if($project['is_userid'] && $value['user_id']){
				$ulist[] = intval($value['user_id']);
				$user_id_list[$value['id']] = $value['user_id']; 
			}
			$elist[] = 'list-'.$value['id'];
		}
		//读取会员信息
		if($project['is_userid']){
			$user_ids = implode(",",array_unique($ulist));
			if($user_ids){
				$condition = "u.id IN(".$user_ids.") AND u.status=1";
				$ulist = $this->model('user')->get_list($condition,0,0);
				if($ulist){
					foreach($user_id_list as $key=>$value){
						if($ulist[$value]){
							$rslist[$key]['user'] = $ulist[$value];
						}
					}
				}
			}
		}
		//读取主题分类信息
		if($project['cate']){
			$title_ids = implode(",",array_unique($idlist));
			$sql = "SELECT lc.id,lc.cate_id,c.title,c.identifier FROM ".$this->db->prefix."list_cate lc ";
			$sql.= "LEFT JOIN ".$this->db->prefix."cate c ON(lc.cate_id=c.id) WHERE lc.id IN(".$title_ids.") ";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				$sql = "SELECT l.id,l.cate_id,c.title,c.identifier FROM ".$this->db->prefix."list l LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
				$sql.= "WHERE l.id IN(".$title_ids.")";
				$tmplist = $this->db->get_all($sql);
			}
			if($tmplist){
				foreach($tmplist as $key=>$value){
					$tmp = $value;
					$tmp['url'] = $this->url($project['identifier'],$value['identifier']);
					unset($tmp['id']);
					$rslist[$value['id']]['catelist'][$value['cate_id']] = $tmp;
					$cate_id = $rslist[$value['id']]['cate_id'];
					if($cate_id && $cate_id == $value['cate_id']){
						$rslist[$value['id']]['cate'] = $tmp;
					}
				}
			}
		}

		//读取主题的扩展
		$elist = array_unique($elist);
		$tmplist = $this->model('ext')->get_all($elist,true);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$k = explode('-',$key);
				$rslist[$k[1]] = array_merge($value,$rslist[$k[1]]);
			}
		}
		return $rslist;
	}

	public function arc_count($mid,$condition='')
	{
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		if($condition && strpos($condition,'ext.') !== false){
			$sql .= " JOIN ".$this->db->prefix."list_".$mid." ext ";
			$sql .= " ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		}
		if($condition){
			if(strpos($condition,'b.') !== false){
				$sql .= " LEFT JOIN ".$this->db->prefix."list_biz b ON(l.id=b.id) ";
			}
			if(strpos($condition,'lc.') !== false){
				$sql.= " LEFT JOIN ".$this->db->prefix."list_cate lc ON(l.id=lc.id) ";
			}
			$sql .= " WHERE ".$condition." ";
		}
		$this->_total = $this->db->count($sql);
		return $this->_total;
	}

	public function delete($id,$mid=0)
	{
		if(!$mid){
			$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
			$rs = $this->db->get_one($sql);
			$mid = $rs['module_id'];
		}
		//删除扩展主题信息
		if($mid){
			//删除附件
			$this->delete_res($id,$mid);
			$sql = "DELETE FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$sql = "DELETE FROM ".$this->db->prefix."list WHERE id='".$id."'";
		$this->db->query($sql);
		//删除相关的回复信息
		$sql = "DELETE FROM ".$this->db->prefix."reply WHERE tid='".$id."'";
		$this->db->query($sql);
		//删除Tag相关
		$sql = "DELETE FROM ".$this->db->prefix."tag_stat WHERE title_id='".$id."'";
		$this->db->query($sql);
		//删除扩展分类
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE id='".$id."'";
		$this->db->query($sql);
		//删除主题自身的扩展字段
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE ftype='list-".$id."'";
		$tmplist = $this->db->get_all($sql);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$value['id']."'";
				$this->db->query($sql);
			}
			$sql = "DELETE FROM ".$this->db->prefix."fields WHERE ftype='list-".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 删除模块下的附件信息
	 * @参数 $id 主题ID
	 * @参数 $mid 模块ID，为0时，尝试从主题中获取
	**/
	public function delete_res($id,$mid=0)
	{
		if(!$mid){
			$sql = "SELECT module_id FROM ".$this->db->prefix."list WHERE id='".$id."'";
			$rs = $this->db->get_one($sql);
			if(!$rs){
				return false;
			}
			$mid = $rs['module_id'];
		}
		if(!$mid){
			return false;
		}
		$module = $this->model('module')->get_one($mid);
		if(!$module){
			return false;
		}
		$table = $module['mtype'] ? $mid : "list_".$mid;
		$sql = "SELECT * FROM ".$this->db->prefix.$table." WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$flist = $this->model('module')->fields_all($mid);
		if(!$flist){
			return false;
		}
		foreach($flist as $key=>$value){
			if($value['form_type'] != 'upload'){
				continue;
			}
			if(!$rs[$value['identifier']]){
				continue;
			}
			$tmp = explode(',',$rs[$value['identifier']]);
			if($tmp){
				foreach($tmp as $k=>$v){
					if($v && intval($v)){
						$this->model('res')->delete(intval($v));
					}
				}
			}
			$sql = "UPDATE ".$this->db->prefix.$table." SET ".$value['identifier']."='' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	public function subtitle_ids($id)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."list WHERE parent_id='".$id."' AND status=1 ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		return array_keys($rslist);
	}

	public function biz_attrlist($tid,$aid=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."list_attr WHERE tid='".$tid."' ";
		if($aid){
			$sql .= " AND aid='".$aid."'";
		}
		$sql.= " ORDER BY aid ASC,taxis ASC";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得主题的财富基数
	 * @参数 $id 主题ID，数组或字串或数字
	 * @返回 false/数字
	 * @更新时间 2016年11月28日
	**/
	public function integral($id='')
	{
		$id = $this->title_id_to_string($id);
		if(!$id){
			return false;
		}
		$sql = "SELECT SUM(integral) FROM ".$this->db->prefix."list WHERE status=1 AND id IN(".$id.")";
		return $this->db->count($sql);
	}

	public function integral_list($id='')
	{
		$id = $this->title_id_to_string($id);
		if(!$id){
			return false;
		}
		$sql = "SELECT id,integral FROM ".$this->db->prefix."list WHERE status=1 AND id IN(".$id.") AND integral>0";
		$list = $this->db->get_all($sql);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$rslist[$value['id']] = $value['integral'];
		}
		return $rslist;
	}

	/**
	 * 保存电商数据
	 * @参数 $data 数组，里面含有字段：id,unit,price,is_virtual,currency_id,weight,volume
	**/
	public function biz_save($data)
	{
		if(!$data || !is_array($data) || !$data['id']){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."list_biz WHERE id='".$data['id']."'";
		$tmp = $this->db->get_one($sql);
		if($tmp){
			$id = $data['id'];
			unset($data['id']);
			return $this->db->update_array($data,'list_biz',array('id'=>$id));
		}
		return $this->db->insert_array($data,'list_biz','replace');
	}


	private function title_id_to_string($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$list = explode(",",$id);
		$tmp = false;
		foreach($list as $key=>$value){
			if(!$value || !intval($value)){
				continue;
			}
			if(!$tmp){
				$tmp = array();
			}
			$tmp[] = intval($value);
		}
		if(!$tmp){
			return false;
		}
		return implode(",",$tmp);
	}

	public function add_hits($id)
	{
		$sql = "UPDATE ".$this->db->prefix."list SET hits=hits+1 WHERE id='".$id."'";
		return $this->db->query($sql,false);
	}

	public function all_list($condition='',$offset=0,$psize=30)
	{
		$sql  = "SELECT l.*,p.title project_title FROM ".$this->db->prefix."list l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		$sql .= "WHERE l.site_id='".$this->site_id."' AND l.status=1 ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		$sql .= " ORDER BY id DESC LIMIT ".intval($offset).",".intval($psize);
		return $this->db->get_all($sql);
	}

	public function all_total($condition='')
	{
		$sql  = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."project p ON(l.project_id=p.id) ";
		$sql .= "WHERE l.site_id='".$this->site_id."' AND l.status=1 ";
		if($condition){
			$sql .= " AND ".$condition." ";
		}
		return $this->db->count($sql);
	}
}
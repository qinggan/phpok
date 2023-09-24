<?php
/**
 * 表单选择器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年01月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_model_base extends phpok_model
{
	private $info = array();
	private $space_array = array(' ','	','　',',','，');
	public function __construct()
	{
		parent::model();
		$this->_init();
	}

	/**
	 * 表单类型
	 * @参数 $note 是否显示备注，默认为 false
	**/
	public function form_all($note = false)
	{
		if($this->info['form']){
			if($note){
				return $this->info['form'];
			}
			$list = array();
			foreach($this->info['form'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 格式化方式
	 * @参数 $note 是否显示备注，默认为 false
	**/
	public function format_all($note = false)
	{
		if($this->info['format']){
			if($note){
				return $this->info['format'];
			}
			$list = array();
			foreach($this->info['format'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 字段类型
	 * @参数 $note 是否显示备注，默认为 false
	**/
	public function field_all($note = false)
	{
		if($this->info['field']){
			if($note){
				return $this->info['field'];
			}
			$list = array();
			foreach($this->info['field'] as $key=>$value){
				$list[$key] = is_array($value) ? $value['title'] : $value;
			}
			return $list;
		}
		return false;
	}

	/**
	 * 获取自定义表单里的数据
	 * @参数 $type 表单类型，如text，user，upload等
	 * @参数 $field 要获取的数据
	 * @返回 列表数组/数组，为空返回 false
	**/
	public function get($type='',$field='')
	{
		if(!$type || !$field){
			return false;
		}
		$rs = array('form_type'=>$type);
		$rs['identifier'] = $field;
		return $this->lib('form')->get($rs);
	}

	//读取表单下的子项目信息
	public function project_sublist($pid)
	{
		$sql = "SELECT id as val,title FROM ".$this->db->prefix."project WHERE parent_id=".intval($pid)." AND status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	/**
	 * 通过搜索扩展字段的关键字，找回存储的val值
	 * @参数 $fid qinggan_fields 表中的主键ID 或是 数组
	 * @参数 $keywords 要搜索的值
	 * @参数 $ext 扩展表前缀
	**/
	public function search($fid,$keywords,$ext='ext')
	{
		if(!$fid || !$keywords){
			return false;
		}
		if(is_array($fid)){
			$rs = $fid;
			$fid = $rs['id'];
		}else{
			$rs = $this->model('fields')->one($fid);
		}
		if(!$rs){
			return false;
		}
		//不支持搜索
		if(!$rs['search']){
			return false;
		}
		if($rs['ext'] && is_string($rs['ext'])){
			$rs['ext'] = unserialize($rs['ext']);
		}
		if($ext){
			if(substr($ext,-1) != '.'){
				$ext .= '.';
			}
		}
		//区间检索
		if($rs['search'] == 3){
			if(!$rs['search_separator']){
				return false;
			}
			$tmp_format = array('int','float','time');
			if(!in_array($rs['format'],$tmp_format)){
				return false;
			}
			if(strpos($keywords,$rs['search_separator']) === false){
				return false;
			}
			$tmp = explode($rs['search_separator'],$keywords);
			if($rs['format'] == 'time'){
				$min = strtotime($tmp[0]);
				$max = strtotime($tmp[1]);
			}elseif($rs['format'] == 'int'){
				$min = intval($tmp[0]);
				$max = intval($tmp[1]);
			}else{
				$min = floatval($tmp[0]);
				$max = floatval($tmp[1]);
			}
			if($min>$max){
				$min2 = $max;
				$max = $min;
				$min = $min2;
				unset($min2);
			}
			$condition = $ext.$rs['identifier'].">=".$min." AND ".$ext.$rs['identifier']."<=".$max;
			return $condition;
		}
		//文本搜索
		$txtlist = array('text','password','textarea','editor','code_editor','param','pca','url');
		if($rs['form_type'] && in_array($rs['form_type'],$txtlist)){
			$keywords = str_replace($this->space_array,'|',$keywords);
			$tmplist = explode("|",$keywords);
			$tmp_condition = array();
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = $ext.$rs['identifier']."='".$value."'";
				}else{
					$tmp_condition[] = $ext.$rs['identifier']." LIKE '%".$value."%'";
				}
			}
			$condition = implode(" OR ",$tmp_condition);
			return $condition;
		}
		$keywords = str_replace($this->space_array,'|',$keywords);
		$tmplist = explode("|",$keywords);
		$tmp_condition = array();
		//用户信息检索
		if($rs['form_type'] == 'user'){
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = "user='".$value."'";
					$tmp_condition[] = "email='".$value."'";
					$tmp_condition[] = "mobile='".$value."'";
				}else{
					$tmp_condition[] = "user LIKE '%".$value."%'";
					$tmp_condition[] = "email LIKE '%".$value."%'";
					$tmp_condition[] = "mobile LIKE '%".$value."%'";
				}
			}
			$sql = "SELECT id FROM ".$this->db->prefix."user WHERE ".implode(" OR ",$tmp_condition);
			$condition = $ext.$rs['identifier']." IN(".$sql.")";
			return $condition;
		}
		//上传附件检索
		if($rs['form_type'] == 'upload'){
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = "name='".$value."'";
					$tmp_condition[] = "title='".$value."'";
					$tmp_condition[] = "ext='".$value."'";
				}else{
					$tmp_condition[] = "name LIKE '%".$value."%'";
					$tmp_condition[] = "title LIKE '%".$value."%'";
					$tmp_condition[] = "ext LIKE '%".$value."%'";
				}
			}
			$sql = "SELECT id FROM ".$this->db->prefix."res WHERE ".implode(" OR ",$tmp_condition);
			$condition = $ext.$rs['identifier']." IN(".$sql.")";
			return $condition;
		}
		//扩展主题检索
		if($rs['form_type'] == 'title'){
			foreach($tmplist as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				if($rs['search'] == 1){
					$tmp_condition[] = "title='".$value."'";
				}else{
					$tmp_condition[] = "title LIKE '%".$value."%'";
				}
			}
			$sql = "SELECT id FROM ".$this->db->prefix."list WHERE ".implode(" OR ",$tmp_condition);
			$condition = $ext.$rs['identifier']." IN(".$sql.")";
			return $condition;
		}
		//扩展模型检索
		if($rs['form_type'] == 'extitle'){
			if(!$rs['ext'] || !$rs['ext']['form_pid']){
				return false;
			}
			$pid = $rs['ext']['form_pid'];
			$project = $this->model('project')->get_one($pid,false);
			if(!$project || !$project['module']){
				return false;
			}
			$mid = $project['module'];
			$module = $this->model('module')->get_one($mid);
			if(!$module){
				return false;
			}
			$condition = '';
			if(!$module['mtype']){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($rs['search'] == 1){
						$tmp_condition[] = "title='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
					}
				}
				$sql = "SELECT GROUP_CONCAT(id SEPARATOR '|') FROM ".$this->db->prefix."list WHERE ".implode(" OR ",$tmp_condition);
				$condition = "REPLACE(".$ext.$rs['identifier'].",',','|') REGEXP (".$sql.")";
			}
			$flist = $this->model('module')->fields_all($mid,'identifier');
			if($flist){
				foreach($flist as $key=>$value){
					if(!$value['search'] || $value['search'] == 3){
						continue;
					}
					$tmp_condition = array();
					foreach($tmplist as $k=>$v){
						if(!$v || !trim($v)){
							continue;
						}
						$v = trim($v);
						if($value['search'] == 1){
							$tmp_condition[] = $v['identifier']."='".$v."'";
						}else{
							$tmp_condition[] = $v['identifier']." LIKE '%".$v."%'";
						}
					}
					$sql = "SELECT GROUP_CONCAT(id SEPARATOR '|') FROM ".tablename($module)." WHERE ".implode(" OR ",$tmp_condition);
					if($condition){
						$condition .= " OR ";
						$condition .= "REPLACE(".$ext.$rs['identifier'].",',','|') REGEXP (".$sql.")";
					}else{
						$condition = "REPLACE(".$ext.$rs['identifier'].",',','|') REGEXP (".$sql.")";
					}
				}
			}
			if($condition){
				return $condition;
			}
			return false;
		}
		//单选框 下拉 复选框 检索
		$txtlist = array('radio','checkbox','select');
		if(in_array($rs['form_type'],$txtlist)){
			$jz_search = false;
			if($rs['form_type'] == 'radio' && $rs['search'] == 1){
				$jz_search = true;
			}
			if($rs['form_type'] == 'select' && $rs['search'] == 1 && !$rs['ext']['is_multiple']){
				$jz_search = true;
			}
			if(!$rs['ext'] || !$rs['ext']['option_list']){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($jz_search){
						$tmp_condition[] = $ext.$rs['identifier']."='".$value."'";
					}else{
						$tmp_condition[] = $ext.$rs['identifier']." LIKE '%".$value."%'";
					}
				}
				$condition = " ".implode(" OR ",$tmp_condition);
				return $condition;
			}
			$tmp = explode(":",$rs['ext']['option_list']);
			if(!$tmp[1]){
				return false;
			}
			if($tmp[0] == 'opt'){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($jz_search){
						$tmp_condition[] = "title='".$value."'";
						$tmp_condition[] = "val='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
						$tmp_condition[] = "val LIKE '%".$value."%'";
					}
				}
				$sql = "SELECT val FROM ".$this->db->prefix."opt WHERE group_id='".$tmp[1]."' AND (".implode(" OR ",$tmp_condition).")";
				$condition = $ext.$rs['identifier']." IN(".$sql.") ";
				if($jz_search){
					$condition .= " OR ".$ext.$rs['identifier']."='".$value."'";
				}else{
					$condition .= " OR ".$ext.$rs['identifier']." LIKE '%".$value."%'";
				}
				if($rs['form_type'] == 'checkbox' || ($rs['form_type'] == 'select' && $rs['is_multiple'])){
					$condition = $ext.$rs['identifier']." LIKE CONCAT('%',".($sql).",'%')";
				}
				return $condition;
			}
			if($tmp[0] == 'project' || $tmp[0] == 'cate'){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($jz_search){
						$tmp_condition[] = "title='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
					}
				}
				$tbl = $tmp[0] == 'project' ? $this->db->prefix."project" : $this->db->prefix."cate";
				$sql = "SELECT id FROM ".$tbl." WHERE parent_id='".$tmp[1]."' AND (".implode(" OR ",$tmp_condition).")";
				$condition = $ext.$rs['identifier']." IN(".$sql.")";
				if($rs['form_type'] == 'checkbox' || ($rs['form_type'] == 'select' && $rs['is_multiple'])){
					$condition = $ext.$rs['identifier']." LIKE CONCAT('%',".($sql).",'%')";
				}
				return $condition;
			}
			if($tmp[0] == 'title'){
				foreach($tmplist as $key=>$value){
					if(!$value || !trim($value)){
						continue;
					}
					$value = trim($value);
					if($rs['search'] == 1){
						$tmp_condition[] = "title='".$value."'";
					}else{
						$tmp_condition[] = "title LIKE '%".$value."%'";
					}
				}
				$sql = "SELECT id FROM ".$this->db->prefix."list WHERE project_id='".$tmp[1]."' AND (".implode(" OR ",$tmp_condition).")";
				$condition = $ext.$rs['identifier']." IN(".$sql.")";
				if($rs['form_type'] == 'checkbox' || ($rs['form_type'] == 'select' && $rs['is_multiple'])){
					$condition = $ext.$rs['identifier']." LIKE CONCAT('%',".($sql).",'%')";
				}
				return $condition;
			}
			return false;
		}
	}

	/**
	 * 基于模块字段进行格式化
	 * @参数 $rs 模块字段信息，数组
	**/
	public function optlist($rs)
	{
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		list($type,$group_id) = explode(":",$rs['option_list']);
		if($type == 'default'){
			return $this->_optlist_default($rs['ext_select']);
		}
		if($type == 'opt'){
			return $this->_optlist_opt($group_id);
		}
		if($type == 'project'){
			return $this->_optlist_subproject($group_id);
		}
		if($type == 'cate'){
			return $this->_optlist_subcate($group_id);
		}
		if($type == 'user' && $group_id == 'grouplist'){
			return $this->_optlist_usergroup();
		}
		if($type == 'gateway' && $group_id == 'express'){
			return $this->_optlist_express();
		}
		if($type == 'gateway' && $group_id != 'express'){
			return $this->_optlist_gateway();
		}
		//检测项目及模块相关信息
		if($type == 'title' && $group_id){
			$project = $this->model('project')->get_one($group_id,false);
			if(!$project || !$project['module']){
				return false;
			}
			$module = $this->model('module')->get_one($project['module']);
			if(!$module){
				return false;
			}
			if($module['mtype']){
				return $this->_optlist_title_single($group_id,$rs,$project,$module);
			}
			if($module['tbl'] == 'cate'){
				return false;
			}
			return $this->_optlist_title($group_id,$rs,$project,$module);
		}
		return false;
	}

	/**
	 * 格式化内容
	**/
	public function optinfo($val,$rs)
	{
		if(!$rs["option_list"]){
			$rs['option_list'] = 'default:0';
		}
		list($type,$group_id) = explode(":",$rs['option_list']);
		$info = array('val'=>$val,'title'=>$val,'type'=>$type,'data'=>array());
		if($type == 'opt'){
			return $this->_optinfo_opt($info,$group_id);
		}
		if($type == 'project'){
			return $this->_optinfo_project($info);
		}
		if($type == 'title'){
			return $this->_optinfo_title($info,$group_id,$rs);
		}
		if($type == 'cate'){
			return $this->_optinfo_cate($info);
		}
		if($type == 'user' && $group_id == 'grouplist'){
			return $this->_optinfo_user_grouplist($info);
		}
		if($type == 'gateway' && $group_id == 'express'){
			return $this->_optinfo_express($info);
		}
		if($type == 'gateway' && $group_id != 'express'){
			return $this->_optinfo_gateway($info);
		}
		if(!$group_id && $rs['ext_select']){
			$tmplist = explode("\n",$rs['ext_select']);
			foreach($tmplist as $key => $value){
				$value = trim($value);
				if(!$value){
					continue;
				}
				$tmp = explode(":",$value);
				if($tmp[1] && $tmp[0] == $info['val']){
					$info['title'] = $tmp[1];
					break;
				}
			}
			return $info;
		}
		return $info;
	}

	private function _init()
	{
		$this->info = $this->lib('xml')->read($this->dir_phpok.'system.xml');
		$dlist = $this->lib('file')->ls($this->dir_extension.'ext-form');
		if(!$dlist){
			return $this->info;
		}
		foreach($dlist as $key=>$value){
			if(is_file($value)){
				continue;
			}
			$cfile = $value.'/config.xml';
			if(!file_exists($cfile)){
				continue;
			}
			$basename = basename($value);
			$tmp = $this->lib('xml')->read($cfile);
			if(!$tmp){
				continue;
			}
			$this->info['form'][$basename] = $tmp;
		}
		return $this->info;
	}

	private function _optinfo_gateway($info)
	{
		$tmp = $this->model('gateway')->get_one($info['val']);
		if(!$tmp){
			$info['title'] = false;
			$info['data'] = false;
			return $info;
		}
		$info['title'] = $tmp['title'];
		$info['data'] = $tmp;
		return $info;
	}

	private function _optinfo_express($info)
	{
		$tmp = $this->model('express')->get_one($info['val']);
		if(!$tmp){
			$info['title'] = false;
			$info['data'] = false;
			return $info;
		}
		$info['title'] = $tmp['title'];
		$info['data'] = $tmp;
		return $info;
	}

	private function _optinfo_user_grouplist($info)
	{
		$tmp = $this->model('usergroup')->get_one($info['val']);
		if(!$tmp){
			$info['title'] = false;
			$info['data'] = false;
			return $info;
		}
		$info['title'] = $tmp['title'];
		$info['data'] = $tmp;
		return $info;
	}

	private function _optinfo_cate($info)
	{
		$tmp = $this->model('cate')->cate_info($info['val'],false);
		if(!$tmp || !$tmp['status']){
			$info['title'] = false;
			$info['data'] = false;
			return $info;
		}
		$info['title'] = $tmp['title'];
		$info['data'] = $tmp;
		return $info;
	}

	private function _optinfo_title($info,$group_id=0,$rs=array())
	{
		if(!$info || !$info['val']){
			return false;
		}
		if(!$group_id){
			return $info;
		}
		$project = $this->model('project')->get_one($group_id,false);
		if(!$project || !$project['module']){
			$info['data'] = false;
			return $info;
		}
		$module = $this->model('module')->get_one($project['module']);
		if($module['mtype']){
			$tmp = $this->model('list')->single_one($info['val'],$module);
		}else{
			$tmp = $this->model('list')->call_one($info['val']);
		}
		if(!$tmp){
			$info['data'] = false;
			return $info;
		}
		$field_show = $rs['field_show'] ? $rs['field_show'] : 'id';
		$info['title'] = $tmp[$field_show] ? $tmp[$field_show] : $tmp['id'];
		$info['data'] = $tmp;
		return $info;
	}

	/**
	 * 读取项目信息，不读扩展表
	**/
	private function _optinfo_project($info)
	{
		$tmp = $this->model('project')->get_one($info['val'],false);
		if(!$tmp || !$tmp['status']){
			return false;
		}
		$info['title'] = $tmp['title'];
		$info['data'] = $tmp;
		return $info;
	}

	private function _optinfo_opt($info,$group_id=0)
	{
		$val = $info['val'];
		$group_rs = $this->model('opt')->group_one($group_id);
		//检查是否是联动数据
		if($group_rs && $group_rs['link_symbol'] && strpos($val,$group_rs['link_symbol']) !== false){
			$list = explode($group_rs['link_symbol'],$val);
			$list2 = array();
			$parent_id = 0;
			foreach( $list as $key => $value ){
				if(!$value || !trim($value)){
					continue;
				}
				$value = trim($value);
				$condition = "val='".$value."' AND group_id='".$group_id."' AND parent_id='".$parent_id."'";
				$opt_data = $this->model('opt')->opt_one_condition($condition);
				if($opt_data){
					$list2[$key] = array('val'=>$value,'title'=>$opt_data['title']);
					$parent_id = $opt_data['id'];
				}
			}
			$tmp = array('title'=>array(),'val'=>array(),'data'=>$list2);
			$tmp['type'] = $info['type'];
			foreach($list2 as $key=>$value){
				if($value && is_array($value)){
					$tmp['title'][$key] = $value['title'];
					$tmp['val'][$key] = $value['val'];
				}
			}
			return $tmp;
		}
		$tmp = $this->model('opt')->opt_val($group_id,$val);
		if(!$tmp){
			return $info;
		}
		$info['title'] = $tmp['title'];
		$info['data'] = $tmp;
		return $info;
	}

	/**
	 * 读取独立表信息
	**/
	private function _optlist_title_single($group_id,$rs,$project,$module)
	{
		$orderby ='l.sort ASC,l.dateline DESC,l.id DESC';
		$sql  = " SELECT l.*,c.title catename FROM ".tablename($module)." l ";
		$sql .= " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
		$sql .= " WHERE l.project_id='".$group_id."'";
		$sql .= " AND l.status=1 AND l.hidden=0 ORDER BY l.sort ASC,l.id DESC";
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		$field_value = $rs['field_value'] ? $rs['field_value'] : 'id';
		$field_show = $rs['field_show'] ? $rs['field_show'] : 'id';
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value[$field_value],"title"=>$value[$field_show],'cate_id'=>$value['cate_id'],'catename'=>$value['catename']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 读主题的信息
	**/
	private function _optlist_title($group_id,$rs,$project,$module)
	{
		//检测主字段
		if(!$rs['field_show']){
			$rs['field_show'] = 'title';
		}
		if(!$rs['field_value']){
			$rs['field_value'] = 'id';
		}
		$project = $this->model('project')->get_one($group_id,false);
		if(!$project || !$project['status'] || !$project['module']){
			return false;
		}
		$module = $this->model('module')->get_one($project['module']);
		$orderby = $project['orderby'] ? $project['orderby'] : 'l.sort ASC,l.dateline DESC,l.id DESC';
		if($module['mtype']){
			$field = "l.cate_id,c.title catename,l.".$rs['field_value']." id, l.".$rs['field_show']." title";
			$sql  = " SELECT ".$field." FROM ".tablename($module)." l ";
			$sql .= " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
			$sql .= " WHERE l.project_id='".$group_id."' AND l.status=1 AND l.hidden=0 ";
			$sql .= " ORDER BY ".$orderby;
		}else{
			$fields = $this->db->list_fields('list');
			$is_ext = true;
			if(in_array($rs['field_show'],$fields) && in_array($rs['field_value'],$fields)){
				$is_ext = false;
			}
			$field = 'l.cate_id,c.title catename';
			if(in_array($rs['field_value'],$fields)){
				$field .= ",l.".$rs['field_value']." id";
			}else{
				$field .= ",ext.".$rs['field_value']." id";
			}
			if(in_array($rs['field_show'],$fields)){
				$field .= ",l.".$rs['field_show']." title";
			}else{
				$field .= ",ext.".$rs['field_show']." title";
			}
			$sql  = " SELECT ".$field." FROM ".$this->db->prefix."list l ";
			$sql .= " LEFT JOIN ".$this->db->prefix."cate c ON(l.cate_id=c.id) ";
			$sql .= " LEFT JOIN ".tablename($module)." ext ON(l.id=ext.id) ";
			$sql .= " WHERE l.project_id='".$group_id."' AND l.status=1 AND l.hidden=0 ";
			$sql .= " ORDER BY ".$orderby;
		}
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title'],'cate_id'=>$value['cate_id'],'catename'=>$value['catename']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 取得其他网关信息
	**/
	private function _optlist_gateway($group_id)
	{
		$tmplist = $this->model('gateway')->all($group_id);//其他网关参数
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array('val'=>$value['id'],'title'=>$value['title']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	
	/**
	 * 取得物流相关信息
	**/
	private function _optlist_express()
	{
		$tmplist = $this->model('express')->get_all();
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array('val'=>$value['id'],'title'=>$value['title']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}


	/**
	 * 读取用户组
	**/
	private function _optlist_usergroup()
	{
		$tmplist = $this->model('usergroup')->get_all('status=1');
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 读取子分类
	**/
	private function _optlist_subcate($group_id)
	{
		$tmplist = $this->model('cate')->catelist_sonlist($group_id,false);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title'],'parent_id'=>$value['parent_id']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 读取子项目信息
	**/
	private function _optlist_subproject($group_id)
	{
		$tmplist = $this->model('project')->project_sonlist($group_id);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 基于自定义表单中获取到的数据
	**/
	private function _optlist_opt($group_id)
	{
		return $this->model('opt')->opt_all("group_id=".$group_id);
	}

	/**
	 * 自定义默认的选项
	**/
	private function _optlist_default($info)
	{
		$list = explode("\n",$info);
		$rslist = array();
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			if(strpos($value,':') !== false){
				$tmp2 = explode(":",$value);
				if(!$tmp2[1]){
					$tmp2[1] = $tmp2[0];
				}
				$rslist[] = array('val'=>$tmp2[0],'title'=>$tmp2[1]);
			}else{
				$rslist[] = array('val'=>trim($value),'title'=>trim($value));
			}
		}
		return $rslist;
	}
}
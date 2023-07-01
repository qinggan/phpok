<?php
/**
 * 字段增删查改
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年05月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 删除字段
	**/
	public function del($id=0)
	{
		if(!$id){
			return false;
		}
		if(is_numeric($id)){
			$rs = $this->one($id);
			if(!$rs){
				return false;
			}
		}else{
			$rs = $id;
		}
		$this->del_module_fields($rs);
		if($rs['ftype'] == 'user'){
			$field = $rs["identifier"];
			$sql = "ALTER TABLE ".$this->db->prefix."user_ext DROP `".$field."`";
			$this->db->query($sql);
		}
		//删除扩展存储器里的字段
		if(!is_numeric($rs['ftype']) && $rs['ftype'] != 'default' && $rs['ftype'] != 'user'){
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id='".$rs['id']."'";
			$this->db->query($sql);
		}
		$this->fields_ext_delete($rs['id']);
		$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 读取模块下的所有扩展字段信息，返回的 ext 信息已自动转成数组模式
	 * @参数 $ftype 模块ID 或 模块类型
	 * @参数 $primary 自定义 key 键，默认为空，支持 id 和 identifier
	**/
	public function flist($ftype,$primary='')
	{
		if(!$ftype){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields ORDER BY taxis ASC,id DESC";
		$cache_id = $this->cache->id($sql.'-'.$primary);
		if($cache_id){
			$info = $this->cache->get($cache_id);
			if($info){
				if($info[$ftype]){
					return $info[$ftype];
				}
				return false;
			}
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$datalist = array();
		foreach($rslist as $key=>$value){
			$ext = $this->fields_ext_all($value['id']);
			if($ext){
				$value = array_merge($ext,$value);
			}
			if(!isset($datalist[$value['ftype']])){
				$datalist[$value['ftype']] = array();
			}
			if($primary && isset($value[$primary])){
				$datalist[$value['ftype']][$value[$primary]] = $value;
			}else{
				$datalist[$value['ftype']][] = $value;
			}
		}
		if($cache_id){
			$this->cache->key_list($cache_id,array($this->db->prefix."fields",$this->db->prefix."fields_ext"));
			$this->cache->save($cache_id,$datalist);
		}
		if($datalist[$ftype]){
			return $datalist[$ftype];
		}
		return false;
	}

	public function fields_count($words,$type="")
	{
		if(!$words) $words = "id,identifier";
		$sql = "SELECT count(id) FROM ".$this->db->prefix."fields ";
		$list = explode(",",$words);
		$list = array_unique($list);
		$words = implode("','",$list);
		$sql .= " WHERE identifier NOT IN ('".$words."') ";
		if($type)
		{
			$sql .= " AND area LIKE '%".$type."%'";
		}
		return $this->db->count($sql);
	}

	public function fields_ext_save($data,$fields_id=0)
	{
		if(!$fields_id){
			return false;
		}
		foreach($data as $key=>$value){
			if($value && is_array($value)){
				$value = serialize($value);
			}
			$sql = "DELETE FROM ".$this->db->prefix."fields_ext WHERE keyname='".$key."' AND fields_id='".$fields_id."'";
			$this->db->query($sql);
			$array = array('fields_id'=>$fields_id,'keyname'=>$key,'keydata'=>$value);
			$this->db->insert($array,'fields_ext');
		}
		return true;
	}

	public function fields_ext_delete($fields_id=0)
	{
		if(!$fields_id){
			return false;
		}
		$sql = "DELETE FROM ".$this->db->prefix."fields_ext WHERE fields_id='".$fields_id."'";
		return $this->db->query($sql);
	}

	public function fields_ext_all($fields_id=0)
	{
		if(!$fields_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields_ext";
		$cache_id = $this->cache->id($sql);
		if($cache_id){
			$info = $this->cache->get($cache_id);
			if($info){
				if($info[$fields_id]){
					return $info[$fields_id];
				}
				return false;
			}
		}
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$datalist = array();
		foreach($rslist as $key=>$value){
			$tmp = $value['keydata'];
			if(strpos($tmp,'{') !== false && strpos($tmp,':') !== false && substr($tmp,-1) == '}'){
				$tmp = unserialize($tmp);
			}
			if(!isset($datalist[$value['fields_id']])){
				$datalist[$value['fields_id']] = array();
			}
			$datalist[$value['fields_id']][$value['keyname']] = $tmp;
		}
		if($cache_id){
			$this->cache->key_list($cache_id,$this->db->prefix."fields_ext");
			$this->cache->save($cache_id,$datalist);
		}
		if($datalist[$fields_id]){
			return $datalist[$fields_id];
		}
		return false;
	}

	/**
	 * 取得指定页面下的字段
	**/
	public function fields_list($words="",$offset=0,$psize=40,$type="")
	{
		if(!$words){
			$words = "id,identifier";
		}
		if(is_string($words)){
			$words = explode(",",$words);
		}
		$rslist = $this->_all();
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(in_array($key,$words)){
				unset($rslist[$key]);
			}
		}
		if(!$rslist){
			return false;
		}
		return $rslist;
	}

	public function get_all($condition='',$offset=0,$psize=30,$pri='')
	{
		$sql = "SELECT * FROM ".$this->db->prefix."fields ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		if($psize && intval($psize)){
			$offset = intval($offset);
			$sql .= " LIMIT ".$offset.",".intval($psize);
		}
		$rslist = $this->db->get_all($sql,$pri);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			$ext = $this->fields_ext_all($value['id']);
			if($ext){
				$value = array_merge($ext,$value);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function get_from_identifier($identifier,$module)
	{
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' AND ftype='".$module."'";
		$info = $this->db->get_one($sql);
		if(!$info){
			return false;
		}
		return $this->one($info['id']);
	}

	public function get_list($id)
	{
		if(!$id){
			return false;
		}
		$list = $this->_ids($id,true);
		if(!$list){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$tmp = $this->one($value);
			if($tmp){
				$rslist[] = $tmp;
			}
		}
		return $rslist;
	}

	public function groups()
	{
		if(file_exists($this->dir_data.'xml/fields-group.xml')){
			return $this->lib('xml')->read($this->dir_data.'xml/fields-group.xml',true);
		}
		return array('main'=>'主层','ext'=>'扩展层');
	}

	//判断字段是否被使用了
	public function is_has_sign($identifier,$id=0)
	{
		if(!$identifier){
			return true;
		}
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' ";
		if($id){
			$sql .= " AND id !='".$id."' ";
		}
		$rs = $this->db->get_one($sql);
		if($rs){
			return true;
		}
		$idlist = array("title","phpok","identifier","app");
		$idlist = $this->_rslist("list",$idlist);
		if($idlist){
			$idlist = array_unique($idlist);
			if(in_array($identifier,$idlist)){
				return true;
			}
		}
		return false;
	}

	public function list_fields()
	{
		return $this->db->list_fields('list');
	}

	public function next_taxis($module)
	{
		$sql = "SELECT max(taxis) as taxis FROM ".$this->db->prefix."fields WHERE ftype='".$module."' AND taxis<255";
		$rs = $this->db->get_one($sql);
		return $this->return_next_taxis($rs);
	}

	/**
	 * 读取 qigngan_fields 表下的一条字段配置信息，返回的 ext 信息已经自动转成数组
	 * @参数 $id 主键ID
	**/
	public function one($id)
	{
		if(!$id || !intval($id)){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id=".intval($id);
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs['ext']){
			$rs['ext'] = unserialize($rs['ext']);
		}
		//检测扩展
		$ext = $this->fields_ext_all($rs['id']);
		if($ext){
			$rs = array_merge($ext,$rs);
		}
		//检测扩展文件是否存在
		return $rs;
	}

	/**
	 * 保存字段
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['module_id'] && !isset($data['ftype'])){
			$data['ftype'] = $data['module_id'];
		}
		if(isset($data['module_id'])){
			unset($data['module_id']);
		}
		$ext = array();
		if($data['ext']){
			if(is_string($data['ext'])){
				$data['ext'] = serialize($data['ext']);
			}
			$ext = $data['ext'];
			$data['ext'] = '';
		}
		$fields = $this->db->list_fields('fields');
		$xmldata = array();
		foreach($data as $key=>$value){
			if(!in_array($key,$fields)){
				if(!$xmldata){
					$xmldata = array();
				}
				$xmldata[$key] = $value;
				unset($data[$key]);
			}
		}
		foreach($ext as $key=>$value){
			$xmldata[$key] = $value;
		}
		if($id){
			if($data && count($data)>0){
				$status = $this->db->update_array($data,"fields",array("id"=>$id));
				if(!$status){
					return false;
				}
			}
			if($xmldata && count($xmldata)>0){
				$this->fields_ext_save($xmldata,$id);
			}
			return $id;
		}
		$insert_id = $this->db->insert_array($data,"fields");
		if(!$insert_id){
			return false;
		}
		if($xmldata && count($xmldata)>0){
			$this->fields_ext_save($xmldata,$insert_id);
		}
		return $insert_id;
	}

	public function tbl_fields($tbl)
	{
		return $this->_rslist($tbl);
	}

	//取得数据表字段设置的字段类型
	public function type_all()
	{
		$array = array(
			"varchar"=>"字符串",
			"int"=>"整型",
			"float"=>"浮点型",
			"date"=>"日期",
			"datetime"=>"日期时间",
			"longtext"=>"长文本",
			"longblob"=>"二进制信息"
		);
		return $array;
	}

	private function _all()
	{
		$flist = $this->lib('file')->ls($this->dir_data.'xml/fields/');
		if(!$flist){
			return false;
		}
		$rslist = array();
		foreach($flist as $key=>$value){
			$rs = $this->lib('xml')->read($value);
			$rslist[$rs['identifier']] = $rs;
		}
		ksort($rslist);
		return $rslist;
	}

	private function _rslist($tbl,$idlist=array())
	{
		$sql = "SHOW FIELDS FROM ".$this->db->prefix.$tbl;
		$rslist = $this->db->get_all($sql);
		if($rslist){
			$idlist = array();
			foreach($rslist AS $key=>$value){
				$idlist[] = $value["Field"];
			}
			return $idlist;
		}else{
			return false;
		}
	}

	protected function del_module_fields($rs)
	{
		if(!is_numeric($rs['ftype'])){
			return false;
		}
		$module = $this->model('module')->get_one($rs['ftype']);
		if(!$module){
			return false;
		}
			//检查表
		$table = $this->db->prefix.$module['tbl'].'_'.$module['id'];
		if($module['mtype']){
			$table = $this->db->prefix.$module['id'];
		}
		$tblist = $this->db->list_tables();
		if(!in_array($table,$tblist)){
			return false;
		}
		$fields = $this->db->list_fields($table);
		if(!in_array($rs['identifier'],$fields)){
			return false;
		}
		//删除字段信息
		$this->db->delete_table_fields($table,$rs['identifier']);
	}

}
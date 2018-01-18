<?php
/**
 * 常用字段增删查改
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
	}

	public function get_one($id,$identifier='id')
	{
		$filename = $this->dir_data.'xml/fields/'.$id.'.xml';
		if(!file_exists($filename)){
			return false;
		}
		return $this->lib('xml')->read($filename);
	}

	public function get_all()
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
		return $rslist;
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
		$rslist = $this->get_all();
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

	function fields_count($words,$type="")
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

	function get_list($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id IN(".$id.") ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}

	//判断字段是否被使用了
	function is_has_sign($identifier,$id=0)
	{
		if(!$identifier) return true;
		$sql = "SELECT identifier FROM ".$this->db->prefix."fields WHERE identifier='".$identifier."' ";
		if($id)
		{
			$sql .= " AND id !='".$id."' ";
		}
		$rs = $this->db->get_one($sql);
		if($rs)
		{
			return true;
		}
		# 检查核心表的字段ID
		$idlist = array("title","phpok","identifier");
		$idlist = $this->_rslist("list",$idlist);
		if($idlist){
			$idlist = array_unique($idlist);
			if(in_array($identifier,$idlist)){
				return true;
			}
		}
		return false;
	}

	public function tbl_fields($tbl)
	{
		return $this->_rslist($tbl);
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

	/**
	 * 保存常用字段
	 * @参数 $data 要保存的数据信息
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($data['ext'] && is_string($data['ext'])){
			$data['ext'] = unserialize($data['ext']);
		}
		$filename = $this->dir_data.'xml/fields/'.$data['identifier'].'.xml';
		$this->lib('xml')->save($data,$filename);
		return true;
	}

	//删除字段
	public function delete($id)
	{
		$filename = $this->dir_data.'xml/fields/'.$id.'.xml';
		if(file_exists($filename)){
			$this->lib('file')->rm($filename);
		}
		return true;
	}

	//取得数据表字段设置的字段类型
	function type_all()
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

	public function list_fields()
	{
		return $this->db->list_fields('list');
	}	
}
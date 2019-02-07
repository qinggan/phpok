<?php
/**
 * 字段增删查改
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年05月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_model_base extends phpok_model
{
	function __construct()
	{
		parent::model();
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
		return $rs;
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
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE ftype='".$ftype."' ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,$primary);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($value['ext']){
				$value['ext'] = unserialize($value['ext']);
				$rslist[$key] = $value;
			}
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

	public function get_list($id)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."fields WHERE id IN(".$id.") ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
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
		return $this->db->get_all($sql,$pri);
	}
}
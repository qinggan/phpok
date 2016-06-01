<?php
/***********************************************************
	Filename: {phpok}/model/res.php
	Note	: 资源读取
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-27 13:09
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_model_base extends phpok_model
{
	var $img_type_list;
	function __construct()
	{
		parent::model();
		$this->img_type_list = array("jpg","gif","png","jpeg");
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	# 取得资源信息
	function get_one($id,$is_ext=false)
	{
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id='".$id."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		if($rs["attr"])
		{
			$attr = unserialize($rs["attr"]);
			$rs["attr"] = $attr;
		}
		//判断附件方案
		$list = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$list) && $is_ext)
		{
			$gd = $this->get_gd_pic($id);
			$rs["gd"] = $gd;
		}
		return $rs;
	}

	//取得扩展GD图片
	//id，附件ID，多个ID用英文逗号隔开
	//is_list，这里人工设置是否多个附件ID，设为true时将写成数组
	//
	function get_gd_pic($id,$is_list=false)
	{
		if(!$id) return false;
		$sql = "SELECT e.*,gd.identifier FROM ".$this->db->prefix."res_ext e ";
		$sql.= " JOIN ".$this->db->prefix."gd gd ON(e.gd_id=gd.id) ";
		$sql.= " WHERE e.res_id IN(".$id.") ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		$idlist = explode(",",$id);
		$list = array();
		foreach($idlist AS $key=>$value)
		{
			foreach($rslist AS $k=>$v)
			{
				if($v["res_id"] == $value)
				{
					$list[$value][$v["identifier"]] = $v;
				}
			}
		}
		$return_rs = $is_list ? $list : $list[$idlist[0]];
		return $return_rs;
	}

	# 取得单个扩展图片的GD
	function get_pic($res_id,$gd_id)
	{
		if(!$res_id && !$gd_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."res_ext WHERE res_id='".$res_id."' AND gd_id='".$gd_id."'";
		return $this->db->get_one($sql);
	}

	function get_name($name)
	{
		if(!$name) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE name='".$name."'";
		return $this->db->get_one($sql);
	}

	# 取得资源列表
	function get_list($condition="",$offset=0,$psize=20,$is_ext=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		if(!$is_ext)
		{
			$rslist = $this->db->get_all($sql);
			if(!$rslist) return false;
			foreach($rslist AS $key=>$value)
			{
				$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
				$rslist[$key] = $value;
			}
			return $rslist;
		}
		else
		{
			$rslist = $this->db->get_all($sql,"id");
			if(!$rslist) return false;
			$id = implode(",",array_keys($rslist));
			$extlist = $this->get_gd_pic($id,true);
			$tmplist = array();
			foreach($rslist AS $key=>$value)
			{
				$value["gd"] = $extlist[$value["id"]];
				$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
				$tmplist[] = $value;
			}
			return $tmplist;
		}
	}

	# 取得数量
	function get_list_from_id($id,$is_ext=false)
	{
		if(!$id) return false;
		if(is_array($id)) $id = implode(",",$id);
		$list = explode(",",$id);
		foreach($list AS $key=>$value)
		{
			if(intval($value))
			{
				$list[$key] = intval($value);
			}
		}
		$id = implode(",",$list);
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$id.") ORDER BY SUBSTRING_INDEX('".$id."',id,1)";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		if(!$is_ext) return $rslist;
		$id = implode(",",array_keys($rslist));
		$extlist = $this->get_gd_pic($id,true);
		foreach($rslist AS $key=>$value)
		{
			$value["gd"] = $extlist[$value["id"]];
			$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	function delete_gd_id($id,$root_dir="/")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_ext WHERE gd_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				if($value["filename"] && file_exists($root_dir.$value["filename"]) && is_file($root_dir.$value["filename"]))
				{
					unlink($root_dir.$value["filename"]);
				}
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE gd_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	# 取得资源数量
	function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."res ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	# 取得附件信息
	function get_one_filename($filename,$is_ext=true)
	{
		if(!$filename) return false;
		$sql = "SELECT id FROM ".$this->db->prefix."res WHERE filename='".$filename."' ORDER BY id ASC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		return $this->get_one($rs["id"],$is_ext);
	}

	# 删除资源
	function delete($id)
	{
		
		$rs = $this->get_one($id);
		if(!$rs) return false;
		if(file_exists($rs["filename"]) && is_file($rs["filename"]))
		{
			unlink($rs["filename"]);
		}
		if($rs["ico"] && substr($rs["ico"],0,7) != "images/" && file_exists($this->dir_root.$rs["ico"]))
		{
			unlink($this->dir_root.$rs["ico"]);
		}
		# 删除扩展资源方案
		$this->ext_delete($id);
		# 删除主表记录
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	function delete_ext($id)
	{
		return $this->ext_delete($id);
	}

	# 存储图片信息
	function save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		if($id){
			return $this->db->update_array($data,"res",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"res");
		}
	}

	function save_ext($data)
	{
		if(!$data || !is_array($data)) return false;
		return $this->db->insert_array($data,"res_ext","replace");
	}

	# 删除扩展图片方案
	function ext_delete($id)
	{
		
		$sql = "SELECT * FROM ".$this->db->prefix."res_ext WHERE res_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		foreach($rslist AS $key=>$value)
		{
			if(is_file($this->dir_root.$value["filename"]))
			{
				unlink($this->dir_root.$value["filename"]);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE res_id='".$id."'";
		return $this->db->query($sql);
	}

	# 取得资源分类
	function cate_one($id=0)
	{
		if($id)
		{
			$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
			return $this->db->get_one($sql);
		}
		else
		{
			return $this->cate_default();
		}
	}

	function cate_one_from_title($title)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE title='".$title."'";
		return $this->db->get_one($sql);
	}

	# 取得全部分类
	function cate_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC ";
		return $this->db->get_all($sql);
	}

	# 取得默认分类ID
	function cate_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default='1'";
		return $this->db->get_one($sql);
	}

	function cate_default_set($id)
	{
		if(!$id) return false;
		
		$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default='0' WHERE is_default='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	# 分类存储操作
	function cate_save($data,$id=0)
	{
		if(!$data || !is_array($data)) return false;
		
		if($id)
		{
			return $this->db->update_array($data,"res_cate",array("id"=>$id));
		}
		else
		{
			return $this->db->insert_array($data,"res_cate");
		}
	}

	# 删除图片附件分类
	function cate_delete($id,$default_id=0)
	{
		
		$sql = "DELETE FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
		$this->db->query($sql);
		if($default_id)
		{
			$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$default_id."' WHERE cate_id='".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	# 更新附件名称
	function update_title($title,$id)
	{
		if(!$id || !$title) return false;
		
		$sql = "UPDATE ".$this->db->prefix."res SET title='".$title."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	# 更新附件备注
	function update_note($note,$id)
	{
		if(!$id) return false;
		$sql = "UPDATE ".$this->db->prefix."res SET note='".$note."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	# 取得所有的附件类型
	function type_list()
	{
		$sql = "SELECT id,title,filetypes,typeinfo,gdall,filemax FROM ".$this->db->prefix."res_cate ORDER BY is_default DESC,id ASC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			$array = array("picture"=>array("name"=>"图片","swfupload"=>"*.jpg;*.png;*.gif;*.jpeg","ext"=>"jpg,png,gif,jpeg","gd"=>1));
			return $array;
		}
		$array = array();
		foreach($rslist as $key=>$value){
			$types = $value['filetypes'] ? explode(",",$value['filetypes']) : array('jpg','gif','png');
			$swflist = array();
			foreach($types as $k=>$v){
				$swflist[] = '*.'.$v;
			}
			$value['swfupload'] = implode(";",$swflist);
			$value['name'] = $value['typeinfo'] ? $value['typeinfo'] : $value['title'];
			$array[$value['id']] = array('name'=>$value['name'],'swfupload'=>$value['swfupload'],'ext'=>implode(",",$types),'gd'=>1);
		}
		return $array;
	}

	function pl_delete($id)
	{
		if(!$id) return false;
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE res_id IN(".$id.") ";
		$this->db->query($sql);
		return true;
	}

	function edit_pic_list($condition="",$offset=0,$psize=30,$gd=false)
	{
		if($gd)
		{
			$sql = "SELECT e.filename,res.id,res.title,res.ico,res.addtime FROM ".$this->db->prefix."res_ext e ";
			$sql.= "JOIN ".$this->db->prefix."res res ON(e.res_id=res.id) ";
		}
		else
		{
			$sql = "SELECT res.filename,res.id,res.title,res.ico,res.addtime FROM ".$this->db->prefix."res res ";
		}
		if($condition)
		{
			$sql.= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY res.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	function edit_pic_total($condition="",$gd=false)
	{
		
		if($gd)
		{
			$sql = " SELECT count(res.id) ";
			$sql.= "FROM ".$this->db->prefix."res_ext e ";
			$sql.= "JOIN ".$this->db->prefix."res res ON(e.res_id=res.id) ";
		}
		else
		{
			$sql = "SELECT id FROM ".$this->db->prefix."res ";
		}
		if($condition)
		{
			$sql.= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	//读取附件信息
	function reslist($string,$ext=true)
	{
		if(!$string) return false;
		if(is_array($string))
		{
			$string = array_unique($string);
			$string = implode(",",$string);
		}
		else
		{
			$list = explode(",",$string);
			foreach($list AS $key=>$value)
			{
				$value = intval($value);
				if(!$value)
				{
					unset($list[$key]);
				}
			}
			$list = array_unique($list);
			$string = implode(",",$list);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$string.") ORDER BY id ASC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist) return false;
		if(!$ext) return $rslist;
		$sql = "SELECT e.*,gd.identifier FROM ".$this->db->prefix."res_ext e ";
		$sql.= " JOIN ".$this->db->prefix."gd gd ON(e.gd_id=gd.id) ";
		$sql.= " WHERE e.res_id IN(".$string.") ";
		$extlist = $this->db->get_all($sql);
		if(!$extlist) return $rslist;
		foreach($extlist AS $key=>$value)
		{
			$rslist[$value["res_id"]]["gd"][$value["identifier"]] = $value;
		}
		return $rslist;
	}

	public function gd_update($id)
	{
		if(!$id){
			return false;
		}
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		if($rs['cate_id']){
			$cate_rs = $this->model('rescate')->get_one($rs['cate_id']);
		}
		if(!$cate_rs){
			$cate_rs = $this->model('rescate')->get_default();
			if(!$cate_rs){
				return false;
			}
			$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$cate_rs['id']."' WHERE id='".$id."'";
			$this->db->query($sql);
		}
		$arraylist = array("jpg","gif","png","jpeg");
		if(!in_array($rs['ext'],$arraylist)){
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!is_file($this->dir_root.$ico)){
				$ico = "images/filetype-large/unknown.jpg";
			}
			$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$id."'";
			$this->db->query($sql);
			return true;
		}
		$ico = '';
		if($cate_rs['ico']){
			$ico = $this->lib('gd')->thumb($this->dir_root.$rs['filename'],$id);
			if($ico){
				$ico = $rs['folder'].$ico;
			}
		}
		if(!$ico){
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!file_exists($ico)){
				$ico = "images/filetype-large/unknown.jpg";
			}
		}
		$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$id."'";
		$this->db->query($sql);
		$this->ext_delete($id);
		$gdlist = $this->model('gd')->get_all('id');
		if(!$gdlist){
			return true;
		}
		if(!$cate_rs['gdall'] && !$cate_rs['gdtypes']){
			return true;
		}
		if(!$cate_rs['gdall'] && $cate_rs['gdtypes']){
			$tmp = explode(",",$cate_rs['gdtypes']);
			$tmplist = false;
			foreach($tmp as $key=>$value){
				if($gdlist[$value]){
					if(!$tmplist){
						$tmplist = array();
					}
					$tmplist[$value] = $gdlist[$value];
				}
			}
			if(!$tmplist){
				return true;
			}
			$gdlist = $tmplist;
		}
		foreach($gdlist AS $key=>$value){
			$array = array();
			$array["res_id"] = $rs['id'];
			$array["gd_id"] = $value["id"];
			$array["filetime"] = $this->time;
			$gd_tmp = $this->lib('gd')->gd($this->dir_root.$rs["filename"],$id,$value);
			if($gd_tmp){
				$array["filename"] = $rs["folder"].$gd_tmp;
				$this->db->insert_array($array,"res_ext","replace");
			}
		}
		return true;
	}

	public function update_cate($id,$newcate)
	{
		$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$newcate."' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}
}
?>
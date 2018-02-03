<?php
/**
 * 附件管理基础类
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年01月17日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_model_base extends phpok_model
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 取得资源信息
	 * @参数 $id 附件ID
	 * @参数 $is_ext 是否读取扩展
	 * @返回 数组
	**/
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

	/**
	 * 取得扩展GD图片
	 * @参数 $id，附件ID，多个ID用英文逗号隔开
	 * @参数 $is_list，这里人工设置是否多个附件ID，设为true时将写成数组
	 * @返回 多维数组或一维数组（受$is_list控制）
	**/
	public function get_gd_pic($id,$is_list=false)
	{
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$sql = "SELECT e.*,gd.identifier FROM ".$this->db->prefix."res_ext e ";
		$sql.= " JOIN ".$this->db->prefix."gd gd ON(e.gd_id=gd.id) ";
		$sql.= " WHERE e.res_id IN(".$id.") ";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$idlist = explode(",",$id);
		$list = array();
		foreach($idlist AS $key=>$value){
			foreach($rslist AS $k=>$v){
				if($v["res_id"] == $value){
					$list[$value][$v["identifier"]] = $v;
				}
			}
		}
		$return_rs = $is_list ? $list : $list[$idlist[0]];
		return $return_rs;
	}

	/**
	 * 取得单个扩展图片的GD
	 * @参数 $res_id 附件ID
	 * @参数 $gd_id GD库ID
	 * @返回 数组
	**/
	public function get_pic($res_id,$gd_id)
	{
		if(!$res_id && !$gd_id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."res_ext WHERE res_id='".$res_id."' AND gd_id='".$gd_id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 通过名字查找附件，仅查一条
	 * @参数 $name 附件名称
	 * @返回 数组
	**/
	public function get_name($name)
	{
		if(!$name) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE name='".$name."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得资源列表
	 * @参数 $condition 条件
	 * @参数 $offset 查询初始位置
	 * @参数 $psize 查询数量
	 * @参数 $is_ext 是否包含扩展
	 * @返回 多维数组
	**/
	public function get_list($condition="",$offset=0,$psize=20,$is_ext=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res ";
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		$sql .= " ORDER BY addtime DESC,id DESC LIMIT ".$offset.",".$psize;
		if(!$is_ext){
			$rslist = $this->db->get_all($sql);
			if(!$rslist){
				return false;
			}
			foreach($rslist AS $key=>$value){
				$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
				$rslist[$key] = $value;
			}
			return $rslist;
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$id = implode(",",array_keys($rslist));
		$extlist = $this->get_gd_pic($id,true);
		$tmplist = array();
		foreach($rslist AS $key=>$value){
			$value["gd"] = $extlist[$value["id"]];
			$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
			$tmplist[] = $value;
		}
		return $tmplist;
	}

	/**
	 * 取得指定ID下的附件，基于ID排序
	 * @参数 $id 附件ID，多个ID用英文逗号隔开
	 * @参数 $is_ext 是否读取扩展表数据
	 * @返回 
	 * @更新时间 
	**/
	public function get_list_from_id($id,$is_ext=false)
	{
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$id.") ORDER BY SUBSTRING_INDEX('".$id."',id,1)";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$is_ext){
			return $rslist;
		}
		$id = implode(",",array_keys($rslist));
		$extlist = $this->get_gd_pic($id,true);
		foreach($rslist AS $key=>$value){
			$value["gd"] = $extlist[$value["id"]];
			$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 删除GD库对应的附件信息
	 * @参数 $id GD库记录的ID
	 * @参数 $root_dir 根目录ID
	 * @返回 
	 * @更新时间 
	**/
	public function delete_gd_id($id,$root_dir="")
	{
		$sql = "SELECT filename FROM ".$this->db->prefix."res_ext WHERE gd_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		if(!$root_dir){
			$root_dir = $this->dir_root;
		}
		foreach($rslist AS $key=>$value){
			if(!$value['filename'] || strpos($value['filename'],'://') === false){
				continue;
			}
			if(file_exists($root_dir.$value['filename'])){
				$this->lib('file')->rm($root_dir.$value['filename']);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE gd_id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 取得资源数量
	 * @参数 $condition 条件，单表查询
	 * @返回 数字
	**/
	public function get_count($condition="")
	{
		$sql = "SELECT count(id) FROM ".$this->db->prefix."res ";
		if($condition)
		{
			$sql .= " WHERE ".$condition;
		}
		return $this->db->count($sql);
	}

	/**
	 * 取得附件信息
	 * @参数 $filename 附件文件名
	 * @参数 $is_ext 是否读取扩展信息
	 * @返回 false / 数组
	**/
	public function get_one_filename($filename,$is_ext=true)
	{
		if(!$filename){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."res WHERE filename='".$filename."' ORDER BY id DESC LIMIT 1";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		return $this->get_one($rs["id"],$is_ext);
	}

	/**
	 * 删除资源
	 * @参数 $id 附件ID
	**/
	public function delete($id)
	{
		$rs = $this->get_one($id);
		if(!$rs){
			return false;
		}
		if(file_exists($rs["filename"]) && file_exists($rs["filename"])){
			unlink($rs["filename"]);
		}
		if($rs["ico"] && substr($rs["ico"],0,7) != "images/" && file_exists($this->dir_root.$rs["ico"])){
			unlink($this->dir_root.$rs["ico"]);
		}
		$this->ext_delete($id);
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 删除扩展附件信息，是ext_delete的别名
	 * @参数 $id 附件ID
	**/
	public function delete_ext($id)
	{
		return $this->ext_delete($id);
	}

	/**
	 * 保存数据
	 * @参数 $data 数组
	 * @参数 $id 附件ID，为空表示添加，反之为修改
	**/
	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"res",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"res");
		}
	}

	/**
	 * 保存扩展信息，注意此项使用的是覆盖更新，需要确保扩展字段res_id,gd_id,filename,filetime数组完全
	 * @参数 $data 数组 保存到qinggan_res_ext表中
	**/
	public function save_ext($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		return $this->db->insert_array($data,"res_ext","replace");
	}


	/**
	 * 删除扩展图片方案
	 * @参数 $id 附件ID
	**/
	public function ext_delete($id)
	{
		$sql = "SELECT filename FROM ".$this->db->prefix."res_ext WHERE res_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist AS $key=>$value){
			if(!$value['filename']){
				continue;
			}
			if(strpos($value['filename'],'://') !== false){
				continue;
			}
			if(file_exists($this->dir_root.$value['filename'])){
				unlink($this->dir_root.$value["filename"]);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE res_id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得资源分类
	 * @参数 $id 分类ID，为空读默认分类
	 * @返回 false或分类数组信息
	**/
	public function cate_one($id=0)
	{
		if($id){
			$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
			return $this->db->get_one($sql);
		}else{
			return $this->cate_default();
		}
	}

	/**
	 * 根据分类标题取得分类信息，如果标题一样，仅读取第一条数据
	 * @参数 $title
	 * @返回 
	 * @更新时间 
	**/
	public function cate_one_from_title($title='')
	{
		if(!$title){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE title='".$title."' ORDER BY id DESC";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得全部分类
	**/
	public function cate_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY id ASC ";
		return $this->db->get_all($sql);
	}

	/**
	 * 取得默认分类ID
	**/
	function cate_default()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate WHERE is_default='1'";
		return $this->db->get_one($sql);
	}

	/**
	 * 设置默认分类
	 * @参数 $id 要设置的默认分类
	**/
	public function cate_default_set($id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default='0' WHERE is_default='1'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."res_cate SET is_default='1' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 分类存储操作
	 * @参数 $data 数组，附件分类信息
	 * @参数 $id 分类ID，为空或为0表示添加，不为空表示编辑
	**/
	public function cate_save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update_array($data,"res_cate",array("id"=>$id));
		}else{
			return $this->db->insert_array($data,"res_cate");
		}
	}

	/**
	 * 删除图片附件分类
	 * @参数 $id 要删除的附件分类ID
	 * @参数 $default_id 要变更的分类ID
	**/
	public function cate_delete($id,$default_id=0)
	{
		$sql = "DELETE FROM ".$this->db->prefix."res_cate WHERE id='".$id."'";
		$this->db->query($sql);
		$cate_rs = $this->cate_one($default_id);
		if(!$cate_rs){
			$cate_rs = $this->cate_default();
		}
		if($cate_rs){
			$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$cate_rs['id']."' WHERE cate_id='".$id."'";
			$this->db->query($sql);
		}
		return true;
	}

	/**
	 * 更新附件名称
	 * @参数 $title 附件名称
	 * @参数 $id 附件ID
	**/
	public function update_title($title,$id)
	{
		$sql = "UPDATE ".$this->db->prefix."res SET title='".$title."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 更新附件备注
	 * @参数 $note 附件备注
	 * @参数 $id 附件ID
	**/
	public function update_note($note='',$id=0)
	{
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."res SET note='".$note."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 取得所有的附件类型
	**/
	public function type_list()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."res_cate ORDER BY is_default DESC,id ASC";
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

	/**
	 * 批量删除附件
	 * @参数 $id 附件ID，数组或整数或多个字串
	**/
	public function pl_delete($id=0)
	{
		if(!$id){
			return false;
		}
		if(!is_array($id)){
			$id = explode(',',$id);
		}
		$list = array();
		foreach($id as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				continue;
			}
			$list[] = intval($value);
		}
		$id = implode(",",$list);
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if($value['filename'] && strpos($value['filename'],'://') === false && file_exists($this->dir_root.$value['filename'])){
					$this->lib('file')->rm($this->dir_root.$value['filename']);
				}
				if($value['ico'] && substr($value["ico"],0,7) != "images/" && file_exists($this->dir_root.$value["ico"])){
					$this->lib('file')->rm($this->dir_root.$value['ico']);
				}
			}
		}
		$sql = "SELECT filename FROM ".$this->db->prefix."res_ext WHERE id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if($value['filename'] && strpos($value['filename'],'://') === false && file_exists($this->dir_root.$value['filename'])){
					$this->lib('file')->rm($this->dir_root.$value['filename']);
				}
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id IN(".$id.")";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."res_ext WHERE res_id IN(".$id.") ";
		$this->db->query($sql);
		return true;
	}

	/**
	 * 取得编辑器中的图片列表
	 * @参数 $condition 查询条件，扩展表别名是e，主表别名是res
	 * @参数 $offset 起始值，默认是0
	 * @参数 $psize 数量，默认是30条
	 * @参数 $gd 是否读扩展表里的数据
	 * @返回 false 或 多维数组
	**/
	public function edit_pic_list($condition="",$offset=0,$psize=30,$gd=false)
	{
		if($gd){
			$sql = "SELECT e.filename,res.id,res.title,res.ico,res.addtime FROM ".$this->db->prefix."res_ext e ";
			$sql.= "JOIN ".$this->db->prefix."res res ON(e.res_id=res.id) ";
		}else{
			$sql = "SELECT res.filename,res.id,res.title,res.ico,res.addtime FROM ".$this->db->prefix."res res ";
		}
		if($condition){
			$sql.= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY res.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
	}

	/**
	 * 统计可在编辑器调用的图片总数
	 * @参数 $condition 查询条件，扩展表别名是e，主表别名是res
	 * @参数 $gd 是否读扩展表里的数据
	 * @返回 数字
	**/
	public function edit_pic_total($condition="",$gd=false)
	{
		
		if($gd){
			$sql = " SELECT count(res.id) ";
			$sql.= "FROM ".$this->db->prefix."res_ext e ";
			$sql.= "JOIN ".$this->db->prefix."res res ON(e.res_id=res.id) ";
		}else{
			$sql = "SELECT id FROM ".$this->db->prefix."res ";
		}
		if($condition){
			$sql.= " WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

	/**
	 * 读取附件信息
	 * @参数 $string 要读取的附件ID，多个ID用英文逗号隔开，或数组
	 * @参数 $ext 读取扩展字段内容
	**/
	public function reslist($string,$ext=true)
	{
		if(!$string){
			return false;
		}
		if(is_array($string)){
			$string = array_unique($string);
			$string = implode(",",$string);
		}
		$list = explode(",",$string);
		$array = array();
		foreach($list AS $key=>$value){
			if(!$value || !trim($value) || !intval($value) || in_array($value,$array)){
				continue;
			}
			$array[] = intval($value);
		}
		$string = implode(",",$array);
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$string.") ORDER BY id ASC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		$sql = "SELECT e.*,gd.identifier FROM ".$this->db->prefix."res_ext e ";
		$sql.= " JOIN ".$this->db->prefix."gd gd ON(e.gd_id=gd.id) ";
		$sql.= " WHERE e.res_id IN(".$string.") ";
		$extlist = $this->db->get_all($sql);
		if(!$extlist){
			return $rslist;
		}
		foreach($extlist AS $key=>$value){
			$rslist[$value["res_id"]]["gd"][$value["identifier"]] = $value;
		}
		return $rslist;
	}

	/**
	 * 更新附件GD
	 * @参数 $id 附件ID
	 * @返回 false / true
	**/
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
		if($cate_rs['ico'] && file_exists($this->dir_root.$rs['filename'])){
			$ico = $this->lib('gd')->thumb($this->dir_root.addslashes($rs['filename']),$id);
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

	/**
	 * 更新附件到新的分类
	 * @参数 $id 要更新的附件ID，多个ID用英文逗号隔开
	 * @参数 $newcate 新的分类ID
	 * @返回 
	 * @更新时间 
	**/
	public function update_cate($id='',$newcate=0)
	{
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$sql = "UPDATE ".$this->db->prefix."res SET cate_id='".$newcate."' WHERE id IN(".$id.")";
		return $this->db->query($sql);
	}

	/**
	 * ID的安全过滤
	 * @参数 $id，支持数组，字符串，数字
	 * @返回 false 或 数字+英文逗号的字符串
	**/
	private function ids_safe($id)
	{
		if(!$id){
			return false;
		}
		if(is_array($id)){
			$id = implode(",",$id);
		}
		$list = explode(',',$id);
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value)){
				unset($list[$key]);
				continue;
			}
			$list[$key] = intval($value);
		}
		return implode(",",$list);
	}

	/**
	 * 读写附件远程配置
	 * @参数 $data 不为空且为数组时，表示保存信息
	**/
	public function remote_config($data='')
	{
		$file = $this->dir_data.'xml/remote_config_'.$this->site_id.'.xml';
		if($data && is_array($data)){
			$this->lib('xml')->save($data,$file);
			return true;
		}
		$file = $this->dir_data.'xml/remote_config_'.$this->site_id.'.xml';
		if(!file_exists($file)){
			$file = $this->dir_data.'xml/remote_config.xml';
		}
		if(!file_exists($file)){
			return false;
		}
		return $this->lib('xml')->read($file);
	}
}
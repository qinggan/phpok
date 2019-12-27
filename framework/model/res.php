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
	private $gdlist = array();
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
		$this->gdlist = $this->model('gd')->get_all('id');
	}

	/**
	 * 取得资源信息
	 * @参数 $id 附件ID
	 * @参数 $is_ext 是否读取扩展
	 * @返回 数组
	**/
	public function get_one($id,$is_ext=false)
	{
		if(!$id){
			return false;
		}
		$sql  = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql .= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.etype) ";
		$sql .= "WHERE res.id='".$id."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($rs["attr"]){
			$attr = unserialize($rs["attr"]);
			$rs["attr"] = $attr;
		}
		//判断附件方案
		$list = array("jpg","gif","png","jpeg");
		if(in_array($rs['ext'],$list)){
			$rs['ico'] = $this->get_ico($rs);
		}
		if(!in_array($rs["ext"],$list) || !$is_ext || !$this->gdlist || count($this->gdlist)<1){
			return $rs;
		}
		if($this->is_local($rs['filename'])){
			foreach($this->gdlist as $key=>$value){
				$rs['gd'][$value['identifier']] = $this->local_url($rs,$value);
			}
			return $rs;
		}
		if(!$rs['cate_id'] || !$rs['etype']){
			return $rs;
		}
		$tmp = $this->control('gateway','api')->exec_file($rs['etype'],'gd',array('filename'=>$rs['filename']));
		if($tmp){
			$rs['gd'] = $tmp;
		}
		return $rs;
	}

	/**
	 * 检测文件是否本地址
	 * @参数 $file 文件名，对应数据表 qinggan_res 下的 filename
	 * @返回 true 或 false
	**/
	public function is_local($file)
	{
		$file = trim(strtolower($file));
		if(strpos($file,'?') !== false){
			return false;
		}
		$tmp = substr($file,0,7);
		if($tmp == 'http://' || $tmp == 'https:/'){
			return false;
		}
		return true;
	}

	/**
	 * 取得扩展GD图片
	 * @参数 $id，附件ID，多个ID用英文逗号隔开
	 * @参数 $is_list，这里人工设置是否多个附件ID，设为true时将写成多维数组
	 * @返回 多维数组或一维数组（受$is_list控制）
	**/
	public function get_gd_pic($id,$is_list=false)
	{
		if(!$this->gdlist){
			return false;
		}
		$id = $this->ids_safe($id);
		if(!$id){
			return false;
		}
		$sql  = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql .= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$list = array();
		foreach($rslist as $key=>$value){
			if($this->is_local($value['filename'])){
				foreach($this->gdlist as $k=>$v){
					$list[$value['id']][$v['identifier']] = $this->local_url($value,$v);
				}
			}else{
				if($value['etype']){
					$tmp = $this->control('gateway','api')->exec_file($value['etype'],'gd',array('filename'=>$value['filename']));
					if($tmp){
						$list[$value['id']] = $tmp;
					}
				}
			}
		}
		if($is_list){
			return $list;
		}
		reset($list);
		return current($list);
	}

	/**
	 * 取得单个扩展图片的GD
	 * @参数 $res_id 附件ID
	 * @参数 $gd_id GD库ID
	 * @返回 数组
	**/
	public function get_pic($res_id,$gd_id)
	{
		if(!$res_id && !$gd_id){
			return false;
		}
		if(!$this->gdlist || !$this->gdlist[$gd_id]){
			return false;
		}
		$rs = $this->get_one($res_id,false);
		if(!$rs || !$rs['cate_id']){
			return false;
		}
		$gdinfo = $this->gdlist[$gd_id];
		if($this->is_local($rs['filename'])){
			$filename = $this->local_url($rs,$gdinfo);
			return array('res_id'=>$res_id,'gd_id'=>$gd_id,'filename'=>$filename,'filetime'=>$rs['addtime']);
		}
		$cate = $this->model('rescate')->get_one($rs['cate_id']);
		if(!$cate['etype']){
			return false;
		}
		$tmp = $this->control('gateway','api')->exec_file($cate['etype'],'gd',array('filename'=>$rs['filename']));
		if(!$tmp){
			return false;
		}
		$gdinfo = $this->gdlist[$gd_id];
		$filename = $tmp[$gdinfo['identifier']];
		return array('res_id'=>$res_id,'gd_id'=>$gd_id,'filename'=>$filename,'filetime'=>$rs['addtime']);
	}

	/**
	 * 通过名字查找附件，仅查一条
	 * @参数 $name 附件名称
	 * @返回 数组
	**/
	public function get_name($name)
	{
		if(!$name){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE name='".$name."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		$list = array("jpg","gif","png","jpeg");
		if(in_array($rs['ext'],$list)){
			$rs['ico'] = $this->get_ico($rs);
		}
		return $rs;
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
		$extlist = array("jpg","gif","png","jpeg");
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
			foreach($rslist as $key=>$value){
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
		foreach($rslist as $key=>$value){
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
		$extlist = array("jpg","gif","png","jpeg");
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id IN(".$id.") ORDER BY SUBSTRING_INDEX('".$id."',id,1)";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$is_ext){
			foreach($rslist as $key=>$value){
				$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
				if(in_array($value['ext'],$extlist)){
					$value['ico'] = $this->get_ico($value);
				}
				$rslist[$key] = $value;
			}
			return $rslist;
		}
		$id = implode(",",array_keys($rslist));
		$extlist = $this->get_gd_pic($id,true);
		foreach($rslist as $key=>$value){
			$value["gd"] = $extlist[$value["id"]];
			$value["attr"] = $value["attr"] ? unserialize($value["attr"]) : "";
			if(in_array($value['ext'],$extlist)){
				$value['ico'] = $this->get_ico($value);
			}
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
		if($this->is_local($rs['filename'])){
			$this->lib('file')->rm($this->dir_root.$rs['filename']);
			$folder = 'res/_cache/_ico/'.substr($rs['id'],0,2).'/';
			if(is_file($this->dir_root.$folder.$rs['id'].'.'.$rs['ext'])){
				$this->lib('file')->rm($this->dir_root.$folder.$rs['id'].'.'.$rs['ext']);
			}
			foreach($this->gdlist as $key=>$value){
				$folder = 'res/_cache/'.$value['identifier'].'/'.substr($rs['id'],0,2).'/';
				if(is_file($this->dir_root.$folder.$rs['id'].'.'.$rs['ext'])){
					$this->lib('file')->rm($this->dir_root.$folder.$rs['id'].'.'.$rs['ext']);
				}
			}
		}
		//删除远程附件
		if($rs['etype']){
			$this->control('gateway','api')->exec_file($rs['etype'],'delete',array('filename'=>$rs['name']));			
		}
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id='".$id."'";
		return $this->db->query($sql);
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
		if($data['attr'] && is_array($data['attr'])){
			$data['attr'] = serialize($data['attr']);
		}
		if($id){
			return $this->db->update_array($data,"res",array("id"=>$id));
		}else{
			$insert_id = $this->db->insert_array($data,"res");
			if(!$insert_id){
				return false;
			}
			$data['id'] = $insert_id;
			//生成缩略图及附件规格图片
			$this->get_ico($data);
			$this->get_gd_pic($insert_id,false);
			return $insert_id;
		}
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
		$sql = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql.= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.id) WHERE res.id IN(".$id.")";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				if($value['filename'] && strpos($value['filename'],'://') === false && file_exists($this->dir_root.$value['filename'])){
					$this->lib('file')->rm($this->dir_root.$value['filename']);
				}
				if($value['ico'] && substr($value["ico"],0,7) != "images/" && $this->is_local($value['ico']) && file_exists($this->dir_root.$value["ico"])){
					$this->lib('file')->rm($this->dir_root.$value['ico']);
				}
				//批量删除云端数据
				if($value['etype'] && !$this->is_local($value['filename'])){
					$this->control('gateway','api')->exec_file($value['etype'],'delete',array('filename'=>$value['name']));
				}
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."res WHERE id IN(".$id.")";
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
		$extlist = array("jpg","gif","png","jpeg");
		$sql = "SELECT res.* FROM ".$this->db->prefix."res res ";
		if($condition){
			$sql.= " WHERE ".$condition." ";
		}
		$sql.= " ORDER BY res.id DESC LIMIT ".$offset.",".$psize;
		$rslist = $this->db->get_all($sql);
		if(!$rslist){
			return false;
		}
		$ids = array();
		foreach($rslist as $key=>$value){
			if(!$value['ico'] && in_array($value['ext'],$extlist) && $this->is_local($value['filename'])){
				$value['ico'] = $this->get_ico($value);
			}
			if($this->is_local($value['filename'])){
				foreach($this->gdlist as $k=>$v){
					$value['gd'][$v['identifier']] = $this->local_url($value,$v);
				}
			}else{
				if($value['etype']){
					$tmp = $this->control('gateway','api')->exec_file($value['etype'],'gd',array('filename'=>$value['filename']));
					if($tmp){
						$value['gd'] = $tmp;
					}
				}
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 统计可在编辑器调用的图片总数
	 * @参数 $condition 查询条件，扩展表别名是e，主表别名是res
	 * @参数 $gd 是否读扩展表里的数据
	 * @返回 数字
	**/
	public function edit_pic_total($condition="",$gd=false)
	{
		
		$sql = "SELECT id FROM ".$this->db->prefix."res ";
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
		foreach($list as $key=>$value){
			if(!$value || !trim($value) || !intval($value) || in_array($value,$array)){
				continue;
			}
			$array[] = intval($value);
		}
		$string = implode(",",$array);
		$sql = "SELECT res.*,cate.etype FROM ".$this->db->prefix."res res ";
		$sql.= "LEFT JOIN ".$this->db->prefix."res_cate cate ON(res.cate_id=cate.id) ";
		$sql.= "WHERE res.id IN(".$string.") ORDER BY res.id ASC";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		foreach($rslist as $key=>$value){
			if($this->is_local($value['filename'])){
				foreach($this->gdlist as $k=>$v){
					$rslist[$key]['gd'][$v['identifier']] = $this->local_url($value,$v);
				}
			}else{
				if($value['etype']){
					$tmp = $this->control('gateway','api')->exec_file($value['etype'],'gd',array('filename'=>$value['filename']));
					if($tmp){
						$rslist[$key]['gd'] = $tmp;
					}
				}
			}
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
		if($this->is_local($rs['filename'])){
			$this->update_ico($rs);
			if($this->gdlist){
				foreach($this->gdlist as $key=>$value){
					$this->local_url($rs,$value,true);
				}
			}
			return true;
		}
		$ico = $this->control('gateway','api')->exec_file($rs['etype'],'ico',array('filename'=>$rs['filename'],'filext'=>$rs['ext']));
		if(!$ico){
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!file_exists($ico)){
				$ico = "images/filetype-large/unknown.jpg";
			}
		}
		$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}

	public function get_ico($rs,$width=200,$height=200,$cutype=1,$qty=80)
	{
		if(!$this->is_local($rs['filename'])){
			return $rs['ico'];
		}
		if($rs['ico']){
			return $rs['ico'];
		}
		$folder = 'res/_cache/_ico/'.substr($rs['id'],0,2).'/';
		if(is_file($this->dir_root.$folder.$rs['id'].'.'.$rs['ext'])){
			$ico = $folder.$rs['id'].'.'.$rs['ext'];
			$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$rs['id']."'";
			$this->db->query($sql);
			return $ico;
		}
		$tmp = array('url'=>$rs['filename']);
		$tmp['width'] = $width;
		$tmp['height'] = $height;
		$tmp['cut_type'] = $cutype;
		$tmp['quality'] =$qty;
		$tmp['bgcolor'] = 'FFFFFF';
		$tmp['_id'] = $rs['id'];
		$tmp['folder'] = $folder;
		$tmp['ext'] = $rs['ext'];
		$ico = $this->img_create($tmp);
		$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return $ico;
	}

	public function update_ico($rs,$width=200,$height=200,$cutype=1,$qty=80)
	{
		if(!$this->is_local($rs['filename'])){
			return false;
		}
		if($rs['ico'] && strpos($rs['ico'],'_cache') !== true){
			return true;
		}
		$tmp = array('url'=>$rs['filename']);
		$tmp['width'] = $width;
		$tmp['height'] = $height;
		$tmp['cut_type'] = $cutype;
		$tmp['quality'] =$qty;
		$tmp['bgcolor'] = 'FFFFFF';
		$tmp['_id'] = $rs['id'];
		$tmp['folder'] = $folder;
		$tmp['ext'] = $rs['ext'];
		$ico = $this->img_create($tmp);
		$sql = "UPDATE ".$this->db->prefix."res SET ico='".$ico."' WHERE id='".$rs['id']."'";
		$this->db->query($sql);
		return $ico;
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

	private function local_url($rs,$gdinfo,$is_update=false)
	{
		if(!$this->is_local($rs['filename'])){
			return false;
		}
		$gdinfo['url'] = $rs['filename'];
		$gdinfo['_id'] = $rs['id'];
		$gdinfo['ext'] = $rs['ext'];
		$gdinfo['folder'] = 'res/_cache/'.$gdinfo['identifier'].'/'.substr($rs['id'],0,2).'/';
		if($is_update){
			return $this->img_create($gdinfo);
		}
		if(is_file($this->dir_root.$gdinfo['folder'].$gdinfo['_id'].'.'.$rs['ext'])){
			return $gdinfo['folder'].$gdinfo['_id'].'.'.$rs['ext'];
		}
		return false;
	}

	public function img_create($gdinfo)
	{
		if($gdinfo['folder']){
			$this->lib('file')->make($this->dir_root.$gdinfo['folder']);
		}
		$this->lib('gd')->isgd(true);
		$this->lib('gd')->filename($this->dir_root.$gdinfo['url']);
		$this->lib('gd')->Filler($gdinfo["bgcolor"]);
		if($gdinfo["width"] && $gdinfo["height"] && $gdinfo["cut_type"]){
			$this->lib('gd')->SetCut(true);
		}else{
			$this->lib('gd')->SetCut(false);
		}
		$this->lib('gd')->SetWH($gdinfo["width"],$gdinfo["height"]);
		$this->lib('gd')->CopyRight($gdinfo["mark_picture"],$gdinfo["mark_position"],$gdinfo["trans"]);
		if($gdinfo["quality"]){
			$this->lib('gd')->Set('quality',$gdinfo['quality']);
		}
		$ext = $gdinfo['ext'];
		if(!$ext){
			$ext = strtolower(substr($gdinfo['url'],-4));
			if(!in_array($ext,array('.jpg','.gif','.png','jpeg'))){
				return $gdinfo['url'];
			}
			$ext = str_replace('.','',$ext);
		}
		$this->lib('gd')->Create($this->dir_root.$gdinfo['url'],$gdinfo['_id'].'.'.$ext,$this->dir_root.$gdinfo['folder']);
		return $gdinfo['folder'].$gdinfo['_id'].'.'.$ext;
	}
}
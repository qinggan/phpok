<?php
/**
 * 栏目管理
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月23日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_model_base extends phpok_model
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::model();
	}

	public function get_root_id(&$rootid,$id)
	{
		$sql = "SELECT parent_id FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if($rs){
			if(!$rs['parent_id']){
				$rootid = $id;
			}else{
				$this->get_root_id($rootid,$rs['parent_id']);
			}
		}
	}
	
	/**
	 * 取得单条分类信息
	 * @参数 $id 主键或是指定的字段名对应的值
	 * @参数 $field 字段名，支持id，identifier
	 * @参数 $ext 是否读取扩展数据
	 * @参数 $is_edit 是否可编辑，如果为否，表示输出格式化后的信息，如果为是，输出原始信息
	**/
	public function get_one($id,$field="id",$ext=true,$is_edit=false)
	{
		if(!$id){
			return false;
		}
		$sql = " SELECT * FROM ".$this->db->prefix."cate WHERE `".$field."`='".$id."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if(!$ext){
			return $rs;
		}
		$ext_rs = $this->cate_module($rs['id'],$is_edit);
		if($ext_rs){
			$rs = array_merge($ext_rs,$rs);
		}
		$tmplist = $this->model('ext')->ext_all('cate-'.$rs['id'],true);
		if(!$tmplist){
			return $rs;
		}
		$ext_rs = array();
		foreach($tmplist as $key=>$value){
			$ext_rs[$value['identifier']] = $value['content'];
			if(!$is_edit){
				$ext_rs[$value['identifier']] = $this->lib('form')->show($value);
			}
		}
		$rs = array_merge($ext_rs,$rs);
		return $rs;
	}

	//取得分类下的模块扩展
	protected function cate_module($id,$is_edit=false)
	{
		$sql = "SELECT parent_id,module_id FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		$tmp = $this->db->get_one($sql);
		if(!$tmp){
			return false;
		}
		if(!$tmp['parent_id']){
			$rootid = $id;
		}else{
			$rootid = $tmp['parent_id'];
			$this->get_root_id($rootid,$tmp['parent_id']);
		}
		if(!$rootid){
			return false;
		}
		$sql = "SELECT module_id FROM ".$this->db->prefix."cate WHERE id='".$rootid."'";
		$info = $this->db->get_one($sql);
		if(!$info){
			return false;
		}
		$flist = $this->model('module')->fields_all($info['module_id']);
		if(!$flist){
			return false;
		}
		$fields = array();
		foreach($flist as $key=>$value){
			$fields[] = $value['identifier'];
		}
		$sql = "SELECT ".implode(",",$fields)." FROM ".$this->db->prefix."cate_".$info['module_id']." WHERE id='".$id."'";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($is_edit){
			return $rs;
		}
		foreach($flist as $key=>$value){
			$rs[$value['identifier']] = $this->lib('form')->show($value,$rs[$value['identifier']]);
		}
		return $rs;
	}

	//取得分类下的模块扩展
	protected function catelist_module($mid)
	{
		$flist = $this->model('module')->fields_all($mid);
		if(!$flist){
			return false;
		}
		$fields = array();
		$fields[] = 'id';
		foreach($flist as $key=>$value){
			$fields[] = $value['identifier'];
		}
		$sql = "SELECT ".implode(",",$fields)." FROM ".$this->db->prefix."cate_".$mid;
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			foreach($flist as $k=>$v){
				$value[$v['identifier']] = $this->lib('form')->show($v,$value[$v['identifier']]);
			}
			$rslist[$value['id']] = $value;
		}
		return $rslist;
	}

	/**
	 * 分类信息
	 * @参数 $id 分类ID
	 * @参数 $ext 是扩包含扩展
	**/
	public function cate_info($id,$ext=false)
	{
		if($ext){
			return $this->get_one($id,'id',true);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	/**
	 * 取得当前分类
	 * @参数 $id 分类ID
	 * @参数 $site_id 站点ID
	**/
	public function cate_one($id,$site_id=0)
	{
		if($site_id){
			$rslist = $this->cate_all($site_id,true);
			if($rslist[$id]){
				return $rslist[$id];
			}
			return false;
		}else{
			$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
			return $this->db->get_one($sql);
		}
	}

	/**
	 * 取得全部分类信息
	 * @参数 $site_id 站点ID
	 * @参数 $status 状态，为1时表示仅读已审核数据
	 * @参数 $pid 父级分类ID
	**/
	public function get_all($site_id=0,$status=0,$pid=0)
	{
		$rslist = $this->cate_all($site_id,$status);
		if(!$rslist){
			return false;
		}
		$tmplist = array();
		$this->format_list($tmplist,$rslist,$pid,"0");
		$this->cate_list = $tmplist;
		return $tmplist;
	}

	/**
	 * 读取分类列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始页数
	 * @参数 $psize 每页显示数量
	 * @参数 $orderby 排序
	**/
	public function cate_list($condition='',$offset=0,$psize=0,$orderby="")
	{
		$sql = " SELECT * FROM ".$this->db->prefix."cate ";
		if($condition){
			$sql .= " WHERE ".$condition." ";
		}
		if(!$orderby){
			$orderby = "taxis ASC,id DESC";
		}
		$sql .= " ORDER BY ".$orderby." ";
		if($psize && intval($psize)>0){
			$sql .= " LIMIT ".intval($offset).",".intval($psize);
		}
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$extlist = array();
		$idlist = array_keys($rslist);
		foreach($rslist as $key=>$value){
			if($value['parent_id'] || !$value['module_id']){
				continue;
			}
			$tmplist = $this->catelist_module($value['module_id']);
			if($tmplist){
				$extlist = array($tmplist,$extlist);
			}
		}
		if($extlist){
			foreach($rslist as $key=>$value){
				if(!$extlist[$value['id']]){
					continue;
				}
				$value = array_merge($extlist[$value['id']],$value);
				$rslist[$key] = $value;
			}
		}
		return $this->cate_ext($rslist);
	}

	/**
	 * 取得全部的分类信息（不格式化）
	 * @参数 $site_id 站点ID
	 * @参数 $status 状态，为1时表示仅读已审核数据
	**/
	public function cate_all($site_id=0,$status=0,$orderby='')
	{
		$sql = " SELECT * FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status='1' ";
		}
		if(!$orderby){
			$orderby = "taxis ASC,id DESC";
		}
		$sql .= " ORDER BY ".$orderby." ";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		$extlist = array();
		$idlist = array_keys($rslist);
		foreach($rslist as $key=>$value){
			if($value['parent_id']){
				continue;
			}
			if(!$value['module_id']){
				continue;
			}
			$tmplist = $this->catelist_module($value['module_id']);
			if($tmplist){
				$extlist = array($tmplist,$extlist);
			}
		}
		if($extlist){
			foreach($rslist as $key=>$value){
				if(!$extlist[$value['id']]){
					continue;
				}
				$value = array_merge($extlist[$value['id']],$value);
				$rslist[$key] = $value;
			}
		}
		return $this->cate_ext($rslist);
	}

	/**
	 * 格式化分类数组
	 * @参数 $rslist 存储目标
	 * @参数 $tmplist 原始数据
	 * @参数 $parent_id 父级ID
	 * @参数 $layer 层级位置
	**/
	public function format_list(&$rslist,$tmplist,$parent_id=0,$layer=0)
	{
		if(!$tmplist && !is_array($tmplist)){
			$tmplist = array();
		}
		foreach($tmplist as $key=>$value){
			if($value["parent_id"] == $parent_id){
				$is_end = true;
				foreach($tmplist as $k=>$v){
					if($v["parent_id"] == $value["id"]){
						$is_end = false;
						break;
					}
				}
				$value["_layer"] = $layer;
				$value["_is_end"] = $is_end;
				$rslist[] = $value;
				//执行子级
				$new_layer = $layer+1;
				$this->format_list($rslist,$tmplist,$value["id"],$new_layer);
			}
		}
	}

	/**
	 * 生成适用于select的下拉菜单中的参数
	 * @参数 $list 列表
	**/
	public function cate_option_list($list)
	{
		if(!$list || !is_array($list)){
			return false;
		}
		$rslist = array();
		foreach($list as $key=>$value){
			$value["_space"] = "";
			for($i=0;$i<$value["_layer"];$i++){
				$value["_space"] .= "&nbsp; &nbsp;│";
			}
			if($value["_is_end"] && $value["_layer"]){
				$value["_space"] .= "&nbsp; &nbsp;├";
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	/**
	 * 读取分类表中的字段名，用于检测字段名防止重复
	 * @参数 $idlist 字段名存储目标
	**/
	public function cate_fields(&$idlist)
	{
		$sql = "SHOW FIELDS FROM ".$this->db->prefix."cate";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$idlist[] = $value["Field"];
			}
		}
	}

	/**
	 * 取得子分类ID信息
	 * @参数 $id 分类ID，支持多个分类ID，用英文逗号格开
	 * @参数 $status 状态，为1时表示仅读已审核数据
	**/
	public function get_son_id_list($id,$status=0)
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$id.")";
		if($status){
			$sql .= " AND status='1'";
		}
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		$id_list = array_keys($rslist);
		return $id_list;
	}

	/**
	 * 取得子分类信息
	 * @参数 $list 存储目标
	 * @参数 $id 父分类ID
	 * @参数 $space 空格补尝
	**/
	public function get_sublist(&$list,$id,$space="")
	{
		if(!$id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parent_id='".$id."'";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$value["space"] = $space ? $space."├ " : "";
				$list[] = $value;
				$newspace = $space ."　　";
				$this->get_sublist($list,$value["id"],$newspace);
			}
		}
	}

	/**
	 * 取得子分类信息
	 * @参数 $id 父级分类ID，多个分类ID用英文逗号隔开
	 * @参数 $status 1为仅读审核过的
	**/
	public function get_sonlist($id=0,$status=0)
	{
		$list = array();
		$sql  = "SELECT id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$id.") ";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " ORDER BY SUBSTRING_INDEX('".$id."',id,1),taxis ASC";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			foreach($rslist as $key=>$value){
				$list[] = $value['id'];
			}
		}
		$list = array_unique($list);
		$id = implode(",",$list);
		return $this->catelist_cid($id,true);
	}

	/**
	 * 递归获取父级分类信息
	 * @参数 $list 父级分类信息
	 * @参数 $id 当前分类ID
	 * @参数 $status 状态，设为1时表示只读审核过的分类
	 * @参数 $ext 是否获取扩展信息
	**/
	public function parent_list(&$list,$id=0,$status=0,$ext=false)
	{
		$rs = $this->get_one($id,'id',$ext,false);
		if($status && !$rs['status']){
			return false;
		}
		$list[] = $rs;
		if($rs['parent_id']){
			$this->parent_list($list,$rs['parent_id'],$status,$ext);
		}
	}

	/**
	 * 读取子分类ID信息
	 * @参数 $list 存储目标
	 * @参数 $id 父级分类ID，多个分类ID用英文逗号隔开
	 * @参数 $status 1为仅读审核过的
	**/
	public function get_sonlist_id(&$list,$id=0,$status=0)
	{
		if(!$id){
			return false;
		}
		$sql  = "SELECT id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$id.") ";
		if($status){
			$sql .= " AND status=1 ";
		}
		$sql .= " ORDER BY SUBSTRING_INDEX('".$id."',id,1),taxis ASC";
		$rslist = $this->db->get_all($sql);
		if($rslist){
			$idlist = array();
			foreach($rslist as $key=>$value){
				$list[] = $value["id"];
				$idlist[] = $value['id'];
			}
			$idstring = implode(",",$idlist);
			$this->get_sonlist_id($list,$idstring,$status);
		}
	}

	/**
	 * 取得子分类的数量
	 * @参数 $ids 分类ID，多个分类用英文逗号隔开，支持数组
	**/
	public function count_sublist($ids = 0)
	{
		if(!$ids){
			$ids = 0;
		}
		if($ids && is_array($ids)){
			$ids = implode(",",$ids);
		}
		$sql = "SELECT count(id) as total,parent_id FROM ".$this->db->prefix."cate WHERE parent_id IN(".$ids.") GROUP BY parent_id";
		$rs = $this->db->get_all($sql);
		if(!$rs){
			return false;
		}
		$rslist = array();
		foreach($rs as $key=>$value){
			$rslist[$value['parent_id']] = $value['total'];
		}
		return $rslist;
	}

	/**
	 * 更新排序
	 * @参数 $id 分类ID
	 * @参数 $taxis 排序值
	**/
	public function update_taxis($id,$taxis=255)
	{
		$sql = "UPDATE ".$this->db->prefix."cate SET taxis='".$taxis."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 删除分类
	 * @参数 $id 分类ID
	**/
	public function cate_delete($id)
	{
		$rs = $this->get_one($id);
		if($rs['parent_id']){
			$rootid = $rs['parent_id'];
			$this->get_root_id($rootid,$rs['parent_id']);
			$root = $this->get_one($rootid);
			if($root['module_id']){
				$sql = "DELETE FROM ".$this->db->prefix."cate_".$root['module_id']." WHERE id='".$rs['id']."'";
				$this->db->query($sql);
			}
		}
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."list SET cate_id=0 WHERE cate_id='".$id."'";
		$this->db->query($sql);
		$sql = "DELETE FROM ".$this->db->prefix."list_cate WHERE cate_id='".$id."'";
		$this->db->query($sql);
		$sql = "SELECT id FROM ".$this->db->prefix."fields WHERE ftype='cate-".$id."'";
		$rslist = $this->db->get_all($sql,'id');
		if($rslist){
			$idlist = array_keys($rslist);
			$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id IN(".implode(',',$idlist).")";
			$this->db->query($sql);
			//删除当前分类的扩展
			$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id IN(".implode(',',$idlist).")";
			$this->db->query($sql);
		}
		//删除标签下与分类有关的
		$this->model('tag')->stat_delete('c'.$id,"title_id");
		return true;
	}

	/**
	 * 取得根分类信息
	 * @参数 $site_id 站点ID
	**/
	public function root_catelist($site_id=0)
	{
		if(!$site_id){
			$site_id = $this->site_id;
		}
		if(!$site_id){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parent_id=0 AND site_id='".$site_id."' ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql,"id");
	}

	/**
	 * 通过分类ID获取分类内容
	 * @参数 $cid 分类ID，多个分类ID用英文逗号隔开
	 * @参数 $ext 是否读取扩展数据
	**/
	public function catelist_cid($cid,$ext=true)
	{
		if(!$cid){
			return false;
		}
		if(is_array($cid)){
			$cid = implode(",",$cid);
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$cid.") AND status=1 ORDER BY SUBSTRING_INDEX('".$cid."',id,1) ";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		return $this->cate_ext($rslist);
	}

	/**
	 * 前台调用当前分类下的子分类
	 * @参数 $cid 父级分类ID
	 * @参数 $ext 是否读取扩展数据
	 * @参数 $status 1为仅读审核过的
	**/
	public function catelist_sonlist($cid,$ext=true,$status=1)
	{
		if(!$cid){
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE parent_id=".intval($cid)." ";
		if($status){
			$sql .= ' AND status=1 ';
		}
		$sql.= " ORDER BY taxis ASC,id DESC ";
		$rslist = $this->db->get_all($sql,"id");
		if(!$rslist){
			return false;
		}
		if(!$ext){
			return $rslist;
		}
		return $this->cate_ext($rslist);
	}

	/**
	 * 通过主表数据，读取扩展数据
	 * @参数 $rslist 多级数组，即主表中的分类信息，KEY值为主键ID
	**/
	public function cate_ext($rslist)
	{
		if(!$rslist){
			return false;
		}
		$idlist = array_keys($rslist);
		$total = count($idlist);
		$clist = array();
		foreach($idlist as $key=>$value){
			$clist[] = "cate-".$value;
		}
		$cateinfo = implode(",",$clist);
		$extlist = $this->model('ext')->get_all($cateinfo,true);
		foreach($rslist as $key=>$value){
			$tmp = $extlist["cate-".$key];
			if($tmp){
				$rslist[$key] = array_merge($tmp,$value);
			}
		}
		return $rslist;
	}

	public function catelist_extlist($mid,$format=true)
	{
		$flist = $this->model('module')->fields_all($mid);
		if(!$flist){
			return false;
		}
		$fields = array("id");
		foreach($flist as $key=>$value){
			$fields[] = $value['identifier'];
		}
		$sql = "SELECT ".implode(",",$fields)." FROM ".$this->db->prefix."cate_".$mid;
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		if(!$format){
			$rslist = array();
			foreach($tmplist as $key=>$value){
				$rslist[$value['id']] = $value;
			}
			return $rslist;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			foreach($flist as $k=>$v){
				if($value[$v['identifier']] == ''){
					continue;
				}
				$value[$v['identifier']] = $this->lib('form')->show($v,$value[$v['identifier']]);
			}
			$rslist[$value['id']] = $value;
		}
		return $rslist;
	}

	/**
	 * 递归获取分类信息
	 * @参数 $array 存储目标
	 * @参数 $parent_id 父级分类ID
	 * @参数 $rslist 数据来源
	**/
	public function cate_ids(&$array,$parent_id=0,$rslist='')
	{
		if($rslist && is_array($rslist)){
			foreach($rslist as $key=>$value){
				if($value['parent_id'] == $parent_id){
					$array[] = $value['id'];
					$this->cate_ids($array,$value['id'],$rslist);
				}
			}
		}
	}

	/**
	 * 读取主题下绑定的扩展分类信息
	 * @参数 $id 主题ID
	**/
	public function ext_catelist($id,$pri="id")
	{
		$sql = "SELECT c.* FROM ".$this->db->prefix."list_cate lc JOIN ".$this->db->prefix."cate c ON(lc.cate_id=c.id) ";
		$sql.= "WHERE lc.id='".$id."' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC";
		return $this->db->get_all($sql,$pri);
	}

	/**
	 * 读取分类信息
	 * @参数 $ids 多个分类ID用英文逗号隔开，或数组
	 * @参数 $project_identifier 项目标识
	**/
	public function list_ids($ids,$project_identifier='')
	{
		if(!$ids){
			return false;
		}
		$ids = is_array($ids) ? implode(",",$ids) : $ids;
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$ids.") AND status=1";
		$rslist = $this->db->get_all($sql,'id');
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if($project_identifier){
				$value['url'] = $this->url($project_identifier,$value['identifier']);
			}
			$cate_tmp = $this->model('ext')->ext_all('cate-'.$value['id'],true);
			if($cate_tmp){
				$cate_ext = array();
				foreach($cate_tmp as $k=>$v){
					$cate_ext[$v['identifier']] = $this->lib('form')->show($v);
					if($v['form_type'] == 'url' && $v['content']){
						$v['content'] = unserialize($v['content']);
						$value['url'] = $v['content']['default'];
						if($this->site['url_type'] == 'rewrite' && $v['content']['rewrite']){
							$value['url'] = $v['content']['rewrite'];
						}
					}
				}
				$value = array_merge($cate_ext,$value);
				unset($cate_ext,$cate_tmp);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}
}
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

	/**
	 * 取得单条分类信息
	 * @参数 $id 主键或是指定的字段名对应的值
	 * @参数 $field 字段名，支持id，identifier
	 * @参数 $ext 是否读取扩展数据
	**/
	public function get_one($id,$field="id",$ext=true)
	{
		if(!$id){
			return false;
		}
		$sql = " SELECT * FROM ".$this->db->prefix."cate WHERE `".$field."`='".$id."' ";
		$rs = $this->db->get_one($sql);
		if(!$rs){
			return false;
		}
		if($ext){
			$tmplist = $this->model('ext')->ext_all('cate-'.$rs['id'],true);
			if($tmplist){
				$ext_rs = array();
				foreach($tmplist as $key=>$value){
					$ext_rs[$value['identifier']] = content_format($value);
				}
				if($ext_rs){
					$rs = array_merge($rs,$ext_rs);
				}
			}
		}
		return $rs;
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
	 * 取得全部的分类信息（不格式化）
	 * @参数 $site_id 站点ID
	 * @参数 $status 状态，为1时表示仅读已审核数据
	**/
	public function cate_all($site_id=0,$status=0)
	{
		$sql = " SELECT * FROM ".$this->db->prefix."cate WHERE site_id='".$site_id."'";
		if($status){
			$sql .= " AND status='1' ";
		}
		$sql .= " ORDER BY taxis ASC,id DESC ";
		return $this->db->get_all($sql,'id');
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
		$sql = "DELETE FROM ".$this->db->prefix."cate WHERE id='".$id."'";
		$this->db->query($sql);
		$sql = "UPDATE ".$this->db->prefix."list SET cate_id=0 WHERE cate_id='".$id."'";
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
	 * @参数 $cid 父级分类ID，多个分类ID用英文逗号隔开
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
	public function ext_catelist($id)
	{
		$sql = "SELECT c.* FROM ".$this->db->prefix."list_cate lc JOIN ".$this->db->prefix."cate c ON(lc.cate_id=c.id) ";
		$sql.= "WHERE lc.id='".$id."' AND c.status=1 ORDER BY c.taxis ASC,c.id DESC";
		return $this->db->get_all($sql,'id');
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
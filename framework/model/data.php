<?php
/***********************************************************
	Filename: {phpok}/model/data.php
	Note	: 前台用于调用的数据
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月9日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class data_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	private function _res_info($id)
	{
		if(!$id)
		{
			return false;
		}
		//通过数据库读取数据
		$sql = "SELECT * FROM ".$this->db->prefix."res WHERE id=".intval($id);
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		$sql = "SELECT ext.res_id,ext.filename,gd.identifier FROM ".$this->db->prefix."res_ext ext ";
		$sql.= "JOIN ".$this->db->prefix."gd gd ON(ext.gd_id=gd.id) ";
		$sql.= "WHERE ext.res_id=".intval($id);
		$extlist = $this->db->get_all($sql);
		if($extlist)
		{
			foreach($extlist AS $key=>$value)
			{
				$rs['gd'][$value['identifier']] = $value['filename'];
			}
			unset($extlist);
		}
		unset($sql);
		return $rs;
	}


	//取得单篇ID
	private function _list_info($id)
	{
		//读取内容
		$sql  = "SELECT * FROM ".$this->db->prefix."list WHERE ";
		$sql .= is_numeric($id) ? " id='".$id."' " : " identifier='".$id."' ";
		$sql .= " AND site_id='".$this->site['id']."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//取得项目基本信息
		$rs['project_id'] = $this->_project_info($rs['project_id']);
		if(!$rs['project_id'])
		{
			return false;
		}
		$rs['project_id']['url'] = $this->url($rs['project_id']['identifier']);
		//读取会员信息及扩展
		if($rs['user_id'])
		{
			$rs['user_id'] = $this->_user_info($rs['user_id']);
		}
		//读分类及其扩展信息
		if($rs['cate_id'])
		{
			$rs['cate_id'] = $this->_cate_info($rs['cate_id']);
			if($rs['cate_id'])
			{
				$rs['cate_id']['url'] = $this->url($rs['project_id']['identifier'],$rs['cate_id']['identifier']);
			}
		}
		//读模块信息及其扩展
		if($rs['module_id'])
		{
			$ext = $this->_list_ext_info($rs['id'],$rs['module_id']);
			if($ext)
			{
				$rs = array_merge($ext,$rs);
			}
		}
		return $rs;
	}

	//读单篇主题的扩展信息
	private function _list_ext_info($id=0,$mid=0)
	{
		if(!$id || !$mid)
		{
			return false;
		}
		$sql = "SELECT * FROM ".$this->db->prefix."list_".$mid." WHERE id='".$id."' AND site_id='".$this->site['id']."'";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//清除四个核心变量
		unset($rs['id'],$rs['site_id'],$rs['project_id'],$rs['cate_id']);
		if($rs && count($rs)>0)
		{
			return $rs;
		}
		return false;
	}

	//项目信息内容
	private function _project_info($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//获取扩展内容
		$extlist = $this->_ext_info('project-'.$id);
		if($extlist)
		{
			$rs = array_merge($extlist,$rs);
		}
		return $rs;
	}

	//读取分类及期扩展信息，但并不格式化
	private function _cate_info($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		//获取扩展内容
		$extlist = $this->_ext_info('cate-'.$id);
		if($extlist)
		{
			$rs = array_merge($extlist,$rs);
		}
		return $rs;
	}

	//读取扩展内容，未格式化
	private function _ext_info($module)
	{
		$sql = "SELECT e.identifier,c.content FROM ".$this->db->prefix."ext e ";
		$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(e.id=c.id) WHERE e.module='".$module."'";
		$rslist = $this->db->get_all($sql);
		if(!$rslist)
		{
			return false;
		}
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			$list[$value['identifier']] = $value['content'];
		}
		return $list;
	}
	
	//取得会员内容及未格式化的扩展信息
	private function _user_info($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id='".$id."' AND status=1";
		$rs = $this->db->get_one($sql);
		if(!$rs) return false;
		//读取会员扩展
		$sql = "SELECT * FROM ".$this->db->prefix."user_ext WHERE id='".$id."'";
		$ext_rs = $this->db->get_one($sql);
		if($ext_rs)
		{
			$rs = array_merge($ext_rs,$rs);
		}
		return $rs;
	}
	//内容分页
	function info_page($content,$pageid=0)
	{
		if(!$content) return false;
		if(!$pageid) $pageid = 1;
		$lst = explode('[:page:]',$content);
		$t = $pageid-1;
		if($lst[$t])
		{
			$total = count($lst);
			if($total>1)
			{
				$array = array();
				for($i=0;$i<$total;$i++)
				{
					$array[$i] = $i+1;
				}
			}
			return array('pagelist'=>$array,'content'=>$lst[$t]);
		}
		return $lst[0];
	}

	//取得当前分类信息
	public function cate($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		//取得项目信息
		$project_rs = $this->_project($rs['pid'],false);
		if(!$project_rs['cate']) return false;
		if($rs['cate'])
		{
			$tmp = $this->_id($rs['cate'],$this->site['id']);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		if(!$rs['cateid']) $rs['cateid'] = $project_rs['cate'];
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$rs['cateid']."' AND status=1";
		$cate_rs = $this->db->get_one($sql);
		if(!$cate_rs) return false;
		if($rs['cate_ext'])
		{
			$ext = $this->ext_all('cate-'.$cate_rs['id'],$cate_rs);
			if($ext) $cate_rs = array_merge($ext,$cate_rs);
		}
		if(!$cate_rs['url'])
		{
			$cate_rs['url'] = $GLOBALS['app']->url($project_rs['identifier'],$cate_rs['identifier']);
		}
		return $cate_rs;
	}

	//取得分类，不带项目
	function cate_id($rs)
	{
		$id = $rs['id'] ? $rs['id'] : $rs['cateid'];
		if(!$id && !$rs['phpok']) return false;
		if(!$id)
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'cate') $id = $tmp['id'];
		}
		if(!$id) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id='".$id."' AND status=1";
		$cate_rs = $this->db->get_one($sql);
		if(!$cate_rs) return false;
		if($rs['cate_ext'])
		{
			$ext = $this->ext_all('cate-'.$cate_rs['id'],$cate_rs);
			if($ext) $cate_rs = array_merge($ext,$cate_rs);
		}
		return $cate_rs;
	}

	//取得当前分类下的子类
	public function subcate($rs)
	{
		if(!$rs['cateid'] && !$rs['phpok']) return false;
		if(!$rs['cateid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'cate') return false;
			$rs['cateid'] = $tmp['id'];
		}
		$list = array();
		$cate_all = $GLOBALS['app']->model('cate')->cate_all($rs['site_id']);
		$this->cate_sublist($list,$rs['cateid'],$cate_all,$rs['project']);
		return $list;
	}

	public function _tree(&$list,$catelist,$parent_id=0)
	{
		foreach($catelist as $key=>$value)
		{
			if($value['parent_id'] == $parent_id)
			{
				$list[$value['id']] = $value;
				$this->_tree($list[$value['id']]['sublist'],$catelist,$value['id']);
			}
		}
	}

	//取得项目信息
	public function project($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$rs = $this->_project($rs['pid'],$rs['project_ext']);
		if(!$rs) return false;
		//绑定链接
		if(!$rs['url']) $rs['url'] = $GLOBALS['app']->url($rs['identifier']);
		return $rs;
	}

	//取得父级项目信息
	public function _project_parent($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$project_rs = $this->_project($rs['pid'],false);
		if(!$project_rs || !$project_rs['parent_id']) return false;
		$rs = $this->_project($project_rs['parent_id'],$rs['parent_ext']);
		if(!$rs) return false;
		//绑定链接
		if(!$rs['url']) $rs['url'] = $GLOBALS['app']->url($rs['identifier']);
		return $rs;
	}
	
	//取得子项目信息
	public function sublist($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE parent_id=".intval($rs['pid'])." AND status=1 ";
		$sql.= "AND hidden=0 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql);
		if(!$rslist) return false;
		if($rs['sublist_ext'])
		{
			foreach($rslist AS $key=>$value)
			{
				$ext_rs = $this->ext_all('project-'.$value['id'],$value);
				if($ext_rs) $value = array_merge($ext_rs,$value);
				$rslist[$key] = $value;
			}
		}
		foreach($rslist AS $key=>$value)
		{
			if(!$value['url']) $value['url'] = $GLOBALS['app']->url($value['identifier']);
			$rslist[$key] = $value;
 		}
 		return $rslist;

	}
	//读取当前分类的子分类
	public function cate_sublist(&$list,$parent_id=0,$rslist='',$identifier='')
	{
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				if($value['parent_id'] == $parent_id)
				{
					if($identifier)
					{
						$value['url'] = $this->url($identifier,$value['identifier']);
					}
					if($value['_url'])
					{
						$value['url'] = $value['_url'];
						unset($value['_url']);
					}
					$list[$value['id']] = $value;
					$this->cate_sublist($list,$value['id'],$rslist,$identifier);
				}
			}
		}
	}

	//取得自定义字段信息
	public function fields($rs)
	{
		if(!$rs['pid'] && !$rs['phpok']) return false;
		if(!$rs['pid'])
		{
			$tmp = $this->_id($rs['phpok'],$this->site['id']);
			if(!$tmp || $tmp['type'] != 'project') return false;
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid']) return false;
		$project_rs = $this->_project($rs['pid'],false);
		if(!$project_rs || !$project_rs['module']) return false;
		//自定义字段
		$array = array();
		$flist = $this->module_field($project_rs['module']);
		//如果存在扩展字段，对扩展字段进行处理，标前识加前缀等
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				if(!$value['is_front'])
				{
					unset($flist[$key]);
					continue;
				}
				if($rs['prefix'])
				{
					$value["identifier"] = $rs['prefix'].$value['identifier'];
				}
				if($rs['info'][$value['identifier']])
				{
					$value['content'] = $rs['info'][$value['identifier']];
				}
				$flist[$key] = $value;
			}
			//如果包含主题
			if($rs['in_title'])
			{
				$tmp_id = $rs['prefix'].'title';
				$array['title'] = array('id'=>0,"module_id"=>$project_rs['module'],'title'=>($project_rs['alias_title'] ? $project_rs['alias_title'] : '主题'),'identifier'=>$tmp_id,'field_type'=>'varchar','form_type'=>'text','format'=>'safe','taxis'=>1,'width'=>'300','content'=>$rs['info']['title']);
				$array = array_merge($array,$flist);
			}
			else
			{
				$array = $flist;
			}
		}
		//判断是否格式化
		if($rs['fields_format'])
		{
			foreach($array AS $key=>$value)
			{
				if($value['ext'])
				{
					$ext = is_string($value['ext']) ? unserialize($value['ext']) : $value['ext'];
					unset($value['ext']);
					$value = array_merge($ext,$value);
				}
				$array[$key] = $GLOBALS['app']->lib('form')->format($value);
			}
		}
		return $array;
	}
	
	//取得项目信息
	public function _project($id,$ext=false)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".intval($id);
		$rs = $this->db->get_one($sql);
		if(!$rs)
		{
			return false;
		}
		if($ext)
		{
			$ext = $this->ext_all('project-'.$id,$rs);
			if($ext)
			{
				$rs = array_merge($ext,$rs);
				unset($ext);
			}
		}
		return $rs;
	}

	function module_field($mid)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."module_fields WHERE module_id='".$mid."' ORDER BY taxis ASC,id DESC";
		$rslist = $this->db->get_all($sql,'identifier');
		if(!$rslist)
		{
			return false;
		}
		foreach($rslist as $key=>$value)
		{
			if($value['ext'])
			{
				$value['ext'] = unserialize($value['ext']);
			}
			$rslist[$key] = $value;
		}
		return $rslist;
	}

	public function id($identifier,$site_id=0)
	{
		return $this->_id($identifier,$site_id);
	}

	//通过标识串获取内容信息
	private function _id($identifier,$site_id=0)
	{
		$rslist = $this->_id_all($site_id);
		if($rslist[$identifier])
		{
			return $rslist[$identifier];
		}
		return false;
	}

	//读取全部ID
	private function _id_all($site_id=0)
	{
		return $GLOBALS['app']->model('id')->id_all($site_id,true);
	}

	//获取项目，分类的扩展信息
	public function ext_all($id,$baseinfo='')
	{
		$sql = "SELECT ext.ext,ext.identifier,ext.form_type,c.content FROM ".$this->db->prefix."ext ext ";
		$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(ext.id=c.id) ";
		$sql.= "WHERE ext.module='".$id."'";
		$rslist = $this->db->get_all($sql,'identifier');
		if(!$rslist)
		{
			unset($sql);
			return false;
		}
		$res = '';
		$type = substr($id,0,4) == "cate" ? 'c' : 'p';
		foreach($rslist AS $key=>$value)
		{
			//当内容表单为网址时
			if($value['form_type'] == 'url' && $value['content'])
			{
				$value['content'] = unserialize($value['content']);
				$url = $this->site['url_type'] == 'rewrite' ? $value['content']['rewrite'] : $value['content']['default'];
				if(!$url) $url = $value['content']['default'];
				$value['content'] = $url;
				//绑定扩展自定义url
				if(!$rslist['url']) $rslist['url'] = array('form_type'=>'text','content'=>$url);
			}
			elseif($value['form_type'] == 'upload' && $value['content'])
			{
				$tmp = explode(',',$value['content']);
				foreach($tmp AS $k=>$v)
				{
					$v = intval($v);
					if($v) $res[] = $v;
				}
			}
			elseif($value['form_type'] == 'editor' && $value['content'])
			{
				if($value['ext'])
				{
					$value['ext'] = unserialize($value['ext']);
				}
				if($value['ext'] && $value['ext']['inc_tag'])
				{
					$value['content'] = $this->_tag_format($value['content'],$type.$baseinfo['id']);
				}
				$value['content'] = str_replace('[:page:]','',$value['content']);
				$value['content'] = $this->lib('ubb')->to_html($value['content'],false);
			}
			$rslist[$key] = $value;
		}
		//格式化内容数据，并合并附件数据
		$flist = "";
		foreach($rslist AS $key=>$value)
		{
			$flist[$key] = $value;
			$rslist[$key] = $value['content'];
		}
		if($res && is_array($res)) $res = $this->_res_info2($res);
		$rslist = $this->_format($rslist,$flist,$res);
		unset($flist,$res);
		return $rslist;
	}

	//读取分类下的子分类id
	private function _cate_id(&$array,$parent_id=0,$rslist='')
	{
		if($rslist && is_array($rslist))
		{
			foreach($rslist as $key=>$value)
			{
				if($value['parent_id'] == $parent_id)
				{
					$array[] = $value['id'];
					$this->_cate_id($array,$value['id'],$rslist);
				}
			}
		}
	}

	public function res_info($id)
	{
		return $this->_res_info2($id);
	}

	//读取附件信息
	private function _res_info2($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT id,name,filename,addtime,title,note,download FROM ".$this->db->prefix."res WHERE id IN(".$id.")";
		$reslist = $this->db->get_all($sql,'id');
		if(!$reslist) return false;
		$sql = "SELECT ext.res_id,ext.filename,gd.identifier FROM ".$this->db->prefix."res_ext ext ";
		$sql.= "JOIN ".$this->db->prefix."gd gd ON(ext.gd_id=gd.id) ";
		$sql.= "WHERE ext.res_id IN(".$id.")";
		$extlist = $this->db->get_all($sql);
		if($extlist)
		{
			foreach($extlist AS $key=>$value)
			{
				$reslist[$value["res_id"]]["gd"][$value['identifier']] = $value['filename'];
			}
			unset($extlist);
		}
		return $reslist;
	}

	//格式化单列信息
	private function _format($rs,$flist="",$reslist="",$catelist="",$userlist="",$tlist="")
	{
		if(!$rs || !is_array($rs)) return false;
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				$ext = $value['ext'];
				if($ext && is_string($ext))
				{
					$ext = unserialize($ext);
				}
				//格式化附件信息
				if($value['form_type'] == "upload" && $rs[$value['identifier']] && $reslist && is_array($reslist))
				{
					if($ext['is_multiple'])
					{
						$res = false;
						$tmp = explode(',',$rs[$value['identifier']]);
						foreach($tmp AS $k=>$v)
						{
							$v = intval($v);
							if($v && $reslist[$v]) $res[$v] = $reslist[$v];
						}
						$rs[$value['identifier']] = $res;
					}
					else
					{
						$rs[$value['identifier']] = $reslist[$rs[$value['identifier']]];
					}
				}
			}
			unset($flist);
		}
		//格式化分类信息
		if($rs['cate_id'] && $catelist && $catelist[$rs['cate_id']]) $rs['cate_id'] = $catelist[$rs['cate_id']];
		//格式化会员信息
		if($rs['user_id'] && $userlist && $userlist[$rs['user_id']]) $rs['user_id'] = $userlist[$rs['user_id']];
		return $rs;
	}

	//读取分类基础信息
	private function _cate_info2($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT * FROM ".$this->db->prefix."cate WHERE id IN(".$id.")";
		return $this->db->get_all($sql,"id");
	}

	//读取会员基础信息
	private function _user_info2($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT * FROM ".$this->db->prefix."user WHERE id IN(".$id.")";
		return $this->db->get_all($sql,"id");
	}

	//读取内容基础信息
	private function _title_info($id)
	{
		if(!$id) return false;
		if(is_string($id)) $id = array($id);
		$id = array_unique($id);
		$id = implode(',',$id);
		$sql = "SELECT * FROM ".$this->db->prefix."list WHERE id IN(".$id.")";
		return $this->db->get_all($sql,"id");
	}
}
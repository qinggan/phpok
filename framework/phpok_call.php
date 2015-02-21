<?php
/***********************************************************
	Filename: {phpok}/phpok_call.php
	Note	: PHPOK调用中心类
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-20 17:42
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class phpok_call extends phpok_control
{
	private $mlist;
	public function __construct()
	{
		parent::control();
		$this->mlist = get_class_methods($this);
		$this->model('project')->site_id($this->site['id']);
		$this->lib('form')->appid('www');
	}

	public function __destruct()
	{
		unset($this->mlist);
	}

	//执行数据调用
	public function phpok($id,$rs="")
	{
		if(!$id)
		{
			return false;
		}
		if($rs && is_string($rs))
		{
			parse_str($rs,$rs);
		}
		if(!isset($rs['site']) || (isset($rs['site']) && !$rs['site']))
		{
			$rs['site'] = $this->site['id'];
		}
		//判断是内置参数还是调用数据中心的数据
		if(substr($id,0,1) != '_')
		{
			$call_rs = $this->model('call')->one($id,$rs['site']);
			if(!$call_rs)
			{
				unset($rs);
				return false;
			}
			if($rs && is_array($rs))
			{
				$call_rs = array_merge($call_rs,$rs);
				unset($rs);
			}
		}
		else
		{
			if(!$rs || !is_array($rs)) return false;
			//未指定站点ID时，直读站点id
			if(!$rs['site_id'])
			{
				$rs['site_id'] = $this->site['id'];
			}
			$list = array('arclist','arc','cate','catelist','project','sublist','parent','plist');
			$list[] = 'fields';
			$list[] = 'user';
			$list[] = 'userlist';
			$list[] = 'total';
			$list[] = 'cate_id';
			$list[] = 'subcate';
			$list[] = 'res';
			$list[] = 'reslist';
			$id = substr($id,1);
			//如果是arclist，且未定义is_list属性，则默认启用此属性
			if($id == "arclist")
			{
				$rs["is_list"] = $rs["is_list"] == 'false' ? 0 : 1;
			}
			if(!$id || !in_array($id,$list))
			{
				return false;
			}
			$call_rs = array_merge($rs,array('type_id'=>$id));
			unset($rs);
		}
		$func = '_'.$call_rs['type_id'];
		if(!in_array($func,$this->mlist))
		{
			return false;
		}
		return $this->$func($call_rs);
	}

	//读取文章列表
	private function _arclist($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			return false;
		}
		$cache_tbl = $this->_cache_tbl('arclist');
		$cache_id = $this->db->cache_id(serialize($rs),$cache_tbl);
		$cache_info = $this->db->cache_get($cache_id);
		if($cache_info && !is_bool($cache_info))
		{
			return $cache_info;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid'])
		{
			unset($rs);
			return false;
		}
		$project = $this->_project(array('pid'=>$rs['pid'],'site'=>$rs['site']));
		if(!$project || !$project['status'] || !$project['module'])
		{
			return false;
		}
		$array = array();
		$array['project'] = $project;
		if($project['cate'] || $rs['cateid'])
		{
			$cateid = $rs['cateid'] ? $rs['cateid'] : $project['cate'];
			$cate = $this->_cate(array("pid"=>$rs['pid'],"cateid"=>$cateid,'site'=>$rs['site']));
			if($cate)
			{
				$array['cate'] = $cate;
				$rs['cateid'] = $cateid;
			}
		}
		$flist = $this->model('module')->fields_all($project['module']);
		$field = 'l.*';
		$nlist = false;
		if($flist)
		{
			foreach($flist as $key=>$value)
			{
				$field .= ",ext.".$value['identifier'];
				$nlist[$value['identifier']] = $value;
			}
		}
		$condition = $this->_arc_condition($rs);
		$orderby = $rs['orderby'] ? $rs['orderby'] : $project['orderby'];
		if(!$rs['is_list']) $rs['psize'] = 1;
		$offset = $rs['offset'] ? intval($rs['offset']) : 0;
		$psize = ($rs['is_list'] && $rs['is_list'] != 'false') ? intval($rs['psize']) : 1;
		$rslist = $this->model('list')->arc_all($project['module'],$field,$condition,$offset,$psize,$orderby);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				if($nlist && is_array($nlist))
				{
					foreach($nlist AS $k=>$v)
					{
						$myval = $this->lib('form')->show($v,$value[$k]);
						if($v['form_type'] == 'url' && $value[$k])
						{
							$tmp = unserialize($value[$k]);
							$link = $this->site['url_type'] == 'rewrite' ? $tmp['rewrite'] : $tmp['default'];
							if(!$link) $link = $tmp['default'];
							if(!$value['url'] && $k != 'url' && $link) $value['url'] = $link;
						}
						$value[$k] = $myval;
					}
				}
				if(!$value['url'])
				{
					$tmpid = $value['identifier'] ? $value['identifier'] : $value['id'];
					$value['url'] = $this->config['user_rewrite'] ? $this->url($value['id']) : $this->url($tmpid);
				}
				$rslist[$key] = $value;
			}
			if($rs['in_sub'] && strtolower($rs['in_sub']) != 'false')
			{
				$list = false;
				foreach($rslist AS $key=>$value)
				{
					if(!$value['parent_id'])
					{
						$value['sonlist'] = '';
						foreach($rslist AS $k=>$v)
						{
							if($v['parent_id'] == $value['id'])
							{
								$value['sonlist'][] = $v;
							}
						}
						$list[] = $value;
					}
				}
				$rslist = $list;
				unset($list);
			}
			if(!$rs['is_list'] || $rs['is_list'] == 'false')
			{
				$array['rs'] = current($rslist);
			}
			else
			{
				$array['rslist'] = $rslist;
			}
		}
		$array['total'] = $this->model('list')->arc_count($project['module'],$condition);
		unset($rslist,$project);
		if($cache_id)
		{
			$this->db->cache_save($cache_id,$array);
		}
		return $array;	
	}

	private function _arc_condition($rs)
	{
		$condition  = " l.site_id='".$rs['site']."' AND l.hidden=0 ";
		if($rs['pid'])
		{
			$condition .= " AND l.project_id=".intval($rs['pid'])." ";
		}
		if(!$rs['not_status'])
		{
			if($_SESSION['user_id'])
			{
				$condition .= " AND (l.status=1 OR (l.user_id=".intval($_SESSION['user_id'])." AND l.status=0)) ";
			}
			else
			{
				$condition .= " AND l.status=1 ";
			}
		}
		if($rs['notin'])
		{
			$condition .= " AND l.id NOT IN(".$rs['notin'].") ";
		}
		if(!$rs['in_sub'] || $rs['in_sub'] == 'false')
		{
			$condition .= " AND l.parent_id=0 ";
		}
		if($rs['cate'])
		{
			$tmp = $this->_id($rs['cate'],$this->site['id']);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		if($rs['cateid'])
		{
			$cate_all = $this->model('cate')->cate_all($rs['site']);
			$array = array($rs['cateid']);
			$this->model('cate')->cate_ids($array,$rs['cateid'],$cate_all);
			$condition .= " AND l.cate_id IN(".implode(",",$array).") ";
			unset($cate_all,$array);
		}
		//绑定某个会员
		if($rs['user_id'])
		{
			if(is_array($rs['user_id'])) $rs['user_id'] = implode(",",$rs['user_id']);
			$condition .= strpos($rs['user_id'],",") === false ? " AND l.user_id='".$rs['user_id']."'" : " AND l.user_id IN(".$rs['user_id'].")";
		}
		if($rs['attr'])
		{
			$sql.= " AND l.attr LIKE '%".$rs['attr']."%' ";
		}
		if($rs['idin'])
		{
			$sql .= " AND l.id IN(".$rs['idin'].") ";
		}
		if($rs['tag'])
		{
			$list = explode(",",$rs['tag']);
			$tag_condition = false;
			foreach($list AS $key=>$value)
			{
				if($value && trim($value))
				{
					$tag_condition [] = " l.tag LIKE '%".trim($value)."%'";
				}
			}
			if($tag_condition)
			{
				$condition .= " AND (".implode(" OR ",$tag_condition).")";
			}
			unset($tid_sql,$tag_condition,$list);
		}
		if($rs['keywords'])
		{
			$list = explode(",",$rs['keywords']);
			$k_condition = false;
			foreach($list AS $key=>$value)
			{
				$k_condition [] = " l.seo_title LIKE '%".$value."%'";
				$k_condition [] = " l.seo_keywords LIKE '%".$value."%'";
				$k_condition [] = " l.seo_desc LIKE '%".$value."%'";
				$k_condition [] = " l.title LIKE '%".$value."%'";
				$k_condition [] = " l.tag LIKE '%".$value."%'";
			}
			$condition .= "AND (".implode(" OR ",$k_condition).") ";
			unset($k_condition,$list);
		}
		if($rs['fields_need'])
		{
			$list = explode(",",$rs['fields_need']);
			foreach($list AS $key=>$value)
			{
				$condition .= " AND ".$value." != '' AND ".$value." != '0' AND ".$value." is NOT NULL ";
			}
			unset($list);
		}
		if($rs['sqlext'])
		{
			$condition .= " AND ".$rs['sqlext'];
		}
		if($rs['ext'] && is_array($rs['ext']))
		{
			foreach($rs['ext'] AS $key=>$value)
			{
				$condition .= " AND ext.".$key."='".$value."' ";
			}
		}
		unset($rs);
		return $condition;
	}

	//格式化扩展数据
	private function _format_ext_all($rslist)
	{
		$res = '';
		foreach($rslist AS $key=>$value)
		{
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

	function _total($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			unset($rs);
			return false;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid'])
		{
			unset($rs);
			return false;
		}
		$project = $this->_project(array('pid'=>$rs['pid'],'site'=>$rs['site']));
		if(!$project || !$project['status'] || !$project['module'])
		{
			return false;
		}
		$condition = $this->_arc_condition($rs);
		return $this->model('list')->arc_count($project['module'],$condition);
	}

	//读取单篇文章
	private function _arc($param)
	{
		$tmpid = $param['phpok'] ? $param['phpok'] : ($param['title_id'] ? $param['title_id'] : $param['id']);
		if(!$tmpid)
		{
			return false;
		}
		$arc = $this->model('content')->get_one($tmpid);
		if(!$arc)
		{
			return false;
		}
		$arc['tag'] = $this->model('tag')->tag_list($arc['id'],$rs['site']);
		if(!$arc['module_id'])
		{
			$url_id = $arc['identifier'] ? $arc['identifier'] : $arc['id'];
			$arc['url'] = $this->url($url_id);
			return $arc;
		}
		$flist = $this->model('module')->fields_all($arc['module_id']);
		if(!$flist)
		{
			$url_id = $arc['identifier'] ? $arc['identifier'] : $arc['id'];
			$arc['url'] = $this->url($url_id);
			return $arc;
		}
		//格式化扩展内容
		foreach($flist AS $key=>$value)
		{
			//指定分类
			if($value['form_type'] == 'editor')
			{
				$value['pageid'] = intval($param['pageid']);
			}
			$arc[$value['identifier']] = $this->lib('form')->show($value,$arc[$value['identifier']]);
			if($value['form_type'] == 'url' && $arc[$value['identifier']] && $value['identifier'] != 'url')
			{
				if(!$arc['url'])
				{
					$arc['url'] = $arc[$value['identifier']];
				}
			}
			//针对编辑器内容的格式化
			if($value['form_type'] == 'editor')
			{
				if(is_array($arc[$value['identifier']]))
				{
					$arc[$value['identifier'].'_pagelist'] = $arc[$value['identifier']]['pagelist'];
					$arc[$value['identifier']] = $arc[$value['identifier']]['content'];
				}
			}
		}
		//如果未绑定网址
		if(!$arc['url'])
		{
			$url_id = $arc['identifier'] ? $arc['identifier'] : $arc['id'];
			$arc['url'] = $this->url($url_id);
		}
		return $arc;
	}

	//取得项目信息
	private function _project($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			return false;
		}
		$cache_tbl = $this->_cache_tbl('project');
		$cache_id = $this->db->cache_id(serialize($rs),$cache_tbl);
		$cache_info = $this->db->cache_get($cache_id);
		if($cache_info && !is_bool($cache_info))
		{
			return $cache_info;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid'])
		{
			return false;
		}
		$project = $this->model('project')->project_one($rs['site'],$rs['pid']);
		if(!$project || !$project['status'])
		{
			unset($rs);
			return false;
		}
		$project_tmp = $this->model('ext')->ext_all('project-'.$rs['pid'],true);
		//格式化扩展内容
		if($project_tmp)
		{
			foreach($project_tmp as $key=>$value)
			{
				$project_ext[$value['identifier']] = $this->lib('form')->show($value);
				if($value['form_type'] == 'url' && !$project['url'] && $value['content'])
				{
					$project['url'] = $project_ext[$value['identifier']];
				}
			}
			$project = array_merge($project_ext,$project);
			unset($project_ext,$project_tmp);
		}
		unset($rs);
		if(!$project['url'])
		{
			$project['url'] = $this->url($project['identifier']);
		}
		if($cache_id)
		{
			$this->db->cache_save($cache_id,$project);
		}
		return $project;
	}

	//读取分类树
	private function _catelist($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			return false;
		}
		$cache_tbl = $this->_cache_tbl('catelist');
		$cache_id = $this->db->cache_id(serialize($rs),$cache_tbl);
		$cache_info = $this->db->cache_get($cache_id);
		if($cache_info && !is_bool($cache_info))
		{
			return $cache_info;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		if(!$rs['pid'])
		{
			return false;
		}
		$project_rs = $this->_project(array('pid'=>$rs['pid'],'site'=>$rs['site']));
		if(!$project_rs || !$project_rs['status'] || !$project_rs['cate'])
		{
			return false;
		}
		if($rs['cate'])
		{
			$tmp = $this->model('id')->id($rs['cate'],$rs['site']);
			if($tmp && $tmp['type'] == 'cate')
			{
				$rs['cateid'] = $tmp['id'];
			}
		}
		if(!$rs['cateid'])
		{
			$rs['cateid'] = $project_rs['cate'];
		}
		$list = array();
		$cate_all = $this->model('cate')->cate_all($rs['site']);
		if(!$cate_all)
		{
			return false;
		}
		foreach($cate_all as $k=>$v)
		{
			$cate_tmp = $this->model('ext')->ext_all('cate-'.$v['id'],true);
			if($cate_tmp)
			{
				foreach($cate_tmp as $key=>$value)
				{
					$cate_ext[$value['identifier']] = $this->lib('form')->show($value);
					if($value['form_type'] == 'url' && $value['content'])
					{
						$value['content'] = unserialize($value['content']);
						$v['url'] = $value['content']['default'];
						if($this->site['url_type'] == 'rewrite' && $value['content']['rewrite'])
						{
							$v['url'] = $value['content']['rewrite'];
						}
					}
				}
				$v = array_merge($cate_ext,$v);
				unset($cate_ext,$cate_tmp);
			}
			$cate_all[$k] = $v;
		}
		$this->model('data')->cate_sublist($list,$project_rs['cate'],$cate_all,$project_rs['identifier']);
		if(!$list || !is_array($list) || count($list)<1)
		{
			return false;
		}
		//格式化分类
		$array = array('all'=>$list,'project'=>$project_rs);
		$array['cate'] = $this->cate(array('pid'=>$project_rs['id'],'cateid'=>$rs['cateid'],"site"=>$rs['site']));
		//读子分类
		foreach($list AS $key=>$value)
		{
			if($value['parent_id'] == $rs['cateid'])
			{
				$array['sublist'][$value['id']] = $value;
			}
		}
		//取得分类树
		$tree = array();
		foreach($list as $key=>$value)
		{
			if($value['parent_id'] == $rs['cateid'])
			{
				$tree[$value['id']] = $value;
				$this->model('data')->_tree($tree[$value['id']]['sublist'],$list,$value['id']);
			}
		}
		$array['tree'] = $tree;
		if($cache_id)
		{
			$this->db->cache_save($cache_id,$array);
		}
		return $array;
	}

	//读取当前分类信息
	private function _cate($rs)
	{
		if(!$rs['cateid'] && !$rs['phpok'] && !$rs['cate'])
		{
			return false;
		}
		$cache_tbl = $this->_cache_tbl('cate');
		$cache_id = $this->db->cache_id(serialize($rs),$cache_tbl);
		$cache_info = $this->db->cache_get($cache_id);
		if($cache_info && !is_bool($cache_info))
		{
			return $cache_info;
		}
		if(!$rs['cateid'])
		{
			$identifier = $rs['cate'] ? $rs['cate'] : $rs['phpok'];
			$tmp = $this->model('id')->id($identifier,$rs['site']);
			if(!$tmp || $tmp['type'] != 'cate')
			{
				unset($tmp,$rs);
				return false;
			}
			$rs['cateid'] = $tmp['id'];
			unset($tmp,$identifier);
		}
		$cate = $this->model('cate')->cate_one($rs['cateid'],$rs['site']);
		if(!$cate)
		{
			unset($rs);
			return false;
		}
		//增加扩展分类
		$cate_tmp = $this->model('ext')->ext_all('cate-'.$rs['cateid'],true);
		//格式化扩展内容
		if($cate_tmp)
		{
			foreach($cate_tmp as $key=>$value)
			{
				$cate_ext[$value['identifier']] = $this->lib('form')->show($value);
				if($value['form_type'] == 'url' && $value['content'])
				{
					$value['content'] = unserialize($value['content']);
					$cate['url'] = $value['content']['default'];
					if($this->site['url_type'] == 'rewrite' && $value['content']['rewrite'])
					{
						$cate['url'] = $value['content']['default'];
					}
				}
			}
			$cate = array_merge($cate_ext,$cate);
			unset($cate_ext,$cate_tmp);
		}
		if($cate['url'])
		{
			return $cate;
		}
		if(!$rs['pid'] && $rs['phpok'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				unset($tmp,$rs);
				return false;
			}
			$rs['pid'] = $tmp['id'];
			unset($tmp);
		}
		$project = false;
		if(!$rs['pid'])
		{
			return $cate;
		}
		$project = $this->model('project')->project_one($rs['site'],$rs['pid']);
		$cate['url'] = $this->url($project['identifier'],$cate['identifier']);
		if($cache_id)
		{
			$this->db->cache_save($cache_id,$cate);
		}
		return $cate;
	}

	private function _cate_id($rs)
	{
		return $this->_cate($rs);
	}

	//取得项目扩展字段
	private function _fields($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			return false;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				return false;
			}
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid'])
		{
			return false;
		}
		$project_rs = $this->model('project')->project_one($rs['site'],$rs['pid']);
		if(!$project_rs || !$project_rs['module'] || !$project_rs['status'])
		{
			return false;
		}
		$array = false;
		if($rs['in_title'])
		{
			$tmp_id = $rs['prefix'].'title';
			$tmp_title = $project_rs['alias_title'] ? $project_rs['alias_title'] : '主题';
			$array['title'] = array('id'=>0,"module_id"=>$project_rs['module'],'title'=>$tmp_title,'identifier'=>$tmp_id,'field_type'=>'varchar','form_type'=>'text','format'=>'safe','taxis'=>1,'width'=>'300','content'=>$rs['info']['title']);
		}
		$flist = $this->model('module')->fields_all($project_rs['module']);
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				if($value['ext'])
				{
					$value['ext'] = unserialize($value['ext']);
				}
				if(!$value['is_front'])
				{
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
				$array[$key] = $value;
			}
		}
		if(!$array)
		{
			return false;
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
				$array[$key] = $this->lib('form')->format($value);
			}
		}
		return $array;
	}

	//取得上一级项目
	function _parent($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			return false;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				return false;
			}
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid'])
		{
			return false;
		}
		$project = $this->_project($rs);
		if(!$project || !$project['status'] || !$project['parent_id'])
		{
			return false;
		}
		$rs['pid'] = $project['parent_id'];
		$project = $this->_project($rs);
		if(!$project || !$project['status'] || !$project['parent_id'])
		{
			return false;
		}
		return $project;
	}

	//读取当前项目下的子项目，支持多级
	function _sublist($rs)
	{
		if(!$rs['pid'] && !$rs['phpok'])
		{
			return false;
		}
		$cache_tbl = $this->_cache_tbl('sublist');
		$cache_id = $this->db->cache_id(serialize($rs),$cache_tbl);
		$cache_info = $this->db->cache_get($cache_id);
		if($cache_info && !is_bool($cache_info))
		{
			return $cache_info;
		}
		if(!$rs['pid'])
		{
			$tmp = $this->model('id')->id($rs['phpok'],$rs['site']);
			if(!$tmp || $tmp['type'] != 'project')
			{
				return false;
			}
			$rs['pid'] = $tmp['id'];
		}
		if(!$rs['pid'])
		{
			return false;
		}
		$rslist = $this->model('project')->get_all($rs['site'],$rs['pid'],'p.status=1','id');
		if(!$rslist)
		{
			return false;
		}
		$list = false;
		foreach($rslist AS $key=>$value)
		{
			$ext_rs = $this->model('ext')->ext_all('project-'.$value['id'],true);
			if($ext_rs)
			{
				$project_ext = false;
				foreach($ext_rs as $k=>$v)
				{
					$project_ext[$v['identifier']] = $this->lib('form')->show($v);
					if($v['form_type'] == 'url' && $v['content'])
					{
						$v['content'] = unserialize($v['content']);
						$value['url'] = $v['content']['default'];
						if($this->site['url_type'] == 'rewrite' && $v['content']['rewrite'])
						{
							$value['url'] = $v['content']['rewrite'];
						}
					}
				}
				if($project_ext)
				{
					$value = array_merge($project_ext,$value);
				}
			}
			$list[$value['id']] = $value;
		}
		unset($rslist);
		foreach($list AS $key=>$value)
		{
			if(!$value['url']) $value['url'] = $this->url($value['identifier']);
			$list[$key] = $value;
 		}
 		if($cache_id)
		{
			$this->db->cache_save($cache_id,$list);
		}
 		return $list;
	}

	//读取当前分类下的子分类
	private function _subcate($rs)
	{
		if(!$rs['cateid'] && !$rs['phpok'] && !$rs['cate'])
		{
			return false;
		}
		$cache_tbl = $this->_cache_tbl('catelist');
		$cache_id = $this->db->cache_id(serialize($rs),$cache_tbl);
		$cache_info = $this->db->cache_get($cache_id);
		if($cache_info && !is_bool($cache_info))
		{
			return $cache_info;
		}
		if(!$rs['cateid'])
		{
			$identifier = $rs['cate'] ? $rs['cate'] : $rs['phpok'];
			$tmp = $this->model('id')->id($identifier,$rs['site']);
			if(!$tmp || $tmp['type'] != 'cate')
			{
				return false;
			}
			$rs['cateid'] = $tmp['id'];
			unset($tmp,$identifier);
		}
		$cate_all = $this->model('cate')->cate_all($rs['site']);
		if(!$cate_all)
		{
			return false;
		}
		$list = false;
		foreach($cate_all as $k=>$v)
		{
			if($v['parent_id'] != $rs['cateid'])
			{
				continue;
			}
			$cate_tmp = $this->model('ext')->ext_all('cate-'.$v['id'],true);
			if($cate_tmp)
			{
				foreach($cate_tmp as $key=>$value)
				{
					$cate_ext[$value['identifier']] = $this->lib('form')->show($value);
					if($value['form_type'] == 'url' && $value['content'])
					{
						$value['content'] = unserialize($value['content']);
						$v['url'] = $value['content']['default'];
						if($this->site['url_type'] == 'rewrite' && $value['content']['rewrite'])
						{
							$v['url'] = $value['content']['rewrite'];
						}
					}
				}
				$v = array_merge($cate_ext,$v);
				unset($cate_ext,$cate_tmp);
			}
			$list[$k] = $v;
		}
		if($cache_id)
		{
			$this->db->cache_save($cache_id,$array);
		}
		return $list;
	}

	//读取附件信息
	private function _res($rs)
	{
		if(!$rs['fileid'] && !$rs['phpok'])
		{
			return false;
		}
		if(!$rs['fileid']) $rs['fileid'] = $rs['phpok'];
		return $this->model('res')->get_one(intval($rs['fileid']),true);
	}

	//读取附件列表
	private function _reslist($rs)
	{
		if(!$rs['fileids'] && !$rs['phpok'])
		{
			return false;
		}
		if(!$rs['fileids']) $rs['fileids'] = $rs['phpok'];
		if(is_array($rs['fileids']))
		{
			$rs['fileids'] = implode(",",$rs['fileids']);
		}
		$list = explode(",",$rs['fileids']);
		$ids = false;
		foreach($list as $key=>$value)
		{
			if($value && intval($value))
			{
				$ids[] = intval($value);
			}
		}
		$condition = " id IN(".implode(",",$ids).") ";
		return $this->model('res')->get_list($condition,0,999,true);
	}
	
	private function _cache_tbl($type)
	{
		$prefix = $this->db->prefix;
		$cache_tbl = array($prefix.'tag',$prefix.'tag_stat',$prefix.'res',$prefix.'res_ext',$prefix.'ext',$prefix.'extc');
		if(in_array($type,array('arc','arclist','total')))
		{
			$cache_tbl[] = $prefix.'list';
			$cache_tbl[] = $prefix.'project';
			$cache_tbl[] = $prefix.'cate';
			$cache_tbl[] = $prefix.'user';
			$cache_tbl[] = $prefix.'user_ext';
			$cache_tbl[] = $prefix.'module';
			$cache_tbl[] = $prefix.'module_fields';
			return $cache_tbl;
		}
		if($type == 'project' || $type == 'sublist' || $type == 'parent')
		{
			$cache_tbl[] = $prefix."project";
			return $cache_tbl;
		}
		if($type == 'catelist' || $type == 'cate')
		{
			$cache_tbl[] = $prefix."project";
			$cache_tbl[] = $prefix."cate";
			return $cache_tbl;
		}
		if(in_array($type,array('user','user_group')))
		{
			$cache_tbl[] = $prefix."user";
			$cache_tbl[] = $prefix."user_ext";
			$cache_tbl[] = $prefix."user_fields";
			$cache_tbl[] = $prefix."user_group";
		}
		return $cache_tbl;
	}
}
?>
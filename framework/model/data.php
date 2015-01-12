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
class data_model extends phpok_model
{
	//缓存数据
	private $cdata;
	function __construct()
	{
		parent::model();
	}

	//取得文章列表
	public function arclist($rs)
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
		$project_ext = $rs['in_project'] == 2 ? true : false;
		$project_rs = $this->_project($rs['pid'],$project_ext);
		if(!$project_rs) return false;
		if(!$project_rs['url']) $project_rs['url'] = $GLOBALS['app']->url($project_rs['identifier']);
		//判断是否有绑定模块，没有绑定模块，跳过
		if(!$project_rs['module']) return false;
		//取得扩展字段信息
		$flist = $this->module_field($project_rs['module']);
		$field = 'l.*';
		$nlist = "";
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				if($value['field_type'] != 'longtext' && $value['field_type'] != 'longblob')
				{
					$field .= ',ext.'.$key;
					$nlist[$key] = $value;
				}
				else
				{
					if($rs['in_text'] && ($value['field_type'] == 'longtext' || $value['field_type'] == 'longblob'))
					{
						$field .= ",ext.".$key;
						$nlist[$key] = $value;
					}
				}
			}
		}
		$sql = "SELECT ".$field." FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."list_".$project_rs['module']." ext ";
		$sql.= "ON(l.id=ext.id AND l.site_id=ext.site_id AND l.project_id=ext.project_id) ";
		$sql.= "WHERE l.project_id=".intval($rs['pid'])." AND l.site_id=".intval($this->site['id'])." ";
		$sql.= " AND l.hidden=0 ";
		if(!$rs['not_status'])
		{
			if($_SESSION['user_id'])
			{
				$sql .= " AND (l.status=1 OR (l.user_id=".intval($_SESSION['user_id'])." AND l.status=0)) ";
			}
			else
			{
				$sql .= " AND l.status=1 ";
			}
		}
		//$sql.= "AND l.status=1 AND l.hidden=0 ";
		//不包含主题
		if($rs['notin'])
		{
			$sql .= "AND l.id NOT IN(".$rs['notin'].") ";
		}
		if(!$rs['in_sub'])
		{
			$sql .= "AND l.parent_id=0 ";
		}
		if($rs['cate'])
		{
			$tmp = $this->_id($rs['cate'],$this->site['id']);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		if($rs['cateid'])
		{
			//取得站点下的所有分类
			$cate_all = $GLOBALS['app']->model('cate')->cate_all($rs['site_id']);
			//读取这个分类下的所有子分类信息
			$array = array($rs['cateid']);
			$this->_cate_id($array,$rs['cateid'],$cate_all);
			$sql .= "AND l.cate_id IN(".implode(",",$array).") ";
		}
		//绑定某个会员
		if($rs['user_id'])
		{
			$sql.= "AND l.user_id IN(".$rs['user_id'].") ";
		}
		if($rs['attr'])
		{
			$sql.= "AND l.attr LIKE '%".$rs['attr']."%' ";
		}

		if($rs['idin'])
		{
			$sql .= " AND l.id IN(".$rs['idin'].") ";
		}
		//绑定Tag
		if($rs['tag'])
		{
			$list = explode(",",$rs['tag']);
			$tid_sql = "SELECT DISTINCT l.tid FROM qinggan_tag_list l JOIN qinggan_tag t ON(l.id=t.id) ";
			$tag_condition = '';
			foreach($list AS $key=>$value)
			{
				$tag_condition [] = " t.title='".$value."'";
			}
			$tid_sql.= "WHERE (".implode(" OR ",$condition).") ";
			$condition .= " AND l.id IN(".$tid_sql.")";
		}
		//关键字
		if($rs['keywords'])
		{
			$list = explode(",",$rs['keywords']);
			$condition = '';
			foreach($list AS $key=>$value)
			{
				$condition [] = " l.seo_title LIKE '%".$value."%'";
				$condition [] = " l.seo_keywords LIKE '%".$value."%'";
				$condition [] = " l.seo_desc LIKE '%".$value."%'";
				$condition [] = " l.title LIKE '%".$value."%'";
				$condition [] = " l.tag LIKE '%".$value."%'";
			}
			$sql.= "AND (".implode(" OR ",$condition).") ";
		}
		//必须的字段
		if($rs['fields_need'])
		{
			$list = explode(",",$rs['fields_need']);
			foreach($list AS $key=>$value)
			{
				$sql .= " AND ".$value." != '' AND ".$value." != '0' AND ".$value." is NOT NULL ";
			}
		}
		//自定义SQL扩展
		if($rs['sqlext'])
		{
			$sql.= " AND ".$rs['sqlext'];
		}
		//更深一层的扩展
		if($rs['ext'] && is_array($rs['ext']))
		{
			foreach($rs['ext'] AS $key=>$value)
			{
				$sql .= " AND ext.".$key."='".$value."'";
			}
		}
		$orderby = $rs['orderby'] ? $rs['orderby'] : $project_rs['orderby'];
		if(!$orderby) $orderby = 'l.sort DESC,l.dateline DESC,l.id DESC ';
		$sql.= 'ORDER BY '.$orderby.' ';
		//非列表模式，强制只读取一条
		if(!$rs['is_list']) $rs['psize'] = 1;
		//
		if($rs['psize'])
		{
			$sql .= "LIMIT ".intval($rs['offset']).','.$rs['psize'];
		}
		$rslist = $this->db->get_all($sql);
		//当数据获取为空时，如果包含项目信息，将返回项目信息并返回空列表
		if(!$rslist && !$rs['in_project'] && !$rs['in_cate'])
		{
			return false;
		}
		//如果内容存在
		if($rslist)
		{
			//更新附件信息，分类信息，会员信息，主题信息
			$res = $cate = $user = $tid = "";
			foreach($rslist AS $key=>$value)
			{
				//绑定分类
				if($value['cate_id']) $cate[] = $value['cate_id'];
				//绑定会员
				if($value['user_id']) $user[] = $value['user_id'];
				//格式化扩展字段
				if($nlist && is_array($nlist))
				{
					foreach($nlist AS $k=>$v)
					{
						//绑定上传的附件
						if($value[$k] && $v['form_type'] == 'upload')
						{
							$tmp = explode(",",$value[$k]);
							foreach($tmp AS $kk=>$vv)
							{
								$vv = intval($vv);
								if($vv) $res[] = $vv;
							}
						}
						elseif($value[$k] && $v['form_type'] == 'title')
						{
							$tmp = explode(",",$value[$k]);
							foreach($tmp AS $kk=>$vv)
							{
								$vv = intval($vv);
								if($vv) $tid[] = $vv;
							}
						}
						elseif($value[$k] && $v['form_type'] == 'url')
						{
							$tmp = unserialize($value[$k]);
							$link = $this->site['url_type'] == 'rewrite' ? $tmp['rewrite'] : $tmp['default'];
							if(!$link) $link = $tmp['default'];
							$value[$k] = $link;
							if(!$value['url'] && $k != 'url') $value['url'] = $link;
						}
					}
				}
				//绑定链接
				if(!$value['url'])
				{
					$value['url'] = msgurl(($value['identifier'] ? $value['identifier'] : $value['id']));
				}
				$rslist[$key] = $value;
			}
			if($res) $res = $this->_res_info2($res);
			if($cate) $cate = $this->_cate_info2($cate);
			if($user) $user = $this->_user_info2($user);
			if($tid) $tid = $this->_tid_info($tid);
			foreach($rslist AS $key=>$value)
			{
				$rslist[$key] = $this->_format($value,$nlist,$res,$cate,$user,$tid);
			}
			//如果包含子主题，再执行一次格式化
			if($rs['in_sub'])
			{
				$list = '';
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
			}
		}
		if(!$rs['in_cate'] && !$rs['in_project'] && $rslist)
		{
			if(!$rs['is_list']) return current($rslist);
			return $rslist;
		}
		//如果包含项目
		$array = array();
		if($rs['in_project'])
		{
			$array['project'] = $project_rs;
		}
		if($rs['in_cate'])
		{
			$cate_ext = $rs['in_cate'] == 2 ? true : false;
			$array['cate'] = $this->cate(array("pid"=>$rs['pid'],"cateid"=>$rs['cateid'],"cate_ext"=>$cate_ext));
		}
		if(!$rs['is_list'] && $rslist)
		{
			$array['rs'] = current($rslist);
		}
		else
		{
			$array['rslist'] = $rslist;
		}
		return $array;		
	}

	//取得文章总数
	public function total($rs)
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
		//判断是否有绑定模块，没有绑定模块，跳过
		if(!$project_rs['module']) return false;
		$sql = "SELECT count(l.id) FROM ".$this->db->prefix."list l ";
		$sql.= "JOIN ".$this->db->prefix."list_".$project_rs['module']." ext ON(l.id=ext.id AND l.site_id=ext.site_id) ";
		$sql.= "WHERE l.project_id=".intval($rs['pid'])." AND l.site_id=".intval($this->site['id'])." ";
		$sql.= " AND l.hidden=0 ";
		if(!$rs['not_status']) $sql .= " AND l.status=1 ";
		//不包含主题
		if($rs['notin'])
		{
			$sql .= "AND l.id NOT IN(".$rs['notin'].") ";
		}
		if(!$rs['in_sub'])
		{
			$sql .= "AND l.parent_id=0 ";
		}
		if($rs['cate'])
		{
			$tmp = $this->_id($rs['cate'],$this->site['id']);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		if($rs['cateid'])
		{
			$cate_all = $GLOBALS['app']->model('cate')->cate_all($rs['site_id']);
			$array = array($rs['cateid']);
			$this->_cate_id($array,$rs['cateid'],$cate_all);
			$sql .= "AND l.cate_id IN(".implode(",",$array).") ";
		}
		//绑定某个会员
		if($rs['user_id'])
		{
			$sql.= "AND l.user_id IN(".$rs['user_id'].") ";
		}
		if($rs['attr'])
		{
			$sql.= "AND l.attr LIKE '%".$rs['attr']."%' ";
		}
		//绑定Tag
		if($rs['tag'])
		{
			$list = explode(",",$rs['tag']);
			$tid_sql = "SELECT DISTINCT l.tid FROM qinggan_tag_list l JOIN qinggan_tag t ON(l.id=t.id) ";
			$tag_condition = '';
			foreach($list AS $key=>$value)
			{
				$tag_condition [] = " t.title='".$value."'";
			}
			$tid_sql.= "WHERE (".implode(" OR ",$condition).") ";
			$condition .= " AND l.id IN(".$tid_sql.")";
		}
		//关键字
		if($rs['keywords'])
		{
			$list = explode(",",$rs['keywords']);
			$condition = '';
			foreach($list AS $key=>$value)
			{
				$condition [] = " l.seo_title LIKE '%".$value."%'";
				$condition [] = " l.seo_keywords LIKE '%".$value."%'";
				$condition [] = " l.seo_desc LIKE '%".$value."%'";
				$condition [] = " l.title LIKE '%".$value."%'";
				$condition [] = " l.tag LIKE '%".$value."%'";
			}
			$sql.= "AND (".implode(" OR ",$condition).") ";
		}
		//必须的字段
		if($rs['fields_need'])
		{
			$list = explode(",",$rs['fields_need']);
			foreach($list AS $key=>$value)
			{
				$sql.= " AND ".$value." != '' AND ".$value." is NOT NULL AND ".$value." != 0 ";
			}
		}
		//自定义SQL扩展
		if($rs['sqlext'])
		{
			$sql.= " AND ".$rs['sqlext'];
		}
		return $this->db->count($sql);
	}
	//取得单篇文章信息
	public function arc($param)
	{
		$tmpid = $param['phpok'] ? $param['phpok'] : ($param['title_id'] ? $param['title_id'] : $param['id']);
		if(!$tmpid)
		{
			return false;
		}
		$rs = $this->_list_info($tmpid);
		if(!$rs)
		{
			return false;
		}
		//无绑定模块时直接返回结果
		if(!$rs['module_id'])
		{
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$rs['url'] = $this->url($url_id);
			return $rs;
		}
		//格式化内容
		$flist = $this->module_field($rs['module_id']);
		if(!$flist)
		{
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$rs['url'] = $this->url($url_id);
			return $rs;
		}
		//格式化扩展内容
		foreach($flist AS $key=>$value)
		{
			//针对编辑器内容的格式化
			if($value['form_type'] == 'editor')
			{
				$tmp = $this->info_page($rs[$value['identifier']],$param['pageid']);
				if($tmp && is_array($tmp))
				{
					$rs[$value['identifier'].'_pagelist'] = $tmp['pagelist'];
					$rs[$value['identifier']] = $tmp['content'];
				}
				else
				{
					$rs[$value['identifier']] = $tmp;
				}
				
				$rs[$value['identifier']] = phpok_ubb($rs[$value['identifier']],false);
			}
			//针对网址进行格式化
			elseif($value['form_type'] == 'url' && $rs[$value['identifier']])
			{
				$tmp = unserialize($rs[$value['identifier']]);
				$rs[$value['identifier']] = $tmp[$this->site['url_type']];
				$rs['url'] = $rs[$value['identifier']];
			}
			//当表单属性不为url时，对内容再执行一次格式化
			if($value['form_type'] != 'url')
			{
				$rs[$value['identifier']] = $this->_format_info($value,$rs[$value['identifier']]);
			}
		}
		//如果未绑定网址
		if(!$rs['url'])
		{
			$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
			$rs['url'] = $this->url($url_id);
		}
		return $rs;
	}

	//格式化扩展字段信息
	private function _format_info($rs,$content='')
	{
		//字段内容为空时
		if($content == '')
		{
			return false;
		}
		//字段属性为空时
		if(!$rs || !is_array($rs))
		{
			return $content;
		}
		//获取用户自定义的格式化信息，当自定义格式化为空时，将继续执行下一步内容
		$info = $GLOBALS['app']->lib('form')->show($rs,$content);
		if($info)
		{
			return $info;
		}
		//如果表单属性有扩展信息时间
		if($rs['ext'] && is_string($rs['ext']))
		{
			$rs['ext'] = unserialize($rs['ext']);
		}
		//表单属性为：文本框、密码框、文本区及代码编辑器时，不执行内容格式化
		$not_format = array('text','password','textarea','code_editor','editor');
		if(in_array($rs['form_type'],$not_format))
		{
			return $content;
		}
		//单选
		if($rs['form_type'] == 'radio')
		{
			return $this->_format_optlist($rs,$content);
		}
		//复选
		if($rs['form_type'] == 'checkbox')
		{
			$content = unserialize($content);
			$list = array();
			foreach($content AS $key=>$value)
			{
				$list[$value] = $this->_format_optlist($rs,$value);
			}
			return $list;
		}
		//下拉，多选
		if($rs['form_type'] == 'selsect' && $rs['ext']['is_multiple'])
		{
			$content = unserialize($content);
			$list = array();
			foreach($content AS $key=>$value)
			{
				$list[$value] = $this->_format_optlist($rs,$value);
			}
			return $list;
		}
		//下拉，单选
		if($rs['form_type'] == 'select' && !$rs['ext']['is_multiple'])
		{
			return $this->_format_optlist($rs,$content);
		}
		//附件，多图
		if($rs['form_type'] == 'upload' && $rs['ext']['is_multiple'])
		{
			$content = explode(",",$content);
			$list = array();
			foreach($content AS $key=>$value)
			{
				$tmp = $this->_res_info($value);
				if($tmp)
				{
					$list[$value] = $tmp;
				}
			}
			return $list;
		}
		//附件，单图
		if($rs['form_type'] == 'upload' && !$rs['ext']['is_multiple'])
		{
			$content = intval($content);
			if(!$content)
			{
				return false;
			}
			return $this->_res_info($content);
		}
		//主题，单个
		if($rs['form_type'] == 'title' && !$rs['ext']['is_multiple'])
		{
			$content = intval($content);
			if(!$content)
			{
				return false;
			}
			return $this->_list_info($content);
		}
		//主题，多个
		if($rs['form_type'] == 'title' && $rs['ext']['is_multiple'])
		{
			$content = explode(",",$content);
			$list = array();
			foreach($content AS $key=>$value)
			{
				$tmp = $this->_list_info($value);
				if($tmp)
				{
					$list[$value] = $tmp;
				}
			}
			return $list;
		}
		if($rs['form_type'] == 'user' && $rs['ext']['is_multiple'])
		{
			$content = explode(",",$content);
			$list = array();
			foreach($content AS $key=>$value)
			{
				$tmp = $this->_user_info($value);
				if($tmp)
				{
					$list[$value] = $tmp;
				}
			}
			return $list;
		}
		if($rs['form_type'] == 'user' && !$rs['ext']['is_multiple'])
		{
			$content = intval($content);
			return $this->_user_info($content);
		}
		return $content;
	}

	private function _res_info($id)
	{
		if(!$id)
		{
			return false;
		}
		//如果存在缓存中
		if($this->cdata['res'][$id])
		{
			return $this->cdata['res'][$id];
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
		}
		//存储到缓存中
		$this->cdata['res'][$id] = $rs;
		return $rs;
	}

	//格式化下菜单选项等内容
	function _format_optlist($rs,$content='')
	{
		if($content == '' || !$rs || !is_array($rs))
		{
			return false;
		}
		//当没有下拉属性时，直接返回结果数组
		if(!$rs['ext']['option_list'])
		{
			return array('id'=>'','val'=>$content,'title'=>$content);
		}
		$opt = explode(':',$rs['ext']['option_list']);
		//当下拉选项为表单项时
		if($opt[0] == 'opt')
		{
			$sql = "SELECT id,val,title FROM ".$this->db->prefix."opt WHERE group_id='".$opt[1]."' AND val='".$content."'";
			return $this->db->get_one($sql);
		}
		//当下拉项为子项目时
		if($opt[0] == 'project')
		{
			$info = $this->_project_info($content);
			$info['url'] = $this->url($info['identifier']);
			$info['val'] = $info['id'];
			return $info;
		}
		//当下拉为子分类时
		if($opt[0] == 'cate')
		{
			$info = $this->_cate_info($content);
			$info['val'] = $info['id'];
			return $info;
		}
		//当下拉菜单子主题时
		if($opt[0] == 'title')
		{
			$info = $this->_list_info($content);
			$info['val'] = $info['id'];
			return $info;
		}
		return array('id'=>'','val'=>$content,'title'=>$content);
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
			$ext = $this->ext_all('cate-'.$cate_rs['id']);
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
			$ext = $this->ext_all('cate-'.$cate_rs['id']);
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
	
	//取得分类列表
	public function catelist($rs)
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
		$project_rs = $this->_project($rs['pid'],true);
		//判断是否有绑定分类
		if(!$project_rs['cate']) return false;
		if($rs['cate'])
		{
			$tmp = $this->_id($rs['cate'],$this->site['id']);
			if($tmp['type'] == 'cate') $rs['cateid'] = $tmp['id'];
		}
		$list = array();
		$cate_all = $GLOBALS['app']->model('cate')->cate_all($rs['site_id']);
		$this->cate_sublist($list,$project_rs['cate'],$cate_all,$project_rs['identifier']);
		if(!$list || !is_array($list) || count($list)<1) return false;
		//格式化分类
		$array = array('all'=>$list,'project'=>$project_rs);
		//
		$cateid = $rs['cateid'] ? $rs['cateid'] : $project_rs['cate'];
		$array['cate'] = $this->cate(array('pid'=>$project_rs['id'],'cateid'=>$cateid,"cate_ext"=>true));
		//读子分类
		foreach($list AS $key=>$value)
		{
			if($value['parent_id'] == $cateid)
			{
				$array['sublist'][$value['id']] = $value;
			}
		}
		//取得分类树
		$tree = array();
		foreach($list as $key=>$value)
		{
			if($value['parent_id'] == $cateid)
			{
				$tree[$value['id']] = $value;
				$this->_tree($tree[$value['id']]['sublist'],$list,$value['id']);
			}
		}
		$array['tree'] = $tree;
		return $array;
	}


	private function _tree(&$list,$catelist,$parent_id=0)
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
				$ext_rs = $this->ext_all('project-'.$value['id']);
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
	private function cate_sublist(&$list,$parent_id=0,$rslist='',$identifier='')
	{
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				if($value['parent_id'] == $parent_id)
				{
					if($identifier)
					{
						$value['url'] = $GLOBALS['app']->url($identifier,$value['identifier']);
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
		if($this->cdata['project'][$id])
		{
			$rs = $this->cdata['project'][$id];
		}
		else
		{
			$sql = "SELECT * FROM ".$this->db->prefix."project WHERE id=".intval($id);
			$rs = $this->db->get_one($sql);
			$this->cdata['project'][$id] = $rs;
		}
		if(!$rs) return false;
		if($ext)
		{
			$ext = $this->ext_all('project-'.$id);
			if($ext) $rs = array_merge($ext,$rs);
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
		$rslist = false;
		$site_id = intval($site_id);
		$site_id = $site_id ? '0,'.$site_id : '0';
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."project WHERE site_id IN(".$site_id.")";
		$tmplist = $this->db->get_all($sql);
		if($tmplist)
		{
			foreach($tmplist as $key=>$value)
			{
				$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'project');
			}
		}
		//读分类
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."cate WHERE site_id IN(".$site_id.")";
		$tmplist = $this->db->get_all($sql);
		if($tmplist)
		{
			foreach($tmplist as $key=>$value)
			{
				if(!$rslist[$value['identifier']])
				{
					$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'cate');
				}
			}
		}
		//读主题
		$sql = "SELECT id,identifier FROM ".$this->db->prefix."list WHERE identifier!='' AND site_id IN(".$site_id.")";
		$tmplist = $this->db->get_all($sql);
		if($tmplist)
		{
			foreach($tmplist as $key=>$value)
			{
				if(!$rslist[$value['identifier']])
				{
					$rslist[$value['identifier']] = array('id'=>$value['id'],'type'=>'content');
				}
			}
		}
		return $rslist;
	}

	//获取项目，分类的扩展信息
	public function ext_all($id)
	{
		$sql = "SELECT ext.ext,ext.identifier,ext.form_type,c.content FROM ".$this->db->prefix."ext ext ";
		$sql.= "LEFT JOIN ".$this->db->prefix."extc c ON(ext.id=c.id) ";
		$sql.= "WHERE ext.module='".$id."'";
		$rslist = $this->db->get_all($sql,'identifier');
		if(!$rslist) return false;
		$res = '';
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
				$value['content'] = str_replace('[:page:]','',$value['content']);
				$value['content'] = phpok_ubb($value['content'],false);
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
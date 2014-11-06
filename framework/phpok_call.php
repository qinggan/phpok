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
		//判断是内置参数还是调用数据中心的数据
		if(substr($id,0,1) != '_')
		{
			$call_rs = $GLOBALS['app']->model('call')->one($id,$this->site['id']);
			if(!$call_rs)
			{
				return false;
			}
			if($rs && is_array($rs))
			{
				$call_rs = array_merge($call_rs,$rs);
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
		}
		$func = '_'.$call_rs['type_id'];
		if(!in_array($func,$this->mlist))
		{
			return false;
		}
		return $this->$func($call_rs);
	}

	//读取文章列表
	function _arclist($rs)
	{
		return $GLOBALS['app']->model('data')->arclist($rs);
	}

	function _total($rs)
	{
		return $GLOBALS['app']->model('data')->total($rs);
	}

	//读取单篇文章
	function _arc($rs)
	{
		return $GLOBALS['app']->model('data')->arc($rs);
	}

	//取得项目信息
	function _project($rs)
	{
		return $GLOBALS['app']->model('data')->project($rs);
	}

	//读取分类树
	function _catelist($rs)
	{
		return $GLOBALS['app']->model('data')->catelist($rs);
	}

	//读取当前分类信息
	function _cate($rs)
	{
		return $GLOBALS['app']->model('data')->cate($rs);
	}

	function _cate_id($rs)
	{
		return $GLOBALS['app']->model('data')->cate_id($rs);
	}

	//取得项目扩展字段
	function _fields($rs)
	{
		return $GLOBALS['app']->model('data')->fields($rs);
	}

	//取得上一级项目
	function _parent($rs)
	{
		return $GLOBALS['app']->model('data')->_project_parent($rs);
	}

	//读取当前项目下的子项目，支持多级
	function _sublist($rs)
	{
		return $GLOBALS['app']->model('data')->sublist($rs);
	}

	//读取当前分类下的子分类
	function _subcate($rs)
	{
		return $GLOBALS['app']->model('data')->subcate($rs);
	}
}
?>
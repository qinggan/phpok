<?php
/***********************************************************
	Filename: {phpok}/libs/form.php
	Note	: 表单选项管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_lib
{
	//表单对象
	public $cls;

	//构造函数
	function __construct()
	{
		//自动装载表单信息
		$flist = $GLOBALS['app']->model('form')->form_all();
		if($flist)
		{
			foreach($flist AS $key=>$value)
			{
				$file = $GLOBALS['app']->dir_phpok.'form/'.$key.'_'.$GLOBALS['app']->app_id.'.php';
				if(!file_exists($file))
				{
					$file = $GLOBALS['app']->dir_phpok.'form/'.$key.'_admin.php';
				}
				//如果文件存在
				if(file_exists($file))
				{
					$cls_name = $key."_form";
					include_once($file);
					$this->cls[$key] = new $cls_name();
				}
			}
		}
	}

	//格式化表单信息
	function format($rs)
	{
		//对象不存在时，返回否
		if(!$this->cls[$rs['form_type']])
		{
			return false;
		}
		$info = $this->cls[$rs['form_type']]->format($rs);
		$rs['html'] = $info;
		return $rs;
	}

	//获取内容信息
	function get($rs)
	{
		//对象不存在时，返回否
		if(!$this->cls[$rs['form_type']])
		{
			return false;
		}
		$mlist = get_class_methods($this->cls[$rs['form_type']]);
		if(in_array('get',$mlist))
		{
			return $this->cls[$rs['form_type']]->get($rs);
		}
		return false;
	}

	//输出内容信息
	function show($rs,$value='')
	{
		if(!$this->cls[$rs['form_type']])
		{
			return false;
		}
		$mlist = get_class_methods($this->cls[$rs['form_type']]);
		if(in_array('show',$mlist))
		{
			if(!$value) $value = $rs['content'];
			return $this->cls[$rs['form_type']]->show($rs,$value);
		}
		return false;
	}


	//弹出窗口，用于创建字段
	function open_form_setting($saveurl)
	{
		if(!$saveurl) return false;
		$GLOBALS['app']->assign('saveUrl',$saveurl);
		//读取格式化类型
		$field_list = $GLOBALS['app']->model('form')->field_all();
		$form_list = $GLOBALS['app']->model('form')->form_all();
		$format_list = $GLOBALS['app']->model('form')->format_all();
		$GLOBALS['app']->assign('fields',$field_list);
		$GLOBALS['app']->assign('formats',$format_list);
		$GLOBALS['app']->assign('forms',$form_list);
		//创建字段
		$GLOBALS['app']->view("field_create");
	}

	//格式化值，对应的表单内容
	function info($val,$rs)
	{
		if($val == '' || !$rs || !is_array($rs)) return $val;
		//如果只是普通的文本框
		if($rs['form_type'] == 'text' || $rs['form_type'] == 'password')
		{
			return $val;
		}
		//如果是代码编辑器 或是 文本区
		if($rs['form_type'] == 'code_editor' || $rs['form_type'] == 'textarea')
		{
			return $val;
		}
		//如果是编辑器
		if($rs['form_type'] == 'editor')
		{
			return phpok_ubb($val);
		}
		//如果是单选框
		if($rs['form_type'] == 'radio')
		{
			if(!$rs["option_list"]) $rs['option_list'] = 'default:0';
			$opt_list = explode(":",$rs["option_list"]);
			$rslist = opt_rslist($opt_list[0],$opt_list[1],$rs['ext_select']);
			//如果内容为空，则返回空信息
			if(!$rslist) return false;
			foreach($rslist AS $key=>$value)
			{
				//
			}
		}
		return $val;
	}

	function cssjs($rs='')
	{
		if($rs && $this->cls[$rs['form_type']])
		{
			$obj = $this->cls[$rs['form_type']];
			$mlist = get_class_methods($obj);
			if(in_array('cssjs',$mlist))
			{
				$obj->cssjs();
			}
			return true;
		}
		foreach($this->cls as $key=>$value)
		{
			$mlist = get_class_methods($value);
			if(in_array('cssjs',$mlist))
			{
				$value->cssjs();
			}
		}
		return true;
	}
}
?>
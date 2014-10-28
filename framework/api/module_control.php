<?php
/***********************************************************
	Filename: {phpok}/api/module_control.php
	Note	: 模块相关信息操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月13日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function field_addok_f()
	{
		if(!$_SESSION['admin_id'] || !$_SESSION['admin_rs']['if_system']) $this->json('只有系统管理员才有此权限');
		//指定模块
		$mid = $this->get('mid','int');
		if(!$mid) $this->json('未指要添加到哪个模块');
		$array = array('module_id'=>$mid);
		$array['title'] = $this->get("title");
		if(!$array['title']) $this->json('请填写名称');
		$array['note'] = $this->get("note");
		$identifier = $this->get('identifier');
		//字段不能为空
		if(!$identifier) $this->json('字段标识不能为空');
		//字段小写化
		$identifier = strtolower($identifier);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier))
		{
			$this->json('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头');
		}
		//系统禁用的字符
		if($identifier == 'phpok') $this->json('系统禁用字符，请不要使用');
		//判断是否是否已使用
		$flist = $this->model('fields')->tbl_fields('list');
		if($flist && in_array($identifier,$flist)) $this->json('字符已经存在');
		//判断字符在扩展模块中是否已被使用
		$flist = $this->model('fields')->tbl_fields('list_'.$mid);
		if($flist && in_array($identifier,$flist)) $this->json('字符在扩展表中已使用');
		$array['identifier'] = $identifier;
		//其他
		$array['field_type'] = $this->get("field_type");
		$array['form_type'] = $this->get("form_type");
		$array['form_style'] = $this->get("form_style");
		$array['format'] = $this->get("format");
		$array['content'] = $this->get("content");
		$array['taxis'] = $this->get("taxis","int");
		$array['is_front'] = $this->get('is_front','int');
		
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id)
		{
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value)
			{
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox")
				{
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}
				else
				{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}		
		$array['ext'] = ($ext && count($ext)>0) ? serialize($ext) : "";
		//存储扩展表
		$this->model('module')->fields_save($array);
		//更新扩展表信息
		$tbl_exists = $this->model('module')->chk_tbl_exists($mid);
		if(!$tbl_exists)
		{
			$this->model('module')->create_tbl($mid);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($mid);
			if(!$tbl_exists2)
			{
				$this->json("模块：".$rs["title"]." 创建表失败，请检查！");
			}
		}
		$list = $this->model('module')->fields_all($mid);
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				$this->model('module')->create_fields($mid,$value);
			}
		}
		$this->json("字段添加成功",true);
	}
}

?>
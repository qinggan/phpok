<?php
/***********************************************************
	Filename: {phpok}/admin/form_control.php
	Note	: 控制器管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:45
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function config_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			exit("未指定ID");
		}
		$idfile = $this->dir_phpok."form/".$id."_".$this->app_id.".php";
		if(!file_exists($idfile))
		{
			exit("文件：".$idfile." 不存在");
		}
		include_once($idfile);
		$name = $id."_form";
		$form = new $name();
		$list = get_class_methods($form);
		if(!in_array("config",$list))
		{
			exit("方法：config 不存在");
		}
		$eid = $this->get("eid","int");
		$etype = $this->get("etype");
		if(!$etype) $etype = "ext";
		if($eid)
		{
			if($etype == "fields")
			{
				$this->model("fields");
				$rs = $this->model('fields')->get_one($eid);
			}
			elseif($etype == "module")
			{
				$this->model("module");
				$rs = $this->model('module')->field_one($eid);
			}
			elseif($etype == "user")
			{
				$this->model("user");
				$rs = $this->model('user')->field_one($eid);
			}
			else
			{
				$this->model("ext");
				$rs = $this->model('ext')->get_one($eid);
			}
			if($rs["ext"])
			{
				$ext = unserialize($rs["ext"]);
				foreach($ext AS $key=>$value)
				{
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
		}
		$form->config();
	}
}
?>
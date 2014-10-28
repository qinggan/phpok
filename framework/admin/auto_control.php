<?php
/***********************************************************
	Filename: {phpok}/admin/auto_control.php
	Note	: 自动读写表单处理（数据表qinggan_temp）
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-10 00:01
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class auto_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	# 存储表单
	function index_f()
	{
		$type = $this->get("__type");
		if(!$type) $type = "list";
		$str = $_POST ? serialize($_POST) : "";
		if(!$str)
		{
			json_exit("没有自动存储的表单数据",true);
		}
		$rs = $this->model('temp')->chk($type,$_SESSION["admin_id"]);
		if($rs)
		{
			$id = $rs["id"];
			unset($rs["id"]);
			$rs["content"] = $str;
		}
		else
		{
			$rs["content"] = $str;
			$rs["tbl"] = $type;
			$rs["admin_id"] = $_SESSION["admin_id"];
		}
		$this->model('temp')->save($rs,$id);
		json_exit("数据存储成功！",true);
	}

	function read_f()
	{
		$type = $this->get("__type");
		if(!$type) $type = "list";
		$rs = $this->model('temp')->chk($type,$_SESSION["admin_id"]);
		if($rs)
		{
			$content = unserialize($rs["content"]);
			json_exit($content,true);
		}
		else
		{
			json_exit("没有数据");
		}
	}

}
?>
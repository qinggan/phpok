<?php
/***********************************************************
	Filename: {phpok}/ajax_control.php
	Note	: Ajax公共操作，不限前台，后台
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年06月15日 10时02分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ajax_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->lib("json");
	}

	//默认返回字符串信息
	function index_f()
	{
		$this->exit_f();
	}

	//默认返回JSON信息
	function json_f()
	{
		$filename = $this->get("filename");
		if(!$filename)
		{
			$this->json_exit("Ajax目标文件不能为空！");
		}
		$ajax_file = $this->dir_phpok."ajax/".$this->app_id."_".$filename.".php";
		if(!is_file($ajax_file))
		{
			$ajax_file = $this->dir_root."ajax/".$this->app_id."_".$filename.".php";
			if(!is_file($ajax_file))
			{
				$this->json_exit("Ajax文件：".$ajax_file."不存在");
			}
		}
		$rs = include $ajax_file;
		if(!$rs)
		{
			$this->json_exit("反回异常");
		}
		if(is_array($rs) && $rs["status"])
		{
			if($rs["status"] == "ok")
			{
				$this->json_exit($rs["content"],true);
			}
			else
			{
				$this->json_exit($rs["content"]);
			}
		}
		exit($this->json_lib->encode($rs));
	}

	function json_exit($content,$status=false)
	{
		$rs = array();
		$rs["status"] = $status ? "ok" : "error";
		$rs["content"] = $content;
		exit($this->json_lib->encode($rs));
	}
	
	//返回字符串信息
	function exit_f()
	{
		$filename = $this->get("filename");
		if(!$filename)
		{
			exit("Ajax目标文件不能为空！");
		}
		$ajax_file = $this->dir_phpok."ajax/".$this->app_id."_".$filename.".php";
		if(!file_exists($ajax_file))
		{
			$ajax_file = $this->dir_root."ajax/".$this->app_id."_".$filename.".php";
			if(!file_exists($ajax_file))
			{
				exit("Ajax文件：".$ajax_file."不存在");
			}
		}
		$rs = include $ajax_file;
		if(!$rs)
		{
			json_exit("反回异常");
		}
		if(!is_array($rs) && !is_object($rs))
		{
			exit($rs);
		}
		if(is_array($rs))
		{
			exit($rs["content"]);
		}
		if(is_object($rs))
		{
			exit($rs->content);
		}
		exit("ok");
	}
}
?>
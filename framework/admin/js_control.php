<?php
/***********************************************************
	Filename: phpok/admin/js_control.php
	Note	: JS控制器，这里用来控制后台的JS信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-29 20:22
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class js_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$file = $_SERVER["SCRIPT_NAME"] ? basename($_SERVER["SCRIPT_NAME"]) : basename($_SERVER["SCRIPT_FILENAME"]);
		# 加载配置常用的JS
		$this->assign("basefile",$file);
		$this->assign("ctrl_id",$this->config["ctrl_id"]);
		$this->assign("func_id",$this->config["func_id"]);
		//header('Content-type: application/json; charset=UTF-8');
		header("Content-type: application/x-javascript; charset=UTF-8");
		// 时间总是过去的
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		// 文件总是被修改的
		header("Last-Modified:". gmdate("D, d M Y H:i:s") ." GMT");
		// HTTP/1.1
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		// HTTP/1.0
		header("Pragma: no-cache");
		$ext = $this->get("ext");
		$ext_js = "\n";
		$ext_js.= $this->lib('file')->cat($this->dir_phpok."jquery.js");
		$ext_js.= "\n";
		$ext_js.= $this->lib('file')->cat($this->dir_phpok."form.js");
		$ext_js.= "\n";
		$autoload_js = $this->config["autoload_js"];
		if($autoload_js)
		{
			$ext = $ext ? ",".$autoload_js : $autoload_js;
		}
		if($ext)
		{
			$list = explode(",",$ext);
			$list = array_unique($list);
			foreach($list AS $key=>$value)
			{
				$value = trim($value);
				if($value && file_exists($this->dir_phpok."js/".$value) && $value != "jquery.js")
				{
					$ext_js .= $this->lib('file')->cat($this->dir_phpok."js/".$value);
					$ext_js .= "\n";
				}
			}
		}
		$this->assign("ext_js",$ext_js);
		$this->view("js");
	}

	# 人工指定js
	function ext_f()
	{
		$js = $this->get("js");
		if($js)
		{
			header("Content-type: application/x-javascript; charset=UTF-8");
			// 时间总是过去的
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			// 文件总是被修改的
			header("Last-Modified:". gmdate("D, d M Y H:i:s",($this->time - 3600 * 24)) ." GMT");
			// HTTP/1.1
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			// HTTP/1.0
			header("Pragma: no-cache");
			$list = explode(",",$js);
			$ext_js = "\n";
			foreach($list AS $key=>$value)
			{
				$value = trim($value);
				if($value && file_exists($this->dir_phpok."js/".$value) && $value != "jquery.js")
				{
					$ext_js .= $this->lib('file')->cat($this->dir_phpok."js/".$value);
					$ext_js .= "\n";
				}
			}
			echo $ext_js;
		}
	}

	function pingyin_f()
	{
		$this->lib("pingyin");
 		$this->lib('pingyin')->path = $this->dir_phpok."dict/pingyin.qdb";
 		$title = $this->get("title");
 		$py = iconv("UTF-8","GBK",$title);
	 	$py = $this->lib('pingyin')->ChineseToPinyin($py);
	 	if(!$py)
	 	{
		 	exit("error");
	 	}
	 	$py = strtolower($py);
	 	$safe_string = "abcdefghijklmnopqrstuvwxyz0123456789-_";
		$str_array = str_split($py);
		$safe_array = str_split($safe_string);
		$string = "";
		foreach($str_array AS $key=>$value)
		{
			if(in_array($value,$safe_array))
			{
				$string .= $value;
			}
			else
			{
				$string .= "-";
			}
		}
		exit($string);
	}
}
?>
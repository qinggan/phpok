<?php
/**********************************************************************
	Filename	: {phpok}/admin/res_action_control.php
	Note		: 附件常见动作操作
	Version		: 4.0
	Web			: www.phpok.com
	Author		: qinggan <qinggan@188.com>
	Update		: 2013-04-06 00:52
**********************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_action_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("res");
	}

	function download_f()
	{
		$file = $this->get("file");
		$id = $this->get("id");
		if(!$id && !$file)
		{
			error("未指定要下载的附件","","error");
		}
		if($id)
		{
			$rs = $this->model('res')->get_one($id);
			$file = $rs["filename"];
			$title = $rs["title"].".".$rs["ext"];
		}
		else
		{
			$title = basename($file);
		}
		if(!$file)
		{
			error("附件未指定","","error");
		}
		if(substr($file,0,7) != "http://" && substr($file,0,8) != "https://")
		{
			$file = $this->dir_root.$file;
		}
		if(!file_exists($file))
		{
			error("附件不存在","","error");
		}
		$filesize = filesize($file);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $this->system_time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->system_time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($title));
		header("Content-Length: ".$filesize);
		header("Accept-Ranges: bytes");
		readfile($file);
		flush();
		ob_flush();
	}

	function view_f()
	{
		$file = $this->get("file");
		$id = $this->get("id");
		if(!$id && !$file)
		{
			error_open("未指定附件");
		}
		if($id)
		{
			$rs = $this->model('res')->get_one($id,true);
		}
		else
		{
			$rs = array();
			$rs["title"] = basename($file);
			$rs["filename"] = $file;
		}
		$this->assign("rs",$rs);
		$this->view("res_action_view");
	}

	function video_f()
	{
		$file = $this->get("file");
		$id = $this->get("id");
		if(!$id && !$file)
		{
			error_open("未指定视频源");
		}
		if($id)
		{
			$rs = $this->model('res')->get_one($id);
			$file = $rs["filename"];
		}
		$this->assign("file",$file);
		$this->view("res_action_video");
	}

	function preview_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			error_open("未指定附件");
		}
		$rs = $this->model('res')->get_one($id,true);
		$config = $this->model('res')->type_list();
		$type = "files";
		foreach($config AS $key=>$value)
		{
			$ext = array();
			if($value["ext"])
			{
				$ext = explode(",",$value["ext"]);
			}
			if(in_array($rs["ext"],$ext))
			{
				$type = $key;
			}
		}
		$this->assign("type",$type);
		$this->assign("rs",$rs);
		$this->view("res_action_preview");
	}
}
?>
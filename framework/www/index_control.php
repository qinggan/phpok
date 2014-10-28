<?php
/***********************************************************
	Filename: {phpok}/www/index_control.php
	Note	: 网站首页及APP的封面页
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 11:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("id");
		$this->model("project");
		$this->model("cate");
		$this->model("list");
		$this->lib("ext");
	}

	// 网站首页
	function index_f()
	{
		//下载附件
		$dfile = $this->get("dfile");
		if($dfile)
		{
			$this->download($dfile);
			exit;
		}
		$tmp = $this->model('data')->id('index',$this->site['id']);
		$tplfile = 'index';
		if($tmp)
		{
			$pid = $tmp['id'];
			$page_rs = $this->call->phpok('_project',array('pid'=>$pid,'project_ext'=>true));
			$this->phpok_seo($page_rs);
			$this->assign("page_rs",$page_rs);
			if($page_rs["tpl_index"] && $this->tpl->check_exists($page_rs["tpl_index"])) $tplfile = $page_rs["tpl_index"];
		}
		$this->view($tplfile);
	}

	function download($dfile)
	{
		if(!$dfile) error("未指定附件地址信息");
		$rs = $this->model("res")->get_one_filename($dfile,false);
		if(!$rs || !$rs["filename"] || !is_file($this->dir_root.$rs["filename"]))
		{
			error("附件不存在","","error");
		}
		$filesize = filesize($this->dir_root.$rs["filename"]);
		$title = $rs["title"] ? $rs['title'] : basename($rs['filename']);
		$title = str_replace(".".$rs["ext"],"",$title);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $this->system_time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->system_time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($title.".".$rs["ext"]));
		header("Content-Length: ".$filesize);
		header("Accept-Ranges: bytes");
		readfile($this->dir_root.$rs['filename']);
		flush();
		ob_flush();
	}
}
?>
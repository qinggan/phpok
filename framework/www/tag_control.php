<?php
/***********************************************************
	Filename: {phpok}/www/tag_control.php
	Note	: Tag标签读取
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-20 23:51
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		//查询
		$title = $this->get('title');
		if(!$title)
		{
			$this->view('tag');
			exit;
		}
		$rs = $this->model('tag')->get_one($title,'title',$this->site['id']);
		if(!$rs)
		{
			$this->view('tag');
			exit;
		}
		$this->model('tag')->add_hits($rs['id']);
		if($rs['url'])
		{
			header("Location:".$rs['url']);
			exit;
		}
		//读取列表
		$total = $this->model('tag')->get_total($rs['id']);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$idlist = $this->model('tag')->id_list($rs['id'],$offset,$psize);
		if(!$idlist)
		{
			$this->view('tag');
			exit;
		}
		$rslist = array();
		foreach($idlist AS $key=>$value)
		{
			$rslist[] = $this->model('data')->arc(array("id"=>$value['id']));
		}
		$this->assign("rslist",$rslist);
		$pageurl = $this->url('tag','','title='.rawurlencode($title));
		$this->assign("pageurl",$pageurl);
		$this->assign("total",$total);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("keywords",$keywords);
		$this->assign("rs",$rs);
		$this->view('tag');
	}
}
?>
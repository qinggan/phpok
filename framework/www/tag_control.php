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
		if(!$title){
			$this->view('tag');
			exit;
		}
		$rs = $this->model('tag')->get_one($title,'title',$this->site['id']);
		if(!$rs){
			$this->view('tag');
			exit;
		}
		$this->model('tag')->add_hits($rs['id']);
		if($rs['url']){
			$this->_location($rs['url']);
		}
		//读取列表
		$total = $this->model('tag')->get_total($rs['id']);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$idlist = $this->model('tag')->id_list($rs['id'],$offset,$psize);
		if(!$idlist){
			$this->view('tag');
			exit;
		}
		$rslist = false;
		foreach($idlist AS $key=>$value){
			if(substr($value['id'],0,1) == 'p'){
				$tmp = substr($value['id'],1);
				$rslist[] = $this->call->phpok('_project',array('pid'=>$tmp));
			}elseif(substr($value['id'],0,1) == 'c'){
				$tmp = substr($value['id'],1);
				$cate_rs = $this->model('cate')->get_one($tmp,$this->site['id']);
				if($cate_rs['parent_id']){
					$root_cate_id = $this->model('cate')->get_root_id($cate_rs['parent_id']);
				}else{
					$root_cate_id = $cate_rs['id'];
				}
				$project_info = $this->model('project')->get_one_condition("cate='".$root_cate_id."' AND status=1");
				$rslist[] = $this->call->phpok('_cate',array('pid'=>$project_info['id'],'cateid'=>$tmp));
			}else{
				$rslist[] = $this->call->phpok('_arc',array('title_id'=>$value['id']));
			}
		}
		$this->assign("rslist",$rslist);
		$pageurl = $this->url('tag','','title='.rawurlencode($title));
		$this->assign("pageurl",$pageurl);
		$this->assign("total",$total);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("keywords",$title);
		$this->assign("rs",$rs);
		$this->view('tag');
	}
}
?>
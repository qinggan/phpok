<?php
/***********************************************************
	Filename: {phpok}/inp_control.php
	Note	: 自定义表单数据获取接口
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-29 20:22
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class inp_control extends phpok_control
{
	var $form_list;
	var $field_list;
	var $format_list;
	function __construct()
	{
		parent::control();
		$this->form_list = $this->model("form")->form_list();
		$this->field_list = $this->model("form")->field_list();
		$this->format_list = $this->model("form")->format_list();
	}

	//取得表单数据
	public function index_f()
	{
		if(!$_SESSION['admin_id']){
			$this->json(P_Lang('仅限后台接入'));
		}
		$type = $this->get("type");
		$content = $this->get("content");
		if($type == "title" && $content)
		{
			$this->get_title_list($content);
		}
		elseif($type == "user" && $content)
		{
			$this->get_user_list($content);
		}
		json_exit("ok");
	}

	public function xml_f()
	{
		$file = $this->get('file',"system");
		if(!$file){
			$this->json(P_Lang('未指定XML文件'));
		}
		if(!file_exists($this->dir_root.'data/xml/'.$file.'.xml')){
			$this->json(P_Lang('XML文件不存在'));
		}
		$info = $this->lib('xml')->read($this->dir_root.'data/xml/'.$file.'.xml');
		$this->json($info,true);
	}

	function get_title_list($content)
	{
		$content = explode(",",$content);
		$list = array();
		foreach($content AS $key=>$value)
		{
			$value = intval($value);
			if($value) $list[] = $value;
		}
		$list = array_unique($list);
		$content = implode(",",$list);
		if(!$content) json_exit("ok");
		$condition = "l.id IN(".$content.")";
		$rslist = $this->model("list")->get_all($condition,0,0);
		if($rslist)
		{
			json_exit($rslist,true);
		}
		json_exit("ok");
	}

	function get_user_list($content)
	{
		$content = explode(",",$content);
		$list = array();
		foreach($content AS $key=>$value)
		{
			$value = intval($value);
			if($value) $list[] = $value;
		}
		$list = array_unique($list);
		$content = implode(",",$list);
		if(!$content) json_exit("ok");
		$condition = "u.id IN(".$content.")";
		$rslist = $this->model("user")->get_list($condition,0,999);
		if($rslist)
		{
			json_exit($rslist,true);
		}
		json_exit("ok");
	}

	//取得主题列表
	function title_f()
	{
		$psize = $this->config["psize"];
		if(!$psize) $psize = 30;
		$pageid = $this->config["pageid"] ? $this->config["pageid"] : "pageid";
		$pageid = $this->get($pageid,"int");
		if(!$pageid || $pageid<1) $pageid=1;
		$offset = ($pageid-1) * $psize;
		$input = $this->get("identifier");
		if(!$input)
		{
			error_open("未指定表单ID","error");
		}
		$multi = $this->get("multi","int");
		$pageurl = $this->url("inp","title")."&identifier=".rawurlencode($input);
		if($multi)
		{
			$pageurl .= "&multi=1";
		}
		$project_id = $this->get("project_id");
		if(!$project_id)
		{
			error_open("未指定项目ID","error");
		}
		$tmp = explode(",",$project_id);
		$lst = array();
		foreach($tmp AS $key=>$value)
		{
			$value = intval($value);
			if($value)
			{
				$lst[] = $value;
			}
		}
		$lst = array_unique($lst);
		$project_id = implode(",",$lst);
		if(!$project_id)
		{
			error_open("指定项目异常","error");
		}
		$pageurl .="&project_id=".rawurlencode($project_id);
		$condition = "l.project_id IN(".$project_id.") AND l.status='1'";
		$total = $this->model('list')->get_all_total($condition);
		if($total<1)
		{
			error("没有内容信息");
		}
		$rslist = $this->model('list')->get_all($condition,$offset,$psize);
		$this->assign("total",$total);
		$this->assign("rslist",$rslist);
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
		$this->assign("pagelist",$pagelist);
		$this->assign("multi",$multi);
		$this->assign("input",$input);
		$this->tpl->path_change("");
		$this->view($this->dir_phpok."view/inp_title.html","abs-file");
	}

	//function 

}
?>
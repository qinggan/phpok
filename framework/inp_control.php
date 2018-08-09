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
		if(!file_exists($this->dir_data.'xml/'.$file.'.xml')){
			$this->json(P_Lang('XML文件不存在'));
		}
		$info = $this->lib('xml')->read($this->dir_data.'xml/'.$file.'.xml');
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

	/**
	 * 取得主题列表
	 * @参数 pageid 页码
	 * @参数 identifier 表单标识，对应输出的变量是$input
	 * @参数 multi 是否多选，1为多选，其他为单选
	 * @参数 project_id 项目ID
	**/
	public function title_f()
	{
		$psize = $this->config["psize"];
		if(!$psize){
			$psize = 30;
		}
		$pageid = $this->config["pageid"] ? $this->config["pageid"] : "pageid";
		$pageid = $this->get($pageid,"int");
		if(!$pageid || $pageid<1){
			$pageid=1;
		}
		$offset = ($pageid-1) * $psize;
		$input = $this->get("identifier");
		if(!$input){
			$this->error("未指定表单ID");
		}
		$multi = $this->get("multi","int");
		$pageurl = $this->url("inp","title")."&identifier=".rawurlencode($input);
		if($multi){
			$pageurl .= "&multi=1";
		}
		$project_id = $this->get("project_id");
		if(!$project_id){
			$this->error("未指定项目ID");
		}
		$tmp = explode(",",$project_id);
		$lst = array();
		foreach($tmp AS $key=>$value){
			$value = intval($value);
			if($value){
				$lst[] = $value;
			}
		}
		$lst = array_unique($lst);
		$project_id = implode(",",$lst);
		if(!$project_id){
			$this->error("指定项目异常");
		}
		$pageurl .="&project_id=".rawurlencode($project_id);
		$formurl = $pageurl;
		$condition = "l.project_id IN(".$project_id.") AND l.status='1'";
		$keywords = $this->get('keywords');
		if($keywords){
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$condition .= " AND l.title LIKE '%".$keywords."%'";
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('list')->get_all_total($condition);
		if($total){
			$rslist = $this->model('list')->get_all($condition,$offset,$psize);
			$this->assign("total",$total);
			$this->assign("rslist",$rslist);
			$string = "home=".P_Lang('首页')."&prev=".P_Lang('上一页')."&next=".P_Lang('下一页')."&last=".P_Lang('尾页')."&half=5&add=(total)/(psize)&always=1";
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("multi",$multi);
		$this->assign("input",$input);
		$this->assign('formurl',$formurl);
		$this->tpl->path_change("");
		$this->view($this->dir_phpok."view/inp_title.html","abs-file");
	}
}
<?php
/**
 * 自定义表单数据获取接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年9月4日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class inp_control extends phpok_control
{
	var $form_list;
	var $field_list;
	var $format_list;
	public function __construct()
	{
		parent::control();
		$this->form_list = $this->model("form")->form_list();
		$this->field_list = $this->model("form")->field_list();
		$this->format_list = $this->model("form")->format_list();
	}

	//取得表单数据
	public function index_f()
	{
		$this->config('is_ajax',true);
		if(!$this->session->val('admin_id')){
			$this->error(P_Lang('仅限后台接入'));
		}
		$type = $this->get("type");
		$content = $this->get("content");
		if($type == "title" && $content){
			$this->get_title_list($content);
		}elseif($type == "user" && $content){
			$this->get_user_list($content);
		}
		$this->success();
	}

	public function xml_f()
	{
		$this->config('is_ajax',true);
		$file = $this->get('file',"system");
		if(!$file){
			$this->error(P_Lang('未指定XML文件'));
		}
		if(!file_exists($this->dir_data.'xml/'.$file.'.xml')){
			$this->error(P_Lang('XML文件不存在'));
		}
		$info = $this->lib('xml')->read($this->dir_data.'xml/'.$file.'.xml');
		$this->success($info);
	}

	private function get_title_list($content)
	{
		$content = explode(",",$content);
		$list = array();
		foreach($content as $key=>$value){
			$value = intval($value);
			if($value){
				$list[] = $value;
			}
		}
		$list = array_unique($list);
		$content = implode(",",$list);
		if(!$content){
			$this->error(P_Lang('未指定ID'));
		}
		$condition = "l.id IN(".$content.")";
		$rslist = $this->model("list")->get_all($condition,0,0);
		if($rslist){
			$this->success($rslist);
		}
		$this->error(P_Lang('没有主题信息'));
	}

	private function get_user_list($content)
	{
		$content = explode(",",$content);
		$list = array();
		foreach($content as $key=>$value){
			$value = intval($value);
			if($value){
				$list[] = $value;
			}
		}
		$list = array_unique($list);
		$content = implode(",",$list);
		if(!$content){
			$this->error(P_Lang('暂无内容'));
		}
		$condition = "u.id IN(".$content.")";
		$rslist = $this->model("user")->get_list($condition,0,999);
		if($rslist){
			$this->success($rslist);
		}
		$this->error(P_Lang('没有数据信息'));
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
		$pageurl = $this->url("inp","title","identifier=".rawurlencode($input));
		if($multi){
			$pageurl .= "&multi=1";
		}
		$project_id = $this->get("project_id");
		if(!$project_id){
			$this->error(P_Lang('未指定项目ID'));
		}
		if(!$this->session->val('admin_id') && !$this->session->val('user_id')){
			$this->error(P_Lang('游客不支持这里获取数据'));
		}
		$tmp = explode(",",$project_id);
		$lst = array();
		foreach($tmp as $key=>$value){
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
		$condition = "l.project_id IN(".$project_id.")";
		if(!$this->session->val('admin_id')){
			$condition .= " AND l.user_id='".$this->session->val('user_id')."'";
		}
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
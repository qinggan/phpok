<?php
/***********************************************************
	Filename: {phpok}/admin/open_control.php
	Note	: 虚弹窗口管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-02-07 17:23
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class open_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定附件存储ID'));
		}
		$this->assign('id',$id);
		$pageurl = $this->url('open','','id='.$id);
		if($_SESSION['user_id']){
			$condition = "user_id='".$_SESSION['user_id']."' ";
		}else{
			$condition = "session_id='".$this->session->sessid()."' ";
		}
		$multiple = $this->get('multiple','int');
		if($multiple){
			$pageurl .= "&multiple=1";
			$this->assign('multiple',$multiple);
		}
		$cate_id = $this->get('cate_id','int');
		if($cate_id){
			$cate_rs = $this->model('rescate')->get_one($cate_id);
			if($cate_rs && $cate_rs['filetypes']){
				$types = explode(',',$cate_rs['filetypes']);
				$condition .= " AND ext IN('".implode("','",$types)."') ";
				$this->assign('cate_id',$cate_id);
				$pageurl .= "&cate_id=".$cate_id;
			}
		}
		$selected = $this->get('selected','int');
		if($selected){
			$condition .= " AND id !='".$selected."' ";
			$pageurl .= '&selected='.$selected;
			$this->assign('selected',$selected);
		}
		$formurl = $pageurl;
		$keywords = $this->get("keywords");
		if($keywords){
			$keywords = str_replace(' ','%',$keywords);
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR filename LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$this->assign('keywords',$keywords);
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$total = $this->model('res')->get_count($condition);
		if(!$total){
			$this->error(P_Lang('您没有上传过附件'));
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign('rslist',$rslist);
		$this->assign('formurl',$formurl);
		$this->assign('pageurl',$pageurl);
		$this->assign('total',$total);
		$this->assign('pageid',$pageid);
		$this->assign('offset',$offset);
		$this->assign('psize',$psize);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=1';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->view($this->dir_phpok.'open/res_openselect.html','abs-file');
	}

	// 附件选择器
	public function input_f()
	{
		$id = $this->get("id");
		if(!$id) $id = "content";
		# 附件分类
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		# 取得附件类型
		$config = $this->model('res')->type_list("type");
		$this->assign("attr_list",$config);
		$pageurl = admin_url("open","input") ."&id=".$id;
		$type = $this->get("type");
		$type_s = $type;
		if($type == "image" || $type == "picture"){
			$type_s = "picture";
			$ext = strtolower($config["picture"]["ext"]);
			$tplfile = "open_image";
		}elseif($type == "video"){
			$ext = strtolower($config["video"]["ext"]);
			$tplfile = "open_input";
		}else{
			if($config[$type]){
				$ext = $config[$type]["ext"];
			}else{
				$ext = $this->get("ext");
			}
			$tplfile = "open_input";
		}
		$tpl = $this->get("tpl");
		if($tpl){
			$tplfile = $tpl;
			$pageurl .= "&tpl=".rawurlencode($tpl);
			$this->assign('tplfile',$tpl);
		}
		$is_multiple = $this->get("is_multiple","int");
		if($is_multiple){
			$this->assign("is_multiple",$is_multiple);
			$pageurl .= "&is_multiple=1";
		}
		$pageurl .= "&type=".$type;
		$this->assign("type",$type);
		$this->assign("type_s",$type_s);	
		$this->get_list($pageurl,$ext);
		$this->assign("id",$id);
		$this->view($this->dir_phpok.'view/'.$tplfile.'.html','abs-file');
	}

	private function get_list($pageurl,$ext="")
	{
		$formurl = $pageurl;
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 28;
		$offset = ($pageid - 1) * $psize;
		# 关键字
		$condition = "1=1";
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$start_date = $this->get("start_date");
		if($start_date){
			$condition .= " AND addtime>=".strtotime($start_date)." ";
			$pageurl .= "&start_date=".strtolower($start_date);
			$this->assign("start_date",$start_date);
		}
		$stop_date = $this->get("stop_date");
		if($stop_date){
			$condition .= " AND addtime<=".strtotime($stop_date)." ";
			$pageurl .= "&stop_date=".strtolower($stop_date);
			$this->assign("stop_date",$stop_date);
		}
		if($ext){
			$extlist = explode(",",$ext);
			$ext_string = implode("','",$extlist);
			$condition .= " AND ext IN('".$ext_string."') ";
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
	}

	//网址列表，这里读的是项目的网址列表
	function url_f()
	{
		$id = $this->get("id");
		if(!$id) $id = "content";
		$this->assign("id",$id);
		$pid = $this->get("pid");
		if($pid){
			$p_rs = $this->model('project')->get_one($pid);
			$type = $this->get("type");
			if(!$p_rs){
				error_open(P_Lang('项目不存在'));
			}
			if($type == "cate" && $p_rs["cate"]){
				$catelist = $this->model("cate")->get_all($p_rs['site_id'],1,$p_rs['cate']);
				$this->assign("rslist",$catelist);
				$this->assign("p_rs",$p_rs);
				$this->view("open_url_cate");
			}
			$pageid = $this->get($this->config["pageid"],"int");
			$psize = $this->config["psize"];
			if(!$psize) $psize = 20;
			if(!$pageid) $pageid = 1;
			$offset = ($pageid - 1) * $psize;
			$pageurl = $this->url("open","url","pid=".$pid."&type=list&id=".$id);
			$condition = "l.site_id='".$p_rs["site_id"]."' AND l.project_id='".$pid."' AND l.parent_id='0' ";
			$keywords = $this->get("keywords");
			if($keywords){
				$condition .= " AND l.title LIKE '%".$keywords."%' ";
				$pageurl .= "&keywords=".rawurlencode($keywords);
				$this->assign("keywords",$keywords);
			}
			$rslist = $this->model('list')->get_list($p_rs["module"],$condition,$offset,$psize,$p_rs["orderby"]);
			if($rslist){
				$sub_idlist = array_keys($rslist);
				$sub_idstring = implode(",",$sub_idlist);
				$con_sub = "l.site_id='".$p_rs["site_id"]."' AND l.project_id='".$pid."' AND l.parent_id IN(".$sub_idstring.") ";
				$sublist = $this->model('list')->get_list($p_rs["module"],$con_sub,0,0,$p_rs["orderby"]);
				if($sublist){
					foreach($sublist AS $key=>$value){
						$rslist[$value["parent_id"]]["sonlist"][$value["id"]] = $value;
					}
				}
			}
			//读子主题
			$total = $this->model('list')->get_total($p_rs["module"],$condition);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("p_rs",$p_rs);
			$this->assign("rslist",$rslist);
			$this->view("open_url_list");				
		}
		$condition = " p.status='1' ";
		$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"],$condition);
		$this->assign("rslist",$rslist);
		$this->assign("id",$id);
		$this->view("open_url");
	}

	//读取会员列表
	function user_f()
	{
		$id = $this->get("id");
		if(!$id) $id = "user";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"];
		if(!$psize) $psize = 30;
		$keywords = $this->get("keywords");
		$multi = $this->get("multi","int");
		$this->assign("multi",$multi);
		$page_url = $this->url("open","user","id=".$id."&multi=".$multi);
		$condition = "1=1";
		if($keywords){
			$this->assign("keywords",$keywords);
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$page_url.="&keywords=".rawurlencode($keywords);
		}
		$offset = ($pageid - 1) * $psize;
		$rslist = $this->model('user')->get_list($condition,$offset,$psize);
		$count = $this->model('user')->get_count($condition);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=2';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("total",$count);
		$this->assign("rslist",$rslist);
		$this->assign("id",$id);
		$this->assign("pagelist",$pagelist);
		$this->view("open_user_list");
	}

	function user2_f()
	{
		$id = $this->get("id");
		if(!$id) $id = "user";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"];
		if(!$psize) $psize = 30;
		$keywords = $this->get("keywords");
		$page_url = $this->url("open","user2","id=".$id);
		$condition = "1=1";
		if($keywords){
			$this->assign("keywords",$keywords);
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$page_url.="&keywords=".rawurlencode($keywords);
		}
		$offset = ($pageid - 1) * $psize;
		$rslist = $this->model('user')->get_list($condition,$offset,$psize);
		$count = $this->model('user')->get_count($condition);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("total",$count);
		$this->assign("rslist",$rslist);
		$this->assign("id",$id);
		$this->assign("pagelist",$pagelist);
		$this->view("open_user_list2");
	}
}
?>
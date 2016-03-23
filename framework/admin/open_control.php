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
		$keytype_list = array(
			'id'=>"ID",
			'title'=>P_Lang('名称'),
			'name'=>P_Lang('文件名'),
			'ext'=>P_Lang('附件扩展名'),
			'start_date'=>P_Lang('开始时间'),
			'stop_date'=>P_Lang('结束时间')
		);
		$this->assign('keytype_list',$keytype_list);
	}

	//附件资源选择器
	public function upload_f()
	{
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('未指定表单ID'));
		}
		$this->assign('id',$id);
		$multiple = $this->get('multiple','int');
		$this->assign('multiple',$multiple);
		$pageurl = $this->url('open','upload','id='.$id.'&multiple='.$multiple);
		$this->assign('formurl',$pageurl);
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign("catelist",$catelist);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$selected = $this->get('selected');
		if($selected){
			$pageurl .= '&selected='.rawurlencode($selected);
			$olist = explode(",",$selected);
			foreach($olist as $key=>$value){
				if(!$value || !intval($value)){
					unset($olist[$key]);
				}
			}
			$condition .= " AND id NOT IN(".implode(",",$olist).")";
		}
		$keywords = $this->get("keywords");
		$keytype = $this->get('keytype');
		if(!$keytype){
			$keytype = 'title';
		}
		if($keywords){
			if($keytype == 'title' || $keytype == 'name'){
				$condition .= " AND ".$keytype." LIKE '%".$keywords."%' ";
			}elseif($keytype == 'id'){
				$condition .= " AND id='".$keywords."' ";
			}elseif($keytype == 'ext'){
				$keywords = str_replace(",",' ',$keywords);
				$extlist = explode(" ",$keywords);
				$extlist = array_unique($extlist);
				$ext_string = implode("','",$extlist);
			}elseif($keytype == 'start_date'){
				$condition .= " AND addtime>=".strtotime($keywords)." ";
			}elseif($keytype == 'stop_date'){
				$condition .= " AND addtime<=".strtotime($keywords)." ";
			}
			$pageurl .= "&keywords=".rawurlencode($keywords).'&keytype='.$keytype;
			$this->assign("keywords",$keywords);
		}
		$this->assign("keytype",$keytype);
		
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		$this->assign("pageurl",$pageurl);
		$sendAsBinary = ini_get('upload_tmp_dir') ? false : true;
		$this->assign('sendAsBinary',$sendAsBinary);
		$this->view('open_upload');
	}
	

	// 附件选择器
	function input_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "content";
		}
		$this->assign('id',$id);
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign("catelist",$catelist);
		$pageurl = $this->url('open','input','id='.$id);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$keywords = $this->get("keywords");
		$keytype = $this->get('keytype');
		if(!$keytype){
			$keytype = 'title';
		}
		if($keywords){
			if($keytype == 'title' || $keytype == 'name'){
				$condition .= " AND ".$keytype." LIKE '%".$keywords."%' ";
			}elseif($keytype == 'id'){
				$condition .= " AND id='".$keywords."' ";
			}elseif($keytype == 'ext'){
				$keywords = str_replace(",",' ',$keywords);
				$extlist = explode(" ",$keywords);
				$extlist = array_unique($extlist);
				$ext_string = implode("','",$extlist);
			}elseif($keytype == 'start_date'){
				$condition .= " AND addtime>=".strtotime($keywords)." ";
			}elseif($keytype == 'stop_date'){
				$condition .= " AND addtime<=".strtotime($keywords)." ";
			}
			$pageurl .= "&keywords=".rawurlencode($keywords).'&keytype='.$keytype;
			$this->assign("keywords",$keywords);
		}
		$this->assign("keytype",$keytype);
		
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		$this->assign("pageurl",$pageurl);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$this->view('open_input');
	}

	function get_list($pageurl,$ext="")
	{
		$formurl = $pageurl;
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 28;
		$offset = ($pageid - 1) * $psize;
		# 关键字
		$condition = "1=1";
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$start_date = $this->get("start_date");
		if($start_date)
		{
			$condition .= " AND addtime>=".strtotime($start_date)." ";
			$pageurl .= "&start_date=".strtolower($start_date);
			$this->assign("start_date",$start_date);
		}
		$stop_date = $this->get("stop_date",'html');
		if($stop_date)
		{
			$condition .= " AND addtime<=".strtotime($stop_date)." ";
			$pageurl .= "&stop_date=".strtolower($stop_date);
			$this->assign("stop_date",$stop_date);
		}
		if($ext)
		{
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
				$catelist = $this->model('cate')->get_all($p_rs["site_id"],1,$p_rs["cate"]);
				$this->assign("rslist",$catelist);
				$this->assign("p_rs",$p_rs);
				$this->view("open_url_cate");
			}else{
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
		}else{
			$condition = " p.status='1' ";
			$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"],$condition);
			$this->assign("rslist",$rslist);
		}
		$this->assign("id",$id);
		$this->view("open_url");
	}

	//读取会员列表
	public function user_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "user";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config['psize'] : 30;
		$keywords = $this->get("keywords");
		$multi = $this->get("multi","int");
		$page_url = $this->url("open","user","id=".$id);
		if($multi){
			$page_url .= "&multi=1";
			$this->assign("multi",$multi);
		}
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
		$pagelist = phpok_page($page_url,$count,$pageid,$psize,$string);
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
		$psize = 39;
		$keywords = $this->get("keywords");
		$page_url = $this->url("open","user2","id=".$id);
		$condition = "1=1";
		if($keywords)
		{
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
		
		$this->view("open_user_list2");
	}
}
?>
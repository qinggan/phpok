<?php
/***********************************************************
	Filename: {phpok}/admin/uedit_control.php
	Note	: 百度编辑器扩展之服务器插件
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年6月7日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class uedit_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		if(!$_SESSION['user_id']) error_open('非会员没有权限使用此功能');
	}

	//编辑器图片管理器
	function picture_f()
	{
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 32;
		$offset = ($pageid - 1) * $psize;
		$condition = "res.ext IN ('gif','jpg','png','jpeg') ";
		$gd_rs = $this->model('gd')->get_editor_default();
		if(!$gd_rs)
		{
			error_open("未设置编辑器默认插入图片方案，请联系管理员设置！","error",'<input type="button" onclick="$.dialog.close();" value="关闭" />');
		}
		$condition .= " AND e.gd_id='".$gd_rs["id"]."' ";
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND res.cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (res.title LIKE '%".$keywords."%' OR res.name LIKE '%".$keywords."%' OR res.id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->edit_pic_total($condition);
		$this->assign("pageurl",$pageurl);
		if($total>$psize)
		{
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=4&add=(total)/(psize)&always=1");
			$this->assign("pagelist",$pagelist);
		}
		$this->all("picture");
		$this->view($this->dir_phpok."/view/edit_picture.html",'abs-file');
	}

	//编辑器文件管理器
	function file_f()
	{
		$pageurl = $this->url("uedit","file");
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 20;
		$offset = ($pageid - 1) * $psize;
		$condition = " 1=1 ";
		
		$nopic = $this->get("nopic","checkbox");
		if($nopic)
		{
			$condition .= " AND ext NOT IN('jpg','gif','png','jpeg') ";
			$pageurl .= "&nopic=1";
			$this->assign("nopic",1);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$type = $this->get("type");
		if($type)
		{
			$type_rs = phpok_res_type($type);
			if(!$type_rs)
			{
				$type_rs["swfupload"] = "*.*";
				$type_rs["name"] = "文件";
			}
			if($type_rs["ext"])
			{
				$tmp_ext_list = explode(",",$type_rs["ext"]);
				$condition .= " AND ext IN('".implode("','",$tmp_ext_list)."')";
			}
			$pageurl .= "&type=".$type;
			$this->assign("type",$type);
			$this->assign("type_rs",$type_rs);
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("pageurl",$pageurl);
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=0&add=(total)/(psize)，页码：(num)/(total_page)&always=1");
		$this->assign("pagelist",$pagelist);
		$this->all($type);
		$this->view("edit_file");
	}

	//编辑器影音管理器
	function video_f()
	{
		$pageurl = $this->url("uedit","video");
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 10;
		$offset = ($pageid - 1) * $psize;
		$video_type = phpok_res_type("video");
		if(!$video_type)
		{
			$video_type["swfupload"] = "*.swf;*.flv;*.mp3;*.mp4";
			$video_type["name"] = "视频";
			$video_type["ext"] = "swf,flv,mp3,mp4";
		}
		$this->assign("file_type",$video_type["swfupload"]);
		$file_type_list = explode(",",$video_type["ext"]);
		$file_type_condition = implode("','",$file_type_list);
		$condition = " ext IN('".$file_type_condition."') ";
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("pageurl",$pageurl);
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=0&add=(total)/(psize)，页码：(num)/(total_page)&always=1");
		$this->assign("pagelist",$pagelist);
		$this->all("video");
		$this->view("edit_video");
	}

	//读取所有主题
	function info_f()
	{
		$pageurl = $this->url("uedit","info");
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 28;
		$offset = ($pageid - 1) * $psize;
		//读取所有项目
		$projectlist = $this->model('project')->get_all_project($_SESSION['admin_site_id']);
		$this->assign("projectlist",$projectlist);
		//读取全部列表
		$condition = "l.site_id=".$_SESSION['admin_site_id'];
		$project_id = $this->get('project_id','int');
		if($project_id)
		{
			$p_rs = $this->model('project')->get_one($project_id);
			if($p_rs)
			{
				$condition .= " AND l.project_id=".$project_id;
				$pageurl .= "&project_id=".$project_id;
				$cate_id = $this->get('cate_id','int');
				if($cate_id && $p_rs['cate'])
				{
					$cate_rs = $this->model('cate')->get_one($cate_id);
					$catelist = array($cate_rs);
					$this->model('cate')->get_sublist($catelist,$cate_id);
					$cate_id_list = array();
					foreach($catelist AS $key=>$value)
					{
						$cate_id_list[] = $value["id"];
					}
					$cate_idstring = implode(",",$cate_id_list);
					$condition .= " AND l.cate_id IN(".$cate_idstring.")";
					$pageurl .= "&cate_id=".$cate_id;
					$this->assign("cate_id",$cate_id);
				}
				$this->assign("project_id",$project_id);
			}
		}
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (l.title LIKE '%".$keywords."%' OR l.tag LIKE '%".$keywords."%' OR l.seo_keywords LIKE '%".$keywords."%' OR l.seo_desc LIKE '%".$keywords."%' OR l.seo_title LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$total = $this->model('list')->get_all_total($condition);
		if($total>0)
		{
			$rslist = $this->model('list')->get_all($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			$this->assign("total",$total);
			if($total>$psize)
			{
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=4&add=(total)/(psize)&always=1");
				$this->assign("pagelist",$pagelist);
			}
		}
		$this->view("edit_title");
	}

	//取得项目下的分类信息
	function info_cate_f()
	{
		$id = $this->get('project_id','int');
		if(!$id)
		{
			$this->json('未指定项目信息');
		}
		$rs = $this->model('project')->get_one($id);
		if($rs['cate'])
		{
			$catelist = $this->model('cate')->get_all($rs["site_id"],0,$rs["cate"]);
			if($catelist)
			{
				$catelist = $this->model('cate')->cate_option_list($catelist);
				$this->json($catelist,true);
			}
		}
		$this->json('无分类');
	}

	//任意弹出窗口都要执行的信息，主题弹窗除外
	function all($type="")
	{
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$config = $this->model('res')->type_list();
		$this->assign("typelist",$config);
		$file_type = "*.*";
		$file_type_desc = "文件";
		if($type && $config[$type])
		{
			$file_type = $config[$type]["type"];
			$file_type_desc = $config[$type]["name"];
		}
		$this->assign("file_type",$file_type);
		$this->assign("file_type_desc",$file_type_desc);
		return true;
	}

	function attachment_f()
	{
		$this->view('uedit_attachment');
	}

}
?>
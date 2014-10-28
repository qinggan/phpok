<?php
/***********************************************************
	Filename: {phpok}/admin/edit_control.php
	Note	: 编辑器专用扩展管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年6月7日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class edit_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		exit("OK");
	}

	function picture_f()
	{
		$input = $this->get("input");
		if(!$input) $input = "content";
		$pageurl = $this->url("edit","picture") ."&input=".$input;
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
		$this->assign("input",$input);
		$this->all("picture");
		$this->view("edit_picture");
	}

	function file_f()
	{
		$input = $this->get("input");
		if(!$input) $input = "content";
		$pageurl = $this->url("edit","file") ."&input=".$input;
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 32;
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
		}
		if($type_rs["swfupload"]) $this->assign("file_type",$type_rs["swfupload"]);
		$this->assign("type_rs",$type_rs);
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("pageurl",$pageurl);
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=0&add=(total)/(psize)，页码：(num)/(total_page)&always=1");
		$this->assign("pagelist",$pagelist);
		$this->assign("input",$input);
		$this->all();
		$this->view("edit_file");
	}

	//读取视频列表
	function video_f()
	{
		$input = $this->get("input");
		if(!$input) $input = "content";
		$pageurl = $this->url("edit","video") ."&input=".$input;
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 32;
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
		$this->assign("input",$input);
		$this->all("video");
		$this->view("edit_video");
	}

	//任意弹出窗口都要执行的信息
	function all($type="")
	{
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$config = $this->model('res')->type_list();
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
}
?>
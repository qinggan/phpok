<?php
/***********************************************************
	Filename: {phpok}/admin/call_control.php
	Note	: 数据调用中心
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-18 02:22
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_control extends phpok_control
{
	var $psize = 20;
	var $phpok_type_list;//可调用类型
	var $popedom;
	function __construct()
	{
		parent::control();
		$list = array(
			"arclist"=>"文章列表"
			,"arc"=>"文章内容"
			,"cate"=>"分类信息"
			,"catelist"=>"分类树"
			,"project"=>"项目信息"
			,"sublist"=>"子项目信息"
			,"parent"=>"上级项目"
			,"fields"=>"字段及表单"
			//,"user"=>"会员信息"
			//,"userlist"=>"会员列表"
		);
		$this->phpok_type_list = $list;
		$this->assign("phpok_type_list",$list);
		$this->popedom = appfile_popedom("call");
		$this->assign("popedom",$this->popedom);
	}

	function phpok_autoload()
	{
		$site_id = $_SESSION["admin_site_id"];
		$this->model('call')->site_id($site_id);
		$psize = $this->config["psize"] ? $this->config["psize"] : 20;
		$this->model('call')->psize($psize);
		$this->psize = $psize;
	}

	function index_f()
	{
		if(!$this->popedom["list"]) error("你没有查看权限");
		$this->phpok_autoload();
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$keywords = $this->get("keywords");
		$pageurl = $this->url("call");
		$condition = "";
		if($keywords)
		{
			$this->assign("keywords",$keywords);
			$pageurl.="&keywords=".rawurlencode($keywords)."&";
			$condition = " (title LIKE '%".$keywords."%' OR note LIKE '%".$keywords."%' OR identifier LIKE '%".$keywords."%') ";
		}
		$rslist = $this->model('call')->get_list($condition,$pageid);
		$this->assign("rslist",$rslist);
		$total = $this->model('call')->get_count($condition);
		$this->assign("total",$total);
		$pagelist = phpok_page($pageurl,$total,$pageid,$this->psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
		$this->assign("pagelist",$pagelist);
		$attrlist = $this->model('list')->attr_list();
		$this->assign("attrlist",$attrlist);
		$this->view("phpok_index");
	}

	function set_f()
	{
		$this->phpok_autoload();
		$id = $this->get("id");
		if($id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限");
			$rs = $this->model('call')->get_one($id);
			if($rs['ext'])
			{
				$ext = unserialize($rs['ext']);
				unset($rs['ext']);
				if($ext) $rs = array_merge($ext,$rs);
			}
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限");
		}
		$site_id = $_SESSION["admin_site_id"];
		$rslist = $this->model('project')->get_all_project($site_id);
		$this->assign("rslist",$rslist);
		$this->model("list");
		$attrlist = $this->model('list')->attr_list();
		$this->assign("attrlist",$attrlist);
		$this->view("phpok_set");
	}

	//取得分类列表
	function cate_list_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定项目ID");
		}
		$val = $this->get("val");
		if($val)
		{
			$val = explode(",",$val);
			$this->assign("val",$val);
		}
		$rs = $this->model('project')->get_one($id);
		$this->assign("rs",$rs);
		if(!$rs["cate"])
		{
			json_exit("无分类");
		}
		$cate_rs = $this->model('cate')->cate_info($rs["cate"],false);
		$this->assign("cate_rs",$cate_rs);
		$catelist = $this->model('cate')->get_all($rs["site_id"],0,$rs["cate"]);
		$catelist = $this->model('cate')->cate_option_list($catelist);
		$this->assign("catelist",$catelist);
		$content = $this->fetch("phpok_ajax_cate");
		json_exit($content,true);
	}
	
	function type_list_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			$val = $this->get("val");
			$this->assign("val",$val);
			$content = $this->fetch("phpok_ajax_list");
			json_exit($content,true);
		}
		$this->assign("id",$id);
		$val = $this->get("val");
		$this->assign("val",$val);
		$rs = $this->model('project')->get_one($id);
		$this->assign("rs",$rs);
		//判断是否有子项，有的话，读取子项信息
		$son_rs = $this->model('project')->get_son($id);
		if($son_rs)
		{
			$this->assign("son_rs",$son_rs);
		}
		if($rs["module"])
		{
			if($rs["cate"])
			{
				$cate_rs = $this->model('cate')->get_one($rs["cate"]);
				$catelist = array();
				$this->model('cate')->get_sublist($list,$rs["cate"]);
				$this->assign("catelist",$catelist);
			}
		}
		$content = $this->fetch("phpok_ajax_list");
		json_exit($content,true);
	}

	function fields_f()
	{
		$pid = $this->get("pid","int");
		if(!$pid)
		{
			$this->json("未指定ID");
		}
		$p_rs = $this->model('project')->get_one($pid,false);
		if(!$p_rs['module']) $this->json('未绑定模块');
		$rslist = $this->model('module')->fields_all($p_rs['module']);
		$this->assign('rslist',$rslist);
		$info = $this->fetch("phpok_ajax_fields");
		$this->json($info,true);
	}

	function arclist_f()
	{
		$pid = $this->get("pid","int");
		if(!$pid) $this->json("未指定ID");
		$p_rs = $this->model('project')->get_one($pid,false);
		if(!$p_rs['module']) $this->json('未绑定模块');
		$rslist = $this->model('module')->fields_all($p_rs['module']);
		$this->assign('rslist',$rslist);
		$info = $this->fetch("phpok_ajax_fields");
		$order = $this->fetch("phpok_ajax_orderby");
		
		$this->json(array('need'=>$info,'orderby'=>$order),true);
	}

	function check_identifier($identifier)
	{
		if(!$identifier)
		{
			return "未指定标识串";
		}
		//$this->phpok_autoload();
		$identifier = strtolower($identifier);
		//字符串是否符合条件
		if(!preg_match("/^[a-z][a-z0-9\_\-]+$/u",$identifier))
		{
			return "字段标识不符合系统要求，限小写字母、数字、中划线及下划线且必须是小写字母开头";
		}
		$rs = $this->model('call')->chk_identifier($identifier);
		if($rs)
		{
			return "字符串已被使用";
		}
		return "ok";		
	}

	function check_f()
	{
		$identifier = $this->get("identifier");
		$check = $this->check_identifier($identifier);
		$this->json($check == 'ok' ? true : $check);
	}

	function save_f()
	{
		$id = $this->get("id","int");
		$this->phpok_autoload();
		$array = array();
		$error_url = $this->url("call","set");
		if(!$id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限");
			$identifier = $this->get("identifier");
			$chk = $this->check_identifier($identifier);
			if($chk != "ok")
			{
				error($chk,$error_url,"error");
			}
			$array["identifier"] = $identifier;
			$array["site_id"] = $_SESSION["admin_site_id"];
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限");
			$error_url .= "&id=".$id;
		}
		$title = $this->get("title");
		if(!$title)
		{
			error("备注不能为空",$error_url,"error");
		}
		$array["title"] = $title;
		$array["pid"] = $this->get("pid","int");
		$array["type_id"] = $this->get("type_id");
		$array["status"] = $this->get("status","int");
		$array["cateid"] = $this->get("cateid",'int');
		//更新扩展数据
		$ext = array();
		$ext['psize'] = $this->get("psize",'int');
		$ext['offset'] = $this->get("offset",'int');
		$ext['is_list'] = $this->get("is_list",'int');
		$ext['in_text'] = $this->get("in_text",'int');
		$ext['attr'] = $this->get('attr');
		$ext['fields_need'] = $this->get('fields_need');
		$ext['tag'] = $this->get('tag');
		$ext['keywords'] = $this->get('keywords');
		$ext['orderby'] = $this->get('orderby');
		$ext['cate'] = $this->get('cate');
		$ext['cate_ext'] = $this->get('cate_ext','int');
		$ext['catelist_ext'] = $this->get('catelist_ext','int');
		$ext['project_ext'] = $this->get('project_ext','int');
		$ext['sublist_ext'] = $this->get('sublist_ext','int');
		$ext['parent_ext'] = $this->get('parent_ext','int');
		$ext['fields_format'] = $this->get('fields_format','int');
		$ext['user_ext'] = $this->get('user_ext','int');
		$ext['user'] = $this->get('user');
		$ext['userlist_ext'] = $this->get('userlist_ext','int');
		$ext['in_sub'] = $this->get('in_sub','int');
		$ext['in_project'] = $this->get('in_project','int');
		$ext['in_cate'] = $this->get('in_cate','int');//主题是包含分类信息
		$ext['title_id'] = $this->get('title_id');
		$array['ext'] = serialize($ext);
		$id = $this->model('call')->save($array,$id);
		error("数据调用中心配置成功",$this->url("call"),"ok");
	}

	function delete_f()
	{
		if(!$this->popedom["delete"]) json_exit("你没有删除权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定ID");
		}
		$this->model('call')->del($id);
		json_exit("删除成功",true);
	}

	//取得模块的扩展字段
	function mfields_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			json_exit("未指定项目ID");
		}
		$rs = $this->model('project')->get_one($id);
		if(!$rs || !$rs["module"])
		{
			json_exit("无数据或未设置模块");
		}
		$mid = $rs["module"];
		$this->model("module");
		$rslist = $this->model('module')->fields_all($mid);
		if(!$rslist)
		{
			json_exit("没有自定义字段");
		}
		$list = array();
		foreach($rslist AS $key=>$value)
		{
			if($value["field_type"] != "longtext" && $value["field_type"] != "longblob" && $value["field_type"] != "text")
			{
				$list[] = array("id"=>$value["id"],"identifier"=>$value["identifier"],"title"=>$value["title"]);
			}
		}
		json_exit($list,true);
	}

}
?>
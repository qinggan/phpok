<?php
/***********************************************************
	Filename: admin/list_control.php
	Note	: 内容控制器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-31 19:45
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->lib('form')->cssjs();
	}

	function popedom_auto($pid)
	{
		$this->popedom = appfile_popedom("list",$pid);
		$this->assign("popedom",$this->popedom);
		return $this->popedom;
	}

	function index_f()
	{
		$site_id = $_SESSION["admin_site_id"];
		$rslist = $this->model('project')->get_all($site_id,0,"p.status=1 AND p.hidden=0");
		if(!$rslist) $rslist = array();
		//读取全部模型
		if(!$_SESSION["admin_rs"]["if_system"])
		{
			$this->model("popedom");
			$this->model("sysmenu");
			if(!$_SESSION["admin_popedom"])
			{
				error("该管理员未配置权限，请检查");
			}
			$condition = "parent_id>0 AND appfile='list' AND func=''";
			$p_rs = $this->model('sysmenu')->get_one_condition($condition);
			if(!$p_rs)
			{
				error("数据获取异常，请检查");
			}
			$gid = $p_rs["id"];
			//取得全部项目权限信息
			$popedom_list = $this->model('popedom')->get_all("gid='".$gid."' AND pid>0",false,false);
			if(!$popedom_list)
			{
				error("未配置站点内容权限，请检查");
			}
			$popedom = array();
			foreach($popedom_list AS $key=>$value)
			{
				if(in_array($value["id"],$_SESSION["admin_popedom"]))
				{
					$popedom[$value["pid"]][$value["identifier"]] = true;
				}
			}
			foreach($rslist AS $key=>$value)
			{
				if(!$popedom[$value["id"]] || !$popedom[$value["id"]]["list"])
				{
					unset($rslist[$key]);
					continue;
				}
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("list_index");
	}

	function action_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			error("未指定ID信息",$this->url("list"),"error");
		}
		$this->popedom_auto($id);
		if(!$this->popedom["list"]) error("你没有查看权限");
		$rs = $this->model('project')->get_one($id);
		if(!$rs)
		{
			error("项目信息不存在",$this->url("list"),"error");
		}
		//如果子类，并分析这些子类是否有权限查看
		$son_list = $this->model('project')->get_all($rs["site_id"],$id,"p.status=1");
		if($son_list)
		{
			foreach($son_list AS $key=>$value)
			{
				$popedom = appfile_popedom("list",$value["id"]);
				if(!$popedom["list"])
				{
					unset($son_list[$key]);
				}
			}
		}
		if($son_list)
		{
			$this->assign("project_list",$son_list);
		}
		$this->assign("rs",$rs);
		$this->assign("id",$id);
		$this->assign("pid",$id);
		//设置面包屑
		$plist = array($rs);
		if($rs["parent_id"])
		{
			$this->model('project')->get_parentlist($plist,$rs["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		//读取当前分类下的子分类
		$cateid = $this->get("cateid");
		if(!$cateid) $cateid = $rs["cate"];
		if($cateid && $rs["cate"])
		{
			$show_parent_catelist = $cateid != $rs["cate"] ? $cateid : false;
			$catelist = $this->model('cate')->get_sonlist($cateid);
			if(!$catelist)
			{
				//读取这个分类信息
				$cate_rs = $this->model('cate')->get_one($cateid);
				if($cate_rs["parent_id"])
				{
					$catelist = $this->model('cate')->get_sonlist($cate_rs["parent_id"]);
				}
			}
			$this->assign("catelist",$catelist);
			$opt_catelist = $this->model('cate')->get_all($rs["site_id"],1,$rs["cate"]);
			$opt_catelist = $this->model('cate')->cate_option_list($opt_catelist);
			$this->assign("opt_catelist",$opt_catelist);
			if($show_parent_catelist)
			{
				$parent_cate_rs = $this->model('cate')->get_one($show_parent_catelist);
				$this->assign('parent_cate_rs',$parent_cate_rs);
			}
			$this->assign("show_parent_catelist",$show_parent_catelist);
		}
		
		//设置内容列表
		if($rs["module"])
		{
			$this->content_list($rs);
			$this->view("list_content");
		}
		else
		{
			$show_edit = true;
			$this->view("list_set");
		}
	}


	function set_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			error("操作有错误！",$this->url("list"),"error");
		}
		$this->popedom_auto($id);
		//echo "<pre>";
		//print_r($this->popedom);
		if(!$this->popedom["set"]) error("你没有配置项目权限");
		$rs = $this->model('project')->get_one($id);
		if(!$rs)
		{
			error("项目信息不存在",$this->url("list"),"error");
		}
		$this->assign("rs",$rs);
		$this->assign("id",$id);
		$this->assign("pid",$id);
		$plist = array($rs);
		if($rs["parent_id"])
		{
			$this->model('project')->get_parentlist($plist,$rs["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		$this->view("list_set");
	}
	
	function save_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			error("未指定项目ID",$this->url('list'),"error");
		}
		$this->popedom_auto($id);
		if(!$this->popedom["set"]) error("你没有配置项目权限");
		$title = $this->get("title");
		if(!$title)
		{
			error("名称不能为空",$this->url("list","action","id=".$id),"error");
		}
		$array = array("title"=>$title);
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$this->model('project')->save($array,$id);
		ext_save("project-".$id);
		$this->model('temp')->clean("project-".$id,$_SESSION["admin_id"]);
		$ok_url = $this->url("list","action","id=".$id);
		error("项目信息编辑成功",$ok_url,"ok");
	}

	function check_identifier($sign,$id=0,$site_id=0)
	{
		if(!$sign)
		{
			return "标识串不能为空";
		}
		$sign = strtolower($sign);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-\.]+/",$sign))
		{
			return "标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头！";
		}
		if(!$site_id) $site_id = $_SESSION["admin_site_id"];
		$check = $this->model('id')->get_id($sign,$site_id,true);
		if($check)
		{
			if($id)
			{
				if($check["id"] != $id)
				{
					return "标识符已被使用";
				}
			}
			else
			{
				return "标识符已被使用";
			}
		}
		return "ok";
	}

	// 验证字串是否符合要求
	function identifier_f()
	{
		$id = $this->get("id");
		$sign = $this->get("sign");
		$check_rs = $this->check_identifier($sign,$id);
		if($check_rs != "ok")
		{
			json_exit($check_rs);
		}
		else
		{
			json_exit("验证通过",true);
		}
	}

	//列表管理
	function content_list($project_rs)
	{
		if(!$project_rs) return false;
		$pid = $project_rs["id"];
		$mid = $project_rs["module"];
		$site_id = $project_rs["site_id"];
		$orderby = $project_rs["orderby"];
		if(!$pid || !$mid || !$site_id) return false;
		//内容布局维护
		$layout = $m_list = array();
		$m_rs = $this->model('module')->get_one($mid);
		$m_list = $this->model('module')->fields_all($mid,"identifier");
		if($m_rs["layout"]) $layout = explode(",",$m_rs["layout"]);
		$this->assign("m_rs",$m_rs);
		//布局
		$layout_list = array();
		foreach($layout AS $key=>$value)
		{
			if($value == "hits")
			{
				$layout_list[$value] = "查看次数";
			}
			elseif($value == "dateline")
			{
				$layout_list[$value] = "发布时间";
			}
			else
			{
				$layout_list[$value] = $m_list[$value]["title"];
			}
		}
		$this->assign("ext_list",$m_list);
		$this->assign("layout",$layout_list);
		unset($layout_list);
		$psize = $this->config["psize"] ? $this->config["psize"] : "30";
		if(!$this->config["pageid"]) $this->config["pageid"] = "pageid";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$offset = ($pageid-1) * $psize;
		$condition = "l.site_id='".$site_id."' AND l.project_id='".$pid."' AND l.parent_id='0' ";
		$pageurl = $this->url("list","action","id=".$pid);
		$cateid = $this->get("cateid","int");
		$cate_popedom = $_SESSION['admin_rs']['if_system'] ? 'all' : $this->model('admin')->cate_popedom($_SESSION['admin_id']);
		$this->assign('cate_popedom',$cate_popedom);
		if($cateid)
		{
			$cate_rs = $this->model('cate')->get_one($cateid);
			$catelist = array($cate_rs);
			$this->model('cate')->get_sublist($catelist,$cateid);
			$cate_id_list = array();
			
			foreach($catelist AS $key=>$value)
			{
				if($cate_popedom != 'all' && $cate_popedom[$value['id']]['read'])
				{
					$cate_id_list[] = $value["id"];
				}
				else
				{
					if($cate_popedom == 'all')
					{
			  			$cate_id_list[] = $value["id"];
					}
				}
			}
			$cate_idstring = implode(",",$cate_id_list);
			$condition .= " AND l.cate_id IN(".$cate_idstring.")";
			$pageurl .= "&cateid=".$cateid;
			$this->assign("cateid",$cateid);
		}
		else
		{
			if(!$_SESSION['admin_rs']['if_system'] && $project_rs['cate'])
			{
				$cate_rs = $this->model('cate')->get_one($project_rs['cate']);
				$catelist = array($cate_rs);
				$this->model('cate')->get_sublist($catelist,$project_rs['cate']);
				$cate_id_list = array();
				foreach($catelist AS $key=>$value)
				{
					if($cate_popedom && $cate_popedom[$value['id']]['read'])
					{
						$cate_id_list[] = $value["id"];
					}
				}
				$cate_idstring = implode(",",$cate_id_list);
				$condition .= " AND l.cate_id IN(".$cate_idstring.")";
			}
		}
		$keywords = $this->get("keywords");
		if($keywords) $keywords = trim($keywords);
		if($keywords)
		{
			$condition .= " AND (l.title LIKE '%".$keywords."%' OR l.tag LIKE '%".$keywords."%' OR l.seo_keywords LIKE '%".$keywords."%' OR l.seo_desc LIKE '%".$keywords."%' OR l.seo_title LIKE '%".$keywords."%'";
			//搜索扩展字段
			if($m_list)
			{
				foreach($m_list AS $key=>$value)
				{
					if($value['field_type'] != 'longtext' && $value['field_type'] != 'longblob')
					{
						$condition .= " OR ext.".$key." LIKE '%".$keywords."%' ";
					}
				}
			}
			$condition .= ") ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$attr = $this->get("attr");
		if($attr)
		{
			if(is_array($attr) && count($attr)>0)
			{
				$attr_list = array();
				foreach($attr AS $key=>$value)
				{
					$attr_list[] = "l.attr LIKE '%".$attr."%'";
					$pageurl .= "&attr[]=".$value;
				}
				$attr_string = implode(" OR ",$attr_list);
				$condition .= " AND (".$attr_string.") ";
				$this->assign("attr",$attr);
			}
			else
			{
				$condition .= " AND l.attr LIKE '%".$attr."%'";
				$pageurl .= "&attr=".$attr;
				$this->assign("attr",array($attr));
			}
		}
		//取得列表信息
		$total = $this->model('list')->get_total($mid,$condition);
		if($total > 0)
		{
			$rslist = $this->model('list')->get_list($mid,$condition,$offset,$psize,$orderby);
			$sub_idlist = $rslist ? array_keys($rslist) : array();
			$sub_idstring = implode(",",$sub_idlist);
			$condition = "l.site_id='".$site_id."' AND l.project_id='".$pid."' AND l.parent_id IN(".$sub_idstring.") ";
			$sublist = $this->model('list')->get_list($mid,$condition,0,0,$orderby);
			if($sublist)
			{
				foreach($sublist AS $key=>$value)
				{
					$rslist[$value["parent_id"]]["sonlist"][$value["id"]] = $value;
				}
			}
			unset($sublist,$sub_idstring,$sub_idlist);
			$_SESSION["last_page_url"] = $pageurl."&".$this->config["pageid"]."=".$pageid;
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
			$this->assign("pagelist",$pagelist);
			$this->assign("rslist",$rslist);
		}
		else
		{
			$_SESSION["last_page_url"] = $pageurl;
		}
		$attrlist = $this->model('list')->attr_list();
		$this->assign("attrlist",$attrlist);
		return true;
	}

	//添加或编辑内容
	function edit_f()
	{
		$id = $this->get("id","int");
		$pid = $this->get("pid","int");
		if(!$id && !$pid)
		{
			error("操作异常",$this->url("list"),"error");
		}
		if($id)
		{
			$rs = $this->model('list')->get_one($id,false);
			$pid = $rs["project_id"];
		}
		else
		{
			$cateid = $this->get("cateid","int");
			$rs = array();
			if($cateid)
			{
				$rs["cate_id"] = $cateid;
			}
		}
		if(!$pid)
		{
			error("操作异常",$this->url("list"),"error");
		}
		$this->popedom_auto($pid);
		if($id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限");
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限");
		}
		$p_rs = $this->model('project')->get_one($pid);
		if(!$p_rs)
		{
			error("操作异常",$this->url("list"),"error");
		}
		$m_rs = $this->model('module')->get_one($p_rs["module"]);
		//读取扩展属性
		$ext_list = $this->model('module')->fields_all($p_rs["module"]);
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value)
		{
			if($value["ext"])
			{
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v)
				{
					$value[$k] = $v;
				}
			}
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]])
			{
				$value["content"] = $rs[$value["identifier"]];
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		$this->assign("p_rs",$p_rs);
		$this->assign("m_rs",$m_rs);
		$this->assign("pid",$pid);
		$plist = array($p_rs);
		if($p_rs["parent_id"])
		{
			$this->model('project')->get_parentlist($plist,$p_rs["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		if($rs["id"])
		{
			$this->assign("id",$rs["id"]);
		}
		if($p_rs["cate"])
		{
			$catelist = $this->model('cate')->get_all($p_rs["site_id"],1,$p_rs["cate"]);
			$catelist = $this->model('cate')->cate_option_list($catelist);
			$this->assign("catelist",$catelist);
		}
		if($p_rs['is_biz'])
		{
			$currency_list = $this->model('currency')->get_list();
			$this->assign('currency_list',$currency_list);
			if(!$rs['currency_id'])
			{
				$rs['currency_id'] = $p_rs['currency_id'];
			}
		}

		//判断是否有父主题
		$parent_id = $this->get("parent_id","int");
		if($parent_id)
		{
			$parent_rs = $this->model('list')->get_one($parent_id);
			if(!$rs["cate_id"])
			{
				$rs["cate_id"] = $parent_rs["cate_id"];
			}
			$this->assign("parent_rs",$parent_rs);
			$this->assign("parent_id",$parent_id);
		}
		$this->assign("rs",$rs);
		$this->model("list");
		if($p_rs['is_attr'])
		{
			$attrlist = $this->model('list')->attr_list();
			$this->assign("attrlist",$attrlist);
		}

		//增加JS和CSS
		$this->addjs('js/laydate/laydate.js');
		$cate_popedom = $_SESSION['admin_rs']['if_system'] ? 'all' : $this->model('admin')->cate_popedom($_SESSION['admin_id']);
		$this->assign('cate_popedom',$cate_popedom);
		$this->view("list_edit");
	}

	function ok_f()
	{
		$id = $this->get("id","int");
		$pid = $this->get("pid","int");
		$parent_id = $this->get("parent_id","int");
		if(!$pid && !$id)
		{
			error("操作异常",$this->url("list"),"error");
		}
		$is_add = true;
		if($id)
		{
			$rs = $this->model('list')->get_one($id);
			$this->assign("rs",$rs);
			$pid = $rs["project_id"];
			$parent_id = $rs["parent_id"];
			$is_add = false;
		}
		if(!$pid)
		{
			error("操作异常",$this->url("list"),"error");
		}
		$this->popedom_auto($pid);
		if($id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限");
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限");
		}
		$p_rs = $this->model('project')->get_one($pid);
		if(!$p_rs)
		{
			error("操作异常",$this->url("list"),"error");
		}
		$error_url = $this->url("list","action");
		if($id)
		{
			$error_url .= "&id=".$id;
		}
		else
		{
			$error_url .= "&pid=".$pid;
		}
		$title = $this->get("title");
		if(!$title)
		{
			error("主题不能为空",$error_url,"error");
		}
		$cate_id = $this->get("cate_id","int");
		$attr = $this->get("attr");
		if($attr && is_array($attr)) $attr = implode(",",$attr);
		$array = array();
		$array["title"] = $title;
		$array["parent_id"] = $parent_id;
		$dateline = $this->get("dateline","time");
		if(!$dateline)
		{
			$dateline = $this->time;
		}
		$array["dateline"] = $dateline;
		if($this->popedom["status"])
		{
			$array["status"] = $this->get("status","int");
		}
		$array["hidden"] = $this->get("hidden","int");
		$array["hits"] = $this->get("hits","int");
		$array["tpl"] = $this->get("tpl");
		$array["sort"] = $this->get("sort","int");
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array["tag"] = $this->get("tag");
		$array["cate_id"] = $cate_id;
		$array["attr"] = $attr;
		//更新标识串
 		$array['identifier'] = $this->get("identifier");
 		if($array['identifier'])
 		{
	 		$check = $this->check_identifier($array['identifier'],$id,$p_rs["site_id"]);
	 		if($check != 'ok') $array['identifier'] = '';
 		}
		if($array["tag"])
		{
			$array["tag"] = str_replace(array("　","，",",","｜","|","、","/","\\","／","＼","+","＋","-","－","_","＿","—")," ",$array["tag"]);
			$array["tag"] = preg_replace("/(\x20{2,})/"," ",$array["tag"]);
		}
		$array["project_id"] = $p_rs['id'];
		$array["module_id"] = $p_rs["module"];
		$array["site_id"] = $p_rs["site_id"];
		//存储价格及货币
		$array["price"] = $this->get('price','float');
		$array["currency_id"] = $this->get('currency_id','int');
		$array["user_id"] = $this->get('user_id','int');
		if(!$id)
		{
			$id = $this->model('list')->save($array);
 		}
 		else
 		{
 			$this->model('list')->save($array,$id);
 		}
 		if(!$id)
 		{
	 		error("存储数据失败，请检查",$error_url,"error");
 		}
 		//更新Tag标签
 		$this->model('tag')->update_tag($array['tag'],$id,$_SESSION['admin_site_id']);
 		if($p_rs["module"])
 		{
	 		$ext_list = $this->model('module')->fields_all($p_rs["module"]);
	 		$tmplist = array();
	 		$tmplist["id"] = $id;
	 		$tmplist["site_id"] = $p_rs["site_id"];
	 		$tmplist["project_id"] = $pid;
	 		$tmplist["cate_id"] = $cate_id;
	 		if(!$ext_list) $ext_list = array();
			foreach($ext_list AS $key=>$value)
			{
				$val = ext_value($value);
				if($value["ext"])
				{
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v)
					{
						$value[$k] = $v;
					}
				}
				if($value["form_type"] == "password")
				{
					$content = $rs[$value["identifier"]] ? $rs[$value["identifier"]] : $value["content"];
					$val = ext_password_format($val,$content,$value["password_type"]);
				}
				$tmplist[$value["identifier"]] = $val;
			}
			$this->model('list')->save_ext($tmplist,$p_rs["module"]);
		}

 		$go_back = $_SESSION["last_page_url"] ? $_SESSION["last_page_url"] : $this->url("list","action","id=".$p_rs["id"]);
 		if($is_add)
 		{
	 		$go_back = $this->url("list","edit","pid=".$p_rs["id"]);
	 		if($cate_id)
	 		{
		 		$go_back .= "&cateid=".$cate_id;
	 		}
	 		if($parent_id)
	 		{
		 		$go_back .= "&parent_id=".$parent_id;
	 		}
 		}
 		//运行插件节点
 		$this->plugin("ap-list-ok-after",array("id"=>$id,"project"=>$p_rs));
 		error("数据存储成功",$go_back,"ok");
	}

	function del_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			json_exit("没有指定主题ID");
		}
		$idlist = explode(",",$id);
		$chk_id = intval($idlist[0]);
		$rs = $this->model('list')->get_one($chk_id);
		$pid = $rs["project_id"];
		$this->popedom_auto($pid);
		if(!$this->popedom["delete"])
		{
			json_exit("您没有删除权限");
		}
		foreach($idlist AS $key=>$value)
		{
			$value = intval($value);
			$this->model('list')->delete($value);
		}
		json_exit("主题删除成功",true);
	}

	function content_status_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("没有指定ID！");
		}
		$rs = $this->model('list')->get_one($id);
		$this->popedom_auto($rs['project_id']);
		if(!$this->popedom["status"])
		{
			json_exit("您没有启用/禁用权限");
		}
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('list')->update_status($id,$status);
		if(!$action)
		{
			json_exit("操作失败，请检查SQL语句！");
		}
		else
		{
			//执行插件接入点
			$this->plugin("ap-list-status",$id);
			json_exit($status,true);
		}
	}

	//执行动作
	function execute_f()
	{
		$ids = $this->get('ids');
		if(!$ids)
		{
			$this->json(P_Lang('未指定要操作的主题'));
		}
		$title = $this->get('title');
		$list = explode(',',$ids);
		$tmp1 = $list[0];
		$rs = $this->model('list')->get_one($tmp1);
		$this->popedom_auto($rs['project_id']);
		if(!$this->popedom["status"])
		{
			$this->json(P_Lang("您没有相应的权限"));
		}
		foreach($list AS $key=>$value)
		{
			$value = intval($value);
			if(!$value)
			{
				continue;
			}
			if($title == 'status')
			{
				$this->model('list')->update_status($value,1);
			}
			elseif($title == 'unstatus')
			{
				$this->model('list')->update_status($value,0);
			}
			elseif($title == 'hidden')
			{
				$this->model('list')->save(array('hidden'=>1),$value);
			}
			elseif($title == 'show')
			{
				$this->model('list')->save(array('hidden'=>0),$value);
			}
		}
		$this->json(P_Lang('操作成功'),true);
	}

	function content_sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort))
		{
			json_exit("更新排序失败");
		}
		foreach($sort AS $key=>$value)
		{
			$this->model('list')->update_sort($key,$value);
		}
		json_exit("更新排序成功",true);
	}

	function move_cate_f()
	{
		$ids = $this->get("ids");
		$cate_id = $this->get("cate_id");
		if(!$cate_id || !$ids)
		{
			json_exit("参数传递不完整");
		}
		$list = explode(",",$ids);
		foreach($list AS $key=>$value)
		{
			$value = intval($value);
			if($value)
			{
				$mid = $this->model('list')->get_mid($value);
				if($mid)
				{
					$array = array("cate_id"=>$cate_id);
					$this->model('list')->save($array,$value);
					$this->model('list')->update_ext($array,$mid,$value);
				}
			}
		}
		json_exit("更新成功",true);
	}

	//设置属性
	function attr_set_f()
	{
		$ids = $this->get("ids");
		$val = $this->get("val");
		$type = $this->get("type");
		if(!$val || !$ids || !$type)
		{
			json_exit("参数传递不完整");
		}
		if($type != "add" && $type != "delete") $type = "add";
		$list = explode(",",$ids);
		foreach($list AS $key=>$value)
		{
			$value = intval($value);
			if(!$value) continue;
			$rs = $this->model('list')->simple_one($value);
			if(!$rs) continue;
			$attr = $rs["attr"];
			if($attr)
			{
				$tmp = explode(",",$attr);
				if($type == "add")
				{
					$tmp[] = $val;
					$tmp = array_unique($tmp);
					$attr = implode(",",$tmp);
				}
				else
				{
					if(in_array($val,$tmp))
					{
						foreach($tmp AS $k=>$v)
						{
							if($v == $val) unset($tmp[$k]);
						}
						if($tmp && count($tmp)>0)
						{
							$attr = implode(",",$tmp);
						}
						else
						{
							$attr = "";
						}
					}
				}
			}
			else
			{
				$attr = $type == "add" ? $val : "";
			}
			$array = array("attr"=>$attr);
			$this->model('list')->save($array,$value);
		}
		json_exit("更新成功",true);
	}

	//更多批处理功能
	function plaction_f()
	{
		$id = $this->get('id');
		if(!$id)
		{
			error(P_Lang('未指定项目ID'),$this->url('list'),'error');
		}
		$this->popedom_auto($id);
		if(!$this->popedom["list"])
		{
			error(P_Lang("你没有查看权限"));
		}
		$project_rs = $this->model('project')->get_one($id);
		if(!$project_rs)
		{
			error(P_Lang("项目信息不存在"),$this->url("list"),"error");
		}
		if(!$project_rs['module'])
		{
			error(P_Lang('未绑定模块，不能使用此功能'),$this->url('list','action','id='.$id),'error');
		}
		$this->assign('page_rs',$project_rs);
		$this->view('list_plaction');
	}

	function plaction_submit_f()
	{
		$pid = $this->get('pid');
		if(!$pid)
		{
			$this->json(P_Lang('未指定项目ID'));
		}
		$this->popedom_auto($id);
		$project_rs = $this->model('project')->get_one($pid);
		if(!$project_rs)
		{
			$this->json(P_Lang("项目信息不存在"));
		}
		if(!$project_rs['module'])
		{
			$this->json(P_Lang('未绑定模块，不能使用此功能'));
		}
		$startid = $this->get('startid','int');
		$endid = $this->get('endid','int');
		$plaction = $this->get('plaction');
		if($plaction == 'delete')
		{
			if(!$this->popedom['delete'])
			{
				$this->json(P_Lang('您没有此权限'));
			}
			$condition = "project_id=".$pid." ";
			if($startid)
			{
				$condition .= "AND id>=".$startid." ";
			}
			if($endid)
			{
				$condition .= "AND id<=".$endid." ";
			}
			$this->model('list')->pl_delete($condition,$project_rs['module']);
			$this->json(P_Lang('批量删除操作成功'),true);
		}
		$sql = "UPDATE ".$this->db->prefix."list SET ";
		if($plaction == 'status' || $plaction == 'unstatus')
		{
			if(!$this->popedom['status'])
			{
				$this->json(P_Lang('您没有此权限'));
			}
			$sql .= " status=".($plaction == 'status' ? '1' : '0')." ";
		}
		elseif($plaction == 'hidden' || $plaction == 'show')
		{
			if(!$this->popedom['list'])
			{
				$this->json(P_Lang('您没有此权限'));
			}
			$sql .= " hidden=".($plaction == 'hidden' ? '1' : '0')." ";
		}
		$sql.= " WHERE project_id='".$pid."' ";
		if($startid)
		{
			$sql .= "AND id>=".$startid." ";
		}
		if($endid)
		{
			$sql .= "AND id<=".$endid." ";
		}
		$this->db->query($sql);
		$this->json(P_Lang('批处理操作成功'),true);
	}
}
?>
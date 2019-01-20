<?php
/***********************************************************
	Filename: phpok/admin/global.func.php
	Note	: 后台通用函数加载
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-27 14:04
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

# 自动加载函数
function autoload_sysmenu()
{
	$list = phpok_sys_menu();
	if(!$list){
		$list = array();
	}
	$toplist = $GLOBALS['app']->model('site')->get_all_site();
	if($toplist){
		$site_info = array();
		foreach($toplist as $key=>$value){
			if($value['id'] == $_SESSION['admin_site_id']){
				$site_info = $value;
				break;
			}
		}
		$list['site_info'] = $site_info;
		$list['site_list'] = $toplist;
	}
	return $list;
}

//后台核心菜单调用
function phpok_sys_menu()
{
	//非管理员不能执行此操作
	if(!$_SESSION["admin_id"]){
		return false;
	}
	//取得所有的权限字段
	$plist = $GLOBALS['app']->model('popedom')->get_all('',false,false);
	if(!$plist){
		$plist = array();
	}
	$popedom_m = $popedom_p = array();
	foreach($plist AS $key=>$value){
		if(!$value["pid"]){
			$popedom_m[$value["gid"]][] = $value["id"];
		}else{
			$popedom_p[] = $value["id"];
		}
	}
	$popedom = $_SESSION["admin_rs"]["if_system"] ? array("all") : $_SESSION["admin_popedom"];
	$menulist = $GLOBALS['app']->model('sysmenu')->get_all($_SESSION["admin_site_id"],1);
	if(!$menulist){
		$menulist = array();
	}
	$ftmp = array('list','index','res');
	foreach($menulist AS $key=>$value){
		//如果不存在子类，则清空该级栏目
		if(!$value["sublist"] || !is_array($value["sublist"]) || count($value["sublist"]) < 1){
			unset($menulist[$key]);
			continue;
		}
		//定义sublist信息
		foreach($value["sublist"] AS $k=>$v){
			if(!in_array($v['appfile'],$ftmp) && !$_SESSION['admin_rs']['if_system'] && $popedom_m[$v['id']]){
				$tmp = array_intersect($popedom,$popedom_m[$v["id"]]);
				if(!$tmp){
					unset($value["sublist"][$k]);
					continue;
				}
			}
			if($v["appfile"] == "list" && !$_SESSION["admin_rs"]["if_system"]){
				if(!$popedom_p || count($popedom_p)<1){
					unset($value["sublist"][$k]);
					continue;
				}else{
					$tmp = array_intersect($popedom,$popedom_p);
					if(!$tmp){
						unset($value["sublist"][$k]);
						continue;
					}
				}
			}
		}
		if(!$value["sublist"] || !is_array($value["sublist"]) || count($value["sublist"]) < 1){
			unset($menulist[$key]);
			continue;
		}
		$menulist[$key] = $value;
	}

	if(!$menulist || count($menulist) < 1 || !is_array($menulist)){
		$GLOBALS['app']->error(P_Lang('导航菜单异常：无法获取后台导航菜单，请检查表qinggan_sysmenu'));
	}

	$top_list = array();
	$tmp_id_list = array();
	$first_id = 0;
	$tmp_i = 0;
	foreach($menulist AS $key=>$value)
	{
		if($tmp_i<1){
			$first_id = $value["id"];
		}
		$title = $value["title"];
		$top_id =$value["id"];
		$ext = "menu_id=".$value["id"];
		if($value["identifier"]){
			$ext .= "&identifier=".$value["identifier"];
		}
		$url = $GLOBALS['app']->url($value["appfile"],$value["func"],$ext);
		$top_list[$key] = array("title"=>$title,"id"=>$top_id,"url"=>$url);
		$tmp_id_list[] = $top_id;
		$tmp_i++;
	}
	$array["top_list"] = $top_list;
	$ctrl = $GLOBALS['app']->get($GLOBALS['app']->config["ctrl_id"],"system");
	$func = $GLOBALS['app']->get($GLOBALS['app']->config["func_id"],"system");
	$identifier = $GLOBALS['app']->get("identifier","system");
	// 计算当前高亮ID，这里是头部的
	$menu_id = $GLOBALS['app']->get("menu_id","int");
	if(!$menu_id){
		$condition = array();
		if($func){
			$condition['func'] = $func;
		}
		if($identifier){
			$condition['identifier'] = $identifier;
		}
		if($_GET){
			$str_list = array();
			foreach($_GET AS $key=>$value){
				if($key != $GLOBALS['app']->config['ctrl_id'] && $key != $GLOBALS['app']->config['func_id'] && $key != 'menu_id' && $key != 'identifier'){
					$str_list[] = $key.'='.rawurlencode($value);
				}
			}
			if($str_list && count($str_list)>0){
				sort($str_list);
				$condition['ext'] = implode('&',$str_list);
			}
		}
		$menu_id = $GLOBALS['app']->model('sysmenu')->get_current_id($_SESSION['admin_site_id'],$ctrl,$condition);
	}
	if($menu_id){
		$rs = $GLOBALS['app']->model('sysmenu')->get_one($menu_id);
		$top_id = $rs["parent_id"] ? $rs["parent_id"] : $rs["id"];
	}else{
		$top_id = $first_id;
	}
	$array["top_id"] = $top_id;

	// 循环得到子列表页
	$i = 0;
	$sub_list = array();
	//计算左侧菜单
	foreach($menulist AS $k=>$v){
		if($k == $top_id){
			foreach($v["sublist"] AS $key=>$value){
				$title = $value["title"];
				$ext = "menu_id=".$value["id"];
				if($value["identifier"]) $ext .= "&identifier=".$value["identifier"];
				if($value['ext']) $ext .= "&".$value['ext'];
				$url = $GLOBALS['app']->url($value["appfile"],$value["func"],$ext);
				$sub_id = $value["id"];
				$sub_list[$i] = array("title"=>$title,"url"=>$url,"id"=>$sub_id);
				$i++;
			}
		}
	}
	$array["sub_list"] = $sub_list;
	// 现在计算子页的高亮
	if($menu_id && $menu_id == $first_id){
		ob_clean();
		header("Location:".$GLOBALS['app']->url("index"));
		exit();
	}
	if($menu_id && in_array($menu_id,$tmp_id_list)){
		$tmp = $menulist[$menu_id]["sublist"];
		$rs = current($tmp);
		$title = $rs["title"];
		$ext = "menu_id=".$rs["id"];
		if($rs["identifier"]){
			$ext .= "&identifier=".$rs["identifier"];
		}
		$url = $GLOBALS['app']->url($rs['appfile'],$rs['func'],$ext);
		ob_clean();
		header("Location:".$url);
		exit;
	}
	$array["sub_id"] = $menu_id;
	return $array;
}

function show_pending_info()
{
	$site_id = $_SESSION["admin_site_id"];
	//取得所有未审核的主题列表
	$list = array();
	$rslist = $GLOBALS['app']->model('list')->pending_info($site_id);
	if($rslist){
		foreach($rslist AS $key=>$value){
			if($value["total"]>0){
				$url = $GLOBALS['app']->url("list","action","id=".$value["pid"]);
				$list[] = array("title"=>$value["title"],"total"=>$value["total"],"url"=>$url);
			}
		}
	}
	//读取未审核的会员信息
	$condition = "u.status=0";
	$user_total = $GLOBALS['app']->model('user')->get_count($condition);
	if($user_total > 0){
		$url = $GLOBALS['app']->url("user","","status=3");
		$list[] = array("title"=>"待审核会员","total"=>$user_total,"url"=>$url);
	}
	//读取未审核的回复信息
	$condition = "status=0";
	$reply_total = $GLOBALS['app']->model('reply')->get_total($condition);
	if($reply_total>0){
		$url = $GLOBALS['app']->url("reply","","status=3");
		$list[] = array("title"=>P_Lang('未审核评论'),"total"=>$reply_total,"url"=>$url);
	}
	return $list;
}

//返回JS URL 或 <script type="text/javascript" src="url"></script>
function include_js($js="",$mini=false,$injs=false)
{
	global $app;
	$array = $app->config['load_js'][$app->app_id];
	$str = array();
	if($array && is_array($array)){
		foreach($array AS $key=>$value){
			if($key == 'global' || $key == $app->ctrl){
				$str[] = $value;
			}
		}
	}
	if($js){
		$str[] = $js;
	}
	$str = implode(",",$str);
	$url = $mini ? $app->url('js','mini','ext='.rawurlencode($str)) : $app->url('js','ext','js='.rawurlencode($str));
	if($injs){
		$url = '<script type="text/javascript" src="'.$url.'"></script>';
	}
	return $url;
}

# 读取扩展
function get_phpok_ext($module,$words="id,identifier")
{
	if(!$module){
		return array_return(P_Lang('未指定模块'));
	}
	if(substr($module,0,3) == "add"){
		$idstring = $_SESSION[$module.'-ext-id'];
		$show_edit = false;
		$rslist = $GLOBALS['app']->model("fields")->get_list($idstring);
	}else{
		$show_edit = true;
		$rslist = $GLOBALS['app']->model("ext")->ext_all($module);
	}
	$list = array();
	$idlist = $words ? explode(",",$words) : array("id","identifier");
	foreach(($rslist ? $rslist : array()) AS $key=>$value){
		if($value["ext"]){
			$ext = unserialize($value["ext"]);
			foreach($ext AS $k=>$v){
				$value[$k] = $v;
			}
		}
		$idlist[] = strtolower($value["identifier"]);
		$list[] = $GLOBALS['app']->lib('form')->format($value);
		$GLOBALS['app']->lib('form')->cssjs($value);
	}
	$idlist = array_unique($idlist);
	$GLOBALS['app']->assign("module",$module);
	$GLOBALS['app']->assign("show_edit",$show_edit);
	$GLOBALS['app']->assign("extlist",$list);
	$content = $GLOBALS['app']->fetch("ext_load");
	$array = array("words"=>implode(",",$idlist),"content"=>$content);
	return array_return($array,true);
}

function appfile_popedom($string,$project_id=0)
{
	if(!$string) return false;
	$string = str_replace(array(",","|","/",">"),":",$string);
	$tmp_s = explode(":",$string);
	$condition = 'parent_id>0 AND ';
	$condition.= $tmp_s[1] ? "appfile='".$tmp_s[0]."' AND func='".$tmp_s[1]."'" : "appfile='".$string."' AND func=''";
	$p_rs = $GLOBALS['app']->model("sysmenu")->get_one_condition($condition);
	if(!$p_rs) return false;
	$gid = $p_rs["id"];
	if(!$_SESSION["admin_rs"]["if_system"]){
		$rslist = $GLOBALS['app']->model("popedom")->get_list($gid,$project_id);
	}else{
		$rslist = $GLOBALS['app']->model("popedom")->get_list($gid,0);
	}
	if(!$rslist) return false;
	$list = array();
	foreach($rslist AS $key=>$value){
		if($_SESSION["admin_rs"]["if_system"]){
			$list[$value["identifier"]] = true;
		}else{
			if($_SESSION["admin_popedom"] && in_array($value["id"],$_SESSION["admin_popedom"])){
				$list[$value["identifier"]] = true;
			}else{
				$list[$value["identifier"]] = false;
			}
		}
	}
	return $list;
}

//权限判断
//string，权限ID，用于处理
function system_popedom($string,$return_type="")
{
	if($_SESSION["admin_rs"]["if_system"]){
		return true;
	}
	if(!$_SESSION["admin_popedom"] || !is_array($_SESSION["admin_popedom"]) || !$string){
		if($return_type == "tips" || $return_type == "tpl" || $return_type == "tip"){
			error(P_Lang('您没有权限执行此操作'),"","error");
		}elseif($return_type == "json"){
			$GLOBALS['app']->json(P_Lang('您没有权限执行此操作'));
		}elseif($return_type == "ajax"){
			exit(P_Lang('您没有权限执行此操作'));
		}else{
			return false;
		}
	}
	$string = str_replace(array(",","|","/",">"),":",$string);
	$list = explode(":",$string);
	$type = $list[0];
	$identify = $list[1];
	$plist = appfile_popedom($type);
	if(!$identify || !$plist || !$plist[$identify]){
		if($return_type == "tips" || $return_type == "tpl" || $return_type == "tip"){
			error(P_Lang('您没有权限执行此操作'),"","error");
		}elseif($return_type == "json"){
			$GLOBALS['app']->json(P_Lang('您没有权限执行此操作'));
		}elseif($return_type == "ajax"){
			exit(P_Lang('您没有权限执行此操作'));
		}else{
			return false;
		}
	}
	return true;
}
<?php
/***********************************************************
	Filename: {phpok}/www/global.func.php
	Note	: 前台公共函数
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-04-16 13:13
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

//下一篇主题
if(!function_exists("phpok_next"))
{
	function phpok_next($rs)
	{
		if(!$rs) return false;
		if(!is_array($rs))
		{
			$rs = $GLOBALS['app']->model('list')->call_one($rs);
			if(!$rs) return false;
		}
		$rs = $GLOBALS['app']->model('list')->get_next($rs['id'],$rs["cate_id"],$rs["project_id"],$rs["module_id"],$rs["site_id"]);
		if($rs)
		{
			$rs['url'] = msgurl($rs['identifier'] ? $rs['identifier'] : $rs['id']);
		}
		return $rs;
	}
}

//取得上一篇主题
if(!function_exists("phpok_prev"))
{
	function phpok_prev($rs)
	{
		if(!$rs) return false;
		if(!is_array($rs))
		{
			$rs = $GLOBALS['app']->model('list')->call_one($rs);
			if(!$rs) return false;
		}
		$rs = $GLOBALS['app']->model('list')->get_prev($rs['id'],$rs["cate_id"],$rs["project_id"],$rs["module_id"],$rs["site_id"]);
		if($rs)
		{
			$rs['url'] = msgurl($rs['identifier'] ? $rs['identifier'] : $rs['id']);
		}
		return $rs;
	}
}

if(!function_exists("phpok_all"))
{
	function phpok_all($id,$total=100,$startid=0)
	{
		global $app;
		$app->model("id");
		$app->model('ext');
		$app->model('module');
		$site_id = $app->config_site["id"];
		$app_rs = $app->id_model->get_id($id,$site_id);
		$list = array();
		$list["app_rs"] = $app_rs;
		if($app_rs["type_id"] == "project")
		{
			$project_rs = phpok_project($app_rs["id"]);
			$list["rs"] = $project_rs;
			if($project_rs["module"])
			{
				$app->model("list");
				$module_list = phpok_module($project_rs["module"]);
				$list["module_list"] = $module_list;
				$condition = " l.project_id='".$project_rs['id']."' ";
				$condition.= " AND l.module_id='".$project_rs["module"]."' ";
				if($project_rs["cate"])
				{
					$cate_array = phpok_cate($project_rs["cate"]);
					if($cate_array)
					{
						foreach($cate_array AS $key=>$value)
						{
							$list[$key] = $value;
						}
					}
					$condition .= " AND l.cate_id IN(".$list["cate_idstring"].")";
				}
				$extlist = $app->module_model->fields_all($project_rs["module"],"identifier");
				if(!$extlist) return false;
				$rslist = $app->list_model->get_list($project_rs['module'],$condition,$startid,$total);
				if($rslist)
				{
					$list['rslist'] = $rslist;
				}
			}
			return $list;
		}
	}
}

if(!function_exists('phpok_module'))
{
	function phpok_module($id)
	{
		global $app;
		$app->model('module');
		$app->model("id");
		$app->model("cate");
		$site_id = $app->config_site["id"];
		$module_list = $app->module_model->fields_all($id);
		if(!$module_list) return false;
		foreach($module_list AS $key=>$value)
		{
			$ext = $value["ext"] ? unserialize($value["ext"]) : array();
			if($value["form_type"] == "cate")
			{
				if($ext["root_cate"])
				{
					$sonlist = $app->cate_model->get_sonlist($ext["root_cate"]);
					$value["ext"] = $sonlist;
				}
			}
			$module_list[$key] = $value;
		}
		return $module_list;
	}
}

if(!function_exists("phpok_project"))
{
	function phpok_project($id,$type="id")
	{
		global $app;
		$app->model('ext');
		if($type != "id")
		{
			$app->model("id");
			$site_id = $app->config_site["id"];
			$app_rs = $app->id_model->get_id($id,$site_id);
			if(!$app_rs || $app_rs['type_id'] != "project") return false;
			$id = $app_rs["id"];
		}
		$app->model("project");
		$project_rs = $app->project_model->get_one($id);
		if(!$project_rs) return false;
		$ext_rs = $app->ext_model->ext_all("project-".$id);
		if($ext_rs)
		{
			foreach($ext_rs AS $key=>$value)
			{
				$project_rs[$value["identifier"]] = content_format($value);
			}
		}
		return $project_rs;
	}
}

if(!function_exists("phpok_cate"))
{
	function phpok_cate($id,$type="id")
	{
		global $app;
		$app->model('ext');
		if($type != "id")
		{
			$app->model("id");
			$site_id = $app->config_site["id"];
			$app_rs = $app->id_model->get_id($id,$site_id);
			if(!$app_rs || $app_rs['type_id'] != "cate") return false;
			$id = $app_rs["id"];
		}
		$app->model("cate");
		$cate_rs = $app->cate_model->get_one($id);
		$array = array();
		$array["cate_rs"] = $cate_rs;
		$catelist = array();
		$app->cate_model->get_sublist($catelist,$id);
		$array["cate_sublist"] = $catelist;
		$cate_id_list = array();
		foreach($catelist AS $key=>$value)
		{
			$cate_id_list[] = $value["id"];
		}
		$cate_idstring = implode(",",$cate_id_list);
		$array["cate_idstring"] = $cate_idstring;
		return $array;
	}
}


function autoload_qq_info()
{
	global $app;
	include_once($app->dir_root."OpenApiV3.php");
	$rs = phpok("app");
	if(!$rs) return false;
	$app->assign("app_rs",$rs);
	if(!$rs["app_id"] || !$rs["app_key"] || !$rs["app_qq"] || !$rs["app_ip"]) return false;
	$openid = $app->get("openid");
	$openkey = $app->get("openkey");
	$pf = $app->get("pf");
	if(!$openid || !$openkey) return false;
	if(!$pf) $pf = "qzone";
	$urlext = "&openid=".$openid."&openkey=".$openkey."&pf=".$pf;
	$app->assign("urlext",$urlext);
	$sdk = new OpenApiV3($rs["app_id"], $rs["app_key"]);
	$sdk->setServerName($rs["app_ip"]);
	$params = array('openid' => $openid,'openkey' => $openkey,'pf' => $pf);
	$script_name = '/v3/user/get_info';
	$qq_info = $sdk->api($script_name, $params,'post');
	$app->assign("qq_info",$qq_info);
}

function phpok_fav()
{
	if(!$_SESSION['user_id']) return false;
	//收藏夹ID
	$mid = "23";
	$sql = "SELECT title_id FROM ".$GLOBALS['app']->db->prefix."list_".$mid." WHERE post_uid='".$_SESSION['user_id']."'";
	$rslist = $GLOBALS['app']->db->get_all($sql);
	if(!$rslist) return false;
	$list = array();
	foreach($rslist AS $key=>$value)
	{
		$list[] = $value['title_id'];
	}
	$GLOBALS['app']->cache_data['fav_list'] = $list;
	$GLOBALS['app']->assign("fav_id_list",$list);
}
?>
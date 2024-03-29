<?php
/**
 * 常用函数
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年06月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

/**
 * 适用于在插件或模板中获取参数变量值
 * @参数 $id 变量名
 * @参数 $type 类型，默认是 safe，支持 html，safe，int，float
**/
function G($id,$type='safe')
{
	global $app;
	return $app->get($id,$type);
}

//调用生成器
function Gen($string = '')
{
	if(!$string){
		return array();
	}
	$list = explode("/",$string);
	if(!isset($list[1]) || !isset($list[2])){
		return array();
	}
	global $app;
	$tmp = array('model','lib');
	if(!in_array($list[0],$tmp)){
		return array();
	}
	if(isset($list[5])){
		return $app->$list[0]($list[1])->$list[2]($list[3],$list[4],$list[5]);
	}
	if(isset($list[4])){
		return $app->$list[0]($list[1])->$list[2]($list[3],$list[4]);
	}
	if(isset($list[3])){
		return $app->$list[0]($list[1])->$list[2]($list[3]);
	}
	return $app->$list[0]($list[1])->$list[2]();
}


/**
 * 获取表名
 * @参数 $name 表名称，仅数字表示模块ID，数组表示模块信息，非数字返回带上表前缀的表名称
 * @返回 表名称
**/
function tablename($name='',$prefix=true)
{
	if(!$name){
		return false;
	}
	global $app;
	if(is_numeric($name) || is_array($name)){
		return $app->model('module')->tablename($name,$prefix);
	}
	if($prefix){
		return $app->db()->prefix().$name;
	}
	return $name;
}

/**
 * 字符串截取
 * @参数 $string 要截取的字符串
 * @参数 $length 长度
 * @参数 $dot 尾部是否增加参数，如省略号等
**/
function phpok_cut($string,$length=255,$dot="")
{
	global $app;
	return $app->lib("string")->cut($string,$length,$dot);
}

/**
 * 执行SQL操作
 * @参数 $db DB引挈，这里直接从外部引入
 * @参数 $sql SQL文件或是SQL代码
 * @参数 $isfile 为true时表示这是$sql是一个文件，为false表示是一个字串
**/
function phpok_loadsql($db,$sql='',$isfile=false)
{
	if(!$db){
		return false;
	}
	if($isfile && !file_exists($sql)){
		return false;
	}
	if($isfile){
		$sql = file_get_contents($sql);
	}
	$sql = str_replace("\r","\n",$sql);
	if($db->prefix != 'qinggan_'){
		$sql = str_replace("qinggan_",$db->prefix,$sql);
	}
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query){
		$queries = explode("\n", trim($query));
		foreach($queries as $query){
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	foreach($ret as $query){
		$query = trim($query);
		if($query){
			$db->query($query);
		}
	}
	return true;
}

/**
 * 获取随机数据
 * @参数 $length 长度，默认10
 * @参数 $type，类型，支持 all，number，letter，默认是all
**/
function str_rand($length=10,$type='all')//随机字符，参数是长度
{
	if(!$length){
		$length = 10;
	}
	global $app;
	return $app->lib('common')->str_rand($length,$type);
}

//创建API_url
//ctrl，控制器名称
//func，应用方法名称
//ext，是否有扩展参数
//root，是否包含网站域名
function api_url($ctrl,$func="",$ext="",$root=false)
{
	$url = $root ? $GLOBALS['app']->url : '';
	$url .= $GLOBALS['app']->config['api_file'].'?';
	if($ctrl && $ctrl != 'index'){
		$url .= $GLOBALS['app']->config['ctrl_id']."=".rawurlencode($ctrl).'&';
	}
	if($func && $func != 'index'){
		$url .= $GLOBALS['app']->config['func_id'].'='.rawurlencode($func).'&';
	}
	if($ext){
		$url .= $ext;
	}
	$url = str_replace('&amp;','&',$url);
	if(substr($url,-1) == '&'){
		$url = substr($url,0,-1);
	}
	return $url;
}

# Ajax提示信息封装
function json_exit($content,$status=false)
{
	$GLOBALS['app']->json($content,$status);
}

function json_ok($content,$format=true)
{
	$GLOBALS['app']->json($content,true,true,$format);
}

# Ajax 返回提示封装
function array_return($content,$status=false)
{
	$rs = array();
	$rs["status"] = $status ? "ok" : "error";
	$rs["content"] = $content;
	return $rs;
}

//自定义跳转
//tips，提示信息
//url，网址
//type，类型，支持
//	notice：通知类文件
//	ok：状态为对
//	error：状态为错误
function error($tips="",$url="",$type="notice",$time=2)
{
	global $app;
	if(!$tips && !ob_get_contents()){
		$app->_location($url);
		exit;
	}
	if(!$url && !$tips){
		$app->error("操作异常，没有传递任何有效参数！");
	}
	if($type == 'error'){
		$app->error($tips,$url,$time);
	}else{
		if($type == 'notice'){
			$app->tip($tips,$url,$time);
			exit;
		}
		$app->success($tips,$url,$time);
	}
	exit;
}

/**
 * 字串不可逆加密（加盐模式）
 * @参数 $pass 要加密的字串
**/
function password_create($pass)
{
	$password = md5($pass);
	$get_rand = substr($password,rand(0,30),2);
	$newpass = md5($pass.$get_rand).":".$get_rand;
	return $newpass;
}

/**
 * 密码验证，基于MD5实现，支持三种加密：32位长度，16位长度，32位加盐模式
 * @参数 $pass 原文，即明文密码
 * @参数 $password 要比较的字串，即加密后的密码
 * @参数
**/
function password_check($pass,$password)
{
	if(!$password || !$pass) return false;
	$list = explode(":",$password);
	if($list[1]){
		$chkpass = strlen($pass) != 32 ? md5($pass.$list[1]) : $pass;
		return $chkpass == $list[0] ? true : false;
	}
	if(strlen($pass) == 32){
		if($pass == $password){
			return true;
		}
		return false;
	}
	if(strlen($password) == 16){
		$tmp = substr(md5($pass),8,16);
		if($tmp == $password){
			return true;
		}
		return false;
	}
	$chkpass = strlen($pass) != 32 ? md5($pass) : $pass;
	return $chkpass == $password ? true : false;
}

//格式化获取扩展数据的内容
function ext_value($rs)
{
	global $app;
	$val = $app->lib('form')->get($rs);
	if($val != ''){
		return $val;
	}
	$tmp = array('int','float','html','html_js','time','text');
	if($rs['format'] && in_array($rs['format'],$tmp)){
		$val = $app->get($rs['identifier'],$rs['format']);
	}else{
		$val = $app->get($rs['identifier']);
	}
	if($val && is_array($val)){
		if($rs['form_type'] == 'url'){
			$val = array('default'=>$val[0],'rewrite'=>$val[1]);
		}
		$val = serialize($val);
	}
	return $val;
}

/**
 * 内容图片本地化操作
**/
function phpok_img_local($content)
{
	if(!$content){
		return false;
	}
	preg_match_all("/<img\s*.+\s*src\s*=\s*[\"|']?\s*([^>\"'\s]+?)[\"|'| ]?.*[\/]?>/isU",$content,$matches);
	$list = $matches[1];
	if(!$list || count($list)<1){
		return $content;
	}
	global $app;
	$list = array_unique($list);
	$url_list = array();
	$local_url = $app->get_url();
	$local_url_length = strlen($local_url);
	$local_url_parse = parse_url($local_url);
	if(!$local_url_parse["port"]){
		$local_url_parse["port"] = $local_url_parse["scheme"] == "http" ? "80" : "443";
	}
	$pic_type_list = array("gif","png","jpg","jpeg");
	$cate_rs = $app->model("res")->cate_default();
	$folder = $cate_rs["root"];
	if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
		$folder .= date($cate_rs["folder"],$app->time);
	}
	if(!file_exists($folder)){
		$app->lib("file")->make($folder);
	}
	if(substr($folder,-1) != "/"){
		$folder .= "/";
	}
	if(substr($folder,0,1) == "/"){
		$folder = substr($folder,1);
	}
	if($folder){
		$folder = str_replace("//","/",$folder);
	}
	$save_folder = $app->dir_root.$folder;
	foreach($list as $key=>$value){
		$value = trim($value);
		if(!$value){
			continue;
		}
		$tmp = substr($value,0,7);
		$tmp = strtolower($tmp);
		if($tmp == "file://" && $tmp != "http://" && $tmp != "https:/"){
			continue;
		}
		//将网址后面的@符号去掉
		$old_url = $value;
		$value = preg_replace("/\.(jpg|png|gif|jpeg)@.+?/isU",'.$1',$value);
		$tmp = parse_url($value);
		if(!$tmp["port"]){
			$tmp["port"] = $tmp["scheme"] == "http" ? "80" : "443";
		}
		if($tmp["host"] == $local_url_parse["host"]){
			if(substr($value,0,$local_url_length) == $local_url){
				$new_url = substr($value,$local_url_length);
			}else{
				$new_url = $value;
				if($tmp["port"] == $local_url_parse['port']){
					$del_url = $tmp["scheme"]."://".$tmp["host"];
					if($tmp["port"] != "80" && $tmp["port"] != "443"){
						$del_url .= ":".$tmp["port"];
					}
					$del_url_length = strlen($del_url);
					if(substr($value,0,$del_url_length) == $del_url){
						$new_url = substr($value,$del_url_length);
					}
				}
			}
			$url_list[] = array("old_url"=>$old_url,"new_url"=>$new_url);
		}else{
			$tmp = explode(".",$value);
			$ext_id = count($tmp) - 1;
			$ext = $tmp[$ext_id];
			if(!$ext){
				$ext = "png";
			}
			$ext = strtolower($ext);
			if(!in_array($ext,$pic_type_list)){
				$ext = "png";
			}
			$content_img = $app->lib("html")->get_content($value);
			if(!$content_img){
				continue;
			}
			//文件名
			$filename = $app->time."_".$key.".".$ext;
			$app->lib("file")->save_pic($content_img,$save_folder.$filename);
			unset($content_img);
			//生成记录
			$array = array();
			$array["cate_id"] = $cate_rs["id"];
			$array["folder"] = $folder;
			$array["name"] = $filename;
			$array["ext"] = $ext;
			$array["filename"] = $folder.$filename;
			$array["addtime"] = $app->time;
			$array["title"] = str_replace(".".$ext,"",$app->lib('string')->to_utf8(basename($value)));
			$img_ext = getimagesize($save_folder.$filename);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
			$array["session_id"] = $app->session->sessid();
			$insert_id = $app->model("res")->save($array);
			$ico = $app->lib("gd")->thumb($array["filename"],$insert_id);
			if(!$ico){
				$ico = "images/filetype-large/".$ext.".jpg";
				if(!file_exists($ico)){
					$ico = "images/filetype-large/unknow.jpg";
				}
			}else{
				$ico = $folder.$ico;
			}
			$tmp = array();
			$tmp["ico"] = $ico;
			$app->model("res")->save($tmp,$insert_id);
			$url_list[] = array("old_url"=>$old_url,"new_url"=>$folder.$filename);
		}
	}
	foreach($url_list as $key=>$value){
		$content = str_replace($value["old_url"],$value["new_url"],$content);
	}
	return $content;
}

//Post 密码属性的设置
function ext_password_format($val,$content,$type)
{
	if(!$val || !$type || $type == "default") return $val;
	if(!$content)
	{
		return $type == "md5" ? md5($val) : $val;
	}
	if($type == "show")
	{
		if(strlen($val) == strlen($content) && substr($val,0,1) == substr($content,0,1) && substr($content,-1) == substr($val,-1))
		{
			return $content;
		}
		return $val;
	}
	return $val == $content ? $val : md5($val);
}

# 存储扩展字段
function ext_save($myid,$is_add=false,$save_id="")
{
	if($is_add){
		$tmplist = $_SESSION[$myid];
		if(!$tmplist){
			return false;
		}
		foreach($tmplist as $key=>$value){
			$val = ext_value($value);
			$array = array();
			$array["module"] = $save_id ? $save_id : $myid;
			$array["title"] = $value['title'];
			$array["identifier"] = $value['identifier'];
			$array["field_type"] = $value['field_type'];
			$array["note"] = $value['note'];
			$array["form_type"] = $value['form_type'];
			$array["form_style"] = $value["form_style"];
			$array["format"] = $value["format"];
			$array["content"] = $value["content"];
			$array["taxis"] = $value["taxis"];
			$array["ext"] = $value["ext"];
			if($value["ext"] && $value["content"] && $val){
				$tmp = is_string($value['ext']) ? unserialize($value["ext"]) : $value['ext'];
				if($value["form_type"] == "password"){
					$val = ext_password_format($val,$value["content"],$tmp["password_type"]);
				}
				$array["ext"] = serialize($tmp);
			}
			$insert_id = $GLOBALS['app']->model("ext")->save($array);
			$GLOBALS['app']->model("ext")->extc_save($val,$insert_id);
		}
		$_SESSION[$myid] = "";
	}else{
		$tmplist = $GLOBALS['app']->model("ext")->ext_all($myid);
		if(!$tmplist){
			return false;
		}
		foreach($tmplist as $key=>$value){
			$val = ext_value($value);
			if($value["form_type"] == "password"){
				$tmp = $value["ext"] ? unserialize($value["ext"]) : "";
				if(!$tmp){
					$tmp = array();
					$tmp["password_type"] = "default";
				}
				$val = ext_password_format($val,$value["content"],$tmp["password_type"]);
			}
			$GLOBALS['app']->model("ext")->extc_save($val,$value["id"]);
		}
	}
	return true;
}

# 删除扩展字段
function ext_delete($myid)
{
	return $GLOBALS['app']->model("ext")->del($myid);
}

/**
 * 输入输出价格信息
**/
function __price_format_chk($currency_id=0,$show_id=0,$currency_rate=0,$show_rate=0)
{
	global $app;
	if(!$currency_id && !$show_id){
		$currency_id = $app->site['currency_id'];
		if(!$currency_id){
			return false;
		}
	}
	$data = array();
	if(!$currency_id){
		$currency_id = $show_id;
	}
	if(is_array($currency_id)){
		$currency = $currency_id;
	}else{
		$currency = $app->model('currency')->get_one($currency_id);
	}
	if($currency_rate && $currency_rate>0){
		$currency['val'] = $currency_rate;
	}
	if(!$show_id){
		$show_id = $currency_id;
	}
	if(is_array($show_id)){
		$show = $show_id;
	}else{
		$show = $app->model('currency')->get_one($show_id);
	}
	if($app->app_id == 'admin' && $show['id'] != $app->site['currency_id']){
		$show = $app->model('currency')->get_one($app->site['currency_id']);
	}
	if($show_rate && $show_rate>0){
		$show['val'] = $show_rate;
	}
	return array('in'=>$currency,'out'=>$show);
}

/**
 * 价格格式化
 * @参数 $val 要格式化的价格
 * @参数 $currency_id 当前货币ID，如果为浮点值，表示当前汇率，支持数组
 * @参数 $show_id 输出货币ID，支持数组
 * @参数 $show_rate 输出货币对应的汇率
 * @参数 $currency_rate 当前货币的汇率
**/
function price_format($val='',$currency_id='',$show_id=0,$show_rate=0,$currency_rate=0)
{
	$data = __price_format_chk($currency_id,$show_id,$currency_rate,$show_rate);
	if(!$data){
		return false;
	}
	$currency = $data['in'];
	$show = $data['out'];
	if($show['id'] != $currency['id'] && $currency['val']){
		$val = ($val/$currency['val']) * $show['val'];
	}
	$tmp = number_format(abs($val),$show['dpl'],'.',',');
	$string = $show['symbol_left'].$tmp.$show['symbol_right'];
	if($val<0){
		$string = '- '.$string;
	}
	return $string;
}

/**
 * 价格格式化，仅显示值
 * @参数 $val 要格式化的价格
 * @参数 $currency_id 当前货币ID，如果为浮点值，表示当前汇率，支持数组
 * @参数 $show_id 输出货币ID，支持数组
 * @参数 $show_rate 输出货币对应的汇率
 * @参数 $currency_rate 当前货币的汇率
**/
function price_format_val($val='',$currency_id='',$show_id=0,$show_rate=0,$currency_rate=0)
{
	$data = __price_format_chk($currency_id,$show_id,$currency_rate,$show_rate);
	if(!$data){
		return false;
	}
	$currency = $data['in'];
	$show = $data['out'];
	if($show['id'] != $currency['id'] && $currency['val']){
		$val = ($val/$currency['val']) * $show['val'];
	}
	$val = number_format($val,$show['dpl'],".","");
	return $val;
}

function content_format($value,$type="ext")
{
	if($value['form_type'] == "cate" && $value["content"]){
		$tmplist = $GLOBALS['app']->model("list")->catelist($value["content"]);
		$value["content"] = $tmplist[$value["content"]];
	}elseif($value["form_type"] == "upload" && $value["content"]){
		if(is_array($value["content"])){
			if($value["content"]["id"]){
				$tmp = $value["content"]["id"];
			}else{
				$tmp = array();
				foreach($value["content"] as $k=>$v){
					$tmp[] = $v["id"];
				}
				$tmp = implode(",",$tmp);
			}
			$value["content"] = $tmp;
		}
		$tmplist = $GLOBALS['app']->model("res")->reslist($value["content"]);
		$ext = $value["ext"] ? unserialize($value["ext"]) : array("is_multiple"=>false);
		if($ext["is_multiple"]){
			$tmp = explode(",",$value["content"]);
			foreach($tmp as $kk=>$vv){
				$tmp[$kk] = $tmplist[$vv];
			}
			$value["content"] = $tmp;
		}else{
			$value["content"] = $tmplist[$value["content"]];
		}
	}
	return $value["content"];
}

function phpok_filesize($size,$is_file=true)
{
	if($is_file){
		$size = file_exists($size) ? filesize($size) : 0;
	}
	if(!$size){
		return "0 KB";
	}
	return $GLOBALS['app']->lib("trans")->num_format($size);
}

function phpok_user_login($id,$pass="",$field='id')
{
	if(!$id){
		return P_Lang('未指定用户账号或Email或手机号或ID号');
	}
	$rs = $GLOBALS['app']->model('user')->get_one($id,$field);
	if(!$rs){
		return P_Lang('用户信息不存在');
	}
	if(!$rs["status"]){
		return P_Lang('用户账号未审核');
	}
	if($rs['status'] == '2'){
		return P_Lang('用户账号被锁定，请联系管理员');
	}
	if($pass && !password_check($pass,$rs["pass"])){
		return P_Lang('用户账号验证不通过，密码不正确');
	}
	$_SESSION["user_id"] = $id;
	$_SESSION["user_gid"] = $rs['group_id'];
	$_SESSION["user_name"] = $rs["user"];
	$_SESSION["user_status"] = $rs["status"];
	return 'ok';
}

//取得扩展表里的信息
function phpok_ext_info($module,$extc=true)
{
	$rslist = $GLOBALS['app']->model('ext')->ext_all($module,true);
	if(!$rslist) return false;
	$rs = array();
	foreach($rslist as $key=>$value){
		$rs[$value["identifier"]] = content_format($value);
	}
	return $rs;
}

//取得主题扩展内容信息
function phpok_ext_list($mid,$tid=0)
{
	if(!$mid || !$tid) return false;
	$GLOBALS['app']->model("module");
	$rslist = $GLOBALS['app']->model('module')->fields_all($mid,"identifier");
	if(!$rslist) return false;
	$idlist = array_keys($rslist);
	$GLOBALS['app']->model("list");
	$infolist = $GLOBALS['app']->model('list')->get_ext_list($mid,$tid);
	if(!$infolist) $infolist = array();
	foreach($idlist as $key=>$value){
		foreach($infolist as $k=>$v){
			unset($v["site_id"],$v["project_id"],$v["cate_id"],$v["id"]);
			if($v[$value])
			{
				$tmp = $rslist[$value];
				$tmp["content"] = $v[$value];
				$v[$value] = content_format($tmp);
			}
			$infolist[$k] = $v;
		}
	}
	return $infolist;
}


//取得表单选项信息
function phpok_opt($id,$ext="")
{
	$group_rs = $GLOBALS['app']->model("opt")->group_one($id);
	if(!$group_rs)
	{
		return false;
	}
	$condition = "group_id='".$group_rs["id"]."'";
	if($ext)
	{
		$ext_condition = "group_id='".$group_rs["id"]."' AND val='".$ext."'";
		$ext_rs = $GLOBALS['app']->model("opt")->opt_one_condition($ext_condition);
		if($ext_rs)
		{
			$condition .= " AND parent_id='".$ext_rs["id"]."'";
		}
	}
	else
	{
		$condition .= " AND parent_id='0'";
	}
	$all = $GLOBALS['app']->model("opt")->opt_all($condition);
	if(!$all){
		return false;
	}
	return $all;
}

function phpok_decode($string,$id="")
{
	if(!$string){
		return false;
	}
	$t = unserialize($string);
	if(!$id){
		return $t;
	}
	if($id == "url"){
		return $t[$GLOBALS['app']->site["url_type"]];
	}
	return $t[$id];
}

function phpok_config($id='')
{
	global $app;
	if($id){
		return $app->model('config')->get_one($id);
	}
	return $app->model('config')->get_all();
}

//WEB前台通用模板，如果您的程序比较复杂，请自己写Head
function tpl_head($array=array())
{
	global $app;
	$html  = '<!DOCTYPE html>'."\n";
	$html .= '<html>'."\n";
	$html .= '<head>'."\n\t".'<meta charset="utf-8" />'."\n\t";
	$html .= '<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />'."\n\t";
	$html .= '<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />'."\n\t";
	$html .= '<meta http-equiv="Cache-control" content="no-cache,no-store,must-revalidate" />'."\n\t";
	$html .= '<meta http-equiv="Pragma" content="no-cache" />'."\n\t";
	$html .= '<meta http-equiv="Expires" content="-1" />'."\n\t";
	$html .= '<meta name="renderer" content="webkit" />'."\n\t";
	if($app->license == 'LGPL' || $app->license == 'MIT'){
		$html .= '<meta name="author" content="phpok,admin@phpok.com" />'."\n\t";
	}
	$html .= '<meta name="license" content="'.$app->license.'" />'."\n\t";
	$seo = $app->tpl->val('seo');
	if($array['seo_title']){
		$seo['title'] = $array['seo_title'];
	}
	if($app->site['meta']){
		$app->site['meta'] = trim(str_replace(array("\t","\r"),"",$app->site['meta']));
		if($app->site['meta']){
			$t = explode("\n",$app->site['meta']);
			foreach($t as $key=>$value){
				$html .= $value."\n\t";
			}
		}
	}
	$headtitle = $app->config['seo']['format'] ? $app->config['seo']['format'] : '{title}-{seo}-{sitename}';
	$headtitle = explode("-",$headtitle);
	foreach($headtitle as $key=>$value){
		if($value == '{seo}'){
			if($seo['title']){
				$headtitle[$key] = $seo['title'];
			}else{
				unset($headtitle[$key]);
			}
		}elseif($value == '{sitename}'){
			if($app->site['title']){
				$headtitle[$key] = $app->site['title'];
			}else{
				unset($headtitle[$key]);
			}
		}elseif($value == '{title}'){
			if($array['title']){
				$headtitle[$key] = $array['title'];
			}else{
				unset($headtitle[$key]);
			}
		}elseif($value == '{cate}'){
			if($array['cate']){
				$headtitle[$key] = $array['cate'];
			}else{
				$tmp = $GLOBALS['app']->tpl->val('cate_rs');
				if($tmp){
					$headtitle[$key] = $tmp['title'];
					unset($tmp);
				}else{
					unset($headtitle[$key]);
				}
			}
		}elseif($value == '{project}'){
			if($array['project']){
				$headtitle[$key] = $array['project'];
			}else{
				$tmp = $GLOBALS['app']->tpl->val('page_rs');
				if($tmp){
					$headtitle[$key] = $tmp['title'];
					unset($tmp);
				}else{
					unset($headtitle[$key]);
				}
			}
		}
	}
	$headtitle = implode('-',$headtitle);
	$headtitle = str_replace('-',$app->config['seo']['line'],$headtitle);
	$html .= '<title>'.trim($headtitle).'</title>'."\n\t";
	if($array['keywords']){
		$seo['keywords'] = $array['keywords'];
	}
	if($array['description']){
		$seo['description'] = $array['description'];
	}
	if($seo['keywords'] && trim($seo['keywords'])){
		$html .= '<meta name="keywords" content="'.$seo['keywords'].'" />'."\n\t";
	}
	if($seo['description'] && trim($seo['description'])){
		$html .= '<meta name="description" content="'.$seo['description'].'" />'."\n\t";
	}
	if(substr($app->url,-1) != '/'){
		$app->url .= "/";
	}
	$html .= '<meta name="toTop" content="true" />'."\n\t";
	$html .= '<base href="'.$app->url.'" />'."\n\t";
	$cssjs_debug = $app->config['debug'] ? '?_noCache=0.'.rand(1000,9999) : '';
	$ico = ($array['ico'] && file_exists($app->dir_root.$array['ico'])) ? $array['ico'] : '';
	if(!$ico && $app->site['favicon']){
		$ico = $app->site['favicon'];
	}
	if($ico){
		$html .= '<link rel="shortcut icon" href="'.$app->url.$ico.$cssjs_debug.'" />'."\n\t";
	}
	//增加Bootstrap
	if(!isset($array['css'])){
		$array['css'] = '';
	}
	$array['css'] = 'static/bootstrap/css/bootstrap.css,static/fontawesome/css/font-awesome.css,static/wow/animate.css,'.$array['css'];
	if($array["css"]){
		$tmp = explode(",",$array['css']);
		$tmp = array_unique($tmp);
		foreach($tmp as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			if($value == basename($value)){
				$html .= '<link rel="stylesheet" type="text/css" href="'.$app->url."css/".$value.$cssjs_debug.'" />'."\n\t";
			}else{
				$html .= '<link rel="stylesheet" type="text/css" href="'.$app->url.$value.$cssjs_debug.'" />'."\n\t";
			}
		}
	}
	$html .= phpok_head_css();
	$jsurl = $app->url('js');
	$include_js = $app->is_mobile ? $app->config['mobile']['includejs'] : $app->config['pc']['includejs'];
	if($array['extjs']){
		$include_js = $include_js ? $include_js.','.$array['extjs'] : $array['extjs'];
	}
	if($array['includejs']){
		$include_js = $include_js ? $include_js.','.$array['includejs'] : $array['includejs'];
	}
	if($array['incjs']){
		$include_js = $include_js ? $include_js.','.$array['incjs'] : $array['incjs'];
	}
	if($include_js){
		$jsurl .= "&ext=".rawurlencode($include_js);
	}
	$exclude_js = $app->is_mobile ? $app->config['mobile']['excludejs'] : $app->config['pc']['excludejs'];
	if($array['excludejs']){
		$exclude_js = $exclude_js ? $exclude_js.','.$array['excludejs'] : $array['excludejs'];
	}
	if($exclude_js){
		$jsurl .= "&_ext=".rawurlencode($exclude_js);
	}
	$html .= '<script type="text/javascript" src="'.$jsurl.'" charset="utf-8"></script>'."\n\t";
	if(!isset($array['js'])){
		$array['js'] = '';
	}
	$array['js'] = 'static/bootstrap/js/bootstrap.bundle.js,static/wow/wow.js,'.$array['js'];
	if($array['js']){
		$tmp = explode(",",$array['js']);
		$tmp = array_unique($tmp);
		$tpldir = $app->tpl->dir_tpl;
		$tpldir_length = strlen($tpldir);
		foreach($tmp as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			if(substr($value,0,$tpldir_length) == $tpldir){
				$html .= '<script type="text/javascript" src="'.$app->url.$value.$cssjs_debug.'"></script>'."\n\t";
			}else{
				$html .= '<script type="text/javascript" src="'.$app->url.'js/'.$value.$cssjs_debug.'"></script>'."\n\t";
			}
		}
	}
	$html .= phpok_head_js();
	$close = true;
	if(isset($array['close']) && !$array['close']){
		$close = false;
	}
	if($close){
		ob_start();
		$app->plugin_html_ap("phpokhead");
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= "\n".'</head>';
	}
	$html .= "\n";
	return $html;
}

/**
 * 表单生成器
**/
function form_edit($id,$content="",$type="text",$attr="",$return='echo')
{
	global $app;
	if(!$id){
		return false;
	}
	if(is_numeric($id)){
		$array = $app->model('fields')->one($id);
		if($content != ''){
			$array['content'] = $content;
		}
	}else{
		$array = array("id"=>$id,"identifier"=>$id,"form_type"=>$type,"content"=>$content);
		if($attr && is_string($attr)){
			parse_str($attr,$list);
			if($list){
				$attr = $list;
			}
		}
		if($attr && is_array($attr)){
			$array = array_merge($attr,$array);
		}
	}
	$rs = $app->lib('form')->format($array);
	if($return == 'array'){
		return $rs;
	}
	return $rs['html'];
}

/**
 * 基于字段管理生成表单项
**/
function form_fields($identifer='',$id='',$content='',$return='')
{
	if(!$identifer){
		return false;
	}
	if(!$id){
		$id = $identifer;
	}
	$rs = $GLOBALS['app']->model('fields')->get_one($identifer,'identifier');
	if(!$rs){
		return false;
	}
	$rs['identifier'] = $id;
	$rs['content'] = $content;
	$rs = $GLOBALS['app']->lib('form')->format($rs);
	if($return == 'array') return $rs;
	return $rs['html'];
}

/**
 * 生成HTML表单控制，此项机制与form_edit类似
 * @参数 type，HTML表单类型，具体支持请查看form里的配置
 * @参数 id，表单名称
 * @参数 attr，各种属性，多种属性连接模式为：type=1&ok=2&upload=1
 * @参数 content，表单默认值内容
**/
function form_html($type='text',$id='phpok',$attr='',$content='')
{
	$array = array("identifier"=>$id,"form_type"=>$type,"content"=>$content);
	if($attr && is_string($attr)){
		parse_str($attr,$list);
		if($list){
			$array = array_merge($list,$array);
		}
	}
	return $GLOBALS['app']->lib('form')->format($array);
}

/**
 * 取得授权时间
**/
function license_date()
{
	if($GLOBALS['app']->license_site == '.phpok.com') return '2005-'.date("Y",$GLOBALS['app']->time);
	$date_start = substr($GLOBALS['app']->license_date,0,4);
	$date_end = date("Y",$GLOBALS['app']->time);
	if($date_start >= $date_end) return $date_end;
	return $date_start."-".$date_end;
}


/**
 * PHPOK日志存储，可用于调试
**/
function phpok_log($info='',$file='')
{
	global $app;
	if(!$info){
		$info = '没有提示内容';
	}
	$mask = false;
	if(is_array($info) || is_object($info)){
		$info = print_r($info,true);
		$mask = true;
	}
	$info = trim($info);
	$app->model('log')->save($info,$mask,$file);
	return true;
}

/**
 * 邮箱合法性验证
**/
function phpok_check_email($email)
{
	return $GLOBALS['app']->lib('common')->email_check($email);
}

/**
 * 获取用户组信息
**/
function user_group($gid)
{
	return $GLOBALS['app']->model('usergroup')->get_one($gid);
}

/**
 * 详细页分页
**/
function pageurl($pageurl,$pageid=1)
{
	if($pageid < 2) return $pageurl;
	$page = $GLOBALS['app']->config['pageid'] ? $GLOBALS['app']->config['pageid'] : 'pageid';
	$pageurl .= strpos($pageurl,'?') === false ? '?'.$page.'='.$pageid : '&'.$page.'='.$pageid;
	return $pageurl;
}


/**
 * 自定义表单中涉及到的内容获取
**/
function opt_rslist($type='default',$group_id=0,$info='')
{
	//当类型为默认时
	if($type == 'default' && $info){
		$list = explode("\n",$info);
		$rslist = array();
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			if(strpos($value,':') !== false){
				$tmp2 = explode(":",$value);
				if(!$tmp2[1]){
					$tmp2[1] = $tmp2[0];
				}
				$rslist[] = array('val'=>$tmp2[0],'title'=>$tmp2[1]);
			}else{
				$rslist[] = array('val'=>trim($value),'title'=>trim($value));
			}
		}
		return $rslist;
	}
	if($type == "opt"){
		return $GLOBALS['app']->model('opt')->opt_all("group_id=".$group_id);
	}
	if($type == 'project'){
		$tmplist = $GLOBALS['app']->model('project')->project_sonlist($group_id);
		if(!$tmplist) return false;
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}
	//读主题列表信息
	if($type == 'title'){
		$tmplist = $GLOBALS['app']->model("list")->title_list($group_id);
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title'],'cate_id'=>$value['cate_id'],'catename'=>$value['catename']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}
	//读子分类信息
	if($type == 'cate'){
		$tmplist = $GLOBALS['app']->model('cate')->catelist_sonlist($group_id,false);
		if(!$tmplist) return false;
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array("val"=>$value['id'],"title"=>$value['title'],'parent_id'=>$value['parent_id']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}
	if($type == 'user'){
		if($group_id == 'grouplist'){
			$tmplist = $GLOBALS['app']->model('usergroup')->get_all('status=1');
			if(!$tmplist){
				return false;
			}
			$rslist = array();
			foreach($tmplist as $key=>$value){
				$tmp = array("val"=>$value['id'],"title"=>$value['title']);
				$rslist[] = $tmp;
			}
			return $rslist;
		}
	}
	if($type == 'gateway'){
		if($group_id == 'express'){
			$tmplist = $GLOBALS['app']->model('express')->get_all();
		}else{
			$tmplist = $GLOBALS['app']->model('gateway')->all($group_id);//其他网关参数
		}
		if(!$tmplist){
			return false;
		}
		$rslist = array();
		foreach($tmplist as $key=>$value){
			$tmp = array('val'=>$value['id'],'title'=>$value['title']);
			$rslist[] = $tmp;
		}
		return $rslist;
	}
	return false;
}


function add_js($js)
{
	return $GLOBALS['app']->url("js","ext","js=".rawurlencode($js));
}

function phpok_call_api_url($phpok,$param='',$tpl='')
{
	global $app;
	$api_code = $app->model('config')->get_one('api_code',$app->site['id']);
	if(!$api_code || !$phpok){
		return false;
	}
	$ext = $tpl ? 'tpl='.rawurlencode($tpl) : '';
	$info = array('id'=>$phpok,'param'=>$param);
	$app->lib('token')->keyid($api_code);
	$token = $app->lib('token')->encode($info);
	if($ext){
		$ext .= "&";
	}
	$ext .= "token=".rawurlencode($token);
	return api_url('index','phpok',$ext,true);
}

function token_userid()
{
	$info = array();
	if($_SESSION['user_id']){
		return $GLOBALS['app']->model('user')->token_create($_SESSION['user_id']);
	}
	return $GLOBALS['app']->lib('token')->encode($info);
}

/**
 * 视频链接自动解析成真实地址
 * @参数 $url
**/
function phpok_video_url($url,$type="html")
{
	if(!$url){
		return false;
	}
	global $app;
	return $app->lib('video_url')->format($url,$type);
}

function phpok_sitelist($notin=false)
{
	global $app;
	$sitelist = $app->model('site')->get_all_site();
	if(!$sitelist){
		return false;
	}
	$langlist = $app->model('lang')->get_list();
	foreach($sitelist as $key=>$value){
		if(!$value['status']){
			unset($sitelist[$key]);
			continue;
		}
		if($value['id'] == $app->site['id'] && $notin){
			unset($sitelist[$key]);
			continue;
		}
		if($langlist && $langlist[$value['lang']]){
			$value['lang_title'] = $langlist[$value['lang']];
			$sitelist[$key] = $value;
		}
	}
	return $sitelist;
}

/**
 * 智能使用CDN
**/
function phpok_cdn()
{
	global $app;
	if(!$app->config['cdn']){
		return 'static/cdn/';
	}
	$config = $app->config['cdn'];
	$time = $config['time'] ? $config['time'] : 3600;
	if(!$config['status'] || !$config['server']){
		$folder = $config['folder'] ? $config['folder'] : 'static/cdn/';
		if(substr($folder,-1) != '/'){
			$folder .= '/';
		}
		return $folder;
	}
	$info = $app->lib('file')->cat($app->dir_data.'phpok-cdn-status.php');
	$remote_check_status = false;
	if(!$info){
		$remote_check_status = true;
	}
	$use_cdn = false;
	if($info && !$remote_check_status){
		$tmp = explode("|",$info);
		if($tmp[1] == 'false'){
			$folder = $config['folder'] ? $config['folder'] : 'static/cdn/';
			if(substr($folder,-1) != '/'){
				$folder .= '/';
			}
			return $folder;
		}
		if(($tmp[0] + $time) > $app->time){
			$remote_check_status = true;
		}else{
			$use_cdn = true;
		}
	}
	if($remote_check_status){
		$url = $config['https'] ? 'https://' : 'http://';
		$url.= $config['server'];
		if($config['ip']){
			$app->lib('curl')->host_ip($config['ip']);
		}
		$app->lib('curl')->connect_timeout(2);
		$content = $app->lib('curl')->get_content($url);
		$http_code = $app->lib('curl')->http_code();
		if($content && $http_code == '200'){
			$info = $app->time.'|true';
			$use_cdn = true;
		}else{
			$info = $app->time.'|false';
		}
		$app->lib('file')->vi($info,$app->dir_data.'phpok-cdn-status.php');
	}
	if($use_cdn){
		$url = $config['https'] ? 'https://' : 'http://';
		$url.= $config['server'];
		if(substr($url,-1) != '/'){
			$url .= "/";
		}
		return $url;
	}
	$folder = $config['folder'] ? $config['folder'] : 'static/cdn/';
	if(substr($folder,-1) != '/'){
		$folder .= '/';
	}
	return $folder;
}

function phpok_post_save($data,$pid=0)
{
	global $app;
	if(!$data || !is_array($data) || !$pid){
		return false;
	}
	if(is_numeric($pid)){
		$project_rs = $app->model('project')->get_one($pid,false);
		if(!$project_rs || !$project_rs['status'] || !$project_rs['module']){
			return false;
		}
	}else{
		$project_rs = $pid;
	}
	$module = $app->model('module')->get_one($project_rs['module']);
	if(!$module || !$module['status']){
		return false;
	}
	if($module['mtype']){
		$array = array('site_id'=>$project_rs['site_id']);
		$tid = $data['id'];
		if($tid){
			$rs = $app->model('list')->single_one($tid,$module);
			$array['id'] = $rs['id'];
		}
		$array['project_id'] = $project_rs['id'];
		if($data['cate_id']){
			$array['cate_id'] = $data['cate_id'];
		}
		if(isset($data['status'])){
			$array['status'] = $data['status'];
		}
		if(isset($data['hidden'])){
			$array['hidden'] = $data['hidden'];
		}
		if(isset($data['sort'])){
			$array['sort'] = $data['sort'];
		}
		$flist = $app->model('module')->fields_all($module['id']);
		if($flist){
			foreach($flist as $key=>$value){
				if(isset($data[$value['identifier']])){
					$array[$value['identifier']] = $data[$value['identifier']];
				}
			}
		}
		return $app->model("list")->single_save($array,$project_rs['module']);
	}
	$flist = $app->db->list_fields('list');
	unset($flist['id']);
	$array = array();
	foreach($flist as $key=>$value){
		if(isset($data[$value])){
			$array[$value] = $data[$value];
		}
	}
	$array['site_id'] = $project_rs['site_id'];
	$array["project_id"] = $project_rs["id"];
	$array["module_id"] = $project_rs["module"];
	if(!$data['dateline']){
		$array['dateline'] = $app->time;
	}
	$is_insert = true;
	if($data['id']){
		$app->model('list')->save($array,$data['id']);
		$insert_id = $data['id'];
		$app->model('list')->list_cate_clear($insert_id);
		$is_insert = false;
	}else{
		$insert_id = $app->model('list')->save($array);
		if(!$insert_id){
			return false;
		}
	}
	if($array['cate_id']){
		$ext_cate = $data['_cate'] ? $data['_cate'] : array($array['cate_id']);
		$app->model('list')->save_ext_cate($insert_id,$ext_cate);
	}
	if($project_rs['is_biz']){
		$biz = array('price'=>$data['price']);
		$biz['currency_id'] = $data['currency_id'];
		if(!$biz['currency_id']){
			$biz['currency_id'] = $project_rs['currency_id'];
		}
 		$biz['weight'] = $data['weight'];
 		$biz['volume'] = $data['volume'];
 		$biz['unit'] = $data['unit'];
 		$biz['id'] = $insert_id;
 		$biz['is_virtual'] = $data['is_virtual'] ? $data['is_virtual'] : 0;
 		$app->model('list')->biz_save($biz);
	}
	//Tag标签的同步
	$app->model('tag')->update_tag($array['tag'],$insert_id);
	//存储扩展字段
	$ext_list = $app->model('module')->fields_all($project_rs["module"]);
	if(!$ext_list){
		$ext_list = array();
	}
	$tmplist = array();
	if($is_insert){
		$tmplist["id"] = $insert_id;
	}
	$tmplist["site_id"] = $project_rs["site_id"];
	$tmplist["project_id"] = $project_rs["id"];
	$tmplist["cate_id"] = $array["cate_id"];
	foreach($ext_list as $key=>$value){
		if(isset($data[$value['identifier']])){
			$val = $data[$value['identifier']];
			$tmplist[$value["identifier"]] = $val;
		}
	}
	if($is_insert){
		$app->model('list')->save_ext($tmplist,$project_rs["module"]);
	}else{
		$app->model('list')->update_ext($tmplist,$project_rs['module'],$insert_id);
	}
	return $insert_id;
}
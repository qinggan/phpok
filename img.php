<?php
/**
 * 图片信息，用于展示各种规格的图片
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月20日
**/

/**
 * 定义应用的根目录，如果程序出程，请将ROOT改为：define("ROOT","./");
**/
define("ROOT",str_replace("\\","/",dirname(__FILE__))."/");

/**
 * 定义缓存目录
**/
define('CACHE',ROOT.'_cache/');

/**
 * 定义框架目录
**/
define("FRAMEWORK",ROOT."framework/");


error_reporting(E_ALL ^ E_NOTICE);
function phpok_header($type='jpeg')
{
	$type = strtolower($type);
	if($type == 'jpeg'){
		$type = 'jpg';
	}
	header("Content-type: image/".$type);
}

function phpok_error($id)
{
	$file = 'images/error/'.$id.'.gif';
	if(!is_file($file)){
		$file = 'images/error/error.gif';
	}
	phpok_header('gif');
	echo file_get_contents($file);
	exit;
}

function phpok_decode($str)
{
	$data = str_replace(array('-','_'),array('+','/'),$str);
	$mod4 = strlen($data) % 4;
	if($mod4){
		$data .= substr('====', $mod4);
	}
	$info = base64_decode($data);
	return unserialize($info);
}

if(!isset($_GET['token'])){
	phpok_error('id');
}
$token = $_GET['token'];
if(!$token){
	phpok_error('error');
}
$rs = phpok_decode($token);
if(!$rs || !is_array($rs) || !$rs['url']){
	phpok_error('error');
}
$ext = strtolower(substr($rs['url'],-4));
if(!in_array($ext,array('.jpg','.gif','.png','jpeg'))){
	phpok_error('only');
}
$ext = str_replace('.','',$ext);
$cache_id = md5($token).'.'.$ext;
if(!is_file(ROOT.$rs['url'])){
	phpok_error('info');
}

if(filesize(ROOT.$rs['url']) >= (4*1024*1024)){
	phpok_error('max');
}
if(!is_file(CACHE.$cache_id) && !function_exists('imagecreate')){
	phpok_header($ext);
	echo file_get_contents(ROOT.$rs['url']);
	exit;
}
if(!is_file(CACHE.$cache_id) && function_exists('imagecreate')){
	include FRAMEWORK.'libs/gd.php';
	$gd = new gd_lib();
	$gd->isgd(true);
	$gd->filename(ROOT.$rs['url']);
	$gd->Filler($rs["bgcolor"]);
	if($rs["width"] && $rs["height"] && $rs["cut_type"]){
		$gd->SetCut(true);
	}else{
		$gd->SetCut(false);
	}
	$gd->SetWH($rs["width"],$rs["height"]);
	$gd->CopyRight($rs["mark_picture"],$rs["mark_position"],$rs["trans"]);
	if($rs["quality"]){
		$gd->Set('quality',$rs['quality']);
	}
	$gd->Create(ROOT.$rs['url'],$cache_id,CACHE);
}
phpok_header($ext);
echo file_get_contents(CACHE.$cache_id);
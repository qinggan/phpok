<?php
/***********************************************************
	Filename: update.php
	Note	: PHPOK升级包
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月30日
***********************************************************/
error_reporting(E_ALL ^ E_NOTICE);
//旧版数据配置
/*
$oldconfig["host"] = "localhost";
$oldconfig["port"] = "3306";
$oldconfig["user"] = "root";
$oldconfig["pass"] = "root";
$oldconfig["data"] = "oxy_old";
$oldconfig["prefix"] = "oxy_";

//新版数据配置
$config["host"] = "localhost";
$config["port"] = "3306";
$config["user"] = "root";
$config["pass"] = "root";
$config["data"] = "oxy";
$config["prefix"] = "qinggan_";
*/

$psize = 20;
$pageid = intval($_GET['pageid']);
if(!$pageid) $pageid = 1;
$offset = ($pageid-1) * $psize;

//显示内容
//有url时就带跳转
//time为当有url时有效
function msg($msg,$url='',$time=2)
{
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="expires" content="wed, 26 feb 1997 08:21:57 gmt"> 
	<title>PHPOK升级程序</title>
	<style type="text/css">
	body{font-family:'微软雅黑','宋体','Arial';font-size:14px;}
	body div{line-height:170%;}
	a{text-decoration:none;color:darkblue;}
	a:hover{color:red;}
	h3{font-size:16px;line-height:20px;}
	.center{text-align:center;}
	</style>
</head>
<body>
EOT;
if($url)
{
	echo '<div align="center"><a href="'.$url.'">如果浏览器没有跳转，请点这里</a></div>';
	echo <<<EOT
	<script type="text/javascript">
	function gourl()
	{
		window.location.href = '{$url}';
	}
	setTimeout(gourl,{$time}*1000);
	</script>
EOT;
}
echo <<<EOT
<div style="width:700px;margin:0 auto;">
<div class="center"><h3>PHPOK升级程序</h3></div>
EOT;
echo $msg;
echo <<<EOT
</div>
</body>
</html>
EOT;
exit;
}


$type = $_GET['type'];
if(!$type)
{
	$html .= '<div style="color:red;border:1px solid #ccc;margin:10px auto;padding:10px;">升级前请做好相应的备份准备，本次升级不允许中断，为了保证您数据的安全，建议复制到本地后再升级！<br />跨版本升级成功后，请修改相应的模板写法，以保证在新版本中能正常使用！<br />P3升级到P4前，会重构P4里的所有数据！</div>';
	$html .= '<ol>';
	$html .= '<li><a href="update.php?type=phpok3">PHPOK3.x 升级到 PHPOK4.1</a></li>';
	$html .= '<li><a href="update.php?type=phpok4">PHPOK4.0.x 升级到 PHPOK4.1</a></li>';
	$html .= '</ol>';
	msg($html);
}

include_once('framework/engine/db/mysqli.php');
$odb = new db_mysqli($oldconfig);
$ndb = new db_mysqli($config);

if($type == "res")
{
	//更新附件
	$sql = "SELECT * FROM ".$odb->prefix."upfiles ORDER BY id ASC LIMIT ".$offset.",".$psize;
	$rslist = $odb->get_all($sql);
	if(!$rslist)
	{
		msg('<div>附件数据更新成功，<a href="update.php">点这里返回</a>！</div>');
		exit;
	}
	$html = '<div>正在更新附件数据：'.$offset.' - '.($offset+$psize).'</div>';
	foreach($rslist AS $key=>$value)
	{
		$sql = "SELECT * FROM ".$odb->prefix."upfiles_gd WHERE pid='".$value['id']."'";
		//删除旧图片
		$olist = $odb->get_all($sql);
		if($olist)
		{
			foreach($olist AS $k=>$v)
			{
				if($v['filename'] && is_file($v['filename']))
				{
					@unlink($v['filename']);
				}
			}
			//$sql = "DELETE"
		}
		$sql = "SELECT * FROM ".$ndb->prefix."res WHERE filename='".$value['filename']."' LIMIT 1";
		$rs = $ndb->get_one($sql);
		if(!$rs)
		{
			//存储附件数据
			$tmpname = basename($value['filename']);
			$folder = str_replace($tmpname,'',$value['filename']);
			if(substr($folder,-1) != '/') $folder .= '/';
			$data = array('cate_id'=>7,'folder'=>$folder,'name'=>$tmpname,'ext'=>$value['ftype']);
			$data['filename'] = $value['filename'];
			$data['ico'] = $value['thumb'];
			$data['addtime'] = $value['postdate'] ? $value['postdate'] : time();
			$data['title'] = $value['title'] ? str_replace('.'.$ext,'',$value['title']) : $tmpname;
			$data['note'] = $value['id'];
			$img_ext = getimagesize($data['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$data['attr'] = serialize($my_ext);
			$ndb->insert_array($data,'res');
			$html .= '<div>转换附件：'.$value['title'].'</div>';
		}
		else
		{
			$html .= '<div style="color:red;">附件已存在：'.$value['title'].'</div>';
		}
	}
	$url = "update.php?type=res&pageid=".($pageid+1);
	msg($html,$url,3);
}

if($type == "list")
{
	//分类处理
	$catelist = array(56=>74,57=>75,58=>76);
	$pid = intval($_GET["pid"]);
	$nid = intval($_GET["nid"]);
	$url = "update.php?type=list&pid=".$pid."&nid=".$nid;
	if(!$nid || !$pid)
	{
		msg("参数传递不正确！");
	}
	$prs = $ndb->get_one("SELECT * FROM ".$ndb->prefix."project WHERE id='".$nid."'");
	if(!$prs)
	{
		msg("网站项目不存在");
	}
	$html = '<div>正在更新数据：'.$offset.' - '.($offset+$psize).'</div>';
	//取得新项目的扩展字段
	$flist = $ndb->get_all("SELECT * FROM ".$ndb->prefix."module_fields WHERE module_id='".$prs['module']."'","identifier");
	if(!$flist) $flist = array();
	$sql = "SELECT * FROM ".$odb->prefix."module WHERE id='".$pid."'";
	$old_rs = $odb->get_one($sql);
	//内容
	$sql = "SELECT * FROM ".$odb->prefix."list WHERE module_id='".$pid."' ORDER BY id ASC LIMIT ".$offset.",".$psize;
	$rslist = $odb->get_all($sql);
	if(!$rslist)
	{
		msg("项目：".$old_rs['title']."更新完成！<a href='update.php'>点这里返回</a>");
	}
	//取得项目的扩展字段信息
	$of_list = $odb->get_all("SELECT * FROM ".$odb->prefix."module_fields WHERE module_id='".$pid."' ORDER BY id ASC","identifier");
	//循环更新内容
	foreach($rslist AS $key=>$value)
	{
		//检查内容是否已存在
		$sql = "SELECT * FROM ".$ndb->prefix."list WHERE title='".$value["title"]."' AND dateline='".$value["post_date"]."'";
		$chk_rs = $ndb->get_one($sql);
		if($chk_rs)
		{
			$html .= '<div class="darkred">主题：'.$value['title']." 已经更新过</div>";
			continue;
		}
		//获取内容扩展
		$sql = "SELECT * FROM ".$odb->prefix."list_ext WHERE id='".$value['id']."'";
		$extlist = $odb->get_all($sql);
		if($extlist)
		{
			foreach($extlist AS $k=>$v)
			{
				$value[$v["field"]] = $v['val'];
			}
		}
		$sql = "SELECT * FROM ".$odb->prefix."list_c WHERE id='".$value['id']."'";
		$extclist = $odb->get_all($sql);
		if($extclist)
		{
			foreach($extclist AS $k=>$v)
			{
				$value[$v['field']] = $v['val'];
			}
		}
		$cate_id = $catelist[$value["cate_id"]];
		$data = array("title"=>$value['title'],"cate_id"=>$cate_id,'module_id'=>$prs['module'],"project_id"=>$nid,"site_id"=>1);
		$data["status"] = $value["status"];
		$data['dateline'] = $value['post_date'];
		$data['hits'] = $value['hits'];
		$insert_id = $ndb->insert_array($data,"list");
		//如果写入失败，跳过，进行下一个循环
		if(!$insert_id)
		{
			$html .= '<div class="red">主题：'.$value['title']." 写入失败</div>";
			continue;
		}
		$ext = array("id"=>$insert_id,"site_id"=>1,"project_id"=>$nid,"cate_id"=>$cate_id);
		foreach($flist AS $k=>$v)
		{
			if($value[$k] && $of_list && $of_list[$k])
			{
				if($of_list[$k]["input"] == "img")
				{
					$sql = "SELECT id FROM ".$ndb->prefix."res WHERE note IN(".$value[$k].")";
					$vlist = $ndb->get_all($sql,"id");
					if($vlist)
					{
						$vlist = array_keys($vlist);
						$ext[$k] = implode(",",$vlist);
					}
				}
				else
				{
					$ext[$k] = $value[$k];
				}
			}
		}
		//写入内容
		$ndb->insert_array($ext,"list_".$prs['module']);
		$html .= '<div>转化数据：'.$value['title']."</div>";
	}
	$url .= "&pageid=".($pageid+1);
	msg($html,$url,3);
}

<?php
/**
 * 生成副本
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2022年8月2日
**/

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

function array2str($rs)
{
	$tlist = array();
	foreach($rs as $key=>$value){
		if($key == 'filename'){
			$tmpname = $value;
		}else{
			$len = $key == 'ext' ? 10 : 32;
			if($key == 'mdate' || $key == 'cdate' || $key == 'adate'){
				$len = 16;
			}
			if($key == 'filesize'){
				$len = 10;
			}
			$tmpname = str_pad($value,$len);
		}
		$tlist[$key] = $tmpname;
	}
	return implode(" | ",$tlist);
}

function scan(&$list,$dir=''){
	if(!$dir){
		$dir = '.';
	}
	$basename = basename($dir);
	$not_include = array('res','_cache','tpl_admin','tpl_www','log','log-stat','uniapp','update','uniapp-web','.svn','.git');
	if(in_array($basename,$not_include)){
		return false;
	}
	$dirArr = scandir($dir.'/');
	foreach($dirArr as $key=>$value){
		if($value == '.' || $value == '..' || $value == '.svn' || $value == '.git'){
			continue;
		}
		if($dir == '.'){
			$dirname = $value;
		}else{
			$dirname = $dir.'/'.$value;
		}
		echo $dirname."\n";
		if(is_dir($dirname)){
			scan($list,$dirname);
			continue;
		}
		$tmp = basename($dirname);
		if($tmp == '_table.php'){
			continue;
		}
		$rs = file2array($dirname);
		$list[] = $rs;
	}
}

function file2array($file)
{
	$f2 = $file;
	$filesize = filesize($file);
	if($filesize>=1024*300){
		$content = file_get_contents($file,null,null,0,300);
		$hash = md5($f2.'-'.$content);
	}else{
		$hash = md5_file($file);
	}
	$mdate = filemtime($file);
	$rs = array();
	$rs['md5'] = md5($f2); //文件路径MD5
	$rs['hash'] = $hash;
	$rs['mdate'] = $mdate;
	$rs['cdate'] = filectime($file);
	$rs['adate'] = fileatime($file);
	$rs['filesize'] = $filesize;
	$rs['ext'] = file_ext($file);
	$rs['filename'] = $f2;
	return $rs;
}

function file_ext($file)
{
	if(function_exists('pathinfo')){
		$t = pathinfo($file,PATHINFO_EXTENSION);
		if($t){
			return $t;
		}
	}
	$file = basename($file);
	$e = explode(".",$file);
	if(!$e[1]){
		return 'txt';
	}
	$len = count($e);
	$ext = $e[($len-1)];
	return $ext;
}

$info = array();
$info['md5'] = 'MD5';
$info['hash'] = 'Hash';
$info['mdate'] = 'Modify Datetime';
$info['cdate'] = 'Create Datetime';
$info['adate'] = 'Read Datetime';
$info['filesize'] = 'Filesize';
$info['ext'] = 'Ext';
$info['filename'] = 'File';
$str = array2str($info);
$handle = fopen('table.txt','wb');
fwrite($handle,$str."\n");

$list = array();
scan($list,'.');
foreach($list as $key=>$value){
	$str = array2str($value);
	fwrite($handle,$str."\n");
}
fclose($handle);
echo "OK\n";
<?php
/**
 * 删除无效旧的附件
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 6.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2023年3月3日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
$list = array();
$this->lib('file')->deep_ls($this->dir_root.'res',$list);
foreach($list as $key=>$value){
	$bname = basename($value);
	$tmp1 = substr($bname,0,1);
	$tmp2 = substr($bname,0,6);
	$tmp3 = substr($bname,0,5);
	if($tmp1 == '_' || $tmp2 == 'thumb_' || $tmp3 == 'auto_'){
		$this->lib('file')->rm($value);
		unset($list[$key]);
	}
}
echo "<pre>".print_r($list,true)."</pre>";
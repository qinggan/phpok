<?php
/**
 * 阿里云OSS
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月18日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
$this->addjs($this->dir_webroot.'static/aliyun/es6-promise.min.js');
$this->addjs($this->dir_webroot.'static/tencentyun/cos-js-sdk-v5.js');
$this->addjs($this->dir_webroot.'static/tencentyun/uploadcos.js');
$f = $this->tpl->val('_rs');
if($f && $f['cate_id']){
	$cate_rs = $this->model('rescate')->get_one($f['cate_id']);
	$folder = $cate_rs['root'];
	if($cate_rs['folder']){
		$folder .= date($cate_rs['folder'],$this->time);
	}
	$this->assign('folder',$folder);
}
$this->assign('gateway_rs',$rs);
$tplfile = $this->dir_gateway.'object-storage/tencentcos/btn_www.html';
return $this->fetch($tplfile,'abs-file');
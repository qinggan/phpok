<?php
/**
 * 获取图片的后台缩略图
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年1月20日
**/
$filename = $this->get('filename');
if(!$filename){
	return false;
}
$filext = $this->get('filext');
if(!$filext){
	$filext = 'unknown';
}
$ico = 'images/filetype-large/'.$filext.'.jpg';
$extlist = array('png','gif','jpeg','jpg');
//裁剪缩略图
if(in_array($filext,$extlist)){
	$ico = $filename.'?imageView2/1/w/200';
}else{
	if(!is_file($this->dir_root.$ico)){
		$ico = 'images/filetype-large/unknown.jpg';
	}
}
return $ico;
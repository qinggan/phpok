<?php
/**
 * 视频解析
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月24日
**/
$video = '';
if($rs['video'] && (substr($rs['video'],0,7) == 'http://' || substr($rs['video'],0,8) == 'https://')){
	$tmp = parse_url($rs['video']);
	if(strpos($tmp['host'],'youku.com') !== false){
		$filename = basename($tmp['path']);
		$filename = substr($filename,3,-5);
		$video = '//player.youku.com/embed/'.$filename;
	}
}
$video = $rs['video'];

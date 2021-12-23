<?php
/**
 * 直播插件<公共函数>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

function url_auth($url='',$prikey='',$expire=3600)
{
	if(!$url || !$prikey){
		return false;
	}
	$tmp = parse_url($url);
	$link = $tmp['scheme'].'://'.$tmp['host'].$tmp['path'];
	$mytime = time();
	$expire_time = $mytime + $expire;
	$code = $tmp['path'].'-'.$expire_time.'-0-0-'.$prikey;
	$link .= "?auth_key=".$expire_time.'-0-0-'.md5($code);
	return $link;
}

if(!function_exists('sec2time')){
	function sec2time($seconds=0)
	{
		if($seconds<60){
			if($seconds<10){
				return '00:00:0'.round($seconds);
			}
			return '00:00:'.round($seconds);
		}
		if($seconds >= 60 && $seconds < 3600){
			//执行分
			$min = intval($seconds/60);
			$sec = $seconds%60;
			return '00:'.($min<10 ? '0'.$min : $min).':'.($sec<10 ? '0'.$sec : $sec);
		}
		$hour = intval($seconds/3600);
		$info = $hour < 10 ? '0'.$hour : $hour;
		$secs = $seconds/3600;
		if($secs < 60){
			if($secs<10){
				return $info.':00:0'.round($secs);
			}
			return $info.':00:'.round($secs);
		}
		if($secs >= 60 && $secs < 3600){
			//执行分
			$min = intval($secs/60);
			$sec = $secs%60;
			return $info.':'.($min<10 ? '0'.$min : $min).':'.($sec<10 ? '0'.round($sec) : round($sec));
		}
		return $seconds;
	}
}

/**
 * 获取ffmpeg的图片，仅限宽是32倍数和高是2倍数的图片
**/
function ffmpeg_mp3_bg($rs='',$waiting='')
{
	global $app;
	if(!$rs || !$waiting){
		return $app->dir_data.'bg.jpg';
	}
	$thumb = $app->dir_data.'bg.jpg';
	if(is_string($rs) && file_exists($app->dir_root.$rs)){
		list($width, $height, $type, $attr) = getimagesize($app->dir_root.$rs);
		if($width%32 == '' && $height%2 == ''){
			$thumb = $app->dir_root.$rs;
			return $thumb;
		}
	}
	if(is_numeric($rs)){
		$rs = $app->model('res')->get_one($rs,true);
	}
	if(is_array($rs)){
		$video = ($rs['gd'] && $rs['gd']['video']) ? $rs['gd']['video'] : $rs['filename'];
		list($width, $height, $type, $attr) = getimagesize($app->dir_root.$video);
		if($video && file_exists($app->dir_root.$video) && $width%32 == '' && $height%2 ==''){
			$thumb = $app->dir_root.$video;
			return $thumb;
		}
	}
	if(is_array($waiting)){
		$video = ($waiting['gd'] && $waiting['gd']['video']) ? $waiting['gd']['video'] : $waiting['filename'];
		list($width, $height, $type, $attr) = getimagesize($app->dir_root.$video);
		if($video && file_exists($app->dir_root.$video) && $width%32 == '' && $height%2 ==''){
			$thumb = $app->dir_root.$video;
			return $thumb;
		}
	}
	if(is_numeric($waiting)){
		$rs = $app->model('res')->get_one($waiting,true);
		if($rs){
			$video = ($rs['gd'] && $rs['gd']['video']) ? $rs['gd']['video'] : $rs['filename'];
			list($width, $height, $type, $attr) = getimagesize($app->dir_root.$video);
			if($video && file_exists($app->dir_root.$video) && $width%32 == '' && $height%2 ==''){
				$thumb = $app->dir_root.$video;
				return $thumb;
			}
		}
	}
	return $thumb;
}
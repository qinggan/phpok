<?php
/**
 * 视频解析类
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年11月24日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class video_url_lib extends _init_lib
{
	private $show_type = 'html';
	public function __construct()
	{
		//
	}

	public function show_type($type='')
	{
		if($type){
			$this->show_type = $type;
		}
		return $this->show_type;
	}
	
	public function format($url,$return_type='',$bgimg="")
	{
		if(!$url){
			return false;
		}
		$this->show_type($return_type);
		if(substr($url,0,7) != 'http://' && substr($url,0,8) != 'https://'){
			return $this->_show($url,'h5',$bgimg);
		}
		$tmp = parse_url($url);
		if(strpos($tmp['host'],'youku.com') !== false){
			$url = $this->_youku($tmp);
			return $this->_show($url,'iframe');
		}
		if(strpos($tmp['host'],'youtube.com') !== false){
			$url = $this->_youtube($tmp);
			return $this->_show($url,'iframe');
		}
		if(strpos($tmp['host'],'qq.com') !== false){
			$url = $this->_qq($tmp);
			return $this->_show($url,'iframe');
		}
		if(strpos($tmp['host'],'bilibili.com') != false){
			$url = $this->_bilibili($tmp);
			return $this->_show($url,'iframe');
		}
		return $this->_show($url,'iframe');
	}

	private function _bilibili($rs)
	{
		$filename = basename($rs['path']);
		$video = '//player.bilibili.com/player.html?bvid='.$filename;
		return $video;
	}

	private function _youku($rs)
	{
		$filename = basename($rs['path']);
		$filename = substr($filename,3,-5);
		return '//player.youku.com/embed/'.$filename;
	}

	private function _youtube($rs)
	{
		if(!$rs['query']){
			return false;
		}
		parse_str($rs['query'],$tmparray);
		return '//www.youtube.com/embed/'.$tmparray['v'];
	}

	private function _qq($rs)
	{
		$filename = basename($rs['path']);
		$filename = substr($filename,0,-5);
		$video = '//'.$rs['host'].'/txp/iframe/player.html?vid='.$filename;
		return $video;
	}

	public function _show($url,$type='h5',$bgimg="")
	{
		if($this->show_type == 'array'){
			return array('type'=>$type,'url'=>$url);
		}
		if($type == 'iframe'){
			$html = '<iframe style="width:100%;height:100%;border:0;" frameborder="0" src="'.$url.'" allowFullScreen="true"></iframe>';
			return $html;
		}
		$html  = '<video type="video/mp4" src="'.$url.'" controls="controls" style="width:100%;height:100%;border:0;"';
		if($bgimg){
			$html .= " poster='".$bgimg."'";
		}
		$html .= '></video>';
		return $html;
	}
}

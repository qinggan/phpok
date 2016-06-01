<?php
/*****************************************************************************************
	文件： {phpok}/libs/ubb.php
	备注： UBB类，暂时仅支持UBB转HTML
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月03日 11时47分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ubb_lib
{
	public function __construct()
	{
		//
	}

	public function to_html($info,$nl2br=true)
	{
		if(is_array($info))
		{
			foreach($info AS $key=>$value)
			{
				$value = $this->to_html($value,$nl2br);
				$info[$key] = $value;
			}
			return $info;
		}
		$info=trim($info);
		if($nl2br)
		{
			$info=str_replace("\n","<br />",$info);
		}
		$info=preg_replace("/\[hr\]/is","<hr />",$info);
		$info=preg_replace("/\[separator\]/is","<br/>",$info);
		$info=preg_replace("/\[h([1-6])\](.+?)\[\/h([1-6])\]/is","<h\\1>\\2</h\\1>",$info);
		$info=preg_replace("/\[center\](.+?)\[\/center\]/is","<div style='text-align:center'>\\1</div>",$info);
		$info=preg_replace("/\[url\](.+?)\[\/url\]/is","<a href=\"\\1\" target='_blank'>\\1</a>",$info);
		$info=preg_replace("/\[url=(http:\/\/.+?)\](.+?)\[\/url\]/is","<a href='\\1' target='_blank' title='\\2'>\\2</a>",$info);
		$info=preg_replace("/\[url=(.+?)\](.+?)\[\/url\]/is","<a href='\\1' title='\\2'>\\2</a>",$info);
		$info=preg_replace("/\[img\](.+?)\[\/img\]/is","<img src='\\1'>",$info);
		$info=preg_replace("/\[img\s(.+?)\](.+?)\[\/img\]/is","<img \\1 src='\\2'>",$info);
		$info=preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/is","<span style='color:\\1'>\\2</span>",$info);
		$info=preg_replace("/\[size=(.+?)\](.+?)\[\/size\]/is","<span style='font-size:\\1'>\\2</span>",$info);
		$info=preg_replace("/\[sup\](.+?)\[\/sup\]/is","<sup>\\1</sup>",$info);
		$info=preg_replace("/\[sub\](.+?)\[\/sub\]/is","<sub>\\1</sub>",$info);
		$info=preg_replace("/\[pre\](.+?)\[\/pre\]/is","<pre>\\1</pre>",$info);
		$info=preg_replace("/\[email\](.+?)\[\/email\]/is","<a href='mailto:\\1'>\\1</a>",$info);
		$info=preg_replace("/\[i\](.+?)\[\/i\]/is","<i>\\1</i>",$info);
		$info=preg_replace("/\[u\](.+?)\[\/u\]/is","<u>\\1</u>",$info);
		$info=preg_replace("/\[b\](.+?)\[\/b\]/is","<b>\\1</b>",$info);
		$info=preg_replace("/\[quote\](.+?)\[\/quote\]/is","<blockquote><div style='border:1px solid silver;background:#EFFFDF;color:#393939;padding:5px' >\\1</div></blockquote>", $info);
		$info = $this->_download($info);
		$info = $this->_title($info);
		$info = $this->_video($info);
		return $info;
	}

	//PHPOK扩展格式化附件下载
	private function _download($Text)
	{
		//UBB下载格式化
		preg_match_all("/\[download[:|：|=]*([0-9]*)\](.*)\[\/download\]/isU",$Text,$list);
		if($list && count($list)>0)
		{
			$dlist = '';
			foreach($list[0] AS $key=>$value)
			{
				$tmpid = $list[1][$key] ? $list[1][$key] : intval($list[2][$key]);
				if(!$tmpid)
				{
					continue;
				}
				if($list[2][$key] && intval($list[2][$key]) == $tmpid)
				{
					$resinfo = $GLOBALS['app']->model('res')->get_one($tmpid);
					if(!$resinfo)
					{
						continue;
					}
					$list[2][$key] = $resinfo['title'];
				}
				$string = '<a class="download" href="'.$GLOBALS['app']->url('download','','id='.$tmpid).'" title="'.strip_tags($list[2][$key]).'">'.$list[2][$key].'</a>';
				$Text = str_replace($value,$string,$Text);
			}
		}
		$list = '';
		//格式化旧版的UBB附件下载
		preg_match_all("/\[download[:|：]*([0-9]+)\]/isU",$Text,$list);
		if($list && count($list)>0)
		{
			foreach($list[0] AS $key=>$value)
			{
				if(!$list[1][$key])
				{
					continue;
				}
				$rs = $GLOBALS['app']->model('res')->get_one($list[1][$key]);
				if(!$rs)
				{
					continue;
				}
				$string = '<a class="download" href="'.$GLOBALS['app']->url('download','','id='.$rs['id']).'" title="'.$rs['title'].'">'.$rs['title'].'</a>';
				$Text = str_replace($value,$string,$Text);
			}
		}
		return $Text;
	}

	private function _title($Text)
	{
		//格式化主题列表
		$list = '';
		preg_match_all("/\[title[:|：]*([0-9a-zA-Z\_\-]+)\](.+)\[\/title\]/isU",$Text,$list);
		if($list && count($list)>0)
		{
			$dlist = '';
			foreach($list[0] AS $key=>$value)
			{
				if(!$list[1][$key] || !$list[2][$key]) continue;
				$string = '<a href="'.$GLOBALS['app']->url($list[1][$key]).'" title="'.strip_tags($list[2][$key]).'" target="_blank">'.$list[2][$key].'</a>';
				$Text = str_replace($value,$string,$Text);
			}
		}
		return $Text;
	}

	private function _video($Text)
	{
		//格式化视频链接地址，主要是格式化FLV格式的转换
		$list = false;
		preg_match_all("/<embed(.+)src=[\"|'](.+\.flv)[\"|'](.*)>/isU",$Text,$list);
		if($list && $list[2] && $list[0])
		{
			foreach($list[2] as $key=>$value)
			{
				$tmpurl = 'js/vcastr.swf?xml={vcastr}{channel}{item}{source}../'.$value.'{/source}{duration}{/duration}{title}{/title}{/item}{/channel}{config}{isAutoPlay}false{/isAutoPlay}{isLoadBegin}false{/isLoadBegin}{/config}{plugIns}{beginEndImagePlugIn}{url}js/image.swf{/url}{source}{/source}{type}beginend{/type}{scaletype}exactFil{/scaletype}{/beginEndImagePlugIn}{/plugIns}{/vcastr}';
				$string = '<embed'.$list[1][$key].' src="'.$tmpurl.'"'.$list[3][$key].'>';
				$Text = str_replace($list[0][$key],$string,$Text);
			}
		}
		return $Text;
	}
}

?>
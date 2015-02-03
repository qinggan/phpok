<?php
/***********************************************************
	Filename: plugins/identifier/api.php
	Note	: 标识串自动生成API接口
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_identifier extends phpok_plugin
{
	function __construct()
	{
		parent::plugin();
	}

	function fanyi()
	{
		$rs = $this->plugin_info();
		$q = $this->get("q");
		if(!$q) $this->json("youdao_not_info");
		$url = "http://fanyi.youdao.com/openapi.do?keyfrom=".$rs['param']["keyfrom"];
		$url.= "&key=".$rs['param']["keyid"]."&type=data&doctype=json&version=1.1&q=".rawurlencode($q);
		$content = $this->lib("html")->get_content($url);
		if(!$content) $this->json("youdao_get_error");
		$rs = $this->lib("json")->decode($content);
		if($rs["errorCode"])
		{
			$errlist = array(
				"20"=>$this->lang['youdao'][20],
				"30"=>$this->lang['youdao'][30],
				"40"=>$this->lang['youdao'][40],
				"50"=>$this->lang['youdao'][50]
			);
			$err = $errlist[$rs["errorCode"]];
			if(!$err) $err = $this->lang['youdao'][60];
			$this->json($err);
		}
		$content = strtolower($rs["translation"][0]);
		$content = $this->return_safe($content);
		$this->json($content,true);
	}

	function return_safe($content)
	{
		$safe_string = "abcdefghijklmnopqrstuvwxyz0123456789-_";
		$str_array = str_split($content);
		$safe_array = str_split($safe_string);
		$string = "";
		foreach($str_array AS $key=>$value)
		{
			if(in_array($value,$safe_array))
			{
				$string .= $value;
			}
			else
			{
				$string .= "-";
			}
		}
		//如果首字母为0-9的数字或非字母
		$array = array('0','1','2','3','4','5','6','7','8','9','-','_');
		$t1 = substr($string,0,1);
		if(in_array($t1,$array))
		{
			$string = $safe_array[rand(0,25)].$string;
		}
		return $string;
	}

	function pingyin()
	{
		//取得关键字
		$title = $this->get('title');
		if(!$title) $this->json('没有指定要翻译的内容');
		//取得拼音库
		$content = $this->py_share($title,false);
		$this->json($content,true);
	}

	function py()
	{
		//取得关键字
		$title = $this->get('title');
		if(!$title) $this->json('没有指定要翻译的内容');
		//取得拼音库
		$content = $this->py_share($title,true);
		$this->json($content,true);
	}

	function py_share($title,$is_first=false)
	{
		$rs = $this->plugin_info();
		if(!$title) $this->json('未指定要翻译的信息');
		if(!is_file($rs['path'].'libs/pingyin.php')) $this->json('出错，插件文件不完整，缺少文件：libs/pingyin.php');
		include_once($rs['path'].'libs/pingyin.php');
		$trans = new pingyin();
		$trans->path = $rs['path'].'libs/pingyin.qdb';
		if($is_first)
		{
			$trans->isFrist = true;
		}
		$title = $this->lib('string')->charset($title,'UTF-8','GBK');
		$info = $trans->ChineseToPinyin($title);
		$info = strtolower($info);
		$info = $this->return_safe($info);
		return $info;
	}
}
?>
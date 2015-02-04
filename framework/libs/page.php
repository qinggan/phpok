<?php
/***********************************************************
	Filename: page.php
	Note	: 分页类
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-07 19:20
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class page_lib
{
	var $half = 5;
	var $psize = 20;
	var $pageid = "pageid";
	var $num = 1;
	var $always = 0; //0 根据实际情况隐藏部分参数，1为一直显示

	# 累计
	var $add_up = "";
	var $home_str = "";
	var $next_str = "";
	var $prev_str = "";
	var $last_str = "";
	var $opt_str = "";

	function __construct()
	{
		$this->half = 5;
		$this->psize = 20;
		$this->pageid= "pageid";
		$this->num = 1;
		$this->total = 0;
	}

	function pageid($pageid)
	{
		$this->pageid = $pageid;
	}

	function num($num=1)
	{
		$this->num = $num;
	}

	function half($half=5)
	{
		$this->half = $half;
	}

	function psize($psize=20)
	{
		$this->psize = $psize;
	}

	function always($always=0)
	{
		$this->always = $always;
	}

	# 是否显示合计信息，如 15/30 等用于统计页面的信息
	function add_up($add_up="")
	{
		$this->add_up = $add_up;
	}


	# 显示首页
	function home_str($title)
	{
		$this->home_str = $title;
	}

	# 显示上一页
	function prev_str($title)
	{
		$this->prev_str = $title;
	}

	# 显示下一页
	function next_str($title)
	{
		$this->next_str = $title;
	}

	# 显示尾页
	function last_str($title)
	{
		$this->last_str = $title;
	}

	# 显示列表
	function opt_str($title)
	{
		$this->opt_str = $title;
	}

	function page($url,$total,$num=0,$psize=0)
	{
		if($num) $this->num = $num;
		if($psize) $this->psize = $psize;
		if(!$url || !$total) return false;
		$url = str_replace("&amp;","&",$url);
		if(strpos($url,"?") === false)
		{
			$url .= "?";
		}
		if(substr($url,-1) != "&" && substr($url,-1) != "?")
		{
			$url .= "&";
		}

		$total_page = intval($total/$this->psize);
		if($total % $this->psize)
		{
			$total_page ++;
		}
		if($this->num > $total_page) $this->num = $total_page;

		#字符替换，用于替换传递参数中可能需要用到的变量格式
		$old_array = array("(total)","(psize)","(total_page)","(num)","(pageid)","(url)");
		$new_array = array($total,$this->psize,$total_page,$this->num,$this->pageid,$url);
		# 判断是否显示统计
		$phpok_i = 0;
		$list = array();
		if($this->add_up)
		{
			$this->add_up = str_replace($old_array,$new_array,$this->add_up);
			$list[$phpok_i]["url"] = "";
			$list[$phpok_i]["title"] = $this->add_up;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "add";
			$phpok_i++;
		}
		#判断是否显示首页
		if($this->home_str)
		{
			$show = $this->always ? true : ($this->num > 1 ? true : false);
			if($show)
			{
				$list[$phpok_i]["url"] = substr($url,0,-1);
				$list[$phpok_i]["title"] = $this->home_str;
				$list[$phpok_i]["status"] = 0;
				$list[$phpok_i]["type"] = "home";
				if($this->num <= 1)
				{
					$list[$phpok_i]['nolink'] = true;
				}
				$phpok_i++;
			}
		}
		#判断是否显示上一页
		if($this->prev_str)
		{
			$show = $this->always ? true : ($this->num > 1 ? true : false);
			if($show)
			{
				$prev_url = $this->num > 1 ? $url.$this->pageid."=".($this->num - 1) : substr($url,0,-1);
				$list[$phpok_i]["url"] = $prev_url;
				$list[$phpok_i]["title"] = $this->prev_str;
				$list[$phpok_i]["status"] = 0;
				$list[$phpok_i]["type"] = "prev";
				if($this->num <= 1)
				{
					$list[$phpok_i]['nolink'] = true;
				}
				$phpok_i++;
			}
		}
		#判断是否显示数字列表
		if($this->half)
		{
			for($i = $this->num - $this->half , $i>0 || $i=0,$j = $this->num + $this->half , $j < $total_page || $j = $total_page ; $i < $j ; $i++)
			{
				$l = $i + 1;
				$list[$phpok_i]["url"] = $l == 1 ? substr($url,0,-1) : $url.$this->pageid."=".$l;
				$list[$phpok_i]["title"] = $l;
				$list[$phpok_i]["status"] = ($l == $this->num) ? 1 : 0;
				$list[$phpok_i]["type"] = "num";
				$phpok_i++;
			}
		}
		#判断是否显示下一页
		if($this->next_str)
		{
			$show = $this->always ? true : ( ($total_page > 1 && $this->num < $total_page) ? true : false);
			if($show)
			{
				$mynum = $this->num + 1;
				if($mynum > $total_page) $mynum = $total_page;
				$list[$phpok_i]["url"] = $url.$this->pageid."=".$mynum;
				$list[$phpok_i]["title"] = $this->next_str;
				$list[$phpok_i]["status"] = 0;
				$list[$phpok_i]["type"] = "next";
				if($this->num >= $total_page)
				{
					$list[$phpok_i]['nolink'] = true;
				}
				$phpok_i++;
			}
		}
		#判断是否显示尾页
		if($this->last_str)
		{
			$show = $this->always ? true : ( ($total_page > 1 && $this->num < $total_page) ? true : false);
			if($show)
			{
				$list[$phpok_i]["url"] = $url.$this->pageid."=".$total_page;
				$list[$phpok_i]["title"] = $this->last_str;
				$list[$phpok_i]["status"] = 0;
				$list[$phpok_i]["type"] = "last";
				if($this->num >= $total_page)
				{
					$list[$phpok_i]['nolink'] = true;
				}
				$phpok_i++;
			}
		}
		#判断是否显示Option列表
		if($this->opt_str && $this->half)
		{
			#[添加select里的中间项]
			$list[$phpok_i]["url"] = $url.$this->pageid."=";
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] ="opt";
			$array = array();
			for($i = $this->num - $this->half * 3,$i>0 || $i=0,$j=$this->num + $this->half * 3,$j<$total_page || $j=$total_page;$i<$j;$i++)
			{
				$l = $i + 1;
				$tmp = array();
				$tmp["value"] = $l;
				$tmp["title"] = str_replace("(num)",$l,$this->opt_str);
				if($tmp['title'])
				{
					$tmp["title"] = str_replace($old_array,$new_array,$tmp['title']);
				}
				$tmp["status"] = $l == $this->num ? 1 : 0;
				$array[] = $tmp;
			}
			$list[$phpok_i]["title"] = $array;
			$phpok_i++;
		}
		return $list;
	}
}
?>
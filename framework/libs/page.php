<?php
/**
 * 分页类
 * @author phpok.com <admin@phpok.com>
 * @copyright 2015-2016 深圳市锟铻科技有限公司
 * @version 5.0.0
 * @date 2016年02月05日
 */
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class page_lib
{
	private $half = 5;
	private $psize = 20;
	private $pageid = "pageid";
	private $num = 1;
	private $always = 0;
	private $add_up = "";
	private $home_str = "";
	private $next_str = "";
	private $prev_str = "";
	private $last_str = "";
	private $opt_str = "";
	private $url_format = '';
	private $url_cut = '';

	public function __construct()
	{
		$this->half = 5;
		$this->psize = 20;
		$this->pageid= "pageid";
		$this->num = 1;
		$this->total = 0;
	}

	public function pageid($pageid=1)
	{
		$this->pageid = $pageid;
		return $this->pageid;
	}

	public function num($num=1)
	{
		$this->num = $num;
		return $this->num;
	}

	public function half($half=5)
	{
		$this->half = intval($half);
		return $this->half;
	}

	public function psize($psize=20)
	{
		$this->psize = intval($psize);
		return $this->psize;
	}

	public function always($always=0)
	{
		$this->always = $always;
		return $this->always;
	}

	public function add_up($add_up="")
	{
		if($add_up){
			$this->add_up = $add_up;
		}
		return $this->add_up;
	}

	public function home_str($title='')
	{
		if($title){
			$this->home_str = $title;
		}
		return $this->home_str;
	}

	public function prev_str($title='')
	{
		if($title){
			$this->prev_str = $title;
		}
		return $this->prev_str;
	}

	public function next_str($title='')
	{
		if($title){
			$this->next_str = $title;
		}
		return $this->next_str;
	}

	public function last_str($title='')
	{
		if($title){
			$this->last_str = $title;
		}
		return $this->last_str;
	}

	public function opt_str($title='')
	{
		if($title){
			$this->opt_str = $title;
		}
		return $this->opt_str;
	}

	public function url_format($str='')
	{
		if($str){
			$this->url_format = $str;
		}
		return $this->url_format;
	}

	public function url_cut($val='')
	{
		if($val){
			$this->url_cut = $val;
		}
		return $this->url_cut;
	}

	private function _page_rewrite($url,$total,$num=0,$psize=0,$total_page=0)
	{
		$tmpurl = $this->url_cut ? str_replace($this->url_cut,'',$url) : $url;
		$old = array("(total)","(psize)","(total_page)","(num)","(pageid)","(url)");
		$new = array($total,$psize,$total_page,$num,$this->pageid,$url);
		$phpok_i = 0;
		$list = array();
		if($this->add_up){
			$this->add_up = str_replace($old,$new,$this->add_up);
			$list[$phpok_i]["url"] = "";
			$list[$phpok_i]["title"] = $this->add_up;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "add";
			$list[$phpok_i]['nolink'] = true;
			$phpok_i++;
		}
		if($this->home_str && ($this->always || ($num>1))){
			$list[$phpok_i]["url"] = $url;
			$list[$phpok_i]["title"] = $this->home_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "home";
			if($num <= 1){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		if($this->prev_str && ($this->always || $num > 1)){
			if($num>1){
				$prev_num = $num-1;
				$prev_url = $prev_num > 1 ? $tmpurl.str_replace('(page)',$prev_num,$this->url_format) : $url;
			}else{
				$prev_num = 1;
				$prev_url = $url;
			}
			$list[$phpok_i]["url"] = $prev_url;
			$list[$phpok_i]["title"] = $this->prev_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "prev";
			if($num <= 1){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}

		if($this->half){
			for($i=$num-$this->half,$i>0 || $i=0, $j=$num+$this->half,$j<$total_page || $j=$total_page;$i<$j;$i++){
				$l = $i + 1;
				if($l != 1){
					$purl = $tmpurl.str_replace('(page)',$l,$this->url_format);
				}else{
					$purl = $url;
				}
				$list[$phpok_i]["url"] = $purl;
				$list[$phpok_i]["title"] = $l;
				$list[$phpok_i]["status"] = ($l == $num) ? 1 : 0;
				$list[$phpok_i]["type"] = "num";
				$phpok_i++;
			}
		}
		if($this->next_str && ($this->always || ($total_page > 1 && $num < $total_page))){
			$mynum = $num + 1;
			if($mynum > $total_page){
				$mynum = $total_page;
			}
			$purl = $mynum > 1 ? $tmpurl.str_replace('(page)',$mynum,$this->url_format) : $url;			
			$list[$phpok_i]["url"] = $purl;
			$list[$phpok_i]["title"] = $this->next_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "next";
			if($num >= $total_page){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		if($this->last_str && ($this->always || ($total_page>1 && $num < $total_page))){
			$purl = $total_page > 1 ? $tmpurl.str_replace('(page)',$total_page,$this->url_format) : $url;
			$list[$phpok_i]["url"] = $purl;
			$list[$phpok_i]["title"] = $this->last_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "last";
			if($num >= $total_page){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		return $list;
	}

	public function page($url,$total,$num=0,$psize=0)
	{
		if($num){
			$this->num = $num;
		}
		if($psize && intval($psize)){
			$this->psize = intval($psize);
		}
		if(!$url || !$total){
			return false;
		}
		$total_page = intval($total/$this->psize);
		if($total % $this->psize){
			$total_page ++;
		}
		if($this->num > $total_page){
			$this->num = $total_page;
		}
		if($this->url_format && strpos($url,'?') !== true){
			return $this->_page_rewrite($url,$total,$this->num,$this->psize,$total_page);
		}
		$url = str_replace("&amp;","&",$url);
		if(strpos($url,"?") === false){
			$url .= "?";
		}
		if(substr($url,-1) != "&" && substr($url,-1) != "?"){
			$url .= "&";
		}
		$old_array = array("(total)","(psize)","(total_page)","(num)","(pageid)","(url)");
		$new_array = array($total,$this->psize,$total_page,$this->num,$this->pageid,$url);
		$phpok_i = 0;
		$list = array();
		if($this->add_up){
			$this->add_up = str_replace($old_array,$new_array,$this->add_up);
			$list[$phpok_i]["url"] = "";
			$list[$phpok_i]["title"] = $this->add_up;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "add";
			$phpok_i++;
		}
		if($this->home_str && ($this->always || $this->num>1)){
			$list[$phpok_i]["url"] = substr($url,0,-1);
			$list[$phpok_i]["title"] = $this->home_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "home";
			if($this->num <= 1){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		if($this->prev_str && ($this->always || ($this->num>1))){
			$purl = substr($url,0,-1);
			if($this->num > 1){
				$prev_num = $this->num - 1;
				$purl = $url.$this->pageid."=".$prev_num;
			}
			$list[$phpok_i]["url"] = $purl;
			$list[$phpok_i]["title"] = $this->prev_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "prev";
			if($this->num <= 1){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		if($this->half){
			for($i=$this->num - $this->half,$i>0 || $i=0,$j=$this->num + $this->half,$j<$total_page || $j=$total_page;$i<$j;$i++){
				$l = $i + 1;
				$purl = substr($url,0,-1);
				if($l != 1){
					$purl = $url.$this->pageid."=".$l;
				}
				$list[$phpok_i]["url"] = $purl;
				$list[$phpok_i]["title"] = $l;
				$list[$phpok_i]["status"] = ($l == $this->num) ? 1 : 0;
				$list[$phpok_i]["type"] = "num";
				$phpok_i++;
			}
		}
		if($this->next_str && ($this->always || ($total_page > 1 || $this->num < $total_page))){
			$mynum = $this->num + 1;
			if($mynum > $total_page){
				$mynum = $total_page;
			}
			$purl = $url.$this->pageid."=".$mynum;
			$list[$phpok_i]["url"] = $purl;
			$list[$phpok_i]["title"] = $this->next_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "next";
			if($this->num >= $total_page){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		if($this->last_str && ($this->always || ($total_page>1 && $this->num < $total_page))){
			$purl = $url.$this->pageid."=".$total_page;
			$list[$phpok_i]["url"] = $purl;
			$list[$phpok_i]["title"] = $this->last_str;
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] = "last";
			if($this->num >= $total_page){
				$list[$phpok_i]['nolink'] = true;
			}
			$phpok_i++;
		}
		if($this->opt_str && $this->half){
			$list[$phpok_i]["url"] = $url.$this->pageid."=";
			$list[$phpok_i]["status"] = 0;
			$list[$phpok_i]["type"] ="opt";
			$array = array();
			for($i = $this->num - $this->half * 3,$i>0 || $i=0,$j=$this->num + $this->half * 3,$j<$total_page || $j=$total_page;$i<$j;$i++){
				$l = $i + 1;
				$tmp = array();
				$tmp["value"] = $l;
				$tmp["title"] = str_replace("(num)",$l,$this->opt_str);
				if($tmp['title']){
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
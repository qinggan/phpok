<?php
/***********************************************************
	Filename: libs/autoload/trans.php
	Note	: 数据安全传输
	Version : 3.0
	Author  : qinggan
	Update  : 2009-10-20
***********************************************************/
class trans_lib
{
	var $script = false;
	var $iframe = false;
	var $style = false;

	function __construct()
	{
		$this->script = false;
		$this->iframe = false;
		$this->style = false;
		//字符串过滤
		$this->html_string = array("&amp;","&nbsp;","'",'"',"<",">","\t","\r");
		$this->html_clear = array("&"," ","&#39;","&quot;","&lt;","&gt;","&nbsp; &nbsp; ","");
		//JS过滤
		$this->js_string = array("/<script(.*)<\/script>/isU");
		$this->js_clear = array("");
		//iframe框过滤
		$this->frame_string = array("/<frame(.*)>/isU","/<\/fram(.*)>/isU","/<iframe(.*)>/isU","/<\/ifram(.*)>/isU",);
		$this->frame_clear = array("","","","");
		//style样式过滤
		$this->style_string = array("/<style(.*)<\/style>/isU","/<link(.*)>/isU","/<\/link>/isU");
		$this->style_clear = array("","","");
	}

	#[兼容PHP4]
	function trans_lib()
	{
		$this->__construct();
	}

	function __destruct()
	{
		return true;
	}

	//设置全局状态
	function setting($script=false,$iframe=false,$style=false)
	{
		$this->script = $script;
		$this->iframe = $iframe;
		$this->style = $style;
	}

	function safe($msg)
	{
		$msg = $this->post_get($msg);
		$msg = $this->_safe($msg);
		if(!$msg)
		{
			return false;
		}
		return $msg;
	}

	function text($msg)
	{
		$msg = $this->safe($msg);
		if($msg)
		{
			$msg = strip_tags($msg);
		}
		return $msg;
	}

	function format($msg)
	{
		if(!$msg) return false;
		return $this->_safe($msg);
	}

	//实体化HTML
	function st_safe($msg)
	{
		$msg = $this->_safe($msg);
		if(!$msg)
		{
			return false;
		}
		return $msg;
	}

	function _safe($msg)
	{
		if(is_array($msg))
		{
			foreach($msg AS $key=>$value)
			{
				$msg[$key] = $this->_safe($value);
			}
		}
		else
		{
			$msg = trim($msg);
			$msg = str_replace($this->html_string,$this->html_clear,$msg);
			$msg = str_replace("   ","&nbsp; &nbsp;",$msg);
			//过滤JS
			$msg = preg_replace($this->js_string,$this->js_clear,$msg);
			$msg = preg_replace($this->frame_string,$this->frame_clear,$msg);
			$msg = preg_replace($this->style_string,$this->style_clear,$msg);
		}
		return $msg;
	}

	#[用户加载文件的数据传输]
	function safeinc($msg)
	{
		$msg = $this->safe($msg);
		$msg = str_replace(".","_",$msg);
		return $msg;
	}

	function html_js($msg,$delurl=true)
	{
		$this->setting(true,true,true);
		$msg = $this->html($msg);
		$this->setting(false,false,false);
		return $msg;
	}

	//格式化为时间模式
	function time($msg)
	{
		$msg = $this->safe($msg);
		if($msg)
		{
			$msg = strtotime($msg);
		}
		return $msg;
	}

	function html($msg,$delurl=true)
	{
		$msg = $this->post_get($msg);
		if(!$msg)
		{
			return false;
		}
		if(is_array($msg))
		{
			foreach($msg AS $key=>$value)
			{
				$msg[$key] = $this->html($value);
			}
		}
		else
		{
			$msg = trim($msg);
			$msg = stripslashes($msg);
			if(!$this->script)
			{
				$msg = preg_replace($this->js_string,$this->js_clear,$msg);
			}
			if(!$this->iframe)
			{
				$msg = preg_replace($this->frame_string,$this->frame_clear,$msg);
			}
			if(!$this->style)
			{
				$msg = preg_replace($this->style_string,$this->style_clear,$msg);
			}
			if($delurl)
			{
				$url = $this->get_url();
				$msg = str_replace($url,"",$msg);
			}
			$msg = addslashes($msg);
		}
		return $msg;
	}

	function post_get($msg)
	{
		$val = $_POST[$msg] ? $_POST[$msg] : $_GET[$msg];
		return $val;
	}

	function int($id)
	{
		$id = $this->safe($id);
		return intval($id);
	}

	function float($id)
	{
		$id = $this->safe($id);
		return floatval($id);
	}

	function checkbox($id)
	{
		return isset($_POST[$id]) ? 1 : 0;
	}

	#[截取字符长度，仅支持UTF-8]
	function cut($string,$sublen,$dot="…")
	{
		if(!$string) return false;
		//过滤iframe
		$string = preg_replace($this->frame_string,$this->frame_clear,$string);
		//清空HTML,CSS
		$string = $this->Html2Text($string);
		if(!$string) return false;
		$pa="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		preg_match_all($pa,$string,$t_string);
		if(count($t_string[0])>$sublen) return join('',array_slice($t_string[0],0,$sublen)).$dot;
		return join('',array_slice($t_string[0],0,$sublen));
	}

	function Html2Text($str)
	{
		if(!$str) return false;
		$str = preg_replace("/<sty(.*)\\/style>|<scr(.*)\\/script>|<!--(.*)-->/isU","",$str);
		$alltext = "";
		$start = 1;
		for($i=0;$i<strlen($str);$i++){
			if($start==0 && $str[$i]==">"){
				$start = 1;
			}else if($start==1){
				if($str[$i]=="<"){
					$start = 0;
					$alltext .= " ";
				}else if(ord($str[$i])>31){
					$alltext .= $str[$i];
				}
			}
		}
		$alltext = str_replace("　"," ",$alltext);
		$alltext = preg_replace("/&([^;&]*)(;|&)/","",$alltext);
		$alltext = preg_replace("/[ ]+/s"," ",$alltext);
		return $alltext;
	}
	

	#[编码转换，使用PHP里的iconv功能]
	function charset($msg, $s_code="UTF-8", $e_code="GBK")
	{
		if(!$msg)
		{
			return false;
		}
		if(is_array($msg))
		{
			foreach($msg AS $key=>$value)
			{
				$msg[$key] = $this->charset($value,$s_code,$e_code);
			}
		}
		else
		{
			if(function_exists("iconv"))
			{
				$msg = iconv($s_code,$e_code,$msg);
			}
		}
		return $msg;
	}

	function num_format($a,$ext=2)
	{
		if(!$a || $a == 0)
		{
			return false;
		}
		if($a <= 1024)
		{
			$a = "1 KB";
		}
		elseif($a>1024 && $a<(1024*1024))
		{
			$a = round(($a/1024),$ext)." KB";
		}
		elseif($a>=(1024*1024) && $a<(1024*1024*1024))
		{
			$a = round(($a/(1024*1024)),$ext)." MB";
		}
		else
		{
			$a = round(($a/(1024*1024*1024)),$ext)." GB";
		}
		return $a;
	}

	function get_url()
	{
		$myurl = "http://".str_replace("http://","",$_SERVER["SERVER_NAME"]);
		$docu = $_SERVER["PHP_SELF"];
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1)
		{
			foreach($array AS $key=>$value)
			{
				$value = trim($value);
				if($value)
				{
					if(($key+1) < $count)
					{
						$myurl .= "/".$value;
					}
				}
			}
		}
		$myurl .= "/";
		return $myurl;
	}

	function is_utf8($string)
	{
		return preg_match('/^(?:[x09x0Ax0Dx20-x7E]|[xC2-xDF][x80-xBF]|xE0[xA0-xBF][x80-xBF]|[xE1-xECxEExEF][x80-xBF]{2}|xED[x80-x9F][x80-xBF]|xF0[x90-xBF][x80-xBF]{2}|[xF1-xF3][x80-xBF]{3}| xF4[x80-x8F][x80-xBF]{2})*$/xs',$string);
	}

	function html_edit($content)
	{
		if(!$content)
		{
			return false;
		}
		$content = str_replace("&","&amp;",$content);
		$old = array("'",'"',"<",">");
		$new = array("&#39;","&quot;","&lt;","&gt;");
		return str_replace($old,$new,$content);
	}

	function edit_html($content)
	{
		if(!$content)
		{
			return false;
		}
		$old = array("&#39;","&quot;","&lt;","&gt;");
		$new = array("'",'"',"<",">");
		$content = str_replace($old,$new,$content);
		$content = str_replace("&amp;","&",$content);
		return $content;
	}

	function html_fck($msg)
	{
		if(!$msg)
		{
			return false;
		}
		$url = $this->get_url();
		$imgArray = array();
		preg_match_all("/src=[\"|'| ]((.*)\.(gif|jpg|jpeg|bmp|png|swf))/isU",$msg,$imgArray);
		$imgArray = array_unique($imgArray[1]);
		$count = count($imgArray);
		if($count < 1)
		{
			return $msg;
		}
		foreach($imgArray AS $key=>$value)
		{
			$value = trim($value);
			if(strpos($value,"http://") === false && $value)
			{
				$msg = str_replace($value,$url.$value,$msg);
			}
		}
		return $msg;
	}

}
?>
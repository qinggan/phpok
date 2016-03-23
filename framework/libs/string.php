<?php
/*****************************************************************************************
	文件： {phpok}/libs/string.php
	备注： 字符串处理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月16日 19时20分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class string_lib
{
	private $cut_type = false;
	public function __construct()
	{
		if(function_exists('mb_substr') && function_exists('mb_internal_encoding'))
		{
			mb_internal_encoding("UTF-8");
			$this->cut_type = true;
		}
	}

	
	//字符串分割
	//string，传入的字符串内容
	//length，截取长度
	//dot，补截（如省略号等）
	//html，是否保留HTML样式
	public function cut($string,$length=0,$dot='',$html=false)
	{
		if(!$length || !trim($string)){
			return $string;
		}
		$str = $string;
		$string = strip_tags(trim($string));
		$string = str_replace(array("&nbsp;",' ','　'),"",$string);
		if(strlen($string) <= $length){
			return $html ? $str : $string;
		}
		$info = $this->cut_type ? $this->_substr($string,$length,$dot) : $this->_cut($string,$length,$dot);
		if(!$html){
			return $info;
		}
		//组成HTML样式
		$starts = $ends = $starts_str = false;
		preg_match_all('/<\w+[^>]*>/isU',$str,$starts,PREG_OFFSET_CAPTURE);
		preg_match_all('/<\/\w+>/isU',$str,$ends,PREG_OFFSET_CAPTURE);
		if(!$starts || ($starts && !$starts[0])){
			return str_replace(" ","&nbsp;",$info);
		}
		$lst = $use = false;
		foreach($starts[0] as $key=>$value){
			if($value[1] >= $length){
				break;
			}
			$info = substr($info,0,$value[1]).$value[0].substr($info,$value[1]);
			$length += strlen($value[0]);
			if($ends && $ends[0][$key]){
				$chk = str_replace(array('/','>'),'',$ends[0][$key][0]);
				if(substr($value[0],0,strlen($chk)) == $chk){
					$info = substr($info,0,$ends[0][$key][1]).$ends[0][$key][0].substr($info,$ends[0][$key][1]);
					$length += strlen($ends[0][$key][0]);
					$use[$key] = $ends[0][$key];
				}else{
					$lst[] = $value[0];
				}
			}else{
				$lst[] = $value[0];
			}
		}
		if($ends && $lst){
			foreach($ends[0] as $key=>$value){
				if($use && $use[$key]){
					continue;
				}
				$chk = str_replace(array('/','>'),'',$value[0]);
				foreach($lst as $k=>$v){
					if(substr($v,0,strlen($chk)) == $chk){
						$info = substr($info,0,$value[1]).$value[0].substr($info,$value[1]);
						$length += strlen($value[0]);
						$use[$key] = $value;
						unset($lst[$k]);
					}
				}
			}
		}
		return $info;
	}

	//把字符串转成数组，支持汉字，只能是utf-8格式的，返回数组
	public function to_array($str)
	{
		$result = array();
		$len = strlen($str);
		$i = 0;
		while($i < $len){
			$chr = ord($str[$i]);
			if($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
				$result[] = substr($str,$i,1);
				$i +=1;
			}elseif(192 <= $chr && $chr <= 223){
				$result[] = substr($str,$i,2);
				$i +=2;
			}elseif(224 <= $chr && $chr <= 239){
				$result[] = substr($str,$i,3);
				$i +=3;
			}elseif(240 <= $chr && $chr <= 247){
				$result[] = substr($str,$i,4);
				$i +=4;
			}elseif(248 <= $chr && $chr <= 251){
				$result[] = substr($str,$i,5);
				$i +=5;
			}elseif(252 <= $chr && $chr <= 253){
				$result[] = substr($str,$i,6);
				$i +=6;
			}
		}
		return $result;
	}

	//将HTML安全格式化
	public function safe_html($content,$clear_url='')
	{
		$content = $this->xss_clean($content);
		$content = preg_replace("/<(^[script|applet|style|title|iframe|frame|frameset|link]+).*>[.\n\t\r]*<\/\\1>/isU",'',$content);
		$content = preg_replace("/<\/?link.*?>/isU","",$content);
		$content = preg_replace('/<meta(.+)>/isU','',$content);
		if($clear_url){
			return $this->clear_url($content,$clear_url);
		}
		return $content;
	}

	public function clear_url($content='',$url='')
	{
		if(!$content || !$url){
			return $content;
		}
		return str_replace(array("src='".$url,'src="'.$url,"src=".$url),array("src='",'src="',"src="),$content);
	}

	//判断字符是否是utf8
	public function is_utf8($string)
	{
		if(function_exists('mb_detect_encoding'))
		{
			$e=mb_detect_encoding($string, array('UTF-8','GBK'));
			return $e == 'UTF-8' ? true : false;
		}
		return preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$string) == true ? true : false;
	}

	//转换成utf8
	public function charset($msg,$from_charset="GBK",$to_charset="UTF-8")
	{
		if(!$msg) return false;
		if(!function_exists("iconv")) return $msg;
		if(is_array($msg))
		{
			foreach($msg AS $key=>$value)
			{
				$msg[$key] = $this->charset($value,$from_charset,$to_charset);
			}
		}
		else
		{
			$msg = iconv($from_charset,$to_charset,$msg);
		}
		return $msg;
	}

	//将非UTF-8字符转成UTF-8
	public function to_utf8($msg)
	{
		if(!$this->is_utf8($msg))
		{
			$msg = $this->charset($msg,'GBK','UTF-8');
		}
		return $msg;
	}

	public function xss_clean($data)
	{
		$data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
		do {
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|iframe|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		} while ($old_data !== $data);
		return $data;
	}
	
	private function _substr($sourcestr,$cutlength=255,$dot='')
	{
		$returnstr = '';
		$i = 0;
		$n = 0;
		$str_length = strlen($sourcestr);
		$mb_str_length = mb_strlen($sourcestr,'utf-8');
		while(($n < $cutlength) && ($i <= $str_length)){
			$temp_str = substr($sourcestr,$i,1);
			$ascnum = ord($temp_str);
			if($ascnum >= 224){
				$returnstr = $returnstr.substr($sourcestr,$i,3);
				$i = $i + 3;
				$n++;
			}elseif($ascnum >= 192){
				$returnstr = $returnstr.substr($sourcestr,$i,2);
				$i = $i + 2;
				$n++;
			}elseif(($ascnum >= 65) && ($ascnum <= 90)){
				$returnstr = $returnstr.substr($sourcestr,$i,1);
				$i = $i + 1;
				$n = $n + 0.5;
			}else{
				$returnstr = $returnstr.substr($sourcestr,$i,1);
				$i = $i + 1;
				$n = $n + 0.5;
			}
		}
		if ($mb_str_length > $cutlength){
			$returnstr = $returnstr . $dot;
		}
		return $returnstr; 
	}

	//旧版字符串截取
	private function _cut($string,$length=0,$dot='')
	{
		$wordscut = "";
		$n = 0;
		$tn = 0;
		$noc = 0;
		while ($n < strlen($string)){
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)){
				$tn = 1;
				$n++;
				$noc = $noc + 0.5;
			}elseif (194 <= $t && $t <= 223){
				$tn = 2;
				$n += 2;
				$noc++;
			}elseif (224 <= $t && $t < 239){
				$tn = 3;
				$n += 3;
				$noc++;
			}elseif (240 <= $t && $t <= 247){
				$tn = 4;
				$n += 4;
				$noc++;
			}elseif (248 <= $t && $t <= 251){
				$tn = 5;
				$n += 5;
				$noc++;
			}elseif ($t == 252 || $t == 253){
				$tn = 6;
				$n += 6;
				$noc++;
			}else{
				$n++;
				$noc = $noc + 0.5;
			}
			if ($noc >= $length){
				break;
			}
		}
		if ($noc > $length){
			$n -= $tn;
		}
		$wordscut = substr($string, 0, $n);
		if($wordscut != $string){
			return $wordscut.$dot;
		}
		return $wordscut;
	}

}
?>
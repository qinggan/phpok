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
		if(!$length)
		{
			return $string;
		}
		$str = $string;
		$string = strip_tags(trim($string));
		$string = str_replace("&nbsp;"," ",$string);
		if(strlen($string) <= $length)
		{
			return $html ? $str : $string;
		}
		$info = $this->cut_type ? $this->_substr($string,$length) : $this->_cut($string,$length);
		if(!$html)
		{
			return $info.$dot;
		}
		//组成HTML样式
		$starts = $ends = $starts_str = false;
		preg_match_all('/<\w+[^>]*>/isU',$str,$starts,PREG_OFFSET_CAPTURE);
		preg_match_all('/<\/\w+>/isU',$str,$ends,PREG_OFFSET_CAPTURE);
		if(!$starts || ($starts && !$starts[0]))
		{
			return str_replace(" ","&nbsp;",$info).$dot;
		}
		$lst = $use = false;
		foreach($starts[0] as $key=>$value)
		{
			if($value[1] >= $length)
			{
				break;
			}
			$info = substr($info,0,$value[1]).$value[0].substr($info,$value[1]);
			$length += strlen($value[0]);
			if($ends && $ends[0][$key])
			{
				//检测标签是否符合要求
				$chk = str_replace(array('/','>'),'',$ends[0][$key][0]);
				if(substr($value[0],0,strlen($chk)) == $chk)
				{
					$info = substr($info,0,$ends[0][$key][1]).$ends[0][$key][0].substr($info,$ends[0][$key][1]);
					$length += strlen($ends[0][$key][0]);
					$use[$key] = $ends[0][$key];
				}
				else
				{
					$lst[] = $value[0];
				}
			}
			else
			{
				$lst[] = $value[0];
			}
		}
		if($ends && $lst)
		{
			foreach($ends[0] as $key=>$value)
			{
				//检测是否已经使用过了
				if($use && $use[$key])
				{
					continue;
				}
				$chk = str_replace(array('/','>'),'',$value[0]);
				foreach($lst as $k=>$v)
				{
					if(substr($v,0,strlen($chk)) == $chk)
					{
						$info = substr($info,0,$value[1]).$value[0].substr($info,$value[1]);
						$length += strlen($value[0]);
						$use[$key] = $value;
						unset($lst[$k]);
					}
				}
			}
		}
		return $info.$dot;
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
		$content = preg_replace_callback('/<(.+)>/isU',array($this,'_clean_xss_on'),$content);
		//清除带src和href里的信息
		$content = preg_replace_callback("/<(.*)(src|href)\s*=(\"|')(.+)(\\3)(.*)>/isU",array($this,'_clean_xss_script'),$content);
		//清除src传递没有引号的数据
		$content = preg_replace_callback("/<(.*)(src|href)\s*=([^\s>]+)([\s|\/|>])/isU",array($this,'_clean_xss_script2'),$content);
		//清除script,applet,style,title,iframe等不安全信息
		$content = preg_replace("/<(^[script|applet|style|title|iframe|frame|frameset|link]+).*>[.\n\t\r]*<\/\\1>/isU",'',$content);
		$content = preg_replace("/<\/?link.*?>/isU","",$content);
		//清除meta信息
		$content = preg_replace('/<meta(.+)>/isU','',$content);
		if($clear_url)
		{
			$content = str_replace(array("src='".$clear_url,'src="'.$clear_url,"src=".$clear_url),array("src='",'src="',"src="),$content);
			return $content;
		}
		return $content;
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
	
	private function _clean_xss_on($info)
	{
		if(!$info || !$info[1] || !trim($info[1]))
		{
			return false;
		}
		$info = $info[1];
		$info = str_replace(array("\n","\t","\r"),'',$info);
		if(!$info)
		{
			return false;
		}
		//清除on等动作属性（带引号）
		$preg = "/on([a-zA-Z]+)\s*=(\"|')+(.*)(\\2)+/isU";
		$info = preg_replace($preg,'',$info);
		//清除on等动作属性（不带引号，不支持空格）
		$preg = "/on([a-zA-Z]+)\s*=([^\s>]+)[\s|\/|>]+/isU";
		$info = preg_replace($preg,'',$info);
		return '<'.$info.'>';
	}

	private function _clean_xss_script($info)
	{
		if(!$info)
		{
			return false;
		}
		if(!$info[4] || !trim($info[4]))
		{
			return $info[0];
		}
		$tmp = strtolower($info[4]);
		if(substr($tmp,0,8) == 'vbscript' || substr($tmp,0,10) == 'javascript')
		{
			return '<'.$info[1].$info[2].'="javascript:void(0);" '.trim($info[6]).'>';
		}
		return $info[0];
	}

	private function _clean_xss_script2($info)
	{
		if(!$info)
		{
			return false;
		}
		if(!$info[3] || !trim($info[3]))
		{
			return $info[0];
		}
		$tmp = strtolower($info[3]);
		if(substr($tmp,0,8) == 'vbscript' || substr($tmp,0,10) == 'javascript')
		{
			return '<'.$info[1].$info[2].'="javascript:void(0);"'.$info[4];
		}
		return $info[0];
	}

	private function _substr($string,$length=255)
	{
		return mb_substr($string,0,$length);
		
	}

	//旧版字符串截取
	private function _cut($string,$length=0)
	{
		$wordscut = "";
		//utf8编码
		$n = 0;
		$tn = 0;
		$noc = 0;
		while ($n < strlen($string))
		{
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126))
			{
				$tn = 1;
				$n++;
				$noc++;
			}
			elseif (194 <= $t && $t <= 223)
			{
				$tn = 2;
				$n += 2;
				//$noc += 2;
				$noc++;
			}
			elseif (224 <= $t && $t < 239)
			{
				$tn = 3;
				$n += 3;
				//$noc += 2;
				$noc++;
			}
			elseif (240 <= $t && $t <= 247)
			{
				$tn = 4;
				$n += 4;
				//$noc += 2;
				$noc++;
			}
			elseif (248 <= $t && $t <= 251)
			{
				$tn = 5;
				$n += 5;
				//$noc += 2;
				$noc++;
			}
			elseif ($t == 252 || $t == 253)
			{
				$tn = 6;
				$n += 6;
				//$noc += 2;
				$noc++;
			}
			else
			{
				$n++;
			}
			if ($noc >= $length) break;
		}
		if ($noc > $length) $n -= $tn;
		$wordscut = substr($string, 0, $n);
		return $wordscut;
	}

}
?>
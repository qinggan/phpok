<?php
/***********************************************************
	Filename: phpok/libs/string.php
	Note	: 字符串管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-19 13:38
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class string_lib
{
	private $use_mb_substr = false;
	public function __construct()
	{
		if(function_exists('mb_substr') && function_exists('mb_internal_encoding'))
		{
			mb_internal_encoding("UTF-8");
			$this->use_mb_substr = true;
		}
	}

	private function _substr($string,$length=255)
	{
		return mb_substr($string,0,$length);
		
	}

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
		$info = $this->use_mb_substr ? $this->_substr($string,$length) : $this->_cut($string,$length);
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

	//字符串截取
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
				$noc += 2;
			}
			elseif (224 <= $t && $t < 239)
			{
				$tn = 3;
				$n += 3;
				$noc += 2;
			}
			elseif (240 <= $t && $t <= 247)
			{
				$tn = 4;
				$n += 4;
				$noc += 2;
			}
			elseif (248 <= $t && $t <= 251)
			{
				$tn = 5;
				$n += 5;
				$noc += 2;
			}
			elseif ($t == 252 || $t == 253)
			{
				$tn = 6;
				$n += 6;
				$noc += 2;
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

	/**
	 * 把字符串转成数组，支持汉字，只能是utf-8格式的
	 * @param $str
	 * @return array
	 */
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
}
?>
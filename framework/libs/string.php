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
	function __construct()
	{
		//
	}

	//字符串截取
	function cut($string, $length=255,$dot = "")
	{
		//去除HTML和PHP代码
		$string = strip_tags(trim($string));
		$string = str_replace("&nbsp;"," ",$string);
		$string = preg_replace("/(\x20{2,})/"," ",$string);# 去除多余空格，只保留一个空格
		if(!$length) $length = 255;
		if(strlen($string) <= $length) return $string;

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
		if($wordscut != $string)
		{
			$string = $wordscut.$dot;
		}
		$string = $wordscut.$dot;
		return trim($string);
	}

	/**
	 * 把字符串转成数组，支持汉字，只能是utf-8格式的
	 * @param $str
	 * @return array
	 */
	function StringToArray($str)
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
<?php
/**
 * 字符串处理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2020年3月5日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class string_lib
{
	private $cut_type = false;
	public function __construct()
	{
		if(function_exists('mb_substr') && function_exists('mb_internal_encoding')){
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
		$string = str_replace('&nbsp;',' ',$string);
		if(strlen($string) <= $length){
			return $html ? $str : $string;
		}
		$string = str_replace('　','',$string);
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
		$content = $this->remove_xss($content);
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
		if(function_exists('mb_detect_encoding')){
			$e=mb_detect_encoding($string, array('UTF-8','GBK'));
			return $e == 'UTF-8' ? true : false;
		}
		return preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$string) == true ? true : false;
	}

	//转换成utf8
	public function charset($msg,$from_charset="GBK",$to_charset="UTF-8")
	{
		if(!$msg){
			return false;
		}
		if(!function_exists("iconv")){
			return $msg;
		}
		if(is_array($msg)){
			foreach($msg as $key=>$value){
				$msg[$key] = $this->charset($value,$from_charset,$to_charset);
			}
		}else{
			$msg = iconv($from_charset,$to_charset,$msg);
		}
		return $msg;
	}

	//将非UTF-8字符转成UTF-8
	public function to_utf8($msg)
	{
		if(!$this->is_utf8($msg)){
			$msg = $this->charset($msg,'GBK','UTF-8');
		}
		return $msg;
	}

	public function xss_clean($data)
	{
		return $this->remove_xss($data);
	}

	public function remove_xss($val)
	{
		$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
			$val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
		}
		$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(�{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
				$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
				if ($val_before == $val) {
					// no replacements were made, so exit the loop
					$found = false;
				}
			}
		}
		return $val;
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
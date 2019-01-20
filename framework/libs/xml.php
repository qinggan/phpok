<?php
/*****************************************************************************************
	文件： {phpok}/libs/xml.php
	备注： PHP操作XML类，读取方法：SimpleXML>正则解析
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年01月05日 14时23分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class xml_lib
{
	private $xml_read_func = 'phpok';
	private $xml_save_func = 'phpok';
	public function __construct()
	{
		if(function_exists('simplexml_load_string')){
			$this->xml_read_func = 'simplexml';
		}
		$this->xml_save_func = 'phpok';
	}

	public function read_setting($type='phpok')
	{
		$this->xml_read_func = $type;
	}

	public function save_setting($type="phpok")
	{
		$this->xml_save_func = $type;
	}

	public function reset_setting()
	{
		$this->xml_read_func = function_exists('simplexml_load_string') ? 'simplexml' : 'phpok';
		$this->xml_save_func = 'phpok';
	}

	/**
	 * 读取XML操作
	 * @参数 $info XML文件或要解析的XML内容
	 * @参数 $isfile，是否是文件，默认为是，如果要解析XML内容，请改为false
	**/
	public function read($info,$isfile=true)
	{
		$func = "read_".$this->xml_read_func;
		if($isfile && !file_exists($info)){
			return false;
		}
		return $this->$func($info,$isfile);
	}

	public function save($data,$file='',$ekey='')
	{
		if(!$data || !$file || !is_array($data) || !is_string($file)){
			return false;
		}
		$func = "write_".$this->xml_save_func;
		return $this->$func($data,$file,$ekey);
	}

	public function write($data,$file='',$ekey='')
	{
		return $this->save($data,$file,$ekey);
	}

	private function write_phpok($data,$file,$ekey='')
	{
		$dir = pathinfo($file,PATHINFO_DIRNAME);
		$tmpfile = $dir.'/'.uniqid('tmp_',true).'.xml';
		$handle = fopen($tmpfile,'ab');
		fwrite($handle,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($handle,'<root>'."\n");
		$string = '';
		$this->_array_to_string($string,$data,"\t",$ekey);
		fwrite($handle,$string);
		fwrite($handle,'</root>');
		fclose($handle);
		if(file_exists($file)){
			unlink($file);
		}
		rename($tmpfile,$file);
		return true;
	}

	public function to_xml($data,$ekey='')
	{
		$string = '';
		$this->_array_to_string($string,$data,"\t",$ekey);
		return $string;
	}

	private function _array_to_string(&$string,$data,$space="",$ekey='')
	{
		foreach($data as $key=>$value){
			$tmpid = (is_numeric($key) && $ekey) ? $ekey : $key;
			if($value && (is_array($value) || is_object($value))){
				if(count($value)>0){
					$tmp = "\n";
					$this->_array_to_string($tmp,$value,$space."\t");
					$string .= $space."<".$tmpid.">".$tmp.$space."</".$tmpid.">\n";
				}
			}else{
				if(is_array($value) || is_object($value)){
					continue;
				}
				$value = str_replace(array('<![CDATA[',']]>'),array('&lt;![CDATA[',']]&gt;'),$value);
				$string .= $space."<".$tmpid."><![CDATA[".$value."]]></".$tmpid.">\n";
			}
		}
	}

	//通过SimpleXML读取XML信息
	private function read_simplexml($info,$isfile=true)
	{
		if($isfile){
			$info = file_get_contents($info);
		}
		$info = trim($info);
		libxml_disable_entity_loader(true);
		$xml = simplexml_load_string($info);
		$info = $this->simplexml_obj_to_array($xml);
		if(!$info){
			return false;
		}
		if(isset($info['root']) && $info['root']){
			return $info['root'];
		}
		return $info;
	}

	private function simplexml_obj_to_array($xml)
	{
		$list = false;
		if(!is_object($xml) && !is_array($xml)){
			return $xml;
		}
		foreach($xml as $key=>$value){
			$attr = false;
			if($value->attributes()){
				foreach($value->attributes() as $k=>$v){
					$attr[$k] = (string) $v;
				}
			}
			//检测子节点
			$val = (string) $value;
			if($value->children()){
				$tmp = $this->simplexml_obj_to_array($value->children());
				if($tmp){
					$val = $tmp;
				}
			}
			if(isset($list[$key])){
				if(!is_array($list[$key]) || (isset($list[$key]['attr']) && $list[$key]['attr']) || (isset($list[$key]['val']) && $list[$key]['val'])){
					$tmp = $list[$key];
					unset($list[$key]);
					$list[$key][] = $tmp;
				}
				$list[$key][] = (isset($attr) && $attr) ? array('attr'=>$attr,'val'=>$val) : $val;
			}else{
				$list[$key] = (isset($attr) && $attr) ? array('attr'=>$attr,'val'=>$val) : $val;
			}
		}
		return $list;
	}

	//通过人工编写自己读取XML，效率较慢
	private function read_phpok($info,$isfile=true)
	{
		if($isfile){
			$info = file_get_contents($info);
		}
		$info = preg_replace('/<\?xml[^\?>]+\?>/isU','',$info);
		if(!$info){
			return false;
		}
		$info = str_replace(array("\n","\t","\r"),"",$info);
		$info = $this->xml_to_array($info);
		if(!$info){
			return false;
		}
		if($info['root']){
			return $info['root'];
		}
		return $info;
	}

	private function _string_to_array($ext='')
	{
		if(!$ext || !trim($ext)){
			return false;
		}
		if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()){
			$ext = stripslashes($ext);
		}
		$ext = trim($ext);
		$ext = preg_replace("/(\x20{2,})/"," ",$ext);
		$ext = preg_replace("/[\"|'](^[\"|'|\s]+)\s+(^[\"|'|\s]+)[\"|']/isU",'\\1:_:_:-phpok-:_:_:\\2',$ext);
		$ext = str_replace(array("'",'"'),'',$ext);
		$ext = str_replace(" ","&",$ext);
		parse_str($ext,$list);
		foreach($list as $key=>$value){
			
			if(substr($value,0,1) == '"' || substr($value,0,1) == "'") $value = substr($value,1);
			if(substr($value,-1) == '"' || substr($value,-1) == "'") $value = substr($value,0,-1);
			$value = str_replace(':_:_:-phpok-:_:_:',' ',$value);
			$list[$key] = $value;
		}
		return $list;
	}

	private function xml_to_array($xml)
	{
		if(!$xml || !trim($xml)){
			return false;
		}
		$xml = trim($xml);
		//$reg = "/<(\\w+)([^>]*)>([\\x00-\\xFF]*)<\\/\\1>/isU";
		$reg = "/<([a-zA-Z0-9\_\-]+)([^>]*)>(.*)<\/\\1>/isU";
		if(!preg_match_all($reg, $xml, $matches)){
			return $xml;
		}
		$count = count($matches[0]);
		$array = array();
		for($i=0;$i<$count;$i++){
			$id = $matches[1][$i];
			$attr = $this->_string_to_array($matches[2][$i]);
			$val = $this->xml_to_array($matches[3][$i]);
			if(is_string($val)){
				$val = preg_replace('/<\!\[CDATA\[([^\]\]>]+)\]\]>/isU','\\1',$val);
				//将值中的HTML标记更换
				$val = preg_replace('/\[html:([^\]]+)\]/isU','<\\1>',$val);
				$val = preg_replace('/\[\/([a-zA-Z0-9\_\-]+):html\]/isU','</\\1>',$val);
			}
			if(isset($array[$id])){
				if(!is_array($array[$id]) || $array[$id]['attr'] || $array[$id]['val']){
					$tmp = $array[$id];
					unset($array[$id]);
					$array[$id][] = $tmp;
				}
				$array[$id][] = $attr ? array('attr'=>$attr,'val'=>$val) : $val;
			}else{
				$array[$id] = $attr ? array('attr'=>$attr,'val'=>$val) : $val;
			}
		}
		return $array;
	}
}
?>
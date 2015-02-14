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
	private $xmlfunc = 'phpok';
	function __construct()
	{
		$this->xmlfunc = function_exists('simplexml_load_file') && function_exists('simplexml_load_string') ? 'simplexml' : 'phpok';
		//$this->xmlfunc = 'phpok';
	}

	//读取XML操作
	function read($info,$isfile=true)
	{
		$func = "read_".$this->xmlfunc;
		if($isfile && !file_exists($info))
		{
			return false;
		}
		return $this->$func($info,$isfile);
	}

	//通过SimpleXML读取XML信息
	private function read_simplexml($info,$isfile=true)
	{
		$xml = $isfile ? simplexml_load_file($info) : simplexml_load_string($info);
		return $this->simplexml_obj_to_array($xml);
	}

	private function simplexml_obj_to_array($xml)
	{
		$list = false;
		if(!is_object($xml) && !is_array($xml))
		{
			return $xml;
		}
		foreach($xml as $key=>$value)
		{
			$attr = false;
			if($value->attributes())
			{
				foreach($value->attributes() as $k=>$v)
				{
					$attr[$k] = (string) $v;
				}
			}
			//检测子节点
			$val = (string) $value;
			if($value->children())
			{
				$tmp = $this->simplexml_obj_to_array($value->children());
				if($tmp)
				{
					$val = $tmp;
				}
			}
			if(isset($list[$key]))
			{
				if(!is_array($list[$key]) || $list[$key]['attr'] || $list[$key]['val'])
				{
					$tmp = $list[$key];
					unset($list[$key]);
					$list[$key][] = $tmp;
				}
				$list[$key][] = (isset($attr) && $attr) ? array('attr'=>$attr,'val'=>$val) : $val;
			}
			else
			{
				$list[$key] = (isset($attr) && $attr) ? array('attr'=>$attr,'val'=>$val) : $val;
			}
		}
		return $list;
	}

	//通过人工编写自己读取XML，效率较慢
	private function read_phpok($info,$isfile=true)
	{
		if($isfile)
		{
			$info = file_get_contents($info);
		}
		$list = $this->xml_to_array($info);
		foreach($list as $key=>$value)
		{
			return isset($value['val']) ? $value['val'] : $value;
		}
	}

	private function _string_to_array($ext='')
	{
		if(!$ext || !trim($ext))
		{
			return false;
		}
		$ext = trim($ext);
		$ext = preg_replace("/(\x20{2,})/"," ",$ext);
		$ext = preg_replace("/[\"|'](^[\"|'|\s+]+)\s+(^[\"|'|\s+]+)[\"|']/isU",'\\1:_:_:-phpok-:_:_:\\2',$ext);
		$ext = str_replace(" ","&",$ext);
		parse_str($ext,$list);
		foreach($list as $key=>$value)
		{
			if(substr($value,0,1) == '"' || substr($value,0,1) == "'") $value = substr($value,1);
			if(substr($value,-1) == '"' || substr($value,-1) == "'") $value = substr($value,0,-1);
			$value = str_replace(':_:_:-phpok-:_:_:',' ',$value);
			$list[$key] = $value;
		}
		return $list;
	}

	private function xml_to_array($xml)
	{
		if(!$xml || !trim($xml))
		{
			return false;
		}
		$xml = trim($xml);
		$reg = "/<([a-zA-Z0-9\_\-]+)([^>]*?)>([\\x00-\\xFF]*?)<\\/\\1>/";
		if(!preg_match_all($reg, $xml, $matches))
		{
			return $xml;
		}
		$count = count($matches[0]);
		$array = array();
		for($i=0;$i<$count;$i++)
		{
			$id = $matches[1][$i];
			$attr = $this->_string_to_array($matches[2][$i]);
			$val = $this->xml_to_array($matches[3][$i]);
			if(is_string($val))
			{
				$val = preg_replace('/<\!\[CDATA\[(.+)\]\]>/isU','\\1',$val);
				//将值中的HTML标记更换
				$val = preg_replace('/\[html:(.+)\]/isU','<\\1>',$val);
				$val = preg_replace('/\[\/(.+):html\]/isU','</\\1>',$val);
			}
			if(isset($array[$id]))
			{
				if(!is_array($array[$id]) || $array[$id]['attr'] || $array[$id]['val'])
				{
					$tmp = $array[$id];
					unset($array[$id]);
					$array[$id][] = $tmp;
				}
				$array[$id][] = $attr ? array('attr'=>$attr,'val'=>$val) : $val;
			}
			else
			{
				$array[$id] = $attr ? array('attr'=>$attr,'val'=>$val) : $val;
				//$array[$id] = array('attr'=>$attr,'val'=>$val);
			}
		}
		return $array;
	}
}
?>
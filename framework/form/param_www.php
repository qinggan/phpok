<?php
/*****************************************************************************************
	文件： {phpok}/form/param_www.php
	备注： 规格参数前端数据操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月9日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class param_form
{
	function __construct()
	{
		//
	}

	//输出格式化后的内容
	function show($rs,$val='')
	{
		if(!$rs || !$rs['ext'])
		{
			return false;
		}
		if(!$val) $val = $rs['content'];
		if(!$val)
		{
			return false;
		}
		$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
		$list = unserialize($val);
		$pname = explode("\n",$ext['p_name']);
		$tlist = $list[0];
		$data = array();
		foreach($pname as $key=>$value)
		{
			if($tlist[$key])
			{
				$data['title'][$key] = $value;
			}
		}
		foreach($list as $key=>$value)
		{
			if($key<1)
			{
				continue;
			}
			foreach($value as $k=>$v)
			{
				if(!$tlist[$k])
				{
					continue;
				}
				if($ext['p_type'])
				{
					$data['content'][$key][$k] = $v;
				}
				else
				{
					$data['content'][$k] = $v;
				}
			}
		}
		return $data;
	}
}
?>
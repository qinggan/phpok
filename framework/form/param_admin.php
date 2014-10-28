<?php
/*****************************************************************************************
	文件： {phpok}/form/param_admin.php
	备注： 规格参数属性，支持列模式和行模式，暂不支持组功能
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年7月8日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class param_form
{
	function __construct()
	{
		//
	}

	//装载CSS+JS信息
	function cssjs()
	{
		//
	}

	function config()
	{
		$html =$GLOBALS['app']->dir_phpok."form/html/param_admin.html";
		$GLOBALS['app']->view($html,"abs-file");
	}

	//格式化表单
	function format($rs)
	{
		if(!$rs || !$rs['p_name'])
		{
			return false;
		}
		$pname = explode("\n",$rs['p_name']);
		if($rs['content'])
		{
			$list = unserialize($rs['content']);
		}
		else
		{
			$tmp = $tmp1 = array();
			foreach($pname as $key=>$value)
			{
				$tmp[$key] = 1;
				$tmp1[$key] = '';
			}
			$list = array(0=>$tmp,1=>$tmp1);
		}
		if($rs['p_type'])
		{
			$rs['p_width'] = intval($rs['p_width']) ? intval($rs['p_width']) : '80';
		}
		else
		{
			$rs['p_width'] = intval($rs['p_width']) ? intval($rs['p_width']) : '300';
		}
		$GLOBALS['app']->assign('_pname',$pname);
		$GLOBALS['app']->assign('_rslist',$list);
		$GLOBALS['app']->assign('_rs',$rs);
		$GLOBALS['app']->assign('_ptype',$rs['p_type']);
		$file =$GLOBALS['app']->dir_phpok."form/html/param_from_admin.html";
		$content = $GLOBALS['app']->fetch($file,'abs-file');
		$GLOBALS['app']->unassign("_rs");
		$GLOBALS['app']->unassign("_ptype");
		$GLOBALS['app']->unassign("_pname");
		$GLOBALS['app']->unassign("_rslist");
		return $content;
	}

	//获取内容
	function get($rs)
	{
		if(!$rs || !$rs['ext'])
		{
			return false;
		}
		$ext = unserialize($rs['ext']);
		if(!$ext['p_name'])
		{
			return false;
		}
		$pname = explode("\n",$ext['p_name']);
		$list = $tmp = array();
		foreach($pname as $key=>$value)
		{
			$tmp[$key] = $GLOBALS['app']->get($rs['identifier'].'_title_'.$key,'checkbox');
		}
		$p_count = count($pname);
		$list[0] = $tmp;
		if($ext['p_type'])
		{
			$tmp = $GLOBALS['app']->get($rs['identifier'].'_body');
			$tmp2 = array_chunk($tmp,$p_count);
			foreach($tmp2 as $key=>$value)
			{
				$list[] = $value;
			}
		}
		else
		{
			$list[1] = $GLOBALS['app']->get($rs['identifier'].'_body');
		}
		return serialize($list);
	}
}
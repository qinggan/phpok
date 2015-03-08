<?php
/*****************************************************************************************
	文件： {phpok}/form/title_www.php
	备注： 关联主题表单在前台的处理操作
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月26日 16时55分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class title_form
{
	public function show($rs,$val='')
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
		$ext = $rs['ext'] ? (is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext']) : array();
		if($ext['is_multiple'])
		{
			$list = explode(",",trim($val));
			$rslist = array();
			foreach($list as $key=>$value)
			{
				$rslist[$value] = $GLOBALS['app']->call->phpok('_arc',array('title_id'=>$value));
			}
		}
		else
		{
			$rslist = $GLOBALS['app']->call->phpok('_arc',array('title_id'=>$value));
		}
		return $rslist;
	}
}
?>
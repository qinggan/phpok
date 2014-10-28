<?php
/*****************************************************************************************
	文件： php/bbs_list.php
	备注： 格式化论坛主题
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月4日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//常用项目参数
//多少小时内发的贴子或回复，显示为最新主题
$hour = 6;
if($rslist)
{
	//格式化内容
	foreach($rslist as $key=>$value)
	{
		$icon = $value['toplevel'] ? 'bbs-icon-top'.$value['toplevel'] : 'bbs-icon-common';
		$time = $value['replydate'] ? $value['replydate'] : $value['dateline'];
		if(($time + $hour * 3600) > $sys['time'])
		{
			$icon = 'bbs-icon-new';
		}
		$value['_icon'] = $icon;
		$value['cate_id'] = $value['cate_id']['id'];
		$value['_author'] = $value['user_id'] ? $value['user_id']['user'] : '佚名';
		$value['_author_url'] = $value['user_id'] ? $app->url('user','info','id='.$value['user_id']['id']) : '';
		$value['_lastdate'] = $value['replydate'] ? date("Y-m-d",$value['replydate']) : date("Y-m-d",$value['dateline']);
		$rslist[$key] = $value;
	}
	//$this->assign('rslist',$rslist);
}
?>
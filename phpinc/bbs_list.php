<?php
/*****************************************************************************************
	文件： php/bbs_list.php
	备注： 格式化论坛主题
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年10月4日
*****************************************************************************************/
/**
 * 格式化论坛主题
 * @package phpok\phpinc
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月21日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//常用项目参数
//多少小时内发的贴子或回复，显示为最新主题
$hour = 6;
if($rslist){
	foreach($rslist as $key=>$value){
		$icon = $value['toplevel'] ? 'am-icon-angle-double-up'.$value['toplevel'] : 'am-icon-angle-right';
		$time = $value['replydate'] ? $value['replydate'] : $value['dateline'];
		if(($time + $hour * 3600) > $sys['time']){
			$icon = 'am-icon-angle-double-right am-primary';
		}
		$value['_icon'] = $icon;
		$value['_user'] = $value['user'] ? $value['user'] : array('user'=>'佚名');
		$value['_author'] = $value['_user']['user'];
		$value['_author_url'] = $value['user_id'] ? $app->url('user','info','id='.$value['user_id']) : '';
		$value['_lastdate'] = $value['replydate'] ? time_format($value['replydate']) : time_format($value['dateline']);
		$rslist[$key] = $value;
	}
}
<?php
/**
 * 显示会员组
 * @package phpok\phpinc
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月21日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
if($session['user_gid']){
	$usergroup = $app->model('usergroup')->get_one($session['user_gid']);
}
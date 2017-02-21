<?php
/**
 * phpexcel 类操作
 * @package phpok\extension
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年12月30日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class phpexcel_lib
{
	public function __construct()
	{
		require_once 'phar://' . ROOT . 'extension/phpexcel/phpexcel.phar';
	}
}

<?php
/**
 * 地址库相关操作
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月04日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class address_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function save_f()
	{
		$info = phpok_token();
		if(!$info){
			$this->error(P_Lang('数据错误，请检查'));
		}
	}

	
}

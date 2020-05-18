<?php
/**
 * 后台管理_实现微信的分享模式，支持是否关注公众号
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月29日 16时34分
**/
namespace phpok\app\control\wxshare;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('wxshare');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$this->display('admin_index');
	}
}

<?php
/**
 * 页面设计器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年05月30日
**/

class design_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('design');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 装载设计器
	**/
	public function index_f()
	{
		
	}

	
}

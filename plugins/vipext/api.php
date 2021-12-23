<?php
/**
 * PHPOK-VIP插件扩展<接口应用>
 * @package phpok\plugins
 * @作者 phpok.com
 * @版本 5.3.135
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月11日 10时21分
**/
class api_vipext extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 全局运行插件，在执行当前方法运行前，调整参数，如果不使用，请删除这个方法
	**/
	public function phpok_before()
	{
		//PHP代码;
	}
	
	/**
	 * 全局运行插件，在执行当前方法运行后，数据未输出前，如果不使用，请删除这个方法
	**/
	public function phpok_after()
	{
		//PHP代码;
	}
	
	
}
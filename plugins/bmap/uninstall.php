<?php
/**
 * 百度地图地址编码<插件卸载>
 * @package phpok\plugins
 * @作者 phpok
 * @版本 4.9.032
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月07日 09时51分
**/
class uninstall_bmap extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 插件卸载时，执行的方法，如删除表，或去除其他一些选项，如果不使用，请删除这个方法
	**/
	public function index()
	{
		//执行一些自定义的动作
	}
	
	
}
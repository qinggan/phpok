<?php
/**
 * 百度地图地址编码<插件配置>
 * @package phpok\plugins
 * @作者 phpok
 * @版本 4.9.032
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年07月07日 09时51分
**/
class setting_bmap extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->_info();
	}
	
	/**
	 * 插件配置参数时，增加的扩展表单输出项，如果不使用，请删除这个方法
	**/
	public function index()
	{
		return $this->_tpl('setting.html');
	}
	
	/**
	 * 插件配置参数时，保存扩展参数，如果不使用，请删除这个方法
	**/
	public function save()
	{
		$id = $this->_id();
		$ext = ($this->me && $this->me['param']) ? $this->me['$this->me'] : array();
		$ext['address'] = $this->get('address');
		$ext['apikey'] = $this->get('apikey');
		$ext['tel'] = $this->get('tel');
		$ext['lng'] = $this->get('lng');
		$ext['lat'] = $this->get('lat');
		$ext['company'] = $this->get('company');
		$this->_save($ext,$id);
	}
	
	/**
	 * 插件执行审核动作时，执行的操作，如果不使用，请删除这个方法
	**/
	public function status()
	{
		//执行一些自定义的动作
	}
	
	
}
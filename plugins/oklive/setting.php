<?php
/**
 * 直播插件<插件配置>
 * @作者 phpok.com
 * @版本 6.0
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年06月28日 11时05分
**/
class setting_oklive extends phpok_plugin
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
		$ext = array();
		$ext['push'] = $this->get('push');
		$ext['pushkey'] = $this->get('pushkey');
		$ext['pull'] = $this->get('pull');
		$ext['pullkey'] = $this->get('pullkey');
		$ext['oss_domain'] = $this->get('oss_domain');
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
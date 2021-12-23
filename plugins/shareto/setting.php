<?php
/**
 * 分享插件<插件配置>
 * @作者 phpok.com
 * @版本 6.0.007
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年05月19日 09时47分
**/
class setting_shareto extends phpok_plugin
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
		$ext['share-pc-status'] = $this->get('share-pc-status','checkbox');
		$ext['share-mobile-status'] = $this->get('share-mobile-status','checkbox');
		$ext['weixin-appid'] = $this->get('weixin-appid');
		//$ext['扩展参数字段名'] = $this->get('表单字段名');
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
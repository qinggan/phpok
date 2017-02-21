<?php
/*****************************************************************************************
	文件： plugins/sitemap/setting.php
	备注： 站长Sitemap地图设置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2014年6月2日
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class setting_sitemap extends phpok_plugin
{
	public $me;
	function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
		$this->tpl->assign('plugin',$this->me);
	}

	function index()
	{
		$glist = $this->model('gateway')->get_all(0);
		if(!$glist){
			$this->error('没有网关信息',$this->url('plugin'));
		}
		if(!$glist['sms']){
			$this->error('没有短信网关配置',$this->url('plugin'));
		}
		if(!$glist['sms']['list']){
			$this->error('请先配置好短信网关',$this->url('plugin'));
		}
		$this->assign('smslist',$glist['sms']['list']);
		return $this->_tpl('setting.html');
	}

	//存储扩展表单内容
	function save()
	{
		$id = $this->plugin_id();
		$ext = array();
		$ext['changefreq'] = $this->get('changefreq');
		$this->plugin_save($ext,$id);
	}
}
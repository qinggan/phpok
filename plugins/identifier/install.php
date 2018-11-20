<?php
/**
 * 插件安装
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class install_identifier extends phpok_plugin
{
	var $path;
	function __construct()
	{
		parent::plugin();
		$this->path = str_replace("\\","/",dirname(__FILE__)).'/';
	}

	public function index()
	{
		return $this->_tpl('setting.html');
	}

	//存储安装配置
	public function save()
	{
		$id = $this->_id();
		$ext = array();
		$ext['youdao_appid'] = $this->get('youdao_appid');
		$ext['youdao_appkey'] = $this->get('youdao_appkey');
		$ext['youdao_https'] = $this->get('youdao_https','int');
		$ext['phpok_appid'] = $this->get('phpok_appid');
		$ext['phpok_appkey'] = $this->get('phpok_appkey');		
		$this->_save($ext,$id);
	}
}
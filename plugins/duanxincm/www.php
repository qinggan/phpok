<?php
/*****************************************************************************************
	文件： plugins/duanxincm/www.php
	备注： 前台短信接入
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月04日 10时50分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class www_duanxincm extends phpok_plugin
{
	public $me;
	public function __construct()
	{
		parent::plugin();
		$this->me = $this->plugin_info();
	}

	public function html_register_index_phpokhead()
	{
		$this->show_tpl('js.html');
	}

	public function html_register_index_phpokbody()
	{
		$this->show_tpl('btn.html');
	}

	public function html_usercp_mobile_phpokhead()
	{
		$this->show_tpl('js.html');
	}

	public function html_usercp_mobile_phpokbody()
	{
		$this->show_tpl('btn2.html');
	}

}
?>
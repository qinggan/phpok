<?php
/*****************************************************************************************
	文件： {phpok}/form/pca_form.php
	备注： 省市县联动
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月02日 14时48分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class pca_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/pca_admin.html','abs-file');
	}

	//格式化内容
	public function phpok_format($rs,$appid='admin')
	{
		if($appid == 'admin'){
			return $this->_format_admin($rs);
		}else{
			return $this->_format_default($rs);
		}
	}

	private function _format_admin($rs)
	{
		$province = $this->lib('xml')->read($this->dir_root.'data/xml/provinces.xml');
		$this->assign('_province',$province['province']);
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok.'form/html/pca_admin_tpl.html','abs-file');
	}

	private function _format_default($rs)
	{
		$province = $this->lib('xml')->read($this->dir_root.'data/xml/provinces.xml');
		$this->assign('_province',$province['province']);
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok.'form/html/pca_www_tpl.html','abs-file');
	}
}
?>
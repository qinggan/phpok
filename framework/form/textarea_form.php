<?php
/*****************************************************************************************
	文件： {phpok}/form/textarea_form.php
	备注： 文本区内容
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月08日 23时06分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class textarea_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/textarea_admin.html','abs-file');
	}

	public function phpok_format($rs,$appid="admin")
	{
		if($appid == 'admin'){
			$width = '100%';
			$height = ($rs['height'] && intval($rs['height']) == $rs['height']) ? intval($rs['height']).'px' : ($rs['height'] ? $rs['height'] : '100px');
			$rs['height'] = $height;
			$this->assign('_rs',$rs);
			return $this->fetch($this->dir_phpok."form/html/textarea_admin_tpl.html",'abs-file');
		}
		$height = ($rs['height'] && intval($rs['height']) == $rs['height']) ? intval($rs['height']).'px' : ($rs['height'] ? $rs['height'] : '100px');
		$width = ($rs['width'] && intval($rs['width']) == $rs['width']) ? intval($rs['width']).'px' : ($rs['width'] ? $rs['width'] : '99%');
		$rs['width'] = $width;
		$rs['height'] = $height;
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok."form/html/textarea_www_tpl.html",'abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier'],$rs['format']);
	}

	public function phpok_show($rs,$appid="admin")
	{
		return $rs['content'];
	}
}
?>
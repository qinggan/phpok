<?php
/**
 * 字体图标后台可视化配置
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年1月30日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class fonticon_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function cssjs()
	{
		$this->addjs('js/form.select.js');
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/fonticon_admin.html','abs-file');
	}

	//格式化内容
	public function phpok_format($rs,$appid='admin')
	{
		if(!$rs['ext_cssfile']){
			return false;
		}
		$file = $this->dir_root.$rs['ext_cssfile'];
		if(!file_exists($file)){
			return false;
		}
		if(!$rs['ext_prefix']){
			$rs['ext_prefix'] = 'icon';
		}
		$this->addcss($rs['ext_cssfile']);
		$css = $this->lib("file")->cat($file);
		preg_match_all("/\.".$rs['ext_prefix']."-([a-z\-0-9]*):before\s*(\{|,)/isU",$css,$iconlist);
		$iconlist = $iconlist[1];
		sort($iconlist);
		$this->assign("_iconlist",$iconlist);
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/fonticon_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier']);
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		if($appid == 'admin'){
			$this->addcss($rs['ext_cssfile']);
			$_admin = array("id"=>$rs["content"],"type"=>"html");
			$_admin["info"] = '<i class="'.$rs['ext_prefix'].' '.$rs['content'].'"></i>';
			return array("info"=>$rs['content'],"_admin"=>$_admin);
		}
		return $rs['content'];
	}
}
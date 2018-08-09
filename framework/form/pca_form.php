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

	public function phpok_get($rs,$appid="admin")
	{
		$province = $this->get($rs['identifier'].'_p');
		if(!$province){
			return false;
		}
		$city = $this->get($rs['identifier'].'_c');
		$area = $this->get($rs['identifier'].'_a');
		if($area == $city){
			$area = '';
		}
		return $province.'/'.$city.'/'.$area;
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		return str_replace('/','',$rs['content']);
	}


	private function _format_admin($rs)
	{
		if($rs['content']){
			$tmp = explode('/',$rs['content']);
			$info = array('p'=>$tmp[0],'c'=>$tmp[1],'a'=>$tmp[2]);
			$rs['content'] = $info;
		}else{
			$rs['content'] = array('p'=>'','c'=>'','a'=>'');
		}
		$province = $this->lib('xml')->read($this->dir_data.'xml/provinces.xml');
		$this->assign('_province',$province['province']);
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok.'form/html/pca_admin_tpl.html','abs-file');
	}

	private function _format_default($rs)
	{
		if($rs['content'] && is_string($rs['content'])){
			$tmp = explode('/',$rs['content']);
			$info = array('p'=>$tmp[0],'c'=>$tmp[1],'a'=>$tmp[2]);
			$rs['content'] = $info;
		}else{
			if(!$rs['content']){
				$rs['content'] = array('p'=>'','c'=>'','a'=>'');
			}
		}
		$province = $this->lib('xml')->read($this->dir_data.'xml/provinces.xml');
		$this->assign('_province',$province['province']);
		$this->assign('_rs',$rs);
		return $this->fetch($this->dir_phpok.'form/html/pca_www_tpl.html','abs-file');
	}
}
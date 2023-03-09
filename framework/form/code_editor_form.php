<?php
/*****************************************************************************************
	文件： {phpok}/form/code_editor_form.php
	备注： 代码编辑框
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月12日 22时03分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class code_editor_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$list = $this->lib('file')->ls($this->dir_root.'static/codemirror/mode');
		if(!$list){
			$list = array();
		}
		foreach($list as $key=>$value){
			if(!is_dir($value)){
				unset($list[$key]);
				continue;
			}
			$list[$key] = basename($value);
		}
		$this->assign('attrs',$list);
		$theme_list = $this->lib('file')->ls($this->dir_root.'static/codemirror/theme');
		if(!$theme_list){
			$theme_list = array();
		}
		foreach($theme_list as $key=>$value){
			 $tmp = basename($value);
			 $tmp = substr($tmp,0,-4);
			 $theme_list[$key] = $tmp;
		}
		$this->assign('theme_list',$theme_list);
		$this->view($this->dir_phpok.'form/html/code_admin.html',"abs-file");
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->addcss("static/codemirror/lib/codemirror.css");
		$this->addjs("static/codemirror/lib/codemirror.js");
		if($rs['mode']){
			foreach($rs['mode'] as $key=>$value){
				$this->addjs('static/codemirror/mode/'.$key.'/'.$key.'.js');
				$cssfile = 'static/codemirror/mode/'.$key.'/'.$key.'.css';
				if(file_exists($this->dir_root.$cssfile)){
					$this->addcss($cssfile);
				}
			}
		}else{
			$this->addjs('static/codemirror/mode/css/css.js');
			$this->addjs('static/codemirror/mode/javascript/javascript.js');
			$this->addjs('static/codemirror/mode/htmlmixed/htmlmixed.js');
			$this->addjs('static/codemirror/mode/php/php.js');
			$this->addjs('static/codemirror/mode/xml/xml.js');
		}
		if($rs['theme']){
			$cssfile = 'static/codemirror/theme/'.$rs['theme'].'.css';
			if(file_exists($this->dir_root.$cssfile)){
				$this->addcss($cssfile);
			}
		}
		if($appid == 'admin'){
			$width = '100%';
			$height = ($rs['height'] && intval($rs['height']) == $rs['height']) ? intval($rs['height']).'px' : ($rs['height'] ? $rs['height'] : 'auto !important');
		}else{
			$height = ($rs['height'] && intval($rs['height']) == $rs['height']) ? intval($rs['height']).'px' : ($rs['height'] ? $rs['height'] : 'auto !important');
			$width = ($rs['width'] && intval($rs['width']) == $rs['width']) ? intval($rs['width']).'px' : ($rs['width'] ? $rs['width'] : '99%');
		}
		$rs["width"] = $width;
		$rs['height'] = $height;
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/code_admin_tpl.html','abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier'],'html_js');
	}

	public function phpok_show($rs,$appid="admin")
	{
		return $rs['content'];
	}
}
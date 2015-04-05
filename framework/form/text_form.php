<?php
/*****************************************************************************************
	文件： {phpok}/form/text.php
	备注： 文本框表单配置器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月27日 19时35分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class text_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	//此项限制后台使用
	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/text_admin.html','abs-file');
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

	public function phpok_get($rs,$appid='admin')
	{
		if(!$rs)
		{
			return false;
		}
		$array = array('int','intval','float','floatval','html','html_js','time');
		if(in_array($rs['format'],$array))
		{
			return $this->get($rs['identifier'],$rs['format']);
		}
		$info = $this->get($rs['identifier'],'html');
		if($info){
			$info = strip_tags($info);
		}
		return $info;
	}

	//输出显示的内容
	public function phpok_show($rs,$appid='admin')
	{
		if(!$rs || !$rs['content'])
		{
			return '';
		}
		if($appid == 'admin'){
			$ext = $rs['ext'];
			if($ext && is_string($ext)){
				$ext = unserialize($rs['ext']);
			}
			if($rs['format'] == 'time'){
				$format = $ext['form_btn'] == 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
				return date($format,$rs['content']);
			}
		}
		return $rs['content'];
	}

	private function _format_admin($rs)
	{
		if($rs['format'] == 'time')
		{
			$format = $rs['form_btn'] == "datetime" ? "Y-m-d H:i" : "Y-m-d";
			$time = $rs['content'] ? $rs['content'] : $this->time;
			$rs['content'] = date($format,$time);
		}
		//当有扩展按钮为颜色选择器时，加载颜色选择器的JS文件
		if($rs['form_btn'] == 'color')
		{
			$this->addjs('js/jscolor/jscolor.js');
		}
		if($rs['form_btn'] == 'date' || $rs['form_btn'] == 'datetime')
		{
			$this->addjs('js/laydate/laydate.js');
		}
		//未设置表单宽度时，使用200作为默认宽度
		if(!$rs['width'] || intval($rs['width'])<1)
		{
			$rs['width'] = '200';
		}
		//封装并格式化CSS
		$css = $rs['form_style'] ? $rs['form_style'].';width:'.intval($rs['width']).'px;' : 'width:'.intval($rs['width']).'px';
		$rs['form_style'] = $this->lib('common')->css_format($css);
		if($rs['ext_quick_words'])
		{
			$tmp = explode("\n",$rs['ext_quick_words']);
			foreach($tmp as $key=>$value)
			{
				if(!$value || !trim($value))
				{
					unset($tmp[$key]);
				}
				else
				{
					$tmp[$key] = trim($value);
				}
			}
			$rs['ext_quick_words'] = $tmp;
		}
		$this->assign('_rs',$rs);
		$file = $this->dir_phpok."form/html/text_admin_tpl.html";
		return $this->fetch($file,'abs-file');
	}

	private function _format_default($rs)
	{
		if($rs['format'] == 'time'){
			$format = $rs['form_btn'] == "datetime" ? "Y-m-d H:i" : "Y-m-d";
			$time = $rs['content'] ? $rs['content'] : $this->time;
			$rs['content'] = date($format,$time);
		}
		if(!$rs['width'] || intval($rs['width'])<1) $rs['width'] = '200';
		$css = $rs['form_style'] ? $rs['form_style'].';width:'.intval($rs['width']).'px;' : 'width:'.intval($rs['width']).'px';
		$rs['form_style'] = $this->lib('common')->css_format($css);
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok."form/html/text_www_tpl.html",'abs-file');
	}
}
?>
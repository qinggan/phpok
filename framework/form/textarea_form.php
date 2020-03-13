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
			//$width = $rs['width']>500 ? $rs['width'].'px' : '905px';
			$width = '100%';
			$height = intval($rs['height']) ? intval($rs['height']) : '100';
			$html .= '<textarea name="'.$rs["identifier"].'" id="'.$rs["identifier"].'" phpok_id="textarea" class="layui-textarea" ';
			$html .= 'style="'.$rs["form_style"].';width:'.$width.';height:'.$height.'px"';
			$html .= '>'.$rs["content"].'</textarea>';
			return $html;
		}else{
			$width = intval($rs['width']) ? intval($rs['width']).'px' : '100%';
			$height = intval($rs['height']) ? intval($rs['height']).'px' : '100px';
			$html  = '<table style="border:0;margin:0;padding:0" cellpadding="0" cellspacing="0"><tr><td>';
			$html .= '<textarea name="'.$rs["identifier"].'" id="'.$rs["identifier"].'" phpok_id="textarea" class="layui-textarea" ';
			$html .= 'style="'.$rs["form_style"].';width:'.$width.';height:'.$height.'"';
			$html .= ' placeholder="'.$rs['note'].'">'.$rs["content"].'</textarea>';
			$html .= "</td></tr></table>";
			return $html;
		}
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
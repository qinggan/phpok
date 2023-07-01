<?php
/**
 * Markdown 编辑器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2021年2月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class md_editor_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/md_editor_admin.html','abs-file');
	}

	public function cssjs()
	{
		$this->addjs('static/md-editor/editormd.min.js');
		$this->addcss('static/md-editor/editormd.css');
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->cssjs();
		if(!$rs['height']){
			$rs['height'] = 500;
		}
		$this->assign("_rs",$rs);
		if($appid == 'admin'){
			$file = $this->dir_phpok.'form/html/md_editor_admin_tpl.html';
		}else{
			$file = $this->dir_phpok.'form/html/md_editor_www_tpl.html';
		}
		return $this->fetch($file,'abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		if($rs['format'] != 'html_js'){
			return $this->get($rs['identifier'],'html');
		}
		return $this->get($rs['identifier'],'html_js');
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		if($appid == 'admin'){
			return $rs['content'];
		}else{
			$this->addcss('static/md-editor/editormd.css');
			$this->addjs('static/md-editor/lib/marked.min.js');
			$this->addjs('static/md-editor/lib/prettify.min.js');
			$this->addjs('static/md-editor/lib/raphael.min.js');
			$this->addjs('static/md-editor/lib/underscore.min.js');
			$this->addjs('static/md-editor/lib/sequence-diagram.min.js');
			$this->addjs('static/md-editor/lib/flowchart.min.js');
			$this->addjs('static/md-editor/lib/jquery.flowchart.min.js');
			$this->addjs('static/md-editor/editormd.min.js');
			$var = 'mdEditor_'.time();
			$html  = '<div id="doc-content-'.$rs['identifier'].'"><textarea style="display: none;">'.$rs['content'].'</textarea></div>'."\n";
			$html .= '<script type="text/javascript">'."\n";
			$html .= 'var '.$var.';'."\n";
			$html .= '$(document).ready(function(){'."\n";
			$html .= '	'.$var.' = editormd.markdownToHTML("doc-content-'.$rs['identifier'].'",{htmlDecode:"style,script,iframe",emoji:true,taskList:true,tex:true,flowChart:true,sequenceDiagram:true,codeFold:true})'."\n";
			$html .= '});'."\n";
			$html .= '</script>'."\n";
			return $html;
		}
	}
}
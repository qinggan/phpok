<?php
/*****************************************************************************************
	文件： {phpok}/form/editor_form.php
	备注： 可视化编辑器配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月12日 22时37分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class editor_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function phpok_config()
	{
		$this->view($this->dir_phpok.'form/html/editor_admin.html','abs-file');
	}

	public function cssjs()
	{
		$this->addjs('js/ueditor/ueditor.config.js');
		$this->addjs('js/ueditor/ueditor.all.min.js');
		$this->addjs('js/ueditor/lang/zh-cn/zh-cn.js');
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->addjs('js/ueditor/ueditor.config.js');
		$this->addjs('js/ueditor/ueditor.all.min.js');
		$this->addjs('js/ueditor/lang/zh-cn/zh-cn.js');
		if($appid == 'admin'){
			$rs['width'] = '100%';
		}
		$style = array();
		if($rs['form_style']){
			$list = explode(";",$rs['form_style']);
			foreach($list AS $key=>$value){
				$tmp = explode(":",$value);
				if($tmp[0] && $tmp[1] && trim($tmp[1])){
					$style[strtolower($tmp[0])] = trim($tmp[1]);
				}
			}
		}
		if($rs['width']){
			if($this->is_mobile && $appid != 'admin'){
				$style['width'] = '100%';
				$rs['width'] = '100%';
			}else{
				$style["width"] = ($rs['width'] && $rs['width'] != '100%') ? $rs['width'].'px' : $rs['width'];
			}
		}
		if($rs['height']){
			$style["height"] = $rs['height'].'px';
		}
		$rs['form_style'] = '';
		foreach($style AS $key=>$value){
			if($rs['form_style']) $rs['form_style'] .= ';';
			$rs['form_style'] .= $key.':'.$value;
		}
		$this->assign("_rs",$rs);
		if($appid == 'admin'){
			$save_path = $this->model('res')->cate_all();
			if($save_path){
				$save_path_array = array();
				foreach($save_path AS $key=>$value){
					$save_path_array[] = $value['title'];
				}
				$save_path = "['". implode("','",$save_path_array) ."']";
			}else{
				$save_path = '["默认分类"]';
			}
			$this->assign("_save_path",$save_path);
			$file = $this->dir_phpok.'form/html/editor_admin_tpl.html';
		}else{
			$file = $this->dir_phpok.'form/html/editor_www_tpl.html';
		}
		return $this->fetch($file,'abs-file');
	}

	public function phpok_get($rs,$appid="admin")
	{
		if($rs['format'] != 'html_js'){
			return $this->get($rs['identifier'],'html');
		}else{
			return $this->get($rs['identifier'],'html_js');
		}
	}

	public function phpok_show($rs,$appid="admin")
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		if($appid == 'admin'){
			return $this->lib('string')->cut($rs['content'],100);
		}else{
			if(!$rs['pageid']) $rs['pageid'] = 1;
			$lst = explode('[:page:]',$rs['content']);
			$total = count($lst);
			if($total<=1){
				return $this->lib('ubb')->to_html($rs['content'],false);
			}
			$t = $rs['pageid']-1;
			if($lst[$t]){
				$array = array();
				for($i=0;$i<$total;$i++){
					$array[$i] = $i+1;
				}
				$lst[$t] = $this->lib('ubb')->to_html($lst[$t],false);
				return array('pagelist'=>$array,'content'=>$lst[$t]);
			}
			return $this->lib('ubb')->to_html($lst[0]);
		}
	}
}
?>
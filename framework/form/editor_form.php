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
		$this->addjs('js/ueditor/extension/quickformat/button.js?v=3');
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->cssjs();
		$style = array();
		if($rs['form_style']){
			$list = explode(";",$rs['form_style']);
			foreach($list as $key=>$value){
				$tmp = explode(":",$value);
				if($tmp[0] && $tmp[1] && trim($tmp[1])){
					$style[strtolower($tmp[0])] = trim($tmp[1]);
				}
			}
		}
		$style['width'] = '100%';
		if($rs['height']){
			$style["height"] = $rs['height'].'px';
		}
		$rs['form_style'] = '';
		foreach($style AS $key=>$value){
			if($rs['form_style']) $rs['form_style'] .= ';';
			$rs['form_style'] .= $key.':'.$value;
		}
		$btns = array();
		$btns["image"] = true;
		$btns["info"] = true;
		$btns["video"] = true;
		$btns["file"] = true;
		$btns["page"] = true;
		$btns["table"] = true;
		$btns["emotion"] = true;
		$btns["map"] = true;
		$btns["spechars"] = true;
		$btns["insertcode"] = true;
		$btns["paragraph"] = true;
		$btns["fontsize"] = true;
		$btns["fontfamily"] = true;
		if($appid == 'admin' && !$rs['btns']){
			$rs['btns'] = $btns;
		}
		if(!$rs['btns']){
			$rs['btns'] = array();
		}
		$this->assign("_rs",$rs);
		if($appid == 'admin'){
			$save_path = $this->model('res')->cate_all();
			if($save_path){
				$save_path_array = array();
				foreach($save_path as $key=>$value){
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
			return $this->lib('string')->cut($rs['content'],10000);
		}else{
			if(!$rs['pageid']) $rs['pageid'] = 1;
			$rs['content'] = $this->lib('ubb')->to_html($rs['content'],false);
			$lst = explode('[:page:]',$rs['content']);
			$total = count($lst);
			if($total<=1){
				return $rs['content'];
			}
			$tmp = array();
			$array = array();
			for($i=0;$i<$total;$i++){
				$array[$i] = $i+1;
			}
			$tmp['pagelist'] = $array;
			$t = $rs['pageid']-1;
			if($lst[$t]){
				$tmp['content'] = $lst[$t];
			}
			$tmp['list'] = $lst;
			return $tmp;
		}
	}
}
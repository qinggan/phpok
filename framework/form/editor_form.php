<?php
/**
 * HTML编辑器配置
 * @作者 苏相锟 <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 GNU Lesser General Public License https://www.phpok.com/lgpl.html 
 * @时间 2021年5月11日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class editor_form extends _init_auto
{
	private $langcode = 'zh-cn';
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
		$this->addjs('static/ckeditor/ckeditor.js');
		if($this->langid == 'en_US' || $this->langid == 'en'){
			$this->langcode = 'en';
		}
		if($this->langid == 'big5' || $this->langid == 'zh'){
			$this->langcode = 'zh';
		}
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
		foreach($style as $key=>$value){
			if($rs['form_style']) $rs['form_style'] .= ';';
			$rs['form_style'] .= $key.':'.$value;
		}
		$btns = array();
		$btns["image"] = true;
		$btns["info"] = true;
		$btns["video"] = false;
		$btns["audio"] = false;
		$btns["file"] = false;
		$btns["page"] = false;
		$btns["table"] = true;
		$btns["emotion"] = true;
		$btns["map"] = false;
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
		$rs['config'] = array();
		$rs['config']['langid'] = $this->langcode;
		$rs['config']['height'] = $rs['height'];
		//修正高亮插件代码被改写Bug，主要是针对尖括号
		if($rs['content'] && strpos($rs['content'],'<pre') !== false){
			preg_match_all("/<pre(.*)>(.*)<\/pre>/isU",$rs['content'],$matches);
			if($matches && $matches[0] && $matches[1] && $matches[2]){
				$old = array("&lt;","&gt;");
				$new = array("&amp;lt;","&amp;gt;");
				foreach($matches[0] as $key=>$value){
					$tmp = str_replace($old,$new,$value);
					$rs['content'] = str_replace($value,$tmp,$rs['content']);
				}
			}
			//echo "<pre>".print_r($matches,true)."</pre>";
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
		//移除插件
		$removePlugins = array();
		if(!$rs['auto_height']){
			$removePlugins[] = 'autogrow';
		}
		if(!$rs['is_float']){
			$removePlugins[] = 'fixed';
		}
		if($rs['is_code']){
			$removePlugins[] = 'autogrow';
			$removePlugins[] = 'fixed';
		}
		$removePlugins = array_unique($removePlugins);
		$this->assign('_removePlugins',implode(",",$removePlugins));
		//本地化的域名
		$domain = $this->lib('server')->domain($this->config['get_domain_method']);
		$tmp = array('localhost','127.0.0.1','::1');
		if($domain && !in_array($domain,$tmp)){
			$tmp[] = $domain;
		}
		$tmp_xml = $this->model('res')->remote_config();
		$domainlist = '*';
		if($tmp_xml && $tmp_xml['domain1']){
			$tmp = explode("\n",$tmp_xml['domain1']);
			if($domain){
				$tmp[] = $domain;
			}
		}
		if($tmp_xml && $tmp_xml['domain2']){
			$tmplist = explode("\n",$tmp_xml['domain2']);
			$dlist = array();
			$is_all = false;
			foreach($tmplist as $key=>$value){
				if($value && trim($value) =='*'){
					$is_all = true;
					break;
				}
				if($value && trim($value) && trim($value) != '*'){
					$dlist[] = trim($value);
				}
			}
			if(!$is_all && $dlist){
				$domainlist = implode(",",$dlist);
			}
		}
		$this->assign('_remoteDomain',$domainlist);
		$this->assign('_ignoreDomain',implode(",",$tmp));
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
			if(!isset($rs['pageid'])){
				$rs['pageid'] = 1;
			}
			$rs['content'] = $this->lib('ubb')->to_html($rs['content'],false);
			$rs['content'] = preg_replace("/<div[^>]*page-break-after:always[^>]*>\s*<span[^>]*>\s*\[:page:\]\s*<\/span>\s*<\/div>/isU",'[:page:]',$rs['content']);
			//$rs['content'] = str_replace('<span style="display:none">[:page:]</span>',"[:page:]",$rs['content']);
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
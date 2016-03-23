<?php
/***********************************************************
	Filename: {phpok}/js_control.php
	Note	: JS控制器，这里用来控制后台的JS信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-29 20:22
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class js_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	//WEB前台通用JS
	public function index_f()
	{
		$this->js_base();
		echo $this->lib('file')->cat($this->dir_phpok."form.js");
		echo "\n";
		$ext = $this->get("ext");
		$_ext = $this->get('_ext');
		$autoload_js = $this->config["autoload_js"];
		if($autoload_js){
			$ext = $ext ? $ext.",".$autoload_js : $autoload_js;
		}
		if(!$ext && !$_ext){
			exit;
		}
		$list = explode(",",$ext);
		$list = array_unique($list);
		$forbid_ext = $_ext ? explode(",",$_ext) : array();
		$is_artdialog = false;
		foreach($list AS $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			if($value && in_array($value,$forbid_ext)){
				continue;
			}
			if($value && strtolower(substr($value,-3)) != '.js'){
				$value .= '.js';
			}
			$jsfile = is_file($this->dir_root."js/".$value) ? $this->dir_root."js/".$value : $this->dir_phpok."js/".$value;
			if($value && is_file($jsfile) && $value != "jquery.js"){
				echo $this->lib('file')->cat($jsfile);
				if($value == 'jquery.artdialog.js'){
					$this->js_artdialog_global_config();
				}
				echo "\n";
			}
		}
		exit;
	}

	//人工指定js
	public function ext_f()
	{
		header("Content-type: application/x-javascript; charset=UTF-8");
		$js = $this->get("js");
		if(!$js) exit("\n");
		$list = explode(",",$js);
		echo "\n";
		$is_artdialog = false;
		foreach($list AS $key=>$value){
			$value = trim($value);
			if(!$value) continue;
			//判断后缀是否是.js
			if(strtolower(substr($value,-3)) != '.js') $value .= '.js';
			//判断文件是否存在
			$jsfile = is_file($this->dir_root."js/".$value) ? $this->dir_root."js/".$value : $this->dir_phpok."js/".$value;
			if(is_file($jsfile)){
				echo "\n";
				echo $this->lib('file')->cat($jsfile);
				echo "\n";
				if($value == 'jquery.artdialog.js'){
					$this->js_artdialog_global_config();
				}
			}
		}
	}

	public function mini_f()
	{
		$this->js_base();
		$ext = $this->get('ext');
		if($ext){
			$list = explode(",",$ext);
			foreach($list AS $key=>$value){
				$value = trim($value);
				if(!$value) continue;
				if(strtolower(substr($value,-3)) != '.js') $value .= '.js';
				$file = is_file($this->dir_phpok.'js/'.$value) ? $this->dir_phpok.'js/'.$value : $this->dir_root."js/".$value;
				if(is_file($file)){
					echo "\n";
					echo $this->lib('file')->cat($file);
					echo "\n";
					if($value == 'jquery.artdialog.js'){
						$this->js_artdialog_global_config();
					}
				}
			}
		}
	}

	private function js_artdialog_global_config()
	{
		echo "\n";
		echo '(function (config) {'."\n\t";
		echo 'config["title"] = "'.P_Lang('消息').'";'."\n\t";
		echo 'config["okVal"] = "'.P_Lang('确定').'";'."\n\t";
		echo 'config["cancelVal"] = "'.P_Lang('取消').'";'."\n";
		echo '})(art.dialog.defaults);'."\n";
	}

	//最小化加载js
	private function js_base()
	{
		header("Content-type: application/x-javascript; charset=UTF-8");
		$file = $_SERVER["SCRIPT_NAME"] ? basename($_SERVER["SCRIPT_NAME"]) : basename($_SERVER["SCRIPT_FILENAME"]);
		//加载配置常用的JS
		$weburl = $this->get_url();
		echo 'var basefile = "'.$file.'";'."\n";
		echo 'var ctrl_id = "'.$this->config['ctrl_id'].'";'."\n";
		echo 'var func_id = "'.$this->config['func_id'].'";'."\n";
		echo 'var webroot = "'.$weburl.'";'."\n";
		echo 'var apifile = "'.$this->config['api_file'].'";'."\n";
		//echo 'var langid = "'.$this->langid.'"'."\n";
		echo 'var lang= new Array();'."\n";
		$js_default_file = $this->dir_root.'langs/'.$this->app_id.'.xml';
		$default_list = $this->lib('xml')->read($js_default_file);
		if(!$default_list){
			$default_list = array();
		}
		$langs = false;
		if($this->langid != 'default' && $this->langid != 'cn'){
			$langfile = $this->dir_root."langs/".$this->langid."/LC_MESSAGES/".$this->app_id.".xml";
			$langlist = $this->lib('xml')->read($langfile);
			if($langlist){
				foreach($langlist as $key=>$value){
					if($default_list[$key]){
						$langs[$default_list[$key]] = $value;
					}
				}
			}
		}
		if($langs){
			foreach($langs as $key=>$value){
				echo 'lang["'.$key.'"] = "'.$value.'";'."\n";
			}
		}
		echo "\n";
		echo 'function get_url(ctrl,func,ext){var url = "'.$weburl.$file.'?'.$this->config['ctrl_id'].'="+ctrl;if(func){url+="&'.$this->config['func_id'].'="+func;};if(ext){url+="&"+ext};return url;}';
		echo "\n";
		echo 'function api_url(ctrl,func,ext){var url = "'.$weburl.$this->config['api_file'].'?'.$this->config['ctrl_id'].'="+ctrl;if(func){url+="&'.$this->config['func_id'].'="+func;};if(ext){url+="&"+ext};url+="&_noCache="+Math.random();return url;};';
		echo "\n";
		echo 'function api_plugin_url(id,func,ext){var url = "'.$weburl.$this->config['api_file'].'?'.$this->config['ctrl_id'].'=plugin&'.$this->config['func_id'].'=index&id="+id+"&exec="+func;if(ext){url+="&"+ext};url+="&_noCache="+Math.random();return url;};';
		echo "\n";
		//实现jQuery.js文件的自定义
		if($this->app_id != 'admin'){
			$file = $this->dir_root.$this->tpl->dir_tpl.'js/jquery.js';
			if(!file_exists($file)){
				$file = $this->dir_root.$this->tpl->dir_tpl.'javascript/jquery.js';
			}
			if(!file_exists($file)){
				$file = $this->dir_root.'js/jquery.js';
			}
		}else{
			$file = $this->dir_root.'js/jquery.js';
		}
		echo $this->lib('file')->cat($file);
		echo "\n";
	}
}
?>
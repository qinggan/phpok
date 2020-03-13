<?php
/**
 * JS 控制器
 * @package phpok\framework
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年11月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class js_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 通用 JS，包括加载 form.js
	**/
	public function index_f()
	{
		$this->js_base();
		echo $this->lib('file')->cat($this->dir_phpok."form.js");
		echo "\n";
		if($this->app_id == 'admin'){
			echo $this->lib('file')->cat($this->dir_phpok."admin.form.js");
			echo "\n";
		}
		$list = array();
		$ext = $this->get("ext","safe_text");
		if($ext){
			$tmp = explode(",",$ext);
			foreach($tmp as $key=>$value){
				if($value && trim($value)){
					$list[] = $value;
				}
			}
		}
		$autoload_js = $this->config["autoload_js"];
		if($autoload_js){
			$tmp = explode(",",$autoload_js);
			foreach($tmp as $key=>$value){
				if($value && trim($value)){
					$list[] = $value;
				}
			}
		}
		if($this->is_mobile){
			if($this->config['mobile']['includejs']){
				$tmp = explode(",",$this->config['mobile']['includejs']);
				foreach($tmp as $key=>$value){
					if($value && trim($value)){
						$list[] = $value;
					}
				}
			}
		}else{
			if($this->config['pc']['includejs']){
				$tmp = explode(",",$this->config['pc']['includejs']);
				foreach($tmp as $key=>$value){
					if($value && trim($value)){
						$list[] = $value;
					}
				}
			}
		}
		$myctrl = $this->get('_ctrl');
		$myfunc = $this->get('_func');
		if($myctrl && is_file($this->dir_phpok.'js/'.$this->app_id.'.'.$myctrl.'.js')){
			$list[] = $this->app_id.'.'.$myctrl.'.js';
		}
		if($myctrl && $myfunc && is_file($this->dir_phpok.'js/'.$this->app_id.'.'.$myctrl.'-'.$myfunc.'.js')){
			$list[] = $this->app_id.'.'.$myctrl.'-'.$myfunc.'.js';
		}
		if($this->app_id == 'admin' && $myctrl){
			$list[] = $myctrl.'/admin.js';
		}
		if($this->app_id == 'admin' && $myctrl && $myfunc){
			$list[] = $myctrl.'/admin.'.$myfunc.'.js';
		}
		//自动装载前端下的js
		if($this->app_id == 'www'){
			$tlist = $this->model('url')->protected_ctrl();
			if($tlist){
				foreach($tlist as $key=>$value){
					if(is_file($this->dir_app.$value.'/'.$this->app_id.'.js')){
						$list[] = $value.'/'.$this->app_id.'.js';
					}
				}
			}
		}
		$list = array_unique($list);
		$_ext = $this->get('_ext');
		if($this->is_mobile){
			if($this->config['mobile']['excludejs']){
				$_ext = $_ext ? $_ext.",".$this->config['mobile']['excludejs'] : $this->config['mobile']['excludejs'];
			}
		}else{
			if($this->config['pc']['excludejs']){
				$_ext = $_ext ? $_ext.",".$this->config['pc']['excludejs'] : $this->config['pc']['excludejs'];
			}
		}
		if($_ext){
			$forbid_ext = is_string($_ext) ? explode(",",$_ext) : $_ext;
			$list = array_diff($list,$forbid_ext);
		}
		if(!$list){
			exit;
		}
		if($this->app_id == 'admin'){
			$this->load_ext($list,true);
			exit;
		}
		$this->load_ext($list,false);
		exit;
	}

	/**
	 * 加载扩展JS，不包括核心JS
	**/
	public function ext_f()
	{
		ob_start();
		header("Content-type: application/x-javascript; charset=UTF-8");
		$js = $this->get("js");
		if(!$js){
			exit("\n");
		}
		$this->load_ext($js,false);
	}

	/**
	 * 最小核心JS加载（只加载 jquery.js 及 system.js 文件）
	**/
	public function mini_f()
	{
		$this->js_base();
		$ext = $this->get('ext');
		if($ext){
			$this->load_ext($ext,true);
		}
	}

	private function load_ext($ext,$is_admin=false)
	{
		if(!$ext){
			return false;
		}
		$list = is_string($ext) ? explode(",",$ext) : $ext;
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$value = trim($value);
			if(strtolower(substr($value,-3)) != '.js'){
				$value .= '.js';
			}
			$tmplist = array($this->dir_root.'js/'.$value);
			$tmplist[] = $this->dir_phpok.'js/'.$value;
			$tmplist[] = $this->dir_app.$value;
			$file = '';
			foreach($tmplist as $k=>$v){
				if(is_file($v)){
					$file = $v;
					break;
				}
			}
			if(!$file){
				continue;
			}
			if($file && is_file($file)){
				echo "\n";
				echo $this->lib('file')->cat($file);
				echo "\n";
			}
			if($value == 'jquery.artdialog.js'){
				echo "\n";
				$this->js_artdialog_global_config();
				echo "\n";
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
		if($this->app_id == 'admin'){
			echo 'config["opacity"] = "0.2";'."\n";
		}
		echo '})(art.dialog.defaults);'."\n";
	}

	/**
	 * 加载基本的JS
	**/
	private function js_base()
	{
		ob_start();
		header("Content-type: text/javascript; charset=utf-8");
		$file = $this->app_id == 'admin' ? $this->config['admin_file'] : $this->config['www_file'];
		$this->assign('basefile',$file);
		$this->load_language_js();
		$file = $this->dir_root.'js/jquery.js';
		//实现jQuery.js文件的自定义
		if($this->app_id != 'admin'){
			if(is_file($this->dir_root.$this->tpl->dir_tpl.'js/jquery.js')){
				$file = $this->dir_root.$this->tpl->dir_tpl.'js/jquery.js';
			}
			if(is_file($this->dir_root.$this->tpl->dir_tpl.'js/jquery.min.js')){
				$file = $this->dir_root.$this->tpl->dir_tpl.'js/jquery.min.js';
			}
		}
		$jquery = $this->lib('file')->cat($file);
		$this->assign('jquery',$jquery);
		if($this->app_id == 'www'){
			if(defined('PHPOK_SITE_ID')){
				$this->assign('phpok_site_id',PHPOK_SITE_ID);
			}else{
				$this->assign('phpok_site_id',$this->site['id']);
			}
			$this->assign('site_id',$this->site['id']);
		}elseif($this->app_id == 'admin'){
			$this->assign('phpok_site_id',$this->session->val('admin_site_id'));
			$this->assign('site_id',$this->session->val('admin_site_id'));
		}
		$this->tpl->output($this->dir_phpok.'system.js','abs-file',false);
	}

	/**
	 * 加载JS下的语言包
	**/
	private function load_language_js()
	{
		$multiple_language = isset($GLOBALS['app']->config['multiple_language']) ? $GLOBALS['app']->config['multiple_language'] : false;
		if(!$multiple_language){
			return false;
		}
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
			$this->assign('langs',$langs);
		}
	}
}
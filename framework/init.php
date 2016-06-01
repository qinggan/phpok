<?php
/***********************************************************
	Filename: phpok/init.php
	Note	: PHPOK框架入口引挈文件，请不要改动此文件
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-15 15:30
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
//强制使用UTF-8编码
header("Content-type: text/html; charset=utf-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT");
header("Cache-control: no-cache,no-store,must-revalidate,max-age=1");
header("Pramga: no-cache"); 

//计算执行的时间
function run_time($is_end=false)
{
	if(!$is_end){
		if(defined("SYS_TIME_START")){
			return false;
		}
		define("SYS_TIME_START",microtime(true));
	}else{
		if(!defined("SYS_TIME_START")){
			return false;
		}
		return round((microtime(true) - SYS_TIME_START),5);
	}
}

//登记内存
function run_memory($is_end=false)
{
	if(!$is_end){
		if(defined("SYS_MEMORY_START") || !function_exists("memory_get_usage")){
			return false;
		}
		define("SYS_MEMORY_START",memory_get_usage());
	}else{
		if(!defined("SYS_MEMORY_START")){
			return false;
		}
		$memory = memory_get_usage() - SYS_MEMORY_START;
		//格式化大小
		if($memory <= 1024){
			$memory = "1KB";
		}elseif($memory>1024 && $memory<(1024*1024)){
			$memory = round(($memory/1024),2)."KB";
		}else{
			$memory = round(($memory/(1024*1024)),2)."MB";
		}
		return $memory;
	}
}

run_time();
run_memory();

function debug_time()
{
	$time = run_time(true);
	$memory = run_memory(true);
	$sql_db_count = $GLOBALS['app']->db->sql_count();
	$sql_db_time = $GLOBALS['app']->db->sql_time();
	$cache_count = $GLOBALS['app']->cache->count();
	$cache_time = $GLOBALS['app']->cache->time();
	$string = '运行{total}秒，内存使用{mem_total}，数据库执行{sql_count}次，';
	$string.= '用时{sql_time}秒，缓存执行{cache_count}次，用时{cache_time}秒';
	$array = array('total'=>$time,'mem_total'=>$memory);
	$array['sql_count']=$GLOBALS['app']->db->sql_count();
	$array['sql_time'] = $GLOBALS['app']->db->sql_time();
	$array['cache_count'] = $GLOBALS['app']->cache->count();
	$array['cache_time'] = $GLOBALS['app']->cache->time();
	$string = P_Lang($string,$array);
	$db_debug = $GLOBALS['app']->db->debug();
	if($db_debug && is_string($db_debug)){
		$string .= $db_debug;
	}
	$cache_debug = $GLOBALS['app']->cache->debug();
	if($cache_debug){
		$string .= $cache_debug;
	}
	return $string;
}

//PHPOK4最新框架，其他应用可直接通过该框架调用
class _init_phpok
{
	//应用ID
	public $app_id = "www";
	//网站根目录
	public $dir_root = "./";
	//框架目录
	public $dir_phpok = "phpok/";
	//引挈库
	public $engine;
	//应用
	public $obj;
	//public $obj_list;
	//配置信息
	public $config;
	//版本
	public $version = "4.0";
	//当前时间
	public $time;
	//网址
	public $url;
	//缓存信息（任意接口都可以通过获取该缓存信息）
	public $cache_data;
	//授权相关信息
	public $license = "LGPL";
	public $license_code = "ED988858BCB1903A529C762DBA51DD40";
	public $license_date = "2012-10-29";
	public $license_name = "phpok";
	public $license_site = "phpok.com";
	public $license_powered = true;

	//是否是手机端，如果使用手机端可能会改写网址
	public $is_mobile = false;

	//定义插件
	public $plugin = '';

	//定义css列表和js列表
	public $csslist;
	public $jslist;

	//读语言包方式
	public $lang;
	public $langid;
	public $js_langlist;
	public $language_status = 'gettext';

	public $gateway;
	public $api_code;

	public $token;

	//数据传输是否使用Ajax
	public $is_ajax = false;

	public function __construct()
	{
		if(version_compare(PHP_VERSION, '5.3.0', '<') && function_exists('set_magic_quotes_runtime')){
			ini_set("magic_quotes_runtime",0);
		}
		$this->init_constant();
		$this->init_config();
		$this->init_engine();
	}

	public function __destruct()
	{
		unset($this);
	}

	private function init_assign()
	{
		$url = $this->url;
		$afile = $this->config[$this->app_id.'_file'];
		if(!$afile){
			$afile = 'index.php';
		}
		$url .= $afile;
		if($_SERVER['QUERY_STRING']){
			$url .= "?".$_SERVER['QUERY_STRING'];
		}
		$this->site["url"] = $url;
		$this->config["url"] = $this->url;
		$this->config['app_id'] = $this->app_id;
		$this->config['time'] = $this->time;
		$this->assign("sys",$this->config);
		$this->phpok_seo($this->site);
		$this->assign("config",$this->site);
		$langid = $this->get("_langid");
		if($this->app_id == 'admin'){
			if(!$langid){
				$langid = (isset($_SESSION['admin_lang_id']) && $_SESSION['admin_lang_id']) ? $_SESSION['admin_lang_id'] : 'default';
			}
			$_SESSION['admin_lang_id'] = $langid;
		}else{
			if(!$langid){
				$langid = isset($this->site['lang']) ? $this->site['lang'] : 'default';
			}
		}
		$this->langid = $langid;
		$this->language($langid);
	}

	public function language($langid='default')
	{
		if(!function_exists('gettext')){
			$this->language_status = 'user';
			$mofile = $this->dir_root.'langs/'.$langid.'/LC_MESSAGES/'.$this->app_id.'.mo';
			if(!is_readable($mofile)){
				return false;
			}
			$this->lang = $this->lib('pomo')->lang($mofile);
		}else{
			$this->language_status = 'gettext';
			if($langid != 'default' && $langid != 'cn'){
				putenv('LANG='.$langid);
				setlocale(LC_ALL,$langid);
				bindtextdomain($this->app_id,$this->dir_root.'langs');
				textdomain($this->app_id);
			}else{
				putenv('LANG=zh_CN');
				setlocale(LC_ALL,'zh_CN');
				bindtextdomain($this->app_id,$this->dir_root.'langs');
				textdomain($this->app_id);
			}
		}
	}

	//语言包变量格式化
	final public function lang_format($info,$var)
	{
		if(!$info) return false;
		if(!$var || !is_array($var)) return $info;
		foreach($var AS $key=>$value)
		{
			$info = str_replace('{'.$key.'}',$value,$info);
		}
		return $info;
	}

	//加载视图引挈
	public function init_view()
	{
		include_once($this->dir_phpok."phpok_tpl.php");
		$this->model('url')->ctrl_id($this->config['ctrl_id']);
		$this->model('url')->func_id($this->config['func_id']);
		if($this->app_id == "admin"){
			$tpl_rs = array();
			$tpl_rs["id"] = "1";
			$tpl_rs["dir_tpl"] = substr($this->dir_phpok,strlen($this->dir_root))."/view/";
			$tpl_rs["dir_cache"] = $this->dir_root."data/tpl_admin/";
			$tpl_rs["dir_php"] = $this->dir_root;
			$tpl_rs["dir_root"] = $this->dir_root;
			$tpl_rs["refresh_auto"] = true;
			$tpl_rs["tpl_ext"] = "html";
			//定制语言模板ID
			$tpl_rs['langid'] = isset($_SESSION['admin_lang_id']) ? $_SESSION['admin_lang_id'] : 'default';
			$this->tpl = new phpok_tpl($tpl_rs);
			unset($tpl_rs);
		}else{
			if(!$this->site["tpl_id"] || ($this->site["tpl_id"] && !is_array($this->site["tpl_id"]))){
				$this->_error("未指定模板文件");
			}
			$this->model('url')->base_url($this->url);
			$this->model('url')->set_type($this->site['url_type']);
			$this->model('url')->protected_ctrl($this->config['reserved']);
			//初始化伪静态中需要的东西
			if($this->site['url_type'] == 'rewrite'){
				$this->model('url')->site_id($this->site['id']);
				$this->model('rewrite')->site_id($this->site['id']);
				$this->model('url')->global_list();
				$this->model('url')->rules($this->model('rewrite')->get_all());
				$this->model('url')->page_id($this->config['pageid']);
			}
			$this->tpl = new phpok_tpl($this->site["tpl_id"]);
			include($this->dir_phpok."phpok_call.php");
			$this->call = new phpok_call();
		}
		include_once($this->dir_phpok."phpok_tpl_helper.php");
	}

	//手机判断
	//使用第三方类
	public function is_mobile()
	{
		if($this->lib('mobile')->is_mobile()){
			return true;
		}
		return false;
	}

	public function init_site()
	{
		if($this->app_id == "admin"){
			if($_SESSION['admin_site_id']){
				$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
			}else{
				$site_rs = $this->model("site")->get_one_default();
			}
			if(!$site_rs){
				$site_rs = array('title'=>'PHPOK.Com');
			}
			$this->site = $site_rs;
			return true;
		}
		if($this->app_id == 'www' && $this->config['mobile']['status']){
			$this->is_mobile = $this->config['mobile']['default'];
			if(!$this->is_mobile && $this->config['mobile']['autocheck']){
				$this->is_mobile = $this->is_mobile();
			}
		}
		$site_id = $this->get("siteId","int");
		$domain = strtolower($_SERVER[$this->config['get_domain_method']]);
		if(!$site_id){
			$site_id = $domain;
			if(!$site_id){
				$this->_error('站点信息获取失败');
			}
		}
		$site_rs = $this->model('site')->site_info($site_id);
		if(!$site_rs){
			$this->_error('网站信息不存在或未启用');
		}
		$url_type = $this->is_https() ? 'https://' : 'http://';
		if(is_numeric($site_id) && $site_rs['domain'] && $site_rs['domain'] != $domain){
			$url = $url_type.$site_rs['domain'].$site_rs['dir'];
			$this->_location($url);
			exit;
		}
		if($site_rs['_mobile']){
			if($site_rs['_mobile']['domain'] == $domain){
				$this->url = $url_type.$site_rs['_mobile']['domain'].$site_rs['dir'];
				$this->is_mobile = true;
			}else{
				if($this->is_mobile){
					$url = $url_type.$site_rs['_mobile']['domain'].$site_rs['dir'];
					$this->_location($url);
					exit;
				}
			}
		}
		if($site_rs["tpl_id"]){
			$rs = $this->model("tpl")->get_one($site_rs["tpl_id"]);
			if(!$rs){
				$this->site = $site_rs;
				return true;
			}
			$tpl_rs = array();
			$tpl_rs["id"] = $rs["id"];
			$tpl_rs["dir_tpl"] = $rs["folder"] ? "tpl/".$rs["folder"]."/" : "tpl/www/";
			$tpl_rs["dir_cache"] = $this->dir_root."data/tpl_www/";
			$tpl_rs["dir_php"] = $rs['phpfolder'] ? $this->dir_root.$rs['phpfolder'].'/' : $this->dir_root;
			$tpl_rs["dir_root"] = $this->dir_root;
			if($rs["folder_change"]){
				$tpl_rs["path_change"] = $rs["folder_change"];
			}
			$tpl_rs["refresh_auto"] = $rs["refresh_auto"] ? true : false;
			$tpl_rs["refresh"] = $rs["refresh"] ? true : false;
			$tpl_rs["tpl_ext"] = $rs["ext"] ? $rs["ext"] : "html";
			if($this->is_mobile){
				$tpl_rs["id"] = $rs["id"]."_mobile";
				$tplfolder = $rs["folder"] ? $rs["folder"]."_mobile" : "www_mobile";
				if(!file_exists($this->dir_root."tpl/".$tplfolder)){
					$tplfolder = $rs["folder"] ? $rs["folder"] : "www";
				}
				$tpl_rs["dir_tpl"] = "tpl/".$tplfolder;
			}
			$tpl_rs['langid'] = isset($_SESSION[$this->app_id.'_lang_id']) ? $_SESSION[$this->app_id.'_lang_id'] : 'default';
			$site_rs["tpl_id"] = $tpl_rs;
		}
		$this->site = $site_rs;
	}

	protected function is_https()
	{
		if($_SERVER['SERVER_PORT'] == 443){
			return true;
		}
	    if(!isset($_SERVER['HTTPS'])){
		    return false;
	    }
	    if($_SERVER['HTTPS'] === 1 || strtolower($_SERVER['HTTPS']) == 'on'){
		    return true;
	    }
	    return false;
	}  


	//装载插件
	public function init_plugin()
	{
		$rslist = $this->model('plugin')->get_all(1);
		if(!$rslist){
			return false;
		}
		$param = array();
		foreach($rslist AS $key=>$value){
			if($value['param']){
				$value['param'] = unserialize($value['param']);
			}
			if(file_exists($this->dir_root.'plugins/'.$key.'/'.$this->app_id.'.php')){
				include_once($this->dir_root.'plugins/'.$key.'/'.$this->app_id.'.php');
				$name = $this->app_id."_".$key;
				$cls = new $name();
				$mlist = get_class_methods($cls);
				$this->plugin[$key] = array("method"=>$mlist,"obj"=>$cls,'id'=>$key);
				$param[$key] = $value;
			}
		}
		$this->assign('plugin',$param);
	}
	
	public function lib($class)
	{
		if($this->_libs && $this->_libs[$class]){
			$config = $this->_libs[$class];
		}else{
			$config = array('param'=>'','include'=>'','auto'=>'','classname'=>$class.'_lib');
			if(file_exists($this->dir_root.'extension/'.$class.'/config.inc.php')){
				include($this->dir_root.'extension/'.$class.'/config.inc.php');
				if($config['include']){
					$list = explode(",",$config['include']);
					foreach($list as $key=>$value){
						include_once($this->dir_root.'extension/'.$class.'/'.$value);
					}
				}
			}
			$this->_libs[$class] = $config;			
		}
		$tmp = $config['class'] ? $config['class'] : $class.'_lib';
		if($this->$tmp && is_object($this->$tmp)){
			return $this->$tmp;
		}
		$vfile = array($this->dir_phpok.'libs/'.$class.'.php');
		$vfile[] = $this->dir_root.'extension/'.$class.'/phpok.php';
		$vfile[] = $this->dir_root.'extension/'.$class.'/index.php';
		$vfile[] = $this->dir_root.'extension/'.$class.'.php';
		$chkstatus = false;
		foreach($vfile as $key=>$value){
			if(file_exists($value)){
				include_once($value);
				$chkstatus = true;
				break;
			}
		}
		if(!$chkstatus){
			$this->error(P_Lang('类文件{classfile}不存在',array('classfile'=>$class.'.php')));
		}
		$this->$tmp = new $tmp($config['param']);
		if($config['auto']){
			$list = explode(",",$config['auto']);
			foreach($list as $key=>$value){
				$this->$name->$value();
			}
		}
		$this->$tmp = new $tmp();
		return $this->$tmp;
	}

	public function model($name)
	{
		$class_name = $name."_model";
		$class_base = $name."_model_base";
		//扩展类存在，读扩展类
		if($this->$class_name && is_object($this->$class_name)){
			return $this->$class_name;
		}
		//扩展类不存在，只有基类，则读基类
		if($this->$class_base && is_object($this->$class_base)){
			return $this->$class_base;
		}
		$basefile = $this->dir_phpok.'model/'.$name.'.php';
		if(!file_exists($basefile)){
			$this->_error("Model基础类：".$name." 不存在，请检查");
		}
		include($basefile);
		$extfile = $this->dir_phpok.'model/'.$this->app_id.'/'.$name.'_model.php';
		if(file_exists($extfile)){
			include($extfile);
			$this->$class_name = new $class_name();
			return $this->$class_name;
		}else{
			$this->$class_base = new $class_base();
			return $this->$class_base;
		}
	}

	//运行插件
	public function plugin($ap,$param="")
	{
		if(!$ap){
			return false;
		}
		$ap = str_replace("-","_",$ap);//替换节点的中划线为下划线
		if(!$this->plugin || count($this->plugin)<1 || !is_array($this->plugin)){
			return false;
		}
		foreach($this->plugin AS $key=>$value){
			if(in_array($ap,$value['method'])){
				$value['obj']->$ap($param);
			}
		}
		return true;
	}

	//加载HTML插件节点
	public function plugin_html_ap($name)
	{
		$ap = 'html-'.$this->ctrl.'-'.$this->func.'-'.$name;
		$this->plugin($ap);
		$this->plugin('html-'.$name);
	}

	//装载资源引挈
	private function init_engine()
	{
		if(!$this->config["db"] && !$this->config["engine"]){
			$this->_error("资源引挈装载失败，请检查您的资源引挈配置，如数据库连接配置等");
		}
		if($this->config["db"] && !$this->config["engine"]["db"]){
			$this->config["engine"]["db"] = $this->config["db"];
			$this->config["db"] = "";
		}
		include($this->dir_phpok.'engine/db.php');
		include($this->dir_phpok.'engine/db/'.$this->config['engine']['db']['file'].'.php');
		$var = 'db_'.$this->config['engine']['db']['file'];
		$this->db = new $var($this->config['engine']['db']);
		foreach($this->config["engine"] AS $key=>$value){
			if($key == 'db'){
				continue;
			}
			$basefile = $this->dir_phpok.'engine/'.$key.'.php';
			if(file_exists($basefile)){
				include($basefile);
			}
			$file = $this->dir_phpok."engine/".$key."/".$value["file"].".php";
			if(file_exists($file)){
				include($file);
				$var = $key."_".$value["file"];
				$obj = new $var($value);
			}else{
				$obj = new $key($value);
			}
			if($value['auto_methods']){
				$tmp = explode(",",$value['auto_methods']);
				foreach($tmp as $k=>$v){
					$v = trim($v);
					if(!$v){
						continue;
					}
					$temp = explode(":",$v);
					if(!$temp[0]){
						continue;
					}
					$funclist = get_class_methods($obj);
					if(!$funclist || !in_array($temp[0],$funclist)){
						continue;
					}
					if($temp[1]){
						$var = $temp[1];
						$param = $this->config['engine'][$var] ? $this->config['engine'][$var] : ($this->$var ? $this->$var : $this->lib($var));
						$var = $temp[0];
						$obj->$var($param);
					}else{
						$var = $temp[0];
						$obj->$var();
					}
				}
			}
			$this->$key = $obj;
		}
	}

	/**
	 * 读取网站参数配置
	 * @date 2016年02月05日
	 */
	private function init_config()
	{
		if(file_exists($this->dir_phpok."config/config.global.php")){
			include($this->dir_phpok."config/config.global.php");
		}
		if(file_exists($this->dir_phpok."config/config_".$this->app_id.".php")){
			include($this->dir_phpok."config/config_".$this->app_id.".php");
		}
		if(file_exists($this->dir_root."config.php")){
			include($this->dir_root."config.php");
		}
		if($config['debug']){
			@ini_set('opcache.enable',false);
			error_reporting(E_ALL ^ E_NOTICE);
		}else{
			error_reporting(0);
		}
		if(ini_get('zlib.output_compression')){
			ob_start();
		}else{
			($config["gzip"] && function_exists("ob_gzhandler")) ? ob_start("ob_gzhandler") : ob_start();
		}
		if($config["timezone"] && function_exists("date_default_timezone_set")){
			date_default_timezone_set($config["timezone"]);
		}
		$this->time = time();
		if($config["timetuning"]){
			$this->time = $this->time + $config["timetuning"];
		}
		$this->system_time = $this->time;
		if(!$config['get_domain_method']){
			$config['get_domain_method'] = 'SERVER_NAME';
		}
		$this->config = $config;
		$this->url = $this->root_url();
		unset($config);
	}

	//自定义网址生成器
	final public function url($ctrl="",$func="",$ext="",$appid='',$baseurl=false)
	{
		if(!$appid){
			$appid = $this->app_id;
		}
		$this->model('url')->app_file($this->config[$appid.'_file']);
		$this->model('url')->set_type($this->site['url_type']);
		$this->model('url')->url_appid($appid);
		if(is_bool($func)){
			$baseurl = $func;
			$func = '';
		}
		if($baseurl){
			$this->model('url')->base_url($this->url);
		}
		return $this->model('url')->url($ctrl,$func,$ext);
	}

	final public function root_url()
	{
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$port = $_SERVER["SERVER_PORT"];
		$myurl = $_SERVER[$this->config['get_domain_method']];
		if($port != "80" && $port != "443"){
			$myurl .= ":".$port;
		}
		$docu = $_SERVER["PHP_SELF"];
		if($_SERVER['PATH_INFO']){
			$docu = substr($docu,0,-(strlen($_SERVER['PATH_INFO'])));
		}
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1){
			foreach($array AS $key=>$value){
				$value = trim($value);
				if($value && ($key+1) < $count){
					$myurl .= "/".$value;
				}
			}
			unset($array,$count);
		}
		$myurl .= "/";
		$myurl = str_replace("//","/",$myurl);
		return $http_type.$myurl;
	}
	
	/**
	 * 配置网站全局常量
	 */
	private function init_constant()
	{
		//配置网站根目录
		if(!defined("ROOT")){
			define("ROOT",str_replace("\\","/",dirname(__FILE__))."/../");
		}
		$this->dir_root = ROOT;
		if(substr($this->dir_root,-1) != "/"){
			$this->dir_root .= "/";
		}
		//配置框架根目录
		if(!defined("FRAMEWORK")){
			defined("FRAMEWORK",$this->dir_root."framework/");
		}
		$this->dir_phpok = FRAMEWORK;
		if(substr($this->dir_phpok,-1) != "/"){
			$this->dir_phpok .= "/";
		}
		if(substr($this->dir_phpok,0,strlen($this->dir_root)) != $this->dir_root){
			$this->dir_phpok = $this->dir_root.$this->dir_phpok;
		}
		//定义APP_ID
		if(!defined("APP_ID")){
			define("APP_ID","www");
		}
		$this->app_id = APP_ID;
		//判断加载的版本及授权方式
		if(is_file($this->dir_root."version.php")){
			include($this->dir_root."version.php");
			$this->version = defined("VERSION") ? VERSION : "4.5.0";
		}
		if(is_file($this->dir_root."license.php")){
			include($this->dir_root."license.php");
			$license_array = array("LGPL","PBIZ","CBIZ");
			$this->license = (defined("LICENSE") && in_array(LICENSE,$license_array)) ? LICENSE : "LGPL";
			if(defined("LICENSE_DATE")){
				$this->license_date = LICENSE_DATE;
			}
			if(defined("LICENSE_SITE")){
				$this->license_site = LICENSE_SITE;
			}
			if(defined("LICENSE_CODE")){
				$this->license_code = LICENSE_CODE;
			}
			if(defined("LICENSE_NAME")){
				$this->license_name = LICENSE_NAME;
			}
			if(defined("LICENSE_POWERED")){
				$this->license_powered = LICENSE_POWERED;
			}
		}
		//初始化是否使用Ajax
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			$this->is_ajax = true;
		}
		if(!$this->is_ajax && defined('IS_AJAX')){
			$this->is_ajax = true;
		}
		if(!$this->is_ajax && (isset($_SERVER['request_type']) && strtolower($_SERVER['request_type']) == 'ajax')){
			$this->is_ajax = true;
		}
		if(!$this->is_ajax && (isset($_SERVER['phpok_ajax']) || isset($_SERVER['is_ajax']))){
			$this->is_ajax = true;
		}
		if(!$this->is_ajax && (isset($_POST['ajax_submit']) || isset($_GET['ajax_submit']))){
			$this->is_ajax = true;
		}
	}

	//通过post或get取得数据，并格式化成自己需要的
	final public function get($id,$type="safe",$ext="")
	{
		$val = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : "");
		if($val == ''){
			if($type == 'int' || $type == 'intval' || $type == 'float' || $type == 'floatval'){
				return 0;
			}else{
				return '';
			}
		}
		//判断内容是否有转义，所有未转义的数据都直接转义
		$addslashes = false;
		if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()){
			$addslashes = true;
		}
		if(!$addslashes){
			$val = $this->_addslashes($val);
		}
		return $this->format($val,$type,$ext);
	}

	//格式化内容
	//msg，要格式化的内容，该内容已经addslashes了
	//type，类型，支持：safe，text，html，html_js，func，int，float，system
	//ext，扩展，当type为html时，ext存在表示支持js，不存在表示不支持js
	//当type为func属性时，表示ext直接执行函数
	final public function format($msg,$type="safe",$ext="")
	{
		if($msg == ""){
			return '';
		}
		if(is_array($msg)){
			foreach($msg AS $key=>$value){
				if(!is_numeric($key)){
					$key2 = $this->format($key,"system");
					if($key2 == ''){
						unset($msg[$key]);
						continue;
					}
				}
				$msg[$key] = $this->format($value,$type,$ext);
			}
			if($msg && count($msg)>0){
				return $msg;
			}
			return false;
		}
		if($type == 'html_js' || ($type == 'html' && $ext)){
			$msg = stripslashes($msg);
			if($this->app_id != 'admin'){
				$msg = $this->lib('string')->xss_clean($msg);
			}
			$msg = $this->lib('string')->clear_url($msg,$this->url);
			return addslashes($msg);
		}
		$msg = stripslashes($msg);
		//格式化处理内容
		switch ($type)
		{
			case 'safe':$msg = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$msg);break;
			case 'system':$msg = !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u",$msg) ? false : $msg;break;
			case 'id':$msg = !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u",$msg) ? false : $msg;break;
			case 'checkbox':$msg = strtolower($msg) == 'on' ? 1 : $this->format($msg,'safe');break;
			case 'int':$msg = intval($msg);break;
			case 'intval':$msg = intval($msg);break;
			case 'float':$msg = floatval($msg);break;
			case 'floatval':$msg = floatval($msg);break;
			case 'time':$msg = strtotime($msg);break;
			case 'html':$msg = $this->lib('string')->safe_html($msg,$this->url);break;
			case 'func':$msg = function_exists($ext) ? $ext($msg) : false;break;
			case 'text':$msg = strip_tags($msg);break;
		}
		if($msg){
			$msg = addslashes($msg);
		}
		return $msg;
	}

	//安全的HTML信息
	//主要是过滤HTML中的on****各种属性
	//过滤iframe,script,link等信息
	public function safe_html($info)
	{
		return $this->lib('string')->safe_html($info);
	}

	private function _addslashes($val)
	{
		if(is_array($val)){
			foreach($val AS $key=>$value){
				$val[$key] = $this->_addslashes($value);
			}
		}else{
			$val = addslashes($val);
		}
		return $val;
	}

	final public function assign($var,$val)
	{
		$this->tpl->assign($var,$val);
	}

	final public function unassign($var)
	{
		$this->tpl->unassign($var);
	}

	final public function view($file,$type="file",$path_format=true)
	{
		$this->plugin('phpok-after');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=3"); 
		header("Pramga: no-cache"); 
		$this->tpl->display($file,$type,$path_format);
	}

	final public function fetch($file,$type="file",$path_format=true)
	{
		$this->plugin('phpok-after');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		return $this->tpl->fetch($file,$type,$path_format);
	}

	final public function get_url()
	{
		return $this->url;
	}
	//导常抛出
	final public function _error($content="")
	{
		if(!$content) $content = "异常请检查";
		$html = '<!DOCTYPE html>'."\n";
		$html.= '<html>'."\n";
		$html.= '<head>'."\n";
		$html.= '	<meta charset="utf-8" />'."\n";
		$html.= '	<title>友情提示</title>'."\n";
		$html.= '</head>'."\n";
		$html.= '<body style="padding:10px;font-size:14px;">'."\n";
		$html.= $content."\n";
		$html.= '</body>'."\n";
		$html.= '</html>';
		exit($html);
	}

	//执行应用
	final public function action()
	{
		$this->init_assign();
		$this->init_plugin();
		$func_name = "action_".$this->app_id;
		$array = array('www','admin','api');
		if(in_array($this->app_id,$array)){
			//读取会员信息
			if($this->app_id != 'admin' && $_SESSION['user_id']){
				$this->user = $this->model('user')->get_one($_SESSION['user_id']);
				if($this->app_id == 'www'){
					$this->assign('user',$this->user);
				}
			}
			$this->$func_name();
			exit;
		}
		$this->action_www();
		exit;
	}

	final public function action_api()
	{
		$id = $this->config['token_id'] ? $this->config['token_id'] : 'token';
		$token = $this->get($id);
		if($token){
			$info = $this->lib('token')->decode($token);
			if($info && is_array($info) && $info['user_id'] && $info['user_chk']){
				$chk_status = $this->model('user')->token_check($info['user_id'],$info['user_chk']);
				if($chkstatus){
					$token = $this->lib('token')->encode(array('user_id'=>$info['user_id'],'user_chk'=>$info['user_chk']));
					$this->token = $token;
				}
			}
		}
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		if(!$ctrl){
			$ctrl = 'index';
		}
		$func = $this->get($this->config["func_id"],"system");
		if(!$func){
			$func = 'index';
		}
		$this->_action($ctrl,$func);
	}

	//前端参数获取
	final public function action_www()
	{
		//判断是否有PATH_INFO;
		if($this->site['url_type'] == 'rewrite' && $_SERVER['REQUEST_URI']){
			$uri = $_SERVER['REQUEST_URI'];
			$docu = $_SERVER["PHP_SELF"];
			if($_SERVER['PATH_INFO']){
				$docu = substr($docu,0,-(strlen($_SERVER['PATH_INFO'])));
			}
			$array = explode("/",$docu);
			$docu = '/';
			$count = count($array);
			if($count>1){
				foreach($array AS $key=>$value){
					$value = trim($value);
					if($value && ($key+1) < $count){
						$docu .= $value.'/';
					}
				}
			}
			if($docu != '/' && substr($uri,0,strlen($docu)) == $docu){
				$uri = substr($uri,(strlen($docu)-1));
			}
			$script_name = $_SERVER['SCRIPT_NAME'] ? basename($_SERVER['SCRIPT_NAME']) : 'index.php';
			if('/'.$script_name == substr($uri,0,(strlen($script_name)+1))){
				$uri = substr($uri,(strlen($script_name)+1));
			}
			$query_string = $_SERVER['QUERY_STRING'];
			if($query_string){
				$uri = str_replace('?'.$query_string,'',$uri);
			}
			if($uri != '/' && strlen($uri)>2){
				if(substr($uri,0,1) == '/'){
					$uri = substr($uri,1);
				}
				if(substr($uri,-1) == '/'){
					$uri = substr($uri,0,-1);
				}
			}
			if($uri != '/'){
				$this->model('rewrite')->uri_format($uri);
			}
		}
		$id = $this->get('id');
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = '';
		if($id && !$ctrl && $id != 'index'){
			$ctrl = $id;
			$reserved = $this->config['reserved'] ? explode(',',$this->config['reserved']) : array('js','ajax','inp');
			if(!in_array($id,$reserved)){
				$ctrl = intval($id)>0 ? 'content' : $this->model('id')->get_ctrl($id,$this->site['id']);
				if(!$ctrl){
					$this->_action('index','error404');
					exit;
				}
			}
			if($ctrl == 'post'){
				$cate = $this->get('cate','system');
				if($cate == 'add' || $cate == 'edit'){
					$func = $cate;
				}
			}
		}
		//如果没有Ctrl，将读取 index 控制器
		if(!$ctrl){
			$ctrl = 'index';
		}
		//如果没有Func,将使用 index 
		if(!$func){
			$func = $this->get($this->config["func_id"],"system");
		}
		if(!$func){
			$func = 'index';
		}
		if($this->site['url_type'] == 'html' && $this->app_id == 'www'){
			$is_create = (isset($_GET['_html']) && $_GET['_html']) ? true : false;
			if($is_create){
				$this->_action($ctrl,$func);
				exit;
			}else{
				if($ctrl == 'index'){
					$root_dir = ($this->site['html_root_dir'] && $this->site['html_root_dir'] != '/') ? $this->site['html_root_dir'] : '';
					$url = $this->url.$root_dir.'index.html';
					$this->_location($url);
				}
			}
		}
		$this->_action($ctrl,$func);
	}

	//仅限管理员的操作
	final public function action_admin()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl) $ctrl = "index";
		if(!$func) $func = "index";
		if($ctrl != 'login' && !$this->config['develop']){
			if(!$_SERVER['HTTP_REFERER']){
				$ctrl='login';
				$func = 'index';
				session_destroy();
				$this->_location($this->url('login'));
			}else{
				$info = parse_url($_SERVER['HTTP_REFERER']);
				$chk = parse_url($this->url);
				if($info['host'] != $chk['host']){
					$ctrl = 'login';
					$func = 'index';
					session_destroy();
					$this->_location($this->url('login'));
				}
			}
		}
		$this->lib('form')->appid('admin');
		$this->_action($ctrl,$func);
	}

	public function _location($url)
	{
		ob_end_clean();
		ob_start();
		header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=0"); 
		header("Pramga: no-cache");
		header("Location:".$url);
		ob_end_flush();
		exit;
	}

	private function _action($ctrl='index',$func='index')
	{
		//如果App_id非指定的三种，强制初始化
		if(!in_array($this->app_id,array('api','www','admin'))){
			$this->app_id = 'www';
		}
		$reserved = array('login','js','ajax','inp','register');
		$is_login = $this->config[$this->app_id]['is_login'] ? true : false;
		$is_admin = $this->config[$this->app_id]['is_admin'] ? true : false;
		if($is_admin && !$_SESSION['admin_id'] && !in_array($ctrl,$reserved)){
			$ctrl = 'login';
			$go_url = $this->url($ctrl);
			$this->_location($go_url);
		}
		if($is_login && !$_SESSION['user_id'] && !in_array($ctrl,$reserved)){
			$ctrl = 'login';
			$go_url = $this->url($ctrl);
			$this->_location($go_url);
		}
		$dir_root = $this->dir_phpok.$this->app_id.'/';
		if($ctrl == 'js' || $ctrl == 'ajax' || $ctrl == "inp"){
			$dir_root = $this->dir_phpok;
		}
		//加载应用文件
		if(!file_exists($dir_root.$ctrl.'_control.php')){
			$this->_error('应用文件：'.$ctrl.'_control.php 不存在，请检查');
		}
		include($dir_root.$ctrl.'_control.php');
		if(file_exists($this->dir_phpok.$this->app_id."/global.func.php")){
			include($this->dir_phpok.$this->app_id."/global.func.php");
		}
		//执行应用
		$app_name = $ctrl."_control";
		$this->ctrl = $ctrl;
		$this->func = $func;
		$cls = new $app_name();
		$func_name = $func."_f";
		if(!in_array($func_name,get_class_methods($cls))){
			$this->_error("控制器 ".$ctrl." 不存在方法 ".$func_name);
		}
		//自动运行的函数
		if($this->config[$this->app_id]["autoload_func"]){
			$list = explode(",",$this->config["autoload_func"]);
			foreach($list AS $key=>$value)
			{
				if(function_exists($value))
				{
					$value();
				}
			}
			unset($list);
		}
		$this->config['ctrl'] = $ctrl;
		$this->config['func'] = $func;
		$this->config['time'] = $this->time;
		$this->assign('sys',$this->config);
		//节点触发器
		$this->plugin('phpok-before');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-before');
		if($this->app_id == 'www' && !$this->site['status'] && !$_SESSION['admin_id']){
			$this->error($this->site["content"]);
		}
		$cls->$func_name();
	}

	//JSON内容输出
	final public function json($content,$status=false,$exit=true,$format=true)
	{
		if($exit){
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
			header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
			header("Cache-control: no-cache,no-store,must-revalidate,max-age=0"); 
			header("Pramga: no-cache"); 
		}
		if(!$content && is_bool($content)){
			$rs = array('status'=>'error');
			exit($this->lib('json')->encode($rs));
		}
		//当content内容为true 且为布尔类型，直接返回正确通知结果
		if($content && is_bool($content)){
			$rs = array('status'=>'ok');
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
			exit($this->lib('json')->encode($rs));
		}
		$status_info = $status ? 'ok' : 'error';
		if($status_info == 'ok'){
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		}
		$rs = array('status'=>$status_info);
		if($content != '') $rs['content'] = $content;
		$info = $this->lib('json')->encode($rs);
		unset($rs);
		if($exit){
			exit($info);
		}
		return $info;
	}

	/**
	 * 友情错误提示，支持Ajax
	 * @param string $info 错误信息
	 * @param string $url 跳转网址
	 * @param mixed $ajax $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @date 2016年01月22日
	 */
	public function error($info='',$url='',$ajax=false)
	{
		$this->_tip($info,0,$url,$ajax);
	}

	/**
	 * 友情成功提示，支持Ajax
	 * @param string $info 错误信息
	 * @param string $url 跳转网址
	 * @param mixed $ajax $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @date 2016年01月22日
	 */
	public function success($info='',$url='',$ajax=false)
	{
		$this->_tip($info,1,$url,$ajax);
	}

	public function tip($info,$url,$ajax=false)
	{
		$this->_tip($info,2,$url,$ajax);
	}

	/**
	 * 友好提示
	 * @param string $info 错误信息
     * @param Boolean $status 状态，1或true为成功，0或false为失败
	 * @param string $url 跳转网址
	 * @param mixed $ajax $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @date 2016年01月22日
	 */
	protected function _tip($info='',$status=0,$url='',$ajax=false)
	{
		if(true === $ajax || $this->is_ajax){
			$data = is_array($ajax) ? $ajax : array();
			$data['info'] = $info;
			$data['status'] = $status;
			$data['url'] = $url;
			header('Content-Type:application/json; charset=utf-8');
            exit($this->lib('json')->encode($data));
        }
        if($ajax && is_int($ajax)){
	        $this->assign('time',$ajax);
        }
        if($url){
	        $this->assign('url',$url);
        }
        $this->assign('title',($status ? P_Lang('操作成功') : P_Lang('操作失败')));
        $this->assign('type',($status ? 'success' : 'error'));
        if($status == 2){
	        $this->assign('type','notice');
        }
        $this->assign('status',$status);
        $this->assign('tips',$info);
        $this->assign('info',$info);
        $this->assign('content',$info);
        if($this->get("close_win")){
	        $this->assign('url','javascript:window.close();void(0)');
        }
        $fileid = $status ? 'success' : 'error';
        $tplfile = $this->tpl->check($fileid) ? $fileid : ($this->tpl->check('tips') ? 'tips' : '');
        header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=3"); 
		header("Pramga: no-cache"); 
        if(!$tplfile){
	        $chk = array($this->dir_root.'tpl/'.$fileid.'.html',$this->dir_root.'tpl/tips.html');
	        foreach($chk as $key=>$value){
		        if($this->tpl->check($value,true,true)){
			        $tplfile = $value;
		        }
	        }
	        $this->tpl->display($tplfile,'abs-file',false);
        }
		$this->tpl->display($tplfile);
	}

	//针对PHPOK4前台执行SEO优化
	final public function phpok_seo($rs)
	{
		if(!$rs || !is_array($rs)) return false;
		$seo = $this->site['seo'] ? $this->site["seo"] : array();
		foreach($rs AS $key=>$value){
			if(substr($key,0,3) == "seo" && $value && is_string($value)){
				$subkey = substr($key,4);
				if($subkey == "kw" || $subkey == "keywords" || $subkey == "keyword"){
					$seo["keywords"] = $value;
				}elseif($subkey == "desc" || $subkey == "description"){
					$seo["description"] = $value;
				}elseif($subkey == "title"){
					$seo["title"] = $value;
				}else{
					$seo[$subkey] = $value;
				}
			}
		}
		$this->site['seo'] = $seo;
		$this->assign("seo",$seo);
		unset($seo);
	}

	final public function ascii($str='')
	{
		if(!$str) return false;
		$str = iconv("UTF-8", "UTF-16BE", $str);
		$output = "";
		for ($i = 0; $i < strlen($str); $i++,$i++)
		{
			$code = ord($str{$i}) * 256 + ord($str{$i + 1});
			if ($code < 128)
			{
				$output .= chr($code);
			}
			else if($code != 65279)
			{
				$output .= "&#".$code.";";
			}
		}
		return $output;
	}

	//增加js库，在HTML模板里可以直接使用 phpok_head_js，将生成符合标准的js文件链接
	function addjs($url='')
	{
		$this->jslist[] = $url;
	}

	//增加css文件链接，在HTML里可以直接使用 phpok_head_css，将生成符合标准的CSS文件链接
	function addcss($url='')
	{
		$this->csslist[] = $url;
	}

	//第三方网关接入
	final public function gateway($action,$param='')
	{
		if($action == 'type'){
			$this->gateway['type'] = $param;
			return true;
		}
		if($action == 'param'){
			if($param == 'default'){
				$info = $this->model('gateway')->get_default($this->gateway['type']);
			}else{
				$info = $this->model('gateway')->get_one($param);
			}
			if($info){
				$this->gateway['param'] = $info;
			}
			return true;
		}
		if($action == 'extinfo'){
			$this->gateway['extinfo'] = $param;
		}
		if($action == 'exec'){
			if(!$this->gateway['param']){
				return false;
			}
			$rs = $this->gateway['param'];
			$extinfo = $this->gateway['extinfo'];
			if(!$extinfo && $param){
				$extinfo = $param;
			}
			$file = $this->dir_root.'gateway/'.$this->gateway['param']['type'].'/'.$this->gateway['param']['code'].'/exec.php';
			$info = false;
			if(file_exists($file)){
				$info = include $file;
			}
			if($param == 'json'){
				exit($this->lib('json')->encode($info));
			}else{
				return $info;
			}
		}
		if(!$this->gateway['param']){
			return false;
		}
		$file = $this->dir_root.'gateway/'.$this->gateway['param']['type'].'/'.$this->gateway['param']['code'].'/'.$action.'.php';
		if(file_exists($file) && $param){
			$param = explode(":",$param);
			$classname = $param[0];
			$funcname = $param[1] ? $param[1] : 'index';
			$obj = new $classname($this->gateway['param']);
			return $obj->$funcname($this->gateway['funcparam']);
		}
		return true;
	}

}

//核心魔术方法，此项可实现类，方法的自动加载
class _init_auto
{
	//构造函数
	public function __construct()
	{
		//
	}

	public function __destruct()
	{
		unset($this);
	}

	//魔术方法之方法重载
	public function __call($method,$param)
	{
		if(method_exists($GLOBALS['app'],$method)){
			return call_user_func_array(array($GLOBALS['app'],$method),$param);
		}else{
			$lst = explode("_",$method);
			if($lst[1] == 'model'){
				$GLOBALS['app']->model($lst[0]);
				call_user_func_array(array($GLOBALS['app'],$method),$param);
			}elseif($lst[1] == 'lib'){
				$GLOBALS['app']->lib($lst[0]);
				return call_user_func_array(array($GLOBALS['app'],$method),$param);
			}
		}
	}

	public function __get($id)
	{
		$lst = explode("_",$id);
		if($lst[1] == "model"){
			return $GLOBALS['app']->model($lst[0]);
		}elseif($lst[1] == "lib"){
			return $GLOBALS['app']->lib($lst[0]);
		}
		return $GLOBALS['app']->$id;
	}

	public function __isset($id)
	{
		return $this->__get($id);
	}
}

//针对扩展的一些完善
class _init_lib
{
	protected $dir_root;
	protected $dir_phpok;
	protected $dir_data;
	protected $dir_cache;
	protected $dir_extension;
	public function __construct()
	{
		$this->dir_root = $GLOBALS['app']->dir_root;
		$this->dir_phpok = $GLOBALS['app']->dir_phpok;
		$this->dir_data = $GLOBALS['app']->dir_root.'data/';
		$this->dir_cache = $GLOBALS['app']->dir_root.'data/cache/';
		$this->dir_extension = $GLOBALS['app']->dir_root.'extension/';
	}

	protected function dir_root($dir='')
	{
		if($dir){
			$this->dir_root = $dir;
		}
		return $this->dir_root;
	}

	protected function dir_phpok($dir='')
	{
		if($dir){
			$this->dir_phpok = $dir;
		}
		return $this->dir_phpok;
	}

	protected function dir_data($dir='')
	{
		if($dir){
			$this->dir_data = $dir;
		}
		return $this->dir_data;
	}

	protected function dir_cache($dir='')
	{
		if($dir){
			$this->dir_cache = $dir;
		}
		return $this->dir_cache;
	}

	protected function dir_extension($dir='')
	{
		if($dir){
			$this->dir_extension = $dir;
		}
		return $this->dir_extension;
	}
}

//PHPOK控制器，里面大部分函数将通过Global功能调用核心引挈
class phpok_control extends _init_auto
{
	public function control()
	{
		parent::__construct();
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}
}


class phpok_model extends _init_auto
{
	//继承control信息
	public $site_id = 0;
	public function model($id='')
	{
		if(!$id){
			parent::__construct();
			if($this->app_id == 'admin' && $_SESSION['admin_site_id']){
				$this->site_id = $_SESSION['admin_site_id'];
			}
			if($this->app_id != 'admin' && $this->site['id']){
				$this->site_id = $this->site['id'];
			}
		}else{
			return $GLOBALS['app']->model($id);
		}
	}
	
	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	public function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}


	protected function return_next_taxis($rs='')
	{
		if($rs){
			if(is_array($rs)){
				$taxis = $rs['taxis'] ? $rs['taxis'] : $rs['sort'];
			}else{
				$taxis = $rs;
			}
			$taxis = intval($taxis);
			return intval($taxis+10);
		}else{
			return 10;
		}
	}
}

class phpok_plugin extends _init_auto
{
	public function plugin()
	{
		parent::__construct();
	}

	final public function _id()
	{
		$name = get_class($this);
		$lst = explode("_",$name);
		unset($lst[0]);
		return implode("_",$lst);
	}

	final public function _info($id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$rs = array('id'=>$id);
		}
		if($rs['param']){
			$rs['param'] = unserialize($rs['param']);
		}
		$rs['path'] = $this->dir_root.'plugins/'.$id.'/';
		return $rs;
	}

	final public function _save($ext,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		if(!$id){
			return false;
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			return false;
		}
		$info = ($ext && is_array($ext)) ? serialize($ext) : '';
		return $this->model('plugin')->update_param($id,$info);
	}

	final public function _tpl($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if(!$file){
			return false;
		}
		return $this->fetch($file,'abs-file');
	}

	final public function _show($name,$id='')
	{
		$info = $this->_tpl($name,$id);
		if($info){
			echo $info;
		}
	}

	final public function _view($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if($file){
			$this->view($file,'abs-file');
		}
	}

	private function _tplfile($name,$id='')
	{
		if(!$id){
			$id = $this->_id();
		}
		$list = array();
		$list[0] = $this->dir_root.$this->tpl->dir_tpl.'plugins/'.$id.'/template/'.$name;
		$list[1] = $this->dir_root.$this->tpl->dir_tpl.'plugins/'.$id.'/'.$name;
		$list[2] = $this->dir_root.$this->tpl->dir_tpl.$id.'/'.$name;
		$list[3] = $this->dir_root.$this->tpl->dir_tpl.'plugins_'.$id.'_'.$name;
		$list[4] = $this->dir_root.$this->tpl->dir_tpl.$id.'_'.$name;
		$list[5] = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		$list[6] = $this->dir_root.'plugins/'.$id.'/'.$name;
		$file = false;
		foreach($list as $key=>$value){
			if(file_exists($value)){
				$file = $value;
				break;
			}
		}
		return $file;
	}

	//旧版本写法，中止兼容时间2016年11月27日 将不再兼容
	//plugin_id，plugin_info，plugin_save，plugin_tpl，show_tpl，echo_tpl 将被禁用
	//可使用的有_id,_info,_save,_tpl,_show,_view来替代
	protected function plugin_id()
	{
		return $this->_id();
	}

	protected function plugin_info($id='')
	{
		return $this->_info();
	}

	protected function plugin_save($ext,$id="")
	{
		return $this->_save($ext,$id);
	}
	
	protected function plugin_tpl($name,$id='')
	{
		return $this->_tpl($name,$id);
	}

	protected function show_tpl($name,$id='')
	{
		$this->_show($name,$id);
	}

	protected function echo_tpl($name,$id='')
	{
		$this->_view($name,$id);
	}
}

//安全注销全局变量
unset($_ENV, $_SERVER['MIBDIRS'],$_SERVER['MYSQL_HOME'],$_SERVER['OPENSSL_CONF'],$_SERVER['PHP_PEAR_SYSCONF_DIR'],$_SERVER['PHPRC'],$_SERVER['SystemRoot'],$_SERVER['COMSPEC'],$_SERVER['PATHEXT'], $_SERVER['WINDIR'],$_SERVER['PATH']);

$app = new _init_phpok();
include_once($app->dir_phpok."phpok_helper.php");
$app->init_site();
$app->init_view();
function init_app(){
	return $GLOBALS['app'];
}
//核心函数，phpok_head_js，用于加载自定义扩展中涉及到的js
function phpok_head_js()
{
	$jslist = $GLOBALS['app']->jslist;
	if(!$jslist || !is_array($jslist)) return false;
	$jslist = array_unique($jslist);
	$html = "";
	foreach($jslist AS $key=>$value){
		$html .= '<script type="text/javascript" src="'.$value.'" charset="utf-8"></script>'."\n";
	}
	return $html;
}
//核心函数，phpok_head_css，用于加载自定义扩展中涉及到的css
function phpok_head_css()
{
	$csslist = $GLOBALS['app']->csslist;
	if(!$csslist || !is_array($csslist)) return false;
	$csslist = array_unique($csslist);
	$html = "";
	foreach($csslist AS $key=>$value){
		$html .= '<link rel="stylesheet" type="text/css" href="'.$value.'" charset="utf-8" />'."\n";
	}
	return $html;
}
//核心函数，语言包
function P_Lang($info,$replace='')
{
	if(!$info){
		return false;
	}
	if($GLOBALS['app']->language_status == 'user'){
		if($GLOBALS['app']->lang){
			$info = $GLOBALS['app']->lang->translate($info);
		}
	}else{
		$info = gettext($info);
	}
	if($replace && is_string($replace)){
		$replace  = unserialize($replace);
	}
	if($replace && is_array($replace)){
		foreach($replace as $key=>$value){
			$info = str_replace('{'.$key.'}',$value,$info);
			$info = str_replace('['.$key.']',$value,$info);
		}
		return $info;
	}
	return $info;
}

//核心函数，增加CSS
function phpok_add_css($file='')
{
	$GLOBALS['app']->addcss($file);
}
function phpok_add_js($file='')
{
	$GLOBALS['app']->addjs($file);
}
$app->action();
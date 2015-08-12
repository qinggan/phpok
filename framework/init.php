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

//xml纯内容
function xml_to_array($xml)
{
	if(isset($GLOBALS['app'])){
		return $GLOBALS['app']->lib('xml')->read($xml,false);
	}else{
		include_once(FRAMEWORK.'libs/xml.php');
		$obj = new xml_lib();
		return $obj->read($xml,false);
	}
}


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
			$memory = "1 KB";
		}elseif($memory>1024 && $memory<(1024*1024)){
			$memory = round(($memory/1024),2)." KB";
		}else{
			$memory = round(($memory/(1024*1024)),2)." MB";
		}
		return $memory;
	}
}

run_time();
run_memory();

function debug_time($memory_ctrl=1,$sql_ctrl=1,$file_ctrl=0,$cache_ctrl=0)
{
	$time = run_time(true);
	$memory = run_memory(true);
	$sql_db_count = $GLOBALS['app']->db->conn_count();
	$sql_db_time = $GLOBALS['app']->db->conn_times();
	$sql_cache_count = $GLOBALS['app']->db->cache_count();
	$sql_cache_time = $GLOBALS['app']->db->cache_time();
	$string  = P_Lang('运行{seconds_total}秒',array('seconds_total'=>$time));
	//$string  = "运行 ".$time." 秒";
	if($memory_ctrl && $memory_ctrl != 'false')
	{
		$string .= P_Lang('，内存使用{memory_total}',array('memory_total'=>$memory));
	}
	if($sql_ctrl && $sql_ctrl != 'false')
	{
		$string .= P_Lang('，数据库执行{sql_count}次，耗时{sql_time}秒',array('sql_count'=>$sql_db_count,'sql_time'=>$sql_db_time));
		//$string .= "，数据库执行 ".$sql_db_count." 次，耗时 ".$sql_db_time." 秒";
	}
	if($file_ctrl && $count>0 && $file_ctrl != 'false')
	{
		$string .= P_Lang('，文件执行{file_count}次',array('file_count'=>$count));
	}
	if($cache_ctrl && $cache_ctrl != 'false')
	{
		$string .= P_Lang('，缓存执行{cache_count}次，耗时{cache_time}秒',array('cache_count'=>$sql_cache_count,'cache_time'=>$sql_cache_time));
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

	public function __construct()
	{
		@ini_set("magic_quotes_runtime",0);
		$this->init_constant();
		$this->init_config();
		if($this->app_id == 'www' && $this->config['mobile']['status']){
			$this->is_mobile = $this->config['mobile']['default'];
			if(!$this->is_mobile && $this->config['mobile']['autocheck']){
				$this->is_mobile = $this->is_mobile();
			}
		}
		$this->init_engine();
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
			include($this->dir_phpok.'libs/pomo/mo.php');
			$this->lang = new NOOP_Translations;
			$mo = new MO();
			if (!$mo->import_from_file($mofile)){
				return false;
			}
			$mo->merge_with($this->lang);
			$this->lang = &$mo;
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
		$file = $this->dir_phpok."phpok_tpl.php";
		if(!is_file($file)){
			$this->error("视图引挈文件：".basename($file)." 不存在！");
		}
		include_once($file);
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
				$this->error("未指定模板文件");
			}
			$this->model('url')->base_url($this->url);
			$this->model('url')->set_type($this->site['url_type']);
			$this->model('url')->protected_ctrl($this->config['reserved']);
			//初始化伪静态中需要的东西
			if($this->site['url_type'] == 'rewrite'){
				$this->model('url')->site_id($this->site['id']);
				$this->model('rewrite')->site_id($this->site['id']);
				if($this->config['user_rewrite']){
					$this->model('url')->id_list();
					$this->model('url')->cate_list();
					$this->model('url')->project_list();
					$this->model('url')->rules($this->model('rewrite')->get_all());
					$this->model('url')->type_ids($this->model('rewrite')->type_ids());
				}
				$this->model('url')->page_id($this->config['pageid']);
			}
			$this->tpl = new phpok_tpl($this->site["tpl_id"]);
			include($this->dir_phpok."phpok_call.php");
			$this->call = new phpok_call();
		}
		include_once($this->dir_phpok."phpok_tpl_helper.php");
	}

	//手机判断
	public function is_mobile()
	{
		if(isset($_SERVER['HTTP_X_WAP_PROFILE'])){
			return true;
		}
		if(isset($_SERVER['HTTP_PROFILE'])){
			return true;
		}
		$regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
		$regex_match.= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
		$regex_match.= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|";
		$regex_match.= "sagem|wellcom|bunjalloo|maui|symbian|smartphone|midp|wap|phone|windows ce|";
		$regex_match.= "iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
		$regex_match.= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
		$regex_match.= ")/i";
		if(preg_match($regex_match,strtolower($_SERVER['HTTP_USER_AGENT']))){
			unset($regex_match);
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
		$siteId = $this->get("siteId","int");
		$domain = strtolower($_SERVER[$this->config['get_domain_method']]);
		$port = $_SERVER["SERVER_PORT"];
		if($port != '80' && $port != '443'){
			$domain .= ':'.$port;
		}
		$site_rs = false;
		if($siteId){
			$site_rs = $this->model('site')->get_one($siteId);
			if($site_rs && $site_rs['domain']){
				if($site_rs['domain'] != $domain){
					$url = 'http://'.$site_rs['domain'].$site_rs['dir'];
					$this->_location($url);
				}
			}
		}
		if(!$site_rs){
			$site_rs = $this->model("site")->get_one_from_domain($domain);
			if(!$site_rs){
				$site_rs = $this->model('site')->get_one_default();
				if(!$site_rs){
					$this->error("无法获取网站信息，请检查！");
				}
			}
		}
		//验证
		if($site_rs && $site_rs['_mobile']){
			if($site_rs['_mobile']['domain'] == $domain){
				$this->url = 'http://'.$site_rs['_mobile']['domain'].$site_rs['dir'];
				$this->is_mobile = true;
			}else{
				if($this->is_mobile){
					$url = 'http://'.$site_rs['_mobile']['domain'].$site_rs['dir'];
					$this->_location($url);
				}
			}
		}
		$ext_list = $this->model('site')->site_config($site_rs["id"]);
		if($ext_list){
			$site_rs = array_merge($ext_list,$site_rs);
			unset($ext_list);
		}
		if($site_rs["tpl_id"]){
			$rs = $this->model("tpl")->get_one($site_rs["tpl_id"]);
			if($rs){
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
				unset($tpl_rs,$rs);
			}
		}
		$this->site = $site_rs;
		unset($site_rs);
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
	
	public function lib($class,$ext_folder="")
	{
		$tmp = $class.'_lib';
		if($this->$tmp && is_object($this->$tmp))
		{
			return $this->$tmp;
		}
		$file = $this->dir_phpok.'libs/';
		if($folder && $folder != '/')
		{
			$file .= $folder;
			if(substr($folder,-1) != '/')
			{
				$file .= '/';
			}
		}
		$file .= $class.'.php';
		if(!is_file($file))
		{
			return false;
		}
		include($file);
		$this->$tmp = new $tmp();
		return $this->$tmp;
	}

	public function model($name)
	{
		$class_name = $name."_model";
		$class_base = $name."_model_base";
		//扩展类存在，读扩展类
		if($this->$class_name && is_object($this->$class_name))
		{
			return $this->$class_name;
		}
		//扩展类不存在，只有基类，则读基类
		if($this->$class_base && is_object($this->$class_base))
		{
			return $this->$class_base;
		}
		$basefile = $this->dir_phpok.'model/'.$name.'.php';
		if(!is_file($basefile))
		{
			$this->error("基础类：".$name." 不存在，请检查");
		}
		include($basefile);
		$extfile = $this->dir_phpok.'model/'.$this->app_id.'/'.$name.'_model.php';
		if(is_file($extfile))
		{
			include($extfile);
			$this->$class_name = new $class_name();
			return $this->$class_name;
		}
		else
		{
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
		if(!$this->config["db"] && !$this->config["engine"])
		{
			$this->error("资源引挈装载失败，请检查您的资源引挈配置，如数据库连接配置等");
		}
		if($this->config["db"] && !$this->config["engine"]["db"])
		{
			$this->config["engine"]["db"] = $this->config["db"];
			$this->config["db"] = "";
		}
		//$engine_file_list = "";
		foreach($this->config["engine"] AS $key=>$value)
		{
			$basefile = $this->dir_phpok.'engine/'.$key.'.php';
			if(is_file($basefile))
			{
				include($basefile);
			}
			$file = $this->dir_phpok."engine/".$key."/".$value["file"].".php";
			if(is_file($file))
			{
				include($file);
				$var = $key."_".$value["file"];
				$this->$key = new $var($value);
			}
		}
	}

	//读取网站参数配置
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
		$config["debug"] ? error_reporting(E_ALL ^ E_NOTICE) : error_reporting(0);
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
		if(!$appid)
		{
			$appid = $this->app_id;
		}
		$this->model('url')->app_file($this->config[$appid.'_file']);
		if($appid  == "admin" || $appid == 'api')
		{
			if($baseurl)
			{
				$this->model('url')->base_url($this->url);
			}
			return $this->model('url')->url($ctrl,$func,$ext);
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
		if($_SERVER['PATH_INFO'])
		{
			$docu = substr($docu,0,-(strlen($_SERVER['PATH_INFO'])));
		}
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1)
		{
			foreach($array AS $key=>$value)
			{
				$value = trim($value);
				if($value)
				{
					if(($key+1) < $count)
					{
						$myurl .= "/".$value;
					}
				}
			}
			unset($array,$count);
		}
		$myurl .= "/";
		$myurl = str_replace("//","/",$myurl);
		return $http_type.$myurl;
	}
	
	//配置网站全局常量
	private function init_constant()
	{
		//配置网站根目录
		if(!defined("ROOT")) define("ROOT",str_replace("\\","/",dirname(__FILE__))."/../");
		$this->dir_root = ROOT;
		if(substr($this->dir_root,-1) != "/") $this->dir_root .= "/";
		//配置框架根目录
		if(!defined("FRAMEWORK")) defined("FRAMEWORK",$this->dir_root."phpok/");
		$this->dir_phpok = FRAMEWORK;
		if(substr($this->dir_phpok,-1) != "/") $this->dir_phpok .= "/";
		if(substr($this->dir_phpok,0,strlen($this->dir_root)) != $this->dir_root)
		{
			$this->dir_phpok = $this->dir_root.$this->dir_phpok;
		}
		//定义APP_ID
		if(!defined("APP_ID")) define("APP_ID","phpok");
		$this->app_id = APP_ID;
		# 判断加载的版本及授权方式
		if(is_file($this->dir_root."version.php"))
		{
			include($this->dir_root."version.php");
			$this->version = defined("VERSION") ? VERSION : "4.0";
		}
		if(is_file($this->dir_root."license.php"))
		{
			include($this->dir_root."license.php");
			$license_array = array("LGPL","PBIZ","CBIZ");
			$this->license = (defined("LICENSE") && in_array(LICENSE,$license_array)) ? LICENSE : "LGPL";
			if(defined("LICENSE_DATE")) $this->license_date = LICENSE_DATE;
			if(defined("LICENSE_SITE")) $this->license_site = LICENSE_SITE;
			if(defined("LICENSE_CODE")) $this->license_code = LICENSE_CODE;
			if(defined("LICENSE_NAME")) $this->license_name = LICENSE_NAME;
			if(defined("LICENSE_POWERED")) $this->license_powered = LICENSE_POWERED;
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
			if(!$_SESSION['admin_id']){
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
	final public function error($content="")
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
		if(in_array($func_name,get_class_methods($this))){
			$this->$func_name();
			exit;
		}
		$func_name = "action_www";
		$this->$func_name();
		exit;
	}

	final public function action_api()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl) $ctrl = 'index';
		if(!$func) $func = 'index';
		$this->_action($ctrl,$func);
	}

	//前端参数获取
	final public function action_www()
	{
		//前端ID的获取问题
		$id = $this->get('id');
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = '';
		if($id && !$ctrl && $id != 'index')
		{
			$ctrl = $id;
			$reserved = $this->config['reserved'] ? explode(',',$this->config['reserved']) : array('js','ajax','inp');
			if(!in_array($id,$reserved))
			{
				$ctrl = intval($id)>0 ? 'content' : $this->model('id')->get_ctrl($id,$this->site['id']);
			}
			if($ctrl == 'post')
			{
				$cate = $this->get('cate','system');
				if($cate == 'add' || $cate == 'edit') $func = $cate;
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
		if($_SESSION['admin_id']){
			$this->lib('form')->appid('admin');
		}
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
		$reserved = array('login','js','ajax','inp');
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
		//已登录的会员账号 $this->user
		if($_SESSION['user_id']){
			$this->user = $this->model('user')->get_one($_SESSION['user_id']);
			$this->assign('user',$this->user);
		}
		//管理员账号 $this->admin
		if($_SESSION['admin_id']){
			$this->admin = $this->model('admin')->get_one($_SESSION['admin_id']);
			$this->assign('admin',$this->admin);
		}
		$dir_root = $this->dir_phpok.$this->app_id.'/';
		if($ctrl == 'js' || $ctrl == 'ajax' || $ctrl == "inp"){
			$dir_root = $this->dir_phpok;
		}
		//加载应用文件
		if(!file_exists($dir_root.$ctrl.'_control.php')){
			$this->error('应用文件：'.$ctrl.'_control.php 不存在，请检查');
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
			$this->error("文件：".$ctrl."_control.php 不存在方法：".$func_name."！");
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
}

//核心魔术方法，此项可实现类，方法的自动加载
class _init_auto
{
	//构造函数
	public function __construct()
	{
		//
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

//PHPOK控制器，里面大部分函数将通过Global功能调用核心引挈
class phpok_control extends _init_auto
{
	function control()
	{
		parent::__construct();
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

	public function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}

	public function __destruct()
	{
		unset($this);
	}
}

class phpok_plugin extends _init_auto
{
	public function plugin()
	{
		parent::__construct();
	}

	//读取插件ID
	final public function plugin_id()
	{
		$name = get_class($this);
		$lst = explode("_",$name);
		unset($lst[0]);
		return implode("_",$lst);
	}

	//读取指定ID的插件信息
	//为空读取当前插件信息
	final public function plugin_info($id='')
	{
		if(!$id){
			$id = $this->plugin_id();
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

	//存储插件配置
	final public function plugin_save($ext,$id="")
	{
		if(!$id){
			$id = $this->plugin_id();
		}
		if(!$id){
			return false;
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			return false;
		}
		$info = ($ext && is_array($ext)) ? serialize($ext) : '';
		$this->model('plugin')->update_param($id,$info);
	}
	
	//加载插件模板
	final public function plugin_tpl($name,$id='')
	{
		if(!$id){
			$id = $this->plugin_id();
		}
		$file = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		if(file_exists($file)){
			return $this->fetch($file,'abs-file');
		}
		return false;
	}

	//输出HTML，不中止
	final public function show_tpl($name,$id='')
	{
		echo $this->plugin_tpl($name,$id);
	}

	//输出插件模板，为空不输出任何信息
	final public function echo_tpl($name,$id='')
	{
		if(!$id){
			$id = $this->plugin_id();
		}
		$file = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		if(file_exists($file)){
			$this->view($file,'abs-file');
		}
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
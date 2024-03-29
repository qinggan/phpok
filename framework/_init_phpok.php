<?php
/**
 * 核心应用类
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html> https://www.phpok.com/lgpl.html
 * @时间 2021年3月1日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

/**
 * PHPOK4最新框架，一般不直接调用此框架
 * @更新时间 2016年06月05日
**/
#[\AllowDynamicProperties]
class _init_phpok
{
	/**
	 * 指定app_id，该id是通过入口的**APP_ID**来获取，留空使用www
	**/
	public $app_id = "www";

	/**
	 * 控制器及方法
	**/
	public $ctrl = 'index';
	public $func = 'index';

	/**
	 * 定义网站程序根目录，对应入口的**ROOT**，为空使用./
	**/
	public $dir_root = "./";

	/**
	 * 框架目录，对应入口的**FRAMEWORK**，为空使用phpok/
	**/
	public $dir_phpok = "phpok/";

	public $dir_data = './_data/';
	public $dir_cache = './_cache/';
	public $dir_config = './_config/';
	public $dir_extension = './_extension/';
	public $dir_plugin = './_plugins/';
	public $dir_app = './_app/';

	/**
	 * 定义引挈，在P4中，将MySQL，Cache，Session设为三个引挈（后续版本可能会改动）
	**/
	public $engine;

	/**
	 * 配置信息，对应framework/config/目录下的内容及根目录的config.php里的信息
	**/
	public $config;

	/**
	 * 定义版本，该参数会被常量VERSION改变，如使用了在线升级，会被update.xml里改变，即
	 * 优先级是：update.xml > version.php > 自身
	**/
	public $version = "4.0";

	/**
	 * 当前时间，该时间是经常config里的两个参数timezone和timetuning调整过的，适用于虚拟主机用户无法较正服务器时间用的
	**/
	public $time;

	/**
	 * 当前网址，由系统生成，在模板中直接使用{$sys.url}输出
	**/
	public $url;

	/**
	 * 授权类型，对应license.php里的常量LICENSE
	**/
	public $license = "MIT";

	/**
	 * 授权码，16位或32位的授权码，要求全部大写，对应license.php里的常量LICENSE_CODE
	**/
	public $license_code = "";

	/**
	 * 授权时间，对应license.php里的常量LICENSE_DATE
	**/
	public $license_date = "";

	/**
	 * 授权者称呼，企业授权填写公司名称，个人授权填写姓名，对应license.php里的常量LICENSE_NAME
	**/
	public $license_name = "phpok";

	/**
	 * 授权的域名，注意必须以.开始，仅支持国际域名，二级域名享有国际域名授权，对应license.php里的常量LICENSE_SITE
	**/
	public $license_site = "phpok.com";

	/**
	 * 显示开发者信息，即Powered by信息，对应license.php里的常量LICENSE_POWERED
	**/
	public $license_powered = true;

	/**
	 * 是否是手机端，如果使用手机端可能会改写网址，此项受config配置里的mobile相关参数影响
	**/
	public $is_mobile = false;

	/**
	 * 定义插件
	**/
	public $plugin;

	/**
	 * 通过framework/form/里实现自定义扩展动态调用CSS样式，后续版本将抛弃此功能
	**/
	public $csslist;

	/**
	 * 通过framework/form/里实现自定义扩展动态调用js文件，后续版本将抛弃此功能
	**/
	public $jslist;

	/**
	 * 语言包，默认使用gettext方法，系统不支持将使用第三方扩展读取pomo文件
	**/
	public $lang;

	/**
	 * 语言ID，暂时生成的网址不支持带语言参数
	**/
	public $langid;

	/**
	 * 语言读取言式，通过系统检测，支持gettext和user两种
	**/
	private $language_status = 'user';

	/**
	 * 网关路由接口，对应文件夹gateway里的PHP执行
	**/
	public $gateway;

	/**
	 * 数据传输是否使用Ajax
	**/
	public $is_ajax = false;

	private $_libs = array();

	private $_dataParams = array();

	private $_iscmd = false;

	private $_api_code = '';

	private $_counter_node_plugin = false;

	/**
	 * 标记已执行过的AP节点及插件节点
	**/
	private $_ext_all = array();

	/**
	 * 构造函数，用于初化一些数据
	**/
	public function __construct($iscmd=false)
	{
		$this->_iscmd = $iscmd;
		$this->init_constant();
		$this->init_config();
		require_once $this->dir_extension.'autoload.php';
	}

	/**
	 * 变量参数核心处理
	 * @参数 $id 变量名
	 * @参数 $val 变量值
	 * @参数 $type 变量类型，system 系统变量，config 配置变量，site 站点变量
	**/
	final public function config($id,$val='',$type='system')
	{
		if($id == 'debug' && is_bool($val)){
			if($val){
				if(function_exists('opcache_reset')){
					ini_set('opcache.enable',false);
				}
				ini_set('display_errors','on');
				error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
			}else{
				error_reporting(0);
				if(isset($this->config) && isset($this->config['opcache']) && function_exists('opcache_reset')){
					ini_set('opcache.enable',$this->config['opcache']);
				}
			}
			return true;
		}
		if($type == 'system'){
			$this->$id = $val;
		}
		if($type == 'config'){
			$this->config[$id] = $val;
		}
		if($type == 'site'){
			$this->site[$id] = $val;
		}
		return true;
	}

	final public function data($var='',$val='')
	{
		if(!$var){
			return $this->_dataParams;
		}
		if($val == ''){
			if(strpos($var,'.') === false){
				return $this->_dataParams[$var];
			}
			$list = explode(".",$var);
			if(!isset($this->_dataParams[$list[0]]) || !is_array($this->_dataParams[$list[0]])){
				return false;
			}
			$tmp = $this->_dataParams[$list[0]];
			foreach($list as $key=>$value){
				if($key<1){
					continue;
				}
				if(!isset($tmp[$value])){
					$tmp = false;
					break;
				}else{
					$tmp = $tmp[$value];
				}
			}
			return $tmp;
		}
		if(strpos($var,'.') !== true){
			$this->_dataParams[$var] = $val;
			return true;
		}
		$list = explode(".",$var);
		krsort($list);
		$tmp = array();
		$total = count($list);
		$i=0;
		foreach($list as $key=>$value){
			if($i<1){
				$tmp[$value] = $val;
			}else{
				if(($i+1) == $total){
					if(isset($this->_dataParams[$value])){
						$this->_dataParams[$value] = array_merge($this->_dataParams[$value],$tmp);
					}else{
						$this->_dataParams[$value] = $tmp;
					}
				}else{
					$ok = array();
					$ok[$value] = $tmp;
					$tmp = $ok;
				}
			}
			$i++;
		}
		return true;
	}

	final public function undata($var)
	{
		if(!$var){
			return false;
		}
		if(strpos($var,'.') === false){
			unset($this->_dataParams[$var]);
			return true;
		}
		$list = explode(".",$var);
		$total = count($list);
		$list = explode(".",$var);
		krsort($list);
		$i=0;
		foreach($list as $key=>$value){
			if($i<1){
				$tmp = array();
			}else{
				if(($i+1) == $total){
					$this->_dataParams[$value] = $tmp;
				}else{
					$ok = array();
					$ok[$value] = $tmp;
					$tmp = $ok;
				}
			}
			$i++;
		}
		return true;
	}

	/**
	 * 加载语言包
	 * @参数 $langid 字符串，留空加载cn，中文不需要加载语言包
	 * @更新时间 2016年06月05日
	**/
	public function language($langid='cn')
	{
		if($langid == 'cn'){
			return true;
		}
		include_once($this->dir_phpok.'language.php');
		$this->language = new phpok_language($langid);
		$this->language->status($multiple_language);
		$this->language->folder($this->dir_root.'langs');
		$this->language->id($this->app_id);
		$this->language->pomo($this->dir_extension);
		return true;
	}

	/**
	 * 语言包变量格式化，$info将转化成系统的语言包，同是将$info里的带{变量}替换成$var里传过来的信息
	 * @参数 $info 字符串，要替变的字符串用**{}**包围，包围的内容对应$var里的$key
	 * @参数 $var 数组，要替换的字符。
	 * @返回 字符串，$info为空返回false
	 * @更新时间 2016年06月05日
	**/
	final public function lang_format($info,$var='')
	{
		if($this->langid == 'cn'){
			return $this->_lang_format($info,$var);
		}
		if(!$this->language){
			$this->language($this->langid);
		}
		return $this->language->format($info,$var);
	}

	private function _lang_format($info,$var='')
	{
		if($var && (is_string($var) || is_int($var) || is_float($var))){
			$var = explode(",",$var);
		}
		if($var && is_array($var)){
			foreach($var as $key=>$value){
				$info = str_replace(array('{'.$key.'}','['.$key.']'),'<b>'.$value.'</b>',$info);
			}
		}
		return $info;
	}

	public function is_cmd()
	{
		return $this->_iscmd;
	}

	/**
	 * 手机判断，使用了第三方扩展extension里的mobile类
	**/
	public function is_mobile()
	{
		if($this->lib('mobile')->is_mobile()){
			return true;
		}
		return false;
	}

	/**
	 * 判断是否启用https
	**/
	protected function is_https()
	{
		if($this->config['force_https']){
			return true;
		}
		return $this->lib('server')->https();
	}

	/**
	 * 初始化网址要输出的一些全局信息，如网站信息，初始化后的SEO信息
	**/
	private function init_assign()
	{
		$url = $this->url;
		$afile = $this->config[$this->app_id.'_file'];
		if(!$afile){
			$afile = 'index.php';
		}
		$url .= $afile;
		if($this->lib('server')->query()){
			$url .= "?".$this->lib('server')->query();
		}
		$this->site["url"] = $url;
		$this->config["url"] = $this->url;
		$this->config['app_id'] = $this->app_id;
		$this->config['time'] = $this->time;
		$this->config['webroot'] = $this->dir_webroot;
		$this->assign("sys",$this->config);
		$this->assign("config",$this->site);
		$langid = $this->get("_langid");
		if($this->app_id == 'admin'){
			if(!$langid && $this->session()->val('admin_lang_id')){
				$langid = $this->session()->val('admin_lang_id');
			}
			if(!$langid || $langid == 'default'){
				$langid = $this->config['langid'] ? $this->config['langid'] : 'cn';
			}
			$this->session()->assign('admin_lang_id',$langid);
		}else{
			if(!$langid && $this->config['multiple_language'] && isset($this->site['lang'])){
				$langid = $this->site['lang'];
			}
			if(!$langid && isset($this->config['langid'])){
				$langid = $this->config['langid'];
			}
			if(!$langid || $langid == 'default'){
				$langid = 'cn';
			}
			$this->session()->assign($this->app_id.'_lang_id',$langid);
		}
		$this->langid = $langid;
		if($this->langid != 'cn'){
			$this->language($this->langid);
		}
		$this->assign('langid',$this->langid);
	}

	public function load_config($name='')
	{
		$config = array();
		if(file_exists($this->dir_config.$name.'.ini.php')){
			$config = parse_ini_string(file_get_contents($this->dir_config.$name.'.ini.php'),true);
		}
		if($config){
			foreach($config as $k=>$v){
				$v = preg_replace_callback('/\{(.+)\}/isU',array($this,'_config_ini_format'),$v);
				$config[$k] = $v;
			}
		}
		return $config;
	}

	/**
	 * 读取网站参数配置
	 * @更新时间 2016年02月05日
	 */
	private function init_config()
	{
		$config = array();
		if(file_exists($this->dir_config.'global.ini.php')){
			$config = parse_ini_string(file_get_contents($this->dir_config.'global.ini.php'),true);
		}
		//装载引挈参数
		if(file_exists($this->dir_config.'engine.ini.php')){
			$ext = parse_ini_string(file_get_contents($this->dir_config.'engine.ini.php'),true);
			if($ext && is_array($ext)){
				$config['engine'] = $ext;
				unset($ext);
			}
		}
		if(file_exists($this->dir_config.$this->app_id.'.ini.php')){
			$ext = parse_ini_string(file_get_contents($this->dir_config.$this->app_id.'.ini.php'),true);
			if($ext && is_array($ext)){
				$config = array_merge($config,$ext);
				unset($ext);
			}
		}
		$list = glob($this->dir_config."*.ini.php");
		$forbid = array("global.ini.php","www.ini.php","admin.ini.php","api.ini.php");
		foreach($list as $key=>$value){
			$tmp = basename($value);
			if(in_array($tmp,$forbid)){
				continue;
			}
			$ext = parse_ini_string(file_get_contents($value),true);
			$tmpid = str_replace(".ini.php","",$tmp);
			$config[$tmpid] = $ext;
		}

		if($config['debug']){
			if(function_exists('opcache_reset')){
				ini_set('opcache.enable',false);
			}
			ini_set('display_errors','on');
			error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
		}else{
			error_reporting(0);
			if(isset($config['opcache']) && function_exists('opcache_reset')){
				ini_set('opcache.enable',$config['opcache']);
			}
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
		if(!$config['get_domain_method']){
			$config['get_domain_method'] = 'HTTP_HOST';
		}
		//定义是否允许嵌套
		if($config['iframe_options'] && $config['iframe_options'] != '*'){
			$tmp = array('DENY','SAMEORIGIN');
			if(in_array(strtoupper($config['iframe_options']),$tmp)){
				header("X-Frame-Options: ".strtolower($config['iframe_options']));
			}
			if(substr(strtoupper($config['iframe_options']),0,10) == 'ALLOW-FROM'){
				header("X-Frame-Options: ".strtolower($config['iframe_options']));
			}
		}
		if($config['cross_domain']){
			$tmp = $config['cross_domain_origin'] ? explode(",",$config['cross_domain_origin']) : array("*");
			foreach($tmp as $k=>$v){
				header("Access-Control-Allow-Origin: ".$v);
			}
			header("Access-Control-Allow-Headers: *");
		}
		$this->config = $config;
		unset($config);
	}

	/**
	 * 连接数据库
	 * @参数 $name 参数文件，如果是 mysqli，配置文件为 db-mysqli.ini.php，为空则读 db.ini.php
	**/
	public function db($name='')
	{
		$ini = $name ? 'db-'.$name : 'db';
		$config = $this->load_config($ini);
		return $this->engine('db',$config);
	}

	/**
	 * 连接SESSION
	 * @参数 $name 参数文件，如果是 file，配置文件为 cache-file.ini.php，为空则读 cache.ini.php
	**/
	public function session($name='')
	{
		$ini = $name ? 'session-'.$name : 'session';
		$config = $this->load_config($ini);
		return $this->engine('session',$config);
	}

	/**
	 * 连接缓存服务器
	 * @参数 $name 参数文件，如果是 file，配置文件为 cache-file.ini.php，为空则读 cache.ini.php
	**/
	public function cache($name='')
	{
		$ini = $name ? 'cache-'.$name : 'cache';
		$config = $this->load_config($ini);
		return $this->engine('cache',$config);
	}

	/**
	 * 连Key-Value存服务器
	 * @参数 $name 参数文件，如果是 redis，配置文件为 kv-redis.ini.php，为空则读 kv.ini.php
	**/
	public function kv($name='')
	{
		$ini = $name ? 'kv-'.$name : 'kv';
		$config = $this->load_config($ini);
		return $this->engine('kv',$config);
	}

	public function engine($name='',$config=array())
	{
		if(!$name){
			return false;
		}
		$engine_name = $name;
		//检测引挈类是否存在
		if(isset($this->$engine_name)){
			return $this->$engine_name;
		}
		if(!$config){
			$config = $this->load_config($name);
		}
		include_once($this->dir_phpok.'engine/'.$name.'.php');
		if(!$config['file']){
			$this->$engine_name  = new $name($config);
			return $this->$engine_name;
		}
		$extfile = $this->dir_phpok.'engine/'.$name.'/'.$config['file'].'.php';
		if(!file_exists($extfile)){
			$this->$engine_name  = new $name($config);
			return $this->$engine_name;
		}
		include_once($extfile);
		$var = $name.'_'.$config['file'];
		$this->$engine_name = new $var($config);
		return $this->$engine_name;
	}

	/**
	 * 装载插件，程序在初始化时就执行插件加载，一次性加载但未运行，
	 * 如果插件编写有问题，会直接无法运行。因此加载插件时请仔细检查。
	**/
	public function init_plugin()
	{
		$rslist = $this->model('plugin')->get_all(1);
		if(!$rslist){
			return false;
		}
		$param = array();
		foreach($rslist as $key=>$value){
			if($value['param']){
				$value['param'] = unserialize($value['param']);
			}
			if(file_exists($this->dir_plugin.$key.'/'.$this->app_id.'.php')){
				include_once($this->dir_plugin.$key.'/'.$this->app_id.'.php');
				$name = $this->app_id."_".$key;
				$cls = new $name();
				$mlist = get_class_methods($cls);
				$this->plugin[$key] = array("method"=>$mlist,"obj"=>$cls,'id'=>$key);
				$param[$key] = $value;
			}
		}
		$this->assign('plugin',$param);
	}

	/**
	 * 初始化加载站点信息，后台仅加载站点信息，返回true，前端会执行域名判断，手机判断，及模板加载
	**/
	public function init_site()
	{
		$site_id = $this->get("siteId","int");
		if(!$site_id){
			$site_id = $this->session()->val('siteId');
		}
		$this->url = $this->root_url($site_id);
		if($this->app_id == "admin"){
			if($this->session()->val('admin_site_id')){
				$site_rs = $this->model('site')->get_one($this->session()->val('admin_site_id'));
			}else{
				$site_rs = $this->model("site")->get_one_default();
			}
			if(!$site_rs){
				$site_rs = array('title'=>'PHPOK.Com');
			}
			$this->site = $site_rs;
			return true;
		}
		$domain = $this->lib('server')->domain();
		if(!$domain){
			$this->_error('无法获取网站域名信息，请检查环境是否支持 SERVER_NAME 或 HTTP_HOST');
		}
		$site_rs = $this->model('site')->site_info(($site_id ? $site_id : $domain));
		if(!$site_rs && $this->app_id == 'www'){
			$this->_error('网站信息不存在或未启用');
		}
		if(!$site_rs['is_default']){
			$site_default = $this->model('site')->get_one_default();
			$tmplist = array();
			if($site_default && $site_default['_domain']){
				foreach($site_default['_domain'] as $key=>$value){
					$tmplist[] = $value['domain'];
				}
			}
			if(in_array($domain,$tmplist) && !defined( 'PHPOK_SITE_ID' )){
				define("PHPOK_SITE_ID",$site_rs['id']);
			}
		}
		$url_type = $this->is_https() ? 'https://' : 'http://';
		if($this->app_id == 'www'){
			if($this->config['mobile']['status']){
				$this->is_mobile = $this->config['mobile']['default'];
				if(!$this->is_mobile && $this->config['mobile']['autocheck']){
					$this->is_mobile = $this->is_mobile();
				}
			}
			if(isset($site_rs['_mobile'])){
				if($site_rs['_mobile']['domain'] == $domain){
					$this->url = $url_type.$site_rs['_mobile']['domain'].$site_rs['dir'];
					$this->is_mobile = true;
				}else{
					if($this->is_mobile){
						$url = $url_type.$site_rs['_mobile']['domain'].$site_rs['dir'];
						if(substr($url,-1) != '/'){
							$url .= '/';
						}
						$pathinfo = $this->lib('server')->path_info();
						if($pathinfo){
							$url .= $pathinfo;
						}else{
							$url .= $this->config['www_file'];
						}
						$this->_location($url);
						exit;
					}
				}
			}
			if($site_id && is_numeric($site_id) && $site_rs['domain'] && $site_rs['domain'] != $domain){
				$url = $url_type.$site_rs['domain'].$site_rs['dir'];
				if(substr($url,-1) != '/'){
					$url .= '/';
				}
				$url .= $this->config['www_file'];
				$this->_location($url);
				exit;
			}
		}
		$this->data('site_rs',$site_rs);
		$this->node('system_init_site');
		$site_rs = $this->data('site_rs');
		//自定义模板
		$tpl_id = $this->get("_tpl","int");
		if(!$tpl_id){
			$tpl_id = $site_rs['tpl_id'];
		}
		$rs = $this->model('tpl')->get_one($tpl_id);
		if($site_rs && $rs){
			$tpl_rs = array('id'=>$rs['id'],'dir_root'=>$this->dir_root);
			$tpl_rs['dir_tplroot'] = 'tpl/';
			$tpl_rs["dir_tpl"] = $rs["folder"] ? "tpl/".$rs["folder"]."/" : "tpl/www/";
			if($this->dir_webroot && $this->dir_webroot != '.' && $this->dir_webroot != './'){
				$tmp = $this->dir_webroot;
				if(substr($tmp,-1) != '/'){
					$tmp .= '/';
				}
				$tpl_rs["dir_tpl"] = $tmp.$tpl_rs["dir_tpl"];
			}
			$tpl_rs["dir_cache"] = $this->dir_data."tpl_www/";
			$this->lib('file')->make($tpl_rs['dir_cache']);
			$tpl_rs["dir_php"] = $rs['phpfolder'] ? $this->dir_root.$rs['phpfolder'].'/' : $this->dir_root.'phpinc/';
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
				$tpl_rs["dir_tpl"] = "tpl/".$tplfolder."/";
			}
			$tpl_rs['langid'] = $this->langid;
			$site_rs["tpl_id"] = $tpl_rs;
		}
		//货币检测
		$currency_id = $site_rs['currency_id'];
		if($this->session()->val('currency')){
			$currency_id = $this->session()->val('currency.id');
		}
		$currency_code = $this->get("_currency");
		if($currency_code && !is_numeric($currency_code)){
			$currency_rs = $this->model('currency')->get_one($currency_code,'code');
			if($currency_rs){
				$currency_id = $currency_rs['id'];
			}
		}
		$currency_rs = $this->model('currency')->get_one($currency_id,'id');
		if($currency_rs){
			$this->session()->assign('currency',$currency_rs);
			$site_rs['currency'] = $currency_rs;
		}
		$this->site = $site_rs;
	}


	/**
	 * 加载视图引挈，后台加载framework/view/下的模板文件，css，js，images路径不会修改。前端加载tpl/下的模板文件
	**/
	public function init_view()
	{
		include_once($this->dir_phpok."phpok_tpl.php");
		$this->model('url')->ctrl_id($this->config['ctrl_id']);
		$this->model('url')->func_id($this->config['func_id']);
		if($this->app_id == "admin"){
			$tpl_rs = array();
			$tpl_rs["id"] = "1";
			$tpl_rs["dir_tpl"] = substr($this->dir_phpok,strlen($this->dir_root))."/view/";
			$tpl_rs["dir_cache"] = $this->dir_data."tpl_admin/";
			$tpl_rs["dir_php"] = $this->dir_root;
			$tpl_rs["dir_root"] = $this->dir_root;
			$tpl_rs["refresh_auto"] = true;
			$tpl_rs["tpl_ext"] = "html";
			//定制语言模板ID
			$tpl_rs['langid'] = 'cn';
			if($this->session()->val('admin_lang_id')){
				$tpl_rs['langid'] = $this->session()->val('admin_lang_id');
			}
			$this->lib('file')->make($tpl_rs['dir_cache']);
			$this->tpl = new phpok_template($tpl_rs);
		}else{
			if($this->app_id == 'www'){
				if(!$this->site["tpl_id"] || ($this->site["tpl_id"] && !is_array($this->site["tpl_id"]))){
					$this->_error("未指定模板文件");
				}
			}
			$this->model('site')->site_id($this->site['id']);
			$this->model('url')->base_url($this->url);
			$this->model('url')->set_type($this->site['url_type']);
			$this->model('url')->protected_ctrl($this->model('site')->reserved());
			//初始化伪静态中需要的东西
			if($this->site['url_type'] == 'rewrite'){
				$this->model('url')->site_id($this->site['id']);
				$this->model('rewrite')->site_id($this->site['id']);
				$this->model('url')->rules($this->model('rewrite')->get_all());
				$this->model('url')->page_id($this->config['pageid']);
			}
			$this->tpl = new phpok_template($this->site["tpl_id"]);
		}
		include($this->dir_phpok."phpok_call.php");
		$this->call = new phpok_call();
		include_once($this->dir_phpok."phpok_tpl_helper.php");
		if($this->app_id == 'www' && !$this->site['status']){
			$close = $this->site['content'] ? $this->site['content'] : P_Lang('网站暂停关闭');
			$this->_tip($close,2);
		}
		///
	}

	/**
	 * 动态引态第三方类包，官方提供的类包在framework/libs/下，用户自行编写的class放在extension目录下。
	 * 请注意，extension支持下的类支持config.inc.php配置自动执行
	 * config.inc.php支持的参数有：
	 * 		1. auto，自动运行的方法
	 *		2. include，包含这个类下需要调用的其他php文件，多个文件用英文逗号隔开，仅支持相对路径
	 * @参数 $class，类的名称，第三方对应的是文件夹名称，要求全部小写
	**/
	public function lib($class='',$param='')
	{
		if(!$class){
			return false;
		}
		if(isset($this->_libs) && $this->_libs && isset($this->_libs[$class]) && $this->_libs[$class]){
			$config = $this->_libs[$class];
		}else{
			$config = array('param'=>$param,'include'=>'','auto'=>'','classname'=>$class.'_lib');
			if(file_exists($this->dir_extension.$class.'/config.inc.php')){
				include($this->dir_extension.$class.'/config.inc.php');
				$list = $config['include'] ? explode(",",$config['include']) : array();
				foreach($list as $key=>$value){
					if(substr(strtolower($value),-4) != '.php'){
						$value .= '.php';
					}
					if(file_exists($this->dir_extension.$class.'/'.$value)){
						include_once($this->dir_extension.$class.'/'.$value);
					}
				}
			}
			$this->_libs[$class] = $config;
		}
		$tmp = isset($config['classname']) ? $config['classname'] : $class.'_lib';
		if(isset($this->$tmp) && is_object($this->$tmp)){
			return $this->$tmp;
		}
		$vfile = array($this->dir_phpok.'libs/'.$class.'.php');
		$vfile[] = $this->dir_phpok.'libs/'.$class.'.phar';
		$vfile[] = $this->dir_extension.$class.'.phar';
		$vfile[] = $this->dir_extension.$class.'/phpok.php';
		$vfile[] = $this->dir_extension.$class.'/index.php';
		$vfile[] = $this->dir_extension.$class.'.php';
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
				$this->$tmp->$value();
			}
		}
		return $this->$tmp;
	}

	/**
	 * 按需加载 Control 类文件，以实现control里数据交叉处理
	 * @参数 $name 字符串，方法名称
	 * @参数 $appid 字符串，指定APP_ID，不指定使用内置
	**/
	public function control($name,$appid='')
	{
		if($appid && !in_array($appid,array('www','api','admin'))){
			$appid = $this->app_id;
		}
		if(!$appid){
			$appid = $this->app_id;
		}
		$class_name = $appid.'_'.$name.'_control';
		if($this->$class_name && is_object($this->$class_name)){
			return $this->$class_name;
		}
		if(is_file($this->dir_app.$name.'/'.$appid.'.control.php')){
			return $this->_ctrl_phpok5($name,$appid);
		}
		$file = $this->dir_phpok.'/'.$appid.'/'.$name.'_control.php';
		if(!is_file($file)){
			return false;
		}
		include_once($file);
		$class_name2 = $name.'_control';
		$this->$class_name = new $class_name2();
		return $this->$class_name;
	}

	public function ctrl($name,$appid='')
	{
		return $this->control($name,$appid);
	}

	private function _node($name)
	{
		$class_name = $name.'_nodes';
		if(isset($this->$class_name) && is_object($this->$class_name)){
			return $this->$class_name;
		}
		if(!is_file($this->dir_app.$name.'/nodes.php')){
			return false;
		}
		include_once($this->dir_app.$name.'/nodes.php');
		$tmp = '\phpok\app\\'.$name.'\\nodes_phpok';
		$this->$class_name = new $tmp();
		return $this->$class_name;
	}

	private function _node2html($name)
	{
		$class_name = $name.'_nodes_html';
		if(isset($this->$class_name) && is_object($this->$class_name)){
			return $this->$class_name;
		}
		if(!is_file($this->dir_app.$name.'/html.php')){
			return false;
		}
		include_once($this->dir_app.$name.'/html.php');
		$tmp = '\phpok\app\\'.$name.'\\html_phpok';
		$this->$class_name = new $tmp();
		return $this->$class_name;
	}

	/**
	 * 按需加载Model信息，所有的文件均放在framework/model/目录下。会根据**app_id**自动加载同名但不同入口的文件
	 * @参数 $name，字符串
	 * @参数 $check 是否用于检测，如果设为true检测异常只返回 false 并不报错
	 * @返回 实例化后的类，出错则中止运行报错
	 * @更新时间 2016年06月05日
	**/
	public function model($name,$check=false)
	{
		$class_name = $name."_model";
		$class_base = $name."_model_base";
		//扩展类存在，读扩展类
		if(isset($this->$class_name) && is_object($this->$class_name)){
			return $this->$class_name;
		}
		//扩展类不存在，只有基类，则读基类
		if(isset($this->$class_base) && is_object($this->$class_base)){
			return $this->$class_base;
		}
		//检查是否有 phpok5 使用的类
		$model_file = $this->dir_app.$name.'/model.php';
		if(is_file($model_file)){
			return $this->_model_phpok5($name);
		}
		$basefile = $this->dir_phpok.'model/'.$name.'.php';
		if(!file_exists($basefile)){
			if($check){
				return false;
			}
			$this->error_404("Model基础类：".$name." 不存在，请检查");
		}
		include($basefile);
		$extfile = $this->dir_phpok.'model/'.$this->app_id.'/'.$name.'_model.php';
		if(file_exists($extfile)){
			include($extfile);
			$this->$class_name = new $class_name();
			return $this->$class_name;
		}
		$this->$class_base = new $class_base();
		return $this->$class_base;
	}

	private function _ctrl_phpok5($name,$appid)
	{
		include_once($this->dir_app.$name.'/'.$appid.'.control.php');
		$class_name = $appid.'_'.$name.'_control';
		$tmp = 'phpok\app\control\\'.$name.'\\'.$appid.'_control';
		$this->$class_name = new $tmp();
		return $this->$class_name;
	}

	private function _model_phpok5($name)
	{
		$class_name = $name."_model";
		$class_base = $name."_model_base";
		include($this->dir_app.$name.'/model.php');
		if(is_file($this->dir_app.$name.'/'.$this->app_id.'.model.php')){
			include($this->dir_app.$name.'/'.$this->app_id.'.model.php');
			$tmp = 'phpok\app\model\\'.$name.'\\'.$this->app_id.'_model';
			$this->$class_name = new $tmp();
			return $this->$class_name;
		}
		$tmp = 'phpok\app\model\\'.$name.'\model';
		$this->$class_base = new $tmp();
		return $this->$class_base;
	}

	/**
	 * 运行插件
	 * @参数 $ap 字符串，对应插件下的方法
	 * @参数 $param 执行方法中涉及到的参数，字符串，可根据实际情况传入
	 * @返回 视插件运行返回，默认返回true或false
	 * @更新时间 2016年06月05日
	**/
	public function plugin($ap,$param="")
	{
		if(!$ap){
			return false;
		}
		$ap = str_replace("-","_",$ap);//替换节点的中划线为下划线
		if(!$this->plugin || count($this->plugin)<1 || !is_array($this->plugin)){
			return false;
		}
		$count = func_num_args();
		if($count>2){
			$tmp = array(0=>$param);
			for($i=2;$i<$count;$i++){
				$val = func_get_arg($i);
				$tmp[($i-1)] = $val;
			}
			foreach($this->plugin as $key=>$value){
				if($this->_ext_all && $this->_ext_all['plugin'] && $this->_ext_all['plugin'][$key] && $this->_ext_all['plugin'][$key][$ap]){
					continue;
				}
				if(in_array($ap,$value['method'])){
					if($ap == 'phpok_after' || $ap == 'ap_'.$this->ctrl.'_'.$this->func.'_after'){
						$this->_ext_all['plugin'][$key][$ap] = true;
					}
					call_user_func_array(array($value['obj'], $ap),$tmp);
				}
			}
			return true;
		}
		foreach($this->plugin as $key=>$value){
			if($this->_ext_all['plugin'][$key][$ap]){
				continue;
			}
			if(in_array($ap,$value['method'])){
				if($ap == 'phpok_after' || $ap == 'ap_'.$this->ctrl.'_'.$this->func.'_after'){
					$this->_ext_all['plugin'][$key][$ap] = true;
				}
				$value['obj']->$ap($param);
			}
		}
		return true;
	}

	final public function node($ap,$param='')
	{
		if(!$ap){
			return false;
		}
		$ap = str_replace("-","_",$ap);//替换节点的中划线为下划线
		$applist = $this->model('appsys')->installed();
		if(!$applist){
			return false;
		}
		if($ap == $this->app_id.'_after' || $ap == $this->app_id.'_'.$this->ctrl.'_'.$this->func.'_after'){
			return $this->_node4after($applist,$ap,$param);
		}

		$count = func_num_args();
		if($count>2){
			$tmp = array(0=>$param);
			for($i=2;$i<$count;$i++){
				$val = func_get_arg($i);
				$tmp[($i-1)] = $val;
			}
			foreach($applist as $key=>$value){
				$obj = $this->_node($key);
				if($obj && method_exists($obj,$ap)){
					call_user_func_array(array($obj, $ap),$tmp);
				}
			}
			return true;
		}
		foreach($applist as $key=>$value){
			$obj = $this->_node($key);
			if($obj && method_exists($obj,$ap)){
				$obj->$ap($param);
			}
		}
		return true;
	}

	private function _node4after($applist,$ap,$param='')
	{
		$chk = $this->_ext_all['node'];
		foreach($chk as $key=>$value){
			if(!$applist[$key]){
				continue;
			}
			//已执行的跳过
			if($value[$ap]){
				continue;
			}
			$obj = $this->_node($key);
			if(!$obj){
				continue;
			}
			if(method_exists($obj,$ap)){
				$this->_ext_all['node'][$key][$ap] = true;
				$obj->$ap($param);
			}
		}
		return true;
	}

	private function _node4html($ap,$param='')
	{
		if(!$ap){
			return false;
		}
		$ap = str_replace("-","_",$ap);//替换节点的中划线为下划线
		$applist = $this->model('appsys')->installed();
		if(!$applist){
			return false;
		}
		$count = func_num_args();
		if($count>2){
			$tmp = array(0=>$param);
			for($i=2;$i<$count;$i++){
				$val = func_get_arg($i);
				$tmp[($i-1)] = $val;
			}
			foreach($applist as $key=>$value){
				$obj = $this->_node2html($key);
				if($obj && method_exists($obj,$ap)){
					call_user_func_array(array($obj, $ap),$tmp);
				}
			}
			return true;
		}
		foreach($applist as $key=>$value){
			$obj = $this->_node2html($key);
			if($obj && method_exists($obj,$ap)){
				$obj->$ap($param);
			}
		}
		return true;
	}

	final public function _node_html($name)
	{
		$ap = $this->app_id.'-'.$this->ctrl.'-'.$this->func.'-'.$name;
		$this->_node4html($ap);
		$this->_node4html($this->app_id.'-'.$name);
	}

	/**
	 * 加载HTML插件节点
	 * @参数 $name 插件节点名称
	**/
	public function plugin_html_ap($name)
	{
		$ap = 'html-'.$this->ctrl.'-'.$this->func.'-'.$name;
		$this->plugin($ap);
		$this->plugin('html-'.$name);
	}

	private function _config_ini_format($array)
	{
		$tmp_array = array("dir_root"=>$this->dir_root,'dir_data'=>$this->dir_data,'dir_cache'=>$this->dir_cache);
		return $tmp_array[$array[1]];
	}

	/**
	 * 网址生成，在模板中通过{url ctrl=控制器 func=方法 id=标识 …/}生成网址
	 * @参数 $ctrl 字符串或数字，系统保留字串（$config[reserved]）为系统，非保留字符自动移成标识符或ID
	 * @参数 $func 字符串，当ctrl为标识或ID是，该参数对应cate里的标识
	 * @参数 $ext 字符串，扩展参数，格式为：变量名=变量值，多个扩展参数用&符号连接，示例：pageid=1&param=1
	 * @参数 $appid 字符串，留空自动调用当前页面系统使用的app_id，支持的字符串有：api，www，admin三个
	 * @参数 $baseurl 布尔值，为true时网址会带上{$sys.url}，即http://****，为false时，仅返回相对网址
	 * @返回 字符串，网址链接
	 * @更新时间 2016年06月05日
	**/
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
		if($this->config['is_baseurl'] || $baseurl){
			$root_url = $this->config['baseurl'] ? $this->config['baseurl'] : $this->url;
			$this->model('url')->base_url($root_url);
		}
		return $this->model('url')->url($ctrl,$func,$ext);
	}

	/**
	 * 自动生成网址，系统自带
	**/
	final public function root_url($siteId=0)
	{
		$http_type = $this->is_https() ? 'https://' : 'http://';
		$port = $this->lib('server')->port();
		if($siteId){
			$myurl = $this->model('site')->site_domain($siteId,$this->is_mobile);
		}
		if(!isset($myurl)){
			$myurl = $this->lib('server')->domain($this->config['get_domain_method']);
		}
		if(!$myurl){
			$this->_error('无法获取网站域名信息，请检查环境是否支持$_SERVER["SERVER_NAME"]或$_SERVER["HTTP_HOST"]');
		}
		if($port != "80" && $port != "443"){
			$myurl .= ":".$port;
		}
		$docu = $this->lib('server')->me();
		if($this->lib('server')->path_info()){
			$docu = substr($docu,0,-(strlen($this->lib('server')->path_info())));
		}
		$array = explode("/",$docu);
		$count = count($array);
		if($count>1){
			foreach($array as $key=>$value){
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
		//配置程序根目录
		if(!defined("ROOT")){
			define("ROOT",str_replace("\\","/",dirname(__FILE__))."/../");
		}
		$this->dir_root = ROOT;
		if(substr($this->dir_root,-1) != "/"){
			$this->dir_root .= "/";
		}
		//配置访问根目录
		if(!defined("WEBROOT")){
			define("WEBROOT",'/');
		}
		$this->dir_webroot = WEBROOT;
		if($this->dir_webroot == '.'){
			$this->dir_webroot = '';
		}
		if($this->dir_webroot && substr($this->dir_webroot,-1) != "/"){
			$this->dir_webroot .= "/";
		}
		//配置框架根目录
		if(!defined("FRAMEWORK")){
			define("FRAMEWORK",$this->dir_root."framework/");
		}
		$this->dir_phpok = FRAMEWORK;
		if(substr($this->dir_phpok,-1) != "/"){
			$this->dir_phpok .= "/";
		}
		$list = array('cache','config','data','extension','plugin','gateway');
		$extlist = array();
		foreach($list as $key=>$value){
			$tmp = strtoupper($value);
			if(!defined($tmp)){
				define($tmp,$this->dir_root.'_'.$value.'/');
			}
			$name = 'dir_'.$value;
			$this->$name = constant($tmp);
			if(substr($this->$name,-1) != "/"){
				$this->$name .= "/";
			}
		}
		if(!file_exists($this->dir_cache)){
			$this->lib('file')->make($this->dir_cache);
		}
		if(!defined('OKAPP')){
			define('OKAPP',ROOT.'_app/');
		}
		$this->dir_app = OKAPP;
		//定义APP_ID
		if(!defined("APP_ID")){
			define("APP_ID","www");
		}
		$this->app_id = APP_ID;
		//判断加载的版本及授权方式
		if(file_exists($this->dir_root."version.php")){
			include($this->dir_root."version.php");
			$this->version = defined("VERSION") ? VERSION : "4.5.0";
		}
		if(file_exists($this->dir_root."license.php")){
			include($this->dir_root."license.php");
			$license_array = array("MIT","PBIZ","CBIZ");
			$this->license = (defined("LICENSE") && in_array(LICENSE,$license_array)) ? LICENSE : "MIT";
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
		$this->is_ajax = $this->lib('server')->ajax();
	}

	/**
	 * 通过post或get取得数据，自动判断是否转义，未转义将自动转义，转义后执行格式化操作
	 * @参数 $id 字符串，要取得的数据ID，对应网页中的input里的name信息
	 * @参数 $type 字符串，格式化方式，默认是safe，支持：safe，html，html_js，float，int，checkbox，time，text，system等多种格式化方式
	 * @参数 $ext 数值或布尔值，为1或true时，在type为html时，等同于html_js，当type为func时，则ext为直接运行的函数
	 * 			  当 type 为 float,floatval,int,intval 时，ext 表示连接的字符，为空表示英文逗号
	 * @返回 格式化后的数据
	**/
	final public function get($id,$type="safe",$ext="")
	{
		$val = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : (isset($_COOKIE[$id]) ? $_COOKIE[$id] : ''));
		if($val == ''){
			if($type == 'int' || $type == 'intval' || $type == 'float' || $type == 'floatval'){
				return 0;
			}else{
				return '';
			}
		}
		//当变量为id，且使用默认 safe 传参时，进行值判断
		if($type == 'safe' && $id == 'id' && is_string($val)){
			if(!preg_match("/^[a-z0-9A-Z\_\-\,\x{4e00}-\x{9fa5}\x{3130}-\x{318F}\x{AC00}-\x{D7A3}\x{0800}-\x{4e00}]+$/u",$val)){
				return '';
			}
		}
		//判断内容是否有转义，所有未转义的数据都直接转义
		$addslashes = false;
		//修正5.4版本以上对是否转义的判断
		if(PHP_VERSION_ID<50400 && function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()){
			$addslashes = true;
		}
		if(!$addslashes){
			$val = $this->_addslashes($val);
		}
		return $this->format($val,$type,$ext);
	}

	/**
	 * 格式化内容
	 * @参数 $msg，要格式化的内容，该内容已经转义了
	 * @参数 $type，类型，支持：safe，text，html，html_js，func，int，float，system
	 * @参数 $ext，扩展，当type为html时，ext存在表示支持js，不存在表示不支持js，当type为func属性时，表示ext直接执行函数
	 * 			  当 type 为 float,floatval,int,intval 时，ext 表示连接的字符，为空表示英文逗号
	**/
	final public function format($msg,$type="safe",$ext="")
	{
		if($msg == ""){
			return '';
		}
		if(is_array($msg)){
			foreach($msg as $key=>$value){
				//支持中文，韩文，日文，字母，数字，下划线及中划线做键名
				//其他符号不支持
				if(!preg_match("/^[a-z0-9A-Z\_\-\,\x{4e00}-\x{9fa5}\x{3130}-\x{318F}\x{AC00}-\x{D7A3}\x{0800}-\x{4e00}]+$/u",$key)){
					unset($msg[$key]);
					continue;
				}
				$msg[$key] = $this->format($value,$type,$ext);
			}
			if($msg && count($msg)>0){
				return $msg;
			}
			return false;
		}
		//针对字符串，带逗号格式化操作
		if(is_string($msg) && strpos($msg,',') !== false && in_array($type,array('int','intval','float','floatval'))){
			$tmp_ext = $ext ? $ext : ',';
			$t = explode($tmp_ext,$msg);
			$t2 = array();
			foreach($t as $key=>$value){
				$value = $this->format($value,$type);
				if(!$value){
					continue;
				}
				$t2[] = $value;
			}
			return implode($tmp_ext,$t2);
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
		switch ($type){
			case 'safe_text':
				$msg = strip_tags($msg);
				$msg = str_replace(array("\\","'",'"',"<",">","&",";","(",")","0x"),'',$msg);
			break;
			case 'system':
				$msg = !preg_match("/^[a-zA-Z][a-z0-9A-Z\_\-]+$/u",$msg) ? false : $msg;
			break;
			case 'id':
				$msg = !preg_match("/^[a-z0-9A-Z\_\-]+$/u",$msg) ? false : $msg;
			break;
			case 'checkbox':
				$msg = strtolower($msg) == 'on' ? 1 : $this->format($msg,'safe');
			break;
			case 'int':
				$msg = intval($msg);
			break;
			case 'intval':
				$msg = intval($msg);
			break;
			case 'float':
				$msg = floatval($msg);
			break;
			case 'floatval':
				$msg = floatval($msg);
			break;
			case 'time':
				$msg = strtotime($msg);
			break;
			case 'html':
				$msg = $this->lib('string')->safe_html($msg,$this->url);
			break;
			case 'func':
				$msg = function_exists($ext) ? $ext($msg) : false;
			break;
			case 'text':
				$msg = strip_tags($msg);
			break;
			default:
				$msg = str_replace(array("\\","'",'"',"<",">"),array("&#92;","&#39;","&quot;","&lt;","&gt;"),$msg);
			break;
		}
		if($msg){
			$msg = addslashes($msg);
		}
		return $msg;
	}

	/**
	 * 安全的HTML信息，用于过滤iframe,script,link及html中涉及到的一些触发信息
	**/
	public function safe_html($info)
	{
		return $this->lib('string')->safe_html($info);
	}

	/**
	 * 转义数据
	**/
	private function _addslashes($val)
	{
		if(is_array($val)){
			foreach($val as $key=>$value){
				$val[$key] = $this->_addslashes($value);
			}
		}else{
			$val = addslashes($val);
		}
		return $val;
	}

	/**
	 * 分配信息给模板，使用模板中可调用
	 * @参数 $var 模板中要使用的变量名
	 * @参数 $val 要分配的信息
	**/
	final public function assign($var,$val)
	{
		$this->tpl->assign($var,$val);
	}

	/**
	 * 注销分配给模板中的变量信息
	 * @参数 $var 要注销的变量
	**/
	final public function unassign($var)
	{
		$this->tpl->unassign($var);
	}

	/**
	 * 视图输出，这是针对 phpok5 版写的，实现不同的路径的模板文件识别，不适合插件
	 * @参数 $file 相对文件
	**/
	final public function display($file,$return=false,$ext=false)
	{
		$tlist = array();
		//针对前端的识别
		if($this->app_id == 'www' && $this->site && $this->site['tpl_id']){
			$tmp = $this->site['tpl_id']['dir_tpl'].$this->ctrl.'/'.$file;
			if(!$ext){
				$tmp .= ".".$this->site['tpl_id']["tpl_ext"];
			}
			$tlist[] = $tmp;
			if(!$ext && $this->site['tpl_id']["tpl_ext"] != 'html'){
				$tmp = $this->site['tpl_id']['dir_tpl'].$this->ctrl.'/'.$file.'.html';
				$tlist[] = $tmp;
			}
			$tmp = $this->site['tpl_id']['dir_tpl'].$file;
			if(!$ext){
				$tmp .= ".".$this->site['tpl_id']["tpl_ext"];
			}
			$tlist[] = $tmp;
			if(!$ext && $this->site['tpl_id']["tpl_ext"] != 'html'){
				$tmp = $this->site['tpl_id']['dir_tpl'].$file.'.html';
				$tlist[] = $tmp;
			}
		}
		$tmp = $this->dir_app.$this->ctrl.'/tpl/'.$file;
		if(!$ext){
			$tmp .= ".html";
		}
		$tlist[] = $tmp;
		$tplfile = '';
		foreach($tlist as $key=>$value){
			if(file_exists($value)){
				$tplfile = $value;
				break;
			}
		}
		if(!$tplfile){
			$tplfile = $this->dir_app.$this->ctrl.'/tpl/'.$file;
			if(!$ext){
				$tmp .= ".html";
			}
		}
		if(file_exists($tplfile)){
			if($return){
				return $this->fetch($tplfile,'abs-file');
			}
			$this->view($tplfile,'abs-file');
		}
		if($return){
			return $this->fetch($file);
		}
		$this->view($file);
	}

	/**
	 * 输出HTML信息
	 * @参数 $file 字符串，指定的模板文件，支持不带后缀的模板名称，也支持完整的模板名称，也支持HTML内容，具体受参数$type影响
	 * @参数 $type 字符串，支持 file：不带后缀的模板名，file-ext：带后缀的模板名，
	 *                         content：直接是内容，msg：等同于content，abs-file：完整路径的模板文件
	 * @参数 $path_format 布尔值 是否格式化路径信息，慎用，模板里有大量嵌套，可能会混乱（未深度测试）
	 * @返回 无，直接输出HTML信息到设备上
	**/
	final public function view($file,$type="file",$path_format=true)
	{
		$this->_counter_node_plugin_code();
		$this->tpl->display($file,$type,$path_format);
	}

	/**
	 * 取得HTML信息，不输出到设备上，方便二次更改
	 * @参数 $file 字符串，指定的模板文件，支持不带后缀的模板名称，也支持完整的模板名称，也支持HTML内容，具体受参数$type影响
	 * @参数 $type 字符串，支持 file：不带后缀的模板名，file-ext：带后缀的模板名，
	 *                         content：直接是内容，msg：等同于content，abs-file：完整路径的模板文件
	 * @参数 $path_format 布尔值 是否格式化路径信息，慎用，模板里有大量嵌套，可能会混乱（未深度测试）
	 * @返回 字符串
	**/
	final public function fetch($file,$type="file",$path_format=true)
	{
		return $this->tpl->fetch($file,$type,$path_format);
	}

	/**
	 * 取得系统URL
	**/
	final public function get_url()
	{
		return $this->url;
	}

	/**
	 * 异常抛出，该错误主要用于未加载模板时使用，出现这个错误，表示程序无法正常运行，直接中止
	 * @参数 $content 字符串，在设备上要打印的错误信息
	**/
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

	/**
	 * 执行应用，三个入口（前端，接口，后台）都是从这里执行，进行初始化处理
	 * token 及 user_id 在 phpok5.0 中将剥离，不会放在核心引挈里
	**/
	final public function action()
	{
		if(!$this->_iscmd){
			$this->init_site();
		}
		$this->init_view();
		$this->init_assign();
		$this->init_plugin();
		if($this->app_id == 'admin'){
			$this->action_admin();
			exit;
		}
		if($this->app_id == 'api'){
			$this->action_api();
			exit;
		}
		$this->action_www();
	}

	/**
	 * 接口入口处理
	**/
	private function action_api()
	{
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

	private function _route()
	{
		$data = array();
		$uri = $this->lib('server')->uri();
		$docu = $this->lib('server')->me();
		if($this->lib('server')->path_info()){
			$docu = substr($docu,0,-(strlen($this->lib('server')->path_info())));
		}
		$array = explode("/",$docu);
		$docu = '/';
		$count = count($array);
		if($count>1){
			foreach($array as $key=>$value){
				$value = trim($value);
				if($value && ($key+1) < $count){
					$docu .= $value.'/';
				}
			}
		}
		if($docu != '/' && substr($uri,0,strlen($docu)) == $docu){
			$uri = substr($uri,(strlen($docu)-1));
		}
		$script_name = $this->lib('server')->phpfile();
		if('/'.$script_name == substr($uri,0,(strlen($script_name)+1))){
			$uri = substr($uri,(strlen($script_name)+1));
		}
		$data['script'] = $script_name;
		$query_string = $this->lib('server')->query();
		if($query_string){
			$uri = str_replace('?'.$query_string,'',$uri);
			$data['query'] = $query_string;
			parse_str($query_string,$get);
			$this->data('get',$get);
		}
		if($uri != '/' && strlen($uri)>2){
			if(substr($uri,0,1) == '/'){
				$uri = substr($uri,1);
			}
			if(substr($uri,-1) == '/'){
				$uri = substr($uri,0,-1);
			}
		}
		$data['url'] = $uri;
		$data['folder'] = $docu;
		$this->data('uri',$data);
		$this->model('rewrite')->uri_format($uri);
	}

	/**
	 * 前台入口处理
	**/
	private function action_www()
	{
		if($this->session()->val('user_id')){
			$me = $this->model('user')->get_one($this->session()->val('user_id'));
			if($me){
				$this->data('me',$me);
				$this->assign('me',$me);
			}
		}
		$this->model('site')->site_id($this->site['id']);
		$this->_route();
		$id = $this->get('id');
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = '';
		if($id && !$ctrl && $id != 'index'){
			$ctrl = $id;
			$reserved = $this->model('site')->reserved();
			if(!in_array($id,$reserved)){
				$ctrl = is_numeric($id) ? 'content' : $this->model('id')->get_ctrl($id,$this->site['id']);
				if(!$ctrl){
					$this->error_404();
				}
			}
			if($ctrl == 'post'){
				$cate = $this->get('cate','system');
				if($cate == 'add' || $cate == 'edit'){
					$func = $cate;
					unset($_GET['cate']);
				}
			}
		}
		if(!$ctrl){
			$ctrl = 'index';
		}
		if(!$func){
			$func = $this->get($this->config["func_id"],"system");
		}
		if(!$func){
			$func = 'index';
		}
		//针对乱七八糟网址进行清理
		$query = $this->data('uri.query');
		if($query && $this->config['safe_homepage'] && $this->config['get_params']){
			$params = explode(",",$this->config['get_params']);
			$params[] = $this->config['ctrl_id'];
			$params[] = $this->config['func_id'];
			$params = array_unique($params);
			parse_str($query,$tmp);
			if(!$tmp || !is_array($tmp)){
				$this->error_404(P_Lang('参数信息不正确'));
			}
			$has_key = false;
			foreach($tmp as $key=>$value){
				if(in_array($key,$params)){
					unset($tmp[$key]);
					$has_key = true;
					break;
				}
			}
			if(!$has_key){
				$this->error_404(P_Lang('您的请求信息不正确，请检查（无效参数）'));
			}
		}
		//针对乱七八糟的网址，或是路径进行清理
		if($ctrl == 'index' && $func == 'index'){
			$uri = $this->data('uri.url');
			if(substr($uri,-1) == '?'){
				$uri = substr($uri,0,-1);
			}
			$docu = $this->data('uri.folder');
			$script_name = $this->data('uri.script');
			$exit = false;
			if(is_file($this->dir_root.$uri)){
				$exit = true;
			}
			$uri = str_replace(array('index.html','index.htm',$this->config['www_file'],'index'),'',$uri);
			$basename = basename($docu);
			$folder = $docu;
			if($basename){
				$length = substr($docu,-1) == '/' ? strlen($basename)+1 : strlen($basename);
				$folder = substr($docu,0,-$length);
				if($uri && substr($uri,-(strlen($basename))) == $basename){
					$uri = substr($uri,0,-(strlen($basename)));
				}
			}
			if($uri && $uri != '/' && $folder && $uri != $folder && !$exit){
				$this->error_404(P_Lang('您的请求信息不正确，请检查（无效路由）'));
			}
		}
		$this->_action($ctrl,$func);
	}

	/**
	 * 后台入口处理
	**/
	private function action_admin()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl){
			$ctrl = "index";
		}
		if(!$func){
			$func = "index";
		}
		if($ctrl != 'login' && $ctrl != 'js' && !$this->config['develop']){
			$referer = $this->lib('server')->referer();
			if(!$referer && !$this->session()->val('admin_id')){
				$ctrl='login';
				$func = 'index';
				$this->_location($this->url('login'));
			}
			if($referer){
				$chk = parse_url($this->url);
				$info = parse_url($referer);
				if($info['host'] != $chk['host']){
					$ctrl = 'login';
					$func = 'index';
					$this->session()->destroy();
					$this->_location($this->url('login'));
				}
			}
		}
		$this->lib('form')->appid('admin');
		$this->_action($ctrl,$func);
	}

	/**
	 * 网页跳转，此跳转基于PHP执行
	 * @参数 $url 字符串，要跳转的网址
	**/
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

	/**
	 * 调用控制器及方法执行
	 * @参数 $ctrl 控制器名称，根据不同的入口调用不同的控制器
	 * @参数 $func 要执行的方法
	**/
	private function _action($ctrl='index',$func='index')
	{
		//如果App_id非指定的三种，强制初始化
		if(!in_array($this->app_id,array('api','www','admin'))){
			$this->app_id = 'www';
		}
		$reserved = array('login','js','ajax','inp','register');
		if($this->app_id == 'admin'){
			if(!$this->session()->val('admin_id') && !in_array($ctrl,$reserved)){
				$ctrl = 'login';
				$go_url = $this->url($ctrl);
				$this->_location($go_url);
			}
		}
		if($this->app_id == 'www'){
			$is_login = isset($this->config['is_login']) ? $this->config['is_login'] : false;
			if(isset($this->config[$this->app_id]['is_login'])){
				$is_login = $this->config[$this->app_id]['is_login'];
			}
			if($this->config['reserved']){
				$tmp = explode(",",$this->config['reserved']);
				$reserved = array_merge($reserved,$tmp);
				$reserved = array_unique($reserved);
			}
			if(isset($this->config[$this->app_id]['reserved'])){
				$tmp = explode(",",$this->config[$this->app_id]['reserved']);
				$reserved = array_merge($reserved,$tmp);
				$reserved = array_unique($reserved);
			}
			if($is_login && !$this->session()->val('user_id') && !in_array($ctrl,$reserved)){
				$ctrl = 'login';
				$go_url = $this->url($ctrl);
				$this->_location($go_url);
			}
		}

		if(file_exists($this->dir_phpok.$this->app_id."/global.func.php")){
			include($this->dir_phpok.$this->app_id."/global.func.php");
		}
		//前台后台都支持自定义加载的 global.func.php
		if(file_exists($this->dir_plugin."global.func.php")){
			include($this->dir_plugin."global.func.php");
		}
		if(file_exists($this->dir_extension."global.func.php")){
			include($this->dir_extension."global.func.php");
		}
		if(file_exists($this->dir_root."gateway/global.func.php")){
			include($this->dir_root."gateway/global.func.php");
		}
		//允许用户自定义加载 global.func.php 文件
		//前台及接口支持二个地方加载，分别是：data，phpinc
		if($this->app_id != 'admin'){
			if(file_exists($this->dir_data."global.func.php")){
				include($this->dir_data."global.func.php");
			}
			if(file_exists($this->dir_root."phpinc/global.func.php")){
				include($this->dir_root."phpinc/global.func.php");
			}
		}

		//针对各个插件下的 global.func.php 文件加载
		if($this->plugin && is_array($this->plugin)){
			foreach($this->plugin as $key=>$value){
				if(file_exists($this->dir_plugin.$key.'/global.func.php')){
					include_once($this->dir_plugin.$key.'/global.func.php');
				}
			}
		}

		//--- 增加 phpok5 写法
		if(file_exists($this->dir_app.'global.func.php')){
			include($this->dir_app."global.func.php");
		}
		if(file_exists($this->dir_app.$this->app_id.'.func.php')){
			include($this->dir_app.$this->app_id.".func.php");
		}
		$apps = $this->model('appsys')->installed();
		$protected_ctrl = array();
		if($apps){
			foreach($apps as $key=>$value){
				$protected_ctrl[] = $key;
				if(is_file($this->dir_app.$key.'/global.func.php')){
					include_once($this->dir_app.$key.'/global.func.php');
				}
				if(is_file($this->dir_app.$key.'/'.$this->app_id.'.func.php')){
					include_once($this->dir_app.$key.'/'.$this->app_id.'.func.php');
				}
			}
		}
		$this->model('url')->protected_ctrl($protected_ctrl);

		//自动运行的函数
		if(isset($this->config[$this->app_id]) && isset($this->config[$this->app_id]["autoload_func"])){
			$list = explode(",",$this->config[$this->app_id]["autoload_func"]);
			foreach($list as $key=>$value){
				if(function_exists($value)){
					$value();
				}
			}
			unset($list);
		}

		$appfile = $this->dir_app.$ctrl.'/'.$this->app_id.'.control.php';
		if($appfile && file_exists($appfile)){
			if(!$apps[$ctrl]){
				$this->error(P_Lang('应用未安装或未启用'));
			}
			$this->_action_phpok5($appfile,$ctrl,$func);
		}
		$this->_action_phpok4($ctrl,$func);
	}

	private function _action_phpok4($ctrl,$func)
	{
		$dir_root = $this->dir_phpok.$this->app_id.'/';
		if($ctrl == 'js' || $ctrl == 'ajax' || $ctrl == "inp"){
			$dir_root = $this->dir_phpok;
		}
		//加载应用文件
		if(!file_exists($dir_root.$ctrl.'_control.php')){
			$this->error(P_Lang('应用文件 {ctrl}_control.php 不存在，请检查',array("ctrl"=>$ctrl)));
		}
		$control_file = $dir_root.$ctrl.'_control.php';
		include($control_file);

		$app_name = $ctrl."_control";
		$this->ctrl = $ctrl;
		$this->func = $func;
		$cls = new $app_name();
		$func_name = $func."_f";
		if(!in_array($func_name,get_class_methods($cls))){
			$this->error(P_Lang('应用 {ctrl} 不存在方法 {func}',array('ctrl'=>$ctrl,'func'=>$func_name)));
		}
		$this->config['ctrl'] = $ctrl;
		$this->config['func'] = $func;
		$this->config['time'] = $this->time;
		$this->config['webroot'] = $this->dir_webroot;
		$this->config['is_mobile'] = $this->is_mobile;
		$this->assign('sys',$this->config);
		$this->node($this->app_id.'-before');
		$this->node($this->app_id.'-'.$this->ctrl.'-'.$this->func.'-before');
		$this->plugin('phpok-before');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-before');
		if($this->app_id == 'www' && !$this->site['status'] && !$this->session()->val('admin_id')){
			$this->error($this->site["content"]);
		}
		$cls->$func_name();
		exit;
	}

	private function _action_phpok5($appfile,$ctrl,$func)
	{
		include($appfile);
		$this->ctrl = $ctrl;
		$this->func = $func;
		$name = 'phpok\app\control\\'.$ctrl.'\\'.$this->app_id.'_control';
		$cls = new $name();
		$func_name = $func."_f";
		if(!in_array($func_name,get_class_methods($cls))){
			$this->error(P_Lang('应用 {ctrl} 不存在方法 {func}',array('ctrl'=>$ctrl,'func'=>$func_name)));
		}
		$this->config['ctrl'] = $ctrl;
		$this->config['func'] = $func;
		$this->config['time'] = $this->time;
		$this->config['webroot'] = $this->dir_webroot;
		$this->config['is_mobile'] = $this->is_mobile;
		$this->assign('sys',$this->config);
		$this->node($this->app_id.'-before');
		$this->node($this->app_id.'-'.$this->ctrl.'-'.$this->func.'-before');
		$this->plugin('phpok-before');
		$this->plugin('ap-'.$ctrl.'-'.$func.'-before');
		if($this->app_id == 'www' && !$this->site['status'] && !$this->session()->val('admin_id')){
			$this->error($this->site["content"]);
		}
		$cls->$func_name();
		exit;
	}

	/**
	 * JSON数据输出，要注意的是在输出时会触发插件，故该方法在插件使用要小心，防止出现死循环
	 * @参数 $content 要输出的内容，支持字符串，数组及布尔值，为布尔值是true直接输出 status=>ok，为false时输出 status=>error
	 * @参数 $status 布尔值，为true时输出status=>ok，false输出status=>error，并附带相应的内容content=>$content
	 * @参数 $exit 布尔值，为false时，不中止运行，会继续执行下面的PHP文件，一般不需要用到
	 * @返回 格式化后json数据
	 * @更新时间 2019年10月5日
	**/
	final public function json($content,$status=false,$exit=true)
	{
		//判断是否写入日志
		$_savelog = false;
		if($this->config['log']['status']){
			$_savelog = true;
			//状态成功的时候，不写入日志
			if(!$this->config['log']['type'] && ($status || (is_bool($content) && $content))){
				$_savelog = false;
			}
			if($this->config['log']['forbid'] && in_array($this->ctrl,explode(",",$this->config['log']['forbid']))){
				$_savelog = false;
			}
		}
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
			$this->_counter_node_plugin_code();
			exit($this->lib('json')->encode($rs));
		}
		$status_info = $status ? 'ok' : 'error';
		if($status_info == 'ok'){
			$this->_counter_node_plugin_code($content);
		}
		$rs = array('status'=>$status_info);
		if($content != ''){
			$rs['content'] = $content;
		}
		if($_savelog){
			$this->model('log')->save($content);
		}
		$info = $this->lib('json')->encode($rs);
		if($exit){
			exit($info);
		}
		return $info;
	}


	/**
	 * JSONP数据返回操作
	 * @参数 $content，混合型，为字符串或数组时，表示内容。为true或false时，status里的内容表示网址
	 * @参数 $status，状态，如果为字符串时，表示网址
	 * @参数 $url，网址，如果为true或false时表示状态
	 * @返回 字符串
	 * @更新时间 2016年06月11日
	**/
	final public function jsonp($content,$status=false,$url='')
	{
		$callback = $this->get($this->config['jsonp']['getid']);
		if(!$callback){
			$callback = $this->config['jsonp']['default'];
			if(!$callback){
				$callback = 'callback';
			}
		}
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT");
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=0");
		header("Pramga: no-cache");
		if(!$content && is_bool($content)){
			$rs = array('status'=>0);
			if($status && is_string($status)){
				$rs['url'] = $status;
			}
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		if($content && is_bool($content)){
			$rs = array('status'=>1);
			if($status && is_string($status)){
				$rs['url'] = $status;
			}
			$this->_counter_node_plugin_code();
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		if($status){
			$rs = array('info'=>$content);
			if(is_bool($status)){
				$rs['status'] = 1;
				if($url){
					$rs['url'] = $url;
				}
				$this->_counter_node_plugin_code($content);
			}else{
				$rs = array('info'=>$content,'url'=>$status);
				if($url && is_bool($url)){
					$rs['status'] = 1;
					$this->_counter_node_plugin_code($content);
				}
			}
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		$rs = array('status'=>0);
		$rs['info'] = $content;
		if($url && is_string($url)){
			$rs['url'] = $url;
		}
		exit($callback.'('.$this->lib('json')->encode($rs).')');
	}

	private function _counter_node_plugin_code($content='')
	{
		if(!$this->_ext_all || count($this->_ext_all)<1){
			$list = $this->_ext_all();
			if(!$list){
				return true;
			}
			$this->_ext_all = $list;
		}
		$act_node = $act_plugin = true;
		foreach($this->_ext_all as $key=>$value){
			if($key == 'node'){
				if(!$value){
					continue;
				}
				foreach($value as $k=>$v){
					if(!$v){
						continue;
					}
					foreach($v as $kk=>$vv){
						if(!$vv){
							$act_node = false;
							break;
						}
					}
				}
			}
			if($key == 'plugin'){
				if(!isset($value)){
					continue;
				}
				foreach($value as $k=>$v){
					if(!$v){
						continue;
					}
					foreach($v as $kk=>$vv){
						if(!$vv){
							$act_plugin = false;
							break;
						}
					}
				}
			}
		}
		if(!$act_node){
			$this->node($this->app_id.'-after');
			$this->node($this->app_id.'-'.$this->ctrl.'-'.$this->func.'-after',$content);
		}
		if(!$act_plugin){
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after',$content);
		}
	}

	private function _ext_all()
	{
		$list = array();
		$applist = $this->model('appsys')->installed();
		if($applist){
			foreach($applist as $key=>$value){
				$obj = $this->_node($key);
				if(!$obj){
					continue;
				}
				$mlist = get_class_methods($obj);
				if(!$mlist){
					continue;
				}
				foreach($mlist as $k=>$v){
					if($v == $this->app_id.'_after' || $v == $this->app_id.'_'.$this->ctrl.'_'.$this->func.'_after'){
						$list['node'][$key][$v] = false;
					}
				}
			}
		}
		if($this->plugin && count($this->plugin)>0 && is_array($this->plugin)){
			foreach($this->plugin as $key=>$value){
				foreach($value['method'] as $k=>$v){
					if($v == 'phpok_after' || $v == 'ap_'.$this->ctrl.'_'.$this->func.'_after'){
						$list['plugin'][$key][$v] = false;
					}
				}
			}
		}
		return $list;
	}

	/**
	 * 404错误，基于页面
	**/
	final public function error_404($ajax=false)
	{
		$this->plugin("error-404");
		header("HTTP/1.0 404 Not Found");
		header('Status: 404 Not Found');
		if(true === $ajax || $this->is_ajax){
			header('Content-Type:application/json; charset=utf-8');
			exit($this->lib('json')->encode(array('status'=>false,'info'=>P_Lang('404错误'))));
		}
		if($ajax && is_string($ajax)){
			$this->tpl->assign('info',$ajax);
		}
		if($this->tpl->check_exists('404')){
			$this->tpl->display('404');
			exit;
		}
		echo '<h1>404 错误</h1>';
		if($ajax && is_string($ajax)){
			echo "<p>".$ajax.'</p>';
		}else{
			echo '<p>您要访问的页面不存在</p>';
		}
		exit;
	}

	/**
	 * 友情错误提示，支持Ajax
	 * @参数 $info 错误信息
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @更新时间 2016年01月22日
	**/
	public function error($info='',$url='',$ajax=false,$errid=0)
	{
		if(!$errid){
			$tmp_array = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			foreach($tmp_array as $key=>$value){
				if($value['function'] == 'error'){
					$errid = $value['line'];
					continue;
				}
			}
		}
		if($url && is_numeric($url)){
			$errid = $url;
			$url = '';
		}
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		$this->_tip($info,0,$url,$ajax,$errid);
	}

	/**
	 * 友情成功提示，支持Ajax
	 * @参数 $info 错误信息
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @参数 $errid 错误ID号，用于区别同一个错误提示的不同ID
	 * @更新时间 2016年01月22日
	 */
	public function success($info='',$url='',$ajax=false)
	{
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		$this->_tip($info,1,$url,$ajax);
	}

	/**
	 * 提示信息
	 * @参数 $info 错误信息
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @参数 $errid 错误ID号，用于区别同一个错误提示的不同ID
	 * @更新时间 2016年01月22日
	**/
	public function tip($info='',$url='',$ajax=false,$errid=0)
	{
		if($url && is_numeric($url) && $url>100){
			$errid = $url;
			$url = '';
		}
		if(is_numeric($ajax) && $ajax>100){
			$errid = $ajax;
			$ajax = 2;
		}
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		$this->_tip($info,2,$url,$ajax,$errid);
	}

	/**
	 * 友好提示
	 * @参数 $info 错误信息
     * @参数 $status 状态，1或true为成功，0或false为失败，2为提示
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @参数 $errid 错误ID号，用于区别同一个错误提示的不同ID
	 * @更新时间 2016年01月22日
	**/
	private function _tip($info='',$status=0,$url='',$ajax=false,$errid=0)
	{
		//判断是否写入日志
		$_savelog = false;
		if($this->config['log'] && $this->config['log']['status']){
			$_savelog = true;
			if($status || (is_bool($info) && $info)){
				$_savelog = false;
			}
			$tmp = array('index','log');
			if(in_array($this->ctrl,$tmp)){
				$_savelog = false;
			}
		}
		if((true === $ajax || $this->is_ajax) && !is_numeric($ajax)){
			$data = is_array($ajax) ? $ajax : array();
			$data['info'] = $info;
			$data['status'] = $status;
			if(!$status && $errid){
				$data['error_id'] = $this->app_id.'-'.$this->ctrl.'-'.$this->func.'-'.$errid;
				$data['error_msg'] = $info;
			}
			if($url){
				$data['url'] = $url;
			}
			if($this->ctrl == 'login'){
				$session_val = $this->session()->sessid();
				$data['session_name'] = $this->session()->sid();
				$data['session_val'] = $session_val;
			}
			if($_savelog){
				$this->model('log')->save($info);
			}
			$this->_counter_node_plugin_code($info);
			header('Content-Type:application/json; charset=utf-8');
            exit($this->lib('json')->encode($data));
        }
        if($ajax && is_numeric($ajax)){
	        $this->assign('time',$ajax);
        }
        if($url){
	        if(defined('PHPOK_SITE_ID')){
		        if(strpos($url,'?') === false){
					$url .= "?siteId=".PHPOK_SITE_ID;
				}else{
					$url .= "&siteId=".PHPOK_SITE_ID;
				}
	        }
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
        $this->assign('error_id',$errid);
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
		if($_savelog){
			$this->model('log')->save($info);
		}
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

	private function _phpok_seo($rs)
	{
		return array('title'=>$rs['seo_title'],'keywords'=>$rs['seo_keywords'],'desc'=>$rs['seo_desc']);
	}

	/**
	 * 针对PHPOK前台执行SEO优化
	**/
	final public function phpok_seo()
	{
		$line = $this->config['seo']['line'];
		if(!$line){
			$line = '_';
		}
		$inherit = $this->config['seo']['inherit'];
		$in_title = $this->config['seo']['in_title'];
		$seo = array('title'=>'','keywords'=>'','desc'=>'');
		if($this->config['ctrl'] == 'index'){
			$seo = $this->_phpok_seo($this->site);
		}
		if($this->config['ctrl'] == 'project'){
			//继承模式的SEO
			$page = $this->tpl->val('page_rs');
			$cate = $this->tpl->val('cate_rs');
			if($inherit){
				$seo = $this->_phpok_seo($this->site);
				$seo['title'] = ($page && $page['seo_title']) ? $page['seo_title'] : $page['title'];
				$seo['title'] = ($cate && $cate['seo_title']) ? $cate['seo_title'] : $seo['title'];
				if($seo['title'] && $cate && !$cate['seo_title']){
					$seo['title'] = ($cate ? $cate['title'].$line : '').$page['title'].$line.$seo['title'];
				}
				$seo['keywords'] = ($page && $page['seo_keywords']) ? $page['seo_keywords'] : $seo['keywords'];
				$seo['keywords'] = ($cate && $cate['seo_keywords']) ? $cate['seo_keywords'] : $seo['keywords'];
				$seo['desc'] = ($page && $page['seo_desc']) ? $page['seo_desc'] : $seo['desc'];
				$seo['desc'] = ($cate && $cate['seo_desc']) ? $cate['seo_desc'] : $seo['desc'];
			}else{
				if($cate){
					$seo = $this->_phpok_seo($cate);
					if(!$seo['title']){
						$seo['title'] = $cate['title'].$line.$page['title'];
					}
				}else{
					$seo = $this->_phpok_seo($page);
					if(!$seo['title']){
						$seo['title'] = $page['title'];
					}
				}
			}
		}
		if($this->config['ctrl'] == 'content'){
			$seo = $this->_phpok_seo($this->site);
			$page = $this->tpl->val('page_rs');
			$cate = $this->tpl->val('cate_rs');
			$rs = $this->tpl->val('rs');
			if($inherit){
				$seo['title'] = ($page && $page['seo_title']) ? $page['seo_title'] : $seo['title'];
				$seo['title'] = ($cate && $cate['seo_title']) ? $cate['seo_title'] : $seo['title'];
				$seo['title'] = ($rs && $rs['seo_title']) ? $rs['seo_title'] : $seo['title'];
				if($seo['title'] && !$rs['seo_title'] && $in_title){
					$seo['title'] = $rs['title'].$line.($cate ? $cate['title'].$line : '').$page['title'].$line.$seo['title'];
				}
				if($seo['title'] && !$rs['seo_title'] && !$in_title){
					$seo['title'] = $rs['title'].$line.($cate ? $cate['title'].$line : '').$page['title'];
				}
				$seo['keywords'] = ($page && $page['seo_keywords']) ? $page['seo_keywords'] : $seo['keywords'];
				$seo['keywords'] = ($cate && $cate['seo_keywords']) ? $cate['seo_keywords'] : $seo['keywords'];
				$seo['keywords'] = ($rs && $rs['seo_keywords']) ? $rs['seo_keywords'] : $seo['keywords'];
				$seo['desc'] = ($page && $page['seo_desc']) ? $page['seo_desc'] : $seo['desc'];
				$seo['desc'] = ($cate && $cate['seo_desc']) ? $cate['seo_desc'] : $seo['desc'];
				$seo['desc'] = ($rs && $rs['seo_desc']) ? $rs['seo_desc'] : $seo['desc'];
			}else{
				$seo = $this->_phpok_seo($rs);
				if(!$seo['title']){
					$seo['title'] = $rs['title'].$line.($cate ? $cate['title'].$line : '').$page['title'];
				}
			}
		}
		if($this->config['ctrl'] == 'tag'){
			$rs = $this->tpl->val('rs');
			if($inherit){
				$seo = $this->_phpok_seo($this->site);
				$seo['title'] = ($rs && $rs['seo_title']) ? $rs['seo_title'] : $seo['title'];
				$seo['keywords'] = ($rs && $rs['seo_keywords']) ? $rs['seo_keywords'] : $seo['keywords'];
				$seo['desc'] = ($rs && $rs['seo_desc']) ? $rs['seo_desc'] : $seo['desc'];
			}else{
				$seo = $this->_phpok_seo($rs);
				if(!$seo['title']){
					$seo['title'] = $rs['title'];
				}
			}
		}
		$seo['description'] = $seo['desc'];
		$this->assign("seo",$seo);
		return $seo;
	}

	/**
	 * 增加js库，在HTML模板里可以直接使用 phpok_head_js，将生成符合标准的js文件链接
	**/
	public function addjs($url='')
	{
		$this->jslist[] = $url;
	}

	/**
	 * 增加css文件链接，在HTML里可以直接使用 phpok_head_css，将生成符合标准的CSS文件链接
	**/
	public function addcss($url='')
	{
		$this->csslist[] = $url;
	}

	/**
	 * 第三方网关执行
	 * @参数 $action 要执行的网关，param表示读取网关信息，extinfo表示变更网关扩展信息extinfo，exec表示网关路由文件的执行
	 * @参数 $param action为param时表示网关ID，default表示读默认网关，action为extinfo时，param表示内容，
	 *              action为exec时表示输出方式，为空返回，支持json，action为check时表示检测网关是否存在
	**/
	final public function gateway($action,$param='')
	{
		if($action == 'type'){
			$this->gateway['type'] = $param;
			return true;
		}
		if($action == 'param'){
			if($param == 'default'){
				$info = $this->model('gateway')->get_default($this->gateway['type']);
			}elseif(is_numeric($param)){
				$info = $this->model('gateway')->get_one($param);
			}else{
				$info = $param;
			}
			if($info){
				$this->gateway['param'] = $info;
			}
			return true;
		}
		if($action == 'extinfo'){
			$this->gateway['extinfo'] = $param;
		}
		if($action == 'exec' || substr($action,-4) == '.php'){
			if(!$this->gateway['param']){
				return false;
			}
			$file = $action == 'exec' ? 'exec.php' : $action;
			$rs = $this->gateway['param'];
			if($action == 'exec' && $param){
				$extinfo = $param;
			}else{
				$extinfo = $this->gateway['extinfo'];
			}
			$exec_file = $this->dir_gateway.''.$this->gateway['param']['type'].'/'.$this->gateway['param']['code'].'/'.$file;
			$info = '';
			if(file_exists($exec_file)){
				$info = include($exec_file);
			}
			if($param == 'json'){
				if(!$info){
					$this->error();
				}
				exit($this->lib('json')->encode($info));
			}else{
				return $info;
			}
		}
		if($action == 'check'){
			return $this->gateway['param'] ? true : false;
		}
		if(!$this->gateway['param']){
			return false;
		}
		return true;
	}

}

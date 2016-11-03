<?php
/**
 * PHPOK框架入口引挈文件，请不要改动此文件
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月21日
**/


/**
 * 安全限制
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

/**
 * 强制使用UTF-8编码
**/
header("Content-type: text/html; charset=utf-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT");
header("Cache-control: no-cache,no-store,must-revalidate,max-age=1");
header("Pramga: no-cache"); 

/**
 * 计算执行的时间
 * @参数 $is_end 布尔值
 * @返回 参数为true时返回执行的时间，为false定义常量 SYS_TIME_START 为当前时间
**/
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

/**
 * 登记内存
 * @参数 $is_end 布尔值
 * @返回 参数为true时返回使用的内存值，为false定义常量 SYS_MEMORY_START 为当前内存值
**/
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

/**
 * 用于调试统计时间，无参数，启用数据库调试的结果会在这里输出，需要在模板适当位置写上：{func debug_time} 
**/
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

/**
 * PHPOK4最新框架，一般不直接调用此框架
 * @更新时间 2016年06月05日
**/
class _init_phpok
{
	/**
	 * 指定app_id，该id是通过入口的**APP_ID**来获取，留空使用www
	**/
	public $app_id = "www";

	/**
	 * 定义网站程序根目录，对应入口的**ROOT**，为空使用./
	**/
	public $dir_root = "./";

	/**
	 * 框架目录，对应入口的**FRAMEWORK**，为空使用phpok/
	**/
	public $dir_phpok = "phpok/";

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
	public $license = "LGPL";

	/**
	 * 授权码，16位或32位的授权码，要求全部大写，对应license.php里的常量LICENSE_CODE
	**/
	public $license_code = "ED988858BCB1903A529C762DBA51DD40";

	/**
	 * 授权时间，对应license.php里的常量LICENSE_DATE
	**/
	public $license_date = "2012-10-29";

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
	private $language_status = 'gettext';

	/**
	 * 网关路由接口，对应文件夹gateway里的PHP执行
	**/
	public $gateway;

	/**
	 * 用于api.php接口接入传递token参数，此项功能还不成熟，请慎用
	**/
	public $token;

	/**
	 * 数据传输是否使用Ajax
	**/
	public $is_ajax = false;

	private $_libs = array();

	/**
	 * 构造函数，用于初化一些数据
	**/
	public function __construct()
	{
		if(version_compare(PHP_VERSION, '5.3.0', '<') && function_exists('set_magic_quotes_runtime')){
			ini_set("magic_quotes_runtime",0);
		}
		$this->init_constant();
		$this->init_config();
		$this->init_engine();
	}

	/**
	 * 析构函数
	**/
	public function __destruct()
	{
		unset($this);
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

	/**
	 * 加载语言包
	 * @参数 $langid 字符串，留空加载default，中文不需要加载语言包
	 * @更新时间 2016年06月05日
	**/
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

	/**
	 * 语言包变量格式化，$info将转化成系统的语言包，同是将$info里的带{变量}替换成$var里传过来的信息
	 * @参数 $info 字符串，要替变的字符串用**{}**包围，包围的内容对应$var里的$key
	 * @参数 $var 数组，要替换的字符。
	 * @返回 字符串，$info为空返回false
	 * @更新时间 2016年06月05日
	**/
	final public function lang_format($info,$var='')
	{
		if(!$info){
			return false;
		}
		if($this->language_status == 'user' && $this->lang){
			$info = $this->lang->translate($info);
		}else{
			if($this->language_status == 'gettext'){
				$info = gettext($info);
			}
		}
		if($var && is_string($var)){
			$var  = unserialize($var);
		}
		if($var && is_array($var)){
			foreach($var as $key=>$value){
				$info = str_replace(array('{'.$key.'}','['.$key.']'),$value,$info);
			}
		}
		return $info;
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
			if($this->app_id == 'www'){
				if(!$this->site["tpl_id"] || ($this->site["tpl_id"] && !is_array($this->site["tpl_id"]))){
					$this->_error("未指定模板文件");
				}
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
	 * 初始化加载站点信息，后台仅加载站点信息，返回true，前端会执行域名判断，手机判断，及模板加载
	**/
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
		$site_id = $this->get("siteId","int");
		$domain = $this->lib('server')->domain($this->config['get_domain_method']);
		if(!$domain){
			$this->_error('无法获取网站域名信息，请检查环境是否支持$_SERVER["SERVER_NAME"]或$_SERVER["HTTP_HOST"]');
		}
		if(!$site_id){
			$site_id = $domain;
			if(!$site_id){
				$this->_error('站点信息获取失败');
			}
		}
		$site_rs = $this->model('site')->site_info($site_id);
		if(!$site_rs && $this->app_id == 'www'){
			$this->_error('网站信息不存在或未启用');
		}
		$url_type = $this->is_https() ? 'https://' : 'http://';
		if($this->app_id == 'www'){
			if($this->config['mobile']['status']){
				$this->is_mobile = $this->config['mobile']['default'];
				if(!$this->is_mobile && $this->config['mobile']['autocheck']){
					$this->is_mobile = $this->is_mobile();
				}
			}
			if(is_numeric($site_id) && $site_rs['domain'] && $site_rs['domain'] != $domain){
				$url = $url_type.$site_rs['domain'].$site_rs['dir'];
				if(substr($url,-1) != '/'){
					$url .= '/';
				}
				$url .= $this->config['www_file'];
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
						if(substr($url,-1) != '/'){
							$url .= '/';
						}
						$url .= $this->config['www_file'];
						$this->_location($url);
						exit;
					}
				}
			}
		}
		if($site_rs && $site_rs["tpl_id"]){
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

	/**
	 * 判断是否启用https
	**/
	protected function is_https()
	{
		return $this->lib('server')->https();
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

	/**
	 * 动态引态第三方类包，官方提供的类包在framework/libs/下，用户自行编写的class放在extension目录下。
	 * 请注意，extension支持下的类支持config.inc.php配置自动执行
	 * config.inc.php支持的参数有：
	 * 		1. auto，自动运行的方法
	 *		2. include，包含这个类下需要调用的其他php文件，多个文件用英文逗号隔开，仅支持相对路径
	 * @参数 $class，类的名称，第三方对应的是文件夹名称，要求全部小写
	**/
	public function lib($class='')
	{
		if(!$class){
			return false;
		}
		if($this->_libs && $this->_libs[$class]){
			$config = $this->_libs[$class];
		}else{
			$config = array('param'=>'','include'=>'','auto'=>'','classname'=>$class.'_lib');
			if(file_exists($this->dir_root.'extension/'.$class.'/config.inc.php')){
				include($this->dir_root.'extension/'.$class.'/config.inc.php');
				$list = $config['include'] ? explode(",",$config['include']) : array();
				foreach($list as $key=>$value){
					if(substr(strtolower($value),-4) != '.php'){
						$value .= '.php';
					}
					if(file_exists($this->dir_root.'extension/'.$class.'/'.$value)){
						include_once($this->dir_root.'extension/'.$class.'/'.$value);
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
				$this->$tmp->$value();
			}
		}
		return $this->$tmp;
	}

	/**
	 * 按需加载Model信息，所有的文件均放在framework/model/目录下。会根据**app_id**自动加载同名但不同入口的文件
	 * @参数 $name，字符串
	 * @返回 实例化后的类，出错则中止运行报错
	 * @更新时间 2016年06月05日
	**/
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
		foreach($this->plugin AS $key=>$value){
			if(in_array($ap,$value['method'])){
				$value['obj']->$ap($param);
			}
		}
		return true;
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

	/**
	 * 装载资源引挈，默认引挈加载将在config里配置
	**/
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
	 * @更新时间 2016年02月05日
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
		if(file_exists($this->dir_root.'config.'.$this->app_id.'.php')){
			include($this->dir_root.'config.'.$this->app_id.'.php');
		}
		if($config['debug']){
			if(function_exists('opcache_reset')){
				ini_set('opcache.enable',false);
			}
			ini_set('display_errors','on');
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
		if($baseurl){
			$this->model('url')->base_url($this->url);
		}
		return $this->model('url')->url($ctrl,$func,$ext);
	}

	/**
	 * 自动生成网址，系统自带
	**/
	final public function root_url()
	{
		$http_type = $this->lib('server')->https() ? 'https://' : 'http://';
		$port = $this->lib('server')->port();
		//$myurl = $_SERVER[$this->config['get_domain_method']];
		$myurl = $this->lib('server')->domain($this->config['get_domain_method']);
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
		if(file_exists($this->dir_root."version.php")){
			include($this->dir_root."version.php");
			$this->version = defined("VERSION") ? VERSION : "4.5.0";
		}
		if(file_exists($this->dir_root."license.php")){
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
		$this->is_ajax = $this->lib('server')->ajax();
	}

	/**
	 * 通过post或get取得数据，自动判断是否转义，未转义将自动转义，转义后执行格式化操作
	 * @参数 $id 字符串，要取得的数据ID，对应网页中的input里的name信息
	 * @参数 $type 字符串，格式化方式，默认是safe，支持：safe，html，html_js，float，int，checkbox，time，text，system等多种格式化方式
	 * @参数 $ext 数值或布尔值，为1或true时，在type为html时，等同于html_js，当type为func时，则ext为直接运行的函数
	 * @返回 格式化后的数据
	**/
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

	/**
	 * 格式化内容
	 * @参数 $msg，要格式化的内容，该内容已经转义了
	 * @参数 $type，类型，支持：safe，text，html，html_js，func，int，float，system
	 * @参数 $ext，扩展，当type为html时，ext存在表示支持js，不存在表示不支持js，当type为func属性时，表示ext直接执行函数
	**/
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
		switch ($type){
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
			foreach($val AS $key=>$value){
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
	 * 输出HTML信息
	 * @参数 $file 字符串，指定的模板文件，支持不带后缀的模板名称，也支持完整的模板名称，也支持HTML内容，具体受参数$type影响
	 * @参数 $type 字符串，支持 file：不带后缀的模板名，file-ext：带后缀的模板名，
	 *                         content：直接是内容，msg：等同于content，abs-file：完整路径的模板文件
	 * @参数 $path_format 布尔值 是否格式化路径信息，慎用，模板里有大量嵌套，可能会混乱（未深度测试）
	 * @返回 无，直接输出HTML信息到设备上
	**/
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
		$this->plugin('phpok-after');
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
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
	**/
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

	/**
	 * 接口入口处理
	**/
	private function action_api()
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

	/**
	 * 前台入口处理
	**/
	private function action_www()
	{
		//判断是否有PATH_INFO;
		if($this->site['url_type'] == 'rewrite' && $this->lib('server')->uri()){
			$uri = $this->lib('server')->uri();
			$docu = $this->lib('server')->me();
			if($this->lib('server')->path_info()){
				$docu = substr($docu,0,-(strlen($this->lib('server')->path_info())));
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
			$script_name = $this->lib('server')->phpfile();
			if('/'.$script_name == substr($uri,0,(strlen($script_name)+1))){
				$uri = substr($uri,(strlen($script_name)+1));
			}
			$query_string = $this->lib('server')->query();
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
		$this->_action($ctrl,$func);
	}

	/**
	 * 后台入口处理
	**/
	private function action_admin()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl) $ctrl = "index";
		if(!$func) $func = "index";
		if($ctrl != 'login' && !$this->config['develop']){
			if(!$this->lib('server')->referer()){
				$ctrl='login';
				$func = 'index';
				$this->session->destroy();
				$this->_location($this->url('login'));
			}else{
				$info = parse_url($this->lib('server')->referer());
				$chk = parse_url($this->url);
				if($info['host'] != $chk['host']){
					$ctrl = 'login';
					$func = 'index';
					$this->session->destroy();
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

	/**
	 * JSON数据输出，要注意的是在输出时会触发插件，故该方法在插件使用要小心，防止出现死循环
	 * @参数 $content 要输出的内容，支持字符串，数组及布尔值，为布尔值是true直接输出 status=>ok，为false时输出 status=>error
	 * @参数 $status 布尔值，为true时输出status=>ok，false输出status=>error，并附带相应的内容content=>$content
	 * @参数 $exit 布尔值，为false时，不中止运行，会继续执行下面的PHP文件，一般不需要用到
	 * @返回 格式化后json数据
	 * @更新时间 2016年06月05日
	**/
	final public function json($content,$status=false,$exit=true)
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
			$this->plugin('phpok-after');
			$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
			exit($callback.'('.$this->lib('json')->encode($rs).')');
		}
		if($status){
			$rs = array('info'=>$content);
			if(is_bool($status)){
				$rs['status'] = 1;
				if($url){
					$rs['url'] = $url;
				}
				$this->plugin('phpok-after');
				$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
			}else{
				$rs = array('info'=>$content,'url'=>$status);
				if($url && is_bool($url)){
					$rs['status'] = 1;
					$this->plugin('phpok-after');
					$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
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

	/**
	 * 友情错误提示，支持Ajax
	 * @参数 $info 错误信息
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @更新时间 2016年01月22日
	**/
	public function error($info='',$url='',$ajax=false)
	{
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		$this->_tip($info,0,$url,$ajax);
	}

	/**
	 * 友情成功提示，支持Ajax
	 * @参数 $info 错误信息
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
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
	 * @更新时间 2016年01月22日
	**/
	public function tip($info,$url,$ajax=false)
	{
		if($url && $ajax === false && !$this->is_ajax){
			$ajax = 2;
		}
		$this->_tip($info,2,$url,$ajax);
	}

	/**
	 * 友好提示
	 * @参数 $info 错误信息
     * @参数 $status 状态，1或true为成功，0或false为失败，2为提示
	 * @参数 $url 跳转网址
	 * @参数 $ajax 是否为Ajax方式 当数字时指定跳转时间
	 * @更新时间 2016年01月22日
	**/
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

	/**
	 * 针对PHPOK4前台执行SEO优化
	 * @参数 $rs 数组，要替换的数据，需要包含：keywords或kw或keyword表示SEO里的关键字，
	 *                                       description或desc表示优化描述，title表示优化标题
	**/
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

	/**
	 * 将非字母数字的字符转化为ASCII字符，以实现任意编码正常显示
	 * @参数 $str 字符串，要转化的字串
	 * @返回 转化后的字符串
	**/
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
	 *              action为exec时表示输出方式，为空返回，支持json
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

/**
 * 核心魔术方法，此项可实现类，方法的自动加载，PHPOK里的Control，Model及Plugin都继承了这个类
**/
class _init_auto
{
	public function __construct()
	{
		//
	}

	public function __destruct()
	{
		unset($this);
	}

	/**
	 * 魔术方法之方法重载
	 * @参数 $method $GLOBALS['app']下的方法，如果存在，直接调用，不存在，通过分析动态加载lib或是model
	 * @参数 $param 传递过来的变量
	**/
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

	/**
	 * 属性重载，读取不可访问属性的值时，尝试通过这里重载
	 * @参数 $id $GLOBALS['app']下的属性
	**/
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

	/**
	 * 属性重载，当对不可访问属性调用
	 * @参数 $id $GLOBALS['app']下的属性
	**/
	public function __isset($id)
	{
		return $this->__get($id);
	}
}

/**
 * 初始化第三方类，如果第三方类继承该类，则可以直接使用一些变量，而无需再定位及初化，
 * 继承该类后可以直接使用下类属性：<br />
 *     1. $this->dir_root，程序根目录<br />
 *     2. $this->dir_phpok，程序框架目录<br />
 *     3. $this->dir_data，程序数据保存目录<br />
 *     4. $this->dir_cache，缓存目录<br />
 *     5. $this->dir_extension，第三方扩展类根目录
**/
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

/**
 * PHPOK控制器，里面大部分函数将通过Global功能调用核心引挈
**/
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

/**
 * Model根类，继承了_into_auto类，支持直接调用核心引挈里的信息
**/
class phpok_model extends _init_auto
{
	/**
	 * 站点ID，所有的Model类都可以直接用这个
	**/
	public $site_id = 0;

	/**
	 * 动态加载Model
	 * @参数 $id 为空用于继承父构造函数，不为空时动态加载其他model类，即实现了多个model的互相调用
	**/
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

	/**
	 * 定义站点ID，用于实现同一个程序里有多个站点
	 * @参数 $site_id，站点ID
	**/
	public function site_id($site_id=0)
	{
		$this->site_id = $site_id;
	}

	/**
	 * 动态获取下一个排序
	 * @参数 $rs 数组或数字，为数字时返回该值+10后的数字，为数组时，尝试获取taxis或sort对应的数值，并返回+10后的数字，为空时返回10
	 * @返回 数字，下一个排序
	**/
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

/**
 * 初始化插件类，即在插件中，也可以使用$this->model或是$this->lib等方法来获取相应的核心信息
**/
class phpok_plugin extends _init_auto
{
	public function plugin()
	{
		parent::__construct();
	}

	/**
	 * 返回插件的ID
	**/
	final public function _id()
	{
		$name = get_class($this);
		$lst = explode("_",$name);
		unset($lst[0]);
		return implode("_",$lst);
	}

	/**
	 * 返回插件信息
	 * @参数 $id 插件ID，为空时尝试读取当前插件ID
	 * @返回 数组 id插件ID，title名称，author作者，version版本，note说明，param插件扩展保存的数据，这个是一个数组，path插件路径
	 * @更新时间 
	**/
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

	/**
	 * 保存插件扩展数据，注意，这里仅保存插件的扩展数据
	 * @参数 $ext 数组，要保存的数组
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	**/
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

	/**
	 * 返回插件输出的HTML数据，请注意，这里并没有输出，只是返回
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时返回模板内容，错误时返回false 
	**/
	final public function _tpl($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if(!$file){
			return false;
		}
		return $this->fetch($file,'abs-file');
	}

	/**
	 * 输出的HTML数据到设备上，请注意，这里是输出，不是返回，同时也要注意，这里没有中止
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	final public function _show($name,$id='')
	{
		$info = $this->_tpl($name,$id);
		if($info){
			echo $info;
		}
	}

	/**
	 * 输出的HTML数据到设备上并中断后续操作，请注意，这里是输出，有中断
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查，具体请看：<b>private function _tplfile()</b>
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
	final public function _view($name,$id='')
	{
		$file = $this->_tplfile($name,$id);
		if($file){
			$this->view($file,'abs-file');
		}
	}

	/**
	 * 按顺序读取挑出最近的一个模板
	 * @参数 $name 模板名称，带后缀的模板名称，相对路径，系统会依次检查这些文件是否存在，只要有一个符合要求即可<br />
	 * 1. 当前模板目录/plugins/插件ID/template/$name<br />
	 * 2. 当前模板目录/plugins/插件ID/$name<br />
	 * 3. 当前模板目录/插件ID/$name<br />
	 * 4. 当前模板目录/plugins_插件ID_$name<br />
	 * 5. 当前模板目录/插件ID_$name<br />
	 * 6. 程序根目录/plugins/插件ID/template/$name<br />
	 * 7. 程序根目录/plugins/插件ID/$name
	 * @参数 $id 字符串，指定的插件ID，为空尝试获取当前插件ID
	 * @返回 正确时输出HTML，错误时跳过没有任何输出
	**/
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

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_id()
	**/
	protected function plugin_id()
	{
		return $this->_id();
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_info()
	**/
	protected function plugin_info($id='')
	{
		return $this->_info();
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_save()
	**/
	protected function plugin_save($ext,$id="")
	{
		return $this->_save($ext,$id);
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_tpl()
	**/
	protected function plugin_tpl($name,$id='')
	{
		return $this->_tpl($name,$id);
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_show()
	**/
	protected function show_tpl($name,$id='')
	{
		$this->_show($name,$id);
	}

	/**
	 * 旧版本写法，与之对应新的写法是：$this->_view()
	**/
	protected function echo_tpl($name,$id='')
	{
		$this->_view($name,$id);
	}
}

/**
 * 安全注销全局变量
**/
unset($_ENV, $_SERVER['MIBDIRS'],$_SERVER['MYSQL_HOME'],$_SERVER['OPENSSL_CONF'],$_SERVER['PHP_PEAR_SYSCONF_DIR'],$_SERVER['PHPRC'],$_SERVER['SystemRoot'],$_SERVER['COMSPEC'],$_SERVER['PATHEXT'], $_SERVER['WINDIR'],$_SERVER['PATH']);

$app = new _init_phpok();
include_once($app->dir_phpok."phpok_helper.php");
$app->init_site();
$app->init_view();

/**
 * 引用全局 app
**/
function init_app(){
	return $GLOBALS['app'];
}

/**
 * 核心函数，phpok_head_js，用于加载自定义扩展中涉及到的js
**/
function phpok_head_js()
{
	$debug = $GLOBALS['app']->config['debug'];
	$jslist = $GLOBALS['app']->jslist;
	if(!$jslist || !is_array($jslist)){
		return false;
	}
	$jslist = array_unique($jslist);
	$html = "";
	foreach($jslist AS $key=>$value){
		if($debug){
			$value .= strpos($value,'?') !== false ? '&_noCache='.time() : '?_noCache='.time();
		}
		$html .= '<script type="text/javascript" src="'.$value.'" charset="utf-8"></script>'."\n";
	}
	return $html;
}

/**
 * 核心函数，phpok_head_css，用于加载自定义扩展中涉及到的css
**/
function phpok_head_css()
{
	$debug = $GLOBALS['app']->config['debug'];
	$csslist = $GLOBALS['app']->csslist;
	if(!$csslist || !is_array($csslist)){
		return false;
	}
	$csslist = array_unique($csslist);
	$html = "";
	foreach($csslist AS $key=>$value){
		if($debug){
			$value .= strpos($value,'?') !== false ? '&_noCache='.time() : '?_noCache='.time();
		}
		$html .= '<link rel="stylesheet" type="text/css" href="'.$value.'" charset="utf-8" />'."\n";
	}
	return $html;
}

/**
 * 语言包变量格式化，$info将转化成系统的语言包，同是将$info里的带{变量}替换成$var里传过来的信息
 * @参数 $info 字符串，要替变的字符串用**{}**包围，包围的内容对应$var里的$key
 * @参数 $replace 数组，要替换的字符。
 * @返回 字符串，$info为空返回false
 * @更新时间 2016年06月05日
**/
function P_Lang($info,$replace='')
{
	return $GLOBALS['app']->lang_format($info,$replace);
}

/**
 * 核心函数，动态加CSS
**/
function phpok_add_css($file='')
{
	$GLOBALS['app']->addcss($file);
}

/**
 * 核心函数，动态加js
**/
function phpok_add_js($file='')
{
	$GLOBALS['app']->addjs($file);
}

/**
 * 执行动作
**/
$app->action();
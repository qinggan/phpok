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
header("Cache-control: no-cache,no-store,must-revalidate,max-age=3"); 
header("Pramga: no-cache"); 
//将数组存为XML
function array_to_xml(&$xml,$rs,$ext="")
{
	if(is_array($rs))
	{
		foreach($rs AS $key=>$value)
		{
			if(is_array($value))
			{
				$ext .= "	";
				$xml .= "<".$key.">\n";
				array_to_xml($xml,$value,$ext);
				$xml .= "</".$key.">\n";
			}
			else
			{
				$xml .= $ext."<".$key.">".$value."</".$key.">\n";
			}
		}
	}
}

//将XML转成数组
//xml纯内容
function xml_to_array($xml)
{
	$reg = "/<([a-zA-Z0-9\_\-]+)([^>]*?)>([\\x00-\\xFF]*?)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches))
	{
		$count = count($matches[0]);
		$arr = array();
		for($i = 0; $i < $count; $i++)
		{
			$key = $matches[1][$i];
			$ext = $matches[2][$i];
			$val = xml_to_array( $matches[3][$i] );  // 递归
			if(array_key_exists($key, $arr))
			{
				if(is_array($arr[$key]))
				{
					if(!array_key_exists(0,$arr[$key]))
					{
						$arr[$key] = array($arr[$key]);
					}
				}else{
					$arr[$key] = array($arr[$key]);
				}
				if(!$val && $ext && trim($ext))
				{
					$ext = trim($ext);
					preg_match_all("/([0-9a-zA-Z\_]+)\=[\"|'](.+)[\"|']/isU",$ext,$extlist);
					if($extlist[1])
					{
						$tmplist = array();
						foreach($extlist[1] AS $kk=>$vv)
						{
							$tmplist[$vv] = $extlist[2][$kk];
						}
						$arr[$key][] = $tmplist;
					}
					else
					{
						$arr[$key][] = $val;
					}
				}
				else
				{
					if($ext && trim($ext))
					{
						$ext = trim($ext);
						preg_match_all("/([0-9a-zA-Z\_]+)\=[\"|'](.+)[\"|']/isU",$ext,$extlist);
						if($extlist[1])
						{
							$tmplist = array();
							foreach($extlist[1] AS $kk=>$vv)
							{
								$tmplist[$vv] = $extlist[2][$kk];
							}
							$arr[$key][] = array("id"=>$tmplist,"val"=>$val);
						}
						else
						{
							$arr[$key][] = $val;
						}
					}
					else
					{
						$arr[$key][] = $val;
					}
				}
			}else{
				if($val && !is_array($val))
				{
					$val = preg_replace('/\{html:(.+)\}/isU','<\\1>',$val);
					$val = preg_replace('/\{\/(.+):html\}/isU','</\\1>',$val);
				}
				if(!$val && $ext && trim($ext))
				{
					$ext = trim($ext);
					preg_match_all("/([0-9a-zA-Z\_]+)\=[\"|'](.+)[\"|']/isU",$ext,$extlist);
					if($extlist[1])
					{
						$tmplist = array();
						foreach($extlist[1] AS $kk=>$vv)
						{
							$tmplist[$vv] = $extlist[2][$kk];
						}
						$arr[$key] = $tmplist;
					}
					else
					{
						$arry[$key] = $val;
					}
				}
				else
				{
					if($ext && trim($ext))
					{
						$ext = trim($ext);
						preg_match_all("/([0-9a-zA-Z\_]+)\=[\"|'](.+)[\"|']/isU",$ext,$extlist);
						if($extlist[1])
						{
							$tmplist = array();
							foreach($extlist[1] AS $kk=>$vv)
							{
								$tmplist[$vv] = $extlist[2][$kk];
							}
							$arr[$key]["id"] = $tmplist;
							$arr[$key]["val"] = $val;
						}
						else
						{
							$arr[$key] = $val;
						}
					}
					else
					{
						$arr[$key] = $val;
					}
				}
			}
		}
		return $arr;
	}else{
		return $xml;
	}
}


//计算执行的时间
function run_time($is_end=false)
{
	$time = explode(" ",microtime());
	if(!$is_end)
	{
		if(defined("SYS_TIME_START"))
		{
			return false;
		}
		define("SYS_TIME_START",($time[0] + $time[1]));
	}
	else
	{
		if(!defined("SYS_TIME_START"))
		{
			return false;
		}
		$time = $time[0] + $time[1] - SYS_TIME_START;
		return round($time,5);
	}
}

//登记内存
function run_memory($is_end=false)
{
	if(!$is_end)
	{
		if(defined("SYS_MEMORY_START") || !function_exists("memory_get_usage"))
		{
			return false;
		}
		define("SYS_MEMORY_START",memory_get_usage());
	}
	else
	{
		if(!defined("SYS_MEMORY_START"))
		{
			return false;
		}
		$memory = memory_get_usage() - SYS_MEMORY_START;
		//格式化大小
		if($memory <= 1024)
		{
			$memory = "1 KB";
		}
		elseif($memory>1024 && $memory<(1024*1024))
		{
			$memory = round(($memory/1024),2)." KB";
		}
		else
		{
			$memory = round(($memory/(1024*1024)),2)." MB";
		}
		return $memory;
	}
}

run_time();
run_memory();

function debug_time($memory_ctrl=1,$sql_ctrl=1,$file_ctrl=0,$cache_ctrl=0)
{
	//global $app;
	//$app->lib("file");
	$count = $GLOBALS['app']->lib('file')->read_count;
	$time = run_time(true);
	$memory = run_memory(true);
	$sql_db_count = $GLOBALS['app']->db->conn_count();
	$sql_db_time = $GLOBALS['app']->db->conn_times();
	$sql_cache_count = $GLOBALS['app']->db->cache_count();
	$sql_cache_time = $GLOBALS['app']->db->cache_time();
	$string  = "运行 ".$time." 秒";
	if($memory_ctrl && $memory_ctrl != 'false')
	{
		$string .= "，内存使用 ".$memory;
	}
	if($sql_ctrl && $sql_ctrl != 'false')
	{
		$string .= "，数据库执行 ".$sql_db_count." 次，耗时 ".$sql_db_time." 秒";
	}
	if($file_ctrl && $count>0 && $file_ctrl != 'false')
	{
		$string .= "，文件执行 ".$count." 次";
	}
	if($cache_ctrl && $cache_ctrl != 'false')
	{
		$string .= "，缓存执行 ".$sql_cache_count." 次，耗时 ".$sql_cache_time." 秒";
	}
	return $string;
}

//PHPOK4最新框架，其他应用可直接通过该框架调用
class _init_phpok
{
	//应用ID
	var $app_id = "www";
	//网站根目录
	var $dir_root = "./";
	//框架目录
	var $dir_phpok = "phpok/";
	//引挈库
	var $engine;
	//应用
	var $obj;
	var $obj_list;
	//配置信息
	var $config;
	//版本
	var $version = "4.0";
	//当前时间
	var $time;
	//网址
	var $url;
	//缓存信息（任意接口都可以通过获取该缓存信息）
	var $cache_data;
	//授权相关信息
	var $license = "LGPL";
	var $license_code = "ED988858BCB1903A529C762DBA51DD40";
	var $license_date = "2012-10-29";
	var $license_name = "phpok";
	var $license_site = "phpok.com";
	var $license_powered = true;

	//是否是手机端，如果使用手机端可能会改写网址
	var $is_mobile = false;

	//定义插件
	var $plugin = '';

	var $this_method_list;

	//定义css列表和js列表
	var $csslist;
	var $jslist;

	function __construct()
	{
		$this->this_method_list = get_class_methods($this);
		ini_set("magic_quotes_runtime",0);
		$this->init_constant();
		$this->init_config();
		if($this->app_id == 'www' && $this->config['mobile']['status'])
		{
			$this->is_mobile = $this->config['mobile']['default'];
			if(!$this->is_mobile && $this->config['mobile']['autocheck'])
			{
				$this->is_mobile = $this->is_mobile();
			}
		}
		$this->init_engine();
	}

	function init_assign()
	{
		//取得当前页网址
		$url = $this->url;
		$afile = $this->config[$this->app_id.'_file'];
		if(!$afile) $afile = 'index.php';
		$url .= $afile;
		if($_SERVER['QUERY_STRING']) $url .= "?".$_SERVER['QUERY_STRING'];
		$this->site["url"] = $url;
		$this->config["url"] = $this->url;
		$this->config['app_id'] = $this->app_id;
		$this->config['time'] = $this->time;
		//核心变量赋值
		$this->assign("sys",$this->config);
		//针对网站进行SEO优化
		$this->phpok_seo($this->site);
		
		$this->assign("config",$this->site);
		//加载语言包，如果有使用的话
		if($this->site['lang'] && is_file($this->dir_root."data/xml/langs/".$this->site['lang'].".xml"))
		{
			$this->lang = xml_to_array(file_get_contents($this->dir_root."data/xml/langs/".$this->site['lang'].".xml"));
			foreach($this->lang AS $key=>$value)
			{
				$this->lang[$key] = phpok_ubb($value);
			}
			$this->assign("lang",$this->lang[$this->app_id]);
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

	//UBB代码格式化
	public function ubb($Text,$nl2br=true) 
	{
		return phpok_ubb($Text,$nl2br);
	}

	function init_autoload()
	{
		if($this->config["autoload_model"])
		{
			$list = explode(",",$this->config["autoload_model"]);
			foreach($list AS $key=>$value)
			{
				$this->load($value,"model");
			}
		}
		if($this->config["autoload_lib"])
		{
			$list = explode(",",$this->config["autoload_lib"]);
			foreach($list AS $key=>$value)
			{
				$this->load($value,"lib");
			}
		}
	}

	# 加载视图引挈
	function init_view()
	{
		$file = $this->dir_phpok."phpok_tpl.php";
		if(!is_file($file))
		{
			$this->error("视图引挈文件：".basename($file)." 不存在！");
		}
		include_once($file);
		if($this->app_id == "admin")
		{
			$tpl_rs = array();
			$tpl_rs["id"] = "1";
			$tpl_rs["dir_tpl"] = substr($this->dir_phpok,strlen($this->dir_root))."/view/";
			$tpl_rs["dir_cache"] = $this->dir_root."data/tpl_admin/";
			$tpl_rs["dir_php"] = $this->dir_root;
			$tpl_rs["dir_root"] = $this->dir_root;
			$tpl_rs["refresh_auto"] = true;
			$tpl_rs["tpl_ext"] = "html";
			$this->tpl = new phpok_tpl($tpl_rs);
		}
		else
		{
			if(!$this->site["tpl_id"] || ($this->site["tpl_id"] && !is_array($this->site["tpl_id"])))
			{
				$this->error("未指定模板文件");
			}
			$this->tpl = new phpok_tpl($this->site["tpl_id"]);
			include_once($this->dir_phpok."phpok_call.php");
			$this->call = new phpok_call();
		}
		include_once($this->dir_phpok."phpok_tpl_helper.php");
	}

	//手机判断
	function is_mobile()
	{
		if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
		{
			return true;
		}
		if(isset($_SERVER['HTTP_PROFILE']))
		{
			return true;
		}
		$regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
		$regex_match.= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
		$regex_match.= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|";
		$regex_match.= "sagem|wellcom|bunjalloo|maui|symbian|smartphone|midp|wap|phone|windows ce|";
		$regex_match.= "iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
		$regex_match.= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
		$regex_match.= ")/i";
		if(preg_match($regex_match,strtolower($_SERVER['HTTP_USER_AGENT'])))
		{
			return true;
		}
		return false;
	}

	function init_site()
	{
		if($this->app_id == "admin")
		{
			if($_SESSION['admin_site_id'])
			{
				$site_rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
			}
			else
			{
				$site_rs = $this->model("site")->get_one_default();
			}
			if(!$site_rs) $site_rs = array('title'=>'PHPOK企业建站系统');
			$this->site = $site_rs;
			return true;
		}
		$siteId = $this->get("siteId","int");
		$domain = strtolower($_SERVER["SERVER_NAME"]);
		$site_rs = false;
		if($siteId)
		{
			$site_rs = $this->model('site')->get_one($siteId);
			if($site_rs)
			{
				$domain_rs = $this->model('site')->domain_one($site_rs['domain_id']);
				if($domain_rs && $domain_rs['domain'] != $domain)
				{
					header("Location:http://".$domain_rs['domain'].$site_rs['dir']);
					exit;
				}
			}
		}
		if(!$site_rs)
		{
			$site_rs = $this->model("site")->get_one_from_domain($domain);
			if(!$site_rs) $site_rs = $this->site_model->get_one_default();
			if(!$site_rs) $this->error("无法获取网站信息，请检查！");
			$ext_list = $this->site_model->site_config($site_rs["id"]);
			if($ext_list) $site_rs = array_merge($ext_list,$site_rs);
			//读取模板扩展
		}
		if($site_rs["tpl_id"])
		{
			$rs = $this->model("tpl")->get_one($site_rs["tpl_id"]);
			if($rs)
			{
				$tpl_rs = array();
				$tpl_rs["id"] = $rs["id"];
				$tpl_rs["dir_tpl"] = $rs["folder"] ? "tpl/".$rs["folder"]."/" : "tpl/www/";
				$tpl_rs["dir_cache"] = $this->dir_root."data/tpl_www/";
				$tpl_rs["dir_php"] = $rs['phpfolder'] ? $this->dir_root.$rs['phpfolder'].'/' : $this->dir_root;
				$tpl_rs["dir_root"] = $this->dir_root;
				if($rs["folder_change"])
				{
					$tpl_rs["path_change"] = $rs["folder_change"];
				}
				$tpl_rs["refresh_auto"] = $rs["refresh_auto"] ? true : false;
				$tpl_rs["refresh"] = $rs["refresh"] ? true : false;
				$tpl_rs["tpl_ext"] = $rs["ext"] ? $rs["ext"] : "html";
				//针对手机版的配置
				if($this->is_mobile)
				{
					$tpl_rs["id"] = $rs["id"]."_mobile";
					$tplfolder = $rs["folder"] ? $rs["folder"]."_mobile" : "www_mobile";
					if(!file_exists($this->dir_root."tpl/".$tplfolder))
					{
						$tplfolder = $rs["folder"] ? $rs["folder"] : "www";
					}
					$tpl_rs["dir_tpl"] = "tpl/".$tplfolder;
				}
				$site_rs["tpl_id"] = $tpl_rs;
			}
		}
		$this->site = $site_rs;
	}

	//装载插件
	function init_plugin()
	{
		$rslist = $this->model('plugin')->get_all();
		if(!$rslist) return $rslist;
		foreach($rslist AS $key=>$value)
		{
			if($value['param']) $value['param'] = unserialize($value['param']);
			if(is_file($this->dir_root.'plugins/'.$key.'/'.$this->app_id.'.php'))
			{
				include_once($this->dir_root.'plugins/'.$key.'/'.$this->app_id.'.php');
				$name = $this->app_id."_".$key;
				$cls = new $name();
				$mlist = get_class_methods($cls);
				$this->plugin[$key] = array("method"=>$mlist,"obj"=>$cls,'id'=>$key);
			}
		}
	}
	
	function lib($class,$ext_folder="")
	{
		return $this->load($class,"lib",$ext_folder);
	}

	function model($class,$ext_folder="")
	{
		return $this->load($class,"model",$ext_folder);
	}

	//运行插件
	function plugin($ap,$param="")
	{
		if(!$ap) return false;
		$ap = str_replace("-","_",$ap);//替换节点的中划线为下划线
		if(!$this->plugin || count($this->plugin)<1 || !is_array($this->plugin)) return false;
		foreach($this->plugin AS $key=>$value)
		{
			if(in_array($ap,$value['method']))
			{
				$value['obj']->$ap($param);
			}
		}
		return true;
	}

	//加载HTML插件节点
	function plugin_html_ap($name)
	{
		$ap = 'html-'.$this->ctrl.'-'.$this->func.'-'.$name;
		$this->plugin($ap);
	}

	function load($class,$type="lib",$ext_folder="")
	{
		if(!$class)
		{
			return false;
		}
		$tmp = $class.'_'.$type;
		if($this->obj_list && is_array($this->obj_list) && in_array($tmp,$this->obj_list))
		{
			return $this->$tmp;
		}
		if($type == 'model')
		{
			return $this->_load_model($class,$ext_folder);
		}
		return $this->_load_lib($class,$ext_folder);
	}

	//仅限内部使用的加载Model信息
	private function _load_model($class,$folder='')
	{
		$file = $this->dir_phpok.'model/'.$this->app_id.'/';
		if($folder && $folder != '/')
		{
			$file .= $folder;
			if(substr($folder,-1) != '/') $file .= '/';
		}
		$file .= $class.'_model.php';
		if(!is_file($file))
		{
			$file = $this->dir_phpok.'model/';
			if($folder && $folder != '/')
			{
				$file .= $folder;
				if(substr($folder,-1) != '/') $file .= '/';
			}
			$file .= $class.'.php';
		}
		if(!is_file($file))
		{
			return false;
		}
		include($file);
		$name = $class.'_model';
		$this->obj_list[] = $name;
		$this->$name = new $name();
		return $this->$name;
	}

	private function _load_lib($class,$folder='')
	{
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
		$name = $class.'_lib';
		$this->obj_list[] = $name;
		$this->$name = new $name();
		return $this->$name;
	}
	
	//装载资源引挈
	function init_engine()
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
			$file = $this->dir_phpok."engine/".$key."/".$value["file"].".php";
			if(file_exists($file))
			{
				include($file);
				$var = $key."_".$value["file"];
				$this->$key = new $var($value);
			}
		}
	}

	//读取网站参数配置
	function init_config()
	{
		//根目录下的config，一般该文件是来用配置全局网站需要用到的信息
		$file = $this->dir_root."config.php";
		if(is_file($file)) include($file);
		//全站全局参数
		$file = $this->dir_phpok."config/config.global.php";
		if(is_file($file)) include($file);
		//配置文档下的config，这里就可以针对各个应用进行配置了
		$file = $this->dir_phpok."config/config_".$this->app_id.".php";
		if(is_file($file)) include($file);
		//判断是否有使用Debug
		$config["debug"] ? error_reporting(E_ALL ^ E_NOTICE) : error_reporting(0);
		//判断是否使用gzip功能
		if(ini_get('zlib.output_compression'))
		{
			ob_start();
		}
		else
		{
			($config["gzip"] && function_exists("ob_gzhandler")) ? ob_start("ob_gzhandler") : ob_start();
		}
		//调节时差
		if($config["timezone"] && function_exists("date_default_timezone_set"))
		{
			date_default_timezone_set($config["timezone"]);
		}
		//调节时间误差，支持到秒
		$this->time = time();
		if($config["timetuning"])
		{
			$this->time = $this->time + $config["timetuning"];
		}
		$this->system_time = $this->time;
		$this->config = $config;
		$this->url = $this->root_url();
	}

	//自定义网址生成器
	function url($ctrl="",$func="",$ext="",$appid='',$baseurl=false)
	{
		if(!$appid) $appid = $this->app_id;
		if($appid  == "admin" || $appid == 'api')
		{
			$url = $this->config[$appid.'_file'];
			if($baseurl)
			{
				$url = $this->url.$url;
			}
			if(!$ctrl && !$func && !$ext) return $url;
			if($ctrl || $func || $ext) $url .= "?";
			if($ctrl) $url .= $this->config["ctrl_id"]."=".$ctrl."&";
			if($func) $url .= $this->config["func_id"]."=".$func."&";
			if($ext) $url .= $ext;
			if(substr($url,-1) == "&" || substr($url,-1) == "?") $url = substr($url,0,-1);
			return $url;
		}
		$url = $this->url;
		//伪静态页
		$reserved = $this->config['reserved'] ? explode(',',$this->config['reserved']) : array('js','ajax','inp');
		if($this->site["url_type"] == "rewrite" && !in_array($ctrl,$reserved))
		{
			$url .= $ctrl;
			if($func) $url .= "/".$func;
			$url .= ".html";
			if($ext)
			{
				$url .="?".$ext;
			}
			return $url;
		}
		if(!$ctrl) return $url;
		$url .= $this->config['www_file'];
		//判断ctrl在
		if(in_array($ctrl,$reserved))
		{
			$url .= "?".$this->config["ctrl_id"]."=".$ctrl;
			if($func) $url .= "&".$this->config["func_id"]."=".$func;
			if($ext && $ext != "&")
			{
				if(substr($ext,0,1) == "&") $ext = substr($ext,1);
				$url .= "&".$ext;
			}
			return $url;
		}
		$url .= "?id=".$ctrl;
		if($func && $func != "&")
		{
			$url .= substr($func,0,1) == "&" ? $func : '&cate='.$func;
		}
		if($ext && $ext != "&")
		{
			if(substr($ext,0,1) == "&") $ext = substr($ext,1);
			$url .= "&".$ext;
		}
		return $url;
	}

	function root_url()
	{
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$port = $_SERVER["SERVER_PORT"];
		$myurl = $_SERVER["SERVER_NAME"];
		if($port != "80" && $port != "443")
		{
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
		}
		$myurl .= "/";
		$myurl = str_replace("//","/",$myurl);
		return $http_type.$myurl;
	}
	
	//配置网站全局常量
	function init_constant()
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

	//注销
	function __destruct(){}

	//通过post或get取得数据，并格式化成自己需要的
	function get($id,$type="safe",$ext="")
	{
		$val = isset($_POST[$id]) ? $_POST[$id] : (isset($_GET[$id]) ? $_GET[$id] : "");
		if($val == '') return false;
		//判断内容是否有转义，所有未转义的数据都直接转义
		$addslashes = false;
		if(function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) $addslashes = true;
		if(!$addslashes) $val = $this->_addslashes($val);
		return $this->format($val,$type,$ext);
	}

	//格式化内容
	//msg，要格式化的内容，该内容已经addslashes了
	//type，类型，支持：safe，text，html，html_js，func，int，float，system
	//ext，扩展，当type为html时，ext存在表示支持js，不存在表示不支持js
	//当type为func属性时，表示ext直接执行函数
	function format($msg,$type="safe",$ext="")
	{
		if($msg == "") return false;
		if(is_array($msg))
		{
			foreach($msg AS $key=>$value)
			{
				$key2 = $this->format($key,"system");
				if(!$key2)
				{
					unset($msg[$key]);
					continue;
				}
				$msg[$key] = $this->format($value,$type,$ext);
			}
			if($msg && count($msg)>0)
			{
				return $msg;
			}
			return false;
		}
		//echo "<pre>".print_r($msg,true)."</pre>";
		//如果返回的是html
		if($type == 'html_js' || ($type == 'html' && $ext))
		{
			//去除编辑器里的绝对网址
			$msg = stripslashes($msg);
			$array = array("src='".$this->url,'src="'.$this->url,"src=".$this->url);
			$new = array("src='",'src="',"src=");
			$msg = str_replace($array,$new,$msg);
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
			case 'html':$msg = $this->safe_html($msg);break;
			case 'func':$msg = function_exists($ext) ? $ext($msg) : false;break;
		}
		if($msg)
		{
			$msg = addslashes($msg);
		}
		return $msg;
	}

	//安全的HTML信息
	//主要是过滤HTML中的on****各种属性
	//过滤iframe,script,link等信息
	function safe_html($info)
	{
		if(!$info)
		{
			return false;
		}
		$tmp = "/<([a-zA-Z0-9]+)(.*)(on[abort|beforeonload|blur|change|click|contextmenu|dblclick|drag|dragend|dragenter|dragleave|dragstart|drop|error|focus|keydown|keypress|keyup|load|message|mousedown|mousemove|mouseover|mouseout|mouseup|mousewheel|reset|resize|scroll|select|submit|unload]+)=(.+)>/isU";
		$info = preg_replace($tmp,"<\\1\\2\\4>",$info);
		//$info = preg_replace("/<([a-zA-Z0-9]+)(.*)([onabort|onbeforeonload|onblur|onchange|onclick|oncontextmenu|ondblclick|ondrag|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onfocus|onkeydown|onkeypress|onkeyup|onload|onmessage|onmousedown|onmousemove|onmouseover|onmouseout|onmouseup|onmousewheel|onreset|onresize|onscroll|onselect|onsubmit|onunload]+)\s*=\s*(.+)>/isU","<\\1\\3>",$info);
		$tmp = array("/<script(.*)<\/script>/isU","/<frame(.*)>/isU","/<\/fram(.*)>/isU","/<iframe(.*)>/isU","/<\/ifram(.*)>/isU","/<style(.*)<\/style>/isU","/<link(.*)>/isU","/<\/link>/isU");
		$info = preg_replace($tmp,'',$info);
		$array = array("src='".$this->url,'src="'.$this->url,"src=".$this->url);
		$new = array("src='",'src="',"src=");
		$info = str_replace($array,$new,$info);
		return $info;
	}

	//转义字符串数据，此函数仅限get使用
	function _addslashes($val)
	{
		if(is_array($val))
		{
			foreach($val AS $key=>$value)
			{
				$val[$key] = $this->_addslashes($value);
			}
		}
		else
		{
			$val = addslashes($val);
		}
		return $val;
	}

	function assign($var,$val)
	{
		$this->tpl->assign($var,$val);
	}

	function unassign($var)
	{
		$this->tpl->unassign($var);
	}

	function view($file,$type="file",$path_format=true)
	{
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		header("Content-type: text/html; charset=utf-8");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00  GMT"); 
		header("Cache-control: no-cache,no-store,must-revalidate,max-age=3"); 
		header("Pramga: no-cache"); 
		$this->tpl->display($file,$type,$path_format);
	}

	function fetch($file,$type="file",$path_format=true)
	{
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		return $this->tpl->fetch($file,$type,$path_format);
	}

	function get_url()
	{
		return $this->url;
	}
	//导常抛出
	function error($content="")
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

	# 判断是否是UTF8
	function is_utf8($string)
	{
		if(function_exists('mb_detect_encoding'))
		{
			$e=mb_detect_encoding($string, array('UTF-8','GBK'));
			return $e == 'UTF-8' ? true : false;
		}
		return preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$word) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$string) == true ? true : false;
	}

	//字符串转换
	function charset($msg,$from_charset="GBK",$to_charset="UTF-8")
	{
		if(!$msg) return false;
		if(!function_exists("iconv")) return $msg;
		if(is_array($msg))
		{
			foreach($msg AS $key=>$value)
			{
				$msg[$key] = $this->charset($value,$from_charset,$to_charset);
			}
		}
		else
		{
			$msg = iconv($from_charset,$to_charset,$msg);
		}
		return $msg;
	}

	//执行应用
	function action()
	{
		$this->init_assign();
		//装载插件
		$this->init_plugin();
		$func_name = "action_".$this->app_id;
		if(in_array($func_name,$this->this_method_list))
		{
			$this->$func_name();
			exit;
		}
		$func_name = "action_www";
		$this->$func_name();
		exit;
	}

	function action_api()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl) $ctrl = 'index';
		if(!$func) $func = 'index';
		$this->_action($ctrl,$func);
	}

	//前端参数获取
	function action_www()
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
		if(!$ctrl) 
		if(!$ctrl) $ctrl = 'index';
		//如果没有Func,将使用 index 
		if(!$func) $func = $this->get($this->config["func_id"],"system");
		if(!$func) $func = 'index';
		//针对静态页网站进行跳转
		if($this->site['url_type'] == 'html' && $this->app_id == 'www' && $ctrl == 'index')
		{
			$root_dir = ($this->site['html_root_dir'] && $this->site['html_root_dir'] != '/') ? $this->site['html_root_dir'] : '';
			$url = $this->url.$root_dir.'index.html';
			header("Location:".$url);
			exit;
		}
		$this->_action($ctrl,$func);
	}

	//仅限管理员的操作
	function action_admin()
	{
		$ctrl = $this->get($this->config["ctrl_id"],"system");
		$func = $this->get($this->config["func_id"],"system");
		if(!$ctrl) $ctrl = "index";
		if(!$func) $func = "index";
		$this->_action($ctrl,$func);
	}

	function _action($ctrl='index',$func='index')
	{
		//如果App_id非指定的三种，强制初始化
		if(!in_array($this->app_id,array('api','www','admin')))
		{
			$this->app_id = 'www';
		}
		$reserved = array('js');
		if($this->config[$this->app_id]['reserved'])
		{
			$reserved = explode(',',$this->config[$this->app_id][reserved]);
		}
		$reserved = array_merge($reserved,array('login','js','ajax','inp'));
		$reserved = array_unique($reserved);
		$is_login = $this->config[$this->app_id]['is_login'] ? true : false;
		$is_admin = $this->config[$this->app_id]['is_admin'] ? true : false;
		if($is_admin && !$_SESSION['admin_id'])
		{
			if(!in_array($ctrl,$reserved))
			{
				$ctrl = 'login';
				$go_url = $this->url($ctrl);
				header("Location:".$go_url);
				exit;
			}
		}
		if($is_login && !$_SESSION['user_id'])
		{
			if(!in_array($ctrl,$reserved))
			{
				$ctrl = 'login';
				$go_url = $this->url($ctrl);
				header("Location:".$go_url);
				exit;
			}
		}
		$dir_root = $this->dir_phpok.$this->app_id.'/';
		if($ctrl == 'js' || $ctrl == 'ajax' || $ctrl == "inp")
		{
			$dir_root = $this->dir_phpok;
		}
		//加载应用文件
		if(!is_file($dir_root.$ctrl.'_control.php'))
		{
			$this->error('应用文件：'.$ctrl.'_control.php 不存在，请检查');
		}
		include($dir_root.$ctrl.'_control.php');
		if(is_file($this->dir_phpok.$this->app_id."/global.func.php"))
		{
			include($this->dir_phpok.$this->app_id."/global.func.php");
		}
		//执行应用
		$app_name = $ctrl."_control";
		$this->ctrl = $ctrl;
		$this->func = $func;
		$cls = new $app_name();
		$list = get_class_methods($cls);
		$func_name = $func."_f";
		if(!in_array($func_name,$list))
		{
			$this->error("文件：".$ctrl."_control.php 不存在方法：".$func_name."！");
		}
		//自动运行的函数
		if($this->config[$this->app_id]["autoload_func"])
		{
			$list = explode(",",$this->config["autoload_func"]);
			foreach($list AS $key=>$value)
			{
				if(function_exists($value))
				{
					$value();
				}
			}
		}
		$this->config['ctrl'] = $ctrl;
		$this->config['func'] = $func;
		$this->config['time'] = $this->time;
		$this->assign('sys',$this->config);
		//节点触发器
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-before');
		if($this->app_id == 'www' && !$this->site['status'])
		{
			$this->error($this->site["content"]);
		}
		$cls->$func_name();
	}

	//JSON内容输出
	final public function json($content,$status=false,$exit=true,$format=true)
	{
		$this->plugin('ap-'.$this->ctrl.'-'.$this->func.'-after');
		//当content内容为true 且为布尔类型，直接返回正确通知结果
		if($content && is_bool($content))
		{
			$rs = array('status'=>'ok');
			exit($this->lib('json')->encode($rs));
		}
		$status_info = $status ? 'ok' : 'error';
		$rs = array('status'=>$status_info);
		if($content != '') $rs['content'] = $content;
		$info = $this->lib('json')->encode($rs);
		if($exit) exit($info);
		return $info;
	}

	//针对PHPOK4前台执行SEO优化
	final public function phpok_seo($rs)
	{
		if(!$rs || !is_array($rs)) return false;
		$seo = $this->site['seo'] ? $this->site["seo"] : array();
		foreach($rs AS $key=>$value)
		{
			if(substr($key,0,3) == "seo" && $value && is_string($value))
			{
				$subkey = substr($key,4);
				if($subkey == "kw" || $subkey == "keywords" || $subkey == "keyword")
				{
					$seo["keywords"] = $value;
				}
				elseif($subkey == "desc" || $subkey == "description")
				{
					$seo["description"] = $value;
				}
				elseif($subkey == "title")
				{
					$seo["title"] = $value;
				}
				else
				{
					$seo[$subkey] = $value;
				}
			}
		}
		$this->site['seo'] = $seo;
		$this->assign("seo",$seo);
	}

	function ascii($str='')
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

	//增加默认存储，这个缓存每次读取都会重新生成，不存在过期时效
	function cache_set($id,$content='')
	{
		if($id == '' || $content == '')
		{
			return false;
		}
		$id = 'phpok_'.substr(md5($id),9,16);
		$this->phpok_cache[$id] = $content;
	}

	//读取缓存
	function cache_get($id)
	{
		$id = 'phpok_'.substr(md5($id),9,16);
		if($this->phpok_cache[$id])
		{
			return $this->phpok_cache[$id];
		}
		return false;
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
		if(method_exists($GLOBALS['app'],$method))
		{
			return call_user_func_array(array($GLOBALS['app'],$method),$param);
		}
		else
		{
			$lst = explode("_",$method);
			if($lst[1] == 'model')
			{
				$GLOBALS['app']->model($lst[0]);
				 call_user_func_array(array($GLOBALS['app'],$method),$param);
			}
			elseif($lst[1] == 'lib')
			{
				$GLOBALS['app']->lib($lst[0]);
				return call_user_func_array(array($GLOBALS['app'],$method),$param);
			}
		}
	}

	//5.3.0版以后的使用方法，这是调用静态方法重载的魔术方法
	public static function __callStatic($name, $arguments) 
    {
		return $this->__call($name,$arguments);
    }

	public function __get($id)
	{
		$lst = explode("_",$id);
		if($lst[1] == "model")
		{
			return $GLOBALS['app']->model($lst[0]);
		}
		elseif($lst[1] == "lib")
		{
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
	public function model()
	{
		parent::__construct();
	}
}

class phpok_plugin extends _init_auto
{
	//默认父类，装载语言包
	function plugin()
	{
		parent::__construct();
		//读取语言ID
		$id = $this->plugin_id();
		if($this->site['lang'] && is_file($this->dir_root.'plugins/'.$id.'/langs/'.$this->site['lang'].'.xml'))
		{
			$langs = xml_to_array(file_get_contents($this->dir_root.'plugins/'.$id.'/langs/'.$this->site['lang'].'.xml'));
			if($langs && is_array($langs))
			{
				$langs = phpok_ubb($langs);
				if($GLOBALS['app']->lang)
				{
					$GLOBALS['app']->lang = array_merge($langs,$GLOBALS['app']->lang);
				}
				else
				{
					$GLOBALS['app']->lang = $langs;
				}
				$this->assign('lang',$GLOBALS['app']->lang);
			}
		}
	}

	final public function plugin_id()
	{
		$name = get_class($this);
		$lst = explode("_",$name);
		return $lst[1];
	}

	final public function plugin_info($id='')
	{
		if(!$id) $id = $this->plugin_id();
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs)
		{
			$rs = array('id'=>$id);
		}
		if($rs['param'])
		{
			$rs['param'] = unserialize($rs['param']);
		}
		$rs['path'] = $this->dir_root.'plugins/'.$id.'/';
		return $rs;
	}

	//存储插件配置
	final public function plugin_save($ext,$id="")
	{
		if(!$id) $id = $this->plugin_id();
		if(!$id) return false;
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs) return false;
		$info = ($ext && is_array($ext)) ? serialize($ext) : '';
		$this->model('plugin')->update_param($id,$info);
	}
	
	//cf，控制接入点
	function load_after($cf,$param="")
	{
		//接入点不存在时，取消执行
		if(!$cf) return false;
		//接入点不符合要求时，取消执行
		if(!in_array($cf,$this->AP)) return false;
		//取得接入点
	}

	// 接入点列表
	function cf_list()
	{
		$list = array();
		$list["list-ok"] = "存储内容数据";
		$list["list-edit"] = "编辑内容数据";
		return $list;
	}

	//加载插件模板
	function plugin_tpl($name,$id='')
	{
		if(!$id) $id = $this->plugin_id();
		$file = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		if(is_file($file))
		{
			return $this->fetch($file,'abs-file');
		}
		return false;
	}

	//输入模板
	function echo_tpl($name,$id='')
	{
		if(!$id) $id = $this->plugin_id();
		$file = $this->dir_root.'plugins/'.$id.'/template/'.$name;
		$this->view($file,'abs-file');
	}	
	
}

//安全注销全局变量
unset($_ENV, $_SERVER['MIBDIRS'],$_SERVER['MYSQL_HOME'],$_SERVER['OPENSSL_CONF'],$_SERVER['PHP_PEAR_SYSCONF_DIR'],$_SERVER['PHPRC'],$_SERVER['SystemRoot'],$_SERVER['COMSPEC'],$_SERVER['PATHEXT'], $_SERVER['WINDIR'],$_SERVER['PATH']);

$app = new _init_phpok();
include_once($app->dir_phpok."phpok_helper.php");
$app->init_autoload();
$app->init_site();
$app->init_view();
function init_app()
{
	return $GLOBALS['app'];
}
//核心函数，phpok_head_js，用于加载自定义扩展中涉及到的js
function phpok_head_js()
{
	$jslist = $GLOBALS['app']->jslist;
	if(!$jslist || !is_array($jslist)) return false;
	$jslist = array_unique($jslist);
	$html = "";
	foreach($jslist AS $key=>$value)
	{
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
	foreach($csslist AS $key=>$value)
	{
		$html .= '<link rel="stylesheet" type="text/css" href="'.$value.'" charset="utf-8" />'."\n";
	}
	return $html;
}
//核心函数，语言包
function P_Lang($info)
{
	if(!$info)
	{
		return false;
	}
	if(!$GLOBALS['app']->lang)
	{
		return $info;
	}
	$keyid = array_search($info,$GLOBALS['app']->lang);
	if(!$keyid)
	{
		return $info;
	}
	return $GLOBALS['app']->lang[$keyid];
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
<?php
/**
 * 对$_SERVER进行封装操作
 * @package phpok\libs
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年09月28日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class server_lib
{
	public function __construct()
	{
		//
	}

	/**
	 * 取得域名，通过$_SERVER['SERVER_NAME'] 或 $_SERVER['HTTP_HOST'] 取得，并进行安全过滤检测
	 * @参数 $name 支持 server_name 和 http_host
	 * @返回 false 或是 正确的域名
	**/
	public function domain($name='server_name')
	{
		if(!$name){
			$name = 'server_name';
		}
		$name = strtolower($name);
		if(!in_array($name,array('server_name','http_host'))){
			return false;
		}
		$domain = $_SERVER[strtoupper($name)];
		//检测domain是否符合要求
		if(!preg_match('/^[0-9a-zA-Z][\w\-\.]*[0-9a-zA-Z]$/isU',$domain)){
			return false;
		}
		return $domain;
	}

	/**
	 * 取得$_SERVER['PHP_SELF']，当前执行脚本的文件名，与 document root 有关
	**/
	public function me()
	{
		return $_SERVER['PHP_SELF'];
	}

	/**
	 * 取得包含由客户端提供的，包括在真实脚本名称之后并且在查询语句（query string）之前的路径信息
	**/
	public function path_info()
	{
		return $_SERVER['PATH_INFO'];
	}

	/**
	 * 取得用来指定要访问的页面
	**/
	public function uri()
	{
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * 取得网址中?后面的参数
	**/
	public function query($system=false)
	{
		global $app;
		$string = $_SERVER['QUERY_STRING'];
		if(!$string){
			return false;
		}
		parse_str($string,$info);
		if(!$info){
			return false;
		}
		$format = $system ? 'system' : 'safe';
		foreach($info as $key=>$value){
			$tmp = $app->format($value,$format);
			if(!$tmp){
				unset($info[$key]);
			}
		}
		return http_build_query($info);
	}

	/**
	 * 判断是否走https
	**/
	public function https()
	{
		if($_SERVER['SERVER_PORT'] == 443){
			return true;
		}
		if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
			return true;
		}
	    if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
		    return true;
	    }
	    return false;
	}

	/**
	 * 取得当前WEB端口
	**/
	public function port()
	{
		if(isset($_SERVER['SERVER_PORT'])){
			return intval($_SERVER['SERVER_PORT']);
		}
		if($this->https()){
			return 443;
		}
		return 80;
	}

	/**
	 * 检测是否走ajax
	**/
	public function ajax()
	{
		if(defined('IS_AJAX')){
			return true;
		}
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			return true;
		}
		if(isset($_SERVER['request_type']) && strtolower($_SERVER['request_type']) == 'ajax'){
			return true;
		}
		if(isset($_SERVER['phpok_ajax']) || isset($_SERVER['is_ajax'])){
			return true;
		}
		if(isset($_POST['ajax_submit']) || isset($_GET['ajax_submit'])){
			return true;
		}
		return false;
	}

	/**
	 * 取得当前脚本文件名，为空或获取失败返回index.php
	**/
	public function phpfile()
	{
		return $_SERVER['SCRIPT_NAME'] ? basename($_SERVER['SCRIPT_NAME']) : 'index.php';
	}

	public function referer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
	}
}

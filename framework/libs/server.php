<?php
/**
 * 对$_SERVER进行封装操作
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年09月28日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class server_lib extends _init_lib
{
	public function __construct()
	{
		//
	}

	/**
	 * 取得域名，通过$_SERVER['SERVER_NAME'] 或 $_SERVER['HTTP_HOST'] 取得，并进行安全过滤检测
	 * @返回 false 或是 正确的域名
	**/
	public function domain()
	{
		$domain = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		if(!$domain){
			return false;
		}
		if(strpos($domain,":") !== false){
			$tmp = explode(":",$domain);
			$domain = $tmp[0];
		}
		//检测domain是否符合要求
		if(!preg_match('/^[0-9a-zA-Z][\w\-\.]*[0-9a-zA-Z]$/isU',$domain)){
			return false;
		}
		return $domain;
	}

	/**
	 * 取得IP，仅限服务端IP，客户端IP不能通过此方法取
	**/
	public function ip()
	{
		return $_SERVER['SERVER_ADDR'];
	}

	/**
	 * 取得服务器版本和虚拟主机名的字符串。
	**/
	public function signature()
	{
		return $_SERVER['SERVER_SIGNATURE'];
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
		return isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
	}

	/**
	 * 取得用来指定要访问的页面
	**/
	public function uri()
	{
		$uri = $_SERVER['REQUEST_URI'];
		if(strpos($uri, "?") !== false){
			$tmp = explode("?",$uri);
			$uri = $tmp[0].'?'.$this->query();
		}
		return $uri;
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
			if($tmp == ''){
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
		if(isset($_SERVER['HTTP_REQUEST_TYPE']) && strtolower($_SERVER['HTTP_REQUEST_TYPE']) == 'ajax'){
			return true;
		}
		if(isset($_SERVER['request_type']) && strtolower($_SERVER['request_type']) == 'ajax'){
			return true;
		}
		if(isset($_SERVER['phpok_ajax']) || isset($_SERVER['is_ajax'])){
			return true;
		}
		if(isset($_SERVER['HTTP_AJAX'])){
			return true;
		}
		if(isset($_SERVER['HTTP_PHPOK_AJAX']) || isset($_SERVER['HTTP_IS_AJAX'])){
			return true;
		}
		if(isset($_POST['ajax_submit']) || isset($_GET['ajax_submit'])){
			return true;
		}
		if(isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']),'json') !== false){
			return true;
		}
		if(isset($_POST['IS_AJAX']) && $_POST['IS_AJAX']){
			return true;
		}
		if(isset($_POST['AJAX']) && $_POST['AJAX']){
			return true;
		}
		if(isset($_POST['is_ajax']) && $_POST['is_ajax']){
			return true;
		}
		if(isset($_POST['ajax']) && $_POST['ajax']){
			return true;
		}
		if(isset($_GET['IS_AJAX']) && $_GET['IS_AJAX']){
			return true;
		}
		if(isset($_GET['AJAX']) && $_GET['AJAX']){
			return true;
		}
		if(isset($_GET['is_ajax']) && $_GET['is_ajax']){
			return true;
		}
		if(isset($_GET['ajax']) && $_GET['ajax']){
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
		$info = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
		if(!$info){
			return false;
		}
		$tlist = parse_url($info);
		$info = str_replace(array('<','>','"',"'"),'',$info);
		return $info;
	}

	public function agent()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}
}

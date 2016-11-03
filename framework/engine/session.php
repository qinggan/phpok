<?php
/*****************************************************************************************
	文件： session.php
	备注： SESSION基类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2016年01月12日 06时28分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session
{
	protected $timeout = 600;
	protected $config;
	protected $sid = 'PHPSESSION';
	protected $sessid = '';
	public function __construct($config='')
	{
		if($config && is_array($config)){
			$this->config($config);
		}
		session_name($this->sid);
		$session_id = isset($_POST[$this->sid]) ? $_POST[$this->sid] : (isset($_GET[$this->sid]) ? $_GET[$this->sid] : "");
		if($session_id && preg_match("/^[a-z0-9A-Z\_\-]+$/u",$session_id)){
			session_id($session_id);
			$this->sessid($session_id);
		}else{
			$this->sessid();
		}
		session_cache_expire(intval($this->timeout)/60);
		session_cache_limiter('public');
	}

	public function start()
	{
		session_start();
		return true;
	}

	public function save_path($path='')
	{
		if($path){
			session_save_path($path);
		}
	}

	public function config($config)
	{
		if($config){
			$this->config = $config;
			if($config['timeout']){
				$this->timeout($config['timeout']);
			}
			if($config['id']){
				$this->sid($config['sid']);
			}
		}
		return $this->config;
	}

	public function sessid($sessid="")
	{
		if($sessid){
			$this->sessid = $sessid;
		}
		if(!$this->sessid){
			$this->sessid = session_id();
		}
		return $this->sessid;
	}

	public function sid($sid='')
	{
		if($sid){
			$this->sid = $sid;
		}
		return $this->sid;
	}

	public function timeout($timeout='')
	{
		if($timeout){
			$this->timeout = $timeout;
		}
		return $this->timeout;
	}

	/**
	 * 返回设定的SESSION信息，用$this->session->val('变量名')，替代写法 $_SESSION['变量名']
	 * @参数 $var 变量名
	 * @返回 session存储的信息，可以是对象，字符串，数值，布尔值等
	**/
	final public function val($var)
	{
		if(strpos($var,'.') !== false){
			$list = explode(".",$var);
			if(!isset($_SESSION[$list[0]])){
				return false;
			}
			$tmp = $_SESSION[$list[0]];
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
		if(isset($_SESSION[$var])){
			return $_SESSION[$var];
		}
		return false;
	}

	/**
	 * 设定session信息，用$this->session->assign('变量名','变量值') 替代写法 $_SESSION['变量名'] = '变量值'
	 * @参数 $var 变量名
	 * @参数 $val 变量值
	 * @返回 true，无论如何，都返回true
	**/
	final public function assign($var,$val='')
	{
		if(strpos($var,'.') !== false){
			$list = explode(".",$var);
			$string = '$_SESSION';
			foreach($list as $key=>$value){
				$string .= '["'.$value.'"]';
			}
			eval("$string = $val;");
			return true;
		}
		$_SESSION[$var] = $val;
		return true;
	}

	/**
	 * 取消session设定的内容，用$this->session->unassign('变量名') 替代写法 unset($_SESSION['变量名'])
	 * @参数 $var 要取消的变量
	 * @返回 true
	 * @更新时间 2016年07月25日
	**/
	final public function unassign($var)
	{
		if(strpos($var,'.') !== false){
			$list = explode(".",$var);
			$string = '$_SESSION';
			foreach($list as $key=>$value){
				$string .= '['.$value.']';
			}
			eval("unset($string);");
			return true;
		}
		if(isset($_SESSION[$var])){
			unset($_SESSION[$var]);
		}
		return true;
	}

	public function destroy(){
		session_destroy();
		return true;
	}
}
?>
<?php
/**
 * SESSION基类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月19日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class session
{
	protected $timeout = 2000;
	protected $config;
	protected $sid = 'PHPSESSION';
	protected $sessid = '';
	protected $cache_expire = 30;
	protected $secure = false;
	protected $httponly = true;
	protected $cache_limiter = 'nocache';
	protected $cookie_domain = '';

	/**
	 * 构造函数
	**/
	public function __construct($config='')
	{
		if($config && is_array($config)){
			$this->config($config);
		}
		session_name($this->sid);
		if(isset($_POST[$this->sid]) || isset($_GET[$this->sid])){
			$session_id = $_POST[$this->sid] ? $_POST[$this->sid] : $_GET[$this->sid];
		}elseif($_COOKIE[$this->sid] || $_SERVER[$this->sid]){
			$session_id = $_COOKIE[$this->sid] ? $_COOKIE[$this->sid] : $_SERVER[$this->sid];
		}elseif($_COOKIE['HTTP_'.$this->sid] || $_SERVER['HTTP_'.$this->sid]){
			$session_id = $_COOKIE['HTTP_'.$this->sid] ? $_COOKIE['HTTP_'.$this->sid] : $_SERVER['HTTP_'.$this->sid];
		}else{
			$session_id = '';
		}
		if($session_id && preg_match("/^[a-z0-9A-Z\_\-]+$/u",$session_id)){
			session_id($session_id);
		}
		session_set_cookie_params($this->timeout,'/',$this->cookie_domain,$this->secure,$this->httponly);
		session_cache_expire($this->cache_expire);
		session_cache_limiter('nocache');
	}

	public function start()
	{
		session_start();
	}

	public function save_path($path='')
	{
		if($path){
			session_save_path($path);
		}
	}

	public function config($config)
	{
		if($config && is_array($config)){
			$this->config = $config;
			$keys = array('cache_expire','cache_limiter','secure','httponly','timeout');
			foreach($config as $key=>$value){
				if($key == 'id'){
					$this->sid($value);
				}
				if($key == 'domain'){
					$this->cookie_domain($value);
				}
				if(in_array($key,$keys)){
					$this->$key($value);
				}
			}
		}
		return $this->config;
	}

	public function comment($sessid='')
	{
		if(!$sessid){
			return false;
		}
		$this->sessid($sessid);
		session_commit();
		session_id($sessid);
		$this->start();
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

	public function timeout($timeout=0)
	{
		if(intval($timeout)>600){
			$this->timeout = $timeout;
		}
		return $this->timeout;
	}

	public function cache_expire($val='')
	{
		if(is_numeric($val) && $val && $val>10){
			$this->cache_expire = $val;
		}
		return $this->cache_expire;
	}

	public function cookie_domain($val='')
	{
		if($val){
			$this->cookie_domain = $val;
		}
		return $this->cookie_domain;
	}

	public function cache_limiter($val='')
	{
		if($val && in_array($val,array('nocache','private','public','private_no_expire'))){
			$this->cache_limiter = $val;
		}
		return $this->cache_limiter;
	}

	/**
	 * 是否仅在安全下使用
	**/
	public function secure($val=false)
	{
		if(is_bool($val)){
			$this->secure = $val;
		}
		return $this->secure;
	}

	/**
	 * 是否仅使用 httponly 模板
	**/
	public function httponly($val=false)
	{
		if(is_bool($val)){
			$this->httponly = $val;
		}
		return $this->httponly;
	}

	/**
	 * 返回设定的SESSION信息，用$this->session->val('变量名')，替代写法 $_SESSION['变量名']
	 * @参数 $var 变量名
	 * @返回 session存储的信息，可以是对象，字符串，数值，布尔值等
	**/
	final public function val($var='')
	{
		if($var == ''){
			return $_SESSION;
		}
		if(!$this->_string_safe_check($var)){
			return false;
		}
		if(strpos($var,'.') !== false){
			$list = explode(".",$var);
			if(!isset($_SESSION[$list[0]]) || !is_array($_SESSION[$list[0]])){
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
		if(!$this->_string_safe_check($var)){
			return false;
		}
		if(strpos($var,'.') === false){
			$_SESSION[$var] = $val;
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
					if(isset($_SESSION[$value])){
						$_SESSION[$value] = array_merge($_SESSION[$value],$tmp);
					}else{
						$_SESSION[$value] = $tmp;
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

	private function _string_safe_check($info)
	{
		return !preg_match('/^[a-z0-9A-Z\.\_\-\x7f-\xff]+$/u',$info) ? false : true;
	}

	/**
	 * 取消session设定的内容，用$this->session->unassign('变量名') 替代写法 unset($_SESSION['变量名'])
	 * @参数 $var 要取消的变量
	 * @返回 true
	 * @更新时间 2016年07月25日
	**/
	final public function unassign($var)
	{
		if(!$this->_string_safe_check($var)){
			return false;
		}
		$info = $this->val($var);
		if(!$info && is_bool($info)){
			return true;
		}
		if(strpos($var,'.') === false){
			unset($_SESSION[$var]);
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
					$_SESSION[$value] = $tmp;
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
	 * 销毁SESSION
	**/
	public function destroy(){
		session_destroy();
		return true;
	}

	/**
	 * 销毁SESSION别名
	**/
	public function clean()
	{
		return $this->destroy();
	}

	public function session_newid($newid) {
	    session_commit();

	    // 使用新的会话 ID 开始会话
	    session_id($newid);
	    ini_set('session.use_strict_mode', 0);
	    session_start();
	}

	/**
	 * 报错提醒，直接中止运行
	 * @参数 $error 错误信息
	 * @参数 $errid 错误内码
	**/
	protected function error($error='',$errid='')
	{
		if(!$error) $error = "SESSION异常";
		$html = '<!DOCTYPE html>'."\n";
		$html.= '<html>'."\n";
		$html.= '<head>'."\n";
		$html.= '	<meta charset="utf-8" />'."\n";
		$html.= '	<title>SESSION错误</title>'."\n";
		$html.= '</head>'."\n";
		$html.= '<body style="padding:10px;font-size:14px;">'."\n";
		if($errid){
			$html .= '错误ID：'.$errid.'：';
		}
		$html.= $error."\n";
		$html.= '</body>'."\n";
		$html.= '</html>';
		exit($html);
	}
}
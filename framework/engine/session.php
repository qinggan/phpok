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
	protected $timeout = 600;
	protected $config;
	protected $sid = 'PHPSESSION';
	protected $sessid = '';

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
			$this->sessid($session_id);
		}else{
			$this->sessid();
		}
		if($this->config['domain']){
			session_set_cookie_params($this->timeout,'/',$this->config['domain']);
		}
		session_cache_expire(intval($this->timeout)/60);
		session_cache_limiter('nocache');
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
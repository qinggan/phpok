<?php
/*****************************************************************************************
	文件： {phpok}/engine/cache.php
	备注： 缓存基类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年11月16日 16时03分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cache
{
	protected $timeout = 1800;
	protected $status = true;
	protected $prefix = 'qinggan_';
	protected $keyfile = '';
	protected $folder = 'data/cache/';
	protected $key_id;
	protected $key_list;
	protected $debug = false;
	protected $time;
	//
	private $time_use = 0;
	private $time_tmp = 0;
	private $count = 0;
	
	public function __construct($config)
	{
		$this->status = $config['status'] ? true : false;
		$this->debug = $config['debug'] ? true : false;
		$this->timeout = $config['timeout'] ? $config['timeout'] : 1800;
		$this->prefix = $config["prefix"] ? $config["prefix"] : "qinggan_";
		$this->folder = $config['folder'] ? $config['folder'] : 'data/cache/';
		ksort($config);
		$this->key_id = md5($this->prefix."_".serialize($config));
		$this->key_list = array();
		if($this->status){
			$this->key_list = $this->get($this->key_id);
			if(!$this->key_list){
				$this->key_list = array();
			}
		}
		$this->time = time();
	}

	public function __destruct()
	{
		$this->save($this->key_id,$this->key_list);
		$this->expired();
		unset($this);
	}

	public function key_list($id,$value)
	{
		$this->key_list[$id] = $value;
	}

	public function status($status='')
	{
		if($status != ""){
			$this->status = $status ? true : false;
		}
		return $this->status;
	}

	public function timeout($time="")
	{
		if($time){
			$this->timeout = $time;
		}
		return $this->timeout;
	}

	public function prefix($prefix='')
	{
		if($prefix){
			$this->prefix = $prefix;
		}
		return $this->prefix;
	}

	public function save($id,$content)
	{
		if(!$id || !$content || !$this->status){
			return false;
		}
		$this->_time();
		$content = serialize($content);
		$file = $this->folder.$id.".php";
		file_put_contents($file,'<?php exit();?>'.$content);
		$this->_time();
		$this->_count();
		if($GLOBALS['app']->db){
			$this->key_list($id,$GLOBALS['app']->db->cache_index($id));
		}
		return true;
	}

	public function get($id)
	{
		if(!$id || !$this->status){
			return false;
		}
		if(!file_exists($this->folder.$id.'.php')){
			return false;
		}
		$this->_time();
		$ftime = filemtime($this->folder.$id.'.php');
		if(($ftime + $this->timeout) < $this->time){
			$this->delete($id);
			return false;
		}
		$this->_count();
		$content = file_get_contents($this->folder.$id.'.php');
		$this->_time();
		if(!$content || !trim($content)){
			return false;
		}
		$content = trim(substr($content,15));
		if(!$content){
			return false;
		}
		return unserialize($content);
	}

	//根据参数生成id
	public function id($var='')
	{
		if(!$this->status){
			return false;
		}
		if(!$var){
			$var = $this->time;
		}
		$count = func_num_args();
		if($count>1){
			$var = array($var);
			for($i=1;$i<$count;++$i){
				$var[] = func_get_arg($i);
			}
		}
		if(is_array($var) || is_object($var)){
			$var = serialize($var);
		}
		return md5($this->prefix."_".$var);
	}

	public function delete($id)
	{
		@unlink($this->folder.$id.'.php');
		if($this->key_list && $this->key_list){
			unset($this->key_list[$id]);
		}
		return true;
	}

	public function count()
	{
		return $this->count;
	}

	public function time()
	{
		return $this->time_use;
	}

	//根据索引删除
	public function delete_index($id)
	{
		foreach($this->key_list as $key=>$value){
			if(!$value || !is_array($value)){
				continue;
			}
			if(in_array($id,$value)){
				$this->delete($key);
			}
		}
	}

	public function clear()
	{
		$handle = opendir($this->folder);
		$array = array();
		while(false !== ($myfile = readdir($handle))){
			if(file_exists($this->folder.$myfile) && is_file($this->folder.$myfile)){
				$id = substr($myfile,0,-4);
				$this->delete($id);
			}
		}
		closedir($handle);
		return true;
	}

	public function expired()
	{
		$handle = opendir($this->folder);
		$array = array();
		$expire_time = $this->time - $this->timeout;
		while(false !== ($myfile = readdir($handle))){
			if(is_file($this->folder.$myfile) && filemtime($this->folder.$myfile) < $expire_time){
				$id = substr($myfile,0,-4);
				$this->delete($id);
			}
		}
		closedir($handle);
		return true;
	}

	public function error($error='')
	{
		$info = "执行错误【".$error."】";
		exit($this->ascii($info));
	}

	private function ascii($str='')
	{
		if(!$str) return false;
		$str = iconv("UTF-8", "UTF-16BE", $str);
		$output = "";
		for ($i = 0; $i < strlen($str); $i++,$i++){
			$code = ord($str{$i}) * 256 + ord($str{$i + 1});
			if($code < 128){
				$output .= chr($code);
			}elseif($code != 65279){
				$output .= "&#".$code.";";
			}
		}
		return $output;
	}

	public function debug()
	{
		if(!$this->debug){
			return false;
		}
		$html = '<table cellspacing="0" border="1" style="border:1px solid #000;width:100%;height:auto;margin:10px;">';
		$html.= '<tr><td colspan="2" style="text-align:center;line-height:160%;padding:3px;">KEY-Index:'.$this->key_id.'</td></tr>';
		$html.= '<tr>';
		$html.= '<th style="background:#EEE;color:#000;text-align:center;font-weight:bold;padding:3px;">ID</th>';
		$html.= '<th style="background:#EEE;color:#000;text-align:center;font-weight:bold;padding:3px;">KEY</th>';
		$html.= '</tr>';
		foreach($this->key_list as $key=>$value){
			$html.= '<tr>';
			$html.= '<td style="text-align:center;line-height:160%;padding:3px;">'.$key.'</td>';
			$html.= '<td style="text-align:left;padding:3px;">'.($value ? implode(", ",$value) : '-').'</td>';
			$html.= '</tr>';
		}
		$html.= '</table>';
		return $html;
	}


	//缓存运行计时器
	protected function _time()
	{
		$time = microtime(true);
		if($this->time_tmp){
			$this->time_use = round(($this->time_use + ($time - $this->time_tmp)),5);
			$this->time_tmp = 0;
		}else{
			$this->time_tmp = $time;
		}
	}

	//计数器
	protected function _count($val=1)
	{
		$this->count += $val;
	}


}
?>
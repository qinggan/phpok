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
	protected $folder = '../_cache/';
	protected $key_id;
	protected $key_list;
	protected $debug = false;
	protected $time;
	//
	private $keylist = array();
	private $timelist = array();
	private $md5list = array();
	private $time_use = 0;
	private $time_tmp = 0;
	private $count = 0;
	private $safecode = "<?php die('forbidden'); ?>\n";
	private $db;
	
	public function __construct($config)
	{
		$this->status = $config['status'] ? true : false;
		$this->debug = $config['debug'] ? true : false;
		$this->timeout = $config['timeout'] ? $config['timeout'] : 1800;
		$this->prefix = $config["prefix"] ? $config["prefix"] : "qinggan_";
		$this->folder = $config['folder'] ? $config['folder'] : '../_cache/';
		$this->time = time();
		$this->keylist_load();
	}

	public function __destruct()
	{
		$this->expired();
		$this->keylist_save();
	}

	public function key_list($id,$tbl='')
	{
		if(!$id || !$tbl){
			return false;
		}
		$list = array();
		if(is_string($tbl)){
			$tbl = trim($tbl);
			if(!$tbl){
				return false;
			}
			$list = explode(",",$tbl);
		}
		if(is_array($tbl)){
			$list = $tbl;
		}
		if(!$list || count($list)<1){
			return false;
		}
		foreach($list as $key=>$value){
			$this->keylist[$value][$id] = true;
			$this->md5list[$id][$value] = true;
		}
		$this->timelist[$id] = $this->time;
		return true;
	}

	public function status($status='')
	{
		if(is_bool($status) || is_numeric($status)){
			$this->status = $status ? true : false;
		}
		return $this->status;
	}

	public function close()
	{
		return $this->status(false);
	}

	public function open()
	{
		return $this->status(true);
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

	public function save($id,$content='')
	{
		if(!$id || $content === '' || !$this->status){
			return false;
		}
		$this->_time();
		$content = serialize($content);
		$file = $this->folder.$id.".php";
		file_put_contents($file,'<?php exit();?>'.$content);
		$this->_time();
		$this->_count();
		return true;
	}

	public function get($id,$onlycheck=false)
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
			$this->delete($id,false);
			return false;
		}
		$this->_count();
		$content = file_get_contents($this->folder.$id.'.php');
		$this->_time();
		if(!$content || !trim($content)){
			return false;
		}
		$content = trim(substr($content,15));
		if($content == ''){
			return false;
		}
		if($onlycheck){
			return true;
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
			sort($var);
			$var = serialize($var);
		}
		return md5($this->prefix."_".$var);
	}

	public function delete($id)
	{
		@unlink($this->folder.$id.'.php');
		if(!$this->md5list || !$this->md5list[$id]){
			return true;
		}
		foreach($this->md5list[$id] as $key=>$value){
			if($this->keylist && $this->keylist[$key][$id]){
				unset($this->keylist[$key][$id]);
			}
		}
		unset($this->md5list[$id]);
		if($this->timelist && $this->timelist[$id]){
			unset($this->timelist[$id]);
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
		if(!$this->keylist || !$this->keylist[$id]){
			return true;
		}
		foreach($this->keylist[$id] as $key=>$value){
			if($this->md5list && $this->md5list[$key][$id]){
				$this->delete($key);
			}
		}
		return true;
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
		$sql = "TRUNCATE ".tablename('cache');
		$GLOBALS['app']->db()->query($sql);
		return true;
	}

	public function expired()
	{
		if(!$this->timelist){
			return true;
		}
		$expire_time = $this->time - $this->timeout;
		foreach($this->timelist as $key=>$value){
			if($value < $expire_time){
				$this->delete($key);
			}
		}
		$sql = "DELETE FROM ".tablename('cache')." WHERE dateline<".$expire_time;
		$GLOBALS['app']->db()->query($sql);
		return true;
	}

	public function error($error='')
	{
		echo "执行错误【".$error."】";
		exit;
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

	protected function keylist_load()
	{
		if(!$this->status){
			return false;
		}
		$expire_time = $this->time - $this->timeout;
		$sql = "SELECT * FROM ".tablename("cache")." WHERE dateline>".$expire_time;
		$rslist = $GLOBALS['app']->db()->get_all($sql);
		if(!$rslist){
			return false;
		}
		foreach($rslist as $key=>$value){
			if(!isset($this->keylist[$value['tbl']])){
				$this->keylist[$value['tbl']] = array();
			}
			if(!isset($this->md5list[$value['code']])){
				$this->md5list[$value['code']] = array();
			}
			$this->keylist[$value['tbl']][$value['code']] = true;
			$this->md5list[$value['code']][$value['tbl']] = true;
			$this->timelist[$value['code']] = $value['dateline'];
		}
		return true;
	}

	protected function keylist_save()
	{
		if(!$this->status){
			return false;
		}
		$sql = "REPLACE INTO ".tablename('cache')."(tbl,code,dateline) VALUES";
		foreach($this->keylist as $key=>$value){
			foreach($value as $k=>$v){
				$time = $this->timelist[$k] ? $this->timelist[$k] : $this->time;
				$mylist[] = "('".$key."','".$k."','".$time."')";
			}
		}
		if($mylist){
			$sql .= implode(",",$mylist);
			$GLOBALS['app']->db()->query($sql);
		}
		return true;
	}
}
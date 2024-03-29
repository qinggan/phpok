<?php
/**
 * DB基类
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年12月04日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class db
{
	protected $database;
	protected $conn;
	protected $query;
	protected $preg_sql = '/^(UPDATE|DELETE|REPLACE|INSERT|TRUNCATE|DROP)/isU';
	protected $escapes = '';
	protected $special_replace;
	protected $debug = false;
	protected $cache; //接入外部缓存类
	protected $cache_id = ''; //当前的缓存ID，主要用于即时缓存
	protected $cache_status = false; //是否开启缓存
	protected $cache_data = array(); //缓存内容
	protected $tables = array();
	protected $host = '127.0.0.1';
	protected $user = 'root';
	protected $pass = '';
	protected $port = 3306;
	protected $socket = '';
	protected $charset = 'utf8';
	//保留字转义符，用于转义数据库保留字的转义
	protected $kec_left = '`';
	protected $kec_right = '`';


	//使用远程链接
	private $time = 0;
	private $count = 0;
	private $time_tmp = 0;
	private $_sqlist = array();
	private $_slowlist = array();
	private $slow_status = false;
	private $slow_time = 1;
	public $prefix = 'qinggan_';
	public $error_type = 'json';


	public function __construct($config=array())
	{
		$this->config($config);
	}

	//写入调试日志
	public function __destruct()
	{
		if($this->_slowlist){
			$file = DATA.'log/'.date("Ymd").'_slow.log.php';
			if(!is_file($file)){
				$content = "<?php die('forbidden'); ?>\n";
				file_put_contents($file,$content);
			}
			$info = '';
			foreach($this->_slowlist as $key=>$value){
				$info .= $value['dateline'];
				$info .= '|=phpok=|'.$value['time'];
				$info .= '|=phpok=|'.$value['sql']."\n";
			}
			file_put_contents($file,$info,FILE_APPEND);
		}
		if($this->_sqlist){
			$file = DATA.'log/'.date("Ymd").'_debug.log.php';
			if(!is_file($file)){
				$content = "<?php die('forbidden'); ?>\n";
				file_put_contents($file,$content);
			}
			$info = '';
			foreach($this->_sqlist as $key=>$value){
				$info .= $value['count'];
				$info .= '|=phpok=|'.$value['time'];
				$info .= '|=phpok=|'.$value['sql']."\n";
			}
			file_put_contents($file,$info,FILE_APPEND);
		}
	}

	public function config($config)
	{
		$this->database($config['data']);
		$this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
		$this->debug = $config['debug'] ? true : false;
		$this->host = $config['host'] ? $config['host'] : '127.0.0.1';
		$this->user = $config['user'] ? $config['user'] : 'root';
		$this->pass = $config['pass'] ? $config['pass'] : '';
		$this->port = $config['port'] ? $config['port'] : 3306;
		$this->socket = $config['socket'] ? $config['socket'] : '';
		$this->cache_status = $config['cache'] ? $config['cache'] : false;
		$this->slow_status = $config['slow'] ? $config['slow'] : false;
		$this->slow_time = $config['slow_time'] ? floatval($config['slow_time']) : 1;
		if($config['charset'] == 'utf8mb4'){
			$this->charset = 'utf8mb4';
		}
	}


	/**
	 * 设定保留字的转义符
	 * @参数 $left 左侧转义符
	 * @参数 $right 右侧转义符，留空使用与左侧一样的转义符
	**/
	public function kec($left='',$right='')
	{
		if($left){
			$this->kec_left = $left;
			$this->kec_right = $right ? $right : $left;
		}
		return true;
	}

	/**
	 * 数据库服务器
	 * @参数 $host 指定数据库服务器
	**/
	public function host($host='')
	{
		if($host){
			$this->host = $host;
		}
		return $this->host;
	}

	/**
	 * 数据库账号
	 * @参数 $user 账号名称
	**/
	public function user($user='')
	{
		if($user){
			$this->user = $user;
		}
		return $this->user;
	}

	/**
	 * 数据库密码
	 * @参数 $pass 密码
	**/
	public function pass($pass='')
	{
		if($pass){
			$this->pass = $pass;
		}
		return $this->pass;
	}

	/**
	 * 数据库端口
	 * @参数 $port 端口，必须是数字
	**/
	public function port($port='')
	{
		if($port && is_numeric($port)){
			$this->port = $port;
		}
		return $this->port;
	}

	/**
	 * Socket 套接字，使应用程序能够读写与收发通讯协定（protocol）与资料的程序
	 * @参数 $socket 指定 socket 文件
	**/
	public function socket($socket='')
	{
		if($socket){
			$this->socket = $socket;
		}
		return $this->socket;
	}

	/**
	 * 指定数据库
	 * @参数 $database 数据库名称
	**/
	public function database($database='')
	{
		if($database){
			$this->database = $database;
		}
		return $this->database;
	}

	/**
	 * 表前缀
	 * @参数 $val 设置表前缀
	**/
	public function prefix($val='')
	{
		if($val){
			$this->prefix = $val;
		}
		return $this->prefix;
	}

	/**
	 * 判断是否有连接到数据库
	**/
	public function status()
	{
		if(!$this->conn){
			return false;
		}
		return true;
	}

	public function sql_time()
	{
		return $this->time;
	}

	public function sql_count()
	{
		return $this->count;
	}

	/**
	 * 报错类型
	 * @参数 $type 仅支持 exit 和 json 两个字串
	**/
	public function error_type($type='')
	{
		if($type){
			$this->error_type = $type;
		}
		return $this->error_type;
	}

	/**
	 * 自定义错误
	 * @参数 $error 错误信息
	 * @参数 $errid 错误ID
	**/
	public function error($error='',$errid='')
	{
		if($this->debug){
			$info = P_Lang('SQL执行错误【ID：{errid}，错误信息是：{error}】',array('errid'=>$errid,'error'=>$error));
		}else{
			$info = P_Lang('SQL执行错误，请检查');
			$this->_log('SQL错误，'.$errid.': '.$error);
		}
		if($this->error_type == 'json'){
			$array = array('status'=>false,'info'=>$info);
			exit(json_encode($array,JSON_UNESCAPED_UNICODE));
		}else{
			echo $info;
			exit;
		}
	}


	/**
	 * 调试，2018年11月24日后不再支持即时输出，改为写日志
	 * @参数 $sql SQL语句
	 * @参数 $time 当前SQL运行时间
	**/
	public function debug($sql='',$time=0)
	{
		if(isset($sql) && is_bool($sql)){
			$this->debug = $sql;
			return $this->debug;
		}
		if($this->slow_status){
			$this->_slow($sql,$time);
		}
		if(!$this->debug){
			return true;
		}
		if($sql && trim($sql)){
			$sql = trim($sql);
			$sqlid = 'phpok'.md5($sql);
			if($this->_sqlist && $this->_sqlist[$sqlid]){
				$this->_sqlist[$sqlid]['count']++;
				$this->_sqlist[$sqlid]['time'] = round(($this->_sqlist[$sqlid]['time']+$time),5);
			}else{
				$this->_sqlist[$sqlid] = array('sql'=>$sql,'count'=>1,'time'=>$time);
			}
			return true;
		}
		$html  = '<table class="table table-bordered">';
		$html .= '<tr>';
		$html .= '<th>Count</th>';
		$html .= '<th>Time</th>';
		$html .= '<th>SQL</th>';
		$html .= '</tr>';
		foreach($this->_sqlist as $key=>$value){
			$html .= '<tr>';
			$html .= '<td>'.$value['count'].'</td>';
			$html .= '<td>'.$value['time'].'</td>';
			$html .= '<td class="text-start">'.$value['sql'].'</td>';
			$html .= '</tr>';
		}
		$html.= '</table>';
		return $html;
	}


	public function slowlist()
	{
		if(!$this->_slowlist){
			return false;
		}
		return $this->_slowlist;
	}

	public function conn()
	{
		return $this->conn;
	}

	public function cache_set($id='')
	{
		if($id){
			$this->cache_id = $id;
		}
		return $this->cache_id;
	}

	public function cache_update($sql){
		if(!preg_match($this->preg_sql,$sql)){
			preg_match_all('/(FROM|JOIN)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
			$tbl = $list[2] ? $list[2] : false;
			if(!$tbl){
				return false;
			}
			if($this->cache_id && $tbl){
				$GLOBALS['app']->cache()->key_list($this->cache_id,$tbl);
			}
			return true;
		}
		preg_match_all('/(FROM|JOIN|UPDATE|INTO|TRUNCATE|DROP)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
		$tbl = $list[2] ? $list[2] : false;
		if(!$tbl){
			return false;
		}
		foreach($tbl as $key=>$value){
			$GLOBALS['app']->cache()->delete_index($value);
		}
		return true;
	}

	


	/**
	 * 写入操作
	 * @参数 $sql 要插入的SQL或数组
	 * @参数 $tbl 数据表名称
	 * @参数 $type 插放入方式，仅限 $sql 为数组时有效，当为布尔值时表示是否前缀，此时type默认为 insert
	 * @参数 $prefix 是否检查前缀
	**/
	public function insert($sql,$tbl='',$type='insert',$prefix=true)
	{
		if(is_array($sql) && $tbl){
			if(is_bool($type)){
				$prefix = $type;
				$type = 'insert';
			}
			return $this->insert_array($sql,$tbl,$type,$prefix);
		}
		$this->query($sql);
		return $this->insert_id();
	}

	/**
	 * 数组写入操作
	 * @参数 $data 数组
	 * @参数 $tbl 表名
	 * @参数 $type 写入方式
	 * @参数 $prefix 是否检查前缀
	**/
	public function insert_array($data,$tbl,$type="insert",$prefix=true)
	{
		if(!$tbl || !$data || !is_array($data)){
			return false;
		}
		if($prefix && substr($tbl,0,strlen($this->prefix)) != $this->prefix){
			$tbl = $this->prefix.$tbl;
		}
		$sql = $this->_insert_array($data,$tbl,$type);
		return $this->insert($sql);
	}

	/**
	 * 删除表数据操作
	 * @参数 $table 表名
	 * @参数 $condition 查询条件
	 * @参数 $prefix 是否检查前缀
	**/
	public function delete($table,$condition='',$prefix=true)
	{
		if(!$condition || !$table){
			return false;
		}
		if(is_array($condition)){
			$sql_fields = array();
			foreach($condition as $key=>$value){
				$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."' ";
			}
			$condition = implode(" AND ",$sql_fields);
			if(!$condition){
				return false;
			}
		}
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "DELETE FROM ".$table." WHERE ".$condition;
		return $this->query($sql);
	}

	public function one($table,$condition="",$prefix=true)
	{
		if(!$table){
			return false;
		}
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "SELECT * FROM ".$table." ";
		if($condition && is_array($condition)){
			$sql_fields = array();
			foreach($condition as $key=>$value){
				$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."' ";
			}
			$condition = implode(" AND ",$sql_fields);
		}
		if($condition){
			$sql .= " WHERE ".$condition;
		}
		return $this->get_one($sql);
	}

	/**
	 * 更新操作
	 * @参数 $sql SQL语句或数组
	 * @参数 $tbl 表格
	 * @参数 $condition 更新条件
	 * @参数 $prefix 是否检查前缀
	**/
	public function update($sql,$tbl='',$condition='',$prefix=true)
	{
		if(is_array($sql) && $tbl && $condition){
			return $this->update_array($sql,$tbl,$condition,$prefix);
		}
		return $this->query($sql);
	}

	/**
	 * 更新数据
	 * @参数 $data SQL语句或数组
	 * @参数 $tbl 表格
	 * @参数 $condition 更新条件
	 * @参数 $prefix 是否检查前缀
	**/
	public function update_array($data,$table,$condition,$prefix=true)
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition)){
			return false;
		}
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "UPDATE ".$table." SET ";
		$sql_fields = array();
		foreach($data as $key=>$value){
			$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition as $key=>$value){
			$sql_fields[] = $this->kec_left.$key.$this->kec_right."='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		return $this->query($sql);
	}

	//计数器
	protected function _count($val=1)
	{
		$this->count += $val;
	}
	
	/**
	 * 格式化数组成SQL
	 * @参数 $data 数组
	 * @参数 $table 表名
	 * @参数 $type 类型，仅支持 insert，replace 三种
	**/
	protected function _insert_array($data,$table,$type='insert')
	{
		$sql = (strtolower($type) == 'insert' ? "INSERT" : "REPLACE")." INTO ".$table." ";
		$sql_fields = array();
		$sql_val = array();
		foreach($data as $key=>$value){
			$sql_fields[] = $this->kec_left.$key.$this->kec_right;
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
		return $sql;
	}
	
	//缓存运行计时器
	protected function _time()
	{
		$time = microtime(true);
		if($this->time_tmp){
			$tmptime = round(($time - $this->time_tmp),5);
			$this->time = round(($this->time + $tmptime),5);
			$this->time_tmp = 0;
			return $tmptime;
		}else{
			$this->time_tmp = $time;
		}
	}



	private function _log($info='')
	{
		global $app;
		if(!$info){
			$info = '没有提示内容';
		}
		if(is_array($info) || is_object($info)){
			$info = print_r($info,true);
		}
		$info = trim($info);
		$date = date("Ymd",$app->time);
		if(!file_exists($app->dir_data.'log/log'.$date.'.php')){
			file_put_contents($app->dir_data.'log/log'.$date.'.php',"<?php exit();?>\n");
		}
		$handle = fopen($app->dir_data.'log/log'.$date.'.php','ab');
		$info2 = '---start---Time:'.date("H:i:s",$app->time).'---------------------'."\n";
		$info2.= 'APP_ID: '.$app->app_id."\n";
		$info2.= 'CTRL_ID: '.$app->ctrl."\n";
		$info2.= 'FUNC_ID: '.$app->func."\n";
		$info2.= 'INFO:'."\n";
		$info2.= $info."\n";
		$info2.= '---end---'."\n";
		fwrite($handle,$info2);
		fclose($handle);
		return true;
	}
	
	private function _slow($sql,$time)
	{
		if($time < $this->slow_time){
			return true;
		}
		$sql = trim($sql);
		$this->_slowlist[] = array('sql'=>$sql,'dateline'=>time(),'time'=>$time);
		return true;
	}

}
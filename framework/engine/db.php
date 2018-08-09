<?php
/**
 * DB基类
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
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
	protected $preg_sql = '/^(UPDATE|DELETE|REPLACE|INSERT)/isU';
	protected $escapes = '';
	protected $special_replace;
	protected $debug = false;
	protected $cache = false;
	protected $cache_false = array();
	protected $cache_data = array();
	protected $cache_first = array();
	protected $tbl_list = array();
	protected $host = '127.0.0.1';
	protected $user = 'root';
	protected $pass = '';
	protected $port = 3306;
	protected $socket = '';
	//保留字转义符，用于转义数据库保留字的转义
	protected $kec_left = '`';
	protected $kec_right = '`';

	//使用远程链接
	protected $client_url = '';
	
	private $time = 0;
	private $count = 0;
	private $time_tmp = 0;
	private $_sqlist = array();
	public $prefix = 'qinggan_';
	public $error_type = 'exit';
	
	public function __construct($config=array())
	{
		$this->config($config);
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
		$this->cache = $config['cache'] ? $config['cache'] : false;
		if($config['client_url']){
			$this->client_url = $config['client_url'];
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
	 * 判断是否有连接到数据库
	**/
	public function status()
	{
		if(!$this->conn){
			return false;
		}
		return true;
	}

	//收集表名称
	public function cache_index($id='')
	{
		if(!$id){
			return false;
		}
		$info = $this->tbl_list[$id];
		unset($this->tbl_list[$id]);
		return $info;
	}

	//重置表名称收集
	public function cache_set($id)
	{
		if(!$id){
			return false;
		}
		$this->tbl_list[$id] = array();
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
		}
		if($this->error_type == 'json'){
			$array = array('status'=>'error','content'=>$info);
			exit(json_encode($array));
		}else{
			echo $info;
			exit;
		}
	}

	/**
	 * 输出调试
	 * @参数 $sql SQL语句
	 * @参数 $time 当前SQL运行时间
	**/
	public function debug($sql='',$time=0)
	{
		if(isset($sql) && is_bool($sql)){
			$this->debug = $sql;
			return $this->debug;
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
		if(!$this->debug){
			return true;
		}
		$html  = '<style type="text/css">'."\n";
		$html .= 'table.debug{border-collapse:collapse;border:1px solid #000;width:100%;height:auto;margin:10px auto;background:#fff;padding:0;font-size:12px;}'."\n";
		$html .= 'table.debug tr th{background:#ccc;color:#000;text-align:center;font-weight:bold;padding:3px;border:1px solid #000;}'."\n";
		$html .= 'table.debug tr td{text-align:center;padding:3px;background:#fff;color:#000;border:1px solid #000;}'."\n";
		$html .= 'table.debug tr td:first-child{text-align:left;table-layout: fixed;word-break: break-all; word-wrap: break-word}'."\n";
		$html .= 'table.debug tr:hover td{background:#efefef;}'."\n";
		$html .= '</style>'."\n";
		$html .= '<table class="debug">'."\n";
		$html .= '<tr>'."\n";
		$html .= '	<th>SQL</th>'."\n";
		$html .= '	<th>Count</th>'."\n";
		$html .= '	<th>Time</th>'."\n";
		$html .= '</tr>'."\n";
		foreach($this->_sqlist as $key=>$value){
			$html .= '<tr>'."\n";
			$html .= '	<td>'.$value['sql'].'</td>'."\n";
			$html .= '	<td>'.$value['count'].'</td>'."\n";
			$html .= '	<td>'.$value['time'].'</td>'."\n";
			$html .= '</tr>'."\n";
		}
		$html .= '</table>';
		return $html;
	}

	public function conn()
	{
		return $this->conn;
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

	//计数器
	protected function _count($val=1)
	{
		$this->count += $val;
	}


	//通过正则获取表
	protected function cache_sql($sql)
	{
		preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
		$tbl = $list[2] ? $list[2] : false;
		if(!$tbl){
			return true;
		}
		foreach($this->tbl_list as $key=>$value){
			if(!$value){
				$value = $tbl;
			}else{
				foreach($tbl as $k=>$v){
					$value[] = $v;
				}
			}
			$value = array_unique($value);
			$this->tbl_list[$key] = $value;
		}
	}

	protected function cache_update($sql)
	{
		if(preg_match($this->preg_sql,$sql)){
			preg_match_all('/(FROM|JOIN|UPDATE|INTO)\s+([a-zA-Z0-9\_\.\-]+)(\s|\()+/isU',$sql,$list);
			$tbl = $list[2] ? $list[2] : false;
			if($tbl && $GLOBALS['app']){
				foreach($tbl as $key=>$value){
					$GLOBALS['app']->cache->delete_index($value);
				}
			}
		}
	}

	protected function cache_false_save($sql)
	{
		if(!$this->cache){
			return true;
		}
		$id = 'sql'.md5($sql);
		$this->cache_false[$id] = true;
		return true;
	}

	protected function cache_false($sql)
	{
		if(!$this->cache){
			return false;
		}
		$id = 'sql'.md5($sql);
		if(isset($this->cache_false[$id])){
			return true;
		}
		return false;
	}

	/**
	 * 检测是否需要即时缓存
	 * @参数 $sql 要检测的SQL语句
	**/
	protected function cache_need($sql)
	{
		if(!$this->cache){
			return false;
		}
		$id = 'sql'.md5($sql);
		if($this->cache_first[$id]){
			return true;
		}
		return false;
	}

	protected function cache_first($sql)
	{
		if(!$this->cache){
			return true;
		}
		$id = 'sql'.md5($sql);
		$this->cache_first[$id] = true;
		return true;
	}

	/**
	 * 需要即时缓存的数据
	 * @参数 $sql SQL语句
	 * @参数 $data 返回的结果集
	**/
	public function cache_save($sql,$data)
	{
		if(!$this->cache){
			return true;
		}
		$id = "sql".md5($sql);
		$this->cache_data[$id] = $data;
		return true;
	}

	public function cache_get($sql)
	{
		if(!$this->cache){
			return false;
		}
		$id = "sql".md5($sql);
		if(isset($this->cache_data[$id])){
			return $this->cache_data[$id];
		}
		return false;
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
}
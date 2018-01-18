<?php
/**
 * MySQL读取引挈
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年09月26日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class db_mysqli extends db
{
	private $host = '127.0.0.1';
	private $user = 'root';
	private $pass = '';
	private $port = 3306;
	private $socket = '';
	private $type = MYSQLI_ASSOC;

	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->config($config);
	}

	/**
	 * 初始数据库连接参数，用于更换数据库服务器使用
	 * @参数 $config 数组
	**/
	public function config($config)
	{
		parent::config($config);
		$this->host = $config['host'] ? $config['host'] : '127.0.0.1';
		$this->user = $config['user'] ? $config['user'] : 'root';
		$this->pass = $config['pass'] ? $config['pass'] : '';
		$this->port = $config['port'] ? $config['port'] : 3306;
		$this->socket = $config['socket'] ? $config['socket'] : '';
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
	 * 类型设置
	 * @参数 $type ，为 num 时使用 MYSQLI_NUM ，返之为 MYSQLI_ASSOC
	**/
	public function type($type='')
	{
		if($type && ($type == 'num' || $type == MYSQLI_NUM)){
			$this->type = MYSQLI_NUM;
		}else{
			$this->type = MYSQLI_ASSOC;
		}
		return $this->type;
	}

	/**
	 * 数据库连接
	**/
	public function connect()
	{
		$this->_time();
		$this->conn = mysqli_init();
		@mysqli_real_connect($this->conn,$this->host,$this->user,$this->pass,$this->database,$this->port,$this->socket,MYSQLI_CLIENT_COMPRESS);
		if(mysqli_connect_errno($this->conn)){
			$this->error(mysqli_connect_error($this->conn),mysqli_connect_errno($this->conn));
		}
		if(mysqli_error($this->conn)){
			$this->error(mysqli_error($this->conn),mysqli_errno($this->conn));
		}
		mysqli_query($this->conn,"SET NAMES 'utf8'");
		mysqli_query($this->conn,"SET sql_mode=''");
		$this->_time();
		return $this->conn;
	}

	//检测链接是否存在
	private function check_connect()
	{
		if(!$this->conn || !is_object($this->conn)){
			$this->connect();
		}else{
			if(!mysqli_ping($this->conn)){
				mysqli_close($this->conn);
				$this->connect();
			}
		}
		if(!$this->conn || !is_object($this->conn)){
			$this->error('数据库连接失败');
		}
	}

	public function __destruct()
	{
		if($this->conn && is_object($this->conn)){
			mysqli_close($this->conn);
		}
	}

	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? MYSQLI_NUM : MYSQLI_ASSOC;
			$this->type = $value;
		}else{
			$this->$name = $value;
		}
	}

	public function query($sql,$loadcache=true)
	{
		if($loadcache){
			$this->cache_sql($sql);
		}
		$this->check_connect();
		$this->_time();
		$this->query = mysqli_query($this->conn,$sql);
		if($loadcache){
			$this->cache_update($sql);
		}
		$tmptime = $this->_time();
		$this->_count();
		if($this->debug){
			$this->debug($sql,$tmptime);
		}
		if(mysqli_error($this->conn)){
			$this->error(mysqli_error($this->conn).', '.$sql,mysqli_errno($this->conn));
		}
		return $this->query;
	}

	public function get_all($sql='',$primary="")
	{
		if($sql){
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = false;
		while($rows = mysqli_fetch_array($this->query,$this->type)){
			if($primary){
				$rs[$rows[$primary]] = $rows;
			}else{
				$rs[] = $rows;
			}
		}
		mysqli_free_result($this->query);
		$this->_time();
		return $rs;
	}

	public function get_one($sql="")
	{
		if($sql){
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = mysqli_fetch_array($this->query,$this->type);
		mysqli_free_result($this->query);
		$this->_time();
		return $rs;
	}

	//返回最后插入的ID
	public function insert_id()
	{
		$this->check_connect();
		return mysqli_insert_id($this->conn);
	}

	//写入操作
	public function insert($sql,$tbl='',$type='insert')
	{
		if(is_array($sql) && $tbl){
			return $this->insert_array($sql,$tbl,$type);
		}
		$this->query($sql);
		return $this->insert_id();
	}

	public function insert_array($data,$tbl,$type="insert")
	{
		if(!$tbl || !$data || !is_array($data)){
			return false;
		}
		if(substr($tbl,0,strlen($this->prefix)) != $this->prefix){
			$tbl = $this->prefix.$tbl;
		}
		$type = strtolower($type);
		$sql = $type == 'insert' ? "INSERT" : "REPLACE";
		$sql.= " INTO ".$tbl." ";
		$sql_fields = array();
		$sql_val = array();
		foreach($data as $key=>$value){
			$sql_fields[] = "`".$key."`";
			$sql_val[] = "'".$value."'";
		}
		$sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
		return $this->insert($sql);
	}

	//更新操作
	public function update($data,$tbl='',$condition='')
	{
		if(is_array($data) && $tbl && $condition){
			return $this->update_array($data,$tbl,$condition);
		}
		return $this->query($data);
	}

	//删除操作
	public function delete($table,$condition='')
	{
		if(!$condition || !$table){
			return false;
		}
		if(is_array($condition)){
			$sql_fields = array();
			foreach($condition as $key=>$value){
				$sql_fields[] = "`".$key."`='".$value."' ";
			}
			$condition = implode(" AND ",$sql_fields);
			if(!$condition){
				return false;
			}
		}
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "DELETE FROM ".$table." WHERE ".$condition;
		return $this->query($sql);
	}

	//更新数据
	public function update_array($data='',$table='',$condition='')
	{
		if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition)){
			return false;
		}
		if(substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "UPDATE ".$table." SET ";
		$sql_fields = array();
		foreach($data as $key=>$value){
			$sql_fields[] = "`".$key."`='".$value."'";
		}
		$sql.= implode(",",$sql_fields);
		$sql_fields = array();
		foreach($condition as $key=>$value){
			$sql_fields[] = "`".$key."`='".$value."' ";
		}
		$sql .= " WHERE ".implode(" AND ",$sql_fields);
		return $this->query($sql);
	}

	/**
	 * 计算数量
	**/
	public function count($sql="",$is_count=true)
	{
		if($sql && is_string($sql) && $is_count){
			$this->set('type','num');
			$rs = $this->get_one($sql);
			$this->set('type','assoc');
			return $rs[0];
		}else{
			if($sql && is_string($sql)){
				$this->query($sql);
			}
			if($this->query){
				return mysqli_num_rows($this->query);
			}
		}
		return false;
	}

	public function num_fields($sql="")
	{
		if($sql){
			$this->query($sql);
		}
		if($this->query){
			return mysqli_num_fields($this->query);
		}
		return false;
	}

	/**
	 * 显示表字段，仅限字段名，没有字段属性
	 * @参数 $table 表名
	 * @参数 $check_prefix 是否检查数据表前缀
	 * @返回 无值或表字段数组
	**/
	public function list_fields($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
		if(!$rs){
			return false;
		}
		foreach($rs as $key=>$value){
			$rslist[] = $value["Field"];
		}
		return $rslist;
	}

	/**
	 * 取得明细的字段管理
	**/
	public function list_fields_more($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW FULL COLUMNS FROM ".$table);
		if(!$rs){
			return false;
		}
		foreach($rs as $key=>$value){
			$tmp = array();
			foreach($value as $k=>$v){
				$tmp[strtolower($k)] = $v;
			}
			$rslist[$value["Field"]] = $tmp;
		}
		return $rslist;
	}

	/**
	 * 显示数据库表
	**/
	public function list_tables()
	{
		$list = $this->get_all("SHOW TABLES");
		if(!$list){
			return false;
		}
		$rslist = array();
		$id = 'Tables_in_'.$this->database;
		foreach($list as $key=>$value){
			$rslist[] = $value[$id];
		}
		return $rslist;
	}

	public function escape_string($char)
	{
		if(!$char){
			return false;
		}
		$this->check_connect();
		return mysqli_escape_string($this->conn,$char);
	}


	/**
	 * 取得数据库服务版本
	 * @参数 $type 支持server和client两种类型
	**/
	public function version($type="server")
	{
		if($type == 'server'){
			return mysqli_get_server_info($this->conn);
		}else{
			return mysqli_get_client_info($this->conn);
		}
	}

	/**
	 * 存储过程执行，返回多个结果集
	 * @参数 $sql 要执行的存储过程
	 * @参数 $out 返回的结果集变量处理，多个变量用英文逗号隔开
	**/
	public function call_more($sql,$out='')
	{
		mysqli_multi_query($this->conn,$sql);
		if(!$out){
			$out = array();
		}
		if(is_string($out)){
			$out = explode(",",$out);
		}
		$data = array();
		$i=0;
		do{
			$id = $out[$i] ? $out[$i] : $i;
			if($result = mysqli_store_result($this->conn)){
				while($row = mysqli_fetch_assoc($result)){
					$data[$id][] = $row;
				}
				mysqli_free_result($result);
			}
			if (!mysqli_more_results($this->conn)){
				break;
			}
			$i++;
		}while(mysqli_next_result($this->conn));
		if($data && count($data)>0){
			return $data;
		}
		return false;
	}

	/**
	 * 存储过程执行，返回一条结果集
	 * @参数 $sql 要执行的存储过程的语句
	 * @参数 $out 返回的变量，如果out为空，直接返回
	**/
	public function call($sql,$out='')
	{
		$this->query($sql,false);
		if($out && !is_bool($out)){
			$tmp = array();
			if(is_string($out)){
				$out = explode(",",$out);
			}
			foreach($out as $key=>$value){
				$value = str_replace('@','',$value);
				$tmp[] = '@'.$value;
			}
			$sql = "SELECT ".implode(",",$tmp);
			$this->query($sql,false);
			return $this->get_one();
		}
		return $this->get_one();
	}

	/**
	 * 存储过程执行，返回一个结果集的多条
	 * @参数 $sql 要执行的SQL
	 * @参数 $out 返回值，多个值用英文逗号隔开
	**/
	public function call_list($sql,$out='')
	{
		$this->query($sql,false);
		if($out && is_string($out)){
			$tmp = array();
			if(is_string($out)){
				$out = explode(",",$out);
			}
			foreach($out as $key=>$value){
				$value = str_replace('@','',$value);
				$tmp[] = '@'.$value;
			}
			$sql = "SELECT ".implode(",",$tmp);
			$this->query($sql,false);
			return $this->get_all();
		}
		return $this->get_all();
	}

	/**
	 * 创建主表操作
	 * @参数 $tblname 表名称
	 * @参数 $pri_id 主键ID
	 * @参数 $note 表摘要
	 * @参数 $engine 引挈，默认是 MYISAM
	**/
	public function create_table_main($tblname,$pri_id='',$note='',$engine='')
	{
		if(!$engine){
			$engine = 'MYISAM';
		}
		if(!$pri_id){
			$pri_id = 'id';
		}
		$sql  = "CREATE TABLE IF NOT EXISTS `".$tblname."`(`".$pri_id."` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',";
		$sql .= "PRIMARY KEY (`".$pri_id."`) ) ";
		$sql .= "ENGINE=".$engine." DEFAULT CHARACTER SET utf8 COMMENT='".$note."' AUTO_INCREMENT=1;";
		return $this->query($sql);
	}

	/**
	 * 增加或修改表字段
	 * @参数 $tblname 表名称，带前缀
	 * @参数 $data 要更新的表信息，包括字段有：id 表ID，type类型，length长度，unsigned是否无符号，notnull是否非空，default默认值，comment备注
	 * @参数 $old 旧表字段ID，如果检查不能，表示新增
	**/
	public function update_table_fields($tblname,$data,$old='')
	{
		if(!$tblname || !$data || !is_array($data)){
			return false;
		}
		$check = $this->list_fields_more($tblname,false);
		if(!$check){
			return false;
		}
		if(!$oldid){
			$old = $data['id'];
		}
		if(!$data['type']){
			$data['type'] = 'varchar';
		}
		$sql = "ALTER TABLE `".$tblname."` ";
		if($check[$old]){
			$sql .= "CHANGE `".$old."` `".$data['id']."` ";
		}else{
			$sql .= "ADD `".$data['id']."` ";
		}
		$sql .= strtoupper($data['type']);
		if($data['type'] == 'varchar'){
			$sql .= "(255)";
		}else{
			if($data['length']){
				$sql.= "(".$data['length'].")";
			}
		}
		$sql .= " ";
		if($data['unsigned']){
			$sql .= "UNSIGNED ";
		}
		if($data['notnull']){
			$sql .= "NOT NULL ";
			if($data['default'] == ''){
				$sql .= "DEFAULT '' ";
			}
		}else{
			$sql .= "NULL ";
		}
		if($data['default'] != ''){
			$sql .= "DEFAULT '".$data['default']."' ";
		}
		if($data['comment']){
			$sql .= "COMMENT '".$data['comment']."' ";
		}
		return $this->query($sql);
	}

	/**
	 * 创建更新索引
	 * @参数 $tblname 表名
	 * @参数 $indexname 索引名，也可以是字段名
	 * @参数 $fields 字段名，支持字段数组，留空使用索引名
	 * @参数 $old 删除旧索引
	**/
	public function update_table_index($tblname,$indexname,$fields='',$old='')
	{
		$sql = "ALTER TABLE ".$tblname." ";
		if($old){
			$sql .= "DROP INDEX `".$old."`,";
		}
		if(!$fields){
			$fields = $indexname;
		}
		if(is_array($fields)){
			$fields = implode("`,`",$fields);
		}
		$sql .= "ADD INDEX `".$indexname."`(`".$fields."`)";
		return $this->query($sql);
	}

	/**
	 * 删除表字段
	 * @参数 $tblname 表名称
	 * @参数 $id 要删除的字段
	**/
	public function delete_table_fields($tblname,$id)
	{
		$sql = "ALTER TABLE ".$tblname." DROP `".$id."`";
		return $this->query($sql);
	}

	/**
	 * 删除表操作
	 * @参数 $table 表名称，要求带前缀
	 * @参数 $check_prefix 是否加前缀
	**/
	public function delete_table($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$sql = "DROP TABLE IF EXISTS `".$table."`";
		return $this->query($sql);
	}
}
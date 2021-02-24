<?php
/**
 * 通过PDO连接MYSQL数据库
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2017年12月02日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class db_pdo_mysql extends db
{
	private $type = PDO::FETCH_ASSOC;

	public function __construct($config=array())
	{
		parent::__construct($config);
		$this->kec("`","`");
	}

	public function type($type='')
	{
		if($type && $type == 'num'){
			$this->type = PDO::FETCH_NUM;
		}else{
			$this->type = PDO::FETCH_ASSOC;
		}
		return $this->type;
	}

	/**
	 * 数据库链接
	**/
	public function connect()
	{
		$this->_time();
		$dsn = 'mysql:host='.$this->host.';dbname='.$this->database.';port='.$this->port;
		if($this->socket){
			$dsn .= ';unix_socket='.$this->socket;
		}
		try{
			$this->conn = new PDO($dsn,$this->user,$this->pass);
		} catch(PDOException $e){
			$this->error('数据库连接失败，错误信息：'.$e->getMessage());
		}
		$this->conn->exec("SET NAMES '".$this->charset."'");
		$this->conn->exec("SET sql_mode=''");
		$this->conn->setAttribute(PDO::ATTR_CASE,PDO::CASE_NATURAL);
		$this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
		$this->_time();
		return true;
	}

	/**
	 * 检测连接
	**/
	private function check_connect()
	{
		if(!$this->conn || !is_object($this->conn)){
			$this->connect();
		}else{
			$status = $this->conn->getAttribute(PDO::ATTR_SERVER_INFO);
			if($status == 'MySQL server has gone away'){
				$this->connect();
			}
		}
		if(!$this->conn || !is_object($this->conn)){
			$this->error('数据库连接失败');
		}
		return true;
	}

	/**
	 * 结束连接
	**/
	public function __destruct()
	{
		if($this->conn && is_object($this->conn)){
			$this->conn = null;
		}
	}

	//定义基本的变量信息
	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
			$this->type = $value;
		}else{
			$this->$name = $value;
		}
	}

	public function query($sql,$loadcache=true)
	{
		$this->check_connect();
		$this->_time();
		$this->query = $this->conn->query($sql);
		$tmptime = $this->_time();
		$this->_count();
		$this->debug($sql,$tmptime);
		$this->cache_update($sql);
		return $this->query;
	}

	/**
	 * 获取列表数据
	 * @参数 $sql 要查询的SQL
	 * @参数 $primary 绑定主键
	**/
	public function get_all($sql='',$primary='',$is_cache=true)
	{
		if($sql){
			if((is_bool($primary) && $primary) || $is_cache){
				$info = $this->cache_get($sql);
				if($info){
					if($info['_phpok_query_false']){
						return false;
					}
					if(!is_bool($primary) && $primary){
						$tlist = array();
						foreach($info as $key=>$value){
							$tlist[$value[$primary]] = $value;
						}
						$info = $tlist;
						unset($tlist);
					}
					return $info;
				}
			}
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = array();
		while($rows = $this->query->fetch($this->type)){
			$rs[] = $rows;
		}
		$this->query->closeCursor();
		$this->_time();
		if(!$rs || count($rs)<1){
			$this->cache_false($sql);
			return false;
		}
		$this->cache_save($sql,$rs);
		if($primary && !is_bool($primary)){
			$tlist = array();
			foreach($rs as $key=>$value){
				$tlist[$value[$primary]] = $value;
			}
			$rs = $tlist;
			unset($tlist);
		}
		return $rs;
	}

	/**
	 * 获取一条数据
	 * @参数 $sql 要执行的SQL
	**/
	public function get_one($sql="",$is_cache=true)
	{
		if($sql){
			if($is_cache){
				$info = $this->cache_get($sql);
				if($info){
					if($info['_phpok_query_false']){
						return false;
					}
					return $info;
				}
			}
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = $this->query->fetch($this->type);
		$this->query->closeCursor();
		$this->_time();
		if(!$rs){
			$this->cache_false($sql);
			return false;
		}
		$this->cache_save($sql,$rs);
		return $rs;
	}

	/**
	 * 返回最后插入的ID
	**/
	public function insert_id()
	{
		$this->check_connect();
		return $this->conn->lastInsertId();
	}


	/**
	 * 返回行数
	 * @参数 $sql 要执行的SQL语句
	 * @参数 $is_count 是否计算数量，仅限 sql 中使用 count() 时有效
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
				return $this->query->rowCount();
			}
		}
		return false;
	}

	/**
	 * 返回被筛选出来的字段数目
	 * @参数 $sql 要执行的SQL语句
	**/
	public function num_fields($sql="")
	{
		if($sql){
			$this->query($sql);
		}
		if($this->query){
			return $this->query->columnCount();
		}
		return false;
	}

	/**
	 * 显示表字段，仅限字段名，没有字段属性
	 * @参数 $table 表名
	 * @参数 $prefix 是否检查数据表前缀
	 * @返回 无值或表字段数组
	**/
	public function list_fields($table,$prefix=true)
	{
		if($prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
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
	 * @参数 $table 表名
	 * @参数 $check_prefix 是否检查数据表前缀
	**/
	public function list_fields_more($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW COLUMNS FROM ".$table);
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

	public function list_keys($table,$check_prefix=true)
	{
		if($check_prefix && substr($table,0,strlen($this->prefix)) != $this->prefix){
			$table = $this->prefix.$table;
		}
		$rs = $this->get_all("SHOW KEYS FROM ".$table);
		if(!$rs){
			return false;
		}
		$tmp = array();
		foreach($rs as $key=>$value){
			$tmp[strtolower($value['Key_name'])][$value['Seq_in_index']] = $value['Column_name'];
		}
		return $tmp;
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

	/**
	 * 显示表名
	 * @参数 $table_list 数组，整个数据库中的表
	 * @参数 $i 顺序ID
	**/
	public function table_name($table_list,$i)
	{
		return $table_list[$i];
	}

	/**
	 * 字符转义
	 * @参数 $char 要转义的字符
	**/
	public function escape_string($char)
	{
		if(!$char){
			return false;
		}
		return addslashes($char);
	}

	/**
	 * 取得MySQL版本号
	 * @参数 $type 支持server和client两种类型
	**/
	public function version($type='server')
	{
		if($type == 'server'){
			return $this->conn->getAttribute(PDO::ATTR_SERVER_VERSION);
		}else{
			return $this->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
		}
	}

	/**
	 * 创建主表操作
	 * @参数 $tblname 表名称
	 * @参数 $pri_id 主键ID
	 * @参数 $note 表摘要
	 * @参数 $engine 引挈，默认是 MyISAM
	**/
	public function create_table_main($tblname,$pri_id='',$note='',$engine='')
	{
		if(!$engine){
			$engine = 'MyISAM';
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
			if($data['default'] != ''){
				$sql .= "DEFAULT '".$data['default']."' ";
			}else{
				if($data['type'] == 'varchar'){
					$sql .= "DEFAULT '' ";
				}
			}
		}else{
			$sql .= "NULL ";
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
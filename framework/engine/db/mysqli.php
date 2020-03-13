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
	private $type = MYSQLI_ASSOC;

	public function __construct($config=array())
	{
		parent::__construct($config);
		//定义MySQL的保留字转义符
		$this->kec("`","`");
	}

	/**
	 * 析构函数，结束链接
	**/
	public function __destruct()
	{
		parent::__destruct();
		if($this->conn && is_object($this->conn)){
			mysqli_close($this->conn);
		}
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

	/**
	 * 检测链接是否存在
	**/
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

	/**
	 * 创建主表操作
	 * @参数 $tblname 表名称
	 * @参数 $pri_id 主键ID
	 * @参数 $note 表摘要
	 * @参数 $engine 引挈，默认是 InnoDB
	**/
	public function create_table_main($tblname,$pri_id='',$note='',$engine='')
	{
		if(!$engine){
			$engine = 'InnoDB';
		}
		if(!$pri_id){
			$pri_id = 'id';
		}
		$sql  = "CREATE TABLE IF NOT EXISTS `".$tblname."`(`".$pri_id."` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',";
		$sql .= "PRIMARY KEY (`".$pri_id."`) ) ";
		$sql .= "ENGINE=".$engine." DEFAULT CHARACTER SET utf8 COMMENT='".$note."' AUTO_INCREMENT=1";
		return $this->query($sql);
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
				return mysqli_num_rows($this->query);
			}
		}
		return false;
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
	 * 字符转义
	 * @参数 $char 要转义的字符
	**/
	public function escape_string($char)
	{
		if(!$char){
			return false;
		}
		$this->check_connect();
		return mysqli_escape_string($this->conn,$char);
	}

	/**
	 * 获取列表数据
	 * @参数 $sql 要查询的SQL
	 * @参数 $primary 绑定主键
	**/
	public function get_all($sql='',$primary="")
	{
		if($sql){
			$false = $this->cache_false($primary.'-'.$sql);
			if($false){
				return false;
			}
			if($this->cache_get($primary.'-'.$sql)){
				return $this->cache_get($primary.'-'.$sql);
			}
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = array();
		while($rows = mysqli_fetch_array($this->query,$this->type)){
			if($primary){
				$rs[$rows[$primary]] = $rows;
			}else{
				$rs[] = $rows;
			}
		}
		mysqli_free_result($this->query);
		$this->_time();
		if(!$rs || count($rs)<1){
			$this->cache_false_save($primary.'-'.$sql);
			return false;
		}
		if($this->cache_need($primary.'-'.$sql)){
			$this->cache_save($primary.'-'.$sql,$rs);
		}
		$this->cache_first($primary.'-'.$sql);
		return $rs;
	}

	/**
	 * 获取一条数据
	 * @参数 $sql 要执行的SQL
	**/
	public function get_one($sql='')
	{
		if($sql){
			$false = $this->cache_false($sql);
			if($false){
				return false;
			}
			if($this->cache_get($sql)){
				return $this->cache_get($sql);
			}
			$this->query($sql);
		}
		if(!$this->query || !is_object($this->query)){
			return false;
		}
		$this->_time();
		$rs = mysqli_fetch_array($this->query,$this->type);
		mysqli_free_result($this->query);
		$this->_time();
		if(!$rs){
			$this->cache_false_save($sql);
			return false;
		}
		if($this->cache_need($sql)){
			$this->cache_save($sql,$rs);
		}
		$this->cache_first($sql);
		return $rs;
	}

	/**
	 * 返回最后插入的ID
	**/
	public function insert_id()
	{
		return mysqli_insert_id($this->conn);
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
	 * 返回被筛选出来的字段数目
	 * @参数 $sql 要执行的SQL语句
	**/
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
	 * 执行SQL
	**/
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
		$this->debug($sql,$tmptime);
		if(mysqli_error($this->conn)){
			$this->error(mysqli_error($this->conn).', '.$sql,mysqli_errno($this->conn));
		}
		return $this->query;
	}

	/**
	 * 设置参数
	**/
	public function set($name,$value)
	{
		if($name == "rs_type" || $name == 'type'){
			$value = strtolower($value) == "num" ? MYSQLI_NUM : MYSQLI_ASSOC;
			$this->type = $value;
		}else{
			$this->$name = $value;
		}
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
	 * 事务提交
	**/
	public function t_commit()
	{
		return $this->transaction('commit');
	}

	/**
	 * 事务回滚
	**/
	public function t_rollback()
	{
		return $this->transaction('rollback');
	}
	
	/**
	 * 事务开始
	**/
	public function t_start()
	{
		return $this->transaction('start');
	}

	/**
	 * 启用事物
	 * @参数 $type  当值为【start,begin,1,open,init】，表示开启事务
	 *             当值为【finish,end,ok,true,2,commit,right,success】表示提交事务
	 *             当值为【cacel,stop,fail,false,0,wrong,error,rollback】表示回滚事务
	 * @参数 
	 * @参数 
	**/
	public function transaction($type='')
	{
		$act = false;
		if($type == ''){
			$act = 'start';
		}
		if(is_numeric($type)){
			$act = $type == 1 ? 'start' : ($type == 2 ? 'commit' : 'rollback');
		}elseif(is_bool($type)){
			$act = $type ? 'commit' : 'rollback';
		}else{
			$type = strtolower($type);
			$a = array('start','begin','open','init');
			$b = array('finish','end','ok','true','commit','right','success');
			$c = array('cacel','stop','fail','false','wrong','error','rollback');
			if(in_array($type,$a)){
				$act = 'start';
			}elseif(in_array($type,$b)){
				$act = 'commit';
			}elseif(in_array($type,$c)){
				$act = 'rollback';
			}
		}
		if(!$act){
			return false;
		}
		if($act == 'start'){
			mysqli_query($this->conn,'BEGIN');
		}elseif($act == 'commit'){
			mysqli_query($this->conn,'COMMIT');
			mysqli_query($this->conn,'END');
		}elseif($act == 'rollback'){
			mysqli_query($this->conn,'ROLLBACK');
			mysqli_query($this->conn,'END');
		}
		return true;
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
	
}
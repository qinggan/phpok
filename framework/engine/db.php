<?php
/*****************************************************************************************
	文件： {phpok}/engine/db.php
	备注： DB基类
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月04日 09时55分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class db
{
	protected $database;
	protected $conn;
	protected $query;
	protected $preg_sql = '/^(UPDATE|DELETE|REPLACE|INSERT)/isU';
	protected $escapes = '';
	protected $special_replace;
	protected $debug = false;
	protected $tbl_list = array();
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
	}

	public function __destruct()
	{
		session_write_close();
		unset($this);
	}

	public function database($database='')
	{
		if($database){
			$this->database = $database;
		}
		return $this->database;
	}

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

	public function cache_save($id,$data)
	{
		return true;
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
}
?>
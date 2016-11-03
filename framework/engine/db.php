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
			exit($this->ascii($info));
		}
	}

	public function debug($sql='')
	{
		if($sql && trim($sql)){
			$sql = trim($sql);
			$sqlid = 'phpok'.md5($sql);
			if($this->_sqlist && $this->_sqlist[$sqlid]){
				$this->_sqlist[$sqlid]['count']++;
			}else{
				$this->_sqlist[$sqlid] = array('sql'=>$sql,'count'=>1);
			}
			return true;
		}
		if(!$this->debug){
			return true;
		}
		$html = '<table cellspacing="0" border="1" style="border:1px solid #000;width:100%;height:auto;margin:10px 0;">';
		$html.= '<tr>';
		$html.= '<th style="background:#EEE;color:#000;text-align:center;font-weight:bold;padding:3px;">SQL</th>';
		$html.= '<th style="background:#EEE;color:#000;text-align:center;font-weight:bold;padding:3px;">Count</th>';
		$html.= '</tr>';
		foreach($this->_sqlist as $key=>$value){
			$html.= '<tr>';
			$html.= '<td style="text-align:left;padding:3px;background:#fff;color:#000;">'.$value['sql'].'</td>';
			$html.= '<td style="text-align:center;padding:3px;background:#fff;color:#000;">'.$value['count'].'</td>';
			$html.= '</tr>';
		}
		$html.= '</table>';
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
			$this->time = round(($this->time + ($time - $this->time_tmp)),5);
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
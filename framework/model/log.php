<?php
/**
 * 日志相关
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月31日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}

class log_model_base extends phpok_model
{
	public function __construct()
	{
		parent::model();
	}

	/**
	 * 保存日志
	 * @参数 $note 日志说明
	 * @参数 $mask 是否手动标记，为true时表示手动标志
	**/
	public function save($note='',$mask=false)
	{
		if(!$note){
			$tmpfile = $this->app_id.'/'.$this->ctrl.'_control.php';
			$note = P_Lang('执行文件 {ctrl} 方法：{func}',array('ctrl'=>$tmpfile,'func'=>$this->func.'_f'));
		}
		//传过来的日志说明为数组或对像都是手动标志
		if(is_array($note) || is_object($note)){
			$note = print_r($note,true);
			$note = str_replace(array("'",'"',"\r"," ","\t","\n"),'',$note);
			$mask = true;
		}
		$note = addslashes(trim($note));
		$ip = $this->lib('common')->ip();
		$data = array('note'=>$note,'dateline'=>$this->time,'app_id'=>$this->app_id,'ip'=>$ip);
		if($this->session->val('admin_id') && $this->app_id == 'admin'){
			$data['admin_id'] = $this->session->val('admin_id');
			$data['account'] = $this->session->val('admin_account');
		}else{
			$data['admin_id'] = 0;
		}
		if($this->session->val('user_id') && $this->app_id != 'admin'){
			$data['user_id'] = $this->session->val('user_id');
			$data['user'] = $this->session->val('user_name');
		}else{
			$data['user_id'] = 0;
		}
		$data['ctrl'] = $this->ctrl;
		$data['func'] = $this->func;
		$data['appid'] = $this->app_id;
		$data['mask'] = $mask ? 1 : 0;
		$url = $this->lib('server')->https() ? 'https://' : 'http://';
		$url.= $this->lib('server')->domain($this->config['get_domain_method']);
		$port = $this->lib('server')->port();
		if($port != 80 && $port != 443){
			$url .= ':'.$port;
		}
		$url .= $this->lib('server')->uri();
		$referer = $this->lib('server')->referer();
		$data['url'] = $this->format($url);
		$data['referer'] = $this->format($referer);
		$data['session_id'] = $this->session->sessid();
		return $this->_save($data);
	}

	private function _save($data)
	{
		if(!$data){
			return false;
		}
		ksort($data);
		$html  = "<?php exit('--------- START ---------');?>\n";
		$html .= "时间：".date("Y-m-d H:i:s",$data['dateline'])."\n";
		$html .= "网址：".$data['url']."\n";
		$html .= "来源：".($data['referer'] ? $data['referer'] : '未知')."\n";
		$html .= "应用ID：".$data['appid']."\n";
		$html .= "控制器：".$data['ctrl']."\n";
		$html .= "方法：".$data['func']."\n";
		$html .= "用户：".$data['user']."\n";
		$html .= "用户ID：".$data['user_id']."\n";
		$html .= "管理员：".$data['account']."\n";
		$html .= "管理员ID：".$data['admin_id']."\n";
		$html .= "IP：".$data['ip']."\n";
		if($data['note']){
			$html .= "内容：\n".$data['note']."\n-----END\n";
		}
		$html .= "<?php exit('--------- END ---------');?>\n";
		$file = $this->dir_data."log/".date("Ymd",$this->time).'.php';
		$handle = fopen($file,'ab');
		flock($handle,LOCK_EX | LOCK_NB);
		fwrite($handle,$html);
		flock($handle,LOCK_UN);
		fclose($handle);
	}

	private function id2name($id,$iskey=true)
	{
		$data = array();
		$data['ip'] = "IP";
		$data['admin_id'] = '管理员ID';
		$data['account'] = '管理员';
		$data['user_id'] = '用户ID';
		$data['user'] = '用户';
		$data['func'] = '方法';
		$data['ctrl'] = '控制器';
		$data['appid'] = '应用ID';
		$data['referer'] = '来源';
		$data['url'] = '网址';
		$data['dateline'] = '时间';
		$data['note'] = '内容';
		if($iskey && $data[$id]){
			return $data[$id];
		}
		if(!$iskey){
			$name = '';
			foreach($data as $key=>$value){
				if($value == $id){
					$name = $key;
					break;
				}
			}
			return $name;
		}
		return false;
	}

	/**
	 * 取得日志列表
	 * @参数 $date 查询日期
	**/
	public function get_list($date='',$num=1000)
	{
		$file = $this->dir_data.'log/'.$date.'.php';
		if(!file_exists($file)){
			return false;
		}
		$handle = fopen($file,'rb');
		$pos = -2;
		$eof = "";
		$list = array();
		while($num>0){
			while ($eof != "\n") {//这里控制从文件的最后一行开始读
				if (!fseek($handle, $pos, SEEK_END)) {
					$eof = fgetc($handle);
					$pos--;
				} else {
					break;
				}
			}
			$num--;
			$eof = "";
			if(ftell($handle) < 2){
				$num = 0;
			}
			$tmp = fgets($handle);
			if($tmp === false){
				$pos -= 2;
				continue;
			}
			if($tmp == ''){
				$pos -= 2;
				continue;
			}
			$tmp2 = trim($tmp);
			if($tmp2 == ''){
				$l = strlen($tmp);
				$pos -= $l;
				$pos -= 2;
				continue;
			}
			$tmp = trim($tmp);
			if($tmp == "<?php exit('--------- END ---------');?>"){
				$data = array();
				continue;
			}
			if($tmp == "<?php exit('--------- START ---------');?>"){
				$list[] = $data;
				$num--;
				continue;
			}
			if($tmp == "-----END"){
				$data['content'] = array();
				continue;
			}
			if($tmp == "内容："){
				krsort($data['content']);
				$data['note'] = implode("\n",$data['content']);
				if(strpos($data['note'],'Array') !== false){
					$data['note'] = '<pre>'.$data['note'].'</pre>';
				}
				unset($data['content']);
				continue;
			}
			if(isset($data['content'])){
				$data['content'][] = $tmp;
				continue;
			}
			$t = explode("：",$tmp);
			$id = $this->id2name($t[0],false);
			if($id){
				$data[$id] = $t[1];
			}
		}
		return $list;
	}
	

	public function string2array($string)
	{
		if($string == '<?php exit();?>'){
			return false;
		}
		$tmp = explode("[:[:]:]",$string);
		if(isset($tmp[0]) && isset($tmp[1])){
			$code = explode(",",$tmp[0]);
			$rs = array();
			foreach($tmp as $key=>$value){
				if($key <1){
					continue;
				}
				$id = $code[($key-1)];
				if($id == 'dateline' && $value){
					$rs[$id] = date("Y-m-d H:i:s",$value);
				}else{
					$rs[$id] = $value;
				}
				
			}
			return $rs;
		}
		return false;
	}

	/**
	 * 取得日志数量
	 * @参数 $condition 查询条件
	**/
	public function get_count($condition='')
	{
		$sql  = "SELECT count(l.id) FROM ".$this->db->prefix."log l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."adm a ON(l.admin_id=a.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		return $this->db->count($sql);
	}

}

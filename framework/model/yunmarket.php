<?php
/**
 * 云市场应用
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年4月23日
 * @更新 2023年4月23日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class yunmarket_model_base extends phpok_model
{
	private $config_data = array();
	public function __construct()
	{
		parent::model();
	}

	public function config()
	{
		if($this->config_data){
			return $this->config_data;
		}
		$file = $this->dir_data.'yunmarket_config.php';
		if(!file_exists($file)){
			return false;
		}
		include_once($file);
		if(!$config){
			return false;
		}
		$this->config_data = $config;
		return $config;
	}

	/**
	 * 下载
	**/
	public function download($id)
	{
		if(!$id){
			return false;
		}
		$config = $this->config();
		if(!$config){
			return false;
		}
		$data = array('id'=>$id,'func'=>'download');
		$data['domain'] = $this->lib('server')->domain();
		$data['_appid'] = $config['appid'];
		$data['_signature'] = $this->signature($data,$config['appsecret']);
		$this->lib('curl')->is_post(true);
		$this->lib('curl')->post_data($data);
		if($config['ip']){
			$this->lib('curl')->host_ip($config['ip']);
		}
		$info = $this->lib('curl')->get_json($config['server']);
		if(!$info){
			return false;
		}
		return $info;
	}


	/**
	 * 远程获取数据
	**/
	public function get_all($keywords='',$cateid=0,$offset=0,$psize=0)
	{
		$config = $this->config();
		if(!$config){
			return false;
		}
		$data = array();
		if($keywords){
			$data['keywords'] = $keywords;
		}
		if($cateid){
			$data['cateid'] = $cateid;
		}
		if($offset){
			$data['offset'] = $offset;
		}
		$data['psize'] = $psize;
		$data['domain'] = $this->lib('server')->domain();
		$data['_appid'] = $config['appid'];
		$data['_signature'] = $this->signature($data,$config['appsecret']);
		$this->lib('curl')->is_post(true);
		$this->lib('curl')->post_data($data);
		if($config['ip']){
			$this->lib('curl')->host_ip($config['ip']);
		}
		$info = $this->lib('curl')->get_json($config['server']);
		return $info;
	}

	/**
	 * 检查购买记录
	**/
	public function get_buy($user_id=0,$domain='',$id=0)
	{
		$sql = "SELECT soft_id FROM ".$this->db->prefix."yunmarket_server WHERE 1=1 ";
		if($user_id){
			$sql .= " AND user_id='".$user_id."' ";
		}
		if($domain){
			$sql .= " AND domain='".$domain."' ";
		}
		if($id){
			$sql .= " AND soft_id='".$id."' ";
		}
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rs = array();
		foreach($tmplist as $key=>$value){
			$rs[] = $value['soft_id'];
		}
		return $rs;
	}

	/**
	 * 获取插件信息
	**/
	public function get_info($id)
	{
		if(!$id){
			return false;
		}
		$config = $this->config();
		if(!$config){
			return false;
		}
		$data = array('id'=>$id,'func'=>'info');
		$data['domain'] = $this->lib('server')->domain();
		$data['_appid'] = $config['appid'];
		$data['_signature'] = $this->signature($data,$config['appsecret']);
		$this->lib('curl')->is_post(true);
		$this->lib('curl')->post_data($data);
		if($config['ip']){
			$this->lib('curl')->host_ip($config['ip']);
		}
		$info = $this->lib('curl')->get_json($config['server']);
		return $info;
	}

	/**
	 * 获取已安装的信息
	**/
	public function get_install($id=0)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."yunmarket_client";
		if($id){
			$sql .= " WHERE id='".$id."'";
		}
		$tmplist = $this->db->get_all($sql);
		if(!$tmplist){
			return false;
		}
		$rs = array();
		foreach($tmplist as $key=>$value){
			$rs[$value['id']] = $value;
		}
		return $rs;
	}

	public function install($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$this->db->insert($data,'yunmarket_client','replace');
		return true;
	}

	public function save($data,$id=0)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if($id){
			return $this->db->update($data,'yunmarket_server',array('id'=>$id));
		}
		return $this->db->insert($data,'yunmarket_server');
	}

	/**
	 * 配置环境信息
	**/
	public function setting($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		$config = $this->config();
		if(!$config){
			$config = array();
		}
		$config = array_merge($config,$data);
		$this->lib('file')->vi($config,$this->dir_data.'yunmarket_config.php','config');
		return true;
	}

	public function signature($data,$appsecret='')
	{
		if(isset($data['_appid'])){
			unset($data['_appid']);
		}
		if(isset($data['_signature'])){
			unset($data['_signature']);
		}
		ksort($data);
		$string = $appsecret;
		foreach($data as $key=>$value){
			$string .= $key.'='.$value;
		}
		return md5($string);
	}

	public function vip($id)
	{
		if(!$id){
			return false;
		}
		$config = $this->config();
		if(!$config){
			return false;
		}
		$data = array('id'=>$id,'func'=>'order');
		$data['domain'] = $this->lib('server')->domain();
		$data['_appid'] = $config['appid'];
		$data['_signature'] = $this->signature($data,$config['appsecret']);
		$this->lib('curl')->is_post(true);
		$this->lib('curl')->post_data($data);
		if($config['ip']){
			$this->lib('curl')->host_ip($config['ip']);
		}
		$info = $this->lib('curl')->get_json($config['server']);
		return $info;
	}

	public function uninstall($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."yunmarket_client WHERE id='".$id."'";
		$this->db->query($sql);
		return true;
	}
}

<?php
/**
 * 日志相关
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年05月05日
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
			$note = P_Lang('执行文件{ctrl}方法：{func}',array('ctrl'=>$tmpfile,'func'=>$this->func.'_f'));
		}
		if(is_string($note) && strpos($note,'<') !== false){
			$note = htmlentities($note);
			$note = phpok_cut($note,255,'…');
		}
		//传过来的日志说明为数组或对像都是手动标志
		if(is_array($note) || is_object($note)){
			$note = '<pre>'.print_r($note,true).'</pre>';
			$mask = true;
		}
		$note = addslashes(trim($note));
		$ip = $this->lib('common')->ip();
		$data = array('note'=>$note,'dateline'=>$this->time,'app_id'=>$this->app_id,'admin_id'=>0,'user_id'=>0,'ip'=>$ip);
		if($this->app_id == 'admin'){
			if($this->session->val('admin_id')){
				$data['admin_id'] = $this->session->val('admin_id');
			}
		}else{
			if($this->session->val('user_id')){
				$data['user_id'] = $this->session->val('user_id');
			}
		}
		$data['ctrl'] = $this->ctrl;
		$data['func'] = $this->func;
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
		
		//1分钟内同样的错误不再重复写入
		$time = $this->time - 60;
		$sql = "SELECT id FROM ".$this->db->prefix."log WHERE note='".$note."' AND app_id='".$this->app_id."' AND ctrl='".$data['ctrl']."'";
		$sql.= " AND func='".$data['func']."' AND dateline>=".$time." LIMIT 1";
		$chk = $this->db->get_one($sql);
		if($chk){
			return false;
		}

		//登录页防止刷库，仅允许10秒写入一条数据
		if($data['ctrl'] == 'login'){
			$time = $this->time - 10;
			$sql = "SELECT id FROM ".$this->db->prefix."log WHERE app_id='".$this->app_id."' AND ctrl='login' AND dateline>=".$time." LIMIT 1";
			$chk = $this->db->get_one($sql);
			if($chk){
				return false;
			}
		}
		$this->db->insert_array($data,'log');
	}

	/**
	 * 取得日志列表
	 * @参数 $condition 查询条件
	 * @参数 $offset 开始位置，首位从0计起
	 * @参数 $psize 每次读取数量
	**/
	public function get_list($condition='',$offset=0,$psize=30)
	{
		$sql  = "SELECT l.*,a.account,u.user FROM ".$this->db->prefix."log l ";
		$sql .= "LEFT JOIN ".$this->db->prefix."adm a ON(l.admin_id=a.id) ";
		$sql .= "LEFT JOIN ".$this->db->prefix."user u ON(l.user_id=u.id) ";
		if($condition){
			$sql.= "WHERE ".$condition." ";
		}
		$sql.= "ORDER BY l.dateline DESC,l.id DESC LIMIT ".$offset.",".$psize;
		return $this->db->get_all($sql);
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

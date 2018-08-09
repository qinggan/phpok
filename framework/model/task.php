<?php
/**
 * 计划任务
 * @package phpok\model
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月22日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class task_model_base extends phpok_model
{
	private $task_info;
	public function __construct()
	{
		parent::model();
	}

	//增加一次性执行的动作
	public function add_once($action,$param='')
	{
		if(!$action){
			return false;
		}
		$mytime = $this->time + 5;
		$data = array('year'=>date("Y",$mytime));
		$data['month'] = date("m",$mytime);
		$data['day'] = date("d",$mytime);
		$data['hour'] = date("H",$mytime);
		$data['minute'] = date("i",$mytime);
		$data['second'] = date("s",$mytime);
		$data['action'] = $action;
		$data['param'] = $param;
		$data['only_once'] = 1;
		$this->db->insert_array($data,'task');
	}

	public function log_add($data)
	{
		if(!$data || !is_array($data)){
			return false;
		}
		if(!$data['dateline']){
			$data['dateline'] = $this->time;
		}
		return $this->db->insert_array($data,'task_log');
	}

	public function get_all($is_lock=0,$condition="")
	{
		$sql = "SELECT * FROM ".$this->db->prefix."task WHERE 1=1 ";
		if($is_lock){
			$sql.= " AND is_lock='".($is_lock == 1 ? 1 : 0)."'";
		}
		if($condition){
			$sql.= " AND ".$condition." ";
		}
		$sql.= "ORDER BY id ASC";
		return $this->db->get_all($sql);
	}

	public function get_first()
	{
		$exec_time = $this->time - 3 * 3600;
		$sql = "SELECT * FROM ".$this->db->prefix."task WHERE is_lock=0 AND exec_time<".$exec_time." ORDER BY id ASC LIMIT 1";
		return $this->db->get_one($sql);
	}

	public function get_one($id)
	{
		$sql = "SELECT * FROM ".$this->db->prefix."task WHERE id='".$id."'";
		return $this->db->get_one($sql);
	}

	public function lock($id)
	{
		$rs = $this->get_one($id);
		$this->info($rs);
		$sql = "UPDATE ".$this->db->prefix."task SET is_lock=1 WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function unlock($id=0)
	{
		if($id){
			$sql = "UPDATE ".$this->db->prefix."task SET is_lock=0 WHERE id='".$id."'";
			return $this->db->query($sql);
		}else{
			$time = $this->time - 5;
			$sql = "UPDATE ".$this->db->prefix."task SET is_lock=0 WHERE exec_time<".$time." AND exec_time>0 AND is_lock=1";
			return $this->db->query($sql);
		}
	}


	public function info($rs)
	{
		$this->task_info = $rs;
	}

	public function exec_start($id)
	{
		$sql = "UPDATE ".$this->db->prefix."task SET exec_time='".time()."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function exec_stop($id)
	{
		$sql = "UPDATE ".$this->db->prefix."task SET stop_time='".time()."' WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	public function delete($id)
	{
		$sql = "DELETE FROM ".$this->db->prefix."task WHERE id='".$id."'";
		return $this->db->query($sql);
	}

	/**
	 * 定时更新主题状态，此项仅限访问首页时会自动执行
	 * @date 2016年02月05日
	 */
	public function set_title_status()
	{
		if(file_exists($this->dir_cache.'tasklock.php')){
			$time = filemtime($this->dir_cache.'tasklock.php');
			if( ($time + 3600) > $this->time ){
				return true;
			}
		}
		$sql = "UPDATE ".$this->db->prefix."list SET hidden=0 WHERE status=1 AND hidden=2";
		$sql.= " AND dateline<='".$this->time."' AND site_id='".$this->site_id."'";
		$this->db->query($sql,false);
		$this->lib('file')->vi($this->time,$this->dir_cache.'tasklock.php');
		return true;
	}

}
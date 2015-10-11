<?php
/*****************************************************************************************
	文件： {phpok}/model/task.php
	备注： 计划任务
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月10日 13时27分
*****************************************************************************************/
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

	public function get_all()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."task WHERE is_lock=0 ORDER BY id ASC";
		return $this->db->get_all($sql);
	}

	public function get_first()
	{
		$sql = "SELECT * FROM ".$this->db->prefix."task WHERE is_lock=0 ORDER BY id ASC LIMIT 1";
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
}

?>
<?php
/*****************************************************************************************
	文件： {phpok}/api/task_control.php
	备注： 计划任务通知
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年09月20日 10时05分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class task_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	//计划任务，每次只执行一条
	public function index_f()
	{
		//解除锁定
		$this->model('task')->unlock();
		//获取计划任务
		$rslist = $this->model('task')->get_all();
		if(!$rslist){
			$this->json(true);
		}
		foreach($rslist as $key=>$value){
			$this->exec_action($value);
		}
		$this->json(true);
	}

	private function exec_action($rs)
	{
		//锁定计划任务执行
		$id = $rs['id'];
		$this->model('task')->lock($id);
		//年份处理
		$rs['year'] = $rs['year'] == '*' ? date("Y",$this->time) : $rs['year'];
		$rs['month'] = $rs['month'] == '*' ? date("m",$this->time) : $rs['month'];
		$rs['day'] = $rs['day'] == '*' ? date("d",$this->time) : $rs['day'];
		$rs['hour'] = $rs['hour'] == '*' ? date("H",$this->time) : $rs['hour'];
		$rs['minute'] = $rs['minute'] == '*' ? date("i",$this->time) : $rs['minute'];
		$rs['second'] = $rs['second'] == '*' ? date('s',$this->time) : $rs['second'];
		$time = $rs['year'].'-'.$rs['month'].'-'.$rs['day'].' '.$rs['hour'].':'.$rs['minute'].':'.$rs['second'];
		$time = strtotime($time) - 5;
		//五分钟内只执行一次
		if($rs['exec_time'] && ($rs['exec_time'] + 300)>$this->time){
			$this->model('task')->unlock($id);
			return true;
		}
		//只执行一天内的计划任务，超过一天的不再执行
		$if_delete = false;
		if($time <= $this->time && (($time+24*3600)>$this->time || $rs['only_once'])){
			$this->model('task')->exec_start($id);
			$file = $this->dir_root.'task/'.$rs['action'].'.php';
			if(file_exists($file)){
				if($rs['param']){
					parse_str($rs['param'],$param);
				}
				$status = include $file;
			}
			$this->model('task')->exec_stop($id);
			if($rs['only_once']){
				$this->model('task')->delete($id);
				$if_delete = true;
			}
		}
		if(!$if_delete){
			$this->model('task')->unlock($id);
		}
		return true;
	}
}

?>
<?php
/**
 * 日志管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年05月07日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class log_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$condition = date("Ymd",$this->time);
		$start_time = $this->get('start_time');
		if($start_time){
			$condition = str_replace("-",'',$start_time);
			$this->assign('start_time',$start_time);
		}else{
			$this->assign('start_time',date("Y-m-d",$this->time));
		}
		$this->assign('condition',$condition);
		$rslist = $this->model('log')->get_list($condition);
		$this->assign('rslist',$rslist);
		$this->view('log_index');
	}

	public function delete_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('只有系统管理员才有此权限'));
		}
		$id = $this->get('id','int');
		$ids = $this->get('ids');
		$date = $this->get('date','int');
		if(!$id && !$ids && !$date){
			$this->error(P_Lang('参数不完整！'));
		}
		if($id){
			$condition = "id='".$id."'";
			$tip = P_Lang('删除日志#{id}',array('id'=>$id));
		}
		if($ids){
			$lst = explode(",",$ids);
			foreach($lst as $key=>$value){
				if(!$value || !trim($value) || !intval($value)){
					unset($lst[$key]);
					continue;
				}
				$lst[$key] = intval($value);
			}
			$ids = implode(",",$lst);
			if(!$ids){
				$this->error(P_Lang('未指定要删除的日志'));
			}
			$condition = "id IN(".$ids.")";
			$tip = P_Lang('删除日志#{id}',array('id'=>$ids));
		}
		if($date){
			$time = strtotime(date("Y-m-d",$this->time)) - $date*24*60*60;
			$condition = "dateline<".$time;
			$tip = P_Lang('删除{date}天前的日志',array('date'=>$date));
		}
		$this->model('log')->delete($condition);
		$this->model('log')->save($tip);
		$this->success();
	}

	public function download_f()
	{
		$date = date("Ymd",$this->time);
		$start_time = $this->get('start_time');
		if($start_time){
			$date = str_replace("-",'',$start_time);
		}
		$file = $this->dir_data."log/".$date.".php";
		if(!file_exists($file)){
			$this->error(P_Lang('日志文件不存在'));
		}
		$this->lib('file')->download($file,$date.".log");
	}
}

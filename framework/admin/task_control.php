<?php
/*****************************************************************************************
	文件： {phpok}/admin/task_control.php
	备注： 计划任务管理器
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年10月21日 09时48分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class task_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('task','only_once=0');		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('task')->get_all();
		$this->assign('rslist',$rslist);

		$yearlist = array();
		$this_year = date("Y",$this->time);
		$last_year = $this_year + 5;
		for($i=$this_year;$i<$last_year;$i++){
			$yearlist[] = $i;
		}
		$this->assign('yearlist',$yearlist);
		$monthlist = array();
		for($i=1;$i<13;$i++){
			$monthlist[] = $i;
		}
		$this->assign('monthlist',$monthlist);

		$daylist = array();
		for($i=1;$i<32;$i++){
			$daylist[] = $i;
		}
		$this->assign('daylist',$daylist);

		$hourlist = array();
		for($i=0;$i<24;$i++){
			$hourlist[] = $i;
		}
		$this->assign('hourlist',$hourlist);

		$minutelist = array();
		for($i=1;$i<61;$i++){
			$minutelist[] = $i;
		}
		$this->assign('minutelist',$minutelist);

		//计划任务文件
		$filelist = $this->lib('file')->ls($this->dir_root.'task/');
		if($filelist){
			foreach($filelist as $key=>$value){
				$tmp = $this->lib('file')->cat($value,1024);
				$info = '';
				preg_match_all("/.*备注(：|:)([^\n]+)\n.*/isU",$tmp,$matchs);
				if($matchs && $matchs[2]){
					$info = $matchs[2][0];
					if($info){
						$info = trim($info);
					}
				}
				$value = basename($value);
				$value = substr($value,0,-4);
				$filelist[$key] = array('id'=>$value,'txt'=>($info ? $info : $value));
			}
		}
		$this->assign('filelist',$filelist);

		$this->view('task_index');
	}

	public function save_f()
	{
		$id = $this->get('id');
		if($id){
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}
		$main = array();
		$main['year'] = $this->get('year');
		$main['month'] = $this->get('month');
		$main['day'] = $this->get('day');
		$day30 = array(4,6,9,11);
		if($main['day'] && is_numeric($main['day']) && $main['month'] && is_numeric($main['month'])){
			if(in_array($main['month'],$day30) && $main['day'] == '31'){
				$this->json(P_Lang('当前月份不支持31日'));
			}
			if($main['month'] == '02' && $main['day'] > 29){
				$this->json(P_Lang('二月份不支持30日及31日'));
			}
			if($main['day'] == 29 && $main['year'] && is_numeric($main['year'])){
				if($main['year']%4){
					$this->json(P_Lang('当前月份不支持29日'));
				}
			}
		}
		$main['hour'] = $this->get('hour');
		$main['minute'] = $this->get('minute');
		$main['second'] = '*';
		$main['action'] = $this->get('actionfile');
		if(!$main['action']){
			$this->json(P_Lang('未指定动作'));
		}
		if(!file_exists($this->dir_root.'task/'.$main['action'].'.php')){
			$this->json(P_Lang('文件不存在'));
		}
		$main['param'] = $this->get('param');
		$this->model('task')->save($main,$id);
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('task')->delete($id);
		$this->json(true);
	}
}

?>
<?php
/*****************************************************************************************
	文件： {phpok}/admin/workflow_control.php
	备注： 工作流管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年06月20日 15时42分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class workflow_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('workflow');
		$this->assign("popedom",$this->popedom);
	}

	public function title_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$idlist = explode(',',$id);
		$endlist = $rslist = false;
		foreach($idlist as $key=>$value){
			$tmp = $this->model('workflow')->get_tid($value);
			if($tmp && $tmp['is_end']){
				if(!$endlist){
					$endlist = array();
				}
				$endlist[$value] = $tmp;
			}else{
				if(!$rslist){
					$rslist = array();
				}
				$rslist[] = $value;
			}
		}
		if(!$rslist){
			$this->error(P_Lang('指定的主题已经授权且已完成维护'));
		}
		$ids = implode(",",$rslist);
		$this->assign('ids',$ids);
		$rslist = $this->model('list')->get_all("l.id IN(".$ids.")",0,999,'id');
		$this->assign('rslist',$rslist);
		$this->assign('endlist',$endlist);
		$alist = $this->model('admin')->all_manager();
		if(!$alist){
			$this->error(P_Lang('系统没有普通管理员，不能执行此操作'));
		}
		$this->assign('alist',$alist);
		$this->view('workflow_title');
	}
}

?>
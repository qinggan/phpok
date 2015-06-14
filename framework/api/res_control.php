<?php
/***********************************************************
	Filename: {phpok}/api/res_control.php
	Note	: 附件相关信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年12月4日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	//根据ID获取附件列表
	//返回JS数据
	public function idlist_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$newlist = array();
		foreach($list AS $key=>$value){
			$value = intval($value);
			if($value){
				$newlist[] = $value;
			}
		}
		$id = implode(",",$newlist);
		if(!$id){
			$this->json(P_Lang('附件ID不正确'));
		}
		$rslist = $this->model("res")->get_list_from_id($id,true);
		if($rslist){
			$this->json($rslist,true);
		}
		$this->json("附件信息获取失败");
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('附件不存在'));
		}
		if($_SESSION['user_id']){
			if($rs['user_id'] != $_SESSION['user_id']){
				$this->json('您没有权限执行此操作');
			}
		}else{
			if($_SESSION['session_id'] != $this->session->sessid()){
				$this->json('您没有权限执行此操作');
			}
		}
		$this->model('res')->delete($id);
		$this->json(P_Lang('删除成功'),true);
	}

	public function update_title_note_f()
	{
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$_SESSION["admin_id"]){
			$rs = $this->model('res')->get_one($id);
			if(!$rs){
				$this->json(P_Lang('附件不存在'));
			}
			if($_SESSION['user_id']){
				if($rs['user_id'] != $_SESSION['user_id']){
					$this->json('您没有权限执行此操作');
				}
			}else{
				if($_SESSION['session_id'] != $this->session->sessid()){
					$this->json('您没有权限执行此操作');
				}
			}
		}
		$title = $this->get("title");
		if(!$title){
			$this->json(P_Lang('名称不能为空'));
		}
		$this->model('res')->update_title($title,$id);
		$note = $this->get("note");
		$this->model('res')->update_note($note,$id);
		$this->json(P_Lang('附件信息更新成功'),true);
	}
}
?>
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
	function __construct()
	{
		parent::control();
	}

	//根据ID获取附件列表
	//返回JS数据
	function idlist_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			$this->json("没有指定附件ID");
		}
		$list = explode(",",$id);
		$newlist = array();
		foreach($list AS $key=>$value)
		{
			$value = intval($value);
			if($value)
			{
				$newlist[] = $value;
			}
		}
		$id = implode(",",$newlist);
		if(!$id)
		{
			$this->json("请传递正确的附件ID");
		}
		$rslist = $this->model("res")->get_list_from_id($id,true);
		if($rslist)
		{
			$this->json($rslist,true);
		}
		$this->json("附件信息获取失败，可能已经删除，请检查");
	}

	//删除附件
	function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id) $this->json('附件ID为空');
		//判断是否有这个权限
		$rs = $this->model('res')->get_one($id);
		if(!$rs) $this->json('附件不存在');
		if($_SESSION['user_id'])
		{
			if($rs['user_id'] != $_SESSION['user_id']) $this->json('您没有权限删除这个附件信息');
		}
		else
		{
			if($_SESSION['session_id'] != $this->sesson->sessid()) $this->json('您没有权限删除这个附件信息');
		}
		//删除附件信息
		$this->model('res')->delete($id);
		$this->json('删除成功',true);
	}

	//更新附件信息
	# 通过Ajax更新名称和备注
	function update_title_note_f()
	{
		$id = $this->get("id","int");
		if(!$id) $this->json('未指定附件ID');
		//非后台管理员进行限制
		if(!$_SESSION["admin_id"])
		{
			$rs = $this->model('res')->get_one($id);
			if(!$rs) $this->json('附件不存在');
			if($_SESSION['user_id'])
			{
				if($rs['user_id'] != $_SESSION['user_id']) $this->json('您没有权限删除这个附件信息');
			}
			else
			{
				if($_SESSION['session_id'] != $this->sesson->sessid()) $this->json('您没有权限删除这个附件信息');
			}
		}
		$title = $this->get("title");
		if(!$title)
		{
			json_exit("附件名称不能为空");
		}
		$this->model('res')->update_title($title,$id);
		$note = $this->get("note");
		$this->model('res')->update_note($note,$id);
		json_exit("附件信息更新成功",true);
	}
}
?>
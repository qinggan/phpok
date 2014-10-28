<?php
/***********************************************************
	Filename: app/admin/control/email.php
	Note	: 邮件发送操作
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-12
***********************************************************/
class email_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("email");
		$this->assign("popedom",$this->popedom);
	}

	function index_f()
	{
		if(!$this->popedom["list"]) error("你没有查看权限");
		$site_id = $_SESSION["admin_site_id"];
		if($site_id)
		{
			$condition = "site_id IN(".$site_id.",0)";
		}
		else
		{
			$condition = "site_id=0";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('email')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('email')->get_count($condition);//读取模块总数
		$pageurl = $this->url("email");
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
		if($pagelist)
		{
			$this->assign("pagelist",$pagelist);
		}
		$this->view("email_list");
	}

	function set_f()
	{
		$id = $this->get("id","int");
		if($id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限");
			$rs = $this->model('email')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限");
			$rs = array("content"=>'');
		}
		$edit_content = form_edit('content',$rs['content'],'editor','height=480&btn_image=1&is_code=1');
		$this->assign('edit_content',$edit_content);
		$this->view("email_set");
	}

	function setok_f()
	{
		$array = array();
		$id = $this->get("id","int");
		if(!$id)
		{
			$array["site_id"] = $_SESSION["admin_site_id"];
		}
		$array["title"] = $this->get("title");
		$array["content"] = $this->get("content","html",false);
		$array["identifier"] = $this->get("identifier");
		if(!$array["title"] || !$array["content"] || !$array["identifier"])
		{
			error("信息填写不完整",$this->url("email","set","id=".$id),"error");
		}
		$this->model('email')->save($array,$id);
		error("邮件内容创建/修改成功，请稍候……",$this->url("email"),"ok");
	}

	//删除邮件
	function del_f()
	{
		if(!$this->popedom["delete"]) exit("你没有删除权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			exit("没有指定要删除的邮件！");
		}
		else
		{
			$this->model('email')->del($id);
			exit("ok");
		}
	}

	function check_f()
	{
		$id = $this->get("id","int");
		$identifier = $this->get("identifier");
		if(!$identifier)
		{
			exit("未指定标识串");
		}
		$rs = $this->model('email')->get_identifier($identifier,$_SESSION["admin_site_id"],$id);
		if($rs)
		{
			exit("标识串已被使用");
		}
		exit("ok");
	}
}
?>
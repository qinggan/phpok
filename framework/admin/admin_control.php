<?php
/***********************************************************
	Filename: {phpok}/admin/admin_control.php
	Note	: 管理员及其组管理组
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年7月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("admin");
		$this->assign("popedom",$this->popedom);
	}

	function index_f()
	{
		if(!$this->popedom['list']) error("您没有权限查看管理员信息");
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"];
		if(!$psize) $psize = 30;
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$keywords = $this->get("keywords");
		$pageurl = $this->url("admin");
		if($keywords)
		{
			$condition .= " AND account LIKE '%".$keywords."%' ";
			$pageurl .= '&keywords='.rawurlencode($keywords);
		}
		$rslist = $this->model('admin')->get_list($condition,$offset,$psize);
		$total = $this->model('admin')->get_total($condition);
		if($total > $psize)
		{
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=(total)/(psize)&always=1");
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("rslist",$rslist);
		$this->view("admin_list");
	}

	//添加或修改管理员信息
	function set_f()
	{
		$id = $this->get("id","int");
		$plist = array();
		if($id)
		{
			if(!$this->popedom['modify']) error("您没有权限修改管理员信息");
			if($id == $_SESSION["admin_id"])
			{
				error("您不能编辑自己的信息",$this->url("admin"),"error");
			}
			$this->assign("id",$id);
			$rs = $this->model('admin')->get_one($id);
			if($rs["if_system"] && !$_SESSION["admin_rs"]["if_system"])
			{
				error("非系统管理员不能编辑系统管理员信息",$this->url("admin"),"error");
			}
			$this->assign("rs",$rs);
			if(!$rs["if_system"])
			{
				$plist = $this->model('admin')->get_popedom_list($id);
			}
			$category = $rs['category'] ? explode(",",$rs['category']) : array('all');
		}
		else
		{
			if(!$this->popedom['add']) error("您没有权限添加新管理员");
			$category = array('all');
		}
		$this->assign("plist",$plist);
		//读取全部功能
		$syslist = $this->model('sysmenu')->get_all(0,1);
		$this->assign("syslist",$syslist);
		//读取全部功能的权限信息
		$glist = $this->model('popedom')->get_all("pid=0",true,false);
		$clist = $this->model('popedom')->get_all("pid>0",true,true);
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$this->assign("c_rs",$c_rs);
		$sitelist = $this->model('site')->get_all_site();
		if($sitelist)
		{
			foreach($sitelist AS $key=>$value)
			{
				$all_project = $this->model('project')->get_all_project($value["id"]);
				if($all_project)
				{
					foreach($all_project AS $k=>$v)
					{
						if($clist[$v["id"]])
						{
							$all_project[$k]["_popedom"] = $clist[$v["id"]];
						}
					}
				}
				$value["sonlist"] = $all_project;
				$sitelist[$key] = $value;
			}
			$this->assign("sitelist",$sitelist);
		}
		$this->assign("glist",$glist);
		$this->view("admin_set");
	}

	function check_if_system_f()
	{
		$id = $this->get("id","int");
		$exit = $this->check_system($id);
		if($exit == "ok")
		{
			json_exit("ok",true);
		}
		else
		{
			json_exit($exit);
		}
	}

	function check_system($id=0)
	{
		$condition = "if_system=1 AND status=1";
		$rslist = $this->model('admin')->get_list($condition,0,100);
		if(!$rslist)
		{
			return "没有系统管理员，请检查";
		}
		$if_system = false;
		foreach($rslist AS $key=>$value)
		{
			if($value["id"] != $id)
			{
				$if_system = true;
			}
		}
		if(!$if_system)
		{
			return "至少需要有一位可登录的系统管理员，请检查！";
		}
		return "ok";
	}

	//删除管理员
	function delete_f()
	{
		if(!$this->popedom["delete"])
		{
			json_exit("您没有删除管理员权限");
		}
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("没有指定要删除的管理员");
		}
		$exit = $this->check_system($id);
		if($exit != "ok")
		{
			json_exit($exit);
		}
		//删除管理员信息
		$this->model('admin')->delete($id);
		json_exit("ok",true);
	}

	//检测账号是否存在
	function check_account_f()
	{
		$id = $this->get("id","int");
		$account = $this->get("account");
		$str = $this->check_account($account,$id);
		if($str == "ok")
		{
			json_exit("ok",true);
		}
		json_exit($str);
	}

	function check_account($account,$id=0)
	{
		if(!$account)
		{
			return "账号不能为空";
		}
		$rs = $this->model('admin')->check_account($account,$id);
		if($rs)
		{
			return "账号已存在";
		}
		return "ok";
	}

	//存储管理员信息
	function save_f()
	{
		$id = $this->get("id","int");
		if($id && $id == $_SESSION["admin_id"])
		{
			error("您不能编辑自己的信息",$this->url("admin"),"error");
		}
		if($id)
		{
			if(!$this->popedom["modify"]) error("您没有编辑管理员权限");
		}
		else
		{
			if(!$this->popedom["add"]) error("您没有添加管理员权限");
		}
		$account = $this->get("account");
		if(!$account)
		{
			error("管理员账号不能为空",$this->url("admin","set","id=".$id),"error");
		}
		$check_str = $this->check_account($account,$id);
		if($check_str != "ok")
		{
			error($check_str,$this->url("admin","set","id=".$id),"error");
		}
		$array = array();
		$array["account"] = $account;
		$pass = $this->get("pass");
		if(!$pass && !$id)
		{
			error("密码不能为空",$this->url("admin","set","id=".$id),"error");
		}
		if($pass)
		{
			if(strlen($pass) < 4)
			{
				error("密码长度不能少于4位",$this->url("admin","set","id=".$id),"error");
			}
			$array["pass"] = password_create($pass);
		}
		$array['email'] = $this->get("email");
		if($this->popedom["status"])
		{
			$array["status"] = $this->get("status","int");
		}
		$if_system = $this->get("if_system","int");
		if(!$_SESSION["admin_rs"]["if_system"])
		{
			$if_system = 0;
		}
		$array["if_system"] = $if_system;
		if($id)
		{
			$st = $this->model('admin')->save($array,$id);
			if(!$st)
			{
				error("管理员信息更新失败，请检查！",$this->url("admin","set","id=".$id),"error");
			}
		}
		else
		{
			$id = $this->model('admin')->save($array);
			if(!$id)
			{
				error("管理员信息添加失败，请检查！",$this->url("admin","set"),"error");
			}
		}
		//清空权限信息
		$this->model('admin')->clear_popedom($id);
		//更新普通管理员权限
		if(!$if_system)
		{
			$popedom = $this->get("popedom");
			if($popedom)
			{
				$popedom = array_unique($popedom);
				$this->model('admin')->save_popedom($popedom,$id);
			}
		}
		error("管理员信息 <span class='red'>添加/更新</span> 成功！",$this->url("admin"),"ok");
	}

	//更新管理员状态
	function status_f()
	{
		if(!$this->popedom["status"])
		{
			json_exit("你没有启用/禁用管理员权限");
		}
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("没有指定ID！");
		}
		if($id == $_SESSION["admin_id"])
		{
			json_exit("你不能操作自己的账号信息");
		}
		$rs = $this->model('admin')->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('admin')->update_status($id,$status);
		if(!$action)
		{
			json_exit("操作失败，请检查SQL语句！");
		}
		else
		{
			json_exit($status,true);
		}
	}
}
?>
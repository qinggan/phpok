<?php
/***********************************************************
	Filename: {phpok}/www/register_control.php
	Note	: 会员注册信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2014年10月11日 05时42分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class register_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		$_back = $this->get("_back");
		if(!$_back)
		{
			$_back = $this->url;
		}
		if($_SESSION["user_id"])
		{
			error(P_Lang('您已登录，不用注册'),$_back,'error');
		}
		$this->assign('_back',$_back);
		//判断是否有启用注册
		if(!$this->site['register_status'])
		{
			$tips = $this->site["register_close"] ? $this->site["register_close"] : P_Lang('系统暂停会员注册，请联系站点管理员');
			error($tips,$_back,'error');
		}
		//取得开放的会员组信息
		$grouplist = $this->model("usergroup")->opened_grouplist();
		if(!$grouplist)
		{
			error(P_Lang('未找到有效的会员组信息'),$_back,'error',10);
		}
		$this->assign("grouplist",$grouplist);
		$gid = $this->get("group_id","int");
		if($gid)
		{
			$group_rs = $this->model("usergroup")->get_one($gid);
			if(!$group_rs || !$group_rs["status"]) $gid = 0;
		}
		if(!$gid)
		{
			if(count($grouplist) == 1)
			{
				$group_rs = current($grouplist);
				$gid = $group_rs['id'];
			}
			else
			{
				foreach($grouplist AS $key=>$value)
				{
					if($value["is_default"])
					{
						$gid = $value["id"];
						$group_rs = $value;
					}
				}
			}
		}
		//判断是否使用验证码注册
		$this->assign("group_id",$gid);
		$this->assign("group_rs",$group_rs);
		if($group_rs["register_status"] && $group_rs["register_status"] != "1")
		{
			if(!$group_rs['tbl_id'])
			{
				error(P_Lang('未绑定验证项目'),$this->url,'error');
			}
			$p_rs = $this->model("project")->get_one($group_rs["tbl_id"],false);
			if(!$p_rs['module'])
			{
				error(P_Lang('绑定的项目中没有关联模块'),$this->url,'error');
			}
			$code = $this->get('_code');
			if(!$code)
			{
				$this->view('register_check_'.$group_rs["register_status"]);
				exit;
			}
			$chk_rs = $this->model("list")->get_one_condition("l.title='".$code."'",$p_rs['module']);
			if(!$chk_rs)
			{
				error(P_Lang("验证码不正确，请检查"),$this->url("register"),"error");
			}
			if($chk_rs && $chk_rs["account"])
			{
				error(P_Lang("验证码已使用过，请填写新的验证码"),$this->url("register"));
			}
			if(!$chk_rs["status"])
			{
				error(P_Lang("验证码未启用"),$this->url("register"));
			}
			if(($chk_rs['dateline'] + 86400) < $this->time)
			{
				error(P_Lang('验证码已过期'),$this->url('register'));
			}
			$email = $this->get('email');
			if($email)
			{
				$this->assign('account',$email);
				$this->assign('email',$email);
			}
			$this->assign("code",$code);
		}
		//取得当前组的扩展字段
		$ext_list = $this->model("user")->fields_all("is_edit=1");
		$extlist = false;
		if(!$ext_list) $ext_list = array();
		foreach($ext_list AS $key=>$value)
		{
			if($value["ext"])
			{
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v)
				{
					$value[$k] = $v;
				}
			}
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]])
			{
				$value["content"] = $rs[$value["identifier"]];
			}
			if(!$group_rs['fields'] || ($group_rs['fields'] && in_array($value['identifier'],explode(",",$group_rs['fields']))))
			{
				$extlist[] = $this->lib('form')->format($value);
			}
		}
		$this->assign("extlist",$extlist);
		$this->view("register");
	}
}
?>
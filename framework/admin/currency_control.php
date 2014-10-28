<?php
/***********************************************************
	Filename: {phpok}/admin/currency_control.php
	Note	: 货币管理
	Version : 3.0
	Author  : qinggan
	Update  : 2011-07-16 07:15
***********************************************************/
class currency_control extends phpok_control
{
	public $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("currency");
		$this->assign("popedom",$this->popedom);
	}

	function index_f()
	{
		if(!$this->popedom['list']) error('您没有此项查看权限',$this->url,'error',10);
		$rslist = $this->model('currency')->get_list();
		$this->assign("rslist",$rslist);
		$this->view("currency_list");
	}

	function set_f()
	{
		$id = $this->get('id','int');
		if($id)
		{
			if(!$this->popedom['modify']) error("您没有此项修改权限",$this->url('currency'),'error',10);
		}
		else
		{
			if(!$this->popedom['add']) error("您没有此项添加权限",$this->url('currency'),'error',10);
		}
		if($id)
		{
			$rs = $this->model('currency')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		$this->view("currency_set");
	}

	function setok_f()
	{
		$id = $this->get('id','int');
		if($id)
		{
			if(!$this->popedom['modify']) error("您没有此项修改权限",$this->url('currency'),'error',10);
		}
		else
		{
			if(!$this->popedom['add']) error("您没有此项添加权限",$this->url('currency'),'error',10);
		}
		$array = array();
		$array["code"] = $this->get('code');
		$array["val"] = $this->get("val","float");
		$array["title"] = $this->get("title");
		$array["symbol_left"] = $this->get("symbol_left");
		$array["symbol_right"] = $this->get("symbol_right");
		$array["taxis"] = $this->get("taxis","int");
		$array["status"] = $this->get("status","int");
		$array["hidden"] = $this->get("hidden","int");
		#[检测相关信息]
		$error_url = $this->url('currency','set');
		if($id) $error_url = $this->url('currency','set','id='.$id);
		if(!$array["title"])
		{
			error("名称不允许为空",$error_url,'error');
		}
		if(!$array["code"])
		{
			error("编码不允许为空！",$error_url,'error');
		}
		$this->model('currency')->save($array,$id);
		error("货币设置操作成功",$this->url("currency"));
	}

	//删除货币方案
	function delete_f()
	{
		$id = $this->get("id",'int');
		if(!$id)
		{
			$this->json("操作非法，没有指定ID");
		}
		if(!$this->popedom['delete']) $this->json("您没有删除此项权限");
		$this->model('currency')->del($id);
		$this->json("ok",true);
	}

	//货币排序
	function sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort)) $this->json("更新排序失败");
		foreach($sort AS $key=>$value)
		{
			$key = intval($key);
			$value = intval($value);
			$this->model('currency')->update_sort($key,$value);
		}
		json_exit("更新排序成功",true);
	}

	function status_f()
	{
		if(!$this->popedom['delete']) $this->json("您没有启用/禁用操作权限");
		$id = $this->get('id','int');
		if(!$id) $this->json('未指定要操作的ID');
		$rs = $this->model('currency')->get_one($id);
		$status = $rs['status'] ? '0' : '1';
		$this->model('currency')->update_status($id,$status);
		$this->json($status,true);
	}
}
?>

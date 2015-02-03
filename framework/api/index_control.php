<?php
/***********************************************************
	Filename: {phpok}/api/index_control.php
	Note	: API接口默认接入
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月30日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	function index_f()
	{
		if(!$this->site['api_code'])
		{
			$this->json(P_Lang("系统未启用接口功能"));
		}
		$this->json(true);
	}

	function phpok_f()
	{
		if(!$this->site['api_code'])
		{
			$this->json(P_Lang("系统未启用接口功能"));
		}
		$token = $this->get("token");
		if(!$token)
		{
			$this->json(P_Lang("接口数据异常"));
		}
		$this->lib('token')->keyid($this->site['api_code']);
		$info = $this->lib('token')->decode($token);
		if(!$info)
		{
			$this->json(P_Lang('信息为空'));
		}
		$id = $info['id'];
		if(!$id)
		{
			$this->json(P_Lang('未指定数据调用中心ID'));
		}
		$param = $info['param'];
		if($param)
		{
			if(is_string($param))
			{
				$pm = array();
				parse_str($param,$pm);
				$param = $pm;
				unset($pm);
			}
		}
		$list = $this->call->phpok($id,$param);
		if(!$list)
		{
			$this->json(P_Lang("没有获取到数据"));
		}
		$tpl = $this->get("tpl");
		if($tpl && $this->tpl->check_exists($tpl))
		{
			$this->assign("rslist",$list);
			$info = $this->fetch($tpl);
			$this->json($info,true);
		}
		$this->json($list,true);
	}
}
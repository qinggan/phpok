<?php
/***********************************************************
	Filename: .php
	Note	: API
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年11月2日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class api_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	//取得验证ID
	function index_f()
	{
		if(!$this->site['api'])
		{
			$this->json(1015);
		}
		$apikey = $this->get('apikey');
		$account = $this->get('account');
		if(!$apikey)
		{
			$this->json(1014);
		}
		$rs = $this->model('list')->get_one_condition("l.title='".$apikey."'",$this->site['api']);
		if(!$rs)
		{
			$this->json(1016);
		}
	}

	function phpok_f()
	{
		$this->json(P_Lang('此接口有安全问题，暂停使用！'));
		$id = $this->get('id');
		if(!$id)
		{
			$this->json('未指定数据调用中心ID');
		}
		$param = $this->get('param');
		if($param)
		{
			$intval = array('pid','cateid','user_id');
			foreach($param as $key=>$value)
			{
				if(in_array($key,$intval))
				{
					$param[$key] = intval($value);
				}
				else
				{
					$value = strtolower($value);
					$param[$key] = str_replace(array('union','select','update','delete','insert','*','where','from'),"",$value);
				}
			}
		}
		
		$list = $this->call->phpok($id,$param);
		if(!$list) $this->json('ok',true,true,false);
		$tpl = $this->get("tpl");
		if($tpl && $this->tpl->check_exists($tpl))
		{
			$this->assign("rslist",$list);
			$info = $this->fetch($tpl);
			$this->json($info,true,true,false);
		}
		$this->json($list,true);
	}
}
?>
<?php
/***********************************************************
	Filename: {phpok}/admin/form_control.php
	Note	: 控制器管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:45
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id)
		{
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid","int");
		$etype = $this->get("etype");
		if(!$etype) $etype = "ext";
		if($eid) {
			if($etype == "fields")
			{
				$rs = $this->model('fields')->get_one($eid);
			}
			elseif($etype == "module")
			{
				$rs = $this->model('module')->field_one($eid);
			}
			elseif($etype == "user")
			{
				$rs = $this->model('user')->field_one($eid);
			}
			else
			{
				$rs = $this->model('ext')->get_one($eid);
			}
			if($rs["ext"])
			{
				$ext = unserialize($rs["ext"]);
				foreach($ext AS $key=>$value)
				{
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
		}
		$this->lib('form')->config($id);
	}
}
?>
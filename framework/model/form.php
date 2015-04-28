<?php
/***********************************************************
	Filename: {phpok}/model/form.php
	Note	: 表单选择器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-12 17:34
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class form_model_base extends phpok_model
{
	public $info = "";
	function __construct()
	{
		parent::model();
		$this->info = $this->lib('xml')->read($this->dir_phpok.'system.xml');
	}

	public function __destruct()
	{
		parent::__destruct();
		unset($this);
	}

	function form_all()
	{
		if($this->info['form'])
		{
			return $this->info['form'];
		}
		return false;
	}

	function format_all()
	{
		if($this->info['format'])
		{
			return $this->info['format'];
		}
		return false;
	}

	//字段类型
	function field_all()
	{
		if($this->info['field'])
		{
			return $this->info['field'];
		}
		return false;
	}

	//读取表单下的子项目信息
	public function project_sublist($pid)
	{
		$sql = "SELECT id as val,title FROM ".$this->db->prefix."project WHERE parent_id=".intval($pid)." AND status=1 ";
		$sql.= "ORDER BY taxis ASC,id DESC";
		return $this->db->get_all($sql);
	}
}
?>
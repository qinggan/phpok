<?php
/***********************************************************
	Filename: plugins/identifier/admin.php
	Note	: 标识串自动生成工具
	Version : 4.0
	Author  : qinggan
	Update  : 2012-08-22 19:59
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_identifier extends phpok_plugin
{
	var $path;
	function __construct()
	{
		parent::plugin();
		$this->path = str_replace("\\","/",dirname(__FILE__))."/";
	}

	private function create_btn()
	{
		$pinfo = $this->plugin_info();
		$this->assign("pinfo",$pinfo);
		echo $this->plugin_tpl('btn.html');
	}

	//分类标识串增加取得翻译插件
	public function html_cate_set_body()
	{
		$this->create_btn();
	}

	//弹出窗口的分类增加
	public function html_cate_add_body()
	{
		$this->create_btn();
	}

	//内容标识串
	public function html_list_edit_body()
	{
		$this->create_btn();
	}

	//项目标识串
	public function html_project_set_body()
	{
		$this->create_btn();
	}

	//数据调用中心
	public function html_call_set_body()
	{
		$this->create_btn();
	}
}
?>
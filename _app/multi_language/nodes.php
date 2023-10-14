<?php
/**
 * 接入节点_用于管理多语言，支持批量翻译等操作
 * @作者 phpok.com <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年10月13日 18时20分
**/
namespace phpok\app\multi_language;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class nodes_phpok extends \_init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function admin_before()
	{
		//公共管理后台数据未执行前操作
	}

	public function admin_after()
	{
		//公共管理后台数据执行后未输出前
	}

	public function www_before()
	{
		//前台未执行前
	}

	public function www_after()
	{
		//数据执行后未输出前
	}

	public function PHPOK_arclist()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_arc()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_project()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_catelist()
	{
		//这里开始编写PHP代码
	}

	public function PHPOK_cate()
	{
		//这里开始编写PHP代码
	}

	/**
	 * 删除主题时触发删除这个应用事件
	 * @参数 $id 主题ID
	 * @返回 true 
	**/
	public function system_admin_title_delete($id)
	{
		//这里开始编写PHP代码
		return true;
	}

	/**
	 * 更新或添加保存完主题后触发动作
	 * @参数 $id 主题ID
	 * @参数 $project 项目信息，数组
	 * @返回 true 
	**/
	public function system_admin_title_success($id,$project)
	{
		//这里开始编写PHP代码
		return true;
	}

	/**
	 * 初始化站点信息接口，无参数，需要通过data来获取信息
	**/
	public function system_init_site()
	{
		$site_rs = $this->data("site_rs");
		//这里开始编写PHP代码
		$this->data("site_rs",$site_rs);
		return true;
	}

}

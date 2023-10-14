<?php
/**
 * HTML节点_用于管理多语言，支持批量翻译等操作
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
class html_phpok extends \_init_node_html
{
	public function __construct()
	{
		parent::__construct();
	}

	public function admin_before()
	{
		//这是后台页头公共页的地方
		//$this->_show("public");
	}

	public function admin_after()
	{
		//这是后台页脚公共页的地方
		//$this->_show("public");
	}

	public function admin_list_edit_after()
	{
		//这是后台内容编辑页的页脚的地方
		//$this->_show("public");
	}

	public function www_before()
	{
		//这是前台页头公共页的地方
		//$this->_show("public");
	}

	public function www_after()
	{
		//这是前台页脚公共页的地方
		//$this->_show("public");
	}

	public function www_project_index_after()
	{
		//项目页里页脚改写模板的地方
		//$this->_show("public");
	}

	public function www_content_index_after()
	{
		//内容页里页脚改写模板的地方
		//$this->_show("public");
	}

}

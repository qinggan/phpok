<?php
/**
 * HTML节点_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
namespace phpok\app\pm;
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
		//
	}

	public function www_after()
	{
		//
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

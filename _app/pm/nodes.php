<?php
/**
 * 接入节点_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
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
class nodes_phpok extends \_init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	//在所有页面上显示短消息功能
	public function www_after()
	{
		$uid = $this->session->val('user_id');
		if($uid){
			$data = array();
			$condition = "p.isread=0 AND p.user_id='".$uid."'";
			$data['total'] = $this->model('pm')->get_count($condition);
			$this->tpl->assign('pm',$data);
		}
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

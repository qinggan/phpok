<?php
/**
 * 接入节点_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年11月28日 11时26分
**/
namespace phpok\app\weixin;
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

	public function api_index_index_after($info)
	{
		if(!$info || !is_array($info)){
			return false;
		}
		$rs = $this->model('weixin')->config();
		if(isset($rs['mp']['app_secret'])){
			unset($rs['mp']['app_secret']);
		}
		if(isset($rs['ap']['app_secret'])){
			unset($rs['ap']['app_secret']);
		}
		if(isset($rs['op']['app_secret'])){
			unset($rs['op']['app_secret']);
		}
		$info['weixin'] = $rs;
		$this->success($info);
	}

	public function www_after()
	{
		$rs = $this->model('weixin')->config();
		if(isset($rs['mp']['app_secret'])){
			unset($rs['mp']['app_secret']);
			$this->lib('weixin')->app_id($rs['mp']['app_id']);
			$rs = $this->tpl->val('rs');
			$cate_rs = $this->tpl->val('cate_rs');
			$page_rs = $this->tpl->val('page_rs');
			$wxurl = $rs ? $rs['url'] : ($cate_rs ? $cate_rs['url'] : ($page_rs ? $page_rs['url'] : $app->url));
			$wxconfig = $this->lib('weixin')->jsapi_config($wxurl);
			$this->tpl->assign('wxconfig',$wxconfig);
		}
		if(isset($rs['ap']['app_secret'])){
			unset($rs['ap']['app_secret']);
		}
		if(isset($rs['op']['app_secret'])){
			unset($rs['op']['app_secret']);
		}
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

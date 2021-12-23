<?php
/**
 * HTML节点_集成微信所有接口功能，包括公众号（mp），开放平台（op），小程序（ap）等相关服务
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
		$config = $this->model('weixin')->config_one('op');
		if($config && $config['app_id']){
			$state = $this->get_state('weixin');
			$url = 'https://open.weixin.qq.com/connect/qrconnect?appid='.$config['app_id'];
			$url.= "&redirect_uri=".urlencode($this->url('weixin','op_login'));
			$url.= "&response_type=code&scope=snsapi_login&state=".$state."#wechat_redirect";
			$this->assign('wxlink',$url);
		}
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

	private function get_state($type='weixin')
	{
		$id = $type.'_state';
		if($this->session->val($id)){
			return $this->session->val($id);
		}
		$info = md5(uniqid(rand(), true));
		$this->session->assign($id,$info);
		return $info;
	}
}

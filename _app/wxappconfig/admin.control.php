<?php
/**
 * 
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年11月15日
**/
namespace phpok\app\control\wxappconfig;

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('wxappconfig');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$rs = $this->model('wxappconfig')->get_one();
		$text_color = form_edit('text_color',$rs['text_color'],'text','form_btn=color&ext_include_3=1');
		$this->assign('text_color',$text_color);
		$text_color_highlight = form_edit('text_color_highlight',$rs['text_color_highlight'],'text','form_btn=color&ext_include_3=1');
		$this->assign('text_color_highlight',$text_color_highlight);
		$tab_bgcolor = form_edit('tab_bgcolor',$rs['tab_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('tab_bgcolor',$tab_bgcolor);
		$top_bgcolor = form_edit('top_bgcolor',$rs['top_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('top_bgcolor',$top_bgcolor);
		$usercp_bgcolor = form_edit('usercp_bgcolor',$rs['usercp_bgcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('usercp_bgcolor',$usercp_bgcolor);
		$usercp_txtcolor = form_edit('usercp_txtcolor',$rs['usercp_txtcolor'],'text','form_btn=color&ext_include_3=1');
		$this->assign('usercp_txtcolor',$usercp_txtcolor);
		$this->assign('rs',$rs);
		$this->display('admin_index');
	}

	public function save_f()
	{
		$data = array();
		$data['title'] = $this->get('title');
		$data['top_bgcolor'] = $this->get('top_bgcolor');
		$data['top_txtcolor'] = $this->get('top_txtcolor');
		$data['text_color'] = $this->get('text_color');
		$data['text_color_highlight'] = $this->get('text_color_highlight');
		$data['tab_bgcolor'] = $this->get('tab_bgcolor');
		$data['tab_bordercolor'] = $this->get('tab_bordercolor');
		$data['usercp_bgcolor'] = $this->get('usercp_bgcolor');
		$data['usercp_bgimg'] = $this->get('usercp_bgimg');
		$data['usercp_txtcolor'] = $this->get('usercp_txtcolor');
		$this->model('wxappconfig')->save($data);
		$this->success();
	}
}

<?php
/**
 * 标识串自动生成工具
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_identifier extends phpok_plugin
{
	private $youdao = false;
	private $kunwu = false;
	function __construct()
	{
		parent::plugin();
	}

	private function create_btn()
	{
		$pinfo = $this->_info();
		if($pinfo && $pinfo['param']){
			if($pinfo['param']['youdao_appid'] && $pinfo['param']['youdao_appkey']){
				$this->youdao = true;
			}
			if($pinfo['param']['phpok_appid'] && $pinfo['param']['phpok_appkey']){
				$this->kunwu = true;
			}
		}
		$this->assign("pinfo",$pinfo);
		$this->assign('is_youdao',$this->youdao);
		$this->assign('is_kunwu',$this->kunwu);
		$this->_show('btn.html');
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
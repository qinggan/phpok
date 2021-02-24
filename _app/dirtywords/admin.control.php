<?php
/**
 * 后台管理_用于过滤敏感的，粗爆的字词，一行一个，用户提交表单数据时直接报错
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年09月04日 15时50分
**/
namespace phpok\app\control\dirtywords;
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
		$this->popedom = appfile_popedom('dirtywords');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$content = $this->model('dirtywords')->read();
		$this->assign('content',$content);
		$this->display('admin_index');
	}

	public function save_f()
	{
		$content = $this->get('content');
		$this->model('dirtywords')->save($content);
		$this->success();
	}

	public function setting_f()
	{
		$rs = $this->model('dirtywords')->config();
		$this->assign('rs',$rs);
		$this->display('admin_setting');
	}

	public function setting_save_f()
	{
		$data = array();
		$data['aip_status'] = $this->get('aip_status','int');
		$data['aip_appid'] = $this->get('aip_appid');
		$data['aip_apikey'] = $this->get('aip_apikey');
		$data['aip_secret'] = $this->get('aip_secret');
		if(!$data['aip_appid']){
			$this->error(P_Lang('百度应用 ID 不能为空'));
		}
		if(!$data['aip_apikey']){
			$this->error(P_Lang('百度接口 Key 不能为空'));
		}
		if(!$data['aip_secret']){
			$this->error(P_Lang('百度接口密钥不能为空'));
		}
		$this->model('dirtywords')->config($data);
		$this->success();
	}
}

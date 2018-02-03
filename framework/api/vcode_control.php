<?php
/**
 * 验证码接口
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年11月22日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class vcode_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$info = $this->lib("vcode")->word();
		$appid = $this->get("id");
		if(!$appid){
			$appid = $this->app_id;
		}
		if($appid == 'admin'){
			$_SESSION["vcode_".$appid] = md5(strtolower($info));
		}else{
			$_SESSION["vcode"] = md5(strtolower($info));
			$_SESSION["vcode_".$appid] = md5(strtolower($info));
		}
		$this->lib("vcode")->create();
	}
}
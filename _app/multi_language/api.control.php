<?php
/**
 * 接口应用_用于管理多语言，支持批量翻译等操作
 * @作者 phpok.com <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年10月13日 18时20分
**/
namespace phpok\app\control\multi_language;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		//$info = "";
		//$this->error($info);
		$this->success();
	}
}

<?php
/**
 * 接口端
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2023年3月7日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_control extends phpok_control
{
	private $form_list;
	private $field_list;
	private $popedom;
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 取得全部常用字段列表
	**/
	public function index_f()
	{
		$this->success();
	}

	/**
	 * 添加字段
	**/
	public function set_f()
	{
		$this->success();
	}

	/**
	 * 保存表单信息
	**/
	public function save_f()
	{
		$this->success();
	}

	public function delete_f()
	{
		$this->success();
	}

	public function config_f()
	{
		$this->success();
	}

	/**
	 * 自定义宽度保留
	**/
	public function width_f()
	{
		$this->success();
	}

	public function filemanage_f()
	{
		$this->success();
	}
}
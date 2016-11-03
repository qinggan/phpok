<?php
/**
 * 各种常用验证接口
 * @package phpok\api
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年09月11日
**/

if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class check_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 邮箱验证
	 * @参数 val 要验证的邮箱
	 * @返回 Json数据，status为1时表示验证通过
	**/
	public function email_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('邮箱不能为空'));
		}
		if(!$this->lib('common')->email_check($val)){
			$this->error(P_Lang('邮箱不合法'));
		}
		$this->success();
	}

	/**
	 * 电话验证，支持400，座机及手机号验证，仅验证合法性，不验证是否已存在
	 * @参数 val 要验证的电话
	 * @返回 Json数据，status为1时表示验证通过
	**/
	public function tel_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('电话不能为空'));
		}
		if(!$this->lib('common')->tel_check($val)){
			$this->error(P_Lang('电话不合法'));
		}
		$this->success();
	}

	/**
	 * 手机验证，仅验证合法性，不验证是否已存在
	 * @参数 val 要验证的手机
	 * @返回 Json数据，status为1时表示验证通过
	**/
	public function mobile_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('手机号不能为空'));
		}
		if(!$this->lib('common')->tel_check($val,'mobile')){
			$this->error(P_Lang('手机号不合法'));
		}
		$this->success();
	}

	/**
	 * 身份证验证，仅验证合法性，不验证是否已存在
	 * @参数 val 要验证的身份证
	 * @返回 Json数据，status为1时表示验证通过
	**/
	public function idcard_f()
	{
		$val = $this->get('val');
		if(!$val){
			$this->error(P_Lang('身份证号不能为空'));
		}
		if(!$this->lib('common')->idcard_check($val)){
			$this->error(P_Lang('身份证号不合法'));
		}
		$this->success();
	}	
}

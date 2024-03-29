<?php
/**
 * 邮件相关操作
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月30日
 * @更新 2023年2月8日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class email_control extends phpok_control
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 邮件发送
	 * @参数 email 仅限管理员登录后可直接通过email来发送邮件
	 * @参数 token 限前台使用，用于普通用户使用PHPOK服务器来发送邮件
	 * @参数 title 邮件标题
	 * @参数 content 邮件内容，支持HTML
	 * @参数 fullname 收件人姓名，留空使用Email中@的前半部分做名称
	**/
	public function index_f()
	{
		if($this->session->val('admin_id')){
			$email = $this->get('email');
			if(!$email){
				$this->error(P_Lang('Email不能为空'));
			}
		}else{
			$token = $this->get('token');
			if(!$token){
				$this->error(P_Lang('Email获取异常，未指定Token信息'));
			}
			$info = $this->lib('token')->decode($token);
			if(!$info || !$info['email']){
				$this->error(P_Lang('异常，内容不能为空'));
			}
			$email = $info['email'];
			if(!$email){
				$this->error(P_Lang('Token中没有Email，请检查'));
			}
		}
		$title = $this->get('title');
		$content = $this->get('content','html');
		if(!$content){
			$this->error(P_Lang('邮件内容不能为空'));
		}
		if(!$title){
			$title = phpok_cut($content,50,'…');
		}
		$email_server = $this->model('gateway')->get_default('email');
		if(!$email_server){
			$this->error(P_Lang('SMTP未配置好'));
		}
		$list = explode(',',$email);
		//如果仅只有一个Email时
		if(count($list) == 1){
			if(!$this->lib('common')->email_check($email)){
				$this->error(P_Lang('Email邮箱不符合要求'));
			}
			$fullname = $this->get('fullname');
			if(!$fullname){
				$fullname = str_replace(strstr($value,'@'),'',$email);
			}
			$info = $this->lib('email')->send_mail($email,$title,$content,$fullname);
			if(!$info){
				$this->error($this->lib('email')->error());
			}
			$this->success();
		}
		foreach($list as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			if(!$this->lib('common')->email_check($value)){
				continue;
			}
			$value_name = str_replace(strstr($value,'@'),'',$value);
			$info = $this->lib('email')->send_mail($value,$title,$content,$value_name);
			if(!$info){
				phpok_log($this->lib('email')->error());
			}
		}
		$this->success();		
	}

	public function chk_f()
	{
		$email = false;
		$server = $this->model('gateway')->get_default('email');
		if($server){
			$email = true;
		}
		$this->success($email);
	}
}
<?php
/***********************************************************
	Filename: {phpok}/api/email_control.php
	Note	: 邮件相关操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2015年06月13日 11时31分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class email_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$email = $this->get('email');
		if(!$email){
			$this->json(P_Lang('Email不能为空'));
		}
		$title = $this->get('title');
		$content = $this->get('content','html');
		if(!$content){
			$this->json(P_Lang('邮件内容不能为空'));
		}
		if(!$title){
			$title = phpok_cut($content,50,'…');
		}
		if(!$this->site['email_server'] || !$this->site['email_account'] || !$this->site['email_pass'] || !$this->site['email']){
			$this->json(P_Lang('SMTP未配置好'));
		}
		$list = explode(',',$email);
		foreach($list AS $key=>$value){
			$value = trim($value);
			if($value && phpok_check_email($value)){
				$value_name = str_replace(strstr($value,'@'),'',$value);
				$info = $this->lib('email')->send_mail($value,$title,$content,$value_name);
				if(!$info){
					$this->json($this->lib('email')->error());
				}
			}
		}
		$this->json(true);		
	}

	//会员注册成功后的邮件通知
	public function register_f()
	{
		if($_SESSION['user_id']){
			$this->json(P_Lang('您已经是会员，不能执行这个操作'));
		}
		$group_id = $this->get('group_id','int');
		if($group_id){
			$group_rs = $this->model("usergroup")->get_one($group_id);
			if(!$group_rs || !$group_rs['status']) $group_id = 0;
		}
		if(!$group_id){
			$group_rs = $this->model('usergroup')->get_default();
			if(!$group_rs || !$group_rs["status"]){
				$this->json(P_Lang('会员组不存在或未启用'));
			}
			$group_id = $group_rs["id"];
		}
		if(!$group_id) $this->json(P_Lang('会员组ID不存在'));
		$gid = $group_id;
		//检测组是否符合要求
		if(!$group_rs['register_status'] || $group_rs['register_status'] == '1'){
			$this->json(P_Lang('会员组不支持邮箱注册认证'));
		}
		if(!$group_rs['tbl_id']){
			$this->json(P_Lang('未绑定相应的注册项目'));
		}
		$p_rs = $this->model('project')->get_one($group_rs['tbl_id'],false);
		if(!$p_rs['module']){
			$this->json(P_Lang('未绑定相应的模块'));
		}
		$email_rs = $this->model('email')->get_identifier('register_code');
		if(!$email_rs){
			 $this->json(P_Lang('通知邮箱模板不存在'));
		}
		$email = $this->get('email');
		if(!$email){
			$this->json(P_Lang('邮箱不存在'));
		}
		if(!$this->lib('common')->email_check($email)){
			$this->json(P_Lang('邮箱验证不通过'));
		}
		$uid = $this->model('user')->uid_from_email($email);
		if($uid){
			$this->json(P_Lang('邮箱已被使用'));
		}
		$title = $this->lib('common')->str_rand(10).$this->time;
		$array = array('site_id'=>$this->site['id'],'module_id'=>$p_rs['module'],'project_id'=>$p_rs['id']);
		$array['title'] = $title;
		$array['dateline'] = $this->time;
		$array['status'] = 1;
		$insert_id = $this->model('list')->save($array);
		if(!$insert_id){
			$this->json(P_Lang('数据存储失败，请联系管理员'));
		}
		$ext = array('id'=>$insert_id,'site_id'=>$p_rs['site_id'],'project_id'=>$p_rs['id']);
		$ext['account'] = '';
		$this->model('list')->save_ext($ext,$p_rs['module']);
		$ext = '_code='.rawurlencode($title).'&group_id='.$group_id.'&email='.rawurlencode($email);
		$link = $this->url('register','',$ext,'www');
		$this->assign('link',$link);
		$this->assign('email',$email);
		$title = $this->fetch($email_rs["title"],"content");
		$content = $this->fetch($email_rs["content"],"content");
		$info = $this->lib('email')->send_mail($email,$title,$content);
		if(!$info){
			$this->json($this->lib('email')->error());
		}
		$this->json(true);
	}
}
?>
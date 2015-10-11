<?php
/***********************************************************
	Filename: {phpok}/www/index_control.php
	Note	: 网站首页及APP的封面页
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2015年06月06日 09时09分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$tmp = $this->model('id')->id('index',$this->site['id'],true);
		$tplfile = 'index';
		if($tmp){
			$pid = $tmp['id'];
			$page_rs = $this->call->phpok('_project',array('pid'=>$pid));
			$this->phpok_seo($page_rs);
			$this->assign("page_rs",$page_rs);
			if($page_rs["tpl_index"] && $this->tpl->check_exists($page_rs["tpl_index"])){
				$tplfile = $page_rs["tpl_index"];
			}
			unset($page_rs);
		}
		$this->view($tplfile);
	}

	public function tips_f()
	{
		$info = $this->get('info');
		$backurl = $this->get('back');
		if(!$info){
			$info = P_Lang('友情提示');
		}
		if(!$backurl){
			$backurl = $this->url;
		}
		$this->assign('url',$backurl);
		$this->assign('tips',$info);
		$this->view('tips');
	}

	//推荐链执行
	public function linker($code)
	{
		$rs = $this->model('user')->code_one($code);
		if(!$rs || !$rs['user_id']){
			$this->_location('index.php');
		}
		//增加点击率
		$this->model('user')->code_addhits($rs['id']);
		//已登录的会员，跳过
		if($_SESSION['user_id']){
			
			if($rs['link']){
				$this->_location($rs['link']);
			}
			$this->_location('index.php');
		}
		$user = $this->model('user')->get_one($rs['user_id']);
		if(!$user){
			$this->_location('index.php');
		}
		$_SESSION['introducer'] = $user['id'];
	}
}
?>
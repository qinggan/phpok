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
		$this->model('task')->set_title_status();
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'index';
		}
		//检测是否有指定
		$tmp = $this->model('id')->id('index',$this->site['id'],true);
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
		if($this->config['async']['status']){
			$taskurl = api_url('task','index',$this->session->sid()."=".$this->session->sessid(),true);
			if($this->config['async']['type']){
				$this->lib('async')->loadtype($this->config['async']['type']);
			}
			$this->lib('async')->start($taskurl);
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

	/**
	 * 推荐人
	 * @参数 uid 推荐人ID
	**/
	public function link_f()
	{
		$uid = $this->get('uid','int');
		if(!$uid){
			$this->_location($this->config['www_file']);
		}
		$rs = $this->model('user')->get_one($uid,'id',false,false);
		if(!$rs){
			$this->_location($this->config['www_file']);
		}
		if($this->session->val('user_id')){
			$this->_location($this->config['www_file']);
		}
		$this->session->assign('introducer',$uid);
		$this->_location($this->url('register'));
	}
}
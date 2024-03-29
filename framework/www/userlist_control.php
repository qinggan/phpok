<?php
/**
 * 用户列表
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年07月01日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class userlist_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$gid = $this->get('gid');
		$array = array();
		$pageurl = $this->url('userlist');
		if($gid){
			$array['group_id'] = $gid;
			$pageurl .= '&gid='.$gid;
		}
		$is_avatar = $this->get('is_avatar','int');
		if($is_avatar){
			$array['is_avatar'] = 1;
			$pageurl .= '&is_avatar=1';
		}
		$pageid = $this->get($this->config['pageid'],'int');
		$array['pageid'] = $pageid;
		$array['psize'] = $this->config['psize'];
		$data = $this->call->phpok('_userlist',$array);
		if($data['total']){
			$this->assign('rslist',$data['rslist']);
			$this->assign('pageurl',$pageurl);
			$this->assign('total',$data['total']);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$array['psize']);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'userlist';
		}
		$this->view($tplfile);
	}
}
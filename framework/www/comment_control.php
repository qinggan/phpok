<?php
/***********************************************************
	备注：评论列表读取
	版本：5.0.0
	官网：www.phpok.com
	作者：qinggan <qinggan@188.com>
	更新：2016年02月07日
***********************************************************/ 
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class comment_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($_SESSION['user_id']);
		if(!$groupid){
			error(P_Lang('无法获取前端用户组信息'),'','error');
		}
		$this->user_groupid = $groupid;
	}

	public function index_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('content')->get_one($id,true);
		if(!$rs){
			$this->error(P_Lang('内容不存在'),$this->url,5);
		}
		if(!$rs['project_id']){
			$this->error(P_Lang('未绑定项目'),$this->url,5);
		}
		if(!$rs['module_id']){
			$this->error(P_Lang('未绑定相应的模块'));
		}
		$project = $this->call->phpok('_project',array('pid'=>$rs['project_id']));
		if(!$project || !$project['status']){
			$this->error(P_Lang('项目不存在或未启用'));
		}
		if(!$this->model('popedom')->check($project['id'],$this->user_groupid,'read')){
			$this->error(P_Lang('您没有阅读此文章权限'));
		}
		$this->assign('page_rs',$project);
		$url_id = $rs['identifier'] ? $rs['identifier'] : $rs['id'];
		$tmpext = '&project='.$project['identifier'];
		if($project['cate'] && $rs['cate_id']){
			$tmpext.= '&cateid='.$rs['cate_id'];
		}
		$rs['url'] = $this->url($url_id,'',$tmpext,'www');
		$this->assign("rs",$rs);
		$psize = $project['psize'] ? $project['psize'] : $this->config['psize'];
		if(!$psize){
			$psize = 30;
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$comment = phpok('_comment','tid='.$rs['id'],'pageid='.$pageid,'psize='.$psize);
		if($comment){
			$total = $comment['total'];
			$this->assign('total',$total);
			$this->assign('psize',$psize);
			$this->assign('pageid',$pageid);
			$this->assign('pageurl',$this->url('comment','','id='.$rs['id']));
			$this->assign('rslist',$comment['rslist']);
			$this->assign('avatar',$comment['avatar']);
			$this->assign('nickname',$comment['user']);
		}
		$this->view('comment');
	}
}

?>
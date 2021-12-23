<?php
/**
 * 网站前台_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
 * @作者 phpok.com <admin@phpok.com>
 * @主页 www.phpok.com
 * @版本 5.x
 * @许可 www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年07月16日 10时13分
**/
namespace phpok\app\control\social;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class www_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$this->display('www_index');
	}

	public function help_f()
	{
		$this->addcss('static/md-editor/editormd.css');
		$this->addjs('static/md-editor/lib/marked.min.js');
		$this->addjs('static/md-editor/lib/prettify.min.js');
		$this->addjs('static/md-editor/lib/raphael.min.js');
		$this->addjs('static/md-editor/lib/underscore.min.js');
		$this->addjs('static/md-editor/lib/sequence-diagram.min.js');
		$this->addjs('static/md-editor/lib/flowchart.min.js');
		$this->addjs('static/md-editor/lib/jquery.flowchart.min.js');
		$this->addjs('static/md-editor/editormd.min.js');
		$file = $this->dir_app.'social/www-help.md';
		if(file_exists($file)){
			$content = file_get_contents($file);
			$this->assign('content',$content);
		}
		$this->display('www-help');
	}

	/**
	 * 黑名单
	**/
	public function blacklist_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$offset = ($pageid-1)*$psize;
		$condition = "";
		$keywords = $this->get('keywords');
		$pageurl = $this->url('social','blacklist');
		if($keywords){
			$condition = "u.user LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('social')->black_count($this->session->val('user_id'),$condition);
		if($total){
			$rslist = $this->model('social')->black_list($this->session->val('user_id'),$offset,$psize,$condition);
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
		}
		$this->display('www-blacklist');
	}

	/**
	 * 粉丝单
	**/
	public function fans_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$offset = ($pageid-1)*$psize;
		$condition = "";
		$keywords = $this->get('keywords');
		$pageurl = $this->url('social','fans');
		if($keywords){
			$condition = "u.user LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('social')->fans_count($this->session->val('user_id'),$condition);
		if($total){
			$rslist = $this->model('social')->fans_list($this->session->val('user_id'),$offset,$psize,$condition);
			$uids = array();
			if($rslist){
				foreach($rslist as $key=>$value){
					$uids[] = $value['id'];
				}
				$uinfo = $this->model('social')->links_list($this->session->val('user_id'),$uids,"is_idol=1");
				$uids = array();
				if($uinfo){
					foreach($uinfo as $key=>$value){
						$uids[] = $value['who_id'];
					}
				}
				foreach($rslist as $key=>$value){
					$value['is_idol'] = ($uids && in_array($value['id'],$uids)) ? true : false;
					$rslist[$key] = $value; 
				}
			}
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
		}
		$this->display('www-fans');
	}

	public function homepage_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$rs = $this->model('social')->homepage($this->session->val('user_id'));
		$this->assign('rs',$rs);
		$this->display("www-homepage");
	}

	public function idol_f()
	{
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$offset = ($pageid-1)*$psize;
		$condition = "";
		$keywords = $this->get('keywords');
		$pageurl = $this->url('usercp','idol');
		if($keywords){
			$condition = "u.user LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		$total = $this->model('social')->idol_count($this->session->val('user_id'),$condition);
		if($total){
			$rslist = $this->model('social')->idol_list($this->session->val('user_id'),$offset,$psize,$condition);
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
		}
		$this->display('www-idol');
	}
}

<?php
/**
 * 收藏夹
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月04日
**/
namespace phpok\app\control\fav;

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
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('您还未登录，不能执行此操作'));
		}
		$condition = "f.user_id='".$this->session->val('user_id')."'";
		$total = $this->model('fav')->get_count($condition);
		if($total){
			$pageurl = $this->url('fav');
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->get('psize','int');
			if(!$psize){
				$psize = $this->config['psize'] ? $this->config['psize'] : 30;
			}
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('fav')->get_all($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('pageid',$pageid);
			$this->assign('total',$total);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_fav';
		}
		$this->display($tplfile);
	}
}

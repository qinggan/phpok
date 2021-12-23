<?php
/**
 * 网站前台_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年12月25日 22时25分
**/
namespace phpok\app\control\pm;
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
			$this->error(P_Lang('仅限会员才能查看'));
		}
		$pageurl = $this->url($this->ctrl,$this->func);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$offset = ($pageid-1) * $psize;
		$condition = "p.user_id='".$this->session->val('user_id')."'";
		$total = $this->model('pm')->get_count($condition);
		if($total){
			$rslist = $this->model('pm')->get_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('total',$total);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('offset',$offset);
		}
		$this->display('www-index');
	}
}

<?php
/**
 * 收藏夹管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
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

class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('fav');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有查看权限'));
		}
		$keywords = $this->get('keywords');
		$condition = '1=1';
		$pageurl = $this->url('fav');
		if($keywords && is_array($keywords)){
			if(isset($keywords['title']) && $keywords['title'] != ''){
				$condition .= " AND f.title LIEK '%".$keywords['title']."%'";
				$pageurl .= "&keywords[title]=".rawurlencode($keywords['title']);
			}
			if(isset($keywords['note']) && $keywords['note'] != ''){
				$condition .= " AND f.note LIEK '%".$keywords['note']."%'";
				$pageurl .= "&keywords[note]=".rawurlencode($keywords['note']);
			}
			if(isset($keywords['thumb']) && $keywords['thumb'] != ''){
				$condition .= " AND f.thumb LIEK '%".$keywords['thumb']."%'";
				$pageurl .= "&keywords[thumb]=".rawurlencode($keywords['thumb']);
			}
			if(isset($keywords['user']) && $keywords['user'] != ''){
				$condition .= " AND u.user='".$keywords['user']."'";
				$pageurl .= "&keywords[user]=".rawurlencode($keywords['user']);
			}
			if(isset($keywords['email']) && $keywords['email'] != ''){
				$condition .= " AND u.email='".$keywords['email']."'";
				$pageurl .= "&keywords[email]=".rawurlencode($keywords['email']);
			}
			if(isset($keywords['mobile']) && $keywords['mobile'] != ''){
				$condition .= " AND u.mobile='".$keywords['user']."'";
				$pageurl .= "&keywords[mobile]=".rawurlencode($keywords['mobile']);
			}
			$this->assign('keywords',$keywords);
		}
		$startdate = $this->get('startdate');
		if($startdate && strtotime($startdate)>0){
			$condition .= " AND f.addtime>=".strtotime($startdate);
			$pageurl .= "&startdate=".rawurlencode($startdate);
		}
		$stopdate = $this->get('stopdate');
		if($stopdate && strtotime($stopdate)>0){
			$condition .= " AND f.addtime<=".strtotime($stopdate);
			$pageurl .= "&stopdate=".rawurlencode($stopdate);
		}
		$total = $this->model('fav')->get_count($condition);
		if($total){
			$this->assign('total',$total);
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('fav')->get_all($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->display('admin_index');
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除权限'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要删除的ID'));
		}
		$this->model('fav')->del($id);
		$this->success();
	}
}

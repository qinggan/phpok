<?php
/**
 * 后台管理_用于平台用户内部沟通交流，同样适用于APP互动交流，发留言有副本，接收人员也有一份副本
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
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('pm');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有查看站内短信权限'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 20;
		$offset = ($pageid-1)*$psize;
		$condition = "1=1";
		$keywords = $this->get('keywords');
		$pageurl = $this->url('pm');
		if($keywords){
			$condition = " AND (u.user LIKE '%".$keywords."%' OR p.content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		$startdate = $this->get('startdate');
		if($startdate){
			$condition .= " AND p.addtime>=".strtotime($startdate)." ";
			$pageurl .= "&startdate=".rawurlencode($startdate);
			$this->assign('startdate',$startdate);
		}
		$stopdate = $this->get('stopdate');
		if($stopdate){
			$condition .= " AND p.addtime<".(strtotime($stopdate)+86400)." ";
			$pageurl .= "&stopdate=".rawurlencode($stopdate);
			$this->assign('stopdate',$stopdate);
		}
		$total = $this->model('pm')->get_count($condition);
		if($total){
			$rslist = $this->model('pm')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
			$this->assign('pagelist',$pagelist);
		}
		$this->display('admin-index');
	}

	public function add_f()
	{
		if(!$this->popedom['add']){
			$this->error(P_Lang('您没有添加站内短信权限'));
		}
		$who = form_edit('user_id',0,'user');
		$this->assign('who',$who);
		$this->display("admin-add");
	}

	public function save_f()
	{
		$user_id = $this->get('user_id');
		if(!$user_id){
			$this->error(P_Lang('会员不能为空'));
		}
		$content = $this->get('content');
		if(!$content){
			$this->error(P_Lang('消息内容不能为空'));
		}
		$data = array('user_id'=>$user_id,'content'=>$content);
		$data['addtime'] = $this->time;
		$data['admin_id'] = $this->session->val('admin_id');
		$this->model('pm')->save($data);
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除站内短信权限'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('pm')->delete($value);
		}
		$this->success();
	}
}

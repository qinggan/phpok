<?php
/**
 * 后台管理_登记微信平台里所有会员，包括开放平台，公众平台及小程序平台
 * @作者 phpok.com <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2020年03月26日 03时25分
**/
namespace phpok\app\control\wxuser;
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
		$this->popedom = appfile_popedom('wxuser');
		$this->assign("popedom",$this->popedom);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error('您没有权限操作');
		}
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定ID');
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
				continue;
			}
			$list[$key] = $value;
		}
		if(!$list || count($list)<1){
			$this->error('未指定ID');
		}
		$this->model('wxuser')->delete($list);
		$this->success();
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$pageurl = $this->url('wxuser');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		//关键字
		if($keywords){
			$condition .= " AND (wx.nickname LIKE '%".$keywords."%' OR u.user LIKE '%".$keywords."%' ";
			$condition .= " OR u.email LIKE '%".$keywords."%' OR u.mobile LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		//状态
		$status = $this->get('status','int');
		if($status){
			$condition .= " AND u.status='".($status == 1 ? 1 : 0)."' ";
			$pageurl .= "&status=".$status;
			$this->assign('status',$status);
		}
		//微信性别
		$gender = $this->get('gender','int');
		if($gender){
			$condition .= " AND wx.gender='".$gender."' ";
			$pageurl .= "&gender=".$gender;
			$this->assign('gender',$gender);
		}
		$total = $this->model('wxuser')->get_count($condition);
		if($total){
			$rslist = $this->model('wxuser')->get_all($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$pageurl);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('offset',$offset);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->display('admin_index');
	}

	public function lock_f()
	{
		if(!$this->popedom['lock']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$user = $this->get('user');
		if(!$user){
			$this->error(P_Lang('未指定会员'));
		}
		$info = $this->model('user')->get_one($user,'user',false,false);
		if(!$info){
			$this->error(P_Lang('会员账号不存在'));
		}
		$this->model('wxuser')->user_lock($id,$info['id']);
		$this->success();
	}

	public function unlock_f()
	{
		if(!$this->popedom['unlock']){
			$this->error(P_Lang('您没有权限操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
				continue;
			}
			$list[$key] = $value;
		}
		if(!$list || count($list)<1){
			$this->error('未指定ID');
		}
		$this->model('wxuser')->user_unlock($list);
		$this->success();
	}
}

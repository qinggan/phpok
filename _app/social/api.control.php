<?php
/**
 * 接口应用_针对社交信息增加的一些服务，如关注，粉丝，黑名单等功能
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
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		$this->success();
	}

	public function homepage_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$data = array();
		$data['banner'] = $this->get('banner');
		$data['mbanner'] = $this->get('mbanner');
		$data['heart'] = $this->get('heart');
		$data['tags'] = $this->get('tags');
		$this->model('social')->homepage($this->session->val('user_id'),$data);
		$this->success();
	}

	public function idol_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$type = $this->get('type');
		if($type == 'add'){
			$act = $this->model('social')->idol_add($this->session->val('user_id'),$id);
			if(!$act){
				$this->error(P_Lang('添加关注失败，请联系客服'));
			}
			$this->success();
		}
		$act = $this->model('social')->idol_del($this->session->val('user_id'),$id);
		if(!$act){
			$this->error(P_Lang('取消关注失败，请联系客服'));
		}
		$this->success();
	}

	public function idols_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$offset = ($pageid-1)*$psize;
		$condition = "";
		$keywords = $this->get('keywords');
		if($keywords){
			$condition = "u.user LIKE '%".$keywords."%'";
		}
		$total = $this->model('social')->idol_count($this->session->val('user_id'),$condition);
		$data = array('pageid'=>$pageid,'psize'=>$psize,'total'=>$total);
		if($total){
			$rslist = $this->model('social')->idol_list($this->session->val('user_id'),$offset,$psize,$condition);
			$uids = array();
			if($rslist){
				foreach($rslist as $key=>$value){
					$uids[] = $value['id'];
				}
				$uinfo = $this->model('social')->fans_list($this->session->val('user_id'),0,count($uids),"l.user_id IN(".implode(",",$uids).")");
				$uids = array();
				if($uinfo){
					foreach($uinfo as $key=>$value){
						$uids[] = $value['id'];
					}
				}
				foreach($rslist as $key=>$value){
					$value['is_fans'] = ($uids && in_array($value['id'],$uids)) ? true : false;
					$rslist[$key] = $value; 
				}
			}
			$data['rslist'] = $rslist;
		}
		$this->success($data);
	}

	public function black_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$type = $this->get('type');
		if($type == 'add'){
			$act = $this->model('social')->black_add($this->session->val('user_id'),$id);
			if(!$act){
				$this->error(P_Lang('加入黑名单失败，请联系客服'));
			}
			$this->success();
		}
		$act = $this->model('social')->black_del($this->session->val('user_id'),$id);
		if(!$act){
			$this->error(P_Lang('取消黑名单失败，请联系客服'));
		}
		$this->success();
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
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$offset = ($pageid-1)*$psize;
		$condition = "";
		$keywords = $this->get('keywords');
		if($keywords){
			$condition = "u.user LIKE '%".$keywords."%'";
		}
		$total = $this->model('social')->black_count($this->session->val('user_id'),$condition);
		$data = array('pageid'=>$pageid,'psize'=>$psize,'total'=>$total);
		if($total){
			$rslist = $this->model('social')->black_list($this->session->val('user_id'),$offset,$psize,$condition);
			$data['rslist'] = $rslist;
		}
		$this->success($data);
	}

	/**
	 * 粉丝单
	**/
	public function fanslist_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$offset = ($pageid-1)*$psize;
		$condition = "";
		$keywords = $this->get('keywords');
		if($keywords){
			$condition = "u.user LIKE '%".$keywords."%'";
		}
		$total = $this->model('social')->fans_count($this->session->val('user_id'),$condition);
		$data = array('pageid'=>$pageid,'psize'=>$psize,'total'=>$total);
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
			$data['rslist'] = $rslist;
		}
		$this->success($data);
	}

	/**
	 * 取得主页装扮信息
	**/
	public function homepage_info_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$rs = $this->model('social')->homepage($this->session->val('user_id'));
		$this->success($rs);
	}

	/**
	 * 修改个性签名
	**/
	public function heart_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'));
		}
		$data = array();
		$data['heart'] = $this->get('heart');
		$this->model('social')->homepage($this->session->val('user_id'),$data);
		$this->success();
	}

}

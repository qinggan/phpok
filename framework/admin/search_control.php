<?php
/**
 * 搜索关键字设置
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2020年7月21日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class search_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('search');
		$this->assign("popedom",$this->popedom);
	}

	public function add_f()
	{
		$content = $this->get('content');
		if(!$content){
			$this->error(P_Lang('未指定要添加的内容'));
		}
		$list = explode(",",$content);
		foreach($list as $key=>$value){
			$value = trim($value);
			if(!$value){
				continue;
			}
			$this->model('search')->save($value);
		}
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('search')->delete($id);
		$this->success();
	}

	public function edit_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您没有权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('search')->get_one($id);
		$this->assign('rs',$rs);
		$this->assign('id',$id);
		$this->view('search_edit');
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限'));
		}
		$keywords = $this->get('keywords');
		$condition = " site_id='".$this->session->val('admin_site_id')."' ";
		$pageurl = $this->url('search');
		$page_id = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		}
		$pageid = $this->get($page_id,'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$this->assign('keywords',$keywords);
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$sign = $this->get('sign','int');
		if($sign){
			$condition .= " AND sign='".($sign == 1 ? 1 : 0)."' ";
			$pageurl .= "&sign=".$sign;
			$this->assign('sign',$sign);
		}
		$total = $this->model('search')->get_count($condition);
		if($total){
			$type = $this->get('type');
			$this->assign('type',$type);
			if($type){
				$pageurl .= "&type=".rawurlencode($type);
				$this->assign('type',$type);
			}
			$orderby = $this->_orderby($type);
			$rslist = $this->model('search')->get_all($condition,$offset,$psize,$orderby);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
			$this->assign('pageid',$pageid);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('rslist',$rslist);
		}
		$this->view("search_list");
	}

	public function save_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('关键字不能为空'));
		}
		$sign = $this->get('sign','int');
		$hits = $this->get('hits','int');
		$data = array('title'=>$title,'sign'=>$sign,'hits'=>$hits);
		$this->model('search')->update($data,$id);
		$this->success();
	}

	public function sign_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$data = array('sign'=>1);
			$this->model('search')->update($data,$value);
			$this->success();
		}
	}

	public function unsign_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$data = array('sign'=>0);
			$this->model('search')->update($data,$value);
			$this->success();
		}
	}

	private function _orderby($type='')
	{
		$orderby = '';
		switch ($type)
		{
			case 'hot':
				$orderby = 'hits DESC,dateline DESC';
				break;
			case 'cold':
				$orderby = 'hits ASC,dateline DESC';
				break;
			case 'old':
				$orderby = 'dateline ASC';
				break;
			default:
				$orderby = "dateline DESC";
				break;
		}
		return $orderby;
	}
}

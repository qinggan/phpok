<?php
/**
 * Tag标签管理工具
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年04月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("tag");
		$this->assign("popedom",$this->popedom);
		$this->model('tag')->site_id($_SESSION['admin_site_id']);
	}

	/**
	 * 标签管理列表
	 * @参数 keywords 搜索关键字
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pageurl = $this->url('tag');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0){
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_index');
	}

	/**
	 * 添加或编辑标签
	**/
	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('tag')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$this->view("tag_set");
	}

	/**
	 * 保存标签数据
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		$popedom = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('关键字名称不能为空'));
		}
		$chk = $this->model('tag')->chk_title($title,$id);
		if($chk){
			$this->error(P_Lang('关键字已存在，请检查'));
		}
		$data = array('title'=>$title,'url'=>$this->get('url'),'target'=>$this->get('target','int'));
		$data['site_id'] = $this->session->val('admin_site_id');
		$data['alt'] = $this->get('alt');
		$data['is_global'] = $this->get('is_global','int');
		$data['replace_count'] = $this->get('replace_count','int');
		if($id){
			$this->model('tag')->save($data,$id);
			$this->success();
		}
		$insert_id = $this->model('tag')->save($data);
		if(!$insert_id){
			$this->error(P_Lang('添加失败，请检查'));
		}
		$this->success();
	}

	/**
	 * 删除标签
	 * @参数 id 要删除的标签对应的ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('tag')->delete($id);
		$this->success();
	}

	/**
	 * 配置Tag标签管理参数
	**/
	public function config_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('tag')->config();
		if($rs){
			$this->assign('rs',$rs);
		}
		$this->view("tag_config");
	}

	/**
	 * 存储配置信息
	**/
	public function config_save_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$data = array();
		$data['separator'] = $this->get('separator');
		if(!$data['separator']){
			$data['separator'] = '|';
		}
		$data['count'] = $this->get('count','int');
		$this->model('tag')->config($data);
		$this->success();
	}

	/**
	 * 弹出窗选择
	**/
	public function open_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('tag')->config();
		if($rs){
			$this->assign('rs',$rs);
		}
		$pageurl = $this->url('tag','open');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
		}
		$psize = 80;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0){
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_open');
	}
}
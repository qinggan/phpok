<?php
/*****************************************************************************************
	文件： {phpok}/admin/options_control.php
	备注： 产品属性管理工具
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年08月07日 13时27分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class options_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('options');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('options')->get_all();
		$this->assign('rslist',$rslist);
		$taxis = $rslist ? (count($rslist)+1) * 10 : 10;
		$this->assign('taxis',$taxis);
		$this->view('options_index');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('名称不能为空'));
		}
		$taxis = $this->get('taxis');
		if(!$taxis){
			$taxis = 255;
		}
		$this->model('options')->save(array('title'=>$title,'taxis'=>$taxis),$id);
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('options')->delete($id);
		$this->json(true);
	}

	public function list_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'),'','error');
		}
		$rs = $this->model('options')->get_one($id);
		$this->assign('rs',$rs);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid-1)*$psize;
		$condition = "aid='".$id."'";
		$total = $this->model('options')->values_total($condition);
		if($total>0){
			$rslist = $this->model('options')->values_list($condition,$offset,$psize);
			$pageurl = $this->url('options','list','id='.$id);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign('rslist',$rslist);
		}
		$taxis = $rslist ? (count($rslist)+1) * 10 : 10;
		$this->assign('taxis',$taxis);
		$this->view('options_list');
	}

	public function save_values_f()
	{
		$id = $this->get('id');
		if(!$id){
			$aid = $this->get('aid');
			if(!$aid){
				$this->json(P_Lang('未指定属性ID'));
			}
		}else{
			$rs = $this->model('options')->value_one($id);
			if(!$rs){
				$this->json(P_Lang('属性参数有错误'));
			}
			$aid = $rs['aid'];
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('名称不能为空'));
		}
		$taxis = $this->get('taxis');
		if(!$taxis){
			$taxis = 255;
		}
		$array = array('aid'=>$aid,'title'=>$title,'taxis'=>$taxis);
		$array['pic'] = $this->get('pic');
		$array['val'] = $this->get('val');
		$this->model('options')->save_values($array,$id);
		$this->json(true);
	}

	public function delete_values_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('options')->delete_values($id);
		$this->json(true);
	}
}

?>
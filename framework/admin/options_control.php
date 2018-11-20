<?php
/**
 * 产品属性管理工具
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年02月02日
**/

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
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('options')->get_all();
		$this->assign('rslist',$rslist);
		$taxis = $rslist ? (count($rslist)+1) * 5 : 5;
		$this->assign('taxis',$taxis);
		$this->view('options_index');
	}

	public function all_f()
	{
		$rslist = $this->model('options')->get_all();
		if(!$rslist){
			$this->json(P_Lang('没有数据'));
		}
		$this->json($rslist,true);
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$taxis = $this->get('taxis');
		if(!$taxis){
			$rslist = $this->model('options')->get_all();
			$taxis = $rslist ? (count($rslist)+1) * 5 : 5;
			if($taxis > 255){
				$taxis = 255;
			}
		}
		if(!$id){
			$insert_id = $this->model('options')->save(array('title'=>$title,'taxis'=>$taxis));
			if(!$insert_id){
				$this->error(P_Lang('添加失败'));
			}
			$this->success($insert_id);
		}
		$this->model('options')->save(array('title'=>$title,'taxis'=>$taxis),$id);
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('options')->delete($id);
		$this->success();
	}

	public function list_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
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
		$taxis = $rslist ? (count($rslist)+1) * 5 : 5;
		$this->assign('taxis',$taxis);
		$this->view('options_list');
	}

	public function save_values_f()
	{
		$id = $this->get('id');
		if(!$id){
			$aid = $this->get('aid');
			if(!$aid){
				$this->error(P_Lang('未指定属性ID'));
			}
		}else{
			$rs = $this->model('options')->value_one($id);
			if(!$rs){
				$this->error(P_Lang('属性参数有错误'));
			}
			$aid = $rs['aid'];
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$taxis = $this->get('taxis');
		if(!$taxis){
			$condition = "aid='".$aid."'";
			$rslist = $this->model('options')->values_list($condition,0,9999);
			$taxis = $rslist ? (count($rslist)+1) * 5 : 5;
		}
		$array = array('aid'=>$aid,'title'=>$title,'taxis'=>$taxis);
		$array['pic'] = $this->get('pic');
		$array['val'] = $this->get('val');
		if($id){
			$this->model('options')->save_values($array,$id);
			$this->success();
		}
		$insert_id = $this->model('options')->save_values($array);
		$this->success($insert_id);
	}

	public function delete_values_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('options')->delete_values($id);
		$this->success();
	}
}
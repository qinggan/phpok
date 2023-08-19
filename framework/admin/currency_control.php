<?php
/**
 * 货币管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年11月25日
**/

class currency_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("currency");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('currency')->get_list();
		if($rslist){
			foreach($rslist as $key=>$value){
				if($value['dpl']){
					$rand = $this->lib('common')->str_rand($value['dpl'],'number');
					$value['rand'] = $rand;
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->model('log')->add(P_Lang('访问【货币及汇率】页面'));
		$this->view("currency_list");
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('currency'),10);
		}
		if($id){
			$rs = $this->model('currency')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		$this->model('log')->add(P_Lang('访问【添加或修改货币信息】页面'));
		$this->view("currency_set");
	}

	public function setok_f()
	{
		$id = $this->get('id','int');
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('currency'),10);
		}
		$array = array();
		$array["code"] = $this->get('code');
		$array["code_num"] = $this->get('code_num');
		$array["val"] = $this->get("val","float");
		$array["title"] = $this->get("title");
		$array["symbol_left"] = $this->get("symbol_left");
		$array["symbol_right"] = $this->get("symbol_right");
		$array["taxis"] = $this->get("taxis","int");
		$array["status"] = $this->get("status","int");
		$array["hidden"] = $this->get("hidden","int");
		$array['dpl'] = $this->get('dpl','int');
		if(!$array["title"]){
			$this->error(P_Lang('名称不允许为空'));
		}
		if(!$array["code"]){
			$this->error(P_Lang('编码不允许为空'));
		}
		$this->model('currency')->save($array,$id);
		if($id){
			$this->model('log')->add(P_Lang('修改货币及汇率信息，ID#{0}',$id));
		}else{
			$this->model('log')->add(P_Lang('添加货币信息，货币名称：',$array['title']));
		}
		$this->success(P_Lang('货币设置操作成功'));
	}

	public function delete_f()
	{
		$id = $this->get("id",'int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->model('currency')->del($id);
		$this->model('log')->add(P_Lang('删除货币信息 #{0}',$id));
		$this->success();
	}

	public function sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort)){
			$this->error(P_Lang('更新排序失败'));
		}
		foreach($sort as $key=>$value){
			$key = intval($key);
			$value = intval($value);
			$this->model('currency')->update_sort($key,$value);
			$this->model('log')->add(P_Lang('更新货币信息排序，ID#{0}',$key));
		}
		$this->success();
	}

	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要操作的ID'));
		}
		$rs = $this->model('currency')->get_one($id);
		$status = $rs['status'] ? '0' : '1';
		$this->model('currency')->update_status($id,$status);
		$this->success($status);
	}
}
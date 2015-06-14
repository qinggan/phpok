<?php
/*****************************************************************************************
	文件： {phpok}/admin/gd_control.php
	备注： GD方案管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月25日 13时29分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class gd_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('gd');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('gd')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("gd_index");
	}

	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('gd'),'error');
			}
			$rs = $this->model('gd')->get_one($id);
			if($rs["mark_picture"] && !file_exists($rs["mark_picture"])){
				$rs["mark_picture"] = "";
			}
			$this->assign("id",$id);
			$this->assign("rs",$rs);
		} else {
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('gd'),'error');
			}
		}
		$this->view("gd_set");
	}

	public function save_f()
	{
		$id = $this->get("id","int");
		$array = array();
		if(!$id){
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$identifier = $this->get("identifier");
			if(!$identifier){
				$this->json(P_Lang('标识不能为空'));
			}
			$identifier = strtolower($identifier);
			if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier)){
				$this->json(P_Lang('标识不符合系统要求，限字母、数字及下划线且必须是字母开头'));
			}
			$chk = $this->model('gd')->get_one($identifier,'identifier');
			if($chk){
				$this->json(P_Lang('标识已经存在'));
			}
			$array["identifier"] = $identifier;
		}else{
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}
		$array["width"] = $this->get("width","int");
		$array["height"] = $this->get("height","int");
		$array["mark_picture"] = $this->get("mark_picture");
		$array["mark_position"] = $this->get("mark_position");
		$array["cut_type"] = $this->get("cut_type","int");
		$array["bgcolor"] = $this->get("bgcolor");
		$array["trans"] = $this->get("trans","int");
		$array["quality"] = $this->get("quality","int");
		$this->model('gd')->save($array,$id);
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
		$this->model('gd')->delete($id);
		$this->json(true);
	}

	public function editor_f()
	{
		if(!$this->popedom['modify']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('gd')->update_editor($id);
		$this->json(true);
	}
}

?>
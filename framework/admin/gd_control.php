<?php
/**
 * GD方案管理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年10月04日
**/

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

	/**
	 * 方案列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('gd')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("gd_index");
	}

	/**
	 * 编辑页面
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'),$this->url('gd'));
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

	/**
	 * 保存数据
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		$array = array();
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$identifier = $this->get("identifier");
			if(!$identifier){
				$this->error(P_Lang('标识不能为空'));
			}
			$identifier = strtolower($identifier);
			if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier)){
				$this->error(P_Lang('标识不符合系统要求，限字母、数字及下划线且必须是字母开头'));
			}
			$chk = $this->model('gd')->get_one($identifier,'identifier');
			if($chk){
				$this->error(P_Lang('标识已经存在'));
			}
			$array["identifier"] = $identifier;
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
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
		$this->success();
	}

	/**
	 * 删除图片方案
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
		$this->model('gd')->delete($id);
		$this->success();
	}

	/**
	 * 设置编辑器使用的图片规格
	**/
	public function editor_f()
	{
		if(!$this->popedom['modify']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		$this->model('gd')->update_editor($id);
		$this->success();
	}
}
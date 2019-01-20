<?php
/**
 * 管理员及其组管理组
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2016年07月21日
**/


if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class admin_control extends phpok_control
{
	private $popedom;

	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("admin");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 管理员列表，普通管理员要有查看权限（admin:list）
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"];
		if(!$psize){
			$psize = 30;
		}
		$offset = ($pageid - 1) * $psize;
		$condition = "1=1";
		$keywords = $this->get("keywords");
		$pageurl = $this->url("admin");
		if($keywords){
			$condition .= " AND account LIKE '%".$keywords."%' ";
			$pageurl .= '&keywords='.rawurlencode($keywords);
		}
		$rslist = $this->model('admin')->get_list($condition,$offset,$psize);
		$total = $this->model('admin')->get_total($condition);
		if($total > $psize){
			$string = P_Lang("home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("rslist",$rslist);
		$this->view("admin_list");
	}

	/**
	 * 添加或修改管理员信息，不能修改自己的信息，编辑管理员要有编辑权限（admin:modify），添加管理员要有添加权限（admin:add）
	 * @参数 id 管理员ID，为0或空表示添加管理员
	**/
	public function set_f()
	{
		$id = $this->get("id","int");
		$plist = array();
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			if($id == $this->session->val('admin_id')){
				$this->error(P_Lang('您不能操作自己的信息'),$this->url("admin"));
			}
			$this->assign("id",$id);
			$rs = $this->model('admin')->get_one($id);
			if($rs["if_system"] && !$this->session->val('admin_rs.if_system')){
				$this->error(P_Lang("非系统管理员不能执行此项"),$this->url("admin"));
			}
			$this->assign("rs",$rs);
			if(!$rs["if_system"]){
				$plist = $this->model('admin')->get_popedom_list($id);
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$this->assign("plist",$plist);
		//读取全部功能
		$syslist = $this->model('sysmenu')->get_all(0,1);
		$this->assign("syslist",$syslist);
		//读取全部功能的权限信息
		$glist = $this->model('popedom')->get_all("pid=0",true,false);
		$clist = $this->model('popedom')->get_all("pid>0",true,true);
		$c_rs = $this->model('sysmenu')->get_one_condition("appfile='list' AND parent_id>0");
		$this->assign("c_rs",$c_rs);
		$sitelist = $this->model('site')->get_all_site();
		if($sitelist){
			foreach($sitelist AS $key=>$value){
				$all_project = $this->model('project')->get_all_project($value["id"]);
				if($all_project){
					foreach($all_project AS $k=>$v){
						if($clist[$v["id"]]){
							$all_project[$k]["_popedom"] = $clist[$v["id"]];
						}
					}
				}
				$value["sonlist"] = $all_project;
				$sitelist[$key] = $value;
			}
			$this->assign("sitelist",$sitelist);
		}
		$this->assign("glist",$glist);
		$this->view("admin_set");
	}

	/**
	 * 检查是否有系统管理员
	 * @参数 id 管理员ID，即要跳过检查的管理员
	 * @返回 Json字串
	**/
	public function check_if_system_f()
	{
		$id = $this->get("id","int");
		$exit = $this->check_system($id);
		if($exit == "ok"){
			$this->json("ok",true);
		}else{
			$this->json($exit);
		}
	}

	/**
	 * 检查是否有系统管理员
	 * @参数 $id 管理员ID，即要跳过检查的管理员
	 * @返回 字符串，存在系统管理员返回为ok 不存在就返回错误信息
	**/
	private function check_system($id=0)
	{
		$condition = "if_system=1 AND status=1";
		$rslist = $this->model('admin')->get_list($condition,0,100);
		if(!$rslist){
			return P_Lang('没有系统管理员');
		}
		$if_system = false;
		foreach($rslist AS $key=>$value){
			if($value["id"] != $id){
				$if_system = true;
			}
		}
		if(!$if_system){
			return P_Lang('没有系统管理员');
		}
		return "ok";
	}

	/**
	 * 删除管理员，普通管理员要有删除权限（admin:delete），普通管理员不能删除系统管理员
	 * @参数 id 要删除的管理员，不能删除自己，普通管理员
	 * @返回 
	 * @更新时间 
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
		if($id == $this->session->val('admin_id')){
			$this->error(P_Lang('您不能删除自己'));
		}
		$rs = $this->model('admin')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('管理员信息不存在'));
		}
		
		if($rs['if_system'] && !$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('非系统管理员不能删除系统管理员'));
		}
		$exit = $this->check_system($id);
		if($exit != "ok"){
			$this->error($exit);
		}
		$this->model('admin')->delete($id);
		$this->success();
	}

	/**
	 * 检测账号是否存在
	 * @参数 id 管理员ID，不为0且不为空时，表示要检查的管理员账号跳过id值
	 * @参数 account 管理员账号
	 * @返回 Json字串
	**/
	public function check_account_f()
	{
		$id = $this->get("id","int");
		$account = $this->get("account");
		$str = $this->check_account($account,$id);
		if($str == "ok"){
			$this->json("ok",true);
		}
		$this->json($str);
	}

	/**
	 * 检测账号是否存在，仅限内部使用
	 * @参数 $account 管理员账号
	 * @参数 $id 管理员ID，不为0且不为空时，表示要检查的管理员账号跳过id值
	 * @返回 字符串，检测通过返回ok，不通过返回错误信息
	**/
	private function check_account($account,$id=0)
	{
		if(!$account){
			return P_Lang('账号不能为空');
		}
		$rs = $this->model('admin')->check_account($account,$id);
		if($rs){
			return P_Lang('账号已经存在');
		}
		return "ok";
	}

	/**
	 * 存储管理员信息，无法自己修改自己信息
	 * @参数 id 为0或空值时表示添加管理员，不为0表示编辑管理员信息，包括分配权限
	 * @参数 account 管理员账号，不能为空
	 * @参数 pass 管理员密码，id有值时pass可以为空
	 * @参数 email 管理员邮箱，系统管理员此邮箱为接收通知使用
	 * @参数 status 管理员状态
	 * @参数 popedom 普通管理员权限（系统管理员没有此参数传递）
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		if($id && $id == $this->session->val('admin_id')){
			$this->error(P_Lang('您不能操作自己的信息'));
		}
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$account = $this->get("account");
		if(!$account){
			$this->error(P_Lang('账号不能为空'));
		}
		$rs = $this->model('admin')->check_account($account,$id);
		if($rs){
			$this->error(P_Lang('账号已经存在'.$account));
		}
		$array = array();
		$array["account"] = $account;
		$pass = $this->get("pass");
		if(!$pass && !$id){
			$this->error(P_Lang('密码不能为空'));
		}
		if($pass){
			if(strlen($pass) < 5){
				$this->error(P_Lang('密码长度不能少于4位'));
			}
			$array["pass"] = password_create($pass);
		}
		$array['email'] = $this->get("email");
		if($this->popedom["status"]){
			$array["status"] = $this->get("status","int");
		}
		$if_system = $this->get("if_system","int");
		if(!$this->session->val('admin_rs.if_system')){
			$if_system = 0;
		}
		$array["if_system"] = $if_system;
		if($id){
			$st = $this->model('admin')->save($array,$id);
			if(!$st){
				$this->error(P_Lang('管理员信息更新失败，请检查'));
			}
		}else{
			$id = $this->model('admin')->save($array);
			if(!$id){
				$this->error(P_Lang('管理员信息添加失败，请检查'));
			}
		}
		$this->model('admin')->clear_popedom($id);
		if(!$if_system){
			$popedom = $this->get("popedom");
			if($popedom){
				$popedom = array_unique($popedom);
				$this->model('admin')->save_popedom($popedom,$id);
			}
		}
		$this->success();
	}

	/**
	 * 更新管理员状态，不能更新自己的权限
	 * @参数 id 管理员ID
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if($id == $this->session->val('admin_id')){
			$this->error(P_Lang('您不能操作自己的信息'));
		}
		$rs = $this->model('admin')->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('admin')->update_status($id,$status);
		if(!$action){
			$this->error(P_Lang('更新状态失败'));
		}
		$this->success($status);
	}
}
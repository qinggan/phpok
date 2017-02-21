<?php
/**
 * 下拉菜单管理器，支持无限级别下拉菜单
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 2015-2016 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年01月18日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class opt_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("opt");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 读取全部组信息
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('opt')->group_all();
		$this->assign("rslist",$rslist);
		$this->view("opt_group");
	}

	/**
	 * 存储选项组
	**/
	public function group_save_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			exit(P_Lang('没有指定选项组'));
		}
		$id = $this->get("id","int");
		$this->model('opt')->group_save($title,$id);
		exit("ok");
	}

	/**
	 * 删除选项组，同时删除选项组下的数据
	**/
	public function group_del_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定选项组'));
		}
		$this->model('opt')->group_del($id);
		exit("ok");
	}

	/**
	 * 取得指定选项组下的内容，支持分页
	**/
	public function list_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pid = $this->get("pid","int");
		$group_id = $this->get("group_id","int");
		if(!$group_id && !$pid){
			$this->error(P_Lang('未指定选项组'),$this->url("opt"));
		}
		if($pid){
			$p_rs = $this->model('opt')->opt_one($pid);
			if(!$p_rs){
				$this->error(P_Lang('操作异常，请检查'),$this->url("opt"));
			}
			$group_id = $p_rs["group_id"];
			$list[0] = $p_rs;
			if($p_rs["parent_id"]){
				$this->model('opt')->opt_parent($list,$p_rs["parent_id"]);
			}
			krsort($list);
			$this->assign("lead_list",$list);
			$this->assign("p_rs",$p_rs);
			$this->assign("pid",$pid);
		}
		$this->assign("group_id",$group_id);
		$rs = $this->model('opt')->group_one($group_id);
		$psize = $this->config["psize"];
		$pageid = $this->get($this->config["pageid"],"int");
		$offset = $pageid ? ($pageid-1) * $psize : 0;
		$pageurl = $this->url("opt","list");
		$keywords = $this->get("keywords");
		$condition = "group_id='".$group_id."'";
		if($pid){
			$condition .= " AND parent_id='".$pid."' ";
			$pageurl .= "&pid=".$pid;
		}else{
			$pageurl .= "&group_id=".$group_id;
			$condition .= " AND parent_id='0' ";
		}
		if($keywords){
			$condition .= " AND (title LIKE '%".$keywords."%' OR val LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$rslist = $this->model('opt')->opt_list($condition,$offset,$psize);
		$total = $this->model('opt')->opt_count($condition);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		$this->assign("total",$total);
		$this->assign("psize",$psize);
		$this->assign("pageid",$pageid);
		$this->assign("pageurl",$pageurl);
		$this->assign("rslist",$rslist);
		$this->assign("rs",$rs);
		$this->view("opt_list");
	}

	/**
	 * 添加选项内容
	**/
	public function add_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$group_id = $this->get("group_id","int");
		if(!$group_id){
			exit(P_Lang('未指定选项组'));
		}
		$title = $this->get("title");
		$val = $this->get("val");
		$pid = $this->get("pid","int");
		$taxis = $this->get("taxis","int");
		if(!$title || $val == ""){
			exit(P_Lang('显示或值不能为空'));
		}
		$chk_exists = $this->model('opt')->chk_val($group_id,$val,$pid);
		if($chk_exists){
			exit(P_Lang('值已存在不允许重复创建'));
		}
		$array = array("group_id"=>$group_id,"parent_id"=>$pid,"title"=>$title,"val"=>$val,"taxis"=>$taxis);
		$this->model('opt')->opt_save($array);
		exit("ok");
	}

	/**
	 * 更新值选项信息
	**/
	public function edit_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定要编辑的ID'));
		}
		$rs = $this->model('opt')->opt_one($id);
		if(!$rs){
			exit(P_Lang('没有此项内容'));
		}
		$pid = $rs["parent_id"];
		$title = $this->get("title");
		$val = $this->get("val");
		$taxis = $this->get("taxis","int");
		if(!$title || $val == ""){
			exit(P_Lang('显示或值不能为空'));
		}
		$chk_exists = $this->model('opt')->chk_val($rs["group_id"],$val,$pid,$id);
		if($chk_exists){
			exit(P_Lang('值已存在不允许重复'));
		}
		$array = array("title"=>$title,"val"=>$val,"taxis"=>$taxis);
		$this->model('opt')->opt_save($array,$id);
		exit("ok");
	}

	/**
	 * 删除选项内容
	**/
	public function del_f()
	{
		if(!$this->popedom["set"]){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定要编辑的ID'));
		}
		$rs = $this->model('opt')->opt_one($id);
		if(!$rs){
			exit(P_Lang('没有此项内容'));
		}
		$list[0] = $rs;
		$this->model('opt')->opt_son($list,$id);
		foreach($list AS $key=>$value){
			$this->model('opt')->opt_del($value["id"]);
		}
		exit("ok");
	}
}
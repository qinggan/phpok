<?php
/***********************************************************
	Filename: {phpok}/admin/site_control.php
	Note	: 站点管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年10月8日
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class site_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("site");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('site')->get_all_site();
		$this->assign("rslist",$rslist);
		$this->view("site_list");
	}

	public function delete_f()
	{
		//删除站点操作
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定ID'));
		$rs = $this->model('site')->get_one($id);
		if(!$rs) $this->json(P_Lang('站点信息不存在'));
		if($rs['is_default']){
			$this->json(P_Lang('默认站点不支持删除操作'));
		}
		//删除网站内容
		$this->model("site")->site_delete($id);
		if($id == $_SESSION['admin_site_id']){
			$d_rs = $this->model('site')->get_one_default();
			$_SESSION['admin_site_id'] = $d_rs['id'];
		}
		$this->json(P_Lang('网站删除成功'),true);
	}

	public function default_f()
	{
		if(!$this->popedom['default']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定站点信息'));
		$rs = $this->model('site')->get_one($id);
		if(!$rs) $this->json(P_Lang('站点信息不存在'));
		if($rs['is_default']) $this->json(P_Lang('默认站点不支持此操作'));
		$this->model('site')->set_default($id);
		$this->json(P_Lang('默认站点设置成功'),true);
	}

	public function order_status_f()
	{
		if(!$this->popedom["order"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		//订单状态
		$rslist = $this->model('site')->order_status_all(true);
		$this->assign('rslist',$rslist);

		//价格状态
		$pricelist = $this->model('site')->price_status_all(true);
		$this->assign('pricelist',$pricelist);
		$this->view('site_order_status');
	}

	public function order_status_set_f()
	{
		if(!$this->popedom["order"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('site')->order_status_one($id);
		$this->assign('rs',$rs);
		$this->assign('id',$id);
		//邮件模板列表
		$emailtpl = $this->model('email')->simple_list($_SESSION['admin_site_id']);
		$this->assign("emailtpl",$emailtpl);
		$this->view('site_order_status_set');
	}

	public function order_status_save_f()
	{
		if(!$this->popedom["order"]){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('未指定状态名称'));
		}
		$array = array('title'=>$title);
		$array['status'] = $this->get('status','int');
		$array['email_tpl_user'] = $this->get('email_tpl_user');
		$array['email_tpl_admin'] = $this->get('email_tpl_admin');
		$array['sms_tpl_user'] = $this->get('sms_tpl_user');
		$array['sms_tpl_admin'] = $this->get('sms_tpl_admin');
		$array['taxis'] = $this->get('taxis','int');
		$this->model('site')->order_status_update($array,$id);
		$this->json(true);
	}

	public function price_status_save_f()
	{
		if(!$this->popedom["order"]){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('未指定名称'));
		}
		$array = array('title'=>$title);
		$array['status'] = $this->get('status','int');
		$array['action'] = $this->get('action');
		$array['taxis'] = $this->get('taxis','int');
		$this->model('site')->price_status_update($array,$id);
		$this->json(true);
	}

	public function alias_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定站点ID'));
		}
		$alias = $this->get('alias');
		if(!$alias){
			$this->json(P_Lang('未指定别名'));
		}
		$this->model('site')->alias_save($alias,$id);
		$this->json(true);
	}
}
?>
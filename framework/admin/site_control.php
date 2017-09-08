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

	/**
	 * 添加新站点
	**/
	public function add_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->view("site_add");
	}

	public function addok_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('网站标题不能为空'));
		}
		$domain = $this->get("domain");
		if(!$domain){
			$this->error("域名不能为空");
		}
		$domain = strtolower($domain);
		if(strpos($domain,'/') !== false){
			$this->error(P_Lang('域名填写不规范，不能带有http://或https://或/'));
		}
		$domain_rs = $this->model("site")->domain_check($domain);
		if($domain_rs){
			$this->error(P_Lang('域名已被使用，请更换'));
		}
		$array = array();
		$array["title"] = $title;
		$array["dir"] = '/';
		$array["status"] = 0;
		$array["content"] = '';
		$array["tpl_id"] = 0;
		$array["url_type"] = 'default';
		$array["domain_id"] = "";
		$array["logo"] = '';
		$array["meta"] = '';
		$array["register_status"] = 0;
		$array["register_close"] = "";
		$array["login_status"] = 0;
		$array["login_close"] = "";
		$site_id = $this->model('site')->save($array);
		if(!$site_id){
			$this->error(P_Lang('网站创建失败'));
		}
		$domain_id = $this->model('site')->domain_add($domain,$site_id);
		if($domain_id){
			$tmp = array('domain_id'=>$domain_id);
			$this->model("site")->save($tmp,$site_id);
		}
		$this->success();
	}


	/**
	 * 删除站点操作，仅限系统管理员有权限
	 * @参数 id 网站ID，默认站点不支持删除
	**/
	public function delete_f()
	{
		//删除站点操作
		$admin_rs = $this->session->val('admin_rs');
		if(!$admin_rs['if_system']){
			$this->error(P_Lang('您没有权限执行此操作，此操作仅限系统管理员有权限'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('site')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('站点信息不存在'));
		}
		if($rs['is_default']){
			$this->error(P_Lang('默认站点不支持删除操作'));
		}
		$this->model("site")->site_delete($id);
		if($id == $this->session->val('admin_site_id')){
			$d_rs = $this->model('site')->get_one_default();
			$this->session->assign('admin_site_id',$d_rs['id']);
		}
		$this->success();
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
		if($rs['next']){
			$rs['next'] = explode(",",$rs['next']);
		}
		$this->assign('rs',$rs);
		$this->assign('id',$id);
		//邮件模板列表
		$emailtpl = $this->model('email')->simple_list($_SESSION['admin_site_id']);
		$this->assign("emailtpl",$emailtpl);
		$statuslist = $this->model('order')->status_list();
		$this->assign('statuslist',$statuslist);
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
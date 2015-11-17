<?php
/***********************************************************
	Filename: app/admin/control/email.php
	Note	: 邮件发送操作
	Version : 3.0
	Author  : qinggan
	Update  : 2011-03-12
***********************************************************/
class email_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("email");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$site_id = $_SESSION["admin_site_id"];
		if($site_id){
			$condition = "site_id IN(".$site_id.",0)";
		}else{
			$condition = "site_id=0";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('email')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('email')->get_count($condition);//读取模块总数
		$pageurl = $this->url("email");
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		if($pagelist){
			$this->assign("pagelist",$pagelist);
		}
		$this->view("email_list");
	}

	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('email'),'error');
			}
			$rs = $this->model('email')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}else{
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('email'),'error');
			}
			$rs = array("content"=>'');
		}
		$edit_content = form_edit('content',$rs['content'],'editor','height=300&btn_image=1&is_code=1');
		$this->assign('edit_content',$edit_content);
		$this->view("email_set");
	}

	public function setok_f()
	{
		$array = array();
		$id = $this->get("id","int");
		if(!$id){
			$array["site_id"] = $_SESSION["admin_site_id"];
		}
		$array["title"] = $this->get("title");
		$array["identifier"] = $this->get("identifier");
		if(substr($array['identifier'],0,4) == 'sms_'){
			$array['content'] = $this->get('content','text');
		}else{
			$array["content"] = $this->get("content","html",false);
		}
		if(!$array["title"] || !$array["content"] || !$array["identifier"]){
			error(P_Lang('信息填写不完整'),$this->url("email","set","id=".$id),"error");
		}
		$this->model('email')->save($array,$id);
		error(P_Lang('邮件内容创建/修改成功，请稍候……'),$this->url("email"),"ok");
	}

	//删除邮件
	public function del_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('email')->del($id);
		$this->json(true);
	}

	public function check_f()
	{
		$id = $this->get("id","int");
		$identifier = $this->get("identifier");
		if(!$identifier){
			$this->json(P_Lang('未指定标识串'));
		}
		$rs = $this->model('email')->get_identifier($identifier,$id);
		if($rs){
			$this->json(P_Lang('标识串已被使用'));
		}
		$this->json(true);
	}
}
?>
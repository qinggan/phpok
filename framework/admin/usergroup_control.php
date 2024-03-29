<?php
/**
 * 用户组管理
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年12月23日
**/

class usergroup_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("usergroup");
		$this->assign("popedom",$this->popedom);
		$this->lib('form')->cssjs();
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('usergroup')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("usergroup_list");
	}

	public function set_f()
	{
		$id = $this->get("id","int");
		$popedom_users = array();
		$read_popedom_list = $reply_popedom_list = array();
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('usergroup')->get_one($id);
			if($rs['popedom']){
				$rs['popedom'] = unserialize($rs['popedom']);
				if($rs['popedom'][$_SESSION['admin_site_id']]){
					$popedom_users = explode(",",$rs['popedom'][$_SESSION['admin_site_id']]);
				}
			}
			$this->assign("rs",$rs);
			$this->assign('id',$id);
			$ext_module = "usergroup-".$id;
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$ext_module = "add-usergroup";
		}
		$this->assign("popedom_users",$popedom_users);
		
		//项目列表
		$rslist = $this->model('project')->get_all_project($_SESSION["admin_site_id"]);
		$this->assign("project_list",$rslist);
		
		//判断是否启用
		//自定义扩展字段
		$this->assign("ext_module",$ext_module);
		$forbid_list = $this->model('ext')->fields("user_group");
		$forbid = array_unique(array_merge(array("id","identifier"),$forbid_list));
		$extlist = get_phpok_ext($ext_module,implode(",",$forbid));
		$this->assign("extlist",$extlist);

		//用户字段列表
		$all_fields_list = $this->model('user')->fields_all();
		if($all_fields_list){
			$this->assign("all_fields_list",$all_fields_list);
			$fields_list = "";
			if($rs["fields"]){
				$fields_list = explode(",",$rs["fields"]);
			}
			$this->assign("fields_list",$fields_list);
		}

		$emailtpl = $this->model('email')->simple_list($this->session->val('admin_site_id'));
		if($emailtpl){
			$_etpl = array('sms'=>array('title'=>P_Lang('短信模板'),'rslist'=>array()),'email'=>array('title'=>P_Lang('邮件模板'),'rslist'=>array()));
			foreach($emailtpl as $key=>$value){
				if(substr($value['identifier'],0,4) == 'sms_'){
					$_etpl['sms']['rslist'][$key] = $value;
				}else{
					$_etpl['email']['rslist'][$key] = $value;
				}
			}
			$this->assign('notice_list',$_etpl);
		}
		
		$this->view("usergroup_set");
	}

	//存储信息
	public function setok_f()
	{
		$array = array();
		$id = $this->get("id","int");
		$title = $this->get("title");
		$error_url = $this->url("usergroup","set");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$error_url .= "&id=".$id;
			$rs = $this->model('usergroup')->get_one($id);
			$old_popedom = $rs['popedom'] ? unserialize($rs['popedom']) : array();
			$sitelist = $this->model('site')->get_all_site();
			if($sitelist){
				foreach($sitelist as $key=>$value){
					$array['popedom'][$value['id']] = $old_popedom[$value['id']] ? $old_popedom[$value['id']] : '';
				}
			}
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		if(!$title){
			$this->error(P_Lang('组名称不允许为空'),$error_url);
		}
		$array["title"] = $title;
		$popedom = $this->get('popedom','checkbox');
		if($popedom){
			$array['popedom'][$this->session->val('admin_site_id')] = implode(",",$popedom);
		}
		$array['popedom'] = serialize($array['popedom']);
		$array["is_open"] = $this->get("is_open","int");
		$array["taxis"] = $this->get("taxis","int");
		$array["register_status"] = $this->get("register_status");
		$array['tpl_id'] = $this->get('tpl_id','int');
		$fields_list = $this->get("fields_list");
		if($fields_list && is_array($fields_list)){
			if(in_array("all",$fields_list)){
				$array["fields"] = "";
			}else{
				$array["fields"] = implode(",",$fields_list);
			}
		}
		if($id){
			$this->model('usergroup')->save($array,$id);
			//存储扩展字段
			ext_save("usergroup-".$id);
			$tip = P_Lang('用户组编辑成功');
		}else{
			$id = $this->model('usergroup')->save($array);
			if($id){
				ext_save("add-usergroup-ext-id",true,"usergroup-".$id);
			}
			$tip = P_Lang('用户组添加成功');
		}
		$this->success($tip,$this->url('usergroup'));
	}

	public function delete_f()
	{
		if(!$this->popedom["delete"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('usergroup')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('数据记录不存在'));
		}
		if($rs["is_default"]){
			$this->error(P_Lang('默认用户组不能删除'));
		}
		if($rs["is_guest"]){
			$this->error(P_Lang('默认游客组不能删除'));
		}
		$this->model('usergroup')->del($id);
		$this->success();
	}


	public function default_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('usergroup')->set_default($id);
		$this->success();
	}

	public function guest_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('usergroup')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('数据记录不存在'));
		}
		if($rs["is_default"]){
			$this->error(P_Lang('默认用户组不能设为游客组'));
		}
		$this->model('usergroup')->set_guest($id);
		$this->success();
	}

	public function status_f()
	{
		if(!$this->popedom['status']){
			exit(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$rs = $this->model('usergroup')->get_one($id);
		if(!$rs)
		{
			exit(P_Lang('用户组信息不存在'));
		}
		$status = $this->get("status","int");
		$this->model('usergroup')->set_status($id,$status);
		exit("ok");
	}
}
?>
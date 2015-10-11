<?php
/***********************************************************
	Filename: app/admin/user.php
	Note	: 会员中心
	Version : 3.0
	Author  : qinggan
	Update  : 2009-12-23
***********************************************************/
class user_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->model("user");
		$this->model("usergroup");
		$this->popedom = appfile_popedom("user");
		$this->assign("popedom",$this->popedom);
	}


	//会员列表
	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = $this->config["psize"];
		if(!$psize) $psize = 30;
		$keywords = $this->get("keywords");
		$page_url = $this->url("user");
		$condition = "1=1";
		if($keywords){
			$this->assign("keywords",$keywords);
			$condition .= " AND u.user LIKE '%".$keywords."%'";
			$page_url.="&keywords=".rawurlencode($keywords);
		}
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('user')->get_list($condition,$offset,$psize);
		$count = $this->model('user')->get_count($condition);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($page_url,$count,$pageid,$psize,$string);
		$this->assign("total",$count);
		$this->assign("rslist",$rslist);
		$this->assign("pagelist",$pagelist);
		$list = $this->lib('xml')->read($this->dir_root.'data/xml/admin_user.xml');
		$this->assign("arealist",$list);

		$grouplist = $this->model('usergroup')->get_all("","id");
		$this->assign("grouplist",$grouplist);

		$wlist = $this->model('wealth')->get_all(1,'identifier');
		$this->assign('wlist',$wlist);
		
		$this->view("user_list");
	}

	public function address_f()
	{
		//读取会员地址库
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定会员ID'),'','error');
		}
		$rslist = $this->model('user')->address($id);
		$this->assign('rslist',$rslist);
		$this->assign('total',count($rslist));
		$this->assign('uid',$id);
		$this->view('user_address');
	}

	public function address_set_f()
	{
		$uid = $this->get('uid','int');
		$id = $this->get('id','int');
		if(!$id && !$uid){
			error(P_Lang('操作异常，参数不完整'));
		}
		$rs = array();
		if($id){
			$rs = $this->model('user')->address_one($id);
			if(!$rs){
				error(P_Lang('数据获取失败，请检查'));
			}
			$uid = $rs['user_id'];
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		if(!$uid){
			error(P_Lang('未指定会员账号'));
		}
		$this->assign('uid',$uid);
		$info = form_edit('pca',array('p'=>$rs['province'],'c'=>$rs['city'],'a'=>$rs['county']),'pca');
		$this->assign('pca_rs',$info);
		$this->view('user_address_set');
	}

	public function address_setok_f()
	{
		$uid = $this->get('uid','int');
		$id = $this->get('id','int');
		if(!$id && !$uid){
			$this->json(P_Lang('操作异常，参数不完整'));
		}
		$country = $this->get('country');
		$province = $this->get('pca_p');
		$city = $this->get('pca_c');
		$county = $this->get('pca_a');
		$array = array('user_id'=>$uid,'country'=>$country,'province'=>$province,'city'=>$city,'county'=>$county);
		$array['fullname'] = $this->get('fullname');
		if(!$array['fullname']){
			$this->json(P_Lang('收件人姓名不能为空'));
		}
		$array['address'] = $this->get('address');
		$array['mobile'] = $this->get('mobile');
		$array['tel'] = $this->get('tel');
		if(!$array['mobile'] && !$array['tel']){
			$this->json(P_Lang('手机或固定电话必须有填写一项'));
		}
		$array['email'] = $this->get('email');
		$this->model('user')->address_save($array,$id);
		$this->json(true);
	}

	public function address_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('user')->address_delete($id);
		$this->json(true);
	}

	public function invoice_f()
	{
		//读取会员地址库
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定会员ID'),'','error');
		}
		$rslist = $this->model('user')->invoice($id);
		$this->assign('rslist',$rslist);
		$this->assign('total',count($rslist));
		$this->assign('uid',$id);
		$this->view('user_invoice');
	}

	public function invoice_set_f()
	{
		$uid = $this->get('uid','int');
		$id = $this->get('id','int');
		if(!$id && !$uid){
			error(P_Lang('操作异常，参数不完整'));
		}
		$rs = array();
		if($id){
			$rs = $this->model('user')->invoice_one($id);
			if(!$rs){
				error(P_Lang('数据获取失败，请检查'));
			}
			$uid = $rs['user_id'];
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		if(!$uid){
			error(P_Lang('未指定会员账号'));
		}
		$this->assign('uid',$uid);
		$this->view('user_invoice_set');
	}

	public function invoice_setok_f()
	{
		$uid = $this->get('uid','int');
		$id = $this->get('id','int');
		if(!$id && !$uid){
			$this->json(P_Lang('操作异常，参数不完整'));
		}
		$title = $this->get('title');
		$type = $this->get('type');
		$content = $this->get('content');
		if(!$type){
			$this->json(P_Lang('请选择发票类型'));
		}
		if(!$title){
			$title = "个人发票";
		}
		if(!$content){
			$content = "明细";
		}
		$array = array('user_id'=>$uid,'title'=>$title,'type'=>$type,'content'=>$content,'note'=>$this->get('note'));
		$this->model('user')->invoice_save($array,$id);
		$this->json(true);
	}

	public function invoice_delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('user')->invoice_delete($id);
		$this->json(true);
	}

	public function add_f()
	{
		$this->set_f();
	}

	public function set_f()
	{
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),'','error');
			}
			$rs = $this->model('user')->get_one($id);
		}else{
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),'','error');
			}
		}
		//创建扩展字段的表单
		//读取扩展属性
		$this->lib("form")->cssjs();
		$ext_list = $this->model('user')->fields_all();
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value){
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]]){
				$value["content"] = $rs[$value["identifier"]];
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		//会员组
		$grouplist = $this->model('usergroup')->get_all();
		$this->assign("grouplist",$grouplist);
		$this->assign("rs",$rs);
		$this->assign("id",$id);
		$this->view("user_add");
	}

	public function chk_f()
	{
		$id = $this->get("id","int");
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('会员账号不允许为空'));
		}
		$rs_name = $this->model('user')->chk_name($user,$id);
		if($rs_name){
			$this->json(P_Lang('会员账号已经存在'));
		}
		$mobile = $this->get('mobile');
		if($mobile){
			if(!$this->lib('common')->tel_check($mobile)){
				$this->json(P_Lang('手机号填写不正确'));
			}
			$chk = $this->model('user')->get_one($mobile,'mobile');
			if($id){
				if($chk && $chk['id'] != $id){
					$this->json(P_Lang('手机号已被占用'));
				}
			}else{
				if($chk){
					$this->json(P_Lang('手机号已被占用'));
				}
			}
		}
		$email = $this->get('email');
		if($email){
			if(!$this->lib('common')->email_check($email)){
				$this->json(P_Lang('邮箱填写不正确'));
			}
			$chk = $this->model('user')->get_one($email,'email');
			if($id){
				if($chk && $chk['id'] != $id){
					$this->json(P_Lang('邮箱已被占用'));
				}
			}else{
				if($chk){
					$this->json(P_Lang('邮箱已被占用'));
				}
			}
		}
		$this->json(P_Lang('验证通过'),true);
	}

	//存储信息
	public function setok_f()
	{
		$id = $this->get("id","int");
		$array = array();
		$array["user"] = $this->get("user");
		$array['avatar'] = $this->get('avatar');
		$array['email'] = $this->get('email');
		$array['mobile'] = $this->get('mobile');
		$pass = $this->get("pass");
		if($pass){
			$array["pass"] = password_create($pass);
		}else{
			if(!$id){
				$array["pass"] = password_create("123456");
			}
		}
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$array["group_id"] = $this->get("group_id","int");
		if($this->popedom["status"]){
			$array["status"] = $this->get("status","int");
		}
		$regtime = $this->get("regtime","time");
		if(!$regtime) $regtime = $this->time;
		$array["regtime"] = $regtime;
		//存储扩展表信息
		$insert_id = $this->model('user')->save($array,$id);
		//读取扩展字段
 		$ext_list = $this->model('user')->fields_all();
 		$tmplist = array();
 		$tmplist["id"] = $insert_id;
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value){
			$val = ext_value($value);
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext AS $k=>$v){
					$value[$k] = $v;
				}
			}
			if($value["form_type"] == "password"){
				$content = $rs[$value["identifier"]] ? $rs[$value["identifier"]] : $value["content"];
				$val = ext_password_format($val,$content,$value["password_type"]);
			}
			$tmplist[$value["identifier"]] = $val;
		}
		$this->model('user')->save_ext($tmplist);
		$note = $id ? P_Lang('会员编辑成功') : P_Lang('新会员添加成功');
		error($note,$this->url("user"),"ok");
	}

	public function ajax_status_f()
	{
		if(!$this->popedom["status"]) exit(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('没有指定ID'));
		}
		$rs = $this->model('user')->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$this->model('user')->set_status($id,$status);
		exit("ok");
	}

	public function ajax_del_f()
	{
		if(!$this->popedom["delete"]) exit(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$this->model('user')->del($id);
		exit("ok");
	}

	//会员字段管理器中涉及到的字段
	function fields_auto()
	{
		$this->form_list = $this->model('form')->form_all();
		$this->field_list = $this->model('form')->field_all();
		$this->format_list = $this->model('form')->format_all();
		$this->assign('form_list',$this->form_list);
		$this->assign("field_list",$this->field_list);
		$this->assign("format_list",$this->format_list);
		$this->popedom = appfile_popedom("user:fields");
		$this->assign("popedom",$this->popedom);
	}

	function fields_f()
	{
		$this->fields_auto();
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		// 取得现有全部字段
		$condition = "area LIKE '%user%'";
		$used_list = $this->model('user')->fields_all("","identifier");
		if($used_list)
		{
			foreach($used_list AS $key=>$value)
			{
				$value["field_type_name"] = $this->field_list[$value["field_type"]];
				$value["form_type_name"] = $this->form_list[$value["form_type"]];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		if($this->popedom["set"])
		{
			$fields_list = $this->model('fields')->get_all($condition,"identifier");
			if($fields_list)
			{
				foreach($fields_list AS $key=>$value)
				{
					$value["field_type_name"] = $this->field_list[$value["field_type"]];
					$value["form_type_name"] = $this->form_list[$value["form_type"]];
					$fields_list[$key] = $value;
				}
			}
			if($fields_list && $used_list)
			{
				$main_key = $this->model('user')->fields();
				$newlist = array();
				foreach($fields_list AS $key=>$value)
				{
					if(!$used_list[$key] && !in_array($key,$main_key))
					{
						$newlist[$key] = $value;
					}
				}
				$this->assign("fields_list",$newlist);
			}
			else
			{
				$this->assign("fields_list",$fields_list);
			}
		}
		$this->view("user_fields");
	}

	//自定义字段
	function fields_save_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]) error(P_Lang('您没有权限执行此操作'));
		$id_list = isset($_POST["add_field"]) ? $_POST["add_field"] : "";
		if($id_list && is_array($id_list))
		{
			$condition = "area LIKE '%user%'";
			$flist = $this->model('fields')->get_all($condition,"id");
			foreach($id_list AS $key=>$value)
			{
				if(!$flist[$value]) continue;
				$f_rs = $flist[$value];
				$title = $this->get("field_title_".$value);
				if(!$title) $title = $f_rs["title"];
				$note = $this->get("field_note_".$value);
				if(!$note) $note = $f_rs["note"];
				$tmp_array = array("title"=>$title,"note"=>$note);
				$tmp_array["identifier"] = $f_rs["identifier"];
				$tmp_array["field_type"] = $f_rs["field_type"];
				$tmp_array["form_type"] = $f_rs["form_type"];
				$tmp_array["form_style"] = $f_rs["form_style"];
				$tmp_array["format"] = $f_rs["format"];
				$tmp_array["content"] = $f_rs["content"];
				$tmp_array["taxis"] = $f_rs["taxis"];
				$tmp_array["ext"] = $f_rs["ext"] ? serialize(unserialize($f_rs["ext"])) : "";
				$this->model('user')->fields_save($tmp_array);
			}
		}
		$list = $this->model('user')->fields_all();
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				$this->model('user')->create_fields($value);
			}
		}
		error(P_Lang('会员自定义字段配置成功'),$this->url("user","fields"));
	}

	
	function field_edit_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]) error_open(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id)
		{
			error_open(P_Lang('未指定ID'));
		}
		$rs = $this->model('user')->field_one($id);
		$this->assign("rs",$rs);
		$this->assign("id",$id);
		$this->view("user_field_set");
	}

	function field_edit_save_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]) error_open(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id)
		{
			error_open(P_Lang('未指定ID'));
		}
		$title = $this->get("title");
		$note = $this->get("note");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style","html");
		$content = $this->get("content");
		$format = $this->get("format");
		$taxis = $this->get("taxis","int");
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id)
		{
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value)
			{
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox")
				{
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}
				else
				{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["note"] = $note;
		$array["form_type"] = $form_type;
		$array["form_style"] = $form_style;
		$array["format"] = $format;
		$array["content"] = $content;
		$array["taxis"] = $taxis;
		$array["ext"] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$array["is_edit"] = $this->get("is_edit","int");
		$this->model('user')->fields_save($array,$id);
		$html = '<input type="button" value=" '.P_Lang('确定').' " class="submit" onclick="$.dialog.close();" />';
		error_open(P_Lang('自定义字段信息配置成功'),"ok",$html);
	}
	//删除字段
	function field_delete_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]) $this->json(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id)
		{
			$this->json(P_Lang('未指定要删除的字段'));
		}
		$this->model('user')->field_delete($id);
		$this->json(P_Lang('删除成功'),true);
	}

	public function info_f()
	{
		$uid = $this->get('uid');
		if(!$uid){
			$this->json(P_Lang('未指定会员ID'));
		}
		$type = $this->get('type');
		if($type == 'invoice'){
			$rslist = $this->model('user')->invoice($uid);
			if(!$rslist){
				$this->json(P_Lang('该会员未设置发票信息'));
			}
			$first = $default = false;
			foreach($rslist as $key=>$value){
				if($key<1){
					$first = $value;
				}
				if($value['is_default']){
					$default = $value;
				}
			}
			if(!$default){
				$default = $first;
				unset($first);
			}
			$this->json(array('rs'=>$default,'rslist'=>$rslist),true);
		}elseif($type == 'address'){
			$rslist = $this->model('user')->address($uid);
			if(!$rslist){
				$this->json(P_Lang('该会员未设置收件人信息'));
			}
			$first = $default = false;
			foreach($rslist as $key=>$value){
				if($key<1){
					$first = $value;
				}
				if($value['is_default']){
					$default = $value;
				}
			}
			if(!$default){
				$default = $first;
				unset($first);
			}
			$this->json(array('rs'=>$default,'rslist'=>$rslist),true);
		}else{
			$info = $this->model('user')->get_one($uid);
			if(!$info){
				$this->json(P_Lang('会员信息不存在'));
			}
		}
		$this->json($info,true);
	}
}
?>
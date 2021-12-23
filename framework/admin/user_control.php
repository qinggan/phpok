<?php
/**
 * 用户相关处理
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月08日
**/

class user_control extends phpok_control
{
	private $popedom;
	function __construct()
	{
		parent::control();
		$this->model("user");
		$this->model("usergroup");
		$this->popedom = appfile_popedom("user");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 配置要显示的用户字段，仅在后台有效
	**/
	public function show_setting_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$list = $this->lib('xml')->read($this->dir_data.'xml/admin_user.xml');
		if($list){
			$this->assign("arealist",$list);
			$keys = array_keys($list);
			$this->assign('keys',$keys);
		}
		$rslist = array('user'=>P_Lang('账号'),'group_id'=>P_Lang('用户组'),'email'=>P_Lang('邮箱'),'mobile'=>P_Lang('手机号'),'code'=>P_Lang('邀请码'));
		$rslist['introducer'] = P_Lang('推荐人');
		$rslist['order'] = P_Lang('订单');
		$rslist['snss'] = P_Lang('社交属性');
		$rslist['wealth'] = P_Lang('财富');
		$flist = $this->model('user')->fields_all();
		if($flist){
			foreach($flist as $key=>$value){
				$rslist[$value['identifier']] = $value['title'];
			}
		}
		$this->assign('rslist',$rslist);
		$this->view('user_show_setting');
	}


	public function show_setting_save_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$array = $this->get('setting','checkbox');
		if(!$array){
			$array = array('user');
		}
		$rslist = array('user'=>P_Lang('账号'),'group_id'=>P_Lang('用户组'),'email'=>P_Lang('邮箱'),'mobile'=>P_Lang('手机号'),'code'=>P_Lang('邀请码'));
		$rslist['introducer'] = P_Lang('推荐人');
		$rslist['order'] = P_Lang('订单');
		$rslist['snss'] = P_Lang('社交属性');
		$rslist['wealth'] = P_Lang('财富');
		$flist = $this->model('user')->fields_all();
		if($flist){
			foreach($flist as $key=>$value){
				$rslist[$value['identifier']] = $value['title'];
			}
		}
		$arealist = array();
		foreach($rslist as $key=>$value){
			if(in_array($key,$array)){
				$arealist[$key] = $value;
			}
		}
		$this->lib('xml')->save($arealist,$this->dir_data.'xml/admin_user.xml');
		$this->success();
	}


	//用户列表
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$flist = array('user'=>P_Lang('账号'),'mobile'=>P_Lang('手机号'),'email'=>P_Lang('邮箱'),'code'=>P_Lang('邀请码'),'introducer'=>P_Lang('推荐人'));
		$tmplist = $this->model('user')->fields_all("form_type='text'");
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$flist[$value['identifier']] = $value['title'];
			}
		}
		$this->assign('flist',$flist);
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->get('psize','int');
		if(!$psize){
			$psize = $this->config["psize"];
			if(!$psize){
				$psize = 30;
			}
		}
		$this->assign('psize',$psize);
		$page_url = $this->url("user",'','psize='.$psize);
		$condition = "1=1";
		$keywords = $this->get('keywords');
		if($keywords && is_array($keywords)){
			$tmparray = array('email','user','mobile','code');
			foreach($keywords as $key=>$value){
				if(!$value || !trim($value)){
					continue;
				}
				$page_url .= "&keywords[".$key."]=".rawurlencode($value);
				if($key == 'introducer'){
					$tmp = $this->model('user')->get_one($value,'user',false,false);
					if(!$tmp){
						$this->error(P_Lang('没有搜索到用户信息'),$this->url('user'));
					}
					$condition .= " AND u.id IN(SELECT uid FROM ".$this->db->prefix."user_relation WHERE introducer=".$tmp['id'].") ";
					continue;
				}
				if($key == 'status'){
					$value = intval($value);
					if($value>2){
						$condition .= " AND u.status=0 ";
					}else{
						$condition .= " AND u.status='".$value."' ";
					}
					continue;
				}
				$tmpe = in_array($key,$tmparray) ? 'u' : 'e';
				$condition .= " AND ".$tmpe.".".$key." LIKE '%".$value."%' ";
			}
			$this->assign("keywords",$keywords);
		}
		$group_id = $this->get('group_id','int');
		if($group_id){
			$this->assign('group_id',$group_id);
			$condition .= " AND u.group_id='".$group_id."'";
			$page_url .= "&group_id=".$group_id;
		}
		$offset = ($pageid-1) * $psize;
		$arealist = $this->lib('xml')->read($this->dir_data.'xml/admin_user.xml');
		if(!$arealist){
			$arealist = array();
		}
		$this->assign("arealist",$arealist);
		$count = $this->model('user')->get_count($condition);
		if($count){
			$rslist = $this->model('user')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($page_url,$count,$pageid,$psize,$string);
			//取得订单统计
			if($this->site['biz_status'] && isset($arealist['order']) && $rslist){
				$ids = array_keys($rslist);
				$olist = $this->model('order')->stat_count($ids);
				if($olist){
					foreach($olist as $key=>$value){
						$rslist[$key]['order'] = $value;
					}
				}
			}
			if(isset($arealist['snss']) && $rslist){
				$ids = array_keys($rslist);
				$idol_rslist = $this->model('user')->idol_count($ids);
				$fans_rslist = $this->model('user')->fans_count($ids);
				$black_rslist = $this->model('user')->black_count($ids);
				foreach($rslist as $key=>$value){
					$value['snss'] = array();
					$value['snss']['idol'] = ($idol_rslist && $idol_rslist[$value['id']]) ? $idol_rslist[$value['id']]['total'] : 0;
					$value['snss']['fans'] = ($fans_rslist && $fans_rslist[$value['id']]) ? $fans_rslist[$value['id']]['total'] : 0;
					$value['snss']['black'] = ($black_rslist && $black_rslist[$value['id']]) ? $black_rslist[$value['id']]['total'] : 0;
					$rslist[$key] = $value;
				}
			}
			$this->assign("rslist",$rslist);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$count);
		
		$grouplist = $this->model('usergroup')->get_all("","id");
		$this->assign("grouplist",$grouplist);

		$wlist = $this->model('wealth')->get_all(1,'identifier');
		$this->assign('wlist',$wlist);
		
		$this->view("user_list");
	}

	public function add_f()
	{
		$this->set_f();
	}

	public function set_f()
	{
		$id = $this->get("id","int");
		$group_id = 0;
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('user')->get_one($id);
			$group_id = $rs['group_id'];
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		//创建扩展字段的表单
		//读取扩展属性
		$this->lib("form")->cssjs();
		$ext_list = $this->model('user')->fields_all();
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) as $key=>$value){
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
		//用户组
		$grouplist = $this->model('usergroup')->get_all("is_guest=0 AND status=1");
		if(!$group_id){
			foreach($grouplist as $key=>$value){
				if($value['is_default']){
					$group_id = $value['id'];
					break;
				}
			}
		}
		$this->assign('group_id',$group_id);
		$this->assign("grouplist",$grouplist);
		$this->assign("rs",$rs);
		$this->assign("id",$id);
		$relation = $this->model('user')->get_relation($rs['id']);
		if($relation){
			$this->assign('relation_id',$relation);
		}
		$this->view("user_add");
	}

	public function chk_f()
	{
		$id = $this->get("id","int");
		$user = $this->get("user");
		if(!$user){
			$this->json(P_Lang('用户账号不允许为空'));
		}
		$rs_name = $this->model('user')->chk_name($user,$id);
		if($rs_name){
			$this->json(P_Lang('用户账号已经存在'));
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
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$array = array();
		$array["user"] = $this->get("user");
		if(!$array["user"]){
			$this->error(P_Lang('用户账号不允许为空'));
		}
		$rs_name = $this->model('user')->chk_name($array["user"],$id);
		if($rs_name){
			$this->error(P_Lang('用户账号已经存在'));
		}
		$array['avatar'] = $this->get('avatar');
		$array['email'] = $this->get('email');
		if($array['email']){
			if(!$this->lib('common')->email_check($array['email'])){
				$this->error(P_Lang('邮箱填写不正确'));
			}
			$chk = $this->model('user')->get_one($array['email'],'email');
			if($id){
				if($chk && $chk['id'] != $id){
					$this->error(P_Lang('邮箱已被占用'));
				}
			}else{
				if($chk){
					$this->error(P_Lang('邮箱已被占用'));
				}
			}
		}
		$array['mobile'] = $this->get('mobile');
		if($array['mobile']){
			if(!$this->lib('common')->tel_check($array['mobile'])){
				$this->error(P_Lang('手机号填写不正确'));
			}
			$chk = $this->model('user')->get_one($array['mobile'],'mobile');
			if($id){
				if($chk && $chk['id'] != $id){
					$this->error(P_Lang('手机号已被占用'));
				}
			}else{
				if($chk){
					$this->error(P_Lang('手机号已被占用'));
				}
			}
		}
		$pass = $this->get("pass");
		if($pass){
			$array["pass"] = password_create($pass);
		}else{
			if(!$id){
				$this->error(P_Lang('密码不能为空'));
			}
		}

		$array["group_id"] = $this->get("group_id","int");
		if($this->popedom["status"]){
			$array["status"] = $this->get("status","int");
		}
		$regtime = $this->get("regtime","time");
		if(!$regtime){
			$regtime = $this->time;
		}
		$array["regtime"] = $regtime;
		$array['code'] = $this->get('code');
		if($array['code']){
			$tmpcheck = $this->model('user')->chk_code($array['code'],$id);
			if($tmpcheck){
				$this->error(P_Lang('邀请码已存在，请更换'));
			}
		}
		if($id){
			$this->model('user')->save($array,$id);
			$insert_id = $id;
		}else{
			$insert_id = $this->model('user')->save($array);
		}
 		$ext_list = $this->model('user')->fields_all();
 		$tmplist = array();
 		$tmplist["id"] = $insert_id;
 		if($ext_list){
	 		foreach($ext_list as $key=>$value){
		 		$val = ext_value($value);
		 		if($value['ext'] && is_string($value['ext'])){
			 		$ext = unserialize($value["ext"]);
			 		foreach($ext as $k=>$v){
						$value[$k] = $v;
					}
		 		}
		 		if($value["form_type"] == "password"){
					$content = $rs[$value["identifier"]] ? $rs[$value["identifier"]] : $value["content"];
					$val = ext_password_format($val,$content,$value["password_type"]);
				}
				$tmplist[$value["identifier"]] = $val;
	 		}
 		}
		$this->model('user')->save_ext($tmplist);
		//推荐人功能
		$relation_id = $this->get('relation_id');
		$this->model('user')->save_relation($insert_id,$relation_id);
		$note = $id ? P_Lang('用户编辑成功') : P_Lang('新用户添加成功');
		$this->plugin('ap_user_setok',$insert_id);
		$this->success($note,$this->url('user'));
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

	/**
	 * 用户字段管理器中涉及到的字段
	**/
	private function fields_auto()
	{
		$this->form_list = $this->model('form')->form_all(true);
		$this->field_list = $this->model('form')->field_all(true);
		$this->format_list = $this->model('form')->format_all(true);
		$this->assign('form_list',$this->form_list);
		$this->assign("field_list",$this->field_list);
		$this->assign("format_list",$this->format_list);
		$this->popedom = appfile_popedom("user:fields");
		$this->assign("popedom",$this->popedom);
	}

	public function fields_f()
	{
		$this->fields_auto();
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		// 取得现有全部字段
		$condition = "area LIKE '%user%'";
		$used_list = $this->model('user')->fields_all("","identifier");
		if($used_list){
			foreach($used_list AS $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		if($this->popedom["set"]){
			$fields_list = $this->model('fields')->default_all();
			$this->assign("fields_list",$fields_list);
			if($fields_list && $used_list){
				$main_key = $this->model('user')->fields();
				$newlist = array();
				foreach($fields_list AS $key=>$value){
					if(!$used_list[$key] && !in_array($key,$main_key)){
						$newlist[$key] = $value;
					}
				}
				$this->assign("fields_list",$newlist);
			}
		}
		$this->view("user_fields");
	}

	/**
	 * 保存自定义字段
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function fields_save_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('fields')->default_one($id);
		if(!$rs){
			$this->error(P_Lang('字段内容不存在'));
		}
		$tmp_array = array('title'=>$rs['title'],'note'=>$rs['note']);
		$tmp_array["identifier"] = $rs["identifier"];
		$tmp_array["field_type"] = $rs["field_type"];
		$tmp_array["form_type"] = $rs["form_type"];
		$tmp_array["form_style"] = $rs["form_style"];
		$tmp_array["format"] = $rs["format"];
		$tmp_array["content"] = $rs["content"];
		$tmp_array["taxis"] = $this->model('user')->user_next_taxis();
		if($rs['ext'] && is_array($rs['ext'])){
			$tmp_array['ext'] = serialize($rs['ext']);
		}
		$this->model('user')->fields_save($tmp_array);
		$list = $this->model('user')->fields_all();
		if($list){
			foreach($list AS $key=>$value){
				$this->model('user')->create_fields($value);
			}
		}
		$this->success();
	}

	/**
	 * 用户字段添加修改操作
	**/
	public function field_edit_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if($id){
			$rs = $this->model('user')->field_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		$this->view("user_field_set");
	}

	/**
	 * 保存用户字段
	**/
	public function field_edit_save_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$identifier = $this->get('identifier','system');
			if(!$identifier){
				$this->error(P_Lang('标识不能为空或不符合要求'));
			}
			//检查标识是否已被使用
			if(!$this->model('user')->identifier_chk($identifier)){
				$this->error(P_Lang('标识检测到已经使用，请更换标识'));
			}
			$field_type = $this->get('field_type');
			if(!$field_type){
				$this->error(P_Lang('请选择字段类型'));
			}
		}
		$form_type = $this->get("form_type");
		if(!$form_type){
			$this->error(P_Lang('表单类型不能为空'));
		}
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list as $key=>$value){
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox"){
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}else{
					$value = $val[0];
					$ext[$value] = $this->get($value);
					if($val[2] && $val[2] == 'required' && $ext[$value] == ''){
						$this->error(P_Lang('扩展参数属性有必填选项没有写'));
					}
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["note"] = $this->get("note");
		$array["form_type"] = $form_type;
		$array["form_style"] = $this->get("form_style","html");
		$array["format"] = $this->get("format");
		$array["content"] = $this->get("content");
		$array["taxis"] = $this->get("taxis","int");
		$array["ext"] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$array["is_front"] = $this->get("is_front","int");
		if($id){
			$this->model('user')->fields_save($array,$id);
			$this->success();
		}
		$array['identifier'] = $identifier;
		$array['field_type'] = $field_type;
		$this->model('user')->fields_save($array);
		$list = $this->model('user')->fields_all();
		if($list){
			foreach($list as $key=>$value){
				$this->model('user')->create_fields($value);
			}
		}
		$this->success();
	}

	/**
	 * 删除字段
	 * @参数 id 要删除的字段ID，数字
	 * @返回 JSON数据
	**/
	public function field_delete_f()
	{
		$this->fields_auto();
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定要删除的字段'));
		}
		$this->model('user')->field_delete($id);
		$this->success();
	}

	public function show_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定用户ID'));
		}
		$rs = $this->model('user')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('用户信息不存在'));
		}
		$this->assign('rs',$rs);
		$relation = $this->model('user')->get_relation($rs['id']);
		if($relation){
			$relation = $this->model('user')->get_one($relation);
			$this->assign('relation',$relation);
		}
		$ext_list = $this->model('user')->fields_all();
		if($ext_list){
			$extlist = array();
			foreach($ext_list as $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext as $k=>$v){
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
		}
		$wealth = $this->model('user')->wealth($id);
		$this->assign('wealth',$wealth);

		//查看推荐人
		$count = $this->model('user')->count_relation($id);
		if($count){
			$this->assign('relation_count',$count);
			$rlist = $this->model('user')->list_relation($id,0,15);
			$this->assign('rlist',$rlist);
			//查看下线的订单数及订单产品数量
			$count = $this->model('user')->relation_order_count($id);
			if($count){
				$this->assign('relation_order_count',$count);
			}
			$count = $this->model('user')->relation_product_count($id);
			if($count){
				$this->assign('relation_product_count',$count);
			}
			//$sql = "SELECT uid FROM ".$this->db->prefix.""
		}
		//查看我的订单
		$condition = "o.user_id='".$id."'";
		$count = $this->model('order')->get_count($condition);
		if($count){
			$this->assign('order_count',$count);
			$rlist = $this->model('order')->get_list($condition,0,15);
			$this->assign('olist',$rlist);
			//取得产品数量
			$count = $this->model('order')->product_count($condition);
			$this->assign('product_count',$count);
		}
		$alist = $this->model('user')->address_all($id);
		if($alist){
			$this->assign('address_list',$alist);
			$this->assign('address_total',count($alist));
		}

		$idol = $this->model('user')->idol_count($id);
		$fans = $this->model('user')->fans_count($id);
		$black = $this->model('user')->black_count($id);
		$snss = array('idol'=>$idol,'fans'=>$fans,'black'=>$black);
		$this->assign('snss',$snss);
		$this->view("user_show");
	}

	public function info_f()
	{
		$uid = $this->get('uid');
		if(!$uid){
			$this->json(P_Lang('未指定用户ID'));
		}
		$type = $this->get('type');
		if($type == 'invoice'){
			$rslist = $this->model('user')->invoice($uid);
			if(!$rslist){
				$this->json(P_Lang('该用户未设置发票信息'));
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
				$this->json(P_Lang('该用户未设置收件人信息'));
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
				$this->json(P_Lang('用户信息不存在'));
			}
		}
		$this->json($info,true);
	}

	public function address_list_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有权限查看用户地址信息'));
		}
		$uid = $this->get('uid','int');
		if(!$uid){
			$this->error(P_Lang('未指定用户ID'));
		}
		$rslist = $this->model('user')->address_all($uid);
		if($rslist){
			$this->assign('rslist',$rslist);
			$this->assign('total',count($rslist));
		}
		$this->view('user_address');
	}

	public function autologin_f()
	{
		$uid = $this->get('id','int');
		if(!$uid){
			$this->error(P_Lang('未指定要登录的用户'));
		}
		$user = $this->model('user')->get_one($uid);
		if(!$user){
			$this->error(P_Lang('用户信息不存在'));
		}
		$this->session->assign('user_id',$user['id']);
		$this->session->assign('user_name',$user['user']);
		$this->session->assign('user_gid',$user['group_id']);
		$this->success(P_Lang('用户登录成功，正在跳转，请稍候…'),$this->config['url']);
	}

	//推荐用户
	public function vouch_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定用户ID'));
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$pageurl = $this->url('user','vouch','id='.$id);
		$total = $this->model('user')->count_relation($id);
		if($total){
			$rslist = $this->model('user')->list_relation($id,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			//查看下线的订单数及订单产品数量
			$count = $this->model('user')->relation_order_count($id);
			if($count){
				$this->assign('relation_order_count',$count);
			}
			$count = $this->model('user')->relation_product_count($id);
			if($count){
				$this->assign('relation_product_count',$count);
			}
		}
		$this->view('user_relation');
	}
}
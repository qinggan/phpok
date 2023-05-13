<?php
/**
 * 用户相关处理
 * @作者 qinggan <admin@phpok.com>
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
		$rslist['wealth'] = P_Lang('财富');
		$rslist['regtime'] = P_Lang('注册时间');
		$flist = $this->model('user')->fields_all();
		if($flist){
			foreach($flist as $key=>$value){
				$rslist[$value['identifier']] = $value['title'];
			}
		}
		$rslist['_action'] = P_Lang('操作');
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
		$rslist['wealth'] = P_Lang('财富');
		$rslist['regtime'] = P_Lang('注册时间');
		$flist = $this->model('user')->fields_all();
		if($flist){
			foreach($flist as $key=>$value){
				$rslist[$value['identifier']] = $value['title'];
			}
		}
		$rslist['_action'] = P_Lang('操作');
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
		$flist['order'] = P_Lang('订单');
		$flist['wealth'] = P_Lang('财富');
		$flist['regtime'] = P_Lang('注册时间');
		$tmplist = $this->model('user')->fields_all("form_type='text'");
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$flist[$value['identifier']] = $value['title'];
			}
		}
		$flist['_action'] = P_Lang('操作');
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
		if(!$keywords){
			$keywords = array();
		}
		$key_type = $this->get('key_type');
		$key_data = $this->get('key_data');
		if($key_type && $key_data){
			$page_url .="&key_type=".$key_type."&key_data=".rawurlencode($key_data);
			$this->assign("key_type",$key_type);
			$this->assign("key_data",$key_data);
			$keywords[$key_type] = $key_data;
		}
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
					if($value){
						if($value==4){
							$condition .= " AND u.status=0 ";
						}else{
							$condition .= " AND u.status='".$value."' ";
						}
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
			$this->assign("rslist",$rslist);
			if($count>$psize){
				$pagelist = phpok_page($page_url,$count,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
			
			$this->assign('psize',$psize);
			$this->assign('pageid',$pageid);
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
		$ext_list = $this->model('fields')->flist('user');
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) as $key=>$value){
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
			$first = $default = array();
			foreach($rslist as $key=>$value){
				if($key<1){
					$first = $value;
				}
				if($value['is_default']){
					$default = $value;
				}
			}
			if(!$default && count($default)<1){
				$default = $first;
				unset($first);
			}
			$this->json(array('rs'=>$default,'rslist'=>$rslist),true);
		}elseif($type == 'address'){
			$rslist = $this->model('user')->address($uid);
			if(!$rslist){
				$this->json(P_Lang('该用户未设置收件人信息'));
			}
			$first = $default = array();
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
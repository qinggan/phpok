<?php
/**
 * 用户控制面板
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年12月04日
**/

class usercp_control extends phpok_control
{
	public $group_rs;
	public $user_rs;
	private $user;
	public function __construct()
	{
		parent::control();
		$user_id = $this->session->val('user_id');
		if(!$user_id){
			$errurl = $this->url('login','',$this->url('usercp'));
			$this->error(P_Lang('请登录或注册账号'),$errurl);
		}
		$this->user = $this->model('user')->get_one($user_id);
		$this->group_rs = $this->model('usergroup')->group_rs($user_id);
		if(!$this->group_rs){
			$this->error(P_Lang('您的账号显示异常，请联系管理员'));
		}
	}

	public function homepage_f()
	{
		$link = $this->get('link');
		if($link){
			$this->assign('link',$link);
			$iframe_title = $this->get('title');
			if(!$iframe_title){
				$iframe_title = '###';
			}
			$this->assign('iframe_title',$iframe_title);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp/homepage';
		}
		$this->view($tplfile);
	}

	//收货地址管理
	public function address_f()
	{
		$rslist = $this->model('user')->address_all($this->session->val('user_id'));
		if($rslist){
			$this->assign('rslist',$rslist);
			$this->assign('total',count($rslist));
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_address';
		}
		$this->view($tplfile);
	}

	/**
	 * 添加或是修改地址信息
	**/
	public function address_setting_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->address_one($id);
			if(!$rs || $rs['user_id'] != $_SESSION['user_id']){
				$this->error(P_Lang('地址信息不存在或您没有权限修改此地址'));
			}
			$this->assign('id',$id);
			$this->assign('rs',$rs);
		}else{
			$rs = array();
		}
		$info = form_edit('pca',array('p'=>$rs['province'],'c'=>$rs['city'],'a'=>$rs['county']),'pca');
		$this->assign('pca_rs',$info);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_address_setting';
		}
		$this->view($tplfile);
	}

	public function avatar_f()
	{
		$this->assign('rs',$this->user);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_avatar';
		}
		$this->view($tplfile);
	}

	public function avatar_cut_f()
	{
		$id = $this->get('thumb_id','int');
		$x1 = $this->get("x1");
		$y1 = $this->get("y1");
		$x2 = $this->get("x2");
		$y2 = $this->get("y2");
		$w = $this->get("w");
		$h = $this->get("h");
		$rs = $this->model('res')->get_one($id,true,false);
		$new = $rs["folder"]."_tmp_".$id."_.".$rs["ext"];
		if($rs['attr']['width'] > 500){
			$beis = round($rs['attr']['width']/500,2);
			$w = round($w * $beis);
			$h = round($h * $beis);
			$x1 = round($x1 * $beis);
			$y1 = round($y1 * $beis);
			$x2 = round($x2 * $beis);
			$y2 = round($y2 * $beis);
		}
		$rs['filename'] = str_replace($this->config['url'],'',$rs['filename']);
		$cropped = $this->create_img($new,$this->dir_root.$rs["filename"],$w,$h,$x1,$y1,1);
		$this->lib('file')->mv($this->dir_root.$new,$this->dir_root.$rs['filename']);
		$this->model('user')->update_avatar($rs['filename'],$_SESSION['user_id']);
		$this->json(true);
	}

	public function comments_f()
	{
		$uid = $this->session->val('user_id');
		//读取留言管理的模板
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp-comments';
		}
		$condition = "r.admin_id=0 AND r.uid=".$uid;
		$parent_id = $this->get('parent_id','int');
		if($parent_id){
			$condition .= " AND r.parent_id='".$parent_id."'";
			$pageurl .= "&parent_id=".$parent_id; 
			$this->assign("parent_id",$parent_id);
			$rs = $this->model('reply')->get_one($parent_id);
			$this->assign('rs',$rs);
			$this->assign('title',P_Lang('我的评论_#{id}',array('id'=>$parent_id)));
		}else{
			$this->assign('title',P_Lang('我的评论'));
		}
		$pageurl = $this->url("usercp","comments");
		$status = $this->get("status","int");
		if($status){
			$n_status = $status == 1 ? "1" : "0";
			$condition .= "AND status=".$n_status." ";
			$pageurl .= "&status=".$status; 
			$this->assign("status",$status);
		}
		//关键字
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= "AND (title LIKE '%".$keywords."%' OR content LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if(!$total){
			$this->view($tplfile);
		}
		$this->assign("total",$total);
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('reply')->get_all($condition,$offset,$psize);
		if(!isset($rslist)){
			$this->view($tplfile);
		}
		$ids = array_keys($rslist);
		$condition_ext = " r.admin_id=0 AND (r.status=1 OR (r.status=0 AND r.uid=".$uid."))";
		$sub_total = $this->model('reply')->group_parent_total($ids,$condition_ext,false);
		$clicklist = $this->model('click')->get_all($ids,'reply');
		if(isset($sub_total) || isset($clicklist)){
			foreach($rslist as $key=>$value){
				$value['reply_total'] = 0;
				$value['click_list'] = array();
				if(isset($sub_total[$value['id']])){
					$value['reply_total'] = $sub_total[$value['id']];
				}
				if(isset($clicklist[$value['id']])){
					$value['click_list'] = $clicklist[$value['id']];
				}
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("pageurl",$pageurl);
		$this->view($tplfile);
	}

	//用户注销页
	public function destory_f()
	{
		$check_sms = $this->model('gateway')->get_default('sms');
		$check_email = $this->model('gateway')->get_default('email');
		$this->assign('is_email',$check_email);
		$this->assign('is_sms',$check_sms);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp-destroy';
		}
		$this->view($tplfile);
	}

	//修改邮箱
	public function email_f()
	{
		$this->assign('rs',$this->user);
		//判断后台是否配置好第三方网关
		$sendemail = $this->model('gateway')->get_default('email') ? true : false;
		$this->assign('sendemail',$sendemail);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_email';
		}
		$this->view($tplfile);
	}


	

	//用户个人中心
	public function index_f()
	{
		$backurl = $this->get('_back');
		if(!$backurl){
			$backurl = $this->config['url'];
		}
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能执行此操作'),$backurl);
		}
		$user = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$user){
			$this->error(P_Lang('您登录的账号信息不存在'),$backurl);
		}
		if(!$user['status']){
			$this->model('user')->logout();
			$this->error(P_Lang('您的注册信息未审核通过，请与管理员联系'),$backurl);
		}
		if($user['status'] == 2){
			$this->model('user')->logout();
			$this->error(P_Lang('您的账号被锁定，请与管理员联系'),$backurl);
		}
		$this->assign('rs',$user);
		$this->assign('user',$user);
		$condition = "user_id='".$this->session->val('user_id')."'";

		//读取最新下单信息
		$rslist = $this->model('order')->get_list($condition,0,10);
		$this->assign('rslist',$rslist);
		//读取用户上传的最新附件
		$reslist = $this->model('res')->get_list($condition,0,10);
		$this->assign('reslist',$reslist);
		$comment = $this->model('reply')->get_total($this->session->val('user_id'));
		$this->assign('comment',$comment);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp';
		}
		$link = $this->get('link');
		if($link){
			$this->assign('link',$link);
			$iframe_title = $this->get('title');
			if(!$iframe_title){
				$iframe_title = '###';
			}
			$this->assign('iframe_title',$iframe_title);
		}
		$this->view($tplfile);
	}

	//修改个人资料
	public function info_f()
	{
		$rs = $this->model('user')->get_one($this->session->val('user_id'));
		$group_rs = $this->group_rs;
		//读取扩展属性
		$condition = 'is_front=1';
		if($group_rs['fields']){
			$tmp = explode(",",$group_rs['fields']);
			$condition .= " AND identifier IN('".(implode("','",$tmp))."')";
		}
		$ext_list = $this->model('user')->fields_all($condition,"id");
		if($ext_list){
			$tmp_f = $group_rs['fields'] ? explode(",",$group_rs['fields']) : 'all';
			$extlist = array();
			foreach($ext_list as $key=>$value){
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
				if($tmp_f == 'all' || (is_array($tmp_f) && in_array($value['identifier'],$tmp_f))){
					$extlist[] = $this->lib('form')->format($value);
				}
			}
			$this->assign("extlist",$extlist);
		}
		$this->assign("rs",$rs);
		$this->assign("group_rs",$group_rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_info';
		}
		$this->view($tplfile);
	}

	/**
	 * 查看用户的推广链及推广统计
	**/
	public function introducer_f()
	{
		$me = $this->model('user')->get_one($this->session->val('user_id'));
		if(!$me['code']){
			$code = 'U'.$this->session->val('user_id').''.$this->lib('common')->str_rand(5,'number');
			$data = array('code'=>$code);
			$this->model('user')->save($data,$me['id']);
			$me['code'] = $code;
		}
		$this->assign('me',$me);
		$this->model('url')->nocache();
		$vlink = $this->url("index","link","uid=".$this->session->val('user_id'));
		$this->assign('vlink',$vlink);
		//取得推荐人列表
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$monthlist = $this->model('user')->stat_relation($this->session->val('user_id'));
		if($monthlist){
			$this->assign('monthlist',$monthlist);
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$month = $this->get('month');
		$pageurl = $this->url('usercp','introducer');
		$condition = '';
		if($month && strlen($month) == 6 && is_numeric($month)){
			$condition = "FROM_UNIXTIME(dateline,'%Y%m')='".$month."'";
			$this->assign('month',$month);
			$pageurl .= "&month=".$month;
		}
		$total = $this->model('user')->count_relation($this->session->val('user_id'),$condition);
		if($total && $total>0){
			$rslist = $this->model('user')->list_relation($this->session->val('user_id'),$offset,$psize,$condition);
			$this->assign('psize',$psize);
			$this->assign('offset',$offset);
			$this->assign('pageid',$pageid);
			$this->assign('total',$total);
			$this->assign('pageurl',$pageurl);
			$this->assign('rslist',$rslist);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_introducer';
		}
		$this->view($tplfile);
	}

	//发票管理
	public function invoice_f()
	{
		$rslist = $this->model('user')->invoice($_SESSION['user_id']);
		if($rslist){
			$this->assign('rslist',$rslist);
			$this->assign('total',count($rslist));
		}
		$this->view("usercp_invoice");
	}

	public function invoice_setting_f()
	{
		$id = $this->get('id','int');
		if($id){
			$rs = $this->model('user')->invoice_one($id);
			if(!$rs || $rs['user_id'] != $_SESSION['user_id']){
				$this->error(P_Lang('发票信息不存在或您没有权限修改此发票设置'));
			}
			$this->assign('id',$id);
			$this->assign('rs',$rs);
		}
		$this->view("usercp_invoice_setting");
	}


	//获取项目列表
	public function list_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定项目'),$this->url('usercp'));
		}
		$this->assign('id',$id);
		$pid = $this->model('id')->project_id($id,$this->site['id']);
		if(!$pid){
			$this->error(P_Lang('项目信息不存在'),$this->url('usercp'));
		}
		$project_rs = $this->model('project')->get_one($pid);
		if(!$project_rs || !$project_rs['status']){
			$this->error(P_Lang('项目不存在或未启用'),$this->url('usercp'));
		}
		$tplfile = 'usercp/'.$id;
		$tplfile.= $project_rs['module'] ? '-list' : '-page';
		//非列表项目直接指定
		$this->assign("page_rs",$project_rs);
		if(!$project_rs['module']){
			$this->view($tplfile);
			exit;
		}
		$m_rs = $this->model('module')->get_one($project_rs['module']);
		$m_list = $this->model('module')->fields_all($project_rs['module'],"identifier");
		if($m_rs["layout"]){
			$layout = explode(",",$m_rs["layout"]);
		}
		if($project_rs['layout']){
			$layout = explode(",",$project_rs["layout"]);
		}
		$this->assign("m_rs",$m_rs);
		$layout_list = array();
		foreach($layout as $key=>$value){
			if($value == 'user_id' || $value == 'sort'){
				continue;
			}
			if($m_list[$value] && !$m_list[$value]['is_front']){
				continue;
			}
			if($value == "hits"){
				$layout_list[$value] = array('title'=>P_Lang('次数'),'width'=>80,'edit'=>'false','align'=>'center','sort'=>'true');
			}elseif($value == "dateline"){
				$layout_list[$value] = array('title'=>P_Lang('日期'),'width'=>150,'edit'=>'false','align'=>'center','sort'=>'true');
			}else{
				$layout_tmparray = array();
				$layout_tmparray['title'] = $m_list[$value]["title"];
				$layout_tmparray['width'] = $m_list[$value]['admin-list-width'] ? $m_list[$value]['admin-list-width'] : 80;
				$layout_tmparray['edit'] = 'false';
				$layout_tmparray['sort'] = $m_list[$value]['admin-list-sort'] ? 'true' : 'false';
				$layout_tmparray['align'] = 'left';
				$layout_tmparray['form_type'] = $m_list[$value]['form_type'];
				$layout_list[$value] = $layout_tmparray;
			}
		}
		$this->assign("ext_list",$m_list);
		$this->assign("layout",$layout_list);
		unset($layout_list);
		//用于判断前台是否有发布及删除权限
		$popedom = array();
		$popedom['add'] = $popedom['edit'] = $this->model('popedom')->check($project_rs['id'],$this->group_rs['id'],'post');
		$popedom['delete'] = $this->model('popedom')->check($project_rs['id'],$this->group_rs['id'],'post1');
		$this->assign('popedom',$popedom);

		$dt = array('pid'=>$project_rs['id'],'user_id'=>$this->session->val('user_id'));
		if($project_rs['cate']){
			$cate = $this->get('cate');
			$cateid = $this->get('cateid','int');
			if($cate){
				$dt['cate'] = $cate;
			}
			if($cateid){
				$dt['cateid'] = $cateid;
			}
		}
		//读取符合要求的内容
		$pageurl = $this->url('usercp','list','id='.$id);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $project_rs['psize'] ? $project_rs['psize'] : $this->config['psize'];
		if(!$psize){
			$psize = 20;
		}
		$offset = ($pageid-1) * $psize;
		$tpl = $this->get('tpl');
		if($tpl){
			$pageurl .= "&tpl=".rawurlencode($tpl);
			$tplfile = $tpl;
		}
		$dt['psize'] = $psize;
		$dt['offset'] = $offset;
		$keywords = $this->get('keywords');
		if($keywords){
			$dt['keywords'] = $keywords;
			$pageurl .= "&keywords=".$keywords;
			$this->assign("keywords",$keywords);
		}
		$dt['not_status'] = true;
		$dt['is_usercp'] = 1;
		$status = $this->get('status');
		if($status){
			if($status == 1){
				$dt['sqlext'] = "l.status=1";
			}else{
				$dt['sqlext'] = "l.status=0";
			}
		}
		
		$dt['cache'] = false;
		$ext = $this->get('ext');
		if($ext && is_array($ext)){
			foreach($ext AS $key=>$value){
				if($key && $value){
					$dt['e_'.$key] = $value;
					$pageurl .= "&ext[".$key."]=".rawurlencode($value);
				}
			}
			$this->assign('ext',$ext);
		}
		$list = $this->call->phpok('_arclist',$dt);
		//
		$this->assign("pageid",$pageid);
		$this->assign("psize",$psize);
		$this->assign("pageurl",$pageurl);
		if($list['total']){
			$this->assign("total",$list['total']);
			$this->assign("rslist",$list['rslist']);
		}
		if(!$this->tpl->check_exists($tplfile)){
			$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
			if(!$tplfile){
				$tplfile = 'usercp_list';
			}
		}
		$this->view($tplfile);
	}

	public function media_f()
	{
		$typelist = $this->model('res')->user_typelist();
		if(!$typelist){
			$this->error(P_Lang('异常，请联系管理员检查'));
		}
		$this->assign('typelist',$typelist);
		$type = $this->get('type','int');
		if(!$type){
			foreach($typelist as $key=>$value){
				if($value['is_default']){
					$type = $value['id'];
					break;
				}
			}
		}
		if(!$type){
			$this->error(P_Lang('异常，没有找到媒体库'));
		}
		$this->assign('type',$type);
		$this->assign('rs',$typelist[$type]);
		$ext = explode(",",$typelist[$type]['ext']);
		$condition  = " user_id='".$this->session->val('user_id')."'";
		$condition .= " AND cate_id='".$type."' ";
		$pageid = $this->get("pageid",'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = 24;
		$offset = ($pageid-1) * $psize;
		$total = $this->model('res')->get_count($condition);
		if($total){
			$rslist = $this->model('res')->get_list($condition,$offset,$psize,false);
			$this->assign('pageid',$pageid);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('rslist',$rslist);
			$this->assign('pageurl',$this->url('usercp','media','type='.$type));
			$this->assign('total',$total);
		}
		
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_media';
		}
		$this->view($tplfile);
	}

	public function media_add_f()
	{
		$type = $this->get('type','int');
		if(!$type){
			$tmp = $this->model('rescate')->get_default();
			if($tmp){
				$type = $tmp['id'];
			}
		}
		if(!$type){
			$this->error(P_Lang('未指定类型'));
		}
		$this->assign('type',$type);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_media_add';
		}
		$btn = form_edit('picture','','upload','ext[cate_id]='.$type.'&is_multiple=1');
		$this->assign('button',$btn);
		$this->view($tplfile);
	}

	//修改手机
	public function mobile_f()
	{
		$this->assign('rs',$this->user);
		$sendsms = $this->model('gateway')->get_default('sms') ? true : false;
		$this->assign('sendsms',$sendsms);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_mobile';
		}
		$this->view($tplfile);
	}


	//修改密码
	public function passwd_f()
	{
		$rs = $this->model('user')->get_one($this->session->val('user_id'));
		$this->assign('rs',$rs);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_passwd';
		}
		$this->view($tplfile);
	}





	public function wealth_f()
	{
		$rslist = $this->model('wealth')->get_all(1);
		if(!$rslist){
			$this->error(P_Lang('系统没有启用任何财富功能，请联系管理员'));
		}
		$wealth = $this->user['wealth'];
		foreach($rslist as $key=>$value){
			$value['val'] = $wealth[$value['identifier']]['val'];
			$rslist[$key] = $value;
		}
		$this->assign('rslist',$rslist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_wealth';
		}
		$this->view($tplfile);
	}

	/**
	 * 积分日志
	**/
	public function wealth_log_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定财富规则'));
		}
		$rs = $this->model('wealth')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('财富信息不存在'));
		}
		$mywealth = $this->model('wealth')->get_val($this->session->val('user_id'),$rs['id']);
		$rs['val'] = $mywealth;
		$this->assign('id',$id);
		$this->assign('rs',$rs);
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1)*$psize;
		$pageurl = $this->url('usercp','wealth_log','id='.$id);
		$condition = "wid='".$id."' AND goal_id='".$this->session->val('user_id')."' AND status=1";
		$total = $this->model('wealth')->log_total($condition);
		if($total){
			$rslist = $this->model('wealth')->log_list($condition,$offset,$psize);
			$this->assign('rslist',$rslist);
			$this->assign('offset',$offset);
			$this->assign('psize',$psize);
			$this->assign('total',$total);
			$this->assign('pageid',$pageid);
			$this->assign('pageurl',$pageurl);
		}
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'usercp_wealth_log';
		}
		$this->view($tplfile);
	}


	private function create_img($thumb_image_name, $image, $width, $height, $x1, $y1,$scale=1)
	{
		list($imagewidth, $imageheight, $imageType) = getimagesize($image);
		$imageType = image_type_to_mime_type($imageType);
		switch($imageType) {
			case "image/gif":
				$source=imagecreatefromgif($image);
				break;
			case "image/pjpeg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/jpeg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/jpg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/png":
				$source=imagecreatefrompng($image);
				break;
			case "image/x-png":
				$source=imagecreatefrompng($image);
				break;
		}
		$nWidth = ceil($width * $scale);
		$nHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($nWidth,$nHeight);
		imagecopyresampled($newImage,$source,0,0,$x1,$y1,$nWidth,$nHeight,$width,$height);
		switch($imageType) {
			case "image/gif":
				imagegif($newImage,$thumb_image_name);
				break;
			case "image/pjpeg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/jpeg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/jpg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/png":
				imagepng($newImage,$thumb_image_name);
				break;
			case "image/x-png":
				imagepng($newImage,$thumb_image_name);
				break;
		}
		return $thumb_image_name;
	}

}
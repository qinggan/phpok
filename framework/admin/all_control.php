<?php
/***********************************************************
	Filename: {phpok}/admin/all_control.php
	Note	: 全局栏目配置
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-12 16:41
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class all_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("all");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('site')->all_list($_SESSION["admin_site_id"]);
		$this->assign("rslist",$rslist);
		$rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$this->assign("rs",$rs);
		$this->view("all_index");
	}

	public function setting_f()
	{
		if(!$this->popedom["site"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		$this->assign("rs",$rs);
		# 读取风格列表
		$tpl_list = $this->model('tpl')->get_all();
		$this->assign("tpl_list",$tpl_list);
		// 获取网站语言包列表
		$langlist = $this->model('lang')->get_list();
		$this->assign("langlist",$langlist);

		//项目列表
		$project_list = $this->model('project')->project_all($_SESSION['admin_site_id'],'id','status=1 AND hidden=1 AND module>0');
		$this->assign("project_list",$project_list);

		//读取网站货币
		$currency_list = $this->model('currency')->get_list();
		$this->assign("currency_list",$currency_list);

		//读取支付方式
		$payment = $this->model('payment')->get_all();
		$this->assign("payment",$payment);

		//邮件模板列表
		$emailtpl = $this->model('email')->simple_list($_SESSION['admin_site_id']);
		$this->assign("emailtpl",$emailtpl);

		//运费列表
		$freight = $this->model('freight')->get_all();
		$this->assign('freight',$freight);

		
		$this->view("all_setting");
	}

	# 存储全局配置
	public function save_f()
	{
		if(!$this->popedom["site"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$error_url = $this->url("all","setting");
		$title = $this->get("title");
		if(!$title){
			error(P_Lang('网站标题不能为空'),$error_url,"error");
		}
		$dir = $this->get("dir");
		if(!$dir){
			$dir = "/";
		}
		if(substr($dir,0,1) != "/"){
			$dir = "/".$dir;
		}
		if(substr($dir,-1) != "/"){
			$dir .= "/";
		}
		$status = $this->get("status","int");
		$content = $this->get("content");
		$tpl_id = $this->get("tpl_id","int");
		$url_type = $this->get("url_type");
		$logo = $this->get("logo");
		$meta = $this->get("meta","html_js");
		$site_rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		if(!$site_rs){
			error(P_Lang('网站信息不存在'),$error_url,"error");
		}
		$domain = $this->get("domain");
		$domain_id = $site_rs["domain_id"];
		if($domain){
			$domain = strtolower($domain);
			if(substr($domain,0,7) == "https://"){
				$domain = substr($domain,7);
			}
			if(substr($domain,0,8) == "https://"){
				$domain = substr($domain,8);
			}
			if(!$domain){
				error(P_Lang('域名填写不规范'),$error_url,"error");
			}
			if($domain && $domain != str_replace("/","",$domain)){
				error(P_Lang('域名填写不规范'),$error_url,"error");
			}
			//检查域名是否存在
			$domain_rs = $this->model('site')->domain_check($domain);
			if($domain_rs){
				if($domain_rs["id"] != $site_rs["domain_id"]){
					error(P_Lang('域名已被使用，请更换'),$error_url,"error");
				}
				if($domain_rs["domain"] != $site_rs["domain"]){
					$this->model('site')->domain_update($domain,$domain_rs["id"]);
				}
			}else{
				if($site_rs["domain_id"]){
					$this->model('site')->domain_update($domain,$site_rs["domain_id"]);
				}else{
					$domain_id = $this->model('site')->domain_add($domain,$_SESSION["admin_site_id"]);
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["dir"] = $dir;
		$array["status"] = $status;
		$array["content"] = $content;
		$array["tpl_id"] = $tpl_id;
		$array["url_type"] = $url_type;
		$array["domain_id"] = $domain_id;
		$array["logo"] = $logo;
		$array["meta"] = $meta;
		$array["register_status"] = $this->get("register_status","int");
		$array["register_close"] = $this->get("register_close");
		$array["login_status"] = $this->get("login_status","int");
		$array["login_close"] = $this->get("login_close");
		$array["adm_logo29"] = $this->get("adm_logo29");
		$array["adm_logo180"] = $this->get("adm_logo180");
		$array["lang"] = $this->get("lang");
		$array['api'] = $this->get('api','int');
		$array['api_code'] = $this->get("api_code");
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array["currency_id"] = $this->get("currency_id","int");
		$array["biz_sn"] = $this->get("biz_sn");
		$array["biz_payment"] = $this->get("biz_payment","int");
		$array['upload_guest'] = $this->get('upload_guest','int');
		$array['upload_user'] = $this->get('upload_user','int');
		//2016年09月08日
		$array['biz_status'] = $this->get('biz_status','int');
		$array['biz_freight'] = $this->get('biz_freight');
		$this->model('site')->save($array,$_SESSION['admin_site_id']);
		error(P_Lang('网站信息更新完成'),$this->url("all","setting"),'ok');
	}

	public function domain_check_f()
	{
		$domain = $this->get("domain");
		$isadd = $this->get("isadd","int");
		$id = $this->get("id","int");
		if(!$domain){
			$this->json(P_Lang('域名不能为空'));
		}
		if(substr($domain,0,7) == "https://" || substr($domain,0,8) == "https://"){
			$this->json(P_Lang('域名填写不规范，不能含有http://或https://'));
		}
		if($domain && $domain != str_replace("/","",$domain)){
			$this->json(P_Lang('域名不符合规范，不能有 / 符号'));
		}
		$domain_rs = $this->model('site')->domain_check($domain);
		if($domain_rs){
			if($isadd){
				$this->json(P_Lang('域名已经被使用'));
			}
			if($id){
				if($domain_rs["id"] != $id){
					$this->json(P_Lang('域名已经存在'));
				}
			}else{
				$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
				if($domain_rs["id"] != $rs["domain_id"]){
					$this->json(P_Lang('域名已经被使用'));
				}
			}
		}
		$this->json(true);
	}

	public function domain_f()
	{
		if(!$this->popedom["domain"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		$this->assign("rs",$rs);
		$rslist = $this->model('site')->domain_list($_SESSION["admin_site_id"]);
		$this->assign("rslist",$rslist);
		$this->view("all_domain");
	}

	public function domain_save_f()
	{
		if(!$this->popedom['domain']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$domain = $this->get("domain");
		if(!$domain){
			$this->json(P_Lang('域名不能为空'));
		}
		$domain = strtolower($domain);
		if(substr($domain,0,7) == "https://"){
			$domain = substr($domain,7);
		}
		if(substr($domain,0,8) == "https://"){
			$domain = substr($domain,8);
		}
		if(!$domain){
			$this->json(P_Lang('域名不符合规范'));
		}
		if($domain && $domain != str_replace("/","",$domain)){
			$this->json(P_Lang('域名不符合规范'));
		}
		$id = $this->get("id","int");
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		$domain_rs = $this->model('site')->domain_check($domain);
		if($id){
			if($domain_rs && $domain_rs["id"] != $id){
				$this->json(P_Lang('域名已存在，请检查'));
			}
			$this->model('site')->domain_update($domain,$id);
		}else{
			if($domain_rs){
				$this->json(P_Lang('域名已存在，请检查'));
			}
			$this->model('site')->domain_add($domain,$_SESSION["admin_site_id"]);
		}
		$this->json(true);
	}

	public function domain_default_f()
	{
		if(!$this->popedom['domain']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定域名ID'));
		}
		$domain_rs = $this->model('site')->domain_one($id);
		if(!$domain_rs){
			$this->json(P_Lang('域名信息不存在'));
		}
		if($domain_rs["site_id"] != $_SESSION["admin_site_id"]){
			$this->json(P_Lang('域名配置与网站不一致，请联系管理员'));
		}
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		if($domain_rs["id"] == $rs["domain_id"]){
			$this->json(P_Lang('此域名已经是主域名了，不用再设置'));
		}
		$array = array();
		$array["domain_id"] = $id;
		$this->model('site')->save($array,$_SESSION["admin_site_id"]);
		$this->json(true);
	}

	public function domain_delete_f()
	{
		if(!$this->popedom['domain']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定域名ID'));
		}
		$domain_rs = $this->model('site')->domain_one($id);
		if(!$domain_rs){
			$this->json(P_Lang('域名信息不存在'));
		}
		if($domain_rs["site_id"] != $_SESSION["admin_site_id"]){
			$this->json(P_Lang('域名配置与网站不一致，请联系管理员'));
		}
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		if($domain_rs["id"] == $rs["domain_id"]){
			$this->json(P_Lang('此域名是主域名，不能删除'));
		}
		$this->model('site')->domain_delete($id);
		$this->json(true);
	}

	public function domain_mobile_f()
	{
		if(!$this->popedom['domain']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定域名ID'));
		}
		$act_mobile = $this->get('act_mobile','int');
		$this->model('site')->set_mobile($id,$act_mobile);
		$this->json(true);
	}

	public function gset_f()
	{
		if(!$this->popedom["gset"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id","int");
		$rs = array();
		if($id){
			$this->assign("id",$id);
			$rs = $this->model('site')->all_one($id);
		}
		$status = $this->get('status','int');
		$icolist = $this->lib('file')->ls('images/ico/');
		if(($rs['ico'] && !in_array($rs['ico'],$icolist)) || !$rs['ico']){
			$rs['ico'] = 'images/ico/default.png';
		}
		$this->assign("rs",$rs);
		$this->assign("icolist",$icolist);
		$this->view("all_gset");
	}

	function gset_save_f()
	{
		if(!$this->popedom["gset"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id","int");
		$title = $this->get("title");
		$status = $this->get('status','int');
		$identifier = $this->get("identifier");
		$ico = $this->get("ico");
		$chk = $this->all_check($identifier,$id);
		$error_url = $this->url("all","gset");
		if($id){
			$error_url .= "&id=".$id;
		}
		if($chk != "ok"){
			error(P_Lang($chk),$error_url,"error");
		}
		$array = array();
		$array["title"] = $title;
		$array["status"] = $status;
		$array["ico"] = $ico;
		$array["identifier"] = $identifier;
		$array["site_id"] = $_SESSION["admin_site_id"];
		$this->model('site')->all_save($array,$id);
		error(P_Lang('扩展组配置成功'),$this->url("all"),'ok');
	}

	private function all_check($identifier,$id=0)
	{
		if(!$identifier){
			return "标识串不能为空";
		}
		$identifier = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier)){
			return "标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头";
		}
		if($identifier == "phpok" || $identifier == "config"){
			return "系统标识串：config 和 phpok 不允许用户自定义使用";
		}
		$check = $this->model('site')->all_check($identifier,$_SESSION["admin_site_id"],$id);
		if($check){
			return "标识串已经被使用了";
		}
		return "ok";
	}

	public function all_check_f()
	{
		$identifier = $this->get("identifier");
		$id = $this->get("id","int");
		$chk = $this->all_check($identifier,$id);
		if($chk == "ok"){
			$this->json(true);
		}else{
			$this->json(P_Lang($chk));
		}
	}

	public function set_f()
	{
		if(!$this->popedom["set"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'),$this->url("all"));
		}
		$this->assign("id",$id);
		$rs = $this->model('site')->all_one($id);
		if(!$rs){
			error(P_Lang('全局配置不存在'),$this->url("all"));
		}
		$this->assign("rs",$rs);
		$ext_module = "all-".$id;
		$this->assign("ext_module",$ext_module);
		$extlist = $this->model('ext')->ext_all($ext_module);
		if($extlist){
			$tmp = false;
			foreach($extlist AS $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v){
						$value[$k] = $v;
					}
				}
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
			}
			$this->assign('extlist',$tmp);
		}
		$this->view("all_set");
	}

	public function ext_save_f()
	{
		if(!$this->popedom["set"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id","int");
		if(!$id){
			error(P_Lang('未指定ID'),$this->url("all"));
		}
		$rs = $this->model('site')->all_one($id);
		if(!$rs){
			error(P_Lang('全局配置不存在'),$this->url("all"));
		}
		ext_save("all-".$id);
		$this->model('temp')->clean("all-".$id,$_SESSION["admin_id"]);
		error(P_Lang('扩展全局内容设置成功'),$this->url("all"));
	}

	public function ext_gdelete_f()
	{
		if(!$this->popedom["gset"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id","int");
		if(!$id){
			error(P_Lang('未指定ID'),$this->url("all"));
		}
		$rs = $this->model('site')->all_one($id);
		if($rs["is_system"]){
			error(P_Lang('系统模块不允许删除'),$this->url("all"),"error");
		}
		$this->model('site')->ext_delete($id);
		error(P_Lang('全局配置删除成功'),$this->url("all"),"ok");
	}

	public function add_f()
	{
		if(!$_SESSION['admin_rs']['if_system']){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$this->view("all_add");
	}

	public function addok_f()
	{
		if(!$_SESSION['admin_rs']['if_system']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->json(P_Lang('网站标题不能为空'));
		}
		$dir = "/";
		$status = 0;
		$content = "";
		$tpl_id = 0;
		$url_type = 'default';
		$logo = "";
		$meta = "";
		# 检测网站域名
		$domain = $this->get("domain");
		if(!$domain){
			$this->json("域名不能为空");
		}
		$domain = strtolower($domain);
		# 检查域名是否符合要求
		if(substr($domain,0,7) == "https://"){
			$domain = substr($domain,7);
		}
		if(substr($domain,0,8) == "https://"){
			$domain = substr($domain,8);
		}
		if(!$domain){
			$this->json(P_Lang('域名填写不规范'));
		}
		if($domain && $domain != str_replace("/","",$domain)){
			$this->json(P_Lang('域名不符合规范'));
		}
		$domain_rs = $this->model("site")->domain_check($domain);
		if($domain_rs){
			$this->json(P_Lang('域名已被使用，请更换'));
		}
		$array = array();
		$array["title"] = $title;
		$array["dir"] = $dir;
		$array["status"] = $status;
		$array["content"] = $content;
		$array["tpl_id"] = $tpl_id;
		$array["url_type"] = $url_type;
		$array["domain_id"] = "";
		$array["logo"] = $logo;
		$array["meta"] = $meta;
		$array["register_status"] = 0;
		$array["register_close"] = "";
		$array["login_status"] = 0;
		$array["login_close"] = "";
		$site_id = $this->model('site')->save($array);
		if(!$site_id){
			$this->json(P_Lang('创建失败'));
		}
		$domain_id = $this->model('site')->domain_add($domain,$site_id);
		if($domain_id){
			$tmp = array('domain_id'=>$domain_id);
			$this->model("site")->save($tmp,$site_id);
		}
		$this->json(P_Lang('创建网站完成，您可以切换到新网站中进行栏目配置'),true);
	}

	public function email_f()
	{
		$content = form_edit('content','','editor','height=300&btn_image=1&etype=simple');
		$this->assign('content',$content);
		$this->view("all_email");
	}
}
?>
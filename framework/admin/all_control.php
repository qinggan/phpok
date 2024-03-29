<?php
/**
 * 全局栏目配置
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2018年10月25日
**/

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

	public function setting_f()
	{
		if(!$this->popedom["site"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		# 读取风格列表
		$tpl_list = $this->model('tpl')->get_all();
		$this->assign("tpl_list",$tpl_list);
		// 获取网站语言包列表
		$multiple_language = false;
		if(isset($this->config['multiple_language'])){
			$multiple_language = $this->config['multiple_language'];
		}
		if($multiple_language){
			$langlist = $this->model('lang')->get_list();
			$this->assign("langlist",$langlist);
		}
		$this->assign('multiple_language',$multiple_language);

		//读取网站货币
		$currency_list = $this->model('currency')->get_list();
		$this->assign("currency_list",$currency_list);

		//读取支付方式
		$payment = $this->model('payment')->get_all();
		$this->assign("payment",$payment);

		//邮件模板列表
		$emailtpl = $this->model('email')->simple_list($this->session->val('admin_site_id'));
		$this->assign("emailtpl",$emailtpl);

		//运费列表
		$freight = $this->model('freight')->get_all();
		$this->assign('freight',$freight);

		//检测是否有启用邮件通知
		$gateway_email = $this->model('gateway')->get_default('email');
		if($rs['login_type'] && $rs['login_type'] == 'email' && !$gateway_email){
			$rs['login_type'] = 0;
		}
		$this->assign('gateway_email',$gateway_email);
		if($gateway_email){
			$_etpl = array();
			foreach($emailtpl as $key=>$value){
				if(substr($value['identifier'],0,4) != 'sms_'){
					$_etpl[$key] = $value;
				}
			}
			$this->assign('email_tplist',$_etpl);
		}
		$gateway_sms = $this->model('gateway')->get_default('sms');
		if(!$gateway_sms && $rs['login_type'] && $rs['login_type'] == 'sms'){
			$rs['login_type'] = 0;
		}
		$this->assign('gateway_sms',$gateway_sms);
		if($gateway_sms){
			$_etpl = array();
			foreach($emailtpl as $key=>$value){
				if(substr($value['identifier'],0,4) == 'sms_'){
					$_etpl[$key] = $value;
				}
			}
			$this->assign('sms_tplist',$_etpl);
		}
		$code_editor_info = form_edit('meta',$rs['meta'],'code_editor','width=650&height=200');
		$this->assign('code_editor_info',$code_editor_info);
		$this->assign("rs",$rs);
		$this->model('log')->add(P_Lang('访问【网站信息】'));
		$this->view("all_setting");
	}

	public function tpl_setting_f()
	{
		if(!$this->popedom["site"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定站点ID'));
		}
		$rs = $this->model('site')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('站点信息不存在'));
		}
		$this->assign('site_rs',$rs);
		$tplid = $this->get('tplid','int');
		$ext = 'html';
		if($tplid){
			$tpl_rs = $this->model('tpl')->get_one($tplid);
			if($tpl_rs && $tpl_rs['ext']){
				$ext = $tpl_rs['ext'];
			}
		}
		$this->assign('tplext',$ext);
		$filelist = $this->model('tpl')->all_files();
		$this->assign('filelist',$filelist);
		//读取项目模块
		$tpls = $this->model('site')->tpl_setting();
		$this->assign('tpls',$tpls);
		$this->assign('id',$id);
		$this->assign('tplid',$tplid);
		$this->model('log')->add(P_Lang('访问【网站信息里的自定义模板】'));
		$this->view('all_tpl');
	}

	/**
	 * 初始化站点模板
	**/
	public function tpl_resetting_f()
	{
		if(!$this->popedom["site"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->model('site')->tpl_reset();
		$this->model('log')->add(P_Lang('初始化【自定义模板】'));
		$this->success();
	}

	public function tpl_setting_save_f()
	{
		if(!$this->popedom["site"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$list = $this->model('site')->tpl_default();
		if(!$list){
			$list = array();
		}
		$all = array();
		foreach($list as $key=>$value){
			$tmp = $this->get($key);
			if($tmp){
				$all[$key] = $tmp;
			}
		}
		if(!$all){
			$this->model('site')->tpl_reset();
		}else{
			$this->model('site')->tpl_setting($all);
		}
		$this->model('log')->add(P_Lang('保存【自定义模板】'));
		$this->success();
	}

	/**
	 * 存储全局配置
	**/
	public function save_f()
	{
		if(!$this->popedom["site"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$error_url = $this->url("all","setting");
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('网站标题不能为空'),$error_url);
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
		$site_rs = $this->model('site')->get_one($this->session->val('admin_site_id'));
		if(!$site_rs){
			$this->error(P_Lang('网站信息不存在'),$error_url);
		}
		$domain_id = $site_rs["domain_id"];
		$array = array();
		$array["title"] = $title;
		$array["dir"] = $dir;
		$array["status"] = $status;
		$array["content"] = $content;
		$array["tpl_id"] = $tpl_id;
		$array["url_type"] = $url_type;
		$array["domain_id"] = $domain_id;
		$array["logo"] = $logo;
		$array['logo_mobile'] = $this->get('logo_mobile');
		$array["meta"] = $meta;
		$array["register_status"] = $this->get("register_status","int");
		$array["register_close"] = $this->get("register_close");
		$array["login_status"] = $this->get("login_status","int");
		$array["login_close"] = $this->get("login_close");
		//登录模式2016年11月22日
		$array['login_type'] = $this->get('login_type');
		$array['login_type_email'] = $this->get('login_type_email');
		$array['login_type_sms'] = $this->get('login_type_sms');
		$array["adm_logo29"] = $this->get("adm_logo29");
		$array['adm_logo50'] = $this->get('adm_logo50');
		$array["adm_logo180"] = $this->get("adm_logo180");
		$array["lang"] = $this->get("lang");
		$array['api'] = $this->get('api','int');
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array["currency_id"] = $this->get("currency_id","int");
		$array["biz_sn"] = $this->get("biz_sn");
		$array["biz_payment"] = $this->get("biz_payment","int");
		$array['upload_guest'] = $this->get('upload_guest','int');
		$array['upload_user'] = $this->get('upload_user','int');
		$array['biz_status'] = $this->get('biz_status','int');
		$array['biz_freight'] = $this->get('biz_freight');
		$array['biz_main_service'] = $this->get('biz_main_service','int');
		$array['biz_is_user'] = $this->get('biz_is_user');
		$array['favicon'] = $this->get('favicon');

		$this->model('site')->save($array,$this->session->val('admin_site_id'));
		$this->model('log')->add(P_Lang('更新【网站信息】'));
		$this->success(P_Lang('网站信息更新完成'),$this->url("all","setting"));
	}

	public function domain_f()
	{
		if(!$this->popedom["domain"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('site')->get_one($_SESSION["admin_site_id"]);
		$this->assign("rs",$rs);
		$rslist = $this->model('site')->domain_list($_SESSION["admin_site_id"]);
		$this->assign("rslist",$rslist);
		$this->model('log')->add(P_Lang('访问【网站域名】'));
		$this->view("all_domain");
	}

	/**
	 * 验证码权限配置
	**/
	public function vcode_f()
	{
		if(!$this->popedom['site']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$vcodelist = $this->model('site')->vcode_all();
		$condition = "module>0 AND (post_status=1 || comment_status=1)";
		$plist = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
		if($plist){
			foreach($plist as $key=>$value){
				$tmpid = 'p-'.$value['id'];
				$tmp = array('add'=>array('title'=>P_Lang('添加'),'status'=>1));
				$tmp['edit'] = array('title'=>P_Lang('修改'),'status'=>1);
				$tmp['comment'] = array('title'=>P_Lang('评论'),'status'=>1);
				if($vcodelist[$tmpid] && $vcodelist[$tmpid]['list']){
					if(isset($vcodelist[$tmpid]['list']['add'])){
						$tmp['add']['status'] = $vcodelist[$tmpid]['list']['add']['status'];
					}
					if(isset($vcodelist[$tmpid]['list']['edit'])){
						$tmp['edit']['status'] = $vcodelist[$tmpid]['list']['edit']['status'];
					}
					if(isset($vcodelist[$tmpid]['list']['comment'])){
						$tmp['comment']['status'] = $vcodelist[$tmpid]['list']['comment']['status'];
					}
				}
				$vcodelist[$tmpid] = array('title'=>$value['title'],'list'=>$tmp);
			}
			foreach($vcodelist as $key=>$value){
				if($key == 'system'){
					continue;
				}
				$tmpid = substr($key,2);
				if(!$plist[$tmpid]){
					unset($vcodelist[$key]);
				}
			}
		}else{
			foreach($vcodelist as $key=>$value){
				if($key == 'system'){
					continue;
				}
				unset($vcodelist[$key]);
			}
		}
		$this->assign('vcodelist',$vcodelist);
		$this->model('log')->add(P_Lang('访问【验证码配置页】'));
		$this->view('all_vcode');
	}

	public function vcode_save_f()
	{
		if(!$this->popedom['site']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		//读取系统的验证码
		$tmp = array();
		$tmp['system'] = array();
		$tmp['system']['title'] = P_Lang('系统设置');
		$tmp['system']['list'] = array();
		$tmp['system']['list']['login'] = array('title'=>P_Lang('登录'),'status'=>$this->get('system-login','checkbox'));
		$tmp['system']['list']['register'] = array('title'=>P_Lang('注册'),'status'=>$this->get('system-register','checkbox'));
		$tmp['system']['list']['getpass'] = array('title'=>P_Lang('忘记密码'),'status'=>$this->get('system-getpass','checkbox'));
		$condition = "module>0 AND (post_status=1 || comment_status=1)";
		$plist = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
		if($plist){
			foreach($plist as $key=>$value){
				$tmpid = 'p-'.$value['id'];
				$tmp[$tmpid] = array('list'=>array());
				$tmp[$tmpid]['list']['add'] = array('status'=>$this->get($tmpid.'-add','checkbox'));
				$tmp[$tmpid]['list']['edit'] = array('status'=>$this->get($tmpid.'-edit','checkbox'));
				$tmp[$tmpid]['list']['comment'] = array('status'=>$this->get($tmpid.'-comment','checkbox'));
			}
		}
		$this->lib('xml')->save($tmp,$this->dir_data.'xml/vcode_'.$this->session->val('admin_site_id').'.xml');
		$this->model('log')->add(P_Lang('保存【验证码配置】'));
		$this->success();
	}

	/**
	 * 保存域名信息
	**/
	public function domain_save_f()
	{
		if(!$this->popedom['domain']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$domain = $this->get("domain");
		if(!$domain){
			$this->error(P_Lang('域名不能为空'));
		}
		$domain = strtolower($domain);
		if(substr($domain,0,7) == "http://"){
			$domain = substr($domain,7);
		}
		if(substr($domain,0,8) == "https://"){
			$domain = substr($domain,8);
		}
		if(!$domain){
			$this->error(P_Lang('域名不符合规范'));
		}
		if($domain && $domain != str_replace("/","",$domain)){
			$this->error(P_Lang('域名不符合规范'));
		}
		$id = $this->get("id","int");
		$rs = $this->model('site')->get_one($this->session->val('admin_site_id'));
		if($id){
			$this->model('site')->domain_update($domain,$id);
		}else{
			$this->model('site')->domain_add($domain,$this->session->val('admin_site_id'));
		}
		$this->model('log')->add(P_Lang('保存【域名设置】'));
		$this->success();
	}

	/**
	 * 设置默认域名
	**/
	public function domain_default_f()
	{
		if(!$this->popedom['domain']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定域名ID'));
		}
		$domain_rs = $this->model('site')->domain_one($id);
		if(!$domain_rs){
			$this->error(P_Lang('域名信息不存在'));
		}
		if($domain_rs["site_id"] != $_SESSION["admin_site_id"]){
			$this->error(P_Lang('域名配置与网站不一致，请联系管理员'));
		}
		$rs = $this->model('site')->get_one($this->session->val('admin_site_id'));
		if($domain_rs["id"] == $rs["domain_id"]){
			$this->error(P_Lang('此域名已经是主域名了，不用再设置'));
		}
		$array = array();
		$array["domain_id"] = $id;
		$this->model('site')->save($array,$this->session->val('admin_site_id'));
		$this->model('log')->add(P_Lang('设置【默认域名】'));
		$this->success();
	}

	/**
	 * 域名删除
	**/
	public function domain_delete_f()
	{
		if(!$this->popedom['domain']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定域名ID'));
		}
		$domain_rs = $this->model('site')->domain_one($id);
		if(!$domain_rs){
			$this->error(P_Lang('域名信息不存在'));
		}
		if($domain_rs["site_id"] != $this->session->val('admin_site_id')){
			$this->error(P_Lang('域名配置与网站不一致，请联系管理员'));
		}
		$this->model('site')->domain_delete($id);
		$this->model('log')->add(P_Lang('删除域名'));
		$this->success();
	}

	/**
	 * 设置或取消手机模板域名
	**/
	public function domain_mobile_f()
	{
		if(!$this->popedom['domain']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定域名ID'));
		}
		$act_mobile = $this->get('act_mobile','int');
		$this->model('site')->set_mobile($id,$act_mobile);
		if($act_mobile){
			$this->model('log')->add(P_Lang('设置【手机域名】'));
		}else{
			$this->model('log')->add(P_Lang('取消【手机域名】'));
		}
		$this->success();
	}

	public function gset_f()
	{
		if(!$this->popedom["gset"]){
			$this->error(P_Lang('您没有权限执行此操作'));
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
		$this->model('log')->add(P_Lang('配置【全局项目扩展】'));
		$this->view("all_gset");
	}

	public function gset_save_f()
	{
		if(!$this->popedom["gset"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		$title = $this->get("title");
		$status = $this->get('status','int');
		$identifier = $this->get("identifier");
		$ico = $this->get("ico");
		$chk = $this->all_check($identifier,$id);
		if($chk != "ok"){
			$this->error(P_Lang($chk));
		}
		$array = array();
		$array["title"] = $title;
		$array["status"] = $status;
		$array["ico"] = $ico;
		$array["identifier"] = $identifier;
		$array['is_api'] = $this->get('is_api','int');
		$array["site_id"] = $this->session->val('admin_site_id');
		$this->model('site')->all_save($array,$id);
		$this->model('log')->add(P_Lang('保存【全局扩展】'));
		$this->success();
	}

	/**
	 * 编辑扩展配置
	 * @参数 $id 扩展ID
	 * @返回 HTML页面
	**/
	public function set_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->assign("id",$id);
		$rs = $this->model('site')->all_one($id);
		if(!$rs){
			$this->error(P_Lang('全局配置不存在'));
		}
		$this->assign("rs",$rs);
		$ext_module = "all-".$id;
		$this->assign("ext_module",$ext_module);
		$extlist = $this->model('ext')->ext_all($ext_module);
		if($extlist){
			$tmp = array();
			foreach($extlist as $key=>$value){
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
			}
			$this->assign('extlist',$tmp);
		}
		$this->model('log')->add(P_Lang('访问【扩展编辑#{0}】',$id));
		$this->view("all_set");
	}

	public function ext_save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('site')->all_one($id);
		if(!$rs){
			$this->error(P_Lang('全局配置不存在'));
		}
		ext_save("all-".$id);
		$this->model('log')->add(P_Lang('保存【扩展编辑#{0}】',$id));
		$this->success(P_Lang('扩展全局内容设置成功'),$this->url("all"));
	}

	public function ext_gdelete_f()
	{
		if(!$this->popedom["gset"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('site')->all_one($id);
		if($rs["is_system"]){
			$this->error(P_Lang('系统模块不允许删除'));
		}
		$this->model('site')->ext_delete($id);
		$this->model('log')->add(P_Lang('删除【全局扩展组#{0}】',$id));
		$this->success();
	}

	public function system_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		if(!$this->session->val('admin2verify')){
			$this->view('admin_vcode');
		}
		$rs = $this->model('config')->get_all();
		if($rs['admin_homepage_setting']){
			$rs['admin_homepage_setting'] = explode(",",$rs['admin_homepage_setting']);
		}else{
			$rs['admin_homepage_setting'] = array();
		}
		$links = $rs['ok_links'] ? unserialize($rs['ok_links']) : array();
		$this->assign('links',$links);
		$this->assign('rs',$rs);
		$this->model('log')->add(P_Lang('访问【系统参数设置页】'));
		$this->view("all_system");
	}

	public function system_save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未通过二次验证'));
		}
		$data = array();
		$data['api_code'] = $this->get('api_code','html');
		$data['public_key'] = $this->get('public_key','html');
		$data['private_key'] = $this->get('private_key','html');
		$data['public_key2'] = $this->get('public_key2','html');
		$data['private_key2'] = $this->get('private_key2','html');
		$data['chktype'] = $this->get('chktype');
		$tmp = $this->get("admin_homepage_setting");
		if($tmp){
			$data['admin_homepage_setting'] = implode(",",$tmp);
		}else{
			$data['admin_homepage_setting'] = '';
		}
		$data['ok_status'] = $this->get('ok_status','int');
		$data['ok_appid'] = $this->get('ok_appid');
		$data['ok_appkey'] = $this->get('ok_appkey');
		$data['ok_links'] = $this->get('links');
		if($data['ok_links']){
			$data['ok_links'] = serialize($data['ok_links']);
		}
		$this->model('config')->save($data);
		$this->model('log')->add(P_Lang('保存系统参数'));
		$this->success();
	}

	public function rsa_f()
	{
		$email = $this->lib('common')->str_rand(5,'number').'@'.$this->lib('common')->str_rand(8,'letter').'.cn';
		$rs = $this->lib('token')->create($email);
		if(!$rs){
			$this->error('证书生成失败，请检查系统环境');
		}
		$this->model('log')->add(P_Lang('执行新证书生成'));
		$this->success($rs);
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
}
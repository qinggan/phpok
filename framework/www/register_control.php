<?php
/**
 * 用户注册
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2016年07月25日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class register_control extends phpok_control
{
	/**
	 * 构造函数
	**/
	public function __construct()
	{
		parent::control();
	}

	/**
	 * 注册页面，包含注册验证页，使用到模板：register_check_项目ID
	 * @参数 _back 返回上一页
	 * @参数 _code 验证码
	 * @参数 email 邮箱
	**/
	public function index_f()
	{
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->config['url'];
		}
		if($this->session->val('user_id')){
			$this->error(P_Lang('您已登录，不用注册'),$_back);
		}
		$this->assign('_back',$_back);
		if(!$this->site['register_status']){
			$tips = $this->site["register_close"] ? $this->site["register_close"] : P_Lang('系统暂停用户注册，请联系站点管理员');
			$this->error($tips);
		}
		//取得开放的用户组信息
		$grouplist = $this->model("usergroup")->opened_grouplist();
		if(!$grouplist){
			$this->error(P_Lang('未找到有效的用户组信息'),$_back,10);
		}
		$this->assign("grouplist",$grouplist);
		$gid = $this->get("group_id","int");
		if($gid){
			$group_rs = $this->model("usergroup")->get_one($gid);
			if(!$group_rs || !$group_rs["status"]){
				$gid = 0;
			}
		}
		if(!$gid){
			if(count($grouplist) == 1){
				$group_rs = current($grouplist);
				$gid = $group_rs['id'];
			}else{
				foreach($grouplist as $key=>$value){
					if($value["is_default"]){
						$gid = $value["id"];
						$group_rs = $value;
					}
				}
			}
		}
		//判断是否使用验证码注册
		$this->assign("group_id",$gid);
		$this->assign("group_rs",$group_rs);
		//取得当前组的扩展字段
		$ext_list = $this->model("user")->fields_all("is_front=1");
		$extlist = array();
		if(!$ext_list){
			$ext_list = array();
		}
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
			if(!$group_rs['fields'] || ($group_rs['fields'] && in_array($value['identifier'],explode(",",$group_rs['fields'])))){
				$extlist[] = $this->lib('form')->format($value);
			}
		}
		$this->assign("extlist",$extlist);
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register';
		}
		if($group_rs['register_status'] && $group_rs['register_status'] != '1'){
			$tplfile = $this->model('site')->tpl_file($this->ctrl,$group_rs['register_status']);
			if(!$tplfile){
				$tplfile = 'register_'.$group_rs['register_status'];
			}
		}
		$this->assign('is_vcode',$this->model('site')->vcode('system','register'));
		$this->view($tplfile);
	}

	/**
	 * 友情提示页
	**/
	public function tip_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register_tip';
		}
		$this->view($tplfile);
	}

	/**
	 * 注册成功页
	**/
	public function success_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register_success';
		}
		$this->view($tplfile);
	}

	public function readme_f()
	{
		$tplfile = $this->model('site')->tpl_file($this->ctrl,$this->func);
		if(!$tplfile){
			$tplfile = 'register_readme';
		}
		$this->view($tplfile);
	}
}
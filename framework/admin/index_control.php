<?php
/***********************************************************
	Filename: phpok/admin/index_control.php
	Note	: 后台首页控制台
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-10-19 13:03
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	function __construct()
	{
		parent::control();
	}

	public function index_f()
	{
		if(!$this->license_code){
			$this->license = "LGPL";
		}
		$license = strtoupper($this->license);
		$code = P_Lang('LGPL开源授权');
		if($license == "PBIZ" && $this->license_code && $this->license_name){
			$code = P_Lang('个人（{license}）商业授权',array('license'=>$this->license_name));
		}elseif($license == "CBIZ" && $this->license_code && $this->license_name){
			$code = P_Lang('企业（{license}）商业授权',array('license'=>$this->license_name));
		}
		$license_site = $this->license_site;
		if(substr($license_site,0,1) == '.'){
			$license_site = substr($license_site,1);
		}
		$this->assign('license_site',$license_site);
		$this->assign("license",$code);
		$this->assign("version",$this->version);
		$sitelist = $this->model('site')->get_all_site();
		if(!$_SESSION['admin_rs']['if_system']){
			foreach($sitelist as $key=>$value){
				$chk_popedom = $this->model('popedom')->site_popedom($value['id'],$_SESSION['admin_id']);
				if(!$chk_popedom){
					unset($sitelist[$key]);
				}
			}
			if(!$sitelist){
				error(P_Lang('没有找到相关权限，请联系管理员'));
			}
		}
		$this->assign('sitelist',$sitelist);
		$plist = $this->model('popedom')->get_all('',false,false);
		$popedom_m = $popedom_p = array();
		foreach($plist AS $key=>$value){
			if(!$value["pid"]){
				$popedom_m[$value["gid"]][] = $value["id"];
			}else{
				$popedom_p[] = $value["id"];
			}
		}
		$popedom = $_SESSION["admin_rs"]["if_system"] ? array("all") : $_SESSION["admin_popedom"];
		if(!$popedom){
			$popedom = array();
		}
		$menulist = $this->model('sysmenu')->get_all($_SESSION["admin_site_id"],1);
		if(!$menulist){
			$menulist = array();
		}
		$ftmp = array('list','index','res');
		foreach($menulist AS $key=>$value){
			if(!$value["sublist"] || !is_array($value["sublist"]) || count($value["sublist"]) < 1){
				unset($menulist[$key]);
				continue;
			}
			foreach($value["sublist"] AS $k=>$v){
				if(!in_array($v['appfile'],$ftmp) && !$_SESSION['admin_rs']['if_system'] && $popedom_m[$v['id']]){
					if(!$popedom_m || !$popedom_m[$v['id']] || !is_array($popedom_m[$v['id']]) || count($popedom_m[$v["id"]])<1){
						unset($value["sublist"][$k]);
						continue;
					}
					$tmp = array_intersect($popedom,$popedom_m[$v["id"]]);
					if(!$tmp){
						unset($value["sublist"][$k]);
						continue;
					}
				}
				if($v["appfile"] == "list" && !$_SESSION["admin_rs"]["if_system"]){
					if(!$popedom_p || count($popedom_p)<1){
						unset($value["sublist"][$k]);
						continue;
					}else{
						$tmp = array_intersect($popedom,$popedom_p);
						if(!$tmp){
							unset($value["sublist"][$k]);
							continue;
						}
					}
				}
				$ext = "menu_id=".$v["id"];
				if($v["identifier"]) $ext .= "&identifier=".$v["identifier"];
				if($v['ext']) $ext .= "&".$v['ext'];
				$v['url'] = $this->url($v['appfile'],$v['func'],$ext);
				$value['sublist'][$k] = $v;
			}
			if(!$value["sublist"] || !is_array($value["sublist"]) || count($value["sublist"]) < 1){
				unset($menulist[$key]);
				continue;
			}
			$menulist[$key] = $value;
		}
		$this->assign('menulist',$menulist);
		
		if($menulist){
			$iconlist = false;
			foreach($menulist as $key=>$value){
				if($value['sublist']){
					foreach($value['sublist'] as $k=>$v){
						if($v['icon']){
							$iconlist[] = $v;
						}
					}
				}
			}
			if($iconlist){
				$this->assign('iconlist',$iconlist);
			}
		}
		$all_info = $this->all_info();
		if($all_info){
			$this->assign('all_info',$all_info);
		}
		$list_setting = $this->list_setting();
		if($list_setting){
			$this->assign('list_setting',$list_setting);
		}
		//读取语言包
		$langlist = $this->model('lang')->get_list();
		$this->assign('langlist',$langlist);
		$this->view("index");
	}

	public function all_setting_f()
	{
		$info = $this->all_info();
		if(!$info){
			$this->json(false);
		}
		$this->json($info,true);
	}

	private function all_info()
	{
		$all_popedom = appfile_popedom("all");
		if(!$all_popedom || !$all_popedom['list']){
			return false;
		}
		$this->assign('all_popedom',$all_popedom);
		$site_popedom = appfile_popedom('site');
		$this->assign('site_popedom',$site_popedom);
		$rslist = $this->model('site')->all_list($_SESSION["admin_site_id"]);
		$this->assign("all_rslist",$rslist);
		$rs = $this->model('site')->get_one($_SESSION['admin_site_id']);
		$this->assign("all_rs",$rs);
		return $this->fetch('index_block_allsetting');
	}

	public function list_setting_f()
	{
		$info = $this->list_setting();
		if(!$info){
			$this->json(false);
		}
		$this->json($info,true);
	}

	private function list_setting()
	{
		$site_id = $_SESSION["admin_site_id"];
		$rslist = $this->model('project')->get_all($site_id,0,"p.status=1 AND p.hidden=0");
		if(!$rslist){
			$rslist = array();
		}
		if(!$_SESSION["admin_rs"]["if_system"]){
			if(!$_SESSION["admin_popedom"]){
				return false;
			}
			$condition = "parent_id>0 AND appfile='list' AND func=''";
			$p_rs = $this->model('sysmenu')->get_one_condition($condition);
			if(!$p_rs){
				return false;
			}
			$gid = $p_rs["id"];
			$popedom_list = $this->model('popedom')->get_all("gid='".$gid."' AND pid>0",false,false);
			if(!$popedom_list){
				return false;
			}
			$popedom = array();
			foreach($popedom_list AS $key=>$value){
				if(in_array($value["id"],$_SESSION["admin_popedom"])){
					$popedom[$value["pid"]][$value["identifier"]] = true;
				}
			}
			foreach($rslist AS $key=>$value){
				if(!$popedom[$value["id"]] || !$popedom[$value["id"]]["list"]){
					unset($rslist[$key]);
					continue;
				}
			}
		}
		if(!$rslist || count($rslist)< 1){
			return false;
		}
		foreach($rslist as $key=>$value){
			$value['url'] = $this->url('list','action','id='.$value['id']);
			$rslist[$key] = $value;
		}
		//系统管理员
		if($_SESSION['admin_rs']['if_system']){
			$chk = $this->model('workflow')->chk();
			if($chk){
				$tmp = array('title'=>P_Lang('我授权的'),'ico'=>'images/ico/manage.png','id'=>'workflow');
				$tmp['url'] = $this->url('workflow','manage');
				$rslist[] = $tmp;
			}
		}else{
			$chk = $this->model('workflow')->chk("admin_id=".$_SESSION['admin_id']);
			if($chk){
				$tmp = array('title'=>P_Lang('我管理的'),'ico'=>'images/ico/manage.png','id'=>'workflow');
				$tmp['url'] = $this->url('workflow','list');
				$rslist[] = $tmp;
			}
		}
		$this->assign('list_rslist',$rslist);
		return $this->fetch('index_block_listsetting');
	}

	public function clear_f()
	{
		$this->lib('file')->rm($this->dir_root."data/tpl_www/");
		$this->lib('file')->rm($this->dir_root."data/tpl_admin/");
		$this->lib('file')->rm($this->dir_root."data/tpl_html/");
		$this->cache->clear();
		$this->json(true);
	}

	public function site_f()
	{
		$siteid = $this->get("id","int");
		if(!$siteid){
			error(P_Lang('请选择要维护的站点'),$this->ur('index'));
		}
		$rs = $this->model("site")->get_one($siteid);
		if(!$rs){
			error(P_Lang('站点信息不存在'),$this->url("index"));
		}
		$_SESSION['admin_site_id'] = $siteid;
		$tip = P_Lang('您正在切换到网站：{sitename}，请稍候…',array('sitename'=>"<span style='color:red;font-weight:bold;'>".$rs['title']."</span>"));
		error($tip,$this->url("index"),"ok");
	}

	//获取待处理信息
	function pendding_f()
	{
		$list = false;
		//读取未操作的主题
		$rslist = $this->model('list')->pending_info($_SESSION['admin_site_id']);
		if($rslist){
			foreach($rslist AS $key=>$value){
				if(!$value['parent_id']){
					$url = $this->url("list","action","id=".$value["pid"]);
					$list['project_'.$value['pid']] = array("title"=>$value["title"],"total"=>$value["total"],"url"=>$url,'id'=>$value['pid']);
				}
			}
			//合并子项目提示
			foreach($rslist as $key=>$value){
				if(!$value['total'] || !$value['parent_id']){
					continue;
				}
				if($list['project_'.$value['parent_id']]){
					$list['project_'.$value['parent_id']]['total'] += $value['total'];
				}else{
					$url = $this->url("list","action","id=".$value["pid"]);
					$list['project_'.$value['parent_id']] = array("title"=>$value["title"],"total"=>$value["total"],"url"=>$url,'id'=>$value['parent_id']);
				}
			}
			if($list){
				foreach($list as $key=>$value){
					if(!$value['total']){
						unset($list[$key]);
					}
				}
			}
		}
		//读取未审核的会员信息
		$condition = "u.status=0";
		$user_total = $this->model('user')->get_count($condition);
		if($user_total > 0){
			$url = $this->url("user","","status=3");
			$list['ctrl_user'] = array("title"=>P_Lang('会员列表'),"total"=>$user_total,"url"=>$url,'id'=>'user');
		}
		//读取未审核的回复信息
		$condition = "status=0";
		$reply_total = $this->model('reply')->get_total($condition);
		if($reply_total>0){
			$url = $this->url("reply","","status=3");
			$list['ctrl_reply'] = array("title"=>P_Lang('评论管理'),"total"=>$reply_total,"url"=>$url,'id'=>'reply');
		}
		if(!$list){
			$this->json(P_Lang('没有消息'));
		}
		$this->json($list,true);
	}

	function pendding_sublist_f()
	{
		$list = false;
		$rslist = $this->model('list')->pending_info($_SESSION['admin_site_id']);
		if($rslist){
			foreach($rslist AS $key=>$value){
				if($value['parent_id']){
					$url = $this->url("list","action","id=".$value["pid"]);
					$list['project_'.$value['pid']] = array("title"=>$value["title"],"total"=>$value["total"],"url"=>$url,'id'=>$value['pid']);
				}
			}
		}
		if(!$list){
			$this->json(P_Lang('没有消息'));
		}
		$this->json($list,true);
	}
}
?>
<?php
/**
 * 后台首页控制台
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月13日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class index_control extends phpok_control
{
	public function __construct()
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
		if(!$plist){
			$plist = array();
		}
		$popedom_m = $popedom_p = array();
		foreach($plist as $key=>$value){
			if(!$value["pid"]){
				$popedom_m[$value["gid"]][] = $value["id"];
			}else{
				$popedom_p[] = $value["id"];
			}
		}
		$popedom = $this->session->val('admin_rs.if_system') ? array("all") : $_SESSION["admin_popedom"];
		if(!$popedom){
			$popedom = array();
		}
		
		$site_rs = $this->model('site')->get_one($this->session->val('admin_site_id'));
		$condition = '';
		if(!$site_rs['biz_status']){
			$biz_ctrl = array('order','options','payment','currency','express','freight');
			$string = implode("','",$biz_ctrl);
			$condition = "appfile NOT IN('".$string."')";
		}
		$menulist = $this->model('sysmenu')->get_all($_SESSION["admin_site_id"],1,$condition);
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
				if($v["appfile"] == "list" && !$this->session->val('admin_rs.if_system')){
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
		//检测插件列表有没有快捷图标
		$plugin_mlist = $plugin_alist = $plugin_glist = array();
		if($this->plugin && is_array($this->plugin)){
			foreach($this->plugin as $key=>$value){
				$tmplist = $this->model('plugin')->iconlist($key);
				if(!$tmplist){
					continue;
				}
				foreach($tmplist as $k=>$v){
					$tmp = array();
					$tmp['url'] = $this->url('plugin','exec','id='.$key.'&exec='.$v['efunc']);
					$tmp['title'] = $v['title'];
					if($v['type'] == 'menu'){
						$tmp['icon'] = $v['icon'];
					}else{
						$tmp['ico'] = 'images/ico/'.$v['icon'];
						$tmp['id'] = $key.'-'.$v['id'];
					}
					$tmp['appfile'] = 'plugin';
					if($v['type'] == 'menu'){
						$plugin_mlist[] = $tmp;
					}
					if($v['type'] == 'all'){
						$plugin_alist[] = $tmp;
					}
					if($v['type'] == 'content'){
						$plugin_glist[] = $tmp;
					}
				}
			}
		}
		$iconlist = false;
		if($menulist){
			foreach($menulist as $key=>$value){
				if(!$value['sublist']){
					continue;
				}
				foreach($value['sublist'] as $k=>$v){
					if(!$v['icon']){
						continue;
					}
					$iconlist[] = $v;
				}
			}
		}
		if($plugin_mlist && count($plugin_mlist)>0){
			foreach($plugin_mlist as $key=>$value){
				$iconlist[] = $value;
			}
		}
		if($iconlist){
			$this->assign('iconlist',$iconlist);
		}
		$all_info = $this->all_info($plugin_alist);
		if($all_info){
			$this->assign('all_info',$all_info);
		}
		$list_setting = $this->list_setting($plugin_glist);
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

	private function all_info($alist='')
	{
		$all_popedom = appfile_popedom("all");
		if(!$all_popedom || !$all_popedom['list']){
			return false;
		}
		$this->assign('all_popedom',$all_popedom);
		$site_popedom = appfile_popedom('site');
		$this->assign('site_popedom',$site_popedom);
		$rslist = $this->model('site')->all_list($this->session->val('admin_site_id'));
		$this->assign("all_rslist",$rslist);
		$rs = $this->model('site')->get_one($this->session->val('admin_site_id'));
		$this->assign("all_rs",$rs);
		if($alist && is_array($alist)){
			$this->assign('plugin_alist',$alist);
		}
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

	private function list_setting($glist)
	{
		$rslist = $this->model('project')->get_all($this->session->val('admin_site_id'),0,"p.status=1 AND p.hidden=0");
		if(!$rslist){
			$rslist = array();
		}
		if(!$this->session->val('admin_rs.if_system')){
			if(!$this->session->val('admin_popedom')){
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
				if(in_array($value["id"],$this->session->val('admin_popedom'))){
					$popedom[$value["pid"]][$value["identifier"]] = true;
				}
			}
			foreach($rslist as $key=>$value){
				if(!$popedom[$value["id"]] || !$popedom[$value["id"]]["list"]){
					unset($rslist[$key]);
					continue;
				}
			}
		}
		if($rslist && is_array($rslist)){
			foreach($rslist as $key=>$value){
				$value['url'] = $this->url('list','action','id='.$value['id']);
				$rslist[$key] = $value;
			}
		}
		if($glist && is_array($glist) && count($glist)>0){
			foreach($glist as $key=>$value){
				$rslist[] = $value;
			}
		}
		$this->assign('list_rslist',$rslist);
		return $this->fetch('index_block_listsetting');
	}

	/**
	 * 清空缓存，包括过时的购物车，及Data目录下的Session文件
	**/
	public function clear_f()
	{
		$this->model('cart')->clear_expire_cart();
		$this->lib('file')->rm($this->dir_data."tpl_www/");
		$this->lib('file')->rm($this->dir_data."tpl_admin/");
		$this->lib('file')->rm($this->dir_cache);
		//清空SESSION过期的SESSION文件
		$list = $this->lib('file')->ls($this->dir_data.'session/');
		if($list){
			foreach($list as $key=>$value){
				if(filesize($value)>0 && (filemtime($value) + $this->session->timeout()) > $this->time){
					continue;
				}
				$this->lib('file')->rm($value);
			}
		}
		$this->cache->clear();
		$this->json(true);
	}

	/**
	 * 站点切换提示
	**/
	public function site_f()
	{
		$siteid = $this->get("id","int");
		if(!$siteid){
			$this->error(P_Lang('请选择要维护的站点'),$this->ur('index'));
		}
		$rs = $this->model("site")->get_one($siteid);
		if(!$rs){
			$this->error(P_Lang('站点信息不存在'),$this->url("index"));
		}
		$this->session->assign('admin_site_id',$siteid);
		$tip = P_Lang('您正在切换到网站：{sitename}，请稍候…',array('sitename'=>"<span style='color:red;font-weight:bold;'>".$rs['title']."</span>"));
		$this->success($tip,$this->url("index"));
	}

	/**
	 * 获取待处理信息
	**/
	public function pendding_f()
	{
		$list = false;
		//读取未操作的主题
		$rslist = $this->model('list')->pending_info($this->session->val('admin_site_id'));
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
		if($this->config['async']['status']){
			$taskurl = api_url('task','index','',true);
			if($this->config['async']['type']){
				$this->lib('async')->loadtype($this->config['async']['type']);
			}
			$this->lib('async')->start($taskurl);
		}
		//远程检查更新
		if(file_exists($this->dir_root.'data/update.php')){
			include($this->dir_root.'data/update.php');
			$time = 0;
			if(file_exists($this->dir_root.'data/update.time')){
				$time = $this->lib('file')->cat($this->dir_data.'update.time');
			}
			$check = false;
			if($time < $this->time && ($this->time - $uconfig['date'] * 86400) > $time){
				$check = true;
			}
			if($check){
				$this->lib('file')->vim($this->time,$this->dir_data.'update.time');
				$list['update_action'] = true;
			}
		}
		if(!$list){
			$this->error();
		}
		$this->success($list);
	}

	public function pendding_sublist_f()
	{
		$list = false;
		$rslist = $this->model('list')->pending_info($this->session->val('admin_site_id'));
		if($rslist){
			foreach($rslist AS $key=>$value){
				if($value['parent_id']){
					$url = $this->url("list","action","id=".$value["pid"]);
					$list['project_'.$value['pid']] = array("title"=>$value["title"],"total"=>$value["total"],"url"=>$url,'id'=>$value['pid']);
				}
			}
		}
		if(!$list){
			$this->success();
		}
		$this->success($list);
	}
}
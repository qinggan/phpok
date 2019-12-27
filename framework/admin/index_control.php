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
		$this->_index();
		$this->view('index');
	}

	private function _index()
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
		if(!$this->session->val('admin_rs.if_system')){
			foreach($sitelist as $key=>$value){
				$chk_popedom = $this->model('popedom')->site_popedom($value['id'],$this->session->val('admin_id'));
				if(!$chk_popedom){
					unset($sitelist[$key]);
				}
			}
			if(!$sitelist){
				$this->error(P_Lang('没有找到相关权限，请联系管理员'));
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
		foreach($menulist as $key=>$value){
			if(!$value["sublist"] || !is_array($value["sublist"]) || count($value["sublist"]) < 1){
				unset($menulist[$key]);
				continue;
			}
			foreach($value["sublist"] as $k=>$v){
				if($v['appfile'] == 'all' || $v['appfile'] == 'list'){
					unset($value['sublist'][$k]);
					continue;
				}
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
		if($this->session->val('admin_rs.if_system') && $this->session->val('adm_develop')){
			$this->assign('menulist',$menulist);
		}
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
		$iconlist = array();
		if($menulist && !$this->session->val('adm_develop')){
			foreach($menulist as $key=>$value){
				if(!$value['sublist']){
					continue;
				}
				foreach($value['sublist'] as $k=>$v){
					if(!$v['icon'] || $v['appfile'] == 'list' || $v['appfile'] == 'all'){
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
		$this->list_setting($plugin_glist);
		if($this->config['multiple_language']){
			$langlist = $this->model('lang')->get_list();
			if($langlist){
				$language = '简体中文';
				foreach($langlist as $key=>$value){
					if($key == $this->session->val('admin_lang_id')){
						$language = $value;
						break;
					}
				}
				$this->assign('language',$language);
			}
			$this->assign('langlist',$langlist);
		}
		$logo = ($this->site['adm_logo29'] && is_file($this->site['adm_logo29'])) ? $this->site['adm_logo29'] : 'images/admin.svg';
		$this->assign('logo',$logo);
		if($this->site['adm_logo50']){
			$this->assign('logo2',$this->site['adm_logo50']);
		}
	}

	/**
	 * 默认首页
	**/
	public function homepage_f()
	{
		$this->_index();
		//读取统计
		$all = $this->model('list')->status_all($this->session->val('admin_site_id'));
		$this->assign('all_status',$all);
		//读取服务器信息
		$list = $this->_serverInfo();
		if($list && count($list)>10){
			$serverlist = array();
			foreach($list as $key=>$value){
				if($key<10){
					$serverlist[] = $value;
				}else{
					break;
				}
			}
			$this->assign('serverlist',$serverlist);
		}else{
			$this->assign('serverlist',$list);
		}
		//读取快捷链接
		$qlink = $this->model('qlink')->get_all();
		$this->assign('qlink',$qlink);
		$this->view('homepage');
	}

	private function _serverInfo()
	{
		$list = array();
		if(function_exists('phpversion')){
			$list[]=array(P_Lang('PHP版本'),phpversion());
		}
		if(function_exists('zend_version')){
			$list[]=array(P_Lang('Zend引擎版本'),zend_version());
		}
		$list[]=array(P_Lang('MySQL服务端'),$this->db->version());
		$list[]=array(P_Lang('MySQL客户端'),$this->db->version('client'));
		if($this->config['debug']){
			$list[] = array(P_Lang('PHPOK调试'),P_Lang('开启，建议正式运行时关闭'),'color:red;font-weight:bold;',P_Lang('修改_config/global.ini.php，将 debug 设为 false 即可'));
		}
		if($this->config['develop']){
			$list[] = array(P_Lang('PHPOK开发模式'),P_Lang('开启，建议正式运行时关闭'),'color:red;font-weight:bold;',P_Lang('修改_config/global.ini.php，将 develop 设为 false 即可'));
		}
		if(isset($_SERVER['SERVER_SOFTWARE'])){
			$list[]=array(P_Lang('服务器软件'),$_SERVER['SERVER_SOFTWARE']);
		}
		$list[] = array(P_Lang('IP地址'),$this->lib('common')->ip());
		if(isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME'])){
			$list[]=array(P_Lang('域名'),$_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
		}		
		$list[]=array(P_Lang('协议端口'),$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['SERVER_PORT']);
		$list[]=array(P_Lang('服务器时间'),date('Y-m-d H:i:s',$this->time));
		if(function_exists('get_current_user')){
			$list[]=array(P_Lang('当前系统用户'),get_current_user());
		}
		if(function_exists('php_uname')){
			$list[]=array(P_Lang('操作系统'),php_uname('s').' '.php_uname('r').' '.php_uname('v'));
		}
		if(function_exists('php_sapi_name')){
			$list[]=array(P_Lang('PHP运行模式'),php_sapi_name());
		}
		if(strtoupper(ini_get("display_errors")) == 'ON'){
			$list[]=array(P_Lang('报错模式'),P_Lang('开启，正式运行建议关闭'),'color:red;font-weight:bold;',P_Lang('修改 php.ini 文件，将 display_errors 值改为 Off 即可'));
		}else{
			$list[]=array(P_Lang('报错模式'),P_Lang('关闭'),'color:darkblue;');
		}
		$list[]=array(P_Lang('POST提交限制'),ini_get('post_max_size'));
		$list[]=array(P_Lang('上传大小限制'),ini_get('upload_max_filesize'));
		if(ini_get('max_execution_time')){
			$list[]=array(P_Lang('脚本超时时间'),ini_get('max_execution_time').P_Lang('秒'),'color:red;font-weight:bold;');
		}
		
		if (ini_get("safe_mode")==0){
			$list[]=array(P_Lang('安全模式'),P_Lang('关闭'));
		}else{
			$list[]=array(P_Lang('安全模式'),P_Lang('开启'));
		}
		if (function_exists('memory_get_usage')){
			$list[]=array(P_Lang('当前使用内存'),$this->lib('common')->num_format(memory_get_usage()));
		}
		return $list;
	}

	/**
	 * 模式切换
	**/
	public function develop_f()
	{
		$val = $this->get('val','int');
		if($val){
			$this->session->assign('adm_develop',true);
		}else{
			$this->session->unassign('adm_develop');
		}
		$this->success();
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
		//是否显示验证码信息
		$show_vcode_setting = false;
		if($rs['register_status'] || $rs['login_status']){
			$show_vcode_setting = true;
		}
		if(!$show_vcode_setting){
			//读取项目是否有开放评论及发布功能
			$condition = "module>0 AND (post_status=1 || comment_status=1)";
			$plist = $this->model('project')->project_all($this->session->val('admin_site_id'),'id',$condition);
			if($plist){
				$show_vcode_setting = true;
			}
		}
		if($this->config['hide_vcode_setting']){
			$show_vcode_setting = false;
		}
		$this->assign("show_vcode_setting",$show_vcode_setting);
		return true;
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
			foreach($popedom_list as $key=>$value){
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
		return true;
	}

	public function cache_f()
	{
		$this->view('index_cache');
	}

	/**
	 * 清空缓存，包括过时的购物车，及Data目录下的Session文件
	**/
	public function clear_f()
	{
		$type = $this->get('type');
		if($type == 'cart' || $type == 'all'){
			$this->model('cart')->clear_expire_cart();
		}
		if($type == 'file' || $type == 'all'){
			$this->lib('file')->rm($this->dir_cache);
		}
		if($type == 'compile' || $type == 'all'){
			$this->lib('file')->rm($this->dir_data."tpl_www/");
			$this->lib('file')->rm($this->dir_data."tpl_admin/");
		}
		if($type == 'log' || $type == 'all'){
			$this->lib('file')->rm($this->dir_data."log/");
			$list = $this->lib('file')->ls($this->dir_data);
			if($list){
				foreach($list as $key=>$value){
					$tmp = basename($value);
					if(substr($tmp,0,3) == 'log' && substr($tmp,-4) == '.php' && is_file($value)){
						$this->lib('file')->rm($value);
					}
				}
			}
		}
		if($type == 'session' || $type == 'all'){
			$list = $this->lib('file')->ls($this->dir_data.'session/');
			if($list){
				foreach($list as $key=>$value){
					if(filesize($value)>0 && (filemtime($value) + $this->session->timeout()) > $this->time){
						continue;
					}
					$this->lib('file')->rm($value);
				}
			}
		}
		//清理更新后自定义扩展异常Bug
		if($type == 'u_error'){
			$sql = "SELECT * FROM ".$this->db->prefix."fields ORDER BY id ASC";
			$tmplist = $this->db->get_all($sql);
			if(!$tmplist){
				$this->success();
			}
			$mlist = array();
			$ids = array();
			foreach($tmplist as $key=>$value){
				if(is_numeric($value['ftype'])){
					continue;
				}
				$tmpid = $value['ftype'].'-'.$value['identifier'];
				if($mlist[$tmpid]){
					$sql = "DELETE FROM ".$this->db->prefix."fields WHERE id='".$value['id']."'";
					$this->db->query($sql);
					unset($tmplist[$key]);
				}else{
					$mlist[$tmpid] = $value;
					$ids[] = $value['id'];
				}
			}
			if($ids && count($ids)>0){
				$sql = "DELETE FROM ".$this->db->prefix."extc WHERE id NOT IN(".implode(",",$ids).")";
				$this->db->query($sql);
			}
		}
		if($type == 'other' || $type == 'all'){
			$this->cache->clear();
		}
		$this->success();
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
		$this->config('is_ajax',true);
		$list = false;
		//读取未操作的主题
		$rslist = $this->model('list')->pending_info($this->session->val('admin_site_id'));
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!$value['total']){
					continue;
				}
				$url = $this->url("list","action","id=".$value["pid"]);
				$list['project_'.$value['pid']] = array("title"=>$value["title"],"total"=>$value["total"],"url"=>$url,'id'=>$value['pid']);
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
		if(file_exists($this->dir_data.'update.php')){
			include($this->dir_data.'update.php');
			$time = 0;
			if(file_exists($this->dir_data.'update.time')){
				$time = $this->lib('file')->cat($this->dir_data.'update.time');
			}
			$check = false;
			if($uconfig['status'] && $time < $this->time && ($this->time - $uconfig['date'] * 86400) > $time){
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
			foreach($rslist as $key=>$value){
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

	public function info_f()
	{
		$showphp = $this->get('php','int');
		$this->assign('showphp',$showphp);
		if(function_exists('phpinfo')){
			$this->assign('showphpinfo',true);
			if($showphp){
				phpinfo();
				exit;
			}
		}else{
			$this->assign('showphpinfo',false);
		}
		$list = $this->_serverInfo();
		
		$this->assign('list',$list);
		$this->view('index_server_info');
	}


	public function qlink_f()
	{
		$id = $this->get('id');
		if($id){
			$rs = $this->model('qlink')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		$dirlist = array();
		$list = $this->lib('file')->ls($this->dir_app);
		if($list){
			foreach($list as $key=>$value){
				$tmp = basename($value);
				if(is_file($value.'/'.$this->app_id.'.control.php')){
					$dirlist[] = array('id'=>$tmp,'title'=>$tmp);
				}
			}
		}
		$list = $this->lib('file')->ls($this->dir_phpok."admin");
		foreach($list as $key=>$value){
			$tmp = str_replace("_control.php","",strtolower(basename($value)));
			if(strpos($tmp,".func.php") === false){
				$dirlist[] = array("id"=>$tmp,"title"=>basename($value));
			}
		}
		$this->assign("ctrlist",$dirlist);
		$this->view('index_qlink');
	}

	public function funclist_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有配置权限'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定控制器'));
		}
		$ctrlfile = '';
		if(is_file($this->dir_app.$id.'/'.$this->app_id.'.control.php')){
			$ctrlfile = $this->dir_app.$id.'/'.$this->app_id.'.control.php';
		}
		if(!$ctrlfile && is_file($this->dir_phpok.$this->app_id.'/'.$id.'_control.php')){
			$ctrlfile = $this->dir_phpok.$this->app_id.'/'.$id.'_control.php';
		}
		if(!$ctrlfile){
			$this->error(P_Lang('控制器不存在'));
		}
		$list = $this->model('qlink')->func_list($ctrlfile);
		if($list){
			$this->success($list);
		}
		$this->success();
	}

	public function qlink_save_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有配置权限'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$id = 'qlink-'.$this->time.rand(0,999);
		}
		$data = array('id'=>$id);
		$data['title'] = $this->get('title');
		$data['link'] = $this->get('link');
		$data['ico'] = $this->get('ico');
		$this->model('qlink')->save($data);
		$this->success();
	}

	public function qlink_delete_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有删除权限'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('qlink')->delete($id);
		$this->success();
	}
}
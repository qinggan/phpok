<?php
/**
 * 后台首页控制台
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
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
		//检测是否需要打开自动更新附件功能
		if(file_exists($this->dir_data.'first.lock')){
			$this->assign('first_login',true);
			$this->lib('file')->rm($this->dir_data.'first.lock');
		}
		$this->view('index');
	}

	private function _index()
	{
		if(!$this->license_code){
			$this->license = "MIT";
		}
		$license = strtoupper($this->license);
		$code = P_Lang('开源授权');
		if($license == "PBIZ" && $this->license_code && $this->license_name){
			$code = P_Lang('个人（{license}）商业授权',array('license'=>$this->license_name));
		}elseif($license == "CBIZ" && $this->license_code && $this->license_name){
			$code = P_Lang('企业（{license}）商业授权',array('license'=>$this->license_name));
		}
		$license_site = $this->license_site;
		if(substr($license_site,0,1) == '.'){
			$license_site = substr($license_site,1);
		}
		$this->assign('license_domain',$license_site);
		$this->assign("license_site",$code);
		$this->assign('license_code',$license);
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
		$site_rs = $this->model('site')->get_one($this->session->val('admin_site_id'));
		foreach($sitelist as $key=>$value){
			if($value['id'] == $this->session->val('admin_site_id')){
				break;
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
		$popedom = $this->session->val('admin_rs.if_system') ? array("all") : $this->session->val('admin_popedom');
		if(!$popedom){
			$popedom = array();
		}
		$this->assign('site_rs',$site_rs);
		$condition = '';
		if(!$site_rs['biz_status']){
			$biz_ctrl = array('order','options','payment','currency','express','freight');
			$string = implode("','",$biz_ctrl);
			$condition = "appfile NOT IN('".$string."')";
		}
		$menulist = $this->model('sysmenu')->get_all($this->session->val('admin_site_id'),1,$condition);
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
				if(!in_array($v['appfile'],$ftmp) && !$this->session->val('admin_rs.if_system') && $popedom_m[$v['id']]){
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
		$adminsys = $this->model('config')->get_all();
		if($adminsys && $adminsys['admin_homepage_setting']){
			$setting = explode(",",$adminsys['admin_homepage_setting']);
			if(in_array('stat',$setting)){
				$this->addjs('js/echarts.min.js');
				$tmpfile = $this->dir_data.'json/index-report.json';
				$tmpdata = array('type'=>'year','ids'=>'title');
				if(file_exists($tmpfile)){
					$tmp = $this->lib('file')->cat($tmpfile);
					$tmpdata = $this->lib('json')->decode($tmp);
				}
				$tmpdata['ids'] = explode(",",$tmpdata['ids']);
				$this->assign('report',$tmpdata);
			}
			//读取服务器信息
			if(in_array('env',$setting)){
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
			}

			if(in_array('content',$setting)){
				$all = $this->model('list')->status_all($this->session->val('admin_site_id'));
				$this->assign('all_status',$all);
			}

			if(in_array('safecheck',$setting)){
				$this->assign('safecheck',true);
			}
		}
		//读取快捷链接
		$qlink = $this->model('qlink')->get_all();
		$this->assign('qlink',$qlink);
		$this->view('homepage');
	}

	public function clear_ignore_f()
	{
		$this->lib('file')->rm($this->dir_data."ignore.txt");
		$this->success();
	}

	public function ignore_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error('未指定要忽略的文件');
		}
		$info = $this->lib('file')->cat($this->dir_data.'ignore.txt');
		$ignore = $info ? explode("\n",$info) : array();
		if(!in_array($id,$ignore)){
			$ignore[] = $id;
		}
		$content = implode("\n",$ignore);
		$this->lib('file')->vi($content,$this->dir_data.'ignore.txt');
		$this->success();
	}

	public function download_table_f()
	{
		$url = 'https'.'://cdn.phpok.com/tables/'.$this->version.'.txt';
		$content = $this->lib('curl')->get_content($url);
		$this->lib('file')->vi($content,$this->dir_data.'table.php');
		$this->success();
	}

	public function getlist_f()
	{
		$this->config('is_ajax',true);
		$folder = $this->get('folder');
		$dir = $this->dir_root;
		if($folder && substr($folder,-1) == '/'){
			$folder = substr($folder,0,-1);
		}
		if($folder){
			$dir .= $folder;
		}
		$list = $this->lib('file')->ls($dir);
		if(!$list){
			$this->success(array("total"=>0));
		}
		$total = count($list);
		$dirlist = array();
		$rslist = array();
		$length = strlen($this->dir_root);
		$info = $this->lib('file')->cat($this->dir_data.'ignore.txt');
		$ignore = $info ? explode("\n",$info) : array();
		$tables = $this->_tables();
		foreach($list as $key=>$value){
			if(is_dir($value)){
				$dirlist[] = substr($value,$length);
			}
			if(is_file($value)){
				$tmp = $this->file_safe_checking($value,$ignore,$tables);
				if($tmp){
					$rslist[] = $tmp;
				}
			}
		}
		$info = array("total"=>$total);
		if($dirlist && count($dirlist)>0){
			$info['dirlist'] = $dirlist;
		}
		if($rslist && count($rslist)>0){
			usort($rslist,array($this,"_sort"));
			$info['rslist'] = $rslist;
		}
		$this->success($info);
	}

	private function _tables()
	{
		$file = $this->dir_data.'table.php';
		if(!file_exists($file)){
			return false;
		}
		$list = file($file);
		if(!$list){
			return false;
		}
		unset($list[0]);
		if(!$list){
			return false;
		}
		$ids = array();
		foreach($list as $key=>$value){
			$tmp = explode("|",$value);
			$name = trim($tmp[1]);
			if($name == 'Hash'){
				continue;
			}
			$ids[] = $name;
		}
		return $ids;
	}

	private function _sort($a,$b)
	{
		if($a['filesize'] == $b['filesize']){
			return 0;
		}
		return ($a['filesize'] > $b['filesize']) ? -1 : 1;
	}

	/**
	 * 文件安全检测
	 * 检测深度方法：
	 *		1、码表对照，一致的文件直接忽略过
	 *		2、空字符文件返回删除提示
	 *		3、文件头与后缀不一致检测
	 *		4、图片超过1M返回报错
	 *		5、脚本文件超过200K返回报错
	 *		6、含敏感字符的脚本谁的返回报错
	 *		7、忽略所有 txt，md，tiff，mp4，mpeg，mp3，wav，wmv，mpg 等附件
	**/
	private function file_safe_checking($file,$ignore=array(),$tables=array())
	{
		$t = 'passthru,exec,system,putenv,chroot,chgrp,chown,popen,proc_open,ini_alter,ini_restore,dl,openlog,syslog,readlink,symlink,popepassthru,pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_getpriority,pcntl_setpriority,imap_open,apache_setenv';
		$codes = explode(",",$t);
		$codes[] = "pack";
		$codes[] = "dechex";
		$codes[] = "hexdec";
		$codes[] = "chr";
		$codes[] = "str_rot13";
		$codes[] = "eval";
		$codes = array_unique($codes);
		$rs = $this->file2array($file);
		if(!$rs){
			return false;
		}

		if($rs['is_txt']){
			return false;
		}
		
		if($tables && in_array($rs['hash'],$tables)){
			return false;
		}
		
		if($ignore && in_array($rs['hash'],$ignore)){
			return false;
		}
		if($rs['ext'] == $rs['filename'] || $rs['ext'] == '未知'){
			return false;
		}
		//可以放脚本的文件
		$exelist = array('php','asp','aspx','jsp','py','html','htm','js');
		
		//忽略的文件后缀
		$out_extinfo  = 'doc,docx,xls,xlsx,pdf,ppt,pptx,rtf,txt,md';
		$out_extinfo .= ',mp4,mp3,rm,wav,wmv,mpg,mpeg';
		$out_extinfo .= ',tiff,json,config,conf,lock,ini,htaccess,zh_cn,tpl';
		$out_extinfo .= ',time,sql,ttf,zip,rar,woff,eot,woff2,po,mo,map,otf';
		$out_extinfo .= ',phar,xdb,db,yml,pem,psd,js,jsx,fon,log,jpg,png,gif,jpe,jpeg,webp,bmp';
		$out_extinfo .= ',tiff,svg,js,css,xml,scss,ico,css';
		$outlist = explode(",",$out_extinfo);
		$outlist = array_unique($outlist);
		if($rs['ext'] && in_array($rs['ext'],$outlist)){
			return false;
		}

		//检测附件目录禁止脚本
		//脚本类型检测，超过100K，提示编辑
		if(substr($rs['folder'],0,4) == 'res/'){
			$rs['error'] = ' res 目录禁止脚本文件存在，请检查';
			$rs['act'] = "edit";
			$rs['status'] = false;
			return $rs;
		}
		if($rs['filesize']<100){
			$rs['error'] = '脚本文件小于100字节，请人工检查是否异常';
			$rs['act'] = "edit";
			$rs['status'] = false;
			return $rs;
		}
		if($rs['filesize']>=(1024*100)){
			$rs['error'] = '脚本文件超过100K，请人工检查是否异常';
			$rs['act'] = "edit";
			$rs['status'] = false;
			return $rs;
		}
		$tmp = file_get_contents($file);
		$tmp = str_replace(array(" ","\t","\n","\r"),"",$tmp);
		$tmp = strtolower($tmp);
		foreach($codes as $key=>$value){
			if(strpos($tmp,$value.'(') !== true){
				continue;
			}
			preg_match_all('/('.$value.'\()/isU',$tmp,$match_1);
			preg_match_all('/([>|:|\_|\$]'.$value.'\()/isU',$tmp,$match_2);
			if($match_1 && $match_2 && $match_1[1] && $match_2 && count($match_1[1]) != count($match_2[2])){
				$rs['error'] = '脚本文件含有敏感变量 '.$value.'，请人工检查';
				$rs['act'] = "edit";
				$rs['status'] = false;
				$isbreak = true;
				break;
			}
		}
		if($isbreak){
			return $rs;
		}
		$codelist = array();
		$codelist[] = '$_=';
		$codelist[] = '$_[';
		$codelist[] = '$__}';
		$codelist[] = '->"_"';
		foreach($codelist as $key=>$value){
			if(strpos($tmp,$value) !== false){
				$rs['error'] = '脚本文件含有敏感 '.$value.' 特征码，请人工检查';
				$rs['act'] = "edit";
				$rs['status'] = false;
				$isbreak = true;
				break;
			}
		}
		if($isbreak){
			return $rs;
		}
		// 判断是否有类似 \x63\x72\x65\x61\x74 变量名
		preg_match('/(\\\x[0-9a-z]+)/isU',$tmp,$match);
		if($match && $match[1]){
			$rs['error'] = '脚本文件含有敏感十六进 '.$match[1].' 信息，请人工检查';
			$rs['act'] = "edit";
			$rs['status'] = false;
			return $rs;
		}
		// 判断是否有非除
		preg_match('/\$([^0-9a-z\_\'\/\)\"]+)(\)|;|=|\s|\]|\[|\-)/isU',$tmp,$match);
		if($match && $match[1]){
			$rs['error'] = '脚本文件含有怪异变量名 '.rawurlencode($match[1]).'，请人工检查';
			$rs['act'] = "edit";
			$rs['status'] = false;
			return $rs;
		}
		return false;
	}

	private function file2array($file)
	{
		$f2 = substr($file,strlen($this->dir_root));
		$filesize = filesize($file);
		if($filesize>=1024*300){
			$content = file_get_contents($file,null,null,0,300);
			$hash = md5($f2.'-'.$content);
		}else{
			$hash = md5_file($file);
		}
		$mdate = filemtime($file);
		$rs = array();
		$rs['name'] = basename($file);
		$rs['filesize'] = $filesize;
		$rs['filename'] = $f2;
		$rs['hash'] = $hash;
		$rs['md5'] = md5($f2); //文件路径MD5
		$rs['cdate'] = filectime($file);
		$rs['mdate'] = $mdate;
		$rs['adate'] = fileatime($file);
		$rs['size'] = $this->lib('common')->num_format($rs['filesize'],2,false);
		$rs['ext'] = $this->file_ext($file);
		$rs['folder'] = $f2 ? substr($f2,0,-(strlen($rs['name']))) : '';
		$rs['mdate_format'] = date("Y-m-d H:i:s",$rs['mdate']);
		$rs['cdate_format'] = date("Y-m-d H:i:s",$rs['cdate']);
		$rs['adate_format'] = date("Y-m-d H:i:s",$rs['adate']);
		$tmp = file_get_contents($file,null,null,0,26);
		if($tmp == "<?php die('forbidden'); ?>" || $tmp == "<?php exit('--------- STAR"){
			$rs['is_txt'] = true;
		}else{
			$rs['is_txt'] = false;
		}
		return $rs;
	}

	/**
	 * 取得文件类型
	 * @参数 $file 文件（含路径）
	**/
	private function file_ext($file)
	{
		if(function_exists('pathinfo')){
			$t = pathinfo($file,PATHINFO_EXTENSION);
			if($t){
				return $t;
			}
		}
		$file = basename($file);
		$e = explode(".",$file);
		if(!$e[1]){
			return '未知';
		}
		$len = count($e);
		$ext = $e[($len-1)];
		return $ext;
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
			$groups = $this->model('project')->group();
			$tmp = array();
			foreach($groups as $key=>$value){
				$tmp[$key] = array("title"=>$value,"url"=>$this->url("index","group","id=".$key),"rslist"=>array());
			}
			$groups = $tmp;
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$value['url'] = $this->url('list','action','id='.$value['id']);
				if($value['admin_group'] && $groups && $groups[$value['admin_group']]){
					$groups[$value['admin_group']]["rslist"][] = $value;
					$tmplist[$value['admin_group']] = $groups[$value['admin_group']];
				}else{
					$tmplist[$key] = $value;
				}
			}
			$rslist = $tmplist;
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
		$is_opcache = function_exists('opcache_reset');
		$this->assign('is_opcache',$is_opcache);
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
		if($type == 'log'){
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
		if($type == 'opcache' || $type == 'all'){
			if(function_exists('opcache_reset')){
				opcache_reset();
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
		if($this->session->val('admin_login_time') && $this->session->val('admin_long_time')){
			$etime = $this->time - $this->session->val('admin_login_time');
			if($etime > ($this->session->val('admin_long_time') * 60)){
				$this->session->unassign('admin_id');
				$this->session->unassign('admin_account');
				$this->session->unassign('admin_rs');
				$this->session->unassign('adm_develop');
				$this->session->unassign('admin_login_time');
				$this->session->unassign('admin_long_time');
				$this->error('系统超时，强制退出');
			}
		}
		$this->config('is_ajax',true);
		$list = array();
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
		//读取未审核的用户信息
		$condition = "u.status=0";
		$user_total = $this->model('user')->get_count($condition);
		if($user_total > 0){
			$url = $this->url("user","","status=3");
			$list['ctrl_user'] = array("title"=>P_Lang('用户列表'),"total"=>$user_total,"url"=>$url,'id'=>'user');
		}
		//读取未审核的回复信息
		$condition = "status=0";
		$reply_total = $this->model('reply')->get_total($condition);
		if($reply_total>0){
			$url = $this->url("reply","","status=3");
			$list['ctrl_reply'] = array("title"=>P_Lang('评论管理'),"total"=>$reply_total,"url"=>$url,'id'=>'reply');
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
		$list = array();
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
				$showphpcode = 'phpinfo';
				$showphpcode();
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
			$rand_string = $this->lib('common')->str_rand(10);
			$id = 'qlink-'.$this->time.'-'.$rand_string;
		}
		$data = array('id'=>$id);
		$data['title'] = $this->get('title');
		$data['link'] = $this->get('link');
		$data['ico'] = $this->get('ico');
		$data['islink'] = $this->get('islink');
		$data['intitle'] = $this->get('intitle');
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

	public function copyright_f()
	{
		$type = $this->get('type');
		if($type == 'LGPL' || $type == 'MIT'){
			$this->error(P_Lang('开源授权不需要修改'));
		}
		$company = $this->get('company');
		if(!$company){
			$this->error('授权企业不能为空');
		}
		$domain = $this->get('domain');
		if(!$domain){
			$this->error(P_Lang('授权域名不能为空'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->error(P_Lang('授权代码不能为空'));
		}
		$date = $this->get('date');
		if(!$date){
			$date = date("Y-m-d",$this->time);
		}
		$content = '<?php'."\n";
		$content.= "/*****************************************************************************************\n";
		$content.= "	文件： license.php\n";
		$content.= "	说明： PHPOK-VIP 许可证书\n";
		$content.= "	版本： PHPOK ".VERSION."\n";
		$content.= "	作者： phpok.com<admin@phpok.com>\n";
		$content.= "	更新： ".date("Y-m-d H:i",$this->time)."\n";
		$content.= "*****************************************************************************************/\n";
		$content.= 'if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}'."\n";
		//授权类型
		$content.= 'define("LICENSE","'.$type.'");'."\n";
		//授权时间
		$content.= 'define("LICENSE_DATE","'.$date.'");'."\n";
		//授权域名
		$content.= 'define("LICENSE_SITE","'.$domain.'");'."\n";
		//授权码
		$content.= 'define("LICENSE_CODE","'.strtoupper($code).'");'."\n";
		//授权人信息
		$content.= 'define("LICENSE_NAME","'.$company.'");'."\n";
		//是否显示版权
		$content.= 'define("LICENSE_POWERED",false);'."\n";
		$this->lib("file")->vim($content,$this->dir_root.'license.php');
		$this->success();
	}
}
<?php
/**
 * 功能应用管理工具
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2018年06月05日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class appsys_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('appsys');
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 管理整个平台功能应用器
	**/
	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('您没有查看权限'));
		}
		$rslist = $this->model('appsys')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('appsys_index');
	}

	public function setting_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您没有配置环境权限'));
		}
		$rs = $this->model('appsys')->server();
		if($rs && is_array($rs)){
			$this->assign('rs',$rs);
		}
		$this->view('appsys_setting');
	}

	public function setting_save_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您没有配置环境权限'));
		}
		$data = array();
		$data['server'] = $this->get('server');
		$data['ip'] = $this->get('ip');
		$data['folder'] = $this->get('folder');
		$data['https'] = $this->get('https','int');
		$this->model('appsys')->server($data);
		$this->success();
	}

	public function remote_f()
	{
		if(!$this->popedom['remote']){
			$this->error(P_Lang('您没有更新远程数据权限'));
		}
		$server = $this->model('appsys')->server();
		if(!$server || !$server['server']){
			$this->error(P_Lang('未配置好远程服务器环境，请更新配置环境'));
		}
		$url  = $server['https'] ? 'https://' : 'http://';
		$url .= $server['server'];
		if(substr($url,-1) == '/'){
			$url = substr($url,0,-1);
		}
		$folder = !$server['folder'] ? '/' : (substr($server['folder'],0,1) != '/' ? '/'.$server['folder'] : $server['folder']);
		$url .= $folder;
		$this->lib('curl')->timeout(30);
		if($server['ip']){
			$this->lib('curl')->host_ip($server['ip']);
		}
		$url .= "?code=".md5(strtoupper(LICENSE_CODE)).'&domain='.rawurlencode(LICENSE_SITE);
		$data = $this->lib('curl')->get_json($url);
		if(!$data){
			$this->error(P_Lang('远程更新数据失败'));
		}
		if(!$data['status']){
			$tip = $data['info'] ? $data['info'] : ($data['error'] ? $data['error'] : P_Lang('获取数据失败'));
			$this->error($tip);
		}
		$this->lib('xml')->save($data['info'],$this->dir_data.'xml/appall.xml');
		$this->success();
	}

	public function import_f()
	{
		$array = array("identifier"=>'zipfile',"form_type"=>'upload');
		$array['upload_type'] = 'update';
		$this->lib('form')->cssjs($array);
		$upload = $this->lib('form')->format($array);
		$this->assign('upload_html',$upload);
		$this->view('appsys_upload');
	}

	/**
	 * 解压应用
	**/
	public function unzip_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$filename = $this->get('filename');
			if(!$filename){
				$this->error(P_Lang('附件不存在'));
			}
		}else{
			$rs = $this->model('res')->get_one($id);
			if(!$rs){
				$this->error(P_Lang('附件不存在'));
			}
			$filename = $rs['filename'];
		}
		$tmp = strtolower(substr($filename,-4));
		if($tmp != '.zip'){
			$this->error(P_Lang('非ZIP文件不支持在线解压'));
		}
		if(!file_exists($this->dir_root.$filename)){
			$this->error(P_Lang('文件不存在'));
		}
		$info = $this->lib('phpzip')->zip_info($this->dir_root.$filename);
		$info = current($info);
		if(!$info['filename']){
			$this->error(P_Lang('应用有异常'));
		}
		$info = explode('/',$info['filename']);
		if(!$info[0]){
			$this->error(P_Lang('应用有异常'));
		}
		if(file_exists($this->dir_app.$info[0])){
			$this->error(P_Lang('应用已存在，不允许重复解压'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$filename,$this->dir_app);
		$config = $this->model('appsys')->get_one($info[0]);
		$config['installed'] = false;
		$this->lib('xml')->save($config,$this->dir_app.$info[0].'/config.xml');
		$this->success();
	}

	public function backup_list_f()
	{
		$rslist = $this->model('appsys')->backup_all(false);
		if($rslist){
			$this->assign('rslist',$rslist);
		}
		$this->view('appsys_backuplist');
	}

	/**
	 * 备份应用到 zip 目录
	**/
	public function backup_f()
	{
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定项目'),$this->url('appsys'));
		}
		if(!file_exists($this->dir_app.$id)){
			$this->error(P_Lang('应用不存在'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('未找到配置信息'),$this->url('appsys'));
		}
		$zipfile = $this->dir_data.'zip/'.$id.'-'.date("Ymd",$this->time).'.zip';
		if(is_file($zipfile)){
			$this->lib('file')->rm($zipfile);
		}
		$this->lib('phpzip')->set_root($this->dir_app);
		$this->lib('phpzip')->zip($this->dir_app.$id,$zipfile);
		$this->success();
	}

	/**
	 * 删除备份文件
	**/
	public function backup_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除应用的权限'),$this->url('appsys'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定要删除的备份文件'));
		}
		if(is_file($this->dir_data.'zip/'.$id)){
			$this->lib('file')->rm($this->dir_data.'zip/'.$id);
		}
		$this->success();
	}

	/**
	 * 卸载应用，不删除操作
	**/
	public function uninstall_f()
	{
		if(!$this->popedom['uninstall']){
			$this->error(P_Lang('您没有卸载应用的权限'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定项目'),$this->url('appsys'));
		}
		if(!file_exists($this->dir_app.$id)){
			$this->error(P_Lang('应用不存在'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if($rs && $rs['uninstall'] && is_file($this->dir_app.$id.'/'.$rs['uninstall'])){
			include_once($this->dir_app.$id.'/'.$rs['uninstall']);
		}
		$this->model('appsys')->uninstall($id);
		$this->success(P_Lang('应用卸载成功'),$this->url('appsys'));
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有删除应用的权限'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定项目'),$this->url('appsys'));
		}
		if(!file_exists($this->dir_app.$id)){
			$this->error(P_Lang('应用不存在'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(isset($rs['installed']) && $rs['installed']){
			$this->error(P_Lang('未卸载的应用不能删除'));
		}
		$baklist = $this->model('appsys')->backup_all(true);
		if(!$baklist[$id]){
			$this->error(P_Lang('没有找到备份文件，不能删除'));
		}
		$this->lib('file')->rm($this->dir_app.$id);
		$this->success();
	}

	/**
	 * 导出应用
	 * @参数 $id 应用ID
	**/
	public function export_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定备份应用ID'),$this->url('appsys'));
		}
		$rs = $this->model('appsys')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('未找到配置信息'),$this->url('appsys'));
		}
		$zipfile = $this->dir_cache.$id.'-'.$this->time.'-'.rand(100,999).'.zip';
		if(is_file($zipfile)){
			$this->lib('file')->rm($zipfile);
		}
		$this->lib('phpzip')->set_root($this->dir_app);
		$this->lib('phpzip')->zip($this->dir_app.$id,$zipfile);
		$this->lib('file')->download($zipfile,$rs['title'].'.zip');
	}

	/**
	 * 运行安装
	**/
	public function install_f()
	{
		if(!$this->popedom['install']){
			$this->error(P_Lang('您没有安装应用的权限'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定项目'),$this->url('appsys'));
		}
		if(is_dir($this->dir_app.$id)){
			//检查Config文件
			if(is_file($this->dir_app.$id.'/config.xml')){
				$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
				if($info && $info['install'] && is_file($this->dir_app.$id.'/'.$info['install'])){
					include_once($this->dir_app.$id.'/'.$info['install']);
				}
			}
			$this->model('appsys')->install($id);
			$this->success(P_Lang('安装成功'),$this->url('appsys'));
		}
		if(!is_file($this->dir_data.'zip/'.$id.'.zip')){
			$server = $this->model('appsys')->server();
			if(!$server || !$server['server']){
				$this->error(P_Lang('未配置好远程服务器环境，请更新配置环境'),$this->url('appsys'));
			}
			$url  = $server['https'] ? 'https://' : 'http://';
			$url .= $server['server'];
			if(substr($url,-1) == '/'){
				$url = substr($url,0,-1);
			}
			$folder = !$server['folder'] ? '/' : (substr($server['folder'],0,1) != '/' ? '/'.$server['folder'] : $server['folder']);
			$url .= $folder;
			$this->lib('curl')->timeout(30);
			if($server['ip']){
				$this->lib('curl')->host_ip($server['ip']);
			}
			$info = $this->lib('curl')->get_json($url.'?id='.$id);
			if(!$info['status']){
				$info = $info['info'] ? $info['info'] : ($info['error'] ? $info['error'] : '安装失败');
				$this->error(P_Lang($info),$this->url('appsys'));
			}
			$content = base64_decode($info['info']);
			$this->lib('file')->save_pic($content,$this->dir_data.'zip/'.$id.'.zip');
		}
		if(!is_file($this->dir_data.'zip/'.$id.'.zip')){
			$this->error(P_Lang('项目不存在'));
		}
		//解压到目标文件
		$ziplist = $this->lib('phpzip')->zip_info($this->dir_data.'zip/'.$id.'.zip');
		if(!$ziplist){
			$this->error(P_Lang('压缩包数据有错误'),$this->url('appsys'));
		}
		if(count($ziplist)==1){
			if(!file_exists($this->dir_app.$id)){
				$this->lib('file')->make($this->dir_app.$id);
			}
			$this->lib('phpzip')->unzip($this->dir_data.'zip/'.$id.'.zip',$this->dir_app.$id);
		}else{
			$this->lib('phpzip')->unzip($this->dir_data.'zip/'.$id.'.zip',$this->dir_app);
		}
		//检查Config文件
		if(is_file($this->dir_app.$id.'/config.xml')){
			$info = $this->lib('xml')->read($this->dir_app.$id.'/config.xml',true);
			if($info && $info['install'] && is_file($this->dir_app.$id.'/'.$info['install'])){
				include_once($this->dir_app.$id.'/'.$info['install']);
			}
		}
		$this->model('appsys')->install($id);
		$this->success(P_Lang('安装成功'),$this->url('appsys'));
	}

	public function add_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('您没有创建应用权限'));
		}
		$this->view('appsys_add');
	}

	public function create_f()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('应用名称不能为空'));
		}
		$identifier = $this->get('identifier','system');
		if(!$identifier){
			$this->error(P_Lang('标识为空或标识不符合规定'));
		}
		$elist = $this->_get_applist();
		if($elist && in_array($identifier,$elist)){
			$this->error(P_Lang('标识已存在'));
		}
		$is_admin = $this->get('is_admin','checkbox');
		$is_api = $this->get('is_api','checkbox');
		$is_www = $this->get('is_www','checkbox');
		if(!$is_admin && !$is_api && !$is_www){
			$this->error(P_Lang('至少选择一个执行范围'));
		}
		$install = $this->get('install');
		$uninstall = $this->get('uninstall');
		$note = $this->get('note');
		$author = $this->get('author');
		$this->lib('file')->make($this->dir_app.$identifier,'dir');
		if(!file_exists($this->dir_app.$identifier)){
			$this->error(P_Lang('目录不存在'));
		}
		//创建模板目录
		$this->lib('file')->make($this->dir_app.$identifier.'/tpl','dir');
		//写入文件
		$data = array('title'=>$title);
		$data['status'] = array('admin'=>$is_admin,'www'=>$is_www,'api'=>$is_api);
		if($install){
			if(substr($install,-4) != '.php'){
				$install .= ".php";
			}
			$data['install'] = $install;
		}
		if($uninstall){
			if(substr($uninstall,-4) != '.php'){
				$uninstall .= ".php";
			}
			$data['uninstall'] = $uninstall;
		}
		$data['installed'] = false;
		$this->lib('xml')->save($data,$this->dir_app.$identifier.'/config.xml');
		if(!is_file($this->dir_app.$identifier.'/config.xml')){
			$this->error(P_Lang('配置文件写入失败'));
		}
		//安装文件
		if($install){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('安装文件'),$note,$author);
			$content .= $this->_php_safe();
			$content .= $this->_php_install($title,$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/'.$install);
			$content = '-- 安装数据库文件，直接在这里写SQL';
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/install.sql');
		}
		//卸载文件
		if($uninstall){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('卸载文件'),$note,$author);
			$content .= $this->_php_safe();
			$content .= $this->_php_uninstall($identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/'.$uninstall);
			$content = '-- 卸载数据库文件，直接在这里写SQL';
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/uninstall.sql');
		}

		//创建控制器
		if($is_admin){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('后台管理'),$note,$author);
			$content .= $this->_php_namespace($identifier,'control');
			$content .= $this->_php_safe();
			$content .= $this->_php_control('admin',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/admin.control.php');
			$content = "<!-- include tpl=head_lay nopadding=true -->\n//\n<!-- include tpl=foot_lay is_open=true -->";
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/tpl/admin_index.html');
			//创建JS
			$content  = $this->_php_notes(P_Lang('后面页面脚本'),$note,$author);
			$content .= $this->_js_config('admin',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/admin.js');
		}
		if($is_www){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('网站前台'),$note,$author);
			$content .= $this->_php_namespace($identifier,'control');
			$content .= $this->_php_safe();
			$content .= $this->_php_control('www',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/www.control.php');
			$content = "<!-- include tpl=head -->\n//\n<!-- include tpl=foot -->";
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/tpl/www_index.html');
			//创建JS
			$content  = $this->_php_notes(P_Lang('前台页面脚本'),$note,$author);
			$content .= $this->_js_config('www',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/www.js');
		}
		if($is_api){
			$content  = $this->_php_head();
			$content .= $this->_php_notes(P_Lang('接口应用'),$note,$author);
			$content .= $this->_php_namespace($identifier,'control');
			$content .= $this->_php_safe();
			$content .= $this->_php_control('api',$identifier);
			$this->lib('file')->vim($content,$this->dir_app.$identifier.'/api.control.php');
		}
		//公共Model
		$content  = $this->_php_head();
		$content .= $this->_php_notes(P_Lang('模型内容信息'),$note,$author);
		$content .= $this->_php_namespace($identifier,'model');
		$content .= $this->_php_safe();
		$content .= $this->_php_model_base();
		$this->lib('file')->vim($content,$this->dir_app.$identifier.'/model.php');
		//创建公共页global.func.php
		$content  = $this->_php_head();
		$content .= $this->_php_notes(P_Lang('公共方法'),$note,$author);
		$content .= $this->_php_safe();
		$this->lib('file')->vim($content,$this->dir_app.$identifier.'/global.func.php');
		//创建节点接入文件，此文件用于数据的接入
		$content  = $this->_php_head();
		$content .= $this->_php_notes(P_Lang('接入节点'),$note,$author);
		$content .= $this->_php_namespace_nodes($identifier);
		$content .= $this->_php_safe();
		$content .= $this->_php_nodes($identifier);
		$this->lib('file')->vim($content,$this->dir_app.$identifier.'/nodes.php');
		$this->success();
	}

	private function _php_notes($title,$note='',$author='')
	{
		if(!$author){
			$author = 'phpok.com <admin@phpok.com>';
		}
		if($note){
			$title .= '_'.$note;
		}
		$info  = '/**'."\n";
		$info .= ' * '.$title."\n";
		$info .= ' * @作者 '.$author."\n";
		$info .= ' * @版权 深圳市锟铻科技有限公司'."\n";
		$info .= ' * @主页 http://www.phpok.com'."\n";
		$info .= ' * @版本 5.x'."\n";
		$info .= ' * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License'."\n";
		$info .= ' * @时间 '.date("Y年m月d日 H时i分",$this->time)."\n";
		$info .= '**/'."\n";
		return $info;
	}

	private function _php_namespace($identifier,$type='control')
	{
		$info = 'namespace phpok\\\app\\\\'.$type.'\\\\'.$identifier.';'."\n";
		return $info;
	}

	private function _php_namespace_nodes($identifier)
	{
		$info = 'namespace phpok\\\app\\\\'.$identifier.';'."\n";
		return $info;
	}

	private function _php_safe()
	{
		$info  = '/**'."\n";
		$info .= ' * 安全限制，防止直接访问'."\n";
		$info .= '**/'."\n";
		$info .= 'if(!defined("PHPOK_SET")){'."\n";
		$info .= '	exit("<h1>Access Denied</h1>");'."\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _php_install($title,$identifier)
	{
		$info  = '//phpok_loadsql($this->db,$this->dir_app.\''.$identifier.'/install.sql\',true);'."\n";
		$info .= '//增加导航菜单'."\n";
		$info .= '//$menu = array(\'parent_id\'=>5,\'title\'=>\''.$title.'\',\'status\'=>1);'."\n";
		$info .= '//$menu[\'appfile\'] = \''.$identifier.'\';'."\n";
		$info .= '//$menu[\'taxis\'] = 255;'."\n";
		$info .= '//$menu[\'site_id\'] = 0;'."\n";
		$info .= '//$menu[\'icon\'] = \'newtab\';'."\n";
		$info .= '//$insert_id = $this->model(\'sysmenu\')->save($menu);'."\n";
		$info .= '//if($insert_id){'."\n";
		$info .= '//	$tmparray = array(\'gid\'=>$insert_id,\'title\'=>\'查看\',\'identifier\'=>\'list\',\'taxis\'=>10);'."\n";
		$info .= '//	$this->model(\'popedom\')->save($tmparray);'."\n";
		$info .= '//	$tmparray = array(\'gid\'=>$insert_id,\'title\'=>\'删除\',\'identifier\'=>\'delete\',\'taxis\'=>10);'."\n";
		$info .= '//	$this->model(\'popedom\')->save($tmparray);'."\n";
		$info .= '//}'."\n";
		return $info;
	}

	private function _php_uninstall($identifier)
	{
		$info  = '//phpok_loadsql($this->db,$this->dir_app.\''.$identifier.'/uninstall.sql\',true);'."\n";
		$info .= '//$sql = "SELECT * FROM ".$this->db->prefix."sysmenu WHERE appfile=\''.$identifier.'\'";'."\n";
		$info .= '//$rs = $this->db->get_one($sql);'."\n";
		$info .= '//if($rs){'."\n";
		$info .= '//	$sql = "DELETE FROM ".$this->db->prefix."popedom WHERE gid=\'".$rs[\'id\']."\'";'."\n";
		$info .= '//	$this->db->query($sql);'."\n";
		$info .= '//	$sql = "DELETE FROM ".$this->db->prefix."sysmenu WHERE id=\'".$rs[\'id\']."\'";'."\n";
		$info .= '//	$this->db->query($sql);'."\n";
		$info .= '//}'."\n";
		return $info;
	}

	private function _php_control($type='admin',$identifier='')
	{
		$info  = 'class '.$type.'_control extends \\\phpok_control'."\n";
		$info .= '{'."\n";
		if($type == 'admin'){
			$info .= '	private $popedom;'."\n";
		}
		$info .= '	public function __construct()'."\n";
		$info .= '	{'."\n";
		$info .= '		parent::control();'."\n";
		if($type == 'admin'){
			$info .= '		$this->popedom = appfile_popedom(\''.$identifier.'\');'."\n";
			$info .= '		$this->assign("popedom",$this->popedom);'."\n";
		}
		$info .= '	}'."\n\n";
		$info .= '	public function index_f()'."\n";
		$info .= '	{'."\n";
		if($type == 'api'){
			$info .= '		//$info = "";'."\n";
			$info .= '		//$this->error($info);'."\n";
			$info .= '		$this->success();'."\n";
		}else{
			$info .= '		$this->display(\''.$type.'_index\');'."\n";
		}
		$info .= '	}'."\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _php_nodes($identifier='')
	{
		$info  = 'class nodes_phpok extends \\\_init_auto'."\n";
		$info .= '{'."\n";
		$info .= '	public function __construct()'."\n";
		$info .= '	{'."\n";
		$info .= '		parent::__construct();'."\n";
		$info .= '	}'."\n\n";
		$info .= '	public function PHPOK_arclist()'."\n";
		$info .= '	{'."\n";
		$info .= '		//这里开始编写PHP代码'."\n";
		$info .= '	}'."\n\n";
		$info .= '	public function PHPOK_arc()'."\n";
		$info .= '	{'."\n";
		$info .= '		//这里开始编写PHP代码'."\n";
		$info .= '	}'."\n\n";
		$info .= '	public function PHPOK_project()'."\n";
		$info .= '	{'."\n";
		$info .= '		//这里开始编写PHP代码'."\n";
		$info .= '	}'."\n\n";
		$info .= '	public function PHPOK_catelist()'."\n";
		$info .= '	{'."\n";
		$info .= '		//这里开始编写PHP代码'."\n";
		$info .= '	}'."\n\n";
		$info .= '	public function PHPOK_cate()'."\n";
		$info .= '	{'."\n";
		$info .= '		//这里开始编写PHP代码'."\n";
		$info .= '	}'."\n\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _php_model_base()
	{
		$info  = 'class model extends \\\phpok_model'."\n";
		$info .= '{'."\n";
		$info .= '	public function __construct()'."\n";
		$info .= '	{'."\n";
		$info .= '		parent::model();'."\n";
		$info .= '	}'."\n\n";
		$info .= '}'."\n";
		return $info;
	}

	private function _js_config($type='admin',$identifier='')
	{
		$lft = $type == 'admin' ? 'admin' : 'phpok_app';
		$info  = ';(function($){'."\n";
		$info .= '	$.'.$lft.'_'.$identifier.' = {'."\n\t\t//\n";
		$info .= '	}'."\n";
		$info .= '})(jQuery);'."\n";
		return $info;
	}

	private function _php_head()
	{
		$info = '<?php'."\n";
		return $info;
	}


	private function _get_applist()
	{
		$list = array();
		$this->_system_applist('admin',$list);
		$this->_system_applist('www',$list);
		$this->_system_applist('api',$list);
		$tmplist = $this->lib('file')->ls($this->dir_app);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				if(is_dir($value)){
					$list[] = basename($value);
				}
			}
		}
		if($list){
			$list = array_unique($list);
			return $list;
		}
		return false;
	}

	private function _system_applist($folder='admin',$list)
	{
		$tmplist = $this->lib('file')->ls($this->dir_phpok.$folder);
		if($tmplist){
			foreach($tmplist as $key=>$value){
				$tmp = basename($value);
				if(strpos($tmp,'_control.php') === false){
					unset($list[$key]);
					continue;
				}
				$tmp = str_replace("_control.php","",$tmp);
				$list[] = $tmp;
			}
		}
	}
}
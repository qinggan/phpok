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

	/**
	 * 卸载应用（删除相应文件）
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
		$zipfile = $this->dir_data.'zip/'.$id.'-'.date("Ymd",$this->time).'.zip';
		if(is_file($zipfile)){
			$this->lib('file')->rm($zipfile);
		}
		$this->lib('phpzip')->set_root($this->dir_app);
		$this->lib('phpzip')->zip($this->dir_app.$id,$zipfile);
		$config = $this->model('appsys')->get_one($id);
		if(!$config){
			$this->error(P_Lang('未找到配置信息'),$this->url('appsys'));
		}
		if($config['uninstall'] && is_file($this->dir_app.$id.'/'.$config['uninstall'])){
			include_once($this->dir_app.$id.'/'.$config['uninstall']);
		}
		$this->model('appsys')->uninstall($id);
		$this->success(P_Lang('应用卸载成功，系统已删除相关文件'),$this->url('appsys'));
	}

	/**
	 * 运行安装
	**/
	public function install_f()
	{
		if(!$this->popedom['install']){
			$this->error(P_Lang('您没有卸载应用的权限'),$this->url('appsys'));
		}
		$id = $this->get('id','system');
		if(!$id){
			$this->error(P_Lang('未指定项目'),$this->url('appsys'));
		}
		if(file_exists($this->dir_app.$id)){
			$this->error(P_Lang('应用已经存在，请检查'),$this->url('appsys'));
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
}
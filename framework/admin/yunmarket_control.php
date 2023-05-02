<?php
/**
 * 云市场客户端管理
 * @作者 苏相锟 <admin@phpok.com>
 * @版本 6.x
 * @授权 GNU Lesser General Public License (LGPL)
 * @时间 2023年4月23日
 * @更新 2023年4月23日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class yunmarket_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('yunmarket');
		$this->assign("popedom",$this->popedom);
	}


	/**
	 * 配置云市场参数
	**/
	public function config_f()
	{
		if(!$this->popedom['setting']){
			$this->error(P_Lang('没有权限操作'));
		}
		$config = $this->model('yunmarket')->config();
		if($config){
			$this->assign('rs',$config);
		}
		$this->view('yunmarket_config');
	}
	

	/**
	 * 保存配置的云市场信息
	**/
	public function config_save_f()
	{
		$status = $this->get('status','int');
		$server = $this->get('server');
		$ip = $this->get('ip');
		$appid = $this->get('appid');
		$appsecret = $this->get('appsecret');
		if($status && (!$server || !$appid || !$appsecret)){
			$this->error('参数不全，请填写完整参数');
		}
		$data = array('status'=>$status,'server'=>$server,'ip'=>$ip,'appid'=>$appid,'appsecret'=>$appsecret);
		$this->model('yunmarket')->setting($data);
		$this->success();
	}

	public function content_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$info = $this->model('yunmarket')->get_info($id);
		if(!$info){
			$this->error('内容信息不存在');
		}
		if(!$info['status']){
			$this->error($info['info']);
		}
		$install_rs = $this->model('yunmarket')->get_install($id);
		$rs = $info['info'];
		$rs['is_install'] = ($install_rs && $install_rs[$rs['id']]) ? true : false;
		$rs['is_update'] = false;
		if($install_rs && $install_rs[$rs['id']] && $install_rs[$rs['id']]['version_update'] != $rs['version_update']){
			$rs['is_update'] = true;
		}
		$rs['action'] = true;
		$rs['tips'] = '';
		if(!$rs['is_install']){
			if(file_exists($this->dir_root.$rs['folder'])){
				$rs['is_install'] = true;
				$rs['tips'] = P_Lang('本地安装模式');
				$rs['action'] = false;
			}
		}
		$this->assign('rs',$rs);
		$this->view('yunmarket_content');
	}

	/**
	 * 云市场列表
	**/
	public function index_f()
	{
		if(!$this->popedom['list']){
			$this->error(P_Lang('没有权限操作'));
		}
		$config = $this->model('yunmarket')->config();
		if(!$config || !$config['status']){
			$this->error(P_Lang('云市场未开启，<a href="'.$this->url('yunmarket','config').'">点击这里开启云市场</a>'));
		}
		$psize = $this->config['psize'];
		if(!$psize){
			$psize = 30;
		}
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$pageurl = $this->get('yunmarket');
		$keywords = $this->get('keywords');
		if($keywords){
			$this->assign('keywords',$keywords);
			$pageurl .= '&keywords='.rawurlencode($keywords);
		}
		$cateid = $this->get('cateid','int');
		if($cateid){
			$this->assign('cateid',$cateid);
			$pageurl .= '&cateid='.$cateid;
		}
		$t = $this->model('yunmarket')->get_all($keywords,$cateid,$offset,$psize);
		if($t && $t['status']){
			$info = $t['info'];
			$total = $info['total'];
			$rslist = $info['rslist'] ? $info['rslist'] : array();
			$install_rs = $this->model('yunmarket')->get_install();
			foreach($rslist as $key=>$value){
				$value['is_install'] = ($install_rs && $install_rs[$value['id']]) ? true : false;
				$value['is_update'] = false;
				if($install_rs && $install_rs[$value['id']] && $install_rs[$value['id']]['version_update'] != $value['version_update']){
					$value['is_update'] = true;
				}
				$value['action'] = true;
				$value['tips'] = '';
				if(!$value['is_install']){
					if(file_exists($this->dir_root.$value['folder'])){
						$value['is_install'] = true;
						$value['tips'] = P_Lang('本地安装模式');
						$value['action'] = false;
					}
				}
				$rslist[$key] = $value;
			}
			$catelist = $info['catelist'] ? $info['catelist'] : array();
			$this->assign('catelist',$catelist);
			$this->assign('rslist',$rslist);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			if($total>$psize){
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
		}
		if($t && !$t['status']){
			$this->assign('errinfo',$t['info']);
		}
		$this->view('yunmarket_index');
	}


	public function install_f()
	{
		if(!$this->popedom['install']){
			$this->error(P_Lang('没有权限操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$install_rs = $this->model('yunmarket')->get_install($id);
		if($install_rs && $install_rs[$id]){
			$this->error(P_Lang('软件已存在，请不要重复安装'));
		}
		$info = $this->model('yunmarket')->download($id);
		if(!$info){
			$this->error('内容信息不存在');
		}
		if(!$info['status']){
			$this->error($info['info']);
		}
		$rs = $info['info'];
		if(!$rs['download']){
			$this->error(P_Lang('安装文件下载失败'));
		}
		//开始进入安装
		$info = base64_decode($rs['download']);
		file_put_contents($this->dir_data.'tmp.zip',$info);
		$this->lib('file')->make($this->dir_data.'tmp/','folder');
		$this->lib('phpzip')->unzip($this->dir_data.'tmp.zip',$this->dir_data.'tmp/');
		$this->lib('file')->rm($this->dir_data.'tmp.zip');
		if(substr($rs['folder']) == '/'){
			$rs['folder'] = substr($rs['folder'],0,-1);
		}
		$tmpname = basename($rs['folder']);
		$list = $this->lib('file')->ls($this->dir_data.'tmp/');
		$strlen = strlen($this->dir_data."tmp/".$tmpname.'/');
		$install_php = '';
		$checkfile = $this->dir_data.'tmp/'.$tmpname;
		if(!file_exists($checkfile) || !is_dir($checkfile)){
			$this->lib('file')->rm($this->dir_data.'tmp','folder');
			$this->error(P_Lang('安装失败，安装包制作异常'));
		}
		$folder = $this->dir_root.$rs['folder'].'/';
		if(!is_dir($folder)){
			$this->lib('file')->make($folder);
		}
		$list = array();
		$this->lib('file')->deep_ls($this->dir_data.'tmp/'.$tmpname,$list);
		foreach($list as $key=>$value){
			$tmp = substr($value,$strlen);
			if(is_file($value)){
				$this->lib('file')->mv($value,$folder.$tmp);
			}
			if(is_dir($value) && !is_dir($this->dir_root.$folder.$tmp)){
				$this->lib('file')->make($folder.$tmp,'folder');
			}
		}
		$install_php = $this->dir_data.'tmp/install.php';
		if(file_exists($install_php)){
			include($install_php);
		}
		//检测是否_app或是plugins
		$app_chk = substr($rs['folder'],0,5);
		$plugin_chk = substr($rs['folder'],0,8);
		$type = '';
		if($app_chk == '_app/'){
			$type = 'app';
		}
		if($plugin_chk == 'plugins/'){
			$type = 'plugin';
		}
		//增加记录
		$data = array('id'=>$id,'md5'=>$rs['md5'],'version'=>$rs['version'],'version_update'=>$rs['version_update'],'dateline'=>$this->time);
		$data['folder'] = $rs['folder'];
		$this->model('yunmarket')->install($data);
		//删除目录
		$this->lib('file')->rm($this->dir_data.'tmp','folder');
		$this->success($type);
	}

	public function update_f()
	{
		if(!$this->popedom['update']){
			$this->error(P_Lang('没有权限操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$install_rs = $this->model('yunmarket')->get_install($id);
		if(!$install_rs || !$install_rs[$id]){
			$this->error(P_Lang('软件未安装，不能执行升级'));
		}
		$info = $this->model('yunmarket')->download($id);
		if(!$info){
			$this->error('内容信息不存在');
		}
		if(!$info['status']){
			$this->error($info['info']);
		}
		$rs = $info['info'];
		if(!$rs['download']){
			$this->error(P_Lang('安装文件下载失败'));
		}
		//开始进入升级
		$info = base64_decode($rs['download']);
		file_put_contents($this->dir_data.'tmp.zip',$info);
		$this->lib('file')->make($this->dir_data.'tmp/','folder');
		$this->lib('phpzip')->unzip($this->dir_data.'tmp.zip',$this->dir_data.'tmp/');
		$this->lib('file')->rm($this->dir_data.'tmp.zip');
		if(substr($rs['folder']) == '/'){
			$rs['folder'] = substr($rs['folder'],0,-1);
		}
		$tmpname = basename($rs['folder']);
		$list = $this->lib('file')->ls($this->dir_data.'tmp/');
		$strlen = strlen($this->dir_data."tmp/".$tmpname.'/');
		$checkfile = $this->dir_data.'tmp/'.$tmpname;
		if(!file_exists($checkfile) || !is_dir($checkfile)){
			$this->lib('file')->rm($this->dir_data.'tmp','folder');
			$this->error(P_Lang('升级失败，升级包制作异常'));
		}
		$folder = $this->dir_root.$rs['folder'].'/';
		if(!is_dir($folder)){
			$this->lib('file')->make($folder);
		}
		$list = array();
		$this->lib('file')->deep_ls($this->dir_data.'tmp/'.$tmpname,$list);
		$app_chk = substr($rs['folder'],0,5);
		foreach($list as $key=>$value){
			$tmp = substr($value,$strlen);
			if(is_file($value)){
				$tmpbasename = basename($value);
				//因为是升级操作，这里就不覆盖 config.xml 配置文件
				if($tmpbasename == 'config.xml' && $app_chk == '_app/'){
					continue;
				}
				$this->lib('file')->mv($value,$folder.$tmp);
			}
			if(is_dir($value) && !is_dir($this->dir_root.$folder.$tmp)){
				$this->lib('file')->make($folder.$tmp,'folder');
			}
		}
		$install_php = $this->dir_data.'tmp/update.php';
		if(file_exists($install_php)){
			include($install_php);
		}
		//检测是否_app或是plugins
		$plugin_chk = substr($rs['folder'],0,8);
		$type = '';
		if($app_chk == '_app/'){
			$type = 'app';
		}
		if($plugin_chk == 'plugins/'){
			$type = 'plugin';
		}
		//升级记录
		$data = array('id'=>$id,'md5'=>$rs['md5'],'version'=>$rs['version'],'version_update'=>$rs['version_update'],'dateline'=>$this->time);
		$data['folder'] = $rs['folder'];
		$this->model('yunmarket')->install($data);
		//删除目录
		$this->lib('file')->rm($this->dir_data.'tmp','folder');
		$this->success($type);
	}

	public function uninstall_f()
	{
		if(!$this->popedom['uninstall']){
			$this->error(P_Lang('没有权限操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$install_rs = $this->model('yunmarket')->get_install($id);
		if(!$install_rs || !$install_rs[$id]){
			$this->error(P_Lang('软件不存在'));
		}
		$rs = $install_rs[$id];
		$folder = $rs['folder'];
		if(!$folder){
			$this->error(P_Lang('数据异常，未指定目录'));
		}
		$app_chk = substr($rs['folder'],0,5);
		$plugin_chk = substr($rs['folder'],0,8);
		$tmpname = basename($folder);
		if($app_chk == '_app/'){
			$info = $this->model('appsys')->get_one($tmpname);
			if($info['installed']){
				$this->error(P_Lang('请先到应用中心去卸载应用，才能执行云市场卸载'));
			}
		}
		if($plugin_chk == 'plugins/'){
			$info = $this->model('plugin')->get_one($tmpname);
			if($info){
				$this->error(P_Lang('请先到插件中心去卸载插件，才能执行云市场卸载'));
			}
		}
		$this->model('yunmarket')->uninstall($id);
		$this->lib('file')->rm($this->dir_root.$folder,'folder');
		$this->success();
	}
}

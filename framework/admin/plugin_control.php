<?php
/***********************************************************
	Filename: {phpok}/admin/plugin_control.php
	Note	: 插件中心
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-08 10:04
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class plugin_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("plugin");
		$this->assign("popedom",$this->popedom);
	}

	//取得插件列表
	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('plugin')->get_all();
		$this->assign("rslist",$rslist);
		$dlist = $this->model('plugin')->dir_list();
		if($dlist){
			$not_install = "";
			foreach($dlist AS $key=>$value){
				if(!$rslist[$value] || !$rslist){
					$not_install[$value] = $this->model('plugin')->get_xml($value);
				}
			}
			$this->assign('not_install',$not_install);
		}
		$array = array("identifier"=>'zipfile',"form_type"=>'upload');
		$array['upload_type'] = 'update';
		$this->lib('form')->cssjs($array);
		$upload = $this->lib('form')->format($array);
		$this->assign('upload_html',$upload);
		$this->view("plugin_index");
	}

	//配置件插件信息
	public function config_f()
	{
		if(!$this->popedom["config"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_one($id);
		if($rs['param']){
			$rs['param'] = unserialize($rs['param']);
		}
		$this->assign("rs",$rs);
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('index',$methods)){
				$plugin_html = $cls->index();
				$this->assign('plugin_html',$plugin_html);
			}
		}
		$this->view("plugin_config");
	}

	//存储配置的插件信息
	public function save_f()
	{
		if(!$this->popedom["config"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			error(P_Lang('数据记录不存在'),$this->url('plugin'),'error');
		}
		$title = $this->get('title');
		if(!$title){
			error(P_Lang('插件名称不能为空'),$this->url('plugin','config','id='.$id),'error');
		}
		$note = $this->get('note');
		$taxis = $this->get("taxis",'int');
		$author = $this->get('author');
		$version = $this->get('version');
		$array = array('title'=>$title,'note'=>$note,'taxis'=>$taxis,'author'=>$author,'version'=>$version);
		$this->model('plugin')->update_plugin($array,$id);
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods)){
				$cls->save();
			}
		}
		error(P_Lang('{title}设置成功',array('title'=>' <span class="red">'.$rs['title'].'</span> ')),$this->url("plugin"),'ok');
	}

	public function unzip_f()
	{
		$id = $this->get('id','int');
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('附件不存在'));
		}
		if($rs['ext'] != 'zip'){
			$this->json(P_Lang('非ZIP文件不支持在线解压'));
		}
		if(!file_exists($this->dir_root.$rs['filename'])){
			$this->json(P_Lang('文件不存在'));
		}
		$info = $this->lib('phpzip')->zip_info($this->dir_root.$rs['filename']);
		$info = current($info);
		if(!$info['filename']){
			$this->json(P_Lang('插件有异常'));
		}
		$info = explode('/',$info['filename']);
		if(!$info[0]){
			$this->json(P_Lang('插件有异常'));
		}
		if(file_exists($this->dir_root.'plugins/'.$info[0])){
			$this->json(P_Lang('插件已存在，不允许重复解压'));
		}
		if(!$info[1]){
			$this->json(P_Lang('插件打包模式有问题'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$rs['filename'],$this->dir_root.'plugins/');
		$this->json(true);
	}

	//安装插件
	function install_f()
	{
		if(!$this->popedom["install"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$this->assign("id",$id);
		$rs = $this->model('plugin')->get_xml($id);
		$rs['taxis'] = $this->model('plugin')->get_next_taxis();
		$this->assign("rs",$rs);
		if(file_exists($rs['path'].'install.php')){
			include_once($rs['path'].'install.php');
			$name = 'install_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array("index",$methods)){
				$info = $cls->index();
				$this->assign("plugin_html",$info);
			}
		}
		$this->view("plugin_install");
	}

	//存储安装插件中的信息
	public function install_save_f()
	{
		if(!$this->popedom["install"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('plugin'),'error');
		}
		$title = $this->get('title');
		if(!$title){
			error(P_Lang('插件名称不能为空'),$this->url('plugin','config','id='.$id),'error');
		}
		$note = $this->get("note");
		$taxis = $this->get('taxis','int');
		$author = $this->get('author');
		$version = $this->get('version');
		$array = array('id'=>$id,'title'=>$title,'note'=>$note,'status'=>0,'author'=>$author,'taxis'=>$taxis,'version'=>$version);
		//存储安装数据
		$id = $this->model('plugin')->install_save($array);
		if(!$id){
			error(P_Lang('插件安装失败'),$this->url('plugin','install','id='.$id),'error');
		}
		//判断是否有
		$xmlrs = $this->model('plugin')->get_xml($id);
		if(file_exists($xmlrs['path'].'install.php')){
			include_once($xmlrs['path'].'install.php');
			$name = 'install_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('save',$methods)){
				$cls->save();
			}
		}
		error(P_Lang('{title}安装成功',array('title'=>' <span class="red">'.$title.'</span> ')),$this->url("plugin"),'ok');
	}

	//卸载插件
	public function uninstall_f()
	{
		if(!$this->popedom["install"]){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('数据记录不存在'));
		}
		if(file_exists($this->dir_root.'plugins/'.$id.'/uninstall.php')){
			include_once($this->dir_root.'plugins/'.$id.'/uninstall.php');
			$name = 'uninstall_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if(in_array("index",$methods)){
				$cls->index();
			}
		}
		$this->model('plugin')->delete($id);
		$this->json(P_Lang('插件卸载成功'),true);
	}

	//状态执行
	public function status_f()
	{
		if(!$this->popedom["install"]){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('数据记录不存在'));
		}
		$status = $rs["status"] ? 0 : 1;
		$this->model('plugin')->update_status($id,$status);
		//执行插件运行
		if(file_exists($this->dir_root.'plugins/'.$id.'/setting.php')){
			include_once($this->dir_root.'plugins/'.$id.'/setting.php');
			$name = 'setting_'.$id;
			$cls = new $name();
			$methods = get_class_methods($cls);
			if($methods && in_array('status',$methods)){
				$cls->status();
			}
		}
		$this->json($status,true,true,false);
	}

	//执行JS
	public function exec_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('plugin')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('数据记录不存在'));
		}
		if($rs['param']) $rs['param'] = unserialize($rs['param']);
		if(!file_exists($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php')){
			$this->json(P_Lang('插件文件{appid}不存在',array('appid'=>' <span class="red">'.$this->app_id.'.php</span> ')));
		}
		include_once($this->dir_root.'plugins/'.$id.'/'.$this->app_id.'.php');
		$name = $this->app_id.'_'.$id;
		$cls = new $name();
		$methods = get_class_methods($cls);
		$exec = $this->get("exec");
		if(!$exec) $exec = 'index';
		if(!$methods || !in_array($exec,$methods)){
			$this->json(P_Lang('方法{method}不存在',array('method'=>'<span class=red>'.$exec.'</span> ')));
		}
		$cls->$exec($rs);
	}

	public function ajax_f()
	{
		$this->exec_f();
	}

	//导出插件
	public function zip_f()
	{
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('插件标识不存在'),$this->url('plugin'),'error');
		}
		if(!file_exists($this->dir_root.'plugins/'.$id)){
			error(P_Lang('插件不存在'),$this->url('plugin'),'error');
		}
		//打包
		$zipfile = $this->dir_root.'data/cache/'.$id.'.zip';
		$this->lib('phpzip')->set_root($this->dir_root.'plugins/');
		$this->lib('phpzip')->zip($this->dir_root.'plugins/'.$id,$zipfile);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->time)." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($id.".zip"));
		header("Content-Length: ".filesize($zipfile));
		header("Accept-Ranges: bytes");
		readfile($zipfile);
		flush();
		ob_flush();
	}

	public function create_f()
	{
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('插件名称不能为空'));
		}
		$id = $this->get('id','system');
		if($id){
			if(strpos($id,'_') !== false){
				$this->json(P_Lang('插件标识不支持下划线'));
			}
			$id = strtolower($id);
		}else{
			$id = md5($title.'-phpok.com-'.uniqid(rand(), true));
		}
		//检测插件文件夹是否存在
		if(file_exists($this->dir_root.'plugins/'.$id)){
			$this->json(P_Lang('插件标识已被使用，请重新设置'));
		}		
		$note = $this->get('note');
		$author = $this->get('author');
		if(!$author){
			$author = 'phpok.com';
		}
		if(!$note){
			$note = P_Lang('这是由程序后台初始化的一个小插件');
		}
		//创建XML文件
		$content = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$content.= '<root>'."\n\t";
		$content.= '<title>'.$title.'</title>'."\n\t";
		$content.= '<desc>'.$note.'</desc>'."\n\t";
		$content.= '<author>'.$author.'</author>'."\n\t";
		$content.= '<version>1.0</version>'."\n";
		$content.= '</root>';
		$this->lib('file')->vim($content,$this->dir_root.'plugins/'.$id.'/config.xml');
		$this->lib('file')->vim('',$this->dir_root.'plugins/'.$id.'/template/setting.html');
		$array = array('www','api','admin','install','uninstall','setting');
		foreach($array as $key=>$value){
			$content = '<?php'."\n".$this->php_note_title($id,$value,$title,$author)."\n".$this->php_demo($id,$value);
			$this->lib('file')->vim($content,$this->dir_root.'plugins/'.$id.'/'.$value.'.php');
		}
		$this->json(true);
	}

	private function php_note_title($id,$fileid,$title='',$author='')
	{
		$note = '';
		switch($fileid) {
			case "admin":
				$note = P_Lang('后台应用');
				break;
			case 'www':
			    $note = P_Lang('前台应用');
				break;
			case 'api':
			    $note = P_Lang('接口应用');
				break;
			case 'install':
			    $note = P_Lang('插件安装');
				break;
			case 'uninstall':
			    $note = P_Lang('插件卸载');
				break;
			case 'setting':
			    $note = P_Lang('插件配置');
				break;
			default:
				$note = P_Lang('未知');
		}
		$string = '/*****************************************************************************************'."\n\t";
		$string.= '文件： plugins/'.$id.'/'.$fileid.'.php'."\n\t";
		$string.= '备注： '.$title.'<'.$note.">\n\t";
		$string.= '版本： 4.x'."\n\t";
		$string.= '网站： www.phpok.com'."\n\t";
		if($author){
			$string.= '作者： '.$author."\n\t";
		}
		$string.= '时间： '.date("Y年m月d日 H时i分",$this->time)."\n";
		$string.= '*****************************************************************************************/';
		return $string;
	}

	private function php_demo($id,$fileid)
	{
		$string = 'class '.$fileid.'_'.$id.' extends phpok_plugin'."\n";
		$string.= '{'."\n\t";
		$string.= 'public $me;'."\n\t";
		$string.= 'public function __construct()'."\n\t";
		$string.= '{'."\n\t\t";
		$string.= 'parent::plugin();'."\n\t\t";
		$string.= '$this->me = $this->_info();'."\n\t";
		$string.= '}'."\n\t";
		//初始化全局应用
		if($fileid == 'www' || $fileid == 'admin' || $fileid == 'api'){
			$string .= '//全局运行插件，在执行当前方法运行前，调整参数'."\n\t";
			$string .= 'public function phpok_before()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//编写代码;'."\n\t";
			$string.= '}'."\n\t";
			$string .= '//全局运行插件，在执行当前方法运行后，数据未输出前'."\n\t";
			$string .= 'public function phpok_after()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//编写代码;'."\n\t";
			$string.= '}'."\n\t";
		}
		if($fileid == 'install'){
			$string .= '//插件安装时，增加的扩展表单输出项'."\n\t";
			$string .= 'public function index()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//return $this->_tpl(\'setting.html\');'."\n\t";
			$string.= '}'."\n\t";
			$string .= '//插件安装时，保存扩展参数'."\n\t";
			$string .= 'public function save()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '$id = $this->_id();'."\n\t\t";
			$string.= '$ext = array();'."\n\t\t";
			$string.= '//$ext[\'扩展参数字段名\'] = $this->get(\'表单字段名\');'."\n\t\t";
			$string.= '$this->_save($ext,$id);'."\n\t";
			$string.= '}'."\n\t";
		}
		if($fileid == 'uninstall'){
			$string .= '//插件卸载时，执行的方法，如删除表，或去除其他一些选项'."\n\t";
			$string .= 'public function index()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//执行一些自定义的动作'."\n\t";
			$string.= '}'."\n\t";
		}
		if($fileid == 'setting'){
			$string .= '//插件配置参数时，增加的扩展表单输出项'."\n\t";
			$string .= 'public function index()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//return $this->_tpl(\'setting.html\');'."\n\t";
			$string.= '}'."\n\t";
			$string .= '//插件配置参数时，保存扩展参数'."\n\t";
			$string .= 'public function save()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '$id = $this->_id();'."\n\t\t";
			$string.= '$ext = array();'."\n\t\t";
			$string.= '//$ext[\'扩展参数字段名\'] = $this->get(\'表单字段名\');'."\n\t\t";
			$string.= '$this->_save($ext,$id);'."\n\t";
			$string.= '}'."\n\t";
			$string .= '//插件执行审核动作时，执行的操作'."\n\t";
			$string .= 'public function status()'."\n\t";
			$string.= '{'."\n\t\t";
			$string.= '//执行一些自定义的动作'."\n\t";
			$string.= '}'."\n\t";
		}
		$string.= "\n";
		$string.= '}';
		return $string;
	}
}
?>
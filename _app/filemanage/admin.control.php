<?php
/**
 * 后台管理_管理整个平台的文件，包括修改自身，仅限系统管理员
 * @作者 锟铻科技
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2019年04月09日 15时31分
**/
namespace phpok\app\control\filemanage;
/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}
class admin_control extends \phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('filemanage');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->session->val('admin_id_checked')){
			$this->display('admin_vcode');
		}
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		if(substr($folder,-1) != '/'){
			$folder .= "/";
		}
		$tmplist = explode("/",$folder);
		$leadlist = array();
		$leadlist[0] = array('title'=>P_Lang('根目录'),'url'=>$this->url('filemanage'));
		$tmplist = explode("/",$folder);
		$str = '';
		foreach($tmplist as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$str .= $value."/";
			$leadurl = $this->url('filemanage','','folder='.rawurlencode($str));
			$leadlist[] = array('title'=>basename($value),'url'=>$leadurl);
		}
		$this->assign('leadlist',$leadlist);
		$this->assign("folder",$folder);
		//绑定目录
		$tpl_dir = $this->dir_root.$folder;
		$tpl_list = $this->lib('file')->ls($tpl_dir);
		$ext_length = strlen($rs["ext"]);
		if(!$tpl_list){
			$tpl_list = array();
		}
		$myurl = $this->url('filemanage');
		$rslist = $dirlist = array();
		$rs_i = $dir_i = 0;
		$edit_array = array("html","php","js","css","asp","jsp","tpl","dwt","aspx","htm","txt","xml");
		$pic_array = array("gif","png","jpeg","jpg","svg");
		$this->assign("edit_array",$edit_array);
		$this->assign("pic_array",$pic_array);
		foreach($tpl_list as $key=>$value){
			$bname = basename($value);
			$type = is_dir($value) ? "dir" : "file";
			if(is_dir($value)){
				$url = $this->url("filemanage","folder=".rawurlencode($folder.$bname."/"));
				$dirlist[] = array("filename"=>$value,"title"=>$bname,"data"=>"","type"=>"dir","url"=>$url,'id'=>'ok_'.md5($folder.$bname));
				$dir_i++;
			}else{
				$date = date("Y-m-d H:i:s",filemtime($value));
				$type = "html";
				if(substr($bname,-$ext_length) != $rs["ext"]){
					$tmp = explode(".",$bname);
					$tmp_total = count($tmp);
					$type = "unknown";
					if($tmp_total > 1){
						$tmp_ext = strtolower($tmp[($tmp_total-1)]);
						$typefile = $this->dir_root."images/filetype/".$tmp_ext.".gif";
						$type = file_exists($typefile) ? $tmp_ext : "unknown";
					}
				}
				$tmp = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>$type,'id'=>'ok_'.md5($folder.$bname));
				$rslist[] = $tmp;
				$rs_i++;
			}
		}
		if($dir_i> 0){
			$this->assign("dirlist",$dirlist);
		}
		if($rs_i > 0){
			$this->assign("rslist",$rslist);
		}
		$this->display('admin_index');
	}

	/**
	 * 内容编辑器
	**/
	public function edit_f()
	{
		if(!$this->session->val('admin_id_checked')){
			$this->display('admin_vcode');
		}
		if(!$this->popedom["edit"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}

		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		if(substr($folder,-1) != '/'){
			$folder .= "/";
		}
		$title = $this->get("title");
		$file = $this->dir_root.$folder.$title;
		if(!file_exists($file)){
			$this->error(P_Lang('文件不存在'));
		}
		$is_edit = true;
		if(!is_writable($file)){
			$tips = P_Lang('文件无法写法，不支持在线编辑');
			$this->assign('tips',$tips);
			$is_edit = false;
		}
		$this->assign('is_edit',$is_edit);
		$content = $this->lib('file')->cat($file);
		$content = str_replace(array("&lt;",'&gt;'),array("&amp;lt;","&amp;gt;"),$content);
		$content = str_replace(array('<','>'),array('&lt;','&gt;'),$content);
		$this->assign("content",$content);
		$this->assign("folder",$folder);
		$this->assign("title",$title);
		//加载编辑器
		$cdnUrl = phpok_cdn();
		$this->addcss($cdnUrl.'codemirror/5.42.2/lib/codemirror.css?response-content-type=text/css');
		$this->addjs($cdnUrl.'codemirror/5.42.2/lib/codemirror.js?response-content-type=application/x-javascript');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/css/css.js?response-content-type=application/x-javascript');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/javascript/javascript.js?response-content-type=application/x-javascript');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/htmlmixed/htmlmixed.js?response-content-type=application/x-javascript');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/php/php.js?response-content-type=application/x-javascript');
		$this->addjs($cdnUrl.'codemirror/5.42.2/mode/xml/xml.js?response-content-type=application/x-javascript');
		//数据调用
		$oklist = $this->model('call')->get_list("ok.status=1",0,999);
		$this->assign('oklist',$oklist);
		//
		$this->display("admin_edit");
	}

	public function save_f()
	{
		if(!$this->session->val('admin_id_checked')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		if(substr($folder,-1) != '/'){
			$folder .= "/";
		}
		$title = $this->get("title");
		$file = $this->dir_root.$folder.$title;
		if(!file_exists($file)){
			$this->error(P_Lang('文件不存在'));
		}
		if(!is_writable($file)){
			$this->error(P_Lang('文件无法写法，不支持在线编辑'));
		}
		$content = $this->get("content","html_js");
		$this->lib('file')->vim($content,$file);
		$this->success();
	}

	/**
	 * 上传附件
	**/
	public function import_f()
	{
		if(!$this->session->val('admin_id_checked')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$array = array("identifier"=>'zipfile',"form_type"=>'upload');
		$array['upload_type'] = 'update';
		$this->lib('form')->cssjs($array);
		$upload = $this->lib('form')->format($array);
		$this->assign('upload_html',$upload);
		$folder = $this->get('folder');
		if($folder != '/' && substr($folder,0,1) == '/'){
			$folder = substr($folder,1);
		}
		if($folder != '/' && substr($folder,-1) != '/'){
			$folder .= '/';
		}
		$this->assign('folder',$folder);
		//上传的附件类型
		$cate_all = $this->model('cate')->cate_all();
		$types = 'php,js,css,html,txt,xml';
		if($cate_all){
			foreach($cate_all as $key=>$value){
				if($value['filetypes']){
					$types .= ",".$value['filetypes'];
				}
			}
		}
		$this->assign('filetypes',$types);
		$this->display('admin_upload');
	}

	public function upload_f()
	{
		$this->config('is_ajax',true);
		if(!$this->session->val('admin_id_checked')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->get('folder');
		if($folder != '/' && substr($folder,0,1) == '/'){
			$folder = substr($folder,1);
		}
		if($folder != '/' && substr($folder,-1) != '/'){
			$folder .= '/';
		}
		$rs = $this->lib('upload')->upload('upfile',$folder);
		if($rs['status'] != 'ok'){
			$this->error($rs['error']);
		}
		$nfile = $folder.$rs['title'].'.'.$rs['ext'];
		if(is_file($this->dir_root.$rs['filename'])){
			$this->lib('file')->mv($this->dir_root.$rs['filename'],$this->dir_root.$nfile);
		}
		$this->success($nfile);
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
	
}

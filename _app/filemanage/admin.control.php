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

	/**
	 * 创建文件（夹）
	**/
	public function create_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->view('admin_vcode');
		}
		if(!$this->popedom["add"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->_folder();
		$title = $this->get("title");
		$type = $this->get("type");
		if(!$type){
			$type = "file";
		}
		$file = $this->dir_root.$folder.$title;
		if(file_exists($file)){
			$this->error(P_Lang('要创建的文件（夹）名称已经存在，请检查'));
		}
		if($type == "folder"){
			$this->lib('file')->make($file,"dir");
		}else{
			$this->lib('file')->make($file,"file");
		}
		$this->success();
	}

	/**
	 * 复制文件（夹）
	**/
	public function copy_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"] || !$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('未指定要复制的文件（夹）'));
		}
		$folder = $this->get('folder');
		if(!$folder){
			$folder = '/';
		}
		$tmpdata = array('title'=>$title,'folder'=>$folder);
		$file = $this->dir_data.'cp-'.$this->session->val('admin_id').'.php';
		$this->lib('file')->vi($tmpdata,$file,'config');
		//删除移动记录
		$file = $this->dir_data.'mv-'.$this->session->val('admin_id').'.php';
		if(file_exists($file)){
			$this->lib('file')->rm($file);
		}
		$this->success();
	}

	/**
	 * 仅供CSS操作
	**/
	public function css_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$input = $this->get('input');
		if(!$input){
			$input = 'css';
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
		$leadlist[0] = array('title'=>P_Lang('根目录'),'url'=>$this->url('filemanage','css','input='.$input));
		$tmplist = explode("/",$folder);
		$str = '';
		foreach($tmplist as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$str .= $value."/";
			$leadurl = $this->url('filemanage','css','input='.$input.'&folder='.rawurlencode($str));
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
		foreach($tpl_list as $key=>$value){
			$bname = basename($value);
			$type = is_dir($value) ? "dir" : "file";
			$date = date("Y-m-d H:i:s",filemtime($value));
			
			if(is_dir($value)){
				$url = $this->url("filemanage","css","input=".$input."&folder=".rawurlencode($folder.$bname."/"));
				$dirlist[] = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>"dir","url"=>$url,'id'=>'ok_'.md5($folder.$bname));
				$dir_i++;
			}else{
				if(substr($bname,-3) != 'css'){
					continue;
				}
				$tmp = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>'css','id'=>'ok_'.md5($folder.$bname));
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
		$this->assign('input',$input);
		$this->display('admin_css');
	}

	/**
	 * 删除文件夹
	**/
	public function delfile_f()
	{
		if(!$this->popedom["delete"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->get('folder');
		if(!$folder){
			$this->error(P_Lang('未指定目录'));
		}
		
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('未指定要删除的文件（夹）'));
		}
		if($folder == '/'){
			$delfile = $this->dir_root.$title;
		}else{
			if(substr($folder,0,1) == '/'){
				$folder = substr($folder,1);
			}
			if(substr($folder,-1) != '/'){
				$folder .= '/';
			}
			$delfile = $this->dir_root.$folder.$title;
		}
		if(!file_exists($delfile)){
			$this->error(P_Lang('文件（夹）不存在'));
		}
		if(is_dir($delfile)){
			$this->lib('file')->rm($delfile,"folder");
		}else{
			$this->lib('file')->rm($delfile);
		}
		$this->success();
	}

	/**
	 * 下载文件
	**/
	public function download_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->view('admin_vcode');
		}
		if(!$this->popedom["edit"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->_folder();
		$title = $this->get("title");
		$file = $this->dir_root.$folder.$title;
		if(!is_file($file)){
			$this->error(P_Lang('不支持文件夹下载或当前文件不存在'));
		}
		$this->lib('file')->download($file,$title);
	}

	/**
	 * 内容编辑器
	**/
	public function edit_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->view('admin_vcode');
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
			$tips = P_Lang('文件无法写入，不支持在线编辑');
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
		$this->addcss("static/codemirror/lib/codemirror.css");
		$this->addjs("static/codemirror/lib/codemirror.js");
		$this->addjs('static/codemirror/mode/css/css.js');
		$this->addjs('static/codemirror/mode/javascript/javascript.js');
		$this->addjs('static/codemirror/mode/htmlmixed/htmlmixed.js');
		$this->addjs('static/codemirror/mode/php/php.js');
		$this->addjs('static/codemirror/mode/xml/xml.js');
		$istpl = strpos($folder,'tpl/') !== false ? true : false;
		$this->assign('istpl',$istpl);
		if($istpl){
			$oklist = $this->model('call')->get_list("ok.status=1",0,999);
			$this->assign('oklist',$oklist);
			$this->assign('ishtml',true);
		}else{
			$tmp = strtolower($title);
			$ishtml = strpos($tmp,'.htm') !== false ? true : false;
			$this->assign('ishtml',$ishtml);
			$isphp = strpos($tmp,'.php') !== false ? true : false;
			$this->assign('isphp',$isphp);
		}
		$this->display("admin_edit");
	}


	/**
	 * 仅供文件选择操作
	**/
	public function file_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$input = $this->get('input');
		if(!$input){
			$input = 'tplfile';
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
		$leadlist[0] = array('title'=>P_Lang('根目录'),'url'=>$this->url('filemanage','file','input='.$input));
		$tmplist = explode("/",$folder);
		$str = '';
		foreach($tmplist as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$str .= $value."/";
			$leadurl = $this->url('filemanage','file','input='.$input.'&folder='.rawurlencode($str));
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
		$extlist = array('.php','.html','.htm','.asp','.aspx','.xhtml','.jsp','.jspx');
		foreach($tpl_list as $key=>$value){
			$bname = basename($value);
			$type = is_dir($value) ? "dir" : "file";
			$date = date("Y-m-d H:i:s",filemtime($value));
			if(is_dir($value)){
				$url = $this->url("filemanage","file","input=".$input."&folder=".rawurlencode($folder.$bname."/"));
				$dirlist[] = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>"dir","url"=>$url,'id'=>'ok_'.md5($folder.$bname));
				$dir_i++;
			}else{
				$tmpext = strstr($bname,'.');
				if(!$tmpext){
					continue;
				}
				$tmpext = strtolower($tmpext);
				if(!in_array($tmpext,$extlist)){
					continue;
				}
				$tmp = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>'html','id'=>'ok_'.md5($folder.$bname));
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
		$this->assign('input',$input);
		$this->display('admin_html');
	}

	/**
	 * 移动
	**/
	public function move_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"] || !$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('未指定要移动的文件（夹）'));
		}
		$folder = $this->get('folder');
		if(!$folder){
			$folder = '/';
		}
		$tmpdata = array('title'=>$title,'folder'=>$folder);
		$file = $this->dir_data.'mv-'.$this->session->val('admin_id').'.php';
		$this->lib('file')->vi($tmpdata,$file,'config');
		//删除复制记录
		$file = $this->dir_data.'cp-'.$this->session->val('admin_id').'.php';
		if(file_exists($file)){
			$this->lib('file')->rm($file);
		}
		$this->success();
	}


	/**
	 * 上传附件
	**/
	public function import_f()
	{
		if(!$this->session->val('admin2verify')){
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

	/**
	 * 文件列表
	**/
	public function index_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->view('admin_vcode');
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
			$date = date("Y-m-d H:i:s",filemtime($value));
			
			if(is_dir($value)){
				$url = $this->url("filemanage","folder=".rawurlencode($folder.$bname."/"));
				$dirlist[] = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>"dir","url"=>$url,'id'=>'ok_'.md5($folder.$bname));
				$dir_i++;
			}else{
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
		//判断是否有粘贴
		$is_paste = false;
		if($this->popedom['add'] && $this->popedom['edit']){
			
			$file = $this->dir_data.'cp-'.$this->session->val('admin_id').'.php';
			if(file_exists($file)){
				include_once($file);
				if($config && $config['folder'] != $folder){
					$is_paste = true;
				}
			}else{
				$file = $this->dir_data.'mv-'.$this->session->val('admin_id').'.php';
				if(file_exists($file)){
					include_once($file);
					if($config && $config['folder'] != $folder){
						$is_paste = true;
					}
				}
			}
			$this->assign('is_paste',$is_paste);
		}
		$this->display('admin_index');
	}


	/**
	 * 粘贴
	**/
	public function paste_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"] || !$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$file = $this->dir_data.'mv-'.$this->session->val('admin_id').'.php';
		$is_move = true;
		if(!file_exists($file)){
			$is_move = false;
			$file = $this->dir_data.'cp-'.$this->session->val('admin_id').'.php';
			if(!file_exists($file)){
				$this->error(P_Lang('没有粘贴项'));
			}
		}
		include_once($file);
		if(!$config){
			$this->error(P_Lang('粘贴异常，移动（复制）记录有异'));
		}
		$folder = $this->get('folder');
		if(!$folder){
			$folder = '/';
		}
		$old = $this->dir_root;
		if($config['folder'] == $folder){
			$this->error(P_Lang('目标一样，不支持移动或复制'));
		}
		$old = $this->dir_root;
		if($config['folder'] == '/'){
			$old .= $config['title'];
		}else{
			if(substr($config['folder'],0,-1) == '/'){
				$config['folder'] = substr($config['folder'],1);
			}
			if(substr($config['folder'],-1) != '/'){
				$config['folder'] .= '/';
			}
			$old .= $config['folder'].$config['title'];
		}
		if(!file_exists($old)){
			$this->error(P_Lang('复制或移动失败，文件（夹）不存在'));
		}
		$new = $this->dir_root;
		if($folder == '/'){
			$new .= $config['title'];
		}else{
			if(substr($folder,0,-1) == '/'){
				$folder = substr($folder,1);
			}
			if(substr($config['folder'],-1) != '/'){
				$folder .= '/';
			}
			$new .= $folder.$title;
		}
		if($is_move){
			$this->lib('file')->mv($old,$new);
		}else{
			$this->lib('file')->cp($old,$new);
		}
		$this->lib('file')->rm($file);
		$this->success();
	}

	/**
	 * 重命名
	**/
	public function rename_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$folder = $this->_folder();
		$title = $this->get("title");
		$old = $this->get("old");
		if($old ==  $title){
			$this->error(P_Lang('新旧名称一样，不需要执行改名操作'));
		}
		$file = $this->dir_root.$folder.$old;
		if(!file_exists($file)){
			$this->error(P_Lang('文件（夹）不存在'));
		}
		$newfile = $this->dir_root.$folder.$title;
		if(file_exists($newfile)){
			$this->error(P_Lang('新文件（夹）已经存在，请重新改名'));
		}
		$this->lib('file')->mv($file,$newfile);
		$this->success();
	}

	public function save_f()
	{
		if(!$this->session->val('admin2verify')){
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
			$this->error(P_Lang('文件无法写入，不支持在线编辑'));
		}
		$content = $this->get("content","html_js");
		$this->lib('file')->vim($content,$file);
		$this->success();
	}

	public function upload_f()
	{
		$this->config('is_ajax',true);
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"] || !$this->popedom['add']){
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
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"] || !$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('未指定目录'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		if($folder == '/'){
			$file = $this->dir_root.$title;
			$dir_root = $this->dir_root;
		}else{
			if(substr($folder,0,1) == '/'){
				$folder = substr($folder,1);
			}
			if(substr($folder,-1) != '/'){
				$folder .= "/";
			}
			$file = $this->dir_root.$folder.$title;
			$dir_root = $this->dir_root.$folder;
		}
		if(!file_exists($file)){
			$this->error(P_Lang('文件不存在'));
		}
		$tmp = strtolower(substr($file,-4));
		if($tmp != '.zip'){
			$this->error(P_Lang('非ZIP文件不支持在线解压'));
		}
		
		$this->lib('phpzip')->unzip($file,$dir_root);
		$this->success();
	}

	public function zip_f()
	{
		if(!$this->session->val('admin2verify')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
		if(!$this->popedom["edit"] || !$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('未指定目录'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		if($folder == '/'){
			$file = $this->dir_root.$title;
			$dir_root = $this->dir_root;
		}else{
			if(substr($folder,0,1) == '/'){
				$folder = substr($folder,1);
			}
			if(substr($folder,-1) != '/'){
				$folder .= "/";
			}
			$file = $this->dir_root.$folder.$title;
			$dir_root = $this->dir_root.$folder;
		}
		if(!file_exists($file)){
			$this->error(P_Lang('文件不存在'));
		}
		$tmp = explode(".",$title);
		$tmpname = $tmp[0];
		//接下来开始压缩
		$zipname = $tmpname.'_'.date("YmdHis",$this->time).'.zip';
		$this->lib('phpzip')->set_root($dir_root);
		$this->lib('phpzip')->zip($file,$dir_root.$zipname);
		$this->success();
	}


	private function _folder()
	{
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		if(substr($folder,-1) != '/'){
			$folder .= "/";
		}
		return $folder;
	}
}

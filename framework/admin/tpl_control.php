<?php
/**
 * 模板控制器
 * @package phpok\admin
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年06月29日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tpl_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("tpl");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 模板方案
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('tpl')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("tpl_index");
	}

	/**
	 * 添加或修改风格信息
	**/
	public function set_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('tpl'));
		}
		$id = $this->get("id","int");
		if($id){
			$rs = $this->model('tpl')->get_one($id);
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}
		$this->view("tpl_set");
	}

	/**
	 * 保存模板信息
	**/
	public function save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('tpl'));
		}
		$id = $this->get("id","int");
		$error_url = $this->url("tpl","set");
		if($id) $error_url .= '&id='.$id;
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('名称不能为空'),$error_url);
		}
		$folder = $this->get("folder");
		if(!$folder){
			$this->error(P_Lang('文件夹目录名不能为空'),$error_url);
		}
		$ext = $this->get("ext");
		if(!$ext){
			$this->error(P_Lang('后缀不允许为空'),$error_url);
		}
		$array = array("title"=>$title,"folder"=>$folder,"ext"=>$ext);
		$array["folder_change"] = $this->get("folder_change");
		$array["author"] = $this->get("author");
		$array['phpfolder'] = $this->get('phpfolder');
		$array["refresh_auto"] = $this->get("refresh_auto","checkbox");
		$array["refresh"] = $this->get("refresh","checkbox");
		$this->model('tpl')->save($array,$id);
		$this->success(P_Lang('风格方案配置成功'),$this->url("tpl"));
	}

	/**
	 * 通过Ajax删除风格方案配置
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('tpl')->delete($id);
		$this->success();
	}

	/**
	 * 查看文件列表
	**/
	public function list_f()
	{
		if(!$this->popedom["filelist"]){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('tpl'));
		}
		$id = $this->get("id","int");
		if(!$id) error(P_Lang('未指定风格ID'),$this->url("tpl"));
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs) error(P_Lang('风格信息不存在'),$this->url("tpl"));
		if(!$rs["folder"] || !file_exists($this->dir_root."tpl/".$rs["folder"])){
			$this->error(P_Lang('风格目录不存在，或未指定风格目录，请检查'),$this->url("tpl"));
		}
		$this->assign("rs",$rs);
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$tmplist = explode("/",$folder);
		$leadlist = array();
		$leadurl = $this->url("tpl","list","id=".$id);
		if(substr($folder,-1) != "/"){
			$folder .= "/";
		}
		$this->assign("folder",$folder);
		//绑定目录
		$tpl_dir = $this->dir_root."tpl/".$rs["folder"].$folder;
		$tpl_list = $this->lib('file')->ls($tpl_dir);
		$ext_length = strlen($rs["ext"]);
		if(!$tpl_list){
			$tpl_list = array();
		}
		$myurl = $this->url("tpl","list","id=".$id);
		$rslist = $dirlist = array();
		$rs_i = $dir_i = 0;
		$edit_array = array("html","php","js","css","asp","jsp","tpl","dwt","aspx","htm","txt","xml");
		$pic_array = array("gif","png","jpeg","jpg");
		$this->assign("edit_array",$edit_array);
		$this->assign("pic_array",$pic_array);
		foreach($tpl_list as $key=>$value){
			$bname = basename($value);
			$type = is_dir($value) ? "dir" : "file";
			if(is_dir($value)){
				$url = $this->url("tpl","list","id=".$id."&folder=".rawurlencode($folder.$bname."/"));
				$dirlist[] = array("filename"=>$value,"title"=>$bname,"data"=>"","type"=>"dir","url"=>$url);
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
				$tmp = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>$type);
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
		$this->assign("id",$id);
		$this->view("tpl_list");
	}

	/**
	 * 文件夹改名
	**/
	public function rename_f()
	{
		if(!$this->popedom["filelist"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定风格ID'));
		}
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('风格信息不存在'));
		}
		if(!$rs["folder"]){
			$this->error(P_Lang('未设置风格文件夹'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		$old = $this->get("old");
		if($old ==  $title){
			$this->error(P_Lang('新旧名称一样，不需要执行改名操作'));
		}
		$file = $this->dir_root."tpl/".$rs["folder"].$folder.$old;
		if(!file_exists($file)){
			$this->error(P_Lang('文件（夹）不存在'));
		}
		$newfile = $this->dir_root."tpl/".$rs["folder"].$folder.$title;
		if(file_exists($newfile)){
			$this->error(P_Lang('新文件（夹）已经存在，请重新改名'));
		}
		$this->lib('file')->mv($file,$newfile);
		$this->success();
	}

	/**
	 * 创建文件（夹）
	**/
	public function create_f()
	{
		if(!$this->popedom["filelist"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定风格ID'));
		}
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('风格信息不存在'));
		}
		if(!$rs["folder"]){
			$this->error(P_Lang('未设置风格文件夹'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		$type = $this->get("type");
		if(!$type){
			$type = "file";
		}
		$file = $this->dir_root."tpl/".$rs["folder"].$folder.$title;
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
	 * 下载文件
	**/
	public function download_f()
	{
		if(!$this->popedom["filelist"]){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定风格ID'));
		}
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('风格信息不存在'));
		}
		if(!$rs["folder"]){
			$this->json(P_Lang('未设置风格文件夹'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		$file = $this->dir_root."tpl/".$rs["folder"].$folder.$title;
		if(!file_exists($file)){
			$this->json(P_Lang('文件（夹）不存在'));
		}
		$filesize = filesize($file);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($title));
		header("Content-Length: ".$filesize);
		header("Accept-Ranges: bytes");
		readfile($file);
		flush();
		ob_flush();
	}
	
	/**
	 * 删除文件（夹）
	**/
	public function delfile_f()
	{
		if(!$this->popedom["filelist"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定风格ID'));
		}
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('风格信息不存在'));
		}
		if(!$rs["folder"]){
			$this->error(P_Lang('未设置风格文件夹'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		$file = $this->dir_root."tpl/".$rs["folder"].$folder.$title;
		if(!file_exists($file)){
			$this->error(P_Lang('文件（夹）不存在'));
		}
		if(is_dir($file)){
			$this->lib('file')->rm($file,"folder");
		}else{
			$this->lib('file')->rm($file);
		}
		$this->success();
	}

	/**
	 * 内容模板编辑
	**/
	public function edit_f()
	{
		if(!$this->popedom["filelist"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定风格ID'));
		}
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('风格信息不存在'));
		}
		if(!$rs["folder"]){
			$this->error(P_Lang('未设置风格文件夹'));
		}
		$folder = $this->get("folder");
		if(!$folder) $folder = "/";
		$title = $this->get("title");
		$file = $this->dir_root."tpl/".$rs["folder"].$folder.$title;
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
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->assign("folder",$folder);
		$this->assign("title",$title);
		$this->view("tpl_edit");
	}

	/**
	 * 存储模板代码
	**/
	public function edit_save_f()
	{
		if(!$this->popedom["filelist"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('tpl')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('风格信息不存在'));
		}
		if(!$rs["folder"]){
			$this->error(P_Lang('未设置风格文件夹'));
		}
		$folder = $this->get("folder");
		if(!$folder){
			$folder = "/";
		}
		$title = $this->get("title");
		$file = $this->dir_root."tpl/".$rs["folder"].$folder.$title;
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
	
	//模板弹出选择器
	public function open_f()
	{
		$id = $this->get("id");
		if(!$id){
			$id = "tpl";
		}
		$config = $this->model('site')->get_one($this->session->val('admin_site_id'));
		if(!$config){
			$this->error(P_Lang('站点信息不存在'));
		}
		if(!$config['tpl_id']){
			$this->error(P_Lang('站点尚未设置默认风格，请先设置好'));
		}
		$tpl_id = $this->get('tpl_id','int');
		if(!$tpl_id){
			$tpl_id = $config["tpl_id"];
		}
		$rs = $this->model('tpl')->get_one($tpl_id);
		if(!$rs){
			$this->error(P_Lang('风格文件信息不存在'));
		}
		if(!$rs["ext"]){
			$rs["ext"] = "html";
		}
		$this->assign("site_rs",$config);
		$this->assign("rs",$rs);
		//绑定目录
		$tpl_dir = $this->dir_root."tpl/".$rs["folder"].'/';
		$tpl_list = $this->lib('file')->ls($tpl_dir);
		$ext_length = strlen($rs["ext"]);
		if(!$tpl_list){
			$tpl_list = array();
		}
		$myurl = $this->url("tpl","open",'tpl_id='.$tpl_id);
		$rslist = false;
		foreach($tpl_list AS $key=>$value){
			$bname = $this->lib('string')->to_utf8(basename($value));
			if(is_dir($value) || substr($bname,-$ext_length) != $rs["ext"]){
				continue;
			}
			$date = date("Y-m-d H:i:s",filemtime($value));
			$tplid = substr($bname,0,-($ext_length+1));
			$type = "html";
			$rslist[] = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>$type,"url"=>$url,"tplid"=>$tplid);
		}
		$this->assign("rslist",$rslist);
		$this->assign("id",$id);
		//取得模板风格全部列表
		$tplist = $this->model('tpl')->get_all();
		if($tplist){
			foreach($tplist as $key=>$value){
				if(!file_exists($this->dir_root.'tpl/'.$value['folder'])){
					unset($tplist[$key]);
					continue;
				}
			}
			$this->assign('tplist',$tplist);
			$this->assign('tpl_id',$tpl_id);
		}
		$this->view("tpl_open");	
	}
}
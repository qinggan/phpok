<?php
/**
 * 接口应用_管理整个平台的文件，包括修改自身，仅限系统管理员
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
class api_control extends \phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	private function _popedom()
	{
		if(!$this->session->val('admin_id')){
			$this->error(P_Lang('非管理员不能执行此操作'));
		}
		if(!$this->session->val('admin_id_checked')){
			$this->error(P_Lang('未经过二次密码确认，不能执行此操作'));
		}
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

	public function check_f()
	{
		if(!$this->session->val('admin_id')){
			$this->error(P_Lang('非管理员不能执行此操作'));
		}
		$code = $this->get('code');
		if(!$code){
			$this->error('二次密码不能为空');
		}
		$admin = $this->model('admin')->get_one($this->session->val('admin_id'));
		if(!$admin){
			$this->error('管理员不存在');
		}
		if(!$admin['status']){
			$this->error('管理员不存在或未审核');
		}
		$vcode = md5(md5($code));
		
		if($vcode != $admin['vpass']){
			$this->error('二次验证不通过，请检查');
		}
		$this->session->assign('admin_id_checked',true);
		$this->success();
	}

	public function rename_f()
	{
		$this->_popedom();
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

	/**
	 * 创建文件（夹）
	**/
	public function create_f()
	{
		$this->_popedom();
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
	 * 下载文件
	**/
	public function download_f()
	{
		$this->_popedom();
		$folder = $this->_folder();
		$title = $this->get("title");
		$file = $this->dir_root.$folder.$title;
		if(!is_file($file)){
			$this->error(P_Lang('文件不存在'));
		}
		$this->lib('file')->download($file,$title);
	}

	/**
	 * 删除文件（夹）
	**/
	public function delfile_f()
	{
		$this->_popedom();
		$folder = $this->_folder();
		$title = $this->get("title");
		$file = $this->dir_root.$folder.$title;
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
}

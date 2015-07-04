<?php
/*****************************************************************************************
	文件： {phpok}/admin/rescate_control.php
	备注： 资源归档管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年04月24日 23时05分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rescate_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('rescate');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('rescate')->get_all();
		$this->assign('rslist',$rslist);
		$this->view("rescate_index");
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('rescate'),'error');
			}
			$rs = array();
		}else{
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('rescate'),'error');
			}
			$rs = $this->model('rescate')->get_one($id);
			$this->assign('id',$id);
		}
		$rs['gdtypes'] = $rs['gdtypes'] ? explode(',',$rs['gdtypes']) : array();
		$this->assign('rs',$rs);
		$gdlist = $this->model('gd')->get_all();
		$this->assign('gdlist',$gdlist);
		$this->view('rescate_set');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['modify']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('附件分类名称不能为空'));
		}
		$root = $this->get('root');
		if(!$root){
			$this->json(P_Lang('附件存储目录不能为空'));
		}
		if($root == '/'){
			$this->json(P_Lang('不支持使用/作为根目录'));
		}
		if(!preg_match("/[a-z0-9\_\/]+/",$root)){
			$this->json(P_Lang('文件夹不符合系统要求，只支持：小写字母、数字、下划线及斜杠'));
		}
		if(substr($root,0,1) == "/"){
			$root = substr($root,1);
		}
		if(!file_exists($this->dir_root.$root)){
			$this->lib('file')->make($this->dir_root.$root);
		}
		$filetypes = $this->get('filetypes');
		if(!$filetypes){
			$this->json(P_Lang('附件类型不能为空'));
		}
		$list_filetypes = explode(",",$filetypes);
		foreach($list_filetypes as $key=>$value){
			$value = trim($value);
			if(!$value){
				unset($list_filetypes[$key]);
				continue;
			}
			if(!preg_match("/[a-z0-9\_\.]+/",$value)){
				$this->json(P_Lang('附件类型设置不正确，仅限字母，数字及英文点符号'));
			}
		}
		$filetypes = implode(",",$list_filetypes);
		$typeinfo = $this->get('typeinfo');
		if(!$typeinfo){
			$this->json(P_Lang('附件类型说明不能为空'));
		}
		$maxinfo = str_replace(array('K','M','KB','MB','GB','G'),'',get_cfg_var('upload_max_filesize')) * 1024;
		$filemax = $this->get('filemax','int');
		if(!$filemax || ($filemax && $filemax>$maxinfo)){
			$filemax = $maxinfo;
		}
		$data = array('title'=>$title,'root'=>$root,'filetypes'=>$filetypes,'typeinfo'=>$typeinfo,'filemax'=>$filemax);
		$data['folder'] = $this->get('folder');
		$data['gdall'] = $this->get('gdall','int');
		if(!$data['gdall']){
			$gdtypes = $this->get('gdtypes');
			$data['gdtypes'] = $gdtypes ? implode(',',$gdtypes) : '';
		}else{
			$data['gdtypes'] = '';
		}
		$data['ico'] = $this->get('ico','int');
		$data['is_default'] = $this->get('is_default','int');
		$this->model('rescate')->save($data,$id);
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('rescate')->get_one($id);
		if($rs['is_default']){
			$this->json(P_Lang('默认附件分类不支持删除'));
		}
		$rs = $this->model('rescate')->delete($id);
		$this->json(true);
	}
}

?>
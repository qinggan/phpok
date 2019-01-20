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
			$this->error(P_Lang('您没有权限执行此操作'));
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
				$this->error(P_Lang('您没有权限执行此操作'),$this->url('rescate'));
			}
			$rs = array();
		}else{
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'),$this->url('rescate'));
			}
			$rs = $this->model('rescate')->get_one($id);
			$this->assign('id',$id);
		}
		$rs['gdtypes'] = $rs['gdtypes'] ? explode(',',$rs['gdtypes']) : array();
		$this->assign('rs',$rs);
		$gdlist = $this->model('gd')->get_all();
		$this->assign('gdlist',$gdlist);
		//读取接口列表
		$osslist = $this->model('gateway')->all('object-storage');
		$this->assign('osslist',$osslist);
		$this->view('rescate_set');
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('附件分类名称不能为空'));
		}
		$root = $this->get('root');
		if(!$root){
			$this->error(P_Lang('附件存储目录不能为空'));
		}
		if($root == '/'){
			$this->error(P_Lang('不支持使用/作为根目录'));
		}
		if(!preg_match("/[a-z0-9\_\/\-]+/",$root)){
			$this->error(P_Lang('文件夹不符合系统要求，只支持：小写字母、数字、下划线、中划线及斜杠'));
		}
		if(substr($root,0,1) == "/"){
			$root = substr($root,1);
		}
		if(!file_exists($this->dir_root.$root)){
			$this->lib('file')->make($this->dir_root.$root);
		}
		$filetypes = $this->get('filetypes');
		if(!$filetypes){
			$this->error(P_Lang('附件类型不能为空'));
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
			$this->error(P_Lang('附件类型说明不能为空'));
		}
		$filemax = $this->get('filemax','int');
		if(!$filemax){
			$filemax = 1024 * 100;
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
		$data['etype'] = $this->get('etype','int');
		$data['compress'] = $this->get('compress','int');
		$data['upload_binary'] = $this->get('upload_binary','int');
		$this->model('rescate')->save($data,$id);
		$this->success();
	}

	/**
	 * 快速创建分类
	 * @参数 
	 * @返回 
	 * @更新时间 
	**/
	public function qcreate_f()
	{
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('附件分类名称不能为空'));
		}
		$name = $this->get('name');
		if(!$name){
			$name = $title;
		}
		$filetypes = $this->get('filetypes');
		if(!$filetypes){
			$filetypes = 'jpg,gif,png,jpeg';
		}
		$data = array('title'=>$title,'root'=>'res/','filetypes'=>$filetypes,'typeinfo'=>$name,'filemax'=>1024*100);
		$data['folder'] = 'Ym/d/';
		$data['gdall'] = 1;
		$data['gdtypes'] = '';
		$data['ico'] = 1;
		$data['is_default'] = 0;
		$id = $this->model('rescate')->save($data);
		$this->success($id);
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
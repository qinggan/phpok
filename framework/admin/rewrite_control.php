<?php
/*****************************************************************************************
	文件： {phpok}/model/admin/rewrite_control.php
	备注： Rewrite规则配置
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年02月02日 14时18分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class rewrite_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('rewrite');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('rewrite')->get_all();
		$this->assign('rslist',$rslist);
		$this->view('rewrite_index');
	}

	public function set_f()
	{
		if(!$this->popedom['set']){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get('id');
		if($id){
			$rs = $this->model('rewrite')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}
		//读取控制器
		$clist = $this->model('rewrite')->ctrl_list();
		$this->assign('clist',$clist);
		$this->view("rewrite_set");
	}

	public function getfunc_f()
	{
		$id = $this->get('id');
		if(!$id){
			$list = array('index'=>"Index");
			$this->json($list,true);
		}
		$list = explode("|",$id);
		$rslist = array();
		foreach($list as $key=>$value){
			$value = trim($value);
			$tmp = $this->model('rewrite')->get_func($value);
			if($tmp && is_array($tmp)){
				$rslist = array_merge($rslist,$tmp);
			}
		}
		$this->json($rslist,true);
	}

	public function save_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		$data = array();
		if($id){
			$data['id'] = $id;
		}
		$data['title'] = $this->get('title');
		if(!$data['title']){
			$this->json(P_Lang('主题不能为空'));
		}
		$data['rule'] = $this->get('rule','html');
		if(!$data['rule']){
			$this->json(P_Lang('规则不能为空'));
		}
		$data['val'] = $this->get('val');
		if(!$data['val']){
			$this->json(P_Lang('目标网址不能为空'));
		}
		$data['format'] = $this->get('format');
		if(!$data['format']){
			$this->json(P_Lang('格式化方法不能为空'));
		}
		$data['ctrl'] = $this->get('ctrl');
		if(!$data['ctrl']){
			$this->json(P_Lang('控制器不能为空'));
		}
		$data['func'] = $this->get('func');
		$data['var'] = $this->get('var');
		$data['sort'] = $this->get('sort','int');
		$this->model('rewrite')->save($data,$id);
		$this->json(true);
	}

	public function taxis_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$sort = $this->get('sort','int');
		$this->model('rewrite')->update_taxis($id,$sort);
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('rewrite')->delete($id);
		$this->json(true);
	}

	public function copy_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('rewrite')->get_one($id);
		$rs['id'] = md5(serialize($rs));
		$this->model('rewrite')->save($rs,'',false);
		$this->success();
	}
}


?>
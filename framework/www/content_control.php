<?php
/***********************************************************
	Filename: {phpok}/www/content_control.php
	Note	: 内容信息
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-27 11:24
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class content_control extends phpok_control
{
	private $user_groupid;
	function __construct()
	{
		parent::control();
		$this->model('popedom')->siteid($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($_SESSION['user_id']);
		if(!$groupid){
			error(P_Lang('无法获取前端用户组信息，请检查'),'','error');
		}
		$this->user_groupid = $groupid;
	}

	//内容首页
	function index_f()
	{
		$id = $this->get("id");
		if(!$id) error("操作异常！","","error");
		$dt = array('site'=>$this->site['id']);
		if(intval($id) && $id == intval($id)){
			$dt['title_id'] = $id;
		}else{
			$dt['phpok'] = $id;
		}
		$page = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$pageid = $this->get($page,'int');
		if(!$pageid){
			$pageid = 1;
		}
		$dt['pageid'] = $pageid;
		$this->assign('pageid',$pageid);
		$rs = $this->call->phpok('_arc',$dt);
		if(!$rs){
			error(P_Lang('内容不存在'),$this->url,'notice',5);
		}
		$project_rs = $this->call->phpok('_project',array('pid'=>$rs['project_id']));
		if(!$this->model('popedom')->check($project_rs['id'],$this->user_groupid,'read')){
			error(P_Lang('您没有阅读权限，请联系网站管理员'),'','error');
		}
		$this->assign("page_rs",$project_rs);
		$tpl = $project_rs['tpl_content'];
		//父级项目信息
		if($project_rs['parent_id']){
			$pt['pid'] = $project_rs['parent_id'];
			$parent_rs = $this->call->phpok("_project",$pt);
			if(!$parent_rs || !$parent_rs['status']){
				error(P_Lang('父级项目未启用'),$this->url,'notice',10);
			}
			$this->assign("parent_rs",$parent_rs);
			$this->phpok_seo($parent_rs);
		}
		$this->phpok_seo($project_rs);
		if($rs['cate_id']){
			$cate_root = $project_rs['cate'];
			$cate_root_rs = $this->call->phpok('_cate',array('pid'=>$project_rs['id'],'cateid'=>$cate_root));
			if(!$cate_root_rs){
				error(P_Lang('根分类信息不存在'),$this->url,'notice',5);
			}
			if(!$cate_root_rs['status']){
				error(P_Lang('根分类未启用'),$this->url,'notice',10);
			}
			$this->assign('cate_root_rs',$cate_root_rs);
			$this->phpok_seo($cate_root_rs);
			if($cate_root_rs['tpl_content']){
				$tpl = $cate_root_rs['tpl_content'];
			}
			//分类信息
			$cate_rs = $this->call->phpok('_cate',array("pid"=>$project_rs['project_id'],'cateid'=>$rs['cate_id']));
			if(!$cate_rs){
				error(P_Lang('分类信息不存在'),$this->url,'notice',5);
			}
			if(!$cate_rs['status']){
				error(P_Lang('分类未启用'),$this->url,'notice',10);
			}
			if($cate_rs['parent_id']){
				$cate_parent_rs = $this->call->phpok('_cate',array('pid'=>$project_rs['id'],'cateid'=>$cate_rs['parent_id']));
				if(!$cate_parent_rs){
					error(P_Lang('父级分类信息不存在'),$this->url,'notice',5);
				}
				if(!$cate_root_rs['status']){
					error(P_Lang('父级分类未启用'),$this->url,'notice',10);
				}
				$this->assign('cate_parent_rs',$cate_parent_rs);
				$this->phpok_seo($cate_parent_rs);
				if($cate_parent_rs['tpl_content']){
					$tpl = $cate_parent_rs['tpl_content'];
				}
			}
			$this->assign("cate_rs",$cate_rs);
			$this->phpok_seo($cate_rs);
			if($cate_rs['tpl_content']){
				$tpl = $cate_rs['tpl_content'];
			}
		}
		if($rs['tpl']){
			$tpl = $rs['tpl'];
		}
		if(!$tpl){
			$tpl = $project_rs['identifier'].'_content';
		}
		if(!$this->tpl->check_exists($tpl)){
			error(P_Lang('未配置模板，请检查'),'','error');
		}
		$this->db->cache_close();
		$this->model('list')->add_hits($rs["id"]);
		$rs['hits'] = $this->model('list')->get_hits($rs['id']);
		$this->db->cache_open();
		$this->phpok_seo($rs);
		$this->assign("rs",$rs);
		$this->view($tpl);
	}
}
?>
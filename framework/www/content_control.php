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
		if(!$groupid)
		{
			error(P_Lang('无法获取前端用户组信息，请检查'),'','error');
		}
		$this->user_groupid = $groupid;
	}

	//内容首页
	function index_f()
	{
		$id = $this->get("id");
		if(!$id) error("操作异常！","","error");
		$dt = array('site_id'=>$this->site['id']);
		if(intval($id) && $id == intval($id))
		{
			$dt['id'] = $id;
		}
		else
		{
			$dt['phpok'] = $id;
		}
		$dt['site_id'] = $this->site['id'];
		$page = $this->config['pageid'] ? $this->config['pageid'] : 'pageid';
		$pageid = $this->get($page,'int');
		if(!$pageid) $pageid = 1;
		$dt['pageid'] = $pageid;
		$this->assign('pageid',$pageid);
		$rs = $this->call->phpok('_arc',$dt);
		if(!$rs)
		{
			error("内容不存在",$this->url,'notice',5);
		}
		$project_rs = $this->call->phpok('_project',array('pid'=>$rs['project_id']));
		if(!$this->model('popedom')->check($project_rs['id'],$this->user_groupid,'read'))
		{
			error(P_Lang('您没有阅读权限，请联系网站管理员'),'','error');
		}
		$this->assign("page_rs",$project_rs);
		//父级项目信息
		if($project_rs['parent_id'])
		{
			$pt['pid'] = $project_rs['parent_id'];
			$parent_rs = $this->call->phpok("_project",$pt);
			if(!$parent_rs || !$parent_rs['status'])
			{
				error("父级项目未启用",$this->url,'notice',10);
			}
			$this->assign("parent_rs",$parent_rs);
		}
		//如果存在分类
		if($rs['cate_id'])
		{
			$cate_rs = $this->call->phpok('_cate',array("pid"=>$rs['project_id'],'cateid'=>$rs['cate_id']));
			$this->assign("cate_rs",$cate_rs);
			//父级分类
			if($cate_rs['parent_id'] && $cate_rs['parent_id'] != $project_rs['cate'])
			{
				$dt = array('site_id'=>$rs['site_id'],'pid'=>$project_rs['id'],'cateid'=>$rs['parent_id'],'cate_ext'=>1);
				$cate_parent_rs = $this->call->phpok("_cate",$dt);
				$this->assign("cate_parent_rs",$cate_parent_rs);
			}
		}
		
		//获取模板配置
		$tpl = $rs['tpl'];
		if(!$tpl && $cate_rs['tpl_content']) $tpl = $cate_rs['tpl_content'];
		if(!$tpl && $cate_parent_rs['tpl_content']) $tpl = $cate_parent_rs['tpl_content'];
		if(!$tpl && $project_rs['tpl_content']) $tpl = $project_rs['tpl_content'];
		if(!$tpl && $parent_rs['tpl_content']) $tpl = $parent_rs['tpl_content'];
		if(!$tpl) $tpl = $project_rs['identifier'].'_content';
		if(!$this->tpl->check_exists($tpl))
		{
			error('未配置模板：'.$tpl.'，请配置相应的内容显示模板','','error');
		}
		//关闭缓存
		$this->db->cache_close();
		//增加点击
		$this->model('list')->add_hits($rs["id"]);
		$rs['hits'] = $this->model('list')->get_hits($rs['id']);
		//开启缓存
		$this->db->cache_open();
		//执行SEO优化
		$this->phpok_seo($rs);
		unset($rs['project_id']);
		$rs['project_id'] = $project_rs['id'];
		if($rs['cate_id'] && $cate_rs)
		{
			unset($rs['cate_id']);
			$rs['cate_id'] = $cate_rs['id'];
		}
		$this->assign("rs",$rs);
		$this->view($tpl);
	}
}
?>
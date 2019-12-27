<?php
/**
 * 导航菜单管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年11月20日
**/

class menu_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom('menu');
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		$menulist = $this->model('menu')->group();
		$this->assign('menulist',$menulist);
		$id = $this->get('id');
		if(!$id){
			$id = 'top';
		}
		$this->assign('id',$id);
		$rslist = $this->model('menu')->get_all($id);
		$this->assign('rslist',$rslist);
		$this->view('menu_index');
	}

	public function group_f()
	{
		if(!$this->popedom['group']){
			$this->error(P_Lang('您没有权限执行导航组编辑操作'));
		}
		$rslist = $this->model('menu')->group();
		$this->assign('rslist',$rslist);
		$this->view('menu_group');
	}

	public function group_save_f()
	{
		if(!$this->popedom['group']){
			$this->error(P_Lang('您没有权限执行导航组编辑操作'));
		}
		$rslist = $this->model('menu')->group();
		$name = $this->get('nameid');
		if($rslist){
			foreach($rslist as $key=>$value){
				if($name[$key]){
					$rslist[$key] = $name[$key];
				}
			}
		}
		$keyid = $this->get('keyid_add');
		$keyid2 = $this->get('keyid_add','system');
		if($keyid && $keyid != $keyid2){
			$this->error(P_Lang('标识符仅限字母，数字及下划线，且必须字母开头'));
		}
		$nameid = $this->get('nameid_add');
		if($keyid && $rslist[$keyid]){
			$this->error(P_Lang('标识已被使用'));
		}
		if($keyid && $nameid){
			$rslist[$keyid] = $nameid;
		}
		$this->model('menu')->group($rslist);
		$this->success();
	}

	public function group_delete_f()
	{
		if(!$this->popedom['group']){
			$this->error(P_Lang('您没有权限执行导航组删除操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('menu')->group_delete($id);
		$this->success();
	}

	/**
	 * 状态设置
	**/
	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('menu')->get_one($id);
		$content = $rs['status'] ? 0 : 1;
		$this->model('menu')->set_status($id,$content);
		$this->success($content);
	}

	public function taxis_f()
	{
		if(!$this->popedom['modify']){
			$this->error(P_Lang('您没有权限执行导航修改操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$taxis = $this->get('taxis','int');
		$data = array('taxis'=>$taxis);
		$this->model('menu')->save($data,$id);
		$this->success();
	}

	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行导航修改操作'));
			}
			$rs = $this->model('menu')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
			$this->assign('group_id',$rs['group_id']);
			$this->assign('parent_id',$rs['parent_id']);
			if($rs['type'] == 'cate' && $rs['project_id'] && $rs['cate_id']){
				$project = $this->model('project')->get_one($rs['project_id']);
				if($project){
					$catelist = $this->model('cate')->get_all($project['site_id'],false,$project['cate']);
					$this->assign('catelist',$catelist);
				}
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行导航添加操作'));
			}
			$group_id = $this->get('group_id');
			$parent_id = $this->get('parent_id','int');
			if(!$group_id){
				$this->error(P_Lang('未指定分组'));
			}
			$this->assign('group_id',$group_id);
			$this->assign('parent_id',$parent_id);
			$taxis = $this->model('menu')->get_next_taxis($group_id,$parent_id);
			$rs = array('taxis'=>$taxis,'status'=>1);
			$this->assign('rs',$rs);
		}
		$plist = $this->model('project')->project_all($this->session->val('admin_site_id'));
		$this->assign('plist',$plist);
		$this->view('menu_set');
	}

	public function catelist_f()
	{
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定项目ID'));
		}
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('项目信息不存在'));
		}
		if(!$project['cate']){
			$this->error(P_Lang('未绑定分类'));
		}
		$catelist = $this->model('cate')->get_all($this->session->val('admin_site_id'),false,$project['cate']);
		if(!$catelist){
			$this->error(P_Lang('暂无分类信息'));
		}
		$this->success($catelist);
	}

	public function titles_f()
	{
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定项目'));
		}
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('项目未绑定模块，不支持主题功能'));
		}
		$this->assign('pid',$pid);
		$this->assign('project',$project);
		$condition = "l.project_id='".$project['id']."' AND l.site_id='".$project['site_id']."'";
		$keywords = $this->get("keywords");
		$pageurl = $this->url('menu','titles','pid='.$pid);
		if($keywords){
			$condition .= " AND (l.title LIKE '%".$keywords."%' OR l.tag LIKE '%".$keywords."%' OR l.seo_keywords LIKE '%".$keywords."%' OR l.seo_desc LIKE '%".$keywords."%' OR l.seo_title LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$total = $this->model('list')->get_all_total($condition);
		if($total){
			$this->assign('total',$total);
			$pageid = $this->get($this->config['pageid'],'int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('list')->get_all($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			if($total>$psize){
				$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
				$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
			$this->view('menu_titles');
		}
	}

	public function save_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行导航修改操作'));
			}
			$rs = $this->model('menu')->get_one($id);
			$group_id = $rs['group_id'];
			$parent_id = $rs['parent_id'];
			$site_id = $rs['site_id'];
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行导航添加操作'));
			}
			$group_id = $this->get('group_id');
			$parent_id = $this->get('parent_id','int');
			if(!$group_id){
				$this->error(P_Lang('未指定分组'));
			}
			$site_id = $this->session->val('admin_site_id');
		}
		$data = array('site_id'=>$site_id,'group_id'=>$group_id,'parent_id'=>$parent_id);
		$title = $this->get('title');
		$type = $this->get('type');
		if($type == 'project'){
			$pid = $this->get('pid-project','int');
			if(!$pid){
				$this->error(P_Lang('未指定项目'));
			}
			$project = $this->model('project')->get_one($pid);
			if(!$project){
				$this->error(P_Lang('项目不存在'));
			}
			if(!$title){
				$title = $project['title'];
			}
			$data['project_id'] = $project['id'];
		}elseif($type == 'cate'){
			$pid = $this->get('pid-cate','int');
			if(!$pid){
				$this->error(P_Lang('未指定项目'));
			}
			$project = $this->model('project')->get_one($pid);
			if(!$project){
				$this->error(P_Lang('项目不存在'));
			}
			$cate_id = $this->get('cate_id');
			if(!$cate_id){
				$this->error(P_Lang('未指定分类'));
			}
			$cate = $this->model('cate')->get_one($cate_id);
			if(!$cate){
				$this->error(P_Lang('分类信息不存在'));
			}
			if(!$title){
				$title = $cate['title'];
			}
			$data['project_id'] = $project['id'];
			$data['cate_id'] = $cate['id'];
		}elseif($type == 'content'){
			$pid = $this->get('pid-content','int');
			if(!$pid){
				$this->error(P_Lang('未指定项目'));
			}
			$project = $this->model('project')->get_one($pid);
			if(!$project){
				$this->error(P_Lang('项目不存在'));
			}
			$data['project_id'] = $project['id'];
			$list_id = $this->get('list_id','int');
			if(!$list_id){
				$this->error(P_Lang('未绑定主题'));
			}
			$msg = $this->model('list')->simple_one($list_id);
			if(!$msg){
				$this->error(P_Lang('主题信息不存在'));
			}
			if(!$title){
				$title = $msg['title'];
			}
			$data['list_id'] = $list_id;
		}else{
			$link = $this->get('link');
			if(!$title){
				$this->error(P_Lang('标题不能为空'));
			}
			$data['link'] = $link;
		}
		$data['title'] = $title;
		$data['type'] = $type;
		$data['target'] = $this->get('target','int');
		$data['is_userid'] = $this->get('is_userid','int');
		$data['status'] = $this->get('status','int');
		$data['taxis'] = $this->get('taxis','int');
		$this->model('menu')->save($data,$id);
		$this->success();
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行导航删除操作'));
		}
		$chk = $this->model('menu')->check_parent($id);
		if($chk){
			$this->error(P_Lang('存在子菜单，请先删除子菜单'));
		}
		$this->model('menu')->del($id);
		$this->success();
	}
}

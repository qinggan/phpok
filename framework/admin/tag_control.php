<?php
/**
 * Tag标签管理工具
 * @package phpok
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年04月20日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class tag_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("tag");
		$this->assign("popedom",$this->popedom);
		$this->model('tag')->site_id($_SESSION['admin_site_id']);
	}

	/**
	 * 标签管理列表
	 * @参数 keywords 搜索关键字
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pageurl = $this->url('tag');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
			$this->assign('keywords',$keywords);
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0){
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_index');
	}

	/**
	 * 添加或编辑标签
	**/
	public function set_f()
	{
		$id = $this->get('id','int');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('tag')->get_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$this->view("tag_set");
	}

	/**
	 * 保存标签数据
	**/
	public function save_f()
	{
		$id = $this->get('id','int');
		$popedom = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('关键字名称不能为空'));
		}
		$chk = $this->model('tag')->chk_title($title,$id);
		if($chk){
			$this->error(P_Lang('关键字已存在，请检查'));
		}
		$old = '';
		if($id){
			$rs = $this->model('tag')->get_one($id);
			$old = $rs['title'];
		}
		$data = array('title'=>$title,'url'=>$this->get('url'),'target'=>$this->get('target','int'));
		$identifier = $this->get('identifier');
		if($identifier){
			$chk = $this->model('tag')->get_one($identifier,'identifier',$this->session->val('admin_id'));
			if(!$id && $chk){
				$this->error(P_Lang('标识已被使用'));
			}
			if($id && $chk && $chk['id'] != $id){
				$this->error(P_Lang('标识已被使用'));
			}
			$data['identifier'] = $identifier;
		}
		$data['site_id'] = $this->session->val('admin_site_id');
		$data['alt'] = $this->get('alt');
		$data['is_global'] = $this->get('is_global','int');
		$data['replace_count'] = $this->get('replace_count','int');
		$data['seo_title'] = $this->get('seo_title');
		$data['seo_keywords'] = $this->get('seo_keywords');
		$data['seo_desc'] = $this->get('seo_desc');
		$data['tpl'] = $this->get('tpl');
		if($id){
			$this->model('tag')->save($data,$id);
			if($old && $old != $title){
				$this->model('tag')->update_tag_title($old,$title,$id);
			}
			$this->success();
		}
		$insert_id = $this->model('tag')->save($data);
		if(!$insert_id){
			$this->error(P_Lang('添加失败，请检查'));
		}
		$this->success();
	}

	/**
	 * 删除标签
	 * @参数 id 要删除的标签对应的ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('tag')->delete($id);
		$this->success();
	}

	/**
	 * 配置Tag标签管理参数
	**/
	public function config_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('tag')->config();
		if($rs){
			$this->assign('rs',$rs);
		}
		$this->view("tag_config");
	}

	/**
	 * 存储配置信息
	**/
	public function config_save_f()
	{
		if(!$this->session->val('admin_rs.if_system')){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$data = array();
		$data['separator'] = $this->get('separator');
		if(!$data['separator']){
			$data['separator'] = '|';
		}
		$data['count'] = $this->get('count','int');
		$data['psize'] = $this->get('psize','int');
		$data['urlformat'] = $this->get('urlformat');
		$this->model('tag')->config($data);
		$this->success();
	}

	/**
	 * 弹出窗选择
	**/
	public function open_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('tag')->config();
		if($rs){
			$this->assign('rs',$rs);
		}
		$pageurl = $this->url('tag','open');
		$keywords = $this->get('keywords');
		$condition = "1=1";
		if($keywords){
			$condition .= " AND title LIKE '%".$keywords."%' ";
			$pageurl .= "&title=".rawurlencode($keywords);
		}
		$psize = 80;
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid - 1) * $psize;
		$total = $this->model('tag')->get_total($condition);
		if($total>0){
			$rslist = $this->model('tag')->get_list($condition,$offset,$psize);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_open');
	}

	/**
	 * 查看标签下关联的主题，可以利用此项删除已失效的主题
	 * @参数 $id 标签ID
	**/
	public function list_f()
	{
		$this->db->debug(true);
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('tag')->get_one($id,'id');
		if(!$rs){
			$this->error(P_Lang('标签信息不存在'));
		}
		$this->assign('rs',$rs);
		$total = $this->model('tag')->tag_total($rs['id']);
		if($total){
			$pageid = $this->get('pageid','int');
			if(!$pageid){
				$pageid = 1;
			}
			$psize = $this->config['psize'] ? $this->config['psize'] : 30;
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('tag')->id_list($rs['id'],$offset,$psize);
			if($rslist){
				$tmp_idlist = array();
				foreach($rslist as $key=>$value){
					$tmp_idlist[] = $value['id'];
				}
				$tmp_idlist = array_unique($tmp_idlist);
				$tmplist = $this->id2title($tmp_idlist);
				if($tmplist){
					foreach($rslist as $key=>$value){
						if($tmplist[$value['id']]){
							$value = array_merge($tmplist[$value['id']],$value);
							$rslist[$key] = $value;
						}
					}
				}
				$this->assign("rslist",$rslist);
			}
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pageurl = $this->url('tag','list','id='.$rs['id']);
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_list');
	}

	public function del_stat_f()
	{
		$tag_id = $this->get('tag_id','int');
		$title_id = $this->get('title_id');
		if(!$tag_id || !$title_id){
			$this->error(P_Lang('异常，未指定标签ID或主题ID'));
		}
		$list = explode(",",$title_id);
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$this->model('tag')->delete_title($tag_id,$value);
		}
		$this->success();
	}

	public function titles_f()
	{
		$tag_id = $this->get('tag_id');
		if(!$tag_id){
			$this->error(P_Lang('未指定标签ID'));
		}
		$rs = $this->model('tag')->get_one($tag_id,'id');
		if(!$rs){
			$this->error(P_Lang('标签不存在'));
		}
		$this->assign('rs',$rs);
		$condition  = "l.id NOT IN(SELECT title_id FROM ".$this->db->prefix."tag_stat WHERE tag_id='".$rs['id']."')";
		$condition .= " AND l.project_id IN(SELECT id FROM ".$this->db->prefix."project WHERE site_id='".$this->session->val('admin_site_id')."' AND status=1 AND is_tag=1) ";
		$total = $this->model('list')->all_total($condition);
		if(!$total){
			$this->error(P_Lang('没有未关联的主题'));
		}
		$pageurl = $this->url('tag','titles','tag_id='.$tag_id);
		$pageid  = $this->get($this->config['pageid'],'int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$rslist = $this->model('list')->all_list($condition,$offset,$psize);
		if($rslist){
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=2';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("rslist",$rslist);
			$this->assign('pagelist',$pagelist);
		}
		$this->view('tag_title');
	}

	public function cates_f()
	{
		$tag_id = $this->get('tag_id');
		if(!$tag_id){
			$this->error(P_Lang('未指定标签ID'));
		}
		$rs = $this->model('tag')->get_one($tag_id,'id');
		if(!$rs){
			$this->error(P_Lang('标签不存在'));
		}
		$this->assign('rs',$rs);
		$parentlist = $this->model('cate')->get_all($this->session->val('admin_site_id'));
		$parentlist = $this->model('cate')->cate_option_list($parentlist);
		if(!$parentlist){
			$this->error(P_Lang('无分类信息'));
		}
		$idlist = $this->model('tag')->id_list($rs['id'],0,999,"title_id LIKE 'c%'");
		if($idlist){
			$tmp = array();
			foreach($idlist as $key=>$value){
				$tmp[] = substr($value['id'],1);
			}
			foreach($parentlist as $key=>$value){
				if(in_array($value['id'],$tmp)){
					unset($parentlist[$key]);
					continue;
				}
			}
		}
		$this->assign('rslist',$parentlist);
		$this->view('tag_cate');
	}

	public function projects_f()
	{
		$tag_id = $this->get('tag_id');
		if(!$tag_id){
			$this->error(P_Lang('未指定标签ID'));
		}
		$rs = $this->model('tag')->get_one($tag_id,'id');
		if(!$rs){
			$this->error(P_Lang('标签不存在'));
		}
		$this->assign('rs',$rs);
		$project_list = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
		if(!$project_list){
			$this->error(P_Lang('无项目信息'));
		}
		$idlist = $this->model('tag')->id_list($rs['id'],0,999,"title_id LIKE 'p%'");
		if($idlist){
			$tmp = array();
			foreach($idlist as $key=>$value){
				$tmp[] = substr($value['id'],1);
			}
			foreach($parentlist as $key=>$value){
				if(in_array($value['id'],$tmp)){
					unset($project_list[$key]);
					continue;
				}
			}
		}
		$this->assign('rslist',$project_list);
		$this->view('tag_project');
	}

	public function add_it_f()
	{
		$tag_id = $this->get('tag_id','int');
		$title_id = $this->get('title_id');
		if(!$tag_id || !$title_id){
			$this->error(P_Lang('异常，未指定标签ID或主题ID'));
		}
		$rs = $this->model('tag')->get_one($tag_id,'id');
		if(!$rs){
			$this->error(P_Lang('标签信息不存在'));
		}
		$list = explode(",",$title_id);
		foreach($list as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$this->model('tag')->add_title($tag_id,$value);
		}
		$this->success();
	}

	public function add_it_node_f()
	{
		$node_id = $this->get('node_id','int');
		$title_id = $this->get('title_id');
		if(!$node_id || !$title_id){
			$this->error(P_Lang('异常，未指定节点ID或主题ID'));
		}
		$rs = $this->model('tag')->node_one($node_id);
		if(!$rs){
			$this->error(P_Lang('节点信息不存在'));
		}
		$ids = $rs['ids'] ? $rs['ids'].','.$title_id : $title_id;
		$list = explode(",",$ids);
		$list = array_unique($list);
		$ids = implode(",",$list);
		$array = array('ids'=>$ids);
		$this->model('tag')->node_save($array,$node_id);
		$this->success();
	}

	/**
	 * 节点管理器
	**/
	public function nodelist_f()
	{
		$tag_id = $this->get('tag_id');
		if(!$tag_id){
			$this->error(P_Lang('未指定节点ID'));
		}
		$rs = $this->model('tag')->get_one($tag_id);
		if(!$rs){
			$this->error(P_Lang('标签信息不存在'));
		}
		$rs['total'] = $this->model('tag')->tag_total($tag_id);
		$this->assign('rs',$rs);
		$rslist = $this->model('tag')->node_list($tag_id);
		if($rslist){
			foreach($rslist as $key=>$value){
				if(!$value['ids']){
					continue;
				}
				$tmp = explode(",",$value['ids']);
				$tmplist = $this->id2title($tmp);
				if($tmplist){
					$value['tlist'] = $tmplist;
				}
				$rslist[$key] = $value;
			}
			$this->assign('rslist',$rslist);
			$this->assign('total',count($rslist));
		}
		$this->view('tag_nodelist');
	}

	public function node_set_f()
	{
		$id = $this->get('id');
		if($id){
			$rs = $this->model('tag')->node_one($id);
			$this->assign('rs',$rs);
			$this->assign('id',$id);
			$tag = $this->model('tag')->get_one($rs['tag_id']);
		}else{
			$tag_id = $this->get('tag_id','int');
			if(!$tag_id){
				$this->error(P_Lang('未指定标签ID'));
			}
			$tag = $this->model('tag')->get_one($tag_id);
		}
		$this->assign('tag',$tag);
		$project_list = $this->model('project')->get_all_project($this->session->val('admin_site_id'));
		$this->assign('plist',$project_list);
		$rootcate = 0;
		$parentlist = array();
		if($rs && $rs['pid']){
			$project = $this->model('project')->get_one($rs['pid'],false);
			if($project && $project['cate']){
				$rootcate = $project['cate'];
				$parentlist = $this->model('cate')->get_all($this->session->val('admin_site_id'),0,$rootcate);
			}
		}else{
			$parentlist = $this->model('cate')->get_all($this->session->val('admin_site_id'));
		}
		if($parentlist){
			$parentlist = $this->model('cate')->cate_option_list($parentlist);
			$this->assign("clist",$parentlist);
		}
		$this->view('tag_node');
	}

	public function node_save_f()
	{
		$data = array();
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('节点名称不能为空'));
		}
		$data['title'] = $title;
		$tag_id = $this->get('tag_id','int');
		if(!$tag_id){
			$this->error(P_Lang('未指定标签ID'));
		}
		$data['tag_id'] = $tag_id;
		//标识检测
		$id = $this->get('id','int');
		$identifier = $this->get('identifier','system');
		if(!$identifier){
			$this->error(P_Lang('标识不能为空或不符合系统要求'));
		}
		$check = $this->model('tag')->node_check($identifier,$tag_id,$id);
		if($check){
			$this->error(P_Lang('标识已被使用，请更换一个'));
		}
		$data['identifier'] = $identifier;
		$data['psize'] = $this->get('psize','int');
		$data['type'] = $this->get('type','int');
		$data['status'] = $this->get('status','int');
		$data['pid'] = $this->get('pid','int');
		$data['cid'] = $this->get('cid','int');
		$data['taxis'] = $this->get('taxis','int');
		$this->model('tag')->node_save($data,$id);
		$this->success();
	}

	public function node_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('tag')->node_delete($id);
		$this->success();
	}

	public function node_title_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('tag')->node_one($id);
		if(!$rs){
			$this->error(P_Lang('未指定节点'));
		}
		$this->assign('id',$id);
		$this->assign('rs',$rs);
		$tag = $this->model('tag')->get_one($rs['tag_id']);
		if(!$tag){
			$this->error(P_Lang('标签不存在'));
		}
		$this->assign('tag',$tag);
		$total = $this->model('tag')->tag_total($rs['tag_id']);
		if(!$total){
			$this->error(P_Lang('标签下未找到关联的主题，请到标签下绑定主题'));
		}
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] ? $this->config['psize'] : 30;
		$offset = ($pageid-1) * $psize;
		$ids = $rs['ids'];
		$condition = '';
		if($ids){
			$tmplist = explode(",",$ids);
			$ids = implode("','",$tmplist);
			$condition = "title_id NOT IN('".$ids."')";
		}
		$rslist = $this->model('tag')->id_list($rs['tag_id'],$offset,$psize,$condition);
		if(!$rslist){
			$this->error(P_Lang('没有可以选的主题'));
		}
		$tmp_idlist = array();
		foreach($rslist as $key=>$value){
			$tmp_idlist[] = $value['id'];
		}
		$tmp_idlist = array_unique($tmp_idlist);
		$tmplist = $this->id2title($tmp_idlist);
		if($tmplist){
			foreach($rslist as $key=>$value){
				if($tmplist[$value['id']]){
					$value = array_merge($tmplist[$value['id']],$value);
					$rslist[$key] = $value;
				}
			}
		}
		$this->assign("rslist",$rslist);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pageurl = $this->url('tag','list','id='.$rs['id']);
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign('pagelist',$pagelist);
		$this->view('tag_node_title');
	}

	public function node_delete_ids_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$node_id = $this->get('node_id');
		if(!$node_id){
			$this->error(P_Lang('未指定节点ID'));
		}
		$rs = $this->model('tag')->node_one($node_id);
		if(!$rs){
			$this->error(P_Lang('节点不存在'));
		}
		if(!$rs['ids']){
			$this->error(P_Lang('未绑定主题，请刷新'));
		}
		if($rs['ids'] == $id){
			$array = array('ids'=>'');
			$this->model('tag')->node_save($array,$node_id);
			$this->success();
		}
		$list = explode(",",$rs['ids']);
		foreach($list as $key=>$value){
			if($value == $id){
				unset($list[$key]);
				continue;
			}
		}
		$ids = implode(",",$list);
		$array = array('ids'=>$ids);
		$this->model('tag')->node_save($array,$node_id);
		$this->success();
	}

	private function id2title($list)
	{
		$rslist = array();
		foreach($list as $key=>$value){
			$data = array();
			$data['id'] = $value;
			if(substr($value,0,1) == 'p'){
				$data['type'] = P_Lang('项目');
				$tmp = $this->model('project')->get_one(substr($value,1),false);
				if($tmp){
					$data['status'] = true;
					$data['title'] = $tmp['title'];
					$data['manage'] = $this->url('project','set','id='.$tmp['id']);
				}else{
					$data['status'] = false;
					$data['title'] = P_Lang('项目已不存在，请删除');
				}
				$rslist[$value] = $data;
				continue;
			}
			if(substr($value,0,1) == 'c'){
				$data['type'] = P_Lang('分类');
				$tmp = $this->model('cate')->get_one(substr($value,1),'id',false);
				if($tmp){
					$data['status'] = true;
					$data['title'] = $tmp['title'];
					$data['manage'] = $this->url('cate','set','id='.$tmp['id']);
				}else{
					$data['status'] = false;
					$data['title'] = P_Lang('分类已不存在，请删除');
				}
				$rslist[$value] = $data;
				continue;
			}
			if(is_numeric($value)){
				$data['type'] = P_Lang('主题');
				$tmp = $this->model('list')->simple_one($value);
				if($tmp){
					$data['status'] = true;
					$data['title'] = $tmp['title'];
					$data['manage'] = $this->url('list','edit','id='.$tmp['id']);
				}else{
					$data['status'] = false;
					$data['title'] = P_Lang('主题已不存在，请删除');
				}
				$rslist[$value] = $data;
				continue;
			}
		}
		return $rslist;
	}

	public function catelist_f()
	{
		$pid = $this->get('pid','int');
		if($pid){
			$project = $this->model('project')->get_one($pid,false);
			if(!$project){
				$this->error(P_Lang('项目不存在'));
			}
			if(!$project['cate']){
				$this->success();
			}
			$rootcate = $project['cate'];
		}else{
			$rootcate = 0;
		}
		$parentlist = $this->model('cate')->get_all($this->session->val('admin_site_id'),0,$rootcate);
		$parentlist = $this->model('cate')->cate_option_list($parentlist);
		$this->success($parentlist);
	}
}
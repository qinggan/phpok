<?php
/**
 * 表单发布/修改页
 * @package phpok\www
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 4.x
 * @授权 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2017年08月28日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class post_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->model('popedom')->site_id($this->site['id']);
		$groupid = $this->model('usergroup')->group_id($this->session->val('user_id'));
		if(!$groupid){
			$this->error(P_Lang('无法获取前端用户组信息'));
		}
		$this->user_groupid = $groupid;
	}

	/**
	 * 内容发布页
	**/
	public function index_f()
	{
		if($this->session->val('user_id')){
			$me = $this->model('user')->get_one($this->session->val('user_id'));
			if(!$me){
				$this->error(P_Lang('未找到用户信息'));
			}
			if($me['status'] == '2'){
				$this->error(P_Lang('您的账号被锁定，请与管理员联系'));
			}
			if($me['status'] == '3'){
				$this->error(P_Lang('您的账号被禁言，请与管理员联系'));
			}
		}

		$id = $this->get("id");
		$pid = $this->get('pid','int');
		if(!$id && !$pid){
			$this->error(P_Lang('未指定项目'));
		}
		$project_rs = $this->call->phpok('_project',array("phpok"=>$id,'pid'=>$pid));
		if(!$project_rs || !$project_rs['module']){
			$this->error(P_Lang("项目不符合要求"));
		}
		$err_url = $project_rs['url'];
		if(!$project_rs['post_status']){
			$this->error(P_Lang('项目未启用发布功能，联系管理员启用此功能'),$err_url,10);
		}
		$project_rs['url'] = $this->url('post',$project_rs['identifier']);
		$this->assign("page_rs",$project_rs);
		$group_rs = $this->model('usergroup')->get_one($this->user_groupid);
		if(!$this->model('popedom')->check($project_rs['id'],$this->user_groupid,'post')){
			$this->error(P_Lang('您的级别（{grouptitle}）没有发布权限，请联系我们的客服',array('grouptitle'=>$group_rs['title'])),$err_url,10);
		}
		//绑定分类信息
		if($project_rs['cate']){
			$catelist = array();
			$cate_all = $this->model("cate")->cate_all($project_rs['site_id']);
			$this->model("cate")->sublist($catelist,$project_rs['cate'],$cate_all);
			$this->assign("catelist",$catelist);
		}
		$cateid = $this->get("cateid","int");
		if($cateid){
			$cate_rs = $this->call->phpok('_cate',array('pid'=>$project_rs['id'],'cateid'=>$cateid,'cate_ext'=>true));
			$this->assign("cate_rs",$cate_rs);
		}else{
			$cate = $this->get('cate');
			if($cate){
				$cate_rs = $this->call->phpok('_cate',array('pid'=>$project_rs['id'],'cate'=>$cate,'cate_ext'=>true));
				$this->assign("cate_rs",$cate_rs);
			}
		}

		//扩展字段
		$ext_list = $this->model('module')->fields_all($project_rs["module"],"identifier");
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) as $key=>$value){
			if(!$value['is_front']){
				continue;
			}
			if($value["ext"]){
				$ext = unserialize($value["ext"]);
				foreach($ext as $k=>$v){
					$value[$k] = $v;
				}
			}
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		//检测发布模板是否存在
		$vfile = array();
		if($project_rs['post_tpl']){
			$vfile[] = $project_rs['post_tpl'];
		}
		$vfile[] = 'usercp/'.$project_rs['identifier'].'-add';
		$vfile[] = 'usercp/post-add-'.$project_rs['identifier'];
		$vfile[] = 'usercp/post-add';
		$vfile[] = $project_rs['identifier'].'_post';
		$vfile[] = 'post-add';
		$vfile[] = 'post_add';
		$tpl = '';
		foreach($vfile as $key=>$value){
			if($this->tpl->check_exists($value)){
				$tpl = $value;
				break;
			}
		}
		if(!$tpl){
			$this->error(P_Lang('未配置发布模板，联系管理员进行配置'));
		}
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->lib('server')->referer();
		}
		if(!$_back){
			$_back = $this->url($project_rs['identifier'],$cate_rs['identifier']);
		}
		$this->assign('_back',$_back);
		$this->assign('is_vcode',$this->model('site')->vcode($project_rs['id'],'add'));
		$this->view($tpl);
	}

	/**
	 * 编辑主题信息
	**/
	public function edit_f()
	{
		if(!$this->session->val('user_id')){
			$this->error(P_Lang('非用户不能操作此信息'),$this->url,10);
		}
		$_back = $this->get("_back");
		if(!$_back){
			$_back = $this->lib('server')->referer();
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'),$_back);
		}
		$this->assign('id',$id);
		$rs = $this->model('content')->get_one($id,0);
		if(!$rs){
			$this->error(P_Lang('内容信息不存在'),$_back);
		}
		if($rs['user_id'] != $this->session->val('user_id')){
			$this->error(P_Lang('您没有修改此内容权限'));
		}
		//获取项目信息
		$project_rs = $this->call->phpok('_project','pid='.$rs['project_id']);
		if(!$project_rs || !$project_rs['module']){
			$this->error(P_Lang('项目不符合要求'));
		}
		$project_rs['url'] = $this->url('usercp','list','id='.$project_rs['identifier']);
		$this->assign("page_rs",$project_rs);

		//绑定分类信息
		if($project_rs['cate']){
			$catelist = array();
			$cate_all = $this->model("cate")->cate_all($project_rs['site_id']);
			$this->model("cate")->sublist($catelist,$project_rs['cate'],$cate_all);
			$this->assign("catelist",$catelist);
		}
		if($rs['cate_id']){
			$cate_rs = $this->model("cate")->get_one($rs['cate_id'],"id",$project_rs['site_id']);
			$this->assign("cate_rs",$cate_rs);
		}

		//扩展字段
		$ext_list = $this->model('module')->fields_all($project_rs["module"],"identifier");
		$extlist = array();
		foreach(($ext_list ? $ext_list : array()) AS $key=>$value){
			if(!$value['is_front']){
				continue;
			}
			$value['content'] = $rs[$value['identifier']];
			$extlist[] = $this->lib('form')->format($value);
		}
		$this->assign("extlist",$extlist);
		$this->assign('rs',$rs);
		$vfile = array();
		if($project_rs['post_tpl']){
			$vfile[] = $project_rs['post_tpl'].'_edit';
		}
		$vfile[] = 'usercp/'.$project_rs['identifier'].'-edit';
		$vfile[] = 'usercp/post-edit-'.$project_rs['identifier'];
		$vfile[] = 'usercp/post-edit';
		$vfile[] = $project_rs['identifier'].'_post_edit';
		$vfile[] = 'post-edit';
		$vfile[] = 'post_edit';
		$tpl = '';
		foreach($vfile as $key=>$value){
			if($this->tpl->check_exists($value)){
				$tpl = $value;
				break;
			}
		}
		if(!$tpl){
			$this->error(P_Lang('未配置编辑模板，联系管理员进行配置'));
		}
		$this->assign('is_vcode',$this->model('site')->vcode($project_rs['id'],'edit'));
		$this->view($tpl);
	}
}
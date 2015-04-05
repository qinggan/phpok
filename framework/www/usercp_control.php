<?php
/***********************************************************
	Filename: {phpok}/www/usercp_control.php
	Note	: 用户控制面板
	Version : 3.0
	Author  : qinggan
	Update  : 2013年07月01日 06时14分
***********************************************************/
class usercp_control extends phpok_control
{
	var $group_rs;
	var $user_rs;
	public function __construct()
	{
		parent::control();
		if(!$_SESSION["user_id"]){
			error(P_Lang('未登录会员不能执行此操作'),$this->url,"error");
		}
		$this->group_rs = $this->model('usergroup')->group_rs($_SESSION['user_id']);
		if(!$this->group_rs){
			error(P_Lang('您的账号有异常：无法获取相应的会员组信息，请联系管理员'),'',"error");
		}
	}

	//会员个人中心
	public function index_f()
	{
		$rs = $this->model("user")->get_one($_SESSION['user_id']);
		$this->assign('rs',$rs);
		$this->view('usercp');
	}

	//修改个人资料
	public function info_f()
	{
		$rs = $this->model("user")->get_one($_SESSION['user_id']);
		$group_rs = $this->group_rs;
		//读取扩展属性
		$condition = 'is_edit=1';
		if($group_rs['fields']){
			$tmp = explode(",",$group_rs['fields']);
			$condition .= " AND identifier IN('".(implode("','",$tmp))."')";
		}
		$ext_list = $this->model('user')->fields_all($condition,"id");
		if($ext_list){
			$tmp_f = $group_rs['fields'] ? explode(",",$group_rs['fields']) : 'all';
			$extlist = array();
			foreach($ext_list as $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v){
						$value[$k] = $v;
					}
				}
				$idlist[] = strtolower($value["identifier"]);
				if($rs[$value["identifier"]]){
					$value["content"] = $rs[$value["identifier"]];
				}
				if($tmp_f == 'all' || (is_array($tmp_f) && in_array($value['identifier'],$tmp_f))){
					$extlist[] = $this->lib('form')->format($value);
				}
			}
			$this->assign("extlist",$extlist);
		}
		$this->assign("rs",$rs);
		$this->assign("group_rs",$group_rs);
		$this->view("usercp_info");
	}

	//修改密码
	public function passwd_f()
	{
		$this->view("usercp_passwd");
	}

	//获取项目列表
	public function list_f()
	{
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定项目'),$this->url('usercp'),'notice',10);
		}
		$pid = $this->model('id')->project_id($id,$this->site['id']);
		if(!$pid){
			error('项目信息不存在',$this->url('usercp'),'error');
		}
		//判断是否有这个权限
		if(!$this->model('popedom')->check($pid,$this->user_groupid,'post')){
			error(P_Lang('您没有这个权限功能，请联系网站管理员'),$this->url('usercp'),'error');
		}
		$project_rs = $this->model('project')->get_one($pid);
		if(!$project_rs || !$project_rs['status'])
		{
			error('项目不存在或未启用',$this->url('usercp'),'error');
		}
		$tplfile = 'usercp_'.$id;
		$tplfile.= $project_rs['module'] ? '_list' : '_page';
		//非列表项目直接指定
		$this->assign("page_rs",$project_rs);
		if(!$project_rs['module'])
		{
			$this->view($tplfile);
			exit;
		}
		$dt = array('pid'=>$project_rs['id'],'user_id'=>$_SESSION['user_id']);
		//读取符合要求的内容
		$pageurl = $this->url('usercp','list','id='.$id);
		$pageid = $this->get($this->config['pageid'],'int');
		if(!$pageid) $pageid = 1;
		$psize = $project_rs['psize'] ? $project_rs['psize'] : $this->config['psize'];
		if(!$psize) $psize = 20;
		$offset = ($pageid-1) * $psize;
		$tpl = $this->get('tpl');
		if($tpl)
		{
			$pageurl .= "&tpl=".rawurlencode($tpl);
			$tplfile = $tpl;
		}
		//查询条件
		$condition = " l.project_id='".$pid."' AND l.user_id='".$_SESSION["user_id"]."' ";
		$condition.= " AND l.module_id='".$project_rs["module"]."' ";
		$keywords = $this->get('keywords');
		if($keywords)
		{
			$dt['keywords'] = $keywords;
			$pageurl .= "&keywords=".$keywords;
			$this->assign("keywords",$keywords);
			//$condition .= " AND l.title LIKE '%".$keywords."%'";
		}
		$dt['not_status'] = 1;
		//取得内容总数
		$total = $this->model('data')->total($dt);
		if($total>0)
		{
			$dt['offset'] = $offset;
			$dt['psize'] = $psize;
			$dt['in_text'] = 0;
			$dt['is_list'] = 1;
			//$rslist = $this->model('list')->get_list($project_rs['module'],$condition,$offset,$psize,"",$project_rs["orderby"]);
			$rslist = $this->model('data')->arclist($dt);
			//$rslist = $this->call->phpok("_arclist",$dt);
			$this->assign("pageid",$pageid);
			$this->assign("psize",$psize);
			$this->assign("pageurl",$pageurl);
			$this->assign("total",$total);
			$this->assign("rslist",$rslist);
		}
		if(!$this->tpl->check_exists($tplfile)) $tplfile = "usercp_list";
		$this->view($tplfile);
	}

	//收货地址管理
	function address_f()
	{
		$shipping = $this->model('address')->address_list($_SESSION['user_id'],'shipping');
		if($shipping)
		{
			reset($shipping);
			$shipping = current($shipping);
			$this->assign("shipping",$shipping);
		}
		$billing = $this->model('address')->address_list($_SESSION['user_id'],'billing');
		if($billing)
		{
			reset($billing);
			$billing = current($billing);
			$this->assign("billing",$billing);
		}
		$this->view("usercp_address");
	}
}
?>
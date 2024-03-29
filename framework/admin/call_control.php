<?php
/**
 * 数据调用中心
 * @作者 qinggan <admin@phpok.com>
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年1月10日
**/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class call_control extends phpok_control
{
	private $psize = 100;
	private $phpok_type_list;//可调用类型
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->phpok_type_list = $this->model('call')->types();
		$this->assign("phpok_type_list",$this->phpok_type_list);
		$this->popedom = appfile_popedom("call");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->phpok_autoload();
		$psize = $this->psize;
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$keywords = $this->get("keywords");
		$pageurl = $this->url("call");
		$condition = "";
		if($keywords){
			$this->assign("keywords",$keywords);
			$pageurl.="&keywords=".rawurlencode($keywords)."&";
			$condition = " (ok.title LIKE '%".$keywords."%' OR ok.identifier LIKE '%".$keywords."%') ";
		}
		$rslist = $this->model('call')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('call')->get_count($condition);
		$this->assign("total",$total);
		
		if($total>$this->psize){
			$string = P_Lang("home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
			$pagelist = phpok_page($pageurl,$total,$pageid,$this->psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$attrlist = $this->model('list')->attr_list();
		$this->assign("attrlist",$attrlist);
		$this->model('log')->add(P_Lang('访问【数据调用】页面'));
		$this->view("phpok_index");
	}

	public function set_f()
	{
		$this->phpok_autoload();
		$id = $this->get("id");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('call')->get_one($id);
			if($rs['ext']){
				$ext = unserialize($rs['ext']);
				unset($rs['ext']);
				if($ext) $rs = array_merge($ext,$rs);
			}
			$this->assign("rs",$rs);
			$this->assign("id",$id);
			$this->model('log')->add(P_Lang('访问【编辑数据调用#{0}】',$id));
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$this->model('log')->add(P_Lang('访问【添加数据调用】'));
		}
		$site_id = $this->session()->val('admin_site_id');
		$rslist = $this->model('project')->get_all_project($site_id);
		$this->assign("rslist",$rslist);
		$attrlist = $this->model('list')->attr_list();
		$this->assign("attrlist",$attrlist);

		//读取用户组
		$ugroup = $this->model('usergroup')->get_all("is_guest=0");
		$this->assign("usergroup",$ugroup);

		//读取表单选项
		$option_list = $this->model('opt')->group_all();
		$this->assign('option_list',$option_list);

		//读取菜单列表
		$menulist = $this->model('menu')->group();
		$this->assign('menulist',$menulist);
		$this->view("phpok_set");
	}

	//取得分类列表
	public function cate_list_f()
	{
		$id = $this->get("id","int");
		if($id){
			$rs = $this->model('project')->get_one($id);
			if(!$rs["cate"]){
				$this->error(P_Lang('无分类'));
			}
			$cate_rs = $this->model('cate')->cate_info($rs["cate"],false);
			$catelist = $this->model('cate')->get_all($rs["site_id"],0,$rs["cate"]);
			$catelist = $this->model('cate')->cate_option_list($catelist);
			$this->success(array('cate'=>$cate_rs,'catelist'=>$catelist));
		}
		$catelist = $this->model('cate')->get_all($_SESSION["admin_site_id"]);
		$catelist = $this->model('cate')->cate_option_list($catelist);
		$this->success(array('catelist'=>$catelist));
	}

	public function arclist_f()
	{
		$pid = $this->get("pid","int");
		if(!$pid){
			$this->error(P_Lang('未指定ID'));
		}
		$p_rs = $this->model('project')->get_one($pid,false);
		if(!$p_rs['module']){
			$this->error(P_Lang('未绑定模块'));
		}
		$module = $this->model('module')->get_one($p_rs['module']);
		$rslist = $this->model('module')->fields_all($p_rs['module']);
		$this->assign('rslist',$rslist);
		$this->assign('mtype',$module['mtype']);
		$info = $this->fetch("phpok_ajax_fields");
		$order = $this->fetch("phpok_ajax_orderby");
		$this->success(array('need'=>$info,'orderby'=>$order,'attr'=>$p_rs['is_attr'],'rslist'=>$rslist,'mtype'=>$module['mtype'],'sub'=>$p_rs['']),true);
	}

	public function save_f()
	{
		$id = $this->get("id","int");
		$this->phpok_autoload();
		$title = $this->get("title");
		$array = array();
		if(!$id){
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			if(!$title){
				$this->error(P_Lang('标题不能为空'),$error_url);
			}
			$identifier = $this->get("identifier",'system');
			$chk = $this->check_identifier($identifier);
			if($chk != "ok"){
				$this->error($chk,$error_url);
			}
			$array["identifier"] = $identifier;
			$array["site_id"] = $this->session->val('admin_site_id');
			$this->model('log')->add(P_Lang('添加数据调用'));
		}else{
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			if(!$title){
				$this->error(P_Lang('标题不能为空'),$error_url);
			}
			$this->model('log')->add(P_Lang('保存修改数据调用#{0}',$id));
		}
		$array["title"] = $title;
		$array["pid"] = $this->get("pid","int");
		$array["type_id"] = $this->get("type_id");
		$array["status"] = $this->get("status","int");
		$array["cateid"] = $this->get("cateid",'int');
		$array['sqlinfo'] = $this->get('sqlinfo');
		$array['is_api'] = $this->get('is_api','int');
		$ext = array();
		$ext['psize'] = $this->get("psize",'int');
		$ext['offset'] = $this->get("offset",'int');
		$ext['is_list'] = $this->get("is_list",'int');
		$ext['attr'] = $this->get('attr');
		$ext['fields_need'] = $this->get('fields_need');
		$ext['tag'] = $this->get('tag');
		$ext['keywords'] = $this->get('keywords');
		$ext['orderby'] = $this->get('orderby');
		$ext['fields'] = $this->get('fields');
		$ext['fields_format'] = $this->get('fields_format','int');
		$ext['user'] = $this->get('user');
		$ext['user_ext'] = $this->get('user_ext','int');
		$ext['usergroup'] = $this->get('usergroup','int');
		$ext['in_sub'] = $this->get('in_sub','int');
		$ext['title_id'] = $this->get('title_id');
		$ext['menu'] = $this->get('menu');
		$ext['option_id'] = $this->get('option_id','int');
		$ext['keywords_sign'] = $this->get('keywords_sign','int');
		$ext['keywords_type'] = $this->get('keywords_type');
		$array['ext'] = serialize($ext);
		$id = $this->model('call')->save($array,$id);
		$this->success();
	}

	/**
	 * 删除数据调用
	 * @参数 id 要删除的调用ID
	**/
	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('call')->del($id);
		$this->model('log')->add(P_Lang('删除数据调用#{0}',$id));
		$this->success();
	}

	public function status_f()
	{
		if(!$this->popedom["modify"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$rs = $this->model('call')->get_one($id);
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('call')->set_status($id,$status);
		if(!$action){
			$this->error(P_Lang('操作失败，请检查SQL语句'));
		}
		$status_name = $status ? '正常' : '关闭';
		$this->model('log')->add(P_Lang('修改数据调用ID #{0} 的状态为【{1}】',array($id,$status_name)));
		$this->success($status);
	}

	private function check_identifier($identifier)
	{
		if(!$identifier){
			return P_Lang('未指定标识串');
		}
		$identifier = strtolower($identifier);
		if(!preg_match("/^[a-z][a-z0-9\_\-]+$/u",$identifier)){
			return P_Lang('字段标识不符合系统要求，限小写字母、数字、中划线及下划线且必须是小写字母开头');
		}
		$rs = $this->model('call')->chk_identifier($identifier);
		if($rs){
			return P_Lang('字符串已被使用');
		}
		return "ok";		
	}
	
	private function phpok_autoload()
	{
		$site_id = $this->session->val('admin_site_id');
		$this->model('call')->site_id($site_id);
		$this->model('call')->psize($this->psize);
	}
}
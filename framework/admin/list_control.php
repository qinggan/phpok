<?php
/**
 * 内容控制器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 6.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2017年07月09日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class list_control extends phpok_control
{
	public $popedom;
	public function __construct()
	{
		parent::control();
		$this->lib('form')->cssjs();
	}

	private function popedom_auto($pid)
	{
		$this->popedom = appfile_popedom("list",$pid);
		$this->assign("popedom",$this->popedom);
		return $this->popedom;
	}

	/**
	 * 内容管理首页
	**/
	public function index_f()
	{
		$site_id = $this->session->val('admin_site_id');
		$rslist = $this->model('project')->get_all($this->session->val('admin_site_id'),0,"p.status=1 AND p.hidden=0");
		if(!$rslist){
			$rslist = array();
		}
		if(!$this->session->val('admin_rs.if_system')){
			if(!$this->session->val('admin_popedom')){
				$this->error(P_Lang('该管理员未配置权限，请检查'));
			}
			$condition = "parent_id>0 AND appfile='list' AND func=''";
			$p_rs = $this->model('sysmenu')->get_one_condition($condition);
			if(!$p_rs){
				$this->error(P_Lang('数据获取异常，请检查'));
			}
			$gid = $p_rs["id"];
			$popedom_list = $this->model('popedom')->get_all("gid='".$gid."' AND pid>0",false,false);
			if(!$popedom_list){
				$this->error(P_Lang('未配置站点内容权限，请检查'));
			}
			$popedom = array();
			foreach($popedom_list as $key=>$value){
				if(in_array($value["id"],$this->session->val('admin_popedom'))){
					$popedom[$value["pid"]][$value["identifier"]] = true;
				}
			}
			foreach($rslist as $key=>$value){
				if(!$popedom[$value["id"]] || !$popedom[$value["id"]]["list"]){
					unset($rslist[$key]);
					continue;
				}
			}
		}
		$this->assign("rslist",$rslist);
		$this->model('log')->add(P_Lang('访问【内容管理】页面'));
		$this->view("list_index");
	}

	public function action_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url("list"));
		}
		$this->popedom_auto($id);
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rs = $this->model('project')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('项目信息不存在'),$this->url("list"));
		}
		$son_list = $this->model('project')->get_all($rs["site_id"],$id,"p.status=1 AND p.hidden=0");
		if($son_list){
			foreach($son_list as $key=>$value){
				$popedom = appfile_popedom("list",$value["id"]);
				if(!$popedom["list"]){
					unset($son_list[$key]);
				}
			}
		}
		if($son_list){
			$this->assign("project_list",$son_list);
		}
		if(!$rs['tag']){
			$rs['tag'] = $this->model('tag')->get_tags('p'.$rs['id']);
		}
		$this->assign("rs",$rs);
		$this->assign("id",$id);
		$this->assign("pid",$id);
		$plist = array($rs);
		if($rs["parent_id"]){
			$this->model('project')->get_parentlist($plist,$rs["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		$cateid = $this->get("cateid");
		if(!$cateid){
			$cateid = $rs["cate"];
		}
		if($cateid && $rs["cate"]){
			$show_parent_catelist = $cateid != $rs["cate"] ? $cateid : false;
			$catelist = $this->model('cate')->get_sonlist($cateid);
			if(!$catelist){
				$cate_rs = $this->model('cate')->get_one($cateid);
				if($cate_rs["parent_id"]){
					$catelist = $this->model('cate')->get_sonlist($cate_rs["parent_id"]);
				}
			}
			$this->assign("catelist",$catelist);
			$opt_catelist = $this->model('cate')->get_all($rs["site_id"],1,$rs["cate"]);
			$opt_catelist = $this->model('cate')->cate_option_list($opt_catelist);
			if($opt_catelist){
				$cateall = array();
				foreach($opt_catelist as $key=>$value){
					$cateall[$value['id']] = $value['title'];
				}
				$this->assign('cateall',$cateall);
			}
			$this->assign("opt_catelist",$opt_catelist);
			if($show_parent_catelist){
				$parent_cate_rs = $this->model('cate')->get_one($show_parent_catelist);
				$this->assign('parent_cate_rs',$parent_cate_rs);
			}
			$this->assign("show_parent_catelist",$show_parent_catelist);
		}

		//设置内容列表
		if($rs["module"]){
			$m_rs = $this->model('module')->get_one($rs['module']);
			if($m_rs['mtype']){
				$this->model('log')->add(P_Lang('访问【{0}】，ID#{1}',array($rs['title'],$rs['id'])));
				$this->standalone_app($rs,$m_rs);
			}
			$this->content_list($rs);
			$this->model('log')->add(P_Lang('访问【{0}】，ID#{1}',array($rs['title'],$rs['id'])));
			$this->view("list_content");
		}
		$show_edit = true;
		$extlist = $this->model('ext')->ext_all('project-'.$id);
		if($extlist){
			$tmp = array();
			foreach($extlist as $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext as $k=>$v){
						$value[$k] = $v;
					}
				}
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
			}
			$this->assign('extlist',$tmp);
		}
		$this->model('log')->add(P_Lang('访问【{0}】，ID#{1}',array($rs['title'],$rs['id'])));
		$this->view("list_set2");
	}

	/**
	 * 保存项目信息
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定项目ID'));
		}
		$this->popedom_auto($id);
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('名称不能为空'));
		}
		$project = $this->model('project')->get_one($id,false);
		$array = array("title"=>$title);
		$array['style'] = $this->get('style');
		if($project['is_seo']){
			$array["seo_title"] = $this->get("seo_title");
			$array["seo_keywords"] = $this->get("seo_keywords");
			$array["seo_desc"] = $this->get("seo_desc");
		}
		if($project['is_tag']){
			$array['tag'] = $this->get('tag');
			$this->model('tag')->update_tag($array['tag'],'p'.$id);
		}
		$this->model('project')->save($array,$id);
		ext_save("project-".$id);
		$this->model('log')->add(P_Lang('保存项目 #{0} #{1}',array($id,$title)));
		$this->success();
	}

	private function check_identifier($sign,$id=0,$site_id=0)
	{
		if(!$sign){
			return P_Lang('标识串不能为空');
		}
		$sign = strtolower($sign);
		//字符串是否符合条件
		if(!preg_match("/[a-z0-9\_\-\.]+/",$sign)){
			return P_Lang('标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头');
		}
		if(!$site_id){
			$site_id = $_SESSION["admin_site_id"];
		}
		$check = $this->model('id')->check_id($sign,$site_id,$id);
		if($check){
			return P_Lang('标识符已被使用');
		}
		return 'ok';
	}

	/**
	 * 独立模块应用
	 * @参数 $project 项目信息，数组
	 * @参数 $module 模块信息，数组
	**/
	private function standalone_app($project,$module)
	{
		$pid = $project["id"];
		$mid = $project["module"];

		$layout = $module['layout'] ? explode(",",$module['layout']) : array();
		if($project['layout']){
			$layout = explode(",",$project['layout']);
		}
		$this->assign("m_rs",$module);
		$m_list = $this->model('module')->fields_all($mid,"identifier");
		if(!$m_list){
			$m_list = array();
		}
		$layout_list = array();
		$layout_admin_edit = array('text','textarea');
		$tmpid = 0;
		foreach($layout as $key=>$value){
			if($value == "hits"){
				$layout_list[$value] = array('title'=>P_Lang('次数'),'width'=>80,'edit'=>'true','align'=>'center','sort'=>'true','stat'=>'false','stat_title'=>'');
			}elseif($value == "dateline"){
				$layout_list[$value] = array('title'=>P_Lang('日期'),'width'=>150,'edit'=>'false','align'=>'center','sort'=>'true','stat'=>'false','stat_title'=>'');
			}elseif($value == "sort"){
				$layout_list[$value] = array('title'=>P_Lang('排序'),'width'=>80,'edit'=>'true','align'=>'center','sort'=>'true','stat'=>'false','stat_title'=>'');
			}else{
				$layout_tmparray = array();
				$layout_tmparray['title'] = $m_list[$value]["title"];
				$layout_tmparray['width'] = $m_list[$value]['admin-list-width'] ? $m_list[$value]['admin-list-width'] : 80;
				$layout_tmparray['edit'] = 'false';
				$layout_tmparray['sort'] = $m_list[$value]['admin-list-sort'] ? 'true' : 'false';
				if($m_list[$value]['admin-list-edit'] && in_array($m_list[$value]['form_type'],$layout_admin_edit)){
					$layout_tmparray['edit'] = 'true';
				}
				$layout_tmparray['align'] = 'left';
				if($project['admin-list-stat']){
					$layout_tmparray['stat'] = $m_list[$value]['admin-list-stat'] ? 'true' : 'false';
					$layout_tmparray['stat_title'] = $m_list[$value]['admin-stat-prefix'];
				}else{
					$layout_tmparray['stat'] = 'false';
					$layout_tmparray['stat_title'] = '';
				}
				$layout_tmparray['idx'] = $tmpid;
				$tmpid++;
				$layout_list[$value] = $layout_tmparray;
			}
		}
		$search_list = array();
		foreach($m_list as $key=>$value){
			if($value['search'] && ($value['search'] == 1 || $value['search'] == 2)){
				$search_list[] = $value;
			}
		}
		$this->assign('search_list',$search_list);
		$this->assign("ext_list",$m_list);
		$this->assign("layout",$layout_list);
		unset($layout_list);
		$psize = $this->config["psize"] ? $this->config["psize"] : "30";
		if($project['psize'] && $project['psize'] > $psize){
			$psize = $project['psize'];
		}
		$this->assign('psize',$psize);
		if(!$this->config["pageid"]){
			$this->config["pageid"] = "pageid";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "site_id='".$project['site_id']."' AND project_id='".$project['id']."'";
		$pageurl = $this->url("list","action","id=".$pid);
		$keywords = $this->get('keywords');
		if($keywords){
			$this->assign('keywords',$keywords);
			if($m_list){
				foreach($m_list as $key=>$value){
					$_condition = $this->model('form')->search($value,$keywords[$value['identifier']],false);
					if($_condition){
						$condition .= " AND (".$_condition.") ";
						$pageurl .= "&keywords[".$value['identifier']."]=".rawurlencode($keywords[$value['identifier']]);
					}
				}
			}
		}

		$keytype = $this->get('keytype');
		$keywords = $this->get("keywords");
		if($keytype && $keywords && trim($keywords)){
			$condition .= " AND ".$keytype." LIKE '%".$keywords."%'";
			$pageurl .= "&keywords=".rawurlencode($keywords)."&keytype=".rawurlencode($keytype);
			$this->assign("keywords",$keywords);
			$this->assign("keytype",$keytype);
		}
		$total = $this->model('list')->single_count($mid,$condition);
		if($total > 0){
			$this->assign('total',$total);
			$rslist = $this->model('list')->single_list($mid,$condition,$offset,$psize,$project['orderby']);
			if($project['cate'] && $rslist){
				$cate_ids = array();
				foreach($rslist as $key=>$value){
					$cate_ids[] = $value['cate_id'];
				}
				$cate_ids = array_unique($cate_ids);
				$catelist = $this->model('cate')->catelist_cid($cate_ids,false);
				if($catelist){
					foreach($rslist as $key=>$value){
						if($value['cate_id'] && $catelist[$value['cate_id']]){
							$value['cate'] = $catelist[$value['cate_id']];
							$rslist[$key] = $value;
						}
					}
				}
			}
			$string = P_Lang("home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
			if($total>$psize){
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
			$this->assign("rslist",$rslist);
		}
		$this->view('list_standalone');
	}
	//列表管理
	private function content_list($project_rs)
	{
		if(!$project_rs){
			$this->error(P_Lang('项目信息不能为空'));
		}
		$pid = $project_rs["id"];
		$mid = $project_rs["module"];
		$site_id = $project_rs["site_id"];
		$orderby = $project_rs["orderby"];
		if(!$pid || !$mid || !$site_id){
			$this->error(P_Lang('数据异常'));
		}
		//读取电商数据
		$this->model('list')->is_biz(($project_rs['is_biz'] ? true : false));
		//读取多级分类
		$this->model('list')->multiple_cate(($project_rs['cate_multiple'] ? true : false));
		//绑定用户
		$this->model('list')->is_user(($project_rs['is_userid'] ? true : false));
		//内容布局维护
		$layout = $m_list = array();
		$m_rs = $this->model('module')->get_one($mid);
		$m_list = $this->model('module')->fields_all($mid,"identifier");
		if($m_rs["layout"]){
			$layout = explode(",",$m_rs["layout"]);
		}
		if($project_rs['layout']){
			$layout = explode(",",$project_rs["layout"]);
		}
		$this->assign("m_rs",$m_rs);
		$layout_list = array();
		$layout_admin_edit = array('text','textarea');
		foreach($layout as $key=>$value){
			if($value == "hits"){
				$layout_list[$value] = array('title'=>P_Lang('次数'),'width'=>80,'edit'=>'true','align'=>'center','sort'=>'true','stat'=>'false','stat_title'=>'');
			}elseif($value == "dateline"){
				$layout_list[$value] = array('title'=>P_Lang('日期'),'width'=>150,'edit'=>'false','align'=>'center','sort'=>'true','stat'=>'false','stat_title'=>'');
			}elseif($value == "sort"){
				$layout_list[$value] = array('title'=>P_Lang('排序'),'width'=>80,'edit'=>'true','align'=>'center','sort'=>'true','stat'=>'false','stat_title'=>'');
			}elseif($value == 'user_id'){
				$layout_tmparray = array();
				$layout_tmparray['title'] = $project_rs['user_alias'] ? $project_rs['user_alias'] : P_Lang('用户');
				$layout_tmparray['width'] = 120;
				$layout_tmparray['edit'] = 'false';
				$layout_tmparray['align'] = 'center';
				$layout_tmparray['sort'] = 'true';
				$layout_tmparray['stat'] = 'false';
				$layout_tmparray['stat_title'] = '';
				$layout_list['user_id'] = $layout_tmparray;
			}else{
				$layout_tmparray = array();
				$layout_tmparray['title'] = $m_list[$value]["title"];
				$layout_tmparray['width'] = $m_list[$value]['admin-list-width'] ? $m_list[$value]['admin-list-width'] : 80;
				$layout_tmparray['edit'] = 'false';
				$layout_tmparray['sort'] = $m_list[$value]['admin-list-sort'] ? 'true' : 'false';
				if($m_list[$value]['admin-list-edit'] && in_array($m_list[$value]['form_type'],$layout_admin_edit)){
					$layout_tmparray['edit'] = 'true';
				}
				$layout_tmparray['align'] = 'left';
				if($project_rs['admin-list-stat']){
					$layout_tmparray['stat'] = $m_list[$value]['admin-list-stat'] ? 'true' : 'false';
					$layout_tmparray['stat_title'] = $m_list[$value]['admin-stat-prefix'];
				}else{
					$layout_tmparray['stat'] = 'false';
					$layout_tmparray['stat_title'] = '';
				}
				$layout_list[$value] = $layout_tmparray;
			}
		}
		$this->assign("ext_list",$m_list);
		$this->assign("layout",$layout_list);
		unset($layout_list);
		$psize = $this->config["psize"] ? $this->config["psize"] : "30";
		if($project_rs['psize'] && $project_rs['psize'] > $psize){
			$psize = $project_rs['psize'];
		}
		$psize2 = $this->get('psize');
		if($psize2){
			$psize = $psize2;
			$this->assign('psize2',$psize2);
		}
		$this->assign('psize',$psize);
		if(!$this->config["pageid"]){
			$this->config["pageid"] = "pageid";
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$offset = ($pageid-1) * $psize;
		$condition = "l.site_id='".$site_id."' AND l.project_id='".$pid."' ";
		$pageurl = $this->url("list","action","id=".$pid);
		if($psize2){
			$pageurl .= "&psize=".$psize2;
		}
		$keywords = $this->get('keywords');
		$key_type = $this->get('key_type');
		$key_data = $this->get('key_data');
		if(!$keywords){
			$keywords = array();
		}
		if($key_type && $key_data){
			$this->assign('key_type',$key_type);
			$this->assign('key_data',$key_data);
			$pageurl .= "&key_type=".$key_type."&key_data=".rawurlencode($key_data);
			$keywords[$key_type] = $key_data;
		}
		if($keywords){
			$this->assign('keywords',$keywords);
		}
		if(!$keywords || ($keywords && $keywords['_id'] && $keywords['_id'] != 3)){
			$condition .= "  AND l.parent_id='0' ";
		}
		if($keywords && $keywords['id'] && intval($keywords['id'])){
			if($keywords['_id'] == 1){
				$condition .= " AND l.id>".intval($keywords['id'])." ";
			}elseif($keywords['_id'] == 2){
				$condition .= " AND l.id<".intval($keywords['id'])." ";
			}elseif($keywords['_id'] == 3){
				$condition .= " AND l.parent_id=".intval($keywords['id'])." ";
			}else{
				$condition .= " AND l.id=".intval($keywords['id'])." ";
			}
			$pageurl .= "&keywords[id]=".$keywords['id']."&keywords[_id]=".$keywords['_id'];
		}
		if($keywords && $keywords['cateid'] && $project_rs['cate']){
			$cate_rs = $this->model('cate')->get_one($keywords['cateid']);
			$catelist = array($cate_rs);
			$this->model('cate')->get_sublist($catelist,$keywords['cateid']);
			$cate_id_list = array();
			foreach($catelist as $key=>$value){
				$cate_id_list[] = $value["id"];
			}
			$cate_idstring = implode(",",$cate_id_list);
			if($project_rs['cate_multiple']){
				$condition .= " AND lc.cate_id IN(".$cate_idstring.")";
			}else{
				$condition .= " AND l.cate_id IN(".$cate_idstring.")";
			}

			$pageurl .= "&keywords[cateid]=".$keywords['cateid'];
		}
		if($keywords && $keywords['title']){
			$tmp_condition = array();
			$tmp = str_replace(' ','%',$keywords['title']);
			$tmp_condition[] = "l.title LIKE '%".$tmp."%'";
			if($project_rs['is_seo']){
				$tmp_condition[] = "l.seo_title LIKE '%".$tmp."%'";
				$tmp_condition[] = "l.seo_keywords LIKE '%".$tmp."%'";
				$tmp_condition[] = "l.seo_desc LIKE '%".$tmp."%'";
			}
			if($project['is_identifier']){
				$tmp_condition[] = "l.identifier LIKE '%".$tmp."%'";
			}
			$condition .= " AND (".implode(" OR ",$tmp_condition).") ";
			$pageurl .= "&keywords[title]=".rawurlencode($keywords['title']);
		}
		if($keywords && $keywords['identifier'] && $project_rs['is_identifier']){
			$keywords['identifier'] = str_replace("，",",",$keywords['identifier']);
			$tmplist = explode(",",$keywords['identifier']);
			$tmp_condition = array();
			foreach($tmplist as $key=>$value){
				$tmp_condition[] = "l.identifier LIKE '%".$value."%'";
			}
			$condition .= " AND (".implode(" OR ",$tmp_condition).") ";
			$pageurl .= "&keywords[identifier]=".rawurlencode($keywords['identifier']);
		}
		if($keywords && $keywords['tag'] && $project_rs['is_tag']){
			$keywords['tag'] = str_replace("，",",",$keywords['tag']);
			$tmplist = explode(",",$keywords['tag']);
			$tmp_condition = array();
			foreach($tmplist as $key=>$value){
				$tmp_condition[] = "l.tag LIKE '%".$value."%'";
			}
			$condition .= " AND (".implode(" OR ",$tmp_condition).") ";
			$pageurl .= "&keywords[tag]=".rawurlencode($keywords['tag']);
		}
		if($keywords && $keywords['user'] && $project_rs['is_userid']){
			$keywords['user'] = str_replace("，",",",$keywords['user']);
			$tmplist = explode(",",$keywords['user']);
			$tmp_condition = array();
			foreach($tmplist as $key=>$value){
				$tmp_condition[] = "u.user LIKE '%".$value."%'";
				$tmp_condition[] = "u.email LIKE '%".$value."%'";
				$tmp_condition[] = "u.mobile LIKE '%".$value."%'";
			}
			$condition .= " AND (".implode(" OR ",$tmp_condition).") ";
			$pageurl .= "&keywords[user]=".rawurlencode($keywords['user']);
		}
		if($keywords && $m_list){
			foreach($m_list as $key=>$value){
				$_condition = $this->model('form')->search($value,$keywords[$value['identifier']]);
				if($_condition){
					$condition .= " AND (".$_condition.") ";
					$pageurl .= "&keywords[".$value['identifier']."]=".rawurlencode($keywords[$value['identifier']]);
				}
			}
		}
		if($keywords && $keywords['attr']){
			$condition .= " AND FIND_IN_SET('".$keywords['attr']."',l.attr) ";
			$pageurl .= "&keywords[attr]=".rawurlencode($keywords['attr']);
		}
		if($keywords && $keywords['dateline_start']){
			$tmp = strtotime($keywords['dateline_start']);
			if($tmp){
				$condition .= " AND l.dateline>=".$tmp." ";
				$pageurl .= "&keywords[dateline_start]=".rawurlencode($keywords['dateline_start']);
			}
		}
		if($keywords && $keywords['dateline_stop']){
			$tmp = strtotime($keywords['dateline_stop']);
			if($tmp){
				$condition .= " AND l.dateline<=".$tmp." ";
				$pageurl .= "&keywords[dateline_stop]=".rawurlencode($keywords['dateline_stop']);
			}
		}
		if($keywords && $keywords['status']){
			if($keywords['status'] == 1){
				$condition .= ' AND l.status=1 ';
			}else{
				$condition .= ' AND l.status=0 ';
			}
			$pageurl .= "&keywords[status]=".$keywords['status'];
		}
		if($keywords && $keywords['hidden']){
			if($keywords['hidden'] == 1){
				$condition .= ' AND l.hidden=1 ';
			}else{
				$condition .= ' AND l.hidden=0 ';
			}
			$pageurl .= "&keywords[hidden]=".$keywords['hidden'];
		}

		$orderby_search = $this->get('orderby_search');
		if($keywords && $keywords['orderby_search']){
			switch($keywords['orderby_search']){
				case "hits_hot":
					$orderby = "l.hits DESC,l.sort ASC,l.id DESC";
					break;
				case "hits_cold":
					$orderby = "l.hits ASC,l.sort ASC,l.id DESC";
					break;
				case "price_high":
					$orderby = "b.price DESC,l.sort ASC,l.id DESC";
					break;
				case "price_low":
					$orderby = "b.price ASC,l.sort ASC,l.id DESC";
					break;
				case "sort_max":
					$orderby = "l.sort DESC,l.sort ASC,l.id DESC";
					break;
				case "sort_min":
					$orderby = "l.sort ASC,l.sort ASC,l.id DESC";
					break;
				case "dateline_max":
					$orderby = "l.dateline DESC,l.sort ASC,l.id DESC";
					break;
				case "dateline_min":
					$orderby = "l.dateline ASC,l.sort ASC,l.id DESC";
					break;
				case "id_max":
					$orderby = "l.id DESC";
					break;
				case "id_min":
					$orderby = "l.id ASC";
					break;
			}
			$pageurl .= "&keywords[orderby_search]=".$keywords['orderby_search'];
		}
		//取得列表信息
		$total = $this->model('list')->get_total($mid,$condition);
		if($total > 0){
			$rslist = $this->model('list')->get_list($mid,$condition,$offset,$psize,$orderby);
			$extcate_ids = $rslist ? array_keys($rslist) : array();
			if($project_rs['cate'] && $project_rs['cate_multiple']){
				$clist = $this->model('list')->catelist($extcate_ids);
				$this->assign('clist',$clist);
			}
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			if(isset($_POST) && count($_POST)>0){
				$this->_location($pageurl);
			}
			if($total>$psize){
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
			$this->assign("rslist",$rslist);
			$this->assign("total",$total);
		}
		if($project_rs['is_attr']){
			$attrlist = $this->model('list')->attr_list($project_rs['id'],$project_rs['site_id']);
			$this->assign("attrlist",$attrlist);
		}
		return true;
	}

	/**
	 * 添加或编辑独立模块下的内容
	 * @参数 id 主题ID
	 * @参数 pid 项目ID
	**/
	public function edit2_f()
	{
		$pid = $this->get("pid","int");
		if(!$pid){
			$this->error(P_Lang('操作异常，未指定项目'),$this->url("list"));
		}
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('项目信息不存在'));
		}
		$this->assign('p_rs',$project);
		$this->assign('pid',$pid);
		$this->popedom_auto($pid);
		if($project["cate"]){
			$catelist = $this->model('cate')->get_all($project["site_id"],1,$project["cate"]);
			$catelist = $this->model('cate')->cate_option_list($catelist);
			$this->assign("catelist",$catelist);
		}
		$plist = array($project);
		if($project["parent_id"]){
			$this->model('project')->get_parentlist($plist,$project["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		$id = $this->get('id','int');
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$module = $this->model('module')->get_one($project['module']);
		$this->assign('m_rs',$module);
		if($id){
			$rs = $this->model('list')->single_one($id,$module);
			if(!$rs){
				$this->error(P_Lang('主题信息不存在'));
			}
			$this->assign('rs',$rs);
			$this->assign("id",$rs["id"]);
		}
		$ext_list = $this->model('module')->fields_all($project["module"]);
		if(!$ext_list){
			$ext_list = array();
		}
		$myextlist = array();
		foreach($ext_list as $key=>$value){
			if($value['parent_id'] && $value['parent_id'] == $value['id']){
				$sublist = array();
				foreach($ext_list as $k=>$v){
					if($v["ext"] && is_string($v['ext'])){
						$ext = unserialize($v["ext"]);
						$v = array_merge($v,($ext ? $ext : array()));
					}
					if($rs && $rs[$v["identifier"]]){
						$v["content"] = $rs[$v["identifier"]];
					}
					if($v['parent_id'] != $v['id'] && $v['parent_id'] == $value['id']){
						$sublist[] = $this->lib('form')->format($v);
					}
				}
				if($sublist && count($sublist)>0){
					$value['sublist'] = $sublist;
				}
				$myextlist[] = $value;
			}else{
				if($value['parent_id'] && $value['parent_id'] != $value['id']){
					continue;
				}
				$myextlist[] = $value;
			}
		}
		$ext_list = $myextlist;
		$extlist = array();
		$e_sublist = array();
		foreach($ext_list as $key=>$value){
			$idlist[] = strtolower($value["identifier"]);
			if($rs[$value["identifier"]] != ''){
				$value["content"] = $rs[$value["identifier"]];
			}
			$css_cls = "layui-col-md4 layui-col-lg3";
			if($value['sublist']){
				$count = count($value['sublist']);
				if($count == 2){
					$css_cls = "layui-col-md4";
				}elseif($count == 1){
					$css_cls = "layui-col-md6";
				}
			}
			$value['css_cls'] = $css_cls;
			if(!$value['group_id'] || $value['group_id'] == 'main'){
				$extlist[] = $this->lib('form')->format($value);
			}else{
				$e_sublist[] =  $this->lib('form')->format($value);
			}
		}
		if($e_sublist && count($e_sublist)>0){
			$this->assign('e_sublist',$e_sublist);
		}
		$this->assign("extlist",$extlist);
		$isopen = $this->get("_isopen",'int');
		$this->assign('isopen',($isopen ? true : false));
		$this->view('list_edit2');
	}

	/**
	 * 添加或编辑内容，这里的内容是带模块的
	**/
	public function edit_f()
	{
		$id = $this->get("id","int");
		$pid = $this->get("pid","int");
		if(!$id && !$pid){
			$this->error(P_Lang('操作异常'),$this->url("list"));
		}
		if($id){
			$rs = $this->model('list')->get_one($id,false);
			$pid = $rs["project_id"];
			$extcate = $this->model('list')->ext_catelist($id);
			if(!$extcate){
				$extcate = array();
			}
		}else{
			$cateid = $this->get("cateid","int");
			$rs = $extcate = array();
			//判断是否有临时未保存的数据
			$autosave = $this->lib('file')->cat($this->dir_data.'cache/autosave_'.$this->session->val('admin_id').'_'.$pid.'.php');
			if($autosave){
				$rs = unserialize($autosave);
				if($rs['dateline']){
					$rs['dateline'] = strtotime($rs['dateline']);
				}
			}
			if($cateid){
				$rs["cate_id"] = $cateid;
				$extcate = array($cateid);
			}
			$rs['is_virtual'] = $this->site['biz_main_service'];
		}
		if(!$pid){
			$this->error(P_Lang('操作异常'),$this->url("list"));
		}
		$this->popedom_auto($pid);
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$p_rs = $this->model('project')->get_one($pid);
		if(!$p_rs){
			$this->error(P_Lang('项目不存在'));
		}
		//针对项目里设置的电商属性
		if(!$id){
			$rs['is_virtual'] = ($p_rs['biz_service'] == 1 || $p_rs['biz_service'] == 2) ? 1 : 0;
		}
		$m_rs = $this->model('module')->get_one($p_rs["module"]);
		//读取扩展属性
		$ext_list = $this->model('module')->fields_all($p_rs["module"]);
		if(!$ext_list){
			$ext_list = array();
		}
		$myextlist = array();
		foreach($ext_list as $key=>$value){
			if($value['parent_id'] && $value['parent_id'] == $value['id']){
				$sublist = array();
				foreach($ext_list as $k=>$v){
					if($v["ext"] && is_string($v['ext'])){
						$ext = unserialize($v["ext"]);
						$v = array_merge($v,($ext ? $ext : array()));
					}
					if($rs && $rs[$v["identifier"]]){
						$v["content"] = $rs[$v["identifier"]];
					}
					if($v['parent_id'] != $v['id'] && $v['parent_id'] == $value['id']){
						$sublist[] = $this->lib('form')->format($v);
					}
				}
				if($sublist && count($sublist)>0){
					$value['sublist'] = $sublist;
				}
				$myextlist[] = $value;
			}else{
				if($value['parent_id'] && $value['parent_id'] != $value['id']){
					continue;
				}
				$myextlist[] = $value;
			}
		}
		$ext_list = $myextlist;
		$extlist = array();
		$e_sublist = array();
		foreach($ext_list as $key=>$value){
			$idlist[] = strtolower($value["identifier"]);
			if($value["ext"] && is_string($value['ext'])){
				$ext = unserialize($value["ext"]);
				$value = array_merge($value,($ext ? $ext : array()));
			}
			if($rs[$value["identifier"]] != ''){
				$value["content"] = $rs[$value["identifier"]];
			}
			$css_cls = "layui-col-md4 layui-col-lg3";
			if($value['sublist']){
				$count = count($value['sublist']);
				if($count == 2){
					$css_cls = "layui-col-md4";
				}elseif($count == 1){
					$css_cls = "layui-col-md6";
				}
			}
			$value['css_cls'] = $css_cls;
			if(!$value['group_id'] || $value['group_id'] == 'main'){
				$extlist[] = $this->lib('form')->format($value);
			}else{
				$e_sublist[] =  $this->lib('form')->format($value);
			}
		}
		if($e_sublist && count($e_sublist)>0){
			$this->assign('e_sublist',$e_sublist);
		}
		if($id){
			$tmplist = $this->model('ext')->ext_all('list-'.$id,true);
		}else{
			$tmplist = $this->session->val('admin-add-list');
		}
		if($tmplist){
			foreach($tmplist as $key=>$value){
				if($value['ext']){
					$ext = $value['ext'];
					if(is_string($value['ext'])){
						$ext = unserialize($value['ext']);
					}
					unset($value['ext']);
					if($ext){
						$value = array_merge($value,$ext);
					}
				}
				if($this->popedom['ext']){
					$value['is_edit'] = true;
				}
				$extlist[] = $this->lib('form')->format($value);
			}
		}
		$this->assign("extlist",$extlist);
		$this->assign("p_rs",$p_rs);
		$this->assign("m_rs",$m_rs);
		$this->assign("pid",$pid);
		$this->assign('extcate',$extcate);
		$plist = array($p_rs);
		if($p_rs["parent_id"]){
			$this->model('project')->get_parentlist($plist,$p_rs["parent_id"]);
			krsort($plist);
		}
		$this->assign("plist",$plist);
		if($rs["id"]){
			$this->assign("id",$rs["id"]);
		}
		if($p_rs["cate"]){
			$catelist = $this->model('cate')->get_all($p_rs["site_id"],1,$p_rs["cate"]);
			$catelist = $this->model('cate')->cate_option_list($catelist);
			$this->assign("catelist",$catelist);
		}
		if($p_rs['is_biz']){
			$this->_biz_edit($p_rs,$rs);
		}
		//判断是否有父主题
		$parent_id = $this->get("parent_id","int");
		if($parent_id){
			$parent_rs = $this->model('list')->get_one($parent_id);
			if(!$rs["cate_id"]){
				$rs["cate_id"] = $parent_rs["cate_id"];
			}
			$this->assign("parent_rs",$parent_rs);
			$this->assign("parent_id",$parent_id);
		}
		$this->assign("rs",$rs);
		if($p_rs['is_attr']){
			$attrlist = $this->model('list')->attr_list($p_rs['id'],$p_rs['site_id']);
			if($attrlist){
				$attr = $rs['attr'] ? explode(",",$rs['attr']) : array();
				foreach($attrlist as $key=>$value){
					$tmp = array('status'=>false,'val'=>$value);

					if($attr && in_array($key,$attr)){
						$tmp['status'] = true;
					}
					$attrlist[$key] = $tmp;
				}
				$this->assign("attrlist",$attrlist);
			}
		}

		// 扩展字段管理
		if($this->popedom['ext']){
			$ext_module = $id ? 'list-'.$id : 'add-list';
			$this->assign("ext_module",$ext_module);
			$no_include = array('id','title','identifier');
			$used_fields = $this->model('list')->fields_all($p_rs['module'],$id,$this->session->val('admin-add-list'));
			$used_fields = $used_fields ? array_merge($no_include,$used_fields) : $no_include;
			$used_fields = array_unique($used_fields);
			$extfields = $this->model('fields')->fields_list($used_fields);
			$this->assign("extfields",$extfields);
		}

		//获取标签选项
		if($p_rs['is_tag']){
			$tag_config = $this->model('tag')->config();
			$this->assign('tag_config',$tag_config);
			if($tag_config['count']){
				$taglist = $this->model('tag')->tag_quick($tag_config['count']);
				$this->assign('taglist',$taglist);
			}
		}
		$isopen = $this->get("_isopen",'int');
		if(!$isopen && $id){
			$loglist = $this->model('log')->list_log($id,'list');
			$this->assign('loglist',$loglist);
		}
		$this->assign('isopen',($isopen ? true : false));
		$this->view("list_edit");
	}

	public function copy_f()
	{
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error('未指定项目ID');
			return false;
		}
		$this->popedom_auto($pid);
		if(!$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$ids = $this->get('ids','int');
		if(!$ids){
			$this->error(P_Lang('未指定主题ID'));
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('list')->copy_id($value,true);
		}
		$this->success();
	}

	private function _biz_edit($p_rs,$rs=array())
	{
		$currency_list = $this->model('currency')->get_list('id');
		if(!$rs['currency_id']){
			$rs['currency_id'] = $p_rs['currency_id'];
		}
		if($currency_list){
			$this->assign('currency_list',$currency_list);
			$currency = array();
			foreach($currency_list as $key=>$value){
				if($value['id'] == $p_rs['currency_id']){
					$currency = $value;
				}
			}
			$this->assign('currency',$currency);
		}
		if($p_rs['freight']){
			$freight = $this->model('freight')->get_one($p_rs['freight']);
			$this->assign('freight',$freight);
		}
		if($p_rs['biz_attr']){
			$biz_attrlist = $this->model('options')->get_all();
			if($biz_attrlist){
				$this->assign('biz_attrlist',$biz_attrlist);
			}
			//加载现有电商属性
			if($rs['id']){
				$this->_biz_attr($rs['id']);
			}
		}
		$unitlist = $this->model('biz')->unitlist();
		$this->assign('unitlist',$unitlist['name']);
		$wholesale = $this->model('wholesale')->all($rs['id']);
		$this->assign('wholesale',$wholesale);
	}

	private function _biz_attr($id)
	{
		$tmplist = $this->model('stock')->val_all($id);
		if(!$tmplist){
			return false;
		}
		$tmp_ids = array();
		foreach($tmplist as $key=>$value){
			$tmp_ids[] = $value['attr'];
		}
		$tmp_ids = implode(",",$tmp_ids);
		$tmp_ids = explode(",",$tmp_ids);
		$tmp_ids = array_unique($tmp_ids);
		$tmp_condition = "id IN(".implode(",",$tmp_ids).")";
		$tmplist = $this->model('options')->values_list($tmp_condition,0,9999);
		if(!$tmplist){
			return false;
		}
		$_attr = $_attr_values = array();
		foreach($tmplist as $key=>$value){
			$_attr[] = $value['aid'];
			$_attr_values[] = $value['aid'].'_'.$value['id'];
		}
		$_attr = array_unique($_attr);
		$this->assign("_biz_attr",implode(",",$_attr));
		$this->assign("_biz_attr_value",implode(",",$_attr_values));
	}

	private function _wholesale_save($id)
	{
		$qty = $this->get('_wholesale_qty');
		$price = $this->get('_wholesale_price');
		if(!$qty || !$price){
			$this->model('wholesale')->delete($id);
			return true;
		}
		$t = array();
		foreach($qty as $key=>$value){
			if(!$value){
				continue;
			}
			$tmp = array();
			$tmp['qty'] = $value;
			$tmp['price'] = $price[$key];
			$t[] = $tmp;
		}
		if(!$t || count($t)<1){
			$this->model('wholesale')->delete($id);
			return true;
		}
		$this->model('wholesale')->save($t,$id);
		return true;
	}

	/**
	 * 保存产品属性及库存
	**/
	private function _attr_save($id)
	{
 		//删除现有属性
 		$attr = $this->get('_biz_attr');
 		if(!$attr){
	 		$this->model('stock')->clean($id);
	 		return true;
 		}
 		$tmp_cost = $this->get('_cost_price');
 		$tmp_market = $this->get('_market_price');
 		$tmp_price = $this->get('_sell_price');
 		$tmp_stock = $this->get('_stock');
		$_attrlist = $this->get('_attrlist');
 		if(!$_attrlist || !is_array($_attrlist)){
	 		$this->model('stock')->clean($id);
	 		return false;
 		}
 		$olist = $this->model('stock')->val_all($id);
 		if(!$olist){
	 		$olist = array();
 		}
 		$keys = array_keys($olist);
 		$deletes = array_diff($keys,$_attrlist);
 		if($deletes){
	 		foreach($deletes as $key=>$value){
		 		$t = $olist[$value];
		 		$this->model('stock')->delete($t['id']);
	 		}
 		}
 		$adds = array_diff($_attrlist,$keys);
 		$qty = 0;
 		foreach($_attrlist as $key=>$value){
	 		$tmp = array();
	 		$tmp['tid'] = $id;
	 		$tmp['attr'] = $value;
	 		$tmp['qty'] = $tmp_stock[$value];
	 		$tmp['cost'] = $tmp_cost[$value];
	 		$tmp['market'] = $tmp_market[$value];
	 		$tmp['price'] = $tmp_price[$value];
	 		if($olist[$value]){
		 		$this->model('stock')->update($tmp,$olist[$value]['id']);
	 		}else{
		 		$this->model('stock')->add($tmp);
	 		}
	 		$qty += $tmp['qty'];
 		}
 		$data = array();
 		$data['qty'] = $qty;
 		$data['id'] = $id;
 		$this->model('list')->biz_save($data);
	}

	public function ok_f()
	{
		$id = $this->get("id","int");
		$pid = $this->get("pid","int");
		$parent_id = $this->get("parent_id","int");
		if(!$pid && !$id){
			$this->error(P_Lang('操作异常，无法取得项目信息'));
		}
		$is_add = true;
		if($id){
			$rs = $this->model('list')->get_one($id);
			$this->assign("rs",$rs);
			$pid = $rs["project_id"];
			$parent_id = $rs["parent_id"];
			$is_add = false;
		}
		if(!$pid){
			$this->error(P_Lang('操作异常，无法取得项目信息'));
		}
		$this->popedom_auto($pid);
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}else{
			if(!$this->popedom['add']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$p_rs = $this->model('project')->get_one($pid);
		if(!$p_rs){
			$this->error(P_Lang('操作异常，无法取得项目信息'));
		}
		$_autosave = $this->get('_autosave','int');
		$array = array();
		$title = $this->get("title");
		if($title == ''){
			$this->error(P_Lang('内容的主题不能为空'));
		}
		$array["title"] = $title;
		if($p_rs['cate']){
			$cate_id = $this->get("cate_id","int");
			if(!$cate_id && !$_autosave){
				$this->error(P_Lang('主分类不能为空'));
			}
			$array["cate_id"] = $cate_id;
		}else{
			$array['cate_id'] = 0;
		}
		//更新标识串
 		$array['identifier'] = $this->get("identifier");
 		if(!$array['identifier'] && $p_rs['is_identifier'] == 2 && !$_autosave){
	 		$this->error(P_Lang('自定义标识不能为空，此项是系统设置必填项'));
 		}
 		if($array['identifier']){
	 		$check = $this->check_identifier($array['identifier'],$id,$p_rs["site_id"]);
	 		if($check != 'ok'){
		 		$this->error($check);
	 		}
 		}
		if($p_rs['is_attr']){
			$attr = $this->get("attr");
			if($attr && is_array($attr)){
				$attr = implode(",",$attr);
			}
			$array["attr"] = $attr;
		}
		if($p_rs['is_tag']){
			$array["tag"] = $this->get("tag");
			if($array["tag"]){
				$array["tag"] = preg_replace("/(\x20{2,})/"," ",$array["tag"]);
			}
			if(!$array['tag'] && $p_rs['is_tag'] == 2 && !$_autosave){
				$this->error(P_Lang('Tag标签不能为空'));
			}
		}else{
			$array['tag'] = '';
		}
		if($p_rs['is_userid']){
			$array['user_id'] = $this->get('user_id','int');
			if(!$array['user_id'] && $p_rs['is_userid'] == 2 && !$_autosave){
				$this->error(P_Lang('用户账号不能为空'));
			}
		}
		if($p_rs['is_tpl_content']){
			$array['tpl'] = $this->get('tpl');
			if(!$array['tpl'] && $p_rs['is_tpl_content'] == 2 && !$_autosave){
				$this->error(P_Lang('自定义内容模板不能为空'));
			}
		}else{
			$array['tpl'] = '';
		}
		$array["parent_id"] = $parent_id;
		$dateline = $this->get("dateline","time");
		if(!$dateline){
			$dateline = $this->time;
		}
		$array["dateline"] = $dateline;
		if($this->popedom["status"]){
			$array["status"] = $this->get("status","int");
		}
		$array["hidden"] = $this->get("hidden","int");
		if($_autosave){
			$array["status"] = 0;
			$array['hidden'] = 0;
		}
		if(!$_autosave){
			$crontab = 0;
			if($dateline > $this->time && $array['status']){
				$array['hidden'] = 2;
				//加入定时取消操作
				$crontab = $dateline;
			}
		}
		$array["hits"] = $this->get("hits","int");
		$array["sort"] = $this->get("sort","int");
		$array["seo_title"] = $this->get("seo_title");
		$array["seo_keywords"] = $this->get("seo_keywords");
		$array["seo_desc"] = $this->get("seo_desc");
		$array['lastdate'] = $this->time;//最后修改时间
		if(!$array["seo_title"] && $p_rs['is_seo'] == 3 && !$_autosave){
			$this->error(P_Lang('SEO标题不能为空'));
		}
		if(!$array["seo_keywords"] && $p_rs['is_seo'] == 3 && !$_autosave){
			$this->error(P_Lang('SEO关键字不能为空'));
		}
		if(!$array["seo_desc"] && $p_rs['is_seo'] == 3 && !$_autosave){
			$this->error(P_Lang('SEO描述不能为空'));
		}

		//自定义字段信息
		if($p_rs["module"]){
	 		$ext_list = $this->model('module')->fields_all($p_rs["module"]);
	 		$ext_data = array();
	 		$ext_data["site_id"] = $p_rs["site_id"];
	 		$ext_data["project_id"] = $p_rs['id'];
	 		$ext_data["cate_id"] = $cate_id;
	 		if(!$ext_list){
		 		$ext_list = array();
	 		}
			foreach($ext_list as $key=>$value){
				if($rs[$value['identifier']]){
					$value['content'] = $rs[$value['identifier']];
				}
				$ext_data[$value["identifier"]] = $this->lib('form')->get($value);
				if($value['onlyone'] && $ext_data[$value["identifier"]] != ''){
					$check = $this->model('list')->ext_only_check($value['identifier'],$ext_data[$value["identifier"]],$p_rs["id"],$p_rs["module"],false,$id);
					if($check){
						$this->error(P_Lang('字段 [title] 内容重复，请重新设置',array('title'=>$value['title'])));
					}
				}
			}
		}

		$array["project_id"] = $p_rs['id'];
		$array["module_id"] = $p_rs["module"];
		$array["site_id"] = $p_rs["site_id"];
		$array['integral'] = $this->get('integral','int');
		$array['style'] = $this->get('style');
		$tmpadd = false;
		if(!$id){
			$id = $this->model('list')->save($array);
			$tmpadd = true;
			$this->lib('file')->rm($this->dir_data.'cache/autosave_'.$this->session->val('admin_id').'_'.$p_rs['id'].'.php');
			$this->model('log')->add(P_Lang('项目【{0}】，添加ID #{1}，主题 {2}',array($p_rs['title'],$id,$array['title'])));
 		}else{
	 		//检测是否数据有变化
	 		$this->model('log')->title_save($id,$array['title']);
 			$this->model('list')->save($array,$id);
 			$this->model('log')->add(P_Lang('项目【{0}】，修改ID #{1}，主题 {2}',array($p_rs['title'],$id,$array['title'])));
 		}
 		if(!$id){
	 		$this->error(P_Lang('存储数据失败，请检查'));
 		}
 		$crontab_list = $this->lib('file')->ls($this->dir_data.'crontab');
 		if($crontab_list){
	 		foreach($crontab_list as $key=>$value){
		 		$tmp = basename($value);
		 		$tmp = str_replace('.php','',$tmp);
		 		$tmplist = explode("-",$tmp);
		 		if(!$tmplist || count($tmplist) != 2 || $tmplist[1] != $id){
			 		continue;
		 		}
		 		$this->lib('file')->rm($value);
	 		}
 		}
 		if($crontab){
	 		$this->lib('file')->vi($id,$this->dir_data.'crontab/'.$crontab.'-'.$id.'.php');
 		}
 		//保存电商信息
 		if($p_rs['is_biz']){
	 		$biz = array('price'=>$this->get('price','float'),'currency_id'=>$this->get('currency_id','int'));
	 		$biz['weight'] = $this->get('weight','float');
	 		$biz['volume'] = $this->get('volume','float');
	 		$biz['unit'] = $this->get('unit');
	 		$biz['id'] = $id;
	 		$biz['is_virtual'] = $this->get('is_virtual','int');
	 		$biz['qty'] = $this->get('qty','int');
	 		$biz['min_qty'] = $this->get('min_qty','int');
	 		//价格区别
	 		$this->model('log')->biz_save($id,$biz['price']);
	 		//
	 		$this->model('list')->biz_save($biz);
	 		$this->_attr_save($id);
	 		$this->_wholesale_save($id);
 		}
 		//更新扩展分类
 		$this->model('list')->list_cate_clear($id);
 		if($cate_id){
	 		$ext_cate = $this->get('ext_cate_id');
	 		if(!$ext_cate){
		 		$ext_cate = array($cate_id);
	 		}else{
		 		$ext_cate = array_merge($ext_cate,array($cate_id));
		 		$ext_cate = array_unique($ext_cate);
	 		}
	 		$this->model('list')->save_ext_cate($id,$ext_cate);
 		}
 		//更新Tag标签
 		$this->model('tag')->update_tag($array['tag'],$id);
 		//保存模块里的扩展字段信息
 		if($p_rs["module"] && $ext_data){
	 		$ext_data['id'] = $id;
	 		if(!$tmpadd){
		 		$this->model('log')->ext_save($id,$p_rs["module"],$ext_data);
	 		}
	 		$this->model('list')->save_ext($ext_data,$p_rs["module"]);
		}
		//保存内容扩展字段
		if($tmpadd){
			ext_save("admin-add-list",true,"list-".$id);
		}else{
			ext_save("list-".$id);
		}
		if($array['status'] && $array['user_id']){
			$this->model('wealth')->add_integral($id,$array['user_id'],'post',P_Lang('管理员编辑主题发布#{id}',array('id'=>$id)));
		}
		$this->node('system_admin_title_success',$id,$p_rs);
		$this->plugin('system_admin_title_success',$id,$p_rs);
 		$this->success($id);
	}

	public function single_save_f()
	{
		$id = $this->get("id","int");
		$pid = $this->get("pid","int");
		if(!$pid){
			$this->error(P_Lang('未指定项目ID'));
		}
		$this->popedom_auto($pid);
		$popedom_id = $id ? 'modify' : 'add';
		if(!$this->popedom[$popedom_id]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('操作异常，无法取得项目信息'));
		}
		$dateline = $this->get('dateline');
		$array = array();
		$array["project_id"] = $pid;
		$array['site_id'] = $project['site_id'];
		if($project['cate']){
			$array['cate_id'] = $this->get('cate_id','int');
		}
		//补全部分属性
		$array['status'] = $this->get('status','int');
		$array['sort'] = $this->get('sort','int');
		$array['dateline'] = $dateline ? strtotime($dateline) : $this->time;
		$array['hidden'] = $this->get('hidden','int');
		$array['hits'] = $this->get('hits');

		$extlist = $this->model('module')->fields_all($project["module"]);
		if($extlist){
			foreach($extlist as $key=>$value){
				$array[$value['identifier']] = $this->lib('form')->get($value);
				if($value['onlyone'] && $array[$value["identifier"]] != ''){
					$check = $this->model('list')->ext_only_check($value['identifier'],$array[$value["identifier"]],$project["id"],$project["module"],true,$id);
					if($check){
						$this->error(P_Lang('字段 [title] 内容重复，请重新设置',array('title'=>$value['title'])));
					}
				}
			}
		}
		//保存数据
		if($id){
			$this->model('log')->single_save($id,$project['module'],$array);
			$array['id'] = $id;
			$state = $this->model('list')->single_save($array,$project["module"]);
			if(!$state){
				$this->error(P_Lang('更新数据失败，请检查'));
			}
		}else{
			$id = $this->model('list')->single_save($array,$project["module"]);
			if(!$id){
				$this->error(P_Lang('保存数据失败，请检查'));
			}
		}
		$this->node('system_admin_title_success',$id,$project);
		$this->plugin('system_admin_title_success',$id,$project);
		$this->success();
	}

	/**
	 * 主题删除
	**/
	public function del_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('没有指定主题ID'));
		}
		$idlist = explode(",",$id);
		$chk_id = intval($idlist[0]);
		$rs = $this->model('list')->get_one($chk_id);
		$pid = $rs["project_id"];
		$this->popedom_auto($pid);
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		foreach($idlist as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('list')->delete($value);
		}
		$this->success();
	}

	/**
	 * 单独模块主题删除
	**/
	public function single_delete_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('没有指定主题ID'));
		}
		$pid = $this->get('pid');
		if(!$pid){
			$this->error(P_Lang('未指定项目'));
		}
		$this->popedom_auto($pid);
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$project = $this->model('project')->get_one($pid);
		$idlist = explode(",",$id);
		foreach($idlist as $key=>$value){
			$value = intval($value);
			$this->model('list')->single_delete($value,$project['module']);
		}
		$this->success();
	}

	/**
	 * 主题审核，通过主题原来的状态取1或0
	 * @参数 id 主题ID
	**/
	public function content_status_f()
	{
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$rs = $this->model('list')->get_one($id);
		$this->popedom_auto($rs['project_id']);
		if(!$this->popedom["status"]){
			$this->error("您没有启用/禁用权限");
		}
		$status = $rs["status"] ? 0 : 1;
		$action = $this->model('list')->update_status($id,$status);
		if(!$action){
			$this->error(P_Lang('操作失败，请检查SQL语句'));
		}
		if($status && $rs['user_id']){
			$this->model('wealth')->add_integral($id,$rs['user_id'],'post',P_Lang('管理员操作审核主题发布#{id}',array('id'=>$rs['id'])));
		}
		//执行插件接入点
		$this->plugin("ap-list-status",$id);
		$this->success($status);
	}

	/**
	 * 主题审核，通过主题原来的状态取1或0
	 * @参数 id 主题ID
	**/
	public function single_status_f()
	{
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('没有指定项目ID'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('未绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module['mtype']){
			$this->error(P_Lang('非得立模块不能执行此操作'));
		}
		$popedom = $this->popedom_auto($project['id']);
		if(!$popedom['status']){
			$this->error(P_Lang('您没有权限'));
		}
		$rs = $this->model('list')->single_one($id,$module);
		$status = $rs["status"] ? 0 : 1;
		$data = array();
		$data['id'] = $id;
		$data['status'] = $status;
		$action = $this->model('list')->single_save($data,$project['module']);
		if(!$action){
			$this->error(P_Lang('操作失败，请检查SQL语句'));
		}
		$this->success($status);
	}

	/**
	 * 审核，取消审核，显示，隐藏
	 * @参数 id 主题ID
	**/
	public function single_action_f()
	{
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('没有指定项目ID'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('未绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if(!$module['mtype']){
			$this->error(P_Lang('非得立模块不能执行此操作'));
		}

		$val = $this->get('val','int');
		$val = $val == 1 ? 1 : 0;
		$type = $this->get('type');
		if(!$type || !in_array($type,array('hidden','status'))){
			$type = 'status';
		}
		if($type == 'status'){
			$popedom = $this->popedom_auto($project['id']);
			if(!$popedom['status']){
				$this->error(P_Lang('您没有权限'));
			}
		}else{
			$popedom = $this->popedom_auto($project['id']);
			if(!$popedom['modify']){
				$this->error(P_Lang('您没有权限'));
			}
		}
		$list = explode(",",$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				return false;
			}
			$data = array();
			$data['id'] = $value;
			$data[$type] = $val;
			$action = $this->model('list')->single_save($data,$project['module']);
		}
		$this->success($status);
	}

	//执行动作
	public function execute_f()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->json(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		$list = explode(',',$ids);
		$tmp1 = $list[0];
		$rs = $this->model('list')->get_one($tmp1);
		$this->popedom_auto($rs['project_id']);
		if(!$this->popedom['status']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			if($title == 'status'){
				$this->model('list')->update_status($value,1);
			}elseif($title == 'unstatus'){
				$this->model('list')->update_status($value,0);
			}elseif($title == 'hidden'){
				$this->model('list')->save(array('hidden'=>1),$value);
			}elseif($title == 'show'){
				$this->model('list')->save(array('hidden'=>0),$value);
			}
		}
		$this->json(P_Lang('操作成功'),true);
	}

	/**
	 * 内容排序
	**/
	public function content_sort_f()
	{
		$sort = $this->get('sort');
		if(!$sort || !is_array($sort)){
			$this->error(P_Lang('更新排序失败'));
		}
		foreach($sort as $key=>$value){
			$this->model('list')->update_sort($key,$value);
		}
		$this->success();
	}

	public function single_move_cate_f()
	{
		$ids = $this->get("ids");
		$cate_id = $this->get("cate_id",'int');
		$pid = $this->get('pid','int');
		if(!$cate_id || !$ids || !$pid){
			$this->error(P_Lang('参数传递不完整'));
		}
		$list = explode(",",$ids);
		$project = $this->model('project')->get_one($pid);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('模块不存在'));
		}
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$data = array();
			$data['id'] = $value;
			$data['cate_id'] = $cate_id;
			$this->model('list')->single_save($data,$project['module']);
		}
		$this->success();
	}

	public function move_cate_f()
	{
		$ids = $this->get("ids");
		$cate_id = $this->get("cate_id");
		$type = $this->get('type');
		if(!$cate_id || !$ids || !$type){
			$this->json(P_Lang('参数传递不完整'));
		}
		$list = explode(",",$ids);
		$delete_ok = true;
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			if($type == 'add'){
				$this->model('list')->list_cate_add($cate_id,$value);
			}
			if($type == 'delete'){
				$act = $this->model('list')->list_cate_delete($cate_id,$value);
				if(!$act){
					$delete_ok = false;
				}
			}
			if($type == 'move'){
				$mid = $this->model('list')->get_mid($value);
				if($mid){
					$this->model('list')->update_ext(array("cate_id"=>$cate_id),$mid,$value);
				}
				$this->model('list')->save(array('cate_id'=>$cate_id),$value);
				$this->model('list')->list_cate_add($cate_id,$value);
			}
		}
		if(!$delete_ok){
			$this->json(P_Lang('主分类不允许删除'));
		}
		$this->json(P_Lang('更新成功'),true);
	}

	//设置属性
	public function attr_set_f()
	{
		$ids = $this->get("ids");
		$val = $this->get("val");
		$type = $this->get("type");
		if(!$val || !$ids || !$type)
		{
			$this->json(P_Lang('参数传递不完整'));
		}
		if($type != "add" && $type != "delete") $type = "add";
		$list = explode(",",$ids);
		foreach($list as $key=>$value)
		{
			$value = intval($value);
			if(!$value) continue;
			$rs = $this->model('list')->simple_one($value);
			if(!$rs) continue;
			$attr = $rs["attr"];
			if($attr)
			{
				$tmp = explode(",",$attr);
				if($type == "add")
				{
					$tmp[] = $val;
					$tmp = array_unique($tmp);
					$attr = implode(",",$tmp);
				}
				else
				{
					if(in_array($val,$tmp))
					{
						foreach($tmp as $k=>$v)
						{
							if($v == $val) unset($tmp[$k]);
						}
						if($tmp && count($tmp)>0)
						{
							$attr = implode(",",$tmp);
						}
						else
						{
							$attr = "";
						}
					}
				}
			}
			else
			{
				$attr = $type == "add" ? $val : "";
			}
			$array = array("attr"=>$attr);
			$this->model('list')->save($array,$value);
		}
		$this->json(P_Lang('更新成功'),true);
	}

	//更多批处理功能
	public function plaction_f()
	{
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('未指定ID'),$this->url('list'),'error');
		}
		$this->popedom_auto($id);
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$project_rs = $this->model('project')->get_one($id);
		if(!$project_rs)
		{
			error(P_Lang("项目信息不存在"),$this->url("list"),"error");
		}
		if(!$project_rs['module'])
		{
			error(P_Lang('未绑定模块，不能使用此功能'),$this->url('list','action','id='.$id),'error');
		}
		$this->assign('page_rs',$project_rs);
		$this->view('list_plaction');
	}

	public function plaction_submit_f()
	{
		$pid = $this->get('pid');
		if(!$pid){
			$this->json(P_Lang('未指定项目ID'));
		}
		$this->popedom_auto($id);
		$project_rs = $this->model('project')->get_one($pid);
		if(!$project_rs){
			$this->json(P_Lang("项目信息不存在"));
		}
		if(!$project_rs['module']){
			$this->json(P_Lang('未绑定模块，不能使用此功能'));
		}
		$startid = $this->get('startid','int');
		$endid = $this->get('endid','int');
		$plaction = $this->get('plaction');
		if($plaction == 'delete'){
			if(!$this->popedom['delete']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$condition = "project_id=".$pid." ";
			if($startid){
				$condition .= "AND id>=".$startid." ";
			}
			if($endid){
				$condition .= "AND id<=".$endid." ";
			}
			$this->model('list')->pl_delete($condition,$project_rs['module']);
			$this->json(P_Lang('批量删除操作成功'),true);
		}
		$sql = "UPDATE ".$this->db->prefix."list SET ";
		if($plaction == 'status' || $plaction == 'unstatus'){
			if(!$this->popedom['status']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$sql .= " status=".($plaction == 'status' ? '1' : '0')." ";
		}elseif($plaction == 'hidden' || $plaction == 'show'){
			if(!$this->popedom['list']){
				$this->json(P_Lang('您没有权限执行此操作'));
			}
			$sql .= " hidden=".($plaction == 'hidden' ? '1' : '0')." ";
		}
		$sql.= " WHERE project_id='".$pid."' ";
		if($startid){
			$sql .= "AND id>=".$startid." ";
		}
		if($endid){
			$sql .= "AND id<=".$endid." ";
		}
		$this->db->query($sql);
		$this->json(P_Lang('批处理操作成功'),true);
	}

	/**
	 * 读取产品属性及可操作内容
	**/
	public function attr_f()
	{
		$tid = $this->get('tid','int');
		$aid = $this->get('aid','int');
		if(!$aid){
			$this->error(P_Lang('未指定属性ID'));
		}
		$attrlist = $this->model('options')->get_all('id');
		$list = explode(",",$aid);
		sort($list);
		$glist = array();
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			if(!$attrlist[$value]){
				continue;
			}
			$optlist = $this->model('options')->values_list("aid='".$value."'",0,9999,'id');
			$rs = $attrlist[$value];
			$rs['optlist'] = $optlist;
			$glist[$value] = $rs;
		}
		$this->assign('glist',$glist);
		$vals = $this->get('vals');
		if($vals){
			$this->_attrlist($glist,$vals,$tid);
		}
		$content = $this->fetch('list_option_info');
		$this->success($content);
	}



	public function comment_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('list')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('数据不存在'));
		}
		$this->popedom_auto($rs['project_id']);
		if(!$this->popedom['comment']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->assign("rs",$rs);
		$pageurl = $this->url("list","comment","id=".$id);
		$condition = "tid='".$id."' AND admin_id=0";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config["psize"] ? $this->config["psize"] : 30;
		$total = $this->model('reply')->get_total($condition);
		if($total>0){
			$offset = ($pageid-1) * $psize;
			$rslist = $this->model('reply')->get_all($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->assign("total",$total);
		$this->view("list_comment");
	}

	/**
	 * 设定主题的父层关系
	 * @参数 id 指定的父层
	 * @参数 ids 要绑定的主题，多个主题用英文逗号隔开
	 * @返回 JSON数据
	 * @更新时间 2016年10月25日
	**/
	public function set_parent_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$ids = $this->get('ids');
		if(!$ids){
			$this->error(P_Lang('没有要变更的ID'));
		}
		$list = explode(",",$ids);
		$isin = false;
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value || $value == $id){
				$isin = true;
				break;
			}
		}
		if($isin){
			$this->error(P_Lang('ID有冲突，要变更的主题ID和内置ID重复了'));
		}
		$rs = $this->model('list')->get_one($id,false);
		if($rs['parent_id']){
			$this->error(P_Lang('父主题不符合要求，父主题不允许存在上级关系'));
		}
		foreach($list as $key=>$value){
			$value = intval($value);
			if($value){
				$tmp = array('parent_id'=>$id);
				$this->model('list')->save($tmp,$value);
			}
		}
		$this->success();
	}

	/**
	 * 取消父层主题
	 * @参数 ids 要取消的主题，多个主题用英文逗号隔开
	 * @返回 JSON
	 * @更新时间 2016年10月25日
	**/
	public function unset_parent_f()
	{
		$ids = $this->get('ids');
		if(!$ids){
			$this->error(P_Lang('没有要变更的ID'));
		}
		$list = explode(",",$ids);
		$isin = false;
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				$isin = true;
				break;
			}
		}
		if($isin){
			$this->error(P_Lang('ID有冲突，要变更的主题ID和内置ID重复了'));
		}
		foreach($list as $key=>$value){
			$value = intval($value);
			if($value){
				$tmp = array('parent_id'=>0);
				$this->model('list')->save($tmp,$value);
			}
		}
		$this->success();
	}

	public function quickedit_f()
	{
		$pid = $this->get('pid','int');
		if(!$pid){
			$this->error(P_Lang('未指定项目'));
		}
		$project = $this->model('project')->get_one($pid,false);
		if(!$project){
			$this->error(P_Lang('项目不存在'));
		}
		if(!$project['module']){
			$this->error(P_Lang('未绑定模块'));
		}
		$popedom = $this->popedom_auto($project['id']);
		if(!$popedom['modify']){
			$this->error(P_Lang('您没有编辑权限'));
		}
		$val = $this->get('val');
		$field = $this->get('field');
		if(!$field){
			$this->error(P_Lang('未指定字段'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$module = $this->model('module')->get_one($project['module']);
		if($module && $module['mtype']){
			$rs = $this->model('list')->single_one($id,$module);
			if(!$rs){
				$this->error(P_Lang('信息不存在'));
			}
			$data = array();
			$data['id'] = $id;
			$data[$field] = $val;
			$this->model('list')->single_save($data,$module['id']);
			$this->success();
		}
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('信息不存在'));
		}
		$main = array('title','hits','sort');
		if(in_array($field,$main)){
			if($field != 'title'){
				$val = intval($val);
			}
			$this->model('list')->update_field($id,$field,$val);
			$this->success();
		}
		$elist = $this->model('module')->fields_all($rs["module_id"],'identifier');
		$keys = array_keys($elist);
		if(!in_array($field,$keys)){
			$this->error(P_Lang('字段无效'));
		}
		$data = array();
		$data[$field] = $val;
		$this->model('list')->update_ext($data,$rs['module_id'],$id);
		$this->success();
	}

	public function preview_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定主题ID'));
		}
		$rs = $this->model('list')->get_one($id,false);
		if(!$rs){
			$this->error(P_Lang('暂无内容'));
		}
		$project = $this->model('project')->get_one($rs['project_id'],false);
		if(!$project || !$project['module']){
			$this->error(P_Lang('项目不存在或未绑定模块'));
		}
		$module = $this->model('module')->get_one($project['module']);
		$data = array('id'=>P_Lang('主键ID'),'site_id'=>P_Lang('站点ID'),'project_id'=>P_Lang('项目ID'),'cate_id'=>P_Lang('分类ID'));
		if(!$module['mtype']){
			$data = $this->mainlist($project);
		}
		$flist = $this->model('module')->fields_all($module['id'],'identifier');
		if($flist){
			foreach($flist as $key=>$value){
				$data[$value['identifier']] = $value['title'];
			}
		}
		$rslist = array();
		$auto_hide = array('user_id','integral','replydate','parent_id','seo_title','seo_keywords','seo_desc','tpl'); //空值隐藏
		$auto_hide[] = 'style';
		$auto_hide[] = 'identifier';
		$auto_hide[] = 'attr';
		$auto_hide[] = 'tag';
		$auto_hide[] = 'hits';
		$forbid = array('hidden'); // 强制隐藏
		if(!$project['cate']){
			$forbid[] = 'cate_id';
		}
		if(!$project['is_seo']){
			$forbid[] = 'seo_title';
			$forbid[] = 'seo_keywords';
			$forbid[] = 'seo_desc';
		}
		if(!$project['is_userid']){
			$forbid[] = 'user_id';
		}
		if(!$project['is_tpl_content'] || !$project['is_front']){
			$forbid[] = 'tpl';
		}
		foreach($rs as $key=>$value){
			if(in_array($key,$forbid) || (in_array($key,$auto_hide) && !$value)){
				continue;
			}
			$tmp = array();
			$tmp['field'] = $key;
			$tmp['content'] = is_array($value) ? print_r($value,true) : $value;
			if($data[$key]){
				$tmp['title'] = $data[$key];
				$tmp['style'] = '';
			}else{
				$tmp['title'] = $key;
				$tmp['style'] = "color:red;";
			}
			$rslist[] = $tmp;
		}
		$this->assign('rslist',$rslist);
		$content = $this->fetch('list_preview');
		$this->success($content);
	}

	public function log_reset_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$log = $this->model('log')->get_one($id);
		if(!$log){
			$this->error('内容不存在');
		}
		if(!$log['rs']){
			$this->error('数据内容不存在');
		}
		$popedom = $this->popedom_auto($log['rs']['project_id']);
		if(!$popedom['modify']){
			$this->error(P_Lang('您没有编辑权限'));
		}
		$this->model('log')->update_reset($id);
		$this->success();
	}

	private function _attrlist($glist,$vals,$tid=0)
	{
		$tmplist = explode(",",$vals);
		$tmps = array();
		foreach($tmplist as $key=>$value){
			$tmp = explode("_",$value);
			if($tmp[0] && $glist[$tmp[0]] && $tmp[1] && intval($tmp[1])){
				$tmps[] = intval($tmp[1]);
			}
		}
		$vals = implode(",",$tmps);
		$condition = "id IN(".$vals.")";
		$vlist = $this->model('options')->values_list($condition,0,9999,'id');
		if(!$vlist){
			return false;
		}
		foreach($vlist as $key=>$value){
			if($glist[$value['aid']]['optlist'][$value['id']]){
				unset($glist[$value['aid']]['optlist'][$value['id']]);
			}
		}
		$this->assign('glist',$glist);
		$mlist = array();
		foreach($vlist as $key=>$value){
			if(!$glist[$value['aid']]){
				continue;
			}
			if(!isset($mlist[$value['aid']])){
				$mlist[$value['aid']] = $glist[$value['aid']];
			}
			$mlist[$value['aid']]['rslist'][$value['id']] = $value;
		}
		ksort($mlist);
		$tmplist = array();
		foreach($mlist as $key=>$value){
			ksort($value['rslist']);
			$tmplist[] = array_keys($value['rslist']);
			$mlist[$key] = $value;
		}
		$res = array();
		$idlist = $this->_cartesian($res,$tmplist);
		if($idlist){
			foreach($idlist as $key=>$value){
				$tmp = explode(",",$value);
				sort($tmp);
				$idlist[$key] = implode(",",$tmp);
			}
		}

		$alldata = array();
		if($tid){
			$alldata = $this->model('stock')->val_all($tid);
		}
		$rslist = array();
		foreach($idlist as $key=>$value){
			$tmp = explode(",",$value);
			$rs = array();
			$info = array();
			foreach($tmp as $k=>$v){
				$info[$vlist[$v]['aid']] = $vlist[$v]['title'];
			}
			$info_count = count($info);
			$glist_count = count($glist);
			for($i=$info_count;$i<$glist_count;$i++){
				$info[] = '-';
			}
			if($alldata && $alldata[$value]){
				$rs = $alldata[$value];
			}
			$rs['attr'] = $value;
			$rs['info'] = $info;
			$rslist[] = $rs;
		}
		$this->assign('rslist',$rslist);
	}

	private function _cartesian($res = array(), $arr = array())
	{
		if (empty($res)){
			$res = (array)array_shift($arr);
		}
		if (empty($arr)){
			return $res;
		}
		$current = array_shift($arr); # 接下来要参与计算的一组属性
		$last = [];
		foreach ($res as $row => $row_val) { # 循环上一次已经算出的集合
			foreach ($current as $col => $col_val) {
				$last[] = $row_val . ',' . $col_val;
			}
		}
		return $this->_cartesian($last,$arr); # 递归处理, 直到$arr滚到最后一组属性
	}

	private function mainlist($project)
	{
		$data = array();
		$data['title'] = $project['alias_title'] ? $project['alias_title'] : P_Lang('主题');
		$data['id'] = P_Lang('主键ID');
		$data['site_id'] = P_Lang('站点ID');
		$data['parent_id'] = P_Lang('父主题ID');
		$data['cate_id'] = P_Lang('分类ID');
		$data['project_id'] = P_Lang('项目ID');
		$data['module_id'] = P_Lang('模块ID');
		$data['dateline'] = P_Lang('日期');
		$data['lastdate'] = P_Lang('最后修改时间');
		$data['sort'] = P_Lang('排序');
		$data['status'] = P_Lang('审核状态');
		$data['hidden'] = P_Lang('隐藏状态');
		$data['hits'] = P_Lang('点击数');
		$data['tpl'] = P_Lang('模板');
		$data['seo_title'] = P_Lang('SEO标题');
		$data['seo_keywords'] = P_Lang('SEO关键字');
		$data['seo_desc'] = P_Lang('SEO描述');
		$data['tag'] = P_Lang('标签');
		$data['attr'] = P_Lang('属性');
		$data['replydate'] = P_Lang('最后回复时间');
		$data['user_id'] = P_Lang('用户ID');
		$data['identifier'] = P_Lang('标识');
		$data['integral'] =  P_Lang('财富基数');
		$data['style'] = P_Lang('样式');
		$data['price'] = P_Lang('价格');
		$data['currency_id'] = P_Lang('货币ID');
		$data['weight'] = P_Lang('重量');
		$data['volume'] = P_Lang('体积');
		$data['unit'] = P_Lang('单位');
		$data['is_virtual'] = P_Lang('是否虚拟');
		return $data;
	}


}

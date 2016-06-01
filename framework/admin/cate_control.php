<?php
/***********************************************************
	Filename: {phpok}/admin/cate_control.php
	Note	: 栏目管理
	Version : 4.0
	Author  : qinggan
	Update  : 2012-08-22 16:05
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class cate_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("cate");
		$this->assign("popedom",$this->popedom);
	}

	# 栏目列表
	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('cate')->get_all($_SESSION["admin_site_id"]);
		$this->assign("rslist",$rslist);
		$this->view("cate_index");
	}

	# 添加或编辑栏目信息，支持自定义字段
	public function set_f()
	{
		$parent_id = $this->get("parent_id","int");
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('cate'),'error');
			}
			$rs = $this->model('cate')->get_one($id);
			$this->assign("id",$id);
			$this->assign("rs",$rs);
			$parent_id = $rs["parent_id"];
			$this->assign("parent_id",$parent_id);
			$ext_module = "cate-".$id;
			$extlist = $this->model('ext')->ext_all($ext_module);
			if(!$rs['parent_id']){
				$ext2 = $this->lib('xml')->read($this->dir_root.'data/xml/cate_extfields_'.$id.'.xml');
				if($ext2['fid']){
					$this->assign('ext2',explode(",",$ext2['fid']));
				}
			}
		}else{
			if(!$this->popedom["add"]){
				error(P_Lang('您没有权限执行此操作'),$this->url('cate'),'error');
			}
			$this->assign("parent_id",$parent_id);
			$ext_module = "add-cate";
			$extlist = $_SESSION['admin-add-cate'];
			$taxis = $this->model('cate')->cate_next_taxis($parent_id);
			$this->assign('rs',array('taxis'=>$taxis));
			if($parent_id && !$_SESSION['admin-add-cate']){
				$root_id = $parent_id;
				$this->model('cate')->get_root_id($root_id,$parent_id);
				$ext2 = $this->lib('xml')->read($this->dir_root.'data/xml/cate_extfields_'.$root_id.'.xml');
				if($ext2['fid']){
					$tmplist = explode(",",$ext2['fid']);
					foreach($tmplist as $key=>$value){
						$tmp = $this->model('fields')->get_one($value);
						if($tmp){
							unset($tmp['id']);
							$_SESSION['admin-add-cate'][$tmp['identifier']] = $tmp;
						}
					}
					$extlist = $_SESSION['admin-add-cate'];
				}
			}
		}
		$used_fields = array();
		if($extlist){
			$tmp = false;
			foreach($extlist AS $key=>$value){
				if($value["ext"]){
					$ext = unserialize($value["ext"]);
					foreach($ext AS $k=>$v){
						$value[$k] = $v;
					}
				}
				$tmp[] = $this->lib('form')->format($value);
				$this->lib('form')->cssjs($value);
				$used_fields[] = $value['identifier'];
			}
			$this->assign('extlist',$tmp);
			//已使用的扩展字段
			$this->assign('used_fields',$used_fields);
		}
		$this->assign("ext_module",$ext_module);
		$parentlist = $this->model('cate')->get_all($_SESSION["admin_site_id"]);
		$parentlist = $this->model('cate')->cate_option_list($parentlist);
		$this->assign("parentlist",$parentlist);
		$extfields = $this->model('fields')->fields_list('',0,999,'cate');
		$this->assign("extfields",$extfields);
		$this->view("cate_set");
	}

	//添加根分类
	public function add_f()
	{
		if(!$this->popedom["add"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$this->view("cate_open_add");
	}

	public function status_f()
	{
		if(!$this->popedom['status']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('cate')->cate_info($id,false);
		$content = $rs['status'] ? 0 : 1;
		$this->model('cate')->save(array('status'=>$content),$id);
		$this->json($content,true);
	}

	public function open_save_f()
	{
		if(!$this->popedom['add']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		if(!$title || !$identifier) $this->json("信息不完整");
		$identifier2 = strtolower($identifier);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier2)){
			$this->json(P_Lang('标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头'));
		}
		$check = $this->model('id')->check_id($identifier2,$_SESSION["admin_site_id"]);
		if($check){
			$this->json(P_Lang('标识已被使用'));
		}
		$array = array();
		$array["site_id"] = $_SESSION["admin_site_id"];
		$array["parent_id"] = 0;
		$array["title"] = $title;
		$array["taxis"] = $this->model('cate')->cate_next_taxis(0);
		$array["psize"] = "";
		$array["tpl_list"] = "";
		$array["tpl_content"] = "";
		$array["status"] = 1;
		$array["identifier"] = $identifier;
		$id = $this->model('cate')->save($array);
		if(!$id){
			$this->json(P_Lang('分类添加失败，请检查！'));
		}
		$this->json(P_Lang('分类添加成功'),true);
	}
	
	public function save_f()
	{
		$id = $this->get("id","int");
		if((!$id && !$this->popedom['add']) || ($id && !$this->popedom['modify'])){
			error(P_Lang('您没有权限执行此操作'),$this->url('cate'),'error');
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$error_url = $this->url("cate","set");
		if($id) $error_url .= "&id=".$id;
		if(!$identifier){
			error(P_Lang('标识不能为空'),$error_url,"error");
		}
		$identifier2 = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier2)){
			error(P_Lang('标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头'),$error_url,"error");
		}
		$check = $this->model('id')->check_id($identifier2,$_SESSION["admin_site_id"],$id);
		if($check){
			error(P_Lang('标识已被使用'),$error_url,"error");
		}
		$parent_id = $this->get('parent_id','int');
		$array = array('title'=>$title,'identifier'=>$identifier);
		$array['parent_id'] = $parent_id;
		$array['status'] = $this->get('status','int');
		$array['tpl_list'] = $this->get('tpl_list');
		$array['tpl_content'] = $this->get('tpl_content');
		$array['psize'] = $this->get('psize','int');
		$array['taxis'] = $this->get('taxis','int');
		$array['seo_title'] = $this->get('seo_title');
		$array['seo_keywords'] = $this->get('seo_keywords');
		$array['seo_desc'] = $this->get('seo_desc');
		$array['tag'] = $this->get('tag');
		if(!$id){
			$array["site_id"] = $_SESSION["admin_site_id"];
			$id = $this->model('cate')->save($array);
			if(!$id){
				error(P_Lang('分类添加失败，请检查'),$error_url);
			}
			ext_save("admin-add-cate",true,"cate-".$id);
		}else{
			$rs = $this->model('cate')->get_one($id);
			if($parent_id == $id){
				$old_rs = $this->model('cate')->get_one($id);
				$parent_id = $old_rs["id"];
			}
			$son_cate_list = array();
			$this->son_cate_list($son_cate_list,$id);
			if(in_array($parent_id,$son_cate_list)){
				error(P_Lang('不允许将分类迁移至此分类下的子分类'),$error_url,"error");
			}
			$array["parent_id"] = $parent_id;
			$update = $this->model('cate')->save($array,$id);
			if(!$update){
				error(P_Lang('分类更新失败'),$error_url);
			}
			ext_save("cate-".$id);
		}
		$this->_save_tag($id);
		//保存根分类的扩展字段属性配置
		if(!$parent_id && $id)
		{
			$extfields = $this->get('_extfields');
			if($extfields){
				$extfields = implode(",",$extfields);
				$this->lib('xml')->save(array('fid'=>$extfields),$this->dir_root.'data/xml/cate_extfields_'.$id.'.xml');
			}
		}
		error(P_Lang('分类信息配置成功'),$this->url("cate"),"ok");
	}

	private function _save_tag($id)
	{
		$rs = $this->model('cate')->cate_info($id,false);
		if($rs['tag']){
			$this->model('tag')->update_tag($rs['tag'],'c'.$id,$_SESSION['admin_site_id']);
		}else{
			$this->model('tag')->stat_delete('c'.$id,"title_id");
		}
		return true;
	}

	function son_cate_list(&$son_cate_list,$id)
	{
		$list = $this->model('cate')->get_son_id_list($id);
		if($list){
			foreach($list AS $key=>$value){
				$son_cate_list[] = $value;
			}
			$this->son_cate_list($son_cate_list,implode(",",$list));
		}
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$idlist = $this->model('cate')->get_son_id_list($id);
		if($idlist){
			$this->json(P_Lang('存在子栏目，不能直接删除，请先删除相应的子栏目'));
		}
		$check_rs = $this->model('project')->chk_cate($id);
		if($check_rs){
			$this->json(P_Lang('分类使用中，请先删除'));
		}
		$this->model('cate')->cate_delete($id);
		$this->model('tag')->stat_delete('c'.$id,"title_id");
		$this->json(P_Lang('删除成功'),true);
	}

	public function ext_delete_f()
	{
		if(!$this->popedom['ext']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->json(P_Lang('未指定要删除的ID'));
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$action = $this->model('cate')->cate_ext_delete($cate_id,$id);
		}else{
			$idstring = $_SESSION["cate_ext_id"];
			if($idstring){
				$list = explode(",",$idstring);
				$tmp = array();
				foreach($list AS $key=>$value){
					if($value && $value != $id){
						$tmp[] = $value;
					}
				}
				$new_idstring = implode(",",$tmp);
				$_SESSION["cate_ext_id"] = $new_idstring;
			}
		}
		$this->json(P_Lang('扩展字段删除成功'),true);
	}

	public function check_f()
	{
		$id = $this->get("id","int");
		$sign = $this->get("sign");
		if(!$sign){
			$this->json(P_Lang('标识串不能为空'));
		}
		$sign = strtolower($sign);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$sign)){
			$this->json(P_Lang('标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头'));
		}
		$check = $this->model('id')->check_id($sign,$_SESSION["admin_site_id"],$id);
		if($check){
			$this->json(P_Lang('标识已被使用，请检查'));
		}
		$this->json("标识正常，可以使用",true);
	}

	public function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis)){
			$this->json(P_Lang('没有指定要更新的排序'));
		}
		foreach($taxis AS $key=>$value){
			$this->model('cate')->update_taxis($key,$value);
		}
		$this->json(P_Lang('数据排序更新成功'),true);
	}

	public function ajax_taxis_f()
	{
		$id = $this->get('id','int');
		$taxis = $this->get('taxis','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$this->model('cate')->update_taxis($id,$taxis);
		$this->json(true);
	}
}
?>
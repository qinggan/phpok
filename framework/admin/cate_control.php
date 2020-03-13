<?php
/**
 * 栏目管理
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2018年09月16日
**/

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

	/**
	 * 栏目列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$parent_id = $this->get('parent_id','int');
		if(!$parent_id){
			$rslist = $this->model('cate')->root_catelist($this->session->val('admin_site_id'));
			if($rslist){
				$ids = array_keys($rslist);
				$total_list = $this->model('cate')->count_sublist($ids);
				if($total_list){
					foreach($rslist as $key=>$value){
						if($total_list[$value['id']]){
							$value['total'] = $total_list[$value['id']];
						}
						$rslist[$key] = $value;
					}
				}
			}
			$this->assign("rslist",$rslist);
			$this->view("cate_index");
		}
		$rs = $this->model('cate')->get_one($parent_id);
		$this->assign('rs',$rs);
		$rslist = $this->model('cate')->catelist_sonlist($rs['id'],false,false);
		if($rslist){
			$ids = array_keys($rslist);
			$total_list = $this->model('cate')->count_sublist($ids);
			if($total_list){
				foreach($rslist as $key=>$value){
					if($total_list[$value['id']]){
						$value['total'] = $total_list[$value['id']];
					}
					$rslist[$key] = $value;
				}
			}
		}
		$this->assign("rslist",$rslist);
		$navlist = array();
		$navlist[] = $rs;
		if($rs['parent_id']){
			$this->model('cate')->parent_list($navlist,$rs['parent_id'],false,false);
		}
		krsort($navlist);
		$this->assign('navlist',$navlist);
		$this->view("cate_index");
	}

	/**
	 * 添加或编辑栏目信息，支持自定义字段
	**/
	public function set_f()
	{
		$parent_id = $this->get("parent_id","int");
		$id = $this->get("id","int");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'),$this->url('cate'));
			}
			$rs = $this->model('cate')->get_one($id);
			$this->assign("id",$id);
			$this->assign("rs",$rs);
			$parent_id = $rs["parent_id"];
			$this->assign("parent_id",$parent_id);
			$ext_module = "cate-".$id;
			$extlist = $this->model('ext')->ext_all($ext_module);
			if(!$rs['parent_id']){
				$ext2 = $this->lib('xml')->read($this->dir_data.'xml/cate_extfields_'.$id.'.xml');
				if($ext2['fid']){
					$this->assign('ext2',explode(",",$ext2['fid']));
				}
			}
			if(!$rs['parent_id']){
				$module = $this->model('module')->get_all();
				if($module){
					$mlist = array();
					foreach($module as $key=>$value){
						if(!$value['mtype'] && $value['tbl'] == 'cate'){
							$mlist[] = $value;
							continue;
						}
					}
					if($mlist && count($mlist)>0){
						$this->assign('mlist',$mlist);
					}
				}
			}
			$parent_id = $rs['parent_id'];
			$this->assign('parent_id',$rs['parent_id']);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$this->assign("parent_id",$parent_id);
			$ext_module = "add-cate";
			$extlist = $this->session->val('admin-add-cate');
			$taxis = $this->model('cate')->cate_next_taxis($parent_id);
			$this->assign('rs',array('taxis'=>$taxis));
			if($parent_id){
				$root_id = $parent_id;
				$this->model('cate')->get_root_id($root_id,$parent_id);
				$ext2 = $this->lib('xml')->read($this->dir_data.'xml/cate_extfields_'.$root_id.'.xml');
				if($ext2['fid']){
					$tmplist = explode(",",$ext2['fid']);
					foreach($tmplist as $key=>$value){
						$tmp = $this->model('fields')->default_one($value);
						if($tmp){
							unset($tmp['id']);
							$this->session->assign('admin-add-cate.'.$tmp['identifier'],$tmp);
						}
					}
					$extlist = $this->session->val('admin-add-cate');
				}
			}else{
				//读取模块
				$module = $this->model('module')->get_all();
				if($module){
					$mlist = array();
					foreach($module as $key=>$value){
						if(!$value['mtype'] && $value['tbl'] == 'cate'){
							$mlist[] = $value;
							continue;
						}
					}
					if($mlist && count($mlist)>0){
						$this->assign('mlist',$mlist);
					}
				}
			}
		}
		$used_fields = array();
		if($extlist){
			$tmp = false;
			foreach($extlist as $key=>$value){
				if($value["ext"]){
					$ext = is_string($value['ext']) ? unserialize($value["ext"]) : $value['ext'];
					if(!$ext){
						$ext = array();
					}
					foreach($ext as $k=>$v){
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
		$parentlist = $this->model('cate')->get_all($this->session->val('admin_site_id'));
		$parentlist = $this->model('cate')->cate_option_list($parentlist);
		$this->assign("parentlist",$parentlist);
		$extfields = $this->model('fields')->default_all();
		$this->assign("extfields",$extfields);

		$tag_config = $this->model('tag')->config();
		$this->assign('tag_config',$tag_config);
		if($parent_id){
			$root_id = $parent_id;
			$this->model('cate')->get_root_id($root_id,$parent_id);
			$cate_root = $this->model('cate')->get_one($root_id);
			if($cate_root['module_id']){
				$ext_list = $this->model('module')->fields_all($cate_root["module_id"]);
				$clist = array();
				foreach(($ext_list ? $ext_list : array()) as $key=>$value){
					if($value["ext"] && is_string($value['ext'])){
						$ext = unserialize($value["ext"]);
						$value = array_merge($value,($ext ? $ext : array()));
					}
					$idlist[] = strtolower($value["identifier"]);
					if($rs[$value["identifier"]] != ''){
						$value["content"] = $rs[$value["identifier"]];
					}
					$clist[] = $this->lib('form')->format($value);
				}
				$this->assign("clist",$clist);
			}
		}
		$this->view("cate_set");
	}

	/**
	 * 添加根分类
	**/
	public function add_f()
	{
		if(!$this->popedom["add"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$this->view("cate_open_add");
	}

	/**
	 * 分类状态设置
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
		$rs = $this->model('cate')->cate_info($id,false);
		$content = $rs['status'] ? 0 : 1;
		$this->model('cate')->save(array('status'=>$content),$id);
		$this->success($content);
	}

	/**
	 * 分类状态批量设置
	**/
	public function pl_status_f()
	{
		if(!$this->popedom['status']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$ids = $this->get('ids');
		if(!$ids){
			$this->error(P_Lang('未指定ID'));
		}
		$status = $this->get('status','int');
		
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$this->model('cate')->save(array('status'=>$status),$value);
		}
		$this->success();
	}

	/**
	 * 弹窗分类信息保存
	**/
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
		$check = $this->model('id')->check_id($identifier2,$this->session->val('admin_site_id'));
		if($check){
			$this->json(P_Lang('标识已被使用'));
		}
		$array = array();
		$array["site_id"] = $this->session->val('admin_site_id');
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

	/**
	 * 快速添加分类操作
	**/
	public function qsave_f()
	{
		if(!$this->popedom['add']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('分类名称不能为空'));
		}
		$array = array();
		$array["site_id"] = $this->session->val('admin_site_id');
		$array["parent_id"] = $this->get('root_id','int');
		$array["title"] = $title;
		$array["taxis"] = $this->model('cate')->cate_next_taxis(0);
		$array["status"] = 1;
		$array["identifier"] = uniqid('cate-');
		$id = $this->model('cate')->save($array);
		if(!$id){
			$this->error(P_Lang('分类添加失败，请检查！'));
		}
		$this->success($id);
	}

	/**
	 * 保存分类信息
	**/
	public function save_f()
	{
		$id = $this->get("id","int");
		if((!$id && !$this->popedom['add']) || ($id && !$this->popedom['modify'])){
			$this->error(P_Lang('您没有权限执行此操作'),$this->url('cate'));
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$error_url = $this->url("cate","set");
		if($id){
			$error_url .= "&id=".$id;
		}
		if(!$identifier){
			$this->error(P_Lang('标识不能为空'),$error_url);
		}
		$identifier2 = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier2)){
			$this->error(P_Lang('标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头'),$error_url);
		}
		$check = $this->model('id')->check_id($identifier2,$this->session->val('admin_site_id'),$id);
		if($check){
			$this->error(P_Lang('标识已被使用'),$error_url);
		}
		$parent_id = $this->get('parent_id','int');
		$array = array('title'=>$title,'identifier'=>$identifier);
		$array['parent_id'] = $parent_id;
		$array['status'] = $this->get('status','int');
		$array['tpl_list'] = $this->get('tpl_list');
		$array['tpl_content'] = $this->get('tpl_content');
		$array['psize'] = $this->get('psize','int');
		$array['psize_api'] = $this->get('psize_api','int');
		$array['taxis'] = $this->get('taxis','int');
		$array['seo_title'] = $this->get('seo_title');
		$array['seo_keywords'] = $this->get('seo_keywords');
		$array['seo_desc'] = $this->get('seo_desc');
		$array['tag'] = $this->get('tag');
		$array['style'] = $this->get('style');
		if(!$id){
			if(!$parent_id){
				$array['module_id'] = $this->get('module_id','int');
			}
			$array["site_id"] = $this->session->val('admin_site_id');
			$id = $this->model('cate')->save($array);
			if(!$id){
				$this->error(P_Lang('分类添加失败，请检查'),$error_url);
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
				$this->error(P_Lang('不允许将分类迁移至此分类下的子分类'),$error_url,"error");
			}
			$array["parent_id"] = $parent_id;
			if(!$parent_id){
				$module_id = $this->get('module_id','int');
				if($rs['module_id'] && $module_id != $rs['module_id'] && count($son_cate_list)>0){
					$this->error(P_Lang('分类已存在子分类，不允许更换模块'));
				}
				$array['module_id'] = $module_id;
			}
			$update = $this->model('cate')->save($array,$id);
			if(!$update){
				$this->error(P_Lang('分类更新失败'),$error_url);
			}
			ext_save("cate-".$id);
		}
		$this->_save_tag($id);
		if(!$parent_id && $id){
			$extfields = $this->get('_extfields');
			if($extfields){
				$extfields = implode(",",$extfields);
				$this->lib('xml')->save(array('fid'=>$extfields),$this->dir_data.'xml/cate_extfields_'.$id.'.xml');
			}else{
				$this->lib('file')->rm($this->dir_data.'xml/cate_extfields_'.$id.'.xml');
			}
		}
		//保存模块内的扩展字段
		if($parent_id && $id){
			$root_id = $parent_id;
			$this->model('cate')->get_root_id($root_id,$parent_id);
			$cate_root = $this->model('cate')->get_one($root_id);
			if($cate_root['module_id']){
				$ext_list = $this->model('module')->fields_all($cate_root["module_id"]);
		 		$tmplist = array();
		 		$tmplist["id"] = $id;
		 		$tmplist["site_id"] = $cate_root["site_id"];
		 		$tmplist["project_id"] = 0;
		 		$tmplist["cate_id"] = $id;
		 		if(!$ext_list){
			 		$ext_list = array();
		 		}
				foreach($ext_list as $key=>$value){
					if($rs[$value['identifier']]){
						$value['content'] = $rs[$value['identifier']];
					}
					$tmplist[$value["identifier"]] = $this->lib('form')->get($value);
				}
				$this->model('cate')->save_ext($tmplist,$cate_root["module_id"]);
			}
		}
		$this->success(P_Lang('分类信息配置成功'),$this->url("cate"));
	}

	private function _save_tag($id)
	{
		$rs = $this->model('cate')->cate_info($id,false);
		$this->model('tag')->update_tag($rs['tag'],'c'.$id);
		return true;
	}

	function son_cate_list(&$son_cate_list,$id)
	{
		$list = $this->model('cate')->get_son_id_list($id);
		if($list){
			foreach($list as $key=>$value){
				$son_cate_list[] = $value;
			}
			$this->son_cate_list($son_cate_list,implode(",",$list));
		}
	}

	/**
	 * 删除分类操作
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
		$idlist = $this->model('cate')->get_son_id_list($id);
		if($idlist){
			$this->error(P_Lang('存在子栏目，不能直接删除，请先删除相应的子栏目'));
		}
		$check_rs = $this->model('project')->chk_cate($id);
		if($check_rs){
			$this->error(P_Lang('分类使用中，请先删除'));
		}
		$this->model('cate')->cate_delete($id);
		$this->success();
	}

	/**
	 * 批量删除分类
	**/
	public function pl_delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$ids = $this->get("ids");
		if(!$ids){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$ids);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				continue;
			}
			$idlist = $this->model('cate')->get_son_id_list($value);
			if($idlist){
				continue;
			}
			$check_rs = $this->model('project')->chk_cate($value);
			if($check_rs){
				continue;
			}
			$this->model('cate')->cate_delete($value);
		}
		$this->success();
	}

	/**
	 * 删除扩展字段
	**/
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
			$this->json(P_Lang('扩展字段删除成功'),true);
		}
		$idstring = $this->session->val('cate_ext_id');
		if($idstring){
			$list = explode(",",$idstring);
			$tmp = array();
			foreach($list AS $key=>$value){
				if($value && $value != $id){
					$tmp[] = $value;
				}
			}
			$new_idstring = implode(",",$tmp);
			$this->session->assign('cate_ext_id',$new_idstring);
		}
		$this->json(P_Lang('扩展字段删除成功'),true);
	}

	/**
	 * 验证分类是否可用
	**/
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
		$check = $this->model('id')->check_id($sign,$this->session->val('admin_site_id'),$id);
		if($check){
			$this->json(P_Lang('标识已被使用'));
		}
		$this->json("标识正常，可以使用",true);
	}

	/**
	 * 分类自定义排序
	**/
	public function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis)){
			$this->json(P_Lang('没有指定要更新的排序'));
		}
		foreach($taxis as $key=>$value){
			$this->model('cate')->update_taxis($key,$value);
		}
		$this->json(P_Lang('数据排序更新成功'),true);
	}
	
	/**
	 * 单个分类自定义排序
	**/
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
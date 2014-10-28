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
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("cate");
		$this->assign("popedom",$this->popedom);
	}

	# 栏目列表
	function index_f()
	{
		if(!$this->popedom["list"]) error("你没有查看权限");
		$rslist = $this->model('cate')->get_all($_SESSION["admin_site_id"]);
		if($rslist)
		{
			foreach($rslist as $key=>$value)
			{
				if($_SESSION['admin_rs']['if_system'])
				{
					$value['popedom'] = array("read"=>true,"edit"=>true,'status'=>true,'delete'=>true,'add'=>true,'e_add'=>true,'e_edit'=>true,'e_delete'=>true);
					$rslist[$key] = $value;
				}
				else
				{
					$cate_popedom = $this->model('admin')->cate_popedom($_SESSION['admin_id']);
					if($cate_popedom[$value['id']])
					{
						$value['popedom'] = $cate_popedom[$value['id']];
						$rslist[$key] = $value;
					}
					else
					{
						unset($rslist[$key]);
					}
				}
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("cate_index");
	}

	# 添加或编辑栏目信息，支持自定义字段
	function set_f()
	{
		$parent_id = $this->get("parent_id","int");
		$id = $this->get("id","int");
		if($id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限");
			$rs = $this->model('cate')->get_one($id);
			$this->assign("id",$id);
			$this->assign("rs",$rs);
			$parent_id = $rs["parent_id"];
			$this->assign("parent_id",$parent_id);
			$ext_module = "cate-".$id;
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限");
			$ext_module = "add-cate";
			$ext_id = $_SESSION[$ext_module."-ext-id"];
			$this->assign("parent_id",$parent_id);
		}
		$this->assign("ext_module",$ext_module);
		$forbid = array("id","identifier");
		$forbid_list = $this->model('ext')->fields("cate,id");
		$forbid = array_merge($forbid,$forbid_list);
		$forbid = array_unique($forbid);
		$this->assign("ext_idstring",implode(",",$forbid));

		# 取得根分类列表
		$parentlist = $this->model('cate')->get_all($_SESSION["admin_site_id"]);
		$parentlist = $this->model('cate')->cate_option_list($parentlist);
		$this->assign("parentlist",$parentlist);
		$this->view("cate_set");
	}

	//添加根分类
	function add_f()
	{
		if(!$this->popedom['add']) error_open("您没有添加分类权限");
		$this->view("cate_open_add");
	}

	//弹出窗写入
	function open_save_f()
	{
		if(!$this->popedom["add"]) json_exit("你没有添加权限");
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		if(!$title || !$identifier) json_exit("信息不完整");
		$identifier = strtolower($identifier);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier))
		{
			json_exit("标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头！");
		}
		$check = $this->model('id')->check_id($identifier,$_SESSION["admin_site_id"]);
		if($check)
		{
			json_exit("标识已被使用");
		}
		$array = array();
		$array["site_id"] = $_SESSION["admin_site_id"];
		$array["parent_id"] = 0;
		$array["title"] = $title;
		$array["taxis"] = 255;
		$array["psize"] = "";
		$array["tpl_list"] = "";
		$array["tpl_content"] = "";
		$array["status"] = 1;
		$array["identifier"] = $identifier;
		$id = $this->model('cate')->save($array);
		if(!$id)
		{
			json_exit("分类添加失败，请检查！");
		}
		json_exit("分类添加成功",true);
	}
	
	# 存储分类信息
	function save_f()
	{
		$id = $this->get("id","int");
		if($id)
		{
			if(!$this->popedom["modify"]) error("你没有编辑权限",$this->url('index'),'error');
		}
		else
		{
			if(!$this->popedom["add"]) error("你没有添加权限",$this->url('index'),'error');
		}
		$title = $this->get("title");
		$identifier = $this->get("identifier");
		$error_url = $this->url("cate","set");
		if($id) $error_url .= "&id=".$id;
		if(!$identifier)
		{
			error("标识不能为空！",$error_url,"error");
		}
		$identifier = strtolower($identifier);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier))
		{
			error("标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头！",$error_url,"error");
		}
		//检测标识串是否被使用了
		$check = $this->model('id')->check_id($identifier,$_SESSION["admin_site_id"],$id);
		if($check) error("标识已被使用！",$error_url,"error");
		$array = array('title'=>$title,'identifier'=>$identifier);
		$array['parent_id'] = $this->get("parent_id","int");
		$array['status'] = $this->get('status','int');
		$array['tpl_list'] = $this->get('tpl_list');
		$array['tpl_content'] = $this->get('tpl_content');
		$array['psize'] = $this->get('psize','int');
		$array['taxis'] = $this->get('taxis','int');
		$array['seo_title'] = $this->get('seo_title');
		$array['seo_keywords'] = $this->get('seo_keywords');
		$array['seo_desc'] = $this->get('seo_desc');
		if(!$id)
		{
			//存储分类信息
			$array["site_id"] = $_SESSION["admin_site_id"];
			$id = $this->model('cate')->save($array);
			if(!$id) error("分类添加失败，请检查！",$error_url);
			//更新扩展表单信息
			ext_save("add-cate-ext-id",true,"cate-".$id);
			//清空临时表
			$this->model('temp')->clean("add-cate",$_SESSION["admin_id"]);
		}
		else
		{
			$parent_id = $this->get('parent_id','int');
			$rs = $this->model('cate')->get_one($id);
			if($parent_id == $id)
			{
				$old_rs = $this->model('cate')->get_one($id);
				$parent_id = $old_rs["id"];
			}
			$son_cate_list = array();
			$this->son_cate_list($son_cate_list,$id);
			if(in_array($parent_id,$son_cate_list))
			{
				error("不允许将分类迁移至此分类下的子分类！",$error_url,"error");
			}
			$array["parent_id"] = $parent_id;
			$update = $this->model('cate')->save($array,$id);
			if(!$update) error("分类更新失败！",$error_url);
			ext_save("cate-".$id);
			$this->model('temp')->clean("cate-".$id,$_SESSION["admin_id"]);
		}
		error("分类信息配置成功！",admin_url("cate"),"ok");
	}

	function son_cate_list(&$son_cate_list,$id)
	{
		$list = $this->model('cate')->get_son_id_list($id);
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				$son_cate_list[] = $value;
			}
			$this->son_cate_list($son_cate_list,implode(",",$list));
		}
	}

	# 删除分类ID
	function delete_f()
	{
		if(!$this->popedom["delete"]) json_exit("你没有删除权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定要删除的ID！");
		}
		# 检查是否有子类
		$idlist = $this->model('cate')->get_son_id_list($id);
		if($idlist)
		{
			json_exit("存在子栏目，不能直接删除，请先删除相应的子栏目！");
		}
		$check_rs = $this->model('project')->chk_cate($id);
		if($check_rs)
		{
			json_exit("分类已被内容：".$check_rs["title"]." 中使用，请先移除");
		}
		$this->model('cate')->cate_delete($id);
		json_exit("删除成功！",true);
	}

	//删除扩展字段
	function ext_delete_f()
	{
		if(!$this->popedom["ext"]) json_exit("你没有删除扩展权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定要删除的ID！");
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$action = $this->model('cate')->cate_ext_delete($cate_id,$id);
		}
		else
		{
			$idstring = $_SESSION["cate_ext_id"];
			if($idstring)
			{
				$list = explode(",",$idstring);
				$tmp = array();
				foreach($list AS $key=>$value)
				{
					if($value && $value != $id)
					{
						$tmp[] = $value;
					}
				}
				$new_idstring = implode(",",$tmp);
				$_SESSION["cate_ext_id"] = $new_idstring;
			}
		}
		json_exit("扩展字段删除成功！",true);
	}

	# 检测标识串是否有被处理
	function check_f()
	{
		$id = $this->get("id","int");
		$sign = $this->get("sign");
		if(!$sign)
		{
			json_exit("标识串不能为空！");
		}
		$sign = strtolower($sign);
		//字符串是否符合条件
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$sign))
		{
			json_exit("标识不符合系统要求，限字母、数字及下划线（中划线）且必须是字母开头！");
		}
		//
		$check = $this->model('id')->check_id($sign,$_SESSION["admin_site_id"],$id);
		if($check)
		{
			$this->json('标识已被使用，请检查！');
		}
		json_exit("标识正常，可以使用",true);
	}

	# 批量更新排序
	function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis))
		{
			json_exit("没有指定要更新的排序！");
		}
		foreach($taxis AS $key=>$value)
		{
			$this->model('cate')->update_taxis($key,$value);
		}
		json_exit("数据排序更新成功！",true);
	}
}
?>
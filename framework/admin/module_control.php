<?php
/***********************************************************
	Filename: {phpok}/admin/module_control.php
	Note	: 模块管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-11-29 20:21
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_control extends phpok_control
{
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->form_list = $this->model('form')->form_all();
		$this->field_list = $this->model('form')->field_all();
		$this->format_list = $this->model('form')->format_all();
		$this->assign('form_list',$this->form_list);
		$this->assign("field_list",$this->field_list);
		$this->assign("format_list",$this->format_list);

		$this->popedom = appfile_popedom("module");
		$this->assign("popedom",$this->popedom);
	}

	function index_f()
	{
		if(!$this->popedom["list"]) error("你没有查看权限");
		$rslist = $this->model('module')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("module_index");
	}

	function set_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$id = $this->lib('trans')->int("id");
		if($id)
		{
			$this->assign("id",$id);
			$rs = $this->model('module')->get_one($id);
			$this->assign("rs",$rs);
		}
		$this->view("module_set");
	}

	function layout_f()
	{
		$id = $this->get("id");
		$btn = '<input type="button" value="关闭" onclick="$.dialog.close()" />';
		if(!$id) error_open("未指定模块ID","error",$btn);
		$rs = $this->model('module')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$layout = array("hits","dateline");
		if($rs["layout"])
		{
			$layout = explode(",",$rs["layout"]);
		}
		$this->assign("layout",$layout);
		$used_list = $this->model('module')->fields_all($id,"identifier");
		if($used_list)
		{
			foreach($used_list AS $key=>$value)
			{
				$value["field_type_name"] = $this->field_list[$value["field_type"]];
				$value["form_type_name"] = $this->form_list[$value["form_type"]];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		$this->view("module_layout");
	}

	function layout_save_f()
	{
		$id = $this->get("id");
		$btn = '<input type="button" value="关闭" onclick="$.dialog.close()" />';
		if(!$id) error_open("未指定模块ID","error",$btn);
		$layout = $this->get("layout");
		if($layout && is_array($layout))
		{
			$layout = implode(",",$layout);
		}
		$array = array("layout"=>$layout);
		$this->model('module')->save($array,$id);
		error_open("后台列表布局设置成功","ok",$btn);
	}

	function copy_f()
	{
		if(!$this->popedom["set"]) json_exit("你没有权限");
		$id = $this->get("id","int");
		if(!$id) json_exit("未指定模块ID");
		$rs = $this->model('module')->get_one($id);
		if(!$rs) json_exit("模块信息不存在");
		$title = $this->get("title");
		if(!$title) $title = $rs["title"]."(复制)";
		$rs["title"] = $title;
		unset($rs["id"]);
		$new_id = $this->model('module')->save($rs);
		if(!$new_id) json_exit("模块复制失败，请检查！");
		$list = $this->model('module')->fields_all($id);
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				unset($value["id"]);
				$value["module_id"] = $new_id;
				if($value["ext"])
				{
					$value["ext"] = stripslashes($value["ext"]);
				}
				$this->model('module')->fields_save($value);
			}
		}
		//更新扩展表信息

		$this->model('module')->create_tbl($new_id);
		$tbl_exists = $this->model('module')->chk_tbl_exists($new_id);
		if(!$tbl_exists)
		{
			json_exit("模块：".$title." 创建表失败，请检查！");
		}
		$rslist = $this->model('module')->fields_all($new_id);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$this->model('module')->create_fields($new_id,$value);
			}
		}
		json_exit("模块复制成功",true);
	}

	//存储或更新模型
	function save_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$id = $this->get("id","int");
		$title = $this->get("title");
		$error_url = $this->url("module","set");
		if($id)
		{
			$error_url = "&id=".$id;
		}
		if(!$title)
		{
			error("模块名称不能为空！",$error_url,"error");
		}
		$note = $this->get("note");
		$taxis = $this->get("taxis","int");
		$array = array("title"=>$title,"note"=>$note,"taxis"=>$taxis);
		if($id)
		{
			$this->model('module')->save($array,$id);
		}
		else
		{
			$array["layout"] = "hits,dateline";
			$id = $this->model('module')->save($array);
		}
		if(!$id)
		{
			error("数据存储失败，请检查！",$error_url,"error");
		}
		//检查模型表是否已创建
		$tbl_exists = $this->model('module')->chk_tbl_exists($id);
		if(!$tbl_exists)
		{
			$this->model('module')->create_tbl($id);
		}
		error("模块数据添加/更新成功！",$this->url("module"));
	}

	//字段管理器
	function fields_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$id = $this->get("id","int");
		if(!$id) error("未指定模型",$this->url("module"),"error");
		$rs = $this->model('module')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$condition = "area LIKE '%module%'";
		$fields_list = $this->model('fields')->get_all($condition,"identifier");
		if($fields_list)
		{
			foreach($fields_list AS $key=>$value)
			{
				$value["field_type_name"] = $this->field_list[$value["field_type"]];
				$value["form_type_name"] = $this->form_list[$value["form_type"]];
				$fields_list[$key] = $value;
			}
		}
		$used_list = $this->model('module')->fields_all($id,"identifier");
		if($used_list)
		{
			foreach($used_list AS $key=>$value)
			{
				$value["field_type_name"] = $this->field_list[$value["field_type"]];
				$value["form_type_name"] = $this->form_list[$value["form_type"]];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		if($fields_list && $used_list)
		{
			$newlist = array();
			foreach($fields_list AS $key=>$value)
			{
				if(!$used_list[$key])
				{
					$newlist[$key] = $value;
				}
			}
			$this->assign("fields_list",$newlist);
		}
		else
		{
			$this->assign("fields_list",$fields_list);
		}
		$this->view("module_fields");
	}

	function field_add_f()
	{
		if(!$this->popedom["set"]) json_exit("你没有权限");
		$id = $this->get("id","int");
		if(!$id) json_exit("未指定模型ID");
		$fid = $this->get("fid");
		if(!$fid) json_exit("未指定要添加的字段ID");
		$rs = $this->model('module')->get_one($id);
		if(!$rs) json_exit("模型不存在");
		//取得fid的内容信息
		$f_rs = $this->model('fields')->get_one($fid);
		if(!$f_rs) json_exit("字段不存在");
		$title = $this->get("title");
		if(!$title) $title = $f_rs["title"];
		$note = $this->get("note");
		if(!$note) $note = $f_rs["note"];
		$tmp_array = array("module_id"=>$id);
		$tmp_array["title"] = $title;
		$tmp_array["note"] = $note;
		$tmp_array["identifier"] = $f_rs["identifier"];
		$tmp_array["field_type"] = $f_rs["field_type"];
		$tmp_array["form_type"] = $f_rs["form_type"];
		$tmp_array["form_style"] = $f_rs["form_style"];
		$tmp_array["format"] = $f_rs["format"];
		$tmp_array["content"] = $f_rs["content"];
		$tmp_array["taxis"] = $f_rs["taxis"];
		$tmp_array["ext"] = "";
		if($f_rs["ext"])
		{
			$tmp_array["ext"] = stripslashes($f_rs["ext"]);
		}
		$this->model('module')->fields_save($tmp_array);
		//更新扩展表信息
		$tbl_exists = $this->model('module')->chk_tbl_exists($id);
		if(!$tbl_exists)
		{
			$this->model('module')->create_tbl($id);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($id);
			if(!$tbl_exists2)
			{
				json_exit("模块：".$rs["title"]." 创建表失败，请检查！");
			}
		}
		$list = $this->model('module')->fields_all($id);
		if($list)
		{
			foreach($list AS $key=>$value)
			{
				$this->model('module')->create_fields($id,$value);
			}
		}
		json_exit("字段添加成功",true);
	}

	//创建扩展字段
	function field_create_f()
	{
		$mid = $this->get('mid','int');
		if(!$mid)
		{
			error('未指定模块ID');
		}
		$m_rs = $this->model('module')->get_one($mid);
		$this->assign('m_rs',$m_rs);
		$fields = $this->model('form')->field_all();
		$formats = $this->model('form')->format_all();
		$forms = $this->model('form')->form_all();
		$this->assign('fields',$fields);
		$this->assign('formats',$formats);
		$this->assign('forms',$forms);
		$this->assign('mid',$mid);
		$this->view('module_field_create');
	}
	
	//删除字段
	function field_delete_f()
	{
		if(!$this->popedom["set"]) json_exit("你没有权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定要删除的字段！");
		}
		$this->model('module')->field_delete($id);
		json_exit("删除成功！",true);
	}

	//删除模块
	function delete_f()
	{
		if(!$this->popedom["set"]) json_exit("你没有删除权限");
		$id = $this->get("id","int");
		if(!$id) json_exit("未指定要删除的模块");
		$rs = $this->model('module')->get_one($id);
		if(!$rs) json_exit("模块不存在");
		if($rs["status"]) json_exit("模块使用中，请先停用模块信息");
		//删除模块操作
		$this->model("module")->delete($id);
		json_exit("模块删除成功",true);
	}

	//通过Ajax执行操作
	function status_f()
	{
		if(!$this->popedom["set"]) json_exit("你没有权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("没有指定ID！");
		}
		$rs = $this->model('module')->get_one($id);
		$status = $rs["status"];
		$status++;
		if($status>2)
		{
			$status = '0';
		}
		$action = $this->model('module')->update_status($id,$status);
		if(!$action)
		{
			json_exit("操作失败，请检查SQL语句！");
		}
		else
		{
			json_exit($status,true);
		}
	}

	//批量更新排序
	function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis))
		{
			json_exit("没有指定要更新的排序！");
		}
		foreach($taxis AS $key=>$value)
		{
			$this->model('module')->update_taxis($key,$value);
		}
		json_exit("数据排序更新成功！",true);
	}

	function field_edit_f()
	{
		if(!$this->popedom["set"]) error_open("你没有权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			error_open("未指定ID");
		}
		$rs = $this->model('module')->field_one($id);
		$this->assign("rs",$rs);
		$m_rs = $this->model('module')->get_one($rs['module_id']);
		$this->assign('m_rs',$m_rs);
		$this->assign("id",$id);
		$this->view("module_field_set");
	}

	function field_edit_save_f()
	{
		if(!$this->popedom["set"]) error("你没有权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			error('未指定ID');
		}
		$rs = $this->model('module')->field_one($id);
		$module_id = $rs['module_id'];
		$title = $this->get("title");
		$note = $this->get("note");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style","html");
		$content = $this->get("content");
		$format = $this->get("format");
		$taxis = $this->get("taxis","int");
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id)
		{
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value)
			{
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox")
				{
					$value = $val[0];
					$ext[$value] = $this->lib('trans')->checkbox($value);
				}
				else
				{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["note"] = $note;
		$array["form_type"] = $form_type;
		$array["form_style"] = $form_style;
		$array["format"] = $format;
		$array["content"] = $content;
		$array["taxis"] = $taxis;
		$array['is_front'] = $this->get('is_front','int');
		$array["ext"] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$this->model('module')->fields_save($array,$id);
		error('自定义字段信息配置成功',$this->url('module','fields','id='.$module_id),'ok');
	}
}
?>
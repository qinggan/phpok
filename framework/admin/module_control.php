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
	private $popedom;
	public function __construct()
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

	public function index_f()
	{
		if(!$this->popedom["list"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$rslist = $this->model('module')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("module_index");
	}

	function set_f()
	{
		if(!$this->popedom["set"]) error(P_Lang('您没有权限执行此操作'));
		$id = $this->get('id','int');
		if($id){
			$this->assign("id",$id);
			$rs = $this->model('module')->get_one($id);
		}else{
			$taxis = $this->model('module')->module_next_taxis();
			$rs = array('taxis'=>$taxis);
		}
		$this->assign("rs",$rs);
		$this->view("module_set");
	}

	function layout_f()
	{
		$id = $this->get("id");
		$btn = '<input type="button" value="'.P_Lang('关闭').'" onclick="$.dialog.close()" />';
		if(!$id) error_open(P_Lang('未指定模块ID'),"error",$btn);
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
		$btn = '<input type="button" value="'.P_Lang('关闭').'" onclick="$.dialog.close()" />';
		if(!$id) error_open(P_Lang('未指定模块ID'),"error",$btn);
		$layout = $this->get("layout");
		if($layout && is_array($layout))
		{
			$layout = implode(",",$layout);
		}else{
			$layout = '';
		}
		$array = array("layout"=>$layout);
		$this->model('module')->save($array,$id);
		error_open(P_Lang('后台列表布局设置成功'),"ok",$btn);
	}

	function copy_f()
	{
		if(!$this->popedom["set"]) $this->json(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定模块ID'));
		$rs = $this->model('module')->get_one($id);
		if(!$rs) $this->json(P_Lang('模块信息不存在'));
		$title = $this->get("title");
		if(!$title) $title = $rs["title"].P_Lang('(复制)');
		$rs["title"] = $title;
		unset($rs["id"]);
		$new_id = $this->model('module')->save($rs);
		if(!$new_id) $this->json(P_Lang('模块复制失败，请检查'));
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
			$this->json(P_Lang('模块创建表失败，请检查'));
		}
		$rslist = $this->model('module')->fields_all($new_id);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				$this->model('module')->create_fields($new_id,$value);
			}
		}
		$this->json(P_Lang('模块复制成功'),true);
	}

	//存储或更新模型
	public function save_f()
	{
		if(!$this->popedom["set"]) error(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		$title = $this->get("title");
		$error_url = $this->url("module","set");
		if($id)
		{
			$error_url = "&id=".$id;
		}
		if(!$title)
		{
			error(P_Lang('模块名称不能为空'),$error_url,"error");
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
			error(P_Lang('数据存储失败，请检查'),$error_url,"error");
		}
		//检查模型表是否已创建
		$tbl_exists = $this->model('module')->chk_tbl_exists($id);
		if(!$tbl_exists)
		{
			$this->model('module')->create_tbl($id);
		}
		error(P_Lang('模块数据添加/更新成功'),$this->url("module"),'ok');
	}

	//字段管理器
	public function fields_f()
	{
		if(!$this->popedom["set"]) error(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id) error(P_Lang('未指定模型'),$this->url("module"),"error");
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

	public function field_add_f()
	{
		if(!$this->popedom["set"]) $this->json(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定模型ID'));
		$fid = $this->get("fid");
		if(!$fid) $this->json(P_Lang('未指定要添加的字段ID'));
		$rs = $this->model('module')->get_one($id);
		if(!$rs) $this->json(P_Lang('模型不存在'));
		//取得fid的内容信息
		$f_rs = $this->model('fields')->get_one($fid);
		if(!$f_rs) $this->json(P_Lang('字段不存在'));
		$title = $this->get("title");
		if(!$title) $title = $f_rs["title"];
		$note = $this->get("note");
		if(!$note) $note = $f_rs["note"];
		$taxis = $this->model('module')->fields_next_taxis($id);
		$tmp_array = array("module_id"=>$id);
		$tmp_array["title"] = $title;
		$tmp_array["note"] = $note;
		$tmp_array["identifier"] = $f_rs["identifier"];
		$tmp_array["field_type"] = $f_rs["field_type"];
		$tmp_array["form_type"] = $f_rs["form_type"];
		$tmp_array["form_style"] = $f_rs["form_style"];
		$tmp_array["format"] = $f_rs["format"];
		$tmp_array["content"] = $f_rs["content"];
		$tmp_array["taxis"] = $taxis;
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
				$this->json(P_Lang('模块创建表失败，请检查'));
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
		$this->json(P_Lang('字段添加成功'),true);
	}

	//创建扩展字段
	public function field_create_f()
	{
		$mid = $this->get('mid','int');
		if(!$mid){
			error(P_Lang('未指定模块ID'));
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
		$taxis = $this->model('module')->fields_next_taxis($mid);
		$this->assign('rs',array('taxis'=>$taxis));
		$this->view('module_field_create');
	}
	
	//删除字段
	public function field_delete_f()
	{
		if(!$this->popedom["set"]) $this->json(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id)
		{
			$this->json(P_Lang('未指定要删除的字段'));
		}
		$this->model('module')->field_delete($id);
		$this->json(P_Lang('删除成功'),true);
	}

	//删除模块
	public function delete_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id) $this->json(P_Lang('未指定要删除的模块'));
		$rs = $this->model('module')->get_one($id);
		if(!$rs) $this->json(P_Lang('模块不存在'));
		if($rs["status"]) $this->json(P_Lang('模块使用中，请先停用模块信息'));
		//删除模块操作
		$this->model("module")->delete($id);
		$this->json(P_Lang('模块删除成功'),true);
	}

	//通过Ajax执行操作
	public function status_f()
	{
		if(!$this->popedom["set"]) $this->json(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id)
		{
			$this->json(P_Lang('没有指定ID'));
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
			$this->json(P_Lang('操作失败，请检查SQL语句'));
		}
		else
		{
			$this->json($status,true);
		}
	}

	//批量更新排序
	public function taxis_f()
	{
		$taxis = $this->lib('trans')->safe("taxis");
		if(!$taxis || !is_array($taxis))
		{
			$this->json(P_Lang('没有指定要更新的排序'));
		}
		foreach($taxis AS $key=>$value)
		{
			$this->model('module')->update_taxis($key,$value);
		}
		$this->json(P_Lang('数据排序更新成功'),true);
	}

	public function field_edit_f()
	{
		if(!$this->popedom["set"]) error_open(P_Lang('您没有权限执行此操作'));
		$id = $this->get("id","int");
		if(!$id)
		{
			error_open(P_Lang('未指定ID'));
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
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('module')->field_one($id);
		$module_id = $rs['module_id'];
		$title = $this->get("title");
		if(!$title){
			$this->json(P_Lang('字段名称不能为空'));
		}
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value){
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox"){
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}else{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}
		$array = array();
		$array["title"] = $title;
		$array["note"] = $this->get("note");
		$array["form_type"] = $this->get("form_type");
		$array["form_style"] = $this->get("form_style","html");
		$array["format"] = $this->get("format");
		$array["content"] = $this->get("content");
		$array["taxis"] = $this->get("taxis","int");
		$array['is_front'] = $this->get('is_front','int');
		$array["ext"] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$array['search'] = $this->get('search','int');
		$array['search_separator'] = $this->get('search_separator');
		$this->model('module')->fields_save($array,$id);
		$this->model('module')->update_fields($id);
		$this->json(true);
	}

	public function field_addok_f()
	{
		if(!$this->popedom['set']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->json(P_Lang('未指定ID'));
		}
		$array = array('module_id'=>$mid);
		$array['title'] = $this->get("title");
		if(!$array['title']){
			$this->json(P_Lang('名称不能为空'));
		}
		$array['note'] = $this->get("note");
		$identifier = $this->get('identifier');
		if(!$identifier){
			$this->json(P_Lang('标识串不能为空'));
		}
		$identifier = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_]+/",$identifier)){
			$this->json(P_Lang('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头'));
		}
		if($identifier == 'phpok'){
			$this->json(P_Lang('phpok是系统禁用字符，请不要使用'));
		}
		$flist = $this->model('fields')->tbl_fields('list');
		if($flist && in_array($identifier,$flist)){
			$this->json(P_Lang('字符已经存在'));
		}
		$flist = $this->model('fields')->tbl_fields('list_'.$mid);
		if($flist && in_array($identifier,$flist)){
			$this->json(P_Lang('字符在扩展表中已使用'));
		}
		$array['identifier'] = $identifier;
		$array['field_type'] = $this->get("field_type");
		$array['form_type'] = $this->get("form_type");
		$array['form_style'] = $this->get("form_style");
		$array['format'] = $this->get("format");
		$array['content'] = $this->get("content");
		$array['taxis'] = $this->get("taxis","int");
		$array['is_front'] = $this->get('is_front','int');
		$array['search'] = $this->get('search','int');
		$array['search_separator'] = $this->get('search_separator');
		$ext_form_id = $this->get("ext_form_id");
		$ext = array();
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list AS $key=>$value){
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox"){
					$value = $val[0];
					$ext[$value] = $this->get($value,"checkbox");
				}else{
					$value = $val[0];
					$ext[$value] = $this->get($value);
				}
			}
		}
		$array['ext'] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$this->model('module')->fields_save($array);
		$tbl_exists = $this->model('module')->chk_tbl_exists($mid);
		if(!$tbl_exists){
			$this->model('module')->create_tbl($mid);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($mid);
			if(!$tbl_exists2){
				$this->json(P_Lang('模块：[title]创建表失败',array('title'=>$rs['title'])));
			}
		}
		$list = $this->model('module')->fields_all($mid);
		if($list){
			foreach($list AS $key=>$value){
				$this->model('module')->create_fields($mid,$value);
			}
		}
		$this->json(true);
	}
}
?>
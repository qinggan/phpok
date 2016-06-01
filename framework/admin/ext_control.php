<?php
/***********************************************************
	Filename: {phpok}/admin/ext_control.php
	Note	: 扩展字段管理
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-05 16:07
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class ext_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
		$this->assign("form_list",$this->model('form')->form_all());
		$this->assign("field_list",$this->model('form')->field_all());
		$this->assign("format_list",$this->model('form')->format_all());
	}

	//创建扩展字段
	public function create_f()
	{
		$id = $this->get('id');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$taxis = $this->model('ext')->ext_next_taxis($id);
		$this->assign('taxis',$taxis);
		$info = explode("-",$id);
		$this->assign('id',$id);
		$this->view('ext_open_create');
	}

	public function save_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$info = explode("-",$id);
		$array = array();
		$array['title'] = $this->get("title");
		if(!$array['title']){
			$this->json(P_Lang('未指定标题'));
		}
		$array['note'] = $this->get("note");
		$array['form_type'] = $this->get("form_type");
		if(!$array['form_type']){
			$this->json(P_Lang('未选择配置表单类型'));
		}
		$array['form_style'] = $this->get("form_style","html");
		$array['content'] = $this->get("content","html");
		$array['format'] = $this->get("format");
		$array['taxis'] = $this->get("taxis","int");
		$ext_form_id = $this->get("ext_form_id");
		$ext = false;
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
			$array['ext'] = ($ext && is_array($ext)) ? serialize($ext) : '';
		}
		$tid = $this->get('tid','int');
		if($tid || !in_array('add',$info)){
			if(!$tid){
				$identifier = $this->get('identifier');
				if(!$identifier){
					$this->json(P_Lang('未指定标识串'));
				}
				if(!$this->model('ext')->check_identifier_add($identifier,$info[1])){
					$this->json(P_Lang('验证码不符合验证要求，请检查'));
				}
				$array['identifier'] = $identifier;
			}
			$array['module'] = $id;
			$this->model('ext')->save($array,$tid);
			$this->json(true);
		}
		$tmpid = $this->get('tmpid');
		if(!$tmpid){
			$identifier = $this->get('identifier');
			if(!$identifier){
				$this->json(P_Lang('未指定标识串'));
			}
			if(!$this->model('ext')->check_identifier_add($identifier,$info[1])){
				$this->json(P_Lang('验证码不符合验证要求，请检查'));
			}
			if($_SESSION['admin-'.$id] && $_SESSION['admin-'.$id][$identifier]){
				$this->json(P_Lang('标识串已被使用'));
			}
		}else{
			$identifier = $tmpid;
		}
		$array['identifier'] = $identifier;
		$_SESSION['admin-'.$id][$identifier] = $array;
		$this->json(true);
	}

	public function float_f()
	{
		$type = $this->get("type");
		$this->assign("type",$type);
		$rslist = $this->model('fields')->fields_list($words,0,999,$type);
		$module = $this->get("module");
		$this->assign("module",$module);
		$this->assign("rslist",$rslist);
		$this->view("ext_float");
	}

	public function select_f()
	{
		$type = $this->get("type");
		$this->assign("type",$type);
		$rslist = $this->model('fields')->fields_list('',0,999,$type);
		$module = $this->get("module");
		$this->assign("module",$module);
		$this->assign("rslist",$rslist);
		$this->view("ext_select");
	}

	public function add_f()
	{
		$id = $this->get("id","int");
		$module = $this->get("module");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$module){
			$this->json(P_Lang('未指哪个模型要添加扩展字段'));
		}
		$tmplist = explode(",",$id);
		$list = explode("-",$module);
		foreach($tmplist as $key=>$value){
			$rs = $this->model('fields')->get_one($value);
			if(!$rs){
				continue;
			}
			if($list[0] == "add"){
				if($_SESSION['admin-'.$module] && $_SESSION['admin-'.$module][$rs['identifier']]){
					continue;
				}
				unset($rs['id']);
				$_SESSION['admin-'.$module][$rs['identifier']] = $rs;
			}else{
				$chk_rs = $this->model('ext')->check_identifier($rs["identifier"],$module);
				if($chk_rs){
					continue;
				}
				$array = array();
				$array["module"] = $module;
				$array["title"] = $rs['title'];
				$array["identifier"] = $rs['identifier'];
				$array["field_type"] = $rs['field_type'];
				$array["note"] = $rs['note'];
				$array["form_type"] = $rs['form_type'];
				$array["form_style"] = $rs["form_style"];
				$array["format"] = $rs["format"];
				$array["content"] = $rs["content"];
				$array["taxis"] = $rs["taxis"];
				$array["ext"] = $rs["ext"] ? serialize(unserialize($rs["ext"])) : "";
				$this->model('ext')->save($array);
			}
		}
		$this->json(true);
	}

	public function delete_f()
	{
		$id = $this->get("id");
		$module = $this->get("module");
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$module){
			$this->json(P_Lang('未指哪个模型要添加扩展字段'));
		}
		$list = explode("-",$module);
		if($list[0] == "add"){
			if($_SESSION['admin-'.$module] && $_SESSION['admin-'.$module][$id]){
				unset($_SESSION['admin-'.$module][$id]);
			}
			$this->json(true);
		}
		$this->model('ext')->delete($id,$module,'identifier');
		$this->json(true);
	}

	public function load_f()
	{
		$module = $this->get("module");
		if(!$module){
			$this->json(P_Lang('未指定模块'));
		}
		if(substr($module,0,3) == "add"){
			$idstring = $_SESSION[$module.'-ext-id'];
			$show_edit = false;
			$rslist = $this->model('fields')->get_list($idstring);
		}else{
			$show_edit = true;
			$rslist = $this->model('ext')->ext_all($module);
		}
		$this->lib("form")->cssjs();
		$list = array();
		$words = $this->get("words");
		$idlist = $words ? explode(",",$words) : array("id","identifier");
		foreach($rslist AS $key=>$value){
			$idlist[] = strtolower($value["identifier"]);
			$list[] = $this->lib('form')->format($value);
		}
		$idlist = array_unique($idlist);
		$this->assign("module",$module);
		$this->assign("show_edit",$show_edit);
		$this->assign("rslist",$list);
		$content = $this->fetch("ext_load");
		$array = array("words"=>implode(",",$idlist),"content"=>$content);
		$this->json($array,true);
	}

	# 编辑扩展属性
	public function edit_f()
	{
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$module = $this->get("module");
		if(!$module){
			error(P_Lang('未指定模块'));
		}
		$info = explode('-',$module);
		if(in_array('add',$info)){
			if($_SESSION['admin-'.$module] && $_SESSION['admin-'.$module][$id]){
				$rs = $_SESSION['admin-'.$module][$id];
			}
		}else{
			$rs = $this->model('ext')->get_from_identifier($id,$module);
			$this->assign('tid',$rs['id']);
		}
		if(!$rs){
			error(P_Lang('自定义字段不存在！'));
		}
		$this->assign("module",$module);
		$this->assign("rs",$rs);
		$this->assign('tmpid',$id);
		$this->view("ext_edit");
	}
}
?>
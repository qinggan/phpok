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
	function __construct()
	{
		parent::control();
		$this->assign("form_list",$this->model('form')->form_all());
		$this->assign("field_list",$this->model('form')->field_all());
		$this->assign("format_list",$this->model('form')->format_all());
	}

	# 读取fields里的页面有效字段，Float
	function float_f()
	{
		$psize = 40;
		$pageid = $this->get("pageid","int");
		if(!$pageid) $pageid = 1;
		$offset = ($pageid - 1) * $psize;
		$words = $this->get("words");
		$this->assign("words",$words);
		$this->assign("pageid",$pageid);
		$bd = $words ? explode(",",$words) : array("id","identifier");
		$type = $this->get("type");
		$this->assign("type",$type);
		$rslist = $this->model('fields')->fields_list($words,$offset,$psize,$type);
		$module = $this->get("module");
		$this->assign("module",$module);
		$this->assign("rslist",$rslist);
		$total = $this->model('fields')->fields_count($words,$type);
		$total_page = intval($total/$psize);
		if($total%$psize)
		{
			$total_page++;
		}
		if($total_page>5)
		{
			$pagelist = array();
			if($pageid == 1)
			{
				$pagelist[0] = array("id"=>"1","title"=>"1");
				$pagelist[1] = array("id"=>"2","title"=>"2");
				$pagelist[2] = array("id"=>"3","title"=>"3");
				$pagelist[3] = array("id"=>"2","title"=>"&gt;");
				$pagelist[4] = array("id"=>$total_page,"title"=>"&gt;&gt;");
			}
			elseif($pageid == 2)
			{
				$pagelist[0] = array("id"=>"1","title"=>"&lt;&lt;");
				$pagelist[1] = array("id"=>"1","title"=>"1");
				$pagelist[2] = array("id"=>"2","title"=>"2");
				$pagelist[3] = array("id"=>($pageid+1),"title"=>"&gt;");
				$pagelist[4] = array("id"=>$total_page,"title"=>"&gt;&gt;");
			}
			elseif($pageid == $total_page)
			{
				$pagelist[0] = array("id"=>"1","title"=>"&lt;&lt;");
				$pagelist[1] = array("id"=>($total_page-1),"title"=>"&lt;");
				$pagelist[2] = array("id"=>($total_page-2),"title"=>($total_page-2));
				$pagelist[3] = array("id"=>($total_page-1),"title"=>($total_page-1));
				$pagelist[4] = array("id"=>$total_page,"title"=>$total_page);
			}
			elseif($pageid == ($total_page -1) )
			{
				$pagelist[0] = array("id"=>"1","title"=>"&lt;&lt;");
				$pagelist[1] = array("id"=>($total_page-1),"title"=>"&lt;");
				$pagelist[2] = array("id"=>($total_page-2),"title"=>($total_page-2));
				$pagelist[3] = array("id"=>($total_page-1),"title"=>($total_page-1));
				$pagelist[4] = array("id"=>$total_page,"title"=>"&gt;&gt;");
			}
			else
			{
				$pagelist[0] = array("id"=>"1","title"=>"&lt;&lt;");
				$pagelist[1] = array("id"=>($pageid-1),"title"=>"&lt;");
				$pagelist[2] = array("id"=>$pageid,"title"=>$pageid);
				$pagelist[3] = array("id"=>($pageid+1),"title"=>"&gt;");
				$pagelist[4] = array("id"=>$total_page,"title"=>"&gt;&gt;");
			}
		}
		else
		{
			for($i = 0;$i<$total_page;$i++)
			{
				$m = $i + 1;
				$pagelist[$i] = array("id"=>$m,"title"=>$m);
			}
		}
		$this->assign("pagelist",$pagelist);
		$this->view("ext_float");
	}

	# 添加存储字段
	function add_f()
	{
		$id = $this->get("id","int");
		$module = $this->get("module");
		if(!$id)
		{
			json_exit("未指定要添加的ID！");
		}
		if(!$module)
		{
			json_exit("未指哪个模型要添加扩展字段");
		}
		$rs = $this->model('fields')->get_one($id);
		if(!$rs)
		{
			json_exit("没有相关字段内容");
		}
		$list = explode("-",$module);
		if($list[0] == "add")
		{
			$idstring = $_SESSION[$module.'-ext-id'];
			$idstring = $idstring ? $idstring.",".$id : $id;
			$_SESSION[$module."-ext-id"] = $idstring;
		}
		else
		{
			//检测这个字段是否已被使用
			$chk_rs = $this->model('ext')->check_identifier($rs["identifier"],$module);
			if($chk_rs)
			{
				json_exit("字段标识已被使用");
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
			$this->model('ext')->ext_save($array);
		}
		json_exit("添加成功！",true);
	}

	# 删除扩展字段
	function delete_f()
	{
		$id = $this->get("id","int");
		$module = $this->get("module");
		if(!$id)
		{
			json_exit("未指定要删除的ID！");
		}
		if(!$module)
		{
			json_exit("未指哪个模型要添加扩展字段");
		}
		$list = explode("-",$module);
		if($list[0] == "add")
		{
			$idstring = $_SESSION[$module."-ext-id"];
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
				$_SESSION[$module."-ext-id"] = $new_idstring;
			}
		}
		else
		{
			$this->model('ext')->ext_delete($id,$module);
		}
		json_exit("扩展字段删除成功！",true);
	}

	# 加载已存在的扩展项
	function load_f()
	{
		$module = $this->get("module");
		if(!$module)
		{
			json_exit("未指定模块");
		}
		if(substr($module,0,3) == "add")
		{
			$idstring = $_SESSION[$module.'-ext-id'];
			$show_edit = false;
			$rslist = $this->model('fields')->get_list($idstring);
		}
		else
		{
			$show_edit = true;
			$rslist = $this->model('ext')->ext_all($module);
		}
		$this->lib("form")->cssjs();
		$list = array();
		$words = $this->get("words");
		$idlist = $words ? explode(",",$words) : array("id","identifier");
		foreach($rslist AS $key=>$value)
		{
			$idlist[] = strtolower($value["identifier"]);
			$list[] = $this->lib('form')->format($value);
		}
		$idlist = array_unique($idlist);
		$this->assign("module",$module);
		$this->assign("show_edit",$show_edit);
		$this->assign("rslist",$list);
		$content = $this->fetch("ext_load");
		$array = array("words"=>implode(",",$idlist),"content"=>$content);
		json_exit($array,true);
	}

	# 编辑扩展属性
	function edit_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			error_open("未指定ID！");
		}
		$module = $this->get("module");
		$rs = $this->model('ext')->get_one($id);
		if(!$rs)
		{
			error_open("自定义字段不存在！");
		}
		$this->assign("module",$module);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->view("ext_edit");
	}

	# 存储扩展的编辑
	function edit_save_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			error_open("未指定ID");
		}
		$title = $this->get("title");
		$note = $this->get("note");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style","html");
		$content = $this->get("content","html");
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
					$ext[$value] = $this->get($value,"checkbox");
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
		$array["ext"] = ($ext && count($ext)>0) ? serialize($ext) : "";
		$this->model('ext')->save($array,$id);
		$html = '<input type="button" value=" 确定 " class="submit" onclick="$.dialog.close();" />';
		error_open("自定义字段信息配置成功！","ok",$html);
	}
}
?>
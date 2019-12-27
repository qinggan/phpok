<?php
/**
 * 模块管理器
 * @作者 qinggan <admin@phpok.com>
 * @版权 深圳市锟铻科技有限公司
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @授权 http://www.phpok.com/lgpl.html 开源授权协议：GNU Lesser General Public License
 * @时间 2019年3月3日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class module_control extends phpok_control
{
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->form_list = $this->model('form')->form_all(true);
		$this->field_list = $this->model('form')->field_all(true);
		$this->format_list = $this->model('form')->format_all(true);
		$this->assign('form_list',$this->form_list);
		$this->assign("field_list",$this->field_list);
		$this->assign("format_list",$this->format_list);

		$this->popedom = appfile_popedom("module");
		$this->assign("popedom",$this->popedom);
	}

	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('module')->get_all();
		if($rslist){
			$projects = $cates = array();
			foreach($rslist as $key=>$value){
				if($value['tbl'] == 'cate'){
					$cates[] = $value['id'];
				}else{
					$projects[] = $value['id'];
				}
			}
			if($projects){
				$plist = $this->model('project')->projects_include_modules($projects);
				if($plist){
					foreach($rslist as $key=>$value){
						if($plist[$value['id']]){
							$rslist[$key]['link'] = $plist[$value['id']];
						}
					}
				}
			}
			if($cates){
				$clist = $this->model('cate')->cates_include_modules($cates);
				if($clist){
					foreach($rslist as $key=>$value){
						if($clist[$value['id']]){
							$rslist[$key]['link'] = $clist[$value['id']];
						}
					}
				}
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("module_index");
	}

	public function set_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id','int');
		if($id){
			$this->assign("id",$id);
			$rs = $this->model('module')->get_one($id);
			$layout = !$rs['mtype'] ? array("hits","dateline") : array();
			if($rs["layout"]){
				$layout = explode(",",$rs["layout"]);
			}
			$this->assign("layout",$layout);
			$used_list = $this->model('module')->fields_all($id,"identifier");
			if($used_list){
				foreach($used_list as $key=>$value){
					$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
					$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
					$used_list[$key] = $value;
				}
			}
			$this->assign("used_list",$used_list);
		}else{
			$taxis = $this->model('module')->module_next_taxis();
			$rs = array('taxis'=>$taxis);
		}
		$this->assign("rs",$rs);
		$tblist = $this->model('module')->tblist();
		$this->assign('tblist',$tblist);
		$tblid = 'list';
		if($rs && $rs['tbl']){
			$tblid = $rs['tbl'];
		}
		$this->assign('tblid',$tblid);
		$this->view("module_set");
	}

	public function layout_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定模块ID'));
		}
		$rs = $this->model('module')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$layout = !$rs['mtype'] ? array("hits","dateline") : array();
		if($rs["layout"]){
			$layout = explode(",",$rs["layout"]);
		}
		$this->assign("layout",$layout);
		$used_list = $this->model('module')->fields_all($id,"identifier");
		if($used_list){
			foreach($used_list AS $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		$this->view("module_layout");
	}

	public function layout_save_f()
	{
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定模块ID'));
		}
		$layout = $this->get("layout");
		if($layout && is_array($layout)){
			$layout = implode(",",$layout);
		}else{
			$layout = '';
		}
		$array = array("layout"=>$layout);
		$this->model('module')->save($array,$id);
		$this->success();
	}

	/**
	 * 模块复制
	 * @参数 id 要复制的模块ID
	 * @参数 title 新的模块名称
	**/
	public function copy_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定模块ID'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模块信息不存在'));
		}
		$title = $this->get("title");
		if(!$title){
			$title = $rs["title"].P_Lang('(复制)');
		}
		$rs["title"] = $title;
		unset($rs["id"]);
		$new_id = $this->model('module')->save($rs);
		if(!$new_id){
			$this->error(P_Lang('模块复制失败，请检查'));
		}
		$list = $this->model('module')->fields_all($id);
		if($list){
			foreach($list AS $key=>$value){
				unset($value["id"]);
				$value["ftype"] = $new_id;
				if($value["ext"]){
					$value["ext"] = stripslashes($value["ext"]);
				}
				$this->model('module')->fields_save($value);
			}
		}
		$this->model('module')->create_tbl($new_id);
		$tbl_exists = $this->model('module')->chk_tbl_exists($new_id,$rs['mtype']);
		if(!$tbl_exists){
			$this->error(P_Lang('模块创建表失败，请检查'));
		}
		$rslist = $this->model('module')->fields_all($new_id);
		if($rslist){
			foreach($rslist as $key=>$value){
				$this->model('module')->create_fields($value['id']);
			}
		}
		$this->success();
	}

	/**
	 * 存储或更新模型
	**/
	public function save_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		$title = $this->get("title");
		if(!$title){
			$this->error(P_Lang('模块名称不能为空'));
		}
		$note = $this->get("note");
		$taxis = $this->get("taxis","int");
		$array = array("title"=>$title,"note"=>$note,"taxis"=>$taxis);
		if($id){
			$old = $this->model('module')->get_one($id);
			$layout = $this->get("layout");
			if($layout && is_array($layout)){
				$array['layout'] = implode(",",$layout);
			}else{
				$array['layout'] = '';
			}
			$array["mtype"] = $this->get('mtype','int');
			$array['tbl'] = $this->get('tbl');
			if($array['mtype'] != $old['mtype'] || $array['tbl'] != $old['tbl']){
				//检测是否已被使用了
				$chk = $this->model('project')->projects_include_modules($id);
				if($chk){
					$this->error(P_Lang('模块已使用，不允许更换类型'));
				}
			}
			if($array['mtype']){
				$array['tbl'] = 'list';
			}
			$this->model('module')->save($array,$id);
			$oldtbl = $old['mtype'] ? $id : $old['tbl'].'_'.$id;
			$mytbl = $oldtbl;
			if($array["mtype"] != $old['mtype']){
				$mytbl = $array["mtype"] ? $id : $array['tbl'].'_'.$id;
			}else{
				if(!$array['mtype']){
					$mytbl = $array['tbl'].'_'.$id;
				}
			}
			if($oldtbl != $newtbl){
				$this->model('module')->rename_tbl($oldtbl,$mytbl);
			}
		}else{
			$array["layout"] = "hits,dateline,sort";
			$array["mtype"] = $this->get("mtype",'int');
			if(!$array['mtype']){
				$array['tbl'] = $this->get('tbl');
			}
			$id = $this->model('module')->save($array);
		}
		if(!$id){
			$this->error(P_Lang('数据存储失败，请检查'));
		}
		$this->model('module')->create_tbl($id);
		$this->success();
	}

	/**
	 * 字段管理器
	**/
	public function fields_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定模型'),$this->url("module"));
		}
		$rs = $this->model('module')->get_one($id);
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$condition = "area LIKE '%module%'";
		$fields_list = $this->model('fields')->default_all();
		if($fields_list){
			foreach($fields_list as $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$fields_list[$key] = $value;
			}
		}
		$used_list = $this->model('module')->fields_all($id,"identifier");
		if($used_list){
			foreach($used_list as $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$value['format_type_name'] = $this->format_list[$value['format']]['title'];
				$used_list[$key] = $value;
			}
		}
		$this->assign("used_list",$used_list);
		if($fields_list && $used_list){
			$newlist = array();
			foreach($fields_list AS $key=>$value){
				if(!$used_list[$key]){
					$newlist[$key] = $value;
				}
			}
			$this->assign("fields_list",$newlist);
		}else{
			$this->assign("fields_list",$fields_list);
		}
		$this->view("module_fields");
	}

	public function field_add_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定模型ID'));
		}
		$fid = $this->get("fid");
		if(!$fid){
			$this->error(P_Lang('未指定要添加的字段ID'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模型不存在'));
		}
		//取得fid的内容信息
		$f_rs = $this->model('fields')->default_one($fid);
		if(!$f_rs){
			$this->error(P_Lang('字段不存在'));
		}
		$title = $this->get("title");
		if(!$title){
			$title = $f_rs["title"];
		}
		$note = $this->get("note");
		if(!$note){
			$note = $f_rs["note"];
		}
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
		if($f_rs["ext"]){
			$tmp_array["ext"] = serialize($f_rs['ext']);
		}
		$this->model('module')->fields_save($tmp_array);

		$tbl_exists = $this->model('module')->chk_tbl_exists($id,$rs['mtype'],$rs['tbl']);
		if(!$tbl_exists){
			$this->model('module')->create_tbl($id);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($id,$rs['mtype'],$rs['tbl']);
			if(!$tbl_exists2){
				$this->error(P_Lang('模块：[title]创建表失败',array('title'=>$rs['title'])));
			}
		}
		$list = $this->model('module')->fields_all($id);
		if($list){
			foreach($list as $key=>$value){
				if($flist && in_array($value['identifier'],$flist)){
					continue;
				}
				$this->model('module')->create_fields($value['id']);
			}
		}

		$this->success();
	}

	/**
	 * 创建扩展字段
	**/
	public function field_create_f()
	{
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->error(P_Lang('未指定模块ID'));
		}
		$m_rs = $this->model('module')->get_one($mid);
		$this->assign('m_rs',$m_rs);
		$this->assign('mid',$mid);
		$taxis = $this->model('module')->fields_next_taxis($mid);
		$this->assign('rs',array('taxis'=>$taxis));
		$this->view('module_field_create');
	}
	
	/**
	 * 删除字段
	**/
	public function field_delete_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定要删除的字段'));
		}
		$this->model('module')->field_delete($id);
		$this->success();
	}

	/**
	 * 删除模块
	**/
	public function delete_f()
	{
		if(!$this->popedom['set']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定要删除的模块'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模块不存在'));
		}
		if($rs["status"] == '1'){
			$this->error(P_Lang('模块使用中，请先停用模块信息'));
		}
		$this->model("module")->delete($id);
		$this->success();
	}

	/**
	 * 更新模块状态
	**/
	public function status_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('没有指定ID'));
		}
		$rs = $this->model('module')->get_one($id);
		$status = $rs["status"];
		$status++;
		if($status>2){
			$status = '0';
		}
		$action = $this->model('module')->update_status($id,$status);
		if(!$action){
			$this->error(P_Lang('操作失败，请检查SQL语句'));
		}
		$this->success($status);
	}

	/**
	 * 更新模块排序
	 * @参数 $id，数值，要排序的模块ID
	 * @参数 $taxis，排序值
	 * @返回 JSON
	 * @更新时间 2016年07月12日
	**/
	public function taxis_f()
	{
		$taxis = $this->get('taxis','int');
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->model('module')->update_taxis($id,$taxis);
		$this->success();
	}

	public function field_edit_f()
	{
		if(!$this->popedom["set"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('module')->field_one($id);
		$this->assign("rs",$rs);
		$m_rs = $this->model('module')->get_one($rs['module_id']);
		$this->assign('m_rs',$m_rs);
		$this->assign("id",$id);
		$this->view("module_field_set");
	}

	/**
	 * 保存更新的字段排序
	 * @参数 $id，要排序的字段ID
	 * @参数 $taxis，排序值
	 * @返回 JSON数据
	 * @更新时间 2016年07月12日
	**/
	public function field_taxis_f()
	{
		$taxis = $this->get('taxis','int');
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定要编辑的ID'));
		}
		$array = array('taxis'=>$taxis);
		$this->model('module')->fields_save($array,$id);
		$this->success();
	}

	public function field_edit_save_f()
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
		$array['field_type'] = $this->get('field_type');
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
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$mid = $this->get('mid','int');
		if(!$mid){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('module')->get_one($mid);
		if(!$rs){
			$this->error(P_Lang('模块信息不存在'));
		}
		$array = array('module_id'=>$mid);
		$array['title'] = $this->get("title");
		if(!$array['title']){
			$this->error(P_Lang('名称不能为空'));
		}
		$array['note'] = $this->get("note");
		$identifier = $this->get('identifier');
		if(!$identifier){
			$this->error(P_Lang('标识串不能为空'));
		}
		$identifier = strtolower($identifier);
		if(!preg_match("/^[a-z][a-z0-9\_]+$/u",$identifier)){
			$this->error(P_Lang('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头'));
		}
		if($identifier == 'phpok'){
			$this->error(P_Lang('phpok是系统禁用字符，请不要使用'));
		}
		if(!$rs['mtype']){
			$tbl = $rs['tbl'] ? $rs['tbl'] : 'list';
			$flist = $this->model('fields')->tbl_fields($tbl);
			if($flist && in_array($identifier,$flist)){
				$this->json(P_Lang('字符已经存在'));
			}
		}
		$tblname = $rs['mtype'] ? $mid : $rs['tbl'].'_'.$mid;
		$flist = $this->model('fields')->tbl_fields($tblname);
		if($flist && in_array($identifier,$flist)){
			$this->error(P_Lang('字符在扩展表中已使用'));
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
		$tbl_exists = $this->model('module')->chk_tbl_exists($mid,$rs['mtype'],$rs['tbl']);
		if(!$tbl_exists){
			$this->model('module')->create_tbl($mid);
			$tbl_exists2 = $this->model('module')->chk_tbl_exists($mid,$rs['mtype'],$rs['tbl']);
			if(!$tbl_exists2){
				$this->error(P_Lang('模块：[title]创建表失败',array('title'=>$rs['title'])));
			}
		}
		$list = $this->model('module')->fields_all($mid);
		if($list){
			foreach($list as $key=>$value){
				if($flist && in_array($value['identifier'],$flist)){
					continue;
				}
				$this->model('module')->create_fields($value['id']);
			}
		}
		$this->success();
	}

	/**
	 * 导出模块字段，此项仅用于导出XML配置文件，如果模块中绑定主题或其他一些选项，在这里不会被体现，需要您手动再绑定
	 * @参数 $id，要导出的模块字段ID
	**/
	public function export_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'),$this->url('module'));
		}
		$rs = $this->model('module')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('模块数据不存在'),$this->url('module'));
		}
		unset($rs['id']);
		$rslist = $this->model('module')->fields_all($id,'identifier');
		if($rslist){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				unset($value['id'],$value['module_id']);
				if($value['ext']){
					$value['ext'] = unserialize($value['ext']);
				}
				$tmplist[$key] = $value;
			}
			$rs['_fields'] = $tmplist;
		}
		//将数据写成XML
		$tmpfile = $this->dir_cache.'module.xml';
		$this->lib('xml')->save($rs,$tmpfile);
		$this->lib('phpzip')->set_root($this->dir_cache);
		$zipfile = $this->dir_cache.$this->time.'.zip';
		$this->lib('phpzip')->zip($tmpfile,$zipfile);
		$this->lib('file')->rm($tmpfile);
		//下载zipfile
		$this->lib('file')->download($zipfile,$rs['title']);
	}

	/**
	 * 模块导入
	 * @变量 zipfile 指定的ZIP文件地址
	**/
	public function import_f()
	{
		$zipfile = $this->get('zipfile');
		if(!$zipfile){
			$this->lib('form')->cssjs(array('form_type'=>'upload'));
			$this->addjs('js/webuploader/admin.upload.js');
			$this->view('module_import');
		}
		if(strpos($zipfile,'..') !== false){
			$this->error(P_Lang('不支持带..上级路径'));
		}
		if(!file_exists($this->dir_root.$zipfile)){
			$this->error(P_Lang('ZIP文件不存在'));
		}
		$this->lib('phpzip')->unzip($this->dir_root.$zipfile,$this->dir_cache);
		if(!file_exists($this->dir_cache.'module.xml')){
			$this->error(P_Lang('导入模块失败，请检查解压缩是否成功'));
		}
		$rs = $info = $this->lib('xml')->read($this->dir_cache.'module.xml',true);
		if(!$rs){
			$this->error(P_Lang('XML内容解析异常'));
		}
		$tmp = $rs;
		if(isset($tmp['_fields'])){
			unset($tmp['_fields']);
		}		
		$insert_id = $this->model('module')->save($tmp);
		if(!$insert_id){
			$this->error(P_Lang('模块导入失败，保存模块基本信息错误'));
		}
		$this->model('module')->create_tbl($insert_id);
		$tbl_exists = $this->model('module')->chk_tbl_exists($insert_id,$tmp['mtype'],$tmp['tbl']);
		if(!$tbl_exists){
			$this->model('module')->delete($insert_id);
			$this->error(P_Lang('创建模块表失败'));
		}
		if(isset($rs['_fields']) && $rs['_fields'] && is_array($rs['_fields'])){
			foreach($rs['_fields'] as $key=>$value){
				$value['ftype'] = $insert_id;
				$tmpid = $this->model('module')->fields_save($value);
				if($tmpid){
					$this->model('module')->create_fields($tmpid);
				}
			}
		}
		$this->lib('file')->rm($this->dir_cache.'module.xml');
		$this->lib('file')->rm($this->dir_root.$zipfile);
		$this->success();
	}
}
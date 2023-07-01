<?php
/**
 * 常用字段管理器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2023年3月7日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class fields_control extends phpok_control
{
	private $form_list;
	private $field_list;
	private $popedom;
	public function __construct()
	{
		parent::control();
		$this->form_list = $this->model('form')->form_all(true);
		$this->field_list = $this->model('form')->field_all(true);
		$this->format_list = $this->model('form')->format_all(true);
		$this->assign("field_list",$this->field_list);
		$this->assign("form_list",$this->form_list);
		$this->assign("format_list",$this->format_list);
		$this->popedom = appfile_popedom("fields");
		$this->assign("popedom",$this->popedom);
	}

	/**
	 * 取得全部常用字段列表
	**/
	public function index_f()
	{
		if(!$this->popedom["list"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$rslist = $this->model('fields')->default_all();
		if($rslist){
			foreach($rslist as $key=>$value){
				$value["field_type_name"] = $this->field_list[$value["field_type"]]['title'];
				$value["form_type_name"] = $this->form_list[$value["form_type"]]['title'];
				$rslist[$key] = $value;
			}
		}
		$this->assign("rslist",$rslist);
		$this->view("fields_index");
	}

	/**
	 * 添加字段
	**/
	public function set_f()
	{
		$id = $this->get("id");
		if($id){
			if(!$this->popedom["modify"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$rs = $this->model('fields')->default_one($id);
			if($rs['ext'] && is_array($rs['ext'])){
				foreach($rs['ext'] as $key=>$value){
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
			$this->assign("id",$id);
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
		}
		$opt_list = $this->model('opt')->group_all();
		$this->assign("opt_list",$opt_list);
		$this->view("fields_set");
	}

	/**
	 * 保存表单信息
	**/
	public function save_f()
	{
		$id = $this->get('id','system');
		if($id){
			if(!$this->popedom['modify']){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$identifier = $id;
		}else{
			if(!$this->popedom["add"]){
				$this->error(P_Lang('您没有权限执行此操作'));
			}
			$identifier = $this->get('identifier');
			if(!$identifier){
				$this->error(P_Lang('字段标识不能为空'));
			}
			$identifier = strtolower($identifier);
			if(!preg_match("/^[a-z][a-z0-9\_]+$/u",$identifier)){
				$this->error(P_Lang('字段标识不符合系统要求，限字母、数字及下划线且必须是字母开头'));
			}
			//检测标识是否存在
			$chk = $this->model('fields')->default_one($identifier);
			if($chk){
				$this->error(P_Lang('标识已被使用'));
			}
		}
		$title = $this->get("title");
		$note = $this->get("note");
		$field_type = $this->get("field_type");
		$form_type = $this->get("form_type");
		$form_style = $this->get("form_style");
		$format = $this->get("format");
		$content = $this->get("content");
		$ext_form_id = $this->get("ext_form_id");
		$array = array();
		$array["title"] = $title;
		$array["identifier"] = $identifier;
		$array["field_type"] = $field_type;
		$array["note"] = $note;
		$array["form_type"] = $form_type;
		$array["form_style"] = $form_style;
		$array["format"] = $format;
		$array["content"] = $content;
		$array['group_id'] = 'main';
		if($ext_form_id){
			$list = explode(",",$ext_form_id);
			foreach($list as $key=>$value){
				$val = explode(':',$value);
				if($val[1] && $val[1] == "checkbox"){
					$value = $val[0];
					$array[$value] = $this->get($value,"checkbox");
				}else{
					$value = $val[0];
					$array[$value] = $this->get($value,'html_js');
				}
			}
		}
		$this->model('fields')->default_save($array);
		$this->success();
	}

	public function delete_f()
	{
		if(!$this->popedom['delete']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定字段ID'));
		}
		$this->model('fields')->default_delete($id);
		$this->success();
	}

	public function config_f()
	{
		$id = $this->get("id");
		if(!$id){
			exit(P_Lang('未指定ID'));
		}
		$eid = $this->get("eid");
		if($eid){
			$rs = $this->model('fields')->default_one($eid);
			if($rs && $rs['ext'] && is_array($rs['ext'])){
				foreach($rs['ext'] as $key=>$value){
					$rs[$key] = $value;
				}
			}
			$this->assign("rs",$rs);
		}
		$this->lib('form')->config($id);
	}

	/**
	 * 自定义宽度保留
	**/
	public function width_f()
	{
		$field = $this->get('field');
		$width = $this->get('width','int');
		$mid = $this->get('mid');
		if(!$field || !$mid){
			$this->error(P_Lang('参数不完整'));
		}
		$condition = "identifier='".$field."' AND ftype='".$mid."'";
		$chk = $this->model('fields')->get_all($condition);
		if(!$chk){
			$this->success();
		}
		if(count($chk)>1){
			$this->error(P_Lang('系统异常，同一模块中存在多字段，请检查'));
		}
		$rs = current($chk);
		$data = array("admin-list-width"=>$width);
		$this->model('fields')->save($data,$rs['id']);
		$this->success();
	}

	public function filemanage_f()
	{
		$input = $this->get('input');
		if(!$input){
			$this->error(P_Lang('未指定目标框'));
		}
		$type = $this->get('type');
		if(!$type){
			$type = '*';
		}
		$ext_length = strlen($type);
		$baseurl = $this->url('fields','filemanage','type='.rawurlencode($type)."&input=".$input);
		$folder = $this->get('folder');
		if(!$folder){
			$folder = "/";
		}
		if(substr($folder,-1) != '/'){
			$folder .= "/";
		}
		$tmplist = explode("/",$folder);
		$leadlist = array();
		$leadlist[0] = array('title'=>P_Lang('根目录'),'url'=>$baseurl);
		$tmplist = explode("/",$folder);
		$str = '';
		foreach($tmplist as $key=>$value){
			if(!$value || !trim($value)){
				continue;
			}
			$str .= $value."/";
			$leadurl = $baseurl.'&folder='.rawurlencode($str);
			$leadlist[] = array('title'=>basename($value),'url'=>$leadurl);
		}
		$this->assign('leadlist',$leadlist);
		$this->assign("folder",$folder);
		if($folder == '/'){
			$dir = $this->dir_root;
		}else{
			$dir = $this->dir_root.$folder;
		}
		if(substr($dir,-1) == '/'){
			$dir = substr($dir,0,-1);
		}
		$rslist = $this->lib('file')->ls($dir);
		$this->assign('url',$baseurl);
		$this->assign('input',$input);
		if(!$rslist){
			$this->view('fields_template');
		}
		$dirlist = array();
		foreach($rslist as $key=>$value){
			if(is_dir($value)){
				unset($rslist[$key]);
				$bname = basename($value);
				$tmpdata = array();
				$tmpdata['url'] = $baseurl.'&folder='.rawurlencode($folder.$bname."/");
				$tmpdata['title'] = $this->lib('string')->to_utf8($bname);
				$elist = $this->lib('file')->ls($value);
				if(!$elist){
					continue;
				}
				if($type != '*'){
					$is_del = true;
					foreach($elist as $k=>$v){
						if(is_dir($v)){
							$is_del = false;
							break;
						}
						$tmp2 = substr($v,-($ext_length+1));
						$tmp2 = strtolower($tmp2);
						if($tmp2 == strtolower('.'.$type)){
							$is_del = false;
							break;
						}
					}
					if($is_del){
						unset($rslist[$key]);
						continue;
					}else{
						$dirlist[] = $tmpdata;
					}
				}else{
					$dirlist[] = $tmpdata;
				}
			}else{
				if($type != '*'){
					$tmp2 = substr($value,-($ext_length+1));
					$tmp2 = strtolower($tmp2);
					if($tmp2 != strtolower('.'.$type)){
						unset($rslist[$key]);
						continue;
					}
				}
			}
		}
		if($dirlist && count($dirlist)>0){
			$this->assign('dirlist',$dirlist);
		}
		if($rslist && count($rslist)>0){
			$tmplist = array();
			foreach($rslist as $key=>$value){
				$bname = basename($value);
				$date = date("Y-m-d H:i:s",filemtime($value));
				$type = "html";
				$etype = $type == '*' ? 'txt' : $type;
				if(substr($bname,-$ext_length) != $rs["ext"]){
					$tmp = explode(".",$bname);
					$tmp_total = count($tmp);
					$type = "unknown";
					if($tmp_total > 1){
						$tmp_ext = strtolower($tmp[($tmp_total-1)]);
						$typefile = $this->dir_root."images/filetype/".$tmp_ext.".gif";
						$type = file_exists($typefile) ? $tmp_ext : "unknown";
					}
				}
				$tmpdata = array("filename"=>$value,"title"=>$bname,"date"=>$date,"type"=>$etype);
				$tmpdata['value'] = $folder.$bname;
				$tmplist[] = $tmpdata;
			}
			$this->assign('rslist',$tmplist);
		}

		$this->view('fields_template');
	}
}
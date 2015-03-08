<?php
/*****************************************************************************************
	文件： {phpok}/form/upload_form.php
	备注： 上传控件管理
	版本： 4.x
	网站： www.phpok.com
	作者： qinggan <qinggan@188.com>
	时间： 2015年03月07日 17时54分
*****************************************************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_form extends _init_auto
{
	public function __construct()
	{
		parent::__construct();
	}

	public function cssjs()
	{
		$this->addjs('js/webuploader/webuploader.min.js');
		$this->addcss('js/webuploader/webuploader.css');
	}

	public function phpok_config()
	{
		$type_list = $this->model('res')->type_list();
		$cate_list = $this->model('res')->cate_all();
		$this->assign("cate_list",$cate_list);
		$this->assign("type_list",$type_list);
		$html = $this->dir_phpok."form/html/upload_admin.html";
		$this->view($html,"abs-file",false);
	}

	public function phpok_format($rs,$appid="admin")
	{
		if($appid == 'admin'){
			return $this->_format_admin($rs);
		}else{
			return $this->_format_default($rs);
		}
	}

	public function phpok_get($rs,$appid="admin")
	{
		return $this->get($rs['identifier']);
	}

	public function phpok_show($rs,$appid="admin")
	{
		if($appid == 'admin'){
			return $this->_show_admin($rs);
		}else{
			return $this->_show_www($rs);
		}
	}

	private function _show_admin($rs)
	{
		if(!$rs || !$rs["content"]){
			return false;
		}
		if($rs['ext'] && is_string($rs['ext'])){
			$rs['ext'] = unserialize($rs['ext']);
		}
		if($rs['ext'] && $rs["ext"]["is_multiple"]){
			$list = $this->lib('ext')->res_list($rs["content"]);
			if(!$list){
				return false;
			}
			$_admin = array("id"=>$rs["content"],"type"=>"pic");
			$tmp = current($list);
			$_admin["info"] = $tmp["ico"];
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->lib('ext')->res_info($rs["content"]);
		if(!$list){
			return false;
		}
		$_admin = array("id"=>$rs["content"],"type"=>"pic","info"=>$list["ico"]);
		$list["_admin"] = $_admin;
		return $list;
	}

	private function _show_www($rs)
	{
		if(!$rs || !$rs['content']){
			return false;
		}
		if($rs['ext'] && is_string($rs['ext'])){
			$rs['ext'] = unserialize($rs['ext']);
		}
		$condition = "id IN(".$rs['content'].")";
		$rslist = $GLOBALS['app']->model('res')->get_list($condition,0,999,true);
		if(!$rslist){
			return false;
		}
		$list = false;
		foreach($rslist as $key=>$value){
			if($value['gd']){
				$tmp = false;
				foreach($value['gd'] as $k=>$v){
					$tmp[$k] = $v['filename'];
				}
				$value['gd'] = $tmp;
			}
			$list[$value['id']] = $value;
		}
		if(!$list){
			return false;
		}
		if($rs['ext'] && $rs['ext']['is_multiple']){
			return $list;
		}else{
			$ids = explode(",",$rs['content']);
			$id = $ids[0];
			return $list[$id];
		}
	}

	private function _format_admin($rs)
	{
		if($rs["content"]){
			if(is_string($rs["content"])){
				$res = $this->model('res')->get_list_from_id($rs['content']);
			}else{
				$is_list = $rs["content"]["id"] ? false : true;
				$res = array();
				if($is_list){
					foreach($rs["content"]["info"] AS $key=>$value){
						$res[$value["id"]] = $value;
					}
				}else{
					$res[$rs["content"]["id"]] = $rs["content"];
				}
				$id_list = array_keys($res);
				$rs["content"] = implode(",",$id_list);
			}
			$rs["content_list"] = $res; //附件列表
		}else{
			$rs["content_list"] = array(); //附件列表
		}
		$type_list = $this->model('res')->type_list();
		$type_id = $rs["upload_type"];
		if($rs["upload_type"] && $type_list[$rs["upload_type"]]){
			$rs["upload_type"] = $type_list[$rs["upload_type"]];
		}else{
			$str_array = array();
			foreach($type_list AS $key=>$value){
				$str_array[] = $value["ext"];
			}
			$str = implode(',',$str_array);
			$swfupload = array();
			$str_array = explode(",",$str);
			foreach($str_array AS $key=>$value){
				$swfupload[] = "*.".$value;
			}
			$swfupload = implode(";",$swfupload);
			$rs["upload_type"] = array("id"=>"file","title"=>"附件","ext"=>$str,"swfupload"=>$swfuploads);
		}
		$rs["upload_type"]["id"] = $type_id;
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/upload_admin_tpl.html','abs-file',false);
	}

	private function _format_default($rs)
	{
		$rs = $this->_content_list($rs);
		$type_list = $this->model('res')->type_list();
		$type_id = $rs["upload_type"];
		if($rs["upload_type"] && $type_list[$rs["upload_type"]]){
			$rs["upload_type"] = $type_list[$rs["upload_type"]];
		}else{
			$str_array = array();
			foreach($type_list AS $key=>$value){
				$str_array[] = $value["ext"];
			}
			$str = implode(',',$str_array);
			$swfupload = array();
			$str_array = explode(",",$str);
			foreach($str_array AS $key=>$value){
				$swfupload[] = "*.".$value;
			}
			$swfupload = implode(";",$swfupload);
			$rs["upload_type"] = array("id"=>"file","title"=>"附件","ext"=>$str,"swfupload"=>$swfupload);
		}
		$rs["upload_type"]["id"] = $type_id;
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/upload_www_tpl.html','abs-file',false);
	}

	private function _content_list($rs)
	{
		$rs['content_list'] = array();
		if(!$rs['content']){
			return $rs;
		}
		if(is_string($rs['content'])){
			$res = $this->model('res')->get_list_from_id($rs['content']);
		}else{
			$is_list = $rs["content"]["id"] ? false : true;
			$res = array();
			if($is_list){
				foreach($rs["content"]["info"] AS $key=>$value){
					$res[$value["id"]] = $value;
				}
			}else{
				$res[$rs["content"]["id"]] = $rs["content"];
			}
			$id_list = array_keys($res);
			$rs["content"] = implode(",",$id_list);
		}
		$rs["content_list"] = $res; //附件列表
		return $rs;
	}
}
?>
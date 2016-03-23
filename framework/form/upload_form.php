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
		$catelist = $this->model('rescate')->get_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['filetypes'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign("catelist",$catelist);
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
			$_admin = array("id"=>$rs["content"],"type"=>"html");
			$html = '';
			if(count($list)>=5){
				$html .= '<span class="red" style="float:left;margin-right:5px;line-height:30px;">('.count($list).')</span>';
			}
			$i = 0;
			foreach($list as $key=>$value){
				if($i>= 5){
					break;
				}
				$html .= '<img src="'.$value['ico'].'" width="28px" border="0" class="hand" onclick="preview_attr(\''.$value['id'].'\')" style="border:1px solid #dedede;padding:1px;margin-right:5px;" />';
				$i++;
			}
			$_admin["info"] = $html;
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $this->lib('ext')->res_info($rs["content"]);
		if(!$list){
			return false;
		}
		$info = '<img src="'.$list['ico'].'" width="28px" border="0" class="hand" onclick="preview_attr(\''.$list['id'].'\')" style="border:1px solid #dedede;padding:1px;margin-right:10px;" />';
		$_admin = array("id"=>$rs["content"],"type"=>"html","info"=>$info);
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
		$tmp = explode(",",$rs['content']);
		foreach($tmp as $key=>$value){
			if(!$value || !intval($value)){
				unset($tmp[$key]);
			}
		}
		if(!$tmp || count($tmp)<1){
			return false;
		}
		$rs['content'] = implode(",",$tmp);
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
			$tmp = explode(',',$rs['content']);
			$rslist = array();
			foreach($tmp as $key=>$value){
				$value = trim($value);
				if(!$value || ($value && !$list[$value])){
					continue;
				}
				$rslist[$value] = $list[$value];
			}
			return $rslist;
		}else{
			$ids = explode(",",$rs['content']);
			$id = $ids[0];
			return $list[$id];
		}
	}

	private function _format_admin($rs)
	{
		$this->addjs('js/webuploader/admin.upload.js');
		//判断是否
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
		//上传类型
		$upload_type = array('title'=>'图片','ext'=>'jpg,gif,png','maxsize'=>'512000','swfupload'=>'*.jpg,*.png,*.gif');
		$ext = ($rs['ext'] && is_string($rs['ext'])) ? unserialize($rs['ext']) : ($rs['ext'] ? $rs['ext'] : array());
		if($ext['cate_id']){
			$cateinfo = $this->model('rescate')->get_one($ext['cate_id']);
		}
		if(!$cateinfo){
			$cateinfo = $this->model('rescate')->get_default();
		}
		if($cateinfo){
			$upload_type = array('title'=>($cateinfo['typeinfo'] ? $cateinfo['typeinfo'] : $cateinfo['title']));
			$upload_type['ext'] = $cateinfo['filetypes'] ? $cateinfo['filetypes'] : 'jpg,png,gif';
			$upload_type['maxsize'] = $cateinfo['filemax'] * 1024;
			$upload_type['id'] = $ext['cate_id'];
			$tmp = array();
			foreach(explode(",",$upload_type['ext']) as $key=>$value){
				$tmp[] = "*.".$value;
			}
			$upload_type['swfupload'] = implode(", ",$tmp);
			$rs['cate_id'] = $cateinfo['id'];
		}
		$rs['upload_type'] = $upload_type;
		//echo "<pre>".print_r($rs,true)."</pre>";
		$rs['_upload_binary'] = ini_get('upload_tmp_dir') ? false : true;
		$this->assign("_rs",$rs);
		return $this->fetch($this->dir_phpok.'form/html/upload_admin_tpl.html','abs-file',false);
	}

	private function _format_default($rs)
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

		//上传类型
		$upload_type = array('title'=>'图片','ext'=>'jpg,gif,png','maxsize'=>'512000','swfupload'=>'*.jpg,*.png,*.gif');
		$ext = ($rs['ext'] && is_string($rs['ext'])) ? unserialize($rs['ext']) : ($rs['ext'] ? $rs['ext'] : array());
		if($ext['cate_id']){
			$cateinfo = $this->model('rescate')->get_one($ext['cate_id']);
		}
		if(!$cateinfo){
			$cateinfo = $this->model('rescate')->get_default();
		}
		if($cateinfo){
			$upload_type = array('title'=>($cateinfo['typeinfo'] ? $cateinfo['typeinfo'] : $cateinfo['title']));
			$upload_type['ext'] = $cateinfo['filetypes'] ? $cateinfo['filetypes'] : 'jpg,png,gif';
			$upload_type['maxsize'] = $cateinfo['filemax'] * 1024;
			$upload_type['id'] = $ext['cate_id'];
			$tmp = array();
			foreach(explode(",",$upload_type['ext']) as $key=>$value){
				$tmp[] = "*.".$value;
			}
			$upload_type['swfupload'] = implode(", ",$tmp);
		}
		$rs['upload_type'] = $upload_type;
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
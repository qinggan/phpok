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
		$this->addjs($this->dir_webroot.'js/webuploader/webuploader.min.js');
		$this->addcss($this->dir_webroot.'js/webuploader/webuploader.css');
	}

	public function phpok_config()
	{
		$catelist = $this->model('rescate')->get_all();
		$this->assign("catelist",$catelist);
		$rs = $this->tpl->val('rs');
		if($rs['ext']){
			$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
			$this->assign('ext',$ext);
		}
		$html = $this->dir_phpok."form/html/upload_admin.html";
		$this->view($html,"abs-file",false);
	}

	public function phpok_format($rs,$appid="admin")
	{
		$this->cssjs();
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
		if(is_array($rs['content'])){
			return $rs['content'];
		}else{
			$tmp = explode(",",$rs['content']);
		}
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
		$list = $this->model('res')->get_list($condition,0,999,true);
		if(!$list){
			return false;
		}
		$tmplist = array();
		foreach($list as $key=>$value){
			$tmplist[$value['id']] = $value;
		}
		$tmp = explode(',',$rs['content']);
		if($rs['ext'] && $rs['ext']['is_multiple']){
			$rslist = array();
			foreach($tmp as $key=>$value){
				$value = trim($value);
				if(!$value || ($value && !$tmplist[$value])){
					continue;
				}
				$rslist[$value] = $tmplist[$value];
			}
			return $rslist;
		}
		return $tmplist[$tmp[0]];
	}

	private function _format_admin($rs)
	{
		$this->cssjs();
		$this->addjs($this->dir_webroot.'js/webuploader/admin.upload.js');
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
		if($rs['ext']){
			$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
		}else{
			$string = 'cate_id,is_multiple';
			$tmp = explode(",",$string);
			$ext = array();
			foreach($tmp as $key=>$value){
				if($rs[$value]){
					$ext[$value] = $rs[$value];
				}
			}
		}
		$cateinfo = $this->model('rescate')->cate_info($ext['cate_id']);
		if($cateinfo){
			$upload_type = array();
			$upload_type['ext'] = $cateinfo['filetypes'] ? $cateinfo['filetypes'] : 'jpg,png,gif,rar,zip';
			$upload_type['title'] = $cateinfo['typeinfo'] ? $cateinfo['typeinfo'] : $cateinfo['title'];
			$upload_type['maxsize'] = $cateinfo['filemax'] * 1024;
			$upload_type['id'] = $ext['cate_id'];
			$upload_type['etype'] = $cateinfo['etype'];
			$upload_type['upload_binary'] = $cateinfo['upload_binary'];
			$rs['cate_id'] = $cateinfo['id'];
		}else{
			$upload_type = array('title'=>P_Lang('附件'));
			$upload_type['ext'] = 'jpg,gif,png,rar,zip';
			$upload_type['maxsize'] = 1024*1024*100;
			$upload_type['id'] = 0;
			$upload_type['etype'] = 0;
			$upload_type['upload_binary'] = 0;
			$rs['cate_id'] = 0;
		}
		$tmp = array();
		foreach(explode(",",$upload_type['ext']) as $key=>$value){
			$tmp[] = "*.".$value;
		}
		$upload_type['swfupload'] = implode(", ",$tmp);
		if($upload_type['etype']){
			$etype_info = $this->model('gateway')->get_one($upload_type['etype']);
			if(!$etype_info){
				$upload_type['etype'] = 0;
			}
		}
		$rs['upload_type'] = $upload_type;
		unset($tmp);
		//二进制上传设置
		$rs['upload_binary'] = 'false';
		if($ext['upload_binary']){
			$rs['upload_binary'] = 'true';
		}
		if($rs['upload_binary'] == 'false' && !ini_get('upload_tmp_dir')){
			$rs['upload_binary'] = 'true';
		}
		//上传压缩属性
		$compress = 'false';
		if($upload_type['compress']){
			$compress = "{width:".$upload_type['compress'].",height:".$upload_type['compress'].",quality:100,allowMagnify:false,crop:false}";
		}
		$rs['upload_compress'] = $compress;
		$rs['upload_etype_info'] = $upload_type['etype'] ? $etype_info : array();
		$this->assign("_rs",$rs);
		if($etype_info){
			$this->gateway('type',$etype_info['type']);
			$this->gateway('param',$etype_info['id']);
			$this->gateway('extinfo',$etype_info['ext']);
			return $this->gateway('exec');
		}
		return $this->fetch($this->dir_phpok.'form/html/upload_admin_tpl.html','abs-file',false);
	}

	private function _format_default($rs)
	{
		$this->addjs("js/webuploader/www.upload.js");
		if($rs["content"]){
			if(is_string($rs["content"])){
				$res = $this->model('res')->get_list_from_id($rs['content']);
			}else{
				$is_list = $rs["content"]["id"] ? false : true;
				$res = array();
				if($is_list){
					if($rs['content'] && $rs['content']['info']){
						foreach($rs["content"]["info"] AS $key=>$value){
							$res[$value["id"]] = $value;
						}
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
		$cateinfo = $this->model('rescate')->cate_info($ext['cate_id']);
		if($cateinfo){
			$upload_type = array('title'=>($cateinfo['typeinfo'] ? $cateinfo['typeinfo'] : $cateinfo['title']));
			$upload_type['ext'] = $cateinfo['filetypes'] ? $cateinfo['filetypes'] : 'jpg,png,gif,rar,zip';
			if($ext['upload_type']){
				$upload_type['ext'] = $ext['upload_type'];
			}
			if($ext['upload_name']){
				$upload_type['title'] = $ext['upload_name'];
			}
			$upload_type['maxsize'] = $cateinfo['filemax'] * 1024;
			$upload_type['id'] = $ext['cate_id'];
			$upload_type['etype'] = $cateinfo['etype'];
			$rs['cate_id'] = $cateinfo['id'];
		}else{
			$upload_type = array('title'=>($ext['upload_name'] ? $ext['upload_name'] : P_Lang('附件')));
			$upload_type['ext'] = $ext['upload_type'] ? $ext['upload_type'] : 'jpg,gif,png,rar,zip';
			$upload_type['maxsize'] = 1024*1024*100;
			$upload_type['id'] = 0;
			$rs['cate_id'] = 0;
		}
		$tmp = array();
		foreach(explode(",",$upload_type['ext']) as $key=>$value){
			$tmp[] = "*.".$value;
		}
		$upload_type['swfupload'] = implode(", ",$tmp);
		if($upload_type['etype']){
			$etype_info = $this->model('gateway')->get_one($upload_type['etype']);
			if(!$etype_info){
				$upload_type['etype'] = 0;
			}
		}
		$rs['upload_type'] = $upload_type;

		//二进制上传设置
		$rs['upload_binary'] = 'false';
		if($ext['upload_binary']){
			$rs['upload_binary'] = 'true';
		}
		if($rs['upload_binary'] == 'false' && !ini_get('upload_tmp_dir')){
			$rs['upload_binary'] = 'true';
		}
		//上传压缩属性
		$compress = 'false';
		if($ext['upload_compress']){
			$compress = "{width:".$ext['upload_compress_wh'].",height:".$ext['upload_compress_wh'].",quality:100,allowMagnify:false,crop:false}";
		}
		$rs['upload_compress'] = $compress;
		$rs['upload_ios'] = $this->lib('mobile')->is_ios();
		$rs['upload_etype_info'] = $upload_type['etype'] ? $etype_info : array();
		$this->assign("_rs",$rs);
		if($etype_info){
			$this->gateway('type',$etype_info['type']);
			$this->gateway('param',$etype_info['id']);
			$this->gateway('extinfo',$etype_info['ext']);
			return $this->gateway('exec_www.php');
		}
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
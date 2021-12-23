<?php
/**
 * CKeditor 上传组件
 * @作者 qinggan <admin@phpok.com>
 * @主页 http://www.phpok.com
 * @版本 5.x
 * @许可 http://www.phpok.com/lgpl.html PHPOK开源授权协议：GNU Lesser General Public License
 * @时间 2021年5月13日
**/

/**
 * 安全限制，防止直接访问
**/
if(!defined("PHPOK_SET")){
	exit("<h1>Access Denied</h1>");
}

class ckeditor_control extends phpok_control
{
	public function __construct()
	{
		parent::control();
	}

	public function audiolist_f()
	{
		$cateid = $this->get('cateid','int');
		if(!$cateid){
			$cate = $this->model('rescate')->get_default();
		}else{
			$cate = $this->model('rescate')->get_one($cateid);
		}
		$ext = array();
		$ext['CKEditor'] = $this->get('CKEditor');
		$ext['CKEditorFuncNum'] = $this->get('CKEditorFuncNum');
		$ext['langCode'] = $this->get('langCode');
		$this->assign('CKEditorFuncNum',$ext['CKEditorFuncNum']);
		$pageurl = $this->url('ckeditor','videolist',http_build_query($ext));
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('mp3','wma','mid','aac','flac') ";
		if($this->session->val('user_id')){
			$condition .= " AND user_id='".$this->session->val('user_id')."' ";
		}else{
			$condition .= " AND session_id='".$this->session->sessid()."' ";
		}
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (filename LIKE '%".$keywords."%' OR title LIKE '%".$keywords."%') ";
		}
		$total = $this->model('res')->get_count($condition);
		if($total){
			$rslist = $this->model('res')->get_list($condition,$offset,$psize,false);
			$this->assign('rslist',$rslist);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("pageurl",$pageurl);
		}
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$catelist = $this->model('rescate')->get_all();
		if($catelist){
			$is_cate = false;
			$exts = array('mp3','wma','mid','aac','flac');
			foreach($catelist as $key=>$value){
				$tmp = explode(",",$value['filetypes']);
				if(!array_intersect($tmp,$exts)){
					unset($catelist[$key]);
					continue;
				}
				if($cate && $value['id'] == $cate['id']){
					$is_cate = true;
				}
			}
			if($catelist){
				$this->assign("catelist",$catelist);
				if(!$is_cate){
					reset($catelist);
					$cate = current($catelist);
				}
				$array = array();
				$array['is_multiple'] = 0;
				$array['cate_id'] = $cate['id'];
				$ext = array('ext'=>$array,'manage_forbid'=>1,'is_multiple'=>0,'cate_id'=>$cate['id'],'is_refresh'=>1);
				$button = form_edit('values','','upload',$ext);
				$this->assign('button',$button);
				$this->assign('cate',$cate);
			}
		}
		$this->view($this->dir_phpok.'open/ckeditor_audiolist.html','abs-file');
	}

	public function filelist_f()
	{
		$cateid = $this->get('cateid','int');
		if(!$cateid){
			$cate = $this->model('rescate')->get_default();
		}else{
			$cate = $this->model('rescate')->get_one($cateid);
		}
		$pageurl = $this->url("ckeditor","filelist");
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = 20;
		$offset = ($pageid - 1) * $psize;
		$keywords = $this->get("keywords");
		if($this->session->val('user_id')){
			$condition = " user_id='".$this->session->val('user_id')."' ";
		}else{
			$condition = " session_id='".$this->session->sessid()."' ";
		}
		if($keywords){
			$condition .= " AND (title LIKE '%".$keywords."%' OR filename LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$file_ext = $this->get('file_ext');
		if($file_ext){
			$file_ext = strtolower($file_ext);
			$condition .= " AND ext='".$file_ext."' ";
			$pageurl .= "&file_ext=".rawurlencode($file_ext);
			$this->assign("file_ext",$file_ext);
		}
		$total = $this->model('res')->get_count($condition);
		if($total){
			$rslist = $this->model('res')->get_list($condition,$offset,$psize,false);
			$this->assign("rslist",$rslist);
			$this->assign("total",$total);
			if($total>$psize){
				$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3&always=0';
				$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
				$this->assign("pagelist",$pagelist);
			}
		}
		$catelist = $this->model('rescate')->get_all();
		if($catelist){
			$this->assign("catelist",$catelist);
		}
		$array = array();
		$array['is_multiple'] = 1;
		$array['cate_id'] = $cate['id'];
		$ext = array('ext'=>$array,'manage_forbid'=>1,'is_multiple'=>1,'cate_id'=>$cate['id'],'is_refresh'=>1);
		$button = form_edit('values','','upload',$ext);
		$this->assign('button',$button);
		$this->assign('cate',$cate);
		$this->view($this->dir_phpok.'open/ckeditor_filelist.html','abs-file');
	}


	public function video_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->ckerror(P_Lang('未指定要插入的附件'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->ckerror(P_Lang('附件不存在'));
		}
		$filename = $rs['filename'];
		if(!$filename){
			$this->ckerror(P_Lang('附件不存在'));
		}
		if(substr($filename,0,7) != 'http://' && substr($filename,0,8) != 'https://'){
			$filename = $this->config['url'].$filename;
		}
		$this->ckhtml($filename);
	}

	public function videolist_f()
	{
		$cateid = $this->get('cateid','int');
		if(!$cateid){
			$cate = $this->model('rescate')->get_default();
		}else{
			$cate = $this->model('rescate')->get_one($cateid);
		}
		$ext = array();
		$ext['CKEditor'] = $this->get('CKEditor');
		$ext['CKEditorFuncNum'] = $this->get('CKEditorFuncNum');
		$ext['langCode'] = $this->get('langCode');
		$this->assign('CKEditorFuncNum',$ext['CKEditorFuncNum']);
		$this->assign('CKEditor',$ext['CKEditor']);
		$this->assign('langCode',$ext['langCode']);
		$pageurl = $this->url('ckeditor','videolist',http_build_query($ext));
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('mp4','mpeg','avi','mov','wmv','webm','3gp','rm','rmvb','flv','mkv','ram','asf','mpg','ogg','ogv','dat','ogm') ";
		if($this->session->val('user_id')){
			$condition .= " AND user_id='".$this->session->val('user_id')."' ";
		}else{
			$condition .= " AND session_id='".$this->session->sessid()."' ";
		}
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (filename LIKE '%".$keywords."%' OR title LIKE '%".$keywords."%') ";
		}
		$total = $this->model('res')->get_count($condition);
		if($total){
			$rslist = $this->model('res')->get_list($condition,$offset,$psize,false);
			$this->assign('rslist',$rslist);
			$this->assign('total',$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("pageurl",$pageurl);
		}
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');

		$catelist = $this->model('rescate')->get_all();
		if($catelist){
			$is_cate = false;
			$exts = array('mp4','mpeg','avi','mov','wmv','webm','3gp','rm','rmvb','flv','mkv','ram','asf','mpg','ogg','ogv','dat','ogm');
			foreach($catelist as $key=>$value){
				if(!$value['is_front'] && !$value['is_default']){
					unset($catelist[$key]);
					continue;
				}
				$tmp = explode(",",$value['filetypes']);
				if(!array_intersect($tmp,$exts)){
					unset($catelist[$key]);
					continue;
				}
				if($cate && $value['id'] == $cate['id']){
					$is_cate = true;
				}
			}
			if($catelist){
				$this->assign("catelist",$catelist);
				if(!$is_cate){
					reset($catelist);
					$cate = current($catelist);
				}
				$array = array();
				$array['is_multiple'] = 0;
				$array['cate_id'] = $cate['id'];
				$ext = array('ext'=>$array,'manage_forbid'=>1,'is_multiple'=>0,'cate_id'=>$cate['id'],'is_refresh'=>1);
				$button = form_edit('values','','upload',$ext);
				$this->assign('button',$button);
				$this->assign('cate',$cate);
			}else{
				$this->error(P_Lang('平台未启视频上传功能，请联系网站管理员'));
			}
		}
		$this->view($this->dir_phpok.'open/ckeditor_videolist.html','abs-file');
	}

	/**
	 * 单选一张图片
	**/
	public function image_f()
	{
		$cateid = $this->get('cateid','int');
		if(!$cateid){
			$cate = $this->model('rescate')->get_default();
		}else{
			$cate = $this->model('rescate')->get_one($cateid);
		}
		$ext = array();
		$ext['CKEditor'] = $this->get('CKEditor');
		$ext['CKEditorFuncNum'] = $this->get('CKEditorFuncNum');
		$ext['langCode'] = $this->get('langCode');
		$this->assign('CKEditorFuncNum',$ext['CKEditorFuncNum']);
		$this->assign('CKEditor',$ext['CKEditor']);
		$this->assign('langCode',$ext['langCode']);
		$formurl = $pageurl = $this->url('ckeditor','imglist',http_build_query($ext));
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('gif','jpg','png','jpeg','webp') ";
		if($this->session->val('user_id')){
			$condition .= " AND user_id='".$this->session->val('user_id')."' ";
		}else{
			$condition .= " AND session_id='".$this->session->sessid()."' ";
		}
		$gd_rs = $this->model('gd')->get_editor_default();
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (filename LIKE '%".$keywords."%' OR title LIKE '%".$keywords."%') ";
			$this->assign('keywords',$keywords);
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$total = $this->model('res')->edit_pic_total($condition,$gd_rs);
		if(!$total){
			$this->error(P_Lang('没有图片'));
		}
		$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,$gd_rs);
		if($rslist){
			$piclist = array();
			foreach($rslist as $key=>$value){
				$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title'],'id'=>$value['id']);
				if($value['attr']){
					$attr = is_string($value['attr']) ? unserialize($value['attr']) : $value['attr'];
					$tmp['width'] = $attr['width'];
					$tmp['height'] = $attr['height'];
				}
				$piclist[] = $tmp;
			}
			$this->assign('rslist',$piclist);
		}
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		$this->assign("pageurl",$pageurl);
		$this->assign('formurl',$formurl);
		$catelist = $this->model('rescate')->get_all();
		if($catelist){
			$exts = array('jpg','gif','png','webp','exif','fpx','svg','psd','cdr','eps','ai','jpeg');
			foreach($catelist as $key=>$value){
				$tmp = explode(",",$value['filetypes']);
				if(!array_intersect($tmp,$exts)){
					unset($catelist[$key]);
					continue;
				}
			}
			if($catelist){
				$this->assign("catelist",$catelist);
				reset($catelist);
				$cate = current($catelist);
				$array = array();
				$array['is_multiple'] = 0;
				$array['cate_id'] = $cate['id'];
				$ext = array('ext'=>$array,'manage_forbid'=>1,'is_multiple'=>0,'cate_id'=>$cate['id'],'is_refresh'=>1);
				$button = form_edit('values','','upload',$ext);
				$this->assign('button',$button);
				$this->assign('cate',$cate);
			}
		}
		$this->view($this->dir_phpok.'open/ckeditor_image.html','abs-file');
	}

	/**
	 * 在弹出CKeditor图片选择器时插入图片
	**/
	public function insert_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->ckerror(P_Lang('未指定要插入的附件'));
		}
		$rs = $this->model('res')->get_one($id,true);
		if(!$rs){
			$this->ckerror(P_Lang('附件不存在'));
		}
		$filename = $rs['editor'] ? $rs['editor'] : $rs['filename'];
		if(!$filename){
			$this->ckerror(P_Lang('附件不存在'));
		}
		$this->ckhtml($filename);
	}

	private function upError($error='')
	{
		$data = array();
		$data['uploaded'] = 0;
		$data['error'] = array('message'=>$error);
		echo $this->lib('json')->encode($data);
		exit;
	}

	private function upOk($filename,$title='')
	{
		$data = array();
		$data['uploaded'] = 1;
		$data['fileName'] = $title;
		$data['url'] = $filename;
		echo $this->lib('json')->encode($data);
		exit;
	}

	private function ckerror($error='')
	{
		$callback = $this->get('callback');
		if(!$callback){
			$callback = $this->get('CKEditorFuncNum');
		}
		$html  = "<script type=\"text/javascript\">\n";
		$html .= "window.opener.CKEDITOR.tools.callFunction(".$callback.",'','".$error."');window.close();\n";
		$html .= "</script>";
		echo $html;
		exit;
	}

	private function ckhtml($filename='')
	{
		$callback = $this->get('callback');
		if(!$callback){
			$callback = $this->get('CKEditorFuncNum');
		}
		$html  = "<script type=\"text/javascript\">\n";
		$html .= "window.opener.CKEDITOR.tools.callFunction(".$callback.",'".$filename."','');window.close();\n";
		$html .= "</script>";
		echo $html;
		exit;
	}

	/**
	 * 基于 Base64 上传图片
	 * @参数 cateid 分类ID
	 * @参数 data Base64 的数据
	 * @参数 url 远程图片地址
	**/
	public function remote_f()
	{
		$this->config('is_ajax',true);
		$cateid = $this->get('cateid','int');
		if($cateid){
			$cate_rs = $this->model('rescate')->get_one($cateid);
		}
		if(!$cate_rs){
			$cate_rs = $this->model('rescate')->get_default();
			if(!$cate_rs){
				$this->error(P_Lang('未配置附件存储方式'));
			}
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($this->dir_root.$folder)){
			$this->lib('file')->make($this->dir_root.$folder);
		}
		$data = $this->get('data');
		$url = $this->get('url');
		if(!$data && !$url){
			$this->error(P_Lang('未指定要上传的数据'));
		}
		if($data){
			if(strpos($data,',') === false){
				$this->error(P_Lang('附片格式不正确'));
			}
			$tmp = explode(",",$data);
			$tmpinfo = substr($data,strlen($tmp[0]));
			$content = base64_decode($tmpinfo);
			if($content == $tmpinfo){
				$this->error(P_Lang('不是合法的附件'));
			}
			$info = explode(";",$tmp[0]);
			if(!$info[0]){
				$this->error(P_Lang('不是合法的 Base64 文件'));
			}
			$mime_type = $info[0];
			$tmp = explode("/",$mime_type);
			if(!$tmp[1]){
				$this->error(P_Lang('不是合法的 Base64 文件'));
			}
			$ext = $tmp[1];
			if($ext && $ext == 'jpeg'){
				$ext = 'jpg';
			}
		}else{
			$content = $this->lib('curl')->get_content($url);
			if(!$content){
				$this->error(P_Lang('获取失败'));
			}
			$info = parse_url($url);
			$tmp = basename($info['path']);
			$tmp2 = explode(".",$tmp);
			$ext = 'jpg';
			if(count($tmp2)>1){
				$ext = $tmp2[count($tmp2)-1];
			}
			if($ext && $ext == 'jpeg'){
				$ext = 'jpg';
			}
			$mime_type = 'image/'.$ext;
		}

		$name = $this->time.'-'.rand(1000,9999).'.'.$ext;
		$filetypes = 'jpeg,jpg,png,gif,webp';
		$ft_list = explode(",",$filetypes);
		if(!in_array($ext,$ft_list)){
			$this->error(P_Lang('您不能上传 {ext} 格式的附件',array('ext'=>$ext)));
		}
		//保存文件
		$this->lib('file')->save_pic($content,$this->dir_root.$folder.$name);
		if(!file_exists($this->dir_root.$folder.$name)){
			$this->error(P_Lang('文件保存失败，请检查'));
		}
		$array = array();
		$array["cate_id"] = $cate_rs['id'];
		$array["folder"] = $folder;
		$array["name"] = $name;
		$array["ext"] = $ext;
		$array["filename"] = $folder.$name;
		$array["addtime"] = $this->time;
		$array['title'] = $name;
		$array["mime_type"] = $mime_type;
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($ext,$arraylist)){
			$img_ext = getimagesize($this->dir_root.$array['filename']);
			if(!$img_ext || !$img_ext[0] || !$img_ext[1]){
				$this->lib('file')->rm($this->dir_root.$array['filename']);
				$this->error(P_Lang('文件异常，无法获取宽高，请检查'));
			}
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$array['filename']);
			$this->error(P_Lang('图片存储失败'));
		}
		$this->model('res')->gd_update($id);
		$gd_rs = $this->model('gd')->get_editor_default();
		if($gd_rs){
			$ext_rs = $this->model('res')->get_gd_pic($id);
			$filename = ($ext_rs && $ext_rs[$gd_rs['identifier']]) ? $ext_rs[$gd_rs['identifier']] : $rs['filename'];
		}else{
			$filename = $rs['filename'];
		}
		$this->success($filename);
	}

	/**
	 * 图片上传操作
	**/
	public function imgupload_f()
	{
		$rs = $this->lib('upload')->getfile('upload');
		if($rs['status'] != 'ok'){
			$tip = $rs['error'] ? $rs['error'] : '附件上传失败';
			$this->upError($tip);
		}
		$array = array();
		$array["cate_id"] = $rs['cate']['id'];
		$array["folder"] = $rs['folder'];
		$array["name"] = basename($rs['filename']);
		$array["ext"] = $rs['ext'];
		$array["filename"] = $rs['filename'];
		$array["addtime"] = $this->time;
		$array["title"] = $rs['title'];
		$array['admin_id'] = $this->session->val('admin_id');
		$array["mime_type"] = $rs['mime_type'];
		$arraylist = array("jpg","gif","png","jpeg","webp");
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($this->dir_root.$rs['filename']);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($this->dir_root.$rs['filename']);
			$this->upError(P_Lang('图片存储失败'));
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$gd_rs = $this->model('gd')->get_editor_default();
		if($gd_rs && $rs['gd'] && $rs['gd'][$gd_rs['identifier']]){
			$filename = $rs[$gd_rs['identifier']];
		}else{
			$filename = $rs['filename'];
		}
		$this->upOk($filename,$rs['title']);
	}

	public function upload_f()
	{
		$cateid = $this->get('cateid','int');
		if(!$cateid){
			$cate = $this->model('rescate')->get_default();
		}else{
			$cate = $this->model('rescate')->get_one($cateid);
		}
		if(!$cate){
			$this->error(P_Lang('分类信息不存在'));
		}
		$catelist = $this->model('rescate')->get_all();
		if(!$catelist){
			$this->error(P_Lang('未创建分类'));
		}
		foreach($catelist as $key=>$value){
			if(!$value['is_front']){
				unset($catelist[$key]);
				continue;
			}
		}
		if(!$catelist){
			$this->error(P_Lang('没有开放的附件分类'));
		}
		$this->assign("catelist",$catelist);
		$array = array();
		$array['is_multiple'] = 1;
		$array['cate_id'] = $cate['id'];
		$button = form_edit('values','','upload',array('ext'=>$array,'manage_forbid'=>1,'is_multiple'=>1,'cate_id'=>$cate['id']));
		$this->assign('button',$button);
		$this->assign('cate',$cate);
		$this->view($this->dir_phpok.'open/ckeditor_upload.html','abs-file');
	}

	/**
	 * 浏览器图片预览
	**/
	public function images_f()
	{
		$ext = array();
		$ext['CKEditor'] = $this->get('CKEditor');
		$ext['CKEditorFuncNum'] = $this->get('CKEditorFuncNum');
		$ext['langCode'] = $this->get('langCode');
		$this->assign('CKEditorFuncNum',$ext['CKEditorFuncNum']);
		$formurl = $pageurl = $this->url('ckeditor','imglist',http_build_query($ext));
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'];
		$offset = ($pageid - 1) * $psize;
		$condition = "ext IN ('gif','jpg','png','jpeg','webp') ";
		if($this->session->val('user_id')){
			$condition .= " AND user_id='".$this->session->val('user_id')."' ";
		}else{
			$condition .= " AND session_id='".$this->session->sessid()."' ";
		}
		$gd_rs = $this->model('gd')->get_editor_default();
		$keywords = $this->get('keywords');
		if($keywords){
			$condition .= " AND (filename LIKE '%".$keywords."%' OR title LIKE '%".$keywords."%') ";
			$this->assign('keywords',$keywords);
			$pageurl .= "&keywords=".rawurlencode($keywords);
		}
		$total = $this->model('res')->edit_pic_total($condition,$gd_rs);
		if($total){
			$rslist = $this->model('res')->edit_pic_list($condition,$offset,$psize,$gd_rs);
			if($rslist){
				$piclist = array();
				foreach($rslist as $key=>$value){
					$tmp = array('url'=>$value['filename'],'ico'=>$value['ico'],'mtime'=>$value['addtime'],'title'=>$value['title'],'id'=>$value['id']);
					if($value['attr']){
						$attr = is_string($value['attr']) ? unserialize($value['attr']) : $value['attr'];
						$tmp['width'] = $attr['width'];
						$tmp['height'] = $attr['height'];
					}
					$piclist[] = $tmp;
				}
				$this->assign('rslist',$piclist);
			}
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=3';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
			$this->assign("pageurl",$pageurl);
			$this->assign('formurl',$formurl);
		}

		$catelist = $this->model('rescate')->get_all();
		if($catelist){
			$is_cate = false;
			$exts = array('jpg','gif','png','webp','exif','fpx','svg','psd','cdr','eps','ai','jpeg');
			foreach($catelist as $key=>$value){
				if(!$value['is_front'] && !$value['is_default']){
					unset($catelist[$key]);
					continue;
				}
				$tmp = explode(",",$value['filetypes']);
				if(!array_intersect($tmp,$exts)){
					unset($catelist[$key]);
					continue;
				}
				if($cate && $value['id'] == $cate['id']){
					$is_cate = true;
				}
			}
			if($catelist){
				$this->assign("catelist",$catelist);
				if(!$is_cate){
					reset($catelist);
					$cate = current($catelist);
				}
				$array = array();
				$array['is_multiple'] = 1;
				$array['cate_id'] = $cate['id'];
				$ext = array('ext'=>$array,'manage_forbid'=>1,'is_multiple'=>1,'cate_id'=>$cate['id'],'is_refresh'=>1);
				$button = form_edit('values','','upload',$ext);
				$this->assign('button',$button);
				$this->assign('cate',$cate);
			}
		}
		
		$this->view($this->dir_phpok.'open/ckeditor_images.html','abs-file');
	}
}

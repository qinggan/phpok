<?php
/**
 * 资源管理器
 * @作者 qinggan <admin@phpok.com>
 * @主页 https://www.phpok.com
 * @版本 5.x
 * @授权 MIT License <https://www.phpok.com/mit.html>
 * @时间 2019年2月6日
**/

if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class res_control extends phpok_control
{
	private $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("res");
		$this->assign("popedom",$this->popedom);
	}

	//附件
	public function index_f()
	{
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = $this->config['psize'] * 5;
		$pageurl = $this->url('res');
		$offset = ($pageid - 1) * $psize;
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			$filetypes = array();
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
					$filetypes[] = $v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
			$filetypes = array_unique($filetypes);
		}else{
			$filetypes = array('jpg','gif','png','rar','zip');
		}
		$this->assign('filetypes',$filetypes);
		$this->assign("catelist",$catelist);
		$condition = "1=1";
		$tmp_c = $this->condition($condition,$pageurl);
		$condition = $tmp_c["condition"];
		$pageurl = $tmp_c["pageurl"];
		$total = $this->model('res')->get_count($condition);
		if($total){
			$rslist = $this->model('res')->get_list($condition,$offset,$psize);
			$this->assign("rslist",$rslist);
			$this->assign("total",$total);
			$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
			$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
			$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
			$this->assign("pagelist",$pagelist);
		}
		$this->view("res_index");
	}

	public function clear_f()
	{
		$this->view('res_clear');
	}

	public function add_f()
	{
		$catelist = $this->model('res')->cate_all();
		$cateid = $this->get('cateid','int');
		if($catelist){
			foreach($catelist as $key=>$value){
				if(!$cateid && $value['is_default']){
					$cateid = $value['id'];
				}
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign('cateid',$cateid);
		$this->assign("catelist",$catelist);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$btns = form_edit('upload','','upload','cate_id='.$cateid.'&manage_forbid=1&auto_forbid=1&is_multiple=1');
		$this->assign('html',$btns);
		$this->view("res_add");
	}

	public function set_f()
	{
		$this->assign("home_url",$error_url);
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$this->assign("id",$id);
		$rs = $this->model('res')->get_one($id,true);
		if(!$rs){
			$this->error(P_Lang('附件不存在'));
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$content = form_edit('note',$rs['note'],'editor','width=650&height=250&etype=simple');
		$this->assign('content',$content);
		$is_local = false;
		if($this->model('res')->is_local($rs['filename'])){
			$filesize = filesize($this->dir_root.$rs['filename']);
			if($rs['filename'] && file_exists($this->dir_root.$rs['filename']) && $rs['ext'] && in_array($rs['ext'],array('jpg','gif','png','jpeg')) && $filesize > 102400){
				$this->assign('resize',true);
				$this->assign('filesize',$this->lib('common')->num_format($filesize));
			}
			$is_local = true;
		}
		$this->assign('file_is_local',$is_local);
		$this->view("res_manage");
	}

	/**
	 * 修改附件信息
	**/
	public function setok_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->error(P_Lang('附件标题不能为空'));
		}
		$note = $this->get('note','html');
		$this->model('res')->save(array('title'=>$title,'note'=>$note),$id);
		$this->success();
	}

	/**
	 * 批量压缩附件大小
	**/
	public function compress_f()
	{
		$maxwidth = $this->get('maxwidth','int');
		if(!$maxwidth){
			$this->error(P_Lang('未设置最小宽度限制'));
		}
		$maxheight = $this->get('maxheight','int');
		if(!$maxheight){
			$this->error(P_Lang('未设置最小高度限制'));
		}
		$maxfilesize = $this->get('maxfilesize','int');
		if(!$maxfilesize){
			$this->error(P_Lang('未设置文件大小限制'));
		}
		$maxfilesize_bit = $maxfilesize * 1000;
		$condition  = "ext IN('jpg','gif','png','jpeg','bmp','webp','tiff','exif') ";
		$condition .= "AND filename NOT LIKE 'https://%' AND filename NOT LIKE 'http://%' AND filename NOT LIKE '//%'";
		$id = $this->get('id','int');
		if($id){
			$condition .= " AND id<".$id." ";
		}
		$rslist = $this->model('res')->get_list($condition,0,100);
		if(!$rslist){
			$this->success(P_Lang('批量压缩操作完成，请关闭窗口'));
		}
		$rs_width = $rs_height = $rs_compress = array();
		foreach($rslist as $key=>$value){
			$imginfo = getimagesize($this->dir_root.$value['filename']);
			$img_x = $imginfo[0];
			$img_y = $imginfo[1];
			if($img_x > $maxwidth){
				$rs_width = $value;
				$rs = $value;
				break;
			}
			if($img_y > $maxheight){
				$rs_height = $value;
				$rs = $value;
				break;
			}
			$filesize = filesize($this->dir_root.$value['filename']);
			if($filesize > $maxfilesize_bit){
				$rs_compress = $value;
				$rs = $value;
				break;
			}
		}
		if(!$rs || count($rs)<1){
			$next_rs = end($rslist);
			$next_id = $next_rs['id'];
		}else{
			$next_id = $rs['id'];
			if($rs_width){
				$this->compress_pic_width($rs,$maxwidth);
			}
			if($rs_height){
				$this->compress_pic_height($rs,$maxheight);
			}
			if($rs_compress){
				$this->compress_pic($rs,100);
			}
		}
		$linkurl = $this->url('res','compress','id='.$next_id.'&maxwidth='.$maxwidth.'&maxheight='.$maxheight.'&maxfilesize='.$maxfilesize);
		$this->success(P_Lang('已更新图片ID #{id}，正在执行下一张图片处理',array('id'=>$next_id)),$linkurl);
	}

	private function compress_pic_width($rs,$width=0){
		if(!$rs || !$width){
			return false;
		}
		$imginfo = getimagesize($this->dir_root.$rs['filename']);
		$img_x = $imginfo[0];
		$img_y = $imginfo[1];
		$height = intval(($width * $img_y) / $img_x);
		$this->_compress($rs,$width,$height);
		return true;
	}

	private function compress_pic_height($rs,$height=0){
		if(!$rs || !$height){
			return false;
		}
		$imginfo = getimagesize($this->dir_root.$rs['filename']);
		$img_x = $imginfo[0];
		$img_y = $imginfo[1];
		$width = intval(($height * $img_x) / $img_y);
		$this->_compress($rs,$width,$height);
		return true;
	}

	private function compress_pic($rs,$quality=100){
		if(!$rs || !$quality){
			return false;
		}
		$imginfo = getimagesize($this->dir_root.$rs['filename']);
		$img_x = $imginfo[0];
		$img_y = $imginfo[1];
		$this->_compress($rs,$img_x,$img_y,$quality);
		return true;
	}

	private function _compress($rs,$width=0,$height=0,$quality=100)
	{
		if(!$width || !$height){
			return false;
		}
		$this->lib('gd')->isgd(true);
		$this->lib('gd')->filename($this->dir_root.$rs['filename']);
		$this->lib('gd')->SetCut(false);
		$this->lib('gd')->SetWH($width,$height);
		$this->lib('gd')->Set('quality',$quality);
		$picfile = $this->lib('gd')->Create($rs['filename'],$rs['id']);
		$this->lib('file')->mv($this->dir_root.$rs['folder'].$picfile,$this->dir_root.$rs['filename']);
		$array = array('width'=>$width,'height'=>$height);
		$data = array('attr'=>serialize($array));
		$this->model('res')->save($data,$rs['id']);
	}

	public function resize_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('附件信息不存在'));
		}
		if(!is_file($this->dir_root.$rs['filename'])){
			$this->error(P_Lang('附件不存在'));
		}
		if(!$rs['ext'] || ($rs['ext'] && !in_array(strtolower($rs['ext']),array('jpg','gif','png','jpeg')))){
			$this->error(P_Lang('要压缩的图片仅支持JPG，GIF，PNG，JPEG'));
		}
		$ext = strtolower($rs['ext']);
		$imginfo = getimagesize($this->dir_root.$rs['filename']);
		$img_x = $imginfo[0];
		$img_y = $imginfo[1];
		$width = $this->get('width','int');
		$ptype = $this->get('ptype','int');
		$this->lib('gd')->isgd(true);
		$this->lib('gd')->filename($this->dir_root.$rs['filename']);
		if(!$width){
			$width = $img_x;
			$height = $img_y;
		}else{
			if($width > $img_x){
				$width = $img_x;
			}
			$height = intval(($width * $img_y) / $img_x);
		}
		$this->lib('gd')->SetCut(false);
		$this->lib('gd')->SetWH($width,$height);
		if($ext && in_array($ext,array('jpg','jpeg')) && $ptype){
			$this->lib('gd')->Set('quality',$ptype);
		}
		$picfile = $this->lib('gd')->Create($rs['filename'],$rs['id']);
		$this->lib('file')->mv($this->dir_root.$rs['folder'].$picfile,$this->dir_root.$rs['filename']);
		$array = array('width'=>$width,'height'=>$height);
		$data = array('attr'=>serialize($array));
		$this->model('res')->save($data,$rs['id']);
		$this->success();
	}

	/**
	 * 下载附件
	 * @参数 id 附件ID
	**/
	public function download_f()
	{
		$e_url = $this->session->val('admin_return_url') ? $this->session->val('admin_return_url') : $this->url('res');
		$id = $this->get("id","int");
		if(!$id){
			$this->error(P_Lang('未指定附件ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->error(P_Lang('附件信息不存在'));
		}
		$my = strtolower(substr($rs["filename"],0,7));
		if($my == "https:/" || $my == "http://"){
			$this->_location($rs['filename']);
		}
		if(!$rs["filename"] || !file_exists($this->dir_root.$rs["filename"])){
			$this->error(P_Lang('附件不存在'));
		}
		$this->lib('file')->download($rs['filename'],$rs['title']);
	}

	/**
	 * 附件批量处理
	**/
	public function pl_f()
	{
		if(!$this->popedom["pl"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid){
			$pageid = 1;
		}
		$psize = 240;
		$pageurl = $this->url("res","pl");
		$offset = ($pageid - 1) * $psize;
		# 附件分类
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$condition = "1=1";
		$tmp_c = $this->condition($condition,$pageurl);
		$condition = $tmp_c["condition"];
		$pageurl = $tmp_c["pageurl"];
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		# 存储当前的URL信息
		$myurl = $pageurl ."&".$this->config["pageid"]."=".$pageid;
		$_SESSION["admin_return_url"] = $myurl;
		$this->view("res_list");
	}

	private function condition($condition="",$pageurl="")
	{
		$keywords = $this->get("keywords");
		if($keywords){
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id){
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$start_date = $this->get("start_date");
		if($start_date){
			$condition .= " AND addtime>=".strtotime($start_date)." ";
			$pageurl .= "&start_date=".strtolower($start_date);
			$this->assign("start_date",$start_date);
		}
		$stop_date = $this->get("stop_date");
		if($stop_date){
			$condition .= " AND addtime<=".strtotime($stop_date)." ";
			$pageurl .= "&stop_date=".strtolower($stop_date);
			$this->assign("stop_date",$stop_date);
		}
		$ext = $this->get("ext");
		if(!$ext){
			$ext = array();
		}
		$this->assign("ext",$ext);
		$ext_array = array();
		foreach($ext as $key=>$value){
			$ext_array[] = $value;
			$pageurl .= "&ext[]=".rawurlencode($value);
		}
		$myext = $this->get("myext");
		if($myext){
			$myext = str_replace("，",",",$myext);
			$myext_list = explode(",",$myext);
			foreach($myext_list AS $key=>$value){
				$ext_array[] = $value;
			}
			$this->assign("myext",$myext);
			$pageurl .= "&myext=".rawurlencode($myext);
		}
		if($ext_array && count($ext_array)>0 ){
			$ext_array = array_unique($ext_array);
		}
		$ext_string = implode("','",$ext_array);
		if($ext_string){
			$condition .= " AND ext IN('".$ext_string."') ";
		}
		return array("condition"=>$condition,"pageurl"=>$pageurl);
	}

	/**
	 * 批量更新图片
	**/
	public function update_pl_f()
	{
		if(!$this->popedom["pl"]){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		@set_time_limit(60);
		//设置内存 128M
		@ini_set('memory_limit', '128M');
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定要操作的附件'));
		}
		$psize = 20;
		$pageid = $this->get("pageid","int");
		if(!$pageid){
			$pageid = 1;
		}
		$ext_list = array("jpg","gif","png","jpeg");
		if($id == 'all'){
			$start_id = $this->get('start_id','int');
			$next_id = $this->get('next_id','int');
			$total_condition = $condition = "filename NOT LIKE 'http%//%' AND ext IN('".implode("','",$ext_list)."')";
			if($start_id){
				$total_condition .= " AND id>".$start_id;
			}
			if(!$next_id){
				$next_id = $start_id ? $start_id : 0;
			}
			$condition .= " AND id>".$next_id;
			$total = $this->model('res')->get_count($total_condition);
			if(!$total){
				$this->success(P_Lang('附件中没有涉及到图片信息，请关闭窗口'));
			}
			$num = $this->get('num','int');
			if(!$num){
				$num = 0;
			}
			$reslist = $this->model('res')->admin_get_list($condition,0,$psize,'id ASC');
			if(!$reslist){
				$this->success(P_Lang('附件信息更新完毕，共更新数量：{total}，请关闭窗口',array('total'=>"<span class='red'>".$total."</span>")));
			}
			$tmplist = array();
			$memsize = 0;
			foreach($reslist as $key=>$value){
				$next_id = $value['id'];
				//删除无效文件
				if(!$value['filename'] || !file_exists($this->dir_root.$value['filename'])){
					$this->model('res')->delete($value['id']);
					continue;
				}
				$tmpsize = 0;
				$tmp_attr = $value['attr'] ? unserialize($value['attr']) : array();
				if($tmp_attr && $tmp_attr['width'] && $tmp_attr['height'] && is_numeric($tmp_attr['width']) && is_numeric($tmp_attr['height'])){
					$tmpsize = $tmp_attr['width'] * $tmp_attr['height'] * 4;
				}else{
					$infos = getimagesize($this->dir_root.$value['filename']);
					$tmpsize = $infos[0] * $infos[1] * 4;
				}
				if($tmpsize && $tmpsize >= (16 * 1024 * 1024)){
					continue;
				}
				$num++;
				$this->model('res')->gd_update($value['id']);
			}
			$myurl = $this->url("res","update_pl") ."&id=all&next_id=".$next_id;
			if($start_id){
				$myurl .= "&start_id=".$start_id;
			}
			$myurl.= "&num=".$num;
			$this->tip(P_Lang('附件信息正更新，共有数量：{total}，已更新数量：{num}，请耐心等候…',array('total'=>"<span class='red'>".$total."</span>",'num'=>"<span class='red'>".$num."</span>")),$myurl,0.5);
		}
		$myurl = $this->url("res","update_pl") ."&id=".rawurlencode($id)."&pageid=".($pageid+1);
		$list = explode(",",$id);
		if(!$list[$pageid]){
			$this->success(P_Lang('附件信息更新完毕，共更新数量：{pageid}，点击右上角关闭窗口^_^',array('pageid'=>"<span class='red'>".count($list)."</span>")));
		}
		$rs = $this->model('res')->get_one($list[$pageid]);
		if(!$rs){
			$this->error(P_Lang("附件更新中，当前ID：{pageid} 不存在附件",array('pageid'=>$list[$pageid])),$myurl);
		}
		if(!$rs['filename'] || !file_exists($this->dir_root.$rs['filename'])){
			$this->error(P_Lang("附件更新中，当前ID：{pageid} 不存在附件",array('pageid'=>$list[$pageid])),$myurl);
		}
		$tmpsize = 0;
		if($rs['attr'] && $rs['attr']['width'] && $rs['attr']['height']){
			$tmpsize = $rs['attr']['width'] * $rs['attr']['height'] * 4;
		}else{
			$infos = getimagesize($this->dir_root.$rs['filename']);
			$tmpsize = $infos[0] * $infos[1] * 4;
		}
		if($tmpsize && $tmpsize >= (16 * 1024 * 1024)){
			$this->error(P_Lang("附件更新中，当前ID：{pageid} 文件太大，不支持生成小图",array('pageid'=>$list[$pageid])),$myurl);
		}
		$this->model('res')->gd_update($rs['id']);
		$total = $pageid+1;
		$this->tip(P_Lang('附件更新中，当前已更新数量：{total}',array('total'=>"<span class='red'><strong>".$total."</strong></span>")),$myurl,0.3);
	}


	function delete_pl_f()
	{
		if(!$this->popedom['pl']){
			$this->json(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$tmplist = array();
		foreach($list as $key=>$value){
			$tmp = intval($value);
			if($tmp){
				$this->model('res')->delete($tmp);
			}
		}
		$this->json(true);
	}

	public function delete_f()
	{
		if(!$this->popedom['pl']){
			$this->error(P_Lang('您没有权限执行此操作'));
		}
		$id = $this->get('id');
		if(!$id){
			$this->error(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$tmplist = array();
		foreach($list as $key=>$value){
			$tmp = intval($value);
			if($tmp){
				$this->model('res')->delete($tmp);
			}
		}
		$this->success();
	}

	public function movecate_f()
	{
		$id = $this->get('id');
		$newcate = $this->get('newcate','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		if(!$newcate){
			$this->json(P_Lang('未指定新的附件分类'));
		}
		$list = explode(',',$id);
		foreach($list as $key=>$value){
			$value = intval($value);
			if(!$value){
				unset($list[$key]);
			}
		}
		$id = implode(",",$list);
		$this->model('res')->update_cate($id,$newcate);
		$this->json(true);
	}

	/**
	 * 编辑器远程附件本地化设置
	**/
	public function setting_remote_to_local_f()
	{
		$rs = $this->model('res')->remote_config();
		if(!$rs){
			$rs = array('domain1'=>'localhost','domain2'=>'*');
		}
		$this->assign('rs',$rs);
		$this->view('res_remote_file');
	}

	/**
	 * 保存附件本地化配置信息
	**/
	public function setting_remote_to_local_save_f()
	{
		$data = array();
		$data['domain1'] = $this->get('domain1');
		$data['domain2'] = $this->get('domain2');
		$this->model('res')->remote_config($data);
		$this->success();
	}

	/**
	 * 检测获取最小ID，最大ID及数量
	**/
	public function clearlist_f()
	{
		$condition = "1=1";
		$start_date = $this->get('start_date');
		if($start_date){
			$condition = " AND addtime>=".strtotime($start_date);
		}
		$stop_date = $this->get('stop_date');
		if($stop_date){
			$condition .= " AND addtime<=".strtotime($stop_date);
		}
		$id_start = $this->get('id_start','int');
		if($id_start){
			$condition .= " AND id>=".$id_start;
		}
		$id_stop = $this->get('id_stop','int');
		if($id_stop){
			$condition .= " AND id<=".$id_stop;
		}
		$rs = $this->model('res')->admin_clearlist($condition);
		if(!$rs){
			$this->error(P_Lang('暂无附件信息'));
		}
		$this->success($rs);
	}

	/**
	 * 一个个附件检测是否已被使用
	**/
	public function check_f()
	{
		$id_start = $this->get('id_start','int');
		$id_stop = $this->get('id_stop','int');
		$rs = $this->model('res')->admin_res_info($id_start,$id_stop);
		if(!$rs){
			$this->success('end');
		}
		$status = $this->model('res')->admin_check($rs);
		$rs['addtime_format'] = time_format($rs['addtime']);
		if($status){
			$rs['status'] = true;
			$this->success($rs);
		}
		$rs['status'] = false;
		$this->success($rs);
	}

	public function editor_update_all_f()
	{
		$this->config('is_ajax',false);
		$pageid = $this->get('pageid','int');
		if(!$pageid){
			$pageid = 1;
		}
		$psize = 30;
		$offset = ($pageid-1) * $psize;
		$condition = "ext IN('jpg','gif','png','jpeg')";
		$total = $this->model('res')->get_count($condition);
		if(!$total){
			$this->error('附件为空，请检查');
		}
		$all_page = intval($total/$psize);
		if($total%$psize){
			$all_page++;
		}
		$rslist = $this->model('res')->get_list($condition,$offset,$psize,true);
		if(!$rslist){
			$this->success('附件信息已更新完成，请手动关闭');
		}
		$gdinfo = $this->model('gd')->get_editor_default();
		$o2n = array();
		if($gdinfo){
			foreach($rslist as $key=>$value){
				if(!$value['gd'][$gdinfo['identifier']]){
					continue;
				}
				//更换原图路径到新图片路径
				$o2n[] = array('old'=>$value['filename'],'new'=>$value['gd'][$gdinfo['identifier']]);
				//更换GD类的图片到新图片路径
				if(!$value['gd']){
					continue;
				}
				foreach($value['gd'] as $k=>$v){
					if($k == $gdinfo['identifier']){
						continue;
					}
					$o2n[] = array('old'=>$v,'new'=>$value['gd'][$gdinfo['identifier']]);
				}
			}
		}else{
			foreach($rslist as $key=>$value){
				if(!$value['gd']){
					continue;
				}
				foreach($value['gd'] as $k=>$v){
					$o2n[] = array('old'=>$v,'new'=>$value['filename']);
				}
			}
		}
		if(!$o2n || count($o2n)<1){
			$this->error('没有附件要执行批量处理');
		}
		unset($rslist);
		$sql = "SELECT f.*,m.mtype,m.tbl FROM ".$this->db->prefix."fields f LEFT JOIN ".$this->db->prefix."module m ";
		$sql.= " ON(f.ftype=m.id) WHERE f.form_type IN('editor','ckeditor') AND (f.ftype+0)>0";
		$flist = $this->db->get_all($sql);
		if(!$flist){
			$this->error(P_Lang('没有符合要求的扩展字段'));
		}
		foreach($flist as $key=>$value){
			foreach($o2n as $k=>$v){
				$sql = "UPDATE ".tablename($value['ftype'])." SET ".$value['identifier']."=replace(".$value['identifier'].",'".$v['old']."','".$v['new']."')";
				$this->db->query($sql);
			}
		}
		$oldpage = $pageid;
		$pageid++;
		$tmp = array('pageid'=>'<span style="color:red">'.$oldpage.'</span>');
		$tmp['allpage'] = '<span style="color:red">'.$all_page.'</span>';
		$url = $this->url('res','editor_update_all','pageid='.$pageid);
		$this->success(P_Lang('正在更新编辑框附件，当前已更新第 {pageid} 页，共有 {allpage} 页',$tmp),$url,0.3);
	}

	public function info_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->error(P_Lang('未指定附件ID'));
		}
		$rs = $this->model('res')->get_one($id,true);
		$this->success($rs);
	}
}
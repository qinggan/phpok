<?php
/***********************************************************
	Filename: {phpok}/admin/res_control.php
	Note	: 资源管理器
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2012-12-27 12:02
***********************************************************/
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
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$string = 'home='.P_Lang('首页').'&prev='.P_Lang('上一页').'&next='.P_Lang('下一页').'&last='.P_Lang('尾页').'&half=5';
		$string.= '&add='.P_Lang('数量：').'(total)/(psize)'.P_Lang('，').P_Lang('页码：').'(num)/(total_page)&always=1';
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,$string);
		$this->assign("pagelist",$pagelist);
		$myurl = $pageurl ."&".$this->config["pageid"]."=".$pageid;
		$this->view("res_index");
	}

	public function add_f()
	{
		$catelist = $this->model('res')->cate_all();
		if($catelist){
			foreach($catelist as $key=>$value){
				$types = explode(",",$value['filetypes']);
				$tmp = array();
				foreach($types as $k=>$v){
					$tmp[] = "*.".$v;
				}
				$value['typeinfos'] = implode(" , ",$tmp);
				$catelist[$key] = $value;
			}
		}
		$this->assign("catelist",$catelist);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
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

	function condition($condition="",$pageurl="")
	{
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%' OR id LIKE '%".$keywords."%') ";
			$pageurl .= "&keywords=".rawurlencode($keywords);
			$this->assign("keywords",$keywords);
		}
		$cate_id = $this->get("cate_id","int");
		if($cate_id)
		{
			$condition .= " AND cate_id='".$cate_id."' ";
			$pageurl .= "&cate_id=".$cate_id;
			$this->assign("cate_id",$cate_id);
		}
		$start_date = $this->get("start_date");
		if($start_date)
		{
			$condition .= " AND addtime>=".strtotime($start_date)." ";
			$pageurl .= "&start_date=".strtolower($start_date);
			$this->assign("start_date",$start_date);
		}
		$stop_date = $this->get("stop_date");
		if($stop_date)
		{
			$condition .= " AND addtime<=".strtotime($stop_date)." ";
			$pageurl .= "&stop_date=".strtolower($stop_date);
			$this->assign("stop_date",$stop_date);
		}
		$ext = $this->get("ext");
		if(!$ext) $ext = array();
		$this->assign("ext",$ext);
		$ext_array = array();
		foreach($ext AS $key=>$value)
		{
			$ext_array[] = $value;
			$pageurl .= "&ext[]=".rawurlencode($value);
		}
		$myext = $this->get("myext");
		if($myext)
		{
			$myext = str_replace("，",",",$myext);
			$myext_list = explode(",",$myext);
			foreach($myext_list AS $key=>$value)
			{
				$ext_array[] = $value;
			}
			$this->assign("myext",$myext);
			$pageurl .= "&myext=".rawurlencode($myext);
		}
		if($ext_array && count($ext_array)>0 ) $ext_array = array_unique($ext_array);
		$ext_string = implode("','",$ext_array);
		if($ext_string)
		{
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
		$id = $this->get("id");
		if(!$id){
			$this->error(P_Lang('未指定要操作的附件'));
		}
		$psize = 1;
		$pageid = $this->get("pageid","int");
		$pageid = intval($pageid);
		$ext_list = array("jpg","gif","png","jpeg");
		if($id == 'all'){
			$start_id = $this->get('start_id','int');
			if($start_id && !$pageid){
				$total = $this->model('res')->get_count("id>='".$start_id."'");
				if($total){
					$pageid = $total;
				}
			}
			$reslist = $this->model('res')->get_list("filename NOT LIKE 'http%//%'",$pageid,8);
			if(!$reslist){
				$this->success(P_Lang('附件信息更新完毕，共更新数量：{pageid}，点击右上角关闭窗口^_^',array('pageid'=>"<span class='red'>".$pageid."</span>")));
			}
			$tmplist = array();
			$filesize = 0;
			foreach($reslist as $key=>$value){
				if(file_exists($this->dir_root.$value['filename'])){
					$filesize += filesize($this->dir_root.$value['filename']);
					$tmplist[$key] = $value;
					if($filesize >= (2 * 1024 * 1024)){
						break;
					}
				}
			}
			if(!$tmplist || count($tmplist)<1){
				$this->success(P_Lang('附件信息更新完毕，共更新数量：{pageid}，点击右上角关闭窗口^_^',array('pageid'=>"<span class='red'>".$pageid."</span>")));
			}
			foreach($tmplist as $key=>$value){
				$this->model('res')->gd_update($value['id']);
				$pageid++;
			}
			$myurl = $this->url("res","update_pl") ."&id=all&pageid=".$pageid;
		}else{
			$myurl = $this->url("res","update_pl") ."&id=".rawurlencode($id)."&pageid=".($pageid+1);
			$list = explode(",",$id);
			if(!$list[$pageid]){
				$this->success(P_Lang('附件信息更新完毕，共更新数量：{pageid}，点击右上角关闭窗口^_^',array('pageid'=>"<span class='red'>".count($list)."</span>")));
			}
			$rs = $this->model('res')->get_one($list[$pageid]);
			if(!$rs){
				$this->error(P_Lang("附件更新中，当前ID：{pageid} 不存在附件",array('pageid'=>$list[$pageid])),$myurl);
			}
			$this->model('res')->gd_update($rs['id']);
		}
		
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
		foreach($list AS $key=>$value){
			$tmp = intval($value);
			if($tmp){
				$this->model('res')->delete($tmp);
			}
		}
		$this->json(true);
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
}
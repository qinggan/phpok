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
		$psize = 150;
		$pageurl = $this->url('res');
		$offset = ($pageid - 1) * $psize;
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
		$backurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $this->url('res');
		$this->assign("home_url",$error_url);
		$id = $this->get("id","int");
		if(!$id){
			error(P_Lang('未指定ID'),$backurl,"error");
		}
		$this->assign("id",$id);
		$rs = $this->model('res')->get_one($id,true);
		if(!$rs){
			error(P_Lang('附件不存在'),$backurl,"error");
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->lib('form')->cssjs(array('form_type'=>'upload'));
		$this->addjs('js/webuploader/admin.upload.js');
		$content = form_edit('note',$rs['note'],'editor','width=650&height=250&etype=simple');
		$this->assign('content',$content);
		$this->assign('backurl',$backurl);
		$this->view("res_manage");
	}

	public function download_f()
	{
		$e_url = $_SESSION["admin_return_url"] ? $_SESSION["admin_return_url"] : admin_url("res");
		$id = $this->get("id","int");
		if(!$id)
		{
			error(P_Lang('未指定附件名'),$e_url,"error");
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs)
		{
			error(P_Lang('附件信息不存在'),$e_url,"error");
		}
		$e_url = admin_url("res","set","id=".$id);
		if(!$rs["filename"] || !file_exists($this->dir_root.$rs["filename"]))
		{
			error(P_Lang('附件不存在'),$e_url,"error");
		}
		$my = strtolower(substr($rs["filename"],0,7));
		if($my == "https:/" || $my == "http://")
		{
			error(P_Lang('远程附件不允许下载，请直接打开'),$e_url,"error");
		}
		$filesize = filesize($this->dir_root.$rs["filename"]);
		ob_end_clean();
		header("Date: ".gmdate("D, d M Y H:i:s", $rs["addtime"])." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $rs["addtime"])." GMT");
		header("Content-Encoding: none");
		header("Content-Disposition: attachment; filename=".rawurlencode($rs["title"].".".$rs["ext"]));
		header("Content-Length: ".$filesize);
		header("Accept-Ranges: bytes");
		readfile($this->dir_root.$rs["filename"]);
		flush();
		ob_flush();
	}

	//附件批量处理
	function pl_f()
	{
		if(!$this->popedom["pl"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
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

	function update_pl_f()
	{
		if(!$this->popedom["pl"]){
			error(P_Lang('您没有权限执行此操作'),'','error');
		}
		$id = $this->get("id");
		if(!$id){
			error(P_Lang('未指定要操作的附件'));
		}
		$psize = 1;
		$pageid = $this->get("pageid","int");
		$pageid = intval($pageid);
		$ext_list = array("jpg","gif","png","jpeg");
		if($id == 'all'){
			$condition = "1=1";
			$reslist = $this->model('res')->get_list($condition,$pageid,1);
			if(!$reslist){
				error(P_Lang('附件信息更新完毕，共更新数量：{pageid}，点击右上角关闭窗口^_^',array('pageid'=>"<span class='red'>".$pageid."</span>")));
			}
			$rs = current($reslist);
			$myurl = $this->url("res","update_pl") ."&id=all&pageid=".($pageid+1);
		}else{
			$myurl = $this->url("res","update_pl") ."&id=".rawurlencode($id)."&pageid=".($pageid+1);
			$list = explode(",",$id);
			if(!$list[$pageid]){
				error(P_Lang('附件信息更新完毕，共更新数量：{pageid}，点击右上角关闭窗口^_^',array('pageid'=>"<span class='red'>".count($list)."</span>")));
			}
			$rs = $this->model('res')->get_one($list[$pageid]);
			if(!$rs){
				error(P_Lang("附件更新中，当前ID：{pageid} 不存在附件",array('pageid'=>$list[$pageid])),$myurl,'notice');
			}
		}
		$this->gd_update($rs['id']);
		$total = $pageid+1;
		error(P_Lang('附件更新中，当前已更新数量：{total}',array('total'=>"<span class='red'><strong>".$total."</strong></span>")),$myurl,'notice',1);
	}

	private function gd_update($id)
	{
		
		if(!$id){
			return false;
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			return false;
		}
		if($rs['ico'] && substr($rs['ico'],0,7) != 'images/' && is_file($rs['ico'])){
			$this->lib('file')->rm($this->dir_root.$rs['ico']);
		}
		$this->model('res')->ext_delete($id);
		if($rs['cate_id']){
			$cate_rs = $this->model('rescate')->get_one($rs['cate_id']);
			if(!$cate_rs){
				$cate_rs = $this->model('rescate')->get_default();
			}
		}else{
			$cate_rs = $this->model('rescate')->get_default();
		}
		if(!$cate_rs){
			$cate_rs = array('ico'=>1,'gdall'=>1,'gdtypes'=>'');
		}
		$arraylist = array('png','gif','jpeg','jpg');
		if($cate_rs['ico'] && in_array($rs['ext'],$arraylist)){
			$ico = $this->lib('gd')->thumb($this->dir_root.$rs["filename"],$id);
			if(!$ico){
				$ico = "images/filetype-large/".$rs["ext"].".jpg";
				if(!file_exists($this->dir_root.$ico)){
					$ico = "images/filetype-large/unknown.jpg";
				}
			}
			$this->model('res')->save(array('ico'=>$rs['folder'].$ico),$id);
		}else{
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!file_exists($this->dir_root.$ico)){
				$ico = "images/filetype-large/unknown.jpg";
			}
			$this->model('res')->save(array('ico'=>$ico),$id);
		}
		//判断是否有GD图案
		$gdlist = $this->model('gd')->get_all('id');
		if(!$gdlist){
			return true;
		}
		if(!$cate_rs['gdtypes'] && !$cate_rs['gdall']){
			return true;
		}
		$gdtypes = $cate_rs['gdall'] ? array_keys($gdlist) : explode(",",$cate_rs['gdtypes']);
		foreach($gdlist as $key=>$value){
			if(!in_array($value['id'],$gdtypes)){
				continue;
			}
			$array = array();
			$array["res_id"] = $id;
			$array["gd_id"] = $value["id"];
			$array["filetime"] = $this->time;
			$gd_tmp = $this->lib('gd')->gd($this->dir_root.$rs["filename"],$id,$value);
			if($gd_tmp){
				$array["filename"] = $rs["folder"].$gd_tmp;
				$this->model('res')->save_ext($array);
			}
		}
		return true;
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
}
?>
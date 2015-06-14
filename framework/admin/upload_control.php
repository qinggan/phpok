<?php
/***********************************************************
	Filename: {phpok}/admin/upload_control.php
	Note	: 附件上传操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-01-09 10:51
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("res");
		$this->model("gd");
		$this->lib("file");
		$this->lib("json");
		$this->lib("trans");
	}

	//附件上传
	function save_f()
	{
		$cateid = $this->get('cateid','int');
		$rs = $this->upload_base('upfile',$cateid);
		if(!$rs || $rs['status'] != 'ok'){
			$this->json($rs['error']);
		}
		unset($rs['status']);
		$rs['uploadtime'] = date("Y-m-d H:i:s",$rs['addtime']); 
		$this->json($rs,true);
	}


	//基础上传
	private function upload_base($input_name='upfile',$cateid=0)
	{
		if(!$cateid){
			$cate_rs = $this->model('rescate')->get_default();
		}else{
			$cate_rs = $this->model('rescate')->get_one($cateid);
			if(!$cate_rs){
				$cate_rs = $this->model('rescate')->get_default();
			}
		}
		if(!$cate_rs){
			return array('status'=>'error','error'=>P_Lang('未指定分类或附件分类不存在'));
		}
		if(!$cate_rs['filetypes']){
			$cate_rs['filetypes'] = 'jpg,png,gif,rar,zip,doc,docx,xls,xlsx';
		}
		$this->lib('upload')->set_type($cate_rs['filetypes']);
		$rs = $this->lib('upload')->upload($input_name);
		if($rs["status"] != "ok"){
			return $rs;
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/"){
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($folder)){
			$this->lib('file')->make($folder);
		}
		if(!file_exists($folder)){
			$folder = $cate_rs['root'];
		}
		$basename = basename($rs["filename"]);
		$save_folder = $this->dir_root.$folder;
		if($save_folder.$basename != $rs["filename"]){
			$this->lib('file')->mv($rs["filename"],$save_folder.$basename);
		}
		if(!file_exists($save_folder.$basename)){
			$this->lib('file')->rm($rs["filename"]);
			return array('status'=>'error','error'=>P_Lang('图片迁移失败'));
		}
		$array = array();
		$array["cate_id"] = $cateid;
		$array["folder"] = $folder;
		$array["name"] = $basename;
		$array["ext"] = $rs["ext"];
		$array["filename"] = $folder.$basename;
		$array["addtime"] = $this->time;
		$array["title"] = $this->lib('string')->to_utf8($rs['title']);
		$array["title"] = str_replace(".".$rs["ext"],"",$array["title"]);
		$array['admin_id'] = $_SESSION['admin_id'];
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($save_folder.$basename);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$id = $this->model('res')->save($array);
		if(!$id){
			$this->lib('file')->rm($save_folder.$basename);
			return array('status'=>'error','error'=>P_Lang('图片存储失败'));
		}
		//更新缩略图
		$this->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}

	//附件上传替换
	public function replace_f()
	{
		$id = $this->get("oldid",'int');
		if(!$id){
			$this->json(P_Lang('没有指定要替换的附件'));
		}
		$old_rs = $this->model('res')->get_one($id);
		if(!$old_rs){
			$this->json(P_Lang('资源不存在'));
		}
		$rs = $this->lib('upload')->upload('upfile');
		if($rs["status"] != "ok"){
			$this->json(P_Lang('附件上传失败'));
		}
		$arraylist = array("jpg","gif","png","jpeg");
		$my_ext = array();
		if(in_array($rs["ext"],$arraylist)){
			$img_ext = getimagesize($rs["filename"]);
			$my_ext["width"] = $img_ext[0];
			$my_ext["height"] = $img_ext[1];
		}
		$this->lib('file')->mv($rs["filename"],$old_rs["filename"]);
		$tmp = array("addtime"=>$this->time);
		$tmp["attr"] = serialize($my_ext);
		$this->model('res')->save($tmp,$id);
		$this->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
	}

	private function gd_update($id)
	{
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			return false;
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

	public function thumbshow_f()
	{
		$id = $this->get('id');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$list = explode(",",$id);
		$newlist = array();
		foreach($list AS $key=>$value){
			$value = intval($value);
			if($value){
				$newlist[] = $value;
			}
		}
		$id = implode(",",$newlist);
		if(!$id){
			$this->json(P_Lang('请传递正确的附件ID'));
		}
		$rslist = $this->model("res")->get_list_from_id($id);
		if($rslist){
			//排序
			$reslist = array();
			foreach($newlist as $key=>$value){
				if($rslist[$value]){
					$reslist[] = $rslist[$value];
				}
			}
			$this->json($reslist,true);
		}
		$this->json(P_Lang('附件信息获取失败，可能已经删除，请检查'));
	}

	public function editopen_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		$note = form_edit('note',$rs['note'],'editor','width=650&height=250&etype=simple');
		$this->assign('rs',$rs);
		$this->assign('note',$note);
		$this->view("res_editopen");
	}

	public function editopen_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$title = $this->get('title');
		if(!$title){
			$this->json(P_Lang('附件标题不能为空'));
		}
		$note = $this->get('note','html');
		$this->model('res')->save(array('title'=>$title,'note'=>$note),$id);
		$this->json(true);
	}

	public function preview_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		$arraylist = array('jpg','png','gif','jpeg');
		if($rs['ext'] && in_array($rs['ext'],$arraylist)){
			$this->assign('ispic',true);
		}
		$this->assign('rs',$rs);
		$this->view('res_openview');
	}

	public function delete_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('附件信息不存在'));
		}
		if(!$_SESSION['admin_rs']['if_system'] && $rs['admin_id'] != $_SESSION['admin_id']){
			$this->json(P_Lang('非系统管理员不能删除其他管理员上传的附件'));
		}
		//执行删除操作
		$this->model('res')->delete($id);
		$this->json(true);
	}
}
?>
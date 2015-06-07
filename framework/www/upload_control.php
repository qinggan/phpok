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
	}

	//附件上传
	function save_f()
	{
		$this->popedom();
		$cateid = $this->get('cateid','int');
		$rs = $this->upload_base('upfile',$cateid);
		if(!$rs || $rs['status'] != 'ok')
		{
			$this->json($rs['error']);
		}
		unset($rs['status']);
		$rs['uploadtime'] = date("Y-m-d H:i:s",$rs['addtime']); 
		$this->json($rs,true);
	}

	//设置权限，防止非法人员上传
	private function popedom()
	{
		if(!$this->site['upload_guest'] && !$_SESSION['user_id']){
			$this->json(P_Lang('系统已禁止游客上传，请联系管理员'));
		}
		if(!$this->site['upload_user'] && $_SESSION['user_id']){
			$this->json(P_Lang('系统已禁止会员上传，请联系管理员'));
		}
		return true;
	}


	//基础上传
	function upload_base($input_name='upfile',$cateid=0)
	{
		//上传类型
		$typelist = $this->model('res')->type_list();
		if($typelist)
		{
			$ext = array();
			foreach($typelist as $key=>$value)
			{
				$ext[] = $value['ext'];
			}
			$ext = implode(",",$ext);
			$this->lib('upload')->set_type($ext);
		}
		$rs = $this->lib('upload')->upload($input_name);
		if($rs["status"] != "ok")
		{
			return $rs;
		}
		$cate_rs = $this->model('res')->cate_one($cateid);
		if(!$cate_rs || $cate_rs['root'] == '/' || !$cate_rs['root'])
		{
			$cate_rs["id"] = 0;
			$cate_rs["root"] = "res/";
			$cate_rs["folder"] = "/";
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/")
		{
			$folder .= date($cate_rs["folder"],$this->time);
		}
		if(!file_exists($folder))
		{
			$this->lib('file')->make($folder);
		}
		//如果还是没有检测到文件夹，则回到默认目录
		if(!file_exists($folder))
		{
			$folder = $cate_rs['root'];
		}
		//存储目录
		$basename = basename($rs["filename"]);
		$save_folder = $this->dir_root.$folder;
		if($save_folder.$basename != $rs["filename"])
		{
			$this->lib('file')->mv($rs["filename"],$save_folder.$basename);
		}
		if(!file_exists($save_folder.$basename))
		{
			$this->lib('file')->rm($rs["filename"]);
			$rs = array();
			$rs["status"] = "error";
			$rs["error"] = "图片迁移失败";
			return $rs;
		}
		$rs['title'] = $this->lib('string')->to_utf8($rs['title']);
		$array = array();
		$array["cate_id"] = $cateid;
		$array["folder"] = $folder;
		$array["name"] = $basename;
		$array["ext"] = $rs["ext"];
		$array["filename"] = $folder.$basename;
		$array["addtime"] = $this->time;
		$array["title"] = str_replace(".".$rs["ext"],"",$rs["title"]);
		if($_SESSION['user_id'])
		{
			$array['user_id'] = $_SESSION['user_id'];
		}
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist))
		{
			$img_ext = getimagesize($save_folder.$basename);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		//存储图片信息
		$id = $this->model('res')->save($array);
		if(!$id)
		{
			$this->lib('file')->rm($save_folder.$basename);
			$rs = array();
			$rs["status"] = "error";
			$rs["error"] = "图片存储失败";
			return $rs;
		}
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}

	//附件上传替换
	function replace_f()
	{
		$this->popedom();
		$id = $this->get("oldid",'int');
		if(!$id)
		{
			$this->json('没有指定要替换的附件');
		}
		$old_rs = $this->model('res')->get_one($id);
		if(!$old_rs)
		{
			$this->json("资源不存在");
		}
		$rs = $this->lib('upload')->upload('upfile');
		if($rs["status"] != "ok")
		{
			$this->json('附件上传失败');
		}
		$arraylist = array("jpg","gif","png","jpeg");
		$my_ext = array();
		if(in_array($rs["ext"],$arraylist))
		{
			$img_ext = getimagesize($rs["filename"]);
			$my_ext["width"] = $img_ext[0];
			$my_ext["height"] = $img_ext[1];
		}
		//替换资源
		$this->lib('file')->mv($rs["filename"],$old_rs["filename"]);
		$tmp = array("addtime"=>$this->time);
		$tmp["attr"] = serialize($my_ext);
		$this->model('res')->save($tmp,$id);
		//更新附件扩展信息
		$this->model('res')->gd_update($id);
		$rs = $this->model('res')->get_one($id);
		$this->json($rs,true);
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
		$this->json("附件信息获取失败，可能已经删除，请检查");
	}

	public function editopen_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			error(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			error(P_Lang('数据不存在'));
		}
		if($_SESSION['user_id']){
			if($_SESSION['user_id'] != $rs['user_id']){
				error(P_Lang('您没有权限修改此附件信息'));
			}
		}else{
			if(!$rs['session_id']){
				error(P_Lang('您没有权限修改此附件信息'));
			}
			if($_SESSION['session_id'] != $rs['session_id']){
				error(P_Lang('您没有权限修改此附件信息'));
			}
		}
		$note = form_edit('note',$rs['note'],'editor','width=650&height=250&etype=simple');
		$this->assign('rs',$rs);
		$this->assign('note',$note);
		$this->view($this->dir_phpok."open/res_editopen.html",'abs-file',false);
	}

	public function editopen_save_f()
	{
		$id = $this->get('id','int');
		if(!$id){
			$this->json(P_Lang('未指定ID'));
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs){
			$this->json(P_Lang('数据不存在'));
		}
		if($_SESSION['user_id']){
			if($_SESSION['user_id'] != $rs['user_id']){
				$this->json(P_Lang('您没有权限修改此附件信息'));
			}
		}else{
			if(!$rs['session_id']){
				$this->json(P_Lang('您没有权限修改此附件信息'));
			}
			if($_SESSION['session_id'] != $rs['session_id']){
				$this->json(P_Lang('您没有权限修改此附件信息'));
			}
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
		if(!$rs){
			error(P_Lang('数据不存在'));
		}
		if($_SESSION['user_id']){
			if($_SESSION['user_id'] != $rs['user_id']){
				error(P_Lang('您没有权限修改此附件信息'));
			}
		}else{
			if(!$rs['session_id']){
				error(P_Lang('您没有权限修改此附件信息'));
			}
			if($_SESSION['session_id'] != $rs['session_id']){
				error(P_Lang('您没有权限修改此附件信息'));
			}
		}
		$arraylist = array('jpg','png','gif','jpeg');
		if($rs['ext'] && in_array($rs['ext'],$arraylist)){
			$this->assign('ispic',true);
		}
		$this->assign('rs',$rs);
		$this->view($this->dir_phpok."open/res_openview.html",'abs-file',false);
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
		if($_SESSION['user_id']){
			if($_SESSION['user_id'] != $rs['user_id']){
				$this->json(P_Lang('您没有权限删除此附件信息'));
			}
		}else{
			if(!$rs['session_id']){
				$this->json(P_Lang('您没有权限删除此附件信息'));
			}
			if($_SESSION['session_id'] != $rs['session_id']){
				$this->json(P_Lang('您没有权限删除此附件信息'));
			}
		}
		//执行删除操作
		$this->model('res')->delete($id);
		$this->json(true);
	}

}
?>
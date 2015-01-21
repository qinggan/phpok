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
		if(!$rs || $rs['status'] != 'ok')
		{
			$this->json($rs['error']);
		}
		unset($rs['status']);
		$rs['uploadtime'] = date("Y-m-d H:i:s",$rs['addtime']); 
		$this->json($rs,true);
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
		$array = array();
		$array["cate_id"] = $cateid;
		$array["folder"] = $folder;
		$array["name"] = $basename;
		$array["ext"] = $rs["ext"];
		$array["filename"] = $folder.$basename;
		$array["addtime"] = $this->time;
		$rs['title'] = $this->lib('string')->to_utf8($rs['title']);
		$array["title"] = str_replace(".".$rs["ext"],"",$rs["title"]);
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
}
?>
<?php
/***********************************************************
	Filename: {phpok}/admin/ueditor_control.php
	Note	: Ueditor 编辑器中涉及到上传的操作
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013年06月26日 19时04分
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class xheditor_control extends phpok_control
{
	function __construct()
	{
		parent::control();
		$this->model("res");
		$this->model("gd");
		$this->lib("json");
		$this->lib("file");
		$this->lib("html");
	}

	//上传图片操作
	function image_up_f()
	{
		$rs = $this->upload_base();
		if($rs["status"] != "ok")
		{
			//file_put_contents("tmp.php",print_r($rs,true)."------");
			exit("{'state':'".$rs["error"]."'}");
			//exit('{"state":"'.$rs['error'].'"}');
		}
		$title = $this->get("pictitle");
		if($title)
		{
			$tmp = array("title"=>$title);
			$this->model('res')->save($tmp,$rs["id"]);
			$rs["title"] = $title;
		}
		$tmp = array("title"=>$rs['title']);
		$tmp["url"] = $rs["filename"];
		$tmp["original"] = $rs["title"];
		$tmp["status"] = "SUCCESS";
		//echo "{'url':'" . $rs["filename"] . "','title':'" . $rs['title'] . "','original':'" . $rs["name"] . "','state':'SUCCESS'}";
		exit("{'url':'".$tmp["url"]."','title':'".$tmp["title"]."','original':'".$tmp["original"]."','state':'SUCCESS'}");
		//exit($this->lib('json')->encode($tmp));
	}

	//上传文件
	function file_up_f()
	{
		$rs = $this->upload_base();
		if($rs["status"] != "ok")
		{
			exit('{"state":"'.$rs['error'].'"}');
		}
		exit("{'url':'".$rs["filename"]."','fileType':'".$rs["ext"]."','original':'".$rs["title"]."','state':'SUCCESS'}");
	}

	//远程抓图
	function remote_image_f()
	{
		$uri = $this->get("upfile");
		$uri = str_replace( "&amp;" , "&" , $uri );
		if(!$uri)
		{
			echo "{'url':'','tip':'没有图片信息！','srcUrl':'" . $uri . "'}";
			exit;
		}
		$this->lib("html");
		set_time_limit( 0 );
		$imgUrls = explode( "ue_separate_ue" , $uri );
		$tmpNames = array();
		//设置存储附件的目录
		# 存储附件
		$cate_rs = $this->model('res')->cate_one($cateid);
		if(!$cate_rs)
		{
			$cate_rs["id"] = 0;
			$cate_rs["root"] = $this->dir_root."res/";
			$cate_rs["folder"] = "/";
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/")
		{
			$folder .= date($cate_rs["folder"],$this->system_time);
		}
		if(!file_exists($folder))
		{
			$this->lib('file')->make($folder);
		}
		if(substr($folder,-1) != "/") $folder .= "/";
		if(substr($folder,0,1) == "/") $folder = substr($folder,1);
		if($folder)
		{
			$folder = str_replace("//","/",$folder);
		}
		$arraylist = array("jpg","gif","png","jpeg");
		foreach($imgUrls AS $key=>$imgUrl)
		{
			if(strpos($imgUrl,"http")!==0)
			{
                array_push( $tmpNames , "error" );
                continue;
            }
            $content = $this->lib('html')->get_content($imgUrl);
            if(!$content)
            {
	            array_push( $tmpNames , "error" );
                continue;
            }
            $tmp_title = basename($imgUrl);
            $new_filename = substr(md5($imgUrl),9,16)."_".rand(0,99)."_".$key;
            $fileType = strtolower( strrchr( $imgUrl , '.' ));
            $ext = substr($fileType,1);
            if(!$ext) $ext = "png";
			$save_folder = $this->dir_root.$folder;
			$newfile = $save_folder.$new_filename.".".$ext;
			$this->lib('file')->save_pic($content,$newfile);
			if(!file_exists($newfile))
			{
				array_push( $tmpNames , "error" );
                continue;
			}
			//记录到数据库中
			# 将图片移到新目录
			$array = array();
			$array["cate_id"] = $cate_rs["id"];
			$array["folder"] = $folder;
			$array["name"] = $new_filename.".".$ext;
			$array["ext"] = $ext;
			$array["filename"] = $folder.$new_filename.".".$ext;
			$array["addtime"] = $this->system_time;
			if($tmp_title && !$this->is_utf8($tmp_title))
			{
				$tmp_title = $this->charset($tmp_title,"GBK","UTF-8");
			}
			$array["title"] = $tmp_title ? str_replace(".".$ext,"",$tmp_title) : $new_filename;
			if(in_array($ext,$arraylist))
			{
				$img_ext = getimagesize($newfile);
				$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
				$array["attr"] = serialize($my_ext);
			}
			$array["session_id"] = $this->session->sessid();
			//存储图片信息
			$id = $this->model('res')->save($array);
			if(!$id)
			{
				array_push( $tmpNames , "error" );
                continue;
			}
			//更新后台小图
			$this->gd_admin($id);
			//更新附件方案
			$this->gd($id);
			array_push( $tmpNames ,  $folder.$new_filename.".".$ext );
		}
		echo "{'url':'" . implode( "ue_separate_ue" , $tmpNames ) . "','tip':'远程图片抓取成功！','srcUrl':'" . $uri . "'}";
	}
    

	//基础上传
	function upload_base($input_name = "upfile")
	{
		$cateid = $this->get("cateid","int");
		$this->lib("upload");
		$rs = $this->lib('upload')->upload($input_name);
		if($rs["status"] != "ok")
		{
			return $rs;
		}
		# 存储附件
		$cate_rs = $this->model('res')->cate_one($cateid);
		if(!$cate_rs)
		{
			$cate_rs["id"] = 0;
			$cate_rs["root"] = $this->dir_root."res/";
			$cate_rs["folder"] = "/";
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/")
		{
			$folder .= date($cate_rs["folder"],$this->system_time);
		}
		if(!file_exists($folder))
		{
			$this->lib('file')->make($folder);
		}
		if(substr($folder,-1) != "/") $folder .= "/";
		if(substr($folder,0,1) == "/") $folder = substr($folder,1);
		if($folder)
		{
			$folder = str_replace("//","/",$folder);
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
			$rs["error_id"] = "1004";
			return $rs;
		}
		# 将图片移到新目录
		$array = array();
		$array["cate_id"] = $cate_rs["id"];
		$array["folder"] = $folder;
		$array["name"] = $basename;
		$array["ext"] = $rs["ext"];
		$array["filename"] = $folder.$basename;
		$array["addtime"] = $this->system_time;
		if(!$this->is_utf8($rs["title"]))
		{
			$rs["title"] = $this->charset($rs["title"],"GBK","UTF-8");
		}
		$array["title"] = str_replace(".".$rs["ext"],"",$rs["title"]);
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist))
		{
			$img_ext = getimagesize($save_folder.$basename);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$array["session_id"] = $this->session->sessid();
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
		# 更新后台小图
		$this->gd_admin($id);
		# 更新附件方案
		$this->gd($id);
		$rs = $this->model('res')->get_one($id);
		$rs["status"] = "ok";
		return $rs;
	}

	function image_f()
	{
		$id = $this->get("id");
		if(!$id) $id = "content";
		$pageurl = $this->url("xheditor","image","id=".$id);
		$condition = " ext IN('jpg','gif','png','jpeg') ";
		$this->get_list($pageurl,$condition);
		$this->view("xheditor_image");
	}

	function get_list($pageurl,$condition="")
	{
		if(!$condition) $condition = "1=1";
		$keywords = $this->get("keywords");
		if($keywords)
		{
			$condition .= " AND (title LIKE '%".$keywords."%' OR name LIKE '%".$keywords."%') ";
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
		if(!$this->config["pageid"]) $this->config["pageid"] = "pageid";
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 28;
		$offset = ($pageid - 1) * $psize;
		$rslist = $this->model('res')->get_list($condition,$offset,$psize);
		$this->assign("rslist",$rslist);
		$total = $this->model('res')->get_count($condition);
		$this->assign("total",$total);
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
		$this->assign("pagelist",$pagelist);
	}

	function remote_f()
	{
		//当项目为管理员时，且未登录，则不执行图片上动上传
		if($this->app_id == "admin" && !$_SESSION["admin_id"]) exit;

		//要更新的图片信息
		$urls = $this->get("urls");
		if(!$urls)
		{
			exit;
		}
		$list = explode("|",$urls);
		$site_id = $this->app_id == "admin" ? $_SESSION["admin_site_id"] : $this->site["id"];
		$dlist  = $this->model('site')->domain_list($site_id);
		if(!$dlist) $dlist = array(0=>array("id"=>'0',"site_id"=>$site_id,"domain"=>($_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : "localhost")));
		$hostlist = array();
		foreach($dlist AS $key=>$value)
		{
			$hostlist[] = $value["domain"];
		}		
		foreach($list AS $key=>$value)
		{
			$localUrl = $this->save_remote_image($value,$hostlist);
			if($localUrl)
			{
				$list[$key] = $localUrl;
			}
		}
		echo implode("|",$list);
	}

	function save_remote_image($url,$hostlist)
	{
		$upExt="jpg,jpeg,gif,png";//上传扩展名
		$reExt='('.str_replace(',','|',$upExt).')';
		//base64编码的图片，可能出现在firefox粘贴，或者某些网站上，例如google图片
		if(substr($url,0,10)=='data:image')
		{
			if(!preg_match('/^data:image\/'.$reExt.'/i',$url,$sExt))return false;
			$sExt=$sExt[1];
			$imgContent=base64_decode(substr($url,strpos($url,'base64,')+7));
			$tmp_title = "base64_".$this->system_time;
		}
		else
		{//url图片
			if(!preg_match('/\.'.$reExt.'$/i',$url,$sExt)) return false;
			$arrUrl = parse_url(trim($url));
			if(!$arrUrl || in_array($arrUrl['host '],$hostlist)) return false;
			$sExt=$sExt[1];
			$imgContent = $this->lib('html')->get_content($url);
			$tmp_title = basename($url);
		}
		if(!$imgContent)
		{
			return false;
		}
		$new_filename = substr(md5($url),9,16)."_".rand(0,99);
		$fileType = strtolower( strrchr( $url , '.' ));
		$ext = substr($fileType,1);
		if(!$ext) $ext = "png";
		//存储的目录
		$cate_rs = $this->model('res')->cate_one(0);
		if(!$cate_rs)
		{
			$cate_rs["id"] = 0;
			$cate_rs["root"] = $this->dir_root."res/";
			$cate_rs["folder"] = "/";
		}
		$folder = $cate_rs["root"];
		if($cate_rs["folder"] && $cate_rs["folder"] != "/")
		{
			$folder .= date($cate_rs["folder"],$this->system_time);
		}
		if(!file_exists($folder))
		{
			$this->lib('file')->make($folder);
		}
		if(substr($folder,-1) != "/") $folder .= "/";
		if(substr($folder,0,1) == "/") $folder = substr($folder,1);
		if($folder)
		{
			$folder = str_replace("//","/",$folder);
		}
		$save_folder = $this->dir_root.$folder;
		$newfile = $save_folder.$new_filename.".".$ext;
		$this->lib('file')->save_pic($imgContent,$newfile);
		if(!file_exists($newfile))
		{
			return false;
		}
		//记录到数据库中
		# 将图片移到新目录
		$array = array();
		$array["cate_id"] = $cate_rs["id"];
		$array["folder"] = $folder;
		$array["name"] = $new_filename.".".$ext;
		$array["ext"] = $ext;
		$array["filename"] = $folder.$new_filename.".".$ext;
		$array["addtime"] = $this->system_time;
		if($tmp_title && !$this->is_utf8($tmp_title))
		{
			$tmp_title = $this->charset($tmp_title,"GBK","UTF-8");
		}
		$array["title"] = $tmp_title ? str_replace(".".$ext,"",$tmp_title) : $new_filename;
		$arraylist = explode(",",$upExt);
		if(in_array($ext,$arraylist))
		{
			$img_ext = getimagesize($newfile);
			$my_ext = array("width"=>$img_ext[0],"height"=>$img_ext[1]);
			$array["attr"] = serialize($my_ext);
		}
		$array["session_id"] = $this->session->sessid();
		//存储图片信息
		$id = $this->model('res')->save($array);
		if(!$id)
		{
			return false;
		}
		//更新后台小图
		$this->gd_admin($id);
		//更新附件方案
		$this->gd($id);
		return $folder.$new_filename.".".$ext;
	}

	// 更新ICO图片
	function gd_admin($id)
	{
		if(!$id) return false;
		$rs = $this->model('res')->get_one($id);
		if(!$rs) return false;
		$arraylist = array("jpg","gif","png","jpeg");
		if(in_array($rs["ext"],$arraylist))
		{
			$this->lib("gd");
			$ico = $this->lib('gd')->thumb($this->dir_root.$rs["filename"],$id);
			if(!$ico)
			{
				$ico = "images/filetype-large/".$rs["ext"].".jpg";
				if(!file_exists($this->dir_root.$ico))
				{
					$ico = "images/filetype-large/unknow.jpg";
				}
			}
			else
			{
				$ico = $rs["folder"].$ico;
			}
			$tmp = array();
			$tmp["ico"] = $ico;
		}
		else
		{
			$tmp = array();
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!file_exists($this->dir_root.$ico))
			{
				$ico = "images/filetype-large/unknow.jpg";
			}
			$tmp["ico"] = $ico;
		}
		$this->model('res')->save($tmp,$id);
		return true;
	}

	//更新附件的GD方案
	function gd($id)
	{
		if(!$id) return false;
		$this->model('res')->ext_delete($id);//清除现在扩展图片存储信息
		$rs = $this->model('res')->get_one($id);
		if(!$rs || !is_array($rs)) return false;
		$arraylist = array("jpg","gif","png","jpeg");
		$id = $rs["id"];
		if(!in_array($rs["ext"],$arraylist)) return false;
		$this->lib("gd");
		$tmp = array();
		$gdlist = $this->model('gd')->get_all();
		if(!$gdlist) return false;
		foreach($gdlist AS $key=>$value)
		{
			$array = array();
			$array["res_id"] = $id;
			$array["gd_id"] = $value["id"];
			$array["filetime"] = $this->system_time;
			$gd_tmp = $this->lib('gd')->gd($rs["filename"],$id,$value);
			if($gd_tmp)
			{
				$array["filename"] = $rs["folder"].$gd_tmp;
				$this->model('res')->save_ext($array);
			}
		}
		return true;
	}
	
}
?>
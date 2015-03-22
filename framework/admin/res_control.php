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
	var $popedom;
	function __construct()
	{
		parent::control();
		$this->popedom = appfile_popedom("res");
		$this->assign("popedom",$this->popedom);
		//$this->lib('form')->cssjs();
	}

	//附件
	function index_f()
	{
		$pageid = $this->get($this->config["pageid"],"int");
		if(!$pageid) $pageid = 1;
		$psize = 32;
		$pageurl = admin_url("res");
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
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1");
		$this->assign("pagelist",$pagelist);
		# 存储当前的URL信息
		$myurl = $pageurl ."&".$this->config["pageid"]."=".$pageid;
		$_SESSION["admin_return_url"] = $myurl;
		$this->view("res_index");
	}

	# 添加附件
	function add_f()
	{
		# 附件分类
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		# 附件类型
		$config = $this->model('res')->type_list();
		$this->assign("attr_list",$config);
		$this->view("res_add");
	}

	//附件管理
	function set_f()
	{
		$error_url = $_SESSION["admin_return_url"] ? $_SESSION["admin_return_url"] : admin_url("res");
		$this->assign("home_url",$error_url);
		$id = $this->get("id","int");
		if(!$id)
		{
			error("未指定附件ID！",$error_url,"error");
		}
		$this->assign("id",$id);
		$rs = $this->model('res')->get_one($id,true);
		if(!$rs)
		{
			error("附件不存在",$error_url,"error");
		}
		//判断附件属性
		$extlist = array("jpg","png","gif","jpeg");
		$show_ext = false;
		if(in_array($rs["ext"],$extlist))
		{
			$show_ext = true;
			$rs["gd"] = ($rs["gd"] && is_array($rs["gd"])) ? $rs["gd"] : array();
			# 取得所有的GD列
			$gdlist = $this->model('gd')->get_all("identifier");
			$tmplist = array();
			foreach($rs["gd"] AS $key=>$value)
			{
				if(file_exists($this->dir_root.$value["filename"]))
				{
					$tmp = array();
					list($width, $height, $type, $attr) = getimagesize($this->dir_root.$value["filename"]);
					$tmp["width"] = $width;
					$tmp["height"] = $height;
					$tmp["gd"] = $value["identifier"];
					$tmp["filename"] = $value["filename"];
					$tmplist[] = $tmp;
					if($gdlist[$value["identifier"]]) unset($gdlist[$key]);
				}
			}
			$rs["gd"] = $tmplist;
			$this->assign("gdlist",$gdlist);
		}
		$this->assign("id",$id);
		$this->assign("rs",$rs);
		$this->assign("show_ext",$show_ext);
		$catelist = $this->model('res')->cate_all();
		$this->assign("catelist",$catelist);
		$this->view("res_manage");
	}

	# 存储编辑
	function setok_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			error("未指定附件ID",admin_url("res"));
		}
		$title = $this->get("title");
		if(!$title)
		{
			error("附件名称不能为空",admin_url("res","set","id=".$id),"error");
		}
		$note = $this->get("note");
		$cate_id = $this->get("cate_id","int");
		$array = array();
		$array["title"] = $title;
		$array["note"] = $note;
		$array["cate_id"] = $cate_id;
		$this->model('res')->save($array,$id);
		$myurl = $_SESSION["admin_return_url"] ? $_SESSION["admin_return_url"] : admin_url("res");
		error("附件信息更新成功",$myurl,"ok");
	}

	# 获取AJAX里的图片信息
	function ajax_one_f()
	{
		$id = $this->get("id","int");
		$filename = $this->get("filename");
		if(!$id && !$filename)
		{
			json_exit("没有指定要删除的信息！");
		}
		$rs = $id ? $this->model('res')->get_one($id) : $this->model('res')->get_one_filename($filename);
		if(!$rs)
		{
			json_exit("没有找到要删除的信息！");
		}
		$this->assign("rs",$rs);
		$content = $this->fetch("res_li");
		json_exit($content,true);
	}

	# 通过Ajax更新附件名称
	function update_title_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定ID");
		}
		$title = $this->get("title");
		if(!$title)
		{
			json_exit("附件名称不能为空");
		}
		$this->model('res')->update_title($title,$id);
		json_exit("附件名称更新成功",true);
	}

	# 通过Ajax更新名称和备注
	function update_title_note_f()
	{
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定ID");
		}
		$title = $this->get("title");
		if(!$title)
		{
			json_exit("附件名称不能为空");
		}
		$this->model('res')->update_title($title,$id);
		$note = $this->get("note");
		$this->model('res')->update_note($note,$id);
		json_exit("附件信息更新成功",true);
	}

	# 附件分类维护
	function cate_f()
	{
		if(!$this->popedom["cate"]) error("你没有管理附件分类权限");
		$rslist = $this->model('res')->cate_all();
		$this->assign("rslist",$rslist);
		$this->view("res_cate");
	}

	# 存储分类配置信息
	function cate_save_f()
	{
		if(!$this->popedom["cate"]) json_exit("你没有管理附件分类权限");
		$id = $this->get("id","int");
		$title = $this->get("title");
		if(!$title)
		{
			json_exit("分类名称不能为空！");
		}
		$root = $this->get("root");
		if(!preg_match("/[a-z0-9\_\-\/]+/",$root))
		{
			json_exit("文件夹不符合系统要求，只支持：小写字母、数字、下划线、中划线及斜杠");
		}
		if($root && $root != "/")
		{
			if(substr($root,0,1) == "/") $root = substr($root,1);
			if(!file_exists($this->dir_root.$root))
			{
				$this->lib('file')->make($this->dir_root.$root);
			}
		}
		$folder = $this->get("folder");
		$array = array();
		$array["title"] = $title;
		$array["root"] = $root;
		$array["folder"] = $folder;
		$this->model('res')->cate_save($array,$id);
		json_exit("分类创建/更新成功",true);
	}

	# 删除分类操作
	function cate_delete_f()
	{
		if(!$this->popedom["cate"]) json_exit("你没有管理附件分类权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定要删除的分类！");
		}
		$rs = $this->model('res')->cate_default();
		if(!$rs)
		{
			json_exit("附件分类不存在默认分类，不能执行删除操作");
		}
		if($rs["id"] == $id)
		{
			json_exit("该分类为默认分类，不能删除");
		}
		$this->model('res')->cate_delete($id,$rs["id"]);
		json_exit("附件分类删除成功",true);
	}

	# 设置为默认分类
	function cate_default_f()
	{
		if(!$this->popedom["cate"]) json_exit("你没有管理附件分类权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定要分类ID！");
		}
		$rs = $this->model('res')->cate_one($id);
		if(!$rs)
		{
			json_exit("分类不存在");
		}
		$this->model('res')->cate_default_set($id);
		json_exit("默认分类属性操作成功",true);
	}

	# 删除附件操作
	function delete_f()
	{
		$id = $this->get("id","int");
		$filename = $this->get("filename");
		if(!$id && !$filename)
		{
			json_exit("没有指定要删除的信息！");
		}
		$rs = $id ? $this->model('res')->get_one($id) : $this->model('res')->get_one_filename($filename);
		if(!$rs)
		{
			json_exit("没有找到要删除的信息！");
		}
		$this->model('res')->delete($rs["id"]);
		json_exit("删除成功",true);
	}

	function gd_f()
	{
		if(!$this->popedom["gd"]) error("你没有管理附件GD方案权限");
		$rslist = $this->model('gd')->get_all();
		$this->assign("rslist",$rslist);
		$this->view("res_gd");
	}

	# 删除图片方案
	function gd_delete_f()
	{
		if(!$this->popedom["gd"]) json_exit("你没有管理附件GD方案权限");
		$id = $this->get("id","int");
		if(!$id)
		{
			json_exit("未指定ID！");
		}
		//删除图片方案下的图片信息
		$this->model('res')->delete_gd_id($id,$this->dir_root);
		//删除方案信息
		$this->model('gd')->delete($id);
		json_exit("图片方案删除成功！",true);
	}

	function gd_set_f()
	{
		if(!$this->popedom["gd"]) error("你没有管理附件GD方案权限");
		$id = $this->get("id","int");
		if($id)
		{
			$rs = $this->model('gd')->get_one($id);
			if($rs["mark_picture"] && !file_exists($rs["mark_picture"]))
			{
				$rs["mark_picture"] = "";
			}
			$this->assign("id",$id);
			$this->assign("rs",$rs);
		}
		$this->view("res_gd_set");
	}

	# 存储GD库方案信息
	function gd_save_f()
	{
		if(!$this->popedom["gd"]) error("你没有管理附件GD方案权限");
		$id = $this->get("id","int");
		$error_url = admin_url("res","gd_set");
		$array = array();
		if(!$id)
		{
			$identifier = $this->get("identifier");
			$chk = $this->gd_chk($identifier);
			if($chk != "ok")
			{
				error($chk,$error_url,"error");
			}
			$array["identifier"] = $identifier;
		}
		if($id)
		{
			$error_url .= "&id=".$id;
		}
		$array["width"] = $this->get("width","int");
		$array["height"] = $this->get("height","int");
		$array["mark_picture"] = $this->get("mark_picture");
		$array["mark_position"] = $this->get("mark_position");
		$array["cut_type"] = $this->get("cut_type","int");
		$array["bgcolor"] = $this->get("bgcolor");
		$array["trans"] = $this->get("trans","int");
		$array["quality"] = $this->get("quality","int");
		$this->model('gd')->save($array,$id);
		error("GD方案操作成功！",admin_url("res","gd"));
	}

	# 设为默认编辑器
	function gd_editor_f()
	{
		if(!$this->popedom["gd"]) json_exit("你没有管理附件GD方案权限");
		$id = $this->get("id");
		if(!$id)
		{
			json_exit("未指定ID");
		}
		$this->model('gd')->update_editor($id);
		json_exit("设置成功",true);
	}

	# 验证GD标识串
	function gd_chk($identifier)
	{
		if(!$identifier) return "标识不能为空";
		$identifier = strtolower($identifier);
		if(!preg_match("/[a-z][a-z0-9\_\-]+/",$identifier))
		{
			return "标识不符合系统要求，限字母、数字及下划线且必须是字母开头！";
		}
		$chk = $this->model('gd')->identifier_check($identifier);
		if($chk)
		{
			return "标识已经存在！";
		}
		return "ok";
	}

	# 检测GD类
	function gd_chk_f()
	{
		$identifier = $this->get("identifier");
		$rs = $this->gd_chk($identifier);
		if($rs == "ok")
		{
			json_exit("标识符合要求！",true);
		}
		json_exit($rs);
	}

	function recreate_f()
	{
		$id = $this->get("id","int");
		$gd = $this->get("gd");
		if(!$id)
		{
			json_exit("没有指定图片ID");
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs)
		{
			json_exit("内容不存在");
		}
		$list = array("jpg","png","gif","jpeg");
		if(!in_array($rs["ext"],$list))
		{
			json_exit("附件类型：".$rs["ext"]." 不支持创建缩略图");
		}
		if($gd)
		{
			$gd_rs = $this->model('gd')->get_one($gd,"identifier");
			if(!$gd_rs)
			{
				json_exit("此方案图片不存在！");
			}
			$gd_id = $gd_rs["id"];
			$ext_rs = $this->model('res')->get_pic($id,$gd_id);
			$array = array();
			$array["res_id"] = $id;
			$array["gd_id"] = $gd_id;
			if(!$ext_rs)
			{
				$ext_rs = array();
			}
			# 更新存储
			$array["x1"] = $ext_rs["x1"];
			$array["y1"] = $ext_rs["y1"];
			$array["x2"] = $ext_rs["x2"];
			$array["y2"] = $ext_rs["y2"];
			$array["w"] = $ext_rs["w"];
			$array["h"] = $ext_rs["h"];
			if($ext_rs["x2"] && $ext_rs["y2"] && $ext_rs["w"] && $ext_rs["h"])
			{
				$w = $ext_rs["w"];
				$h = $ext_rs["h"];
				$x1 = $ext_rs["x1"];
				$y1 = $ext_rs["y1"];
				$new = $rs["folder"]."_tmp_".$id."_.".$rs["ext"];
				$cropped = $this->create_img($new,$this->dir_root.$rs["filename"],$w,$h,$x1,$y1);
				$this->lib('gd')->gd($new,$id,$gd_rs);
			}
			else
			{
				$this->lib('gd')->gd($this->dir_root.$rs["filename"],$id,$gd_rs);
			}
			$array["filename"] = $rs["folder"].$gd_rs["identifier"]."_".$id.".".$rs["ext"];
			$array["filetime"] = $this->system_time;
			$this->model('res')->save_ext($array);
		}
		else
		{
			$ico = $this->lib('gd')->thumb($this->dir_root.$rs["filename"],$id);
			if(!$ico)
			{
				$ico = "images/filetype-large/".$rs["ext"].".jpg";
				if(!file_exists($ico) && !file_exists($this->dir_root.$ico))
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
			$this->model('res')->save($tmp,$id);
		}
		json_exit("更新完成",true);
	}

	//通过Ajax更新缩略图信息
	function cut_save_f()
	{
		$id = $this->get("id","int");
		$x1 = $this->get("x1");
		$y1 = $this->get("y1");
		$x2 = $this->get("x2");
		$y2 = $this->get("y2");
		$w = $this->get("w");
		$h = $this->get("h");
		$type = $this->get("type");
		$gd = $this->get("gd","int");
		$rs = $this->model('res')->get_one($id);
		$new = $rs["folder"]."_tmp_".$id."_.".$rs["ext"];
		$cropped = $this->create_img($new,$this->dir_root.$rs["filename"],$w,$h,$x1,$y1,1);
		# 判断是否GD
		if($gd)
		{
			$gd_rs = $this->model('gd')->get_one($type,"identifier");
			if(!$gd_rs)
			{
				json_exit("没有此图片方案，请检查");
			}
			$this->lib('gd')->gd($new,$id,$gd_rs);
			# 更新存储
			$array = array();
			$array["res_id"] = $id;
			$array["gd_id"] = $gd_rs["id"];
			$array["x1"] = $x1;
			$array["y1"] = $y1;
			$array["x2"] = $x2;
			$array["y2"] = $y2;
			$array["w"] = $w;
			$array["h"] = $h;
			$array["filename"] = $rs["folder"].$gd_rs["identifier"]."_".$id.".".$rs["ext"];
			$array["filetime"] = $this->system_time;
			$this->model('res')->save_ext($array);
		}
		else
		{
			$ico = $this->lib('gd')->thumb($new,$id);
			$array = array();
			$array["ico"] = $rs["folder"].$ico;
			$this->model('res')->save($array,$id);
		}
		# 删除裁取生成的图片规格
		$this->lib('file')->rm($new);
		json_exit("操作成功",true);
	}

	function create_img($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale=1)
	{
		list($imagewidth, $imageheight, $imageType) = getimagesize($image);
		$imageType = image_type_to_mime_type($imageType);

		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
		switch($imageType) {
			case "image/gif":
				$source=imagecreatefromgif($image);
				break;
			case "image/pjpeg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/jpeg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/jpg":
				$source=imagecreatefromjpeg($image);
				break;
			case "image/png":
				$source=imagecreatefrompng($image);
				break;
			case "image/x-png":
				$source=imagecreatefrompng($image);
				break;
		}
		imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
		switch($imageType) {
			case "image/gif":
				imagegif($newImage,$thumb_image_name);
				break;
			case "image/pjpeg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/jpeg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/jpg":
				imagejpeg($newImage,$thumb_image_name,100);
				break;
			case "image/png":
				imagepng($newImage,$thumb_image_name);
				break;
			case "image/x-png":
				imagepng($newImage,$thumb_image_name);
				break;
		}
		return $thumb_image_name;
	}

	# 下载附件
	function download_f()
	{
		$e_url = $_SESSION["admin_return_url"] ? $_SESSION["admin_return_url"] : admin_url("res");
		$id = $this->get("id","int");
		if(!$id)
		{
			error("未指定附件名！",$e_url,"error");
		}
		$rs = $this->model('res')->get_one($id);
		if(!$rs)
		{
			error("附件信息不存在",$e_url,"error");
		}
		$e_url = admin_url("res","set","id=".$id);
		if(!$rs["filename"] || !file_exists($this->dir_root.$rs["filename"]))
		{
			error("附件不存在",$e_url,"error");
		}
		$my = strtolower(substr($rs["filename"],0,7));
		if($my == "https:/" || $my == "http://")
		{
			error("远程附件不允许下载，请直接打开",$e_url,"error");
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
		if(!$this->popedom["pl"]) error("你没有批处理附件权限");
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
		$pagelist = phpok_page($pageurl,$total,$pageid,$psize,"home=首页&prev=上一页&next=下一页&last=尾页&half=5&add=数量：(total)/(psize)，页码：(num)/(total_page)&always=1&half=3");
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
		if(!$this->popedom["pl"]) error_open("你没有批处理附件权限");
		$id = $this->get("id");
		if(!$id)
		{
			error_open("未指定要操作的附件","error");
		}
		$psize = 1;
		$pageid = $this->get("pageid","int");
		$pageid = intval($pageid);
		$ext_list = array("jpg","gif","png","jpeg");
		if($id == 'all')
		{
			//绑定附件类型
			$condition = "1=1";
			$reslist = $this->model('res')->get_list($condition,$pageid,1);
			if(!$reslist)
			{
				error("附件信息更新完毕，共更新数量：<span class='red'>".$pageid."</span>，点击右上角关闭窗口^_^");
			}
			$rs = current($reslist);
			$myurl = $this->url("res","update_pl") ."&id=all&pageid=".($pageid+1);
		}
		else
		{
			$myurl = $this->url("res","update_pl") ."&id=".rawurlencode($id)."&pageid=".($pageid+1);
			$list = explode(",",$id);
			if(!$list[$pageid])
			{
				error_open("附件信息更新完毕，共更新数量：<span class='red'>".count($list)."</span>，点击右上角关闭窗口^_^");
			}
			$rs = $this->model('res')->get_one($list[$pageid]);
			if(!$rs)
			{
				error("附件更新中，当前ID：".$list[$pageid]." 不存在附件",$myurl,'notice');
			}
		}
		//如果附件符合要求更新
		$gdlist = $this->model('gd')->get_all();
		if($rs["ext"] && in_array($rs['ext'],$ext_list) && is_file($rs['filename']) && $gdlist)
		{
			//删除旧的附件扩展信息
			$this->model('res')->ext_delete($rs['id']);
			foreach($gdlist AS $key=>$value)
			{
				$array = array();
				$array["res_id"] = $rs['id'];
				$array["gd_id"] = $value["id"];
				$array["filetime"] = $this->system_time;
				$gd_tmp = $this->lib('gd')->gd($this->dir_root.$rs["filename"],$rs['id'],$value);
				if($gd_tmp)
				{
					$array["filename"] = $rs["folder"].$gd_tmp;
					$this->model('res')->save_ext($array);
				}
			}
			//更新附件缩略图
			$ico = $this->lib('gd')->thumb($this->dir_root.$rs["filename"],$rs['id']);
			if($ico)
			{
				$ico = $rs['folder'].$ico;
			}
		}
		if(!$ico)
		{
			$ico = "images/filetype-large/".$rs["ext"].".jpg";
			if(!is_file($this->dir_root.$ico)) $ico = 'images/filetype-large/unknown.jpg';
		}
		//更新图标
		$tmp = array();
		$tmp["ico"] = $ico;
		$this->model('res')->save($tmp,$rs['id']);
		$total = $pageid+1;
		error("附件更新中，当前已更新数量：<span class='red'><strong>".$total."</strong></span>",$myurl,'notice',1);
	}

	function delete_pl_f()
	{
		if(!$this->popedom["pl"]) json_exit("你没有批处理附件权限");
		$id = $this->get("id");
		if(!$id)
		{
			json_exit("未指定要删除的附件ID");
		}
		$list = explode(",",$id);
		$tmplist = array();
		foreach($list AS $key=>$value)
		{
			$tmp = intval($value);
			if($tmp) $tmplist[] = $value;
		}
		$id = implode(",",$tmplist);
		if(!$id) return false;
		$rslist = $this->model('res')->get_list_from_id($id,true);
		if($rslist)
		{
			foreach($rslist AS $key=>$value)
			{
				//删除附件
				if(file_exists($this->dir_root.$value["filename"]) && is_file($this->dir_root.$value["filename"]))
				{
					$this->lib('file')->rm($value["filename"]);
				}
				if($value["ico"] && substr($value["ico"],0,7) != "images/" && file_exists($this->dir_root.$value["ico"]))
				{
					$this->lib('file')->rm($value["ico"]);
				}
				//删除扩展
				foreach($value["gd"] AS $k=>$v)
				{
					if($v["filename"] && file_exists($this->dir_root.$v["filename"]))
					{
						$this->lib('file')->rm($this->dir_root.$v["filename"]);
					}
				}
			}
		}
		//删除主表记录
		$this->model('res')->pl_delete($id);
		json_exit("附件删除成功",true);
	}
}
?>
<?php
/***********************************************************
	Filename: {phpok}/form/upload_admin.php
	Note	: 附件属性
	Version : 4.0
	Web		: www.phpok.com
	Author  : qinggan <qinggan@188.com>
	Update  : 2013-03-14 05:37
***********************************************************/
if(!defined("PHPOK_SET")){exit("<h1>Access Denied</h1>");}
class upload_form
{
	function __construct()
	{
		//初始化表单
		//$GLOBALS['app']->addjs('js/swfupload/swfupload.js');
		//$GLOBALS['app']->addcss('css/swfupload.css');
	}

	function cssjs()
	{
		$GLOBALS['app']->addjs('js/webuploader/webuploader.min.js');
		$GLOBALS['app']->addcss('js/webuploader/webuploader.css');
	}

	function config()
	{
		
		$type_list = $GLOBALS['app']->model('res')->type_list();
		$GLOBALS['app']->assign("type_list",$type_list);
		//取得附件分类
		$cate_list = $GLOBALS['app']->model('res')->cate_all();
		$GLOBALS['app']->assign("cate_list",$cate_list);
		$html = $GLOBALS['app']->dir_phpok."form/html/upload_admin.html";
		$GLOBALS['app']->view($html,"abs-file",false);
	}

	function format($rs)
	{
		if($rs["content"])
		{
			if(is_string($rs["content"]))
			{
				$res = $GLOBALS['app']->model('res')->get_list_from_id($rs["content"]);
			}
			else
			{
				$is_list = $rs["content"]["id"] ? false : true;
				$res = array();
				if($is_list)
				{
					foreach($rs["content"]["info"] AS $key=>$value)
					{
						$res[$value["id"]] = $value;
					}
				}
				else
				{
					$res[$rs["content"]["id"]] = $rs["content"];
				}
				$id_list = array_keys($res);
				$rs["content"] = implode(",",$id_list);
			}
			$rs["content_list"] = $res; //附件列表
		}
		else
		{
			$rs["content_list"] = array(); //附件列表
		}
		$type_list = $GLOBALS['app']->model('res')->type_list();
		$type_id = $rs["upload_type"];
		if($rs["upload_type"] && $type_list[$rs["upload_type"]])
		{
			$rs["upload_type"] = $type_list[$rs["upload_type"]];
		}
		else
		{
			$str_array = array();
			foreach($type_list AS $key=>$value)
			{
				$str_array[] = $value["ext"];
			}
			$str = implode(',',$str_array);
			$swfupload = array();
			$str_array = explode(",",$str);
			foreach($str_array AS $key=>$value)
			{
				$swfupload[] = "*.".$value;
			}
			$swfupload = implode(";",$swfupload);
			$rs["upload_type"] = array(
				"id"=>"file",
				"title"=>"附件",
				"ext"=>$str,
				"swfupload"=>$swfupload
			);
		}
		$rs["upload_type"]["id"] = $type_id;
		$GLOBALS['app']->assign("_rs",$rs);
		$file = $GLOBALS['app']->dir_phpok."form/html/upload_form_admin.html";
		$content = $GLOBALS['app']->fetch($file,'abs-file',false);
		$GLOBALS['app']->unassign("_rs");
		return $content;
	}

	//附件操作
	public function show($rs,$info='')
	{
		if($info)
		{
			$rs['content'] = $info;
		}
		if(!$rs || !$rs["content"])
		{
			return false;
		}
		if($rs['ext'] && is_string($rs['ext']))
		{
			$rs['ext'] = unserialize($rs['ext']);
		}
		if($rs['ext'] && $rs["ext"]["is_multiple"])
		{
			$list = $GLOBALS['app']->lib('ext')->res_list($rs["content"]);
			if(!$list) return false;
			$_admin = array("id"=>$rs["content"],"type"=>"pic");
			$tmp = current($list);
			$_admin["info"] = $tmp["ico"];
			return array("info"=>$list,"_admin"=>$_admin);
		}
		$list = $GLOBALS['app']->lib('ext')->res_info($rs["content"]);
		if(!$list) return false;
		$_admin = array("id"=>$rs["content"],"type"=>"pic","info"=>$list["ico"]);
		$list["_admin"] = $_admin;
		return $list;
	}
}
?>
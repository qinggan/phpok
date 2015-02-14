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
	private $tpl_file;
	function __construct()
	{
		$this->tpl_file = $GLOBALS['app']->dir_phpok.'form/html/upload_form_www.html';
	}
	
	function format($rs)
	{
		$rs = $this->_content_list($rs);
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
		$content = $GLOBALS['app']->fetch($this->tpl_file,'abs-file',false);
		$GLOBALS['app']->unassign("_rs");
		return $content;
	}

	private function _content_list($rs)
	{
		$rs['content_list'] = array();
		if(!$rs['content'])
		{
			return $rs;
		}
		if(is_string($rs['content']))
		{
			$res = $GLOBALS['app']->model('res')->get_list_from_id($rs['content']);
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
		return $rs;
	}

	//显示附件信息
	public function show($rs,$info='')
	{
		if(!$info)
		{
			$info = $rs['content'];
		}
		if(!$info)
		{
			return false;
		}
		//读出附件信息
		$condition = "id IN(".$info.")";
		$rslist = $GLOBALS['app']->model('res')->get_list($condition,0,999,true);
		if(!$rslist)
		{
			return false;
		}
		$list = false;
		foreach($rslist as $key=>$value)
		{
			if($value['gd'])
			{
				$tmp = false;
				foreach($value['gd'] as $k=>$v)
				{
					$tmp[$k] = $v['filename'];
				}
				$value['gd'] = $tmp;
			}
			unset($value['attr']);
			$list[$value['id']] = $value;
		}
		$rslist = $list;
		unset($list);
		$multiple = false;
		if($rs['ext'])
		{
			$ext = is_string($rs['ext']) ? unserialize($rs['ext']) : $rs['ext'];
			$multiple = $ext['is_multiple'] ? true : false;
		}
		if($multiple)
		{
			return $rslist;
		}
		else
		{
			$id = explode(",",$info);
			$id = $id[0];
			return $rslist[$id];
		}
	}
}
?>